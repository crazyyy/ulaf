(function( $ ) {
	"use strict";

	var bt_bb_css_post_grid_load_images = function( root ) {		
		root.each(function() {
			var page_bottom = $( window ).scrollTop() + $( window ).height();
			$( this ).find( '.bt_bb_grid_item' ).each(function() {
				var this_top = $( this ).offset().top;
				if ( this_top < page_bottom + $( window ).height() ) {
					var img_src = $( this ).data( 'src' );
					if ( img_src !== '' && $( this ).find( '.bt_bb_grid_item_post_thumbnail a' ).html() == '' ) {
						$( this ).find( '.bt_bb_grid_item_post_thumbnail a' ).html( '<img src="' + img_src + '" alt="' + $( this ).data( 'alt' ) + '">' );
					}
				}
			});
		});
	}

	var bt_bb_css_post_grid_load_items = function( root ) {		
		root.each(function() {	
			var loading = root.data( 'loading' );
			if ( loading === undefined || ( loading != 'loading' && loading != 'no_more' ) ) {
				var page_bottom = $( window ).scrollTop() + $( window ).height();
				$( this ).find( '.bt_bb_grid_item' ).each(function() {
					var this_top = $( this ).offset().top;
					if ( this_top < page_bottom + $( window ).height() ) {
						if ( $( this ).is( ':last-child' ) ) {
							var root_data_offset = root.attr( 'data-offset' );							
							var offset = parseInt( root_data_offset === undefined ? 0 : root_data_offset ) + parseInt( root.data( 'number' ) );
							bt_bb_css_post_grid_load_posts( root, offset );
							return false;							
						}
					}
				});
			}
		});
	}

	var bt_bb_css_post_grid_load_posts = function( root, offset ) {
		if ( offset == 0 ) {
			root.addClass( 'bt_bb_grid_hide' );
			root.find( '.bt_bb_grid_item' ).remove();
		}
		
		root.parent().find( '.bt_bb_post_grid_loader' ).show();

		var action = 'bt_bb_get_css_grid';

		var root_data_number = root.data( 'number' );
		var root_grid_number = root.data( 'grid-number' );

		var data = {
			'action': action,
			'number': root_data_number,
			'category': root.data( 'category' ),
			'bt-bb-css-post-grid-nonce': root.data( 'bt-bb-css-post-grid-nonce' ),
			'post-type': root.data( 'post-type' ),
			'offset': offset,
			'show': root.data( 'show' ),
			'show_superheadline': root.data( 'show-superheadline' ),
			'show_subheadline': root.data( 'show-subheadline' ),
			'format': root.data( 'format' ),
			'grid_number': root.data( 'grid-number' )
		};
		root.data( 'loading', 'loading' );
		
		$.ajax({
			type: 'POST',
			url: ajax_object.ajax_url,
			data: data,
			async: true,
			success: function( response ) {
				if ( response == '' ) {					
					root.data( 'loading', 'no_more' );
					root.parent().find( '.bt_bb_post_grid_loader' ).hide();
					return;
				}

				var $content = $( response );
				root.append( $content );

				root.attr( 'data-offset', offset );

				root.parent().find( '.bt_bb_post_grid_loader' ).hide();
				root.removeClass( 'bt_bb_grid_hide' );
				root.parent().find( '.bt_bb_grid_container' ).css( 'height', 'auto' );

				bt_bb_css_post_grid_load_images( root );

				if ( root.data( 'auto-loading' ) == 'auto_loading' ) {
					root.data( 'loading', '' );
				} else {
					root.data( 'loading', 'no_more' );
				}

			},
			error: function( response ) {
				root.parent().find( '.bt_bb_post_grid_loader' ).hide();
				root.removeClass( 'bt_bb_grid_hide' );			
			}
		});
	}

	$( document ).ready(function() {
		
		$( window ).on( 'resize', function() {
		});		
		
		$( window ).on( 'scroll', function() {	
				$( '.bt_bb_css_post_grid' ).each(function() {					
						$( this ).find( '.bt_bb_css_post_grid_content' ).each(function() {	
								if (bt_bb_css_post_grid_isOnScreen( $( this ).parent(), -200 )){									
										bt_bb_css_post_grid_load_images( $( this ) );
										bt_bb_css_post_grid_load_items( $( this ) );
								}
						});
				});
		});

		var j = 1;
		$( '.bt_bb_css_post_grid' ).each(function() {			
			   $( this ).find( '.bt_bb_css_post_grid_content' ).each(function() {
						$( this ).attr( 'data-grid-number', j );
						bt_bb_css_post_grid_load_posts( $( this ), 0 );	
						
			   });
			   j++;
	   });
		   
		$( '.bt_bb_css_post_grid_filter_item' ).on( 'click', function() {
			var root = $( this ).closest( '.bt_bb_grid_container' );
			root.height( root.height() );
			$( this ).parent().find( '.bt_bb_css_post_grid_filter_item' ).removeClass( 'active' ); 
			$( this ).addClass( 'active' );
			var grid_content = $( this ).closest( '.bt_bb_css_post_grid' ).find( '.bt_bb_css_post_grid_content' );
			grid_content.data( 'category', $( this ).data( 'category' ) );
			bt_bb_css_post_grid_load_posts( grid_content, 0 );
		});
	});

	// isOnScreen fixed
	
	function bt_bb_css_post_grid_iOSversion() {
	  if (/iP(hone|od|ad)/.test(navigator.platform)) {
		// supports iOS 2.0 and later: <http://bit.ly/TJjs1V>
		var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
		return [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];
	  } else {
		  return false;
	  }
	}

	var ver = bt_bb_css_post_grid_iOSversion();
	
	// isOnScreen
	
	function bt_bb_css_post_grid_isOnScreen( elem, top_offset ) {
		if ( ver && ver[0] == 13 ) return true;
		top_offset = ( top_offset === undefined ) ? 75 : top_offset;
		var element = elem.get( 0 );
		if ( element == undefined ) return false;
		var bounds = element.getBoundingClientRect();
		var output = bounds.top + top_offset < window.innerHeight && bounds.bottom > 0;
		// alert(output);
		return output;
	}

})( jQuery );