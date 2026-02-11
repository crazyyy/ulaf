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
 * @var $description
 * @var $image
 * @var $link
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Jumbotron
 */

$title = $custom_title_markup_is_active = $custom_title = $subtitle = $description = $image = $link = $el_class = $values = $el_id = $css = $css_animation = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$jumbo_classes = array(
	'jumbotron',
	'jumbotron--style1',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
);

$class_to_filter = implode( ' ', array_filter( $jumbo_classes ) );
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

$attributes = implode( ' ', $attributes );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}
?>

<!-- Jumbotron -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>
	<div class="container">
		<div class="row">
			<div class="col align-self-end">
				<?php if ( ! empty( $image ) ) : ?>
				<figure class="jumbotron__img">
					<img src="<?php echo esc_url( wp_get_attachment_url( $atts['image'] ) ); ?>" alt="">
				</figure>
				<?php endif; ?>
			</div>
			<div class="col align-self-center">
				<div class="jumbotron__content text-center">

					<?php if ( $subtitle ) : ?>
						<h5 class="jumbotron__subtitle">
							<?php echo esc_html( $subtitle ); ?>
						</h5>
					<?php endif; ?>

					<?php if ( $custom_title_markup_is_active ) : ?>
						<h1 class="jumbotron__title">
							<?php echo wp_kses_post( rawurldecode( $custom_title ) ); ?>
						</h1>
					<?php else : ?>
						<?php if ( $title ) : ?>
							<h1 class="jumbotron__title">
								<?php echo wp_kses_post( $title ); ?>
							</h1>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( $description ) : ?>
						<p class="jumbotron__desc">
						<?php echo esc_html( $description ); ?>
						</p>
					<?php endif; ?>

				</div>
			</div>
		</div>
	</div>

	<?php
	if ( $use_link ) {
		echo '<a class="stretched-link" ' . $attributes . '></a>';
	}
	?>
</div>
<!-- Jumbotron / End -->
