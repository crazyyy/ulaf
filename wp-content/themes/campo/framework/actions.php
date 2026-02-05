<?php

add_action( 'after_setup_theme', 'boldthemes_get_options' );
add_action( 'after_setup_theme', 'boldthemes_theme_init' );
add_action( 'after_setup_theme', 'boldthemes_image_sizes' );
add_action( 'after_setup_theme', 'boldthemes_theme_support' );
add_action( 'wp_head', 'boldthemes_get_options', 0 );
add_action( 'wp_head', 'boldthemes_customize_body_class', 1 );
add_action( 'widgets_init', 'boldthemes_widgets_init' );
add_action( 'wp_enqueue_scripts', 'boldthemes_enqueue' );
add_action( 'wp_enqueue_scripts', 'boldthemes_text_strings' );
//add_action( 'admin_enqueue_scripts', 'boldthemes_wp_admin_style' );
add_action( 'admin_enqueue_scripts', 'boldthemes_custom_fields' );
add_action( 'init', 'boldthemes_add_excerpt_to_page' );
add_action( 'wp_enqueue_scripts', 'boldthemes_cat_select' );
add_action( 'wp_head', 'boldthemes_enqueue_custom_fonts' );
add_action( 'boldthemes_body_data', 'boldthemes_body_data' );
add_action( 'boldthemes_css_vars', 'boldthemes_css_vars' );
add_action( 'wp_enqueue_scripts', 'boldthemes_customize_default_js' );
add_action( 'wp_enqueue_scripts', 'boldthemes_color_schemes_js' );
add_action( 'admin_enqueue_scripts', 'boldthemes_load_fonts' );
add_action( 'wp_enqueue_scripts', 'boldthemes_load_fonts' );

// callbacks

/**
 * Theme setup
 */
if ( ! function_exists( 'boldthemes_theme_init' ) ) {
	function boldthemes_theme_init() {  
		load_theme_textdomain( 'campo', get_parent_theme_file_path( 'languages' ) );
		
		// add theme support
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
		add_theme_support( 'post-formats', array( 'image', 'gallery', 'video', 'audio', 'link', 'quote' ) );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( "responsive-embeds" );
		add_theme_support( 'align-wide' );
		/* Switch default core markup for search form, comment form, and comments to output valid HTML5. */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary-menu' => esc_html__( 'Primary menu', 'campo' ),
			)
		);

		// Set up the WordPress core custom background feature.
		/*add_theme_support(
			'custom-background',
			apply_filters(
				'boldthemes_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);*/

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		/*add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);*/
		
		// date format
		BoldThemesFramework::$date_format = get_option( 'date_format' );
	}
}

/**
 * Image sizes
 */
if ( ! function_exists( 'boldthemes_image_sizes' ) ) {
	function boldthemes_image_sizes() {
		
		update_option( 'thumbnail_size_w', 160 );
		update_option( 'thumbnail_size_h', 160 );
		
		update_option( 'medium_size_w', 640 );
		update_option( 'medium_size_h', 0 );
		
		update_option( 'large_size_w', 1280 );
		update_option( 'large_size_h', 0 );
		
		/* Small */

		add_image_size( 'boldthemes_small', 320, 0, true );
		add_image_size( 'boldthemes_small_rectangle', 320, 240, true );
		add_image_size( 'boldthemes_small_square', 320, 320, true );
		
		/* Medium */

		add_image_size( 'boldthemes_medium', 640 );
		add_image_size( 'boldthemes_medium_rectangle', 640, 480, true );
		add_image_size( 'boldthemes_medium_square', 640, 640, true );
		
		/* Large */
		
		add_image_size( 'boldthemes_large_square', 1280, 1280, true );
		add_image_size( 'boldthemes_large_rectangle', 1280, 720, true );
		add_image_size( 'boldthemes_large_vertical_rectangle', 720, 1280, true );

	}
}

/**
 * Register sidebar and widget areas
 */
