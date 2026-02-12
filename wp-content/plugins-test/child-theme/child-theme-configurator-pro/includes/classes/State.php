<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Class: ChildThemeConfiguratorState
    License: GPLv2
    Copyright (C) 2014-2020 Lilaea Media
*/


class ChildThemeConfiguratorState {
    
    var $state;
    var $packer;
    var $dict_seq;
    var $val_ndx;
    var $empty; // id of empty property value ( '' );
    
    function __construct(){
        if ( !( $this->state = get_user_meta( get_current_user_id(), 'ctc_user_state', TRUE ) ) ):
            // defaults
            $this->state = array(
                'mode'              => 'frontend',   // vs. 'editor'
                'target'            => 'stylesheet', // vs. 'inline'
                'theme'             => NULL, // 
                'debug'             => 0,
                'rec_stylesheet'    => array(),
                'rec_inline'        => array(),
                'rec_editor'        => array(),
            );
            $this->maybe_convert_data();
        endif;
    }
    
    private function _unpack( $packed ){
        $this->packer()->reset( $this->packer()->decode( $packed ) );
        return $this->packer()->unpack();
    }
    
    /**
     * Verifies that submitted form matches current user state.
     */
    function fingerprint_is_valid(){
        return ( isset( $_POST[ 'ctc_fingerprint' ] ) && $_POST[ 'ctc_fingerprint' ] == $this->get_fingerprint() );
    }
    
    function get_css_fingerprint(){
        return get_option( CHLD_THM_CFG_OPTIONS . '_hash_' . $this->get_property( 'theme' ) );
    }

    function get_empty(){
        if ( !isset( $this->empty ) )
            $this->empty = ChildThemeConfiguratorCore::ctc()->css()->get_dict_id( 'val', '' );
        return $this->empty;
    }
    
    function get_fingerprint(){
        $token = implode( 
            '|', array(
                $this->get_property( 'theme' ),
                $this->get_property( 'mode' ),
                $this->get_property( 'target' ),
                get_current_user_id()
            )
        );
            
        return md5( $token );
    }
    
    function get_fingerprint_field(){
        return '<input type="hidden" id="ctc_fingerprint" name="ctc_fingerprint" value="' . $this->get_fingerprint() . '" />' . PHP_EOL;
    }
    
    function get_inline( $qsid ){
        return $this->has_inline( $qsid ) ? $this->val_ndx[ $qsid ] : FALSE;
    }
    
    function get_inline_css(){
        return wp_get_custom_css( $this->get_property( 'theme' ) );
    }
    
    function get_property( $property ){
        return isset( $this->state[ $property ] ) && $this->state[ $property ] ? $this->state[ $property ] : FALSE;
    }
    
    function get_recent( $mode = 'stylesheet' ){
        return (array) $this->get_property( 'rec_' . $mode );
    }
    
    function has_inline( $qsid ){
        return isset( $this->val_ndx[ $qsid ] );
    }
    
    function is_inline(){
        return ( 'frontend' == $this->get_property( 'mode' ) && 'inline' == $this->get_property( 'target' ) );
    }
        
    function load_state( $key = NULL ){
        if ( !$key ) 
            $key = 'configvars';
        if ( $theme = $this->get_property( 'theme' ) ):
            // theme, mode and target must be set
            $mode = $this->get_property( 'mode' );
            if ( 'dict_seq' == $key || 'val_ndx' == $key ):
                // qs, seq and val_ndx are unique for frontend-stylesheet, frontend-custom and editor-stylesheet
                $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_' . $mode . '_' . $key;
                // this works for both standard and multisite WordPress
                $data = get_site_option( $option );
                if ( $this->is_inline() ):
                    //ChildThemeConfiguratorCore::ctc()->debug( 'loading inline: ' . $key, __FUNCTION__, __CLASS__ );
                    $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_inline_' . $key;
                    // load inline css styles from options table, not site options for in multisite
                    if ( !( $this->{ $key } = get_option( $option ) ) ):
                        $this->{ $key } = array();
                    endif;
                endif;
            else:
                $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_' . $key;
                // query, selector, rule, value and configvars are shared for all modes.
                // this works for both standard and multisite WordPress
                $data = get_site_option( $option );
            endif;
        endif;
        if ( empty( $data ) ):
            // fallback: load old options
            $data = $this->maybe_convert_data();
        endif;
        
        return $data;
    }
    
