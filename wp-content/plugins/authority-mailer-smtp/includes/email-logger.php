<?php
/**
 * Authority Mailer Email Logger
 *
 * Record outgoing test emails to a DB table for Authority Mailer.
 *
 * NOTE: This file is the same logger you already have but with an improved
 * authority_mailer_email_logger_shortcode() rendering function. The rendering now
 * attempts multiple decoding passes (json, stripslashes, html-entity decode)
 * and falls back cleanly so the admin view shows the human-readable email
 * content instead of raw JSON payloads.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
-----------------------
 * Simple cache-version helpers
 * ----------------------- */

/**
 * Get the current cache version for email logger.
 *
 * @return int The current cache version number.
 */
function authority_mailer_smtp_email_logger_cache_version() {
	$ver = wp_cache_get( 'authority_mailer_smtp_email_logger_version', 'authority_mailer_smtp_email_logger' );
	if ( false === $ver || ! is_int( $ver ) ) {
		$ver = 1;
		wp_cache_set( 'authority_mailer_smtp_email_logger_version', $ver, 'authority_mailer_smtp_email_logger' );
	}
	return $ver;
}

/**
 * Increment the email logger cache version.
 *
 * @return int The new cache version number.
 */
function authority_mailer_smtp_email_logger_cache_bump() {
	$ver = authority_mailer_smtp_email_logger_cache_version();
	++$ver;
	wp_cache_set( 'authority_mailer_smtp_email_logger_version', $ver, 'authority_mailer_smtp_email_logger' );
	return $ver;
}

/*
-----------------------
 * Ensure table exists
 * ----------------------- */

/**
 * Create the email log database table if it doesn't exist.
 *
 * @deprecated 1.0.0 Tables are now created centrally via Authority_Mailer_Database_Setup on activation.
 *
 * This function is kept for backward compatibility but does nothing.
 * All table creation is now handled by Authority_Mailer_Database_Setup::create_all_tables()
 * which is called only during plugin activation.
 */
function authority_mailer_smtp_email_logger_maybe_create_table() {
	// Deprecated - tables are now created on plugin activation only.
	// Database setup removed for free version.
}

/*
-----------------------
 * Inserts / updates (writes)
 * ----------------------- */

/**
 * Helper function for providers to log emails with automatic spam score extraction.
 *
 * This centralizes spam score handling and debug logging for ALL providers.
 * Providers should call this instead of authority_mailer_smtp_email_logger_insert() directly.
 *
 * @since 1.0.0
 *
 * @param array $email_array The email array passed to the provider (contains spam_score if available).
 * @param array $log_data    The log data to insert (provider, to_email, subject, etc.).
 * @return int|false The inserted row ID on success, false on failure.
 */
