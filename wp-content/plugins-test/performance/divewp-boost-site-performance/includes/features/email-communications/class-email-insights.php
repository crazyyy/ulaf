<?php
/**
 * Email Communications Insights functionality for DiveWP
 *
 * This class provides email-related insights and recommendations.
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Email_Insights {
    /**
     * Status constants
     */
    const STATUS_GOOD = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO = 'info';

    /**
     * Content loader instance
     * 
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Database access instance
     *
     * @var DiveWP_DB_Access
     */
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->content_loader = new DiveWP_Content_Loader();
        $this->db = DiveWP_DB_Access::get_instance();
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_divewp_send_test_email', array($this, 'send_test_email_handler'));
        add_action('wp_ajax_divewp_refresh_email_log', array($this, 'refresh_email_log_handler'));
        add_action('wp_ajax_divewp_delete_all_email_logs', array($this, 'ajax_delete_all_email_logs'));
    }

    /**
     * Enqueue necessary assets
     */
    public function enqueue_assets($hook) {
        if ('toplevel_page_divewp' !== $hook) {
            return;
        }

        // Enqueue Email Communications CSS
        wp_enqueue_style(
            'divewp-email-communications',
            DIVEWP_PLUGIN_URL . 'assets/css/features/email-communications.css',
            array(),
            DIVEWP_VERSION
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );

        // Add admin JS file
        wp_enqueue_script(
            'divewp-admin-js', // This handle must match localization
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-admin.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );

        // Localize for AJAX - CORRECTED VERSION
        wp_localize_script('divewp-admin-js', 'divewpEmailTest', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('divewp_nonce'),
            'sending' => __('Sending test email...', 'divewp-boost-site-performance'),
            'success' => __('Test email sent successfully! Check your inbox.', 'divewp-boost-site-performance'),
            'error' => __('Failed to send test email. Check your email configuration.', 'divewp-boost-site-performance'),
            'confirmDeleteLogs' => __('Are you sure you want to delete all email logs? This cannot be undone.', 'divewp-boost-site-performance'),
            'deleting' => __('Deleting...', 'divewp-boost-site-performance'),
            'deleteAllLogs' => __('Delete All Logs', 'divewp-boost-site-performance'),
            'noLogsFound' => __('No email logs found.', 'divewp-boost-site-performance')
        ));

        // Enqueue user-events CSS
        wp_enqueue_style(
            'divewp-user-events',
            DIVEWP_PLUGIN_URL . 'assets/css/features/user-events.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Render email insights
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        /* translators: Title for the email communications insights section in the admin interface */
        echo '<h3>' . esc_html__('Email Communications Insights', 'divewp-boost-site-performance') . '</h3>';
        
        // Render checks in cards
        echo '<div class="recommendations-grid">';
        $this->render_smtp_check();
        $this->render_auth_check();
        $this->render_wpmail_check();
        echo '</div>';

        // Render email log section
        echo '<div class="email-log-section">';
        /* translators: Title for the email log section that shows sent emails history */
        echo '<h3>' . esc_html__('Email Log', 'divewp-boost-site-performance') . '</h3>';
        $this->render_email_log_table();
        echo '</div>';
    }

    /**
     * Render SMTP configuration check
     *
     * @since 1.0.0
     * @return void
     */
    private function render_smtp_check() {
        $this->render_check('smtp-configuration', $this->check_smtp_configuration());
    }

    /**
     * Render email authentication check
     *
     * @since 1.0.0
     * @return void
     */
    private function render_auth_check() {
        $this->render_check('email-authentication', $this->check_spf_dkim());
    }

    /**
     * Render WP Mail status check
     *
     * @since 1.0.0
     * @return void
     */
    private function render_wpmail_check() {
        $this->render_check('wp-mail-status', $this->check_wp_mail());
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @since 1.0.0
     * @param string $type The type of icon to retrieve (smtp|auth|mail)
     * @return string SVG markup for the icon
     */
    private function get_icon($type) {
        $icons = array(
            'smtp' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>',
            'auth' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>',
            'mail' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <path d="M22,6 L12,13 L2,6"/>
                    </svg>'
        );

        return isset($icons[$type]) ? $icons[$type] : $icons['mail'];
    }

    /**
     * Get status text based on status code
     *
     * @since 1.0.0
     * @param string $status Status code (success|warning|danger|info)
     * @return string Translated status text
     */
    private function get_status_text($status) {
        switch ($status) {
            case self::STATUS_GOOD:
                /* translators: Status text shown when an email feature is properly configured */
                return __('Configured', 'divewp-boost-site-performance');
            case self::STATUS_WARNING:
                /* translators: Status text shown when an email feature needs attention */
                return __('Needs Attention', 'divewp-boost-site-performance');
            case self::STATUS_CRITICAL:
                /* translators: Status text shown when an email feature is not configured */
                return __('Not Configured', 'divewp-boost-site-performance');
            default:
                /* translators: Status text shown when an email feature's status is unknown */
                return __('Unknown', 'divewp-boost-site-performance');
        }
    }

    /**
     * Check SMTP configuration by detecting active email plugins
     *
     * @since 1.0.0
     * @return array {
     *     @type string $status           Check status (success|warning|danger)
     *     @type array  $detected_plugins List of detected SMTP plugins
     * }
     */
    private function check_smtp_configuration() {
        $smtp_plugins = array(
            'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
            'post-smtp/postman-smtp.php' => 'Post SMTP',
            'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
            'fluent-smtp/fluent-smtp.php' => 'FluentSMTP',
            'gosmtp/gosmtp.php' => 'GoSMTP',
            'mailgun/mailgun.php' => 'Mailgun',
            'mailin/sendinblue.php' => 'Brevo'
        );

        $detected_plugins = array();
        foreach ($smtp_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = $plugin_name;
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_CRITICAL,
            'detected_plugins' => $detected_plugins
        );
    }

    /**
     * Check SPF/DKIM authentication plugin status
     *
     * @since 1.0.0
     * @return array {
     *     @type string $status           Check status (success|warning|danger)
     *     @type array  $detected_plugins List of detected authentication plugins
     * }
     */
    private function check_spf_dkim() {
        $auth_plugins = array(
            'wp-mail-smtp/wp-mail-smtp.php' => 'WP Mail SMTP',
            'post-smtp/postman-smtp.php' => 'Post SMTP',
            'fluent-smtp/fluent-smtp.php' => 'FluentSMTP'
        );

        $detected_plugins = array();
        foreach ($auth_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = $plugin_name;
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_CRITICAL,
            'detected_plugins' => $detected_plugins
        );
    }

    /**
     * Check WP Mail function availability
     *
     * @since 1.0.0
     * @return array {
     *     @type string $status Check status (success|warning|danger)
     * }
     */
    private function check_wp_mail() {
        return array(
            'status' => function_exists('wp_mail') ? self::STATUS_GOOD : self::STATUS_CRITICAL
        );
    }

    /**
     * Generic method to render a recommendation card
     */
    private function render_check($check_type, $check_result) {
        try {
            if (empty($check_type)) {
                throw new Exception(__('Invalid check type', 'divewp-boost-site-performance'));
            }

            $content = $this->content_loader->get_content('email-communications', $check_type);
            if (empty($content) || !is_array($content)) {
                /* translators: %s: Check type for which content was not found */
                throw new Exception(sprintf(__("Content not found for check type: %s", 'divewp-boost-site-performance'), $check_type));
            }

            // Validate required content structure
            if (!isset($content['messages']) || !is_array($content['messages'])) {
                throw new Exception(__('Missing or invalid messages array', 'divewp-boost-site-performance'));
            }

            // Validate check result
            if (empty($check_result) || !isset($check_result['status'])) {
                throw new Exception(__('Invalid check result', 'divewp-boost-site-performance'));
            }

            $message_type = ($check_result['status'] === self::STATUS_GOOD) ? 'success' : 'error';

            // Validate message type exists
            if (!isset($content['messages'][$message_type]) || !is_array($content['messages'][$message_type])) {
                /* translators: %s: Message type identifier */
                throw new Exception(sprintf(__("Invalid message type: %s", 'divewp-boost-site-performance'), $message_type));
            }

            $messages = $content['messages'][$message_type];

            // Process message content with translations
            $processed_message = array(
                'title'   => isset($messages['title']) ? esc_html($messages['title']) : '',
                'details' => isset($messages['details']) ? esc_html($messages['details']) : ''
            );

            // Replace plugin placeholder if plugins are detected
            if (!empty($check_result['detected_plugins'])) {
                $processed_message['details'] = strtr($processed_message['details'], array(
                    '{plugin_name}' => implode(', ', array_map(function($plugin) {
                        return esc_html($plugin);
                    }, $check_result['detected_plugins']))
                ));
            }

            // Process steps with translations
            if (isset($messages['steps'])) {
                $processed_message['steps'] = array_map(function($step) {
                    return esc_html($step);
                }, $messages['steps']);
            } else {
                $processed_message['steps'] = array();
            }

            // Prepare template variables with validation and translation
            $template_vars = array(
                'title' => isset($messages['title']) ? esc_html($messages['title']) : '',
                'icon' => $this->get_icon($check_type),
                'details' => esc_html($processed_message['details']),
                'steps' => array_map('esc_html', $processed_message['steps']),
                'status' => $check_result['status'],
                'status_text' => $this->get_status_text($check_result['status']),
                'check_name' => esc_attr($check_type),
                'feature' => 'email-communications'
            );

            // Process learn more content with translations
            if (isset($content['learn_more']) && is_array($content['learn_more'])) {
                $template_vars['learn_more'] = array(
                    'description'    => isset($content['learn_more']['description']) 
                        ? esc_html($content['learn_more']['description']) 
                        : '',
                    'benefits_title' => esc_html__('Benefits:', 'divewp-boost-site-performance'),
                    'benefits'       => isset($content['learn_more']['benefits']) 
                        ? array_map(function($benefit) {
                            return esc_html($benefit);
                        }, $content['learn_more']['benefits']) 
                        : array()
                );

                if (isset($content['learn_more']['recommended_plugins']) && is_array($content['learn_more']['recommended_plugins'])) {
                    $template_vars['learn_more']['plugins_title'] = esc_html__('Recommended plugins:', 'divewp-boost-site-performance');
                    $template_vars['learn_more']['plugins'] = array_map(function($plugin) {
                        return array(
                            'name' => isset($plugin['name']) 
                                ? esc_html($plugin['name']) 
                                : '',
                            'description' => isset($plugin['description']) 
                                ? esc_html($plugin['description']) 
                                : ''
                        );
                    }, $content['learn_more']['recommended_plugins']);
                }
            }

            // Extract variables for template
            extract($template_vars);

            // Include the card template
            require DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';

        } catch (Exception $e) {
            $this->log_debug(
                'Error rendering email check',
                'Email Check Render',
                array(
                    'check_type' => sanitize_text_field($check_type),
                    'error' => $e->getMessage()
                )
            );
        }
    }

    /**
     * Enhanced error logging with detailed context
     *
     * Logs detailed error information when WP_DEBUG is enabled.
     * Uses WordPress error logging functions in a controlled manner.
     * Only logs in debug mode and sanitizes all data.
     *
     * Note: This is a development-only feature that is disabled in production.
     * Logging only occurs when both WP_DEBUG and DIVEWP_DEBUG_LOG are enabled.
     *
     * @since 1.0.0
     * @param string $message     Error message to log
     * @param string $context     Context where the error occurred
     * @param mixed  $error_data  Optional. Additional error data
     * @return void
     */
    private function log_debug($message, $context = '', $error_data = null) {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $log_data = array(
            'message' => sanitize_text_field($message),
            'context' => sanitize_text_field($context),
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );

        if ($error_data !== null) {
            if (is_array($error_data)) {
                $log_data['error_details'] = array_map('sanitize_text_field', $error_data);
            } else {
                $log_data['error_details'] = sanitize_text_field($error_data);
            }
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
            // Translators: 1: Message 2: Context 3: Error details
            $log_message = sprintf(
                '[DiveWP Email Insights] %1$s - Context: %2$s - Details: %3$s',
                $log_data['message'],
                $log_data['context'],
                wp_json_encode($log_data)
            );
            
            if (function_exists('wp_privacy_anonymize_data')) {
                $log_message = wp_privacy_anonymize_data('ip', $log_message);
            }
            
            // Use WordPress debug logging if available
            if (function_exists('wp_debug_log')) {
                wp_debug_log($log_message);
            }
        }
    }

    /**
     * Send test email handler with rate limiting
     *
     * @since 1.0.0
     */
    public function send_test_email_handler() {
        // Add rate limiting
        $user_id = get_current_user_id();
        $rate_limit_key = 'divewp_test_email_' . $user_id;
        $last_email_time = get_transient($rate_limit_key);
        
        if ($last_email_time !== false) {
            $time_passed = time() - $last_email_time;
            if ($time_passed < 300) { // 5 minutes limit
                $this->log_debug(
                    'Rate limit hit for test email',
                    'Test Email',
                    array(
                        'user_id' => $user_id,
                        'time_remaining' => 300 - $time_passed
                    )
                );
                wp_send_json_error(array(
                    'message' => sprintf(/* translators: %d: number of minutes the user must wait before sending another test email */
                        __('Please wait %d minutes before sending another test email.', 'divewp-boost-site-performance'),
                        ceil((300 - $time_passed) / 60)
                    )
                ));
            }
        }

        // Get and validate IP
        $ip = '';
        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $ip = 'invalid';
            }
        }

        // Debug log with request info
        $this->log_debug(
            'Test email handler called',
            'Test Email',
            array(
                'ip' => $ip,
                'user_id' => get_current_user_id()
            )
        );
        
        // Security checks
        if (!check_ajax_referer('divewp_nonce', 'nonce', false)) {
            $this->log_debug(
                'Test email security check failed',
                'Test Email',
                array('user_id' => get_current_user_id())
            );
            wp_send_json_error(array(
                'message' => __('Security check failed', 'divewp-boost-site-performance')
            ));
        }

        if (!current_user_can('manage_options')) {
            $this->log_debug(
                'Test email permission denied',
                'Test Email',
                array('user_id' => get_current_user_id())
            );
            wp_send_json_error(array(
                'message' => __('Permission denied', 'divewp-boost-site-performance')
            ));
        }

        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Debug log
        $this->log_debug(
            'Attempting to send test email',
            'Test Email',
            array('to' => $admin_email)
        );

        // Prepare email content
        $subject = __('DiveWP Email Configuration Test - Verify Your Email Settings', 'divewp-boost-site-performance');
        $message = sprintf(/* translators: %s: Name of the current blog */
            __('This is a test email sent from %s using the DiveWP plugin.', 'divewp-boost-site-performance'),
            get_bloginfo('name')
        );
        $message .= "\n\n";
        $message .= __('If you received this email, it means your WordPress email functionality is working correctly.', 'divewp-boost-site-performance');

        // Send email
        $result = wp_mail($admin_email, $subject, $message);
        
        // Log detailed result
        $this->log_debug(
            'Test email result',
            'Test Email',
            array(
                'to' => $admin_email,
                'result' => $result ? 'success' : 'failed',
                'error' => error_get_last() ? json_encode(error_get_last()) : 'No error details'
            )
        );

        if ($result) {
            // Set rate limit
            set_transient($rate_limit_key, time(), 300);
            wp_send_json_success(array(
                'message' => __('Test email sent successfully! Check your inbox.', 'divewp-boost-site-performance')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to send test email. Check your email configuration.', 'divewp-boost-site-performance')
            ));
        }
    }

    /**
     * Render email log table
     */
    private function render_email_log_table() {
        // Add test email button section
        ?>
        <div class="email-test-section divewp-header-actions divewp-notice divewp-notice-warning">
            <div class="cleanup-status">
                <?php esc_html_e('Email logs are automatically cleaned up after 30 days.', 'divewp-boost-site-performance'); ?>
            </div>
            <div class="divewp-actions">
                <button type="button" id="divewp-refresh-email-logs" class="button divewp-action-button divewp-refresh-button">
                    <?php esc_html_e('Refresh Logs', 'divewp-boost-site-performance'); ?>
                </button>
                <button type="button" id="divewp-delete-all-email-logs" class="button divewp-action-button divewp-delete-button">
                    <?php esc_html_e('Delete All Logs', 'divewp-boost-site-performance'); ?>
                </button>
                <button type="button" id="divewp-send-test-email" class="button">
                    <?php esc_html_e('Send Test Email', 'divewp-boost-site-performance'); ?>
                </button>
            </div>
            <span id="test-email-result" class="test-email-message"></span>
        </div>
        <?php

        // Add placeholder for email logs
        ?>
        <div id="divewp-email-logs-placeholder" class="divewp-table-container">
            <?php if (!empty($logs)) : ?>
                <?php echo wp_kses_post($this->get_email_logs_table_html()); ?>
            <?php else : ?>
                <div class="divewp-no-activity">
                    <span class="dashicons dashicons-email-alt"></span>
                    <p><?php esc_html_e('No email logs found.', 'divewp-boost-site-performance'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get email logs table HTML
     *
     * @since 1.0.0
     * @return string HTML markup for the email logs table
     */
    private function get_email_logs_table_html() {
        $logs = $this->db->get_recent_emails(10);
        
        ob_start();
        
        if (empty($logs)) {
            echo '<div class="divewp-no-activity">' .
                   '<span class="dashicons dashicons-email-alt"></span>' .
                   '<p>' . esc_html__('No email logs found.', 'divewp-boost-site-performance') . '</p>' .
                   '</div>';
        } else {
            ?>
            <table class="divewp-table divewp-email-log-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'divewp-boost-site-performance'); ?></th>
                        <th><?php esc_html_e('Status', 'divewp-boost-site-performance'); ?></th>
                        <th><?php esc_html_e('Initiator', 'divewp-boost-site-performance'); ?></th>
                        <th><?php esc_html_e('From', 'divewp-boost-site-performance'); ?></th>
                        <th><?php esc_html_e('To', 'divewp-boost-site-performance'); ?></th>
                        <th><?php esc_html_e('Subject', 'divewp-boost-site-performance'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->date_sent); ?></td>
                            <td>
                                <span class="status-pill status-pill-<?php echo $log->status === 'sent' ? 'success' : 'danger'; ?>">
                                    <?php echo esc_html(ucfirst($log->status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($this->clean_initiator_info($log->initiator)); ?></td>
                            <td><?php echo esc_html($log->from_email); ?></td>
                            <td><?php echo esc_html($log->to_email); ?></td>
                            <td><?php echo esc_html($log->subject); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        }
        
        return ob_get_clean();
    }

    /**
     * Clean up initiator information while preserving important details
     */
    private function clean_initiator_info($initiator) {
        // Remove full paths but keep function names
        $initiator = preg_replace('/(?:\/.*\/)?([^\/]+\.php)/', '$1', $initiator);
        
        // Keep important hooks and function names
        $important_parts = array();
        if (preg_match_all('/(wp_mail|do_action|WP_Hook|apply_filters|[a-zA-Z_]+_handler)/', $initiator, $matches)) {
            $important_parts = $matches[0];
        }
        
        return implode(' → ', array_unique($important_parts));
    }

    /**
     * AJAX handler for refreshing email log
     */
    public function refresh_email_log_handler() {
        // Add security check
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied', 'divewp-boost-site-performance')));
        }
        
        $html = $this->get_email_logs_table_html();
        wp_send_json_success(array('html' => $html));
    }

    public function ajax_delete_all_email_logs() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')
            ));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('Permission denied', 'divewp-boost-site-performance')
            ));
            return;
        }

        try {
            if ($this->db->delete_all_email_logs()) {
                wp_send_json_success(array(
                    'message' => esc_html__('All email logs deleted successfully', 'divewp-boost-site-performance'),
                    'html' => $this->get_email_logs_table_html()
                ));
            } else {
                throw new Exception(esc_html__('Failed to delete email logs', 'divewp-boost-site-performance'));
            }
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    public function ajax_refresh_email_log_handler() {
        // Existing code remains, just ensure it returns proper HTML
        $html = $this->get_email_logs_table_html();
        wp_send_json_success(array('html' => $html));
    }

    /**
     * Aggregate the three email checks for Abilities/MCP (no logs).
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $checks = array(
            'smtp'  => $this->check_smtp_configuration(),
            'auth'  => $this->check_spf_dkim(),
            'wp_mail' => $this->check_wp_mail(),
        );

        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );

        $overall = self::STATUS_GOOD;

        foreach ( $checks as $key => $result ) {
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

        return array(
            'status'  => $overall,
            'checks'  => $checks,
            'summary' => $summary,
        );
    }
} 