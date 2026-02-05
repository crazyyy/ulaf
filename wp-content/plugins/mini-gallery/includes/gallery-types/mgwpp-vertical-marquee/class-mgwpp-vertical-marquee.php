<?php

/**
 * Mini Gallery - Vertical Marquee Gallery Type
 * 
 * A smooth vertical scrolling gallery with infinite loop animation
 * Similar to the horizontal marquee but scrolls up/down in columns
 *
 * @package Mini_Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MGWPP_Vertical_Marquee
 */
class MGWPP_Vertical_Marquee
{

    /**
     * Initialize the gallery type
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_assets']);
    }

    /**
     * Conditionally enqueue assets when gallery is displayed
     */
    public static function maybe_enqueue_assets()
    {
        global $post;

        // Check if shortcode is in content
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
            'mgwpp-vertical-marquee-css',
            $base_url . 'mgwpp-vertical-marquee.css',
            [],
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-vertical-marquee-js',
            $base_url . 'mgwpp-vertical-marquee.js',
            [],
            MGWPP_ASSET_VERSION,
            true
        );
    }

    /**
     * Render the vertical marquee gallery
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
            'columns'        => 3,
            'speed'          => 30,
            'image_gap'      => 15,
            'image_radius'   => 8,
            'pause_on_hover' => true,
            'height'         => '600px',
            'direction'      => 'up', // 'up' or 'down'
            'stagger'        => true, // Stagger column speeds
        ];

        $settings = wp_parse_args($settings, $defaults);

        // Distribute images across columns
        $num_columns = max(1, min(6, intval($settings['columns'])));
        $columns = array_fill(0, $num_columns, []);

        foreach ($images as $index => $image_id) {
            $col_index = $index % $num_columns;
            $columns[$col_index][] = $image_id;
        }

        // Build unique ID
        $unique_id = 'mgwpp-vmarquee-' . ($gallery_id ?: wp_rand(1000, 9999));

        ob_start();
?>
        <div id="<?php echo esc_attr($unique_id); ?>"
            class="mgwpp-vertical-marquee-gallery"
            data-speed="<?php echo esc_attr($settings['speed']); ?>"
            data-pause-hover="<?php echo esc_attr($settings['pause_on_hover'] ? 'true' : 'false'); ?>"
            data-stagger="<?php echo esc_attr($settings['stagger'] ? 'true' : 'false'); ?>"
            style="height: <?php echo esc_attr($settings['height']); ?>; --vmarquee-gap: <?php echo esc_attr($settings['image_gap']); ?>px; --vmarquee-radius: <?php echo esc_attr($settings['image_radius']); ?>px;">

            <!-- Gradient Overlays -->
            <div class="mgwpp-vmarquee-fade-top"></div>
            <div class="mgwpp-vmarquee-fade-bottom"></div>

            <div class="mgwpp-vmarquee-columns" style="grid-template-columns: repeat(<?php echo esc_attr($num_columns); ?>, 1fr);">
                <?php foreach ($columns as $col_index => $col_images) :
                    if (empty($col_images)) continue;

                    // Determine direction with alternating pattern
                    $col_direction = $settings['direction'];
                    if ($settings['stagger'] && $col_index % 2 === 1) {
                        $col_direction = ($col_direction === 'up') ? 'down' : 'up';
                    }

                    // Stagger speeds slightly
                    $col_speed = $settings['speed'];
                    if ($settings['stagger']) {
                        $col_speed = $col_speed + ($col_index * 5);
                    }
                ?>
                    <div class="mgwpp-vmarquee-column"
                        data-direction="<?php echo esc_attr($col_direction); ?>"
                        data-speed="<?php echo esc_attr($col_speed); ?>">
                        <div class="mgwpp-vmarquee-track">
                            <?php
                            // Duplicate images for seamless loop
                            $all_images = array_merge($col_images, $col_images);
                            foreach ($all_images as $image_id) :
                                $image_url = wp_get_attachment_image_url($image_id, 'large');
                                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                                if (!$image_url) continue;
                            ?>
                                <div class="mgwpp-vmarquee-item">
                                    <img src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        loading="lazy" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Accessibility: Pause/Play Button -->
            <button type="button"
                class="mgwpp-vmarquee-pause-btn"
                aria-label="<?php esc_attr_e('Pause animation', 'mini-gallery'); ?>">
                <span class="mgwpp-pause-icon">⏸</span>
                <span class="mgwpp-play-icon" style="display:none;">▶</span>
            </button>
        </div>
<?php
        return ob_get_clean();
    }
}

// Initialize
MGWPP_Vertical_Marquee::init();
