<?php
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

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================
// Various constants

$upload_dir = wp_upload_dir();
define('CODE_PROFILER_PRO_UPLOAD_DIR', $upload_dir['basedir'] .'/code-profiler-pro');
define('CODE_PROFILER_PRO_LOG', CODE_PROFILER_PRO_UPLOAD_DIR .'/log.php');
define('CODE_PROFILER_PRO_TMP_IOSTATS_LOG', 'iostats.tmp');
define('CODE_PROFILER_PRO_TMP_SUMMARY_LOG', 'summary.tmp');
define('CODE_PROFILER_PRO_TMP_IOLIST_LOG', 'iolist.tmp');
define('CODE_PROFILER_PRO_TMP_TICKS_LOG', 'ticks.tmp');
define('CODE_PROFILER_PRO_TMP_DISKIO_LOG', 'diskio.tmp');
define('CODE_PROFILER_PRO_TMP_QUERY_LOG', 'query.tmp');
define('CODE_PROFILER_PRO_TMP_CONNECTIONS_LOG', 'connections.tmp');
define('CODE_PROFILER_PRO_UPDATE_NOTICE', '<div class="updated notice is-dismissible"><p>%s</p></div>');
define('CODE_PROFILER_PRO_ERROR_NOTICE', '<div class="error notice is-dismissible"><p>%s</p></div>');
define('CODE_PROFILER_PRO_URI', 'https://code-profiler.com/pro/');
global $wp_version;
if (! defined('CODE_PROFILER_PRO_UA') ) { // UA signatures can be user-defined in the wp-config.php
	define ('CODE_PROFILER_PRO_UA', [
		esc_html__('Desktop', 'code-profiler-pro') => [
			'Firefox'			=> 'Mozilla/5.0 (Linux x86_64; rv:104.0) Gecko/20100101 Firefox/110.0',
			'Chrome'				=> 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko)'.
										' Chrome/111.0.0.0 Safari/537.36',
			'Edge'				=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML,'.
										' like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/110.0.1587.63'
		],
		esc_html__('Mobile', 'code-profiler-pro') => [
			'Android Phone'	=> 'Mozilla/5.0 (Android 13; Mobile; rv:68.0) Gecko/68.0 Firefox/110.0',
			'Android Tablet'	=> 'Mozilla/5.0 (Linux; Android 13.0; SAMSUNG-SM-T377A Build/NMF26X)'.
									' AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36',
			'iPhone'				=> 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15'.
									' (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1',
			'iPad'				=> 'Mozilla/5.0 (iPad; CPU OS 16_3_1 like Mac OS X) AppleWebKit/605.1.15'.
									' (KHTML, like Gecko) GSA/213.0.449417121 Mobile/15E148 Safari/605.1.15'
		],
		esc_html__('Bot', 'code-profiler-pro')    => [
			'Google Bot'		=> 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
			'WordPress'			=> 'Mozilla/5.0 (compatible; CodeProfiler for WordPress/'. $wp_version .
										'; https://code-profiler.com/)'
		]

	] );
}
if (! defined('CODE_PROFILER_PRO_MUPLUGIN') ) {
	// MU plugin's name can be defined in the wp-config.php
	define('CODE_PROFILER_PRO_MUPLUGIN', '0----code-profiler-pro.php');
}
define('CODE_PROFILER_PRO_ACCURACY', [
	1		=> __('Highest (default)', 'code-profiler-pro'),
	5		=> __('High', 'code-profiler-pro'),
	10		=> __('Moderate', 'code-profiler-pro'),
	15		=> __('Low', 'code-profiler-pro'),
	20		=> __('Lowest', 'code-profiler-pro')
]);

// =====================================================================
// Prevent cURL timeout if a plugin changes its timeout options.

function code_profiler_pro_curl_timeout( $handle, $parsed_args, $url ) {

	if ( isset( $_REQUEST['action'] ) &&
		$_REQUEST['action'] == 'codeprofiler_pro_start_profiler') {

		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 300 );
		curl_setopt( $handle, CURLOPT_TIMEOUT, 300 );
	}
}
add_action('http_api_curl', 'code_profiler_pro_curl_timeout', 1000, 3 );

// =====================================================================
// Update version in the DB and check if options must be updated.

