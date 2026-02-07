<div class="stm-latest-news-single">
    <div <?php post_class('stm-single-post-loop'); ?>>
        <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">

            <?php if (has_post_thumbnail() and !is_search()): ?>
                <div class="image <?php echo esc_html(get_post_format(get_the_ID())); ?>">
                    <?php the_post_thumbnail('stm-555-460', array('class' => 'img-responsive')); ?>
                    <?php if (is_sticky(get_the_id())): ?>
                        <div class="stm-sticky-post heading-font"><?php esc_html_e('Sticky Post', 'splash'); ?></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (is_sticky(get_the_id())): ?>
                    <div class="stm-sticky-post stm-sticky-no-image heading-font"><?php esc_html_e('Sticky Post', 'splash'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </a>

        <div class="stm-news-data-wrapp">
            <div class="date clear">
                <?php echo esc_attr(get_the_date( 'd M' )); ?>
            </div>
            <div class="title heading-font clear">
                <a href="<?php the_permalink() ?>">
                    <?php the_title(); ?>
                </a>
            </div>
            <div class="content_">
                <?php the_excerpt(); ?>
            </div>
        </div>
    </div>
</div>