<?php
/**
 * Helper: executes stored snippets inside a guarded sandbox.
 *
 * @package advanced-analytics
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use WP_Error;
use ADVAN\Helpers\File_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\\Snippets_Sandbox' ) ) {
	/**
	 * Tiny sandbox wrapper used for snippet execution.
	 */
	class Snippets_Sandbox {

		private const OUTPUT_LIMIT = 15000;

		private const STORAGE_UPLOADS = 'uploads';

		private const STORAGE_STREAM = 'php_temp';

		/**
		 * Execute PHP snippet and return structured result.
		 *
		 * @param string $code    - Snippet code.
		 * @param array  $context - Context variables to expose inside snippet.
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		public static function execute( string $code, array $context = array() ): array {
			if ( ! \current_user_can( 'manage_options' ) ) {
				return self::permission_error();
			}

			return self::run( $code, $context );
		}

		/**
		 * Execute snippet without permission checks (runtime hooks/shortcodes).
		 *
		 * @param string $code    Snippet code.
		 * @param array  $context Execution context variables.
		 *
		 * @return array
		 */
		public static function execute_runtime( string $code, array $context = array() ): array {
			return self::run( $code, $context );
		}

		/**
		 * Shared execution pipeline between admin/runtime.
		 *
		 * @param string $code    Snippet code.
		 * @param array  $context Execution context variables.
		 *
		 * @return array
		 */
		private static function run( string $code, array $context ): array {
			$normalized = self::normalize_code( $code );
			$callable   = self::bootstrap_callable( $normalized );

			if ( \is_wp_error( $callable ) ) {
				return array(
					'status'      => 'error',
					'message'     => $callable->get_error_message(),
					'output'      => '',
					'duration'    => 0,
					'result_dump' => '',
				);
			}

			$prepared_context = self::prepare_context( $context );
			$start            = microtime( true );
			$buffer           = '';
			$status           = 'success';
			$message          = '';
			$result_dump      = '';

			ob_start();
			try {
				$result      = $callable( $prepared_context );
				$buffer      = (string) ob_get_clean();
				$result_dump = self::stringify_result( $result );
			} catch ( \Throwable $throwable ) {
				$buffer      = (string) ob_get_clean();
				$status      = 'error';
				$message     = $throwable->getMessage();
				$result_dump = self::stringify_result( $throwable->getTraceAsString() );
			}

			$duration = round( max( microtime( true ) - $start, 0 ), 5 );

			return array(
				'status'      => $status,
				'message'     => $message,
				'output'      => self::truncate_output( $buffer ),
				'duration'    => $duration,
				'result_dump' => $result_dump,
			);
		}

		/**
		 * Standardized permission error payload.
		 *
		 * @return array
		 */
		private static function permission_error(): array {
			return array(
				'status'      => 'error',
				'message'     => \__( 'Insufficient permissions to execute snippets.', '0-day-analytics' ),
				'output'      => '',
				'duration'    => 0,
				'result_dump' => '',
			);
		}

		/**
		 * Normalize incoming snippet code.
		 *
		 * @param string $code - Raw snippet code.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function normalize_code( string $code ): string {
			$code = str_replace( array( "\r\n", "\r" ), "\n", $code );
			$code = trim( $code );
			$code = preg_replace( '/^<\?(php)?/i', '', $code );
			$code = preg_replace( '/\?>$/', '', $code );

			return trim( $code ) . "\n";
		}

		/**
		 * Build temporary callable from snippet body.
		 *
		 * @param string $code - Snippet code.
		 *
		 * @return callable|WP_Error
		 *
		 * @since 4.3.0
		 */
		private static function bootstrap_callable( string $code ) {
			$wrapper  = self::build_wrapper( $code );
			$storage  = self::get_storage_mode();
			$fallback = ( self::STORAGE_UPLOADS === $storage ) ? self::STORAGE_STREAM : self::STORAGE_UPLOADS;

			$primary = ( self::STORAGE_UPLOADS === $storage )
				? self::create_callable_from_uploads( $wrapper )
				: self::create_callable_from_stream( $wrapper );

			if ( ! \is_wp_error( $primary ) ) {
				return $primary;
			}

			$fallback_callable = ( self::STORAGE_UPLOADS === $fallback )
				? self::create_callable_from_uploads( $wrapper )
				: self::create_callable_from_stream( $wrapper );

			return \is_wp_error( $fallback_callable ) ? $primary : $fallback_callable;
		}

		/**
		 * Create wrapper code shared across storage strategies.
		 *
		 * @param string $code - Snippet code.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function build_wrapper( string $code ): string {
			return "return ( static function( array \$context ) {\n\textract( \$context, EXTR_SKIP );\n" . $code . "\n} );";
		}

		/**
		 * Build callable by writing payload inside uploads directory.
		 *
		 * @param string $wrapper - Wrapped snippet code.
		 *
		 * @return callable|WP_Error
		 *
		 * @since 4.3.0
		 */
		private static function create_callable_from_uploads( string $wrapper ) {
			$path = self::create_upload_temp_file();
			if ( \is_wp_error( $path ) ) {
				return $path;
			}

			$payload = "<?php\n" . $wrapper;
			$written = file_put_contents( $path, $payload ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			if ( false === $written ) {
				@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				return new WP_Error( 'temp_write', \__( 'Unable to bootstrap snippet executor.', '0-day-analytics' ) );
			}

			try {
				$callable = include $path;
			} catch ( \Throwable $throwable ) {
				@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				return new WP_Error( 'snippet_parse_error', $throwable->getMessage() );
			}

			@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			if ( ! is_callable( $callable ) ) {
				return new WP_Error( 'not_callable', \__( 'Snippet payload is not callable.', '0-day-analytics' ) );
			}

			return $callable;
		}

		/**
		 * Build callable via in-memory php://temp stream.
		 *
		 * @param string $wrapper - Wrapped snippet code.
		 *
		 * @return callable|WP_Error
		 *
		 * @since 4.3.0
		 */
		private static function create_callable_from_stream( string $wrapper ) {
			$handle = fopen( 'php://temp', 'r+' );
			if ( false === $handle ) {
				return new WP_Error( 'temp_stream', \__( 'Unable to open php://temp stream for snippets.', '0-day-analytics' ) );
			}

			fwrite( $handle, $wrapper );
			rewind( $handle );
			$payload = stream_get_contents( $handle );
			fclose( $handle );

			try {
				$callable = eval( $payload ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
			} catch ( \ParseError $error ) {
				return new WP_Error( 'snippet_parse_error', $error->getMessage() );
			}

			if ( ! is_callable( $callable ) ) {
				return new WP_Error( 'not_callable', \__( 'Snippet payload is not callable.', '0-day-analytics' ) );
			}

			return $callable;
		}

		/**
		 * Resolve preferred storage mode from settings.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_storage_mode(): string {
			$preference = Settings::get_option( 'snippets_temp_storage' );
			$allowed    = array( self::STORAGE_UPLOADS, self::STORAGE_STREAM );
			return in_array( $preference, $allowed, true ) ? $preference : self::STORAGE_UPLOADS;
		}

		/**
		 * Create unique temp file inside uploads directory.
		 *
		 * @return string|WP_Error
		 *
		 * @since 4.3.0
		 */
		private static function create_upload_temp_file() {
			$uploads = \wp_upload_dir();
			if ( ! empty( $uploads['error'] ) ) {
				return new WP_Error( 'uploads_dir', \__( 'Unable to access uploads directory for snippets.', '0-day-analytics' ) );
			}

			$dir = \trailingslashit( $uploads['basedir'] ) . 'advanced-analytics/snippets-runtime';
			if ( ! is_dir( $dir ) && ! \wp_mkdir_p( $dir ) ) {
				return new WP_Error( 'uploads_dir', \__( 'Unable to create snippets runtime directory.', '0-day-analytics' ) );
			}

			// Ensure directory guards exist (idempotent calls).
			File_Helper::create_htaccess_file( $dir );
			File_Helper::create_index_file( $dir );

			$tmp_file = \tempnam( $dir, 'advan-snippet-' );
			if ( false === $tmp_file ) {
				return new WP_Error( 'temp_file', \__( 'Unable to create temporary execution file.', '0-day-analytics' ) );
			}

			// Lock down permissions for the temporary file as tightly as possible.
			@chmod( $tmp_file, 0600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			return $tmp_file;
		}

		/**
		 * Ensure only safe-ish data gets exposed to snippets.
		 *
		 * @param array $context - Incoming context variables.
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		private static function prepare_context( array $context ): array {
			global $wpdb;

			$defaults = array(
				'wpdb'         => $wpdb,
				'current_user' => \wp_get_current_user(),
				'site_url'     => \site_url(),
				'home_url'     => \home_url(),
				'blog_id'      => \get_current_blog_id(),
				'is_multisite' => \is_multisite(),
			);

			// Remove any callable/resource or unexpected object that is not explicitly whitelisted.
			foreach ( $context as $key => $value ) {
				if ( is_resource( $value ) || is_callable( $value ) ) {
					unset( $context[ $key ] );
					continue;
				}

				// Allow WP_User but strip other objects for safety.
				if ( is_object( $value ) && ! ( $value instanceof \WP_User ) ) {
					unset( $context[ $key ] );
					continue;
				}

				// Normalize strings coming from external sources.
				if ( is_string( $value ) ) {
					$context[ $key ] = \wp_unslash( $value );
				}
			}

			return array_merge( $defaults, $context );
		}

		/**
		 * Convert arbitrary result values into short printable strings.
		 *
		 * @param mixed $value - Incoming result value.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function stringify_result( $value ): string {
			if ( is_scalar( $value ) || null === $value ) {
				return (string) \maybe_serialize( $value );
			}

			return substr( print_r( $value, true ), 0, self::OUTPUT_LIMIT ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		/**
		 * Limit lengthy STDOUT output to keep UI responsive.
		 *
		 * @param string $buffer - Full output buffer.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function truncate_output( string $buffer ): string {
			if ( strlen( $buffer ) <= self::OUTPUT_LIMIT ) {
				return $buffer;
			}

			return substr( $buffer, 0, self::OUTPUT_LIMIT ) . "\n…";
		}
	}
}
