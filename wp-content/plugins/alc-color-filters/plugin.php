<?php
/*
Plugin Name: Alchemists Color Filters for WooCommerce
Plugin URI: https://github.com/danfisher85/alc-color-filters
Description: Filter WooCommerce products by color from a sidebar widget.
Author: Dan Fisher
Author URI: https://github.com/danfisher85/alc-color-filters
Version: 1.0.7
Text Domain: alc-color-filters
WC requires at least: 3.4
WC tested up to: 4.1
Domain Path: /languages/
*/

define( 'CF_VERSION', '1.0.7' );
define( 'CF_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'CF_INCLUDES_PATH', CF_PLUGIN_PATH . '/includes' );
define( 'CF_PLUGIN_FOLDER', basename( CF_PLUGIN_PATH ) );
define( 'CF_PLUGIN_URL', plugins_url() . '/' . CF_PLUGIN_FOLDER );

require CF_PLUGIN_PATH . '/color-filters.php';
require CF_INCLUDES_PATH . '/widgets.php';

$color_filters = new NM_Color_Filters();

// Install plugin
register_activation_hook( __FILE__, array( $color_filters, 'install' ) );
