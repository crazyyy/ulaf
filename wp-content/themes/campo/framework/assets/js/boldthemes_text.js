(function($) {
	
	'use strict';

	$( document ).ready(function() {

		var init_picker = function() {
			$( '.boldthemes_override_color_field' ).wpColorPicker({
				change: function( event, ui ) { var element = event.target; var color = ui.color.toString(); set_value( $( element ), color ); },
				clear: function( event ) { var element = $( event.target ).siblings( '.wp-color-picker' )[0]; set_value( $( element ), '' ); }
			});
		}

		init_picker();

		var frame;

		// ADD IMAGE LINK
		// https://codex.wordpress.org/Javascript_Reference/wp.media
		$( 'body' ).on( 'click', '.boldthemes_override_add_image', function( event ) {

			event.preventDefault();

			var parent = $( this ).parent();
			var input = parent.find( '.boldthemes_value' );

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
			}

			// Create a new media frame
			var frame = wp.media({
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected in the media frame...
			frame.on( 'select', function() {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var url = attachment.sizes !== undefined && attachment.sizes.full !== undefined ? attachment.sizes.full.url : attachment.url;
				var preview_url = attachment.sizes !== undefined && attachment.sizes.thumbnail !== undefined ? attachment.sizes.thumbnail.url : attachment.url;
				input.val( url );
				input.trigger( 'keyup' );
				parent.find( 'img' ).remove();
				input.after( '<img src="' + preview_url + '">' );
			});

			// Finally, open the modal on click
			frame.open();

		});

		var set_value = function( element, value ) {
			var hidden_el = element.closest( '.rwmb-boldthemestext-clone' ).find( 'input[type="hidden"]' );
			var key_el = element.closest( '.rwmb-boldthemestext-clone' ).find( 'select' );
			
			var key = key_el.val();
			
			if ( key != '' ) {
				hidden_el.attr( 'value', key + ':' + value );
			} else {
				hidden_el.attr( 'value', '' );
			}
		}

		$( 'body' ).on( 'keyup change', 'input[type="text"].boldthemes_value, textarea.boldthemes_value', function() {
			var value = $( this ).val();
			var hidden_el = $( this ).parent().find( 'input[type="hidden"]' );
			var key_el = $( this ).parent().find( 'select' );
			
			if ( key_el.length == 0 ) {
				key_el = $( this ).parent().find( '.boldthemes_key' ); // BoldThemesText1
			}
			
			var key = key_el.val();
			
			if ( key != '' ) {
				hidden_el.attr( 'value', key + ':' + value );
			} else {
				hidden_el.attr( 'value', '' );
			}
		});

		$( 'body' ).on( 'click', 'input[type="checkbox"].boldthemes_value', function() {
			var value = $( this ).is( ':checked' ) ? 'true' : 'false';
			var hidden_el = $( this ).parent().find( 'input[type="hidden"]' );
			var key_el = $( this ).parent().find( 'select' );
			
			var key = key_el.val();
			
			if ( key != '' ) {
				hidden_el.attr( 'value', key + ':' + value );
			} else {
				hidden_el.attr( 'value', '' );
			}
		});

		$( 'body' ).on( 'change', 'select.boldthemes_value', function() {
			var value = $( this ).val();
			var hidden_el = $( this ).parent().find( 'input[type="hidden"]' );
			var key_el = $( this ).parent().find( 'select.boldthemes_key_select' );
			
			var key = key_el.val();
			
			if ( key != '' ) {
				hidden_el.attr( 'value', key + ':' + value );
			} else {
				hidden_el.attr( 'value', '' );
			}
		});

		$( 'body' ).on( 'keyup', '.boldthemes_key', function() { // BoldThemesText1
			var key = $( this ).val();
			var hidden_el = $( this ).parent().find( 'input[type="hidden"]' );
			var val_el = $( this ).parent().find( '.boldthemes_value' );
			var value = val_el.val();
			
			if ( key != '' ) {
				hidden_el.attr( 'value', key + ':' + value );
			} else {
				hidden_el.attr( 'value', '' );
			}
		});
		
		$( 'body' ).on( 'change', '.boldthemes_key_select', function() {
			var hidden_el = $( this ).parent().find( 'input[type="hidden"]' );
			var input_el = $( this ).parent().find( '.wp-picker-container,input[type="text"],input[type="checkbox"],select,textarea,.bt_bb_iconpicker_widget_placeholder,.bt_bb_iconpicker' ).not( '.boldthemes_key_select' ).not( '.boldthemes_key_filter' ).first();
			
			$( this ).parent().find( 'img,span' ).remove();

			var val = '';
			
			var pfx = $( this ).data( 'pfx' ) + '_';
			var selected_option = $( this ).find( 'option:selected' );
			var selected_val = selected_option.val();
			selected_val = selected_val.replace( pfx, '' );
			var selected_parent = selected_option.parent().attr('label');
			$( this ).find( 'option' ).each(function( index ) {
				$( this ).text( $( this ).text().split( ' / ' )[0] );
			});
			if( selected_parent ) selected_option.text( selected_option.text() + ' / ' + selected_parent );

			if ( window.bt_fake_customizer_controls[ selected_val ] !== undefined ) {
				var selected_control_object = window.bt_fake_customizer_controls[ selected_val ];
				if ( selected_control_object['type'] !== undefined ) {
					var selected_control_type = selected_control_object['type'];
					if ( selected_control_type == 'checkbox' ) {
						var input_el_checkbox = $( '<input type="checkbox" class="boldthemes_value">' );
						val = 'false';
						input_el_checkbox.insertAfter( input_el );
					} else if ( selected_control_type == 'select' ) {
						var input_el_select = $( '<select class="boldthemes_value"></select>' );
						input_el_select.insertAfter( input_el );
						$.each( selected_control_object['choices'], function( k, v ) {
							input_el_select.append( '<option value="' + k + '">' + v + '</option>' );
						});
						val = input_el_select.val();
					} else if ( selected_control_type == 'BoldThemes_Customize_Multiple_Select_Control' ) {
						var input_el_select = $( '<select class="boldthemes_value" multiple title="' + boldthemes_text_translate.multiple + '"></select>' );
						input_el_select.insertAfter( input_el );
						$.each( selected_control_object['choices'], function( k, v ) {
							input_el_select.append( '<option value="' + k + '">' + v + '</option>' );
						});
						val = input_el_select.val();
						val = val.join();
					} else if ( selected_control_type == 'WP_Customize_Color_Control' ) {
						var input_el_color = $( '<input type="text" class="boldthemes_value boldthemes_override_color_field">' );
						val = '';
						input_el_color.insertAfter( input_el );
						init_picker();
					} else if ( selected_control_type == 'WP_Customize_Image_Control' ) {
						var input_el_image = $( '<input type="text" class="boldthemes_value"><span class="boldthemes_override_add_image" title="' + boldthemes_text_translate.media + '"><i class="dashicons dashicons-plus-alt"></i></span>' );
						val = '';
						input_el_image.insertAfter( input_el );
					} else if ( selected_control_type == 'BoldThemes_Customize_Textarea_Control' ) {
						var input_el_text = $( '<textarea rows="5" class="boldthemes_value">' );
						input_el_text.insertAfter( input_el );
					} else if ( selected_control_type == 'BoldThemes_Customize_Iconpicker_Control' ) {
						var input_el_text = $( window.bt_bb_iconpicker( false, '' ) );
						input_el_text.insertAfter( input_el );
						window.bt_bb_iconpicker_init( 'override' );
					} else {
						var input_el_text = $( '<input type="text" class="boldthemes_value">' );
						if ( typeof selected_control_object["input_attrs"] != 'undefined' ) input_el_text.attr( 'placeholder', selected_control_object["input_attrs"]["placeholder"] );
						input_el_text.insertAfter( input_el );
					}
				} else {
					var input_el_text = $( '<input type="text" class="boldthemes_value">' );
					input_el_text.insertAfter( input_el );
				}
			} else {
				var input_el_text = $( '<input type="text" class="boldthemes_value">' );
				input_el_text.insertAfter( input_el );
			}
		
			input_el.remove();

			if ( $( this ).val() != '' ) {
				hidden_el.attr( 'value', $( this ).val() + ':' + val );
			} else {
				hidden_el.attr( 'value', '' );
			}
		});	
		
		toggle_remove_buttons();
		
		function add_cloned_fields( $input ) {
			var $clone_last = $input.find( '.rwmb-clone:last' ),
				$clone = $clone_last.clone(),
				$input, name;

			$clone.find( '.wp-picker-container' ).remove();
			$clone.find( '.boldthemes_value' ).remove();
			$clone.find( 'img' ).remove();
			$clone.find( 'span' ).remove();
			$clone.find( 'textarea' ).remove();
			$clone.find( '.bt_bb_iconpicker_widget_placeholder' ).remove(); // just in case bt_bb_iconpicker is not initialized
			$clone.find( '.bt_bb_iconpicker' ).remove();

			$clone.append( '<input type="text" class="boldthemes_value">' );
		
			$clone.insertAfter( $clone_last );

			$input = $clone.find( ':input[class|="rwmb"]' );
			var $input1 = $clone.find( ':input' ).val( '' );
			
			$clone.find( '.boldthemes_key_filter' ).trigger( 'keyup' );

			name = $input.attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 ) {
				return '[' + ( parseInt( p1 ) + 1 ) + ']';
			});

			$input.attr( 'name', name );

			toggle_remove_buttons( $input );
			
			$input.trigger( 'clone' );
		}
		
		$( '.add-clone' ).on( 'click', function( e ) {
			
			//boldthemes_theme_custom_fields
			var id_boldthemestext1 = $( this ).closest( '.inside' ).find(".rwmb-boldthemestext1-clone").find(".rwmb-text:hidden").attr( 'id' );
			var res_custom_fields  = id_boldthemestext1 != null ? id_boldthemestext1.substring(0, 29) : '';
			
			//boldthemes_theme_override
			var id_boldthemestext = $( this ).closest( '.inside' ).find(".rwmb-boldthemestext-clone").find(".rwmb-text:hidden").attr( 'id' );
			var res_theme_override = id_boldthemestext != null ? id_boldthemestext.substring(0, 25) : '';

			if ( res_custom_fields == 'boldthemes_theme_custom_fields' || res_theme_override == 'boldthemes_theme_override' ){ 
				e.preventDefault();
				e.stopPropagation();
				var $input = $( this ).closest( '.rwmb-input' ),
				$clone_group = $( this ).closest( '.rwmb-field' ).attr( 'clone-group' );

				if ( $clone_group ) {
					var $metabox = $( this ).closest( '.inside' );
					var $clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );

					$.each( $clone_group_list.find( '.rwmb-input' ),
							function( key, value ) {
									add_cloned_fields( $( value ) );
					});
				} else {
					add_cloned_fields( $input );
				}

				toggle_remove_buttons( $input );
				
				return false;

			}

				
		});	

		$( '.rwmb-input' ).on( 'click', '.remove-clone', function() {
			var $this = $( this ),
				$input = $this.closest( '.rwmb-input' ),
				$clone_group = $( this ).closest( '.rwmb-field' ).attr( 'clone-group' );

			if ( $input.find( '.rwmb-clone' ).length == 1 ) {
				$input.find( '.add-clone' ).click();
			}

			if ( $clone_group ) {
				var $metabox = $( this ).closest( '.inside' );
				var $clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );
				var $index = $this.parent().index();

				$.each( $clone_group_list.find( '.rwmb-input' ),
					function( key, value ) {
						$( value ).children( '.rwmb-clone' ).eq( $index ).remove();
						toggle_remove_buttons( $( value ) );
					}
				);
			} else {
				$this.parent().remove();

				toggle_remove_buttons( $input );
			}

			return false;
		});	

		function toggle_remove_buttons( $el ) {
			var $button;
			if ( ! $el )
				$el = $( '.rwmb-field' );
			$el.each(function() {
				$button = $( this ).find( '.remove-clone' );
				$button.show();
			});
		}
		
		$( 'body' ).on( 'keyup change', 'input[type="text"].boldthemes_key_filter', function() {
			filter( $( this ).parent() );
		});
		
		$( 'body' ).on( 'focus', 'input[type="text"].boldthemes_key_filter', function() {
			$( '.boldthemes_key_select' ).removeAttr( 'size' );
			if ( $( this ).val() != '' ) {
				$( this ).parent().find( '.boldthemes_key_select' ).attr( 'size', 10 );
			}
		});
		
		function filter( container ) {
			var keyword = container.find( '.boldthemes_key_filter' ).val().toLowerCase().trim();
			var select = container.find( '.boldthemes_key_select' )[0];
			
			if ( keyword == '' ) {
				container.find( '.boldthemes_key_select' ).removeAttr( 'size' );
			} else {
				container.find( '.boldthemes_key_select' ).attr( 'size', 10 );
			}
			
			var section_match;
			
			for ( var i = 0; i < select.length; i++ ) {
				var el = select.options[i];
				var search_txt = el.text.toLowerCase();
				var search_txt = ( el.parentElement.label != null ) ? search_txt + ' ' + el.parentElement.label.toLowerCase() : search_txt;
				var words = keyword.split(' ');
				// var regs = words.map( i => '/\b' + i + '\b/');;
				var regs = words.map( i => '' + i + '');;
				var matches = regs.every( reg => search_txt.match( reg ) );
				/*console.log( regs );
				console.log( search_txt );
				console.log( matches );*/
				if ( matches ) {
					$( select.options[ i ] ).removeAttr( 'disabled' ).removeClass( 'boldthemes_key_select_hide_option' );
				} else {
					$( select.options[ i ] ).attr( 'disabled', 'disabled' ).addClass( 'boldthemes_key_select_hide_option' );
				}
			}
		}
		
		$( '.rwmb-boldthemestext-clone .bt_bb_iconpicker_widget_placeholder' ).each(function() {
			$( this ).replaceWith( window.bt_bb_iconpicker( false, $( this ).data( 'icon' ) ) );
			window.bt_bb_iconpicker_init( 'override' );
		});
		
	});
	
})(jQuery);