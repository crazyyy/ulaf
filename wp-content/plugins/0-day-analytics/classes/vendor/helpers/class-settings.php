<?php
/**
 * Class: Determine the context in which the plugin is executed.
 *
 * Helper class to determine the proper status of the request.
 *
 * @package advanced-analytics
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use ADVAN\Lists\Logs_List;
use ADVAN\Lists\Crons_List;
use ADVAN\Lists\Table_List;
use ADVAN\Controllers\Slack;
use ADVAN\Lists\Fatals_List;
use ADVAN\Views\File_Editor;
use ADVAN\Lists\WP_Mail_List;
use ADVAN\Lists\Requests_List;
use ADVAN\Lists\Snippets_List;
use ADVAN\Controllers\Telegram;
use ADVAN\Controllers\Error_Log;
use ADVAN\Controllers\Slack_API;
use ADVAN\Lists\Transients_List;
use ADVAN\Lists\Views\Crons_View;
use ADVAN\Lists\Views\Table_View;
use ADVAN\Lists\Views\Fatals_View;
use ADVAN\Controllers\Telegram_API;
use ADVAN\Lists\Hooks_Capture_List;
use ADVAN\Lists\Hooks_Management_List;
use ADVAN\Lists\Views\WP_Mail_View;
use ADVAN\Lists\Views\Requests_View;
use ADVAN\Settings\Settings_Builder;
use ADVAN\Lists\Views\Logs_List_View;
use ADVAN\Lists\Views\Transients_View;
use ADVAN\Migration\Abstract_Migration;
use WpOrg\Requests\Hooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\Settings' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 1.1.0
	 */
	class Settings {

		public const SETTINGS_MENU_SLUG = 'advan_logs_settings';

		public const OPTIONS_PAGE_SLUG = 'analytics-options-page';

		public const SETTINGS_FILE_FIELD = ADVAN_PREFIX . 'import_file';

		public const SETTINGS_FILE_UPLOAD_FIELD = ADVAN_PREFIX . 'import_upload';

		public const SETTINGS_VERSION = ADVAN_PREFIX . 'plugin_version';

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_logs_settings';

		public const LIVE_NOTIF_JS_MODULE = ADVAN_INNER_SLUG . '-live-notifications-js';

		/**
		 * Paths to sensitive fields for masking on export.
		 * Dot notation for nested arrays.
		 *
		 * @since 3.3.2
		 */
		private const SENSITIVE_EXPORT_PATHS = array(
			'smtp_password',
			'slack_notifications.all.auth_token',
			'telegram_notifications.all.auth_token',
		);

		/**
		 * Holds cache for disabled severity levels
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $disabled_severities = null;

		/**
		 * Holds cache for enabled severity levels
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $enabled_severities = null;

		/**
		 * Default wp_config.php writer configs
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $config_args = array(
			'normalize' => true,
			'raw'       => true,
			'add'       => true,
		);

		/**
		 * Array with the current options
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $current_options = array();

		/**
		 * The name of the hook for the menu.
		 *
		 * @var string
		 *
		 * @since 1.1.0
		 */
		private static $hook = null;

		/**
		 * Array with the default options
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $default_options = array();

		/**
		 * The current version of the plugin
		 *
		 * @var string
		 */
		private static $current_version = '';

		/**
		 * Inits the class.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function init() {

			self::get_current_options();

			// Hook me up.
			\add_action( 'admin_menu', array( __CLASS__, 'add_options_page' ) ); // Insert the Admin panel.
			if ( \is_multisite() ) {
				\add_action( 'network_admin_menu', array( __CLASS__, 'add_options_page' ) ); // Insert the Admin on multisite install panel.
			}

			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_custom_wp_admin_style' ) );

			// Show any stored wp-config transformer errors after a settings save.
			\add_action( 'admin_notices', array( __CLASS__, 'show_config_transformer_errors' ) );

			/* Crons start */
			if ( self::get_option( 'cron_module_enabled' ) ) {
				Crons_List::hooks_init();
			}
			/* Crons end */

			/* File Editor start */
			if ( self::get_option( 'file_editor_module_enabled' ) ) {
				File_Editor::init();
			}
			/* File Editor end */

			/* Transients start */
			if ( self::get_option( 'transients_module_enabled' ) ) {
				Transients_List::hooks_init();
			}
			/* Transients end */

			/* Tables start */
			if ( self::get_option( 'tables_module_enabled' ) ) {
				Table_List::hooks_init();
			}
			/* Tables end */

			Logs_List::hooks_init();

			/**
			 * Draws the save button in the settings
			 */
			\add_action( ADVAN_PREFIX . 'settings_save_button', array( __CLASS__, 'save_button' ) );
		}

		/**
		 * Enqueue the custom admin style.
		 *
		 * @param string $hook - The current admin page.
		 *
		 * @return void
		 *
		 * @since 1.7.4
		 */
		public static function load_custom_wp_admin_style( $hook ) {
			// Output browser notifications script only for privileged users and if not disabled.
			if ( ! self::get_option( 'browser_notifications_not_send' ) && \current_user_can( 'manage_options' ) ) { ?>
				<script>
					window.addEventListener("load", () => {
						const pollInterval = Math.max(5000, <?php echo (int) ( (int) self::get_option( 'browser_notifications_seconds' ) * 1000 ); ?>);
						function sanitizeField(str){ return (str||'').toString().replace(/[<>\n\r]/g,' ').substring(0,256); }
						function isSafeUrl(u){ try { const parsed = new URL(u); return ['https:'].includes(parsed.protocol); } catch(e){ return false; } }
						function pushNotify() {
							if (!("Notification" in window)) { return; }
							if (Notification.permission === "default") { Notification.requestPermission(); }
							if (Notification.permission !== "granted") { return; }
							const dataObj = {
								'action': '<?php echo \esc_attr( ADVAN_PREFIX ); ?>get_notification_data',
								'_wpnonce': '<?php echo \esc_attr( \wp_create_nonce( 'advan-plugin-data' ) ); ?>'
							};
							jQuery.get({
								url: '<?php echo \esc_url( \admin_url( 'admin-ajax.php' ) ); ?>',
								data: dataObj,
								success: function(resp){
									if(!resp || !resp.data){ return; }
									let title = sanitizeField(resp.data.title);
									let body = sanitizeField(resp.data.body);
									let icon = isSafeUrl(resp.data.icon) ? resp.data.icon : '';
									let url = isSafeUrl(resp.data.url) ? resp.data.url : '';
									if(!title && !body){ return; }
									let notification = new Notification(title, { icon: icon, body: body });
									if(url){ notification.onclick = () => window.open(url); }
									setTimeout(()=>{ try{ notification.close(); }catch(e){} }, 5000);
								},
								error: function(){ /* silent fail */ }
							});
						}
						setInterval(pushNotify, pollInterval);
					});
				</script>
				<?php
			}

			// $hook is string value given add_menu_page function.
			if ( ! in_array( $hook, Miscellaneous::get_plugin_page_slugs(), true ) ) {
				return;
			}

			\wp_enqueue_style( 'advan-admin-style', \ADVAN_PLUGIN_ROOT_URL . 'css/admin/style.css', array(), \ADVAN_VERSION, 'all' );

			\wp_enqueue_script(
				'wp-api-fetch'
			);

			// Exporting CSV start.

			\wp_enqueue_style(
				'oda-admin-export-style',
				ADVAN_PLUGIN_ROOT_URL . 'css/admin-export-csv.css',
				array(),
				'1.1'
			);

			\wp_enqueue_script(
				'aadvana-admin-export-js',
				ADVAN_PLUGIN_ROOT_URL . 'js/admin-export-csv.js',
				array(),
				'1.1',
				true
			);

			\wp_localize_script(
				'aadvana-admin-export-js',
				'aadvanaExport',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'export_large_csv_nonce' ),
					'i18n'     => array(
						'starting'          => __( 'Starting export...', '0-day-analytics' ),
						'exporting'         => __( 'Exporting...', '0-day-analytics' ),
						'completed'         => __( '✅ Export complete! Downloading...', '0-day-analytics' ),
						'cancelled'         => __( '❌ Export cancelled.', '0-day-analytics' ),
						'networkError'      => __( 'Network error', '0-day-analytics' ),
						'error'             => __( 'Error during export:', '0-day-analytics' ),
						'unauthorized'      => __( 'Unauthorized', '0-day-analytics' ),
						'csvExportBtnTitle' => __( 'CSV Export', '0-day-analytics' ),
					),
				)
			);
			// Exporting CSV end.

			?>
			<script>
				window.onload= ( () => {
					jQuery('a.view-source').on('click', function(e) {
						this.href += '&width=' + ( window.innerWidth - 100 ) + '&height=' + ( window.innerHeight - 100 ) ;
					});
				});
			</script>
			<?php
		}

		/**
		 * Responsible for printing the styles for the CodeMirror editor.
		 *
		 * @return void
		 *
		 * @since 1.8.5
		 */
		public static function print_styles() {
			$action = ! empty( $_REQUEST['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? \sanitize_key( $_REQUEST['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';

			if ( \in_array( $action, array( 'edit_transient', 'edit_cron', 'new_transient', 'new_cron' ), true ) ) {
				// Try to enqueue the code editor.
				$settings = \wp_enqueue_code_editor(
					array(
						'type'       => 'text/plain',
						'codemirror' => array(
							'indentUnit' => 4,
							'tabSize'    => 4,
							'theme'      => 'cobalt',
						),
					)
				);

				// Bail if user disabled CodeMirror.
				if ( false === $settings ) {
					return;
				}

				// Target the textarea.
				\wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery( function() { wp.codeEditor.initialize( "transient-editor", %s ); } );',
						\wp_json_encode( $settings )
					)
				);

				// Custom styling.
				\wp_add_inline_style(
					'code-editor',
					'.CodeMirror-wrap {
                    width: 99%;
                    border: 1px solid #8c8f94;
                    border-radius: 3px;
                    overflow: hidden;
                }
                .CodeMirror-gutters {
                    background: transparent;
                }'
				);
			}
		}

		/**
		 * Returns the current options.
		 * Fills the current options array with values if empty.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function get_current_options(): array {
			if ( empty( self::$current_options ) ) {

				// Get the current settings or setup some defaults if needed.
				self::$current_options = \get_option( ADVAN_SETTINGS_NAME );
				if ( ! self::$current_options ) {

					self::$current_options = self::get_default_options();
					// Ensure sensitive fields are stored encrypted at rest.
					$to_store = self::$current_options;
					Secure_Store::encrypt_sensitive_fields( $to_store );
					self::store_options( $to_store );
				}

				// Ensure any missing default options are set. Persist only when we added keys.
				$defaults = self::get_default_options();
				$added    = false;
				foreach ( $defaults as $key => $value ) {
					if ( ! isset( self::$current_options[ $key ] ) ) {
						self::$current_options[ $key ] = $value;
						$added                         = true;
					}
				}

				if ( $added ) {
					$to_store = self::$current_options;
					Secure_Store::encrypt_sensitive_fields( $to_store );
					self::store_options( $to_store );
				}

				// Decrypt sensitive fields for runtime and migrate legacy plaintext on the fly.
				if ( is_array( self::$current_options ) ) {
					$migrated = Secure_Store::decrypt_sensitive_fields( self::$current_options );
					if ( $migrated ) {
						$to_store = self::$current_options;
						Secure_Store::encrypt_sensitive_fields( $to_store );
						self::store_options( $to_store );
					}
				}
			}

			return self::$current_options;
		}

		/**
		 * Returns current option or one stored in the defaults if not present in the current options.
		 *
		 * @param string $option - The name of the option to return value for.
		 *
		 * @return mixed
		 *
		 * @since 2.8.0
		 */
		public static function get_option( string $option ) {

			$current = self::get_current_options();

			if ( ! isset( $current[ $option ] ) ) {
				$current = self::get_default_options();

				if ( ! isset( $current[ $option ] ) ) {
					return null;
				}
			}

			return $current[ $option ];
		}

		/**
		 * Stores the options in the database
		 *
		 * @param array $options - The array with the options to store.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function store_options( array $options ): void {
			global $wpdb;
			// Ensure option exists with autoload = no (prevents loading secrets on every request).
			if ( false === \get_option( ADVAN_SETTINGS_NAME, false ) ) {
				\add_option( ADVAN_SETTINGS_NAME, $options, '', 'no' );
				return;
			}
			\update_option( ADVAN_SETTINGS_NAME, $options );
			// Force autoload = no for existing installs.
			$wpdb->update( $wpdb->options, array( 'autoload' => 'no' ), array( 'option_name' => ADVAN_SETTINGS_NAME ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		/**
		 * Returns the default plugin options
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function get_default_options(): array {

			if ( empty( self::$default_options ) ) {
				// Define default options.
				self::$default_options = array(
					'menu_admins_only'                 => true,
					'live_notifications_admin_bar'     => true,
					'environment_type_admin_bar'       => true,
					'protected_config_source'          => true,
					'keep_reading_error_log'           => false,
					'plugin_debug_enable'              => false,
					'plugin_exception_handler_disable' => false,
					'advana_requests_enable'           => true,
					'advana_http_requests_disable'     => false,
					'advana_rest_requests_disable'     => false,
					'no_rest_api_monitor'              => false,
					'no_wp_die_monitor'                => false,
					'advana_server_info_admin_bar_refresh_interval' => 10,
					'keep_error_log_records_truncate'  => 10,
					'plugin_version_switch_count'      => 3,
					'cron_module_enabled'              => true,
					'file_editor_module_enabled'       => false,
					'show_active_plugins_first'        => true,
					'requests_module_enabled'          => true,
					'fatals_module_enabled'            => true,
					'wp_mail_module_enabled'           => true,
					'transients_module_enabled'        => true,
					'tables_module_enabled'            => true,
					'snippets_module_enabled'          => true,
					'snippets_temp_storage'            => 'uploads',
					'hooks_capture_module_enabled'     => true,
					'advana_hooks_capture_clear'       => 'weekly',
					'server_info_module_enabled'       => true,
					'advana_server_info_mem_enable'    => true,
					'advana_server_info_hdd_enable'    => true,
					'advana_server_info_cpu_enable'    => true,
					'advana_rest_requests_clear'       => 'weekly',
					'advana_mail_logging_clear'        => 'weekly',
					'advana_error_log_clear'           => '-1',
					'browser_notifications_seconds'    => 10,
					'browser_notifications_not_send'   => false,
					'from_email'                       => '',
					'from_email_name'                  => '',
					'smtp_host'                        => '',
					'smtp_port'                        => '',
					'smtp_username'                    => '',
					'smtp_password'                    => '',
					'encryption_type'                  => 'none',
					'bypass_ssl_verification'          => false,
					'slack_notifications'              => array(
						'all' => array(
							'channel'    => '',
							'auth_token' => '',
						),
					),
					'telegram_notifications'           => array(
						'all' => array(
							'channel'    => '',
							'auth_token' => '',
						),
					),
					'severities'                       => array(
						'deprecated'     => array(
							'name'    => __( 'Deprecated', '0-day-analytics' ),
							'color'   => '#c4b576',
							'display' => true,
						),
						'error'          => array(
							'name'    => __( 'Error', '0-day-analytics' ),
							'color'   => '#ffb3b3',
							'display' => true,
						),
						'success'        => array(
							'name'    => __( 'Success', '0-day-analytics' ),
							'color'   => '#00ff00',
							'display' => true,
						),
						'info'           => array(
							'name'    => __( 'Info', '0-day-analytics' ),
							'color'   => '#aeaeec',
							'display' => true,
						),
						'notice'         => array(
							'name'    => __( 'Notice', '0-day-analytics' ),
							'color'   => '#feeb8e',
							'display' => true,
						),
						'warning'        => array(
							'name'    => __( 'Warning', '0-day-analytics' ),
							'color'   => '#ffff00',
							'display' => true,
						),
						'fatal'          => array(
							'name'    => __( 'Fatal', '0-day-analytics' ),
							'color'   => '#f09595',
							'display' => true,
						),
						'parse'          => array(
							'name'    => __( 'Parse', '0-day-analytics' ),
							'color'   => '#e3bb8d',
							'display' => true,
						),
						'user'           => array(
							'name'    => __( 'User', '0-day-analytics' ),
							'color'   => '#85b395',
							'display' => true,
						),
						'not set'        => array(
							'name'    => __( 'Not Set', '0-day-analytics' ),
							'color'   => '#7a6f72',
							'display' => true,
						),
						'request'        => array(
							'name'    => __( 'Request', '0-day-analytics' ),
							'color'   => '#759b71',
							'display' => true,
						),
						'rest_no_route'  => array(
							'name'    => __( 'Rest No Route', '0-day-analytics' ),
							'color'   => '#759b71',
							'display' => true,
						),
						'rest_forbidden' => array(
							'name'    => __( 'Rest Forbidden', '0-day-analytics' ),
							'color'   => '#759b71',
							'display' => true,
						),
					),
				);
			}

			return self::$default_options;
		}

		/**
		 * Returns the stored main menu hook
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function get_main_menu_page_hook() {
			return self::$hook;
		}

		/**
		 * Add to Admin
		 *
		 * Add the options page to the admin menu
		 *
		 * @since 1.1.0
		 */
		public static function add_options_page() {

			if ( self::get_option( 'menu_admins_only' ) && ! \current_user_can( 'manage_options' ) ) {
				return;
			} else {

				$base = 'base';

				$base .= '64_en';

				$base .= 'code';

				self::$hook = \add_menu_page(
					ADVAN_INNER_NAME,
					ADVAN_INNER_NAME . self::get_updates_count_html(),
					( ( self::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ),
					Logs_List::MENU_SLUG,
					array( Logs_List_View::class, 'render' ),
					'data:image/svg+xml;base64,' . $base( file_get_contents( \ADVAN_PLUGIN_ROOT . 'assets/icon.svg' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					3
				);

				\add_filter( 'manage_' . self::$hook . '_columns', array( Logs_List::class, 'manage_columns' ) );

				Logs_List::add_screen_options( self::$hook );

				\register_setting(
					\ADVAN_SETTINGS_NAME,
					\ADVAN_SETTINGS_NAME,
					array(
						self::class,
						'collect_and_sanitize_options',
					)
				);

				\add_submenu_page(
					Logs_List::MENU_SLUG,
					ADVAN_INNER_NAME,
					\esc_html__( 'Error Log viewer', '0-day-analytics' ),
					( ( self::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
					Logs_List::MENU_SLUG,
					array( Logs_List_View::class, 'render' ),
					1
				);

				if ( self::get_option( 'server_info_module_enabled' ) ) {
					\add_submenu_page(
						Logs_List::MENU_SLUG,
						__( 'Server Info', '0-day-analytics' ),
						__( 'Server Info', '0-day-analytics' ),
						'manage_options',
						'advan_system_analytics',
						array( System_Analytics::class, 'render_admin_page' ),
						7
					);
				}

				/* Fatals start */
				if ( self::get_option( 'fatals_module_enabled' ) ) {
					Fatals_List::menu_add();
				}
				/* Fatals end */

				/* Crons start */
				if ( self::get_option( 'cron_module_enabled' ) ) {
					Crons_List::menu_add();
				}
				/* Crons end */

				/* File Editor start */
				if ( self::get_option( 'file_editor_module_enabled' ) ) {
					File_Editor::menu_add();
				}
				/* File Editor end */

				/* Transients */
				if ( self::get_option( 'transients_module_enabled' ) ) {
					Transients_List::menu_add();
				}
				/* Transients end */

				/* WP Mail start */
				if ( self::get_option( 'wp_mail_module_enabled' ) ) {
					WP_Mail_List::menu_add();
				}
				/* WP Mail end */

				/* Snippets start */
				if ( self::get_option( 'snippets_module_enabled' ) ) {
					Snippets_List::menu_add();
				}
				/* Snippets end */

				/* Table */
				if ( self::get_option( 'tables_module_enabled' ) ) {
					Table_List::menu_add();
				}
				/* Table end */

				/* Hooks Capture start */
				if ( self::get_option( 'hooks_capture_module_enabled' ) ) {
					\ADVAN\Lists\Hooks_Capture_List::menu_add();
					\ADVAN\Lists\Hooks_Management_List::menu_add();
				}
				/* Hooks Capture end */

				/* Requests start */
				if ( self::get_option( 'requests_module_enabled' ) ) {
					Requests_List::menu_add();
				}
				/* Requests end */

				if ( ! is_a( WP_Helper::check_debug_status(), '\WP_Error' ) && ! is_a( WP_Helper::check_debug_log_status(), '\WP_Error' ) && self::get_option( 'live_notifications_admin_bar' ) ) {
					\add_action( 'admin_bar_menu', array( __CLASS__, 'live_notifications' ), 1000, 1 );
					\add_action(
						'admin_enqueue_scripts',
						function() {
							\wp_enqueue_script(
								self::LIVE_NOTIF_JS_MODULE,
								\ADVAN_PLUGIN_ROOT_URL . 'js/admin/endpoints.js',
								array( 'wp-api-fetch', 'wp-dom-ready', 'wp-i18n' ),
								\ADVAN_VERSION,
								array( 'in_footer' => true )
							);
						}
					);
				}

				\add_action( 'admin_footer', array( __CLASS__, 'show_error_count' ), \PHP_INT_MAX );

				\add_action( 'load-' . self::$hook, array( __CLASS__, 'aadvana_common_help' ) );

				$settings_hook = \add_submenu_page(
					Logs_List::MENU_SLUG,
					\esc_html__( 'Settings', '0-day-analytics' ),
					\esc_html__( 'Settings', '0-day-analytics' ),
					'manage_options', // No capability requirement.
					self::SETTINGS_MENU_SLUG,
					array( __CLASS__, 'aadvana_show_options' ),
					301
				);

				\add_action( 'load-' . $settings_hook, array( __CLASS__, 'aadvana_settings_help' ) );

				if ( ! self::is_plugin_settings_page() ) {
					return;
				}

				// Reset settings.
				if ( isset( $_REQUEST['reset-settings'] ) && \check_admin_referer( 'reset-plugin-settings', 'reset_nonce' ) ) {

					\delete_option( ADVAN_SETTINGS_NAME );
					// \get_option( self::SETTINGS_VERSION );

					Crons_Helper::clear_events( ADVAN_PREFIX . 'request_table_clear' );
					Crons_Helper::clear_events( ADVAN_PREFIX . 'error_log_clear' );

					// Redirect to the plugin settings page.
					\wp_safe_redirect(
						\add_query_arg(
							array(
								'page'  => Logs_List::MENU_SLUG,
								'reset' => 'true',
							),
							\admin_url( 'admin.php' )
						)
					);
					exit;
				} elseif ( isset( $_REQUEST['export-settings'] ) && \check_admin_referer( 'export-plugin-settings', 'export_nonce' ) ) { // Export Settings.

					if ( ! \current_user_can( 'manage_options' ) ) {
						\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
					}

					global $wpdb;

					$stored_options = $wpdb->get_results(
						$wpdb->prepare( 'SELECT option_name, option_value FROM ' . $wpdb->options . ' WHERE option_name = %s', \ADVAN_SETTINGS_NAME ),
						ARRAY_A
					);

					header( 'Cache-Control: public, must-revalidate' );
					header( 'Pragma: hack' );
					header( 'Content-Type: text/plain' );
					header( 'Content-Disposition: attachment; filename="' . ADVAN_TEXTDOMAIN . '-options-' . gmdate( 'dMy' ) . '.dat"' );

					if ( ! empty( $stored_options ) && isset( $stored_options[0]['option_value'] ) ) {
						$data = json_decode( $stored_options[0]['option_value'], true );
						if ( json_last_error() !== JSON_ERROR_NONE ) {
							$data = unserialize( $stored_options[0]['option_value'], array( 'allowed_classes' => false ) );
						}
						if ( is_array( $data ) ) {
							self::mask_sensitive_fields( $data );
						}
						// Include current plugin version in the exported payload so imports
						// can make decisions based on source version. Do not rely on this
						// value during import without proper validation.
						if ( is_array( $data ) ) {
							$data['plugin_version'] = \ADVAN_VERSION;
						}
						echo \wp_json_encode( $data );
					} else {
						echo \wp_json_encode( array() );
					}
					die();
				} elseif ( isset( $_FILES[ self::SETTINGS_FILE_FIELD ] ) && \check_admin_referer( 'aadvana-plugin-data', 'aadvana-security' ) ) { // Import the settings.
					$options = array();
					if ( isset( $_FILES ) &&
						isset( $_FILES[ self::SETTINGS_FILE_FIELD ] ) &&
						isset( $_FILES[ self::SETTINGS_FILE_FIELD ]['error'] ) &&
						! $_FILES[ self::SETTINGS_FILE_FIELD ]['error'] > 0 &&
						isset( $_FILES[ self::SETTINGS_FILE_FIELD ]['tmp_name'] ) ) {

							\add_filter(
								'upload_mimes',
								function( $mimes ) {
									$mimes['dat'] = 'application/json';
									return $mimes;
								}
							);

						// Basic size limit (50KB) to avoid large payload abuse.
						if ( isset( $_FILES[ self::SETTINGS_FILE_FIELD ]['size'] ) && (int) $_FILES[ self::SETTINGS_FILE_FIELD ]['size'] > 51200 ) {
							// Oversized file, abort import.
							$_FILES[ self::SETTINGS_FILE_FIELD ] = array();
						} else {
							$ft = \wp_check_filetype_and_ext(
								$_FILES[ self::SETTINGS_FILE_FIELD ]['tmp_name'],
								$_FILES[ self::SETTINGS_FILE_FIELD ]['name'],
								array(
									'json' => 'application/json',
									'txt'  => 'text/plain',
									'dat'  => 'application/json',
								)
							);
							if ( empty( $ft['ext'] ) || ! in_array( $ft['ext'], array( 'json', 'txt', 'dat' ), true ) ) {
								// Invalid file type.
								$_FILES[ self::SETTINGS_FILE_FIELD ] = array();
							} else {
								$path    = \sanitize_text_field( \wp_unslash( $_FILES[ self::SETTINGS_FILE_FIELD ]['tmp_name'] ) );
								$content = '';

								// Try WP_Filesystem first; fall back to native PHP on failure.
								try {
									global $wp_filesystem;
									if ( ! isset( $wp_filesystem ) || ! is_object( $wp_filesystem ) ) {
										\WP_Filesystem();
									}
									if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) && $wp_filesystem->exists( $path ) ) {
										$tmp = $wp_filesystem->get_contents( $path );
										if ( false !== $tmp && '' !== $tmp ) {
											$content = (string) $tmp;
										}
									}
								} catch ( \Throwable $e ) {
									// Ignore and fall back below.
								}

								if ( '' === $content && \is_readable( $path ) ) {
									$content = @file_get_contents( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
								}

								$options = array();
								if ( is_string( $content ) && '' !== $content ) {
									$options = json_decode( $content, true );
								}
								if ( ! is_array( $options ) ) {
									$options = array();
								}
								if ( ! empty( $options ) ) {
									// If the imported payload contains a plugin version, compare it
									// (after normalizing) to the current plugin version. If the
									// imported version is lower than the current one, write the
									// imported version into the global stored plugin version so the
									// migration system will run subsequent migrations accordingly.
									if ( isset( $options['plugin_version'] ) && is_string( $options['plugin_version'] ) && '' !== trim( $options['plugin_version'] ) ) {
										try {
											$import_version_norm  = Abstract_Migration::normalize_version( (string) $options['plugin_version'] );
											$current_version_norm = Abstract_Migration::normalize_version( (string) \ADVAN_VERSION );
											if ( (int) $import_version_norm < (int) $current_version_norm ) {
												// Store the imported version into WP options so migrations run.
												\update_option( self::SETTINGS_VERSION, \sanitize_text_field( $options['plugin_version'] ) );
											}
										} catch ( \Throwable $e ) {
											// Ignore malformed version strings — treat as untrusted input.
										}
										// Remove the helper field so it is not persisted inside plugin options.
										unset( $options['plugin_version'] );
									}

									\remove_filter( 'sanitize_option_' . ADVAN_SETTINGS_NAME, array( self::class, 'collect_and_sanitize_options' ) );
									\update_option( ADVAN_SETTINGS_NAME, self::collect_and_sanitize_options( $options, true ) );
								}
							}
						}
					}

					\wp_safe_redirect(
						\add_query_arg(
							array(
								'page'   => Logs_List::MENU_SLUG,
								'import' => 'true',
							),
							\admin_url( 'admin.php' )
						)
					);
					exit;
				}
			}
		}

		/**
		 * Return the updates count markup.
		 *
		 * @return string Updates count markup, empty string if no updates available.
		 *
		 * @since 1.1.0
		 */
		public static function get_updates_count_html(): string {

			$count_html = sprintf(
				' <span id="advan-errors-menu" style="display:none" class="update-plugins"><span class="update-count">%s</span></span>',
				''
				// \number_format_i18n( $count )
			);

			return $count_html;
		}

		/**
		 * Add Options Help
		 *
		 * Add help tab to options screen
		 *
		 * @since 1.1.0
		 */
		public static function aadvana_common_help() {

			$screen = WP_Helper::get_wp_screen();

			$suffix = '';

			if ( WP_Helper::is_multisite() ) {
				$suffix = '-network';
			}

			if ( Logs_List::PAGE_SLUG . $suffix === $screen->base || Logs_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => self::add_help_content_error_log(),
					)
				);
			}

			if ( Transients_List::PAGE_SLUG . $suffix === $screen->base || Transients_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => Transients_View::add_help_content_transients(),
					)
				);
			}

			if ( Crons_List::PAGE_SLUG . $suffix === $screen->base || Crons_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => Crons_View::add_help_content_crons(),
					)
				);
			}

			if ( Table_List::PAGE_SLUG . $suffix === $screen->base || Table_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'Table Info', '0-day-analytics' ),
						'content' => Table_View::add_config_content_table(),
					)
				);
				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-info-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => Table_View::add_help_content_table(),
					)
				);
			}

			if ( Requests_List::PAGE_SLUG . $suffix === $screen->base || Requests_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'Requests Table Info', '0-day-analytics' ),
						'content' => Requests_View::add_config_content_table(),
					)
				);
				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-info-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => Requests_View::add_help_content_table(),
					)
				);
			}

			if ( WP_Mail_List::PAGE_SLUG . $suffix === $screen->base || WP_Mail_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'WP Mail Log Table Info', '0-day-analytics' ),
						'content' => WP_Mail_View::add_config_content_table(),
					)
				);
				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-info-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => WP_Mail_View::add_help_content_table(),
					)
				);
			}

			if ( Fatals_List::PAGE_SLUG . $suffix === $screen->base || Fatals_List::PAGE_SLUG === $screen->base ) {

				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-help-tab',
						'title'   => __( 'WP Mail Log Table Info', '0-day-analytics' ),
						'content' => Fatals_View::add_config_content_table(),
					)
				);
				$screen->add_help_tab(
					array(
						'id'      => 'advanced-analytics-info-tab',
						'title'   => __( 'Help', '0-day-analytics' ),
						'content' => Fatals_View::add_help_content_table(),
					)
				);
			}

			$screen->set_help_sidebar( self::add_sidebar_content() );
		}

		/**
		 * Add Options Help
		 *
		 * Add help tab to options screen
		 *
		 * @since 1.1.0
		 */
		public static function aadvana_settings_help() {

			$add_style = '
			<style>
				.' . \esc_attr( self::PAGE_SLUG ) . ' #screen-meta-links {
					z-index: 10;
					position: relative;
				}
			</style>';

			$screen = WP_Helper::get_wp_screen();

			$screen->add_help_tab(
				array(
					'id'      => 'advanced-analytics-help-tab',
					'title'   => __( 'Help', '0-day-analytics' ),
					'content' => $add_style . self::add_help_content(),
				)
			);

			$screen->set_help_sidebar( self::add_sidebar_content() );
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 1.9.8.1
		 */
		public static function add_help_content_error_log() {

			$help_text  = '<p>' . __( 'This screen allows you to see last occurred records (last are first), check their sources, see the code responsible / involved in given error.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can specify how many errors to be shown (up to 100), which columns to see or filter error by severity.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can truncate error log (clear it) or truncate it but leave last records (from settings you can specify how many records you want to be kept).', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Right under the list, there is a console-like window where you can see the raw error list, everything you select there (with mouse) is automatically copied in you clipboard, so you can use it in chat channel or share it easily.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can see the size of your log file and download it if you need to.', '0-day-analytics' ) . '</p>';

			return $help_text;
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 1.1.0
		 */
		public static function add_help_content() {

			$help_text  = '<p>' . __( 'This screen allows you to specify the options for the 0 Day Analytics plugin.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Here adjust the plugin to your specific needs.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Remember to click the Save Changes button when on settings page for new settings to take effect.', '0-day-analytics' ) . '</p>';

			return $help_text;
		}

		/**
		 * Options Help Sidebar
		 *
		 * Add a links sidebar to the options help
		 *
		 * @return string  Help Text
		 *
		 * @since 1.1.0
		 */
		public static function add_sidebar_content() {

			$help_text  = '<p><strong>' . __( 'For more information:', '0-day-analytics' ) . '</strong></p>';
			$help_text .= '<p><a href="https://wordpress.org/plugins/0-day-analytics/" target="__blank">' . __( 'Instructions', '0-day-analytics' ) . '</a></p>';
			$help_text .= '<p><a href="https://wordpress.org/support/plugin/0-day-analytics" target="__blank">' . __( 'Support Forum', '0-day-analytics' ) . '</a></p>';

			return $help_text;
		}

		/**
		 * Shows the save button in the settings
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function save_button() {

			?>
			<div class="aadvana-panel-submit">
				<button name="<?php echo \esc_attr( \ADVAN_SETTINGS_NAME ); ?>[save_button]" class="aadvana-save-button aadvana-primary-button button button-primary button-hero"
						type="submit"><?php esc_html_e( 'Save Changes', '0-day-analytics' ); ?></button>
			</div>
			<?php
		}

		/**
		 * The Settings Panel UI
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function aadvana_show_options() {

			\wp_enqueue_script( 'aadvana-admin-scripts', \ADVAN_PLUGIN_ROOT_URL . 'js/admin/aadvana-settings.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker', 'jquery-ui-autocomplete' ), \ADVAN_VERSION, false );

			\wp_enqueue_style( 'advan-admin-style', \ADVAN_PLUGIN_ROOT_URL . 'css/admin/style.css', array(), \ADVAN_VERSION, 'all' );
			\wp_enqueue_media();

			$settings_tabs = self::build_options_tabs();

			?>

			<div id="aadvana-page-overlay"></div>

			<div id="aadvana-saving-settings">
				<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
					<circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
					<path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
					<path class="checkmark__error_1" d="M38 38 L16 16 Z" />
					<path class="checkmark__error_2" d="M16 38 38 16 Z" />
				</svg>
			</div>

			<div class="aadvana-panel wrap">

				<div class="aadvana-panel-tabs">
					<div class="aadvana-logo">
						<svg fill="currentColor" height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  viewBox="0 0 512.001 512.001" xml:space="preserve">
							<g>
								<g>
								<path d="M484.312,86.624H19.688C8.812,86.624,0,95.436,0,106.312v291.376c0,10.876,8.812,19.688,19.688,19.688h464.624
			c10.876,0,19.688-8.812,19.688-19.688V106.312C504,95.436,495.188,86.624,484.312,86.624z M330.56,149.624h71.068V189H330.56
			c-10.884,0-19.736-8.804-19.736-19.688C310.824,158.428,319.676,149.624,330.56,149.624z M330.56,208.688h27.752v39.376H330.56
			c-10.884,0-19.736-8.804-19.736-19.688C310.824,217.492,319.676,208.688,330.56,208.688z M330.56,263.812h55.316v39.376H330.56
			c-10.884,0-19.736-8.804-19.736-19.688C310.824,272.616,319.676,263.812,330.56,263.812z M149.164,362.156
			c-51.984,0-94.276-42.296-94.276-94.276c0-51.98,42.524-94.272,94.508-94.272c2.172,0,4.168,1.764,4.168,3.936v86.272h85.94
			c2.172,0,3.936,1.828,3.936,4C243.44,319.8,201.148,362.156,149.164,362.156z M262.916,248.064H172.58
			c-2.172,0-3.264-1.42-3.264-3.596v-90.34c0-2.172,1.424-3.936,3.6-3.936c51.98,0,94.104,42.12,94.104,94.1
			C267.02,246.472,265.088,248.064,262.916,248.064z M334.688,358.312h-4.128c-10.884,0-19.736-8.804-19.736-19.688
			c0-10.884,8.852-19.688,19.736-19.688h4.128V358.312z M428.904,358.312h-66.656v-39.376H428.9
			c10.884,0,19.736,8.804,19.736,19.688C448.636,349.508,439.784,358.312,428.904,358.312z M428.904,303.188H413.44v-39.376h15.464
			c10.884,0,19.736,8.804,19.736,19.688C448.64,294.384,439.784,303.188,428.904,303.188z M428.904,248.064h-43.028v-39.376h43.028
			c10.884,0,19.736,8.804,19.736,19.688C448.64,239.26,439.784,248.064,428.904,248.064z M429.188,189.272v-39.46
			c11.812,0.028,19.688,8.864,19.688,19.732S441,189.248,429.188,189.272z"/>
								</g>
							</g>
						</svg>
					</div>

					<ul>
					<?php
					foreach ( $settings_tabs as $tab => $settings ) {

						if ( ! empty( $settings['title'] ) ) {
							$icon  = $settings['icon'];
							$title = $settings['title'];
							?>

							<li class="aadvana-tabs aadvana-options-tab-<?php echo \esc_attr( $tab ); ?>">
								<a href="#aadvana-options-tab-<?php echo \esc_attr( $tab ); ?>">
									<span class="dashicons-before dashicons-<?php echo \esc_html( $icon ); ?> aadvana-icon-menu"></span>
								<?php echo \esc_html( $title ); ?>
								</a>
							</li>
						<?php } else { ?>
							<li class="aadvana-tab-menu-head"><?php echo $settings;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php
						}
					}

					?>
					</ul>
					<div class="clear"></div>
				</div> <!-- .aadvana-panel-tabs -->

				<div class="aadvana-panel-content">

					<form method="post" name="<?php echo \esc_attr( ADVAN_PREFIX ); ?>form" id="<?php echo \esc_attr( ADVAN_PREFIX ); ?>form" enctype="multipart/form-data">

						<div class="aadvana-tab-head">
							<div id="aadvana-options-search-wrap">
								<input id="aadvana-panel-search" type="text" placeholder="<?php esc_html_e( 'Search', '0-day-analytics' ); ?>">
								<div id="aadvana-search-list-wrap" class="has-custom-scroll">
									<ul id="aadvana-search-list"></ul>
								</div>
							</div>

							<div class="awefpanel-head-elements">

							<?php \do_action( ADVAN_PREFIX . 'settings_save_button' ); ?>

							
								<ul>
									<li>
										<div id="awefpanel-darkskin-wrap">
											<span class="darkskin-label"><svg height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg"><title/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="256" x2="256" y1="48" y2="96"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="256" x2="256" y1="416" y2="464"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="403.08" x2="369.14" y1="108.92" y2="142.86"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="142.86" x2="108.92" y1="369.14" y2="403.08"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="464" x2="416" y1="256" y2="256"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="96" x2="48" y1="256" y2="256"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="403.08" x2="369.14" y1="403.08" y2="369.14"/><line style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px" x1="142.86" x2="108.92" y1="142.86" y2="108.92"/><circle cx="256" cy="256" r="80" style="fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px"/></svg></span>
											<input id="awefpanel-darkskin" class="aadvana-js-switch" type="checkbox" value="true">
											<span class="darkskin-label"><svg height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg"><title/><path d="M160,136c0-30.62,4.51-61.61,16-88C99.57,81.27,48,159.32,48,248c0,119.29,96.71,216,216,216,88.68,0,166.73-51.57,200-128-26.39,11.49-57.38,16-88,16C256.71,352,160,255.29,160,136Z" style="fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px"/></svg></span>
											<script>
												if( 'undefined' != typeof localStorage ){
													var skin = localStorage.getItem('aadvana-backend-skin');
													if( skin == 'dark' ){
														document.getElementById('awefpanel-darkskin').setAttribute('checked', 'checked');

														var element = document.getElementsByTagName("html")[0];
														element.classList.add("aadvana-darkskin");
													}
												}
											</script>
										</div>
									</li>

								</ul>
							</div>
						</div>

						<?php
						foreach ( $settings_tabs as $tab => $settings ) {
							if ( ! empty( $settings['title'] ) ) {
								?>
							<!-- <?php echo \esc_attr( $tab ); ?> Settings -->
							<div id="aadvana-options-tab-<?php echo \esc_attr( $tab ); ?>" class="tabs-wrap">

								<?php
								include_once \ADVAN_PLUGIN_ROOT . 'classes/vendor/settings/settings-options/' . $tab . '.php';

								\do_action( ADVAN_PREFIX . 'plugin_options_tab_' . $tab );
								?>

							</div>
								<?php
							}
						}
						?>

						<?php \wp_nonce_field( 'aadvana-plugin-data', 'aadvana-security' ); ?>
						<input type="hidden" name="action" value="<?php echo \esc_attr( ADVAN_PREFIX ); ?>plugin_data_save" />

						<div class="aadvana-footer">

						<?php \do_action( ADVAN_PREFIX . 'settings_save_button' ); ?>
						</div>
					</form>

				</div><!-- .aadvana-panel-content -->
				<div class="clear"></div>

			</div><!-- .aadvana-panel -->

			<?php
		}

		/**
		 * The settings panel option tabs.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function build_options_tabs(): array {

			$settings_tabs = array(

				// 'head-general' => \esc_html__( 'General Settings', '0-day-analytics' ),

				// 'general'      => array(
				// 'icon'  => 'admin-generic',
				// 'title' => \esc_html__( 'General', '0-day-analytics' ),
				// ),

				// 'head-global'  => \esc_html__( 'Global Settings', '0-day-analytics' ),

				// 'backup'       => array(
				// 'icon'  => 'migrate',
				// 'title' => \esc_html__( 'Export/Import', '0-day-analytics' ),
				// ),

				'head-error-log-list'  => \esc_html__( 'Error Log', '0-day-analytics' ),

				'error-log-list'       => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'Error Log Listing', '0-day-analytics' ),
				),

				'head-cron-list'       => \esc_html__( 'Cron Log', '0-day-analytics' ),

				'cron-list'            => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'Cron options', '0-day-analytics' ),
				),

				'head-transients-list' => \esc_html__( 'Transients Log', '0-day-analytics' ),

				'transient-list'       => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'Transient options', '0-day-analytics' ),
				),

				'head-requests-list'   => \esc_html__( 'Requests Log', '0-day-analytics' ),

				'request-list'         => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'Request options', '0-day-analytics' ),
				),

				'head-server-info'     => \esc_html__( 'Server info', '0-day-analytics' ),

				'server-info'          => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'Server info options', '0-day-analytics' ),
				),

				'head-table-list'      => \esc_html__( 'Tables Viewer', '0-day-analytics' ),

				'table-list'           => array(
					'icon'  => 'editor-table',
					'title' => \esc_html__( 'Tables options', '0-day-analytics' ),
				),

				'head-mail-list'       => \esc_html__( 'Mails Viewer', '0-day-analytics' ),

				'mail-list'            => array(
					'icon'  => 'editor-table',
					'title' => \esc_html__( 'Mail options', '0-day-analytics' ),
				),

				'head-fatals-list'     => \esc_html__( 'PHP error Log', '0-day-analytics' ),

				'fatals-list'          => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'PHP error Log options', '0-day-analytics' ),
				),

				'head-file-editor'     => \esc_html__( 'File Editor', '0-day-analytics' ),

				'file-editor'          => array(
					'icon'  => 'list-view',
					'title' => \esc_html__( 'File Editor options', '0-day-analytics' ),
				),

				'head-notifications'   => \esc_html__( 'Notifications', '0-day-analytics' ),

				'notifications'        => array(
					'icon'  => 'bell',
					'title' => \esc_html__( 'Notification options', '0-day-analytics' ),
				),

				'head-snippets'        => \esc_html__( 'Snippets', '0-day-analytics' ),

				'snippets'             => array(
					'icon'  => 'editor-code',
					'title' => \esc_html__( 'Snippets', '0-day-analytics' ),
				),

				'head-hooks-capture'   => \esc_html__( 'Hooks Capture', '0-day-analytics' ),

				'hooks-capture'        => array(
					'icon'  => 'admin-generic',
					'title' => \esc_html__( 'Hooks Capture Module', '0-day-analytics' ),
				),

				'head-advanced'        => \esc_html__( 'Advanced', '0-day-analytics' ),

				'advanced'             => array(
					'icon'  => 'admin-tools',
					'title' => \esc_html__( 'Advanced', '0-day-analytics' ),
				),

				'backup'               => array(
					'icon'  => 'migrate',
					'title' => \esc_html__( 'Export/Import', '0-day-analytics' ),
				),

				'system-info'          => array(
					'icon'  => 'wordpress-alt',
					'title' => \esc_html__( 'System Info', '0-day-analytics' ),
				),
			);

			return $settings_tabs;
		}

		/**
		 * Creates an option and draws it
		 *
		 * @param array $value - The array with option data.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function build_option( array $value ) {
			$data = null;

			if ( empty( $value['id'] ) ) {
				$value['id'] = ' ';
			}

			if ( isset( self::get_current_options()[ $value['id'] ] ) ) {
				$data = self::get_current_options()[ $value['id'] ];
			}

			Settings_Builder::create( $value, \ADVAN_SETTINGS_NAME . '[' . $value['id'] . ']', $data );
		}

		/**
		 * Setter method for the current options
		 *
		 * @param array $options - Array with the options to store.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function set_current_options( array $options ) {
			return self::$current_options = $options; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
		}

		/**
		 * Checks if current page is plugin settings page
		 *
		 * @return boolean
		 *
		 * @since 1.1.0
		 */
		public static function is_plugin_settings_page() {

			$current_page = ! empty( $_REQUEST['page'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			return Logs_List::MENU_SLUG === $current_page || self::OPTIONS_PAGE_SLUG === $current_page || Crons_List::CRON_MENU_SLUG === $current_page || Transients_List::TRANSIENTS_MENU_SLUG === $current_page || Table_List::TABLE_MENU_SLUG === $current_page || self::SETTINGS_MENU_SLUG === $current_page || Requests_List::REQUESTS_MENU_SLUG === $current_page || WP_Mail_List::WP_MAIL_MENU_SLUG === $current_page || Fatals_List::FATALS_MENU_SLUG === $current_page || System_Analytics::SYS_MENU_SLUG === $current_page || File_Editor::FILE_EDITOR_MENU_SLUG === $current_page || Snippets_List::MENU_SLUG === $current_page || Hooks_Management_List::MENU_SLUG === $current_page || Hooks_Capture_List::MENU_SLUG === $current_page;
		}

		/**
		 * Extracts the current version of the plugin
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function get_version(): string {
			if ( empty( self::$current_version ) ) {
				self::$current_version = (string) \get_option( self::SETTINGS_VERSION, '' );
			}

			if ( empty( self::$current_version ) ) {
				self::$current_version = '0.0.0';
			}

			return self::$current_version;
		}

		/**
		 * Stores the current version of the plugin into the global options table
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function store_version(): void {
			\update_option( self::SETTINGS_VERSION, \ADVAN_VERSION );
		}

		/**
		 * Shows live notifications in the admin bar if there are candidates.
		 *
		 * @param \WP_Admin_Bar $admin_bar - Current admin bar object.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function live_notifications( $admin_bar ) {
			if ( \current_user_can( 'manage_options' ) && \is_admin() ) {
				?>
				<style>
					#wp-admin-bar-aadvan-menu {
						overflow: auto;
						overflow-x: hidden;
						text-overflow: ellipsis;
						max-width: 50%;
						height: 30px;
						width: 400px;
					}
					/* #wpadminbar:not(.mobile) .ab-top-menu > li#wp-admin-bar-aadvan-menu:hover > .ab-item {
						background: #d7dce0;
						color: #42425d !important;
					} */
				</style>
				<?php
					$admin_bar->add_node(
						array(
							'id'    => 'aadvan-menu',
							'title' => \esc_html__( '0 day', '0-day-analytics' ),
							'href'  => \add_query_arg( 'page', Logs_List::MENU_SLUG, network_admin_url( 'admin.php' ) ),
							'meta'  => array(
								'class'      => 'aadvan-live-notif-item',
								'aria-label' => \esc_attr__( 'Analytics notifications', '0-day-analytics' ),
							),
						)
					);
			}
		}

		/**
		 * Collects the passed options, validates them and stores them.
		 *
		 * @param array $post_array - The collected settings array.
		 * @param bool  $import - The settings store comes from the imported file.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 * @since 1.9.0 - Added $import parameter to allow importing settings, without interfering with the current options (everything related to wp-config manipulation is not stored in the settings).
		 */
		public static function collect_and_sanitize_options( array $post_array, bool $import = false ): array {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', '0-day-analytics' ) );
			}

			$advanced_options = array();

			$advanced_options['menu_admins_only'] = ( array_key_exists( 'menu_admins_only', $post_array ) ) ? filter_var( $post_array['menu_admins_only'], FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['live_notifications_admin_bar'] = ( array_key_exists( 'live_notifications_admin_bar', $post_array ) ) ? filter_var( $post_array['live_notifications_admin_bar'], FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['environment_type_admin_bar'] = ( array_key_exists( 'environment_type_admin_bar', $post_array ) ) ? filter_var( $post_array['environment_type_admin_bar'], FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['protected_config_source'] = ( array_key_exists( 'protected_config_source', $post_array ) ) ? filter_var( $post_array['protected_config_source'], FILTER_VALIDATE_BOOLEAN ) : false;

			foreach ( self::get_option( 'severities' ) as $name => $severity ) {
				$advanced_options['severities'][ $name ]['color'] = ( array_key_exists( 'severity_colors_' . $name . '_color', $post_array ) && ! empty( $post_array[ 'severity_colors_' . $name . '_color' ] ) ) ? \sanitize_text_field( $post_array[ 'severity_colors_' . $name . '_color' ] ) : ( ( isset( $post_array['severities'][ $name ]['color'] ) ) ? \sanitize_text_field( $post_array['severities'][ $name ]['color'] ) : $severity['color'] );

				$advanced_options['severities'][ $name ]['display'] = ( array_key_exists( 'severity_show_' . $name . '_display', $post_array ) && ! empty( $post_array[ 'severity_show_' . $name . '_display' ] ) ) ? true : ( ( isset( $post_array['severities'][ $name ]['display'] ) ) ? (bool) $post_array['severities'][ $name ]['display'] : false );

				$advanced_options['severities'][ $name ]['name'] = self::get_option( 'severities' )[ $name ]['name'];
			}

			// Email SMTP settings start.
			$advanced_options['smtp_host'] = ( array_key_exists( 'smtp_host', $post_array ) && ! empty( $post_array['smtp_host'] ) ) ? \sanitize_text_field( $post_array['smtp_host'] ) : '';

			$advanced_options['from_email'] = ( array_key_exists( 'from_email', $post_array ) && ! empty( $post_array['from_email'] ) ) ? \sanitize_text_field( $post_array['from_email'] ) : '';

			$advanced_options['from_email_name'] = ( array_key_exists( 'from_email_name', $post_array ) && ! empty( $post_array['from_email_name'] ) ) ? \sanitize_text_field( $post_array['from_email_name'] ) : '';

			$advanced_options['smtp_port'] = ( array_key_exists( 'smtp_port', $post_array ) && ! empty( $post_array['smtp_port'] ) ) ? filter_var(
				$post_array['smtp_port'],
				\FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 65535,
					),
				)
			) : '';

			$advanced_options['encryption_type'] = ( array_key_exists( 'encryption_type', $post_array ) && ! empty( $post_array['encryption_type'] ) ) ? ( in_array( $post_array['encryption_type'], array( 'none', 'ssl', 'tls' ) ) ? $post_array['encryption_type'] : self::get_option( 'encryption_type' ) ) : 'none';

			$advanced_options['smtp_username'] = ( array_key_exists( 'smtp_username', $post_array ) && ! empty( $post_array['smtp_username'] ) ) ? \sanitize_text_field( $post_array['smtp_username'] ) : '';

			$advanced_options['smtp_password'] = ( array_key_exists( 'smtp_password', $post_array ) && ! empty( $post_array['smtp_password'] ) ) ? \sanitize_text_field( $post_array['smtp_password'] ) : '';

			$advanced_options['bypass_ssl_verification'] = ( array_key_exists( 'bypass_ssl_verification', $post_array ) ) ? filter_var( $post_array['bypass_ssl_verification'], FILTER_VALIDATE_BOOLEAN ) : false;
			// Email SMTP settings end.

			$advanced_options['slack_notifications']['all'] = array();

			if ( array_key_exists( 'slack_notification_auth_token', $post_array ) ) {

				$slack_token = ( array_key_exists( 'slack_notification_auth_token', $post_array ) && ! empty( $post_array['slack_notification_auth_token'] ) ) ? \sanitize_text_field( \wp_unslash( $post_array['slack_notification_auth_token'] ) ) : '';

				if ( ! empty( $slack_token ) ) {

					if ( 'REMOVE' === $slack_token ) {
						$advanced_options['slack_notifications']['all']['auth_token'] = '';
					} elseif ( preg_match( '/^xox[aboprsl]-[A-Za-z0-9-]{10,}$/', $slack_token ) && Slack_API::verify_slack_token( $slack_token ) ) {
						$advanced_options['slack_notifications']['all']['auth_token'] = $slack_token;
					}
				} elseif ( Slack::is_set() ) {
					$advanced_options['slack_notifications']['all']['auth_token'] = Slack::get_slack_auth_key();
				}
			} elseif ( Slack::is_set() ) {
				$advanced_options['slack_notifications']['all']['auth_token'] = Slack::get_slack_auth_key();
			}

			$advanced_options['slack_notifications']['all']['channel'] = ( array_key_exists( 'notification_default_slack_channel', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['notification_default_slack_channel'] ) ) : '';

			$advanced_options['telegram_notifications']['all'] = array();

			if ( array_key_exists( 'telegram_notification_auth_token', $post_array ) ) {

				$telegram_token = ( array_key_exists( 'telegram_notification_auth_token', $post_array ) && ! empty( $post_array['telegram_notification_auth_token'] ) ) ? \sanitize_text_field( \wp_unslash( $post_array['telegram_notification_auth_token'] ) ) : '';

				if ( ! empty( $telegram_token ) ) {

					if ( 'REMOVE' === $telegram_token ) {
						$advanced_options['telegram_notifications']['all']['auth_token'] = '';
					} elseif ( preg_match( '/^\d{5,}:[A-Za-z0-9_-]{30,}$/', $telegram_token ) && Telegram_API::verify_telegram_token( $telegram_token ) ) {
						$advanced_options['telegram_notifications']['all']['auth_token'] = $telegram_token;
					}
				} elseif ( Telegram::is_set() ) {
					$advanced_options['telegram_notifications']['all']['auth_token'] = Telegram::get_telegram_auth_key();
				}
			} elseif ( Telegram::is_set() ) {
				$advanced_options['telegram_notifications']['all']['auth_token'] = Telegram::get_telegram_auth_key();
			}

			$advanced_options['telegram_notifications']['all']['channel'] = ( array_key_exists( 'notification_default_telegram_channel', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['notification_default_telegram_channel'] ) ) : '';

			$advanced_options['keep_reading_error_log'] = ( array_key_exists( 'keep_reading_error_log', $post_array ) ) ? filter_var( $post_array['keep_reading_error_log'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['plugin_debug_enable'] = ( array_key_exists( 'plugin_debug_enable', $post_array ) ) ? filter_var( $post_array['plugin_debug_enable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['plugin_exception_handler_disable'] = ( array_key_exists( 'plugin_exception_handler_disable', $post_array ) ) ? filter_var( $post_array['plugin_exception_handler_disable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			// If keep reading error log is disabled, plugin debug must be disabled too.
			if ( false === $advanced_options['keep_reading_error_log'] ) {
				$advanced_options['plugin_debug_enable'] = false;
			}

			$advanced_options['advana_requests_enable'] = ( array_key_exists( 'advana_requests_enable', $post_array ) ) ? filter_var( $post_array['advana_requests_enable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['advana_http_requests_disable'] = ( array_key_exists( 'advana_http_requests_disable', $post_array ) ) ? filter_var( $post_array['advana_http_requests_disable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['advana_rest_requests_disable'] = ( array_key_exists( 'advana_rest_requests_disable', $post_array ) ) ? filter_var( $post_array['advana_rest_requests_disable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['no_rest_api_monitor'] = ( array_key_exists( 'no_rest_api_monitor', $post_array ) ) ? filter_var( $post_array['no_rest_api_monitor'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['no_wp_die_monitor'] = ( array_key_exists( 'no_wp_die_monitor', $post_array ) ) ? filter_var( $post_array['no_wp_die_monitor'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['browser_notifications_not_send'] = ( array_key_exists( 'browser_notifications_not_send', $post_array ) ) ? filter_var( $post_array['browser_notifications_not_send'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['advana_server_info_cpu_enable'] = ( array_key_exists( 'advana_server_info_cpu_enable', $post_array ) ) ? filter_var( $post_array['advana_server_info_cpu_enable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['advana_server_info_mem_enable'] = ( array_key_exists( 'advana_server_info_mem_enable', $post_array ) ) ? filter_var( $post_array['advana_server_info_mem_enable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['advana_server_info_hdd_enable'] = ( array_key_exists( 'advana_server_info_hdd_enable', $post_array ) ) ? filter_var( $post_array['advana_server_info_hdd_enable'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['keep_error_log_records_truncate'] = ( array_key_exists( 'keep_error_log_records_truncate', $post_array ) ) ? filter_var(
				$post_array['keep_error_log_records_truncate'],
				\FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 100,
					),
				)
			) : 10;

			$advanced_options['advana_server_info_admin_bar_refresh_interval'] = ( array_key_exists( 'advana_server_info_admin_bar_refresh_interval', $post_array ) ) ? filter_var(
				$post_array['advana_server_info_admin_bar_refresh_interval'],
				\FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 100,
					),
				)
			) : 10;

			$advanced_options['browser_notifications_seconds'] = ( array_key_exists( 'browser_notifications_seconds', $post_array ) ) ? filter_var(
				$post_array['browser_notifications_seconds'],
				\FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 100,
					),
				)
			) : 10;
			// Clamp to minimum 5 seconds server-side to avoid rapid polling.
			if ( $advanced_options['browser_notifications_seconds'] < 5 ) {
				$advanced_options['browser_notifications_seconds'] = 5;
			}

			$advanced_options['plugin_version_switch_count'] = ( array_key_exists( 'plugin_version_switch_count', $post_array ) ) ? filter_var(
				$post_array['plugin_version_switch_count'],
				\FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 10,
					),
				)
			) : 3;

			// Modules start.
			$advanced_options['cron_module_enabled']        = ( array_key_exists( 'cron_module_enabled', $post_array ) ) ? filter_var( $post_array['cron_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['file_editor_module_enabled'] = ( array_key_exists( 'file_editor_module_enabled', $post_array ) ) ? filter_var( $post_array['file_editor_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['requests_module_enabled']    = ( array_key_exists( 'requests_module_enabled', $post_array ) ) ? filter_var( $post_array['requests_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['server_info_module_enabled'] = ( array_key_exists( 'server_info_module_enabled', $post_array ) ) ? filter_var( $post_array['server_info_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['fatals_module_enabled']      = ( array_key_exists( 'fatals_module_enabled', $post_array ) ) ? filter_var( $post_array['fatals_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['wp_mail_module_enabled']     = ( array_key_exists( 'wp_mail_module_enabled', $post_array ) ) ? filter_var( $post_array['wp_mail_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['transients_module_enabled']  = ( array_key_exists( 'transients_module_enabled', $post_array ) ) ? filter_var( $post_array['transients_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			$advanced_options['tables_module_enabled']      = ( array_key_exists( 'tables_module_enabled', $post_array ) ) ? filter_var( $post_array['tables_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;
			// Modules end.

			// Snippets module settings.
			$advanced_options['snippets_module_enabled'] = ( array_key_exists( 'snippets_module_enabled', $post_array ) ) ? filter_var( $post_array['snippets_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;

			$advanced_options['snippets_temp_storage'] = ( array_key_exists( 'snippets_temp_storage', $post_array ) && ! empty( $post_array['snippets_temp_storage'] ) ) ? ( in_array( $post_array['snippets_temp_storage'], array( 'uploads', 'php_temp' ), true ) ? $post_array['snippets_temp_storage'] : self::get_option( 'snippets_temp_storage' ) ) : self::get_option( 'snippets_temp_storage' );

			// Hooks Capture module settings.
			$advanced_options['hooks_capture_module_enabled'] = ( array_key_exists( 'hooks_capture_module_enabled', $post_array ) ) ? filter_var( $post_array['hooks_capture_module_enabled'], \FILTER_VALIDATE_BOOLEAN ) : false;

			// Crons.
			$advanced_options['advana_hooks_capture_clear'] = ( array_key_exists( 'advana_hooks_capture_clear', $post_array ) ) ? ( in_array( $post_array['advana_hooks_capture_clear'], array_merge( array( '-1' ), \array_keys( \wp_get_schedules() ) ), true ) ? $post_array['advana_hooks_capture_clear'] : self::get_option( 'advana_hooks_capture_clear' ) ) : self::get_option( 'advana_hooks_capture_clear' );
			// Crons.
			$advanced_options['advana_rest_requests_clear'] = ( array_key_exists( 'advana_rest_requests_clear', $post_array ) ) ? ( in_array( $post_array['advana_rest_requests_clear'], \array_keys( \wp_get_schedules(), true ) ) ? $post_array['advana_rest_requests_clear'] : self::get_option( 'advana_rest_requests_clear' ) ) : self::get_option( 'advana_rest_requests_clear' );

			$advanced_options['advana_mail_logging_clear'] = ( array_key_exists( 'advana_mail_logging_clear', $post_array ) ) ? ( in_array( $post_array['advana_mail_logging_clear'], \array_keys( \wp_get_schedules(), true ) ) ? $post_array['advana_mail_logging_clear'] : self::get_option( 'advana_mail_logging_clear' ) ) : self::get_option( 'advana_mail_logging_clear' );

			$advanced_options['advana_error_log_clear'] = ( array_key_exists( 'advana_error_log_clear', $post_array ) ) ? ( in_array( $post_array['advana_error_log_clear'], \array_keys( \wp_get_schedules(), true ) ) ? $post_array['advana_error_log_clear'] : self::get_option( 'advana_error_log_clear' ) ) : self::get_option( 'advana_error_log_clear' );

			if ( array_key_exists( 'advana_rest_requests_clear', $post_array ) ) {
				if ( -1 === (int) $post_array['advana_rest_requests_clear'] ) {
					Crons_Helper::clear_events( ADVAN_PREFIX . 'request_table_clear' );
					$advanced_options['advana_rest_requests_clear'] = '-1';
				}
			}

			if ( array_key_exists( 'advana_hooks_capture_clear', $post_array ) ) {
				if ( -1 === (int) $post_array['advana_hooks_capture_clear'] ) {
					Crons_Helper::clear_events( ADVAN_PREFIX . 'hooks_capture_clear' );
					$advanced_options['advana_hooks_capture_clear'] = '-1';
				}
			}

			if ( array_key_exists( 'advana_mail_logging_clear', $post_array ) ) {
				if ( -1 === (int) $post_array['advana_mail_logging_clear'] ) {
					Crons_Helper::clear_events( ADVAN_PREFIX . 'mail_logging_clear' );
					$advanced_options['advana_mail_logging_clear'] = '-1';
				}
			}

			if ( array_key_exists( 'advana_error_log_clear', $post_array ) ) {
				if ( -1 === (int) $post_array['advana_error_log_clear'] ) {
					Crons_Helper::clear_events( ADVAN_PREFIX . 'error_log_clear' );
					$advanced_options['advana_error_log_clear'] = '-1';
				}
			}
			// Crons end.

			$advanced_options['show_active_plugins_first'] = ( array_key_exists( 'show_active_plugins_first', $post_array ) ) ? filter_var( $post_array['show_active_plugins_first'], \FILTER_VALIDATE_BOOLEAN ) : false;

			if ( ! $import && ! is_a( Config_Transformer::init(), '\WP_Error' ) ) {

				// Collect wp-config transformer errors during this save flow so we can show them as an admin notice.
				$config_errors = array();

				$wp_debug_enable = ( array_key_exists( 'wp_debug_enable', $post_array ) ) ? filter_var( $post_array['wp_debug_enable'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'WP_DEBUG', $wp_debug_enable, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'WP_DEBUG', $wp_debug_enable ),
						'message' => $e->getMessage(),
					);
				}

				$wp_debug_display_enable = ( array_key_exists( 'wp_debug_display_enable', $post_array ) ) ? filter_var( $post_array['wp_debug_display_enable'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'WP_DEBUG_DISPLAY', $wp_debug_display_enable, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'WP_DEBUG_DISPLAY', $wp_debug_display_enable ),
						'message' => $e->getMessage(),
					);
				}

				$wp_script_debug = ( array_key_exists( 'wp_script_debug', $post_array ) ) ? filter_var( $post_array['wp_script_debug'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'SCRIPT_DEBUG', $wp_script_debug, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'SCRIPT_DEBUG', $wp_script_debug ),
						'message' => $e->getMessage(),
					);
				}

				$wp_save_queries = ( array_key_exists( 'wp_save_queries', $post_array ) ) ? filter_var( $post_array['wp_save_queries'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'SAVEQUERIES', $wp_save_queries, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'SAVEQUERIES', $wp_save_queries ),
						'message' => $e->getMessage(),
					);
				}

				$wp_environment_type = ( array_key_exists( 'wp_environment_type', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['wp_environment_type'] ) ) : false;

				if ( false !== $wp_environment_type && ! in_array( $wp_environment_type, array( 'production', 'development', 'staging', 'local' ), true ) ) {
					$wp_environment_type = 'production';
				}

				try {
					Config_Transformer::update( 'constant', 'WP_ENVIRONMENT_TYPE', $wp_environment_type, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'WP_ENVIRONMENT_TYPE', $wp_environment_type ),
						'message' => $e->getMessage(),
					);
				}

				$wp_development_mode = ( array_key_exists( 'wp_development_mode', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['wp_development_mode'] ) ) : false;

				if ( false !== $wp_development_mode && ! in_array( $wp_development_mode, array( '', 'all', 'core', 'plugin', 'theme' ), true ) ) {
					$wp_development_mode = '';
				}

				try {
					Config_Transformer::update( 'constant', 'WP_DEVELOPMENT_MODE', $wp_development_mode, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'WP_DEVELOPMENT_MODE', $wp_development_mode ),
						'message' => $e->getMessage(),
					);
				}

				$wp_debug_log_enable = ( array_key_exists( 'wp_debug_log_enable', $post_array ) ) ? filter_var( $post_array['wp_debug_log_enable'], FILTER_VALIDATE_BOOLEAN ) : false;

				if ( $wp_debug_log_enable ) {

					@clearstatcache( false, File_Helper::get_wp_config_file_path() );

					$wp_debug_log_generate = ( array_key_exists( 'wp_debug_log_file_generate', $post_array ) ) ? filter_var( $post_array['wp_debug_log_file_generate'], FILTER_VALIDATE_BOOLEAN ) : false;

					$wp_debug_log_filename = ( array_key_exists( 'wp_debug_log_filename', $post_array ) ) ? \sanitize_text_field( $post_array['wp_debug_log_filename'] ) : '';

					if ( ! empty( $wp_debug_log_filename ) && Error_Log::autodetect() !== $wp_debug_log_filename ) {
						$candidate    = \wp_normalize_path( $wp_debug_log_filename );
						$content_root = \wp_normalize_path( \WP_CONTENT_DIR );
						// Allow only paths inside WP_CONTENT_DIR to mitigate arbitrary path writes.
						if ( 0 === strpos( $candidate, $content_root ) && \is_writable( \dirname( $candidate ) ) ) {
							try {
								Config_Transformer::update( 'constant', 'WP_DEBUG_LOG', $candidate, self::$config_args );
							} catch ( \Throwable $e ) {
								$config_errors[] = array(
									'context' => self::build_config_error_context( 'update', 'WP_DEBUG_LOG', $candidate ),
									'message' => $e->getMessage(),
								);
							}
						}
					}

					if ( $wp_debug_log_generate ) {
						$file_name = \WP_CONTENT_DIR . \DIRECTORY_SEPARATOR . 'debug_' . File_Helper::generate_random_file_name() . '.log';

						try {
							Config_Transformer::update( 'constant', 'WP_DEBUG_LOG', $file_name, self::$config_args );
						} catch ( \Throwable $e ) {
							$config_errors[] = array(
								'context' => self::build_config_error_context( 'update', 'WP_DEBUG_LOG', $file_name ),
								'message' => $e->getMessage(),
							);
						}
					}

					// If at this point file is still not set, set to default.
					if ( false === $wp_debug_log_generate && '' === trim( $wp_debug_log_filename ) ) {
						try {
							Config_Transformer::update( 'constant', 'WP_DEBUG_LOG', $wp_debug_log_enable, self::$config_args );
						} catch ( \Throwable $e ) {
							$config_errors[] = array(
								'context' => self::build_config_error_context( 'update', 'WP_DEBUG_LOG', $wp_debug_log_enable ),
								'message' => $e->getMessage(),
							);
						}
					}

					// Clear the flag for keep reading the error log if WP settings are disabled (because at this point they are enabled).
					$advanced_options['keep_reading_error_log'] = false;
					$advanced_options['plugin_debug_enable']    = false;
				} else {

					try {
						Config_Transformer::update( 'constant', 'WP_DEBUG_LOG', $wp_debug_log_enable, self::$config_args );
					} catch ( \Throwable $e ) {
						$config_errors[] = array(
							'context' => self::build_config_error_context( 'update', 'WP_DEBUG_LOG', $wp_debug_log_enable ),
							'message' => $e->getMessage(),
						);
					}
				}

				$wp_cron_disable = ( array_key_exists( 'wp_cron_disable', $post_array ) ) ? filter_var( $post_array['wp_cron_disable'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'DISABLE_WP_CRON', $wp_cron_disable, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'DISABLE_WP_CRON', $wp_cron_disable ),
						'message' => $e->getMessage(),
					);
				}

				$block_external_requests = ( array_key_exists( 'block_external_requests', $post_array ) ) ? filter_var( $post_array['block_external_requests'], FILTER_VALIDATE_BOOLEAN ) : false;

				try {
					Config_Transformer::update( 'constant', 'WP_HTTP_BLOCK_EXTERNAL', $block_external_requests, self::$config_args );
				} catch ( \Throwable $e ) {
					$config_errors[] = array(
						'context' => self::build_config_error_context( 'update', 'WP_HTTP_BLOCK_EXTERNAL', $block_external_requests ),
						'message' => $e->getMessage(),
					);
				}

				@clearstatcache( false, File_Helper::get_wp_config_file_path() );

				if ( ! empty( $config_errors ) ) {
					// Persist messages across the redirect so admin_notices can display them.
					try {
						\update_option( 'advan_config_transformer_errors', $config_errors );
					} catch ( \Throwable $e ) {
						// Ignore failures updating the option.
					}
				}
			}

			// Before returning (WordPress will persist), encrypt sensitive fields.
			$to_store = $advanced_options;
			Secure_Store::encrypt_sensitive_fields( $to_store );
			self::$current_options = $advanced_options; // Keep plaintext in-memory.

			return $to_store;
		}

		/**
		 * Returns the disabled severities levels and stores them in the internal class cache.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function get_disabled_severities(): array {
			if ( null === self::$disabled_severities ) {
				self::$disabled_severities = array();
				foreach ( self::get_option( 'severities' ) as $name => $severity ) {
					if ( ! $severity['display'] ) {
						self::$disabled_severities[] = $name;
					}
				}
			}

			return self::$disabled_severities;
		}

		/**
		 * Returns the enabled severities levels and stores them in the internal class cache.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function get_enabled_severities(): array {
			if ( null === self::$enabled_severities ) {
				self::$enabled_severities = array();
				foreach ( self::get_option( 'severities' ) as $name => $severity ) {
					if ( $severity['display'] ) {
						self::$enabled_severities[] = $name;
					}
				}
			}

			return self::$enabled_severities;
		}

		/**
		 * Mask sensitive fields in an associative array using dot-notated paths.
		 *
		 * Example path: "slack_notifications.all.auth_token"
		 *
		 * @param array $data Data to process (modified in place).
		 *
		 * @return void
		 *
		 * @since 3.3.2
		 */
		private static function mask_sensitive_fields( array &$data ): void {
			if ( empty( $data ) || ! is_array( $data ) ) {
				return;
			}

			foreach ( self::SENSITIVE_EXPORT_PATHS as $path ) {
				if ( ! is_string( $path ) || '' === $path ) {
					continue;
				}
				$segments = explode( '.', $path );
				$ref      =& $data;
				$found    = true;
				foreach ( $segments as $seg ) {
					if ( is_array( $ref ) && array_key_exists( $seg, $ref ) ) {
						$ref =& $ref[ $seg ];
					} else {
						$found = false;
						break;
					}
				}
				if ( $found ) {
					$ref = '***';
				}
			}
		}

		/**
		 * Modifies the admin footer text.
		 *
		 * @param   string $text The current admin footer text.
		 * @return  string
		 *
		 * @since 1.7.5
		 */
		public static function admin_footer_text( $text ) {

			if ( WP_Helper::get_wp_screen() && ( in_array( WP_Helper::get_wp_screen()->base, Miscellaneous::get_plugin_page_slugs(), true ) ) ) {

				$link        = 'https://github.com/sdobreff';
				$footer_link = 'https://wordpress.org/plugins/0-day-analytics/';

				return \sprintf(
				/* translators: This text is prepended by a link to plugin WP store. */
					'<a href="%1$s" target="_blank">' . ADVAN_NAME . '</a> ' . __( 'is developed and maintained by', '0-day-analytics' ) . ' <a href="%2$s" target="_blank">Stoil Dobreff</a>.',
					$footer_link,
					$link
				) . '<br>' . sprintf(
				/* translators: 1: Plugin Name, 3: Plugin review URL */
					__( 'If you like <strong><ins>%1$s</ins></strong>. please leave us a <a target="_blank" style="color:#f9b918" href="%2$s">★★★★★</a> rating. A huge thank you in advance!', '0-day-analytics' ),
					\esc_attr( ADVAN_NAME ),
					\esc_url_raw( 'https://wordpress.org/support/view/plugin-reviews/0-day-analytics?filter=5' ),
				);
			}

			return $text;
		}

		/**
		 * Show stored Config_Transformer errors as an admin notice.

		/**
		 * Build a short context string for a wp-config operation.
		 *
		 * @param string $action Action performed (e.g. 'update').
		 * @param string $name   Config name (constant or variable).
		 * @param mixed  $value  Optional value attempted to set.
		 *
		 * @return string
		 */
		protected static function build_config_error_context( $action, $name, $value = null ) {
			$ctx = ucfirst( (string) $action ) . ' ' . (string) $name;
			if ( null !== $value ) {
				if ( is_bool( $value ) ) {
					$val = $value ? 'true' : 'false';
				} elseif ( is_scalar( $value ) ) {
					$val = (string) $value;
				} else {
					$val = json_encode( $value );
				}
				$ctx .= ' (value: ' . $val . ')';
			}
			return $ctx;
		}

		/**
		 *
		 * Reads persisted errors from the options table, displays them, and
		 * then removes the stored errors so the notice is shown only once.
		 *
		 * @return void
		 *
		 * @since 1.2.0
		 */
		public static function show_config_transformer_errors() {
			if ( ! \is_admin() ) {
				return;
			}

			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}

			$errors = \get_option( 'advan_config_transformer_errors', array() );
			if ( empty( $errors ) || ! is_array( $errors ) ) {
				return;
			}

			// Remove stored option so notice is not shown again.
			\delete_option( 'advan_config_transformer_errors' );

			echo '<div class="notice notice-error is-dismissible"><p><strong>' . \esc_html__( 'wp-config.php update warnings', '0-day-analytics' ) . '</strong></p><ul>';
			foreach ( $errors as $err ) {
				if ( is_array( $err ) ) {
					$context = isset( $err['context'] ) ? $err['context'] : '';
					$message = isset( $err['message'] ) ? $err['message'] : '';
					echo '<li><strong>' . \esc_html( (string) $context ) . ':</strong> ' . \esc_html( (string) $message ) . '</li>';
				} else {
					echo '<li>' . \esc_html( (string) $err ) . '</li>';
				}
			}
			echo '</ul></div>';
		}

		/**
		 * Sets severity as enabled
		 *
		 * @param string $severity - The name of the severity to enable.
		 *
		 * @return void
		 *
		 * @since 1.9.5.1
		 */
		public static function enable_severity( string $severity ): void {
			if ( ! isset( self::$current_options['severities'][ $severity ] ) ) {
				return;
			}

			self::$current_options['severities'][ $severity ]['display'] = true;

			self::store_options( self::$current_options );
		}

		/**
		 * Sets severity as disabled
		 *
		 * @param string $severity - The name of the severity to disable.
		 *
		 * @return void
		 *
		 * @since 1.9.5.1
		 */
		public static function disable_severity( string $severity ): void {
			if ( ! isset( self::$current_options['severities'][ $severity ] ) ) {
				return;
			}

			self::$current_options['severities'][ $severity ]['display'] = false;

			self::store_options( self::$current_options );
		}

		/**
		 * Triggers JS to show the new errors count in the menu (if found)
		 *
		 * @return void
		 *
		 * @since 3.3.1
		 */
		public static function show_error_count() {

			if ( \is_admin() && ! \wp_doing_ajax() ) {
				if ( 1 <= ( $count = Log_Line_Parser::get_lines_to_show_interface() ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
					?>
						<script>
							if (jQuery('#advan-errors-menu .update-count').length) {
								jQuery('#advan-errors-menu').show();
								jQuery('#advan-errors-menu .update-count').html('<?php echo \esc_attr( \number_format_i18n( $count ) ); ?>');
							}
						</script>
						<?php
				}
			}
		}
	}
}
