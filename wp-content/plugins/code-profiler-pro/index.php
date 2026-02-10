<?php
/*
Plugin Name: Code Profiler Pro
Plugin URI: https://code-profiler.com/
Description: A profiler to measure the performance of your WordPress plugins and themes.
Author: Jerome Bruandet ~ NinTechNet Ltd.
Author URI: https://code-profiler.com/
Version: 1.6.5
Network: true
License: GPLv3 or later
Text Domain: code-profiler-pro
Domain Path: /languages
Update URI: https://code-profiler.com/
*/

define('CODE_PROFILER_PRO_VERSION', '1.6.5');
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================
// Menu functions
require __DIR__ .'/lib/menu.php';
// Helper (can be already loaded by the MU plugin)
require_once __DIR__ .'/lib/helper.php';
// AJAX calls
require __DIR__ .'/lib/ajax.php';
// ===================================================================== 2023-06-17
// Activation: make sure the blog meets the requirements.

function code_profiler_pro_activate() {

	if (! defined('WP_CLI') && ! current_user_can('activate_plugins') ) {
		exit( esc_html__('Your are not allowed to activate plugins.',
			'code-profiler-pro')
		);
	}

	global $wp_version;
	if ( version_compare( $wp_version, '5.0', '<') ) {
		exit( sprintf(
			esc_html__('Code Profiler requires WordPress %s or greater but '.
			'your current version is %s.', 'code-profiler-pro'),
			'5.0',
			esc_html( $wp_version )
		) );
	}

	if ( version_compare( PHP_VERSION, '7.1', '<') ) {
		exit( sprintf(
			esc_html__('Code Profiler requires PHP 7.1 or greater but your '.
			'current version is %s.', 'code-profiler-pro'),
			esc_html( PHP_VERSION )
		) );
	}

	// Verify/create our storage folder
	code_profiler_pro_check_uploadsdir();

	// If we don't have any options yet, create them
	$cp_options = get_option('code-profiler-pro');
	if ( $cp_options === false ) {
		code_profiler_pro_default_options();
	}

	// If the free version is active, we deactivate it
	if ( is_plugin_active('code-profiler/index.php') ) {
		deactivate_plugins('code-profiler/index.php');
	}
}

register_activation_hook( __FILE__, 'code_profiler_pro_activate');

// ===================================================================== 2023-06-17
// Deactivation.

function code_profiler_pro_deactivate() {

	if (! defined('WP_CLI') && ! current_user_can('activate_plugins') ) {
		exit( esc_html__('Your are not allowed to deactivate plugins.',
			'code-profiler-pro')
		);
	}

	// Remove the MU plugin
	if ( file_exists( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN ) ) {
		unlink( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN );
	}
}

register_deactivation_hook( __FILE__, 'code_profiler_pro_deactivate');

// ===================================================================== 2023-06-14
// Create Profiler's menu.

function code_profiler_pro_admin_menu() {

	// In a MU environment, only the superadmin can run Code Profiler
	if (! is_super_admin() ) {
		return;
	}

	add_menu_page(
		'Code Profiler Pro',
		'Code Profiler Pro',
		'manage_options',
		'code-profiler-pro',
		'code_profiler_pro_main_menu',
		plugins_url('/static/dashicon-16x16.png', __FILE__ )
	);
}

add_action('admin_menu', 'code_profiler_pro_admin_menu');

// ===================================================================== 2023-06-17
// Register scripts and styles.

