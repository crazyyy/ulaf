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

$sidebar_settings = splash_get_sidebar_settings();
$sidebar_id = $sidebar_settings['id'];

$stm_sidebar_layout_mode = splash_sidebar_layout_mode($sidebar_settings['position'], $sidebar_id);

if(empty($stm_sidebar_layout_mode['sidebar_before'])) {
    $col = "col-md-4 col-sm-4 col-xs-12 stm-col-12";
}
?>
<div class="<?php echo esc_attr($col); ?>">
    <div <?php post_class('stm-single-post-loop'); ?>>
        <?php if(has_post_thumbnail() && !is_search()): ?>
            <div class="news-loop">
                <div class="wrap">
                    <div class="img">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php the_post_thumbnail_url('post-350-220'); ?>" />
                        </a>
                        <?php if(!empty(get_the_terms( get_the_ID(), 'category' ))): ?>
                        <div class="categ">
                            <ul>
                                <?php
                                foreach( get_the_terms( get_the_ID(), 'category' ) as $val) {
                                    $catColor = get_term_meta($val->term_id, '_category_color', true);
                                    echo '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="background-color: #' . $catColor . '">' . $val->name . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="meta">
                        <div class="meta-middle title heading-font">
                            <a href="<?php the_permalink()?>">
                                <?php the_title(); ?>
                            </a>
                        </div>
                        <div class="meta-bottom">
                            <?php the_excerpt(); ?>
                            <div class="date normal_font">
                                <?php echo get_the_date(); ?>
                                <?php echo splash_getPostViewsCountHtml(get_the_ID()); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php if(get_post_status() == 'publish'): ?>
                <div class="news-loop">
                    <div class="wrap">
                        <div class="img">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php the_post_thumbnail_url('post-350-220'); ?>" />
                            </a>
                        </div>
                        <div class="meta">
                            <div class="meta-middle title heading-font">
                                <a href="<?php the_permalink()?>">
                                    <?php the_title(); ?>
                                </a>
                            </div>
                            <div class="meta-bottom">
                                <?php the_excerpt(); ?>
                                <div class="date normal_font">
                                    <?php echo get_the_date(); ?>
                                    <?php echo splash_getPostViewsCountHtml(get_the_ID()); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>