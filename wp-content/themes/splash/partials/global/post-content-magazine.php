<?php
$sidebar_id = get_theme_mod('sidebar_blog', 'primary_sidebar');
$sidebar_position = get_theme_mod('sidebar_position', 'left');

if (!empty($sidebar_id)) {
    $blog_sidebar = get_post($sidebar_id);
} else {
    $blog_sidebar = '';
}

if ($sidebar_id == 'no_sidebar') {
    $sidebar_id = false;
}

$stm_sidebar_layout_mode = splash_sidebar_layout_mode($sidebar_position, $sidebar_id);
$format = get_post_format();

if (!splash_is_layout('magazine_two') && !splash_is_layout('soccer_news')):
    ?>

    <div class="row stm-format-<?php echo esc_attr($format); ?> single-post-content-magazine">
        <?php echo wp_kses_post($stm_sidebar_layout_mode['content_before']); ?>
        <div class="stm-single-post-meta clearfix normal_font">
            <div class="meta-top">
                <div class="stm_author_box clearfix">
                    <div class="avatar-wrap">
                        <div class="author_avatar">
                            <?php echo get_avatar(get_the_author_meta('email'), 174); ?>
                        </div>
                    </div>
                    <div class="author_info">
                    <span class="author-name normal_font">
                        <?php the_author_meta('nickname'); ?>
                    </span>
                        <div class="stm-date">
                            <?php echo get_the_date(); ?>
                        </div>
                    </div>
                </div>
                <div class="follow-btn">
                    <div class="stm-share-this-wrapp">
                    <span class="stm-share-btn-wrapp">
                        <?php if (function_exists('A2A_SHARE_SAVE_pre_get_posts')) echo A2A_SHARE_SAVE_add_to_content(""); ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="meta-bottom">
                <div class="stm_post_tags">
                    <?php the_tags('<i class="icon-mg-tag"></i>', ''); ?>
                </div>
                <div class="post-meta-wrap">
                    <div class="time_for_read">
                        <i class="icon-mg-time"></i>
                        <span class="read_text normal_font"></span>
                    </div>
                    <?php echo splash_getPostViewsCountHtml(get_the_ID()); ?>
                    <div class="stm-comments-num">
                        <a href="<?php comments_link(); ?>" class="stm-post-comments">
                            <i class="icon-mg-comments" aria-hidden="true"></i><?php comments_number("", "", "%"); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="read-content">
            <div class="post-excerpt">
                <?php the_excerpt(); ?>
            </div>

            <!--Post thumbnail-->
            <?php if (has_post_thumbnail()): ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail('stm-1170-650', array('class' => 'img-responsive')); ?>
                </div>
            <?php endif; ?>


            <div class="post-content">
                <?php the_content(); ?>
                <div class="clearfix"></div>
            </div>
        </div>

        <?php splash_pages_pagination(); ?>


        <div class="stm-post-meta-bottom <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?> clearfix">
            <div class="stm_post_tags">
                <?php the_tags('<i class="icon-mg-tag"></i>', ''); ?>
            </div>
            <div class="stm-share-this-wrapp">
            <span class="stm-share-btn-wrapp">
                <?php if (function_exists('A2A_SHARE_SAVE_pre_get_posts')) echo A2A_SHARE_SAVE_add_to_content(""); ?>
            </span>
            </div>
        </div>

        <div class="related-posts-by-cat">
            <h4><?php echo splash_firstWordBold(esc_html__('We recommend', 'splash')); ?></h4>
            <div class="related-list">
                <?php
                $related = get_posts(array('category__in' => wp_get_post_categories($post->ID), 'numberposts' => 3, 'post__not_in' => array($post->ID)));
                if ($related) foreach ($related as $post) {
                    setup_postdata($post);
                    $id = $post->ID;
                    ?>
                    <div class="latest-news-loop-with-img">
                        <a href="<?php echo get_the_permalink($id) ?>">
                            <div class="wrap">
                                <div class="img">
                                    <img src="<?php echo get_the_post_thumbnail_url($id, 'post-350-220'); ?>"/>
                                    <div class="categ">
                                        <ul>
                                            <?php
                                            foreach (get_the_terms($id, 'category') as $val) {
                                                $catColor = get_term_meta($val->term_id, '_category_color', true);
                                                echo '<li class="normal_font" style="background-color: #' . $catColor . '">' . $val->name . '</li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="meta">
                                    <div class="meta-middle title heading-font">
                                        <?php echo get_the_title($id); ?>
                                    </div>
                                    <div class="meta-bottom">
                                        <div class="date normal_font">
                                            <?php echo get_the_date('d M, Y', $id); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php }
                wp_reset_postdata();
                ?>
            </div>
        </div>

        <!--Comments-->
        <?php if (comments_open() || get_comments_number()) { ?>
            <div class="stm_post_comments">
                <?php comments_template(); ?>
            </div>
        <?php } ?>

        <?php echo wp_kses_post($stm_sidebar_layout_mode['content_after']); ?>


        <!--Sidebar-->
        <?php splash_display_sidebar(
            $sidebar_id,
            $stm_sidebar_layout_mode['sidebar_before'],
            $stm_sidebar_layout_mode['sidebar_after'],
            $blog_sidebar
        ); ?>
    </div>
    <script>
        var toRead = '<?php echo esc_html__('to read', 'splash'); ?>';
    </script>
<?php else: ?>

    <?php the_content(); ?>

    <!--Comments-->
    <?php if (comments_open() || get_comments_number()) { ?>
        <div class="stm_post_comments">
            <?php comments_template(); ?>
        </div>
    <?php } ?>
<?php endif; ?>
