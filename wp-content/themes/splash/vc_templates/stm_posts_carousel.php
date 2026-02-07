<?php
$team = $player_list = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

splash_enqueue_modul_scripts_styles('stm_posts_carousel');

$tax = '';

if(!empty($post_categories)) {
    $tax = array(
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => explode(',', $post_categories)
        )
    );
}

$query = new WP_Query(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $number_posts,
    'tax_query' => $tax
));


$id = 'stm-posts-' . rand(0, 9999);

?>
<div class="stm-posts-list-wrapper">
    <div class="carousel-wrap">
        <div class="stm-post-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
        <div class="stm-posts clearfix">
            <?php if($query->have_posts()) :?>
            <ul class="<?php echo esc_attr($id); ?>">
                <?php
                    while($query->have_posts()){
                        $query->the_post();
                        get_template_part('partials/vc_templates_views/post_carousel_item');
                    }
                ?>
            </ul>
            <?php endif; ?>
        </div>
        <div class="stm-post-carousel-control-next"><i class="fa fa-angle-right"></i></div>
    </div>
</div>

<?php
if (empty($per_row)) {
    $per_row = 4;
}
?>
<script type="text/javascript">
    (function ($) {
        "use strict";

        var unique_class = "<?php echo esc_js($id); ?>";

        var owl = $('.' + unique_class);

        $(document).ready(function () {
            owl.owlCarousel({
                items: 4,
                dots: false,
                autoplay: false,
                slideBy: 4,
                loop: true,
                responsive: {
                    0: {
                        items: 1,
                        slideBy: 1
                    },
                    440: {
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
                    1100: {
                        items: <?php echo intval($visible_items); ?>,
                        slideBy: <?php echo intval($visible_items); ?>
                    }
                }
            });

            $('.stm-post-carousel-control-prev').on('click', function () {
                owl.trigger('prev.owl.carousel');
            });

            $('.stm-post-carousel-control-next').on('click', function () {
                owl.trigger('next.owl.carousel');
            });
        });
    })(jQuery);
</script>