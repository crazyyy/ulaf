<?php
$text_color = $call_to_action_label = $button_type = $button_size = $button_color_style = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$link = vc_build_link( $link );

if(!empty($text_color)) {
	$text_color = 'style="color:'.$text_color.'"';
}

?>

<?php if(!empty($call_to_action_label)): ?>
	<div class="stm-call-to-action clearfix">
		<?php if(!empty($link['url']) and !empty($link['title'])): ?>
			<a
				class="button btn-<?php echo esc_attr($button_type); ?> <?php echo sanitize_text_field($button_size); ?> btn-<?php echo esc_attr($button_color_style); ?>"
				href="<?php echo esc_url($link['url']) ?>"
				title="<?php echo esc_attr($link['title']); ?>"
				<?php if(!empty($link['target'])): ?>target="_blank" <?php endif; ?>
			><?php echo esc_attr($link['title']); ?></a>
		<?php endif; ?>
		<div class="stm-call-to-action-inner">
			<h4 <?php echo sanitize_text_field($text_color); ?>><?php echo esc_attr($call_to_action_label); ?></h4>
		</div>
	</div>

<?php endif; ?>