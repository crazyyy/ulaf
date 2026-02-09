<?php
/**
 * The template for displaying archive pages
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

$posts_layout_get      = isset( $_GET['posts_layout'] ) ? sanitize_key( $_GET['posts_layout'] ) : '';
$sidebar_position      = isset( $alchemists_data['alchemists__blog-sidebar'] ) ? esc_html( $alchemists_data['alchemists__blog-sidebar'] ) : '1';
$posts_layout          = isset( $alchemists_data['alchemists__blog-posts-style'] ) ? esc_html( $alchemists_data['alchemists__blog-posts-style'] ) : '2';

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

// Content
$content_width = 'col-lg-8';

// Sidebar
$sidebar_width = 'col-lg-4';

// Post Template
$post_template = '';

// Check for Posts Layout
if ( $posts_layout_get == '1' || $posts_layout == '1' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'post-grid',
		'post-grid--2cols',
		'row',
	);
	$post_template = 'blog-1';

} elseif ( $posts_layout_get == '3' || $posts_layout == '3' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'posts--cards-thumb-lg',
		'post-list',
	);
	$post_template = 'blog-3';

} elseif ( $posts_layout_get == '4' || $posts_layout == '4' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'post-grid',
		'post-grid--masonry',
		'row',
	);
	$post_template = 'blog-4';

	if ( $posts_layout == '4' ) {
		$content_width = 'col-lg-8';
	}

} elseif ( $posts_layout_get == 5 || $posts_layout == 5 ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'post-grid',
		'row',
	);
	$post_template = 'blog-5';

	if ( $posts_layout == 5 ) {
		$content_width = 'col-lg-8';
	}

} elseif ( $posts_layout_get == 6 || $posts_layout == 6 ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'post-grid',
		'row',
	);
	$post_template = 'blog-6';

} elseif ( $posts_layout_get == 7 || $posts_layout == 7 ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'post-grid',
	);
	$post_template = 'blog-7';

} elseif ( $posts_layout_get == 8 || $posts_layout == 8 ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'post-grid',
		'post-grid--masonry',
		'row',
	);
	$post_template = 'blog-8';

} else {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'posts--cards-thumb-left',
		'post-list',
	);
}

// Sidebar Position
if ( $sidebar_position == '2' ) {
	$content_width = 'col-lg-8 order-lg-2';
	$sidebar_width = 'col-lg-4 order-lg-1';
} elseif ( $sidebar_position == '3' ) {
	$content_width = 'col-lg-12';
}

if ( $posts_layout_get == 4 || $posts_layout_get == 8 ) {
	$content_width = 'col-lg-12';
}

$posts_classes = implode( " ", $posts_classes_array );

// show sidebar
$is_sidebar = true;
if ( $posts_layout_get == 4 || $posts_layout_get == 8 ) {
	$is_sidebar = false;
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
						the_archive_title( "<$page_title_tag class='page-heading__title'>", "</$page_title_tag>" );
					}
					// Breadcrumb
					if ( function_exists( 'breadcrumb_trail' ) && $breadcrumbs != 0 ) {
						breadcrumb_trail( array(
							'show_browse' => false,
							// 'show_title'  => false
						));
					}?>

					<?php if ( is_category() || is_tag() ) : ?>
					<div class="page-header__desc">
						<div class="row">
							<div class="col-lg-10 offset-lg-1">
								<?php
								if ( is_category( ) ) :
									echo category_description();
								elseif ( is_tag() ) :
									echo tag_description();
								endif;
								?>
							</div>
						</div>
					</div>
					<?php endif; ?>

				</div>
			<?php else : ?>

				<div class="col align-self-start">
					<?php
					if ( $page_title_on ) {
						the_archive_title( "<$page_title_tag class='page-heading__title'>", "</$page_title_tag>" );
					}
					?>
					<?php if ( is_category() || is_tag() ) : ?>
						<div class="page-header__desc">
							<?php
							if ( is_category( ) ) :
								echo category_description();
							elseif ( is_tag() ) :
								echo tag_description();
							endif;
							?>
						</div>
					<?php endif; ?>
				</div>


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

			<div id="primary" class="content-area <?php echo esc_attr( $content_width ); ?>">
				<main id="main" class="site-main">

				<?php if ( have_posts() ) : ?>

					<div class="<?php echo esc_attr( $posts_classes ); ?>">

					<?php /* Start the Loop */
					while ( have_posts() ) : the_post();

						if ( 'sp_event' === get_post_type() ) {
							sp_get_template( 'event-logos.php' );
						} else {
							get_template_part( 'template-parts/content', $post_template );
						}

					endwhile; ?>

					</div><!-- .posts -->

					<?php alchemists_pagination(); ?>

				<?php else :

					get_template_part( 'template-parts/content', 'none' );

				endif; ?>

				</main><!-- #main -->
			</div><!-- #primary -->


			<?php
			if ( $is_sidebar ) :
				if ( $sidebar_position != 3 ) :
				?>
				<aside id="secondary" class="sidebar widget-area <?php echo esc_attr( $sidebar_width ); ?>">
					<?php get_sidebar(); ?>
				</aside><!-- #secondary -->
				<?php
				endif;
			endif;
			?>

		</div>
	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
