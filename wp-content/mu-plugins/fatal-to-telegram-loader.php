<?php
if (!defined('ABSPATH')) exit;

// Early fatal error hook loader
if (get_option('fttg_active')) {
    $main_plugin_path = WP_PLUGIN_DIR . '/fatal-to-telegram/includes/notifier.php';
    if (file_exists($main_plugin_path)) {
        require_once $main_plugin_path;
        if (function_exists('fttg_shutdown_handler')) {
            register_shutdown_function('fttg_shutdown_handler');
        }
    }
}
