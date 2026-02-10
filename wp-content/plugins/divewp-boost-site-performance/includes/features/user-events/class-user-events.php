<?php
/**
 * User Events functionality for DiveWP - Administrator Activity Tracking
 *
 * @package DiveWP
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

/**
 * Class DiveWP_User_Events
 * 
 * Handles administrator activity tracking and display functionality.
 * 
 * @package DiveWP
 * @since 1.0.0
 */
class DiveWP_User_Events {
    /**
     * Instance of this class
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Event logger instance
     *
     * @var DiveWP_Event_Logger
     */
    private $logger;

    /**
     * Get instance of this class
     *
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load the logger class
        require_once dirname(__FILE__) . '/class-event-logger.php';
        $this->logger = DiveWP_Event_Logger::get_instance();

        // Register AJAX handlers
        add_action('wp_ajax_divewp_delete_all_logs', array($this, 'ajax_delete_all_logs'));
        add_action('wp_ajax_divewp_refresh_logs', array($this, 'ajax_refresh_logs'));
        add_action('wp_ajax_divewp_load_more_events', array($this, 'ajax_load_more_events'));
        add_action('wp_ajax_divewp_load_recent_timeline', array($this, 'ajax_load_recent_timeline'));

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_divewp' !== $hook) {
            return;
        }

        // Enqueue User Events CSS
        wp_enqueue_style(
            'divewp-user-events',
            DIVEWP_PLUGIN_URL . 'assets/css/features/user-events.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Get user events data for display
     *
     * @param int $page     Page number
     * @param int $per_page Items per page
     * @return array|false Array of events or false on error
     */
    public function get_user_events_data($page = 1, $per_page = 10) {
        try {
            $events = $this->logger->get_recent_events($per_page);
            return $events;
        } catch (Exception $e) {
            $this->log_error('Failed to get user events', array(
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $per_page
            ));
            return false;
        }
    }

    /**
     * Get total number of events
     */
    public function get_total_events() {
        return $this->logger->get_total_events();
    }

