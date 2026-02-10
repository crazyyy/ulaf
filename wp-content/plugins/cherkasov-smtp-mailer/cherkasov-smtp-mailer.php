<?php
/**
 * Plugin Name: Cherkasov SMTP Mailer
 * Version: 1.0
 * Author: Denys Cherkasov
 * Author URI: https://cherkasov.dev
 * Description: A reliable SMTP plugin for WordPress that ensures email delivery with logging and Telegram notifications.
 * Text Domain: cherkasov-smtp-mailer
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ### CONSTANTS ###
define( 'CSMTP_VERSION', '1.0' );
define( 'CSMTP_FILE', __FILE__ );
define( 'CSMTP_LOG_TABLE', $GLOBALS['wpdb']->prefix . 'csmtp_logs' );
define( 'CSMTP_CRON_HOOK', 'csmtp_log_cleanup_cron' );

/**
 * The main plugin class.
 */
final class CSMTP_Mailer {

    private $hook_suffix = '';

    public function __construct() {
        $this->loader_operations();
    }

    /**
     * Register all hooks and filters.
     */
    public function loader_operations() {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded_handler' ) );
        add_action( 'admin_menu', array( $this, 'options_menu' ) );
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ), 999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'prepare_assets' ) );

        $ajax_actions = [
            'get_logs', 'get_log_details', 'send_test_email', 'clear_logs', 'delete_log',
            'resend_email', 'resend_all_failed', 'save_settings', 'save_log_retention',
            'save_telegram_settings', 'send_telegram_test',
        ];
        foreach ( $ajax_actions as $action ) {
            add_action( 'wp_ajax_csmtp_' . $action, array( $this, $action . '_ajax' ) );
        }

        add_filter( 'wp_mail', array( $this, 'log_email_data' ) );
        add_action( 'wp_mail_succeeded', array( $this, 'log_email_success' ) );
        add_action( 'wp_mail_failed', array( $this, 'log_email_failure' ) );

        add_action( 'update_option_csmtp_options', 'csmtp_handle_log_retention_cron_scheduling', 10, 2 );
        add_action( CSMTP_CRON_HOOK, 'csmtp_perform_log_cleanup' );
    }

    /**
     * Prepare assets for the admin page.
     */
    public function prepare_assets( $hook ) {
        if ( $hook === $this->hook_suffix ) {
            wp_enqueue_style( 'csmtp-fontawesome', plugin_dir_url( __FILE__ ) . 'fontawesome/all.min.css', array(), '6.5.2' );
            wp_enqueue_style( 'csmtp-settings-css', plugin_dir_url( __FILE__ ) . 'css/smtp-setting.css', array(), CSMTP_VERSION );
            wp_enqueue_script( 'csmtp-settings-js', plugin_dir_url( __FILE__ ) . 'js/smtp-setting.js', array(), CSMTP_VERSION, true );
            
            $l10n = array(
                'networkError'              => __( 'A network error occurred. Please check your connection and server configuration.', 'cherkasov-smtp-mailer' ),
                'success'                   => __( 'Success!', 'cherkasov-smtp-mailer' ),
                'error'                     => __( 'Error!', 'cherkasov-smtp-mailer' ),
                'confirmDeletionTitle'      => __( 'Confirm Deletion', 'cherkasov-smtp-mailer' ),
                'confirmDeletionMessage'    => __( 'Are you sure you want to delete this log entry? This action cannot be undone.', 'cherkasov-smtp-mailer' ),
                'confirmClearLogsMessage'   => __( 'Are you sure you want to delete all email logs? This action cannot be undone.', 'cherkasov-smtp-mailer' ),
                'errorOccurred'             => __( 'An error occurred.', 'cherkasov-smtp-mailer' ),
                'smtpDebugLog'              => __( 'SMTP Debug Log', 'cherkasov-smtp-mailer' ),
            );
            
            wp_localize_script( 'csmtp-settings-js', 'csmtpMailerData', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'l10n'    => $l10n,
            ) );
        }
    }

    public function plugins_loaded_handler() {
        if ( is_admin() ) {
            $this->verify_log_table_exists();
            if ( current_user_can( 'manage_options' ) ) {
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
            }
        }
    }

    public function verify_log_table_exists() {
        global $wpdb;
        $table_name = CSMTP_LOG_TABLE;
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
            csmtp_create_log_table();
        }
    }

    public function configure_smtp( &$phpmailer ) {
        $options = csmtp_get_option();
        if ( 'smtp' !== $options['mailer'] || empty( $options['smtp_host'] ) ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = $options['smtp_host'];
        $phpmailer->Port       = intval( $options['smtp_port'] );
        $phpmailer->SMTPAuth   = 'true' === $options['smtp_auth'];
        if ( $phpmailer->SMTPAuth ) {
            $phpmailer->Username = $options['smtp_username'];
            $phpmailer->Password = $options['smtp_password'] ?? '';
        }
        $phpmailer->SMTPSecure = 'none' === $options['type_of_encryption'] ? '' : $options['type_of_encryption'];
        if ( 'none' === $options['type_of_encryption'] ) {
            $phpmailer->SMTPAutoTLS = false;
        }
        if ( '1' === $options['disable_ssl_verification'] ) {
            $phpmailer->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
        }
        if ( isset( $GLOBALS['csmtp_test_debug'] ) && true === $GLOBALS['csmtp_test_debug'] ) {
            $phpmailer->SMTPDebug   = 2;
            $phpmailer->Debugoutput = function( $str, $level ) { echo esc_html( gmdate( 'Y-m-d H:i:s' ) . "\t" . str_replace( "\n", '', $str ) . "\n" ); };
        }
        if ( '1' === $options['force_from_address'] && ! empty( $options['from_email'] ) ) {
            $from_name = ! empty( $options['from_name'] ) ? $options['from_name'] : get_bloginfo( 'name' );
            $phpmailer->setFrom( $options['from_email'], $from_name );
        }
    }
    
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="options-general.php?page=csmtp-settings" class="smtp-settings-link">' . esc_html__( 'Settings', 'cherkasov-smtp-mailer' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    public function options_menu() {
        $this->hook_suffix = add_options_page(
            __( 'Cherkasov SMTP Mailer', 'cherkasov-smtp-mailer' ),
            __( 'Cherkasov SMTP Mailer', 'cherkasov-smtp-mailer' ),
            'manage_options',
            'csmtp-settings',
            array( $this, 'csmtp_render_settings_page' ) // <-- ОСЬ ГОЛОВНЕ ВИПРАВЛЕННЯ
        );
    }

    public function log_email_data( $args ) {
        global $wpdb;
        $options = csmtp_get_option();
        $headers = is_array( $args['headers'] ) ? implode( "\r\n", $args['headers'] ) : $args['headers'];
        
        if ( false === stripos( $headers, 'Content-Type' ) ) {
            $headers .= "\r\nContent-Type: text/html; charset=UTF-8";
        }

        $log_data = [
            'to_address' => is_array( $args['to'] ) ? implode( ', ', $args['to'] ) : $args['to'],
            'subject'    => $args['subject'], 'message' => $args['message'], 'headers' => $headers,
            'timestamp'  => current_time( 'mysql' ), 'status' => 'Sending',
            'mailer'     => ('smtp' === $options['mailer'] && !empty( $options['smtp_host'])) ? 'SMTP' : 'WP Mail',
        ];
        $wpdb->insert( CSMTP_LOG_TABLE, $log_data );
        set_transient( 'csmtp_last_log_id', $wpdb->insert_id, 60 );
        return $args;
    }

    public function log_email_success( $mail_data ) {
        $log_id = get_transient( 'csmtp_last_log_id' );
        if ( $log_id ) {
            global $wpdb;
            $wpdb->update( CSMTP_LOG_TABLE, ['status' => 'Sent'], ['id' => $log_id] );
            
            $options = csmtp_get_option();
            if ('1' === $options['telegram_enabled'] && '1' === $options['telegram_notify_success']) {
                $log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . CSMTP_LOG_TABLE . ' WHERE id = %d', $log_id ) );
                if($log){
                    $site_url = site_url();
                    $subject = $log->subject;
                    $to = $log->to_address;
                    $message = "✅ *Email Sent Successfully*\n\nAn email was sent from *{$site_url}*.\n\n*To:* `{$to}`\n*Subject:* `{$subject}`";
                    csmtp_send_telegram_message($message);
                }
            }
            delete_transient( 'csmtp_last_log_id' );
        }
    }

    public function log_email_failure( $wp_error ) {
        $log_id = get_transient( 'csmtp_last_log_id' );
        if ( $log_id ) {
            global $wpdb;
            $error_message = is_wp_error($wp_error) ? $wp_error->get_error_message() : strval($wp_error);
            $log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . CSMTP_LOG_TABLE . ' WHERE id = %d', $log_id ) );
            if ($log) {
                $wpdb->update(CSMTP_LOG_TABLE, ['status' => 'Failed', 'error_message' => $error_message], ['id' => $log_id]);
                $options = csmtp_get_option();
                if ('1' === $options['telegram_enabled'] && '1' === $options['telegram_notify_failed']) {
                    $site_url = site_url();
                    $subject = $log->subject;
                    $to = $log->to_address;
                    $message = "🔴 *Email Send Failure*\n\nAn email failed to send from *{$site_url}*.\n\n*To:* `{$to}`\n*Subject:* `{$subject}`\n\n*Error:* `{$error_message}`";
                    csmtp_send_telegram_message($message);
                }
            }
            delete_transient( 'csmtp_last_log_id' );
        }
    }
    
    private function check_ajax_permission($nonce_action) {
        check_ajax_referer($nonce_action, '_ajax_nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'cherkasov-smtp-mailer')], 403);
        }
    }

    public function get_logs_ajax() {
        $this->check_ajax_permission('csmtp_get_logs_nonce');
        global $wpdb;
        $table_name = CSMTP_LOG_TABLE;
        $per_page = 20;
        $current_page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $offset = ($current_page - 1) * $per_page;
        $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $where_clauses = [];
        $query_params = [];
        if (!empty($search_term)) {
            $like_term = '%' . $wpdb->esc_like($search_term) . '%';
            $where_clauses[] = "(to_address LIKE %s OR subject LIKE %s)";
            $query_params[] = $like_term;
            $query_params[] = $like_term;
        }
        $where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);
        $total_items_sql = "SELECT COUNT(id) FROM `$table_name` " . $where_sql;
        $total_items = $wpdb->get_var($wpdb->prepare($total_items_sql, $query_params));
        $logs_sql = "SELECT id, timestamp, to_address, subject, status, mailer FROM `$table_name` " . $where_sql . " ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        $logs_query_params = array_merge($query_params, [$per_page, $offset]);
        $logs = $wpdb->get_results($wpdb->prepare($logs_sql, $logs_query_params));
        foreach ($logs as &$log) {
            $log->formatted_date = wp_date( 'F j, Y, g:i a', strtotime( $log->timestamp ) );
        }
        unset($log);
        ob_start();
        if (empty($logs)) {
                echo '<div class="empty-log-state"><i class="fa-solid fa-envelope-open empty-log-icon"></i><h3 class="empty-log-title">' . (!empty($search_term) ? esc_html__('No Results Found', 'cherkasov-smtp-mailer') : esc_html__('No Emails Logged Yet', 'cherkasov-smtp-mailer')) . '</h3><p class="empty-log-text">' . (!empty($search_term) ? esc_html__('Try a different search term.', 'cherkasov-smtp-mailer') : esc_html__('When your site sends an email, it will appear here.', 'cherkasov-smtp-mailer')) . '</p></div>';
        } else {
            echo '<div class="log-list">';
            foreach ($logs as $log) {
                $status_icon = ('Sent' === $log->status) ? '<i class="fa-solid fa-circle-check log-status-icon-sent"></i>' : '<i class="fa-solid fa-circle-xmark log-status-icon-failed"></i>';
                $mailer_class = ('SMTP' === $log->mailer) ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-700 border-slate-200';
                echo '<div class="log-item"><div class="log-status-icon">' . $status_icon . '</div><div class="log-content"><strong class="log-subject">' . esc_html($log->subject) . '</strong><span class="log-recipient">' . esc_html__('To:', 'cherkasov-smtp-mailer') . ' ' . esc_html($log->to_address) . '</span></div><div class="log-meta"><time class="log-time" datetime="' . esc_attr($log->timestamp) . '">' . esc_html($log->formatted_date) . '</time><span class="log-badge ' . esc_attr($mailer_class) . '">' . esc_html($log->mailer) . '</span><button type="button" class="btn-resend-log" data-log-id="' . esc_attr($log->id) . '" data-nonce="' . esc_attr(wp_create_nonce('csmtp_resend_email_nonce')) . '" title="' . esc_attr__('Resend Email', 'cherkasov-smtp-mailer') . '"><i class="fa-solid fa-rotate-right"></i></button><button type="button" class="btn-delete-log" data-log-id="' . esc_attr($log->id) . '" data-nonce="' . esc_attr(wp_create_nonce('csmtp_delete_log_nonce')) . '" title="' . esc_attr__('Delete Log', 'cherkasov-smtp-mailer') . '"><i class="fa-solid fa-trash-can"></i></button><button type="button" class="btn-view-log" data-log-id="' . esc_attr($log->id) . '">' . esc_html__('View', 'cherkasov-smtp-mailer') . '<i class="fa-solid fa-chevron-right"></i></button></div></div>';
            }
            echo '</div>';
        }
        $html = ob_get_clean();
        $pagination_html = paginate_links(['base' => add_query_arg('paged', '%#%'), 'format' => '?paged=%#%', 'current' => $current_page, 'total' => ceil($total_items / $per_page), 'prev_text' => '&laquo;', 'next_text' => '&raquo;', 'type' => 'array']);
        wp_send_json_success(['html' => $html, 'pagination_html' => $pagination_html]);
    }
    
    public function get_log_details_ajax() {
        $this->check_ajax_permission('csmtp_get_log_details_nonce');
        global $wpdb;
        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . CSMTP_LOG_TABLE . " WHERE id = %d", $log_id));
        if ($log) {
            $log->formatted_date = wp_date( 'F j, Y \a\t g:i a', strtotime( $log->timestamp ) );
            $log->headers = esc_html($log->headers);
            $log->error_message = esc_html($log->error_message);
            wp_send_json_success($log);
        } else {
            wp_send_json_error(['message' => __('Log not found.', 'cherkasov-smtp-mailer')], 404);
        }
    }

    public function send_test_email_ajax() {
        $this->check_ajax_permission('csmtp_send_test_email_nonce');
        $to = isset($_POST['to_email']) ? sanitize_email(wp_unslash($_POST['to_email'])) : '';
        if ( !is_email($to) ) {
            wp_send_json_error(['message' => __('Recipient email is invalid.', 'cherkasov-smtp-mailer')]);
        }
        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';
        
        $GLOBALS['csmtp_test_debug'] = true;
        ob_start();
        $sent = wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        $debug_output = ob_get_clean();
        unset($GLOBALS['csmtp_test_debug']);

        if ($sent) {
            wp_send_json_success(['message' => __('Test email sent successfully!', 'cherkasov-smtp-mailer'), 'debug' => $debug_output]);
        } else {
            global $phpmailer;
            $error_info = isset($phpmailer) && is_object($phpmailer) ? $phpmailer->ErrorInfo : __('Unknown error.', 'cherkasov-smtp-mailer');
            wp_send_json_error(['message' => __('Test email failed to send: ', 'cherkasov-smtp-mailer') . $error_info, 'debug' => $debug_output]);
        }
    }

    public function clear_logs_ajax() {
        $this->check_ajax_permission('csmtp_clear_logs_nonce');
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE `" . CSMTP_LOG_TABLE . "`");
        if ($wpdb->last_error) {
            wp_send_json_error(['message' => __('Failed to clear logs.', 'cherkasov-smtp-mailer') . ' ' . $wpdb->last_error], 500);
        } else {
            wp_send_json_success(['message' => __('Email logs cleared successfully.', 'cherkasov-smtp-mailer')]);
        }
    }

    public function delete_log_ajax() {
        $this->check_ajax_permission('csmtp_delete_log_nonce');
        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        if ( !$log_id ) {
            wp_send_json_error(['message' => __('Invalid Log ID.', 'cherkasov-smtp-mailer')], 400);
        }
        global $wpdb;
        $result = $wpdb->delete(CSMTP_LOG_TABLE, ['id' => $log_id], ['%d']);
        if (false === $result) {
            wp_send_json_error(['message' => __('Failed to delete log entry.', 'cherkasov-smtp-mailer')], 500);
        } else {
            wp_send_json_success(['message' => __('Log entry deleted successfully.', 'cherkasov-smtp-mailer')]);
        }
    }

    public function resend_email_ajax() {
        $this->check_ajax_permission('csmtp_resend_email_nonce');
        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        if (!$log_id) {
            wp_send_json_error(['message' => __('Invalid Log ID.', 'cherkasov-smtp-mailer')]);
        }
        global $wpdb;
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . CSMTP_LOG_TABLE . " WHERE id = %d", $log_id));
        if (!$log) {
            wp_send_json_error(['message' => __('Log entry not found.', 'cherkasov-smtp-mailer')], 404);
        }
        
        $headers = [];
        if (!empty($log->headers)) {
                $headers = explode("\r\n", $log->headers);
        }

        $sent = wp_mail($log->to_address, $log->subject, $log->message, $headers);

        if ($sent) {
            wp_send_json_success(['message' => __('Email resent successfully!', 'cherkasov-smtp-mailer')]);
        } else {
            global $phpmailer;
            $error_info = isset($phpmailer) && is_object($phpmailer) ? $phpmailer->ErrorInfo : __('Unknown error.', 'cherkasov-smtp-mailer');
            wp_send_json_error(['message' => __('Failed to resend the email: ', 'cherkasov-smtp-mailer') . $error_info]);
        }
    }
    
    public function resend_all_failed_ajax() {
        $this->check_ajax_permission('csmtp_resend_all_failed_nonce');
        global $wpdb;
        $failed_logs = $wpdb->get_results("SELECT * FROM " . CSMTP_LOG_TABLE . " WHERE status = 'Failed'");
        if (empty($failed_logs)) {
            wp_send_json_success(['message' => __('No failed emails to resend.', 'cherkasov-smtp-mailer')]);
            return;
        }

        $success_count = 0;
        $fail_count = 0;

        foreach ($failed_logs as $log) {
            $headers = !empty($log->headers) ? explode("\r\n", $log->headers) : [];
            if (wp_mail($log->to_address, $log->subject, $log->message, $headers)) {
                $success_count++;
            } else {
                $fail_count++;
            }
        }

        $message = sprintf(__('Resend process completed. Successfully resent: %1$d. Failed: %2$d.', 'cherkasov-smtp-mailer'), $success_count, $fail_count);
        wp_send_json_success(['message' => $message]);
    }
    
    public function save_settings_ajax() {
        $this->check_ajax_permission('csmtp_save_settings_nonce');
        $options = csmtp_get_option();

        $options['mailer']                   = isset($_POST['mailer']) ? sanitize_key($_POST['mailer']) : 'smtp';
        $options['smtp_host']                = isset($_POST['smtp_host']) ? sanitize_text_field(wp_unslash($_POST['smtp_host'])) : '';
        $options['smtp_port']                = isset($_POST['smtp_port']) ? sanitize_text_field(wp_unslash($_POST['smtp_port'])) : '587';
        $options['type_of_encryption']       = isset($_POST['type_of_encryption']) ? sanitize_key($_POST['type_of_encryption']) : 'tls';
        $options['smtp_auth']                = isset($_POST['smtp_auth']) && 'true' === $_POST['smtp_auth'] ? 'true' : 'false';
        $options['smtp_username']            = isset($_POST['smtp_username']) ? sanitize_text_field(wp_unslash($_POST['smtp_username'])) : '';
        $options['from_name']                = isset($_POST['from_name']) ? sanitize_text_field(wp_unslash($_POST['from_name'])) : '';
        $options['from_email']               = isset($_POST['from_email']) ? sanitize_email(wp_unslash($_POST['from_email'])) : '';
        $options['force_from_address']       = isset($_POST['force_from_address']) && '1' === $_POST['force_from_address'] ? '1' : '0';
        $options['disable_ssl_verification'] = isset($_POST['disable_ssl_verification']) && '1' === $_POST['disable_ssl_verification'] ? '1' : '0';

        if (isset($_POST['smtp_password']) && !empty($_POST['smtp_password'])) {
            $options['smtp_password'] = sanitize_text_field( wp_unslash( $_POST['smtp_password'] ) );
        }

        update_option('csmtp_options', $options);
        wp_send_json_success(['message' => __('Settings saved successfully!', 'cherkasov-smtp-mailer')]);
    }

    public function save_log_retention_ajax() {
        $this->check_ajax_permission('csmtp_save_log_retention_nonce');
        $options = csmtp_get_option();
        $new_period = isset($_POST['log_retention_period']) ? sanitize_key($_POST['log_retention_period']) : 'never';
        
        if (!in_array($new_period, ['never', '1', '7', '30', '365'], true)) {
            wp_send_json_error(['message' => __('Invalid log retention period specified.', 'cherkasov-smtp-mailer')]);
        }
        
        $options['log_retention_period'] = $new_period;
        update_option('csmtp_options', $options);
        wp_send_json_success(['message' => __('Log retention settings saved.', 'cherkasov-smtp-mailer')]);
    }
    
    public function save_telegram_settings_ajax() {
        $this->check_ajax_permission('csmtp_save_telegram_settings_nonce');
        $options = csmtp_get_option();

        $options['telegram_enabled'] = isset($_POST['telegram_enabled']) && '1' === $_POST['telegram_enabled'] ? '1' : '0';
        $options['telegram_notify_failed'] = isset($_POST['telegram_notify_failed']) && '1' === $_POST['telegram_notify_failed'] ? '1' : '0';
        $options['telegram_notify_success'] = isset($_POST['telegram_notify_success']) && '1' === $_POST['telegram_notify_success'] ? '1' : '0';
        $options['telegram_chat_id'] = isset($_POST['telegram_chat_id']) ? sanitize_text_field(wp_unslash($_POST['telegram_chat_id'])) : '';
        
        if (isset($_POST['telegram_bot_token']) && !empty($_POST['telegram_bot_token'])) {
            $options['telegram_bot_token'] = sanitize_text_field(wp_unslash($_POST['telegram_bot_token']));
        }

        update_option('csmtp_options', $options);
        wp_send_json_success(['message' => __('Telegram settings saved successfully!', 'cherkasov-smtp-mailer')]);
    }

    public function send_telegram_test_ajax() {
        $this->check_ajax_permission('csmtp_send_telegram_test_nonce');
        $site_url = esc_html(site_url());
        $message = "✅ *Test Message*\n\nThis is a test message from Cherkasov SMTP Mailer on your website *{$site_url}*.\n\nYour Telegram notifications are configured correctly!";
        $result = csmtp_send_telegram_message($message);
        if ($result['success']) {
            wp_send_json_success(['message' => __('Test message sent successfully to Telegram!', 'cherkasov-smtp-mailer')]);
        } else {
            wp_send_json_error(['message' => __('Failed to send test message:', 'cherkasov-smtp-mailer') . ' ' . $result['error']]);
        }
    }

    // --- UI Rendering Methods ---

    public function csmtp_render_settings_page() {
        ?>
        <div class="smtp-mailer-wrap">
            <div class="p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto">
                <header class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-800"><?php esc_html_e( 'Cherkasov SMTP Mailer', 'cherkasov-smtp-mailer' ); ?></h1>
                        <p class="text-sm text-slate-500 mt-1">v<?php echo esc_html( CSMTP_VERSION ); ?> - <?php esc_html_e( 'A modern way to handle WordPress emails.', 'cherkasov-smtp-mailer' ); ?></p>
                    </div>
                    <div class="header-buttons">
                            <button type="button" class="js-open-modal btn btn-secondary" data-modal-id="telegram-settings-modal">
                                <i class="fa-brands fa-telegram -ml-1 mr-2 text-sky-500"></i>
                                <?php esc_html_e( 'Telegram Notifications', 'cherkasov-smtp-mailer' ); ?>
                            </button>
                            <button type="button" class="js-open-modal btn btn-secondary" data-modal-id="email-log-list-modal">
                                <i class="fa-solid fa-clipboard-list -ml-1 mr-2"></i>
                                <?php esc_html_e( 'Email Logs', 'cherkasov-smtp-mailer' ); ?>
                            </button>
                            <button type="button" class="js-open-modal btn btn-primary" data-modal-id="test-email-modal">
                                <i class="fa-solid fa-paper-plane -ml-1 mr-2"></i>
                                <?php esc_html_e( 'Send Test Email', 'cherkasov-smtp-mailer' ); ?>
                            </button>
                    </div>
                </header>
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-slate-200">
                    <?php $this->csmtp_ui_general_settings(); ?>
                </div>
            </div>
            <?php $this->csmtp_ui_render_modals(); ?>
            <?php $this->csmtp_ui_notification_popup(); ?>
        </div>
        <?php
    }

    public function csmtp_ui_general_settings() {
        $options = csmtp_get_option();
        ?>
        <form id="csmtp-settings-form">
            <div class="smtp-mailer-settings-form">
                <div class="mb-8">
                    <label class="form-label mb-2"><?php esc_html_e( 'Mailer', 'cherkasov-smtp-mailer' ); ?></label>
                    <input type="hidden" name="mailer" id="mailer_type" value="<?php echo esc_attr( $options['mailer'] ); ?>">
                    <div id="mailer-type-control" class="segmented-control">
                        <span class="glider"></span>
                        <div class="segmented-control-button <?php if ( 'smtp' === $options['mailer'] ) echo 'active'; ?>" data-value="smtp"><i class="fa-solid fa-server mr-2"></i>SMTP</div>
                        <div class="segmented-control-button <?php if ( 'default' === $options['mailer'] ) echo 'active'; ?>" data-value="default"><i class="fa-brands fa-wordpress-simple mr-2"></i><?php esc_html_e('Default WP Mailer', 'cherkasov-smtp-mailer'); ?></div>
                    </div>
                </div>

                <div id="wp-mailer-info-state" class="wp-mailer-info-state" style="display: none;">
                    <i class="fa-solid fa-info-circle wp-mailer-info-icon"></i>
                    <h3 class="wp-mailer-info-title"><?php esc_html_e('Default Mailer is Active', 'cherkasov-smtp-mailer'); ?></h3>
                    <p class="wp-mailer-info-text"><?php esc_html_e('Emails are being sent via your web host. For better reliability and deliverability, we recommend switching to SMTP.', 'cherkasov-smtp-mailer'); ?></p>
                </div>

                <div id="smtp-settings-fields-container" class="grid grid-cols-1 lg:grid-cols-5 gap-x-12 gap-y-10">
                    <div class="lg:col-span-3 space-y-8">
                        <div class="card !p-0 !shadow-none !border-none">
                            <div class="mb-8">
                                <label class="form-label mb-2"><?php esc_html_e( 'Common Providers', 'cherkasov-smtp-mailer' ); ?></label>
                                <div id="provider-control" class="segmented-control">
                                    <span class="glider"></span>
                                    <div class="segmented-control-button" data-value="gmail"><i class="fa-brands fa-google"></i>Gmail</div>
                                    <div class="segmented-control-button" data-value="outlook"><i class="fa-brands fa-microsoft"></i>Outlook</div>
                                    <div class="segmented-control-button" data-value="sendgrid"><i class="fa-solid fa-paper-plane"></i>SendGrid</div>
                                    <div class="segmented-control-button active" data-value="custom"><?php esc_html_e( 'Other', 'cherkasov-smtp-mailer' ); ?></div>
                                </div>
                            </div>
                            <h3 class="card-title"><?php esc_html_e( 'Mail Server', 'cherkasov-smtp-mailer' ); ?></h3>
                            <p class="card-description"><?php esc_html_e( 'Specify your outgoing mail server (SMTP) details.', 'cherkasov-smtp-mailer' ); ?></p>
                            <div class="space-y-6">
                                <div class="grid sm:grid-cols-2 gap-6">
                                    <div>
                                        <label for="csmtp_host" class="form-label"><?php esc_html_e( 'SMTP Host', 'cherkasov-smtp-mailer' ); ?></label>
                                        <input type="text" name="smtp_host" id="csmtp_host" class="form-input" placeholder="smtp.example.com" value="<?php echo esc_attr( $options['smtp_host'] ); ?>">
                                    </div>
                                    <div>
                                        <label for="csmtp_port" class="form-label"><?php esc_html_e( 'SMTP Port', 'cherkasov-smtp-mailer' ); ?></label>
                                        <input type="text" name="smtp_port" id="csmtp_port" class="form-input" placeholder="587" value="<?php echo esc_attr( $options['smtp_port'] ); ?>">
                                        <div class="port-suggestions">
                                            <button type="button" class="port-suggestion-btn" data-port="25">25</button>
                                            <button type="button" class="port-suggestion-btn" data-port="465">465</button>
                                            <button type="button" class="port-suggestion-btn" data-port="587">587</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-6 items-center">
                                    <div>
                                        <label class="form-label mb-2"><?php esc_html_e( 'Encryption', 'cherkasov-smtp-mailer' ); ?></label>
                                        <input type="hidden" name="type_of_encryption" id="type_of_encryption" value="<?php echo esc_attr( $options['type_of_encryption'] ); ?>">
                                        <div id="encryption-control" class="segmented-control">
                                            <span class="glider"></span>
                                            <div class="segmented-control-button <?php if ( 'tls' === $options['type_of_encryption'] ) echo 'active'; ?>" data-value="tls">TLS</div>
                                            <div class="segmented-control-button <?php if ( 'ssl' === $options['type_of_encryption'] ) echo 'active'; ?>" data-value="ssl">SSL</div>
                                            <div class="segmented-control-button <?php if ( 'none' === $options['type_of_encryption'] ) echo 'active'; ?>" data-value="none"><?php esc_html_e( 'None', 'cherkasov-smtp-mailer' ); ?></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-5 sm:pt-0">
                                        <label for="csmtp_auth_toggle" class="form-label"><?php esc_html_e( 'Authentication', 'cherkasov-smtp-mailer' ); ?></label>
                                        <input type="hidden" name="smtp_auth" id="csmtp_auth" value="<?php echo esc_attr( $options['smtp_auth'] ); ?>">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="csmtp_auth_toggle" <?php checked( $options['smtp_auth'], 'true' ); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                             <h3 class="card-title"><?php esc_html_e( 'SMTP Credentials', 'cherkasov-smtp-mailer' ); ?></h3>
                            <div class="mt-6 space-y-6">
                                <div>
                                    <label for="csmtp_username" class="form-label"><?php esc_html_e( 'SMTP Username', 'cherkasov-smtp-mailer' ); ?></label>
                                    <input type="text" name="smtp_username" id="csmtp_username" autocomplete="username" class="form-input" value="<?php echo esc_attr( $options['smtp_username'] ); ?>">
                                </div>
                                <div>
                                    <label for="csmtp_password" class="form-label"><?php esc_html_e( 'SMTP Password', 'cherkasov-smtp-mailer' ); ?></label>
                                    <div class="mt-1 relative">
                                        <input type="password" name="smtp_password" id="csmtp_password" autocomplete="new-password" class="form-input pr-10" value="" placeholder="<?php echo !empty($options['smtp_password']) ? '••••••••••••' : esc_attr__('Enter SMTP Password', 'cherkasov-smtp-mailer'); ?>">
                                        <button type="button" class="password-toggle-btn">
                                            <i class="fa-solid fa-eye eye-icon"></i>
                                            <i class="fa-solid fa-eye-slash eye-off-icon hidden"></i>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500"><?php esc_html_e( 'Leave empty to keep the current password.', 'cherkasov-smtp-mailer' ); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-2 space-y-8">
                         <div class="card">
                             <h3 class="card-title"><?php esc_html_e( 'Sender Details', 'cherkasov-smtp-mailer' ); ?></h3>
                            <div class="mt-6 space-y-6">
                                <div>
                                    <label for="csmtp_from_name" class="form-label"><?php esc_html_e( 'From Name', 'cherkasov-smtp-mailer' ); ?></label>
                                    <input type="text" name="from_name" id="csmtp_from_name" class="form-input" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" value="<?php echo esc_attr( $options['from_name'] ); ?>">
                                </div>
                                <div>
                                    <label for="csmtp_from_email" class="form-label"><?php esc_html_e( 'From Email', 'cherkasov-smtp-mailer' ); ?></label>
                                    <input type="email" name="from_email" id="csmtp_from_email" class="form-input" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" value="<?php echo esc_attr( $options['from_email'] ); ?>">
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm"><?php esc_html_e( 'Force Sender', 'cherkasov-smtp-mailer' ); ?></p>
                                        <p class="text-xs text-slate-500 mt-1"><?php esc_html_e( 'Override From Name & Email.', 'cherkasov-smtp-mailer' ); ?></p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input id="csmtp_force_from_address" name="force_from_address" type="checkbox" value="1" <?php checked( $options['force_from_address'], '1' ); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                         <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-amber-900 text-sm"><?php esc_html_e( 'Disable SSL Verification', 'cherkasov-smtp-mailer' ); ?></p>
                                    <p class="text-xs text-amber-700 mt-1"><?php esc_html_e( 'Not recommended. Use only for local dev.', 'cherkasov-smtp-mailer' ); ?></p>
                                </div>
                                <label class="toggle-switch">
                                    <input id="csmtp_disable_ssl_verification" name="disable_ssl_verification" type="checkbox" value="1" <?php checked( $options['disable_ssl_verification'], '1' ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 sm:px-8 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" id="csmtp-save-settings-button" class="btn btn-primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_save_settings_nonce' ) ); ?>">
                    <i class="fa-solid fa-save mr-2 -ml-1"></i>
                    <span class="button-text"><?php esc_html_e( 'Save Changes', 'cherkasov-smtp-mailer' ); ?></span>
                    <span class="spinner hidden"></span>
                </button>
            </div>
        </form>
        <?php
    }

    public function csmtp_ui_render_modals() {
        ?>
        <div id="test-email-modal" class="smtp-modal">
            <div class="modal-overlay js-close-modal"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title"><?php esc_html_e( 'Send a Test Email', 'cherkasov-smtp-mailer' ); ?></h3>
                    <button type="button" class="modal-close-btn js-close-modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <?php $this->csmtp_ui_test_email_form(); ?>
            </div>
        </div>

        <div id="telegram-settings-modal" class="smtp-modal">
            <div class="modal-overlay js-close-modal"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fa-brands fa-telegram text-sky-500 mr-2"></i><?php esc_html_e( 'Telegram Notifications', 'cherkasov-smtp-mailer' ); ?></h3>
                    <button type="button" class="modal-close-btn js-close-modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <?php $this->csmtp_ui_telegram_settings_form(); ?>
            </div>
        </div>

        <div id="email-log-list-modal" class="smtp-modal">
            <div class="modal-overlay js-close-modal"></div>
            <div class="modal-container modal-xl">
                <div class="modal-header">
                    <div class="flex items-center gap-4 flex-grow">
                        <h3 class="modal-title"><?php esc_html_e( 'Email Log', 'cherkasov-smtp-mailer' ); ?></h3>
                        <div class="search-input-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="search" id="log-search-input" placeholder="<?php esc_attr_e( 'Search by email or subject...', 'cherkasov-smtp-mailer' ); ?>" class="form-input !mt-0">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" id="log-settings-toggle-button" class="btn btn-secondary btn-sm" title="<?php esc_attr_e( 'Log Settings', 'cherkasov-smtp-mailer' ); ?>">
                            <i class="fa-solid fa-gear"></i>
                        </button>
                        <button type="button" id="resend-failed-logs-button" class="btn btn-secondary btn-sm" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_resend_all_failed_nonce' ) ); ?>" title="<?php esc_attr_e( 'Resend Failed Emails', 'cherkasov-smtp-mailer' ); ?>">
                            <span class="button-text"><i class="fa-solid fa-arrows-rotate"></i></span>
                            <span class="spinner hidden"></span>
                        </button>
                        <button type="button" id="clear-logs-button" class="btn btn-danger-outline btn-sm" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_clear_logs_nonce' ) ); ?>" title="<?php esc_attr_e( 'Clear All Logs', 'cherkasov-smtp-mailer' ); ?>">
                            <span class="button-text"><i class="fa-solid fa-trash-can"></i></span>
                             <span class="spinner hidden"></span>
                        </button>
                        <button type="button" class="modal-close-btn js-close-modal">
                           <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div style="flex: 1; overflow-y: auto; min-height: 0;">
                    <div id="email-log-settings-container" class="hidden p-6 border-b border-slate-200 bg-slate-50">
                        <?php
                        $options = csmtp_get_option();
                        $log_retention = $options['log_retention_period'] ?? 'never';
                        ?>
                        <h4 class="card-title mb-4"><?php esc_html_e( 'Log Management', 'cherkasov-smtp-mailer' ); ?></h4>
                        <label class="form-label mb-2"><?php esc_html_e( 'Automatically delete logs older than:', 'cherkasov-smtp-mailer' ); ?></label>
                        <input type="hidden" id="modal_log_retention_period" value="<?php echo esc_attr( $log_retention ); ?>">
                        <div id="modal-log-retention-control" class="segmented-control">
                            <span class="glider"></span>
                            <div class="segmented-control-button <?php if ( 'never' === $log_retention ) echo 'active'; ?>" data-value="never"><?php esc_html_e( 'Never', 'cherkasov-smtp-mailer' ); ?></div>
                            <div class="segmented-control-button <?php if ( '1' === $log_retention ) echo 'active'; ?>" data-value="1"><?php esc_html_e( '1 Day', 'cherkasov-smtp-mailer' ); ?></div>
                            <div class="segmented-control-button <?php if ( '7' === $log_retention ) echo 'active'; ?>" data-value="7"><?php esc_html_e( '1 Week', 'cherkasov-smtp-mailer' ); ?></div>
                            <div class="segmented-control-button <?php if ( '30' === $log_retention ) echo 'active'; ?>" data-value="30"><?php esc_html_e( '1 Month', 'cherkasov-smtp-mailer' ); ?></div>
                            <div class="segmented-control-button <?php if ( '365' === $log_retention ) echo 'active'; ?>" data-value="365"><?php esc_html_e( '1 Year', 'cherkasov-smtp-mailer' ); ?></div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="button" id="save-log-retention-button" class="btn btn-primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_save_log_retention_nonce' ) ); ?>">
                                <span class="button-text"><?php esc_html_e( 'Save Log Settings', 'cherkasov-smtp-mailer' ); ?></span>
                                <span class="spinner hidden"></span>
                            </button>
                        </div>
                    </div>
                    <div id="email-log-list-content" class="p-2" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_get_logs_nonce' ) ); ?>">
                        <div class="loading-spinner-container"><div class="spinner"></div></div>
                    </div>
                </div>
                <div id="email-log-list-pagination" class="log-pagination-container"></div>
            </div>
        </div>

        <div id="email-log-detail-modal" class="smtp-modal">
            <div class="modal-overlay js-close-modal"></div>
            <div class="modal-container modal-xl !p-0">
                <div class="log-detail-layout">
                    <div class="log-detail-sidebar">
                        <div class="flex justify-between items-center mb-6">
                             <h3 class="modal-title" id="log-detail-subject"><?php esc_html_e( 'Email Details', 'cherkasov-smtp-mailer' ); ?></h3>
                            <button type="button" class="modal-close-btn js-close-modal">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <dl class="log-detail-grid" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_get_log_details_nonce' ) ); ?>">
                            <div class="log-detail-item">
                                <dt><i class="fa-solid fa-envelope"></i><span><?php esc_html_e( 'To', 'cherkasov-smtp-mailer' ); ?></span></dt>
                                <dd id="log-detail-to"></dd>
                            </div>
                            <div class="log-detail-item">
                                <dt><i class="fa-solid fa-clock"></i><span><?php esc_html_e( 'Date', 'cherkasov-smtp-mailer' ); ?></span></dt>
                                <dd id="log-detail-date"></dd>
                            </div>
                            <div class="log-detail-item">
                                <dt><i class="fa-solid fa-paper-plane"></i><span><?php esc_html_e( 'Mailer', 'cherkasov-smtp-mailer' ); ?></span></dt>
                                <dd id="log-detail-mailer"></dd>
                            </div>
                            <div class="log-detail-item">
                                 <dt><i class="fa-solid fa-circle-info"></i><span><?php esc_html_e( 'Status', 'cherkasov-smtp-mailer' ); ?></span></dt>
                                <dd id="log-detail-status"></dd>
                            </div>
                            <div class="mt-2">
                                 <details class="log-tech-details">
                                     <summary><?php esc_html_e( 'Technical Details', 'cherkasov-smtp-mailer' ); ?></summary>
                                    <div class="log-tech-details-content space-y-4">
                                        <dl>
                                            <dt class="text-xs font-semibold uppercase text-slate-500 mb-1"><?php esc_html_e( 'Headers', 'cherkasov-smtp-mailer' ); ?></dt>
                                            <dd><pre id="log-detail-headers"></pre></dd>
                                        </dl>
                                        <dl id="log-detail-error-container" style="display: none;">
                                            <dt class="text-xs font-semibold uppercase text-slate-500 mb-1"><?php esc_html_e( 'Error Message', 'cherkasov-smtp-mailer' ); ?></dt>
                                            <dd><pre id="log-detail-error" class="!bg-red-800 !text-red-100"></pre></dd>
                                        </dl>
                                    </div>
                                </details>
                            </div>
                        </dl>
                    </div>
                    <div class="log-detail-body-wrapper">
                        <iframe id="log-detail-iframe" src="about:blank" class="log-visual-iframe"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div id="confirmation-modal" class="smtp-modal">
            <div class="modal-overlay js-close-modal"></div>
            <div class="modal-container" style="max-width: 28rem;">
                 <div class="modal-header">
                    <h3 class="modal-title" id="confirmation-modal-title"></h3>
                    <button type="button" class="modal-close-btn js-close-modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body-padded">
                    <p id="confirmation-modal-message" class="text-sm text-slate-600"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary js-close-modal"><?php esc_html_e( 'Cancel', 'cherkasov-smtp-mailer' ); ?></button>
                    <button type="button" id="confirmation-modal-confirm-btn" class="btn btn-danger"><?php esc_html_e( 'Confirm', 'cherkasov-smtp-mailer' ); ?></button>
                </div>
            </div>
        </div>
        <?php
    }

    public function csmtp_ui_telegram_settings_form() {
        $options = csmtp_get_option();
        ?>
        <form id="csmtp-telegram-settings-form" method="post" action="" class="flex flex-col flex-1 min-h-0">
            <div class="modal-body-padded space-y-6">
                <div id="telegram-test-results"></div>
                
                <div class="form-toggle-row">
                    <div class="label-group">
                        <label for="telegram_enabled_toggle" class="form-label !mb-0"><?php esc_html_e( 'Enable Telegram Notifications', 'cherkasov-smtp-mailer' ); ?></label>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="telegram_enabled_toggle" name="telegram_enabled" value="1" <?php checked( $options['telegram_enabled'], '1' ); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div>
                    <label for="telegram_bot_token" class="form-label"><?php esc_html_e( 'Bot Token', 'cherkasov-smtp-mailer' ); ?></label>
                    <div class="relative mt-1">
                        <input type="password" name="telegram_bot_token" id="telegram_bot_token" class="form-input pr-10" value="" autocomplete="new-password" placeholder="<?php echo !empty($options['telegram_bot_token']) ? '••••••••••••' : esc_attr__('Enter your Telegram Bot Token', 'cherkasov-smtp-mailer'); ?>">
                        <button type="button" class="password-toggle-btn">
                            <i class="fa-solid fa-eye eye-icon"></i>
                            <i class="fa-solid fa-eye-slash eye-off-icon hidden"></i>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">
                        <?php
                        printf(
                            wp_kses(
                                __( 'Talk to <a href="%s" target="_blank" class="text-indigo-600 hover:underline font-medium">@BotFather</a> to create a new bot and get your token.', 'cherkasov-smtp-mailer' ),
                                [ 'a' => [ 'href' => true, 'target' => true, 'class' => true ] ]
                            ),
                            'https://t.me/botfather'
                        );
                        ?>
                    </p>
                </div>
                <div>
                    <label for="telegram_chat_id" class="form-label"><?php esc_html_e( 'Chat ID', 'cherkasov-smtp-mailer' ); ?></label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" class="form-input" value="<?php echo esc_attr( $options['telegram_chat_id'] ); ?>" placeholder="<?php esc_attr_e( 'Enter your Telegram Chat ID', 'cherkasov-smtp-mailer' ); ?>">
                    <p class="mt-2 text-sm text-slate-500">
                        <?php
                        printf(
                            wp_kses(
                                __( 'Get your Chat ID by talking to <a href="%s" target="_blank" class="text-indigo-600 hover:underline font-medium">@userinfobot</a>.', 'cherkasov-smtp-mailer' ),
                                [ 'a' => [ 'href' => true, 'target' => true, 'class' => true ] ]
                            ),
                            'https://t.me/userinfobot'
                        );
                        ?>
                    </p>
                </div>

                <fieldset class="border-t border-slate-200 pt-6">
                    <legend class="text-base font-semibold text-slate-900 mb-4"><?php esc_html_e( 'Notification Events', 'cherkasov-smtp-mailer' ); ?></legend>
                    <div class="space-y-4">
                        <div class="form-toggle-row !bg-white !p-0 !border-none">
                            <div class="label-group">
                                <label for="telegram_notify_failed_toggle" class="form-label"><?php esc_html_e( 'Failed Emails', 'cherkasov-smtp-mailer' ); ?></label>
                                <p class="label-description"><?php esc_html_e( 'Send a notification when an email fails to send.', 'cherkasov-smtp-mailer' ); ?></p>
                            </div>
                            <label class="toggle-switch">
                                <input id="telegram_notify_failed_toggle" name="telegram_notify_failed" type="checkbox" value="1" <?php checked($options['telegram_notify_failed'], '1'); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                         <div class="form-toggle-row !bg-white !p-0 !border-none">
                            <div class="label-group">
                                <label for="telegram_notify_success_toggle" class="form-label"><?php esc_html_e( 'Successful Emails', 'cherkasov-smtp-mailer' ); ?></label>
                                <p class="label-description"><?php esc_html_e( 'Send a notification on success. (Can be noisy)', 'cherkasov-smtp-mailer' ); ?></p>
                            </div>
                            <label class="toggle-switch">
                                <input id="telegram_notify_success_toggle" name="telegram_notify_success" type="checkbox" value="1" <?php checked($options['telegram_notify_success'], '1'); ?>>
                                 <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </fieldset>

            </div>
            <div class="modal-footer justify-between">
                <button type="button" id="send-telegram-test-button" class="btn btn-secondary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_send_telegram_test_nonce' ) ); ?>">
                    <span class="button-text"><?php esc_html_e( 'Send Test', 'cherkasov-smtp-mailer' ); ?></span>
                    <span class="spinner hidden"></span>
                </button>
                <button type="button" id="save-telegram-settings-button" class="btn btn-primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'csmtp_save_telegram_settings_nonce' ) ); ?>">
                    <span class="button-text"><?php esc_html_e( 'Save', 'cherkasov-smtp-mailer' ); ?></span>
                    <span class="spinner hidden"></span>
                </button>
            </div>
        </form>
        <?php
    }

    public function csmtp_ui_test_email_form() {
        ?>
        <div id="test-email-results" class="modal-body-padded"></div>
        <form id="csmtp-test-email-form" method="post" action="" class="flex flex-col flex-1 min-h-0">
            <div class="modal-body-padded">
                <div class="space-y-6">
                    <div>
                        <label for="csmtp_to_email" class="form-label"><?php esc_html_e( 'To', 'cherkasov-smtp-mailer' ); ?></label>
                        <input name="to_email" type="email" id="csmtp_to_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="mt-1 form-input" required>
                    </div>
                    <div>
                        <label for="csmtp_email_subject" class="form-label"><?php esc_html_e( 'Subject', 'cherkasov-smtp-mailer' ); ?></label>
                        <input name="subject" type="text" id="csmtp_email_subject" value="<?php esc_attr_e( 'Test Email from Cherkasov SMTP Mailer', 'cherkasov-smtp-mailer' ); ?>" class="mt-1 form-input">
                    </div>
                    <div>
                        <label for="csmtp_email_body" class="form-label"><?php esc_html_e( 'Message', 'cherkasov-smtp-mailer' ); ?></label>
                        <div class="test-email-tabs">
                            <button type="button" class="tab-btn active" data-tab="write"><?php esc_html_e( 'Write', 'cherkasov-smtp-mailer' ); ?></button>
                            <button type="button" class="tab-btn" data-tab="preview"><?php esc_html_e( 'Preview', 'cherkasov-smtp-mailer' ); ?></button>
                        </div>
                        <div class="test-email-tab-content-wrapper">
                            <div id="tab-write" class="tab-content active">
                                <textarea name="message" id="csmtp_email_body" rows="8" class="form-textarea"><?php
                                    printf(
                                        '<h3 style="font-family: sans-serif; color: #1e2gfa93b;">%s</h3><p style="font-family: sans-serif; color: #475569;">%s <strong>HTML email</strong> %s %s %s %s.</p>',
                                        esc_html__( 'Test Email', 'cherkasov-smtp-mailer' ),
                                        esc_html__( 'This is a test', 'cherkasov-smtp-mailer' ),
                                        esc_html__( 'sent from', 'cherkasov-smtp-mailer' ),
                                        esc_html( get_bloginfo( 'name' ) ),
                                        esc_html__( 'at', 'cherkasov-smtp-mailer' ),
                                        esc_html( site_url() )
                                    );
                                ?></textarea>
                            </div>
                            <div id="tab-preview" class="tab-content">
                                <iframe id="test-email-preview-iframe" src="about:blank"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="action" value="csmtp_send_test_email">
                <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'csmtp_send_test_email_nonce' ) ); ?>">
                <button type="submit" id="send-test-email-button" class="w-full btn btn-primary">
                    <span class="button-text"><?php esc_html_e( 'Send Email', 'cherkasov-smtp-mailer' ); ?></span>
                    <span class="spinner hidden"></span>
                </button>
            </div>
        </form>
        <?php
    }

    public function csmtp_ui_notification_popup() {
        ?>
         <div id="csmtp-notification" class="notification">
            <div class="flex items-start">
                <div id="notification-icon" class="flex-shrink-0"></div>
                <div class="ml-3 w-0 flex-1 pt-0-5"><p id="notification-message" class="text-sm font-semibold"></p></div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button type="button" class="notification-close-btn" onclick="document.getElementById('csmtp-notification').style.display = 'none'">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
         </div>
        <?php
    }
}

