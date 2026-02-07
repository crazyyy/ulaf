<?php
/*
 * 'title' => string 'Latest news' (length=11)
  'view_style' => string 'with_image' (length=10)
  'post_categories' => string '' (length=0)
  'show_load_more_btn' => string 'enable' (length=6)
  'load_mpre_btn_title' => string 'Load more' (length=9)
  'number_columns' => string '2' (length=1)
  'number' => string '8' (length=1)
 * */
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if( empty( $number ) ) {
    $number = 4;
}

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

splash_enqueue_modul_scripts_styles( 'stm_latest_news_most_styles' );

$generateClass = 'stm-news-' . $view_style;
$generateClass .= ( $number_columns == 2 ) ? ' columns-2' : ' columns-1';
$generateAjaxClass = 'stm-news-' . $view_style . rand( 0, 1000 );
$generateClass .= ' ' . $generateAjaxClass;
$tax = '';
$meta = '';

if( !empty( $post_categories ) ) {
    $tax = array(
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => explode( ',', $post_categories )
        )
    );
}

$data = 'data-view-style="' . $view_style . '"';
$data .= ( !empty( $post_categories ) ) ? ' data-categs="' . $post_categories . '"' : '';
$data .= ' data-offset="' . $number . '"';
$data .= ' data-limit="' . $number . '"';
$data .= ' data-num-columns="' . $number_columns . '"';
$data .= ' data-generate-class="' . $generateAjaxClass . '"';

$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $number,
    'tax_query' => $tax,
    'meta_query' => $meta
);

if( !empty( $order_by_popular ) ) {
    $args[ 'meta_query' ] = array(
        'post_views' => array(
            'key' => 'post_views_counter',
            'compare' => 'EXISTS',
        ),
    );
    $args[ 'orderby' ] = 'meta_value_num';
    $args[ 'order' ] = 'DESC';
}

$query = new WP_Query( $args );


?>
<div class="stm_latest_news_most_styles <?php echo esc_attr( $css_class . ' ' . $view_style ); ?>">

    <?php if( !empty( $title ) ): ?>
        <?php if( splash_is_layout( 'soccer_two' ) || splash_is_layout( 'volleyball' ) ): ?>
            <h2><?php echo splash_firstWordBold( $title ); ?></h2>
        <?php else: ?>
            <h4><?php echo splash_firstWordBold( $title ); ?></h4>
        <?php endif; ?>
    <?php endif; ?>
    <div class="<?php echo esc_attr( $generateClass ); ?>">
        <?php
        if( $query->have_posts() ) {
            $q = 0;
            if( $view_style == 'list' ) {
                $col = 'col-md-12 col-sm-12 col-xs-6';
                $firstColumn = $query->post_count;
                $posts = $query->get_posts();
                ?>
                <div class="row simple-list">
                    <?php
                    $count = 0;
                    while ( $query->have_posts() ) {
                        $count++;
                        $query->the_post();
                        echo '<div class="' . esc_attr( $col ) . '"><div class="column-content"><span class="simple-post-count heading-font">' . $count . '</span>';
                        get_template_part( 'partials/vc_templates_views/latest_news_most_styles_list' );
                        echo '</div></div>';
                    }
                    ?>
                </div>
                <?php
                wp_reset_postdata();
            } else if( $view_style == 'masonry' ) {
                ?>
                <div class="stm-post-masonry-wrap row">
                    <?php if( $news_page_url ): ?>
                        <a href="<?php echo esc_url( $news_page_url ); ?>"
                           class="news-page-link button btn-only-border">
                            <?php esc_html_e( 'All News', 'splash' ); ?>
                            <i class="icon-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                    <?php
                    $count = 0;
                    while ( $query->have_posts() ):
                        $count++;
                        $query->the_post(); ?>
                        <a href="<?php the_permalink(); ?>" class="stm-masonry-post-url">
                            <div class="stm-post-masonry-wrap__item <?php if( $count == 1 ) {
                                echo esc_attr( 'col-md-8 col-sm-8 big-post' );
                            } else {
                                echo esc_attr( 'col-md-4 col-sm-4 col-xs-6 small-post' );
                            } ?>">
                                <?php
                                $size = $count == 1 ? 'post-770-450' : 'post-370-210'; ?>
                                <div class="masonry-image-wrap">
                                    <?php the_post_thumbnail( $size ); ?>
                                </div>
                                <div class="stm-masonry-post-data">
                                    <div class="stm-masonry-post-data__title">
                                        <h4><?php the_title(); ?></h4>
                                    </div>
                                    <div class="stm-masonry-post-data__cat">
										<span class="cat">
											<?php
                                            $category = get_the_category( get_the_id() );
                                            if( !empty( $category ) ) {
                                                echo esc_html( $category[ 0 ]->name );
                                            }
                                            ?>
										</span>
                                        <span class="date"> | <?php echo get_the_date(); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>

                </div>
                <?php
                wp_reset_postdata();
            } else if( $view_style == 'masonry_2' ) {
                ?>
                <?php
                while ( $query->have_posts() ): $query->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" class="stm_latest_news_most_styles__post">
                        <span class="stm_latest_news_most_styles__post-image">
                            <?php the_post_thumbnail( 'large' ); ?>
                            <span class="stm_latest_news_most_styles__post-date">
                                <strong><?php echo get_the_date('d') ?></strong>
                                <span><?php echo get_the_date('M') ?></span>
                            </span>
                        </span>
                        <span class="stm_latest_news_most_styles__post-content">
                            <h2 class="stm_latest_news_most_styles__post-title"><?php the_title() ?></h2>
                            <?php the_excerpt() ?>
                        </span>
                    </a>
                <?php endwhile; ?>
            <?php
            } else if( $view_style != 'grid' ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $padding = '';
                    if( $number_columns == 2 ) $padding = ( $q % 2 == 1 ) ? 'pad-l-15' : 'pad-r-15';

                    if( $view_style == 'with_image' ) {
                        $class = ( $q < 2 ) ? 'column-content' : 'row-content';
                        echo '<div class="' . $class . ' ' . $padding . '">';
                        get_template_part( 'partials/vc_templates_views/latest_news_most_styles_with-img-' . $class );
                        echo '</div>';
                        if( $number_columns == 2 ) {
                            echo esc_html( $q % 2 == 1 ) ? "<div class='clearfix'></div>" : '';
                        }
                    } else {
                        echo '<div class="no-img ' . $padding . '">';
                        get_template_part( 'partials/vc_templates_views/latest_news_most_styles_no-img' );
                        echo '</div>';
                        if( $number_columns == 2 ) {
                            echo esc_html( $q % 2 == 1 ) ? "<div class='clearfix'></div>" : '';
                        }
                    }

                    $q++;
                }
            } else {
                $col = 'col-md-4 col-sm-6 col-xs-12';
                $firstColumn = $query->post_count;
                $posts = $query->get_posts();
                ?>
                <div class="row">
                    <?php
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        echo '<div class="' . esc_attr( $col ) . '"><div class="column-content">';
                        get_template_part( 'partials/vc_templates_views/latest_news_most_styles_with-img-column-content' );
                        echo '</div></div>';
                    }
                    ?>
                </div>
                <?php
            }
        }

        wp_reset_postdata();
        ?>
    </div>
    <?php if( $show_load_more_btn == 'enable' ): ?>
        <div class="load-more-btn-wrap">
            <a href="#"
               class="load-posts-more-style load-more-btn heading-font" <?php echo splash_sanitize_text_field( $data ); ?>>
                <?php echo esc_html( $load_mpre_btn_title ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
