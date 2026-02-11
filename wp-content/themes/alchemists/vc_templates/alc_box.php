<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $content - shortcode content
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Alert
 */

$title = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$box_classes = array(
  'card',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
);

$class_to_filter = implode( ' ', array_filter( $box_classes ) );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

?>

<!-- Card -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>

  <?php if ( $title ) { ?>
    <div class="card__header">
      <?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
    </div>
  <?php } ?>

  <div class="card__content">
    <?php echo wpb_js_remove_wpautop( $content, true ); ?>
  </div>

</div>
<!-- Card / End -->
