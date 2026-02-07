<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

// ID
$timeline_id = uniqid('stm-timeline-');
?>

<div class="stm-timeline__nav-bar">
	<div class="stm-timeline__steps">
		<ul class="stm-timeline__steps-list">
			<li class="stm-timeline__steps-progress"></li>
		</ul>
	</div>
	<div class="stm-timeline__nav"></div>
</div>

<div class="stm-timeline" id="<?php echo esc_attr( $timeline_id ); ?>">

	<div class="stm-timeline-images-carousel<?php echo esc_attr( $css_class ); ?>">
		<?php echo wpb_js_remove_wpautop( $content ); ?>
	</div>

	<div class="stm-timeline-captions-carousel"></div>

</div>
<script>
	(function($) {
		"use strict";

		var sliderId = '<?php echo esc_js( $timeline_id ); ?>';

		$(document).ready(function() {
			$( "#" + sliderId + " .stm-timeline-images-carousel" ).owlCarousel({
				nav:true,
				dots:false,
				items: 1,
				navText:['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
				smartSpeed: 450,
				navContainer: '.stm-timeline__nav',
				URLhashListener:true,
				autoplayHoverPause:true,
				startPosition: 'URLHash'
			});

			$( "#" + sliderId + " .stm-timeline-captions-carousel" ).owlCarousel({
				dots:false,
				items: 1,
				animateIn: 'fadeIn',
				mouseDrag: false,
				URLhashListener:true
			});

			$( "#" + sliderId + " .stm-timeline-images-carousel" ).on("translated.owl.carousel", function(event) {
				$("#" + sliderId + " .stm-timeline-captions-carousel").trigger('to.owl.carousel', [event.item.index, 300, true]);

				$(".stm-timeline__step").eq(event.item.index).find("a").trigger("click");
			});
		});

		$( "#" + sliderId + " .stm-timeline-images-carousel" ).find(".stm-timeline__image").each(function() {
			var itemHash = $(this).data("hash");

			$(".stm-timeline__steps-list").append('<li class="stm-timeline__step"><a href="#'+ itemHash +'">'+ itemHash +'</a></li>');
		});

		$(document).on("click", ".stm-timeline__step a", function() {
			var stepEl = $(this).parent(),
				progressLineWidth = stepEl.position().left;

			stepEl.addClass("active").siblings().removeClass("active");

			$(".stm-timeline__steps-progress").width( progressLineWidth + "px" );
		});

		$("#" + sliderId + " .stm-timeline-images-carousel .stm-timeline__image").each(function() {
			$(this).find(".stm-timeline__caption").clone().appendTo($(".stm-timeline-captions-carousel"));
			$(this).find(".stm-timeline__caption").remove();
		});

	})(jQuery);
</script>