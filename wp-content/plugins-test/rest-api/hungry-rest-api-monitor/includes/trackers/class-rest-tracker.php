<?php
/**
 * REST API Request Tracker.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Rest_Tracker
 * Intercepts and logs REST API requests.
 */
class NANDRESTAPI_Rest_Tracker
{

    /**
     * Request start time.
     *
     * @var float
     */
    private static $start_time = 0;

    /**
     * Initial memory usage.
     *
     * @var int
     */
    private static $start_memory = 0;

    /**
     * Initial query count.
     *
     * @var int
     */
    private static $start_query_count = 0;

    /**
     * Initialize the tracker.
     */
    public static function init()
    {
        // Hook before REST request is processed.
        add_filter('rest_pre_dispatch', array(__CLASS__, 'before_dispatch'), 10, 3);

        // Hook after REST request is processed.
        add_filter('rest_request_after_callbacks', array(__CLASS__, 'after_callbacks'), 9999, 3);
    }

    /**
     * Capture state before REST request processing.
     *
     * @param mixed           $result  Response to replace the requested version with.
     * @param WP_REST_Server  $server  Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     * @return mixed Unchanged result.
     */
    public static function before_dispatch($result, $server, $request)
    {
        global $wpdb;

        self::$start_time = microtime(true);
        self::$start_memory = memory_get_usage(true);
        self::$start_query_count = $wpdb->num_queries;

        // Initialize other trackers.
        NANDRESTAPI_Query_Tracker::start();
        NANDRESTAPI_HTTP_Tracker::start();
        NANDRESTAPI_Cache_Tracker::start();
        NANDRESTAPI_Error_Tracker::start();
        NANDRESTAPI_Transient_Tracker::start();

        return $result;
    }

    /**
     * Log request after processing.
     *
     * @param WP_REST_Response|WP_Error $response Response object.
     * @param array                     $handler  Route handler.
     * @param WP_REST_Request           $request  Request object.
     * @return WP_REST_Response|WP_Error Unchanged response.
     */
    public static function after_callbacks($response, $handler, $request)
    {
        global $wpdb;

        // Check if logging is enabled.
        $options = nandrestapi_get_options();
        if (empty($options['enable_logging'])) {
            return $response;
        }

        // Get endpoint.
        $endpoint = $request->get_route();

        // Check if endpoint is excluded.
        if (nandrestapi_is_endpoint_excluded($endpoint)) {
            return $response;
        }

        // Calculate metrics.
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);
        $peak_memory = memory_get_peak_usage(true);

        $response_time = $end_time - self::$start_time;
        $memory_usage = $end_memory - self::$start_memory;
        $query_count = $wpdb->num_queries - self::$start_query_count;

        // Get memory and time limits.
        $memory_limit = nandrestapi_get_memory_limit();
        $time_limit = nandrestapi_get_max_execution_time();

        $memory_percent = ($memory_limit > 0) ? ($peak_memory / $memory_limit) * 100 : 0;
        $time_percent = ($time_limit > 0) ? ($response_time / $time_limit) * 100 : 0;

        // Get status code.
        $status_code = 200;
        if (is_wp_error($response)) {
            $status_code = 500;
            $error_data = $response->get_error_data();
            if (isset($error_data['status'])) {
                $status_code = $error_data['status'];
            }
        } elseif ($response instanceof WP_REST_Response) {
            $status_code = $response->get_status();
        }

        // Get query time and duplicates from tracker.
        $query_stats = NANDRESTAPI_Query_Tracker::get_stats();

        // Get HTTP tracker stats.
        $http_stats = NANDRESTAPI_HTTP_Tracker::get_stats();

        // Get cache tracker stats.
        $cache_stats = NANDRESTAPI_Cache_Tracker::get_stats();

        // Get error tracker stats.
        $error_stats = NANDRESTAPI_Error_Tracker::get_stats();

        // Get transient tracker stats.
        $transient_stats = NANDRESTAPI_Transient_Tracker::get_stats();

        // Build log data.
        $log_data = array(
            'endpoint' => $endpoint,
            'full_url' => self::get_full_url($request),
            'method' => $request->get_method(),
            'status_code' => $status_code,
            'response_time' => $response_time,
            'memory_usage' => $memory_usage,
            'memory_peak' => $peak_memory,
            'memory_limit' => $memory_limit,
            'memory_percent' => round($memory_percent, 2),
            'time_limit' => $time_limit,
            'time_percent' => round($time_percent, 2),
            'query_count' => $query_count,
            'query_time' => $query_stats['total_time'],
            'duplicate_queries' => $query_stats['duplicates'],
            'user_id' => get_current_user_id(),
            'request_size' => self::get_request_size($request),
            'response_size' => self::get_response_size($response),
            'is_error' => $status_code >= 400 ? 1 : 0,
            'error_message' => self::get_error_message($response),
            'php_errors_count' => $error_stats['count'],
            'http_requests_count' => $http_stats['count'],
            'http_requests_time' => $http_stats['total_time'],
            'cache_hits' => $cache_stats['hits'],
            'cache_misses' => $cache_stats['misses'],
            'transient_updates' => $transient_stats['count'],
            'recorded_at' => current_time('mysql', true),
        );

        // Add IP address if enabled.
        if (!empty($options['log_ip_address'])) {
            $log_data['ip_address'] = self::get_client_ip();
        }

        // Insert log.
        $log_id = NANDRESTAPI_DB_Handler::insert_log($log_data);

        // Save detailed HTTP requests if any were made.
        if ($log_id && $http_stats['count'] > 0) {
            NANDRESTAPI_HTTP_Tracker::save_requests($log_id);
        }

        return $response;
    }

    /**
     * Get full request URL.
     *
     * @param WP_REST_Request $request Request object.
     * @return string Full URL.
     */
    private static function get_full_url($request)
    {
        $route = $request->get_route();
        $params = $request->get_query_params();

        $url = rest_url($route);
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        return esc_url_raw($url);
    }

    /**
     * Get request body size.
     *
     * @param WP_REST_Request $request Request object.
     * @return int Size in bytes.
     */
    private static function get_request_size($request)
    {
        $body = $request->get_body();
        return strlen($body);
    }

    /**
     * Get response body size.
     *
     * @param WP_REST_Response|WP_Error $response Response object.
     * @return int Size in bytes.
     */
    private static function get_response_size($response)
    {
        if (is_wp_error($response)) {
            return 0;
        }

        $data = $response->get_data();
        return strlen(wp_json_encode($data));
    }

    /**
     * Get error message from response.
     *
     * @param WP_REST_Response|WP_Error $response Response object.
     * @return string|null Error message or null.
     */
    private static function get_error_message($response)
    {
        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        if ($response instanceof WP_REST_Response && $response->get_status() >= 400) {
            $data = $response->get_data();
            if (isset($data['message'])) {
                return sanitize_text_field($data['message']);
            }
        }

        return null;
    }

    /**
     * Get client IP address.
     *
     * @return string IP address.
     */
    private static function get_client_ip()
    {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare.
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = sanitize_text_field(wp_unslash($_SERVER[$key]));
                // Handle comma-separated list.
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '';
    }
}
