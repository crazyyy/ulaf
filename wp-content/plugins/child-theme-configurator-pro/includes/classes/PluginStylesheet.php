<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* 
 * This file and all accompanying files (C) 2014-2020 Lilaea Media LLC except where noted. See license for details.
 */
class ChildThemeConfiguratorPluginStylesheet {
    
    static function parse_stylesheet(){
        // check for old ctc-pro stylesheet hook
        if ( function_exists( 'chld_thm_cfg_plugin_css' ) )
            echo 'HAS_PLUGIN_STYLESHEET' . LF;
    }
    
    static function remove_plugin_stylesheet_action(){
        $marker = 'CTC ENQUEUE PLUGIN ACTION';
        $code = array();
        if ( $filename = ChildThemeConfiguratorCore::ctc()->css()->is_file_ok( 
            ChildThemeConfiguratorCore::ctc()->css()->get_child_target( 'functions.php' ), 'write' ) ):
            if ( FALSE !== ChildThemeConfiguratorCore::ctc()->insert_with_markers( $filename, $marker, $code, FALSE, TRUE ) ):
                ChildThemeConfiguratorCore::ctc()->debug( 'Plugin stylesheet action removed', __FUNCTION__ );
                return;
            endif;
        endif;
        ChildThemeConfiguratorCore::ctc()->debug( 'Could not remove plugin stylesheet action.', __FUNCTION__ );
    }
    static function parse_plugin_stylesheet_to_target() {
        ChildThemeConfiguratorCore::ctc()->css()->parse_css_file( 'child', 'ctc-plugins.css' );
    }
    static function delete_plugin_stylesheet(){
        ChildThemeConfiguratorCore::ctc()->delete_child_file( 'ctc-plugins', 'css' );
    }
}

