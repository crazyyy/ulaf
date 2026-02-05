<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<div class="entry-content-inner">
			<?php the_content(); ?>
			<?php boldthemes_link_pages(); ?>
		</div><!-- .entry-content-inner -->
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
