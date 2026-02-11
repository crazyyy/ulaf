<?php
/**
 * Plugin Activator.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Activator
 * Handles plugin activation tasks.
 */
class NANDRESTAPI_Activator
{

    /**
     * Run activation tasks.
     */
    public static function activate()
    {
        // Create database table.
        NANDRESTAPI_DB_Schema::create_tables();

        // Initialize default options.
        self::initialize_options();

        // Flush rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Initialize default plugin options.
     */
    private static function initialize_options()
    {
        $defaults = array(
            'enable_logging' => 1,
            'data_retention_days' => 7,
            'log_ip_address' => 0,
            'log_request_body' => 0,
            'log_response_body' => 0,
            'excluded_endpoints' => '',
            'enable_stack_traces' => 0,
        );

        $existing = get_option(NANDRESTAPI_OPTIONS_KEY);

        if (false === $existing) {
            add_option(NANDRESTAPI_OPTIONS_KEY, $defaults);
        } else {
            // Merge with defaults to add any new options.
            update_option(NANDRESTAPI_OPTIONS_KEY, wp_parse_args($existing, $defaults));
        }
    }
}
