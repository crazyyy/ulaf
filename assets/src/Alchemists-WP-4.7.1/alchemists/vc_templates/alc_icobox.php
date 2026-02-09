<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $description
 * @var $link
 * @var $style
 * @var $custom_color_holder
 * @var $i_color
 * @var $custom_color_icon
 * @var $size
 * @var $shape
 * @var $align
 * @var $i_type
 * @var $i_icon_fontawesome
 * @var $i_icon_openiconic
 * @var $i_icon_typicons
 * @var $i_icon_entypo
 * @var $i_icon_linecons
 * @var $i_icon_simpleline
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Icobox
 */

$title = $description = $link = $style = $custom_color_holder = $i_color = $custom_color_icon = $size = $shape = $align = $i_type = $i_icon_fontawesome = $i_icon_openiconic = $i_icon_typicons = $i_icon_entypo = $i_icon_linecons = $i_icon_simpleline = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();
$icon_attributes = array();
$icon_styles = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$wrapper_classes = array(
	'icobox',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
	'icobox--' . $align,
);

$icon_classes = array(
	'icobox__icon',
	'icobox__icon--' . $style,
	'icobox__icon--' . $size,
	'icobox__icon--' . $shape,
);

// Enqueue needed icon font
vc_icon_element_fonts_enqueue( $i_type );
$iconClass = isset( ${'i_icon_' . $i_type} ) ? esc_attr( ${'i_icon_' . $i_type} ) : 'fa fa-adjust';

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

// add icon custom background-color
if ( 'custom' === $style ) {
	if ( $custom_color_holder ) {
		$icon_styles[] = vc_get_css_color( 'background-color', $custom_color_holder );
	}
}

// add icon custom color
if ( 'custom' === $i_color ) {
	if ( $custom_color_icon ) {
		$icon_styles[] = vc_get_css_color( 'color', $custom_color_icon );
	}
}

if ( $icon_styles ) {
	$icon_attributes[] = 'style="' . implode( ' ', $icon_styles ) . '"';
}

$class_to_filter = implode( ' ', array_filter( $wrapper_classes ) );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

if ( $icon_classes ) {
	$icon_classes = esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $icon_classes ) ), $this->settings['base'], $atts ) );
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


<!-- Icobox -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>
	<div class="<?php echo trim( esc_attr( $icon_classes ) ) ?>" <?php echo implode( ' ', $icon_attributes ); ?>>
		<i class="<?php echo esc_attr( $iconClass ); ?>"></i>
	</div>
	<div class="icobox__content">

		<?php
		if ( $title ) :
			if ( $use_link ) {
				echo '<h4 class="icobox__title"><a ' . $attributes . '>' . esc_html( $title ) . '</a></h4>';
			} else {
				echo '<h4 class="icobox__title">' . esc_html( $title ) . '</h4>';
			}
		endif; ?>

		<?php if ( $description ) : ?>
		<div class="icobox__description">
			<?php echo esc_html( $description ); ?>
		</div>
		<?php endif; ?>

	</div>
</div>
<!-- Icobox / End -->
