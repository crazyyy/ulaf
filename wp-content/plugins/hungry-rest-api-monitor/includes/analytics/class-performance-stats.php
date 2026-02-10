<?php
/**
 * Performance Statistics.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Performance_Stats
 * Provides overall performance metrics.
 */
class NANDRESTAPI_Performance_Stats
{

    /**
     * Get dashboard summary stats.
     *
     * @param int $days Number of days to look back.
     * @return array Summary statistics.
     */
    public static function get_summary($days = 7)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(DISTINCT endpoint) as unique_endpoints,
                    AVG(response_time) as avg_response_time,
                    MAX(response_time) as max_response_time,
                    AVG(memory_usage) as avg_memory,
                    AVG(query_count) as avg_queries,
                    SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) as total_errors,
                    (SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as error_rate
                FROM %i
                WHERE recorded_at >= %s",
                $table_name,
                $date_from
            ),
            ARRAY_A
        );

        if (!$results) {
            return array(
                'total_requests' => 0,
                'unique_endpoints' => 0,
                'avg_response_time' => 0,
                'max_response_time' => 0,
                'avg_memory' => 0,
                'avg_queries' => 0,
                'total_errors' => 0,
                'error_rate' => 0,
            );
        }

        return $results;
    }

    /**
     * Get comparison with previous period.
     *
     * @param int $days Number of days for current period.
     * @return array Comparison data with changes.
     */
    public static function get_comparison($days = 7)
    {
        $current = self::get_summary($days);
        $previous = self::get_summary_for_period($days * 2, $days);

        $comparison = array();
        foreach ($current as $key => $value) {
            $prev_value = isset($previous[$key]) ? floatval($previous[$key]) : 0;
            $curr_value = floatval($value);

            $change = 0;
            if ($prev_value > 0) {
                $change = (($curr_value - $prev_value) / $prev_value) * 100;
            }

            $comparison[$key] = array(
                'current' => $curr_value,
                'previous' => $prev_value,
                'change' => round($change, 1),
            );
        }

        return $comparison;
    }

    /**
     * Get summary for a specific period.
     *
     * @param int $days_ago_start Start of period (days ago).
     * @param int $days_ago_end   End of period (days ago).
     * @return array Summary statistics.
     */
    private static function get_summary_for_period($days_ago_start, $days_ago_end)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days_ago_start} days"));
        $date_to = gmdate('Y-m-d H:i:s', strtotime("-{$days_ago_end} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(DISTINCT endpoint) as unique_endpoints,
                    AVG(response_time) as avg_response_time,
                    MAX(response_time) as max_response_time,
                    AVG(memory_usage) as avg_memory,
                    AVG(query_count) as avg_queries,
                    SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) as total_errors,
                    (SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as error_rate
                FROM %i
                WHERE recorded_at >= %s AND recorded_at < %s",
                $table_name,
                $date_from,
                $date_to
            ),
            ARRAY_A
        );

        return $results ? $results : array();
    }
}
