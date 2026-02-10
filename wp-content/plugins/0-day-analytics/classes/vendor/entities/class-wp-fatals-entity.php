<?php
/**
 * Entity: Fatal Errors.
 *
 * @package advan
 *
 * @since 3.8.0
 */

declare(strict_types=1);

namespace ADVAN\Entities;

use ADVAN\Helpers\WP_Helper;
use ADVAN\Controllers\Requests_Log;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Entities_Global\Common_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Entities\WP_Fatals_Entity' ) ) {
	/**
	 * Responsible for the fatal errors.
	 */
	class WP_Fatals_Entity extends Abstract_Entity {
		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $table = ADVAN_PREFIX . 'wp_fatals_log';

		/**
		 * Cache for available plugins list used in dropdown rendering.
		 *
		 * @var array|null
		 *
		 * @since 4.1.1
		 */
		private static $plugins_cache = null;

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 3.8.0
		 */
		protected static $fields = array(
			'id'                => 'int',
			'hash_key'          => 'string',
			'blog_id'           => 'int',
			'datetime'          => 'string',
			'severity'          => 'string',
			'message'           => 'string',
			'error_file'        => 'string',
			'error_line'        => 'int',
			'backtrace_segment' => 'string',
			'user_id'           => 'int',
			'user_roles'        => 'string',
			'ip'                => 'string',
			'type_env'          => 'string',
			'source_type'       => 'string',
			'source'            => 'string',
			'source_slug'       => 'string',
			'version_text'      => 'string',
			'version'           => 'int',
			'repeating'         => 'int',
		);

		/**
		 * Holds all the default values for the columns.
		 *
		 * @var array
		 *
		 * @since 3.8.0
		 */
		protected static $fields_values = array(
			'id'                => 0,
			'hash_key'          => '',
			'blog_id'           => 0,
			'datetime'          => '',
			'severity'          => '',
			'message'           => '',
			'error_file'        => '',
			'error_line'        => 0,
			'backtrace_segment' => '',
			'user_id'           => 0,
			'user_roles'        => '',
			'ip'                => '',
			'type_env'          => '',
			'source_type'       => '',
			'source'            => '',
			'source_slug'       => '',
			'version_text'      => '',
			'version'           => 0,
			'repeating'         => 1,
		);

		/**
		 * Creates table functionality.
		 *
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @since 3.8.0
		 */
		public static function create_table( $connection = null ): bool {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$collate = $connection->get_charset_collate();

				}
			} else {
				$collate = self::get_connection()->get_charset_collate();
			}
			$table_name    = self::get_table_name( $connection );
			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					id BIGINT unsigned not null auto_increment,
					hash_key CHAR(64) NOT NULL,
					blog_id int NOT NULL,
					datetime BIGINT NOT NULL DEFAULT 0,
					severity VARCHAR(50) DEFAULT NULL,
					message MEDIUMTEXT DEFAULT NULL,
					error_file VARCHAR(255) DEFAULT NULL,
					error_line INT DEFAULT NULL,
					backtrace_segment MEDIUMTEXT NOT NULL,
					user_id BIGINT unsigned NOT NULL DEFAULT 0,
					user_roles VARCHAR(155) DEFAULT NULL,
					ip TEXT DEFAULT NULL,
					type_env VARCHAR(20) NOT NULL DEFAULT "",
					source_type VARCHAR(20) DEFAULT NULL,
					source VARCHAR(255) DEFAULT NULL,
					source_slug VARCHAR(255) DEFAULT NULL,
					version_text VARCHAR(19) DEFAULT NULL,
					version BIGINT UNSIGNED DEFAULT NULL,
					repeating int NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY (hash_key),
				KEY `datetime` (`datetime`),
				KEY `severity` (`severity`),
				KEY `source_slug` (`source_slug`),
				KEY `source_type` (`source_type`)
				)
			  ' . $collate . ';';

			/*
			Storing version:
					"#{major}.#{format(minor)}#{format(revision)}"

			Where the format() function is defined as:

			sprintf("%0.4d", i)[0,4]
			*/
			return self::maybe_create_table( $table_name, $wp_entity_sql, $connection );
		}

		/**
		 * Returns the table CMS admin fields
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_column_names_admin(): array {
			$columns = array(
				'datetime'     => __( 'Date', '0-day-analytics' ),
				'severity'     => __( 'Severity', '0-day-analytics' ),
				'message'      => __( 'Message', '0-day-analytics' ),
				'error_file'   => __( 'File', '0-day-analytics' ),
				'source_type'  => __( 'Source Type', '0-day-analytics' ),
				'source'       => __( 'Source', '0-day-analytics' ),
				'version_text' => __( 'Version', '0-day-analytics' ),
				'type_env'     => __( 'Environment', '0-day-analytics' ),
				'user_id'      => __( 'User', '0-day-analytics' ),
				'user_roles'   => __( 'User Role(s)', '0-day-analytics' ),
				'ip'           => __( 'IP', '0-day-analytics' ),
				'repeating'    => __( 'Number of occurrences', '0-day-analytics' ),
			);

			if ( WP_Helper::is_multisite() ) {
				$columns['blog_id'] = __( 'From Blog', '0-day-analytics' );
			}

			return $columns;
		}

		/**
		 * Returns array with all of the columns which are shown in the row details window.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function get_details_columns(): array {
			$columns = array(
				'datetime'          => __( 'Date', '0-day-analytics' ),
				'severity'          => __( 'Severity', '0-day-analytics' ),
				'message'           => __( 'Message', '0-day-analytics' ),
				'error_file'        => __( 'File', '0-day-analytics' ),
				'error_line'        => __( 'Line', '0-day-analytics' ),
				'source_type'       => __( 'Source Type', '0-day-analytics' ),
				'source'            => __( 'Source', '0-day-analytics' ),
				'version_text'      => __( 'Version', '0-day-analytics' ),
				'type_env'          => __( 'Environment', '0-day-analytics' ),
				'user_id'           => __( 'User', '0-day-analytics' ),
				'user_roles'        => __( 'User Role(s)', '0-day-analytics' ),
				'ip'                => __( 'IP', '0-day-analytics' ),
				'repeating'         => __( 'Number of occurrences', '0-day-analytics' ),
				'backtrace_segment' => __( 'Backtrace', '0-day-analytics' ),
			);

			if ( WP_Helper::is_multisite() ) {
				$columns['blog_id'] = __( 'From Blog', '0-day-analytics' );
			}

			return $columns;
		}

		/**
		 * Generates a hash from the given data.
		 *
		 * @param string $data - The data to be hashed.
		 *
		 * @return string
		 *
		 * @since 3.8.0
		 */
		public static function hash_generating( string $data ): string {
			return hash( 'sha256', $data );
		}

		/**
		 * Redacts sensitive tokens and PII from a text blob.
		 *
		 * - Masks common secrets (API keys, tokens, passwords).
		 * - Masks bearer/JWT tokens and long hex strings.
		 * - Masks email addresses.
		 * - Optionally allows external filtering via `advan_redact_text`.
		 *
		 * @param string $text Raw text to redact.
		 *
		 * @return string Redacted text.
		 *
		 * @since 4.1.1
		 */
		private static function redact_text( string $text ): string {
			$patterns = array(
				// Bearer tokens.
				'/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/i',
				// JWT tokens.
				'/(eyJ[\w\-]+\.[\w\-]+\.?[\w\-]*)/i',
				// Pattern for api_key/token/secret/password style parameters.
				'/(api[_-]?key|access[_-]?token|id[_-]?token|refresh[_-]?token|token|secret|password|pwd)\s*[:=]\s*([^\s&,"\']+)/i',
				// Email addresses.
				'/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
				// Long hex strings (32+).
				'/\b[a-f0-9]{32,}\b/i',
			);

			$replacements = array(
				'Bearer [REDACTED]',
				'[REDACTED_JWT]',
				'$1=[REDACTED]',
				'[REDACTED_EMAIL]',
				'[REDACTED_TOKEN]',
			);

			$redacted = preg_replace( $patterns, $replacements, $text );
			if ( is_array( $redacted ) ) {
				$redacted = $text; // Safety: preg_replace shouldn't return array for string input, but guard anyway.
			}

			// Redact sensitive query parameters in URLs (only known names to avoid over-masking).
			$param_names = '(api[_-]?key|token|access[_-]?token|id[_-]?token|refresh[_-]?token|secret|password|pwd|auth|key)';
			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Alignment not required here.
			$redacted = preg_replace_callback(
				'/([?&])(' . $param_names . ')=([^&#\s]+)/i',
				function ( $m ) {
					return $m[1] . $m[2] . '=[REDACTED]';
				},
				$redacted
			);

			// Allow external filters to further redact or unredact as needed.
			if ( function_exists( 'apply_filters' ) ) {
				$redacted = (string) apply_filters( 'advan_redact_text', $redacted, $text );
			}

			return $redacted;
		}

		/**
		 * Recursively redact sensitive data from a backtrace structure (array|string).
		 *
		 * @param mixed $backtrace Backtrace structure to redact.
		 *
		 * @return mixed Redacted backtrace structure.
		 *
		 * @since 4.1.1
		 */
		private static function redact_backtrace( $backtrace ) {
			if ( is_array( $backtrace ) ) {
				$clean = array();
				foreach ( $backtrace as $k => $v ) {
					$clean[ $k ] = self::redact_backtrace( $v );
				}
				return $clean;
			}
			if ( is_object( $backtrace ) ) {
				// Convert objects to arrays conservatively.
				return self::redact_backtrace( (array) $backtrace );
			}
			if ( is_string( $backtrace ) ) {
				return self::redact_text( $backtrace );
			}
			return $backtrace;
		}

		/**
		 * Creates a normalized string used for hashing/deduplication to reduce noise.
		 *
		 * @param string     $message   Error message.
		 * @param mixed      $backtrace Backtrace array/string.
		 * @param string     $file      File path.
		 * @param int|string $line      Line number.
		 *
		 * @return string Normalized string for hashing.
		 *
		 * @since 4.1.1
		 */
		private static function normalize_for_hash( string $message, $backtrace, string $file, $line ): string {
			$bt = $backtrace;
			if ( is_array( $bt ) || is_object( $bt ) ) {
				$bt = \wp_json_encode( $bt );
			}
			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Simplicity preferred over alignment.
			$bt = (string) $bt;
			$norm_file = $file;
			if ( defined( 'ABSPATH' ) && is_string( ABSPATH ) && $file ) {
				$norm_file = str_replace( ABSPATH, '', $file );
			}

			$patterns     = array(
				'/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', // timestamps.
				'/line\s+\d+/i',
				'/(:|=)\d+/',
				'/\b\d{3,}\b/', // large numbers (IDs, ports, etc.).
				'/0x[0-9a-f]+/i',
			);
			$replacements = array( '[TS]', 'line [N]', '$1[N]', '[N]', '0x[HEX]' );

			$m = preg_replace( $patterns, $replacements, $message );
			$b = preg_replace( $patterns, $replacements, $bt );

			return strtolower( trim( $m ) . '|' . trim( (string) $line ) . '|' . trim( $norm_file ) . '|' . trim( $b ) );
		}

		/**
		 * Truncate a string safely to a maximum length.
		 *
		 * @param string $text Text to truncate.
		 * @param int    $max  Maximum length in bytes.
		 *
		 * @return string Truncated text.
		 *
		 * @since 4.1.1
		 */
		private static function truncate( string $text, int $max ): string {
			if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
				return mb_strlen( $text, '8bit' ) > $max ? mb_substr( $text, 0, $max, '8bit' ) : $text;
			}
			return strlen( $text ) > $max ? substr( $text, 0, $max ) : $text;
		}

		/**
		 * Prepares data for storing
		 *
		 * @param array $fatality - Array with the error data collected.
		 *
		 * @return array
		 *
		 * @since 3.8.0
		 */
		public static function prepare_fatality_to_store( array $fatality ): array {
			$prepared_fatality = array();

			// Redact sensitive information before hashing/storing.
			$message   = isset( $fatality['message'] ) ? (string) $fatality['message'] : '';
			$file      = isset( $fatality['file'] ) ? (string) $fatality['file'] : '';
			$line      = isset( $fatality['line'] ) ? (string) $fatality['line'] : '';
			$backtrace = $fatality['backtrace'] ?? array();
			$red_msg   = self::redact_text( $message );
			$red_bt    = self::redact_backtrace( $backtrace );

			$prepared_fatality['hash_key'] = self::hash_generating( self::normalize_for_hash( $red_msg, $red_bt, $file, $line ) );

			$record = self::load( 'hash_key=%s', array( $prepared_fatality['hash_key'] ) );

			if ( ! empty( $record ) ) {
				$record['datetime']  = time();
				$record['repeating'] = (int) $record['repeating'] + 1;

				self::insert( $record );

				return $record;
			}
			// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Keep simple one-per-line assignments.
			$prepared_fatality['blog_id'] = (int) \get_current_blog_id();
			$prepared_fatality['datetime'] = time();
			$prepared_fatality['severity'] = isset( $fatality['severity'] ) ? (string) $fatality['severity'] : '';
			$prepared_fatality['message'] = self::truncate( $red_msg, 16384 );
			$prepared_fatality['error_file'] = $file;
			$prepared_fatality['error_line'] = (int) ( $fatality['line'] ?? 0 );
			// phpcs:enable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			// Encode backtrace using wp_json_encode only (WordPress standard).
			$prepared_fatality['backtrace_segment'] = function_exists( 'wp_json_encode' ) ? \wp_json_encode( $red_bt ) : \json_encode( $red_bt ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Fallback if wp_json_encode missing.
			// phpcs:enable Generic.Formatting.MultipleStatementAlignment.NotSameWarning

			if ( function_exists( 'get_current_user_id' ) ) {
				$prepared_fatality['user_id'] = (int) \get_current_user_id();
			} else {
				$prepared_fatality['user_id'] = 0;
			}
			if ( function_exists( 'wp_get_current_user' ) ) {
				$current_user                    = \wp_get_current_user();
				$prepared_fatality['user_roles'] = implode( ',', $current_user->roles );
			} else {
				$prepared_fatality['user_roles'] = '';
			}

			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Alignment not necessary.
			$filter_private = function_exists( 'apply_filters' ) ? (bool) \apply_filters( 'advan_ip_filter_private', false ) : false;
			$prepared_fatality['ip']       = \implode( ',', self::collect_all_client_ips( $filter_private ) );
			$prepared_fatality['type_env'] = Requests_Log::current_page_type();

			$plugin_base = Plugin_Theme_Helper::get_plugin_from_file_path(
				$prepared_fatality['error_file'] ?? '',
			);

			if ( $plugin_base ) {
				$prepared_fatality['source_type']  = 'plugin';
				$plugin_name                       = Plugin_Theme_Helper::get_sources()['plugins'][ $plugin_base ]['Name'] ?? $plugin_base;
				$prepared_fatality['source']       = $plugin_name;
				$prepared_fatality['source_slug']  = $plugin_base;
				$prepared_fatality['version_text'] = Plugin_Theme_Helper::get_sources()['plugins'][ $plugin_base ]['Version'] ?? '';
				$prepared_fatality['version']      = self::version_to_decimal( $prepared_fatality['version_text'] ?? '0.0.0' );

			} else {
				$theme = Plugin_Theme_Helper::get_theme_from_file_path( $fatality['file'] );
				if ( $theme ) {
					$prepared_fatality['source_type']  = 'theme';
					$prepared_fatality['source']       = $theme->get( 'Name' ) ?? '';
					$prepared_fatality['version_text'] = $theme->get( 'Version' ) ?? '';
					$prepared_fatality['version']      = self::version_to_decimal( $prepared_fatality['version_text'] ?? '0.0.0' );

				} elseif ( false !== \mb_strpos( $prepared_fatality['message'], ABSPATH . WPINC . \DIRECTORY_SEPARATOR ) ) {
						$prepared_fatality['source_type'] = 'core';
						$prepared_fatality['source']      = 'WordPress';
					if ( ! function_exists( 'get_bloginfo' ) ) {
						require_once ABSPATH . 'wp-includes/version.php';
					}
						$prepared_fatality['version_text'] = \get_bloginfo( 'version' );
						$prepared_fatality['version']      = self::version_to_decimal( $prepared_fatality['version_text'] ?? '0.0' );
				} else {
					$prepared_fatality['source_type']  = 'php';
					$prepared_fatality['source']       = 'PHP';
					$prepared_fatality['version_text'] = PHP_VERSION;
					$prepared_fatality['version']      = self::version_to_decimal( $prepared_fatality['version_text'] ?? '0.0' );
				}
			}

			$prepared_fatality['repeating'] = 1;

			self::insert( $prepared_fatality );

			return $prepared_fatality;
		}

		/**
		 * Collects all IP addresses seen in the request (headers + REMOTE_ADDR).
		 * Safely handles missing inet_pton()/inet_ntop().
		 *
		 * @param bool $filter_private If true, exclude private/reserved/loopback addresses.
		 * @return array Ordered list of distinct IPs (first = closest to client).
		 *
		 * @since 3.8.0
		 */
		public static function collect_all_client_ips( bool $filter_private = false ): array {
			$keys = array(
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_CLIENT_IP',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'HTTP_VIA',
				'HTTP_CF_CONNECTING_IP',
				'HTTP_TRUE_CLIENT_IP',
				'HTTP_X_REAL_IP',
				'REMOTE_ADDR',
				'REMOTE_HOST',
			);

			// Allow sites to restrict trusted headers (e.g., when not behind a proxy), or add custom ones.
			if ( function_exists( 'apply_filters' ) ) {
				// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Alignment not necessary.
				$keys = (array) \apply_filters( 'advan_ip_header_keys', $keys );
				$trust_proxy = (bool) \apply_filters( 'advan_ip_trust_proxy', false );
				if ( ! $trust_proxy ) {
					$keys = array_intersect( $keys, array( 'REMOTE_ADDR' ) );
				}
			}

			$raw_ips = array();

			foreach ( $keys as $k ) {
				if ( empty( $_SERVER[ $k ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading server headers only.
					continue;
				}

				// Sanitize header value: unslash then allow only visible characters.
				$value = isset( $_SERVER[ $k ] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER[ $k ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_text_field.

				// Handle standard 'Forwarded' header with for= tokens.
				if ( stripos( $k, 'FORWARDED' ) !== false && stripos( $value, 'for=' ) !== false ) {
					if ( preg_match_all( '/for=(?:"?\[?([^"\];,\s\]]+)\]?"?)/i', $value, $m ) ) {
						foreach ( $m[1] as $ip_token ) {
							$raw_ips[] = $ip_token;
						}
					} else {
						$raw_ips[] = $value;
					}
				} else {
					$parts = preg_split( '/[,\s;]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
					foreach ( $parts as $p ) {
						$raw_ips[] = $p;
					}
				}
			}

			$normalized = array();

			foreach ( $raw_ips as $ip ) {
				$ip = preg_replace( '/^\[?([^\]]+)\]?:\d+$/', '$1', trim( $ip, "\"'" ) );

				if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					continue;
				}

				$norm = $ip;

				if ( function_exists( 'inet_pton' ) && function_exists( 'inet_ntop' ) ) {
					$packed = inet_pton( $ip ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Silencing removed.
					if ( false !== $packed ) {
						$norm = inet_ntop( $packed );
					}
				}

				if ( $filter_private ) {
					if ( ! filter_var( $norm, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
						continue;
					}
				}

				$normalized[] = $norm;
			}

			$ips = array_values( array_unique( $normalized ) );

			// Post-process per site preference: anonymize or hash.
			if ( function_exists( 'apply_filters' ) ) {
				$mode = (string) \apply_filters( 'advan_ip_mode', 'raw' ); // raw|anonymize|hash.
			} else {
				$mode = 'raw';
			}

			if ( 'anonymize' === $mode ) {
				$ips = array_map( array( __CLASS__, 'anonymize_ip' ), $ips );
			} elseif ( 'hash' === $mode ) {
				$ips = array_map(
					function ( $ip ) {
						$salt = '';
						if ( function_exists( 'apply_filters' ) ) {
							$salt = (string) \apply_filters( 'advan_ip_hash_salt', '' );
						}
						if ( empty( $salt ) && function_exists( 'wp_salt' ) ) {
							$salt = \wp_salt( 'auth' );
						}
						return 'h:' . hash( 'sha256', $salt . '|' . $ip );
					},
					$ips
				);
			}

			return $ips;
		}

		/**
		 * Anonymize an IP address: IPv4 -> zero last octet; IPv6 -> zero lower 80 bits.
		 *
		 * @param string $ip Raw IP address.
		 * @return string Anonymized IP.
		 */
		private static function anonymize_ip( string $ip ): string {
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Simple assignments.
				$parts = explode( '.', $ip );
				$parts[3] = '0';
				// phpcs:enable Generic.Formatting.MultipleStatementAlignment.NotSameWarning
				return implode( '.', $parts );
			}
			if ( function_exists( 'inet_pton' ) && function_exists( 'inet_ntop' ) && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				$packed = inet_pton( $ip ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- No silencing used.
				if ( false !== $packed ) {
					// Zero the last 10 bytes (80 bits) -> keep /48 approx.
					$bytes = str_split( $packed );
					for ( $i = 6; $i < 16; $i++ ) {
						$bytes[ $i ] = "\x00";
					}
					return inet_ntop( implode( '', $bytes ) );
				}
			}
			// Fallback: return raw if we cannot process.
			return $ip;
		}

		/**
		 * Converts given version to decimal format for storing.
		 *
		 * @param string $version - The version string to be converted.
		 *
		 * @return int
		 *
		 * @since 3.8.0
		 */
		public static function version_to_decimal( string $version ): int {
			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Standard simple assignments.
			$parts = explode( '.', $version );
			// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- Standard simple assignments.
			$parts = array_pad( $parts, 3, '0' );
			list($major, $minor, $patch) = array_map( 'intval', $parts );

			// Encode into a single sortable number
			// 1e12 gives room for 3 groups of 3 digits each (999,999,999 max).
			$encoded = $major * 1_000_000_000_000 + $minor * 1_000_000 + $patch;

			return $encoded;
		}

		/**
		 * Alters the table for version 4.7.0 - adds performance indexes
		 *
		 * @return bool
		 *
		 * @since 4.7.0
		 */
		public static function alter_table_470(): bool {
			$table_name = self::get_table_name();
			$indexes    = array(
				'severity'    => 'ADD KEY severity (severity)',
				'source_slug' => 'ADD KEY source_slug (source_slug)',
				'source_type' => 'ADD KEY source_type (source_type)',
			);

			global $wpdb;

			$table_name = self::get_table_name();
			$queries    = array();
			$results    = array();

			// Check existing indexes.
			$indexes = $wpdb->get_results(
				$wpdb->prepare(
					"SHOW INDEX FROM {$table_name} WHERE Key_name != %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					'PRIMARY'
				)
			);

			$existing_indexes = array();
			foreach ( $indexes as $index ) {
				$existing_indexes[] = strtolower( $index->Key_name ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}

			if ( ! in_array( 'severity', $existing_indexes, true ) ) {
				$queries[] = 'ALTER TABLE `' . $table_name . '` ADD INDEX `severity` (`severity`)';
			}

			if ( ! in_array( 'source_slug', $existing_indexes, true ) ) {
				$queries[] = 'ALTER TABLE `' . $table_name . '` ADD INDEX `source_slug` (`source_slug`)';
			}

			if ( ! in_array( 'source_type', $existing_indexes, true ) ) {
				$queries[] = 'ALTER TABLE `' . $table_name . '` ADD INDEX `source_type` (`source_type`)';
			}

			// Execute queries.
			foreach ( $queries as $sql ) {
				$result = Common_Table::execute_query( $sql );
				if ( false === $result ) {
					// Log error but continue with other indexes.
					error_log( 'Failed to create index with query: ' . $sql );
					continue;
				}
				$results[] = $result;
			}

			// Return true if we either successfully created indexes or no indexes were needed.
			return empty( $queries ) || ! empty( $results );
		}

		/**
		 * Prunes old fatal error records based on retention settings
		 *
		 * @param int $retention_days - Number of days to keep records (default 90).
		 *
		 * @return int Number of records deleted
		 *
		 * @since 4.7.0
		 */
		public static function prune_old_records( int $retention_days = 90 ): int {
			global $wpdb;
			$cutoff_time = time() - ( $retention_days * 24 * 60 * 60 );
			$table_name  = self::get_table_name();

			$sql = $wpdb->prepare(
				"DELETE FROM `$table_name` WHERE datetime < %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$cutoff_time
			);

			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching , WordPress.DB.PreparedSQL.NotPrepared

			return (int) $result;
		}

		/**
		 * Generates drop down with all the subsites that have mail logs.
		 *
		 * @param string $selected - The selected (if any) site ID.
		 * @param string $which - Indicates position of the dropdown (top or bottom).
		 *
		 * @return string
		 *
		 * @since 3.6.3
		 */
		public static function get_all_plugins_dropdown( $selected = '', $which = '' ): string {
			// Build cache of plugins once; always render fresh HTML so $selected/$which are honored.
			if ( null === self::$plugins_cache ) {
				$sql     = 'SELECT source_slug as plugin FROM ' . self::get_table_name() . ' WHERE source_type="plugin" GROUP BY source_slug ORDER BY source_slug DESC';
				$results = self::get_results( $sql );
				$plugins = array();
				if ( $results ) {
					foreach ( $results as $result ) {
						if ( ! isset( $result['plugin'] ) || empty( trim( (string) $result['plugin'] ) ) ) {
							continue;
						}
						$details   = Plugin_Theme_Helper::get_plugin_from_path( $result['plugin'] );
						$name      = ( isset( $details ) && isset( $details['Name'] ) ) ? $details['Name'] : (string) $result['plugin'];
						$plugins[] = array(
							'id'   => $result['plugin'],
							'name' => $name,
						);
					}
				}
				self::$plugins_cache = $plugins;
			}

			$output = '';
			if ( ! empty( self::$plugins_cache ) ) {
				$output  = '<select class="plugin_filter" name="plugin_' . \esc_attr( $which ) . '" id="plugin_' . \esc_attr( $which ) . '">';
				$output .= '<option value="-1">' . __( 'All plugins', '0-day-analytics' ) . '</option>';
				foreach ( self::$plugins_cache as $plugin_info ) {
					if ( isset( $selected ) && ! empty( trim( (string) $selected ) ) && (string) $selected === (string) $plugin_info['id'] ) {
						$output .= '<option value="' . \esc_attr( $plugin_info['id'] ) . '" selected>' . \esc_html( $plugin_info['name'] ) . '</option>';
						continue;
					}
					$output .= '<option value="' . \esc_attr( $plugin_info['id'] ) . '">' . \esc_html( $plugin_info['name'] ) . '</option>';
				}
				$output .= '</select>';
			}

			return $output;
		}
	}
}
