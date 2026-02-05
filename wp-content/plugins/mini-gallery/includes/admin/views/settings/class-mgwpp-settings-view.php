<?php

/**
 * Settings View for Mini Gallery
 * 
 * Provides a UI to enable/disable plugin modules like Testimonials, 3D Galleries, and Canvas.
 *
 * @package MiniGallery
 * @since 1.6
 */

if (!defined('ABSPATH')) {
    exit;
}

// Note: inner-header is loaded in main plugin file

class MGWPP_Settings_View
{
    /**
     * Module settings option key
     */
    const OPTION_KEY = 'mgwpp_module_settings';

    /**
     * Default module settings
     */
    private static $default_settings = [
        'testimonials' => true,
        '3d_galleries' => true,
        'canvas' => true,
    ];

    /**
     * Get module configuration with labels and descriptions
     */
    private static function get_module_config()
    {
        return [
            'testimonials' => [
                'label' => __('Testimonials', 'mini-gallery'),
                'description' => __('Enable the testimonials module for displaying customer reviews and testimonials.', 'mini-gallery'),
                'icon' => 'dashicons-format-quote',
                'badge' => '',
            ],
            '3d_galleries' => [
                'label' => __('3D Galleries', 'mini-gallery'),
                'description' => __('Enable 3D model carousel galleries with support for GLTF, GLB, OBJ, and FBX files.', 'mini-gallery'),
                'icon' => 'dashicons-admin-site-alt3',
                'badge' => '',
            ],
            'canvas' => [
                'label' => __('Canvas Editor', 'mini-gallery'),
                'description' => __('Enable the visual drag-and-drop canvas editor for creating custom gallery layouts.', 'mini-gallery'),
                'icon' => 'dashicons-art',
                'badge' => 'beta',
            ],
        ];
    }

