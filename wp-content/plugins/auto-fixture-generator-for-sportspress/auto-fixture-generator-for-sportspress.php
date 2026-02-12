<?php

/**
 * Plugin Name: Auto Fixture Generator for SportsPress
 * Description: Automatically generate fixtures (events) for a selected SportsPress league and season using pluggable scheduling algorithms.
 * Version: 1.5
 * Author: Savvas
 * Author URI: https://savvasha.com
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl.html
 * Text Domain: auto-fixture-generator-for-sportspress
 *
 * @package Auto_Fixture_Generator_For_SportsPress
 */
declare (strict_types = 1);
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'afgsp_fs' ) ) {
    afgsp_fs()->set_basename( false, __FILE__ );
} else {
    /**
     * DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE
     * `function_exists` CALL ABOVE TO PROPERLY WORK.
     */
    if ( !function_exists( 'afgsp_fs' ) ) {
        /**
         * Helper function for easy Freemius SDK access.
         *
         * @return \Freemius Freemius SDK instance.
         */
        function afgsp_fs() {
            global $afgsp_fs;
            if ( !isset( $afgsp_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $afgsp_fs = fs_dynamic_init( array(
                    'id'               => '20296',
                    'slug'             => 'auto-fixture-generator-for-sportspress',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_19fa71e75c7c02d053dff45b1bb79',
                    'is_premium'       => false,
                    'is_org_compliant' => true,
                    'premium_suffix'   => 'Premium',
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'menu'             => array(
                        'first-path' => 'edit.php?post_type=sp_event&page=afgsp-generator',
                    ),
                    'is_live'          => true,
                ) );
            }
            return $afgsp_fs;
        }

        // Init Freemius.
        afgsp_fs();
        // Signal that SDK was initiated.
        do_action( 'afgsp_fs_loaded' );
        // Prevent Freemius from redirecting to a non-existent page during activation.
        //afgsp_fs()->add_filter( 'show_activation_message', '__return_false' );
        //afgsp_fs()->add_filter( 'show_first_time_activation_message', '__return_false' );
        //afgsp_fs()->add_filter( 'redirect_on_activation', '__return_false' );
    }
    /**
     * Define plugin constants.
     */
    if ( !defined( 'AFGSP_VERSION' ) ) {
        define( 'AFGSP_VERSION', '1.5' );
    }
    if ( !defined( 'AFGSP_PLUGIN_FILE' ) ) {
        define( 'AFGSP_PLUGIN_FILE', __FILE__ );
    }
    if ( !defined( 'AFGSP_PLUGIN_DIR' ) ) {
        define( 'AFGSP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    if ( !defined( 'AFGSP_PLUGIN_URL' ) ) {
        define( 'AFGSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }
    /**
     * Load required files (classes and helpers).
     *
     * All includes are wrapped in a function and hooked late to ensure WordPress and SportsPress
     * are fully loaded before we try to access their APIs.
     */
    function afgsp_load_dependencies() {
        // Include helper functions.
        require_once AFGSP_PLUGIN_DIR . 'includes/helpers.php';
        // Include algorithm registry and generator.
        require_once AFGSP_PLUGIN_DIR . 'includes/class-afgsp-registry.php';
        require_once AFGSP_PLUGIN_DIR . 'includes/class-afgsp-generator.php';
        // Include admin UI.
        require_once AFGSP_PLUGIN_DIR . 'includes/class-afgsp-admin.php';
        // Include debug/dry-run functionality.
        require_once AFGSP_PLUGIN_DIR . 'includes/class-afgsp-debug.php';
        // Load bundled algorithms by including all PHP files under algorithms/.
        // Each file registers itself via the `afgsp_algorithms` filter.
        $algorithms_dir = trailingslashit( AFGSP_PLUGIN_DIR . 'algorithms' );
        if ( is_dir( $algorithms_dir ) ) {
            $algorithm_files = glob( $algorithms_dir . '*.php' );
            if ( is_array( $algorithm_files ) ) {
                foreach ( $algorithm_files as $algorithm_file ) {
                    // Load algorithm files - safe as we control the directory structure.
                    require_once $algorithm_file;
                }
            }
        }
    }

    add_action( 'plugins_loaded', 'afgsp_load_dependencies', 20 );
    /**
     * Initialize plugin functionality once dependencies are loaded.
     */
    function afgsp_init_plugin() {
        // SportsPress activation checks are intentionally disabled for now to allow
        // the UI to load even if SportsPress is not active.
        // Instantiate admin UI controller.
        \AFGSP\AFGSP_Admin::get_instance();
    }

    add_action( 'plugins_loaded', 'afgsp_init_plugin', 30 );
    /**
     * Activation hook.
     *
     * Currently used to maybe set up default options or future DB upgrades.
     */
    function afgsp_activate() {
        // Placeholder for future use.
    }

    register_activation_hook( __FILE__, 'afgsp_activate' );
    /**
     * Deactivation hook.
     */
    function afgsp_deactivate() {
        // Placeholder for future use.
    }

    register_deactivation_hook( __FILE__, 'afgsp_deactivate' );
}