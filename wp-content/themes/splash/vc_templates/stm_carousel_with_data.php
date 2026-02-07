<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($images)):

$images = explode(',', $images);
$images_thumbs = array();


foreach($images as $image) {
	$post_thumbnail = wpb_getImageBySize( array(
		'attach_id' => $image,
		'thumb_size' => 'full'
	) );

	$images_thumbs[] = $post_thumbnail['thumbnail'];
}


$id = 'stm-images-carousel-' . rand(0,9999);
?>

<div class="stm-carousel-with-data-wrapp <?php echo esc_attr($id); ?>">
	<?php if($atts['title'] != ''): ?>
	<div class="stm-cwd-title">
		<<?php echo esc_html(getHTag()); ?>><?php echo esc_html($atts['title']); ?></<?php echo esc_html(getHTag()); ?>>
	</div>
	<?php endif; ?>
	<div class="stm-cwd-carousel-wrapp">
		<div class="stm-image-carousel-init">
			<?php foreach($images_thumbs as $image_tag): ?>
				<div class="stm-single-image-carousel">
					<?php echo wp_kses_post($image_tag); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if (splash_is_layout("rugby")): ?>
	<br>
	<?php endif; ?>
	<div class="stm-cwd-data-wrapp">
		<div class="stm-data-row">
			<table>
				<tr>
					<td>
						<i class="stm-icon-ico_pin icon-ico_pin"></i>
					</td>
					<td>
						<span class="stm-cwd-title"><?php esc_html_e("Location", 'splash'); ?></span>
					</td>
					<td>
						<span class="stm-data-content"><?php echo esc_html($atts['location']); ?></span>
						<a href="<?php echo get_site_url()?>/contacts" class="location_on_map"><?php esc_html_e('View on map', 'splash'); ?></a>
					</td>
				</tr>
			</table>
		</div>
		<div class="stm-data-row">
			<table>
				<tr>
					<td>
						<i class="stm-icon-ico_computer icon-ico_computer"></i>
					</td>
					<td>
						<span class="stm-cwd-title"><?php esc_html_e("Capacity", 'splash'); ?></span>
					</td>
					<td>
						<?php echo esc_html($atts['capacity']); ?>
					</td>
				</tr>
				<tr>
					<td>
						<i class="stm-icon-ico_quad icon-ico_quad"></i>
					</td>
					<td>
						<span class="stm-cwd-title"><?php esc_html_e("Surface", 'splash'); ?></span>
					</td>
					<td>
						<?php echo esc_html($atts['surface']); ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="stm-data-row">
			<table>
				<tr>
					<td>
						<i class="stm-icon-ico_calendar icon-ico_calendar"></i>
					</td>
					<td>
						<span class="stm-cwd-title"><?php esc_html_e("Opened", 'splash'); ?></span>
					</td>
					<td>
						<?php echo esc_html($atts['opened']); ?>
					</td>
				</tr>
				<tr>
					<td>
						<i class="stm-icon-ico_face icon-ico_face"></i>
					</td>
					<td>
						<span class="stm-cwd-title"><?php esc_html_e("Renovated", 'splash'); ?></span>
					</td>
					<td>
						<?php echo esc_html($atts['renovated']); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
	<script type="text/javascript">
		(function($) {
			"use strict";

			var unique_class = "<?php echo esc_js($id); ?>";

			var owl = $('.' + unique_class + ' .stm-image-carousel-init');

			$(document).ready(function () {

				<?php if(splash_is_af()): ?>
				var blockWidth = $(".stm-cwd-carousel-wrapp").width();
				var blockHeight = $(".stm-cwd-carousel-wrapp").height();

				console.log((parseInt(blockHeight)));
				console.log((parseInt(blockHeight) / parseInt(2)));

				owl.on('initialized.owl.carousel',function(){
					$(".owl-prev").css("left", "0px");
					$(".owl-prev").css("top", ((blockHeight/2)/2-13) + "px" );
					$(".owl-next").css("left", (blockWidth-74) + "px");
					$(".owl-next").css("top", ((blockHeight/2)/2-35) + "px" );
				});
				<?php endif; ?>

				owl.owlCarousel({
					items: 1,
					dots: false,
					autoplay: false,
					slideBy: 1,
					loop: true,
					navText: "",
					responsive:{
						0:{
							items:1,
							slideBy: 1
						},
						768:{
							items:1,
							slideBy: 1
						},
						992:{
							items:1,
							slideBy: 1
						},
						1100: {
							items: 1,
							slideBy: 1
						}
					}
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>