<?php
$atts  = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$addvertBg =  wp_get_attachment_image_src($addvertisment_bg_img, "full");
$addvertLogo =  wp_get_attachment_image_src($addvertisment_logo, "full");
$addvertisment_title;
$addvertisment_sub_title;
$addvertisment_btn_text;
$addvertisment_btn_link;
?>

<div class="addvert_wrapp" style="background: url('<?php echo esc_url($addvertBg[0]); ?>') no-repeat;">
	<?php if($addvertisment_btn_text == ""): ?>
	<a href="<?php echo esc_url($addvertisment_btn_link); ?>">
	<?php endif; ?>
	<?php if($addvertLogo != null): ?>
	<div class="addvert_logo">
		<img src="<?php echo esc_url($addvertLogo[0]) ?>" />
	</div>
	<?php endif; ?>
	<div class="addvert_info_wrapp">
		<?php if($addvertisment_title != ""): ?>
		<div class="addvert_title heading-font">
			<span>
				<?php echo esc_html($addvertisment_title); ?>
			</span>
		</div>
		<?php endif; ?>
		<?php if($addvertisment_sub_title != ""): ?>
		<div class="addvert_sub_title normal_font">
			<span>
				<?php echo esc_html($addvertisment_sub_title); ?>
			</span>
		</div>
		<?php endif; ?>
		<?php if($addvertisment_btn_text != ""): ?>
		<div class="addvert_btn">
			<a href="<?php echo esc_url($addvertisment_btn_link); ?>" class="button button-default-white">
				<?php echo esc_html($addvertisment_btn_text); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>
</div>

