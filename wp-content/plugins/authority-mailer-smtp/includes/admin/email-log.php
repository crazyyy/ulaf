<?php
/**
 * Authority Mailer SMTP - Email Log Admin Page
 *
 * This file provides the Email Log listing and detail views with consistent
 * styling matching the dashboard and onboarding wizard.
 *
 * @package Authority_Mailer
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

/**
 * Ensure email logger functions are loaded.
 *
 * @return void
 */
function authority_mailer_smtp_ensure_email_logger() {
	if ( ! function_exists( 'authority_mailer_smtp_email_logger_cache_version' ) ) {
		$logger_path = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/email-logger.php';
		if ( file_exists( $logger_path ) ) {
			require_once $logger_path;
		}
	}
}

// Ensure email logger functions are available.
authority_mailer_smtp_ensure_email_logger();

// Load dashboard stats class for cache invalidation.
if ( ! class_exists( 'Authority_Mailer_Dashboard_Stats' ) ) {
	$stats_class_path = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/class-authority-mailer-dashboard-stats.php';
	if ( file_exists( $stats_class_path ) ) {
		require_once $stats_class_path;
	}
}

/* Lightweight string helper (uses global $AUTHORITY_MAILER_STRINGS when available). */
if ( ! function_exists( 'authority_mailer_smtp_str' ) ) {
	/**
	 * Get a string from the AUTHORITY_MAILER_STRINGS global array.
	 *
	 * @param string $key      The string key.
	 * @param string $fallback Fallback value if key not found.
	 * @return string
	 */
	function authority_mailer_smtp_str( $key, $fallback = '' ) {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global constant convention.
		global $AUTHORITY_MAILER_STRINGS;
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global constant convention.
		if ( isset( $AUTHORITY_MAILER_STRINGS ) && is_array( $AUTHORITY_MAILER_STRINGS ) && array_key_exists( $key, $AUTHORITY_MAILER_STRINGS ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global constant convention.
			$value = $AUTHORITY_MAILER_STRINGS[ $key ];
			if ( is_string( $value ) ) {
				return $value;
			}
			if ( is_array( $value ) ) {
				return wp_json_encode( $value );
			}
			return (string) $value;
		}
		return $fallback ? $fallback : $key;
	}
}

/*
Permission guard */
// Redirect to login if not authenticated
if ( ! is_user_logged_in() ) {
	auth_redirect();
	return;
}

// Check capability for logged-in users
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( authority_mailer_smtp_get_string( 'no_permission' ) ) );
}

/**
 * Enqueue assets for this admin page only
 *
 * @param string $hook_suffix The current admin page hook.
 *
 * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter required by WordPress action hook.
 */
function authority_mailer_smtp_email_log_enqueue_assets( $hook_suffix ) {
	// Only load on email log page.
	if ( ! Authority_Mailer_Admin_Assets::is_authority_mailer_page( 'email-log' ) ) {
		return;
	}

	// Enqueue common admin styles.
	Authority_Mailer_Admin_Assets::enqueue_common_admin_styles();

	// Enqueue email log specific assets.
	Authority_Mailer_Admin_Assets::enqueue_email_log_assets();

	// Localize email log script.
	wp_localize_script(
		'authority-mailer-smtp-email-log-js',
		'authorityMailerEmailLog',
		array(
			'ajax_url'           => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'nonce'              => wp_create_nonce( 'authority_mailer_smtp_email_log_nonce' ),
			'action_delete'      => 'authority_mailer_smtp_delete_email_log',
			'action_bulk_delete' => 'authority_mailer_smtp_bulk_delete_email_logs',
			'action_resend'      => 'authority_mailer_smtp_resend_email_from_log',
			'strings'            => array(
				'confirm_resend'      => authority_mailer_smtp_get_string( 'confirm_resend' ),
				'resend_attempted'    => authority_mailer_smtp_get_string( 'resend_attempted' ),
				'resend_failed'       => authority_mailer_smtp_get_string( 'resend_failed' ),
				'confirm_delete'      => authority_mailer_smtp_get_string( 'confirm_delete' ),
				'no_rows_selected'    => authority_mailer_smtp_get_string( 'no_rows_selected' ),
				'select_bulk_action'  => authority_mailer_smtp_get_string( 'select_bulk_action' ),
				'bulk_delete_confirm' => authority_mailer_smtp_get_string( 'bulk_delete_confirm' ),
				'bulk_delete_failed'  => authority_mailer_smtp_get_string( 'bulk_delete_failed' ),
				'collapse'            => authority_mailer_smtp_get_string( 'collapse' ),
				'expand'              => authority_mailer_smtp_get_string( 'expand' ),
				'unknown_bulk_action' => authority_mailer_smtp_get_string( 'unknown_bulk_action' ),
				'delete_success'      => authority_mailer_smtp_get_string( 'delete_success' ),
				'delete_failed'       => authority_mailer_smtp_get_string( 'delete_failed' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'authority_mailer_smtp_email_log_enqueue_assets' );

/**
 * AJAX handler: Delete single email log entry.
 */
function authority_mailer_smtp_ajax_delete_email_log() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'permission_denied' ) ), 403 );
	}

	check_ajax_referer( 'authority_mailer_smtp_email_log_nonce', 'nonce' );

	$log_id = isset( $_POST['log_id'] ) ? absint( wp_unslash( $_POST['log_id'] ) ) : 0;
	if ( ! $log_id ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'invalid_log_id' ) ), 400 );
	}

	global $wpdb;
	$table = authority_mailer_smtp_email_logger_table_name();

	// Verify table exists and is valid.
	if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'invalid_table' ) ), 500 );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->delete(
		$table,
		array( 'id' => $log_id ),
		array( '%d' )
	);

	if ( false === $deleted ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'delete_failed' ) ), 500 );
	}

	// Clear dashboard stats cache after deletion.
	if ( class_exists( 'Authority_Mailer_Dashboard_Stats' ) ) {
		Authority_Mailer_Dashboard_Stats::clear_cache();
	}

	wp_send_json_success(
		array(
			'message'    => authority_mailer_smtp_get_string( 'log_entry_deleted' ),
			'deleted_id' => $log_id,
		)
	);
}
add_action( 'wp_ajax_authority_mailer_smtp_delete_email_log', 'authority_mailer_smtp_ajax_delete_email_log' );

