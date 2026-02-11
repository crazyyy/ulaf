<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

// Create a unique identifier
$sponsors_id = uniqid( 'alc-sponsors-logos-' );

$alchemists_data        = get_option( 'alchemists_data' );
$footer_sponsors        = isset( $alchemists_data['alchemists__footer-sponsors'] ) ? $alchemists_data['alchemists__footer-sponsors'] : 0;
$footer_sponsors_layout = isset( $alchemists_data['alchemists__footer-sponsors-layout'] ) ? $alchemists_data['alchemists__footer-sponsors-layout'] : 'default';
$footer_sponsors_pos    = isset( $alchemists_data['alchemists__footer-position'] ) ? $alchemists_data['alchemists__footer-position'] : 'after_widgets';
$footer_sponsors_title  = isset( $alchemists_data['alchemists__footer-sponsors-title'] ) ? $alchemists_data['alchemists__footer-sponsors-title'] : '';
$footer_sponsors_imgs   = isset( $alchemists_data['alchemists__footer-sponsors-images'] ) ? $alchemists_data['alchemists__footer-sponsors-images'] : '';
$footer_sponsors_size   = isset( $alchemists_data['alchemists__footer-sponsors-images-size'] ) ? $alchemists_data['alchemists__footer-sponsors-images-size'] : 'full';

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

$footer_sponsors_wrapper_classes = array(
	'sponsors',
	'row',
	'justify-content-md-center',
	'sponsors--' . $footer_sponsors_layout,
);

if ( empty( $footer_sponsors_title ) ) {
	$footer_sponsors_wrapper_classes[] = 'sponsors--arrow-side';
}

$footer_sponsors_classes = array(
	'sponsors-logos-wrapper'
);

if ( ! empty( $footer_sponsors_title ) ) {
	array_push( $footer_sponsors_classes, 'col-md-8', 'mr-auto' );
} else {
	$footer_sponsors_classes[] = 'col-md-12';
}

if ( 'before_widgets' == $footer_sponsors_pos || 'after_header' == $footer_sponsors_pos ) {
	$sponsors_pre   = '<div class="sponsors-wrapper">';
	$sponsors_after = '</div>';
} else {
	$sponsors_pre   = '';
	$sponsors_after = '';
}

$footer_sponsors_classes = implode( ' ', $footer_sponsors_classes );
$footer_sponsors_wrapper_classes = implode( ' ', $footer_sponsors_wrapper_classes );

