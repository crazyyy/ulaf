<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $subtitle
 * @var $subtitle_2
 * @var $link
 * @var $image
 * @var $discount_txt
 * @var $discount_price
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Woo_Banner
 */

$title = $subtitle = $subtitle_2 = $link = $image = $discount_txt = $discount_price = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$banner_classes = array(
  'shop-banner',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
);

$class_to_filter = implode( ' ', array_filter( $banner_classes ) );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

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

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$attributes = implode( ' ', $attributes );

?>

<!-- Shop Banner -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>

  <div class="shop-banner__content">

    <?php if ( $subtitle_2) : ?>
    <div class="shop-banner__txt1"><?php echo esc_html( $subtitle_2 ); ?></div>
    <?php endif; ?>

    <?php if ( $title ) : ?>
    <h2 class="shop-banner__title"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if ( $subtitle) : ?>
    <span class="shop-banner__subtitle"><?php echo esc_html( $subtitle ); ?></span>
    <?php endif; ?>

    <?php if ( $use_link ) {
      echo '<a class="btn btn-primary btn-lg shop-banner__btn" ' . $attributes . '>' . $a_title . '</a>';
    } ?>
  </div>

  <?php if ( !empty( $image ) ) : ?>
  <figure class="shop-banner__img">
    <img src="<?php echo esc_url( wp_get_attachment_url( $atts['image'] ) ); ?>" alt="">
  </figure>
  <?php endif; ?>

  <?php if ( $title ) : ?>
  <div class="shop-banner__bg-txt"><?php echo esc_html( $title ); ?></div>
  <?php endif; ?>

  <?php if ( $discount_txt || $discount_price ) : ?>
  <div class="shop-banner__discount">
    <span class="shop-banner__discount-txt"><?php echo esc_html( $discount_txt ); ?></span>
    <span class="shop-banner__discount-price"><?php echo esc_html( $discount_price ); ?></span>
  </div>
  <?php endif; ?>
</div>
<!-- Shop Banner / End -->
