<?php

/**
 * Version: 1.0.0
 * Plugin Name: Plugin Organiser
 * Plugin URI: https://innocow.com
 * Description: Organises your plugins into groups. You can set up the groups in the settings menu.
 * Author: Innocow
 * Author URI: http://innocow.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: icwppo
 * Domain Path: /lang
 * 
 * Requires at least: 5.2
 * Requires PHP: 5.4
 **/

if ( ! defined( "ABSPATH" ) ) { exit; }

$translate = __( 
    "Organises your plugins into groups. You can set up the groups in the settings menu.", 
    "icwppo" 
);

//
// Plugin start
//

try {

    require( __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php" );

    icwppo_modify_autoloader( __FILE__, "Innocow\\Plugin_Organiser" );

    $Plugin_Organiser = \Innocow\Plugin_Organiser\Plugin_Organiser::get_instance();
    $Plugin_Organiser->prepare_plugin_variables( __FILE__, WP_PLUGIN_DIR );
    $Plugin_Organiser->set_rest_namespace_version( "1" );
    $Plugin_Organiser->set_plugin_code( "icwppo" );
    $Plugin_Organiser->set_translation_key( "icwppo" );

    $GLOBALS["ICWPPO"] = $Plugin_Organiser;

    add_action( "plugins_loaded", function() {

        $Plugin_Organiser = $GLOBALS["ICWPPO"];
        $Plugin_Organiser->queue_hooks();

        if ( is_admin() || icwppo_is_rest_context() ) {
            $Plugin_Organiser->queue_hooks_rest();
        }

    } );

} catch( \Exception $e ) {
    // Note, the above try block only catches errors during the plugin
    // initialisation. Any subsequent hooks (like REST API paths) aren't
    // caught here.
    icwppo_init_exception_handler( $e );
}

