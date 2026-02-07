<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );
global $media_style;
$media_style = $atts['media_archive_style'];
?>

<div class="stm-media-vc-archive <?php echo esc_attr($css_class) ?>">
	<?php get_template_part('partials/global/media-archive'); ?>
</div>