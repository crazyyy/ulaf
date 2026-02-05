<?php
/**
 * 3D Model Carousel Admin Integration for Mini Gallery
 * 
 * @package     MiniGallery
 * @subpackage  Admin
 * @since       1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_3D_Admin_Integration
{
    /**
     * Initialize admin hooks
     */
    public static function init()
    {
        // Add gallery type to dropdown filter (if plugin uses filters)
        add_filter('mgwpp_gallery_types', [__CLASS__, 'add_gallery_type']);
        
        // Add admin assets for 3D settings
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        
        // Add 3D settings meta box
        add_action('add_meta_boxes', [__CLASS__, 'add_3d_meta_box']);
        
        // Save 3D settings
        add_action('save_post_mgwpp_soora', [__CLASS__, 'save_3d_settings'], 20);
    }
    
    /**
     * Add 3D Model Carousel to gallery types
     */
    public static function add_gallery_type($types)
    {
        if (!is_array($types)) {
            $types = [];
        }
        
        $types['3d_model_carousel'] = __('3D Model Carousel', 'mini-gallery');
        return $types;
    }
    
    /**
     * Enqueue admin assets for 3D settings
     */
    public static function enqueue_admin_assets($hook)
    {
        global $post;
        
        // Only load on gallery edit pages
        if (!in_array($hook, ['post.php', 'post-new.php']) || 
            !$post || $post->post_type !== 'mgwpp_soora') {
            return;
        }
        
        // Get current gallery type
        $gallery_type = get_post_meta($post->ID, 'gallery_type', true);
        
        // Only load 3D assets if 3D gallery type is selected
        if ($gallery_type !== '3d_model_carousel') {
            return;
        }
        
        // Enqueue color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue 3D admin assets
        wp_enqueue_script(
            'mgwpp-3d-admin',
            MG_PLUGIN_URL . '/includes/gallery-types/mgwpp-3d-model-carousel/admin/js/admin-3d.js',
            ['jquery', 'wp-color-picker'],
            filemtime(MG_PLUGIN_PATH . '/includes/gallery-types/mgwpp-3d-model-carousel/admin/js/admin-3d.js'),
            true
        );
        
        wp_enqueue_style(
            'mgwpp-3d-admin-css',
            MG_PLUGIN_URL . '/includes/gallery-types/mgwpp-3d-model-carousel/admin/css/admin-3d.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/gallery-types/mgwpp-3d-model-carousel/admin/css/admin-3d.css')
        );
        
        // Localize script
        wp_localize_script('mgwpp-3d-admin', 'mgwpp_3d_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_3d_admin_nonce'),
            'post_id' => $post->ID,
            'text' => [
                'select_3d_models' => __('Select 3D Models', 'mini-gallery'),
                'upload_3d_model' => __('Upload 3D Model', 'mini-gallery'),
                'supported_formats' => __('Supported formats: GLTF, GLB, OBJ, FBX', 'mini-gallery'),
                'max_size' => __('Maximum total size: 100MB', 'mini-gallery'),
            ],
        ]);
    }
    
    /**
     * Add 3D settings meta box
     */
    public static function add_3d_meta_box()
    {
        add_meta_box(
            'mgwpp-3d-settings',
            __('3D Model Settings', 'mini-gallery'),
            [__CLASS__, 'render_3d_meta_box'],
            'mgwpp_soora',
            'side',
            'default'
        );
    }
    
    /**
     * Render 3D settings meta box
     */
    public static function render_3d_meta_box($post)
    {
        wp_nonce_field('mgwpp_3d_settings_nonce', 'mgwpp_3d_settings_nonce_field');
        
        $settings = get_post_meta($post->ID, 'mgwpp_3d_settings', true);
        $defaults = [
            'preloader_type'   => 'spinner',
            'autoplay'         => true,
            'autoplay_delay'   => 5000,
            'controls'         => true,
            'lighting'         => 'studio',
            'background_color' => '#1a1a1a',
            'show_captions'    => true,
            'interaction'      => 'orbit',
            'show_grid'        => false,
        ];
        
        $settings = wp_parse_args($settings, $defaults);
        ?>
        <div class="mgwpp-3d-settings" style="padding: 10px 0;">
            <p>
                <label for="mgwpp_3d_preloader_type">
                    <strong><?php esc_html_e('Preloader Type', 'mini-gallery'); ?></strong>
                </label>
                <select id="mgwpp_3d_preloader_type" name="mgwpp_3d_settings[preloader_type]" 
                        class="widefat" style="margin-top: 5px;">
                    <option value="spinner" <?php selected($settings['preloader_type'], 'spinner'); ?>>
                        <?php esc_html_e('Spinner', 'mini-gallery'); ?>
                    </option>
                    <option value="progress" <?php selected($settings['preloader_type'], 'progress'); ?>>
                        <?php esc_html_e('Progress Bar', 'mini-gallery'); ?>
                    </option>
                    <option value="skeleton" <?php selected($settings['preloader_type'], 'skeleton'); ?>>
                        <?php esc_html_e('Skeleton', 'mini-gallery'); ?>
                    </option>
                    <option value="none" <?php selected($settings['preloader_type'], 'none'); ?>>
                        <?php esc_html_e('None', 'mini-gallery'); ?>
                    </option>
                </select>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="mgwpp_3d_settings[autoplay]" value="1" 
                           <?php checked($settings['autoplay'], true); ?>>
                    <?php esc_html_e('Auto-rotate models', 'mini-gallery'); ?>
                </label>
            </p>
            
            <p>
                <label for="mgwpp_3d_autoplay_delay">
                    <?php esc_html_e('Rotation Speed (ms)', 'mini-gallery'); ?>
                </label>
                <input type="number" id="mgwpp_3d_autoplay_delay" 
                       name="mgwpp_3d_settings[autoplay_delay]" 
                       value="<?php echo esc_attr($settings['autoplay_delay']); ?>"
                       class="widefat" min="1000" max="20000" step="500">
            </p>
            
            <p>
                <label for="mgwpp_3d_background_color">
                    <?php esc_html_e('Background Color', 'mini-gallery'); ?>
                </label><br>
                <input type="text" id="mgwpp_3d_background_color" 
                       class="mgwpp-color-picker"
                       name="mgwpp_3d_settings[background_color]" 
                       value="<?php echo esc_attr($settings['background_color']); ?>"
                       data-default-color="#1a1a1a">
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="mgwpp_3d_settings[controls]" value="1" 
                           <?php checked($settings['controls'], true); ?>>
                    <?php esc_html_e('Show controls', 'mini-gallery'); ?>
                </label>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" name="mgwpp_3d_settings[show_captions]" value="1" 
                           <?php checked($settings['show_captions'], true); ?>>
                    <?php esc_html_e('Show captions', 'mini-gallery'); ?>
                </label>
            </p>
            
            <div class="mgwpp-3d-info" style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-top: 10px;">
                <p style="margin: 0 0 5px 0; font-weight: bold;">
                    <?php esc_html_e('3D Model Formats:', 'mini-gallery'); ?>
                </p>
                <p style="margin: 0; font-size: 12px;">
                    <?php esc_html_e('Supported: GLTF, GLB, OBJ, FBX', 'mini-gallery'); ?><br>
                    <?php esc_html_e('Max total size: 100MB (limit: 12 models)', 'mini-gallery'); ?>
                </p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize color picker
            $('.mgwpp-color-picker').wpColorPicker();
            
            // Show/hide 3D settings based on gallery type
            function toggle3DSettings() {
                const galleryType = $('input[name="gallery_type"]:checked').val() || $('#mgwpp-create-gallery-type').val();
                const $metaBox = $('#mgwpp-3d-settings');
                
                if (galleryType === '3d_model_carousel') {
                    $metaBox.show();
                } else {
                    $metaBox.hide();
                }
            }
            
            // Listen for gallery type changes
            $(document).on('change', 'input[name="gallery_type"], #mgwpp-create-gallery-type', toggle3DSettings);
            toggle3DSettings(); // Initial check
        });
        </script>
        <?php
    }
    
    /**
     * Save 3D settings
     */
    public static function save_3d_settings($post_id)
    {
        // Check nonce exists first
        if (!isset($_POST['mgwpp_3d_settings_nonce_field'])) {
            return;
        }
        
        // Unslash and sanitize nonce before verification
        $nonce = sanitize_text_field(wp_unslash($_POST['mgwpp_3d_settings_nonce_field']));
        if (!wp_verify_nonce($nonce, 'mgwpp_3d_settings_nonce')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save settings if 3D gallery type
        $gallery_type = get_post_meta($post_id, 'gallery_type', true);
        if ($gallery_type !== '3d_model_carousel') {
            delete_post_meta($post_id, 'mgwpp_3d_settings');
            return;
        }
        
        // Process and save settings
        if (isset($_POST['mgwpp_3d_settings']) && is_array($_POST['mgwpp_3d_settings'])) {
            // Unslash and sanitize the entire array
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $raw_settings = array_map('sanitize_text_field', wp_unslash($_POST['mgwpp_3d_settings']));
            
            $settings = [];
            foreach ($raw_settings as $key => $value) {
                $settings[sanitize_key($key)] = $value;
            }
            
            // Convert checkbox values
            $settings['autoplay'] = isset($settings['autoplay']) && $settings['autoplay'] === '1';
            $settings['controls'] = isset($settings['controls']) && $settings['controls'] === '1';
            $settings['show_captions'] = isset($settings['show_captions']) && $settings['show_captions'] === '1';
            $settings['show_grid'] = isset($settings['show_grid']) && $settings['show_grid'] === '1';
            
            // Validate autoplay delay
            $settings['autoplay_delay'] = max(1000, min(20000, intval($settings['autoplay_delay'])));
            
            // Validate background color
            if (!preg_match('/^#[a-f0-9]{6}$/i', $settings['background_color'])) {
                $settings['background_color'] = '#1a1a1a';
            }
            
            update_post_meta($post_id, 'mgwpp_3d_settings', $settings);
        }
    }
}