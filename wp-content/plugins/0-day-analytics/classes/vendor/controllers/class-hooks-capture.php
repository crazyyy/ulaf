<?php
/**
 * Hooks Capture Controller - captures and logs WordPress hooks.
 *
 * @package 0-day-analytics
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

use ADVAN\Entities\Hooks_Capture_Entity;
use ADVAN\Entities\Hooks_Management_Entity;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\File_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Controllers\Hooks_Capture' ) ) {
	/**
	 * Responsible for capturing WordPress hooks.
	 *
	 * @since 4.5.0
	 */
	class Hooks_Capture {

		/**
		 * Maximum depth for capturing nested hooks.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_CAPTURE_DEPTH = 3;

		/**
		 * Maximum number of hook logs to store in memory before forcing a commit.
		 *
		 * @var int
		 *
		 * @since 4.6.1
		 */
		private const MAX_MEMORY_LOGS = 1000;

		/**
		 * Maximum size for parameter/output JSON strings (64KB).
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_JSON_SIZE = 65536;

		/**
		 * Maximum length for string parameters before truncation.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_STRING_LENGTH = 255;

		/**
		 * Maximum depth for recursive array/object sanitization.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_SANITIZE_DEPTH = 2;

		/**
		 * Maximum number of backtrace frames to capture.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_BACKTRACE_FRAMES = 3;

		/**
		 * Maximum number of properties to capture from objects.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private const MAX_OBJECT_PROPERTIES = 50;

		/**
		 * Maximum length for hook names.
		 *
		 * @var int
		 *
		 * @since 4.6.1
		 */
		private const MAX_HOOK_NAME_LENGTH = 255;

		/**
		 * Array of hooks currently being captured to prevent infinite loops.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		private static $capturing_hooks = array();

		/**
		 * Maximum depth for capturing nested hooks.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private static $max_depth = 3;

		/**
		 * Current capture depth.
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		private static $current_depth = 0;

		/**
		 * Cache of enabled hooks.
		 *
		 * @var array|null
		 *
		 * @since 4.5.0
		 */
		private static $enabled_hooks = null;

		/**
		 * Cache file path.
		 *
		 * @var string|null
		 *
		 * @since 4.5.0
		 */
		private static $cache_file_path = null;

		/**
		 * Cache directory path.
		 *
		 * @var string|null
		 *
		 * @since 4.5.0
		 */
		private static $cache_dir_path = null;

		/**
		 * Unique request ID for grouping hook calls per request.
		 *
		 * @var string|null
		 *
		 * @since 4.5.0
		 */
		private static $request_id = null;

		/**
		 * In-memory storage for hook logs to deduplicate per request.
		 *
		 * @var array
		 *
		 * @since 4.6.0
		 */
		private static $hook_logs = array();

		/**
		 * Inits the class.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function init() {
			if ( ! Settings::get_option( 'hooks_capture_module_enabled' ) ) {
				return;
			}

			// Generate unique request ID for this execution.
			self::$request_id = uniqid( 'req_', true );

			// self::debug_log( 'Initializing hooks capture', array( 'request_id' => self::$request_id ) );

			// In WP-CLI context, ensure hooks are attached properly.
			if ( defined( 'WP_CLI' ) && \WP_CLI ) {
				self::attach_hooks_cli();
			} else {
				// Attach hooks as early as possible to catch wp_login and other early hooks.
				self::attach_hooks();
			}

			// Clear cache when hooks are modified.
			\add_action( 'advan_hooks_management_updated', array( __CLASS__, 'clear_cache' ) );

			// Re-attach hooks after cache clear to pick up changes.
			\add_action( 'advan_hooks_management_updated', array( __CLASS__, 'detach_and_reattach_hooks' ) );

			// Commit hook logs at the end of the request.
			\add_action( 'shutdown', array( __CLASS__, 'commit_hook_logs' ) );

			// =======================================================================
			// MEMORY POOL: Initialize memory pool for reusing structures
			// =======================================================================
			self::init_memory_pool();

			// =======================================================================
			// ERROR RECOVERY: Setup error recovery for serialization failures
			// =======================================================================
			\add_filter( 'advan_serialize_hook_data', array( __CLASS__, 'safe_json_encode' ), 10, 2 );
			\add_filter( 'advan_unserialize_hook_data', array( __CLASS__, 'safe_json_decode' ), 10, 2 );

			// Cleanup memory pool on shutdown.
			\add_action( 'shutdown', array( __CLASS__, 'cleanup_memory_pool' ), 999 );
		}

		/**
		 * Clear enabled hooks cache and regenerate cache file.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function clear_cache() {
			self::$enabled_hooks = null;
			\wp_cache_delete( 'advan_enabled_hooks', 'advan' );

			// Regenerate cache file with latest hooks configuration.
			// Defer regeneration if WordPress isn't fully loaded yet.
			if ( ! \did_action( 'init' ) ) {
				\add_action( 'init', array( __CLASS__, 'regenerate_cache_file' ), 1 );
			} else {
				self::regenerate_cache_file();
			}
		}

		/**
		 * Detach all existing hooks and reattach based on current configuration.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function detach_and_reattach_hooks() {
			// Detach existing hooks first.
			if ( ! empty( self::$enabled_hooks ) && is_array( self::$enabled_hooks ) ) {
				foreach ( self::$enabled_hooks as $hook_config ) {
					if ( empty( $hook_config['hook_name'] ) ) {
						continue;
					}

					$hook_name = $hook_config['hook_name'];
					$priority  = isset( $hook_config['priority'] ) ? (int) $hook_config['priority'] : 10;
					$hook_type = isset( $hook_config['hook_type'] ) ? $hook_config['hook_type'] : 'action';

					if ( 'action' === $hook_type ) {
						\remove_action( $hook_name, array( __CLASS__, 'capture_action' ), $priority );
					} else {
						\remove_filter( $hook_name, array( __CLASS__, 'capture_filter' ), $priority );
					}
				}
			}

			// Clear the cache and reattach.
			self::clear_cache();
			self::attach_hooks();
		}

		/**
		 * Attach hooks to be captured in WP-CLI context.
		 * Forces database loading to ensure hooks work in CLI environment.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function attach_hooks_cli() {
			// In CLI context, always load from DB to ensure hooks are attached.
			self::$enabled_hooks = Hooks_Management_Entity::get_enabled_hooks();

			if ( empty( self::$enabled_hooks ) ) {
				return;
			}

			// Attach monitoring to each enabled hook.
			foreach ( self::$enabled_hooks as $hook_config ) {
				if ( empty( $hook_config['hook_name'] ) || ! self::is_valid_hook_name( $hook_config['hook_name'] ) ) {
					continue;
				}

				$hook_name = $hook_config['hook_name'];
				$priority  = isset( $hook_config['priority'] ) ? (int) $hook_config['priority'] : 10;
				$hook_type = isset( $hook_config['hook_type'] ) ? $hook_config['hook_type'] : 'action';

				// Use a high number of accepted args to capture all parameters.
				if ( 'action' === $hook_type ) {
					\add_action( $hook_name, array( __CLASS__, 'capture_action' ), $priority, self::get_accepted_args() );
				} else {
					\add_filter( $hook_name, array( __CLASS__, 'capture_filter' ), $priority, self::get_accepted_args() );
				}
			}
		}

		/**
		 * Attach hooks to be captured.
		 * Uses cache file if available, falls back to DB queries.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function attach_hooks() {
			// Try to load from cache file first.
			$cache_file = self::get_cache_file_path();

			if ( $cache_file && file_exists( $cache_file ) && is_readable( $cache_file ) ) {
				// Include the cache file which registers all hooks.
				// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
				@include_once $cache_file;

				// Load hook data for runtime (still needed for get_hook_config).
				self::$enabled_hooks = Hooks_Management_Entity::get_enabled_hooks();
				return;
			}

			// Fallback: Load from DB and register hooks dynamically.
			self::$enabled_hooks = Hooks_Management_Entity::get_enabled_hooks();

			if ( empty( self::$enabled_hooks ) ) {
				return;
			}

			// Attach monitoring to each enabled hook.
			foreach ( self::$enabled_hooks as $hook_config ) {
				if ( empty( $hook_config['hook_name'] ) || ! self::is_valid_hook_name( $hook_config['hook_name'] ) ) {
					continue;
				}

				$hook_name = $hook_config['hook_name'];
				$priority  = isset( $hook_config['priority'] ) ? (int) $hook_config['priority'] : 10;
				$hook_type = isset( $hook_config['hook_type'] ) ? $hook_config['hook_type'] : 'action';

				// Use a high number of accepted args to capture all parameters.
				if ( 'action' === $hook_type ) {
					\add_action( $hook_name, array( __CLASS__, 'capture_action' ), $priority, self::get_accepted_args() );
				} else {
					\add_filter( $hook_name, array( __CLASS__, 'capture_filter' ), $priority, self::get_accepted_args() );
				}
			}
		}

		/**
		 * Capture action hook execution.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function capture_action() {
			$hook_name = \current_filter();
			$args      = \func_get_args();

			self::log_hook( $hook_name, 'action', $args, null );
		}

		/**
		 * Capture filter hook execution.
		 *
		 * @param mixed ...$args Filter arguments.
		 *
		 * @return mixed First argument (for filter chain).
		 *
		 * @since 4.5.0
		 */
		public static function capture_filter( ...$args ) {
			$hook_name = \current_filter();
			$value     = isset( $args[0] ) ? $args[0] : null;

			self::log_hook( $hook_name, 'filter', $args, $value );

			return $value;
		}

		/**
		 * Log hook execution.
		 *
		 * @param string $hook_name Hook name.
		 * @param string $hook_type Hook type (action/filter).
		 * @param array  $args Hook arguments.
		 * @param mixed  $output Hook output/return value.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function log_hook( string $hook_name, string $hook_type, array $args, $output ) {
			// Validate hook name for security.
			if ( ! self::is_valid_hook_name( $hook_name ) ) {
				return;
			}

			// =======================================================================
			// EARLY FILTERING: Filter out unwanted hooks before processing
			// =======================================================================
			if ( ! self::should_capture_hook_early( $hook_name ) ) {
				return;
			}

			// =======================================================================
			// SAMPLING: Apply sampling for high-frequency hooks to reduce storage
			// =======================================================================
			if ( ! self::should_sample_hook( $hook_name ) ) {
				return;
			}

			// Prevent infinite loops.
			if ( isset( self::$capturing_hooks[ $hook_name ] ) ) {
				return;
			}

			// Prevent excessive nesting.
			if ( self::$current_depth >= self::MAX_CAPTURE_DEPTH ) {
				return;
			}

			// Skip our own hooks to prevent recursion.
			if ( 0 === strpos( $hook_name, 'advan_' ) || 0 === strpos( $hook_name, 'wpdb_' ) ) {
				return;
			}

			self::$capturing_hooks[ $hook_name ] = true;
			++self::$current_depth;

			$start_time   = microtime( true );
			$start_memory = memory_get_usage();

			try {
				$hook_config = self::get_hook_config( $hook_name );

				if ( ! $hook_config ) {
					return;
				}

				$capture_args   = ! empty( $hook_config['capture_args'] );
				$capture_output = ! empty( $hook_config['capture_output'] );

				// Get trigger source.
				$trigger_source = self::detect_trigger_source();

				// Get user info.
				$user_id    = \get_current_user_id();
				$user_login = '';
				if ( $user_id > 0 ) {
					$user       = \get_userdata( $user_id );
					$user_login = $user ? $user->user_login : '';
				}

				// Capture parameters (with size limit for performance).
				$parameters_json = '';
				if ( $capture_args && ! empty( $args ) ) {
					$sanitized_args  = self::sanitize_args( $args, $hook_name );
					$parameters_json = \wp_json_encode( $sanitized_args );

					// Limit parameter size (max 64KB).
					if ( strlen( $parameters_json ) > self::MAX_JSON_SIZE ) {
						$parameters_json = substr( $parameters_json, 0, self::MAX_JSON_SIZE ) . '... [truncated]';
					}
				}

				// Capture output (with size limit).
				$output_json = '';
				if ( $capture_output && null !== $output ) {
					$output_json = \wp_json_encode( self::sanitize_args( array( $output ) ) );

					// Limit output size (max 64KB).
					if ( strlen( $output_json ) > self::MAX_JSON_SIZE ) {
						$output_json = substr( $output_json, 0, self::MAX_JSON_SIZE ) . '... [truncated]';
					}
				}

				// Get simplified backtrace (limited depth for performance).
				$backtrace = self::get_backtrace();

				$execution_time = microtime( true ) - $start_time;
				$memory_usage   = memory_get_usage() - $start_memory;

				// Collect performance metrics for monitoring.
				self::collect_performance_metrics( $execution_time, $memory_usage, count( self::$hook_logs ) );

				// Create unique key for deduplication based on hook name and args.
				// Use optimized key generation for better performance.
				$key = self::generate_deduplication_key( $hook_name, $args );

				if ( ! isset( self::$hook_logs[ $key ] ) ) {
					// Check if we've exceeded memory limits and force a commit if needed.
					if ( count( self::$hook_logs ) >= self::MAX_MEMORY_LOGS ) {
						self::commit_hook_logs();
					}

					// Prepare log data.
					$log_data = self::prepare_hook_log_data( $hook_name, $hook_type, $args, $output, $capture_args, $capture_output, $trigger_source, $user_id, $user_login, $backtrace, $execution_time, $memory_usage );

					// Store the log data in memory.
					self::$hook_logs[ $key ] = $log_data;
				} else {
					// Increment count for duplicate hook calls.
					++self::$hook_logs[ $key ]['count'];
				}
			} finally {
				--self::$current_depth;
				unset( self::$capturing_hooks[ $hook_name ] );
			}
		}

		/**
		 * Get hook configuration.
		 *
		 * @param string $hook_name Hook name.
		 *
		 * @return array|null
		 *
		 * @since 4.5.0
		 */
		private static function get_hook_config( string $hook_name ) {
			if ( null === self::$enabled_hooks ) {
				self::$enabled_hooks = Hooks_Management_Entity::get_enabled_hooks();
			}

			foreach ( self::$enabled_hooks as $config ) {
				if ( $config['hook_name'] === $hook_name ) {
					return $config;
				}
			}

			return null;
		}

		/**
		 * Detect the trigger source (core, user, cron, cli, ajax, rest).
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		private static function detect_trigger_source(): string {
			if ( self::is_cli() ) {
				return 'cli';
			}

			if ( \wp_doing_cron() ) {
				return 'cron';
			}

			if ( \wp_doing_ajax() ) {
				return 'ajax';
			}

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return 'rest';
			}

			if ( \is_admin() ) {
				return 'admin';
			}

			if ( \get_current_user_id() > 0 ) {
				return 'user';
			}

			return 'frontend';
		}

		/**
		 * Check if running in WP-CLI.
		 *
		 * @return bool
		 *
		 * @since 4.5.0
		 */
		private static function is_cli(): bool {
			return defined( 'WP_CLI' ) && WP_CLI;
		}

		/**
		 * Get simplified backtrace for hook source identification.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		private static function get_backtrace(): array {
			// Use Exception backtrace for better performance (faster than debug_backtrace).
			$trace = ( new \Exception( '' ) )->getTrace();

			$simplified = array();

			foreach ( $trace as $frame ) {
				// Skip our own frames.
				if ( isset( $frame['class'] ) && __CLASS__ === $frame['class'] ) {
					continue;
				}

				$simplified[] = array(
					'file'      => isset( $frame['file'] ) ? \basename( $frame['file'] ) : '',
					'full_path' => isset( $frame['file'] ) ? $frame['file'] : '',
					'line'      => isset( $frame['line'] ) ? $frame['line'] : 0,
					'function'  => isset( $frame['function'] ) ? $frame['function'] : '',
					'class'     => isset( $frame['class'] ) ? $frame['class'] : '',
				);

				// Limit to configured number of frames for performance.
				if ( count( $simplified ) >= self::MAX_BACKTRACE_FRAMES ) {
					break;
				}
			}

			return $simplified;
		}

		/**
		 * Generate optimized deduplication key for hook calls.
		 *
		 * @param string $hook_name Hook name.
		 * @param array  $args Hook arguments.
		 *
		 * @return string Deduplication key.
		 *
		 * @since 4.6.1
		 */
		private static function generate_deduplication_key( string $hook_name, array $args ): string {
			// For performance, use a simplified approach for common cases.
			$arg_signature = '';

			// Limit to first few arguments to avoid expensive serialization.
			$max_args  = 3;
			$arg_count = 0;

			foreach ( $args as $arg ) {
				if ( $arg_count >= $max_args ) {
					break;
				}

				if ( is_scalar( $arg ) ) {
					$arg_signature .= (string) $arg . '|';
				} elseif ( is_array( $arg ) ) {
					$arg_signature .= 'array(' . count( $arg ) . ')|';
				} elseif ( is_object( $arg ) ) {
					$arg_signature .= 'object(' . get_class( $arg ) . ')|';
				} else {
					$arg_signature .= gettype( $arg ) . '|';
				}

				++$arg_count;
			}

			// Add count of remaining args if any.
			if ( count( $args ) > $max_args ) {
				$arg_signature .= '+' . ( count( $args ) - $max_args ) . 'more';
			}

			return $hook_name . '_' . md5( $arg_signature );
		}

		/**
		 * Get module health status.
		 *
		 * @return array Health status information.
		 *
		 * @since 4.6.1
		 */
		public static function get_health_status(): array {
			return self::health_check();
		}

		/**
		 * Force commit of pending hook logs.
		 *
		 * @return bool True on success.
		 *
		 * @since 4.6.1
		 */
		public static function force_commit(): bool {
			if ( empty( self::$hook_logs ) ) {
				return true;
			}

			self::commit_hook_logs();
			return empty( self::$hook_logs );
		}

		/**
		 * Get current performance metrics.
		 *
		 * @return array Performance metrics.
		 *
		 * @since 4.6.1
		 */
		public static function get_performance_metrics(): array {
			return array(
				'memory_usage'  => memory_get_usage( true ),
				'peak_memory'   => memory_get_peak_usage( true ),
				'queued_logs'   => count( self::$hook_logs ),
				'max_logs'      => self::MAX_MEMORY_LOGS,
				'cache_enabled' => ! empty( self::get_cache_file_path() ),
				'request_id'    => self::$request_id,
			);
		}

		/**
		 * Perform health check for the hooks capture module.
		 *
		 * @return array Health check results.
		 *
		 * @since 4.6.1
		 */
		public static function health_check(): array {
			$health = array(
				'status'    => 'healthy',
				'issues'    => array(),
				'metrics'   => array(),
				'timestamp' => time(),
			);

			// Check memory usage.
			$memory_usage = memory_get_usage( true );
			$memory_limit = self::get_memory_limit_bytes();

			if ( $memory_limit > 0 && $memory_usage > $memory_limit * 0.8 ) {
				$health['issues'][] = 'High memory usage detected';
				$health['status']   = 'warning';
			}

			$health['metrics']['memory_usage'] = $memory_usage;
			$health['metrics']['memory_limit'] = $memory_limit;

			// Check hook logs count.
			$log_count                        = count( self::$hook_logs );
			$health['metrics']['queued_logs'] = $log_count;

			if ( $log_count > self::MAX_MEMORY_LOGS * 0.9 ) {
				$health['issues'][] = 'Approaching memory log limit';
				$health['status']   = 'warning';
			}

			// Check cache file status.
			$cache_file = self::get_cache_file_path();
			if ( $cache_file ) {
				$health['metrics']['cache_file_exists']   = file_exists( $cache_file );
				$health['metrics']['cache_file_readable'] = is_readable( $cache_file );

				if ( file_exists( $cache_file ) && is_readable( $cache_file ) ) {
					$cache_content                         = file_get_contents( $cache_file );
					$health['metrics']['cache_file_valid'] = self::is_valid_cache_content( $cache_content );
				}
			}

			// Check database connectivity.
			try {
				$test_query                              = Hooks_Capture_Entity::load( '1=0' ); // Should return empty array.
				$health['metrics']['database_connected'] = true;
			} catch ( \Exception $e ) {
				$health['issues'][]                      = 'Database connectivity issue: ' . $e->getMessage();
				$health['status']                        = 'error';
				$health['metrics']['database_connected'] = false;
			}

			return $health;
		}

		/**
		 * Get memory limit in bytes.
		 *
		 * @return int Memory limit in bytes, or 0 if unlimited.
		 *
		 * @since 4.6.1
		 */
		private static function get_memory_limit_bytes(): int {
			$memory_limit = ini_get( 'memory_limit' );

			if ( empty( $memory_limit ) || $memory_limit === '-1' ) {
				return 0; // Unlimited.
			}

			$unit  = strtolower( substr( $memory_limit, -1 ) );
			$value = (int) substr( $memory_limit, 0, -1 );

			switch ( $unit ) {
				case 'g':
					return $value * 1024 * 1024 * 1024;
				case 'm':
					return $value * 1024 * 1024;
				case 'k':
					return $value * 1024;
				default:
					return (int) $memory_limit;
			}
		}

		/**
		 * Prepare hook log data for storage.
		 *
		 * @param string $hook_name Hook name.
		 * @param string $hook_type Hook type.
		 * @param array  $args Hook arguments.
		 * @param mixed  $output Hook output.
		 * @param bool   $capture_args Whether to capture arguments.
		 * @param bool   $capture_output Whether to capture output.
		 * @param string $trigger_source Trigger source.
		 * @param int    $user_id User ID.
		 * @param string $user_login User login.
		 * @param array  $backtrace Backtrace data.
		 * @param float  $execution_time Execution time.
		 * @param int    $memory_usage Memory usage.
		 *
		 * @return array Prepared log data.
		 *
		 * @since 4.6.1
		 */
		private static function prepare_hook_log_data( string $hook_name, string $hook_type, array $args, $output, bool $capture_args, bool $capture_output, string $trigger_source, int $user_id, string $user_login, array $backtrace, float $execution_time, int $memory_usage ): array {
			// Capture parameters (with size limit for performance).
			$parameters_json = '';
			if ( $capture_args && ! empty( $args ) ) {
				$sanitized_args  = self::sanitize_args( $args, $hook_name );
				$parameters_json = \wp_json_encode( $sanitized_args );

				// Limit parameter size (max 64KB).
				if ( strlen( $parameters_json ) > self::MAX_JSON_SIZE ) {
					$parameters_json = substr( $parameters_json, 0, self::MAX_JSON_SIZE ) . '... [truncated]';
				}
			}

			// Capture output (with size limit).
			$output_json = '';
			if ( $capture_output && null !== $output ) {
				$output_json = \wp_json_encode( self::sanitize_args( array( $output ) ) );

				// Limit output size (max 64KB).
				if ( strlen( $output_json ) > self::MAX_JSON_SIZE ) {
					$output_json = substr( $output_json, 0, self::MAX_JSON_SIZE ) . '... [truncated]';
				}
			}

			return array(
				'blog_id'             => \is_multisite() ? \get_current_blog_id() : 0,
				'user_id'             => $user_id,
				'user_login'          => $user_login,
				'trigger_source'      => $trigger_source,
				'hook_name'           => $hook_name,
				'hook_type'           => $hook_type,
				'parameters'          => $parameters_json,
				'output'              => $output_json,
				'backtrace'           => \wp_json_encode( $backtrace ),
				'execution_time'      => $execution_time,
				'memory_usage'        => $memory_usage,
				'is_cli'              => (int) self::is_cli(),
				'hooks_management_id' => self::get_hook_management_id( $hook_name ),
				'count'               => 1,
				'date_added'          => microtime( true ),
			);
		}

		/**
		 * Check if a key contains sensitive data that should be masked.
		 *
		 * @param string $key The array key to check.
		 *
		 * @return bool True if the key indicates sensitive data.
		 *
		 * @since 4.5.0
		 */
		private static function is_sensitive_key( string $key ): bool {
			$sensitive_patterns = array(
				'password',
				'pwd',
				'pass',
				'passwd',
				'secret',
				'key',
				'token',
				'auth',
				'credential',
				'api_key',
				'access_token',
				'refresh_token',
				'private_key',
				'secret_key',
				'client_secret',
				'session_key',
				'encryption_key',
				'hash',
				'salt',
				'nonce',
				'card',
				'cc_number',
				'credit_card',
				'ssn',
				'social_security',
				'pin',
				'cvv',
				'expiry',
				'security_code',
			);

			$key_lower = strtolower( $key );

			foreach ( $sensitive_patterns as $pattern ) {
				if ( strpos( $key_lower, $pattern ) !== false ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Handle special cases for hooks with sensitive positional parameters.
		 *
		 * @param array  $args Hook arguments.
		 * @param string $hook_name The hook name.
		 *
		 * @return array Modified arguments with sensitive data masked.
		 *
		 * @since 4.5.0
		 */
		private static function handle_special_hook_cases( array $args, string $hook_name ): array {
			switch ( $hook_name ) {
				case 'authenticate':
					// authenticate filter: $user, $username, $password.
					if ( isset( $args[2] ) ) {
						$args[2] = '[REDACTED - Password]';
					}
					break;
				case 'wp_authenticate':
					// wp_authenticate action: $username, $password.
					if ( isset( $args[1] ) ) {
						$args[1] = '[REDACTED - Password]';
					}
					break;
				case 'wp_login':
					// wp_login action: $user_login, $user
					// Password is not passed to this hook, so no action needed.
					break;
				case 'wp_set_auth_cookie':
					// wp_set_auth_cookie action: $auth_cookie, $expire, $expiration, $user_id, $scheme, $token
					// The auth_cookie and token might be sensitive.
					if ( isset( $args[0] ) ) {
						$args[0] = '[REDACTED - Auth Cookie]';
					}
					if ( isset( $args[5] ) ) {
						$args[5] = '[REDACTED - Session Token]';
					}
					break;
			}

			return $args;
		}

		/**
		 * Sanitize arguments for safe storage.
		 *
		 * @param array  $args Arguments to sanitize.
		 * @param string $hook_name Optional hook name for special handling.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		private static function sanitize_args( array $args, string $hook_name = '' ): array {
			// Handle special cases for known hooks with sensitive positional parameters.
			if ( ! empty( $hook_name ) ) {
				$args = self::handle_special_hook_cases( $args, $hook_name );
			}

			$sanitized = array();

			foreach ( $args as $key => $value ) {
				if ( is_scalar( $value ) || is_null( $value ) ) {
					// Check for sensitive data first.
					if ( self::is_sensitive_key( (string) $key ) ) {
						$sanitized[ $key ] = '[REDACTED - Sensitive Data]';
					} elseif ( is_string( $value ) && mb_strlen( $value ) > self::MAX_STRING_LENGTH ) {
						$sanitized[ $key ] = mb_substr( $value, 0, self::MAX_STRING_LENGTH ) . '... (truncated)';
					} else {
						$sanitized[ $key ] = $value;
					}
				} elseif ( is_array( $value ) ) {
					// Limit array depth.
					$sanitized[ $key ] = self::sanitize_args_recursive( $value, 1 );
				} elseif ( is_object( $value ) ) {
					$sanitized[ $key ] = self::normalize_object( $value, 1 );
				} elseif ( is_resource( $value ) ) {
					$sanitized[ $key ] = 'resource';
				} else {
					$sanitized[ $key ] = \gettype( $value );
				}
			}

			return $sanitized;
		}

		/**
		 * Recursively sanitize arguments with depth limit.
		 *
		 * @param array $args Arguments to sanitize.
		 * @param int   $depth Current depth.
		 *
		 * @return array|string
		 *
		 * @since 4.5.0
		 */
		private static function sanitize_args_recursive( array $args, int $depth ) {
			if ( $depth > self::MAX_SANITIZE_DEPTH ) {
				return '[nested array]';
			}

			$sanitized = array();

			foreach ( $args as $key => $value ) {
				if ( is_scalar( $value ) || is_null( $value ) ) {
					// Check for sensitive data first.
					if ( self::is_sensitive_key( (string) $key ) ) {
						$sanitized[ $key ] = '[REDACTED - Sensitive Data]';
					} elseif ( is_string( $value ) && mb_strlen( $value ) > self::MAX_STRING_LENGTH ) {
						$sanitized[ $key ] = mb_substr( $value, 0, self::MAX_STRING_LENGTH ) . '... (truncated)';
					} else {
						$sanitized[ $key ] = $value;
					}
				} elseif ( is_array( $value ) ) {
					$sanitized[ $key ] = self::sanitize_args_recursive( $value, $depth + 1 );
				} elseif ( is_object( $value ) ) {
					$sanitized[ $key ] = self::normalize_object( $value, $depth + 1 );
				} else {
					$sanitized[ $key ] = \gettype( $value );
				}
			}

			return $sanitized;
		}

		/**
		 * Normalize an object to an array with properties and class marker.
		 *
		 * @param object $object Object to normalize.
		 * @param int    $depth Current depth.
		 *
		 * @return array|string
		 *
		 * @since 4.5.0
		 */
		private static function normalize_object( $object, int $depth ) {
			if ( $depth > self::MAX_SANITIZE_DEPTH ) {
				return '[nested object]';
			}

			// Get class name.
			$class_name = \get_class( $object );

			// Start with class marker.
			$normalized = array( '__class__' => $class_name );

			// Try to get object properties.
			$properties = array();

			// Check if object implements JsonSerializable.
			if ( $object instanceof \JsonSerializable ) {
				$properties = $object->jsonSerialize();
			} elseif ( \method_exists( $object, 'to_array' ) ) {
				$properties = $object->to_array();
			} elseif ( \method_exists( $object, 'toArray' ) ) {
				$properties = $object->toArray();
			} else {
				// Get public properties.
				$properties = \get_object_vars( $object );
			}

			// Limit to reasonable number of properties.
			if ( \count( $properties ) > self::MAX_OBJECT_PROPERTIES ) {
				$properties                  = \array_slice( $properties, 0, self::MAX_OBJECT_PROPERTIES, true );
				$normalized['__truncated__'] = true;
			}

			// Sanitize each property.
			foreach ( $properties as $key => $value ) {
				if ( is_scalar( $value ) || is_null( $value ) ) {
					// Check for sensitive data first.
					if ( self::is_sensitive_key( (string) $key ) ) {
						$normalized[ $key ] = '[REDACTED - Sensitive Data]';
					} elseif ( is_string( $value ) && mb_strlen( $value ) > self::MAX_STRING_LENGTH ) {
						$normalized[ $key ] = mb_substr( $value, 0, self::MAX_STRING_LENGTH ) . '... (truncated)';
					} else {
						$normalized[ $key ] = $value;
					}
				} elseif ( is_array( $value ) ) {
					$normalized[ $key ] = self::sanitize_args_recursive( $value, $depth + 1 );
				} elseif ( is_object( $value ) ) {
					// Nested object - just store class name to avoid deep recursion.
					$normalized[ $key ] = \get_class( $value );
				} else {
					$normalized[ $key ] = \gettype( $value );
				}
			}

			return $normalized;
		}

		/**
		 * Get cache directory path.
		 *
		 * @return string|null Cache directory path or null if unavailable.
		 *
		 * @since 4.5.0
		 */
		private static function get_cache_dir_path() {
			if ( null !== self::$cache_dir_path ) {
				return self::$cache_dir_path;
			}

			// Use uploads directory as base.
			$upload_dir = \wp_upload_dir();

			if ( ! empty( $upload_dir['error'] ) ) {
				return null;
			}

			if ( empty( $upload_dir['basedir'] ) || ! \wp_is_writable( $upload_dir['basedir'] ) ) {
				return null;
			}

			// Create subdirectory for our cache.
			self::$cache_dir_path = \trailingslashit( $upload_dir['basedir'] ) . 'advan-hooks-cache';

			return self::$cache_dir_path;
		}

		/**
		 * Get cache file path.
		 *
		 * @return string|null Cache file path or null if unavailable.
		 *
		 * @since 4.5.0
		 */
		private static function get_cache_file_path() {
			if ( null !== self::$cache_file_path ) {
				return self::$cache_file_path;
			}

			$cache_dir = self::get_cache_dir_path();

			if ( ! $cache_dir ) {
				return null;
			}

			// Use blog ID for multisite compatibility.
			$blog_id               = \is_multisite() ? \get_current_blog_id() : 1;
			self::$cache_file_path = \trailingslashit( $cache_dir ) . 'hooks-' . $blog_id . '.php';

			return self::$cache_file_path;
		}

		/**
		 * Create and harden cache directory.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		private static function create_cache_directory() {
			$cache_dir = self::get_cache_dir_path();

			if ( ! $cache_dir ) {
				return false;
			}

			// Check if directory already exists.
			if ( is_dir( $cache_dir ) ) {
				return true;
			}

			// Create directory with restricted permissions.
			if ( ! function_exists( '\\wp_mkdir_p' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$created = @\wp_mkdir_p( $cache_dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			if ( ! $created ) {
				return false;
			}

			// Harden directory permissions.
			@chmod( $cache_dir, 0755 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_chmod

			// Add index.php to prevent directory listing.
			File_Helper::create_index_file( $cache_dir );

			// Add .htaccess for additional protection (Apache).
			File_Helper::create_htaccess_file( $cache_dir );

			return true;
		}

		/**
		 * Generate PHP cache file with all enabled hooks.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		private static function generate_cache_file() {
			$cache_file = self::get_cache_file_path();

			if ( ! $cache_file ) {
				return false;
			}

			// Ensure cache directory exists.
			if ( ! self::create_cache_directory() ) {
				return false;
			}

			// Get enabled hooks from database.
			$enabled_hooks = Hooks_Management_Entity::get_enabled_hooks();

			if ( empty( $enabled_hooks ) ) {
				// Create empty cache file to avoid repeated DB queries.
				$content  = self::generate_cache_file_header();
				$content .= "\n// No enabled hooks.\n";
				return File_Helper::write_to_file( $cache_file, $content, false );
			}

			// Generate PHP code.
			$content = self::generate_cache_file_header();

			$content .= "\n// Auto-generated hook registrations.\n";
			$content .= '// Total hooks: ' . count( $enabled_hooks ) . "\n\n";

			foreach ( $enabled_hooks as $hook_config ) {
				if ( empty( $hook_config['hook_name'] ) || ! self::is_valid_hook_name( $hook_config['hook_name'] ) ) {
					continue;
				}

				$hook_name = $hook_config['hook_name'];
				$priority  = isset( $hook_config['priority'] ) ? (int) $hook_config['priority'] : 10;
				$hook_type = isset( $hook_config['hook_type'] ) ? $hook_config['hook_type'] : 'action';

				// Escape hook name for safe inclusion in PHP code.
				$escaped_hook_name = \addslashes( $hook_name );
				$escaped_priority  = (int) $priority;

				$content .= "// Hook: {$escaped_hook_name}\n";

				if ( 'action' === $hook_type ) {
					$content .= "add_action( '{$escaped_hook_name}', array( '\\ADVAN\\Controllers\\Hooks_Capture', 'capture_action' ), {$escaped_priority}, " . self::get_accepted_args() . " );\n";
				} else {
					$content .= "add_filter( '{$escaped_hook_name}', array( '\\ADVAN\\Controllers\\Hooks_Capture', 'capture_filter' ), {$escaped_priority}, " . self::get_accepted_args() . " );\n";
				}

				$content .= "\n";
			}

			// Write cache file with restricted permissions.
			$result = File_Helper::write_to_file( $cache_file, $content, false );

			if ( $result ) {
				// Set restrictive permissions on cache file.
				@chmod( $cache_file, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_chmod
			}

			return $result;
		}

		/**
		 * Generate cache file header with security measures.
		 *
		 * @return string PHP file header.
		 *
		 * @since 4.5.0
		 */
		private static function generate_cache_file_header() {
			$timestamp = \current_time( 'mysql' );
			$blog_id   = \is_multisite() ? \get_current_blog_id() : 1;

			$header  = "<?php\n";
			$header .= "/**\n";
			$header .= " * Auto-generated hooks cache file.\n";
			$header .= " * \n";
			$header .= " * DO NOT EDIT THIS FILE MANUALLY!\n";
			$header .= " * \n";
			$header .= ' * This file is automatically generated by ' . ADVAN_NAME . ".\n";
			$header .= " * It contains optimized hook registrations loaded from cache.\n";
			$header .= " * \n";
			$header .= " * Generated: {$timestamp}\n";
			$header .= " * Blog ID: {$blog_id}\n";
			$header .= " * \n";
			$header .= ' * @package ' . ADVAN_TEXTDOMAIN . "\n";
			$header .= " * @since 4.5.0\n";
			$header .= " */\n\n";
			$header .= "// Exit if accessed directly.\n";
			$header .= "if ( ! defined( 'ABSPATH' ) ) {\n";
			$header .= "\texit;\n";
			$header .= "}\n\n";
			$header .= "// Verify this is loaded in correct context.\n";
			$header .= "if ( ! class_exists( '\\ADVAN\\Controllers\\Hooks_Capture' ) ) {\n";
			$header .= "\treturn;\n";
			$header .= "}\n";

			return $header;
		}

		/**
		 * Regenerate cache file with current hooks configuration.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		public static function regenerate_cache_file() {
			// Only regenerate in admin or CLI context for security.
			if ( ! \is_admin() && ! self::is_cli() ) {
				return false;
			}

			// Check user capabilities (only if WordPress is fully loaded and function is available).
			if ( ! self::is_cli() ) {
				// During early WordPress loading, skip capability checks to avoid fatal errors.
				if ( ! \did_action( 'init' ) || ! \function_exists( 'current_user_can' ) || ! \function_exists( 'wp_get_current_user' ) ) {
					return false;
				}

				if ( ! \current_user_can( 'manage_options' ) ) {
					return false;
				}
			}

			return self::generate_cache_file();
		}

		/**
		 * Get the number of arguments accepted by hook capture callbacks.
		 *
		 * @return int Number of accepted arguments.
		 *
		 * @since 4.5.0
		 */
		private static function get_accepted_args(): int {
			// Use a high number to capture all possible hook arguments.
			return 99;
		}

		/**
		 * Get the group_id for a hook name from hooks management.
		 *
		 * @param string $hook_name The hook name.
		 *
		 * @return int The group_id or 0 if not found.
		 *
		 * @since 4.5.0
		 */
		private static function get_hook_management_id( string $hook_name ): int {
			$results = Hooks_Management_Entity::load( 'hook_name=%s', array( $hook_name ) );
			if ( ! empty( $results ) ) {
				return (int) $results['id'];
			}

			return 0;
		}

		/**
		 * Maximum number of hook logs to store in memory before forcing a commit.
		 * This prevents excessive memory usage on long-running requests.
		 *
		 * @var int
		 *
		 * @since 4.6.1
		 */
		private static $max_memory_logs = 1000;

		/**
		 * Commit accumulated hook logs to the database at the end of the request.
		 *
		 * @return void
		 *
		 * @since 4.6.0
		 */
		public static function commit_hook_logs() {
			if ( empty( self::$hook_logs ) ) {
				// self::debug_log( 'No hook logs to commit' );
				return;
			}

			$log_count = count( self::$hook_logs );
			// self::debug_log( 'Committing hook logs', array( 'count' => $log_count, 'request_id' => self::$request_id ) );

			try {
				foreach ( self::$hook_logs as $log ) {
					$log_entry = array(
						'blog_id'             => $log['blog_id'],
						'user_id'             => $log['user_id'],
						'user_login'          => $log['user_login'],
						'trigger_source'      => $log['trigger_source'],
						'request_id'          => self::$request_id,
						'hook_name'           => $log['hook_name'],
						'hook_type'           => $log['hook_type'],
						'parameters'          => $log['parameters'],
						'output'              => $log['output'],
						'backtrace'           => $log['backtrace'],
						'execution_time'      => $log['execution_time'],
						'memory_usage'        => $log['memory_usage'],
						'is_cli'              => $log['is_cli'],
						'hooks_management_id' => $log['hooks_management_id'],
						'count'               => $log['count'],
						'date_added'          => $log['date_added'],
					);

					Hooks_Capture_Entity::insert( $log_entry );
				}
			} catch ( \Exception $e ) {
				// Log the error but don't let it break the request.
				self::debug_log( 'Failed to commit hook logs', array( 'error' => $e->getMessage() ) );
				if ( function_exists( 'error_log' ) ) {
					\error_log( 'Hooks Capture: Failed to commit logs - ' . $e->getMessage() );
				}
			} finally {
				// Always clear the logs after attempting to commit.
				self::$hook_logs = array();
			}
		}

		/**
		 * Validate hook name for security.
		 *
		 * @param string $hook_name The hook name to validate.
		 *
		 * @return bool True if valid, false otherwise.
		 *
		 * @since 4.6.1
		 */
		private static function is_valid_hook_name( string $hook_name ): bool {
			// Basic validation: not empty, reasonable length, no dangerous characters.
			if ( empty( $hook_name ) || strlen( $hook_name ) > self::MAX_HOOK_NAME_LENGTH ) {
				return false;
			}

			// Allow alphanumeric, underscores, hyphens, slashes, and dots.
			if ( ! preg_match( '/^[a-zA-Z0-9_\/\-\.]+$/', $hook_name ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Validate cache file content for security before evaluation.
		 *
		 * @param string $content Cache file content.
		 *
		 * @return bool True if content is safe to evaluate.
		 *
		 * @since 4.6.1
		 */
		private static function is_valid_cache_content( string $content ): bool {
			// Basic validation: check for expected PHP structure.
			if ( empty( $content ) ) {
				return false;
			}

			// Check for HTML content (indicates corrupted cache file).
			if ( preg_match( '/<html|<head|<body|<div|<p|<span/i', $content ) ) {
				self::debug_log( 'Cache content contains HTML - file is corrupted' );
				return false;
			}

			// Check for dangerous PHP functions that shouldn't be in cache.
			$dangerous_patterns = array(
				'/exec\(/i',
				'/system\(/i',
				'/shell_exec\(/i',
				'/passthru\(/i',
				'/eval\(/i',
				'/include\(/i',
				'/require\(/i',
				'/file_get_contents\(/i',
				'/fopen\(/i',
				'/\$\w+\s*\(/', // Variable function calls.
			);

			foreach ( $dangerous_patterns as $pattern ) {
				if ( preg_match( $pattern, $content ) ) {
					self::debug_log( 'Cache content contains dangerous pattern', array( 'pattern' => $pattern ) );
					return false;
				}
			}

			// Check that content contains expected PHP structure.
			if ( ! preg_match( '/^<\?php/', $content ) ) {
				self::debug_log( 'Cache content does not start with PHP opening tag' );
				return false;
			}

			// Check for ABSPATH check (security measure).
			if ( ! preg_match( '/defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/', $content ) ) {
				self::debug_log( 'Cache content missing ABSPATH security check' );
				return false;
			}

			// Check that it contains our class reference (ensures it's our cache file).
			if ( ! preg_match( '/ADVAN\\\\Controllers\\\\Hooks_Capture/', $content ) ) {
				self::debug_log( 'Cache content does not reference our class' );
				return false;
			}

			// Check that it contains hook registration calls.
			if ( ! preg_match( '/add_(?:action|filter)\s*\(/', $content ) ) {
				self::debug_log( 'Cache content does not contain hook registrations' );
				return false;
			}

			// Basic PHP syntax check - try to parse the content.
			try {
				// Remove PHP opening tag for tokenization.
				$code = preg_replace( '/^<\?php\s*/', '', $content );
				if ( false === $code ) {
					self::debug_log( 'Failed to prepare code for syntax check' );
					return false;
				}

				// Use token_get_all to check for basic syntax validity.
				$tokens = token_get_all( '<?php ' . $code );
				if ( empty( $tokens ) ) {
					self::debug_log( 'Cache content tokenization failed' );
					return false;
				}
			} catch ( \Throwable $e ) {
				self::debug_log( 'Cache content syntax check failed', array( 'error' => $e->getMessage() ) );
				return false;
			}

			return true;
		}

		/**
		 * Collect performance metrics for monitoring.
		 *
		 * @param float $execution_time Hook execution time in seconds.
		 * @param int   $memory_usage Memory usage in bytes.
		 * @param int   $log_count Current number of logs in memory.
		 *
		 * @return void
		 *
		 * @since 4.6.1
		 */
		private static function collect_performance_metrics( float $execution_time, int $memory_usage, int $log_count ) {
			static $metrics = array(
				'total_execution_time' => 0.0,
				'total_memory_usage'   => 0,
				'hook_count'           => 0,
				'max_execution_time'   => 0.0,
				'max_memory_usage'     => 0,
				'avg_execution_time'   => 0.0,
				'avg_memory_usage'     => 0,
			);

			$metrics['total_execution_time'] += $execution_time;
			$metrics['total_memory_usage']   += $memory_usage;
			++$metrics['hook_count'];

			if ( $execution_time > $metrics['max_execution_time'] ) {
				$metrics['max_execution_time'] = $execution_time;
			}

			if ( $memory_usage > $metrics['max_memory_usage'] ) {
				$metrics['max_memory_usage'] = $memory_usage;
			}

			$metrics['avg_execution_time'] = $metrics['total_execution_time'] / $metrics['hook_count'];
			$metrics['avg_memory_usage']   = $metrics['total_memory_usage'] / $metrics['hook_count'];

			// Log performance warnings if thresholds are exceeded.
			if ( $execution_time > 0.1 ) { // More than 100ms.
				self::debug_log(
					'Slow hook execution detected',
					array(
						'execution_time' => $execution_time,
						'memory_usage'   => $memory_usage,
						'log_count'      => $log_count,
					)
				);
			}

			if ( $memory_usage > 1048576 ) { // More than 1MB.
				self::debug_log(
					'High memory usage detected',
					array(
						'execution_time' => $execution_time,
						'memory_usage'   => $memory_usage,
						'log_count'      => $log_count,
					)
				);
			}

			if ( $log_count > self::MAX_MEMORY_LOGS * 0.8 ) { // 80% of max capacity.
				self::debug_log(
					'Approaching memory log limit',
					array(
						'log_count' => $log_count,
						'max_logs'  => self::MAX_MEMORY_LOGS,
					)
				);
			}
		}

		/**
		 * Log debug information for troubleshooting.
		 *
		 * @param string $message Debug message.
		 * @param array  $context Additional context data.
		 *
		 * @return void
		 *
		 * @since 4.6.1
		 */
		private static function debug_log( string $message, array $context = array() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				$log_message = '[Hooks Capture] ' . $message;
				if ( ! empty( $context ) ) {
					$log_message .= ' ' . wp_json_encode( $context );
				}
				error_log( $log_message );
			}
		}

		/**
		 * Delete the existing cache file.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		public static function delete_cache_file() {
			$cache_file = self::get_cache_file_path();

			if ( ! $cache_file || ! file_exists( $cache_file ) ) {
				return true;
			}

			return @unlink( $cache_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.unlink_unlink
		}

		/**
		 * =======================================================================
		 * NEW FEATURES: Early Filtering, Memory Pool, Error Recovery, Sampling
		 * =======================================================================
		 */

		/**
		 * Initialize memory pool for reusing structures.
		 *
		 * @return void
		 *
		 * @since latest
		 */
		private static function init_memory_pool() {
			if ( ! isset( $GLOBALS['advan_memory_pool'] ) ) {
				$GLOBALS['advan_memory_pool'] = array(
					'backtrace_cache' => array(),
					'sanitized_data'  => array(),
					'json_cache'      => array(),
					'pool_size'       => 0,
					'max_pool_size'   => 100 * 1024 * 1024, // 100MB limit.
				);
			}
		}

		/**
		 * Get cached backtrace.
		 *
		 * @param string $key Cache key.
		 *
		 * @return array|null Cached backtrace or null if not found.
		 *
		 * @since latest
		 */
		private static function get_cached_backtrace( string $key ) {
			if ( isset( $GLOBALS['advan_memory_pool']['backtrace_cache'][ $key ] ) ) {
				return $GLOBALS['advan_memory_pool']['backtrace_cache'][ $key ];
			}
			return null;
		}

		/**
		 * Set cached backtrace.
		 *
		 * @param string $key Cache key.
		 * @param array  $backtrace Backtrace data.
		 *
		 * @return void
		 *
		 * @since latest
		 */
		private static function set_cached_backtrace( string $key, array $backtrace ) {
			if ( ! isset( $GLOBALS['advan_memory_pool'] ) ) {
				return;
			}

			// Check pool size limit.
			$backtrace_size = strlen( \wp_json_encode( $backtrace ) );
			if ( $GLOBALS['advan_memory_pool']['pool_size'] + $backtrace_size > $GLOBALS['advan_memory_pool']['max_pool_size'] ) {
				// Clear oldest entries if pool is full.
				array_shift( $GLOBALS['advan_memory_pool']['backtrace_cache'] );
			}

			$GLOBALS['advan_memory_pool']['backtrace_cache'][ $key ] = $backtrace;
			$GLOBALS['advan_memory_pool']['pool_size']              += $backtrace_size;
		}

		/**
		 * Get cached JSON.
		 *
		 * @param string $key Cache key.
		 *
		 * @return string|null Cached JSON or null if not found.
		 *
		 * @since latest
		 */
		private static function get_cached_json( string $key ) {
			if ( isset( $GLOBALS['advan_memory_pool']['json_cache'][ $key ] ) ) {
				return $GLOBALS['advan_memory_pool']['json_cache'][ $key ];
			}
			return null;
		}

		/**
		 * Set cached JSON.
		 *
		 * @param string $key  Cache key.
		 * @param string $json JSON data.
		 *
		 * @return void
		 *
		 * @since latest
		 */
		private static function set_cached_json( string $key, string $json ) {
			if ( ! isset( $GLOBALS['advan_memory_pool'] ) ) {
				return;
			}

			$json_size = strlen( $json );
			if ( $GLOBALS['advan_memory_pool']['pool_size'] + $json_size > $GLOBALS['advan_memory_pool']['max_pool_size'] ) {
				array_shift( $GLOBALS['advan_memory_pool']['json_cache'] );
			}

			$GLOBALS['advan_memory_pool']['json_cache'][ $key ] = $json;
			$GLOBALS['advan_memory_pool']['pool_size']         += $json_size;
		}

		/**
		 * Cleanup memory pool on shutdown.
		 *
		 * @return void
		 *
		 * @since latest
		 */
		public static function cleanup_memory_pool() {
			unset( $GLOBALS['advan_memory_pool'] );
		}

		/**
		 * Early filtering: Check if hook should be captured before processing.
		 *
		 * @param string $hook_name The hook name to check.
		 * @return bool True if hook should be captured, false otherwise.
		 */
		private static function should_capture_hook_early( string $hook_name ): bool {
			static $filtered_hooks = null;

			// Initialize filtered hooks list on first call
			if ( null === $filtered_hooks ) {
				$filtered_hooks = apply_filters(
					'advan_excluded_hooks',
					array(
						// WordPress core hooks that are too frequent/noisy.
						'gettext',
						'gettext_with_context',
						'ngettext',
						'ngettext_with_context',
						'locale',
						'override_load_textdomain',
						'load_textdomain',
						'unload_textdomain',

						// Option hooks (very frequent).
						'pre_option_*',
						'default_option_*',
						'option_*',

						// Transient hooks (very frequent).
						'pre_transient_*',
						'transient_*',
						'set_transient_*',
						'delete_transient_*',

						// Cache hooks (very frequent).
						'pre_cache_*',
						'cache_*',

						// WP_Query hooks (can be very frequent).
						'pre_get_posts',
						'posts_where',
						'posts_join',
						'posts_orderby',
						'posts_fields',
						'posts_clauses',
						'posts_request',
						'posts_results',
						'posts_pre_query',

						// Meta hooks (very frequent).
						'get_*_metadata',
						'update_*_metadata',
						'add_*_metadata',
						'delete_*_metadata',

						// User hooks (frequent in some contexts).
						'get_user_metadata',
						'update_user_metadata',

						// Comment hooks (can be frequent).
						'wp_update_comment_count',
						'pre_get_comments',
						'comments_clauses',

						// Taxonomy hooks.
						'get_terms',
						'get_term',
						'get_*_terms',
					)
				);
			}

			// Check if hook matches any filtered pattern.
			foreach ( $filtered_hooks as $pattern ) {
				if ( fnmatch( $pattern, $hook_name ) ) {
					return false; // Don't capture this hook.
				}
			}

			return true;
		}

		/**
		 * Sampling: Check if hook should be sampled (for high-frequency hooks).
		 *
		 * @param string $hook_name The hook name to check.
		 *
		 * @return bool True if hook should be captured, false if skipped for sampling.
		 *
		 * @since latest
		 */
		private static function should_sample_hook( string $hook_name ): bool {
			static $hook_counters  = array();
			static $sampling_rates = null;

			// Initialize sampling rates on first call.
			if ( null === $sampling_rates ) {
				$sampling_rates = apply_filters(
					'advan_hook_sampling_rates',
					array(
						// Sample every Nth call for these hooks.
						'option_*'       => 100,  // Sample 1% of option hooks.
						'transient_*'    => 50,   // Sample 2% of transient hooks.
						'gettext*'       => 20,   // Sample 5% of gettext hooks.
						'get_*_metadata' => 10,   // Sample 10% of metadata hooks.
						'pre_get_posts'  => 5,    // Sample 20% of query hooks.
					)
				);
			}

			// Check if this hook should be sampled.
			foreach ( $sampling_rates as $pattern => $rate ) {
				if ( fnmatch( $pattern, $hook_name ) ) {
					// Initialize counter for this hook.
					if ( ! isset( $hook_counters[ $hook_name ] ) ) {
						$hook_counters[ $hook_name ] = 0;
					}

					++$hook_counters[ $hook_name ];

					// Only capture if counter is multiple of rate.
					if ( $hook_counters[ $hook_name ] % 0 !== $rate ) {
						return false; // Skip this call.
					}

					break; // Found matching pattern, no need to check others.
				}
			}

			return true;
		}

		/**
		 * Safe JSON encoding with fallback for error recovery.
		 *
		 * @param mixed $data    Data to encode.
		 * @param mixed $fallback Fallback data if encoding fails.
		 *
		 * @return string JSON encoded data or fallback.
		 *
		 * @since latest
		 */
		public static function safe_json_encode( $data, $fallback = null ) {
			try {
				$encoded = \wp_json_encode( $data );
				if ( false === $encoded ) {
					throw new \Exception( 'JSON encoding failed' );
				}
				return $encoded;
			} catch ( \Exception $e ) {
				// Log the error.
				if ( function_exists( 'error_log' ) ) {
					error_log( 'Hooks Capture: JSON encoding failed - ' . $e->getMessage() );
				}

				// Return fallback or simplified data.
				if ( null !== $fallback ) {
					return \wp_json_encode( $fallback );
				}

				// Create a simplified version.
				if ( is_array( $data ) ) {
					return \wp_json_encode(
						array(
							'error' => 'JSON encoding failed',
							'type'  => 'array',
							'count' => count( $data ),
						)
					);
				} elseif ( is_object( $data ) ) {
					return \wp_json_encode(
						array(
							'error' => 'JSON encoding failed',
							'type'  => 'object',
							'class' => get_class( $data ),
						)
					);
				} else {
					return \wp_json_encode(
						array(
							'error'   => 'JSON encoding failed',
							'type'    => gettype( $data ),
							'content' => substr( (string) $data, 0, 100 ),
						)
					);
				}
			}
		}

		/**
		 * Safe JSON decoding with error handling for error recovery.
		 *
		 * @param string $data     Data to decode.
		 * @param mixed  $fallback Fallback data if decoding fails.
		 *
		 * @return mixed Decoded data or fallback.
		 *
		 * @since latest
		 */
		public static function safe_json_decode( string $data, $fallback = null ) {
			if ( empty( $data ) ) {
				return $fallback ?: array();
			}

			try {
				$decoded = \wp_json_decode( $data, true );
				if ( null === $decoded && 'null' !== $data ) {
					throw new \Exception( 'JSON decoding failed' );
				}
				return $decoded;
			} catch ( \Exception $e ) {
				// Log the error.
				if ( function_exists( 'error_log' ) ) {
					error_log( 'Hooks Capture: JSON decoding failed - ' . $e->getMessage() );
				}

				// Return fallback.
				return $fallback ?: array( 'error' => 'JSON decoding failed' );
			}
		}

		/**
		 * Safe serialization with fallback for error recovery.
		 * DEPRECATED: Use safe_json_encode() instead for security.
		 *
		 * @param mixed $data    Data to serialize.
		 * @param mixed $fallback Fallback data if serialization fails.
		 *
		 * @return string Serialized data or fallback.
		 *
		 * @throws \Exception
		 *
		 * @deprecated Use safe_json_encode() instead
		 * @since latest
		 */
		public static function safe_serialize( $data, $fallback = null ) {
			// Log deprecation warning.
			if ( function_exists( 'error_log' ) ) {
				error_log( 'Hooks Capture: safe_serialize() is deprecated. Use safe_json_encode() instead.' );
			}

			// Fall back to JSON encoding for security.
			return self::safe_json_encode( $data, $fallback );
		}

		/**
		 * Safe unserialization with error handling for error recovery.
		 * DEPRECATED: Use safe_json_decode() instead for security.
		 *
		 * @param string $data     Data to unserialize.
		 * @param mixed  $fallback Fallback data if unserialization fails.
		 *
		 * @return mixed Unserialized data or fallback.
		 *
		 * @throws \Exception
		 *
		 * @deprecated Use safe_json_decode() instead
		 * @since latest
		 */
		public static function safe_unserialize( string $data, $fallback = null ) {
			// Log deprecation warning.
			if ( function_exists( 'error_log' ) ) {
				error_log( 'Hooks Capture: safe_unserialize() is deprecated. Use safe_json_decode() instead.' );
			}

			// Fall back to JSON decoding for security.
			return self::safe_json_decode( $data, $fallback );
		}
	}
}
