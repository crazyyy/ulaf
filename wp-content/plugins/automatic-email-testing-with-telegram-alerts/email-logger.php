<?php
/*
Plugin Name: Automatic Email Testing With Telegram Alerts
Plugin URI: https://azbrand.ca/free-automatic-wordpress-email-testing-plugin-with-telegram-alerts/
Description: A plugin to send 6 hour emails and log results and will send an alert to Telegram if emails fail. Admins can send manual tests and receive Telegram notifications on failures.
Version: 1.8.13
Author URI: https://AZBrand.ca
Author: <a href="https://azbrand.ca" target="_blank">AZBrand.ca</a>
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Add custom cron schedule for every 6 hours
function aetwtaha4cca_custom_cron_schedules($schedules) {
    $schedules['aetwtaha4cca_every_six_hours'] = array(
        'interval' => 21600, // 6 hours in seconds
        'display'  => esc_html__('Every Six Hours', 'automatic-email-testing-with-telegram-alerts') 
    );
    return $schedules;
}
add_filter('cron_schedules', 'aetwtaha4cca_custom_cron_schedules');



// Schedule the email sending event
function aetwtaha4cca_schedule_email_event() {
    if (!wp_next_scheduled('aetwtaha4cca_send_email_event')) {
        wp_schedule_event(time(), 'every_six_hours', 'aetwtaha4cca_send_email_event');
    }
}
// Keeping this hook as requested to handle low traffic triggering
add_action('wp', 'aetwtaha4cca_schedule_email_event');

// Hook for sending the email
add_action('aetwtaha4cca_send_email_event', 'aetwtaha4cca_send_email');

function aetwtaha4cca_send_email() {
    $email_address = get_option('aetwtaha4cca_email_address');
    $subject = 'Scheduled Email';
    $message = 'This is a test email from your WordPress plugin.';
    
    // Attempt to send the email
    $mail_sent = wp_mail($email_address, $subject, $message);
    
    // Log the email result using WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }
    
    // FIXED: Removed undefined $email_sent variable. 
    // This maintains the log format: "[Date] Email : Success" and prevents PHP Warnings.
    $log_entry = '[' . gmdate('Y-m-d H:i:s') . '] Email : ' . ($mail_sent ? 'Success' : 'Failure');
    
    $log_file = plugin_dir_path(__FILE__) . 'emaillog.txt';
    
    // put_contents overwrites by default, keeping the file 1 line long as requested.
    $wp_filesystem->put_contents($log_file, $log_entry, FS_CHMOD_FILE);
    
    // Telegram notification on failure
    if (!$mail_sent) {
        $bot_id = get_option('aetwtaha4cca_telegram_bot_id');
        $chat_id = get_option('aetwtaha4cca_telegram_chat_id');
        $site_name = get_option('blogname'); // Get the site name
        $telegram_message = "Failed to send email from $site_name to $email_address";
        wp_remote_get("https://api.telegram.org/bot$bot_id/sendMessage?chat_id=$chat_id&text=" . urlencode($telegram_message));
    }
}
//atribution comments//
function aetwtaha4cca_add_custom_comment() {
    echo '<!-- Wordpress Automatic Email Testing With Telegram Alerts - AZBrand.ca -->';
    echo '<!-- Note: This plugin helps ensure your email functionality is working correctly and sends alerts via Telegram if any issues are detected. -->';
}
add_action('wp_head', 'aetwtaha4cca_add_custom_comment');

//atrib page
// Hook to run when the plugin is activated
register_activation_hook(__FILE__, 'aetwtaha4cca_create_attribution_page');

function aetwtaha4cca_create_attribution_page() {
    
    // Set a transient to indicate that the plugin has just been activated
    set_transient('aetwtaha4cca_email_scheduler_activated', true, 30);
}

// Hook to run on admin_init to redirect the user after plugin activation
add_action('admin_init', 'aetwtaha4cca_redirect_after_activation');

function aetwtaha4cca_redirect_after_activation() {
    // Check if the transient is set
    if (get_transient('aetwtaha4cca_email_scheduler_activated')) {
        // Delete the transient so it doesn't redirect again
        delete_transient('aetwtaha4cca_email_scheduler_activated');

        // Redirect to the settings page
        wp_redirect(admin_url('options-general.php?page=email-scheduler'));
        exit;
    }
}



// Add the settings page link to the plugin action links
function aetwtaha4cca_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=email-scheduler') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'aetwtaha4cca_plugin_action_links');


// Create the settings page
function aetwtaha4cca_options_page() {
    if (isset($_POST['aetwtaha4cca_save_settings'])) {
        // Check nonce for security
        $nonce = isset($_POST['aetwtaha4cca_nonce']) ? sanitize_text_field(wp_unslash($_POST['aetwtaha4cca_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'aetwtaha4cca_save_settings')) {
            die('Security check failed');
        }

        // Sanitize and save email, Telegram bot, and chat ID
        if (isset($_POST['aetwtaha4cca_email_address'])) {
            $email_address = sanitize_email(wp_unslash($_POST['aetwtaha4cca_email_address']));
            update_option('aetwtaha4cca_email_address', $email_address);
        }
        if (isset($_POST['aetwtaha4cca_telegram_bot_id'])) {
            $bot_id = sanitize_text_field(wp_unslash($_POST['aetwtaha4cca_telegram_bot_id']));
            update_option('aetwtaha4cca_telegram_bot_id', $bot_id);
        }
        if (isset($_POST['aetwtaha4cca_telegram_chat_id'])) {
            $chat_id = sanitize_text_field(wp_unslash($_POST['aetwtaha4cca_telegram_chat_id']));
            update_option('aetwtaha4cca_telegram_chat_id', $chat_id);
        }
        if (isset($_POST['aetwtaha4cca_start_time'])) {
            $start_time = sanitize_text_field(wp_unslash($_POST['aetwtaha4cca_start_time']));
            update_option('aetwtaha4cca_start_time', $start_time);
            
            // Clear any existing scheduled events before rescheduling
            $timestamp = wp_next_scheduled('aetwtaha4cca_send_email_event');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'aetwtaha4cca_send_email_event');
            }
            aetwtaha4cca_schedule_cron_job($start_time);
        }
    }

    // Manual test email handling
    if (isset($_POST['aetwtaha4cca_manual_test'])) {
        aetwtaha4cca_send_email();
        echo '<div class="notice notice-success is-dismissible"><p>Test email sent.</p></div>';
    }

    // Clear all options handling
    if (isset($_POST['aetwtaha4cca_clear_settings'])) {
        delete_option('aetwtaha4cca_email_address');
        delete_option('aetwtaha4cca_telegram_bot_id');
        delete_option('aetwtaha4cca_telegram_chat_id');
        delete_option('aetwtaha4cca_start_time');
        echo '<div class="notice notice-success is-dismissible"><p>All settings cleared.</p></div>';
    }

    // Read the log file contents using WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    $log_file_path = plugin_dir_path(__FILE__) . 'emaillog.txt';
    $log_contents = $wp_filesystem->get_contents($log_file_path) ? $wp_filesystem->get_contents($log_file_path) : 'Log file does not exist.';
    
    $email_address = get_option('aetwtaha4cca_email_address');
    $telegram_bot_id = get_option('aetwtaha4cca_telegram_bot_id');
    $telegram_chat_id = get_option('aetwtaha4cca_telegram_chat_id');
    $next_event = wp_next_scheduled('aetwtaha4cca_send_email_event');
    $next_event_time = $next_event ? gmdate('Y-m-d H:i:s', $next_event) : 'No scheduled email';
    $current_time = gmdate('Y-m-d H:i:s');

    ?>
    <style>
        .aet-dashboard-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; max-width: 1200px; margin: 20px 0; }
        .aet-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .aet-header h1 { margin: 0; padding: 0; color: #1d2327; font-size: 24px; font-weight: 600; }
        .aet-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .aet-card { background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; border-radius: 4px; }
        .aet-card h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #f0f0f1; font-size: 18px; color: #1d2327; }
        .aet-card p { color: #50575e; font-size: 14px; line-height: 1.5; }
        .aet-form-group { margin-bottom: 15px; }
        .aet-form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #1d2327; }
        .aet-form-group input[type="text"], .aet-form-group input[type="email"], .aet-form-group input[type="time"] { width: 100%; max-width: 100%; padding: 8px; border: 1px solid #8c8f94; border-radius: 4px; }
        .aet-status-box { background: #f6f7f7; padding: 15px; border-radius: 4px; margin-bottom: 10px; border-left: 4px solid #2271b1; }
        .aet-status-title { font-size: 12px; text-transform: uppercase; color: #646970; font-weight: 600; margin-bottom: 5px; }
        .aet-status-value { font-size: 18px; color: #1d2327; font-weight: 500; }
        .aet-status-value span { color: #2271b1; }
        .aet-log-viewer { background: #2c3338; color: #00ff00; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap; margin-top: 10px; }
        .aet-actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; border-top: 1px solid #f0f0f1; padding-top: 20px; }
        .aet-notice { background: #fff8e5; border-left: 4px solid #ffb900; padding: 10px; font-size: 13px; margin-bottom: 15px; }
        .aet-info-list li { margin-bottom: 8px; font-size: 13px; color: #3c434a; }
        .aet-attribution { margin-top: 20px; text-align: center; font-size: 12px; color: #a7aaad; }
        @media (max-width: 782px) { .aet-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="wrap aet-dashboard-wrapper">
        <div class="aet-header">
            <h1>Email Scheduler & Telegram Alerts</h1>
            <a href="https://AZBrand.ca" target="_blank" style="text-decoration: none; color: #2271b1; font-weight: 600;">AZBrand.ca</a>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('aetwtaha4cca_save_settings', 'aetwtaha4cca_nonce'); ?>
            
            <div class="aet-grid">
                <!-- Left Column: Settings -->
                <div class="aet-column-left">
                    <div class="aet-card">
                        <h2>Configuration</h2>
                        <p>Configure your email destination and Telegram bot details below.</p>
                        
                        <div class="aet-form-group">
                            <label for="aetwtaha4cca_email_address">Email Address</label>
                            <input type="email" id="aetwtaha4cca_email_address" name="aetwtaha4cca_email_address" value="<?php echo esc_attr($email_address); ?>" placeholder="you@example.com" />
                            <p class="description">The address where test emails will be sent.</p>
                        </div>

                        <div class="aet-form-group">
                            <label for="aetwtaha4cca_telegram_bot_id">Telegram Bot ID</label>
                            <input type="text" id="aetwtaha4cca_telegram_bot_id" name="aetwtaha4cca_telegram_bot_id" value="<?php echo esc_attr($telegram_bot_id); ?>" placeholder="123456789:ABC..." />
                        </div>

                        <div class="aet-form-group">
                            <label for="aetwtaha4cca_telegram_chat_id">Telegram Chat ID</label>
                            <input type="text" id="aetwtaha4cca_telegram_chat_id" name="aetwtaha4cca_telegram_chat_id" value="<?php echo esc_attr($telegram_chat_id); ?>" placeholder="-100..." />
                        </div>

                        <div class="aet-form-group">
                            <label for="aetwtaha4cca_start_time">Start Time (UTC)</label>
                            <input type="time" id="aetwtaha4cca_start_time" name="aetwtaha4cca_start_time" value="<?php echo esc_attr(gmdate('H:i', wp_next_scheduled('aetwtaha4cca_send_email_event'))); ?>" />
                            <p class="description">Set when the 6-hour cycle should begin.</p>
                        </div>

                        <div class="aet-notice">
                            <strong>Suggestion:</strong> Create an email filter to automatically move these test emails to trash to keep your inbox clean.
                        </div>

                        <div class="aet-actions">
                            <?php submit_button('Save Settings', 'primary', 'aetwtaha4cca_save_settings', false); ?>
                            <?php submit_button('Send Test Email', 'secondary', 'aetwtaha4cca_manual_test', false); ?>
                            <?php submit_button('Clear All Settings', 'secondary', 'aetwtaha4cca_clear_settings', false); ?>
                        </div>
                    </div>

                    <div class="aet-card" style="margin-top: 20px;">
                        <h2>System Logs</h2>
                        <p>Current log status (Last Entry):</p>
                        <div class="aet-log-viewer"><?php echo esc_html($log_contents); ?></div>
                    </div>
                </div>

                <!-- Right Column: Status & Info -->
                <div class="aet-column-right">
                    <div class="aet-card">
                        <h2>System Status</h2>
                        
                        <div class="aet-status-box">
                            <div class="aet-status-title">Current Server Time (UTC)</div>
                            <div class="aet-status-value"><span id="aetwtaha4cca-current-server-time"><?php echo esc_html($current_time); ?></span></div>
                        </div>

                        <div class="aet-status-box">
                            <div class="aet-status-title">Next Scheduled Send</div>
                            <div class="aet-status-value"><span id="aetwtaha4cca-next-email-send"><?php echo esc_html($next_event_time); ?></span></div>
                        </div>

                        <div class="aet-status-box" style="border-left-color: #46b450;">
                            <div class="aet-status-title">Countdown</div>
                            <div class="aet-status-value"><span id="aetwtaha4cca-countdown-timer">Calculating...</span></div>
                        </div>
                    </div>

                    <div class="aet-card" style="margin-top: 20px;">
                        <h2>Quick Guide</h2>
                        <ul class="aet-info-list">
                            <li><strong>Step 1:</strong> Enter your email.</li>
                            <li><strong>Step 2:</strong> Add Telegram Bot & Chat ID.</li>
                            <li><strong>Step 3:</strong> Save Settings.</li>
                            <li><strong>Step 4:</strong> Use "Send Test Email" to verify.</li>
                        </ul>
                        <hr style="border: 0; border-top: 1px solid #f0f0f1; margin: 15px 0;">
                        <p><strong>Low Traffic Warning:</strong> <br>Tests might not run exactly every 6 hours if your site has low traffic due to WP-Cron limitations.</p>
                        
                        <p><strong>Need tighter monitoring?</strong><br>
                        <a href="https://github.com/AZBrandCanada/WordPress-Automatic-Email-Testing-With-Telegram-Advanced-Alerts-Serverside" target="_blank">Get the Advanced Python Script</a> for 5-second server-side alerts.</p>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="aet-attribution">
            Technical Support & Feature Requests: <a href="https://AZBrand.ca" target="_blank">AZBrand.ca</a>
        </div>
    </div>
    <?php
}

// Add the options page to the menu
function aetwtaha4cca_admin_menu() {
    add_options_page(
        'Email Scheduler Settings',
        'Email Scheduler',
        'manage_options',
        'email-scheduler',
        'aetwtaha4cca_options_page'
    );
}
add_action('admin_menu', 'aetwtaha4cca_admin_menu');

// Enqueue JavaScript
function aetwtaha4cca_enqueue_scripts($hook) {
    // Only enqueue on the settings page
    if ($hook != 'settings_page_email-scheduler') {
        return;
    }

    // Register the script
    wp_register_script(
        'email-scheduler-js', // Handle
        plugin_dir_url(__FILE__) . 'assets/js/email-scheduler.js', // Path to your JS file
        array(), // Dependencies (if any)
        '1.0.0', // Version number
        true // Load in footer
    );

    // FIX: Calculate the next scheduled event time dynamically
    // Previously this was looking for a non-existent option 'next_event_time'
    $next_timestamp = wp_next_scheduled('aetwtaha4cca_send_email_event');
    $next_event_time_js = $next_timestamp ? esc_js(gmdate('c', $next_timestamp)) : '';

    // Localize script to pass PHP variables to JavaScript
    wp_localize_script('email-scheduler-js', 'nextEventData', array(
        'nextEventTime' => $next_event_time_js,
    ));

    // Enqueue the script
    wp_enqueue_script('email-scheduler-js');
}
add_action('admin_enqueue_scripts', 'aetwtaha4cca_enqueue_scripts');


// Define the cron job function
function aetwtaha4cca_schedule_cron_job($start_time) {
    $timestamp = strtotime($start_time . ' UTC');
    wp_schedule_event($timestamp, 'aetwtaha4cca_every_six_hours', 'aetwtaha4cca_send_email_event');
}