<?php
/**
 * Cron Execution Logger Class
 *
 * Tracks cron execution performance, memory usage, and errors.
 * Provides non-invasive logging for WP-Cron and Action Scheduler.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.2.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Cron Logger Class
 *
 * @since 2.2.0
 */
class DiveWP_Cron_Logger {

    /**
     * Singleton instance
     *
     * @var DiveWP_Cron_Logger
     */
    private static $instance = null;

    /**
     * Database access instance
     *
     * @var DiveWP_DB_Access
     */
    private $db;

    /**
     * Current execution tracking data
     *
     * @var array
     */
    private $current_execution = array();

    /**
     * Previous error handler
     *
     * @var callable|null
     */
    private $previous_error_handler = null;

    /**
     * Captured errors during execution
     *
     * @var array
     */
    private $captured_errors = array();

    /**
     * Hooks that have been attached for tracking
     *
     * @var array
     */
    private $attached_hooks = array();

    /**
     * Whether we're currently in cron context
     *
     * @var bool
     */
    private $is_cron_context = false;

    /**
     * Cached cron hooks for performance
     *
     * @var array|null
     */
    private $cron_hooks_cache = null;

    /**
     * Get singleton instance
     *
     * @since 2.2.0
     * @return DiveWP_Cron_Logger
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - registers hooks as early as possible
     */
    private function __construct() {
        $this->db = DiveWP_DB_Access::get_instance();
        
        // Register custom schedule IMMEDIATELY - before any scheduling can happen
        // This must be in constructor to ensure it's available when WordPress checks schedules
        add_filter('cron_schedules', array($this, 'add_custom_schedule_interval'), 1);
        
        // Initialize all other hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks for cron tracking
     *
     * @since 2.2.0
     * @return void
     */
    private function init_hooks() {
        // Attach to cron hooks as early as possible - plugins_loaded with priority 1
        add_action('plugins_loaded', array($this, 'early_attach_to_cron_hooks'), 1);
        
        // Also attach on muplugins_loaded for even earlier attachment (if available)
        add_action('muplugins_loaded', array($this, 'early_attach_to_cron_hooks'), 1);
        
        // Set up cron context detection and error handling
        add_action('plugins_loaded', array($this, 'setup_cron_context'), 1);

        // Universal hook tracker - catches ALL actions during cron execution
        add_action('all', array($this, 'track_all_hooks'), 1);

        // Hook into cron spawn process
        add_filter('cron_request', array($this, 'on_cron_request'), 10, 2);

        // Action Scheduler integration - use plugins_loaded to ensure AS is loaded
        add_action('plugins_loaded', array($this, 'init_action_scheduler_hooks'), 20);

        // Schedule daily cleanup - do this on init to avoid early scheduling issues
        add_action('init', array($this, 'schedule_cleanup_event'), 10);
        add_action('divewp_cleanup_cron_logs', array($this, 'cleanup_old_logs'));

        // Dynamically attach to hooks when new events are scheduled
        add_filter('pre_schedule_event', array($this, 'on_pre_schedule_event'), 10, 2);
        add_filter('pre_schedule_single_event', array($this, 'on_pre_schedule_single_event'), 10, 2);
        
        // Cleanup orphaned refresh event on init
        add_action('init', array($this, 'cleanup_orphaned_events'), 5);
    }

    /**
     * Add custom schedule interval for any future needs
     *
     * @since 2.2.0
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_custom_schedule_interval($schedules) {
        // Keep the schedule registered in case other code references it
        $schedules['divewp_5min'] = array(
            'interval' => 300,
            'display'  => 'Every 5 Minutes',
        );
        return $schedules;
    }

    /**
     * Cleanup orphaned scheduled events from previous version
     *
     * @since 2.2.1
     * @return void
     */
    public function cleanup_orphaned_events() {
        // Remove the old refresh event - it's no longer needed
        $timestamp = wp_next_scheduled('divewp_refresh_cron_hooks');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'divewp_refresh_cron_hooks');
        }
        
