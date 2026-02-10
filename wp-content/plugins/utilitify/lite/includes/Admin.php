<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Utilitify
 * @subpackage Utilitify/admin
 */

namespace Kaizencoders\Utilitify;

use Kaizencoders\Utilitify\Admin\Request_404_Table;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Utilitify
 * @subpackage Utilitify/admin
 * @author     Your Name <email@example.com>
 */
class Admin {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin $plugin This plugin's instance.
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param Plugin $plugin This plugin's instance.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Utilitify_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Utilitify_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( Helper::is_plugin_admin_screen() ) {

			\wp_enqueue_style(
				'utilitify-main',
				\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/styles/app.css',
				array(),
				$this->plugin->get_version(),
				'all' );


			\wp_enqueue_style(
				$this->plugin->get_plugin_name() . '-admin',
				\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/styles/utilitify-admin.css',
				array(),
				$this->plugin->get_version(),
				'all' );
		}

		\wp_enqueue_style(
			'utilitify',
			\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/styles/utilitify.css',
			array(),
			$this->plugin->get_version(),
			'all' );


	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Utilitify_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Utilitify_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( Helper::is_plugin_admin_screen() ) {

			\wp_enqueue_script(
				'uf-app',
				\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/scripts/app.js',
				array( 'jquery' ),
				$this->plugin->get_version(),
				true );


			\wp_enqueue_script(
				$this->plugin->get_plugin_name() . '-admin',
				\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/scripts/utilitify-admin.js',
				array( 'jquery' ),
				$this->plugin->get_version(),
				false );
		}
	}

	/**
	 * Add admin menu
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {

		add_menu_page( __( 'Utilitify', 'utilitify' ), __( 'Utilitify', 'utilitify' ), 'manage_options', 'utilitify', array( $this, 'render_dashboard' ), 'dashicons-hammer', 30 );

		$hook = add_submenu_page( 'utilitify', __( '404 Requests', 'utilitify' ), __( '404 Requests', 'utilitify' ), 'manage_options', '404-requests', array( $this, 'render_404_requests' ) );

		new \Kaizencoders\Utilitify\Admin\Settings();
	}

	public function render_dashboard() {
		include_once KC_UF_ADMIN_TEMPLATES_DIR . '/dashboard.php';
	}

	public function render_404_requests() {
		$requests_table = new Request_404_Table();
		$requests_table->render();
	}

	/**
	 * Remove all unwanted admin notices from others
	 *
	 * @since 1.0.0
	 */
	public function remove_admin_notices() {
		global $wp_filter;

		if ( ! Helper::is_plugin_admin_screen() ) {
			return;
		}

		$get_page = Helper::get_request_data( 'page' );

		if ( ! empty( $get_page ) && 'url_shortify' == $get_page ) {
			remove_all_actions( 'admin_notices' );
		} else {

			$allow_display_notices = array(
				'show_review_notice',
				'kc_uf_fail_php_version_notice',
				'kc_uf_show_admin_notice',
				'show_custom_notices',
				'_admin_notices_hook',
			);

			$filters = array(
				'admin_notices',
				'user_admin_notices',
				'all_admin_notices',
			);

			foreach ( $filters as $filter ) {

				if ( ! empty( $wp_filter[ $filter ]->callbacks ) && is_array( $wp_filter[ $filter ]->callbacks ) ) {

					foreach ( $wp_filter[ $filter ]->callbacks as $priority => $callbacks ) {

						foreach ( $callbacks as $name => $details ) {

							if ( is_object( $details['function'] ) && $details['function'] instanceof \Closure ) {
								unset( $wp_filter[ $filter ]->callbacks[ $priority ][ $name ] );
								continue;
							}

							if ( ! empty( $details['function'][0] ) && is_object( $details['function'][0] ) && count( $details['function'] ) == 2 ) {
								$notice_callback_name = $details['function'][1];
								if ( ! in_array( $notice_callback_name, $allow_display_notices ) ) {
									unset( $wp_filter[ $filter ]->callbacks[ $priority ][ $name ] );
								}
							}

							if ( ! empty( $details['function'] ) && is_string( $details['function'] ) ) {
								if ( ! in_array( $details['function'], $allow_display_notices ) ) {
									unset( $wp_filter[ $filter ]->callbacks[ $priority ][ $name ] );
								}
							}
						}
					}
				}

			}
		}

	}

	/**
	 * Redirect after activation
	 *
	 * @since 1.0.0
	 *
	 */
	public function redirect_to_dashboard() {

		// Check if it is multisite and the current user is in the network administrative interface. e.g. `/wp-admin/network/`
		if ( is_multisite() && is_network_admin() ) {
			return;
		}

		if ( get_option( 'utilitify_do_activation_redirect', false ) ) {
			delete_option( 'utilitify_do_activation_redirect' );
			wp_redirect( 'admin.php?page=utilitify' );
		}
	}

	/**
	 * Dismiss Admin Notices
	 *
	 * @since 1.2.11
	 */
	public function dismiss_admin_notice() {
		if ( isset( $_GET['kc_uf_dismiss_admin_notice'] ) && $_GET['kc_uf_dismiss_admin_notice'] == '1' && isset( $_GET['option_name'] ) ) {

			$option_name = sanitize_text_field( $_GET['option_name'] );

			update_option( 'kc_uf_' . $option_name . '_dismissed', 'yes', false );

			if ( $option_name === 'offer_halloween_2020' ) {
				exit();
			} else {
				$referer = wp_get_referer();
				wp_safe_redirect( $referer );
				exit();
			}
		}
	}

	public function kc_uf_show_admin_notice() {

		$notice = Cache::get_transient( 'notice' );

		if ( ! empty( $notice ) ) {

			$status = Helper::get_data( $notice, 'status', '' );

			if ( ! empty( $status ) ) {
				$message       = Helper::get_data( $notice, 'message', '' );
				$is_dismisible = Helper::get_data( $notice, 'is_dismisible', true );

				switch ( $status ) {
					case 'success':
						KC_UF()->notices->success( $message, $is_dismisible );
						break;
					case 'error':
						KC_UF()->notices->error( $message, $is_dismisible );
						break;
					case 'warning':
						KC_UF()->notices->warning( $message, $is_dismisible );
						break;
					case 'info':
					default;
						KC_UF()->notices->info( $message, $is_dismisible );
						break;

				}

				Cache::delete_transient( 'notice' );
			}
		}
	}

	/**
	 * Show Custom notice/ offers/ promotions
	 *
	 * @since 1.0.0
	 */
	public function show_custom_notices() {


	}

	/**
	 * Hide toolbar from frontend
	 *
	 * @return false
	 *
	 */
	public function hide_admin_bar() {

		$settings = KC_UF()->get_settings( 'kc_uf' );

		$enable = Helper::get_data( $settings, 'general_options_enable_hide_admin_bar_from_frontend', 0 );

		if ( 1 == $enable ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Hide tooldbar from backend
	 */
	public function hide_admin_bar_from_admin() {

		$settings = KC_UF()->get_settings();

		$enable = Helper::get_data( $settings, 'general_options_enable_hide_admin_bar_from_backend', 0 );

		if ( 1 == $enable ) { ?>
            <style type="text/css">
                #wpadminbar {
                    display: none;
                }
            </style>
		<?php }
	}

	/**
	 * Update admin footer text
	 *
	 * @param $footer_text
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function update_admin_footer_text( $footer_text ) {

		// Update Footer admin only on ES pages
		if ( Helper::is_plugin_admin_screen() ) {

			$wordpress_url = 'https://www.wordpress.org';
			$website_url   = 'https://www.kaizencoders.com';

			$footer_text = sprintf( __( '<span id="footer-thankyou">Thank you for creating with <a href="%1$s" target="_blank">WordPress</a> | Utilitify <b>%2$s</b>. Developed by team <a href="%3$s" target="_blank">KaizenCoders</a></span>', 'url-shortify' ), $wordpress_url, KC_UF_PLUGIN_VERSION, $website_url );
		}

		return $footer_text;
	}

	/**
	 * Add 404 header code in .htaccess file
	 *
	 * @since 1.0.1
	 */
	public function add_404_header_code() {

		$file = get_home_path() . "/.htaccess";

		$content = "FRedirect_ErrorDocument " . Helper::get_site_404_page_path();

		$marker_name = "FRedirect_ErrorDocument";

		if ( file_exists( $file ) ) {
			$f = @fopen( $file, 'r+' );

			$file_str = @fread( $f, filesize( $file ) );
			if ( strpos( $file_str, $marker_name ) === false ) {
				insert_with_markers( $file, $marker_name, $content );
			}
		} else {
			insert_with_markers( $file, $marker_name, $content );
		}
	}

	/**
	 * Update plugin notice
	 *
	 * @param $data
	 * @param $response
	 *
	 * @since 1.0.3
	 */
	public function in_plugin_update_message( $data, $response ) {

		if ( isset( $data['upgrade_notice'] ) ) {
			printf(
				'<div class="update-message">%s</div>',
				wpautop( $data['upgrade_notice'] )
			);
		}
	}

	/**
     * Disable Auto Update?
     *
	 * @param $disabled
	 *
	 * @return mixed|true
     *
     * @since 1.0.12
	 */
	public function disabled_auto_update_core( $disabled ) {
		$settings = KC_UF()->get_settings();

		$disable = Helper::get_data( $settings, 'general_wordpress_auto_updates_disable_core_auto_updates', false );

		if ( $disable ) {
			return true;
		}

		return $disabled;
	}

}
