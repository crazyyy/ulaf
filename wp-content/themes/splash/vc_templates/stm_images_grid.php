<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($images)) {
	$images = explode(',', $images);
}

if(empty($columns)) {
	$columns = '4';
}

$columns = intval(12/$columns);

if(empty($load_by)) {
	$load_by = 12;
} else {
	$load_by = intval($load_by);
}

$uniq_id = rand(0,9999);

$classH = (splash_is_layout('magazine_one')) ? 'h4' : 'h5';

?>

<div class="stm-images-grid">

	<?php if(!empty($title)): ?>
		<div class="title <?php echo esc_attr($classH); ?> <?php if(!splash_is_layout('hockey')) echo esc_html("white")?>">
			<?php echo (splash_is_layout('magazine_one')) ? splash_firstWordBold(esc_attr($title)) : esc_attr($title); ?>
		</div>
	<?php endif; ?>

	<?php if(!empty($images)): ?>
		<div class="row">
			<?php foreach($images as $key => $image): ?>
				<?php $post_thumbnail = wpb_getImageBySize( array(
					'attach_id' => $image,
					'thumb_size' => $image_size
				) );

				$class = '';

				if(($key + 1) > $load_by) {
					$class = 'stm-waiting';
				}

				$image_full = wp_get_attachment_image_src($image, 'full');

				if(!empty($image_full[0])) {
					$image_full = $image_full[0];
				}

				if(!empty($post_thumbnail['thumbnail'])): ?>
					<div class="col-md-<?php echo esc_attr($columns); ?> col-sm-4">
						<a href="<?php echo esc_url($image_full); ?>" rel="stm-images-grid-<?php echo esc_attr($uniq_id); ?>" class="stm-fancybox stm-images-grid-single <?php echo esc_attr($class); ?>">
							<div class="stm-images-grid-zoom"><i class="icon-search"></i></div>
							<?php echo wp_kses_post($post_thumbnail['thumbnail']); ?>
						</a>
					</div>
				<?php endif; ?>

			<?php endforeach; ?>

		</div>

		<?php if(count($images) > $load_by): ?>
			<div class="text-center stm-load-more-images-grid">
				<a href="#" class="button  btn-<?php echo esc_attr($button_type); ?> <?php echo sanitize_text_field($button_size); ?> btn-<?php echo esc_attr($button_color_style); ?>" data-loadby="<?php echo esc_attr($load_by) ?>" data-page="1">
					<?php esc_html_e('Load more', 'splash'); ?>
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>

</div>