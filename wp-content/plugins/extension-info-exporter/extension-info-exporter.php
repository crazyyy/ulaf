<?php

/**
 * Plugin Name: Extension Info Exporter
 * Description: The ultimate WordPress plugin export tool! Export plugin details in 4 formats (CSV, JSON, TXT, XML) with advanced filtering, custom filenames, and a stunning modern interface. Perfect for agencies, developers, and site managers who need comprehensive plugin audits and inventory management.
 * Version: 4.0
 * Author: Dhaval Vachhani
 * Author URI: https://dhavalwp.com
 * Text Domain: extension-info-exporter
 * Requires at least: 5.0
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXT_INFO_EXPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once EXT_INFO_EXPORTER_PLUGIN_DIR . 'includes/admin-settings.php';

// Hook to add admin menu
add_action('admin_menu', 'ext_info_exporter_add_admin_menu');

function ext_info_exporter_add_admin_menu()
{
    add_menu_page(
        'Extension Info Exporter',           // Page title
        'Extension Info Exporter',           // Menu title
        'manage_options',                    // Capability (admin users)
        'extension-info-exporter',           // Menu slug
        'ext_info_exporter_settings_page',   // Callback function
        'dashicons-list-view',               // Icon
        20                                   // Position
    );
}

// Handle export request
add_action('admin_post_ext_info_exporter_export', 'ext_info_exporter_export_plugin_info');

function ext_info_exporter_export_plugin_info() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (!isset($_POST['ext_info_exporter_export_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ext_info_exporter_export_nonce'])), 'ext_info_exporter_export_action')) {
        return;
    }

    // Get selected fields from settings
    $fields = get_option('ext_info_exporter_export_fields', array());

    // Ensure "name" is always included in the export (but not twice)
    if (!is_array($fields) || empty($fields)) {
        $fields = array('name');
    } elseif (!in_array('name', $fields)) {
        // Always include 'name' as the first field
        array_unshift($fields, 'name');
    } else {
        // Move 'name' to the first position if it exists in the array
        $key = array_search('name', $fields);
        if ($key !== false) {
            unset($fields[$key]);
            array_unshift($fields, 'name');
        }
    }

    // Get export type and optional recent days
    $export_type = isset($_POST['ext_info_exporter_export_type']) ? sanitize_text_field(wp_unslash($_POST['ext_info_exporter_export_type'])) : 'all';
    $export_format = isset($_POST['ext_info_exporter_format']) ? sanitize_text_field(wp_unslash($_POST['ext_info_exporter_format'])) : 'csv';
    $recent_days = isset($_POST['ext_info_exporter_recent_days']) ? intval($_POST['ext_info_exporter_recent_days']) : 0;
    if ($recent_days < 0) {
        $recent_days = 0;
    }

    // Get all installed plugins
    $all_plugins = get_plugins();
    $plugin_updates = get_plugin_updates();

    // Initialize CSV output
    $csv_output = '';

    // Add "Sr. Number" as the first column in the header
    $csv_output .= 'Sr. Number,';
    foreach ($fields as $field) {
        $csv_output .= esc_html(ucfirst($field)) . ',';  // Use the selected fields
    }
    $csv_output = rtrim($csv_output, ',') . "\n"; // Trim the last comma and start a new line

    // Initialize counter for "Sr. Number"
    $sr_number = 1;

    // Helper: filter by export type
    $mu_plugins = array();
    if ($export_type === 'mu') {
        // List MU plugins using WP core API
        if (function_exists('get_mu_plugins')) {
            $mu_plugins = get_mu_plugins();
        } else {
            // Fallback: scan mu-plugins directory
            $mu_dir = WPMU_PLUGIN_DIR;
            if (is_dir($mu_dir)) {
                foreach (glob($mu_dir . '/*.php') as $mu_file) {
                    $plugin_data = get_plugin_data($mu_file, false, false);
                    if (!empty($plugin_data['Name'])) {
                        $rel = basename($mu_file);
                        $mu_plugins[$rel] = array(
                            'Name' => $plugin_data['Name'],
                            'Version' => $plugin_data['Version'],
                            'Author' => $plugin_data['Author'],
                            'AuthorURI' => $plugin_data['AuthorURI'],
                        );
                    }
                }
            }
        }
    }

    // Loop through plugins
    foreach ($all_plugins as $plugin_file => $plugin) {
        // Apply export type filters for normal plugins
        if ($export_type === 'active' && !is_plugin_active($plugin_file)) {
            continue;
        }
        if ($export_type === 'inactive' && is_plugin_active($plugin_file)) {
            continue;
        }
        if ($export_type === 'needs_update' && !isset($plugin_updates[$plugin_file])) {
            continue;
        }
        if ($export_type === 'mu') {
            // Skip normal plugins when exporting only MU plugins
            continue;
        }

        $row = ''; // Initialize row for each plugin

        // Add "Sr. Number" to the row
        $row .= $sr_number++ . ',';

        // Check for updates
        $needs_update = isset($plugin_updates[$plugin_file]) ? 'Yes' : 'No';

        // Initialize readme-derived vars to avoid undefined notices
            $wp_version_required = '';
            $wp_version_tested = '';
            $php_version_required = '';

        // Get the path to the plugin directory and readme.txt file
            $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
            $readme_file = $plugin_dir . '/readme.txt';

            if (file_exists($readme_file)) {
                $readme_content = file_get_contents($readme_file);

                // Get "Requires at least" WordPress version
                if (preg_match('/Requires at least:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $wp_version_required = $matches[1];
                }

                // Get "Tested up to" WordPress version
                if (preg_match('/Tested up to:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $wp_version_tested = $matches[1];
                }

                // Get "Requires PHP" version
                if (preg_match('/Requires PHP:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $php_version_required = $matches[1];
                }   
            }

        // Get name and clean up the author names
        $name = ''; 
        if (isset($plugin['Name'])) {
            $name = $plugin['Name'];
            $name = preg_replace('/\s*,\s*/', ' / ', $name); 
            $name = trim($name); 
        }

        // Get authors and clean up the author names
        $authors = ''; 
        if (isset($plugin['Author'])) {
            $raw_authors = $plugin['Author'];
            $raw_authors = preg_replace('/\s*,\s*/', ' / ', $raw_authors); 
            $authors = trim($raw_authors); 
        }

        // Check if the plugin is active or inactive
        $is_active = is_plugin_active($plugin_file) ? 'Active' : 'Inactive';

        // Add plugin details based on selected fields (always start with "name")
        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $row .= (!empty($name) ? esc_html($name) : '-') . ','; 
                    break;
                case 'version':
                    $row .= (!empty($plugin['Version']) ? esc_html($plugin['Version']) : '-') . ','; 
                    break;
                case 'latest_version':
                    $row .= isset($plugin_updates[$plugin_file]) ? esc_html($plugin_updates[$plugin_file]->update->new_version) . ',' : '-,'; 
                    break;
                case 'slug':
                    $row .= esc_html(dirname($plugin_file)) . ','; 
                    break;
                case 'author':
                    $row .= (!empty($authors) ? esc_html($authors) : '-') . ','; 
                    break;
                case 'author_url':
                    $row .= (!empty($plugin['AuthorURI']) ? esc_html($plugin['AuthorURI']) : '-') . ','; 
                    break;
                case 'needs_update':
                    $row .= esc_html($needs_update) . ',';
                    break;
                case 'status':
                    $row .= (!empty($is_active) ? esc_html($is_active) : '-') . ','; 
                    break;
                case 'requires_wp_version':
                    $row .= esc_html($wp_version_required) . ',';
                    break;
                case 'compatible_up_to':
                    $row .= esc_html($wp_version_tested) . ',';
                    break;
                case 'requires_php_version':
                    $row .= esc_html($php_version_required) . ',';
                    break;
            }
        }

        // Trim the trailing comma and add a new line for the current plugin row
        $csv_output .= rtrim($row, ',') . "\n";
    }

    // Handle MU plugins export if requested
    if ($export_type === 'mu') {
        foreach ($mu_plugins as $plugin_file => $plugin) {
            $row = '';
            $row .= $sr_number++ . ',';

            $name = isset($plugin['Name']) ? $plugin['Name'] : '';
            $authors = isset($plugin['Author']) ? $plugin['Author'] : '';

            // MU plugins are always active
            $is_active = 'Active';

            // MU plugins do not participate in updates via get_plugin_updates
            $needs_update = 'No';

            $wp_version_required = '';
            $wp_version_tested = '';
            $php_version_required = '';

            foreach ($fields as $field) {
                switch ($field) {
                    case 'name':
                        $row .= (!empty($name) ? esc_html($name) : '-') . ',';
                        break;
                    case 'version':
                        $row .= (!empty($plugin['Version']) ? esc_html($plugin['Version']) : '-') . ',';
                        break;
                    case 'latest_version':
                        $row .= '-,';
                        break;
                    case 'slug':
                        $row .= esc_html($plugin_file) . ',';
                        break;
                    case 'author':
                        $row .= (!empty($authors) ? esc_html($authors) : '-') . ',';
                        break;
                    case 'author_url':
                        $row .= (!empty($plugin['AuthorURI']) ? esc_html($plugin['AuthorURI']) : '-') . ',';
                        break;
                    case 'needs_update':
                        $row .= esc_html($needs_update) . ',';
                        break;
                    case 'status':
                        $row .= esc_html($is_active) . ',';
                        break;
                    case 'requires_wp_version':
                        $row .= esc_html($wp_version_required) . ',';
                        break;
                    case 'compatible_up_to':
                        $row .= esc_html($wp_version_tested) . ',';
                        break;
                    case 'requires_php_version':
                        $row .= esc_html($php_version_required) . ',';
                        break;
                }
            }
            $csv_output .= rtrim($row, ',') . "\n";
        }
    }

    // If exporting recently updated plugins, filter rows by last modified time
    if ($export_type === 'recent' && $recent_days > 0) {
        $filtered_output = '';
        $lines = explode("\n", trim($csv_output));
        if (!empty($lines)) {
            $header = array_shift($lines);
            $filtered_output .= $header . "\n";
            $cutoff = time() - ($recent_days * DAY_IN_SECONDS);

            $sr_number = 1;
            foreach ($all_plugins as $plugin_file => $plugin) {
                $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
                $plugin_main_file = WP_PLUGIN_DIR . '/' . $plugin_file;
                $mtime = 0;
                if (file_exists($plugin_main_file)) {
                    $mtime = max($mtime, filemtime($plugin_main_file));
                }
                if (is_dir($plugin_dir)) {
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plugin_dir, FilesystemIterator::SKIP_DOTS)) as $file) {
                        $mtime = max($mtime, @filemtime($file));
                    }
                }
                if ($mtime >= $cutoff) {
                    // Reconstruct row for this plugin with the same logic as above
                    $row = '';
                    $row .= $sr_number++ . ',';

                    $plugin_updates = get_plugin_updates();
                    $needs_update = isset($plugin_updates[$plugin_file]) ? 'Yes' : 'No';

                    $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
                    $readme_file = $plugin_dir . '/readme.txt';
                    $wp_version_required = '';
                    $wp_version_tested = '';
                    $php_version_required = '';
                    if (file_exists($readme_file)) {
                        $readme_content = file_get_contents($readme_file);
                        if (preg_match('/Requires at least:\s*([\d.]+)/i', $readme_content, $matches)) {
                            $wp_version_required = $matches[1];
                        }
                        if (preg_match('/Tested up to:\s*([\d.]+)/i', $readme_content, $matches)) {
                            $wp_version_tested = $matches[1];
                        }
                        if (preg_match('/Requires PHP:\s*([\d.]+)/i', $readme_content, $matches)) {
                            $php_version_required = $matches[1];
                        }
                    }

                    $name = isset($plugin['Name']) ? preg_replace('/\s*,\s*/', ' / ', $plugin['Name']) : '';
                    $name = trim($name);
                    $authors = isset($plugin['Author']) ? preg_replace('/\s*,\s*/', ' / ', $plugin['Author']) : '';
                    $authors = trim($authors);
                    $is_active = is_plugin_active($plugin_file) ? 'Active' : 'Inactive';

                    foreach ($fields as $field) {
                        switch ($field) {
                            case 'name':
                                $row .= (!empty($name) ? esc_html($name) : '-') . ',';
                                break;
                            case 'version':
                                $row .= (!empty($plugin['Version']) ? esc_html($plugin['Version']) : '-') . ',';
                                break;
                            case 'latest_version':
                                $row .= isset($plugin_updates[$plugin_file]) ? esc_html($plugin_updates[$plugin_file]->update->new_version) . ',' : '-,';
                                break;
                            case 'slug':
                                $row .= esc_html(dirname($plugin_file)) . ',';
                                break;
                            case 'author':
                                $row .= (!empty($authors) ? esc_html($authors) : '-') . ',';
                                break;
                            case 'author_url':
                                $row .= (!empty($plugin['AuthorURI']) ? esc_html($plugin['AuthorURI']) : '-') . ',';
                                break;
                            case 'needs_update':
                                $row .= esc_html($needs_update) . ',';
                                break;
                            case 'status':
                                $row .= (!empty($is_active) ? esc_html($is_active) : '-') . ',';
                                break;
                            case 'requires_wp_version':
                                $row .= esc_html($wp_version_required) . ',';
                                break;
                            case 'compatible_up_to':
                                $row .= esc_html($wp_version_tested) . ',';
                                break;
                            case 'requires_php_version':
                                $row .= esc_html($php_version_required) . ',';
                                break;
                        }
                    }
                    $filtered_output .= rtrim($row, ',') . "\n";
                }
            }
        }
        if (!empty($filtered_output)) {
            $csv_output = $filtered_output;
        }
    }

    // Get site URL and current time for filename
    $site_url = wp_parse_url(home_url(), PHP_URL_HOST);
    $current_time = current_time('Y-m-d_H-i-s');

    // Build filename from template
    $template = isset($_POST['ext_info_exporter_filename_template']) ? sanitize_text_field(wp_unslash($_POST['ext_info_exporter_filename_template'])) : '';
    if ($template === '') {
        $template = '{site_name}_{date}';
    }
    $date_str = current_time('Y-m-d');
    $time_str = current_time('H-i-s');
    $export_type_label = $export_type;
    $base_name = $template;
    $base_name = str_replace('{date}', $date_str, $base_name);
    $base_name = str_replace('{time}', $time_str, $base_name);
    $base_name = str_replace('{site_name}', $site_url, $base_name);
    $base_name = str_replace('{export_type}', $export_type_label, $base_name);
    $base_name = str_replace('{format}', $export_format, $base_name);
    $base_name = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $base_name);

    if ($export_format === 'csv') {
        $filename = "{$base_name}.csv";
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        echo esc_html($csv_output);
        exit;
    }

    // Build structured rows for non-CSV formats
    $header_cols = array_merge(array('Sr. Number'), array_map('ucfirst', $fields));
    $data_rows = array();
    $sr_counter = 1;

    // Helper closure to push a row
    $push_row = function($row_values) use (&$data_rows, &$sr_counter) {
        array_unshift($row_values, (string)$sr_counter++);
        $data_rows[] = $row_values;
    };

    // Normal plugins unless MU-only
    if ($export_type !== 'mu') {
        foreach ($all_plugins as $plugin_file => $plugin) {
            if ($export_type === 'active' && !is_plugin_active($plugin_file)) {
                continue;
            }
            if ($export_type === 'inactive' && is_plugin_active($plugin_file)) {
                continue;
            }
            if ($export_type === 'needs_update' && !isset($plugin_updates[$plugin_file])) {
                continue;
            }
            if ($export_type === 'recent' && $recent_days > 0) {
                $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
                $plugin_main_file = WP_PLUGIN_DIR . '/' . $plugin_file;
                $mtime = 0;
                if (file_exists($plugin_main_file)) {
                    $mtime = max($mtime, filemtime($plugin_main_file));
                }
                if (is_dir($plugin_dir)) {
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plugin_dir, FilesystemIterator::SKIP_DOTS)) as $file) {
                        $mtime = max($mtime, @filemtime($file));
                    }
                }
                $cutoff = time() - ($recent_days * DAY_IN_SECONDS);
                if ($mtime < $cutoff) {
                    continue;
                }
            }

            $needs_update = isset($plugin_updates[$plugin_file]) ? 'Yes' : 'No';

            $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
            $readme_file = $plugin_dir . '/readme.txt';
            $wp_version_required = '';
            $wp_version_tested = '';
            $php_version_required = '';
            if (file_exists($readme_file)) {
                $readme_content = file_get_contents($readme_file);
                if (preg_match('/Requires at least:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $wp_version_required = $matches[1];
                }
                if (preg_match('/Tested up to:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $wp_version_tested = $matches[1];
                }
                if (preg_match('/Requires PHP:\s*([\d.]+)/i', $readme_content, $matches)) {
                    $php_version_required = $matches[1];
                }
            }

            $name = '';
            if (isset($plugin['Name'])) {
                $name = preg_replace('/\s*,\s*/', ' / ', $plugin['Name']);
                $name = trim($name);
            }
            $authors = '';
            if (isset($plugin['Author'])) {
                $authors = preg_replace('/\s*,\s*/', ' / ', $plugin['Author']);
                $authors = trim($authors);
            }
            $is_active = is_plugin_active($plugin_file) ? 'Active' : 'Inactive';

            $row_values = array();
            foreach ($fields as $field) {
                switch ($field) {
                    case 'name':
                        $row_values[] = (!empty($name) ? $name : '-');
                        break;
                    case 'version':
                        $row_values[] = (!empty($plugin['Version']) ? $plugin['Version'] : '-');
                        break;
                    case 'latest_version':
                        $row_values[] = isset($plugin_updates[$plugin_file]) ? $plugin_updates[$plugin_file]->update->new_version : '-';
                        break;
                    case 'slug':
                        $row_values[] = dirname($plugin_file);
                        break;
                    case 'author':
                        $row_values[] = (!empty($authors) ? $authors : '-');
                        break;
                    case 'author_url':
                        $row_values[] = (!empty($plugin['AuthorURI']) ? $plugin['AuthorURI'] : '-');
                        break;
                    case 'needs_update':
                        $row_values[] = $needs_update;
                        break;
                    case 'status':
                        $row_values[] = (!empty($is_active) ? $is_active : '-');
                        break;
                    case 'requires_wp_version':
                        $row_values[] = $wp_version_required;
                        break;
                    case 'compatible_up_to':
                        $row_values[] = $wp_version_tested;
                        break;
                    case 'requires_php_version':
                        $row_values[] = $php_version_required;
                        break;
                    default:
                        $row_values[] = '';
                }
            }
            $push_row($row_values);
        }
    }

    // MU plugins
    if ($export_type === 'mu') {
        foreach ($mu_plugins as $plugin_file => $plugin) {
            $name = isset($plugin['Name']) ? $plugin['Name'] : '';
            $authors = isset($plugin['Author']) ? $plugin['Author'] : '';
            $is_active = 'Active';
            $needs_update = 'No';
            $wp_version_required = '';
            $wp_version_tested = '';
            $php_version_required = '';

            $row_values = array();
            foreach ($fields as $field) {
                switch ($field) {
                    case 'name':
                        $row_values[] = (!empty($name) ? $name : '-');
                        break;
                    case 'version':
                        $row_values[] = (!empty($plugin['Version']) ? $plugin['Version'] : '-');
                        break;
                    case 'latest_version':
                        $row_values[] = '-';
                        break;
                    case 'slug':
                        $row_values[] = $plugin_file;
                        break;
                    case 'author':
                        $row_values[] = (!empty($authors) ? $authors : '-');
                        break;
                    case 'author_url':
                        $row_values[] = (!empty($plugin['AuthorURI']) ? $plugin['AuthorURI'] : '-');
                        break;
                    case 'needs_update':
                        $row_values[] = $needs_update;
                        break;
                    case 'status':
                        $row_values[] = $is_active;
                        break;
                    case 'requires_wp_version':
                        $row_values[] = '';
                        break;
                    case 'compatible_up_to':
                        $row_values[] = '';
                        break;
                    case 'requires_php_version':
                        $row_values[] = '';
                        break;
                    default:
                        $row_values[] = '';
                }
            }
            $push_row($row_values);
        }
    }

    // Output non-CSV formats
    switch ($export_format) {
        case 'json':
            $filename = "{$base_name}.json";
            header('Content-Type: application/json');
            header("Content-Disposition: attachment; filename={$filename}");
            // Convert to array of objects keyed by header
            $objects = array();
            foreach ($data_rows as $row) {
                $obj = array();
                foreach ($header_cols as $i => $col) {
                    $key = sanitize_key(str_replace(' ', '_', strtolower($col)));
                    $obj[$key] = isset($row[$i]) ? $row[$i] : '';
                }
                $objects[] = $obj;
            }
            echo wp_json_encode($objects);
            break;
        case 'txt':
            $filename = "{$base_name}.txt";
            header('Content-Type: text/plain');
            header("Content-Disposition: attachment; filename={$filename}");
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- File download output
            echo implode("\t", $header_cols) . "\n";
            foreach ($data_rows as $row) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- File download output
                echo implode("\t", array_map('ext_info_exporter_sanitize_field_value', $row)) . "\n";
            }
            break;
        case 'xml':
            $filename = "{$base_name}.xml";
            header('Content-Type: application/xml');
            header("Content-Disposition: attachment; filename={$filename}");
            $xml = new SimpleXMLElement('<plugins/>' );
            foreach ($data_rows as $row) {
                $item = $xml->addChild('plugin');
                foreach ($header_cols as $i => $col) {
                    $key = sanitize_key(str_replace(' ', '_', strtolower($col)));
                    $item->addChild($key, htmlspecialchars((string)(isset($row[$i]) ? $row[$i] : '')));
                }
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- File download output
            echo $xml->asXML();
            break;
        default:
            // Fallback to CSV if unknown
            $filename = "{$base_name}.csv";
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            echo esc_html($csv_output);
            break;
    }
    exit;
}


// Sanitize field value to remove unwanted HTML entities and quotes
function ext_info_exporter_sanitize_field_value($value)
{
    // Decode any HTML entities (like &quot;)
    $value = html_entity_decode($value, ENT_QUOTES);

    // Remove leading and trailing quotes
    $value = trim($value, '"');

    // Ensure no extra quotes are present
    return str_replace('"', '', $value);
}