function code_profiler_pro_enqueue( $hook ) {

	if (! is_super_admin() ) {
		return;
	}

	// Load files only if we're in Code Profiler's main page
	if ( $hook != 'toplevel_page_code-profiler-pro') {
		return;
	}

	wp_enqueue_script(
		'code_profiler_pro_javascript',
		plugin_dir_url( __FILE__ ) .'static/code-profiler-pro.js',
		['jquery'],
		CODE_PROFILER_PRO_VERSION
	);

	wp_enqueue_script(
		'code-profiler_pro_tiptip',
		plugin_dir_url( __FILE__ ) .'static/vendor/jquery.tipTip.js',
		['jquery'],
		CODE_PROFILER_PRO_VERSION
	);

	wp_enqueue_style(
		'code-profiler_pro_style',
		plugin_dir_url( __FILE__ ) .'static/code-profiler-pro.css',
		[],
		CODE_PROFILER_PRO_VERSION
	);

	// Enqueue Chart.js if we're viewing a profile
	if ( isset( $_REQUEST['action'] ) &&
		$_REQUEST['action'] == 'view_profile') {

		wp_enqueue_script(
			'code_profiler_pro_charts',
			plugin_dir_url( __FILE__ ) .'static/vendor/chart.min.js',
			['jquery'],
			CODE_PROFILER_PRO_VERSION,
			// We load it in the footer, because some plugins loads it too
			// on every pages and that could mess with our pages
			true
		);
	}

	// JS i18n
	$code_profiler_pro_i18n = [
		'missing_nonce' =>
			esc_attr__('Security nonce is missing, try to reload the page.',
			'code-profiler-pro'),
		'missing_frontbackend' =>
			esc_attr__('Please select either the fontend or backend option.',
			'code-profiler-pro'),
			'missing_profileid' =>
			esc_attr__('Missing profile identifier.', 'code-profiler-pro'),
		'missing_profilename' =>
			esc_attr__('Please enter a name for this profile.',
			'code-profiler-pro'),
		'missing_userauth' =>
			esc_attr__('Please select whether the profiler should run as '.
			'an authenticated user or not.', 'code-profiler-pro'),
		'missing_username' =>
			esc_attr__('Please enter the name of the user.',
			'code-profiler-pro'),
		'missing_post' =>
			esc_attr__('Please select a page to profile.',
			'code-profiler-pro'),
		'unknown_error' =>
			esc_attr__('Unknown error returned by AJAX',
			'code-profiler-pro'),
		'http_error' =>
			esc_attr__('The HTTP server returned the following error:',
			'code-profiler-pro'),
		'timeout_error' =>
			esc_attr__('You can try to select a lower Accuracy and '.
			'Precision level in the Settings page', 'code-profiler-pro'),
		'notfound_error' =>
			esc_attr__('The requested page does not seem to exist. '.
			'Please check the syntax of the URL you want to profile',
			'code-profiler-pro'),
		'forbidden_error' =>
			esc_attr__('The server rejected and blocked the requested page. '.
			'Make sure you are allowed to access that page or that there '.
			'is no security plugin or application blocking it',
			'code-profiler-pro'),
		'internal_error' =>
			esc_attr__('This is an internal server error. Please check your'.
			' server, PHP and Code Profiler logs as they may contain more '.
			'details about the error', 'code-profiler-pro'),
		'unknown_error' =>
			esc_attr__('An unknown error occurred:', 'code-profiler-pro'),
		'preparing_report' =>
			esc_attr__('Preparing the report', 'code-profiler-pro') .'...',
		'empty_log' =>
			esc_attr__('No records were found that match the specified '.
			'search criteria.', 'code-profiler-pro'),
		'delete_log' =>
			esc_attr__('Delete log?', 'code-profiler-pro'),
		'delete_profile' =>
			esc_attr__('Delete this profile?', 'code-profiler-pro'),
		// Charts
		'exec_sec_plugins' =>
			esc_attr__('Execution time in seconds', 'code-profiler-pro'),
		'pc_plugins' =>
			/* Translators: xx% of all plugins and the theme */
			esc_attr__('% of all plugins and the theme', 'code-profiler-pro'),
		'exec_tot_plugins_1' =>
			esc_attr__('Plugins and theme execution time, in seconds',
			'code-profiler-pro'),
		'chart_total' =>
			esc_attr__('total:', 'code-profiler-pro'),
		'iolist_total_calls' =>
			esc_attr__('Total calls', 'code-profiler-pro'),
		'io_calls' =>
			esc_attr__('File I/O operations', 'code-profiler-pro'),
		'disk_io_bytes' =>
			esc_attr__('Total bytes', 'code-profiler-pro'),
		'disk_io_title' =>
			esc_attr__('Total Disk I/O read and write, in bytes',
			'code-profiler-pro'),
		'text_copied' =>
			esc_attr__('The text was successfully copied to the clipboard.',
			'code-profiler-pro')

	];
	wp_localize_script(
		'code_profiler_pro_javascript',
		'cpi18n',
		$code_profiler_pro_i18n
	);
}

add_action('admin_enqueue_scripts', 'code_profiler_pro_enqueue');

