<?php
/**
 * Concurrency Helpers
 *
 * Shared utilities for running true parallel requests against a local REST
 * worker endpoint using curl_multi.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.0.0
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Concurrency_MultiRunner {

    /**
     * Run concurrent worker calls using curl_multi
     *
     * @param string $type            Worker type: db|http|mem|file
     * @param int    $parallel        Number of concurrent workers
     * @param float  $runtime_seconds Target run time (seconds)
     * @param string $token           Auth token for the worker route
     * @param array  $extra_params    Additional query params
     * @param float  $max_wall_time   Hard stop wall time (seconds)
     * @return array Results: success_count, fail_count, durations[], errors[], http_codes[]
     */
    public static function run($type, $parallel, $runtime_seconds, $token, $extra_params = array(), $max_wall_time = 45.0) {
        $results = array(
            'success_count' => 0,
            'fail_count' => 0,
            'durations' => array(),
            'errors' => array(),
            'http_codes' => array()
        );

        // Fallback if curl_multi is unavailable
        if (!function_exists('curl_multi_init')) {
            // Sequential fallback for minimal compatibility (very light)
            $end_time = microtime(true) + $runtime_seconds;
            while (microtime(true) < $end_time) {
                $unit = self::call_worker_sequential($type, $token, $extra_params);
                if ($unit['ok']) {
                    $results['success_count']++;
                    $results['durations'][] = $unit['duration'];
                } else {
                    $results['fail_count']++;
                    $results['errors'][] = $unit['error'];
                }
                usleep(20000); // 20ms to avoid hot loop
            }
            return $results;
        }

        $base_url = home_url('/wp-json/divewp/v1/bench/worker');
        $start_time = microtime(true);
        $hard_stop = $start_time + max(1.0, min($max_wall_time, $runtime_seconds + 5.0));

        // BENCHMARK REQUIREMENT - Concurrent HTTP testing requires cURL multi for accurate measurement
        // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_init
        $mh = curl_multi_init();
        $handles = array();

        $make_url = function() use ($base_url, $type, $token, $extra_params) {
            $params = array_merge($extra_params, array(
                'type' => $type,
                'token' => $token,
                'r' => wp_generate_password(8, false, false)
            ));
            return $base_url . '?' . http_build_query($params);
        };

        $add_handle = function() use (&$mh, &$handles, $make_url) {
            // BENCHMARK REQUIREMENT - Using cURL handle for precise connection control
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
            $ch = curl_init();
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_URL, $make_url());
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            // Local benchmark endpoint; TLS verification disabled intentionally for local/self-signed scenarios
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // BENCHMARK REQUIREMENT - Multi-handle used for concurrent requests
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_add_handle
            curl_multi_add_handle($mh, $ch);
            $handles[(int)$ch] = $ch;
        };

        // Seed pool
        $pool = max(1, min(32, (int)$parallel));
        for ($i = 0; $i < $pool; $i++) {
            $add_handle();
        }

        do {
            $running = 0;
            // BENCHMARK REQUIREMENT - Driving cURL state machine for concurrency loop
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_exec
            curl_multi_exec($mh, $running);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_select
            curl_multi_select($mh, 1.0);

            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_info_read
            while ($info = curl_multi_info_read($mh)) {
                $ch = $info['handle'];
                // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_getcontent
                $content = curl_multi_getcontent($ch);
                // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $results['http_codes'][] = $http_code;

                $ok = false;
                $duration = 0.0;
                $error_note = '';

                if ($http_code >= 200 && $http_code < 400 && !empty($content)) {
                    $decoded = json_decode($content, true);
                    if (is_array($decoded) && isset($decoded['ok']) && $decoded['ok'] === true) {
                        $ok = true;
                        $duration = isset($decoded['duration']) ? (float)$decoded['duration'] : 0.0;
                    } else {
                        $error_note = isset($decoded['error']) ? $decoded['error'] : 'Malformed worker response';
                    }
                } else {
                    $error_note = 'HTTP ' . $http_code;
                }

                if ($ok) {
                    $results['success_count']++;
                    $results['durations'][] = $duration;
                } else {
                    $results['fail_count']++;
                    $results['errors'][] = $error_note;
                }

                // Recycle or retire
                // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_remove_handle
                curl_multi_remove_handle($mh, $ch);
                // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
                curl_close($ch);
                unset($handles[(int)$ch]);

                $now = microtime(true);
                if ($now < $start_time + $runtime_seconds && $now < $hard_stop) {
                    $add_handle();
                }
            }
        } while (!empty($handles) && microtime(true) < $hard_stop);

        // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_multi_close
        curl_multi_close($mh);
        return $results;
    }

    /**
     * Sequential worker call (fallback when curl_multi is unavailable)
     */
    private static function call_worker_sequential($type, $token, $extra_params) {
        $base_url = home_url('/wp-json/divewp/v1/bench/worker');
        $params = array_merge($extra_params, array(
            'type' => $type,
            'token' => $token,
            'r' => wp_generate_password(8, false, false)
        ));
        $url = $base_url . '?' . http_build_query($params);
        $response = wp_remote_get($url, array('timeout' => 10, 'sslverify' => false));
        if (is_wp_error($response)) {
            return array('ok' => false, 'error' => $response->get_error_message(), 'duration' => 0);
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code >= 200 && $code < 400) {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['ok']) && $decoded['ok'] === true) {
                return array('ok' => true, 'duration' => isset($decoded['duration']) ? (float)$decoded['duration'] : 0);
            }
            return array('ok' => false, 'error' => isset($decoded['error']) ? $decoded['error'] : 'Malformed response', 'duration' => 0);
        }
        return array('ok' => false, 'error' => 'HTTP ' . $code, 'duration' => 0);
    }
}


