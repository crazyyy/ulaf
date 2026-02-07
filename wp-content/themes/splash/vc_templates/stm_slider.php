<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($images)):

	$images = explode(',', $images);
	$images_thumbs = array();


	foreach($images as $image) {
		$post_thumbnail = wpb_getImageBySize( array(
			'attach_id' => $image,
			'thumb_size' => $image_size
		) );

		$images_thumbs[] = $post_thumbnail['thumbnail'];
	}


	$id = 'stm-images-slider-' . rand(0,9999);
	?>

	<div class="stm-image-slider <?php echo esc_attr($id); ?>">

		<div class="stm-slider-control-prev"><i class="fa fa-angle-left"></i></div>
		<div class="stm-slider-control-next"><i class="fa fa-angle-right"></i></div>

		<div class="stm-image-slider-init-unit">
			<div class="stm-image-slider-init">
				<?php foreach($images_thumbs as $image_tag): ?>
					<div class="stm-single-image-slider">
						<?php echo wp_kses_post($image_tag); ?>
					</div>
				<?php endforeach; ?>
			</div>
			
		</div>
		<?php if($enable_thumbnails): ?>
			<div class="stm-thumbs-slider-init">
				<?php foreach($images_thumbs as $image_tag): ?>
					<div class="stm-single-thumb">
						<?php echo wp_kses_post($image_tag); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<script type="text/javascript">
		(function($) {
			"use strict";

			var unique_class = "<?php echo esc_js($id); ?>";
			var flag = false, duration = 300;

			var owl = $('.' + unique_class + ' .stm-image-slider-init');
			var owl_thumbs = $('.' + unique_class + ' .stm-thumbs-slider-init');

			$(document).ready(function () {
				owl.owlCarousel({
					items: 1,
					dots: false,
					autoplay: false,
					slideBy: 1,
					loop: false
				})
				<?php if($enable_thumbnails): ?>
				.on('changed.owl.carousel', function (e) {

					owl_thumbs.find('.owl-item').removeClass("thumb_active");
					owl_thumbs.find('.owl-item').eq(e.item.index).addClass("thumb_active");

				})
				<?php endif; ?>
				;

				$('.' + unique_class + ' .stm-slider-control-prev').on('click', function(){
					owl.trigger('prev.owl.carousel');
				});

				$('.' + unique_class + ' .stm-slider-control-next').on('click', function(){
					owl.trigger('next.owl.carousel');
				});

				<?php if($enable_thumbnails): ?>
				owl_thumbs.on('initialized.owl.carousel', function () {
					owl_thumbs.find('.owl-item').eq(0).addClass("thumb_active");
				});
				owl_thumbs.owlCarousel({
					items: 6,
					dots: false,
					autoplay: false,
					slideBy: 6,
					loop: false
				})
				.on('click', '.owl-item', function () {
					owl.trigger('to.owl.carousel', [$(this).index(), duration, true]);
				})
				.on('changed.owl.carousel', function (e) {
					if (!flag) {
						flag = true;
						owl.trigger('to.owl.carousel', [e.item.index, duration, true]);
						flag = false;
					}
				});
				<?php endif; ?>
			});
		})(jQuery);
	</script>

<?php endif; ?>