function code_profiler_pro_init_update() {

	$cp_options = get_option('code-profiler-pro');
	if ( empty( $cp_options['version'] ) ||
		version_compare( $cp_options['version'], CODE_PROFILER_PRO_VERSION, '<') ) {

		$cp_options['version'] = CODE_PROFILER_PRO_VERSION;

		// Version 1.1
		if (! isset( $cp_options['enable_wpcli'] ) ) {
			$cp_options['enable_wpcli'] = 1;
		}

		// Version 1.2
		if (! isset( $cp_options['disable_wpcron'] ) ) {
			$cp_options['disable_wpcron'] = 1;
		}

		// Version 1.3.1
		if (! isset( $cp_options['http_response'] ) ) {
			$cp_options['http_response'] = '^(?:3|4|5)\d{2}$';
		}

		// Version 1.3.2
		if (! isset( $cp_options['truncate_queries'] ) ) {
			$cp_options['truncate_queries'] = 500;
		}

		// Version 1.4.4
		if (! isset( $cp_options['disable_db-php'] ) ) {
			$cp_options['disable_db-php'] = 1;
		}

		// Version 1.5
		if (! isset( $cp_options['accuracy'] ) ) {
			$cp_options['accuracy'] = 1;
		}

		// Version 1.5.1
		if (! isset( $cp_options['backtrace_limit'] ) ) {
			$cp_options['backtrace_limit'] = 2;
		}

		// Update version in the DB
		update_option('code-profiler-pro', $cp_options );
	}

}
add_action('admin_init', 'code_profiler_pro_init_update');

// =====================================================================
// Verify or create our storage folder in the uploads directory.
// Call during activation and access to the plugin.

function code_profiler_pro_check_uploadsdir() {

	$upload_dir = wp_upload_dir();
	if (! file_exists( CODE_PROFILER_PRO_UPLOAD_DIR ) ) {
		mkdir( CODE_PROFILER_PRO_UPLOAD_DIR, 0755 );
	}
	if (! is_writable( CODE_PROFILER_PRO_UPLOAD_DIR ) ) {
		// PHP running as an Apache module?
		chmod( CODE_PROFILER_PRO_UPLOAD_DIR, 0777 );
	}
	if (! file_exists( CODE_PROFILER_PRO_UPLOAD_DIR .'/index.html') ) {
		touch( CODE_PROFILER_PRO_UPLOAD_DIR .'/index.html');
	}
	// For Apache & Litespeed
	if (! file_exists( CODE_PROFILER_PRO_UPLOAD_DIR .'/.htaccess') ) {
		file_put_contents(
			CODE_PROFILER_PRO_UPLOAD_DIR .'/.htaccess',
			'Require all denied'
		);
	}
	// Make sure there's a MU plugin directory
	if (! is_dir( WPMU_PLUGIN_DIR ) ) {
		wp_mkdir_p( WPMU_PLUGIN_DIR );
	}


	if (! file_exists( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN ) ) {

		if (! is_writable( WPMU_PLUGIN_DIR ) ) {
			wp_die(
				sprintf(
					__('Error: The MU folder is read only, please make it writable: %s', 'code-profiler-pro'),
					esc_html( WPMU_PLUGIN_DIR ) .'/'
				)
			);
		}

		if (! file_exists( __DIR__ .'/mu.plugin') ) {
			code_profiler_pro_log_error( sprintf(
				esc_html__('Cannot find the MU plugin: %s', 'code-profiler-pro'),
				__DIR__ .'/mu.plugin'
			));
		} else {
			$res = copy( __DIR__ .'/mu.plugin', WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN );
			if ( $res === false ) {
				code_profiler_pro_log_error( sprintf(
					esc_html__('Cannot copy the MU plugin to %s', 'code-profiler-pro'),
					WPMU_PLUGIN_DIR
				));
			}
		}
	} else {
		// Update MU plugin it if needed
		if ( md5_file( WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN ) !== md5_file( __DIR__ .'/mu.plugin') ) {
			copy( __DIR__ .'/mu.plugin', WPMU_PLUGIN_DIR .'/'. CODE_PROFILER_PRO_MUPLUGIN );
		}
	}

	// Log file
	if (! file_exists( CODE_PROFILER_PRO_LOG ) ) {
		file_put_contents( CODE_PROFILER_PRO_LOG, "<?php exit; ?>\n" );
	}

}

