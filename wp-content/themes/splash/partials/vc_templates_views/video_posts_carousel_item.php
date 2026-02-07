<?php
$li = '';
$catColor = '';
foreach( get_the_terms( get_the_ID(), 'category' ) as $val) {
    $catColor = get_term_meta($val->term_id, '_category_color', true);
    $li .= '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="background-color: #' . $catColor . '">' . $val->name . '</a></li>';
}

$prev = get_previous_post(true);
$next = get_next_post(true);
?>

<li class="video-post-carousel-wrap" data-prev-title="<?php echo (!empty($next)) ? $next->post_title : '';?>" data-next-title="<?php echo (!empty($prev)) ? $prev->post_title : '';?>">
    <div class="title heading-font" >
        <?php the_title(); ?>
    </div>
    <div class="img">
        <img src="<?php the_post_thumbnail_url('post-350-220'); ?>" />
        <div class="video-btn"></div>
        <div class="categ">
            <ul>
                <?php echo splash_sanitize_text_field($li); ?>
            </ul>
        </div>
    </div>
</li>