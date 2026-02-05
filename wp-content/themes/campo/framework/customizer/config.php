<?php

// https://make.wordpress.org/core/2016/07/05/customizer-apis-in-4-6-for-setting-validation-and-notifications/

if ( ! class_exists( 'BoldThemes_Customize_Default' ) ) {

	class BoldThemes_Customize_Default {

		public static $data = array(

			// GENERAL SETTINGS

			'display_branding_text'						=> true,
			'branding_text_html_tag'					=> 'p',
			'custom_js'									=> '',
			'logo'										=> '',
			'sticky_logo'								=> '',
			'responsive_logo'							=> '',
			'responsive_sticky_logo'					=> '',
			'responsive_menu_logo'						=> '',
			'logo_height'								=> '100px',
			'sticky_logo_height'						=> '80px',
			'responsive_logo_height'					=> '80px',
			'responsive_sticky_logo_height'				=> '60px',
			'responsive_menu_logo_height'				=> '200px',
			'header_responsive_breakpoint'				=> '1024',
			'sticky_header_scroll_breakpoint'			=> '0',
			'sidebar_position'							=> 'right',
			'sidebar_sticky'							=> false,
			'sidebar_sticky_top_position'				=> '20px',
			'sidebar_width'								=> '25%',
			'sidebar_responsive_breakpoint'				=> '1024',
			'content_width' 							=> 'boxed-1200',	
			'sidebar_headline_size'						=> 'small',

			// STYLES AND COLORS

			'accent_color'								=> '#2196f3',
			'alternate_color' 							=> '#ff9800',
			'template_color_scheme' 					=> '10',
			'page_background_color' 					=> '',
			'page_color' 								=> '',
			'page_background_image'						=> '',
			'top_bar_color_scheme'						=> '9',
			'top_bar_background_color' 					=> '',
			'top_bar_color' 							=> '',
			'site_branding_bar_color_scheme' 			=> '12',
			'site_branding_bar_background_color' 		=> '',
			'site_branding_bar_color' 					=> '',
			'menu_bar_color_scheme' 					=> '12',
			'menu_bar_background_color' 				=> '',
			'menu_bar_color' 							=> '',
			'menu_hover_color_scheme' 					=> '3',
			'menu_dropdown_color_scheme' 				=> '10',
			'menu_dropdown_hover_color_scheme'			=> '10',
			
			'enable_sticky' 							=> false,
			'sticky_style' 								=> 'classic',
			'sticky_site_branding_bar_color_scheme'		=> '10',
			'sticky_site_branding_bar_background_color' => '',
			'sticky_site_branding_bar_color' 			=> '',
			'sticky_menu_bar_color_scheme' 				=> '11',
			'sticky_menu_bar_background_color' 			=> '',
			'sticky_menu_bar_color' 					=> '',
			
			'footer_widgets_color_scheme'				=> '3',
			'footer_widgets_background_color'			=> '',
			'footer_widgets_color' 						=> '',
			
			'default_headline_color_scheme' 			=> '3',
			'default_headline_background_color' 		=> '',
			'default_headline_color'					=> '',

			// HEADER AND FOOTER

			'header_width' 								=> 'boxed-boxed-1200',
			'sticky_header_width' 						=> 'wide-wide',
			'primary_menu_position' 					=> 'logo-right',
			'primary_menu_reverse_menu_levels'			=> '1',
			'header_position' 							=> 'top',
			'footer_width' 								=> 'wide-boxed-1200',
			'footer_page_slug'							=> '',

			// RESPONSIVE HEADER

			'responsive_logo_position' 					=> 'left',
			'responsive_trigger_position' 				=> 'left',
			'responsive_menu_position' 					=> 'left',
			'responsive_menu_max_width'					=> '320px',
			'responsive_header_color_scheme' 			=> '10',
			'responsive_header_background_color' 		=> '',
			'responsive_header_color' 					=> '',
			'responsive_sticky_header_color_scheme' 	=> '10',
			'responsive_sticky_header_background_color' => '',
			'responsive_sticky_header_color' 			=> '',
			'responsive_menu_color_scheme' 				=> '10',
			'responsive_menu_background_color' 			=> '',
			'responsive_menu_color' 					=> '',

			// DEFAULT HEADLINE

			'default_headline_height' 					=> 'regular',
			'default_headline_size' 					=> 'large',
			'default_headline_h_tag' 					=> 'h2',
			'default_headline_width' 					=> 'wide-boxed-1200',
			'default_headline_parallax' 				=> 'slow',
			'default_headline_overlay' 					=> 'dark-40',
			'default_headline_alignment' 				=> 'inherit',
			'page_single_show_superheadline'			=> array(),
			'page_single_show_subheadline'				=> array( 'excerpt' ),
			'page_single_share'							=> array(),
			'page_single_share_size'					=> 'xsmall',
			'page_single_share_style'					=> 'filled',
			'page_single_share_shape'					=> 'circle',
			'page_single_share_color_scheme'			=> 'accent-light',

			// PRELOADER

			'enable_preloader' 							=> false,
			'preloader_color_scheme' 					=> 'accent-light',
			'preloader_background_color'				=> '',
			'preloader_color'							=> '',
			'preloader_transition' 						=> 'fade',
			'preloader_animation' 						=> 'square',
			'preloader_text'							=> 'Loading. Please wait.',
			'preloader_logo'							=> '',

			// TYPOGRAPHY

			'body_font'									=> 'Arial, Helvetica, sans-serif',
			'body_font_load_font_extension'				=> '',
			'body_text_transform'						=> 'none',
			'body_font_weight'							=> 'normal',
			'body_font_style'							=> 'none',
			'heading_font'								=> 'Arial, Helvetica, sans-serif',
			'heading_font_load_font_extension'			=> '',
			'heading_text_transform'					=> 'none',
			'heading_font_weight'						=> 'normal',
			'heading_font_style'						=> 'none',
			'heading_letter_spacing'					=> '0px',
			'supertitle_font'							=> 'Arial, Helvetica, sans-serif',
			'supertitle_font_load_font_extension'		=> '',
			'supertitle_text_transform'					=> 'none',
			'supertitle_font_weight'					=> 'normal',
			'supertitle_font_style'						=> 'none',
			'supertitle_letter_spacing'					=> '0px',
			'subtitle_font'								=> 'Arial, Helvetica, sans-serif',
			'subtitle_font_load_font_extension'			=> '',
			'subtitle_text_transform'					=> 'none',
			'subtitle_font_weight'						=> 'normal',
			'subtitle_font_style'						=> 'none',
			'subtitle_letter_spacing'					=> '0px',
			'menu_font'									=> 'Arial, Helvetica, sans-serif',
			'menu_font_load_font_extension'				=> '',
			'button_font'								=> 'Arial, Helvetica, sans-serif',
			'button_font_load_font_extension'			=> '',
			'button_text_transform'						=> 'none',
			'button_font_weight'						=> 'normal',
			'button_font_style'							=> 'none',
			'button_style'								=> 'filled',
			'button_shape'								=> 'square',
			'button_letter_spacing'						=> '0px',
			'button_color_scheme'						=> '3',
			'menu_first_level_text_transform'			=> 'none',
			'menu_other_levels_text_transform'			=> 'none',
			'menu_first_level_font_weight'				=> 'normal',
			'menu_other_levels_font_weight'				=> 'normal',
			
			// SEARCH
				
			'search_settings_page_slug'			 		=> '',
			'search_list_headline_size' 				=> 'medium',
			'search_list_show_superheadline'			=> array( 'author', 'categories' ),
			'search_list_show_subheadline'				=> array( 'date' ),
			'search_list_show_excerpt'					=> 'full',
			'search_list_show_bottom'					=> array( 'share' ),
			'search_list_share'							=> array(),
			'search_list_share_size'					=> 'xsmall',
			'search_list_share_style'					=> 'filled',
			'search_list_share_shape'					=> 'circle',
			'search_list_share_color_scheme'			=> '1',
			
			'search_list_read_more_icon'				=> 'fontawesome5solid_f105',
			'search_list_read_more_size'				=> 'small',
			'search_list_read_more_style'				=> 'filled',
			'search_list_read_more_shape'				=> 'inherit',
			'search_list_read_more_color_scheme'		=> '0',

			// BLOG

			'blog_list_view'							=> 'standard',
			'blog_list_show_superheadline'				=> array( 'author', 'categories' ),
			'blog_list_show_subheadline'				=> array( 'date' ),
			'blog_list_show_excerpt'					=> 'full',
			'blog_list_show_bottom'						=> array( 'share' ),
			'blog_list_headline_size' 					=> 'medium',
			'blog_list_share'							=> array(),
			'blog_list_share_size'						=> 'xsmall',
			'blog_list_share_style'						=> 'filled',
			'blog_list_share_shape'						=> 'circle',
			'blog_list_share_color_scheme'				=> '0',
			'blog_list_read_more_icon'					=> 'fontawesome5solid_f105',
			'blog_list_read_more_size'					=> 'small',
			'blog_list_read_more_style'					=> 'filled',
			'blog_list_read_more_shape'					=> 'inherit',
			'blog_list_read_more_color_scheme'			=> '0',
			'blog_list_load_animation'					=> 'no_animation',
			'blog_single_view'							=> 'standard',
			'blog_settings_page_slug'					=> '',
			'blog_single_show_superheadline'			=> array( 'author', 'categories' ),
			'blog_single_show_subheadline'				=> array( 'excerpt' ),
			'blog_single_show_bottom'					=> array( 'date', 'tags' ),
			'blog_single_share'							=> array(),
			'blog_single_share_size'					=> 'xsmall',
			'blog_single_share_style'					=> 'filled',
			'blog_single_share_shape'					=> 'circle',
			'blog_single_share_color_scheme'			=> '1',
			'blog_single_about_author_style'			=> 'none',
			'blog_single_show_navigation'				=> array( 'label', 'title', 'featured_image' ),
			'blog_single_headline_size'					=> 'normal',


			// PORTFOLIO

			'pf_list_view'								=> 'standard',
			'pf_list_show_superheadline'				=> array( 'author', 'categories' ),
			'pf_list_show_subheadline'					=> array( 'date' ),
			'pf_list_show_bottom'						=> array( 'share' ),
			'pf_list_headline_size' 					=> 'medium',
			'pf_list_share'								=> array(),
			'pf_list_share_size'						=> 'xsmall',
			'pf_list_share_style'						=> 'filled',
			'pf_list_share_shape'						=> 'circle',
			'pf_list_share_color_scheme'				=> '1',
			'pf_list_read_more_icon'					=> 'fontawesome5solid_f105',
			'pf_list_read_more_size'					=> 'small',
			'pf_list_read_more_style'					=> 'filled',
			'pf_list_read_more_shape'					=> 'inherit',
			'pf_list_read_more_color_scheme'			=> '0',
			'pf_list_load_animation'					=> 'no_animation',
			'pf_single_view'							=> 'standard',
			'pf_settings_page_slug'						=> '',
			'pf_single_show_superheadline'				=> array( 'author', 'categories' ),
			'pf_single_show_subheadline'				=> array( 'date' ),
			'pf_list_show_excerpt'						=> 'full',
			'pf_single_show_bottom'						=> array( 'share' ),
			'pf_single_share'							=> array(),
			'pf_single_share_size'						=> 'xsmall',
			'pf_single_share_style'						=> 'filled',
			'pf_single_share_shape'						=> 'circle',
			'pf_single_share_color_scheme'				=> '1',			
			'pf_slug'									=> 'portfolio',
			'pf_category_slug'							=> '',			
			'pf_single_about_author_style'				=> 'none',
			'pf_single_show_navigation'					=> array( 'label', 'title', 'featured_image' ),
			'pf_single_headline_size'					=> 'normal',


			// SHOP

			'shop_settings_page_slug'					=> '',
			'shop_single_show_superheadline'			=> array( 'categories' ),
			'shop_single_show_subheadline'				=> array( 'SKU', 'tags' ),
			'shop_single_show_bottom'					=> array( 'share' ),
			'shop_button_style'							=> 'filled',
			// 'shop_button_shape'						=> 'inherit',
			'shop_button_color_scheme'					=> 'inherit',
			'shop_single_share'							=> array(),
			'shop_single_share_size'					=> 'xsmall',
			'shop_single_share_style'					=> 'filled',
			'shop_single_share_shape'					=> 'circle',
			'shop_single_share_color_scheme'			=> '1',
			'shop_single_related_products_columns'		=> '3',
			'shop_single_related_products_rows'			=> '1',
			'shop_list_show_superheadline'				=> array( 'categories' ),
			'shop_list_show_subheadline'				=> array( 'SKU', 'tags' ),
			'shop_list_show_bottom'						=> array( 'share' ),
			'shop_list_button_color_scheme'				=> '0',
			'shop_list_share'							=> array(),
			'shop_list_share_size'						=> 'xsmall',
			'shop_list_share_style'						=> 'filled',
			'shop_list_share_shape'						=> 'circle',
			'shop_list_share_color_scheme'				=> '1',
			//'shop_list_loop_shop_columns'				=> '3',
			//'shop_list_loop_shop_rows'				=> '2',
			'shop_list_headline_size' 					=> 'small',
			'shop_single_headline_size'					=> 'normal',
			
			// 404
			
			'error_404_page_slug'						=> '',
			'error_404_color_scheme'					=> '10',
			'error_404_background_color'				=> '',
			'error_404_color' 							=> '',
			'error_404_background_image'				=> '',
			
			// MESSAGES
			
			'copy_to_clipboard_ok'						=> 'Copied current url to clipboard ',
			'copy_to_clipboard_notok'					=> 'Error. Could not copy current url',
			
			// EMBEDED
			
			'images_in_slider'							=> 3,
			'grid_gallery_columns'						=> 3,
		);
	}
}

if ( ! function_exists( 'boldthemes_get_archive_layout_options' ) ) {
	function boldthemes_get_archive_layout_options() {
		$layout_options =  array(
				'standard' 				=> esc_html__( 'Standard, with title on top', 'campo' ),
				'standard-top-image' 	=> esc_html__( 'Standard with image on top', 'campo' ),
				'columns' 				=> esc_html__( 'Columns', 'campo' ),
				'zig-zag' 				=> esc_html__( 'Zig-zag', 'campo' ),
				'simple' 				=> esc_html__( 'Simple', 'campo' )
			);
		return $layout_options;
	}
}

if ( ! function_exists( 'boldthemes_get_animations' ) ) {
	function boldthemes_get_animations() {
		$animations = array(
				'no_animation'			=> esc_html__( 'No Animation', 'campo' ),
				'fade_in'				=> esc_html__( 'Fade In', 'campo' ),
				'move_up'				=> esc_html__( 'Move Up', 'campo' ),
				'move_left'				=> esc_html__( 'Move Left', 'campo' ),
				'move_right'			=> esc_html__( 'Move Right', 'campo' ),
				'move_down'				=> esc_html__( 'Move Down', 'campo' ),
				'zoom_in'				=> esc_html__( 'Zoom in', 'campo' ),
				'zoom_out'				=> esc_html__( 'Zoom out', 'campo' ),
				'fade_in_move_up'		=> esc_html__( 'Fade In / Move Up', 'campo' ),
				'fade_in_move_left'		=> esc_html__( 'Fade In / Move Left', 'campo' ),
				'fade_in_move_right'	=> esc_html__( 'Fade In / Move Right', 'campo' ),
				'fade_in_move_down'		=> esc_html__( 'Fade In / Move Down', 'campo' ),
				'fade_in_zoom_in'		=> esc_html__( 'Fade In / Zoom in', 'campo' ),
				'fade_in_zoom_out'		=> esc_html__( 'Fade In / Zoom out', 'campo' )
		);
		return $animations;
	}
}

if ( ! function_exists( 'boldthemes_get_headline_sizes' ) ) {
	function boldthemes_get_headline_sizes() {
		$headline_sizes = array(
			'extrasmall'     	=> esc_html__( 'Extra Small', 'campo' ),
			'small'     		=> esc_html__( 'Small', 'campo' ),
			'medium'    		=> esc_html__( 'Medium', 'campo' ),
			'normal'    		=> esc_html__( 'Normal', 'campo' ),
			'large'     		=> esc_html__( 'Large', 'campo' ),
			'extralarge'     	=> esc_html__( 'Extra Large', 'campo' ),
			'huge'     			=> esc_html__( 'Huge', 'campo' ),
		);
		return $headline_sizes;
	}
}

if ( ! function_exists( 'boldthemes_get_font_styles' ) ) {
	function boldthemes_get_font_styles() {
		$font_styles = array(
			'none'		=> esc_html__( 'None', 'campo' ),
			'italic' 	=> esc_html__( 'Italic', 'campo' ),
			'oblique' 	=> esc_html__( 'Oblique', 'campo' ),
		);
		return $font_styles;
	}
}

if ( ! function_exists( 'boldthemes_get_font_weights' ) ) {
	function boldthemes_get_font_weights() {
		$font_weights = array(
			'default' 				=> esc_html__( 'Default', 'campo' ),
			'lighter' 				=> esc_html__( 'Lighter', 'campo' ),
			'normal' 				=> esc_html__( 'Normal', 'campo' ),
			'bold' 					=> esc_html__( 'Bold', 'campo' ),
			'bolder' 				=> esc_html__( 'Bolder', 'campo' ),
			'100' 					=> esc_html__( '100', 'campo' ),
			'200' 					=> esc_html__( '200', 'campo' ),
			'300' 					=> esc_html__( '300', 'campo' ),
			'400' 					=> esc_html__( '400', 'campo' ),
			'500' 					=> esc_html__( '500', 'campo' ),
			'600' 					=> esc_html__( '600', 'campo' ),
			'700' 					=> esc_html__( '700', 'campo' ),
			'800' 					=> esc_html__( '800', 'campo' ),
			'900' 					=> esc_html__( '900', 'campo' ),
		);
		return $font_weights;
	}
}

if ( ! function_exists( 'boldthemes_get_text_transforms' ) ) {
	function boldthemes_get_text_transforms() {
		$text_transforms = array(
			'none'			=> esc_html__( 'None', 'campo' ),
			'initial' 		=> esc_html__( 'Initial', 'campo' ),
			'inherit' 		=> esc_html__( 'Inherit', 'campo' ),
			'uppercase' 	=> esc_html__( 'UPPERCASE', 'campo' ),
			'capitalize' 	=> esc_html__( 'Capitalize', 'campo' ),
			'lowercase' 	=> esc_html__( 'lowercase', 'campo' ),
		);
		return $text_transforms;
	}
}

if ( ! function_exists( 'boldthemes_get_icon_sizes' ) ) {
	function boldthemes_get_icon_sizes() {
		$icon_sizes = array(
			'xsmall'		=> esc_html__( 'Extra small', 'campo' ),
			'small'			=> esc_html__( 'Small', 'campo' ),
			'normal'		=> esc_html__( 'Normal', 'campo' ),
			'large'			=> esc_html__( 'Large', 'campo' ),
			'xlarge'		=> esc_html__( 'Extra large', 'campo' ),
		);
		return $icon_sizes;
	}
}

if ( ! function_exists( 'boldthemes_get_icon_styles' ) ) {
	function boldthemes_get_icon_styles() {
		$icon_styles = array(
			'outline'		=> esc_html__( 'Outline', 'campo' ),
			'filled'		=> esc_html__( 'Filled', 'campo' ),
			'borderless'	=> esc_html__( 'Borderless', 'campo' ),
		);
		return $icon_styles;
	}
}

if ( ! function_exists( 'boldthemes_get_icon_shapes' ) ) {
	function boldthemes_get_icon_shapes() {
		$icon_shapes = array(
			'circle'		=> esc_html__( 'Circle', 'campo' ),
			'square'		=> esc_html__( 'Square', 'campo' ),
			'round'			=> esc_html__( 'Soft rounded', 'campo' ),
		);
		return $icon_shapes;
	}
}

if ( ! function_exists( 'boldthemes_get_button_sizes' ) ) {
	function boldthemes_get_button_sizes() {
		$button_sizes = array(
			'small'			=> esc_html__( 'Small', 'campo' ),
			'medium'		=> esc_html__( 'Medium', 'campo' ),
			'normal'		=> esc_html__( 'Normal', 'campo' ),
			'large'			=> esc_html__( 'Large', 'campo' ),
		);
		return $button_sizes;
	}
}

