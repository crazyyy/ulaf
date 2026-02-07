<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
?>
<div class="stm-coach-excerption-wrapp">
	<?php if(!empty($atts['title'])) :?>
	<div class="stm-title-wrapp">
		<h3><?php echo esc_html($atts['title']); ?></h3>
	</div>
	<?php endif; ?>
	<div class="clearfix"></div>
	<div class="stm-data-wrapp">
		<div class="stm-photo-wrapp">
			<img src="<?php echo esc_url(wp_get_attachment_url($atts['photo']))?>" />
		</div>
		<div class="stm-excerption-wrapp">
			<span class="stm-excerption">
				<?php echo esc_html($atts['excerption']); ?>
			</span>
			<div class="stm-name-sign-wrapp">
				<span class="stm-coach-name heading-font">
					<?php echo esc_html($atts['name']); ?>
				</span>
				<div class="stm-img-wrapp">
					<img src="<?php echo esc_url(wp_get_attachment_url($atts['signature']))?>" />
				</div>
			</div>
		</div>
	</div>
</div>
