<?php

/**
 * Header meta tags output
 */
if ( ! function_exists( 'boldthemes_header_meta' ) ) {
	function boldthemes_header_meta() { ?>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!--meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"-->
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
	<?php }
}

/**
 * Custom fonts
 */

if ( ! function_exists( 'boldthemes_custom_font' ) ) {
	function boldthemes_custom_font( $font ) {
		if ( array_key_exists( $font, BoldThemesFramework::$custom_fonts ) ) {
			BoldThemesFramework::$custom_fonts_enqueue[ $font ] = $font;
			return ''; // do not enqueue as google font
		}
		return $font;
	}
}

if ( ! function_exists( 'boldthemes_enqueue_custom_fonts' ) ) {
	function boldthemes_enqueue_custom_fonts() {
		foreach ( BoldThemesFramework::$custom_fonts as $item ) {
			$font_family = $item['font'];
			foreach ( $item['variants'] as $variant_key => $variant_arr ) {
				echo '<style>';
				echo '@font-face{';
					echo 'font-family:"' . esc_html( $font_family ) . '";';
					if ( stripos( $variant_key, 'italic' ) !== false ) {
						echo 'font-style:italic;';
					} else {
						echo 'font-style:normal;';
					}
					if ( $variant_key == 'regular' || $variant_key == 'italic' ) {
						echo 'font-weight:400;';
					} else {
						echo 'font-weight:' . intval( $variant_key ) . ';';
					}
					echo 'src:';
					if ( isset( $variant_arr['woff2'] ) ) {
						echo 'url(' . esc_url( $variant_arr['woff2'] ) . ')format("woff2")';
						if ( isset( $variant_arr['woff'] ) || isset( $variant_arr['ttf'] ) ) {
							echo ',';
						}
					}
					if ( isset( $variant_arr['woff'] ) ) {
						echo 'url(' . esc_url( $variant_arr['woff'] ) . ')format("woff")';
						if ( isset( $variant_arr['ttf'] ) ) {
							echo ',';
						}
					}
					if ( isset( $variant_arr['ttf'] ) ) {
						echo 'url(' . esc_url( $variant_arr['ttf'] ) . ')format("truetype")';
					}
					echo ';';
				echo '}';
				echo '</style>';
			}
		}
		BoldThemesFramework::$custom_fonts_enqueue = array();
	}
}