if ( ! function_exists( 'boldthemes_get_button_styles' ) ) {
	function boldthemes_get_button_styles() {
		$button_styles = array(
			'filled'		=> esc_html__( 'Filled', 'campo' ),
			'outline'		=> esc_html__( 'Outline', 'campo' ),
			'clean'			=> esc_html__( 'Clean', 'campo' ),
		);
		return $button_styles;
	}
}

if ( ! function_exists( 'boldthemes_get_button_shapes' ) ) {
	function boldthemes_get_button_shapes() {
		$button_shapes = array( 'inherit' => esc_html__( 'Inherit', 'campo' ) ) + boldthemes_get_typography_button_shapes();
		return $button_shapes;
	}
}

if ( ! function_exists( 'boldthemes_get_typography_button_shapes' ) ) {
	function boldthemes_get_typography_button_shapes() {
		$button_shapes = array(
			'square'		=> esc_html__( 'Square', 'campo' ),
			'rounded'		=> esc_html__( 'Soft Rounded', 'campo' ),
			'round'			=> esc_html__( 'Hard Rounded', 'campo' ),
		);
		return $button_shapes;
	}
}

if ( ! function_exists( 'boldthemes_get_columns' ) ) {
	function boldthemes_get_columns() {
		$columns = array(
			'1'    => esc_html__( '1', 'campo' ),
			'2'    => esc_html__( '2', 'campo' ),
			'3'    => esc_html__( '3', 'campo' ),
			'4'    => esc_html__( '4', 'campo' ),
			'5'    => esc_html__( '5', 'campo' ),
			'6'    => esc_html__( '6', 'campo' ),
		);
		return $columns;
	}
}

if ( ! function_exists( 'boldthemes_get_rows' ) ) {
	function boldthemes_get_rows() {
		$rows = array(
			'1'    => esc_html__( '1', 'campo' ),
			'2'    => esc_html__( '2', 'campo' ),
			'3'    => esc_html__( '3', 'campo' ),
			'4'    => esc_html__( '4', 'campo' ),
			'5'    => esc_html__( '5', 'campo' ),
			'6'    => esc_html__( '6', 'campo' ),
		);
		return $rows;
	}
}

if ( ! function_exists( 'boldthemes_get_about_author_styles' ) ) {
	function boldthemes_get_about_author_styles() {
		$styles = array(
			'none'				=> esc_html__( 'None', 'campo' ),
			'with-image'		=> esc_html__( 'With image', 'campo' ),
			'without-image'		=> esc_html__( 'Without image', 'campo' ),
		);
		return $styles;
	}
}

if ( ! function_exists( 'boldthemes_get_single_navigation_options' ) ) {
	function boldthemes_get_single_navigation_options() {
		$styles = array(
			'featured_image'	=> esc_html__( 'Featured image', 'campo' ),
			'label'				=> esc_html__( 'Label', 'campo' ),
			'title'				=> esc_html__( 'Title', 'campo' ),
			'date'				=> esc_html__( 'Date', 'campo' ),
		);
		return $styles;
	}
}

if ( ! function_exists( 'boldthemes_get_color_schemes' ) ) {
	function boldthemes_get_color_schemes() {
		$color_scheme_arr = array();
		if ( function_exists( 'bt_bb_get_color_scheme_param_array' ) ) {
			$color_scheme_arr = bt_bb_get_color_scheme_param_array();
		} else {
			$color_scheme_arr = boldthemes_get_color_scheme_param_array();
		}
		$new_color_scheme_arr = array();
		$i = 0;
		foreach( $color_scheme_arr as $key => $val ) {
			$new_color_scheme_arr[ $i ] = $key;
			$i++;
		}

		return $new_color_scheme_arr;
	}
}

if ( ! function_exists( 'boldthemes_get_color_scheme_param_array' ) ) {
	function boldthemes_get_color_scheme_param_array() {
		$color_scheme_arr = array( esc_html__( 'Inherit', 'campo' ) => '0' );

		$color_scheme_arr_temp = boldthemes_get_color_scheme_array();

		if ( isset( $color_scheme_arr_temp[0] ) && $color_scheme_arr_temp[0] != '' ) {
			foreach( $color_scheme_arr_temp as $item ) {
				if ( $item != '' ) {
					$item_arr = explode( ';', $item, 4 );
					if ( count( $item_arr ) == 4 ) {
						$color_scheme_arr[ $item_arr[1] ] = $item_arr[0];
					} else {
						$color_scheme_arr[ $item_arr[0] ] = $item_arr[0];
					}
				}
			}
		}
		return $color_scheme_arr;
	}
}

if ( ! function_exists( 'boldthemes_get_color_scheme_array' ) ) {
	function boldthemes_get_color_scheme_array() {

		$options = get_option( 'bt_bb_settings' );
		if ( ! $options ) {
			$color_scheme_arr = array();
		} else {
			$color_schemes = $options['color_schemes'];
			$color_scheme_arr = preg_split( '/(\r\n|\n|\r)/', $color_schemes );
		}
		
		$color_scheme_arr = apply_filters( 'bt_bb_color_scheme_arr', $color_scheme_arr );

		return $color_scheme_arr;
	}
}

if ( ! function_exists( 'boldthemes_get_preloader_color_schemes' ) ) {
	function boldthemes_get_preloader_color_schemes() {
		$styles_array = boldthemes_get_color_schemes();
		$preloader_color_schemes_insert_array = array(
			'none' 		=> esc_html__( 'None', 'campo' ),
		);
		$pos = 1;
		$preloader_color_schemes = $preloader_color_schemes_insert_array + array_slice( $styles_array, $pos, null, true );
		return $preloader_color_schemes;
	}
}

if ( ! function_exists( 'boldthemes_get_preloader_transitions' ) ) {
	function boldthemes_get_preloader_transitions() {
		$preloader_transitions = array(
			'fade' 					=> esc_html__( 'Fade', 'campo' ),
			'fade-move-up' 			=> esc_html__( 'Fade & move up', 'campo' ),
			'fade-move-down' 		=> esc_html__( 'Fade & move down', 'campo' ),
			'fade-move-left' 		=> esc_html__( 'Fade & move left', 'campo' ),
			'fade-move-right' 		=> esc_html__( 'Fade & move right', 'campo' ),
			'fade-scale-up' 		=> esc_html__( 'Fade & scale up', 'campo' ),
			'fade-scale-down' 		=> esc_html__( 'Fade & scale down', 'campo' ),
			'fade-blur' 			=> esc_html__( 'Fade & blur', 'campo' ),
		);
		return $preloader_transitions;
	}
}

if ( ! function_exists( 'boldthemes_get_preloader_animations' ) ) {
	function boldthemes_get_preloader_animations() {
		$preloader_animations = array(
			'sqare' 				=> esc_html__( 'Square', 'campo' ),
			'dots' 					=> esc_html__( 'Dots', 'campo' ),
			'circle' 				=> esc_html__( 'Circle', 'campo' ),
			'dots-square' 			=> esc_html__( 'Dotted square', 'campo' ),
			'line' 					=> esc_html__( 'Line', 'campo' ),
			'multiline' 			=> esc_html__( 'Multiline', 'campo' ),
			'classic' 				=> esc_html__( 'Classic', 'campo' ),
		);
		return $preloader_animations;
	}
}

if ( ! function_exists( 'boldthemes_get_gallery_gaps' ) ) {
	function boldthemes_get_gallery_gaps() {
		$gallery_gaps = array(
			'no_gap'    => esc_html__( 'No gap', 'campo' ),
			'small'     => esc_html__( 'Small', 'campo' ),
			'normal'    => esc_html__( 'Normal', 'campo' ),
			'large'     => esc_html__( 'Large', 'campo' ),
		);
		return $gallery_gaps;
	}
}

// EXCERPT

if ( ! function_exists( 'boldthemes_get_excerpt_options' ) ) {
	function boldthemes_get_excerpt_options() {
		$excerpt_options_array = array(
			'none' 					=> esc_html__( 'Hide excerpt', 'campo' ),
			'full' 					=> esc_html__( 'Show full excerpt', 'campo' ),
			'1-line' 				=> esc_html__( 'Show excerpt, 1 line', 'campo' ),
			'2-lines' 				=> esc_html__( 'Show excerpt, 2 lines', 'campo' ),
			'3-lines' 				=> esc_html__( 'Show excerpt, 3 lines', 'campo' ),
			'4-lines' 				=> esc_html__( 'Show excerpt, 4 lines', 'campo' ),
			'5-lines' 				=> esc_html__( 'Show excerpt, 5 lines', 'campo' ),
			'6-lines' 				=> esc_html__( 'Show excerpt, 6 lines', 'campo' ),
		);
		return $excerpt_options_array;
	}
}

// META

if ( ! function_exists( 'boldthemes_get_meta_options' ) ) {
	function boldthemes_get_meta_options() {
		$meta_options_array = array(
			'date' 					=> esc_html__( 'Date', 'campo' ),
			'author' 				=> esc_html__( 'Author', 'campo' ),
			'author-photo' 			=> esc_html__( 'Author photo', 'campo' ),
			'share' 				=> esc_html__( 'Share', 'campo' ),
			'tags' 					=> esc_html__( 'Tags', 'campo' ),
			'categories' 			=> esc_html__( 'Categories', 'campo' ),
			'comments-number' 		=> esc_html__( 'Comments number', 'campo' ),
			'comments-link'			=> esc_html__( 'Comments link', 'campo' ),
			'excerpt' 				=> esc_html__( 'Excerpt', 'campo' ),
		);
		// if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
			$meta_options_array = array( 'breadcrumbs-yoast' => esc_html__( 'Breadcrumbs (Yoast)', 'campo' ) ) + $meta_options_array;
		// 
		$meta_options_array = array( 'breadcrumbs-yoast' => esc_html__( 'Breadcrumbs (Yoast)', 'campo' ) ) + $meta_options_array;
		return $meta_options_array;
	}
}

// SHOP META

if ( ! function_exists( 'boldthemes_get_shop_meta_options' ) ) {
	function boldthemes_get_shop_meta_options() {
		$meta_options_array = boldthemes_get_meta_options();
		$meta_options_insert_array = array(
			'product-sku' 				=> esc_html__( 'Product SKU', 'campo' ),
			'product-rating-average' 	=> esc_html__( 'Product average rating', 'campo' ),
		);
		$pos = 6;
		$meta_options_array = array_slice( $meta_options_array, 0, $pos ) + $meta_options_insert_array + array_slice( $meta_options_array, $pos );
		return $meta_options_array;
	}
}

// SHARE OPTIONS 

if ( ! function_exists( 'boldthemes_get_share_options' ) ) {
	function boldthemes_get_share_options() {
		return array(
			'copy_clipboard' 	=> esc_html__( 'Copy url to clipboard', 'campo' ),
			'facebook' 			=> esc_html__( 'Facebook', 'campo' ),
			'twitter' 			=> esc_html__( 'Twitter', 'campo' ),
			'twitter-x' 		=> esc_html__( 'Twitter (X)', 'campo' ),
			'linkedin' 			=> esc_html__( 'Linkedin', 'campo' ),
			'vk' 				=> esc_html__( 'VK', 'campo' ),
			'whatsapp' 			=> esc_html__( 'WhatsApp', 'campo' ),
			'mailto' 			=> esc_html__( 'Mail', 'campo' ),
			'reddit' 			=> esc_html__( 'Reddit', 'campo' ),
			'tumblr' 			=> esc_html__( 'Tumblr', 'campo' ),
			'pinterest' 		=> esc_html__( 'Pinterest', 'campo' ),
			'blogger' 			=> esc_html__( 'Blogger', 'campo' ),
			'evernote' 			=> esc_html__( 'EverNote', 'campo' ),
			//'livejournal' 	=> esc_html__( 'LiveJournal', 'campo' ),
			'get-pocket' 		=> esc_html__( 'GetPocket', 'campo' ),
			'hacker-news' 		=> esc_html__( 'HackerNews', 'campo' ),
			'flipboard' 		=> esc_html__( 'FlipBoard', 'campo' ),
			'googlebookmarks' 	=> esc_html__( 'GoogleBookmarks', 'campo' ),
			//'instapaper' 		=> esc_html__( 'InstaPaper', 'campo' ),
			'diaspora' 			=> esc_html__( 'Diaspora', 'campo' ),
			'qq' 				=> esc_html__( 'QZone', 'campo' ),
			'weibo' 			=> esc_html__( 'Weibo', 'campo' ),
			//'okru' 			=> esc_html__( 'OKru', 'campo' ),
			//'douban' 			=> esc_html__( 'Douban', 'campo' ),
			'renren' 			=> esc_html__( 'RenRen', 'campo' ),
			'xing' 				=> esc_html__( 'XING', 'campo' ),
			//'threema' 		=> esc_html__( 'Threema', 'campo' ),
			//'sms' 			=> esc_html__( 'SMS', 'campo' ),
			'skype' 			=> esc_html__( 'Skype', 'campo' ),
			'lineme' 			=> esc_html__( 'Line.me', 'campo' ),
			'telegram' 			=> esc_html__( 'Telegram.me', 'campo' ),
			'viber' 			=> esc_html__( 'Viber', 'campo' ),
		);
	}
}

/* CUSTOM CONTROLS */

if ( ! function_exists( 'boldthemes_custom_controls' ) ) {
	function boldthemes_custom_controls() {
		
		class BoldThemes_Customize_Separator_Control extends WP_Customize_Control {
			public $type = 'separator';
			public function render_content() {
				?>
				<div class="customize-control-separator-inner">
					<hr>
					<?php
					if ( !empty( $this->label ) ) {
						echo '<label>';
							echo '<span class="customize-action customize-section">' . $this->label . '</span>';
						echo '</label>';
					}
					?>
				</div>
				<?php
			}
		}		
		
		class BoldThemes_Customize_Textarea_Control extends WP_Customize_Control {
			public function render_content() {
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php if ( !empty( $this->description ) ) { ?><span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span><?php } ?>
					<textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value()); ?></textarea>
				</label>
				<?php
			}
		}

		class BoldThemes_Customize_Multiple_Select_Control extends WP_Customize_Control {
			public $type = 'multiple-select';
			public function render_content() {
				if ( empty( $this->choices ) ) {
					return;
				}
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php if ( !empty( $this->description ) ) { ?><span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span><?php } ?>
					<select <?php $this->link(); ?> multiple="multiple" title="<?php esc_attr_e( 'Hold CTRL to select multiple options', 'campo' ); ?>">
						<?php
						foreach ( $this->choices as $value => $label ) {
							$selected = ( in_array( $value, $this->value() ) ) ? selected( 1, 1, false ) : '';
							echo '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . $label . '</option>';
						}
						?>
					</select>
				</label>
			<?php }
		}

		class BoldThemes_Reset_Control extends WP_Customize_Control {
			public function render_content() {
				?>
				<div>
				<label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>
					<input type="submit" onclick="var c = confirm('<?php echo esc_js( esc_html__( 'Reset theme settings to default values?', 'campo' ) ); ?>'); if (c != true) return false;var href=window.location.href;if (href.indexOf('?') > -1) {window.location.replace(href + '&boldthemes_reset=reset')} else {window.location.replace(href + '?boldthemes_reset=reset')};return false;" name="boldthemes_reset" id="boldthemes_reset" class="button" value="Reset">
				</div>
				<?php
			}
		}
		
		class BoldThemes_Customize_Iconpicker_Control extends WP_Customize_Control {
			public function render_content() {					
					$icon_bb = $this->value();
				?>
				<div class="bt_bb_iconpicker_widget_container">
					<label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>
					<input type="hidden" data-customize-setting-link="boldthemes_theme_theme_options[<?php echo esc_attr( $this->id ); ?>]" value="<?php echo esc_attr( $icon_bb ); ?>">
					<div class="bt_bb_iconpicker_widget_placeholder" data-icon="<?php echo esc_attr( $icon_bb ); ?>"></div>
				</div>
				<?php
				if ( ! class_exists( 'BT_BB_Root' ) ) {
					echo '<span class="description customize-control-description">' . esc_html__( 'Please activate Bold Builder plugin to access this option.', 'campo' ) . '</span>';
				}
			}
			public function enqueue() {
				
			}
		}
	}
}
add_action( 'customize_register', 'boldthemes_custom_controls' );
add_action( 'boldthemes_customize_register', 'boldthemes_custom_controls' );

if ( ! function_exists( 'boldthemes_custom_text' ) ) {
	function boldthemes_custom_text( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'boldthemes_custom_js' ) ) {
	function boldthemes_custom_js( $js ) {
		return trim( $js );
	}
}

