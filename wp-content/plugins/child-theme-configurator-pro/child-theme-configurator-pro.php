<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Plugin Name: Child Theme Configurator Pro
    Plugin URI: http://www.childthemeplugin.com/child-theme-configurator-pro/
    Description: Adds plugin stylesheets and other additional functionality to Child Theme Configurator
    Version: 2.3.8
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: child-theme-configurator
    Domain Path: /lang
  
    This file and all accompanying files (C) 2014-2020 Lilaea Media LLC except where noted. See license for details.
*/

// Pro constants
defined( 'LF' ) or define( 'LF', "\n" );
define( 'CHLD_THM_CFG_PRO_VERSION', '2.3.8' );
define( 'CHLD_THM_CFG_PLUGINS_MENU', 'ctc-plugins' );
define( 'CHLD_THM_CFG_PRO_DIR', dirname( __FILE__ ) );
define( 'CHLD_THM_CFG_PRO_URL', plugin_dir_url( __FILE__ ) );
define( 'CHLD_THM_CFG_PRO_FILE', __FILE__ );

// activate autoloader
spl_autoload_register( 'chld_thm_cfg_pro_autoload' );

function chld_thm_cfg_pro_autoload( $class ) {
    $base = str_replace( 'ChildThemeConfigurator', '', $class );
    $file = dirname( __FILE__ ) . '/includes/classes/' . $base . '.php';
    if ( file_exists( $file ) )
        include_once( $file );
}     

// Initialize
if ( is_admin() ):
    // only initialize CTCP if in admin
    add_action( 'plugins_loaded', 'ChildThemeConfiguratorCore::init' );

    // clean out options with uninstall
    register_uninstall_hook( __FILE__, 'chld_thm_cfg_pro_uninstall' );
endif;

// handle CTC Preview
function chld_thm_cfg_pro_preview(){
    // replace core preview function with CTCP function for quick preview
    if ( isset( $_GET[ 'preview_ctc' ] ) ):
        if ( isset( $_GET[ 'preview_iframe' ] ) ):
            add_action ( 'init', 'ChildThemeConfiguratorPreview::maybe_cache_menus' );
            // start output buffer with preview filter
            ob_start( 'ChildThemeConfiguratorPreview::preview_filter' );
        endif;
        remove_action( 'setup_theme', 'preview_theme' );
        add_action( 'chld_thm_cfg_parse_stylesheet', 'ChildThemeConfiguratorPluginStylesheet::parse_stylesheet' );
        new ChildThemeConfiguratorPreview();
    endif;
}

add_action( 'plugins_loaded', 'chld_thm_cfg_pro_preview' );

// append timestamp to linked stylesheets to force cache refresh
add_filter( 'style_loader_src', 'ChildThemeConfiguratorCore::filter_version', 10, 2 );

// cleanup options table
function chld_thm_cfg_pro_uninstall() {
    delete_site_option( 'chld_thm_cfg_options' );
    delete_site_option( 'chld_thm_cfg_options_plugins_configvars' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_qs' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_sel' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_query' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_rule' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_val' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_seq' );
    delete_site_option( 'chld_thm_cfg_options_plugins_dict_token' );
    delete_site_option( 'chld_thm_cfg_options_plugins_sel_ndx' );
    delete_site_option( 'chld_thm_cfg_options_plugins_val_ndx' );
    //from main
    delete_site_option( 'chld_thm_cfg_options_configvars' );
    delete_site_option( 'chld_thm_cfg_options_dict_qs' );
    delete_site_option( 'chld_thm_cfg_options_dict_sel' );
    delete_site_option( 'chld_thm_cfg_options_dict_query' );
    delete_site_option( 'chld_thm_cfg_options_dict_rule' );
    delete_site_option( 'chld_thm_cfg_options_dict_val' );
    delete_site_option( 'chld_thm_cfg_options_dict_seq' );
    delete_site_option( 'chld_thm_cfg_options_dict_token' );
    delete_site_option( 'chld_thm_cfg_options_sel_ndx' );
    delete_site_option( 'chld_thm_cfg_options_val_ndx' );
}

    
