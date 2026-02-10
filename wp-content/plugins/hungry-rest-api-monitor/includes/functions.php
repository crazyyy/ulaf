<?php
/**
 * Helper functions for Hungry Rest API Monitor.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get plugin options.
 *
 * @return array Plugin options.
 */
function nandrestapi_get_options()
{
    $defaults = array(
        'enable_logging' => 1,
        'data_retention_days' => 7,
        'log_ip_address' => 0,
        'log_request_body' => 0,
        'log_response_body' => 0,
        'excluded_endpoints' => '',
        'enable_stack_traces' => 0,
    );

    $options = get_option(NANDRESTAPI_OPTIONS_KEY, array());
    return wp_parse_args($options, $defaults);
}

/**
 * Get PHP memory limit in bytes.
 *
 * @return int Memory limit in bytes.
 */
function nandrestapi_get_memory_limit()
{
    $memory_limit = ini_get('memory_limit');
    if ('-1' === $memory_limit) {
        return PHP_INT_MAX;
    }
    return wp_convert_hr_to_bytes($memory_limit);
}

/**
 * Get max execution time in seconds.
 *
 * @return int Max execution time.
 */
function nandrestapi_get_max_execution_time()
{
    $max_time = (int) ini_get('max_execution_time');
    return $max_time > 0 ? $max_time : 30;
}

/**
 * Format bytes to human-readable string.
 *
 * @param int $bytes Bytes to format.
 * @return string Formatted string.
 */
function nandrestapi_format_bytes($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

/**
 * Format milliseconds to human-readable string.
 *
 * @param float $seconds Seconds to format.
 * @return string Formatted string.
 */
function nandrestapi_format_time($seconds)
{
    if ($seconds >= 1) {
        return number_format($seconds, 2) . 's';
    }
    return number_format($seconds * 1000, 0) . 'ms';
}

/**
 * Extract namespace from endpoint.
 *
 * @param string $endpoint Endpoint path.
 * @return string Namespace (e.g., 'wp/v2', 'wc/v3').
 */
function nandrestapi_extract_namespace($endpoint)
{
    // Remove leading slash.
    $endpoint = ltrim($endpoint, '/');

    // Match namespace pattern (e.g., wp/v2, wc/v3).
    if (preg_match('/^([a-z0-9-]+\/v\d+)/i', $endpoint, $matches)) {
        return $matches[1];
    }

    return 'unknown';
}

/**
 * Check if endpoint should be excluded from logging.
 *
 * @param string $endpoint Endpoint to check.
 * @return bool True if excluded.
 */
function nandrestapi_is_endpoint_excluded($endpoint)
{
    $options = nandrestapi_get_options();
    $excluded = $options['excluded_endpoints'];

    if (empty($excluded)) {
        return false;
    }

    $patterns = array_filter(array_map('trim', explode("\n", $excluded)));

    foreach ($patterns as $pattern) {
        if (fnmatch($pattern, $endpoint)) {
            return true;
        }
    }

    return false;
}
