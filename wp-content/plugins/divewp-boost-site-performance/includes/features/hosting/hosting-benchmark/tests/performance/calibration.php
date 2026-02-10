<?php
/**
 * Performance Calibration Utilities
 *
 * Measures lightweight CPU and database baselines to derive dynamic thresholds
 * for the performance tests. Results are cached in a transient for reuse across
 * sub-tests within the same benchmark session.
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

/**
 * Hosting Benchmark Performance Calibration Class
 */
class DiveWP_Benchmark_Performance_Calibration {

    /**
     * Transient key for cached baselines
     */
    const TRANSIENT_KEY = 'divewp_benchmark_perf_baselines';

    /**
     * Transient expiration (seconds)
     * One hour is sufficient for a benchmark session
     */
    const TRANSIENT_TTL = 3600;

    /**
     * Return cached baselines or compute them
     *
     * @return array{
     *   cpu_ops_per_sec: float,
     *   db_reads_per_sec: float,
     *   db_writes_per_sec: float,
     *   env: array
     * }
     */
    public static function get_baselines() {
        $cached = get_transient(self::TRANSIENT_KEY);
        if (is_array($cached) && isset($cached['cpu_ops_per_sec'])) {
            return $cached;
        }

        $baselines = array(
            'cpu_ops_per_sec' => 0.0,
            'db_reads_per_sec' => 0.0,
            'db_writes_per_sec' => 0.0,
            'env' => self::collect_environment_info()
        );

        // Measure CPU baseline
        $baselines['cpu_ops_per_sec'] = self::measure_cpu_baseline();

        // Measure DB baselines (read/write). Fail-safe: tolerate DB issues
        try {
            $db = self::measure_db_baselines();
            $baselines['db_reads_per_sec'] = $db['reads'];
            $baselines['db_writes_per_sec'] = $db['writes'];
        } catch (Exception $e) {
            // Leave DB baselines at 0; scoring will fallback
        }

        set_transient(self::TRANSIENT_KEY, $baselines, self::TRANSIENT_TTL);
        return $baselines;
    }

    /**
     * Measure a lightweight CPU baseline using mixed arithmetic/string ops
     *
     * Target ~300-500ms to avoid long calibration while producing stable values
     *
     * @return float Operations per second
     */
    private static function measure_cpu_baseline() {
        $target_seconds = 0.4; // ~400ms
        $min_iterations = 20000;

        $ops = 0;
        $start = microtime(true);
        do {
            // Mixed micro-kernel: arithmetic, branching, hashing, json
            for ($i = 0; $i < $min_iterations; $i++) {
                $a = ($i * 31) ^ 0xABCDEF;
                $b = ($a % 97) + 1;
                $c = $b * $b - ($a & 255);
                $s = md5((string)($c));
                $j = json_decode(json_encode(array('x' => $c, 's' => $s)), true);
                if (!isset($j['x'])) {
                    // keep branch to avoid JIT eliminating work
                    $ops += 0; // no-op
                }
                $ops++;
            }
        } while ((microtime(true) - $start) < $target_seconds && $ops < 1000000);

        $elapsed = microtime(true) - $start;
        if ($elapsed <= 0) {
            return 0.0;
        }
        return $ops / $elapsed;
    }

    /**
     * Measure simple DB read/write baselines using a tiny temporary table
     *
     * @return array{reads: float, writes: float}
     */
    private static function measure_db_baselines() {
        global $wpdb;

        // Attempt to create a small temporary table
        $table_name = $wpdb->prefix . 'divewp_perf_calib';

        // Create table (if exists from previous failed cleanup, drop it first)
        // PERFORMANCE CALIBRATION - Direct query required for temporary calibration table cleanup
        // WordPress has no equivalent for DROP TABLE; essential for calibration baseline measurement isolation
        $wpdb->query('DROP TABLE IF EXISTS `' . esc_sql($table_name) . '`'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange

        $charset_collate = $wpdb->get_charset_collate();
        $create_sql = "CREATE TABLE `{$table_name}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `k` varchar(64) NOT NULL,
            `v` varchar(64) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `k_idx` (`k`)
        ) {$charset_collate}";

        // PERFORMANCE CALIBRATION - Direct query required for temporary calibration table creation
        // WordPress has no equivalent for calibration table creation; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $created = $wpdb->query($create_sql);

        $reads_per_sec = 0.0;
        $writes_per_sec = 0.0;

        try {
            if ($created === false) {
                throw new Exception('Calibration table create failed');
            }

            // Seed ~200 rows
            // BEST PRACTICE: Assign dynamic table name to $wpdb property to avoid identifier interpolation warnings
            $wpdb->perf_calib_table = $table_name;
            $seed_rows = 200;
            $inserted = 0;
            $seed_start = microtime(true);
            for ($i = 0; $i < $seed_rows; $i++) {
                $k = 'k_' . $i;
                $v = 'v_' . wp_generate_password(12, false, false);
                // PERFORMANCE CALIBRATION - Direct query required for calibration data seeding during baseline measurement
                // WordPress abstractions would interfere with database write performance calibration accuracy
                $prepared = $wpdb->prepare(
                    "INSERT INTO `{$wpdb->perf_calib_table}` (`k`,`v`) VALUES (%s,%s)",
                    $k,
                    $v
                );
                $res = $wpdb->query($prepared); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                if ($res !== false) {
                    $inserted++;
                }
            }
            $seed_elapsed = max(0.000001, microtime(true) - $seed_start);
            $writes_per_sec = $inserted / $seed_elapsed;

            // Measure reads for ~250ms
            $ops = 0;
            $read_target = 0.25;
            $read_start = microtime(true);
            while ((microtime(true) - $read_start) < $read_target) {
                $idx = wp_rand(0, $seed_rows - 1);
                $k = 'k_' . $idx;
                // PERFORMANCE CALIBRATION - Direct query required for read performance baseline measurement
                // WordPress abstractions would distort database read performance calibration timing accuracy
                $prepared = $wpdb->prepare(
                    "SELECT `v` FROM `{$wpdb->perf_calib_table}` WHERE `k` = %s",
                    $k
                );
                $wpdb->get_var($prepared); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                $ops++;
            }
            $read_elapsed = max(0.000001, microtime(true) - $read_start);
            $reads_per_sec = $ops / $read_elapsed;

        } finally {
            // Cleanup table
            // PERFORMANCE CALIBRATION - Direct query required for calibration table cleanup after baseline measurement
            // WordPress has no equivalent for DROP TABLE; essential for proper calibration isolation
            $wpdb->query('DROP TABLE IF EXISTS `' . esc_sql($table_name) . '`'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        }

        // Clean up $wpdb property
        unset($wpdb->perf_calib_table);
        return array('reads' => $reads_per_sec, 'writes' => $writes_per_sec);
    }

    /**
     * Collect environment info for interpretation text
     *
     * @return array
     */
    private static function collect_environment_info() {
        $info = array();
        $info['php_version'] = PHP_VERSION;
        $info['memory_limit'] = ini_get('memory_limit');
        $info['opcache_enabled'] = function_exists('opcache_get_status') ? (bool)ini_get('opcache.enable') : false;
        $info['opcache_jit'] = function_exists('opcache_get_status') ? (int)ini_get('opcache.jit') : 0;
        $info['object_cache'] = wp_using_ext_object_cache();
        return $info;
    }
}


