/**
 * DiveWP Cron Jobs Dashboard JavaScript
 *
 * Handles all interactivity for the Unified Cron Intelligence Dashboard.
 *
 * @package DiveWP
 * @version 2.2.0
 */

(function($) {
    'use strict';

    // Check if we're on the right page
    if (typeof divewpCronData === 'undefined') {
        return;
    }

    var CronDashboard = {
        /**
         * Configuration
         */
        config: {
            nonce: divewpCronData.nonce,
            ajaxurl: divewpCronData.ajaxurl,
            strings: divewpCronData.strings
        },

        /**
         * State
         */
        state: {
            currentTab: 'wp-cron',
            selectedItems: [],
            isLoading: false,
            wpCronEvents: []
        },

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            var self = this;

            // Tab switching
            $(document).on('click', '.divewp-cron-tab', function(e) {
                e.preventDefault();
                self.switchTab($(this).data('tab'));
            });

            // Refresh button
            $(document).on('click', '.divewp-cron-refresh', function(e) {
                e.preventDefault();
                self.refresh();
            });

            // Add task button
            $(document).on('click', '.divewp-cron-add', function(e) {
                e.preventDefault();
                self.showAddTaskModal();
            });

            // Row actions
            $(document).on('click', '.divewp-cron-action', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $row = $(this).closest('.divewp-cron-row');
                var action = $(this).data('action');
                self.handleRowAction(action, $row);
            });

            // Action Scheduler actions
            $(document).on('click', '.divewp-cron-as-action', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var action = $(this).data('action');
                var actionId = $(this).data('id');
                var $row = $(this).closest('.divewp-cron-row');
                self.handleAsAction(action, actionId, $row);
            });

            // Select all checkbox
            $(document).on('change', '.divewp-cron-select-all', function() {
                var checked = $(this).prop('checked');
                $('.divewp-cron-select').prop('checked', checked);
                self.updateBulkActions();
            });

            // Individual checkbox
            $(document).on('change', '.divewp-cron-select', function() {
                self.updateBulkActions();
            });

            // Bulk action apply
            $(document).on('click', '.divewp-cron-bulk-apply', function(e) {
                e.preventDefault();
                self.handleBulkAction();
            });

            // Search
            $(document).on('input', '.divewp-cron-search', function() {
                self.filterTable($(this).val());
            });

            // Filters
            $(document).on('change', '.divewp-cron-filter', function() {
                self.applyFilters();
            });

            // Drawer close
            $(document).on('click', '.divewp-cron-drawer__close, .divewp-cron-drawer__overlay', function(e) {
                e.preventDefault();
                self.closeDrawer();
            });

            // Drawer actions
            $(document).on('click', '.divewp-cron-drawer__action', function(e) {
                e.preventDefault();
                var action = $(this).data('action');
                self.handleDrawerAction(action);
            });

            // Copy to clipboard
            $(document).on('click', '.divewp-cron-copy', function(e) {
                e.preventDefault();
                self.copyToClipboard($(this).data('copy'));
            });

            // Row click to open drawer
            $(document).on('click', '.divewp-cron-row', function(e) {
                // Ignore clicks on checkboxes or action controls
                if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.col-check, .col-actions, .divewp-cron-table__check, .divewp-cron-table__actions').length) {
                    return;
                }
                var $row = $(this);
                self.openDrawer($row);
            });

            // Health link scroll helper
            $(document).on('click', '.divewp-cron-hero__health-link', function(e) {
                e.preventDefault();
                // Prefer explicit anchor, fallback to section wrapper
                var $target = $('#cron-jobs-health');
                if (!$target.length) {
                    $target = $('#divewp-cron-health');
                }
                // Small delay to ensure layout is ready
                setTimeout(function() {
                    if ($target.length) {
                        var top = $target.offset().top - 120; // offset for admin bar/header
                        window.scrollTo({ top: top, behavior: 'smooth' });
                        // Keep hash in URL for consistency
                        if (history.replaceState) {
                            history.replaceState(null, '', '#cron-jobs-health');
                        } else {
                            window.location.hash = 'cron-jobs-health';
                        }
                    }
                }, 50);
            });

            // Escape key to close drawer
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.divewp-cron-drawer').hasClass('open')) {
                    self.closeDrawer();
                }
            });

            // Load More button
            $(document).on('click', '.divewp-cron-load-more', function(e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                if (tab === 'wp-cron') {
                    self.loadMoreWpCronEvents();
                }
            });
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            // Load action scheduler tab content when clicked
            // Load execution log tab content when clicked
        },

        /**
         * Switch tab
         */
        switchTab: function(tabId) {
            this.state.currentTab = tabId;

            // Update tab buttons
            $('.divewp-cron-tab').removeClass('active').attr('aria-selected', 'false');
            $('.divewp-cron-tab[data-tab="' + tabId + '"]').addClass('active').attr('aria-selected', 'true');

            // Update panels
            $('.divewp-cron-panel').removeClass('active');
            $('.divewp-cron-panel[data-panel="' + tabId + '"]').addClass('active');

            // Load content if needed (only for tabs that need AJAX loading)
            if (tabId === 'action-scheduler') {
                this.loadActionSchedulerContent();
            } else if (tabId === 'overdue') {
                this.loadOverdueContent();
            } else if (tabId === 'execution-log') {
                this.loadExecutionLogContent();
            }
        },

        /**
         * Load overdue tasks content via AJAX
         */
        loadOverdueContent: function() {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="overdue"]');

            $panel.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_overdue',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $panel.html('<div class="divewp-cron-overdue-content">' + response.data.html + '</div>');
                        // Update overdue count in tab
                        var $overdueTab = $('.divewp-cron-tab[data-tab="overdue"]');
                        $overdueTab.find('.divewp-cron-tab__count').text(response.data.count);
                        // Update warning class
                        if (response.data.count > 0) {
                            $overdueTab.addClass('divewp-cron-tab--warning');
                        } else {
                            $overdueTab.removeClass('divewp-cron-tab--warning');
                        }
                        // Update hero summary
                        self.updateHeroSummary(response.data.count);
                        // Update hero health status
                        self.updateHeroHealth(response.data.health, response.data.health_label);
                        // Update dashboard widget
                        self.updateDashboardWidget(response.data);
                        self.updateBulkActions();
                    } else {
                        $panel.html('<div class="divewp-cron-empty"><p>' + (response.data.message || self.config.strings.error) + '</p></div>');
                    }
                },
                error: function() {
                    $panel.html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Update hero summary with overdue count
         */
        updateHeroSummary: function(count) {
            var $heroHealth = $('.divewp-cron-hero__health');
            var $summary = $heroHealth.find('.divewp-cron-hero__summary');
            
            if (count > 0) {
                // Build the text with proper pluralization
                var text = count === 1 
                    ? this.config.strings.task_overdue.replace('%d', count)
                    : this.config.strings.tasks_overdue.replace('%d', count);
                
                if ($summary.length) {
                    // Update existing summary
                    $summary.text(text);
                } else {
                    // Create new summary element
                    $heroHealth.append('<span class="divewp-cron-hero__summary divewp-cron-hero__summary--warning">' + text + '</span>');
                }
            } else {
                // Remove summary when no overdue tasks
                $summary.remove();
            }
        },

        /**
         * Update hero health status
         */
        updateHeroHealth: function(health, healthLabel) {
            var $heroHealth = $('.divewp-cron-hero__health');
            var $healthLabel = $heroHealth.find('.divewp-cron-hero__health-label');
            
            // Remove all health classes
            $heroHealth.removeClass('divewp-cron-hero__health--good divewp-cron-hero__health--warning divewp-cron-hero__health--critical');
            // Add new health class
            $heroHealth.addClass('divewp-cron-hero__health--' + health);
            // Update label text
            $healthLabel.text(healthLabel);
        },

        /**
         * Update dashboard widget counts
         */
        updateDashboardWidget: function(data) {
            var $cronCard = $('.divewp-card-cron');
            if (!$cronCard.length) {
                return;
            }
            
            var $statusList = $cronCard.find('.divewp-status-list li');
            
            // Update WP Tasks count (first item)
            if (data.wp_tasks !== undefined) {
                $statusList.eq(0).find('.divewp-card-count').text(data.wp_tasks);
            }
            
            // Update Queue Tasks count (second item)
            if (data.queue_tasks !== undefined) {
                $statusList.eq(1).find('.divewp-card-count').text(data.queue_tasks);
            }
            
            // Update Overdue count (third item)
            if (data.count !== undefined) {
                var $overdueItem = $statusList.eq(2);
                $overdueItem.find('.divewp-card-count').text(data.count);
                
                // Toggle warning class
                if (data.count > 0) {
                    $overdueItem.addClass('divewp-status-list__warning');
                } else {
                    $overdueItem.removeClass('divewp-status-list__warning');
                }
            }
        },

        /**
         * Refresh current view
         */
        refresh: function() {
            if (this.state.currentTab === 'wp-cron') {
                this.loadWpCronEvents();
            } else if (this.state.currentTab === 'action-scheduler') {
                this.loadActionSchedulerContent();
            } else if (this.state.currentTab === 'overdue') {
                this.loadOverdueContent();
            } else if (this.state.currentTab === 'execution-log') {
                this.loadExecutionLogContent();
            }
        },

        /**
         * Load WP-Cron events via AJAX
         */
        loadWpCronEvents: function() {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="wp-cron"]');

            $panel.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_events',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.renderWpCronTable(response.data.events, response.data.total);
                        // Update count
                        $('.divewp-cron-tab[data-tab="wp-cron"] .divewp-cron-tab__count').text(response.data.total);
                    } else {
                        $panel.html('<div class="divewp-cron-empty"><p>' + (response.data.message || self.config.strings.error) + '</p></div>');
                    }
                },
                error: function() {
                    $panel.html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Render WP-Cron table
         */
        renderWpCronTable: function(events, total) {
            var $panel = $('.divewp-cron-panel[data-panel="wp-cron"]');

            if (!events || events.length === 0) {
                $panel.html('<div class="divewp-cron-empty"><span class="dashicons dashicons-calendar-alt"></span><p>' + this.config.strings.noTasks + '</p></div>');
                return;
            }

            // Paginate - show 10 by default
            var limit = 30;
            var hasMore = total > limit;
            var eventsPaged = events.slice(0, limit);

            var html = '<div class="divewp-cron-wp-content" data-offset="' + limit + '" data-total="' + total + '">';
            html += '<div class="divewp-cron-list-table">';
            html += '<div class="divewp-cron-table-header divewp-cron-table-header--wp">';
            html += '<div class="col-check"><input type="checkbox" class="divewp-cron-select-all"></div>';
            html += '<div class="col-hook">' + this.escapeHtml('Task Name') + '</div>';
            html += '<div class="col-next">' + this.escapeHtml('Next Run') + '</div>';
            html += '<div class="col-schedule">' + this.escapeHtml('Recurrence') + '</div>';
            html += '<div class="col-source">' + this.escapeHtml('Source') + '</div>';
            html += '<div class="col-actions">' + this.escapeHtml('Actions') + '</div>';
            html += '</div>';
            html += '<div class="divewp-cron-table-body">';

            // Store all events for load more
            this.state.wpCronEvents = events;

            for (var i = 0; i < eventsPaged.length; i++) {
                html += this.renderWpCronRow(eventsPaged[i]);
            }

            html += '</div></div>';

            if (hasMore) {
                html += '<div class="divewp-cron-load-more-wrap"><button type="button" class="button divewp-cron-load-more" data-tab="wp-cron">Load More</button></div>';
            }

            html += '</div>';
            $panel.html(html);
            this.updateBulkActions();
        },

        /**
         * Render a single WP-Cron row
         */
        renderWpCronRow: function(event) {
            var rowClass = 'divewp-cron-row divewp-cron-row--wp';
            var validation = event.validation || { status: 'healthy' };
            
            if (event.is_overdue) rowClass += ' divewp-cron-row--overdue';
            if (validation.status === 'potential_orphan' || validation.status === 'confirmed_orphan') {
                rowClass += ' divewp-cron-row--orphaned';
            }

            var rowStatus = event.is_overdue ? 'overdue' : 'scheduled';
            var html = '<div class="' + rowClass + '" data-hook="' + this.escapeHtml(event.hook) + '" data-sig="' + this.escapeHtml(event.sig) + '" data-timestamp="' + event.timestamp + '" data-source="' + this.escapeHtml(event.source) + '" data-status="' + rowStatus + '">';
            html += '<div class="col-check"><input type="checkbox" class="divewp-cron-select" value="' + this.escapeHtml(event.hook + '|' + event.sig) + '"></div>';
            html += '<div class="col-hook"><strong class="divewp-cron-hook-name">' + this.escapeHtml(event.hook) + '</strong>';
            
            if (validation.status === 'potential_orphan') {
                html += ' <span class="status-pill status-pill-warning">Potential orphan</span>';
            } else if (validation.status === 'confirmed_orphan') {
                html += ' <span class="status-pill status-pill-danger">Orphaned</span>';
            }

            if (event.args_display && event.args_display !== 'None') {
                html += '<span class="divewp-cron-args">' + this.escapeHtml(event.args_display) + '</span>';
            }
            html += '</div>';
            html += '<div class="col-next"><span class="divewp-cron-next-run' + (event.is_overdue ? ' divewp-cron-next-run--overdue' : '') + '">' + this.escapeHtml(event.next_run) + '</span>';
            html += '<span class="divewp-cron-relative">' + (event.is_overdue ? event.next_run_relative + ' overdue' : 'in ' + event.next_run_relative) + '</span></div>';
            html += '<div class="col-schedule"><span class="divewp-cron-schedule">' + this.escapeHtml(event.schedule_label) + '</span></div>';
            html += '<div class="col-source"><span class="divewp-cron-source">' + this.escapeHtml(event.source) + '</span></div>';
            html += '<div class="col-actions">';
            html += '<button type="button" class="button button-small divewp-cron-action" data-action="run-now" title="Run Now"><span class="dashicons dashicons-controls-play"></span></button>';
            html += '<button type="button" class="button button-small divewp-cron-action" data-action="view" title="View Details"><span class="dashicons dashicons-visibility"></span></button>';
            html += '<button type="button" class="button button-small divewp-cron-action" data-action="delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>';
            html += '</div></div>';

            return html;
        },

        /**
         * Load more WP-Cron events via AJAX
         */
        loadMoreWpCronEvents: function() {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="wp-cron"]');
            var $content = $panel.find('.divewp-cron-wp-content');
            var offset = parseInt($content.data('offset')) || 10;
            var limit = 30;

            $panel.find('.divewp-cron-load-more').prop('disabled', true).text('Loading...');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_events',
                    nonce: this.config.nonce,
                    offset: offset,
                    limit: limit
                },
                success: function(response) {
                    if (response.success && response.data.events) {
                        var html = '';
                        for (var i = 0; i < response.data.events.length; i++) {
                            html += self.renderWpCronRow(response.data.events[i]);
                        }
                        $content.find('.divewp-cron-table-body').append(html);
                        $content.data('offset', offset + response.data.events.length);

                        // Check if more available
                        if (response.data.has_more) {
                            $panel.find('.divewp-cron-load-more').prop('disabled', false).text('Load More');
                        } else {
                            $panel.find('.divewp-cron-load-more-wrap').remove();
                        }
                    }
                },
                error: function() {
                    $panel.find('.divewp-cron-load-more').prop('disabled', false).text('Load More');
                }
            });
        },

        /**
         * Load Action Scheduler content
         */
        loadActionSchedulerContent: function() {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="action-scheduler"]');

            $panel.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_as_actions',
                    nonce: this.config.nonce,
                    status: 'pending',
                    limit: 50
                },
                success: function(response) {
                    if (response.success) {
                        self.renderActionSchedulerTable(response.data.actions);
                        // Update count
                        if (response.data.stats) {
                            $('.divewp-cron-tab[data-tab="action-scheduler"] .divewp-cron-tab__count').text(response.data.stats.pending);
                        }
                    } else {
                        $panel.html('<div class="divewp-cron-empty"><p>' + (response.data.message || self.config.strings.error) + '</p></div>');
                    }
                },
                error: function() {
                    $panel.html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Render Action Scheduler table
         */
        renderActionSchedulerTable: function(actions) {
            var $panel = $('.divewp-cron-panel[data-panel="action-scheduler"]');

            if (!actions || actions.length === 0) {
                $panel.html('<div class="divewp-cron-empty"><span class="dashicons dashicons-list-view"></span><p>' + this.config.strings.noTasks + '</p></div>');
                return;
            }

            var html = '<div class="divewp-cron-list-table">' +
                '<div class="divewp-cron-table-header divewp-cron-table-header--as">' +
                '<div class="col-check"><input type="checkbox" class="divewp-cron-select-all"></div>' +
                '<div class="col-hook">' + this.escapeHtml('Action') + '</div>' +
                '<div class="col-next">' + this.escapeHtml('Scheduled') + '</div>' +
                '<div class="col-status">' + this.escapeHtml('Status') + '</div>' +
                '<div class="col-group">' + this.escapeHtml('Group') + '</div>' +
                '<div class="col-actions">' + this.escapeHtml('Actions') + '</div>' +
                '</div>' +
                '<div class="divewp-cron-table-body">';

            for (var i = 0; i < actions.length; i++) {
                var action = actions[i];
                var rowClass = 'divewp-cron-row divewp-cron-row--as-queue';
                var validation = action.validation || { status: 'healthy' };

                if (action.is_overdue) rowClass += ' divewp-cron-row--overdue';
                if (validation.status === 'potential_orphan' || validation.status === 'confirmed_orphan') {
                    rowClass += ' divewp-cron-row--orphaned';
                }

                var rowStatus = action.is_overdue ? 'overdue' : (action.status || 'pending');
                html += '<div class="' + rowClass + '" data-action-id="' + action.action_id + '" data-hook="' + this.escapeHtml(action.hook) + '" data-timestamp="' + action.timestamp + '" data-source="' + this.escapeHtml(action.source || '') + '" data-status="' + rowStatus + '">';
                html += '<div class="col-check"><input type="checkbox" class="divewp-cron-select" value="' + this.escapeHtml('as|' + action.action_id) + '"></div>';
                html += '<div class="col-hook"><strong class="divewp-cron-hook-name">' + this.escapeHtml(action.hook) + '</strong>';
                
                if (validation.status === 'potential_orphan') {
                    html += ' <span class="status-pill status-pill-warning">Potential orphan</span>';
                } else if (validation.status === 'confirmed_orphan') {
                    html += ' <span class="status-pill status-pill-danger">Orphaned</span>';
                }

                if (action.args_display && action.args_display !== 'None') {
                    html += '<span class="divewp-cron-args">' + this.escapeHtml(action.args_display) + '</span>';
                }
                html += '</div>';
                html += '<div class="col-next"><span class="divewp-cron-next-run">' + this.escapeHtml(action.next_run) + '</span></div>';
                html += '<div class="col-status"><span class="status-pill status-pill-' + this.getStatusClass(action.status) + '">' + this.escapeHtml(action.status_label) + '</span></div>';
                html += '<div class="col-group"><span class="divewp-cron-source">' + this.escapeHtml(action.group || '-') + '</span></div>';
                html += '<div class="col-actions">';
                if (action.status === 'pending') {
                    html += '<button type="button" class="button button-small divewp-cron-as-action" data-action="run" data-id="' + action.action_id + '" title="Run Now"><span class="dashicons dashicons-controls-play"></span></button>';
                    html += '<button type="button" class="button button-small divewp-cron-as-action" data-action="cancel" data-id="' + action.action_id + '" title="Cancel"><span class="dashicons dashicons-no"></span></button>';
                }
                html += '</div></div>';
            }

            html += '</div></div>';
            $panel.html(html);
            this.updateBulkActions();
        },

        /**
         * Load execution log content (grouped by hook)
         */
        loadExecutionLogContent: function() {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="execution-log"]');

            $panel.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_logs',
                    nonce: this.config.nonce,
                    limit: 500
                },
                success: function(response) {
                    if (response.success) {
                        self.renderExecutionLog(response.data.hooks);
                    } else {
                        $panel.html('<div class="divewp-cron-empty"><p>' + (response.data.message || self.config.strings.error) + '</p></div>');
                    }
                },
                error: function() {
                    $panel.html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Render execution log (grouped by hook)
         */
        renderExecutionLog: function(hooks) {
            var self = this;
            var $panel = $('.divewp-cron-panel[data-panel="execution-log"]');

            if (!hooks || hooks.length === 0) {
                $panel.html('<div class="divewp-cron-empty"><span class="dashicons dashicons-media-text"></span><p>' + this.config.strings.noLogs + '</p></div>');
                return;
            }

            var html = '<div class="divewp-cron-log-grouped">';
            html += '<div class="divewp-cron-log-header">';
            html += '<div class="divewp-cron-log-header__check"><input type="checkbox" class="divewp-cron-select-all"></div>';
            html += '<div class="divewp-cron-log-header__hook">Hook Name</div>';
            html += '<div class="divewp-cron-log-header__last-run">Last Run</div>';
            html += '<div class="divewp-cron-log-header__runs">Runs</div>';
            html += '<div class="divewp-cron-log-header__success">Success</div>';
            html += '<div class="divewp-cron-log-header__duration">Avg Time</div>';
            html += '</div>';

            for (var i = 0; i < hooks.length; i++) {
                var hook = hooks[i];
                var statusClass = this.getStatusClass(hook.last_status);
                var successRate = hook.success_rate || 0;
                var successClass = successRate >= 90 ? 'success' : (successRate >= 50 ? 'warning' : 'error');

                html += '<div class="divewp-cron-log-hook-row" data-hook="' + this.escapeHtml(hook.hook) + '" data-status="' + statusClass + '" data-source="logs">';
                html += '<div class="divewp-cron-log-hook-row__check"><input type="checkbox" class="divewp-cron-select" value="' + this.escapeHtml('log|' + hook.hook) + '"></div>';
                html += '<div class="divewp-cron-log-hook-row__status divewp-cron-log-status--' + statusClass + '"></div>';
                html += '<div class="divewp-cron-log-hook-row__hook">' + this.escapeHtml(hook.hook) + '</div>';
                var lastRun = hook.last_run_local || hook.last_run || '-';
                html += '<div class="divewp-cron-log-hook-row__last-run">' + this.escapeHtml(lastRun) + '</div>';
                html += '<div class="divewp-cron-log-hook-row__runs"><span class="divewp-cron-badge">' + hook.total_runs + '</span></div>';
                html += '<div class="divewp-cron-log-hook-row__success"><span class="divewp-cron-badge divewp-cron-badge--' + successClass + '">' + successRate + '%</span></div>';
                html += '<div class="divewp-cron-log-hook-row__duration">' + (hook.avg_duration_ms || 0) + 'ms</div>';
                html += '</div>';
            }

            html += '</div>';
            $panel.html(html);
            this.updateBulkActions();

            // Add click handlers for hook rows
            $panel.find('.divewp-cron-log-hook-row').on('click', function(e) {
                // Ignore checkbox clicks
                if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.divewp-cron-log-hook-row__check').length) {
                    return;
                }
                var hookName = $(this).data('hook');
                self.openHookHistoryModal(hookName);
            });
        },

        /**
         * Open hook history modal
         */
        openHookHistoryModal: function(hookName) {
            var self = this;

            // Get drawer elements
            var $drawer = $('.divewp-cron-drawer');
            var $content = $drawer.find('.divewp-cron-drawer__content');
            var $title = $drawer.find('.divewp-cron-drawer__title');
            var $footer = $drawer.find('.divewp-cron-drawer__footer');

            // Clear current row (not a task row)
            this.state.currentRow = null;

            // Set title and show loading
            $title.text(hookName);
            $content.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');

            // Open the drawer
            $drawer.addClass('open');

            // Fetch hook history
            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_get_hook_logs',
                    nonce: this.config.nonce,
                    hook: hookName,
                    limit: 50
                },
                success: function(response) {
                    if (response.success) {
                        self.renderHookHistoryModal(response.data, hookName);
                    } else {
                        $content.html('<div class="divewp-cron-empty"><p>' + (response.data.message || self.config.strings.error) + '</p></div>');
                        $footer.hide();
                    }
                },
                error: function() {
                    $content.html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                    $footer.hide();
                }
            });
        },

        /**
         * Render hook history modal content
         */
        renderHookHistoryModal: function(data, hookName) {
            var $content = $('.divewp-cron-drawer__content');
            var $footer = $('.divewp-cron-drawer__footer');
            var summary = data.summary;
            var executions = data.executions;

            var html = '<div class="divewp-cron-hook-history">';

            // Summary stats
            html += '<div class="divewp-cron-hook-summary">';
            html += '<div class="divewp-cron-hook-summary__item">';
            html += '<span class="divewp-cron-hook-summary__value">' + summary.total_runs + '</span>';
            html += '<span class="divewp-cron-hook-summary__label">Total Runs</span>';
            html += '</div>';
            html += '<div class="divewp-cron-hook-summary__item">';
            html += '<span class="divewp-cron-hook-summary__value divewp-cron-hook-summary__value--' + (summary.success_rate >= 90 ? 'success' : (summary.success_rate >= 50 ? 'warning' : 'error')) + '">' + summary.success_rate + '%</span>';
            html += '<span class="divewp-cron-hook-summary__label">Success Rate</span>';
            html += '</div>';
            html += '<div class="divewp-cron-hook-summary__item">';
            html += '<span class="divewp-cron-hook-summary__value">' + summary.avg_duration_ms + 'ms</span>';
            html += '<span class="divewp-cron-hook-summary__label">Avg Duration</span>';
            html += '</div>';
            html += '<div class="divewp-cron-hook-summary__item">';
            html += '<span class="divewp-cron-hook-summary__value">' + (summary.min_duration_ms || 0) + ' - ' + (summary.max_duration_ms || 0) + 'ms</span>';
            html += '<span class="divewp-cron-hook-summary__label">Min / Max</span>';
            html += '</div>';
            html += '</div>';

            // Execution timeline
            html += '<div class="divewp-cron-hook-timeline">';
            html += '<h5>Execution History</h5>';

            if (executions.length === 0) {
                html += '<p class="divewp-cron-empty-text">No executions recorded.</p>';
            } else {
                html += '<div class="divewp-cron-hook-timeline__list">';
                for (var i = 0; i < executions.length; i++) {
                    var exec = executions[i];
                    var statusClass = this.getStatusClass(exec.status);
                    var statusIcon = this.getStatusIcon(exec.status);

                    html += '<div class="divewp-cron-hook-timeline__item">';
                    html += '<div class="divewp-cron-hook-timeline__status divewp-cron-log-status--' + statusClass + '">';
                    html += '<span class="dashicons ' + statusIcon + '"></span>';
                    html += '</div>';
                    html += '<div class="divewp-cron-hook-timeline__content">';
                    var startedAt = exec.started_at_local || exec.started_at || '';
                    html += '<div class="divewp-cron-hook-timeline__time">' + this.escapeHtml(startedAt) + '</div>';
                    html += '<div class="divewp-cron-hook-timeline__meta">';
                    html += '<span class="divewp-cron-hook-timeline__duration">' + (exec.duration_ms || 0) + 'ms</span>';
                    html += '<span class="divewp-cron-hook-timeline__source">' + this.escapeHtml(exec.trigger_source) + '</span>';
                    if (exec.error_message) {
                        html += '<span class="divewp-cron-hook-timeline__error">' + this.escapeHtml(exec.error_message) + '</span>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                }
                html += '</div>';
            }

            html += '</div>';
            html += '</div>';

            $content.html(html);

            // Show footer with Run Now button for hook history
            $footer.html('<button type="button" class="button button-primary divewp-cron-drawer__action" data-action="run-hook" data-hook="' + this.escapeHtml(hookName) + '"><span class="dashicons dashicons-controls-play"></span>Run Now</button>');
            $footer.show();
        },

        /**
         * Show execution loading state
         */
        showExecutionLoading: function(title) {
            var $drawer = $('.divewp-cron-drawer');
            var $content = $drawer.find('.divewp-cron-drawer__content');
            var $title = $drawer.find('.divewp-cron-drawer__title');
            var $footer = $drawer.find('.divewp-cron-drawer__footer');

            $title.text(title || 'Executing Task');
            $content.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>Executing task...</p></div>');
            $footer.hide();
            $drawer.addClass('open');
        },

        /**
         * Handle row action
         */
        handleRowAction: function(action, $row) {
            var self = this;
            var hook = $row.data('hook');
            var sig = $row.data('sig');
            var timestamp = $row.data('timestamp');

            switch (action) {
                case 'run-now':
                    if (confirm(this.config.strings.confirmRunNow)) {
                        this.runNow(hook, sig, $row);
                    }
                    break;

                case 'view':
                    this.openDrawer($row);
                    break;

                case 'delete':
                    if (confirm(this.config.strings.confirmDelete)) {
                        this.deleteEvent(hook, sig, timestamp, $row);
                    }
                    break;
            }
        },

        /**
         * Handle Action Scheduler action
         */
        handleAsAction: function(action, actionId, $row) {
            var self = this;

            switch (action) {
                case 'run':
                    if (confirm(this.config.strings.confirmRunNow || 'Run this action now?')) {
                        this.runAsAction(actionId, $row);
                    }
                    break;

                case 'cancel':
                    if (confirm('Cancel this action?')) {
                        this.cancelAsAction(actionId, $row);
                    }
                    break;
            }
        },

        /**
         * Run Action Scheduler action now
         */
        runAsAction: function(actionId, $row) {
            var self = this;
            
            // Show loading modal immediately
            var hookName = $row.find('.divewp-cron-hook-name').text().trim() || 'Action ' + actionId;
            this.showExecutionLoading(hookName);

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_run_as_action',
                    nonce: this.config.nonce,
                    action_id: actionId
                },
                success: function(response) {
                    if (response.success) {
                        // Reload Action Scheduler content in background
                        if (self.state.currentTab === 'action-scheduler') {
                            self.loadActionSchedulerContent();
                        }
                        
                        // Show success message and open drawer with details
                        self.openDrawer($row, response.data.message || 'Action executed successfully.');
                    } else {
                        // Show error in the drawer
                        var errorMsg = response.data.message || self.config.strings.error;
                        $('.divewp-cron-drawer__content').html('<div class="divewp-cron-empty"><p>' + self.escapeHtml(errorMsg) + '</p></div>');
                    }
                },
                error: function() {
                    // Show error in the drawer
                    $('.divewp-cron-drawer__content').html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Cancel Action Scheduler action
         */
        cancelAsAction: function(actionId, $row) {
            var self = this;
            $row.addClass('loading');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_cancel_as_action',
                    nonce: this.config.nonce,
                    action_id: actionId
                },
                success: function(response) {
                    $row.removeClass('loading');
                    if (response.success) {
                        self.showNotice(response.data.message || 'Action canceled successfully.', 'success');
                        // Reload Action Scheduler content
                        if (self.state.currentTab === 'action-scheduler') {
                            self.loadActionSchedulerContent();
                        }
                    } else {
                        self.showNotice(response.data.message || self.config.strings.error, 'error');
                    }
                },
                error: function() {
                    $row.removeClass('loading');
                    self.showNotice(self.config.strings.error, 'error');
                }
            });
        },

        /**
         * Run event now
         */
        runNow: function(hook, sig, $row) {
            var self = this;
            
            // Show loading modal immediately
            var hookName = $row.find('.divewp-cron-hook-name').text().trim() || hook;
            this.showExecutionLoading(hookName);

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_run_now',
                    nonce: this.config.nonce,
                    hook: hook,
                    sig: sig
                },
                success: function(response) {
                    if (response.success) {
                        // Refresh current tab to update the table in background
                        if (self.state.currentTab === 'wp-cron' || self.state.currentTab === 'overdue') {
                            self.refresh();
                        }

                        // Update the already open drawer with success message and details
                        self.openDrawer($row, response.data.message);
                    } else {
                        // Show error in the drawer
                        var errorMsg = response.data.message || self.config.strings.error;
                        $('.divewp-cron-drawer__content').html('<div class="divewp-cron-empty"><p>' + self.escapeHtml(errorMsg) + '</p></div>');
                    }
                },
                error: function() {
                    // Show error in the drawer
                    $('.divewp-cron-drawer__content').html('<div class="divewp-cron-empty"><p>' + self.config.strings.error + '</p></div>');
                }
            });
        },

        /**
         * Delete event
         */
        deleteEvent: function(hook, sig, timestamp, $row) {
            var self = this;
            $row.addClass('loading');

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_delete',
                    nonce: this.config.nonce,
                    hook: hook,
                    sig: sig,
                    timestamp: timestamp
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            self.updateCounts();
                        });
                        self.showNotice(response.data.message, 'success');
                    } else {
                        $row.removeClass('loading');
                        self.showNotice(response.data.message || self.config.strings.error, 'error');
                    }
                },
                error: function() {
                    $row.removeClass('loading');
                    self.showNotice(self.config.strings.error, 'error');
                }
            });
        },

        /**
         * Update bulk actions button state
         */
        updateBulkActions: function() {
            var selectedCount = $('.divewp-cron-select:checked').length;
            $('.divewp-cron-bulk-apply').prop('disabled', selectedCount === 0);
        },

        /**
         * Handle bulk action
         */
        handleBulkAction: function() {
            var self = this;
            var action = $('.divewp-cron-bulk-action').val();
            var items = [];

            $('.divewp-cron-select:checked').each(function() {
                items.push($(this).val());
            });

            if (!action || items.length === 0) {
                return;
            }

            if (action === 'delete' && !confirm(this.config.strings.confirmBulkDelete)) {
                return;
            }

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_bulk_action',
                    nonce: this.config.nonce,
                    bulk_action: action,
                    items: items
                },
                success: function(response) {
                    if (response.success) {
                        self.refresh();
                        self.showNotice(response.data.message, 'success');
                    } else {
                        self.showNotice(response.data.message || self.config.strings.error, 'error');
                    }
                },
                error: function() {
                    self.showNotice(self.config.strings.error, 'error');
                }
            });
        },

        /**
         * Filter table by search term
         */
        filterTable: function(term) {
            var self = this;
            term = term.toLowerCase();
            
            // Determine which selector to use based on current tab
            var selector = '.divewp-cron-row';
            if (this.state.currentTab === 'execution-log') {
                selector = '.divewp-cron-log-hook-row';
            }
            
            $(selector).each(function() {
                var $row = $(this);
                var hook = ($row.data('hook') || '').toString().toLowerCase();
                var visible = !term || hook.indexOf(term) !== -1;
                $row.toggle(visible);
            });
        },

        /**
         * Apply filters
         */
        applyFilters: function() {
            var source = $('.divewp-cron-filter[data-filter="source"]').val();
            var status = $('.divewp-cron-filter[data-filter="status"]').val();
            var selector = '.divewp-cron-panel.active .divewp-cron-row';

            $(selector).each(function() {
                var $row = $(this);
                var rowSource = ($row.data('source') || '').toString().toLowerCase();
                var rowStatus = ($row.data('status') || '').toString().toLowerCase();
                var visible = true;

                if (source === 'wordpress-core') {
                    visible = rowSource === 'wordpress core';
                } else if (source === 'plugin') {
                    visible = rowSource !== 'wordpress core';
                }

                if (visible && status === 'overdue') {
                    visible = rowStatus === 'overdue';
                } else if (visible && status === 'scheduled') {
                    visible = rowStatus !== 'overdue';
                }

                $row.toggle(visible);
            });
        },

        /**
         * Open drawer with event details
         *
         * @param {jQuery} $row The clicked row element
         * @param {string} successMessage Optional success message to display
         */
        openDrawer: function($row, successMessage) {
            var self = this;
            var hook = $row.data('hook');
            var sig = $row.data('sig');
            var timestamp = $row.data('timestamp');
            var actionId = $row.data('action-id');
            var isActionScheduler = $row.hasClass('divewp-cron-row--as-queue') || actionId !== undefined;

            // Store current row for actions
            this.state.currentRow = $row;

            var $drawer = $('.divewp-cron-drawer');
            var $content = $drawer.find('.divewp-cron-drawer__content');
            var $footer = $drawer.find('.divewp-cron-drawer__footer');

            // Only show loading if drawer is not already open (to avoid overwriting execution loading)
            if (!$drawer.hasClass('open')) {
                $content.html('<div class="divewp-loading-container"><div class="divewp-loader"></div><p>' + this.config.strings.loading + '</p></div>');
                $footer.show();
                $drawer.addClass('open');
            }

            // If we have a success message, render content immediately without AJAX
            if (successMessage) {
                // Prepare AJAX data based on task type
                var ajaxData = {
                    action: 'divewp_cron_get_event_details',
                    nonce: this.config.nonce
                };

                if (isActionScheduler && actionId) {
                    // Action Scheduler task
                    ajaxData.action_id = actionId;
                } else {
                    // WP-Cron task
                    ajaxData.hook = hook;
                    ajaxData.sig = sig;
                    ajaxData.timestamp = timestamp;
                }

                $.ajax({
                    url: this.config.ajaxurl,
                    type: 'POST',
                    data: ajaxData,
                    success: function(response) {
                        if (response.success) {
                            self.renderDrawerContent(response.data, successMessage);
                        } else {
                            $content.html('<p>' + (response.data.message || self.config.strings.error) + '</p>');
                        }
                    },
                    error: function() {
                        $content.html('<p>' + self.config.strings.error + '</p>');
                    }
                });
                return;
            }

            // Normal drawer opening (no success message)
            // Prepare AJAX data based on task type
            var ajaxData = {
                action: 'divewp_cron_get_event_details',
                nonce: this.config.nonce
            };

            if (isActionScheduler && actionId) {
                // Action Scheduler task
                ajaxData.action_id = actionId;
            } else {
                // WP-Cron task
                ajaxData.hook = hook;
                ajaxData.sig = sig;
                ajaxData.timestamp = timestamp;
            }

            $.ajax({
                url: this.config.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        self.renderDrawerContent(response.data);
                    } else {
                        $content.html('<p>' + (response.data.message || self.config.strings.error) + '</p>');
                    }
                },
                error: function() {
                    $content.html('<p>' + self.config.strings.error + '</p>');
                }
            });
        },

        /**
         * Render drawer content
         * 
         * @param {object} data The response data
         * @param {string} successMessage Optional success message to display
         */
        renderDrawerContent: function(data, successMessage) {
            var event = data.event;
            var logs = data.logs || [];
            var fallback = data.fallback || null;

            if (!event && fallback) {
                this.renderFallbackLogs(fallback, logs, successMessage);
                return;
            }

            if (!event) {
                $('.divewp-cron-drawer__content').html('<p>' + this.escapeHtml(this.config.strings.error || 'Unable to load task details.') + '</p>');
                return;
            }

            // Detect if this is an Action Scheduler event
            var isActionScheduler = event.type === 'action_scheduler' || event.action_id !== undefined;

            // Status indicator
            var statusClass = event.is_overdue ? 'overdue' : 'scheduled';
            var statusLabel = event.is_overdue ? 'Overdue' : 'Scheduled';
            var statusIcon = event.is_overdue ? 'dashicons-warning' : 'dashicons-clock';

            var html = '<div class="divewp-modal-details">';
            
            // Success Message (if provided)
            if (successMessage) {
                html += '<div class="divewp-modal-notice">';
                html += '<span class="dashicons dashicons-yes-alt"></span>';
                html += '<span>' + this.escapeHtml(successMessage) + '</span>';
                html += '</div>';
            }

            // Orphaned explanation at top if task is orphaned
            var validation = event.validation || { status: 'healthy' };
            if (validation.status === 'potential_orphan' || validation.status === 'confirmed_orphan') {
                var isConfirmed = validation.status === 'confirmed_orphan';
                var noticeTitle = isConfirmed ? 'Confirmed orphaned task' : 'Potential orphaned task (verify before deleting)';
                var noticeClass = isConfirmed ? 'divewp-orphaned-notice--danger' : '';

                html += '<div class="divewp-orphaned-notice ' + noticeClass + '">';
                html += '<div class="divewp-orphaned-notice__header">';
                html += '<span class="dashicons ' + (isConfirmed ? 'dashicons-warning' : 'dashicons-info') + '"></span>';
                html += '<strong>' + this.escapeHtml(noticeTitle) + '</strong>';
                html += '</div>';
                html += '<div class="divewp-orphaned-notice__content">';
                
                // Show the specific validation message from backend
                if (validation.message) {
                    html += '<p>' + this.escapeHtml(validation.message) + '</p>';
                }

                if (isActionScheduler) {
                    // Action Scheduler orphan explanation
                    html += '<div class="divewp-modal-pill divewp-modal-pill--warning"><strong>Important:</strong> Detection is best-effort. Some plugins load handlers only at runtime. Confirm the plugin status before canceling.</div>';
                } else {
                    // WP-Cron orphan explanation
                    html += '<div class="divewp-modal-pill divewp-modal-pill--warning"><strong>Important:</strong> Detection is not 100% accurate. Some plugins only load code at run time.</div>';
                }
                html += '<div class="divewp-modal-pill divewp-modal-pill--info"><strong>Recommendation:</strong> Safe to delete/cancel if the source was removed from the system.</div>';
                html += '</div>';
                html += '</div>';
            }
            
            // Status badge at top
            html += '<div class="divewp-modal-status divewp-modal-status--' + statusClass + '">';
            html += '<span class="dashicons ' + statusIcon + '"></span>';
            html += '<span>' + statusLabel + '</span>';
            html += '</div>';

            // Hook name (prominent)
            html += '<div class="divewp-modal-hook">';
            html += '<code>' + this.escapeHtml(event.hook) + '</code>';
            html += '</div>';

            // Info grid
            html += '<div class="divewp-modal-grid">';
            
            // Next Run
            html += '<div class="divewp-modal-item">';
            html += '<div class="divewp-modal-item__icon"><span class="dashicons dashicons-calendar-alt"></span></div>';
            html += '<div class="divewp-modal-item__content">';
            html += '<span class="divewp-modal-item__label">Next Run</span>';
            html += '<span class="divewp-modal-item__value">' + this.escapeHtml(event.next_run) + '</span>';
            if (event.next_run_relative) {
                html += '<span class="divewp-modal-item__meta">' + (event.is_overdue ? event.next_run_relative + ' overdue' : 'in ' + event.next_run_relative) + '</span>';
            }
            html += '</div></div>';

            // Schedule (for WP-Cron) or Status (for Action Scheduler)
            if (isActionScheduler) {
                html += '<div class="divewp-modal-item">';
                html += '<div class="divewp-modal-item__icon"><span class="dashicons dashicons-flag"></span></div>';
                html += '<div class="divewp-modal-item__content">';
                html += '<span class="divewp-modal-item__label">Status</span>';
                html += '<span class="divewp-modal-item__value">' + this.escapeHtml(event.status_label || event.status || 'Unknown') + '</span>';
                html += '</div></div>';

                // Group (for Action Scheduler)
                if (event.group) {
                    html += '<div class="divewp-modal-item">';
                    html += '<div class="divewp-modal-item__icon"><span class="dashicons dashicons-groups"></span></div>';
                    html += '<div class="divewp-modal-item__content">';
                    html += '<span class="divewp-modal-item__label">Group</span>';
                    html += '<span class="divewp-modal-item__value">' + this.escapeHtml(event.group) + '</span>';
                    html += '</div></div>';
                }
            } else {
                // Schedule for WP-Cron
            html += '<div class="divewp-modal-item">';
            html += '<div class="divewp-modal-item__icon"><span class="dashicons dashicons-backup"></span></div>';
            html += '<div class="divewp-modal-item__content">';
            html += '<span class="divewp-modal-item__label">Schedule</span>';
                html += '<span class="divewp-modal-item__value">' + this.escapeHtml(event.schedule_label || 'One-time') + '</span>';
            html += '</div></div>';
            }

            // Source
            html += '<div class="divewp-modal-item">';
            html += '<div class="divewp-modal-item__icon"><span class="dashicons dashicons-admin-plugins"></span></div>';
            html += '<div class="divewp-modal-item__content">';
            html += '<span class="divewp-modal-item__label">Source</span>';
            html += '<span class="divewp-modal-item__value">' + this.escapeHtml(event.source || 'Unknown') + '</span>';
            html += '</div></div>';

            html += '</div>'; // end grid

            // Arguments section (collapsible style)
            var argsDisplay = event.args && Object.keys(event.args).length > 0 ? JSON.stringify(event.args, null, 2) : 'No arguments';
            html += '<div class="divewp-modal-args">';
            html += '<div class="divewp-modal-args__header">';
            html += '<span class="dashicons dashicons-editor-code"></span>';
            html += '<span>Arguments</span>';
            html += '</div>';
            html += '<pre class="divewp-modal-args__code">' + this.escapeHtml(argsDisplay) + '</pre>';
            html += '</div>';

            // Recent executions
            if (logs.length > 0) {
                html += '<div class="divewp-modal-logs">';
                html += '<div class="divewp-modal-logs__header">';
                html += '<span class="dashicons dashicons-list-view"></span>';
                html += '<span>Recent Executions</span>';
                html += '</div>';
                html += '<div class="divewp-modal-logs__list">';
                for (var i = 0; i < Math.min(logs.length, 5); i++) {
                    var log = logs[i];
                    var logStatusClass = this.getStatusClass(log.status);
                    var logIcon = log.status === 'success' || log.status === 'complete' ? 'dashicons-yes-alt' : 'dashicons-warning';
                    var startedAt = log.started_at_local || log.started_at || '';
                    
                    html += '<div class="divewp-modal-log-item divewp-modal-log-item--' + logStatusClass + '">';
                    html += '<span class="divewp-modal-log-item__icon dashicons ' + logIcon + '"></span>';
                    html += '<span class="divewp-modal-log-item__time">' + this.escapeHtml(startedAt) + '</span>';
                    if (log.duration_ms) {
                        html += '<span class="divewp-modal-log-item__duration">' + log.duration_ms + 'ms</span>';
                    }
                    html += '</div>';
                }
                html += '</div></div>';
            }

            html += '</div>'; // end modal-details

            $('.divewp-cron-drawer__content').html(html);
        },

        /**
         * Render fallback content when task already executed
         * 
         * @param {object} fallback Fallback data
         * @param {array} logs Execution logs
         * @param {string} successMessage Optional success message
         */
        renderFallbackLogs: function(fallback, logs, successMessage) {
            var hookName = fallback.hook ? this.escapeHtml(fallback.hook) : '';
            var message = fallback.message ? this.escapeHtml(fallback.message) : 'This task already executed.';

            // Hide footer actions because task no longer exists
            $('.divewp-cron-drawer__footer').hide();

            var html = '<div class="divewp-modal-details">';
            
            // Success Message (if provided)
            if (successMessage) {
                html += '<div class="divewp-modal-notice">';
                html += '<span class="dashicons dashicons-yes-alt"></span>';
                html += '<span>' + this.escapeHtml(successMessage) + '</span>';
                html += '</div>';
            }
            
            html += '<div class="divewp-modal-status divewp-modal-status--executed">';
            html += '<span class="dashicons dashicons-yes-alt"></span>';
            html += '<span>' + message + '</span>';
            html += '</div>';

            if (hookName) {
                html += '<div class="divewp-modal-hook">';
                html += '<code>' + hookName + '</code>';
                html += '</div>';
            }

            if (logs.length > 0) {
                html += '<div class="divewp-modal-logs">';
                html += '<div class="divewp-modal-logs__header">';
                html += '<span class="dashicons dashicons-list-view"></span>';
                html += '<span>Recent Executions</span>';
                html += '</div>';
                html += '<div class="divewp-modal-logs__list">';
                for (var i = 0; i < Math.min(logs.length, 5); i++) {
                    var log = logs[i];
                    var logStatusClass = this.getStatusClass(log.status);
                    var logIcon = log.status === 'success' || log.status === 'complete' ? 'dashicons-yes-alt' : 'dashicons-warning';

                    html += '<div class="divewp-modal-log-item divewp-modal-log-item--' + logStatusClass + '">';
                    html += '<span class="divewp-modal-log-item__icon dashicons ' + logIcon + '"></span>';
                    html += '<span class="divewp-modal-log-item__time">' + this.escapeHtml(log.started_at) + '</span>';
                    if (log.duration_ms) {
                        html += '<span class="divewp-modal-log-item__duration">' + log.duration_ms + 'ms</span>';
                    }
                    html += '</div>';
                }
                html += '</div></div>';
            } else {
                html += '<p class="divewp-cron-empty-text">' + this.escapeHtml('No recent executions recorded.') + '</p>';
            }

            html += '</div>';

            $('.divewp-cron-drawer__content').html(html);
        },

        /**
         * Close drawer
         */
        closeDrawer: function() {
            $('.divewp-cron-drawer').removeClass('open');
            this.state.currentRow = null;
        },

        /**
         * Handle drawer action
         */
        handleDrawerAction: function(action) {
            var self = this;

            // Add Task modal actions (not tied to a row)
            if (action === 'add-cancel') {
                this.closeDrawer();
                return;
            }
            if (action === 'add-save') {
                this.submitAddTaskForm();
                return;
            }

            // Handle run-hook action for execution log modal
            if (action === 'run-hook') {
                var $button = $('.divewp-cron-drawer__action[data-action="run-hook"]');
                var hookName = $button.data('hook');

                // Close drawer and show execution loading immediately
                this.closeDrawer();
                this.showExecutionLoading(hookName);

                // Try to find and run the hook from scheduled tasks
                // First check WP-Cron events
                var foundRow = null;
                $('.divewp-cron-row').each(function() {
                    var $row = $(this);
                    if ($row.data('hook') === hookName) {
                        foundRow = $row;
                        return false; // break
                    }
                });

                if (foundRow) {
                    // Found in WP-Cron
                    var hook = foundRow.data('hook');
                    var sig = foundRow.data('sig');
                    this.runNow(hook, sig, foundRow);
                } else {
                    // Check Action Scheduler
                    $('.divewp-cron-row--as').each(function() {
                        var $row = $(this);
                        if ($row.data('hook') === hookName) {
                            foundRow = $row;
                            return false; // break
                        }
                    });

                    if (foundRow) {
                        // Found in Action Scheduler
                        var actionId = foundRow.data('action-id');
                        this.runAsAction(actionId, foundRow);
                    } else {
                        // Hook not found in scheduled tasks, show error
                        $('.divewp-cron-drawer__content').html('<div class="divewp-cron-empty"><p>This hook is not currently scheduled as a task.</p></div>');
                    }
                }
                return;
            }

            if (!this.state.currentRow) {
                return;
            }

            var $row = this.state.currentRow;

            // Handle run-now action directly for immediate feedback
            if (action === 'run-now') {
                var hookName = $row.find('.divewp-cron-hook-name').text().trim();

                // Close drawer and show execution loading immediately
                this.closeDrawer();
                this.showExecutionLoading(hookName);

                // Check if this is an Action Scheduler row
                var actionId = $row.data('action-id');
                if (actionId) {
                    // Action Scheduler action
                    this.runAsAction(actionId, $row);
                } else {
                    // WP-Cron event
                    var hook = $row.data('hook');
                    var sig = $row.data('sig');
                    this.runNow(hook, sig, $row);
                }
                return;
            }

            // Handle other actions normally
            this.closeDrawer();
            this.handleRowAction(action, this.state.currentRow);
        },

        /**
         * Copy to clipboard
         */
        copyToClipboard: function(text) {
            var self = this;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showNotice(self.config.strings.copied, 'success');
                });
            } else {
                // Fallback for older browsers
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                self.showNotice(self.config.strings.copied, 'success');
            }
        },

        /**
         * Show add task modal
         */
        showAddTaskModal: function() {
            var self = this;

            var $drawer = $('.divewp-cron-drawer');
            var $content = $drawer.find('.divewp-cron-drawer__content');
            var $title = $drawer.find('.divewp-cron-drawer__title');
            var $footer = $drawer.find('.divewp-cron-drawer__footer');

            // Reset state (this modal is not tied to a row)
            this.state.currentRow = null;

            // Title
            $title.text(this.config.strings.addTaskTitle || 'Add Task');

            // Form HTML (keep minimal; rely on WordPress admin form styling)
            var schedules = (divewpCronData && divewpCronData.schedules) ? divewpCronData.schedules : {};
            var scheduleOptions = '<option value="once">' + this.escapeHtml(this.config.strings.scheduleOnce || 'One-time') + '</option>';
            Object.keys(schedules).forEach(function(key) {
                scheduleOptions += '<option value="' + self.escapeHtml(key) + '">' + self.escapeHtml(schedules[key]) + '</option>';
            });

            var html = '';
            html += '<form class="divewp-cron-add-form" novalidate>';
            html += '  <div class="divewp-cron-add-form__field">';
            html += '    <label class="divewp-cron-add-form__label" for="divewp-cron-add-hook">' + this.escapeHtml(this.config.strings.hookLabel || 'Hook name') + '</label>';
            html += '    <input type="text" class="divewp-cron-add-form__input" id="divewp-cron-add-hook" name="hook" placeholder="' + this.escapeHtml(this.config.strings.hookPlaceholder || 'example_hook_name') + '" autocomplete="off" spellcheck="false" required>';
            html += '    <p class="divewp-cron-add-form__help">' + this.escapeHtml(this.config.strings.hookHelp || 'Letters, numbers, and underscores only.') + '</p>';
            html += '    <p class="divewp-cron-add-form__error" data-error-for="hook" aria-live="polite"></p>';
            html += '  </div>';

            html += '  <div class="divewp-cron-add-form__field">';
            html += '    <label class="divewp-cron-add-form__label" for="divewp-cron-add-when">' + this.escapeHtml(this.config.strings.runAtLabel || 'Run at') + '</label>';
            html += '    <input type="datetime-local" class="divewp-cron-add-form__input" id="divewp-cron-add-when" name="run_at" required>';
            html += '    <p class="divewp-cron-add-form__help">' + this.escapeHtml(this.config.strings.runAtHelp || 'Uses your current browser time.') + '</p>';
            html += '    <p class="divewp-cron-add-form__error" data-error-for="run_at" aria-live="polite"></p>';
            html += '  </div>';

            html += '  <div class="divewp-cron-add-form__field">';
            html += '    <label class="divewp-cron-add-form__label" for="divewp-cron-add-schedule">' + this.escapeHtml(this.config.strings.scheduleLabel || 'Schedule') + '</label>';
            html += '    <select class="divewp-cron-add-form__input" id="divewp-cron-add-schedule" name="schedule">';
            html += scheduleOptions;
            html += '    </select>';
            html += '    <p class="divewp-cron-add-form__error" data-error-for="schedule" aria-live="polite"></p>';
            html += '  </div>';

            html += '  <div class="divewp-cron-add-form__field">';
            html += '    <label class="divewp-cron-add-form__label" for="divewp-cron-add-args">' + this.escapeHtml(this.config.strings.argsLabel || 'Arguments (JSON)') + '</label>';
            html += '    <textarea class="divewp-cron-add-form__input divewp-cron-add-form__textarea" id="divewp-cron-add-args" name="args" rows="5" placeholder="' + this.escapeHtml(this.config.strings.argsPlaceholder || '[]') + '"></textarea>';
            html += '    <p class="divewp-cron-add-form__help">' + this.escapeHtml(this.config.strings.argsHelp || 'Optional. Example: [\"abc\", 123]') + '</p>';
            html += '    <p class="divewp-cron-add-form__error" data-error-for="args" aria-live="polite"></p>';
            html += '  </div>';

            html += '</form>';

            $content.html(html);

            // Footer buttons
            $footer.html(
                '<button type="button" class="button button-secondary divewp-cron-drawer__action" data-action="add-cancel">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                    self.escapeHtml(self.config.strings.cancel || 'Cancel') +
                '</button>' +
                '<button type="button" class="button button-primary divewp-cron-drawer__action" data-action="add-save">' +
                    '<span class="dashicons dashicons-yes-alt"></span>' +
                    self.escapeHtml(self.config.strings.saveTask || 'Save Task') +
                '</button>'
            );
            $footer.show();

            // Open drawer
            $drawer.addClass('open');

            // Focus first field
            setTimeout(function() {
                $('#divewp-cron-add-hook').trigger('focus');
            }, 0);
        },

        /**
         * Validate and submit add task form
         */
        submitAddTaskForm: function() {
            var self = this;
            var $form = $('.divewp-cron-add-form');
            if (!$form.length) {
                return;
            }

            self.clearAddTaskErrors();

            var hook = ($form.find('[name="hook"]').val() || '').toString().trim();
            var schedule = ($form.find('[name="schedule"]').val() || 'once').toString().trim();
            var runAt = ($form.find('[name="run_at"]').val() || '').toString().trim();
            var argsRaw = ($form.find('[name="args"]').val() || '').toString().trim();

            // Hook: letters, numbers, underscores only
            if (!hook) {
                self.setAddTaskError('hook', self.config.strings.errorHookRequired || 'Hook name is required.');
                return;
            }
            if (!/^[A-Za-z0-9_]+$/.test(hook)) {
                self.setAddTaskError('hook', self.config.strings.errorHookInvalid || 'Only letters, numbers, and underscores are allowed.');
                return;
            }

            // Run time: required and must parse
            if (!runAt) {
                self.setAddTaskError('run_at', self.config.strings.errorRunAtRequired || 'Run time is required.');
                return;
            }

            var runDate = new Date(runAt);
            if (isNaN(runDate.getTime())) {
                self.setAddTaskError('run_at', self.config.strings.errorRunAtInvalid || 'Invalid date/time.');
                return;
            }

            var timestamp = Math.floor(runDate.getTime() / 1000);
            var nowTs = Math.floor(Date.now() / 1000);
            if (timestamp <= nowTs) {
                self.setAddTaskError('run_at', self.config.strings.errorRunAtPast || 'Please choose a future time.');
                return;
            }

            // Args: optional JSON
            var argsJson = '[]';
            if (argsRaw) {
                try {
                    var parsed = JSON.parse(argsRaw);
                    argsJson = JSON.stringify(parsed);
                } catch (e) {
                    self.setAddTaskError('args', self.config.strings.errorArgsInvalid || 'Arguments must be valid JSON.');
                    return;
                }
            }

            // Disable save button while working
            var $saveBtn = $('.divewp-cron-drawer__action[data-action="add-save"]');
            $saveBtn.prop('disabled', true);

            $.ajax({
                url: self.config.ajaxurl,
                type: 'POST',
                data: {
                    action: 'divewp_cron_add_event',
                    nonce: self.config.nonce,
                    hook: hook,
                    schedule: schedule,
                    timestamp: timestamp,
                    args: argsJson
                },
                success: function(response) {
                    $saveBtn.prop('disabled', false);
                    if (response && response.success) {
                        self.showNotice((response.data && response.data.message) ? response.data.message : (self.config.strings.success || 'Saved.'), 'success');
                        self.closeDrawer();

                        // Ensure WP-Cron tab shows the new task
                        self.switchTab('wp-cron');
                        self.loadWpCronEvents();
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : self.config.strings.error;
                        self.showNotice(msg, 'error');
                    }
                },
                error: function() {
                    $saveBtn.prop('disabled', false);
                    self.showNotice(self.config.strings.error, 'error');
                }
            });
        },

        /**
         * Clear inline errors for add task form
         */
        clearAddTaskErrors: function() {
            $('.divewp-cron-add-form__error').text('').hide();
        },

        /**
         * Set inline error message for add task form
         */
        setAddTaskError: function(field, message) {
            var $el = $('.divewp-cron-add-form__error[data-error-for="' + field + '"]');
            if ($el.length) {
                $el.text(message).show();
            }
        },

        /**
         * Update event counts
         */
        updateCounts: function() {
            var wpCronCount = $('.divewp-cron-panel[data-panel="wp-cron"] .divewp-cron-row').length;
            $('.divewp-cron-tab[data-tab="wp-cron"] .divewp-cron-tab__count').text(wpCronCount);
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            // Use existing DiveWP notice system if available
            if (typeof divewpData !== 'undefined' && typeof divewpData.showNotice === 'function') {
                divewpData.showNotice(message, type);
                return;
            }

            // Fallback to simple alert
            if (type === 'error') {
                alert('Error: ' + message);
            }
        },

        /**
         * Get status CSS class
         */
        getStatusClass: function(status) {
            var classes = {
                'success': 'success',
                'complete': 'success',
                'error': 'danger',
                'failed': 'danger',
                'warning': 'warning',
                'running': 'info',
                'pending': 'warning',
                'in-progress': 'info'
            };
            return classes[status] || 'info';
        },

        /**
         * Get status icon
         */
        getStatusIcon: function(status) {
            var icons = {
                'success': 'dashicons-yes-alt',
                'complete': 'dashicons-yes-alt',
                'error': 'dashicons-warning',
                'failed': 'dashicons-warning',
                'warning': 'dashicons-info',
                'running': 'dashicons-update'
            };
            return icons[status] || 'dashicons-marker';
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CronDashboard.init();
    });

})(jQuery);
