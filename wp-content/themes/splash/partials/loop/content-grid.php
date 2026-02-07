<?php
if (splash_is_layout("sccr")) {
    $col = "col-md-3 col-sm-4 col-xs-6";
} elseif (splash_is_layout("hockey")) {
    $col = "col-md-6 col-sm-6 hc";
} else {
    $col = "col-md-4 col-sm-4";
}
$image_size = 'stm-255-183';
if (splash_is_layout("bb")) {
    $image_size = 'stm-570-350';
} elseif (splash_is_layout("sccr")) {
    $image_size = 'blog_list';
} elseif (splash_is_layout("soccer_two") || splash_is_layout("esport")) {
    $image_size = 'post-370-210';
} elseif (splash_is_layout("hockey")) {
    $image_size = 'post-720-440';
}
?>
<?php $cat = wp_get_post_terms(get_the_ID(), "category"); ?>
<div class="<?php echo esc_attr($col); ?>">
    <div <?php post_class('stm-single-post-loop'); ?>>
        <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">

            <?php if (has_post_thumbnail()): ?>
                <?php if (splash_is_layout('soccer_two') || !is_search()): ?>
                    <div class="image <?php if (get_post_format(get_the_ID())) echo get_post_format(get_the_ID()); ?>">
                        <div class="stm-plus"></div>
                        <?php
                        the_post_thumbnail($image_size, array('class' => 'img-responsive'))?>
                        <?php if (is_sticky(get_the_id())): ?>
                            <div class="stm-sticky-post heading-font"><?php esc_html_e('Sticky Post', 'splash'); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if (is_sticky(get_the_id())): ?>
                    <div class="stm-sticky-post stm-sticky-no-image heading-font"><?php esc_html_e('Sticky Post', 'splash'); ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!splash_is_layout("sccr") && !splash_is_layout('soccer_two')): ?>
                <div class="date <?php echo (!splash_is_layout("af")) ? "heading-font" : "normal_font"; ?>">
                    <?php echo esc_attr(get_the_date()); ?>
                </div>
            <?php endif; ?>
            <?php if(splash_is_layout("bb") || splash_is_layout('esport')): ?>
                <div class="title heading-font">
                    <?php the_title(); ?>
                </div>
            <?php endif; ?>
            <?php if (!splash_is_layout("sccr") && !splash_is_layout("hockey") && !splash_is_layout("bb") && !splash_is_layout('esport')): ?>
                <div class="post-info-sccr-two">
                    <div class="title heading-font">
                        <?php the_title(); ?>
                    </div>
                    <?php
                    if(!splash_is_layout("af")):
                    if (count($cat) > 0) :
                        $catList = "<span class='cats'>";
                        $catList = $catList . $cat[0]->name;
                        $catList = $catList . "</span>";
                        ?>

                        <div class="stm-cat-list-wrapp">
                            <?php echo splash_sanitize_text_field($catList); ?>
                        </div>
                    <?php endif; ?>
                    <div class="stm-post-time">
                        <?php echo esc_attr(get_the_date()); ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </a>

        <?php if (!splash_is_af() && !splash_is_layout('soccer_two') && !splash_is_layout('basketball_two') && !splash_is_layout('esport')): ?>
            <div class="content">
                <?php if (splash_is_layout("sccr") || splash_is_layout("hockey")): ?>
                    <div class="title heading-font">
                        <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                    </div>
                <?php endif; ?>
                <?php the_excerpt(); ?>
            </div>
        <?php endif; ?>
        <!--  hockey     -->
        <?php if (!splash_is_layout('hockey') && !splash_is_layout('esport')): ?>
            <!--  hockey end -->
            <div class="post-meta <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?>">
                <!--category-->
                <?php if (splash_is_layout("af") || splash_is_layout("basketball_two")): ?>

                    <?php
                    if (count($cat) > 0) :
                        $catList = "<ul>";
                        //foreach ($cat as $k => $val) {
                        $catList = $catList . "<li><a href='" . get_term_link($cat[0]->term_id) . "'>" . $cat[0]->name;
                        //	if(($k + 1) < count($cat)) $catList = $catList . ", ";
                        $catList = $catList . "</a></li>";
                        //}
                        $catList = $catList . "</ul>";
                        ?>

                        <div class="stm-cat-list-wrapp">
                            <i class="fa fa-folder-o" aria-hidden="true"></i>
                            <?php echo splash_sanitize_text_field($catList); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (splash_is_layout("sccr")): ?>
                    <div class="stm-post-time">
                        <?php echo esc_attr(get_the_date()); ?>
                    </div>
                <?php endif; ?>
                <!--comments-->
                <?php if (!splash_is_layout('soccer_two')): ?>
                    <?php $comments_num = get_comments_number(get_the_id()); ?>
                    <?php if ($comments_num): ?>
                        <div class="comments-number">
                            <a href="<?php the_permalink() ?>#comments">
                                <?php echo (!splash_is_layout("bb")) ? '<i class="fa fa-comment-o" aria-hidden="true"></i>' : '<i class="fa fa-commenting"></i>'; ?>
                                <span><?php echo esc_attr($comments_num); ?><?php if (splash_is_af()) esc_html_e('comments', 'splash'); ?></span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="comments-number">
                            <a href="<?php the_permalink() ?>#comments">
                                <?php echo (!splash_is_layout("bb")) ? '<i class="fa fa-comment-o" aria-hidden="true"></i>' : '<i class="fa fa-commenting"></i>'; ?>
                                <span>0 <?php if (splash_is_af()) esc_html_e('comments', 'splash'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <!--tags-->
                <?php if (!splash_is_layout('af') && !splash_is_layout('soccer_two')): ?>
                    <?php $posttags = get_the_tags();
                    if ($posttags): ?>
                        <div class="post_list_item_tags">
                            <?php $count = 0;
                            foreach ($posttags as $tag): $count++; ?>
                                <?php if ($count == 1): ?>
                                    <a href="<?php echo get_tag_link($tag->term_id); ?>">
                                        <i class="fa fa-tag"></i>
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <!--  hockey     -->
        <?php endif; ?>
        <!--  hockey end   -->
    </div>
</div>