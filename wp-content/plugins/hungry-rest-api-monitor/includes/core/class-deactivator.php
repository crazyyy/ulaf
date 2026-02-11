<?php
/**
 * Plugin Deactivator.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Deactivator
 * Handles plugin deactivation tasks.
 */
class NANDRESTAPI_Deactivator
{

    /**
     * Run deactivation tasks.
     */
    public static function deactivate()
    {
        // Clear scheduled hooks.
        wp_clear_scheduled_hook('nandrestapi_daily_cleanup');

        // Flush rewrite rules.
        flush_rewrite_rules();
    }
}
