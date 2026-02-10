<?php
/**
 * Requests log class - captures the requests and fulfills the log table with the results.
 *
 * @package 0-day-analytics
 *
 * @since 2.7.0
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

use ADVAN\Helpers\Settings;
use ADVAN\Helpers\Context_Helper;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Entities\Requests_Log_Entity;
use ADVAN\Controllers\Controller_Init_Trait;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Controllers\Requests_Log' ) ) {
	/**
	 * Responsible for collecting the requests.
	 *
	 * @since 2.7.0
	 */
	class Requests_Log {

		use Controller_Init_Trait;

		/**
		 * Class cache for the requests count.
		 *
		 * @var integer
		 *
		 * @since 2.7.0
		 */
		private static $requests = 0;

		/**
		 * Class cache for the last inserted request ID.
		 *
		 * @var integer
		 *
		 * @since 2.7.0
		 */
		private static $last_id = 0;

		/**
		 * Class cache for the extracted page URL.
		 *
		 * @var string
		 *
		 * @since 2.7.0
		 */
		private static $page_url = '';

		/**
		 * Class cache for the collected trace.
		 *
		 * @var string
		 *
		 * @since 2.7.0
		 */
		private static $trace = '';

		/**
		 * Inits the class.
		 *
		 * @return void
		 *
		 * @since 2.7.0
		 */
		public static function init() {
			if ( Settings::get_option( 'requests_module_enabled' ) ) {

				static::conditional_init(
					'advana_requests_enable',
					function() {
						if ( ! Settings::get_option( 'advana_http_requests_disable' ) ) {
							\add_filter( 'pre_http_request', array( __CLASS__, 'pre_http_request' ), 0, 3 );
							\add_action( 'http_api_debug', array( __CLASS__, 'capture_request' ), 10, 5 );
						}

						if ( ! Settings::get_option( 'advana_rest_requests_disable' ) ) {
							// REST API events.
							\add_filter( 'rest_pre_dispatch', array( __CLASS__, 'pre_http_request' ), 0, 3 );
							\add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'capture_rest_request' ), 10, 3 );
						}
					}
				);
			}
		}

		/**
		 * Fires after an HTTP API response is received and before the response is returned.
		 *
		 * @param array|WP_Error $response    HTTP response or WP_Error object.
		 * @param string         $context     Context under which the hook is fired.
		 * @param string         $class       HTTP transport used.
		 * @param array          $parsed_args HTTP request arguments.
		 * @param string         $url         The request URL.
		 *
		 * @since 2.7.0
		 */
		public static function capture_request( $response, $context, $class, $parsed_args, $url ) {

			static $user_id = null;

			// Check if the response is an error.
			if ( \is_wp_error( $response ) ) {
				$status = 'error';
			} else {
				$status = 'success';
			}

			++self::$requests;

			if ( \function_exists( 'is_user_logged_in' ) && \function_exists( 'get_current_user_id' ) ) {
				// Cache the user id for this request only once.
				if ( null === $user_id ) {
					$user_id = \is_user_logged_in() ? (int) \get_current_user_id() : 0;
				}
			} else {
				$user_id = 0;
			}

			// Prepare the log entry.
			$trace_array = \json_decode( self::get_trace(), true );
			$log_entry   = array(
				'url'            => $url,
				'page_url'       => self::page_url(),
				'type'           => self::current_page_type(),
				'domain'         => \wp_parse_url( $url, PHP_URL_HOST ),
				'user_id'        => $user_id,
				'runtime'        => microtime( true ) - (float) ( ( $_SERVER['REQUEST_TIME_FLOAT'] ) ?? 0 ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'request_status' => $status,
				'request_group'  => isset( $parsed_args['group'] ) ? $parsed_args['group'] : '',
				'request_source' => isset( $parsed_args['source'] ) ? $parsed_args['source'] : '',
				'request_args'   => \wp_json_encode( self::sanitize_request_args( (array) $parsed_args ) ),
				'response'       => self::sanitize_response( $response ),
				'date_added'     => time(),
				'requests'       => self::$requests,
				'trace'          => self::get_trace(),
				'plugin'         => ( isset( $trace_array[7] ) && isset( $trace_array[7]['file'] ) ) ? Plugin_Theme_Helper::get_plugin_from_file_path( $trace_array[7]['file'] ) : '',
			);

			if ( isset( self::$last_id ) && self::$last_id > 0 ) {
				$log_entry['id'] = self::$last_id;
			}

			// Save the log entry to the database.
			self::$last_id = Requests_Log_Entity::insert( $log_entry );
		}

		/**
		 * Collects and returns the trace of the current request in JSON format.
		 *
		 * @return string
		 *
		 * @since 2.7.0
		 */
		public static function get_trace(): string {
			if ( empty( self::$trace ) ) {
				$trace = ( new \Exception( '' ) )->getTrace();

				self::$trace = \wp_json_encode( $trace );
			}

			return (string) self::$trace;
		}

		/**
		 * Fires before the actual request - start our timer.
		 *
		 * @param false|array|WP_Error $response    A preemptive return value of an HTTP request. Default false.
		 * @param array                $parsed_args HTTP request arguments.
		 * @param string               $url         The request URL.
		 *
		 * @since 2.7.0
		 */
		public static function pre_http_request( $response, $parsed_args, $url ) {
			// Silence unused parameter warnings.
			unset( $parsed_args, $url );
			// Start the timer.
			$_SERVER['REQUEST_TIME_FLOAT'] = microtime( true );

			return $response;
		}

		/**
		 * Return current page type.
		 * Id adding new page type update self::$page_types array with new page type group
		 *
		 * @return string cron|ajax|rest_api|xmlrpc|login|admin|frontend|core|installing|activate|undetermined|wp-cli
		 *
		 * @since 2.7.0
		 */
		public static function current_page_type() {

			static $return;

			if ( is_null( $return ) ) {
				if ( is_null( $return ) && Context_Helper::is_cron() ) {
					$return = 'cron';
				}

				if ( is_null( $return ) && Context_Helper::is_ajax() ) {
					$return = 'ajax';
				}

				// Is REST API endpoint.
				if ( is_null( $return ) && Context_Helper::is_rest() ) {
					$return = 'rest_api';
				}

				if ( is_null( $return ) && Context_Helper::is_xml_rpc() ) {
					$return = 'xmlrpc';
				}

				if ( is_null( $return ) && Context_Helper::is_wp_cli() ) {
					$return = 'wp-cli';
				}

				if ( is_null( $return ) && Context_Helper::is_login() ) {
					$return = 'login';
				}

				if ( is_null( $return ) && Context_Helper::is_front() ) {
					$return = 'frontend';
				}

				if ( is_null( $return ) && Context_Helper::is_admin() ) {
					$return = 'admin';
				}

				if ( is_null( $return ) && Context_Helper::is_core() ) {
					$return = 'core';
				}

				if ( is_null( $return ) && Context_Helper::is_installing() ) {
					$return = 'installing';
				}

				if ( is_null( $return ) && Context_Helper::is_wp_activate() ) {
					$return = 'activate';
				}
				if ( is_null( $return ) && Context_Helper::is_undetermined() ) {
					$return = 'undetermined';
				}
			}

			// Certain or fallback type.
			return $return;
		}

		/**
		 * Collects the given page URL.
		 *
		 * @return string
		 *
		 * @since 2.7.0
		 */
		public static function page_url(): string {

			if ( ! empty( self::$page_url ) ) {
				return self::$page_url;
			}

			if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
				$host = \sanitize_text_field( (string) $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

				$uri = \sanitize_text_field( (string) $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

				$built = ( \is_ssl() ? 'https://' : 'http://' ) . $host . $uri;
				// Strip potentially sensitive query params from the logged URL.
				self::$page_url = self::sanitize_url( $built );
			} else {
				// use WordPress functions.
				global $wp;

				if ( ! isset( $wp ) ) {
					$wp = new \WP(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
				self::$page_url = \home_url( \add_query_arg( array(), $wp->request ) );
			}

			return self::$page_url;
		}

		/**
		 * Captures the REST API request response and store it.
		 *
		 *  @param \WP_REST_Response|\WP_HTTP_Response|WP_Error|mixed - $response Result to send to the client.
		 *                                                                   Usually a WP_REST_Response or WP_Error.
		 * @param array                                            -   $handler  Route handler used for the request.
		 * @param \WP_REST_Request                                  -  $request  Request used to generate the response.
		 *
		 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error|mixed
		 *
		 * @since 2.8.0
		 */
		public static function capture_rest_request( $response, $handler, $request ) {

			static $user_id = null;

			// Check if the response is an error.
			if ( \is_wp_error( $response ) ) {
				$status = 'error';
			} else {
				$status = 'success';
			}

			++self::$requests;

			if ( \function_exists( 'is_user_logged_in' ) && \function_exists( 'get_current_user_id' ) ) {
				if ( null === $user_id ) {
					$user_id = \is_user_logged_in() ? (int) \get_current_user_id() : 0;
				}
			} else {
				$user_id = 0;
			}

			// Silence unused parameter warnings.
			unset( $handler );

			// Prepare the log entry.
			$log_entry = array(
				'url'            => \property_exists( $request, 'route' ) ? $request->get_route() : '',
				'page_url'       => self::page_url(),
				'type'           => self::current_page_type(),
				'domain'         => ( \property_exists( $request, 'headers' ) && isset( $request->get_headers()['host'] ) ) ? \sanitize_text_field( \implode( ', ', (array) $request->get_headers()['host'] ) ) : '',
				'user_id'        => $user_id,
				'runtime'        => microtime( true ) - (float) ( ( $_SERVER['REQUEST_TIME_FLOAT'] ) ?? 0 ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'request_status' => $status,
				'request_group'  => '',
				'request_source' => '',
				'request_args'   => \property_exists( $request, 'attributes' ) ? \wp_json_encode( self::sanitize_request_args( (array) $request->get_attributes() ) ) : '',
				'response'       => self::sanitize_response( $response ),
				'date_added'     => time(),
				'requests'       => self::$requests,
				'trace'          => self::get_trace(),
				'plugin'         => ( isset( \json_decode( self::get_trace(), true )[7] ) && isset( \json_decode( self::get_trace(), true )[7]['file'] ) ) ? Plugin_Theme_Helper::get_plugin_from_file_path( \json_decode( self::get_trace(), true )[7]['file'] ) : '',
			);

			if ( isset( self::$last_id ) && self::$last_id > 0 ) {
				$log_entry ['id'] = self::$last_id;
			}

			// Save the log entry to the database.
			self::$last_id = Requests_Log_Entity::insert( $log_entry );

			return $response;
		}

		/**
		 * Remove or mask sensitive data from request args before logging.
		 *
		 * @param array $args Request arguments/headers/body to be logged.
		 *
		 * @return array
		 *
		 * @since 4.1.1
		 */
		private static function sanitize_request_args( array $args ): array {
			$redact_keys = array(
				'authorization',
				'proxy-authorization',
				'cookie',
				'set-cookie',
				'x-api-key',
				'x-auth-token',
				'api_key',
				'apikey',
				'password',
				'pass',
				'secret',
				'client-secret',
				'token',
				'access_token',
				'refresh_token',
				'signature',
				'_wpnonce',
				'nonce',
				'code',
			);

			$normalized = array();
			foreach ( $args as $key => $value ) {
				$lower = is_string( $key ) ? strtolower( $key ) : $key;
				if ( in_array( $lower, $redact_keys, true ) || ( is_string( $lower ) && self::key_matches_sensitive_pattern( $lower ) ) ) {
					$normalized[ (string) $key ] = '[REDACTED]';
					continue;
				}

				if ( is_array( $value ) ) {
					$normalized[ (string) $key ] = self::sanitize_request_args( $value );
				} elseif ( is_string( $value ) ) {
					// Truncate very long strings (e.g., large bodies) to avoid storing excessive data.
					$normalized[ (string) $key ] = ( strlen( $value ) > 2048 ) ? substr( $value, 0, 2048 ) . '…[truncated]' : $value;
				} else {
					$normalized[ (string) $key ] = $value;
				}
			}

			return $normalized;
		}

		/**
		 * Best-effort masking of sensitive response information; limit size.
		 *
		 * @param mixed $response Raw response or WP_Error from HTTP/REST callbacks.
		 *
		 * @return string
		 *
		 * @since 4.1.1
		 */
		private static function sanitize_response( $response ): string {
			if ( \is_wp_error( $response ) ) {
				return \sanitize_text_field( $response->get_error_message() );
			}

			// WP HTTP API debug can pass arrays/objects. Summarize safely.
			try {
				if ( is_array( $response ) ) {
					// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
					$summary = array();
					$summary['response_code'] = isset( $response['response']['code'] ) ? (int) $response['response']['code'] : null;
					$summary['response_message'] = isset( $response['response']['message'] ) ? (string) $response['response']['message'] : null;
					$summary['headers'] = isset( $response['headers'] ) ? self::sanitize_request_args( (array) $response['headers'] ) : array();
					$body = isset( $response['body'] ) && is_string( $response['body'] ) ? $response['body'] : '';
					$summary['body_preview'] = ( '' !== $body ) ? substr( $body, 0, 512 ) . ( strlen( $body ) > 512 ? '…[truncated]' : '' ) : '';
					// phpcs:enable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
					return (string) \wp_json_encode( $summary );
				}

				// For objects or other types, defer to JSON encoding with size limits.
				$json = (string) \wp_json_encode( $response );
				return strlen( $json ) > 4096 ? substr( $json, 0, 4096 ) . '…[truncated]' : $json;
			} catch ( \Throwable $e ) {
				return 'response_unavailable';
			}
		}

		/**
		 * Heuristic to detect sensitive keys in arrays.
		 *
		 * @param string $key Array key to test for sensitivity.
		 *
		 * @return bool
		 *
		 * @since 4.1.1
		 */
		private static function key_matches_sensitive_pattern( string $key ): bool {
			$patterns = array( 'auth', 'token', 'secret', 'pass', 'cookie', 'key', 'nonce' );
			foreach ( $patterns as $p ) {
				if ( false !== strpos( $key, $p ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Remove sensitive query params from URLs before logging.
		 *
		 * @param string $url URL that may contain sensitive query parameters.
		 *
		 * @return string
		 *
		 * @since 4.1.1
		 */
		private static function sanitize_url( string $url ): string {
			$sensitive = array( 'password', 'pass', 'token', 'access_token', 'refresh_token', 'code', 'key', 'api_key', 'auth', '_wpnonce', 'nonce', 'signature' );
			try {
				return (string) \remove_query_arg( $sensitive, $url );
			} catch ( \Throwable $e ) {
				return $url;
			}
		}
	}
}