// =====================================================================
// Display the settings link in the "Plugins" page.

function code_profiler_pro_settings_link( $links ) {

   $links[] = '<a href="'. get_admin_url( null, 'admin.php?page=code-profiler-pro') .
					'">'.	esc_html__('Start Profiling', 'code-profiler-pro'). '</a>';
	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'code_profiler_pro_settings_link');

// =====================================================================
// Download data as a CSV file.

function code_profiler_pro_download_csv() {

	// Only the superadmin can access this part
	if (! is_super_admin() ) {
		return;
	}

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'code-profiler-pro' &&
		isset( $_REQUEST['cp-download-csv'] ) &&
		preg_match('`^\d{10}\.\d+$`', $_REQUEST['cp-download-csv'] ) &&
		! empty( $_REQUEST['cp-type'] ) &&	in_array( $_REQUEST['cp-type'], [
			'profiles',
			'slugs',
			'scripts',
			'functions',
			'connections',
			'iolist',
			'iostats',
			'diskio',
			'queries' ] )
	) {

		if ( $_REQUEST['cp-type'] == 'profiles') {
			require_once __DIR__ .'/lib/class-export-list.php';
			new CodeProfilerPro_ExportList();

		} else {
			require_once __DIR__ .'/lib/class-export.php';
			new CodeProfilerPro_Export();
		}
	}
}

add_action('admin_init', 'code_profiler_pro_download_csv');

// =====================================================================
// File viewer.

function code_profiler_pro_file_viewer() {

	// Only the superadmin can access this part
	if (! is_super_admin() ) {
		return;
	}

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'code-profiler-pro' &&
		isset( $_GET['ffile'] ) && isset( $_GET['fnonce'] ) ) {

		// Security nonce
		if ( wp_verify_nonce( $_GET['fnonce'], 'code-profile-view-file') === false ) {
			wp_die( esc_html__('Missing or wrong security nonce. Reload the page and try again', 'code-profiler-pro') );
		}

		$ffile = base64_decode( $_GET['ffile'] );
		if ( $ffile === false ) {
			wp_die( esc_html__('File does not seem valid', 'code-profiler-pro') );
		}

		if (! preg_match('`^(?i:[a-z]:|/)`', $ffile ) || preg_match('`\.\.\B`', $ffile ) ) {
			wp_die( sprintf(
				esc_html__('File does not seem valid: %s', 'code-profiler-pro'),
				esc_html( $ffile )
			) );
		}

		// File must be in the ABSPATH or DOCUMENT_ROOT (or the folder above them):
		if ( empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
			$path_docroot = preg_quote( dirname( ABSPATH ) );
		} else {
			$path_docroot = preg_quote( dirname( $_SERVER['DOCUMENT_ROOT'] ) );
		}
		$path_abs = preg_quote( dirname( ABSPATH ) );
		if (! preg_match( "`^($path_abs|$path_docroot)`", realpath( $ffile ) ) ) {
			wp_die( sprintf(
				esc_html__('File is not in the ABSPATH or DOCUMENT_ROOT: %s', 'code-profiler-pro'),
				esc_html( $ffile )
			) );
		}

		$content = file_get_contents( $ffile );
		// We cannot display binaries
		code_profiler_pro_is_binary( $content );

		// Check if we need to display a method/function and or line number
		$ffunction = ''; $fline = 0;
		if ( ! empty( $_GET['ffunction'] ) ) {
			$ffunction = $_GET['ffunction'];

		} elseif (! empty( $_GET['fline'] ) && preg_match('/^\d+$/', $_GET['fline'] ) ) {
			$fline = $_GET['fline'];
		}

		require 'lib/class-fileview.php';
		new CodeProfilerPro_FileView( $ffile, $ffunction, $fline );

		exit;
	}

}

add_action('admin_init', 'code_profiler_pro_file_viewer');

// =====================================================================
// Check if the file is binary.

function code_profiler_pro_is_binary( $content ) {

	if ( strpos( $content, "\x00" ) !== false ) {
		wp_die( esc_html__('This is a binary file and it cannot be viewed.', 'code-profiler-pro') );
	}
}

// =====================================================================
// WP CLI commands.

if ( defined('WP_CLI') && WP_CLI ) {
	require_once __DIR__ . '/lib/class-cli.php';
}

// =====================================================================
// EOF
