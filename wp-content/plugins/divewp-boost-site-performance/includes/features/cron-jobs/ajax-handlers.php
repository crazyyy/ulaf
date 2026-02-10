<?php
/**
 * AJAX Handlers for Cron Jobs Feature
 *
 * Handles all AJAX requests for cron management operations.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.2.0
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Cron AJAX Handler Class
 *
 * @since 2.2.0
 */
class DiveWP_Cron_Ajax {

    /**
     * Initialize AJAX handlers
     *
     * @since 2.2.0
     * @return void
     */
    public static function init() {
        // WP-Cron events
        add_action('wp_ajax_divewp_cron_get_events', array(__CLASS__, 'handle_get_events'));
        add_action('wp_ajax_divewp_cron_run_now', array(__CLASS__, 'handle_run_now'));
        add_action('wp_ajax_divewp_cron_delete', array(__CLASS__, 'handle_delete'));
        add_action('wp_ajax_divewp_cron_bulk_action', array(__CLASS__, 'handle_bulk_action'));
        add_action('wp_ajax_divewp_cron_add_event', array(__CLASS__, 'handle_add_event'));
        add_action('wp_ajax_divewp_cron_get_event_details', array(__CLASS__, 'handle_get_event_details'));

        // Action Scheduler
        add_action('wp_ajax_divewp_cron_get_as_actions', array(__CLASS__, 'handle_get_as_actions'));
        add_action('wp_ajax_divewp_cron_run_as_action', array(__CLASS__, 'handle_run_as_action'));
        add_action('wp_ajax_divewp_cron_cancel_as_action', array(__CLASS__, 'handle_cancel_as_action'));

        // Execution logs
        add_action('wp_ajax_divewp_cron_get_logs', array(__CLASS__, 'handle_get_logs'));
        add_action('wp_ajax_divewp_cron_get_hook_logs', array(__CLASS__, 'handle_get_hook_logs'));
        add_action('wp_ajax_divewp_cron_get_log_details', array(__CLASS__, 'handle_get_log_details'));
        add_action('wp_ajax_divewp_cron_clear_logs', array(__CLASS__, 'handle_clear_logs'));

        // Diagnostics
        add_action('wp_ajax_divewp_cron_get_diagnostics', array(__CLASS__, 'handle_get_diagnostics'));

        // Overdue tasks
        add_action('wp_ajax_divewp_cron_get_overdue', array(__CLASS__, 'handle_get_overdue'));

    }