// =====================================================================
// Check the PHP memory limit and return a suggested size for the
// profiler's buffer.

function code_profiler_pro_suggested_memory() {

	$memory_limit = ini_get('memory_limit');

	if ('-1' == $memory_limit ) {
		// Return max size
		return 10;
	}

	if ( preg_match('/^(\d+)([PTGMK])$/i', $memory_limit, $match ) ) {
		$bytes = (int) $match[1];
		switch ( strtoupper( $match[2] ) ) {
			case 'P':
				$bytes *= 1024;
			case 'T':
				$bytes *= 1024;
			case 'G':
				$bytes *= 1024;
			case 'M':
				$bytes *= 1024;
			case 'K':
				$bytes *= 1024;
		}
		// 256 MB
		if ( $bytes >= 268435456 ) {
			return 10;
		// 128 MB
		} elseif ( $bytes >= 134217728 ) {
			return 7;
		// 64 MB
		} elseif ( $bytes >= 67108864 ) {
			return 4;
		// <64 MB
		} else {
			return 1;
		}
	}
	// Don't know :/
	return 5;
}

// =====================================================================
// Create the default options.

function code_profiler_pro_default_options() {

	$buffer = code_profiler_pro_suggested_memory();

	$cp_options = [
		'show_paths' 			=> 'relative',
		'display_name'			=> 'full',
		'truncate_name'		=> 30,
		'chart_type'			=> 'x',
		'chart_max_plugins'	=> 25,
		'hide_empty_value'	=> 1,
		'table_max_rows'		=> 30,
		'warn_composer'		=> 1,
		'enable_wpcli'			=> 1,
		'disable_wpcron'		=> 1,
		'disable_db-php'		=> 1,
		'http_response'		=> '^(?:3|4|5)\d{2}$',
		'accuracy'				=> 1,
		'backtrace_limit'		=> 2,
		'buffer'					=> (int) $buffer

	];

	update_option('code-profiler-pro', $cp_options );

}

// ===================================================================== 2023-06-14
// Create a profile name.

function code_profiler_pro_profile_name() {

	return date('Y-m-d_') . substr( time(), 4 );
}

// ===================================================================== 2023-06-14
// Disable PHP display_errors so that notice, warning and error messages
// don't show up in the AJAX response.

function code_profiler_pro_hide_errors() {

	ini_set('display_errors', 0 );
}

// =====================================================================
// Disable opcode cache.

function code_profiler_pro_disable_opcode() {

	try {
		if (extension_loaded('Zend OPcache')) {
			ini_set('opcache.enable', 0);
		} elseif ( extension_loaded('wincache') ) {
			ini_set('wincache.fcenabled', 0);
		}
		set_time_limit(0);

	} catch ( Exception $e ) { }

	$cp_options = get_option('code-profiler-pro');
	if (! empty( $cp_options['disable_wpcron'] ) ) {
		if (! defined('DISABLE_WP_CRON') ) {
			define('DISABLE_WP_CRON', true );
		}
	}

}
// =====================================================================
// Verify the security key when running the profile.

function code_profiler_pro_verify_key() {

	$response = [
		'status'		=> 'error',
		'message'	=> __('Security keys do not match. Reload the page and try again (%s)', 'code-profiler-pro')
	];

	$cp_options = get_option('code-profiler-pro');

	if ( empty( $cp_options['hash'] ) ) {
		$response['message'] = sprintf( $response['message'], '#1');

	} else {
		if ( empty( $_REQUEST['profiler_key'] ) ) {
			$response['message'] = sprintf( $response['message'], '#2');

		} else {
			if ( $cp_options['hash'] == sha1( $_REQUEST['profiler_key'] ) ) {
				return;
			} else {
				$response['message'] = sprintf( $response['message'], '#3');
			}
		}
	}
	wp_send_json( $response );
}

// =====================================================================
// Write message to the log.
// Log level can be a combination of INFO (1), WARN (2), ERROR (4)
// and DEBUG (8).

function code_profiler_pro_write2log( $message, $level, $create ) {

	if ( empty( $create ) ) {
		file_put_contents(
			CODE_PROFILER_PRO_LOG,
			time() ."~~$level~~$message\n",
			FILE_APPEND
		);
	} else {
		file_put_contents(
			CODE_PROFILER_PRO_LOG,
			time() ."~~$level~~$message\n"
		);
	}
}

