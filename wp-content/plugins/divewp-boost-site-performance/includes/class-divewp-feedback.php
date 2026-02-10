<?php
/**
 * DiveWP Feedback Notice Handler
 *
 * Manages the display and interaction of feedback notices to users.
 * Handles notice dismissal, reminders, and tracks user engagement.
 *
 * @package DiveWP
 * @since 1.0.4
 * @license GPL-2.0+
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Class DiveWP_Feedback
 * 
 * Handles user feedback collection and notice management.
 *
 * @since 1.0.4
 */
class DiveWP_Feedback {
    
    /**
     * Plugin settings
     *
     * @var array
     * @access private
     */
    private $settings;

    /**
     * Logger instance
     *
     * @var WP_Error
     */
    private $logger;

    /**
     * Default settings
     *
     * @since 1.0.0
     * @var array
     */
    private static $default_settings = array(
        'initial_delay' => 464000,      // Seconds before first display (5 days)
        'remind_delay'  => 864000,      // Seconds to wait after "remind me later" (10 days)
        'min_insights'  => 0,           // No minimum insights for testing
        'min_fixes'     => 0,           // No minimum fixes for testing
    );

    /**
     * Settings configuration
     */
    private const SETTINGS_CONFIG = array(
        'type' => 'array',
        'description' => 'DiveWP Feedback Settings',
        'sanitize_callback' => array(self::class, 'sanitize_settings_static'),
        'show_in_rest' => false,
        'default' => array(
            'initial_delay' => 464000,
            'remind_delay'  => 864000,
            'min_insights'  => 0,
            'min_fixes'     => 0
        ),
        'schema' => array(
            'type' => 'object',
            'required' => array('initial_delay', 'remind_delay', 'min_insights', 'min_fixes'),
            'properties' => array(
                'initial_delay' => array(
                    'type' => 'integer',
                    'minimum' => 3600,
                    'maximum' => 2592000,
                ),
                'remind_delay' => array(
                    'type' => 'integer',
                    'minimum' => 3600,
                    'maximum' => 2592000,
                ),
                'min_insights' => array(
                    'type' => 'integer',
                    'minimum' => 0,
                ),
                'min_fixes' => array(
                    'type' => 'integer',
                    'minimum' => 0,
                ),
            ),
        ),
    );

    /**
     * Initialize the feedback functionality
     */
    public function __construct() {
        $this->logger = new WP_Error();
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            $this->log_message('Constructor called');
        }
        
        $this->init_settings();
        
