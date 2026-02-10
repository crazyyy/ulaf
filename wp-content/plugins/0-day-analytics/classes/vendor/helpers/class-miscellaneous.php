<?php
/**
 * Class: General stuff goes here.
 *
 * Helper class to properly extract miscellaneous things.
 *
 * @package advanced-analytics
 *
 * @since 3.8.0
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use ADVAN\Lists\Logs_List;
use ADVAN\Lists\Crons_List;
use ADVAN\Lists\Table_List;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Fatals_List;
use ADVAN\Views\File_Editor;
use ADVAN\Lists\WP_Mail_List;
use ADVAN\Lists\Requests_List;
use ADVAN\Lists\Snippets_List;
use ADVAN\Lists\Transients_List;
use ADVAN\Lists\Hooks_Capture_List;
use ADVAN\Lists\Hooks_Management_List;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\Miscellaneous' ) ) {
	/**
	 * Responsible for general repeating stuff.
	 *
	 * @since 3.8.0
	 */
	class Miscellaneous {

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 1.2.0
		 */
		private static $settings_page_link = '';

		/**
		 * The link to the fatals settings page
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		private static $settings_fatals_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 1.2.0
		 */
		private static $settings_crons_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 2.1.0
		 */
		private static $settings_table_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $settings_error_logs_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $settings_transients_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $settings_requests_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $settings_wp_mails_link = '';

		/**
		 * Cached link to snippets admin page.
		 *
		 * @var string
		 *
		 * @since 4.3.0
		 */
		private static $settings_snippets_link = '';

		/**
		 * The link to the WP admin settings page
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $settings_file_editor_link = '';

		/**
		 * Returns general flex styles.
		 *
		 * @return string
		 *
		 * @since 3.8.0
		 */
		public static function get_flex_style(): string {
			\ob_start();
			?>
			.flex {
				display:flex;
			}
			.flex-row {
				flex-direction:row;
			}
			.grow-0 {
				flex-grow:0;
			}
			.p-2 {
				padding:8px;
			}
			.w-full {
				width:auto;
			}
			.border-t {
				border-bottom-width:1px;
			}
			.justify-between {
				justify-content:space-between;
			}
			.italic {
				font-style: italic;
			}
			.text-lg {
				font-size: 1.1em;
				font-weight: bold;
			}
			#wpwrap {
				overflow-x: hidden !important;
			}
			.wp-list-table {
				white-space: nowrap;
				display: block;
				overflow-x: auto;
			}
			<?php
			return \ob_get_clean();
		}

		/**
		 * Extracts the export directory for the plugin (in uploads)
		 *
		 * @return array|void
		 *
		 * @since 3.8.0
		 */
		public static function get_export_dir() {
			$upload_dir = \wp_upload_dir();
			$export_dir = \trailingslashit( $upload_dir['basedir'] ) . ADVAN_TEXTDOMAIN . \DIRECTORY_SEPARATOR;
			$export_url = \trailingslashit( $upload_dir['baseurl'] ) . ADVAN_TEXTDOMAIN . \DIRECTORY_SEPARATOR;

			// Create directory if missing.
			if ( ! file_exists( $export_dir ) ) {
				if ( ! \wp_mkdir_p( $export_dir ) ) {
					\wp_send_json_error(
						array(
							'message' => __( 'Unable to create export directory.', '0-day-analytics' ),
						),
						500
					);
				}
			}

			// Check write permissions.
			if ( ! \wp_is_writable( $export_dir ) ) {
				\wp_send_json_error(
					array(
						'message' => __( 'Export directory is not writable.', '0-day-analytics' ),
					),
					500
				);
			}

			// Ensure directory guards exist (idempotent calls).
			File_Helper::create_htaccess_file( $export_dir );
			File_Helper::create_index_file( $export_dir );

			return array( $export_dir, $export_url );
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function get_settings_page_link() {
			if ( '' === self::$settings_page_link ) {
				self::$settings_page_link = \add_query_arg( 'page', Logs_List::MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_page_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.7.4
		 */
		public static function get_crons_page_link() {
			if ( '' === self::$settings_crons_link ) {
				self::$settings_crons_link = \add_query_arg( 'page', Crons_List::CRON_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_crons_link;
		}
		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 4.0.0
		 */
		public static function get_file_editor_page_link() {
			if ( '' === self::$settings_file_editor_link ) {
				self::$settings_file_editor_link = \add_query_arg( 'page', File_Editor::FILE_EDITOR_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_file_editor_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.7.4
		 */
		public static function get_fatals_page_link() {
			if ( '' === self::$settings_fatals_link ) {
				self::$settings_fatals_link = \add_query_arg( 'page', Fatals_List::FATALS_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_fatals_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 2.1.0
		 */
		public static function get_tables_page_link() {
			if ( '' === self::$settings_table_link ) {
				self::$settings_table_link = \add_query_arg( 'page', Table_List::TABLE_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_table_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.7.5
		 */
		public static function get_error_log_page_link() {
			if ( '' === self::$settings_error_logs_link ) {
				self::$settings_error_logs_link = \add_query_arg( 'page', Logs_List::MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_error_logs_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.7.4
		 */
		public static function get_transients_page_link() {
			if ( '' === self::$settings_transients_link ) {
				self::$settings_transients_link = \add_query_arg( 'page', Transients_List::TRANSIENTS_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_transients_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 1.7.4
		 */
		public static function get_requests_page_link() {
			if ( '' === self::$settings_requests_link ) {
				self::$settings_requests_link = \add_query_arg( 'page', Requests_List::REQUESTS_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_requests_link;
		}

		/**
		 * Returns the link to the WP admin settings page, based on the current WP install
		 *
		 * @return string
		 *
		 * @since 3.0.0
		 */
		public static function get_wp_mail_page_link() {
			if ( '' === self::$settings_wp_mails_link ) {
				self::$settings_wp_mails_link = \add_query_arg( 'page', WP_Mail_List::WP_MAIL_MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_wp_mails_link;
		}

		/**
		 * Returns the link to the snippets admin page.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		public static function get_snippets_page_link() {
			if ( '' === self::$settings_snippets_link ) {
				self::$settings_snippets_link = \add_query_arg( 'page', Snippets_List::MENU_SLUG, \network_admin_url( 'admin.php' ) );
			}

			return self::$settings_snippets_link;
		}

		/**
		 * Modifies the admin footer version text.
		 *
		 * @param   string $text The current admin footer version text.
		 *
		 * @return  string
		 *
		 * @since 1.7.5
		 */
		public static function admin_footer_version_text( $text ) {

			if ( WP_Helper::get_wp_screen() && ( in_array( WP_Helper::get_wp_screen()->base, self::get_plugin_page_slugs(), true ) ) ) {

				return sprintf(
					'<a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; <a href="%s">%s</a> &#8729; %s %s',
					\esc_url( self::get_error_log_page_link() ),
					\esc_html__( 'Error Log', '0-day-analytics' ),
					( Settings::get_option( 'fatals_module_enabled' ) ) ? \esc_url( self::get_fatals_page_link() ) : '',
					( Settings::get_option( 'fatals_module_enabled' ) ) ? \esc_html__( 'PHP errors', '0-day-analytics' ) : '',
					( Settings::get_option( 'cron_module_enabled' ) ) ? \esc_url( self::get_crons_page_link() ) : '',
					( Settings::get_option( 'cron_module_enabled' ) ) ? \esc_html__( 'Crons', '0-day-analytics' ) : '',
					( Settings::get_option( 'tables_module_enabled' ) ) ? \esc_url( self::get_tables_page_link() ) : '',
					( Settings::get_option( 'tables_module_enabled' ) ) ? \esc_html__( 'Tables', '0-day-analytics' ) : '',
					( Settings::get_option( 'transients_module_enabled' ) ) ? \esc_url( self::get_transients_page_link() ) : '',
					( Settings::get_option( 'transients_module_enabled' ) ) ? \esc_html__( 'Transients', '0-day-analytics' ) : '',
					( Settings::get_option( 'requests_module_enabled' ) ) ? \esc_url( self::get_requests_page_link() ) : '',
					( Settings::get_option( 'requests_module_enabled' ) ) ? \esc_html__( 'Requests', '0-day-analytics' ) : '',
					( Settings::get_option( 'wp_mail_module_enabled' ) ) ? \esc_url( self::get_wp_mail_page_link() ) : '',
					( Settings::get_option( 'wp_mail_module_enabled' ) ) ? \esc_html__( 'Mails', '0-day-analytics' ) : '',
					( Settings::get_option( 'snippets_module_enabled' ) ) ? \esc_url( self::get_snippets_page_link() ) : '',
					( Settings::get_option( 'snippets_module_enabled' ) ) ? \esc_html__( 'Snippets', '0-day-analytics' ) : '',
					\esc_html__( 'Version ', '0-day-analytics' ),
					\esc_html( (string) ADVAN_VERSION )
				);
			}

			return $text;
		}

		/**
		 * Returns all fo the plugin admin pages slugs.
		 *
		 * @return array
		 *
		 * @since 1.7.5
		 */
		public static function get_plugin_page_slugs(): array {

			$suffix = '';

			if ( WP_Helper::is_multisite() ) {
				$suffix = '-network';
			}

			return array_unique(
				array(
					Settings::PAGE_SLUG . $suffix,
					Logs_List::PAGE_SLUG . $suffix,
					Requests_List::PAGE_SLUG . $suffix,
					WP_Mail_List::PAGE_SLUG . $suffix,
					Snippets_List::PAGE_SLUG . $suffix,
					Transients_List::PAGE_SLUG . $suffix,
					Crons_List::PAGE_SLUG . $suffix,
					Table_List::PAGE_SLUG . $suffix,
					Fatals_List::PAGE_SLUG . $suffix,
					System_Analytics::PAGE_SLUG . $suffix,
					File_Editor::PAGE_SLUG . $suffix,
					Settings::PAGE_SLUG,
					Logs_List::PAGE_SLUG,
					Requests_List::PAGE_SLUG,
					WP_Mail_List::PAGE_SLUG,
					Snippets_List::PAGE_SLUG,
					Transients_List::PAGE_SLUG,
					Crons_List::PAGE_SLUG,
					Table_List::PAGE_SLUG,
					Fatals_List::PAGE_SLUG,
					System_Analytics::PAGE_SLUG,
					File_Editor::PAGE_SLUG,
					Hooks_Management_List::PAGE_SLUG,
					Hooks_Capture_List::PAGE_SLUG,
				)
			);
		}

		/**
		 * Recursively sanitize all values in an array.
		 *
		 * @param mixed $options Array or value to sanitize.
		 *
		 * @return mixed
		 *
		 * @since 3.9.1
		 */
		public static function sanitize_options_recursive( $options ) {
			if ( is_array( $options ) ) {
				$sanitized = array();
				foreach ( $options as $key => $value ) {
					$sanitized[ $key ] = self::sanitize_options_recursive( $value );
				}

				return $sanitized;
			} else {
				if ( is_string( $options ) ) {
					return \sanitize_text_field( $options );
				}
				return $options;
			}
		}
	}
}
