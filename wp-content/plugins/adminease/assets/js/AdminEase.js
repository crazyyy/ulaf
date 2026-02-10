jQuery( document ).ready( function( $ ) {
	var AdminEase = {
		ADMINEASE_SELECTOR: $( '.adminease' ),
		ajaxObj: AdminEaseAjaxObj,
		choices: {},
		intervals: {},
		init: function() {
			this.initEvents();
			this.initStickyMenu();
			this.initChoices();
			this.initSvgSupport();
			this.initPasswordProtectionLog();
			
			if( window.location.hash ) {
				this.handleTabsOnload();
			}
		},
		initEvents: function() {
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#adminease-menu-toggle', this.handleMobileMenu );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '.tabs-nav ul li.tabs-nav-item a', this.tabsNavigation );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '.adminease-switch', this.handleSwitch );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '.clear-selected-choices', this.clearSelectedChoices );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '.select-all-choices', this.selectAllChoices );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'submit', '.save-settings', this.saveSettings );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'change', '.toggle-field', this.handleToggleField );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'change', '#allow-custom-file-extension-upload-select-file', this.handleMimeTypeField );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#refresh-debug-log', this.refreshDebugLog );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#clear-debug-log', this.clearDebugLog );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#download-debug-log', this.downloadDebugLog );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#refresh-password-protection-log', this.refreshPasswordProtectionLog );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#download-password-protection-log', this.downloadPasswordProtectionLog );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'change', '.auto-refresh-toggle', this.toggleAutoRefresh );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '.adminease-toggle-has-sidebar', this.toggleMenuSidebar );
			AdminEase[ 'ADMINEASE_SELECTOR' ].on( 'click', '#adminease-minmax-sidebar', this.toggleMinMaxSidebar );
			
			AdminEase[ 'ADMINEASE_SELECTOR' ].find( '.toggle-field' ).trigger( 'change' );
			
			$( 'body' ).on( 'adminease_settings_saved', this.afterSaveSettings );
		},
		initStickyMenu: function() {
			$( window ).scroll( function() {
				let header = AdminEase[ 'ADMINEASE_SELECTOR' ].find( ' > header' );
				
				if( $( window ).scrollTop() > 0 ) {
					header.addClass( 'sticky' );
				}
				else {
					header.removeClass( 'sticky' );
				}
			} );
		},
		handleMobileMenu: function() {
			$( this ).toggleClass( 'dashicons-menu dashicons-no' );
		},
		tabsNavigation: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			let elTabs = el.closest( '.tabs' );
			let allTabs = $( '.tabs' );
			
			elTabs.find( '.tabs-nav-link' ).removeClass( 'active' );
			el.addClass( 'active' );
			$( '.dashicons.dashicons-no' ).trigger( 'click' );
			
			allTabs.children( '.tab-content' ).removeClass( 'active' );
			allTabs.children( '.tab-content' ).eq( el.parent().index() ).addClass( 'active' );
			
			if( AdminEaseAjaxObj[ 'isMobile' ] ) {
				$( '.adminease #menu-toggle' ).trigger( 'click' );
			}
			
			history.replaceState( null, '', el.attr( 'href' ) );
			
			$( 'html,body' ).animate( {
				scrollTop: 0,
			} );
		},
		handleSwitch: function( e ) {
			let el = $( e.currentTarget );
			let elInputCheckbox = el.find( 'input[type="checkbox"]' );
			let elInputHidden = el.find( 'input[type="hidden"]' );
			
			if( elInputCheckbox.is( ':checked' ) ) {
				elInputCheckbox.prop( 'checked', false ).trigger( 'change' );
				elInputHidden.val( '0' );
			}
			else {
				elInputCheckbox.prop( 'checked', true ).trigger( 'change' );
				elInputHidden.val( '1' );
			}
		},
		clearSelectedChoices: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			let elLabel = el.closest( 'label' );
			let id = elLabel.attr( 'for' );
			let elSelect = el.closest( '.form-group' ).find( 'select' );
			
			AdminEase.choices[ id ].removeActiveItems();
			
			elSelect.trigger( 'change' );
		},
		selectAllChoices: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			let elLabel = el.closest( 'label' );
			let elFormGroup = el.closest( '.form-group' );
			let id = elLabel.attr( 'for' );
			let allValues = [];
			
			// Escape the ID for safe selector usage
			let escapedId = id.replace( /([!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, '\\$1' );
			
			// Handle both optgroups and regular options
			$( '#' + escapedId + ' option' ).each( function() {
				let value = $( this ).val();
				
				if( value ) {
					allValues.push( value );
				}
			} );
			
			AdminEase.choices[ id ].setChoiceByValue( allValues );
			elFormGroup.find( 'input,select,textarea' ).trigger( 'change' );
		},
		saveSettings: function( e ) {
			e.preventDefault();
			
			let elForm = $( this );
			let elSubmit = elForm.find( 'button[type="submit"]' );
			let elErrors = elForm.find( '> .errors' );
			
			$( '.is-invalid' ).removeClass( 'is-invalid' );
			
			elForm.find( '.alert' ).remove();
			elErrors.html( '' );
			elSubmit.prop( 'disabled', true ).removeClass( 'error' ).addClass( 'loading' );
			
			let data = {
				action: 'adminease_save_settings',
				security: AdminEase[ 'ajaxObj' ][ 'security' ][ 'saveSettings' ],
				data: elForm.serialize(),
			}
			
			$.post( AdminEase[ 'ajaxObj' ][ 'ajaxUrl' ], data, function( response ) {
				elSubmit.prop( 'disabled', false ).removeClass( 'loading' );
				
				if( response[ 'success' ] ) {
					$( 'body' ).trigger( 'adminease_settings_saved', [response, data.data] );
					
					elSubmit.addClass( 'success' );
					
					setTimeout(
						function() {
							elSubmit.removeClass( 'success' );
						},
						2000,
					);
					
					if( response[ 'data' ][ 'message' ] ) {
						elErrors.html( '<div class="alert alert-success">' + response[ 'data' ][ 'message' ] + '</div>' );
						
						setTimeout(
							function() {
								elErrors.html( '' );
							},
							2000,
						);
					}
				}
				else {
					elSubmit.addClass( 'error' );
					
					if( response[ 'data' ][ 0 ][ 'code' ] ) {
						let id = response[ 'data' ][ 0 ][ 'code' ].replace( /_/g, '-' );
						let elError = $( '#' + id );
						let elFormGroup = elError.closest( '.form-group' );
						let elTab = elError.closest( '.tab-content' ).attr( 'id' );
						
						$( '.adminease .tab-content' ).removeClass( 'active' );
						$( '.adminease .tabs-nav ul li.tabs-nav-item a[href="#' + elTab + '"]' ).trigger( 'click' );
						
						elFormGroup.addClass( 'is-invalid' ).append( '<div class="alert alert-danger">' + response[ 'data' ][ 0 ][ 'message' ] ?? AdminEaseAjaxObj[ 'i18n' ][ 'unknownError' ] + '</div>' );
						
						$( 'html,body' ).animate( {
							scrollTop: elFormGroup.offset().top - 100,
						} );
					}
					else {
						elErrors.html( '<div class="alert alert-danger">' + AdminEaseAjaxObj[ 'i18n' ][ 'unknownError' ] + '</div>' );
					}
				}
			} );
		},
		handleToggleField: function() {
			let el = $( this );
			let elId = el.attr( 'id' );
			let elParent = $( '[data-parent="' + elId + '"]' );
			let elFormGroup = elParent.closest( '.form-group' );
			
			if( el.is( ':checked' ) || 'other' === el.val() ) {
				elFormGroup.slideDown( 'fast' );
				elParent.slideDown( 'fast' );
				
				if( 'wp-debug' === elId ) {
					setTimeout(
						function() {
							let elPre = $( '#debug-log-container pre' );
							
							elPre.scrollTop( elPre[ 0 ].scrollHeight );
						},
						100,
					)
				}
			}
			else {
				elFormGroup.slideUp( 'fast' );
				elParent.slideUp( 'fast' );
			}
		},
		handleMimeTypeField: function( e ) {
			e.preventDefault();
			
			let file = e.target.files[ 0 ];
			
			$( '.file-name' ).text( file ? file.type : AdminEaseAjaxObj[ 'i18n' ][ 'mimeTypeNotRecognized' ] );
		},
		toggleAutoRefresh: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			let action = el.attr( 'data-action' );
			let elChecked = el.find( 'input' ).is( ':checked' );
			let intervalTime = el.attr( 'data-interval' ) || 10;
			
			if( 'undefined' === typeof AdminEase[ 'intervals' ] ) {
				AdminEase[ 'intervals' ] = {};
			}
			
			// Clear any existing interval for this action to prevent duplicates
			if( AdminEase[ 'intervals' ][ action ] ) {
				clearInterval( AdminEase[ 'intervals' ][ action ] );
			}
			
			if( elChecked ) {
				AdminEase[ 'intervals' ][ action ] = setInterval(
					function() {
						if( 'wp-debug' === action ) {
							AdminEase.refreshDebugLog( e );
						}
					},
					intervalTime * 1000,
				);
			}
		},
		toggleMenuSidebar: function() {
			let elAdminEase = $( '#wpcontent section.adminease' );
			
			elAdminEase.toggleClass( 'has-sidebar' );
			
			let data = {
				action: 'adminease_toggle_menu_sidebar',
				security: AdminEaseAjaxObj[ 'security' ][ 'toggleMenuSidebar' ],
				data: {
					isActive: elAdminEase.hasClass( 'has-sidebar' ) ? 1 : 0,
				},
			}
			
			$.post( AdminEaseAjaxObj[ 'ajaxUrl' ], data );
		},
		toggleMinMaxSidebar: function() {
			let elAdminEase = $( '#wpcontent section.adminease' );
			
			elAdminEase.addClass( 'has-sidebar' ).toggleClass( 'minmax-sidebar' );
			
			let data = {
				action: 'adminease_toggle_menu_sidebar_minmax',
				security: AdminEaseAjaxObj[ 'security' ][ 'toggleMenuSidebarMinMax' ],
				data: {
					isMinMaxActive: elAdminEase.hasClass( 'minmax-sidebar' ) ? 1 : 0,
				},
			}
			
			$.post( AdminEaseAjaxObj[ 'ajaxUrl' ], data );
		},
		
		refreshDebugLog: function( e ) {
			e.preventDefault();
			
			let el = $( e.currentTarget );
			let elPre = $( '#debug-log-container pre' );
			
			el.prop( 'disabled', true ).addClass( 'loading' );
			elPre.addClass( 'loading' );
			
			let data = {
				action: 'adminease_get_debug_log',
				security: AdminEaseAjaxObj[ 'security' ][ 'refreshDebugLog' ],
				lines: $( '#debug-log-lines-to-show' ).val() || 1000,
			}
			
			$.post( AdminEaseAjaxObj[ 'ajaxUrl' ], data, function( response ) {
				el.prop( 'disabled', false ).removeClass( 'loading' );
				elPre.removeClass( 'loading' );
				
				if( response[ 'success' ] ) {
					let contents = response[ 'data' ][ 'contents' ];
					
					if( '' === contents ) {
						contents = AdminEaseAjaxObj[ 'i18n' ][ 'debugLogEmpty' ];
					}
					
					elPre.html( contents );
					
					// Update file size display
					$( '#debug-log-size' ).text( response[ 'data' ][ 'file_size' ] );
					
					// Update percentage if element exists
					let elPercentage = $( '.size-percentage' );
					if( elPercentage.length && response[ 'data' ][ 'percentage' ] > 0 ) {
						elPercentage.attr( 'data-percentage', response[ 'data' ][ 'percentage' ] )
							.text( '(' + response[ 'data' ][ 'percentage' ] + '% ' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogOfMemoryLimit' ] + ')' );
					}
					
					// Show/update warnings
					$( '#debug-log-warning, #debug-log-critical, #debug-log-truncated' ).remove();
					
					// Show truncation notice if file was truncated
					if( response[ 'data' ][ 'truncated' ] ) {
						let truncatedMessage = AdminEaseAjaxObj[ 'i18n' ][ 'debugLogTruncatedMessage' ]
							.replace( '%s', response[ 'data' ][ 'lines_shown' ] )
							.replace( '%s', response[ 'data' ][ 'total_lines' ] )
							.replace( '%s', response[ 'data' ][ 'file_size' ] );
						
						$( '.debug-log-info' ).append(
							'<div class="notice notice-info inline" id="debug-log-truncated">' +
							'<p><span class="dashicons dashicons-info"></span>' +
							'<strong>' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogTruncatedInfo' ] + '</strong> ' +
							truncatedMessage + '</p>' +
							'</div>',
						);
					}
					
					if( response[ 'data' ][ 'critical' ] ) {
						$( '.debug-log-info' ).append(
							'<div class="notice notice-error inline" id="debug-log-critical">' +
							'<p><span class="dashicons dashicons-dismiss"></span>' +
							'<strong>' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogCritical' ] + '</strong> ' +
							AdminEaseAjaxObj[ 'i18n' ][ 'debugLogCriticalMessage' ] + '</p>' +
							'</div>',
						);
						
						// Reload page to reflect disabled settings
						setTimeout( function() {
							location.reload();
						}, 3000 );
					}
					else if( response[ 'data' ][ 'warning' ] ) {
						let warningMessage = AdminEaseAjaxObj[ 'i18n' ][ 'debugLogWarningMessage' ]
							.replace( '%s', response[ 'data' ][ 'file_size' ] )
							.replace( '%s', response[ 'data' ][ 'percentage' ] );
						
						$( '.debug-log-info' ).append(
							'<div class="alert alert-danger" id="debug-log-warning">' +
							'<p>' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogWarning' ] + warningMessage + '</p>' +
							'</div>',
						);
					}
					
					// Auto-scroll to bottom
					elPre.scrollTop( elPre[ 0 ].scrollHeight );
				}
				else {
					elPre.html( '<p class="error">' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogRefreshError' ] + '</p>' );
				}
			} ).fail( function() {
				el.prop( 'disabled', false ).removeClass( 'loading' );
				elPre.removeClass( 'loading' );
				elPre.html( '<p class="error">' + AdminEaseAjaxObj[ 'i18n' ][ 'debugLogRefreshError' ] + '</p>' );
			} );
		},
		clearDebugLog: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			let elPre = $( '#debug-log-container pre' );
			
			if( !confirm( AdminEaseAjaxObj[ 'i18n' ][ 'confirmClearDebugLog' ] ) ) {
				return;
			}
			
			el.prop( 'disabled', true ).addClass( 'loading' );
			elPre.addClass( 'loading' );
			
			let data = {
				action: 'adminease_clear_debug_log',
				security: AdminEaseAjaxObj[ 'security' ][ 'clearDebugLog' ],
			}
			
			$.post( AdminEaseAjaxObj[ 'ajaxUrl' ], data, function( response ) {
				el.prop( 'disabled', false ).removeClass( 'loading' );
				elPre.removeClass( 'loading' );
				
				if( response[ 'success' ] ) {
					elPre.text( AdminEaseAjaxObj[ 'i18n' ][ 'debugLogEmpty' ] );
				}
			} );
		},
		downloadDebugLog: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			
			el.prop( 'disabled', true ).addClass( 'loading' );
			
			let data = {
				action: 'adminease_download_debug_log',
				security: AdminEaseAjaxObj[ 'security' ][ 'downloadDebugLog' ],
			}
			
			$.ajax( {
				url: AdminEaseAjaxObj[ 'ajaxUrl' ],
				type: 'POST',
				data: data,
				xhrFields: {
					responseType: 'blob',
				},
				success: function( blob ) {
					el.prop( 'disabled', false ).removeClass( 'loading' );
					
					// Create a download link and trigger it
					let link = document.createElement( 'a' );
					link.href = window.URL.createObjectURL( blob );
					link.download = 'debug.log';
					document.body.appendChild( link );
					link.click();
					document.body.removeChild( link );
					window.URL.revokeObjectURL( link.href );
				},
				error: function() {
					el.prop( 'disabled', false ).removeClass( 'loading' );
					alert( AdminEaseAjaxObj[ 'i18n' ][ 'debugLogDownloadFailed' ] );
				},
			} );
		},
		refreshPasswordProtectionLog: function( e ) {
			e.preventDefault();
			
			let el = $( e.currentTarget );
			let elTableBody = $( '#password-protection-log-table-body' );
			let elLogCount = $( '#password-protection-log-count' );
			
			el.prop( 'disabled', true ).addClass( 'loading' );
			elTableBody.html( '<tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>' );
			
			let data = {
				action: 'adminease_get_password_protection_log',
				security: AdminEaseAjaxObj[ 'security' ][ 'refreshPasswordProtectionLog' ],
			}
			
			$.post( AdminEaseAjaxObj[ 'ajaxUrl' ], data, function( response ) {
				el.prop( 'disabled', false ).removeClass( 'loading' );
				
				if( response[ 'success' ] ) {
					let logs = response[ 'data' ][ 'logs' ];
					let total = response[ 'data' ][ 'total' ];
					
					if( logs.length === 0 ) {
						elTableBody.html( '<tr><td colspan="5" style="text-align: center; padding: 20px;">' + AdminEaseAjaxObj[ 'i18n' ][ 'passwordProtectionLogEmpty' ] + '</td></tr>' );
						elLogCount.html( '' );
					}
					else {
						let rows = '';
						
						$.each( logs, function( index, log ) {
							let statusClass = log.success ? 'success' : 'failed';
							let statusText = log.success ? '<span style="color: #2e7d32;">Success</span>' : '<span style="color: #c62828;">Failed</span>';
							let passwordHash = log.attempted_password ? '<code style="font-size: 11px;">' + log.attempted_password.substring( 0, 16 ) + '...</code>' : '-';
							
							rows += '<tr>' +
								'<td style="padding: 8px;">' + log.timestamp + '</td>' +
								'<td style="padding: 8px;"><code>' + log.ip + '</code></td>' +
								'<td style="padding: 8px;">' + statusText + '</td>' +
								'<td style="padding: 8px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + log.user_agent + '">' + log.user_agent + '</td>' +
								'<td style="padding: 8px;">' + passwordHash + '</td>' +
								'</tr>';
						} );
						
						elTableBody.html( rows );
						elLogCount.html( 'Showing ' + total + ' of ' + response[ 'data' ][ 'log_limit' ] + ' maximum entries (newest first)' );
					}
				}
				else {
					elTableBody.html( '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #c62828;">' + AdminEaseAjaxObj[ 'i18n' ][ 'passwordProtectionLogRefreshError' ] + '</td></tr>' );
				}
			} ).fail( function() {
				el.prop( 'disabled', false ).removeClass( 'loading' );
				elTableBody.html( '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #c62828;">' + AdminEaseAjaxObj[ 'i18n' ][ 'passwordProtectionLogRefreshError' ] + '</td></tr>' );
			} );
		},
		downloadPasswordProtectionLog: function( e ) {
			e.preventDefault();
			
			let el = $( this );
			
			el.prop( 'disabled', true ).addClass( 'loading' );
			
			let data = {
				action: 'adminease_download_password_protection_log',
				security: AdminEaseAjaxObj[ 'security' ][ 'downloadPasswordProtectionLog' ],
			}
			
			$.ajax( {
				url: AdminEaseAjaxObj[ 'ajaxUrl' ],
				type: 'POST',
				data: data,
				xhrFields: {
					responseType: 'blob',
				},
				success: function( blob ) {
					el.prop( 'disabled', false ).removeClass( 'loading' );
					
					// Create a download link and trigger it
					let link = document.createElement( 'a' );
					link.href = window.URL.createObjectURL( blob );
					link.download = 'password-protection-log-' + new Date().toISOString().slice( 0, 10 ) + '.csv';
					document.body.appendChild( link );
					link.click();
					document.body.removeChild( link );
					window.URL.revokeObjectURL( link.href );
				},
				error: function() {
					el.prop( 'disabled', false ).removeClass( 'loading' );
					alert( AdminEaseAjaxObj[ 'i18n' ][ 'passwordProtectionLogDownloadFailed' ] );
				},
			} );
		},
		initChoices: function() {
			$( '.adminease-choices' ).each( function() {
				let el = $( this );
				let id = el.attr( 'id' );
				let hasOptgroups = el.find( 'optgroup' ).length > 0;
				
				// Enhanced configuration for optgroups
				let choicesConfig = {
					removeItemButton: el.attr( 'data-allow_clear' ),
					searchEnabled: !!el.attr( 'multiple' ),
					placeholder: true,
					placeholderValue: el.attr( 'placeholder' ),
					shouldSort: false,
					loadingText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesLoadingText' ],
					noResultsText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesNoResultsText' ],
					noChoicesText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesNoChoicesText' ],
					itemSelectText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesItemSelectText' ],
					uniqueItemText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesUniqueItemText' ],
					customAddItemText: AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'selectChoicesCustomAddItemText' ],
				};
				
				// Additional config for optgroups
				if( hasOptgroups ) {
					choicesConfig.searchResultLimit = 50;
					choicesConfig.renderSelectedChoices = 'always';
					choicesConfig.searchFields = ['label', 'value'];
					choicesConfig.searchFloor = 1;
					choicesConfig.searchChoices = true;
				}
				
				let currentChoice = new Choices( this, choicesConfig );
				
				if( el.attr( 'readonly' ) ) {
					currentChoice.disable();
				}
				
				AdminEase.choices[ id ] = currentChoice;
			} );
		},
		choicesChildFieldsToggle: function( el ) {
			let elVal = el.val();
			let attrMultiple = el.attr( 'multiple' );
			let elFormGroup = el.closest( '.form-group' );
			let elSelectedChoices = elFormGroup.find( '.clear-selected-choices' );
			let elRow = el.closest( '.row' );
			let elParentFormGroup = elRow.find( '.form-group:not(.form-group-child)' );
			let formGroupChildren = elRow.find( '.form-group-child' );
			let childFieldDescription = elRow.closest( '.row' ).find( '.child-field-description' );
			
			if( 'undefined' !== typeof attrMultiple ) {
				if( 0 !== elVal.length ) {
					formGroupChildren.slideDown( 'fast' );
					childFieldDescription.slideDown( 'fast' );
					elSelectedChoices.show();
				}
				else {
					if( 'undefined' !== typeof elParentFormGroup && 0 === elParentFormGroup.find( 'select,input' ).val().length ) {
						formGroupChildren.slideUp( 'fast' );
						childFieldDescription.slideUp( 'fast' );
					}
					
					elSelectedChoices.hide();
				}
			}
			else {
				if( 'other' === elVal ) {
					formGroupChildren.slideDown( 'fast' );
					childFieldDescription.slideDown( 'fast' );
					elSelectedChoices.show();
				}
				else {
					formGroupChildren.slideUp( 'fast' );
					childFieldDescription.slideUp( 'fast' );
					elSelectedChoices.hide();
				}
			}
		},
		initSvgSupport: function() {
			// Enhanced SVG display in media modal
			$( document ).on( 'click', '.attachment[data-subtype="svg+xml"]', function() {
				setTimeout(
					function() {
						var preview = $( '.attachment-details .thumbnail img' );
						if( preview.length && preview.attr( 'src' ).indexOf( '.svg' ) !== -1 ) {
							preview.css( {
								'max-width': '100%',
								'max-height': '300px',
								'width': 'auto',
								'height': 'auto',
								'object-fit': 'contain',
								'background': '#f9f9f9',
								'border': '1px solid #ddd',
								'border-radius': '4px',
								'padding': '10px',
							} );
							
							// Add sanitization notice
							if( !$( '.svg-sanitized-notice' ).length ) {
								$( '.attachment-details .thumbnail' ).append(
									'<div class="svg-sanitized-notice" style="position: absolute; top: 5px; right: 5px; background: #00a32a; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;">' + AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'svgSanitizedLabel' ] + '</div>',
								);
							}
						}
					},
					100,
				);
			} );
			
			// Show upload success message for SVG (using modern MutationObserver)
			if( typeof MutationObserver !== 'undefined' ) {
				var observer = new MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						if( mutation.type === 'childList' ) {
							$( mutation.addedNodes ).each( function() {
								if( $( this ).hasClass && $( this ).hasClass( 'attachment' ) && $( this ).attr( 'data-subtype' ) === 'svg+xml' ) {
									AdminEase.showSvgUploadNotice();
								}
							} );
						}
					} );
				} );
				
				// Start observing media library changes
				var targetNode = document.querySelector( '.media-frame-content' );
				
				if( targetNode ) {
					observer.observe( targetNode, {
						childList: true,
						subtree: true,
					} );
				}
			}
			else {
				// Fallback for older browsers
				$( document ).on( 'DOMNodeInserted', function( e ) {
					if( $( e.target ).hasClass( 'attachment' ) && $( e.target ).attr( 'data-subtype' ) === 'svg+xml' ) {
						AdminEase.showSvgUploadNotice();
					}
				} );
			}
		},
		showSvgUploadNotice: function() {
			let notice = $( '<div class="notice notice-success"><p><strong>' + AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'svgUploadSuccess' ] + '</strong> ' + AdminEase[ 'ajaxObj' ][ 'i18n' ][ 'svgUploadSuccessDescription' ] + '</p></div>' );
			let elMediaFrameContent = $( '.media-frame-content .attachments-browser' );
			
			if( elMediaFrameContent.length ) {
				elMediaFrameContent.prepend( notice );
				
				setTimeout(
					function() {
						notice.fadeOut();
					},
					4000,
				);
			}
		},
		handleTabsOnload: function() {
			let elTabsContent = $( '.adminease ' + window.location.hash );
			
			$( '.adminease .tab-content' ).removeClass( 'active' );
			$( '.adminease .tabs[data-tabs="main-tabs"] .tabs-nav-item a' ).removeClass( 'active' );
			$( '.adminease .tabs[data-tabs="main-tabs"] .tabs-nav-item a[href="' + window.location.hash + '"]' ).addClass( 'active' );
			
			elTabsContent.addClass( 'active' );
		},
		afterSaveSettings: function( event, response ) {
			if( response[ 'success' ] ) {
				if( response[ 'data' ][ 'reload' ] ) {
					location.reload();
				}
			}
		},
		initPasswordProtectionLog: function() {
			if( $( '#password-protect-site-auto-load-log' ).is(':checked') ) {
				setTimeout(
					function() {
						$( '#refresh-password-protection-log' ).trigger( 'click' );
					},
					500,
				);
			}
		},
	}
	
	AdminEase.init();
	
	var AdminEaseProgressBar = {
		container: null,
		fill: null,
		text: null,
		init: function( containerSelector ) {
			this.container = $( containerSelector );
			this.fill = this.container.find( '.adminease-progress-bar-fill' );
			this.text = this.container.find( '.adminease-progress-bar-text' );
		},
		show: function() {
			this.container.show();
		},
		hide: function() {
			this.container.hide();
		},
		update: function( current, total ) {
			let percentage = total > 0 ? Math.round( ( current / total ) * 100 ) : 0;
			this.fill.css( 'width', percentage + '%' );
			this.text.text( current + ' / ' + total + ' (' + percentage + '%)' );
		},
		reset: function() {
			this.fill.css( 'width', '0%' );
			this.text.text( '0 / 0 (0%)' );
		},
	};
} );