<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $link
 * @var $type
 * @var $size
 * @var $align
 * @var $btn_block
 * @var $color
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Btn
 */


$title = $link = $type = $size = $align = $btn_block = $color = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$wrapper_classes = array(
  'btn-container',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
  'btn-container--align-' . $align,
);

$button_classes = array(
	'btn',
	'btn-' . $color,
	'btn-' . $type,
  'btn-' . $size,
);

if ( 'true' === $btn_block && 'inline' !== $align ) {
	$button_classes[] = 'btn-block';
}

//parse link
$link = ( '||' === $link ) ? '' : $link;
$link = vc_build_link( $link );
$use_link = false;
if ( strlen( $link['url'] ) > 0 ) {
	$use_link = true;
	$a_href = $link['url'];
	$a_title = $link['title'];
	$a_target = $link['target'];
	$a_rel = $link['rel'];
}

$class_to_filter = implode( ' ', array_filter( $wrapper_classes ) );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

if ( $button_classes ) {
	$button_classes = esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $button_classes ) ), $this->settings['base'], $atts ) );
	$attributes[] = 'class="' . trim( $button_classes ) . '"';
}

if ( $use_link ) {
	$attributes[] = 'href="' . trim( $a_href ) . '"';
	$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
	if ( ! empty( $a_target ) ) {
		$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
	}
	if ( ! empty( $a_rel ) ) {
		$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
	}
}

$attributes = implode( ' ', $attributes );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

?>

<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>
  <?php if ( $use_link ) :
    echo '<a ' . $attributes . '>' . esc_html( $title ) . '</a>';
  endif; ?>
</div>
