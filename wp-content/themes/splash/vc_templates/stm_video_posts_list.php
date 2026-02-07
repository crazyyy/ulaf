<?php
$team = $player_list = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);


splash_enqueue_modul_scripts_styles('stm_video_posts_list');

$query = new WP_Query(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $number_posts,
    'tax_query' => array(
        array(
            'taxonomy' => 'post_format',
            'field' => 'slug',
            'terms' => 'post-format-video'
        )
    )
));

?>

<div class="stm_video_posts_list">
    <?php

    if($query->have_posts()):
        if($first_big_img != 'enable') {
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('partials/vc_templates_views/stm_video_posts_list_item');
            }
            wp_reset_postdata();
        } else {

        $col = 'col-md-6 col-sm-6 col-xs-12';
        $posts = $query->get_posts();

    ?>
        <div class="row">
            <div class="<?php echo esc_attr($col); ?>">
                <?php
                $post = $posts[0];
                $post_id = $post->ID;
                $video_url = get_post_meta($post_id, 'video_url', true);
                $img_size = 'post-350-220';
                if(splash_is_layout('esport')){
                    $img_size = 'gallery_image';
                }
                ?>
                <div class="big-img-wrap">
                    <div class="title heading-font" >
                        <?php echo get_the_title($post_id); ?>
                    </div>
                    <div class="img">
                        <a href="<?php the_permalink($post_id); ?>">
                            <img src="<?php echo get_the_post_thumbnail_url($post_id, $img_size); ?>" />
                        </a>
                        <div id="play-video" class="video-btn" data-src="<?php echo esc_attr($video_url); ?>"></div>
                        <div class="categ">
                            <ul>
                                <?php
                                foreach( get_the_terms( $post_id, 'category' ) as $val) {
                                    $catColor = get_term_meta($val->term_id, '_category_color', true);
                                    echo '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="background-color: #' . $catColor . ';">' . $val->name . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <?php if(!empty($video_url)) : ?>
                            <div class="video-frame">
                            <?php
                                $video_w = 350;
                                $video_h = $video_w / 1.58;
                                echo '<iframe id="video-post-frame" width="' . $video_w . '" height="' . $video_h . '" frameborder="0" allowfullscreen allow="autoplay"></iframe>';
                            ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="<?php echo esc_attr($col); ?>">
                <?php
                for($i=1;$i<$query->post_count;$i++) {
                    $post = $posts[$i];
                    $post_id = $post->ID;
                    ?>
                    <div class="latest-news-loop-with-img">
                        <div class="wrap">
                            <div class="img">
                                <a href="<?php the_permalink($post_id); ?>">
                                    <img src="<?php echo get_the_post_thumbnail_url($post_id,'post-110-70'); ?>" />
                                </a>
                                <div id="play-video" class="video-btn" data-src="<?php echo esc_attr($video_url); ?>"></div>
                            </div>
                            <div class="meta">
                                <div class="meta-top">
                                    <div class="categ">
                                        <ul>
                                            <?php
                                            foreach( get_the_terms( $post_id, 'category' ) as $val) {
                                                $catColor = get_term_meta($val->term_id, '_category_color', true);
                                                echo '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="color: #' . $catColor . ' !important;">' . $val->name . '</a></li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="meta-middle title heading-font">
                                    <a href="<?php echo get_the_permalink($post_id); ?>">
                                        <?php echo get_the_title($post_id); ?>
                                    </a>
                                </div>
                                <div class="meta-bottom">
                                    <div class="date normal_font">
                                        <?php echo get_the_date('d M, Y', $post_id); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    <?php
        }
        wp_reset_postdata();
    endif; ?>
</div>
