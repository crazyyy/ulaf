<?php

/**
 * Mini Gallery - 3D Horizontal Marquee
 * 
 * A horizontal scrolling marquee with 3D perspective tilt effect.
 * Features:
 * - 3D Perspective Container
 * - Tilted Scrolling Tracks
 * - Multi-row support
 * - Gradient Overlays
 * - Hover interactions
 *
 * @package Mini_Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MGWPP_3D_Horizontal_Marquee
 */
class MGWPP_3D_Horizontal_Marquee
{

    /**
     * Initialize the gallery type
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_assets']);
    }

    /**
     * Conditionally enqueue assets
     */
    public static function maybe_enqueue_assets()
    {
        global $post;

        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'mgwpp')) {
            self::enqueue_assets();
        }
    }

    /**
     * Enqueue CSS and JS
     */
    public static function enqueue_assets()
    {
        $base_url = plugin_dir_url(__FILE__);

        wp_enqueue_style(
            'mgwpp-3d-h-marquee-css',
            $base_url . 'mgwpp-3d-h-marquee.css',
            [],
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-3d-h-marquee-js',
            $base_url . 'mgwpp-3d-h-marquee.js',
            [],
            MGWPP_ASSET_VERSION,
            true
        );
    }

    /**
     * Render the 3D Horizontal Marquee
     *
     * @param array $images Array of image IDs or WP_Post objects
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
            'rows'             => 3,
            'speed'            => 30,
            'image_gap'        => 20,
            'image_radius'     => 12,
            'pause_on_hover'   => true,
            'height'           => '600px',
            'perspective'      => 1000,
            'rotate_x'         => 20, // Tilt angle
            'rotate_y'         => -20, // Tilt angle
            'scale'            => 1.1,
            'grayscale'        => false,
            'direction'        => 'alternate', // 'left', 'right', 'alternate'
        ];

        $settings = wp_parse_args($settings, $defaults);

        // Split images into rows
        $num_rows = max(1, min(5, intval($settings['rows'])));
        $rows = array_fill(0, $num_rows, []);

        $img_index = 0;
        foreach ($images as $image_id) {
            $rows[$img_index % $num_rows][] = $image_id;
            $img_index++;
        }

        // Unique ID
        $unique_id = 'mgwpp-3d-h-marquee-' . ($gallery_id ?: wp_rand(1000, 9999));

        ob_start();
?>
        <div id="<?php echo esc_attr($unique_id); ?>"
            class="mgwpp-3d-h-marquee-wrapper"
            style="--marquee-perspective: <?php echo esc_attr($settings['perspective']); ?>px;
                   --marquee-rotate-x: <?php echo esc_attr($settings['rotate_x']); ?>deg;
                   --marquee-rotate-y: <?php echo esc_attr($settings['rotate_y']); ?>deg;
                   --marquee-scale: <?php echo esc_attr($settings['scale']); ?>;">

            <div class="mgwpp-3d-h-marquee-content"
                data-pause-hover="<?php echo esc_attr($settings['pause_on_hover'] ? 'true' : 'false'); ?>"
                style="height: <?php echo esc_attr($settings['height']); ?>;
                        --marquee-gap: <?php echo esc_attr($settings['image_gap']); ?>px;
                        --marquee-radius: <?php echo esc_attr($settings['image_radius']); ?>px;">

                <!-- Gradient Fades -->
                <div class="mgwpp-3d-h-fade-left"></div>
                <div class="mgwpp-3d-h-fade-right"></div>

                <div class="mgwpp-3d-h-track-container">
                    <?php foreach ($rows as $row_index => $row_images) :
                        if (empty($row_images)) continue;

                        $row_direction = 'left';
                        if ($settings['direction'] === 'alternate') {
                            $row_direction = ($row_index % 2 === 0) ? 'left' : 'right';
                        } else {
                            $row_direction = $settings['direction'];
                        }

                        // Vary speed slightly for natural feel
                        $row_speed = $settings['speed'] + ($row_index * 5);
                    ?>
                        <div class="mgwpp-3d-h-row"
                            data-direction="<?php echo esc_attr($row_direction); ?>"
                            data-speed="<?php echo esc_attr($row_speed); ?>"
                            style="--row-duration: <?php echo esc_attr(1000 / $row_speed); ?>s;">

                            <div class="mgwpp-3d-h-track">
                                <?php
                                // Duplicate images for seamless loop (triple buffer)
                                $loop_images = array_merge($row_images, $row_images, $row_images, $row_images);
                                foreach ($loop_images as $img_id) :
                                    $img_url = wp_get_attachment_image_url($img_id, 'large');
                                    $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                                    if (!$img_url) continue;
                                ?>
                                    <div class="mgwpp-3d-h-item <?php echo $settings['grayscale'] ? 'mgwpp-grayscale' : ''; ?>">
                                        <img src="<?php echo esc_url($img_url); ?>"
                                            alt="<?php echo esc_attr($img_alt); ?>"
                                            loading="lazy" />
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}

MGWPP_3D_Horizontal_Marquee::init();