function code_profiler_pro_log_info(  $string, $create = 0 ) {
	code_profiler_pro_write2log( $string, 1, $create );
}
function code_profiler_pro_log_warn(  $string, $create = 0 ) {
	code_profiler_pro_write2log( $string, 2, $create );
}
function code_profiler_pro_log_error( $string, $create = 0 ) {
	code_profiler_pro_write2log( $string, 4, $create );
}
function code_profiler_pro_log_debug( $string, $create = 0 ) {
	code_profiler_pro_write2log( $string, 8, $create );
}

// =====================================================================
// Verify if a profile exists and return its full path.

function code_profiler_pro_get_profile_path( $id, $type = 'slugs') {

	$return = false;

	if (! empty( $id ) && preg_match('/^\d{10}\.\d+$/', $id ) ) {
		$glob = glob( CODE_PROFILER_PRO_UPLOAD_DIR ."/$id.*.$type.profile" );
		if ( is_array( $glob ) && ! empty( $glob[0] ) ) {
			if ( preg_match( "`/$id\.(.+?).$type.profile$`", $glob[0], $match ) ) {

				return CODE_PROFILER_PRO_UPLOAD_DIR. "/$id.{$match[1]}";
			}
		}
	}
	return false;
}

// =====================================================================
// Open, parse and return the content of a profile file.

function code_profiler_pro_get_profile_data( $file, $type = 'slugs') {

	$buffer = [];

	$fh = fopen( "$file.$type.profile", 'rb');

	if ( $fh === false ) {
		$err = sprintf(
			CODE_PROFILER_PRO_ERROR_NOTICE,
			sprintf(
				esc_html__('Unable to open profile file: %s.', 'code-profiler-pro'),
				esc_html( "$file.$type.profile" )
			)
		);
		$buffer['error'] = $err;
		return $buffer;
	}

	while (! feof( $fh ) ) {
		$buffer[] = fgetcsv( $fh, 1000, "\t" );
	}

	fclose( $fh );

	if ( empty( $buffer ) ) {
		$err = sprintf(
			CODE_PROFILER_PRO_ERROR_NOTICE,
			sprintf(
				esc_html__('Profile file is empty or corrupted: %s.', 'code-profiler-pro'),
				esc_html( "$file.$type.profile" )
			)
		);
		$buffer['error'] = $err;
		return $buffer;
	}

	// Get rid of the empty field created by fgetcsv
	return array_filter( $buffer );
}

// =====================================================================
// Exit with a json-encoded error message except if we're running
// from WP CLI

function code_profiler_pro_wp_send_json( $response ) {

	if ( defined('WP_CLI') && WP_CLI ) {
		WP_CLI::error( $response['message'] );
	}
	wp_send_json( $response );
}

// =====================================================================
// Retrieve the summary stats for a given profile.

