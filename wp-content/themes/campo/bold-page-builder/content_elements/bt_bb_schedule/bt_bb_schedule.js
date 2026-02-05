(function( $ ) {
	"use strict";
	$( '.bt_bb_schedule_item_title' ).click(function() {
		var $item = $( this ).closest( '.bt_bb_schedule_item' );
		if ( ! $item.hasClass('on') ) {
			$( this ).closest( '.bt_bb_schedule' ).find( '.bt_bb_schedule_item.on' ).removeClass( 'on' );
			$item.addClass( 'on' );
			if ( ! window.initialschedule ) {
				var top_of_element = $item.offset().top;
				var bottom_of_element = $item.offset().top + $("#element").outerHeight();
				var bottom_of_screen = $( window ).scrollTop() + $(window).innerHeight();
				var top_of_screen = $( window ).scrollTop();
				var diff = top_of_screen - top_of_element;
				if( diff > 0 ) {
					$( 'html' ).scrollTop( $( 'html' ).scrollTop() - diff - 15 );
				}
			} else {
				window.initialschedule = false;
			}
		
		} else {
			$( this ).closest( '.bt_bb_schedule_item' ).removeClass( 'on' );
		}
	});
})( jQuery );