<?php
/**
 * Dashboard Overview functionality for DiveWP
 *
 * @package DiveWP
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Dashboard_Overview {
    private static $instance = null;
    private $user_events;
    private $cron_jobs;

    public function __construct($cron_jobs = null) {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $this->user_events = DiveWP_User_Events::get_instance();
        $this->cron_jobs = $cron_jobs;
        add_action('admin_enqueue_scripts', array($this, 'enqueue_timeline_styles'));
    }

    /**
     * Enqueue timeline styles
     */
    public function enqueue_timeline_styles() {
        wp_enqueue_style(
            'divewp-timeline',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/features/timeline.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Get action class for styling
     */
    private function get_action_class($action) {
        $classes = array(
            'created' => 'success',
            'creation' => 'success',
            'login' => 'success',
            'activated' => 'success',
            'installed' => 'success',
            'updated' => 'info',
            'update' => 'info',
            'logout' => 'info',
            'trashed' => 'warning',
            'unpublished' => 'warning',
            'deactivated' => 'warning',
            'deletion' => 'danger',
            'deleted' => 'danger'
        );

        return isset($classes[$action]) ? $classes[$action] : 'info';
    }

    /**
     * Get appropriate icon for event type
     *
     * @param string $event_type
     * @return string HTML for dashicon
     */
    private function get_event_icon($event_type) {
        $icons = array(
            'plugin_management' => 'admin-plugins',
            'theme_management' => 'admin-appearance',
            'content' => 'admin-post',
            'user_management' => 'admin-users',
            'settings' => 'admin-settings',
            'session' => 'admin-network',
        );

        $icon = isset($icons[$event_type]) ? $icons[$event_type] : 'admin-generic';
        return sprintf('<span class="dashicons dashicons-%s"></span>', esc_attr($icon));
    }

    /**
     * Add error logging for debugging
     */
    private function log_error($message, $context = '') {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG && WP_DEBUG) {
            divewp_debug_log(
                sprintf(
                    'Dashboard Overview: %s - Context: %s',
                    $message,
                    $context
                ),
                'error'
            );
        }
    }

    /**
     * Validate event object structure
     */
    private function validate_event($event) {
        if (!is_object($event)) {
            $this->log_error('Invalid event object', 'Event Validation');
            return false;
        }

        $required_properties = array('created_at', 'event_type', 'event_action', 'description');
        foreach ($required_properties as $property) {
            if (!isset($event->$property)) {
                $this->log_error("Missing property: {$property}", 'Event Validation');
                return false;
            }
        }

        return true;
    }

    /**
     * Get recent events with validation
     */
    private function get_recent_events() {
        $events = $this->user_events->get_recent_admin_events(5);
        
        // Validate events
        return array_filter($events, function($event) {
            return $this->validate_event($event);
        });
    }

    private function validate_event_date($date) {
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
            return false;
        }
        return true;
    }

    public function render_overview_cards() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        // Verify nonce for AJAX requests
        if (wp_doing_ajax()) {
            check_ajax_referer('divewp_dashboard_nonce', 'nonce');
        }

        // Add nonce for activity log button and AJAX requests
        $nonce = wp_create_nonce('divewp_activity_log_nonce');
        $ajax_nonce = wp_create_nonce('divewp_dashboard_nonce');
        
        // Get recent events
        $recent_events = $this->get_recent_events();

        ?>
        <div class="wrap" data-nonce="<?php echo esc_attr($ajax_nonce); ?>">
            <h2 class="divewp-section-title"><?php esc_html_e('Status Overview', 'divewp-boost-site-performance'); ?></h2>
            
            <div class="divewp-dashboard-grid">
                <!-- Success Card -->
                <div class="divewp-card divewp-card-success">
                    <div class="divewp-card-header">
                        <span class="status-pill status-pill-success"><?php esc_html_e('Optimal', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-card-count">0</span>
                    </div>
                    <div class="divewp-card-body">
                        <ul class="divewp-status-list"></ul>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="divewp-card divewp-card-warning">
                    <div class="divewp-card-header">
                        <span class="status-pill status-pill-warning"><?php esc_html_e('Warnings', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-card-count">0</span>
                    </div>
                    <div class="divewp-card-body">
                        <ul class="divewp-status-list"></ul>
                    </div>
                </div>

                <!-- Danger Card -->
                <div class="divewp-card divewp-card-danger">
                    <div class="divewp-card-header">
                        <span class="status-pill status-pill-danger"><?php esc_html_e('Critical', 'divewp-boost-site-performance'); ?></span>
                        <span class="divewp-card-count">0</span>
                    </div>
                    <div class="divewp-card-body">
                        <ul class="divewp-status-list"></ul>
                    </div>
                </div>

                <?php $this->render_cron_status_widget(); ?>
            </div>

            <!-- Activity Timeline -->
            <div class="divewp-activity-timeline">
                <div class="divewp-timeline-header">
                    <h2 class="divewp-section-title">
                        <?php esc_html_e('Recent Activity Timeline', 'divewp-boost-site-performance'); ?>
                    </h2>
                </div>
                <div id="divewp-timeline-placeholder" class="divewp-timeline-content">
                    <div class="divewp-loading-container">
                        <div class="divewp-loader"></div>
                        <p><?php esc_html_e('Loading recent activities...', 'divewp-boost-site-performance'); ?></p>
                    </div>
                </div>
                <div class="divewp-timeline-footer">
                    <div class="divewp-card-footer divewp-actions">
                        <button type="button" 
                                id="divewp-view-activity-log" 
                                class="divewp-button" 
                                data-tab="user-events" 
                                data-nonce="<?php echo esc_attr($nonce); ?>">
                            <?php esc_html_e('View Full Activity Log', 'divewp-boost-site-performance'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_activity_timeline($events) {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verify nonce for AJAX requests
        if (wp_doing_ajax()) {
            check_ajax_referer('divewp_timeline_nonce', 'nonce');
        }

        echo '<div class="divewp-timeline">';
        echo '<h3>' . esc_html__('Recent Activity Timeline', 'divewp-boost-site-performance') . '</h3>';
        
        if (!empty($events)) {
            echo '<div class="timeline-items">';
            foreach ($events as $event) {
                if (!$this->validate_event($event)) {
                    continue;
                }

                echo '<div class="timeline-item">';
                echo '<div class="timeline-marker ' . esc_attr($event->event_action) . '"></div>';
                echo '<div class="timeline-time">' . esc_html(wp_date('H:i', strtotime($event->created_at))) . '</div>';
                echo '<div class="timeline-type">' . 
                     esc_html($this->user_events->get_event_type_label($event->event_type)) . 
                     '</div>';
                echo '<span class="timeline-action ' . esc_attr($event->event_action) . '">' . 
                     esc_html($this->user_events->get_action_label($event->event_action, $event->event_type)) . 
                     '</span>';
                echo '<div class="timeline-content">' . esc_html($event->description) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="divewp-no-activity">';
            echo '<span class="dashicons dashicons-marker"></span>';
            echo '<p>' . esc_html__('No recent activity recorded', 'divewp-boost-site-performance') . '</p>';
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_event_date($event) {
        if (!$this->validate_event_date($event->created_at)) {
            return '';
        }
        return esc_html(wp_date('Y-m-d', strtotime($event->created_at)));
    }

    /**
     * Render Cron Jobs status widget
     *
     * @since 2.2.0
     * @return void
     */
    private function render_cron_status_widget() {
        if (!$this->cron_jobs) {
            return;
        }

        $stats = $this->cron_jobs->get_dashboard_stats();
        ?>
        <!-- Cron Jobs Card -->
        <div class="divewp-card divewp-card-cron">
            <div class="divewp-card-header">
                <span class="status-pill status-pill-info">
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e('Cron Jobs', 'divewp-boost-site-performance'); ?>
                </span>
                <a href="#cron-jobs" class="divewp-cron-status-widget__link" data-tab="cron-jobs">
                    <?php esc_html_e('View All', 'divewp-boost-site-performance'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
            </div>
            <div class="divewp-card-body">
                <ul class="divewp-status-list">
                    <li>
                        <a href="#cron-jobs" class="divewp-tab-link" data-tab="cron-jobs">
                            <span><?php esc_html_e('WordPress Cron Jobs', 'divewp-boost-site-performance'); ?></span>
                            <span class="divewp-card-count"><?php echo esc_html($stats['wp_tasks']); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#cron-jobs" class="divewp-tab-link" data-tab="cron-jobs">
                            <span><?php esc_html_e('Action Scheduler Queue', 'divewp-boost-site-performance'); ?></span>
                            <span class="divewp-card-count"><?php echo esc_html($stats['queue_tasks']); ?></span>
                        </a>
                    </li>
                    <li class="<?php echo $stats['overdue'] > 0 ? 'divewp-status-list__warning' : ''; ?>">
                        <a href="#cron-jobs" class="divewp-tab-link" data-tab="cron-jobs">
                            <span><?php esc_html_e('Overdue Tasks', 'divewp-boost-site-performance'); ?></span>
                            <span class="divewp-card-count"><?php echo esc_html($stats['overdue']); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render timeline item with validation
     */
    private function render_timeline_item($event) {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verify nonce for AJAX requests
        if (wp_doing_ajax()) {
            check_ajax_referer('divewp_timeline_nonce', 'nonce');
        }

        if (!$this->validate_event($event)) {
            return;
        }

        $event_date = $this->render_event_date($event);
        if (empty($event_date)) {
            $this->log_error('Invalid event date', 'Timeline Rendering');
            return;
        }

        // Rest of the existing render code remains the same
        $event_time = wp_date('H:i', strtotime($event->created_at));
        ?>
        <div class="divewp-timeline-item">
            <div class="timeline-marker <?php echo esc_attr($this->get_action_class($event->event_action)); ?>">
                <?php echo wp_kses($this->get_event_icon($event->event_type), array('span' => array('class' => array()))); ?>
            </div>
            <div class="timeline-content">
                <div class="timeline-header">
                    <span class="time"><?php echo esc_html($event_time); ?></span>
                    <span class="event-type <?php echo esc_attr($event->event_type); ?>">
                        <?php echo '<div class="timeline-type">' . 
                             esc_html($this->user_events->get_event_type_label($event->event_type)) . 
                             '</div>';
                        ?>
                    </span>
                    <?php if (!empty($event->event_action)): ?>
                        <span class="status-pill status-pill-<?php echo esc_attr($this->get_action_class($event->event_action)); ?>">
                            <?php 
                            $action_label = $this->user_events->get_action_label($event->event_action, $event->event_type);
                            echo '<span class="timeline-action">' . esc_html($action_label) . '</span>';
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="timeline-body">
                    <p><?php echo esc_html($event->description); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_timeline_placeholder() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verify nonce for AJAX requests
        if (wp_doing_ajax()) {
            check_ajax_referer('divewp_timeline_nonce', 'nonce');
        }

        // Add nonce for AJAX operations
        $ajax_nonce = wp_create_nonce('divewp_timeline_nonce');
        ?>
        <div id="divewp-timeline-placeholder" class="divewp-timeline-content" data-nonce="<?php echo esc_attr($ajax_nonce); ?>">
            <div class="divewp-loading-container">
                <div class="divewp-loader"></div>
                <p><?php esc_html_e('Loading recent activities...', 'divewp-boost-site-performance'); ?></p>
            </div>
        </div>
        <?php
    }
}