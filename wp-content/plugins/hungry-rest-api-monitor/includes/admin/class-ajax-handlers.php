<?php
/**
 * AJAX Handlers.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Ajax_Handlers
 * Handles all AJAX requests with proper nonce verification.
 */
class NANDRESTAPI_Ajax_Handlers
{

    /**
     * Initialize AJAX handlers.
     */
    public static function init()
    {
        add_action('wp_ajax_nandrestapi_get_logs', array(__CLASS__, 'get_logs'));
        add_action('wp_ajax_nandrestapi_get_dashboard_data', array(__CLASS__, 'get_dashboard_data'));
        add_action('wp_ajax_nandrestapi_get_endpoints', array(__CLASS__, 'get_endpoints'));
        add_action('wp_ajax_nandrestapi_get_http_requests', array(__CLASS__, 'get_http_requests'));
        add_action('wp_ajax_nandrestapi_clear_logs', array(__CLASS__, 'clear_logs'));
        add_action('wp_ajax_nandrestapi_save_settings', array(__CLASS__, 'save_settings'));
        add_action('wp_ajax_nandrestapi_send_contact', array(__CLASS__, 'send_contact'));
        add_action('wp_ajax_nandrestapi_run_test_requests', array(__CLASS__, 'run_test_requests'));
    }

