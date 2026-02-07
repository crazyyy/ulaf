<div class="latest-news-loop">
    <div class="meta">
        <div class="meta-top">
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
        </div>
        <div class="meta-middle">
            <a href="<?php the_permalink(); ?>">
                <div class="title heading-font">
                    <span class="date normal_font"><?php the_date(); ?></span> <?php the_title(); ?> <?php echo splash_getPostViewsCountHtml(get_the_ID()); ?>
                </div>
            </a>
        </div>
    </div>
</div>