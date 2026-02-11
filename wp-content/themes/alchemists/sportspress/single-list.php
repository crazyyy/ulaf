<?php
/**
 * The template for displaying Single Team List
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

get_header();

$alchemists_data = get_option( 'alchemists_data' );
$page_heading_overlay  = isset( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) : '';
$breadcrumbs           = isset( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) : '';
$post_author_box       = isset( $alchemists_data['alchemists__opt-single-post-author'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-author'] ) : '';
$page_title_on         = isset( $alchemists_data['alchemists__opt-page-title-display'] ) ? $alchemists_data['alchemists__opt-page-title-display'] : 1;
$page_title_tag        = isset( $alchemists_data['alchemists__opt-page-title-tag'] ) ? $alchemists_data['alchemists__opt-page-title-tag'] : 'h1';
$page_title_layout     = isset( $alchemists_data['alchemists__page-title-layout'] ) ? $alchemists_data['alchemists__page-title-layout'] : 1;
$page_title_duotone    = isset( $alchemists_data['alchemists__opt-page-title-duotone'] ) ? $alchemists_data['alchemists__opt-page-title-duotone'] : 1;
$page_duotone_color    = isset( $alchemists_data['alchemists__opt-page-title-duotone-color'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color'] : 'primary';
$page_duotone_color1   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-1'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-1'] : '';
$page_duotone_color2   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-2'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-2'] : '';

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
					<?php
					if ( $page_title_on ) {
						the_title( "<$page_title_tag class='page-heading__title'>", "</$page_title_tag>" );
					}
					// Breadcrumb
					if ( function_exists( 'breadcrumb_trail' ) && $breadcrumbs != 0 ) {
						breadcrumb_trail( array(
							'show_browse' => false,
						));
					}
					?>
				</div>

			<?php else : ?>

				<?php if ( $page_title_on ) : ?>
					<div class="col align-self-start">
						<?php the_title( "<$page_title_tag class='page-heading__title'>", "</$page_title_tag>" ); ?>
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
		<div class="row">

			<div id="primary" class="content-area col-lg-12">

				<?php
				while ( have_posts() ) : the_post();

					the_content();

				endwhile; // End of the loop.
				?>

			</div><!-- #primary -->

		</div>
	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php get_footer();
