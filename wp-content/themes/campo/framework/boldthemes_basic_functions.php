<?php

/**
 * Get option.
 */
 
if ( ! function_exists( 'boldthemes_get_option' ) ) {
	function boldthemes_get_option( $opt ) {

		$post_options = BoldThemesFramework::$post_options;
		
		if ( isset( BoldThemes_Customize_Default::$data[ $opt ] ) ) {			
			if ( isset( $_GET[ $opt ] ) || isset( $_GET[ 'bt_' . $opt ] ) ) {
				$ret = isset( $_GET[ $opt ] ) ? sanitize_text_field( $_GET[ $opt ] ) : sanitize_text_field( $_GET[ 'bt_' . $opt ] );
				if ( $ret === 'true' ) {
					$ret = true;
				} else if ( $ret === 'false' ) {
					$ret = false;
				}
                                
				return sanitize_text_field( $ret );
			}			
		}
		
		if ( $post_options !== null && array_key_exists( BoldThemesFramework::$pfx . '_' . $opt, $post_options ) && $post_options[ BoldThemesFramework::$pfx . '_' . $opt ] === 'null' ) {
			return BoldThemes_Customize_Default::$data[ $opt ];
		}

		if ( $post_options !== null && array_key_exists( BoldThemesFramework::$pfx . '_' . $opt, $post_options ) ) {
			
			$ret = $post_options[ BoldThemesFramework::$pfx . '_' . $opt ];
			
			if ( $ret === 'true' ) {
				$ret = true;
			} else if ( $ret === 'false' ) {
				$ret = false;
			}

			return $ret;
		}
		
		if ( BoldThemesFramework::$options !== null && BoldThemesFramework::$options !== false && array_key_exists( $opt, BoldThemesFramework::$options ) ) {
			
			$ret = BoldThemesFramework::$options[ $opt ];
			if ( $ret === 'true' ) {
				$ret = true;
			} else if ( $ret === 'false' ) {
				$ret = false;
			}

			return $ret;
		} else { 
			if ( BoldThemesFramework::$options !== null ) {

				return BoldThemes_Customize_Default::$data[ $opt ];
			} else {
				
				BoldThemesFramework::$options = get_option( BoldThemesFramework::$pfx . '_theme_options' );
				if ( is_array( BoldThemesFramework::$options ) && array_key_exists( $opt, BoldThemesFramework::$options ) ) {
					
					$ret = BoldThemesFramework::$options[ $opt ];
					if ( $ret === 'true' ) {
						$ret = true;
					} else if ( $ret === 'false' ) {
						$ret = false;
					}

					return $ret;
				} else {

					return BoldThemes_Customize_Default::$data[ $opt ];
				}
			}
		}

	}
}


/**
 * Returns the permalink for a page based on the incoming slug.
 *
 * @param   string  $slug   The slug of the page to which we're going to link.
 * @return  string          The permalink of the page
 */
 if ( ! function_exists( 'boldthemes_get_permalink_by_slug' ) ) {
	function boldthemes_get_permalink_by_slug( $slug, $post_type = 'any' ) {
		if ( $slug != '' && $slug != '#' && substr( $slug, 0, 4 ) != 'http' && substr( $slug, 0, 5 ) != 'https' && substr( $slug, 0, 6 ) != 'mailto' ) {
			$permalink = null;
			$args = array(
				'name'          => $slug,
				'max_num_posts' => 1
			);
			
			$args = array_merge( $args, array( 'post_type' => $post_type ) );
			
			$query = new WP_Query( $args );
			if( $query->have_posts() ) {
				$query->the_post();
				$permalink = get_permalink( get_the_ID() );
				wp_reset_postdata();
			}
			if ( $permalink ) {
				return $permalink;
			}
			return $slug;
		} else {
			return $slug;
		}

	}
}

if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}

/**
 * Prints the post navigation with feature image
 */
