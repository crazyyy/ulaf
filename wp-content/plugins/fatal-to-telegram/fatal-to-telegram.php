<?php
/**
 * Plugin Name:         Fatal message to Telegram
 * Plugin URI:          https://iphil.top/portfolio/fatal-to-telegram/
 * Description:         Sends PHP fatal errors to Telegram instantly. Creates early loader via MU-plugin.
 * Version:             1.4.1
 * Author:              iPhil
 * Author URI:          https://iphil.top
 * Requires at least:   5.3
 * Tested up to:        6.8.1
 * License:             GPLv2 or later
 * Uninstall:           true
 * 
 * Text Domain:         fatal-to-telegram
 *
 */

 if (!defined('ABSPATH')) exit;

 add_action('plugins_loaded', 'fttg_load_textdomain');

function fttg_load_textdomain() 
{

    load_plugin_textdomain('fatal-to-telegram', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
}


 add_filter('plugin_row_meta', 'fttg_plugin_row_meta', 10, 2);
 
 function fttg_plugin_row_meta($links, $file) {
 
     if ($file === plugin_basename(__FILE__)) {
 
         $links[] = '<a href="' . esc_url(admin_url('tools.php?page=fatal-to-telegram')) . '">Settings</a>';
 
     }
 
     return $links;
 
 }
 
 define('FTTG_PLUGIN_PATH', plugin_dir_path(__FILE__));
 
 define('FTTG_PLUGIN_URL', plugin_dir_url(__FILE__));
 
 // === LOAD CORE ===
 require_once FTTG_PLUGIN_PATH . 'includes/helpers.php';
 require_once FTTG_PLUGIN_PATH . 'includes/notifier.php';
 require_once FTTG_PLUGIN_PATH . 'admin/settings-page.php';
 
 // === Register activation hook to generate mu-plugin loader ===
 register_activation_hook(__FILE__, 'fttg_create_mu_loader');
 
 function fttg_create_mu_loader() {
 
     update_option('fttg_active', true);
 
     global $wp_filesystem;
 
     // Include WP_Filesystem API
     if (empty($wp_filesystem)) {
 
         require_once ABSPATH . '/wp-admin/includes/file.php';
 
         WP_Filesystem();
 
     }
 
     $mu_plugin_dir = WP_CONTENT_DIR . '/mu-plugins/';
 
     $mu_loader_file = $mu_plugin_dir . 'fatal-to-telegram-loader.php';
 
     if (!is_dir($mu_plugin_dir)) {
 
         wp_mkdir_p($mu_plugin_dir);
 
     }

     $loader_code = "<?php\n"
    . "if (!defined('ABSPATH')) exit;\n\n"
    . "// Early fatal error hook loader\n"
    . "if (get_option('fttg_active')) {\n"
    . "    \$main_plugin_path = WP_PLUGIN_DIR . '/fatal-to-telegram/includes/notifier.php';\n"
    . "    if (file_exists(\$main_plugin_path)) {\n"
    . "        require_once \$main_plugin_path;\n"
    . "        if (function_exists('fttg_shutdown_handler')) {\n"
    . "            register_shutdown_function('fttg_shutdown_handler');\n"
    . "        }\n"
    . "    }\n"
    . "}\n";
 
     
     if ($wp_filesystem) {
 
         $wp_filesystem->put_contents($mu_loader_file, $loader_code, FS_CHMOD_FILE);
         
     }
 
 }
 
 
 register_deactivation_hook(__FILE__, 'delete_fttg_active_option');
 
 function delete_fttg_active_option() {
 
     delete_option('fttg_active');
 
 }; 