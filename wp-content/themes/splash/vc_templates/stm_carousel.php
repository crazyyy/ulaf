<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($custom_links)) {
    $custom_links = vc_value_from_safe( $custom_links );
    $custom_links = explode( ',', $custom_links );
} else {
    $custom_links = array();
}

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


	$id = 'stm-images-carousel-' . rand(0,9999);
	?>

	<div class="stm-image-carousel <?php echo esc_attr($id); ?>">

		<div class="clearfix">
			<?php if(!empty($title)): ?>
				<div class="stm-title-left">
					<<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
				</div>
			<?php endif; ?>
			<div class="stm-carousel-controls-right stm-image-controls" <?php if($atts['disable_controlls'] == 'disable') echo 'style="display: none;"'; ?>>
				<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
				<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
			</div>
		</div>

		<div class="stm-image-carousel-init-unit <?php if(!empty($disable_grayscale) && $disable_grayscale == 'yes') echo 'disable_grayscale'; ?>">
			<div class="stm-image-carousel-init">
				<?php foreach($images_thumbs as $key => $image_tag): ?>
					<div class="stm-single-image-carousel">
                        <?php if(!empty($custom_links[$key])) echo '<a href="' . esc_url($custom_links[$key]) . '">'; ?>
						    <?php echo wp_kses_post($image_tag); ?>
                        <?php if(!empty($custom_links[$key])) echo '</a>'; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div>

	<script type="text/javascript">
		(function($) {
			"use strict";

			var unique_class = "<?php echo esc_js($id); ?>";

			var owl = $('.' + unique_class + ' .stm-image-carousel-init');

			var desktopItems = 5;

			<?php if(splash_is_layout('esport')): ?>
            desktopItems = 6;
            <?php endif; ?>
			$(document).ready(function () {
				owl.owlCarousel({
					items: 4,
					dots: false,
					autoplay: false,
					slideBy: 4,
					loop: true,
					responsive:{
						0:{
							items:1,
							slideBy: 1
						},
						420: {
                            items:3,
                            slideBy: 3
						},
						768:{
							items:3,
							slideBy: 3
						},
						992:{
							items:4,
							slideBy: 4
						},
						1100: {
							items: desktopItems,
							slideBy: desktopItems
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

<?php endif; ?>