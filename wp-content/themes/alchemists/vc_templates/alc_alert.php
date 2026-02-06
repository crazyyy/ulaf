<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   3.3.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $style
 * @var $dismissible
 * @var $content - shortcode content
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Alert
 */

$dismissible = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$alert_classes = array(
	'alert',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
	'alert-' . $style,
);

if ( 'true' === $dismissible ) {
	$alert_classes[] = 'alert-dismissible';
}

$class_to_filter = implode( ' ', array_filter( $alert_classes ) );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

?>

<!-- Alert -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>

	<?php if ( 'true' === $dismissible ) : ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<?php endif; ?>

	<?php echo wpb_js_remove_wpautop( $content, true ); ?>
</div>
<!-- Alert / End -->