        // Clear all instances of this event
        wp_clear_scheduled_hook('divewp_refresh_cron_hooks');
    }

    /**
     * Schedule the cleanup event
     *
     * @since 2.2.1
     * @return void
     */
    public function schedule_cleanup_event() {
        if (!wp_next_scheduled('divewp_cleanup_cron_logs')) {
            wp_schedule_event(time(), 'daily', 'divewp_cleanup_cron_logs');
        }
    }

    /**
     * Initialize Action Scheduler hooks
     *
     * @since 2.2.1
     * @return void
     */
    public function init_action_scheduler_hooks() {
        if (class_exists('ActionScheduler')) {
            add_action('action_scheduler_before_execute', array($this, 'before_action_execute'), 10, 1);
            add_action('action_scheduler_after_execute', array($this, 'after_action_execute'), 10, 2);
            add_action('action_scheduler_failed_execution', array($this, 'on_action_failed'), 10, 2);
        }
    }

    /**
     * Early attachment to all known cron hooks
     *
     * @since 2.2.1
     * @return void
     */
    public function early_attach_to_cron_hooks() {
        // Build cache of cron hooks
        $this->build_cron_hooks_cache();
        
        // Attach to all known hooks
        if (!empty($this->cron_hooks_cache)) {
            foreach ($this->cron_hooks_cache as $hook => $dummy) {
                $this->attach_to_hook($hook);
            }
        }
    }

    /**
     * Build cache of all cron hooks for quick lookup
     *
     * @since 2.2.1
     * @return void
     */
    private function build_cron_hooks_cache() {
        if ($this->cron_hooks_cache !== null) {
            return;
        }
        
        $this->cron_hooks_cache = array();
        $cron = _get_cron_array();
        
        if (empty($cron)) {
            return;
        }

        foreach ($cron as $timestamp => $cronhooks) {
            foreach ($cronhooks as $hook => $args) {
                $this->cron_hooks_cache[$hook] = true;
            }
        }
    }

    /**
     * Check if a hook is a cron hook
     *
     * @since 2.2.1
     * @param string $hook Hook name
     * @return bool
     */
    private function is_cron_hook($hook) {
        // Rebuild cache if needed
        if ($this->cron_hooks_cache === null) {
            $this->build_cron_hooks_cache();
        }
        
        return isset($this->cron_hooks_cache[$hook]);
    }

    /**
     * Setup cron context detection and error handling
     *
     * @since 2.2.1
     * @return void
     */
    public function setup_cron_context() {
        $this->is_cron_context = defined('DOING_CRON') && DOING_CRON;
        
        if ($this->is_cron_context) {
            // Set up error handling for the cron execution
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Custom error handler needed to capture cron execution errors for logging; not debug code, part of cron monitoring system
            $this->previous_error_handler = set_error_handler(array($this, 'capture_error'));
            
            // Refresh the cache during cron to catch any new hooks
            $this->cron_hooks_cache = null;
            $this->build_cron_hooks_cache();
            
            // Re-attach to all hooks
            foreach ($this->cron_hooks_cache as $hook => $dummy) {
                $this->attach_to_hook($hook);
            }
        }
    }

    /**
     * Track all hooks during cron execution - catches hooks that slip through
     *
     * @since 2.2.1
     * @return void
     */
    public function track_all_hooks() {
        // Only track during cron execution
        if (!$this->is_cron_context && !(defined('DOING_CRON') && DOING_CRON)) {
            return;
        }
        
        // Update context flag if it changed
        if (!$this->is_cron_context) {
            $this->is_cron_context = true;
        }

        $hook = current_filter();
        
        // Skip WordPress internal hooks and common non-cron hooks
        if ($this->should_skip_hook($hook)) {
            return;
        }
        
        // Skip if already tracking this hook execution
        if (isset($this->current_execution[$hook])) {
            return;
        }

        // Check if this is a cron hook (refresh cache if needed during cron)
        if ($this->cron_hooks_cache === null) {
            $this->build_cron_hooks_cache();
        }
        
        // If it's a cron hook and we haven't attached yet, this is our chance
        if (isset($this->cron_hooks_cache[$hook]) && !isset($this->attached_hooks[$hook])) {
            // Start tracking immediately since we're already in the hook
            $this->start_hook_tracking($hook);
            
            // Also attach the end handler
            add_action($hook, array($this, 'on_cron_hook_end'), 99999);
            $this->attached_hooks[$hook] = true;
        }
    }

    /**
     * Check if a hook should be skipped from tracking
     *
     * @since 2.2.1
     * @param string $hook Hook name
     * @return bool True if should skip
     */
    private function should_skip_hook($hook) {
        // Skip empty hooks
        if (empty($hook)) {
            return true;
        }
        
        // Skip common WordPress internal hooks that aren't cron hooks
        $skip_prefixes = array(
            'gettext',
            'sanitize_',
            'esc_',
            'attribute_escape',
            'clean_url',
            'wp_kses',
            'pre_option_',
            'option_',
            'default_option_',
            'pre_transient_',
            'transient_',
            'pre_site_transient_',
            'site_transient_',
            'determine_locale',
            'load_textdomain',
            'override_load_textdomain',
            'plugin_locale',
            'theme_locale',
        );
        
        foreach ($skip_prefixes as $prefix) {
            if (strpos($hook, $prefix) === 0) {
                return true;
            }
        }
        
        // Skip common filter hooks
        $skip_hooks = array(
            'all',
            'shutdown',
            'wp_die_handler',
            'wp_die_ajax_handler',
            'wp_die_json_handler',
            'wp_die_xmlrpc_handler',
        );
        
        return in_array($hook, $skip_hooks, true);
    }

    /**
     * Attach tracking to a specific hook (if not already attached)
     *
     * @since 2.2.0
     * @param string $hook Hook name
     * @return void
     */
    private function attach_to_hook($hook) {
        if (empty($hook) || isset($this->attached_hooks[$hook])) {
            return;
        }

        // Add tracking hooks at very low priority (first) and very high priority (last)
        add_action($hook, array($this, 'on_cron_hook_start'), -9999);
        add_action($hook, array($this, 'on_cron_hook_end'), 99999);
        $this->attached_hooks[$hook] = true;
    }

    /**
     * Called before a cron event is scheduled - attach tracking
     *
     * @since 2.2.0
     * @param object|false $event     Event object or false
     * @param object       $event_obj Event object being scheduled
     * @return object|false
     */
    public function on_pre_schedule_event($event, $event_obj) {
        if ($event_obj && isset($event_obj->hook)) {
            $hook = $event_obj->hook;
            $this->attach_to_hook($hook);
            
            // Also add to cache
            if ($this->cron_hooks_cache !== null) {
                $this->cron_hooks_cache[$hook] = true;
            }
        }
        return $event;
    }

    /**
     * Called before a single cron event is scheduled - attach tracking
     *
     * @since 2.2.0
     * @param object|false $event     Event object or false
     * @param object       $event_obj Event object being scheduled
     * @return object|false
     */
    public function on_pre_schedule_single_event($event, $event_obj) {
        if ($event_obj && isset($event_obj->hook)) {
            $hook = $event_obj->hook;
            $this->attach_to_hook($hook);
            
            // Also add to cache
            if ($this->cron_hooks_cache !== null) {
                $this->cron_hooks_cache[$hook] = true;
            }
        }
        return $event;
    }

    /**
     * Start tracking a hook execution
     *
     * @since 2.2.1
     * @param string $hook Hook name
     * @return void
     */
    private function start_hook_tracking($hook) {
        // Determine trigger source
        $trigger_source = 'wp_cron';
        if (defined('WP_CLI') && WP_CLI) {
            $trigger_source = 'cli';
        } elseif (defined('REST_REQUEST') && REST_REQUEST) {
            $trigger_source = 'rest';
        }

        // Get the arguments passed to the hook
        $args = func_get_args();
        array_shift($args); // Remove $hook from args

        $this->current_execution[$hook] = array(
            'hook'           => $hook,
            'args'           => $args,
            'trigger_source' => $trigger_source,
            'start_time'     => microtime(true),
            'start_memory'   => memory_get_usage(true),
            'started_at'     => current_time('mysql', true),
            'log_id'         => null,
        );

        // Create the log entry
        $log_id = $this->db->log_cron_execution(array(
            'hook'           => $hook,
            'args'           => $args,
            'trigger_source' => $trigger_source,
            'started_at'     => $this->current_execution[$hook]['started_at'],
            'status'         => 'running',
        ));

        if ($log_id) {
            $this->current_execution[$hook]['log_id'] = $log_id;
        }

        // Reset captured errors for this hook
        $this->captured_errors[$hook] = array();
    }

    /**
     * Called before a cron hook starts
     *
     * @since 2.2.0
     * @return void
     */
    public function on_cron_hook_start() {
        $hook = current_filter();
        
        // Skip if already tracking (could happen from track_all_hooks)
        if (isset($this->current_execution[$hook])) {
            return;
        }
        
        $this->start_hook_tracking($hook);
    }

    /**
     * Called after a cron hook ends
     *
     * @since 2.2.0
     * @return void
     */
    public function on_cron_hook_end() {
        $hook = current_filter();

        if (!isset($this->current_execution[$hook])) {
            return;
        }

        $execution = $this->current_execution[$hook];
        $end_time = microtime(true);

        $duration_ms = (int) (($end_time - $execution['start_time']) * 1000);
        $peak_memory = memory_get_peak_usage(true);

        // Determine status based on captured errors
        $status = 'success';
        $error_message = null;
        $error_trace = null;

        if (!empty($this->captured_errors[$hook])) {
            $has_fatal = false;
            $has_warning = false;

            foreach ($this->captured_errors[$hook] as $error) {
                if (in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true)) {
                    $has_fatal = true;
                    break;
                }
                if (in_array($error['type'], array(E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE), true)) {
                    $has_warning = true;
                }
            }

            if ($has_fatal) {
                $status = 'error';
            } elseif ($has_warning) {
                $status = 'warning';
            }

            // Format error messages
            $error_messages = array();
            foreach ($this->captured_errors[$hook] as $error) {
                $error_messages[] = sprintf(
                    '[%s] %s in %s on line %d',
                    $this->get_error_type_name($error['type']),
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            }
            $error_message = implode("\n", $error_messages);
        }

        // Update the log entry
        if ($execution['log_id']) {
            $this->db->update_cron_log($execution['log_id'], array(
                'finished_at'       => current_time('mysql', true),
                'duration_ms'       => $duration_ms,
                'peak_memory_bytes' => $peak_memory,
                'status'            => $status,
                'error_message'     => $error_message,
                'error_trace'       => $error_trace,
            ));
        }

        // Cleanup
        unset($this->current_execution[$hook]);
        unset($this->captured_errors[$hook]);
    }

    /**
     * Handle cron request filter
     *
     * @since 2.2.0
     * @param array  $cron_request_array Cron request arguments
     * @param string $doing_wp_cron      The cron lock value
     * @return array Modified request array
     */
    public function on_cron_request($cron_request_array, $doing_wp_cron = '') {
        // Just pass through - we track at the hook level
        return $cron_request_array;
    }

    /**
     * Before Action Scheduler action executes
     *
     * @since 2.2.0
     * @param int $action_id Action ID
     * @return void
     */
    public function before_action_execute($action_id) {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        $store = ActionScheduler::store();
        $action = $store->fetch_action($action_id);

        if (!$action) {
            return;
        }

        $hook = $action->get_hook();
        $args = $action->get_args();

        $this->current_execution['as_' . $action_id] = array(
            'action_id'      => $action_id,
            'hook'           => $hook,
            'args'           => $args,
            'trigger_source' => 'action_scheduler',
            'start_time'     => microtime(true),
            'start_memory'   => memory_get_usage(true),
            'started_at'     => current_time('mysql', true),
            'log_id'         => null,
        );

        // Create the log entry
        $log_id = $this->db->log_cron_execution(array(
            'hook'           => $hook,
            'args'           => $args,
            'trigger_source' => 'action_scheduler',
            'started_at'     => $this->current_execution['as_' . $action_id]['started_at'],
            'status'         => 'running',
        ));

        if ($log_id) {
            $this->current_execution['as_' . $action_id]['log_id'] = $log_id;
        }
    }

    /**
     * After Action Scheduler action executes
     *
     * @since 2.2.0
     * @param int   $action_id Action ID
     * @param mixed $context   Execution context
     * @return void
     */
    public function after_action_execute($action_id, $context = null) {
        $key = 'as_' . $action_id;

        if (!isset($this->current_execution[$key])) {
            return;
        }

        $execution = $this->current_execution[$key];
        $end_time = microtime(true);
        $peak_memory = memory_get_peak_usage(true);

        $duration_ms = (int) (($end_time - $execution['start_time']) * 1000);

        // Update the log entry
        if ($execution['log_id']) {
            $this->db->update_cron_log($execution['log_id'], array(
                'finished_at'       => current_time('mysql', true),
                'duration_ms'       => $duration_ms,
                'peak_memory_bytes' => $peak_memory,
                'status'            => 'success',
            ));
        }

        unset($this->current_execution[$key]);
    }

    /**
     * When Action Scheduler action fails
     *
     * @since 2.2.0
     * @param int       $action_id Action ID
     * @param Exception $exception Exception thrown
     * @return void
     */
    public function on_action_failed($action_id, $exception) {
        $key = 'as_' . $action_id;

        if (!isset($this->current_execution[$key])) {
            // Try to create a log entry for the failure
            if (class_exists('ActionScheduler')) {
                $store = ActionScheduler::store();
                $action = $store->fetch_action($action_id);

                if ($action) {
                    $this->db->log_cron_execution(array(
                        'hook'          => $action->get_hook(),
                        'args'          => $action->get_args(),
                        'trigger_source' => 'action_scheduler',
                        'started_at'    => current_time('mysql', true),
                        'finished_at'   => current_time('mysql', true),
                        'status'        => 'error',
                        'error_message' => $exception->getMessage(),
                        'error_trace'   => $exception->getTraceAsString(),
                    ));
                }
            }
            return;
        }

        $execution = $this->current_execution[$key];
        $end_time = microtime(true);
        $peak_memory = memory_get_peak_usage(true);

        $duration_ms = (int) (($end_time - $execution['start_time']) * 1000);

        // Update the log entry
        if ($execution['log_id']) {
            $this->db->update_cron_log($execution['log_id'], array(
                'finished_at'       => current_time('mysql', true),
                'duration_ms'       => $duration_ms,
                'peak_memory_bytes' => $peak_memory,
                'status'            => 'error',
                'error_message'     => $exception->getMessage(),
                'error_trace'       => $exception->getTraceAsString(),
            ));
        }

        unset($this->current_execution[$key]);
    }

    /**
     * Custom error handler to capture errors during cron execution
     *
     * @since 2.2.0
     * @param int    $errno   Error level
     * @param string $errstr  Error message
     * @param string $errfile Error file
     * @param int    $errline Error line
     * @return bool
     */
    public function capture_error($errno, $errstr, $errfile, $errline) {
        // Only capture for current executing hook
        $current_hook = current_filter();
        
        if ($current_hook && isset($this->current_execution[$current_hook])) {
            if (!isset($this->captured_errors[$current_hook])) {
                $this->captured_errors[$current_hook] = array();
            }

            $this->captured_errors[$current_hook][] = array(
                'type'    => $errno,
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
            );
        }

        // Call previous error handler if exists
        if ($this->previous_error_handler) {
            return call_user_func($this->previous_error_handler, $errno, $errstr, $errfile, $errline);
        }

        // Return false to allow PHP's default error handler
        return false;
    }

    /**
     * Get human-readable error type name
     *
     * @since 2.2.0
     * @param int $type Error type constant
     * @return string Error type name
     */
    private function get_error_type_name($type) {
        $types = array(
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        );

        return isset($types[$type]) ? $types[$type] : 'Unknown';
    }

    /**
     * Cleanup old log entries
     *
     * @since 2.2.0
     * @return void
     */
    public function cleanup_old_logs() {
        $retention_days = apply_filters('divewp_cron_log_retention_days', 30);
        $this->db->cleanup_cron_logs($retention_days);
    }

    /**
     * Manually log a cron execution
     *
     * @since 2.2.0
     * @param string $hook          Hook name
     * @param array  $args          Hook arguments
     * @param string $trigger_source Trigger source
     * @param string $status        Execution status
     * @param int    $duration_ms   Duration in milliseconds
     * @param int    $memory_bytes  Peak memory in bytes
     * @param string $error_message Error message if failed
     * @return int|false Log ID on success, false on failure
     */
    public function manual_log($hook, $args = array(), $trigger_source = 'manual', $status = 'success', $duration_ms = 0, $memory_bytes = 0, $error_message = null) {
        return $this->db->log_cron_execution(array(
            'hook'              => $hook,
            'args'              => $args,
            'trigger_source'    => $trigger_source,
            'started_at'        => current_time('mysql', true),
            'finished_at'       => current_time('mysql', true),
            'duration_ms'       => $duration_ms,
            'peak_memory_bytes' => $memory_bytes,
            'status'            => $status,
            'error_message'     => $error_message,
        ));
    }
}
