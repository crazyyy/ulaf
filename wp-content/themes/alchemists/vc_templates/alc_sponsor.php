<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.1.0
 * @version   4.1.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $social_links
 * @var $image
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Sponsor
 */

$social_links = $image = $el_class = $el_id = $css = $css_animation = '';

// Create a unique identifier based on the current time in microseconds
$identifier        = uniqid( 'alc-achievements-carousel-' );
$identifier_header = uniqid( 'alc-achievements-carousel-header-' );

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'card sponsor-card';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

// parse link
$attributes = array();
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
$attributes = implode( ' ', $attributes );
?>

<!-- Widget: Achievements -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php
	if ( !empty( $image ) ) :
		$img_alt = get_post_meta( $image, '_wp_attachment_image_alt', true );
		?>
		<header class="card__header sponsor-card__header">
			<figure class="sponsor-card__logo">
				<?php if ( $use_link ) : ?>
					<a href="<?php echo esc_js( $a_href ); ?>"><img src="<?php echo esc_url( wp_get_attachment_url( $image ) ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>"></a>
				<?php else : ?>
					<img src="<?php echo esc_url( wp_get_attachment_url( $image ) ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">
				<?php endif; ?>
			</figure>
		</header>
	<?php endif; ?>

	<div class="card__content sponsor-card__content">
		<?php if ( $content ) : ?>
			<div class="sponsor-card__excerpt">
				<?php echo wpb_js_remove_wpautop( $content, true ); ?>
			</div>
		<?php endif; ?>
		<ul class="social-links sponsor-card__social">
			<?php
			// Items
			$social_links = (array) vc_param_group_parse_atts( $social_links );
			foreach ( $social_links as $social_link ) :
				?>
				<li class="social-links__item">
					<a href="<?php echo esc_url( $social_link['item_social_link'] ); ?>" class="social-links__link social-link-url" title="<?php echo esc_attr( $social_link['item_social_title']); ?>"></a>
				</li>
				<?php
			endforeach;
			?>
		</ul>
	</div>
	<footer class="card__footer sponsor-card__footer">
		<?php
		if ( $use_link ) {
			echo '<a ' . $attributes . ' class="sponsor-card__link">' . $a_title . '</a>';
		}
		?>
	</footer>
</div>
<!-- Widget: Achievements / End -->

