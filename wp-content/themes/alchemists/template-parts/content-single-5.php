<?php
/**
 * Template part for displaying posts on a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$post_tags         = isset( $alchemists_data['alchemists__opt-single-post-tags'] ) ? $alchemists_data['alchemists__opt-single-post-tags'] : 1;
$post_tags_type    = isset( $alchemists_data['alchemists__opt-single-post-tags-type'] ) ? $alchemists_data['alchemists__opt-single-post-tags-type'] : 'buttons';

$post_classes = array(
	'post',
	'post--single',
	'post--style-5',
);
?>

<!-- Article -->
<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

	<div class="post__content">

		<?php the_content(); ?>

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
