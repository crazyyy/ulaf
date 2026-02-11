<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.1.0
 * @version   4.1.3
 *
 * Shortcode attributes
 * @var $atts
 * @var $items_per_page
 * @var $offset
 * @var $layout
 * @var $order
 * @var $order_by
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Sponsor_Cards
 */

$items_per_page = $offset = $layout = $order = $order_by = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = '';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

// Posts arguments
$sponsor_args = array(
	'post_type'      => 'sp_sponsor',
	'post_status'    => 'publish',
	'posts_per_page' => $items_per_page,
	'order'          => $order,
	'orderby'        => $order_by,
	'offset'         => $offset,
);

$sponsors_query = new WP_Query( $sponsor_args );

// Post Template
$thumb_size        = 'full';
$thumb_placeholder = 'sponsor-sample-gray';

// Check for Posts Layout
if ( $layout == 'grid_2cols' ) {

	$posts_classes_array = array(
		'col-6 col-sm-6'
	);

} elseif ( $layout == 'grid_4cols' ) {

	$posts_classes_array = array(
		'col-6 col-sm-3'
	);

} else {

	$posts_classes_array = array(
		'col-6 col-sm-4'
	);
}

$posts_classes = implode( " ", $posts_classes_array );
?>


<!-- Sponsor Cards -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php
	if ( $sponsors_query->have_posts() ) : ?>

		<div class="row">

			<?php /* Start the Loop */
			while ( $sponsors_query->have_posts() ) : $sponsors_query->the_post();

				$sponsor_url = get_post_meta( get_the_ID(), 'sp_url', true );
				?>

				<div class="<?php echo esc_attr( $posts_classes ); ?>">
					<div class="card sponsor-card">
						<header class="card__header sponsor-card__header">
							<figure class="sponsor-card__logo">
								<?php if ( $sponsor_url ) : ?>
									<a href="<?php echo esc_url( $sponsor_url ); ?>">
										<?php
										if ( has_post_thumbnail() ) {
											the_post_thumbnail( $thumb_size );
										} else {
											echo '<img src="' . get_theme_file_uri( '/assets/images/' . $thumb_placeholder . '.png' ) . '" alt="" />';
										}
										?>
									</a>
								<?php else : ?>
									<?php
									if ( has_post_thumbnail() ) {
										the_post_thumbnail( $thumb_size );
									} else {
										echo '<img src="' . get_theme_file_uri( '/assets/images/' . $thumb_placeholder . '.png' ) . '" alt="" />';
									}
									?>
								<?php endif; ?>
							</figure>
						</header>
						<div class="card__content sponsor-card__content">
							<div class="sponsor-card__excerpt">
								<?php the_content(); ?>
							</div>

							<?php if ( have_rows( 'sponsor_social_networks' ) ) : ?>
								<ul class="social-links sponsor-card__social">
									<?php while ( have_rows( 'sponsor_social_networks' ) ) : the_row(); ?>
										<li class="social-links__item">
											<a href="<?php echo esc_url( the_sub_field( 'sponsor_social_networks__link' ) ); ?>" class="social-links__link social-link-url" target="_blank"></a>
										</li>
									<?php endwhile; ?>
								</ul>
							<?php endif; ?>

						</div>
						<?php if ( $sponsor_url ) : ?>
						<footer class="card__footer sponsor-card__footer">
							<a href="<?php echo esc_url( $sponsor_url ); ?>" class="sponsor-card__link"><?php echo esc_html( alchemists_remove_protocol( $sponsor_url ) ); ?></a>
						</footer>
						<?php endif; ?>
					</div>
				</div>

			<?php endwhile; ?>

			<?php // Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

		</div><!-- .posts -->

	<?php else :

		get_template_part( 'template-parts/content', 'none' );

	endif; ?>


</div>
<!-- Sponsor Cards / End -->
