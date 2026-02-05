// https://codex.wordpress.org/Theme_Customization_API#Step_2:_Create_a_JavaScript_File


/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and 
 * then make any necessary changes to the page using jQuery.
 */
( function( $ ) {
	
	/* TODO: treba dodati proveru za override na strani */
	
	/* Helpers */
	
	$.fn.removeClassStartingWith = function ( filter ) {
		$( this ).removeClass( function ( index, className ) {
			return ( className.match(new RegExp("\\S*" + filter + "\\S*", 'g')) || []).join(' ');
			// return ( className.match(new RegExp("\\S*" + filter + "-[^- ]*", 'g')) || []).join(' ');
		});
		return this;
	};
	
	//function $( 'body' ).setCSSVar( css_var, newval ) {
	$.fn.setCSSVar = function ( css_var, newval ) {
		if ( newval != '' ) {
			$( this ).css( css_var, newval );
		} else {	
			// document.getElementsByTagName('body')[0].style.removeProperty( css_var );					
			// console.log( $( this ) );
			// $( this ).style.removeProperty( css_var );
			$( this ).css( css_var, newval );	// documentation for css() says that setting the style property to the empty string will remove that property				
		}		
	}
	
	function addStylesheetURL( url ) {
		var link = document.createElement( 'link' );
		link.rel = 'stylesheet';
		link.href = url;
		document.getElementsByTagName('head')[0].appendChild( link );
	}
	
	function replaceImage( src, id, container ) {
		if ( $( container + ' > img' ).length ) $( container + ' > img' ).remove();
		if ( src != '' ) $( container ).prepend( $( '<img>', { id: id + '-img', src: src } ) );
		$( 'body' ).attr( 'data-' + id , src );
	}
	
	function getPrimaryColor( index ) {
		var color_scheme = boldthemes_color_schemes[ index - 1 ].split( ';' );
		return color_scheme[ color_scheme.length - 2 ];
	}
	
	function getSecondaryColor( index ) {
		var color_scheme = boldthemes_color_schemes[ index - 1 ].split( ';' );
		return color_scheme[ color_scheme.length - 1 ];
	}

	/* TEXTS 
	------------------------------------------------------ */
	
	wp.customize( 'blogname', function( value ) {
		value.bind( function( newval ) {
			$( '.site-title a' ).text( newval );
		} );
	} );
	
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( newval ) {
			$( '.site-description' ).text( newval );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[preloader_text]', function( value ) {
		value.bind( function( newval ) {
			$( '#preloader .content p' ).text( newval );
		} );
	} );
	
	/* IMAGES 
	------------------------------------------------------ */
	
	wp.customize( 'boldthemes_theme_theme_options[logo]', function( value ) {
		value.bind( function( newval ) {
			replaceImage( newval, 'logo', '.site-branding-logo .logo' );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[sticky_logo]', function( value ) {
		value.bind( function( newval ) {
			replaceImage( newval, 'sticky-logo', '.site-branding-logo .sticky-logo' );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[responsive_logo]', function( value ) {
		value.bind( function( newval ) {
			replaceImage( newval, 'responsive-logo', '.site-header-responsive-logo .responsive-logo' );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[responsive_sticky_logo]', function( value ) {
		value.bind( function( newval ) {
			replaceImage( newval, 'responsive-sticky-logo', '.site-header-responsive-logo .responsive-sticky-logo' );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_logo]', function( value ) {
		value.bind( function( newval ) {
			replaceImage( newval, 'responsive-menu-logo', '.site-branding-logo .responsive-menu-logo' );
		} );
	} );

	/* CSS VARS 
	------------------------------------------------------ */
	
	wp.customize( 'boldthemes_theme_theme_options[accent_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--accent-color', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[alternate_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--alternate-color', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[logo_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--logo-height', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_logo_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sticky-logo-height', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_logo_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-logo-height', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_logo_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-menu-logo-height', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[sidebar_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sidebar-width', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_max_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-menu-max-width', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[body_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'body_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--body-font', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[heading_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'heading_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--heading-font', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[supertitle_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'supertitle_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--supertitle-font', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[subtitle_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'subtitle_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--subtitle-font', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'menu_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--menu-font', newval ); 
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[button_font]', function( value ) {
		value.bind( function( newval ) {
			if ( newval == 'default' ) {
				newval = window.boldthemes_customize_default[ 'button_font' ];
			}
			addStylesheetURL('https://fonts.googleapis.com/css2?family=' + newval + '&display=swap');
			$( 'body' ).setCSSVar( '--button-font', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[body_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--body-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[body_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--body-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[body_font_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--body-font-style', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[heading_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--heading-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[heading_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--heading-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[heading_font_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--heading-font-style', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[supertitle_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--supertitle-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[supertitle_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--supertitle-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[supertitle_font_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--supertitle-font-style', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[subtitle_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--subtitle-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[subtitle_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--subtitle-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[subtitle_font_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--subtitle-font-style', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[button_font_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--button-font-style', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[button_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--button-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[button_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--button-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_first_level_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-first-level-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_other_levels_text_transform]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-other-levels-text-transform', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_first_level_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-first-level-font-weight', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_other_levels_font_weight]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-other-levels-font-weight', newval );
		} );
	} );
	
	/* BODY CSS CLASS 
	------------------------------------------------------ */
	
	wp.customize( 'boldthemes_theme_theme_options[display_branding_text]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'display-branding-text' ).addClass( 'display-branding-text-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[branding_text_html_tag]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'branding-text-html-tag' ).addClass( 'branding-text-html-tag-' + newval );
			$( '.site-branding-text .site-title' ).replaceWith( '<' + newval + ' class="site-title"/>' + $( '.site-title' ).html() + '</' + newval + '>' );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sidebar_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'sidebar-position' ).addClass( 'sidebar-position-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[content_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'content-width' ).addClass( 'content-width-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[header_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'header-width' ).addClass( 'header-width-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[footer_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'footer-width' ).addClass( 'footer-width-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_header_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'sticky-header-width' ).addClass( 'sticky-header-width-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[primary_menu_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'primary-menu-position' ).addClass( 'primary-menu-position-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[primary_menu_reverse_menu_levels]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'primary-menu-reverse-menu-levels' ).addClass( 'primary-menu-reverse-menu-levels-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_logo_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'responsive-logo-position' ).addClass( 'responsive-logo-position-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_trigger_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'responsive-trigger-position' ).addClass( 'responsive-trigger-position-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[preloader_transition]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'preloader-transition' ).addClass( 'preloader-transition-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[preloader_animation]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'preloader-animation' ).addClass( 'preloader-animation-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[header_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'header-position' ).addClass( 'header-position-' + newval );
			/* Fix content padding */
			if ( $( 'body' ).hasClass( 'header-position-top' ) ) {
				$( '#page' ).css( 'padding-top',  $( 'body' ).hasClass( 'bt-header-responsive-active' ) ? $( '#masthead-responsive' ).height() : $( '#masthead' ).height() + 'px');
				$( '.page-header .page-header-inner' ).css( 'padding-top',  '0px');
			} else {
				$( '#page' ).css( 'padding-top',  '0px');
				$( '.page-header .page-header-inner' ).css( 'padding-top',  $( 'body' ).hasClass( 'bt-header-responsive-active' ) ? $( '#masthead-responsive' ).height() : $( '#masthead' ).height() + 'px');
			}
			//window.boldthemes_responsive_checker(); // ako moze samo ova f-ja da se pozove, to bi resilo sve
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-height' ).addClass( 'default-headline-height-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-size' ).addClass( 'default-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_h_tag]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-h-tag' ).addClass( 'default-headline-h-tag-' + newval );
			$( '.page-title' ).replaceWith( '<' + newval + ' class="page-title"/>' + $( '.page-title' ).html() + '</' + newval + '>' );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-width' ).addClass( 'default-headline-width-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_parallax]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-parallax' ).addClass( 'default-headline-parallax-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_overlay]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-overlay' ).addClass( 'default-headline-overlay-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_alignment]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'default-headline-alignment' ).addClass( 'default-headline-alignment-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[button_shape]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'button-shape' ).addClass( 'button-shape-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_position]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'responsive-menu-position' ).addClass( 'responsive-menu-position-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_view]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-list-view' ).addClass( 'blog-list-view-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_show_excerpt]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-list-show-excerpt' ).addClass( 'blog-list-show-excerpt-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_view]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-single-view' ).addClass( 'blog-single-view-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_about_author_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-single-about-author-style' ).addClass( 'blog-single-about-author-style-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-single-headline-size' ).addClass( 'blog-single-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-list-headline-size' ).addClass( 'blog-list-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_view]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-list-view' ).addClass( 'pf-list-view-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-list-headline-size' ).addClass( 'pf-list-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_view]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-single-view' ).addClass( 'pf-single-view-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_show_excerpt]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-list-show-excerpt' ).addClass( 'pf-list-show-excerpt-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_about_author_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-single-about-author-style' ).addClass( 'pf-single-about-author-style-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-single-headline-size' ).addClass( 'pf-single-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'search-list-headline-size' ).addClass( 'search-list-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_show_excerpt]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'search-list-show-excerpt' ).addClass( 'search-list-show-excerpt-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[error_404_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'error-404-style' ).addClass( 'error-404-style-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_grid_gap]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-single-grid-gap' ).addClass( 'blog-single-grid-gap-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_grid_gap]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-single-grid-gap' ).addClass( 'pf-single-grid-gap-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_list_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'shop-list-headline-size' ).addClass( 'shop-list-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_single_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'shop-single-headline-size' ).addClass( 'shop-single-headline-size-' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sidebar_headline_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'sidebar-headline-size' ).addClass( 'sidebar-headline-size-' + newval );
		} );
	} );
	
	/* Animationis / refresh */
	
	/*wp.customize( 'boldthemes_theme_theme_options[blog_list_load_animation]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'blog-list-load-animation' ).addClass( 'blog-list-load-animation-' + newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[pf_list_load_animation]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).removeClassStartingWith( 'pf-list-load-animation' ).addClass( 'pf-list-load-animation-' + newval );
		} );
	} );*/	
	
	/* BODY DATA 
	------------------------------------------------------ */
	
	wp.customize( 'boldthemes_theme_theme_options[logo_height]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).attr( "data-logo-height" , newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[header_responsive_breakpoint]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).attr( "data-header-responsive-breakpoint" , newval );
		} );
	} );
	
	wp.customize( 'boldthemes_theme_theme_options[sidebar_responsive_breakpoint]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).attr( "data-sidebar-responsive-breakpoint" , newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_header_scroll_breakpoint]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).attr( "data-sticky-header-scroll-breakpoint" , newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_max_width]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).attr( "data-responsive-menu-max-width" , newval );
		} );
	} );
	
	/* ICONS 
	------------------------------------------------------ */
	
	/* Page single icons */
	
	wp.customize( 'boldthemes_theme_theme_options[page_single_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.page-header .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[page_single_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.page-header .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[page_single_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.page-header .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Blog list icons */
	
	wp.customize( 'boldthemes_theme_theme_options[blog_list_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Blog single icons */
	
	wp.customize( 'boldthemes_theme_theme_options[blog_single_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-post .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-post .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-post .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Portfolio list icons */
	
	wp.customize( 'boldthemes_theme_theme_options[pf_list_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Portfolio single icons */
	
	wp.customize( 'boldthemes_theme_theme_options[pf_single_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Search list icons */
	
	wp.customize( 'boldthemes_theme_theme_options[search_list_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'article.search .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( 'article.search .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( 'article.search .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Shop list icons */
	
	wp.customize( 'boldthemes_theme_theme_options[shop_list_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_list_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_list_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* Shop single icons */
	
	wp.customize( 'boldthemes_theme_theme_options[shop_single_share_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_single_share_style]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_single_share_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	
	/* BUTTONS  
	------------------------------------------------------ */
	
	/* Blog list buttons */
	
	wp.customize( 'boldthemes_theme_theme_options[blog_list_read_more_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_read_more_style]', function( value ) {
		value.bind( function( newval ) {
			newval == 'none' ? $( '.blog .bt_bb_button, .archive:not(.post-type-archive-portfolio) .bt_bb_button' ).hide() : $( '.blog .bt_bb_button, .archive:not(.post-type-archive-portfolio) .bt_bb_button' ).show();
			$( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[blog_list_read_more_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[blog_list_read_more_icon]', function( value ) {
		value.bind( function( newval ) { 
			icoData = newval.split( '_' );
			$( '.blog .read-more .bt_bb_button .bt_bb_icon_holder, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button .bt_bb_icon_holder' ).after( '<span data-ico-' + icoData[0] + '="&#x' + icoData[1] + '" class="bt_bb_icon_holder"></span>' ).remove();
		} );
	} );
	
	/* Portfolio list buttons */
	
	wp.customize( 'boldthemes_theme_theme_options[pf_list_read_more_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-portfolio .bt_bb_button ' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_read_more_style]', function( value ) {
		value.bind( function( newval ) {
			newval == 'none' ? $( '.post-type-archive-portfolio .bt_bb_button' ).hide() : $( '.post-type-archive-portfolio .bt_bb_button' ).show();
			$( '.post-type-archive-portfolio .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_read_more_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.post-type-archive-portfolio .bt_bb_button' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_read_more_icon]', function( value ) {
		value.bind( function( newval ) {
			icoData = newval.split( '_' );
			$( '.post-type-archive-portfolio .read-more .bt_bb_button .bt_bb_icon_holder' ).after( '<span data-ico-' + icoData[0] + '="&#x' + icoData[1] + '" class="bt_bb_icon_holder"></span>' ).remove();
		} );
	} );
	
	/* Search list buttons */
	
	wp.customize( 'boldthemes_theme_theme_options[search_list_read_more_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.search .bt_bb_button' ).removeClassStartingWith( 'bt_bb_size_' ).addClass( 'bt_bb_size_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_read_more_style]', function( value ) {
		value.bind( function( newval ) {
			newval == 'none' ? $( '.search .bt_bb_button' ).hide() : $( '.search .bt_bb_button' ).show();
			$( '.search .bt_bb_button' ).removeClassStartingWith( 'bt_bb_style_' ).addClass( 'bt_bb_style_' + newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[search_list_read_more_shape]', function( value ) {
		value.bind( function( newval ) {
			$( '.search .bt_bb_button' ).removeClassStartingWith( 'bt_bb_shape_' ).addClass( 'bt_bb_shape_' + newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_read_more_icon]', function( value ) {
		value.bind( function( newval ) {
			icoData = newval.split( '_' );
			$( '.search .read-more .bt_bb_button .bt_bb_icon_holder' ).after( '<span data-ico-' + icoData[0] + '="&#x' + icoData[1] + '" class="bt_bb_icon_holder"></span>' ).remove();
		} );
	} );
	
	
	/* Template */
		
	wp.customize( 'boldthemes_theme_theme_options[template_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--page-primary-color', getPrimaryColor( newval ) );
			$( 'body' ).setCSSVar( '--page-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[page_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--page-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[page_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--page-color', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[page_background_image]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--page-background-image', 'url(\'' + newval + '\')' );
		} );
	} );
	
	/* Logo bar */
	
	wp.customize( 'boldthemes_theme_theme_options[site_branding_bar_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-branding' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.site-branding' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[site_branding_bar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--site-branding-bar-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[site_branding_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--site-branding-bar-color', newval );
		} );
	} );	
	
	/* Logo bar, szicky */
	
	wp.customize( 'boldthemes_theme_theme_options[sticky_site_branding_bar_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-branding' ).setCSSVar( '--sticky-primary-color', getPrimaryColor( newval ) );
			$( '.site-branding' ).setCSSVar( '--sticky-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_site_branding_bar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sticky-site-branding-bar-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_site_branding_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sticky-site-branding-bar-color', newval );
		} );
	} );
	
	/* Top bar */
	
	wp.customize( 'boldthemes_theme_theme_options[top_bar_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-header-top-bar' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.site-header-top-bar' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[top_bar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--top-bar-background-color', newval );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[top_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--top-bar-color', newval );
		} );
	} );
	
	/* Menu bar */
	
	wp.customize( 'boldthemes_theme_theme_options[menu_bar_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.main-navigation' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.main-navigation' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_bar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-bar-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--menu-bar-color', newval )
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_hover_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.main-navigation' ).setCSSVar( '--hover-primary-color', getPrimaryColor( newval ) );
			$( '.main-navigation' ).setCSSVar( '--hover-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_dropdown_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.main-navigation' ).setCSSVar( '--dropdown-primary-color', getPrimaryColor( newval ) );
			$( '.main-navigation' ).setCSSVar( '--dropdown-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[menu_dropdown_hover_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.main-navigation' ).setCSSVar( '--dropdown-hover-primary-color', getPrimaryColor( newval ) );
			$( '.main-navigation' ).setCSSVar( '--dropdown-hover-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	
	/* Menu bar, sticky */

	wp.customize( 'boldthemes_theme_theme_options[sticky_menu_bar_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.main-navigation' ).setCSSVar( '--sticky-primary-color', getPrimaryColor( newval ) );
			$( '.main-navigation' ).setCSSVar( '--sticky-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_menu_bar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sticky-menu-bar-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[sticky_menu_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--sticky-menu-bar-color', newval );
		} );
	} );
	
	/* Default headline */
	
	wp.customize( 'boldthemes_theme_theme_options[default_headline_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			console.log( boldthemes_color_schemes[ newval - 1 ] );
			$( '.page-header' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.page-header' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );	
	wp.customize( 'boldthemes_theme_theme_options[default_headline_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--default-headline-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[default_headline_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--default-headline-color', newval );
		} );
	} );
	
	/* Responsive menu */
	
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-header' ).setCSSVar( '--responsive-menu-primary-color', getPrimaryColor( newval ) );
			$( '.site-header' ).setCSSVar( '--responsive-menu-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-menu-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_menu_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-menu-color', newval );
		} );
	} );	
	
	/* Responsive header */
		
	wp.customize( 'boldthemes_theme_theme_options[responsive_header_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-header-responsive' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.site-header-responsive' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_header_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-header-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_header_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-header-color', newval );
		} );
	} );	
	
	/* Responsive sticky header */
		
	wp.customize( 'boldthemes_theme_theme_options[responsive_sticky_header_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-header-responsive' ).setCSSVar( '--primary-sticky-color', getPrimaryColor( newval ) );
			$( '.site-header-responsive' ).setCSSVar( '--secondary-sticky-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_sticky_header_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-sticky-header-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[responsive_sticky_header_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--responsive-sticky-header-color', newval );
		} );
	} );		
	
	/* Footer widgets */
		
	wp.customize( 'boldthemes_theme_theme_options[footer_widgets_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-widgets' ).setCSSVar( '--footer-widgets-primary-color', getPrimaryColor( newval ) );
			$( '.site-footer-widgets' ).setCSSVar( '--footer-widgets-secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[footer_widgets_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--footer-widgets-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[footer_widgets_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--footer-widgets-color', newval );
		} );
	} );

	/* 404 */	
		
	wp.customize( 'boldthemes_theme_theme_options[error_404_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '.error-404' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.error-404' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[error_404_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--error-404-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[error_404_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--error-404-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[error_404_background_image]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--error-404-background-image', 'url(\'' + newval + '\')' );
		} );
	} );
	
	/* Buttons */

	wp.customize( 'boldthemes_theme_theme_options[blog_list_read_more_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.blog .read-more .bt_bb_button, .archive:not(.post-type-archive-portfolio) .read-more .bt_bb_button' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_read_more_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.post-type-archive-portfolio .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.post-type-archive-portfolio .read-more .bt_bb_button' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.post-type-archive-portfolio .read-more .bt_bb_button' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_read_more_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.search .read-more .bt_bb_button' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.search .read-more .bt_bb_button' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.search .read-more .bt_bb_button' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	
	/* Icons */
		
	wp.customize( 'boldthemes_theme_theme_options[page_single_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.page-header .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.page-header .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.page-header .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_list_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.blog .share-options .bt_bb_icon, .archive:not(.post-type-archive-portfolio) .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[blog_single_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.single-post .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.single-post .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.single-post .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_list_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.post-type-archive-portfolio .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[pf_single_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.single-portfolio .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.single-portfolio .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.single-portfolio .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[search_list_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( 'article.search .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( 'article.search .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( 'article.search .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );		
	wp.customize( 'boldthemes_theme_theme_options[shop_list_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.post-type-archive-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.post-type-archive-product .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.post-type-archive-product .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[shop_single_share_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			// $( '.single-product .share-options .bt_bb_icon' ).removeClassStartingWith( 'bt_bb_color_scheme_' ).addClass( 'bt_bb_color_scheme_' + newval );
			$( '.single-product .share-options .bt_bb_icon' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '.single-product .share-options .bt_bb_icon' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );	
	
	

	/* Preloader / leave refresh */	
		
	/*wp.customize( 'boldthemes_theme_theme_options[preloader_color_scheme]', function( value ) {
		value.bind( function( newval ) {
			$( '#preloader' ).setCSSVar( '--primary-color', getPrimaryColor( newval ) );
			$( '#preloader' ).setCSSVar( '--secondary-color', getSecondaryColor( newval ) );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[preloader_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--preloader-background-color', newval );
		} );
	} );
	wp.customize( 'boldthemes_theme_theme_options[preloader_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).setCSSVar( '--preloader-color', newval );
		} );
	} );*/
	
	
	/* Shows if page has overriden customizer settings notification */
	
	$( 'iframe' ).ready(function() {
		if ( $( this ).contents().find( 'body' ).hasClass( 'boldthemes-has-override' ) ) {
			$( 'body' ).append( '<div class="boldthemes-notification"><span>' + boldthemes_text_strings.override_alert + '</span><div class="boldthemes-notification-close"></div></div>' );
			$( 'body' ).on( 'click', '.boldthemes-notification-close', function() {
				$( this ).parent().removeClass( 'boldthemes-notification-show' );
			} );
			setTimeout(() => {
				$( '.boldthemes-notification' ).addClass( 'boldthemes-notification-show' );
			}, 500 );
			setTimeout(() => {
				$( '.boldthemes-notification' ).removeClass( 'boldthemes-notification-show' );
			}, 15000 );
		}
	});
	
} )( jQuery );