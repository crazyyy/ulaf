jQuery( document ).ready( function( $ ) {
	var AdminEaseNetworkViewer = {
		ADMINEASE_SELECTOR: $( '.adminease' ),
		currentPage: 1,
		totalPages: 1,
		autoRefreshInterval: null,
		isAutoRefreshEnabled: false,
		init: function() {
			AdminEaseNetworkViewer.bindEvents();
			
			if( AdminEaseNetworkViewerAjaxObj[ 'autoLoad' ] ) {
				AdminEaseNetworkViewer.refreshConnections();
			}
		},
		bindEvents: function() {
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '#refresh-network-viewer', AdminEaseNetworkViewer.handleRefreshClick );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '#clear-network-viewer', AdminEaseNetworkViewer.handleClearClick );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'change', '#auto-refresh-toggle-network-viewer', AdminEaseNetworkViewer.handleAutoRefreshToggleChange );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '.network-viewer-table .view-details', AdminEaseNetworkViewer.showDetailsModal );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '.adminease-modal-close, .adminease-modal-overlay', AdminEaseNetworkViewer.closeDetailsModal );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '#apply-filters', AdminEaseNetworkViewer.handleFilterChange );
			AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].on( 'click', '#prev-page, #next-page', AdminEaseNetworkViewer.handlePageChange );
		},
		handleRefreshClick: function( e ) {
			e.preventDefault();
			
			AdminEaseNetworkViewer.currentPage = 1;
			AdminEaseNetworkViewer.refreshConnections( e );
		},
		handleClearClick: function( e ) {
			e.preventDefault();
			
			if( !confirm( AdminEaseNetworkViewerAjaxObj [ 'i18n' ][ 'confirmClearNetworkViewerLog' ] ) ) {
				return;
			}
			
			AdminEaseNetworkViewer.clearConnections( e );
		},
		handleAutoRefreshToggleChange: function( e ) {
			AdminEaseNetworkViewer.isAutoRefreshEnabled = $( e.currentTarget ).is( ':checked' );
			
			if( AdminEaseNetworkViewer.isAutoRefreshEnabled ) {
				AdminEaseNetworkViewer.startAutoRefresh();
			}
			else {
				AdminEaseNetworkViewer.stopAutoRefresh();
			}
		},
		showDetailsModal: function( e ) {
			e.preventDefault();
			
			let connections = AdminEaseNetworkViewer.getConnectionFromButton( $( e.currentTarget ) );
			
			AdminEaseNetworkViewer.populateDetailsModal( connections );
			
			$( '#connection-details-modal' ).fadeIn( 200 );
			$( 'body' ).addClass( 'adminease-modal-open' );
		},
		closeDetailsModal: function( e ) {
			if( e ) {
				e.preventDefault();
				e.stopPropagation();
			}
			
			$( '#connection-details-modal' ).fadeOut( 200 );
			$( 'body' ).removeClass( 'adminease-modal-open' );
		},
		handleFilterChange: function( e ) {
			AdminEaseNetworkViewer.currentPage = 1;
			AdminEaseNetworkViewer.refreshConnections( e );
		},
		handlePageChange: function( e ) {
			e.preventDefault();
			
			const direction = e.currentTarget.id === 'prev-page' ? 'prev' : 'next';
			
			if( direction === 'prev' && AdminEaseNetworkViewer.currentPage > 1 ) {
				AdminEaseNetworkViewer.currentPage--;
				AdminEaseNetworkViewer.refreshConnections( e );
			}
			else if( direction === 'next' && AdminEaseNetworkViewer.currentPage < AdminEaseNetworkViewer.totalPages ) {
				AdminEaseNetworkViewer.currentPage++;
				AdminEaseNetworkViewer.refreshConnections( e );
			}
		},
		getConnectionFromButton: function( elButton ) {
			if( !elButton || !elButton.length ) {
				return {};
			}
			
			return {
				id: elButton.attr( 'id' ) || '',
				formatted_time: elButton.attr( 'data-formatted-time' ) || elButton.attr( 'data-timestamp' ) || '-',
				timestamp: elButton.attr( 'data-timestamp' ) || '',
				request_method: ( elButton.attr( 'data-request-method' ) || '' ).toUpperCase(),
				request_type: elButton.attr( 'data-request-type' ) || '-',
				protocol: elButton.attr( 'data-protocol' ) || '-',
				port: elButton.attr( 'data-port' ) || '-',
				request_uri: elButton.attr( 'data-request-uri' ) || '-',
				query_string: elButton.attr( 'data-query-string' ) || '-',
				ip_address: elButton.attr( 'data-ip-address' ) || '-',
				hostname: elButton.attr( 'data-hostname' ) || '-',
				country: elButton.attr( 'data-country' ) || '-',
				country_code: elButton.attr( 'data-country-code' ) || '',
				browser: elButton.attr( 'data-browser' ) || '-',
				browser_icon_file: elButton.attr( 'data-browser-icon-file' ) || '',
				device: elButton.attr( 'data-device' ) || '-',
				device_icon_file: elButton.attr( 'data-device-icon-file' ) || '',
				user_agent: elButton.attr( 'data-user_agent' ) || '-',
				response_code: elButton.attr( 'data-response-code' ) || '',
				response_time: elButton.attr( 'data-response-time' ) || '',
				request_size: elButton.attr( 'data-request-size' ) || '',
				referer: elButton.attr( 'data-referer' ) || '-',
				user_id: elButton.attr( 'data-user-id' ) || '',
				user_role: elButton.attr( 'data-user-role' ) || '-',
				session_id: elButton.attr( 'data-session-id' ) || '-',
				username_text: elButton.attr( 'data-username-text' ) || '-',
				user_edit_url: elButton.attr( 'data-user-edit-url' ) || '',
			};
		},
		populateDetailsModal: function( connection ) {
			let elModal = $( '#connection-details-modal' );
			
			// Basic text fields (safe: uses text()).
			elModal.find( '[data-field="formatted_time"]' ).text( connection[ 'formatted_time' ] || '-' );
			elModal.find( '[data-field="request_type"]' ).text( connection[ 'request_type' ] || '-' );
			elModal.find( '[data-field="protocol"]' ).text( connection[ 'protocol' ] || '-' );
			elModal.find( '[data-field="port"]' ).text( connection[ 'port' ] || '-' );
			elModal.find( '[data-field="request_uri"]' ).text( connection[ 'request_uri' ] || '-' );
			elModal.find( '[data-field="query_string"]' ).text( connection[ 'query_string' ] || '-' );
			elModal.find( '[data-field="ip_address"]' ).text( connection[ 'ip_address' ] || '-' );
			elModal.find( '[data-field="hostname"]' ).text( connection[ 'hostname' ] || '-' );
			elModal.find( '[data-field="country"]' ).text( connection[ 'country' ] || '-' );
			elModal.find( '[data-field="browser"]' ).text( connection[ 'browser' ] || '-' );
			elModal.find( '[data-field="device"]' ).text( connection[ 'device' ] || '-' );
			elModal.find( '[data-field="user_agent"]' ).text( connection[ 'user_agent' ] || '-' );
			elModal.find( '[data-field="user_role"]' ).text( connection[ 'user_role' ] || '-' );
			elModal.find( '[data-field="session_id"]' ).text( connection[ 'session_id' ] || '-' );
			
			// Method badge (text + class only).
			elModal.find( '[data-field="request_method_badge"]' )
				.removeClass()
				.addClass( 'badge method-badge ' + AdminEaseNetworkViewer.getMethodClass( connection[ 'request_method' ] ) )
				.text( connection[ 'request_method' ] || '-' );
			
			// Status badge (text + class only).
			elModal.find( '[data-field="response_code_badge"]' )
				.removeClass()
				.addClass( 'badge badge-' + AdminEaseNetworkViewer.getStatusClass( parseInt( connection[ 'response_code' ] || '0', 10 ) ) )
				.text( connection[ 'response_code' ] || '-' );
			
			// Response time / request size formatting
			elModal.find( '[data-field="response_time"]' ).text( connection[ 'response_time' ] ? connection[ 'response_time' ] + ' ms' : '-' );
			elModal.find( '[data-field="request_size"]' ).text( connection[ 'request_size' ] ? AdminEaseNetworkViewer.formatBytes( connection[ 'request_size' ] ) : '-' );
			elModal.find( '[data-field="referer"]' ).text( connection[ 'referer' ] || '-' );
			
			// Country flag
			let elFlag = elModal.find( '[data-field="country_flag"]' );
			
			if( connection[ 'country_code' ] && 2 === connection[ 'country_code' ].length ) {
				elFlag
					.attr( 'src', 'https://flagcdn.com/' + connection[ 'country_code' ].toLowerCase() + '.svg' )
					.show();
			}
			else {
				elFlag.hide().removeAttr( 'src' );
			}
			
			// Browser icon (from localized pluginUrl + filename)
			let elBrowserIcon = elModal.find( '[data-field="browser_icon"]' );
			
			if( AdminEaseNetworkViewerAjaxObj[ 'pluginUrl' ] && connection[ 'browser_icon_file' ] ) {
				elBrowserIcon
					.attr( 'src', AdminEaseNetworkViewerAjaxObj[ 'pluginUrl' ] + 'assets/img/browsers/' + connection[ 'browser_icon_file' ] )
					.show();
			}
			else {
				elBrowserIcon.hide().removeAttr( 'src' );
			}
			
			// Device icon
			let elDeviceIcon = elModal.find( '[data-field="device_icon"]' );
			
			if( AdminEaseNetworkViewerAjaxObj[ 'pluginUrl' ] && connection[ 'device_icon_file' ] ) {
				elDeviceIcon
					.attr( 'src', AdminEaseNetworkViewerAjaxObj[ 'pluginUrl' ] + 'assets/img/devices/' + connection[ 'device_icon_file' ] )
					.show();
			}
			else {
				elDeviceIcon.hide().removeAttr( 'src' );
			}
			
			// User link (optional)
			let elUserLink = elModal.find( '[data-field="user_link"]' );
			let elUserText = elModal.find( '[data-field="username_text"]' );
			
			if( connection[ 'user_edit_url' ] ) {
				elUserLink
					.attr( 'href', connection[ 'user_edit_url' ] )
					.text( connection[ 'username_text' ] || '-' )
					.show();
				
				elUserText.text( '' );
			}
			else {
				elUserLink.hide().attr( 'href', '#' ).text( '' );
				elUserText.text( connection[ 'username_text' ] || '-' );
			}
		},
		formatBytes: function( bytes ) {
			if( !bytes || isNaN( bytes ) || bytes === '' ) {
				return '-';
			}
			
			bytes = parseInt( bytes, 10 );
			
			if( bytes === 0 ) {
				return '0 Bytes';
			}
			
			const k = 1024;
			const sizes = ['Bytes', 'KB', 'MB', 'GB'];
			const i = Math.floor( Math.log( bytes ) / Math.log( k ) );
			
			return Math.round( bytes / Math.pow( k, i ) * 100 ) / 100 + ' ' + sizes[ i ];
		},
		startAutoRefresh: function() {
			AdminEaseNetworkViewer.stopAutoRefresh();
			
			const intervalSeconds = parseInt( $( '.auto-refresh-toggle' ).data( 'interval' ), 10 ) || 10;
			
			AdminEaseNetworkViewer[ 'autoRefreshInterval' ] = setInterval(
				function() {
					AdminEaseNetworkViewer.refreshConnections();
				},
				intervalSeconds * 1000,
			);
		},
		stopAutoRefresh: function() {
			if( AdminEaseNetworkViewer[ 'autoRefreshInterval' ] ) {
				clearInterval( AdminEaseNetworkViewer[ 'autoRefreshInterval' ] );
				
				AdminEaseNetworkViewer[ 'autoRefreshInterval' ] = null;
			}
		},
		refreshConnections: function( e ) {
			AdminEaseNetworkViewer.showLoading( e );
			
			let data = {
				action: 'adminease_get_network_viewer_log',
				security: AdminEaseNetworkViewerAjaxObj[ 'security' ][ 'refreshNetworkViewerLog' ],
				page: AdminEaseNetworkViewer[ 'currentPage' ] || 1,
				per_page: parseInt( $( '#filter-per-page' ).val(), 10 ) || 50,
				method: $( '#filter-method' ).val() || '',
				ip: $( '#filter-ip' ).val() || '',
			};
			
			$.ajax( {
				url: AdminEaseNetworkViewerAjaxObj[ 'ajaxUrl' ],
				type: 'POST',
				dataType: 'json',
				traditional: true,
				data: data,
				success: ( response ) => {
					$( 'body' ).trigger( 'adminease_network_viewer_refreshed', [response] );
					
					AdminEaseNetworkViewer.hideLoading( e );
					
					if( response[ 'success' ] ) {
						AdminEaseNetworkViewer.updateTable( response[ 'data' ] );
						
						setTimeout(
							function() {
								$( '.network-viewer-table-wrapper' ).scrollTop( 0 );
							},
							100,
						);
					}
					else {
						AdminEaseNetworkViewer.showError( response[ 'data' ] || AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'networkViewerRefreshError' ] );
					}
				},
				error: ( xhr, status, error ) => {
					console.error( 'AJAX Error:', xhr.responseText );
					
					AdminEaseNetworkViewer.hideLoading( e );
					AdminEaseNetworkViewer.showError( AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'networkViewerRefreshError' ] + ': ' + error );
				},
			} );
		},
		clearConnections: function( e ) {
			AdminEaseNetworkViewer.showLoading();
			
			$.ajax( {
				url: AdminEaseNetworkViewerAjaxObj[ 'ajaxUrl' ],
				type: 'POST',
				data: {
					action: 'adminease_clear_network_viewer_log',
					security: AdminEaseNetworkViewerAjaxObj[ 'security' ][ 'clearNetworkViewerLog' ],
				},
				success: ( response ) => {
					AdminEaseNetworkViewer.hideLoading( e );
					
					if( response[ 'success' ] ) {
						AdminEaseNetworkViewer[ 'currentPage' ] = 1;
						AdminEaseNetworkViewer.refreshConnections();
						AdminEaseNetworkViewer.showSuccess( AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'networkViewerLogCleared' ] );
					}
					else {
						AdminEaseNetworkViewer.showError( response[ 'data' ] || AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'networkViewerLoadLogFailed' ] );
					}
				},
				error: ( xhr, status, error ) => {
					AdminEaseNetworkViewer.hideLoading( e );
					AdminEaseNetworkViewer.showError( 'Network error: ' + error );
				},
			} );
		},
		updateTable: function( data ) {
			AdminEaseNetworkViewer.hideEmptyState();
			
			// Detach existing modal (if any) before replacing HTML.
			const elExistingModal = $( '#connection-details-modal' ).length ? $( '#connection-details-modal' ).detach() : $();
			
			let elNetworkViewerTableWrapper = $( '.network-viewer-table-wrapper' );
			
			elNetworkViewerTableWrapper.html( data[ 'table_html' ] );
			
			// Detach modal from the newly injected HTML (if present).
			let elNewModal = elNetworkViewerTableWrapper.find( '#connection-details-modal' ).detach();
			
			// Prefer the newly rendered modal; otherwise reuse the previously detached one.
			const elModalToUse = elNewModal.length ? elNewModal : elExistingModal;
			
			if( elModalToUse.length ) {
				// Ensure only one modal exists in the DOM.
				$( '#connection-details-modal' ).remove();
				
				// Keep it under .adminease so CSS and z-index rules still apply.
				AdminEaseNetworkViewer[ 'ADMINEASE_SELECTOR' ].append( elModalToUse );
			}
			
			AdminEaseNetworkViewer.updatePagination( data[ 'total' ], data[ 'page' ], data[ 'per_page' ] );
			AdminEaseNetworkViewer.updateConnectionCount( data[ 'total' ], data[ 'page' ], data[ 'per_page' ] );
		},
		getMethodClass: function( method ) {
			const classes = {
				'GET': 'primary',
				'POST': 'primary',
				'PUT': 'primary',
				'DELETE': 'primary',
				'PATCH': 'default',
				'HEAD': 'default',
			};
			
			return classes[ method ] || 'method-other';
		},
		getStatusClass: function( status ) {
			if( !status ) {
				return '';
			}
			
			if( status >= 200 && status < 300 ) {
				return 'success';
			}
			
			if( status >= 300 && status < 400 ) {
				return 'redirect';
			}
			
			if( status >= 400 && status < 500 ) {
				return 'client-error';
			}
			
			if( status >= 500 ) {
				return 'server-error';
			}
			
			return '';
		},
		updatePagination: function( total, page, perPage ) {
			AdminEaseNetworkViewer[ 'currentPage' ] = page;
			AdminEaseNetworkViewer[ 'totalPages' ] = Math.ceil( total / perPage );
			
			const paginationEl = $( '#network-pagination' );
			
			if( AdminEaseNetworkViewer[ 'totalPages' ] <= 1 ) {
				paginationEl.hide();
				
				return;
			}
			
			paginationEl.show();
			
			$( '#prev-page' ).prop( 'disabled', page <= 1 );
			$( '#next-page' ).prop( 'disabled', page >= AdminEaseNetworkViewer[ 'totalPages' ] );
			
			const paginationText = AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'paginationInfo' ].replace( '%1$s', page ).replace( '%2$s', AdminEaseNetworkViewer[ 'totalPages' ] );
			
			$( '#pagination-info' ).text( paginationText );
		},
		updateConnectionCount: function( total, page, perPage ) {
			const start = ( ( page - 1 ) * perPage ) + 1;
			const end = Math.min( page * perPage, total );
			let elNetworkConnectionCount = $( '#network-connection-count' );
			
			if( 0 === total ) {
				elNetworkConnectionCount.text( '' );
				
				return;
			}
			
			const connectionText = AdminEaseNetworkViewerAjaxObj[ 'i18n' ][ 'connectionCount' ]
				.replace( '%1$s', start )
				.replace( '%2$s', end )
				.replace( '%3$s', total );
			
			elNetworkConnectionCount.text( connectionText );
		},
		showLoading: function( e ) {
			if( e ) {
				$( e.currentTarget ).attr( 'disabled', 'disabled' ).addClass( 'loading' );
			}
			
			$( '.adminease .network-viewer-table' ).css( 'opacity', '0.5' );
		},
		hideLoading: function( e ) {
			if( e ) {
				$( e.currentTarget ).removeAttr( 'disabled' ).removeClass( 'loading' );
			}
			
			$( '.adminease .network-viewer-table' ).css( 'opacity', '1' );
		},
		showEmptyState: function() {
			$( '#network-viewer-table' ).hide();
			$( '#network-pagination' ).hide();
			$( '#network-connection-count' ).text( '' );
			$( '#network-empty-state' ).show();
		},
		hideEmptyState: function() {
			$( '#network-empty-state' ).hide();
			$( '#network-viewer-table' ).show();
		},
		showError: function( message ) {
			$( '#network-status' ).text( message );
		},
		showSuccess: function( message ) {
			$( '#network-status' ).text( message );
			
			setTimeout(
				function() {
					$( '#network-status' ).text( '' );
				},
				3000,
			);
		},
	};
	
	AdminEaseNetworkViewer.init();
	
	$( window ).on( 'beforeunload', function() {
		AdminEaseNetworkViewer.stopAutoRefresh();
	} );
} );