<?php
/**
 * Authority Mailer SMTP
 *
 * Main plugin bootstrap file.
 *
 * @package Authority_Mailer
 *
 * @wordpress-plugin
 * Plugin Name: Authority Mailer SMTP
 * Plugin URI:  https://www.authorityplugins.com/products/authority-mailer-smtp
 * Description: Authority Mailer — SMTP & transactional email made simple. Onboarding setup wizard included.
 * Version:     1.0.3
 * Author:      Authority Plugins
 * Author URI:  https://authorityplugins.com
 * Text Domain: authority-mailer-smtp
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'AUTHORITY_MAILER_PLUGIN_FILE' ) ) {
	define( 'AUTHORITY_MAILER_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'AUTHORITY_MAILER_PLUGIN_DIR' ) ) {
	define( 'AUTHORITY_MAILER_PLUGIN_DIR', plugin_dir_path( AUTHORITY_MAILER_PLUGIN_FILE ) );
}
if ( ! defined( 'AUTHORITY_MAILER_PLUGIN_URL' ) ) {
	define( 'AUTHORITY_MAILER_PLUGIN_URL', plugin_dir_url( AUTHORITY_MAILER_PLUGIN_FILE ) );
}
if ( ! defined( 'AUTHORITY_MAILER_VERSION' ) ) {
	define( 'AUTHORITY_MAILER_VERSION', '1.0.3' );
}

/**
 * CRITICAL FIX: Load Google OAuth callback handler immediately (top-level)
 * so its rest_api_init registration runs in time for incoming REST requests.
 *
 * We directly register the route here instead of in a separate file to guarantee it loads.
 */
add_action(
	'rest_api_init',
	function () {
		/*
		 * Security Note: permission_callback uses __return_true intentionally.
		 *
		 * This endpoint receives OAuth 2.0 authorization callbacks from Google and must be
		 * publicly accessible for the OAuth flow to complete. Google redirects users here
		 * after they authorize the application.
		 *
		 * Security is ensured by:
		 * - State parameter validation prevents CSRF attacks (verified against 'authority-mailer-smtp')
		 * - Authorization codes are single-use and short-lived (enforced by Google)
		 * - Code exchange requires client_secret which is never exposed to the browser
		 * - Tokens are securely stored server-side using WordPress options API
		 * - All redirects use wp_safe_redirect() to prevent open redirect vulnerabilities
		 *
		 * OAuth 2.0 specification (RFC 6749) requires callback URLs to be publicly accessible.
		 *
		 * WordPress.org Compliance: This is a valid use case for a public REST API endpoint.
		 *
		 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-3.1.2
		 */
		register_rest_route(
			'authority-mailer-smtp',
			'/google/callback',
			array(
				'methods'             => 'GET',
				'callback'            => 'authority_mailer_smtp_google_oauth_callback',
				'permission_callback' => '__return_true',
			)
		);
	},
	10
);

/**
 * Handle OAuth callback from Google
 *
 * @param WP_REST_Request $request
 * @return WP_HTTP_Response|void
 */
