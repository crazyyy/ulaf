<?php

/**
 * Mini Gallery - 3D Masonry Gallery Type
 * 
 * A stunning 3D masonry gallery with multiple view modes:
 * - WALL: Vertical 3D wall perspective
 * - TABLE: Table/floor view with perspective
 * - TUNNEL: Immersive tunnel 3D effect
 * - FLAT: Traditional flat masonry layout
 *
 * Features:
 * - Infinite vertical scrolling
 * - Hover zoom effects
 * - Multiple 3D view modes
 * - GPU-accelerated animations
 * - Responsive design
 *
 * @package Mini_Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MGWPP_3D_Masonry_Gallery
 */
class MGWPP_3D_Masonry_Gallery
{

    /**
     * Initialize the gallery type
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_assets']);

        if (is_admin()) {
            require_once dirname(__FILE__) . '/admin/class-mgwpp-3d-masonry-admin.php';
        }
    }

    /**
     * Conditionally enqueue assets when gallery is displayed
     */
    public static function maybe_enqueue_assets()
    {
        global $post;

        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'mgwpp')) {
            self::enqueue_assets();
        }
    }

    /**
     * Enqueue CSS and JS assets
     */
    public static function enqueue_assets()
    {
        $base_url = plugin_dir_url(__FILE__);

        wp_enqueue_style(
            'mgwpp-3d-masonry-css',
            $base_url . 'mgwpp-3d-masonry.css',
            [],
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-3d-masonry-js',
            $base_url . 'mgwpp-3d-masonry.js',
            [],
            MGWPP_ASSET_VERSION,
            true
        );
    }

    /**
     * Render the 3D masonry gallery
     *
     * @param array $images Array of image IDs
     * @param array $settings Gallery settings
     * @param int   $gallery_id Gallery post ID
     * @return string HTML output
     */
    public static function render($images, $settings = [], $gallery_id = 0)
    {
        if (empty($images)) {
            return '<p class="mgwpp-no-images">' . esc_html__('No images found for this gallery.', 'mini-gallery') . '</p>';
        }

        // Ensure we have IDs (handle WP_Post objects if passed)
        $images = array_map(function ($img) {
            return is_object($img) && isset($img->ID) ? $img->ID : $img;
        }, $images);

        self::enqueue_assets();

        // Default settings
        $defaults = [
            'columns'          => 4,
            'speed'            => 40,
            'image_gap'        => 12,
            'image_radius'     => 8,
            'pause_on_hover'   => true,
            'height'           => '850px',
            'default_mode'     => $gallery_id ? (get_post_meta($gallery_id, 'mgwpp_3d_mode', true) ?: 'wall') : 'wall',
            'show_mode_switch' => $gallery_id ? (get_post_meta($gallery_id, 'mgwpp_3d_show_tabs', true) === '1') : false,
            'hover_scale'      => true,
            'grayscale'        => false,
            'fade_edges'       => true,
        ];

        $settings = wp_parse_args($settings, $defaults);

        // Distribute images across columns (masonry style)
        $num_columns = max(2, min(6, intval($settings['columns'])));
        $columns = array_fill(0, $num_columns, []);

        // Masonry distribution - balance column heights
        $column_heights = array_fill(0, $num_columns, 0);

        foreach ($images as $image_id) {
            // Find shortest column
            $min_height_col = array_search(min($column_heights), $column_heights);
            $columns[$min_height_col][] = $image_id;

            // Estimate height (random for visual variety)
            $column_heights[$min_height_col] += wp_rand(100, 250);
        }

        // Build unique ID
        $unique_id = 'mgwpp-3d-masonry-' . ($gallery_id ?: wp_rand(1000, 9999));

        ob_start();
?>
        <div id="<?php echo esc_attr($unique_id); ?>"
            class="mgwpp-3d-masonry-gallery"
            data-speed="<?php echo esc_attr($settings['speed']); ?>"
            data-pause-hover="<?php echo esc_attr($settings['pause_on_hover'] ? 'true' : 'false'); ?>"
            data-mode="<?php echo esc_attr($settings['default_mode']); ?>"
            data-grayscale="<?php echo esc_attr($settings['grayscale'] ? 'true' : 'false'); ?>"
            style="height: <?php echo esc_attr($settings['height']); ?>; 
                    --masonry-gap: <?php echo esc_attr($settings['image_gap']); ?>px; 
                    --masonry-radius: <?php echo esc_attr($settings['image_radius']); ?>px;
                    --masonry-columns: <?php echo esc_attr($num_columns); ?>;">

            <?php if ($settings['show_mode_switch']) : ?>
                <!-- 3D Mode Switcher -->
                <div class="mgwpp-3d-mode-switcher">
                    <button type="button" class="mgwpp-mode-btn <?php echo $settings['default_mode'] === 'wall' ? 'active' : ''; ?>" data-mode="wall">
                        WALL
                    </button>
                    <button type="button" class="mgwpp-mode-btn <?php echo $settings['default_mode'] === 'table' ? 'active' : ''; ?>" data-mode="table">
                        TABLE
                    </button>
                    <button type="button" class="mgwpp-mode-btn <?php echo $settings['default_mode'] === 'tunnel' ? 'active' : ''; ?>" data-mode="tunnel">
                        TUNNEL
                    </button>
                    <button type="button" class="mgwpp-mode-btn <?php echo $settings['default_mode'] === 'flat' ? 'active' : ''; ?>" data-mode="flat">
                        FLAT
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($settings['fade_edges']) : ?>
                <!-- Gradient Edge Fades -->
                <div class="mgwpp-3d-fade mgwpp-3d-fade-top"></div>
                <div class="mgwpp-3d-fade mgwpp-3d-fade-bottom"></div>
                <div class="mgwpp-3d-fade mgwpp-3d-fade-left"></div>
                <div class="mgwpp-3d-fade mgwpp-3d-fade-right"></div>
            <?php endif; ?>

            <!-- 3D Stage Container -->
            <div class="mgwpp-3d-stage">
                <div class="mgwpp-3d-perspective-wrap">
                    <div class="mgwpp-masonry-columns">
                        <?php foreach ($columns as $col_index => $col_images) :
                            if (empty($col_images)) continue;

                            // Alternate scroll directions
                            $direction = ($col_index % 2 === 0) ? 'up' : 'down';

                            // Stagger speeds
                            $col_speed = $settings['speed'] + ($col_index * 3);
                        ?>
                            <div class="mgwpp-masonry-column"
                                data-direction="<?php echo esc_attr($direction); ?>"
                                data-speed="<?php echo esc_attr($col_speed); ?>">
                                <div class="mgwpp-masonry-track">
                                    <?php
                                    // Duplicate for seamless infinite loop
                                    $all_images = array_merge($col_images, $col_images, $col_images);
                                    foreach ($all_images as $img_index => $image_id) :
                                        $image_url = wp_get_attachment_image_url($image_id, 'large');
                                        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                        $image_title = get_the_title($image_id);
                                        if (!$image_url) continue;

                                        // Assign random aspect ratios for masonry effect
                                        $aspect_classes = ['aspect-portrait', 'aspect-square', 'aspect-landscape'];
                                        $aspect_class = $aspect_classes[wp_rand(0, 2)];
                                    ?>
                                        <div class="mgwpp-masonry-item <?php echo esc_attr($aspect_class); ?>">
                                            <img src="<?php echo esc_url($image_url); ?>"
                                                alt="<?php echo esc_attr($image_alt ?: $image_title); ?>"
                                                loading="lazy"
                                                decoding="async" />
                                            <?php if ($settings['hover_scale']) : ?>
                                                <div class="mgwpp-item-overlay"></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Accessibility Pause Button -->
            <button type="button"
                class="mgwpp-3d-pause-btn"
                aria-label="<?php esc_attr_e('Pause animation', 'mini-gallery'); ?>">
                <svg class="mgwpp-icon-pause" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                <svg class="mgwpp-icon-play" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                    <polygon points="5,3 19,12 5,21"></polygon>
                </svg>
            </button>
        </div>
<?php
        return ob_get_clean();
    }
}

// Initialize
MGWPP_3D_Masonry_Gallery::init();
