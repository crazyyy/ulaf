<div class="stm-latest-news-single">
    <div <?php post_class('stm-single-post-loop'); ?>>
        <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
            <?php if (has_post_thumbnail()): ?>
                <div class="image">
                    <?php the_post_thumbnail('stm-360-240', array('class' => 'img-responsive')); ?>
                    <div class="date heading-font clear">
                        <?php echo esc_attr(get_the_date()); ?>
                    </div>
                </div>
            <?php endif; ?>
        </a>
        <div class="stm-news-data-wrapp">
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