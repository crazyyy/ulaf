<?php
$col = "col-md-4";

switch (get_theme_mod('news_grid_columns', 3)) {
	case 1:
		$col = "col-md-12";
		break;
	case 2:
		$col = "col-md-6";
		break;
	case 4:
		$col = "col-md-3";
		break;
}

$col = $col . " col-sm-6 col-xs-6 stm-col-12";
?>
<div class="<?php echo esc_attr($col); ?>">
    <div <?php post_class('stm-single-post-loop'); ?>>
            <?php if(has_post_thumbnail() and !is_search()): ?>
                <div class="image <?php if(get_post_format(get_the_ID())) echo get_post_format(get_the_ID()); ?>">
                    <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
                        <?php (get_the_post_thumbnail_url(get_the_ID(),'stm-350-250', array('class' => 'img-responsive'))) ? the_post_thumbnail('stm-350-250', array('class' => 'img-responsive')) : the_post_thumbnail('full', array('class' => 'img-responsive')); ?>
                    </a>
                    <?php if(is_sticky(get_the_id())): ?>
                        <div class="stm-sticky-post heading-font"><?php esc_html_e('Sticky Post','splash'); ?></div>
                    <?php endif; ?>
                    <div class="post-meta <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?>">
                        <!--category-->
                        <?php
                        $cat = wp_get_post_terms(get_the_ID(), "category");
                        if(count($cat) > 0) :
                            $catList = "<ul>";
                            $catList = $catList . "<li><a href='" . get_term_link($cat[0]->term_id) . "'>" . $cat[0]->name;
                            $catList = $catList . "</a></li>";
                            $catList = $catList . "</ul>";
                            ?>

                            <div class="stm-cat-list-wrapp">
                                <i class="icon-folder"></i>
                                <?php echo splash_sanitize_text_field($catList); ?>
                            </div>
                        <?php endif; ?>

                        <!--comments-->
                        <?php $comments_num = get_comments_number(get_the_id()); ?>
                        <?php if($comments_num): ?>
                            <div class="comments-number">
                                <a href="<?php the_permalink() ?>#comments">
                                    <i class="icon-comment" aria-hidden="true"></i>
                                    <span><?php echo esc_attr($comments_num); ?> <?php if(splash_is_layout("af")) esc_html_e('comments', 'splash'); ?></span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="comments-number">
                                <a href="<?php the_permalink() ?>#comments">
                                    <i class="icon-comment" aria-hidden="true"></i>
                                    <span>0 <?php if(splash_is_layout("af")) esc_html_e('comments', 'splash'); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if(get_post_format(get_the_ID())): ?>
                        <span class="stm-post-format stm-post-format-<?php echo esc_html(get_post_format(get_the_ID())); ?>">
                            <?php echo esc_html(get_post_format(get_the_ID())); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if(is_sticky(get_the_id())): ?>
                    <div class="stm-sticky-post stm-sticky-no-image heading-font"><?php esc_html_e('Sticky Post','splash'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        <div class="content">
            <!--DATE-->
            <div class="date">
                <span class="stm-date-day heading-font"><?php echo esc_attr(get_the_date("d")); ?></span>
                <span class="stm-date-month normal_font"><?php echo esc_attr(get_the_date("M")); ?></span>
            </div>
            <div class="stm-content-wrap">
                <h5 class="title heading-font">
                    <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                </h5>
                <?php the_excerpt(); ?>
            </div>
        </div>
    </div>
</div>