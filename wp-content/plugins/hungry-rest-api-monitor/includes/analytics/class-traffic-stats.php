<?php
/**
 * Traffic Statistics.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Traffic_Stats
 * Aggregates traffic statistics over time.
 */
class NANDRESTAPI_Traffic_Stats
{

    /**
     * Get requests over time.
     *
     * @param string $period Period: '24h', '7d', '30d'.
     * @return array Traffic data with labels and counts.
     */
    public static function get_requests_over_time($period = '7d')
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        switch ($period) {
            case '24h':
                $date_format = '%Y-%m-%d %H:00';
                $interval = '24 HOUR';
                $group_by = 'hour';
                break;
            case '30d':
                $date_format = '%Y-%m-%d';
                $interval = '30 DAY';
                $group_by = 'day';
                break;
            case '7d':
            default:
                $date_format = '%Y-%m-%d';
                $interval = '7 DAY';
                $group_by = 'day';
                break;
        }

        // The $interval variable is safe - it only contains hardcoded values from the switch above.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE_FORMAT(recorded_at, %s) as time_bucket,
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) as error_count
                FROM %i
                WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL {$interval})
                GROUP BY time_bucket
                ORDER BY time_bucket ASC",
                $date_format,
                $table_name
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $results ? $results : array();
    }

    /**
     * Get method distribution.
     *
     * @param int $days Number of days to look back.
     * @return array Method counts.
     */
    public static function get_method_distribution($days = 7)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT method, COUNT(*) as count
                FROM %i
                WHERE recorded_at >= %s
                GROUP BY method
                ORDER BY count DESC',
                $table_name,
                $date_from
            ),
            ARRAY_A
        );

        return $results ? $results : array();
    }

    /**
     * Get status code distribution.
     *
     * @param int $days Number of days to look back.
     * @return array Status code counts grouped by category.
     */
    public static function get_status_distribution($days = 7)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    CASE 
                        WHEN status_code >= 200 AND status_code < 300 THEN '2xx'
                        WHEN status_code >= 300 AND status_code < 400 THEN '3xx'
                        WHEN status_code >= 400 AND status_code < 500 THEN '4xx'
                        WHEN status_code >= 500 THEN '5xx'
                        ELSE 'other'
                    END as status_group,
                    COUNT(*) as count
                FROM %i
                WHERE recorded_at >= %s
                GROUP BY status_group
                ORDER BY status_group ASC",
                $table_name,
                $date_from
            ),
            ARRAY_A
        );

        return $results ? $results : array();
    }

    /**
     * Get authenticated vs anonymous distribution.
     *
     * @param int $days Number of days to look back.
     * @return array Auth distribution.
     */
    public static function get_auth_distribution($days = 7)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    CASE WHEN user_id > 0 THEN 'authenticated' ELSE 'anonymous' END as auth_type,
                    COUNT(*) as count
                FROM %i
                WHERE recorded_at >= %s
                GROUP BY auth_type",
                $table_name,
                $date_from
            ),
            ARRAY_A
        );

        return $results ? $results : array();
    }
}
