<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
$prefix = is_search() ? 'search' : boldthemes_get_prefix();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('search'); ?>>
	<header class="entry-header">
		<?php 
		printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_list_show_superheadline' ) ) );
		the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );  
		printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option(  $prefix . '_list_show_subheadline' ) ) );
		?>
	</header><!-- .entry-header -->

	<?php 		
	/*if ( has_post_thumbnail() ) : 
		boldthemes_post_thumbnail();
	endif; */
	?>
	<div class="article-inner">
		<?php if ( boldthemes_get_option( $prefix . '_list_show_excerpt' ) != 'none' || is_customize_preview() ): ?>
		<div class="entry-content">
			<div class="entry-content-inner">
			<?php
				boldthemes_excerpt();
			?>
			</div><!-- .entry-content-inner -->
		</div><!-- .entry-content -->
		<?php endif; ?>

		<?php
		boldthemes_entry_footer( $prefix, boldthemes_get_option( $prefix . '_list_show_bottom' ), boldthemes_get_read_more( $prefix ) ); 
		?>

	</div><!-- .article-inner -->
</article><!-- #post-<?php the_ID(); ?> -->
