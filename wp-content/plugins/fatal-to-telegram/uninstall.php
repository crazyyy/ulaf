<?php

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('fttg_enabled');

delete_option('fttg_bot_token');

delete_option('fttg_chat_id');

$mu_plugin_file = WP_CONTENT_DIR . '/mu-plugins/fatal-to-telegram-loader.php';

if (file_exists($mu_plugin_file)) {

    wp_delete_file($mu_plugin_file);

}
