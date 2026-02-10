<?php
/**
 * Email Logger functionality for DiveWP
 *
 * This class handles email logging and management.
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Email_Logger {
    /**
     * Instance of this class
     *
     * @var DiveWP_Email_Logger
     */
    private static $instance = null;

    /**
     * Initialization flag
     *
     * @var boolean
     */
    private static $initialized = false;

    /**
     * Database access instance
     *
     * @var DiveWP_DB_Access
     */
    private $db;

    /**
     * Get instance of this class
     *
     * @return DiveWP_Email_Logger
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        // Initialize database access
        $this->db = DiveWP_DB_Access::get_instance();
        
        // Only initialize if table exists
        if ($this->db->verify_table_exists('divewp_email_log')) {
            add_filter('wp_mail', array($this, 'log_email_before_send'), 1, 1);
            add_action('wp_mail_failed', array($this, 'log_email_failure'), 1, 1);
            
            add_action('divewp_daily_cleanup', array($this, 'cleanup_old_logs'));
            add_action('init', array($this, 'maybe_schedule_cleanup'));
        }
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
     * @param mixed  $data    Optional additional data to log
     * @return void
     */
    private function log_debug($message, $context = '', $data = null) {
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

        if ($data !== null) {
            $log_data['data'] = $data;
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
            if (function_exists('wp_debug_log')) {
                wp_debug_log(sprintf(
                    '[DiveWP Email Logger] %s - Context: %s - Data: %s',
                    $log_data['message'],
                    $log_data['context'],
                    wp_json_encode($log_data)
                ));
            }
        }
    }

    /**
     * Get email source information in a controlled manner
     *
     * Provides basic context about email source without exposing sensitive information.
     * Uses WordPress core functions to safely identify the source context.
     *
     * @since 1.0.4
     * @return string Formatted source information
     */
    private function get_call_trace() {
        $trace = array();
        
        // Get current hook if available
        $current_hook = current_filter();
        if ($current_hook) {
            $trace[] = sprintf('Hook: %s', sanitize_text_field($current_hook));
        }

        // Add basic context about the source
        if (is_admin()) {
            $trace[] = 'Context: Admin';
            $screen = get_current_screen();
            if ($screen) {
                $trace[] = sprintf('Screen: %s', sanitize_text_field($screen->id));
            }
            
            // Add admin-specific context
            global $pagenow;
            if ($pagenow) {
                $trace[] = sprintf('Page: %s', sanitize_text_field($pagenow));
            }
            
            // Check for admin actions with nonce verification
            if (isset($_REQUEST['action']) && isset($_REQUEST['_wpnonce'])) {
                $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
                if (wp_verify_nonce($nonce, 'divewp_admin_action')) {
                    $trace[] = sprintf('Action: %s', sanitize_text_field(wp_unslash($_REQUEST['action'])));
                }
            } elseif (isset($_REQUEST['action'])) {
                // For core WordPress actions that don't require nonce
                $safe_core_actions = array('heartbeat', 'closed-postboxes', 'meta-box-order');
                $action = sanitize_text_field(wp_unslash($_REQUEST['action']));
                if (in_array($action, $safe_core_actions, true)) {
                    $trace[] = sprintf('Core Action: %s', $action);
                }
            }
        } else {
            $trace[] = 'Context: Frontend';
            if (is_singular()) {
                $trace[] = sprintf('Post Type: %s', get_post_type());
                $trace[] = sprintf('Post ID: %d', get_the_ID());
            } elseif (is_archive()) {
                $trace[] = 'Type: Archive';
                if (is_post_type_archive()) {
                    $trace[] = sprintf('Archive Type: %s', get_post_type());
                } elseif (is_category()) {
                    $trace[] = sprintf('Category: %s', single_cat_title('', false));
                } elseif (is_tag()) {
                    $trace[] = sprintf('Tag: %s', single_tag_title('', false));
                }
            } elseif (is_home()) {
                $trace[] = 'Type: Blog Home';
            } elseif (is_search()) {
                $trace[] = 'Type: Search Results';
            }
        }

        // Add user role context if available
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = array_map('sanitize_text_field', $user->roles);
            $trace[] = sprintf('User Role: %s', implode(', ', $roles));
        }

        // Add plugin/theme context if available
        if (did_action('plugins_loaded')) {
            $trace[] = 'Phase: After Plugins Loaded';
        }
        if (did_action('init')) {
            $trace[] = 'Phase: After Init';
        }
        if (did_action('wp_loaded')) {
            $trace[] = 'Phase: After WP Loaded';
        }
        
        return implode(' | ', array_filter($trace));
    }

    /**
     * Log email before sending
     *
     * @since 1.0.0
     * @param array $mail_data Email data array
     * @return array Unmodified email data
     */
    public function log_email_before_send($mail_data) {
        try {
            // Use WordPress timezone instead of UTC
            $datetime = new DateTime('now', wp_timezone());
            
            $log_data = array(
                'status' => 'sent',
                'initiator' => $this->get_call_trace(),
                'from_email' => $mail_data['from'] ?? 'noreply@' . get_site_url(),
                'to_email' => is_array($mail_data['to']) ? reset($mail_data['to']) : $mail_data['to'],
                'subject' => $mail_data['subject'] ?? '',
                'date_sent' => $datetime->format('Y-m-d H:i:s')  // Local time
            );

            $result = $this->db->log_email($log_data);
            
            if (!$result) {
                $this->log_debug(
                    'Failed to log email',
                    'log_email_before_send',
                    array(
                        'from' => $log_data['from_email'],
                        'to' => $log_data['to_email'],
                        'subject' => $log_data['subject']
                    )
                );
            }
        } catch (Exception $e) {
            $this->log_debug(
                'Exception while logging email',
                'log_email_before_send',
                array(
                    'error' => $e->getMessage()
                )
            );
        }
        
        return $mail_data;
    }

    /**
     * Log failed email
     *
     * @since 1.0.0
     * @param WP_Error $error WordPress error object containing email failure details
     */
    public function log_email_failure($error) {
        try {
            // Use WordPress timezone
            $datetime = new DateTime('now', wp_timezone());

            $log_data = array(
                'status' => 'failed',
                'initiator' => $this->get_call_trace(),
                'from_email' => $error->from ?? get_option('admin_email'),
                'to_email' => is_array($error->to) ? reset($error->to) : $error->to,
                'subject' => $error->subject ?? '',
                'error_msg' => $error->errors['wp_mail_failed'][0] ?? '',
                'date_sent' => $datetime->format('Y-m-d H:i:s')  // Local time
            );

            $result = $this->db->log_email($log_data);
            
            if (!$result) {
                $this->log_debug(
                    'Failed to log email failure',
                    'log_email_failure',
                    array(
                        'from' => $log_data['from_email'],
                        'to' => $log_data['to_email'],
                        'error' => $log_data['error_msg']
                    )
                );
            }
        } catch (Exception $e) {
            $this->log_debug(
                'Exception while logging email failure',
                'log_email_failure',
                array(
                    'error' => $e->getMessage()
                )
            );
        }
    }

    /**
     * Keep only last 100 entries in the email log
     *
     * @since 1.0.0
     */
    public function cleanup_old_logs() {
        $this->db->cleanup_email_logs(100);
    }

    /**
     * Register daily cleanup cron after init for proper timing
     *
     * @since 2.2.0
     * @return void
     */
    public function maybe_schedule_cleanup() {
        if (!wp_next_scheduled('divewp_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'divewp_daily_cleanup');
        }
    }

    // Add this new method to handle time conversion
    public function get_localized_time($utc_time) {
        try {
            $utc_date = DateTime::createFromFormat(
                'Y-m-d H:i:s', 
                $utc_time, 
                new DateTimeZone('UTC')
            );
            
            if (!$utc_date) {
                return $utc_time; // Fallback if parsing fails
            }
            
            return $utc_date->setTimezone(wp_timezone())->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return $utc_time; // Fallback on error
        }
    }

    // Then modify the log retrieval method (pseudo-code - need to see actual get_logs method)
    public function get_email_logs() {
        $logs = $this->db->get_logs();
        
        foreach ($logs as $log) {
            $log->local_time = $this->get_localized_time($log->date_sent);
        }
        
        return $logs;
    }
} 