function authority_mailer_smtp_log_email_with_spam_score( $email_array, $log_data ) {
	// Debug logging - entry point.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Logger Helper] log_email_with_spam_score() called | provider: ' . ( $log_data['provider'] ?? 'unknown' ) );
	}

	// Automatically extract spam score from email array if present.
	if ( isset( $email_array['spam_score'] ) && ! isset( $log_data['spam_score'] ) ) {
		$log_data['spam_score'] = floatval( $email_array['spam_score'] );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Logger Helper] Spam score extracted from email array: ' . $log_data['spam_score'] );
		}
	} elseif ( ! isset( $email_array['spam_score'] ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Logger Helper] No spam score in email array' );
		}
	}

	// Automatically extract tracking_id from email array if present.
	$tracking_id = null;
	if ( isset( $email_array['tracking_id'] ) && ! empty( $email_array['tracking_id'] ) ) {
		$tracking_id = sanitize_text_field( $email_array['tracking_id'] );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Logger Helper] Tracking ID extracted from email array: ' . $tracking_id );
		}
	}

	// Call the main logger.
	$log_id = authority_mailer_smtp_email_logger_insert( $log_data );

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Logger Helper] Email logged with ID: ' . $log_id . ' | spam_score: ' . ( $log_data['spam_score'] ?? 'null' ) );
	}

	// Create tracking pixel record if tracking_id is present and email was logged successfully.
	if ( $log_id && $tracking_id ) {
		// Get recipient email from log_data.
		$recipient_email = isset( $log_data['to_email'] ) ? $log_data['to_email'] : '';

		// Only create tracking pixel if Analytics DB class is available.
		if ( class_exists( 'Authority_Mailer_Analytics_DB' ) ) {
			$analytics_db = Authority_Mailer_Analytics_DB::get_instance();

			$pixel_data = array(
				'email_log_id'    => $log_id,
				'tracking_id'     => $tracking_id,
				'recipient_email' => $recipient_email,
			);

			$pixel_id = $analytics_db->insert_tracking_pixel( $pixel_data );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				if ( $pixel_id ) {
										// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
										error_log( '[Authority Mailer Logger Helper] Tracking pixel record created with ID: ' . $pixel_id . ' for tracking_id: ' . $tracking_id );
				} else {
										// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
										error_log( '[Authority Mailer Logger Helper] Failed to create tracking pixel record for tracking_id: ' . $tracking_id );
				}
			}
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Logger Helper] Analytics DB class not available, skipping tracking pixel creation' );
		}
	}

	return $log_id;
}

/**
 * Insert a new email log entry into the database.
 *
 * @param array $data {
 *     Email data to log.
 *
 *     @type string $provider      Email provider name.
 *     @type string $to_email      Recipient email address.
 *     @type string $from_email    Sender email address.
 *     @type string $from_name     Sender name.
 *     @type string $subject       Email subject.
 *     @type string $headers       Email headers.
 *     @type string $body          Email body content.
 *     @type mixed  $payload       Email payload (will be JSON encoded if array/object).
 *     @type int    $response_code HTTP response code.
 *     @type string $response_body HTTP response body.
 *     @type string $status        Email status (attempt, success, error).
 *     @type int    $spam_score    Spam score (0-100).
 * }
 * @return int|false The inserted row ID on success, false on failure.
 */
