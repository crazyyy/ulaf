<?php

// BoldThemes framework related
add_filter( 'get_search_form', 'boldthemes_search_form_filter' );

add_filter( 'embed_oembed_html', 'boldthemes_embed_html', 10, 3 );
add_filter( 'video_embed_html', 'boldthemes_jetpack_embed_html' ); // Jetpack
//add_filter( 'wp_video_shortcode_library', 'boldthemes_wp_video_shortcode_library' );
//add_filter( 'wp_audio_shortcode_library', 'boldthemes_wp_audio_shortcode_library' );
//add_filter( 'body_class', 'boldthemes_get_body_class' );

add_filter( 'pre_get_document_title', 'boldthemes_get_html_title', 10, 1 );
add_filter( 'wp_kses_allowed_html', 'boldthemes_allowed_html', 10, 2 );
add_filter( 'wdev_lib-use_session', '__return_false' ); // Fix session problem for Custom sidebars plugin
add_filter( 'kses_allowed_protocols' , 'boldthemes_allow_skype_protocol' );

add_filter( 'post_class', 'boldthemes_set_post_class', 10,3);

add_filter( 'override_unload_textdomain', 'boldthemes_override_unload_textdomain', 1, 2 );

add_filter( 'get_the_archive_title', 'boldthemes_archive_title' );

/* Remove (some) prefixes from archives */
function boldthemes_archive_title( $title ) {
	if ( is_category() ) {
		// $title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		// $title = single_tag_title( '', false );
	} elseif ( is_author() ) {
		// $title = '<span class="vcard">' . get_the_author() . '</span>';
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_tax() ) {
		// $title = single_term_title( '', false );
	}
 
	return $title;
}

function boldthemes_set_post_class( $classes, $class, $post_id ) {
	 $prefix = boldthemes_get_prefix();
	 $post_classes = array();
		 
	 if ( !is_single() && ( $prefix == 'blog' || $prefix == 'pf' ) ) {
		$post_classes[] = 'article-list-item';
		$post_classes[]	= 'animate';
	 }
	 
	 return array_merge( $classes, $post_classes );
 }

/**
 * HTML title fix for portfolio
 */

/**
 * HTML fix for search form widget
 */
 
 function boldthemes_search_form_filter( $form ) {
	 // return $form;
	 return str_replace( '<input type="submit" class="search-submit" value="' . esc_attr_x( 'Search', 'submit button' ) . '" />' , '<button type="submit" class="search-submit">' . esc_attr_x( 'Search', 'Submit button' ) . '</button>', $form );
	 
 }
 
 function boldthemes_get_html_title( $title = NULL, $sep = ' - ', $seplocation = NULL ) {

	if ( is_post_type_archive( 'portfolio' ) ) {
		$pf_title = '';
		if ( ! is_null( boldthemes_get_option( 'pf_slug' ) ) && boldthemes_get_option( 'pf_slug' ) != '' ) {
			$pf_slug_id = boldthemes_get_id_by_slug( boldthemes_get_option( 'pf_slug' ) );
			if ( $pf_slug_id > 0 ) {
				$pf_title = get_the_title( $pf_slug_id );	
			} else {
				$pf_title = $pf_slug_id . ucwords( boldthemes_get_option( 'pf_slug' ) );	
			}
		}
		
		if ( ! is_null( boldthemes_get_id_by_slug( 'portfolio' ) ) && boldthemes_get_id_by_slug( 'portfolio' ) != '' ) {
			$pf_title = get_the_title( boldthemes_get_id_by_slug( 'portfolio' ) );
		}
		if ( $pf_title != '' ) {
			return $pf_title . $sep . get_bloginfo( 'name', 'display' ) ;
		} else {
			return NULL;
		}
	}
 }

/**
 * Video shortcode custom HTML
 *
 * @return string
 */
if ( ! function_exists( 'boldthemes_wp_video_shortcode' ) ) {
	function boldthemes_wp_video_shortcode( $item_html, $atts, $video, $post_id, $library ) {
		$replace_value = 'width: ' . $atts['width'] . 'px';
		$replace_with  = 'width: 100%';
		$item_html = str_ireplace( $replace_value, $replace_with, $item_html );
		return '<div class="bt-video-container">' . $item_html . '</div>';
	}
}

/**
 * Enqueue video shortcode custom JS
 *
 * @return string 
 */
if ( ! function_exists( 'boldthemes_wp_video_shortcode_library' ) ) {
	function boldthemes_wp_video_shortcode_library() {
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'boldthemes-video-shortcode', get_parent_theme_file_uri( 'framework/js/video_shortcode.js' ), array( 'mediaelement' ), '', true );
		return 'boldthemes_mejs';
	}
}

/**
 * Enqueue audio shortcode custom JS
 *
 * @return string 
 */
if ( ! function_exists( 'boldthemes_wp_audio_shortcode_library' ) ) {
	function boldthemes_wp_audio_shortcode_library() {
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'boldthemes-audio-shortcode', get_parent_theme_file_uri( 'framework/js/audio_shortcode.js' ), array( 'mediaelement' ), '', true );
		return 'boldthemes_mejs';
	}
}

/*  Add responsive container to embeds
 *
 * @return string 
/* ------------------------------------ */ 
function boldthemes_embed_html( $cache, $url, $attr ) {
	if ( false !== strpos( $url, 'vimeo.com' ) || false !== strpos( $url, 'youtube.com' ) ) { 
		return '<div class="bt-video-container">' . $cache . '</div>';
	} else {
		return $cache;
	}
}

