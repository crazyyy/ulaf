<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC All Routes class.
 * 
 * This class centralizes all route registrations and security checks for the plugin.
 */
class ADBC_Premium_Routes {

	/**
	 * Register all ADBC routes.
	 * 
	 * @return void
	 */
	public static function register_routes() {

		// Scan routes.
		ADBC_Routes::register_route( '/start-scan', 'start_scan', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/get-remote-scan-results', 'get_remote_scan_results', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/check-remote-scan-status', 'check_remote_scan_status', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/stop-scan', 'stop_scan', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/scan-heartbeat', 'scan_heartbeat', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/remote-request-retries-reset', 'remote_request_retries_reset', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/get-remote-scan-balance', 'get_remote_scan_balance', WP_REST_Server::READABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/is-scan-exists', 'is_scan_exists', WP_REST_Server::EDITABLE, ADBC_Scan_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-cron-jobs', 'edit_scan_results_cron_jobs', WP_REST_Server::EDITABLE, ADBC_Cron_Jobs_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-users-meta', 'edit_scan_results_users_meta', WP_REST_Server::EDITABLE, ADBC_Users_Meta_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-posts-meta', 'edit_scan_results_posts_meta', WP_REST_Server::EDITABLE, ADBC_Posts_Meta_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-transients', 'edit_scan_results_transients', WP_REST_Server::EDITABLE, ADBC_Transients_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-options', 'edit_scan_results_options', WP_REST_Server::EDITABLE, ADBC_Options_Endpoints::class);
		ADBC_Routes::register_route( '/edit-scan-results-tables', 'edit_scan_results_tables', WP_REST_Server::EDITABLE, ADBC_Tables_Endpoints::class);

		// Analytics routes.
		ADBC_Routes::register_route( '/get-database-chart-data-by-day', 'get_database_chart_data_by_day', WP_REST_Server::EDITABLE, ADBC_Analytics_Endpoints::class);
		ADBC_Routes::register_route( '/get-database-chart-data-by-month', 'get_database_chart_data_by_month', WP_REST_Server::EDITABLE, ADBC_Analytics_Endpoints::class);
		ADBC_Routes::register_route( '/get-tables-chart-data-by-day', 'get_tables_chart_data_by_day', WP_REST_Server::EDITABLE, ADBC_Analytics_Endpoints::class);
		ADBC_Routes::register_route( '/get-tables-chart-data-by-month', 'get_tables_chart_data_by_month', WP_REST_Server::EDITABLE, ADBC_Analytics_Endpoints::class);
		ADBC_Routes::register_route( '/get-last-week-database-size', 'get_last_week_database_size', WP_REST_Server::READABLE, ADBC_Analytics_Endpoints::class);

		// Addons routes.
		ADBC_Routes::register_route( '/get-addons-list', 'get_addons_list', WP_REST_Server::READABLE, ADBC_Addons_Endpoints::class);
		ADBC_Routes::register_route( '/get-addons-activity-timeline', 'get_addons_activity_timeline', WP_REST_Server::EDITABLE, ADBC_Addons_Endpoints::class);
		ADBC_Routes::register_route( '/clear-addons-activity', 'clear_addons_activity', WP_REST_Server::EDITABLE, ADBC_Addons_Endpoints::class);

		// Automation routes.
		ADBC_Routes::register_route( '/automation/get-task-events-log', 'get_task_events_log', WP_REST_Server::EDITABLE, ADBC_Automation_Endpoints::class);
		ADBC_Routes::register_route( '/automation/clear-task-events-log', 'clear_task_events_log', WP_REST_Server::EDITABLE, ADBC_Automation_Endpoints::class);

		// Migration routes.
		ADBC_Routes::register_route( '/migration/get-available-migration-data', 'get_available_migration_data', WP_REST_Server::READABLE, ADBC_Migration_Endpoints::class);
		ADBC_Routes::register_route( '/migration/migrate-data', 'migrate_data', WP_REST_Server::EDITABLE, ADBC_Migration_Endpoints::class);

		// License routes.
		ADBC_Routes::register_route( '/activate-license', 'activate_license', WP_REST_Server::EDITABLE, ADBC_License_Endpoints::class);
		ADBC_Routes::register_route( '/deactivate-license', 'deactivate_license', WP_REST_Server::READABLE, ADBC_License_Endpoints::class);
		ADBC_Routes::register_route( '/refresh-license', 'refresh_license', WP_REST_Server::READABLE, ADBC_License_Endpoints::class);

	}

}