if ( ! function_exists( 'boldthemes_the_post_navigation' ) ) {
	function boldthemes_the_post_navigation() {
		$post_type = get_post_type();
		switch ( $post_type ) {
			case 'post':
				$options = boldthemes_get_option( 'blog_single_show_navigation' );
				break;
			case 'portfolio':
				$options = boldthemes_get_option( 'pf_single_show_navigation' );
				break;
		}
		if ( count( $options ) ) {			
			$prev_post = get_previous_post();
			$prev_thumbnail_html = '';
			$prev_text = '';
			$prev_class_arr[] = 'nav-inner';
			
			$next_post = get_next_post();
			$next_thumbnail_html = '';
			$next_text = '';
			$next_class_arr[] = 'nav-inner';
			
			if ( in_array( 'featured_image', $options) ) {
				$prev_thumbnail = ! empty( $prev_post ) && intval( $prev_post->ID ) > 0 ? get_the_post_thumbnail( $prev_post->ID, 'thumbnail') : '';
				$next_thumbnail = ! empty( $next_post ) && intval( $next_post->ID ) > 0 ? get_the_post_thumbnail( $next_post->ID, 'thumbnail') : '';
				
				if ( ! empty( $prev_thumbnail ) ) {
					$prev_thumbnail_html = '<span class="nav-image">' . $prev_thumbnail . '</span>';
				} else {
					$prev_thumbnail_html = '<span class="nav-image"></span>';
					$prev_class_arr[] = 'nav-inner-post-without-image';
				}
				if ( ! empty( $next_thumbnail ) ) {
					$next_thumbnail_html = '<span class="nav-image">' . $next_thumbnail . '</span>';
				} else {
					$next_thumbnail_html = '<span class="nav-image"></span>';
					$next_class_arr[] = 'nav-inner-post-without-image';
				}
				
				$prev_class_arr[] = 'nav-inner-with-image';							
				$next_class_arr[] = 'nav-inner-with-image';							
			}
			
			if ( in_array( 'label', $options) ) {
				$prev_text .= '<span class="nav-supertitle">' . esc_html__( 'Previous', 'campo' ) . '</span>';
				$next_text .= '<span class="nav-supertitle">' . esc_html__( 'Next', 'campo' ) . '</span>';	
				$prev_class_arr[] = 'nav-inner-with-label';							
				$next_class_arr[] = 'nav-inner-with-label';							
			}
			
			if ( in_array( 'title', $options) ) {
				$prev_text .= '<span class="nav-title">%title</span>';
				$next_text .= '<span class="nav-title">%title</span>';
				$prev_class_arr[] = 'nav-inner-with-title';							
				$next_class_arr[] = 'nav-inner-with-title';							
			}
			
			if ( in_array( 'date', $options) ) {
				$prev_text .= '<span class="nav-date">%date</span>';	
				$next_text .= '<span class="nav-date">%date</span>';
				$prev_class_arr[] = 'nav-inner-with-date';							
				$next_class_arr[] = 'nav-inner-with-date';					
			}
			
			$prev_text_html = ! empty( $prev_text ) ? '<span class="nav-text">' . $prev_text . '</span>' : '';
			$next_text_html = ! empty( $next_text ) ? '<span class="nav-text">' . $next_text . '</span>' : '';			
			

			$args = array(
				'prev_text'  	=> '<span class="'. implode( ' ', $prev_class_arr ) .'">' . $prev_thumbnail_html . $prev_text_html . '</span>',
				'next_text'  	=> '<span class="'. implode( ' ', $next_class_arr ) .'">' . $next_thumbnail_html . $next_text_html . '</span>'
			);

			the_post_navigation( $args );			
		}

		
	}
}

/**
 * Returns post meta
 */
