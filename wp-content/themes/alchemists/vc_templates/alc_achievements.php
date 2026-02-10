<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $items
 * @var $autoplay
 * @var $autoplay_speed
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Achievements
 */

$title = $items = $autoplay = $autoplay_speed = $el_class = $el_id = $css = $css_animation = '';

// Create a unique identifier based on the current time in microseconds
$identifier        = uniqid( 'alc-achievements-carousel-' );
$identifier_header = uniqid( 'alc-achievements-carousel-header-' );

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget card card--no-paddings widget--sidebar widget-achievements';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}


// Autoplay
if ( ! $autoplay ) {
	$autoplay = 'false';
} else {
	$autoplay = 'true';
}

// RTL
$is_rtl = is_rtl() ? 'true' : 'false';
?>

<!-- Widget: Achievements -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) : ?>
	<div id="<?php echo esc_attr( $identifier_header ); ?>" class="widget__title card__header card__header--has-arrows">
		<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
	</div>
	<?php endif; ?>

	<div class="widget__content card__content">

		<div id="<?php echo esc_attr( $identifier ); ?>" class="alc-achievements js-alc-achievements" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "autoplay": <?php echo esc_js( $autoplay); ?>, "autoplaySpeed": <?php echo esc_js( $autoplay_speed ); ?>, "rows": 0, "rtl": <?php echo esc_js( $is_rtl ); ?>, "infinite": true, "appendArrows": "#<?php echo esc_attr( $identifier_header ); ?>"}'>

			<?php
			// Items
			$items = (array) vc_param_group_parse_atts( $items );
			foreach ( $items as $item ) :
			?>
				<div class="alc-achievements__item">
					<div class="alc-achievements__content">
						<div class="alc-achievements__icon alc-icon alc-icon--circle alc-icon--xl alc-icon--outline alc-icon--outline-xl">
							<svg role="img" class="df-icon df-icon--trophy">
							<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/esports/icons-esports.svg#trophy"/>
							</svg>
						</div>
						<h5 class="alc-achievements__event-title"><?php echo esc_html( $item['item_heading'] ); ?></h5>
						<div class="alc-achievements__event-date"><?php echo esc_html( $item['item_subheading'] ); ?></div>
					</div>
					<div class="alc-achievements__meta">
						<?php
						// Info
						$sub_items = (array) vc_param_group_parse_atts( $item['sub_items'] );
						foreach ( $sub_items as $sub_item ) :
						?>
							<div class="alc-achievements__meta-item">
								<h6 class="alc-achievements__meta-value"><?php echo esc_html( $sub_item['subitem_heading'] ); ?></h6>
								<div class="alc-achievements__meta-name"><?php echo esc_html( $sub_item['subitem_subheading'] ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

			<?php endforeach; ?>

		</div>

	</div>
</div>
<!-- Widget: Achievements / End -->