    /**
     * Verify nonce and capability.
     *
     * @return bool True if valid.
     */
    private static function verify_request()
    {
        if (!check_ajax_referer('nandrestapi_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'hungry-rest-api-monitor')));
            return false;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'hungry-rest-api-monitor')));
            return false;
        }

        return true;
    }

    /**
     * Get logs via AJAX.
     */
    public static function get_logs()
    {
        self::verify_request();

        // Nonce verified in verify_request() above.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $args = array(
            'page' => isset($_POST['page']) ? absint($_POST['page']) : 1,
            'per_page' => isset($_POST['per_page']) ? absint($_POST['per_page']) : 20,
            'orderby' => isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'recorded_at',
            'order' => isset($_POST['order']) ? sanitize_key($_POST['order']) : 'DESC',
            'endpoint' => isset($_POST['endpoint']) ? sanitize_text_field(wp_unslash($_POST['endpoint'])) : '',
            'namespace' => isset($_POST['namespace']) ? sanitize_text_field(wp_unslash($_POST['namespace'])) : '',
            'method' => isset($_POST['method']) ? sanitize_text_field(wp_unslash($_POST['method'])) : '',
            'status_code' => isset($_POST['status_code']) ? sanitize_text_field(wp_unslash($_POST['status_code'])) : '',
            'user_id' => isset($_POST['user_id']) ? sanitize_text_field(wp_unslash($_POST['user_id'])) : '',
            'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '',
            'date_to' => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '',
        );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $result = NANDRESTAPI_DB_Handler::get_logs($args);

        wp_send_json_success($result);
    }

    /**
     * Get dashboard data via AJAX.
     */
    public static function get_dashboard_data()
    {
        self::verify_request();

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
        $period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '7d';
        $days = '24h' === $period ? 1 : ('30d' === $period ? 30 : 7);

        $data = array(
            'summary' => NANDRESTAPI_Performance_Stats::get_summary($days),
            'requests_over_time' => NANDRESTAPI_Traffic_Stats::get_requests_over_time($period),
            'method_distribution' => NANDRESTAPI_Traffic_Stats::get_method_distribution($days),
            'status_distribution' => NANDRESTAPI_Traffic_Stats::get_status_distribution($days),
            'auth_distribution' => NANDRESTAPI_Traffic_Stats::get_auth_distribution($days),
            'top_endpoints' => NANDRESTAPI_Endpoint_Stats::get_stats(array('days' => $days, 'limit' => 10)),
            'slowest_endpoints' => NANDRESTAPI_Endpoint_Stats::get_slowest(5, $days),
            'database_size' => nandrestapi_format_bytes(NANDRESTAPI_DB_Cleanup::get_table_size()),
            'log_count' => NANDRESTAPI_DB_Cleanup::get_log_count(),
        );

        wp_send_json_success($data);
    }

    /**
     * Get endpoints data via AJAX.
     */
    public static function get_endpoints()
    {
        self::verify_request();

        // Nonce verified in verify_request() above.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $args = array(
            'days' => isset($_POST['days']) ? absint($_POST['days']) : 7,
            'limit' => isset($_POST['limit']) ? absint($_POST['limit']) : 50,
            'orderby' => isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'total_calls',
            'order' => isset($_POST['order']) ? sanitize_key($_POST['order']) : 'DESC',
        );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $endpoints = NANDRESTAPI_Endpoint_Stats::get_stats($args);

        wp_send_json_success(array('endpoints' => $endpoints));
    }

    /**
     * Clear all logs via AJAX.
     */
    public static function clear_logs()
    {
        self::verify_request();

        $result = NANDRESTAPI_DB_Cleanup::clear_all_logs();

        if (false !== $result) {
            wp_send_json_success(array('message' => __('All logs cleared successfully.', 'hungry-rest-api-monitor')));
        } else {
            wp_send_json_error(array('message' => __('Failed to clear logs.', 'hungry-rest-api-monitor')));
        }
    }

    /**
     * Save settings via AJAX.
     */
    public static function save_settings()
    {
        self::verify_request();

        // Nonce verified in verify_request() above.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $settings = array(
            'enable_logging' => isset($_POST['enable_logging']) ? 1 : 0,
            'data_retention_days' => isset($_POST['data_retention_days']) ? absint($_POST['data_retention_days']) : 7,
            'log_ip_address' => isset($_POST['log_ip_address']) ? 1 : 0,
            'log_request_body' => isset($_POST['log_request_body']) ? 1 : 0,
            'log_response_body' => isset($_POST['log_response_body']) ? 1 : 0,
            'excluded_endpoints' => isset($_POST['excluded_endpoints']) ? sanitize_textarea_field(wp_unslash($_POST['excluded_endpoints'])) : '',
            'enable_stack_traces' => isset($_POST['enable_stack_traces']) ? 1 : 0,
        );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        update_option(NANDRESTAPI_OPTIONS_KEY, $settings);

        wp_send_json_success(array('message' => __('Settings saved successfully.', 'hungry-rest-api-monitor')));
    }

    /**
     * Send contact form via AJAX.
     */
    public static function send_contact()
    {
        self::verify_request();

        // Nonce verified in verify_request() above.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'hungry-rest-api-monitor')));
        }

        $to = 'prakhar@nandann.com';
        $subject = '[REST API Monitor] ' . ($subject ? $subject : 'Support Request');
        $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
        $headers = array('Reply-To: ' . $email);

        $sent = wp_mail($to, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => __('Message sent successfully!', 'hungry-rest-api-monitor')));
        } else {
            wp_send_json_error(array('message' => __('Failed to send message. Please try again.', 'hungry-rest-api-monitor')));
        }
    }

    /**
     * Run test HTTP requests to verify tracking.
     */
    public static function run_test_requests()
    {
        self::verify_request();

        // Start HTTP tracking.
        NANDRESTAPI_HTTP_Tracker::start();

        $results = array();

        // Test 1: Simple GET request.
        $response = wp_remote_get('https://httpbin.org/get', array('timeout' => 10));
        $results[] = array(
            'url' => 'httpbin.org/get',
            'status' => is_wp_error($response) ? 'error' : wp_remote_retrieve_response_code($response),
        );

        // Test 2: JSON API.
        $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/', array('timeout' => 10));
        $results[] = array(
            'url' => 'api.wordpress.org',
            'status' => is_wp_error($response) ? 'error' : wp_remote_retrieve_response_code($response),
        );

        // Test 3: JSONPlaceholder API.
        $response = wp_remote_get('https://jsonplaceholder.typicode.com/todos/1', array('timeout' => 10));
        $results[] = array(
            'url' => 'jsonplaceholder.typicode.com',
            'status' => is_wp_error($response) ? 'error' : wp_remote_retrieve_response_code($response),
        );

        // Get HTTP stats and save to a mock parent log.
        $stats = NANDRESTAPI_HTTP_Tracker::get_stats();

        // Create a test log entry to be the parent.
        global $wpdb;
        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $table_name,
            array(
                'endpoint' => '/test/http-tracker',
                'full_url' => home_url('/wp-json/test/http-tracker'),
                'method' => 'GET',
                'status_code' => 200,
                'response_time' => $stats['total_time'],
                'memory_usage' => memory_get_usage(),
                'memory_peak' => memory_get_peak_usage(),
                'http_requests_count' => $stats['count'],
                'http_requests_time' => $stats['total_time'],
                'recorded_at' => current_time('mysql', true),
            ),
            array('%s', '%s', '%s', '%d', '%f', '%d', '%d', '%d', '%f', '%s')
        );

        $parent_log_id = $wpdb->insert_id;

        // Save the HTTP requests.
        NANDRESTAPI_HTTP_Tracker::save_requests($parent_log_id);

        wp_send_json_success(array(
            'message' => sprintf(
                // translators: %d is the number of HTTP requests made.
                __('Test complete! Made %d HTTP requests. Check the HTTP Requests tab.', 'hungry-rest-api-monitor'),
                $stats['count']
            ),
            'results' => $results,
        ));
    }

    /**
     * Get HTTP requests data via AJAX.
     */
    public static function get_http_requests()
    {
        self::verify_request();

        global $wpdb;

        $table_name = $wpdb->prefix . 'nandrestapi_http_requests';
        $logs_table = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // Parse arguments. Nonce verified in verify_request() above.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $orderby = isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'recorded_at';
        $order = isset($_POST['order']) ? sanitize_key($_POST['order']) : 'DESC';
        $url = isset($_POST['url']) ? sanitize_text_field(wp_unslash($_POST['url'])) : '';
        $method = isset($_POST['method']) ? sanitize_text_field(wp_unslash($_POST['method'])) : '';
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // Build WHERE clause.
        $where = array('1=1');
        $values = array();

        if (!empty($url)) {
            $where[] = 'h.request_url LIKE %s';
            $values[] = '%' . $wpdb->esc_like($url) . '%';
        }

        if (!empty($method)) {
            $where[] = 'h.request_method = %s';
            $values[] = $method;
        }

        if (!empty($status)) {
            switch ($status) {
                case 'success':
                    $where[] = 'h.response_code >= 200 AND h.response_code < 300';
                    break;
                case 'redirect':
                    $where[] = 'h.response_code >= 300 AND h.response_code < 400';
                    break;
                case 'client_error':
                    $where[] = 'h.response_code >= 400 AND h.response_code < 500';
                    break;
                case 'server_error':
                    $where[] = 'h.response_code >= 500';
                    break;
                case 'failed':
                    $where[] = 'h.response_code = 0';
                    break;
            }
        }

        if (!empty($date_from)) {
            $where[] = 'h.recorded_at >= %s';
            $values[] = $date_from;
        }

        if (!empty($date_to)) {
            $where[] = 'h.recorded_at <= %s';
            $values[] = $date_to;
        }

        $where_sql = implode(' AND ', $where);

        // Validate orderby.
        $allowed_orderby = array('id', 'request_url', 'request_method', 'response_code', 'response_time', 'response_body_size', 'recorded_at');
        $orderby = in_array($orderby, $allowed_orderby, true) ? 'h.' . $orderby : 'h.recorded_at';
        $order = 'ASC' === strtoupper($order) ? 'ASC' : 'DESC';

        $offset = ($page - 1) * $per_page;

        // Get total count.
        if (!empty($values)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} h WHERE {$where_sql}", $values));
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} h WHERE {$where_sql}");
        }

        // Get results with parent log endpoint.
        $query = "SELECT h.*, l.endpoint as parent_endpoint 
                  FROM {$table_name} h 
                  LEFT JOIN {$logs_table} l ON h.parent_log_id = l.id 
                  WHERE {$where_sql} 
                  ORDER BY {$orderby} {$order} 
                  LIMIT %d OFFSET %d";

        $query_values = array_merge($values, array($per_page, $offset));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $results = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);

        wp_send_json_success(array(
            'requests' => $results ? $results : array(),
            'total' => intval($total),
            'pages' => ceil($total / $per_page),
        ));
    }
}