if ( ! function_exists( 'boldthemes_the_post_meta' ) ) {
	function boldthemes_the_post_meta( $prefix, $options ) {		
		$output = '';
		if ( !is_array( $options ) && $options != "" ) $options = explode( ",", $options );
		$page_id = ( $prefix == 'page' && ( is_search() || is_archive() )  ) ? BoldThemesFramework::$page_for_header_id : null;
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) { 
				
				switch ( $option ) {
					case 'excerpt':
						// $output .= boldthemes_get_excerpt();
						$output .= boldthemes_get_excerpt( $page_id );
						break;
					case 'breadcrumbs-yoast':
						$output .= boldthemes_get_breadcrumbs_yoast();
						break;
					case 'date':
						$output .= boldthemes_get_posted_on();
						break;
					case 'author':
						$output .= boldthemes_get_author();
						break;
					case 'author-photo':
						$output .= boldthemes_get_author_photo();
						break;
					case 'categories':
						$output .= boldthemes_get_category_list( $prefix );
						break;
					case 'tags':
						$output .= boldthemes_get_tag_list( $prefix );
						break;
					case 'product-sku':
						$output .= boldthemes_get_product_sku();
						break;
					case 'product-rating-average':
						$output .= boldthemes_get_product_rating_average();
						break;
					case 'comments-number':
						$output .= boldthemes_get_comments_number();
						break;
					case 'comments-link':
						$output .= boldthemes_get_comments_link_the_post_meta();
						break;
					case 'share':
						if ( $prefix !== '' ) {
							$share_slug = is_single() || is_page() || $prefix == 'page' ? $prefix . '_single_share' : $prefix . '_list_share';
							
							$size			= boldthemes_get_option( $share_slug . '_size' );
							$style			= boldthemes_get_option( $share_slug . '_style' );
							$shape			= boldthemes_get_option( $share_slug . '_shape' );
							$color_scheme	= boldthemes_get_option( $share_slug . '_color_scheme' );
							
							$args   = array( 'size' => $size, 'style' => $style, 'shape' => $shape, 'color_scheme' => $color_scheme);
							
							$output .= boldthemes_share_html( boldthemes_get_option( $share_slug ), $args );
						}		
						break;				
				}
			}			
		}
		return trim( $output );
	}
}

/**
 * Returns Yoast breadcrumbs
 */
	 
if ( ! function_exists( 'boldthemes_get_breadcrumbs_yoast' ) ) :
	function boldthemes_get_breadcrumbs_yoast() {
		if ( function_exists( 'yoast_breadcrumb' ) ) :  
			return yoast_breadcrumb( '<span class="breadcrumbs yoast-breadcrumbs">', '</span>', false );
		endif;
	}
endif;

/**
 * Returns excerpt
 */
	 
if ( ! function_exists( 'boldthemes_get_excerpt' ) ) :
	function boldthemes_get_excerpt( $page_id = null ) {
		$excerpt = get_the_excerpt( $page_id );
		return sprintf( '<div class="excerpt"><p>%1$s</p></div>', $excerpt );
	}
endif;

/**
 * Prints excerpt
 */
	 
if ( ! function_exists( 'boldthemes_excerpt' ) ) :
	function boldthemes_excerpt( $page_id = null ) {
		$excerpt = get_the_excerpt( $page_id );
		printf( '<div class="excerpt"><p>%1$s</p></div>', $excerpt );
	}
endif;



/**
 * Returns post excerpt by post id
 *
 * @param int
 * @return string 
 */
if ( ! function_exists( 'boldthemes_get_the_excerpt' ) ) {
	function boldthemes_get_the_excerpt( $post_id ) {
		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( $excerpt == '' && $post_id ) {
			$post = get_post( $post_id );
			if ( !is_null( $post ) && $post != '' ) {
				if ( $post->post_type == 'post' ) {
					// Was: temporary removed get_the_excerpt, core bug https://core.trac.wordpress.org/ticket/42814
					// $excerpt = get_the_excerpt( $post_id ); 
				}				
			}
		}
		return $excerpt;
	}
}

// Remove default hellip 
if ( ! function_exists( 'boldthemes_remove_hellip' ) ) {
	function boldthemes_remove_hellip( $more ) {
	    return '...';
	}
}

add_filter('excerpt_more', 'boldthemes_remove_hellip');

/**
 * Returns HTML with meta information for the current post-date/time.
 */
	 
if ( ! function_exists( 'boldthemes_get_posted_on' ) ) :
	function boldthemes_get_posted_on() {
		// $prefix = esc_html_x( 'on %1$s', 'post date', 'campo' );
		$prefix = '%1$s';
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %1$s: post date. */
			$prefix,
			'<span>' . $time_string . '</span>'
		);

		return sprintf( '<span class="posted-on">%1$s</span>', $posted_on ) ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

/**
 * Returns HTML with meta information for the current author.
 */
	 
