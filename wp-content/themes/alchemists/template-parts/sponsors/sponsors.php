<?php
/**
 * The template for displaying the footer sponsors
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0 (patched for PHP 8.x)
 */

// Create a unique identifier
$sponsors_id = uniqid( 'alc-sponsors-logos-' );

// Theme options
$alchemists_data        = get_option( 'alchemists_data', array() );
$footer_sponsors        = ! empty( $alchemists_data['alchemists__footer-sponsors'] ) ? (int) $alchemists_data['alchemists__footer-sponsors'] : 0;
$footer_sponsors_layout = $alchemists_data['alchemists__footer-sponsors-layout'] ?? 'default';
$footer_sponsors_pos    = $alchemists_data['alchemists__footer-position'] ?? 'after_widgets';
$footer_sponsors_title  = $alchemists_data['alchemists__footer-sponsors-title'] ?? '';
$footer_sponsors_imgs   = $alchemists_data['alchemists__footer-sponsors-images'] ?? '';
$footer_sponsors_size   = $alchemists_data['alchemists__footer-sponsors-images-size'] ?? 'full';

// RTL check
$is_rtl = is_rtl() ? 'true' : 'false';

// Wrapper classes
$footer_sponsors_wrapper_classes = array(
	'sponsors',
	'row',
	'justify-content-md-center',
	'sponsors--' . esc_attr( $footer_sponsors_layout ),
);

if ( empty( $footer_sponsors_title ) ) {
	$footer_sponsors_wrapper_classes[] = 'sponsors--arrow-side';
}

$footer_sponsors_classes = array( 'sponsors-logos-wrapper' );

if ( ! empty( $footer_sponsors_title ) ) {
	$footer_sponsors_classes[] = 'col-md-8';
	$footer_sponsors_classes[] = 'mr-auto';
} else {
	$footer_sponsors_classes[] = 'col-md-12';
}

if ( in_array( $footer_sponsors_pos, array( 'before_widgets', 'after_header' ), true ) ) {
	$sponsors_pre   = '<div class="sponsors-wrapper">';
	$sponsors_after = '</div>';
} else {
	$sponsors_pre   = '';
	$sponsors_after = '';
}

$footer_sponsors_wrapper_classes = implode( ' ', $footer_sponsors_wrapper_classes );
$footer_sponsors_classes         = implode( ' ', $footer_sponsors_classes );

if ( $footer_sponsors === 1 ) :
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
			if ( ! empty( $footer_sponsors_imgs ) ) :
				$footer_sponsors_imgs_array = array_filter(
					array_map( 'absint', explode( ',', $footer_sponsors_imgs ) )
				);
				?>
				<div class="<?php echo esc_attr( $footer_sponsors_classes ); ?>">
					<div id="<?php echo esc_attr( $sponsors_id ); ?>" class="sponsors-logos">

						<?php foreach ( $footer_sponsors_imgs_array as $attachment_id ) :

							if ( ! get_post( $attachment_id ) ) {
								continue;
							}

							$sponsor_img_alt     = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
							$sponsor_img_link    = get_post_meta( $attachment_id, '_gallery_link_url', true );
							$sponsor_link_target = get_post_meta( $attachment_id, '_gallery_link_target', true );
							$sponsor_link_css    = get_post_meta( $attachment_id, '_gallery_link_additional_css_classes', true );

							$sponsor_img_attrs = wp_get_attachment_image_src( $attachment_id, $footer_sponsors_size );

							if ( ! is_array( $sponsor_img_attrs ) ) {
								continue;
							}

							$link_attrs = array();

							if ( $sponsor_img_link ) {
								$link_attrs[] = "href='" . esc_url( $sponsor_img_link ) . "'";
							}
							if ( $sponsor_link_target ) {
								$link_attrs[] = "target='" . esc_attr( $sponsor_link_target ) . "'";
							}
							if ( $sponsor_link_css ) {
								$link_attrs[] = "class='" . esc_attr( $sponsor_link_css ) . "'";
							}

							$link_attrs = implode( ' ', $link_attrs );
							?>
							<div class="sponsors__item">
								<?php if ( $sponsor_img_link ) : ?>
									<a <?php echo wp_kses_post( $link_attrs ); ?>>
										<img src="<?php echo esc_url( $sponsor_img_attrs[0] ); ?>"
										     width="<?php echo esc_attr( $sponsor_img_attrs[1] ); ?>"
										     height="<?php echo esc_attr( $sponsor_img_attrs[2] ); ?>"
										     alt="<?php echo esc_attr( $sponsor_img_alt ); ?>">
									</a>
								<?php else : ?>
									<img src="<?php echo esc_url( $sponsor_img_attrs[0] ); ?>"
									     width="<?php echo esc_attr( $sponsor_img_attrs[1] ); ?>"
									     height="<?php echo esc_attr( $sponsor_img_attrs[2] ); ?>"
									     alt="<?php echo esc_attr( $sponsor_img_alt ); ?>">
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

					</div>

					<?php if ( 'carousel' === $footer_sponsors_layout ) :
						$slides_to_show = (int) ( $alchemists_data['alchemists__footer-sponsors-slidestoshow'] ?? 6 );
						$autoplay       = ! empty( $alchemists_data['alchemists__footer-sponsors-autoplay'] );
						$autoplay_speed = (int) ( $alchemists_data['alchemists__footer-sponsors-autoplay-speed'] ?? 8 ) * 1000;
						$arrows         = ! empty( $alchemists_data['alchemists__footer-sponsors-arrows'] );
						?>
						<script>
							(function ($) {
								$(function () {
									$('#<?php echo esc_js( $sponsors_id ); ?>').slick({
										slidesToShow: <?php echo esc_js( $slides_to_show ); ?>,
										slidesToScroll: 1,
										autoplay: <?php echo esc_js( $autoplay ); ?>,
										autoplaySpeed: <?php echo esc_js( $autoplay_speed ); ?>,
										dots: false,
										<?php if ( $arrows ) : ?>
										appendArrows: $('.sponsors--carousel'),
										<?php else : ?>
										arrows: false,
										<?php endif; ?>
										rows: 0,
										rtl: <?php echo esc_js( $is_rtl ); ?>,
										responsive: [
											{ breakpoint: 992, settings: { arrows: false, slidesToShow: 4 } },
											{ breakpoint: 768, settings: { arrows: false, slidesToShow: 3 } },
											{ breakpoint: 480, settings: { arrows: false, slidesToShow: 2 } }
										]
									});
								});
							})(jQuery);
						</script>
					<?php endif; ?>

				</div>
			<?php endif; ?>

		</div>
	</div>
	<?php echo wp_kses_post( $sponsors_after ); ?>
	<!-- Sponsors / End -->
<?php endif; ?>