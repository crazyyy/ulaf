<?php
/**
 * Cron Data Retrieval Class
 *
 * Handles retrieval of WP-Cron events, Action Scheduler actions,
 * and system status information.
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
 * Cron Data Class
 *
 * @since 2.2.0
 */
class DiveWP_Cron_Data {
    /**
     * Grace period (seconds) before flagging a task as overdue.
     *
     * @var int
     */
    private $overdue_grace = 60;

    /**
     * Cache for plugin source mapping
     *
     * @var array
     */
    private $plugin_hooks_cache = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Build plugin hooks cache on init
        add_action('admin_init', array($this, 'build_plugin_hooks_cache'));
    }

    /**
     * Get all WP-Cron events
     *
     * Retrieves fresh cron data directly from WordPress on every call.
     * No caching is used to ensure real-time accuracy of orphan detection.
     *
     * @since 2.2.0
     * @return array Array of cron events
     */
    public function get_wp_cron_events() {
        $cron = _get_cron_array();
        
        if ( empty( $cron ) ) {
            return array();
        }

        $events = array();
        $current_time = time();

        // Collect all unique hooks first
        $all_hooks = array();
        foreach ( $cron as $timestamp => $cronhooks ) {
            foreach ( $cronhooks as $hook => $args ) {
                $all_hooks[ $hook ] = true;
            }
        }

        // Pre-calculate orphan status for ALL hooks at once
        $orphan_status = $this->batch_check_orphaned_hooks( array_keys( $all_hooks ) );

        foreach ( $cron as $timestamp => $cronhooks ) {
            foreach ( $cronhooks as $hook => $args ) {
                foreach ( $args as $sig => $data ) {
                    $schedule = isset( $data['schedule'] ) ? $data['schedule'] : false;
                    $interval = isset( $data['interval'] ) ? $data['interval'] : 0;
                    $event_args = isset( $data['args'] ) ? $data['args'] : array();
                    $source = $this->get_event_source_plugin( $hook );
                    $is_orphaned = isset( $orphan_status[ $hook ] ) ? $orphan_status[ $hook ] : false;
                    $validation = $this->validate_task_health( $hook, $source, $is_orphaned );

                    $events[] = array(
                        'hook' => $hook,
                        'timestamp' => $timestamp,
                        'next_run' => $this->format_next_run( $timestamp, $current_time ),
                        'next_run_relative' => human_time_diff( $current_time, $timestamp ),
                        'is_overdue' => $timestamp < ($current_time - $this->overdue_grace),
                        'schedule' => $schedule,
                        'schedule_label' => $this->get_schedule_label( $schedule, $interval ),
                        'interval' => $interval,
                        'args' => $event_args,
                        'args_display' => $this->format_args_display( $event_args ),
                        'sig' => $sig,
                        'source' => $source,
                        'is_orphaned' => $validation['status'] === 'potential_orphan' || $validation['status'] === 'confirmed_orphan',
                        'validation' => $validation,
                        'type' => 'wp_cron',
                    );
                }
            }
        }

        // Sort by next run time
        usort( $events, function( $a, $b ) {
            return $a['timestamp'] - $b['timestamp'];
        });

        return $events;
    }

    /**
     * Batch check orphan status for multiple hooks
     *
     * This ensures consistent orphan detection by checking all hooks at once
     * at the same point in WordPress's lifecycle.
     *
     * @since 2.2.0
     * @param array $hooks Array of hook names to check
     * @return array Associative array of hook => is_orphaned status
     */
    private function batch_check_orphaned_hooks( $hooks ) {
        global $wp_filter;
        
        $results = array();
        
        foreach ( $hooks as $hook ) {
            // Check if any callbacks are registered for this hook
            if ( isset( $wp_filter[ $hook ] ) && $wp_filter[ $hook ] instanceof WP_Hook ) {
                $results[ $hook ] = count( $wp_filter[ $hook ]->callbacks ) === 0;
            } else {
                $results[ $hook ] = ! isset( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ] );
            }
        }
        
        return $results;
    }

    /**
     * Get Action Scheduler actions
     *
     * @since 2.2.0
     * @param string $status  Action status filter (pending, complete, failed, canceled)
     * @param int    $limit   Number of actions to retrieve
     * @param int    $offset  Offset for pagination
     * @return array Array of Action Scheduler actions
     */
    public function get_action_scheduler_actions($status = 'pending', $limit = 50, $offset = 0) {
        // Check if Action Scheduler is available
        if (!class_exists('ActionScheduler')) {
            return array();
        }

        global $wpdb;

        // Get the Action Scheduler table names
        $actions_table = $wpdb->prefix . 'actionscheduler_actions';
        $groups_table = $wpdb->prefix . 'actionscheduler_groups';

        // Check if tables exist
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only check
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $actions_table
            )
        );

        if (!$table_exists) {
            return array();
        }

        // Build query based on status
        $status_clause = '';
        $prepare_values = array();

        if (!empty($status)) {
            $status_clause = 'WHERE a.status = %s';
            $prepare_values[] = sanitize_text_field($status);
        }

        $prepare_values[] = absint($limit);
        $prepare_values[] = absint($offset);

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table names are constructed from $wpdb->prefix + hardcoded Action Scheduler table names, not user input; status_clause is conditionally built with matching placeholders; spread operator handles variable placeholder count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring
        $actions = $wpdb->get_results(
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
                {$status_clause}
                ORDER BY a.scheduled_date_gmt ASC
                LIMIT %d OFFSET %d",
                ...$prepare_values
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber

        if (empty($actions)) {
            return array();
        }

        $current_time = time();
        $formatted_actions = array();

        foreach ($actions as $action) {
            $timestamp = strtotime($action['scheduled_date_gmt']);
            $args = json_decode($action['args'], true);
            $source = $this->get_event_source_plugin($action['hook']);
            $is_orphaned_legacy = $this->is_action_scheduler_action_orphaned($source, $action['hook']);
            $validation = $this->validate_task_health($action['hook'], $source, $is_orphaned_legacy);

            $formatted_actions[] = array(
                'action_id' => $action['action_id'],
                'hook' => $action['hook'],
                'timestamp' => $timestamp,
                'next_run' => $this->format_next_run($timestamp, $current_time),
                'next_run_relative' => human_time_diff($current_time, $timestamp),
                'is_overdue' => $action['status'] === 'pending' && $timestamp < ($current_time - $this->overdue_grace),
                'is_orphaned' => $validation['status'] === 'potential_orphan' || $validation['status'] === 'confirmed_orphan',
                'validation' => $validation,
                'status' => $action['status'],
                'status_label' => $this->get_as_status_label($action['status']),
                'args' => $args,
                'args_display' => $this->format_args_display($args),
                'schedule' => $action['schedule'],
                'attempts' => $action['attempts'],
                'last_attempt' => $action['last_attempt_gmt'],
                'group' => $action['group_slug'],
                'claim_id' => $action['claim_id'],
                'source' => $source,
                'type' => 'action_scheduler',
            );
        }

        return $formatted_actions;
    }

    /**
     * Get Action Scheduler queue statistics
     *
     * @since 2.2.0
     * @return array Queue statistics
     */
    public function get_action_scheduler_stats() {
        if (!class_exists('ActionScheduler')) {
            return array(
                'available' => false,
            );
        }

        global $wpdb;
        $actions_table = $wpdb->prefix . 'actionscheduler_actions';

        // Check if table exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only check
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $actions_table
            )
        );

        if (!$table_exists) {
            return array(
                'available' => false,
            );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics; table name is constructed from $wpdb->prefix + hardcoded Action Scheduler table name, not user input
        $stats = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
            "SELECT status, COUNT(*) as count FROM {$actions_table} GROUP BY status",
            ARRAY_A
        );

        $result = array(
            'available' => true,
            'pending' => 0,
            'in_progress' => 0,
            'complete' => 0,
            'failed' => 0,
            'canceled' => 0,
        );

        foreach ($stats as $stat) {
            $status = $stat['status'];
            if (isset($result[$status])) {
                $result[$status] = (int) $stat['count'];
            } elseif ($status === 'in-progress') {
                $result['in_progress'] = (int) $stat['count'];
            }
        }

        // Check for overdue pending actions with a 60-second grace
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics; table name is constructed from $wpdb->prefix + hardcoded Action Scheduler table name, not user input
        $result['overdue'] = (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
            "SELECT COUNT(*) FROM {$actions_table} WHERE status = 'pending' AND scheduled_date_gmt < (UTC_TIMESTAMP() - INTERVAL 60 SECOND)"
        );

        return $result;
    }

    /**
     * Detect orphaned hooks (callbacks with missing functions)
     *
     * Uses cached event data from get_wp_cron_events() to ensure consistent
     * orphan detection across all feature components.
     *
     * @since 2.2.0
     * @return array Array of orphaned hooks
     */
    public function get_orphaned_hooks() {
        // Use cached events to ensure consistent orphan detection
        $events = $this->get_wp_cron_events();
        $orphaned = array();

        foreach ( $events as $event ) {
            if ( $event['is_orphaned'] ) {
                $orphaned[] = $event;
            }
        }

        return $orphaned;
    }

    /**
     * Check if a hook is orphaned (no callbacks registered)
     *
     * @since 2.2.0
     * @param string $hook Hook name
     * @return bool True if orphaned
     */
    public function is_hook_orphaned($hook) {
        global $wp_filter;

        // Check if any callbacks are registered for this hook
        if (isset($wp_filter[$hook]) && $wp_filter[$hook] instanceof WP_Hook) {
            return count($wp_filter[$hook]->callbacks) === 0;
        }

        return !isset($wp_filter[$hook]) || empty($wp_filter[$hook]);
    }

    /**
     * Check if an Action Scheduler action is potentially orphaned based on plugin status
     *
     * This is a workaround that checks if the source plugin is still active,
     * since Action Scheduler doesn't support traditional callback-based orphan detection.
     *
     * @since 2.2.0
     * @param string $source The detected source plugin name
     * @param string $hook   The action hook name
     * @return bool True if potentially orphaned (source plugin not active)
     */
    private function is_action_scheduler_action_orphaned($source, $hook) {
        // Skip core WordPress actions - never orphaned
        if ($source === __('WordPress Core', 'divewp-boost-site-performance')) {
            return false;
        }

        // Skip Action Scheduler internal actions - never orphaned
        if ($source === 'Action Scheduler') {
            return false;
        }

        // Skip unknown sources - can't determine orphan status
        if ($source === __('Unknown', 'divewp-boost-site-performance')) {
            return false;
        }

        // Skip Theme sources - themes can be active without being "plugins"
        if ($source === __('Theme', 'divewp-boost-site-performance')) {
            return false;
        }

        // Get all active plugins
        $active_plugins = get_option('active_plugins', array());
        
        // Build list of active plugin names
        foreach ($active_plugins as $plugin_file) {
            $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
            if (file_exists($plugin_path)) {
                $plugin_data = get_plugin_data($plugin_path, false, false);
                if ($plugin_data['Name'] === $source) {
                    return false; // Plugin is active, not orphaned
                }
            }
        }

        // Source plugin not found in active plugins - potentially orphaned
        return true;
    }

    /**
     * Detect cron system status
     *
     * @since 2.2.0
     * @return array System status information
     */
    public function detect_cron_status() {
        $status = array(
            'wp_cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
            'alternate_cron' => defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON,
            'cron_lock_timeout' => defined('WP_CRON_LOCK_TIMEOUT') ? WP_CRON_LOCK_TIMEOUT : 60,
            'doing_cron' => defined('DOING_CRON') && DOING_CRON,
            'php_max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'wp_cli_available' => defined('WP_CLI') && WP_CLI,
        );

        // Check last cron run
        $last_cron = get_transient('doing_cron');
        $status['last_cron_timestamp'] = $last_cron ? $last_cron : 0;
        $status['last_cron_relative'] = $last_cron ? human_time_diff($last_cron, time()) . ' ago' : __('Unknown', 'divewp-boost-site-performance');

        // Check for potential server cron (if WP-Cron is disabled, assume server cron)
        $status['server_cron_likely'] = $status['wp_cron_disabled'];

        // Health verdict
        $status['health'] = $this->calculate_health_status($status);

        // Recommendations
        $status['recommendations'] = $this->get_recommendations($status);

        return $status;
    }

    /**
     * Get the source plugin for a hook
     *
     * @since 2.2.0
     * @param string $hook Hook name
     * @return string Plugin name or 'WordPress Core' or 'Unknown'
     */
    public function get_event_source_plugin($hook) {
        // Check cache first
        if (isset($this->plugin_hooks_cache[$hook])) {
            return $this->plugin_hooks_cache[$hook];
        }

        // Known WordPress core cron hooks
        $core_hooks = array(
            'wp_version_check',
            'wp_update_plugins',
            'wp_update_themes',
            'wp_scheduled_delete',
            'wp_scheduled_auto_draft_delete',
            'delete_expired_transients',
            'wp_privacy_delete_old_export_files',
            'wp_site_health_scheduled_check',
            'recovery_mode_clean_expired_keys',
            'wp_https_detection',
            'wp_delete_temp_updater_backups',
        );

        if (in_array($hook, $core_hooks, true)) {
            return __('WordPress Core', 'divewp-boost-site-performance');
        }

        // Check WooCommerce hooks
        if (strpos($hook, 'woocommerce_') === 0 || strpos($hook, 'wc_') === 0) {
            return 'WooCommerce';
        }

        // Check Action Scheduler hooks
        if (strpos($hook, 'action_scheduler_') === 0) {
            return 'Action Scheduler';
        }

        // Try to identify from hook prefix patterns
        global $wp_filter;
        if (isset($wp_filter[$hook])) {
            $callbacks = $wp_filter[$hook]->callbacks;
            foreach ($callbacks as $priority => $functions) {
                foreach ($functions as $function_data) {
                    $callback = $function_data['function'];
                    $source = $this->identify_callback_source($callback);
                    if ($source) {
                        $this->plugin_hooks_cache[$hook] = $source;
                        return $source;
                    }
                }
            }
        }

        return __('Unknown', 'divewp-boost-site-performance');
    }

    /**
     * Build cache of plugin hooks
     *
     * @since 2.2.0
     * @return void
     */
    public function build_plugin_hooks_cache() {
        if (!empty($this->plugin_hooks_cache)) {
            return;
        }

        // Get all active plugins
        $active_plugins = get_option('active_plugins', array());
        
        foreach ($active_plugins as $plugin) {
            $plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
            if (!file_exists($plugin_path)) {
                continue;
            }

            $plugin_data = get_plugin_data($plugin_path, false, false);
            $plugin_name = $plugin_data['Name'];
            $plugin_slug = dirname($plugin);

            // Add common hook patterns for this plugin
            $this->plugin_hooks_cache[$plugin_slug . '_'] = $plugin_name;

            // Also map by name for direct lookups
            $this->plugin_hooks_cache['name:' . $plugin_name] = array(
                'status' => 'active',
                'type'   => 'plugin'
            );
        }

        // Add active theme to cache
        $theme = wp_get_theme();
        if ($theme->exists()) {
            $this->plugin_hooks_cache['name:' . $theme->get('Name')] = array(
                'status' => 'active',
                'type'   => 'theme'
            );
            $this->plugin_hooks_cache[$theme->get_stylesheet() . '_'] = $theme->get('Name');
        }
    }

    /**
     * Validate the health status of a task with 100% certainty where possible
     * 
     * @since 2.2.0
     * @param string $hook      Hook name
     * @param string $source    Detected source plugin/theme name
     * @param bool   $orphaned  Initial orphan flag (based on callbacks)
     * @return array Validation data {status, message, certainty}
     */
    public function validate_task_health($hook, $source, $orphaned) {
        $system_tasks = array(__('WordPress Core', 'divewp-boost-site-performance'), 'Action Scheduler');
        
        // 1. System tasks are never orphaned
        if (in_array($source, $system_tasks, true)) {
            return array(
                'status'    => 'system',
                'message'   => __('WordPress system task.', 'divewp-boost-site-performance'),
                'certainty' => 100
            );
        }

        // 2. Check if source is an active plugin or theme
        $source_key = 'name:' . $source;
        $is_source_active = isset($this->plugin_hooks_cache[$source_key]) && $this->plugin_hooks_cache[$source_key]['status'] === 'active';

        if ($is_source_active) {
            return array(
                'status'    => 'healthy',
                /* translators: %s: Plugin or theme name detected as the task source */
                'message'   => sprintf(__('Source "%s" is active. Task is likely lazy-loaded.', 'divewp-boost-site-performance'), $source),
                'certainty' => 90
            );
        }

        // 3. "Proof of Life" - check execution logs
        if ($this->check_recent_execution_proof($hook)) {
            return array(
                'status'    => 'healthy',
                'message'   => __('Task successfully executed recently.', 'divewp-boost-site-performance'),
                'certainty' => 100
            );
        }

        // 4. If source is recognized but NOT active
        if ($source !== __('Unknown', 'divewp-boost-site-performance') && !$is_source_active) {
            // Check if it's a deactivated plugin
            $all_plugins = get_plugins();
            foreach ($all_plugins as $plugin_file => $plugin_data) {
                if ($plugin_data['Name'] === $source) {
                    return array(
                        'status'    => 'confirmed_orphan',
                        /* translators: %s: Plugin name detected as the task source */
                        'message'   => sprintf(__('Source plugin "%s" is deactivated.', 'divewp-boost-site-performance'), $source),
                        'certainty' => 100
                    );
                }
            }
        }

        // 5. Final fallback
        if ($orphaned && $source === __('Unknown', 'divewp-boost-site-performance')) {
            return array(
                'status'    => 'potential_orphan',
                'message'   => __('No registered callback or recognized source found.', 'divewp-boost-site-performance'),
                'certainty' => 70
            );
        }

        return array(
            'status'    => 'healthy',
            'message'   => __('Task has registered callbacks.', 'divewp-boost-site-performance'),
            'certainty' => 100
        );
    }

    /**
     * Check for recent successful execution as proof of life
     * 
     * @since 2.2.0
     * @param string $hook Hook name
     * @return bool True if successful run in last 24 hours
     */
    private function check_recent_execution_proof($hook) {
        $db = DiveWP_DB_Access::get_instance();
        // Check logs from last 24 hours
        $logs = $db->get_recent_cron_logs(5, 0, 'success', $hook);
        
        if (empty($logs)) {
            return false;
        }

        foreach ($logs as $log) {
            $started = strtotime($log->started_at);
            if ($started > (time() - DAY_IN_SECONDS)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Identify callback source plugin
     *
     * @since 2.2.0
     * @param mixed $callback Callback function/method
     * @return string|false Plugin name or false
     */
    private function identify_callback_source($callback) {
        if (is_string($callback)) {
            // Function name - try to find file
            if (function_exists($callback)) {
                $reflector = new ReflectionFunction($callback);
                $file = $reflector->getFileName();
                return $this->get_plugin_from_file($file);
            }
        } elseif (is_array($callback) && count($callback) === 2) {
            // Class method
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            if (class_exists($class)) {
                $reflector = new ReflectionClass($class);
                $file = $reflector->getFileName();
                return $this->get_plugin_from_file($file);
            }
        } elseif (is_object($callback) && $callback instanceof Closure) {
            $reflector = new ReflectionFunction($callback);
            $file = $reflector->getFileName();
            return $this->get_plugin_from_file($file);
        }

        return false;
    }

    /**
     * Get plugin name from file path
     *
     * @since 2.2.0
     * @param string $file File path
     * @return string|false Plugin name or false
     */
    private function get_plugin_from_file($file) {
        if (!$file) {
            return false;
        }

        // Check if it's in wp-content/plugins
        $plugins_dir = WP_PLUGIN_DIR;
        if (strpos($file, $plugins_dir) === 0) {
            $relative = substr($file, strlen($plugins_dir) + 1);
            $parts = explode('/', $relative);
            $plugin_folder = $parts[0];

            // Get plugin name from folder
            $plugins = get_plugins();
            foreach ($plugins as $plugin_file => $plugin_data) {
                if (strpos($plugin_file, $plugin_folder . '/') === 0) {
                    return $plugin_data['Name'];
                }
            }
            return $plugin_folder;
        }

        // Check if it's in wp-includes (core)
        if (strpos($file, ABSPATH . 'wp-includes') === 0) {
            return __('WordPress Core', 'divewp-boost-site-performance');
        }

        // Check if it's a theme
        $themes_dir = get_theme_root();
        if (strpos($file, $themes_dir) === 0) {
            return __('Theme', 'divewp-boost-site-performance');
        }

        return false;
    }

    /**
     * Format next run time
     *
     * @since 2.2.0
     * @param int $timestamp Unix timestamp
     * @param int $current_time Current Unix timestamp
     * @return string Formatted date/time
     */
    private function format_next_run($timestamp, $current_time) {
        $format = get_option('date_format') . ' ' . get_option('time_format');
        return wp_date($format, $timestamp);
    }

    /**
     * Get human-readable schedule label
     *
     * @since 2.2.0
     * @param string|false $schedule Schedule name
     * @param int          $interval Interval in seconds
     * @return string Schedule label
     */
    private function get_schedule_label($schedule, $interval) {
        if (!$schedule) {
            return __('One-time', 'divewp-boost-site-performance');
        }

        $schedules = wp_get_schedules();
        
        if (isset($schedules[$schedule])) {
            return $schedules[$schedule]['display'];
        }

        // Custom interval - show human readable
        if ($interval > 0) {
            return sprintf(
                /* translators: %s: Time interval */
                __('Every %s', 'divewp-boost-site-performance'),
                human_time_diff(0, $interval)
            );
        }

        return $schedule;
    }

    /**
     * Get Action Scheduler status label
     *
     * @since 2.2.0
     * @param string $status Status string
     * @return string Human-readable label
     */
    private function get_as_status_label($status) {
        $labels = array(
            'pending' => __('Pending', 'divewp-boost-site-performance'),
            'in-progress' => __('In Progress', 'divewp-boost-site-performance'),
            'complete' => __('Complete', 'divewp-boost-site-performance'),
            'failed' => __('Failed', 'divewp-boost-site-performance'),
            'canceled' => __('Canceled', 'divewp-boost-site-performance'),
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Format arguments for display
     *
     * @since 2.2.0
     * @param array $args Arguments array
     * @return string Formatted arguments
     */
    private function format_args_display($args) {
        if (empty($args)) {
            return __('None', 'divewp-boost-site-performance');
        }

        if (is_array($args)) {
            // For simple arrays, show inline
            if (count($args) <= 2) {
                $formatted = array();
                foreach ($args as $key => $value) {
                    if (is_numeric($key)) {
                        $formatted[] = is_scalar($value) ? $value : gettype($value);
                    } else {
                        $formatted[] = $key . ': ' . (is_scalar($value) ? $value : gettype($value));
                    }
                }
                return implode(', ', $formatted);
            }
            
            // For complex arrays, show count
            return sprintf(
                /* translators: %d: Number of arguments */
                __('%d arguments', 'divewp-boost-site-performance'),
                count($args)
            );
        }

        return (string) $args;
    }

    /**
     * Calculate overall health status
     *
     * @since 2.2.0
     * @param array $status System status
     * @return string Health status (good, warning, critical)
     */
    private function calculate_health_status($status) {
        // Critical issues
        if (!$status['wp_cron_disabled'] && $status['last_cron_timestamp'] > 0) {
            $time_since_last_cron = time() - $status['last_cron_timestamp'];
            if ($time_since_last_cron > 3600) { // 1 hour
                return 'critical';
            }
        }

        // Get overdue count
        $events = $this->get_wp_cron_events();
        $overdue_count = count(array_filter($events, function($e) {
            return $e['is_overdue'];
        }));

        if ($overdue_count > 10) {
            return 'critical';
        }

        // Warnings
        if ($overdue_count > 0) {
            return 'warning';
        }

        if (!$status['wp_cron_disabled']) {
            return 'warning'; // Recommend server cron
        }

        return 'good';
    }

    /**
     * Get recommendations based on status
     *
     * @since 2.2.0
     * @param array $status System status
     * @return array Array of recommendations
     */
    private function get_recommendations($status) {
        $recommendations = array();

        if (!$status['wp_cron_disabled']) {
            $recommendations[] = array(
                'type' => 'info',
                'title' => __('Consider Server Cron', 'divewp-boost-site-performance'),
                'message' => __('For better reliability, consider disabling WP-Cron and setting up a server-side cron job.', 'divewp-boost-site-performance'),
            );
        }

        if ($status['alternate_cron']) {
            $recommendations[] = array(
                'type' => 'info',
                'title' => __('Alternate Cron Active', 'divewp-boost-site-performance'),
                'message' => __('Alternate WP-Cron is enabled. This uses redirects which may add latency.', 'divewp-boost-site-performance'),
            );
        }

        // Check for orphaned hooks
        $orphaned = $this->get_orphaned_hooks();
        if (count($orphaned) > 0) {
            $recommendations[] = array(
                'type' => 'warning',
                'title' => __('Potential Orphaned Tasks Found', 'divewp-boost-site-performance'),
                'message' => sprintf(
                    /* translators: %d: Number of orphaned tasks */
                    __('%d scheduled task(s) have no registered callback. Detection is best-effort—please verify before removal.', 'divewp-boost-site-performance'),
                    count($orphaned)
                ),
            );
        }

        return $recommendations;
    }

    /**
     * Get cron command for server setup
     *
     * @since 2.2.0
     * @return array Command snippets for various environments
     */
    public function get_server_cron_commands() {
        $site_url = site_url();
        $cron_url = $site_url . '/wp-cron.php?doing_wp_cron';

        return array(
            'wget' => sprintf('*/5 * * * * wget -q -O - %s >/dev/null 2>&1', escapeshellarg($cron_url)),
            'curl' => sprintf('*/5 * * * * curl -s %s >/dev/null 2>&1', escapeshellarg($cron_url)),
            'wp_cli' => '*/5 * * * * cd ' . ABSPATH . ' && wp cron event run --due-now >/dev/null 2>&1',
            'php' => sprintf('*/5 * * * * php %swp-cron.php >/dev/null 2>&1', ABSPATH),
        );
    }

    /**
     * Get all available cron schedules
     *
     * @since 2.2.0
     * @return array Available schedules
     */
    public function get_available_schedules() {
        $schedules = wp_get_schedules();
        $formatted = array();

        foreach ($schedules as $name => $schedule) {
            $formatted[] = array(
                'name' => $name,
                'display' => $schedule['display'],
                'interval' => $schedule['interval'],
            );
        }

        // Sort by interval
        usort($formatted, function($a, $b) {
            return $a['interval'] - $b['interval'];
        });

        return $formatted;
    }
}

