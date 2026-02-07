<?php $video_url = get_post_meta(get_the_ID(), 'video_url', true); ?>
<div class="latest-news-loop-with-img">
    <div class="wrap">
        <div class="img">
            <img src="<?php the_post_thumbnail_url('post-110-70'); ?>" />
            <div id="play-video" class="video-btn" data-src="<?php echo esc_attr($video_url); ?>"></div>
        </div>
        <div class="meta">
            <div class="meta-top">
                <div class="categ">
                    <ul>
                        <?php
                        foreach( get_the_terms( get_the_ID(), 'category' ) as $val) {
                            $catColor = get_term_meta($val->term_id, '_category_color', true);
                            echo '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="color: #' . $catColor . ' !important;">' . $val->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="meta-middle title heading-font">
                <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                </a>
            </div>
            <div class="meta-bottom">
                <div class="date normal_font">
                    <?php echo get_the_date(); ?>
                </div>
            </div>
        </div>
    </div>
</div>