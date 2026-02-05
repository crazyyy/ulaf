<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
  
$prefix = boldthemes_get_prefix();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
<?php if ( boldthemes_get_option( 'default_headline_height' ) == 'none' ): ?> 

	<header class="entry-header single">
		<?php 
		printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_single_show_superheadline' ) ) ); 
		the_title( '<h1 class="entry-title">', '</h1><!-- .entry-title -->' ); 
		printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_single_show_subheadline' ) ) ); ?>
	</header><!-- .entry-header -->

	<?php // boldthemes_post_thumbnail(); 

endif; ?>

	<div class="entry-content">
		<?php
		
		$media_html = boldthemes_get_media_html();
		if ( !empty( $media_html ) ) printf ('<div class="article-media">%1$s</div><!-- .article-media -->', $media_html ); ?>
		
		<div class="entry-content-inner">
			<?php
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'campo' ),
						array(
								'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			?>
		</div><!-- .entry-content-inner -->
		<?php boldthemes_link_pages(); ?>
	</div><!-- .entry-content -->

	<?php boldthemes_entry_footer( $prefix, boldthemes_get_option( $prefix . '_single_show_bottom' ) ); ?>

</article><!-- #post-<?php the_ID(); ?> -->
