<?php
/**
 * Security Insights functionality for DiveWP
 *
 * This class provides security checks and insights for WordPress installations.
 *
 * @package DiveWP
 * @subpackage Security
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Class DiveWP_Security
 *
 * Handles security checks and insights for WordPress installations.
 *
 * @since 1.0.0
 */
class DiveWP_Security {
    /**
     * Status constants for security checks
     */
    const STATUS_GOOD     = 'success';
    const STATUS_WARNING  = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO     = 'info';

    /**
     * Content loader instance
     *
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * WordPress Filesystem instance
     *
     * @var WP_Filesystem_Base
     */
    private $wp_filesystem;

    /**
     * AJAX actions
     *
     * @var array
     */
    private $ajax_actions = array(
        'divewp_security_check',
        'divewp_refresh_security'
    );

    /**
     * Security nonce actions
     *
     * @var array
     */
    private $nonce_actions = array(
        'security_check' => 'divewp_security_check_nonce',
        'security_refresh' => 'divewp_security_refresh_nonce',
        'security_settings' => 'divewp_security_settings_nonce'
    );

    /**
     * Initialize the class
     */
    public function __construct() {
        if (!current_user_can('manage_options')) {
            return;
        }

        require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';
        $this->content_loader = new DiveWP_Content_Loader();
        
        // Initialize AJAX handlers
        $this->init();
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_footer', array($this, 'add_nonce_fields'));
        
        // Initialize filesystem with error handling
        $this->init_filesystem();
    }