if ( ! function_exists( 'boldthemes_widgets_init' ) ) {
	function boldthemes_widgets_init() {
		register_sidebar( array (
			'name' 			=> esc_html__( 'Sidebar widgets', 'campo' ),
			'id' 			=> 'primary_widget_area',
			'description'   => esc_html__( 'Main sidebar (left or right position)', 'campo' ),
			'before_widget' => '<div class="sidebar-box %2$s" id="%1$s">',
			'after_widget' 	=> '</div>',
			'before_title' 	=> '<h4><span>',
			'after_title' 	=> '</span></h4>',
		));
		register_sidebar( array (
			'name' 			=> esc_html__( 'Sidebar shop widgets', 'campo' ),
			'id' 			=> 'primary_widget_area_shop',
			'description'   => esc_html__( 'Main sidebar in shop (left or right position)', 'campo' ),
			'before_widget' => '<div class="sidebar-box %2$s" id="%1$s">',
			'after_widget' 	=> '</div>',
			'before_title' 	=> '<h4><span>',
			'after_title' 	=> '</span></h4>',
		));
		register_sidebar( array (
			'name' 			=> esc_html__( 'Footer Widgets', 'campo' ),
			'id' 			=> 'footer_widgets',
			'description'   => esc_html__( 'Footer widgets (bottom position)', 'campo' ),
			'before_widget' => '<div class="footer-widget"><div class="%2$s" id="%1$s">',
			'after_widget' 	=> '</div></div>',
			'before_title' 	=> '<h4><span>',
			'after_title' 	=> '</span></h4>',
		));
	}
}

/**
 * Add excerpt to page
 */
if ( ! function_exists( 'boldthemes_add_excerpt_to_page' ) ) {
	function boldthemes_add_excerpt_to_page() {
		 add_post_type_support( 'page', 'excerpt' );
	}
}

/**
 * Enqueue scripts/styles
 */
if ( ! function_exists( 'boldthemes_enqueue' ) ) {
	function boldthemes_enqueue() {
		wp_enqueue_script( 'boldthemes-framework-misc', get_parent_theme_file_uri( 'framework/assets/js/bt_framework_misc.js' ), array( 'jquery' ), '', true ); // used to add inline script with wp_add_inline_script
		if ( boldthemes_get_option( 'custom_js' ) != '' ) {
			wp_add_inline_script( 'boldthemes-framework-misc', boldthemes_get_option( 'custom_js' ) );
		}
	}
}

/**
 * Admin style
 */
/*if ( ! function_exists( 'boldthemes_wp_admin_style' ) ) {
	function boldthemes_wp_admin_style() {
		wp_enqueue_style( 'boldthemes-framework-admin', get_parent_theme_file_uri( 'framework/assets/admin-css/admin-style.css' ), array(), false );
	}
}*/

/**
 * Custom fields
 */
if ( ! function_exists( 'boldthemes_custom_fields' ) ) {
	function boldthemes_custom_fields() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'boldthemes-framework-bb-custom-fields', get_parent_theme_file_uri( 'framework/assets/js/bb_custom_fields.js' ), array( 'jquery' ), '', true );
	}
}

/**
 * Admin text strings
 */
if ( ! function_exists( 'boldthemes_text_strings' ) ) {
	function boldthemes_text_strings() {
		// Register the script
		wp_register_script( 'boldthemes-text-strings', false );
		 
		// Localize the script with new data
		$translation_array = array(
			'override_alert' => esc_html__( 'This page/post is using Override Global Settings. Some options might not work as expected.', 'campo' ),
		);
		wp_localize_script( 'boldthemes-text-strings', 'boldthemes_text_strings', $translation_array );
		 
		// Enqueued script with localized data.
		wp_enqueue_script( 'boldthemes-text-strings' );
	}
}

/**
 * Alowed tags
 */
if(!function_exists('boldthemes_add_allowed_tags')) {
	function boldthemes_add_allowed_tags($tags) {
		$allowed_attributes = array(
			'class' => true,
			'id' => true,
			'class' => true,
			'target' => true,
			'title' => true,
			'src' => true,
			'style' => true,
			'data-ico-fa' => true,
			'data-ico-icon7stroke' => true,
			'id' => true,
			'href' => true
        );
		$tags['span'] = $allowed_attributes;
		$tags['div'] = $allowed_attributes;
		$tags['a'] = $allowed_attributes;
		return $tags;
	}
	add_filter( 'wp_kses_allowed_html', 'boldthemes_add_allowed_tags' );
}

/**
 * Theme options
 */
if ( ! function_exists( 'boldthemes_get_options' ) ) {
	function boldthemes_get_options() {
		BoldThemesFramework::$options = get_option( BoldThemesFramework::$pfx . '_theme_options' );
	}
}


// BODY CLASS
if ( ! function_exists( 'boldthemes_customize_body_class' ) ) {
	function boldthemes_customize_body_class() {
		foreach ( BoldThemesFramework::$customize_body_class as $option ) {
			BoldThemesFramework::$body_class[] = str_replace( '_', '-', $option . '-' . boldthemes_get_option( $option ) );
		}
		add_filter( 'body_class', function( $classes ) {
			return array_merge( $classes, BoldThemesFramework::$body_class );
		});

	}
}

