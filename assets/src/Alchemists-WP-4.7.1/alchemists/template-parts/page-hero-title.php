<?php
/**
 * Page Header - Title
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.2.1
 * @version   4.5.6
 */

$page_headings_classes = array();
$page_headings_classes[] = $page_heading_overlay;

if ( 2 == $page_title_layout ) {
	$page_headings_classes[] = 'page-heading--horizontal';
}

// Page Duotone
$page_heading_duotone       = get_field( 'page_heading_add_duotone_effect' );
$page_heading_duotone_color = get_field( 'page_heading_duotone_colors' );

// Duotone effect
if ( $page_heading_duotone ) {
	// Common
	$page_headings_classes[] = 'effect-duotone';

	// check if custom effect is selected
	if ( 'custom' != $page_heading_duotone_color ) {
		// use predefined colors
		$page_headings_classes[] = 'effect-duotone--' . $page_heading_duotone_color;
	} else {
		// add custom ones
		$page_headings_classes[] = 'effect-duotone--custom';

		$page_heading_duotone_color_1 = get_field( 'page_heading_duotone_color_1' );
		$page_heading_duotone_color_2 = get_field( 'page_heading_duotone_color_2' );

		echo '<style>';
			echo '.page-heading.effect-duotone--custom .effect-duotone__layer::after { background-color: ' . $page_heading_duotone_color_1 . '; }';
			echo '.page-heading.effect-duotone--custom .effect-duotone__layer-inner { background-color: ' . $page_heading_duotone_color_2 . '; }';
		echo '</style>';
	}

} elseif ( 1 == $page_title_duotone ) {

	// Page
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
<div class="page-heading <?php echo implode( ' ', $page_headings_classes ); ?>" <?php echo implode( ' ', $page_heading_styles_output ); ?>>
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
