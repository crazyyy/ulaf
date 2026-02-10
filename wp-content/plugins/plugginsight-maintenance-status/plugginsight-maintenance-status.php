<?php
/**
 * Plugin Name: PluggInsight - Maintenance Status
 * Plugin URI: https://wordpress.org/plugins/plugginsight-maintenance-status/
 * Description: Easily access maintenance details for each plugin directly on the WordPress plugin page.
 * Version: 1.0.4
 * Author: Alan Jacob Mathew
 * Author URI: https://profiles.wordpress.org/alanjacobmathew/
 * Tested up to: 6.9
 * Text Domain: plugginsight-maintenance-status
 * Domain Path: /languages/
 * License: GPLv3 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Global variable for the major releases versions.
$plugginsight_major_releases = array(
    '5.0', '5.1', '5.2', '5.3', '5.4', '5.5', '5.6', '5.7', '5.8', '5.9', '6.0', '6.1', '6.2' , '6.3','6.4','6.5','6.6','6.7','6.8','6.9'
);
$plugginsight_upcoming_major_release = '7.0';

register_activation_hook(__FILE__, 'plugginsight_maintenance_status_activate_pmswp');
register_deactivation_hook(__FILE__, 'plugginsight_maintenance_status_deactivate_pmswp');
register_uninstall_hook(__FILE__, 'plugginsight_maintenance_status_uninstall_pmswp');

function plugginsight_maintenance_status_activate_pmswp() {
    //add_filter('manage_plugins_columns', 'plugginsight_add_column_to_plugins_page_pmswp');
}

function plugginsight_maintenance_status_deactivate_pmswp() {
	// Clear transient cache for all installed plugins 
    plugginsight_maintenance_status_clear_cache_only_pmswp();
}


/* Add a submenu page under the Plugins menu */
add_action('admin_menu', 'plugginsight_maintenance_status_add_submenu_page_pmswp');

function plugginsight_maintenance_status_add_submenu_page_pmswp() {
    add_plugins_page(
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        __('Maintenance Status', 'plugginsight-maintenance-status'),
        'manage_options',
        'plugginsight-maintenance-status',
        'plugginsight_maintenance_status_render_page_pmswp'
    );
}

function plugginsight_maintenance_status_add_docs_link_pmswp($plugin_meta, $plugin_file) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        $docs_url = 'https://projektisle.com/docs/plugginsight/?utm_campaign=plugginsight-setting-page&utm_source=plugin'; 
        $plugin_meta[] = sprintf('<a href="%s" target="_blank">%s</a>', esc_url($docs_url), esc_html__('Docs', 'plugginsight-maintenance-status'));
    }
    return $plugin_meta;
}
add_filter('plugin_row_meta', 'plugginsight_maintenance_status_add_docs_link_pmswp', 10, 2);


