// Add debugging information
console.log('divewp-admin.js loaded successfully!');

jQuery(document).ready(function ($) {
    console.log('DiveWP jQuery ready handler executing');
    
    // Show content immediately without preloader
    $('.divewp-wrap').show();

    // Track AJAX requests for functionality
    let activeAjaxRequests = 0;

    // Load timeline and email logs immediately
    loadRecentTimeline();
    loadEmailLogs();

    function loadRecentTimeline() {
        const $placeholder = $('#divewp-timeline-placeholder');
        if (!$placeholder.length) return;

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_load_recent_timeline',
                nonce: divewpData.nonce
            },
            beforeSend: function () {
                activeAjaxRequests++;
            },
            success: function (response) {
                if (response.success) {
                    $placeholder.html(response.data.html);
                }
            },
            error: function () {
                $placeholder.html(
                    '<div class="divewp-no-activity">' +
                    '<span class="dashicons dashicons-warning"></span>' +
                    '<p>Failed to load recent activities. Please refresh the page.</p>' +
                    '</div>'
                );
            },
            complete: function () {
                activeAjaxRequests--;
            }
        });
    }

    // Function to load email logs
    function loadEmailLogs() {
        const $placeholder = $('#divewp-email-logs-placeholder');
        if (!$placeholder.length) return;

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_refresh_email_log',
                nonce: divewpData.nonce
            },
            beforeSend: function () {
                activeAjaxRequests++;
            },
            success: function (response) {
                if (response.success) {
                    $placeholder.html(response.data.html);
                }
            },
            error: function () {
                $placeholder.html(
                    '<div class="divewp-no-emails">' +
                    '<span class="dashicons dashicons-warning"></span>' +
                    '<p>Failed to load email logs. Please refresh the page.</p>' +
                    '</div>'
                );
            },
            complete: function () {
                activeAjaxRequests--;
            }
        });
    }

    // Track AJAX requests
    $(document).ajaxStart(function () {
        activeAjaxRequests++;
    });

    $(document).ajaxComplete(function () {
        activeAjaxRequests--;
    });

    // Error handling
    $(window).on('error', function () {
        $('.divewp-wrap').prepend(
            '<div class="notice notice-error">' +
            '<p>Some resources failed to load properly.</p>' +
            '</div>'
        );
    });

    // Enhanced tab switching function
    function switchTab(tabId, updateHash = true) {
        // Validate tab exists
        const targetTab = $("#" + tabId);
        if (!targetTab.length || $(`.divewp-tabs li[data-tab="${tabId}"]`).hasClass('disabled')) {
            tabId = 'welcome'; // Fallback to welcome tab if invalid or disabled
        }

        // Switch tabs using existing classes
        $('.divewp-tabs li').removeClass('active');
        $('.divewp-tab-content').removeClass('active');
        $('.divewp-tabs li[data-tab="' + tabId + '"]').addClass('active');
        $("#" + tabId).addClass('active');

        // Update URL hash if requested
        if (updateHash) {
            // Replace current state instead of pushing new one
            if (history.replaceState) {
                history.replaceState({ tabId: tabId }, '', '#' + tabId);
            } else {
                location.hash = '#' + tabId;
            }
        }

        // Call existing error logs placement fix
        fixErrorLogsPlacement();
    }

    // Initialize tab based on URL hash or default to welcome
    function initializeTab() {
        let hash = window.location.hash.replace('#', '');
        if (!hash || !$("#" + hash).length || $(`.divewp-tabs li[data-tab="${hash}"]`).hasClass('disabled')) {
            hash = 'welcome';
            // Set initial state
            if (history.replaceState) {
                history.replaceState({ tabId: hash }, '', '#' + hash);
            }
        }
        switchTab(hash, false);
    }

    // Handle browser back/forward
    $(window).on('popstate', function (event) {
        let hash = window.location.hash.replace('#', '');
        if (!hash || !$("#" + hash).length || $(`.divewp-tabs li[data-tab="${hash}"]`).hasClass('disabled')) {
            hash = 'welcome';
        }
        switchTab(hash, false);
    });

    // Remove hashchange handler and use popstate instead
    $(window).off('hashchange');

    // Initialize on page load
    initializeTab();

    // Remove duplicate click handlers
    $('.divewp-tabs li').off('click').on('click', function (e) {
        e.preventDefault();
        if (!$(this).hasClass('disabled')) {
            const tabId = $(this).attr('data-tab');
            if (history.pushState) {
                // Push new state when actively clicking
                history.pushState({ tabId: tabId }, '', '#' + tabId);
            }
            switchTab(tabId, false);
        }
    });

    // Handle card link clicks
    $('.divewp-status-list').off('click', '.divewp-tab-link').on('click', '.divewp-tab-link', function (e) {
        e.preventDefault();
        const tabId = $(this).data('tab');
        if (history.pushState) {
            history.pushState({ tabId: tabId }, '', '#' + tabId);
        }
        switchTab(tabId, false);
    });

    // Handle timeline "View All" link clicks
    $(document).off('click', '.divewp-view-all[data-tab]').on('click', '.divewp-view-all[data-tab]', function (e) {
        e.preventDefault();
        const tabId = $(this).attr('data-tab');
        if (history.pushState) {
            history.pushState({ tabId: tabId }, '', '#' + tabId);
        }
        switchTab(tabId, false);
    });

    // Handle Cron Status Widget link clicks
    $(document).off('click', '.divewp-cron-status-widget__link[data-tab]').on('click', '.divewp-cron-status-widget__link[data-tab]', function (e) {
        e.preventDefault();
        const tabId = $(this).attr('data-tab');
        if (history.pushState) {
            history.pushState({ tabId: tabId }, '', '#' + tabId);
        }
        switchTab(tabId, false);
    });

    // Hosting sub-tabs inside a feature (Benchmark | Guide)
    // We follow the same CSS-driven visibility model: toggle .active on .divewp-tab-content children
    $(document).on('click', '.divewp-hosting-tabs .divewp-hosting-tab', function (e) {
        e.preventDefault();
        const id = this.id;
        const $benchmark = $('#hosting-tab-benchmark');
        const $previous = $('#hosting-tab-previous');
        const $guide = $('#hosting-tab-guide');
        if (!$benchmark.length || !$guide.length) return;

        const select = (panel) => {
            $benchmark.toggleClass('active', panel === 'benchmark');
            $previous.toggleClass('active', panel === 'previous');
            $guide.toggleClass('active', panel === 'guide');
            $('#hosting-tab-button-benchmark').attr('aria-selected', panel === 'benchmark' ? 'true' : 'false');
            $('#hosting-tab-button-previous').attr('aria-selected', panel === 'previous' ? 'true' : 'false');
            $('#hosting-tab-button-guide').attr('aria-selected', panel === 'guide' ? 'true' : 'false');
        };

        if (id === 'hosting-tab-button-benchmark') select('benchmark');
        else if (id === 'hosting-tab-button-previous') select('previous');
        else if (id === 'hosting-tab-button-guide') select('guide');
    });

    // Handle view activity log button click
    $(document).off('click', '#divewp-view-activity-log').on('click', '#divewp-view-activity-log', function (e) {
        e.preventDefault();
        const tabId = $(this).data('tab');
        if (history.pushState) {
            history.pushState({ tabId: tabId }, '', '#' + tabId);
        }
        switchTab(tabId, false);
    });

    // Print functionality
    $('#divewp-print-report').on('click', function (e) {
        e.preventDefault();
        var activeTab = $('.divewp-tabs li.active').attr('data-tab');
        var errorLogsTab = $('#error-logs').detach();
        $('.divewp-tab-content').addClass('active');
        window.print();
        $('.divewp-tab-content').removeClass('active');
        $('.divewp-main-content').append(errorLogsTab);
        switchTab(activeTab);
    });

    // Function to check and fix error logs tab placement
    function fixErrorLogsPlacement() {
        var errorLogsTab = $('#error-logs');
        if (errorLogsTab.length && !errorLogsTab.parent().hasClass('divewp-main-content')) {
            $('.divewp-main-content').append(errorLogsTab);
        }
    }

    // Call on page load and after tab switches
    $(document).ready(fixErrorLogsPlacement);
    $('.divewp-tabs li').on('click', function () {
        var tabId = $(this).attr('data-tab');
        switchTab(tabId);
        fixErrorLogsPlacement();
    });

    // Copy error logs
    $('.divewp-copy-error-logs').on('click', function () {
        var errorLogs = $('.error-logs-content').text();
        var $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(errorLogs).select();
        document.execCommand("copy");
        $temp.remove();
        alert('Error logs copied to clipboard!');
    });

    // Clear error logs
    $('.divewp-clear-error-logs').on('click', function () {
        if (confirm(divewpData.clearLogsConfirm)) {
            $.ajax({
                url: divewpData.ajaxurl,
                method: 'POST',
                data: {
                    action: 'divewp_clear_error_logs',
                    nonce: divewpData.nonce
                },
                success: function (response) {
                    console.log('Server Response:', response);
                    if (response.success) {
                        $('.error-logs-content pre').text('');
                        alert(response.data || divewpData.clearLogsSuccess);
                    } else {
                        console.error('Error:', response.data);
                        alert(divewpData.clearLogsFailed + ': ' + (response.data || 'Unknown error'));
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert(divewpData.clearLogsFailed + ': ' + error);
                }
            });
        }
    });

    // Add this to the existing JavaScript
    $('.error-logs-content').on('scroll', function () {
        var element = $(this);
        if (Math.round(element.scrollTop() + element.innerHeight()) >= element[0].scrollHeight) {
            element.addClass('scrolled-bottom');
        } else {
            element.removeClass('scrolled-bottom');
        }
    });

    // Function to refresh email log table
    function refreshEmailLog() {
        $.ajax({
            url: divewpData.ajaxurl,
            method: 'POST',
            data: {
                action: 'divewp_refresh_email_log',
                nonce: divewpData.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('.email-log-section').replaceWith(response.data.html);
                }
            },
            error: function (xhr, status, error) {
                console.log('Failed to refresh email log:', error);
            }
        });
    }

    // Test email functionality
    $('#divewp-send-test-email').off('click').on('click', function (e) {
        e.preventDefault();

        var $button = $(this);
        var $result = $('#test-email-result');

        if ($button.prop('disabled')) {
            return;
        }

        $button.prop('disabled', true).addClass('loading');

        $.ajax({
            url: divewpData.ajaxurl,
            method: 'POST',
            data: {
                action: 'divewp_send_test_email',
                nonce: divewpData.nonce
            },
            success: function (response) {
                if (response.success) {
                    $result.removeClass('notice-error')
                        .addClass('notice notice-success')
                        .html('<p>' + response.data.message + '</p>')
                        .show();

                    setTimeout(function () {
                        $result.fadeOut();
                    }, 3000);

                    refreshEmailLog();
                } else {
                    $result.removeClass('notice-success')
                        .addClass('notice notice-error')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                }
            },
            error: function (xhr, status, error) {
                $result.removeClass('notice-success')
                    .addClass('notice notice-error')
                    .html('<p>' + divewpData.testEmailFailed + '</p>')
                    .show();
            },
            complete: function () {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Security middleware
    function secureAjaxCall(action, data = {}) {
        return $.ajax({
            url: divewpData.ajaxurl,
            method: 'POST',
            data: {
                ...data,
                action: action,
                nonce: divewpData.nonce
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', divewpData.nonce);
            }
        });
    }

    // Delete all logs handler
    $('#divewp-delete-all-logs').on('click', function (e) {
        e.preventDefault();
        if (confirm(divewpData.confirmDeleteLogs || 'Are you sure you want to delete all logs?')) {
            $.ajax({
                url: divewpData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_delete_all_logs',
                    nonce: divewpData.nonce
                },
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        console.error('Delete logs error:', response);
                        alert(response.data?.message || 'Failed to delete logs.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText
                    });
                    alert('Failed to delete logs. Please try again.');
                }
            });
        }
    });

    // Refresh logs functionality
    $(document).on('click', '#divewp-refresh-logs', function (e) {
        e.preventDefault();

        const $button = $(this);
        const $container = $('#divewp-logs-container');

        $button.prop('disabled', true);

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_refresh_logs',
                nonce: divewpData.nonce
            },
            success: function (response) {
                if (response.success) {
                    $container.replaceWith(response.data.html);
                } else {
                    console.error('Refresh logs error:', response);
                    alert(response.data?.message || 'Failed to refresh logs.');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText
                });
                if (xhr.status === 403) {
                    alert('Permission denied. Please refresh the page and try again.');
                } else {
                    alert('Failed to refresh logs. Please try again.');
                }
            },
            complete: function () {
                $button.prop('disabled', false);
            }
        });
    });

    // User Events Pagination
    $(document).on('click', '.divewp-load-more:not(:disabled)', function () {
        const $button = $(this);
        const currentPage = parseInt($button.data('page') || 1);
        const nextPage = currentPage + 1;

        $button.prop('disabled', true).text('Loading...');

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_load_more_events',
                nonce: divewpData.nonce,
                page: nextPage
            },
            success: function (response) {
                if (response.success) {
                    $('#divewp-events-tbody').append(response.data.html);
                    $button.data('page', nextPage);

                    // Update pagination info
                    const totalEvents = response.data.total;
                    const currentShowing = $('#divewp-events-tbody tr').length;
                    $('.divewp-pagination-info').text(
                        'Showing ' + currentShowing + ' of ' + totalEvents + ' entries'
                    );

                    // Hide button if no more events
                    if (currentShowing >= totalEvents) {
                        $button.hide();
                    }
                } else {
                    alert('Error loading more events. ' + (response.data?.message || 'Please try again.'));
                }
            },
            error: function (xhr, status, error) {
                console.error('Load more error:', error);
                alert('Error loading more events. Please try again.');
            },
            complete: function () {
                $button.prop('disabled', false).text('Load More');
            }
        });
    });

    // Handle new feature highlights
    (function () {
        const seenFeatures = JSON.parse(localStorage.getItem('divewp_seen_features') || '{}');

        document.querySelectorAll('.new-feature-highlight-pill').forEach(pill => {
            const featureId = pill.dataset.featureId;
            if (seenFeatures[featureId]) {
                pill.style.display = 'none';
            }
        });

        document.querySelectorAll('[data-feature]').forEach(tab => {
            tab.addEventListener('click', function () {
                const featureId = this.dataset.feature;
                if (featureId) {
                    seenFeatures[featureId] = true;
                    localStorage.setItem('divewp_seen_features', JSON.stringify(seenFeatures));

                    const pill = this.querySelector('.new-feature-highlight-pill');
                    if (pill) {
                        pill.style.display = 'none';
                    }
                }
            });
        });
    })();

    // Add notice removal functionality
    function removeWordPressNotices() {
        $('.notice:not(.divewp-notice), .update-nag, .updated, .error, .is-dismissible').remove();

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        const $node = $(node);
                        if ($node.hasClass('notice') && !$node.hasClass('divewp-notice') ||
                            $node.hasClass('update-nag') ||
                            $node.hasClass('updated') ||
                            $node.hasClass('error') ||
                            $node.hasClass('is-dismissible')) {
                            $node.remove();
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Call it on document ready
    removeWordPressNotices();

    // Also call it after AJAX requests
    $(document).ajaxComplete(function () {
        removeWordPressNotices();
    });

    // Handle recommendation expand/collapse
    $(document).off('click', '.recommendation-expand').on('click', '.recommendation-expand', function (e) {
        // Prevent event bubbling
        e.preventDefault();
        e.stopPropagation();
        
        const $this = $(this);
        const $card = $this.closest('.recommendation-card');
        const $details = $card.find('.recommendation-details');
        
        if ($details.is(':visible')) {
            $details.slideUp(300);
            $this.removeClass('expanded');
        } else {
            // Close any other open details first
            $('.recommendation-details').not($details).slideUp(300);
            $('.recommendation-expand').not($this).removeClass('expanded');
            
            // Open this one
            $this.addClass('expanded');
            $details.slideDown(300);
        }
    });

    // Updated email logs handlers
    $(document).on('click', '#divewp-refresh-email-logs', function (e) {
        const $button = $(this);
        const $container = $('#divewp-email-logs-placeholder');

        $button.prop('disabled', true).addClass('loading');

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_refresh_email_log',
                nonce: divewpData.nonce
            },
            success: function (response) {
                if (response.success) {
                    $container.html(response.data.html);
                } else {
                    $container.html(
                        '<div class="divewp-no-activity">' +
                        '<span class="dashicons dashicons-warning"></span>' +
                        '<p>' + response.data.message + '</p>' +
                        '</div>'
                    );
                }
            },
            error: function (xhr) {
                console.error('Email log refresh failed:', xhr.responseText);
            },
            complete: function () {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    });

    $(document).on('click', '#divewp-delete-all-email-logs', function (e) {
        // Use either divewpEmailTest or divewpData for confirm message
        const confirmMessage = (typeof divewpEmailTest !== 'undefined' && divewpEmailTest.confirmDeleteLogs) 
            ? divewpEmailTest.confirmDeleteLogs 
            : 'Are you sure you want to delete all email logs? This cannot be undone.';
        if (!confirm(confirmMessage)) return;

        const $button = $(this);
        const $container = $('#divewp-email-logs-placeholder');

        $button.prop('disabled', true).addClass('loading');

        $.ajax({
            url: divewpData.ajaxurl,
            type: 'POST',
            data: {
                action: 'divewp_delete_all_email_logs',
                nonce: divewpData.nonce
            },
            success: function (response) {
                if (response.success) {
                    $container.html(response.data.html);
                    $container.append(
                        '<div class="notice notice-success">' +
                        '<p>' + response.data.message + '</p>' +
                        '</div>'
                    );
                } else {
                    $container.append(
                        '<div class="notice notice-error">' +
                        '<p>' + response.data.message + '</p>' +
                        '</div>'
                    );
                }
            },
            error: function (xhr) {
                console.error('Email log deletion failed:', xhr.responseText);
            },
            complete: function () {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Add loading class and spinner animation
    $(document).on('click', '.divewp-action-button', function () {
        const $button = $(this);
        $button.prop('disabled', true).addClass('loading');
    });

    // Remove loading class on complete
    $(document).ajaxComplete(function () {
        $('.divewp-action-button')
            .prop('disabled', false)
            .removeClass('loading');
    });
});

// This was incorrect - removing the WordPress PHP function call that was in the JavaScript file
// wp_localize_script('divewp-admin-js', 'divewpData', { ... });

// Shared toggle functionality for all hosting features
function divewpToggleSection(targetId) {
    const section = document.getElementById(targetId);
    if (!section) return;
    
    const button = section.previousElementSibling;
    if (!button) return;
    
    const icon = button.querySelector('.dashicons');
    
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        if (icon) {
            icon.classList.remove('dashicons-arrow-right-alt2');
            icon.classList.add('dashicons-arrow-down-alt2');
        }
    } else {
        section.style.display = 'none';
        if (icon) {
            icon.classList.remove('dashicons-arrow-down-alt2');
            icon.classList.add('dashicons-arrow-right-alt2');
        }
    }
}

// Legacy global function for template compatibility
window.toggleSection = divewpToggleSection;

// Just add a simple console log instead
console.log('divewp-admin.js finished loading');
