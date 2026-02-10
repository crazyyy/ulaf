<?php
/**
 * DB Insights functionality for DiveWP
 *
 * This class provides database-related insights and recommendations.
 * Direct database queries are intentionally used without caching for real-time monitoring.
 * All operations are admin-only and require appropriate capabilities.
 *
 * @package     DiveWP
 * @subpackage  Features/DB-Insights
 * @since       1.0.4
 * @author      Oleg Petrov
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

/**
 * Class DiveWP_DB_Insights
 *
 * Handles database insights and recommendations.
 * Note on Direct Database Queries:
 * This class intentionally uses direct database queries without caching because:
 * 1. It's an admin-only tool for monitoring and diagnostics
 * 2. All data needs to be real-time and current
 * 3. Caching would provide incorrect/outdated monitoring data
 * 4. All queries are protected by capability checks
 * 5. Performance impact is minimal as this is only used in admin dashboard
 *
 * @since 1.0.4
 */
class DiveWP_DB_Insights {

    /**
     * Status constants for database checks.
     *
     * @since 1.0.4
     */
    const STATUS_GOOD     = 'success';
    const STATUS_WARNING  = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO     = 'info';
    const STATUS_BAD      = 'danger';

    /**
     * Content loader instance.
     *
     * @since 1.0.4
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Database stats cache.
     *
     * @since 1.0.4
     * @var array|null
     */
    private $db_stats = null;

    /**
     * Initialize the class.
     *
     * @since 1.0.4
     */
    public function __construct() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $this->content_loader = new DiveWP_Content_Loader();
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enhanced error logging with detailed context
     *
     * Logs detailed error information when DIVEWP_DEBUG is enabled.
     * Uses WordPress error logging functions in a controlled manner.
     * Only logs in debug mode and sanitizes all data.
     *
     * @since 1.0.4
     * @param string $message     Error message to log
     * @param string $context     Context where the error occurred
     * @param mixed  $error       Optional. Additional error data
     * @return void
     */
    private function log_error($message, $context = '', $error = null) {
        if (!defined('DIVEWP_DEBUG') || !DIVEWP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $error_data = array(
            'message'      => sanitize_text_field($message),
            'context'      => sanitize_text_field($context),
            'timestamp'    => current_time('mysql'),
            'user_id'      => get_current_user_id(),
            'memory_usage' => size_format(memory_get_usage(true))
        );
        
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('divewp_debug_log')) {
            divewp_debug_log(sprintf(
                '[DiveWP DB Insights] %s - Context: %s - Details: %s',
                sanitize_text_field($message),
                sanitize_text_field($context),
                wp_json_encode($error_data)
            ), 'error');
        }
    }

