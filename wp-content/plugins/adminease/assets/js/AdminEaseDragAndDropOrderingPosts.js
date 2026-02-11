jQuery( document ).ready( function( $ ) {
	var AdminEaseDragAndDropPosts = {
		ajaxObj: AdminEaseDragDropOrderingPostsAjaxObj,
		init: function() {
			this.initSortable();
		},
		initSortable: function() {
			let postList = $( '.wp-list-table tbody' );
			
			if( !postList.length ) {
				return;
			}
			
			// Add orderable class to rows with drag handles
			postList.find( 'tr' ).each( function() {
				if( $( this ).find( '.drag-handle' ).length ) {
					$( this ).addClass( 'adminease-orderable' );
				}
			} );
			
			postList.sortable( {
				handle: '.drag-handle',
				axis: 'y',
				placeholder: 'ui-state-highlight',
				helper: AdminEaseDragAndDropPosts.createHelper,
				update: AdminEaseDragAndDropPosts.handleUpdate,
			} );
		},
		createHelper: function( e, tr ) {
			let originals = tr.children();
			let helper = tr.clone();
			
			helper.children().each( function( index ) {
				$( this ).width( originals.eq( index ).width() );
			} );
			
			return helper;
		},
		handleUpdate: function( event, ui ) {
			let elDragHandle = ui.item.find( '.drag-handle' );
			let isPostPage = elDragHandle.data( 'post-id' ) !== undefined;
			let ids = [];
			let action, security;
			
			if( isPostPage ) {
				action = 'adminease_update_post_order';
				security = AdminEaseDragAndDropPosts[ 'ajaxObj' ][ 'security' ][ 'postsNonce' ];
				
				$( '.wp-list-table tbody tr' ).each( function() {
					let postId = $( this ).find( '.drag-handle' ).data( 'post-id' );
					
					if( postId ) {
						ids.push( postId );
					}
				} );
			}
			
			// Show loading state
			elDragHandle.find( '.dashicons' ).removeClass( 'dashicons-move' ).addClass( 'dashicons-update' ).css( 'animation', 'rotation 1s infinite linear' );
			
			let data = {
				action: action,
				security: security,
			};
			
			if( isPostPage ) {
				data[ 'post_ids' ] = ids;
			}
			
			$.post( AdminEaseDragAndDropPosts[ 'ajaxObj' ][ 'ajaxUrl' ], data, function( response ) {
				// Restore icon
				elDragHandle.find( '.dashicons' ).removeClass( 'dashicons-update' ).addClass( 'dashicons-move' ).css( 'animation', '' );
				
				if( response[ 'success' ] ) {
					// Show success feedback
					elDragHandle.addClass( 'success-flash' );
					
					setTimeout(
						function() {
							elDragHandle.removeClass( 'success-flash' );
						},
						1000,
					);
				}
				else {
					// Show error feedback - response['data'] contains WP_Error object
					elDragHandle.addClass( 'error-flash' );
					
					setTimeout(
						function() {
							elDragHandle.removeClass( 'error-flash' );
						},
						2000,
					);
					
					if( response[ 'data' ] && response[ 'data' ][ 'message' ] ) {
						console.error( 'Drag and drop ordering error:', response[ 'data' ][ 'message' ] );
					}
				}
			} ).fail( function( xhr, status, error ) {
				// Restore icon and show error
				elDragHandle.find( '.dashicons' ).removeClass( 'dashicons-update' ).addClass( 'dashicons-move' ).css( 'animation', '' );
				
				// Show error feedback
				elDragHandle.addClass( 'error-flash' );
				
				setTimeout(
					function() {
						elDragHandle.removeClass( 'error-flash' );
					},
					2000,
				);
				
				console.error( 'Drag and drop ordering error:', error );
			} );
		},
	}
	
	AdminEaseDragAndDropPosts.init();
} );