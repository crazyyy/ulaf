<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

// load installation functions.
require_once WPDT_ABSPATH . 'includes/class-wpdt-installation.php';

// load admin user interface.
if ( is_admin() && ! defined( 'DOING_CRON' ) && ! wp_doing_ajax() && ! defined( 'REST_REQUEST' ) ) {

	include_once WPDT_ABSPATH . 'includes/admin/class-wpdt-admin.php';
}

foreach ( glob( WPDT_ABSPATH . 'includes/admin/*.php' ) as $filename ) {
	include_once $filename;
}

foreach ( glob( WPDT_ABSPATH . 'includes/admin/settings/*.php' ) as $filename ) {
	include_once $filename;
}

// load composer autoload file.
require_once WPDT_ABSPATH . 'vendor/autoload.php';

// load debug quick look feature.
require_once WPDT_ABSPATH . 'includes/admin/settings/class-wpdt-debug-reader.php';