/**
 * AJAX handler: Bulk delete email log entries.
 */
function authority_mailer_smtp_ajax_bulk_delete_email_logs() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'permission_denied' ) ), 403 );
	}

	check_ajax_referer( 'authority_mailer_smtp_email_log_nonce', 'nonce' );

	$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
	$ids = array_filter( $ids ); // Remove zeros.

	if ( empty( $ids ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'no_valid_ids' ) ), 400 );
	}

	global $wpdb;
	$table = authority_mailer_smtp_email_logger_table_name();

	// Verify table exists and is valid.
	if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'invalid_table' ) ), 500 );
	}

	$placeholders  = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
	$escaped_table = esc_sql( $table );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM `{$escaped_table}` WHERE id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			...$ids
		)
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( false === $deleted ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'failed_delete_log_entries' ) ), 500 );
	}

	// Clear dashboard stats cache after deletion.
	if ( class_exists( 'Authority_Mailer_Dashboard_Stats' ) ) {
		Authority_Mailer_Dashboard_Stats::clear_cache();
	}

	wp_send_json_success(
		array(
			'message'       => sprintf(
				/* translators: %d: number of deleted entries */
				_n( '%d log entry deleted.', '%d log entries deleted.', $deleted, 'authority-mailer-smtp' ),
				$deleted
			),
			'deleted_count' => $deleted,
		)
	);
}
add_action( 'wp_ajax_authority_mailer_smtp_bulk_delete_email_logs', 'authority_mailer_smtp_ajax_bulk_delete_email_logs' );

/**
 * AJAX handler: Resend email from log.
 *
 * This handler uses the email log nonce for security and delegates to the
 * onboarding class resend logic if available, or performs its own resend.
 */
