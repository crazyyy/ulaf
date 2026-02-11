jQuery( document ).ready( function( $ ) {
	var AdminEaseBulkDeletePosts = {
		ajaxObj: AdminEaseBulkDeletePostsAjaxObj,
		previewButton: null,
		startButton: null,
		previewResults: null,
		listContainer: null,
		list: null,
		completeContainer: null,
		progressBar: null,
		isProcessing: false,
		totalPosts: 0,
		processedPosts: 0,
		currentPage: 1,
		init: function() {
			this.initElements();
			this.initEvents();
		},
		initElements: function() {
			this.previewButton = $( '#bulk-delete-posts-submit' );
			this.startButton = $( '.adminease-bulk-delete-start' );
			this.previewResults = $( '.adminease-bulk-delete-preview-results' );
			this.listContainer = $( '.adminease-bulk-delete-list-container' );
			this.list = $( '.adminease-bulk-delete-list' );
			this.completeContainer = $( '.adminease-bulk-delete-complete' );
			
			// Initialize progress bar component from global AdminEaseProgressBar
			if( typeof AdminEaseProgressBar !== 'undefined' ) {
				AdminEaseProgressBar.init( '.adminease-progress-bar-container' );
				
				this.progressBar = AdminEaseProgressBar;
			}
		},
		initEvents: function() {
			this.previewButton.on( 'click', $.proxy( this.handlePreview, this ) );
			this.startButton.on( 'click', $.proxy( this.handleStart, this ) );
		},
		getCriteria: function() {
			return {
				post_type: $( '#bulk-delete-posts-post-types' ).val(),
				post_status: $( '#bulk-delete-posts-post-status' ).val(),
				date_from: $( '#bulk-delete-posts-date-range_from' ).val(),
				date_to: $( '#bulk-delete-posts-date-range_to' ).val(),
			};
		},
		handlePreview: function( e ) {
			e.preventDefault();
			
			if( this.isProcessing ) {
				return;
			}
			
			let criteria = this.getCriteria();
			
			if( !criteria.post_type ) {
				alert( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'error' ] );
				return;
			}
			
			// Store original button text
			let originalText = this.previewButton.text();
			
			this.previewButton.prop( 'disabled', true ).text( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'processing' ] );
			this.startButton.prop( 'disabled', true );
			this.previewResults.hide();
			this.listContainer.hide();
			this.completeContainer.hide();
			
			let data = {
				action: 'adminease_bulk_delete_preview',
				security: AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'security' ],
				post_type: criteria.post_type,
				post_status: criteria.post_status,
				date_from: criteria.date_from,
				date_to: criteria.date_to,
			};
			
			$.post( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'ajaxUrl' ], data, $.proxy( function( response ) {
				if( response[ 'success' ] ) {
					this.totalPosts = response[ 'data' ][ 'total' ];
					this.displayPreviewResults( response[ 'data' ] );
				}
				else {
					alert( response[ 'data' ][ 'message' ] || AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'error' ] );
					
					this.startButton.prop( 'disabled', true );
				}
			}, this ) ).fail( $.proxy( function() {
				alert( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'error' ] );
				
				this.startButton.prop( 'disabled', true );
			}, this ) ).always( $.proxy( function() {
				this.previewButton.prop( 'disabled', false ).text( originalText );
			}, this ) );
		},
		displayPreviewResults: function( data ) {
			if( data[ 'total' ] === 0 ) {
				this.previewResults.find( '.adminease-preview-text' ).text( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'noPostsFound' ] );
				this.previewResults.show();
				this.startButton.prop( 'disabled', true );
				return;
			}
			
			let breakdownText = '';
			
			if( data[ 'breakdown' ][ 'post' ] ) {
				breakdownText += data[ 'breakdown' ][ 'post' ] + ' ' + AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'post' ].toLowerCase() + 's';
			}
			if( data[ 'breakdown' ][ 'page' ] ) {
				if( breakdownText ) {
					breakdownText += ', ';
				}
				
				breakdownText += data[ 'breakdown' ][ 'page' ] + ' ' + AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'page' ].toLowerCase() + 's';
			}
			
			let message = AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'foundPosts' ].replace( '%d', data[ 'total' ] );
			
			if( breakdownText ) {
				message += ' (' + breakdownText + ')';
			}
			
			this.previewResults.find( '.adminease-preview-text' ).text( message );
			this.previewResults.show();
			this.startButton.prop( 'disabled', false );
		},
		handleStart: function( e ) {
			e.preventDefault();
			
			if( this.isProcessing || this.totalPosts === 0 ) {
				return;
			}
			
			// Confirmation dialog based on deletion method
			let deletionMethod = $( '#bulk-delete-posts-deletion-method' ).val();
			let confirmMessage = deletionMethod === 'permanent'
				? AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'confirmPermanent' ]
				: AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'confirmMessage' ];
			
			if( !confirm( confirmMessage ) ) {
				return;
			}
			
			this.startDeletion();
		},
		startDeletion: function() {
			this.isProcessing = true;
			this.processedPosts = 0;
			this.currentPage = 1;
			
			// Reset UI
			this.startButton.prop( 'disabled', true ).text( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'processing' ] );
			this.previewButton.prop( 'disabled', true );
			this.list.empty();
			this.listContainer.show();
			this.progressBar.reset();
			this.progressBar.show();
			this.completeContainer.hide();
			
			// Disable all filter inputs
			this.toggleInputs( true );
			
			this.processBatch();
		},
		processBatch: function() {
			let criteria = this.getCriteria();
			let deletionMethod = $( '#bulk-delete-posts-deletion-method' ).val();
			let batchSize = $( '#bulk-delete-posts-batch-size' ).val();
			
			let data = {
				action: 'adminease_bulk_delete_batch',
				security: AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'security' ],
				post_type: criteria.post_type,
				post_status: criteria.post_status,
				date_from: criteria.date_from,
				date_to: criteria.date_to,
				page: this.currentPage,
				batch_size: batchSize,
				deletion_method: deletionMethod,
			};
			
			$.post( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'ajaxUrl' ], data, $.proxy( function( response ) {
				if( response[ 'success' ] ) {
					this.handleBatchSuccess( response[ 'data' ] );
				}
				else {
					this.handleError( response[ 'data' ][ 'message' ] );
				}
			}, this ) ).fail( $.proxy( function() {
				this.handleError( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'error' ] );
			}, this ) );
		},
		handleBatchSuccess: function( data ) {
			// Update progress
			this.processedPosts += data[ 'processed' ];
			this.progressBar.update( this.processedPosts, this.totalPosts );
			
			// Append deleted items to list
			if( data[ 'deleted' ] && data[ 'deleted' ].length > 0 ) {
				data[ 'deleted' ].forEach( $.proxy( function( item ) {
					let typeLabel = item[ 'type' ] === 'post' ? AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'post' ] : AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'page' ];
					let listItem = '• ' + typeLabel + ' #' + item[ 'id' ] + ': "' + this.escapeHtml( item[ 'title' ] ) + '"<br>';
					this.list.append( listItem );
				}, this ) );
				
				// Auto-scroll to bottom
				this.list.scrollTop( this.list[ 0 ].scrollHeight );
			}
			
			// Check if more batches are needed
			if( data[ 'total_remaining' ] > 0 ) {
				this.currentPage++;
				this.processBatch();
			}
			else {
				this.completeDeletion();
			}
		},
		completeDeletion: function() {
			this.isProcessing = false;
			this.progressBar.hide();
			
			// Count by type for summary
			let listText = this.list.text();
			let postCount = ( listText.match( /• Post/g ) || [] ).length;
			let pageCount = ( listText.match( /• Page/g ) || [] ).length;
			
			let summaryText = AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'complete' ] + ' ';
			
			summaryText += AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'totalDeleted' ].replace( '%d', this.totalPosts );
			
			if( postCount > 0 || pageCount > 0 ) {
				summaryText += ' (' + AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'postsSummary' ].replace( '%1$d', postCount ).replace( '%2$d', pageCount ) + ')';
			}
			
			this.completeContainer.find( '.adminease-complete-message' ).text( summaryText );
			this.completeContainer.show();
			
			// Re-enable buttons and inputs
			this.startButton.prop( 'disabled', false ).text( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'startButton' ] );
			this.previewButton.prop( 'disabled', false );
			this.toggleInputs( false );
		},
		handleError: function( message ) {
			this.isProcessing = false;
			this.progressBar.hide();
			
			alert( message || AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'error' ] );
			
			// Re-enable buttons and inputs
			this.startButton.prop( 'disabled', false ).text( AdminEaseBulkDeletePosts[ 'ajaxObj' ][ 'i18n' ][ 'startButton' ] );
			this.previewButton.prop( 'disabled', false );
			this.toggleInputs( false );
		},
		toggleInputs: function( disabled ) {
			$( '#bulk-delete-posts-post-types' ).prop( 'disabled', disabled );
			$( '#bulk-delete-posts-post-status' ).prop( 'disabled', disabled );
			$( '#bulk-delete-posts-date-range_from' ).prop( 'disabled', disabled );
			$( '#bulk-delete-posts-date-range_to' ).prop( 'disabled', disabled );
			$( '#bulk-delete-posts-deletion-method' ).prop( 'disabled', disabled );
			$( '#bulk-delete-posts-batch-size' ).prop( 'disabled', disabled );
		},
		escapeHtml: function( text ) {
			let map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;',
			};
			return text.replace( /[&<>"']/g, function( m ) {
				return map[ m ];
			} );
		},
	};
	
	// Only initialize if the elements exist on the page
	if( $( '#bulk-delete-posts-submit' ).length ) {
		AdminEaseBulkDeletePosts.init();
	}
} );