<?php
// REST worker route registration for concurrency tests

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('divewp/v1', '/bench/worker', array(
        'methods' => 'GET',
        'callback' => 'divewp_bench_worker_callback',
        'permission_callback' => '__return_true', // read-only endpoint; secured via token below
        'args' => array(
            'type' => array('required' => true),
            'token' => array('required' => true)
        )
    ));
    register_rest_route('divewp/v1', '/bench/preflight', array(
        'methods' => 'GET',
        'callback' => 'divewp_bench_preflight_callback',
        'permission_callback' => '__return_true'
    ));
});

function divewp_bench_worker_callback(WP_REST_Request $request) {
    $start = microtime(true);
    $type = sanitize_text_field($request->get_param('type'));
    $token = sanitize_text_field($request->get_param('token'));

    // Simple token gate: transient set by the calling test before launching pool
    $expected = get_transient('divewp_concurrency_worker_token');
    if (empty($expected) || !hash_equals($expected, $token)) {
        return new WP_REST_Response(array('ok' => false, 'error' => 'unauthorized'), 403);
    }

    try {
        switch ($type) {
            case 'db':
                global $wpdb;
                // CONCURRENCY WORKER - Direct query required for database performance measurement during concurrent testing
                // WordPress abstractions would add overhead and distort concurrent load testing accuracy
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $val = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload='yes'");
                if ($val === null) throw new Exception('db-failure');
                break;
            case 'http':
                // Hit a lightweight local endpoint
                $url = home_url('/wp-json/wp/v2/');
                $resp = wp_remote_get($url, array('timeout' => 5, 'sslverify' => false));
                if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) >= 400) {
                    throw new Exception('http-failure');
                }
                break;
            case 'mem':
                $block = str_repeat('M', 512 * 1024); // 0.5MB
                $hash = crc32($block);
                if (!$hash && $hash !== 0) throw new Exception('mem-failure');
                break;
            case 'file':
                $tmp = wp_tempnam('divewp_conc');
                if (!$tmp) throw new Exception('file-create');
                // BENCHMARK REQUIREMENT - Direct filesystem write needed for I/O performance testing
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
                $bytes = file_put_contents($tmp, str_repeat('F', 4096));
                if ($bytes === false) throw new Exception('file-write');
                // BENCHMARK REQUIREMENT - Direct filesystem read needed for I/O performance testing
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_get_contents
                $read = file_get_contents($tmp);
                // Prefer WordPress function for deletion when available
                if (function_exists('wp_delete_file')) {
                    wp_delete_file($tmp);
                } else {
                    // BENCHMARK REQUIREMENT - Direct file delete fallback for I/O testing environments
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
                    @unlink($tmp);
                }
                if ($read === false) throw new Exception('file-read');
                break;
            default:
                return new WP_REST_Response(array('ok' => false, 'error' => 'bad-type'), 400);
        }
        return new WP_REST_Response(array(
            'ok' => true,
            'duration' => round(microtime(true) - $start, 6)
        ), 200);
    } catch (Exception $e) {
        return new WP_REST_Response(array(
            'ok' => false,
            'error' => $e->getMessage(),
            'duration' => round(microtime(true) - $start, 6)
        ), 200);
    }
}

function divewp_bench_preflight_callback(WP_REST_Request $request) {
    $start = microtime(true);
    $url = home_url('/wp-json/divewp/v1/bench/worker?type=http&token=preflight');
    $resp = wp_remote_get($url, array('timeout' => 5, 'sslverify' => false));
    $lat = round(microtime(true) - $start, 3);
    if (is_wp_error($resp)) {
        return new WP_REST_Response(array('ok' => false, 'latency' => $lat, 'reason' => $resp->get_error_message()), 200);
    }
    $code = wp_remote_retrieve_response_code($resp);
    return new WP_REST_Response(array('ok' => ($code >= 200 && $code < 400), 'latency' => $lat, 'code' => $code), 200);
}