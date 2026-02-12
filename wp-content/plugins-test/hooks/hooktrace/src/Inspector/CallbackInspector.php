<?php
/**
 * Resolves callback metadata using reflection.
 *
 * @package HookTrace\Inspector
 */

namespace HookTrace\Inspector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ReflectionFunction;
use ReflectionMethod;
use ReflectionException;

/**
 * Inspects callbacks to extract file, line, and plugin information.
 */
class CallbackInspector {

	/**
	 * Cache for reflection results per request.
	 *
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Resolve callback metadata.
	 *
	 * @param callable $callback The callback to inspect.
	 * @param int      $backtrace_limit Limit backtrace depth.
	 * @return array {
	 *     @type string $file File path
	 *     @type int    $line Line number
	 *     @type string $type 'function', 'method', 'closure', 'static'
	 *     @type string $name Callback name
	 *     @type string $class Class name if method
	 *     @type string $plugin Plugin slug or 'core' or 'theme'
	 * }
	 */
	public static function inspect( $callback, int $backtrace_limit = 3 ): array {
		$cache_key = self::get_cache_key( $callback );
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$result = array(
			'file'   => '',
			'line'   => 0,
			'type'   => 'unknown',
			'name'   => '',
			'class'  => '',
			'plugin' => 'unknown',
		);

		try {
			if ( is_string( $callback ) ) {
				// Function name
				if ( function_exists( $callback ) ) {
					$reflection = new ReflectionFunction( $callback );
					$result['type'] = 'function';
					$result['name'] = $callback;
					$result['file'] = $reflection->getFileName();
					$result['line'] = $reflection->getStartLine();
				}
			} elseif ( is_array( $callback ) ) {
				// Array callable: [object, method] or [class, method]
				if ( count( $callback ) === 2 ) {
					list( $object_or_class, $method ) = $callback;
					$reflection = new ReflectionMethod( $object_or_class, $method );
					$result['file'] = $reflection->getFileName();
					$result['line'] = $reflection->getStartLine();
					$result['name'] = $method;
					$result['class'] = is_object( $object_or_class ) ? get_class( $object_or_class ) : $object_or_class;
					$result['type'] = $reflection->isStatic() ? 'static' : 'method';
				}
			} elseif ( is_object( $callback ) ) {
				// Closure or invokable object
				if ( $callback instanceof \Closure ) {
					$reflection = new ReflectionFunction( $callback );
					$result['type'] = 'closure';
					$result['name'] = '{closure}';
					$result['file'] = $reflection->getFileName();
					$result['line'] = $reflection->getStartLine();
				} elseif ( method_exists( $callback, '__invoke' ) ) {
					$reflection = new ReflectionMethod( $callback, '__invoke' );
					$result['type'] = 'invokable';
					$result['name'] = get_class( $callback ) . '::__invoke';
					$result['class'] = get_class( $callback );
					$result['file'] = $reflection->getFileName();
					$result['line'] = $reflection->getStartLine();
				}
			}
		} catch ( ReflectionException $e ) {
			// Reflection failed, try backtrace
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace_limit );
			if ( ! empty( $backtrace[1] ) ) {
				$result['file'] = $backtrace[1]['file'] ?? '';
				$result['line'] = $backtrace[1]['line'] ?? 0;
			}
		}

		// Resolve plugin/theme/core from file path
		if ( ! empty( $result['file'] ) ) {
			$result['plugin'] = self::resolve_source( $result['file'] );
		}

		self::$cache[ $cache_key ] = $result;
		return $result;
	}

	/**
	 * Get cache key for callback.
	 *
	 * @param callable $callback The callback.
	 * @return string
	 */
	private static function get_cache_key( $callback ): string {
		if ( is_string( $callback ) ) {
			return 'str:' . $callback;
		}
		if ( is_array( $callback ) ) {
			$key = 'arr:';
			$key .= is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			$key .= '::' . $callback[1];
			return $key;
		}
		if ( $callback instanceof \Closure ) {
			return 'closure:' . spl_object_hash( $callback );
		}
		return 'unknown:' . spl_object_hash( (object) $callback );
	}

	/**
	 * Resolve plugin/theme/core from file path.
	 *
	 * @param string $file_path File path.
	 * @return string Plugin slug, 'theme', or 'core'
	 */
	private static function resolve_source( string $file_path ): string {
		// Normalize path
		$file_path = wp_normalize_path( $file_path );

		// Check if it's WordPress core
		if ( strpos( $file_path, wp_normalize_path( ABSPATH . WPINC ) ) === 0 ) {
			return 'core';
		}

		// Check if it's in wp-admin
		if ( strpos( $file_path, wp_normalize_path( ABSPATH . 'wp-admin' ) ) === 0 ) {
			return 'core';
		}

		// Check if it's in active theme
		$theme_dir = wp_normalize_path( get_template_directory() );
		if ( strpos( $file_path, $theme_dir ) === 0 ) {
			return 'theme';
		}

		// Check if it's in child theme
		if ( is_child_theme() ) {
			$child_theme_dir = wp_normalize_path( get_stylesheet_directory() );
			if ( strpos( $file_path, $child_theme_dir ) === 0 ) {
				return 'theme';
			}
		}

		// Check if it's a plugin
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );
		if ( strpos( $file_path, $plugin_dir ) === 0 ) {
			$relative_path = str_replace( $plugin_dir . '/', '', $file_path );
			$parts = explode( '/', $relative_path );
			if ( ! empty( $parts[0] ) ) {
				return $parts[0];
			}
		}

		// Check if it's an MU plugin
		$mu_plugin_dir = wp_normalize_path( WPMU_PLUGIN_DIR );
		if ( strpos( $file_path, $mu_plugin_dir ) === 0 ) {
			return 'mu-plugin';
		}

		return 'unknown';
	}
}