if ( ! function_exists( 'boldthemes_customize_register_panels' ) ) {
	function boldthemes_customize_register_panels( $wp_customize ) {
		$wp_customize->add_panel( BoldThemesFramework::$pfx . '_header_footer_panel', array(
			'priority'       => 40,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => esc_html__( 'Header and footer', 'campo' ),
			'description'    => esc_html__( 'Several settings pertaining my theme', 'campo' ),
		));
		$wp_customize->add_panel( BoldThemesFramework::$pfx . '_blog_panel', array(
			'priority'       	=> 70,
			'capability'     	=> 'edit_theme_options',
			'theme_supports' 	=> '',
			'title'          	=> esc_html__( 'Blog', 'campo' ),
			'description'    	=> esc_html__( 'Several settings pertaining my theme', 'campo' ),
			'panel_preview_url' => get_option( 'page_for_posts' ),
			'panelPreviewUrl'   => get_option( 'page_for_posts' ),
		));
		$wp_customize->add_panel( BoldThemesFramework::$pfx . '_portfolio_panel', array(
			'priority'       	=> 80,
			'capability'     	=> 'edit_theme_options',
			'theme_supports' 	=> '',
			'title'          	=> esc_html__( 'Portfolio', 'campo' ),
			'description'    	=> esc_html__( 'Several settings pertaining my theme', 'campo' ),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_register_panels' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_register_panels' );

if ( ! function_exists( 'boldthemes_customize_register' ) ) {
	function boldthemes_customize_register( $wp_customize ) {

		global $wpdb;

		if ( isset( $_GET['boldthemes_reset'] ) && $_GET['boldthemes_reset'] == 'reset' ) {
			$wpdb->query( $wpdb->prepare( "delete from " . $wpdb->options . " where option_name = %s", BoldThemesFramework::$pfx . '_theme_options' ) );
			header( 'Location: ' . wp_customize_url() );
		}

		$wp_customize->remove_section( 'colors' );
		
		/* Sections, 1st level */
		
		// Site Identity, priority: 10
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_general_section', array(
			'title'    => esc_html__( 'General Settings', 'campo' ),
			'priority' => 20,
		));	
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_typo_section', array(
			'title'    => esc_html__( 'Typography', 'campo' ),
			'priority' => 30,
		));
		// Header & footer panel, priority: 40
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_default_headline_section', array(
			'title'    => esc_html__( 'Default headline', 'campo' ),
			'priority' => 45,
		));	
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_preloader_section', array(
			'title'    => esc_html__( 'Preloader', 'campo' ),
			'priority' => 50,
		));
		// Blog panel, priority: 70
		// Portfolio panel, priority: 80
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_search_list_section', array(
			'title'    => esc_html__( 'Search', 'campo' ),
			'priority' => 90,
		));
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_404_section', array(
			'title'    => esc_html__( '404', 'campo' ),
			'priority' => 95,
		));	
		
		// Menus, priority: 100	

		/* Sections in panels, 2nd level */
		
		// Header & footer panel
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_header_section', array(
			'title'    => esc_html__( 'Header ', 'campo' ),
			'priority' => 10,
			'panel'    => BoldThemesFramework::$pfx . '_header_footer_panel',
		));	
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_sticky_header_section', array(
			'title'    => esc_html__( 'Sticky header', 'campo' ),
			'priority' => 20,
			'panel'    => BoldThemesFramework::$pfx . '_header_footer_panel',
		));		
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_responsive_header_section', array(
			'title'    => esc_html__( 'Responsive header', 'campo' ),
			'priority' => 30,
			'panel'    => BoldThemesFramework::$pfx . '_header_footer_panel',
		));	
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_footer_section', array(
			'title'    => esc_html__( 'Footer', 'campo' ),
			'priority' => 40,
			'panel'    => BoldThemesFramework::$pfx . '_header_footer_panel',
		));	
		
		// Blog panel
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_blog_list_section', array(
			'title'    => esc_html__( 'Blog list and archive', 'campo' ),
			'priority' => 10,
			'panel'    => BoldThemesFramework::$pfx . '_blog_panel',
		));
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_blog_single_section', array(
			'title'    => esc_html__( 'Blog single post', 'campo' ),
			'priority' => 20,
			'panel'    => BoldThemesFramework::$pfx . '_blog_panel',
		));
		
		// Portfolio panel
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_pf_list_section', array(
			'title'    => esc_html__( 'Portfolio list and archive', 'campo' ),
			'priority' => 10,
			'panel'    => BoldThemesFramework::$pfx . '_portfolio_panel',
		));
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_pf_single_section', array(
			'title'    => esc_html__( 'Portfolio single post', 'campo' ),
			'priority' => 20,
			'panel'    => BoldThemesFramework::$pfx . '_portfolio_panel',
		));
		
		// Shop panel
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_shop_general_section', array(
			'title'    => esc_html__( 'Shop general (BoldThemes)', 'campo' ),
			'priority' => 300,
			'panel'    => 'woocommerce',
		));
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_shop_list_section', array(
			'title'    => esc_html__( 'Shop products list (BoldThemes)', 'campo' ),
			'priority' => 300,
			'panel'    => 'woocommerce',
		));
		$wp_customize->add_section( BoldThemesFramework::$pfx . '_shop_single_section', array(
			'title'    => esc_html__( 'Shop single product (BoldThemes)', 'campo' ),
			'priority' => 400,
			'panel'    => 'woocommerce',
		));

		require_once( get_parent_theme_file_path( 'framework/web_fonts.php' ) );

	}
}
add_action( 'customize_register', 'boldthemes_customize_register' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_register' );

/* SITE IDENTITY */

// MOVED TO GENERAL SECTION

/* GENERAL SECTION */

// DISPLAY HEADER TEXT

if ( ! function_exists( 'boldthemes_customize_display_branding_text' ) ) {
	function boldthemes_customize_display_branding_text( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[display_branding_text]', array(
			'default'           => BoldThemes_Customize_Default::$data['display_branding_text'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'display_branding_text', array(
			'label'    => esc_html__( 'Display site title and tagline', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[display_branding_text]',
			'priority' => 10,
			'type'     => 'checkbox',
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_display_branding_text' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_display_branding_text' );

// HEADER TEXT HTML TAG

if ( ! function_exists( 'boldthemes_customize_branding_text_html_tag' ) ) {
	function boldthemes_customize_branding_text_html_tag( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[branding_text_html_tag]', array(
			'default'           => BoldThemes_Customize_Default::$data['branding_text_html_tag'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'branding_text_html_tag', array(
			'label'    => esc_html__( 'Site title HTML tag', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[branding_text_html_tag]',
			'priority' => 20,
			'type'      => 'select',
			'choices'   => array(
				'p'     	=> esc_html__( 'p', 'campo' ),
				'h1'    	=> esc_html__( 'h1', 'campo' ),
				'h2'     	=> esc_html__( 'h2', 'campo' ),
				'h3'     	=> esc_html__( 'h3', 'campo' ),
				'h4'     	=> esc_html__( 'h4', 'campo' ),
				'h5'     	=> esc_html__( 'h5', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_branding_text_html_tag' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_branding_text_html_tag' );

// PAGE WIDTH

if ( ! function_exists( 'boldthemes_customize_content_width' ) ) {
	function boldthemes_customize_content_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[content_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['content_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'content_width', array(
			'label'     	=> esc_html__( 'Page width', 'campo' ),
			'section'   	=> BoldThemesFramework::$pfx . '_general_section',
			'settings'  	=> BoldThemesFramework::$pfx . '_theme_options[content_width]',
			'priority'  	=> 30,
			'type'      	=> 'select',
			'choices'   	=> array(
				'wide' 			=> esc_html__( 'Wide', 'campo' ),
				'boxed-1200' 	=> esc_html__( 'Boxed 1200', 'campo' ),
				'boxed-1400' 	=> esc_html__( 'Boxed 1400', 'campo' ),
				'boxed-1600' 	=> esc_html__( 'Boxed 1600', 'campo' ),
				'boxed-1800' 	=> esc_html__( 'Boxed 1800', 'campo' )
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_content_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_content_width' );

// COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_main_colors' ) ) {
	function boldthemes_customize_main_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_main_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_main_colors',
			array(
				'label' => esc_html__( 'Colors & styles', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_general_section',
				'priority' => 100
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_main_colors' );

// ACCENT COLOR

if ( ! function_exists( 'boldthemes_customize_accent_color' ) ) {
	function boldthemes_customize_accent_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[accent_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['accent_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'accent_color', array(
			'label'    => esc_html__( 'Accent color (primary)', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[accent_color]',
			'priority' => 110,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_accent_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_accent_color' );

// ALTERNATE COLOR

if ( ! function_exists( 'boldthemes_customize_alternate_color' ) ) {
	function boldthemes_customize_alternate_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[alternate_color]', array(
			'default'        	   => BoldThemes_Customize_Default::$data['alternate_color'],
			'type'           	   => 'option',
			'capability'     	   => 'edit_theme_options',
			'sanitize_callback'    => 'sanitize_hex_color',
			'transport'            => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'alternate_color', array(
			'label'   				=> esc_html__( 'Alternate color (secondary)', 'campo' ),
			'section'  				=> BoldThemesFramework::$pfx . '_general_section',
			'settings' 				=> BoldThemesFramework::$pfx . '_theme_options[alternate_color]',
			'priority' 				=> 120,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_alternate_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_alternate_color' );

// TEMPLATE SKIN

if ( ! function_exists( 'boldthemes_customize_template_color_scheme' ) ) {
	function boldthemes_customize_template_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[template_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['template_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'template_color_scheme', array(
			'label'    => esc_html__( 'Template skin', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[template_color_scheme]',
			'priority' => 130,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_template_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_template_color_scheme' );

// PAGE CUSTOM BACKGROUND COLOR

if ( ! function_exists( 'boldthemes_customize_page_background_color' ) ) {
	function boldthemes_customize_page_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['page_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'page_background_color', array(
			'label'    => esc_html__( 'Template background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[page_background_color]',
			'priority' => 140,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_background_color' );

// PAGE CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_page_color' ) ) {
	function boldthemes_customize_page_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['page_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'page_color', array(
			'label'    => esc_html__( 'Template text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[page_color]',
			'priority' => 150,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_color' );

// PAGE BACKGROUND IMAGE

if ( ! function_exists( 'boldthemes_customize_page_background_image' ) ) {
	function boldthemes_customize_page_background_image( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_background_image]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_background_image'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'			=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'page_background_image', array(
			'label'    => esc_html__( 'Template background image', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[page_background_image]',
			'priority' => 160,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_background_image' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_background_image' );

// SIDEBAR SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_sidebar' ) ) {
	function boldthemes_customize_separator_sidebar( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_sidebar', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_sidebar',
			array(
				'label' => esc_html__( 'Sidebar', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_general_section',
				'priority' => 200
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_sidebar' );

// SIDEBAR POSITION

if ( ! function_exists( 'boldthemes_customize_sidebar_position' ) ) {
	function boldthemes_customize_sidebar_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_position]', array(
			'default'           	=> BoldThemes_Customize_Default::$data['sidebar_position'],
			'type'              	=> 'option',
			'capability'        	=> 'edit_theme_options',
			'sanitize_callback' 	=> 'boldthemes_sanitize_select',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_position', array(
			'label'     => esc_html__( 'Sidebar position', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_general_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[sidebar_position]',
			'priority'  => 210,
			'type'      => 'select',
			'choices'   => array(
				'none' 			=> esc_html__( 'No Sidebar', 'campo' ),
				'left'       	=> esc_html__( 'Left', 'campo' ),
				'right'      	=> esc_html__( 'Right', 'campo' )
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_position' );

// STICKY SIDEBAR

if ( ! function_exists( 'boldthemes_customize_sidebar_sticky' ) ) {
	function boldthemes_customize_sidebar_sticky( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_sticky]', array(
			'default'           => BoldThemes_Customize_Default::$data['sidebar_sticky'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_sticky', array(
			'label'    => esc_html__( 'Enable sticky sidebar', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sidebar_sticky]',
			'priority' => 220,
			'type'     => 'checkbox',
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_sticky' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_sticky' );

// STICKY SIDEBAR POSITION

if ( ! function_exists( 'boldthemes_customize_sidebar_sticky_top_position' ) ) {
	function boldthemes_customize_sidebar_sticky_top_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_sticky_top_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['sidebar_sticky_top_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_sticky_top_position', array(
			'label'    => esc_html__( 'Sticky sidebar top position', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sidebar_sticky_top_position]',
			'priority' => 250,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 20px or 30%)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_sticky_top_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_sticky_top_position' );

//SIDEBAR HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_sidebar_headline_size' ) ) {
	function boldthemes_customize_sidebar_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['sidebar_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_headline_size', array(
			'label'     => esc_html__( 'Sidebar title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_general_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[sidebar_headline_size]',
			'priority'  => 240,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_headline_size' );

// SIDEBAR WIDTH

if ( ! function_exists( 'boldthemes_customize_sidebar_width' ) ) {
	function boldthemes_customize_sidebar_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['sidebar_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_width', array(
			'label'    => esc_html__( 'Sidebar width', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sidebar_width]',
			'priority' => 250,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1024px or 30%)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_width' );

// SIDEBAR RESPONSIVE BREAKPOINT

if ( ! function_exists( 'boldthemes_customize_sidebar_responsive_breakpoint' ) ) {
	function boldthemes_customize_sidebar_responsive_breakpoint( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sidebar_responsive_breakpoint]', array(
			'default'           => BoldThemes_Customize_Default::$data['sidebar_responsive_breakpoint'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sidebar_responsive_breakpoint', array(
			'label'    		=> esc_html__( 'Sidebar responsive breakpoint (in px)', 'campo' ),
			'description'   => esc_html__( 'Defines screen resolution when sidebar is displayed as defined. On smaller screens sidebar will be displayed below content.', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_general_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[sidebar_responsive_breakpoint]',
			'priority' 		=> 260,
			'type'     		=> 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1024)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sidebar_responsive_breakpoint' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sidebar_responsive_breakpoint' );

// CUSTOM OPTIONS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_custom_sidebar' ) ) {
	function boldthemes_customize_custom_sidebar( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_custom_sidebar', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_custom_sidebar',
			array(
				'label' => esc_html__( 'Custom options', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_general_section',
				'priority' => 300
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_custom_sidebar' );

// CUSTOM JS
/* wp_add_inline_script, boldthemes-framework-misc */

if ( ! function_exists( 'boldthemes_customize_custom_js' ) ) {
	function boldthemes_customize_custom_js( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[custom_js]', array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_custom_js'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Textarea_Control(
			$wp_customize,
			'custom_js', array(
				'label'    => esc_html__( 'Custom JS', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_general_section',
				'priority' => 310,
				'type' 		=> 'textarea',
				'settings' => BoldThemesFramework::$pfx . '_theme_options[custom_js]'
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_custom_js' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_custom_js' );

// RESET

if ( ! function_exists( 'boldthemes_customize_reset' ) ) {
	function boldthemes_customize_reset( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[reset]', array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));
		$wp_customize->add_control( new BoldThemes_Reset_Control( $wp_customize, 'reset', array(
			'label'    => esc_html__( 'Reset theme settings', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'priority' => 320,
			'settings' => BoldThemesFramework::$pfx . '_theme_options[reset]'
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_reset' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_reset' );


/* PRELOADER SECTION */


// ENABLE PRELOADER

if ( ! function_exists( 'boldthemes_customize_enable_preloader' ) ) {
	function boldthemes_customize_enable_preloader( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[enable_preloader]', array(
			'default'           => BoldThemes_Customize_Default::$data['enable_preloader'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'enable_preloader', array(
			'label'    			=> esc_html__( 'Enable preloader', 'campo' ),
			'section'  			=> BoldThemesFramework::$pfx . '_preloader_section',
			'settings' 			=> BoldThemesFramework::$pfx . '_theme_options[enable_preloader]',
			'priority' 			=> 10,
			'type'     			=> 'checkbox',
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_enable_preloader' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_enable_preloader' );

// PRELOADER STYLE

if ( ! function_exists( 'boldthemes_customize_preloader_color_scheme' ) ) {
	function boldthemes_customize_preloader_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['preloader_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			//'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'preloader_color_scheme', array(
			'label'     	=> esc_html__( 'Preloader color scheme', 'campo' ),
			// 'description'   => esc_html__( 'Select \'none\' to disable preloader', 'campo' ),
			'section'   	=> BoldThemesFramework::$pfx . '_preloader_section',
			'settings'  	=> BoldThemesFramework::$pfx . '_theme_options[preloader_color_scheme]',
			'priority'  	=> 20,
			'type'      	=> 'select',
			// 'choices'   	=> boldthemes_get_preloader_color_schemes()
			'choices'   	=> boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_color_scheme' );

// PRELOADER CUSTOM BACKGROUND COLOR
if ( ! function_exists( 'boldthemes_customize_preloader_background_color' ) ) {
	function boldthemes_customize_preloader_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['preloader_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			// 'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'preloader_background_color', array(
			'label'    => esc_html__( 'Preloader background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_preloader_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[preloader_background_color]',
			'priority' => 30,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_background_color' );

// PRELOADER CUSTOM TEXT COLOR
if ( ! function_exists( 'boldthemes_customize_preloader_color' ) ) {
	function boldthemes_customize_preloader_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['preloader_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			// 'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'preloader_color', array(
			'label'    => esc_html__( 'Preloader text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_preloader_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[preloader_color]',
			'priority' => 40,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_color' );

// PRELOADER TRANSITION

if ( ! function_exists( 'boldthemes_customize_preloader_transition' ) ) {
	function boldthemes_customize_preloader_transition( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_transition]', array(
			'default'           => BoldThemes_Customize_Default::$data['preloader_transition'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'preloader_transition', array(
			'label'     => esc_html__( 'Preloader transition', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_preloader_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[preloader_transition]',
			'priority'  => 50,
			'type'      => 'select',
			'choices'   => boldthemes_get_preloader_transitions()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_transition' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_transition' );

// PRELOADER ANIMATION

if ( ! function_exists( 'boldthemes_customize_preloader_animation' ) ) {
	function boldthemes_customize_preloader_animation( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_animation]', array(
			'default'           => BoldThemes_Customize_Default::$data['preloader_animation'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'preloader_animation', array(
			'label'     => esc_html__( 'Preloader animation', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_preloader_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[preloader_animation]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => boldthemes_get_preloader_animations()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_animation' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_animation' );

// PRELOADER LOGO

if ( ! function_exists( 'boldthemes_customize_preloader_logo' ) ) {
	function boldthemes_customize_preloader_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['preloader_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'preloader_logo', array(
			'label'    => esc_html__( 'Preloader logo', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_preloader_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[preloader_logo]',
			'priority' => 70,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_logo' );

// PRELOADER TEXT

if ( ! function_exists( 'boldthemes_customize_preloader_text' ) ) {
	function boldthemes_customize_preloader_text( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[preloader_text]', array(
			'default'           => BoldThemes_Customize_Default::$data['preloader_text'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'preloader_text', array(
			'label'    => esc_html__( 'Preloader text', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_preloader_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[preloader_text]',
			'priority' => 80,
			'type'     => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_preloader_text' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_preloader_text' );

/* TYPOGRAPHY SECTION */

// BODY FONT

if ( ! function_exists( 'boldthemes_customize_body_font' ) ) {
	function boldthemes_customize_body_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[body_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['body_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));    
		$wp_customize->add_control( 'body_font', array(
			'label'     => esc_html__( 'Body font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[body_font]',
			'priority'  => 10,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_body_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_body_font' );

// BODY FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_body_font_load_font_extension' ) ) {
	function boldthemes_customize_body_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[body_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['body_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'body_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom body font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[body_font_load_font_extension]',
			'priority'  => 10,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_body_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_body_font_load_font_extension' );

// BODY  TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_body_text_transform' ) ) {
	function boldthemes_customize_body_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[body_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['body_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'body_text_transform', array(
			'label'     => esc_html__( 'Body text transform', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[body_text_transform]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_body_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_body_text_transform' );

// BODY FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_body_font_weight' ) ) {
	function boldthemes_customize_body_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[body_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['body_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'body_font_weight', array(
			'label'     => esc_html__( 'Body font weight', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[body_font_weight]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_weights(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_body_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_body_font_weight' );

// BODY FONT STYLE

if ( ! function_exists( 'boldthemes_customize_body_font_style' ) ) {
	function boldthemes_customize_body_font_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[body_font_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['body_font_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'body_font_style', array(
			'label'     => esc_html__( 'Body font style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[body_font_style]',
			'priority'  => 40,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_body_font_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_body_font_style' );

// HEADINGS TYPOGRAPHY SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_typo_headings' ) ) {
	function boldthemes_customize_separator_typo_headings( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_typo_headings', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_typo_headings',
			array(
				'label' => esc_html__( 'Headings & subheadings', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_typo_section',
				'priority' => 100
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_typo_headings' );

// HEADING FONT

if ( ! function_exists( 'boldthemes_customize_heading_font' ) ) {
	function boldthemes_customize_heading_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'heading_font', array(
			'label'     => esc_html__( 'Heading font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[heading_font]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_font' );

// HEADING FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_heading_font_load_font_extension' ) ) {
	function boldthemes_customize_heading_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'heading_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom heading font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[heading_font_load_font_extension]',
			'priority'  => 110,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_font_load_font_extension' );

// HEADING TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_heading_text_transform' ) ) {
	function boldthemes_customize_heading_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'heading_text_transform', array(
			'label'     => esc_html__( 'Heading text transform', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[heading_text_transform]',
			'priority'  => 120,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_text_transform' );

// HEADING FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_heading_font_weight' ) ) {
	function boldthemes_customize_heading_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'heading_font_weight', array(
			'label'     => esc_html__( 'Heading font weight', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[heading_font_weight]',
			'priority'  => 130,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_weights(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_font_weight' );

// HEADING FONT STYLE

if ( ! function_exists( 'boldthemes_customize_heading_font_style' ) ) {
	function boldthemes_customize_heading_font_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_font_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_font_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'heading_font_style', array(
			'label'     => esc_html__( 'Heading font style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[heading_font_style]',
			'priority'  => 140,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_font_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_font_style' );

// HEADING LETTER SPACING

if ( ! function_exists( 'boldthemes_customize_heading_letter_spacing' ) ) {
	function boldthemes_customize_heading_letter_spacing( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[heading_letter_spacing]', array(
			'default'           => BoldThemes_Customize_Default::$data['heading_letter_spacing'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'heading_letter_spacing', array(
			'label'    => esc_html__( 'Heading letter spacing', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_typo_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[heading_letter_spacing]',
			'priority' => 140,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_heading_letter_spacing' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_heading_letter_spacing' );

// SUPERTITLE FONT

if ( ! function_exists( 'boldthemes_customize_supertitle_font' ) ) {
	function boldthemes_customize_supertitle_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'supertitle_font', array(
			'label'     => esc_html__( 'Supertitle font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[supertitle_font]',
			'priority'  => 150,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_font' );

// SUPERTITLE FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_supertitle_font_load_font_extension' ) ) {
	function boldthemes_customize_supertitle_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'supertitle_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom supertitle font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[supertitle_font_load_font_extension]',
			'priority'  => 150,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_font_load_font_extension' );

// SUPERTITLE TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_supertitle_text_transform' ) ) {
	function boldthemes_customize_supertitle_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'supertitle_text_transform', array(
			'label'     => esc_html__( 'Supertitle text transform', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[supertitle_text_transform]',
			'priority'  => 160,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_text_transform' );

// SUPERTITLE FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_supertitle_font_weight' ) ) {
	function boldthemes_customize_supertitle_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'supertitle_font_weight', array(
			'label'     => esc_html__( 'Supertitle font weight', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[supertitle_font_weight]',
			'priority'  => 170,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_weights(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_font_weight' );

// SUPERTITLE FONT STYLE

if ( ! function_exists( 'boldthemes_customize_supertitle_font_style' ) ) {
	function boldthemes_customize_supertitle_font_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_font_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_font_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'supertitle_font_style', array(
			'label'     => esc_html__( 'Supertitle font style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[supertitle_font_style]',
			'priority'  => 180,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_font_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_font_style' );

// SUPERTITLE LETTER SPACING

if ( ! function_exists( 'boldthemes_customize_supertitle_letter_spacing' ) ) {
	function boldthemes_customize_supertitle_letter_spacing( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[supertitle_letter_spacing]', array(
			'default'           => BoldThemes_Customize_Default::$data['supertitle_letter_spacing'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'supertitle_letter_spacing', array(
			'label'    => esc_html__( 'Supertitle letter spacing', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_typo_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[supertitle_letter_spacing]',
			'priority' => 180,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_supertitle_letter_spacing' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_supertitle_letter_spacing' );

// SUBTITLE FONT

if ( ! function_exists( 'boldthemes_customize_subtitle_font' ) ) {
	function boldthemes_customize_subtitle_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'subtitle_font', array(
			'label'     => esc_html__( 'Subtitle font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[subtitle_font]',
			'priority'  => 190,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_font' );

// SUBTITLE FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_subtitle_font_load_font_extension' ) ) {
	function boldthemes_customize_subtitle_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'subtitle_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom subtitle font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[subtitle_font_load_font_extension]',
			'priority'  => 190,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_font_load_font_extension' );

// SUBTITLE TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_subtitle_text_transform' ) ) {
	function boldthemes_customize_subtitle_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'subtitle_text_transform', array(
			'label'     => esc_html__( 'Subtitle text transform', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[subtitle_text_transform]',
			'priority'  => 200,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_text_transform' );

// SUBTITLE FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_subtitle_font_weight' ) ) {
	function boldthemes_customize_subtitle_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'subtitle_font_weight', array(
			'label'     => esc_html__( 'Subtitle font weight', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[subtitle_font_weight]',
			'priority'  => 210,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_weights(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_font_weight' );

// SUBTITLE FONT STYLE

if ( ! function_exists( 'boldthemes_customize_subtitle_font_style' ) ) {
	function boldthemes_customize_subtitle_font_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_font_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_font_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'subtitle_font_style', array(
			'label'     => esc_html__( 'Subtitle font style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[subtitle_font_style]',
			'priority'  => 220,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_font_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_font_style' );

// SUBTITLE LETTER SPACING

if ( ! function_exists( 'boldthemes_customize_subtitle_letter_spacing' ) ) {
	function boldthemes_customize_subtitle_letter_spacing( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[subtitle_letter_spacing]', array(
			'default'           => BoldThemes_Customize_Default::$data['subtitle_letter_spacing'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'subtitle_letter_spacing', array(
			'label'    => esc_html__( 'Subtitle letter spacing', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_typo_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[subtitle_letter_spacing]',
			'priority' => 220,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_subtitle_letter_spacing' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_subtitle_letter_spacing' );


// BUTTONS TYPOGRAPHY SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_typo_buttons' ) ) {
	function boldthemes_customize_separator_typo_buttons( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_typo_buttons', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_typo_buttons',
			array(
				'label' => esc_html__( 'Buttons', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_typo_section',
				'priority' => 300
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_typo_buttons' );

// BUTTON FONT

if ( ! function_exists( 'boldthemes_customize_button_font' ) ) {
	function boldthemes_customize_button_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_font', array(
			'label'     => esc_html__( 'Button font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_font]',
			'priority'  => 310,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_font' );

// BUTTON FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_button_font_load_font_extension' ) ) {
	function boldthemes_customize_button_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'button_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom button font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_font_load_font_extension]',
			'priority'  => 310,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_font_load_font_extension' );

// BUTTON  TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_button_text_transform' ) ) {
	function boldthemes_customize_button_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_text_transform', array(
			'label'     => esc_html__( 'Button text transform', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_text_transform]',
			'priority'  => 320,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_text_transform' );

// BUTTON FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_button_font_weight' ) ) {
	function boldthemes_customize_button_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_font_weight', array(
			'label'     => esc_html__( 'Button font weight', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_font_weight]',
			'priority'  => 330,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_weights(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_font_weight' );

// BUTTON FONT STYLE

if ( ! function_exists( 'boldthemes_customize_button_font_style' ) ) {
	function boldthemes_customize_button_font_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_font_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_font_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_font_style', array(
			'label'     => esc_html__( 'Button font style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_font_style]',
			'priority'  => 340,
			'type'      => 'select',
			'choices'   => boldthemes_get_font_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_font_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_font_style' );

// BUTTON STYLE

if ( ! function_exists( 'boldthemes_customize_button_style' ) ) {
	function boldthemes_customize_button_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_style]', array(
			'default'           => urlencode( BoldThemes_Customize_Default::$data['button_style'] ),
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_style', array(
			'label'     => esc_html__( 'Button style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_style]',
			'priority'  => 350,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_style' );

// BUTTON LETTER SPACING

if ( ! function_exists( 'boldthemes_customize_button_letter_spacing' ) ) {
	function boldthemes_customize_button_letter_spacing( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_letter_spacing]', array(
			'default'           => BoldThemes_Customize_Default::$data['button_letter_spacing'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_letter_spacing', array(
			'label'    => esc_html__( 'Button letter spacing', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_typo_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[button_letter_spacing]',
			'priority' => 350,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_letter_spacing' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_letter_spacing' );

// BUTTON SHAPE

if ( ! function_exists( 'boldthemes_customize_button_shape' ) ) {
	function boldthemes_customize_button_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_shape]', array(
			'default'           => urlencode( BoldThemes_Customize_Default::$data['button_shape'] ),
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_shape', array(
			'label'     => esc_html__( 'Button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_shape]',
			'priority'  => 360,
			'type'      => 'select',
			'choices'   => boldthemes_get_typography_button_shapes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_shape' );

// BUTTON COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_button_color_scheme' ) ) {
	function boldthemes_customize_button_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[button_color_scheme]', array(
			'default'           => urlencode( BoldThemes_Customize_Default::$data['button_color_scheme'] ),
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'button_color_scheme', array(
			'label'     => esc_html__( 'Button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[button_color_scheme]',
			'priority'  => 370,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_button_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_button_color_scheme' );

// MENUS TYPOGRAPHY SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_typo_menus' ) ) {
	function boldthemes_customize_separator_typo_menus( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_typo_menus', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_typo_menus',
			array(
				'label' => esc_html__( 'Menus', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_typo_section',
				'priority' => 400
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_typo_menus' );

// MENU FONT

if ( ! function_exists( 'boldthemes_customize_menu_font' ) ) {
	function boldthemes_customize_menu_font( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_font]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_font'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_font', array(
			'label'     => esc_html__( 'Menu font', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_font]',
			'priority'  => 410,
			'type'      => 'select',
			'choices'   => BoldThemesFramework::$customize_fonts
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_font' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_font' );

// MENU FONT CUSTOM LOAD EXTENSION

if ( ! function_exists( 'boldthemes_customize_menu_font_load_font_extension' ) ) {
	function boldthemes_customize_menu_font_load_font_extension( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_font_load_font_extension]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_font_load_font_extension'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));    
		$wp_customize->add_control( 'menu_font_load_font_extension', array(
			'label'     => esc_html__( 'Custom menu font load extension', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_font_load_font_extension]',
			'priority'  => 410,
			'type'      => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_font_load_font_extension' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_font_load_font_extension' );


// MENU FIRST LEVEL TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_menu_first_level_text_transform' ) ) {
	function boldthemes_customize_menu_first_level_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_first_level_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_first_level_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_first_level_text_transform', array(
			'label'     => esc_html__( 'Menu text transform (first level)', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_first_level_text_transform]',
			'priority'  => 420,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_first_level_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_first_level_text_transform' );

// MENU OTHER LEVELS TEXT TRANSFORM

if ( ! function_exists( 'boldthemes_customize_menu_other_levels_text_transform' ) ) {
	function boldthemes_customize_menu_other_levels_text_transform( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_other_levels_text_transform]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_other_levels_text_transform'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_other_levels_text_transform', array(
			'label'     => esc_html__( 'Menu text transform (other levels)', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_other_levels_text_transform]',
			'priority'  => 430,
			'type'      => 'select',
			'choices'   => boldthemes_get_text_transforms(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_other_levels_text_transform' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_other_levels_text_transform' );

// MENU FIRST LEVEL FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_menu_first_level_font_weight' ) ) {
	function boldthemes_customize_menu_first_level_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_first_level_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_first_level_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_first_level_font_weight', array(
			'label'     => esc_html__( 'Menu font weight (first level)', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_first_level_font_weight]',
			'priority'  => 440,
			'type'      => 'select',
			'choices'   => array(
				'default' 				=> esc_html__( 'Default', 'campo' ),
				'lighter' 				=> esc_html__( 'Lighter (1st level only)', 'campo' ),
				'normal' 				=> esc_html__( 'Normal (1st level only)', 'campo' ),
				'bold' 					=> esc_html__( 'Bold (1st level only)', 'campo' ),
				'bolder' 				=> esc_html__( 'Bolder (1st level only)', 'campo' ),
				'100' 					=> esc_html__( '100 (1st level only)', 'campo' ),
				'200' 					=> esc_html__( '200 (1st level only)', 'campo' ),
				'300' 					=> esc_html__( '300 (1st level only)', 'campo' ),
				'400' 					=> esc_html__( '400 (1st level only)', 'campo' ),
				'500' 					=> esc_html__( '500 (1st level only)', 'campo' ),
				'600' 					=> esc_html__( '600 (1st level only)', 'campo' ),
				'700' 					=> esc_html__( '700 (1st level only)', 'campo' ),
				'800' 					=> esc_html__( '800 (1st level only)', 'campo' ),
				'900' 					=> esc_html__( '900 (1st level only)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_first_level_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_first_level_font_weight' );

// MENU OTHER LEVELS FONT WEIGHT

if ( ! function_exists( 'boldthemes_customize_menu_other_levels_font_weight' ) ) {
	function boldthemes_customize_menu_other_levels_font_weight( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_other_levels_font_weight]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_other_levels_font_weight'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_other_levels_font_weight', array(
			'label'     => esc_html__( 'Menu font weight (other levels)', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_typo_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_other_levels_font_weight]',
			'priority'  => 450,
			'type'      => 'select',
			'choices'   => array(
				'default' 				=> esc_html__( 'Default', 'campo' ),
				'lighter' 				=> esc_html__( 'Lighter (other levels)', 'campo' ),
				'normal' 				=> esc_html__( 'Normal (other levels)', 'campo' ),
				'bold' 					=> esc_html__( 'Bold (other levels)', 'campo' ),
				'bolder' 				=> esc_html__( 'Bolder (other levels)', 'campo' ),
				'100' 					=> esc_html__( '100 (other levels)', 'campo' ),
				'200' 					=> esc_html__( '200 (other levels)', 'campo' ),
				'300' 					=> esc_html__( '300 (other levels)', 'campo' ),
				'400' 					=> esc_html__( '400 (other levels)', 'campo' ),
				'500' 					=> esc_html__( '500 (other levels)', 'campo' ),
				'600' 					=> esc_html__( '600 (other levels)', 'campo' ),
				'700' 					=> esc_html__( '700 (other levels)', 'campo' ),
				'800' 					=> esc_html__( '800 (other levels)', 'campo' ),
				'900' 					=> esc_html__( '900 (other levels)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_other_levels_font_weight' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_other_levels_font_weight' );

/* DEFAULT HEADLINE SECTION */

// DEFAULT HEADLINE LAYOUT

if ( ! function_exists( 'boldthemes_customize_default_headline_height' ) ) {
	function boldthemes_customize_default_headline_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_height', array(
			'label'     	=> esc_html__( 'Default headline height', 'campo' ),
			'description'   => esc_html__( 'Select \'none\' to disable default headline', 'campo' ),
			'section'   	=> BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  	=> BoldThemesFramework::$pfx . '_theme_options[default_headline_height]',
			'priority'  	=> 10,
			'type'      	=> 'select',
			'choices'   	=> array(
				'none' 				=> esc_html__( 'None', 'campo' ),
				'thin' 				=> esc_html__( 'Thin', 'campo' ),
				'regular' 			=> esc_html__( 'Regular', 'campo' ),
				'thick' 			=> esc_html__( 'Thick', 'campo' ),
				'fullscreen' 		=> esc_html__( 'Fullscreen', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_height' );

// DEFAULT HEADLINE PARALLAX SPEED

if ( ! function_exists( 'boldthemes_customize_default_headline_parallax' ) ) {
	function boldthemes_customize_default_headline_parallax( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_parallax]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_parallax'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_parallax', array(
			'label'     => esc_html__( 'Default headline parallax speed', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_parallax]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => array(
				'none' 				=> esc_html__( 'None', 'campo' ),
				'slow' 				=> esc_html__( 'Slow', 'campo' ),
				'normal' 			=> esc_html__( 'Normal', 'campo' ),
				'fast' 				=> esc_html__( 'Fast', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_parallax' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_parallax' );

// DEFAULT HEADLINE OVERLAY

if ( ! function_exists( 'boldthemes_customize_default_headline_overlay' ) ) {
	function boldthemes_customize_default_headline_overlay( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_overlay]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_overlay'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_overlay', array(
			'label'     => esc_html__( 'Default headline overlay', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_overlay]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => array(
				'none' 				=> esc_html__( 'None', 'campo' ),
				'dark-90' 			=> esc_html__( 'Dark 90%', 'campo' ),
				'dark-80' 			=> esc_html__( 'Dark 80%', 'campo' ),
				'dark-70' 			=> esc_html__( 'Dark 70%', 'campo' ),
				'dark-60' 			=> esc_html__( 'Dark 60%', 'campo' ),
				'dark-50' 			=> esc_html__( 'Dark 50%', 'campo' ),
				'dark-40' 			=> esc_html__( 'Dark 40%', 'campo' ),
				'dark-30' 			=> esc_html__( 'Dark 30%', 'campo' ),
				'dark-20' 			=> esc_html__( 'Dark 20%', 'campo' ),
				'dark-10' 			=> esc_html__( 'Dark 10%', 'campo' ),
				'light-90' 			=> esc_html__( 'Light 90%', 'campo' ),
				'light-80' 			=> esc_html__( 'Light 80%', 'campo' ),
				'light-70' 			=> esc_html__( 'Light 70%', 'campo' ),
				'light-60' 			=> esc_html__( 'Light 60%', 'campo' ),
				'light-50' 			=> esc_html__( 'Light 50%', 'campo' ),
				'light-40' 			=> esc_html__( 'Light 40%', 'campo' ),
				'light-30' 			=> esc_html__( 'Light 30%', 'campo' ),
				'light-20' 			=> esc_html__( 'Light 20%', 'campo' ),
				'light-10' 			=> esc_html__( 'Light 10%', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_overlay' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_overlay' );


// DEFAULT HEADLINE ALIGNMENT

if ( ! function_exists( 'boldthemes_customize_default_headline_alignment' ) ) {
	function boldthemes_customize_default_headline_alignment( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_alignment]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_alignment'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_alignment', array(
			'label'     => esc_html__( 'Default headline alignment', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_alignment]',
			'priority'  => 31,
			'type'      => 'select',
			'choices'   => array(
				'inherit' 			=> esc_html__( 'Inherit', 'campo' ),
				'left' 				=> esc_html__( 'Left', 'campo' ),
				'center' 			=> esc_html__( 'Center', 'campo' ),
				'right' 			=> esc_html__( 'Right', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_alignment' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_alignment' );



// DEFAULT HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_default_headline_size' ) ) {
	function boldthemes_customize_default_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_size', array(
			'label'     => esc_html__( 'Default headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_size]',
			'priority'  => 40,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_size' );

// DEFAULT HEADLINE H TAG

if ( ! function_exists( 'boldthemes_customize_default_headline_h_tag' ) ) {
	function boldthemes_customize_default_headline_h_tag( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_h_tag]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_h_tag'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_h_tag', array(
			'label'     => esc_html__( 'Default headline title tag', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_h_tag]',
			'priority'  => 50,
			'type'      => 'select',
			'choices'   => array(
				'p'     	=> esc_html__( 'p', 'campo' ),
				'h1'    	=> esc_html__( 'h1', 'campo' ),
				'h2'     	=> esc_html__( 'h2', 'campo' ),
				'h3'     	=> esc_html__( 'h3', 'campo' ),
				'h4'     	=> esc_html__( 'h4', 'campo' ),
				'h5'     	=> esc_html__( 'h5', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_h_tag' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_h_tag' );

// DEFAULT HEADLINE WIDTH

if ( ! function_exists( 'boldthemes_customize_default_headline_width' ) ) {
	function boldthemes_customize_default_headline_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_width', array(
			'label'     		=> esc_html__( 'Default headline width', 'campo' ),
			'section'   		=> BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  		=> BoldThemesFramework::$pfx . '_theme_options[default_headline_width]',
			'priority'  		=> 60,
			'type'      		=> 'select',
			'choices'   => array(
				'wide-wide' 		=> esc_html__( 'Wide', 'campo' ),
				'wide-boxed-1200' 	=> esc_html__( 'Wide, boxed content 1200', 'campo' ),
				'wide-boxed-1400' 	=> esc_html__( 'Wide, boxed content 1400', 'campo' ),
				'wide-boxed-1600' 	=> esc_html__( 'Wide, boxed content 1600', 'campo' ),
				'wide-boxed-1800' 	=> esc_html__( 'Wide, boxed content 1800', 'campo' ),
				'boxed-boxed-1200' 	=> esc_html__( 'Boxed 1200', 'campo' ),
				'boxed-boxed-1400' 	=> esc_html__( 'Boxed 1400', 'campo' ),
				'boxed-boxed-1600' 	=> esc_html__( 'Boxed 1600', 'campo' ),
				'boxed-boxed-1800' 	=> esc_html__( 'Boxed 1800', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_width' );


// DEAFULT HEADLINE COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_default_headline_color_scheme' ) ) {
	function boldthemes_customize_separator_default_headline_color_scheme( $wp_customize ) {
		$wp_customize->add_setting( 'separator_default_headline_color_scheme', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, 'separator_default_headline_color_scheme',
			array(
				'label' => esc_html__( 'Colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_default_headline_section',
				'priority' => 100
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_default_headline_color_scheme' );

// DEFAULT HEADLINE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_default_headline_color_scheme' ) ) {
	function boldthemes_customize_default_headline_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['default_headline_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'default_headline_color_scheme', array(
			'label'     => esc_html__( 'Default headline color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[default_headline_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_color_scheme' );

// DEFAULT HEADLINE CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_default_headline_background_color' ) ) {
	function boldthemes_customize_default_headline_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['default_headline_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'default_headline_background_color', array(
			'label'    => esc_html__( 'Default headline bg', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[default_headline_background_color]',
			'priority' => 120,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_background_color' );

// DEFAULT HEADLINE CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_default_headline_color' ) ) {
	function boldthemes_customize_default_headline_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[default_headline_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['default_headline_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'default_headline_color', array(
			'label'    => esc_html__( 'Default headline text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[default_headline_color]',
			'priority' => 130,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_default_headline_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_default_headline_color' );

// DEAFULT HEADLINE OPTIONS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_default_headline_options' ) ) {
	function boldthemes_customize_separator_default_headline_options( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_default_headline_options', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_default_headline_options',
			array(
				'label' => esc_html__( 'Display options', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_default_headline_section',
				'priority' => 200
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_default_headline_options' );

// PAGE SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_page_single_show_superheadline' ) ) {
	function boldthemes_customize_page_single_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'page_single_show_superheadline',
			array(
				'label'    	=> esc_html__( 'Show in page superheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_default_headline_section',
				'priority' 	=> 210,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[page_single_show_superheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_show_superheadline' );

// PAGE SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_page_single_show_subheadline' ) ) {
	function boldthemes_customize_page_single_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'page_single_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in page subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_default_headline_section',
				'priority' 	=> 220,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[page_single_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_show_subheadline' );

// PAGE SHARE

if ( ! function_exists( 'boldthemes_customize_page_single_share' ) ) {
	function boldthemes_customize_page_single_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'page_single_share',
			array(
				'label'    		=> esc_html__( 'Page share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline or superheadline.', 'campo' ),
				'section'  		=> BoldThemesFramework::$pfx . '_default_headline_section',
				'priority' 		=> 230,
				'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[page_single_share]',
				'choices'  		=> boldthemes_get_share_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_share' );

// PAGE SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_page_single_share_size' ) ) {
	function boldthemes_customize_page_single_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'page_single_share_size', array(
			'label'     => esc_html__( 'Page share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[page_single_share_size]',
			'priority'  => 240,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_share_size' );

// PAGE SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_page_single_share_style' ) ) {
	function boldthemes_customize_page_single_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'page_single_share_style', array(
			'label'     => esc_html__( 'Page share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[page_single_share_style]',
			'priority'  => 250,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_share_style' );

// PAGE SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_page_single_share_shape' ) ) {
	function boldthemes_customize_page_single_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'page_single_share_shape', array(
			'label'     => esc_html__( 'Page share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[page_single_share_shape]',
			'priority'  => 260,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_share_shape' );

// PAGE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_page_single_share_color_scheme' ) ) {
	function boldthemes_customize_page_single_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_single_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_single_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'page_single_share_color_scheme', array(
			'label'     => esc_html__( 'Page share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_default_headline_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[page_single_share_color_scheme]',
			'priority'  => 270,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_single_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_single_share_color_scheme' );

/* HEADER AND FOOTER SECTION */

// HEADER WIDTH

if ( ! function_exists( 'boldthemes_customize_header_width' ) ) {
	function boldthemes_customize_header_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[header_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['header_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'header_width', array(
			'label'     		=> esc_html__( 'Header width', 'campo' ),
			'section'   		=> BoldThemesFramework::$pfx . '_header_section',
			'settings'  		=> BoldThemesFramework::$pfx . '_theme_options[header_width]',
			'priority'  		=> 10,
			'type'      		=> 'select',
			'choices'   => array(
				'wide-wide' 		=> esc_html__( 'Wide', 'campo' ),
				'wide-boxed-1200' 	=> esc_html__( 'Wide, boxed menu 1200', 'campo' ),
				'wide-boxed-1400' 	=> esc_html__( 'Wide, boxed menu 1400', 'campo' ),
				'wide-boxed-1600' 	=> esc_html__( 'Wide, boxed menu 1600', 'campo' ),
				'wide-boxed-1800' 	=> esc_html__( 'Wide, boxed menu 1800', 'campo' ),
				'boxed-boxed-1200' 	=> esc_html__( 'Boxed 1200', 'campo' ),
				'boxed-boxed-1400' 	=> esc_html__( 'Boxed 1400', 'campo' ),
				'boxed-boxed-1600' 	=> esc_html__( 'Boxed 1600', 'campo' ),
				'boxed-boxed-1800' 	=> esc_html__( 'Boxed 1800', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_header_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_header_width' );

// LOGO

if ( ! function_exists( 'boldthemes_customize_logo' ) ) {
	function boldthemes_customize_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo', array(
			'label'    => esc_html__( 'Main logo', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[logo]',
			'priority' => 20,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_logo' );

// LOGO HEIGHT
/* CSS variable */

if ( ! function_exists( 'boldthemes_customize_logo_height' ) ) {
	function boldthemes_customize_logo_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[logo_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['logo_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'logo_height', array(
			'label'    => esc_html__( 'Logo height', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[logo_height]',
			'priority' => 30,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 80px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_logo_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_logo_height' );

// HEADER POSITION

if ( ! function_exists( 'boldthemes_customize_header_position' ) ) {
	function boldthemes_customize_header_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[header_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['header_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'header_position', array(
			'label'     => esc_html__( 'Header position', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[header_position]',
			'priority'  => 40,
			'type'      => 'select',
			'choices'   => array(
				'top' 			=> esc_html__( 'Header over content', 'campo' ),
				'above' 		=> esc_html__( 'Header above content', 'campo' ),
				'hidden' 		=> esc_html__( 'Hidden header', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_header_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_header_position' );

// PRIMARY MENU POSITION

if ( ! function_exists( 'boldthemes_customize_primary_menu_position' ) ) {
	function boldthemes_customize_primary_menu_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[primary_menu_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['primary_menu_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'primary_menu_position', array(
			'label'     => esc_html__( 'Primary menu position', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[primary_menu_position]',
			'priority'  => 50,
			'type'      => 'select',
			'choices'   => array(
				'logo-left' 		=> esc_html__( 'Logo area, left', 'campo' ),
				'logo-right' 		=> esc_html__( 'Logo area, right', 'campo' ),
				'logo-center' 		=> esc_html__( 'Logo area, center', 'campo' ),
				'bottom-left' 		=> esc_html__( 'Below logo area, left', 'campo' ),
				'bottom-right' 		=> esc_html__( 'Below logo area, right', 'campo' ),
				'bottom-center' 	=> esc_html__( 'Below logo area, center', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_primary_menu_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_primary_menu_position' );

// PRIMARY MENU REVERSE MENU INDEX

if ( ! function_exists( 'boldthemes_customize_primary_menu_reverse_menu_levels' ) ) {
	function boldthemes_customize_primary_menu_reverse_menu_levels( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[primary_menu_reverse_menu_levels]', array(
			'default'           => BoldThemes_Customize_Default::$data['primary_menu_reverse_menu_levels'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'primary_menu_reverse_menu_levels', array(
			'label'    		=> esc_html__( 'Switch dropdown direction index', 'campo' ),
			'description'   => esc_html__( 'Last n items will show dropdowns in the opposite direction', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[primary_menu_reverse_menu_levels]',
			'priority' 		=> 60,
			'type'     		=> 'number'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_primary_menu_reverse_menu_levels' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_primary_menu_reverse_menu_levels' );

// TOP BAR COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_top_bar_colors' ) ) {
	function boldthemes_customize_separator_top_bar_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_top_bar_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_top_bar_colors',
			array(
				'label' => esc_html__( 'Top bar colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_header_section',
				'priority' => 100
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_top_bar_colors' );

// TOP BAR COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_top_bar_color_scheme' ) ) {
	function boldthemes_customize_top_bar_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[top_bar_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['top_bar_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'top_bar_color_scheme', array(
			'label'     => esc_html__( 'Top bar color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[top_bar_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_top_bar_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_top_bar_color_scheme' );

// TOP BAR CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_top_bar_background_color' ) ) {
	function boldthemes_customize_top_bar_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[top_bar_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['top_bar_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'top_bar_background_color', array(
			'label'    => esc_html__( 'Top bar background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[top_bar_background_color]',
			'priority' => 120,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_top_bar_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_top_bar_background_color' );

// TOP BAR CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_top_bar_color' ) ) {
	function boldthemes_customize_top_bar_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[top_bar_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['top_bar_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'top_bar_color', array(
			'label'    => esc_html__( 'Top bar text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[top_bar_color]',
			'priority' => 130,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_top_bar_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_top_bar_color' );

// LOGO BAR COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_logo_bar_colors' ) ) {
	function boldthemes_customize_separator_logo_bar_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_logo_bar_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_logo_bar_colors',
			array(
				'label' => esc_html__( 'Logo bar colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_header_section',
				'priority' => 200
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_logo_bar_colors' );

// LOGO BAR COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_site_branding_bar_color_scheme' ) ) {
	function boldthemes_customize_site_branding_bar_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['site_branding_bar_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'site_branding_bar_color_scheme', array(
			'label'     => esc_html__( 'Logo bar color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_color_scheme]',
			'priority'  => 210,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_site_branding_bar_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_site_branding_bar_color_scheme' );

// LOGO BAR CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_site_branding_bar_background_color' ) ) {
	function boldthemes_customize_site_branding_bar_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['site_branding_bar_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'site_branding_bar_background_color', array(
			'label'    => esc_html__( 'Logo bar background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_background_color]',
			'priority' => 220,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_site_branding_bar_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_site_branding_bar_background_color' );


// LOGO BAR CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_site_branding_bar_color' ) ) {
	function boldthemes_customize_site_branding_bar_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['site_branding_bar_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'site_branding_bar_color', array(
			'label'    => esc_html__( 'Logo bar text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[site_branding_bar_color]',
			'priority' => 230,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_site_branding_bar_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_site_branding_bar_color' );

// MENU BAR COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_menu_colors' ) ) {
	function boldthemes_customize_separator_menu_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_menu_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_menu_colors',
			array(
				'label' => esc_html__( 'Menu bar colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_header_section',
				'priority' => 300
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_menu_colors' );

// MENU BAR STYLE

if ( ! function_exists( 'boldthemes_customize_menu_bar_color_scheme' ) ) {
	function boldthemes_customize_menu_bar_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_bar_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_bar_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_bar_color_scheme', array(
			'label'     => esc_html__( 'Menu bar color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_bar_color_scheme]',
			'priority'  => 310,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_bar_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_bar_color_scheme' );

// MENU BAR CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_menu_bar_background_color' ) ) {
	function boldthemes_customize_menu_bar_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_bar_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['menu_bar_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'menu_bar_background_color', array(
			'label'    => esc_html__( 'Menu bar background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[menu_bar_background_color]',
			'priority' => 320,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_bar_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_bar_background_color' );

// MENU BAR CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_menu_bar_color' ) ) {
	function boldthemes_customize_menu_bar_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_bar_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['menu_bar_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'menu_bar_color', array(
			'label'    => esc_html__( 'Menu bar text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[menu_bar_color]',
			'priority' => 330,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_bar_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_bar_color' );

// MENU HOVER STYLE

if ( ! function_exists( 'boldthemes_customize_menu_hover_color_scheme' ) ) {
	function boldthemes_customize_menu_hover_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_hover_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_hover_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_hover_color_scheme', array(
			'label'     => esc_html__( 'Menu hover color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_hover_color_scheme]',
			'priority'  => 340,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_hover_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_hover_color_scheme' );

// MENU DROPDOWN STYLE

if ( ! function_exists( 'boldthemes_customize_menu_dropdown_color_scheme' ) ) {
	function boldthemes_customize_menu_dropdown_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_dropdown_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_dropdown_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_dropdown_color_scheme', array(
			'label'     => esc_html__( 'Menu dropdown color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_dropdown_color_scheme]',
			'priority'  => 350,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_dropdown_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_dropdown_color_scheme' );

// MENU DROPDOWN HOVER STYLE

if ( ! function_exists( 'boldthemes_customize_menu_dropdown_hover_color_scheme' ) ) {
	function boldthemes_customize_menu_dropdown_hover_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[menu_dropdown_hover_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['menu_dropdown_hover_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'menu_dropdown_hover_color_scheme', array(
			'label'     => esc_html__( 'Menu dropdown hover color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[menu_dropdown_hover_color_scheme]',
			'priority'  => 360,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_menu_dropdown_hover_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_menu_dropdown_hover_color_scheme' );

/* STICKY SECTION */


// ENABLE STICKY

if ( ! function_exists( 'boldthemes_customize_enable_sticky' ) ) {
	function boldthemes_customize_enable_sticky( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[enable_sticky]', array(
			'default'           => BoldThemes_Customize_Default::$data['enable_sticky'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'enable_sticky', array(
			'label'    			=> esc_html__( 'Enable sticky header', 'campo' ),
			'section'  			=> BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' 			=> BoldThemesFramework::$pfx . '_theme_options[enable_sticky]',
			'priority' 			=> 10,
			'type'     			=> 'checkbox',
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_enable_sticky' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_enable_sticky' );


// STICKY TRANSITION

if ( ! function_exists( 'boldthemes_customize_sticky_style' ) ) {
	function boldthemes_customize_sticky_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			// 'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_style', array(
			'label'     => esc_html__( 'Sticky header style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[sticky_style]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => array(
				'classic' 				=> esc_html__( 'Classic', 'campo' ),
				'shrink' 				=> esc_html__( 'Fix & shrink', 'campo' ),
				'show-on-scroll-up' 	=> esc_html__( 'Show on scroll up', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_style' );

// SCROLL STICKY HEADER BREAKPOINT

if ( ! function_exists( 'boldthemes_customize_sticky_header_activation_scroll_breakpoint' ) ) {
	function boldthemes_customize_sticky_header_scroll_breakpoint( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_header_scroll_breakpoint]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_header_scroll_breakpoint'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_header_scroll_breakpoint', array(
			'label'    		=> esc_html__( 'Scroll breakpoint (in px)', 'campo' ),
			'description'	=> esc_html__( 'Defines scroll position when sticky header becomes active.', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[sticky_header_scroll_breakpoint]',
			'priority' 		=> 30,
			'type'     		=> 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 60px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_header_scroll_breakpoint' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_header_scroll_breakpoint' );

// STICKY LOGO

if ( ! function_exists( 'boldthemes_customize_sticky_logo' ) ) {
	function boldthemes_customize_sticky_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'sticky_logo', array(
			'label'    		=> esc_html__( 'Header logo', 'campo' ),
			'description'   => esc_html__( 'This logo is visible when sticky header is active', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[sticky_logo]',
			'priority' 		=> 40,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_logo' );

// STICKY HEADER WIDTH

if ( ! function_exists( 'boldthemes_customize_sticky_header_width' ) ) {
	function boldthemes_customize_sticky_header_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_header_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_header_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_header_width', array(
			'label'     		=> esc_html__( 'Header width', 'campo' ),
			'section'   		=> BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings'  		=> BoldThemesFramework::$pfx . '_theme_options[sticky_header_width]',
			'priority'  		=> 50,
			'type'      		=> 'select',
			'choices'   => array(
				'wide-wide' => esc_html__( 'Wide', 'campo' ),
				'wide-boxed-1200' 	=> esc_html__( 'Wide, boxed menu 1200', 'campo' ),
				'wide-boxed-1400' 	=> esc_html__( 'Wide, boxed menu 1400', 'campo' ),
				'wide-boxed-1600' 	=> esc_html__( 'Wide, boxed menu 1600', 'campo' ),
				'wide-boxed-1800' 	=> esc_html__( 'Wide, boxed menu 1800', 'campo' ),
				'boxed-boxed-1200' 	=> esc_html__( 'Boxed 1200', 'campo' ),
				'boxed-boxed-1400' 	=> esc_html__( 'Boxed 1400', 'campo' ),
				'boxed-boxed-1600' 	=> esc_html__( 'Boxed 1600', 'campo' ),
				'boxed-boxed-1800' 	=> esc_html__( 'Boxed 1800', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_header_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_header_width' );

// STICKY LOGO HEIGHT
/* CSS variable */

if ( ! function_exists( 'boldthemes_customize_sticky_logo_height' ) ) {
	function boldthemes_customize_sticky_logo_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_logo_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_logo_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_logo_height', array(
			'label'    => esc_html__( 'Logo height', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sticky_logo_height]',
			'priority' => 60,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 60px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_logo_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_logo_height' );

// STICKY COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_sticky_colors' ) ) {
	function boldthemes_customize_separator_sticky_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_sticky_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_sticky_colors',
			array(
				'label' => esc_html__( 'Colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
				'priority' => 100
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_sticky_colors' );

// STICKY LOGO BAR COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_sticky_site_branding_bar_color_scheme' ) ) {
	function boldthemes_customize_sticky_site_branding_bar_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_site_branding_bar_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_site_branding_bar_color_scheme', array(
			'label'     => esc_html__( 'Logo bar color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_site_branding_bar_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_site_branding_bar_color_scheme' );

// STICKY LOGO BAR CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_sticky_site_branding_bar_background_color' ) ) {
	function boldthemes_customize_sticky_site_branding_bar_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['sticky_site_branding_bar_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'			
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sticky_site_branding_bar_background_color', array(
			'label'    => esc_html__( 'Logo bar background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_background_color]',
			'priority' => 120,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_site_branding_bar_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_site_branding_bar_background_color' );

// STICKY LOGO BAR CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_sticky_site_branding_bar_color' ) ) {
	function boldthemes_customize_sticky_site_branding_bar_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['sticky_site_branding_bar_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'		
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sticky_site_branding_bar_color', array(
			'label'    => esc_html__( 'Logo bar text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sticky_site_branding_bar_color]',
			'priority' => 130,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_site_branding_bar_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_site_branding_bar_color' );

// STICKY MENU BAR STYLE

if ( ! function_exists( 'boldthemes_customize_sticky_menu_bar_color_scheme' ) ) {
	function boldthemes_customize_sticky_menu_bar_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['sticky_menu_bar_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'sticky_menu_bar_color_scheme', array(
			'label'     => esc_html__( 'Menu bar color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_color_scheme]',
			'priority'  => 140,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_menu_bar_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_menu_bar_color_scheme' );

// STICKY MENU BAR CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_sticky_menu_bar_background_color' ) ) {
	function boldthemes_customize_sticky_menu_bar_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['sticky_menu_bar_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sticky_menu_bar_background_color', array(
			'label'    => esc_html__( 'Menu bar background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_background_color]',
			'priority' => 150,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_menu_bar_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_menu_bar_background_color' );

// STICKY MENU BAR CUSTOM TEXT COLOR
if ( ! function_exists( 'boldthemes_customize_sticky_menu_bar_color' ) ) {
	function boldthemes_customize_sticky_menu_bar_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['sticky_menu_bar_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sticky_menu_bar_color', array(
			'label'    => esc_html__( 'Menu bar text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_sticky_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[sticky_menu_bar_color]',
			'priority' => 160,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_sticky_menu_bar_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_sticky_menu_bar_color' );

/* RESPONSIVE HEADER */

// HEADER RESPONSIVE BREAKPOINT

if ( ! function_exists( 'boldthemes_customize_header_responsive_breakpoint' ) ) {
	function boldthemes_customize_header_responsive_breakpoint( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[header_responsive_breakpoint]', array(
			'default'           => BoldThemes_Customize_Default::$data['header_responsive_breakpoint'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'header_responsive_breakpoint', array(
			'label'    => esc_html__( 'Responsive breakpoint (in px)', 'campo' ),
			'description'	=> esc_html__( 'Defines screen resolution when responsive menu becomes active. To enable vertical menu for all screens menu enter 999999.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[header_responsive_breakpoint]',
			'priority' => 10,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 1024)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_header_responsive_breakpoint' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_header_responsive_breakpoint' );

// RESPONSIVE LOGO

if ( ! function_exists( 'boldthemes_customize_responsive_logo' ) ) {
	function boldthemes_customize_responsive_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'         => 'postMessage'
			
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'responsive_logo', array(
			'label'    		=> esc_html__( 'Header logo', 'campo' ),
			'description'	=> esc_html__( 'This logo is visible in responsive header', 'campo' ),
			'section'   	=> BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[responsive_logo]',
			'priority' 		=> 20,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_logo' );

// RESPONSIVE STICKY LOGO

if ( ! function_exists( 'boldthemes_customize_responsive_sticky_logo' ) ) {
	function boldthemes_customize_responsive_sticky_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_sticky_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'         => 'postMessage'
			
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'responsive_sticky_logo', array(
			'label'    		=> esc_html__( 'Sticky header logo', 'campo' ),
			'description'	=> esc_html__( 'This logo is visible in sticky responsive header', 'campo' ),
			'section'   	=> BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_logo]',
			'priority' 		=> 25,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_sticky_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_sticky_logo' );

// RESPONSIVE LOGO HEIGHT
/* CSS variable */

if ( ! function_exists( 'boldthemes_customize_responsive_logo_height' ) ) {
	function boldthemes_customize_responsive_logo_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_logo_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_logo_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_logo_height', array(
			'label'    => esc_html__( 'Header logo height', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_logo_height]',
			'priority' => 30,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 70px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_logo_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_logo_height' );

// RESPONSIVE LOGO HEIGHT
/* CSS variable */

if ( ! function_exists( 'boldthemes_customize_responsive_sticky_logo_height' ) ) {
	function boldthemes_customize_responsive_sticky_logo_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_logo_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_sticky_logo_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_sticky_logo_height', array(
			'label'    => esc_html__( 'Sticky header logo height', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_logo_height]',
			'priority' => 40,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 70px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_sticky_logo_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_sticky_logo_height' );

// RESPONSIVE LOGO POSITION

if ( ! function_exists( 'boldthemes_customize_responsive_logo_position' ) ) {
	function boldthemes_customize_responsive_logo_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_logo_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_logo_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_logo_position', array(
			'label'     => esc_html__( 'Header logo position', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_logo_position]',
			'priority'  => 50,
			'type'      => 'select',
			'choices'   => array(
				'left' 			=> esc_html__( 'Left', 'campo' ),
				'right' 		=> esc_html__( 'Right', 'campo' ),
				'center' 		=> esc_html__( 'Center', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_logo_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_logo_position' );

// RESPONSIVE TRIGGER POSITION

if ( ! function_exists( 'boldthemes_customize_responsive_trigger_position' ) ) {
	function boldthemes_customize_responsive_trigger_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_trigger_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_trigger_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_trigger_position', array(
			'label'     => esc_html__( 'Header trigger position', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_trigger_position]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => array(
				'left' 			=> esc_html__( 'Left', 'campo' ),
				'right' 		=> esc_html__( 'Right', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_trigger_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_trigger_position' );

// RESPONSIVE MENU MAX WIDTH

if ( ! function_exists( 'boldthemes_customize_responsive_menu_max_width' ) ) {
	function boldthemes_customize_responsive_menu_max_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_max_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_menu_max_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_menu_max_width', array(
			'label'    		=> esc_html__( 'Menu max width ', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[responsive_menu_max_width]',
			'priority' 		=> 70,
			'type'     		=> 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 3em, 300px, 70% or calc(100% - 65px))', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_max_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_max_width' );

// RESPONSIVE MENU POSITION

if ( ! function_exists( 'boldthemes_customize_responsive_menu_position' ) ) {
	function boldthemes_customize_responsive_menu_position( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_position]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_menu_position'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_menu_position', array(
			'label'     => esc_html__( 'Menu alignment', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_menu_position]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => array(
				'left' 			=> esc_html__( 'Left', 'campo' ),
				'right' 		=> esc_html__( 'Right', 'campo' ),
				'full-screen' 	=> esc_html__( 'Full screen', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_position' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_position' );

// RESPONSIVE MENU LOGO

if ( ! function_exists( 'boldthemes_customize_responsive_menu_logo' ) ) {
	function boldthemes_customize_responsive_menu_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_menu_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'         => 'postMessage'
			
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'responsive_menu_logo', array(
			'label'    		=> esc_html__( 'Menu logo', 'campo' ),
			'description'	=> esc_html__( 'This logo is visible above responsive menu (when menu is visible)', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[responsive_menu_logo]',
			'priority' 		=> 90,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_logo' );

// RESPONSIVE MENU LOGO HEIGHT
/* CSS variable */

if ( ! function_exists( 'boldthemes_customize_responsive_menu_logo_height' ) ) {
	function boldthemes_customize_responsive_menu_logo_height( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_logo_height]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_menu_logo_height'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_menu_logo_height', array(
			'label'    => esc_html__( 'Menu logo height', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_menu_logo_height]',
			'priority' => 100,
			'type'     => 'text',
			'input_attrs' => array(
				'placeholder' => __( '(e.g. 70px)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_logo_height' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_logo_height' );

// RESPONSIVE HEADER COLOR SCHEME SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_responsive_header_menu_colors' ) ) {
	function boldthemes_customize_separator_responsive_header_menu_colors( $wp_customize ) {
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_separator_responsive_header_menu_colors', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, BoldThemesFramework::$pfx . '_separator_responsive_header_menu_colors',
			array(
				'label' => esc_html__( 'Colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
				'priority' => 200
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_responsive_header_menu_colors' );

// RESPONSIVE HEADER COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_responsive_header_color_scheme' ) ) {
	function boldthemes_customize_responsive_header_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_header_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_header_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_header_color_scheme', array(
			'label'     => esc_html__( 'Header color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_header_color_scheme]',
			'priority'  => 210,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_header_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_header_color_scheme' );


// RESPONSIVE HEADER CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_responsive_header_background_color' ) ) {
	function boldthemes_customize_responsive_header_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_header_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_header_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_header_background_color', array(
			'label'    => esc_html__( 'Header background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_header_background_color]',
			'priority' => 220,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_header_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_header_background_color' );

// RESPONSIVE HEADER CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_responsive_header_color' ) ) {
	function boldthemes_customize_responsive_header_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_header_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_header_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_header_color', array(
			'label'    => esc_html__( 'Header text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_header_color]',
			'priority' => 230,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_header_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_header_color' );

// RESPONSIVE STICKY HEADER COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_responsive_sticky_header_color_scheme' ) ) {
	function boldthemes_customize_responsive_sticky_header_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_sticky_header_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_sticky_header_color_scheme', array(
			'label'     => esc_html__( 'Sticky header color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_color_scheme]',
			'priority'  => 240,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_sticky_header_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_sticky_header_color_scheme' );


// RESPONSIVE STICKY HEADER CUSTOM BACKGROUND
if ( ! function_exists( 'boldthemes_customize_responsive_sticky_header_background_color' ) ) {
	function boldthemes_customize_responsive_sticky_header_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_sticky_header_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_sticky_header_background_color', array(
			'label'    => esc_html__( 'Sticky header bg color', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_background_color]',
			'priority' => 250,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_sticky_header_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_sticky_header_background_color' );

// RESPONSIVE STICKY HEADER CUSTOM TEXT COLOR
if ( ! function_exists( 'boldthemes_customize_responsive_sticky_header_color' ) ) {
	function boldthemes_customize_responsive_sticky_header_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_sticky_header_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_sticky_header_color', array(
			'label'    => esc_html__( 'Sticky header text color', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_sticky_header_color]',
			'priority' => 260,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_sticky_header_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_sticky_header_color' );

// RESPONSIVE MENU STYLE

if ( ! function_exists( 'boldthemes_customize_responsive_menu_color_scheme' ) ) {
	function boldthemes_customize_responsive_menu_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['responsive_menu_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'responsive_menu_color_scheme', array(
			'label'     => esc_html__( 'Menu color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[responsive_menu_color_scheme]',
			'priority'  => 270,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_color_scheme' );


// RESPONSIVE MENU CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_responsive_menu_background_color' ) ) {
	function boldthemes_customize_responsive_menu_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_menu_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_menu_background_color', array(
			'label'    => esc_html__( 'Menu background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_menu_background_color]',
			'priority' => 280,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_background_color' );

// RESPONSIVE MENU CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_responsive_menu_color' ) ) {
	function boldthemes_customize_responsive_menu_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[responsive_menu_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['responsive_menu_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'responsive_menu_color', array(
			'label'    => esc_html__( 'Menu text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[responsive_menu_color]',
			'priority' => 290,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_responsive_menu_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_responsive_menu_color' );

// FOOTER PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_footer_page_slug' ) ) {
	function boldthemes_customize_footer_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[footer_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['footer_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'footer_page_slug', array(
			'label'    => esc_html__( 'Footer page', 'campo' ),
			'description'    => esc_html__( 'Use static page with content as a footer for all pages.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_footer_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[footer_page_slug]',
			'priority' => 10,
			'type'      => 'select',
			'choices'   => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_footer_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_footer_page_slug' );

// FOOTER WIDTH

if ( ! function_exists( 'boldthemes_customize_footer_width' ) ) {
	function boldthemes_customize_footer_width( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[footer_width]', array(
			'default'           => BoldThemes_Customize_Default::$data['footer_width'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'footer_width', array(
			'label'     		=> esc_html__( 'Widget area width', 'campo' ),
			'section'   		=> BoldThemesFramework::$pfx . '_footer_section',
			'settings'  		=> BoldThemesFramework::$pfx . '_theme_options[footer_width]',
			'priority'  		=> 20,
			'type'      		=> 'select',
			'choices'   => array(
				'wide-wide' 		=> esc_html__( 'Wide', 'campo' ),
				'wide-boxed-1200' 	=> esc_html__( 'Wide, boxed menu 1200', 'campo' ),
				'wide-boxed-1400' 	=> esc_html__( 'Wide, boxed menu 1400', 'campo' ),
				'wide-boxed-1600' 	=> esc_html__( 'Wide, boxed menu 1600', 'campo' ),
				'wide-boxed-1800' 	=> esc_html__( 'Wide, boxed menu 1800', 'campo' ),
				'boxed-boxed-1200' 	=> esc_html__( 'Boxed 1200', 'campo' ),
				'boxed-boxed-1400' 	=> esc_html__( 'Boxed 1400', 'campo' ),
				'boxed-boxed-1600' 	=> esc_html__( 'Boxed 1600', 'campo' ),
				'boxed-boxed-1800' 	=> esc_html__( 'Boxed 1800', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_footer_width' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_footer_width' );

// FOOTER COLORS SEPARATOR

if ( ! function_exists( 'boldthemes_customize_separator_footer_color_scheme' ) ) {
	function boldthemes_customize_separator_footer_color_scheme( $wp_customize ) {
		$wp_customize->add_setting( 'separator_footer_color_scheme', array(
			'sanitize_callback' => 'boldthemes_sanitize',
		) );
		$wp_customize->add_control( new BoldThemes_Customize_Separator_Control( $wp_customize, 'separator_footer_color_scheme',
			array(
				'label' => esc_html__( 'Colors & styles', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_footer_section',
				'priority' => 30
		)));	
	}
}

add_action( 'customize_register', 'boldthemes_customize_separator_footer_color_scheme' );

// FOOTER WIDGET STYLE

if ( ! function_exists( 'boldthemes_customize_footer_widgets_color_scheme' ) ) {
	function boldthemes_customize_footer_widgets_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[footer_widgets_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['footer_widgets_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'footer_widgets_color_scheme', array(
			'label'     => esc_html__( 'Widget area color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_footer_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[footer_widgets_color_scheme]',
			'priority'  => 40,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_footer_widgets_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_footer_widgets_color_scheme' );

// FOOTER WIDGET CUSTOM BACKGROUND

if ( ! function_exists( 'boldthemes_customize_footer_widgets_background_color' ) ) {
	function boldthemes_customize_footer_widgets_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[footer_widgets_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['footer_widgets_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_widgets_background_color', array(
			'label'    => esc_html__( 'Widget area background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_footer_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[footer_widgets_background_color]',
			'priority' => 50,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_footer_widgets_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_footer_widgets_background_color' );

// FOOTER WIDGET CUSTOM TEXT COLOR

if ( ! function_exists( 'boldthemes_customize_footer_widgets_color' ) ) {
	function boldthemes_customize_footer_widgets_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[footer_widgets_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['footer_widgets_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'             => 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_widgets_color', array(
			'label'    => esc_html__( 'Widget area text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_footer_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[footer_widgets_color]',
			'priority' => 60,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_footer_widgets_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_footer_widgets_color' );

/* Blog */

// BLOG LIST VIEW

if ( ! function_exists( 'boldthemes_customize_blog_list_view' ) ) {
	function boldthemes_customize_blog_list_view( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_view]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_view'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_view', array(
			'label'     => esc_html__( 'Archive layout', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_view]',
			'priority'  => 10,
			'type'      => 'select',
			'choices'   => boldthemes_get_archive_layout_options()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_view' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_view' );

if ( ! function_exists( 'boldthemes_customize_blog_list_load_animation' ) ) {
	function boldthemes_customize_blog_list_load_animation( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_load_animation]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_load_animation'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
		));
		$wp_customize->add_control( 'blog_list_load_animation', array(
			'label'     => esc_html__( 'Single post animation', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_load_animation]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => boldthemes_get_animations()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_load_animation' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_load_animation' );


// BLOG LIST HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_blog_list_headline_size' ) ) {
	function boldthemes_customize_blog_list_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_headline_size', array(
			'label'     => esc_html__( 'Headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_headline_size]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_headline_size' );

// BLOG LIST SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_blog_list_show_superheadline' ) ) {
	function boldthemes_customize_blog_list_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_list_show_superheadline',
			array(
				'label'    => esc_html__( 'Show in superheadline', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_blog_list_section',
				'priority' => 40,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[blog_list_show_superheadline]',
				'choices'  => boldthemes_get_meta_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_show_superheadline' );


// BLOG LIST SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_blog_list_show_subheadline' ) ) {
	function boldthemes_customize_blog_list_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_list_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_list_section',
				'priority'	=> 50,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_list_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_show_subheadline' );

// BLOG LIST SHOW EXCERPT

if ( ! function_exists( 'boldthemes_customize_blog_list_show_excerpt' ) ) {
	function boldthemes_customize_blog_list_show_excerpt( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_show_excerpt]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_show_excerpt'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage',
		));

		$wp_customize->add_control( 'blog_list_show_excerpt', array(
				'label'    	=> esc_html__( 'Show excerpt', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_list_section',
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_list_show_excerpt]',
				'priority' 	=> 60,
				'type'      => 'select',
				'choices'   => boldthemes_get_excerpt_options()
			)
		);
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_show_excerpt' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_show_excerpt' );

// BLOG LIST SHOW AFTER CONTENT

if ( ! function_exists( 'boldthemes_customize_blog_list_show_bottom' ) ) {
	function boldthemes_customize_blog_list_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_list_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_list_section',
				'priority' 	=> 70,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_list_show_bottom]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_show_bottom' );

// BLOG LIST SHARE

if ( ! function_exists( 'boldthemes_customize_blog_list_share' ) ) {
	function boldthemes_customize_blog_list_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_list_share',
			array(
				'label'    		=> esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  		=> BoldThemesFramework::$pfx . '_blog_list_section',
				'priority' 		=> 80,
				'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[blog_list_share]',
				'choices'  		=> boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_share' );

// BLOG LIST SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_blog_list_share_size' ) ) {
	function boldthemes_customize_blog_list_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_share_size', array(
			'label'     => esc_html__( 'Share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_share_size]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_share_size' );

// BLOG LIST SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_blog_list_share_style' ) ) {
	function boldthemes_customize_blog_list_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_share_style', array(
			'label'     => esc_html__( 'Share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_share_style]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_share_style' );

// BLOG LIST SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_blog_list_share_shape' ) ) {
	function boldthemes_customize_blog_list_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_share_shape', array(
			'label'     => esc_html__( 'Share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_share_shape]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_share_shape' );

// BLOG LIST SHARE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_blog_list_share_color_scheme' ) ) {
	function boldthemes_customize_blog_list_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage',
		));
		$wp_customize->add_control( 'blog_list_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_share_color_scheme]',
			'priority'  => 120,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_share_color_scheme' );

// BLOG LIST READ MORE STYLE

if ( ! function_exists( 'boldthemes_customize_blog_list_read_more_style' ) ) {
	function boldthemes_customize_blog_list_read_more_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_read_more_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_read_more_style', array(
			'label'     => esc_html__( 'Read more button style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_style]',
			'priority'  => 130,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_read_more_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_read_more_style' );

// BLOG LIST READ MORE ICON

if ( ! function_exists( 'boldthemes_customize_blog_list_read_more_icon' ) ) {
	function boldthemes_customize_blog_list_read_more_icon ( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_icon]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_read_more_icon'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'boldthemes_sanitize_iconpicker_control'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Iconpicker_Control(
			$wp_customize,
			'blog_list_read_more_icon',
			array(
				'label'     => esc_html__( 'Read more button icon', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
				'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_icon]',
				'priority'  => 140,
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_read_more_icon' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_read_more_icon' );

// BLOG LIST READ MORE SIZE

if ( ! function_exists( 'boldthemes_customize_blog_list_read_more_size' ) ) {
	function boldthemes_customize_blog_list_read_more_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_read_more_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_read_more_size', array(
			'label'     => esc_html__( 'Read more button size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_size]',
			'priority'  => 150,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_read_more_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_read_more_size' );

// BLOG LIST READ MORE SHAPE

if ( ! function_exists( 'boldthemes_customize_blog_list_read_more_shape' ) ) {
	function boldthemes_customize_blog_list_read_more_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_read_more_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_list_read_more_shape', array(
			'label'     => esc_html__( 'Read more button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_shape]',
			'priority'  => 160,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_read_more_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_read_more_shape' );

// BLOG LIST READ MORE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_blog_list_read_more_color_scheme' ) ) {
	function boldthemes_customize_blog_list_read_more_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_list_read_more_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'blog_list_read_more_color_scheme', array(
			'label'     => esc_html__( 'Read more button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_list_read_more_color_scheme]',
			'priority'  => 170,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_list_read_more_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_list_read_more_color_scheme' );

/* SINGLE POST */

// SINGLE POST SETTINGS PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_blog_settings_page_slug' ) ) {
	function boldthemes_customize_blog_settings_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_settings_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_settings_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'blog_settings_page_slug', array(
			'label'    => esc_html__( 'Single blog settings page', 'campo' ),
			'description'    => esc_html__( 'Use static page with content as a template for all single posts. Select the page which serves as a template.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[blog_settings_page_slug]',
			'priority' => 10,
			'type'     => 'select',
			'choices'  => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_settings_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_settings_page_slug' );

// BLOG SINGLE VIEW

if ( ! function_exists( 'boldthemes_customize_blog_single_view' ) ) {
	function boldthemes_customize_blog_single_view( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_view]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_view'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_view', array(
			'label'     => esc_html__( 'Blog post layout', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_view]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => array(
				'standard' => esc_html__( 'Standard', 'campo' ),
				'columns' => esc_html__( 'Columns', 'campo' )
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_view' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_view' );

// BLOG SINGLE HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_blog_single_headline_size' ) ) {
	function boldthemes_customize_blog_single_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_headline_size', array(
			'label'     => esc_html__( 'Headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_headline_size]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_headline_size' );

// BLOG SINGLE SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_blog_single_show_superheadline' ) ) {
	function boldthemes_customize_blog_single_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_single_show_superheadline',
			array(
				'label'    	=> esc_html__( 'Show in superheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_single_section',
				'priority' 	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_single_show_superheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_show_superheadline' );

// BLOG SINGLE SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_blog_single_show_subheadline' ) ) {
	function boldthemes_customize_blog_single_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_single_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_single_section',
				'priority' 	=> 50,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_single_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_show_subheadline' );


// BLOG SINGLE SHOW ON BOTTOM

if ( ! function_exists( 'boldthemes_customize_blog_single_show_bottom' ) ) {
	function boldthemes_customize_blog_single_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_single_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_blog_single_section',
				'priority' 	=> 60,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[blog_single_show_bottom]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_show_bottom' );

// BLOG POST SHARE

if ( ! function_exists( 'boldthemes_customize_blog_single_share' ) ) {
	function boldthemes_customize_blog_single_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_single_share',
			array(
				'label'    => esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_blog_single_section',
				'priority' => 70,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[blog_single_share]',
				'choices'  => boldthemes_get_share_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_share' );

// BLOG SINGLE SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_blog_single_share_size' ) ) {
	function boldthemes_customize_blog_single_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_share_size', array(
			'label'     => esc_html__( 'Share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_share_size]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_share_size' );

// BLOG SINGLE SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_blog_single_share_style' ) ) {
	function boldthemes_customize_blog_single_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_share_style', array(
			'label'     => esc_html__( 'Share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_share_style]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_share_style' );

// BLOG SINGLE SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_blog_single_share_shape' ) ) {
	function boldthemes_customize_blog_single_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_share_shape', array(
			'label'     => esc_html__( 'Share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_share_shape]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_share_shape' );

// BLOG SINGLE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_blog_single_share_color_scheme' ) ) {
	function boldthemes_customize_blog_single_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_share_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_share_color_scheme' );

// BLOG SINGLE ABOUT AUTHOR STYLE

if ( ! function_exists( 'boldthemes_customize_blog_single_about_author_style' ) ) {
	function boldthemes_customize_blog_single_about_author_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_about_author_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_about_author_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'blog_single_about_author_style', array(
			'label'     => esc_html__( 'Author info style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_about_author_style]',
			'priority'  => 120,
			'type'      => 'select',
			'choices'   => boldthemes_get_about_author_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_about_author_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_about_author_style' );

// BLOG SINGLE POST NAVIGATION
if ( ! function_exists( 'boldthemes_customize_blog_single_show_navigation' ) ) {
	function boldthemes_customize_blog_single_show_navigation( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blog_single_show_navigation]', array(
			'default'           => BoldThemes_Customize_Default::$data['blog_single_show_navigation'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'blog_single_show_navigation',
			array(
				'label'     => esc_html__( 'Show in prevoius/next post navigation', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_blog_single_section',
				'priority'  => 130,
				'settings'  => BoldThemesFramework::$pfx . '_theme_options[blog_single_show_navigation]',
				'choices'   => boldthemes_get_single_navigation_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_blog_single_show_navigation' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blog_single_show_navigation' );

/* PORTFOLIO */

// PORTFOLIO LIST VIEW

if ( ! function_exists( 'boldthemes_customize_portfolio_list_view' ) ) {
	function boldthemes_customize_portfolio_list_view( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_view]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_view'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_view', array(
			'label'     => esc_html__( 'Archive layout', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_view]',
			'priority'  => 10,
			'type'      => 'select',
			'choices'   => boldthemes_get_archive_layout_options()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_portfolio_list_view' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_portfolio_list_view' );

// PORTFOLIO LIST ANIMATION

if ( ! function_exists( 'boldthemes_customize_pf_list_load_animation' ) ) {
	function boldthemes_customize_pf_list_load_animation( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_load_animation]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_load_animation'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
		));
		$wp_customize->add_control( 'pf_list_load_animation', array(
			'label'     => esc_html__( 'Single post animation', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_load_animation]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => boldthemes_get_animations()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_load_animation' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_load_animation' );

// PORTFOLIO LIST HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_pf_list_headline_size' ) ) {
	function boldthemes_customize_pf_list_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_headline_size', array(
			'label'     => esc_html__( 'Headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_headline_size]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_headline_size' );

// PORTFOLIO LIST SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_pf_list_show_superheadline' ) ) {
	function boldthemes_customize_pf_list_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_list_show_superheadline',
			array(
				'label'    	=> esc_html__( 'Show in superheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_list_section',
				'priority' 	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_list_show_superheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_show_superheadline' );

// PORTFOLIO LIST SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_pf_list_show_subheadline' ) ) {
	function boldthemes_customize_pf_list_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_list_show_subheadline',
			array(
				'label'    => esc_html__( 'Show in subheadline', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_pf_list_section',
				'priority' => 50,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_list_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_show_subheadline' );

// PORTFOLIO LIST SHOW EXCERPT

if ( ! function_exists( 'boldthemes_customize_pf_list_show_excerpt' ) ) {
	function boldthemes_customize_pf_list_show_excerpt( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_show_excerpt]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_show_excerpt'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage',
		));

		$wp_customize->add_control( 'pf_list_show_excerpt', array(
				'label'    	=> esc_html__( 'Show excerpt', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_list_section',
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_list_show_excerpt]',
				'priority' 	=> 60,
				'type'      => 'select',
				'choices'   => boldthemes_get_excerpt_options()
			)
		);
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_show_excerpt' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_show_excerpt' );


// PORTFOLIo LIST SHOW AFTER CONTENT

if ( ! function_exists( 'boldthemes_customize_pf_list_show_bottom' ) ) {
	function boldthemes_customize_pf_list_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_list_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_list_section',
				'priority' 	=> 70,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_list_show_bottom]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_show_bottom' );

// PORTFOLIO LIST SHARE

if ( ! function_exists( 'boldthemes_customize_pf_list_share' ) ) {
	function boldthemes_customize_pf_list_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_list_share',
			array(
				'label'    => esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_pf_list_section',
				'priority' => 80,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_list_share]',
				'choices'  => boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_share' );

// PORTFOLIO LIST SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_pf_list_share_size' ) ) {
	function boldthemes_customize_pf_list_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_share_size', array(
			'label'     => esc_html__( 'Share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_share_size]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_share_size' );

// PORTFOLIO LIST SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_pf_list_share_style' ) ) {
	function boldthemes_customize_pf_list_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_share_style', array(
			'label'     => esc_html__( 'Share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_share_style]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_share_style' );

// PORTFOLIO LIST SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_pf_list_share_shape' ) ) {
	function boldthemes_customize_pf_list_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_share_shape', array(
			'label'     => esc_html__( 'Share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_share_shape]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_share_shape' );

// PORTFOLIO LIST SHARE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_pf_list_share_color_scheme' ) ) {
	function boldthemes_customize_pf_list_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage',
		));
		$wp_customize->add_control( 'pf_list_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_share_color_scheme]',
			'priority'  => 120,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_share_color_scheme' );

// PORTFOLIO LIST READ MORE STYLE

if ( ! function_exists( 'boldthemes_customize_pf_list_read_more_style' ) ) {
	function boldthemes_customize_pf_list_read_more_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_read_more_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_read_more_style', array(
			'label'     => esc_html__( 'Read more button style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_style]',
			'priority'  => 130,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_read_more_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_read_more_style' );

// PORTFOLIO LIST READ MORE ICON

if ( ! function_exists( 'boldthemes_customize_pf_list_read_more_icon' ) ) {
	function boldthemes_customize_pf_list_read_more_icon ( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_icon]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_read_more_icon'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'boldthemes_sanitize_iconpicker_control'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Iconpicker_Control(
			$wp_customize,
			'pf_list_read_more_icon',
			array(
				'label'     => esc_html__( 'Read more button icon', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
				'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_icon]',
				'priority'  => 140,
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_read_more_icon' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_read_more_icon' );

// PORTFOLIO LIST READ MORE SIZE

if ( ! function_exists( 'boldthemes_customize_pf_list_read_more_size' ) ) {
	function boldthemes_customize_pf_list_read_more_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_read_more_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_read_more_size', array(
			'label'     => esc_html__( 'Read more button size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_size]',
			'priority'  => 150,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_read_more_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_read_more_size' );

// PORTFOLIO LIST READ MORE SHAPE

if ( ! function_exists( 'boldthemes_customize_pf_list_read_more_shape' ) ) {
	function boldthemes_customize_pf_list_read_more_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_read_more_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_list_read_more_shape', array(
			'label'     => esc_html__( 'Read more button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_shape]',
			'priority'  => 160,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_read_more_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_read_more_shape' );

// PORTFOLIO LIST READ MORE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_pf_list_read_more_color_scheme' ) ) {
	function boldthemes_customize_pf_list_read_more_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_list_read_more_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_list_read_more_color_scheme', array(
			'label'     => esc_html__( 'Read more button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_list_read_more_color_scheme]',
			'priority'  => 170,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_list_read_more_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_list_read_more_color_scheme' );

// PORTFOLIO SLUG

if ( ! function_exists( 'boldthemes_customize_pf_slug' ) ) {
	function boldthemes_customize_pf_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));
		$wp_customize->add_control( 'pf_slug', array(
			'label'    => esc_html__( 'Portfolio slug', 'campo' ),
			'description'    => esc_html__( 'This value will be used to generate urls for Portfolio custom post type. To apply changes, go to Settings > Permalinks and click Save Changes', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_slug]',
			'priority' => 180,
			'type'     => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_slug' );

// PORTFOLIO CATEGORY SLUG

if ( ! function_exists( 'boldthemes_customize_pf_category_slug' ) ) {
	function boldthemes_customize_pf_category_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_category_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_category_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		));
		$wp_customize->add_control( 'pf_category_slug', array(
			'label'    => esc_html__( 'Portfolio category slug', 'campo' ),
			'description'    => esc_html__( 'This value will be used to generate urls for Portfolio category custom post type. To apply changes, go to Settings > Permalinks and click Save Changes', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_pf_list_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_category_slug]',
			'priority' => 190,
			'type'     => 'text'
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_category_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_category_slug' );

/* PORTFOLIO SINGLE */

// SINGLE PORTFOLIO SETTINGS PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_pf_settings_page_slug' ) ) {
	function boldthemes_customize_pf_settings_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_settings_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_settings_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'pf_settings_page_slug', array(
			'label'    => esc_html__( 'Single portfolio settings page', 'campo' ),
			'description'    => esc_html__( 'Use static page with content as a template for all single posts. Select the page which serves as a template.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_settings_page_slug]',
			'priority' => 10,
			'type'     => 'select',
			'choices'  => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_settings_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_settings_page_slug' );

// PORTFOLIO SINGLE VIEW

if ( ! function_exists( 'boldthemes_customize_pf_single_view' ) ) {
	function boldthemes_customize_pf_single_view( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_view]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_view'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_single_view', array(
			'label'     => esc_html__( 'Single project view', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_view]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => array(
				'standard' => esc_html__( 'Standard', 'campo' ),
				'columns-1' => esc_html__( 'Columns 1 (media, content + meta)', 'campo' ),
				'columns-2' => esc_html__( 'Columns 2 (media, meta / content)', 'campo' ),
				'columns-3' => esc_html__( 'Columns 3 (media, content / meta)', 'campo' ),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_view' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_view' );

// PORTFOLIO SINGLE HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_pf_single_headline_size' ) ) {
	function boldthemes_customize_pf_single_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_single_headline_size', array(
			'label'     => esc_html__( 'Single project headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_headline_size]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_headline_size' );

// PORTFOLIO SINGLE SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_pf_single_show_superheadline' ) ) {
	function boldthemes_customize_pf_single_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_single_show_superheadline',
			array(
				'label'    	=> esc_html__( 'Show in superheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_single_section',
				'priority' 	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_single_show_superheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_show_superheadline' );

// PORTFOLIO SINGLE SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_pf_single_show_subheadline' ) ) {
	function boldthemes_customize_pf_single_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_single_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in  subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_single_section',
				'priority' 	=> 50,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_single_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_show_subheadline' );


// PORTFOLIo SINGLE SHOW AFTER CONTENT

if ( ! function_exists( 'boldthemes_customize_pf_single_show_bottom' ) ) {
	function boldthemes_customize_pf_single_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_single_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_pf_single_section',
				'priority' 	=> 60,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[pf_single_show_bottom]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_show_bottom' );


// PORTFOLIO SINGLE SHARE

if ( ! function_exists( 'boldthemes_customize_pf_single_share' ) ) {
	function boldthemes_customize_pf_single_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_single_share',
			array(
				'label'    => esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_pf_single_section',
				'priority' => 70,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[pf_single_share]',
				'choices'  => boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_share' );

// PORTFOLIO SINGLE SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_pf_single_share_size' ) ) {
	function boldthemes_customize_pf_single_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_single_share_size', array(
			'label'     => esc_html__( 'Share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_share_size]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_share_size' );

// PORTFOLIO SINGLE SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_pf_single_share_style' ) ) {
	function boldthemes_customize_pf_single_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_single_share_style', array(
			'label'     => esc_html__( 'Share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_share_style]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_share_style' );

// PORTFOLIO SINGLE SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_pf_single_share_shape' ) ) {
	function boldthemes_customize_pf_single_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_single_share_shape', array(
			'label'     => esc_html__( 'Share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_share_shape]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_share_shape' );

// PORTFOLIO SINGLE SHARE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_pf_single_share_color_scheme' ) ) {
	function boldthemes_customize_pf_single_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'pf_single_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_share_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_share_color_scheme' );

// PORTFOLIO SINGLE ABOUT AUTHOR STYLE

if ( ! function_exists( 'boldthemes_customize_pf_single_about_author_style' ) ) {
	function boldthemes_customize_pf_single_about_author_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_about_author_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_about_author_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'pf_single_about_author_style', array(
			'label'     => esc_html__( 'Author info style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_about_author_style]',
			'priority'  => 120,
			'type'      => 'select',
			'choices'   => boldthemes_get_about_author_styles()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_about_author_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_about_author_style' );

// PORTFOLIO SINGLE POST NAVIGATION
if ( ! function_exists( 'boldthemes_customize_pf_single_show_navigation' ) ) {
	function boldthemes_customize_pf_single_show_navigation( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[pf_single_show_navigation]', array(
			'default'           => BoldThemes_Customize_Default::$data['pf_single_show_navigation'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'pf_single_show_navigation',
			array(
				'label'     => esc_html__( 'Show in prevoius/next post navigation', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_pf_single_section',
				'priority'  => 130,
				'settings'  => BoldThemesFramework::$pfx . '_theme_options[pf_single_show_navigation]',
				'choices'   => boldthemes_get_single_navigation_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_pf_single_show_navigation' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_pf_single_show_navigation' );

/* SEARCH SECTION */

// SEARCH PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_search_settings_page_slug' ) ) {
	function boldthemes_customize_search_settings_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_settings_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_settings_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'search_settings_page_slug', array(
			'label'    => esc_html__( 'Search template page', 'campo' ),
			'description'    => esc_html__( 'Use static page as a template for search.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_search_list_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[search_settings_page_slug]',
			'priority' => 10,
			'type'      => 'select',
			'choices'   => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_settings_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_settings_page_slug' );

// SEARCH LIST HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_search_list_headline_size' ) ) {
	function boldthemes_customize_search_list_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_headline_size', array(
			'label'     => esc_html__( 'Search list headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_headline_size]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_headline_size' );

// SEARCH LIST SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_search_list_show_superheadline' ) ) {
	function boldthemes_customize_search_list_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'search_list_show_superheadline',
			array(
				'label'    => esc_html__( 'Show in search layout superheadline', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_search_list_section',
				'priority' => 30,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[search_list_show_superheadline]',
				'choices'  => boldthemes_get_meta_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_show_superheadline' );


// SEARCH LIST SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_search_list_show_subheadline' ) ) {
	function boldthemes_customize_search_list_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'search_list_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in search layout subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_search_list_section',
				'priority'	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[search_list_show_subheadline]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_show_subheadline' );

// SEARCH LIST SHOW EXCERPT

if ( ! function_exists( 'boldthemes_customize_search_list_show_excerpt' ) ) {
	function boldthemes_customize_search_list_show_excerpt( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_show_excerpt]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_show_excerpt'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'			=> 'postMessage',
		));
		$wp_customize->add_control( 'search_list_show_excerpt', array(
				'label'    	=> esc_html__( 'Show excerpt', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_search_list_section',
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[search_list_show_excerpt]',
				'priority' 	=> 50,
				'type'      => 'select',
				'choices'   => boldthemes_get_excerpt_options()
			)
		);
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_show_excerpt' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_show_excerpt' );

// SEARCH LIST SHOW AFTER CONTENT

if ( ! function_exists( 'boldthemes_customize_search_list_show_bottom' ) ) {
	function boldthemes_customize_search_list_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'search_list_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show in search layout after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_search_list_section',
				'priority' 	=> 60,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[search_list_show_bottom]',
				'choices'   => boldthemes_get_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_show_bottom' );

// SEARCH LIST SHARE

if ( ! function_exists( 'boldthemes_customize_search_list_share' ) ) {
	function boldthemes_customize_search_list_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'search_list_share',
			array(
				'label'    => esc_html__( 'Search list share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline or superheadline.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_search_list_section',
				'priority' => 70,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[search_list_share]',
				'choices'  => boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_share' );

// SEARCH LIST SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_search_list_share_size' ) ) {
	function boldthemes_customize_search_list_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_share_size', array(
			'label'     => esc_html__( 'Search list share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_share_size]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_share_size' );

// SEARCH LIST SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_search_list_share_style' ) ) {
	function boldthemes_customize_search_list_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_share_style', array(
			'label'     => esc_html__( 'Search list share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_share_style]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_share_style' );

// SEARCH LIST SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_search_list_share_shape' ) ) {
	function boldthemes_customize_search_list_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_share_shape', array(
			'label'     => esc_html__( 'Search list share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_share_shape]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_share_shape' );

// SEARCH LIST SHARE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_search_list_share_color_scheme' ) ) {
	function boldthemes_customize_search_list_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_share_color_scheme', array(
			'label'     => esc_html__( 'Search list share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_share_color_scheme]',
			'priority'  => 110,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_share_color_scheme' );


// SEARCH LIST READ MORE ICON

if ( ! function_exists( 'boldthemes_customize_search_list_read_more_icon' ) ) {
	function boldthemes_customize_search_list_read_more_icon ( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_icon]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_read_more_icon'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'boldthemes_sanitize_iconpicker_control'
		));
		$wp_customize->add_control( new BoldThemes_Customize_Iconpicker_Control(
			$wp_customize,
			'search_list_read_more_icon',
			array(
				'label'     => esc_html__( 'Read more button icon', 'campo' ),
				'section'   => BoldThemesFramework::$pfx . '_search_list_section',
				'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_icon]',
				'priority'  => 120,
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_read_more_icon' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_read_more_icon' );

// SEARCH LIST READ MORE SIZE

if ( ! function_exists( 'boldthemes_customize_search_list_read_more_size' ) ) {
	function boldthemes_customize_search_list_read_more_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_read_more_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_read_more_size', array(
			'label'     => esc_html__( 'Read more button size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_size]',
			'priority'  => 130,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_read_more_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_read_more_size' );

// SEARCH LIST READ MORE STYLE

if ( ! function_exists( 'boldthemes_customize_search_list_read_more_style' ) ) {
	function boldthemes_customize_search_list_read_more_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_read_more_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_read_more_style', array(
			'label'     => esc_html__( 'Read more button style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_style]',
			'priority'  => 140,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_read_more_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_read_more_style' );

// SEARCH LIST READ MORE SHAPE

if ( ! function_exists( 'boldthemes_customize_search_list_read_more_shape' ) ) {
	function boldthemes_customize_search_list_read_more_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_read_more_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_read_more_shape', array(
			'label'     => esc_html__( 'Read more button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_shape]',
			'priority'  => 150,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_read_more_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_read_more_shape' );

// SEARCH LIST READ MORE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_search_list_read_more_color_scheme' ) ) {
	function boldthemes_customize_search_list_read_more_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['search_list_read_more_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'search_list_read_more_color_scheme', array(
			'label'     => esc_html__( 'Read more button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_search_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[search_list_read_more_color_scheme]',
			'priority'  => 160,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_search_list_read_more_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_search_list_read_more_color_scheme' );

/* SHOP */

// SETTINGS PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_shop_settings_page_slug' ) ) {
	function boldthemes_customize_shop_settings_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_settings_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_settings_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'shop_settings_page_slug', array(
			'label'    => esc_html__( 'Single product settings page', 'campo' ),
			'description'    => esc_html__( 'Select a page with content which serves as a template for all single product pages.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_settings_page_slug]',
			'priority' => 10,
			'type'     => 'select',
			'choices'  => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_settings_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_settings_page_slug' );

// SHOP GENERAL

// SHOP SINGLE BUTTON STYLE

if ( ! function_exists( 'boldthemes_customize_shop_button_style' ) ) {
	function boldthemes_customize_shop_button_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_button_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_button_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_button_style', array(
			'label'     => esc_html__( 'Button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_general_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_button_style]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_button_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_button_style' );

// SHOP SINGLE BUTTON SHAPE

/*if ( ! function_exists( 'boldthemes_customize_shop_button_shape' ) ) {
	function boldthemes_customize_shop_button_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_button_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_button_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_button_shape', array(
			'label'     => esc_html__( 'Button shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_general_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_button_shape]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => boldthemes_get_button_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_button_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_button_shape' );*/

// SHOP SINGLE BUTTON COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_shop_button_color_scheme' ) ) {
	function boldthemes_customize_shop_button_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_button_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_button_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_button_color_scheme', array(
			'label'     => esc_html__( 'Button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_general_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_button_color_scheme]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_button_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_button_color_scheme' );

// SHOP SINGLE

// SHOP SINGLE HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_shop_single_headline_size' ) ) {
	function boldthemes_customize_shop_single_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_single_headline_size', array(
			'label'    => esc_html__( 'Headline title size', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_single_headline_size]',
			'priority' => 20,
			'type'     => 'select',
			'choices'  => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_headline_size' );

// SHOP SINGLE SHOW IN SUPERHEADLINE

if ( ! function_exists( 'boldthemes_customize_shop_single_show_superheadline' ) ) {
	function boldthemes_customize_shop_single_show_superheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_show_superheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_show_superheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_single_show_superheadline',
			array(
				'label'    	=> esc_html__( 'Show in superheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_shop_single_section',
				'priority' 	=> 30,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[shop_single_show_superheadline]',
				'choices'   => boldthemes_get_shop_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_show_superheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_show_superheadline' );

// SHOP SINGLE SHOW IN SUBHEADLINE

if ( ! function_exists( 'boldthemes_customize_shop_single_show_subheadline' ) ) {
	function boldthemes_customize_shop_single_show_subheadline( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_show_subheadline]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_show_subheadline'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_single_show_subheadline',
			array(
				'label'    	=> esc_html__( 'Show in subheadline', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_shop_single_section',
				'priority' 	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[shop_single_show_subheadline]',
				'choices'   => boldthemes_get_shop_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_show_subheadline' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_show_subheadline' );

// SHOP SINGLE SHOW BOTTOM

if ( ! function_exists( 'boldthemes_customize_shop_single_show_bottom' ) ) {
	function boldthemes_customize_shop_single_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_single_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after single product content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_shop_single_section',
				'priority' 	=> 50,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[shop_single_show_bottom]',
				'choices'   => boldthemes_get_shop_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_show_bottom' );

// SHOP SINGLE SHARE

if ( ! function_exists( 'boldthemes_customize_shop_single_share' ) ) {
	function boldthemes_customize_shop_single_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_single_share',
			array(
				'label'    => esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_shop_single_section',
				'priority' => 60,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_single_share]',
				'choices'  => boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_share' );

// SHOP SINGLE SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_shop_single_share_size' ) ) {
	function boldthemes_customize_shop_single_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_single_share_size', array(
			'label'     => esc_html__( 'Share  icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_single_share_size]',
			'priority'  => 70,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_share_size' );

// SHOP SINGLE SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_shop_single_share_style' ) ) {
	function boldthemes_customize_shop_single_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_single_share_style', array(
			'label'     => esc_html__( 'Share  icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_single_share_style]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_share_style' );

// SHOP SINGLE SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_shop_single_share_shape' ) ) {
	function boldthemes_customize_shop_single_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_single_share_shape', array(
			'label'     => esc_html__( 'Share  icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_single_share_shape]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_share_shape' );

// SHOP SINGLE SHARE COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_shop_single_share_color_scheme' ) ) {
	function boldthemes_customize_shop_single_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_single_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_single_share_color_scheme]',
			'priority'  => 100,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_share_color_scheme' );

// SHOP SINGLE RELATED PRODUCTS COLUMNS NUMBER

if ( ! function_exists( 'boldthemes_customize_shop_single_related_products_columns' ) ) {
	function boldthemes_customize_shop_single_related_products_columns( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_related_products_columns]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_related_products_columns'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'shop_single_related_products_columns', array(
			'label'    => esc_html__( 'Single product related products columns', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_single_related_products_columns]',
			'priority' => 110,
			'type'     => 'select',
			'choices'  => boldthemes_get_columns()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_related_products_columns' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_related_products_columns' );

// SHOP SINGLE RELATED PRODUCTS ROWS NUMBER

if ( ! function_exists( 'boldthemes_customize_shop_single_related_products_rows' ) ) {
	function boldthemes_customize_shop_single_related_products_rows( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_single_related_products_rows]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_single_related_products_rows'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'shop_single_related_products_rows', array(
			'label'    => esc_html__( 'Single product related products rows', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_single_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_single_related_products_rows]',
			'priority' => 120,
			'type'     => 'select',
			'choices'  => boldthemes_get_rows()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_single_related_products_rows' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_single_related_products_rows' );

// SHOP LIST

// SHOP LIST COLUMNS NUMBER FROM WOOCOMMERCE

if ( ! function_exists( 'boldthemes_customize_shop_list_loop_shop_columns' ) ) {
	function boldthemes_customize_shop_list_loop_shop_columns( $wp_customize ) {
		$woocommerce_catalog_columns_control = $wp_customize->get_control( 'woocommerce_catalog_columns' );		
		if ( $woocommerce_catalog_columns_control ) {
			$woocommerce_catalog_columns_control->section = BoldThemesFramework::$pfx . '_shop_list_section';
		}
	}
}
//add_action( 'customize_register', 'boldthemes_customize_shop_list_loop_shop_columns' );

// SHOP LIST ROWS NUMBER FROM WOOCOMMERCE

if ( ! function_exists( 'boldthemes_customize_shop_list_loop_shop_rows' ) ) {
	function boldthemes_customize_shop_list_loop_shop_rows( $wp_customize ) {
		$woocommerce_catalog_rows_control = $wp_customize->get_control( 'woocommerce_catalog_rows' );		
		if ( $woocommerce_catalog_rows_control ) {
			$woocommerce_catalog_rows_control->section = BoldThemesFramework::$pfx . '_shop_list_section';
		}
	}
}
//add_action( 'customize_register', 'boldthemes_customize_shop_list_loop_shop_rows' );


/* USE DEFAULT WOO SETTINGS */

// SHOP LIST COLUMN NUMBER

/*if ( ! function_exists( 'boldthemes_customize_shop_list_loop_shop_columns' ) ) {
	function boldthemes_customize_shop_list_loop_shop_columns( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_loop_shop_columns]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_loop_shop_columns'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'refresh',
		));
		$wp_customize->add_control( 'shop_list_loop_shop_columns', array(
			'label'    => esc_html__( 'Columns number', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_list_loop_shop_columns]',
			'priority' => 100,
			'type'     => 'select',
			'choices'  => boldthemes_get_columns()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_loop_shop_columns' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_loop_shop_columns' );*/

// SHOP LIST ROWS NUMBER

/*if ( ! function_exists( 'boldthemes_customize_shop_list_loop_shop_rows' ) ) {
	function boldthemes_customize_shop_list_loop_shop_rows( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_loop_shop_rows]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_loop_shop_rows'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'refresh',
		));
		$wp_customize->add_control( 'shop_list_loop_shop_rows', array(
			'label'    => esc_html__( 'Rows number', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_list_loop_shop_rows]',
			'priority' => 100,
			'type'     => 'select',
			'choices'  => boldthemes_get_rows()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_loop_shop_rows' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_loop_shop_rows' );*/

//SHOP LIST HEADLINE SIZE

if ( ! function_exists( 'boldthemes_customize_shop_list_headline_size' ) ) {
	function boldthemes_customize_shop_list_headline_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_headline_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_headline_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_list_headline_size', array(
			'label'     => esc_html__( 'Headline title size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_headline_size]',
			'priority'  => 30,
			'type'      => 'select',
			'choices'   => boldthemes_get_headline_sizes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_headline_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_headline_size' );


// SHOP LIST SHOW BOTTOM

if ( ! function_exists( 'boldthemes_customize_shop_list_show_bottom' ) ) {
	function boldthemes_customize_shop_list_show_bottom( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_show_bottom]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_show_bottom'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_list_show_bottom',
			array(
				'label'    	=> esc_html__( 'Show after content', 'campo' ),
				'section'  	=> BoldThemesFramework::$pfx . '_shop_list_section',
				'priority' 	=> 40,
				'settings' 	=> BoldThemesFramework::$pfx . '_theme_options[shop_list_show_bottom]',
				'choices'   => boldthemes_get_shop_meta_options()
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_show_bottom' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_show_bottom' );

// SHOP LIST BUTTON COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_shop_list_button_color_scheme' ) ) {
	function boldthemes_customize_shop_list_button_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_button_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_button_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage'
		));
		$wp_customize->add_control( 'shop_list_button_color_scheme', array(
			'label'     => esc_html__( 'Shop button color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_button_color_scheme]',
			'priority'  => 50,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_button_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_button_color_scheme' );

// SHOP LIST SHARE

if ( ! function_exists( 'boldthemes_customize_shop_list_share' ) ) {
	function boldthemes_customize_shop_list_share( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_share]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_share'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_multiple_select',
		));

		$wp_customize->add_control( new BoldThemes_Customize_Multiple_Select_Control(
			$wp_customize,
			'shop_list_share',
			array(
				'label'    => esc_html__( 'Share options', 'campo' ),
				'description'   => esc_html__( 'Make sure that share options are included in subheadline, superheadline or after post.', 'campo' ),
				'section'  => BoldThemesFramework::$pfx . '_shop_list_section',
				'priority' => 50,
				'settings' => BoldThemesFramework::$pfx . '_theme_options[shop_list_share]',
				'choices'  => boldthemes_get_share_options(),
			)
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_share' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_share' );

// SHOP LIST SHARE SIZE

if ( ! function_exists( 'boldthemes_customize_shop_list_share_size' ) ) {
	function boldthemes_customize_shop_list_share_size( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_share_size]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_share_size'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'shop_list_share_size', array(
			'label'     => esc_html__( 'Share icons size', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_share_size]',
			'priority'  => 60,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_sizes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_share_size' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_share_size' );

// SHOP LIST SHARE STYLE

if ( ! function_exists( 'boldthemes_customize_shop_list_share_style' ) ) {
	function boldthemes_customize_shop_list_share_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_share_style]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_share_style'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'shop_list_share_style', array(
			'label'     => esc_html__( 'Share icons style', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_share_style]',
			'priority'  => 70,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_styles(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_share_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_share_style' );

// SHOP LIST SHARE SHAPE

if ( ! function_exists( 'boldthemes_customize_shop_list_share_shape' ) ) {
	function boldthemes_customize_shop_list_share_shape( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_share_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_share_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'shop_list_share_shape', array(
			'label'     => esc_html__( 'Share icons shape', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_share_shape]',
			'priority'  => 80,
			'type'      => 'select',
			'choices'   => boldthemes_get_icon_shapes(),
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_share_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_share_shape' );

// SHOP LIST COLOR SCHEME

if ( ! function_exists( 'boldthemes_customize_shop_list_share_color_scheme' ) ) {
	function boldthemes_customize_shop_list_share_color_scheme( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[shop_list_share_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['shop_list_share_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'shop_list_share_color_scheme', array(
			'label'     => esc_html__( 'Share icons color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_shop_list_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[shop_list_share_color_scheme]',
			'priority'  => 90,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_shop_list_share_color_scheme' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_shop_list_share_color_scheme' );

// 404

// 404 PAGE SLUG

if ( ! function_exists( 'boldthemes_customize_error_404_page_slug' ) ) {
	function boldthemes_customize_error_404_page_slug( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[error_404_page_slug]', array(
			'default'           => BoldThemes_Customize_Default::$data['error_404_page_slug'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'error_404_page_slug', array(
			'label'    => esc_html__( 'Custom page 404', 'campo' ),
			'description'    => esc_html__( 'Use custom static page with content as a 404 page.', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_404_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[error_404_page_slug]',
			'priority' => 10,
			'type'      => 'select',
			'choices'   => boldthemes_get_all_pages()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_error_404_page_slug' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_error_404_page_slug' );

// 404 SINGLE COLOR SCHEME
if ( ! function_exists( 'boldthemes_customize_404_style' ) ) {
	function boldthemes_customize_404_style( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[error_404_color_scheme]', array(
			'default'           => BoldThemes_Customize_Default::$data['error_404_color_scheme'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select',
			'transport'         => 'postMessage',
		));
		$wp_customize->add_control( 'error_404_color_scheme', array(
			'label'     => esc_html__( 'Page 404 color scheme', 'campo' ),
			'section'   => BoldThemesFramework::$pfx . '_404_section',
			'settings'  => BoldThemesFramework::$pfx . '_theme_options[error_404_color_scheme]',
			'priority'  => 20,
			'type'      => 'select',
			'choices'   => boldthemes_get_color_schemes()
		));
	}
}
add_action( 'customize_register', 'boldthemes_customize_404_style' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_404_style' );

// 404 CUSTOM BACKGROUND COLOR
if ( ! function_exists( 'boldthemes_customize_404_background_color' ) ) {
	function boldthemes_customize_404_background_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[error_404_background_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['error_404_background_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'error_404_background_color', array(
			'label'    => esc_html__( 'Page 404 background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_404_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[error_404_background_color]',
			'priority' => 30,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_404_background_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_404_background_color' );

// 404 CUSTOM TEXT COLOR
if ( ! function_exists( 'boldthemes_customize_404_color' ) ) {
	function boldthemes_customize_404_color( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[error_404_color]', array(
			'default'        	    => BoldThemes_Customize_Default::$data['error_404_color'],
			'type'           	    => 'option',
			'capability'     	    => 'edit_theme_options',
			'sanitize_callback'     => 'sanitize_hex_color',
			'transport'				=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'error_404_color', array(
			'label'    => esc_html__( 'Page 404 text color', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_404_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[error_404_color]',
			'priority' => 40,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_404_color' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_404_color' );

// 404 BACKGROUND IMAGE
if ( ! function_exists( 'boldthemes_customize_404_background_image' ) ) {
	function boldthemes_customize_404_background_image( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[error_404_background_image]', array(
			'default'           => BoldThemes_Customize_Default::$data['error_404_background_image'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
			'transport'			=> 'postMessage'
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'error_404_background_image', array(
			'label'    => esc_html__( 'Page 404 background image', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_404_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[error_404_background_image]',
			'priority' => 50,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_404_background_image' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_404_background_image' );

/**
 * Body classes from customize options
 */
if ( ! function_exists( 'boldthemes_body_class_customize' ) ) {
	function boldthemes_body_class_customize() {
		BoldThemesFramework::$customize_body_class = array_merge( BoldThemesFramework::$customize_body_class, 
			array(
				'display_branding_text',
				'branding_text_html_tag',
				'template_color_scheme',
				'sidebar_position',
				'sidebar_sticky',
				'content_width',
				'header_width',
				'footer_width',
				'enable_sticky',
				'sticky_style',
				'sticky_header_width',
				'primary_menu_position',
				'primary_menu_reverse_menu_levels',
				'responsive_logo_position',
				'responsive_trigger_position',
				'enable_preloader',
				'preloader_color_scheme',
				'preloader_transition',
				'preloader_animation',
				'header_position',
				'default_headline_height',
				'default_headline_color_scheme',
				'default_headline_size',
				'default_headline_h_tag',
				'default_headline_width',
				'default_headline_parallax',
				'default_headline_overlay',
				'default_headline_alignment',
				'button_shape',
				'button_style',
				'button_color_scheme',
				'top_bar_color_scheme',
				'site_branding_bar_color_scheme',
				'sticky_site_branding_bar_color_scheme',
				'menu_bar_color_scheme',
				'sticky_menu_bar_color_scheme',
				'menu_hover_color_scheme',
				'menu_dropdown_color_scheme',
				'menu_dropdown_hover_color_scheme',
				'responsive_sticky_header_color_scheme',
				'responsive_header_color_scheme',
				'responsive_menu_color_scheme',
				'responsive_menu_position',
				'blog_list_view',
				'blog_list_headline_size',
				'blog_list_show_excerpt',
				'blog_single_view',
				'blog_single_about_author_style',
				'blog_single_headline_size',
				'pf_list_view',
				'pf_list_headline_size',
				'pf_list_show_excerpt',
				'pf_list_load_animation',
				'pf_single_view',
				'pf_single_about_author_style',	
				'pf_single_headline_size',
				'search_list_headline_size',
				'search_list_show_excerpt',
				'error_404_color_scheme',
				'footer_widgets_color_scheme',
				'blog_list_load_animation',
				'shop_button_style',
				// 'shop_button_shape',
				'shop_button_color_scheme',
				'shop_list_button_color_scheme',
				'shop_list_button_color_scheme',
				'shop_list_headline_size',
				'shop_single_headline_size',
				'sidebar_headline_size'
			)
		);
	}
}
add_action( 'get_header', 'boldthemes_body_class_customize', 0 );

/**
 * Body data attr from customize options
 */
if ( ! function_exists( 'boldthemes_body_data_customize' ) ) {
	function boldthemes_body_data_customize() {
		BoldThemesFramework::$customize_body_data = array_merge( BoldThemesFramework::$customize_body_data,
			array(
				'header_responsive_breakpoint',
				'sidebar_responsive_breakpoint',
				'sticky_header_scroll_breakpoint',
				'copy_to_clipboard_ok',
				'copy_to_clipboard_notok',
			)
		);
	}
}
add_action( 'get_header', 'boldthemes_body_data_customize', 0 );

/**
 * Body css variables from customize options
 */
if ( ! function_exists( 'boldthemes_css_vars_customize' ) ) {
	function boldthemes_css_vars_customize() {
		BoldThemesFramework::$customize_css_vars = array_merge( BoldThemesFramework::$customize_css_vars,
			array(
				'accent_color',
				'alternate_color',
				'logo_height',
				'sticky_logo_height',
				'responsive_logo_height',
				'responsive_sticky_logo_height',
				'responsive_menu_logo_height',
				'sidebar_width',
				'sidebar_sticky_top_position',
				'page_background_color',
				'page_color',
				'page_background_image',
				'error_404_background_image',
				'site_branding_bar_background_color',
				'site_branding_bar_color',
				'sticky_site_branding_bar_background_color',
				'sticky_site_branding_bar_color',
				'menu_bar_background_color',
				'menu_bar_color',
				'sticky_menu_bar_background_color',
				'sticky_menu_bar_color',
				'responsive_header_background_color',
				'responsive_sticky_header_background_color',
				'responsive_header_color',
				'responsive_sticky_header_color',
				'responsive_menu_background_color',
				'responsive_menu_color',
				'top_bar_background_color',
				'top_bar_color',
				'default_headline_background_color',
				'default_headline_color',
				'responsive_menu_max_width',
				'body_font',
				'body_font_weight',
				'body_text_transform',
				'body_font_style',
				'heading_font',
				'heading_font_weight',
				'heading_text_transform',
				'heading_font_style',
				'heading_letter_spacing',
				'supertitle_font',
				'supertitle_font_weight',
				'supertitle_text_transform',
				'supertitle_font_style',
				'supertitle_letter_spacing',
				'subtitle_font',
				'subtitle_font_weight',
				'subtitle_text_transform',
				'subtitle_font_style',
				'subtitle_letter_spacing',
				'menu_font',
				'menu_first_level_font_weight',
				'menu_first_level_text_transform',
				'menu_other_levels_font_weight',
				'menu_other_levels_text_transform',
				'button_font',
				'button_font_weight',
				'button_text_transform',
				'button_font_style',
				'button_letter_spacing',
				'error_404_background_color',
				'error_404_color',
				'footer_widgets_color',				
				'footer_widgets_background_color',
				'preloader_background_color',
				'preloader_color',
			)
		);

		BoldThemesFramework::$css_vars_background_image = array_merge( BoldThemesFramework::$css_vars_background_image,
			array(
				'page_background_image',
				'error_404_background_image',
			)
		);
	}
}
add_action( 'get_header', 'boldthemes_css_vars_customize', 0 );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function boldthemes_customize_register1( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
}
add_action( 'customize_register', 'boldthemes_customize_register1' );

/**
 * Enqueue script for live preview JS
 */
function boldthemes_customizer_live_preview() {
	wp_enqueue_script(
		'boldthemes-themecustomizer',
		get_template_directory_uri() . '/framework/customizer/front-end.js',
		array( 'jquery','customize-preview' ),
		'',
		true
	);
}
add_action( 'customize_preview_init', 'boldthemes_customizer_live_preview' );

/**
 * Enqueue script for custom customize control.
 */
function boldthemes_custom_customize_enqueue() {
    wp_enqueue_script( 'bt-custom-customize', get_template_directory_uri() . '/framework/customizer/back-end.js', array( 'jquery', 'customize-controls' ), false, true );
}
add_action( 'customize_controls_enqueue_scripts', 'boldthemes_custom_customize_enqueue' );