if ( ! function_exists( 'boldthemes_get_custom_fonts' ) ) {
	function boldthemes_get_custom_fonts( $arr ) {
		$base_dir = $arr['base_dir'];
		$base_uri = $arr['base_uri'];
		$glob_match = glob( $base_dir . '/assets/custom-fonts/*' );
		if ( $glob_match ) {
			foreach( $glob_match as $font_dir ) {
				if ( is_dir( $font_dir ) ) {
					preg_match( '/[^\\\\\/]+$/', $font_dir, $font_dir_match );
					$font_name = $font_dir_match[0];
					BoldThemesFramework::$custom_fonts[ $font_name ] = array(
						'font' => $font_name,
						'variants' => array()
					);
					foreach ( glob( $font_dir . '/*' ) as $font_file ) {
						if ( is_file( $font_file ) ) {
							preg_match( '/[^\\\\\/]+\.[^\.]+$/i', $font_file, $font_file_match );
							$file_name = $font_file_match[0];
							if ( stripos( $file_name, '.woff2' ) ) {
								if ( stripos( $file_name, '-' ) ) {
									preg_match( '/\-([^\-]+)\.[^\.]+$/', $file_name, $m );
									$variant = strtolower( $m[1] );
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants'][ $variant ]['woff2'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								} else {
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants']['regular']['woff2'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								}
							} else if ( stripos( $file_name, '.woff' ) ) {
								if ( stripos( $file_name, '-' ) ) {
									preg_match( '/\-([^\-]+)\.[^\.]+$/', $file_name, $m );
									$variant = strtolower( $m[1] );
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants'][ $variant ]['woff'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								} else {
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants']['regular']['woff'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								}
							} else if ( stripos( $file_name, '.ttf' ) ) {
								if ( stripos( $file_name, '-' ) ) {
									preg_match( '/\-([^\-]+)\.[^\.]+$/', $file_name, $m );
									$variant = strtolower( $m[1] );
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants'][ $variant ]['ttf'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								} else {
									BoldThemesFramework::$custom_fonts[ $font_name ]['variants']['regular']['ttf'] = $base_uri . '/assets/custom-fonts/' . $font_name . '/' . $file_name;
								}
							}
						}
					}
				}
			}
		}
	}
}

$template_dir = get_template_directory();
$stylesheet_dir = get_stylesheet_directory();
if ( $template_dir != $stylesheet_dir ) {
	boldthemes_get_custom_fonts( array( 'base_dir' => $template_dir, 'base_uri' => get_template_directory_uri() ) );
	boldthemes_get_custom_fonts( array( 'base_dir' => $stylesheet_dir, 'base_uri' => get_stylesheet_directory_uri() ) );
} else {
	boldthemes_get_custom_fonts( array( 'base_dir' => $stylesheet_dir, 'base_uri' => get_stylesheet_directory_uri() ) );
}

/**
 * Post media html
 */

if ( ! function_exists( 'boldthemes_get_new_media_html' ) ) {
	function boldthemes_get_media_html( $arg = array() ) { 
	
		$permalink = get_permalink( get_the_ID() );
		$title = get_the_title( get_the_ID() );

		$featured_image = '';
		$featured_image_caption = '';
		
		if ( ( has_post_thumbnail() && boldthemes_get_option( 'default_headline_height' ) == 'none' ) || !is_single()  ) {
			$post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
			$image = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
			if ( $image ) {
				$featured_image = $image[0];
				$featured_image_caption = get_the_post_thumbnail_caption( get_the_ID() );	
			}
		}
		
		$images = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_images', 'type=image' );
		if ( $images == null ) $images = array();
		$grid_gallery_columns = intval( boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_grid_gallery' ) );
		$gallery_type = $grid_gallery_columns == 0 ? 'carousel' : 'grid'; 
		
		$video = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_video' );
		$audio = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_audio' );
		$link_title = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_link_title' );
		$link_url = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_link_url' );
		$quote = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_quote' );
		$quote_author = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_quote_author' );

		$images = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_images', 'type=image&size=large' );
		if ( $images == null ) $images = array();		
		
		$html = '';
		
		if ( $video != '' ) {

			if ( strpos( $video, '</ifra' . 'me>' ) > 0 ) {
				$html = '<div class="media-box media-box-iframe video">' . wp_kses( $video, 'video' ) . '</div>';
			} else {
				
				$hw = 9 / 16;
				
				$html = '<div class="media-box media-box-iframe video" data-hw="' . esc_attr( $hw ) . '"><div class="bt-video-container">';

				if ( strpos( $video, 'vimeo.com/' ) > 0 ) {
					$video_id = substr( $video, strpos( $video, 'vimeo.com/' ) + 10 );
					$html .= '<ifra' . 'me src="' . esc_url( 'https://player.vimeo.com/video/' . $video_id ) . '" allowfullscreen></ifra' . 'me>';
				} else {
					$yt_id_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
					$youtube_id = ( preg_replace( $yt_id_pattern, '$1', $video ) );
					if ( strlen( $youtube_id ) == 11 ) {
						$html .= '<ifra' . 'me width="560" height="315" src="' . esc_url( 'https://www.youtube.com/embed/' . $youtube_id ) . '" allowfullscreen></ifra' . 'me>';
					} else {
						$html = '<div class="media-box media-box-shortcode video" data-hw="' . esc_attr( $hw ) . '"><div class="bt-video-container">';				
						$html .= do_shortcode( $video );
					}
				}
				$html .= '</div></div>';
			}
		} else if ( $audio != '' ) {
		
			if ( strpos( $audio, '</ifra' . 'me>' ) > 0 ) {
				$html = '<div class="media-box media-box-iframe audio">' . wp_kses( $audio, 'audio' ) . '</div>';
			} else {
				$html = '<div class="media-box media-box-shortcode audio">' . do_shortcode( '[audio src=' . esc_url( $audio ) . ']' ) . '</div>';
			}
	
		} else if ( $link_url != '' ) {
			$bt_link_bg_image = '';
			if ( $featured_image != '' ) $bt_link_bg_image .= " style='background-image: url(" . esc_url( $featured_image ) . ")'";
			$html = '<div class="media-box link"' . $bt_link_bg_image . '><blockquote><p><a href="' . esc_url( $link_url ) . '">' . $link_title . '</a></p><cite><a href="' . esc_url( $link_url ) . '">' . $link_url . '</a></cite></blockquote></div>';
	
		} else if ( $quote != '' ) {
			$bt_quote_bg_image = '';
			if ( $featured_image != '' ) $bt_quote_bg_image .= " style='background-image: url(" . esc_url( $featured_image ) . ")'";
			$html = $quote;
			if ( $permalink != '' ) $html = '<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( $title ) . '">' . $html .'</a>';
			$html = '<div class="media-box quote"' . $bt_quote_bg_image . '><blockquote><p>' . $html . '</p><cite>' . $quote_author . '</cite></blockquote></div>';
	
		} else if ( count( $images ) > 0 ) {
			if ( $gallery_type == 'grid' && is_single() ) {
				$html = '<div class="media-box">';
						$image_ids = array();
						foreach( $images as $image ) {
							$image_ids[] = $image['ID'];
						}
						$prefix = boldthemes_get_prefix();
					if ( shortcode_exists( 'bt_bb_css_image_grid' ) ) {
						$html .= do_shortcode( '[bt_bb_css_image_grid images="' . implode( ',', $image_ids ) . '" columns="' . esc_attr( $grid_gallery_columns ) .  '" gap="inherit" ignore_fe_editor="true" img_base_size="boldthemes_medium_square"]' );
					} else {
						$html .= do_shortcode( '[bt_bb_masonry_image_grid images="' . implode( ',', $image_ids ) . '" columns="' . esc_attr( $grid_gallery_columns ) .  '" gap="inherit" shape="inherit" ignore_fe_editor="true" img_base_size="boldthemes_medium_square"]' );						
					}
				$html .= '</div>';
			} else {
				$html = '<div class="media-box">';
					if ( shortcode_exists( 'bt_bb_slider' ) ) {
						$image_urls = array();
						$image_ids = array();
						foreach( $images as $image ) {
							$image_urls[] = $image['url'];
							$image_ids[] = $image['ID'];
						}
						if ( !is_single() ) $image_ids = array_slice( $image_ids, 0, BoldThemes_Customize_Default::$data['images_in_slider'] );
						$html .= do_shortcode( '[bt_bb_slider images="' . implode( ',', $image_ids ) . '" size="boldthemes_large_rectangle" show_dots="bottom" height="auto" auto_play="" animation="fade" ignore_fe_editor="true"]' );
					}
				$html .= '</div>';
			}
		} else if ( $featured_image != '' ) {	
			$html = '<img src="' . esc_url( $featured_image ) . '" alt="' . esc_attr( wp_strip_all_tags( $title ) ) . '"/>';
			if ( $permalink != '' ) $html = '<a href="' . esc_url( $permalink ) . '" title="' . esc_attr( wp_strip_all_tags( $title ) ) . '">' . $html .'</a>';
			if ( $featured_image_caption != '' ) {
				$html .= '<div class="media-box-caption">' . $featured_image_caption . '</div>';
			} 
			$html = '<div class="media-box">' . $html . '</div>';
		}

		return $html;
		
	}
}

/**
 * Returns logo depending on a position
 */
if ( ! function_exists( 'boldthemes_logo' ) ) {
	function boldthemes_logo( $position = 'logo'  ) {
		$logo = boldthemes_get_option( $position );
		if ( $logo == '' ) { 
			// get alternative for logo
			switch ( $position ) {
				case 'sticky_logo' : 				
					$logo = boldthemes_get_option( 'logo'  );
					break;
				case 'responsive_menu_logo' : 		
					$logo = boldthemes_get_option( 'logo'  );
					break;
				case 'responsive_logo' : 			
					$logo = boldthemes_get_option( 'logo'  );
					break;
				case 'responsive_sticky_logo' : 			
					$logo = boldthemes_get_option( 'responsive_logo' ) != '' ? boldthemes_get_option( 'responsive_logo' ) : boldthemes_get_option( 'sticky_logo' );
					break;
			}
		}
		if ( ! is_string( $logo ) ) { // erased from disk
			$logo = '';
		}
		if ( $logo != '' ) {
			$image_id = 0;
			if( is_numeric( $logo ) ) {
				$image_id = $logo + 0;
			} else {
				$tmp = $logo;
				if ( strpos( $logo, '/wp-content' ) === 0 ) {
					$logo = get_home_url() . $logo;
				}
				$image_id = attachment_url_to_postid( $logo );
				if ( $image_id == 0 ) {
					$logo = $tmp;
					$image_id = attachment_url_to_postid( $logo );
				}
			}
			if( $image_id > 0) {
				
				$image_html = wp_get_attachment_image( $image_id, 'full', false, array( 'class' => str_replace( '_', '-', $position ) . '-img' ) );
				if ( $image_html == '' ) {
					$logo = '';
				}
			} else {
				$logo = '';
			}
		}

		$output = '';
		$home_link =  home_url( '/' ) ;
		if ( ( $logo != '' && $logo != ' ' ) ) {
			$output = $image_html;
		}
		if ( ( $logo != '' && $logo != ' ' ) || is_customize_preview() ) {
			$output = '<a href="' . esc_url( $home_link ) . '" class="' . str_replace( '_', '-', $position ) . '">' . $output . '</a>';			
		}
		return $output;
	}
}

/**
 * Pagination output for post archive
 */
if ( ! function_exists( 'boldthemes_pagination' ) ) {
	function boldthemes_pagination() {
	
		$prev = get_previous_posts_link( esc_html__( 'Newer Posts', 'campo' ) );
		$next = get_next_posts_link( esc_html__( 'Older Posts', 'campo' ) );
		
		$extra_slass = '';
		if ( boldthemes_get_option( 'blog_list_view' ) == 'columns' ) {
			$extra_slass = 'btPostListColumns';
		} 
		
		$pattern = '/(<a href=".*">)(.*)(<\/a>)/';
		
		if ( $prev != '' || $next != '' ) {
			echo '<div class="archive-pagination ' . $extra_slass . '">';
				if ( $prev != '' ) {
					echo '<div class="paging paging-left">';
						echo '<p>';
							echo preg_replace( $pattern, '<span class="paging-item"><span class="paging-title">$2</span></span>', $prev );
						echo '</p>';
					echo '</div>';
				}
				if ( $next != '' ) {
					echo '<div class="paging paging-right">';
						echo '<p>';
							echo preg_replace( $pattern, '<span class="paging-item"><span class="paging-title">$2</span></span>', $next );
						echo '</p>';
					echo '</div>';
				}
			echo '</div>';			
		}

	}
}

/**
 * Custom comments HTML output
 */
if ( ! function_exists( 'boldthemes_theme_comment' ) ) {
	function boldthemes_theme_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		
		$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
		
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
			// Display trackbacks differently than normal comments.
		?>
		<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
			<p><?php echo esc_html__( 'Pingback:', 'campo' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( esc_html__( '(Edit)', 'campo' ), '<span class="edit-link">', '</span>' ); ?></p>
		<?php
				break;
			default :
			// Proceed with normal comments.
			global $post;
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class = "">
				<?php $avatar_html = get_avatar( $comment, 140 ); 
					if ( $avatar_html != '' ) {
						echo '<div class="commentAvatar">' . wp_kses( $avatar_html, 'avatar' ) . '</div>';
					}
				?>
				<div class="commentTxt">
					<div class="vcard divider">
						<?php
							printf( '<h5 class="author"><span class="fn">%1$s</span></h5>', get_comment_author_link() );
							echo '<p class="posted">' . sprintf( esc_html__( '%1$s at %2$s', 'campo' ), get_comment_date(), get_comment_time() ) . '</p>';
							if ( $rating > 0 && get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) { ?>
								<div itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf( esc_html__( 'Rated %d out of 5', 'campo' ), $rating ) ?>">
									<span style="width:<?php echo esc_html( ( $rating / 5 ) * 100 ); ?>%"><strong itemprop="ratingValue"><?php echo esc_html( $rating ); ?></strong> <?php echo esc_html__( 'out of 5', 'campo' ); ?></span>
								</div>
							<?php }
						?>
					</div>

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php echo esc_html__( 'Your comment is awaiting moderation.', 'campo' ); ?></p>
					<?php endif; ?>

					<div class="comment">
						

						<?php comment_text();

						if ( comments_open() ) {
							echo '<p class="reply">';
								comment_reply_link( array_merge( $args, array( 'reply_text' => esc_html__( 'Reply', 'campo' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );
							echo '</p>';
						}
						edit_comment_link( esc_html__( 'Edit', 'campo' ), '<p class="edit-link">', '</p>' ); ?>
					</div>
				</div>
				
				
			</article>
		<?php
			break;
		endswitch;
	}
}

/**
 * Custom MetaBox getter function
 *
 * @return string
 */
if ( ! function_exists( 'boldthemes_rwmb_meta' ) ) {
	function boldthemes_rwmb_meta( $key, $args = array(), $post_id = null ) {
		if ( function_exists( 'rwmb_meta' ) ) {
			return rwmb_meta( $key, $args, $post_id );
		} else {
			return null;
		}
	}
}

/**
 * Custom MetaBox input used for Override Global Settings
 */
if ( ! class_exists( 'RWMB_BoldThemesText_Field' ) && class_exists( 'RWMB_Field' ) ) {
	class RWMB_BoldThemesText_Field extends RWMB_Field {
		
		static $sections = array();
	
		static function admin_enqueue_scripts() {
			wp_enqueue_script( 
				'boldthemes-framework-text',
				get_parent_theme_file_uri( 'framework/assets/js/boldthemes_text.js' ),
				array( 'jquery' ),
				'',
				true
			);
			$fake_customizer_js = 'window.bt_fake_customizer_controls={};';
			if ( is_array( BoldThemesFramework::$fake_customizer->control_arr ) ) {
				foreach( BoldThemesFramework::$fake_customizer->control_arr as $k => $v ) {
					$fake_customizer_js .= 'window.bt_fake_customizer_controls["' . $k . '"]=' . json_encode( $v ) . ';';
				}
			}
			wp_add_inline_script( 'boldthemes-framework-text', $fake_customizer_js );
			wp_localize_script( 'boldthemes-framework-text', 'boldthemes_text_translate',
				array( 
					'multiple' => esc_html__( 'Hold CTRL to select multiple options', 'campo' ),
					'media' => esc_html__( 'Open media library', 'campo' ),
				)
			);
		}
		
		static function sort_fake_customizer_controls( $a, $b ) {
			if ( isset( $a['priority'] ) && isset( $b['priority'] ) ) {
				if ( $a['priority'] == $b['priority'] ) {
					return 0;
				}
				return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
			} else {
				return 0;
			}
		}
		
		// static function sort_fake_customizer_subsections( $a, $b ) {
			// $sections = BoldThemesFramework::$fake_customizer->section_arr;
			// if ( isset( $sections[ $a['section'] ]['panel'] ) && isset( $sections[ $b['section'] ]['panel'] ) && $sections[ $a['section'] ]['panel'] == $sections[ $b['section'] ]['panel'] ) {
				// if ( $sections[ $a['section'] ]['priority'] == $sections[ $b['section'] ]['priority'] ) {
					// return 0;
				// }
				// return ( $sections[ $a['section'] ]['priority'] < $sections[ $b['section'] ]['priority'] ) ? -1 : 1;
			// }
			// return 0;
		// }

		// static function sort_fake_customizer_sections_panels( $a, $b ) {
			// $sections = BoldThemesFramework::$fake_customizer->section_arr;
			// $panels = BoldThemesFramework::$fake_customizer->panel_arr;
			// if ( ! isset( $sections[ $a['section'] ]['panel'] ) && ! isset( $sections[ $b['section'] ]['panel'] ) ) {
				// if ( $sections[ $a['section'] ]['priority'] == $sections[ $b['section'] ]['priority'] ) {
					// return 0;
				// }
				// return ( $sections[ $a['section'] ]['priority'] < $sections[ $b['section'] ]['priority'] ) ? -1 : 1;
			// } else if ( ! isset( $sections[ $a['section'] ]['panel'] ) && isset( $sections[ $b['section'] ]['panel'] ) ) {
				// if ( isset( $panels[ $sections[ $b['section'] ]['panel'] ] ) && $panels[ $sections[ $b['section'] ]['panel'] ]['priority'] == $sections[ $a['section'] ]['priority'] ) {
					// return 0;
				// }
				// if ( isset( $panels[ $sections[ $b['section'] ]['panel'] ] ) ) {
					// return ( $sections[ $a['section'] ]['priority'] < $panels[ $sections[ $b['section'] ]['panel'] ]['priority'] ) ? -1 : 1;
				// }
				// return 0;
			// } else if ( ! isset( $sections[ $b['section'] ]['panel'] ) && isset( $sections[ $a['section'] ]['panel'] ) ) {
				// if ( isset( $panels[ $sections[ $a['section'] ]['panel'] ] ) && $panels[ $sections[ $a['section'] ]['panel'] ]['priority'] == $sections[ $b['section'] ]['priority'] ) {
					// return 0;
				// }
				// if ( isset( $panels[ $sections[ $a['section'] ]['panel'] ] ) ) {
					// return ( $sections[ $b['section'] ]['priority'] > $panels[ $sections[ $a['section'] ]['panel'] ]['priority'] ) ? -1 : 1;
				// }
				// return 0;
			// } else if ( isset( $sections[ $a['section'] ]['panel'] ) && isset( $sections[ $b['section'] ]['panel'] ) ) {
				// if ( isset( $panels[ $sections[ $a['section'] ]['panel'] ] ) && isset( $panels[ $sections[ $b['section'] ]['panel'] ] ) ) {
					// if ( $panels[ $sections[ $a['section'] ]['panel'] ]['priority'] == $panels[ $sections[ $b['section'] ]['panel'] ]['priority'] ) {
						// return 0;
					// }
					// return ( $panels[ $sections[ $a['section'] ]['panel'] ]['priority'] < $panels[ $sections[ $b['section'] ]['panel'] ]['priority'] ) ? -1 : 1;
				// }
				// return 0;
			// }
			// return 0;
		// }
		
		static function sort_fake_customizer_sections_panels( $a, $b ) {
			if ( isset( self::$sections[ $a['section'] ]['priority'] ) && isset( self::$sections[ $b['section'] ]['priority'] ) ) {
				if ( self::$sections[ $a['section'] ]['priority'] == self::$sections[ $b['section'] ]['priority'] ) {
					return 0;
				}
				return ( self::$sections[ $a['section'] ]['priority'] < self::$sections[ $b['section'] ]['priority'] ) ? -1 : 1;
			}
			return 0;
		}

		static function stable_uasort( & $array, $cmp_function ) {
			if ( count( $array ) < 2 ) {
				return;
			}
			$halfway = round( count( $array ) / 2 );
			$array1 = array_slice( $array, 0, $halfway, TRUE );
			$array2 = array_slice( $array, $halfway, NULL, TRUE );

			self::stable_uasort( $array1, $cmp_function );
			self::stable_uasort( $array2, $cmp_function );
			if ( call_user_func( $cmp_function, end( $array1 ), reset( $array2 ) ) < 1 ) {
				$array = $array1 + $array2;
				return;
			}
			$array = array();
			reset( $array1 );
			reset( $array2 );
			while( current( $array1 ) && current( $array2 ) ) {
				if ( call_user_func( $cmp_function, current( $array1 ), current( $array2 ) ) < 1 ) {
					$array[ key( $array1 ) ] = current( $array1 );
					next( $array1 );
				} else {
					$array[ key( $array2 ) ] = current( $array2 );
					next( $array2 );
				}
			}
			while( current( $array1 ) ) {
				$array[ key( $array1 ) ] = current( $array1 );
				next( $array1 );
			}
			while( current( $array2 ) ) {
				$array[ key( $array2 ) ] = current( $array2 );
				next( $array2 );
			}
			return;
		}

		static function html( $meta, $field ) {

			$meta_key = substr( $meta, 0, strpos( $meta, ':' ) );
			$meta_value = substr( $meta, strpos( $meta, ':' ) + 1 );

			$vars = BoldThemesFramework::$fake_customizer->control_arr;
			self::stable_uasort( $vars, array( 'self', 'sort_fake_customizer_controls' ) );
			
			$unsorted_sections = BoldThemesFramework::$fake_customizer->section_arr;
			$panels = BoldThemesFramework::$fake_customizer->panel_arr;
			foreach( $unsorted_sections as $k => $v ) {
				if ( isset( $v['panel'] ) && isset( $panels[ $v['panel'] ] ) ) {
					$v['priority'] = $panels[ $v['panel'] ]['priority'] + $v['priority'] * .1; 
				}
				self::$sections[ $k ] = $v;
			}
			
			self::stable_uasort( $vars, array( 'self', 'sort_fake_customizer_sections_panels' ) );
			
			$select = '<input type="text" class="boldthemes_key_filter" placeholder="' . esc_html__( 'Filter options...', 'campo' ) . '">';
			$select .= '<select class="boldthemes_key_select" data-pfx="' . BoldThemesFramework::$pfx . '">';
			$select .= '<option value=""></option>';
			$select .= '<optgroup class="boldthemes_key_select_dummy_optgroup">';
			if ( is_array( $vars ) ) {
				foreach ( $vars as $key => $v ) {
					if ( isset( BoldThemesFramework::$fake_customizer->control_arr[ $key ] ) && isset( BoldThemesFramework::$fake_customizer->section_arr[ BoldThemesFramework::$fake_customizer->control_arr[ $key ]['section'] ] ) && BoldThemesFramework::$current_override_section != BoldThemesFramework::$fake_customizer->section_arr[ BoldThemesFramework::$fake_customizer->control_arr[ $key ]['section'] ]['title'] ) {
						BoldThemesFramework::$current_override_section = BoldThemesFramework::$fake_customizer->section_arr[ BoldThemesFramework::$fake_customizer->control_arr[ $key ]['section'] ]['title'];
						$select .= '</optgroup>' . '<optgroup label="' . BoldThemesFramework::$current_override_section . '">'; ;
					}
					$selected_html = '';
					$title = esc_html( BoldThemesFramework::$fake_customizer->control_arr[ $key ]['label'] );
					if ( BoldThemesFramework::$pfx . '_' . $key == $meta_key ) {
						$selected_html = 'selected="selected"';
						$title .= ' / ' . BoldThemesFramework::$current_override_section;
					}
					if ( isset( BoldThemesFramework::$fake_customizer->control_arr[ $key ] ) ) {
						$select .= '<option value="' . esc_attr( BoldThemesFramework::$pfx . '_' . $key ) . '" ' . $selected_html . '>' . $title . '</option>';
					}
				}
			}
			$select .= '</optgroup >';
			$select .= '</select>';
	
			$input = '<input type="text" class="boldthemes_value" value="' . esc_attr( $meta_value ) . '">';

			$part_meta_key = str_replace( BoldThemesFramework::$pfx . '_', '', $meta_key );

			if ( isset( BoldThemesFramework::$fake_customizer->control_arr[ $part_meta_key ] ) ) {
				if ( isset( BoldThemesFramework::$fake_customizer->control_arr[ $part_meta_key ]['type'] ) ) {
					$type = BoldThemesFramework::$fake_customizer->control_arr[ $part_meta_key ]['type'];
					if ( $type == 'checkbox' ) {
						$input = $meta_value == 'true' ? '<input type="checkbox" class="boldthemes_value" checked>' : '<input type="checkbox" class="boldthemes_value">';
					} else if ( $type == 'select' ) {
						$input = '<select class="boldthemes_value">';
							foreach( BoldThemesFramework::$fake_customizer->control_arr[ $part_meta_key ]['choices'] as $k => $v ) {
								if ( $meta_value == $k ) {
									$input .= '<option value="' . esc_attr( $k ) . '" selected>' . $v . '</option>';
								} else {
									$input .= '<option value="' . esc_attr( $k ) . '">' . $v . '</option>';
								}
							}
						$input .= '</select>';
					} else if ( $type == 'BoldThemes_Customize_Multiple_Select_Control' ) {
						$input = '<select class="boldthemes_value" multiple title="' . esc_html__( 'Hold CTRL to select multiple options', 'campo' ) . '">';
							$selected = explode( ',', $meta_value );
							foreach( BoldThemesFramework::$fake_customizer->control_arr[ $part_meta_key ]['choices'] as $k => $v ) {
								if ( in_array( $k, $selected ) ) {
									$input .= '<option value="' . esc_attr( $k ) . '" selected>' . $v . '</option>';
								} else {
									$input .= '<option value="' . esc_attr( $k ) . '">' . $v . '</option>';
								}
							}
						$input .= '</select>';
					} else if ( $type == 'BoldThemes_Customize_Textarea_Control' ) {
						$input = '<textarea rows="5" class="boldthemes_value">' . esc_textarea( $meta_value ) . '</textarea>';
					}  else if ( $type == 'WP_Customize_Color_Control' ) {
						$input = '<input type="text" class="boldthemes_value boldthemes_override_color_field" value="' . esc_attr( $meta_value ) . '">';
					} else if ( $type == 'WP_Customize_Image_Control' ) {
						$input = '<input type="text" class="boldthemes_value" value="' . esc_attr( $meta_value ) . '">';
						$src = '';
						if ( is_numeric( $meta_value ) ) {
							$image = wp_get_attachment_image_src( $meta_value, 'medium' );
							if ( $image ) {
								$src = $image[0];
							}
						} else {
							$src = $meta_value;
						}
						if ( $src != '' ) {
							$input .= '<img src="' . esc_url( $src ) . '"><span class="boldthemes_override_add_image" title="' . esc_html__( 'Open media library', 'campo' ) . '"><i class="dashicons dashicons-plus-alt"></i></span>';
						} else {
							$input .= '<span class="boldthemes_override_add_image" title="' . esc_html__( 'Open media library', 'campo' ) . '"><i class="dashicons dashicons-plus-alt"></i></span>';
						}
					} else if ( $type == 'BoldThemes_Customize_Iconpicker_Control' ) {
						$input = '<div class="bt_bb_iconpicker_widget_placeholder" data-icon="' . esc_attr( $meta_value ) . '"></div>';
					}
				}
			}

			$input = ' ' . $input;
			
			return sprintf(
				'<input type="hidden" class="rwmb-text" name="%s" id="%s" value="%s" placeholder="%s" %s>%s',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['placeholder'],
				'',
				self::datalist_html( $field )
			) . $select . $input;
		}

		static function normalize_field( $field ) {
			$field = wp_parse_args( $field, array(
				'size'        => 30,
				'datalist'    => false,
				'placeholder' => '',
			) );
			return $field;
		}

		static function datalist_html( $field ) {
			return '';
		}
	}
}

/**
 * Custom MetaBox input used for custom key-value pairs
 */
if ( ! class_exists( 'RWMB_BoldThemesText1_Field' ) && class_exists( 'RWMB_Field' ) ) {
	class RWMB_BoldThemesText1_Field extends RWMB_Field {
	
		static function admin_enqueue_scripts() {
			wp_enqueue_script( 
				'boldthemes-framework-text',
				get_parent_theme_file_uri( 'framework/assets/js/boldthemes_text.js' ),
				array( 'jquery' ),
				'',
				true
			);
		}

		static function html( $meta, $field ) {
		
			$meta_key = substr( $meta, 0, strpos( $meta, ':' ) );
			$meta_value = substr( $meta, strpos( $meta, ':' ) + 1 );
			
			$key_input = '<input type="text" class="boldthemes_key" value="' . esc_attr( $meta_key ) . '">';
			
			$input = ' <input type="text" class="boldthemes_value" value="' . esc_attr( $meta_value ) . '">';
			
			return sprintf(
				'<input type="hidden" class="rwmb-text" name="%s" id="%s" value="%s" placeholder="%s" %s>%s',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['placeholder'],
				'',
				self::datalist_html( $field )
			) . $key_input . $input;
		}
		
		static function normalize_field( $field ) {
			$field = wp_parse_args( $field, array(
				'size'        => 30,
				'datalist'    => false,
				'placeholder' => '',
			) );
			return $field;
		}

		static function datalist_html( $field ) {
			return '';
		}
	}
}

/**
 * Get array of data for a range of posts, used in grid layout
 *
 * @param int $number
 * @param int $offset
 * @param string $cat_slug Category slug
 * @param string $post_type
 * @param string $related
 * @return array Array of data for a range of posts
 */
if ( ! function_exists( 'boldthemes_get_posts_data' ) ) {
	function boldthemes_get_posts_data( $number, $offset, $cat_slug, $post_type = 'blog' ) {
		
		$posts_data1 = array();
		$posts_data2 = array();
		
		$sticky = false;
		
		if ( $offset == 0 && $sticky && count( $sticky_array ) > 0 ) {
			$recent_posts_q_sticky = new WP_Query( array( 'post__in' => $sticky_array, 'post_status' => 'publish' ) );
			$posts_data1 = boldthemes_get_posts_array( $recent_posts_q_sticky, $post_type, array() );
		}
		
		if ( $number > 0 ) {
			if ( $post_type == 'portfolio' ) {
				if ( $cat_slug != '' ) {
					$recent_posts_q = new WP_Query( array( 'post_type' => 'portfolio', 'posts_per_page' => $number, 'offset' => $offset, 'tax_query' => array( array( 'taxonomy' => 'portfolio_category', 'field' => 'slug', 'terms' => explode( ',', $cat_slug ) ) ), 'post_status' => 'publish' ) );
				} else {
					$recent_posts_q = new WP_Query( array( 'post_type' => 'portfolio', 'posts_per_page' => $number, 'offset' => $offset, 'post_status' => 'publish' ) );
				}
			} else {
				if ( $cat_slug != '' ) {
					$recent_posts_q = new WP_Query( array( 'posts_per_page' => $number, 'offset' => $offset, 'category_name' => $cat_slug, 'post_status' => 'publish' ) );
				} else {
					$recent_posts_q = new WP_Query( array( 'posts_per_page' => $number, 'offset' => $offset, 'post_status' => 'publish' ) );
				}
			}
		}

		if ( $sticky ) {
			$posts_data2 = boldthemes_get_posts_array( $recent_posts_q, $post_type, $sticky_array );
		} else {
			$posts_data2 = boldthemes_get_posts_array( $recent_posts_q, $post_type, array() );
		}		

		return array_merge( $posts_data1, $posts_data2 );

	}
}

/**
 * boldthemes_get_posts_data helper function
 *
 * @param object
 * @param array 
 * @return array 
 */
if ( ! function_exists( 'boldthemes_get_posts_array' ) ) {
	function boldthemes_get_posts_array( $recent_posts_q, $post_type, $sticky_arr ) {
		
		$posts_data = array();

		while ( $recent_posts_q->have_posts() ) {
			$recent_posts_q->the_post();
			$post = get_post();
			$post_author = $post->post_author;
			$post_id = get_the_ID();
			if ( in_array( $post_id, $sticky_arr ) ) {
				continue;
			}
			$posts_data[] = boldthemes_get_posts_array_item( $post_type, $post_id, $post_author );
		}
		
		wp_reset_postdata();
		
		return $posts_data;
	}
}

/**
 * boldthemes_get_posts_array helper function
 *
 * @return array
 */
if ( ! function_exists( 'boldthemes_get_posts_array_item' ) ) {
	function boldthemes_get_posts_array_item( $post_type, $post_id, $post_author ) {
		
		$post_data = array();
		$post_data['permalink'] = get_permalink( $post_id );
		$post_data['format'] = get_post_format( $post_id );
		$post_data['title'] = get_the_title( $post_id );
		
		$post_data['excerpt'] = boldthemes_get_the_excerpt( $post_id );
		if ( ! has_filter( 'get_the_time' ) ) {
			$post_data['date'] = date_i18n( BoldThemesFramework::$date_format, strtotime( get_the_time( 'Y-m-d G:m', $post_id ) ) );	
		} else {
			$post_data['date'] =  get_the_time( 'Y-m-d G:m', $post_id ) ;
		}
		
		$user_data = get_userdata( $post_author );
		if ( $user_data ) {
			$author = $user_data->data->display_name;
			$author_url = get_author_posts_url( $post_author );
			$post_data['author'] = '<a href="' . esc_url( $author_url ) . '" class="btArticleAuthorURL">' . esc_html( $author ) . '</a>';
			$post_data['author_id'] = $user_data->data->ID;
			$post_data['author_url'] = $author_url;
			$post_data['author_name'] = $author;
		} else {
			$post_data['author'] = '';
			$post_data['author_id'] = '';
			$post_data['author_url'] = '';
			$post_data['author_name'] = '';
		}

		if ( $post_type == 'portfolio' ) {
			$categories = wp_get_post_terms( $post_id, 'portfolio_category' );
		} else {
			$categories = get_the_category( $post_id );
		}
		
		if ( $post_type == 'portfolio' ) {
			$categories_html = boldthemes_get_post_categories( array( 'categories' => $categories ) );
		} else {
			$categories_html = boldthemes_get_post_categories( array( 'categories' => $categories ) );
		}

		$post_data['category'] = $categories_html;
		
		$comments_open = comments_open( $post_id );
		$comments_number = get_comments_number( $post_id );
		if ( ! $comments_open && $comments_number == 0 ) {
			$comments_number = false;
		}			
		
		$post_data['thumbnail'] = get_post_thumbnail_id( $post_id );
		$post_data['images'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_images', 'type=image', $post_id );
		if ( $post_data['images'] == null ) $post_data['images'] = array();
		$post_data['video'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_video', array(), $post_id );
		$post_data['audio'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_audio', array(), $post_id );
		$post_data['grid_gallery'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_grid_gallery', array(), $post_id );
		$post_data['link_title'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_link_title', array(), $post_id );
		$post_data['link_url'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_link_url', array(), $post_id );
		$post_data['quote'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_quote', array(), $post_id );
		$post_data['quote_author'] = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_quote_author', array(), $post_id );
		$post_data['comments'] = $comments_number;
		$post_data['ID'] = $post_id;
		
		return $post_data;
	}
}

/**
 * Returns page id by slug
 *
 * @return string
 */
if ( ! function_exists( 'boldthemes_get_id_by_slug' ) ) {
	function boldthemes_get_id_by_slug( $page_slug ) {
		$page = get_posts(
			array(
				'name'      => $page_slug,
				'post_type' => 'page'
			)
		);
		if ( isset($page[0]->ID) ) {
			return $page[0]->ID;	
		} else {
			return null;
		}
		
	}
}

/**
 * Creates override of global options for individual posts
 */
if ( ! function_exists( 'boldthemes_set_override' ) ) {
	function boldthemes_set_override() {
		
		$options = BoldThemesFramework::$options;
		$tmp_boldthemes_post_options1 = '';
		
		if ( is_singular() ) {
			$tmp_boldthemes_post_options = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override' );
		} else {
			$tmp_boldthemes_post_options = array();
		}
		if ( ! is_array( $tmp_boldthemes_post_options ) ) {
			$tmp_boldthemes_post_options = array();
		}
		$tmp_boldthemes_post_options = boldthemes_transform_override( $tmp_boldthemes_post_options );
		
		
		if ( ( is_archive() || is_home() ) && get_option( 'page_for_posts' ) != 0 ) {
			$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), get_option( 'page_for_posts' ) );
			BoldThemesFramework::$page_for_header_id = get_option( 'page_for_posts' );
		}
		
		if ( is_search() ) {
			if ( isset( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_search_settings_page_slug'] ) && $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_search_settings_page_slug'] != '' ) { 
				// override with override
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_search_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_search_settings_page_slug' ] );
			} else if ( isset( $options['search_settings_page_slug'] ) && $options['search_settings_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['search_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['search_settings_page_slug'] );
			}
		}
		
		if ( ( is_singular( 'post' ) || is_singular( 'attachment' ) ) && ! is_404() ) {
			if ( isset( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_blog_settings_page_slug' ] ) && $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_blog_settings_page_slug'] != '' ) { 
				// override with override
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_blog_settings_page_slug'] ) );
				if ( is_singular( 'post' ) ) BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_blog_settings_page_slug' ] );
			} else if ( isset( $options['blog_settings_page_slug'] ) && $options['blog_settings_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['blog_settings_page_slug'] ) );
				if ( is_singular( 'post' ) ) BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['blog_settings_page_slug'] );
			} else if ( BoldThemes_Customize_Default::$data['blog_settings_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( BoldThemes_Customize_Default::$data['blog_settings_page_slug'] ) );
			}
		}
	
		if ( is_404() ) {
			if ( isset( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_error_404_page_slug'] ) && $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_error_404_page_slug'] != '' ) { 
				// override with override
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_error_404_page_slug'] ) );
				// BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_error_404_page_slug' ] );
			} else if ( isset( $options['error_404_page_slug'] ) && $options['error_404_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['error_404_page_slug'] ) );
				// BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['error_404_page_slug'] );
			}
		}

		if ( is_singular( 'portfolio' ) ) {
			if ( isset( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_pf_settings_page_slug'] ) && $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_pf_settings_page_slug'] != '' ) { 
				// override with override
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_pf_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_pf_settings_page_slug' ] );
			} else if ( isset( $options['pf_settings_page_slug'] ) && $options['pf_settings_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['pf_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['pf_settings_page_slug'] );
			}
		}
			
		if ( is_post_type_archive( 'portfolio' ) || is_tax( 'portfolio_category' )) {
			if ( isset( $options['pf_slug'] ) && ! is_null( $options['pf_slug'] ) && $options['pf_slug'] != '' && ! is_null( boldthemes_get_id_by_slug( $options['pf_slug'] ) ) && boldthemes_get_id_by_slug( $options['pf_slug'] ) != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['pf_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['pf_slug'] );
			} else if ( ! is_null( boldthemes_get_id_by_slug( 'portfolio' ) ) && boldthemes_get_id_by_slug( 'portfolio' ) != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( 'portfolio' ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( 'portfolio' );
			} else if ( get_option( 'page_for_posts' ) ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), get_option( 'page_for_posts' ) );
				BoldThemesFramework::$page_for_header_id = get_option( 'page_for_posts' );
			}
			// add one class to all portfolio pages
			add_filter( 'body_class', function( $class ) { $class[] = 'post-type-archive-portfolio'; return $class; } );
		}
		
		if ( get_option( 'woocommerce_shop_page_id' ) && function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_taxonomy() ) ) {
			$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), get_option( 'woocommerce_shop_page_id' ) );
			BoldThemesFramework::$page_for_header_id = get_option( 'woocommerce_shop_page_id' );
		}
		
		if ( function_exists( 'is_product' ) && is_product() ) {
			if ( isset( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_shop_settings_page_slug'] ) && $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_shop_settings_page_slug'] != '' ) {
				// override with override
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_shop_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $tmp_boldthemes_post_options[ BoldThemesFramework::$pfx . '_shop_settings_page_slug' ] );
			} else if ( isset( $options['shop_settings_page_slug'] ) && $options['shop_settings_page_slug'] != '' ) {
				$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['shop_settings_page_slug'] ) );
				BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['shop_settings_page_slug'] );
			}
		}
		
		$post_type = get_post_type();

		if ( ( $post_type == 'tribe_events' || $post_type == 'tribe_venue' || $post_type == 'tribe_organizer' ) && isset( $options['events_settings_page_slug'] ) && $options['events_settings_page_slug'] != '' ) {
			BoldThemesFramework::$page_for_header_id = boldthemes_get_id_by_slug( $options['events_settings_page_slug'] );
			$tmp_boldthemes_post_options1 = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . '_override', array(), boldthemes_get_id_by_slug( $options['events_settings_page_slug'] ) );
		}
		
		$tmp_boldthemes_post_options1 = apply_filters( 'boldthemes_post_options_from_page', $tmp_boldthemes_post_options1 );

		if ( is_array( $tmp_boldthemes_post_options1 ) ) {
			if ( is_singular() ) {
				$tmp_boldthemes_post_options = array_merge( boldthemes_transform_override( $tmp_boldthemes_post_options1 ), $tmp_boldthemes_post_options );
			} else {
				$tmp_boldthemes_post_options = boldthemes_transform_override( $tmp_boldthemes_post_options1 );
			}
		}
		
		if ( count( $tmp_boldthemes_post_options ) > 0 ) {
			add_filter( 'body_class', function( $class ) { $class[] = 'boldthemes-has-override'; return $class; } );
		}

		foreach ( $tmp_boldthemes_post_options as $key => $value ) {
			BoldThemesFramework::$post_options[ $key ] = $value;
		}
			
		
	}
}

/**
 * boldthemes_set_override helper function
 *
 * @param array
 * @return array
 */
if ( ! function_exists( 'boldthemes_transform_override' ) ) {
	function boldthemes_transform_override( $arr ) {
		$new_arr = array();
		foreach( $arr as $item ) {
			$key = substr( $item, 0, strpos( $item, ':' ) );
			$value = substr( $item, strpos( $item, ':' ) + 1 );
			$new_arr[ $key ] = $value;
		}
		return $new_arr;
	}
}

/**
 * theme name and version in data attribute
 */
if ( ! function_exists( 'boldthemes_theme_data' ) ) {
	function boldthemes_theme_data() {
		$data = wp_get_theme();
		echo 'data-bt-theme="' . esc_attr( $data['Name'] ) . ' ' . esc_attr( $data['Version'] ) . '"';
	}
}

/**
 * Header menu output
 */
if ( ! function_exists( 'boldthemes_nav_menu' ) ) {
	function boldthemes_nav_menu( $walker = false, $theme_location = 'primary-menu' ) {
		$menu_prefix = '_' . $theme_location;
		$menu_prefix = str_replace( '-menu', '', $menu_prefix );
		$blog_page_menu = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name', array(), get_option( 'page_for_posts' ) );
		$shop_page_menu = false;
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() && get_option( 'woocommerce_shop_page_id' ) ) {
			$shop_page_menu = boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name', array(), get_option( 'woocommerce_shop_page_id' ) );
		}
		$menu_depth = ( $theme_location == 'primary-menu' ) ? apply_filters( 'boldthemes_primary_menu_depth', 4 ) : 1;
		if ( $walker ) {
			if ( is_home() && $blog_page_menu != '' ) {
				wp_nav_menu( array( 'theme_location' => $theme_location, 'menu' => $blog_page_menu, 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false, 'walker' => $walker ) ); 
			} else if ( boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name' ) != '' ) {
				wp_nav_menu( array( 'theme_location' => $theme_location, 'menu' => boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name' ), 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false, 'walker' => $walker ) ); 
			} else {
				wp_nav_menu( array( 'theme_location' => $theme_location, 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false, 'walker' => $walker ) );
			}
		} else {
			if ( is_home() && $blog_page_menu != '' ) {				
				wp_nav_menu( array( 'menu' => $blog_page_menu, 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false ) );
			} else if ( $shop_page_menu ) {
				wp_nav_menu( array( 'menu' => $shop_page_menu, 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false ) );
			} else if ( boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name' ) != '' ) {
				wp_nav_menu( array( 'menu' => boldthemes_rwmb_meta( BoldThemesFramework::$pfx . $menu_prefix . '_menu_name' ), 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'container' => 'nav', 'depth' => $menu_depth, 'fallback_cb' => false ) ); 
			} else {
				wp_nav_menu( array( 'theme_location' => $theme_location, 'menu_id' => $theme_location, 'menu_class' => 'main-navigation-menu', 'container' => 'nav', 'container_aria_label' => 'navigation', 'depth' => $menu_depth, 'fallback_cb' => false ) );
			}
		}
	}
}

/**
 * Enqueue comment script
 */
 
if ( ! function_exists( 'boldthemes_header_init' ) ) {
	function boldthemes_header_init() {
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}

/**
 * Set JS AJAX URL and JS text labels
 */
if ( ! function_exists( 'boldthemes_set_global_uri' ) ) {
	function boldthemes_set_global_uri() {
		$data = 'window.BoldThemesURI = "' . esc_js( get_parent_theme_file_uri() ) . '"; window.BoldThemesAJAXURL = "' . esc_js( admin_url( 'admin-ajax.php' ) ) . '";';
		$data .= 'window.boldthemes_text = [];';
		$data .= 'window.boldthemes_text.previous = \'' . esc_html__( 'previous', 'campo' ) . '\';';
		$data .= 'window.boldthemes_text.next = \'' . esc_html__( 'next', 'campo' ) . '\';';
		return $data;
	}
}


/**
 * Get prefix
 */
if ( ! function_exists( 'boldthemes_get_prefix' ) ) {
	function boldthemes_get_prefix( $position = 'list' ) {
		switch( get_post_type() ) {
			case "portfolio": 
				return 'pf';
				break;
			case 'product': 
				return 'shop';
				break;
			case 'post': 
				return 'blog';
				break;
			case 'page': 
				if ( !is_search() || is_archive() ) return 'page';
				else return 'blog';
				break;
			default: 
				return 'blog';
				break;
		}
	}
}


/**
 * Get post categories, wp function cloned
 * https://developer.wordpress.org/reference/functions/get_the_category/
 */
if ( ! function_exists( 'boldthemes_get_post_categories' ) ) {
	function boldthemes_get_the_category( $post_id = false, $terms_slug = 'category' ) {
		$categories = get_the_terms( $post_id, $terms_slug );
		if ( ! $categories || is_wp_error( $categories ) ) {
			$categories = array();
		}
	 
		$categories = array_values( $categories );
	 
		foreach ( array_keys( $categories ) as $key ) {
			_make_cat_compat( $categories[ $key ] );
		}
	 
		/**
		 * Filters the array of categories to return for a post.
		 *
		 * @since 3.1.0
		 * @since 4.4.0 Added `$post_id` parameter.
		 *
		 * @param WP_Term[] $categories An array of categories to return for the post.
		 * @param int|false $post_id    ID of the post.
		 */
		return apply_filters( 'get_the_categories', $categories, $post_id );
	}
}

/**
 * Get post categories list, wp function cloned
 * https://developer.wordpress.org/reference/functions/get_the_category_list/
 */
if ( ! function_exists( 'boldthemes_get_the_category_list' ) ) {
	function boldthemes_get_the_category_list( $separator = '', $parents = '', $post_id = false, $category_slug = 'category' ) {
		global $wp_rewrite;
	 
		if ( ! is_object_in_taxonomy( get_post_type( $post_id ), $category_slug ) ) {
			/** This filter is documented in wp-includes/category-template.php */
			return apply_filters( 'the_category', '', $separator, $parents );
		}
	 
		/**
		 * Filters the categories before building the category list.
		 *
		 * @since 4.4.0
		 *
		 * @param WP_Term[] $categories An array of the post's categories.
		 * @param int|bool  $post_id    ID of the post we're retrieving categories for.
		 *                              When `false`, we assume the current post in the loop.
		 */
		$categories = apply_filters( 'the_category_list', boldthemes_get_the_category( $post_id, $category_slug ), $post_id );
	 
		if ( empty( $categories ) ) {
			/** This filter is documented in wp-includes/category-template.php */
			return apply_filters( 'the_category', __( 'Uncategorized', 'campo' ), $separator, $parents );
		}
	 
		$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';
	 
		$thelist = '';
		if ( '' === $separator ) {
			$thelist .= '<ul class="post-categories">';
			foreach ( $categories as $category ) {
				$thelist .= "\n\t<li>";
				switch ( strtolower( $parents ) ) {
					case 'multiple':
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, true, $separator );
						}
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . $category->name . '</a></li>';
						break;
					case 'single':
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '"  ' . $rel . '>';
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, false, $separator );
						}
						$thelist .= $category->name . '</a></li>';
						break;
					case '':
					default:
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . $category->name . '</a></li>';
				}
			}
			$thelist .= '</ul>';
		} else {
			$i = 0;
			foreach ( $categories as $category ) {
				if ( 0 < $i ) {
					$thelist .= $separator;
				}
				switch ( strtolower( $parents ) ) {
					case 'multiple':
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, true, $separator );
						}
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . $category->name . '</a>';
						break;
					case 'single':
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>';
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, false, $separator );
						}
						$thelist .= "$category->name</a>";
						break;
					case '':
					default:
						$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . $category->name . '</a>';
				}
				++$i;
			}
		}
	 
		/**
		 * Filters the category or list of categories.
		 *
		 * @since 1.2.0
		 *
		 * @param string $thelist   List of categories for the current post.
		 * @param string $separator Separator used between the categories.
		 * @param string $parents   How to display the category parents. Accepts 'multiple',
		 *                          'single', or empty.
		 */
		return apply_filters( 'the_category', $thelist, $separator, $parents );
	}
}