/* Render the plugin page content */
function plugginsight_maintenance_status_render_page_pmswp() {
    /* Check if the user has access */
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1><span class="dashicons dashicons-plugins-checked"></span> ' . esc_html( __('Maintenance Status Settings', 'plugginsight-maintenance-status') ) . '</h1>';
    echo '<p>' . esc_html( __('Manage Maintenance Status Cache', 'plugginsight-maintenance-status') ) . '</p>';
    echo '<p><em>' . esc_html( __('Disclaimer:', 'plugginsight-maintenance-status') ). ' ' . esc_html( __('The plugin and its developer do not independently verify the accuracy or validity of each plugin\'s data. The data displayed is obtained from the WP plugin repository, where individual plugin authors have tested and reported that their plugins are compatible with the latest WordPress versions. All the displayed logic in this plugin is based on that data.', 'plugginsight-maintenance-status') ). '</em></p>';
    echo '<p><strong><em>' . esc_html( __('The below process will depend on the number of plugins you have installed. More plugins installed, more time it will take to reload this cache.', 'plugginsight-maintenance-status') ). '</em></strong></p>';

    /* Clear Cache button */
    echo '<form method="post">';
	echo '<input type="hidden" name="plugginsight_maintenance_status_clear_cache_nonce" value="' . esc_attr(wp_create_nonce('plugginsight_maintenance_status_clear_cache_nonce')) . '">';

    echo '<input type="submit" name="plugginsight_maintenance_status_clear_cache" class="button" value="' . esc_html( __('Clear Status Cache', 'plugginsight-maintenance-status') ). '">';
    echo '</form>';
	echo '<p style="margin-top: 1em;">' . 
    esc_html__('Having issues?', 'plugginsight-maintenance-status') . ' ' .
    '<a href="https://wordpress.org/support/plugin/plugginsight-maintenance-status/" target="_blank">' . esc_html__('Reach out via the community support forum', 'plugginsight-maintenance-status') . '</a> ' . 
    esc_html__('or visit the', 'plugginsight-maintenance-status') . ' ' .
    '<a href="https://projektisle.com/docs/plugginsight/?utm_campaign=plugginsight-setting-page&utm_source=plugin" target="_blank">' . esc_html__('documentation page', 'plugginsight-maintenance-status') . '</a> ' . 
    esc_html__('for help.', 'plugginsight-maintenance-status') .
    '</p>';


    /* Handle cache clearing and refreshing, when 'Clear Status Cache button is clicked' */
if (isset($_POST['plugginsight_maintenance_status_clear_cache'])) {
    $nonce = isset($_POST['plugginsight_maintenance_status_clear_cache_nonce']) 
        ? sanitize_text_field(wp_unslash($_POST['plugginsight_maintenance_status_clear_cache_nonce'])) 
        : '';

    if ($nonce && wp_verify_nonce($nonce, 'plugginsight_maintenance_status_clear_cache_nonce')) {
        // Clear and refresh cache for all plugins
        plugginsight_maintenance_status_clear_and_refresh_cache_pmswp();

        // Display success notice
        echo '<div class="notice notice-success"><p>' . esc_html(__('Cache cleared and refreshed successfully.', 'plugginsight-maintenance-status')) . '</p></div>';
    } else {
        // Display error notice for invalid nonce
        echo '<div class="notice notice-error"><p>' . esc_html(__('Invalid security token. Cache clearing and refreshing failed.', 'plugginsight-maintenance-status')) . '</p></div>';
    }
}


echo '</div>'; // End wrap container

}

/* Function is called from the 'Clear Status Cache' button */
function plugginsight_maintenance_status_clear_and_refresh_cache_pmswp() {
    
    if ( ! function_exists('get_plugins') ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
    $plugin_slugs = array();

    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $slug = dirname($plugin_file);
        if ($slug === '.' || empty($slug)) {
            $slug = basename($plugin_file, '.php');
        }
        $plugin_slugs[] = $slug;
    }

    // Clear cache for each plugin slug
    foreach ($plugin_slugs as $slug) {
        delete_transient('plugginsight_maintenance_status_' . $slug);
    }

    // Refresh cache for each plugin slug by fetching fresh data
    foreach ($plugin_slugs as $slug) {
        $plugin_data = plugginsight_maintenance_status_get_plugin_data_pmswp($slug);
        if ($plugin_data !== null) {
            set_transient('plugginsight_maintenance_status_' . $slug, $plugin_data, 86400);
        }
    }
}

/* Dedicated function to only clear the cache. Function called only at the deactivation hook */
function plugginsight_maintenance_status_clear_cache_only_pmswp() {
    // Check if the get_plugins function exists
    if ( ! function_exists('get_plugins') ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();

    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $slug = dirname($plugin_file);
        if ($slug === '.' || empty($slug)) {
            $slug = basename($plugin_file, '.php');
        }
        delete_transient('plugginsight_maintenance_status_' . $slug);
    }
}



/* Quick link to the plugin page from the plugin.php page */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugginsight_maintenance_status_add_plugin_page_link_pmswp');

function plugginsight_maintenance_status_add_plugin_page_link_pmswp($links) {
    $plugin_page_link = admin_url('plugins.php?page=plugginsight-maintenance-status');
    $new_link = '<a href="' . $plugin_page_link . '">' . esc_html( __('Settings', 'plugginsight-maintenance-status') ). '</a>';
    array_push($links, $new_link);
    return $links;
}



