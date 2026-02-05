<?php
/**
 * Marquee Gallery - 3-Layer Scrolling Image Gallery
 * 
 * Creates a portfolio-style display with 3 horizontal marquee layers,
 * each scrolling in configurable directions and speeds.
 *
 * @package MiniGallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Marquee_Gallery
{
    /**
     * Default settings for marquee layers
     */
    private static $default_settings = [
        'layer_1' => [
            'direction' => 'left',
            'speed' => 30,
            'pause_on_hover' => true,
        ],
        'layer_2' => [
            'direction' => 'right',
            'speed' => 25,
            'pause_on_hover' => true,
        ],
        'layer_3' => [
            'direction' => 'left',
            'speed' => 35,
            'pause_on_hover' => true,
        ],
        'gap' => 20,
        'image_height' => 250,
        'border_radius' => 12,
        'overlay_enabled' => true,
        'overlay_color' => 'rgba(0,0,0,0.4)',
    ];

    /**
     * Render the marquee gallery
     *
     * @param int   $post_id Gallery post ID
     * @param array $images  Array of image attachment objects
     * @return string HTML output
     */
    public static function render($post_id, $images)
    {
        if (empty($images)) {
            return '<p class="mgwpp-no-images">' . esc_html__('No images found for this marquee gallery.', 'mini-gallery') . '</p>';
        }

        // Get settings from post meta
        $saved_settings = get_post_meta($post_id, '_mgwpp_marquee_settings', true);
        $settings = wp_parse_args($saved_settings ?: [], self::$default_settings);
        
        // Get layer-specific images if configured, otherwise distribute evenly
        $layer_images = self::get_layer_images($post_id, $images);

        // Enqueue assets
        self::enqueue_assets();

        ob_start();
        ?>

        <div class="mgwpp-marquee-gallery" 
             id="mgwpp-marquee-<?php echo esc_attr($post_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode($settings)); ?>">
            
            <?php for ($layer = 1; $layer <= 3; $layer++) : 
                $layer_key = 'layer_' . $layer;
                $layer_config = isset($settings[$layer_key]) ? $settings[$layer_key] : self::$default_settings[$layer_key];
                $current_images = isset($layer_images[$layer]) ? $layer_images[$layer] : [];
                
                if (empty($current_images)) continue;
                
                $direction = isset($layer_config['direction']) ? $layer_config['direction'] : 'left';
                $speed = isset($layer_config['speed']) ? intval($layer_config['speed']) : 30;
                $pause = isset($layer_config['pause_on_hover']) && $layer_config['pause_on_hover'];
            ?>
            
            <div class="mgwpp-marquee-layer mgwpp-marquee-layer-<?php echo esc_attr($layer); ?>"
                 data-layer="<?php echo esc_attr($layer); ?>"
                 data-direction="<?php echo esc_attr($direction); ?>"
                 data-speed="<?php echo esc_attr($speed); ?>"
                 data-pause="<?php echo $pause ? 'true' : 'false'; ?>"
                 style="--marquee-speed: <?php echo esc_attr($speed); ?>s; --marquee-gap: <?php echo esc_attr($settings['gap']); ?>px; --image-height: <?php echo esc_attr($settings['image_height']); ?>px; --border-radius: <?php echo esc_attr($settings['border_radius']); ?>px;">
                
                <div class="mgwpp-marquee-track mgwpp-marquee-<?php echo esc_attr($direction); ?> <?php echo $pause ? 'mgwpp-pause-on-hover' : ''; ?>">
                    
                    <!-- First set of images -->
                    <div class="mgwpp-marquee-content">
                        <?php foreach ($current_images as $image) : 
                            $img_url = wp_get_attachment_image_url($image->ID, 'large');
                            $img_alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true) ?: $image->post_title;
                            $caption = wp_get_attachment_caption($image->ID);
                        ?>
                        <div class="mgwpp-marquee-item" data-image-id="<?php echo esc_attr($image->ID); ?>">
                            <div class="mgwpp-marquee-image-wrapper">
                                <img src="<?php echo esc_url($img_url); ?>" 
                                     alt="<?php echo esc_attr($img_alt); ?>"
                                     loading="lazy"
                                     class="mgwpp-marquee-image" />
                                
                                <?php if ($settings['overlay_enabled']) : ?>
                                <div class="mgwpp-marquee-overlay" style="background: <?php echo esc_attr($settings['overlay_color']); ?>;">
                                    <?php if ($caption) : ?>
                                    <span class="mgwpp-marquee-caption"><?php echo esc_html($caption); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Duplicate for seamless loop -->
                    <div class="mgwpp-marquee-content" aria-hidden="true">
                        <?php foreach ($current_images as $image) : 
                            $img_url = wp_get_attachment_image_url($image->ID, 'large');
                            $img_alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true) ?: $image->post_title;
                            $caption = wp_get_attachment_caption($image->ID);
                        ?>
                        <div class="mgwpp-marquee-item">
                            <div class="mgwpp-marquee-image-wrapper">
                                <img src="<?php echo esc_url($img_url); ?>" 
                                     alt="<?php echo esc_attr($img_alt); ?>"
                                     loading="lazy"
                                     class="mgwpp-marquee-image" />
                                
                                <?php if ($settings['overlay_enabled']) : ?>
                                <div class="mgwpp-marquee-overlay" style="background: <?php echo esc_attr($settings['overlay_color']); ?>;">
                                    <?php if ($caption) : ?>
                                    <span class="mgwpp-marquee-caption"><?php echo esc_html($caption); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                </div>
            </div>
            
            <?php endfor; ?>
            
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Get images distributed across layers
     *
     * @param int   $post_id Gallery post ID
     * @param array $images  All gallery images
     * @return array Images organized by layer
     */
    private static function get_layer_images($post_id, $images)
    {
        // Check if specific layer assignments exist
        $layer_assignments = get_post_meta($post_id, '_mgwpp_marquee_layer_images', true);
        
        if (!empty($layer_assignments) && is_array($layer_assignments)) {
            $result = [];
            foreach ($layer_assignments as $layer => $image_ids) {
                $result[$layer] = [];
                foreach ($image_ids as $image_id) {
                    foreach ($images as $image) {
                        if ($image->ID === intval($image_id)) {
                            $result[$layer][] = $image;
                            break;
                        }
                    }
                }
            }
            return $result;
        }
        
        // Default: distribute images evenly across 3 layers
        $total = count($images);
        $per_layer = max(1, ceil($total / 3));
        
        return [
            1 => array_slice($images, 0, $per_layer),
            2 => array_slice($images, $per_layer, $per_layer),
            3 => array_slice($images, $per_layer * 2, $per_layer),
        ];
    }

    /**
     * Enqueue marquee gallery assets
     */
    private static function enqueue_assets()
    {
        $base_url = MG_PLUGIN_URL . '/includes/gallery-types/mgwpp-marquee-gallery/';
        $version = defined('MG_VERSION') ? MG_VERSION : '1.6';

        wp_enqueue_style(
            'mgwpp-marquee-gallery',
            $base_url . 'mgwpp-marquee-gallery.css',
            [],
            $version
        );

        wp_enqueue_script(
            'mgwpp-marquee-gallery',
            $base_url . 'mgwpp-marquee-gallery.js',
            [],
            $version,
            true
        );
    }

    /**
     * Get default settings (for admin/canvas editor)
     *
     * @return array Default settings
     */
    public static function get_default_settings()
    {
        return self::$default_settings;
    }
}
