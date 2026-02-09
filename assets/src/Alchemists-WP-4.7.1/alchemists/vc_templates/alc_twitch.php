<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $streamer
 * @var $size
 * @var $preview
 * @var $max
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Social_Buttons
 */

$streamer = $size = $preview = $max = $hide_offline = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget widget--sidebar card widget_tp_twitch_widget';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$streams_args  = array();
$template_args = array();
$output_args   = array();

// Streamers
$streamer = str_replace(' ', '', $streamer);
$streams_args['streamer'] = $streamer;

// Templates
$template_args['template'] = 'widget';
$template_args['style'] = 'white';
$template_args['size'] = $size;
$template_args['preview'] = $preview;

// Output
$output_args['max'] = $max;
?>

<!-- Widget: Twitch -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<?php
	if ( function_exists( 'tp_twitch_display_streams' ) ) {
		tp_twitch_display_streams( $streams_args, $template_args, $output_args, true );
	} else {
		echo '<div class="alert alert-warning">' . sprintf( esc_html__( '%s plugin is not installed', 'alchemists' ), '<strong>Twitch for WordPress</strong>' ). '</div>';
	}
	?>
</div>
<!-- Widget: Twitch / End -->
