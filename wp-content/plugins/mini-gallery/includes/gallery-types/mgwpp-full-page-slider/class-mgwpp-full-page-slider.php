<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Full_Page_Slider
{
    /**
     * Initialize the full page slider
     */
    public static function init()
    {
        // Load admin settings on admin pages
        if (is_admin()) {
            require_once dirname(__FILE__) . '/admin/class-mgwpp-full-page-slider-admin.php';
        }
    }

    /**
     * Render the full page slider
     *
     * @param int   $post_id  The gallery post ID
     * @param array $images   Array of image objects/IDs
     * @param array $settings Gallery settings
     * @return string HTML output
     */
    public static function render($post_id, $images, $settings = [])
    {
        if (empty($images) || ! is_array($images)) {
            return '';
        }

        // Get fullpage-specific settings
        $fullpage_settings = get_post_meta($post_id, '_mgwpp_fullpage_settings', true) ?: [];
        $cta_links = get_post_meta($post_id, '_mgwpp_cta_links', true) ?: [];
        $image_links = get_post_meta($post_id, '_mgwpp_image_links', true) ?: [];

        // Settings with defaults
        $primary_color = $fullpage_settings['primary_color'] ?? '#07babe';
        $overlay_color = $fullpage_settings['overlay_color'] ?? '#000000';
        $overlay_opacity = ($fullpage_settings['overlay_opacity'] ?? 40) / 100;
        $default_button_text = $fullpage_settings['default_button_text'] ?? __('Explore Collection', 'mini-gallery');
        $default_button_link = $cta_links['primary'] ?? '';
        $show_arrows = ($fullpage_settings['show_arrows'] ?? '1') === '1';
        $auto_play = ($fullpage_settings['auto_play'] ?? '1') === '1';
        $slide_duration = absint($fullpage_settings['slide_duration'] ?? 6000);
        $transition_effect = $fullpage_settings['transition_effect'] ?? 'fade';

        // Convert overlay color to rgba
        $overlay_rgb = self::hex_to_rgb($overlay_color);
        $overlay_rgba = "rgba({$overlay_rgb['r']}, {$overlay_rgb['g']}, {$overlay_rgb['b']}, {$overlay_opacity})";

        ob_start(); ?>
        <div class="mg-fullpage-slider"
            data-gallery-id="<?php echo esc_attr($post_id); ?>"
            data-autoplay="<?php echo $auto_play ? 'true' : 'false'; ?>"
            data-duration="<?php echo esc_attr($slide_duration); ?>"
            data-transition="<?php echo esc_attr($transition_effect); ?>"
            style="--fullpage-primary: <?php echo esc_attr($primary_color); ?>; --fullpage-overlay: <?php echo esc_attr($overlay_rgba); ?>;">
            <div class="mg-fullpage-viewport">
                <?php foreach ($images as $index => $image) :
                    $image_id = is_object($image) && isset($image->ID) ? intval($image->ID) : intval($image);
                    if (!$image_id) continue;

                    $image_url = wp_get_attachment_url($image_id);
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    $image_title = get_the_title($image_id);
                    $image_content = get_post_field('post_content', $image_id);

                    // Get per-slide button settings
                    $slide_button_text = get_post_meta($image_id, '_mgwpp_slide_button_text', true);
                    $slide_button_link = get_post_meta($image_id, '_mgwpp_slide_button_link', true);

                    // Fallback to image links
                    if (empty($slide_button_link) && isset($image_links[$image_id])) {
                        $slide_button_link = $image_links[$image_id];
                    }

                    // Use defaults if not set
                    $button_text = !empty($slide_button_text) ? $slide_button_text : $default_button_text;
                    $button_link = !empty($slide_button_link) ? $slide_button_link : $default_button_link;

                    // Get link attributes
                    $new_tab = isset($image_links[$image_id . '_new_tab']) && $image_links[$image_id . '_new_tab'];
                    $nofollow = isset($image_links[$image_id . '_nofollow']) && $image_links[$image_id . '_nofollow'];
                ?>
                    <div class="mg-fullpage-slide <?php echo ($index === 0 ? 'mg-active' : ''); ?>" data-transition="<?php echo esc_attr($transition_effect); ?>">
                        <div class="mg-fullpage-overlay"></div>
                        <?php if ($image_url) : ?>
                            <img class="mg-fullpage-image"
                                src="<?php echo esc_url($image_url); ?>"
                                alt="<?php echo esc_attr($image_alt); ?>"
                                loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                        <?php endif; ?>
                        <div class="mg-fullpage-content">
                            <?php if ($image_title) : ?>
                                <h1 class="mg-fullpage-title"><?php echo esc_html($image_title); ?></h1>
                            <?php endif; ?>
                            <?php if ($image_content) : ?>
                                <p class="mg-fullpage-description"><?php echo wp_kses_post($image_content); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($button_link)) : ?>
                                <a href="<?php echo esc_url($button_link); ?>"
                                    class="mg-fullpage-buy"
                                    <?php echo $new_tab ? 'target="_blank"' : ''; ?>
                                    <?php echo $nofollow ? 'rel="nofollow noopener"' : ($new_tab ? 'rel="noopener"' : ''); ?>>
                                    <?php echo esc_html($button_text); ?>
                                </a>
                            <?php else : ?>
                                <button class="mg-fullpage-buy" type="button">
                                    <?php echo esc_html($button_text); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($show_arrows) : ?>
                <button class="mg-fullpage-nav mg-fullpage-slider-prev" aria-label="<?php esc_attr_e('Previous slide', 'mini-gallery'); ?>">❮</button>
                <button class="mg-fullpage-nav mg-fullpage-slider-next" aria-label="<?php esc_attr_e('Next slide', 'mini-gallery'); ?>">❯</button>
            <?php endif; ?>
        </div>

        <style>
            .mg-fullpage-slider .mg-fullpage-overlay {
                background: var(--fullpage-overlay, rgba(0, 0, 0, 0.4));
            }

            .mg-fullpage-slider .mg-fullpage-buy {
                background: var(--fullpage-primary, #07babe);
                border-color: var(--fullpage-primary, #07babe);
            }

            .mg-fullpage-slider .mg-fullpage-buy:hover {
                background: transparent;
                color: var(--fullpage-primary, #07babe);
                border-color: var(--fullpage-primary, #07babe);
            }

            .mg-fullpage-slider .mg-fullpage-nav:hover {
                background: var(--fullpage-primary, #07babe);
            }
        </style>
<?php
        return ob_get_clean();
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex Hex color code
     * @return array RGB values
     */
    private static function hex_to_rgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}

// Initialize the class
MGWPP_Full_Page_Slider::init();
