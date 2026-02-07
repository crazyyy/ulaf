<?php
$team = $player_list = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

splash_enqueue_modul_scripts_styles('stm_video_posts_carousel');

$query = new WP_Query(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $number_posts,
    'tax_query' => array(
        array(
            'taxonomy' => 'post_format',
            'field' => 'slug',
            'terms' => 'post-format-video'
        )
    )
));

$id = 'stm-posts-' . rand(0, 9999);
if($style == 'slider'):
?>
<div class="stm-video-posts-list-wrapper">
    <div class="carousel-wrap">
        <div class="stm-posts clearfix">
            <?php if($query->have_posts()) :?>
                <ul class="<?php echo esc_attr($id); ?>">
                    <?php
                    while($query->have_posts()){
                        $query->the_post();
                        get_template_part('partials/vc_templates_views/video_posts_carousel_item');
                    }
                    wp_reset_postdata();
                    ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="nav-wrap">
            <div class="stm-post-video-carousel-control-prev"><i class="fa fa-angle-left"></i><span class="prev-post-title heading-font"></span></div>
            <div class="stm-post-video-carousel-control-next"><span class="next-post-title heading-font"></span><i class="fa fa-angle-right"></i></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function ($) {
        "use strict";

        var unique_class = "<?php echo esc_js($id); ?>";

        var owl = $('.' + unique_class);

        owl.on('initialized.owl.carousel',function(property){
            showHide(property);
        });

        $(document).ready(function () {
            owl.owlCarousel({
                items: 1,
                dots: false,
                autoplay: false,
                slideBy: 1,
                loop: false,
                animateIn: 'fadeIn',
                animateOut: 'fadeOut',
                responsive: {
                    0: {
                        items: 1,
                        slideBy: 1
                    },
                    440: {
                        items: 1,
                        slideBy: 1
                    },
                    768: {
                        items: 1,
                        slideBy: 1
                    },
                    992: {
                        items: 1,
                        slideBy: 1
                    },
                    1100: {
                        items: 1,
                        slideBy: 1
                    }
                }
            });

            owl.on('changed.owl.carousel',function(property){
                showHide(property)
            });



            $('.stm-post-video-carousel-control-prev').on('click', function () {
                owl.trigger('prev.owl.carousel');
            });

            $('.stm-post-video-carousel-control-next').on('click', function () {
                owl.trigger('next.owl.carousel');
            });
        });

        function showHide(property) {
            var current = property.item.index;
            var item = $(property.target).find(".owl-item").eq(current).find(".video-post-carousel-wrap");
            var prev = item.data('prev-title');
            var next = item.data('next-title');
            var prevWrap = $('.stm-post-video-carousel-control-prev');
            var nextWrap = $('.stm-post-video-carousel-control-next');
            if(prev != '') {
                prevWrap.removeClass('hideArrow');
                $('.prev-post-title').text(prev);
            } else {
                prevWrap.addClass('hideArrow');
                $('.prev-post-title').text('');
            }

            if(next != '') {
                nextWrap.removeClass('hideArrow');
                $('.next-post-title').text(next);
            } else {
                nextWrap.addClass('hideArrow');
                $('.next-post-title').text('');
            }
        }
    })(jQuery);
</script>
<?php else: ?>
	<?php if($query->have_posts()) :?>
		<div class="stm-video-carousel">
			<?php
			$i = 0;
			while($query->have_posts()){
				$query->the_post();
				get_template_part('partials/vc_templates_views/video_posts_carousel_item_carousel');
			}
			wp_reset_postdata();
			?>
		</div>
	<?php endif; ?>
<?php endif; ?>
