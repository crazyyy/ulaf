<?php
/**
 * Cron Jobs Feature Class
 *
 * Main feature class for the Unified Cron Intelligence Dashboard.
 * Provides management for WP-Cron and Action Scheduler with execution logging.
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
 * Main Cron Jobs Feature Class
 *
 * @since 2.2.0
 */
class DiveWP_Cron_Jobs {

    /**
     * Cron data instance
     *
     * @var DiveWP_Cron_Data
     */
    private $cron_data;

    /**
     * Cron logger instance
     *
     * @var DiveWP_Cron_Logger
     */
    private $cron_logger;

    /**
     * Database access instance
     *
     * @var DiveWP_DB_Access
     */
    private $db;

    /**
     * Cached cron data for reuse across requests
     *
     * @var array|null
     */
    private $cached_cron_data = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Load required dependencies
     *
     * @since 2.2.0
     * @return void
     */
    private function load_dependencies() {
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/cron-jobs/class-cron-data.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/cron-jobs/class-cron-logger.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/cron-jobs/ajax-handlers.php';
    }

    /**
     * Initialize components
     *
     * @since 2.2.0
     * @return void
     */
    private function init_components() {
        $this->cron_data = new DiveWP_Cron_Data();
        $this->cron_logger = DiveWP_Cron_Logger::get_instance();
        $this->db = DiveWP_DB_Access::get_instance();

        // Initialize AJAX handlers
        DiveWP_Cron_Ajax::init();
    }