/**
 * Body data attr
 */
if ( ! function_exists( 'boldthemes_body_data' ) ) {
	function boldthemes_body_data() {
		foreach ( BoldThemesFramework::$customize_body_data as $option ) {
			echo ' ' . 'data-' . str_replace( '_', '-', $option ) . '="' . boldthemes_get_option( $option ) . '"';
		}
	}
}

/**
 * CSS vars
 */
if ( ! function_exists( 'boldthemes_css_vars' ) ) {
	function boldthemes_css_vars() {
		// var_dump( BoldThemesFramework::$options );
		// var_dump( BoldThemes_Customize_Default::$data );
		// if ( BoldThemesFramework::$options ) {
			$vars = '';
			foreach ( BoldThemesFramework::$customize_css_vars as $option ) {
				
				// if ( array_key_exists( $option, BoldThemes_Customize_Default::$data ) ) {
					// $vars .= ' ' . $option . ': ' . BoldThemes_Customize_Default::$data[ $option ] . ';';
					
					/*if ( in_array( $option, BoldThemesFramework::$css_vars_background_image ) ) {
						if ( BoldThemesFramework::$options && array_key_exists( $option, BoldThemesFramework::$options ) && BoldThemesFramework::$options[ $option ] != '' && BoldThemesFramework::$options[ $option ] != 'default' ) {
							$vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':url(\'' . BoldThemesFramework::$options[ $option ] . '\');';
						} else if ( BoldThemes_Customize_Default::$data[ $option ] != '' ) {
							$vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':url(\'' . BoldThemes_Customize_Default::$data[ $option ] . '\');';
						}
					} else {
						if ( BoldThemesFramework::$options && array_key_exists( $option, BoldThemesFramework::$options ) && BoldThemesFramework::$options[ $option ] != '' && BoldThemesFramework::$options[ $option ] != 'default' ) {
							$vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':' . urldecode( BoldThemesFramework::$options[ $option ] ) . ';';
						} else if ( BoldThemes_Customize_Default::$data[ $option ] != '' ) {
							$vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':' . urldecode( BoldThemes_Customize_Default::$data[ $option ] ) . ';';
						}
							
					}*/
					
					if ( in_array( $option, BoldThemesFramework::$css_vars_background_image ) ) {
						$image = boldthemes_get_option( $option );
						if ( is_numeric( $image ) ) {
							$image = wp_get_attachment_image_src( $image, 'full' );
							$image = $image[0];				
						}
						$vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':url(\'' . $image . '\');';

					} else {
						
						$value = boldthemes_get_option( $option );
						
						if ( $value == 'default' ) {
							$value = BoldThemes_Customize_Default::$data[ $option ];
						}

						if ( $value != '' ) $vars .= ' ' . '--' . str_replace( '_', '-', $option ) . ':' . urldecode( $value ) . ';';
							
					}					
					
				// }
			}
			echo ' ' . ( $vars ) . '';
		// }
	}
}

/**
 * Customize default JS
 */
if ( ! function_exists( 'boldthemes_customize_default_js' ) ) {
	function boldthemes_customize_default_js() {
		if ( current_user_can( 'edit_pages' ) ) {
			// Register the script
			wp_register_script( 'boldthemes-customize-default', false );
			 
			wp_add_inline_script( 'boldthemes-customize-default', 'window.boldthemes_customize_default = ' . json_encode( BoldThemes_Customize_Default::$data ) );
			 
			// Enqueued script with localized data.
			wp_enqueue_script( 'boldthemes-customize-default' );
		}
	}
};

/**
 * Color schemes JS
 */
if ( ! function_exists( 'boldthemes_color_schemes_js' ) ) {
	function boldthemes_color_schemes_js() {
		if ( current_user_can( 'edit_pages' ) ) {
			// Register the script
			wp_register_script( 'boldthemes-color-schemes', false );
			 
			wp_add_inline_script( 'boldthemes-color-schemes', 'window.boldthemes_color_schemes = ' . json_encode( boldthemes_get_color_scheme_array() ) );
			 
			// Enqueued script with localized data.
			wp_enqueue_script( 'boldthemes-color-schemes' );
		}
	}
};

/**
 * Loads custom Google Fonts
 */
 
