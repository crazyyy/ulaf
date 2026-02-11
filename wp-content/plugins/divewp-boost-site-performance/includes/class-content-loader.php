<?php
/**
 * Content Loader Class
 *
 * Handles loading content from JSON files for all features.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Content_Loader {
    /**
     * Get content for any feature
     *
     * @param string $feature    Feature name (e.g., 'performance-checks')
     * @param string $check_name Specific check (e.g., 'caching')
     * @param bool   $basic_info Only load basic info (status and title)
     * @return array|false       Content array or false if not found
     */
    public function get_content($feature, $check_name, $basic_info = false) {
        // Load from file
        $file_path = DIVEWP_PLUGIN_DIR . "content/features/{$feature}/{$check_name}.json";
        $data = $this->load_json($file_path);
        
        return $basic_info ? $this->get_basic_info($data) : $data;
    }

    /**
     * Extract basic info from content array
     *
     * @param array $data Full content array
     * @return array Basic info array
     */
    private function get_basic_info($data) {
        if (!is_array($data)) {
            return false;
        }

        // Extract only the essential data needed for status pills
        $basic = array();
        
        // Get messages based on status
        if (isset($data['messages'])) {
            $message_type = isset($data['status']) && $data['status'] === 'success' ? 'success' : 'error';
            if (isset($data['messages'][$message_type])) {
                $messages = $data['messages'][$message_type];
                $basic['title'] = isset($messages['title']) ? $messages['title'] : '';
                $basic['status'] = isset($data['status']) ? $data['status'] : 'info';
            }
        }

        return $basic;
    }

    /**
     * Load and parse JSON file
     *
     * @param string $file_path Full path to JSON file
     * @return array|false     Parsed JSON data or false on error
     */
    private function load_json($file_path) {
        if (!file_exists($file_path)) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log("Content file not found: {$file_path}", 'error');
            }
            return false;
        }

        $json_content = file_get_contents($file_path);
        if ($json_content === false) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log("Unable to read content file: {$file_path}", 'error');
            }
            return false;
        }

        $data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log("Invalid JSON in file: {$file_path}", 'error');
            }
            return false;
        }

        return $data;
    }
}
