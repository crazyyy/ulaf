/**
 * This is the javascript file for the module.
 *
 * @package UltimaKit
 */

(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery( document ).ready(
		function ($) {	
			// Modern Dashboard Functionality
			initializeModernDashboard();
			
			const toastConf = {
				timeOut: 1000, // Adjust display time as needed (in milliseconds).
				positionClass: 'toast-top-center', // Adjust position as needed.
				progressBar: true, // Show a progress bar.
				closeButton: true,
				preventDuplicates: true,
				iconClasses: {
					success: "toast-success",
					warning: "toast-warning" // Specify a single CSS class for warning messages.
				},
			};
			
			// Initialize Modern Dashboard
			function initializeModernDashboard() {
				// Category Navigation
				$('.category-item').on('click', function() {
					const category = $(this).data('category');
					const categoryName = $(this).find('.category-name').text();
					const moduleCount = $(this).find('.category-count').text();
					
					// Update active state
					$('.category-item').removeClass('active');
					$(this).addClass('active');
					
					// Content header removed - no longer need to update title and count
					
					// Show/hide content
					if (category === 'all') {
						$('#modules-grid').show();
						$('.category-content').hide();
					} else {
						$('#modules-grid').hide();
						$('.category-content').hide();
						$('#category-' + category).show();
					}
					
					// Apply current filters
					applyFilters();
				});
				

				
				// Search Functionality
				$('#ultimakit_search_module').on('input', function() {
					applyFilters();
				});
				
				// View Toggle
				$('#ultimakit_small_screen').on('click', function() {
					$('.ultimakit-modern-dashboard').addClass('compact-view');
					updateOption('ultimakit_modules_list_view', 'small');
					
					// Update button states
					$(this).addClass('active').siblings().removeClass('active');
					
					// Show feedback
					showToast('Compact view enabled', 'success');
				});
				
				$('#ultimakit_full_screen').on('click', function() {
					$('.ultimakit-modern-dashboard').removeClass('compact-view');
					updateOption('ultimakit_modules_list_view', 'full');
					
					// Update button states
					$(this).addClass('active').siblings().removeClass('active');
					
					// Show feedback
					showToast('Full view enabled', 'success');
				});
				
				// Initialize view mode
				const viewMode = getOption('ultimakit_modules_list_view');
				if (viewMode === 'small') {
					$('.ultimakit-modern-dashboard').addClass('compact-view');
					$('#ultimakit_small_screen').addClass('active');
				} else {
					$('#ultimakit_full_screen').addClass('active');
				}
			}
			
			// Apply Filters Function
			function applyFilters() {
				const search = $('#ultimakit_search_module').val().toLowerCase();
				
				// Reset category counts if search is empty
				if (!search) {
					// Remove search highlighting from all categories
					$('.category-item').removeClass('has-search-results');
					updateCategoryCountsForSearch();
					return;
				}
				
				$('.module-block').each(function() {
					const $module = $(this);
					const moduleName = $module.find('.module-title').text().toLowerCase();
					const moduleDesc = $module.find('.module-description').text().toLowerCase();
					
					let show = true;
					
					// Search filter
					if (search && !moduleName.includes(search) && !moduleDesc.includes(search)) {
						show = false;
					}
					
					$module.toggle(show);
				});
				
				// Update category counts based on search results
				updateCategoryCountsForSearch();
			}
			
			// Update category counts based on search results
			function updateCategoryCountsForSearch() {
				const search = $('#ultimakit_search_module').val().toLowerCase();
				
				// Update "All Categories" count
				const totalVisibleModules = $('.module-block').filter(function() {
					return $(this).css('display') !== 'none';
				}).length;
				$('.category-item[data-category="all"] .category-count').text(Math.floor(totalVisibleModules / 2));
				
				// Update individual category counts
				$('.category-item').each(function() {
					const $categoryItem = $(this);
					const categorySlug = $categoryItem.data('category');
					
					if (categorySlug === 'all') {
						return; // Already handled above
					}
					
					// Count visible modules in this category
					const categoryContent = $('#category-' + categorySlug);
					let visibleCount = 0;
					
					if (categoryContent.length > 0) {
						// Count visible modules in category-specific content
						visibleCount = categoryContent.find('.module-block').filter(function() {
							const $module = $(this);
							const moduleName = $module.find('.module-title').text().toLowerCase();
							const moduleDesc = $module.find('.module-description').text().toLowerCase();
							
							// If searching, check if module matches search
							if (search) {
								return $(this).css('display') !== 'none' && 
									   (moduleName.includes(search) || moduleDesc.includes(search));
							}
							
							return $(this).css('display') !== 'none';
						}).length;
					}
					
					// Update the count
					$categoryItem.find('.category-count').text(visibleCount);
					
					// Add/remove background color based on search results
					if (search && visibleCount > 0) {
						$categoryItem.addClass('has-search-results');
					} else {
						$categoryItem.removeClass('has-search-results');
					}
				});
				
				// Also handle "All Categories" highlighting
				if (search && totalVisibleModules > 0) {
					$('.category-item[data-category="all"]').addClass('has-search-results');
				} else {
					$('.category-item[data-category="all"]').removeClass('has-search-results');
				}
			}
			
			// Helper function to get option
			function getOption(key) {
				return localStorage.getItem(key) || 'full';
			}
			
			// Helper function to update option
			function updateOption(key, value) {
				localStorage.setItem(key, value);
			}
			
			// Show toast notification
			function showToast(message, type = 'info') {
				const toastConfig = {
					timeOut: 2000,
					positionClass: 'toast-top-center',
					progressBar: true,
					closeButton: true,
					preventDuplicates: true,
					iconClasses: {
						success: "toast-success",
						warning: "toast-warning",
						error: "toast-error",
						info: "toast-info"
					},
				};
				
				toastr[type](message, '', toastConfig);
			}
			
			// Migrate settings
		    $('#ultimakit_migrate_settings').on('click', function(e) {
		        e.preventDefault();

				const toastConf = {
					timeOut: 1000, // Adjust display time as needed (in milliseconds).
					positionClass: 'toast-top-right', // Adjust position as needed.
					progressBar: true, // Show a progress bar.
					closeButton: true,
					preventDuplicates: true,
					iconClasses: {
						success: "toast-success",
				        warning: "toast-warning" // Specify a single CSS class for warning messages.
				    },
				};

		      	$.ajax({
				    url: ultimakit_ajax.url,
				    type: 'POST',
				    data: {
				        action: 'ultimakit_migrate_settings',
						nonce: ultimakit_ajax.nonce
				    },
				    success: function (response) {
						toastr.success( 'Settings have been migrated successfully.', '', toastConf );

		            	setTimeout(function(){
		            		window.location.reload();
		            	},1000);
				    },
				    error: function () {
				        toastr.error('Error: AJAX request failed');
				    },
				});
		    });

			// Export settings
		    $('#ultimakit_export_settings').on('click', function(e) {
				e.preventDefault();
			
				$.ajax({
					url: ultimakit_ajax.url,
					type: 'POST',
					data: {
						action: 'export_ultimakit_settings',
					},
					success: function (response) {
						if (response.success) {
							toastr.success('Settings have been exported successfully.', '', toastConf);
							
							// Trigger download by creating a temporary link
							const file_url = response.data.file_url;
							const link = document.createElement('a');
							link.href = file_url;
							link.download = file_url.split('/').pop(); // Set the download attribute with the filename
							document.body.appendChild(link);
							link.click();
							document.body.removeChild(link);
						} else {
							toastr.error(response.data || 'An error occurred while exporting.');
						}
					},
					error: function () {
						toastr.error('Error: AJAX request failed');
					},
				});
			});

		    // Import settings
		    $('#ultimakit_import_settings').on('change', function(e) {
		        e.preventDefault();

		        var file_data = $('#ultimakit_import_settings').prop('files')[0];
		        var form_data = new FormData();
		        form_data.append('json_file', file_data);
		        form_data.append('action', 'import_ultimakit_settings'); // WordPress AJAX action
		        form_data.append('nonce', ultimakit_ajax.nonce); // Security nonce

		        const toastConf = {
					timeOut: 1000, // Adjust display time as needed (in milliseconds).
					positionClass: 'toast-top-right', // Adjust position as needed.
					progressBar: true, // Show a progress bar.
					closeButton: true,
					preventDuplicates: true,
					iconClasses: {
						success: "toast-success",
				        warning: "toast-warning" // Specify a single CSS class for warning messages.
				    },
				};

		        $.ajax({
		            url: ultimakit_ajax.url, // WordPress admin AJAX URL
		            type: 'POST',
		            contentType: false,
		            processData: false,
		            data: form_data,
		            success: function (response) {
		            	toastr.success( 'Settings have been imported successfully.', '', toastConf );

		            	setTimeout(function(){
		            		window.location.reload();
		            	},1000);
		            },
		            error: function (response) {
		                toastr.error( 'Failed to import settings.', '', toastConf );
		            }
		        });

		    });

			let settingsActions = $( '.ultimakit_settings_action' );
			settingsActions.on(
				'change',
				function (event) {
					const restUrl   = ultimakit_ajax.url;
					const toastConf = {
						timeOut: 1000, // Adjust display time as needed (in milliseconds).
						positionClass: 'toast-top-center', // Adjust position as needed.
						progressBar: true, // Show a progress bar.
						closeButton: true,
						preventDuplicates: true,
						iconClasses: {
							success: "toast-success",
					        warning: "toast-warning" // Specify a single CSS class for warning messages.
					    },
					};

					// The event object contains information about the change event.
					if (event.target.checked) {
						status = 'on';
					} else {
						status = 'off';
					}

					// Make a REST API request.
					$.ajax(
					{
						url: restUrl, // Use your endpoint route.
						type: 'POST',
						data: {
							action: 'ultimakit_uninstall_settings',
							stat: status,
							nonce: ultimakit_ajax.nonce,
						},
						beforeSend: function (xhr) {
							xhr.setRequestHeader( 'X-WP-Nonce', ultimakit_ajax.nonce ); // Include the nonce in the request header.
						},
						success: function (response) {
							toastr.success( 'Settings Updated', '', toastConf );
						},
						error: function () {
							// Handle errors.
							toastr.error( 'Error: AJAX request failed', '', toastConf );
						},
					});
				}
			);

			// Select the checkbox element by its ID.
			let checkbox = $( '.ultimakit_module_action' );
			// Add a change event listener to the checkbox.
			checkbox.on(
				'change',
				function (event) {
					let module_id     = jQuery( this ).attr( 'id' );
					let module_status = '';
					let module_name = jQuery( this ).attr( 'module-name' );

					// The event object contains information about the change event.
					if (event.target.checked) {
						module_status = 'on';
					} else {
						module_status = 'off';
					}

					const restUrl   = ultimakit_ajax.url;
					const toastConf = {
						timeOut: 1500, // Adjust display time as needed (in milliseconds).
						positionClass: 'toast-top-center', // Adjust position as needed.
						progressBar: true, // Show a progress bar.
						closeButton: true,
						preventDuplicates: true,
						"showEasing": "linear",
						"hideEasing": "linear",
						"showMethod": "fadeIn",
						"hideMethod": "fadeOut",
						iconClasses: {
							success: "toast-success",
					        warning: "toast-warning" 
					    },
					};
					// Make a REST API request.
					$.ajax(
						{
							url: restUrl, // Use your endpoint route.
							type: 'POST',
							data: {
								action: 'ultimakit_update_settings',
								module_id: module_id,
								module_status: module_status,
								nonce: ultimakit_ajax.nonce,
							},
							beforeSend: function (xhr) {
								xhr.setRequestHeader( 'X-WP-Nonce', ultimakit_ajax.nonce ); // Include the nonce in the request header.
							},
							success: function (response) {
								if ('on' === response.data.status) {
									toastr.success( module_name + ' ' + response.data.message, '', toastConf );
									if ( 'on' == module_status ) {
										jQuery( '.' + module_id ).show();
									} else {
										jQuery( '.' + module_id ).hide();
									}
									setTimeout(
										function () {
											window.location.reload();
										},
										1500
									);
								} else {
									toastr.error( module_name + ' ' + response.data.message, '', toastConf );

									setTimeout(
										function () {
											window.location.reload();
										},
										1500
									);
								}
							},
							error: function () {
								// Handle errors.
								toastr.error( 'Error: AJAX request failed', '', toastConf );
							},
						}
					);
				}
			);

			$( '.module_settings' ).submit(
				function (e) {
					e.preventDefault(); // Prevent the default form submit.
					let settingData = {};
					let module_id   = $( this ).attr( 'id' );

					$( '#' + module_id ).find( ':input' ).each(
						function () {
							if (this.name && ! this.disabled) {
								if ( 'checkbox' == this.type || 'radio' == this.type ) {
									if ($( this ).prop( 'checked' )) {
										settingData[this.name] = 'on';
									} else {
										settingData[this.name] = 'off';
									}
								} else {
									if( this.type !== 'html' ){
										settingData[this.name] = $( this ).val();
									}
								}
							}
						}
					);

					let customOption = null;
					if ($('.wpuk_save_module_settings').length > 0) {
					    customOption = $('.wpuk_save_module_settings').attr('custom-option');
						settingData['custom_option'] = customOption;
					}

					// You can use 'inputData' as needed, like sending to server via AJAX.
					const restUrl   = ultimakit_ajax.url;
					const toastConf = {
						timeOut: 1000, // Adjust display time as needed (in milliseconds).
						positionClass: 'toast-top-right', // Adjust position as needed.
						progressBar: true, // Show a progress bar.
						closeButton: true,
						preventDuplicates: true,
						iconClasses: {
							success: "toast-success",
					    },
					};
					
					// Make a REST API request.
					$.ajax(
						{
							url: restUrl, // Use your endpoint route.
							type: 'POST',
							data: {
								action: 'ultimakit_update_settings',
								module_id: module_id.replace( '_form', '' ),
								module_settings: settingData,
								nonce: ultimakit_ajax.nonce,
								save_mode: 'settings'
							},
							beforeSend: function (xhr) {
								xhr.setRequestHeader( 'X-WP-Nonce', ultimakit_ajax.nonce ); // Include the nonce in the request header.
							},
							success: function (response) {
								if (response.success) {
									toastr.success( response.data.message, '', toastConf );
									setTimeout(
										function () {
											$( '.wpuk_modal' ).hide();
											window.location.reload();
										},
										1000
									);
								} else {
									// Display an error toast.
									toastr.success( 'Success: ' + response.data.message, '', toastConf );
								}
							},
							error: function () {
								// Handle errors.
								toastr.error( 'Error: AJAX request failed', '', toastConf );
							},
						}
					);
				}
			);


			
			// Helper function to update category counts based on visible modules
			function updateCategoryCounts() {
				$('#wpukTabsInner li').each(function() {
					const categoryId = $(this).find('a').attr('href');
					
					// Count visible modules
					const visibleModules = $(categoryId + ' .module-block').filter(function() {
						return $(this).css('display') !== 'none';
					}).length;
					
					// Update count and visibility of category tab
					$(this).find('.cat_count strong').text('[' + visibleModules + ']');
					$(this).toggle(visibleModules > 0);
				});
				
				// Also update sidebar category counts
				updateCategoryCountsForSearch();
			}
			
			$("#moduleFilterForm").on('submit', function(event) {
				event.preventDefault();
			});

			$('#ultimakit_search_module').on('input', function(event) {
				event.preventDefault();
				// Prevent form submission when Enter key is pressed in the search field
				const keyCode = event.keyCode || event.which;
				if (keyCode === 13) {
					event.preventDefault();
				}

				const query = $(this).val().toLowerCase().trim();
				
				// Reset display if search is cleared
				if (query.length === 0) {
					$('.module-block').show();
					$("#wp-modules-tab").find('span').remove();
					
					// Remove search highlighting from all categories
					$('.category-item').removeClass('has-search-results');
					
					// Reset category counts
					updateCategoryCountsForSearch();
					return;
				}
				
				// Only search if query is at least 2 characters
				if (query.length < 2) return;
				
				// Track modules by type
				let moduleCounts = {
					WordPress: 0,
				};
				
				// Search through modules
				$('.module-block').each(function() {
					const $module = $(this);
					const title = $module.find('.module-title').text().toLowerCase();
					const description = $module.find('.module-description').text().toLowerCase();
					
					// Check if module matches search query
					const isMatch = title.includes(query) || description.includes(query);
					$module.toggle(isMatch);
					
					// Count matches by module type
					if (isMatch) {
						const moduleType = $module.find('.module-box').attr('class').split(' ')
							.find(cls => cls === 'WordPress');
						
						if (moduleType) {
							moduleCounts[moduleType]++;
						}
					}
				});
				
				// Update tab counts
				wpuk_updateTabCount("#wp-modules-tab", moduleCounts.WordPress);
				
				// Update category counts based on search results
				updateCategoryCountsForSearch();
				
				// Activate WordPress tab if it has matches
				if (moduleCounts.WordPress > 0) {
					$('#wp-modules').addClass('active show');
					$('#wpukTabs li a').removeClass('active');
					$('#wp-modules-tab').addClass('active');

					$('#wpukTabsInner li a:first-child').addClass('show');
				}
			});
			
			// Function to dynamically add or update tab count
			function wpuk_updateTabCount(tabSelector, count) {
				const $tab = $(tabSelector);
				const $span = $tab.find('span');
			
				// If span doesn't exist, create it
				if ($span.length === 0) {
					$tab.append('<span><strong>[' + count + ']</strong></span>');
				} else {	
					$span.text('[' + count + ']');
				}
			}
			
			// Remove span tags on page refresh
			$(window).on('beforeunload', function() {
				$("#wp-modules-tab").find('span').remove();
				$("#wc-modules-tab").find('span').remove();
			});
			
			function ultimakit_update_module_width( view = 'full' ) {
				$.ajax({
				    url: ultimakit_ajax.url,
				    type: 'POST',
				    data: {
				        action: 'ultimakit_update_module_width',
						view: view,
						nonce: ultimakit_ajax.nonce
				    },
				    success: function (response) {
				    },
				    error: function (error) {
				        toastr.error('Error: AJAX request failed');
				    },
				});
			}

		    $('#ultimakit_small_screen').on('click', function(e) {
		    	e.preventDefault();

				ultimakit_update_module_width('small');

		    	 // Loop through each module-block
		        $('.module-block').each(function() {
		        	$(this).find('.module-description').hide();
		        	$(this).find('.module-box').css('height','100px');
		        });

		    });

		    $('#ultimakit_full_screen').on('click', function(e) {
		    	e.preventDefault();

				ultimakit_update_module_width('full');

		    	 // Loop through each module-block
		        $('.module-block').each(function() {
		        	$(this).find('.module-description').show();
		        	$(this).find('.module-box').css('height','200px');
		        });
		    });
			
			$(document).ready(function() {
				// Only target tabs within #wpukTabs
				$('#wpukTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
					// Get the href (tab ID) of the active tab
					var activeTabId = $(e.target).attr('href').substring(1);
					localStorage.setItem('wpukActiveTab', activeTabId); // Changed key name to be more specific
				});
			
				// Check for active tab in localStorage and activate it
				var activeTab = localStorage.getItem('wpukActiveTab');
				if (activeTab) {
					$(`#wpukTabs a[href="#${activeTab}"]`).tab('show');
				}
			});
		}
	);
	

	

	
	document.addEventListener('DOMContentLoaded', function() {
	    var texts = document.querySelectorAll('selector-for-element-containing-text'); // Replace with actual selector
	    texts.forEach(function(element) {
	        if (element.textContent.includes('W00t! Premium plugin version was successfully activated.')) {
	            element.textContent = element.textContent.replace('W00t! Premium plugin version was successfully activated.', 'Ultimakit For WP Pro plugin version was successfully activated.');
	        }
	    });
	});



})( jQuery );