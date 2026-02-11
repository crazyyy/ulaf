/**
 * VigIA - Extras Page Scripts
 *
 * Handles robots.txt management, blocking, email alerts, and LLMs generator.
 *
 * @package VigIA
 * @since 1.2.0
 */

/* global vigiaData, jQuery */

(function($) {
    'use strict';

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Only run on extras page
        if ($('.vigia-extras-wrap').length === 0) {
            return;
        }

        initRobotsTab();
        initEmailTab();
        initLlmsTab();
    });

    // ==========================================================================
    // Robots.txt & Blocking Tab
    // ==========================================================================

    /**
     * Initialize robots tab functionality
     */
    function initRobotsTab() {
        // Add disallow rule
        $('#vigia-add-disallow').on('click', function() {
            var crawlerName = $('#vigia-robots-crawler').val();
            if (!crawlerName) {
                alert(vigiaData.strings.selectCrawler || 'Please select a crawler');
                return;
            }
            addRobotsRule(crawlerName, 'disallow');
        });

        // Remove robots rule
        $(document).on('click', '.vigia-remove-robots-rule', function() {
            var crawlerName = $(this).data('crawler');
            var actionType = $(this).data('action');
            removeRobotsRule(crawlerName, actionType);
        });

        // Block via PHP from compliance panel (User-Agent)
        $(document).on('click', '.vigia-block-php', function() {
            var crawlerName = $(this).data('crawler');
            blockUserAgent(crawlerName, crawlerName);
        });

        // Add User-Agent block from selector
        $('#vigia-add-block-ua').on('click', function() {
            var $select = $('#vigia-block-crawler');
            var crawlerName = $select.val();
            var userAgent = $select.find(':selected').data('useragent') || crawlerName;
            
            if (!crawlerName) {
                alert(vigiaData.strings.selectCrawler || 'Please select a crawler');
                return;
            }
            
            blockUserAgent(crawlerName, userAgent);
        });

        // Add custom User-Agent block
        $('#vigia-add-custom-block-ua').on('click', function() {
            var name = $('#vigia-custom-ua-name').val();
            var pattern = $('#vigia-custom-ua-pattern').val();
            
            if (!name || !pattern) {
                alert(vigiaData.strings.enterBothFields || 'Please enter both name and pattern');
                return;
            }
            
            blockUserAgent(name, pattern);
        });

        // Add IP block
        $('#vigia-add-block-ip').on('click', function() {
            var name = $('#vigia-block-ip-name').val();
            var ip = $('#vigia-block-ip').val();
            
            if (!ip) {
                alert(vigiaData.strings.enterIP || 'Please enter an IP address');
                return;
            }
            
            blockIP(name || ip, ip);
        });

        // Unblock by ID (generic)
        $(document).on('click', '.vigia-unblock', function() {
            var blockId = $(this).data('id');
            unblockById(blockId);
        });
    }

    /**
     * Add robots.txt rule
     */
    function addRobotsRule(crawlerName, actionType) {
        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_add_robots_rule',
                nonce: vigiaData.ajaxNonce,
                crawler_name: crawlerName,
                action_type: actionType
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.robotsRuleAdded);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            }
        });
    }

    /**
     * Remove robots.txt rule
     */
    function removeRobotsRule(crawlerName, actionType) {
        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_remove_robots_rule',
                nonce: vigiaData.ajaxNonce,
                crawler_name: crawlerName,
                action_type: actionType
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.robotsRuleRemoved);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            }
        });
    }

    /**
     * Block User-Agent via PHP
     */
    function blockUserAgent(name, pattern) {
        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_block_crawler',
                nonce: vigiaData.ajaxNonce,
                crawler_name: name,
                user_agent: pattern,
                block_type: 'useragent'
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.blocked);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            }
        });
    }

    /**
     * Block IP address via PHP
     */
    function blockIP(name, ip) {
        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_block_crawler',
                nonce: vigiaData.ajaxNonce,
                name: name,
                ip: ip,
                block_type: 'ip'
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.blocked);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            }
        });
    }

    /**
     * Unblock by ID
     */
    function unblockById(blockId) {
        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_unblock_crawler',
                nonce: vigiaData.ajaxNonce,
                block_id: blockId
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.unblocked);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            }
        });
    }

    // ==========================================================================
    // Email Alerts Tab
    // ==========================================================================

    /**
     * Initialize email tab functionality
     */
    function initEmailTab() {
        // Save email settings
        $('#vigia-save-email-settings').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: vigiaData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'vigia_save_email_settings',
                    nonce: vigiaData.ajaxNonce,
                    enabled: $('#vigia-email-enabled').is(':checked') ? 'true' : 'false',
                    frequency: $('#vigia-email-frequency').val(),
                    level: $('#vigia-email-level').val(),
                    email: $('#vigia-email-address').val()
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('success', vigiaData.strings.settingsSaved);
                    } else {
                        alert(response.data || vigiaData.strings.error);
                    }
                },
                error: function() {
                    alert(vigiaData.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Test email
        $('#vigia-test-email').on('click', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(vigiaData.strings.sending || 'Sending...');

            $.ajax({
                url: vigiaData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'vigia_test_email',
                    nonce: vigiaData.ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('success', vigiaData.strings.testEmailSent);
                    } else {
                        alert(response.data || vigiaData.strings.error);
                    }
                },
                error: function() {
                    alert(vigiaData.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    // ==========================================================================
    // LLMs.txt Tab (v1.2.0 - completely rewritten)
    // ==========================================================================

    var llmsSearchTimeout = null;
    var taxonomyCache = {};

    /**
     * Initialize LLMs tab functionality
     */
    function initLlmsTab() {
        // Post type selection
        $(document).on('change', 'input[name="vigia_post_types[]"]', function() {
            updateTaxonomyFilters();
            updateContentSummary();
        });

        // Toggle full options
        $('#vigia-generate-full').on('change', function() {
            $('#vigia-full-options').toggle(this.checked);
        });

        // Include search with debounce
        $('#vigia-include-search').on('input', function() {
            var $input = $(this);
            var search = $input.val();
            
            clearTimeout(llmsSearchTimeout);
            
            if (search.length < 2) {
                $('#vigia-include-results').hide().empty();
                return;
            }
            
            llmsSearchTimeout = setTimeout(function() {
                searchPosts(search, 'include');
            }, 300);
        });

        // Exclude search with debounce
        $('#vigia-exclude-search').on('input', function() {
            var $input = $(this);
            var search = $input.val();
            
            clearTimeout(llmsSearchTimeout);
            
            if (search.length < 2) {
                $('#vigia-exclude-results').hide().empty();
                return;
            }
            
            llmsSearchTimeout = setTimeout(function() {
                searchPosts(search, 'exclude');
            }, 300);
        });

        // Click on search result - FIXED: correct target ID
        $(document).on('click', '.vigia-search-result-item', function() {
            var $item = $(this);
            var id = $item.data('id');
            var title = $item.data('title');
            var type = $item.attr('data-type-label') || '';
            
            // Get target from results container ID: vigia-include-results or vigia-exclude-results
            var resultsId = $item.closest('.vigia-search-results').attr('id');
            var targetType = resultsId.replace('vigia-', '').replace('-results', ''); // 'include' or 'exclude'
            var targetContainer = '#vigia-manual-' + targetType + 's'; // #vigia-manual-includes or #vigia-manual-excludes
            
            // Check if already selected
            if ($(targetContainer).find('[data-id="' + id + '"]').length > 0) {
                return;
            }
            
            // Add to selected items
            var $selected = $('<span class="vigia-selected-item" data-id="' + id + '">' +
                escapeHtml(title) + (type ? ' <small>(' + escapeHtml(type) + ')</small>' : '') +
                '<button type="button" class="vigia-remove-item">&times;</button></span>');
            
            $(targetContainer).append($selected);
            
            // Clear search
            $item.closest('.vigia-manual-selector').find('.vigia-ajax-search').val('');
            $item.closest('.vigia-search-results').hide().empty();
            
            updateContentSummary();
        });

        // Remove selected item
        $(document).on('click', '.vigia-remove-item', function() {
            $(this).closest('.vigia-selected-item').remove();
            updateContentSummary();
        });

        // Hide search results on click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.vigia-manual-selector').length) {
                $('.vigia-search-results').hide();
            }
        });

        // Focus on search input shows results if they exist
        $('.vigia-ajax-search').on('focus', function() {
            var $results = $(this).siblings('.vigia-search-results');
            if ($results.children().length > 0) {
                $results.show();
            }
        });

        // Generate files
        $('#vigia-generate-llms').on('click', function() {
            generateLlmsFiles();
        });

        // Delete individual file
        $(document).on('click', '.vigia-delete-llms-file', function() {
            var filename = $(this).data('file');
            if (confirm(vigiaData.strings.confirmDeleteLlms || 'Are you sure you want to delete this file?')) {
                deleteLlmsFile(filename, $(this));
            }
        });

        // Taxonomy checkbox changes
        $(document).on('change', '.vigia-tax-checkbox', function() {
            updateTaxonomySelectionInfo($(this).closest('.vigia-taxonomy-accordion'));
            updateContentSummary();
        });
        
        // Accordion toggle
        $(document).on('click', '.vigia-accordion-header', function(e) {
            // Don't toggle if clicking on buttons inside
            if ($(e.target).is('button')) {
                return;
            }
            var $accordion = $(this).closest('.vigia-taxonomy-accordion');
            var $content = $accordion.find('.vigia-accordion-content');
            var $toggle = $accordion.find('.vigia-accordion-toggle');
            
            $content.slideToggle(200);
            $toggle.toggleClass('dashicons-arrow-right-alt2 dashicons-arrow-down-alt2');
            $accordion.toggleClass('is-open');
        });
        
        // Select all terms in taxonomy
        $(document).on('click', '.vigia-select-all-tax', function(e) {
            e.preventDefault();
            var taxonomy = $(this).data('taxonomy');
            var $accordion = $(this).closest('.vigia-taxonomy-accordion');
            $accordion.find('.vigia-tax-checkbox').prop('checked', true);
            updateTaxonomySelectionInfo($accordion);
            updateContentSummary();
        });
        
        // Select none terms in taxonomy
        $(document).on('click', '.vigia-select-none-tax', function(e) {
            e.preventDefault();
            var taxonomy = $(this).data('taxonomy');
            var $accordion = $(this).closest('.vigia-taxonomy-accordion');
            $accordion.find('.vigia-tax-checkbox').prop('checked', false);
            updateTaxonomySelectionInfo($accordion);
            updateContentSummary();
        });

        // Initialize taxonomy filters on load
        updateTaxonomyFilters();
        updateContentSummary();
    }

    /**
     * Search posts via AJAX
     */
    function searchPosts(search, type) {
        var excludeIds = [];
        
        // Get already selected IDs to exclude from results
        $('#vigia-manual-includes .vigia-selected-item, #vigia-manual-excludes .vigia-selected-item').each(function() {
            excludeIds.push($(this).data('id'));
        });
        
        // Also exclude posts from selected post types (for include search only)
        // This is handled server-side for performance

        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_search_posts',
                nonce: vigiaData.ajaxNonce,
                search: search,
                exclude_ids: excludeIds
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, post) {
                        html += '<div class="vigia-search-result-item" data-id="' + post.id + '" ' +
                                'data-title="' + escapeHtml(post.title) + '" data-type-label="' + escapeHtml(post.type) + '">' +
                                '<span class="vigia-result-title">' + escapeHtml(post.title) + '</span>' +
                                '<span class="vigia-result-type">' + escapeHtml(post.type) + '</span>' +
                                '</div>';
                    });
                    $('#vigia-' + type + '-results').html(html).show();
                } else {
                    $('#vigia-' + type + '-results').html('<div class="vigia-no-results">' + 
                        (vigiaData.strings.noResults || 'No results found') + '</div>').show();
                }
            }
        });
    }

    /**
     * Update taxonomy filters based on selected post types
     * Uses collapsible accordions with Select all/None
     * Checkboxes CHECKED by default - unchecking = exclude
     * Restores saved state from vigiaData.llmsSettings.taxonomy_filters
     */
    function updateTaxonomyFilters() {
        var selectedTypes = [];
        $('input[name="vigia_post_types[]"]:checked').each(function() {
            selectedTypes.push($(this).val());
        });

        if (selectedTypes.length === 0) {
            $('#vigia-taxonomy-filters').hide();
            $('#vigia-taxonomy-selectors').empty();
            return;
        }

        // Get saved taxonomy filters to restore state
        var savedFilters = (typeof vigiaSavedTaxonomyFilters !== 'undefined') ? vigiaSavedTaxonomyFilters : {};

        // Fetch taxonomies for selected post types
        var promises = selectedTypes.map(function(postType) {
            if (taxonomyCache[postType]) {
                return $.Deferred().resolve(taxonomyCache[postType]).promise();
            }
            
            return $.ajax({
                url: vigiaData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'vigia_get_taxonomies',
                    nonce: vigiaData.ajaxNonce,
                    post_type: postType
                }
            }).then(function(response) {
                if (response.success) {
                    taxonomyCache[postType] = response.data;
                    return response.data;
                }
                return {};
            });
        });

        $.when.apply($, promises).done(function() {
            var allTaxonomies = {};
            var taxPostTypeMap = {}; // Track which post_types have each taxonomy
            var results = arguments;
            
            // Merge taxonomies from all selected post types AND track ownership
            $.each(selectedTypes, function(i, postType) {
                var taxonomies = results[i] || taxonomyCache[postType] || {};
                $.each(taxonomies, function(taxName, taxData) {
                    if (!allTaxonomies[taxName]) {
                        allTaxonomies[taxName] = taxData;
                        taxPostTypeMap[taxName] = [];
                    }
                    taxPostTypeMap[taxName].push(postType);
                });
            });
            
            // Store the map globally for getLlmsSettings to use
            window.vigiaTaxPostTypeMap = taxPostTypeMap;

            if ($.isEmptyObject(allTaxonomies)) {
                $('#vigia-taxonomy-filters').hide();
                return;
            }

            // Collect saved term IDs for each taxonomy (from any post type)
            var savedTermsByTax = {};
            $.each(savedFilters, function(postType, taxonomies) {
                if (typeof taxonomies === 'object') {
                    $.each(taxonomies, function(taxName, termIds) {
                        if (!savedTermsByTax[taxName]) {
                            savedTermsByTax[taxName] = [];
                        }
                        if (Array.isArray(termIds)) {
                            $.each(termIds, function(i, termId) {
                                if (savedTermsByTax[taxName].indexOf(String(termId)) === -1) {
                                    savedTermsByTax[taxName].push(String(termId));
                                }
                            });
                        }
                    });
                }
            });

            // Build collapsible taxonomy accordions
            var html = '';
            $.each(allTaxonomies, function(taxName, taxData) {
                var termCount = taxData.terms ? taxData.terms.length : 0;
                var hasSavedFilter = savedTermsByTax[taxName] && savedTermsByTax[taxName].length > 0;
                
                html += '<div class="vigia-taxonomy-accordion" data-taxonomy="' + taxName + '">';
                
                // Accordion header (collapsed by default)
                html += '<div class="vigia-accordion-header">';
                html += '<span class="vigia-accordion-toggle dashicons dashicons-arrow-right-alt2"></span>';
                html += '<strong class="vigia-tax-label">' + escapeHtml(taxData.label) + '</strong>';
                html += '<span class="vigia-tax-term-count">(' + termCount + ')</span>';
                html += '<span class="vigia-tax-selection-info all-included">' + (vigiaData.strings.allIncluded || 'All included') + '</span>';
                html += '</div>';
                
                // Accordion content (hidden by default)
                html += '<div class="vigia-accordion-content" style="display: none;">';
                
                // Select all / None controls
                html += '<div class="vigia-tax-bulk-actions">';
                html += '<button type="button" class="button button-small vigia-select-all-tax" data-taxonomy="' + taxName + '">' + (vigiaData.strings.includeAll || 'Include all') + '</button>';
                html += '<button type="button" class="button button-small vigia-select-none-tax" data-taxonomy="' + taxName + '">' + (vigiaData.strings.excludeAll || 'Exclude all') + '</button>';
                html += '<span class="vigia-tax-hint">' + (vigiaData.strings.uncheckToExclude || 'Uncheck to exclude specific terms') + '</span>';
                html += '</div>';
                
                // Checkboxes grid - restore saved state or check all by default
                html += '<div class="vigia-tax-checkboxes">';
                $.each(taxData.terms, function(j, term) {
                    // If saved filter exists: only check if term is in saved list
                    // If no saved filter: check all (all included by default)
                    var isChecked = true;
                    if (hasSavedFilter) {
                        isChecked = savedTermsByTax[taxName].indexOf(String(term.id)) !== -1;
                    }
                    
                    html += '<label class="vigia-tax-checkbox-label">';
                    html += '<input type="checkbox" class="vigia-tax-checkbox" ' +
                            'name="vigia_tax_' + taxName + '[]" ' +
                            'value="' + term.id + '" ' +
                            'data-taxonomy="' + taxName + '"' + (isChecked ? ' checked' : '') + '>';
                    html += ' ' + escapeHtml(term.name) + ' <span class="vigia-term-count">(' + term.count + ')</span>';
                    html += '</label>';
                });
                html += '</div>';
                
                html += '</div>'; // .vigia-accordion-content
                html += '</div>'; // .vigia-taxonomy-accordion
            });

            $('#vigia-taxonomy-selectors').html(html);
            $('#vigia-taxonomy-filters').show();
            
            // Update selection info on all accordions
            updateAllTaxonomySelectionInfo();
        });
    }
    
    /**
     * Update selection info text for a taxonomy accordion
     */
    function updateTaxonomySelectionInfo($accordion) {
        var $checkboxes = $accordion.find('.vigia-tax-checkbox');
        var total = $checkboxes.length;
        var checked = $checkboxes.filter(':checked').length;
        var excluded = total - checked;
        var $info = $accordion.find('.vigia-tax-selection-info');
        
        if (checked === total) {
            $info.text(vigiaData.strings.allIncluded || 'All included')
                 .removeClass('has-exclusions')
                 .addClass('all-included');
        } else if (checked === 0) {
            $info.text(vigiaData.strings.allExcluded || 'All excluded')
                 .removeClass('all-included')
                 .addClass('has-exclusions');
        } else {
            $info.text((vigiaData.strings.excludedCount || '%d excluded').replace('%d', excluded))
                 .removeClass('all-included')
                 .addClass('has-exclusions');
        }
    }
    
    /**
     * Update all taxonomy selection info
     */
    function updateAllTaxonomySelectionInfo() {
        $('.vigia-taxonomy-accordion').each(function() {
            updateTaxonomySelectionInfo($(this));
        });
    }

    /**
     * Update content summary
     */
    function updateContentSummary() {
        var count = 0;
        var details = [];

        // Count from post types
        $('input[name="vigia_post_types[]"]:checked').each(function() {
            var ptCount = parseInt($(this).data('count'), 10) || 0;
            var ptLabel = $(this).siblings('.vigia-pt-label').text();
            count += ptCount;
            details.push(ptLabel + ': ' + ptCount);
        });

        // Count manual includes
        var manualIncludes = $('#vigia-manual-includes .vigia-selected-item').length;
        if (manualIncludes > 0) {
            count += manualIncludes;
            details.push((vigiaData.strings.manuallyAdded || 'Manually added') + ': +' + manualIncludes);
        }

        // Count manual excludes
        var manualExcludes = $('#vigia-manual-excludes .vigia-selected-item').length;
        if (manualExcludes > 0) {
            count -= manualExcludes;
            details.push((vigiaData.strings.excluded || 'Excluded') + ': -' + manualExcludes);
        }

        // Update summary
        if (count > 0) {
            var summaryText = (vigiaData.strings.estimatedContent || 'Estimated content: %1$d items (%2$s)')
                .replace('%1$d', count)
                .replace('%2$s', details.join(', '));
            $('#vigia-summary-text').html(summaryText);
            $('#vigia-content-summary').addClass('has-content');
        } else {
            $('#vigia-summary-text').text(vigiaData.strings.selectContentTypes || 'Select content types to see estimated count.');
            $('#vigia-content-summary').removeClass('has-content');
        }
    }

    /**
     * Get all LLMs settings from form
     */
    function getLlmsSettings() {
        var settings = {
            site_name: $('#vigia-llms-site-name').val(),
            site_description: $('#vigia-llms-description').val(),
            post_types: [],
            taxonomy_filters: {},
            manual_includes: [],
            manual_excludes: [],
            exclude_patterns: $('#vigia-exclude-patterns').val(),
            exclude_noindex: $('#vigia-exclude-noindex').is(':checked') ? 'true' : 'false',
            generate_full: $('#vigia-generate-full').is(':checked') ? 'true' : 'false',
            full_mode: $('input[name="vigia_full_mode"]:checked').val() || 'full',
            auto_regenerate: $('input[name="vigia_auto_regenerate"]:checked').val() || 'manual',
            robots_llms: $('#vigia-robots-llms').is(':checked') ? 'true' : 'false',
            robots_llms_full: $('#vigia-robots-llms-full').is(':checked') ? 'true' : 'false'
        };

        // Get selected post types
        $('input[name="vigia_post_types[]"]:checked').each(function() {
            settings.post_types.push($(this).val());
        });

        // Get taxonomy filters - only send if some are UNCHECKED (filtering)
        // If all are checked, don't send anything (include all)
        // IMPORTANT: Only apply filter to post_types that actually have this taxonomy
        var taxPostTypeMap = window.vigiaTaxPostTypeMap || {};
        
        $('.vigia-taxonomy-accordion').each(function() {
            var $accordion = $(this);
            var taxonomy = $accordion.data('taxonomy');
            var $checkboxes = $accordion.find('.vigia-tax-checkbox');
            var $checked = $checkboxes.filter(':checked');
            
            // Only add filter if some are unchecked (user wants to exclude some)
            if ($checked.length > 0 && $checked.length < $checkboxes.length) {
                // Get the post_types that actually have this taxonomy
                var postTypesWithTax = taxPostTypeMap[taxonomy] || [];
                
                // Only apply to post_types that have this taxonomy AND are selected
                $.each(postTypesWithTax, function(i, postType) {
                    if (settings.post_types.indexOf(postType) !== -1) {
                        if (!settings.taxonomy_filters[postType]) {
                            settings.taxonomy_filters[postType] = {};
                        }
                        settings.taxonomy_filters[postType][taxonomy] = [];
                        $checked.each(function() {
                            settings.taxonomy_filters[postType][taxonomy].push($(this).val());
                        });
                    }
                });
            }
            // If all checked or all unchecked, don't add filter (include all or none based on post type selection)
        });

        // Get manual includes
        $('#vigia-manual-includes .vigia-selected-item').each(function() {
            settings.manual_includes.push($(this).data('id'));
        });

        // Get manual excludes
        $('#vigia-manual-excludes .vigia-selected-item').each(function() {
            settings.manual_excludes.push($(this).data('id'));
        });

        return settings;
    }

    /**
     * Generate LLMs files
     */
    function generateLlmsFiles() {
        var settings = getLlmsSettings();

        // Validation
        if (!settings.site_name) {
            alert(vigiaData.strings.siteNameRequired || 'Site name is required');
            return;
        }

        if (settings.post_types.length === 0 && settings.manual_includes.length === 0) {
            alert(vigiaData.strings.selectContent || 'Please select at least one content type or add content manually');
            return;
        }

        var $btn = $('#vigia-generate-llms');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> ' + 
            (vigiaData.strings.generating || 'Generating...'));

        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: $.extend({
                action: 'vigia_generate_llms',
                nonce: vigiaData.ajaxNonce
            }, settings),
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.llmsGenerated);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    /**
     * Delete a single LLMs file
     */
    function deleteLlmsFile(filename, $btn) {
        $btn.prop('disabled', true);

        $.ajax({
            url: vigiaData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'vigia_delete_llms_files',
                nonce: vigiaData.ajaxNonce,
                file: filename
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', vigiaData.strings.llmsDeleted);
                    location.reload();
                } else {
                    alert(response.data || vigiaData.strings.error);
                }
            },
            error: function() {
                alert(vigiaData.strings.error);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    }

    // ==========================================================================
    // Helpers
    // ==========================================================================

    /**
     * Show admin notice
     */
    function showNotice(type, message) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible vigia-notice-js">' +
                        '<p>' + message + '</p>' +
                        '<button type="button" class="notice-dismiss"></button></div>');
        
        // Remove existing notices
        $('.vigia-notice-js').remove();
        
        // Add new notice
        $('.vigia-extras-wrap h1').after($notice);
        
        // Handle dismiss
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(function() { $(this).remove(); });
        });
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    /**
     * Escape HTML entities
     */
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})(jQuery);