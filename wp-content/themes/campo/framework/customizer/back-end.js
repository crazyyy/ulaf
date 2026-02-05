( function( api ) {
	'use strict';
	
	var dependent_controls_array = {
		'default_headline_height' : [ 
			'default_headline_parallax', 
			'default_headline_overlay', 
			'default_headline_alignment',
			'default_headline_size',
			'default_headline_h_tag',
			'default_headline_width',
			'separator_default_headline_color_scheme',
			'default_headline_color_scheme',
			'default_headline_background_color',
			'default_headline_color',
			'page_single_show_superheadline',
			'page_single_show_subheadline',
			'page_single_share',
			'page_single_share_size',
			'page_single_share_style',
			'page_single_share_shape',
			'page_single_share_color_scheme'
		],
		'enable_preloader' : [ 
			'preloader_color_scheme',
			'preloader_background_color',
			'preloader_color',
			'preloader_transition',
			'preloader_animation',
			'preloader_logo',
			'preloader_text',
		],
		'enable_sticky' : [ 
			'sticky_style',
			'sticky_header_scroll_breakpoint',
			'sticky_logo',
			'sticky_header_width',
			'sticky_logo_height',
			'boldthemes_theme_separator_sticky_colors',
			'sticky_site_branding_bar_color_scheme',
			'sticky_site_branding_bar_background_color',
			'sticky_site_branding_bar_color',
			'sticky_menu_bar_color_scheme',
			'sticky_menu_bar_background_color',
			'sticky_menu_bar_color',
			'sticky_site_branding_bar_color',
		],
		'error_404_page_slug' : [
			'error_404_background_image',
			'error_404_color',
			'error_404_background_color',
			'error_404_color_scheme',
		]
	};
	
	if ( window.bt_framework_dependent_controls_array_filter ) {
		dependent_controls_array = window.bt_framework_dependent_controls_array_filter( dependent_controls_array );
	}

	api.bind( 'ready', function() {
		
		/* Show / hide default headline options */
			
		api( 'boldthemes_theme_theme_options[default_headline_height]', function( setting ) {
			var linkSettingValueToControlActiveState;
			linkSettingValueToControlActiveState = function( control ) {

				var visibility = function() {
					switch ( setting.get() ) {
						case 'none':
							control.container.addClass( 'customize-control-disabled' );
							break;
						default:
							control.container.removeClass( 'customize-control-disabled' );
							break;
					}
				};
				// Set initial active state.
				visibility();
				// Update activate state whenever the setting is changed.
				setting.bind( visibility );
			};
			// Call linkSettingValueToControlActiveState on the list of controls when they exist.
			for ( var i = 0; i < dependent_controls_array[ 'default_headline_height' ].length; i++ ) {
				api.control( dependent_controls_array[ 'default_headline_height' ][i], linkSettingValueToControlActiveState );
			}
		});
		
		/* Show / hide preloader options */
		
		api( 'boldthemes_theme_theme_options[enable_preloader]', function( setting ) {
			var linkSettingValueToControlActiveState;
			linkSettingValueToControlActiveState = function( control ) {

				var visibility = function() {
					switch ( setting.get() ) {
						case false:
						case '0':
							control.container.addClass( 'customize-control-disabled' );
							break;
						default:
							control.container.removeClass( 'customize-control-disabled' );
							break;
					}
				};
				// Set initial active state.
				visibility();
				// Update activate state whenever the setting is changed.
				setting.bind( visibility );
			};
			// Call linkSettingValueToControlActiveState on the list of controls when they exist.
			for ( var i = 0; i < dependent_controls_array[ 'enable_preloader' ].length; i++ ) {
				api.control( dependent_controls_array[ 'enable_preloader' ][i], linkSettingValueToControlActiveState );
			}
		});
		
		/* Show / hide sticky options */
		
		api( 'boldthemes_theme_theme_options[enable_sticky]', function( setting ) {
			var linkSettingValueToControlActiveState;
			linkSettingValueToControlActiveState = function( control ) {

				var visibility = function() {
					switch ( setting.get() ) {
						case false:
						case '0':
							control.container.addClass( 'customize-control-disabled' );
							break;
						default:
							control.container.removeClass( 'customize-control-disabled' );
							break;
					}
				};
				// Set initial active state.
				visibility();
				// Update activate state whenever the setting is changed.
				setting.bind( visibility );
			};
			// Call linkSettingValueToControlActiveState on the list of controls when they exist.
			for ( var i = 0; i < dependent_controls_array[ 'enable_sticky' ].length; i++ ) {
				api.control( dependent_controls_array[ 'enable_sticky' ][i], linkSettingValueToControlActiveState );
			}

		});
		
		/* Show / hide 404 options */
		
		api( 'boldthemes_theme_theme_options[error_404_page_slug]', function( setting ) {
			var linkSettingValueToControlActiveState;
			linkSettingValueToControlActiveState = function( control ) {

				var visibility = function() {
					switch ( setting.get() ) {
						case '':
							control.container.removeClass( 'customize-control-disabled' );
							break;
						default:
							control.container.addClass( 'customize-control-disabled' );
							break;
					}
				};
				// Set initial active state.
				visibility();
				// Update activate state whenever the setting is changed.
				setting.bind( visibility );
			};
			// Call linkSettingValueToControlActiveState on the list of controls when they exist.
			for ( var i = 0; i < dependent_controls_array[ 'error_404_page_slug' ].length; i++ ) {
				api.control( dependent_controls_array[ 'error_404_page_slug' ][i], linkSettingValueToControlActiveState );
			}
		});

	});

}( wp.customize ) );