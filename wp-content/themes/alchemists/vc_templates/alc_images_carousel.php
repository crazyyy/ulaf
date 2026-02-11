<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $images
 * @var $img_size
 * @var $onclick
 * @var $custom_links
 * @var $custom_links_target
 * @var $autoplay
 * @var $autoplay_speed
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Images_Carousel
 */

$title = $images = $img_size = $onclick = $custom_links = $autoplay = $autoplay_speed = $custom_links_target = $el_class = $el_id = $css = $css_animation = '';

// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'awards-slider-' );

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if ( '' === $images ) {
	$images = '-1,-2,-3';
}

if ( 'custom_link' === $onclick ) {
	$custom_links = vc_value_from_safe( $custom_links );
	$custom_links = explode( ',', $custom_links );
}

$images = explode( ',', $images );
$i = - 1;

$class_to_filter = 'widget card widget-awards';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$award_item_class = 'awards__figure';

if ( alchemists_sp_preset( 'soccer' ) ) {
	$award_item_class = 'awards__figure awards__figure--space';
}

// Autoplay
if ( ! $autoplay ) {
	$autoplay = 'false';
} else {
	$autoplay = 'true';
}
?>

<!-- Widget: Awards -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) { ?>
		<div class="widget__title card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php } ?>

	<div class="widget__content card__content">
		<div id="<?php echo esc_attr( $identifier ); ?>" class="awards awards--slider">

			<?php foreach( $images as $attach_id ) : ?>
				<?php $i ++;
				if ( $attach_id > 0 ) {
					$post_thumbnail = wpb_getImageBySize( array(
						'attach_id'  => $attach_id,
						'thumb_size' => $img_size,
					) );
					$attachment_title = get_the_title( $attach_id );
					$attachment_date = get_the_excerpt( $attach_id );
				} else {
					$post_thumbnail = array();
					$post_thumbnail['thumbnail'] = '<img src="' . vc_asset_url( 'vc/no_image.png' ) . '" />';
					$post_thumbnail['p_img_large'][0] = vc_asset_url( 'vc/no_image.png' );
					$attachment_title = esc_html__( 'Image Title', 'alchemists' );
					$attachment_date = esc_html__( 'Image Caption', 'alchemists' );
				}
				$thumbnail = $post_thumbnail['thumbnail'];
				?>

				<div class="awards__item">
					<figure class="<?php echo esc_attr( $award_item_class ); ?>">
						<?php if ( 'custom_link' === $onclick && isset( $custom_links[ $i ] ) && '' !== $custom_links[ $i ] ) : ?>
							<a href="<?php echo esc_url( $custom_links[ $i ] ); ?>"<?php echo( ! empty( $custom_links_target ) ? ' target="' . esc_attr( $custom_links_target ) . '"' : '' ) ?>>
								<?php echo wp_kses_post( $thumbnail ); ?>
							</a>
						<?php else : ?>
							<?php echo wp_kses_post( $thumbnail ); ?>
						<?php endif; ?>
					</figure>
					<div class="awards__desc">
						<h5 class="awards__name"><?php echo esc_html( $attachment_title ); ?></h5>
						<div class="awards__date"><?php echo esc_html( $attachment_date ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>

		</div>

		<script type="text/javascript">
			(function($){
				$(function() {
					var awards_carousel = $('#<?php echo esc_js( $identifier ); ?>');

					awards_carousel.slick({
						slidesToShow: 1,
						dots: true,
						autoplay: <?php echo esc_js( $autoplay); ?>,
						autoplaySpeed: <?php echo esc_js( $autoplay_speed ); ?>,
						rows: 0,

						<?php if ( alchemists_sp_preset( 'football' ) ) : ?>
						arrows: false,
						/* Vertical slide temporary removed due to bug https://github.com/danfisher85/alchemists/issues/963
						vertical: true,
						verticalSwiping: true,
						*/
						<?php else : ?>
						arrows: true,
						rtl: <?php echo esc_js( $is_rtl ); ?>,
						responsive: [
							{
								breakpoint: 769,
								settings: {
									arrows: false
								}
							}
						]
						<?php endif; ?>

					});
				});
			})(jQuery);
		</script>

	</div>
</div>
<!-- Widget: Awards / End -->
