<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

    class ChildThemeConfiguratorCore {
        
        static $ctcinstance;
        static $cssinstance;
        static $stateinstance;
        static $uiinstance;
        static $plugin  = 'child-theme-configurator/child-theme-configurator.php';
        static $ctcpro  = 'child-theme-configurator-pro/child-theme-configurator-pro.php';
        static $oldpro  = 'child-theme-configurator-plugins/child-theme-configurator-plugins.php';
        
        static function action_links( $actions ) {
            $actions[] = '<a href="' . admin_url( 'tools.php?page=' . CHLD_THM_CFG_MENU ). '">' 
                . __( 'Child Themes', 'child-theme-configurator' ) . '</a>' . LF;
            return $actions;
        }
        
        static function admin() {
            $hook = add_management_page(
                    __( 'Child Theme Configurator', 'child-theme-configurator' ), 
                    __( 'Child Themes', 'child-theme-configurator' ), 
                    'install_themes', 
                    CHLD_THM_CFG_MENU, 
                    'ChildThemeConfiguratorCore::render' 
            );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ChildThemeConfiguratorCore::action_links' );
            add_action( 'load-' . $hook, 'ChildThemeConfiguratorCore::page_init' );        
        }
        
        static function analyze() {
            self::ctc()->ajax_analyze();
        }
    
        static function ctc() {
            // create admin object
            if ( !isset( self::$ctcinstance ) ):
                self::$ctcinstance = new ChildThemeConfiguratorAdmin( __FILE__ );
            endif;
            return self::$ctcinstance;
        }
        
        static function css( $reset = FALSE ) {
            // create CSS object
            if ( $reset || !isset( self::$cssinstance ) ):
                self::$cssinstance = new ChildThemeConfiguratorCSS();
            endif;
            return self::$cssinstance;
        }
        
        static function state(){
            if ( !isset( self::$stateinstance ) ):
                self::$stateinstance = new ChildThemeConfiguratorState();
                //$this->debug( 'state initialized: ' . print_r( $this->state, TRUE ), __FUNCTION__, __CLASS__ );
            endif;
            return self::$stateinstance;
        }

        static function ui() {
            // create UI object
            if ( !isset( self::$uiinstance ) ):
                self::$uiinstance = new ChildThemeConfiguratorUI();
            endif;
            return self::$uiinstance;
        }
        
        static function deactivate_community_version(){
            if ( isset( $_GET[ 'action' ] )
                && 'activate' == $_GET[ 'action' ]
                && isset( $_GET[ 'plugin' ] )
                && self::$plugin == $_GET[ 'plugin' ] ):
                unset( $_GET[ 'action' ] );
            endif;
            if ( isset( $_GET[ 'activate' ] ) )
                unset( $_GET[ 'activate' ] );
            deactivate_plugins( self::$plugin, FALSE, is_network_admin() );
        }

        static function deactivate_ctc_pro() {
            if ( isset( $_GET[ 'action' ] ) && 'activate' == $_GET[ 'action' ] && self::$ctcpro == $_GET[ 'plugin' ] )
                unset( $_GET[ 'action' ] );
            elseif ( isset( $_GET[ 'activate' ] ) )
                unset( $_GET[ 'activate' ] );
            if ( current_user_can( 'activate_plugins' ) )
                deactivate_plugins( self::$ctcpro );
        }

        /**
         * deletes old CTC Pro plugin without removing option settings
         */
        static function delete_old_ctc_pro() {
            if ( isset( $_REQUEST[ 'deleted' ] ) ) return;
            // clean up hooks from < 2.2.0
    		wp_clear_scheduled_hook( 'check_plugin_updates-ctc-plugins' );
    		wp_clear_scheduled_hook( 'check_plugin_updates-child-theme-configurator-plugins' );
    		delete_option( 'external_updates-ctc-plugins' );
    		delete_option( 'external_updates-child-theme-configurator-plugins' );
            // remove old Pro version
            if ( current_user_can( 'delete_plugins' ) ):
                $redir = NULL;
                foreach( array( self::$plugin, self::$oldpro ) as $pluginfile ):
                    $plugindir = '/' . dirname( $pluginfile );
					$full_plugin_path = WP_PLUGIN_DIR . $plugindir;
                    $has_plugin = file_exists( $full_plugin_path ); // && get_plugins( $plugindir );
                    if ( $has_plugin ):
                        if ( isset( $_GET[ 'action' ] ) ): 
                            // unset action parameter if it is for old CTC Pro
                            if ( 'activate' == $_GET[ 'action' ]
                                && isset( $_GET[ 'plugin' ] ) 
                                && $pluginfile == $_GET[ 'plugin' ] ):
                                unset( $_GET[ 'action' ] );
                            // handle two-step FTP Authentication form
                            elseif ( 'delete-selected' == $_GET[ 'action' ] 
                                && isset( $_GET[ 'verify-delete' ] ) 
                                && isset( $_GET[ 'checked' ] ) 
                                && $pluginfile == $_GET[ 'checked' ][ 0 ] ):

                                unset( $_GET[ 'action' ] );
                                unset( $_GET[ 'checked' ] );
                                unset( $_GET[ 'verify-delete' ] );
                                unset( $_REQUEST[ 'action' ] );
                                unset( $_REQUEST[ 'checked' ] );
                                unset( $_REQUEST[ 'verify-delete' ] );

                                $redir = self_admin_url( "plugins.php?activate=true" ); 
                            elseif ( 'activate' != $_GET[ 'action' ] ):
                                return;
                            endif;
                        endif;
                        // deactivate old Pro version
                        deactivate_plugins( $pluginfile );
                        // remove uninstall hook so that options are preserved
                        $uninstallable_plugins = (array) get_option( 'uninstall_plugins' );
                        if ( isset( $uninstallable_plugins[ $pluginfile ] ) ):
                            unset( $uninstallable_plugins[ $pluginfile ] );
                            update_option( 'uninstall_plugins', $uninstallable_plugins );
                        endif;
                        unset( $uninstallable_plugins );
            
                        // remove old Pro version
                        $delete_result = delete_plugins( array( $pluginfile ) );
                        //Store the result in a cache rather than a URL param due to object type & length
                        global $user_ID;
                        set_transient( 'plugins_delete_result_' . $user_ID, $delete_result ); 
                        // force plugin cache to reload
                        wp_cache_delete( 'plugins', 'plugins' );

                        // if this is two-step FTP authentication, redirect back to activated
                        if ( $redir ):
                            if ( is_wp_error( $delete_result ) )
                                $redir = self_admin_url( "plugins.php?deleted=" . $pluginfile );
                            wp_redirect( $redir );
                            exit;
                        endif;
                    endif;
                endforeach;
            endif;
        }
        
        static function dismiss() {
            self::ctc()->ajax_dismiss_notice();
        }
    
        static function dismiss_key_notice() {
            self::ctc()->ajax_dismiss_key_notice();
        }
        
        static function filter_version( $src, $handle ) {
            if ( is_child_theme() && strstr( $src, get_stylesheet() ) && ( $ver = wp_get_theme()->Version ) ):
                $src = preg_replace( "/ver=(.*?)(\&|$)/", 'ver=' . $ver . "$2", $src );
            endif;
            return $src;
        }

        static function init() {
            // Deactivate community version if it is still installed
            if ( defined( 'CHLD_THM_CFG_OPTIONS' ) ):
                add_action( 'admin_init', 'ChildThemeConfiguratorCore::deactivate_community_version' );
                return;
            endif;


            // retained from community CTC
            defined( 'LF' ) or define( 'LF', "\n" );
            defined( 'CHLD_THM_CFG_DIR' ) or define( 'CHLD_THM_CFG_DIR', CHLD_THM_CFG_PRO_DIR );
            defined( 'CHLD_THM_CFG_URL' ) or define( 'CHLD_THM_CFG_URL', CHLD_THM_CFG_PRO_URL );
            defined( 'CHLD_THM_CFG_OPTIONS' ) or define( 'CHLD_THM_CFG_OPTIONS', 'chld_thm_cfg_options' );


            /**
             * BEGIN Community CTC Core init
             */
            defined( 'LILAEAMEDIA_URL' ) or 
            define( 'LILAEAMEDIA_URL',                  "http://www.lilaeamedia.com" );
            defined( 'CHLD_THM_CFG_DOCS_URL' ) or 
            define( 'CHLD_THM_CFG_DOCS_URL',            "http://www.childthemeplugin.com" );
            define( 'CHLD_THM_CFG_VERSION',             '2.3.8' );
            define( 'CHLD_THM_CFG_PREV_VERSION',        '1.7.9.1' );
            define( 'CHLD_THM_CFG_MIN_WP_VERSION',      '3.7' );
            define( 'CHLD_THM_CFG_PRO_MIN_VERSION',     '2.2.0' );
            defined( 'CHLD_THM_CFG_BPSEL' ) or 
            define( 'CHLD_THM_CFG_BPSEL',               '2500' );
            defined( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS' ) or 
            define( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS',   '1000' );
            defined( 'CHLD_THM_CFG_MENU' ) or 
            define( 'CHLD_THM_CFG_MENU',                'chld_thm_cfg_menu' );
            
            // verify WP version support
            global $wp_version;
            if ( version_compare( $wp_version, CHLD_THM_CFG_MIN_WP_VERSION, '<' ) ):
                add_action( 'all_admin_notices', 'ChildThemeConfiguratorCore::version_notice' );
                return;
            endif;

            // setup admin hooks
            if ( is_multisite() )
                add_action( 'network_admin_menu',   'ChildThemeConfiguratorCore::network_admin' );
            add_action( 'admin_menu',               'ChildThemeConfiguratorCore::admin' );
            // add plugin upgrade notification
            add_action( 'in_plugin_update_message-' . self::$plugin, 
                                                    'ChildThemeConfiguratorCore::upgrade_notice', 10, 2 );
            // setup ajax actions
            add_action( 'wp_ajax_ctc_update',       'ChildThemeConfiguratorCore::save' );
            add_action( 'wp_ajax_ctc_query',        'ChildThemeConfiguratorCore::query' );
            add_action( 'wp_ajax_ctc_dismiss',      'ChildThemeConfiguratorCore::dismiss' );
            /* removed v.2.3.0
            add_action( 'wp_ajax_pro_dismiss',      'ChildThemeConfiguratorUpgrade::ajax_dismiss_notice' );
            */
            add_action( 'wp_ajax_ctc_analyze',      'ChildThemeConfiguratorCore::analyze' );
            
            // initialize languages
            add_action( 'init',                     'ChildThemeConfiguratorCore::lang' );
            
            // prevent old Pro activation
            if ( isset( $_GET[ 'action' ] ) 
                && isset( $_GET[ 'plugin' ] ) 
                && 'activate' == $_GET[ 'action' ] 
                && self::$oldpro == $_GET[ 'plugin' ] )
                unset( $_GET[ 'action' ] );
        
            /**
             * END Community CTC Core init
             */
            
            load_plugin_textdomain( 'child-theme-configurator', FALSE, basename( CHLD_THM_CFG_PRO_DIR ) . '/lang' );
            add_action( 'admin_init',                       'ChildThemeConfiguratorCore::delete_old_ctc_pro' );
            // add plugin upgrade notification
            add_action( 'in_plugin_update_message-' . self::$ctcpro, 
                                                            'ChildThemeConfiguratorCore::upgrade_notice', 10, 2 );
            if ( ( $ukey = trim( self::ctc()->options[ 'update_key' ] ) ) && !empty( $ukey ) && strlen( $ukey ) > 10 ):
                include_once( CHLD_THM_CFG_PRO_DIR . '/includes/puc/plugin-update-checker.php' );
                new PluginUpdateChecker(
                    'http://www.lilaeamedia.com/updates/update.php?product=child-theme-configurator-pro&key=' . $ukey,
                    CHLD_THM_CFG_PRO_FILE
                );
            else:
                add_action( 'chld_thm_cfg_admin_notices',            'ChildThemeConfiguratorCore::key_notice' );
            endif;
            // setup admin hooks
            add_action( 'wp_ajax_ctc_plugin',               'ChildThemeConfiguratorCore::save' );
            add_action( 'wp_ajax_ctc_plgqry',               'ChildThemeConfiguratorCore::query' );
            add_action( 'wp_ajax_ctc_keynotice',            'ChildThemeConfiguratorCore::dismiss_key_notice' );
        }

        static function key_notice() {
            if ( self::ctc()->seen_key_notice() )
                return;
            $contact = '<a href="' . LILAEAMEDIA_URL . '/contact" target="_blank">' . __( 'visit our website', 'child-theme-configurator' ) . '</a>';
            $register = __( 'Register', 'child-theme-configurator-pro' );
            $ctcpage = sprintf( '<a href="%s?page=%s&tab=register" title="%s">%s</a>',
                ( is_multisite() 
                    ? network_admin_url( 'themes.php' ) 
                    : admin_url( 'tools.php' ) ),
                CHLD_THM_CFG_MENU,
                $register,
                $register );
    ?>
    <div class="notice-warning notice is-dismissible ctc-key-notice">
      <p>
    <?php printf( __( '<strong>Child Theme Configurator Pro:</strong> To enable updates, please select the "%s" tab and enter the Update Key provided with your order. WordPress will then be notified when updates are available. For assistance please %s.', 'child-theme-configurator' ), 
    $ctcpage,
    $contact ); ?>
      </p>
    </div>
    <?php
        }
        
        static function lang() {
            // initialize languages
            load_plugin_textdomain( 'child-theme-configurator', FALSE, basename( CHLD_THM_CFG_DIR ) . '/lang' );
        }
        
        static function network_admin() {
            $hook = add_theme_page( 
                    __( 'Child Theme Configurator', 'child-theme-configurator' ), 
                    __( 'Child Themes', 'child-theme-configurator' ), 
                    'install_themes', 
                    CHLD_THM_CFG_MENU, 
                    'ChildThemeConfiguratorCore::render' 
            );
            add_action( 'load-' . $hook, 'ChildThemeConfiguratorCore::page_init' );
        }
        
        static function page_init() {
            // start admin controller
            self::ctc()->ctc_page_init();
        }
        
        static function query() {
            // ajax read
            self::ctc()->ajax_query_css();
        }
                
        static function render() {
            // display admin page
            self::ctc()->render();
        }
    
        static function save() {
            // ajax write
            self::ctc()->ajax_save_postdata();
        }

        static function upgrade_notice( $current, $new ){
           if ( isset( $new->upgrade_notice ) && strlen( trim ( $new->upgrade_notice ) ) )
                echo '<p style="background-color:#d54d21;padding:1em;color:#fff;margin: 9px 0">'
                    . esc_html( $new->upgrade_notice ) . '</p>';
        }
        
        static function version_notice() {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            unset( $_GET[ 'activate' ] );
            echo '<div class="notice-warning notice is-dismissible"><p>' . 
                sprintf( __( 'Child Theme Configurator requires WordPress version %s or later.', 'child-theme-configurator' ), 
                CHLD_THM_CFG_MIN_WP_VERSION ) . '</p></div>' . LF;
        }

    }
    