if ( ! function_exists( 'boldthemes_get_author' ) ) :

	function boldthemes_get_author() {
		// $prefix = esc_html_x( 'by %1$s', 'post author', 'campo' );
		$prefix = '%1$s';
		global $post;
		$author_id = $post->post_author;
		$byline = sprintf(
			/* translators: %1$s: post author. */
			$prefix,
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID', $author_id ) ) ) . '">' . esc_html( get_the_author_meta( 'display_name' , $author_id ) ) . '</a></span>'
		);

		return sprintf ( '<span class="author-name">%1$s</span>', $byline ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

/**
 * Returns author photo.
 */
	 
if ( ! function_exists( 'boldthemes_get_author_photo' ) ) :

	function boldthemes_get_author_photo() {
		global $post;
		$author_id = $post->post_author;
		$avatar = get_avatar( $author_id );

		if ( $avatar ) return sprintf ( '<span class="author-avatar">%1$s</span>', $avatar ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

/**
 * Returns author description.
 */
	 
if ( ! function_exists( 'boldthemes_get_author_description' ) ) :

	function boldthemes_get_author_description() {
		global $post;
		$author_id = $post->post_author;
		$description = get_the_author_meta( 'description', $author_id );

		if ( $description ) return sprintf ( '<span class="author-description">%1$s</span>', $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

/**
 * Returns the post navigation with feature image
 */
 
if ( ! function_exists( 'boldthemes_get_comments_number' ) ) :
	function boldthemes_get_comments_number() {
		if ( comments_open() ) :
			$comments_hash = ( 'product' === get_post_type() ) ? '#tab-reviews" ' : '#comments';
			$comments_class = ( 'product' === get_post_type() ) ? 'woocommerce-review-link' : '';
			$comments_text  = get_comments_number( get_the_ID() ) == 1 ? 'comment' : 'comments';
			$comments_number = sprintf(
				/* translators: %1$s: number of comments. */
				esc_html_x( '%1$s ' . $comments_text, 'post comments', 'campo' ),
				'<a href="' . esc_url( get_permalink() ) . $comments_hash . '" rel="bookmark" class="'. esc_attr( $comments_class ) .'">' . get_comments_number() . '</a>'
			);
			return sprintf ( '<span class="comments-number"> %1$s</span>', $comments_number ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
		endif;
	}
endif;

/**
 * Returns the leave comment link in the post meta
 */
 
if ( ! function_exists( 'boldthemes_get_comments_link_the_post_meta' ) ) :
	function boldthemes_get_comments_link_the_post_meta() {
		if ( comments_open() ) :
			$comments_hash  = get_comments_number( get_the_ID() ) ? '#comments' : '#respond';			
			$comments_text  = __( 'Leave a Comment<span class="screen-reader-text"> on %1$s</span>', 'campo' );			
			return sprintf ( '<span class="comments-link"><a href="' . esc_url( get_permalink() ) . $comments_hash . '">' . $comments_text . '</a></span>', wp_kses_post( get_the_title() ) );
		endif;
	}
endif;

/**
 * Returns the leave comment link
 */
if ( ! function_exists( 'boldthemes_comments_link' ) ) :
	function boldthemes_comments_link() {
		if ( comments_open() ) :
			echo ' <span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %1$s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %1$s</span>', 'campo' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		endif;		
	}
endif;

/**
 * Returns the post category list
 */
 
if ( ! function_exists( 'boldthemes_get_category_list' ) ) :
	function boldthemes_get_category_list( $prefix ) {
		$categories_list = '';
		switch( $prefix ){
			case 'blog':
				$categories_list = boldthemes_get_the_category_list( esc_html__( ', ', 'campo' ) );
				break;
			case 'pf':
				$categories_list = boldthemes_get_the_category_list( esc_html__( ', ', 'campo' ), '', false, 'portfolio_category' );
				break;
			case 'shop':
				$categories_list = boldthemes_get_the_category_list( esc_html__( ', ', 'campo' ) );$categories_list = boldthemes_get_the_category_list( esc_html__( ', ', 'campo' ), '', false, 'product_cat' );
				break;
		}
		if ( $categories_list ) {
			/* translators: used between list items, there is a space after the comma */
			/* translators: 1: list of categories. */
			return sprintf( ' <span class="cat-links">' . esc_html__( '%1$s', 'campo' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
endif;

/**
 * Returns the post tag list
 */
 
if ( ! function_exists( 'boldthemes_get_tag_list' ) ) :
	function boldthemes_get_tag_list( $prefix ) {
		/* translators: used between list items, there is a space after the comma */
		$tags_list = '';
		switch( $prefix ){
			case 'blog':
				$tags_list = get_the_term_list( get_the_ID(), 'post_tag', '', esc_html_x( ', ', 'list item separator', 'campo' ) );
				break;
			case 'pf':
				$tags_list = '';
				break;
			case 'shop':
				$tags_list = get_the_term_list( get_the_ID(), 'product_tag', '', esc_html_x( ', ', 'list item separator', 'campo' ) );
				break;
		}
		if ( $tags_list ) {
			/* translators: 1: list of tags. */
			// return sprintf( ' <span class="tags-links">' . esc_html__( 'Tagged %1$s', 'campo' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return sprintf( ' <span class="tags-links">%1$s</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			// return sprintf( ' <span class="tags-links"><span class="tags-links-title">' . esc_html__( 'Tagged: ', 'campo' ) . '</span>%1$s</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}	
	}
endif;



/**
 * Returns share icons HTML
 *
 * @return string
 */
if ( ! function_exists( 'boldthemes_share_html' ) ) {
	function boldthemes_share_html( $share_options, $args ) {		
		$output = "";
	
		$size			= isset ( $args[ 'size' ] )  ? $args[ 'size' ]  : 'small'; 
		$style			= isset ( $args[ 'style' ] ) ? $args[ 'style' ] : 'filled';
		$shape			= isset ( $args[ 'shape' ] ) ? $args[ 'shape' ] : 'circle';
		$color_scheme	= isset ( $args[ 'color_scheme' ] ) ? $args[ 'color_scheme' ] : '';

		$url = get_permalink();
		if ( !is_array( $share_options ) && $share_options != "" ) $share_options = explode( ",", $share_options );
		if ( is_array( $share_options ) ) {
			foreach ( $share_options as $value ) {
				
				switch ( $value ) {
					case 'copy_clipboard':
						$share_url					= '';
						$args_copy['url'] 			= '#';
						//$args_copy['icon'] 			= 'fontawesome5regular_f0c5';
						//$args_copy['icon'] 			= 'fontawesome6brands_f0c5';
						$args_copy['icon'] 			= 'fontawesome6brands_f4f2';
						$args_copy['size'] 			= $size;
						$args_copy['style'] 		= $style;
						$args_copy['el_class'] 		= 'bt-share-copy bt_bb_icon_holder_copy';
						$args_copy['shape'] 		= $shape;
						$args_copy['color_scheme'] 	= $color_scheme;
						$args_copy['url_title'] 	= esc_html__( 'Copy current url to cliboard', 'campo' );
						
						$output .= boldthemes_get_icon_html( $args_copy );
						break;
					case 'facebook':
						$share_url = 'https://www.facebook.com/sharer.php?u=' . $url;
						$share_icon = 'fontawesome6brands_f39e';
						$share_url_title = esc_attr__( 'Facebook', 'campo' );
						break;
					case 'twitter':
						$share_url = 'https://twitter.com/intent/tweet?url=' . $url;
						$share_icon = 'fontawesome6brands_f099';
						$share_url_title = esc_attr__( 'Twitter', 'campo' );
						break;
					case 'twitter-x':
						$share_url = 'https://twitter.com/intent/tweet?url=' . $url;
						$share_icon = 'fontawesome6brands_e61b';
						$share_url_title = esc_attr__( 'Twitter (X)', 'campo' );
						break;
					case 'linkedin':
						$share_url = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url;
						$share_icon = 'fontawesome6brands_f0e1';
						$share_url_title = esc_attr__( 'LinkedIn', 'campo' );
						break;
					case 'vk':
						$share_url = 'http://vk.com/share.php?url=' . $url;	
						$share_icon = 'fontawesome6brands_f189';
						$share_url_title = esc_attr__( 'VK', 'campo' );
						break;	
					case 'whatsapp':
						$share_url = 'https://api.whatsapp.com/send?text=' . $url;
						$share_icon = 'fontawesome6brands_f232';
						$share_url_title = esc_attr__( 'WhatsApp', 'campo' );
						break;		
					case 'mailto':
						$share_url = 'mailto:?subject=' . rawurlencode( esc_attr__( 'I want to share this with you', 'campo' ) . '&amp;body=' . esc_attr__( 'Check out this URL: ', 'campo' ) . $url );
						$share_icon = 'fontawesome6solid_f0e0';//'fa_f0e0';
						$share_url_title = esc_attr__( 'Email', 'campo' );
						break;
					case 'reddit':
						$share_url = 'https://reddit.com/submit?url=' . $url;
						$share_icon = 'fontawesome6brands_f1a1';
						$share_url_title = esc_attr__( 'Reddit', 'campo' );
						break;
					case 'tumblr':
						$share_url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . $url;
						$share_icon = 'fontawesome6brands_f173';
						$share_url_title = esc_attr__( 'Tumblr', 'campo' );
						break;
					case 'pinterest':
						$share_url = 'http://pinterest.com/pin/create/link/?url=' . $url;
						$share_icon = 'fontawesome6brands_f0d2';
						$share_url_title = esc_attr__( 'Pinterest', 'campo' );
						break;
					case 'blogger':
						$share_url = 'https://www.blogger.com/blog-this.g?u=' . $url;
						$share_icon = 'fontawesome6brands_f37c'; // ??
						$share_url_title = esc_attr__( 'Blogger', 'campo' );
						break;
					case 'evernote':
						$share_url = 'https://www.evernote.com/clip.action?url=' . $url;
						$share_icon = 'fontawesome6brands_f839';// ??
						$share_url_title = esc_attr__( 'EverNote', 'campo' );
						break;
					/*case 'livejournal':
						$share_url = 'http://www.livejournal.com/update.bml?event=' . $url;
						$share_icon = 'fa_f791';//??
						$share_url_title = esc_attr__( 'LiveJournal', 'campo' );
						break;*/
					case 'get-pocket':
						$share_url = 'https://getpocket.com/edit?url=' . $url;
						$share_icon = 'fontawesome6brands_f265';
						$share_url_title = esc_attr__( 'GetPocket', 'campo' );
						break;
					case 'hacker-news':
						$share_url = 'https://news.ycombinator.com/submitlink?u=' . $url;
						$share_icon = 'fontawesome6brands_f1d4';
						$share_url_title = esc_attr__( 'HackerNews', 'campo' );
						break;
					case 'flipboard':
						$share_url = 'https://share.flipboard.com/bookmarklet/popout?v=2&url=' . $url; // ?? nesto puca kada dodje na stranu
						$share_icon = 'fontawesome6brands_f44d';
						$share_url_title = esc_attr__( 'FlipBoard', 'campo' );
						break;
					case 'googlebookmarks':
						$share_url = 'https://www.google.com/bookmarks/mark?op=edit&bkmk=' . $url; // Google Bookmarks is no longer a supported product. To save your bookmarks, click on "Export bookmarks".
						$share_icon = 'fontawesome6brands_f1a0';
						$share_url_title = esc_attr__( 'GoogleBookmarks', 'campo' );
						break;
					/*case 'instapaper':
						$share_url = 'http://www.instapaper.com/edit?url=' . $url; 
						$share_icon = 'fa_f791';  // ??
						$share_url_title = esc_attr__( 'InstaPaper', 'campo' );
						break;*/
					case 'diaspora':
						$share_url = 'https://share.diasporafoundation.org/?url=' . $url; 
						$share_icon = 'fontawesome6brands_f791';
						$share_url_title = esc_attr__( 'Diaspora', 'campo' );
						break;
					case 'qq':
						$share_url = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . $url; 
						$share_icon = 'fontawesome6brands_f1d6'; 
						$share_url_title = esc_attr__( 'QZone', 'campo' );
						break;
					case 'weibo':
						$share_url = 'http://service.weibo.com/share/share.php?url=' . $url; 
						$share_icon = 'fontawesome6brands_f18a';
						$share_url_title = esc_attr__( 'Weibo', 'campo' );
						break;
					/*case 'okru':
						$share_url = 'https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl=' . $url; 
						$share_icon = 'fa_f791'; // ??
						$share_url_title = esc_attr__( 'OKru', 'campo' );
						break;*/
					/*case 'douban':
						$share_url = 'http://www.douban.com/recommend/?href=' . $url; 
						$share_icon = 'fa_f791'; // ??
						$share_url_title = esc_attr__( 'Douban', 'campo' );
						break;*/
					case 'renren':
						$share_url = 'http://widget.renren.com/dialog/share?resourceUrl=' . $url . '&srcUrl=' . $url; 
						$share_icon = 'fontawesome6brands_f18b';
						$share_url_title = esc_attr__( 'RenRen', 'campo' );
						break;
					case 'xing':
						$share_url = 'https://www.xing.com/spi/shares/new?url=' . $url; 
						$share_icon = 'fontawesome6brands_f168'; 
						$share_url_title = esc_attr__( 'XING', 'campo' );
						break;
					/*case 'threema':
						$share_url = 'threema://compose?text=' . $url; // nista ne otvara
						$share_icon = 'fa_f791'; // ??
						$share_url_title = esc_attr__( 'Threema', 'campo' );
						break;*/
					/*case 'sms':
						$share_url = '#';
						$share_icon = '#';
						break;*/
					case 'skype':
						$share_url = 'https://web.skype.com/share?url=' . $url; 
						$share_icon = 'fontawesome6brands_f17e';
						$share_url_title = esc_attr__( 'Skype', 'campo' );
						break;
					case 'lineme':
						$share_url = 'https://lineit.line.me/share/ui?url=' . $url; 
						$share_icon = 'fontawesome6brands_f3c0'; // ??
						$share_url_title = esc_attr__( 'Line.me', 'campo' );
						break;
					case 'telegram':
						$share_url = 'https://telegram.me/share/url?url=' . $url; 
						$share_icon = 'fontawesome6brands_f2c6';
						$share_url_title = esc_attr__( 'Telegram.me', 'campo' );
						break;
					case 'viber':
						$share_url = 'viber://forward?text=' . $url; 
						$share_icon = 'fontawesome6brands_f409';
						$share_url_title = esc_attr__( 'Viber', 'campo' );
						break;
					default:
						$share_url = '#';
						$share_icon = '#';
						$share_url_title = '';
						break;			
				}
				
				if ( $share_url != '' && $share_url != '#' ){
					$args['url'] 			= $share_url;
					$args['icon'] 			= $share_icon;
					$args['size'] 			= $size;
					$args['style'] 			= $style;
					$args['el_class'] 		= 'bt-share-' . $value;
					$args['shape'] 			= $shape;
					$args['color_scheme'] 	= $color_scheme;
					$args['url_title'] 		=  $share_url_title != '' ? esc_html__( 'Share on ', 'campo' ) . $share_url_title : '';

					$output .= boldthemes_get_icon_html( $args );
				}
			}
		}
		
		return $output != '' ? sprintf( ' <div class="share-options"><span class="share-options-title">' . esc_html__( 'Share ', 'campo' ) . '</span>%1$s</div>', $output ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
	}
}

/**
 * Prints the product SKU
 */
 
if ( ! function_exists( 'boldthemes_get_product_sku' ) ) :
	function boldthemes_get_product_sku() {
		if( 'product' === get_post_type() ){
			$wc_pf = new WC_Product_Factory();
			$product = $wc_pf->get_product();
			$product_sku = $product->get_sku();
			if ( $product_sku) {
				return sprintf( ' <span class="product-sku">' . esc_html__( 'SKU: %1$s', 'campo' ) . '</span>', $product_sku ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}		
	}
endif;

/**
 * Prints the product rating
 */
 
if ( ! function_exists( 'boldthemes_get_product_rating_average' ) ) :
	function boldthemes_get_product_rating_average() {
		if( 'product' === get_post_type() ){
			$wc_pf = new WC_Product_Factory();
			$product = $wc_pf->get_product();
			$product_rating_html = wc_get_rating_html( $product->get_average_rating() );
			if ( $product_rating_html ) {
				return sprintf( ' <span class="woocommerce-product-rating">' . esc_html__( 'Rating: %1$s', 'campo' ) . '</span>', $product_rating_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		/*
		if ( post_type_supports( 'product', 'comments' ) ) {
			wc_get_template( 'single-product/rating.php' );
		}
		*/
	}
endif;

/*
* Entry footer 
* Prints HTML with meta information for post sharing, the categories, tags and comments.
*/

if ( ! function_exists( 'boldthemes_entry_footer' ) ) :
	function boldthemes_entry_footer( $prefix, $options, $more_content = '' ) {
		$content = boldthemes_the_post_meta( $prefix, $options );
		if ( $content == '' && $more_content == '' ) return '';
		if ( $more_content != '' ) $more_content = '<div class="read-more">' . $more_content . '</div><!-- .read-more -->';
		return printf( '<footer class="entry-footer"><div class="entry-meta entry-footer-meta">%1$s</div>%2$s<!-- .entry-footer-meta --></footer><!-- .entry-footer -->', $content, $more_content ) ;  
	}
endif;

/**
 * Displays read more button on list views.
 */

if ( ! function_exists( 'boldthemes_read_more' ) ) :
	function boldthemes_get_read_more( $prefix ) {
			if ( ! is_single() && ( $prefix == 'blog' || $prefix == 'pf' || $prefix == 'search' ) ) {
				$style			= boldthemes_get_option( $prefix . '_list_read_more_style' );
				$el_style = '';
				if ( $style == '' || $style == 'none' ){
					//return false;
					$el_style	= 'display: none;';
					$style		= 'none';
				}
				$icon			= boldthemes_get_option( $prefix . '_list_read_more_icon' );
				$size			= boldthemes_get_option( $prefix . '_list_read_more_size' );
				$shape			= boldthemes_get_option( $prefix . '_list_read_more_shape' );
				$color_scheme	= boldthemes_get_option( $prefix . '_list_read_more_color_scheme' );				
				
				// $continue_icon		= !is_rtl() ? 'fa_f061' : 'fa_f060';
				$continue_icon			= '';
				$continue_icon			= $icon != '' ?  $icon : $continue_icon;
				$continue_icon_position = !is_rtl() ?  'right' : 'left';
				
				//echo '<div class="read-more">';	
					return boldthemes_get_button_html ( 
						array ( 
							'icon' 			=> $continue_icon, 
							'url' 			=> get_permalink(), 
							'text' 			=> esc_html__( 'Continue reading', 'campo' ), 
							'style' 		=> $style,
							'size' 			=> $size,
							'shape' 		=> $shape,
							'color_scheme'	=> $color_scheme,
							'icon_position' => $continue_icon_position,
							'el_style'		=> $el_style
						)
					);
				// echo '</div><!-- .read-more -->';
			}
	}
endif;

/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 */
	 
if ( ! function_exists( 'boldthemes_post_thumbnail' ) ) :

	function boldthemes_post_thumbnail() {
		
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail article-media">
				<?php the_post_thumbnail(); ?>
			</div><!-- .post-thumbnail -->
	
		<?php else : ?>

			<div class="post-thumbnail article-media">
				<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php
						the_post_thumbnail(
							'post-thumbnail',
							array(
								'alt' => the_title_attribute(
									array(
										'echo' => false,
									)
								),
							)
						);
					?>
				</a>
			</div><!-- .post-thumbnail -->

			<?php
		endif; // End is_singular().
	}
endif;


/**
 * Multipage post navigation
 */
 
if ( ! function_exists( 'boldthemes_link_pages' ) ) {
	function boldthemes_link_pages() {
		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campo' ),
				'after'  => '</div>',
			)
		);				
	}
}

/**
 * Get all pages
 */
 
if ( ! function_exists( 'boldthemes_get_all_pages' ) ) {
	function boldthemes_get_all_pages() {
		$defaults = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => 0,
			'echo'                  => 1,
			'name'                  => 'page_id',
			'id'                    => '',
			'class'                 => '',
			'show_option_none'      => '',
			'show_option_no_change' => '',
			'option_none_value'     => '',
			'value_field'           => 'ID',
		);
 
		$pages = get_pages( $defaults );
		
		$r = array( '' => '' );

		foreach( $pages as $page ) {
			$parent_id = $page->post_parent;
			$depth = 0;
			$pre = '';
			while( $parent_id > 0 ) {
				$page1 = get_page( $parent_id );
				$parent_id = $page1->post_parent;
				$depth++;
				$pre .= '&nbsp;&nbsp;&nbsp;';
			}
			$r[ $page->post_name ] = $pre . $page->post_title;
		}
		
		return $r;
		
	}
}

/**
 * Shim for sites older than 5.2.
 *
 * @link https://core.trac.wordpress.org/ticket/12563
 */
	 
if ( ! function_exists( 'wp_body_open' ) ) :

	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;