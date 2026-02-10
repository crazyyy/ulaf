/**
 * Hungry REST API Monitor - Admin Dashboard JavaScript
 *
 * @package HungryRestApiMonitor
 */

(function ($) {
    'use strict';

    // Chart instances
    let trafficChart = null;
    let methodChart = null;
    let statusChart = null;

    // Current state
    let currentPage = 1;
    let currentOrderBy = 'recorded_at';
    let currentOrder = 'DESC';

    /**
     * Initialize dashboard
     */
    function initDashboard() {
        loadDashboardData();
        initDashboardEvents();
    }

    /**
     * Initialize dashboard events
     */
    function initDashboardEvents() {
        // Period selector
        $('#nandrestapi-period').on('change', loadDashboardData);
        $('#nandrestapi-refresh').on('click', loadDashboardData);
    }

    /**
     * Load dashboard data via AJAX
     */
    function loadDashboardData() {
        const period = $('#nandrestapi-period').val() || '7d';

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'nandrestapi_get_dashboard_data',
                nonce: nandrestapiAdmin.nonce,
                period: period
            },
            beforeSend: function () {
                $('#nandrestapi-top-endpoints tbody').html(
                    '<tr><td colspan="5" class="nandrestapi-loading">Loading...</td></tr>'
                );
            },
            success: function (response) {
                if (response.success) {
                    updateDashboard(response.data);
                } else {
                    showError(response.data.message || nandrestapiAdmin.strings.error);
                }
            },
            error: function () {
                showError(nandrestapiAdmin.strings.error);
            }
        });
    }

    /**
     * Update dashboard with data
     */
    function updateDashboard(data) {
        // Update summary cards
        $('#stat-total-requests').text(formatNumber(data.summary.total_requests || 0));
        $('#stat-avg-time').text(formatTime(data.summary.avg_response_time || 0));
        $('#stat-error-rate').text(parseFloat(data.summary.error_rate || 0).toFixed(1) + '%');
        $('#stat-endpoints').text(formatNumber(data.summary.unique_endpoints || 0));
        $('#stat-db-size').text(data.database_size || '0 B');
        $('#stat-log-count').text(formatNumber(data.log_count || 0));

        // Render charts
        renderTrafficChart(data.requests_over_time || []);
        renderMethodChart(data.method_distribution || []);
        renderStatusChart(data.status_distribution || []);

        // Render top endpoints table
        renderTopEndpoints(data.top_endpoints || []);
    }

    /**
     * Render traffic line chart
     */
    function renderTrafficChart(data) {
        const ctx = document.getElementById('nandrestapi-traffic-chart');
        if (!ctx) return;

        const labels = data.map(item => item.time_bucket);
        const requests = data.map(item => parseInt(item.total_requests || 0));
        const errors = data.map(item => parseInt(item.error_count || 0));

        if (trafficChart) {
            trafficChart.destroy();
        }

        trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Requests',
                        data: requests,
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Errors',
                        data: errors,
                        borderColor: '#d63638',
                        backgroundColor: 'rgba(214, 54, 56, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Render method distribution pie chart
     */
    function renderMethodChart(data) {
        const ctx = document.getElementById('nandrestapi-method-chart');
        if (!ctx) return;

        const labels = data.map(item => item.method);
        const counts = data.map(item => parseInt(item.count || 0));
        const colors = {
            'GET': '#2271b1',
            'POST': '#00a32a',
            'PUT': '#dba617',
            'PATCH': '#c2185b',
            'DELETE': '#d63638'
        };

        if (methodChart) {
            methodChart.destroy();
        }

        methodChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: labels.map(l => colors[l] || '#72aee6')
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Render status distribution pie chart
     */
    function renderStatusChart(data) {
        const ctx = document.getElementById('nandrestapi-status-chart');
        if (!ctx) return;

        const labels = data.map(item => item.status_group);
        const counts = data.map(item => parseInt(item.count || 0));
        const colors = {
            '2xx': '#00a32a',
            '3xx': '#72aee6',
            '4xx': '#dba617',
            '5xx': '#d63638'
        };

        if (statusChart) {
            statusChart.destroy();
        }

        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: labels.map(l => colors[l] || '#c3c4c7')
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Render top endpoints table
     */
    function renderTopEndpoints(endpoints) {
        const tbody = $('#nandrestapi-top-endpoints tbody');

        if (!endpoints.length) {
            tbody.html('<tr><td colspan="5" class="nandrestapi-loading">' + nandrestapiAdmin.strings.no_data + '</td></tr>');
            return;
        }

        let html = '';
        endpoints.forEach(function (endpoint) {
            const errorClass = parseFloat(endpoint.error_rate) > 5 ? 'nandrestapi-status-4xx' : '';
            html += '<tr>';
            html += '<td><code>' + escapeHtml(endpoint.endpoint) + '</code></td>';
            html += '<td>' + formatNumber(endpoint.total_calls) + '</td>';
            html += '<td>' + formatTime(endpoint.avg_time) + '</td>';
            html += '<td>' + formatBytes(endpoint.avg_memory) + '</td>';
            html += '<td><span class="nandrestapi-status ' + errorClass + '">' + parseFloat(endpoint.error_rate).toFixed(1) + '%</span></td>';
            html += '</tr>';
        });

        tbody.html(html);
    }

    /**
     * Initialize endpoints tab
     */
    function initEndpoints() {
        loadEndpoints();

        $('#nandrestapi-endpoints-refresh').on('click', loadEndpoints);

        // Sortable headers
        $('.nandrestapi-endpoints-table .nandrestapi-sortable').on('click', function () {
            const column = $(this).data('sort');
            $('#nandrestapi-endpoints-orderby').val(column);
            loadEndpoints();
        });
    }

    /**
     * Load endpoints data
     */
    function loadEndpoints() {
        const days = $('#nandrestapi-endpoints-period').val() || 7;
        const orderby = $('#nandrestapi-endpoints-orderby').val() || 'total_calls';
        const order = $('#nandrestapi-endpoints-order').val() || 'DESC';

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'nandrestapi_get_endpoints',
                nonce: nandrestapiAdmin.nonce,
                days: days,
                orderby: orderby,
                order: order
            },
            beforeSend: function () {
                $('#nandrestapi-endpoints-table tbody').html(
                    '<tr><td colspan="7" class="nandrestapi-loading">Loading...</td></tr>'
                );
            },
            success: function (response) {
                if (response.success) {
                    renderEndpointsTable(response.data.endpoints || []);
                }
            }
        });
    }

    /**
     * Render endpoints table
     */
    function renderEndpointsTable(endpoints) {
        const tbody = $('#nandrestapi-endpoints-table tbody');

        if (!endpoints.length) {
            tbody.html('<tr><td colspan="7" class="nandrestapi-loading">' + nandrestapiAdmin.strings.no_data + '</td></tr>');
            return;
        }

        let html = '';
        endpoints.forEach(function (endpoint) {
            const errorClass = parseFloat(endpoint.error_rate) > 5 ? 'nandrestapi-status-4xx' : '';
            html += '<tr>';
            html += '<td><code>' + escapeHtml(endpoint.endpoint) + '</code></td>';
            html += '<td>' + formatNumber(endpoint.total_calls) + '</td>';
            html += '<td>' + formatTime(endpoint.avg_time) + '</td>';
            html += '<td>' + formatBytes(endpoint.avg_memory) + '</td>';
            html += '<td>' + parseFloat(endpoint.avg_queries).toFixed(1) + '</td>';
            html += '<td><span class="nandrestapi-status ' + errorClass + '">' + parseFloat(endpoint.error_rate).toFixed(1) + '%</span></td>';
            html += '<td><a href="' + getLogsUrl(endpoint.endpoint) + '" class="button button-small">View Logs</a></td>';
            html += '</tr>';
        });

        tbody.html(html);
    }

    /**
     * Initialize logs tab
     */
    function initLogs() {
        loadLogs();

        $('#nandrestapi-logs-apply').on('click', function () {
            currentPage = 1;
            loadLogs();
        });

        $('#nandrestapi-logs-reset').on('click', function () {
            $('#nandrestapi-logs-endpoint').val('');
            $('#nandrestapi-logs-namespace').val('');
            $('#nandrestapi-logs-method').val('');
            $('#nandrestapi-logs-status').val('');
            $('#nandrestapi-logs-user').val('all');
            $('#nandrestapi-logs-date-from').val('');
            $('#nandrestapi-logs-date-to').val('');
            currentPage = 1;
            loadLogs();
        });

        // Pagination
        $('#nandrestapi-logs-prev').on('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadLogs();
            }
        });

        $('#nandrestapi-logs-next').on('click', function () {
            currentPage++;
            loadLogs();
        });

        // Sortable headers
        $('.nandrestapi-logs-table .nandrestapi-sortable').on('click', function () {
            const column = $(this).data('sort');
            if (currentOrderBy === column) {
                currentOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
            } else {
                currentOrderBy = column;
                currentOrder = 'DESC';
            }
            loadLogs();
        });

        // Modal close
        $('.nandrestapi-modal-close').on('click', function () {
            $('#nandrestapi-log-modal').hide();
        });

        // Close modal when clicking outside content
        $('#nandrestapi-log-modal').on('click', function (e) {
            if ($(e.target).is('#nandrestapi-log-modal')) {
                $(this).hide();
            }
        });
    }

    /**
     * Load logs data
     */
    function loadLogs() {
        const data = {
            action: 'nandrestapi_get_logs',
            nonce: nandrestapiAdmin.nonce,
            page: currentPage,
            per_page: 20,
            orderby: currentOrderBy,
            order: currentOrder,
            endpoint: $('#nandrestapi-logs-endpoint').val() || '',
            namespace: $('#nandrestapi-logs-namespace').val() || '',
            method: $('#nandrestapi-logs-method').val() || '',
            status_code: $('#nandrestapi-logs-status').val() || '',
            user_id: $('#nandrestapi-logs-user').val() || 'all',
            date_from: $('#nandrestapi-logs-date-from').val() || '',
            date_to: $('#nandrestapi-logs-date-to').val() || ''
        };

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#nandrestapi-logs-table tbody').html(
                    '<tr><td colspan="8" class="nandrestapi-loading">Loading...</td></tr>'
                );
            },
            success: function (response) {
                if (response.success) {
                    renderLogsTable(response.data.logs || []);
                    updatePagination(response.data.total || 0, response.data.pages || 1);
                }
            }
        });
    }

    /**
     * Render logs table
     */
    function renderLogsTable(logs) {
        const tbody = $('#nandrestapi-logs-table tbody');

        if (!logs.length) {
            tbody.html('<tr><td colspan="8" class="nandrestapi-loading">' + nandrestapiAdmin.strings.no_data + '</td></tr>');
            return;
        }

        let html = '';
        logs.forEach(function (log) {
            const statusClass = getStatusClass(log.status_code);
            const methodClass = 'nandrestapi-method-' + log.method.toLowerCase();
            const userName = log.user_id > 0 ? 'User #' + log.user_id : 'Anonymous';

            html += '<tr data-id="' + log.id + '">';
            html += '<td>' + formatDate(log.recorded_at) + '</td>';
            html += '<td><code>' + escapeHtml(truncate(log.endpoint, 40)) + '</code></td>';
            html += '<td><span class="nandrestapi-method ' + methodClass + '">' + log.method + '</span></td>';
            html += '<td><span class="nandrestapi-status ' + statusClass + '">' + log.status_code + '</span></td>';
            html += '<td>' + formatTime(log.response_time) + '</td>';
            html += '<td>' + formatBytes(log.memory_usage) + '</td>';
            html += '<td>' + log.query_count + '</td>';
            html += '<td>' + userName + '</td>';
            html += '</tr>';
        });

        tbody.html(html);

        // Click to view details
        tbody.find('tr').on('click', function () {
            const id = $(this).data('id');
            const log = logs.find(l => l.id == id);
            if (log) {
                showLogDetails(log);
            }
        });
    }

    /**
     * Show log details in modal
     */
    function showLogDetails(log) {
        let html = '<table class="nandrestapi-info-table">';
        html += '<tr><th>Endpoint</th><td><code>' + escapeHtml(log.endpoint) + '</code></td></tr>';
        html += '<tr><th>Full URL</th><td><code style="word-break: break-all;">' + escapeHtml(log.full_url) + '</code></td></tr>';
        html += '<tr><th>Method</th><td>' + log.method + '</td></tr>';
        html += '<tr><th>Status Code</th><td>' + log.status_code + '</td></tr>';
        html += '<tr><th>Response Time</th><td>' + formatTime(log.response_time) + ' (' + parseFloat(log.time_percent).toFixed(1) + '% of limit)</td></tr>';
        html += '<tr><th>Memory Usage</th><td>' + formatBytes(log.memory_usage) + ' (' + parseFloat(log.memory_percent).toFixed(1) + '% of limit)</td></tr>';
        html += '<tr><th>Peak Memory</th><td>' + formatBytes(log.memory_peak) + '</td></tr>';
        html += '<tr><th>DB Queries</th><td>' + log.query_count + ' (' + parseFloat(log.query_time * 1000).toFixed(0) + 'ms)</td></tr>';
        html += '<tr><th>Duplicate Queries</th><td>' + log.duplicate_queries + '</td></tr>';
        html += '<tr><th>HTTP Requests</th><td>' + log.http_requests_count + '</td></tr>';
        html += '<tr><th>Cache Hits/Misses</th><td>' + log.cache_hits + ' / ' + log.cache_misses + '</td></tr>';
        html += '<tr><th>Transient Updates</th><td>' + log.transient_updates + '</td></tr>';
        html += '<tr><th>PHP Errors</th><td>' + log.php_errors_count + '</td></tr>';
        html += '<tr><th>User</th><td>' + (log.user_id > 0 ? 'User #' + log.user_id : 'Anonymous') + '</td></tr>';
        if (log.ip_address) {
            html += '<tr><th>IP Address</th><td>' + escapeHtml(log.ip_address) + '</td></tr>';
        }
        html += '<tr><th>Recorded At</th><td>' + log.recorded_at + '</td></tr>';
        if (log.error_message) {
            html += '<tr><th>Error</th><td style="color: #d63638;">' + escapeHtml(log.error_message) + '</td></tr>';
        }
        html += '</table>';

        $('#nandrestapi-log-details').html(html);
        $('#nandrestapi-log-modal').show();
    }

    /**
     * Update pagination
     */
    function updatePagination(total, pages) {
        const start = (currentPage - 1) * 20 + 1;
        const end = Math.min(currentPage * 20, total);

        $('.nandrestapi-pagination-info').text('Showing ' + start + '-' + end + ' of ' + total + ' entries');
        $('.nandrestapi-pagination-current').text('Page ' + currentPage + ' of ' + pages);

        $('#nandrestapi-logs-prev').prop('disabled', currentPage <= 1);
        $('#nandrestapi-logs-next').prop('disabled', currentPage >= pages);
    }

    /**
     * Initialize settings tab
     */
    function initSettings() {
        // Settings form submission
        $('#nandrestapi-settings-form').on('submit', function (e) {
            e.preventDefault();
            saveSettings();
        });

        // Clear logs button
        $('#nandrestapi-clear-logs').on('click', function () {
            if (confirm(nandrestapiAdmin.strings.confirm_clear)) {
                clearLogs();
            }
        });

        // Run test requests button
        $('#nandrestapi-run-test').on('click', function () {
            runTestRequests();
        });
    }

    /**
     * Run test HTTP requests
     */
    function runTestRequests() {
        const button = $('#nandrestapi-run-test');
        const spinner = $('#nandrestapi-test-spinner');

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'nandrestapi_run_test_requests',
                nonce: nandrestapiAdmin.nonce
            },
            beforeSend: function () {
                button.prop('disabled', true);
                spinner.addClass('is-active');
            },
            success: function (response) {
                button.prop('disabled', false);
                spinner.removeClass('is-active');
                if (response.success) {
                    showNotice('success', response.data.message);
                } else {
                    showNotice('error', response.data.message || 'Test failed.');
                }
            },
            error: function () {
                button.prop('disabled', false);
                spinner.removeClass('is-active');
                showNotice('error', 'Test failed. Please try again.');
            }
        });
    }

    /**
     * Save settings
     */
    function saveSettings() {
        const form = $('#nandrestapi-settings-form');
        const spinner = form.find('.spinner');

        const data = {
            action: 'nandrestapi_save_settings',
            nonce: nandrestapiAdmin.nonce,
            enable_logging: form.find('#enable_logging').is(':checked') ? 1 : 0,
            data_retention_days: form.find('#data_retention_days').val(),
            log_ip_address: form.find('#log_ip_address').is(':checked') ? 1 : 0,
            enable_stack_traces: form.find('#enable_stack_traces').is(':checked') ? 1 : 0,
            excluded_endpoints: form.find('#excluded_endpoints').val()
        };

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                spinner.addClass('is-active');
            },
            success: function (response) {
                spinner.removeClass('is-active');
                if (response.success) {
                    showNotice('success', response.data.message);
                } else {
                    showNotice('error', response.data.message || nandrestapiAdmin.strings.error);
                }
            },
            error: function () {
                spinner.removeClass('is-active');
                showNotice('error', nandrestapiAdmin.strings.error);
            }
        });
    }

    /**
     * Clear all logs
     */
    function clearLogs() {
        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'nandrestapi_clear_logs',
                nonce: nandrestapiAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    // Reload if on logs or dashboard tab
                    if ($('.nandrestapi-logs').length) {
                        loadLogs();
                    } else if ($('.nandrestapi-dashboard').length) {
                        loadDashboardData();
                    }
                } else {
                    showNotice('error', response.data.message);
                }
            }
        });
    }

    /**
     * Initialize support tab
     */
    function initSupport() {
        $('#nandrestapi-contact-form').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const spinner = form.find('.spinner');

            $.ajax({
                url: nandrestapiAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'nandrestapi_send_contact',
                    nonce: nandrestapiAdmin.nonce,
                    name: form.find('#contact-name').val(),
                    email: form.find('#contact-email').val(),
                    subject: form.find('#contact-subject').val(),
                    message: form.find('#contact-message').val()
                },
                beforeSend: function () {
                    spinner.addClass('is-active');
                },
                success: function (response) {
                    spinner.removeClass('is-active');
                    if (response.success) {
                        showNotice('success', response.data.message);
                        form[0].reset();
                    } else {
                        showNotice('error', response.data.message);
                    }
                }
            });
        });
    }

    // ==========================================
    // Helper Functions
    // ==========================================

    function formatNumber(num) {
        return parseInt(num || 0).toLocaleString();
    }

    function formatTime(seconds) {
        seconds = parseFloat(seconds || 0);
        if (seconds >= 1) {
            return seconds.toFixed(2) + 's';
        }
        return Math.round(seconds * 1000) + 'ms';
    }

    function formatBytes(bytes) {
        bytes = parseInt(bytes || 0);
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        }
        return bytes + ' B';
    }

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00 00:00:00') {
            return '-';
        }
        // MySQL datetime format: YYYY-MM-DD HH:MM:SS
        // Replace space with T and add Z for UTC
        const isoDate = dateStr.replace(' ', 'T') + 'Z';
        const date = new Date(isoDate);
        if (isNaN(date.getTime())) {
            return dateStr; // Return original if parsing fails
        }
        return date.toLocaleString();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function truncate(str, len) {
        if (str.length <= len) return str;
        return str.substring(0, len) + '...';
    }

    function getStatusClass(code) {
        code = parseInt(code);
        if (code >= 200 && code < 300) return 'nandrestapi-status-2xx';
        if (code >= 300 && code < 400) return 'nandrestapi-status-3xx';
        if (code >= 400 && code < 500) return 'nandrestapi-status-4xx';
        if (code >= 500) return 'nandrestapi-status-5xx';
        return '';
    }

    function getLogsUrl(endpoint) {
        const baseUrl = window.location.href.split('?')[0];
        return baseUrl + '?page=hungry-rest-api-monitor&tab=logs&endpoint=' + encodeURIComponent(endpoint);
    }

    function showNotice(type, message) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.nandrestapi-admin-wrap h1').after(notice);
        setTimeout(function () {
            notice.fadeOut(function () {
                $(this).remove();
            });
        }, 5000);
    }

    function showError(message) {
        showNotice('error', message);
    }

    // ==========================================
    // HTTP Requests Tab
    // ==========================================
    let httpCurrentPage = 1;
    let httpOrderBy = 'recorded_at';
    let httpOrder = 'DESC';
    let httpRequestsData = [];

    function initHttpRequests() {
        loadHttpRequests();

        $('#nandrestapi-http-apply').on('click', function () {
            httpCurrentPage = 1;
            loadHttpRequests();
        });

        $('#nandrestapi-http-reset').on('click', function () {
            $('#nandrestapi-http-url').val('');
            $('#nandrestapi-http-method').val('');
            $('#nandrestapi-http-status').val('');
            $('#nandrestapi-http-date-from').val('');
            $('#nandrestapi-http-date-to').val('');
            httpCurrentPage = 1;
            loadHttpRequests();
        });

        // Pagination
        $('#nandrestapi-http-prev').on('click', function () {
            if (httpCurrentPage > 1) {
                httpCurrentPage--;
                loadHttpRequests();
            }
        });

        $('#nandrestapi-http-next').on('click', function () {
            httpCurrentPage++;
            loadHttpRequests();
        });

        // Sortable headers
        $('.nandrestapi-http-table .nandrestapi-sortable').on('click', function () {
            const column = $(this).data('sort');
            if (httpOrderBy === column) {
                httpOrder = httpOrder === 'DESC' ? 'ASC' : 'DESC';
            } else {
                httpOrderBy = column;
                httpOrder = 'DESC';
            }
            loadHttpRequests();
        });

        // Modal close
        $('#nandrestapi-http-modal .nandrestapi-modal-close').on('click', function () {
            $('#nandrestapi-http-modal').hide();
        });

        // Close modal when clicking outside content
        $('#nandrestapi-http-modal').on('click', function (e) {
            if ($(e.target).is('#nandrestapi-http-modal')) {
                $(this).hide();
            }
        });
    }

    function loadHttpRequests() {
        const data = {
            action: 'nandrestapi_get_http_requests',
            nonce: nandrestapiAdmin.nonce,
            page: httpCurrentPage,
            per_page: 20,
            orderby: httpOrderBy,
            order: httpOrder,
            url: $('#nandrestapi-http-url').val() || '',
            method: $('#nandrestapi-http-method').val() || '',
            status: $('#nandrestapi-http-status').val() || '',
            date_from: $('#nandrestapi-http-date-from').val() || '',
            date_to: $('#nandrestapi-http-date-to').val() || ''
        };

        $.ajax({
            url: nandrestapiAdmin.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#nandrestapi-http-table tbody').html(
                    '<tr><td colspan="8" class="nandrestapi-loading">Loading...</td></tr>'
                );
            },
            success: function (response) {
                if (response.success) {
                    httpRequestsData = response.data.requests || [];
                    renderHttpRequestsTable(httpRequestsData);
                    updateHttpPagination(response.data.total || 0, response.data.pages || 1);
                }
            }
        });
    }

    function renderHttpRequestsTable(requests) {
        const tbody = $('#nandrestapi-http-table tbody');

        if (!requests.length) {
            tbody.html('<tr><td colspan="8" class="nandrestapi-loading">' + nandrestapiAdmin.strings.no_data + '</td></tr>');
            return;
        }

        let html = '';
        requests.forEach(function (req) {
            const statusClass = getStatusClass(req.response_code);
            const methodClass = 'nandrestapi-method-' + req.request_method.toLowerCase();
            const caller = req.caller_function ? req.caller_function + '()' : (req.caller_file ? req.caller_file.split('/').pop() : '-');
            const parentApi = req.parent_endpoint ? req.parent_endpoint : '-';

            html += '<tr data-id="' + req.id + '">';
            html += '<td>' + formatDate(req.recorded_at) + '</td>';
            html += '<td><code title="' + escapeHtml(req.request_url) + '">' + escapeHtml(truncate(req.request_url, 50)) + '</code></td>';
            html += '<td><span class="nandrestapi-method ' + methodClass + '">' + req.request_method + '</span></td>';
            html += '<td><span class="nandrestapi-status ' + statusClass + '">' + (req.response_code || 'Failed') + '</span></td>';
            html += '<td>' + formatTime(req.response_time) + '</td>';
            html += '<td>' + formatBytes(req.response_body_size) + '</td>';
            html += '<td><code title="' + escapeHtml(req.caller_file || '') + '">' + escapeHtml(caller) + '</code></td>';
            html += '<td><code>' + escapeHtml(truncate(parentApi, 25)) + '</code></td>';
            html += '</tr>';
        });

        tbody.html(html);

        // Click to view details
        tbody.find('tr').on('click', function () {
            const id = $(this).data('id');
            const req = httpRequestsData.find(r => r.id == id);
            if (req) {
                showHttpRequestDetails(req);
            }
        });
    }

    function showHttpRequestDetails(req) {
        let html = '<div class="nandrestapi-http-detail-grid">';

        // Request Info
        html += '<div class="nandrestapi-detail-section">';
        html += '<h3>Request</h3>';
        html += '<table class="nandrestapi-info-table">';
        html += '<tr><th>URL</th><td style="word-break: break-all;"><code>' + escapeHtml(req.request_url) + '</code></td></tr>';
        html += '<tr><th>Method</th><td>' + req.request_method + '</td></tr>';
        html += '<tr><th>Timeout</th><td>' + (req.timeout_value || '0') + 's</td></tr>';
        html += '<tr><th>SSL Verify</th><td>' + (req.ssl_verify == 1 ? 'Yes' : 'No') + '</td></tr>';
        html += '<tr><th>Body Size</th><td>' + formatBytes(req.request_body_size) + '</td></tr>';
        if (req.request_headers) {
            html += '<tr><th>Headers</th><td><code style="font-size: 11px; word-break: break-all;">' + escapeHtml(req.request_headers) + '</code></td></tr>';
        }
        html += '</table>';
        html += '</div>';

        // Response Info
        html += '<div class="nandrestapi-detail-section">';
        html += '<h3>Response</h3>';
        html += '<table class="nandrestapi-info-table">';
        html += '<tr><th>Status Code</th><td><span class="nandrestapi-status ' + getStatusClass(req.response_code) + '">' + (req.response_code || 'Failed') + '</span></td></tr>';
        html += '<tr><th>Response Time</th><td>' + formatTime(req.response_time) + '</td></tr>';
        html += '<tr><th>Body Size</th><td>' + formatBytes(req.response_body_size) + '</td></tr>';
        if (req.is_error == 1 && req.error_message) {
            html += '<tr><th>Error</th><td style="color: #d63638;">' + escapeHtml(req.error_message) + '</td></tr>';
        }
        if (req.response_headers) {
            html += '<tr><th>Headers</th><td><code style="font-size: 11px; word-break: break-all;">' + escapeHtml(req.response_headers) + '</code></td></tr>';
        }
        html += '</table>';
        html += '</div>';

        // Debug Info
        html += '<div class="nandrestapi-detail-section nandrestapi-detail-full">';
        html += '<h3>Debug Information</h3>';
        html += '<table class="nandrestapi-info-table">';
        html += '<tr><th>Transport</th><td>' + (req.transport || 'Unknown') + '</td></tr>';
        if (req.caller_file) {
            html += '<tr><th>Caller File</th><td><code style="word-break: break-all;">' + escapeHtml(req.caller_file) + '</code></td></tr>';
        }
        if (req.caller_line) {
            html += '<tr><th>Caller Line</th><td>' + req.caller_line + '</td></tr>';
        }
        if (req.caller_function) {
            html += '<tr><th>Caller Function</th><td><code>' + escapeHtml(req.caller_function) + '()</code></td></tr>';
        }
        if (req.parent_endpoint) {
            html += '<tr><th>Parent API Call</th><td><code>' + escapeHtml(req.parent_endpoint) + '</code></td></tr>';
        }
        html += '<tr><th>Recorded At</th><td>' + req.recorded_at + '</td></tr>';
        html += '</table>';
        html += '</div>';

        html += '</div>';

        $('#nandrestapi-http-details').html(html);
        $('#nandrestapi-http-modal').show();
    }

    function updateHttpPagination(total, pages) {
        const start = total > 0 ? (httpCurrentPage - 1) * 20 + 1 : 0;
        const end = Math.min(httpCurrentPage * 20, total);

        $('#nandrestapi-http-pagination .nandrestapi-pagination-info').text('Showing ' + start + '-' + end + ' of ' + total + ' entries');
        $('#nandrestapi-http-pagination .nandrestapi-pagination-current').text('Page ' + httpCurrentPage + ' of ' + Math.max(1, pages));

        $('#nandrestapi-http-prev').prop('disabled', httpCurrentPage <= 1);
        $('#nandrestapi-http-next').prop('disabled', httpCurrentPage >= pages);
    }

    // ==========================================
    // Initialize on document ready
    // ==========================================
    $(document).ready(function () {
        // Initialize based on current tab
        if ($('.nandrestapi-dashboard').length) {
            initDashboard();
        }

        if ($('.nandrestapi-endpoints').length) {
            initEndpoints();
        }

        if ($('.nandrestapi-logs').length) {
            initLogs();
        }

        if ($('.nandrestapi-http-requests').length) {
            initHttpRequests();
        }

        if ($('.nandrestapi-settings').length) {
            initSettings();
        }

        if ($('.nandrestapi-support').length) {
            initSupport();
        }
    });

})(jQuery);
