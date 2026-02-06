<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

get_header();

$alchemists_data = get_option( 'alchemists_data' );
$page_heading_overlay      = isset( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) : '';
$breadcrumbs               = isset( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) : '';
$page_title_on             = isset( $alchemists_data['alchemists__opt-page-title-display'] ) ? $alchemists_data['alchemists__opt-page-title-display'] : 1;
$page_title_tag            = isset( $alchemists_data['alchemists__opt-page-title-tag'] ) ? $alchemists_data['alchemists__opt-page-title-tag'] : 'h1';
$page_title_layout         = isset( $alchemists_data['alchemists__page-title-layout'] ) ? $alchemists_data['alchemists__page-title-layout'] : 1;
$page_title_duotone        = isset( $alchemists_data['alchemists__opt-page-title-duotone'] ) ? $alchemists_data['alchemists__opt-page-title-duotone'] : 1;
$page_duotone_color        = isset( $alchemists_data['alchemists__opt-page-title-duotone-color'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color'] : 'primary';
$page_duotone_color1       = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-1'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-1'] : '';
$page_duotone_color2       = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-2'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-2'] : '';

$error_image               = isset( $alchemists_data['alchemists__opt-404-image']['url'] ) ? esc_html( $alchemists_data['alchemists__opt-404-image']['url'] ) : '';
$error_page_heading        = isset( $alchemists_data['alchemists__opt-404-page-heading'] ) ? esc_html( $alchemists_data['alchemists__opt-404-page-heading'] ) : '';
$error_title               = isset( $alchemists_data['alchemists__opt-404-page-title'] ) ? esc_html( $alchemists_data['alchemists__opt-404-page-title'] ) : '';
$error_subtitle            = isset( $alchemists_data['alchemists__opt-404-page-subtitle'] ) ? esc_html( $alchemists_data['alchemists__opt-404-page-subtitle'] ) : '';
$error_desc                = isset( $alchemists_data['alchemists__opt-404-desc'] ) ? $alchemists_data['alchemists__opt-404-desc']: '';
$error_btn_primary         = isset( $alchemists_data['alchemists__opt-404-btn'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn'] ) : '';
$error_btn_primary_txt     = isset( $alchemists_data['alchemists__opt-404-btn-txt'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn-txt'] ) : '';
$error_btn_primary_link    = isset( $alchemists_data['alchemists__opt-404-btn-link'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn-link'] ) : '';
$error_btn_secondary       = isset( $alchemists_data['alchemists__opt-404-btn-secondary'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn-secondary'] ) : '';
$error_btn_secondary_txt   = isset( $alchemists_data['alchemists__opt-404-btn-secondary-txt'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn-secondary-txt'] ) : '';
$error_btn_secondary_link  = isset( $alchemists_data['alchemists__opt-404-btn-secondary-link'] ) ? esc_html( $alchemists_data['alchemists__opt-404-btn-secondary-link'] ) : '';

if ( $page_heading_overlay == 0 ) {
	$page_heading_overlay = 'page-heading--no-bg';
} else {
	$page_heading_overlay = 'page-heading--has-bg';
}

$page_headings_classes = array();
$page_headings_classes[] = $page_heading_overlay;

// Title layout
if ( 2 == $page_title_layout ) {
	$page_headings_classes[] = 'page-heading--horizontal';
}

// Duotone effect
if ( 1 == $page_title_duotone ) {
	$page_headings_classes[] = 'effect-duotone';

	// check if custom effect is selected
	if ( 'custom' != $page_duotone_color ) {
		// use predefined colors
		$page_headings_classes[] = 'effect-duotone--' . $page_duotone_color;
	} else {
		// add custom ones
		$page_headings_classes[] = 'effect-duotone--custom';
	}
}
?>

<!-- Page Heading
================================================== -->
<div class="page-heading <?php echo implode( ' ', $page_headings_classes ); ?>">
	<div class="container">
		<div class="row">

		<?php if ( 1 == $page_title_layout ) : ?>
			<div class="col-lg-10 offset-lg-1">

				<?php if ( $page_title_on ) : ?>
					<<?php echo esc_attr( $page_title_tag ); ?> class="page-heading__title">
						<?php
						if ( !empty( $error_page_heading ) ) {
							echo esc_html( $error_page_heading );
						} else {
							esc_html_e( '404 Error', 'alchemists' );
						}
						?>
					</<?php echo esc_attr( $page_title_tag ); ?>>
				<?php endif; ?>

				<?php
				// Breadcrumb
				if ( function_exists( 'breadcrumb_trail' ) && $breadcrumbs != 0 ) {
					breadcrumb_trail( array(
						'show_browse' => false,
						// 'show_title'  => false
					));
				}
				?>
			</div>
		<?php else : ?>

			<?php if ( $page_title_on ) : ?>
				<div class="col align-self-start">
					<<?php echo esc_attr( $page_title_tag ); ?> class="page-heading__title">
						<?php
						if ( !empty( $error_page_heading ) ) {
							echo esc_html( $error_page_heading );
						} else {
							esc_html_e( '404 Error', 'alchemists' );
						}
						?>
					</<?php echo esc_attr( $page_title_tag ); ?>>
				</div>
			<?php endif; ?>

			<?php if ( function_exists( 'breadcrumb_trail' ) && $breadcrumbs != 0 ) : ?>
				<div class="col align-self-end">
					<?php
					// Breadcrumb
					breadcrumb_trail( array(
						'show_browse' => false,
					));
					?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		</div>
	</div>
</div>

<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content" id="content">
	<div class="container">

		<!-- Error 404 -->
		<div class="error-404">
			<div class="row">
				<div class="col-lg-8 offset-md-2">
					<?php if ( !empty( $error_image ) ) { ?>
						<figure class="error-404__figure">
	          	<img src="<?php echo esc_url( $error_image ); ?>" alt="">
						</figure>
	        <?php } else { ?>
						<figure class="error-404__figure error-404__figure--cross">
	          	<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icon-ghost.svg" alt="<?php esc_attr_e( '404 Error Image', 'alchemists' ); ?>">
						</figure>
	        <?php } ?>
					<header class="error__header">
						<h2 class="error__title">
							<?php if ( !empty( $error_title ) ) { ?>
			          <?php echo esc_html( $error_title ); ?>
			        <?php } else { ?>
			          <?php esc_html_e( 'OOOOPS! Page not Found', 'alchemists' ); ?>
			        <?php } ?>
						</h2>
						<h3 class="error__subtitle">
							<?php if ( !empty( $error_subtitle ) ) { ?>
			          <?php echo esc_html( $error_subtitle ); ?>
			        <?php } else { ?>
			          <?php esc_html_e( 'Seems that we have a problem!', 'alchemists'); ?>
			        <?php } ?>
						</h3>
					</header>
					<div class="error__description">
						<?php if ( !empty( $error_desc ) ) { ?>
							<?php echo wp_kses_post( $error_desc ); ?>
						<?php } else { ?>
							<?php echo wp_kses_post( __( 'The page you are looking for has been moved or doesn\'t exist anymore, if you like you can return to our homepage. If the problem persists, please send us an email to <a href="mailto:info@alchemists.com">info@alchemists.com</a>', 'alchemists' ) ); ?>
						<?php } ?>

					</div>
					<footer class="error__cta">

						<?php if ( $error_btn_primary != 0 ) : ?>
						<a href="<?php if ( !empty( $error_btn_primary_link) ) { echo esc_url( $error_btn_primary_link ); } else { echo esc_url( home_url( '/' ) ); } ?>" class="btn btn-primary">
							<?php if ( !empty( $error_btn_primary_txt ) ) {
								echo esc_html( $error_btn_primary_txt );
							} else {
								esc_html_e( 'Return to Home', 'alchemists' );
							} ?>
						</a>
						<?php endif; ?>

						<?php if ( $error_btn_secondary != 0 ) : ?>
						<a href="<?php if ( !empty( $error_btn_secondary_link) ) { echo esc_url( $error_btn_secondary_link ); } else { echo get_permalink( get_option( 'page_for_posts' ) ); } ?>" class="btn btn-primary-inverse">
							<?php if ( !empty( $error_btn_secondary_txt ) ) {
								echo esc_html( $error_btn_secondary_txt );
							} else {
								esc_html_e( 'Keep Browsing', 'alchemists' );
							} ?>
						</a>
						<?php endif; ?>

					</footer>
				</div>
			</div>
		</div>
		<!-- Error 404 / End -->

	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