function authority_mailer_smtp_ajax_resend_email_from_log() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'permission_denied' ) ), 403 );
	}

	check_ajax_referer( 'authority_mailer_smtp_email_log_nonce', 'nonce' );

	$log_id = isset( $_POST['log_id'] ) ? absint( wp_unslash( $_POST['log_id'] ) ) : 0;
	if ( ! $log_id ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'invalid_log_id' ) ), 400 );
	}

	// Get the log entry.
	$log = authority_mailer_smtp_email_logger_get( $log_id );
	if ( ! $log || ! is_array( $log ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'email_log_entry_not_found' ) ), 404 );
	}

	$provider = isset( $log['provider'] ) ? sanitize_key( $log['provider'] ) : '';
	if ( empty( $provider ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'provider_not_recorded' ) ), 400 );
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	// Build settings from saved provider config.
	$settings = array();
	if ( isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ) {
		$settings = $options[ $provider ];
	} else {
		$settings = $options;
	}

	// Use original recipient/from/subject from the log.
	if ( ! empty( $log['to_email'] ) ) {
		$settings['test_recipient'] = sanitize_email( $log['to_email'] );
	}
	if ( ! empty( $log['from_email'] ) ) {
		$settings[ $provider . '_from_email' ] = sanitize_email( $log['from_email'] );
		$settings['from_email']                = sanitize_email( $log['from_email'] );
	}
	if ( ! empty( $log['from_name'] ) ) {
		$settings[ $provider . '_from_name' ] = sanitize_text_field( $log['from_name'] );
		$settings['from_name']                = sanitize_text_field( $log['from_name'] );
	}
	if ( ! empty( $log['subject'] ) ) {
		$settings['test_subject'] = sanitize_text_field( $log['subject'] );
	}

	// Try to extract body from payload if available.
	if ( ! empty( $log['payload'] ) ) {
		$payload_arr = null;
		if ( is_string( $log['payload'] ) ) {
			$payload_arr = json_decode( $log['payload'], true );
		} elseif ( is_array( $log['payload'] ) ) {
			$payload_arr = $log['payload'];
		}

		if ( is_array( $payload_arr ) ) {
			if ( ! empty( $payload_arr['Messages'][0]['HTMLPart'] ) ) {
				$settings['html_content'] = (string) $payload_arr['Messages'][0]['HTMLPart'];
			}
			if ( ! empty( $payload_arr['Messages'][0]['TextPart'] ) ) {
				$settings['plain_content'] = (string) $payload_arr['Messages'][0]['TextPart'];
			}
			if ( empty( $settings['html_content'] ) && ! empty( $payload_arr['html'] ) ) {
				$settings['html_content'] = (string) $payload_arr['html'];
			}
			if ( empty( $settings['plain_content'] ) && ! empty( $payload_arr['text'] ) ) {
				$settings['plain_content'] = (string) $payload_arr['text'];
			}
		}
	}

	// Try to extract body from log if stored separately.
	if ( empty( $settings['html_content'] ) && ! empty( $log['body'] ) ) {
		$settings['html_content'] = $log['body'];
	}

	// Fallbacks.
	if ( empty( $settings['test_recipient'] ) ) {
		$settings['test_recipient'] = get_option( 'admin_email', '' );
	}
	if ( empty( $settings['from_email'] ) ) {
		$settings['from_email'] = get_option( 'admin_email', '' );
	}
	if ( empty( $settings['from_name'] ) ) {
		$settings['from_name'] = get_bloginfo( 'name' );
	}

	// Load the test runner.
	$testers_file = defined( 'AUTHORITY_MAILER_PLUGIN_DIR' ) ? AUTHORITY_MAILER_PLUGIN_DIR . 'includes/testers.php' : plugin_dir_path( __DIR__ ) . 'testers.php';
	if ( ! file_exists( $testers_file ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'test_runner_not_available' ) ), 500 );
	}
	require_once $testers_file;

	if ( ! function_exists( 'authority_mailer_smtp_test_provider' ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'test_runner_function_not_found' ) ), 500 );
	}

	$steps = authority_mailer_smtp_test_provider( $provider, $settings );
	if ( ! is_array( $steps ) ) {
		$steps = array(
			array(
				'status'  => 'error',
				'message' => authority_mailer_smtp_get_string( 'resend_invalid_data' ),
			),
		);
	}

	// Check if any step indicates success.
	$success = false;
	$message = authority_mailer_smtp_get_string( 'resend_attempted' );
	foreach ( $steps as $step ) {
		if ( isset( $step['status'] ) && 'success' === $step['status'] ) {
			$success = true;
			$message = isset( $step['message'] ) ? $step['message'] : authority_mailer_smtp_get_string( 'email_resent_successfully' );
			break;
		}
		if ( isset( $step['status'] ) && 'error' === $step['status'] ) {
			$message = isset( $step['message'] ) ? $step['message'] : authority_mailer_smtp_get_string( 'resend_failed' );
		}
	}

	if ( $success ) {
		wp_send_json_success(
			array(
				'message' => $message,
				'steps'   => $steps,
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => $message,
				'steps'   => $steps,
			),
			500
		);
	}
}
add_action( 'wp_ajax_authority_mailer_smtp_resend_email_from_log', 'authority_mailer_smtp_ajax_resend_email_from_log' );

/**
 * Get the email log table name.
 *
 * @return string
 */
function authority_mailer_smtp_email_logger_table_name() {
	global $wpdb;
	// Always use the standard table name - no fallbacks.
	// Table is created on plugin activation via Authority_Mailer_Database_Setup.
	return $wpdb->prefix . 'am_email_log';
}

/**
 * Get columns for a table.
 *
 * @param string $table Table name.
 * @return array
 */
