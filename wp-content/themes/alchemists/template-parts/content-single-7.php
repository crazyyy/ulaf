<?php
/**
 * Template part for displaying posts on a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$post_title_tag    = isset( $alchemists_data['alchemists__opt-single-post-title-tag'] ) ? $alchemists_data['alchemists__opt-single-post-title-tag'] : 'h1';
$post_author       = isset( $alchemists_data['alchemists__opt-single-post-author'] ) ? $alchemists_data['alchemists__opt-single-post-author'] : 1;
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_show_img     = isset( $alchemists_data['alchemists__opt-single-post-featured-img'] ) ? $alchemists_data['alchemists__opt-single-post-featured-img'] : 1;
$post_tags         = isset( $alchemists_data['alchemists__opt-single-post-tags'] ) ? $alchemists_data['alchemists__opt-single-post-tags'] : 1;
$post_tags_type    = isset( $alchemists_data['alchemists__opt-single-post-tags-type'] ) ? $alchemists_data['alchemists__opt-single-post-tags-type'] : 'buttons';

$post_classes = array(
	'post',
	'post--single',
	'post--style-7',
);
?>

<!-- Article -->
<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

	<?php
	// Categories
	if ( $categories_toggle ) {
		alchemists_post_category_labels( 'post__category' );
	}
	?>

	<header class="post__header">
		<?php the_title( "<$post_title_tag class='post__title'>", "</$post_title_tag>" ); ?>
		<?php alchemists_entry_meta_single(); ?>
	</header>

	<div class="post__content-wrapper">

		<?php
		// Post Sharing
		if ( function_exists( 'alc_post_social_share_buttons_icons' ) ) {
			alc_post_social_share_buttons_icons( $css_class = 'stacked' );
		}
		?>

		<div class="post__content">
			<div class="post__content--inner-left">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

	<?php if ( $post_tags ) : ?>
	<footer class="post__footer">
		<?php alchemists_post_tags( $post, $post_tags_type ); ?>
	</footer>
	<?php endif; ?>

</article>

<?php
// Page Links
alchemists_page_links();