if ( $footer_sponsors == 1 ) :
	?>
	<!-- Sponsors -->
	<?php echo wp_kses_post( $sponsors_pre ); ?>
		<div class="sponsors-container container">
			<div class="<?php echo esc_attr( $footer_sponsors_wrapper_classes ); ?>">

				<?php if ( ! empty( $footer_sponsors_title ) ) : ?>
					<div class="col-md-2 ml-auto">
						<h6 class="sponsors-title"><?php echo esc_html( $footer_sponsors_title ); ?></h6>
					</div>
				<?php endif; ?>

				<?php
				if ( !empty( $footer_sponsors_imgs ) ) :
					$footer_sponsors_imgs_array = explode( ',', $footer_sponsors_imgs );
					?>
					<div class="<?php echo esc_attr( $footer_sponsors_classes ); ?>">

						<div id="<?php echo esc_attr( $sponsors_id ); ?>" class="sponsors-logos">
							<?php
							foreach ( $footer_sponsors_imgs_array as $footer_sponsors_img ) :
								$sponsor_img_alt     = get_post_meta( $footer_sponsors_img, '_wp_attachment_image_alt', true);
								$sponsor_img_link    = get_post_meta( $footer_sponsors_img, '_gallery_link_url', true );
								$sponsor_link_target = get_post_meta( $footer_sponsors_img, '_gallery_link_target', true );
								$sponsor_link_css    = get_post_meta( $footer_sponsors_img, '_gallery_link_additional_css_classes', true );

								// Get image attributes
								$sponsor_img_attrs = wp_get_attachment_image_src( $footer_sponsors_img, $footer_sponsors_size );

								// Skip if image doesn't exist
								if ( ! $sponsor_img_attrs || ! is_array( $sponsor_img_attrs ) ) {
									continue;
								}

								$sponsor_link_attr = array();

								if ( ! empty( $sponsor_img_link ) ) {
									$sponsor_link_attr[] = "href='" . esc_url( $sponsor_img_link ) . "'";
								}

								if ( ! empty( $sponsor_link_target ) ) {
									$sponsor_link_attr[] = "target='" . esc_attr( $sponsor_link_target ) . "'";
								}

								if ( ! empty( $sponsor_link_css ) ) {
									$sponsor_link_attr[] = "class='" . esc_attr( $sponsor_link_css ) . "'";
								}

								$sponsor_link_attr = implode( ' ', $sponsor_link_attr );
								?>
								<div class="sponsors__item">
									<?php if ( ! empty( $sponsor_img_link ) ) : ?>
										<a <?php echo wp_kses_post( $sponsor_link_attr ); ?>>
											<img src="<?php echo esc_url( $sponsor_img_attrs[0] ); ?>" width="<?php echo esc_attr( $sponsor_img_attrs[1] ); ?>" height="<?php echo esc_attr( $sponsor_img_attrs[2] ); ?>" alt="<?php echo esc_attr( $sponsor_img_alt ); ?>">
										</a>
									<?php else : ?>
										<img src="<?php echo esc_url( $sponsor_img_attrs[0] ); ?>" width="<?php echo esc_attr( $sponsor_img_attrs[1] ); ?>" height="<?php echo esc_attr( $sponsor_img_attrs[2] ); ?>" alt="<?php echo esc_attr( $sponsor_img_alt ); ?>">
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>

						<?php
						if ( 'carousel' == $footer_sponsors_layout ) :
							$footer_sponsors_slides_to_show = isset( $alchemists_data['alchemists__footer-sponsors-slidestoshow'] ) ? $alchemists_data['alchemists__footer-sponsors-slidestoshow'] : 6;
							$footer_sponsors_autoplay       = isset( $alchemists_data['alchemists__footer-sponsors-autoplay'] ) ? $alchemists_data['alchemists__footer-sponsors-autoplay'] : true;
							$footer_sponsors_autoplay_speed = isset( $alchemists_data['alchemists__footer-sponsors-autoplay-speed'] ) ? $alchemists_data['alchemists__footer-sponsors-autoplay-speed'] : 8;
							$footer_sponsors_autoplay_speed = $footer_sponsors_autoplay_speed * 1000;
							$footer_sponsors_arrows         = isset( $alchemists_data['alchemists__footer-sponsors-arrows'] ) ? $alchemists_data['alchemists__footer-sponsors-arrows'] : true;
							?>
							<script>
								(function($){
									$(function() {
										var sponsors_logos = $('#<?php echo esc_js( $sponsors_id ); ?>');

										sponsors_logos.slick({
											slidesToShow: <?php echo esc_js( $footer_sponsors_slides_to_show ); ?>,
											slidesToScroll: 1,
											autoplay: <?php echo esc_js( $footer_sponsors_autoplay ); ?>,
											autoplaySpeed: <?php echo esc_js( $footer_sponsors_autoplay_speed ); ?>,
											dots: false,
											<?php if ( $footer_sponsors_arrows ) : ?>
												appendArrows: $('.sponsors--carousel'),
											<?php else : ?>
												arrows: false,
											<?php endif; ?>
											rows: 0,
											rtl: <?php echo esc_js( $is_rtl ); ?>,
											responsive: [
												{
													breakpoint: 992,
													settings: {
														arrows: false,
														slidesToShow: 4
													}
												},
												{
													breakpoint: 768,
													settings: {
														arrows: false,
														slidesToShow: 3
													}
												},
												{
													breakpoint: 480,
													settings: {
														arrows: false,
														slidesToShow: 2
													}
												}
											]
										});

									});
								})(jQuery);
							</script>
						<?php endif; ?>

					</div>
					<?php
				endif;
				?>

			</div>
		</div>
	<?php echo wp_kses_post( $sponsors_after ); ?>
	<!-- Sponsors / End -->
	<?php
endif;
