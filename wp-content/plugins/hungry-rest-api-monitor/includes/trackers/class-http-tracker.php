<?php
/**
 * HTTP Request Tracker (Enhanced).
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_HTTP_Tracker
 * Tracks outgoing HTTP API requests with detailed information.
 */
class NANDRESTAPI_HTTP_Tracker
{

    /**
     * Captured HTTP requests.
     *
     * @var array
     */
    private static $requests = array();

    /**
     * Whether tracking is active.
     *
     * @var bool
     */
    private static $tracking = false;

    /**
     * Request start times.
     *
     * @var array
     */
    private static $start_times = array();

    /**
     * Start tracking.
     */
    public static function start()
    {
        self::$requests = array();
        self::$start_times = array();
        self::$tracking = true;

        // Hook before HTTP request.
        add_filter('pre_http_request', array(__CLASS__, 'before_request'), 10, 3);

        // Hook after HTTP request.
        add_action('http_api_debug', array(__CLASS__, 'after_request'), 10, 5);
    }

    /**
     * Before HTTP request - capture start time and details.
     *
     * @param false|array|WP_Error $preempt Whether to preempt the request.
     * @param array                $parsed_args Request arguments.
     * @param string               $url Request URL.
     * @return false|array|WP_Error Unchanged preempt value.
     */
    public static function before_request($preempt, $parsed_args, $url)
    {
        if (!self::$tracking) {
            return $preempt;
        }

        // Generate unique ID for this request.
        $request_id = md5($url . microtime(true));

        // Store start time.
        self::$start_times[$request_id] = array(
            'start_time' => microtime(true),
            'url' => $url,
            'args' => $parsed_args,
            'caller' => self::get_caller(),
        );

        // Store request ID in args for later retrieval.
        add_filter('http_request_args', function ($args) use ($request_id) {
            $args['_nandrestapi_request_id'] = $request_id;
            return $args;
        }, 9999);

        return $preempt;
    }

    /**
     * After HTTP request - capture response details.
     *
     * @param mixed  $response    HTTP response or WP_Error.
     * @param string $context     Context under which the hook is fired.
     * @param string $class       HTTP transport used.
     * @param array  $parsed_args HTTP request arguments.
     * @param string $url         Request URL.
     */
    public static function after_request($response, $context, $class, $parsed_args, $url)
    {
        if (!self::$tracking) {
            return;
        }

        // Find matching start time.
        $request_id = isset($parsed_args['_nandrestapi_request_id']) ? $parsed_args['_nandrestapi_request_id'] : null;

        $start_data = null;
        if ($request_id && isset(self::$start_times[$request_id])) {
            $start_data = self::$start_times[$request_id];
            unset(self::$start_times[$request_id]);
        } else {
            // Fallback - find by URL.
            foreach (self::$start_times as $id => $data) {
                if ($data['url'] === $url) {
                    $start_data = $data;
                    unset(self::$start_times[$id]);
                    break;
                }
            }
        }

        $start_time = $start_data ? $start_data['start_time'] : microtime(true);
        $caller = $start_data ? $start_data['caller'] : self::get_caller();

        // Calculate response time.
        $response_time = microtime(true) - $start_time;

        // Build request data.
        $request_data = array(
            'request_url' => $url,
            'request_method' => isset($parsed_args['method']) ? strtoupper($parsed_args['method']) : 'GET',
            'timeout_value' => isset($parsed_args['timeout']) ? intval($parsed_args['timeout']) : 0,
            'ssl_verify' => !empty($parsed_args['sslverify']) ? 1 : 0,
            'response_time' => $response_time,
            'transport' => $class,
            'caller_file' => $caller['file'],
            'caller_line' => $caller['line'],
            'caller_function' => $caller['function'],
            'recorded_at' => current_time('mysql', true),
        );

        // Request headers.
        if (isset($parsed_args['headers']) && is_array($parsed_args['headers'])) {
            $request_data['request_headers'] = wp_json_encode($parsed_args['headers']);
        }

        // Request body size.
        if (isset($parsed_args['body'])) {
            $request_data['request_body_size'] = is_string($parsed_args['body']) ? strlen($parsed_args['body']) : 0;
        }

        // Response data.
        if (is_wp_error($response)) {
            $request_data['is_error'] = 1;
            $request_data['error_message'] = $response->get_error_message();
            $request_data['response_code'] = 0;
        } else {
            $request_data['response_code'] = wp_remote_retrieve_response_code($response);
            $request_data['is_error'] = $request_data['response_code'] >= 400 ? 1 : 0;

            // Response headers.
            $response_headers = wp_remote_retrieve_headers($response);
            if ($response_headers) {
                $request_data['response_headers'] = wp_json_encode($response_headers->getAll());
            }

            // Response body size.
            $body = wp_remote_retrieve_body($response);
            $request_data['response_body_size'] = strlen($body);

            // Error message for error status codes.
            if ($request_data['is_error']) {
                $request_data['error_message'] = wp_remote_retrieve_response_message($response);
            }
        }

        self::$requests[] = $request_data;
    }

