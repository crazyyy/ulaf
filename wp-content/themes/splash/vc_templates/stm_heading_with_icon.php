<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

?>
<div class="stm-icon-title-wrap">
	<div class="stm-icon-title">
		<i class="<?php echo esc_attr($icon_title); ?>"></i>
		<h4><?php echo esc_html($title); ?></h4>
	</div>
	<div class="stm-content">
		<?php echo esc_html($content); ?>
	</div>
</div>


