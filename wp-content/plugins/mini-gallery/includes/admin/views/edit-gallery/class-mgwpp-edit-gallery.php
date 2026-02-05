<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Edit_Gallery_View
{
    private static $gallery_types = [
        "single_carousel" => ["Single Carousel", "single-carousel.webp"],
        "multi_carousel" => ["Multi Carousel", "multi-carousel.webp"],
        "grid" => ["Grid Layout", "grid.webp"],
        "mega_slider" => ["Mega Slider", "mega-slider.webp"],
        "full_page_slider" => ["Full Page Slider", "full-page-slider.webp"],
        "pro_carousel" => ["Pro Multi Card Carousel", "pro-carousel.webp"],
        "neon_carousel" => ["Neon Carousel", "neon-carousel.webp"],
        "threed_carousel" => ["3D Carousel", "3d-carousel.webp"],
        "spotlight_carousel" => ["Spotlight Carousel", "spotlight-carousel.webp"],
        "testimonials_carousel" => ["Testimonials Carousel", "testimonials.webp"],
        "3d_model_carousel" => ["3D Model Carousel", "3d-model-carousel.webp"],
        "marquee_gallery" => ["Marquee Gallery", "marquee-gallery.webp"],
        "vertical_marquee" => ["Vertical Marquee", "vertical-marquee.webp"],
        "3d_masonry_gallery" => ["3D Masonry Gallery", "3d-masonry-gallery.webp"],
        "3d_h_marquee" => ["3D Horizontal Marquee", "3d-h-marquee.webp"]
    ];

    public static function init()
    {
        add_action('admin_menu', [self::class, 'register_edit_page']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('admin_post_mgwpp_save_gallery', [self::class, 'handle_save_gallery']);
        add_action('wp_ajax_mgwpp_save_gallery_order', [self::class, 'handle_save_gallery_order']);
    }

    public static function register_edit_page()
    {
        add_submenu_page(
            '',
            'Edit Gallery',
            'Edit Gallery',
            'edit_mgwpp_sooras',
            'mgwpp-edit-gallery',
            [self::class, 'render_edit_page']
        );
    }

    public static function enqueue_assets($hook)
    {
        $is_edit_page = ($hook === 'admin_page_mgwpp-edit-gallery');
        if (!$is_edit_page) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_style(
            'mgwpp-edit-gallery-styles',
            MG_PLUGIN_URL . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.css",
            [],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.css")
        );

        wp_enqueue_script(
            'mgwpp-edit-gallery-scripts',
            MG_PLUGIN_URL . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.js",
            ['jquery', 'jquery-ui-sortable'],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/edit-gallery/mgwpp-edit-gallery.js"),
            true
        );

        wp_localize_script('mgwpp-edit-gallery-scripts', 'mgwppEdit', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_edit_gallery'),
            'i18n' => [
                'saveOrder' => __('Save Order', 'mini-gallery'),
                'saving' => __('Saving...', 'mini-gallery'),
                'saved' => __('Order saved!', 'mini-gallery'),
                'saveFailed' => __('Failed to save order', 'mini-gallery'),
                'noImages' => __('No images added to this gallery yet.', 'mini-gallery')
            ]
        ]);

        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
    }

    public static function render_edit_page()
    {
        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!$gallery_id || !$nonce || !wp_verify_nonce($nonce, 'mgwpp_edit_gallery')) {
            wp_die(esc_html__('Invalid gallery or security check failed.', 'mini-gallery'));
        }

        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'mgwpp_soora') {
            wp_die(esc_html__('Gallery not found.', 'mini-gallery'));
        }

        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        $images = [];

        if (!empty($gallery_images)) {
            $images = is_array($gallery_images)
                ? array_map('absint', $gallery_images)
                : array_map('absint', explode(',', $gallery_images));
        }

        self::render_editor($gallery, $gallery_id, $images);
    }

    private static function render_editor($gallery, $gallery_id, $images)
    {
        $current_type = get_post_meta($gallery_id, 'gallery_type', true);
        $preview_url = add_query_arg([
            'mgwpp_preview' => '1',
            'gallery_id'    => $gallery->ID,
        ], home_url('/'));
        $preview_url = wp_nonce_url($preview_url, 'mgwpp_preview');

        // Check for success message - updated is a read-only display flag, sanitized with absint
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag, no data modification
        $updated = isset($_GET['updated']) ? absint($_GET['updated']) : 0;
?>
        <div class="mgwpp-dashboard-container mgwpp-premium-dashboard">
            <?php if ($updated) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Gallery updated successfully!', 'mini-gallery'); ?></p>
                </div>
            <?php endif; ?>

            <h1><?php
                printf(
                    /* translators: %s: Gallery title/name */
                    esc_html__('Edit: %s', 'mini-gallery'),
                    esc_html($gallery->post_title)
                );
                ?></h1>

            <div class="mgwpp-glass-container">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mgwpp-gallery-form">
                    <input type="hidden" name="action" value="mgwpp_save_gallery">
                    <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery->ID); ?>">
                    <?php wp_nonce_field('mgwpp_save_gallery_data', 'mgwpp_gallery_nonce'); ?>

                    <!-- ROW 1: Title Section (Full Width on Top) -->
                    <div class="mgwpp-edit-section mgwpp-title-section">
                        <h2><span class="dashicons dashicons-edit"></span> <?php esc_html_e('Gallery Title', 'mini-gallery'); ?></h2>
                        <input type="text" name="post_title" value="<?php echo esc_attr($gallery->post_title); ?>"
                            class="widefat" placeholder="<?php esc_attr_e('Enter gallery title...', 'mini-gallery'); ?>">
                    </div>

                    <?php do_action('mgwpp_edit_gallery_before_images', $gallery); ?>

                    <!-- ROW 2: Gallery Images Section (Full Width) -->
                    <div class="mgwpp-edit-section mgwpp-images-section">
                        <h2>
                            <span class="dashicons dashicons-images-alt2"></span>
                            <?php esc_html_e('Gallery Images', 'mini-gallery'); ?>
                            <span class="mgwpp-reorder-hint"><?php esc_html_e('(Drag to reorder)', 'mini-gallery'); ?></span>
                        </h2>
                        <div class="mgwpp-image-manager">
                            <div class="mgwpp-image-container sortable">
                                <?php if (!empty($images)) : ?>
                                    <?php foreach ($images as $image_id) :
                                        $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                        if (!$thumb_url) {
                                            // Fallback for non-image attachments (3D models, etc)
                                            // Check if it's a 3D model we support to show a custom placeholder or generic icon
                                            if (self::is_allowed_3d_model($image_id)) {
                                                $thumb_url = wp_mime_type_icon($image_id);
                                            }
                                        }

                                        if ($thumb_url) : ?>
                                            <div class="mgwpp-image-item" data-id="<?php echo esc_attr($image_id); ?>">
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <input type="hidden" name="gallery_images[]" value="<?php echo esc_attr($image_id); ?>">
                                                <div class="mgwpp-item-actions">
                                                    <button type="button" class="mgwpp-remove-image"
                                                        title="<?php esc_attr_e('Remove from gallery', 'mini-gallery'); ?>">
                                                        <span class="dashicons dashicons-no"></span>
                                                    </button>
                                                    <button type="button" class="mgwpp-delete-image"
                                                        title="<?php esc_attr_e('Permanently delete', 'mini-gallery'); ?>">
                                                        <span class="dashicons dashicons-trash"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p class="mgwpp-no-images">
                                        <span class="dashicons dashicons-format-gallery" style="font-size: 32px; width: 32px; height: 32px; margin-bottom: 10px; display: block;"></span>
                                        <?php esc_html_e('No images added to this gallery yet. Click "Add Images" to get started.', 'mini-gallery'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="mgwpp-manager-actions">
                                <button type="button" class="button button-primary mgwpp-add-images">
                                    <span class="dashicons dashicons-cloud-upload"></span>
                                    <?php esc_html_e('Add Images', 'mini-gallery'); ?>
                                </button>
                                <button type="button" class="button mgwpp-save-order" id="mgwpp-save-order-btn">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('Save Order', 'mini-gallery'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3: Live Preview Section (Full Width with Device Toggles) -->
                    <div class="mgwpp-edit-section mgwpp-preview-section">
                        <div class="mgwpp-preview-header">
                            <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Live Preview', 'mini-gallery'); ?></h2>
                            <div class="mgwpp-device-toggles">
                                <button type="button" class="mgwpp-device-btn active" data-device="desktop" title="<?php esc_attr_e('Desktop', 'mini-gallery'); ?>">
                                    <span class="dashicons dashicons-desktop"></span>
                                </button>
                                <button type="button" class="mgwpp-device-btn" data-device="tablet" title="<?php esc_attr_e('Tablet', 'mini-gallery'); ?>">
                                    <span class="dashicons dashicons-tablet"></span>
                                </button>
                                <button type="button" class="mgwpp-device-btn" data-device="mobile" title="<?php esc_attr_e('Mobile', 'mini-gallery'); ?>">
                                    <span class="dashicons dashicons-smartphone"></span>
                                </button>
                            </div>
                        </div>
                        <div class="mgwpp-preview-wrapper" data-device="desktop">
                            <div class="mgwpp-preview-frame-container">
                                <iframe id="mgwpp-preview-frame" src="<?php echo esc_url($preview_url . '&minimal=1'); ?>"></iframe>
                            </div>
                        </div>
                        <p class="description">
                            <?php esc_html_e('Preview updates automatically when you save. Use device buttons to test responsiveness.', 'mini-gallery'); ?>
                        </p>
                    </div>

                    <!-- ROW 3: Gallery Type Selection (Full Width at Bottom) -->
                    <div class="mgwpp-edit-section mgwpp-type-section">
                        <h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Gallery Type', 'mini-gallery'); ?></h2>
                        <div class="mgwpp-gallery-types">
                            <?php foreach (self::$gallery_types as $type => $details) :
                                $type_image_url = MG_PLUGIN_URL . '/includes/admin/images/galleries-preview/' . $details[1];
                                $is_active = $type === $current_type;
                            ?>
                                <div class="mgwpp-gallery-type <?php echo $is_active ? 'active' : ''; ?>">
                                    <label>
                                        <input type="radio" name="gallery_type" value="<?php echo esc_attr($type); ?>"
                                            <?php checked($is_active); ?>>
                                        <img class="mgwpp-stat-card" src="<?php echo esc_url($type_image_url); ?>"
                                            alt="<?php echo esc_attr($details[0]); ?>">
                                        <span><?php echo esc_html($details[0]); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="mgwpp-edit-actions">
                        <button type="submit" name="submit" class="button button-primary">
                            <span class="dashicons dashicons-saved"></span>
                            <?php esc_html_e('Save Changes', 'mini-gallery'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
<?php
    }

    public static function handle_save_gallery_order()
    {
        try {
            $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

            if (!$nonce || !wp_verify_nonce($nonce, 'mgwpp_edit_gallery')) {
                wp_send_json_error(['message' => __('Security check failed', 'mini-gallery')]);
            }

            if (!current_user_can('edit_mgwpp_sooras')) {
                wp_send_json_error(['message' => __('Insufficient permissions', 'mini-gallery')]);
            }

            $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
            $image_ids = isset($_POST['image_ids']) ? array_map('absint', $_POST['image_ids']) : [];

            if (!$gallery_id || get_post_type($gallery_id) !== 'mgwpp_soora') {
                wp_send_json_error(['message' => __('Invalid gallery ID', 'mini-gallery')]);
            }

            // Validation: Allow images AND supported 3D models
            $valid_ids = array_filter($image_ids, function ($id) {
                return wp_attachment_is_image($id) || self::is_allowed_3d_model($id);
            });
            update_post_meta($gallery_id, 'gallery_images', $valid_ids);

            wp_send_json_success([
                'message' => __('Image order saved', 'mini-gallery'),
                'total_images' => count($valid_ids)
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public static function handle_save_gallery()
    {
        $nonce = isset($_POST['mgwpp_gallery_nonce']) ? sanitize_text_field(wp_unslash($_POST['mgwpp_gallery_nonce'])) : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'mgwpp_save_gallery_data')) {
            wp_die(esc_html__('Security check failed.', 'mini-gallery'));
        }

        if (!current_user_can('edit_mgwpp_sooras')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'mini-gallery'));
        }

        $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
        if (!$gallery_id || get_post_type($gallery_id) !== 'mgwpp_soora') {
            wp_die(esc_html__('Invalid gallery ID.', 'mini-gallery'));
        }

        if (isset($_POST['post_title'])) {
            $title = sanitize_text_field(wp_unslash($_POST['post_title']));
            wp_update_post([
                'ID' => $gallery_id,
                'post_title' => $title
            ]);
        }

        if (isset($_POST['gallery_type'])) {
            $gallery_type = sanitize_text_field(wp_unslash($_POST['gallery_type']));
            if (array_key_exists($gallery_type, self::$gallery_types)) {
                update_post_meta($gallery_id, 'gallery_type', $gallery_type);
            }
        }

        if (isset($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
            $images = array_map('absint', $_POST['gallery_images']);
            // Validation: Allow images AND supported 3D models
            $valid_images = array_filter($images, function ($id) {
                return wp_attachment_is_image($id) || self::is_allowed_3d_model($id);
            });
            update_post_meta($gallery_id, 'gallery_images', $valid_images);
        }

        do_action('mgwpp_save_gallery_data', $gallery_id, $_POST);

        $redirect_url = add_query_arg([
            'gallery_id' => $gallery_id,
            '_wpnonce' => wp_create_nonce('mgwpp_edit_gallery'),
            'updated' => 1
        ], admin_url('admin.php?page=mgwpp-edit-gallery'));

        wp_safe_redirect($redirect_url);
        exit;
    }

    public function render_preview_section($gallery_id)
    {
        $preview_url = add_query_arg([
            'mgwpp_preview' => '1',
            'gallery_id' => $gallery_id,
            '_wpnonce' => wp_create_nonce('mgwpp_preview')
        ], home_url('/'));

        echo '<div class="mgwpp-preview-wrapper">';
        echo '<iframe src="' . esc_url($preview_url) . '" width="100%" height="800px" frameborder="0" class="mgwpp-live-preview"></iframe>';
        echo '</div>';
    }

    private static function is_allowed_3d_model($attachment_id)
    {
        $mime = get_post_mime_type($attachment_id);
        $allowed = [
            'model/gltf+json',
            'model/gltf-binary',
            'model/obj',
            'application/octet-stream' // fbx often detected as this
        ];
        return in_array($mime, $allowed, true);
    }
}

MGWPP_Edit_Gallery_View::init();
