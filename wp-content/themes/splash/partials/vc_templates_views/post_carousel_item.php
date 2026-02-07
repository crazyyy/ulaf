<?php
$li = '';
$catColor = '';
$cats = get_the_terms( get_the_ID(), 'category' );
if($cats) {
    $catColor = get_term_meta($cats[0]->term_id, '_category_color', true);
    $li .= '<li><a href="' . get_category_link($cats[0]->term_id) . '" class="normal_font" style="background-color: #' . $catColor . '">' . $cats[0]->name . '</a></li>';
}

$genClass = 'duotone-' . rand(2, 1000);

$custom_css = "
<style>
." . $genClass . ".lighten::before {
  background-color: rgba(" . splash_hex2rgba($catColor) . ");
}

." . $genClass . ".darken::after {
  background-color: rgba(37,0,64,1);
}
</style>
";
//stm_posts_most_styles
//wp_add_inline_style( 'stm-theme-default-styles', $custom_css );

?>

<li class="post-carousel-wrap">
    <?php echo esc_attr($custom_css); ?>
    <div class="duotone <?php echo esc_attr($genClass); ?> darken lighten" style="background-image: url('<?php the_post_thumbnail_url('post-275-142'); ?>');"></div>
    <div class="black-overlay"></div>
    <div class="meta">
        <div class="meta-top">
            <div class="categ">
                <ul>
                    <?php echo splash_sanitize_text_field($li); ?>
                </ul>
            </div>
        </div>
        <div class="meta-middle title heading-font">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </div>
        <div class="meta-bottom">
            <div class="date normal_font">
                <?php echo get_the_date(); ?>
            </div>
        </div>
    </div>
</li>