if ( ! function_exists( 'boldthemes_load_fonts' ) ) {
	function boldthemes_load_fonts() {
		$body_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'body_font' ) ) );
		$heading_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'heading_font' ) ) );
		$supertitle_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'supertitle_font' ) ) );
		$subtitle_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'subtitle_font' ) ) );
		$menu_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'menu_font' ) ) );
		$button_font = boldthemes_custom_font( urldecode( boldthemes_get_option( 'button_font' ) ) );
		
		// $default_load_font_extension = ':ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900';
		// $default_load_font_extension = ':ital,opsz,wght@0,9..144,100..900;1,9..144,100..900';
		// $default_load_font_extension = ':ital,wght@0,100..900;1,100..800';
		$default_load_font_extension = ':ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900';
		
		$font_families = array();
		
		if ( $body_font != '' ) {
			if ( $body_font == 'default' ) {
				$body_font = BoldThemes_Customize_Default::$data['body_font'];
			}
			$body_font_load_font_extension =  boldthemes_get_option( 'body_font_load_font_extension' ) != '' ? boldthemes_get_option( 'body_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $body_font . $body_font_load_font_extension, $font_families ) ) $font_families[] = $body_font . $body_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}
		
		if ( $heading_font != '' ) {
			if ( $heading_font == 'default' ) {
				$heading_font = BoldThemes_Customize_Default::$data['heading_font'];
			} 
			$heading_font_load_font_extension =  boldthemes_get_option( 'heading_font_load_font_extension' ) != '' ? boldthemes_get_option( 'heading_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $heading_font . $heading_font_load_font_extension, $font_families ) ) $font_families[] = $heading_font . $heading_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}

		if ( $supertitle_font != '' ) {
			if ( $supertitle_font == 'default' ) {
				$supertitle_font = BoldThemes_Customize_Default::$data['supertitle_font'];
			} 
			$supertitle_font_load_font_extension =  boldthemes_get_option( 'supertitle_font_load_font_extension' ) != '' ? boldthemes_get_option( 'supertitle_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $supertitle_font . $supertitle_font_load_font_extension, $font_families ) ) $font_families[] = $supertitle_font . $supertitle_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}

		if ( $subtitle_font != '' ) {
			if ( $subtitle_font == 'default' ) {
				$subtitle_font = BoldThemes_Customize_Default::$data['subtitle_font'];
			} 
			$subtitle_font_load_font_extension =  boldthemes_get_option( 'subtitle_font_load_font_extension' ) != '' ? boldthemes_get_option( 'subtitle_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $subtitle_font . $subtitle_font_load_font_extension, $font_families ) ) $font_families[] = $subtitle_font . $subtitle_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}
		
		if ( $menu_font != '' ) {
			if ( $menu_font == 'default' ) {
				$menu_font = BoldThemes_Customize_Default::$data['menu_font'];
			}
			$menu_font_load_font_extension =  boldthemes_get_option( 'menu_font_load_font_extension' ) != '' ? boldthemes_get_option( 'menu_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $menu_font . $menu_font_load_font_extension, $font_families ) ) $font_families[] = $menu_font . $menu_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}
		
		if ( $button_font != '' ) {
			if ( $button_font == 'default' ) {
				$button_font = BoldThemes_Customize_Default::$data['button_font'];
			}
			$button_font_load_font_extension =  boldthemes_get_option( 'button_font_load_font_extension' ) != '' ? boldthemes_get_option( 'button_font_load_font_extension' ) : $default_load_font_extension;
			if( !in_array( $button_font . $button_font_load_font_extension, $font_families ) ) $font_families[] = $button_font . $button_font_load_font_extension;
		} else {
			/*
			Translators: If there are characters in your language that are not supported
			by chosen font(s), translate this to 'off'. Do not translate into your own language.
			 */
			if ( 'off' !== _x( 'on', 'Lato font: on or off', 'campo' ) ) {
				if( !in_array( 'Lato' . $default_load_font_extension, $font_families ) ) $font_families[] = 'Lato' . $default_load_font_extension;
			}
		}

		if ( count( $font_families ) > 0 ) {
			$query_args = array(
				'family' => ( implode( '&family=', $font_families ) ),
				'subset' => ( 'latin,latin-ext' ),
				'display' => ( 'swap' ),
			);
			$font_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css2' );
			wp_enqueue_style( 'boldthemes-fonts', $font_url, array(), null );
			add_editor_style( $font_url );
		}
	}
}


function boldthemes_theme_support() {
    remove_theme_support( 'widgets-block-editor' );
}
