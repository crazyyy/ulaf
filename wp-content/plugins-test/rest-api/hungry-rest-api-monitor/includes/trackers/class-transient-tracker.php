<?php
/**
 * Transient Tracker.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Transient_Tracker
 * Tracks transient updates during REST API processing.
 */
class NANDRESTAPI_Transient_Tracker
{

    /**
     * Transient update count.
     *
     * @var int
     */
    private static $count = 0;

    /**
     * Whether tracking is active.
     *
     * @var bool
     */
    private static $tracking = false;

    /**
     * Start tracking.
     */
    public static function start()
    {
        self::$count = 0;
        self::$tracking = true;

        // Hook into transient updates.
        add_action('set_transient', array(__CLASS__, 'track_transient'), 10, 3);
        add_action('set_site_transient', array(__CLASS__, 'track_transient'), 10, 3);
    }

    /**
     * Track transient update.
     *
     * @param string $transient  Transient name.
     * @param mixed  $value      Transient value.
     * @param int    $expiration Transient expiration.
     */
    public static function track_transient($transient, $value, $expiration)
    {
        if (self::$tracking) {
            self::$count++;
        }
    }

    /**
     * Get transient update statistics.
     *
     * @return array Transient stats with count.
     */
    public static function get_stats()
    {
        // Remove hooks.
        remove_action('set_transient', array(__CLASS__, 'track_transient'), 10);
        remove_action('set_site_transient', array(__CLASS__, 'track_transient'), 10);
        self::$tracking = false;

        return array(
            'count' => self::$count,
        );
    }
}
