<?php
/**
 * HTTP Requests Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nandrestapi-http-requests">
    <div class="nandrestapi-http-header">
        <h2>
            <?php esc_html_e('Outgoing HTTP Requests', 'hungry-rest-api-monitor'); ?>
        </h2>
        <p class="description">
            <?php esc_html_e('Track all external HTTP API calls made during REST API requests. Useful for debugging third-party integrations.', 'hungry-rest-api-monitor'); ?>
        </p>
    </div>

    <!-- Filters -->
    <div class="nandrestapi-filters nandrestapi-http-filters">
        <div class="nandrestapi-filter-row">
            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('URL:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="text" id="nandrestapi-http-url"
                    placeholder="<?php esc_attr_e('Search URL...', 'hungry-rest-api-monitor'); ?>">
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Method:', 'hungry-rest-api-monitor'); ?>
                </label>
                <select id="nandrestapi-http-method">
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
                <select id="nandrestapi-http-status">
                    <option value="">
                        <?php esc_html_e('All', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="success">
                        <?php esc_html_e('Success (2xx)', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="redirect">
                        <?php esc_html_e('Redirect (3xx)', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="client_error">
                        <?php esc_html_e('Client Error (4xx)', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="server_error">
                        <?php esc_html_e('Server Error (5xx)', 'hungry-rest-api-monitor'); ?>
                    </option>
                    <option value="failed">
                        <?php esc_html_e('Failed (0)', 'hungry-rest-api-monitor'); ?>
                    </option>
                </select>
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Date From:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="date" id="nandrestapi-http-date-from">
            </div>

            <div class="nandrestapi-filter-group">
                <label>
                    <?php esc_html_e('Date To:', 'hungry-rest-api-monitor'); ?>
                </label>
                <input type="date" id="nandrestapi-http-date-to">
            </div>

            <div class="nandrestapi-filter-actions">
                <button type="button" id="nandrestapi-http-apply" class="button button-primary">
                    <?php esc_html_e('Apply Filters', 'hungry-rest-api-monitor'); ?>
                </button>
                <button type="button" id="nandrestapi-http-reset" class="button">
                    <?php esc_html_e('Reset', 'hungry-rest-api-monitor'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- HTTP Requests Table -->
    <table class="widefat nandrestapi-table nandrestapi-http-table" id="nandrestapi-http-table">
        <thead>
            <tr>
                <th class="nandrestapi-sortable" data-sort="recorded_at">
                    <?php esc_html_e('Time', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-arrow-down-alt"></span>
                </th>
                <th>
                    <?php esc_html_e('URL', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="request_method">
                    <?php esc_html_e('Method', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="response_code">
                    <?php esc_html_e('Status', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="response_time">
                    <?php esc_html_e('Time', 'hungry-rest-api-monitor'); ?>
                </th>
                <th class="nandrestapi-sortable" data-sort="response_body_size">
                    <?php esc_html_e('Size', 'hungry-rest-api-monitor'); ?>
                </th>
                <th>
                    <?php esc_html_e('Caller', 'hungry-rest-api-monitor'); ?>
                </th>
                <th>
                    <?php esc_html_e('Parent API', 'hungry-rest-api-monitor'); ?>
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
    <div class="nandrestapi-pagination" id="nandrestapi-http-pagination">
        <span class="nandrestapi-pagination-info">
            <?php esc_html_e('Showing 0 of 0 entries', 'hungry-rest-api-monitor'); ?>
        </span>
        <div class="nandrestapi-pagination-buttons">
            <button type="button" class="button" id="nandrestapi-http-prev" disabled>
                <?php esc_html_e('« Previous', 'hungry-rest-api-monitor'); ?>
            </button>
            <span class="nandrestapi-pagination-current">
                <?php esc_html_e('Page 1', 'hungry-rest-api-monitor'); ?>
            </span>
            <button type="button" class="button" id="nandrestapi-http-next">
                <?php esc_html_e('Next »', 'hungry-rest-api-monitor'); ?>
            </button>
        </div>
    </div>

    <!-- HTTP Request Detail Modal -->
    <div id="nandrestapi-http-modal" class="nandrestapi-modal" style="display: none;">
        <div class="nandrestapi-modal-content nandrestapi-modal-wide">
            <span class="nandrestapi-modal-close">&times;</span>
            <h2>
                <?php esc_html_e('HTTP Request Details', 'hungry-rest-api-monitor'); ?>
            </h2>
            <div id="nandrestapi-http-details"></div>
        </div>
    </div>
</div>