<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
$prefix = boldthemes_get_prefix();

BoldThemesFrameworkTemplate::$cf = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_custom_fields' );
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
		// echo '<div class="article-media">' . boldthemes_get_media_html() . '</div><!-- .article-media -->'; 
		$media_html = boldthemes_get_media_html();
		if ( !empty( $media_html ) ) printf ('<div class="article-media">%1$s</div><!-- .article-media -->', $media_html );
		?>
		<div class="portfolio-meta">
			<?php
			if ( ( BoldThemesFrameworkTemplate::$cf != '' && count( BoldThemesFrameworkTemplate::$cf ) > 0 ) ) {
				echo '<div class="article-super-meta">';
					echo '<dl>';
						for ( $i = 0; $i < count( BoldThemesFrameworkTemplate::$cf ); $i++ ) {
							$item = BoldThemesFrameworkTemplate::$cf[ $i ];
							$item_key = substr( $item, 0, strpos( $item, ':' ) );
							$item_value = substr( $item, strpos( $item, ':' ) + 1 );
							echo '<dt>' . esc_html( $item_key ) . ':</dt>';
							echo '<dd>' . esc_html( $item_value ) . '</dd>';
						}
					echo '</dl>';
				echo '</div><!-- /article-super-meta -->';
			}
			?>
		</div><!-- .portfolio-meta -->
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
			<?php boldthemes_link_pages(); ?>
		</div><!-- .entry-content-inner -->
	</div><!-- .entry-content -->

	<?php boldthemes_entry_footer( $prefix, boldthemes_get_option( $prefix . '_single_show_bottom' ) ); ?>

</article><!-- #post-<?php the_ID(); ?> -->
