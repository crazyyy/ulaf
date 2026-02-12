<?php
/**
* Plugin Name: Setup Default Featured Image
* Description: Setup Default feature image base on post.
* Version: 1.3
* Copyright: 2022
* Text Domain: default-feature-image
*/
if ( ! defined( 'ABSPATH' ) ) {
        die();
}
/* All constants should be defined in this file. */
if ( ! defined( 'WDFI_PLUGINDIR' ) ) {
        define( 'WDFI_PLUGINDIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WDFI_PLUGINBASENAME' ) ) {
        define( 'WDFI_PLUGINBASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'WDFI_PLUGINURL' ) ) {
        define( 'WDFI_PLUGINURL', plugin_dir_url( __FILE__ ) );
}
/* Auto-load all the necessary classes. */
if( ! function_exists( 'wdfi_class_auto_loader' ) ) {
        function wdfi_class_auto_loader( $class ) {
                $includes = WDFI_PLUGINDIR . 'includes/' . $class . '.php';
                if( is_file( $includes ) && ! class_exists( $class ) ) {
                        include_once( $includes );
                        return;
                }
        }
}
spl_autoload_register('wdfi_class_auto_loader');
new WDFI_Cron();
new WDFI_Admin();
new WDFI_Frontend();