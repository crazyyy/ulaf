<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$attsDecode = json_decode( urldecode( $atts[ "video_item" ] ) );
$title = $atts[ "title" ];

$id = 'stm-videos-carousel-' . rand( 0, 9999 );
?>

<div class="single_video <?php echo esc_attr( $id ); ?>">
    <div class="stm-videos-top">
        <div class="stm-title">
            <<?php echo esc_html( getHTag() ); ?>
            class="stm-main-title-unit"><?php echo esc_attr( $title ); ?></<?php echo esc_html( getHTag() ); ?>>
    </div>
    <?php if( count( $attsDecode ) > 1 ): ?>
        <div class="stm-carousel-controls-right stm-videos-controls">
            <div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
            <div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
        </div>
    <?php endif; ?>
</div>

<div class="stm-videos-carousel-init-unit">
    <div id="stm-videos-carousel-init" class="stm-videos-carousel-init">
        <?php
        foreach( $attsDecode as $val ):
            $video_img = wp_get_attachment_image_src( $val->video_img, "full", false );
            ?>
            <div class="stm-video-wrapper">
                <div class="stm-video-title_wrapp">
                    <?php if( !empty( $val->video_title ) ): ?>
                        <h4><?php echo esc_html( $val->video_sub_title ); ?></h4>
                    <?php endif; ?>
                </div>
                <div class="stm-video">
                    <div class="stm-video-wrapp">
                        <?php if( !empty( $val->video_embed_url) && !empty($video_img[ 0 ])  ): ?>
                        <img class="stm-video-holder" src="<?php echo esc_attr( $video_img[ 0 ] ); ?>"
                             data-url="<?php echo esc_url( $val->video_embed_url ) ?>"/>
                        <?php endif; ?>
                    </div>
                    <?php if( !empty( $val->video_sub_title ) ): ?>
                    <h4 class="stm-grid-title"><?php echo esc_html( $val->video_sub_title ); ?></h4>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script type="text/javascript">

    (function ($) {
        "use strict";
        var unique_class = "<?php echo esc_js( $id ); ?>";
        var owl = $('.' + unique_class + ' .stm-videos-carousel-init');

        $("body").on("click", ".stm-video", function () {
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
            initOwl();
        });

        function initOwl() {
            <?php if(splash_is_af()): ?>
            var docWidth = $(document).width();
            var blockWidth = $(".<?php echo esc_js( $id ); ?>").width();
            var blockHeight = $(".stm-videos-carousel-init").height();
            owl.on('initialized.owl.carousel', function () {
                $(".owl-prev").css("left", "-" + (((docWidth - blockWidth) / 2)) + "px");
                $(".owl-prev").css("top", ((blockHeight / 2) - 43) + "px");
                $(".owl-next").css("left", (((docWidth - blockWidth) / 2) + blockWidth - 76) + "px");
                $(".owl-next").css("top", ((blockHeight / 2) - 67) + "px");
            });
            <?php endif; ?>
            <?php if(splash_is_layout('esport')): ?>
            owl.owlCarousel({
                items: 1,
                dots: false,
                autoplay: false,
                slideBy: 1,
                navText: '',
            });
            <?php else: ?>
            owl.owlCarousel({
                items: 4,
                dots: false,
                autoplay: false,
                slideBy: 1,
                margin: 30,
                loop: true,
                navText: '',
                responsive: {
                    0: {
                        items: 1,
                        slideBy: 1
                    },
                    520: {
                        items: 2,
                        slideBy: 2
                    },
                    768: {
                        items: 3,
                        slideBy: 3
                    },
                    992: {
                        items: 3,
                        slideBy: 3
                    },
                }
            });
            <?php endif; ?>


            $('.' + unique_class + ' .stm-carousel-control-prev').on('click', function () {
                owl.trigger('prev.owl.carousel');
            });

            $('.' + unique_class + ' .stm-carousel-control-next').on('click', function () {
                owl.trigger('next.owl.carousel');
            });
        }
    })(jQuery);
</script>
