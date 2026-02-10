<?php
/**
 * Logs Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

$nandrestapi_unique_endpoints = NANDRESTAPI_DB_Handler::get_unique_endpoints();
?>

<div class="nandrestapi-logs">
    <!-- Filters -->
    <div class="nandrestapi-filters nandrestapi-logs-filters">
        <div class="nandrestapi-filter-row">
            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Endpoint:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="text" id="nandrestapi-logs-endpoint" list="nandrestapi-endpoints-list"
                    placeholder="<?php esc_attr_e('Search endpoint...', 'hungry-rest-api-monitor'); ?>">
                <datalist id="nandrestapi-endpoints-list">
                    <?php foreach ($nandrestapi_unique_endpoints as $nandrestapi_endpoint): ?>
                        <option value="<?php echo esc_attr($nandrestapi_endpoint); ?>">
                        <?php endforeach; ?>
                </datalist>
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Namespace:', 'hungry-rest-api-monitor'); ?>
                </label>
                <select id="nandrestapi-logs-namespace">
                    <option value="">
                        <?php esc_html_e('All', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="wp/v2">wp/v2</option>
                    <option value="wc/v3">wc/v3 (WooCommerce)</option>
                </select>
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Method:', 'hungry-rest-api-monitor'); ?>
                </label>
                <select id="nandrestapi-logs-method">
                    <option value="">
                        <?php esc_html_e('All', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="PATCH">PATCH</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Status:', 'hungry-rest-api-monitor'); ?>
                </label>
                <select id="nandrestapi-logs-status">
                    <option value="">
                        <?php esc_html_e('All', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="200">200 OK</option>
                    <option value="201">201 Created</option>
                    <option value="400">400 Bad Request</option>
                    <option value="401">401 Unauthorized</option>
                    <option value="403">403 Forbidden</option>
                    <option value="404">404 Not Found</option>
                    <option value="500">500 Server Error</option>
                </select>
            </div>
        </div>

        <div class="nandrestapi-filter-row">
            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('User:', 'hungry-rest-api-monitor'); ?>
                </label>
                <select id="nandrestapi-logs-user">
                    <option value="all">
                        <?php esc_html_e('All', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="authenticated">
                        <?php esc_html_e('Authenticated', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="anonymous">
                        <?php esc_html_e('Anonymous', 'hungry-rest-api-monitor'); ?>
                    </option>
                </select>
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Date From:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="date" id="nandrestapi-logs-date-from">
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Date To:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="date" id="nandrestapi-logs-date-to">
            </div>

            <div class="nandrestapi-filter-actions">
                <button type="button" id="nandrestapi-logs-apply" class="button button-primary">
                    <?php esc_html_e('Apply Filters', 'hungry-rest-api-monitor'); ?>
                </button>
                <button type="button" id="nandrestapi-logs-reset" class="button">
                    <?php esc_html_e('Reset', 'hungry-rest-api-monitor'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <table class="widefat nandrestapi-table nandrestapi-logs-table" id="nandrestapi-logs-table">
        <thead>
            <tr>
                <th class="nandrestapi-sortable" data-sort="recorded_at">
                    <?php esc_html_e('Time', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-arrow-down-alt"></span>
                </th>
                <th>
                    <?php esc_html_e('Endpoint', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="method">
                    <?php esc_html_e('Method', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="status_code">
                    <?php esc_html_e('Status', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="response_time">
                    <?php esc_html_e('Time', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="memory_usage">
                    <?php esc_html_e('Memory', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="query_count">
                    <?php esc_html_e('Queries', 'hungry-rest-api-monitor'); ?>
                </th>
                <th>
                    <?php esc_html_e('User', 'hungry-rest-api-monitor'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="nandrestapi-loading">
                    <?php esc_html_e('Loading...', 'hungry-rest-api-monitor'); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="nandrestapi-pagination" id="nandrestapi-logs-pagination">
        <span class="nandrestapi-pagination-info">
            <?php esc_html_e('Showing 0 of 0 entries', 'hungry-rest-api-monitor'); ?>
        </span>
        <div class="nandrestapi-pagination-buttons">
            <button type="button" class="button" id="nandrestapi-logs-prev" disabled>
                <?php esc_html_e('« Previous', 'hungry-rest-api-monitor'); ?>
            </button>
            <span class="nandrestapi-pagination-current">
                <?php esc_html_e('Page 1', 'hungry-rest-api-monitor'); ?>
            </span>
            <button type="button" class="button" id="nandrestapi-logs-next">
                <?php esc_html_e('Next »', 'hungry-rest-api-monitor'); ?>
            </button>
        </div>
    </div>

    <!-- Log Detail Modal -->
    <div id="nandrestapi-log-modal" class="nandrestapi-modal" style="display: none;">
        <div class="nandrestapi-modal-content">
            <span class="nandrestapi-modal-close">&times;</span>
            <h2>
                <?php esc_html_e('Request Details', 'hungry-rest-api-monitor'); ?>
            </h2>
            <div id="nandrestapi-log-details"></div>
        </div>
    </div>
</div>