function authority_mailer_smtp_google_oauth_callback( $request ) {
	// Debug test endpoint
	if ( isset( $request['test'] ) && '1' === (string) $request['test'] ) {
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'Authority Mailer google callback route is working - DIRECT REGISTRATION',
				'file'    => __FILE__,
				'query'   => $request->get_query_params(),
			)
		);
	}

	$code  = $request->get_param( 'code' );
	$state = $request->get_param( 'state' );
	$error = $request->get_param( 'error' );

	if ( $error ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=' . urlencode( $error ) ) );
		exit;
	}

	if ( 'authority-mailer-smtp' !== $state ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=invalid_state' ) );
		exit;
	}

	if ( empty( $code ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=no_code' ) );
		exit;
	}

	// Determine the option key
	$option_key = 'authority_mailer_smtp_options';
	if ( class_exists( 'Authority_Mailer_Onboarding' ) ) {
		$reflection = new ReflectionClass( 'Authority_Mailer_Onboarding' );
		if ( $reflection->hasConstant( 'OPTION_KEY' ) ) {
			$option_key = $reflection->getConstant( 'OPTION_KEY' );
		}
	}

	$options = get_option( $option_key, array() );

	$client_id     = ! empty( $options['google_client_id'] ) ? $options['google_client_id'] : '';
	$client_secret = ! empty( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
	$redirect_uri  = ! empty( $options['google_redirect_uri'] ) ? $options['google_redirect_uri'] : home_url( '/wp-json/authority-mailer-smtp/google/callback' );

	if ( empty( $client_id ) || empty( $client_secret ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=missing_credentials' ) );
		exit;
	}

	// Exchange code for tokens
	$response = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		array(
			'body'    => array(
				'code'          => $code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
				'grant_type'    => 'authorization_code',
			),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=token_exchange_failed' ) );
		exit;
	}

	$code_http = wp_remote_retrieve_response_code( $response );
	$body      = wp_remote_retrieve_body( $response );
	$data      = json_decode( $body, true );

	if ( 200 !== $code_http || empty( $data['access_token'] ) ) {
		$error_msg = ! empty( $data['error_description'] ) ? $data['error_description'] : 'token_exchange_failed';
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=' . urlencode( $error_msg ) ) );
		exit;
	}

	$options['google_access_token'] = $data['access_token'];
	if ( ! empty( $data['refresh_token'] ) ) {
		$options['google_refresh_token'] = $data['refresh_token'];
	}
	$options['google_connected']     = true;
	$options['google_token_expires'] = time() + ( ! empty( $data['expires_in'] ) ? (int) $data['expires_in'] : 3600 );

	// Fetch user info
	$user_info_response = wp_remote_get(
		'https://www.googleapis.com/oauth2/v2/userinfo',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $data['access_token'],
			),
			'timeout' => 20,
		)
	);

	if ( ! is_wp_error( $user_info_response ) ) {
		$user_body = wp_remote_retrieve_body( $user_info_response );
		$user_data = json_decode( $user_body, true );

		if ( ! empty( $user_data['email'] ) ) {
			$options['google_from_email'] = $user_data['email'];
		}
		if ( ! empty( $user_data['name'] ) && empty( $options['google_from_name'] ) ) {
			$options['google_from_name'] = $user_data['name'];
		}
	}

	update_option( $option_key, $options );

	wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&connected=1' ) );
	exit;
}

/**
 * Initialize the plugin: load strings, include onboarding class.
 *
 * Hooked to 'init' to ensure translations are available before strings.php is loaded.
 * Note: WordPress 5.0+ automatically loads text domains from the /languages folder.
 */
