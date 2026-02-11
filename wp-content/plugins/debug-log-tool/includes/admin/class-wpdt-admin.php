<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Admin' ) ) :

	final class WPDT_Admin {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
			add_action( 'admin_menu', array( __CLASS__, 'load_admin_menus' ), 10 );
		}

		/**
		 * Load JS and CSS scripts
		 *
		 * @return void
		 */
		public static function load_scripts() {

			if ( ! self::is_debug_log_tool_page() ) {
				return;
			}

			// jquery.
			wp_enqueue_script( 'jquery' );

			// WPSC Scripts.
			wp_enqueue_script( 'wpdt-admin', WPDT_PLUGIN_URL . 'asset/js/admin.js', array( 'jquery' ), WPDT_VERSION, true );
			wp_localize_script( 'wpdt-admin', 'wpdebugtool', self::get_localization_data() );

			if ( is_rtl() ) {
				wp_enqueue_style( 'wpdt-admin', WPDT_PLUGIN_URL . 'asset/css/admin-rtl.css', array(), WPDT_VERSION );
			} else {
				wp_enqueue_style( 'wpdt-admin', WPDT_PLUGIN_URL . 'asset/css/admin.css', array(), WPDT_VERSION );
			}

			// DataTables.
			wp_enqueue_script( 'wpdt-datatables', WPDT_PLUGIN_URL . 'asset/lib/DataTables/datatables.min.js', array( 'jquery' ), WPDT_VERSION, true );
			wp_enqueue_style( 'wpdt-datatables', WPDT_PLUGIN_URL . 'asset/lib/DataTables/datatables.min.css', array(), WPDT_VERSION );
		}

		/**
		 * Load admin/dashboard menus
		 *
		 * @return void
		 */
		public static function load_admin_menus() {

			$data_uri = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( WPDT_ABSPATH . 'asset/images/icon.svg' ) ); // phpcs:ignore

			add_menu_page(
				esc_attr__( 'BugTrace', 'debug-log-tool' ),
				esc_attr__( 'BugTrace', 'debug-log-tool' ),
				'manage_options',
				'wpdt-debugging',
				array( 'WPDT_Logs', 'layout' ),
				$data_uri,
				25
			);

			add_submenu_page(
				'wpdt-debugging',
				esc_attr__( 'Logs', 'debug-log-tool' ),
				esc_attr__( 'Logs', 'debug-log-tool' ),
				'manage_options',
				'wpdt-debugging',
				array( 'WPDT_Logs', 'layout' )
			);

			add_submenu_page(
				'wpdt-debugging',
				esc_attr__( 'Server Info', 'debug-log-tool' ),
				esc_attr__( 'Server Info', 'debug-log-tool' ),
				'manage_options',
				'wpdt-server-info',
				array( 'WPDT_Server_Info', 'layout' )
			);

			add_submenu_page(
				'wpdt-debugging',
				esc_attr__( 'Settings', 'debug-log-tool' ),
				esc_attr__( 'Settings', 'debug-log-tool' ),
				'manage_options',
				'wpdt-settings',
				array( 'WPDT_Settings', 'layout' )
			);
		}

		/**
		 * Get localization data for the admin scripts
		 *
		 * @return string
		 */
		private static function get_localization_data() {

			$localizations = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'plugin_url' => WPDT_PLUGIN_URL,
				'version'    => WPDT_VERSION,
				'nonce'      => wp_create_nonce( 'wpdt_nonce' ),
				'home_url'   => home_url(),
			);

			return apply_filters( 'wpsc_admin_localizations', $localizations );
		}

		/**
		 * Load setting header HTML
		 *
		 * @return void
		 */
		public static function load_setting_header_html() {
			?>
			<div class="wpdt-header">
				<div class="wpdt-header-title">
					<img src="<?php echo esc_url( WPDT_PLUGIN_URL . 'asset/images/logo-header.png' ); // phpcs:ignore ?>" class="wpdt-header-icon" alt="DebugTool Icon">
					<h2>BugTrace</h2>
				</div>
				<div class="wpdt-header-buttons">
					<!-- <a href="#" class="wpdt-button">Documentation</a> -->
					<a href="https://wordpress.org/support/plugin/debug-log-tool/" target="__blank" class="wpdt-button">Support</a>
					<a href="https://wordpress.org/support/plugin/debug-log-tool/reviews/#new-post" target="__blank" class="wpdt-button">Add a Review</a>
				</div>
			</div>
			<?php
		}

		/**
		 * Check if the current page is a BugTrace - Debug Log Tool page
		 *
		 * @return bool
		 */
		public static function is_debug_log_tool_page() {

			if ( is_admin() ) {
				$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore
				return ( ! empty( $page ) && preg_match( '/^wpdt-/', $page ) ) ? true : false;
			} else {
				return true;
			}
		}
	}

endif;

WPDT_Admin::init();