function authority_mailer_smtp_get_table_columns( $table ) {
	global $wpdb;
	$cols = array();
	if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
		return $cols;
	}

	// Check cache first.
	$cache_key   = 'table_columns_' . md5( $table );
	$cache_group = 'authority_mailer_smtp';
	$cached_cols = wp_cache_get( $cache_key, $cache_group );
	if ( false !== $cached_cols && is_array( $cached_cols ) ) {
		return $cached_cols;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$check = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	if ( ! $check ) {
		return $cols;
	}
	// Table name has been validated with preg_match and checked to exist.
	// Safe to use directly in query construction.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_results( 'SHOW COLUMNS FROM `' . esc_sql( $table ) . '`', ARRAY_A );
	if ( ! $rows ) {
		return $cols;
	}
	foreach ( $rows as $r ) {
		if ( isset( $r['Field'] ) ) {
			$cols[] = $r['Field'];
		}
	}

	// Cache the result for 1 hour.
	wp_cache_set( $cache_key, $cols, $cache_group, 3600 );

	return $cols;
}

/**
 * Fetch paginated email log rows.
 *
 * @param array $args Query arguments.
 * @return array
 */
function authority_mailer_smtp_email_logger_fetch( $args = array() ) {
	global $wpdb;
	$table = authority_mailer_smtp_email_logger_table_name();

	$paged    = ! empty( $args['paged'] ) ? max( 1, intval( $args['paged'] ) ) : 1;
	$per_page = ! empty( $args['per_page'] ) ? intval( $args['per_page'] ) : 10;
	$offset   = ( $paged - 1 ) * $per_page;

	$where   = array();
	$prepare = array();

	if ( ! empty( $args['search'] ) ) {
		$s       = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
		$where[] = '( to_email LIKE %s OR from_email LIKE %s OR subject LIKE %s )';
		array_push( $prepare, $s, $s, $s );
	}
	if ( ! empty( $args['provider'] ) ) {
		$where[]   = 'provider = %s';
		$prepare[] = sanitize_text_field( $args['provider'] );
	}
	if ( ! empty( $args['status'] ) ) {
		$where[]   = 'status = %s';
		$prepare[] = sanitize_text_field( $args['status'] );
	}
	if ( ! empty( $args['date_from'] ) ) {
		$where[]   = 'created_at >= %s';
		$prepare[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
	}
	if ( ! empty( $args['date_to'] ) ) {
		$where[]   = 'created_at <= %s';
		$prepare[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
	}

	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	$available_cols = authority_mailer_smtp_get_table_columns( $table );
	$desired        = array( 'id', 'created_at', 'provider', 'to_email', 'from_email', 'from_name', 'subject', 'status', 'response_code', 'spam_score' );
	$select_cols    = array();
	foreach ( $desired as $c ) {
		if ( in_array( $c, $available_cols, true ) ) {
			$select_cols[] = $c;
		}
	}
	if ( empty( $select_cols ) ) {
		return array(
			'total'    => 0,
			'rows'     => array(),
			'per_page' => $per_page,
			'paged'    => $paged,
		);
	}

	$select_sql_parts = array_map(
		function ( $col ) {
			return '`' . esc_sql( $col ) . '`';
		},
		$select_cols
	);
	$select_sql       = implode( ', ', $select_sql_parts );

	$escaped_table = esc_sql( $table );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
	$sql_count = "SELECT COUNT(1) FROM `{$escaped_table}` {$where_sql}";
	$total     = $prepare ? intval( $wpdb->get_var( $wpdb->prepare( $sql_count, ...$prepare ) ) ) : intval( $wpdb->get_var( $sql_count ) );

	$sql    = "SELECT {$select_sql} FROM `{$escaped_table}` {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
	$params = array_merge( $prepare, array( $per_page, $offset ) );
	$rows   = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

	return array(
		'total'    => $total,
		'rows'     => $rows,
		'per_page' => $per_page,
		'paged'    => $paged,
	);
}

/**
 * Get single email log row.
 *
 * @param int $id Log ID.
 * @return array|false
 */
function authority_mailer_smtp_email_logger_get( $id ) {
	global $wpdb;
	$table = authority_mailer_smtp_email_logger_table_name();
	if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
		return false;
	}

	// Check cache first.
	$cache_key     = 'email_log_entry_' . intval( $id );
	$cache_group   = 'authority_mailer_smtp';
	$cache_version = (int) authority_mailer_smtp_email_logger_cache_version();
	$cached_result = wp_cache_get( $cache_key . '_' . $cache_version, $cache_group );
	if ( false !== $cached_result ) {
		return $cached_result;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM `' . esc_sql( $table ) . '` WHERE id = %d LIMIT 1',
			intval( $id )
		),
		ARRAY_A
	);

	// Cache the result for 5 minutes.
	if ( $result ) {
		wp_cache_set( $cache_key . '_' . $cache_version, $result, $cache_group, 300 );
	}

	return $result;
}

