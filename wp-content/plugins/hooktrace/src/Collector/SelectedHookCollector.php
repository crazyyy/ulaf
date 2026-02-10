<?php
/**
 * Collects detailed callbacks for selected hook only.
 *
 * @package HookTrace\Collector
 */

namespace HookTrace\Collector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HookTrace\Inspector\CallbackInspector;
use HookTrace\Storage\RequestStorage;

/**
 * Tracks detailed callbacks for the selected hook only.
 */
class SelectedHookCollector {

	/**
	 * Original callbacks before wrapping.
	 *
	 * @var array
	 */
	private static $original_callbacks = array();

	/**
	 * Whether callbacks have been wrapped.
	 *
	 * @var bool
	 */
	private static $wrapped = false;

	/**
	 * Execution counter for unique IDs.
	 *
	 * @var int
	 */
	private static $execution_counter = 0;

	/**
	 * Register hooks for collection.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		$selected_hook = RequestStorage::get_selected_hook();
		if ( ! $selected_hook ) {
			return;
		}

		// Wrap callbacks right before the hook fires using 'all' filter
		add_filter( 'all', array( $this, 'wrap_callbacks_before_execution' ), PHP_INT_MIN );
	}

	/**
	 * Wrap callbacks right before the selected hook executes.
	 *
	 * @param mixed $value Filter value.
	 * @return mixed Unchanged value.
	 */
	public function wrap_callbacks_before_execution( $value ) {
		$hook_name = current_filter();
		$selected_hook = RequestStorage::get_selected_hook();

		if ( $hook_name !== $selected_hook ) {
			return $value;
		}

		// Only wrap once
		if ( ! self::$wrapped ) {
			$this->wrap_callbacks( $selected_hook );
			self::$wrapped = true;
		}

		return $value;
	}

	/**
	 * Wrap all callbacks for the selected hook.
	 *
	 * @param string $hook_name Hook name.
	 * @return void
	 */
	private function wrap_callbacks( string $hook_name ): void {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook_name ] ) ) {
			return;
		}

		$hook_obj = $wp_filter[ $hook_name ];
		if ( ! is_object( $hook_obj ) || ! isset( $hook_obj->callbacks ) ) {
			return;
		}

		// Get priorities
		$priorities = array_keys( $hook_obj->callbacks );

		foreach ( $priorities as $priority ) {
			if ( ! isset( $wp_filter[ $hook_name ]->callbacks[ $priority ] ) ) {
				continue;
			}

			$callback_keys = array_keys( $wp_filter[ $hook_name ]->callbacks[ $priority ] );

			foreach ( $callback_keys as $callback_key ) {
				$callback_data = $wp_filter[ $hook_name ]->callbacks[ $priority ][ $callback_key ];
				$original_callback = $callback_data['function'] ?? null;

				if ( ! $original_callback ) {
					continue;
				}

				// Store original callback
				$composite_key = $hook_name . ':' . $priority . ':' . $callback_key;

				// Skip if already wrapped
				if ( isset( self::$original_callbacks[ $composite_key ] ) ) {
					continue;
				}

				self::$original_callbacks[ $composite_key ] = $original_callback;

				// Get metadata before wrapping
				$metadata = CallbackInspector::inspect( $original_callback );
				$accepted_args = $callback_data['accepted_args'] ?? 1;

				// Create wrapper
				$collector = $this;
				$wrapped = function( ...$args ) use ( $original_callback, $composite_key, $hook_name, $priority, $accepted_args, $metadata, $collector ) {
					// Start timing
					$start = microtime( true );

					// Execute original callback
					$result = $collector->execute_callback( $original_callback, $args, $accepted_args );

					// End timing
					$end = microtime( true );
					$duration = ( $end - $start ) * 1000; // Convert to ms

					// Increment execution counter
					self::$execution_counter++;

					// Store this execution
					RequestStorage::add_selected_hook_callback(
						array(
							'hook_name'       => $hook_name,
							'callback'        => $collector->format_callback( $original_callback ),
							'callback_raw'    => $original_callback,
							'priority'        => $priority,
							'accepted_args'   => $accepted_args,
							'execution_order' => self::$execution_counter,
							'timestamp'       => $start,
							'duration'        => $duration,
							'file'            => $metadata['file'],
							'line'            => $metadata['line'],
							'plugin'          => $metadata['plugin'],
							'type'            => $metadata['type'],
							'class'           => $metadata['class'] ?? '',
							'name'            => $metadata['name'] ?? '',
						)
					);

					return $result;
				};

				// Directly modify the global $wp_filter
				$wp_filter[ $hook_name ]->callbacks[ $priority ][ $callback_key ]['function'] = $wrapped;
			}
		}
	}

	/**
	 * Execute callback with proper argument handling.
	 *
	 * @param callable $callback Callback.
	 * @param array    $args Arguments.
	 * @param int      $accepted_args Number of accepted arguments.
	 * @return mixed Callback result.
	 */
	public function execute_callback( $callback, array $args, int $accepted_args ) {
		if ( $accepted_args === 0 ) {
			return call_user_func( $callback );
		} elseif ( $accepted_args >= count( $args ) ) {
			return call_user_func_array( $callback, $args );
		} else {
			return call_user_func_array( $callback, array_slice( $args, 0, $accepted_args ) );
		}
	}

	/**
	 * Format callback for display.
	 *
	 * @param callable $callback Callback.
	 * @return string
	 */
	public function format_callback( $callback ): string {
		if ( is_string( $callback ) ) {
			return $callback;
		}
		if ( is_array( $callback ) ) {
			$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			return $class . '::' . $callback[1];
		}
		if ( $callback instanceof \Closure ) {
			return '{closure}';
		}
		return '{unknown}';
	}
}