add_filter('manage_plugins_columns', 'plugginsight_add_column_to_plugins_page_pmswp');
function plugginsight_add_column_to_plugins_page_pmswp($columns) {
    
    $columns['maintenance_status_column'] = sprintf(
        esc_html(__('Maintenance Status', 'plugginsight-maintenance-status')) . ' <a href="%s" class="plugginsight_clear-cache-link" title="%s"><span class="dashicons dashicons-plugins-checked"></span></a>',
        esc_url(admin_url('admin.php?page=plugginsight-maintenance-status')),
        esc_html(__('Update Status Cache', 'plugginsight-maintenance-status'))
    );

    return $columns;
}



function plugginsight_get_latest_major_wp_release_pmswp() {
    global $plugginsight_major_releases;
    return end($plugginsight_major_releases);
}

function plugginsight_generate_status_bar_pmswp($tested_up_to) {
    global $plugginsight_major_releases, $plugginsight_upcoming_major_release;

    $plugginsight_tested_version_parts = explode('.', $tested_up_to);
    $plugginsight_tested_major_release = $plugginsight_tested_version_parts[0] . '.' . $plugginsight_tested_version_parts[1];
    
    if ($tested_up_to === $plugginsight_upcoming_major_release) {
        return '<div class="plugginsight_status-bar skyblue"></div>';
    }

    $plugginsight_latest_major_release = plugginsight_get_latest_major_wp_release_pmswp();
    $plugginsight_latest_major_release_index = array_search($plugginsight_latest_major_release, $plugginsight_major_releases);
    $plugginsight_tested_major_release_index = array_search($plugginsight_tested_major_release, $plugginsight_major_releases);

    $plugginsight_difference = $plugginsight_latest_major_release_index - $plugginsight_tested_major_release_index;

    $plugginsight_status_bar_color = '';

    if ($plugginsight_difference < 1) {
        $plugginsight_status_bar_color = 'green';
    } elseif ($plugginsight_difference >= 1 && $plugginsight_difference < 3) {
        $plugginsight_status_bar_color = 'orange';
    } else {
        $plugginsight_status_bar_color = 'red';
    }

    return '<div class="plugginsight_status-bar ' . esc_html($plugginsight_status_bar_color) . '"></div>';
}



add_action('admin_enqueue_scripts', 'plugginsight_maintenance_status_enqueue_scripts_pmswp');
function plugginsight_maintenance_status_enqueue_scripts_pmswp() {
    /* Register CSS file */
    wp_register_style(
        'plugginsight-maintenance-status', 
        plugins_url('plugginsight-maintenance-status.css', __FILE__), 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.css')
    );
    wp_enqueue_style('plugginsight-maintenance-status');

    /* Register JS file */
    wp_register_script(
        'plugginsight-maintenance-status', 
        plugins_url('plugginsight-maintenance-status.js', __FILE__), 
        array('jquery'), 
        filemtime(plugin_dir_path(__FILE__) . 'plugginsight-maintenance-status.js'), 
        true // Load in footer
    );
    wp_enqueue_script('plugginsight-maintenance-status');
}




function plugginsight_maintenance_status_format_last_updated_pmswp($last_updated) {
    $now = time();
    $lastupdated = sanitize_text_field($last_updated); // Sanitize the input
	$updated_timestamp = strtotime($lastupdated); // Convert to timestamp
	if ($updated_timestamp === false) {
    return esc_html('Data Unavailable');
	}
    $diff = $now - $updated_timestamp;
    $years = floor($diff / (365 * 24 * 60 * 60));
    $months = floor($diff / (30 * 24 * 60 * 60));
    $days = floor($diff / (24 * 60 * 60));

    if ($years > 0) {
        return esc_html($years . ' year' . ($years > 1 ? 's' :  '') . ' ago');
    } elseif ($months > 0) {
    
        return esc_html( $months . ' month' . ($months > 1 ? 's' : '') . ' ago');
    } else if ($days > 0) {
        return esc_html($days . ' day' . ($days > 1 ? 's' : '') . ' ago');
    } else {
        return esc_html('Today');
    }
}


add_action('manage_plugins_custom_column', 'plugginsight_populate_maintenance_status_column_pmswp', 10, 3);

