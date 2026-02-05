<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Inner_Header
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('admin_body_class', [__CLASS__, 'add_admin_body_class']);
        add_action('wp_ajax_mgwpp_toggle_theme', [__CLASS__, 'handle_theme_toggle']);
    }

    public static function enqueue_assets()
    {
        $screen = get_current_screen();
        $is_plugin_page = strpos($screen->id, 'mgwpp_') !== false;

        if ($is_plugin_page) {
            // Script is already loaded by MGWPP_Admin_Assets - just add theme data
            wp_localize_script('mgwpp-admin-core', 'mgwppThemeData', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('mgwpp-theme-nonce')
            ]);

            // Add inline script for theme flicker prevention (runs immediately)
            $theme_init_script = "(function() {
                var savedTheme = localStorage.getItem('mgwpp-theme');
                if (savedTheme === 'dark') {
                    document.body.classList.add('mgwpp-dark-mode');
                } else if (savedTheme === 'light') {
                    document.body.classList.remove('mgwpp-dark-mode');
                }
            })();";
            wp_add_inline_script('mgwpp-admin-core', $theme_init_script, 'before');
        }
    }

    public static function add_admin_body_class($classes)
    {
        $current_theme = self::get_user_theme_preference();
        return $classes . ($current_theme === 'dark' ? ' mgwpp-dark-mode' : '');
    }

    public static function render()
    {
        $current_theme = self::get_user_theme_preference();
        $theme_class = $current_theme === 'dark' ? 'mgwpp-dark-mode' : '';
?>
        <div class="mgwpp-loader-overlay" style="display:none;">
            <div class="mgwpp-loader-spinner"></div>
        </div>
        <div class="mgwpp-dashboard-header <?php echo esc_attr($theme_class); ?>">
            <div class="mgwpp-branding-group">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_dashboard')); ?>" class="mgwpp-link-no-decoration">
                    <img src="<?php echo esc_url(plugins_url('includes/admin/images/icons/logo.png', dirname(__FILE__, 4))); ?>" alt="Mini Gallery" class="mgwpp-header-logo" style="width: 50px; height: 50px; border-radius: 8px;" />
                </a>
                <div class="mgwpp-titles">
                    <h1 class="mgwpp-title">
                        <?php esc_html_e('Mini Gallery', 'mini-gallery') ?>
                        <span class="mgwpp-version"><?php echo esc_html(self::get_plugin_version()); ?></span>
                    </h1>
                    <p class="mgwpp-subtitle">
                        <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
                    </p>
                </div>
            </div>

            <div class="mgwpp-actions-group">
                <?php self::render_theme_toggle($current_theme); ?>
                <a class="mgwpp-admin-button mgwpp-link-no-decoration" href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>">
                    <?php esc_html_e('View Galleries', 'mini-gallery') ?>
                    <span class="dashicons dashicons-plus-alt mgwpp-admin-button__icon" style="vertical-align: middle;"></span>
                </a>
            </div>
        </div>

    <?php
    }

    public static function get_user_theme_preference()
    {
        $user_id = get_current_user_id();
        return get_user_meta($user_id, 'mgwpp_admin_theme', true) ?: 'light';
    }

    private static function get_plugin_version()
    {
        return defined('MG_VERSION') ? MG_VERSION : '1.0.0';
    }

    private static function render_theme_toggle($current_theme)
    {
        $icons_url = plugins_url('public/front-end/icons/', dirname(__FILE__, 4));
    ?>
        <div class="mgwpp-theme-toggle-wrapper">
            <button id="mgwpp-theme-toggle"
                data-current-theme="<?php echo esc_attr($current_theme); ?>"
                aria-label="<?php esc_attr_e('Toggle dark mode', 'mini-gallery'); ?>">
                <img src="<?php echo esc_url($icons_url . ($current_theme === 'dark' ? 'moon.webp' : 'sun.webp')); ?>"
                    alt="<?php echo esc_attr($current_theme === 'dark' ? 'Dark mode' : 'Light mode'); ?>"
                    class="mgwpp-theme-icon"
                    data-sun-url="<?php echo esc_url($icons_url . 'sun.webp'); ?>"
                    data-moon-url="<?php echo esc_url($icons_url . 'moon.webp'); ?>"
                    style="width: 35px; height: 35px;" />
            </button>
        </div>
<?php
    }

    public static function handle_theme_toggle()
    {
        try {
            if (!check_ajax_referer('mgwpp-theme-nonce', 'security', false)) {
                throw new Exception(__('Security verification failed', 'mini-gallery'), 403);
            }

            if (!isset($_POST['theme']) || !in_array($_POST['theme'], ['light', 'dark'])) {
                throw new Exception(__('Invalid theme parameter', 'mini-gallery'), 400);
            }

            $user_id = get_current_user_id();
            $new_theme = sanitize_key($_POST['theme']);

            if (!update_user_meta($user_id, 'mgwpp_admin_theme', $new_theme)) {
                throw new Exception(__('Failed to save theme preference', 'mini-gallery'), 500);
            }

            wp_send_json_success([
                'theme' => $new_theme,
                'body_class' => $new_theme === 'dark' ? 'mgwpp-dark-mode' : ''
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}

MGWPP_Inner_Header::init();
