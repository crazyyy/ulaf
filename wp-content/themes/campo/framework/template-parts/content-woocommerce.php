<?php
/**
 * Template part for displaying woocomerce shop
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
  
$prefix = boldthemes_get_prefix();

/* Shop page content already displayed in header */
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<div class="entry-content">
		<div class="entry-content-inner">
			<?php woocommerce_content(); ?>
		</div><!-- .entry-content-inner -->
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