    /**
     * as of v.2.3.0, CTC supports theme-specific custom css and editor stylesheets
     * to manage this new option records are used. This function gracefully converts
     * the old option to the new format.
     */
    function maybe_convert_data(){
        if ( $config = get_site_option( CHLD_THM_CFG_OPTIONS . '_configvars' ) ):
            $this->set_property( 'theme', $config[ 'child' ] );
            $this->save_state( 'configvars', $config );
            foreach( array( 'dict_query', 'dict_sel', 'dict_qs', 'dict_rule', 'dict_val', 'val_ndx', 'dict_seq' ) as $key ):
                if ( FALSE !== ( $data = get_site_option( CHLD_THM_CFG_OPTIONS . '_' . $key ) ) ):
                    $this->save_state( $key, $data );
                    delete_site_option( CHLD_THM_CFG_OPTIONS . '_' . $key );
                    delete_site_option( CHLD_THM_CFG_OPTIONS . '_plugins_' . $key );
                endif;
            endforeach;
            // cleanup legacy trash
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_configvars' );
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_plugins_configvars' );
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_dict_token' );
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_plugins_dict_token' );
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_sel_ndx' );
            delete_site_option( CHLD_THM_CFG_OPTIONS . '_plugins_sel_ndx' );
            delete_option( CHLD_THM_CFG_OPTIONS . '_debug' );

        endif;
        return $config;
    }
    
    /**
     * for inline (customizer) mode, use inline value for child.
     * if child value exists in stylesheet data, use it for parent.
     * Otherwise, use original parent value.
     */
    function merge_data( $unpacked, $qsid ){
        //ChildThemeConfiguratorCore::ctc()->debug( 'unpacked: ' . $qsid . ' -> ' . print_r( $unpacked, TRUE ), __FUNCTION__, __CLASS__ );
        if ( $packed = $this->get_inline( $qsid ) )
            $inline = $this->_unpack( $packed );
        else
            $inline = array();
        
        //ChildThemeConfiguratorCore::ctc()->debug( 'inline: ' . $qsid . ' -> ' . print_r( $inline, TRUE ), __FUNCTION__, __CLASS__ );

        // move child values to parent values if they exist        
        if ( $unpacked ):
            foreach ( $unpacked as $ruleid => $values ):
                if ( isset( $values[ 'c' ] ) ):
                    $index = 0;
                    foreach( $values[ 'c' ] as $valarr ):
                        if ( $valarr[ 0 ] != $this->get_empty() )
                            $unpacked[ $ruleid ][ 'p' ][ $index ] = $valarr;
                        $index++;
                    endforeach;
                    unset( $unpacked[ $ruleid ][ 'c' ] );
                endif;
            endforeach;
        endif;
         
        // assign inline value to child value
        foreach ( $inline as $ruleid => $values )
            $unpacked[ $ruleid ][ 'c' ] = $values[ 'c' ];
        
        //ChildThemeConfiguratorCore::ctc()->debug( 'merged: ' . $qsid . ' -> ' . print_r( $unpacked, TRUE ), __FUNCTION__, __CLASS__ );
        return $unpacked;
    }
    
    function pack_data( $raw, $qsid, $current ){
        $packed = $this->packer()->encode( $this->packer()->pack( $raw ) );
        if ( $this->is_inline() ):
            $this->val_ndx[ $qsid ] = $packed;
            return $current;
        endif;
        return $packed;
    }
    
    function packer(){
        if ( !isset( $this->packer ) )
            $this->packer = new ChildThemeConfiguratorPacker();
        return $this->packer;
    }
    
    function parse_inline(){
        ChildThemeConfiguratorCore::ctc()->debug( 'parsing inline', __FUNCTION__, __CLASS__ );
        $this->val_ndx = array();
        ChildThemeConfiguratorCore::css()->styles = $this->get_inline_css();
        // $template, $basequery = NULL, $parse_imports = TRUE, $relpath = '', $reset = FALSE
        ChildThemeConfiguratorCore::css()->parse_css( 'child', 'base', FALSE, '', TRUE );
    }
    