        // Only reset settings if in debug mode
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            $this->reset_debug_settings();
        }
        
        $this->init_hooks();
    }

    /**
     * Log a debug message if debugging is enabled
     *
     * Note: This is a development-only feature that is disabled in production.
     * Logging only occurs when both WP_DEBUG and DIVEWP_DEBUG_LOG are enabled.
     *
     * @param string $message Message to log
     * @param string $level One of: info, warning, error
     */
    private function log_message($message, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $this->logger->add('divewp_feedback_' . $level, 'DiveWP Feedback: ' . $message);
        
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
            if (function_exists('wp_debug_log')) {
                wp_debug_log(sprintf('[DiveWP Feedback] %s: %s', $level, $message));
            }
        }
    }

    /**
     * Reset debug settings
     */
    private function reset_debug_settings() {
        delete_option('divewp_feedback_settings');
        delete_option('divewp_feedback_dismissed');
        delete_transient('divewp_feedback_reminder');
        delete_option('divewp_install_time');
        $this->log_message('Debug settings reset');
    }

    /**
     * Initialize hooks at the right time
     *
     * @since 1.0.4
     * @action admin_enqueue_scripts
     * @action admin_notices
     * @action wp_ajax_divewp_dismiss_feedback
     * @action admin_init
     */
    public function init_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_notices', array($this, 'display_feedback_notice'), 10);
        add_action('wp_ajax_divewp_dismiss_feedback', array($this, 'dismiss_feedback_notice'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Initialize default settings
     */
    private function init_settings() {
        $this->settings = get_option('divewp_feedback_settings', self::$default_settings);
    }

    /**
     * Register settings
     *
     * @since 1.0.0
     * @return void
     */
    public static function register_settings() {
        register_setting(
            'divewp_options',
            'divewp_feedback_settings',
            self::SETTINGS_CONFIG
        );
    }

    /**
     * Static sanitization method for settings
     *
     * @since 1.0.0
     * @param array|mixed $input The settings array to sanitize
     * @return array Sanitized settings
     */
    public static function sanitize_settings_static($input) {
        $defaults = array(
            'initial_delay' => 464000,
            'remind_delay'  => 864000,
            'min_insights'  => 0,
            'min_fixes'     => 0
        );

        if (!is_array($input)) {
            return $defaults;
        }

        $sanitized = array();

        // Initial delay (minimum 1 hour, maximum 30 days)
        $initial_delay = isset($input['initial_delay']) ? absint($input['initial_delay']) : $defaults['initial_delay'];
        $sanitized['initial_delay'] = min(max($initial_delay, 3600), 2592000);

        // Remind delay (minimum 1 hour, maximum 30 days)
        $remind_delay = isset($input['remind_delay']) ? absint($input['remind_delay']) : $defaults['remind_delay'];
        $sanitized['remind_delay'] = min(max($remind_delay, 3600), 2592000);

        // Minimum insights (0 or positive integer)
        $sanitized['min_insights'] = isset($input['min_insights']) ? max(0, absint($input['min_insights'])) : $defaults['min_insights'];

        // Minimum fixes (0 or positive integer)
        $sanitized['min_fixes'] = isset($input['min_fixes']) ? max(0, absint($input['min_fixes'])) : $defaults['min_fixes'];

        return $sanitized;
    }

    /**
     * Update feedback settings
     *
     * @param array $settings New settings
     * @return bool Whether the settings were updated
     */
    public function update_settings($settings) {
        $this->settings = wp_parse_args($settings, $this->settings);
        return update_option('divewp_feedback_settings', $this->settings);
    }

    /**
     * Get current settings
     *
     * @return array Current settings
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Enqueue custom styles for feedback notice
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'divewp-feedback-style',
            DIVEWP_PLUGIN_URL . 'assets/css/features/feedback.css',
            array(),
            DIVEWP_VERSION
        );

        wp_enqueue_script(
            'divewp-feedback-script',
            DIVEWP_PLUGIN_URL . 'assets/js/feedback.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );

        wp_localize_script('divewp-feedback-script', 'divewpFeedback', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('divewp_feedback_nonce')
        ));
    }

    /**
     * Check if we should display the feedback notice
     */
    private function should_display_notice() {
        // Remove all debug logs
        
        // Don't show if notice was dismissed permanently
        $dismissed = get_option('divewp_feedback_dismissed');
        if ($dismissed) {
            return false;
        }

        // Don't show if "remind me later" is active
        if (get_transient('divewp_feedback_reminder')) {
            return false;
        }

        // Check if plugin has been active for minimum seconds
        $install_time = get_option('divewp_install_time', time());
        if (!$install_time) {
            $install_time = time();
            update_option('divewp_install_time', $install_time);
        }

        $diff = time() - $install_time;
        if ($diff < $this->settings['initial_delay']) {
            return false;
        }

        return true;
    }

    /**
     * Display the feedback notice
     */
    public function display_feedback_notice() {
        if (!$this->should_display_notice()) {
            return;
        }
        ?>
        <div class="divewp-feedback-notice notice notice-info is-dismissible" role="alert" aria-live="polite">
            <div class="divewp-feedback-content">
                <img src="<?php echo esc_url(DIVEWP_PLUGIN_URL . 'assets/images/oleg_head.png'); ?>" 
                     alt="<?php esc_attr_e('Oleg Petrov profile picture', 'divewp-boost-site-performance'); ?>" 
                     class="divewp-feedback-logo">
                <div class="divewp-feedback-message">
                    <h3 id="divewp-feedback-heading"><?php esc_html_e('Message from Oleg Petrov: PLEASE GIVE FEEDBACK!', 'divewp-boost-site-performance'); ?></h3>
                    <p><?php esc_html_e('Your feedback is incredibly valuable to us! It helps us understand your needs better and improve DiveWP to serve you better. Would you take a moment to share your thoughts with us on Facebook? Your input will directly influence our future updates.', 'divewp-boost-site-performance'); ?></p>
                    <div class="divewp-feedback-actions">
                        <a href="https://www.facebook.com/ReplikonBG/" 
                           class="button button-primary" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           aria-label="<?php esc_attr_e('Open Facebook to give feedback (opens in new window)', 'divewp-boost-site-performance'); ?>">
                            <?php esc_html_e('Give Feedback', 'divewp-boost-site-performance'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle notice dismissal via AJAX
     */
    public function dismiss_feedback_notice() {
        // Verify both nonce and user capabilities
        if (!check_ajax_referer('divewp_feedback_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'divewp-boost-site-performance')), 403);
        }
        
        $type = '';
        if (isset($_POST['type'])) {
            $type = sanitize_text_field(wp_unslash($_POST['type']));
        }
        
        if (!in_array($type, array('permanent', 'remind_later'))) {
            wp_send_json_error(array('message' => __('Invalid dismissal type', 'divewp-boost-site-performance')), 400);
        }
        
        if ($type === 'remind_later') {
            // Set reminder based on settings (in seconds)
            set_transient('divewp_feedback_reminder', true, $this->settings['remind_delay']);
        } else {
            // Permanent dismissal
            update_option('divewp_feedback_dismissed', true);
        }

        wp_send_json_success();
    }

    /**
     * Track user interaction statistics
     *
     * @param string $type Type of interaction (insight_view|fix_applied)
     * @return bool Success status
     */
    private function track_interaction($type) {
        if (!in_array($type, array('insight_view', 'fix_applied'))) {
            $this->log_message("Invalid interaction type: $type", 'error');
            return false;
        }

        $option_name = 'divewp_' . $type . 's';
        $current = get_option($option_name, 0);
        $updated = update_option($option_name, $current + 1);

        if ($updated) {
            $this->log_message("Tracked $type interaction");
        } else {
            $this->log_message("Failed to track $type interaction", 'error');
        }

        return $updated;
    }

    /**
     * Track insight view
     */
    public function track_insight_view() {
        return $this->track_interaction('insight_view');
    }

    /**
     * Track fix applied
     */
    public function track_fix_applied() {
        return $this->track_interaction('fix_applied');
    }
} 