/**
 * Get related posts
 */
if ( ! function_exists( 'boldthemes_get_related_posts' ) ) {
	function boldthemes_get_related_posts( $post_id = false, $num = 3 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$cat_list = '';
		$cat_arr = get_the_category( $post_id );
		if ( count( $cat_arr ) == 0 ) {
			$cat_list = 0;
		}
		foreach ( $cat_arr as $cat ) {
			$cat_list .= $cat->name . ',';
		}
		$cat_list = rtrim( $cat_list, ',' );
		$related_posts = boldthemes_get_posts_data( $num, 0, $cat_list );
		return $related_posts;
	}
}

/**
 * Remove customize setting
 */
if ( ! function_exists( 'boldthemes_remove_customize_setting' ) ) {
	function boldthemes_remove_customize_setting( $id ) {
		unset( BoldThemes_Customize_Default::$data[ $id ] );
		remove_action( 'customize_register', 'boldthemes_customize_' . $id );
		remove_action( 'boldthemes_customize_register', 'boldthemes_customize_' . $id );
	}
}

/**
 * Add meta box
 */
if ( ! function_exists( 'boldthemes_add_mb' ) ) {
	function boldthemes_add_mb( $arr ) { // id, title, post_type, autosave
		BoldThemesFramework::$meta_boxes[ $arr['id'] ] = array(
			'title'     => $arr['title'],
			'post_type' => $arr['post_type'],
			'autosave'  => $arr['autosave'],
			'fields'    => array()
		);
	}
}

