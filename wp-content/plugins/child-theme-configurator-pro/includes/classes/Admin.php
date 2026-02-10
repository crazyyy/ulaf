<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Class: ChildThemeConfiguratorAdmin
    Copyright (C) 2014-2020 Lilaea Media
*/
class ChildThemeConfiguratorAdmin {

    // state
    var $state;
    var $genesis;
    //var $reorder;
    var $processdone;
    var $childtype;
    var $template;
    var $is_ajax;
    var $is_get;
    var $is_post;
    var $skip_form;
    var $fs;
    var $encoding;

    var $fs_prompt;
    var $fs_method;
    var $uploadsubdir;
    var $menuName; // backward compatibility with plugin extension
    var $cache_updates  = TRUE;
    var $debug;
    var $is_debug;
    var $refresh;
    var $copy_mods; // copy theme options from and to 
    var $msg; // passed via 'updated' query var
    // memory checks
    var $max_sel;
    var $sel_limit;
    var $mem_limit;
    // state arrays
    var $themes;
    var $errors         = array();
    var $files          = array();
    var $updates        = array();
    // config arrays
    var $postarrays     = array(
        'ctc_img',
        'ctc_file_parnt',
        'ctc_file_child',
        'ctc_additional_css',
    );
    var $configfields   = array(
        'theme_parnt', 
        'child_type', 
        'theme_child', 
        'child_template', 
        'child_name',
        'child_themeuri',
        'child_author',
        'child_authoruri',
        'child_descr',
        'child_tags',
        'child_version',
        'repairheader',
        'ignoreparnt',
        'handling',
        'enqueue',
        'sepstylesheet',
        'configtype', // backward compatability - no longer used
    );
    var $actionfields   = array(
        'load_styles',
        'parnt_templates_submit',
        'child_templates_submit',
        'image_submit',
        'theme_image_submit',
        'theme_screenshot_submit',
        'export_child_zip',
        'export_theme',
        'reset_permission',
        'templates_writable_submit',
        'set_writable',
        'upgrade',
        'data_reload'
    );
    var $imgmimes       = array(
        'jpg|jpeg|jpe'  => 'image/jpeg',
        'gif'           => 'image/gif',
        'png'           => 'image/png',
    );

    
    var $plugindir;
    var $options        = array();
    var $plugins;
    // var $pluginmode     = FALSE; // v.2.3.0 - no longer using pluginmode
    var $recent_count   = 50; // number of selectors to show
    var $ext;
    var $menus          = array();
    var $preview_mods   = FALSE;
    var $filetypes      = array(
        'php',
        'js',
        'css',
        'txt',
    );

    
    function __construct() {
        
        /**
         * BEGIN old Admin
         */
        $this->processdone  = FALSE;
        $this->genesis      = FALSE;
        //$this->reorder      = FALSE;
        $this->refresh       = FALSE;
        $this->encoding     = WP_Http_Encoding::is_available();
        $this->menuName     = CHLD_THM_CFG_MENU; // backward compatability for plugins extension
        $this->is_post      = ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] );
        $this->is_get       = ( 'GET' == $_SERVER[ 'REQUEST_METHOD' ] );
        $this->debug        = '';
        $this->errors       = array();
        $this->is_debug     = $this->state()->get_property( 'debug' );
        /**
         * END old Admin
         */

        $this->plugindir                                = dirname( CHLD_THM_CFG_PRO_DIR );
        $options                                        = get_site_option( CHLD_THM_CFG_OPTIONS );
        $options = (array) $options;                    // type mismatch from older version
        // prune any options from prior versions
        $this->options[ 'update_key' ]                  = isset( $options[ 'update_key' ] ) ? $options[ 'update_key' ]  : '';
        $this->options[ 'addl_css' ]                    = isset( $options[ 'addl_css' ] ) ? $options[ 'addl_css' ]  : array();

        // filter hooks 
        add_filter( 'chld_thm_cfg_header',              array( $this, 'get_ctc_header' ) );
        add_filter( 'chld_thm_get_prop',                array( $this, 'get_prop' ), 10, 3 );
        add_filter( 'chld_thm_cfg_parnt',               array( $this, 'get_parent' ), 10, 2 );
        add_filter( 'chld_thm_cfg_css_header',          array( $this, 'get_css_header' ), 10, 2 );
        add_filter( 'chld_thm_cfg_update_msg',          array( $this, 'get_update_msg' ), 10, 2 );

        add_filter( 'chld_thm_cfg_localize_array',      array( $this, 'localize_array' ) );
        add_filter( 'wp_theme_editor_filetypes',        array( $this, 'editor_filetypes' ), 10, 2 );

        // action hooks
        add_action( 'chld_thm_cfg_cache_updates',       array( $this, 'cache_updates' ), 10, 2 );
        add_action( 'chld_thm_cfg_update_qsid',         array( $this, 'update_qsid' ), 10, 2 );
        add_action( 'chld_thm_cfg_tabs',                array( $this, 'render_addl_tabs' ), 1, 4 );
        add_action( 'chld_thm_cfg_panels',              array( $this, 'render_addl_panels' ), 1, 4 );
        add_action( 'chld_thm_cfg_sidebar',             array( $this, 'render_sidebar' ) );
        add_action( 'chld_thm_cfg_forms',               array( $this, 'process_file_form' ), 10, 2 );
        add_action( 'chld_thm_cfg_files_tab',           array( $this, 'render_file_form' ), 10, 2 );
        add_action( 'chld_thm_cfg_file_form_buttons',   array( $this, 'render_file_form_buttons' ), 20, 1 );
        add_action( 'chld_thm_cfg_copy_theme_mods',     array( $this, 'copy_premium_theme_mods' ), 10, 2 );
    }

    function add_base_files( $obj ){
        //$this->debug( LF . LF, __FUNCTION__, __CLASS__ );
        // add functions.php file
        $contents = "<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
";
        $handling = $this->get( 'handling' );
        $this->write_child_file( 'functions.php', $contents );
        $this->backup_or_restore_file( 'style.css' );
        $contents = $this->css()->get_css_header_comment( $handling );
        $this->debug( 'writing initial stylesheet header...' . LF . $contents, __FUNCTION__, __CLASS__ );
        $this->write_child_file( 'style.css', $contents );
        if ( 'separate' == $handling ):
            $this->backup_or_restore_file( $this->get_child_stylesheet() );
            $this->write_child_file( $this->get_child_stylesheet(), $contents . LF );
        endif;
    }
    
    function ajax_analyze() {
        $this->is_ajax = TRUE;
        // do_action( 'chld_thm_cfg_pluginmode' ); // v.2.3.0 - no longer using pluginmode
        if ( $this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ): 
            $analysis = new ChildThemeConfiguratorAnalysis();
            $analysis->fetch_page();
            die( json_encode( $analysis->get_analysis() ) );
        endif;
        die( 0 );
    }
    
    /**
     * ajax callback to dismiss update key notice 
     */
    function ajax_dismiss_key_notice() {
        $this->is_ajax = TRUE;
        if ( $this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ):
            update_user_meta( get_current_user_id(), 'chld_thm_cfg_key_notice' , 1 );
            $this->updates[] = array(
                'key'   => '',
                'obj'   => 'dismiss',
                'data'  => 'key_notice',
            );
            die( json_encode( $this->updates ) );
        endif;
        die( 0 );
    }

    /**
     * old ctc - ajax callback to dismiss upgrade notice 
     */
    
    function ajax_dismiss_notice( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        if ( $this->validate_post( $action ) ):
            update_user_meta( get_current_user_id(), 'chld_thm_cfg_upgrade_notice' , CHLD_THM_CFG_VERSION );
            $this->updates[] = array(
                'key'   => '',
                'obj'   => 'dismiss',
                'data'  => CHLD_THM_CFG_VERSION,
            );
            die( json_encode( $this->updates ) );
        endif;
        die( 0 );
    }

    /**
     * ajax callback to query config data 
     */
    function ajax_query_css( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        if ( $this->validate_post( $action ) ):
            //if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' ); // v.2.3.0 - no longer using pluginmode
            $this->load_config();
            add_action( 'chld_thm_cfg_cache_updates', array( $this, 'cache_debug' ) );
            $regex = "/^ctc_query_/";
            foreach( preg_grep( $regex, array_keys( $_POST ) ) as $key ):
                $name = preg_replace( $regex, '', $key );
                $param[ $name ] = sanitize_text_field( $_POST[ $key ] );
            endforeach;
            $this->debug( 'ajax params: ' . print_r( $param, TRUE ), __FUNCTION__, __CLASS__, __CLASS__ );
            if ( !empty( $param[ 'obj' ] ) ):
                // add any additional updates to pass back to browser
                $this->updates[] = array(
                    'key'   => isset( $param[ 'key' ] ) ? $param[ 'key' ] : '',
                    'obj'   => $param[ 'obj' ],
                    'data'  => $this->get( $param[ 'obj' ], $param ),
                );
                do_action( 'chld_thm_cfg_cache_updates' );
                die( json_encode( $this->updates ) );
            endif;
        endif;
        die( 0 );
    }
    
    /**
     * ajax callback for saving form data 
     */
    function ajax_save_postdata( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        $this->debug( 'ajax save ', __FUNCTION__, __CLASS__ );
        // security check
        if ( $this->validate_post( $action ) ):
            // if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' ); // v.2.3.0 - no longer using pluginmode
            $this->verify_creds(); // initialize filesystem access
            add_action( 'chld_thm_cfg_cache_updates', array( $this, 'cache_debug' ) );
            // get configuration data from options API
            if ( FALSE !== $this->load_config() ): // sanity check: only update if config data exists
                if ( isset( $_POST[ 'ctc_is_debug' ] ) ):
                    // toggle debug
                    $this->toggle_debug();
                else:
                    if( isset( $_POST[ 'ctc_copy_mods' ] ) ):
                        // copy menus, widgets and other customizer options from parent to child if selected
                        if ( isset( $_POST[ 'ctc_copy_from' ] ) && isset( $_POST[ 'ctc_copy_to' ] ) ):
                            $this->debug( 'Copy Theme Mods on resubmit', __FUNCTION__, __CLASS__ );
                            $from   = sanitize_text_field( $_POST[ 'ctc_copy_from' ] );
                            $to     = sanitize_text_field( $_POST[ 'ctc_copy_to' ] );
                            $this->copy_theme_mods( $from, $to );
                        else:
                            $this->debug( 'Copy Theme Mods passed but missing to and from values', __FUNCTION__, __CLASS__ );
                        endif;
                        if ( $this->cache_updates ):
                            $this->updates[] = array(
                                'obj'  => 'copy_mods',
                                'data' => array(),
                            );
                        endif;
                    endif;
                    
                    if ( isset( $_POST[ 'ctc_analysis' ] ) ): // process ANALYZER SIGNAL inputs
            
                        if ( $this->cache_updates ):
                            $this->updates[] = array(
                                'obj'  => 'analysis',
                                'data' => array(),
                            );
                        endif;

                        $this->evaluate_signals();
                    endif;
                    $this->css()->parse_post_data(); // parse any passed values
                    // if child theme config has been set up, save new data
                    // return recent edits and selected stylesheets as cache updates
                    if ( $this->get( 'child' ) ):
                        // hook for add'l plugin files and subdirectories
                        do_action( 'chld_thm_cfg_addl_files' );


                        $this->css()->write_css();
                    endif;
                    // update config data in options API
                    $this->save_config();
                endif;
                // add any additional updates to pass back to browser
                do_action( 'chld_thm_cfg_cache_updates' );
            endif;
            // send all updates back to browser to update cache
            die( json_encode( $this->css()->obj_to_utf8( $this->updates ) ) );
        endif;
        
        die();
    }
    
    function backup_or_restore_file( $source, $restore = FALSE, $target = NULL ){
        $action = $restore ? 'Restore' : 'Backup';
        $this->debug( LF . LF . $action . ' main stylesheet...', __FUNCTION__, __CLASS__ );
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access, returning', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        list( $base, $suffix ) = explode( '.', $source );
        if ( empty( $target ) )
            $target = $base . '.ctcbackup.' . $suffix;
        if ( $restore ):
            $source = $target;
            $target = $base . '.' . $suffix;        
        endif;
        $fstarget = $this->fspath( $this->css()->get_child_target( $target ) );
        $fssource = $this->fspath( $this->css()->get_child_target( $source ) );
        global $wp_filesystem;
        if ( ( !$wp_filesystem->exists( $fssource ) ) || ( !$restore && $wp_filesystem->exists( $fstarget ) ) ):
            $this->debug( 'No stylesheet, returning', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        if ( $wp_filesystem->copy( $fssource, $fstarget, FS_CHMOD_FILE ) ):
            $this->debug( 'Filesystem ' . $action . ' successful', __FUNCTION__, __CLASS__ );
            return TRUE;
        else:
            $this->debug( 'Filesystem ' . $action . ' failed', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
    }
    
    function cache_debug() {
        $this->debug( '*** END OF REQUEST ***', __FUNCTION__, __CLASS__ );
        $this->updates[] = array(
            'obj'   => 'debug',
            'key'   => '',
            'data'  => $this->debug,
        );
    }
    
    function cache_updates() {
        $this->updates[] = array(
            'obj'   => 'recent',
            'key'   => '',
            'data'  => $this->denorm_recent()
        );
    }
            
    function changed_notice() {
        $this->ui()->render_notices( 'changed' );
    }
    
    // case insensitive theme search    
    function check_theme_exists( $theme ) {
        $search_array = array_map( 'strtolower', array_keys( wp_get_themes() ) );
        return in_array( strtolower( $theme ), $search_array );
    }
    
    function clean_slug( $string, $repl = '_' ){
	   return preg_replace( '/[^a-zA-Z0-9_]/', '', preg_replace( "/[\s\-]+/", $repl, strtolower( $string ) ) ); 
    }

    function clone_child_theme( $child, $clone ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        // set child theme if not set for get_child_target to use new child theme as source
        $this->css()->set_prop( 'child', $child );

        $dir        = untrailingslashit( $this->css()->get_child_target( '' ) );
        $themedir   = trailingslashit( get_theme_root() );
        $fsthemedir = $this->fspath( $themedir );
        $files = $this->css()->recurse_directory( $dir, NULL, TRUE );
        $errors = array();
        foreach ( $files as $file ):
            $childfile  = $this->theme_basename( $child, $this->normalize_path( $file ) );
            $newfile    = trailingslashit( $clone ) . $childfile;
            $childpath  = $fsthemedir . trailingslashit( $child ) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            $this->debug( 'Verifying child dir... ', __FUNCTION__, __CLASS__ );
            if ( $this->verify_child_dir( is_dir( $file ) ? $newfile : dirname( $newfile ) ) ):
                if ( is_file( $file ) && !@$wp_filesystem->copy( $childpath, $newpath ) ):
                    $this->errors[] = '15:' . $newpath; //'could not copy ' . $newpath;
                endif;
            else:
                $this->errors[] = '16:' . $newfile; //'invalid dir: ' . $newfile;
            endif;
        endforeach;
    }

    function cmp_theme( $a, $b ) {
        return strcmp( strtolower( $a[ 'Name' ] ), strtolower( $b[ 'Name' ] ) );
    }
        
    /**
     * copy_theme_mods
     * Envato, et. al., use non-standard and inconsistent option fields
     * so we must traverse all permutations of the theme slug to copy
     * them correctly. This is imperfect and leaves some redundant option
     * records, but it works for most non-standard themes.
     */
    function copy_premium_theme_mods( $from, $to ){
        $this->set_premium_theme_mods( $this->get_premium_theme_mods( $from, $to ) );
    }
    
    function config_notice() {
        $this->ui()->render_notices( 'config' );
    }
    
    // converts enqueued path into @import statement for config settings
    function convert_enqueue_to_import( $path ) {
        if ( preg_match( '%(https?:)?//%', $path ) ):
            $this->css()->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
            return;
        endif;
        $regex  = '#^' . preg_quote( trailingslashit( $this->get( 'child' ) ) ) . '#';
        $path   = preg_replace( $regex, '', $path, -1, $count );
        if ( $count ): 
            $this->css()->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
            return;
        endif;
        $parent = trailingslashit( $this->get( 'parnt' ) );
        $regex  = '#^' . preg_quote( $parent ) . '#';
        $path   = preg_replace( $regex, '../' . $parent, $path, -1, $count );
        if ( $count )
            $this->css()->imports[ 'child' ]['@import url(' . $path . ')'] = 1;
    }
    
    // parses @import syntax and converts to wp_enqueue_style statement
    function convert_import_to_enqueue( $import, $count, $execute = FALSE ) {
        $relpath    = $this->get( 'child' );
        $import     = preg_replace( "#^.*?url\(([^\)]+?)\).*#", "$1", $import );
        $import     = preg_replace( "#[\'\"]#", '', $import );
        $path       = $this->css()->convert_rel_url( trim( $import ), $relpath , FALSE );
        $abs        = preg_match( '%(https?:)?//%', $path );
        if ( $execute )
            wp_enqueue_style( 'chld_thm_cfg_ext' . $count,  $abs ? $path : trailingslashit( get_theme_root_uri() ) . $path );
        else
            return "wp_enqueue_style( 'chld_thm_cfg_ext" . $count . "', " 
                . ( $abs ? "'" . $path . "'" : "trailingslashit( get_theme_root_uri() ) . '" . $path . "'" ) . ' );';
    }
    
    function copy_parent_file( $file, $ext = 'php' ) {
        
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        $parent_file = NULL;
        if ( 'screenshot' == $file ):
            foreach ( array_keys( $this->imgmimes ) as $extreg ): 
                foreach( explode( '|', $extreg ) as $ext )
                    if ( $parent_file = $this->css()->is_file_ok( $this->css()->get_parent_source( 'screenshot.' . $ext ) ) ) 
                        break;
                if ( $parent_file ):
                    $parent_file = $this->fspath( $parent_file );
                    break;
                endif;
            endforeach;
            if ( !$parent_file ):
                $this->debug( 'No screenshot found.', __FUNCTION__, __CLASS__ );
                return;
            endif;
        else:
            $parent_file = $this->fspath( $this->css()->is_file_ok( $this->css()->get_parent_source( $file . '.' . $ext ) ) );
        endif;
        
        // get child theme + file + ext ( passing empty string and full child path to theme_basename )
        $child_file = $this->css()->get_child_target( $file . '.' . $ext );
        // return true if file already exists
        if ( $wp_filesystem->exists( $this->fspath( $child_file ) ) ) return TRUE;
        $child_dir = dirname( $this->theme_basename( '', $child_file ) );
        $this->debug( 'Verifying child dir... ', __FUNCTION__, __CLASS__ );
        if ( $parent_file // sanity check
            && $child_file // sanity check
                && $this->verify_child_dir( $child_dir ) //create child subdir if necessary
                    && $wp_filesystem->copy( $parent_file, $this->fspath( $child_file ), FS_CHMOD_FILE ) ):
            $this->debug( 'Filesystem copy successful', __FUNCTION__, __CLASS__ );
            return TRUE;
        endif;
        
        $this->errors[] = '13:' . $parent_file; //__( 'Could not copy file:' . $parent_file, 'child-theme-configurator' );
    }
    
    function copy_screenshot() {
        // always copy screenshot
        $this->copy_parent_file( 'screenshot' ); 
    }
    
    // we can copy settings from parent to child even if neither is currently active
    // so we need cases for active parent, active child or neither
    function copy_theme_mods( $from, $to ) {
        if ( strlen( $from ) && strlen( $to ) ):
            $this->debug( 'copying theme mods from ' . $from . ' to ' . $to, __FUNCTION__, __CLASS__ );
            $mods = $this->get_theme_mods( $from );
        
            // handle custom css
            $r = wp_update_custom_css_post( wp_get_custom_css( $from ), array(
                'stylesheet' => $to
            ) );
        
            // if ok, set id in child theme mods
            if ( !( $r instanceof WP_Error ) )
                $mods[ 'custom_css_post_id' ] = $r->ID;
        
            // set new mods based on parent
            $this->set_theme_mods( $to, $mods );

            // handle randomized custom headers
            foreach ( $this->get_randomized_headers( $from ) as $header )
                add_post_meta( $header, '_wp_attachment_is_custom_header', $to );
            do_action( 'chld_thm_cfg_copy_theme_mods', $from, $to );
        endif;
    }
    
    /**
     * helper function to access CSS object.
     */
    function css( $reset = FALSE ){
        return ChildThemeConfiguratorCore::css( $reset );
    }
    
    /**
     * initialize configurator
     */
    function ctc_page_init () {
        // load config data and validate
        $this->load_config();
        // get all available themes
        $this->get_themes();
        $this->childtype = $this->get_theme_count( 'child' ) ? 'existing' : 'new';
    
        // perform any checks prior to processing config data
        do_action( 'chld_thm_cfg_preprocess' );
        // process any additional forms
        do_action( 'chld_thm_cfg_forms' );  // hook for custom forms
        // process main post data
        $this->process_post();

        // initialize help
        $this->ui()->render_help_content();
    }
    
    function debug( $msg = NULL, $fn = NULL, $cl = NULL ) {
        $str = ( isset( $cl ) ? $cl . '::' : '' ) . ( isset( $fn ) ? $fn . ' -- ' : '' ) . ( isset( $msg ) ? $msg . LF : '' );
        if ( $this->is_debug )
            $this->debug .= $str;
        //@file_put_contents( CHLD_THM_CFG_PRO_DIR . '/ctc_debug_log.txt', $str, FILE_APPEND );
    }
    
    function delete_child_file( $file, $ext = 'php' ) {
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        // verify file is in child theme and exists before removing.
        $file = ( 'img' == $ext ? $file : $file . '.' . $ext );
        if ( $child_file  = $this->css()->is_file_ok( $this->css()->get_child_target( $file ), 'write' ) ):
            if ( $wp_filesystem->exists( $this->fspath( $child_file ) ) ):
                
                if ( $wp_filesystem->delete( $this->fspath( $child_file ) ) ):
                    return TRUE;
                else:
                
                    $this->errors[] = '14:' . $ext; //__( 'Could not delete ' . $ext . ' file.', 'child-theme-configurator' );
                    $this->debug( 'Could not delete ' . $ext . ' file', __FUNCTION__, __CLASS__ );
        
                endif;
            endif;
        endif;
    }
    
    function denorm_recent() {
        $arr = array();
        if ( ( $recent = $this->get_recent() )
            && is_array( $recent ) ):
            foreach ( $recent as $qsid ):
                $selarr = $this->css()->denorm_query_sel( $qsid );
                if (! empty( $selarr ) )
                    $arr[] = array( $qsid => $selarr[ 'selector' ] );
            endforeach;
        endif;
        return $arr;
    }

    function editor_filetypes( $types, $theme = NULL ){
        return $this->filetypes;
    }
    
    function enqueue_notice() {
        $this->ui()->render_notices( 'enqueue' );
    }
    
    /**
     * Generates wp_enqueue_script code block for child theme functions file
     * Enqueues parent and/or child stylesheet depending on value of 'enqueue' setting.
     * If external imports are present, it enqueues them as well.
     */
    function enqueue_parent_code(){
        //$this->debug( print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ), __FUNCTION__, __CLASS__ );
        $imports        = $this->get( 'imports' );
        $enqueues       = array();
        $code           = "// AUTO GENERATED - Do not modify or remove comment markers above or below:" . LF;
        $deps           = $this->get( 'parnt_deps' );
        $enq            = $this->get( 'enqueue' );
        $handling       = $this->get( 'handling' );
        $hasstyles      = $this->get( 'hasstyles' );
        $childloaded    = $this->get( 'childloaded' );
        $parntloaded    = $this->get( 'parntloaded' );
        $cssunreg       = $this->get( 'cssunreg' );
        $csswphead      = $this->get( 'csswphead' );
        $cssnotheme     = $this->get( 'cssnotheme' );
        $ignoreparnt    = $this->get( 'ignoreparnt' );
        $priority       = $this->get( 'qpriority' );
        $maxpriority    = $this->get( 'mpriority' );
        $reorder        = $this->get( 'reorder' );
        $this->debug( 'forcedep: ' . print_r( $this->get( 'forcedep' ), TRUE ) . ' deps: ' . print_r( $deps, TRUE ) . ' enq: ' . $enq . ' handling: ' . $handling
            . ' hasstyles: ' . $hasstyles . ' parntloaded: ' . $parntloaded . ' childloaded: ' . $childloaded . ' reorder: ' . $reorder
            . ' ignoreparnt: ' . $ignoreparnt . ' priority: ' . $priority . ' childtype: ' . $this->childtype, __FUNCTION__, __CLASS__ );
        // add RTL handler
        $code .= "
if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( \$uri ){
        if ( empty( \$uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            \$uri = get_template_directory_uri() . '/rtl.css';
        return \$uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
";
        // enqueue parent stylesheet 
        if ( 'enqueue' == $enq && $hasstyles && !$parntloaded && !$ignoreparnt ):
            // Sanity check: remove dependency to parent css handle to avoid loop v2.3.0
            $deps = array_diff( $deps, array( 'chld_thm_cfg_parent' ) );
            $code .= "
if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array( " . implode( ',', $deps ) . " ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', " . $priority . " );
";
            // if loading parent theme, reset deps and add parent stylesheet
            $deps = array( "'chld_thm_cfg_parent'" );
            
        endif;

        // force a stylesheet dependency if parent is loading out of sequence
        if ( 'separate' != $handling && $childloaded && $reorder && ( $parntloaded || in_array( 'chld_thm_cfg_parent', $deps ) ) ):
            $dephandle = $parntloaded ? $parntloaded : 'chld_thm_cfg_parent';
            $code .= "
if ( !function_exists( 'chld_thm_cfg_add_parent_dep' ) ):
function chld_thm_cfg_add_parent_dep() {
    global \$wp_styles;
    array_unshift( \$wp_styles->registered[ '" . $childloaded . "' ]->deps, '" . $dephandle . "' );
}
endif;
add_action( 'wp_head', 'chld_thm_cfg_add_parent_dep', 2 );
";
        endif;
        $this->debug( 'handling imports: ' . print_r( $imports, TRUE ), __FUNCTION__, __CLASS__ );
        // enqueue external stylesheets (previously used @import in the stylesheet)
        if ( !empty( $imports ) ):
            $ext = 0;
            foreach ( $imports as $import ):
                if ( !empty( $import ) ):
                    $ext++;
                    $enqueues[] = '        ' . $this->convert_import_to_enqueue( $import, $ext ); 
                endif;
            endforeach;
        endif;
        
        // deregister and re-register swaps
        foreach ( $this->get( 'swappath' ) as $sphandle => $sppath ):
            if ( in_array( $sphandle, $this->get( 'noswap' ) ) || !file_exists( trailingslashit( get_template_directory() ) . $sppath ) )
                continue;
            $enqueues[] = "        if ( !file_exists( trailingslashit( get_stylesheet_directory() ) . '" . $sppath . "' ) ):";
            $enqueues[] = "            wp_deregister_style( '" . $sphandle . "' );";
            $enqueues[] = "            wp_register_style( '" . $sphandle . "', trailingslashit( get_template_directory_uri() ) . '" . $sppath . "' );";
            $enqueues[] = "        endif;";
        endforeach;
        
        // if child not loaded, enqueue it and add it to dependencies
        if ( 'separate' != $handling && ( ( $csswphead || $cssunreg || $cssnotheme ) 
            || ( 'new' != $this->childtype && !$childloaded ) 
            ) ): 
            $deps = array_merge( $deps, $this->get( 'child_deps' ) );
            // Sanity check: remove dependency to child css handle to avoid loop v2.3.0
            $deps = array_diff( $deps, array( 'chld_thm_cfg_child' ) );
            $enqueues[] = "        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( " . implode( ',', $deps ) . " ) );";
            // if loading child theme stylesheet, reset deps and add child stylesheet
            $deps = array( "'chld_thm_cfg_child'" );
        endif;
        if ( 'separate' == $handling ):
            $deps = array_merge( $deps, $this->get( 'child_deps' ) );
            // Sanity check: remove dependency to separate css handle to avoid loop v2.3.0
            $deps = array_diff( $deps, array( 'chld_thm_cfg_separate' ) );
            $enqueues[] = "        wp_enqueue_style( 'chld_thm_cfg_separate', trailingslashit( get_stylesheet_directory_uri() ) . '" . $this->get_child_stylesheet() . "', array( " . implode( ',', $deps ) . " ) );";
        endif;
        if ( count( $enqueues ) ):
            $code .= "         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {" . LF;
            $code .= implode( "\n", $enqueues );
            $code .= "
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', " . $maxpriority . " );" . LF;
        endif;
        if ( $ignoreparnt )
            $code .= "
defined( 'CHLD_THM_CFG_IGNORE_PARENT' ) or define( 'CHLD_THM_CFG_IGNORE_PARENT', TRUE );" . LF;
        return explode( "\n", $code ); // apply_filters( 'chld_thm_cfg_enqueue_code_filter', $code ) ); // FIXME?
    }
    
    // updates function file with wp_enqueue_script code block. If getexternals flag is passed function is run in read-only mode
    function enqueue_parent_css( $getexternals = FALSE ) {
        $this->debug( 'enqueueing parent css: getexternals = ' . $getexternals, __FUNCTION__, __CLASS__ );
        $marker  = 'ENQUEUE PARENT ACTION';
        $insertion  =  $this->enqueue_parent_code();
        if ( $filename   = $this->css()->is_file_ok( $this->css()->get_child_target( 'functions.php' ), 'write' ) ):
            $this->insert_with_markers( $filename, $marker, $insertion, $getexternals );
        endif;
    }
    
    
    /**
     * Evaluate signals collected from theme preview and set configuration accordingly
     */
    function evaluate_signals() {
        if ( !isset( $_POST[ 'ctc_analysis' ] ) ) return;
        $analysis   = json_decode( urldecode( $_POST[ 'ctc_analysis' ] ) );
        //die( print_r( $analysis, TRUE ) );
        // stylesheets loaded outside wp_styles queue
        $unregs     = array( 'thm_past_wphead', 'thm_unregistered', 'dep_unregistered', 'css_past_wphead', 'dep_past_wphead' );
        //echo '<pre><code>' . print_r( $analysis, TRUE ) . "</code></pre>\n";
        
        // if this is a self-contained child theme ( e.g., Genesis ) use child as baseline
        $baseline = $this->get( 'ignoreparnt' ) ? 'child' : 'parnt';
        $this->debug( 'baseline: ' . $baseline, __FUNCTION__, __CLASS__ );

        // reset dependency arrays
        $this->css()->parnt_deps  = array();
        $this->css()->child_deps  = array();
        //$this->css()->addl_css    = array(); // v.2.3.0 moved to setup child theme params
        
        // store imported parent stylesheets so they are parsed
        if ( isset( $analysis->parnt->imports ) ):
            foreach ( $analysis->parnt->imports as $import ):
                if ( preg_match( '%(https?:)?//%', $import ) ) continue; // ignore external links
                $this->css()->addl_css[] = sanitize_text_field( $import );
            endforeach;
        endif;

        // store stylesheet dependencies
        if ( isset( $analysis->{ $baseline } ) ):
            if ( isset( $analysis->{ $baseline }->deps ) ):
                foreach ( $analysis->{ $baseline }->deps[ 0 ] as $deparray ):
                    // avoid endless loop from renamed parent link (e.g., WP Rocket)
                    if ( 'chld_thm_cfg_parent' == $deparray[ 0 ] )
                        continue;
                    if ( !in_array( $deparray[ 0 ], $unregs ) ):
                          $this->css()->parnt_deps[] = $deparray[ 0 ];
                    endif;
                    /**
                     * v.2.3.0 - no longer automatically parsing additional stylesheets; only checked options
                     */
                endforeach;
                foreach ( $analysis->{ $baseline }->deps[ 1 ] as $deparray ):
                    // avoid endless loop from renamed child link
                    if ( 'chld_thm_cfg_child' == $deparray[ 0 ] )
                        continue;
                    if ( !in_array( $deparray[ 0 ], $unregs ) ):
                        $this->css()->child_deps[] = $deparray[ 0 ];
                    endif;
                    /**
                     * v.2.3.0 - no longer automatically parsing additional stylesheets; only checked options
                     * 
                    if ( 'separate' == $this->get( 'handling' ) || !empty( $analysis->{ $baseline }->signals->ctc_child_loaded ) ):
                        if ( !preg_match( "/^style.*?\.css$/", $deparray[ 1 ] ) ):
                            //if ( !preg_match( "/bootstrap/", $deparray[ 0 ] ) && !preg_match( "/bootstrap/", $deparray[ 1 ] ) )
                                $this->css()->addl_css[] = sanitize_text_field( $deparray[ 1 ] );
                        endif;
                    endif;
                     */
                endforeach;
            endif;
        endif;
        
        // store parent theme signals ( or child if ignore parent is set )
        if ( isset( $analysis->{ $baseline }->signals ) ):
            $this->css()->set_prop( 'hasstyles', isset( $analysis->{ $baseline }->signals->thm_no_styles ) ? 0 : 1 );
            $this->css()->set_prop( 'csswphead', isset( $analysis->{ $baseline }->signals->thm_past_wphead ) ? 1 : 0 );
            $this->css()->set_prop( 'cssunreg', isset( $analysis->{ $baseline }->signals->thm_unregistered ) ? 1 : 0 );
        endif;
        
        // set queue action priorities
        $this->set_enqueue_priority( $analysis, $baseline );
        
        // tests specific to child theme - v2.2.5: changed to series of if statements because it was unsetting changes in previous block 

        if ( isset( $analysis->child->signals->thm_past_wphead ) )
            $this->css()->set_prop( 'csswphead', 1 );
        if ( isset( $analysis->child->signals->thm_unregistered ) )
            $this->css()->set_prop( 'cssunreg', 1 );
        // special case where theme does not link child stylesheet at all
        if ( isset( $analysis->child->signals->thm_notheme ) )
            $this->css()->set_prop( 'cssnotheme', 1 );
        if ( isset( $analysis->child->signals->thm_child_loaded ) ):
            $this->css()->set_prop( 'childloaded', $analysis->child->signals->thm_child_loaded );
            $this->set_enqueue_priority( $analysis, 'child' );
        else:
            $this->css()->set_prop( 'childloaded',  0 );
        endif;
        // if theme loads parent theme when is_child_theme, add child dependency
        if ( isset( $analysis->child->signals->thm_parnt_loaded ) ):
            $this->css()->set_prop( 'parntloaded',  $analysis->child->signals->thm_parnt_loaded );
            if ( 'thm_unregistered' != $analysis->child->signals->thm_parnt_loaded ):
                array_unshift( $this->css()->child_deps, $analysis->child->signals->thm_parnt_loaded );
            endif;
        else:
            $this->css()->set_prop( 'parntloaded',  0 );
        endif;
            
        // if main styleheet is loading out of sequence, force dependency
        if ( isset( $analysis->child->signals->ctc_parnt_reorder ) )
            $this->css()->set_prop( 'reorder', 1 );
        // roll back CTC Pro Genesis handling option
        if ( isset( $analysis->child->signals->ctc_gen_loaded ) )
            $this->genesis = TRUE;
        
        // added v.2.3.0 to clean up old Pro stylesheet
        if ( isset( $analysis->child->signals->thm_has_plugins ) && isset( $_POST[ 'ctc_merge_plugin_css' ] ) ):
            add_action( 'chld_thm_cfg_parse_stylesheets', 'ChildThemeConfiguratorPluginStylesheet::parse_plugin_stylesheet_to_target' );
            add_action( 'chld_thm_cfg_addl_files', 'ChildThemeConfiguratorPluginStylesheet::remove_plugin_stylesheet_action' );
            add_action( 'chld_thm_cfg_addl_options', 'ChildThemeConfiguratorPluginStylesheet::delete_plugin_stylesheet' );
        endif;

        add_action( 'chld_thm_cfg_addl_files',   array( $this, 'enqueue_parent_css' ), 15, 2 );
    }
    
    /**
     * exports theme as zip archive.
     * As of version 2.03, parent themes can be exported as well
     */
    function export_theme() {
        $version = '';
        if ( empty( $_POST[ 'ctc_export_theme' ] ) ):
            $template = $this->get( 'child' );
            $version = preg_replace( "%[^\w\.\-]%", '', $this->get( 'version' ) );
        else:
            $template = sanitize_text_field( $_POST[ 'ctc_export_theme' ] );
            if ( ( $theme = wp_get_theme( $template ) ) && is_object( $theme ) )
                $version = preg_replace( "%\.\d{10}$%", '', $theme->Version );
        endif;
        // make sure directory exists and is in themes folder
        if ( ( $dir = $this->css()->is_file_ok( trailingslashit( get_theme_root() ) . $template, 'search' ) ) ):
            if ( $tmpdir = $this->get_tmp_dir() ):
                $file = trailingslashit( $tmpdir ) . $template . ( empty( $version ) ? '' : '-' . $version ) . '.zip';
                $this->export_zip_file( $file, $dir );
            else:
                return FALSE;
            endif;
        else:
            $this->errors[] = 21; //__( 'Invalid theme root directory.', 'child-theme-configurator' );
        endif;
    }
    
    function export_zip_file( $file, $source ) {
        if ( file_exists( $file ) ) unlink ( $file );

        mbstring_binary_safe_encoding();
        
        // PclZip ships with WordPress
        require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

        $archive = new PclZip( $file );
        if ( $response = $archive->create( $source, PCLZIP_OPT_REMOVE_PATH, dirname( $source ) ) ):

            reset_mbstring_encoding();
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Length: ' . filesize( $file ) );
            header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            readfile( $file );
            unlink( $file );
            die();
        else:
            $this->errors[] = 23; //__( 'PclZip returned zero bytes.', 'child-theme-configurator' );
        endif;
    }
    /**
     * used to verify that post data corresponds to current state
     */
    function fingerprint_field(){
        echo $this->state()->get_fingerprint_field();
    }
    
    /*
     * convert 'direct' filepath into wp_filesystem filepath
     */
    function fspath( $file ){
        if ( ! $this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        if ( is_dir( $file ) ):
            $dir = $file;
            $base = '';
        else:
            $dir = dirname( $file );
            $base = basename( $file );
        endif;
        $fsdir = $wp_filesystem->find_folder( $dir );
        return trailingslashit( $fsdir ) . $base;
    }
        
    /* helper function to retreive css object properties */
    function get( $property, $params = NULL ) {
        return $this->css()->get_prop( $property, $params );
    }
        
    function get_child_stylesheet() {
        if ( 'stylesheet' == $this->get_mode() )
            return 'separate' == $this->get( 'handling' ) ? $this->get( 'sepstylesheet' ) : 'style.css';
        return FALSE;
    }
    
    /**
     * returns css files for active plugins
     */
    function get_css_files(){
        $candidates = array();
        $this->debug( 'Getting Plugin stylesheets from ' . $this->plugindir . ' ...', __FUNCTION__ );
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        foreach( get_plugins() as $handle => $data ):
            if ( !is_plugin_active( $handle ) )
                continue;
            $parentdir = dirname( $handle );
            $dir = trailingslashit( $this->plugindir ) . $parentdir;
            $this->debug( 'Checking ' . $dir . ' ...', __FUNCTION__ );
            foreach ( $this->css()->recurse_directory( $dir ) as $filepath ):
                $file = plugin_basename( $filepath );
                $skip = FALSE;
                foreach ( apply_filters( 'chld_thm_cfg_backend', array() ) as $regex ):
                    if ( preg_match( $regex, $file ) ):
                        $skip = TRUE;
                        break;
                    endif;
                endforeach;
                if (!$skip):
                    $label = '<strong>' . $data[ 'Name' ] . '</strong>'; //preg_replace( '/^' . preg_quote( $parentdir) . '/', '', $file ) . ')';
                    $candidates[ $file ] = $label;
                    $this->debug( 'Added ' . $label . ' - ' . $file, __FUNCTION__ );
                else:
                    $this->debug( 'Skipped ' . $filepath, __FUNCTION__ );
                endif;
            endforeach;
        endforeach;
        if ( empty( $candidates ) )
            $this->debug( 'No plugin stylesheets!', __FUNCTION__ );
        return $candidates; 
    }
         
    function get_ctc_header( $content ) {
        return __( 'Child Theme Configurator Pro', 'child-theme-configurator' ) 
        . ' ' . __( 'version', 'child-theme-configurator' ) . ' ' . CHLD_THM_CFG_PRO_VERSION; // . ' (CTC ' . CHLD_THM_CFG_VERSION . ')';
    }
       
    /* returns child theme either from existing config or passed as post var */
    function get_current_child() {
        // check if parent was passed
        //if ( isset( $_GET[ 'ctc_theme_child' ] ) && ( $child = sanitize_text_field( $_GET[ 'ctc_theme_child' ] ) ) )
        //    return $child;
        // otherwise use css object value
        //else
        if ( $child = $this->get( 'child' ) )
            return $child;
        // otherwise use stylesheet value
        else return get_stylesheet();
    }
        
    /* returns parent theme either from existing config or passed as post var */
    function get_current_parent() {
        // check if child was passed and use Template value
        //if ( isset( $_GET[ 'ctc_theme_child' ] ) && ( $child = sanitize_text_field( $_GET[ 'ctc_theme_child' ] ) ) )
        //    return $this->get_theme_property( $child, 'Template' );
        // otherwise check if parent was passed
        //if ( isset( $_GET[ 'ctc_theme_parnt' ] ) && ( $parent = sanitize_text_field( $_GET[ 'ctc_theme_parnt' ] ) ) )
        //    return $parent;
        // otherwise use css object value
        //else
        if ( $parent = $this->get( 'parnt' ) )
            return $parent;
        // otherwise use template value
        else return get_template();
    }
    
    function get_debug() {
        return get_site_transient( CHLD_THM_CFG_OPTIONS . '_debug' ) . LF . $this->debug;
    }
    
    function get_files( $theme, $type = 'template' ) {
        $isparent = ( $theme === $this->get( 'parnt' ) );
        if ( 'template' == $type && $isparent && ( $templates = $this->get( 'templates' ) ) ): 
            return $templates;
        elseif ( !isset( $this->files[ $theme ] ) ):

            $this->files[ $theme ] = array();
            $imgext = '(' . implode( '|', array_keys( $this->imgmimes ) ) . ')';
            foreach ( $this->css()->recurse_directory(
                trailingslashit( get_theme_root() ) . $theme, '', TRUE ) as $filepath ):
                $file = $this->theme_basename( $theme, $filepath );
                if ( preg_match( "/^style\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'backup' ][ $file ] = $date;
                    //$this->debug( 'This is a backup file', __FUNCTION__, __CLASS__ );
                elseif ( strstr( $file, "ctcbackup" ) ):
                    $date = date_i18n( 'D, j M Y g:i A', filemtime( $filepath ) );
                    $this->files[ $theme ][ 'backup' ][ $file ] = $date;
                elseif ( preg_match( "/^ctc\-plugins\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'pluginbackup' ][ $file ] = $date;
                    //$this->debug( 'This is a plugin backup file', __FUNCTION__, __CLASS__ );
                elseif ( preg_match( "/\.php$/", $file ) ):
                    if ( $isparent ):
                    
                        if ( ( $file_verified = $this->css()->is_file_ok( $this->css()->get_parent_source( $file, $theme ) , 'read' ) ) ):
                            $this->debug( 'scanning ' . $file_verified . '... ', __FUNCTION__, __CLASS__ );
                            // read 2k at a time and bail if code detected
                            $template = FALSE;
                            $lastcontents = '';
                            $size = 0;
                            if ( $handle = fopen( $file_verified, "rb") ):
                                while ( !feof( $handle ) ):
                                    $size++;
                                    if ( $size > 10 ) // if larger than 20k this ain't a template
                                        break;
                                    $contents = fread($handle, 2048);
                                    $chunk = $lastcontents . $contents;
                                    if ( preg_match( "/\w+\s*\(/", $chunk ) ):
                                        $template = TRUE;
                                        // remove scripts so they don't cause false positives - v.2.3.0.4
                                        $chunk = preg_replace( "%<script>.+?</script>%s", '', $chunk );
                                        $chunk = preg_replace( "%(^.+?</script>|<script>.+$)%s", '', $chunk );
                                        // if contents contain functions or requires this is not a template
                                        if ( preg_match( "/(function \w+?|require(_once)?)\s*\(/", $chunk ) )
                                            $template = FALSE;
                                    endif;
                                    $lastcontents = $contents;
                                endwhile;
                                fclose( $handle );
                            endif;
                            if ( $template )
                                $this->files[ $theme ][ 'template' ][] = $file;
                        endif;
                    else:
                        //$this->debug( 'Child PHP, adding to templates', __FUNCTION__, __CLASS__ );
                        $this->files[ $theme ][ 'template' ][] = $file;
                    endif;
                elseif ( preg_match( "/\.css$/", $file ) 
                    && ( !in_array( $file, array( 
                        'style.css', 
                        $this->get_child_stylesheet(), 
                        'ctc-plugins.css' 
                    ) ) ) ):
                    $this->files[ $theme ][ 'stylesheet' ][] = $file;
                    //$this->debug( 'This is a stylesheet', __FUNCTION__, __CLASS__ );
                elseif ( preg_match( "/\.(js|txt)$/", $file ) ):
                    $this->files[ $theme ][ 'txt' ][] = $file;
                elseif ( preg_match( "/^images\/.+?\." . $imgext . "$/", $file ) ):
                    $this->files[ $theme ][ 'img' ][] = $file;
                    //$this->debug( 'This is an image file', __FUNCTION__, __CLASS__ );
                else:
                    $this->files[ $theme ][ 'other' ][] = $file;
                endif;
            endforeach;
        endif;
        if ( $isparent ):
            //$this->debug( 'Setting CSS object templates parameter', __FUNCTION__, __CLASS__ );
            $this->css()->templates = $this->files[ $theme ][ 'template' ];
        endif;
        $types = explode( ",", $type );
        $files = array();
        foreach ( $types as $type ):
            if ( isset( $this->files[ $theme ][ $type ] ) )
                $files = array_merge( $this->files[ $theme ][ $type ], $files );
        endforeach;
        return $files;
    }
        
    function get_mode(){
        if ( $this->state()->is_inline() )
            return 'inline';
        return 'stylesheet';
    }
    
    /**
     * modified v.2.3.0
     * return plugin path if plugin file exists
     * otherwise return original path
     */
    function get_parent( $path, $file ) {
        $pluginfile = trailingslashit( $this->plugindir ) . $file;
        if ( file_exists( $pluginfile ) )
            return $pluginfile;
        return $path;
    }
    
    function get_pathinfo( $path ){
        $pathinfo = pathinfo( $path );
        $path = ( preg_match( "/^[\.\/]/", $pathinfo[ 'dirname' ] ) ? '' : $pathinfo[ 'dirname' ] . '/' ) . $pathinfo[ 'filename' ];
        return array( $path, $pathinfo[ 'extension' ] );
    }
    
    function get_premium_theme_mods( $from, $to ){
        
        // get all options named with $from
        global $wpdb;
            
        // all permutations of options 
        $v = array(
            'from' => array(
                'default' => $from, 
                'undersc' => $this->clean_slug( $from ),
                'thmnmus' => $this->clean_slug( $this->get_theme_property( $from, 'Name', 1 ) ),
                'thmname' => $this->clean_slug( $this->get_theme_property( $from, 'Name', 1 ), '' ),
            ),
            'to' => array(
                'default' => $to,
                'undersc' => $this->clean_slug( $to ),
                'thmnmus' => $this->clean_slug( $this->get( 'child_name' ) ), // this will always contain the prospective child theme name
                'thmname' => $this->clean_slug( $this->get( 'child_name' ), '' ),
            ),
        );
        /*
        if ( $of = get_option( 'avia_options_' . $v[ 'from' ][ 'themnmus' ] ) ):
        
            // parent theme is using avia
        elseif ( ( $of = get_option( 'optionsframework' ) ) ):
            die( print_r( $of, TRUE ) );
            // parent theme is using options framework
        else:
        */
            // use shotgun approach
            $this->debug( 'option permutations array: ' . print_r( $v, TRUE ), __FUNCTION__ );

            $query = "
            SELECT option_name 
            FROM {$wpdb->prefix}options 
            WHERE option_name NOT LIKE %s 
                AND option_name NOT LIKE %s 
                AND ( option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s 
                    OR option_name LIKE %s )
                    ";

                $args = $wpdb->prepare(
                    $query, 
                    'theme_mods_%', 
                    '%_transient_%', 
                    '%' . $v[ 'to' ][ 'default' ],   // ex: blah[slug-parent]
                    $v[ 'to' ][ 'default' ] . '_%',  // ex: [slug-parent]_blah
                    '%' . $v[ 'to' ][ 'undersc' ],   // ex: blah[slug_parent]
                    $v[ 'to' ][ 'undersc' ] . '_%',  // ex: [slug_parent]_blah
                    '%' . $v[ 'to' ][ 'thmnmus' ],   // ex: blah[slugparent]
                    $v[ 'to' ][ 'thmnmus' ] . '_%',   // ex: [slugparent]_blah
                    '%' . $v[ 'to' ][ 'thmname' ],   // ex: blah[slugparent]
                    $v[ 'to' ][ 'thmname' ] . '_%'   // ex: [slugparent]_blah
                );

            $options = $wpdb->get_col( $args );

            if ( !count( $options ) ) return array();
    /**
     * for manual insert:

    INSERT INTO `wp_options` ( option_name, option_value, autoload ) 
    SELECT 'avia_options_enfold-child' as option_name, option_value, autoload
    FROM `wp_options` 
    WHERE option_name = 'avia_options_enfold' 
    LIMIT1
    */
            // build map of from => to option names
            $values = array();
            $done = array();
            foreach ( $options as $to_option ):

                foreach ( array( 'default', 'undersc', 'thmnmus', 'thmname' ) as $var ):  // loop through variations 

                    // create target option name by replacing parent name with child name
                    if ( $from != $to ):
                        $from_option = preg_replace( // replace parent with child
                            '/^' . preg_quote( $v[ 'to' ][ $var ] ) . '|' . preg_quote( $v[ 'to' ][ $var ] ) . '$/', 
                            $v[ 'from' ][ $var ],                                       
                            $to_option, 
                            1, 
                            $count 
                        );
                        // verify count ( filters out case mismatch )
                        if ( !$count ):
                            continue;
                        endif;
                    // if only getting child options, use as is
                    else:
                        $to_option = $from_option;
                    endif;

                    $this->debug( 'fetching non-standard theme option ' . $from_option . ' for child theme option ' . $to_option, __FUNCTION__ );
                    if ( empty( $done[ $from_option ] ) 
                        && ( $fvalue = get_option( $from_option, TRUE ) )
                        && is_array( $fvalue ) ):
                        // both from and to must exist
                        $this->debug( $from_option . ' found', __FUNCTION__ );
                        $values[ $to_option ] = $fvalue;
                        $done[ $from_option ] = 1;
                    endif;
                endforeach;
            endforeach;

        //endif;
        return $values;
    
    }
    
    function get_prop( $null, $obj, $params ) {
        switch ( $obj ):
            case 'all_styles':
                ob_start();
                $this->render_all_selectors();
                $results = ob_get_contents();
                ob_end_clean();
                return $results;
        endswitch;
        return FALSE;
    }
    
    function get_randomized_headers( $from ){
        $ids = array();
        foreach ( get_posts( array( 
                'post_type'     => 'attachment', 
                'meta_key'      => '_wp_attachment_is_custom_header', 
                'meta_value'    => $from, 
                'orderby'       => 'none', 
                'nopaging'      => true,
                'fields'        => 'ID',
        ) ) as $header )
            $ids[] = $header->ID;
        return $ids;
    }
    
    function get_recent(){
        return $this->state()->get_recent( $this->get_mode() );    
    }
    
    function get_theme( $slug ){
        foreach ( $this->get_themes() as $template => $themes ):
            if ( isset( $themes[ $slug ] ) )
                return $themes[ $slug ];
        endforeach;
        return FALSE;
    }
    
    function get_theme_count( $template, $config = FALSE ){
        return count( $this->get_theme_group( $template, $config ) );
    }
    
    function get_theme_group( $template, $config = FALSE ){
        $themes = array();
        foreach( $this->get_themes( $template ) as $slug => $theme ):
            if ( $config && empty( $theme[ 'config'] ) )
                continue;
            $themes[ $slug ] = $theme;
        endforeach;
        uasort( $themes, array( $this, 'cmp_theme' ) );
        return $themes;
    }

    function get_theme_mods( $theme ){
        // get active theme
        $active_theme = get_stylesheet();
        // create temp array from parent settings
        $mods = get_option( 'theme_mods_' . $theme );
        if ( $active_theme == $theme ):
            $this->debug( $theme . ' is active, using active widgets', __FUNCTION__, __CLASS__ );
            // if parent theme is active, get widgets from active sidebars_widgets array
            $mods[ 'sidebars_widgets' ][ 'data' ] = retrieve_widgets();
        else:
            $this->debug( $theme . ' not active, using theme mods widgets', __FUNCTION__, __CLASS__ );
            // otherwise get widgets from parent theme mods
            $mods[ 'sidebars_widgets' ][ 'data' ] = empty( $mods[ 'sidebars_widgets' ][ 'data' ] ) ?
                array( 'wp_inactive_widgets' => array() ) : $mods[ 'sidebars_widgets' ][ 'data' ];
        endif;
        return $mods;
    }
    
    function get_theme_property( $slug, $property ){
        if ( $theme = $this->get_theme( $slug ) ):
            if ( isset( $theme[ $property ] ) )
                return $theme[ $property ];
        endif;
        return FALSE;
    }
    
    function get_themes( $template = NULL ) {
        // create cache of theme info
        if ( !isset( $this->themes ) ):
            $this->themes = array( 'child' => array(), 'parnt' => array() );
            foreach ( wp_get_themes() as $theme ):
                // organize into parent and child themes
                $group      = $theme->parent() ? 'child' : 'parnt';
                // get the theme slug
                $slug       = $theme->get_stylesheet();
                // get the theme version
                $version    = $theme->get( 'Version' );
                // strip auto-generated timestamp from CTC child theme version
                if ( 'child' == $group ) $version = preg_replace("/\.\d{6}\d+$/", '', $version );
                // is there a configuration for this theme?
                $config = ( FALSE !== get_site_option( CHLD_THM_CFG_OPTIONS . '_' . $slug . '_configvars' ) );
                // add theme to themes array
                $this->themes[ $group ][ $slug ] = array(
                    'Template'      => $theme->get( 'Template' ),
                    'Name'          => $theme->get( 'Name' ),
                    'ThemeURI'      => $theme->get( 'ThemeURI' ),
                    'Author'        => $theme->get( 'Author' ),
                    'AuthorURI'     => $theme->get( 'AuthorURI' ),
                    'Descr'         => $theme->get( 'Description' ),
                    'Tags'          => $theme->get( 'Tags' ),
                    'Version'       => $version,
                    'screenshot'    => $theme->get_screenshot(),
                    'allowed'       => $theme->is_allowed(),
                    'config'        => $config,
                );
            endforeach;
        endif;
        return empty( $template ) ? $this->themes : $this->themes[ $template ];
    }

    function get_tmp_dir(){
        // Try to use php system upload dir to store temp files first
        $tmpdir = ini_get( 'upload_tmp_dir' ) ? ini_get( 'upload_tmp_dir' ) : sys_get_temp_dir();
        if ( !is_writable( $tmpdir ) ):
            // try uploads directory
            $uploads = wp_upload_dir();
            $tmpdir = $uploads[ 'basedir' ];
            if ( !is_writable( $tmpdir ) ):
                $this->errors[] = 22; //__( 'No writable temp directory.', 'child-theme-configurator' );
                return FALSE;
            endif;
        endif;
        return $tmpdir;
    }
    
    function get_update_msg( $msg ) {
        if ( isset( $_GET[ 'updated' ] ) && 4 == $_GET[ 'updated' ] ) 
            return __( 'Update Key saved successfully.', 'child-theme-configurator' );
        return $msg;
    }

    function handle_file_upload( $field, $childdir = NULL, $mimes = NULL ){
        $uploadedfile = $_FILES[ $field ];
        $upload_overrides = array( 
            'test_form' => FALSE,
            'mimes' => ( is_array( $mimes ) ? $mimes : NULL )
        );
        if ( ! function_exists( 'wp_handle_upload' ) ) 
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        if ( isset( $movefile[ 'error' ] ) ):
            $this->errors[] = '27:' . $movefile[ 'error' ];
            return FALSE;
        endif;
        $_POST[ 'movefile' ] = $this->uploads_basename( $movefile[ 'file' ] );        
    }
    
    /**
     * Update functions file with wp_enqueue_style code block. Runs in read-only mode if getexternals is passed.
     * This function uses the same method as the WP core function that updates .htaccess 
     * we would have used WP's insert_with_markers function, 
     * but it does not use wp_filesystem API.
     */
    function insert_with_markers( $filename, $marker, $insertion, $getexternals = FALSE, $reset = FALSE ) { 
        if ( count( $this->errors ) ):
            $this->debug( 'Errors detected, returning', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        // first check if this is an ajax update
        if ( $this->is_ajax && is_readable( $filename ) && is_writable( $filename ) ):
            // ok to proceed
            $this->debug( 'Ajax update, bypassing wp filesystem.', __FUNCTION__, __CLASS__ );
            $markerdata = explode( "\n", @file_get_contents( $filename ) );
        elseif ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        else:
            global $wp_filesystem;
            if( !$wp_filesystem->exists( $this->fspath( $filename ) ) ):
                if ( $getexternals ):
                    $this->debug( 'Read only and no functions file yet, returning...', __FUNCTION__, __CLASS__ );
                    return FALSE;
                else:
                    // make sure file exists with php header
                    $this->debug( 'No functions file, creating...', __FUNCTION__, __CLASS__ );
                    $this->add_base_files( $this );
                endif;
            endif;
            // get_contents_array returns extra linefeeds so just split it ourself
            $markerdata = explode( "\n", $wp_filesystem->get_contents( $this->fspath( $filename ) ) );
        endif;
        $newfile = '';
        $externals  = array();
        $phpopen    = 0;
        $in_comment = 0;
        $foundit = FALSE;
        if ( $markerdata ):
            $state = TRUE;
            foreach ( $markerdata as $n => $markerline ):
                // remove double slash comment to end of line
                $str = preg_replace( "/\/\/.*$/", '', $markerline );
                preg_match_all("/(<\?|\?>|\*\/|\/\*)/", $str, $matches );
                if ( $matches ):
                    foreach ( $matches[1] as $token ): 
                        if ( '/*' == $token ):
                            $in_comment = 1;
                        elseif ( '*/' == $token ):
                            $in_comment = 0;
                        elseif ( '<?' == $token && !$in_comment ):
                            $phpopen = 1;
                        elseif ( '?>' == $token && !$in_comment ):
                            $phpopen = 0;
                        endif;
                    endforeach;
                endif;
                if ( strpos( $markerline, '// BEGIN ' . $marker ) !== FALSE )
                    $state = FALSE;
                if ( $state ):
                    if ( $n + 1 < count( $markerdata ) )
                        $newfile .= "{$markerline}\n";
                    else
                        $newfile .= "{$markerline}";
                elseif ( $getexternals ):
                    // look for existing external stylesheets and add to imports config data
                    if ( preg_match( "/wp_enqueue_style.+?'chld_thm_cfg_ext\d+'.+?'(.+?)'/", $markerline, $matches ) ):
                        $this->debug( 'external link found : ' . $matches[ 1 ] );
                        $this->convert_enqueue_to_import( $matches[ 1 ] );
                    // look for deregister/register link paths for swapping parent/child
                    elseif ( preg_match( "/wp_register_style[^']+'(.+?)'[^']+'(.+?)'/", $markerline, $matches ) ):
                        $this->debug( 'link swap found : ' . $matches[ 1 ] . ' => ' . $matches[ 2 ] );
                        
                        $handle = sanitize_text_field( $matches[ 1 ] );
                        $path   = sanitize_text_field( $matches[ 2 ] );
                        $this->css()->swappath[ $handle ] = $path;
                        
                    endif;
                endif;
                if ( strpos( $markerline, '// END ' . $marker ) !== FALSE ):
                    if ( !$reset ):
                        $newfile .= "// BEGIN {$marker}\n";
                        if ( is_array( $insertion ) )
                            foreach ( $insertion as $insertline )
                                $newfile .= "{$insertline}\n";
                        $newfile .= "// END {$marker}\n";
                    endif;
                    $state = TRUE;
                    $foundit = TRUE;
                endif;
            endforeach;
        else:
            $this->debug( 'Could not parse functions file', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        if ( $foundit ):
            $this->debug( 'Found marker, replaced inline', __FUNCTION__, __CLASS__ );
        else:
                // verify there is no PHP close tag at end of file
                if ( ! $phpopen ):
                    $this->debug( 'PHP not open', __FUNCTION__, __CLASS__ );
                    //$this->errors[] = 12 //__( 'A closing PHP tag was detected in Child theme functions file so "Parent Stylesheet Handling" option was not configured. Closing PHP at the end of the file is discouraged as it can cause premature HTTP headers. Please edit <code>functions.php</code> to remove the final <code>?&gt;</code> tag and click "Generate/Rebuild Child Theme Files" again.', 'child-theme-configurator' );
                    //return FALSE;
                    $newfile .= '<?php' . LF;
                endif;
                $newfile .= "\n// BEGIN {$marker}\n";
                foreach ( $insertion as $insertline )
                    $newfile .= "{$insertline}\n";
                $newfile .= "// END {$marker}\n";
        endif;
        // only write file when getexternals is false
        if ( $getexternals ):
            $this->debug( 'Read only, returning.', __FUNCTION__, __CLASS__ );
        else:
            $mode = 'direct' == $this->fs_method ? FALSE : 0666;
            $this->debug( 'Writing new functions file...', __FUNCTION__, __CLASS__ );
            if ( $this->is_ajax && is_writable( $filename ) ): 
                // with ajax we have to bypass wp filesystem so file must already be writable
                if ( FALSE === @file_put_contents( $filename, $newfile ) ): 
                    $this->debug( 'Ajax write failed.', __FUNCTION__, __CLASS__ );
                    return FALSE;
                endif;
            elseif ( FALSE === $wp_filesystem->put_contents( 
                $this->fspath( $filename ), 
                $newfile, 
                $mode 
            ) ): // chmod will fail unless we have fs access. user can secure files after configuring
                $this->debug( 'Filesystem write failed.', __FUNCTION__, __CLASS__ );
                return FALSE;
            endif;
            $this->css()->set_prop( 'converted', 1 );
        endif;
    }

    function load_config() {

        if ( FALSE !== $this->css( TRUE )->load_config() ):
            $this->debug( 'config exists', __FUNCTION__, __CLASS__, __CLASS__ );
            // if themes do not exist reinitialize
            if ( ! $this->check_theme_exists( $this->get( 'child' ) )
                || ! $this->check_theme_exists( $this->get( 'parnt' ) ) ):
                $this->debug( 'theme does not exist', __FUNCTION__, __CLASS__, __CLASS__ );
                add_action( 'chld_thm_cfg_admin_notices', array( $this, 'config_notice' ) );     
                $this->css( TRUE )->enqueue = 'enqueue';
            endif;
        else:
            $this->debug( 'config does not exist', __FUNCTION__, __CLASS__, __CLASS__ );
            // this is a fresh install
            $this->css( TRUE )->enqueue = 'enqueue';
        endif;
        do_action( 'chld_thm_cfg_load' );
        if ( $this->is_get ):
            /**
             * using 'updated' get var to indicate theme mods should be copied and the to/from themes
             * otherwise set msg id
             */
            if ( isset( $_GET[ 'updated' ] ) ):
                $msgparts = explode( ',', $_GET[ 'updated' ] );
                $this->msg = array_shift( $msgparts );
                if ( count( $msgparts ) )
                    $this->copy_mods = $msgparts;
            endif;
            if ( $this->get( 'child' ) ):
                // get filesystem credentials if available
                $this->verify_creds();
                $this->verify_target();
                // enqueue flag will be null for existing install < 1.6.0
                if ( !$this->get( 'enqueue' ) ):
                    $this->debug( 'no enqueue:', __FUNCTION__, __CLASS__, __CLASS__ );

                    add_action( 'chld_thm_cfg_admin_notices', array( $this, 'enqueue_notice' ) );     
                endif;
            endif;
            if ( !$this->seen_upgrade_notice() ):
                add_action( 'chld_thm_cfg_admin_notices', array( $this, 'upgrade_notice' ) ); 
            endif;
            /**
             * Future use: check if max selectors reached
             *
            if ( $this->get( 'max_sel' ) ):
                $this->debug( 'Max selectors exceeded.', __FUNCTION__, __CLASS__, __CLASS__ );
                //$this->errors[] = 26; //__( 'Maximum number of styles exceeded.', 'child-theme-configurator' );
                add_action( 'chld_thm_cfg_admin_notices', array( $this, 'max_styles_notice' ) ); 
            endif;
            */
            // check if file ownership is messed up from old version or other plugin
            // by comparing owner of plugin to owner of child theme:
            if ( fileowner( $this->css()->get_child_target( '' ) ) != fileowner( CHLD_THM_CFG_DIR ) )
                add_action( 'chld_thm_cfg_admin_notices', array( $this, 'owner_notice' ) ); 
        endif;    
    }

    function load_state( $key = NULL ){
        $state = $this->refresh ? array() : $this->state()->load_state( $key );
        //echo "<!-- \n=========\nCTC STATE\n==========\n" . print_r( $state, TRUE ) . "\n=========\n -->\n";
        return $state;
    }
    
    function localize_array( $array ) {
        $array[ 'recent_txt' ]  = __( 'No recent edits.', 'child-theme-configurator' );
        $array[ 'palette' ]     = 1;
        $array[ 'plugin_css' ]  = $this->get_css_files();
        $array[ 'plugin_uri' ]  = dirname( plugins_url() );
        $array[ 'plugin_dir' ]  = basename( plugins_url() );
        $array[ 'plugin_css_headline_txt' ] = __( 'This child theme still uses a separate stylesheet for custom plugin styles.', 'child-theme-configurator' );
        $array[ 'plugin_css_descr_txt' ] = __( 'CTC Pro no longer uses a separate interface to customize plugin styles. Moving forward, plugin styles will appear along with child theme styles in the editors.<br/><label style="display:block;padding:1em;margin:.5em 0;background-color:#efefef;border:1px solid #fff"><input class="ctc_checkbox ctc-themeonly" type="checkbox" name="ctc_merge_plugin_css" value="1" /> Check this box to merge the original plugin stylesheet from an earlier version of CTC Pro into the child theme stylesheet.</label>', 'child-theme-configurator' );
        $array[ 'addl_css' ] = array_merge( $array[ 'addl_css' ], $this->options[ 'addl_css' ] );
        return $array;
    }
    
    function log_debug() {
        $this->debug( '*** END OF REQUEST ***', __FUNCTION__, __CLASS__ );
        // save debug data for 1 hour
        set_site_transient( CHLD_THM_CFG_OPTIONS . '_debug', $this->get_debug(), 3600 );
    }
    
    function max_styles_notice() {
        $this->ui()->render_notices( 'max_styles' );
    }

    function move_file_upload( $subdir = 'images' ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $source_file = sanitize_text_field( $_POST[ 'movefile' ] );
        $target_file = ( '' == $subdir ? 
            preg_replace( "%^.+(\.\w+)$%", "screenshot$1", basename( $source_file ) ) : 
                trailingslashit( $subdir ) . basename( $source_file ) );
        $source_path = $this->uploads_fullpath( $source_file );
        $source_dir = dirname( $source_path );
        $mode = 0;
        if ( FALSE !== $this->verify_child_dir( trailingslashit( $this->get( 'child' ) ) . $subdir ) ):
                
            if ( $target_path = $this->css()->is_file_ok( $this->css()->get_child_target( $target_file ), 'write' ) ):
                $fs_target_path = $this->fspath( $target_path );
                $fs_source_path = $this->fspath( $source_path );
                if ( $wp_filesystem->exists( $fs_source_path ) ):
                    // in case source dir is not writable by wp_filesystem
                    //$fs_source_dir = dirname( $fs_source_path );
                    //if ( !$wp_filesystem->is_writable( $fs_source_dir ) ):
                        // wp_filesystem->is_writable always returns true so just try chmod as webserver
                        $mode = fileperms( $source_dir );
                        if ( $mode ) :
                            $writemode = $mode | 0666;
                            if ( $set_perms = @chmod( $source_dir, $writemode ) )
                                $this->debug( 'Changed source dir permissions from ' . substr( sprintf( '%o', $mode ), -4 ) . ' to ' . substr( sprintf( '%o', $writemode ), -4 ), __FUNCTION__ );
                        endif;
                    //endif;
                    if ( @$wp_filesystem->move( $fs_source_path, $fs_target_path ) ): 
                        if ( $mode && $set_perms ):
                            if ( @chmod( $source_dir, $mode ) )
                                $this->debug( 'Reset source dir permissions to ' . substr( sprintf( '%o', $mode ), -4 ), __FUNCTION__ );
                        endif;
                        return TRUE;
                    else:
                        if ( $mode && $set_perms ):
                            if ( @chmod( $source_dir, $mode ) )
                                $this->debug( 'Reset source dir permissions to ' . substr( sprintf( '%o', $mode ), -4 ), __FUNCTION__ );
                        endif;
                        $this->debug( 'Could not move file from ' . $source_path . ' to ' . $target_file, __FUNCTION__ );
                    endif;
                else:
                    $this->debug( 'Source file does not exist: ' . $source_path, __FUNCTION__ );
                endif;
            else:
                $this->debug( 'Target file not OK: ' . $target_file, __FUNCTION__ );
            endif;
        else:
            $this->debug( 'Could not verify child dir', __FUNCTION__ );
        endif;
        
        $this->errors[] = 20; //__( 'Could not upload file.', 'child-theme-configurator' );        
    }
    
    function network_enable() {
        if ( $child = $this->get( 'child' ) ):
            $allowed_themes = get_site_option( 'allowedthemes' );
            $allowed_themes[ $child ] = true;
            update_site_option( 'allowedthemes', $allowed_themes );
        endif;
    }
    
    // backwards compatability < WP 3.9
    function normalize_path( $path ) {
        $path = str_replace( '\\', '/', $path );
        // accommodates windows NT paths without C: prefix
        $path = substr( $path, 0, 1 ) . preg_replace( '|/+|','/', substr( $path, 1 ) );
        if ( ':' === substr( $path, 1, 1 ) )
            $path = ucfirst( $path );
        return $path;
    }

    function owner_notice() {
        $this->ui()->render_notices( 'owner' );
    }
    
    function pack_data( $array, $qsid, $current ){
        try {
            return $this->state()->pack_data( $array, $qsid, $current );
        } catch ( Exception $e ){
            $this->debug( 'Pack failed -- ' . $e->getMessage(), __FUNCTION__, __CLASS__ );
            return FALSE;
        }       
    }
    
    function parse_additional_stylesheets_to_source() {
        // parse additional stylesheets
            foreach ( $this->get( 'addl_css' ) as $file ):
                $this->debug( 'parsing ' . $file, __FUNCTION__, __CLASS__ );
                //$file = sanitize_text_field( $file );
                $this->css()->parse_css_file( 'parnt', $file );
            endforeach;
    }
    
    function parse_child_stylesheet_to_source() {
        $this->css()->parse_css_file( 'child', 'style.css', 'parnt' );
    }
    
    function parse_child_stylesheet_to_target() {
        $this->css()->parse_css_file( 'child', 'style.css' );
    }
    
    function parse_custom_stylesheet_to_target() {
        $this->css()->parse_css_file( 'child', $this->get_child_stylesheet() );
    }
        
    function parse_genesis_stylesheet_to_source() {
        $this->css()->parse_css_file( 'child', 'ctc-genesis.css', 'parnt' );
    }
        
    function parse_parent_stylesheet_to_source() {
        $this->css()->parse_css_file( 'parnt' );
    }
    
    function preprocess() {
        $this->get_active_plugins();

    }
    
    function process_file_form() {
        if ( $this->is_post ):
            $args = preg_grep( "/nonce/", array_keys( $_POST ), PREG_GREP_INVERT );
            $this->verify_creds( $args );
            if ( isset( $_POST[ 'ctcp_save_update_key' ] ) ):
                if ( $this->validate_post( 'ctcp_update_key' ) ):
                    $this->options[ 'update_key' ] = preg_replace( "/\W/", '', sanitize_text_field( $_POST[ 'ctcp_update_key' ] ) );
                    update_site_option( CHLD_THM_CFG_OPTIONS, $this->options );
                    $this->debug( 'Updated options: ' . LF . print_r( $this->options, TRUE ), __FUNCTION__ );
                    $this->msg = '7&tab=register';
                    $this->update_redirect();
                endif;
            elseif ( isset( $_POST[ 'ctcp_create_file' ] ) ):
                if ( $this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ):
                    //
                    $filepath = preg_replace( "/[^\w\/]/", '-', sanitize_text_field( $_POST[ 'ctcp_blank_filename' ] ) );
                    $ext = sanitize_text_field( $_POST[ 'ctcp_blank_fileext' ] );
                    $path = $this->normalize_path( $this->get( 'child' ) . '/' . dirname( $filepath ) );
                    $file = $this->normalize_path( $filepath );
                    if ( !empty( $file ) ):
                        $ext = in_array( $ext, apply_filters( 'chld_thm_cfg_filetypes', $this->filetypes ) ) ? $ext : 'php';
                        //die( 'path: ' . $path . ' file: ' . $file . ' ext: ' . $ext );
                        if ( $this->verify_child_dir( $path ) ):
                            if ( FALSE !== $this->write_child_file( $file . '.' . $ext, '/* Blank */' ) ):
                                $this->msg = '8&tab=file_options';
                                $this->update_redirect();
                            endif;
                        endif;
                    endif;
                    $this->errors[] = 31; //__( 'Could not create file.', 'child-theme-configurator' );
                endif;
            elseif ( isset( $_POST[ 'ctcp_export_mods' ] ) ):
                if ( $this->validate_post( 'ctcp_export_mods' ) ):
                    if ( $tmpdir = $this->get_tmp_dir() ):
                        $template = $this->get( 'child' );
                        $settings = array(
                            'mods'      => $this->get_theme_mods( $template ),
                            'premium'   => $this->get_premium_theme_mods( $template, $template ),
                            'custom'    =>  wp_get_custom_css( $template ),
                            'headers'   => $this->get_randomized_headers( $template ),
                        );
                        $modfile = $tmpdir . '/theme_mod_export_' . time();
                        if ( $packed = $this->state()->packer()->pack( $settings ) ):
                            @file_put_contents( $modfile, $packed );
                            $version = preg_replace( "%[^\w\.\-]%", '', $this->get( 'version' ) );
                            $file = trailingslashit( $tmpdir ) . $template . '-settings' . ( empty( $version ) ? '' : '-' . $version ) . '.zip';
                            $this->export_zip_file( $file, $modfile );
                        endif;
                    endif;
                    $this->errors[] = 30; //__( 'Could not export settings file.', 'child-theme-configurator' )
                endif;
            elseif ( isset( $_POST[ 'ctcp_import_mods' ] ) ):
                if ( $this->validate_post( 'ctcp_import_mods' ) ):
                    require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
                    $archive = new PclZip( $_FILES[ 'ctcp_import_mods_file' ][ 'tmp_name' ] );
                    if ( ( $opt = $archive->extract( PCLZIP_OPT_EXTRACT_AS_STRING ) ) && isset( $opt[ 0 ] ) && is_array( $opt[ 0 ] ) ):
                        $stream = $opt[ 0 ][ 'content' ];
                        try {
                            $this->state()->packer()->reset( $stream );
                            $settings = $this->state()->packer()->unpack();
                            // we are only checking if array exists and that settings came through unscathed to validate
                            if ( is_array( $settings ) ):
                                //die( '<pre><code><small>' . print_r( $settings, TRUE ) . '</small></code></pre>' );
                                if ( isset( $settings[ 'mods' ] ) ): // && isset( $settings[ 'custom' ] ) ):
                                    $template = $this->get( 'child' );
                                    $r = wp_update_custom_css_post( $settings[ 'custom' ], array(
                                        'stylesheet' => $template
                                    ) );

                                    // if ok, set id in child theme mods
                                    if ( !( $r instanceof WP_Error ) )
                                        $settings[ 'mods' ][ 'custom_css_post_id' ] = $r->ID;

                                    $this->set_theme_mods( $template, $settings[ 'mods' ] );
                                    if ( count( $settings[ 'premium' ] ) )
                                        $this->set_premium_theme_mods( $settings[ 'premium' ] );
                            
                                    foreach ( $settings[ 'headers' ] as $header )
                                        add_post_meta( $header, '_wp_attachment_is_custom_header', $template );

                                    $this->msg = '8&tab=file_options';
                                    $this->update_redirect();
                                endif;
                            else:
                              $this->errors[] = 29; //__( 'Invalid mods archive.', 'child-theme-configurator' );
                            endif;
                        } catch ( Exception $e ){
                            $this->errors[] = 24; //__( 'Unpack failed -- ', 'child-theme-configurator' ) . $e->getMessage();
                        }
                    endif;
                endif;
            endif;
        endif;
        // catch errors immediately
        if ( $this->errors )
            $this->update_redirect();
    }
    
    /**
     * Handles processing for all form submissions.
     * Moved conditions to switch statement with the main setup logic in a separate function.
     */
    function process_post() {
        // make sure this is a post
        if ( $this->is_post ):
            // see if a valid action was passed
            foreach ( $this->actionfields as $field ):
                if ( in_array( 'ctc_' . $field, array_keys( $_POST ) ) ):
                    $actionfield = $field;
                    break;
                endif;
            endforeach;
            if ( empty( $actionfield ) ) return FALSE;
            
            // make sure post passes security checkpoint        
            if ( !$this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ):
                // if you end up here you are persona non grata
                $this->errors[] = 2; //__( 'You do not have permission to configure child themes.', 'child-theme-configurator' );
            else:
                // reset debug log
                delete_site_transient( CHLD_THM_CFG_OPTIONS . '_debug' );
                // handle uploaded file before checking filesystem
                if ( 'theme_image_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_image' ] ) ):
                    $this->handle_file_upload( 'ctc_theme_image', $this->imgmimes );          
                elseif ( 'theme_screenshot_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_screenshot' ] ) ):
                    $this->handle_file_upload( 'ctc_theme_screenshot', $this->imgmimes );
                endif;
                // now we need to check filesystem access 
                $args = preg_grep( "/nonce/", array_keys( $_POST ), PREG_GREP_INVERT );
                $this->verify_creds( $args );
                if ( $this->fs ):
                    // we have filesystem access so proceed with specific actions
                    switch( $actionfield ):
                        case 'export_child_zip':
                        case 'export_theme':
                            $this->export_theme();
                            // if we get here the zip failed
                            $this->errors[] = 1; //__( 'Zip file creation failed.', 'child-theme-configurator' );
                            break;
                        case 'load_styles':
                            // main child theme setup function
                            $this->setup_child_theme();
                            break;
                        case 'data_reload':
                            // parse styles from stylesheet based on mode state
                            $this->reload();
                            $this->msg = '6&tab=query_selector_options';
                            break;
                        case 'parnt_templates_submit':
                            // copy parent templates to child
                            if ( isset( $_POST[ 'ctc_file_parnt' ] ) ):
                                foreach ( $_POST[ 'ctc_file_parnt' ] as $file ):
                                    list( $path, $ext ) = $this->get_pathinfo( sanitize_text_field( $file ) );
                                    $this->copy_parent_file( $path, $ext );
                                endforeach;
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                            
                        case 'child_templates_submit':
                            // delete child theme files
                            if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                
                                foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                    list( $path, $ext ) = $this->get_pathinfo( sanitize_text_field( $file ) );
                                    if ( 'functions' == $path ):
                                        $this->errors[] = 4; // __( 'The Functions file is required and cannot be deleted.', 'child-theme-configurator' );
                                        continue; 
                                    else:
                                        $this->delete_child_file( $path, $ext );
                                    endif;
                                endforeach;
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                            
                        case 'image_submit':
                            // delete child theme images
                            if ( isset( $_POST[ 'ctc_img' ] ) ):
                                foreach ( $_POST[ 'ctc_img' ] as $file )
                                    $this->delete_child_file( 'images/' . sanitize_text_field( $file ), 'img' );
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                            
                        case 'templates_writable_submit':
                            // make specific files writable ( systems not running suExec )
                            if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                    list( $path, $ext ) = $this->get_pathinfo( sanitize_text_field( $file ) );
                                    $this->set_writable( $path, $ext );
                                endforeach;
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                            
                        case 'set_writable':
                            // make child theme style.css and functions.php writable ( systems not running suExec )
                            $this->set_writable(); // no argument defaults to style.css
                            $this->set_writable( 'functions' );
                            $this->msg = '8&tab=file_options';
                            break;
                        
                        case 'reset_permission':
                            // make child theme read-only ( systems not running suExec )
                            $this->unset_writable();
                            $this->msg = '8&tab=file_options';
                            break;
                        
                        case 'theme_image_submit':
                            // move uploaded child theme images (now we have filesystem access)
                            if ( isset( $_POST[ 'movefile' ] ) ):
                                $this->move_file_upload( 'images' );
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                        
                        case 'theme_screenshot_submit':
                            // move uploaded child theme screenshot (now we have filesystem access)
                            if ( isset( $_POST[ 'movefile' ] ) ):
                                // remove old screenshot
                                foreach( array_keys( $this->imgmimes ) as $extreg ): 
                                    foreach ( explode( '|', $extreg ) as $ext )
                                        $this->delete_child_file( 'screenshot', $ext );
                                endforeach;
                                $this->move_file_upload( '' );
                                $this->msg = '8&tab=file_options';
                            endif;
                            break;
                        default:
                            // assume we are on the files tab so just redirect there
                            $this->msg = '8&tab=file_options';
                    endswitch;
                endif; // end filesystem condition
            endif; // end post validation condition
            // if we have errors, redirect failure
            if ( $this->errors ):
                $this->update_redirect( 0 );
            // if we have filesystem access, redirect successful
            elseif ( empty( $this->fs_prompt ) ):
                $this->processdone = TRUE;
                //die( '<pre><code><small>' . print_r( $_POST, TRUE ) . '</small></code></pre>' );
                // no errors so we redirect with confirmation message
                $this->update_redirect();
            endif;
        endif; // end request method condition
        // if we are here, then this is either a get request or we need filesystem access
    }
    
    function reload(){
        $mode = isset( $_POST[ 'ctc_data_mode' ] ) ? sanitize_text_field( $_POST[ 'ctc_data_mode' ] ) : 'stylesheet';
        $target = 'inline' == $mode ? 'inline' : 'stylesheet';
        $mode = 'frontend';
        $this->set_state( 'mode', $mode );
        $this->set_state( 'target', $target );
        if ( isset( $_POST[ 'ctc_theme_data' ] ) && $_POST[ 'ctc_theme_data' ] != $this->get_current_child() ):
            $this->set_state( 'theme', sanitize_text_field( $_POST[ 'ctc_theme_data' ] ) );
            $this->load_config();
        endif;
        // only update data if it has changed since last save or is empty
        if ( $this->target_modified() ):
            if ( 'inline' == $target ):
                $this->state()->parse_inline();
            elseif ( 'stylesheet' == $target ):
        
                if ( $this->get( 'hasstyles' ) && !$this->get( 'ignoreparnt' ) ):
                    $this->debug( 'Adding action: parse_parent_stylesheet_to_source', __FUNCTION__, __CLASS__ );
                    add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_parent_stylesheet_to_source' ) );
                endif;

                $this->debug( 'Adding action: parse_additional_stylesheets_to_source', __FUNCTION__, __CLASS__ );
                add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_additional_stylesheets_to_source' ) );

                if ( 'separate' == $this->get( 'handling' ) ):

                    // parse child theme style.css into source config and leave unchanged
                    $this->debug( 'Adding action: parse_child_stylesheet_to_source', __FUNCTION__, __CLASS__ );
                    add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_child_stylesheet_to_source' ) );

                    // parse child theme ctc-style.css into target config
                    $this->debug( 'Adding action: parse_custom_stylesheet_to_target', __FUNCTION__, __CLASS__ );
                    add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_custom_stylesheet_to_target' ) );
                elseif ( 'primary' == $this->get( 'handling' ) ):
                    // parse child theme style.css into target config
                    $this->debug( 'Adding action: parse_child_stylesheet_to_target', __FUNCTION__, __CLASS__ );
                    add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_child_stylesheet_to_target' ) );

                endif;
                do_action( 'chld_thm_cfg_parse_stylesheets' );
            endif;
            // write new child stylesheet
            $this->css()->write_css();
        endif;
        $this->save_config();
    }
    
    function rename_child_file( $oldname, $newname, $no_source_ok = FALSE ){
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        // generate full paths to files
        $oldfile = $this->fspath( $this->css()->get_child_target( $oldname ) );
        $newfile = $this->fspath( $this->css()->get_child_target( $newname ) );
        // special case where we are renaming in config but not actually renaming file
        if ( !$wp_filesystem->exists( $oldfile ) ):
            if ( $no_source_ok )
                return TRUE;
            return FALSE;
        endif;

        // rename file using wp_filesystem
        // in case where both files exist, rename will fail, CTC will parse new file and ignore old file.
        if ( $wp_filesystem->move( $oldfile, $newfile ) )
            return TRUE;
        // return true on success, false on failure
        return FALSE;
    }
    
    function render() {
        $this->ui()->render();
    }

    function render_addl_panels( $active_tab = NULL, $hidechild = '', $enqueueset = TRUE ) {
        if ( $child  = $this->get( 'child' ) ):
            $parent = $this->get( 'parnt' );
            $allowed = $this->get_theme_property( $child, 'allowed' ); 
        else:
            $parent = '';
            $allowed = FALSE;
        endif;
        remove_action( 'chld_thm_cfg_panels', array( $this, 'render_addl_panels' ) );
        $filter_input = '';        
        $filter_submit = '';        
        $page = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        $linktext = __( 'Refresh all Selectors', 'child-theme-configurator' );
        $link = '<a style="float:right" id="ctc_reload_selectors" href="' . ( is_multisite() ? 
            network_admin_url( 'themes.php' ) : 
                admin_url( 'tools.php' ) ) . '?page=' . $page . '&tab=all_styles" title="' . $linktext . '" class="button">' . $linktext . '</a>';
        include( CHLD_THM_CFG_PRO_DIR . '/includes/views/pro-addl-panels.php' );
    }
    
    function render_addl_tabs( $active_tab = NULL ) {      
        if ( $child  = $this->css()->get_prop( 'child' ) ):
            $allowed = $this->get_theme_property( $child, 'allowed' ); 
            $page = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        endif;
        remove_action( 'chld_thm_cfg_tabs', array( $this, 'render_addl_tabs' ) );
        include( CHLD_THM_CFG_PRO_DIR . '/includes/views/pro-addl-tabs.php' );
    }
    
    function render_all_selectors() {
        $output = '<ul>' . LF;
        foreach ( $this->css()->sort_queries() as $query => $sort_order ):
            $has_selector = 0;
            $sel_output   = '<li>' . LF;
            $selectors = $this->css()->denorm_dict_qs( $query );
            uasort( $selectors, array( $this->css(), 'cmp_seq' ) );
            $sel_output .=  '<strong>' . $query . '</strong>' . LF . '<ul>';
            foreach ( $selectors as $selid => $qsid ):
                $has_value = 0;
                $has_selector = 1;
                $sel_output .= '<li><a href="#" class="ctc-selector-edit" id="ctc_selector_edit_' . $qsid . '" >' . $selid . '</a></li>' . LF;
            endforeach;
            $sel_output .= '</ul></li>' . LF;
            if ( $has_selector ) $output .= $sel_output;
        endforeach;
        $output .= '</ul>' . LF;
        echo $output;
    }

    function render_file_form() {
        include( CHLD_THM_CFG_PRO_DIR . '/includes/views/pro-file-forms.php' );
    }
        
    function render_file_form_buttons( $template ){
        if ( 'child' == $template ): 
            include( CHLD_THM_CFG_PRO_DIR . '/includes/views/pro-file-form-buttons.php' );
        endif;
    }
    
    function render_sidebar() {
        ?><div class="ctc-recent-container"><div id="ctc_recent_selectors"></div></div><?php
    }
    
    /**
     * for themes with hard-coded stylesheets,
     * change references to stylesheet_uri to template_directory_uri  
     * and move wp_head to end of head if possible
     */
    function repair_header() {
        // return if no flaws detected
        if ( ! $this->get( 'cssunreg' ) && !$this->get( 'csswphead' ) ) return;
        $this->debug( 'repairing parent header', __FUNCTION__, __CLASS__ );
        // try to copy from parent
        $this->copy_parent_file( 'header' );
        // try to backup child header template
        $this->backup_or_restore_file( 'header.php' );
        // fetch current header template
        global $wp_filesystem;
        $cssstr = "get_template_directory_uri()";
        $wphstr = '<?php // MODIFIED BY CTC' . LF . 'wp_head();' . LF . '?>' . LF . '</head>';
        $filename = $this->css()->get_child_target( 'header.php' );
        $contents = $wp_filesystem->get_contents( $this->fspath( $filename ) );
        
        // change hard-wired stylesheet link so it loads parent theme instead
        if ( $this->get( 'cssunreg' ) || $this->get( 'csswphead' ) ):
            $repairs = 0;
            $contents = preg_replace( "#(get_bloginfo\(\s*['\"]stylesheet_url['\"]\s*\)|get_stylesheet_uri\(\s*\))#s", $cssstr . ' . "/style.css"', $contents, -1, $count ); 
            $repairs += $count;
            $contents = preg_replace( "#([^_])bloginfo\(\s*['\"]stylesheet_url['\"]\s*\)#s", "$1echo " . $cssstr . ' . "/style.css"', $contents, -1, $count );
            $repairs += $count;
            $contents = preg_replace( "#([^_])bloginfo\(\s*['\"]stylesheet_directory['\"]\s*\)#s", "$1echo " . $cssstr, $contents, -1, $count );
            $repairs += $count;
            $contents = preg_replace( "#(trailingslashit\()?(\s*)get_stylesheet_directory_uri\(\s*\)(\s*\))?\s*\.\s*['\"]\/?([\w\-\.\/]+?)\.css['\"]#s", 
                "$2echo $cssstr . '$3.css'", $contents, -1, $count );
            $repairs += $count;
            if ( $repairs )
                $this->css()->set_prop( 'parntloaded', TRUE );
        endif;

        // put wp_head() call at the end of <head> section where it belongs
        if ( $this->get( 'csswphead' ) ):
            $contents = preg_replace( "#wp_head\(\s*\)\s*;#s", '', $contents );
            $contents = preg_replace( "#</head>#s", $wphstr, $contents );
            $contents = preg_replace( "#\s*<\?php\s*\?>\s*#s", LF, $contents ); // clean up
        endif;
        
        // write new header template to child theme
        $this->debug( 'Writing to filesystem: ' . $filename . LF . $contents, __FUNCTION__, __CLASS__ );
        if ( FALSE === $wp_filesystem->put_contents( $this->fspath( $filename ), $contents ) ):
            $this->debug( 'Filesystem write failed, returning.', __FUNCTION__, __CLASS__ );
            return FALSE; 
        endif;
        //die( '<textarea>' . $contents . '</textarea>' );
    }
    
    function reset_child_theme() {
        $parnt  = $this->get( 'parnt' );
        $child  = $this->get( 'child' );
        $name   = $this->get( 'child_name' );
        $this->css = new ChildThemeConfiguratorCSS();
        $this->css()->set_prop( 'parnt', $parnt );
        $this->css()->set_prop( 'child', $child );
        $this->css()->set_prop( 'child_name', $name );
        $this->css()->set_prop( 'enqueue', 'enqueue' );
        $this->backup_or_restore_file( 'header.php', TRUE );
        $this->delete_child_file( 'header.ctcbackup', 'php' );
        $this->backup_or_restore_file( 'style.css', TRUE );
        $this->delete_child_file( 'style.ctcbackup', 'css' );
        $this->backup_or_restore_file( $this->get_child_stylesheet(), TRUE );
        $this->delete_child_file( 'ctc-style.ctcbackup', 'css' );
    }
    
    function rewrite_stylesheet_header(){
        $this->debug( LF . LF . 'Rewriting main stylesheet header...', __FUNCTION__, __CLASS__ );
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access, returning', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        $origcss        = $this->css()->get_child_target( 'style.css' );
        $fspath         = $this->fspath( $origcss );
        global $wp_filesystem;
        if( !$wp_filesystem->exists( $fspath ) ): 
            $this->debug( 'No stylesheet, returning', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        // get_contents_array returns extra linefeeds so just split it ourself
        $contents       = $wp_filesystem->get_contents( $fspath );
        $child_headers  = $this->css()->get_css_header();
        if ( is_array( $child_headers ) )
            $regex      = implode( '|', array_map( 'preg_quote', array_keys( $child_headers ) ) );
        else $regex     = 'NO HEADERS';
        $regex          = '/(' . $regex . '):.*$/';
        $this->debug( 'regex: ' . $regex, __FUNCTION__, __CLASS__ );
        $header         = str_replace( "\r", LF, substr( $contents, 0, 8192 ) );
        $contents       = substr( $contents, 8192 );
        $this->debug( 'original header: ' . LF . substr( $header, 0, 1024 ), __FUNCTION__, __CLASS__ );
        //$this->debug( 'stripping @import rules...', __FUNCTION__, __CLASS__ );
        // strip out existing @import lines
        $header = preg_replace( '#\@import\s+url\(.+?\);\s*#s', '', $header );
        // parse header line by line
        $headerdata     = explode( "\n", $header );
        $in_comment     = 0;
        $found_header   = 0;
        $headerdone     = 0;
        $newheader      = '';
        if ( $headerdata ):
            $this->debug( 'parsing header...', __FUNCTION__, __CLASS__ );
            foreach ( $headerdata as $n => $headerline ):
                preg_match_all("/(\*\/|\/\*)/", $headerline, $matches );
                if ( $matches ):
                    foreach ( $matches[1] as $token ): 
                        if ( '/*' == $token ):
                            $in_comment = 1;
                        elseif ( '*/' == $token ):
                            $in_comment = 0;
                        endif;
                    endforeach;
                endif;
                if ( $in_comment ):
                    $this->debug( 'in comment', __FUNCTION__, __CLASS__ );
                    if ( preg_match( $regex, $headerline, $matches ) && !empty( $matches[ 1 ] ) ):
                        $found_header = 1;
                        $key = $matches[ 1 ];
                        $this->debug( 'found header: ' . $key, __FUNCTION__, __CLASS__ );
                        if ( array_key_exists( $key, $child_headers ) ):
                            $this->debug( 'child header value exists: ', __FUNCTION__, __CLASS__ );
                            $value = trim( $child_headers[ $key ] );
                            unset( $child_headers[ $key ] );
                            if ( $value ):
                                $this->debug( 'setting ' . $key . ' to ' . $value, __FUNCTION__, __CLASS__ );
                                $count = 0;
                                $headerline = preg_replace( 
                                    $regex, 
                                    ( empty( $value ) ? '' : $key . ': ' . $value ), 
                                    $headerline
                                );
                            else:
                                $this->debug( 'removing ' . $key, __FUNCTION__, __CLASS__ );
                                continue;
                            endif;
                        endif;
                    endif;
                    $newheader .= $headerline . LF;
                elseif ( $found_header && !$headerdone ): // we have gone in and out of header block; insert any remaining parameters
                    //$this->debug( 'not in comment and after header', __FUNCTION__, __CLASS__ );
                    foreach ( $child_headers as $key => $value ):
                        $this->debug( 'inserting ' . $key . ': ' . $value, __FUNCTION__, __CLASS__ );
                        if ( empty( $value ) ) continue;
                        $newheader .= $key . ': ' . trim( $value ) . "\n";
                    endforeach;
                    // if importing parent, add after this line
                    $newheader .= $headerline . "\n" . $this->css()->get_css_imports();
                    $headerdone = 1;
                else:
                    //$this->debug( 'not in comment', __FUNCTION__, __CLASS__ );
                    $newheader .= $headerline . LF;
                endif;
            endforeach;
            $this->debug( 'new header: ' . LF . substr( $newheader, 0, 1024 ), __FUNCTION__, __CLASS__ );
            if ( !$found_header ) return FALSE;
        endif;
        $contents = $newheader . $contents;
        if ( FALSE === $wp_filesystem->put_contents( $fspath, $contents ) ):
            //$this->debug( 'Filesystem write to ' . $fspath . ' failed.', __FUNCTION__, __CLASS__ );
        else:
            //$this->debug( 'Filesystem write to ' . $fspath . ' successful.', __FUNCTION__, __CLASS__ );
        endif;
        //die( '<pre><code>' . $contents . '</code></pre>');
    }

    /*
     * TODO: this is a stub for future use
     */
    function sanitize_options( $input ) {
        return $input;
    }
    
    /**
     * remove slashes and non-alphas from stylesheet name
     */
    function sanitize_slug( $slug ) {
        return preg_replace( "/[^\w\-]/", '', $slug );
    }
    
    function save_config() {
        // update config data in options API
        $this->css()->save_config();
    }
    
    function save_state ( $key, $value ){
        $this->state()->save_state( $key, $value );

    }
    
    /**
     * check if user has been notified about upgrade 
     */
    function seen_key_notice() {
        return get_user_meta( get_current_user_id(), 'chld_thm_cfg_key_notice', TRUE );
    }
    
    /**
     * check if user has been notified about upgrade 
     */
    function seen_upgrade_notice() {
        $seen_upgrade_version = get_user_meta( get_current_user_id(), 'chld_thm_cfg_upgrade_notice', TRUE );
        return version_compare( $seen_upgrade_version, CHLD_THM_CFG_PREV_VERSION, '>=' );
    }
    
    function serialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = implode( "%%", $_POST[ $field ] );
    }
    
    /**
     * Set the priority of the enqueue hook
     * by matching the hook handle of the primary stylesheet ( thm_parnt_loaded or thm_child_loaded )
     * to the hook handles that were passed by the preview fetched by the analyzer.
     * This allows the stylesheets to be enqueued in the correct order.
     */
    function set_enqueue_priority( $analysis, $baseline ){
        $maxpriority = 10;
        foreach ( $analysis->{ $baseline }->irreg as $irreg ):
            $handles = explode( ',', $irreg );
            $priority = array_shift( $handles );
            if ( isset( $analysis->{ $baseline }->signals->{ 'thm_' . $baseline . '_loaded' } ) 
                && ( $handle = $analysis->{ $baseline }->signals->{ 'thm_' . $baseline . '_loaded' } )
                && in_array( $handle, $handles ) ): // override priority if this is theme stylesheet
                $this->debug( '(baseline: ' . $baseline . ') match: ' . $handle . ' setting priority: ' . $priority, __FUNCTION__, __CLASS__ );
                $this->css()->set_prop( 'qpriority', $priority );
            elseif ( preg_match( '/chld_thm_cfg/', $irreg ) ): // skip if this is ctc handle
                continue;
            endif;
            // update max priority if this is higher
            if ( $priority >= $maxpriority )
                $maxpriority = $priority;
        endforeach;
        // set max priority property
        $this->css()->set_prop( 'mpriority', $maxpriority + 10 );

    }

    function set_premium_theme_mods( $options ){
        foreach( $options as $option_name => $value )
            update_option( $option_name, $value );
    }
    
    function set_recent( $recent ){
        return $this->state()->set_recent( $this->get_mode(), $recent );    
    }
    
    function set_skip_form() {
        $this->skip_form = TRUE;
    }
    
    function set_state( $property, $value ){
        $this->state()->set_property( $property, $value );
    }
    
    function set_theme_mods( $theme, $mods ){
        $active_theme = get_stylesheet();
        $widgets = $mods[ 'sidebars_widgets' ][ 'data' ];
        if ( $active_theme == $theme ):
            $this->debug( $theme . ' active, setting active widgets', __FUNCTION__, __CLASS__ );
            // copy widgets to active sidebars_widgets array
            wp_set_sidebars_widgets( $mods[ 'sidebars_widgets' ][ 'data' ] );
            // if child theme is active, remove widgets from temp array
            unset( $mods[ 'sidebars_widgets' ] );
        else:
            $this->debug( $theme . ' not active, saving widgets in theme mods', __FUNCTION__, __CLASS__ );
            // otherwise copy widgets to temp array with time stamp
            // array value is already set
            //$mods[ 'sidebars_widgets' ][ 'data' ] = $widgets;
            $mods[ 'sidebars_widgets' ][ 'time' ] = time();
        endif;
        //$this->debug( 'saving child theme mods:' . LF . print_r( $mods, TRUE ), __FUNCTION__, __CLASS__ );
        // copy temp array to child mods
        update_option( 'theme_mods_' . $theme, $mods );
    }
    
    function set_writable( $file = NULL ) {

        if ( isset( $file ) ):
            $file =  $this->css()->get_child_target( $file . '.php' );
        else:
            $file =  $this->css()->get_child_target( 'separate' == $this->get( 'handling' ) ? $this->get_child_stylesheet() : 'style.css' );
        endif;
        if ( $this->fs ): // filesystem access
            if ( is_writable( $file ) ) return;
            global $wp_filesystem;
            if ( $file && $wp_filesystem->chmod( $this->fspath( $file ), 0666 ) ) 
                return;
        endif;
        $this->errors[] = 28; //__( 'Could not set write permissions.', 'child-theme-configurator' );
        return FALSE;
    }
    
    /**
     * Handle the creation or update of a child theme
     */
    function setup_child_theme() {
        $this->msg = 1;
        $this->refresh = TRUE;
        // sanitize and extract config fields into local vars
        foreach ( $this->configfields as $configfield ):
            $varparts = explode( '_', $configfield );
            $varname = end( $varparts );
            ${$varname} = empty( $_POST[ 'ctc_' . $configfield ] ) ? '' : 
                preg_replace( "/\s+/s", ' ', sanitize_text_field( $_POST[ 'ctc_' . $configfield ] ) );
            $this->debug( 'Extracting var ' . $varname . ' from ctc_' . $configfield . ' value: ' . ${$varname} , __FUNCTION__, __CLASS__ );
        endforeach;
        if ( isset( $type ) ) $this->childtype = $type;
        
        // validate parent and child theme inputs
        if ( $parnt ):
            if ( ! $this->check_theme_exists( $parnt ) ):
                $this->errors[] = '3:' . $parnt; //sprintf( __( '%s does not exist. Please select a valid Parent Theme.', 'child-theme-configurator' ), $parnt );
            endif;
        else:
            $this->errors[] = 5; // __( 'Please select a valid Parent Theme.', 'child-theme-configurator' );
        endif;

        // if this is reset, duplicate or existing, we must have a child theme
        if ( 'new' != $type && empty( $child ) ):
            $this->errors[] = 6; //__( 'Please select a valid Child Theme.', 'child-theme-configurator' );
        // if this is a new or duplicate child theme we must validate child theme directory
        elseif ( 'new' == $type || 'duplicate' == $type ):
            if ( empty( $template ) && empty( $name ) ):
                $this->errors[] = 7; // __( 'Please enter a valid Child Theme directory name.', 'child-theme-configurator' );
            else:
                $template_sanitized = preg_replace( "%[^\w\-]%", '', empty( $template ) ? $name : $template );
                if ( $this->check_theme_exists( $template_sanitized ) ):
                    $this->errors[] = '8:' . $template_sanitized; //sprintf( __( '<strong>%s</strong> exists. Please enter a different Child Theme template name.', 'child-theme-configurator' ), $template_sanitized );
                elseif ( 'duplicate' == $type ):
                    // clone existing child theme
                    $this->clone_child_theme( $child, $template_sanitized );
                    if ( !empty( $this->errors ) ) return FALSE;
                    /**
                     * using 'updated' get var to indicate theme mods should be copied and the to/from themes
                     */
                    $this->msg = '3,' . $child . ',' . $template_sanitized;
                else:
                    $this->msg = 2;
                endif;
                $child = $template_sanitized;
            endif;
        
        endif;
            
        // verify_child_dir creates child theme directory if it doesn't exist.
        if ( FALSE === $this->verify_child_dir( $child ) ):
            // if it returns false then it could not create directory.
            $this->errors[] = 9; //__( 'Your theme directories are not writable.', 'child-theme-configurator' );
            return FALSE;
        endif;
    

            // if any errors, bail before we create css object
            if ( !empty( $this->errors ) ) return FALSE;
            
            // if no name is passed, create one from the child theme directory
            if ( empty( $name ) ):
                $name = ucfirst( $child );
            endif;
    
            /**
             * before we configure the child theme we need to check if this is a rebuild
             * and compare some of the original settings to the new settings.
             */
            //$oldchild           = $this->get( 'child' );
            //$oldimports         = $this->get( 'imports' );
            //$oldenqueue         = $this->get( 'enqueue' );
            $oldhandling        = $this->get( 'handling' );
            $oldstylesheet      = $this->get( 'sepstylesheet' );
            // reset everything else
            $this->css( TRUE );

            // sanitize separate stylesheet filename
            $sepstylesheet = preg_replace( '{\.\w*$}', '', sanitize_file_name( $sepstylesheet ) ) . '.css';
        
            $this->css()->set_prop( 'enqueue',          $enqueue );
            $this->css()->set_prop( 'handling',         $handling );
            $this->css()->set_prop( 'ignoreparnt',      $ignoreparnt );
            $this->css()->set_prop( 'parnt',            $parnt );
            $this->css()->set_prop( 'child',            $child );
            $this->css()->set_prop( 'child_name',       $name );
            $this->css()->set_prop( 'child_author',     $author );
            $this->css()->set_prop( 'child_themeuri',   $themeuri );
            $this->css()->set_prop( 'child_authoruri',  $authoruri );
            $this->css()->set_prop( 'child_descr',      $descr );
            $this->css()->set_prop( 'child_tags',       $tags );
            $this->css()->set_prop( 'child_version',    strlen( $version ) ? $version : '1.0' );
    
            // set state to defaults
            $this->set_state( 'theme',                  $child );
            $this->set_state( 'mode',                   'frontend' );
            $this->set_state( 'target',                 'stylesheet' );
        
            //if ( isset( $_POST[ 'ctc_action' ] ) && 'plugin' == $_POST[ 'ctc_action' ] ):
            // v.2.3.0 - get all additional stylesheets from checked options
                // this is for PRO plugins
                $this->css()->addl_css = array();
                if ( isset( $_POST[ 'ctc_additional_css' ] ) && is_array( $_POST[ 'ctc_additional_css' ] ) ): 
                    $this->debug( 'addl css from post: ' . print_r( $_POST[ 'ctc_additional_css' ], TRUE ), __FUNCTION__, __CLASS__ );
                    foreach ( $_POST[ 'ctc_additional_css' ] as $file ):
                        //$this->debug( 'adding addl css from post: ' . $file, __FUNCTION__, __CLASS__ );
                        $this->css()->addl_css[] = sanitize_text_field( $file );
                    endforeach;
                endif;
                // v2.3.0 - this action is now handled later
                //add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_child_stylesheet_to_target' ) );
            //else
            if ( isset( $_POST[ 'ctc_analysis' ] ) ):
                // this is for themes
                $this->evaluate_signals();
            endif;
            
            // v2.1.3 - force dependency for specific stylesheets
            $this->css()->forcedep = array();
            if ( isset( $_POST[ 'ctc_forcedep' ] ) && is_array( $_POST[ 'ctc_forcedep' ] ) ): 
                foreach ( $_POST[ 'ctc_forcedep' ] as $handle )
                    $this->css()->forcedep[ sanitize_text_field( $handle ) ] = 1;
            endif;


            // v2.3.1 - do not modify path for stylesheets that swap for child themes
            $this->css()->noswap = array();
            if ( isset( $_POST[ 'ctc_noswap' ] ) && is_array( $_POST[ 'ctc_noswap' ] ) ): 
                foreach ( $_POST[ 'ctc_noswap' ] as $handle ):
                    $this->css()->noswap[ sanitize_text_field( $handle ) ] = 1;
                    $this->debug( 'Disabling link swap: ' . $handle, __FUNCTION__, __CLASS__ );
                endforeach;
            endif;   
        
            // if any errors, bail before we set action hooks or write to filesystem
            if ( !empty( $this->errors ) ) return FALSE;
    
            // override enqueue action for parent theme if it is already being loaded
            if ( 'enqueue' == $enqueue && ( $this->get( 'parntloaded' ) || !$this->get( 'hasstyles' ) || $ignoreparnt ) ) $enqueue = 'none';
                
            // automatically network enable new theme // FIXME: shouldn't this be an option?
            if ( is_multisite() )
                add_action( 'chld_thm_cfg_addl_options', array( $this, 'network_enable' ) );
        
            if ( $this->get( 'hasstyles' ) && !$ignoreparnt ):
                $this->debug( 'Adding action: parse_parent_stylesheet_to_source', __FUNCTION__, __CLASS__ );
                add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_parent_stylesheet_to_source' ) );
            endif;
        
            $this->debug( 'Adding action: parse_additional_stylesheets_to_source', __FUNCTION__, __CLASS__ );
            add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_additional_stylesheets_to_source' ) );
        
            if ( 'separate' == $handling ):

                // if old custom stylesheet different than current custom stylesheet, rename file
                if ( $oldstylesheet != $sepstylesheet )
                    // rename oldstylesheet to sepstylesheet -- fails gracefully if file exists but changes name in config
                    $this->rename_child_file( $oldstylesheet, $sepstylesheet, TRUE );
                $this->css()->set_prop( 'sepstylesheet', $sepstylesheet );

                // parse child theme style.css into source config and leave unchanged
                $this->debug( 'Adding action: parse_child_stylesheet_to_source', __FUNCTION__, __CLASS__ );
                add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_child_stylesheet_to_source' ) );
        
                // parse child theme ctc-style.css into target config
                $this->debug( 'Adding action: parse_custom_stylesheet_to_target', __FUNCTION__, __CLASS__ );
                add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_custom_stylesheet_to_target' ) );
            elseif ( 'primary' == $handling ):
                // parse child theme style.css into target config
                $this->debug( 'Adding action: parse_child_stylesheet_to_target', __FUNCTION__, __CLASS__ );
                add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_child_stylesheet_to_target' ) );
        
                if ( $oldhandling != $handling ):
                    $this->debug( 'Adding action: parse_custom_stylesheet_to_target', __FUNCTION__, __CLASS__ );
                    add_action( 'chld_thm_cfg_parse_stylesheets', array( $this, 'parse_custom_stylesheet_to_target' ) );
                endif;
            endif;
        
        
            // function to support wp_filesystem requirements
            //if ( $this->is_theme( $configtype ) ): // v.2.3.0 - no longer using pluginmode
                // is theme means this is not a plugin stylesheet config
                add_action( 'chld_thm_cfg_addl_files', array( $this, 'add_base_files' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files', array( $this, 'copy_screenshot' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files', array( $this, 'enqueue_parent_css' ), 15, 2 );
                if ( $repairheader ):
                    add_action( 'chld_thm_cfg_addl_files', array( $this, 'repair_header' ) );
                endif;
                
            // plugin hook to parse additional or non-standard files
            do_action( 'chld_thm_cfg_parse_stylesheets' );
            // copy menus, widgets and other customizer options from parent to child if selected
            if ( isset( $_POST[ 'ctc_parent_mods' ] ) && 'duplicate' != $type )
                /**
                 * using 'updated' get var to indicate theme mods should be copied and the to/from themes
                 */
                $this->msg .= ',' . $parnt . ',' . $child;
            // run code generation function in read-only mode to add existing external stylesheet links to config data
            $this->enqueue_parent_css( TRUE );
            // hook for add'l plugin files and subdirectories. Must run after stylesheets are parsed to apply latest options
            do_action( 'chld_thm_cfg_addl_files' );
            // do not continue if errors 
            if ( !empty ( $this->errors ) ) return FALSE;
            //echo ' no errors! saving...' . LF;
            if ( 'separate' == $handling ):
                $this->debug( 'Writing new stylesheet header...', __FUNCTION__, __CLASS__ );
                $this->rewrite_stylesheet_header();
            endif;
            // set flag to skip import link conversion on ajax save
            $this->css()->set_prop( 'converted', 1 );
            
            // try to write new stylsheet. If it fails send alert.
            $this->debug( 'Writing new CSS...', __FUNCTION__, __CLASS__ );
            if ( FALSE === $this->css()->write_css() ):
                //$this->debug( print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ), __FUNCTION__, __CLASS__ );
                $this->errors[] = 11; //__( 'Your stylesheet is not writable.', 'child-theme-configurator' );
                return FALSE;
            endif; 
            // get files to reload templates in new css object
            $this->get_files( $parnt );

        $this->debug( 'Saving new config...', __FUNCTION__, __CLASS__ );
        // save new object to WP options table
        $this->save_config();
        $this->debug( 'Firing additional options action...', __FUNCTION__, __CLASS__ );
        // plugin hook for additional child theme setup functions
        do_action( 'chld_thm_cfg_addl_options' );
        //$this->dump_configs();
        // return message id 1, which says new child theme created successfully;
        $this->msg .= '&tab=parent_child_options';
    }

    function state(){
        return ChildThemeConfiguratorCore::state();
    }
    
    function target_modified(){
        $mode = $this->get_mode();
        if ( 'inline' == $mode ):
            $hash = md5( $this->state()->get_inline_css() );
            $test = $this->state()->get_css_fingerprint();
        else:
            $stylesheet = $this->css()->get_child_target( $this->get_child_stylesheet() );
            if ( !file_exists( $stylesheet ) )
                return TRUE;
            $hash = md5_file( $stylesheet );
            
            $test = $this->get( 'stylesheet' == $mode ? 'fp_stylesheet' : 'fp_editor' );
        endif;
        $this->debug( 'hash saved: ' . $test . ' current: ' . $hash, __FUNCTION__, __CLASS__, __CLASS__ );
        return $test !== $hash;
    }
    
    function theme_basename( $theme, $file ) {
        $file = $this->normalize_path( $file );
        // if no theme passed, returns theme + file
        $themedir = trailingslashit( $this->normalize_path( get_theme_root() ) ) . ( '' == $theme ? '' : trailingslashit( $theme ) );
        //$this->debug( 'Themedir: ' . $themedir . ' File: ' . $file , __FUNCTION__, __CLASS__ );
        return preg_replace( '%^' . preg_quote( $themedir ) . '%', '', $file );
    }
    
    function toggle_debug() {
        $debug = '';
        if ( $_POST[ 'ctc_is_debug' ] ):
            $this->is_debug = 1;
        else:
            $this->is_debug = 0;
        endif;
        $this->state()->set_property( 'debug', $this->is_debug );
        delete_site_transient( CHLD_THM_CFG_OPTIONS . '_debug' );
    }
    
    function ui(){
        return ChildThemeConfiguratorCore::ui();
    }
    
    function unpack_data( $packed, $qsid ){
        try {
            return $this->state()->unpack_data( $packed, $qsid );
        } catch ( Exception $e ){
            $this->debug( 'Unpack failed -- ' . $e->getMessage(), __FUNCTION__, __CLASS__ );
            return FALSE;
        }        
    }
    
    function unserialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && !is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = explode( "%%", $_POST[ $field ] );
    }
    
    function unset_writable() {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $dir        = untrailingslashit( $this->css()->get_child_target( '' ) );
        $child      = $this->theme_basename( '', $dir );
        $newchild   = untrailingslashit( $child ) . '-new';
        $themedir   = trailingslashit( get_theme_root() );
        $fsthemedir = $this->fspath( $themedir );
        // is child theme owned by user? 
        if ( fileowner( $dir ) == fileowner( $themedir ) ):
            $copy   = FALSE;
            $wp_filesystem->chmod( $dir );
            // recursive chmod ( as user )
            // WP_Filesystem RECURSIVE CHMOD IS FLAWED! IT SETS ALL CHILDREN TO PERM OF OUTERMOST DIR
            //if ( $wp_filesystem->chmod( $this->fspath( $dir ), FALSE, TRUE ) ):
            //endif;
        else:
            $copy   = TRUE;
        endif;
        // n -> copy entire folder ( as user )
        $files = $this->css()->recurse_directory( $dir, NULL, TRUE );
        $errors = array();
        foreach ( $files as $file ):
            $childfile  = $this->theme_basename( $child, $this->normalize_path( $file ) );
            $newfile    = trailingslashit( $newchild ) . $childfile;
            $childpath  = $fsthemedir . trailingslashit( $child ) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            if ( $copy ):
                $this->debug( 'Verifying child dir... ' . $file, __FUNCTION__, __CLASS__ );
                if ( $this->verify_child_dir( is_dir( $file ) ? $newfile : dirname( $newfile ) ) ):
                    if ( is_file( $file ) && !$wp_filesystem->copy( $childpath, $newpath ) ):
                        $errors[] = '15:' . $newpath; //'could not copy ' . $newpath;
                    endif;
                else:
                    $errors[] = '16:' . $newfile; //'invalid dir: ' . $newfile;
                endif;
            else:
                $wp_filesystem->chmod( $this->fspath( $file ) );
            endif;
        endforeach;
        if ( $copy ):
            // verify copy ( as webserver )
            $newfiles = $this->css()->recurse_directory( trailingslashit( $themedir ) . $newchild, NULL, TRUE );
            $deleteddirs = $deletedfiles = 0;
            if ( count( $newfiles ) == count( $files ) ):
                // rename old ( as webserver )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child . '-old' ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $child, trailingslashit( $fsthemedir ) . $child . '-old' );
                // rename new ( as user )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $newchild, trailingslashit( $fsthemedir ) . $child );
                // remove old files ( as webserver )
                $oldfiles = $this->css()->recurse_directory( trailingslashit( $themedir ) . $child . '-old', NULL, TRUE );
                array_unshift( $oldfiles, trailingslashit( $themedir ) . $child . '-old' );
                foreach ( array_reverse( $oldfiles ) as $file ):
                    if ( $wp_filesystem->delete( $this->fspath( $file ) ) 
                        || ( is_dir( $file ) && @rmdir( $file ) ) 
                            || ( is_file( $file ) && @unlink( $file ) ) ):
                        $deletedfiles++;
                    endif;
                endforeach;
                if ( $deletedfiles != count( $oldfiles ) ):
                    $errors[] = '17:' . $deletedfiles . ':' . count( $oldfiles ); //'deleted: ' . $deletedfiles . ' != ' . count( $oldfiles ) . ' files';
                endif;
            else:
                $errors[] = 18; //'newfiles != files';
            endif;
        endif;
        if ( count( $errors ) ):
            $this->errors[] = 19; //__( 'There were errors while resetting permissions.', 'child-theme-configurator' ) ;
        endif;
    }
    
    function update_qsid( $qsid ) {
        // update recently modified selectors array
        $recent = (array) $this->get_recent();
        while ( FALSE !== ( $key = array_search( $qsid, $recent ) ) ) 
            unset( $recent[ $key ] );
        array_unshift( $recent, $qsid );
        if ( count( $recent ) > $this->recent_count ) 
            array_pop( $recent );
        $this->set_recent( $recent );
    }
    
    function update_redirect() {
        $this->log_debug();
        if ( empty( $this->is_ajax ) ):
            $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
            $screen = get_current_screen()->id;
            wp_safe_redirect(
                ( strstr( $screen, '-network' ) ? network_admin_url( 'themes.php' ) : admin_url( 'tools.php' ) ) 
                    . '?page=' . $ctcpage . ( $this->errors ? '&error=' . implode( ',', $this->errors ) : ( $this->msg ? '&updated=' . $this->msg : '' ) ) );
            die();
        endif;
    }
    
    function upgrade_notice() {
        $this->ui()->render_notices( 'upgrade' );
    }
    
    function uploads_basename( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $this->normalize_path( $uplarr[ 'basedir' ] ) );
        return preg_replace( '%^' . preg_quote( $upldir ) . '%', '', $file );
    }
    
    function uploads_fullpath( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $this->normalize_path( $uplarr[ 'basedir' ] ) );
        return $upldir . $file;
    }
    
    function validate_post( $action = 'ctc_update', $noncefield = '_wpnonce', $cap = 'install_themes' ) {
        // v.2.3.0 moved to state
        return $this->state()->validate_post( $action, $noncefield, $cap );
    }
    
    function verify_child_dir( $path ) {
        $this->debug( 'Verifying child dir: ' . $path, __FUNCTION__, __CLASS__ );
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        $themedir = $wp_filesystem->find_folder( get_theme_root() );
        if ( ! $wp_filesystem->is_writable( $themedir ) ):
            $this->debug( 'Directory not writable: ' . $themedir, __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        $childparts = explode( '/', $this->normalize_path( $path ) );
        while ( count( $childparts ) ):
            $subdir = array_shift( $childparts );
            if ( empty( $subdir ) ) continue;
            $themedir = trailingslashit( $themedir ) . $subdir;
            if ( ! $wp_filesystem->is_dir( $themedir ) ):
                if ( ! $wp_filesystem->mkdir( $themedir, FS_CHMOD_DIR ) ):
                $this->debug( 'Could not make directory: ' . $themedir, __FUNCTION__, __CLASS__ );
                    return FALSE;
                endif;
            elseif ( ! $wp_filesystem->is_writable( $themedir ) ):
                $this->debug( 'Directory not writable: ' . $themedir, __FUNCTION__, __CLASS__ );
                return FALSE;
            endif;
        endwhile;
        $this->debug( 'Child dir verified: ' . $themedir, __FUNCTION__, __CLASS__ );
        return TRUE;
    }
    
    /*
     *
     */
    function verify_creds( $args = array() ) {
        $this->fs_prompt = $this->fs = FALSE;
        // fs prompt does not support arrays as post data - serialize arrays
        $this->serialize_postarrays();
        // generate callback url
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        $url = is_multisite() ?  network_admin_url( 'themes.php?page=' . $ctcpage ) :
            admin_url( 'tools.php?page=' . $ctcpage );
        $nonce_url = wp_nonce_url( $url, apply_filters( 'chld_thm_cfg_action', 'ctc_update' ), '_wpnonce' );
        // buffer output so we can process prior to http header
        ob_start();
        if ( $creds = request_filesystem_credentials( $nonce_url, '', FALSE, FALSE, $args ) ):
            // check filesystem permission if direct or ftp creds exist
            if ( WP_Filesystem( $creds ) )
                // login ok
                $this->fs = TRUE;
            else
                // incorrect credentials, get form with error flag
                $creds = request_filesystem_credentials( $nonce_url, '', TRUE, FALSE, $args );
        else:
            // no credentials, initialize unpriveledged filesystem object
            WP_Filesystem();
        endif;
        // if form was generated, store it
        $this->fs_prompt = ob_get_clean();
        $this->debug( 'FS: ' . $this->fs . ' PROMPT: ' . $this->fs_prompt, __FUNCTION__, __CLASS__ );
        // now we can read/write if fs is TRUE otherwise fs_prompt will contain form
        // fs prompt does not support arrays as post data - unserialize arrays
        $this->unserialize_postarrays();
    }
    
    function verify_target(){
        if ( 'inline' != $this->get_mode() ):
            $stylesheet = $this->css()->get_child_target( $this->get_child_stylesheet() );
            $this->debug( 'mode:' . $this->get_mode() . ' stylesheet: ' . $stylesheet, __FUNCTION__, __CLASS__ );
            // check file permissions
            if ( !is_writable( $stylesheet ) && !$this->fs )
                add_action( 'chld_thm_cfg_admin_notices', array( $this, 'writable_notice' ) );
        endif;
        if ( $this->target_modified() )
            add_action( 'chld_thm_cfg_admin_notices', array( $this, 'changed_notice' ) );
    }
    
    function writable_notice() {
        $this->ui()->render_notices( 'writable' );
    }
    
    // creates/updates file via filesystem API
    function write_child_file( $file, $contents ) {
        //$this->debug( LF . LF, __FUNCTION__, __CLASS__ );
        if ( !$this->fs ): 
            $this->debug( 'No filesystem access, returning.', __FUNCTION__, __CLASS__ );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        if ( $file = $this->css()->is_file_ok( $this->css()->get_child_target( $file ), 'write' ) ):
            $mode = 'direct' == $this->fs_method ? FALSE : 0666;
            $file = $this->fspath( $file );
            if ( $wp_filesystem->exists( $file ) ):
                $this->debug( 'File exists, returning.', __FUNCTION__, __CLASS__ );
                return FALSE;
            else:
                $this->debug( 'Writing to filesystem: ' . $file . LF . $contents, __FUNCTION__, __CLASS__ );
                if ( FALSE === $wp_filesystem->put_contents( 
                    $file, 
                    $contents,
                    $mode 
                    ) ):
                    $this->debug( 'Filesystem write failed, returning.', __FUNCTION__, __CLASS__ );
                    return FALSE; 
                endif;
            endif;
        else:
            $this->debug( 'No directory, returning.', __FUNCTION__, __CLASS__ );
            return FALSE;
        endif;
        $this->debug( 'Filesystem write successful.', __FUNCTION__, __CLASS__ );
    }
    
}
