<?php
/**
 * Collects list of hooks that fired (simplified collector).
 *
 * @package HookTrace\Collector
 */

namespace HookTrace\Collector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HookTrace\Storage\RequestStorage;

/**
 * Tracks which hooks fired on the page (simplified - no profiling).
 */
class HookListCollector {

	/**
	 * Register hooks for collection.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Hook into 'all' filter to capture every hook that fires
		add_filter( 'all', array( $this, 'capture_hook' ), PHP_INT_MIN );
	}

	/**
	 * Capture hook that fired.
	 *
	 * @param mixed $value Filter value (we don't modify it).
	 * @return mixed Unchanged value.
	 */
	public function capture_hook( $value ) {
		$hook_name = current_filter();
		if ( ! $hook_name ) {
			return $value;
		}

		// Determine hook type (action or filter)
		$hook_type = $this->determine_hook_type( $hook_name );

		// Determine source (core/theme/plugin)
		$source = $this->determine_source( $hook_name );

		// Add to hooks list
		RequestStorage::add_hook( $hook_name, $hook_type, $source );

		return $value;
	}

	/**
	 * Determine if hook is an action or filter.
	 *
	 * @param string $hook_name Hook name.
	 * @return string 'action' or 'filter'
	 */
	private function determine_hook_type( string $hook_name ): string {
		global $wp_filter, $wp_actions;

		// Check if it's in actions array (actions are tracked separately)
		if ( isset( $wp_actions[ $hook_name ] ) ) {
			return 'action';
		}

		// Check if it's in filters array
		if ( isset( $wp_filter[ $hook_name ] ) ) {
			// Most hooks can be both, but we check current context
			// If it's being used as filter (has return value), it's a filter
			// For simplicity, we'll check if it's commonly used as action
			$common_actions = array( 'init', 'wp_loaded', 'admin_init', 'wp_head', 'wp_footer', 'shutdown' );
			if ( in_array( $hook_name, $common_actions, true ) ) {
				return 'action';
			}
			return 'filter';
		}

		// Default to filter
		return 'filter';
	}

	/**
	 * Determine source of hook (core/theme/plugin).
	 *
	 * @param string $hook_name Hook name.
	 * @return string 'core', 'theme', or plugin slug
	 */
	private function determine_source( string $hook_name ): string {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook_name ] ) ) {
			return 'core';
		}

		$hook_obj = $wp_filter[ $hook_name ];
		if ( ! is_object( $hook_obj ) || ! isset( $hook_obj->callbacks ) ) {
			return 'core';
		}

		// Check callbacks to determine primary source
		$sources = array();
		foreach ( $hook_obj->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback_data ) {
				$callback = $callback_data['function'] ?? null;
				if ( ! $callback ) {
					continue;
				}

				$metadata = \HookTrace\Inspector\CallbackInspector::inspect( $callback );
				$source = $metadata['plugin'] ?? 'core';
				if ( ! isset( $sources[ $source ] ) ) {
					$sources[ $source ] = 0;
				}
				$sources[ $source ]++;
			}
		}

		// Return most common source, or 'core' if empty
		if ( empty( $sources ) ) {
			return 'core';
		}

		arsort( $sources );
		return array_key_first( $sources );
	}
}

