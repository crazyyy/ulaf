<?php

/**
 * Marquee Gallery Admin Settings
 * 
 * Provides a visual layer editor for configuring marquee gallery layers
 * with an Elementor-style intuitive interface.
 *
 * @package MiniGallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Marquee_Admin
{
    /**
     * Initialize admin hooks
     */
    public static function init()
    {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_mgwpp_soora', [__CLASS__, 'save_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('wp_ajax_mgwpp_save_marquee_layers', [__CLASS__, 'ajax_save_layers']);
    }

    /**
     * Add meta boxes for marquee settings
     */
    public static function add_meta_boxes()
    {
        add_meta_box(
            'mgwpp_marquee_settings',
            __('Marquee Gallery Editor', 'mini-gallery'),
            [__CLASS__, 'render_settings_meta_box'],
            'mgwpp_soora',
            'normal',
            'high'
        );
    }

    /**
     * Render the visual layer editor
     *
     * @param WP_Post $post Current post object
     */
    public static function render_settings_meta_box($post)
    {
        // Only show for marquee gallery type
        $gallery_type = get_post_meta($post->ID, 'gallery_type', true);
        if ($gallery_type !== 'marquee_gallery') {
            echo '<p class="description">' . esc_html__('Select "Marquee Gallery" as the gallery type to enable the layer editor.', 'mini-gallery') . '</p>';
            return;
        }

        // Get current settings
        $settings = get_post_meta($post->ID, '_mgwpp_marquee_settings', true);
        if (!class_exists('MGWPP_Marquee_Gallery')) {
            require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-marquee-gallery/class-mgwpp-marquee-gallery.php';
        }
        $defaults = MGWPP_Marquee_Gallery::get_default_settings();
        $settings = wp_parse_args($settings ?: [], $defaults);

        // Get gallery images
        $gallery_images = get_post_meta($post->ID, 'gallery_images', true);
        $image_ids = [];
        if (!empty($gallery_images)) {
            $image_ids = is_array($gallery_images) ? $gallery_images : explode(',', $gallery_images);
        }

        // Get layer assignments
        $layer_images = get_post_meta($post->ID, '_mgwpp_marquee_layer_images', true);
        if (empty($layer_images)) {
            $layer_images = [1 => [], 2 => [], 3 => []];
        }

        wp_nonce_field('mgwpp_marquee_settings_save', 'mgwpp_marquee_nonce');
?>

        <div class="mgwpp-marquee-editor" id="mgwpp-marquee-editor" data-gallery-id="<?php echo esc_attr($post->ID); ?>">

            <!-- Layer Selector Sidebar -->
            <div class="mgwpp-layer-sidebar">
                <div class="mgwpp-layer-dots">
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                        <button type="button"
                            class="mgwpp-layer-dot <?php echo $i === 1 ? 'active' : ''; ?>"
                            data-layer="<?php echo esc_attr($i); ?>"
                            title="<?php /* translators: %d: Layer number */ printf(esc_attr__('Layer %d', 'mini-gallery'), esc_attr($i)); ?>">
                            <span class="dot"></span>
                        </button>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Main Editor Area -->
            <div class="mgwpp-editor-main">

                <!-- Layer Indicator -->
                <div class="mgwpp-layer-indicator">
                    <span class="mgwpp-layer-label">LAYER 1</span>
                    <div class="mgwpp-layer-settings-toggle">
                        <button type="button" class="mgwpp-settings-btn" title="<?php esc_attr_e('Layer Settings', 'mini-gallery'); ?>">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </button>
                    </div>
                </div>

                <!-- Layer Content Panels -->
                <?php for ($layer = 1; $layer <= 3; $layer++) :
                    $layer_key = 'layer_' . $layer;
                    $layer_settings = isset($settings[$layer_key]) ? $settings[$layer_key] : [];
                    $current_layer_images = isset($layer_images[$layer]) ? $layer_images[$layer] : [];
                ?>
                    <div class="mgwpp-layer-panel <?php echo $layer === 1 ? 'active' : ''; ?>" data-layer="<?php echo esc_attr($layer); ?>">

                        <!-- Layer Settings (Hidden by default) -->
                        <div class="mgwpp-layer-settings-panel">
                            <div class="mgwpp-setting-group">
                                <label><?php esc_html_e('Direction', 'mini-gallery'); ?></label>
                                <div class="mgwpp-direction-toggle">
                                    <button type="button" class="mgwpp-dir-btn <?php echo ($layer_settings['direction'] ?? 'left') === 'left' ? 'active' : ''; ?>" data-dir="left">
                                        <span class="dashicons dashicons-arrow-left-alt"></span>
                                        <?php esc_html_e('Left', 'mini-gallery'); ?>
                                    </button>
                                    <button type="button" class="mgwpp-dir-btn <?php echo ($layer_settings['direction'] ?? 'left') === 'right' ? 'active' : ''; ?>" data-dir="right">
                                        <span class="dashicons dashicons-arrow-right-alt"></span>
                                        <?php esc_html_e('Right', 'mini-gallery'); ?>
                                    </button>
                                </div>
                                <input type="hidden" name="mgwpp_marquee[<?php echo esc_attr($layer_key); ?>][direction]"
                                    value="<?php echo esc_attr($layer_settings['direction'] ?? 'left'); ?>" class="direction-input">
                            </div>

                            <div class="mgwpp-setting-group">
                                <label><?php esc_html_e('Speed', 'mini-gallery'); ?></label>
                                <div class="mgwpp-speed-slider">
                                    <input type="range" min="5" max="60"
                                        value="<?php echo esc_attr($layer_settings['speed'] ?? 30); ?>"
                                        class="speed-range">
                                    <span class="speed-value"><?php echo esc_html($layer_settings['speed'] ?? 30); ?>s</span>
                                </div>
                                <input type="hidden" name="mgwpp_marquee[<?php echo esc_attr($layer_key); ?>][speed]"
                                    value="<?php echo esc_attr($layer_settings['speed'] ?? 30); ?>" class="speed-input">
                            </div>

                            <div class="mgwpp-setting-group">
                                <label class="mgwpp-checkbox-label">
                                    <input type="checkbox" name="mgwpp_marquee[<?php echo esc_attr($layer_key); ?>][pause_on_hover]"
                                        value="1" <?php checked(!empty($layer_settings['pause_on_hover']) || !isset($layer_settings['pause_on_hover'])); ?>>
                                    <?php esc_html_e('Pause on Hover', 'mini-gallery'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Drop Zone -->
                        <div class="mgwpp-drop-zone" data-layer="<?php echo esc_attr($layer); ?>">
                            <?php if (!empty($current_layer_images)) : ?>
                                <div class="mgwpp-layer-images">
                                    <?php foreach ($current_layer_images as $img_id) :
                                        $thumb = wp_get_attachment_image_url(absint($img_id), 'thumbnail');
                                        if ($thumb) :
                                    ?>
                                            <div class="mgwpp-layer-image" data-id="<?php echo esc_attr($img_id); ?>">
                                                <img src="<?php echo esc_url($thumb); ?>" alt="">
                                                <button type="button" class="mgwpp-remove-img" title="<?php esc_attr_e('Remove', 'mini-gallery'); ?>">
                                                    <span class="dashicons dashicons-no-alt"></span>
                                                </button>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="mgwpp-drop-placeholder">
                                    <div class="mgwpp-drop-icons">
                                        <button type="button" class="mgwpp-add-images-btn" title="<?php esc_attr_e('Add Images', 'mini-gallery'); ?>">
                                            <span class="dashicons dashicons-plus-alt2"></span>
                                        </button>
                                        <button type="button" class="mgwpp-browse-btn" title="<?php esc_attr_e('Media Library', 'mini-gallery'); ?>">
                                            <span class="dashicons dashicons-admin-media"></span>
                                        </button>
                                    </div>
                                    <p><?php esc_html_e('Drag images here', 'mini-gallery'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="mgwpp_layer_images[<?php echo esc_attr($layer); ?>]"
                            value="<?php echo esc_attr(implode(',', $current_layer_images)); ?>"
                            class="layer-images-input">
                    </div>
                <?php endfor; ?>

            </div>

            <!-- Image Pool (Available Images) -->
            <div class="mgwpp-image-pool">
                <h4><?php esc_html_e('Gallery Images', 'mini-gallery'); ?></h4>
                <div class="mgwpp-pool-images">
                    <?php if (!empty($image_ids)) :
                        foreach ($image_ids as $img_id) :
                            $thumb = wp_get_attachment_image_url(absint($img_id), 'thumbnail');
                            if ($thumb) :
                    ?>
                                <div class="mgwpp-pool-image" data-id="<?php echo esc_attr($img_id); ?>" draggable="true">
                                    <img src="<?php echo esc_url($thumb); ?>" alt="">
                                </div>
                        <?php endif;
                        endforeach;
                    else : ?>
                        <p class="mgwpp-no-images"><?php esc_html_e('Add images to gallery first', 'mini-gallery'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <style>
            /* ============================================
           Marquee Layer Editor Styles
           ============================================ */
            .mgwpp-marquee-editor {
                display: flex;
                gap: 0;
                background: #f8f9fa;
                border-radius: 8px;
                overflow: hidden;
                min-height: 400px;
                border: 1px solid #e0e0e0;
            }

            /* Layer Sidebar with Dots */
            .mgwpp-layer-sidebar {
                width: 40px;
                background: linear-gradient(180deg, #1e3a5f 0%, #0d1f33 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px 0;
            }

            .mgwpp-layer-dots {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .mgwpp-layer-dot {
                width: 24px;
                height: 24px;
                background: transparent;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                transition: transform 0.2s ease;
            }

            .mgwpp-layer-dot:hover {
                transform: scale(1.2);
            }

            .mgwpp-layer-dot .dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                border: 2px solid rgba(255, 255, 255, 0.5);
                transition: all 0.2s ease;
            }

            .mgwpp-layer-dot.active .dot {
                background: #3b82f6;
                border-color: #60a5fa;
                box-shadow: 0 0 10px rgba(59, 130, 246, 0.6);
            }

            /* Main Editor Area */
            .mgwpp-editor-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                position: relative;
            }

            /* Layer Indicator (Top Label) */
            .mgwpp-layer-indicator {
                background: #3b82f6;
                color: white;
                padding: 8px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid #2563eb;
            }

            .mgwpp-layer-label {
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
            }

            .mgwpp-settings-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 4px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s ease;
            }

            .mgwpp-settings-btn:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .mgwpp-settings-btn .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }

            /* Layer Panels */
            .mgwpp-layer-panel {
                display: none;
                flex-direction: column;
                flex: 1;
            }

            .mgwpp-layer-panel.active {
                display: flex;
            }

            /* Layer Settings Panel */
            .mgwpp-layer-settings-panel {
                display: none;
                padding: 16px;
                background: #fff;
                border-bottom: 1px solid #e0e0e0;
                gap: 16px;
            }

            .mgwpp-layer-settings-panel.open {
                display: flex;
                flex-wrap: wrap;
            }

            .mgwpp-setting-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
                min-width: 150px;
            }

            .mgwpp-setting-group label {
                font-size: 11px;
                font-weight: 600;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Direction Toggle */
            .mgwpp-direction-toggle {
                display: flex;
                gap: 4px;
            }

            .mgwpp-dir-btn {
                flex: 1;
                padding: 8px 12px;
                border: 1px solid #ddd;
                background: #fff;
                cursor: pointer;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
                font-size: 12px;
                transition: all 0.2s ease;
            }

            .mgwpp-dir-btn:hover {
                border-color: #3b82f6;
                color: #3b82f6;
            }

            .mgwpp-dir-btn.active {
                background: #3b82f6;
                border-color: #3b82f6;
                color: white;
            }

            .mgwpp-dir-btn .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
            }

            /* Speed Slider */
            .mgwpp-speed-slider {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .mgwpp-speed-slider input[type="range"] {
                flex: 1;
                accent-color: #3b82f6;
            }

            .mgwpp-speed-slider .speed-value {
                font-size: 12px;
                font-weight: 600;
                color: #3b82f6;
                min-width: 35px;
            }

            /* Checkbox Label */
            .mgwpp-checkbox-label {
                display: flex !important;
                align-items: center;
                gap: 8px;
                cursor: pointer;
                font-size: 13px !important;
                text-transform: none !important;
                font-weight: 500 !important;
            }

            .mgwpp-checkbox-label input {
                accent-color: #3b82f6;
            }

            /* Drop Zone */
            .mgwpp-drop-zone {
                flex: 1;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                min-height: 200px;
                transition: all 0.3s ease;
            }

            .mgwpp-drop-zone.drag-over {
                background: rgba(59, 130, 246, 0.05);
                border: 2px dashed #3b82f6;
            }

            .mgwpp-drop-placeholder {
                text-align: center;
                color: #999;
            }

            .mgwpp-drop-icons {
                display: flex;
                gap: 12px;
                justify-content: center;
                margin-bottom: 12px;
            }

            .mgwpp-drop-icons button {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                border: 1px solid #ddd;
                background: #fff;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }

            .mgwpp-drop-icons button:hover {
                border-color: #3b82f6;
                color: #3b82f6;
                transform: scale(1.05);
            }

            .mgwpp-drop-icons .dashicons {
                font-size: 20px;
                width: 20px;
                height: 20px;
            }

            .mgwpp-drop-placeholder p {
                margin: 0;
                font-size: 13px;
            }

            /* Layer Images Grid */
            .mgwpp-layer-images {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                width: 100%;
            }

            .mgwpp-layer-image {
                width: 80px;
                height: 80px;
                border-radius: 6px;
                overflow: hidden;
                position: relative;
                border: 2px solid transparent;
                transition: all 0.2s ease;
            }

            .mgwpp-layer-image:hover {
                border-color: #3b82f6;
            }

            .mgwpp-layer-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .mgwpp-remove-img {
                position: absolute;
                top: 4px;
                right: 4px;
                width: 20px;
                height: 20px;
                background: rgba(239, 68, 68, 0.9);
                border: none;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 0;
            }

            .mgwpp-layer-image:hover .mgwpp-remove-img {
                display: flex;
            }

            .mgwpp-remove-img .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
            }

            /* Image Pool */
            .mgwpp-image-pool {
                width: 180px;
                background: #fff;
                border-left: 1px solid #e0e0e0;
                padding: 16px;
                overflow-y: auto;
            }

            .mgwpp-image-pool h4 {
                margin: 0 0 12px 0;
                font-size: 12px;
                font-weight: 600;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .mgwpp-pool-images {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .mgwpp-pool-image {
                width: 60px;
                height: 60px;
                border-radius: 4px;
                overflow: hidden;
                cursor: grab;
                border: 2px solid transparent;
                transition: all 0.2s ease;
            }

            .mgwpp-pool-image:hover {
                border-color: #3b82f6;
                transform: scale(1.05);
            }

            .mgwpp-pool-image.dragging {
                opacity: 0.5;
            }

            .mgwpp-pool-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .mgwpp-no-images {
                color: #999;
                font-size: 12px;
                text-align: center;
                margin: 0;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                var editor = $('#mgwpp-marquee-editor');
                var currentLayer = 1;

                // Layer dot selection
                editor.on('click', '.mgwpp-layer-dot', function() {
                    var layer = $(this).data('layer');
                    currentLayer = layer;

                    // Update dots
                    $('.mgwpp-layer-dot').removeClass('active');
                    $(this).addClass('active');

                    // Update label
                    $('.mgwpp-layer-label').text('LAYER ' + layer);

                    // Show corresponding panel
                    $('.mgwpp-layer-panel').removeClass('active');
                    $('.mgwpp-layer-panel[data-layer="' + layer + '"]').addClass('active');
                });

                // Toggle settings panel
                editor.on('click', '.mgwpp-settings-btn', function() {
                    var panel = $('.mgwpp-layer-panel.active .mgwpp-layer-settings-panel');
                    panel.toggleClass('open');
                });

                // Direction toggle
                editor.on('click', '.mgwpp-dir-btn', function() {
                    var panel = $(this).closest('.mgwpp-layer-panel');
                    var dir = $(this).data('dir');

                    panel.find('.mgwpp-dir-btn').removeClass('active');
                    $(this).addClass('active');
                    panel.find('.direction-input').val(dir);
                });

                // Speed slider
                editor.on('input', '.speed-range', function() {
                    var val = $(this).val();
                    var panel = $(this).closest('.mgwpp-layer-panel');

                    panel.find('.speed-value').text(val + 's');
                    panel.find('.speed-input').val(val);
                });

                // Drag and drop from pool to layer
                var draggedImage = null;

                editor.on('dragstart', '.mgwpp-pool-image', function(e) {
                    draggedImage = $(this);
                    $(this).addClass('dragging');
                    e.originalEvent.dataTransfer.setData('text/plain', $(this).data('id'));
                });

                editor.on('dragend', '.mgwpp-pool-image', function() {
                    $(this).removeClass('dragging');
                    draggedImage = null;
                });

                editor.on('dragover', '.mgwpp-drop-zone', function(e) {
                    e.preventDefault();
                    $(this).addClass('drag-over');
                });

                editor.on('dragleave', '.mgwpp-drop-zone', function() {
                    $(this).removeClass('drag-over');
                });

                editor.on('drop', '.mgwpp-drop-zone', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');

                    var imgId = e.originalEvent.dataTransfer.getData('text/plain');
                    if (!imgId || !draggedImage) return;

                    var layer = $(this).data('layer');
                    var imgSrc = draggedImage.find('img').attr('src');

                    // Remove placeholder if exists
                    $(this).find('.mgwpp-drop-placeholder').remove();

                    // Add images container if not exists
                    var container = $(this).find('.mgwpp-layer-images');
                    if (!container.length) {
                        container = $('<div class="mgwpp-layer-images"></div>');
                        $(this).append(container);
                    }

                    // Check if image already in this layer
                    if (container.find('[data-id="' + imgId + '"]').length) return;

                    // Add image
                    var html = '<div class="mgwpp-layer-image" data-id="' + imgId + '">' +
                        '<img src="' + imgSrc + '" alt="">' +
                        '<button type="button" class="mgwpp-remove-img" title="Remove">' +
                        '<span class="dashicons dashicons-no-alt"></span></button></div>';
                    container.append(html);

                    updateLayerInput(layer);
                });

                // Remove image from layer
                editor.on('click', '.mgwpp-remove-img', function() {
                    var item = $(this).closest('.mgwpp-layer-image');
                    var dropZone = $(this).closest('.mgwpp-drop-zone');
                    var layer = dropZone.data('layer');

                    item.remove();

                    // Show placeholder if empty
                    if (!dropZone.find('.mgwpp-layer-image').length) {
                        dropZone.find('.mgwpp-layer-images').remove();
                        dropZone.html('<div class="mgwpp-drop-placeholder">' +
                            '<div class="mgwpp-drop-icons">' +
                            '<button type="button" class="mgwpp-add-images-btn" title="Add Images">' +
                            '<span class="dashicons dashicons-plus-alt2"></span></button>' +
                            '<button type="button" class="mgwpp-browse-btn" title="Media Library">' +
                            '<span class="dashicons dashicons-admin-media"></span></button></div>' +
                            '<p>Drag images here</p></div>');
                    }

                    updateLayerInput(layer);
                });

                // Add images via media library
                editor.on('click', '.mgwpp-add-images-btn, .mgwpp-browse-btn', function() {
                    var dropZone = $(this).closest('.mgwpp-drop-zone');
                    var layer = dropZone.data('layer');

                    var frame = wp.media({
                        title: 'Select Images for Layer ' + layer,
                        button: {
                            text: 'Add to Layer'
                        },
                        multiple: true
                    });

                    frame.on('select', function() {
                        var attachments = frame.state().get('selection').toJSON();

                        // Remove placeholder
                        dropZone.find('.mgwpp-drop-placeholder').remove();

                        // Get or create container
                        var container = dropZone.find('.mgwpp-layer-images');
                        if (!container.length) {
                            container = $('<div class="mgwpp-layer-images"></div>');
                            dropZone.append(container);
                        }

                        attachments.forEach(function(att) {
                            if (container.find('[data-id="' + att.id + '"]').length) return;

                            var thumb = att.sizes && att.sizes.thumbnail ?
                                att.sizes.thumbnail.url :
                                att.url;

                            var html = '<div class="mgwpp-layer-image" data-id="' + att.id + '">' +
                                '<img src="' + thumb + '" alt="">' +
                                '<button type="button" class="mgwpp-remove-img" title="Remove">' +
                                '<span class="dashicons dashicons-no-alt"></span></button></div>';
                            container.append(html);
                        });

                        updateLayerInput(layer);
                    });

                    frame.open();
                });

                // Update hidden input with layer images
                function updateLayerInput(layer) {
                    var ids = [];
                    $('.mgwpp-drop-zone[data-layer="' + layer + '"] .mgwpp-layer-image').each(function() {
                        ids.push($(this).data('id'));
                    });
                    $('.mgwpp-layer-panel[data-layer="' + layer + '"] .layer-images-input').val(ids.join(','));
                }
            });
        </script>
<?php
    }

    /**
     * Save marquee settings
     *
     * @param int $post_id Post ID
     */
    public static function save_settings($post_id)
    {
        // Verify nonce
        if (!isset($_POST['mgwpp_marquee_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['mgwpp_marquee_nonce']));
        if (!wp_verify_nonce($nonce, 'mgwpp_marquee_settings_save')) {
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

        // Save layer settings - properly unslash and sanitize
        if (isset($_POST['mgwpp_marquee']) && is_array($_POST['mgwpp_marquee'])) {
            // Unslash the array first, then pass to sanitization function
            $raw_settings = map_deep(wp_unslash($_POST['mgwpp_marquee']), 'sanitize_text_field');
            $settings = self::sanitize_settings($raw_settings);
            update_post_meta($post_id, '_mgwpp_marquee_settings', $settings);
        }

        // Save layer images - properly sanitize array data
        if (isset($_POST['mgwpp_layer_images']) && is_array($_POST['mgwpp_layer_images'])) {
            $layer_images = [];
            // Properly unslash and sanitize the array
            $layer_data = map_deep(wp_unslash($_POST['mgwpp_layer_images']), 'sanitize_text_field');
            foreach ($layer_data as $layer => $ids) {
                $layer = absint($layer);
                if ($layer >= 1 && $layer <= 3) {
                    // IDs string is already sanitized by map_deep
                    $layer_images[$layer] = array_filter(array_map('absint', explode(',', $ids)));
                }
            }
            update_post_meta($post_id, '_mgwpp_marquee_layer_images', $layer_images);
        }
    }

    /**
     * Sanitize marquee settings
     *
     * @param array $raw_settings Raw settings from POST
     * @return array Sanitized settings
     */
    private static function sanitize_settings($raw_settings)
    {
        $sanitized = [];

        // Global settings with defaults
        $sanitized['gap'] = 20;
        $sanitized['image_height'] = 250;
        $sanitized['border_radius'] = 12;
        $sanitized['overlay_enabled'] = true;
        $sanitized['overlay_color'] = 'rgba(0,0,0,0.4)';

        // Layer settings
        for ($layer = 1; $layer <= 3; $layer++) {
            $layer_key = 'layer_' . $layer;
            if (isset($raw_settings[$layer_key]) && is_array($raw_settings[$layer_key])) {
                $layer_raw = $raw_settings[$layer_key];
                $sanitized[$layer_key] = [
                    'direction' => in_array($layer_raw['direction'] ?? 'left', ['left', 'right'], true)
                        ? $layer_raw['direction'] : 'left',
                    'speed' => isset($layer_raw['speed']) ? max(5, min(120, absint($layer_raw['speed']))) : 30,
                    'pause_on_hover' => !empty($layer_raw['pause_on_hover']),
                ];
            } else {
                // Default layer settings
                $sanitized[$layer_key] = [
                    'direction' => $layer % 2 === 0 ? 'right' : 'left',
                    'speed' => 30,
                    'pause_on_hover' => true,
                ];
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public static function enqueue_admin_assets($hook)
    {
        global $post_type;

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        if ($post_type !== 'mgwpp_soora') {
            return;
        }

        wp_enqueue_media();

        // Toggle visibility based on gallery type
        wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                function toggleMarqueeEditor() {
                    var type = $('input[name=\"gallery_type\"]:checked').val();
                    if (type === 'marquee_gallery') {
                        $('#mgwpp_marquee_settings').show();
                    } else {
                        $('#mgwpp_marquee_settings').hide();
                    }
                }
                
                $('input[name=\"gallery_type\"]').on('change', toggleMarqueeEditor);
                toggleMarqueeEditor();
            });
        ");
    }

    /**
     * AJAX handler for saving layers
     */
    public static function ajax_save_layers()
    {
        check_ajax_referer('mgwpp_marquee_ajax', 'nonce');

        $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
        // Properly unslash and sanitize layers array
        $layers = isset($_POST['layers']) && is_array($_POST['layers']) ? map_deep(wp_unslash($_POST['layers']), 'absint') : [];

        if (!$gallery_id || !current_user_can('edit_post', $gallery_id)) {
            wp_send_json_error(['message' => __('Permission denied', 'mini-gallery')]);
        }

        $layer_images = [];
        foreach ($layers as $layer => $ids) {
            $layer = absint($layer);
            if ($layer >= 1 && $layer <= 3) {
                $layer_images[$layer] = array_map('absint', (array) $ids);
            }
        }

        update_post_meta($gallery_id, '_mgwpp_marquee_layer_images', $layer_images);

        wp_send_json_success(['message' => __('Layers saved', 'mini-gallery')]);
    }
}

// Initialize
MGWPP_Marquee_Admin::init();
