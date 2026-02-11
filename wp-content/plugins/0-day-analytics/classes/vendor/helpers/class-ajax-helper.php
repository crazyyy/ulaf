<?php
/**
 * Class: File functions helper file.
 *
 * Helper class used for extraction / loading classes.
 *
 * @package advanced-analytics
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Lists\Crons_List;
use ADVAN\Lists\Table_List;
use ADVAN\Controllers\Slack;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Fatals_List;
use ADVAN\Views\File_Editor;
use ADVAN\Lists\WP_Mail_List;
use ADVAN\Lists\Requests_List;
use ADVAN\Controllers\Telegram;
use ADVAN\Controllers\Error_Log;
use ADVAN\Controllers\Slack_API;
use ADVAN\Lists\Transients_List;
use ADVAN\Controllers\Telegram_API;
use ADVAN\Entities\Hook_Groups_Entity;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Controllers\Mail_SMTP_Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\Ajax_Helper' ) ) {
	/**
	 * Responsible for ajax operations.
	 *
	 * @since 1.1.0
	 */
	class Ajax_Helper {

		/**
		 * Inits the class and sets the defaults
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function init() {
			if ( \is_admin() && \wp_doing_ajax() ) {

				/**
				 * Truncate file
				 */
				\add_action( 'wp_ajax_advanced_analytics_truncate_log_file', array( __CLASS__, 'truncate_log_file' ) );

				/**
				 * Truncate file keep last records
				 */
				\add_action( 'wp_ajax_advanced_analytics_truncate_and_keep_log_file', array( __CLASS__, 'truncate_and_keep_log_file' ) );

				/**
				 * Download file
				 */
				\add_action( 'wp_ajax_advanced_analytics_download_log_file', array( __CLASS__, 'download_log_file' ) );

				/**
				 * Save Options
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'plugin_data_save', array( __CLASS__, 'save_settings_ajax' ) );

				/**
				 * Delete Cron
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'delete_cron', array( __CLASS__, 'delete_cron' ) );

				/**
				 * Run Cron
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'run_cron', array( __CLASS__, 'run_cron' ) );

				/**
				 * Delete Cron
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'delete_transient', array( __CLASS__, 'delete_transient' ) );

				/**
				 * Store Slack API key
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'store_slack_api_key', array( __CLASS__, 'store_slack_api_key_ajax' ) );

				/**
				 * Send Slack test message
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'send_test_slack', array( __CLASS__, 'slack_test_message_ajax' ) );

				/**
				 * Send telegram test message
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'send_test_telegram', array( __CLASS__, 'telegram_test_message_ajax' ) );

				/**
				 * Store Telegram API key
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'store_telegram_api_key', array( __CLASS__, 'store_telegram_api_key_ajax' ) );

				/**
				 * Show the code source
				 *
				 * @return void
				 *
				 * @since 1.8.2
				 */
				\add_action( 'wp_ajax_log_source_view', array( __CLASS__, 'ajax_view_source' ) );

				/**
				 * Extract notification
				 *
				 * @return void
				 *
				 * @since 1.9.3
				 */
				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'get_notification_data', array( __CLASS__, 'get_notification_data' ) );

				if ( \current_user_can( 'activate_plugins' ) || \current_user_can( 'delete_plugins' ) ) {
					/**
					 * Extract plugin versions
					 *
					 * @return void
					 *
					 * @since 1.9.7
					 */
					\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'extract_plugin_versions', array( __CLASS__, 'extract_plugin_versions' ) );

					/**
					 * Switch plugin versions
					 *
					 * @return void
					 *
					 * @since 1.9.7
					 */
					\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'switch_plugin_version', array( __CLASS__, 'switch_plugin_version' ) );
				}

				\add_action( 'wp_ajax_' . ADVAN_PREFIX . 'send_test_email', array( __CLASS__, 'send_test_email' ) );

				\add_action( 'wp_ajax_advana_error_log_filtering_dismiss_notice', array( __CLASS__, 'error_log_filtering_dismiss_notice' ) );

				\add_action( 'wp_ajax_aadvana_export_large_csv', array( __CLASS__, 'export_large_csv' ) );
				\add_action( 'wp_ajax_aadvana_export_large_csv_cleanup', array( __CLASS__, 'export_large_csv_cleanup' ) );
				\add_action( 'wp_ajax_aadvana_get_table_info', array( __CLASS__, 'get_table_info' ) );

				if ( Settings::get_option( 'server_info_module_enabled' ) ) {
					\add_action( 'wp_ajax_advan_get_system_usage', array( System_Analytics::class, 'ajax_get_system_usage' ) );
				}

				if ( Settings::get_option( 'file_editor_module_enabled' ) ) {
					\add_action( 'wp_ajax_advan_file_editor_list_dir', array( File_Editor::class, 'ajax_list_dir' ) );
					\add_action( 'wp_ajax_advan_file_editor_get_file', array( File_Editor::class, 'ajax_get_file' ) );
					\add_action( 'wp_ajax_advan_file_editor_save_file', array( File_Editor::class, 'ajax_save_file' ) );
					\add_action( 'wp_ajax_advan_file_editor_diff', array( File_Editor::class, 'ajax_diff' ) );
					\add_action( 'wp_ajax_advan_file_editor_create', array( File_Editor::class, 'ajax_create' ) );
					\add_action( 'wp_ajax_advan_file_editor_upload_file', array( File_Editor::class, 'ajax_upload_file' ) );
					\add_action( 'wp_ajax_advan_file_editor_delete', array( File_Editor::class, 'ajax_delete' ) );
					\add_action( 'wp_ajax_advan_file_editor_restore', array( File_Editor::class, 'ajax_restore' ) );
					\add_action( 'wp_ajax_advan_file_editor_empty_trash', array( File_Editor::class, 'ajax_empty_trash' ) );
					\add_action( 'wp_ajax_advan_file_editor_list_backups', array( File_Editor::class, 'ajax_list_backups' ) );
					\add_action( 'wp_ajax_advan_file_editor_restore_backup', array( File_Editor::class, 'ajax_restore_backup' ) );
					\add_action( 'wp_ajax_advan_file_editor_download_backup', array( File_Editor::class, 'ajax_download_backup' ) );
					\add_action( 'wp_ajax_advan_file_editor_compare_backup', array( File_Editor::class, 'ajax_compare_backup' ) );
					\add_action( 'wp_ajax_advan_file_editor_delete_backup', array( File_Editor::class, 'ajax_delete_backup' ) );
					// Added download action registration (returns 0 previously when unregistered).
					\add_action( 'wp_ajax_advan_file_editor_download_file', array( File_Editor::class, 'ajax_download_file' ) );
					// Rename file or directory.
					\add_action( 'wp_ajax_advan_file_editor_rename', array( File_Editor::class, 'ajax_rename' ) );
					// Duplicate file or directory.
					\add_action( 'wp_ajax_advan_file_editor_duplicate', array( File_Editor::class, 'ajax_duplicate' ) );
					// Create archive (zip).
					\add_action( 'wp_ajax_advan_file_editor_create_archive_start', array( File_Editor::class, 'ajax_create_archive_start' ) );
					\add_action( 'wp_ajax_advan_file_editor_create_archive_batch', array( File_Editor::class, 'ajax_create_archive_batch' ) );
					\add_action( 'wp_ajax_advan_file_editor_create_archive_finish', array( File_Editor::class, 'ajax_create_archive_finish' ) );
					\add_action( 'wp_ajax_advan_file_editor_create_archive_cancel', array( File_Editor::class, 'ajax_create_archive_cancel' ) );
				// Extract archive (unzip).
				\add_action( 'wp_ajax_advan_file_editor_extract_archive_start', array( File_Editor::class, 'ajax_extract_archive_start' ) );
				\add_action( 'wp_ajax_advan_file_editor_extract_archive_batch', array( File_Editor::class, 'ajax_extract_archive_batch' ) );
				\add_action( 'wp_ajax_advan_file_editor_extract_archive_finish', array( File_Editor::class, 'ajax_extract_archive_finish' ) );
				\add_action( 'wp_ajax_advan_file_editor_extract_archive_cancel', array( File_Editor::class, 'ajax_extract_archive_cancel' ) );
					\add_action( 'wp_ajax_advan_save_hook_group', array( __CLASS__, 'save_hook_group' ) );
					\add_action( 'wp_ajax_nopriv_advan_save_hook_group', array( __CLASS__, 'save_hook_group' ) );

					/**
					 * Delete hook group
					 */
					\add_action( 'wp_ajax_advan_delete_hook_group', array( __CLASS__, 'delete_hook_group' ) );
					\add_action( 'wp_ajax_nopriv_advan_delete_hook_group', array( __CLASS__, 'delete_hook_group' ) );

				}
			}
		}

		/**
		 * Truncates the error log file.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function truncate_log_file() {
			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			Error_Log::clear( Error_Log::autodetect() );
			Log_Line_Parser::delete_last_parsed_timestamp();

			\wp_send_json_success( 2 );
		}

		/**
		 * Truncates the error log file, but keeps the last records.
		 *
		 * @return void
		 *
		 * @since 1.9.2
		 */
		public static function truncate_and_keep_log_file() {
			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			// Suppress the ouptut which plugin generates during internal operations.
			\ob_start();
			Error_Log::truncate_and_keep_errors();
			\ob_clean();

			\wp_send_json_success( 2 );
		}

		/**
		 * Downloads the error log file.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function download_log_file() {

			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			File_Helper::download( Error_Log::autodetect() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			\wp_send_json_success( 2 );
		}

		/**
		 * Method responsible for AJAX data saving
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function save_settings_ajax() {

			WP_Helper::verify_admin_nonce( 'aadvana-plugin-data', 'aadvana-security' );

			if ( isset( $_POST[ \ADVAN_SETTINGS_NAME ] ) && ! empty( $_POST[ \ADVAN_SETTINGS_NAME ] ) && \is_array( $_POST[ \ADVAN_SETTINGS_NAME ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$data = array_map( 'sanitize_text_field', \stripslashes_deep( $_POST[ \ADVAN_SETTINGS_NAME ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				\update_option( \ADVAN_SETTINGS_NAME, Settings::collect_and_sanitize_options( $data ) );

				\wp_send_json_success( 2 );
			}
			\wp_send_json_error( __( 'Something went wrong.', '0-day-analytics' ), 400 );
		}

		/**
		 * Executes cron deletion sequence.
		 *
		 * @return void
		 *
		 * @since 1.3.0
		 */
		public static function delete_cron() {
			WP_Helper::verify_admin_nonce( 'bulk-custom-delete' );

			$hash = self::validate_hash_param();

			$result = Crons_Helper::delete_event( $hash );

			if ( $result && ! \is_wp_error( $result ) ) {
				\wp_send_json_success( 2 );
			} elseif ( \is_wp_error( $result ) ) {
				\wp_send_json_error( $result->get_error_message(), 500 );
			} else {
				\wp_send_json_error( __( 'Unable to delete cron (no longer exists maybe ? Try refreshing the page).', '0-day-analytics' ), 500 );
			}
		}

		/**
		 * Executes transient deletion sequence.
		 *
		 * @return void
		 *
		 * @since 1.7.0
		 */
		public static function delete_transient() {
			WP_Helper::verify_admin_nonce( 'bulk-custom-delete' );

			$id = self::validate_id_param();

			$result = Transients_Helper::delete_transient( $id );

			if ( $result && ! \is_wp_error( $result ) ) {
				\wp_send_json_success( 2 );
			} elseif ( \is_wp_error( $result ) ) {
				\wp_send_json_error( $result->get_error_message(), 500 );
			} else {
				\wp_send_json_error( __( 'Unable to delete transient.', '0-day-analytics' ), 500 );
			}
		}

		/**
		 * Executes cron run sequence.
		 *
		 * @return void
		 *
		 * @since 1.3.0
		 */
		public static function run_cron() {
			WP_Helper::verify_admin_nonce( 'bulk-custom-delete' );

			$hash = self::validate_hash_param();

			if ( ! defined( 'DOING_CRON' ) ) {
				define( 'DOING_CRON', true );
			}

			$result = Crons_Helper::execute_event( $hash );

			if ( $result && ! \is_wp_error( $result ) ) {
				\wp_send_json_success( 2 );
			} elseif ( \is_wp_error( $result ) ) {
				\wp_send_json_error( $result->get_error_message(), 500 );
			} elseif ( false === $result ) {
				\wp_send_json_error( __( 'Cron does not exists. Try to refresh the page.', '0-day-analytics' ), 500 );
			} else {
				\wp_send_json_error( __( 'Unable to run cron.', '0-day-analytics' ), 500 );
			}
		}

		/**
		 * Validates and retrieves the hash parameter from the request.
		 *
		 * @return string The sanitized hash.
		 *
		 * @since 1.4.0
		 */
		private static function validate_hash_param(): string {
			if ( ! isset( $_REQUEST['hash'] ) || empty( $_REQUEST['hash'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( __( 'Missing or empty hash parameter.', '0-day-analytics' ), 400 );
			}

			return \sanitize_text_field( \wp_unslash( $_REQUEST['hash'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Validates and retrieves the id parameter from the request.
		 *
		 * @return int The sanitized id.
		 *
		 * @since 1.7.0
		 */
		private static function validate_id_param(): int {
			if ( ! isset( $_REQUEST['id'] ) || empty( $_REQUEST['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( 'Missing or empty id parameter.', 400 );
			}

			return (int) \sanitize_text_field( \wp_unslash( $_REQUEST['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Sends test message to Telegram channel
		 *
		 * @return void
		 *
		 * @since 1.9.5
		 */
		public static function telegram_test_message_ajax() {
			WP_Helper::verify_admin_nonce( Telegram::NONCE_NAME );

			if ( Telegram::is_set() ) {
				Telegram_API::send_telegram_message_via_api( null, null, __( 'TEST MESSAGE', '0-day-analytics' ) );

				\wp_send_json_success();
			}

			\wp_send_json_error( __( 'TELEGRAM: Something is wrong with your configuration.', '0-day-analytics' ) . Slack_API::get_slack_error() );
		}

		/**
		 * Sends test message to Slack channel
		 *
		 * @return void
		 *
		 * @since 1.9.5
		 */
		public static function slack_test_message_ajax() {
			WP_Helper::verify_admin_nonce( Slack::NONCE_NAME );

			if ( Slack::is_set() ) {
				Slack_API::send_slack_message_via_api( null, null, __( 'TEST MESSAGE', '0-day-analytics' ) );

				\wp_send_json_success();
			}

			\wp_send_json_error( __( 'SLACK: Something is wrong with your configuration.', '0-day-analytics' ) . Slack_API::get_slack_error() );
		}

		/**
		 * Stores the Slack Credentials key via AJAX request
		 *
		 * @return void
		 *
		 * @since 1.8.0
		 */
		public static function store_slack_api_key_ajax() {

			WP_Helper::verify_admin_nonce( Slack::NONCE_NAME );

			if ( isset( $_REQUEST['slack_auth'] ) && ! empty( $_REQUEST['slack_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$slack_valid =
				Slack_API::verify_slack_token(
					(string) \sanitize_text_field( \wp_unslash( $_REQUEST['slack_auth'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
				if ( $slack_valid ) {
					$options = Slack::get_settings();

					$options['auth_token'] = \sanitize_text_field( \wp_unslash( $_REQUEST['slack_auth'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					Slack::set_settings( $options );

					\wp_send_json_success();
				}

				\wp_send_json_error( __( 'SLACK: No token provided or the provided one is invalid. Please check and provide the details again.', '0-day-analytics' ) . Slack_API::get_slack_error() );
			}
		}

		/**
		 * Stores the Slack Credentials key via AJAX request
		 *
		 * @return void
		 *
		 * @since 1.8.5
		 */
		public static function store_telegram_api_key_ajax() {
			WP_Helper::verify_admin_nonce( Telegram::NONCE_NAME );

			if ( isset( $_REQUEST['telegram_auth'] ) && ! empty( $_REQUEST['telegram_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$telegram_valid =
				Telegram_API::verify_telegram_token(
					(string) \sanitize_text_field( \wp_unslash( $_REQUEST['telegram_auth'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
				if ( $telegram_valid ) {
					$options = Telegram::get_settings();

					$options['auth_token'] = \sanitize_text_field( \wp_unslash( $_REQUEST['telegram_auth'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					Telegram::set_settings( $options );

					\wp_send_json_success();
				}

				\wp_send_json_error( __( 'TELEGRAM: No token provided or the provided one is invalid. Please check and provide the details again.', '0-day-analytics' ) . Telegram_API::get_telegram_error() );
			}
		}

		/**
		 * Returns the data used to show notification (if any)
		 *
		 * @return void
		 *
		 * @since 1.9.3
		 */
		public static function get_notification_data() {
			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			$data = Logs_List::get_notification_data();

			if ( null !== Error_Log::get_last_error() ) {

				// Some error with error log file - do not bother the user with endless notifications.
				\wp_send_json_success();

				\wp_die();
			}

			if ( empty( $data ) ) {
				\wp_send_json_success();
			} else {
				\wp_send_json_success( $data );
			}
		}

		/**
		 * Extracts the plugin versions.
		 *
		 * @return void
		 *
		 * @since 1.9.7
		 */
		public static function extract_plugin_versions() {
			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			if ( ! isset( $_REQUEST['plugin_slug'] ) || empty( $_REQUEST['plugin_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( \esc_html__( 'Plugin slug is not provided.', '0-day-analytics' ), 404 );
			}

			$plugin_versions = Upgrade_Notice::extract_plugin_versions( \sanitize_title( \wp_unslash( $_REQUEST['plugin_slug'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( \is_wp_error( $plugin_versions ) ) {
				\wp_send_json_error( $plugin_versions->get_error_message(), 500 );
			}

			\wp_send_json_success( $plugin_versions );
		}

		/**
		 * Switch the plugin to desired version.
		 *
		 * @return void
		 *
		 * @since 1.9.7
		 */
		public static function switch_plugin_version() {
			WP_Helper::verify_admin_nonce( 'advan-plugin-data', 'advanced-analytics-security' );

			if ( ! isset( $_REQUEST['plugin_slug'] ) || empty( $_REQUEST['plugin_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( \esc_html__( 'Plugin slug is not provided.', '0-day-analytics' ), 404 );
			}

			if ( ! isset( $_REQUEST['version'] ) || empty( $_REQUEST['version'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( \esc_html__( 'Plugin slug is not provided.', '0-day-analytics' ), 404 );
			}

			$switch_versions = Upgrade_Notice::version_switch( \sanitize_title( \wp_unslash( $_REQUEST['plugin_slug'] ) ), \wp_unslash( ( $_REQUEST['version'] ) ) );

			if ( \is_wp_error( $switch_versions ) ) {
				\wp_send_json_error( $switch_versions->get_error_message(), 500 );
			}

			\wp_send_json_success();
		}

		/**
		 * Shows the source code.
		 *
		 * @return void
		 *
		 * @since 1.8.2
		 */
		public static function ajax_view_source() {
			WP_Helper::verify_admin_nonce( 'source-view' );

			if ( ! isset( $_REQUEST['error_file'] ) || empty( $_REQUEST['error_file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				\wp_send_json_error( \esc_html__( 'File not found.', '0-day-analytics' ), 404 );
			}

			$file_name = $_REQUEST['error_file']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! File_Helper::is_file_valid_php( $file_name ) ) {
				\wp_send_json_error( \esc_html__( 'File does not exists or it is not valid PHP file.', '0-day-analytics' ), 404 );

			}
			// Don't show any configurational files for security reasons.
			if ( Settings::get_option( 'protected_config_source' ) && ( strpos( \basename( $file_name ), 'config' ) !== false || strpos( \basename( $file_name ), 'settings' ) !== false || strpos( \basename( $file_name ), 'wp-load' ) !== false ) ) {
				\wp_send_json_error(
					\esc_html__( 'File source view is protected. You can change this in Advanced Settings - Do not show the source of the config and settings files', '0-day-analytics' ),
					404
				);
			}

			$sh_url = ADVAN_PLUGIN_ROOT_URL . 'js/sh/';

			$source = htmlspecialchars( @file_get_contents( $file_name ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			$lines = isset( $_REQUEST['error_line'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['error_line'] ) ) : 11; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( \strpos( (string) $lines, '-' ) ) {
				$source_lines = array_map( 'absint', explode( '-', $lines ) );
				$scroll_to    = $source_lines[0] - 10;
				$line         = ' new Array (' . implode( ', ', $source_lines ) . ')';
			} else {
				$line      = absint( $lines );
				$scroll_to = $line - 10;
			}

			?>
				<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<script type="text/javascript" src="<?php echo $sh_url;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped , WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>scripts/shCore.js"></script>
				<script type="text/javascript" src="<?php echo $sh_url;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped , WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>scripts/shBrushPhp.js"></script>
				<link href="<?php echo $sh_url;  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet , WordPress.Security.EscapeOutput.OutputNotEscaped ?>styles/shCore.css" rel="stylesheet" type="text/css" />
				<link href="<?php echo $sh_url;  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet , WordPress.Security.EscapeOutput.OutputNotEscaped ?>styles/shThemeDefault.css" rel="stylesheet" type="text/css" />
				<style type="text/css" media="all">
					.syntaxhighlighter{
						max-height: 80%;
					}
				</style>
				</head>

				<body>
				<pre class="brush: php; toolbar: false;"><?php echo $source;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></pre>

				<script type="text/javascript">
					SyntaxHighlighter.defaults["highlight"] = <?php echo $line;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					//SyntaxHighlighter.defaults["first-line"] = '.$line.';
					SyntaxHighlighter.defaults['quick-code'] = false;
					SyntaxHighlighter.all();
					function waitUntilRender(){
						linex = document.getElementsByClassName("number<?php echo $scroll_to;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>");
						if (typeof linex === 'undefined') return;
						linex[0].scrollIntoView();
						clearInterval(intervalID);
					}
					var intervalID  = setInterval(waitUntilRender, 200);

				</script>
				</body></html>
			<?php
		}

		/**
		 * Send a test email and use SMTP host if defined in settings
		 *
		 * @since 3.3.0
		 */
		public static function send_test_email() {
			WP_Helper::verify_admin_nonce( Mail_SMTP_Settings::NONCE_NAME );

			$content       = array(
				array(
					'title' => \esc_html__( 'Hey... are you getting this?', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Looks like you did!', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'There\'s a message for you...', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Here it is:', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Is it working?', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Yes, it\'s working!</strong>', '0-day-analytics' ) . '</p>',
				),
				array(
					'title' => \esc_html__( 'Hope you\'re getting this...', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Looks like this was sent out just fine and you got it.', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Testing delivery configuration...', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Everything looks good!', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Testing email delivery', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Looks good!', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Config is looking good', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Seems like everything has been set up properly!', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'All set up', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Your configuration is working properly.', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Good to go', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Config is working great.', '0-day-analytics' ) . '</strong></p>',
				),
				array(
					'title' => \esc_html__( 'Good job', '0-day-analytics' ),
					'body'  => '<p><strong>' . \esc_html__( 'Everything is set.', '0-day-analytics' ) . '</strong></p>',
				),
			);
			$email         = $_REQUEST['email'] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$random_number = rand( 0, count( $content ) - 1 );
			$to            = \sanitize_email( $email );
			$title         = $content[ $random_number ]['title'];
			$body          = $content[ $random_number ]['body'] . '<p>This message was sent from <a href="' . \get_bloginfo( 'url' ) . '">' . \get_bloginfo( 'url' ) . '</a> on ' . \wp_date( 'F j, Y' ) . ' at ' . \wp_date( 'H:i:s' ) . ' via.' . ADVAN_NAME . '</p>';
			$headers       = array( 'Content-Type: text/html; charset=UTF-8' );
			$success       = \wp_mail(
				$to,
				$title,
				$body,
				$headers
			);
			if ( $success ) {
				\wp_send_json_success();
			} else {

				\wp_send_json_error( __( 'Mail: Something is wrong with your configuration.', '0-day-analytics' ) );
			}
		}

		/**
		 * Dismisses the error filtering notice for the given user
		 *
		 * @return void
		 *
		 * @since 3.8.0
		 */
		public static function error_log_filtering_dismiss_notice() {
			$user_id = \get_current_user_id();
			\update_user_meta( $user_id, '_aadvana_filter_error_log_notice_dismissed', 1 );
			\wp_die();
		}

		/**
		 * Export Large CSV in Batches
		 *
		 * @return void
		 *
		 * @since 3.8.0
		 */
		public static function export_large_csv() {
			WP_Helper::verify_admin_nonce( 'export_large_csv_nonce', 'security' );

			$batch      = isset( $_POST['batch'] ) ? intval( $_POST['batch'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 500; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$offset     = $batch * $batch_size;

			list($export_dir, $export_url) = Miscellaneous::get_export_dir();

			$csv_path = $export_dir . 'export_temp.csv';
			$csv_url  = $export_url . 'export_temp.csv';

			$rows            = array();
			$total           = 0;
			$extra_file_name = '';

			if ( isset( $_POST['typeExport'] ) && ! empty( $_POST['typeExport'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( 'cron' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$plugin_filter = ( isset( $_POST['plugin_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_filter'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$site_filter   = ( isset( $_POST['site_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['site_filter'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$rows          = array();

					if ( '' !== $site_filter && -1 !== (int) $site_filter && function_exists( 'is_multisite' ) && is_multisite() ) {
						$rows = Crons_Helper::get_events_for_site( (int) $site_filter );
					} else {
						$rows = Crons_List::get_cron_items();
					}

					if ( '' !== $plugin_filter && -1 !== (int) $plugin_filter ) {
						$rows = array_filter(
							$rows,
							function ( $row ) use ( $plugin_filter ) {
								if ( isset( $row['plugin_slugs'] ) && is_array( $row['plugin_slugs'] ) ) {
									return in_array( $plugin_filter, $row['plugin_slugs'], true );
								}
								return ( isset( $row['plugin_slug'] ) && '' !== $row['plugin_slug'] && $row['plugin_slug'] === $plugin_filter );
							}
						);
					}
					$total = count( $rows );
					$rows  = \array_slice( $rows, $offset, $batch_size, true );
				}
				if ( 'logs' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$plugin_filter = ( ( isset( $_POST['plugin_filter'] ) ) ? \sanitize_text_field( \wp_unslash( $_POST['plugin_filter'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search = isset( $_POST['search'] ) ? \sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$extra_file_name = '_' . $plugin_filter . '_';

					$list_logs = new Logs_List( array( 'plugin_filter' => $plugin_filter ) );

					$rows = $list_logs::get_error_items();
					$rows = \array_slice( $rows, $offset, $batch_size, true );

					foreach ( $rows as $key => $row ) {
						if ( isset( $row['sub_items'] ) ) {
							if ( \is_array( $row['sub_items'] ) && ! empty( $row['sub_items'] ) ) {
								$subs = array();
								foreach ( $row['sub_items'] as $sub_key => $sub_item ) {
									$subs[] = \json_encode( $sub_item );
								}

								$rows[ $key ]['sub_items'] = $subs;
							}
						} else {
							$rows[ $key ]['sub_items'] = '';
						}
						if ( ! isset( $row['plugin'] ) ) {
							$rows[ $key ]['plugin'] = '';
						}
					}

					$total = count( $rows );
				}
				if ( 'table' === $_POST['typeExport'] && isset( $_POST['tableName'] ) && ! empty( $_POST['tableName'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$table = \sanitize_text_field( \wp_unslash( $_POST['tableName'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search = isset( $_POST['search'] ) ? \sanitize_text_field( \wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$extra_file_name = '_' . str_replace( ' ', '_', $table ) . '_';
					if ( Common_Table::check_table_exists( $table ) ) {
						$list_table = new Table_List( $table );

						$rows = $list_table->fetch_table_data(
							array(
								'per_page'      => $batch_size,
								'offset'        => $offset,
								'order'         => 'ASC',
								'search_string' => $search,
							)
						);

						$total = $list_table->get_count();
					}
				}
				if ( 'mail' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$list_table = new WP_Mail_List();
					$search     = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$plugin     = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$extra_file_name = '_' . str_replace( ' ', '_', $list_table->get_table_name() ) . '_';
					if ( ! empty( $plugin ) && -1 !== (int) $plugin ) {
						$extra_file_name .= $plugin . '_';
					}

					$rows = $list_table->fetch_table_data(
						array(
							'per_page' => $batch_size,
							'offset'   => $offset,
							'order'    => 'ASC',
							'site_id'  => '',
							'search'   => $search,
							'plugin'   => $plugin,
						)
					);

					$total = $list_table->get_count();
				}
				if ( 'requests' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search        = isset( $_POST['search'] ) ? \sanitize_text_field( \wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$plugin        = isset( $_POST['plugin'] ) ? \sanitize_text_field( \wp_unslash( $_POST['plugin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$date_from     = isset( $_POST['dateFrom'] ) ? \sanitize_text_field( \wp_unslash( $_POST['dateFrom'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$date_to       = isset( $_POST['dateTo'] ) ? \sanitize_text_field( \wp_unslash( $_POST['dateTo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$status_filter = isset( $_POST['statusFilter'] ) ? \sanitize_text_field( \wp_unslash( $_POST['statusFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$type_filter   = isset( $_POST['typeFilter'] ) ? \sanitize_text_field( \wp_unslash( $_POST['typeFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$domain_filter = isset( $_POST['domainFilter'] ) ? \sanitize_text_field( \wp_unslash( $_POST['domainFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$runtime_min   = isset( $_POST['runtimeMin'] ) ? \sanitize_text_field( \wp_unslash( $_POST['runtimeMin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$runtime_max   = isset( $_POST['runtimeMax'] ) ? \sanitize_text_field( \wp_unslash( $_POST['runtimeMax'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					// Use the list class to compute totals and fetch rows, keeping filters/formatting consistent.
					$list_table      = new Requests_List();
					$extra_file_name = '_' . str_replace( ' ', '_', $list_table->get_table_name() ) . '_';

					// Get total using the list's counting behaviour.
					$list_table->fetch_table_data(
						array(
							'count'         => true,
							'search_string' => $search,
							'plugin'        => $plugin,
							'date_from'     => $date_from,
							'date_to'       => $date_to,
							'status_filter' => $status_filter,
							'type_filter'   => $type_filter,
							'domain_filter' => $domain_filter,
							'runtime_min'   => $runtime_min,
							'runtime_max'   => $runtime_max,
						)
					);
					$total = $list_table->get_count();

					// Fetch the actual rows for this batch.
					$rows = $list_table->fetch_table_data(
						array(
							'offset'        => $offset,
							'per_page'      => $batch_size,
							'order'         => 'ASC',
							'search_string' => $search,
							'plugin'        => $plugin,
							'date_from'     => $date_from,
							'date_to'       => $date_to,
							'status_filter' => $status_filter,
							'type_filter'   => $type_filter,
							'domain_filter' => $domain_filter,
							'runtime_min'   => $runtime_min,
							'runtime_max'   => $runtime_max,
						)
					);
				}
				if ( 'hooks_capture' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search         = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$hook_type      = isset( $_POST['hook_type'] ) ? sanitize_text_field( wp_unslash( $_POST['hook_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$trigger_source = isset( $_POST['trigger_source'] ) ? sanitize_text_field( wp_unslash( $_POST['trigger_source'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$date_from      = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$date_to        = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$group_filter   = isset( $_POST['group_filter'] ) ? absint( $_POST['group_filter'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					// Ask the list class to compute the total and fetch rows so formatting and filters stay consistent.
					$list = new \ADVAN\Lists\Hooks_Capture_List( '' );

					// Get total using the list's counting behaviour.
					$list->fetch_table_data(
						array(
							'count'          => true,
							'search_string'  => $search,
							'hook_type'      => $hook_type,
							'trigger_source' => $trigger_source,
							'date_from'      => $date_from,
							'date_to'        => $date_to,
							'group_filter'   => $group_filter,
						)
					);
					$total = $list->get_count();

					// Fetch the actual rows for this batch.
					$rows = $list->fetch_table_data(
						array(
							'offset'         => $offset,
							'per_page'       => $batch_size,
							'search_string'  => $search,
							'hook_type'      => $hook_type,
							'trigger_source' => $trigger_source,
							'date_from'      => $date_from,
							'date_to'        => $date_to,
							'group_filter'   => $group_filter,
						)
					);
				}

				if ( 'hooks_management' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search   = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$enabled  = isset( $_POST['enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					// Use the list class to compute totals and fetch rows, keeping filters/formatting consistent.
					$list = new \ADVAN\Lists\Hooks_Management_List( '' );

					// Count first using the list class.
					$list->fetch_table_data(
						array(
							'count'         => true,
							'search_string' => $search,
							'category'      => $category,
							'enabled'       => $enabled,
						)
					);
					$total = $list->get_count();

					// Fetch rows for this batch.
					$rows = $list->fetch_table_data(
						array(
							'offset'        => $offset,
							'per_page'      => $batch_size,
							'search_string' => $search,
							'category'      => $category,
							'enabled'       => $enabled,
						)
					);
				}
				if ( 'fatals' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$list_table      = new Fatals_List();
					$search          = isset( $_POST['search'] ) ? \sanitize_text_field( \wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$plugin          = isset( $_POST['plugin'] ) ? \sanitize_text_field( \wp_unslash( $_POST['plugin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$extra_file_name = '_' . str_replace( ' ', '_', $list_table->get_table_name() ) . '_';

					$rows = $list_table->fetch_table_data(
						array(
							'per_page'      => $batch_size,
							'offset'        => $offset,
							'order'         => 'ASC',
							'search_string' => $search,
							'plugin'        => $plugin,
						)
					);

					$total = $list_table->get_count();
				}
				if ( 'transients' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$list_table = new Transients_List( array() );

					$type = isset( $_POST['transientType'] ) ? \sanitize_text_field( \wp_unslash( $_POST['transientType'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$extra_file_name = '_' . str_replace( ' ', '_', $list_table->get_table_name() ) . '_' . $type . '_';

					$search = isset( $_POST['search'] ) ? \sanitize_text_field( \wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$rows = $list_table->fetch_table_data(
						array(
							'per_page' => $batch_size,
							'offset'   => $offset,
							'order'    => 'ASC',
							'type'     => $type,
							'search'   => $search,
						)
					);

					$total = $list_table->get_count();
				}
				if ( 'snippets' === $_POST['typeExport'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$search         = isset( $_POST['search'] ) ? \sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$snippet_status = isset( $_POST['snippet_status'] ) ? \sanitize_text_field( wp_unslash( $_POST['snippet_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$snippet_type   = isset( $_POST['snippet_type'] ) ? \sanitize_text_field( wp_unslash( $_POST['snippet_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$filters = array();

					if ( '' !== $search ) {
						$filters['search'] = $search;
					}

					if ( '' !== $snippet_status ) {
						$filters['status'] = $snippet_status;
					}

					if ( '' !== $snippet_type ) {
						$filters['type'] = $snippet_type;
					}

					$total = \ADVAN\Entities\Snippet_Entity::get_snippets_count_with_filters( $filters );
					$rows  = \ADVAN\Entities\Snippet_Entity::get_snippets_with_filters( $filters, $offset, $batch_size );
				}
			} else {
				\wp_send_json_error(
					array(
						'message' => __( 'Export type is not specified.', '0-day-analytics' ),
					),
					400
				);
			}

			$is_first_batch = ( 0 === $batch );

			if ( empty( $rows ) ) {
				// Open CSV for writing/appending.
				$handle = @fopen( $csv_path, $is_first_batch ? 'w' : 'a' );
				if ( ! $handle ) {
					\wp_send_json_error(
						array(
							'message' => __( 'Failed to write to CSV file. Check folder permissions.', '0-day-analytics' ),
						),
						500
					);
				}

				// Get date and time formats from WordPress settings.
				$date_format = get_option( 'date_format' ); // e.g., 'F j, Y'.
				$time_format = get_option( 'time_format' ); // e.g., 'g:i a'.

				// Provide defaults if options are not set.
				if ( empty( $date_format ) ) {
					$date_format = 'F j, Y';
				}
				if ( empty( $time_format ) ) {
					$time_format = 'H:i:s';
				}

				// Combine date and time if needed.
				$formatted_datetime = date_i18n( $date_format . ' ' . $time_format );

				// Sanitize filename: replace spaces and colons with dashes.
				$safe_datetime = preg_replace( '/[^A-Za-z0-9\-]/', '-', $formatted_datetime );

				// Create filename.
				$filename = 'export_' . \sanitize_text_field( \wp_unslash( $_POST['typeExport'] ) ) . '_' . $extra_file_name . $safe_datetime . '.csv'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				rename( $export_dir . 'export_temp.csv', $export_dir . $filename ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename

				\wp_send_json_success(
					array(
						'done'         => true,
						'download_url' => $export_url . $filename,
					)
				);
			}

			// Open CSV for writing/appending.
			$handle = @fopen( $csv_path, $is_first_batch ? 'w' : 'a' );
			if ( ! $handle ) {
				\wp_send_json_error(
					array(
						'message' => __( 'Failed to write to CSV file. Check folder permissions.', '0-day-analytics' ),
					),
					500
				);
			}

			if ( $is_first_batch && ! empty( $rows ) ) {
				fputcsv(
					$handle,
					array_keys( reset( $rows ) ),
					"\t",
					'"',
					'"',
				);
			}

			foreach ( $rows as $row ) {
				\fputcsv(
					$handle,
					array_map(
						function ( $value ): string {
							// Convert to string.
							if ( is_array( $value ) ) {
								$value = \implode( ', ', $value );
							}
							if ( is_object( $value ) ) {
								$value = json_encode( $value );
							}
							$value = (string) $value;

							// Remove control characters (non-printable).
							$value = preg_replace( '/[\x00-\x1F\x7F]/u', '', $value );

							return (string) $value;
						},
						$row
					),
					"\t",
					'"',
					'"',
				);
			}

			fclose( $handle );

			\wp_send_json_success(
				array(
					'done'       => false,
					'next_batch' => $batch + 1,
					'processed'  => ( $batch + 1 ) * $batch_size,
					'total'      => $total,
				)
			);
		}

		/**
		 * Cleanup Temporary CSV
		 *
		 * @return void
		 *
		 * @since 3.8.0
		 */
		public static function export_large_csv_cleanup() {
			WP_Helper::verify_admin_nonce( 'export_large_csv_nonce', 'security' );

			list($export_dir) = Miscellaneous::get_export_dir();

			$csv_path = $export_dir . 'export_temp.csv';

			if ( file_exists( $csv_path ) ) {
				unlink( $csv_path );
				\wp_send_json_success( array( 'deleted' => true ) );
			} else {
				\wp_send_json_success( array( 'deleted' => false ) );
			}
		}

		/**
		 * Save hook group (create or update).
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function save_hook_group() {
			WP_Helper::verify_admin_nonce( 'advan_hook_groups', 'nonce' );

			if ( ! class_exists( 'ADVAN\Entities\Hook_Groups_Entity' ) ) {
				\wp_send_json_error( 'Hook groups not available' );
			}

			$group_id    = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
			$name        = isset( $_POST['name'] ) ? \sanitize_text_field( \wp_unslash( $_POST['name'] ) ) : '';
			$color       = isset( $_POST['color'] ) ? \sanitize_hex_color( $_POST['color'] ) : '#007cba';
			$description = isset( $_POST['description'] ) ? \sanitize_textarea_field( \wp_unslash( $_POST['description'] ) ) : '';

			if ( empty( $name ) ) {
				\wp_send_json_error( 'Group name is required' );
			}

			if ( $group_id > 0 ) {
				// Update existing group.
				$success = Hook_Groups_Entity::update_group( $group_id, $name, $color, $description );
			} else {
				// Create new group.
				$success = Hook_Groups_Entity::create_group( $name, $color, $description );
			}

			if ( $success ) {
				\wp_send_json_success();
			} else {
				\wp_send_json_error( 'Failed to save group' );
			}
		}

		/**
		 * Delete hook group.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function delete_hook_group() {
			WP_Helper::verify_admin_nonce( 'advan_hook_groups', 'nonce' );

			if ( ! class_exists( 'ADVAN\Entities\Hook_Groups_Entity' ) ) {
				\wp_send_json_error( 'Hook groups not available' );
			}

			$group_id = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;

			if ( $group_id <= 0 ) {
				\wp_send_json_error( 'Invalid group ID' );
			}

			$success = Hook_Groups_Entity::delete_group( $group_id );

			if ( $success ) {
				\wp_send_json_success();
			} else {
				\wp_send_json_error( 'Failed to delete group' );
			}
		}

		/**
		 * Get detailed table information including structure, indexes, foreign keys, and health.
		 *
		 * @return void
		 *
		 * @since 4.7.0
		 */
		public static function get_table_info() {
			WP_Helper::verify_admin_nonce( 'get_table_info', 'security' );

			$table_name = isset( $_POST['table_name'] ) ? \sanitize_text_field( \wp_unslash( $_POST['table_name'] ) ) : '';

			if ( empty( $table_name ) ) {
				\wp_send_json_error( 'Invalid table name' );
			}

			$table_info = Common_Table::get_table_info( $table_name );

			if ( \is_wp_error( $table_info ) ) {
				\wp_send_json_error( $table_info->get_error_message() );
			}

			\wp_send_json_success( $table_info );
		}
	}
}