    /**
     * Get caller information from backtrace.
     *
     * @return array Caller info with file, line, function.
     */
    private static function get_caller()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

        $caller = array(
            'file' => '',
            'line' => 0,
            'function' => '',
        );

        // Skip internal WordPress and our plugin functions.
        $skip_files = array(
            'class-http.php',
            'class-wp-http.php',
            'class-http-tracker.php',
            'class-rest-tracker.php',
            'http.php',
        );

        foreach ($backtrace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            $filename = basename($frame['file']);

            if (in_array($filename, $skip_files, true)) {
                continue;
            }

            // Skip WordPress core files.
            if (strpos($frame['file'], '/wp-includes/') !== false) {
                continue;
            }

            $caller['file'] = $frame['file'];
            $caller['line'] = isset($frame['line']) ? $frame['line'] : 0;
            $caller['function'] = isset($frame['function']) ? $frame['function'] : '';
            break;
        }

        return $caller;
    }

    /**
     * Get HTTP request statistics.
     *
     * @return array HTTP stats with count and total_time.
     */
    public static function get_stats()
    {
        // Remove hooks.
        remove_filter('pre_http_request', array(__CLASS__, 'before_request'), 10);
        remove_action('http_api_debug', array(__CLASS__, 'after_request'), 10);
        self::$tracking = false;

        $total_time = 0;
        foreach (self::$requests as $request) {
            $total_time += $request['response_time'];
        }

        return array(
            'count' => count(self::$requests),
            'total_time' => $total_time,
            'requests' => self::$requests,
        );
    }

    /**
     * Save captured requests to database.
     *
     * @param int $parent_log_id Parent REST API log ID.
     */
    public static function save_requests($parent_log_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nandrestapi_http_requests';

        // Define format for each column - order matches database table columns.
        $column_formats = array(
            'parent_log_id' => '%d',
            'request_url' => '%s',
            'request_method' => '%s',
            'request_headers' => '%s',
            'request_body_size' => '%d',
            'response_code' => '%d',
            'response_headers' => '%s',
            'response_body_size' => '%d',
            'response_time' => '%f',
            'timeout_value' => '%d',
            'ssl_verify' => '%d',
            'is_error' => '%d',
            'error_message' => '%s',
            'caller_file' => '%s',
            'caller_line' => '%d',
            'caller_function' => '%s',
            'transport' => '%s',
            'recorded_at' => '%s',
        );

        foreach (self::$requests as $request) {
            // Build properly ordered data array matching column_formats order.
            $data = array('parent_log_id' => $parent_log_id);
            $formats = array('%d'); // parent_log_id format

            // Add each field in the order defined by column_formats.
            foreach ($column_formats as $column => $format) {
                if ($column === 'parent_log_id') {
                    continue; // Already added.
                }
                if (isset($request[$column])) {
                    $data[$column] = $request[$column];
                    $formats[] = $format;
                }
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert($table_name, $data, $formats);
        }
    }
}