    /**
     * Verify nonce and capabilities
     *
     * @since 2.2.0
     * @return bool True if verified, exits otherwise
     */
    private static function verify_request() {
        // Verify nonce
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce validation
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_cron_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed. Please refresh the page and try again.', 'divewp-boost-site-performance'),
            ));
        }

        // Verify capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'divewp-boost-site-performance'),
            ));
        }

        return true;
    }

    /**
     * Get all WP-Cron events
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_events() {
        self::verify_request();

        $cron_data = new DiveWP_Cron_Data();
        $all_events = $cron_data->get_wp_cron_events();
        $total = count($all_events);

        // Check for pagination parameters
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // If pagination requested, return only the requested slice
        if ($limit > 0) {
            $events = array_slice($all_events, $offset, $limit);
            $has_more = ($offset + $limit) < $total;

            wp_send_json_success(array(
                'events'   => $events,
                'total'    => $total,
                'has_more' => $has_more,
                'offset'   => $offset,
            ));
        }

        // No pagination - return all events
        wp_send_json_success(array(
            'events' => $all_events,
            'total'  => $total,
        ));
    }

    /**
     * Run a cron event immediately
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_run_now() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $hook = isset($_POST['hook']) ? sanitize_text_field(wp_unslash($_POST['hook'])) : '';
        $sig = isset($_POST['sig']) ? sanitize_text_field(wp_unslash($_POST['sig'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($hook)) {
            wp_send_json_error(array(
                'message' => __('Invalid task specified.', 'divewp-boost-site-performance'),
            ));
        }

        // Find the event
        $crons = _get_cron_array();
        $event_found = false;
        $event_args = array();

        foreach ($crons as $timestamp => $cronhooks) {
            if (isset($cronhooks[$hook])) {
                foreach ($cronhooks[$hook] as $event_sig => $data) {
                    if (empty($sig) || $event_sig === $sig) {
                        $event_args = isset($data['args']) ? $data['args'] : array();
                        $event_found = true;
                        break 2;
                    }
                }
            }
        }

        if (!$event_found) {
            wp_send_json_error(array(
                'message' => __('Task not found.', 'divewp-boost-site-performance'),
            ));
        }

        // Log the manual execution
        $logger = DiveWP_Cron_Logger::get_instance();
        $start_time = microtime(true);
        $start_memory = memory_get_usage(true);

        // Run the hook
        $error = null;
        try {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Intentionally executing arbitrary cron hooks; hook name is from WordPress core cron system, not plugin-defined
            do_action_ref_array($hook, $event_args);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $duration_ms = (int) ((microtime(true) - $start_time) * 1000);
        $peak_memory = memory_get_peak_usage(true);

        // Log the execution
        $logger->manual_log(
            $hook,
            $event_args,
            'manual',
            $error ? 'error' : 'success',
            $duration_ms,
            $peak_memory,
            $error
        );

        if ($error) {
            wp_send_json_error(array(
                'message' => sprintf(
                    /* translators: %s: Error message */
                    __('Task executed with error: %s', 'divewp-boost-site-performance'),
                    $error
                ),
            ));
        }

        wp_send_json_success(array(
            'message' => __('Task executed successfully.', 'divewp-boost-site-performance'),
            'duration_ms' => $duration_ms,
        ));
    }

    /**
     * Delete a cron event
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_delete() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $hook = isset($_POST['hook']) ? sanitize_text_field(wp_unslash($_POST['hook'])) : '';
        $sig = isset($_POST['sig']) ? sanitize_text_field(wp_unslash($_POST['sig'])) : '';
        $timestamp = isset($_POST['timestamp']) ? absint($_POST['timestamp']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($hook)) {
            wp_send_json_error(array(
                'message' => __('Invalid task specified.', 'divewp-boost-site-performance'),
            ));
        }

        // Find and delete the event
        $crons = _get_cron_array();
        $deleted = false;

        foreach ($crons as $ts => $cronhooks) {
            if ($timestamp && $ts !== $timestamp) {
                continue;
            }

            if (isset($cronhooks[$hook])) {
                foreach ($cronhooks[$hook] as $event_sig => $data) {
                    if (empty($sig) || $event_sig === $sig) {
                        $args = isset($data['args']) ? $data['args'] : array();
                        wp_unschedule_event($ts, $hook, $args);
                        $deleted = true;
                    }
                }
            }
        }

        if (!$deleted) {
            wp_send_json_error(array(
                'message' => __('Task not found or already deleted.', 'divewp-boost-site-performance'),
            ));
        }

        wp_send_json_success(array(
            'message' => __('Task deleted successfully.', 'divewp-boost-site-performance'),
        ));
    }

    /**
     * Handle bulk actions
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_bulk_action() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $action = isset($_POST['bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bulk_action'])) : '';
        $items = isset($_POST['items']) ? array_map('sanitize_text_field', wp_unslash($_POST['items'])) : array();
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($action) || empty($items)) {
            wp_send_json_error(array(
                'message' => __('Invalid bulk action or no items selected.', 'divewp-boost-site-performance'),
            ));
        }

        $processed_wp = 0;
        $processed_as = 0;
        $processed_logs = 0;
        $errors = array();
        $logs_to_delete = array();

        foreach ($items as $item) {
            $parts = explode('|', $item);
            $type = isset($parts[0]) ? $parts[0] : '';

            // Action Scheduler bulk cancel
            if ($type === 'as') {
                if (!class_exists('ActionScheduler')) {
                    $errors[] = __('Action Scheduler not available.', 'divewp-boost-site-performance');
                    continue;
                }
                $action_id = isset($parts[1]) ? absint($parts[1]) : 0;
                if ($action_id) {
                    $store = ActionScheduler::store();
                    $store->cancel_action($action_id);
                    $processed_as++;
                }
                continue;
            }

            // Execution log bulk delete (by hook)
            if ($type === 'log') {
                $hook = isset($parts[1]) ? sanitize_text_field($parts[1]) : '';
                if (!empty($hook)) {
                    $logs_to_delete[] = $hook;
                }
                continue;
            }

            // WP-Cron bulk delete (default)
            $hook = $type;
            $sig = isset($parts[1]) ? $parts[1] : '';

            if (empty($hook)) {
                continue;
            }

            switch ($action) {
                case 'delete':
                    $crons = _get_cron_array();
                    foreach ($crons as $timestamp => $cronhooks) {
                        if (isset($cronhooks[$hook])) {
                            foreach ($cronhooks[$hook] as $event_sig => $data) {
                                if (empty($sig) || $event_sig === $sig) {
                                    $args = isset($data['args']) ? $data['args'] : array();
                                    wp_unschedule_event($timestamp, $hook, $args);
                                    $processed_wp++;
                                }
                            }
                        }
                    }
                    break;
            }
        }

        // Delete logs by hook if requested
        if (!empty($logs_to_delete)) {
            $db = DiveWP_DB_Access::get_instance();
            foreach ($logs_to_delete as $log_hook) {
                $deleted = $db->delete_cron_logs_by_hook($log_hook);
                if ($deleted !== false) {
                    $processed_logs += $deleted;
                }
            }
        }

        if ($processed_wp === 0 && $processed_as === 0 && $processed_logs === 0) {
            wp_send_json_error(array(
                'message' => __('No tasks were processed.', 'divewp-boost-site-performance'),
            ));
        }

        $processed = $processed_wp + $processed_as + $processed_logs;

        wp_send_json_success(array(
            'message' => sprintf(
                /* translators: %d: Number of tasks processed */
                _n('%d task processed successfully.', '%d tasks processed successfully.', $processed, 'divewp-boost-site-performance'),
                $processed
            ),
            'processed' => $processed,
            'processed_wp' => $processed_wp,
            'processed_as' => $processed_as,
            'processed_logs' => $processed_logs,
        ));
    }

    /**
     * Add a new cron event
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_add_event() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $hook = isset($_POST['hook']) ? sanitize_text_field(wp_unslash($_POST['hook'])) : '';
        $schedule = isset($_POST['schedule']) ? sanitize_key(wp_unslash($_POST['schedule'])) : '';
        $args_json = isset($_POST['args']) ? sanitize_textarea_field(wp_unslash($_POST['args'])) : '[]';
        $timestamp = isset($_POST['timestamp']) ? absint($_POST['timestamp']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($hook)) {
            wp_send_json_error(array(
                'message' => __('Task name (hook) is required.', 'divewp-boost-site-performance'),
            ));
        }

        // Validate hook name
        if (!preg_match('/^[a-z0-9_]+$/i', $hook)) {
            wp_send_json_error(array(
                'message' => __('Task name can only contain letters, numbers, and underscores.', 'divewp-boost-site-performance'),
            ));
        }

        // Parse arguments
        $args = json_decode($args_json, true);
        if ($args_json !== '' && $args_json !== '[]' && !is_array($args)) {
            wp_send_json_error(array(
                'message' => __('Arguments must be valid JSON.', 'divewp-boost-site-performance'),
            ));
        }
        if (!is_array($args)) {
            $args = array();
        }

        // Validate timestamp (must be in the future; server time)
        $now = time();
        if ($timestamp <= $now) {
            wp_send_json_error(array(
                'message' => __('Please choose a future time.', 'divewp-boost-site-performance'),
            ));
        }

        // Schedule the event
        if ($schedule && $schedule !== 'once') {
            // Recurring event
            $schedules = wp_get_schedules();
            if (!isset($schedules[$schedule])) {
                wp_send_json_error(array(
                    'message' => __('Invalid schedule specified.', 'divewp-boost-site-performance'),
                ));
            }

            $result = wp_schedule_event($timestamp, $schedule, $hook, $args);
        } else {
            // Single event
            $result = wp_schedule_single_event($timestamp, $hook, $args);
        }

        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('Failed to schedule the task. It may already exist.', 'divewp-boost-site-performance'),
            ));
        }

        wp_send_json_success(array(
            'message' => __('Task scheduled successfully.', 'divewp-boost-site-performance'),
        ));
    }

    /**
     * Get event details for drawer
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_event_details() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $action_id = isset($_POST['action_id']) ? absint($_POST['action_id']) : 0;
        $hook = isset($_POST['hook']) ? sanitize_text_field(wp_unslash($_POST['hook'])) : '';
        $sig = isset($_POST['sig']) ? sanitize_text_field(wp_unslash($_POST['sig'])) : '';
        $timestamp = isset($_POST['timestamp']) ? absint($_POST['timestamp']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $cron_data = new DiveWP_Cron_Data();
        $event = null;

        // Handle Action Scheduler action
        if ($action_id > 0) {
            if (!class_exists('ActionScheduler')) {
                wp_send_json_error(array(
                    'message' => __('Action Scheduler is not available.', 'divewp-boost-site-performance'),
                ));
            }

            global $wpdb;
            $actions_table = $wpdb->prefix . 'actionscheduler_actions';
            $groups_table = $wpdb->prefix . 'actionscheduler_groups';

            // Query directly for the specific action_id
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only; table names are safe prefix-based strings
            $action_data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT 
                        a.action_id,
                        a.hook,
                        a.status,
                        a.scheduled_date_gmt,
                        a.scheduled_date_local,
                        a.args,
                        a.schedule,
                        a.attempts,
                        a.last_attempt_gmt,
                        a.last_attempt_local,
                        a.claim_id,
                        g.slug as group_slug
                    FROM {$actions_table} a
                    LEFT JOIN {$groups_table} g ON a.group_id = g.group_id
                    WHERE a.action_id = %d",
                    $action_id
                ),
                ARRAY_A
            );
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

            if (!$action_data) {
                wp_send_json_error(array(
                    'message' => __('Task not found.', 'divewp-boost-site-performance'),
                ));
            }

            // Format the action data to match the expected structure
            $current_time = time();
            $timestamp = strtotime($action_data['scheduled_date_gmt']);
            $args = json_decode($action_data['args'], true);

            // Format next run time
            $format = get_option('date_format') . ' ' . get_option('time_format');
            $next_run = wp_date($format, $timestamp);

            // Format status label
            $status_labels = array(
                'pending' => __('Pending', 'divewp-boost-site-performance'),
                'in-progress' => __('In Progress', 'divewp-boost-site-performance'),
                'complete' => __('Complete', 'divewp-boost-site-performance'),
                'failed' => __('Failed', 'divewp-boost-site-performance'),
                'canceled' => __('Canceled', 'divewp-boost-site-performance'),
            );
            $status_label = isset($status_labels[$action_data['status']]) ? $status_labels[$action_data['status']] : $action_data['status'];

            // Format args display
            $args_display = __('None', 'divewp-boost-site-performance');
            if (!empty($args) && is_array($args)) {
                if (count($args) <= 2) {
                    $formatted = array();
                    foreach ($args as $key => $value) {
                        if (is_numeric($key)) {
                            $formatted[] = is_scalar($value) ? $value : gettype($value);
                        } else {
                            $formatted[] = $key . ': ' . (is_scalar($value) ? $value : gettype($value));
                        }
                    }
                    $args_display = implode(', ', $formatted);
                } else {
                    $args_display = sprintf(
                        /* translators: %d: Number of arguments */
                        __('%d arguments', 'divewp-boost-site-performance'),
                        count($args)
                    );
                }
            }

            $event = array(
                'action_id' => (int) $action_data['action_id'],
                'hook' => $action_data['hook'],
                'timestamp' => $timestamp,
                'next_run' => $next_run,
                'next_run_relative' => human_time_diff($current_time, $timestamp),
                'is_overdue' => $action_data['status'] === 'pending' && $timestamp < $current_time,
                'status' => $action_data['status'],
                'status_label' => $status_label,
                'args' => $args,
                'args_display' => $args_display,
                'schedule' => $action_data['schedule'],
                'attempts' => $action_data['attempts'],
                'last_attempt' => $action_data['last_attempt_gmt'],
                'group' => $action_data['group_slug'],
                'claim_id' => $action_data['claim_id'],
                'source' => $cron_data->get_event_source_plugin($action_data['hook']),
                'type' => 'action_scheduler',
            );

            // Get recent execution logs for this hook
            $db = DiveWP_DB_Access::get_instance();
            $logs = $db->get_recent_cron_logs(10, 0, '', $event['hook']);
            $display_format = get_option('date_format') . ' ' . get_option('time_format');
            foreach ($logs as $idx => $log_item) {
                $logs[$idx]->started_at_local = get_date_from_gmt($log_item->started_at, $display_format);
            }

            wp_send_json_success(array(
                'event' => $event,
                'logs' => $logs,
            ));
            return;
        }

        // Handle WP-Cron event
        if (empty($hook)) {
            wp_send_json_error(array(
                'message' => __('Invalid task specified.', 'divewp-boost-site-performance'),
            ));
        }

        // Find the event
        $events = $cron_data->get_wp_cron_events();

        foreach ($events as $e) {
            if ($e['hook'] === $hook && (empty($sig) || $e['sig'] === $sig)) {
                if ($timestamp === 0 || $e['timestamp'] === $timestamp) {
                    $event = $e;
                    break;
                }
            }
        }

        if (!$event) {
            $db = DiveWP_DB_Access::get_instance();
            $logs = $db->get_recent_cron_logs(10, 0, '', $hook);
            $display_format = get_option('date_format') . ' ' . get_option('time_format');
            foreach ($logs as $idx => $log_item) {
                $logs[$idx]->started_at_local = get_date_from_gmt($log_item->started_at, $display_format);
            }

            // Always send fallback response when event not found - either with logs or without
            wp_send_json_success(array(
                'event' => null,
                'logs' => $logs,
                'fallback' => array(
                    'type' => 'executed',
                    'hook' => $hook,
                    'has_logs' => !empty($logs),
                    'message' => !empty($logs)
                        ? __('This task already ran just before you opened it. Here are the most recent executions.', 'divewp-boost-site-performance')
                        : __('This task already executed but has no recorded execution history.', 'divewp-boost-site-performance'),
                ),
            ));
            return;
        }

        // Get recent execution logs for this hook
        $db = DiveWP_DB_Access::get_instance();
        $logs = $db->get_recent_cron_logs(10, 0, '', $hook);

        wp_send_json_success(array(
            'event' => $event,
            'logs' => $logs,
        ));
    }

    /**
     * Get Action Scheduler actions
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_as_actions() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'pending';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 50;
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $cron_data = new DiveWP_Cron_Data();
        $actions = $cron_data->get_action_scheduler_actions($status, $limit, $offset);
        $stats = $cron_data->get_action_scheduler_stats();

        wp_send_json_success(array(
            'actions' => $actions,
            'stats' => $stats,
            'total' => count($actions),
        ));
    }

    /**
     * Run an Action Scheduler action immediately
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_run_as_action() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $action_id = isset($_POST['action_id']) ? absint($_POST['action_id']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (!$action_id) {
            wp_send_json_error(array(
                'message' => __('Invalid action specified.', 'divewp-boost-site-performance'),
            ));
        }

        if (!class_exists('ActionScheduler')) {
            wp_send_json_error(array(
                'message' => __('Action Scheduler is not available.', 'divewp-boost-site-performance'),
            ));
        }

        try {
            $runner = ActionScheduler::runner();
            $runner->process_action($action_id);

            wp_send_json_success(array(
                'message' => __('Action executed successfully.', 'divewp-boost-site-performance'),
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
                    /* translators: %s: Error message */
                    __('Failed to execute action: %s', 'divewp-boost-site-performance'),
                    $e->getMessage()
                ),
            ));
        }
    }

    /**
     * Cancel an Action Scheduler action
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_cancel_as_action() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $action_id = isset($_POST['action_id']) ? absint($_POST['action_id']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (!$action_id) {
            wp_send_json_error(array(
                'message' => __('Invalid action specified.', 'divewp-boost-site-performance'),
            ));
        }

        if (!class_exists('ActionScheduler')) {
            wp_send_json_error(array(
                'message' => __('Action Scheduler is not available.', 'divewp-boost-site-performance'),
            ));
        }

        try {
            $store = ActionScheduler::store();
            $store->cancel_action($action_id);

            wp_send_json_success(array(
                'message' => __('Action canceled successfully.', 'divewp-boost-site-performance'),
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
                    /* translators: %s: Error message */
                    __('Failed to cancel action: %s', 'divewp-boost-site-performance'),
                    $e->getMessage()
                ),
            ));
        }
    }

    /**
     * Get execution logs (grouped by hook)
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_logs() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 500;
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $db = DiveWP_DB_Access::get_instance();
        $logs = $db->get_recent_cron_logs($limit, 0, $status, '');
        $total = $db->get_total_cron_logs($status);
        $stats = $db->get_cron_log_stats();
        $display_format = get_option('date_format') . ' ' . get_option('time_format');

        // Group logs by hook name
        $grouped = array();
        foreach ($logs as $log) {
            $hook = $log->hook;
            if (!isset($grouped[$hook])) {
                $grouped[$hook] = array(
                    'hook' => $hook,
                    'total_runs' => 0,
                    'success_count' => 0,
                    'error_count' => 0,
                    'warning_count' => 0,
                    'total_duration' => 0,
                    'min_duration' => null,
                    'max_duration' => null,
                    'last_run' => null,
                    'last_status' => null,
                    'trigger_sources' => array(),
                );
            }

            $grouped[$hook]['total_runs']++;

            // Count by status
            if ($log->status === 'success') {
                $grouped[$hook]['success_count']++;
            } elseif ($log->status === 'error') {
                $grouped[$hook]['error_count']++;
            } elseif ($log->status === 'warning') {
                $grouped[$hook]['warning_count']++;
            }

            // Duration stats
            $duration = isset($log->duration_ms) ? (int) $log->duration_ms : 0;
            $grouped[$hook]['total_duration'] += $duration;
            if ($grouped[$hook]['min_duration'] === null || $duration < $grouped[$hook]['min_duration']) {
                $grouped[$hook]['min_duration'] = $duration;
            }
            if ($grouped[$hook]['max_duration'] === null || $duration > $grouped[$hook]['max_duration']) {
                $grouped[$hook]['max_duration'] = $duration;
            }

            // Track trigger sources
            if (!empty($log->trigger_source) && !in_array($log->trigger_source, $grouped[$hook]['trigger_sources'], true)) {
                $grouped[$hook]['trigger_sources'][] = $log->trigger_source;
            }

            // Last run (logs are ordered by started_at DESC, so first occurrence is latest)
            if ($grouped[$hook]['last_run'] === null) {
                $grouped[$hook]['last_run'] = $log->started_at;
                $grouped[$hook]['last_run_local'] = get_date_from_gmt($log->started_at, $display_format);
                $grouped[$hook]['last_status'] = $log->status;
            }
        }

        // Calculate averages and success rate
        foreach ($grouped as $hook => &$data) {
            $data['avg_duration_ms'] = $data['total_runs'] > 0 
                ? round($data['total_duration'] / $data['total_runs'], 1) 
                : 0;
            $data['success_rate'] = $data['total_runs'] > 0 
                ? round(($data['success_count'] / $data['total_runs']) * 100, 0) 
                : 0;
            unset($data['total_duration']); // Don't send raw total
        }
        unset($data);

        // Convert to indexed array and sort by last_run
        $hooks = array_values($grouped);
        usort($hooks, function($a, $b) {
            return strcmp($b['last_run'], $a['last_run']);
        });

        wp_send_json_success(array(
            'hooks' => $hooks,
            'total' => $total,
            'stats' => $stats,
        ));
    }

    /**
     * Get execution history for a specific hook
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_hook_logs() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $hook = isset($_POST['hook']) ? sanitize_text_field(wp_unslash($_POST['hook'])) : '';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 50;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (empty($hook)) {
            wp_send_json_error(array(
                'message' => __('Hook name is required.', 'divewp-boost-site-performance'),
            ));
        }

        $db = DiveWP_DB_Access::get_instance();
        $logs = $db->get_recent_cron_logs($limit, 0, '', $hook);

        // Format the executions
        $executions = array();
        $display_format = get_option('date_format') . ' ' . get_option('time_format');
        foreach ($logs as $log) {
            $executions[] = array(
                'id' => $log->id,
                'started_at' => $log->started_at,
                'started_at_local' => get_date_from_gmt($log->started_at, $display_format),
                'finished_at' => $log->finished_at,
                'duration_ms' => $log->duration_ms,
                'status' => $log->status,
                'trigger_source' => $log->trigger_source,
                'error_message' => $log->error_message,
            );
        }

        // Calculate summary stats
        $total_runs = count($executions);
        $success_count = 0;
        $error_count = 0;
        $total_duration = 0;
        $min_duration = null;
        $max_duration = null;

        foreach ($executions as $exec) {
            if ($exec['status'] === 'success') {
                $success_count++;
            } elseif ($exec['status'] === 'error') {
                $error_count++;
            }
            $duration = (int) $exec['duration_ms'];
            $total_duration += $duration;
            if ($min_duration === null || $duration < $min_duration) {
                $min_duration = $duration;
            }
            if ($max_duration === null || $duration > $max_duration) {
                $max_duration = $duration;
            }
        }

        wp_send_json_success(array(
            'hook' => $hook,
            'executions' => $executions,
            'summary' => array(
                'total_runs' => $total_runs,
                'success_count' => $success_count,
                'error_count' => $error_count,
                'success_rate' => $total_runs > 0 ? round(($success_count / $total_runs) * 100, 0) : 0,
                'avg_duration_ms' => $total_runs > 0 ? round($total_duration / $total_runs, 1) : 0,
                'min_duration_ms' => $min_duration,
                'max_duration_ms' => $max_duration,
            ),
        ));
    }

    /**
     * Get log details
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_log_details() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if (!$log_id) {
            wp_send_json_error(array(
                'message' => __('Invalid log specified.', 'divewp-boost-site-performance'),
            ));
        }

        $db = DiveWP_DB_Access::get_instance();
        $log = $db->get_cron_log($log_id);

        if (!$log) {
            wp_send_json_error(array(
                'message' => __('Log not found.', 'divewp-boost-site-performance'),
            ));
        }

        // Parse args JSON
        if (!empty($log->args)) {
            $log->args_parsed = json_decode($log->args, true);
        }

        wp_send_json_success(array(
            'log' => $log,
        ));
    }

    /**
     * Clear execution logs
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_clear_logs() {
        self::verify_request();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request()
        $days = isset($_POST['days']) ? absint($_POST['days']) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $db = DiveWP_DB_Access::get_instance();

        if ($days > 0) {
            $deleted = $db->cleanup_cron_logs($days);
            $message = sprintf(
                /* translators: %d: Number of days */
                __('Logs older than %d days have been deleted.', 'divewp-boost-site-performance'),
                $days
            );
        } else {
            $db->delete_all_cron_logs();
            $message = __('All logs have been deleted.', 'divewp-boost-site-performance');
        }

        wp_send_json_success(array(
            'message' => $message,
        ));
    }

    /**
     * Get diagnostics data
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_diagnostics() {
        self::verify_request();

        $cron_data = new DiveWP_Cron_Data();
        
        $diagnostics = array(
            'status' => $cron_data->detect_cron_status(),
            'orphaned' => $cron_data->get_orphaned_hooks(),
            'schedules' => $cron_data->get_available_schedules(),
            'commands' => $cron_data->get_server_cron_commands(),
            'as_stats' => $cron_data->get_action_scheduler_stats(),
        );

        wp_send_json_success($diagnostics);
    }

    /**
     * Get overdue tasks HTML
     *
     * @since 2.2.0
     * @return void
     */
    public static function handle_get_overdue() {
        self::verify_request();

        $cron_data = new DiveWP_Cron_Data();
        $wp_cron_events = $cron_data->get_wp_cron_events();
        $as_stats = $cron_data->get_action_scheduler_stats();

        // Filter to only overdue WP-Cron events
        $overdue_events = array_filter($wp_cron_events, function($e) {
            return $e['is_overdue'];
        });

        // Fetch overdue Action Scheduler tasks if available
        $overdue_as_actions = array();
        if ($as_stats['available'] && $as_stats['overdue'] > 0) {
            $all_as_actions = $cron_data->get_action_scheduler_actions('pending', 100, 0);
            $overdue_as_actions = array_filter($all_as_actions, function($action) {
                return $action['is_overdue'] === true;
            });
        }

        $total_overdue = count($overdue_events) + count($overdue_as_actions);

        // Build HTML output
        ob_start();

        if ($total_overdue === 0) {
            ?>
            <div class="divewp-cron-empty">
                <span class="dashicons dashicons-yes-alt"></span>
                <p><?php esc_html_e('No overdue tasks. All scheduled jobs are running on time.', 'divewp-boost-site-performance'); ?></p>
            </div>
            <?php
        } else {
            // Render overdue WP-Cron tasks
            if (!empty($overdue_events)) {
                ?>
                <h5><?php esc_html_e('Overdue WordPress Cron Jobs', 'divewp-boost-site-performance'); ?></h5>
                <div class="divewp-cron-list-table">
                    <div class="divewp-cron-table-header divewp-cron-table-header--overdue">
                        <div class="col-check"><input type="checkbox" class="divewp-cron-select-all"></div>
                        <div class="col-hook"><?php esc_html_e('Task Name', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-next"><?php esc_html_e('Was Due', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-schedule"><?php esc_html_e('Recurrence', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-actions"><?php esc_html_e('Actions', 'divewp-boost-site-performance'); ?></div>
                    </div>
                    <div class="divewp-cron-table-body">
                        <?php foreach ($overdue_events as $event) : ?>
                        <div class="divewp-cron-row divewp-cron-row--overdue" data-hook="<?php echo esc_attr($event['hook']); ?>" data-sig="<?php echo esc_attr($event['sig']); ?>" data-timestamp="<?php echo esc_attr($event['timestamp']); ?>" data-source="<?php echo esc_attr($event['source']); ?>" data-status="overdue">
                            <div class="col-check">
                                <input type="checkbox" class="divewp-cron-select" value="<?php echo esc_attr($event['hook'] . '|' . $event['sig']); ?>">
                            </div>
                            <div class="col-hook">
                                <span class="divewp-cron-hook-name"><?php echo esc_html($event['hook']); ?></span>
                            </div>
                            <div class="col-next">
                                <span class="divewp-cron-next-run divewp-cron-next-run--overdue">
                                    <?php echo esc_html($event['next_run']); ?>
                                </span>
                                <span class="divewp-cron-relative">
                                    <?php
                                    /* translators: %s: Time since task was due */
                                    printf(esc_html__('%s overdue', 'divewp-boost-site-performance'), esc_html($event['next_run_relative']));
                                    ?>
                                </span>
                            </div>
                            <div class="col-schedule">
                                <span class="divewp-cron-schedule"><?php echo esc_html($event['schedule_label']); ?></span>
                            </div>
                            <div class="col-actions">
                                <button type="button" class="button button-small divewp-cron-action" data-action="run-now" title="<?php esc_attr_e('Run Now', 'divewp-boost-site-performance'); ?>">
                                    <span class="dashicons dashicons-controls-play"></span>
                                </button>
                                <button type="button" class="button button-small divewp-cron-action" data-action="delete" title="<?php esc_attr_e('Delete', 'divewp-boost-site-performance'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            }

            // Render overdue Action Scheduler tasks in a table
            if (!empty($overdue_as_actions)) {
                ?>
                <h5><?php esc_html_e('Overdue Action Scheduler Queue', 'divewp-boost-site-performance'); ?></h5>
                <div class="divewp-cron-list-table">
                    <div class="divewp-cron-table-header divewp-cron-table-header--as-overdue">
                        <div class="col-check"><input type="checkbox" class="divewp-cron-select-all"></div>
                        <div class="col-hook"><?php esc_html_e('Task Name', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-next"><?php esc_html_e('Was Due', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-status"><?php esc_html_e('Status', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-group"><?php esc_html_e('Group', 'divewp-boost-site-performance'); ?></div>
                        <div class="col-actions"><?php esc_html_e('Actions', 'divewp-boost-site-performance'); ?></div>
                    </div>
                    <div class="divewp-cron-table-body">
                        <?php foreach ($overdue_as_actions as $action) : ?>
                        <div class="divewp-cron-row divewp-cron-row--overdue divewp-cron-row--as <?php echo !empty($action['is_orphaned']) ? 'divewp-cron-row--orphaned' : ''; ?>" data-action-id="<?php echo esc_attr($action['action_id']); ?>" data-hook="<?php echo esc_attr($action['hook']); ?>" data-source="<?php echo esc_attr($action['source']); ?>" data-status="overdue">
                            <div class="col-check">
                                <input type="checkbox" class="divewp-cron-select" value="<?php echo esc_attr('as|' . $action['action_id']); ?>">
                            </div>
                            <div class="col-hook">
                                <span class="divewp-cron-hook-name"><?php echo esc_html($action['hook']); ?></span>
                                <?php if (!empty($action['is_orphaned'])) : ?>
                                    <span class="status-pill status-pill-warning"><?php esc_html_e('Orphaned?', 'divewp-boost-site-performance'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($action['args_display'])) : ?>
                                    <span class="divewp-cron-args" title="<?php echo esc_attr($action['args_display']); ?>">
                                        <?php echo esc_html($action['args_display']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="col-next">
                                <span class="divewp-cron-next-run divewp-cron-next-run--overdue">
                                    <?php echo esc_html($action['next_run']); ?>
                                </span>
                                <span class="divewp-cron-relative">
                                    <?php
                                    /* translators: %s: Time since task was due */
                                    printf(esc_html__('%s overdue', 'divewp-boost-site-performance'), esc_html($action['next_run_relative']));
                                    ?>
                                </span>
                            </div>
                            <div class="col-status">
                                <span class="status-pill status-pill-warning"><?php echo esc_html($action['status_label']); ?></span>
                            </div>
                            <div class="col-group">
                                <span class="divewp-cron-group"><?php echo esc_html($action['group'] ? $action['group'] : '-'); ?></span>
                            </div>
                            <div class="col-actions">
                                <button type="button" class="button button-small divewp-cron-as-action" data-action="run" data-id="<?php echo esc_attr($action['action_id']); ?>" title="<?php esc_attr_e('Run Now', 'divewp-boost-site-performance'); ?>">
                                    <span class="dashicons dashicons-controls-play"></span>
                                </button>
                                <button type="button" class="button button-small divewp-cron-as-action" data-action="cancel" data-id="<?php echo esc_attr($action['action_id']); ?>" title="<?php esc_attr_e('Cancel', 'divewp-boost-site-performance'); ?>">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            }
        }

        $html = ob_get_clean();

        // Use full cron status to keep health consistent with initial render
        $cron_status = $cron_data->detect_cron_status();
        $health = isset($cron_status['health']) ? $cron_status['health'] : 'good';

        // Health labels (mirrors render_hero_bar)
        $health_labels = array(
            'good' => __('Healthy', 'divewp-boost-site-performance'),
            'warning' => __('Needs Attention', 'divewp-boost-site-performance'),
            'critical' => __('Critical Issues', 'divewp-boost-site-performance'),
        );
        $health_label = isset($health_labels[$health]) ? $health_labels[$health] : $health_labels['good'];

        wp_send_json_success(array(
            'html' => $html,
            'count' => $total_overdue,
            'wp_tasks' => count($wp_cron_events),
            'queue_tasks' => $as_stats['available'] ? $as_stats['pending'] : 0,
            'health' => $health,
            'health_label' => $health_label,
        ));
    }

}

