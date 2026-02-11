<?php
/**
 * PHP Error Tracker.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Error_Tracker
 * Captures PHP errors during REST API processing.
 */
class NANDRESTAPI_Error_Tracker
{

    /**
     * Captured errors.
     *
     * @var array
     */
    private static $errors = array();

    /**
     * Previous error handler.
     *
     * @var callable|null
     */
    private static $previous_handler = null;

    /**
     * Whether currently handling an error (recursion guard).
     *
     * @var bool
     */
    private static $is_handling = false;

    /**
     * Maximum number of errors to capture.
     *
     * @var int
     */
    private static $max_errors = 50;

    /**
     * Start tracking.
     */
    public static function start()
    {
        self::$errors = array();
        self::$is_handling = false;

        // Set custom error handler.
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
        self::$previous_handler = set_error_handler(array(__CLASS__, 'handle_error'));
    }

    /**
     * Custom error handler.
     *
     * @param int    $errno   Error level.
     * @param string $errstr  Error message.
     * @param string $errfile File where error occurred.
     * @param int    $errline Line number.
     * @return bool Whether to continue to default handler.
     */
    public static function handle_error($errno, $errstr, $errfile, $errline)
    {
        // Prevent recursion - if we're already handling an error, skip.
        if (self::$is_handling) {
            return false;
        }

        // Stop capturing if we've hit the limit.
        if (count(self::$errors) >= self::$max_errors) {
            return false;
        }

        self::$is_handling = true;

        // Store error details.
        self::$errors[] = array(
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        );

        self::$is_handling = false;

        // Call previous handler if exists.
        if (is_callable(self::$previous_handler)) {
            return call_user_func(self::$previous_handler, $errno, $errstr, $errfile, $errline);
        }

        // Return false to allow PHP's default error handler.
        return false;
    }

    /**
     * Get error statistics.
     *
     * @return array Error stats with count.
     */
    public static function get_stats()
    {
        // Restore previous error handler.
        restore_error_handler();

        return array(
            'count' => count(self::$errors),
            'errors' => self::$errors,
        );
    }
}
