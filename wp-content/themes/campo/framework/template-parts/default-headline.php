<?php
/**
 * Template part for displaying page headline
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
 $prefix = boldthemes_get_prefix();
 
?>
<?php if ( boldthemes_get_option( 'default_headline_height' ) != 'none' || is_customize_preview() ): ?> 
	<?php 
		// TODO: dodati uslove za ispis (blog arhiva itd....) 
		$feat_image = '';
		$default_headline_attr = "";
		$default_headline_style = esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'default_headline_color_scheme' ) ] ) );
		if ( BoldThemesFramework::$page_for_header_id != '' ) {
			$feat_image = wp_get_attachment_url( get_post_thumbnail_id( BoldThemesFramework::$page_for_header_id ) );
			if ( ! $feat_image ) {
				if ( is_singular() && ! is_singular( 'product' ) ) {
					$feat_image = wp_get_attachment_url( get_post_thumbnail_id() );
				} else {
					$feat_image = false;
				}
			}
		} else {
			if ( is_singular() ) {
				$feat_image = wp_get_attachment_url( get_post_thumbnail_id() );
			} else {
				$feat_image = false;
			}
		}
		if ( $feat_image ) {
			$default_headline_style .= ' ;background-image:url(' . esc_url( $feat_image ) . ');';	
			$default_headline_attr .= ' data-parallax="0.8" data-parallax-offset="0" ';	
		}
		
	?>	
	<header class="page-header" style="<?php echo esc_attr( $default_headline_style ); ?>">
		<div class="page-header-inner">
			<?php 
			
			if ( is_home() && ! is_front_page() ) : // latest posts with blog page defined
				
				printf( '<%1$s class="page-title">%2$s</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), single_post_title( '', false ) );
				
				printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', get_the_excerpt( get_queried_object() ) );
				
			elseif ( is_front_page() ) : // latest posts on home page without defined blog page
			
				printf( '<%1$s class="page-title">%2$s</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), get_bloginfo( 'name' ) );
			
				printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', get_bloginfo( 'description' ) );
				
			elseif ( is_archive() ) :
			
				printf( '<h1 class="page-title">%1$s</h1>', get_the_archive_title() );
				printf( '<div class="archive-description">%1$s</div>', get_the_archive_description() );
			
			elseif ( is_search() ) :

				printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( 'page', boldthemes_get_option( 'page_single_show_superheadline' ) ) ); 
				
				printf( '<%1$s class="page-title">' . esc_html__( 'Search Results for: %2$s', 'campo' ) . '</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), '<span>' . get_search_query() . '</span>' );
				
				printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( 'page', boldthemes_get_option( 'page_single_show_subheadline' ) ) );
			
			elseif ( is_404() ) :

				printf( '<%1$s class="page-title">%2$s</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), '404' );
			
			 else :
			 
				// single post
				printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_single_show_superheadline' ) ) );  
				
				printf( '<%1$s class="page-title">%2$s</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), the_title( '', '', false ) );

				printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( $prefix . '_single_show_subheadline' ) ) );  

			endif; ?>
		</div><!-- .page-header-inner -->
	</header><!-- .page-header -->
	
<?php endif; ?>