    /**
     * Get database size and table information.
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Database size and table information must be current for accurate monitoring
     * 3. Caching would provide outdated information, defeating the monitoring purpose
     * 4. This method is protected by capability checks
     * 5. Performance impact is minimal as this is only used in admin dashboard
     *
     * @since 1.0.4
     * @return object|null Database information
     */
    private function get_db_info() {
        if (!current_user_can('manage_options')) {
            return null;
        }

        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only diagnostic tool requiring real-time data
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                    SUM(round(((data_length + index_length) / 1024 / 1024), 2)) as total_size,
                    SUM(data_free) as total_overhead,
                    COUNT(table_name) as total_tables
                FROM information_schema.TABLES 
                WHERE table_schema = %s",
                DB_NAME
            ));
    }

    /**
     * Get content statistics.
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Content statistics must be current for accurate monitoring
     * 3. Caching would provide outdated statistics, defeating the monitoring purpose
     * 4. This method is protected by capability checks
     * 5. Performance impact is minimal as this is only used in admin dashboard
     *
     * @since 1.0.4
     * @return object|null Content statistics
     */
    private function get_content_stats() {
        if (!current_user_can('manage_options')) {
            return null;
        }

        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only diagnostic tool requiring real-time data
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                    (SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s) as revision_count,
                    (SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = %s) as spam_count,
                    (SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d) as expired_transients
                FROM DUAL",
                'revision',
                'spam',
                $wpdb->esc_like('_transient_timeout_') . '%',
                time()
            ));
    }

    /**
     * Get all database statistics at once.
     * 
     * Note: Direct database queries without caching are intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Database statistics must be current for accurate monitoring
     * 3. Caching would provide outdated statistics, defeating the monitoring purpose
     * 4. All queries are protected by capability checks
     * 5. Performance impact is minimal as this is only used in admin dashboard
     * 6. Each query provides critical real-time monitoring data that must be current
     *
     * @since 1.0.4
     * @return array Database statistics
     */
    private function get_all_db_stats() {
        if (!current_user_can('manage_options')) {
            return array();
        }

        if ($this->db_stats !== null) {
            return $this->db_stats;
        }

        global $wpdb;
        
        try {
            // Get database size and table information
            $db_info = $this->get_db_info();
            
            // Get content statistics
            $content_stats = $this->get_content_stats();

            if ($wpdb->last_error) {
                $this->handle_db_error('Database Query');
                return array();
            }

            // Get non-core tables count
            $core_tables = array(
                'commentmeta', 'comments', 'links', 'options', 'postmeta', 
                'posts', 'termmeta', 'terms', 'term_relationships', 
                'term_taxonomy', 'usermeta', 'users'
            );

            $core_tables = array_map(
                function($table) use ($wpdb) {
                    return $wpdb->prefix . $table;
                },
                $core_tables
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only diagnostic tool requiring real-time data
            $all_tables = $wpdb->get_col($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->esc_like($wpdb->prefix) . '%'
            ));

            $non_core_count = count(array_diff($all_tables, $core_tables));

            // Store all stats
            $this->db_stats = array(
                'db_size' => array(
                    'value' => $db_info ? $db_info->total_size : 0,
                    'status' => $this->get_size_status($db_info ? $db_info->total_size : 0)
                ),
                'tables_overhead' => array(
                    'value' => $db_info ? ($db_info->total_overhead / MB_IN_BYTES) : 0,
                    'status' => $this->get_overhead_status(
                        $db_info ? $db_info->total_overhead : 0,
                        $db_info ? $db_info->total_size : 0
                    )
                ),
                'post_revisions' => array(
                    'value' => $content_stats ? $content_stats->revision_count : 0,
                    'status' => $this->get_revision_status($content_stats ? $content_stats->revision_count : 0)
                ),
                'spam_comments' => array(
                    'value' => $content_stats ? $content_stats->spam_count : 0,
                    'status' => $this->get_spam_status($content_stats ? $content_stats->spam_count : 0)
                ),
                'expired_transients' => array(
                    'value' => $content_stats ? $content_stats->expired_transients : 0,
                    'status' => $this->get_transient_status($content_stats ? $content_stats->expired_transients : 0)
                ),
                'non_core_tables' => array(
                    'value' => $non_core_count,
                    'status' => $this->get_table_count_status($non_core_count)
                )
            );

            return $this->db_stats;
        } catch (Exception $e) {
            $this->handle_db_error('Database Stats', $e->getMessage());
            return array();
        }
    }

    /**
     * Handle database errors in a controlled manner.
     * Only logs in debug mode and when user has appropriate permissions.
     *
     * @since 1.0.4
     * @param string $context Error context
     * @param string $additional_info Optional additional error information
     */
    private function handle_db_error($context, $additional_info = '') {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        if ($wpdb->last_error) {
            $error_data = array(
                'context'         => $context,
                'error'           => $wpdb->last_error,
                'additional_info' => $additional_info
            );

            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && function_exists('divewp_debug_log')) {
                divewp_debug_log(sprintf(
                    '[DiveWP DB Error] Context: %s - Error: %s - Info: %s',
                    sanitize_text_field($context),
                    sanitize_text_field($wpdb->last_error),
                    sanitize_text_field($additional_info)
                ), 'error');
            }
        }
    }

    /**
     * Get status for various metrics
     */
    private function get_overhead_status($overhead_bytes, $total_size_mb) {
        $overhead_mb = $overhead_bytes / MB_IN_BYTES;

        // Floor: Small amounts of fragmentation are normal
        if ($overhead_mb < 5) {
            return self::STATUS_GOOD;
        }

        // Protection against division by zero
        if ($total_size_mb <= 0) {
            return self::STATUS_GOOD;
        }

        $percentage = ($overhead_mb / $total_size_mb) * 100;

        // Critical: > 15% overhead or > 100MB absolute
        if ($percentage > 15 || $overhead_mb > 100) {
            return self::STATUS_CRITICAL;
        }

        // Warning: > 5% overhead
        if ($percentage > 5) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_GOOD;
    }

    private function get_revision_status($count) {
        return ($count <= 100) ? self::STATUS_GOOD : 
               ($count <= 500 ? self::STATUS_WARNING : self::STATUS_CRITICAL);
    }

    private function get_spam_status($count) {
        return ($count < 100) ? self::STATUS_GOOD : self::STATUS_BAD;
    }

    private function get_transient_status($count) {
        return ($count < 100) ? self::STATUS_GOOD : self::STATUS_BAD;
    }

    private function get_table_count_status($count) {
        if ($count < 50) {
            return self::STATUS_GOOD;
        } elseif ($count <= 100) {
            return self::STATUS_WARNING;
        }
        return self::STATUS_BAD;
    }

    /**
     * Enqueue necessary assets with nonce.
     *
     * @since 1.0.4
     * @param string $hook The current admin page hook.
     */
    public function enqueue_assets($hook) {
        if (false === strpos($hook, 'divewp')) {
            return;
        }

        wp_localize_script('divewp-recommendations', 'divewpDBInsights', array(
            'nonce' => wp_create_nonce('divewp_db_insights_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));

        wp_enqueue_style(
            'divewp-db-insights',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );
    }

    /**
     * Render the DB insights interface.
     *
     * @since 1.0.4
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        $this->db_stats = null;
        
        ?>
        <h3><?php esc_html_e('Database Insights & Recommendations', 'divewp-boost-site-performance'); ?></h3>
        
        <div class="recommendations-grid">
            <?php 
            $checks = array(
                'database_size',
                'tables_overhead',
                'post_revisions',
                'spam_comments',
                'expired_transients',
                'non_core_tables'
            );

            foreach ($checks as $check) {
                $method = "render_{$check}_check";
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
            ?>
        </div>

        <div class="divewp-notice divewp-notice-warning">
            <p>
                <strong><?php esc_html_e('Important:', 'divewp-boost-site-performance'); ?></strong> 
                <?php esc_html_e('Always backup your database before performing any optimizations or cleanup operations.', 'divewp-boost-site-performance'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render check methods
     */
    private function render_database_size_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['db_size']['status'],
            'value' => sprintf(
                /* translators: %s: database size in MB */
                esc_html__('%s MB', 'divewp-boost-site-performance'),
                number_format($stats['db_size']['value'], 2)
            )
        );
        
        $this->render_check('database-size', $check_result);
    }

    private function render_tables_overhead_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['tables_overhead']['status'],
            'value' => size_format($stats['tables_overhead']['value'] * MB_IN_BYTES, 2)
        );
        
        $this->render_check('tables-overhead', $check_result);
    }

    private function render_post_revisions_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['post_revisions']['status'],
            'value' => number_format_i18n($stats['post_revisions']['value'])
        );
        
        $this->render_check('post-revisions', $check_result);
    }

    private function render_spam_comments_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['spam_comments']['status'],
            'value' => number_format_i18n($stats['spam_comments']['value'])
        );
        
        $this->render_check('spam-comments', $check_result);
    }

    private function render_expired_transients_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['expired_transients']['status'],
            'value' => number_format_i18n($stats['expired_transients']['value'])
        );
        
        $this->render_check('expired-transients', $check_result);
    }

    private function render_non_core_tables_check() {
        $stats = $this->get_all_db_stats();
        $check_result = array(
            'status' => $stats['non_core_tables']['status'],
            'value' => number_format_i18n($stats['non_core_tables']['value'])
        );
        
        $this->render_check('non-core-tables', $check_result);
    }

    /**
     * Generic method to render a check card.
     *
     * @since 1.0.4
     * @param string $check_type Type of check to render.
     * @param array  $check_result Results of the check.
     * @return void
     */
    private function render_check($check_type, $check_result) {
        $content = $this->content_loader->get_content('db-insights', $check_type);

        if (!$content) {
            $this->handle_db_error('Content Loading', sprintf(
                'Content not found for check type: %s',
                sanitize_text_field($check_type)
            ));
            return;
        }

        try {
            if ($check_result['status'] === self::STATUS_GOOD) {
                $message_type = 'success';
            } elseif ($check_result['status'] === self::STATUS_WARNING) {
                $message_type = 'warning';
            } else {
                $message_type = 'error';
            }
            
            // Fallback to error if specific type doesn't exist in content
            if (!isset($content['messages'][$message_type])) {
                $message_type = 'error';
            }

            $messages = $content['messages'][$message_type];

            $details = '';
            if (isset($messages['details']) && is_string($messages['details'])) {
                $details = esc_html($messages['details']);
                if (isset($check_result['value'])) {
                    $details = str_replace('{size}', esc_html($check_result['value']), $details);
                }
            }

            $learn_more = array();
            
            if ( ! empty( $content['learn_more']['description'] ) ) {
                $learn_more['description'] = wp_kses_post( esc_html( $content['learn_more']['description'] ) );
            }
            
            /* translators: %s: Check type name (e.g., "database size") */
            $learn_more['benefits_title'] = sprintf( esc_html__('Benefits of %s Optimization:', 'divewp-boost-site-performance'), esc_html( str_replace( '-', ' ', $check_type ) ) );

            $learn_more['benefits'] = array();
            if ( ! empty( $content['learn_more']['benefits'] ) ) {
                foreach ( $content['learn_more']['benefits'] as $benefit ) {
                    if ( is_string( $benefit ) ) {
                        $learn_more['benefits'][] = wp_kses_post( esc_html( $benefit ) );
                    }
                }
            }

            if ( $check_result['status'] !== self::STATUS_GOOD && ! empty( $content['learn_more']['recommended_plugins'] ) ) {
                $learn_more['plugins_title'] = esc_html__( 'Recommended plugins:', 'divewp-boost-site-performance' );
                $learn_more['plugins'] = array();
                foreach ( $content['learn_more']['recommended_plugins'] as $plugin ) {
                    if ( is_string( $plugin ) ) {
                        $learn_more['plugins'][] = wp_kses_post( esc_html( $plugin ) );
                    }
                }
            }

            $steps = array();
            if ( ! empty( $messages['steps'] ) ) {
                foreach ( $messages['steps'] as $step ) {
                    if ( is_string( $step ) ) {
                        $steps[] = wp_kses_post( esc_html( $step ) );
                    }
                }
            }

            $title = isset( $messages['title'] ) ? esc_html( $messages['title'] ) : '';

            $this->render_card(array(
                'title' => $title,
                'icon' => $this->get_icon($check_type),
                'details' => $details,
                'steps' => $steps,
                'status' => $check_result['status'],
                'status_text' => $this->get_status_text($check_result['status']),
                'learn_more' => $learn_more
            ));
        } catch (Exception $e) {
            $this->handle_db_error('Render Check', sprintf(
                'Error rendering %s check: %s',
                sanitize_text_field($check_type),
                $e->getMessage()
            ));
        }
    }

    /**
     * Helper method to render a recommendation card.
     *
     * @since 1.0.4
     * @param array $args Card rendering arguments
     */
    private function render_card($args) {
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => self::STATUS_INFO,
            'status_text' => esc_html__('Information', 'divewp-boost-site-performance'),
            'learn_more' => array()
        );

        $args = wp_parse_args($args, $defaults);
        extract($args);

        include DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
    }

    /**
     * Get database size status.
     *
     * @since 1.0.4
     * @param float $size Database size in MB
     * @return string Status (success, warning, or danger)
     */
    private function get_size_status($size) {
        if ($size < 250) {
            return self::STATUS_GOOD;
        } elseif ($size <= 500) {
            return self::STATUS_WARNING;
        }
        return self::STATUS_CRITICAL;
    }

    /**
     * Get status text based on status.
     *
     * @since 1.0.4
     * @param string $status Status constant
     * @return string Status text
     */
    private function get_status_text($status) {
        $status_texts = array(
            self::STATUS_GOOD => esc_html__('Optimal', 'divewp-boost-site-performance'),
            self::STATUS_WARNING => esc_html__('Needs Attention', 'divewp-boost-site-performance'),
            self::STATUS_CRITICAL => esc_html__('Critical', 'divewp-boost-site-performance'),
            self::STATUS_BAD => esc_html__('Needs Cleanup', 'divewp-boost-site-performance'),
            self::STATUS_INFO => esc_html__('Unknown', 'divewp-boost-site-performance')
        );

        return isset($status_texts[$status]) ? $status_texts[$status] : $status_texts[self::STATUS_INFO];
    }

    /**
     * Get icon for a specific check.
     *
     * @since 1.0.4
     * @param string $check_type The type of check
     * @return string The SVG icon HTML
     */
    private function get_icon($check_type) {
        $icons = array(
            'database-size' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 5c0 1.1-3.59 2-8 2s-8-.9-8-2 3.59-2 8-2 8 .9 8 2"/><path d="M21 12c0 1.1-3.59 2-8 2s-8-.9-8-2"/><path d="M21 19c0 1.1-3.59 2-8 2s-8-.9-8-2"/><path d="M5 5v14"/><path d="M21 5v14"/></svg>',
            'tables-overhead' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>',
            'post-revisions' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="M8 15l4 4 4-4"/></svg>',
            'spam-comments' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="12" y1="7" x2="12" y2="11"/><line x1="12" y1="13" x2="12" y2="13"/></svg>',
            'expired-transients' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="12" x2="16" y2="14"/><path d="M4 4l16 16"/><path d="M7 8c0 .5 2.2 1 5 1s5-.5 5-1"/><path d="M7 12c0 .5 2.2 1 5 1s5-.5 5-1"/></svg>',
            'non-core-tables' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 6c0 1.1-3.59 2-8 2s-8-.9-8-2"/><path d="M21 12c0 1.1-3.59 2-8 2s-8-.9-8-2"/><path d="M5 6v12c0 1.1 3.59 2 8 2s8-.9 8-2V6"/><circle cx="17" cy="4" r="3"/><path d="M19 6l2 2"/></svg>'
        );

        return isset($icons[$check_type]) ? $icons[$check_type] : 
            '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="8"/></svg>';
    }

    /**
     * Aggregate all DB insights for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $stats = $this->get_all_db_stats();

        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );

        $overall = self::STATUS_GOOD;

        foreach ( $stats as $item ) {
            $summary['total_checks']++;
            $status = isset( $item['status'] ) ? $item['status'] : self::STATUS_INFO;
            if ( in_array( $status, array( self::STATUS_CRITICAL, self::STATUS_BAD ), true ) ) {
                $summary['critical']++;
                $overall = self::STATUS_CRITICAL;
            } elseif ( self::STATUS_WARNING === $status ) {
                $summary['warnings']++;
                if ( self::STATUS_GOOD === $overall ) {
                    $overall = self::STATUS_WARNING;
                }
            } else {
                $summary['passed']++;
            }
        }

        return array(
            'status'  => $overall,
            'checks'  => $stats,
            'summary' => $summary,
        );
    }
}