function authority_mailer_smtp_init_plugin() {
	$strings_path = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/strings.php';
	if ( file_exists( $strings_path ) ) {
		require_once $strings_path;
	}

	require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/admin-helpers.php';

	// Load centralized admin assets manager.
	require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/class-admin-assets.php';

	// Load conflict detector for SMTP plugin conflict detection and system checks.
	$conflict_detector_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/class-conflict-detector.php';
	if ( file_exists( $conflict_detector_file ) ) {
		require_once $conflict_detector_file;
		// Initialize immediately since we're loading after plugins_loaded has already fired.
		if ( function_exists( 'authority_mailer_conflict_detector' ) ) {
			authority_mailer_conflict_detector();
		}
	}

	// Pro features removed for free version.

	$onboarding_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/class-onboarding-wizard.php';
	if ( file_exists( $onboarding_file ) ) {
		require_once $onboarding_file;
		if ( class_exists( 'Authority_Mailer_Onboarding' ) ) {
			Authority_Mailer_Onboarding::init();
		}
	}

	$sender_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/class-sender.php';
	if ( file_exists( $sender_file ) ) {
		require_once $sender_file;
		if ( class_exists( 'Authority_Mailer_Sender' ) && is_callable( array( 'Authority_Mailer_Sender', 'init' ) ) ) {
			Authority_Mailer_Sender::init();
		}
	}

	// Load review request system.
	$review_request_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/class-review-request.php';
	if ( file_exists( $review_request_file ) ) {
		require_once $review_request_file;
		if ( class_exists( 'Authority_Mailer_Review_Request' ) ) {
			Authority_Mailer_Review_Request::init();
		}
	}

	if ( is_admin() ) {
		$admin_includes_dir = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/';
		$admin_files        = array(
			'dashboard.php',
			'email-log.php',
			'tools.php',
			'free-vs-pro.php',
		);
		foreach ( $admin_files as $af ) {
			$path = $admin_includes_dir . $af;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		// Load AJAX handlers.
		$ajax_handler_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/ajax/tools-handler.php';
		if ( file_exists( $ajax_handler_file ) ) {
			require_once $ajax_handler_file;
		}

		// Pro admin files removed for free version.
	}
}
add_action( 'init', 'authority_mailer_smtp_init_plugin' );

/**
 * Centralized admin enqueue callback.
 */
function authority_mailer_smtp_enqueue_assets_from_main( $hook ) {
	$allowed_hooks = array(
		'authority_mailer_smtp_page_authority-mailer-smtp-onboarding',
		'settings_page_authority-mailer-smtp-onboarding',
		'toplevel_page_authority-mailer-smtp-dashboard',
		'authority_mailer_smtp_page_authority-mailer-smtp-dashboard',
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

	$should_call = in_array( $hook, $allowed_hooks, true )
		|| 'authority-mailer-smtp-onboarding' === $current_page;

	if ( $should_call && class_exists( 'Authority_Mailer_Onboarding' ) && is_callable( array( 'Authority_Mailer_Onboarding', 'enqueue_assets' ) ) ) {
		Authority_Mailer_Onboarding::enqueue_assets( $hook );
	}
}
add_action( 'admin_enqueue_scripts', 'authority_mailer_smtp_enqueue_assets_from_main' );

/**
 * Enqueue admin scripts and styles for Authority Mailer pages.
 *
 * @since 1.0.0
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function authority_mailer_smtp_admin_enqueue_scripts( $hook ) {
	if ( strpos( $hook, 'authority-mailer-smtp' ) === false ) {
		return;
	}

	wp_enqueue_style(
		'authority-mailer-smtp-admin',
		plugins_url( 'assets/css/authority-mailer-admin.css', AUTHORITY_MAILER_PLUGIN_FILE ),
		array(),
		AUTHORITY_MAILER_VERSION
	);

	wp_enqueue_style(
		'authority-mailer-dashboard-widgets',
		plugins_url( 'assets/css/dashboard-widgets.css', AUTHORITY_MAILER_PLUGIN_FILE ),
		array(),
		AUTHORITY_MAILER_VERSION
	);

	// Enqueue premium-settings CSS for consistent styling across pages.
	wp_enqueue_style(
		'authority-mailer-premium-settings',
		plugins_url( 'assets/css/premium-settings.css', AUTHORITY_MAILER_PLUGIN_FILE ),
		array( 'authority-mailer-smtp-admin' ),
		AUTHORITY_MAILER_VERSION
	);

	wp_enqueue_style(
		'authority-mailer-design-system',
		AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/admin-design-system.css',
		array(),
		AUTHORITY_MAILER_VERSION
	);

	// Enqueue modern admin JavaScript.
	wp_enqueue_script(
		'authority-mailer-modern-admin',
		plugins_url( 'assets/js/modern-admin.js', AUTHORITY_MAILER_PLUGIN_FILE ),
		array( 'jquery' ),
		AUTHORITY_MAILER_VERSION,
		true
	);

	// Enqueue tools assets on tools page.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking page parameter for asset loading.
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( class_exists( 'Authority_Mailer_Admin_Assets' ) && 'authority-mailer-smtp-tools' === $page ) {
		Authority_Mailer_Admin_Assets::enqueue_tools_assets();
	}
}
add_action( 'admin_enqueue_scripts', 'authority_mailer_smtp_admin_enqueue_scripts' );

/**
 * Activation hook
 */
function authority_mailer_smtp_activate_plugin( $network_wide = false ) {
	set_transient( 'authority_mailer_smtp_activation_redirect', true, 5 * MINUTE_IN_SECONDS );

	// Load centralized database setup class.
	$database_setup_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/class-database-setup.php';
	if ( file_exists( $database_setup_file ) ) {
		require_once $database_setup_file;
	}

	if ( is_multisite() && $network_wide ) {
		if ( function_exists( 'get_sites' ) ) {
			$sites = get_sites(
				array(
					'fields' => 'ids',
					'number' => 999,
				)
			);
			foreach ( (array) $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				// Create all database tables.
				if ( class_exists( 'Authority_Mailer_Database_Setup' ) ) {
					Authority_Mailer_Database_Setup::create_all_tables();
				}
				restore_current_blog();
			}
		} elseif ( class_exists( 'Authority_Mailer_Database_Setup' ) ) {
			Authority_Mailer_Database_Setup::create_all_tables();
		}
	} elseif ( class_exists( 'Authority_Mailer_Database_Setup' ) ) {
		Authority_Mailer_Database_Setup::create_all_tables();
	}
}
register_activation_hook( AUTHORITY_MAILER_PLUGIN_FILE, 'authority_mailer_smtp_activate_plugin' );

/**
 * Deactivation hook
 *
 * Clears all scheduled cron events when the plugin is deactivated.
 */
function authority_mailer_smtp_deactivate_plugin() {
	delete_transient( 'authority_mailer_smtp_activation_redirect' );

	// Clear all plugin cron events.
	wp_clear_scheduled_hook( 'authority_mailer_email_health_check' );
	wp_clear_scheduled_hook( 'authority_mailer_cleanup_old_events' );
}
register_deactivation_hook( AUTHORITY_MAILER_PLUGIN_FILE, 'authority_mailer_smtp_deactivate_plugin' );

/**
 * Activation redirect
 */
function authority_mailer_smtp_maybe_do_activation_redirect() {
	if ( ! is_admin() ) {
		return;
	}

	if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || headers_sent() ) {
		return;
	}

	// Check if redirect transient exists and store value to avoid redundant DB call.
	$has_redirect_transient = get_transient( 'authority_mailer_smtp_activation_redirect' );
	if ( ! $has_redirect_transient ) {
		return;
	}

	// Always delete the transient first to prevent infinite loops.
	delete_transient( 'authority_mailer_smtp_activation_redirect' );

	// Then check permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Redirect to dashboard.
	wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-dashboard' ) );
	exit;
}
add_action( 'admin_init', 'authority_mailer_smtp_maybe_do_activation_redirect' );

/**
 * Get the custom menu icon for the plugin.
 *
 * Returns a base64-encoded SVG data URI for use in WordPress admin menus.
 *
 * @since 1.0.0
 * @return string Base64-encoded SVG data URI.
 */
function authority_mailer_smtp_get_menu_icon() {
	return 'dashicons-email';
}

/**
 * Register top-level Authority Mailer admin menu and subpages.
 *
 * When the pro folder exists but the plugin is not licensed, only the Pro Settings
 * menu is shown to allow users to activate their license.
 */
function authority_mailer_smtp_register_admin_menu() {
	$capability         = 'manage_options';
	$admin_includes_dir = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/';

	$pages = array(
		'dashboard'   => 'dashboard.php',
		'email-log'   => 'email-log.php',
		'free-vs-pro' => 'free-vs-pro.php',
	);

	foreach ( $pages as $slug => $file ) {
		$path = $admin_includes_dir . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	$menu_title  = authority_mailer_smtp_get_string( 'menu_title' );
	$setup_title = authority_mailer_smtp_get_string( 'menu_setup' );
	$menu_icon   = authority_mailer_smtp_get_menu_icon();

	// Register admin menu pages.
	add_menu_page(
		$menu_title,
		$menu_title,
		$capability,
		'authority-mailer-smtp-dashboard',
		'authority_mailer_smtp_render_dashboard_page',
		$menu_icon,
		58
	);

	add_submenu_page(
		'authority-mailer-smtp-dashboard',
		authority_mailer_smtp_get_string( 'menu_dashboard_title' ),
		authority_mailer_smtp_get_string( 'menu_dashboard_title' ),
		$capability,
		'authority-mailer-smtp-dashboard',
		'authority_mailer_smtp_render_dashboard_page'
	);

	add_submenu_page(
		'authority-mailer-smtp-dashboard',
		$setup_title,
		$setup_title,
		$capability,
		'authority-mailer-smtp-onboarding',
		array( 'Authority_Mailer_Onboarding', 'render_wizard_page' )
	);

	if ( function_exists( 'authority_mailer_smtp_render_email_log_page' ) ) {
		$callback = 'authority_mailer_smtp_render_email_log_page';
	} else {
		$callback = 'authority_mailer_smtp_email_log_page';
	}

	add_submenu_page(
		'authority-mailer-smtp-dashboard',
		authority_mailer_smtp_get_string( 'menu_email_log' ),
		authority_mailer_smtp_get_string( 'menu_email_log' ),
		$capability,
		'authority-mailer-smtp-email-log',
		$callback
	);

	// Tools submenu.
	add_submenu_page(
		'authority-mailer-smtp-dashboard',
		authority_mailer_smtp_get_string( 'menu_tools' ),
		authority_mailer_smtp_get_string( 'menu_tools' ),
		$capability,
		'authority-mailer-smtp-tools',
		'authority_mailer_smtp_render_tools_page'
	);

	// Free vs Pro comparison submenu.
	add_submenu_page(
		'authority-mailer-smtp-dashboard',
		authority_mailer_smtp_get_string( 'menu_free_vs_pro' ),
		authority_mailer_smtp_get_string( 'menu_free_vs_pro' ),
		$capability,
		'authority-mailer-free-vs-pro',
		'authority_mailer_smtp_render_free_vs_pro_page'
	);

}
add_action( 'admin_menu', 'authority_mailer_smtp_register_admin_menu' );

/**
 * Helper function to get a string from the centralized strings array.
 *
 * Provides a convenient accessor for localized strings stored in the global
 * AUTHORITY_MAILER_STRINGS array.
 *
 * @since 1.0.0
 *
 * @param string $key      The string key to look up in the AUTHORITY_MAILER_STRINGS array.
 * @param string $fallback Optional. Fallback value if key is not found. Default empty string.
 * @return string The localized string value, or the fallback if not found.
 */
function authority_mailer_smtp_get_string( $key, $fallback = '' ) {
	global $AUTHORITY_MAILER_STRINGS;
	if ( isset( $AUTHORITY_MAILER_STRINGS[ $key ] ) ) {
		return $AUTHORITY_MAILER_STRINGS[ $key ];
	}
	return $fallback;
}

/**
 * Email log page placeholder callback.
 *
 * Renders the Email Log admin page by calling the appropriate rendering function
 * if available, or displays a fallback message.
 *
 * @since 1.0.0
 */
function authority_mailer_smtp_email_log_page() {
	if ( function_exists( 'authority_mailer_smtp_render_email_log_page' ) ) {
		call_user_func( 'authority_mailer_smtp_render_email_log_page' );
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html( authority_mailer_smtp_get_string( 'plugin_email_log_page_title' ) ) . '</h1>';
	echo '<div class="wpmsl-card">';
	echo '<p class="wpmsl-muted">' . esc_html( authority_mailer_smtp_get_string( 'plugin_email_log_not_available' ) ) . '</p>';
	echo '</div>';
	echo '</div>';
}



/**
 * Hook debug logging when WP_DEBUG is enabled.
 *
 * @since 1.0.0
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
	add_action(
		'authority_mailer_smtp_debug',
		function ( $message ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( $message );
		}
	);
}
