<?php
$title = $post_categories = $number = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);


if (empty($number)) {
    $number = 4;
}
if ($view_style == 'style_2') $number = 5;
if ($view_style == 'style_5') $number = 3;

$number = intval($number);
if (!empty($post_categories)) {
    $post_categories = explode(', ', $post_categories);
    ?>
    <div class="stm-news-grid <?php echo esc_attr($view_style == 'style_1') ? '' : $view_style; ?> stm-media-tabs stm-news-tabs-wrapper">
        <div class="clearfix">
            <?php if (!empty($title)): ?>
            <div class="stm-title-left">
                <<?php echo esc_html(getHTag()); ?>
                class="stm-main-title-unit"><?php echo esc_html($title); ?></<?php echo esc_html(getHTag()); ?>>
        </div>
        <?php endif; ?>
        <div id="media_tabs_nav" class="stm-media-tabs-nav">
            <ul class="stm-list-duty heading-font" role="tablist">
                <?php if ($atts["include_all_news"] == 'enable'): ?>
                    <li class="active">
                        <a href="#<?php echo esc_attr('All') ?>" aria-controls="<?php echo esc_attr('All') ?>"
                           class="active" role="tab" data-toggle="tab">
                            <span><?php esc_html_e('All', 'splash'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php $counter = 0; ?>
                <?php foreach ($post_categories as $post_category): $counter++; ?>
                    <?php
                    $category = get_category_by_slug($post_category);
                    if (!empty($category)): ?>
                        <li <?php if ($counter == 1 && $atts["include_all_news"] == ''): ?>class="active"<?php endif; ?>>
                            <a href="#<?php echo esc_attr($category->slug) ?>"
                               aria-controls="<?php echo esc_attr($category->slug) ?>" role="tab" data-toggle="tab">
                                <span><?php echo esc_html($category->name); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <?php if ($atts['include_all_news'] == 'enable'): ?>
            <div role="tabpanel" class="tab-pane fade in active" id="<?php echo esc_attr('All'); ?>">
                <?php /*Create query*/
                $post_args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => $number
                );

                $post_query = new WP_Query($post_args);

                if ($post_query->have_posts()): ?>
                    <div class="stm-latest-news-wrapp">
                        <?php while ($post_query->have_posts()): $post_query->the_post(); ?>
                            <?php if ($view_style == 'style_3'): ?>
                                <?php get_template_part('partials/loop/content-news-grid-style3'); ?>
                            <?php elseif ($view_style == 'style_4'): ?>
                                <?php get_template_part('partials/loop/content-news-grid-style4'); ?>
                            <?php elseif ($view_style == 'style_5'): ?>
                                <?php get_template_part('partials/loop/content-news-grid-style5'); ?>
                            <?php else: ?>
                                <?php get_template_part('partials/loop/content-news-grid'); ?>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>

                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <h4><?php esc_html_e('No news in this category', 'splash'); ?></h4>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php $counter = 0; ?>
        <?php foreach ($post_categories as $post_category): $counter++; ?>
            <div role="tabpanel"
                 class="tab-pane fade <?php if ($counter == 1 && $atts["include_all_news"] == "") { ?>in active<?php } ?>"
                 id="<?php echo esc_attr($post_category); ?>">
                <?php /*Create query*/
                $post_args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => $number,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'category',
                            'field' => 'slug',
                            'terms' => $post_category
                        )
                    )
                );

                $post_query = new WP_Query($post_args);

                if ($post_query->have_posts()): ?>
                    <div class="stm-latest-news-wrapp">
                        <?php while ($post_query->have_posts()) {
                            $post_query->the_post();
                            if ($view_style == 'style_3') {
                                get_template_part( 'partials/loop/content-news-grid-style3' );
                            } elseif ($view_style == 'style_4') {
                                get_template_part('partials/loop/content-news-grid-style4');
                            } else {
                                get_template_part('partials/loop/content-news-grid');
                            }
                        } ?>
                    </div>

                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <h4><?php esc_html_e('No news in this category', 'splash'); ?></h4>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

<?php }