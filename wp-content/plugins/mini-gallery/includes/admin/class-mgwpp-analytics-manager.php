<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Analytics_Manager
{
    private static $table_name;

    public static function init()
    {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mgwpp_analytics';

        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('wp_ajax_mgwpp_track_event', [__CLASS__, 'handle_track_event']);
        add_action('wp_ajax_nopriv_mgwpp_track_event', [__CLASS__, 'handle_track_event']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts']);

        // Only inject GA4 if enabled and measurement ID exists
        if (get_option('mgwpp_ga4_measurement_id')) {
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_ga4_script']);
        }
    }

    public static function register_settings()
    {
        register_setting(
            'mgwpp_analytics_settings',
            'mgwpp_ga4_measurement_id',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => ''
            ]
        );
    }

    public static function create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mgwpp_analytics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            gallery_id bigint(20) DEFAULT NULL,
            gallery_type varchar(50) DEFAULT NULL,
            event_label varchar(255) DEFAULT NULL,
            user_ip varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY gallery_id (gallery_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Enqueue GA4 script using wp_head for tracking scripts
     * Note: GA4 tracking code must come from Google's servers for proper functionality.
     * This uses wp_head output which is the standard WordPress approach for analytics.
     */
    /**
     * Enqueue GA4 script
     */
    public static function enqueue_ga4_script()
    {
        $measurement_id = get_option('mgwpp_ga4_measurement_id');
        if (empty($measurement_id)) {
            return;
        }

        // Sanitize and validate measurement ID format (G-XXXXXXXXXX)
        $measurement_id = sanitize_text_field($measurement_id);
        if (!preg_match('/^G-[A-Z0-9]+$/', $measurement_id)) {
            return;
        }

        // Enqueue Google Tag Manager
        wp_enqueue_script(
            'mgwpp-ga4-gtag',
            'https://www.googletagmanager.com/gtag/js?id=' . $measurement_id,
            [],
            '1.0.0', // Set version
            false // Output in header
        );

        // Add async attribute
        add_filter('script_loader_tag', function ($tag, $handle) {
            if ('mgwpp-ga4-gtag' !== $handle) {
                return $tag;
            }
            return str_replace(' src', ' async src', $tag);
        }, 10, 2);

        // Configure GA4
        // Enable debug_mode if WP_DEBUG is on to help developers
        $debug_config = (defined('WP_DEBUG') && WP_DEBUG) ? ", { 'debug_mode': true }" : "";

        $script = "
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '" . esc_js($measurement_id) . "'" . $debug_config . ");
        ";

        wp_add_inline_script('mgwpp-ga4-gtag', $script);
    }

    public static function enqueue_frontend_scripts()
    {
        wp_enqueue_script(
            'mgwpp-analytics-frontend',
            MG_PLUGIN_URL . '/assets/js/mgwpp-analytics-frontend.js',
            ['jquery'],
            defined('MG_VERSION') ? MG_VERSION : '1.0.0',
            true
        );

        wp_localize_script('mgwpp-analytics-frontend', 'mgwppAnalytics', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('mgwpp-analytics-nonce'),
            'ga4_id'   => sanitize_text_field(get_option('mgwpp_ga4_measurement_id', ''))
        ]);
    }

    public static function handle_track_event()
    {
        // Verify nonce
        if (!check_ajax_referer('mgwpp-analytics-nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
            return;
        }

        global $wpdb;

        // Validate required field exists
        if (!isset($_POST['event_type']) || empty($_POST['event_type'])) {
            wp_send_json_error(['message' => 'Event type is required'], 400);
            return;
        }

        // Properly unslash and sanitize all inputs
        $event_type   = sanitize_text_field(wp_unslash($_POST['event_type']));
        $gallery_id   = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : null;
        $gallery_type = isset($_POST['gallery_type']) ? sanitize_text_field(wp_unslash($_POST['gallery_type'])) : '';
        $event_label  = isset($_POST['event_label']) ? sanitize_text_field(wp_unslash($_POST['event_label'])) : '';

        // Properly sanitize IP address
        $user_ip = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $user_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            // Additional IP validation
            if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
                $user_ip = '';
            }
        }

        // Insert into custom analytics table
        $result = self::insert_analytics_event([
            'event_type'   => $event_type,
            'gallery_id'   => $gallery_id,
            'gallery_type' => $gallery_type,
            'event_label'  => $event_label,
            'user_ip'      => $user_ip,
        ]);

        if ($result === false) {
            wp_send_json_error(['message' => 'Failed to track event'], 500);
            return;
        }

        wp_send_json_success();
    }

    /**
     * Insert analytics event into custom table
     * 
     * @param array $data Event data
     * @return int|false Number of rows inserted or false on error
     */
    private static function insert_analytics_event($data)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom analytics table requires direct insert
        return $wpdb->insert(
            $wpdb->prefix . 'mgwpp_analytics',
            [
                'event_type'   => $data['event_type'],
                'gallery_id'   => $data['gallery_id'],
                'gallery_type' => $data['gallery_type'],
                'event_label'  => $data['event_label'],
                'user_ip'      => $data['user_ip'],
                'created_at'   => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Query event count from analytics table
     * 
     * @param string $event_type Event type to count
     * @param string $date_filter Optional date filter ('month' for last month)
     * @return int Event count
     */
    private static function query_event_count($event_type, $date_filter = '')
    {
        global $wpdb;

        // Sanitize event_type for cache key
        $cache_key = 'mgwpp_event_count_' . sanitize_key($event_type) . '_' . sanitize_key($date_filter);
        $cached = wp_cache_get($cache_key, 'mgwpp_analytics');

        if ($cached !== false) {
            return intval($cached);
        }

        // Sanitize event_type for query
        $safe_event_type = sanitize_text_field($event_type);

        // Build table name directly using prefix + literal (avoids variable assignment warning)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom analytics table, caching implemented
        if ($date_filter === 'month') {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table prefix is safe
                    "SELECT COUNT(*) FROM `{$wpdb->prefix}mgwpp_analytics` WHERE event_type = %s AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
                    $safe_event_type
                )
            );
        } else {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table prefix is safe
                    "SELECT COUNT(*) FROM `{$wpdb->prefix}mgwpp_analytics` WHERE event_type = %s",
                    $safe_event_type
                )
            );
        }

        // Cache for 2 minutes
        wp_cache_set($cache_key, $count, 'mgwpp_analytics', 120);

        return intval($count);
    }

    public static function get_stats($period = 'month')
    {
        // Generate cache key
        $cache_key = 'mgwpp_analytics_stats_' . sanitize_key($period);
        $cached = wp_cache_get($cache_key, 'mgwpp_analytics');

        if ($cached !== false) {
            return $cached;
        }

        // Query counts using helper method
        $date_filter = ($period === 'month') ? 'month' : '';

        $stats = [
            'views'           => self::query_event_count('view', $date_filter),
            'cta_clicks'      => self::query_event_count('cta_click', ''),
            'cta_submissions' => self::query_event_count('cta_submit', ''),
        ];

        // Cache for 5 minutes
        wp_cache_set($cache_key, $stats, 'mgwpp_analytics', 300);

        return $stats;
    }
}
