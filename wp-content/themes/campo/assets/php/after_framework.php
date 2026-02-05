<?php

BoldThemes_Customize_Default::$data['crest_logo'] 							= '';
BoldThemes_Customize_Default::$data['logo_height'] 							= '80px';
BoldThemes_Customize_Default::$data['sticky_logo_height'] 					= '70px';
BoldThemes_Customize_Default::$data['responsive_logo_height'] 				= '80px';

/* Colors */
BoldThemes_Customize_Default::$data['accent_color']							= '#fc3a45';

/* Typography */
BoldThemes_Customize_Default::$data['body_font'] 							= 'Exo';
BoldThemes_Customize_Default::$data['heading_font'] 						= 'Antonio';
BoldThemes_Customize_Default::$data['heading_font_weight'] 					= 'bold';
BoldThemes_Customize_Default::$data['heading_text_transform'] 				= 'uppercase';
BoldThemes_Customize_Default::$data['supertitle_font'] 						= 'Antonio';
BoldThemes_Customize_Default::$data['supertitle_font_weight'] 				= '600';
BoldThemes_Customize_Default::$data['subtitle_font'] 						= 'Exo';
BoldThemes_Customize_Default::$data['subtitle_font_weight'] 				= '500';
BoldThemes_Customize_Default::$data['supertitle_text_transform'] 			= 'uppercase';
BoldThemes_Customize_Default::$data['menu_font'] 							= 'Exo';
BoldThemes_Customize_Default::$data['menu_first_level_text_transform'] 		= 'uppercase';
BoldThemes_Customize_Default::$data['menu_other_levels_text_transform'] 	= 'uppercase';
BoldThemes_Customize_Default::$data['menu_first_level_font_weight']			= '500';
BoldThemes_Customize_Default::$data['menu_other_levels_font_weight']		= '500';
BoldThemes_Customize_Default::$data['button_font'] 							= 'Exo';
BoldThemes_Customize_Default::$data['button_shape'] 						= 'rounded';
BoldThemes_Customize_Default::$data['button_font_weight'] 					= '500';
BoldThemes_Customize_Default::$data['button_text_transform'] 				= 'uppercase';

/* Blog */
BoldThemes_Customize_Default::$data['blog_list_headline_size'] 				= 'large';
BoldThemes_Customize_Default::$data['blog_list_read_more_icon'] 			= 'remixiconssystem_e933';
BoldThemes_Customize_Default::$data['blog_list_read_more_shape'] 			= 'inherit';
BoldThemes_Customize_Default::$data['blog_list_read_more_color_scheme'] 	= '3';
BoldThemes_Customize_Default::$data['blog_list_read_more_size'] 			= 'medium';
BoldThemes_Customize_Default::$data['blog_single_show_superheadline'] 		= array( 'date', 'author', 'categories' );
BoldThemes_Customize_Default::$data['blog_single_show_bottom'] 				= array( 'tags' );


/* Shop */
BoldThemes_Customize_Default::$data['shop_list_headline_size'] 				= 'medium';

/* Template */
BoldThemes_Customize_Default::$data['content_width']						= 'boxed-1400';

/* Header */
BoldThemes_Customize_Default::$data['header_position']						= 'top';
BoldThemes_Customize_Default::$data['header_width']							= 'wide-wide';
BoldThemes_Customize_Default::$data['sticky_header_width']					= 'wide-wide';
BoldThemes_Customize_Default::$data['primary_menu_position']				= 'logo-right';
BoldThemes_Customize_Default::$data['footer_width']							= 'wide-boxed-1400';
BoldThemes_Customize_Default::$data['default_headline_width']				= 'wide-boxed-1400';
BoldThemes_Customize_Default::$data['default_headline_height']				= 'thick';
BoldThemes_Customize_Default::$data['default_headline_color_scheme']		= '9';
BoldThemes_Customize_Default::$data['default_headline_size']				= 'large';

/* 404 page */
BoldThemes_Customize_Default::$data['error_404_color_scheme']				= '';

require_once( get_template_directory() . '/assets/php/after_framework/functions.php' );
require_once( get_template_directory() . '/assets/php/after_framework/customize_params.php' );