/**
 * Get distinct providers from the log.
 *
 * @return array
 */
function authority_mailer_smtp_email_logger_get_providers() {
	global $wpdb;
	$table = authority_mailer_smtp_email_logger_table_name();

	// Validate table name to prevent SQL injection.
	if ( ! is_string( $table ) || ! preg_match( '/^[A-Za-z0-9_\-\.]+$/', $table ) ) {
		return array();
	}

	// Check cache first.
	$cache_key     = 'email_log_providers';
	$cache_group   = 'authority_mailer_smtp';
	$cache_version = (int) authority_mailer_smtp_email_logger_cache_version();
	$cached_result = wp_cache_get( $cache_key . '_' . $cache_version, $cache_group );
	if ( false !== $cached_result && is_array( $cached_result ) ) {
		return $cached_result;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$results = $wpdb->get_col(
		$wpdb->prepare(
			'SELECT DISTINCT provider FROM `' . esc_sql( $table ) . '` WHERE provider IS NOT NULL AND provider != %s ORDER BY provider ASC',
			''
		)
	);
	$results = is_array( $results ) ? $results : array();

	// Cache the result for 5 minutes.
	wp_cache_set( $cache_key . '_' . $cache_version, $results, $cache_group, 300 );

	return $results;
}

/* AJAX handlers remain the same - include them from the original file or keep here */

/**
 * Render the main Email Log page.
 */
function authority_mailer_smtp_render_email_log_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view_id = isset( $_GET['view_id'] ) ? absint( wp_unslash( $_GET['view_id'] ) ) : 0;

	if ( $view_id ) {
		authority_mailer_smtp_render_email_log_view( $view_id );
		return;
	}

	// Get connected provider for header - ensure it's always a string for PHP 8.1+ compatibility.
	$options            = get_option( 'authority_mailer_smtp_options', array() );
	$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';

	// Get filter values.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$search    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$provider  = isset( $_GET['provider'] ) ? sanitize_text_field( wp_unslash( $_GET['provider'] ) ) : '';
	$status    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
	$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
	$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
	$paged     = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
	$per_page  = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 10;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$result      = authority_mailer_smtp_email_logger_fetch(
		array(
			'search'    => $search,
			'provider'  => $provider,
			'status'    => $status,
			'date_from' => $date_from,
			'date_to'   => $date_to,
			'paged'     => $paged,
			'per_page'  => $per_page,
		)
	);
	$rows        = $result['rows'];
	$total       = $result['total'];
	$total_pages = ceil( $total / $per_page );
	$providers   = authority_mailer_smtp_email_logger_get_providers();

	// Render header using the shared function.
	?>
	<div class="am-wrap">
		<div class="am-container">
			<?php
			if ( function_exists( 'authority_mailer_smtp_render_admin_header' ) ) {
				authority_mailer_smtp_render_admin_header( 'email-log', $connected_provider );
			}
			?>
		</div>
	</div>
	<div class="am-wrap am-email-log">
		<div class="am-container">

			<!-- Email Log Card -->
			<div class="am-card">
				<div class="am-card-header">
					<h2 class="am-card-title">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
						<?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_title' ) ); ?>
					</h2>
					<span class="am-card-meta">
						<?php
						printf(
							/* translators: %d: total number of emails */
							esc_html( _n( '%d email logged', '%d emails logged', $total, 'authority-mailer-smtp' ) ),
							(int) $total
						);
						?>
					</span>
				</div>

				<!-- Filter Form -->
				<form method="get" class="am-filter-form">
					<input type="hidden" name="page" value="authority-mailer-smtp-email-log" />
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( authority_mailer_smtp_get_string( 'email_log_search_placeholder' ) ); ?>" class="am-form-input am-max-w-200" />
					<select name="provider" class="am-form-select am-max-w-150">
						<option value=""><?php echo esc_html( authority_mailer_smtp_get_string( 'label_all_providers' ) ); ?></option>
						<?php foreach ( $providers as $p ) : ?>
							<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $provider, $p ); ?>><?php echo esc_html( strtoupper( $p ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<select name="status" class="am-form-select am-max-w-130">
						<option value=""><?php echo esc_html( authority_mailer_smtp_get_string( 'label_all_statuses' ) ); ?></option>
						<option value="attempt" <?php selected( $status, 'attempt' ); ?>><?php echo esc_html( authority_mailer_smtp_get_string( 'status_attempt' ) ); ?></option>
						<option value="success" <?php selected( $status, 'success' ); ?>><?php echo esc_html( authority_mailer_smtp_get_string( 'status_success' ) ); ?></option>
						<option value="error" <?php selected( $status, 'error' ); ?>><?php echo esc_html( authority_mailer_smtp_get_string( 'status_error' ) ); ?></option>
					</select>
					<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" placeholder="<?php echo esc_attr( authority_mailer_smtp_get_string( 'email_log_from_date' ) ); ?>" class="am-form-input am-max-w-140" />
					<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" placeholder="<?php echo esc_attr( authority_mailer_smtp_get_string( 'email_log_to_date' ) ); ?>" class="am-form-input am-max-w-140" />
					<select name="per_page" class="am-form-select am-max-w-120">
						<?php
						$sizes = array( 10, 25, 50, 100 );
						foreach ( $sizes as $s ) :
							?>
							<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $per_page, $s ); ?>>
								<?php echo esc_html( $s ); ?> <?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_per_page' ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="am-btn am-btn-primary">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
						<?php echo esc_html( authority_mailer_smtp_get_string( 'filter_button' ) ); ?>
					</button>
					<a class="am-btn am-btn-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log' ) ); ?>">
						<?php echo esc_html( authority_mailer_smtp_get_string( 'reset_button' ) ); ?>
					</a>
				</form>

				<!-- Toolbar -->
				<div class="am-toolbar">
					<div class="am-toolbar-left">
						<select id="authority-mailer-bulk-action" class="am-form-select am-max-w-150">
							<option value=""><?php echo esc_html( authority_mailer_smtp_get_string( 'bulk_actions_label' ) ); ?></option>
							<option value="delete"><?php echo esc_html( authority_mailer_smtp_get_string( 'bulk_action_delete' ) ); ?></option>
						</select>
						<button id="authority-mailer-bulk-apply" class="am-btn am-btn-secondary am-btn-sm">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'apply_button' ) ); ?>
						</button>
					</div>
					<div class="am-toolbar-right">
						<?php
						$start = 0 === $total ? 0 : ( ( $paged - 1 ) * $per_page ) + 1;
						$end   = min( $total, $paged * $per_page );
						printf(
							/* translators: %1$d: start number, %2$d: end number, %3$d: total */
							esc_html( authority_mailer_smtp_get_string( 'showing_results' ) ),
							(int) $start,
							(int) $end,
							(int) $total
						);
						?>
					</div>
				</div>

				<!-- Table -->
				<?php
				if ( ! empty( $rows ) ) :
					// Check once if spam checker is available to avoid duplicate conditionals.
					$show_spam_score = class_exists( 'Authority_Mailer_Spam_Checker' );
					?>
					<table class="am-table">
						<thead>
							<tr>
								<th class="am-max-w-40"><input id="authority-mailer-select-all" type="checkbox" /></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_date' ) ); ?></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_to' ) ); ?></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_from' ) ); ?></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_subject' ) ); ?></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_provider' ) ); ?></th>
								<?php if ( $show_spam_score ) : ?>
									<th><?php echo esc_html( authority_mailer_smtp_get_string( 'spam_score_column_title' ) ); ?></th>
								<?php endif; ?>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_status' ) ); ?></th>
								<th><?php echo esc_html( authority_mailer_smtp_get_string( 'label_actions' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $rows as $r ) :
								$id         = intval( $r['id'] );
								$row_status = isset( $r['status'] ) ? trim( strtolower( $r['status'] ) ) : 'unknown';

								$status_class = 'pending';
								if ( in_array( $row_status, array( 'success', 'accepted', 'sent' ), true ) ) {
									$status_class = 'success';
								} elseif ( in_array( $row_status, array( 'error', 'failed', 'bounce' ), true ) ) {
									$status_class = 'error';
								} elseif ( in_array( $row_status, array( 'pending', 'attempt', 'queued' ), true ) ) {
									$status_class = 'pending';
								}

								$row_date       = isset( $r['created_at'] ) ? $r['created_at'] : '';
								$date_formatted = '';
								$time_formatted = '';
								if ( $row_date ) {
									$ts = strtotime( $row_date );
									if ( $ts ) {
										$date_formatted = wp_date( 'M j, Y', $ts );
										$time_formatted = wp_date( 'g:i A', $ts );
									}
								}
								?>
								<tr id="authority-mailer-log-row-<?php echo esc_attr( $id ); ?>">
									<td><input class="authority-mailer-select-row" type="checkbox" data-id="<?php echo esc_attr( $id ); ?>" /></td>
									<td>
										<span class="am-text-bold am-text-gray-900"><?php echo esc_html( $date_formatted ); ?></span><br>
										<span class="am-text-xs am-text-gray"><?php echo esc_html( $time_formatted ); ?></span>
									</td>
									<td>
										<span class="am-max-w-180 am-text-ellipsis">
											<?php echo isset( $r['to_email'] ) ? esc_html( $r['to_email'] ) : ''; ?>
										</span>
									</td>
									<td>
										<span class="am-max-w-150 am-text-ellipsis am-text-gray">
											<?php echo isset( $r['from_email'] ) ? esc_html( $r['from_email'] ) : ''; ?>
										</span>
									</td>
									<td>
										<span class="am-max-w-200 am-text-ellipsis">
											<?php echo isset( $r['subject'] ) ? esc_html( $r['subject'] ) : ''; ?>
										</span>
									</td>
									<td>
										<?php if ( isset( $r['provider'] ) && $r['provider'] ) : ?>
											<span class="am-badge am-badge-gray">
												<?php echo esc_html( strtoupper( $r['provider'] ) ); ?>
											</span>
										<?php else : ?>
											<span class="am-text-gray-400">—</span>
										<?php endif; ?>
									</td>
									<?php if ( $show_spam_score ) : ?>
										<td>
											<?php
											$spam_score = isset( $r['spam_score'] ) ? intval( $r['spam_score'] ) : null;
											if ( null !== $spam_score ) :
												// Spam score thresholds for display classification (0-100 scale).
												$spam_score_error_threshold   = 60;
												$spam_score_warning_threshold = 40;

												$score_class = 'success';
												if ( $spam_score >= $spam_score_error_threshold ) {
													$score_class = 'error';
												} elseif ( $spam_score >= $spam_score_warning_threshold ) {
													$score_class = 'warning';
												}
												?>
												<span class="am-status-badge <?php echo esc_attr( $score_class ); ?>">
													<?php echo esc_html( $spam_score ); ?>
												</span>
											<?php else : ?>
												<span class="am-text-gray-400">—</span>
											<?php endif; ?>
										</td>
									<?php endif; ?>
									<td>
										<span class="am-status-badge <?php echo esc_attr( $status_class ); ?>">
											<?php echo isset( $r['status'] ) ? esc_html( strtoupper( $r['status'] ) ) : '—'; ?>
										</span>
									</td>
									<td>
										<div class="am-flex am-gap-2 am-items-center">
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log&view_id=' . $id ) ); ?>" class="am-btn am-btn-secondary am-btn-sm" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'btn_view' ) ); ?>">
												<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
											</a>
											<button class="am-btn am-btn-secondary am-btn-sm authority-mailer-resend" data-id="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'btn_resend' ) ); ?>">
												<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path></svg>
											</button>
											<button class="am-btn am-btn-secondary am-btn-sm authority-mailer-delete-log am-text-error" data-id="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'btn_delete' ) ); ?>">
												<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="am-empty-state">
						<div class="am-empty-icon">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
							</svg>
						</div>
						<p class="am-empty-title"><?php echo esc_html( authority_mailer_smtp_get_string( 'no_log_entries' ) ); ?></p>
						<p class="am-empty-text"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_no_emails_desc' ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="am-pagination">
						<?php
						$big  = 999999999;
						$base = str_replace( $big, '%#%', esc_url( add_query_arg( 'paged', $big ) ) );
						echo wp_kses_post(
							paginate_links(
								array(
									'base'      => $base,
									'format'    => '?paged=%#%',
									'current'   => max( 1, $paged ),
									'total'     => $total_pages,
									'prev_text' => '&laquo; ' . authority_mailer_smtp_get_string( 'pagination_prev' ),
									'next_text' => authority_mailer_smtp_get_string( 'pagination_next' ) . ' &raquo;',
								)
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
	<?php
}

/**
 * Render single email log detail view.
 *
 * @param int $id Log entry ID.
 */
function authority_mailer_smtp_render_email_log_view( $id ) {
	$row = authority_mailer_smtp_email_logger_get( $id );

	if ( ! $row ) {
		?>
		<div class="am-wrap">
			<div class="am-container">
				<div class="am-card">
					<div class="am-card-body">
						<div class="am-empty-state">
							<p class="am-empty-title"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_email_not_found' ) ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log' ) ); ?>" class="am-btn am-btn-primary">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_back_to_log' ) ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return;
	}

	// Get connected provider for header - ensure it's always a string for PHP 8.1+ compatibility.
	$options            = get_option( 'authority_mailer_smtp_options', array() );
	$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';

	// Render header.
	?>
	<div class="am-wrap">
		<div class="am-container">
			<?php
			if ( function_exists( 'authority_mailer_smtp_render_admin_header' ) ) {
				authority_mailer_smtp_render_admin_header( 'email-log', $connected_provider );
			}
			?>
		</div>
	</div>
	<div class="am-wrap am-email-log">
		<div class="am-container">
		</div>
	</div>
	<?php

	$status_class  = 'pending';
	$detail_status = isset( $row['status'] ) ? trim( strtolower( $row['status'] ) ) : 'unknown';

	if ( in_array( $detail_status, array( 'success', 'accepted', 'sent' ), true ) ) {
		$status_class = 'success';
	} elseif ( in_array( $detail_status, array( 'error', 'failed', 'bounce' ), true ) ) {
		$status_class = 'error';
	} elseif ( in_array( $detail_status, array( 'pending', 'attempt', 'queued' ), true ) ) {
		$status_class = 'pending';
	}

	$date_formatted = '';
	if ( ! empty( $row['created_at'] ) ) {
		$ts             = strtotime( $row['created_at'] );
		$date_formatted = $ts ? wp_date( 'F j, Y \a\t g:i A', $ts ) : $row['created_at'];
	}
	?>
	<div class="am-wrap am-email-log-view">
		<div class="am-container">
			<div class="am-mb-4">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log' ) ); ?>" class="am-btn am-btn-secondary">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
					<?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_back_link' ) ); ?>
				</a>
			</div>

			<div class="am-card">
				<div class="am-card-header">
					<h2 class="am-card-title">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
						<?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_email_details' ) ); ?>
					</h2>
					<div class="am-flex am-gap-4 am-items-center">
						<span class="am-status-badge <?php echo esc_attr( $status_class ); ?>">
							<?php echo esc_html( strtoupper( $row['status'] ) ); ?>
						</span>
						<button class="am-btn am-btn-secondary am-btn-sm authority-mailer-resend" data-id="<?php echo esc_attr( $id ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path></svg>
							<?php echo esc_html( authority_mailer_smtp_get_string( 'btn_resend' ) ); ?>
						</button>
					</div>
				</div>

				<div class="am-card-body">
					<div class="am-detail-grid">
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'label_to' ) ); ?></label>
							<span class="am-detail-value"><?php echo esc_html( $row['to_email'] ); ?></span>
						</div>
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'label_from' ) ); ?></label>
							<span class="am-detail-value">
								<?php
								$from_display = $row['from_email'];
								if ( ! empty( $row['from_name'] ) ) {
									$from_display = $row['from_name'] . ' <' . $row['from_email'] . '>';
								}
								echo esc_html( $from_display );
								?>
							</span>
						</div>
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'label_date' ) ); ?></label>
							<span class="am-detail-value"><?php echo esc_html( $date_formatted ); ?></span>
						</div>
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'label_subject' ) ); ?></label>
							<span class="am-detail-value"><?php echo esc_html( $row['subject'] ); ?></span>
						</div>
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'label_provider' ) ); ?></label>
							<span class="am-badge am-badge-gray">
								<?php echo esc_html( strtoupper( $row['provider'] ) ); ?>
							</span>
						</div>
						<?php if ( ! empty( $row['response_code'] ) ) : ?>
						<div class="am-detail-field">
							<label class="am-detail-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_response_code' ) ); ?></label>
							<span class="am-detail-value"><?php echo esc_html( $row['response_code'] ); ?></span>
						</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $row['body'] ) ) : ?>
					<div class="am-mt-6">
						<label class="am-detail-label am-mb-4"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_body_title' ) ); ?></label>
						<div class="am-email-body-preview">
							<?php
							// Detect if body contains HTML tags or if headers indicate HTML content.
							$is_html = false;
							if ( preg_match( '/<[a-z][^>]*>/i', $row['body'] ) ) {
								$is_html = true;
							} elseif ( ! empty( $row['headers'] ) && stripos( $row['headers'], 'Content-Type: text/html' ) !== false ) {
								$is_html = true;
							}

							if ( $is_html ) {
								// Render as HTML, preserving formatting.
								echo wp_kses_post( $row['body'] );
							} else {
								// Render as plain text with line breaks.
								echo nl2br( esc_html( $row['body'] ) );
							}
							?>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $row['headers'] ) ) : ?>
					<div class="am-mt-6">
						<label class="am-detail-label am-mb-4"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_headers_title' ) ); ?></label>
						<div class="am-code-block">
							<pre><?php echo esc_html( $row['headers'] ); ?></pre>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $row['response_body'] ) ) : ?>
					<div class="am-mt-6">
						<label class="am-detail-label am-mb-4"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_response_body_title' ) ); ?></label>
						<div class="am-code-block">
							<pre><?php echo esc_html( $row['response_body'] ); ?></pre>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $row['error_message'] ) ) : ?>
					<div class="am-error-block">
						<label class="am-error-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'email_log_error_message' ) ); ?></label>
						<pre><?php echo esc_html( $row['error_message'] ); ?></pre>
					</div>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
	<?php
}

return;