/**
 * Remove meta box
 */
if ( ! function_exists( 'boldthemes_remove_meta_box' ) ) {
	function boldthemes_remove_meta_box( $id ) {
		unset( BoldThemesFramework::$meta_boxes[ $id ] );
	}
}

/**
 * Convert param to camel case
 */
if ( ! function_exists( 'boldthemes_convert_param_to_camel_case' ) ) {
	function boldthemes_convert_param_to_camel_case( $str ) {
		return str_replace( ' ', '', ucwords( str_replace( "-", " ", $str ) ) );
	}
}

/**
 * Add meta box field
 */
if ( ! function_exists( 'boldthemes_add_mb_field' ) ) {
	function boldthemes_add_mb_field( $arr ) { // mb_id, field_id, name, type, order*, options*, clone*

		BoldThemesFramework::$meta_boxes[ $arr['mb_id'] ]['fields'][ $arr['field_id'] ] = array(
			'id' => $arr['field_id']
		);

		foreach ( $arr as $k => $v ) {
			if ( $k != 'order' &&  $k != 'field_id' ) {
				BoldThemesFramework::$meta_boxes[ $arr['mb_id'] ]['fields'][ $arr['field_id'] ][ $k ] = $v;
			}
		}

		if ( isset( $arr['order'] ) ) {
			BoldThemesFramework::$meta_boxes[ $arr['mb_id'] ]['fields'][ $arr['field_id'] ]['order'] = $arr['order'];
		} else {
			BoldThemesFramework::$meta_boxes[ $arr['mb_id'] ]['fields'][ $arr['field_id'] ]['order'] = count( BoldThemesFramework::$meta_boxes[ $arr['mb_id'] ]['fields'] );
		}

	}
}