function plugginsight_populate_maintenance_status_column_pmswp($column, $plugin_file, $plugin_data) {
    if ($column === 'maintenance_status_column') {
        $plugin_slug = basename(dirname($plugin_file));
        $plugin_data = plugginsight_maintenance_status_get_plugin_data_pmswp($plugin_slug);    
    
        if ($plugin_data !== null) {
            if (isset($plugin_data->removed) && $plugin_data->removed === true) {
                echo '<strong>' . esc_html(__('Plugin Removed from WP', 'plugginsight-maintenance-status')) . '</strong><br>';
                echo '<strong>' . esc_html(__('Date:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->closed_date ?? '-') . '<br>';
                echo '<strong>' . esc_html(__('Reason:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->reason_text ?? '-') . '<br>';
                echo '<div class="plugginsight_status-bar removed"></div>';
            } elseif (
                empty($plugin_data->version) &&
                empty($plugin_data->last_updated) &&
                empty($plugin_data->tested)
            ) {
                // Invalid or missing data
                echo esc_html(__('Plugin not found in the repository', 'plugginsight-maintenance-status'));
            } else {
                // Show standard info
                echo '<strong>' . esc_html(__('Latest Version:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->version) . '<br>';
                echo '<strong>' . esc_html(__('Last Updated:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html(plugginsight_maintenance_status_format_last_updated_pmswp($plugin_data->last_updated)) . '<br>';
                echo '<strong>' . esc_html(__('Tested Up to:', 'plugginsight-maintenance-status')) . '</strong> ' . esc_html($plugin_data->tested) . '<br>';
                echo wp_kses_post(plugginsight_generate_status_bar_pmswp($plugin_data->tested));
            }
        } else {
            echo esc_html(__('Plugin not found in the repository', 'plugginsight-maintenance-status'));
        }
    }
}






function plugginsight_maintenance_status_get_plugin_data_pmswp($plugin_name) {
    $plugin_slug = sanitize_title($plugin_name);
    if (empty($plugin_slug)) {
        $plugin_slug = $plugin_name;
    }

    $cache_key = 'plugginsight_maintenance_status_' . $plugin_slug;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
      //  echo '<script>console.log("Cache hit for plugin: ' . esc_js($plugin_name) . '");</script>';
        return $cached_data;
    } else {
      //  echo '<script>console.log("Cache miss for plugin: ' . esc_js($plugin_name) . '");</script>';
    }

    if (!function_exists('plugins_api')) {
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
     //   echo '<script>console.log("Loaded plugins_api function for'. esc_js($plugin_name) .'");</script>';
    }

    $call_api = plugins_api('plugin_information', [
        'slug'   => $plugin_slug,
        'fields' => [
            'version'      => true,
            'last_updated' => true,
            'tested'       => true,
        ],
    ]);

    if (is_wp_error($call_api)) {
     //   echo '<script>console.log(" API error for plugin: ' . esc_js($plugin_slug) . ' ");</script>';
        return null;
    }

    $required_data = new stdClass();
    $required_data->version = !empty($call_api->version) ? $call_api->version : null;
    $required_data->last_updated = !empty($call_api->last_updated) ? $call_api->last_updated : null;
    $required_data->tested = !empty($call_api->tested) ? $call_api->tested : null;

  //  echo '<script>console.log(" Fetched data for plugin: ' . esc_js($plugin_name) . '");</script>';

    set_transient($cache_key, $required_data, DAY_IN_SECONDS);

    return $required_data;
}



// Footer on Setting page
 
add_filter('admin_footer_text', 'plugginsight_custom_admin_footer_text_pmswp');

function plugginsight_custom_admin_footer_text_pmswp($footer_text) {
   
    $screen = get_current_screen();
    if ($screen && $screen->id !== 'plugins_page_plugginsight-maintenance-status') {
        return $footer_text;
    }

    $plugin_url = 'https://wordpress.org/plugins/plugginsight-maintenance-status/';
    $review_url = $plugin_url . '#reviews';

    $custom_footer = sprintf(
        '<a href="%s" target="_blank">PluggInsight</a> is free and always will be. If you find it useful, consider <a href="%s" target="_blank">giving us a shoutout</a>. ★★★★★',
        esc_url($plugin_url),
        esc_url($review_url)
    );

    return $custom_footer;
}



/* Uninstall hook to delete language files, only when the plugin is deleted */
function plugginsight_maintenance_status_uninstall_pmswp() {
   
    if (!current_user_can('manage_options')) {
        return;
    }
}	