// ### HELPER FUNCTIONS ###

function csmtp_create_log_table() {
    global $wpdb;
    $sql = "CREATE TABLE " . CSMTP_LOG_TABLE . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        to_address text NOT NULL, subject text NOT NULL, message longtext NOT NULL,
        headers text NOT NULL, 
        status varchar(20) DEFAULT 'Sent' NOT NULL, 
        mailer varchar(20) DEFAULT 'WP Mail' NOT NULL,
        error_message text,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY timestamp (timestamp)
    ) " . $wpdb->get_charset_collate() . ";";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function csmtp_handle_log_retention_cron_scheduling( $old_value, $new_value ) {
    $old_period = $old_value['log_retention_period'] ?? 'never';
    $new_period = $new_value['log_retention_period'] ?? 'never';

    if ( $new_period === $old_period ) return;
    
    if ( wp_next_scheduled( CSMTP_CRON_HOOK ) ) {
        wp_clear_scheduled_hook( CSMTP_CRON_HOOK );
    }
    
    if ( 'never' !== $new_period ) {
        wp_schedule_event( time(), 'daily', CSMTP_CRON_HOOK );
    }
}

function csmtp_perform_log_cleanup() {
    global $wpdb;
    $period = csmtp_get_option()['log_retention_period'] ?? 'never';
    if ( 'never' === $period || ! is_numeric( $period ) ) return;
    $cutoff_date = current_datetime()->modify( "-$period days" )->format( 'Y-m-d H:i:s' );
    $wpdb->query( $wpdb->prepare( "DELETE FROM `" . CSMTP_LOG_TABLE . "` WHERE timestamp < %s", $cutoff_date ) );
}

