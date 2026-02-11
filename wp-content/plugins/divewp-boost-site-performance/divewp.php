<?php
/**
 * Plugin Name: DiveWP - Boost Site Performance with Clear, Actionable Steps
 * Plugin URI: https://wordpress.org/plugins/divewp-boost-site-performance/
 * Description: Learn WordPress Best Practices Through Your Own Site! Get clear insights about Performance, Security, and Best Practices – explained in plain English.
 * Version: 2.2.1
 * Requires at least: 6.8
 * Requires PHP: 7.2
 * Author: Oleg Petrov
 * Author URI: https://divewp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: divewp-boost-site-performance
 * Domain Path: /languages
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

// Near the top of the file, after defining ABSPATH check
if (!defined('DIVEWP_DEBUG')) {
    define('DIVEWP_DEBUG', false);
}

// Define plugin constants first
define('DIVEWP_VERSION', '2.2.0');
define('DIVEWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DIVEWP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Add direct debug logging
divewp_debug_log('Plugin loading - DIVEWP_PLUGIN_URL: ' . DIVEWP_PLUGIN_URL, 'debug');
divewp_debug_log('Plugin loading - DIVEWP_PLUGIN_DIR: ' . DIVEWP_PLUGIN_DIR, 'debug');

// Prevent multiple plugin loading - moved after constants
global $divewp_plugin_loaded;
if ($divewp_plugin_loaded) {
    return;
}
$divewp_plugin_loaded = true;

/**
 * Standardized debug logging with severity levels
 *
 * @since 2.0.2
 * @access private
 *
 * @param string $message  The message to log
 * @param string $severity Log severity: 'info', 'warning', or 'error'
 * @return void
 */
function divewp_debug_log($message, $severity = 'info') {
    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG && WP_DEBUG) {
        $timestamp = current_time('mysql');
        $severity = in_array($severity, array('info', 'warning', 'error'), true) ? $severity : 'info';
        
        // Use WordPress debug log function instead of error_log
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $log_message = sprintf(
                'DiveWP [%s] [%s]: %s',
                esc_html($timestamp),
                esc_html(strtoupper($severity)),
                wp_kses_post($message)
            );
            
            // Use wp_debug_log() if available (WP 5.1+)
            if (function_exists('wp_debug_log')) {
                wp_debug_log($log_message);
            } else {
                // Fallback for older WordPress versions
                $debug_file = WP_CONTENT_DIR . '/debug.log';
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log($log_message . PHP_EOL, 3, $debug_file);
            }
        }
    }
}

// Add this check and include
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check PHP Version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(
                /* translators: %s: PHP version */
                esc_html__('DiveWP requires PHP version %s or higher.', 'divewp-boost-site-performance'),
                esc_html('7.4')
            )
        );
    });
    return;
}

// Move the table creation into a separate function that both activation and updates can use
/**
 * Creates required database tables for the plugin
 *
 * @since 2.0.2
 * @access private
 *
 * @return boolean True on success, false on failure
 */
function divewp_create_tables() {
    if (!current_user_can('manage_options')) {
        return false;
    }

    try {
        if (!DiveWP_Database::init_tables()) {
            throw new Exception('Failed to initialize database tables');
        }
        update_option('divewp_db_version', '2.0.1');
        divewp_debug_log('Database tables created successfully with benchmark support', 'info');
        return true;
    } catch (Exception $e) {
        divewp_debug_log('Database creation failed: ' . $e->getMessage(), 'error');
        return false;
    }
}

// Add version check and table verification
function divewp_check_update() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $current_version = get_option('divewp_version', '0');
    
    // If this is a new installation or an update
    if (version_compare($current_version, DIVEWP_VERSION, '<')) {
        // Check if tables exist and create if needed
        if (!DiveWP_Database::verify_tables()) {
            divewp_create_tables();
        } else {
            // Tables exist, check if they're empty and add sample data if needed
            DiveWP_Database::add_sample_data();
        }
        
        // Update stored version
        update_option('divewp_version', DIVEWP_VERSION);
    }
}

