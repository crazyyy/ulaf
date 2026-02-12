<?php
/**
 * Server Insights functionality for DiveWP
 *
 * This class provides server configuration and status checks.
 *
 * @package     DiveWP
 * @subpackage  Features/Server
 * @author      Oleg Petrov
 * @version     1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Server_Insights_New {
    /**
     * Status constants
     */
    const STATUS_GOOD = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO = 'info';

    /**
     * Content loader instance
     */
    private $content_loader;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->content_loader = new DiveWP_Content_Loader();
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue necessary assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'divewp') === false) {
            return;
        }

        wp_enqueue_style(
            'divewp-server-insights',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Render the server insights interface
     */
    public function render() {
        // Add capability check
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        ?>
        <h3><?php esc_html_e('Server Configuration & Status', 'divewp-boost-site-performance'); ?></h3>
        
        <div class="recommendations-grid">
            <?php 
            $checks = array(
                'php-version',
                'database-version',
                'memory-limit',
                'max-execution-time',
                'post-max-size',
                'upload-max-size',
                'max-input-vars',
                'external-connections',
                'php-extensions'
            );

            foreach ($checks as $check) {
                $method = "render_{$check}_check";
                $method = str_replace('-', '_', $method);
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
            ?>
        </div>

        <div class="divewp-notice divewp-notice-warning">
            <p><strong><?php esc_html_e('Important:', 'divewp-boost-site-performance'); ?></strong> 
               <?php esc_html_e('Some server configurations may require hosting provider assistance to modify. Always backup before making server changes.', 'divewp-boost-site-performance'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Log debug messages in a controlled manner
     *
     * Note: This is a development-only feature that is disabled in production.
     * Logging only occurs when both WP_DEBUG and DIVEWP_DEBUG_LOG are enabled.
     *
     * @since 1.0.4
     * @param string $message The message to log
     * @param string $context Additional context information
     * @return void
     */
    private function log_debug($message, $context = '') {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $log_data = array(
            'message' => sanitize_text_field($message),
            'context' => sanitize_text_field($context),
            'file' => __FILE__,
            'line' => __LINE__,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
            if (function_exists('wp_debug_log')) {
                wp_debug_log(sprintf(
                    '[DiveWP Server Insights] %s - Context: %s - File: %s - Line: %d',
                    $log_data['message'],
                    $log_data['context'],
                    $log_data['file'],
                    $log_data['line']
                ));
            }
        }
    }

    /**
     * Generic method to render a check card
     */
    private function render_check($check_type, $check_result) {
        $content = $this->content_loader->get_content('server-insights-new', $check_type);

        if (!$content) {
            $this->log_debug(
                sprintf(
                    'Content not found for check type: %s',
                    sanitize_text_field($check_type)
                ),
                'render_check'
            );
            return;
        }

        try {
            $message_type = ($check_result['status'] === self::STATUS_GOOD) ? 'success' : 'error';
            $messages = $content['messages'][$message_type];

            // Safely escape output while preserving actual values
            /* translators: Text may contain placeholders {current_value} and {recommended_value} which will be replaced */
            $details = isset($messages['details']) ? esc_html($messages['details']) : '';
            if (isset($check_result['current_value'])) {
                $details = str_replace(
                    '{current_value}',
                    esc_html($check_result['current_value']),
                    $details
                );
            }
            if (isset($check_result['recommended_value'])) {
                $details = str_replace(
                    '{recommended_value}',
                    esc_html($check_result['recommended_value']),
                    $details
                );
            }

            // Process steps array with translations
            $steps = isset($messages['steps']) ? array_map(function($step) {
                return esc_html($step);
            }, $messages['steps']) : array();

            // Prepare learn more content with translations
            $learn_more = array(
                'description' => isset($content['learn_more']['description']) ? esc_html($content['learn_more']['description']) : '',
                'benefits_title' => sprintf(
                    /* translators: %s: check type name */
                    esc_html__('Benefits of optimal %s:', 'divewp-boost-site-performance'), 
                    str_replace('-', ' ', $check_type)
                ),
                'benefits' => isset($content['learn_more']['benefits']) ? array_map(function($benefit) {
                    return esc_html($benefit);
                }, $content['learn_more']['benefits']) : array()
            );

            // Add recommendations if needed
            if ($check_result['status'] !== self::STATUS_GOOD) {
                $learn_more['recommendations_title'] = esc_html__('Recommendations:', 'divewp-boost-site-performance');
                $learn_more['recommendations'] = isset($content['learn_more']['recommendations']) ? 
                    array_map(function($recommendation) {
                        return esc_html($recommendation);
                    }, $content['learn_more']['recommendations']) : array();
            }

            // Render the card with translated content
            $this->render_card(array(
                'title' => isset($messages['title']) ? esc_html($messages['title']) : '',
                'icon' => $this->get_icon($check_type),
                'details' => $details,
                'steps' => $steps,
                'status' => $check_result['status'],
                'status_text' => $this->get_status_text($check_result['status']),
                'learn_more' => $learn_more
            ));
        } catch (Exception $e) {
            $this->log_debug(
                sprintf(
                    'Error rendering %s check: %s',
                    sanitize_text_field($check_type),
                    esc_html($e->getMessage())
                ),
                'render_check'
            );
        }
    }

    /**
     * Helper method to render a recommendation card
     */
    private function render_card($args) {
        // Default values
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => self::STATUS_INFO,
            'status_text' => __('Information', 'divewp-boost-site-performance'),
            'learn_more' => array()
        );

        // Merge with defaults
        $args = wp_parse_args($args, $defaults);

        // Extract variables for template
        extract($args);

        // Include the template
        include DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
    }

    /**
     * Render PHP version check card
     */
    private function render_php_version_check() {
        $this->render_check('php-version', $this->check_php_version());
    }

    /**
     * Check PHP version status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of PHP version
     */
    public function check_php_version() {
        $version = PHP_VERSION;
        $optimal_version = '8.2';
        $minimum_version = '7.4';

        $status = self::STATUS_GOOD;
        if (version_compare($version, $optimal_version, '<')) {
            $status = self::STATUS_WARNING;
            if (version_compare($version, $minimum_version, '<')) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $version,
            'recommended_value' => $optimal_version,
            'minimum_value' => $minimum_version
        );
    }

    /**
     * Render database version check card
     */
    private function render_database_version_check() {
        $this->render_check('database-version', $this->check_database_version());
    }

    /**
     * Check database version status
     *
     * Note on Direct Database Query:
     * This method intentionally uses direct database queries without caching because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Database version must be current for accurate monitoring
     * 3. Caching would provide outdated information
     * 4. This is a one-time check per page load
     * 5. Performance impact is minimal as this is only used in admin dashboard
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of database version
     */
    public function check_database_version() {
        global $wpdb;
        
        $version = $wpdb->db_version();
        
        // Direct query required for real-time server version info
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only system diagnostic requiring real-time server data
        $server_info = $wpdb->get_var("SELECT VERSION()");
        
        // Extract version number from server info
        preg_match('/(\d+\.\d+\.\d+)/', $server_info, $matches);
        $clean_version = isset($matches[1]) ? $matches[1] : $version;
        
        // Check if it's MariaDB
        $is_mariadb = stripos($server_info, 'mariadb') !== false;
        $db_type = $is_mariadb ? 'MariaDB' : 'MySQL';
        
        if ($is_mariadb) {
            $optimal_version = '10.6.0';
            $recommended_version = '10.3.0';
            $minimum_version = '10.2.0';
        } else {
            $optimal_version = '8.0.0';
            $recommended_version = '5.7.0';
            $minimum_version = '5.6.0';
        }
        
        $status = self::STATUS_GOOD;
        if (version_compare($clean_version, $optimal_version, '<')) {
            $status = self::STATUS_WARNING;
            if (version_compare($clean_version, $recommended_version, '<')) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $db_type . ' ' . $clean_version,
            'recommended_value' => $is_mariadb ? 'MariaDB 10.6+' : 'MySQL 8.0+',
            'minimum_value' => $is_mariadb ? 'MariaDB 10.2+' : 'MySQL 5.6+'
        );
    }

    /**
     * Render memory limit check card
     */
    private function render_memory_limit_check() {
        $this->render_check('memory-limit', $this->check_memory_limit());
    }

    /**
     * Check memory limit status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of memory limit
     */
    public function check_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
        
        $recommended_bytes = 256 * 1024 * 1024; // 256M
        $minimum_bytes = 128 * 1024 * 1024; // 128M
        
        $status = self::STATUS_GOOD;
        if ($memory_limit_bytes < $recommended_bytes) {
            $status = self::STATUS_WARNING;
            if ($memory_limit_bytes < $minimum_bytes) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $memory_limit,
            'recommended_value' => '256M',
            'minimum_value' => '128M'
        );
    }

    /**
     * Render max execution time check card
     */
    private function render_max_execution_time_check() {
        $this->render_check('max-execution-time', $this->check_max_execution_time());
    }

    /**
     * Check max execution time status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of max execution time
     */
    public function check_max_execution_time() {
        $max_execution_time = ini_get('max_execution_time');
        
        // Convert to integer for comparison
        $max_execution_time = intval($max_execution_time);
        
        $recommended_time = 60; // 60 seconds
        $minimum_time = 30; // 30 seconds
        
        $status = self::STATUS_GOOD;
        if ($max_execution_time < $recommended_time) {
            $status = self::STATUS_WARNING;
            if ($max_execution_time < $minimum_time) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $max_execution_time . 's',
            'recommended_value' => $recommended_time . 's',
            'minimum_value' => $minimum_time . 's'
        );
    }

    /**
     * Render post max size check card
     */
    private function render_post_max_size_check() {
        $this->render_check('post-max-size', $this->check_post_max_size());
    }

    /**
     * Check post max size status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of post max size
     */
    public function check_post_max_size() {
        $post_max_size = ini_get('post_max_size');
        $post_max_size_bytes = wp_convert_hr_to_bytes($post_max_size);
        
        $recommended_bytes = 64 * 1024 * 1024; // 64M
        $minimum_bytes = 32 * 1024 * 1024; // 32M
        
        $status = self::STATUS_GOOD;
        if ($post_max_size_bytes < $recommended_bytes) {
            $status = self::STATUS_WARNING;
            if ($post_max_size_bytes < $minimum_bytes) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $post_max_size,
            'recommended_value' => '64M',
            'minimum_value' => '32M'
        );
    }

    /**
     * Render upload max size check card
     */
    private function render_upload_max_size_check() {
        $this->render_check('upload-max-size', $this->check_upload_max_size());
    }

    /**
     * Check upload max size status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of upload max size
     */
    public function check_upload_max_size() {
        $upload_max_size = ini_get('upload_max_filesize');
        $upload_max_size_bytes = wp_convert_hr_to_bytes($upload_max_size);
        
        $recommended_bytes = 32 * 1024 * 1024; // 32M
        $minimum_bytes = 16 * 1024 * 1024; // 16M
        
        $status = self::STATUS_GOOD;
        if ($upload_max_size_bytes < $recommended_bytes) {
            $status = self::STATUS_WARNING;
            if ($upload_max_size_bytes < $minimum_bytes) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            'current_value' => $upload_max_size,
            'recommended_value' => '32M',
            'minimum_value' => '16M'
        );
    }

    /**
     * Render max input vars check card
     */
    private function render_max_input_vars_check() {
        $this->render_check('max-input-vars', $this->check_max_input_vars());
    }

    /**
     * Check max input vars status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of max input vars
     */
    public function check_max_input_vars() {
        $current_value = ini_get('max_input_vars');
        $recommended_value = 3000;
        $minimum_value = 1000;

        $status = self::STATUS_GOOD;
        if ($current_value < $recommended_value) {
            $status = self::STATUS_WARNING;
            if ($current_value < $minimum_value) {
                $status = self::STATUS_CRITICAL;
            }
        }

        return array(
            'status' => $status,
            /* translators: %d: Current maximum number of input variables allowed */
            'value' => sprintf(
                /* translators: %d: Current maximum number of input variables allowed */
                _n(
                    '%d variable allowed',
                    '%d variables allowed',
                    (int)$current_value,
                    'divewp-boost-site-performance'
                ),
                (int)$current_value
            ),
            'current_value' => $current_value,
            'recommended_value' => $recommended_value,
            'minimum_value' => $minimum_value
        );
    }

    /**
     * Render external connections check card
     */
    private function render_external_connections_check() {
        $this->render_check('external-connections', $this->check_external_connections());
    }

    /**
     * Check external connections status using WordPress Site Health approach
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of external connections
     */
    public function check_external_connections() {
        $test_urls = array(
            'wordpress.org',
            'api.wordpress.org',
            'downloads.wordpress.org'
        );

        $failed_connections = array();
        foreach ($test_urls as $url) {
            $response = wp_safe_remote_get("https://{$url}", array(
                'timeout' => 5,
                'sslverify' => true
            ));

            if (is_wp_error($response)) {
                $failed_connections[] = $url;
            }
        }

        $failed_count = count($failed_connections);
        $status = $failed_count === 0 ? self::STATUS_GOOD : self::STATUS_CRITICAL;

        return array(
            'status' => $status,
            /* translators: %d: Number of failed external connections */
            'value' => sprintf(
                /* translators: %d: Number of failed external connections */
                _n(
                    '%d failed connection',
                    '%d failed connections',
                    $failed_count,
                    'divewp-boost-site-performance'
                ),
                $failed_count
            ),
            'failed_connections' => $failed_connections
        );
    }

    /**
     * Render PHP extensions check card
     */
    private function render_php_extensions_check() {
        $this->render_check('php-extensions', $this->check_php_extensions());
    }

    /**
     * Check PHP extensions status
     *
     * @since 1.0.4
     * @since 2.1.0 Made public for Abilities API integration
     *
     * @return array Status and details of PHP extensions
     */
    public function check_php_extensions() {
        $required_extensions = array(
            'curl',
            'dom',
            'gd',
            'json',
            'mbstring',
            'openssl',
            'xml',
            'zip'
        );

        $missing_extensions = array();
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        $missing_count = count($missing_extensions);
        $status = $missing_count === 0 ? self::STATUS_GOOD : self::STATUS_CRITICAL;

        return array(
            'status' => $status,
            /* translators: %d: Number of missing PHP extensions */
            'value' => sprintf(
                /* translators: %d: Number of missing PHP extensions */
                _n(
                    '%d missing extension',
                    '%d missing extensions',
                    $missing_count,
                    'divewp-boost-site-performance'
                ),
                $missing_count
            ),
            'missing_extensions' => $missing_extensions
        );
    }

    /**
     * Get all server insights as structured data for API consumption
     *
     * Aggregates results from all server checks into a single array
     * suitable for the WordPress Abilities API and AI agent integration.
     *
     * @since 2.1.0 Added for Abilities API integration
     *
     * @return array Structured server insights data
     */
    public function get_all_insights() {
        // Run all checks
        $checks = array(
            'php_version'          => $this->check_php_version(),
            'database_version'     => $this->check_database_version(),
            'memory_limit'         => $this->check_memory_limit(),
            'max_execution_time'   => $this->check_max_execution_time(),
            'post_max_size'        => $this->check_post_max_size(),
            'upload_max_size'      => $this->check_upload_max_size(),
            'max_input_vars'       => $this->check_max_input_vars(),
            'external_connections' => $this->check_external_connections(),
            'php_extensions'       => $this->check_php_extensions(),
        );

        // Calculate summary statistics
        $passed   = 0;
        $warnings = 0;
        $critical = 0;
        $recommendations = array();

        foreach ($checks as $check_name => $check_result) {
            switch ($check_result['status']) {
                case self::STATUS_GOOD:
                    $passed++;
                    break;
                case self::STATUS_WARNING:
                    $warnings++;
                    $recommendations[] = $this->get_recommendation_for_check($check_name, $check_result);
                    break;
                case self::STATUS_CRITICAL:
                    $critical++;
                    $recommendations[] = $this->get_recommendation_for_check($check_name, $check_result);
                    break;
            }
        }

        // Determine overall status
        $overall_status = self::STATUS_GOOD;
        if ($critical > 0) {
            $overall_status = self::STATUS_CRITICAL;
        } elseif ($warnings > 0) {
            $overall_status = self::STATUS_WARNING;
        }

        return array(
            'status'    => $overall_status,
            'timestamp' => gmdate('c'),
            'checks'    => $checks,
            'summary'   => array(
                'total_checks'    => count($checks),
                'passed'          => $passed,
                'warnings'        => $warnings,
                'critical'        => $critical,
                'recommendations' => array_filter($recommendations),
            ),
        );
    }

    /**
     * Get recommendation text for a specific check
     *
     * @since 2.1.0
     *
     * @param string $check_name   The name of the check
     * @param array  $check_result The result array from the check
     * @return string Recommendation text
     */
    private function get_recommendation_for_check($check_name, $check_result) {
        $recommendations = array(
            'php_version' => sprintf(
                /* translators: 1: current PHP version, 2: recommended PHP version */
                __('Upgrade PHP from %1$s to %2$s for better performance and security.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '8.2'
            ),
            'database_version' => sprintf(
                /* translators: 1: current database version, 2: recommended database version */
                __('Consider upgrading your database from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : ''
            ),
            'memory_limit' => sprintf(
                /* translators: 1: current memory limit, 2: recommended memory limit */
                __('Increase PHP memory limit from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '256M'
            ),
            'max_execution_time' => sprintf(
                /* translators: 1: current execution time, 2: recommended execution time */
                __('Increase max execution time from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '60s'
            ),
            'post_max_size' => sprintf(
                /* translators: 1: current post max size, 2: recommended post max size */
                __('Increase post_max_size from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '64M'
            ),
            'upload_max_size' => sprintf(
                /* translators: 1: current upload size, 2: recommended upload size */
                __('Increase upload_max_filesize from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '32M'
            ),
            'max_input_vars' => sprintf(
                /* translators: 1: current max input vars, 2: recommended max input vars */
                __('Increase max_input_vars from %1$s to %2$s.', 'divewp-boost-site-performance'),
                isset($check_result['current_value']) ? $check_result['current_value'] : '',
                isset($check_result['recommended_value']) ? $check_result['recommended_value'] : '3000'
            ),
            'external_connections' => __('Check firewall settings - some WordPress.org connections are failing.', 'divewp-boost-site-performance'),
            'php_extensions' => sprintf(
                /* translators: %s: list of missing PHP extensions */
                __('Install missing PHP extensions: %s', 'divewp-boost-site-performance'),
                isset($check_result['missing_extensions']) ? implode(', ', $check_result['missing_extensions']) : ''
            ),
        );

        return isset($recommendations[$check_name]) ? $recommendations[$check_name] : '';
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @param string $type Check type
     * @return string SVG markup
     */
    private function get_icon($type) {
        $icons = array(
            'php-version' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>',
            'database-version' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 5c0 1.1-3.582 2-8 2s-8-.9-8-2 3.582-2 8-2 8 .9 8 2"/>
                            <path d="M3 5v14c0 1.1 3.582 2 8 2s8-.9 8-2V5"/>
                        </svg>',
            'memory-limit' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <path d="M9 4v4"/>
                            <path d="M15 4v4"/>
                            <path d="M9 16v4"/>
                            <path d="M15 16v4"/>
                            <path d="M4 9h4"/>
                            <path d="M16 9h4"/>
                            <path d="M4 15h4"/>
                            <path d="M16 15h4"/>
                        </svg>',
            'max-execution-time' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 3h14"/>
                            <path d="M5 9h14"/>
                            <path d="M5 15h14"/>
                            <path d="M5 21h8"/>
                            <path d="M17 21l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>',
            'post-max-size' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M12 8v8"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8l-4 4"/>
                            <path d="M12 8l4 4"/>
                        </svg>',
            'upload-max-size' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M12 16V8"/>
                            <path d="M8 12h8"/>
                            <path d="M12 16l-4-4"/>
                            <path d="M12 16l4-4"/>
                        </svg>',
            'max-input-vars' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3h18a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                <path d="M8 10h8"/>
                <path d="M8 14h8"/>
                <path d="M8 6h8"/>
                <path d="M8 18h8"/>
            </svg>',
            'external-connections' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>',
            'php-extensions' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 16l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                            <path d="M4 6h16"/>
                            <path d="M4 12h16"/>
                            <path d="M4 18h8"/>
                        </svg>'
        );

        return isset($icons[$type]) ? $icons[$type] : '';
    }

    /**
     * Get status text based on status
     *
     * @param string $status Status constant
     * @return string Status text
     */
    private function get_status_text($status) {
        switch ($status) {
            case self::STATUS_GOOD:
                return __('Optimal', 'divewp-boost-site-performance');
            case self::STATUS_WARNING:
                return __('Could Be Better', 'divewp-boost-site-performance');
            case self::STATUS_CRITICAL:
                return __('Needs Attention', 'divewp-boost-site-performance');
            case self::STATUS_INFO:
                return __('Information', 'divewp-boost-site-performance');
            default:
                return __('Unknown', 'divewp-boost-site-performance');
        }
    }
}
