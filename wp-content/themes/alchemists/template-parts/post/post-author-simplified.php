<?php
/**
 * Template part for displaying posts Author box a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$author_email    = isset( $alchemists_data['alchemists__opt-single-post-author-email'] ) ? $alchemists_data['alchemists__opt-single-post-author-email'] : 1;
$author_site     = isset( $alchemists_data['alchemists__opt-single-post-author-site'] ) ? $alchemists_data['alchemists__opt-single-post-author-site'] : 1;

$avatar_size = 50;

$post_author_classes = array(
	'post-author',
	'post-author--avatar-left',
	'post-author--simplified',
	'mb-70',
);
?>

<!-- Post Author -->
<div class="<?php echo esc_attr( implode(' ', $post_author_classes ) ); ?>">

	<header class="post-author__header">

		<figure class="post-author__avatar">
			<?php echo get_avatar( get_the_author_meta('email'), $avatar_size ); ?>
		</figure>

		<div class="post-author__info">
			<h5 class="post-author__name"><?php the_author(); ?></h4>
			<span class="post-author__slogan"><?php the_author_meta('nickname'); ?></span>
		</div>
		<ul class="post-author__social-links social-links social-links--btn">
			<?php if ( get_the_author_meta('email') && $author_email == 1 ) : ?>
			<li class="social-links__item">
				<a href="mailto:<?php echo esc_attr( get_the_author_meta('email') ); ?>" class="social-links__link social-links__link--mail"><i class="fa fa-envelope"></i></a>
			</li>
			<?php endif; ?>
			<?php if ( get_the_author_meta('url') && $author_site == 1 ) : ?>
			<li class="social-links__item">
				<a href="<?php echo esc_url( get_the_author_meta('url') ); ?>" class="social-links__link social-links__link--site"><i class="fa fa-link"></i></a>
			</li>
			<?php endif; ?>
		</ul>
	</header>
	<?php if ( get_the_author_meta('description') ) : ?>
	<div class="post-author__description">
		<?php the_author_meta('description'); ?>
	</div>
	<?php endif; ?>
</div>
<!-- Post Author / End -->