    /**
     * Most CTC data is saved to global tables for multisite WordPress.
     * However, custom css must be saved to specific site tables in multisite WordPress.
     * Only the CTC data that varies between modes needs to be saved this way.
     * This is handled by using update_option instead of update_site_option
     * for qs, seq and val_ndx dictionary data for multisite WordPress when in custom css mode.
     */
    function save_state( $key, $value ){
        $theme  = $this->get_property( 'theme' );
        $mode   = $this->get_property( 'mode' );
        if ( 'dict_seq' == $key || 'val_ndx' == $key ):
            if ( $this->is_inline() ):
                $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_inline_' . $key;
                update_option( $option, $this->{ $key }, FALSE ); 
            endif;
            // seq and val_ndx are unique for frontend-stylesheet, frontend-custom and editor-stylesheet
            $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_' . $mode . '_' . $key;
        else:
            // qs, query, selector, rule, value and configvars are shared for all modes.
            $option = CHLD_THM_CFG_OPTIONS . '_' . $theme . '_' . $key;
        endif;
        
        if ( is_multisite() ):
            // Use global options for multisite WordPress
            update_site_option( $option, $value ); 
        else:
            /**
             * Use update_option to save in site options for mode-specific data when in custom css mode 
             * or if this is not multisite WordPress.
             * Also, passing false to update_site_option only works if value changes so we must use
             * update_option to disable autoloading.
             */
            update_option( $option, $value, FALSE ); 
        endif;
    }
    
    function set_css_fingerprint( $hash ){
        $option = CHLD_THM_CFG_OPTIONS . '_hash_' . $this->get_property( 'theme' );
        ChildThemeConfiguratorCore::ctc()->debug( 'setting css fingerprint: ' . $option . ' -> ' . $hash, __FUNCTION__, __CLASS__ );
        update_option( $option, $hash, FALSE );
    }
    
    function set_property( $property, $value ){
        if ( in_array( $property, array( 'mode', 'target', 'theme', 'debug', 'rec_stylesheet', 'rec_inline', 'rec_editor' ) ) 
            && ( $id = get_current_user_id() ) ):
            $this->state[ $property ] = $value;
            update_user_meta( $id, 'ctc_user_state', $this->state );
        endif;
    }

    function set_recent( $mode = 'stylesheet', $recent ){
        $this->set_property( 'rec_' . $mode, (array) $recent );
    }
    
    function unpack_data( $packed, $qsid ){
        if ( $packed )
            $unpacked = $this->_unpack( $packed );
        else 
            $unpacked = array();
        
        if ( $this->is_inline() )
            $unpacked = $this->merge_data( $unpacked, $qsid );

        return empty( $unpacked ) ? FALSE : $unpacked;
    }
    
    function update_inline_css( $css ){
        ChildThemeConfiguratorCore::ctc()->debug( 'updating inline', __FUNCTION__, __CLASS__ );
        if ( !$this->is_inline() ):
            ChildThemeConfiguratorCore::ctc()->debug( 'not inline, returning', __FUNCTION__, __CLASS__ );
            return;
        endif;
        $theme = $this->get_property( 'theme' );
        // write css to customizer data
        $r = wp_update_custom_css_post( $css, array(
            'stylesheet' => $theme
        ) );
        if ( $r instanceof WP_Error )
            return false;
        
        // set id in child theme mods
        $post_id = $r->ID;
        $mods = get_option( 'theme_mods_' . $theme );
        $mods[ 'custom_css_post_id' ] = $post_id;
        update_option( 'theme_mods_' . $theme, $mods );

        // set fingerprint to check for modifications since last update
        $this->set_css_fingerprint( md5( $css ) );
    }

    function validate_post( $action, $noncefield, $cap ){
        // security: request must be post, user must have permission, referrer must be local and nonce must match.
        // Added fingerprint to make sure user state matches form.
        file_put_contents( 'validate.txt', $action . ' ' . $noncefield . ' ' . $cap . print_r( $_POST, TRUE ) . "\n", FILE_APPEND );
        return ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ]
            && current_user_can( $cap ) // ( 'edit_themes' )
            && ( wp_doing_ajax() ? check_ajax_referer( $action, $noncefield, FALSE ) : 
                check_admin_referer( $action, $noncefield, FALSE ) )
            && $this->fingerprint_is_valid() );
    }
    
}