/**
 * Remove meta box field
 */
if ( ! function_exists( 'boldthemes_remove_mb_field' ) ) {
	function boldthemes_remove_mb_field( $mb_id, $field_id ) {
		unset( BoldThemesFramework::$meta_boxes[ $mb_id ][ 'fields' ][ $field_id ] );
	}
}

/**
 * Get icon fonts BB array
 */
if ( ! function_exists( 'boldthemes_get_icon_fonts_bb_array' ) ) {
	function boldthemes_get_icon_fonts_bb_array() {
		$fonts = array();
		$glob_match = glob( get_parent_theme_file_path( '/framework/assets/icon-sets/*/*.php' ) );
		if ( $glob_match ) {
			foreach( $glob_match as $file ) {
				if ( preg_match( '/([a-zA-Z0-9_-]+)\/\1.php$/', $file, $match ) ) {
					if ( substr( $match[1], 0, 1 ) != '_' ) {
						$fonts[ $match[1] ] = $file;
					}
				}
			}
		}
		
		$glob_match = glob( get_parent_theme_file_path( '/assets/icon-sets/*/*.php' ) );
		if ( $glob_match ) {
			foreach( $glob_match as $file ) {
				if ( preg_match( '/([a-zA-Z0-9_-]+)\/\1.php$/', $file, $match ) ) {
					if ( substr( $match[1], 0, 1 ) != '_' ) {
						$fonts[ $match[1] ] = $file;
					}
				}
			}
		}
	
		$icon_arr = array();

		foreach( $fonts as $key => $value ) {
			require( $value );
			$icon_arr[ $key ] = $$set;
		}

		return $icon_arr;
	}
}