    /**
     * Initialize settings
     */
    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'register_settings']);
        // Register the admin_post handler for both logged in users
        add_action('admin_post_mgwpp_save_settings', [__CLASS__, 'save_settings']);
    }

    /**
     * Register WordPress settings
     */
    public static function register_settings()
    {
        register_setting('mgwpp_settings', self::OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_settings'],
            'default' => self::$default_settings,
        ]);
    }

    /**
     * Sanitize settings input
     */
    public static function sanitize_settings($input)
    {
        $sanitized = [];

        foreach (self::$default_settings as $key => $default) {
            $sanitized[$key] = isset($input[$key]) ? (bool) $input[$key] : false;
        }

        return $sanitized;
    }

    /**
     * Get module settings
     */
    public static function get_settings()
    {
        $settings = get_option(self::OPTION_KEY, self::$default_settings);
        return wp_parse_args($settings, self::$default_settings);
    }

    /**
     * Check if a specific module is enabled
     */
    public static function is_module_enabled($module)
    {
        $settings = self::get_settings();
        return isset($settings[$module]) ? (bool) $settings[$module] : true;
    }

    /**
     * Save settings handler
     */
    public static function save_settings()
    {
        // Verify nonce
        if (
            !isset($_POST['mgwpp_settings_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mgwpp_settings_nonce'])), 'mgwpp_save_settings')
        ) {
            wp_die(esc_html__('Security check failed.', 'mini-gallery'));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'mini-gallery'));
        }

        // Sanitize and save settings
        $settings = [];

        foreach (array_keys(self::$default_settings) as $module) {
            $settings[$module] = isset($_POST['mgwpp_modules'][$module]) &&
                sanitize_text_field(wp_unslash($_POST['mgwpp_modules'][$module])) === '1';
        }

        update_option(self::OPTION_KEY, $settings);

        // Flush rewrite rules since we may have enabled/disabled post types
        flush_rewrite_rules(false);

        // Redirect back with success message - include nonce for verification
        wp_safe_redirect(add_query_arg([
            'page' => 'mgwpp_settings',
            'saved' => '1',
            '_mgwpp_saved_nonce' => wp_create_nonce('mgwpp_settings_saved'),
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Render the settings page
     */
    public static function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $settings = self::get_settings();
        $modules = self::get_module_config();

        // Check for saved message with proper nonce verification
        $saved = false;
        if (isset($_GET['saved'], $_GET['_mgwpp_saved_nonce'])) {
            $nonce = sanitize_text_field(wp_unslash($_GET['_mgwpp_saved_nonce']));
            if (wp_verify_nonce($nonce, 'mgwpp_settings_saved')) {
                $saved = sanitize_text_field(wp_unslash($_GET['saved'])) === '1';
            }
        }

        self::enqueue_assets();
?>
        <div class="mgwpp-dashboard-container mgwpp-premium-dashboard">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php MGWPP_Inner_Header::render(); ?>

                    <div class="wrap mgwpp-settings-wrap">
                        <div class="mgwpp-dashboard-header">
                            <h1 class="wp-heading-inline">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <?php esc_html_e('Settings', 'mini-gallery'); ?>
                            </h1>
                            <div class="mgwpp-header-subtitle">
                                <?php esc_html_e('Configure your Mini Gallery modules and features', 'mini-gallery'); ?>
                            </div>
                        </div>

                        <?php if ($saved) : ?>
                            <div class="mgwpp-notice mgwpp-notice-success">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Settings saved successfully!', 'mini-gallery'); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mgwpp-settings-form">
                            <input type="hidden" name="action" value="mgwpp_save_settings">
                            <?php wp_nonce_field('mgwpp_save_settings', 'mgwpp_settings_nonce'); ?>

                            <div class="mgwpp-settings-section">
                                <h2 class="mgwpp-section-title">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                    <?php esc_html_e('Module Settings', 'mini-gallery'); ?>
                                </h2>
                                <p class="mgwpp-section-description">
                                    <?php esc_html_e('Enable or disable specific modules to customize your Mini Gallery experience. Disabled modules will not load, improving performance.', 'mini-gallery'); ?>
                                </p>

                                <div class="mgwpp-modules-grid">
                                    <?php foreach ($modules as $key => $module) : ?>
                                        <?php self::render_module_card($key, $module, $settings[$key]); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mgwpp-settings-actions">
                                <button type="submit" class="mgwpp-btn mgwpp-btn-primary">
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php esc_html_e('Save Settings', 'mini-gallery'); ?>
                                </button>
                                <button type="button" class="mgwpp-btn mgwpp-btn-secondary" onclick="window.location.reload();">
                                    <span class="dashicons dashicons-undo"></span>
                                    <?php esc_html_e('Reset', 'mini-gallery'); ?>
                                </button>
                            </div>
                        </form>

                        <?php self::render_info_section(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render a module toggle card
     */
    private static function render_module_card($key, $module, $enabled)
    {
        $badge_class = $module['badge'] === 'beta' ? 'mgwpp-badge-beta' : '';
    ?>
        <div class="mgwpp-module-card <?php echo $enabled ? 'mgwpp-module-enabled' : 'mgwpp-module-disabled'; ?>">
            <div class="mgwpp-module-header">
                <div class="mgwpp-module-icon">
                    <span class="dashicons <?php echo esc_attr($module['icon']); ?>"></span>
                </div>
                <div class="mgwpp-module-title-wrap">
                    <h3 class="mgwpp-module-title">
                        <?php echo esc_html($module['label']); ?>
                        <?php if (!empty($module['badge'])) : ?>
                            <span class="mgwpp-module-badge <?php echo esc_attr($badge_class); ?>">
                                <?php echo esc_html(strtoupper($module['badge'])); ?>
                            </span>
                        <?php endif; ?>
                    </h3>
                </div>
            </div>
            <div class="mgwpp-module-body">
                <p class="mgwpp-module-description">
                    <?php echo esc_html($module['description']); ?>
                </p>
            </div>
            <div class="mgwpp-module-footer">
                <label class="mgwpp-toggle-switch">
                    <input type="hidden" name="mgwpp_modules[<?php echo esc_attr($key); ?>]" value="0">
                    <input type="checkbox"
                        name="mgwpp_modules[<?php echo esc_attr($key); ?>]"
                        value="1"
                        <?php checked($enabled, true); ?>
                        class="mgwpp-module-toggle">
                    <span class="mgwpp-toggle-slider"></span>
                    <span class="mgwpp-toggle-label">
                        <?php echo $enabled ? esc_html__('Enabled', 'mini-gallery') : esc_html__('Disabled', 'mini-gallery'); ?>
                    </span>
                </label>
            </div>
        </div>
    <?php
    }

    /**
     * Render the info section
     */
    private static function render_info_section()
    {
    ?>
        <div class="mgwpp-settings-info">
            <div class="mgwpp-info-card">
                <span class="dashicons dashicons-info-outline"></span>
                <div class="mgwpp-info-content">
                    <h4><?php esc_html_e('Need Help?', 'mini-gallery'); ?></h4>
                    <p><?php esc_html_e('Changes to module settings may require a page refresh to take full effect. Disabling a module will hide it from the menu but will not delete any existing data.', 'mini-gallery'); ?></p>
                    <a href="https://minigallery.andgowebsolutions.com/docs/" target="_blank" class="mgwpp-link">
                        <?php esc_html_e('View Documentation', 'mini-gallery'); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Enqueue settings page assets
     */
    private static function enqueue_assets()
    {
        $plugin_version = defined('MG_VERSION') ? MG_VERSION : '1.0.0';

        // Enqueue the galleries CSS for consistent styling
        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Enqueue dashboard CSS  
        wp_enqueue_style(
            'mgwpp-admin-dashboard',
            plugins_url('admin/views/dashboard/mgwpp-dashboard-view.css', dirname(__FILE__, 3)),
            array('mgwpp-admin-galleries'),
            $plugin_version
        );

        // Enqueue settings-specific CSS
        wp_enqueue_style(
            'mgwpp-admin-settings',
            plugins_url('admin/views/settings/mgwpp-settings-view.css', dirname(__FILE__, 3)),
            array('mgwpp-admin-dashboard'),
            $plugin_version
        );

        // Enqueue settings JS
        wp_enqueue_script(
            'mgwpp-admin-settings',
            plugins_url('admin/views/settings/mgwpp-settings-view.js', dirname(__FILE__, 3)),
            array('jquery'),
            $plugin_version,
            true
        );
    }
}

// Initialize settings
MGWPP_Settings_View::init();