    /**
     * Initialize hooks
     *
     * @since 2.2.0
     * @return void
     */
    private function init_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue feature assets
     *
     * @since 2.2.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook) {
        if ('toplevel_page_divewp' !== $hook) {
            return;
        }

        // Enqueue video hero CSS (reusable template)
        wp_enqueue_style(
            'divewp-video-hero',
            DIVEWP_PLUGIN_URL . 'assets/css/video-hero.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        // Enqueue CSS
        wp_enqueue_style(
            'divewp-cron-jobs',
            DIVEWP_PLUGIN_URL . 'assets/css/features/cron-jobs.css',
            array('divewp-style', 'divewp-video-hero'),
            DIVEWP_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'divewp-cron-jobs',
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-cron-jobs.js',
            array('jquery', 'divewp-admin-js'),
            DIVEWP_VERSION,
            true
        );

        // Localize script
        $schedules_for_js = array();
        $wp_schedules = wp_get_schedules();
        foreach ( $wp_schedules as $schedule_key => $schedule_data ) {
            $key = sanitize_key( $schedule_key );
            if ( empty( $key ) ) {
                continue;
            }
            $label = isset( $schedule_data['display'] ) ? wp_strip_all_tags( (string) $schedule_data['display'] ) : '';
            if ( $label === '' ) {
                continue;
            }
            $schedules_for_js[ $key ] = $label;
        }

        wp_localize_script('divewp-cron-jobs', 'divewpCronData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('divewp_cron_nonce'),
            'schedules' => $schedules_for_js,
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this scheduled task?', 'divewp-boost-site-performance'),
                'confirmBulkDelete' => __('Are you sure you want to delete the selected tasks?', 'divewp-boost-site-performance'),
                'confirmRunNow' => __('Run this task immediately?', 'divewp-boost-site-performance'),
                'loading' => __('Loading...', 'divewp-boost-site-performance'),
                'error' => __('An error occurred. Please try again.', 'divewp-boost-site-performance'),
                'success' => __('Operation completed successfully.', 'divewp-boost-site-performance'),
                'copied' => __('Copied to clipboard!', 'divewp-boost-site-performance'),
                'noTasks' => __('No scheduled tasks found.', 'divewp-boost-site-performance'),
                'noLogs' => __('No execution logs found.', 'divewp-boost-site-performance'),
                'addTaskTitle' => __( 'Add Task', 'divewp-boost-site-performance' ),
                'saveTask' => __( 'Save Task', 'divewp-boost-site-performance' ),
                'cancel' => __( 'Cancel', 'divewp-boost-site-performance' ),
                'hookLabel' => __( 'Hook name', 'divewp-boost-site-performance' ),
                'hookPlaceholder' => __( 'example_hook_name', 'divewp-boost-site-performance' ),
                'hookHelp' => __( 'Letters, numbers, and underscores only.', 'divewp-boost-site-performance' ),
                'runAtLabel' => __( 'Run at', 'divewp-boost-site-performance' ),
                'runAtHelp' => __( 'Uses your current browser time.', 'divewp-boost-site-performance' ),
                'scheduleLabel' => __( 'Schedule', 'divewp-boost-site-performance' ),
                'scheduleOnce' => __( 'One-time', 'divewp-boost-site-performance' ),
                'argsLabel' => __( 'Arguments (JSON)', 'divewp-boost-site-performance' ),
                'argsPlaceholder' => __( '[]', 'divewp-boost-site-performance' ),
                'argsHelp' => __( 'Optional. Example: [\"abc\", 123]', 'divewp-boost-site-performance' ),
                'errorHookRequired' => __( 'Hook name is required.', 'divewp-boost-site-performance' ),
                'errorHookInvalid' => __( 'Only letters, numbers, and underscores are allowed.', 'divewp-boost-site-performance' ),
                'errorRunAtRequired' => __( 'Run time is required.', 'divewp-boost-site-performance' ),
                'errorRunAtInvalid' => __( 'Invalid date/time.', 'divewp-boost-site-performance' ),
                'errorRunAtPast' => __( 'Please choose a future time.', 'divewp-boost-site-performance' ),
                'errorArgsInvalid' => __( 'Arguments must be valid JSON.', 'divewp-boost-site-performance' ),
                /* translators: %d: Number of overdue tasks (singular) */
                'task_overdue' => __('%d task overdue', 'divewp-boost-site-performance'),
                /* translators: %d: Number of overdue tasks (plural) */
                'tasks_overdue' => __('%d tasks overdue', 'divewp-boost-site-performance'),
            ),
        ));
    }

    /**
     * Get cached cron data (loads if not already cached)
     *
     * @since 2.2.0
     * @return array Cached cron data
     */
    private function get_cached_cron_data() {
        if ($this->cached_cron_data === null) {
            $this->cached_cron_data = array(
                'cron_status' => $this->cron_data->detect_cron_status(),
                'wp_cron_events' => $this->cron_data->get_wp_cron_events(),
                'as_stats' => $this->cron_data->get_action_scheduler_stats(),
            );
        }
        return $this->cached_cron_data;
    }

    /**
     * Get dashboard stats for widget display
     *
     * @since 2.2.0
     * @return array Dashboard statistics
     */
    public function get_dashboard_stats() {
        $data = $this->get_cached_cron_data();
        $wp_cron_events = $data['wp_cron_events'];
        $as_stats = $data['as_stats'];
        // Count WP-Cron events
        $wp_tasks_count = count($wp_cron_events);

        // Count overdue WP-Cron events
        $overdue_wp = count(array_filter($wp_cron_events, function($e) {
            return $e['is_overdue'];
        }));

        // Count queue tasks (Action Scheduler pending)
        $queue_tasks_count = $as_stats['available'] ? $as_stats['pending'] : 0;

        // Count overdue queue tasks
        $overdue_queue = $as_stats['available'] ? $as_stats['overdue'] : 0;

        // Total overdue
        $total_overdue = $overdue_wp + $overdue_queue;

        return array(
            'wp_tasks'   => $wp_tasks_count,
            'queue_tasks' => $queue_tasks_count,
            'overdue'    => $total_overdue,
        );
    }

    /**
     * Lightweight insights snapshot for Abilities/MCP.
     *
     * @since 2.2.0
     * @param int  $limit              Number of items for upcoming/overdue lists (1–20).
     * @param bool $include_all        Whether to include full WP-Cron list (trimmed) and Action Scheduler pending list.
     * @param int  $action_scheduler_limit Max items for Action Scheduler pending list (1–100).
     * @return array
     */
    public function get_insights_snapshot( $limit = 5, $include_all = false, $action_scheduler_limit = 50 ) {
        $limit = max( 1, min( 20, absint( $limit ) ) );
        $action_scheduler_limit = max( 1, min( 100, absint( $action_scheduler_limit ) ) );

        $status   = $this->cron_data->detect_cron_status();
        $events   = $this->cron_data->get_wp_cron_events();
        $as_stats = $this->cron_data->get_action_scheduler_stats();

        $now = time();

        $overdue = array_values( array_filter( $events, function( $e ) {
            return ! empty( $e['is_overdue'] );
        } ) );

        $upcoming = array_values( array_filter( $events, function( $e ) use ( $now ) {
            return empty( $e['is_overdue'] ) && $e['timestamp'] >= $now;
        } ) );

        usort( $upcoming, function( $a, $b ) {
            return $a['timestamp'] - $b['timestamp'];
        } );

        usort( $overdue, function( $a, $b ) {
            return $a['timestamp'] - $b['timestamp'];
        } );

        $summary = array(
            'total_wp_cron'            => count( $events ),
            'overdue_wp_cron'          => count( $overdue ),
            'due_soon_wp_cron'         => count( array_filter( $upcoming, function( $e ) use ( $now ) {
                return ( $e['timestamp'] - $now ) <= 1800; // next 30 minutes
            } ) ),
            'action_scheduler_pending' => $as_stats['available'] ? $as_stats['pending'] : 0,
            'action_scheduler_overdue' => $as_stats['available'] ? $as_stats['overdue'] : 0,
        );

        $upcoming = array_slice( $upcoming, 0, $limit );
        $overdue  = array_slice( $overdue, 0, $limit );

        $trim_event = function( $e ) {
            return array(
                'hook'              => $e['hook'],
                'next_run'          => isset( $e['next_run'] ) ? $e['next_run'] : '',
                'next_run_relative' => isset( $e['next_run_relative'] ) ? $e['next_run_relative'] : '',
                'schedule'          => isset( $e['schedule_label'] ) ? $e['schedule_label'] : $e['schedule'],
                'source'            => isset( $e['source'] ) ? $e['source'] : '',
                'is_orphaned'       => isset( $e['is_orphaned'] ) ? (bool) $e['is_orphaned'] : false,
                'validation'        => isset( $e['validation'] ) ? $e['validation'] : null,
            );
        };

        $upcoming = array_map( $trim_event, $upcoming );
        $overdue  = array_map( $trim_event, $overdue );

        $response = array(
            'status'           => 'success',
            'cron_status'      => $status,
            'summary'          => $summary,
            'upcoming_wp_cron' => $upcoming,
            'overdue_wp_cron'  => $overdue,
            'action_scheduler' => $as_stats,
        );

        if ( $include_all ) {
            $response['wp_cron_all'] = array_map( $trim_event, $events );

            // Pending Action Scheduler actions (lightweight list)
            $pending_actions = $this->cron_data->get_action_scheduler_actions( 'pending', $action_scheduler_limit, 0 );
            $response['action_scheduler_pending'] = $pending_actions;
        }

        return $response;
    }

    /**
     * Render the cron jobs dashboard
     *
     * @since 2.2.0
     * @return void
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        // Get cached data for rendering
        $data = $this->get_cached_cron_data();
        $cron_status = $data['cron_status'];
        $wp_cron_events = $data['wp_cron_events'];
        $as_stats = $data['as_stats'];
        // Start output
        ?>
        <div class="divewp-cron-dashboard">
            <h3><?php esc_html_e('Cron Job Manager', 'divewp-boost-site-performance'); ?></h3>
            
            <p class="divewp-cron-intro">
                <?php esc_html_e('Monitor and manage background tasks that WordPress runs automatically. These include scheduled updates, cleanup routines, and plugin-specific jobs.', 'divewp-boost-site-performance'); ?>
            </p>

            <?php $this->render_video_hero(); ?>

            <?php $this->render_hero_bar($cron_status, $wp_cron_events, $as_stats); ?>

            <?php $this->render_main_workspace($wp_cron_events, $as_stats); ?>

            <?php $this->render_diagnostics_section($cron_status); ?>
        </div>
        <?php
    }

    /**
     * Render the hero context bar
     *
     * @since 2.2.0
     * @param array $cron_status     System cron status
     * @param array $wp_cron_events  WP-Cron events
     * @param array $as_stats        Action Scheduler stats
     * @return void
     */
    private function render_hero_bar($cron_status, $wp_cron_events, $as_stats) {
        $overdue_count = count(array_filter($wp_cron_events, function($e) {
            return $e['is_overdue'];
        }));
        if ($as_stats['available']) {
            $overdue_count += $as_stats['overdue'];
        }

        $health_class = 'divewp-cron-hero__health--' . $cron_status['health'];
        $health_label = $this->get_health_label($cron_status['health']);
        ?>
        <div class="divewp-cron-hero">
            <div class="divewp-cron-hero__status">
                <div class="divewp-cron-hero__health <?php echo esc_attr($health_class); ?>">
                    <span class="divewp-cron-hero__health-indicator"></span>
                    <?php if ($cron_status['health'] !== 'good') : ?>
                        <a class="divewp-cron-hero__health-link" href="#cron-jobs-health">
                            <span class="divewp-cron-hero__health-label"><?php echo esc_html($health_label); ?></span>
                        </a>
                    <?php else : ?>
                        <span class="divewp-cron-hero__health-label"><?php echo esc_html($health_label); ?></span>
                    <?php endif; ?>
                    <?php if ($overdue_count > 0) : ?>
                    <span class="divewp-cron-hero__summary divewp-cron-hero__summary--warning">
                        <?php
                        /* translators: %d: Number of overdue tasks */
                        printf(esc_html(_n('%d task overdue', '%d tasks overdue', $overdue_count, 'divewp-boost-site-performance')), absint($overdue_count));
                        ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="divewp-cron-hero__timestamp">
                    <span class="dashicons dashicons-clock"></span>
                    <?php 
                    /* translators: %s: Current server time */
                    printf(esc_html__('Server Time: %s', 'divewp-boost-site-performance'), esc_html(wp_date('Y-m-d H:i:s'))); 
                    ?>
                    <span class="divewp-cron-hero__timezone">(<?php echo esc_html(wp_timezone_string()); ?>)</span>
                </div>
            </div>

            <div class="divewp-cron-hero__actions">
                <button type="button" class="button button-secondary divewp-cron-refresh" data-action="refresh">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh', 'divewp-boost-site-performance'); ?>
                </button>
                <button type="button" class="button button-primary divewp-cron-add" data-action="add-event">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e('Add Task', 'divewp-boost-site-performance'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render main workspace with tabs
     *
     * @since 2.2.0
     * @param array $wp_cron_events WP-Cron events
     * @param array $as_stats       Action Scheduler stats
     * @return void
     */
    private function render_main_workspace($wp_cron_events, $as_stats) {
        // Calculate overdue count for the Overdue tab
        $overdue_count = count(array_filter($wp_cron_events, function($e) {
            return $e['is_overdue'];
        }));
        if ($as_stats['available']) {
            $overdue_count += $as_stats['overdue'];
        }
        $overdue_tab_class = $overdue_count > 0 ? 'divewp-cron-tab--warning' : '';

        ?>
        <div class="divewp-cron-workspace">
            <div class="divewp-cron-tabs" role="tablist">
                <button type="button" class="divewp-cron-tab active" role="tab" aria-selected="true" data-tab="wp-cron">
                    <?php esc_html_e('WordPress Cron Jobs', 'divewp-boost-site-performance'); ?>
                    <span class="divewp-cron-tab__count"><?php echo esc_html(count($wp_cron_events)); ?></span>
                </button>
                <?php if ($as_stats['available']) : ?>
                <button type="button" class="divewp-cron-tab" role="tab" aria-selected="false" data-tab="action-scheduler">
                    <?php esc_html_e('Action Scheduler Queue', 'divewp-boost-site-performance'); ?>
                    <span class="divewp-cron-tab__count"><?php echo esc_html($as_stats['pending']); ?></span>
                </button>
                <?php endif; ?>
                <button type="button" class="divewp-cron-tab <?php echo esc_attr($overdue_tab_class); ?>" role="tab" aria-selected="false" data-tab="overdue">
                    <?php esc_html_e('Overdue', 'divewp-boost-site-performance'); ?>
                    <span class="divewp-cron-tab__count"><?php echo esc_html($overdue_count); ?></span>
                </button>
                <button type="button" class="divewp-cron-tab" role="tab" aria-selected="false" data-tab="execution-log">
                    <?php esc_html_e('Execution Log', 'divewp-boost-site-performance'); ?>
                </button>
            </div>

            <div class="divewp-cron-toolbar">
                <div class="divewp-cron-toolbar__search">
                    <input type="text" class="divewp-cron-search" placeholder="<?php esc_attr_e('Search tasks...', 'divewp-boost-site-performance'); ?>">
                </div>
                <div class="divewp-cron-toolbar__filters">
                    <select class="divewp-cron-filter" data-filter="source">
                        <option value=""><?php esc_html_e('All Sources', 'divewp-boost-site-performance'); ?></option>
                        <option value="wordpress-core"><?php esc_html_e('WordPress Core', 'divewp-boost-site-performance'); ?></option>
                        <option value="plugin"><?php esc_html_e('Plugins', 'divewp-boost-site-performance'); ?></option>
                    </select>
                    <select class="divewp-cron-filter" data-filter="status">
                        <option value=""><?php esc_html_e('All Status', 'divewp-boost-site-performance'); ?></option>
                        <option value="scheduled"><?php esc_html_e('Scheduled', 'divewp-boost-site-performance'); ?></option>
                        <option value="overdue"><?php esc_html_e('Overdue', 'divewp-boost-site-performance'); ?></option>
                    </select>
                </div>
                <div class="divewp-cron-toolbar__bulk">
                    <select class="divewp-cron-bulk-action">
                        <option value=""><?php esc_html_e('Bulk Actions', 'divewp-boost-site-performance'); ?></option>
                        <option value="delete"><?php esc_html_e('Delete', 'divewp-boost-site-performance'); ?></option>
                    </select>
                    <button type="button" class="button divewp-cron-bulk-apply" disabled>
                        <?php esc_html_e('Apply', 'divewp-boost-site-performance'); ?>
                    </button>
                </div>
            </div>

            <!-- WP-Cron Events Tab -->
            <div class="divewp-cron-panel active" data-panel="wp-cron" role="tabpanel">
                <?php $this->render_wp_cron_table($wp_cron_events); ?>
            </div>

            <!-- Action Scheduler Tab -->
            <?php if ($as_stats['available']) : ?>
            <div class="divewp-cron-panel" data-panel="action-scheduler" role="tabpanel">
                <div class="divewp-cron-as-placeholder">
                    <p><?php esc_html_e('Loading Action Scheduler queue...', 'divewp-boost-site-performance'); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Overdue Tab -->
            <div class="divewp-cron-panel" data-panel="overdue" role="tabpanel">
                <div class="divewp-cron-overdue-content">
                    <?php $this->render_overdue_tasks($wp_cron_events, $as_stats); ?>
                </div>
            </div>

            <!-- Execution Log Tab -->
            <div class="divewp-cron-panel" data-panel="execution-log" role="tabpanel">
                <div class="divewp-cron-log-placeholder">
                    <p><?php esc_html_e('Loading execution logs...', 'divewp-boost-site-performance'); ?></p>
                </div>
            </div>
        </div>

        <!-- Task Detail Drawer -->
        <div class="divewp-cron-drawer" aria-hidden="true">
            <div class="divewp-cron-drawer__overlay"></div>
            <div class="divewp-cron-drawer__panel">
                <div class="divewp-cron-drawer__header">
                    <h4 class="divewp-cron-drawer__title"><?php esc_html_e('Task Details', 'divewp-boost-site-performance'); ?></h4>
                    <button type="button" class="divewp-cron-drawer__close">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="divewp-cron-drawer__content">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="divewp-cron-drawer__footer">
                    <button type="button" class="button button-primary divewp-cron-drawer__action" data-action="run-now">
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php esc_html_e('Run Now', 'divewp-boost-site-performance'); ?>
                    </button>
                    <button type="button" class="button divewp-cron-drawer__action" data-action="delete">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Delete', 'divewp-boost-site-performance'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render WP-Cron events table
     *
     * @since 2.2.0
     * @param array $events WP-Cron events
     * @return void
     */
    private function render_wp_cron_table($events) {
        if (empty($events)) {
            ?>
            <div class="divewp-cron-empty">
                <span class="dashicons dashicons-calendar-alt"></span>
                <p><?php esc_html_e('No scheduled WordPress tasks found.', 'divewp-boost-site-performance'); ?></p>
            </div>
            <?php
            return;
        }

        // Paginate - show 30 by default
        $total_count = count($events);
        $limit = 30;
        $has_more = $total_count > $limit;
        $events_paged = array_slice($events, 0, $limit);

        ?>
        <div class="divewp-cron-wp-content" data-offset="<?php echo esc_attr($limit); ?>">
        <div class="divewp-cron-list-table">
            <div class="divewp-cron-table-header divewp-cron-table-header--wp">
                <div class="col-check">
                    <input type="checkbox" class="divewp-cron-select-all">
                </div>
                <div class="col-hook"><?php esc_html_e('Task Name', 'divewp-boost-site-performance'); ?></div>
                <div class="col-next"><?php esc_html_e('Next Run', 'divewp-boost-site-performance'); ?></div>
                <div class="col-schedule"><?php esc_html_e('Recurrence', 'divewp-boost-site-performance'); ?></div>
                <div class="col-source"><?php esc_html_e('Source', 'divewp-boost-site-performance'); ?></div>
                <div class="col-actions"><?php esc_html_e('Actions', 'divewp-boost-site-performance'); ?></div>
            </div>
            <div class="divewp-cron-table-body">
                <?php foreach ($events_paged as $event) : ?>
                <div class="divewp-cron-row divewp-cron-row--wp <?php echo $event['is_overdue'] ? 'divewp-cron-row--overdue' : ''; ?> <?php echo $event['is_orphaned'] ? 'divewp-cron-row--orphaned' : ''; ?>"
                    data-hook="<?php echo esc_attr($event['hook']); ?>"
                    data-sig="<?php echo esc_attr($event['sig']); ?>"
                    data-timestamp="<?php echo esc_attr($event['timestamp']); ?>"
                    data-source="<?php echo esc_attr($event['source']); ?>"
                    data-status="<?php echo esc_attr($event['is_overdue'] ? 'overdue' : 'scheduled'); ?>">
                    <div class="col-check">
                        <input type="checkbox" class="divewp-cron-select" value="<?php echo esc_attr($event['hook'] . '|' . $event['sig']); ?>">
                    </div>
                    <div class="col-hook">
                        <strong class="divewp-cron-hook-name"><?php echo esc_html($event['hook']); ?></strong>
                        <?php 
                        $val = isset($event['validation']) ? $event['validation'] : array('status' => 'healthy');
                        if ($val['status'] === 'potential_orphan') : ?>
                            <span class="status-pill status-pill-warning"><?php esc_html_e('Potential orphan', 'divewp-boost-site-performance'); ?></span>
                        <?php elseif ($val['status'] === 'confirmed_orphan') : ?>
                            <span class="status-pill status-pill-danger"><?php esc_html_e('Orphaned', 'divewp-boost-site-performance'); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($event['args'])) : ?>
                            <span class="divewp-cron-args" title="<?php echo esc_attr($event['args_display']); ?>">
                                <?php echo esc_html($event['args_display']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="col-next">
                        <span class="divewp-cron-next-run <?php echo $event['is_overdue'] ? 'divewp-cron-next-run--overdue' : ''; ?>">
                            <?php echo esc_html($event['next_run']); ?>
                        </span>
                        <span class="divewp-cron-relative">
                            <?php 
                            if ($event['is_overdue']) {
                                /* translators: %s: Time since task was due */
                                printf(esc_html__('%s overdue', 'divewp-boost-site-performance'), esc_html($event['next_run_relative']));
                            } else {
                                /* translators: %s: Time until next run */
                                printf(esc_html__('in %s', 'divewp-boost-site-performance'), esc_html($event['next_run_relative']));
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-schedule">
                        <span class="divewp-cron-schedule"><?php echo esc_html($event['schedule_label']); ?></span>
                    </div>
                    <div class="col-source">
                        <span class="divewp-cron-source"><?php echo esc_html($event['source']); ?></span>
                    </div>
                    <div class="col-actions">
                        <button type="button" class="button button-small divewp-cron-action" data-action="run-now" title="<?php esc_attr_e('Run Now', 'divewp-boost-site-performance'); ?>">
                            <span class="dashicons dashicons-controls-play"></span>
                        </button>
                        <button type="button" class="button button-small divewp-cron-action" data-action="view" title="<?php esc_attr_e('View Details', 'divewp-boost-site-performance'); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <button type="button" class="button button-small divewp-cron-action" data-action="delete" title="<?php esc_attr_e('Delete', 'divewp-boost-site-performance'); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($has_more) : ?>
        <div class="divewp-cron-load-more-wrap">
            <button type="button" class="button divewp-cron-load-more" data-tab="wp-cron">
                <?php esc_html_e('Load More', 'divewp-boost-site-performance'); ?>
            </button>
        </div>
        <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render overdue tasks panel
     *
     * @since 2.2.0
     * @param array $wp_cron_events WP-Cron events
     * @param array $as_stats       Action Scheduler stats
     * @return void
     */
    private function render_overdue_tasks($wp_cron_events, $as_stats) {
        // Filter to only overdue WP-Cron events
        $overdue_events = array_filter($wp_cron_events, function($e) {
            return $e['is_overdue'];
        });
        
        // Fetch overdue Action Scheduler tasks if available
        $overdue_as_actions = array();
        if ($as_stats['available'] && $as_stats['overdue'] > 0) {
            $all_as_actions = $this->cron_data->get_action_scheduler_actions('pending', 100, 0);
            $overdue_as_actions = array_filter($all_as_actions, function($action) {
                return $action['is_overdue'] === true;
            });
        }
        
        $total_overdue = count($overdue_events) + count($overdue_as_actions);

        if ($total_overdue === 0) {
            ?>
            <div class="divewp-cron-empty">
                <span class="dashicons dashicons-yes-alt"></span>
                <p><?php esc_html_e('No overdue tasks. All scheduled jobs are running on time.', 'divewp-boost-site-performance'); ?></p>
            </div>
            <?php
            return;
        }

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
                            <?php 
                            $val = isset($action['validation']) ? $action['validation'] : array('status' => 'healthy');
                            if ($val['status'] === 'potential_orphan') : ?>
                                <span class="status-pill status-pill-warning"><?php esc_html_e('Potential orphan', 'divewp-boost-site-performance'); ?></span>
                            <?php elseif ($val['status'] === 'confirmed_orphan') : ?>
                                <span class="status-pill status-pill-danger"><?php esc_html_e('Orphaned', 'divewp-boost-site-performance'); ?></span>
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

    /**
     * Render diagnostics section
     *
     * @since 2.2.0
     * @param array $cron_status    System cron status
     * @return void
     */
    private function render_diagnostics_section($cron_status) {
        $commands = $this->cron_data->get_server_cron_commands();
        $wp_cron_enabled = !$cron_status['wp_cron_disabled'];
        $alt_cron_active = $cron_status['alternate_cron'];
        ?>
        <div class="divewp-cron-diagnostics" id="divewp-cron-health">
            <a id="cron-jobs-health"></a>
            <h4><?php esc_html_e('System Health & Configuration', 'divewp-boost-site-performance'); ?></h4>

            <!-- Status Info Grid -->
            <div class="divewp-cron-info-grid">
                <div class="divewp-cron-info-item <?php echo $wp_cron_enabled ? 'divewp-cron-info-item--success' : 'divewp-cron-info-item--neutral'; ?>">
                    <div class="divewp-cron-info-item__icon">
                        <span class="dashicons <?php echo $wp_cron_enabled ? 'dashicons-yes-alt' : 'dashicons-dismiss'; ?>"></span>
                    </div>
                    <div class="divewp-cron-info-item__content">
                        <span class="divewp-cron-info-item__label"><?php esc_html_e('WP-Cron', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-cron-info-item__value"><?php echo $wp_cron_enabled ? esc_html__('Enabled', 'divewp-boost-site-performance') : esc_html__('Disabled', 'divewp-boost-site-performance'); ?></span>
                    </div>
                </div>

                <div class="divewp-cron-info-item <?php echo $alt_cron_active ? 'divewp-cron-info-item--success' : 'divewp-cron-info-item--neutral'; ?>">
                    <div class="divewp-cron-info-item__icon">
                        <span class="dashicons <?php echo $alt_cron_active ? 'dashicons-update' : 'dashicons-minus'; ?>"></span>
                    </div>
                    <div class="divewp-cron-info-item__content">
                        <span class="divewp-cron-info-item__label"><?php esc_html_e('Alternate Cron', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-cron-info-item__value"><?php echo $alt_cron_active ? esc_html__('Active', 'divewp-boost-site-performance') : esc_html__('Inactive', 'divewp-boost-site-performance'); ?></span>
                    </div>
                </div>

                <div class="divewp-cron-info-item">
                    <div class="divewp-cron-info-item__icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="divewp-cron-info-item__content">
                        <span class="divewp-cron-info-item__label"><?php esc_html_e('Max Execution', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-cron-info-item__value"><?php echo esc_html($cron_status['php_max_execution_time']); ?>s</span>
                    </div>
                </div>
            </div>

            <p class="divewp-cron-footnote">
                <?php esc_html_e('* Alternate Cron is a fallback that triggers cron via page requests when loopback requests fail; enable it only when standard WP-Cron calls cannot run.', 'divewp-boost-site-performance'); ?>
            </p>

            <!-- Recommendations -->
            <?php if (!empty($cron_status['recommendations'])) : ?>
            <div class="divewp-cron-recommendations">
                <?php foreach ($cron_status['recommendations'] as $rec) : 
                    $is_info = $rec['type'] === 'info';
                    $icon = $is_info ? 'dashicons-lightbulb' : 'dashicons-warning';
                ?>
                <div class="divewp-cron-recommendation divewp-cron-recommendation--<?php echo esc_attr($is_info ? 'info' : 'warning'); ?>">
                    <div class="divewp-cron-recommendation__icon">
                        <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                    </div>
                    <div class="divewp-cron-recommendation__content">
                        <strong class="divewp-cron-recommendation__title"><?php echo esc_html($rec['title']); ?></strong>
                        <p class="divewp-cron-recommendation__message"><?php echo esc_html($rec['message']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Server Cron Setup -->
            <div class="divewp-cron-setup">
                <h5 class="divewp-cron-setup__title">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Server Cron Setup', 'divewp-boost-site-performance'); ?>
                </h5>
                <p class="divewp-cron-setup__intro">
                    <?php esc_html_e('For reliable task execution, disable WP-Cron and use a server-side cron job. Add one of these commands to your server\'s crontab:', 'divewp-boost-site-performance'); ?>
                </p>
                
                <div class="divewp-cron-commands-grid">
                    <div class="divewp-cron-command">
                        <div class="divewp-cron-command__header">
                            <span class="dashicons dashicons-editor-code"></span>
                            <label><?php esc_html_e('wget', 'divewp-boost-site-performance'); ?></label>
                        </div>
                        <div class="divewp-cron-command__code">
                            <code><?php echo esc_html($commands['wget']); ?></code>
                            <button type="button" class="divewp-cron-copy" data-copy="<?php echo esc_attr($commands['wget']); ?>" title="<?php esc_attr_e('Copy to clipboard', 'divewp-boost-site-performance'); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="divewp-cron-command">
                        <div class="divewp-cron-command__header">
                            <span class="dashicons dashicons-editor-code"></span>
                            <label><?php esc_html_e('curl', 'divewp-boost-site-performance'); ?></label>
                        </div>
                        <div class="divewp-cron-command__code">
                            <code><?php echo esc_html($commands['curl']); ?></code>
                            <button type="button" class="divewp-cron-copy" data-copy="<?php echo esc_attr($commands['curl']); ?>" title="<?php esc_attr_e('Copy to clipboard', 'divewp-boost-site-performance'); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="divewp-cron-command">
                        <div class="divewp-cron-command__header">
                            <span class="dashicons dashicons-editor-code"></span>
                            <label><?php esc_html_e('WP-CLI', 'divewp-boost-site-performance'); ?></label>
                        </div>
                        <div class="divewp-cron-command__code">
                            <code><?php echo esc_html($commands['wp_cli']); ?></code>
                            <button type="button" class="divewp-cron-copy" data-copy="<?php echo esc_attr($commands['wp_cli']); ?>" title="<?php esc_attr_e('Copy to clipboard', 'divewp-boost-site-performance'); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="divewp-cron-setup-note">
                    <div class="divewp-cron-setup-note__icon">
                        <span class="dashicons dashicons-info"></span>
                    </div>
                    <div class="divewp-cron-setup-note__content">
                        <p>
                            <strong><?php esc_html_e('Note:', 'divewp-boost-site-performance'); ?></strong>
                            <?php esc_html_e('Add this line to wp-config.php to disable the default WP-Cron:', 'divewp-boost-site-performance'); ?>
                        </p>
                        <code>define('DISABLE_WP_CRON', true);</code>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get health status label
     *
     * @since 2.2.0
     * @param string $health Health status
     * @return string Health label
     */
    private function get_health_label($health) {
        $labels = array(
            'good' => __('Healthy', 'divewp-boost-site-performance'),
            'warning' => __('Needs Attention', 'divewp-boost-site-performance'),
            'critical' => __('Critical Issues', 'divewp-boost-site-performance'),
        );

        return isset($labels[$health]) ? $labels[$health] : __('Unknown', 'divewp-boost-site-performance');
    }

    /**
     * Render video hero section using reusable template
     *
     * @since 2.2.0
     * @return void
     */
    private function render_video_hero() {
        // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables passed to included template
        // Set template variables
        $title       = __( 'Cron Jobs: The Invisible WordPress Todo List - Explained for non-techies!', 'divewp-boost-site-performance' );
        $description = __( 'Watch this beginner-friendly explainer video to understand how WordPress Cron Jobs work, why they matter for your site, and how to keep them running smoothly — no technical knowledge required.', 'divewp-boost-site-performance' );
        $video_id    = 'Qh84mRrkPiY';
        $features    = array(
            __( 'Easy to follow', 'divewp-boost-site-performance' ),
            __( 'Non-technical', 'divewp-boost-site-performance' ),
            __( 'Practical tips', 'divewp-boost-site-performance' ),
        );
        // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

        // Include the reusable template
        include DIVEWP_PLUGIN_DIR . 'includes/templates/video-hero-template.php';
    }
}

