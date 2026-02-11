<?php
/**
 * Database Handler.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_DB_Handler
 * Handles database CRUD operations.
 */
class NANDRESTAPI_DB_Handler
{

    /**
     * Insert a log entry.
     *
     * @param array $data Log data.
     * @return int|false Insert ID or false on failure.
     */
    public static function insert_log($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        $defaults = array(
            'endpoint' => '',
            'full_url' => '',
            'method' => 'GET',
            'status_code' => 200,
            'response_time' => 0,
            'memory_usage' => 0,
            'memory_peak' => 0,
            'memory_limit' => 0,
            'memory_percent' => 0,
            'time_limit' => 0,
            'time_percent' => 0,
            'query_count' => 0,
            'query_time' => 0,
            'duplicate_queries' => 0,
            'user_id' => 0,
            'ip_address' => null,
            'request_size' => 0,
            'response_size' => 0,
            'is_error' => 0,
            'error_message' => null,
            'php_errors_count' => 0,
            'http_requests_count' => 0,
            'http_requests_time' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'transient_updates' => 0,
            'recorded_at' => current_time('mysql', true),
        );

        // Define format for each column to ensure correct types.
        $column_formats = array(
            'endpoint' => '%s',
            'full_url' => '%s',
            'method' => '%s',
            'status_code' => '%d',
            'response_time' => '%f',
            'memory_usage' => '%d',
            'memory_peak' => '%d',
            'memory_limit' => '%d',
            'memory_percent' => '%f',
            'time_limit' => '%d',
            'time_percent' => '%f',
            'query_count' => '%d',
            'query_time' => '%f',
            'duplicate_queries' => '%d',
            'user_id' => '%d',
            'ip_address' => '%s',
            'request_size' => '%d',
            'response_size' => '%d',
            'is_error' => '%d',
            'error_message' => '%s',
            'php_errors_count' => '%d',
            'http_requests_count' => '%d',
            'http_requests_time' => '%f',
            'cache_hits' => '%d',
            'cache_misses' => '%d',
            'transient_updates' => '%d',
            'recorded_at' => '%s',
        );

        $data = wp_parse_args($data, $defaults);

        // Build format array matching the data keys order.
        $formats = array();
        foreach (array_keys($data) as $key) {
            $formats[] = isset($column_formats[$key]) ? $column_formats[$key] : '%s';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert($table_name, $data, $formats);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get logs with filtering and pagination.
     *
     * @param array $args Query arguments.
     * @return array Logs with total count.
     */
    public static function get_logs($args = array())
    {
        global $wpdb;

        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'recorded_at',
            'order' => 'DESC',
            'endpoint' => '',
            'namespace' => '',
            'method' => '',
            'status_code' => '',
            'user_id' => '',
            'date_from' => '',
            'date_to' => '',
        );

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // Build WHERE clause.
        $where = array('1=1');
        $values = array();

        if (!empty($args['endpoint'])) {
            $where[] = 'endpoint LIKE %s';
            $values[] = '%' . $wpdb->esc_like($args['endpoint']) . '%';
        }

        if (!empty($args['namespace'])) {
            $where[] = 'endpoint LIKE %s';
            $values[] = $wpdb->esc_like($args['namespace']) . '%';
        }

        if (!empty($args['method'])) {
            $methods = is_array($args['method']) ? $args['method'] : array($args['method']);
            $placeholders = implode(',', array_fill(0, count($methods), '%s'));
            $where[] = "method IN ($placeholders)";
            $values = array_merge($values, $methods);
        }

        if (!empty($args['status_code'])) {
            $where[] = 'status_code = %d';
            $values[] = intval($args['status_code']);
        }

        if ('' !== $args['user_id'] && 'all' !== $args['user_id']) {
            if ('anonymous' === $args['user_id']) {
                $where[] = 'user_id = 0';
            } elseif ('authenticated' === $args['user_id']) {
                $where[] = 'user_id > 0';
            } else {
                $where[] = 'user_id = %d';
                $values[] = intval($args['user_id']);
            }
        }

        if (!empty($args['date_from'])) {
            $where[] = 'recorded_at >= %s';
            $values[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where[] = 'recorded_at <= %s';
            $values[] = $args['date_to'];
        }

        $where_sql = implode(' AND ', $where);

        // Validate orderby.
        $allowed_orderby = array(
            'id',
            'endpoint',
            'method',
            'status_code',
            'response_time',
            'memory_usage',
            'query_count',
            'recorded_at',
            'user_id',
        );
        $orderby = in_array($args['orderby'], $allowed_orderby, true) ? $args['orderby'] : 'recorded_at';
        $order = 'ASC' === strtoupper($args['order']) ? 'ASC' : 'DESC';

        // Pagination.
        $per_page = absint($args['per_page']);
        $page = absint($args['page']);
        $offset = ($page - 1) * $per_page;

        // Get total count.
        if (!empty($values)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}", $values));
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}");
        }

        // Get results.
        $query = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $query_values = array_merge($values, array($per_page, $offset));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $results = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);

        return array(
            'logs' => $results ? $results : array(),
            'total' => intval($total),
            'pages' => ceil($total / $per_page),
        );
    }

    /**
     * Get unique endpoints from logs.
     *
     * @return array List of endpoints.
     */
    public static function get_unique_endpoints()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT DISTINCT endpoint FROM %i ORDER BY endpoint ASC LIMIT 500',
                $table_name
            )
        );

        return $results ? $results : array();
    }
}
