<?php
/**
 * CF7 Redirection
 *
 * @since 3.2.0
 * @package CF7Apps
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CF7Apps_Redirection' ) && class_exists( 'CF7Apps_App' ) ) :
    /**
     * Class CF7Apps_Redirection
     *
     * @since 3.2.0
     */
    class CF7Apps_Redirection extends CF7Apps_App {
        /**
         * App version
         *
         * @since 3.2.1
         * @var string $app_version App version.
         */
        private $app_version = '1.0.0';

        /**
         * CF7Apps_Redirection constructor.
         *
         * @since 3.2.0
         */
        public function __construct() {
            $this->id                    = 'cf7-redirection';
            $this->priority              = 1;
            $this->title                 = __( 'Redirection', 'cf7apps' );
            $this->description           = __( 'Easily redirect users to a specific URL after form submission, enhancing user experience and engagement.', 'cf7apps' );
            $this->icon                  = plugin_dir_url( __FILE__ ) . 'assets/images/logo.png';
            $this->has_admin_settings    = true;
            $this->is_pro                = false;
            $this->by_default_enabled    = false;
            $this->has_internal_settings = true;
            $this->documentation_url     = 'https://cf7apps.com/docs/general/redirection';
            $this->parent_menu           = __( 'General', 'cf7apps' );

            $this->run();
        }

        /**
         * Admin settings for the Redirection app.
         *
         * @since 3.2.0
         * @return array[]
         */
        public function admin_settings() {
            $posts = cf7apps_get_post_types_options();
            return array(
                'general' => array(
                    'fields' => array(
                        'notice'           => array(
                            'type'  => 'notice',
                            'class' => 'info',
                            'text'  => sprintf(
                                __( 'Stuck? Check our Documentation on %s', 'cf7apps' ),
                                '<a href="https://cf7apps.com/docs/general/redirection"><u>' . __( 'Redirection', 'cf7apps' ) . '</u></a>'
                            ),
                        ),

                        'is_enabled'       => array(
                            'title'   => __( 'Enable Redirection App', 'cf7apps' ),
                            'type'    => 'checkbox',
                            'default' => false,
                        ),

                        'global_settings'  => array(
                            'title'       => __( 'Global Settings', 'cf7apps' ),
                            'type'        => 'checkbox',
                            'default'     => false,
                            'help' => __( 'Enable global redirection settings for all forms. Individual form settings will override global settings if both are enabled.', 'cf7apps' ),
                        ),

                        'redirection_type' => array(
                            'title'       => __( 'Redirection Type', 'cf7apps' ),
                            'type'        => 'radio',
                            'options'     => array(
                                'post_type'    => __( 'Post Type', 'cf7apps' ),
                                'external_url' => __( 'External URL', 'cf7apps' ),
                            ),
                            'default'     => 'post_type',

                            'sub_fields'  => array(
                                'post_type'    => array(
                                    'title'       => __( 'Select Post Type', 'cf7apps' ),
                                    'type'        => 'select',
                                    'options'     => $posts,
                                    'default'     => array_keys( $posts )[0],
                                    'help'        => __( 'Choose the post type to redirect users to after form submission.', 'cf7apps' ),
                                ),

                                'external_url' => array(
                                    'title'       => __( 'External URL', 'cf7apps' ),
                                    'type'        => 'text',
                                    'default'     => '',
                                    'placeholder' => 'Enter URL',
                                    'help'        => __( 'Enter the external URL to redirect users to after form submission. Make sure to include the full URL (e.g., https://example.com).', 'cf7apps' ),
                                    'required'    => true,
                                ),
                            ),
                        ),

                        'new_tab'          => array(
                            'title'       => __( 'Open in New Tab', 'cf7apps' ),
                            'type'        => 'checkbox',
                            'default'     => false,
                            'help' => __( 'Open the redirection link in a new browser tab.', 'cf7apps' ),
                        ),

                        'save_settings'    => array(
                            'type'  => 'save_button',
                            'text'  => __( 'Save Settings', 'cf7apps' ),
                            'class' => 'button-primary'
                        ),
                    ),
                ),
            );
        }

        /**
         * Get default settings for the Redirection app.
         *
         * @since 3.2.0
         * @return array
         */
        private function get_default_settings() {
            $posts = cf7apps_get_post_types_options();
            return array(
                'is_enabled'       => false,
                'global_settings'  => false,
                'redirection_type' => 'post_type',
                'post_type'        => array_keys( $posts )[0],
                'external_url'     => '',
                'new_tab'          => false,
            );
        }

        /**
         * Internal settings for individual forms.
         *
         * @since 3.2.0
         * @return array[]
         */
        public function internal_settings() {
            $enabled         = $this->get_option( 'is_enabled' );
            $global_settings = $this->get_option( 'global_settings' );
            $posts    = cf7apps_get_post_types_options();
            $settings = array(
                'general' => array(
                    'fields' => array()
                )
            );

            $settings['general']['fields']['notice'] = array(
                'type'  => 'notice',
                'class' => 'info',
                'text'  => sprintf(
                    __( 'Stuck? Check our Documentation on %s', 'cf7apps' ),
                    '<a href="https://cf7apps.com/docs/general/redirection/"><u>' . __( 'Redirection', 'cf7apps' ) . '</u></a>'
                ),
            );

            if ( $global_settings ) {
                $settings['general']['fields']['use_global_settings'] = array(
                    'title'       => __('Use Global Settings', 'cf7apps'),
                    'type'        => 'checkbox',
                    'default'     => false,
                    'help' => __('Enable to use the global redirection settings defined in the Redirection app settings. Disable to set custom redirection settings for this form.', 'cf7apps'),
                    'disabled'    => ! $enabled,
                );
            }

            $settings['general']['fields']['redirection_type'] = array(
                'title'       => __( 'Redirection Type', 'cf7apps' ),
                'type'        => 'radio',
                'options'     => array(
                    'post_type'    => __( 'Post Type', 'cf7apps' ),
                    'external_url' => __( 'External URL', 'cf7apps' ),
                ),
                'default'     => 'post_type',
                'disabled'    => ! $enabled,
                'sub_fields'  => array(
                    'post_type'    => array(
                        'title'       => __( 'Select Post Type', 'cf7apps' ),
                        'type'        => 'select',
                        'options'     => $posts,
                        'default'     => array_keys( $posts )[0],
                        'help'        => __( 'Choose the post type to redirect users to after form submission.', 'cf7apps' ),
                        'disabled'    => ! $enabled,
                    ),
                    'external_url' => array(
                        'title'       => __( 'External URL', 'cf7apps' ),
                        'type'        => 'text',
                        'default'     => '',
                        'placeholder' => 'Enter URL',
                        'help'        => __( 'Enter the external URL to redirect users to after form submission. Make sure to include the full URL (e.g., https://example.com).', 'cf7apps' ),
                        'disabled'    => ! $enabled,
                        'required'    => true,
                    ),
                ),
            );

            $settings['general']['fields']['new_tab']          = array(
                'title'       => __( 'Open in New Tab', 'cf7apps' ),
                'type'        => 'checkbox',
                'default'     => false,
                'help' => __( 'Open the redirection link in a new browser tab.', 'cf7apps' ),
                'disabled'    => ! $enabled,
            );

            $settings['general']['fields']['save_settings']    = array(
                'type'        => 'save_button',
                'text'        => __( 'Save Settings', 'cf7apps' ),
                'class'       => 'button-primary',
                'disabled'    => ! $enabled,
            );

            return $settings;
        }

        /**
         * CF7Apps_Redirection constructor.
         *
         * @since 3.2.0
         * @param CF7Apps_App[] $apps List of registered apps.
         *
         * @return CF7Apps_App[]
         */
        public static function initialize_module( $apps ) {

            $apps[] = __CLASS__;

            return $apps;
        }

        /**
         * Run the Redirection app.
         *
         * @since 3.2.0
         */
        private function run() {
            add_action( 'wp_ajax_nopriv_cf7apps_fetch_settings', array( $this, 'fetch_app_settings' ) );
            add_action( 'wp_ajax_cf7apps_fetch_settings', array( $this, 'fetch_app_settings' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        }

        /**
         * Fetch app settings via AJAX.
         *
         * @since 3.2.0
         */
        public function fetch_app_settings() {
            if ( $this->get_option( 'is_enabled' ) ) {

                if ( isset( $_POST['formId'] ) ) {
                    $form_id             = intval( wp_unslash( $_POST['formId'] ) );
                    $global_app_enabled  = $this->get_option( 'global_settings' ); // from global app settings.
                    $use_global_settings = $this->get_individual_option( $form_id, 'use_global_settings' ); // from individual form settings.

                    if ( $global_app_enabled ) {
                        if ( $use_global_settings ) {
                            $settings = $this->get_option( null );
                        } else {
                            $settings = $this->get_individual_option( $form_id );
                        }
                    } else {
                        $settings = $this->get_individual_option( $form_id );
                    }

                    $settings = wp_parse_args( $settings, $this->get_default_settings() );

                    if ( $settings ) {
                        $redirect_type = $settings['redirection_type'];
                        $redirect_to   = $settings[ $redirect_type ];
                        $new_tab       = $settings['new_tab'];

                        if ( 'post_type' === $redirect_type ) {
                            $redirect_to = get_permalink( $redirect_to );
                        }

                        wp_send_json_success(
                            array(
                                'redirectTo' => $redirect_to,
                                'newTab'     => $new_tab,
                                'isEnabled' => true,
                            ),
                            200
                        );
                    }
                }
                wp_send_json_error(
                    array(
                        'message' => __( 'Form ID is required.', 'cf7apps' ),
                    ),
                    '404'
                );
            }

            wp_send_json_error(
                array(
                    'message' => __( 'Redirection app is not enabled.', 'cf7apps' ),
                ),
                '403'
            );
        }

        /**
         * Enqueue frontend scripts for redirection.
         *
         * @since 3.2.0
         */
        public function wp_enqueue_scripts() {
            if ( $this->get_option( 'is_enabled' ) ) {
                wp_enqueue_script(
                    'cf7apps-redirection',
                    plugin_dir_url( __FILE__ ) . 'assets/js/app.js',
                    array( 'jquery' ),
                    CF7APPS_VERSION . '-' . $this->app_version,
                    true
                );

                wp_localize_script(
                    'cf7apps-redirection',
                    'cf7appsRedirection',
                    array(
                        'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    )
                );
            }
        }
    }

    add_filter( 'cf7apps_apps', array( CF7Apps_Redirection::class, 'initialize_module' ) );
endif;
