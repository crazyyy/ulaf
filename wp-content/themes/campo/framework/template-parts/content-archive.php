<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
 $prefix = boldthemes_get_prefix();
 
 // echo( boldthemes_get_option( $prefix . '_list_view' ) );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( in_array( boldthemes_get_option( $prefix . '_list_view' ), array( 'standard', 'simple' )) || is_customize_preview() ): ?>
	<header class="entry-header">
		<?php
		printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_list_show_superheadline' ) ) );
		the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_list_show_subheadline' ) ) ); ?>
	</header><!-- .entry-header -->
	<?php endif; ?>	

	<?php 
	
	if ( false ) : 
		boldthemes_post_thumbnail();
	else :	
		// echo '<div class="article-media">' . boldthemes_get_media_html() . '</div><!-- /article-media -->';
		$media_html = boldthemes_get_media_html();
		if ( !empty( $media_html ) ) printf ('<div class="article-media">%1$s</div><!-- .article-media -->', $media_html );
	endif;
	?>
	<div class="article-inner">
		<?php if ( !in_array( boldthemes_get_option( $prefix . '_list_view' ), array( 'standard', 'simple' )) || is_customize_preview() ): ?>
		<header class="entry-header">
			<?php
			printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_list_show_superheadline' ) ) );
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_list_show_subheadline' ) ) ); ?>
		</header><!-- .entry-header -->
		<?php endif; ?>	
		
		<?php if ( boldthemes_get_option( $prefix . '_list_show_excerpt' ) != 'none' || is_customize_preview() ): ?>
		<div class="entry-content">
			<div class="entry-content-inner">
			<?php
				boldthemes_excerpt();
				boldthemes_link_pages();
			?>
			</div><!-- .entry-content-inner -->
		</div><!-- .entry-content -->
		<?php endif; ?>

		<?php
		boldthemes_entry_footer( $prefix, boldthemes_get_option( $prefix . '_list_show_bottom' ), boldthemes_get_read_more( $prefix ) ); 
		?>
	</div><!-- .article-inner -->
</article><!-- #post-<?php the_ID(); ?> -->
