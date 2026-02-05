'use strict';

( function( $ ) {
	
	var bt_initialized = false; 
	
	/*------------*/
	/* Header     */
	/*------------*/
		
	/* Body color rgb versions */
	
	function boldthemes_rgb2array( color ) {
		var output = [ color ];
		if( color.indexOf("rgba") > -1 ) {
			output = output.concat( color.slice( color.indexOf("(") + 1, color.lastIndexOf(",") ).split(',') ); // cut-off alpha	
		} else if( color.indexOf("rgb") > -1 ) {
			output = output.concat( color.slice( color.indexOf("(") + 1, color.indexOf(")") ).split(',') );	
		} else if( color != null ) {
			output = color ;	
		}
		return output;
	}
	
	function boldthemes_hex2rgb( color ) {
		var output = color.indexOf("#") > -1 ? /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( color ) : color;
		return output ? output : null;
	}
	
	function boldthemes_set_color_vars( colors_arr ) {
		$.each( colors_arr, function( key, value) {
			var original_color = $( document.body ).css( key );
			var rgb_arr = boldthemes_rgb2array( boldthemes_hex2rgb( original_color ) );
			var r_str = '' + parseInt( rgb_arr[1], 16 );
			var g_str = '' + parseInt( rgb_arr[2], 16 );
			var b_str = '' + parseInt( rgb_arr[3], 16 );
			var rgb_str = r_str + ', ' + g_str + ', ' + b_str + '';
			$( document.body ).css( value + '-r', r_str );
			$( document.body ).css( value + '-g', g_str );
			$( document.body ).css( value + '-b', b_str );
			$( document.body ).css( value + '-rgb', rgb_str );
		});
	}
	
	boldthemes_set_color_vars( {
		"color" : "--page-primary-color",
		"background-color" : "--page-secondary-color",
		"--accent-color" : "--accent-color",
		"--alternate-color" : "--alternate-color",
	} );
	
	$( '.page-header .page-header-inner' ).css( 'padding-top',  $( document.body ).hasClass( 'bt-header-responsive-active' ) ? $( '#masthead-responsive' ).height() : $( '#masthead' ).height() + 'px');
	
	/* Mark date fields with content */

	$( '.wpcf7-form .wpcf7-date, .wpcf7-form .wpcf7-text, .wpcf7-form .wpcf7-textarea' ).change( function( i, obj ) {
		if( $( this ).val() != '' ) {
			$( this ).addClass( 'bt-input-has-value' );
		} else {
			$( this ).removeClass( 'bt-input-has-value' );
		}
	});	

	/* Add advanced placeholders */

	$( '.bt_bb_cf7_advanced_placeholders input, input.bt_bb_cf7_advanced_placeholders, .bt_bb_cf7_advanced_placeholders .wpcf7-textarea, .wpcf7-textarea.bt_bb_cf7_advanced_placeholders' ).each( function( index ) {
		if( $( this ).attr( 'placeholder' ) != '' &&  typeof( $( this ).attr( 'placeholder' ) ) != 'undefined' ) {
			// console.log( $( this ).attr( 'placeholder' ) );
			$( this ).parent().attr( 'data-placeholder', $( this ).attr( 'placeholder' ) );
		}
	});		

	/* Move excerpt before the entry meta */

	$( '.entry-meta .excerpt' ).each( function(){
		$( this ).addClass( 'excerpt-' + $( this ).parent().attr('class').split(' ').pop() );
		$( this ).insertBefore( $( this ).parent() );
	});
	
	/*  Logo area central menu  */
	
	if ( $( document.body ).hasClass( 'primary-menu-position-logo-center' ) ) {
		var nav_elements = $( 'body.primary-menu-position-logo-center .main-navigation-menu > li' ),
		nav_elements_length = $( nav_elements ).length,
		middle = Math.ceil( nav_elements_length / 2 );
		$( '.main-navigation-menu' ).attr( 'id', 'primary-menu-left' ).addClass( 'left-nav' ).clone().appendTo( '.main-navigation > nav' ).attr( 'id', 'primary-menu-right' ).removeClass( 'left-nav' ).addClass( 'right-nav' );
		$( '.right-nav > li' ).slice( 0, middle ).remove();
		$( '.left-nav > li'  ).slice( middle, nav_elements_length ).remove();
		$( '.site-branding-logo-text' ).clone().addClass( 'cloned-site-branding' ).insertAfter( '.main-navigation > nav .left-nav' );		
	}
	
	/* Default headline parallax */
	
	if ( $( document.body ).is( '.default-headline-parallax-slow, .default-headline-parallax-normal, .default-headline-parallax-fast' )) {
		$( '.page-header' ).addClass( 'bt_bb_parallax' );
		$( 'body.default-headline-parallax-slow .page-header' ).attr( 'data-parallax', 0.9 );
		$( 'body.default-headline-parallax-normal .page-header' ).attr( 'data-parallax', 0.7 );
		$( 'body.default-headline-parallax-fast .page-header' ).attr( 'data-parallax', 0.5 );	
		$( '.page-header' ).attr( 'data-parallax-offset', 0 );
	}
	
	/* Block search widget enable full screen */
	$( 'div.widget_search:not(.widget_block)' ).each( function( index ) {
		if( $( this ).css( '--widget-full-screen-enabled' ).trim() == 'on' ) {
			$( this ).addClass( 'bt-enable-fullscreen bt-off' );
			$( this ).find( '.search-form' ).wrap( "<div class='full-screen-wrapper'></div>" );;
			$( '<div class="bt-enable-fullscreen-open"></div>' ).prependTo( $( this ) );
			$( '<div class="bt-enable-fullscreen-close"></div>' ).prependTo( $( this ) );
			$( this ).find( '.bt-enable-fullscreen-open' ).on( 'click', function(){ $( 'body' ).addClass( 'bt-fullscreen-search-widget-on' ); $( this ).parent().addClass( 'bt-on' ).removeClass( 'bt-off' ) ;});
			$( this ).find( '.bt-enable-fullscreen-close' ).on( 'click', function(){ $( 'body' ).removeClass( 'bt-fullscreen-search-widget-on' ); $( this ).parent().removeClass( 'bt-on' ).addClass( 'bt-off' ) ;});			
		}

	});
	
	/*-------------------*/	
	/* Responsive header */
	/*-------------------*/
	
	/* Sticky header helper functions */
	
	window.boldthemes_requestTimeout = function(fn, delay) {
		if( !window.requestAnimationFrame      	&& 
			!window.webkitRequestAnimationFrame && 
			!(window.mozRequestAnimationFrame && window.mozCancelRequestAnimationFrame) && // Firefox 5 ships without cancel support
			!window.oRequestAnimationFrame      && 
			!window.msRequestAnimationFrame)
				return window.setTimeout(fn, delay);
				
		var start = new Date().getTime(),
			handle = new Object();
			
		function loop(){
			var current = new Date().getTime(),
				delta = current - start;
				
			delta >= delay ? fn.call() : handle.value = boldthemes_requestAnimFrame(loop);
		};
		
		handle.value = boldthemes_requestAnimFrame(loop);
		return handle;
	};
	
	window.boldthemes_requestAnimFrame = (function() {
		return  window.requestAnimationFrame       || 
				window.webkitRequestAnimationFrame || 
				window.mozRequestAnimationFrame    || 
				window.oRequestAnimationFrame      || 
				window.msRequestAnimationFrame     || 
				function(/* function */ callback, /* DOMElement */ element){
					window.setTimeout(callback, 1000 / 60);
				};
	})();
	

		
	/* Responsive header and touch menu functions */
	
	function boldthemes_responsive_checker() {
		var header_responsive_breakpoint = parseInt( $( 'body' ).attr('data-header-responsive-breakpoint') );
		var sidebar_responsive_breakpoint = parseInt( $( 'body' ).attr('data-sidebar-responsive-breakpoint') );
		var windowWidth = window.innerWidth;
		/* Show/hide responsive header */
		if ( header_responsive_breakpoint > 0 ) {
			if ( windowWidth < header_responsive_breakpoint ) {
				$( document.body ).addClass( 'bt-header-responsive-active' ).removeClass( 'bt-header-responsive-inactive' );
				boldthemes_requestTimeout( boldthemes_responsive_header_add_active_class, 200);
			} else {
				$( document.body ).removeClass( 'bt-header-responsive-ready' );
				$( document.body ).removeClass( 'bt-header-responsive-active' ).addClass( 'bt-header-responsive-inactive' );
			}			
		}
		/* Move sidebar */
		if ( sidebar_responsive_breakpoint > 0 ) {
			if ( windowWidth < sidebar_responsive_breakpoint && sidebar_responsive_breakpoint > 0 ) {
				$( document.body ).addClass( 'bt-sidebar-responsive-active' ).removeClass( 'bt-sidebar-responsive-inactive' );
			} else {
				$( document.body ).removeClass( 'bt-sidebar-responsive-active' ).addClass( 'bt-sidebar-responsive-inactive' );
			}
		}
		/* Fix content padding */
		if ( $( document.body ).hasClass( 'header-position-top' ) ) {
			$( '#page' ).css( 'padding-top',  $( document.body ).hasClass( 'bt-header-responsive-active' ) ? $( '#masthead-responsive' ).outerHeight() : $( '#masthead' ).outerHeight() + 'px');
			$( '.page-header .page-header-inner' ).css( 'padding-top',  '0px');
		} else {
			$( '#page' ).css( 'padding-top',  '0px');
			$( '.page-header .page-header-inner' ).css( 'padding-top',  $( document.body ).hasClass( 'bt-header-responsive-active' ) ? $( '#masthead-responsive' ).height() : $( '#masthead' ).height() + 'px');
		}	
	}
	
	function boldthemes_responsive_header_add_active_class() {
		$( document.body ).addClass( 'bt-header-responsive-ready' );
	}
	
	/* Responsive header init */
	
	if ( parseInt( $( 'body' ).data( 'header-responsive-breakpoint' ) ) > 0 ) {
		boldthemes_responsive_checker();
		$( window ).on( 'resize', function(){
			boldthemes_responsive_checker();
		});	
	}
	
	/* Sticky header init  */
	
	function boldthemes_is_sticky_active() {
		// return ( parseInt( $( 'body' ).data( 'data-sticky-header-scroll-breakpoint' ) ) > 0 );
		return ( $( 'body' ).hasClass( 'enable-sticky-1' ) );
	}	
	$( document.body ).addClass( 'bt-sticky-header-inactive' ); 
	if ( boldthemes_is_sticky_active() ) {
		$( document.body ).addClass( 'bt-sticky-header' );
		boldthemes_sticky_activation();
		$( window ).on( 'scroll', function(){
			boldthemes_sticky_activation();
			//console.log( this.oldScroll > this.scrollY );
			if ( this.oldScroll > this.scrollY ) {
				$( 'body' ).addClass( 'bt-scroll-up' ).removeClass( 'bt-scroll-down' );	
			} else {
				$( 'body' ).addClass( 'bt-scroll-down' ).removeClass( 'bt-scroll-up' );	
			}
			this.oldScroll = this.scrollY;
		});
	}
	
	/*-------------------*/	
	/* Sticky header     */
	/*-------------------*/
	
	/* Sticky header functions */
	
	function boldthemes_delayed_activate_sticky_open() {
		$( document.body ).addClass( 'bt-sticky-header-active-open' );				  		
		$( document.body ).removeClass( 'bt-sticky-header-inactive' );
		boldthemes_close_all_shopping_carts();		
	}
	function boldthemes_delayed_activate_sticky_close() {
		$( document.body ).removeClass( 'bt-sticky-header-active' ); 		
		$( document.body ).removeClass( 'bt-sticky-header-active-open' ); 		
		$( document.body ).removeClass( 'bt-sticky-header-active-closed' ); 		
		$( document.body ).addClass( 'bt-sticky-header-inactive' ); 		
	}
	
	function boldthemes_sticky_activation() {
		var scroll_breakpoint = parseInt( $( 'body' ).attr('data-sticky-header-scroll-breakpoint') );
		
		// if ( scroll_breakpoint > 0 ) {
			var css_class = 'bt-sticky-header-active';
			var from_top = $( window ).scrollTop();
			if ( from_top > scroll_breakpoint ) {
				if ( !$( document.body ).hasClass( 'bt-sticky-header-active' )  ) {
					$( document.body ).addClass( 'bt-sticky-header-active' ); 
					boldthemes_requestTimeout( boldthemes_delayed_activate_sticky_open, 500);
				}
			} else if ( from_top <= scroll_breakpoint ) {
				if ( ( $( document.body ).hasClass( 'bt-sticky-header-active' ) || $( document.body ).hasClass( 'bt-sticky-header-active-open' ) ) && !$( 'body' ).hasClass('bt-sticky-header-active-closed')  ) {
					$( document.body ).addClass( 'bt-sticky-header-active-closed' );
					boldthemes_requestTimeout( boldthemes_delayed_activate_sticky_close, 500);
				}
			}			
		// } else {
			// boldthemes_requestTimeout( boldthemes_delayed_activate_sticky_close, 500);
		// }
	}

	/*--------------------*/		
	/* - Dropdown menu  - */
	/*--------------------*/	
	
	/* Dropdown menu functions */
	
	function boldthemes_init_dropdowns() {
		if ( $( 'html' ).hasClass( 'no-touch' ) ) {
			$( '#site-navigation nav ul li' ).on( 'mouseenter', function (e) {
				if ( $( document.body ).hasClass( 'bt-header-responsive-inactive' ) ) boldthemes_open_dropdowns( $( this ) );
			});	
			$( '#site-navigation nav ul li' ).on( 'mouseleave', function (e) {
				if ( $( document.body ).hasClass( 'bt-header-responsive-inactive' ) ) boldthemes_close_dropdowns( $( this ) );
			});	
		}
		$( '#site-navigation nav ul li > .sub-toggler' ).on( 'click', function(e) {
			if ( $( this ).parent().hasClass('on') ) {
				boldthemes_close_dropdowns( $( this ).parent() );
			} else {
				boldthemes_open_dropdowns( $( this ).parent() );
			}
			
		});
	}
	
	function boldthemes_close_dropdowns( li ) {
		li.siblings().addBack().removeClass( 'on' );
		return false;
	}
	
	function boldthemes_open_dropdowns( li ) {
		li.siblings().addBack().removeClass( 'on' );
		li.addClass( 'on' );
		return false;
	}
	
	function boldthemes_toggle_dropdowns( li ) {
		li.toggleClass( 'on' );
		return false;
	}
	
	function boldthemes_touch_checker() {
		$( '#site-navigation nav ul ul' ).parent().prepend( '<div class="sub-toggler"></div>');
		var isHoverableDevice = window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
		var isTouchDevice = !isHoverableDevice;
		// var isTouchDevice = ( 'ontouchstart' in window ) || ( navigator.MaxTouchPoints > 0 ) || ( navigator.msMaxTouchPoints > 0 );
		if ( isTouchDevice ) {
			$( 'html' ).addClass( 'touch' ).removeClass( 'no-touch' );
		} else {
			$( 'html' ).addClass( 'no-touch' ).removeClass( 'touch' );
		}
		boldthemes_init_dropdowns();
	}
	
	/* Dropdown menu init */
	
	boldthemes_touch_checker();	
	
	/* Responsive trigger click */
	
	$( '.site-header-responsive-trigger' ).on( 'click', function () {
		$( document.body ).toggleClass( 'bt-responsive-menu-visible' );
		return false;
	});
	
	/* ------------- */
	/* - Preloader - */
	/* ------------- */
	
	function remove_preloader(){
		if( !$( document.body ).hasClass( 'remove-preloader' ) ) {
			$( document.body ).addClass( 'remove-preloader' );
		}		
	}
	
	$( window ).on( 'load', function() { 
		remove_preloader();
	});
	
	/* ----------------- */
	/* - Shopping cart - */
	/* ----------------- */
	
	function boldthemes_close_all_shopping_carts(){
		$( '.site-header .woocommerce.widget_shopping_cart, .site-header-responsive .woocommerce.widget_shopping_cart' ).removeClass( 'bt-on' );
	}
	
	function boldthemes_open_shopping_cart( elem ){
		boldthemes_close_all_shopping_carts();
		elem.addClass( 'bt-on' );
	}
	
	function boldthemes_close_shopping_cart( elem ){
		elem.removeClass( 'bt-on' );
	}
	
	function boldthemes_shoping_cart_remove_added_to_cart_class(){
		$( '.site-header .woocommerce.widget_shopping_cart, .site-header-responsive .woocommerce.widget_shopping_cart' ).removeClass( 'bt-added-to-cart' );
	}
	
	$( window ).on( 'load', function() { 
		$( 'body' ).on('click', '.site-header .woocommerce.widget_shopping_cart .widgettitle, .site-header-responsive .woocommerce.widget_shopping_cart .widgettitle', function () {
			$( this ).parent().hasClass( 'bt-on' ) ? boldthemes_close_shopping_cart( $( this ).parent() ) : boldthemes_open_shopping_cart( $( this ).parent() );
		});
	});
	$( document.body ).on( 'added_to_cart', function(){
		// console.log( 'added_to_cart' );
		$( '.site-header .woocommerce.widget_shopping_cart, .site-header-responsive .woocommerce.widget_shopping_cart' ).addClass( 'bt-added-to-cart' );
		// boldthemes_requestTimeout( boldthemes_shoping_cart_remove_added_to_cart_class, 500);
		setTimeout( function(){ $( '.site-header .woocommerce.widget_shopping_cart, .site-header-responsive .woocommerce.widget_shopping_cart' ).removeClass('bt-added-to-cart'); }, 4000); });
	

	/* ----------------- */
	/* Copy to clipboard */
	/* ----------------- */
	
	$( document.body ).on( 'click', '.bt_bb_icon_holder_copy', function( event ) {
		event.preventDefault();
		var href = window.location.href;
		
		var copy_to_clipboard_ok_message = $( 'body' ).data( 'copy-to-clipboard-ok' );
		var copy_to_clipboard_notok_message = $( 'body' ).data( 'copy-to-clipboard-notok' );
		
		boldthemes_copy_to_clipboard( href )
			.then(() => alert( copy_to_clipboard_ok_message + href ) )
			.catch(() => console.log( copy_to_clipboard_notok_message ) );	
	});

	/* copy to clipboard function */
	
	function boldthemes_copy_to_clipboard( textToCopy ) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText( textToCopy );
		} else {
			let textArea = document.createElement( "textarea" );
			textArea.value = textToCopy;
			textArea.style.position = "fixed";
			textArea.style.left = "-999999px";
			textArea.style.top = "-999999px";
			document.body.appendChild( textArea );
			textArea.focus();
			textArea.select();
			return new Promise( ( res, rej ) => {
				document.execCommand( 'copy' ) ? res() : rej();
				textArea.remove();
			});
		}
	}
	
	/* ----------- */
	/* Top bar fix */
	/* ----------- */
		
	if ( $( "#masthead .site-header-top-bar" ).length ) {
		$( "#masthead .site-header-top-bar" ).css( "--real-top-bar-height", $( ".site-header-top-bar" ).height() + 'px' );
	}
	
})( jQuery );