function authority_mailer_smtp_email_logger_insert( $data = array() ) {
	global $wpdb;
	$table = $wpdb->prefix . 'am_email_log';

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Email Logger] Insert called with keys: ' . wp_json_encode( array_keys( $data ) ) );
		if ( isset( $data['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Email Logger] Spam score in data: ' . $data['spam_score'] );
		} else {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Email Logger] No spam score in data' );
		}
	}

	$now = current_time( 'mysql', 1 );

	$defaults = array(
		'provider'      => '',
		'to_email'      => '',
		'from_email'    => '',
		'from_name'     => '',
		'subject'       => '',
		'headers'       => '',
		'body'          => '',
		'payload'       => '',
		'response_code' => null,
		'response_body' => '',
		'status'        => 'attempt',
		'spam_score'    => null,
		'spam_details'  => null,
	);

	$insert = wp_parse_args( $data, $defaults );

	if ( is_array( $insert['payload'] ) || is_object( $insert['payload'] ) ) {
		$insert['payload'] = wp_json_encode( $insert['payload'] );
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
	$success = $wpdb->insert(
		$table,
		array(
			'created_at'    => $now,
			'provider'      => sanitize_text_field( $insert['provider'] ),
			'to_email'      => sanitize_email( $insert['to_email'] ),
			'from_email'    => sanitize_email( $insert['from_email'] ),
			'from_name'     => sanitize_text_field( $insert['from_name'] ),
			'subject'       => sanitize_text_field( $insert['subject'] ),
			'headers'       => is_string( $insert['headers'] ) ? $insert['headers'] : wp_json_encode( $insert['headers'] ),
			'body'          => (string) $insert['body'],
			'payload'       => (string) $insert['payload'],
			'response_code' => is_null( $insert['response_code'] ) ? null : intval( $insert['response_code'] ),
			'response_body' => (string) $insert['response_body'],
			'status'        => sanitize_text_field( $insert['status'] ),
			'spam_score'    => is_null( $insert['spam_score'] ) ? null : floatval( $insert['spam_score'] ),
			'spam_details'  => is_null( $insert['spam_details'] ) ? null : (string) $insert['spam_details'],
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%f',
			'%s',
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

	if ( false === $success ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Email Logger] Insert failed: ' . $wpdb->last_error );
		}
		return 0;
	}

	$insert_id = (int) $wpdb->insert_id;

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Email Logger] Email logged with ID: ' . $insert_id . ' | spam_score: ' . ( $insert['spam_score'] ?? 'null' ) );
	}

	// Fire delivered event for recipient tracking (only for successful emails).
	if ( in_array( $insert['status'], array( 'success', 'accepted', 'delivered' ), true ) && ! empty( $insert['to_email'] ) ) {
		$event_data = array(
			'event_type'   => 'delivered',
			'tracking_id'  => '',
			'email_log_id' => $insert_id,
			'metadata'     => array(
				'provider' => $insert['provider'],
				'subject'  => $insert['subject'],
			),
		);

		/**
		 * Fires when an email delivery event occurs.
		 *
		 * @since 1.0.0
		 *
		 * @param string $email      Recipient email address.
		 * @param array  $event_data Event data including type, tracking ID, and metadata.
		 */
		do_action( 'authority_mailer_email_event', $insert['to_email'], $event_data );
	}

	authority_mailer_smtp_email_logger_cache_bump();

	// Clear dashboard stats cache when new email is logged.
	if ( class_exists( 'Authority_Mailer_Dashboard_Stats' ) ) {
		Authority_Mailer_Dashboard_Stats::clear_cache();
	}

	/**
	 * Fires after an email is successfully logged.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $insert_id The inserted log entry ID.
	 * @param array $insert    The complete logged email data.
	 */
	do_action( 'authority_mailer_smtp_email_logged', $insert_id, $insert );

	return $insert_id;
}

/**
 * Update an existing email log entry.
 *
 * @param int   $id     The log entry ID to update.
 * @param array $fields Array of fields to update. Allowed fields: provider, to_email,
 *                      from_email, from_name, subject, headers, body, payload,
 *                      response_code, response_body, status, spam_score, spam_details.
 * @return bool True on success, false on failure.
 */
function authority_mailer_smtp_email_logger_update( $id, $fields = array() ) {
	global $wpdb;
	$table = $wpdb->prefix . 'am_email_log';

	if ( empty( $id ) || ! is_numeric( $id ) ) {
		return false;
	}

	$allowed = array( 'provider', 'to_email', 'from_email', 'from_name', 'subject', 'headers', 'body', 'payload', 'response_code', 'response_body', 'status', 'spam_score', 'spam_details', 'sent_at' );
	$update  = array();

	foreach ( $fields as $k => $v ) {
		if ( in_array( $k, $allowed, true ) ) {
			if ( 'payload' === $k && ( is_array( $v ) || is_object( $v ) ) ) {
				$v = wp_json_encode( $v );
			}
			$update[ $k ] = $v;
		}
	}

	// Auto-populate sent_at when status is changed to 'delivered' or 'accepted' (unless explicitly provided).
	if ( isset( $fields['status'] ) && in_array( $fields['status'], array( 'delivered', 'accepted' ), true ) && ! isset( $fields['sent_at'] ) ) {
		$update['sent_at'] = current_time( 'mysql' );
	}

	if ( empty( $update ) ) {
		return false;
	}

	$format = array();
	foreach ( $update as $k => $v ) {
		if ( 'response_code' === $k ) {
			$format[] = '%d';
		} elseif ( 'spam_score' === $k ) {
			$format[] = '%f';
		} else {
			$format[] = '%s';
		}
	}

	$where        = array( 'id' => intval( $id ) );
	$where_format = array( '%d' );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->update( $table, $update, $where, $format, $where_format );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( false !== $result ) {
		authority_mailer_smtp_email_logger_cache_bump();
		// Clear dashboard stats cache when email is updated.
		if ( class_exists( 'Authority_Mailer_Dashboard_Stats' ) ) {
			Authority_Mailer_Dashboard_Stats::clear_cache();
		}
	}

	return false !== $result;
}

