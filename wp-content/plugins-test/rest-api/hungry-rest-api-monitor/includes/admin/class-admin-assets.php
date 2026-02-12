<?php
/**
 * Admin Assets Handler.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Admin_Assets
 * Handles script and style enqueueing.
 */
class NANDRESTAPI_Admin_Assets
{

    /**
     * Initialize assets.
     */
    public static function init()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook Current admin page hook.
     */
    public static function enqueue_assets($hook)
    {
        // Only load on our plugin page.
        if (strpos($hook, 'hungry-rest-api-monitor') === false) {
            return;
        }

        // Enqueue Chart.js.
        wp_enqueue_script(
            'nandrestapi-chartjs',
            NANDRESTAPI_PLUGIN_URL . 'assets/js/chart.min.js',
            array(),
            '4.5.1',
            true
        );

        // Enqueue admin dashboard script.
        wp_enqueue_script(
            'nandrestapi-admin',
            NANDRESTAPI_PLUGIN_URL . 'assets/js/admin-dashboard.js',
            array('jquery', 'nandrestapi-chartjs'),
            NANDRESTAPI_VERSION,
            true
        );

        // Enqueue admin styles.
        wp_enqueue_style(
            'nandrestapi-admin',
            NANDRESTAPI_PLUGIN_URL . 'assets/css/admin-styles.css',
            array(),
            NANDRESTAPI_VERSION
        );

        // Localize script.
        wp_localize_script(
            'nandrestapi-admin',
            'nandrestapiAdmin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nandrestapi_admin_nonce'),
                'strings' => array(
                    'confirm_clear' => __('Are you sure you want to clear all logs? This cannot be undone.', 'hungry-rest-api-monitor'),
                    'processing' => __('Processing...', 'hungry-rest-api-monitor'),
                    'success' => __('Success!', 'hungry-rest-api-monitor'),
                    'error' => __('An error occurred. Please try again.', 'hungry-rest-api-monitor'),
                    'no_data' => __('No data available', 'hungry-rest-api-monitor'),
                ),
            )
        );
    }
}
