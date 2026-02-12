<?php
/**
 * Endpoint Statistics.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Endpoint_Stats
 * Aggregates statistics per endpoint.
 */
class NANDRESTAPI_Endpoint_Stats
{

    /**
     * Get aggregated endpoint statistics.
     *
     * @param array $args Query arguments.
     * @return array Endpoint statistics.
     */
    public static function get_stats($args = array())
    {
        global $wpdb;

        $defaults = array(
            'days' => 7,
            'limit' => 50,
            'orderby' => 'total_calls',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $days = absint($args['days']);
        $limit = absint($args['limit']);
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Validate orderby.
        $allowed_orderby = array('endpoint', 'total_calls', 'avg_time', 'avg_memory', 'avg_queries', 'error_rate');
        $orderby = in_array($args['orderby'], $allowed_orderby, true) ? $args['orderby'] : 'total_calls';
        $order = 'ASC' === strtoupper($args['order']) ? 'ASC' : 'DESC';

        // The $orderby and $order variables are safe - validated against whitelists above.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    endpoint,
                    COUNT(*) as total_calls,
                    AVG(response_time) as avg_time,
                    AVG(memory_usage) as avg_memory,
                    AVG(query_count) as avg_queries,
                    SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) as error_count,
                    (SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as error_rate
                FROM %i
                WHERE recorded_at >= %s
                GROUP BY endpoint
                ORDER BY {$orderby} {$order}
                LIMIT %d",
                $table_name,
                $date_from,
                $limit
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        return $results ? $results : array();
    }

    /**
     * Get top slowest endpoints.
     *
     * @param int $limit  Number of endpoints.
     * @param int $days   Number of days to look back.
     * @return array Top slowest endpoints.
     */
    public static function get_slowest($limit = 10, $days = 7)
    {
        return self::get_stats(array(
            'days' => $days,
            'limit' => $limit,
            'orderby' => 'avg_time',
            'order' => 'DESC',
        ));
    }

    /**
     * Get top memory-heavy endpoints.
     *
     * @param int $limit  Number of endpoints.
     * @param int $days   Number of days to look back.
     * @return array Top memory-heavy endpoints.
     */
    public static function get_memory_heavy($limit = 10, $days = 7)
    {
        return self::get_stats(array(
            'days' => $days,
            'limit' => $limit,
            'orderby' => 'avg_memory',
            'order' => 'DESC',
        ));
    }

    /**
     * Get top query-heavy endpoints.
     *
     * @param int $limit  Number of endpoints.
     * @param int $days   Number of days to look back.
     * @return array Top query-heavy endpoints.
     */
    public static function get_query_heavy($limit = 10, $days = 7)
    {
        return self::get_stats(array(
            'days' => $days,
            'limit' => $limit,
            'orderby' => 'avg_queries',
            'order' => 'DESC',
        ));
    }
}
