<?php
/**
 * Database Query Tracker.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Query_Tracker
 * Tracks database queries and detects duplicates.
 */
class NANDRESTAPI_Query_Tracker
{

    /**
     * Query count at start.
     *
     * @var int
     */
    private static $start_count = 0;

    /**
     * Queries hash map for duplicate detection.
     *
     * @var array
     */
    private static $query_hashes = array();

    /**
     * Start tracking.
     */
    public static function start()
    {
        global $wpdb;

        self::$start_count = $wpdb->num_queries;
        self::$query_hashes = array();

        // If SAVEQUERIES is enabled, track existing queries.
        if (defined('SAVEQUERIES') && SAVEQUERIES && !empty($wpdb->queries)) {
            foreach ($wpdb->queries as $query) {
                $hash = md5($query[0]);
                if (!isset(self::$query_hashes[$hash])) {
                    self::$query_hashes[$hash] = 0;
                }
                self::$query_hashes[$hash]++;
            }
        }
    }

    /**
     * Get query statistics.
     *
     * @return array Query stats with total_time and duplicates count.
     */
    public static function get_stats()
    {
        global $wpdb;

        $total_time = 0;
        $duplicates = 0;
        $new_hashes = array();

        if (defined('SAVEQUERIES') && SAVEQUERIES && !empty($wpdb->queries)) {
            // Get only queries executed during REST request.
            $queries = array_slice($wpdb->queries, self::$start_count);

            foreach ($queries as $query) {
                $total_time += $query[1];

                // Track duplicates.
                $hash = md5($query[0]);
                if (!isset($new_hashes[$hash])) {
                    $new_hashes[$hash] = 0;
                }
                $new_hashes[$hash]++;
            }

            // Count queries that appeared more than once.
            foreach ($new_hashes as $count) {
                if ($count > 1) {
                    $duplicates += $count - 1;
                }
            }
        }

        return array(
            'total_time' => $total_time,
            'duplicates' => $duplicates,
        );
    }
}
