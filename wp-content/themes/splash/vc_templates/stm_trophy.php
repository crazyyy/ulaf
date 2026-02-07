<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$post_thumbnail = wpb_getImageBySize( array(
	'attach_id' => $image,
	'thumb_size' => $image_size
) );

$height = '';
$height_new = explode('x', $image_size);

if(!empty($height_new[1])) {
	$height = 'style="min-height:' . $height_new[1] . 'px;"';
}

?>

<div class="stm-single-trophy">
	<?php if(!empty($post_thumbnail['thumbnail'])): ?>
		<div class="image" <?php echo sanitize_text_field($height); ?>>
			<?php echo wp_kses_post($post_thumbnail['thumbnail']); ?>
		</div>
	<?php endif; ?>

	<?php if(!empty($year)): ?>
		<div class="stm-year h6">
			<?php echo esc_attr($year); ?>
		</div>
	<?php endif; ?>

	<?php if(splash_is_layout("sccr") || splash_is_layout('hockey')) : ?>
		<div class="stm-border stm-animated"></div>
	<?php endif; ?>

	<?php if(!empty($title)): ?>
		<div class="stm-title <?php echo !splash_is_layout('soccer_two') ? 'heading-font' : ''; ?>">
			<?php echo esc_attr($title); ?>
		</div>
	<?php endif; ?>
	<?php if(!splash_is_layout("sccr") && !splash_is_layout('soccer_two') && !splash_is_layout('hockey')) : ?>
		<div class="stm-border stm-animated"></div>
	<?php endif; ?>

</div>


