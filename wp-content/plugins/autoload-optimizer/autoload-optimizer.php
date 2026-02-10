<?php
/*
Plugin Name: Autoload Optimizer
Version: 1.0
Description: Checks the autoloaded data size and lists the top autoloaded data entries sorted by size.
Author: WP Fix Experts
Author URI: https://wpfixexperts.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: autoload-optimizer
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add menu under Tools
add_action('admin_menu', 'autoload_optimizer_menu');
function autoload_optimizer_menu() {
    add_submenu_page(
        'tools.php', // Parent menu slug
        'Autoload Optimizer', // Page title
        'Autoload Optimizer', // Menu title
        'manage_options', // Capability
        'autoload-optimizer', // Menu slug
        'autoload_optimizer_page' // Callback function
    );
}

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'autoload_optimizer_scripts');
function autoload_optimizer_scripts($hook) {
    if ($hook !== 'tools_page_autoload-optimizer') {
        return;
    }
    wp_enqueue_style(
        'autoload-optimizer-style',
        plugins_url('autoload-style.css', __FILE__),
        array(), // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'autoload-style.css') // Dynamic version
    );
    
    wp_enqueue_script(
        'autoload-optimizer-script',
        plugins_url('autoload-script.js', __FILE__),
        array('jquery'), // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'autoload-script.js'), // Dynamic version
        true // Load in footer
    );

    // Localize script for AJAX nonce
    wp_localize_script(
        'autoload-optimizer-script',
        'autoload_optimizer_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('autoload_optimizer_nonce'),
        )
    );
}

// Plugin page content
function autoload_optimizer_page() {
    ?>
    <div class="wrap">
        

        <!-- Welcome Screen -->
        <div class="welcome-screen">
            <h3 style="color: blue;">Welcome to the Autoload Optimizer ! 🚀</h3>
            <p>This powerful tool helps you optimize and manage autoloaded data in your WordPress database, improving site performance and speed. With just a few clicks, you can identify and clean up unnecessary autoloaded entries, reducing database bloat and enhancing website efficiency.    <br>   <br>  <strong>  Warning Note:

⚠ Proceed with Caution! ⚠</strong>
<br>
The Autoload Optimizer Plugin makes direct modifications to your WordPress database. Deleting essential autoloaded data may cause unexpected issues, including broken functionality or site errors.
<br><br>
<strong>📌 Before making changes, please:<br></strong>
✔ Create a full database backup<br>
✔ Review the list of autoloaded options carefully<br>
✔ Test changes on a staging site if possible<br><br>

Use this tool responsibly to avoid any disruptions. If you're unsure, consult a developer or seek professional support.
<br><br>
Your website's stability is our priority! 🚀 </p>
        </div>

        <!-- Database Size and Autoload Data Size -->
        <?php
        $database_size = autoload_optimizer_get_database_size();
        $autoload_size = autoload_optimizer_get_autoload_size();
        ?>
        <div class="database-info">
            <p><strong>Total Database Size:</strong> <?php echo esc_html(size_format($database_size)); ?></p>
            <p><strong>Total Autoload Data Size:</strong> <?php echo esc_html(size_format($autoload_size)); ?></p>
        </div>

        <!-- Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#tab1" class="nav-tab nav-tab-active">Autoload Options</a>
            <a href="#tab2" class="nav-tab">Disabled Options</a>
        </h2>

        <!-- Tab 1: Autoload Options -->
        <div id="tab1" class="tab-content active">
            <?php
            $autoload_options = autoload_optimizer_get_autoload_options();
            if ($autoload_options) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Option Name</th><th>Option Size</th><th>Action</th></tr></thead>';
                echo '<tbody>';
                foreach ($autoload_options as $option_name => $option_value) {
                    $option_size = strlen(maybe_serialize($option_value));
                    echo '<tr>';
                    echo '<td>' . esc_html($option_name) . '</td>';
                    echo '<td>' . esc_html(size_format($option_size)) . '</td>';
                    echo '<td>
                            <button class="button view-value" data-value="' . esc_attr(maybe_serialize($option_value)) . '">View Value</button>
                            <button class="button button-danger disable-option" data-option="' . esc_attr($option_name) . '">Disable</button>
                          </td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No autoload options found.</p>';
            }
            ?>
        </div>

        <!-- Tab 2: Disabled Options -->
        <div id="tab2" class="tab-content">
            <?php
            $disabled_options = get_option('autoload_optimizer_disabled_options', array());
            if ($disabled_options) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Option Name</th><th>Action</th></tr></thead>';
                echo '<tbody>';
                foreach ($disabled_options as $option_name) {
                    echo '<tr>';
                    echo '<td>' . esc_html($option_name) . '</td>';
                    echo '<td><button class="button button-primary restore-option" data-option="' . esc_attr($option_name) . '">Restore</button></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No disabled options found.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Popup for viewing option value -->
    <div id="value-popup" class="popup-modal">
        <div class="popup-content">
            <h3>Option Value</h3>
            <pre id="popup-value"></pre>
            <button id="close-popup" class="button button-primary">Close</button>
        </div>
    </div>
    <?php
}

// Helper function to get database size with caching
function autoload_optimizer_get_database_size() {
    // Check if the database size is cached
    $database_size = wp_cache_get('autoload_optimizer_database_size', 'autoload_optimizer');

    if (false === $database_size) {
        global $wpdb;
        // Get the database size from the database
        $database_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE()");
        $database_size = $database_size ? $database_size : 0;

        // Cache the database size for 12 hours
        wp_cache_set('autoload_optimizer_database_size', $database_size, 'autoload_optimizer', 12 * HOUR_IN_SECONDS);
    }

    return $database_size;
}

// Helper function to get autoload size with caching
function autoload_optimizer_get_autoload_size() {
    // Check if the autoload size is cached
    $autoload_size = wp_cache_get('autoload_optimizer_autoload_size', 'autoload_optimizer');

    if (false === $autoload_size) {
        $autoload_options = autoload_optimizer_get_autoload_options();
        $autoload_size = 0;

        // Calculate the total size of autoloaded options
        foreach ($autoload_options as $option_value) {
            $autoload_size += strlen(maybe_serialize($option_value));
        }

        // Cache the autoload size for 12 hours
        wp_cache_set('autoload_optimizer_autoload_size', $autoload_size, 'autoload_optimizer', 12 * HOUR_IN_SECONDS);
    }

    return $autoload_size;
}

// Helper function to get autoload options (top 20 by size)
function autoload_optimizer_get_autoload_options() {
    $all_options = wp_load_alloptions();
    $options_with_size = array();

    // Calculate size for each option
    foreach ($all_options as $option_name => $option_value) {
        $options_with_size[$option_name] = strlen(maybe_serialize($option_value));
    }

    // Sort options by size in descending order
    arsort($options_with_size);

    // Get the top 20 options
    $top_options = array_slice($options_with_size, 0, 20, true);

    // Fetch the full option data for the top 20
    $autoload_options = array();
    foreach ($top_options as $option_name => $size) {
        $autoload_options[$option_name] = get_option($option_name);
    }

    return $autoload_options;
}

// AJAX handler to disable autoload option
add_action('wp_ajax_disable_autoload_option', 'autoload_optimizer_disable_option');
function autoload_optimizer_disable_option() {
    // Verify nonce
    // Validate the nonce
if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'autoload_optimizer_nonce')) {
    // Sanitize and escape the error message before sending it
    $error_message = esc_html__('Invalid nonce.', 'your-text-domain');
    wp_send_json_error($error_message);
}

    if (!isset($_POST['option_name'])) {
        wp_send_json_error('Option name is required.');
    }
    $option_name = sanitize_text_field($_POST['option_name']);
    update_option($option_name, get_option($option_name), 'no'); // Set autoload to 'no'
    $disabled_options = get_option('autoload_optimizer_disabled_options', array());
    $disabled_options[] = $option_name;
    update_option('autoload_optimizer_disabled_options', $disabled_options);

    // Clear the autoload size cache after disabling an option
    wp_cache_delete('autoload_optimizer_autoload_size', 'autoload_optimizer');

    wp_send_json_success('Option disabled successfully.');
}

// AJAX handler to restore autoload option
add_action('wp_ajax_restore_autoload_option', 'autoload_optimizer_restore_option');
function autoload_optimizer_restore_option() {
    // Verify nonce
    if (
        !isset($_POST['nonce']) || 
        !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'autoload_optimizer_nonce')
    ) {
        wp_send_json_error('Invalid nonce.');
    }
    
    if (!isset($_POST['option_name'])) {
        wp_send_json_error('Option name is required.');
    }
    $option_name = sanitize_text_field($_POST['option_name']);
    update_option($option_name, get_option($option_name), 'yes'); // Set autoload to 'yes'
    $disabled_options = get_option('autoload_optimizer_disabled_options', array());
    $disabled_options = array_diff($disabled_options, array($option_name));
    update_option('autoload_optimizer_disabled_options', $disabled_options);

    // Clear the autoload size cache after restoring an option
    wp_cache_delete('autoload_optimizer_autoload_size', 'autoload_optimizer');

    wp_send_json_success('Option restored successfully.');
}