function csmtp_get_option() {
    $defaults = [
        'mailer' => 'smtp',
        'smtp_host' => '', 'smtp_port' => '587', 'type_of_encryption' => 'tls', 'smtp_auth' => 'true',
        'smtp_username' => '', 'smtp_password' => '', 'from_name' => get_bloginfo('name'),
        'from_email' => get_option('admin_email'), 'force_from_address' => '0', 'disable_ssl_verification' => '0',
        'log_retention_period' => 'never', 
        'telegram_enabled' => '0', 'telegram_bot_token' => '', 'telegram_chat_id' => '', 
        'telegram_notify_failed' => '1', 'telegram_notify_success' => '0',
    ];
    return wp_parse_args( get_option('csmtp_options', []), $defaults );
}

function csmtp_send_telegram_message( $message ) {
    $options = csmtp_get_option();
    if ( empty($options['telegram_bot_token']) || empty($options['telegram_chat_id']) ) {
        return ['success' => false, 'error' => 'Bot Token or Chat ID is not configured.'];
    }
    $response = wp_remote_post("https://api.telegram.org/bot{$options['telegram_bot_token']}/sendMessage", [
        'body' => ['chat_id' => $options['telegram_chat_id'], 'text' => $message, 'parse_mode' => 'Markdown'],
        'timeout' => 10,
    ]);
    if (is_wp_error($response)) return ['success' => false, 'error' => $response->get_error_message()];
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (!$data || !$data['ok']) {
        return ['success' => false, 'error' => $data['description'] ?? 'Unknown API error. Response: ' . $body];
    }
    return ['success' => true];
}

// ### ACTIVATION ###
register_activation_hook( __FILE__, 'csmtp_activate' );

function csmtp_activate() {
    csmtp_create_log_table();
    if ( ! wp_next_scheduled( CSMTP_CRON_HOOK ) ) {
        $options = csmtp_get_option();
        if ( 'never' !== $options['log_retention_period'] ) {
            wp_schedule_event( time(), 'daily', CSMTP_CRON_HOOK );
        }
    }
}

// Initialize the plugin.
new CSMTP_Mailer();