function code_profiler_pro_getsummarystats( $profile_path, $type = 'html') {

	if ( $type == 'html') {
		$open		= '<ul><li>&#10148; ';
		$div		= '</li><li>&#10148; ';
		$close	= '</li></ul>';
	} else {
		// WP-CLI
		$open		= " \u{27A4} ";
		$div		= "\n \u{27A4} ";
		$close	= "\n\n";
	}

	if (! file_exists( "$profile_path.summary.profile" ) ) {
		return false;
	}
	$decode	= json_decode( file_get_contents( "$profile_path.summary.profile" ) );

	$string	= $open;

	if ( empty( $decode->items ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = (int) --$decode->items;
	}
	$string .= sprintf(
		__('%s plugins and 1 theme', 'code-profiler-pro'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->time ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->time, 4 );
	}
	$string .= sprintf(
		__('Execution time: %ss', 'code-profiler-pro'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->memory ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( $decode->memory, 2);
	}
	$string .= sprintf(
		__('Peak memory: %s MB', 'code-profiler-pro'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->io ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( (int) $decode->io );
	}
	$string .= sprintf(
		__('File I/O operations: %s', 'code-profiler-pro'),
		$tmp
	);

	$string .= $div;

	if ( empty( $decode->queries ) ) {
		$tmp = 'N/A';
	} else {
		$tmp = number_format( (int) $decode->queries );
	}
	$string .= sprintf(
		__('SQL queries: %s', 'code-profiler-pro'),
		$tmp
	);

	$string .= $div;

	if (! isset($decode->connections ) ) {
		// CP <1.6
		$tmp = 'N/A';
	} elseif ( empty( $decode->connections ) ) {
		$tmp = '0';
	} else {
		$tmp = number_format( $decode->connections );
	}
	$string .= sprintf(
		__('Remote connections: %s', 'code-profiler-pro'),
		$tmp
	);

	$string .= $close;

	return $string;
}

// =====================================================================
// Remove *tmp files left after an HTTP error (4xx or 5xx).

function code_profiler_pro_cleantmpfiles() {

	$glob = glob( CODE_PROFILER_PRO_UPLOAD_DIR .'/*.tmp');
	if ( is_array( $glob ) ) {
		$count = 0;
		foreach( $glob as $file ) {
			$count++;
			unlink( $file );
		}
		if ( $count ) {
			code_profiler_pro_log_info( sprintf(
				__('Deleting %s temporary files found in the profiles folder', 'code-profiler-pro'),
				(int) $count
			) );
		}
	}
}

// =====================================================================
// Clear the log if it's bigger than 100KB.

function code_profiler_pro_clearlog() {

	if ( file_exists( CODE_PROFILER_PRO_LOG ) && filesize( CODE_PROFILER_PRO_LOG ) > 100000 ) {
		file_put_contents( CODE_PROFILER_PRO_LOG, "<?php exit; ?>\n"	);
	}
}

// ===================================================================== 2023-06-15
// We don't want to be bothered by other themes/plugins' admin notices.

add_action('admin_head', 'code_profiler_pro_hide_admin_notices', 999);

function code_profiler_pro_hide_admin_notices() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'code-profiler-pro') {
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		add_filter('admin_footer_text', 'code_profiler_pro_admin_footer');
	}

	if ( is_main_site() && is_super_admin() ) {
		add_action('all_admin_notices', 'code_profiler_pro_admin_notice');
	}
}

function code_profiler_pro_admin_footer () {
    echo '<span id="footer-thankyou">'.
		sprintf(
			/* Translators: %s are the '<a href="">' and '</a>' anchors */
			esc_html(
				'Thank you for using %sCode Profiler%s.',
				'code-profiler-pro'
			),
			'<a href="https://code-profiler.com" target="_blank">',
			'</a>'
		).
		'</span>';
}

// =====================================================================
// Display an admin notice if the license expired.

function code_profiler_pro_admin_notice() {

	global $pagenow;
	if ( $pagenow != 'plugins.php' && (! isset( $_GET['page'] ) || $_GET['page'] != 'code-profiler-pro') ) {
		return;
	}

	$license_link = sprintf(
		'<a href="%sadmin.php?page=code-profiler-pro&cptab=license">',
		esc_url( admin_url() )
	);

	$res = code_profiler_pro_is_license();

	if ( $res == 0 ) {
		echo '<div class="notice-warning notice is-dismissible"><p>'.
		sprintf(
			/* Translators: %s are the '<a href="">' and '</a>' anchors */
			esc_html__('Please %sclick here%s to enter your Code Profiler Pro license.', 'code-profiler-pro'),
			$license_link, '</a>'
		) . '</p></div>';
		return;
	}

	if ( $res == 4 ) {
		echo '<div class="notice-warning notice is-dismissible"><p>'.
		sprintf(
			esc_html__('Missing license expiry date. Please click the "%s" button below to fix the problem.', 'code-profiler-pro'),
			esc_html__('Check License', 'code-profiler-pro')
		).
			'</p></div>';
		return;
	}

	if ( $res == 3 ) {
		echo '<div class="error notice is-dismissible"><p>' .
		esc_html__('Your Code Profilder Pro license has expired.', 'code-profiler-pro').
		' '.
		sprintf(
			/* Translators: the two %s are the '<a href="....">' and '</a>' anchors */
			esc_html__('Please %sclick here%s to renew it.', 'code-profiler-pro'),
			$license_link, '</a>'
		). '</p></div>';
		return;
	}

	if ( $res == 2 ) {
		echo '<div class="notice-warning notice is-dismissible"><p>' .
		esc_html__('Your Code Profilder Pro license will expire soon.', 'code-profiler-pro').
		' '.
		sprintf(
			/* Translators: the two %s are the '<a href="....">' and '</a>' anchors */
			esc_html__('Please %sclick here%s to renew it.', 'code-profiler-pro'),
			$license_link, '</a>'
		). '</p></div>';
		return;
	}
}

// =====================================================================
// Return info about the license:
// 0 : no license
// 1 : license ok
// 2 : license expires <30 days
// 3 : license expired
// 4 : expiry date is invalid

function code_profiler_pro_is_license() {

	$cp_options = get_option('code-profiler-pro');

	if ( empty( $cp_options['license'] ) ) {
		return 0;
	}

	if ( isset( $cp_options['expire'] ) &&
		preg_match('/^\d{4}-\d{2}-\d{2}$/', $cp_options['expire'] ) ) {

		if ( $cp_options['expire'] < date('Y-m-d') ) {
			return 3;
		}
		if ( $cp_options['expire'] < date('Y-m-d', strtotime('+30 day') ) ) {
			return 2;
		}
		return 1;
	}
	return 4;
}

// =====================================================================
// Check and inform if there's an update (we don't want WordPress
// to connect to the WordPress.org repo).

function code_profiler_pro_check_update( $transient ) {

	if ( empty( $transient->checked['code-profiler-pro/index.php'] ) ) {
		$version = CODE_PROFILER_PRO_VERSION;
	} else {
		$version = $transient->checked['code-profiler-pro/index.php'];
	}

	$args = array(
		'slug' => 'code-profiler-pro',
		'version' => $version
	);

	$domain  = strtolower( parse_url( network_home_url(), PHP_URL_HOST ) );
	global $wp_version;
	$cp_options = get_option('code-profiler-pro');
	if (! empty( $cp_options['license'] ) ) {
		$license    = $cp_options['license'];
	} else {
		$license    = '';
	}
	$request_string = array(
			'body' => array(
				'action'  => 'version',
				'request' => serialize( $args ),
				'license' => $license,
				'domain'	 => $domain,
				'version' => CODE_PROFILER_PRO_VERSION
			),
			'user-agent' => "WordPress/$wp_version;$domain; " . CODE_PROFILER_PRO_VERSION
		);

	$res = wp_remote_post( CODE_PROFILER_PRO_URI, $request_string);

	if (! is_wp_error( $res ) && $res['response']['code'] == 200 ) {
		$response = unserialize( $res['body'] );
	}
	if ( ! empty( $response ) && is_object( $response ) ) {
		$transient->response['code-profiler-pro/index.php'] = $response;
	} else {
		// No update
       $item = (object) array(
			'id'            => 'code-profiler-pro/index.php',
			'slug'          => 'code-profiler-pro',
			'plugin'        => 'code-profiler-pro/index.php',
			'new_version'   => CODE_PROFILER_PRO_VERSION,
			'url'           => '',
			'package'       => '',
			'icons'         => array(),
			'banners'       => array(),
			'banners_rtl'   => array(),
			'tested'        => '',
			'requires_php'  => '',
			'compatibility' => new stdClass()
		);
		// Adding the "mock" item to the `no_update` property is required
		// for the enable/disable auto-updates links to correctly appear in UI.
		$transient->no_update['code-profiler-pro/index.php'] = $item;
	}

	return $transient;
}

add_filter('pre_set_site_transient_update_plugins', 'code_profiler_pro_check_update');

// =====================================================================
// We return the new version's changelog, if any, but we don't return
// any download link.

function code_profiler_pro_check_plugin_info( $def, $action, $args ) {

	if (! isset($args->slug) || $args->slug != 'code-profiler-pro' ||
		$action != 'plugin_information'  ) {

		return $def;
	}
	$plugin_info = get_site_transient('update_plugins');
	if ( isset( $plugin_info->checked['code-profiler-pro/index.php'] ) ) {
		$current_version = $plugin_info->checked['code-profiler-pro/index.php'];
	} else {
		$current_version = CODE_PROFILER_PRO_VERSION;
	}
	$args->version = $current_version;

	$cp_options = get_option('code-profiler-pro');
	if (! empty( $cp_options['license'] ) ) {
		$license    = $cp_options['license'];
	} else {
		$license    = '';
	}
	$domain     = strtolower( parse_url( network_home_url(), PHP_URL_HOST ) );

	global $wp_version;

	$request_string = array(
		'body' => array(
			'action'  => 'plugin_information',
			'request' => serialize( $args ),
			'license' => $license,
			'domain'	 => $domain,
			'version' => CODE_PROFILER_PRO_VERSION
		),
		'user-agent' => "WordPress/$wp_version;$domain; " . CODE_PROFILER_PRO_VERSION
	);

	$res = wp_remote_post( CODE_PROFILER_PRO_URI, $request_string );

	if (! is_wp_error( $res ) && $res['response']['code'] == 200 ) {
		// Server can return either serialized data or json-encoded error
		if ( is_serialized( $res['body'] ) ) {
			return unserialize( $res['body'] );
		}
	}
	return false;
}

add_filter('plugins_api', 'code_profiler_pro_check_plugin_info', 10, 3);

// =====================================================================
// Check or save the license.

function code_profiler_pro_check_license() {

	$return = [];
	$cp_options = get_option('code-profiler-pro');

	if ( empty( $_POST['cppro-license'] ) || strlen( $_POST['cppro-license'] ) < 10
		|| strlen( $_POST['cppro-license'] ) > 64
		|| ! preg_match('`[-\dA-Za-z]`', $_POST['cppro-license'] ) ) {

		$res['error'] = esc_html__('Please enter a valid license.', 'code-profiler-pro');
		return $res;
	}

	global $wp_version;
	$license = strtoupper( $_POST['cppro-license'] );
	$domain  = strtolower( parse_url( network_home_url(), PHP_URL_HOST ) );

	$request = array(
		'body' => array(
			'license' => $license,
			'domain'  => $domain,
			'version' => CODE_PROFILER_PRO_VERSION
		),
		'user-agent' => 'Mozilla/5.0 (compatible; Code-Profiler/'.
							 CODE_PROFILER_PRO_VERSION ."; WordPress/{$wp_version})",
		'timeout'    => 60,
		'httpversion'=> '1.1',
		'sslverify'  => true
	);
	if ( ! empty( $_POST['check_license'] ) ) {
		$request['body']['action'] = 'check_license';
	} else {
		$request['body']['action'] = 'save_license';
	}

	$res = wp_remote_post( CODE_PROFILER_PRO_URI, $request );

	if (! is_wp_error( $res ) ) {
		if ( $res['response']['code'] == 200 ) {
			// We have a response
			$data = json_decode( $res['body'], true );

			// Error (invalid license etc)
			if (! empty( $data['error'] ) ) {
				$return['error'] = sprintf(
					esc_html__('Error: %s', 'code-profiler-pro'),
					esc_html( $data['error'] )
				);
				if (! empty( $data['clear'] ) ) {
					unset( $cp_options['license'] );
					unset( $cp_options['expire'] );
					unset( $cp_options['domain'] );
					update_option('code-profiler-pro', $cp_options );
				}
				return $return;
			}

			// Fetch expiry date
			if (! empty( $data['expire'] ) && ! empty( $data['license'] ) &&
				! empty( $data['domain'] ) ) {
				$cp_options['license'] = $data['license'];
				$cp_options['expire']  = $data['expire'];
				$cp_options['domain']  = $data['domain'];
				update_option('code-profiler-pro', $cp_options );

				// Save it
				if (! empty( $_POST['save_license'] ) ) {
					$return['message'] = esc_html__('Your license was saved.', 'code_profiler_pro_license');
				// Check it
				} else {
					$return['message'] = esc_html__('Your license is valid.', 'code_profiler_pro_license');
				}
				return $return;
			}

			$return['error'] = sprintf(
				esc_html__('Error: %s', 'code-profiler-pro'),
				esc_html__('Unknown error', 'code-profiler-pro')
			);
			return $return;

		// Not a 200 OK return code
		} else {
			$return['error'] = sprintf(
				esc_html__('Error: The server returned HTTP code %s', 'code-profiler-pro'),
				esc_html( $res['response']['code'] )
			);
			return $return;
		}

	// Connection error
	} else {
		$return['error'] = sprintf(
			esc_html__('Error: Unable to connect to the remote server (%s)', 'code-profiler-pro'),
			esc_html( $res->get_error_message() )
		);
		return $return;
	}

}

// =====================================================================
// EOF
