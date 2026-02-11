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
$post_title_tag    = isset( $alchemists_data['alchemists__opt-single-post-title-tag'] ) ? $alchemists_data['alchemists__opt-single-post-title-tag'] : 'h1';
$post_author       = isset( $alchemists_data['alchemists__opt-single-post-author'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-author'] ) : 1;
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_show_img     = isset( $alchemists_data['alchemists__opt-single-post-featured-img'] ) ? $alchemists_data['alchemists__opt-single-post-featured-img'] : 1;
$post_tags         = isset( $alchemists_data['alchemists__opt-single-post-tags'] ) ? $alchemists_data['alchemists__opt-single-post-tags'] : 1;
$post_tags_type    = isset( $alchemists_data['alchemists__opt-single-post-tags-type'] ) ? $alchemists_data['alchemists__opt-single-post-tags-type'] : 'buttons';

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'card',
	'card--lg',
	'post',
	'post--single',
	$post_class
);
?>

<!-- Article -->
<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

	<?php if ( has_post_thumbnail() && $post_show_img ) : ?>
	<figure class="post__thumbnail">
		<?php the_post_thumbnail('alchemists_thumbnail-lg'); ?>
	</figure>
	<?php endif; ?>

	<div class="clearfix">
		<!-- Post Meta - Side -->
		<div class="post__meta-block post__meta-block--side">
			<!-- Post Author -->
			<div class="post-author">
				<figure class="post-author__avatar">
					<?php echo get_avatar( get_the_author_meta('email'), '60' ); ?>
				</figure>
				<div class="post-author__info">
					<h4 class="post-author__name"><?php the_author(); ?></h4>
					<span class="post-author__slogan"><?php echo get_the_author_meta('nickname'); ?></span>
				</div>
			</div>
			<!-- Post Author / End -->

			<?php

			// Post Social Sharing
			if ( function_exists( 'alc_post_social_share_buttons_small' )) {
				alc_post_social_share_buttons_small();
			}

			// Post Meta
			alchemists_entry_meta_single( $date = 'off' ); ?>


		</div>
		<!-- Post Meta - Side / End -->

		<div class="card__content">

			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels( 'post__category' ); ?>
			<?php endif; ?>

			<header class="post__header">

				<?php the_title( "<$post_title_tag class='post__title'>", "</$post_title_tag>" ); ?>

				<?php alchemists_entry_meta_single(); ?>

			</header>

			<div class="post__content">

				<?php the_content(); ?>

			</div>

			<?php if ( $post_tags ) : ?>
			<footer class="post__footer">
				<?php alchemists_post_tags( $post, $post_tags_type ); ?>
			</footer>
			<?php endif; ?>

		</div>
	</div>

</article>

<?php
// Page Links
alchemists_page_links();