/*
-----------------------
 * Read helpers (use caching)
 * ----------------------- */

if ( ! function_exists( 'authority_mailer_smtp_get_table_columns' ) ) {
	/**
	 * Get table columns with caching support.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table The table name to get columns for.
	 * @return array Array of column names.
	 */
	function authority_mailer_smtp_get_table_columns( $table ) {
		global $wpdb;
		$cols = array();

		if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
			return $cols;
		}

		$ver       = authority_mailer_smtp_email_logger_cache_version();
		$cache_key = 'authority_mailer_smtp_table_columns_' . md5( $table ) . '_v' . $ver;
		$cached    = wp_cache_get( $cache_key, 'authority_mailer_smtp_email_logger' );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$check = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! $check ) {
			wp_cache_set( $cache_key, $cols, 'authority_mailer_smtp_email_logger', 300 );
			return $cols;
		}

		// Table name has been validated with preg_match and checked to exist.
		// Safe to use directly in query construction.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results( 'SHOW COLUMNS FROM `' . esc_sql( $table ) . '`', ARRAY_A );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! $rows ) {
			wp_cache_set( $cache_key, $cols, 'authority_mailer_smtp_email_logger', 300 );
			return $cols;
		}
		foreach ( $rows as $r ) {
			if ( isset( $r['Field'] ) ) {
				$cols[] = $r['Field'];
			}
		}

		wp_cache_set( $cache_key, $cols, 'authority_mailer_smtp_email_logger', 300 );
		return $cols;
	}
}

/**
 * Get recent email log entries.
 *
 * @param int $limit Maximum number of entries to retrieve. Default 50.
 * @return array Array of email log entries.
 */
