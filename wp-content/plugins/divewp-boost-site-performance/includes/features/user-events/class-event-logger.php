<?php
/**
 * Event Logger functionality for DiveWP
 *
 * This class handles core event logging functionality.
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Event_Logger {
    /**
     * Instance of this class
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Database access instance
     *
     * @var DiveWP_DB_Access
     */
    private $db;

    /**
     * Table existence status cache
     *
     * @var bool|null
     */
    private $table_exists = null;

    // Add constants for valid values
    private const VALID_EVENT_TYPES = [
        'content',
        'media',
        'user_management',
        'plugin_management',
        'theme_management',
        'settings',
        'admin',
        'taxonomy',
        'comment',
        'api_access'
    ];

    // Add these property declarations near the top of the class with other properties
    private $rate_limit = 50; // events per minute
    private $rate_window = 60; // seconds

    /**
     * Whether the current REST request authenticated via an Application Password.
     *
     * @since 2.1.2
     * @var bool
     */
    private $rest_app_password_authenticated = false;

    /**
     * User ID authenticated via Application Password for this REST request.
     *
     * @since 2.1.2
     * @var int
     */
    private $rest_app_password_user_id = 0;

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
        // Initialize database access
        $this->db = DiveWP_DB_Access::get_instance();
        
        // Check if table exists
        if (!$this->db->verify_table_exists('divewp_user_events')) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('User Events Table does not exist. Plugin activation may have failed.', 'error');
            }
            return;
        }
        
        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks with proper priority
     */
    private function init_hooks() {
        // Batch similar events together
        add_action('transition_post_status', array($this, 'log_post_status_change'), 20, 3);
        add_action('post_updated', array($this, 'log_post_update'), 20, 3);

        // Media Management
        add_action('add_attachment', array($this, 'log_media_upload'));
        add_action('delete_attachment', array($this, 'log_media_deletion'));

        // Category/Taxonomy Changes
        add_action('created_term', array($this, 'log_term_creation'), 10, 3);
        add_action('edited_term', array($this, 'log_term_update'), 10, 3);
        add_action('delete_term', array($this, 'log_term_deletion'), 10, 3);

        // Comment Moderation
        add_action('transition_comment_status', array($this, 'log_comment_status_change'), 10, 3);
        add_action('delete_comment', array($this, 'log_comment_deletion'));
        add_action('edit_comment', array($this, 'log_comment_edit'));

        // Admin Sessions
        add_action('admin_init', array($this, 'maybe_log_admin_login'));
        add_action('wp_logout', array($this, 'log_admin_logout'));
        add_action('clear_auth_cookie', array($this, 'log_admin_logout'));

        // User Management
        add_action('user_register', array($this, 'log_user_creation_by_admin'));
        add_action('delete_user', array($this, 'log_user_deletion_by_admin'));
        add_action('edit_user_profile_update', array($this, 'log_user_update_by_admin'));
        add_action('after_password_reset', array($this, 'log_user_update_by_admin'));

        // Plugin Management
        add_action('activated_plugin', array($this, 'log_plugin_activation'), 10, 1);
        add_action('deactivated_plugin', array($this, 'log_plugin_deactivation'), 10, 1);
        add_action('deleted_plugin', array($this, 'log_plugin_deletion'), 10, 1);
        add_action('upgrader_process_complete', array($this, 'log_plugin_installation'), 10, 2);
        add_action('upgrader_process_complete', array($this, 'log_plugin_update'), 10, 2);

        // Theme Management
        add_action('switch_theme', array($this, 'log_theme_switch'), 10, 3);
        add_action('deleted_theme', array($this, 'log_theme_deletion'), 10, 2);
        add_action('upgrader_process_complete', array($this, 'log_theme_update'), 10, 2);
        add_action('upgrader_process_complete', array($this, 'log_theme_installation'), 10, 2);
        add_action('customize_save', array($this, 'log_theme_customization'));

        // Settings Management
        add_action('updated_option', array($this, 'log_settings_change'), 10, 3);


        // Cleanup schedule hook (scheduled after init to follow WP timing standards)
        add_action('init', array($this, 'maybe_schedule_cleanup'));
        add_action('divewp_user_events_cleanup', array($this, 'cleanup_old_events'));

        // Password reset request
        add_action('retrieve_password', array($this, 'log_password_reset_request'));

        // Application Password authentication (external tools / MCP).
        add_action('application_password_did_authenticate', array($this, 'mark_rest_app_password_authenticated'), 10, 2);

        // REST API authenticated requests (used for method + route context).
        add_filter('rest_request_before_callbacks', array($this, 'log_rest_api_access'), 10, 3);
    }

    /**
     * Insert an event into the database with proper sanitization
     *
     * @since 1.0.0
     * @param array $data {
     *     Event data to be inserted.
     *     @type string $event_type    Type of event (must be one of VALID_EVENT_TYPES)
     *     @type string $event_action  Action being performed
     *     @type int    $user_id       ID of user performing action
     *     @type string $description   Description of event (max 255 chars)
     *     @type string $status        Status of event (success/warning/error/info)
     * }
     * @return bool|int False on failure, event ID on success
     */
    public function insert($data) {
        try {
            if (!$this->validate_event_data($data)) {
                throw new Exception(__('Invalid event data', 'divewp-boost-site-performance'));
            }
            
            $result = $this->db->log_event($data);
            if (!$result) {
                throw new Exception(__('Failed to insert event', 'divewp-boost-site-performance'));
            }
            
            return $result;
        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    'Failed to log user event: %s',
                    $e->getMessage()
                ), 'error');
            }
            return false;
        }
    }

    /**
     * Get recent administrator events
     *
     * @param int $limit Number of events to retrieve
     * @return array Array of event objects
     */
    public function get_recent_events($limit = 5) {
        return $this->db->get_recent_events($limit);
    }

    /**
     * Get total number of events
     *
     * @return int Total number of events
     */
    public function get_total_events() {
        return $this->db->get_total_events();
    }

    /**
     * Cleanup old events
     */
    public function cleanup_old_events() {
        $days = apply_filters('divewp_events_retention_days', 30);
        $this->db->cleanup_events($days);
    }

    /**
     * Register cleanup cron schedule after init to respect WP translation timing
     *
     * @since 2.2.0
     * @return void
     */
    public function maybe_schedule_cleanup() {
        if (!wp_next_scheduled('divewp_user_events_cleanup')) {
            wp_schedule_event(time(), 'daily', 'divewp_user_events_cleanup');
        }
    }

    /**
     * Check if the current user is an administrator
     *
     * @return bool
     */
    private function is_admin_user() {
        return current_user_can('administrator');
    }

    /**
     * Log post status changes
     */
    public function log_post_status_change($new_status, $old_status, $post) {
        if (!$this->is_admin_user() || $new_status === $old_status) {
            return;
        }

        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Post Status Change:', 'info');
            divewp_debug_log(sprintf(
                'Status Change - New: %s, Old: %s, Post ID: %d, Type: %s',
                $new_status,
                $old_status,
                $post->ID,
                $post->post_type
            ), 'info');
        }

        $post_type = get_post_type_object($post->post_type);
        $post_type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;

        // Determine the action based on status change
        if ($old_status === 'trash') {
            $action = 'restored';
        } elseif ($new_status === 'publish' && $old_status !== 'publish') {
            $action = 'published';
        } elseif ($new_status === 'trash') {
            $action = 'trashed';
        } elseif ($old_status === 'publish' && $new_status !== 'publish') {
            $action = 'unpublished';
        } else {
            $action = 'updated';
        }

        $this->insert([
            'event_type' => 'content',
            'event_action' => $action,
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                '%s "%s" was %s by Administrator %s',
                $post_type_label,
                $post->post_title,
                $action,
                wp_get_current_user()->user_login
            ),
            'status' => $this->get_status_for_action($action)
        ]);
    }

    /**
     * Log post content updates
     */
    public function log_post_update($post_id, $post_after, $post_before) {
        if (!$this->is_admin_user()) {
            return;
        }

        // Skip if this is a revision
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if status is changing (already handled by log_post_status_change)
        if ($post_before->post_status !== $post_after->post_status) {
            return;
        }

        // Skip if restoring from trash (already handled by log_post_status_change)
        if ($post_before->post_status === 'trash' || $post_after->post_status === 'trash') {
            return;
        }

        $post_type = get_post_type_object($post_after->post_type);
        $post_type_label = $post_type ? $post_type->labels->singular_name : $post_after->post_type;

        $this->insert([
            'event_type' => 'content',
            'event_action' => 'updated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                '%s "%s" was edited by Administrator %s',
                $post_type_label,
                $post_after->post_title,
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Get status for action
     */
    private function get_status_for_action($action) {
        switch ($action) {
            case 'published':
            case 'restored':
                return 'success';
            case 'trashed':
            case 'deleted':
                return 'danger';
            case 'unpublished':
                return 'warning';
            default:
                return 'info';
        }
    }

    /**
     * Log media upload events
     */
    public function log_media_upload($attachment_id) {
        if (!$this->is_admin_user()) {
            return;
        }

        $attachment = get_post($attachment_id);
        if (!$attachment) return;

        $this->insert([
            'event_type' => 'media',
            'event_action' => 'uploaded',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Media file "%s" was uploaded by Administrator %s',
                $attachment->post_title,
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Log media deletion events
     */
    public function log_media_deletion($attachment_id) {
        if (!$this->is_admin_user()) {
            return;
        }

        $attachment = get_post($attachment_id);
        if (!$attachment) return;

        $this->insert([
            'event_type' => 'media',
            'event_action' => 'deleted',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Media file "%s" was deleted by Administrator %s',
                $attachment->post_title,
                wp_get_current_user()->user_login
            ),
            'status' => 'warning'
        ]);
    }

    /**
     * Log term creation/taxonomy changes
     */
    public function log_term_creation($term_id, $tt_id, $taxonomy) {
        if (!$this->is_admin_user()) {
            return;
        }

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        $tax_object = get_taxonomy($taxonomy);
        $tax_label = $tax_object ? $tax_object->labels->singular_name : $taxonomy;

        $this->insert([
            'event_type' => 'taxonomy',
            'event_action' => 'created',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'New %s "%s" was created by Administrator %s',
                $tax_label,
                $term->name,
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log term updates
     */
    public function log_term_update($term_id, $tt_id, $taxonomy) {
        if (!$this->is_admin_user()) {
            return;
        }

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        $tax_object = get_taxonomy($taxonomy);
        $tax_label = $tax_object ? $tax_object->labels->singular_name : $taxonomy;

        $this->insert([
            'event_type' => 'taxonomy',
            'event_action' => 'updated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                '%s "%s" was updated by Administrator %s',
                $tax_label,
                $term->name,
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Log term deletion
     */
    public function log_term_deletion($term_id, $tt_id, $taxonomy) {
        if (!$this->is_admin_user()) {
            return;
        }

        // Note: We can't get the term name here as it's already deleted
        $tax_object = get_taxonomy($taxonomy);
        $tax_label = $tax_object ? $tax_object->labels->singular_name : $taxonomy;

        $this->insert([
            'event_type' => 'taxonomy',
            'event_action' => 'deleted',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'A %s was deleted by Administrator %s',
                $tax_label,
                wp_get_current_user()->user_login
            ),
            'status' => 'warning'
        ]);
    }

    /**
     * Log comment status changes
     */
    public function log_comment_status_change($new_status, $old_status, $comment) {
        if (!$this->is_admin_user()) {
            return;
        }

        $action = $new_status === 'approved' ? 'approved' : 
                 ($new_status === 'trash' ? 'trashed' : 'updated');

        $this->insert([
            'event_type' => 'comment',
            'event_action' => $action,
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Comment on "%s" was %s by Administrator %s',
                get_the_title($comment->comment_post_ID),
                $action,
                wp_get_current_user()->user_login
            ),
            'status' => $action === 'approved' ? 'success' : 'info'
        ]);
    }

    /**
     * Log comment deletion
     */
    public function log_comment_deletion($comment_id) {
        if (!$this->is_admin_user()) return;
        
        $comment = get_comment($comment_id);
        if (!$comment) return;

        $this->insert([
            'event_type' => 'comment',
            'event_action' => 'deleted',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Comment on "%s" was permanently deleted by Administrator %s',
                get_the_title($comment->comment_post_ID),
                wp_get_current_user()->user_login
            ),
            'status' => 'warning'
        ]);
    }

    /**
     * Log comment edits
     */
    public function log_comment_edit($comment_id) {
        if (!$this->is_admin_user()) return;
        
        $comment = get_comment($comment_id);
        if (!$comment) return;

        $this->insert([
            'event_type' => 'comment',
            'event_action' => 'edited',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Comment on "%s" was edited by Administrator %s',
                get_the_title($comment->comment_post_ID),
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Check and log administrator login on admin_init
     * This ensures user capabilities are fully loaded
     */
    public function maybe_log_admin_login() {
        // Get user session token to track unique logins
        $session = wp_get_session_token();
        
        // Use transient to prevent duplicate logging
        $login_logged = get_transient('divewp_admin_login_' . $session);
        
        if (!$login_logged && current_user_can('administrator')) {
            $user = wp_get_current_user();
            
            $this->insert(array(
                'event_type' => 'admin',
                'event_action' => 'login',
                'user_id' => $user->ID,
                'description' => sprintf('Administrator "%s" logged in', $user->user_login),
                'status' => 'success'
            ));
            
            // Set transient to prevent duplicate logging, expires in 12 hours
            set_transient('divewp_admin_login_' . $session, true, 12 * HOUR_IN_SECONDS);
        }
    }

    /**
     * Log administrator logout
     */
    public function log_admin_logout() {
        static $logged = false;
        if ($logged) {
            return;
        }

        $user = wp_get_current_user();
        if ($user && $user->exists() && user_can($user, 'administrator')) {
            $this->insert(array(
                'event_type' => 'admin',
                'event_action' => 'logout',
                'user_id' => $user->ID,
                'description' => sprintf('Administrator "%s" logged out', $user->user_login),
                'status' => 'info'
            ));
            $logged = true;
        }
    }

    /**
     * Log user creation by administrator
     */
    public function log_user_creation_by_admin($user_id) {
        if (!$this->is_admin_user()) {
            return;
        }

        $new_user = get_userdata($user_id);
        if (!$new_user) {
            return;
        }

        $roles = array_map(function($role) {
            return translate_user_role($role);
        }, $new_user->roles);

        $this->insert([
            'event_type' => 'user_management',
            'event_action' => 'creation',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'New user "%s" (%s) created with role(s): %s by Administrator %s',
                $new_user->user_login,
                $new_user->user_email,
                implode(', ', $roles),
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log user deletion by administrator
     */
    public function log_user_deletion_by_admin($user_id) {
        if (!$this->is_admin_user()) {
            return;
        }

        $deleted_user = get_userdata($user_id);
        if (!$deleted_user) {
            return;
        }

        $this->insert([
            'event_type' => 'user_management',
            'event_action' => 'deletion',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Administrator %s deleted user "%s"',
                wp_get_current_user()->user_login,
                $deleted_user->user_login
            ),
            'status' => 'danger'
        ]);
    }

    /**
     * Log user updates by administrator
     * 
     * This method is hooked into 'edit_user_profile_update' which is already nonce-verified by WordPress core.
     * However, we'll add an additional nonce check for the password change detection.
     * 
     * @since 1.0.4
     * @param int $user_id The ID of the user being updated
     * @return void
     */
    public function log_user_update_by_admin($user_id) {
        if (!$this->is_admin_user()) {
            return;
        }

        // Verify nonce for profile update
        // Nonces are cryptographically signed and validated by WordPress core
        // sanitize_text_field() is not appropriate for nonces as it may alter the signature
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'update-user_' . $user_id)) {
            return;
        }

        $updated_user = get_userdata($user_id);
        if (!$updated_user) {
            return;
        }

        // Check for password change with nonce verification
        $is_password_change = isset($_POST['pass1']) && !empty($_POST['pass1']) && 
                            isset($_POST['_wpnonce']) && 
                            wp_verify_nonce(
                                wp_unslash($_POST['_wpnonce']), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                                'update-user_' . $user_id
                            );
        
        $description = $is_password_change 
            ? sprintf('Password changed for user "%s" by Administrator %s',
                $updated_user->user_login,
                wp_get_current_user()->user_login
            )
            : sprintf('User profile "%s" updated by Administrator %s',
                $updated_user->user_login,
                wp_get_current_user()->user_login
            );

        $this->insert([
            'event_type' => 'user_management',
            'event_action' => 'update',
            'user_id' => get_current_user_id(),
            'description' => $description,
            'status' => 'info'
        ]);
    }

    /**
     * Log plugin activation
     */
    public function log_plugin_activation($plugin) {
        if (!$this->is_admin_user()) {
            return;
        }

        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
        $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : 'unknown';

        $this->insert([
            'event_type' => 'plugin_management',
            'event_action' => 'activated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Plugin %s v%s activated by Administrator %s',
                $plugin_name,
                $plugin_version,
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log plugin deactivation
     */
    public function log_plugin_deactivation($plugin) {
        if (!$this->is_admin_user()) {
            return;
        }

        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
        $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : 'unknown';

        $this->insert([
            'event_type' => 'plugin_management',
            'event_action' => 'deactivated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Plugin %s v%s deactivated by Administrator %s',
                $plugin_name,
                $plugin_version,
                wp_get_current_user()->user_login
            ),
            'status' => 'warning'
        ]);
    }

    /**
     * Log plugin deletion
     */
    public function log_plugin_deletion($plugin) {
        if (!$this->is_admin_user()) {
            return;
        }

        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;

        $this->insert([
            'event_type' => 'plugin_management',
            'event_action' => 'deleted',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Plugin "%s" was deleted by Administrator %s',
                $plugin_name,
                wp_get_current_user()->user_login
            ),
            'status' => 'danger'
        ]);
    }

    /**
     * Log plugin installation
     */
    public function log_plugin_installation($upgrader_object, $options) {
        if (!$this->is_admin_user() || 
            !isset($options['action']) || 
            $options['action'] !== 'install' ||
            !isset($options['type']) || 
            $options['type'] !== 'plugin') {
            return;
        }

        $plugin_file = $upgrader_object->plugin_info();
        if (!$plugin_file) {
            return;
        }

        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
        $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_file;
        $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : '1.0';

        $this->insert([
            'event_type' => 'plugin_management',
            'event_action' => 'installed',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Plugin "%s" v%s was installed by Administrator %s',
                $plugin_name,
                $plugin_version,
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log plugin updates
     */
    public function log_plugin_update($upgrader_object, $options) {
        if (!$this->is_admin_user() || 
            !isset($options['action']) || 
            $options['action'] !== 'update' ||
            !isset($options['type']) || 
            $options['type'] !== 'plugin' ||
            !isset($options['plugins']) || 
            !is_array($options['plugins'])) {
            return;
        }

        foreach ($options['plugins'] as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
            $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : 'unknown';

            $this->insert([
                'event_type' => 'plugin_management',
                'event_action' => 'updated',
                'user_id' => get_current_user_id(),
                'description' => sprintf(
                    'Plugin "%s" was updated to v%s by Administrator %s',
                    $plugin_name,
                    $plugin_version,
                    wp_get_current_user()->user_login
                ),
                'status' => 'info'
            ]);
        }
    }

    /**
     * Log theme switching
     */
    public function log_theme_switch($new_name, $new_theme, $old_theme) {
        if (!$this->is_admin_user()) {
            return;
        }

        $this->insert([
            'event_type' => 'theme_management',
            'event_action' => 'activated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Theme changed from "%s v%s" to "%s v%s" by Administrator %s',
                $old_theme->get('Name'),
                $old_theme->get('Version'),
                $new_theme->get('Name'),
                $new_theme->get('Version'),
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log theme deletion
     */
    public function log_theme_deletion($stylesheet, $deleted) {
        if (!$this->is_admin_user()) {
            return;
        }

        $theme = wp_get_theme($stylesheet);
        $status = $deleted ? 'success' : 'error';

        $this->insert([
            'event_type' => 'theme_management',
            'event_action' => 'deleted',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Theme "%s" was deleted by Administrator %s',
                $theme->get('Name'),
                wp_get_current_user()->user_login
            ),
            'status' => 'danger'
        ]);
    }

    /**
     * Log theme updates
     */
    public function log_theme_update($upgrader_object, $options) {
        if (!$this->is_admin_user() || 
            !isset($options['action']) || 
            $options['action'] !== 'update' ||
            !isset($options['type']) || 
            $options['type'] !== 'theme' ||
            !isset($options['themes']) || 
            !is_array($options['themes'])) {
            return;
        }

        foreach ($options['themes'] as $theme_dir) {
            $theme = wp_get_theme($theme_dir);
            $this->insert([
                'event_type' => 'theme_management',
                'event_action' => 'updated',
                'user_id' => get_current_user_id(),
                'description' => sprintf(
                    'Theme "%s" was updated to v%s by Administrator %s',
                    $theme->get('Name'),
                    $theme->get('Version'),
                    wp_get_current_user()->user_login
                ),
                'status' => 'info'
            ]);
        }
    }

    /**
     * Log theme installation
     */
    public function log_theme_installation($upgrader_object, $options) {
        if (!$this->is_admin_user() || 
            !isset($options['action']) || 
            $options['action'] !== 'install' ||
            !isset($options['type']) || 
            $options['type'] !== 'theme') {
            return;
        }

        $theme = $upgrader_object->theme_info();
        if (!$theme) {
            return;
        }

        $this->insert([
            'event_type' => 'theme_management',
            'event_action' => 'installed',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Theme "%s v%s" was installed by Administrator %s',
                $theme->get('Name'),
                $theme->get('Version'),
                wp_get_current_user()->user_login
            ),
            'status' => 'success'
        ]);
    }

    /**
     * Log theme customization
     */
    public function log_theme_customization($customizer_manager) {
        if (!$this->is_admin_user()) {
            return;
        }

        $theme = wp_get_theme();
        
        $this->insert([
            'event_type' => 'theme_management',
            'event_action' => 'customized',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Theme "%s" customization settings were updated by Administrator %s',
                $theme->get('Name'),
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Log settings changes
     */
    public function log_settings_change($option, $old_value, $value) {
        if (!$this->is_admin_user()) {
            return;
        }

        $monitored_settings = array(
            'blogname' => 'Site Title',
            'blogdescription' => 'Tagline',
            'siteurl' => 'WordPress Address (URL)',
            'home' => 'Site Address (URL)',
            'admin_email' => 'Administration Email Address',
            'users_can_register' => 'Membership',
            'default_role' => 'New User Default Role',
            'timezone_string' => 'Timezone',
            'date_format' => 'Date Format',
            'time_format' => 'Time Format',
            'start_of_week' => 'Week Starts On',
            'posts_per_page' => 'Posts per page',
            'default_category' => 'Default Post Category',
            'permalink_structure' => 'Permalink Structure'
        );

        if (!isset($monitored_settings[$option])) {
            return;
        }

        if ($old_value === $value) {
            return;
        }

        $setting_name = $monitored_settings[$option];
        $old_value_text = $this->format_setting_value($old_value);
        $new_value_text = $this->format_setting_value($value);

        $this->insert([
            'event_type' => 'settings',
            'event_action' => 'updated',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Setting "%s" was changed from "%s" to "%s" by Administrator %s',
                $setting_name,
                $old_value_text,
                $new_value_text,
                wp_get_current_user()->user_login
            ),
            'status' => 'info'
        ]);
    }

    /**
     * Format setting value for display
     */
    private function format_setting_value($value) {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        } elseif (is_array($value)) {
            return 'Array[' . count($value) . ']';
        } elseif (is_object($value)) {
            return 'Object';
        } elseif (is_null($value)) {
            return 'null';
        } else {
            return (string) $value;
        }
    }

    /**
     * Log password reset request
     */
    public function log_password_reset_request($user_login) {
        if (!$this->is_admin_user()) {
            return;
        }

        $user = get_user_by('login', $user_login);
        if (!$user) {
            return;
        }

        $this->insert([
            'event_type' => 'user_management',
            'event_action' => 'password_reset',
            'user_id' => get_current_user_id(),
            'description' => sprintf(
                'Password reset was requested for user "%s" by Administrator %s',
                $user_login,
                wp_get_current_user()->user_login
            ),
            'status' => 'warning'
        ]);
    }

    /**
     * Mark the current REST request as authenticated via Application Password.
     *
     * This runs only when Application Password auth succeeds. We keep a simple
     * request-scoped flag and user ID so we can later log the REST route/method.
     *
     * @since 2.1.2
     * @param WP_User $user Authenticated user.
     * @param array   $item Application password details.
     * @return void
     */
    public function mark_rest_app_password_authenticated($user, $item) {
        $is_rest = (defined('REST_REQUEST') && REST_REQUEST);

        if (!$is_rest) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Server variable used only for route detection, not output.
            $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
            $is_rest = $uri !== '' && strpos($uri, '/wp-json/') !== false;
        }

        if (!$is_rest) {
            return;
        }

        if (!is_object($user) || !method_exists($user, 'exists') || !$user->exists()) {
            return;
        }

        $this->rest_app_password_authenticated = true;
        $this->rest_app_password_user_id = absint($user->ID);
    }

    /**
     * Log authenticated REST API access
     *
     * @param mixed            $response Existing response (unused)
     * @param array            $handler  Route handler
     * @param WP_REST_Request  $request  Current REST request
     * @return mixed Unmodified $response
     */
    public function log_rest_api_access($response, $handler, $request) {
        static $logged = false;

        if ($logged) {
            return $response;
        }

        // Only log external (app-password) REST access.
        if (!$this->rest_app_password_authenticated) {
            return $response;
        }

        // Only log for admins since DB layer enforces manage_options
        if (!current_user_can('manage_options')) {
            return $response;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return $response;
        }

        if ($this->rest_app_password_user_id && absint($user->ID) !== absint($this->rest_app_password_user_id)) {
            return $response;
        }

        // Throttle so MCP bursts don't flood the log.
        $throttle_key = 'divewp_api_access_app_password_' . absint($user->ID);
        if (get_transient($throttle_key)) {
            $logged = true;
            return $response;
        }
        set_transient($throttle_key, 1, 5 * MINUTE_IN_SECONDS);

        $route = $request instanceof WP_REST_Request ? $request->get_route() : '';
        $method = $request instanceof WP_REST_Request ? $request->get_method() : '';

        $this->insert([
            'event_type' => 'api_access',
            'event_action' => 'authenticated',
            'user_id' => $user->ID,
            'description' => sprintf(
                'REST API %s %s accessed by %s',
                $method ?: 'REQUEST',
                $route ?: '(unknown route)',
                $user->user_login
            ),
            'status' => 'info'
        ]);

        $logged = true;
        return $response;
    }

    /**
     * Check if the event is rate limited
     *
     * @since 1.0.0
     * @return bool True if rate limited, false otherwise
     */
    private function is_rate_limited() {
        $current_time = time();
        $last_event_time = get_transient('divewp_last_event_time');
        
        if ($current_time - $last_event_time < $this->rate_window) {
            return true;
        }
        
        set_transient('divewp_last_event_time', $current_time, $this->rate_window);
        return false;
    }

    /**
     * Validate event data before insertion
     *
     * @since 1.0.0
     * @param array $data Event data to validate
     * @return bool True if valid, false otherwise
     */
    private function validate_event_data($data) {
        // Sanitize input data
        $data = array_map('sanitize_text_field', $data);
        
        // Validate event type
        if (!in_array($data['event_type'], self::VALID_EVENT_TYPES)) {
            return false;
        }
        
        // Truncate description if too long
        $data['description'] = substr($data['description'], 0, 255);
        
        return true;
    }
} 