/**
 * Category select fancySelect compatibility
 */
if ( ! function_exists( 'boldthemes_cat_select' ) ) {
	function boldthemes_cat_select() {
		ob_start();
		?>
			var boldthemes_dropdown = document.querySelector( ".widget_categories #cat" );
			function boldthemes_onCatChange() {
				if ( boldthemes_dropdown.options[boldthemes_dropdown.selectedIndex].value > 0 ) {
					location.href = "<?php echo esc_url( home_url( '/' ) ); ?>?cat="+boldthemes_dropdown.options[boldthemes_dropdown.selectedIndex].value;
				}
			}
			if ( boldthemes_dropdown !== null ) {
				boldthemes_dropdown.onchange = boldthemes_onCatChange;
			}
		<?php
		
		$js = ob_get_clean();
		
		wp_add_inline_script( 'boldthemes-framework-misc', $js );
	}
}

/**
 * Get post author
 */
if ( ! function_exists( 'boldthemes_get_post_author' ) ) {
	function boldthemes_get_post_author( $author_url = false, $prefix = 'by', $show_avatar = false ) {
		if ( $prefix == 'by' ) $prefix = esc_html__( 'by', 'campo' );
		$post = get_post();
		$post_author_id = $post->post_author;
		if ( ! $author_url ) {
			$author_url = get_author_posts_url( get_the_author_meta( 'ID', $post_author_id ) );
		}
		$output = '<span class="bt-article-author">';
		if ( $show_avatar ) {
			$output .= get_avatar( get_the_author_meta( 'ID' ), 80 );
		}
		$output .= '<a href="' . esc_url( $author_url ) . '" class="bt-article-author-url">' . $prefix . ' ' . esc_html( get_the_author_meta( 'display_name', $post_author_id )  ) . '</a>';
		$output .= '</span>';
		return $output;
	}
}

