<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Spotlight_Carousel
{
    /**
     * Initialize the spotlight carousel
     */
    public static function init()
    {
        // Load admin settings on admin pages
        if (is_admin()) {
            require_once dirname(__FILE__) . '/admin/class-mgwpp-spotlight-carousel-admin.php';
        }
    }

    /**
     * Render the spotlight carousel
     *
     * @param int   $post_id  The gallery post ID
     * @param array $images   Array of image IDs
     * @param array $settings Gallery settings
     * @return string HTML output
     */
    public static function render($post_id, $images, $settings = [])
    {
        if (empty($images) || ! is_array($images)) {
            return '<div class="mgwpp-error">' . esc_html__('No images found in gallery', 'mini-gallery') . '</div>';
        }

        // Get spotlight-specific settings
        $spotlight_settings = get_post_meta($post_id, '_mgwpp_spotlight_settings', true) ?: [];
        $cta_links = get_post_meta($post_id, '_mgwpp_cta_links', true) ?: [];
        $image_links = get_post_meta($post_id, '_mgwpp_image_links', true) ?: [];

        // Settings with defaults
        $primary_color = $spotlight_settings['primary_color'] ?? '#07babe';
        $secondary_color = $spotlight_settings['secondary_color'] ?? '#05a0a8';
        $default_button_text = $spotlight_settings['default_button_text'] ?? __('Discover More', 'mini-gallery');
        $default_button_link = $cta_links['primary'] ?? '';
        $show_arrows = ($spotlight_settings['show_arrows'] ?? '1') === '1';
        $show_nav_dots = ($spotlight_settings['show_nav_dots'] ?? '1') === '1';
        $auto_play = ($spotlight_settings['auto_play'] ?? '1') === '1';
        $slide_duration = absint($spotlight_settings['slide_duration'] ?? 8000);

        // Generate inline styles for custom colors
        $custom_styles = sprintf(
            '--spotlight-primary: %s; --spotlight-secondary: %s;',
            esc_attr($primary_color),
            esc_attr($secondary_color)
        );

        ob_start();
?>
        <div class="mgwpp-spotlight-carousel"
            data-gallery-id="<?php echo esc_attr($post_id); ?>"
            data-autoplay="<?php echo $auto_play ? 'true' : 'false'; ?>"
            data-duration="<?php echo esc_attr($slide_duration); ?>"
            style="<?php echo esc_attr($custom_styles); ?>">

            <div class="mgwpp-light-overlay"></div>

            <div class="mgwpp-carousel-viewport">
                <?php foreach ($images as $index => $image) :
                    $image_id   = is_object($image) ? $image->ID : $image;
                    $image_data = wp_get_attachment_image_src($image_id, 'large');
                    $image_url  = esc_url($image_data[0]);
                    $image_alt  = esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true));

                    // Get per-slide settings from attachment meta
                    $slide_button_text = get_post_meta($image_id, '_mgwpp_slide_button_text', true);
                    $slide_button_link = get_post_meta($image_id, '_mgwpp_slide_button_link', true);

                    // Fallback to image links if no slide-specific link
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
                    <div class="mgwpp-carousel-slide <?php echo ($index === 0 ? 'mgwpp-active' : ''); ?>">
                        <div class="mgwpp-slide-content">
                            <div class="mgwpp-text-content">
                                <?php if (get_the_title($image_id)) : ?>
                                    <h1 class="mgwpp-slide-title"><?php echo esc_html(get_the_title($image_id)); ?></h1>
                                <?php endif; ?>
                                <?php if (get_post_field('post_content', $image_id)) : ?>
                                    <p class="mgwpp-slide-subtitle"><?php echo wp_kses_post(get_post_field('post_content', $image_id)); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($button_link)) : ?>
                                    <a href="<?php echo esc_url($button_link); ?>"
                                        class="mgwpp-cta-button"
                                        <?php echo $new_tab ? 'target="_blank"' : ''; ?>
                                        <?php echo $nofollow ? 'rel="nofollow noopener"' : ($new_tab ? 'rel="noopener"' : ''); ?>>
                                        <?php echo esc_html($button_text); ?>
                                    </a>
                                <?php else : ?>
                                    <button class="mgwpp-cta-button" type="button">
                                        <?php echo esc_html($button_text); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="mgwpp-image-container">
                                <?php echo wp_get_attachment_image($image_id, 'large', false, [
                                    'class'   => 'mgwpp-carousel-image',
                                    'loading' => 'lazy',
                                    'alt'     => $image_alt,
                                ]); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($show_arrows) : ?>
                <!-- Navigation arrows -->
                <button class="mgwpp-carousel-arrow mgwpp-arrow-prev" aria-label="<?php esc_attr_e('Previous slide', 'mini-gallery'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="mgwpp-carousel-arrow mgwpp-arrow-next" aria-label="<?php esc_attr_e('Next slide', 'mini-gallery'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            <?php endif; ?>

            <?php if ($show_nav_dots) : ?>
                <div class="mgwpp-carousel-nav">
                    <?php foreach ($images as $index => $image) : ?>
                        <button class="mgwpp-nav-btn <?php echo ($index === 0 ? 'mgwpp-active' : ''); ?>"
                            data-index="<?php echo esc_attr($index); ?>"
                            aria-label="<?php
                                        /* translators: %d: slide number */
                                        printf(esc_attr__('Go to slide %d', 'mini-gallery'), absint($index + 1));
                                        ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .mgwpp-spotlight-carousel .mgwpp-cta-button {
                background: linear-gradient(135deg, var(--spotlight-primary, #07babe) 0%, var(--spotlight-secondary, #05a0a8) 100%);
                box-shadow: 0 4px 15px <?php echo esc_attr($primary_color); ?>4d, 0 0 0 0 <?php echo esc_attr($primary_color); ?>66;
            }

            .mgwpp-spotlight-carousel .mgwpp-cta-button:hover {
                box-shadow: 0 8px 25px <?php echo esc_attr($primary_color); ?>66, 0 0 0 4px <?php echo esc_attr($primary_color); ?>33;
            }

            .mgwpp-spotlight-carousel .mgwpp-nav-btn:hover {
                background: <?php echo esc_attr($primary_color); ?>80;
            }

            .mgwpp-spotlight-carousel .mgwpp-nav-btn.mgwpp-active {
                background: <?php echo esc_attr($primary_color); ?>;
                box-shadow: 0 0 15px <?php echo esc_attr($primary_color); ?>99;
            }

            .mgwpp-spotlight-carousel .mgwpp-carousel-arrow:hover {
                background: <?php echo esc_attr($primary_color); ?>33;
                border-color: <?php echo esc_attr($primary_color); ?>80;
                box-shadow: 0 0 20px <?php echo esc_attr($primary_color); ?>4d;
            }

            .mgwpp-spotlight-carousel .mgwpp-slide-title {
                background: linear-gradient(135deg, #ffffff 0%, var(--spotlight-primary, #07babe) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .mgwpp-spotlight-carousel .mgwpp-light-overlay {
                background: radial-gradient(circle at var(--mgwpp-x, 50%) var(--mgwpp-y, 50%),
                        <?php echo esc_attr($primary_color); ?>26 0%,
                        <?php echo esc_attr($primary_color); ?>0d 30%,
                        transparent 60%);
            }
        </style>
<?php
        return ob_get_clean();
    }
}

// Initialize the class
MGWPP_Spotlight_Carousel::init();
