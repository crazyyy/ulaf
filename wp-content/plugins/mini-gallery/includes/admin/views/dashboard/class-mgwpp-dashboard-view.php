<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';


class MGWPP_Dashboard_View
{
    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        // Get all counts including 3D gallery and Canvas
        $stats = [
            'galleries' => MGWPP_Data_Handler::get_post_count('mgwpp_soora'),
            'albums' => MGWPP_Data_Handler::get_post_count('mgwpp_album'),
            'testimonials' => MGWPP_Data_Handler::get_post_count('mgwpp_testimonial'),
            '3d_galleries' => self::get_3d_gallery_count(),
            'canvas' => MGWPP_Data_Handler::get_post_count('mgwpp_canvas'),
        ];

        self::enqueue_dashboard_assets();
?>
        <div class="mgwpp-dashboard-container mgwpp-premium-dashboard">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php
                    MGWPP_Inner_Header::render();
                    ?>

                    <div class="wrap">
                        <div class="mgwpp-dashboard-header">
                            <h1 class="wp-heading-inline">
                                <?php esc_html_e('Dashboard', 'mini-gallery'); ?>
                            </h1>
                            <div class="mgwpp-header-subtitle">
                                <?php esc_html_e('Welcome to Mini Gallery - Your Complete Media Management Solution', 'mini-gallery'); ?>
                            </div>
                        </div>

                        <?php self::render_stats_grid($stats); ?>

                        <?php self::render_quick_actions(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function enqueue_dashboard_assets()
    {
        $plugin_version = defined('MG_VERSION') ? MG_VERSION : '1.0.0';

        // Enqueue the galleries CSS for consistent styling
        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Enqueue dashboard-specific CSS
        wp_enqueue_style(
            'mgwpp-admin-dashboard',
            plugins_url('admin/views/dashboard/mgwpp-dashboard-view.css', dirname(__FILE__, 3)),
            array('mgwpp-admin-galleries'),
            $plugin_version
        );
    }

    private static function get_3d_gallery_count()
    {
        // Try cache first
        $cache_key = 'mgwpp_3d_gallery_count';
        $count = wp_cache_get($cache_key, 'mgwpp_dashboard');

        if ($count !== false) {
            return intval($count);
        }

        // Use WP_Query instead of direct database query
        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        $query = new WP_Query([
            'post_type'      => 'mgwpp_soora',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                [
                    'key'   => 'gallery_type',
                    'value' => '3d_model_carousel',
                ],
            ],
        ]);
        // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query

        $count = $query->post_count;

        // Cache for 5 minutes
        wp_cache_set($cache_key, $count, 'mgwpp_dashboard', 300);

        return intval($count);
    }

    private static function render_stats_grid($stats)
    {
        $admin_url = admin_url('admin.php');
    ?>
        <div class="mgwpp-stats-grid mgwpp-dashboard-stats">
            <?php
            self::render_stat_card(
                __('Galleries', 'mini-gallery'),
                $stats['galleries'],
                'dashicons-format-gallery',
                add_query_arg('page', 'mgwpp_galleries', $admin_url),
                'primary'
            );

            self::render_stat_card(
                __('Albums', 'mini-gallery'),
                $stats['albums'],
                'dashicons-images-alt2',
                add_query_arg('page', 'mgwpp_albums', $admin_url),
                'secondary'
            );

            // Conditionally show Testimonials card
            if (function_exists('mgwpp_is_module_enabled') && mgwpp_is_module_enabled('testimonials')) {
                self::render_stat_card(
                    __('Testimonials', 'mini-gallery'),
                    $stats['testimonials'],
                    'dashicons-format-quote',
                    add_query_arg('page', 'mgwpp_testimonials', $admin_url),
                    'accent'
                );
            }

            // Conditionally show 3D Galleries card
            if (function_exists('mgwpp_is_module_enabled') && mgwpp_is_module_enabled('3d_galleries')) {
                self::render_stat_card(
                    __('3D Galleries', 'mini-gallery'),
                    $stats['3d_galleries'],
                    'dashicons-admin-site-alt3',
                    add_query_arg('page', 'mgwpp_galleries', $admin_url),
                    'gradient'
                );
            }

            // Conditionally show Canvas card with BETA badge
            if (function_exists('mgwpp_is_module_enabled') && mgwpp_is_module_enabled('canvas')) {
                self::render_stat_card(
                    __('Canvas', 'mini-gallery'),
                    $stats['canvas'],
                    'dashicons-art',
                    add_query_arg('page', 'mgwpp_canvas', $admin_url),
                    'canvas',
                    'beta' // Badge parameter
                );
            }
            ?>
        </div>
    <?php
    }

    private static function render_stat_card($title, $count, $icon, $url = '', $theme = 'primary')
    {
        $display_value = number_format_i18n($count);

        $card_content = sprintf(
            '<div class="mgwpp-stat-card-inner mgwpp-stat-%s">
                <div class="mgwpp-stat-icon-wrap">
                    <span class="dashicons %s"></span>
                </div>
                <div class="mgwpp-stat-info">
                    <span class="mgwpp-stat-count">%s</span>
                    <span class="mgwpp-stat-title">%s</span>
                </div>
                <div class="mgwpp-stat-arrow">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </div>
            </div>',
            esc_attr($theme),
            esc_attr($icon),
            esc_html($display_value),
            esc_html($title)
        );

        if ($url) {
            $card_content = sprintf(
                '<a href="%s" class="mgwpp-stat-card-link">%s</a>',
                esc_url($url),
                $card_content
            );
        }

        $output = '<div class="mgwpp-stat-card">' . $card_content . '</div>';
        echo wp_kses_post($output);
    }

    private static function render_quick_actions()
    {
        $admin_url = admin_url('admin.php');
    ?>
        <div class="mgwpp-quick-actions-section">
            <h2 class="mgwpp-section-title">
                <span class="dashicons dashicons-lightning"></span>
                <?php esc_html_e('Quick Actions', 'mini-gallery'); ?>
            </h2>
            <div class="mgwpp-quick-actions-grid">
                <a href="<?php echo esc_url(add_query_arg('page', 'mgwpp_galleries', $admin_url)); ?>" class="mgwpp-quick-action">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php esc_html_e('Create Gallery', 'mini-gallery'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('page', 'mgwpp_albums', $admin_url)); ?>" class="mgwpp-quick-action">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php esc_html_e('Create Album', 'mini-gallery'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('page', 'mgwpp_canvas', $admin_url)); ?>" class="mgwpp-quick-action">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php esc_html_e('Create Canvas', 'mini-gallery'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('page', 'mgwpp_settings', $admin_url)); ?>" class="mgwpp-quick-action">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <span><?php esc_html_e('Settings', 'mini-gallery'); ?></span>
                </a>
            </div>
        </div>
<?php
    }
}