/**
 * Get style from color scheme
 */
 if ( ! function_exists( 'boldthemes_get_style_from_color_scheme' ) ) {
	function boldthemes_get_style_from_color_scheme( $arr ) {
		$style = '';
		$color_scheme = boldthemes_get_color_scheme_colors_by_id( $arr['color_scheme_id'] );
		if ( is_array( $color_scheme ) ) {
			$style .= $arr['css_var1'] . ':' . $color_scheme[0] . ';' . $arr['css_var2'] . ':' . $color_scheme[1] . ';';
		}
		return $style;
	}
}
 
/**
 * Get colors from color scheme
 */

if ( ! function_exists( 'boldthemes_get_color_scheme_colors_by_id' ) ) {
	function boldthemes_get_color_scheme_colors_by_id( $scheme_id ) {
		if ( !is_numeric( $scheme_id ) || intval( $scheme_id ) < 1 ) return false;
		if ( function_exists( 'bt_bb_get_color_scheme_array' ) ) {
			$color_scheme_arr = bt_bb_get_color_scheme_array(); // BB version is preferred
		} else {
			$color_scheme_arr = boldthemes_get_color_scheme_array();
		}
		if ( !isset( $color_scheme_arr[ $scheme_id - 1 ] ) ) return false;
		$color_scheme = explode( ';', $color_scheme_arr[ $scheme_id - 1 ] );
		$color_scheme = array_map( 'trim', array_slice( $color_scheme, -2, 2 ) );
		return $color_scheme;
	}
}
 