<?php 
$id = 'stm-videos-carousel-' . rand(0,9999);
$attsDecode = json_decode(urldecode($atts["video_item"]));
$title = $atts["title"];
?>

<div class="stm-videos-carousel <?php echo esc_attr($id); ?>">
    <div class="stm-videos-top">
        <div class="stm-view-switcher">
            <span class="stm-block-view active"></span>
            <span class="stm-grid-view"></span>
        </div>
        <div class="stm-carousel-controls-right stm-videos-controls">
            <div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
            <div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
        </div>
    </div>
    <div class="stm-title">
        <<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
    </div>
    <div class="stm-videos-carousel-init-unit">
        <div id="stm-videos-carousel-init" class="stm-videos-carousel-init">
            <?php
            foreach ($attsDecode as $val):
                $video_img = wp_get_attachment_image_src($val->video_img, "full", false);
            ?>
            <div class="stm-video-wrapper">
                <div class="stm-video-title_wrapp">
                    <h3><?php echo esc_html($val->video_title); ?></h3>
                    <h4><?php echo esc_html($val->video_sub_title); ?></h4>
                </div>
                <div class="stm-video">
                    <div class="stm-video-wrapp">
                        <img class="stm-video-holder" src="<?php echo esc_attr($video_img[0]); ?>" data-url="<?php echo esc_url($val->video_embed_url)?>" />
                    </div>
                    <h4 class="stm-grid-title"><?php echo esc_html($val->video_sub_title); ?></h4>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script type="text/javascript">

(function($) {
    "use strict";
    var unique_class = "<?php echo esc_js($id); ?>";
    var owl = $('.' + unique_class + ' .stm-videos-carousel-init');

    $("body").on("click", ".stm-video", function() {
        var href = $(this).find(".stm-video-holder").attr("data-url");
        $.fancybox.open({
            padding: 0,
            href: href,
            type: 'iframe',
            width: '560',
            height: '315'
        });
    });

    $(document).ready(function () {

        $(".stm-grid-view").on("click", function () {

            $(".stm-block-view").removeClass("active");
            $(this).addClass("active");

            var $carousel = $('.owl-carousel');

            $(".stm-video-title_wrapp").hide();
            $(".stm-videos-carousel").find(".stm-title").show();

            var listElements = document.getElementsByClassName("stm-video-wrapper");
            var contentParent = document.getElementById("stm-videos-carousel-init");

            var html = "";
            var childs = "";
            for(var q=0;q<listElements.length;q++) {
                childs = childs + listElements[q].outerHTML;
                if(q%8 == 0) {
                    html = html + '<div class="stm-video-grid-wrapp">';
                } else if(q%8 == 7) {
                    html = html + childs;
                    html = html + '</div>';
                    childs = "";
                } else if(q == (listElements.length - 1)) {
                    html = html + childs;
                    html = html + "</div>";
                }
            }

            contentParent.innerHTML = "";
            contentParent.innerHTML = html;
            owl.trigger('destroy.owl.carousel');
            owl.html(owl.find('.owl-stage-outer').html()).removeClass('owl-loaded');
            owl.owlCarousel({
                items: 1,
                dots: false,
                autoplay: false,
                slideBy: 1,
                loop: false,
                navText: ''
            });
        });


        $(".stm-block-view").on("click", function () {

            $(".stm-grid-view").removeClass("active");
            $(this).addClass("active");

            $(".stm-videos-carousel").find(".stm-title").hide();
            $(".stm-video-title_wrapp").show();

            var listElements = document.getElementsByClassName("stm-video-wrapper");
            var contentParent = document.getElementById("stm-videos-carousel-init");

            var childs = "";
            for(var q=0;q<listElements.length;q++) {
                childs = childs + listElements[q].outerHTML;
            }

            contentParent.innerHTML = "";
            contentParent.innerHTML = childs;
            owl.trigger('destroy.owl.carousel');
            owl.html(owl.find('.owl-stage-outer').html()).removeClass('owl-loaded');
            owl.owlCarousel({
                items: 1,
                dots: false,
                autoplay: false,
                slideBy: 1,
                loop: false,
                navText: ''
            });
        });

        initOwl();
    });

    function initOwl() {
        console.log("init owl");
        <?php if(splash_is_af()): ?>
            var docWidth = $(document).width();
            var blockWidth = $(".<?php echo esc_js($id); ?>").width();
            var blockHeight = $(".stm-videos-carousel-init").height();

            owl.on('initialized.owl.carousel',function(){
                $(".owl-prev").css("left", "-" + (((docWidth - blockWidth) / 2)) + "px");
                $(".owl-prev").css("top", ((blockHeight/2) - 43) + "px" );
                $(".owl-next").css("left", (((docWidth - blockWidth) / 2) + blockWidth - 76) + "px");
                $(".owl-next").css("top",((blockHeight/2) - 67) + "px" );
            });
        <?php endif; ?>

        owl.owlCarousel({
            items: 1,
            dots: false,
            autoplay: false,
            slideBy: 1,
            loop: false,
            navText: ''
        });

        $('.' + unique_class + ' .stm-carousel-control-prev').on('click', function(){
            owl.trigger('prev.owl.carousel');
        });

        $('.' + unique_class + ' .stm-carousel-control-next').on('click', function(){
            owl.trigger('next.owl.carousel');
        });
    }

})(jQuery);






</script>
