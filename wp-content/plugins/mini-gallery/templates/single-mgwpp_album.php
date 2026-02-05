<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        echo '<div class="mgwpp-album">';

        // Album title
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';

        // Album content - use WordPress the_content() function
        echo '<div class="mgwpp-album-content">';
        the_content();
        echo '</div>';

        // Fetch related galleries
        $mgwpp_related_galleries = get_post_meta(get_the_ID(), '_mgwpp_album_galleries', true);

        if (!empty($mgwpp_related_galleries) && is_array($mgwpp_related_galleries)) {
            echo '<div class="mgwpp-album-galleries">';
            foreach ($mgwpp_related_galleries as $mgwpp_gallery_id) {
                $mgwpp_gallery_id = absint($mgwpp_gallery_id);
                $mgwpp_gallery = get_post($mgwpp_gallery_id);

                if ($mgwpp_gallery && $mgwpp_gallery->post_type === 'mgwpp_soora' && $mgwpp_gallery->post_status === 'publish') {
                    // Output gallery using shortcode
                    echo '<div class="mgwpp-album-gallery-item">';
                    echo do_shortcode('[mgwpp_gallery id="' . absint($mgwpp_gallery_id) . '"]');
                    echo '</div>';
                } else {
                    echo '<p class="mgwpp-invalid-gallery">' .
                        esc_html__('Invalid gallery ID:', 'mini-gallery') . ' ' .
                        absint($mgwpp_gallery_id) .
                        '</p>';
                }
            }
            echo '</div>';
        } else {
            echo '<p class="mgwpp-no-galleries">' . esc_html__('No galleries found in this album.', 'mini-gallery') . '</p>';
        }

        echo '</div>'; // .mgwpp-album
    endwhile;
endif;

get_footer();
