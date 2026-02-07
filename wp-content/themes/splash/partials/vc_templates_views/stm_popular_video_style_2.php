<?php 
$id = 'stm-videos-carousel-' . rand(0,9999);
$attsDecode = json_decode(urldecode($atts["video_item"]));
$title = $atts["title"];
splash_enqueue_modul_scripts_styles( 'stm_popular_video_' . $atts['view_style'] );
?>
<div class="stm-popular-videos stm-popular-videos--<?php echo esc_attr($atts['view_style']) ?> <?php echo esc_attr($id) ?>">
    <div class="stm-popular-videos__wrapper">
        <h2 class="stm-popular-videos__heading"><?php echo esc_html($title) ?></h2>
        <div class="stm-popular-videos__list">
            <?php foreach ($attsDecode as $video): 
                $poster = wp_get_attachment_image_src($video->video_img, "medium");
                ?>
            <div class="stm-popular-videos__item">
                <div class="stm-popular-videos__poster stm-video-holder" data-url="<?php echo esc_attr($video->video_embed_url) ?>">
                    <img src="<?php echo esc_attr($poster[0]) ?>" alt="<?php echo esc_attr($video->video_title) ?>">
                    <span class="stm-popular-videos__play"></span>
                </div>

                <?php if (isset($video->video_title) && $video->video_title != null): ?>
                <h4 class="stm-popular-videos__title"><?php echo esc_html($video->video_title); ?></h4>
                <?php endif; ?>

                <?php if (isset($video->video_sub_title) && $video->video_sub_title != null): ?>
                <h5 class="stm-popular-videos__subtitle"><?php echo esc_html($video->video_sub_title); ?></h5>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    (function($){
        
        "use strict";

        $("body").on("click", ".stm-video-holder", function() {
            var href = $(this).attr("data-url");
            $.fancybox.open({
                padding: 0,
                href: href,
                type: 'iframe',
                width: '560',
                height: '315',
                // href: href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
                // type: 'swf',
                // swf: {
                //     wmode: 'transparent',
                //     allowfullscreen: 'true'
                // }
            });
        });

    })(jQuery);
</script>