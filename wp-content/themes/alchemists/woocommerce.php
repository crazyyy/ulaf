<?php
/**
 * The template for displaying Woocommerce pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

get_header();

$alchemists_data = get_option( 'alchemists_data' );
$page_heading_overlay  = isset( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) : '';
$breadcrumbs           = isset( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) ? esc_html( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) : '';
$page_title_on         = isset( $alchemists_data['alchemists__opt-page-title-display'] ) ? $alchemists_data['alchemists__opt-page-title-display'] : 1;
$page_title_tag        = isset( $alchemists_data['alchemists__opt-page-title-tag'] ) ? $alchemists_data['alchemists__opt-page-title-tag'] : 'h1';
$page_title_layout     = isset( $alchemists_data['alchemists__page-title-layout'] ) ? $alchemists_data['alchemists__page-title-layout'] : 1;
$page_title_duotone    = isset( $alchemists_data['alchemists__opt-page-title-duotone'] ) ? $alchemists_data['alchemists__opt-page-title-duotone'] : 1;
$page_duotone_color    = isset( $alchemists_data['alchemists__opt-page-title-duotone-color'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color'] : 'primary';
$page_duotone_color1   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-1'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-1'] : '';
$page_duotone_color2   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-2'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-2'] : '';

$post_id = get_option( 'woocommerce_shop_page_id', 0 );

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

$content_classes = array();

// Page Options
$page_heading                = get_field('page_heading', $post_id );
$page_content_top_padding    = get_field('page_content_top_padding', $post_id );
$page_content_bottom_padding = get_field('page_content_bottom_padding', $post_id );

// echo '<pre>' . var_export( $page_heading, true ) . '</pre>';

// Custom Page Heading Options
$page_heading_customize      = get_field('page_heading_customize', $post_id );
$page_heading_style          = array();
$page_heading_styles_output  = array();

if ( $page_heading_customize ) {
	// Page Heading Background Image
	$page_heading_custom_background_img = get_field('page_heading_custom_background_img', $post_id );

	if ( $page_heading_custom_background_img ) {
		// if background image selected display it
		$page_heading_style[] = 'background-image: url(' . $page_heading_custom_background_img . ');';
	} else {
		// if not, remove the default one
		$page_heading_style[] = 'background-image: none;';
	}

	// Page Heading Background Color
	$page_heading_custom_background_color = get_field('page_heading_custom_background_color', $post_id );
	if ( $page_heading_custom_background_color ) {
		$page_heading_style[] = 'background-color: ' . $page_heading_custom_background_color . ';';
	}

	// Overlay
	$page_heading_add_overlay_on = get_field('page_heading_add_overlay_on', $post_id );
	// hide pseudoelement if overlay disabled
	if ( empty( $page_heading_add_overlay_on ) ) {
		$page_heading_overlay = 'page-heading--no-bg';
	}

	$page_heading_custom_overlay_color = get_field('page_heading_custom_overlay_color', $post_id ) ? get_field('page_heading_custom_overlay_color', $post_id ) : 'transparent';
	$page_heading_custom_overlay_opacity = get_field( 'page_heading_custom_overlay_opacity', $post_id );
	$page_heading_remove_overlay_pattern = get_field( 'page_heading_remove_overlay_pattern', $post_id );

	if ( $page_heading_add_overlay_on ) {
		echo '<style>';
			echo '.page-heading::before {';
				echo 'background-color: ' . $page_heading_custom_overlay_color . ';';
				echo 'opacity: ' . $page_heading_custom_overlay_opacity / 100 . ';';
				if ( $page_heading_remove_overlay_pattern ) {
					echo 'background-image: none;';
				}
			echo '}';
		echo '</style>';
	}
}

// combine all custom inline properties into one string
if ( $page_heading_style ) {
	$page_heading_styles_output[] = 'style="' . implode( ' ', $page_heading_style ). '"';
}

// Page Content Options
$page_content_top_padding_class = '';
if ( $page_content_top_padding == 'none' ) {
	$content_classes[] = 'pt-0';
}

$page_content_bottom_padding_class = '';
if ( $page_content_bottom_padding == 'none' ) {
	$content_classes[] = 'pb-0';
}

// Shop Options
$shop_sidebar        = isset( $alchemists_data['alchemists__shop-sidebar'] ) ? esc_html( $alchemists_data['alchemists__shop-sidebar'] ) : 'left_sidebar';
$single_shop_sidebar = isset( $alchemists_data['alchemists__single-shop-sidebar'] ) ? esc_html( $alchemists_data['alchemists__single-shop-sidebar'] ) : 'no_sidebar';

// Left Sidebar
$content_class = 'col-lg-9 order-lg-2';
$sidebar_class = 'col-lg-3 order-lg-1';

if ( is_singular( 'product' ) ) {
	$shop_sidebar = $single_shop_sidebar;
}

if ( $shop_sidebar == 'right_sidebar' ) {
	// Right Sidebar
	$content_class = 'col-lg-9';
	$sidebar_class = 'col-lg-3';
} elseif ( $shop_sidebar == 'no_sidebar' ) {
	// No Sidebar (fullwidth)
	$content_class = 'col-lg-12';
}

// Get parameters
$shop_sidebar_get    = isset( $_GET['layout'] ) ? $_GET['layout'] : '';
if ( 'fullwidth' == $shop_sidebar_get ) {
	$shop_sidebar = 'no_sidebar';
	$content_class = 'col-lg-12';
}
?>


<?php if ( $page_heading == 'page_hero' ) { ?>

	<?php get_template_part( 'template-parts/page-hero-unit'); ?>

<?php } elseif ( $page_heading == 'page_default' || !$page_heading ) { ?>

	<!-- Page Heading
	================================================== -->
	<div class="page-heading <?php echo implode( ' ', $page_headings_classes ); ?>" <?php echo implode( ' ', $page_heading_styles_output ); ?>>
		<div class="container">
			<div class="row">

				<?php if ( 1 == $page_title_layout ) : ?>
					<div class="col-lg-10 offset-lg-1">
						<?php if ( $page_title_on ) : ?>
							<<?php echo esc_html( $page_title_tag ); ?> class="page-heading__title">
								<?php
								if ( is_product_category() ) {
									// display category name
									single_term_title();
								} else {
									echo get_the_title( $post_id );
								}
								?>
							</<?php echo esc_html( $page_title_tag ); ?>>
						<?php endif; ?>
						<?php
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
						<<?php echo esc_html( $page_title_tag ); ?> class="page-heading__title">
							<?php
							if ( is_product_category() ) {
								// display category name
								$current_cat = get_queried_object();
								echo wp_kses_post( $current_cat->name );
							} else {
								echo get_the_title( $post_id );
							}
							?>
						</<?php echo esc_html( $page_title_tag ); ?>>
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

<?php } ?>


<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content <?php echo implode( ' ', $content_classes ); ?>" id="content">
	<div class="container">
		<div class="row">

			<div id="primary" class="content-area <?php echo esc_attr( $content_class ); ?>">
				<main id="main" class="site-main">

				<?php if ( have_posts() ) :
					woocommerce_content();
				endif; ?>

				</main><!-- #main -->
			</div><!-- #primary -->

			<?php if ( $shop_sidebar != 'no_sidebar' ) : ?>
				<aside id="secondary" class="sidebar widget-area <?php echo esc_attr( $sidebar_class ); ?>">
					<?php dynamic_sidebar( 'alchemists-shop-sidebar' ); ?>
				</aside><!-- #secondary -->
			<?php endif; ?>

		</div>
	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
