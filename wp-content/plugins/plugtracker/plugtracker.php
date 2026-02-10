<?php
/**
 * Plugin Name: PlugTracker
 * Description: Tracks plugin activity, including activation, deactivation, addition, deletion, and updates, with date, time, and user information recorded.
 * Version: 1.0
 * Author: Guru Plugins
 * Author URI: https://plugins.guru-is.com
 * Plugin URI: https://plugins.guru-is.com/plugtracker/
 * Text Domain: plugtracker
 * Requires at least: 5.2
 * Requires PHP: 7.4
 * Tested up to: 6.7.1
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('admin_init', function () {
    $pro_plugin_slug = 'plugin-tracker-pro/plugin-tracker-pro.php';

    if (function_exists('is_plugin_active') && is_plugin_active($pro_plugin_slug)) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate this plugin
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Plugin Tracker cannot be activated because Plugin Tracker Pro is installed and active.', 'plugtracker');
            echo '</p></div>';
        });
    }
});

class PlugTracker {

    private $actions_table;
    private $updates_table;

    public function __construct() {
        global $wpdb;
        $this->actions_table = $wpdb->prefix . 'plugtracker_actions';
        $this->updates_table = $wpdb->prefix . 'plugtracker_updates';

        register_activation_hook(__FILE__, [$this, 'plugtracker_activate_plugin']);
		register_deactivation_hook(__FILE__, [$this, 'plugtracker_deactivate_plugin']);

		add_action('admin_menu', [$this, 'plugtracker_add_admin_menu']);
		add_action('admin_enqueue_scripts', [$this, 'plugtracker_enqueue_scripts']);
		add_action('activated_plugin', [$this, 'plugtracker_track_plugin_activated'], 10, 2);
		add_action('deactivated_plugin', [$this, 'plugtracker_track_plugin_deactivated'], 10, 2);
		add_action('upgrader_process_complete', [$this, 'plugtracker_track_plugin_added_or_updated'], 10, 2);
		add_action('deleted_plugin', [$this, 'plugtracker_track_plugin_deleted'], 10, 1);

		add_action('upgrader_source_selection', [$this, 'plugtracker_store_plugin_snapshot_before_install'], 10, 1);

		add_action('wp_ajax_plugtracker_delete_data', [$this, 'plugtracker_delete_all_data']);
    }

	public function plugtracker_activate_plugin()
	{
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $actions_table_sql = "CREATE TABLE {$this->actions_table} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            action_time DATETIME NOT NULL,
            plugin_name VARCHAR(255) NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            user_name VARCHAR(255) NOT NULL
        ) $charset_collate;";

        $updates_table_sql = "CREATE TABLE {$this->updates_table} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            update_time DATETIME NOT NULL,
            plugin_name VARCHAR(255) NOT NULL,
            new_version VARCHAR(50) NOT NULL,
            user_name VARCHAR(255) NOT NULL
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($actions_table_sql);
        dbDelta($updates_table_sql);
    }

	public function plugtracker_deactivate_plugin()
	{
		// Mark the plugin as deactivated using an option
		update_option('plugtracker_active', false);

		// Optionally clear related data or cache (if necessary)
		wp_cache_delete('plugtracker_actions');
		wp_cache_delete('plugtracker_updates');

		// Log the deactivation event using an option for audit purposes
		$deactivation_log = get_option('plugtracker_deactivation_log', []);
		$deactivation_log[] = [
			'time' => current_time('mysql'),
			'user' => wp_get_current_user()->user_login,
		];
		update_option('plugtracker_deactivation_log', $deactivation_log);
	}

	public function plugtracker_add_admin_menu()
	{
		add_submenu_page(
		'plugins.php',
		'PlugTracker',
		'PlugTracker',
		'manage_options',
		'plugtracker',
		[$this, 'plugtracker_render_admin_dashboard']
		);
	}

	public function plugtracker_enqueue_scripts($hook)
	{
		// Check if the current page is the plugin's admin page
		if ($hook !== 'toplevel_page_plugtracker' && $hook !== 'plugins_page_plugtracker') {
			return;
		}

		// Enqueue the JavaScript file for admin functionality
		wp_enqueue_script(
		'plugtracker-script',
		plugin_dir_url(__FILE__) . 'assets/js/admin.js', // Updated path to JavaScript file
		['jquery'], // Dependencies
		'1.0.0', // Version
		true // Load in the footer
		);

		// Localize the script with AJAX URL and nonce
		wp_localize_script('plugtracker-script', 'WPTAjax', [
			'ajax_url' => admin_url('admin-ajax.php'), // AJAX endpoint
			'nonce' => wp_create_nonce('wpt_delete_data_nonce'), // Nonce for security
		]);

		// Enqueue the CSS file for admin styling
		wp_enqueue_style(
		'plugtracker-style',
		plugin_dir_url(__FILE__) . 'assets/css/style.css', // Updated path to CSS file
		[], // No dependencies
		'1.0.0' // Version
		);
	}

	public function plugtracker_render_admin_dashboard()
	{
		global $wpdb;

		$actions = wp_cache_get('plugtracker_actions_data');

		if ($actions === false) {
			// Cache miss, perform the database query
			$actions = $wpdb->get_results("SELECT action_time, plugin_name, action_type, user_name FROM {$wpdb->prefix}plugtracker_actions ORDER BY action_time DESC");

			// Store results in cache for 1 hour
			wp_cache_set('plugtracker_actions_data', $actions, '', HOUR_IN_SECONDS);
		}

		$updates = wp_cache_get('plugtracker_updates_data');

		if ($updates === false) {
			// Cache miss, perform the database query
			$updates = $wpdb->get_results("SELECT update_time, plugin_name, new_version, user_name FROM {$wpdb->prefix}plugtracker_updates ORDER BY update_time DESC");

			// Store results in cache for 1 hour
			wp_cache_set('plugtracker_updates_data', $updates, '', HOUR_IN_SECONDS);
		}

		// Output HTML for tabs and tables
		echo '<div class="wrap">';
		echo '<h1>PlugTracker</h1>';

		echo '<h2><strong>Looking for more features? <a style="text-decoration:none;" href="https://plugins.guru-is.com/product/wp-plugin-tracker-pro/" target="_blank">GO PRO!</a></strong></h2>';
		echo '<p>Unlock advanced features with <strong><a style="text-decoration:none;" href="https://plugins.guru-is.com/product/wp-plugin-tracker-pro/" target="_blank">PlugTracker Pro</a></strong>! Track plugin activity, including activations, updates, and deletions, complete with user information and timestamps. Enjoy <strong>exclusive admin access</strong>, giving you full control and security over your plugin activity data.</p>';
		echo '<a style="text-decoration:none;" href="https://plugins.guru-is.com/product/wp-plugin-tracker-pro/" target="_blank">';
		echo '<img style="max-width:400px; border-radius:15px;" src="' . esc_html(plugin_dir_url(__FILE__)) . 'plugin-tracker-pro-screenshot.png" alt="Plugin Tracker Pro Screenshot"></a><br/><br/>';

		// Tabs
		echo '<div class="tab-container">';
		echo '<button class="tab-button active" data-tab="plugin-actions-tab">Plugin Actions</button>';
		echo '<button class="tab-button" data-tab="plugin-updates-tab">Plugin Updates</button>';
		echo '</div>';

		// Plugin Actions Table
		echo '<div id="plugin-actions-tab" class="tab-content active">';
		echo '<h2>Plugin Actions</h2>';
		echo '<table class="widefat fixed" cellspacing="0">';
		echo '<thead><tr><th>Date/Time</th><th>Plugin Name</th><th>Action</th><th>User</th></tr></thead>';
		echo '<tbody>';

		if (!empty($actions)) {
			foreach ($actions as $action) {
				echo '<tr>';
				echo '<td>' . esc_html($action->action_time) . '</td>';
				echo '<td>' . esc_html($action->plugin_name) . '</td>';
				echo '<td>' . esc_html($action->action_type) . '</td>';
				echo '<td>' . esc_html($action->user_name) . '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="4">' . esc_html__('No actions recorded.', 'plugtracker') . '</td></tr>';
		}

		echo '</tbody></table>';
		echo '</div>';

		// Plugin Updates Table
		echo '<div id="plugin-updates-tab" class="tab-content">';
		echo '<h2>Plugin Updates</h2>';
		echo '<table class="widefat fixed" cellspacing="0">';
		echo '<thead><tr><th>Date/Time</th><th>Plugin Name</th><th>New Version</th><th>User</th></tr></thead>';
		echo '<tbody>';

		if (!empty($updates)) {
			foreach ($updates as $update) {
				echo '<tr>';
				echo '<td>' . esc_html($update->update_time) . '</td>';
				echo '<td>' . esc_html($update->plugin_name) . '</td>';
				echo '<td>' . esc_html($update->new_version) . '</td>';
				echo '<td>' . esc_html($update->user_name) . '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="4">' . esc_html__('No updates recorded.', 'plugtracker') . '</td></tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
		echo '</div>'; // End wrap
	}

	private function plugtracker_get_plugin_name($plugin_file)
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		return $all_plugins[$plugin_file]['Name'] ?? $plugin_file;
	}

	public function plugtracker_track_plugin_activated($plugin, $network_wide)
	{
		$plugin_name = $this->plugtracker_get_plugin_name($plugin);
		$this->plugtracker_log_action($plugin_name, 'Activated');
    }

	public function plugtracker_track_plugin_deactivated($plugin, $network_wide)
	{
		$plugin_name = $this->plugtracker_get_plugin_name($plugin);
		$this->plugtracker_log_action($plugin_name, 'Deactivated');
    }

	public function plugtracker_track_plugin_deleted($plugin_name)
	{
		global $wpdb;

		// Sanitize the plugin name
		$plugin_name = sanitize_text_field($plugin_name);

		// Prepare data for insertion
		$data = [
			'action_time' => current_time('mysql'), // Current time in MySQL format
			'plugin_name' => $plugin_name,
			'action_type' => 'Deleted',
			'user_name' => sanitize_text_field(wp_get_current_user()->user_login),
		];

		// Data formats for insertion
		$data_formats = ['%s', '%s', '%s', '%s'];

		// Insert data into the database
		$inserted = $wpdb->insert(
		"{$wpdb->prefix}plugtracker_actions", // Table name
		$data, // Data to insert
		$data_formats // Data formats
		);

		if ($inserted !== false) {
			// Clear cache for the actions table
			wp_cache_delete('plugtracker_actions');
		}
	}

	public function plugtracker_store_plugin_snapshot_before_install($source)
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		set_transient('plugin_tracker_snapshot_before_install', array_keys(get_plugins()), 3600);
		return $source;
	}

	public function plugtracker_track_plugin_added_or_updated($upgrader, $hook_extra)
	{
        if (isset($hook_extra['type']) && $hook_extra['type'] === 'plugin' && isset($hook_extra['action'])) {
            global $wpdb;

            $current_user = wp_get_current_user();
            $current_time = current_time('mysql');

            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $snapshot_before = get_transient('plugin_tracker_snapshot_before_install') ?: [];
            $all_plugins = array_keys(get_plugins());
            $new_plugins = array_diff($all_plugins, $snapshot_before);

            if ($hook_extra['action'] === 'install') {
                foreach ($new_plugins as $plugin_file) {
                    $plugin_data = get_plugins()[$plugin_file];
                    $wpdb->insert("{$wpdb->prefix}plugtracker_actions", [
                        'action_time' => $current_time,
                        'plugin_name' => $plugin_data['Name'] ?? $plugin_file,
                        'action_type' => 'Added',
                        'user_name' => $current_user->user_login,
                    ]);
                }
            }

            if ($hook_extra['action'] === 'update') {
                foreach ($hook_extra['plugins'] as $plugin_file) {
                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
                    $wpdb->insert("{$wpdb->prefix}plugtracker_updates", [
                        'update_time' => $current_time,
                        'plugin_name' => $plugin_data['Name'] ?? $plugin_file,
                        'new_version' => $plugin_data['Version'] ?? 'Unknown',
                        'user_name' => $current_user->user_login ?: 'Auto-update - System',
                    ]);
                }
            }
        }
    }

	private function plugtracker_log_action($plugin, $action)
	{
        global $wpdb;
        $user = wp_get_current_user();
        $user_name = $user->exists() ? $user->user_login : 'System';

        $wpdb->insert("{$wpdb->prefix}plugtracker_actions", [
            'action_time' => current_time('mysql'),
            'plugin_name' => $plugin,
            'action_type' => $action,
            'user_name' => $user_name,
        ]);
    }

	public function plugtracker_delete_all_data()
	{
		global $wpdb;

		// Clear caches for plugin actions and updates
		wp_cache_delete('plugtracker_actions');
		wp_cache_delete('plugtracker_updates');

		// Use delete instead of truncate to avoid schema-related warnings
		$actions_table = $wpdb->prefix . 'plugtracker_actions';
		$updates_table = $wpdb->prefix . 'plugtracker_updates';

		// Delete all rows in actions table
		$wpdb->delete($actions_table, ['1' => '1']);

		// Delete all rows in updates table
		$wpdb->delete($updates_table, ['1' => '1']);

		wp_send_json_success('All data deleted successfully.');
	}
}

new PlugTracker();


