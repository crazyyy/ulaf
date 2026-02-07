<?php
$title = $post_categories = $number = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

if (empty($number)) {
    $number = 3;
}

$number = intval($number);

if (!empty($post_categories)) {
    $post_categories = explode(', ', $post_categories);
    ?>
    <div class="stm-media-tabs stm-news-tabs-wrapper <?php echo esc_attr($news_tabs_style); ?>">
        <div class="clearfix">
            <?php if (!empty($title)): ?>
            <div class="stm-title-left">
                <<?php echo esc_html(getHTag()); ?>
                class="stm-main-title-unit"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
        </div>
        <?php endif; ?>
        <div class="stm-media-tabs-nav">
            <ul class="stm-list-duty heading-font" role="tablist">
                <?php $counter = 0; ?>
                <?php foreach ($post_categories as $post_category): $counter++; ?>
                    <?php
                    $category = get_category_by_slug($post_category);
                    if (!empty($category)): ?>
                        <li <?php if ($counter == 1): ?>class="active"<?php endif; ?>>
                            <a href="#<?php echo esc_attr($category->slug) ?>"
                               aria-controls="<?php echo esc_attr($category->slug) ?>" role="tab" data-toggle="tab"
                               <?php if (splash_is_layout('baseball')) : ?>class="normal_font"<?php endif; ?>>
                                <span><?php echo esc_attr($category->name); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <?php $counter = 0; ?>
        <?php foreach ($post_categories as $post_category): $counter++; ?>
            <div role="tabpanel" class="tab-pane fade <?php if ($counter == 1) { ?>in active<?php } ?>"
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
                    <div class="row row-3 row-sm-2">
                        <?php while ($post_query->have_posts()): $post_query->the_post(); ?>

                            <?php
                            if ($news_tabs_style == "style_baseball") get_template_part('partials/loop/content-grid-vc-baseball');
                            else get_template_part('partials/loop/content-grid');
                            ?>

                        <?php endwhile; ?>
                    </div>

                    <?php if ($stm_show_load_more == "enable"): ?>
                        <div class="stm-load-more-btn">
                            <a href="<?php echo esc_url(get_term_link($post_category, 'category')); ?>"
                               class="button only_border">
                                <?php echo esc_html__("More News", "splash"); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <h4><?php esc_html_e('No news in this category', 'splash'); ?></h4>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

<?php }