    /**
     * Add nonce fields to the page
     *
     * @since 1.0.4
     * @return void
     */
    public function add_nonce_fields() {
        // Verify nonce for page access
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'divewp_admin_page')) {
            return;
        }

        // Verify page parameter
        if (!isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'divewp') {
            return;
        }
        
        foreach ($this->nonce_actions as $action => $nonce) {
            wp_nonce_field($nonce, '_wpnonce_' . $action);
        }
    }

    /**
     * Initialize WordPress Filesystem
     */
    private function init_filesystem() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $creds = request_filesystem_credentials('', '', false, false, array());
        if (!WP_Filesystem($creds)) {
            return false;
        }
    }

    /**
     * Enqueue required assets
     *
     * @param string $hook The current admin page
     */
    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'divewp' ) === false ) {
            return;
        }

        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        $version = defined('DIVEWP_VERSION') ? DIVEWP_VERSION : '1.0.0';

        wp_enqueue_style(
            'divewp-security',
            esc_url(DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css'),
            array(),
            esc_attr($version)
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            esc_url(DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js'),
            array('jquery'),
            esc_attr($version),
            true
        );

        wp_localize_script(
            'divewp-recommendations',
            'divewpSecurityAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('divewp_security_nonce'),
                'version' => esc_attr($version)
            )
        );
    }

    /**
     * Render the security insights interface
     */
    public function render() {
        if (!$this->verify_capability('view_security')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }
        ?>
        <h3><?php esc_html_e('Security Insights', 'divewp-boost-site-performance'); ?></h3>
        
        <?php 
        // Add nonce fields for each action
        foreach ($this->nonce_actions as $action => $nonce) {
            wp_nonce_field($nonce, '_wpnonce_' . $action);
        }
        ?>
        
        <div class="recommendations-grid">
            <?php 
            $this->render_security_plugins_check();
            $this->render_ssl_check();
            $this->render_file_permissions_check();
            $this->render_admin_user_check();
            $this->render_db_prefix_check();
            $this->render_file_editor_check();
            $this->render_debug_mode_check();
            ?>
        </div>
        <?php
    }

    /**
     * Generic method to render a check card
     *
     * @param string $check_type Type of check
     * @param array  $check_result Result data from check
     */
    private function render_check( $check_type, $check_result ) {
        try {
            if ( empty( $check_type ) ) {
                throw new Exception( esc_html__('Invalid check type', 'divewp-boost-site-performance') );
            }

            $content = $this->content_loader->get_content( 'security-insights', $check_type );
            if ( empty( $content ) || ! is_array( $content ) ) {
                throw new Exception(sprintf(/* translators: %s: check type for which content was not found */
                    esc_html__('Content not found for check type: %s', 'divewp-boost-site-performance'),
                    sanitize_text_field($check_type)
                ));
            }

            // Validate required content structure
            if ( ! isset( $content['messages'] ) || ! is_array( $content['messages'] ) ) {
                throw new Exception( esc_html__('Missing or invalid messages array', 'divewp-boost-site-performance') );
            }

            // Validate check result
            if ( empty( $check_result ) || ! isset( $check_result['status'] ) ) {
                throw new Exception( esc_html__('Invalid check result', 'divewp-boost-site-performance') );
            }

            // Determine which message template to use based on status
            switch ( $check_result['status'] ) {
                case self::STATUS_GOOD:
                    $message_type = 'success';
                    break;
                case self::STATUS_WARNING:
                    $message_type = 'warning';
                    break;
                case self::STATUS_INFO:
                    $message_type = 'info';
                    break;
                default:
                    $message_type = 'error';
            }

            // Validate message type exists
            if ( ! isset( $content['messages'][$message_type] ) || ! is_array( $content['messages'][$message_type] ) ) {
                throw new Exception(sprintf(/* translators: %s: message type identifier */
                    esc_html__('Invalid message type: %s', 'divewp-boost-site-performance'),
                    sanitize_text_field($message_type)
                ));
            }

            $messages = $content['messages'][$message_type];

            // Get title with translation
            $title = '';
            if (isset($messages['title']) && is_string($messages['title'])) {
                $title = esc_html($messages['title']);
            }

            // Prepare details text with translation
            $details = '';
            if (isset($messages['details']) && is_string($messages['details'])) {
                $details = esc_html($messages['details']);
                foreach ($check_result as $key => $value) {
                    if (is_string($value) || is_numeric($value)) {
                        $details = str_replace('{' . $key . '}', esc_html($value), $details);
                    }
                }
            }

            // Prepare steps with translations
            $steps = array();
            if (!empty($messages['steps']) && is_array($messages['steps'])) {
                foreach ($messages['steps'] as $step) {
                    if (is_string($step)) {
                        $steps[] = wp_kses_post(esc_html($step));
                    }
                }
            }

            // Prepare learn more content with translations
            $learn_more = array();
            
            // Add description if exists with translation
            if (!empty($content['learn_more']['description']) && is_string($content['learn_more']['description'])) {
                $learn_more['description'] = wp_kses_post(esc_html($content['learn_more']['description']));
            }

            // Add benefits title with translation
            $learn_more['benefits_title'] = esc_html__('Benefits:', 'divewp-boost-site-performance');

            // Add benefits with translations
            $learn_more['benefits'] = array();
            if (!empty($content['learn_more']['benefits']) && is_array($content['learn_more']['benefits'])) {
                foreach ($content['learn_more']['benefits'] as $benefit) {
                    if (is_string($benefit)) {
                        $learn_more['benefits'][] = wp_kses_post(esc_html($benefit));
                    }
                }
            }

            // Add recommended tools if they exist with translations
            if (!empty($content['learn_more']['recommended_tools']) && is_array($content['learn_more']['recommended_tools'])) {
                $learn_more['tools_title'] = esc_html__('Recommended Tools:', 'divewp-boost-site-performance');
                $learn_more['tools'] = array();
                foreach ($content['learn_more']['recommended_tools'] as $tool) {
                    if (isset($tool['name']) && isset($tool['description'])) {
                        $learn_more['tools'][] = array(
                            'name' => wp_kses_post(esc_html($tool['name'])),
                            'type' => wp_kses_post(esc_html($tool['description']))
                        );
                    }
                }
            }

            // Render the card with translated content
            $this->render_card(array(
                'title' => $title,
                'icon' => $this->get_icon($check_type),
                'details' => $details,
                'steps' => $steps,
                'status' => $check_result['status'],
                'status_text' => $this->get_status_text($check_result['status']),
                'learn_more' => $learn_more
            ));

        } catch (Exception $e) {
            // Use our controlled logging method instead of direct error_log
            $this->log_error(sprintf(/* translators: %1$s: check type, %2$s: error message */
                esc_html__('Error rendering security check %1$s: %2$s', 'divewp-boost-site-performance'),
                sanitize_text_field($check_type),
                $e->getMessage()
            ), 'render_check', array(
                'check_type' => $check_type,
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Check SSL status
     *
     * @return array Status and details of SSL
     */
    private function check_ssl() {
        $is_ssl = is_ssl();
        return array(
            'status' => $is_ssl ? self::STATUS_GOOD : self::STATUS_CRITICAL,
            'site_url' => get_site_url()
        );
    }

    /**
     * Check file permissions using WordPress native functions
     *
     * @return array Status and details of file permissions
     */
    private function check_file_permissions() {
        if (!current_user_can('manage_options')) {
            return array(
                'status' => self::STATUS_WARNING,
                'details' => esc_html__('You need administrator access to check file permissions', 'divewp-boost-site-performance')
            );
        }

        // Get upload directory info safely
        $upload_dir = wp_upload_dir();
        $upload_basedir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';

        $files_to_check = array(
            array(
                'path' => ABSPATH,
                'name' => esc_html__('wp-admin, wp-includes folders', 'divewp-boost-site-performance'),
                'recommended' => '0755'
            ),
            array(
                'path' => WP_CONTENT_DIR,
                'name' => esc_html__('wp-content folder', 'divewp-boost-site-performance'),
                'recommended' => '0755'
            ),
            array(
                'path' => get_theme_root(),
                'name' => esc_html__('themes folder', 'divewp-boost-site-performance'),
                'recommended' => '0755'
            ),
            array(
                'path' => WP_PLUGIN_DIR,
                'name' => esc_html__('plugins folder', 'divewp-boost-site-performance'),
                'recommended' => '0755'
            ),
            array(
                'path' => $upload_basedir,
                'name' => esc_html__('uploads folder', 'divewp-boost-site-performance'),
                'recommended' => '0755'
            ),
            array(
                'path' => ABSPATH . 'wp-config.php',
                'name' => esc_html__('wp-config.php file', 'divewp-boost-site-performance'),
                'recommended' => '0600'
            )
        );

        $issues = array();
        $issue_count = 0;
        
        foreach ($files_to_check as $file) {
            if (!file_exists($file['path'])) {
                continue;
            }

            $current_perms = substr(sprintf('%o', fileperms($file['path'])), -4);
            if (intval($current_perms, 8) > intval($file['recommended'], 8)) {
                $issue_count++;
                /* translators: %1$s: File name, %2$s: Current permissions, %3$s: Recommended permissions */
                $issues[] = sprintf(esc_html__('%1$s (Current: %2$s, Should be: %3$s)', 'divewp-boost-site-performance'),
                    esc_html($file['name']),
                    esc_html($current_perms),
                    esc_html($file['recommended'])
                );
            }
        }

        // Check .htaccess if it exists
        $htaccess_path = ABSPATH . '.htaccess';
        if (file_exists($htaccess_path)) {
            $htaccess_perms = substr(sprintf('%o', fileperms($htaccess_path)), -4);
            if (intval($htaccess_perms, 8) > intval('0644', 8)) {
                $issue_count++;
                /* translators: %1$s: Current permissions, %2$s: Recommended permissions */
                $issues[] = sprintf(esc_html__('.htaccess file (Current: %1$s, Should be: %2$s)', 'divewp-boost-site-performance'),
                    esc_html($htaccess_perms),
                    '0644'
                );
            }
        }

        if (empty($issues)) {
            return array(
                'status' => self::STATUS_GOOD,
                'details' => esc_html__('All file permissions are secure.', 'divewp-boost-site-performance')
            );
        }

        /* translators: %d: Number of files with incorrect permissions */
        $details = sprintf( esc_html(_n(
            'Found %d file with incorrect permissions:',
            'Found %d files with incorrect permissions:',
            $issue_count,
            'divewp-boost-site-performance'
        )), $issue_count ) . "\n" . implode("\n", $issues);

        return array(
            'status' => self::STATUS_CRITICAL,
            'details' => wp_kses_post($details)
        );
    }

    /**
     * Check if default admin username exists
     *
     * @return array Status and details of admin username check
     */
    private function check_admin_user() {
        $user = get_user_by( 'login', 'admin' );
        return array(
            'status' => $user ? self::STATUS_CRITICAL : self::STATUS_GOOD
        );
    }

    /**
     * Check database prefix
     *
     * @return array Status and details of database prefix check
     */
    private function check_db_prefix() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        return array(
            'status' => $prefix === 'wp_' ? self::STATUS_CRITICAL : self::STATUS_GOOD,
            'prefix' => $prefix
        );
    }

    /**
     * Check if file editor is enabled
     *
     * @return array Status and details of file editor check
     */
    private function check_file_editor() {
        return array(
            'status' => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ? 
                self::STATUS_GOOD : 
                self::STATUS_CRITICAL
        );
    }

    /**
     * Check if debug mode is enabled
     *
     * @return array Status and details of debug mode check
     */
    private function check_debug_mode() {
        return array(
            'status' => defined( 'WP_DEBUG' ) && WP_DEBUG ? 
                self::STATUS_CRITICAL : 
                self::STATUS_GOOD
        );
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @param string $type Check type
     * @return string SVG markup
     */
    private function get_icon( $type ) {
        $icons = array(
            'ssl' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>',
            'file-permissions' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <path d="M12 10v4"/>
                        <circle cx="12" cy="8" r="1"/>
                    </svg>',
            'admin-user' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a5 5 0 015 5v2a5 5 0 01-10 0V7a5 5 0 015-5z"/>
                        <path d="M20 21v-2a5 5 0 00-5-5H9a5 5 0 00-5 5v2"/>
                    </svg>',
            'db-prefix' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 5c0 1.1-3.582 2-8 2s-8-.9-8-2 3.582-2 8-2 8 .9 8 2"/>
                        <path d="M3 5v14c0 1.1 3.582 2 8 2s8-.9 8-2V5"/>
                    </svg>',
            'file-editor' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>',
            'debug-mode' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                        <path d="M12 16v.01"/>
                        <path d="M12 8v4"/>
                    </svg>',
            'security-plugins' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>'
        );

        return isset( $icons[$type] ) ? $icons[$type] : '';
    }

    /**
     * Get status text based on status
     *
     * @param string $status Status constant
     * @return string Status text
     */
    private function get_status_text( $status ) {
        $status_texts = array(
            self::STATUS_GOOD     => __( 'Secure', 'divewp-boost-site-performance' ),
            self::STATUS_WARNING  => __( 'Warning', 'divewp-boost-site-performance' ),
            self::STATUS_CRITICAL => __( 'Critical', 'divewp-boost-site-performance' ),
            self::STATUS_INFO     => __( 'Information', 'divewp-boost-site-performance' ),
        );

        return isset( $status_texts[$status] ) ? $status_texts[$status] : __( 'Unknown', 'divewp-boost-site-performance' );
    }

    /**
     * Render SSL check card
     */
    private function render_ssl_check() {
        $this->render_check( 'ssl', $this->check_ssl() );
    }

    /**
     * Render file permissions check card
     */
    private function render_file_permissions_check() {
        $this->render_check( 'file-permissions', $this->check_file_permissions() );
    }

    /**
     * Render admin user check card
     */
    private function render_admin_user_check() {
        $this->render_check( 'admin-user', $this->check_admin_user() );
    }

    /**
     * Render database prefix check card
     */
    private function render_db_prefix_check() {
        $this->render_check( 'db-prefix', $this->check_db_prefix() );
    }

    /**
     * Render file editor check card
     */
    private function render_file_editor_check() {
        $this->render_check( 'file-editor', $this->check_file_editor() );
    }

    /**
     * Render debug mode check card
     */
    private function render_debug_mode_check() {
        $this->render_check( 'debug-mode', $this->check_debug_mode() );
    }

    /**
     * Check WordPress security protection plugins status
     *
     * @return array Status and details of security protection
     */
    private function check_security_plugins() {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Get all active plugins
        $all_active_plugins = get_option('active_plugins', array());
        
        // Load recommended plugins from content
        $content = $this->content_loader->get_content('security-insights', 'security-plugins');
        $active_plugins = array();

        // Define exact matches for known security plugins
        $security_plugin_patterns = array(
            'all-in-one-wp-security' => 'All In One WP Security',
            'sucuri-scanner' => 'Sucuri Security',
            'wordfence' => 'Wordfence Security',
            'better-wp-security' => 'iThemes Security',
            'malcare-security' => 'MalCare Security',
            'bulletproof-security' => 'BulletProof Security',
            'wp-cerber' => 'Cerber Security',
            'wp-simple-firewall' => 'Shield Security',
            'secupress' => 'SecuPress Security',
            'wp-hide-security-enhancer' => 'WP Hide & Security Enhancer'
        );
        
        if (isset($content['learn_more']['recommended_tools'])) {
            foreach ($all_active_plugins as $plugin) {
                if (!is_plugin_active($plugin)) {
                    continue;
                }

                $plugin_dir = (strpos($plugin, '/') !== false) ? dirname($plugin) : $plugin;
                
                // Check against our known security plugin patterns
                foreach ($security_plugin_patterns as $pattern => $name) {
                    if (strpos($plugin_dir, $pattern) !== false) {
                        $active_plugins[] = $name;
                        break;
                    }
                }
            }
        }

        $plugin_count = count($active_plugins);

        if ($plugin_count === 0) {
            return array(
                'status' => self::STATUS_INFO,
                'value' => __('No security protection installed', 'divewp-boost-site-performance')
            );
        }

        if ($plugin_count > 1) {
            return array(
                'status' => self::STATUS_WARNING,
                'value' => implode(', ', $active_plugins)
            );
        }

        return array(
            'status' => self::STATUS_GOOD,
            'value' => reset($active_plugins)
        );
    }

    /**
     * Render WordPress security protection check
     */
    private function render_security_plugins_check() {
        $this->render_check('security-plugins', $this->check_security_plugins());
    }

    /**
     * Initialize AJAX handlers
     */
    public function init() {
        foreach ($this->ajax_actions as $action) {
            add_action('wp_ajax_' . $action, array($this, 'handle_' . $action));
        }
    }

    /**
     * Verify AJAX request
     *
     * @param string $action The nonce action to verify
     * @return bool Whether the request is valid
     */
    private function verify_ajax_request($action) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $nonce_key = '_wpnonce_' . $action;
        if (!isset($_REQUEST[$nonce_key])) {
            return false;
        }

        return check_ajax_referer($this->nonce_actions[$action], $nonce_key, false);
    }

    /**
     * Log error with proper escaping
     */
    private function log_error($message, $context = '', $extra = array()) {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG && current_user_can('manage_options')) {
            $log_message = sprintf(
                            /* translators: 1: Error message 2: Context 3: File 4: Line */

                esc_html__('DiveWP Security: %1$s - Context: %2$s - File: %3$s - Line: %4$d', 'divewp-boost-site-performance'),
                esc_html($message),
                esc_html($context),
                esc_html(__FILE__),
                __LINE__
            );
            
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
                if (function_exists('wp_debug_log')) {
                    wp_debug_log($log_message);
                }
            }
        }
    }

    /**
     * Helper method to render a recommendation card
     *
     * @param array $args {
     *     Arguments for rendering the card.
     *     @type string $title          Card title
     *     @type string $icon           SVG icon markup
     *     @type string $details        Main description text
     *     @type array  $steps          Array of steps to display
     *     @type string $status         Status class (success, warning, danger, info)
     *     @type string $status_text    Status text to display
     *     @type array  $learn_more     Learn more content array
     * }
     * @return void
     */
    private function render_card($args) {
        // Default values
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => self::STATUS_INFO,
            'status_text' => esc_html__('Information', 'divewp-boost-site-performance'),
            'learn_more' => array()
        );

        // Merge with defaults
        $args = wp_parse_args($args, $defaults);

        // Sanitize SVG icon
        $allowed_svg = array(
            'svg' => array(
                'class' => true,
                'width' => true,
                'height' => true,
                'viewBox' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true
            ),
            'path' => array(
                'd' => true,
                'stroke' => true,
                'stroke-width' => true
            ),
            'rect' => array(
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true
            ),
            'circle' => array(
                'cx' => true,
                'cy' => true,
                'r' => true
            ),
            'polyline' => array(
                'points' => true
            ),
            'line' => array(
                'x1' => true,
                'y1' => true,
                'x2' => true,
                'y2' => true
            ),
            'polygon' => array(
                'points' => true
            )
        );

        // Extract and sanitize individual items
        $title = esc_html($args['title']);
        $icon = wp_kses($args['icon'], $allowed_svg);
        $details = wp_kses_post($args['details']);
        $status = sanitize_html_class($args['status']);
        $status_text = esc_html($args['status_text']);

        // Handle arrays separately
        $steps = array();
        if (!empty($args['steps']) && is_array($args['steps'])) {
            foreach ($args['steps'] as $step) {
                if (is_string($step)) {
                    $steps[] = wp_kses_post($step);
                }
            }
        }

        $learn_more = array();
        if (!empty($args['learn_more']) && is_array($args['learn_more'])) {
            foreach ($args['learn_more'] as $key => $value) {
                if (is_string($value)) {
                    $learn_more[$key] = wp_kses_post($value);
                } elseif (is_array($value)) {
                    if ($key === 'tools') {
                        $learn_more[$key] = array();
                        foreach ($value as $tool) {
                            if (isset($tool['name']) && isset($tool['type'])) {
                                $learn_more[$key][] = array(
                                    'name' => wp_kses_post($tool['name']),
                                    'type' => wp_kses_post($tool['type'])
                                );
                            }
                        }
                    } else {
                        $learn_more[$key] = array_map('wp_kses_post', array_filter($value, 'is_string'));
                    }
                }
            }
        }

        // Include the template
        include DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
    }

    /**
     * Verify nonce for specific security action
     *
     * @param string $action The nonce action to verify
     * @return bool Whether the nonce is valid
     */
    private function verify_nonce($action) {
        if (!isset($this->nonce_actions[$action])) {
            return false;
        }

        if (!isset($_REQUEST['_wpnonce'])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
        return wp_verify_nonce($nonce, $this->nonce_actions[$action]);
    }

    /**
     * Enhanced capability check for specific operations
     *
     * @param string $operation The operation to check
     * @return bool Whether user has capability
     */
    private function verify_capability($operation) {
        switch ($operation) {
            case 'view_security':
                return current_user_can('manage_options');
            case 'edit_security':
                return current_user_can('manage_options') && current_user_can('edit_plugins');
            case 'refresh_security':
                return current_user_can('manage_options');
            default:
                return current_user_can('manage_options');
        }
    }

    /**
     * Sanitize and validate database input
     *
     * @param mixed $input The input to sanitize
     * @param string $type The type of input
     * @return mixed Sanitized input
     */
    private function sanitize_db_input($input, $type = 'text') {
        switch ($type) {
            case 'int':
                return intval($input);
            case 'float':
                return floatval($input);
            case 'array':
                return array_map(array($this, 'sanitize_db_input'), (array) $input);
            case 'file_path':
                return sanitize_file_name($input);
            case 'html':
                return wp_kses_post($input);
            case 'sql':
                global $wpdb;
                return $wpdb->prepare('%s', $input);
            default:
                return sanitize_text_field($input);
        }
    }

    /**
     * Handle security check AJAX request
     */
    public function handle_divewp_security_check() {
        try {
            if (!check_ajax_referer('divewp_security_nonce', 'nonce', false)) {
                throw new Exception(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            }

            if (!isset($_POST['check_type'])) {
                throw new Exception(esc_html__('Missing check type', 'divewp-boost-site-performance'));
            }

            $check_type = sanitize_text_field(wp_unslash($_POST['check_type']));

            if (empty($check_type)) {
                throw new Exception(esc_html__('Missing check type', 'divewp-boost-site-performance'));
            }

            $result = $this->perform_security_check($check_type);

            wp_send_json_success($result);

        } catch (Exception $e) {
            $this->log_error($e->getMessage(), 'Security Check Handler');
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    /**
     * Handle security refresh AJAX request
     */
    public function handle_divewp_refresh_security() {
        try {
            if (!$this->verify_ajax_request('security_refresh')) {
                throw new Exception(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            }

            $results = array();
            $checks = array(
                'ssl',
                'file_permissions',
                'admin_user',
                'db_prefix',
                'file_editor',
                'debug_mode',
                'security_plugins'
            );

            foreach ($checks as $check) {
                $method = 'check_' . $check;
                if (method_exists($this, $method)) {
                    $results[$check] = $this->$method();
                }
            }

            wp_send_json_success($results);

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    /**
     * Perform specific security check
     *
     * @param string $check_type Type of security check
     * @return array Check results
     */
    private function perform_security_check($check_type) {
        // Verify nonce for all security checks
        if (!check_ajax_referer('divewp_security_nonce', 'nonce', false)) {
            return array(
                'status' => self::STATUS_WARNING,
                'message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')
            );
        }

        $check_type = $this->sanitize_db_input($check_type);
        
        switch ($check_type) {
            case 'ssl':
                return $this->check_ssl();
            case 'file_permissions':
                return $this->check_file_permissions();
            case 'admin_user':
                return $this->check_admin_user();
            case 'db_prefix':
                return $this->check_db_prefix();
            case 'file_editor':
                return $this->check_file_editor();
            case 'debug_mode':
                return $this->check_debug_mode();
            case 'security_plugins':
                return $this->check_security_plugins();
            default:
                return array(
                    'status' => self::STATUS_WARNING,
                    'message' => esc_html__('Invalid check type', 'divewp-boost-site-performance')
                );
        }
    }

    /**
     * Aggregate all security checks for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $checks = array(
            'ssl'               => 'check_ssl',
            'file_permissions'  => 'check_file_permissions',
            'admin_user'        => 'check_admin_user',
            'db_prefix'         => 'check_db_prefix',
            'file_editor'       => 'check_file_editor',
            'debug_mode'        => 'check_debug_mode',
            'security_plugins'  => 'check_security_plugins',
        );

        $results = array();
        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );
        $overall = self::STATUS_GOOD;

        foreach ( $checks as $key => $method ) {
            if ( method_exists( $this, $method ) ) {
                $result = $this->$method();
                $results[ $key ] = $result;
                $summary['total_checks']++;
                $status = isset( $result['status'] ) ? $result['status'] : self::STATUS_INFO;
                if ( self::STATUS_CRITICAL === $status ) {
                    $summary['critical']++;
                    $overall = self::STATUS_CRITICAL;
                } elseif ( self::STATUS_WARNING === $status ) {
                    $summary['warnings']++;
                    if ( self::STATUS_GOOD === $overall ) {
                        $overall = self::STATUS_WARNING;
                    }
                } else {
                    $summary['passed']++;
                }
            }
        }

        return array(
            'status'  => $overall,
            'checks'  => $results,
            'summary' => $summary,
        );
    }
}