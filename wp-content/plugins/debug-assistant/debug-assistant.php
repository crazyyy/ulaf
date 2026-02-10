<?php
/*
Plugin Name: Debug Assistant
Plugin URI: https://www.wpindeed.com/
Description: Allows admin to edit wp-config.php file by activating some Wordpress constants only for debugging purposes. Also administrator can receive reports about PHP environment or system report. With Debug Assistant it can be created temporary admin users, performing databse queries and much more.
Version: 1.6
Author: WPIndeed
Author URI: https://www.wpindeed.com
*/


if(!defined('IMLT_DIR_PATH')) {
  define('IMLT_DIR_PATH', plugin_dir_path(__FILE__));
}

if(!defined('IMLT_DIR_URL')) {
  define('IMLT_DIR_URL', plugin_dir_url(__FILE__));
}
if(!defined('IMLT_PROTOCOL')){
  if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		define('IMLT_PROTOCOL', 'https://' );
	} else {
	define('IMLT_PROTOCOL', 'http://' );
  }
}

function imlt_setLang()
{
    // language
    add_action('plugins_loaded', function(){
       load_plugin_textdomain( 'debug-assistant', false, IMLT_DIR_PATH . '/languages/' );
    });
}

if(!defined('IMLT_POST_TYPE') || IMLT_POST_TYPE == '' ) {
  define('IMLT_POST_TYPE', 'ind_lucky');
}

require_once IMLT_DIR_PATH . 'utilities.php';

require_once IMLT_DIR_PATH . 'classes/ImltInstall.php';
$ImltInstall = new ImltInstall();

require_once IMLT_DIR_PATH . 'classes/IndeedView.php';
require_once IMLT_DIR_PATH . 'classes/ImltMain.php';
$ImltMain = new ImltMain();

require_once IMLT_DIR_PATH . 'classes/ImltErrors.php';
$imltErrors = new ImltErrors();

require_once IMLT_DIR_PATH . 'classes/ImltConfigFileActions.php';
$imltConfigFileActions = new ImltConfigFileActions();

require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
$imltdatabse = new ImltDatabase();

require_once IMLT_DIR_PATH . 'classes/ImltAjax.php';
$imltdatabse = new ImltAjax();

require_once IMLT_DIR_PATH . 'classes/ImltEnviroment.php';
$imltdatabse = new ImltEnviroment();

require_once IMLT_DIR_PATH . 'classes/ImltTrackingActiveUsers.php';
$ImltTrackingActiveUsers = new ImltTrackingActiveUsers();

require_once IMLT_DIR_PATH . 'classes/ImltTestSpeed.php';
$ImltTestSpeed = new ImltTestSpeed();
