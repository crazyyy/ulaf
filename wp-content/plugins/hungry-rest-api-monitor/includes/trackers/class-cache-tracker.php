<?php
/**
 * Object Cache Tracker.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Cache_Tracker
 * Tracks object cache hits and misses during REST calls.
 */
class NANDRESTAPI_Cache_Tracker
{

    /**
     * Cache hits at start.
     *
     * @var int
     */
    private static $start_hits = 0;

    /**
     * Cache misses at start.
     *
     * @var int
     */
    private static $start_misses = 0;

    /**
     * Start tracking.
     */
    public static function start()
    {
        global $wp_object_cache;

        self::$start_hits = 0;
        self::$start_misses = 0;

        if (isset($wp_object_cache) && is_object($wp_object_cache)) {
            if (isset($wp_object_cache->cache_hits)) {
                self::$start_hits = $wp_object_cache->cache_hits;
            }
            if (isset($wp_object_cache->cache_misses)) {
                self::$start_misses = $wp_object_cache->cache_misses;
            }
        }
    }

    /**
     * Get cache statistics.
     *
     * @return array Cache stats with hits and misses.
     */
    public static function get_stats()
    {
        global $wp_object_cache;

        $hits = 0;
        $misses = 0;

        if (isset($wp_object_cache) && is_object($wp_object_cache)) {
            if (isset($wp_object_cache->cache_hits)) {
                $hits = $wp_object_cache->cache_hits - self::$start_hits;
            }
            if (isset($wp_object_cache->cache_misses)) {
                $misses = $wp_object_cache->cache_misses - self::$start_misses;
            }
        }

        return array(
            'hits' => max(0, $hits),
            'misses' => max(0, $misses),
        );
    }
}
