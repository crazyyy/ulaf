<?php
/**
 * Admin Bar hooks
 *
 * @package AdminTweaks
 */

namespace ADTW;

class HooksAdminBar {
    /**
     * Check options and dispatch hooks
     * 
     * @param  array $options
     * @return void
     */
    public function __construct() {
        # COMPLETELY REMOVE
        if( ADTW()->getOption('adminbar_completely_disable') ) {
            add_action( 
                'init', 
                [$this, 'removeAdminBar'], 
                0  
            );
            add_action( 
                'admin_menu', 
                [$this, 'visitSite'] 
            );
            add_action( 
                'load-dashboard_page_go-home', 
                [$this, 'goHome'] 
            );
        }

        # DISABLE IN FRONT END
        if( ADTW()->getOption('adminbar_disable') ) {
            add_filter( 
                'show_admin_bar', 
                '__return_false', 
                999 
            );
        }

        # REMOVE DEFAULT ITEMS
        if( ADTW()->getOption('adminbar_remove') ) {
            add_action( 
                'wp_before_admin_bar_render', 
                [$this, 'removeItems'], 
                999999 
            );
        }

        # MODIFY HOWDY
        if( ADTW()->getOption('adminbar_howdy_enable') ) {
            add_action( 
                'admin_bar_menu', 
                [$this, 'goodbyeHowdy'],
                9999
            );
        }    

        # SITE NAME WITH ICON
        if( ADTW()->getOption('adminbar_sitename_enable') ) {
            add_action( 
                'admin_bar_menu', 
                [$this, 'siteName'], 
                10 
            );
        }
        
        # ADMIN TWEAKS SHORTCUT
        if( ADTW()->getOption('adminbar_adtw_enable') 
            && current_user_can( 'manage_options' ) ) {
            add_action( 
                'admin_bar_menu', 
                [$this, 'adtwShortcut'], 
                999999 
            );
        }

        # ADD CUSTOM MENU
        if( ADTW()->getOption('adminbar_custom_enable') ) {
            add_action( 
                'admin_bar_menu', 
                [$this, 'customMenu'], 
                99999 
            );
        }
    }


    /**
     * Completely remove the Admin Bar
     * https://wordpress.stackexchange.com/a/77648/12615
     */
    function removeAdminBar() {
        wp_deregister_script( 'admin-bar' );
        wp_deregister_style( 'admin-bar' );
        
        remove_action( 'init', '_wp_admin_bar_init' );
        remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
        remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );

        foreach( ['admin_head', 'wp_head'] as $hook ) {
            add_action( $hook, [$this, 'hideAdminBarCSS'] );
        }
    }


    /**
     * Extra link if Admin Bar removed
     * 
     * @global type $submenu
     */
    public function visitSite() {
        add_submenu_page(
            'index.php', 
            'Visit site', 
            'Visit site', 
            'read', 
            'go-home', 
            '__return_true'
        );
    }


    /**
     * Submenu linking to home page, if admin bar disabled
     *  
     */
    public function goHome() { 
        wp_redirect( home_url() ); 
        exit;
    }

    /**
     * Adjust CSS if Admin Bar completely removed
     * 
     */
    public function hideAdminBarCSS() {
        echo '<style>body.admin-bar #wpwrap
{padding-top: 0px !important; position:absolute; top: 0px;} #wpadminbar {display:none}</style>';
    }

    /**
     * Aux for the howdy stuff
     *
     * @param string $input_string
     * @return void
     */
    private function _add_comma_if_missing($input_string) {
        // Remove any trailing whitespace
        $trimmed = trim($input_string);
        
        // Check if the string ends with a comma
        if (!preg_match('/,$/', $trimmed)) {
            // If not ending with a comma, add one
            return $trimmed . ',';
        }
        
        // If it already ends with a comma, return as is
        return $trimmed;
    }
    
    /**
     * Remove or modify Howdy
     * 
     * @param type $wp_admin_bar
     * @return type
     */
    public function goodbyeHowdy( $wp_admin_bar ) {
        $avatar = get_avatar( get_current_user_id(), 16 );
        if( !$wp_admin_bar->get_node( 'user-actions' ) )
            return;
    
        $howdy = ADTW()->getOption('adminbar_howdy_text') 
                ?  $this->_add_comma_if_missing(ADTW()->getOption('adminbar_howdy_text'))
                : '';
        $original = 'Howdy,';
        if ( !empty(ADTW()->getOption('adminbar_howdy_original_text')) && ADTW()->is_translation() ) {
            $original = $this->_add_comma_if_missing( ADTW()->getOption('adminbar_howdy_original_text') );
        }
        
        $my_account = $wp_admin_bar->get_node('my-account');
        if ( $my_account ) {
            $my_account->title = str_replace( $original, $howdy, $my_account->title );
            $wp_admin_bar->add_node( $my_account );
        }
    }


    /**
     * Remove items from Admin Bar
     * 
     * @global type $wp_admin_bar
     */
    public function removeItems() {
        global $wp_admin_bar;
        foreach ( ADTW()->getOption('adminbar_remove') as $item ) {
            $wp_admin_bar->remove_menu($item);
        }
    }

    /**
     * Add shortcut to Admin Tweaks settings page
     * 
     * @global type $wp_admin_bar
     */
    public function adtwShortcut( \WP_Admin_Bar $admin_bar ) {
        $admin_bar->add_menu( array(
            'id'    => 'admin-tweaks-bar',
            'parent' => null,//'top-secondary'
            'group'  => null,
            'title' => '<div class="wp-menu-image dashicons-before dashicons-admin-settings" aria-hidden="true"><i style="display:none">Admin Tweaks Shortcut</i></div>', 
            'href'  => admin_url('admin.php?page=admintweaks'),
            'meta' => [
                'title' => AdminTweaks::NAME,
            ]
        ) );        
    }
        
    /**
     * Add Site Name to Admin Bar
     * 
     * @global type $wp_admin_bar
     */
    public function siteName() {
        global $wp_admin_bar;
        
        $title = ADTW()->getOption('adminbar_sitename_title');
        $icon  = 
                ADTW()->getOption('adminbar_sitename_img') 
                ? ADTW()->getOption('adminbar_sitename_img')['url'] : '';
        $url   = ADTW()->getOption('adminbar_sitename_url');


        $do_title = 
                ( $icon != '' ) 
                ? '<img src="' 
                    . $icon 
                    . '" style="vertical-align:middle;margin:0 8px 0 6px;max-width:24px;max-height:24px"/>' 
                : '';
        $do_title .= ( $title != '') ? $title : get_bloginfo( 'name' );
        
        $do_title = ( empty( $url ) && !is_admin() ) ? $do_title . esc_html__( ' : Admin') : $do_title;
        $default_url = !is_admin() ? get_admin_url() : get_site_url();
        $do_url   = ( $url != '') ? $url : $default_url;
        $wp_admin_bar->add_menu( array(
                'id'    => 'adtw-site-name',
                'title' => $do_title,
                'href'  => $do_url
        ) );
    }

    /**
     * Add custom menu to Admin Bar
     * 
     * @global type $wp_admin_bar
     * @return type
     */
    public function customMenu() {
        global $wp_admin_bar;
        
        // ERROR, empty array
        if( ADTW()->getOption('adminbar_custom_item_name') === false )
            return;

        $links = ADTW()->getOption('adminbar_custom_item_url');
        $titles = ADTW()->getOption('adminbar_custom_item_name');
        
        // PARENT      
        $wp_admin_bar->add_menu([
            'id'    => 'MTT',
            'title' => $titles[0],
            'href'  => !empty($links[0]) ? $links[0] : '#'
        ]);
        
        if ( count($titles) < 2 ) return; // Just parent link
        
        // SUBMENUS
        for ($i = 1; $i < count($titles); $i++) {
            $wp_admin_bar->add_menu([
                'parent' => 'MTT',
                'id'     => "MTT-$i",
                'title' => $titles[$i],
                'href'  => !empty($links[$i]) ? $links[$i] : '#'
            ]);
            
        }
    }

}