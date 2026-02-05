<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';


class MGWPP_Galleries_View
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

    private $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    private static function enqueue_gallery_scripts()
    {
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
        $plugin_version = $plugin_data['Version'];

        wp_enqueue_style(
            'mgwpp-admin-galleries',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.css', dirname(__FILE__, 3)),
            array(),
            $plugin_version
        );

        // Fixed script enqueueing with explicit dependencies and footer parameter
        wp_enqueue_script(
            'mgwpp-admin-galleries-js',
            plugins_url('admin/views/galleries/mgwpp-galleries-view.js', dirname(__FILE__, 3)),
            array('jquery', 'thickbox'), // Explicit dependencies
            $plugin_version,
            true // Explicitly load in footer
        );

        wp_localize_script('mgwpp-admin-galleries-js', 'mgwppAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-admin-nonce'),
            'i18n' => [
                'selectImages' => __('Select Images', 'mini-gallery'),
                'selectModel' => __('Select 3D Models', 'mini-gallery'),
                'createGallery' => __('Create Gallery', 'mini-gallery'),
                'copied' => __('Copied!', 'mini-gallery'),
                'copyFailed' => __('Failed to copy', 'mini-gallery'),
                'threedRequired' => __('Exactly 3 models are required for this gallery style.', 'mini-gallery'),
                /* translators: %d: number of galleries selected */
                'selectedCount' => __('%d galleries selected', 'mini-gallery'),
                'confirmDelete' => __('Are you sure you want to delete the selected galleries?', 'mini-gallery'),
                'deleteError' => __('An error occurred while deleting galleries.', 'mini-gallery')
            ]
        ]);
    }


    private static function get_plugin_asset_image($relative_path, $attributes = [])
    {
        $src = esc_url(MG_PLUGIN_URL . '/includes/admin/images/' . $relative_path);

        $default_attributes = [
            'loading' => 'lazy'
        ];

        $attributes = array_merge($default_attributes, $attributes);

        $attr_string = '';
        foreach ($attributes as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }

        // Return safely escaped image tag
        return sprintf('<img src="%s"%s>', esc_url($src), $attr_string);
    }

    public function render()
    {
?>
        <div class="mgwpp-dashboard-container mgwpp-premium-dashboard">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">

                    <?php MGWPP_Inner_Header::render(); ?>

                    <div class="wrap">
                        <div class="mgwpp-dashboard-header">
                            <h1 class="wp-heading-inline">
                                <?php esc_html_e('Galleries', 'mini-gallery'); ?>
                            </h1>
                            <div class="mgwpp-header-actions">
                                <button type="button" id="mgwpp-open-create-modal" class="mgwpp-btn mgwpp-btn-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php esc_html_e('Add New Gallery', 'mini-gallery'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Bulk Actions Container -->
                        <div class="mgwpp-bulk-actions-bar" style="display:none;">
                            <div class="mgwpp-bulk-controls">
                                <select id="mgwpp-bulk-action">
                                    <option value="-1"><?php esc_html_e('Bulk Actions', 'mini-gallery'); ?></option>
                                    <option value="delete"><?php esc_html_e('Delete Selected', 'mini-gallery'); ?></option>
                                </select>
                                <button type="button" id="mgwpp-apply-bulk-action" class="mgwpp-btn mgwpp-btn-secondary">
                                    <?php esc_html_e('Apply', 'mini-gallery'); ?>
                                </button>
                            </div>
                        </div>

                        <?php if (empty($this->items)) : ?>
                            <div class="mgwpp-empty-state">
                                <div class="mgwpp-empty-icon">
                                    <?php
                                    echo wp_kses_post(self::get_plugin_asset_image(
                                        'icons/empty-galleries.webp',
                                        ['alt' => __('No galleries', 'mini-gallery'), 'class' => 'mgwpp-empty-logo']
                                    ));
                                    ?>
                                </div>
                                <h3><?php esc_html_e('No galleries found', 'mini-gallery'); ?></h3>
                                <p><?php esc_html_e('Create your first gallery to get started', 'mini-gallery'); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="mgwpp-gallery-grid">
                                <?php foreach ($this->items as $item) : ?>
                                    <div class="mgwpp-gallery-card">
                                        <div class="mgwpp-card-inner">
                                            <div class="mgwpp-card-header">
                                                <div class="mgwpp-card-glare"></div>
                                                <div class="mgwpp-gallery-preview">
                                                    <div class="mgwpp-checkbox-wrapper">
                                                        <input type="checkbox"
                                                            name="bulk_delete[]"
                                                            class="mgwpp-bulk-checkbox"
                                                            value="<?php echo esc_attr($item['ID']); ?>">
                                                    </div>
                                                    <?php echo wp_kses_post($this->get_gallery_preview(esc_attr($item['ID']))); ?>

                                                    <div class="mgwpp-card-overlay">
                                                        <div class="mgwpp-overlay-actions">
                                                            <?php echo wp_kses($item['actions'], [
                                                                'a' => [
                                                                    'href' => [],
                                                                    'class' => [],
                                                                    'data-id' => [],
                                                                    'data-nonce' => [],
                                                                    'title' => [],
                                                                ],
                                                                'span' => [
                                                                    'class' => [],
                                                                ],
                                                            ]);  ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mgwpp-card-body">
                                                <div class="mgwpp-card-title-row">
                                                    <h3 class="mgwpp-card-title">
                                                        <?php echo esc_html($item['title']); ?>
                                                    </h3>
                                                    <span class="mgwpp-card-type-badge">
                                                        <?php echo esc_html($item['type']); ?>
                                                    </span>
                                                </div>

                                                <div class="mgwpp-card-meta">
                                                    <span class="mgwpp-card-date">
                                                        <span class="dashicons dashicons-calendar-alt"></span>
                                                        <?php echo esc_html($item['date']); ?>
                                                    </span>
                                                </div>

                                                <div class="mgwpp-card-shortcode-wrapper">
                                                    <div class="mgwpp-shortcode-box">
                                                        <code><?php echo esc_html($item['shortcode']); ?></code>
                                                        <button class="mgwpp-copy-btn mgwpp-copy-shortcode"
                                                            data-clipboard-text="<?php echo esc_attr($item['shortcode']); ?>"
                                                            title="<?php esc_attr_e('Copy Shortcode', 'mini-gallery'); ?>">
                                                            <span class="dashicons dashicons-admin-page"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
        self::render_preview_modal();
        self::render_create_gallery_modal();
        self::enqueue_gallery_scripts();
    }
    private function get_gallery_preview($gallery_id)
    {
        $image_ids = get_post_meta($gallery_id, 'gallery_images', true);

        if (empty($image_ids)) {
            return $this->get_fallback_preview();
        }

        if (!is_array($image_ids)) {
            $image_ids = explode(',', $image_ids);
        }

        // Check if this is a 3D model gallery
        $gallery_type = get_post_meta($gallery_id, 'gallery_type', true);

        if ($gallery_type === '3d_model_carousel') {
            return $this->render_3d_model_preview($image_ids);
        }

        return $this->render_image_thumbnails($image_ids);
    }

    /**
     * Special preview for 3D model galleries
     */
    private function render_3d_model_preview($model_ids)
    {
        if (!is_array($model_ids) || empty($model_ids)) {
            return $this->get_fallback_preview();
        }

        $output = '<div class="mgwpp-3d-model-preview">';
        $output .= '<div class="mgwpp-3d-model-icon">';
        $output .= '<svg width="48" height="48" viewBox="0 0 24 24" fill="#0073aa">';
        $output .= '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>';
        $output .= '</svg>';
        $output .= '</div>';

        $model_count = count($model_ids);
        $model_text = sprintf(
            /* translators: %d: number of 3D models */
            _n('%d 3D Model', '%d 3D Models', $model_count, 'mini-gallery'),
            $model_count
        );

        $output .= '<div class="mgwpp-3d-model-count">' . esc_html($model_text) . '</div>';
        $output .= '</div>';

        return $output;
    }


    private function render_image_thumbnails($images)
    {
        if (is_string($images)) {
            $images = array_filter(explode(',', $images));
        }

        if (!is_array($images) || empty($images)) {
            return $this->get_fallback_preview();
        }

        if (count($images) === 1) {
            return $this->render_single_preview($images[0]);
        }

        $output = '<div class="mgwpp-preview-thumbnails">';
        $count = 0;
        $max_thumbnails = 4;

        foreach ($images as $image_id) {
            if ($count >= $max_thumbnails) {
                break;
            }

            if ($image_id > 0 && wp_attachment_is_image($image_id)) {
                $image_html = wp_get_attachment_image($image_id, 'thumbnail', false, ['class' => 'mgwpp-preview-thumb']);
                if ($image_html) {
                    $output .= $image_html;
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return $this->get_fallback_preview();
        }

        $total_images = count($images);
        if ($total_images > $max_thumbnails) {
            $remaining = $total_images - $max_thumbnails;
            $output .= sprintf(
                '<div class="mgwpp-preview-more">+%d</div>',
                $remaining
            );
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Special preview for single-image galleries using WordPress functions
     */
    private function render_single_preview($image_id)
    {
        $image_id = intval(trim($image_id));
        if ($image_id <= 0 || !wp_attachment_is_image($image_id)) {
            return $this->get_fallback_preview();
        }

        $image_html = wp_get_attachment_image(
            $image_id,
            'medium',
            false,
            [
                'class' => 'mgwpp-preview-thumb',
                'loading' => 'lazy'
            ]
        );

        if (!$image_html) {
            return $this->get_fallback_preview();
        }

        return sprintf(
            '<div class="mgwpp-single-preview">%s</div>',
            $image_html
        );
    }


    private function get_fallback_preview()
    {
        return sprintf(
            '<div class="mgwpp-preview-fallback">%s</div>',
            self::get_plugin_asset_image(
                'default-gallery.webp',
                ['alt' => __('Default gallery preview', 'mini-gallery')]
            )
        );
    }



    private static function render_preview_modal()
    {
    ?>
        <div id="mgwpp-preview-gallery-modal" class="mgwpp-modal" style="display:none;">
            <div class="mgwpp-modal-overlay"></div>
            <div class="mgwpp-modal-content mgwpp-preview-modal-content">
                <div class="mgwpp-modal-header">
                    <h2><?php esc_html_e('Gallery Preview', 'mini-gallery'); ?></h2>
                    <button type="button" class="mgwpp-modal-close">&times;</button>
                </div>
                <div class="mgwpp-modal-body">
                    <div id="mgwpp-preview-content">
                        <div class="mgwpp-preview-loader">
                            <div class="mgwpp-loading-spinner"></div>
                            <p><?php esc_html_e('Loading preview...', 'mini-gallery'); ?></p>
                        </div>
                        <iframe id="mgwpp-preview-iframe" src="" frameborder="0" style="width:100%; height:600px; display:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_create_gallery_modal()
    {
    ?>
        <div id="mgwpp-create-gallery-modal" class="mgwpp-modal" style="display:none;">
            <div class="mgwpp-modal-overlay"></div>
            <div class="mgwpp-modal-content mgwpp-create-modal-content">
                <div class="mgwpp-modal-header">
                    <h2><?php esc_html_e('Create New Gallery', 'mini-gallery'); ?></h2>
                    <button type="button" class="mgwpp-modal-close">&times;</button>
                </div>
                <div class="mgwpp-modal-body">
                    <form id="mgwpp-create-gallery-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                        <input type="hidden" name="action" value="mgwpp_create_gallery">
                        <?php wp_nonce_field('mgwpp_create_gallery', 'mgwpp_gallery_nonce'); ?>

                        <div class="mgwpp-form-group">
                            <label for="mgwpp-create-gallery-title"><?php esc_html_e('Gallery Title', 'mini-gallery'); ?></label>
                            <input type="text" id="mgwpp-create-gallery-title" name="gallery_title" placeholder="<?php esc_attr_e('Enter gallery title (optional - auto-generated if empty)', 'mini-gallery'); ?>">
                        </div>

                        <div class="mgwpp-form-group">
                            <label for="mgwpp-create-gallery-type"><?php esc_html_e('Gallery Style', 'mini-gallery'); ?></label>
                            <div class="mgwpp-select-wrapper">
                                <select id="mgwpp-create-gallery-type" name="gallery_type" required>
                                    <?php foreach (self::$gallery_types as $key => $type) : ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($type[0]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mgwpp-form-group mgwpp-media-section">
                            <label id="mgwpp-media-label"><?php esc_html_e('Gallery Images', 'mini-gallery'); ?></label>
                            <p class="mgwpp-field-hint" id="mgwpp-3d-hint" style="display:none;">
                                <span class="dashicons dashicons-info"></span>
                                <?php esc_html_e('3D Models required: Please select exactly 3 model files (.glb, .gltf, .fbx).', 'mini-gallery'); ?>
                            </p>

                            <div class="mgwpp-upload-zone">
                                <input type="hidden" name="selected_media" id="mgwpp-create-selected-media" value="">
                                <button type="button" class="mgwpp-btn mgwpp-btn-secondary mgwpp-media-upload">
                                    <span class="dashicons dashicons-cloud-upload"></span>
                                    <span id="mgwpp-upload-btn-text"><?php esc_html_e('Select Images', 'mini-gallery'); ?></span>
                                </button>
                                <div class="mgwpp-media-preview-container">
                                    <div class="mgwpp-media-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div id="mgwpp-gallery-notice" class="mgwpp-notice" style="display:none;"></div>

                        <div class="mgwpp-modal-footer">
                            <p class="mgwpp-auto-create-hint">
                                <span class="dashicons dashicons-info"></span>
                                <?php esc_html_e('Gallery will be created automatically after selecting images.', 'mini-gallery'); ?>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Loading overlay -->
                <div id="mgwpp-create-loading" class="mgwpp-loading-overlay" style="display:none;">
                    <div class="mgwpp-loading-spinner"></div>
                    <p><?php esc_html_e('Assembling your gallery...', 'mini-gallery'); ?></p>
                </div>
            </div>
        </div>
<?php
    }
}
