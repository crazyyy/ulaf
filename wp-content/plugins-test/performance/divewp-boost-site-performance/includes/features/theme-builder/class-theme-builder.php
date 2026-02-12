<?php
/**
 * Theme and Page Builder Insights Class
 *
 * Provides insights and recommendations for WordPress themes and page builders.
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

/**
 * Class DiveWP_Theme_Builder
 *
 * Handles theme and page builder analysis, providing insights and recommendations
 * for WordPress themes, child themes, translations, and page builder configurations.
 *
 * @package DiveWP
 * @since 1.0.0
 */
class DiveWP_Theme_Builder {
    /**
     * Status constants
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
     * List of checks to perform
     *
     * @var array
     */
    private $checks = array(
        'theme-version',
        'child-theme',
        'theme-translation',
        'inactive-themes',
        'page-builder',
        'translation-plugins'
    );

    /**
     * Nonce actions for form security
     *
     * @var array
     * @since 1.0.0
     */
    private $nonce_actions = array(
        'theme_check' => 'divewp_theme_check_nonce',
        'theme_refresh' => 'divewp_theme_refresh_nonce'
    );

    /**
     * Initialize the class and set up security measures
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';
        $this->content_loader = new DiveWP_Content_Loader();

        // Set up AJAX handlers if needed
        add_action('wp_ajax_divewp_theme_check', array($this, 'handle_theme_check'));
        add_action('wp_ajax_divewp_theme_refresh', array($this, 'handle_theme_refresh'));

        // Add nonce field to the page
        add_action('admin_footer', array($this, 'add_nonce_fields'));
    }

    /**
     * Add nonce fields to the page
     *
     * @since 1.0.4
     * @return void
     */
    public function add_nonce_fields() {
        if (!isset($_GET['page']) || !wp_verify_nonce(wp_create_nonce('divewp_admin_page'), 'divewp_admin_page') || sanitize_text_field(wp_unslash($_GET['page'])) !== 'divewp') {
            return;
        }
        
        foreach ($this->nonce_actions as $action => $nonce) {
            wp_nonce_field($nonce, '_wpnonce_' . $action);
        }
    }

    /**
     * Verify nonce for specific theme builder action
     *
     * @since 1.0.0
     * @param string $action The nonce action to verify
     * @return bool Whether the nonce is valid
     */
    private function verify_nonce($action) {
        if (!isset($this->nonce_actions[$action])) {
            return false;
        }

        $nonce_key = '_wpnonce_' . $action;
        if (isset($_REQUEST[$nonce_key])) {
            $nonce = sanitize_text_field(wp_unslash($_REQUEST[$nonce_key]));
        } elseif (isset($_REQUEST['_wpnonce'])) {
            $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
        } else {
            return false;
        }

        return wp_verify_nonce($nonce, $this->nonce_actions[$action]);
    }

