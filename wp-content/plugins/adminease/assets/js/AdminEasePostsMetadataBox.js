jQuery( document ).ready( function( $ ) {
	var AdminEasePostsMetadataBox = {
		container: null, modal: null, ajaxObj: null, postId: null, currentEditKey: null, allMetadata: [], currentSort: 'asc', currentSearch: '',
		
		init: function() {
			this.container = $( '#adminease-posts-metadata-box-inner' );
			this.modal = $( '#adminease-metadata-modal' );
			
			if( !this.container.length ) {
				return;
			}
			
			this.ajaxObj = typeof AdminEasePostsMetadataBoxAjaxObj !== 'undefined' ? AdminEasePostsMetadataBoxAjaxObj : {};
			
			// Try multiple ways to get the post ID
			this.postId = this.container.attr( 'data-post-id' ) || this.container.data( 'postId' ) || this.container.data( 'post-id' );
			
			// Debug check - don't throw errors, just log them
			if( !this.ajaxObj.ajaxUrl ) {
				if( window.console && console.error ) {
					console.error( 'AdminEase: AJAX URL not found' );
				}
				return;
			}
			
			if( !this.postId ) {
				if( window.console && console.warn ) {
					console.warn( 'AdminEase: Post ID not found - metadata box will not be initialized' );
				}
				return;
			}
			
			this.initEvents();
			this.loadMetadata();
		},
		
		initEvents: function() {
			var self = this;
			
			// Add new metadata button
			$( document ).on( 'click', '#adminease-add-metadata', function( e ) {
				e.preventDefault();
				self.openModal( 'add' );
			} );
			
			// Edit metadata button
			$( document ).on( 'click', '.adminease-edit-metadata', function( e ) {
				e.preventDefault();
				var key = $( this ).data( 'key' );
				self.openModal( 'edit', key );
			} );
			
			// Delete metadata button
			$( document ).on( 'click', '.adminease-delete-metadata', function( e ) {
				e.preventDefault();
				var key = $( this ).data( 'key' );
				self.deleteMetadata( key );
			} );
			
			// Modal close buttons
			$( document ).on( 'click', '.adminease-modal-close, .adminease-modal-overlay', function( e ) {
				e.preventDefault();
				self.closeModal();
			} );
			
			// Modal form submit
			$( document ).on( 'submit', '#adminease-metadata-form', function( e ) {
				e.preventDefault();
				self.saveMetadata();
			} );
			
			// Save button click (add this)
			$( document ).on( 'click', '#adminease-save-metadata', function( e ) {
				e.preventDefault();
				self.saveMetadata();
			} );
			
			// Close modal on escape key
			$( document ).on( 'keydown', function( e ) {
				if( e.key === 'Escape' && self.modal.is( ':visible' ) ) {
					self.closeModal();
				}
			} );
			
			// Search metadata
			$( document ).on( 'input', '#adminease-metadata-search', function() {
				self.currentSearch = $( this ).val();
				self.filterAndRenderMetadata();
			} );
			
			// Sort metadata
			$( document ).on( 'change', '#adminease-metadata-sort', function() {
				self.currentSort = $( this ).val();
				self.filterAndRenderMetadata();
			} );
		},
		
		loadMetadata: function() {
			var self = this;
			var container = $( '#adminease-metadata-table-container' );
			
			container.html( '<div class="adminease-loading">' + self.ajaxObj.i18n.metadataLoading + '</div>' );
			
			var data = {
				action: 'adminease_get_post_metadata',
				post_id: self.postId,
				security: self.ajaxObj.security.getPostMetadata,
			};
			
			$.post( self.ajaxObj.ajaxUrl, data, function( response ) {
				if( response.success && response.data.metadata ) {
					self.allMetadata = response.data.metadata;
					self.filterAndRenderMetadata();
				}
				else {
					var errorMsg = response.data && response.data.message ? response.data.message : self.ajaxObj.i18n.metadataLoadError;
					container.html( '<div class="adminease-error">' + errorMsg + '</div>' );
				}
			} ).fail( function() {
				container.html( '<div class="adminease-error">' + self.ajaxObj.i18n.metadataLoadError + '</div>' );
			} );
		},
		
		filterAndRenderMetadata: function() {
			var self = this;
			var filtered = self.allMetadata.slice();
			
			// Apply search filter
			if( self.currentSearch ) {
				var searchLower = self.currentSearch.toLowerCase();
				filtered = filtered.filter( function( item ) {
					return item.key.toLowerCase().indexOf( searchLower ) !== -1 ||
						item.display_value.toLowerCase().indexOf( searchLower ) !== -1;
				} );
			}
			
			// Apply sorting
			filtered.sort( function( a, b ) {
				var keyA = a.key.toLowerCase();
				var keyB = b.key.toLowerCase();
				
				if( self.currentSort === 'asc' ) {
					return keyA < keyB ? -1 : keyA > keyB ? 1 : 0;
				}
				else {
					return keyA > keyB ? -1 : keyA < keyB ? 1 : 0;
				}
			} );
			
			self.renderMetadataTable( filtered );
		},
		
		renderMetadataTable: function( metadata ) {
			var self = this;
			var container = $( '#adminease-metadata-table-container' );
			
			if( !metadata || metadata.length === 0 ) {
				var message = self.currentSearch ?
					self.ajaxObj.i18n.metadataNoResults :
					self.ajaxObj.i18n.metadataNoMetadata;
				container.html( '<p class="adminease-no-metadata">' + message + '</p>' );
				return;
			}
			
			var html = '<table class="adminease-table adminease-metadata-table">';
			html += '<thead>';
			html += '<tr>';
			html += '<th style="width: 30%;">' + self.ajaxObj.i18n.metadataTableHeaderKey + '</th>';
			html += '<th style="width: 50%;">' + self.ajaxObj.i18n.metadataTableHeaderValue + '</th>';
			html += '<th style="width: 20%;">' + self.ajaxObj.i18n.metadataTableHeaderActions + '</th>';
			html += '</tr>';
			html += '</thead>';
			html += '<tbody>';
			
			metadata.forEach( function( item ) {
				var displayValue = self.escapeHtml( item.display_value );
				var truncatedValue = displayValue.length > 200 ? displayValue.substring( 0, 200 ) + '...' : displayValue;
				var keyClass = item.is_protected ? 'adminease-meta-protected' : '';
				var encodedValue = self.escapeHtml( item.value );
				var isImageSize = item.is_image_size || false;
				var isReadonly = item.is_readonly || false;
				
				// Add image size class if applicable
				if( isImageSize ) {
					keyClass += ' adminease-meta-image-size';
				}
				
				html += '<tr>';
				html += '<td class="' + keyClass + '"><code>' + self.escapeHtml( item.key ) + '</code>';
				if( item.is_protected ) {
					html += ' <span class="dashicons dashicons-lock" title="' + self.ajaxObj.i18n.metadataProtectedKey + '"></span>';
				}
				if( isImageSize ) {
					html += ' <span class="dashicons dashicons-format-image" style="color: #2271b1;" title="' + self.ajaxObj.i18n.metadataImageSize + '"></span>';
				}
				html += '</td>';
				
				// For image sizes, make the value a clickable link
				if( isImageSize ) {
					html += '<td class="adminease-meta-image-url">';
					html += '<a href="' + self.escapeHtml( item.value ) + '" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">';
					html += '<span class="dashicons dashicons-external" style="vertical-align: middle;"></span> ';
					html += truncatedValue;
					html += '</a>';
					html += '</td>';
				}
				else {
					html += '<td><pre style="min-height: 16px; overflow: auto; margin: 0; padding: 3px 6px; background: #f5f5f5; border: 1px solid #ddd;">' + truncatedValue + '</pre></td>';
				}
				
				html += '<td>';
				
				// Don't show edit/delete buttons for readonly image size metadata
				if( isReadonly ) {
					html += '<span style="color: #999; font-style: italic;">Read-only</span>';
				}
				else {
					html += '<button type="button" class="button button-small button-secondary adminease-edit-metadata" data-key="' + self.escapeHtml( item.key ) + '" title="' + self.ajaxObj.i18n.metadataEdit + '">';
					html += '<span class="dashicons dashicons-edit"></span> ' + self.ajaxObj.i18n.metadataEdit;
					html += '</button> ';
					html += '<button type="button" class="button button-small button-secondary adminease-delete-metadata" data-key="' + self.escapeHtml( item.key ) + '" title="' + self.ajaxObj.i18n.metadataDelete + '">';
					html += '<span class="dashicons dashicons-trash"></span> ' + self.ajaxObj.i18n.metadataDelete;
					html += '</button>';
				}
				
				html += '</td>';
				html += '</tr>';
			} );
			
			html += '</tbody>';
			html += '</table>';
			
			container.html( html );
		},
		
		openModal: function( mode, key ) {
			var self = this;
			
			mode = mode || 'add';
			self.currentEditKey = mode === 'edit' ? key : null;
			
			// Set modal title
			var title = mode === 'edit' ? self.ajaxObj.i18n.metadataModalTitleEdit : self.ajaxObj.i18n.metadataModalTitleAdd;
			$( '#adminease-modal-title' ).text( title );
			
			// Clear form and errors
			var form = $( '#adminease-metadata-form' );
			if( form.length && form[ 0 ] ) {
				form[ 0 ].reset();
			}
			$( '.adminease-modal-errors' ).empty();
			
			// Populate form if editing
			if( mode === 'edit' && key ) {
				$( '#adminease-meta-key' ).val( key );
				$( '#adminease-original-key' ).val( key );
				
				// Find the metadata item by key
				var metadataItem = self.allMetadata.find( function( item ) {
					return item.key === key;
				} );
				
				if( metadataItem ) {
					// Use display_value which is already formatted (JSON pretty-printed or string)
					var displayValue = metadataItem.display_value;
					
					$( '#adminease-meta-value' ).val( displayValue );
				}
			}
			else {
				$( '#adminease-meta-key' ).val( '' );
				$( '#adminease-meta-value' ).val( '' );
				$( '#adminease-original-key' ).val( '' );
			}
			
			// Show modal
			self.modal.fadeIn( 200 );
			$( 'body' ).addClass( 'adminease-modal-open' );
			$( '#adminease-meta-key' ).focus();
		},
		
		closeModal: function() {
			this.modal.fadeOut( 200 );
			$( 'body' ).removeClass( 'adminease-modal-open' );
			this.currentEditKey = null;
		},
		
		saveMetadata: function() {
			var self = this;
			var submitButton = $( '#adminease-save-metadata' );
			var errorsContainer = $( '.adminease-modal-errors' );
			
			// Clear previous errors
			errorsContainer.empty();
			
			// Get form data
			var metaKey = $( '#adminease-meta-key' ).val().trim();
			var metaValue = $( '#adminease-meta-value' ).val();
			var originalKey = $( '#adminease-original-key' ).val();
			
			// Validate
			if( !metaKey ) {
				errorsContainer.html( '<div class="alert alert-danger">' + self.ajaxObj.i18n.metadataKeyRequired + '</div>' );
				return;
			}
			
			// Check if value is valid JSON (if it looks like JSON)
			if( metaValue.trim().match( /^[\{\[]/ ) ) {
				try {
					JSON.parse( metaValue );
				}
				catch( e ) {
					errorsContainer.html( '<div class="alert alert-danger">' + self.ajaxObj.i18n.metadataInvalidJSON + '</div>' );
					return;
				}
			}
			
			// Disable submit button and show loading
			submitButton.prop( 'disabled', true ).addClass( 'loading' );
			
			var data = {
				action: 'adminease_update_post_metadata', post_id: self.postId, meta_key: metaKey, meta_value: metaValue, original_key: originalKey, security: self.ajaxObj.security.updatePostMetadata,
			};
			
			$.post( self.ajaxObj.ajaxUrl, data, function( response ) {
				submitButton.prop( 'disabled', false ).removeClass( 'loading' );
				
				if( response.success ) {
					self.closeModal();
					self.loadMetadata();
					self.showNotice( 'success', self.ajaxObj.i18n.metadataSaveSuccess );
				}
				else {
					var errorMessage = response.data && response.data.message ? response.data.message : self.ajaxObj.i18n.metadataSaveError;
					errorsContainer.html( '<div class="alert alert-danger">' + errorMessage + '</div>' );
				}
			} ).fail( function() {
				submitButton.prop( 'disabled', false ).removeClass( 'loading' );
				errorsContainer.html( '<div class="alert alert-danger">' + self.ajaxObj.i18n.metadataSaveError + '</div>' );
			} );
		},
		
		deleteMetadata: function( key ) {
			var self = this;
			
			if( !confirm( self.ajaxObj.i18n.metadataConfirmDelete ) ) {
				return;
			}
			
			var data = {
				action: 'adminease_delete_post_metadata', post_id: self.postId, meta_key: key, security: self.ajaxObj.security.deletePostMetadata,
			};
			
			$.post( self.ajaxObj.ajaxUrl, data, function( response ) {
				if( response.success ) {
					self.loadMetadata();
					self.showNotice( 'success', self.ajaxObj.i18n.metadataDeleteSuccess );
				}
				else {
					var errorMessage = response.data && response.data.message ? response.data.message : self.ajaxObj.i18n.metadataDeleteError;
					self.showNotice( 'error', errorMessage );
				}
			} ).fail( function() {
				self.showNotice( 'error', self.ajaxObj.i18n.metadataDeleteError );
			} );
		},
		
		showNotice: function( type, message ) {
			var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			var notice = $( '<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>' );
			
			$( '.wrap h1' ).first().after( notice );
			
			setTimeout( function() {
				notice.fadeOut( function() {
					$( this ).remove();
				} );
			}, 3000 );
		},
		
		escapeHtml: function( text ) {
			if( typeof text !== 'string' ) {
				text = String( text );
			}
			var div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		},
	};
	
	AdminEasePostsMetadataBox.init();
} );