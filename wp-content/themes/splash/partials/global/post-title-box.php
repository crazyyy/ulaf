<?php
$image = '';
if(has_post_thumbnail()){
    $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
}
?>
<div class="post-title-box"
     style="<?php if(!empty($image)) echo 'background: url(' . esc_url($image) . ') no-repeat center;'; ?>">
    <div class="container text-center">
        <div class="post-title">
            <h1><?php the_title(); ?></h1>
        </div>
        <div class="post-date">
            <?php echo get_the_date(); ?>
        </div>
    </div>

</div>