<?php
/**
 * Plugin Name: ZA My Favorite Plugins Installer
 * Plugin URI: https://github.com/zeeshanarshad
 * Description: High-speed automation tool to download, install, and activate custom plugin collections in one click. (ZAMFPI).
 * Version: 2.0
 * Author: Zeeshan Arshad
 * Author URI: https://github.com/zeeshanarshad
 * License: GPL2
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'zamfpi_initialize_core');

function zamfpi_initialize_core() {
    add_menu_page(
        'ZA My Favorite Plugins Installer', // Page Title (Browser tab)
        'ZA My Favorite Plugins Installer', // Menu Title (Sidebar name)
        'manage_options', 
        'zamfpi_installer', 
        'zamfpi_render_engine', 
        'dashicons-cloud-install', 
        62
    );
}

function zamfpi_render_engine() {
    if (!current_user_can('manage_options')) return;
    
    $engine_path = plugin_dir_path(__FILE__) . 'plugin_installer.php';
    if (file_exists($engine_path)) {
        include($engine_path);
    } else {
        echo '<div class="notice notice-error"><p>System Error: Core logic file missing.</p></div>';
    }
}