function authority_mailer_smtp_email_logger_get_recent( $limit = 50 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'am_email_log';
	$limit = intval( $limit );
	if ( $limit <= 0 ) {
		$limit = 50;
	}

	$ver       = authority_mailer_smtp_email_logger_cache_version();
	$cache_key = "authority_mailer_smtp_recent_{$limit}_v{$ver}";
	$cached    = wp_cache_get( $cache_key, 'authority_mailer_smtp_email_logger' );
	if ( false !== $cached ) {
		return $cached;
	}

	$table_clean   = str_replace( '`', '', $table );
	$table_clean   = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $table_clean );
	$escaped_table = esc_sql( $table_clean );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$escaped_table}` ORDER BY created_at DESC LIMIT %d", $limit ) );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	wp_cache_set( $cache_key, $results, 'authority_mailer_smtp_email_logger', 300 );
	return $results;
}

if ( ! function_exists( 'authority_mailer_smtp_email_logger_get' ) ) {
	/**
	 * Get a single email log entry by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The log entry ID.
	 * @return object|null The log entry object or null if not found.
	 */
	function authority_mailer_smtp_email_logger_get( $id ) {
		global $wpdb;

		$id = intval( $id );
		if ( $id <= 0 ) {
			return null;
		}

		$ver       = authority_mailer_smtp_email_logger_cache_version();
		$cache_key = "authority_mailer_smtp_row_{$id}_v{$ver}";
		$cached    = wp_cache_get( $cache_key, 'authority_mailer_smtp_email_logger' );
		if ( false !== $cached ) {
			return $cached;
		}

		if ( function_exists( 'authority_mailer_smtp_email_logger_table_name' ) ) {
			$table = call_user_func( 'authority_mailer_smtp_email_logger_table_name' );
		} else {
			$table = $wpdb->prefix . 'am_email_log';
		}

		$table_clean   = str_replace( '`', '', $table );
		$table_clean   = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $table_clean );
		$escaped_table = esc_sql( $table_clean );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$escaped_table}` WHERE id = %d LIMIT 1", $id ), ARRAY_A );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $row ) {
			wp_cache_set( $cache_key, $row, 'authority_mailer_smtp_email_logger', 300 );
		}

		return $row ? $row : null;
	}
}

/*
-----------------------
 * Shortcode and admin view helper
 * ----------------------- */

/**
 * Helper: try multiple decode attempts for a JSON-like string.
 *
 * Accepts:
 *  - raw JSON string
 *  - JSON with escaped slashes (\" etc)
 *  - JSON wrapped in quotes by accidental double-encoding
 *
 * Returns decoded array or null.
 */
if ( ! function_exists( 'authority_mailer_smtp_try_decode_json_like' ) ) {
	/**
	 * Try to decode JSON-like strings with multiple decoding strategies.
	 *
	 * Attempts various decoding strategies to handle encoded JSON strings.
	 * Returns decoded array or null.
	 *
	 * @since 1.0.0
	 *
	 * @param string $raw The raw string to decode.
	 * @return array|null Decoded array or null on failure.
	 */
	function authority_mailer_smtp_try_decode_json_like( $raw ) {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return null;
		}

		$tries = array();

		$tries[] = $raw;
		$tries[] = stripslashes( $raw );
		$tries[] = html_entity_decode( $raw, ENT_QUOTES | ENT_HTML5 );
		$tries[] = stripslashes( html_entity_decode( $raw, ENT_QUOTES | ENT_HTML5 ) );

		// If it looks wrapped in quotes, trim them
		$trim = trim( $raw );
		if ( ( substr( $trim, 0, 1 ) === '"' && substr( $trim, -1 ) === '"' ) || ( substr( $trim, 0, 1 ) === "'" && substr( $trim, -1 ) === "'" ) ) {
			$tries[] = substr( $trim, 1, -1 );
			$tries[] = stripslashes( substr( $trim, 1, -1 ) );
		}

		foreach ( $tries as $candidate ) {
			if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
				continue;
			}
			$first = ltrim( $candidate );
			if ( ( strpos( $first, '{' ) === 0 ) || ( strpos( $first, '[' ) === 0 ) ) {
				$decoded = @json_decode( $candidate, true );
				if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
					return $decoded;
				}
			}
		}
		return null;
	}
}

/**
 * Alias for authority_mailer_smtp_try_decode_json_like for backward compatibility.
 *
 * @param string $raw Raw string to decode.
 * @return mixed|null Decoded value or null.
 */
if ( ! function_exists( 'authority_mailer_try_decode_json_like' ) ) {
	function authority_mailer_try_decode_json_like( $raw ) {
		return authority_mailer_smtp_try_decode_json_like( $raw );
	}
}

/**
 * Shortcode handler for displaying email logs.
 *
 * Usage: [authority_mailer_email_logs limit="50"]
 *
 * @param array $atts {
 *     Shortcode attributes.
 *
 *     @type int $limit Number of log entries to display. Default 50.
 * }
 * @return string HTML output of the email logs table.
 */
function authority_mailer_smtp_email_logger_shortcode( $atts = array() ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		$msg = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'forbidden' ) : 'Forbidden';
		return '<p>' . esc_html( $msg ) . '</p>';
	}
	$atts = shortcode_atts( array( 'limit' => 50 ), $atts, 'authority_mailer_smtp_email_logs' );
	$rows = authority_mailer_smtp_email_logger_get_recent( intval( $atts['limit'] ) );

	$h_id       = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_id' ) : 'ID';
	$h_when     = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_when' ) : 'When (UTC)';
	$h_provider = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_provider' ) : 'Provider';
	$h_to       = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_to' ) : 'To';
	$h_from     = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_from' ) : 'From';
	$h_subject  = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_subject' ) : 'Subject';
	$h_status   = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_status' ) : 'Status';
	$h_response = function_exists( 'authority_mailer_smtp_s' ) ? authority_mailer_smtp_s( 'log_header_response' ) : 'Response';

	$html  = '<div class="authority-mailer-smtp-email-logs"><table class="widefat striped"><thead><tr>';
	$html .= '<th>' . esc_html( $h_id ) . '</th><th>' . esc_html( $h_when ) . '</th><th>' . esc_html( $h_provider ) . '</th><th>' . esc_html( $h_to ) . '</th><th>' . esc_html( $h_from ) . '</th><th>' . esc_html( $h_subject ) . '</th><th>' . esc_html( $h_status ) . '</th><th>' . esc_html( $h_response ) . '</th>';
	$html .= '</tr></thead><tbody>';
	foreach ( $rows as $r ) {
		$created  = esc_html( mysql2date( 'Y-m-d H:i:s', $r->created_at, true ) );
		$subject  = esc_html( wp_trim_words( $r->subject, 10, '…' ) );
		$response = esc_html( $r->response_code ? $r->response_code : '' );
		$html    .= '<tr>';
		$html    .= '<td>' . intval( $r->id ) . '</td>';
		$html    .= '<td>' . $created . '</td>';
		$html    .= '<td>' . esc_html( $r->provider ) . '</td>';
		$html    .= '<td>' . esc_html( $r->to_email ) . '</td>';
		$html    .= '<td>' . esc_html( $r->from_email ) . '</td>';
		$html    .= '<td>' . $subject . '</td>';
		$html    .= '<td>' . esc_html( $r->status ) . '</td>';
		$html    .= '<td>' . $response . '</td>';
		$html    .= '</tr>';

		// Render expanded "Email Body" row under each entry with improved extraction/formatting.
		$raw_body    = isset( $r->body ) ? (string) $r->body : '';
		$raw_payload = isset( $r->payload ) ? (string) $r->payload : '';

		$body_display = '';

		// Prefer extracting from body column if it contains non-trivial content that is not just the entire payload repeated.
		$used_source = '';

		// Try to decode body if it's JSON-like (handles escaped JSON etc)
		$decoded = authority_mailer_try_decode_json_like( $raw_body );
		if ( is_array( $decoded ) ) {
			// Extract best subject/body candidate from payload array
			if ( function_exists( 'authority_mailer_smtp_extract_subject_and_body_from_payload' ) ) {
				list( $maybe_subject, $maybe_body ) = authority_mailer_smtp_extract_subject_and_body_from_payload( $decoded );
				if ( ! empty( $maybe_body ) ) {
					$body_display = $maybe_body;
					$used_source  = 'body (decoded JSON)';
				}
				if ( empty( $r->subject ) && ! empty( $maybe_subject ) ) {
					$r->subject = $maybe_subject;
				}
			}
		}

		// If still empty, try to extract from payload column
		if ( '' === trim( $body_display ) && '' !== trim( $raw_payload ) ) {
			$decoded2 = authority_mailer_try_decode_json_like( $raw_payload );
			if ( is_array( $decoded2 ) ) {
				if ( function_exists( 'authority_mailer_smtp_extract_subject_and_body_from_payload' ) ) {
					list( $maybe_subject2, $maybe_body2 ) = authority_mailer_smtp_extract_subject_and_body_from_payload( $decoded2 );
					if ( ! empty( $maybe_body2 ) ) {
						$body_display = $maybe_body2;
						$used_source  = 'payload (decoded JSON)';
					}
					if ( empty( $r->subject ) && ! empty( $maybe_subject2 ) ) {
						$r->subject = $maybe_subject2;
					}
				}
			}
		}

		// If still empty, if body column contains HTML tags or plain text, use it directly
		if ( '' === trim( $body_display ) && '' !== trim( $raw_body ) ) {
			// If body looks like JSON but decoding failed, try a last-resort unescape
			$maybe_plain = $raw_body;
			// remove leading/trailing quotes if present
			$tb = trim( $maybe_plain );
			if ( ( substr( $tb, 0, 1 ) === '"' && substr( $tb, -1 ) === '"' ) || ( substr( $tb, 0, 1 ) === "'" && substr( $tb, -1 ) === "'" ) ) {
				$maybe_plain = substr( $tb, 1, -1 );
			}
			// unescape common sequences
			$maybe_plain = stripslashes( $maybe_plain );
			// If looks like HTML, keep; otherwise keep as text
			$body_display = $maybe_plain;
			$used_source  = 'body (raw)';
		}

		// Final fallback: if nothing else, show payload raw
		if ( '' === trim( $body_display ) && '' !== trim( $raw_payload ) ) {
			$body_display = $raw_payload;
			$used_source  = 'payload (raw)';
		}

		// If still empty, leave blank
		if ( '' === trim( $body_display ) ) {
			$body_display = '';
			$used_source  = '';
		}

		// Decide whether content contains HTML
		$is_html = (bool) preg_match( '/<\s*\/?\w+.*?>/', $body_display );

		$body_render = '';
		if ( $is_html ) {
			// sanitize HTML but keep common formatting tags
			$body_render = wp_kses_post( $body_display );
		} else {
			$body_render = nl2br( esc_html( $body_display ) );
		}

		$html .= '<tr class="authority-mailer-log-body-row"><td colspan="8" style="background:#fff;">';
		$html .= '<div style="padding:12px;">';
		$html .= '<strong>' . authority_mailer_smtp_get_string( 'email_body_title' ) . '</strong>';
		if ( $used_source ) {
			$html .= ' <small style="color:#666;margin-left:8px;">' . esc_html( '(' . $used_source . ')' ) . '</small>';
		}
		$html .= '<div style="margin-top:8px;border:1px solid #e6e6e6;padding:12px;background:#fafafa;">';
		$html .= $body_render;
		$html .= '</div>';

		// Always expose raw payload for debugging (collapsed)
		if ( '' !== $raw_payload ) {
			$html .= '<details style="margin-top:8px;"><summary>' . authority_mailer_smtp_get_string( 'email_logger_view_raw_payload' ) . '</summary>';
			$html .= '<pre style="white-space:pre-wrap;word-wrap:break-word;background:#222;color:#cfcfcf;padding:12px;border-radius:4px;margin-top:8px;">' . esc_html( $raw_payload ) . '</pre>';
			$html .= '</details>';
		}

		// Also expose raw body and raw headers briefly
		if ( '' !== $raw_body ) {
			$html .= '<details style="margin-top:8px;"><summary>' . authority_mailer_smtp_get_string( 'email_logger_view_raw_body' ) . '</summary>';
			$html .= '<pre style="white-space:pre-wrap;word-wrap:break-word;background:#222;color:#cfcfcf;padding:12px;border-radius:4px;margin-top:8px;">' . esc_html( $raw_body ) . '</pre>';
			$html .= '</details>';
		}

		if ( ! empty( $r->headers ) ) {
			$html .= '<details style="margin-top:8px;"><summary>' . authority_mailer_smtp_get_string( 'email_logger_view_raw_headers' ) . '</summary>';
			$html .= '<pre style="white-space:pre-wrap;word-wrap:break-word;background:#222;color:#cfcfcf;padding:12px;border-radius:4px;margin-top:8px;">' . esc_html( $r->headers ) . '</pre>';
			$html .= '</details>';
		}

		$html .= '</div></td></tr>';
	}
	$html .= '</tbody></table></div>';
	return $html;
}
add_shortcode( 'authority_mailer_smtp_email_logs', 'authority_mailer_smtp_email_logger_shortcode' );

// Table creation removed - now handled on plugin activation only.