/*  Add responsive container to embeds
 *
 * @return string 
/* ------------------------------------ */ 
function boldthemes_jetpack_embed_html( $html ) {
	return '<div class="bt-video-container">' . $html . '</div>';
}

/**
 * Alowed html
 */
if ( ! function_exists( 'boldthemes_allowed_html' ) ) {
	function boldthemes_allowed_html( $tags, $context ) {
		if ( $context == 'tags' ) {
			$tags['div'] = array(
				'class' => true
			);
			$tags['ul'] = array();
			$tags['li'] = array();
			$tags['a'] = array(
				'href' => true,
				'target' => true,
				'title' => true
			);
		} else if ( $context == 'about_author' ) {
			$tags['div'] = array(
				'class' => true
			);
			$tags['h4'] = array();
			$tags['p'] = array();
			$allowed_attributes = array(
				'alt' => true,
				'class' => true,
				'title' => true,
				'src' => true,
				'height' => true,
				'width' => true,
				'loading' => true,
			);
			$tags['img'] = $allowed_attributes;
		} else if ( $context == 'avatar' ) {
			$allowed_attributes = array(
				'alt' => true,
				'class' => true,
				'title' => true,
				'src' => true,
				'height' => true,
				'width' => true,
				'loading' => true,
			);
			$tags['img'] = $allowed_attributes;
		} else if ( $context == 'share' ) {
			$allowed_attributes = array(
				'class' => true,
				'id' => true,
				'target' => true,
				'title' => true,
				'src' => true,
				'style' => true,
				'data-ico-fa' => true,
				'data-ico-icon7stroke' => true,
				'href' => true
			);
			$tags['span'] = $allowed_attributes;
			$tags['div'] = $allowed_attributes;
			$tags['a'] = $allowed_attributes;
		} else if ( $context == 'wc_icon' ) {
			$tags['div'] = array(
				'class' => true,
				'data-icon' => true,
				'data-icon-code' => true
			);
			$tags['span'] = array(
				'class' => true,
				'data-icon' => true,
				'data-icon-code' => true,
				'data-ico-fa' => true,
				'data-ico-icon7stroke' => true
			);
		} else if ( $context == 'wc_price' ) {
			$tags['span'] = array(
				'class' => true
			);
			$tags['bdi'] = array();
			$tags['del'] = array();
		} else if ( $context == 'wc_image' ) {
			$allowed_attributes = array(
				'alt' => true,
				'class' => true,
				'title' => true,
				'src' => true,
				'height' => true,
				'width' => true,
				'loading' => true,
			);
			$tags['img'] = $allowed_attributes;
		} else if ( $context == 'comments' ) {
			$allowed_attributes = array(
				'href' => true,
			);
			$tags['a'] = $allowed_attributes;
		} else if ( $context == 'audio' ) {
			$tags['iframe'] = array(
				'src'    => true,
				'height' => true,
			);
		} else if ( $context == 'masonry' ) {
			$tags['a'] = array(
				'class'       => true,
				'data-ico-fa' => true,
				'href'        => true,
				'rel'         => true,
				'title'       => true,
				'target'      => true,
			);
			$tags['div'] = array(
				'class'    => true,
				'data-hw'  => true,
				'data-src' => true,
				'data-alt' => true,
				'style'    => true,
			);
			$tags['span'] = array(
				'class' => true,
			);
			$tags['img'] = array(
				'src' => true,
				'alt' => true,
			);
			$tags['h1'] = array(
				'class' => true,
			);
			$tags['h2'] = array(
				'class' => true,
			);
			$tags['h3'] = array(
				'class' => true,
			);
			$tags['h4'] = array(
				'class' => true,
			);
			$tags['h5'] = array(
				'class' => true,
			);
			$tags['h6'] = array(
				'class' => true,
			);
			$tags['ul'] = array(
				'class' => true,
			);
			$tags['li'] = array(
				
			);
		} else if ( $context == 'menu-trigger' ) {
			$tags['div'] = array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
			);
			$tags['a'] = array(
				'href' => array(),
				'title' => array(),
				'target' => array(),
				'data-*' => array(),
				'class' => array(),
			);
			$tags['span'] = array(
				'href' => array(),
				'title' => array(),
				'target' => array(),
				'data-*' => array(),
			);
		} else {
			$allowed_attributes = array(
				'class' => true,
				'id' => true,
				'target' => true,
				'title' => true,
				'src' => true,
				'style' => true,
				'data-ico-fa' => true,
				'data-ico-icon7stroke' => true,
				'href' => true
			);
			$tags['span'] = $allowed_attributes;
			$tags['div'] = $allowed_attributes;
			$tags['a'] = $allowed_attributes;
			$tags['iframe'] = array(
				'src'         => true,
				'height'      => true,
				'width'       => true,
				'scrolling'   => true,
				'frameborder' => true,
			);
		}
		return $tags;
	}
}

if ( ! function_exists( 'boldthemes_allow_skype_protocol' ) ) {
	function boldthemes_allow_skype_protocol( $protocols ) {
		$protocols[] = 'skype';
		return $protocols;
	}
}

// https://developer.wordpress.org/reference/hooks/override_unload_textdomain/
if ( ! function_exists( 'boldthemes_override_unload_textdomain' ) ) {
	function boldthemes_override_unload_textdomain( $override, $domain ) {
		if ( $domain === 'campo' || $domain === 'bt_plugin' ) {
			// Prevents WordPress from unloading this text domain (e.g. in  footer page content)
			$override = true;
		}
		return $override;
	}
}