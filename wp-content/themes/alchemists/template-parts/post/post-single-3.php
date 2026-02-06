<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$post_title_tag    = isset( $alchemists_data['alchemists__opt-single-post-title-tag'] ) ? $alchemists_data['alchemists__opt-single-post-title-tag'] : 'h1';

$post_author_box   = isset( $alchemists_data['alchemists__opt-single-post-author'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-author'] ) : 1;
$post_layout_get   = isset( $_GET['single_post'] ) ? $_GET['single_post'] : '';
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_nav          = isset( $alchemists_data['alchemists__opt-single-post-navigation'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-navigation'] ) : 1;
$post_nav_layout   = isset( $alchemists_data['alchemists__opt-single-post-navigation-type'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-navigation-type'] ) : 'default';
$post_show_img     = isset( $alchemists_data['alchemists__opt-single-post-featured-img'] ) ? $alchemists_data['alchemists__opt-single-post-featured-img'] : 1;

// Overlay
$post_overlay              = isset( $alchemists_data['alchemists__hero-single-post--overlay'] ) ? $alchemists_data['alchemists__hero-single-post--overlay'] : 'simple';
$post_overlay_custom       = isset( $alchemists_data['alchemists__hero-single-post--overlay-color'] ) ? $alchemists_data['alchemists__hero-single-post--overlay-color'] : 0;
$post_overlay_custom_color = isset( $alchemists_data['alchemists__hero-single-post--overlay-color-customize'] ) ? $alchemists_data['alchemists__hero-single-post--overlay-color-customize'] : '';

// get post category class
$post_class = alchemists_post_category_class();

// Post Navigation
if ( $post_nav_layout == 'card-sm' ) {
	$post_nav_layout = 'navigation';
} else if ( $post_nav_layout == 'card-n' ) {
	$post_nav_layout = 'navigation-2';
} else {
	$post_nav_layout = 'navigation';
}

// Page Heading Classes
$page_heading_classes = array(
	'page-heading',
	'page-heading--post-3',
);

if ( 'duotone' == $post_overlay ) {
	array_push( $page_heading_classes, 'page-heading--duotone', 'effect-duotone', $post_class );
} elseif ( 'simple' == $post_overlay ) {
	$page_heading_classes[] = 'page-heading--overlay';
} else {
	$page_heading_classes[] = 'page-heading--no-bg';
}

// post thumbnail
$header_post = '';
if ( has_post_thumbnail() && $post_show_img ) {
	$thumb_id = get_post_thumbnail_id();
	$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );

	$header_post = 'style="background-image:url(' . $thumb_url[0] . ')"';
}

// Simple Post Overlay color
if ( $post_overlay_custom ) {
	echo '<style>';
		echo '.page-heading--overlay::before { opacity: ' . $post_overlay_custom_color['alpha']. '; background-color:' . $post_overlay_custom_color['color'] . ';}';
	echo '</style>';
}
?>


<!-- Page Heading
================================================== -->
<div class="<?php echo esc_attr( implode(' ', $page_heading_classes ) ); ?>" <?php echo wp_kses_post( $header_post ); ?>>
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-md-2">
				<!-- Post Meta - Top -->
				<div class="post__meta-block post__meta-block--top">

					<?php
					if ( $categories_toggle ) {
						alchemists_post_category_labels( 'post__category' );
					}
					?>

					<!-- Post Title -->
					<?php the_title( "<$post_title_tag class='page-heading__title'>", "</$post_title_tag>" ); ?>
					<!-- Post Title / End -->

					<!-- Post Meta Info -->
					<?php alchemists_entry_meta_single(); ?>
					<!-- Post Meta Info / End -->

					<!-- Post Author -->
					<div class="post-author">
						<figure class="post-author__avatar">
							<?php echo get_avatar( get_the_author_meta('email'), '40' ); ?>
						</figure>
						<div class="post-author__info">
							<h4 class="post-author__name"><?php the_author(); ?></h4>
							<span class="post-author__slogan"><?php echo get_the_author_meta('nickname'); ?></span>
						</div>
					</div>
					<!-- Post Author / End -->

				</div>
				<!-- Post Meta - Top / End -->
			</div>
		</div>
	</div>
</div>

<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content" id="content">
	<div class="container">
		<div class="row">

			<div id="primary" class="content-area col-lg-8 offset-md-2">

				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/content', 'single-3' );

					// Post Social Sharing
					if ( function_exists( 'alc_post_social_share_buttons' ) ) {
						alc_post_social_share_buttons();
					}

					if ( $post_layout_get != '3' ) {

						if ( $post_author_box != 0 ) {
							// Post Author
							get_template_part( 'template-parts/post/post', 'author' );
						}
					}

					if ( $post_nav != 0 ) {
						// Post Navigation
						get_template_part( 'template-parts/post/post', $post_nav_layout );
					}


					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>

			</div><!-- #primary -->

		</div>
	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>
