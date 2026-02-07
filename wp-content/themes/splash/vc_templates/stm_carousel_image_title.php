<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

/*
 * $visible_item;
 * $disable_controlls;
 * $
 * */

$imgs = json_decode(urldecode($img_title));
$id = 'stm-imgs-'.rand(0,9999);
?>

<div class="stm-it-main-wrapper <?php echo esc_attr($id); ?>">
	<div class="clearfix">
		<?php if(!empty($title)): ?>
			<div class="stm-title-left">
				<h2 class="stm-main-title-unit"><?php echo esc_attr($title); ?></h2>
			</div>
		<?php endif; ?>
		<?php if($disable_controlls != 'disable') : ?>
			<div class="stm-carousel-controls-right stm-it-controls">
				<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
				<div class="stm-it-dots"></div>
				<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
			</div>
		<?php endif; ?>
	</div>
	<div class="stm-image-title-carousel-wrapper">
		<div id="carousel" class="stm-image-title-wrap">
			<?php foreach($imgs as $val) : ?>
				<?php $img = wp_get_attachment_image_src($val->image, 'post-770-450'); ?>
				<div class="stm-image-title">
					<div class="stm-img">
						<img src="<?php echo esc_url($img[0]); ?>" alt="gallery" />
					</div>
					<div class="stm-title heading-font">
						<span><?php echo esc_html($val->title); ?></span>
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
		
		var owl = $('.' + unique_class + ' .stm-image-title-wrap');
		
		$(document).ready(function () {
			owl.owlCarousel({
				items: <?php echo esc_js($visible_item); ?>,
				dots: true,
				//autoplay: true,
				//autoplayHoverPause: true,
				margin: 20,
				loop:true,
				slideBy: <?php echo esc_js($visible_item); ?>,
                responsive: {
                    320:{
                        items: 1,
                        slideBy: 1
                    },
                    768:{
                        items: 3,
                        slideBy: 3
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