    /**
     * Verify admin access and nonce with proper sanitization
     *
     * @param string $nonce_action Optional nonce action
     * @return bool Whether access is verified
     */
    private function verify_admin_access($nonce_action = 'divewp_display') {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        if (!isset($_REQUEST['_wpnonce'])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
        return wp_verify_nonce($nonce, $nonce_action);
    }

    /**
     * Render user events data with security checks
     *
     * @param array $user_events_data Array of user events to display
     * @return void
     */
    public function render_user_events_data($user_events_data) {
        // Add capability check
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }
        
        $total = $this->get_total_events();
        $per_page = 10;
        $total_pages = ceil($total / $per_page);

        echo '<div id="divewp-logs-container">';
        /* translators: Title for the administrator activity log section */
        echo '<h3>' . esc_html__('Administrator Activity Log', 'divewp-boost-site-performance') . '</h3>';

        // Records cleanup notice
            echo '<div class="divewp-header-actions divewp-notice divewp-notice-warning">';
            $this->display_cleanup_status();
            echo '<div class="divewp-actions">';
            /* translators: Button text to refresh the activity logs */
            echo '<button id="divewp-refresh-logs" class="button button-primary" style="margin-right: 10px;">' . 
                 esc_html__('Refresh Logs', 'divewp-boost-site-performance') . 
                 '</button>';
            /* translators: Button text to delete all activity logs */
            echo '<button id="divewp-delete-all-logs" class="button button-secondary" data-noconfirm="true">' . 
                 esc_html__('Delete All Logs', 'divewp-boost-site-performance') . 
                 '</button>';
            echo '</div>';
            echo '</div>';

        if (empty($user_events_data)) {
            echo '<div class="divewp-no-activity">';
            echo '<span class="dashicons dashicons-clipboard"></span>';
            /* translators: Message shown when no administrator activities have been recorded */
            echo '<p>' . esc_html__('No administrator activities have been logged yet.', 'divewp-boost-site-performance') . '</p>';
            echo '</div>';
            echo '</div>';
            return;
        }

        // Table view
        echo '<div class="divewp-table-container">';
        echo '<table class="divewp-table divewp-user-events-table">';
        echo '<thead>';
        echo '<tr>';
        /* translators: Column header for administrator name in activity log */
        echo '<th>' . esc_html__('Administrator', 'divewp-boost-site-performance') . '</th>';
        /* translators: Column header for activity area (e.g., Themes, Plugins) in activity log */
        echo '<th>' . esc_html__('Activity Area', 'divewp-boost-site-performance') . '</th>';
        /* translators: Column header for action taken (e.g., Installed, Deleted) in activity log */
        echo '<th>' . esc_html__('Action', 'divewp-boost-site-performance') . '</th>';
        /* translators: Column header for detailed description in activity log */
        echo '<th>' . esc_html__('Details', 'divewp-boost-site-performance') . '</th>';
        /* translators: Column header for date and time in activity log */
        echo '<th>' . esc_html__('Date & Time', 'divewp-boost-site-performance') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody id="divewp-events-tbody">';

        foreach ($user_events_data as $event) {
            echo '<tr>';
            echo '<td>' . esc_html($event->user_login) . '</td>';
            echo '<td>' . esc_html($this->get_event_type_label($event->event_type)) . '</td>';
            echo '<td>';
            echo '<span class="status-pill status-pill-' . esc_attr($this->get_action_class($event->event_action)) . '">';
            echo esc_html($this->get_action_label($event->event_action, $event->event_type));
            echo '</span>';
            echo '</td>';
            echo '<td>' . esc_html($event->description) . '</td>';
            echo '<td>' . esc_html(wp_date('Y-m-d H:i:s', strtotime($event->created_at))) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        // Pagination
        if ($total_pages > 1) {
            echo '<div class="divewp-pagination">';
            echo '<span class="divewp-pagination-info">' . 
                 /* translators: 1: Number of entries shown 2: Total number of entries */
                 sprintf(esc_html__('Showing %1$d of %2$d entries', 'divewp-boost-site-performance'), 
                 count($user_events_data), esc_html($total)) . 
                 '</span>';
            echo '<button id="divewp-load-more" class="button button-primary divewp-load-more"' . 
                 ' data-page="1" data-total-pages="' . esc_attr($total_pages) . '">' . 
                 /* translators: Button text to load more activity log entries */
                 esc_html__('Load More', 'divewp-boost-site-performance') . 
                 '</button>';
            echo '</div>';
        }

        echo '</div>'; // #divewp-logs-container
    }

    /**
     * Get event type icon
     */
    private function get_event_icon($type) {
        $icons = array(
            'content' => 'dashicons-edit',
            'media' => 'dashicons-admin-media',
            'taxonomy' => 'dashicons-category',
            'comment' => 'dashicons-admin-comments',
            'plugin' => 'dashicons-admin-plugins',
            'theme' => 'dashicons-admin-appearance',
            'user' => 'dashicons-admin-users',
            'settings' => 'dashicons-admin-settings',
            'content_management' => 'dashicons-admin-post',
            'plugin_management' => 'dashicons-admin-plugins',
            'theme_management' => 'dashicons-admin-appearance',
            'user_management' => 'dashicons-admin-users',
            'api_access' => 'dashicons-rest-api'
        );

        $icon_class = isset($icons[$type]) ? $icons[$type] : 'dashicons-admin-generic';
        return sprintf('<span class="dashicons %s"></span>', esc_attr($icon_class));
    }

    /**
     * Get recent admin events - for dashboard compatibility
     *
     * @param int $limit Number of events to retrieve
     * @return array Array of event objects
     */
    public function get_recent_admin_events($limit = 5) {
        return $this->logger->get_recent_events($limit);
    }

    /**
     * Display cleanup status message
     */
    private function display_cleanup_status() {
        $retention_days = apply_filters('divewp_events_retention_days', 30);
        echo '<div class="cleanup-status">';
        /* translators: %d: Number of days after which events are automatically cleaned up */
        echo sprintf(
/* translators: %d: number of days */
/* translators: %d: number of days */
            esc_html__('Events are automatically cleaned up after %d days.', 'divewp-boost-site-performance'),
            esc_html($retention_days)
        );
        echo '</div>';
    }

    /**
     * Get event type label
     *
     * @param string $type Event type
     * @return string Formatted label
     */
    public function get_event_type_label($type) {
        $labels = array(
            /* translators: Label for theme-related activities in the log */
            'theme_management' => __('Themes', 'divewp-boost-site-performance'),
            /* translators: Label for user-related activities in the log */
            'user_management' => __('Users', 'divewp-boost-site-performance'),
            /* translators: Label for plugin-related activities in the log */
            'plugin_management' => __('Plugins', 'divewp-boost-site-performance'),
            /* translators: Label for general admin activities in the log */
            'admin' => __('Admin', 'divewp-boost-site-performance'),
            /* translators: Label for settings-related activities in the log */
            'settings' => __('Settings', 'divewp-boost-site-performance'),
            /* translators: Label for content-related activities in the log */
            'content_management' => __('Content', 'divewp-boost-site-performance'),
            /* translators: Label for media-related activities in the log */
            'media_management' => __('Media', 'divewp-boost-site-performance'),
            /* translators: Label for comment-related activities in the log */
            'comment_management' => __('Comments', 'divewp-boost-site-performance'),
            /* translators: Label for REST API access in the log */
            'api_access' => __('API Access', 'divewp-boost-site-performance')
        );

        // If label not found, convert underscores to spaces and capitalize
        return isset($labels[$type]) ? $labels[$type] : ucwords(str_replace('_', ' ', $type));
    }

    /**
     * Get action label
     *
     * @param string $action Action name
     * @param string $type Event type
     * @return string Formatted label
     */
    public function get_action_label($action, $type) {
        $labels = array(
            // Theme actions
            /* translators: Action label when a theme is installed */
            'installed' => __('Installed', 'divewp-boost-site-performance'),
            /* translators: Action label when something is deleted */
            'deleted' => __('Deleted', 'divewp-boost-site-performance'),
            /* translators: Action label when a theme/plugin is activated */
            'activated' => __('Activated', 'divewp-boost-site-performance'),
            /* translators: Action label when a theme is customized */
            'customized' => __('Customized', 'divewp-boost-site-performance'),
            
            // User actions
            /* translators: Action label when a new user is created */
            'creation' => __('Creation', 'divewp-boost-site-performance'),
            /* translators: Action label when a user is deleted */
            'deletion' => __('Deletion', 'divewp-boost-site-performance'),
            /* translators: Action label when a password is reset */
            'password_reset' => __('Reset', 'divewp-boost-site-performance'),
            /* translators: Action label when an API request is authenticated */
            'authenticated' => __('Authenticated', 'divewp-boost-site-performance'),
            
            // Login actions
            /* translators: Action label for user login */
            'login' => __('Login', 'divewp-boost-site-performance'),
            /* translators: Action label for user logout */
            'logout' => __('Logout', 'divewp-boost-site-performance'),

            // Plugin actions
            /* translators: Action label when a plugin is deactivated */
            'deactivated' => __('Deactivated', 'divewp-boost-site-performance'),
            /* translators: Action label when something is updated */
            'updated' => __('Updated', 'divewp-boost-site-performance'),
            
            // Content actions
            /* translators: Action label when content is moved to trash */
            'trashed' => __('Trashed', 'divewp-boost-site-performance'),
            /* translators: Action label when content is restored from trash */
            'restored' => __('Restored', 'divewp-boost-site-performance'),
            /* translators: Action label when content is unpublished */
            'unpublished' => __('Unpublished', 'divewp-boost-site-performance'),
            /* translators: Action label when content is edited */
            'edited' => __('Edited', 'divewp-boost-site-performance'),
            /* translators: Action label when content is published */
            'published' => __('Published', 'divewp-boost-site-performance')
        );

        return isset($labels[$action]) ? $labels[$action] : ucfirst($action);
    }

    /**
     * AJAX handler for deleting all logs with enhanced security
     */
    public function ajax_delete_all_logs() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security verification failed', 'divewp-boost-site-performance')
            ));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => esc_html__('Permission denied', 'divewp-boost-site-performance')
            ));
            return;
        }

        try {
            $db = DiveWP_DB_Access::get_instance();
            if ($db->delete_user_events()) {
                wp_send_json_success(array(
                    'message' => esc_html__('All user events deleted successfully', 'divewp-boost-site-performance')
                ));
            } else {
                throw new Exception(esc_html__('Failed to delete user events', 'divewp-boost-site-performance'));
            }
        } catch (Exception $e) {
            $this->log_error('Failed to delete logs', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    /**
     * AJAX handler for refreshing logs with enhanced security
     */
    public function ajax_refresh_logs() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied', 'divewp-boost-site-performance'));
            return;
        }

        ob_start();
        $this->render_user_events_data($this->get_user_events_data());
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX handler for loading more events with enhanced security
     */
    public function ajax_load_more_events() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied', 'divewp-boost-site-performance'));
            return;
        }

        // Sanitize page parameter - must be a positive integer
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $page = max(1, $page); // Ensure page is at least 1
        $db = DiveWP_DB_Access::get_instance();
        
        $events = $db->get_paginated_events($page);
        $total = $db->get_total_events();

        ob_start();
        $this->render_events_rows($events);
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'total' => absint($total)
        ));
    }

    /**
     * Render event rows for AJAX load more
     *
     * @param array $events Array of event objects
     */
    private function render_events_rows($events) {
        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            echo '<tr>';
            echo '<td>' . esc_html($event->user_login) . '</td>';
            echo '<td>' . esc_html($this->get_event_type_label($event->event_type)) . '</td>';
            echo '<td>';
            echo '<span class="status-pill status-pill-' . esc_attr($this->get_action_class($event->event_action)) . '">';
            echo esc_html($this->get_action_label($event->event_action, $event->event_type));
            echo '</span>';
            echo '</td>';
            echo '<td>' . esc_html($event->description) . '</td>';
            echo '<td>' . esc_html(wp_date('Y-m-d H:i:s', strtotime($event->created_at))) . '</td>';
            echo '</tr>';
        }
    }

    /**
     * AJAX handler for loading recent timeline with enhanced security
     */
    public function ajax_load_recent_timeline() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'divewp_nonce')) {
            wp_send_json_error(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied', 'divewp-boost-site-performance'));
            return;
        }

        $recent_events = $this->get_recent_admin_events(5);
        
        ob_start();
        if (empty($recent_events)) {
            echo '<div class="divewp-no-activity">';
            echo '<span class="dashicons dashicons-marker"></span>';
            echo '<p>' . esc_html__('No recent activity recorded', 'divewp-boost-site-performance') . '</p>';
            echo '</div>';
        } else {
            echo '<div class="divewp-timeline">';
            $current_date = '';
            foreach ($recent_events as $event) {
                // Get the site's timezone
                $timezone = wp_timezone();
                
                // Convert UTC timestamp to site's timezone
                $datetime = new DateTime($event->created_at, new DateTimeZone('UTC'));
                $datetime->setTimezone($timezone);
                
                $event_date = $datetime->format('Y-m-d');
                $event_time = $datetime->format('H:i');
                
                if ($event_date !== $current_date) {
                    if ($current_date === '') {
                        echo '<div class="divewp-timeline-date today">';
                        echo '<span class="date-label">' . esc_html__('Today', 'divewp-boost-site-performance') . '</span>';
                    } else {
                        echo '<div class="divewp-timeline-date">';
                        echo '<span class="date-label">' . esc_html($datetime->format('F j')) . '</span>';
                    }
                    echo '</div>';
                    $current_date = $event_date;
                }
                
                echo '<div class="divewp-timeline-item">';
                echo '<div class="timeline-marker ' . esc_attr($this->get_action_class($event->event_action)) . '">';
                echo wp_kses($this->get_event_icon($event->event_type), array(
                    'span' => array(
                        'class' => array()
                    )
                ));
                echo '</div>';
                echo '<div class="timeline-content">';
                echo '<div class="timeline-header">';
                echo '<span class="time">' . esc_html($event_time) . '</span>';
                echo '<span class="event-type ' . esc_attr($event->event_type) . '">';
                echo '<div class="timeline-type">' . esc_html($this->get_event_type_label($event->event_type)) . '</div>';
                echo '</span>';
                if (!empty($event->event_action)) {
                    echo '<span class="status-pill status-pill-' . esc_attr($this->get_action_class($event->event_action)) . '">';
                    echo '<span class="timeline-action">' . esc_html($this->get_action_label($event->event_action, $event->event_type)) . '</span>';
                    echo '</span>';
                }
                echo '</div>';
                echo '<div class="timeline-body">';
                echo '<p>' . esc_html($event->description) . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }

    /**
     * Get action class for event styling
     *
     * @since 1.0.0
     * @param string $action The action name to get class for
     * @return string CSS class name for the action
     */
    private function get_action_class($action) {
        // Document the action-class mapping
        $classes = array(
            'created' => 'success',     // Creation actions
            'creation' => 'success',    // User creation
            'login' => 'success',       // Login events
            'activated' => 'success',   // Plugin/theme activation
            'installed' => 'success',   // Installation events
            'restored' => 'success',    // Restoration events
            'updated' => 'info',        // Update actions
            'update' => 'info',         // General updates
            'logout' => 'info',         // Logout events
            'trashed' => 'warning',     // Trash actions
            'unpublished' => 'warning', // Unpublish events
            'deactivated' => 'warning', // Deactivation events
            'deletion' => 'danger',     // Deletion events
            'deleted' => 'danger',      // Delete actions
            'authenticated' => 'info'   // REST API authenticated access
        );

        return isset($classes[$action]) ? $classes[$action] : 'info';
    }

    /**
     * Log error message with proper debug control and sanitization
     *
     * Note: This is a development-only feature that is disabled in production.
     * Logging only occurs when both WP_DEBUG and DIVEWP_DEBUG_LOG are enabled.
     *
     * @param string $message Error message to log
     * @param array  $context Additional context data
     * @return void
     */
    private function log_error($message, $context = array()) {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $log_data = array(
            'message' => sanitize_text_field($message),
            'context' => array_map('sanitize_text_field', (array) $context),
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && defined('DIVEWP_DEBUG_LOG') && DIVEWP_DEBUG_LOG) {
            if (function_exists('wp_debug_log')) {
                wp_debug_log(sprintf(
                    '[DiveWP User Events] %s | Context: %s',
                    $log_data['message'],
                    wp_json_encode($log_data['context'])
                ));
            }
        }
    }

    /**
     * Display admin notice
     *
     * @param string $message Notice message
     * @param string $type    Notice type (error, warning, success)
     * @return void
     */
    private function display_admin_notice($message, $type = 'error') {
        add_action('admin_notices', function() use ($message, $type) {
            printf(
                '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
                esc_attr($type),
                esc_html($message)
            );
        });
    }

    // ... rest of the UI methods ...
}