// Check for updates on admin init
add_action('admin_init', 'divewp_check_update');

// Check database tables on admin page load
function divewp_admin_init() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Only run on admin pages
    if (!is_admin()) {
        return;
    }
    
    try {
        $current_db_version = get_option('divewp_db_version', '0');
        
        if (version_compare($current_db_version, '2.0.1', '<')) {
            if (DiveWP_Database::init_tables()) {
                update_option('divewp_db_version', '2.0.1');
                divewp_debug_log('Database updated to version 2.0.1 with benchmark results table', 'info');
            }
        }
    } catch (Exception $e) {
        divewp_debug_log('Database Init Error: ' . $e->getMessage(), 'error');
    }
}
add_action('admin_init', 'divewp_admin_init');

// Add activation verification
/**
 * Verifies and handles plugin activation requirements
 *
 * @since 2.0.2
 * @access private
 *
 * @return void
 */
function divewp_verify_activation() {
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Create tables
    if (!divewp_create_tables()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('Failed to create required database tables. Please check server permissions and try again.', 'divewp-boost-site-performance'),
            esc_html__('Plugin Activation Error', 'divewp-boost-site-performance'),
            array('back_link' => true)
        );
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation hook to create tables when plugin is activated
register_activation_hook(__FILE__, 'divewp_verify_activation');

// Include required files
require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-database.php';
require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-main.php';
if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
    divewp_debug_log('DiveWP: Main class loaded', 'info');
}
require_once DIVEWP_PLUGIN_DIR . 'includes/features/email-communications/class-email-logger.php';
require_once DIVEWP_PLUGIN_DIR . 'includes/class-dashboard-overview.php';
require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';

// Plugin initialization
function divewp_run_plugin() {
    try {
        static $plugin = null;
        if ($plugin === null) {
            divewp_debug_log('Run plugin function called');
            $plugin = new DiveWP_Main();
            $plugin->run();
        }
    } catch (Exception $e) {
        divewp_debug_log('Plugin initialization error: ' . $e->getMessage());
        
        // Only show admin notice if user can manage plugins
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>' . 
                     esc_html__('DiveWP initialization failed: ', 'divewp-boost-site-performance') . 
                     esc_html($e->getMessage()) . 
                     '</p></div>';
            });
        }
    }
}
add_action('plugins_loaded', 'divewp_run_plugin');

// Add emergency script loading if all else fails
function divewp_emergency_script_load() {
    // Verify we're in admin area and sanitize page parameter
    if (!is_admin()) {
        return;
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple page check, not processing form submission
    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    
    // Only proceed if this is the divewp page and user has proper permissions
    if ($page !== 'divewp' || !current_user_can('manage_options')) {
        return;
    }
    
    divewp_debug_log('Emergency script loading triggered', 'debug');
    
    // Test script removed - no longer needed
    
    // Direct enqueue for main admin script
    wp_enqueue_script(
        'divewp-admin-emergency',
        plugin_dir_url(__FILE__) . 'assets/js/divewp-admin.js',
        array('jquery'),
        time(), // Use timestamp to prevent caching
        true
    );
    
    // Add direct console log
    add_action('admin_footer', function() {
        echo '<script>console.log("' . esc_js(__('DiveWP emergency script loading activated', 'divewp-boost-site-performance')) . '");</script>';
    });
}
add_action('admin_enqueue_scripts', 'divewp_emergency_script_load', 999);

// Add secure deactivation
register_deactivation_hook(__FILE__, 'divewp_deactivate');

function divewp_deactivate() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Clear scheduled events
    wp_clear_scheduled_hook('divewp_daily_cleanup');
    wp_clear_scheduled_hook('divewp_user_events_cleanup');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    divewp_debug_log('Plugin deactivated', 'info');
}

// WordPress 4.6+ automatically loads plugin translations for WordPress.org hosted plugins
// No need to use load_plugin_textdomain anymore
