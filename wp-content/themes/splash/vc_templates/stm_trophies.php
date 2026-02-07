<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );


$id = 'stm-trophy-carousel-' . rand(0,9999);
if (!in_array($styles, array('default', 'style_1'))) {
	splash_enqueue_modul_scripts_styles('stm_trophies_' . $styles);
}
?>

<div class="stm-trophy-carousel <?php echo esc_attr($styles) ?> <?php echo esc_attr($id); ?>">

	<div class="clearfix stm-trophy-carousel-title">
		<?php if(!empty($title) && !splash_is_layout('soccer_two') && $styles != 'style_2'): ?>
			<div class="stm-title-left">
				<<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
			</div>
		<?php endif; ?>
		<?php if ($styles == 'style_2' && !empty($title)): ?>
			<h2 class="stm-trophy-carousel__title"><?php echo esc_attr($title); ?></h2>
		<?php endif; ?>
		<?php if(!splash_is_layout('soccer_two') && $styles != 'style_2'): ?>
			<div class="stm-carousel-controls-right stm-thophies-controls">
				<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
				<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
			</div>
		<?php endif; ?>
	</div>

	<div class="stm-trophy-carousel-init-unit">
		<div class="stm-trophy-carousel-init <?php echo esc_attr($css_class); ?>">
			<?php echo wpb_js_remove_wpautop($content); ?>
		</div>
		<?php if(splash_is_layout('soccer_two')): ?>
			<div class="stm-carousel-controls-right stm-thophies-controls">
				<div class="stm-carousel-control-prev"><i class="icon-arrow-left"></i></div>
				<div class="stm-carousel-control-next"><i class="icon-arrow-right"></i></div>
			</div>
		<?php endif; ?>
	</div>

</div>

<script type="text/javascript">
	(function($) {
		"use strict";

		var unique_class = "<?php echo esc_js($id); ?>";

		var itemQuant768 = 2;

		var owl = $('.' + unique_class + ' .stm-trophy-carousel-init');
		<?php if(splash_is_af()): ?>
			var docWidth = $(document).width();
			var blockWidth = $(".<?php echo esc_js($id); ?>").width();
			var blockHeight = $(".stm-trophy-carousel-init").height();
			itemQuant768 = 3;

			owl.on('initialized.owl.carousel',function(){
				$(".owl-prev").css("left", "-" + (((docWidth - blockWidth) / 2)) + "px");
				$(".owl-prev").css("top", ((blockHeight/2) - 43) + "px" );
				$(".owl-next").css("left", (((docWidth - blockWidth) / 2) + blockWidth - 76) + "px");
				$(".owl-next").css("top",((blockHeight/2) - 67) + "px" );
			});
		<?php endif; ?>
		
		<?php if(splash_is_layout("sccr")): ?>
        itemQuant768 = 3;
		<?php endif; ?>

		$(document).ready(function () {
			var slides = 4;
			var margin = 0;
			<?php echo (splash_is_layout("sccr")) ? 'slides = 5; ' : ''; ?>
			<?php echo (splash_is_layout("esport")) ? 'margin = 25; ' : ''; ?>
			<?php echo (splash_is_layout("soccer_two")) ? 'slides = 1; ' : ''; ?>
			<?php echo (splash_is_layout("soccer_two")) ? 'itemQuant768 = 1; ' : ''; ?>
			<?php echo (splash_is_layout("rugby")) ? 'margin = 30; ' : ''; ?>
            <?php if(!empty($per_row)) echo 'slides = "' . esc_js($per_row)  . '";'; ?>
			owl.owlCarousel({
				items: slides,
				dots: false,
				autoplay: false,
				slideBy: slides,
				loop: true,
				navText: '',
                margin: margin,
				responsive:{
					0:{
						items:1,
						slideBy: 1
					},
					415: {
                        items: <?php echo (splash_is_layout("soccer_two")) ? 1 : 2; ?>,
                        slideBy: 1
					},
					768:{
						items:itemQuant768,
						slideBy: 1
					},
					992:{
						items: <?php echo (splash_is_layout("soccer_two")) ? 1 : 3; ?>,
						slideBy: 1
					},
					1100: {
						items: slides,
						slideBy: slides
					}
				}
			});

			$('.' + unique_class + ' .stm-carousel-control-prev').on('click', function(){
				owl.trigger('prev.owl.carousel');
			});

			$('.' + unique_class + ' .stm-carousel-control-next').on('click', function(){
				owl.trigger('next.owl.carousel');
			});
		});
	})(jQuery);
</script>