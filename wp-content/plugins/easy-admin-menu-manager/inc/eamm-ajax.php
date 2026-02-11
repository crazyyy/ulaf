<?php

if ( !defined('ABSPATH') ) exit; //Exit if accessed directly

/**
 * AJAX call function used to toggle the menu. Once the toggle has happened, the page is refreshed
 * through the AJAX call and the menu change is applied.
 *
 * @return void
 */
function eamm_toggle_option(){
	$outcome = false;
	$toggle_active = isset($_POST['ta']) && $_POST['ta'] == 1;
	if ( wp_verify_nonce( $_POST['nonce'], 'eamm-nonce-576' ) ) {
		$opts = get_option('eamm_options');
		if ( $toggle_active ){
			if (isset($opts['toggle']) && $opts['toggle']==1){
				$opts['toggle'] = 0;
			} else {
				$opts['toggle'] = 1;
			}
		}
		$outcome = true;
		update_option('eamm_options', $opts);
	} else {
		$opts = [];
	}
	$opts['outcome'] = $outcome;
	echo json_encode($opts);
	wp_die(); //NB!! to prevent the whitespace error
}


if (!function_exists( 'creator_debug_log' )) {
	/**
	 * Send a debug message to the console (logto = 1) otherwise log to a file called debug_file.txt in the root
	 * This can be viewed by switching on the developer tools console in the browser.
	 *
	 * @param Label on the debug console $label
	 * @param object to display $object
	 * @param priority number for indent $priority
	 * @param logto number $logto (0=php log file, 1=console)
	 */
	function creator_debug_log( $label = null, $object = null, $priority = 1, $logto = 0 ) {
		$priority = $priority < 1 ? 1 : $priority;
		$logto    = $logto > 0 ? true : false;
		$message  = json_encode( $object, JSON_PRETTY_PRINT );
		$stamp    = date( 'Y-m-d H:i:s' );
		$label    = "[" . $stamp . "] " . ( $label ? ( " " . $label . ": " ) : ': ' );
		if ( $logto ) {
			echo "<script>console.log('" . str_repeat( "-", $priority - 1 ) . $label . "', " . $message . ");</script>";
		} else {
			//error_log($label."".$message);
			error_log( $label . "" . $message . "\r\n", 3, ABSPATH . "debug_file.txt" );
		}
	}
}