    /**
     * Sanitize and validate input data
     *
     * @since 1.0.0
     * @param mixed $input The input to sanitize
     * @param string $type The type of input (text, int, etc.)
     * @return mixed Sanitized input
     */
    private function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'int':
                return intval($input);
            case 'array':
                return array_map(array($this, 'sanitize_input'), (array) $input);
            case 'file_path':
                return sanitize_file_name($input);
            case 'html':
                return wp_kses_post($input);
            default:
                return sanitize_text_field($input);
        }
    }

    /**
     * Handle AJAX theme check requests
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_theme_check() {
        check_ajax_referer('divewp_theme_check_nonce', '_wpnonce_theme_check');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')
            ));
        }

        $check_type = '';
        if (isset($_POST['check_type'])) {
            $check_type = sanitize_text_field(wp_unslash($_POST['check_type']));
        }

        if (empty($check_type) || !in_array($check_type, $this->checks)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid check type', 'divewp-boost-site-performance')
            ));
        }

        $method = 'check_' . str_replace('-', '_', $check_type);
        if (method_exists($this, $method)) {
            $result = $this->$method();
            wp_send_json_success($result);
        }

        wp_send_json_error(array(
            'message' => esc_html__('Check method not found', 'divewp-boost-site-performance')
        ));
    }

    /**
     * Handle AJAX theme refresh requests
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_theme_refresh() {
        check_ajax_referer('divewp_theme_refresh_nonce', '_wpnonce_theme_refresh');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')
            ));
        }

        $results = array();
        foreach ($this->checks as $check) {
            $method = 'check_' . str_replace('-', '_', $check);
            if (method_exists($this, $method)) {
                $results[$check] = $this->$method();
            }
        }

        wp_send_json_success($results);
    }

    /**
     * Log errors for debugging
     *
     * Note: This is a development-only feature that is disabled in production.
     * Logging only occurs when both WP_DEBUG and DIVEWP_DEBUG_LOG are enabled.
     *
     * @since 1.0.0
     * @param string $message Error message
     * @param string $context Additional context
     * @return void
     */
    private function log_error($message, $context = '') {
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
                    '[DiveWP Theme Builder] %s - Context: %s - File: %s - Line: %d',
                    $log_data['message'],
                    $log_data['context'],
                    $log_data['file'],
                    $log_data['line']
                ));
            }
        }
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @param string $type Check type
     * @return string SVG markup
     */
    private function get_icon( $type ) {
        $icons = array(
            'theme-version' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>',
            'child-theme' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                                <path d="M8 8h8"/>
                                <path d="M8 12h8"/>
                                <path d="M8 16h8"/>
                                <circle cx="19" cy="5" r="3"/>
                            </svg>',
            'theme-translation' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
                            </svg>',
            'page-builder' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7h3a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1z"/>
                                <path d="M17 7h3a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1z"/>
                                <path d="M4 21h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1z"/>
                                <path d="M17 21h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1z"/>
                                <path d="M7 17v-3"/>
                                <path d="M17 17v-3"/>
                                <path d="M7 10V7"/>
                                <path d="M17 10V7"/>
                                <path d="M10 7h4"/>
                                <path d="M10 17h4"/>
                            </svg>',
            'builder-conflicts' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                                <circle cx="19" cy="5" r="3"/>
                                <line x1="17" y1="3" x2="21" y2="7"/>
                            </svg>',
            'translation-plugins' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04z"/>
                                <path d="M18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12z"/>
                            </svg>',
            'inactive-themes' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <line x1="9" y1="3" x2="9" y2="21"/>
                                <path d="M13 8h5"/>
                                <path d="M13 12h5"/>
                                <path d="M13 16h5"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>'
        );

        return isset( $icons[$type] ) ? $icons[$type] : '';
    }

    /**
     * Get status text based on status and check type
     *
     * @param string $status Status constant
     * @param string $check_type Type of check being performed
     * @return string Status text
     */
    private function get_status_text( $status, $check_type ) {
        $status_texts = array(
            'success' => array(
                'theme-version' => __('Up to Date', 'divewp-boost-site-performance'),
                'child-theme' => __('Enabled', 'divewp-boost-site-performance'),
                'theme-translation' => __('Translated', 'divewp-boost-site-performance'),
                'page-builder' => __('Active', 'divewp-boost-site-performance'),
                'builder-conflicts' => __('No Conflicts', 'divewp-boost-site-performance'),
                'translation-plugins' => __('Active', 'divewp-boost-site-performance'),
                'inactive-themes' => __('None Found', 'divewp-boost-site-performance'),
                'default' => __('Good', 'divewp-boost-site-performance')
            ),
            'danger' => array(
                'theme-version' => __('Update Required', 'divewp-boost-site-performance'),
                'child-theme' => __('Required', 'divewp-boost-site-performance'),
                'theme-translation' => __('Missing', 'divewp-boost-site-performance'),
                'page-builder' => __('Multiple Active', 'divewp-boost-site-performance'),
                'builder-conflicts' => __('Conflicts Found', 'divewp-boost-site-performance'),
                'translation-plugins' => __('Multiple Active', 'divewp-boost-site-performance'),
                'inactive-themes' => __('Multiple Found', 'divewp-boost-site-performance'),
                'default' => __('Error', 'divewp-boost-site-performance')
            ),
            'warning' => array(
                'theme-version' => __('Update Available', 'divewp-boost-site-performance'),
                'child-theme' => __('Not Used', 'divewp-boost-site-performance'),
                'theme-translation' => __('Incomplete', 'divewp-boost-site-performance'),
                'page-builder' => __('Not Found', 'divewp-boost-site-performance'),
                'builder-conflicts' => __('Potential Issues', 'divewp-boost-site-performance'),
                'translation-plugins' => __('Not Found', 'divewp-boost-site-performance'),
                'inactive-themes' => __('Warning', 'divewp-boost-site-performance'),
                'default' => __('Warning', 'divewp-boost-site-performance')
            ),
            'info' => array(
                'default' => __('Info', 'divewp-boost-site-performance')
            )
        );
        
        // Get status array for current status
        $current_status = isset($status_texts[$status]) ? $status_texts[$status] : array();
        
        // Return specific text for check type if exists, otherwise return default
        return isset($current_status[$check_type]) ? $current_status[$check_type] : $current_status['default'];
    }

    /**
     * Render individual check
     *
     * @param string $check_type Type of check
     * @param array  $check_result Check result data
     */
    private function render_check( $check_type, $check_result ) {
        try {
            if ( empty( $check_type ) ) {
                throw new Exception( esc_html__('Invalid check type', 'divewp-boost-site-performance') );
            }

            $content = $this->content_loader->get_content( 'theme-builder', $check_type );
            if ( empty( $content ) || ! is_array( $content ) ) {
                throw new Exception( sprintf(
                    /* translators: %s: check type */
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

            $status = $check_result['status'];
            
            // Map status to message type, preserving info and warning states
            if ($status === self::STATUS_GOOD) {
                $message_type = 'success';
            } elseif ($status === self::STATUS_INFO) {
                $message_type = 'info';
            } elseif ($status === self::STATUS_WARNING) {
                $message_type = 'warning';
            } elseif ($status === self::STATUS_CRITICAL) {
                $message_type = 'error';
            } else {
                $message_type = 'error';
            }

            // If message type doesn't exist in content, fallback to appropriate type
            if (!isset($content['messages'][$message_type])) {
                if ($message_type === 'warning' && isset($content['messages']['error'])) {
                    $message_type = 'error';
                } elseif ($message_type === 'info' && isset($content['messages']['success'])) {
                    $message_type = 'success';
                }
            }

            // Validate message type exists
            if ( ! isset( $content['messages'][$message_type] ) || ! is_array( $content['messages'][$message_type] ) ) {
                throw new Exception( sprintf(
                    /* translators: %s: message type */
                    esc_html__('Invalid message type: %s', 'divewp-boost-site-performance'),
                    sanitize_text_field($message_type)
                ));
            }

            $message = $content['messages'][$message_type];

            // Get title with translation
            $title = '';
            if (isset($content['title']) && is_string($content['title'])) {
                $title = esc_html($content['title']);
            }

            // Prepare details text with translation
            $details = '';
            if (isset($message['details']) && is_string($message['details'])) {
                $details = esc_html($message['details']);
                if (isset($check_result['value'])) {
                    $details = str_replace('{value}', esc_html($check_result['value']), $details);
                }
            }

            // Prepare steps with translations
            $steps = array();
            if (!empty($message['steps']) && is_array($message['steps'])) {
                foreach ($message['steps'] as $step) {
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

            // Add recommended plugins if they exist with translations
            if (!empty($content['learn_more']['recommended_plugins']) && is_array($content['learn_more']['recommended_plugins'])) {
                $learn_more['plugins_title'] = esc_html__('Recommended Plugins:', 'divewp-boost-site-performance');
                $learn_more['plugins'] = array();
                foreach ($content['learn_more']['recommended_plugins'] as $plugin) {
                    if (isset($plugin['name']) && isset($plugin['description'])) {
                        $learn_more['plugins'][] = array(
                            'name' => wp_kses_post(esc_html($plugin['name'])),
                            'type' => wp_kses_post(esc_html($plugin['description']))
                        );
                    }
                }
            }

            // Extract variables for template
            $template_vars = array(
                'title' => $title,
                'icon' => $this->get_icon($check_type),
                'details' => $details,
                'steps' => $steps,
                'status' => $status,
                'status_text' => $this->get_status_text($status, $check_type),
                'learn_more' => $learn_more
            );

            // Extract variables for template
            extract($template_vars);

            // Render the card with translated content
            require DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';

        } catch ( Exception $e ) {
            if (defined('WP_DEBUG') && WP_DEBUG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
                $this->log_error(sprintf(
                    /* translators: %1$s: check type, %2$s: error message */
                    esc_html__('Error rendering theme builder check %1$s: %2$s', 'divewp-boost-site-performance'),
                    sanitize_text_field($check_type),
                    $e->getMessage()
                ));
            }
        }
    }

    /**
     * Check theme version
     * 
     * Checks the parent theme version when using a child theme,
     * otherwise checks the active theme version.
     */
    private function check_theme_version() {
        $theme = wp_get_theme();
        $parent_theme = $theme->parent();
        
        // If using child theme, check parent theme version
        if ($parent_theme) {
            $theme_to_check = $parent_theme;
            $theme_name = $parent_theme->get('Name');
        } else {
            $theme_to_check = $theme;
            $theme_name = $theme->get('Name');
        }
        
        // Check for available updates
        $has_update = false;
        if (function_exists('wp_get_theme_update_available')) {
            $has_update = (bool) wp_get_theme_update_available($theme_to_check);
        }
        
        return array(
            'status' => $has_update ? self::STATUS_CRITICAL : self::STATUS_GOOD,
            'value' => sprintf('%s v%s', $theme_name, $theme_to_check->get('Version'))
        );
    }

    /**
     * Render theme version check
     */
    private function render_theme_version_check() {
        $this->render_check( 'theme-version', $this->check_theme_version() );
    }

    /**
     * Check child theme
     * 
     * Checks if a child theme is being used.
     * Returns simple Used/Not Used status.
     */
    private function check_child_theme() {
        $theme = wp_get_theme();
        $parent_theme = $theme->parent();
        
        return array(
            'status' => $parent_theme ? self::STATUS_GOOD : self::STATUS_WARNING,
            'value' => $parent_theme ? __('Used', 'divewp-boost-site-performance') : __('Not Used', 'divewp-boost-site-performance')
        );
    }

    /**
     * Render child theme check
     */
    private function render_child_theme_check() {
        $this->render_check( 'child-theme', $this->check_child_theme() );
    }

    /**
     * Check theme translation
     * 
     * Checks if the current theme has proper translation files for the site's language
     * following WordPress translation standards.
     *
     * @return array Check result with status and details
     */
    private function check_theme_translation() {
        $theme = wp_get_theme();
        $locale = get_locale();
        
        // Initialize result
        $result = array(
            'status' => self::STATUS_WARNING,
            'value' => $locale,
            'details' => array()
        );

        // Get theme details
        $text_domain = $theme->get('TextDomain');
        if (empty($text_domain)) {
            $text_domain = $theme->get_stylesheet(); // Fallback to stylesheet name
        }

        // Define possible translation file patterns
        $mo_patterns = array(
            sprintf('%s-%s.mo', $text_domain, $locale),           // textdomain-locale.mo
            sprintf('%s-%s.mo', $theme->get_stylesheet(), $locale), // stylesheet-locale.mo
            sprintf('%s.mo', $locale)                             // locale.mo
        );

        // Define possible translation directories
        $translation_paths = array(
            get_template_directory() . '/languages',              // Theme languages dir
            get_template_directory() . '/lang',                   // Alternative theme languages dir
            WP_LANG_DIR . '/themes',                             // WP languages dir for themes
            get_stylesheet_directory() . '/languages',            // Child theme languages dir
            get_stylesheet_directory() . '/lang'                  // Alternative child theme languages dir
        );

        // Track found translation files
        $found_translations = array();

        // Check each possible location
        foreach ($translation_paths as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach ($mo_patterns as $pattern) {
                $full_path = trailingslashit($dir) . $pattern;
                if (file_exists($full_path)) {
                    $found_translations[] = $full_path;
                }
            }
        }

        // Determine status based on findings
        if ($locale === 'en_US') {
            // For English, we don't strictly need translation files
            $result['status'] = self::STATUS_GOOD;
            $result['details']['message'] = __('Using default English language', 'divewp-boost-site-performance');
        } elseif (!empty($found_translations)) {
            // Found translation files for current locale
            $result['status'] = self::STATUS_GOOD;
            $result['details']['message'] = sprintf(
                /* translators: %1$d: number of translation files, %2$s: locale */
                __('Found %1$d translation file(s) for %2$s', 'divewp-boost-site-performance'),
                count($found_translations),
                $locale
            );
            $result['details']['files'] = $found_translations;
        } else {
            // No translation files found for non-English locale
            $result['status'] = self::STATUS_WARNING;
            $result['details']['message'] = sprintf(
                /* translators: %s: text */
                __('No translation files found for locale %s', 'divewp-boost-site-performance'),
                $locale
            );
            $result['details']['searched_paths'] = array_filter($translation_paths, 'is_dir');
        }

        // Add theme info to details
        $result['details']['theme_name'] = $theme->get('Name');
        $result['details']['text_domain'] = $text_domain;
        $result['details']['locale'] = $locale;
        
        return $result;
    }

    /**
     * Render theme translation check
     */
    private function render_theme_translation_check() {
        $this->render_check( 'theme-translation', $this->check_theme_translation() );
    }

    /**
     * Detect active page builders including theme-integrated ones
     *
     * @return array List of active builders
     */
    private function detect_page_builders() {
        $active_builders = array();
        
        // Check theme-integrated builders
        $theme = wp_get_theme();
        $theme_builders = array(
            'Divi'   => 'Divi Builder',
            'Avada'  => 'Fusion Builder',
            'Total'  => 'WPBakery',
            'Themify Ultra' => 'Themify Builder',
            'BeTheme' => 'Muffin Builder',
            'Enfold' => 'Avia Layout Builder'
        );

        // Check current theme and parent theme
        $theme_name = $theme->get('Name');
        if (isset($theme_builders[$theme_name])) {
            $active_builders[] = $theme_builders[$theme_name];
        }

        $parent_theme = $theme->parent();
        if ($parent_theme && isset($theme_builders[$parent_theme->get('Name')])) {
            $active_builders[] = $theme_builders[$parent_theme->get('Name')];
        }

        // Check plugin-based builders
        $plugin_builders = array(
            'elementor/elementor.php'                 => 'Elementor',
            'divi-builder/divi-builder.php'          => 'Divi Builder Plugin',
            'beaver-builder-lite-version/fl-builder.php' => 'Beaver Builder',
            'oxygen/functions.php'                   => 'Oxygen Builder',
            'wpbakery-page-builder/js_composer.php'  => 'WPBakery',
            'bricks/bricks.php'                      => 'Bricks',
            'greenshift-animation-and-page-builder-blocks/greenshift.php' => 'GreenShift Builder'
        );

        foreach ($plugin_builders as $plugin => $name) {
            if (is_plugin_active($plugin)) {
                $active_builders[] = $name;
            }
        }

        return array_unique($active_builders);
    }

    /**
     * Check page builder status
     */
    private function check_page_builder() {
        $active_builders = $this->detect_page_builders();
        $builder_count = count($active_builders);
        
        if ($builder_count === 0) {
            return array(
                'status' => self::STATUS_INFO,
                'value' => __('Default Editor', 'divewp-boost-site-performance')
            );
        }
        
        if ($builder_count > 1) {
            return array(
                'status' => self::STATUS_CRITICAL,
                'value' => implode(', ', $active_builders)
            );
        }
        
        return array(
            'status' => self::STATUS_GOOD,
            'value' => reset($active_builders)
        );
    }

    /**
     * Check if theme has an update available
     *
     * @param WP_Theme $theme Theme object
     * @return bool Whether an update is available
     */
    private function theme_has_update( $theme ) {
        if ( ! function_exists( 'wp_get_theme_update_available' ) ) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }
        return (bool) wp_get_theme_update_available( $theme );
    }

    /**
     * Check translation plugins status
     */
    private function check_translation_plugins() {
        $translation_plugins = array(
            'sitepress-multilingual-cms/sitepress.php' => 'WPML',
            'polylang/polylang.php' => 'Polylang',
            'polylang-pro/polylang.php' => 'Polylang Pro',
            'translatepress-multilingual/index.php' => 'TranslatePress',
            'weglot-translate/weglot.php' => 'Weglot',
            'gtranslate/gtranslate.php' => 'GTranslate',
            'multilingual-press/multilingual-press.php' => 'MultilingualPress'
        );

        $compatible_plugins = array(
            'loco-translate/loco.php' => 'Loco Translate'
        );

        $active_plugins = array();
        $active_compatible = array();

        // Check main translation plugins
        foreach ($translation_plugins as $plugin => $name) {
            if (is_plugin_active($plugin)) {
                $active_plugins[] = $name;
            }
        }

        // Check compatible plugins
        foreach ($compatible_plugins as $plugin => $name) {
            if (is_plugin_active($plugin)) {
                $active_compatible[] = $name;
            }
        }

        $plugin_count = count($active_plugins);
        $has_compatible = !empty($active_compatible);

        if ($plugin_count === 0) {
            if ($has_compatible) {
                // Only Loco Translate - this is fine for string translation
                return array(
                    'status' => self::STATUS_GOOD,
                    'value' => sprintf(
                        __('Loco Translate (for theme and plugin translations)', 'divewp-boost-site-performance')
                    )
                );
            }
            // No translation plugins at all - this is also fine if not needed
            return array(
                'status' => self::STATUS_INFO,
                'value' => __('No translation plugins installed', 'divewp-boost-site-performance')
            );
        }

        if ($plugin_count > 1) {
            $value = implode(', ', $active_plugins);
            if ($has_compatible) {
                /* translators: %s: Name of the compatible translation plugin */
                $value .= sprintf(__(', with %s for string translations', 'divewp-boost-site-performance'), reset($active_compatible));
            }
            return array(
                'status' => self::STATUS_CRITICAL,
                'value' => $value
            );
        }

        $value = reset($active_plugins);
        if ($has_compatible) {
            /* translators: %s: Name of the compatible translation plugin */
            $value .= sprintf(__(', with %s for string translations', 'divewp-boost-site-performance'), reset($active_compatible));
        }
        
        return array(
            'status' => self::STATUS_GOOD,
            'value' => $value
        );
    }

    /**
     * Render translation plugins check
     */
    private function render_translation_plugins_check() {
        $this->render_check('translation-plugins', $this->check_translation_plugins());
    }

    /**
     * Check for inactive themes
     *
     * @return array Check result with status and value
     */
    private function check_inactive_themes() {
        // Get all themes
        $themes = wp_get_themes();
        $active_theme = wp_get_theme();
        $parent_theme = $active_theme->parent();
        
        // Count inactive themes (excluding parent theme if using child theme)
        $inactive_count = 0;
        foreach ($themes as $theme) {
            if ($theme->get_stylesheet() !== $active_theme->get_stylesheet() && 
                (!$parent_theme || $theme->get_stylesheet() !== $parent_theme->get_stylesheet())) {
                $inactive_count++;
            }
        }
        
        $status = self::STATUS_GOOD;
        if ($inactive_count > 0) {
            $status = self::STATUS_WARNING;
        }

        return array(
            'status' => $status,
            'value' => $inactive_count
        );
    }

    /**
     * Render inactive themes check card
     */
    private function render_inactive_themes_check() {
        $this->render_check('inactive-themes', $this->check_inactive_themes());
    }

    /**
     * Render page builder check
     */
    private function render_page_builder_check() {
        $this->render_check('page-builder', $this->check_page_builder());
    }

    /**
     * Render the feature content
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        // Add nonce field for security
        wp_nonce_field($this->nonce_actions['theme_check'], '_wpnonce');
        ?>
        <h3><?php esc_html_e('Theme & Page Builder Analysis', 'divewp-boost-site-performance'); ?></h3>
        
        <div class="recommendations-grid">
            <?php
            foreach ($this->checks as $check) {
                $method = 'render_' . str_replace('-', '_', $check) . '_check';
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * Aggregate all theme/builder checks for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $results = array();
        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );
        $overall = self::STATUS_GOOD;

        foreach ( $this->checks as $check ) {
            $method = 'check_' . str_replace( '-', '_', $check );
            if ( method_exists( $this, $method ) ) {
                $result = $this->$method();
                $results[ $check ] = $result;
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