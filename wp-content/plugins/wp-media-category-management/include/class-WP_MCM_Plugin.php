<?php

/**
 * WP_MCM_Plugin class.
 *
 * @package   WP_MCM_Plugin
 * @author    De B.A.A.T. <wp-mcm@de-baat.nl>
 */
if ( !class_exists( 'WP_MCM_Plugin' ) ) {
    class WP_MCM_Plugin {
        public $wp_mcm_date_format;

        public $wp_mcm_time_format;

        public $wp_mcm_datetime_format;

        /**
         * Variable to hold the shortcode attributes for global processing
         *
         * @since    2.0.0
         *
         * @var      array
         */
        var $mcm_shortcode_attributes = '';

        /**
         * Class constructor.
         */
        function __construct() {
            // Do some includes and initializations
            $this->includes();
            $this->initialize();
            $this->add_hooks_and_filters();
        }

        /**
         * Include the required files.
         *
         * @since 2.0.0
         * @return void
         */
        public function includes() {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Create objects for options
            WP_MCM_create_object( 'WP_MCM_Options', 'include/' );
            // Create objects for functionality
            WP_MCM_create_object( 'WP_MCM_Media', 'include/media/' );
            WP_MCM_create_object( 'WP_MCM_Taxonomy', 'include/taxonomy/' );
            WP_MCM_create_object( 'WP_MCM_Shortcode', 'include/shortcode/' );
            if ( is_admin() ) {
                // require_once( WP_MCM_PLUGIN_DIR . 'include/admin/class-WP_MCM_Admin.php' );
                WP_MCM_create_object( 'WP_MCM_Admin', 'include/admin/' );
            }
        }

        /**
         * Run these things during invocation. (called from base object in __construct)
         */
        protected function initialize() {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // Get date and time formats
            $this->wp_mcm_date_format = get_option( 'date_format' );
            if ( empty( $this->wp_mcm_date_format ) ) {
                $this->wp_mcm_date_format = 'dd-mm-yyyy';
            }
            $this->wp_mcm_time_format = get_option( 'time_format' );
            if ( empty( $this->wp_mcm_time_format ) ) {
                $this->wp_mcm_time_format = 'H:i';
            }
            $this->wp_mcm_datetime_format = $this->wp_mcm_date_format . ' ' . $this->wp_mcm_time_format;
            // Configure some settings
            // $this->mcm_register_media_taxonomy();
        }

        /**
         * Add cross-element hooks & filters.
         *
         * Haven't yet moved all items to the AJAX and UI classes.
         */
        function add_hooks_and_filters() {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            add_action( 'init', array($this, 'wp_mcm_load_plugin_textdomain') );
            // Add the options dismiss_notice.
            add_action( 'wp_ajax_mcm_dismiss_notice', array($this, 'mcm_action_ajax_mcm_dismiss_notice') );
            // Load admin style sheet and scripts.
            add_action( 'wp_enqueue_scripts', array($this, 'add_frontend_styles') );
        }

        /**
         * Load the required css styles.
         *
         * @since 2.0.0
         * @return void
         */
        public function add_frontend_styles() {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            wp_enqueue_style(
                'wp-mcm-styles',
                WP_MCM_PLUGIN_URL . '/css/wp-mcm-styles.css',
                array(),
                WP_MCM_VERSION_NUM . '.1'
            );
        }

        /**
         * Load the translations from the language folder.
         *
         * @since 2.0.0
         * @return void
         */
        public function wp_mcm_load_plugin_textdomain() {
            $domain = 'wp-media-category-management';
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
            // Load the language file from the /wp-content/languages/wp-media-category-management folder, custom + update proof translations
            load_textdomain( $domain, WP_LANG_DIR . '/wp-media-category-management/' . $domain . '-' . $locale . '.mo' );
            // Load the language file from the /wp-content/plugins/wp-media-category-management/languages/ folder
            load_plugin_textdomain( $domain, false, dirname( WP_MCM_BASENAME ) . '/languages/' );
        }

        /**
         * Create a timestamp for the current time
         *
         * @return timestamp
         */
        function create_timestamp_now( $timezone_format = '' ) {
            if ( $timezone_format === '' ) {
                $timezone_format = esc_html_x( 'Y-m-d H:i:s', 'timezone date format', 'wp-media-category-management' );
            }
            return wp_date( $timezone_format );
        }

        /**
         * Dismiss notice.
         *
         * @return void
         */
        public function mcm_action_ajax_mcm_dismiss_notice() {
            if ( !current_user_can( 'install_plugins' ) ) {
                return;
            }
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            if ( wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'mcm_dismiss_notice' ) ) {
                global $wp_mcm_options;
                $notice_action = ( empty( $_REQUEST['notice_action'] ) || $_REQUEST['notice_action'] === 'hide' ? 'hide' : esc_attr( $_REQUEST['notice_action'] ) );
                switch ( $notice_action ) {
                    // delay notice
                    case 'delay':
                        // set delay period to WP_MCM_NOTICE_DELAY_PERIOD from now
                        $wp_mcm_options->set_value( 'wp_mcm_notice_activation_date', time() + WP_MCM_NOTICE_DELAY_PERIOD );
                        break;
                    // hide notice
                    default:
                        $wp_mcm_options->set_value( 'wp_mcm_notice_status', '0' );
                        $wp_mcm_options->set_value( 'wp_mcm_notice_activation_date', time() );
                        break;
                }
                // Update the changed options.
                $wp_mcm_options->update_mcm_options();
            }
            exit;
        }

        /**
         * Check whether the current_user has WP_MCM_CAP_MANAGE_MCM_ADMIN capabilities
         *
         * @since 2.0.0
         * @param boolean $noAdmin - whether to validate for non-admins only, default = false
         * @return boolean
         */
        function mcm_is_admin( $noAdmin = false ) {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // User must be logged in
            if ( !is_user_logged_in() ) {
                return false;
            }
            // User can be wordpress admin
            if ( $noAdmin && current_user_can( 'manage_options' ) ) {
                return true;
            }
            // Check what current_user_can manage
            if ( current_user_can( WP_MCM_CAP_MANAGE_MCM_ADMIN ) ) {
                return true;
            }
            return false;
        }

        /**
         * Check whether the current_user has WP_MCM_CAP_MANAGE_MCM_USER capabilities
         * Admin is always allowed
         *
         * @since 2.0.0
         * @param string $userLogin - the login name of the user to check
         * @return boolean
         */
        function mcm_is_user( $noAdmin = false ) {
            $this->debugMP( 'msg', __FUNCTION__ . ' started.' );
            // User must be logged in
            if ( !is_user_logged_in() ) {
                return false;
            }
            // User can be wordpress admin
            if ( $noAdmin && current_user_can( 'manage_options' ) ) {
                return true;
            }
            // Check requested user has WP_MCM_CAP_MANAGE_MCM_USER
            if ( current_user_can( WP_MCM_CAP_MANAGE_MCM_USER ) ) {
                return true;
            }
            return false;
        }

        /**
         * Simplify the plugin debugMP interface.
         *
         * Typical start of function call: $this->debugMP('msg',__FUNCTION__);
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP( $type, $hdr, $msg = '' ) {
            if ( $type === 'msg' && $msg !== '' ) {
                $msg = esc_html( $msg );
            }
            if ( $hdr !== '' ) {
                // Adding __CLASS__ to non-empty hdr
                $hdr = __CLASS__ . '::' . $hdr;
            }
            WP_MCM_debugMP(
                $type,
                $hdr,
                $msg,
                NULL,
                NULL,
                true
            );
        }

    }

}