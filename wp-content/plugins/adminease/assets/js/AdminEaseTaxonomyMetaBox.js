jQuery( document ).ready( function( $ ) {
	var AdminEaseTaxonomyMetaBox = {
		config: {
			termsPerPage: AdminEaseTaxonomyMetaBoxAjaxObj[ 'termsPerPage' ] || 20,
			searchDelay: 500,
			loadingClass: 'adminease-loading',
			selectedClass: 'adminease-selected',
		},
		taxonomies: {},
		searchTimeouts: {},
		pageTimeouts: {},
		observedTaxonomies: new Set(),
		
		init: function( taxonomy, currentTerms ) {
			currentTerms = currentTerms || [];
			
			// Initialize taxonomy state
			this.taxonomies[ taxonomy ] = {
				currentPage: 1,
				totalPages: 1,
				selectedTerms: currentTerms,
				searchQuery: '',
				loading: false,
				termsLoaded: false,
				selectedTermsMap: new Map(),
			};
			
			// Initialize selected terms map
			this.initSelectedTermsMap( taxonomy, currentTerms );
			
			// Bind events
			this.bindEvents( taxonomy );
			
			// Only load terms if metabox is visible
			if( this.isMetaboxVisible( taxonomy ) ) {
				this.loadTerms( taxonomy, 1 );
			}
			else {
				// Set up intersection observer to load when visible
				this.observeMetabox( taxonomy );
			}
		},
		initSelectedTermsMap: function( taxonomy, currentTerms ) {
			// Clear existing map for this taxonomy
			this.taxonomies[ taxonomy ].selectedTermsMap.clear();
			
			if( currentTerms && currentTerms.length > 0 ) {
				// Store pending selected terms - we'll populate names when terms are loaded
				this.taxonomies[ taxonomy ].pendingSelectedTerms = currentTerms;
				
				// Try to get term names via AJAX first
				this.populateSelectedTermsMap( taxonomy, currentTerms );
			}
		},
		isMetaboxVisible: function( taxonomy ) {
			var $metaBox = $( '#adminease-taxonomy-' + taxonomy );
			
			if( !$metaBox.length ) {
				return false;
			}
			
			// Check if element is in viewport
			var elementTop = $metaBox.offset().top;
			var elementBottom = elementTop + $metaBox.outerHeight();
			var viewportTop = $( window ).scrollTop();
			var viewportBottom = viewportTop + $( window ).height();
			
			return elementBottom > viewportTop && elementTop < viewportBottom;
		},
		observeMetabox: function( taxonomy ) {
			if( this.observedTaxonomies.has( taxonomy ) ) {
				return; // Already observing this taxonomy
			}
			
			var self = this;
			var $metaBox = $( '#adminease-taxonomy-' + taxonomy );
			
			if( !$metaBox.length ) {
				return;
			}
			
			// Use Intersection Observer if available, otherwise fall back to scroll events
			if( 'IntersectionObserver' in window ) {
				var observer = new IntersectionObserver( function( entries ) {
					entries.forEach( function( entry ) {
						if( entry.isIntersecting && !self.taxonomies[ taxonomy ].termsLoaded ) {
							self.loadTerms( taxonomy, 1 );
							observer.disconnect(); // Stop observing once loaded
							self.observedTaxonomies.delete( taxonomy );
						}
					} );
				}, {
					rootMargin: '50px', // Load slightly before coming into view
				} );
				
				observer.observe( $metaBox[ 0 ] );
				this.observedTaxonomies.add( taxonomy );
			}
			else {
				// Fallback: use scroll events
				var scrollHandler = function() {
					if( self.isMetaboxVisible( taxonomy ) && !self.taxonomies[ taxonomy ].termsLoaded ) {
						self.loadTerms( taxonomy, 1 );
						$( window ).off( 'scroll', scrollHandler );
						self.observedTaxonomies.delete( taxonomy );
					}
				};
				
				$( window ).on( 'scroll', scrollHandler );
				this.observedTaxonomies.add( taxonomy );
			}
		},
		initAll: function() {
			var self = this;
			
			// Find all taxonomy meta boxes and initialize them
			$( '.adminease-taxonomy-meta-box' ).each( function() {
				var $metaBox = $( this );
				var taxonomy = $metaBox.data( 'taxonomy' );
				
				if( taxonomy ) {
					// Get currently selected terms
					var currentTerms = [];
					
					$metaBox.find( 'input[name="tax_input[' + taxonomy + '][]"]' ).each( function() {
						var termId = parseInt( $( this ).val() );
						if( termId ) {
							currentTerms.push( termId );
						}
					} );
					
					// Initialize this taxonomy
					self.init( taxonomy, currentTerms );
				}
			} );
		},
		escapeHtml: function( text ) {
			var div = document.createElement( 'div' );
			
			div.textContent = text;
			
			return div.innerHTML;
		},
		bindEvents: function( taxonomy ) {
			var self = this;
			
			// Search input - ensure terms are loaded before searching
			$( document ).on( 'input', '#adminease-taxonomy-search-' + taxonomy, function() {
				var query = $( this ).val();
				
				// Load terms if not already loaded
				if( !self.taxonomies[ taxonomy ].termsLoaded ) {
					self.loadTerms( taxonomy, 1, false, function() {
						self.handleSearch( taxonomy, query );
						self.toggleClearButton( taxonomy, query );
					} );
				}
				else {
					self.handleSearch( taxonomy, query );
					self.toggleClearButton( taxonomy, query );
				}
			} );
			
			// Clear search button
			$( document ).on( 'click', '#adminease-search-clear-' + taxonomy, function() {
				self.clearSearch( taxonomy );
			} );
			
			// Per page selection
			$( document ).on( 'change', '#adminease-per-page-' + taxonomy, function() {
				var perPage = parseInt( $( this ).val() );
				
				if( perPage > 0 ) {
					self.config.termsPerPage = perPage;
					self.taxonomies[ taxonomy ].currentPage = 1;
					self.loadTerms( taxonomy, 1 );
				}
			} );
			
			// Page input with delay (same as search)
			$( document ).on( 'keyup', '#adminease-goto-page-' + taxonomy, function() {
				var $input = $( this );
				var page = parseInt( $input.val() );
				var maxPage = parseInt( $input.attr( 'max' ) );
				
				// Validate input value
				if( page > maxPage ) {
					$input.val( maxPage );
					page = maxPage;
				}
				else if( page < 1 || isNaN( page ) ) {
					// Don't do anything for values smaller than 1 or invalid values
					return false;
				}
				
				// Clear existing timeout for this taxonomy's page input
				if( self.pageTimeouts[ taxonomy ] ) {
					clearTimeout( self.pageTimeouts[ taxonomy ] );
				}
				
				// Set new timeout with same delay as search
				self.pageTimeouts[ taxonomy ] = setTimeout(
					function() {
						if( page >= 1 && page <= maxPage && page !== self.taxonomies[ taxonomy ].currentPage ) {
							self.loadTerms( taxonomy, page );
						}
					},
					self.config.searchDelay,
				);
			} );
			
			
			// Page input blur event - validate when user leaves the field
			$( document ).on( 'blur', '#adminease-goto-page-' + taxonomy, function() {
				var $input = $( this );
				var page = parseInt( $input.val() );
				var maxPage = parseInt( $input.attr( 'max' ) );
				
				// Validate input value on blur
				if( page > maxPage ) {
					$input.val( maxPage );
				}
				else if( page < 1 || isNaN( page ) ) {
					// Reset to current page if invalid
					$input.val( self.taxonomies[ taxonomy ].currentPage );
				}
			} );
			
			
			// Also add paste event for immediate response
			$( document ).on( 'paste', '#adminease-goto-page-' + taxonomy, function() {
				var self_input = this;
				setTimeout(
					function() {
						$( self_input ).trigger( 'keyup' );
					},
					10,
				);
			} );
			
			// Page input change event - trigger when clicking number input buttons
			$( document ).on( 'change', '#adminease-goto-page-' + taxonomy, function() {
				var $input = $( this );
				var page = parseInt( $input.val() );
				var maxPage = parseInt( $input.attr( 'max' ) );
				
				// Validate input value
				if( page > maxPage ) {
					$input.val( maxPage );
					page = maxPage;
				}
				else if( page < 1 || isNaN( page ) ) {
					$input.val( self.taxonomies[ taxonomy ].currentPage );
					
					return false;
				}
				
				// Trigger page change immediately on change event (for number input buttons)
				if( page >= 1 && page <= maxPage && page !== self.taxonomies[ taxonomy ].currentPage ) {
					// Clear any existing timeout since we're changing immediately
					if( self.pageTimeouts[ taxonomy ] ) {
						clearTimeout( self.pageTimeouts[ taxonomy ] );
					}
					
					self.loadTerms( taxonomy, page );
				}
			} );
			
			// Create term link (updated for new class)
			$( document ).on( 'click', '.adminease-create-term-link[data-taxonomy="' + taxonomy + '"]', function( e ) {
				e.preventDefault();
				
				var termName = prompt( AdminEaseTaxonomyMetaBoxAjaxObj[ 'i18n' ][ 'enterNewTermText' ] );
				
				if( termName && termName.trim() ) {
					self.createTerm( taxonomy, termName.trim() );
				}
			} );
			
			// Create term from search (existing functionality)
			$( document ).on( 'click', '.adminease-create-term', function( e ) {
				e.preventDefault();
				var $link = $( this );
				var linkTaxonomy = $link.data( 'taxonomy' );
				var termName = $link.data( 'term-name' );
				
				if( linkTaxonomy === taxonomy && termName ) {
					self.createTerm( taxonomy, termName );
				}
			} );
			
			// Term selection for non-hierarchical taxonomies
			$( document ).on( 'click', '#adminease-available-terms-' + taxonomy + ' .adminease-term', function() {
				var termId = parseInt( $( this ).data( 'term-id' ) );
				
				self.selectTerm( taxonomy, termId );
				
				$( this ).remove();
			} );
			
			// Remove selected term
			$( document ).on( 'click', '#adminease-selected-terms-' + taxonomy + ' .adminease-remove-term', function() {
				var termId = parseInt( $( this ).data( 'term-id' ) );
				
				self.deselectTerm( taxonomy, termId );
			} );
			
			// Pagination
			$( document ).on( 'click', '#adminease-taxonomy-pagination-' + taxonomy + ' .adminease-page-link', function( e ) {
				e.preventDefault();
				
				var page = parseInt( $( this ).data( 'page' ) );
				
				if( page > 0 && page <= self.taxonomies[ taxonomy ].totalPages ) {
					self.loadTerms( taxonomy, page );
				}
			} );
			
			// Load more (for infinite scroll)
			$( document ).on( 'click', '#adminease-taxonomy-tree-' + taxonomy + ' .adminease-load-more', function() {
				var nextPage = self.taxonomies[ taxonomy ].currentPage + 1;
				
				if( nextPage <= self.taxonomies[ taxonomy ].totalPages ) {
					self.loadTerms( taxonomy, nextPage, true ); // append = true
				}
			} );
			
			// Term selection for hierarchical taxonomies - delegate to tree container
			$( document ).on( 'change', '#adminease-taxonomy-tree-' + taxonomy + ' input[type="checkbox"]', function() {
				var $checkbox = $( this );
				var termId = parseInt( $checkbox.val() );
				
				// Extract term name from the label text
				var $label = $checkbox.closest( 'label' );
				var $nameSpan = $label.find( '.adminease-term-name' );
				var termName = '';
				var termPath = '';
				
				if( $nameSpan.length ) {
					// Clone the span and remove the hierarchy indicator to get clean name
					var $nameClone = $nameSpan.clone();
					$nameClone.find( '.adminease-hierarchy-indicator' ).remove();
					termName = $nameClone.text().trim();
					
					// For hierarchical terms, use the full text (including hierarchy) as path
					termPath = $nameSpan.text().trim();
				}
				else {
					// Fallback: get text from the entire label and clean it up
					var labelText = $label.text().trim();
					
					termName = labelText.replace( /\s*\(\d+\)\s*$/, '' ).trim();
					termPath = termName;
				}
				
				if( $checkbox.is( ':checked' ) ) {
					self.addSelectedTerm( taxonomy, termId, termName, termPath );
				}
				else {
					self.removeSelectedTerm( taxonomy, termId );
				}
				
				self.updateHiddenInputs( taxonomy );
				
				self.renderChips( taxonomy );
			} );
			
			// Chip removal - delegate to chips container
			$( document ).on( 'click', '#taxonomy-selected-chips-' + taxonomy + ' .chip', function() {
				var $chip = $( this );
				var termId = parseInt( $chip.data( 'term-id' ) );
				
				self.removeSelectedTerm( taxonomy, termId );
				self.updateHiddenInputs( taxonomy );
				self.renderChips( taxonomy );
				
				// Uncheck corresponding checkbox if visible
				$( '#adminease-taxonomy-tree-' + taxonomy + ' input[value="' + termId + '"]' ).prop( 'checked', false );
			} );
			
			// Clear all button
			$( document ).on( 'click', '#taxonomy-clear-all-' + taxonomy, function() {
				self.clearAllSelected( taxonomy );
			} );
			
			// Overflow popover handling
			$( document ).on( 'click', '#taxonomy-chips-more-' + taxonomy, function() {
				self.toggleOverflowPopover( taxonomy );
			} );
			
			// Close popover when clicking outside
			$( document ).on( 'click', function( e ) {
				if( !$( e.target ).closest( '.taxonomy-chips-popover, #taxonomy-chips-more-' + taxonomy ).length ) {
					self.closeOverflowPopover( taxonomy );
				}
			} );
			
			// Keyboard support for chips
			$( document ).on( 'keydown', '#taxonomy-selected-chips-' + taxonomy + ' .chip', function( e ) {
				if( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					$( this ).trigger( 'click' );
				}
			} );
		},
		addSelectedTerm: function( taxonomy, termId, termName, termPath ) {
			this.taxonomies[ taxonomy ].selectedTermsMap.set( termId, {
				name: termName,
				path: termPath || termName,
				ancestry: termPath,
			} );
		},
		
		removeSelectedTerm: function( taxonomy, termId ) {
			this.taxonomies[ taxonomy ].selectedTermsMap.delete( termId );
		},
		
		clearAllSelected: function( taxonomy ) {
			this.taxonomies[ taxonomy ].selectedTermsMap.clear();
			
			// Uncheck all checkboxes in the current view
			$( '#adminease-taxonomy-tree-' + taxonomy + ' input[type="checkbox"]:checked' ).prop( 'checked', false );
			
			// Update hidden inputs and re-render
			this.updateHiddenInputs( taxonomy );
			this.renderChips( taxonomy );
			this.closeOverflowPopover( taxonomy );
		},
		
		updateHiddenInputs: function( taxonomy ) {
			var $container = $( '#adminease-taxonomy-inputs-' + taxonomy );
			$container.empty();
			
			// Add hidden input for each selected term
			this.taxonomies[ taxonomy ].selectedTermsMap.forEach( function( termData, termId ) {
				$container.append( '<input type="hidden" name="tax_input[' + taxonomy + '][]" value="' + termId + '">' );
			} );
		},
		
		renderChips: function( taxonomy ) {
			var self = this;
			var $container = $( '#taxonomy-selected-chips-' + taxonomy );
			
			if( this.taxonomies[ taxonomy ].selectedTermsMap.size === 0 ) {
				$container.html( '' );
				return;
			}
			
			var chips = [];
			var maxVisibleChips = 10;
			var chipCount = 0;
			
			// Generate chips HTML
			this.taxonomies[ taxonomy ].selectedTermsMap.forEach( function( termData, termId ) {
				var chipHtml = '<button class="chip" role="listitem" data-term-id="' + termId + '" ' +
					'aria-label="' + self.escapeHtml( AdminEaseTaxonomyMetaBoxAjaxObj.i18n.removeSelected.replace( '%s', termData.name ) ) + '">' +
					'<span class="chip-text">' + self.escapeHtml( termData.name ) + '</span>' +
					'<span class="chip-remove" aria-hidden="true">×</span>' +
					'</button>';
				
				chips.push( chipHtml );
			} );
			
			// Add Clear All button
			var clearAllHtml = '<button class="chip-clear-all" id="taxonomy-clear-all-' + taxonomy + '" ' +
				'aria-label="' + AdminEaseTaxonomyMetaBoxAjaxObj.i18n.clearAll + '">' +
				AdminEaseTaxonomyMetaBoxAjaxObj.i18n.clearAll +
				'</button>';
			
			// Check if we need overflow handling
			var html = '';
			if( chips.length <= maxVisibleChips ) {
				// Show all chips
				html = chips.join( '' ) + clearAllHtml;
			}
			else {
				// Show first 10 + more button
				var visibleChips = chips.slice( 0, maxVisibleChips );
				var hiddenCount = chips.length - maxVisibleChips;
				
				html = visibleChips.join( '' ) +
					'<button class="chip chip-more" id="taxonomy-chips-more-' + taxonomy + '" ' +
					'aria-label="' + AdminEaseTaxonomyMetaBoxAjaxObj.i18n.moreSelected.replace( '%d', hiddenCount ) + '">' +
					AdminEaseTaxonomyMetaBoxAjaxObj.i18n.moreSelected.replace( '%d', hiddenCount ) +
					'</button>' + clearAllHtml;
			}
			
			$container.html( html );
			
			// Update line clamping
			this.updateLineClamp( taxonomy );
		},
		
		updateLineClamp: function( taxonomy ) {
			var $container = $( '#taxonomy-selected-chips-' + taxonomy );
			var maxLines = 3;
			
			// Simple approach: if container height exceeds 3 lines worth, apply line clamp
			var lineHeight = parseInt( $container.css( 'line-height' ) ) || 20;
			var maxHeight = lineHeight * maxLines;
			
			if( $container[ 0 ].scrollHeight > maxHeight ) {
				$container.addClass( 'chips-overflow' );
			}
			else {
				$container.removeClass( 'chips-overflow' );
			}
		},
		
		toggleOverflowPopover: function( taxonomy ) {
			var $popover = $( '.taxonomy-chips-popover[data-taxonomy="' + taxonomy + '"]' );
			
			if( $popover.length && $popover.is( ':visible' ) ) {
				this.closeOverflowPopover( taxonomy );
			}
			else {
				this.openOverflowPopover( taxonomy );
			}
		},
		
		openOverflowPopover: function( taxonomy ) {
			var self = this;
			var $container = $( '#taxonomy-selected-chips-' + taxonomy );
			var $moreButton = $( '#taxonomy-chips-more-' + taxonomy );
			
			if( !$moreButton.length ) {
				return;
			}
			
			// Close any existing popover
			this.closeOverflowPopover( taxonomy );
			
			// Generate popover content
			var popoverHtml = '<div class="taxonomy-chips-popover" data-taxonomy="' + taxonomy + '">';
			popoverHtml += '<div class="popover-content">';
			
			this.taxonomies[ taxonomy ].selectedTermsMap.forEach( function( termData, termId ) {
				popoverHtml += '<button class="popover-chip" data-term-id="' + termId + '" ' +
					'aria-label="' + self.escapeHtml( AdminEaseTaxonomyMetaBoxAjaxObj.i18n.removeSelected.replace( '%s', termData.name ) ) + '" ' +
					'title="' + self.escapeHtml( termData.ancestry || termData.name ) + '">' +
					'<span class="chip-text">' + self.escapeHtml( termData.name ) + '</span>' +
					'<span class="chip-remove" aria-hidden="true">×</span>' +
					'</button>';
			} );
			
			popoverHtml += '</div></div>';
			
			// Position and show popover
			$( 'body' ).append( popoverHtml );
			
			var $popover = $( '.taxonomy-chips-popover[data-taxonomy="' + taxonomy + '"]' );
			var containerOffset = $container.offset();
			var containerWidth = $container.outerWidth();
			
			$popover.css( {
				position: 'absolute',
				top: containerOffset.top + $container.outerHeight() + 5,
				left: containerOffset.left,
				width: containerWidth,
				zIndex: 9999,
			} );
			
			// Bind popover chip removal
			$popover.on( 'click', '.popover-chip', function() {
				var termId = parseInt( $( this ).data( 'term-id' ) );
				self.removeSelectedTerm( taxonomy, termId );
				self.updateHiddenInputs( taxonomy );
				self.renderChips( taxonomy );
				
				// Uncheck corresponding checkbox if visible
				$( '#adminease-taxonomy-tree-' + taxonomy + ' input[value="' + termId + '"]' ).prop( 'checked', false );
				
				// Update popover or close if empty
				if( self.taxonomies[ taxonomy ].selectedTermsMap.size === 0 ) {
					self.closeOverflowPopover( taxonomy );
				}
				else {
					// Refresh popover content
					self.closeOverflowPopover( taxonomy );
					self.openOverflowPopover( taxonomy );
				}
			} );
		},
		
		closeOverflowPopover: function( taxonomy ) {
			$( '.taxonomy-chips-popover[data-taxonomy="' + taxonomy + '"]' ).remove();
		},
		toggleClearButton: function( taxonomy, query ) {
			var clearButton = $( '#adminease-search-clear-' + taxonomy );
			if( query && query.length > 0 ) {
				clearButton.show();
			}
			else {
				clearButton.hide();
			}
		},
		clearSearch: function( taxonomy ) {
			var searchInput = $( '#adminease-taxonomy-search-' + taxonomy );
			var clearButton = $( '#adminease-search-clear-' + taxonomy );
			
			// Clear the input
			searchInput.val( '' ).focus();
			
			// Hide clear button
			clearButton.hide();
			
			// Reset search state
			this.taxonomies[ taxonomy ].searchQuery = '';
			this.taxonomies[ taxonomy ].currentPage = 1;
			
			// Load original terms
			this.loadTerms( taxonomy, 1 );
		},
		handleSearch: function( taxonomy, query ) {
			var self = this;
			
			// Clear existing timeout
			if( this.searchTimeouts[ taxonomy ] ) {
				clearTimeout( this.searchTimeouts[ taxonomy ] );
			}
			
			// Show search spinner
			this.showSearchSpinner( taxonomy, true );
			
			// Set new timeout
			this.searchTimeouts[ taxonomy ] = setTimeout( function() {
				self.taxonomies[ taxonomy ].searchQuery = query;
				self.taxonomies[ taxonomy ].currentPage = 1;
				
				if( query.length > 0 ) {
					self.searchTerms( taxonomy, query );
				}
				else {
					self.loadTerms( taxonomy, 1 );
				}
			}, this.config.searchDelay );
		},
		showSearchSpinner: function( taxonomy, show ) {
			var spinner = $( '#adminease-search-spinner-' + taxonomy );
			var searchContainer = $( '.adminease-taxonomy-search' ).has( '#adminease-taxonomy-search-' + taxonomy );
			
			if( show ) {
				spinner.addClass( 'searching' );
				searchContainer.addClass( 'searching' );
			}
			else {
				spinner.removeClass( 'searching' );
				searchContainer.removeClass( 'searching' );
			}
		},
		loadTerms: function( taxonomy, page, append, callback ) {
			var self = this;
			
			if( self.taxonomies[ taxonomy ].loading ) {
				return;
			}
			
			page = page || 1;
			append = append || false;
			
			self.taxonomies[ taxonomy ].loading = true;
			
			var $tree = $( '#adminease-taxonomy-tree-' + taxonomy );
			var $pagination = $( '#adminease-taxonomy-pagination-' + taxonomy );
			var $pageInput = $( '#adminease-goto-page-' + taxonomy );
			
			if( !append ) {
				$tree.html( '<div class="adminease-loading">' + AdminEaseTaxonomyMetaBoxAjaxObj.i18n.loadingText + '</div>' );
			}
			
			var data = {
				action: 'adminease_load_taxonomy_terms',
				taxonomy: taxonomy,
				page: page,
				per_page: self.config.termsPerPage,
				search: self.taxonomies[ taxonomy ].searchQuery,
				post_id: $( '#post_ID' ).val() || 0,
				security: AdminEaseTaxonomyMetaBoxAjaxObj.security,
			};
			
			$.post( AdminEaseTaxonomyMetaBoxAjaxObj.ajaxUrl, data, function( response ) {
				self.taxonomies[ taxonomy ].loading = false;
				
				if( response.success ) {
					// Update pagination data
					self.taxonomies[ taxonomy ].currentPage = response.data.pagination.current_page;
					self.taxonomies[ taxonomy ].totalPages = response.data.pagination.total_pages;
					self.taxonomies[ taxonomy ].termsLoaded = true;
					
					// Update the page input's max attribute and value
					$pageInput.attr( 'max', self.taxonomies[ taxonomy ].totalPages );
					$pageInput.val( self.taxonomies[ taxonomy ].currentPage );
					
					// Update terms display
					if( append ) {
						$tree.find( '.adminease-load-more' ).remove();
						$tree.find( '.adminease-terms-list' ).append( response.data.terms_html );
					}
					else {
						$tree.html( response.data.terms_html );
					}
					
					// Update pagination
					$pagination.html( response.data.pagination_html );
					
					// Handle selected terms if this is initial load
					if( self.taxonomies[ taxonomy ].pendingSelectedTerms && self.taxonomies[ taxonomy ].pendingSelectedTerms.length > 0 ) {
						// Wait a bit for the HTML to be rendered, then try to get names from HTML
						setTimeout( function() {
							self.populateSelectedTermsFromHTML( taxonomy, self.taxonomies[ taxonomy ].pendingSelectedTerms );
							delete self.taxonomies[ taxonomy ].pendingSelectedTerms;
						}, 100 );
					}
					
					if( callback ) {
						callback();
					}
				}
				else {
					$tree.html( '<div class="adminease-error">' + ( response.data.message || 'Error loading terms' ) + '</div>' );
				}
			} ).fail( function() {
				self.taxonomies[ taxonomy ].loading = false;
				$tree.html( '<div class="adminease-error">Failed to load terms</div>' );
			} );
		},
		populateSelectedTermsMap: function( taxonomy, selectedTermIds ) {
			var self = this;
			
			if( !selectedTermIds || selectedTermIds.length === 0 ) {
				return;
			}
			
			// Make an AJAX request to get the term names for the selected term IDs
			var data = {
				action: 'adminease_get_term_names',
				taxonomy: taxonomy,
				term_ids: selectedTermIds,
				security: AdminEaseTaxonomyMetaBoxAjaxObj.security,
			};
			
			$.post( AdminEaseTaxonomyMetaBoxAjaxObj.ajaxUrl, data, function( response ) {
				if( response.success && response.data.terms ) {
					// Update the selected terms map with the actual names
					response.data.terms.forEach( function( term ) {
						self.taxonomies[ taxonomy ].selectedTermsMap.set( parseInt( term.id ), {
							id: parseInt( term.id ),
							name: term.name,
							taxonomy: taxonomy,
						} );
					} );
					
					// Re-render chips with updated names
					self.renderChips( taxonomy );
				}
			} ).fail( function() {
				// Fallback: try to extract names from the loaded HTML
				self.populateSelectedTermsFromHTML( taxonomy, selectedTermIds );
			} );
		},
		populateSelectedTermsFromHTML: function( taxonomy, selectedTermIds ) {
			var self = this;
			
			if( !selectedTermIds || selectedTermIds.length === 0 ) {
				return;
			}
			
			// Make an AJAX request to get the term names for the selected term IDs
			var data = {
				action: 'adminease_get_term_names',
				taxonomy: taxonomy,
				term_ids: selectedTermIds,
				security: AdminEaseTaxonomyMetaBoxAjaxObj.security,
			};
			
			$.post( AdminEaseTaxonomyMetaBoxAjaxObj.ajaxUrl, data, function( response ) {
				if( response.success && response.data.terms ) {
					// Update the selected terms map with the actual names
					response.data.terms.forEach( function( term ) {
						self.taxonomies[ taxonomy ].selectedTermsMap.set( parseInt( term.id ), {
							id: parseInt( term.id ),
							name: term.name,
							taxonomy: taxonomy,
						} );
					} );
					
					// Re-render chips with updated names
					self.renderChips( taxonomy );
				}
				else {
					console.log( 'Failed to get term names:', response );
					// Fallback: try to extract names from the loaded HTML
					self.populateSelectedTermsFromHTML( taxonomy, selectedTermIds );
				}
			} ).fail( function( xhr, status, error ) {
				console.log( 'AJAX request failed:', error );
				// Fallback: try to extract names from the loaded HTML
				self.populateSelectedTermsFromHTML( taxonomy, selectedTermIds );
			} );
		},
		searchTerms: function( taxonomy, query ) {
			var self = this;
			var container = $( '#adminease-taxonomy-tree-' + taxonomy );
			var paginationContainer = $( '#adminease-taxonomy-pagination-' + taxonomy );
			
			// Get post ID
			var postId = $( '#post_ID' ).val() || 0;
			
			$.post( AdminEaseTaxonomyMetaBoxAjaxObj.ajaxUrl, {
					action: 'adminease_search_taxonomy_terms',
					taxonomy: taxonomy,
					search: query,
					post_id: postId,
					security: AdminEaseTaxonomyMetaBoxAjaxObj.security,
				} )
				.done( function( response ) {
					if( response.success ) {
						// Render search results (you can also move this to server side)
						self.renderSearchResults( taxonomy, response.data );
					}
					else {
						var errorMessage = 'Search failed';
						if( response.data && response.data.message ) {
							errorMessage = response.data.message;
						}
						container.html( '<div class="adminease-error">' + errorMessage + '</div>' );
					}
				} )
				.fail( function() {
					container.html( '<div class="adminease-error">Search network error</div>' );
				} )
				.always( function() {
					self.showSearchSpinner( taxonomy, false );
					paginationContainer.empty(); // Clear pagination for search results
				} );
		},
		renderSearchResults: function( taxonomy, data ) {
			var container = $( '#adminease-taxonomy-tree-' + taxonomy );
			var html = '';
			
			// Show search results count
			if( data.terms && data.terms.length > 0 ) {
				// Show search results
				html += '<ul class="adminease-terms-list">';
				
				data.terms.forEach( function( term ) {
					var isSelected = this.taxonomies[ taxonomy ].selectedTerms.indexOf( term.id ) !== -1;
					html += '<li class="adminease-term-item">';
					html += '<label>';
					html += '<input type="checkbox" value="' + term.id + '"' + ( isSelected ? ' checked' : '' ) + '>';
					html += '<span class="adminease-term-name">' + term.name + '</span>';
					if( term.count > 0 ) {
						html += ' <span class="adminease-term-count">(' + term.count + ')</span>';
					}
					html += '</label>';
					html += '</li>';
				}.bind( this ) );
				
				html += '</ul>';
			}
			else {
				html += '<div class="adminease-no-results">';
				html += '<p>' + AdminEaseTaxonomyMetaBoxAjaxObj[ 'i18n' ][ 'noResultsText' ] + '</p>';
				
				// Show "Create term" option if user has permission
				if( data.can_create && data.search_term ) {
					html += '<p>';
					html += '<a href="#" class="adminease-create-term" data-taxonomy="' + taxonomy + '" data-term-name="' + this.escapeHtml( data.search_term ) + '">';
					html += 'Create "' + this.escapeHtml( data.search_term ) + '"';
					html += '</a>';
					html += '</p>';
				}
				
				html += '</div>';
			}
			
			container.html( html );
			
			// Clear pagination when showing search results
			$( '#adminease-taxonomy-pagination-' + taxonomy ).empty();
		},
		createTerm: function( taxonomy, termName, parentId ) {
			var self = this;
			
			parentId = parentId || 0;
			
			if( this.taxonomies[ taxonomy ].loading ) {
				return;
			}
			
			this.taxonomies[ taxonomy ].loading = true;
			this.showSearchSpinner( taxonomy, true );
			
			$.post( AdminEaseTaxonomyMetaBoxAjaxObj.ajaxUrl, {
					action: 'adminease_create_taxonomy_term',
					taxonomy: taxonomy,
					term_name: termName,
					parent_id: parentId,
					security: AdminEaseTaxonomyMetaBoxAjaxObj.security,
				} )
				.done( function( response ) {
					if( response.success && response.data ) {
						// Select the newly created term
						if( response.data.id ) {
							self.selectTerm( taxonomy, response.data.id );
						}
						
						// Clear search input and reset search state
						$( '#adminease-taxonomy-search-' + taxonomy ).val( '' );
						self.taxonomies[ taxonomy ].searchQuery = '';
						self.taxonomies[ taxonomy ].currentPage = 1;
						
						// Force refresh all terms to show the newly created term
						self.taxonomies[ taxonomy ].loading = false;
						self.loadTerms( taxonomy, 1 );
						
						// Show success message
						var successMessage = 'Term created successfully!';
						
						if( response.data.name ) {
							successMessage = 'Term "' + response.data.name + '" created successfully!';
						}
						self.showSuccessMessage( taxonomy, successMessage );
					}
					else {
						var errorMessage = 'Failed to create term';
						
						if( response.data && response.data.message ) {
							errorMessage = response.data.message;
						}
						self.showError( taxonomy, errorMessage );
					}
				} )
				.fail( function() {
					self.showError( taxonomy, 'Network error while creating term' );
				} )
				.always( function() {
					self.taxonomies[ taxonomy ].loading = false;
					self.showSearchSpinner( taxonomy, false );
				} );
		},
		showError: function( taxonomy, message ) {
			var container = $( '#adminease-taxonomy-tree-' + taxonomy );
			var errorHtml = '<div class="adminease-error" style="padding: 12px; background: #f8d7da; border: 1px solid #f5c2c7; border-radius: 3px; color: #842029; font-size: 12px; margin-bottom: 12px;">' + message + '</div>';
			
			container.prepend( errorHtml );
			
			// Remove error message after 5 seconds
			setTimeout(
				function() {
					$( '.adminease-error' ).fadeOut();
				},
				5000,
			);
		},
		showSuccessMessage: function( taxonomy, message ) {
			var container = $( '#adminease-taxonomy-tree-' + taxonomy );
			var successHtml = '<div class="adminease-success" style="padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 3px; color: #155724; font-size: 12px; margin-bottom: 12px;">' + message + '</div>';
			
			container.prepend( successHtml );
			
			// Remove success message after 3 seconds
			setTimeout(
				function() {
					$( '.adminease-success' ).fadeOut();
				},
				3000,
			);
		},
		selectTerm: function( taxonomy, termId ) {
			if( this.taxonomies[ taxonomy ].selectedTerms.indexOf( termId ) === -1 ) {
				this.taxonomies[ taxonomy ].selectedTerms.push( termId );
			}
			
			// Add hidden input for form submission
			var hiddenInput = '<input type="checkbox" name="tax_input[' + taxonomy + '][]" value="' + termId + '" checked="checked" style="display: none;" />';
			
			$( '#adminease-taxonomy-' + taxonomy ).append( hiddenInput );
		},
		deselectTerm: function( taxonomy, termId ) {
			var index = this.taxonomies[ taxonomy ].selectedTerms.indexOf( termId );
			
			if( index > -1 ) {
				this.taxonomies[ taxonomy ].selectedTerms.splice( index, 1 );
			}
			
			// Remove hidden input
			$( '#adminease-taxonomy-' + taxonomy + ' input[name="tax_input[' + taxonomy + '][]"][value="' + termId + '"]' ).remove();
		},
		renderTerms: function( taxonomy, terms, pagination, append ) {
			var container = $( '#adminease-taxonomy-tree-' + taxonomy );
			var isHierarchical = $( '#adminease-taxonomy-' + taxonomy ).data( 'hierarchical' ) === true;
			var html = '';
			
			// Remove the loading indicator if it exists
			container.find( '.' + this.config.loadingClass ).remove();
			
			if( !append ) {
				container.empty();
			}
			
			if( terms && terms.length > 0 ) {
				// Find existing term list if appending
				var existingList = container.find( '.adminease-terms-list' );
				
				if( isHierarchical ) {
					html = this.renderHierarchicalTerms( terms, taxonomy );
				}
				else {
					html = this.renderFlatTerms( terms, taxonomy );
				}
				
				if( append && existingList.length > 0 ) {
					existingList.append( html );
				}
				else {
					container.html( '<ul class="adminease-terms-list">' + html + '</ul>' );
				}
			}
			else {
				container.html( '<div class="adminease-no-terms">No terms found</div>' );
			}
		},
		renderHierarchicalTerms: function( terms, taxonomy ) {
			var html = '';
			var self = this;
			
			terms.forEach( function( term ) {
				var isSelected = self.taxonomies[ taxonomy ].selectedTerms.indexOf( term.id ) !== -1;
				
				html += '<li class="adminease-term-item" data-term-id="' + term.id + '">';
				html += '<label class="adminease-term-label">';
				html += '<input type="checkbox" value="' + term.id + '" ' + ( isSelected ? 'checked' : '' ) + ' />';
				html += '<span class="adminease-term-name">' + self.escapeHtml( term.name ) + '</span>';
				
				if( term.count > 0 ) {
					html += ' <span class="adminease-term-count">(' + term.count + ')</span>';
				}
				
				html += '</label>';
				html += '</li>';
			} );
			
			return html;
		},
		renderFlatTerms: function( terms, taxonomy ) {
			return this.renderHierarchicalTerms( terms, taxonomy ); // Same rendering for now
		},
	};
	
	// Initialize when DOM is ready
	AdminEaseTaxonomyMetaBox.initAll();
	
	// Make it globally accessible
	window.AdminEaseTaxonomyMetaBox = AdminEaseTaxonomyMetaBox;
} );