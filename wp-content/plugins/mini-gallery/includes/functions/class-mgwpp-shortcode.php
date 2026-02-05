<?php
if (! defined('ABSPATH')) {
    exit;
}
function mgwpp_gallery_shortcode($atts)
{
    $atts    = shortcode_atts(['id' => '', 'paged' => 1], $atts);
    $post_id = max(0, intval($atts['id']));
    $paged   = max(1, intval($atts['paged']));
    $output  = '';

    if ($post_id) {
        $gallery_type = get_post_meta($post_id, 'gallery_type', true) ?: 'single_carousel';
        $images_per_page = 6;
        $offset = ($paged - 1) * $images_per_page;

        // FIXED: Get images from gallery_images meta instead of attached media
        $image_ids = get_post_meta($post_id, 'gallery_images', true);

        // Handle different storage formats (string or array)
        if (is_string($image_ids) && !empty($image_ids)) {
            $image_ids = explode(',', $image_ids);
        }

        $all_images = [];
        if (!empty($image_ids)) {
            // Get full image objects
            $all_images = array_map('get_post', array_filter(array_map('absint', $image_ids)));
        }

        if ($all_images) {
            $gallery_html = '<p>Gallery type not recognized.</p>';

            switch ($gallery_type) {
                case 'single_carousel':
                    wp_enqueue_style('mg-single-carousel-styles');
                    wp_enqueue_script('mg-single-carousel-js');
                    if (!class_exists('MGWPP_Gallery_Single')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-single-gallery/class-mgwpp-single-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Single::render($post_id, $all_images);
                    break;

                case 'multi_carousel':
                    wp_enqueue_style('mg-multi-carousel-styles');
                    wp_enqueue_script('mg-multi-carousel-js');
                    if (!class_exists('MGWPP_Gallery_Multi')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/mgwpp-multi-gallery/class-mgwpp-multi-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Multi::render($post_id, $all_images, $paged, $images_per_page);
                    break;

                case 'grid':
                    wp_enqueue_style('mg-grid-styles');
                    if (!class_exists('MGWPP_Gallery_Grid')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-grid-gallery.php';
                    }
                    $gallery_html = MGWPP_Gallery_Grid::render($post_id, $all_images);
                    break;

                case 'mega_slider':
                    wp_enqueue_style('mg-mega-carousel-styles');
                    wp_enqueue_script('mg-mega-carousel-js');
                    if (!class_exists('MGWPP_Mega_Slider')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-mega-slider.php';
                    }
                    $gallery_html = MGWPP_Mega_Slider::render($post_id, $all_images);
                    break;

                case 'pro_carousel':
                    wp_enqueue_style('mgwpp-pro-carousel-styles');
                    wp_enqueue_script('mgwpp-pro-carousel-js');
                    $gallery_html = MGWPP_Pro_Carousel::render($post_id, $all_images);
                    break;

                case 'neon_carousel':
                    wp_enqueue_style('mgwpp-neon-carousel-styles');
                    wp_enqueue_script('mgwpp-neon-carousel-js');
                    $gallery_html = MGWPP_Neon_Carousel::render($post_id, $all_images);
                    break;

                case 'threed_carousel':
                    wp_enqueue_style('mgwpp-threed-carousel-styles');
                    wp_enqueue_script('mgwpp-threed-carousel-js');
                    if (!class_exists('MGWPP_3D_Carousel')) {
                        include_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-3d-carousel.php';
                    }
                    $gallery_html = MGWPP_3D_Carousel::render($post_id, $all_images);
                    break;

                case 'full_page_slider':
                    wp_enqueue_style('mg-fullpage-slider-styles');
                    wp_enqueue_script('mg-fullpage-slider-js');
                    if (!class_exists('MGWPP_Full_Page_Slider')) {
                        require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-full-page-slider.php';
                    }
                    $gallery_html = MGWPP_Full_Page_Slider::render($post_id, $all_images);
                    break;

                case 'spotlight_carousel':
                    wp_enqueue_style('mg-spotlight-slider-styles');
                    wp_enqueue_script('mg-spotlight-slider-js');
                    if (!class_exists('MGWPP_Spotlight_Carousel')) {
                        require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-spotlight-carousel.php';
                    }
                    $gallery_html = MGWPP_Spotlight_Carousel::render($post_id, $all_images);
                    break;

                case 'testimonials_carousel':
                    wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                    wp_enqueue_script('mgwpp-testimonial-carousel-js');
                    $testimonials = get_posts([
                        'post_type' => 'mgwpp_testimonial',
                        'posts_per_page' => -1,
                        'suppress_filters' => false
                    ]);

                    $gallery_html = '<p>No testimonials found.</p>';
                    if (!empty($testimonials)) {
                        if (!class_exists('MGWPP_Testimonial_Carousel')) {
                            require_once plugin_dir_path(__FILE__) . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
                        }
                        $gallery_html = MGWPP_Testimonial_Carousel::render($post_id, $testimonials);
                    }
                    break;

                case '3d_model_carousel':
                    wp_enqueue_style('mgwpp-3d-model-carousel-styles');
                    wp_enqueue_script('mgwpp-3d-model-carousel-js');
                    if (!class_exists('MGWPP_3D_Model_Carousel')) {
                        require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-model-carousel/class-mgwpp-3d-model-carousel.php';
                    }
                    $gallery_html = MGWPP_3D_Model_Carousel::render($post_id, $all_images);
                    break;

                case 'marquee_gallery':
                    if (!class_exists('MGWPP_Marquee_Gallery')) {
                        require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-marquee-gallery/class-mgwpp-marquee-gallery.php';
                    }
                    $gallery_html = MGWPP_Marquee_Gallery::render($post_id, $all_images);
                    break;

                case 'vertical_marquee':
                    if (!class_exists('MGWPP_Vertical_Marquee')) {
                        require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-vertical-marquee/class-mgwpp-vertical-marquee.php';
                    }
                    $gallery_html = MGWPP_Vertical_Marquee::render($all_images, [], $post_id);
                    break;

                case '3d_masonry_gallery':
                    if (!class_exists('MGWPP_3D_Masonry_Gallery')) {
                        require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-masonry-gallery/class-mgwpp-3d-masonry-gallery.php';
                    }
                    // Pass images, settings (empty defaults), and gallery_id
                    $gallery_html = MGWPP_3D_Masonry_Gallery::render($all_images, [], $post_id);
                    break;

                case '3d_h_marquee':
                    if (!class_exists('MGWPP_3D_Horizontal_Marquee')) {
                        require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-horizontal-marquee/class-mgwpp-3d-horizontal-marquee.php';
                    }
                    $gallery_html = MGWPP_3D_Horizontal_Marquee::render($all_images, [], $post_id);
                    break;

                default:
                    $gallery_html = '<p>Gallery type not recognized.</p>';
            }

            if (!empty($gallery_html)) {
                $output .= '<div class="mgwpp-gallery-item">' . $gallery_html . '</div>';
            }
        } else {
            $output .= '<p>No images found for this gallery.</p>';
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }

    return $output;
}
