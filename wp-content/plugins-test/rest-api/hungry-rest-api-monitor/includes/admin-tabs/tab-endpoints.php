<?php
/**
 * Endpoints Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nandrestapi-endpoints">
    <!-- Filters -->
    <div class="nandrestapi-filters">
        <div class="nandrestapi-filter-group">
            <label>
                <?php esc_html_e('Time Period:', 'hungry-rest-api-monitor'); ?>
            </label>
            <select id="nandrestapi-endpoints-period">
                <option value="1">
                    <?php esc_html_e('Last 24 Hours', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="7" selected>
                    <?php esc_html_e('Last 7 Days', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="30">
                    <?php esc_html_e('Last 30 Days', 'hungry-rest-api-monitor'); ?>
                </option>
            </select>
        </div>

        <div class="nandrestapi-filter-group">
            <label>
                <?php esc_html_e('Sort By:', 'hungry-rest-api-monitor'); ?>
            </label>
            <select id="nandrestapi-endpoints-orderby">
                <option value="total_calls">
                    <?php esc_html_e('Total Calls', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="avg_time">
                    <?php esc_html_e('Avg Response Time', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="avg_memory">
                    <?php esc_html_e('Avg Memory', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="avg_queries">
                    <?php esc_html_e('Avg Queries', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="error_rate">
                    <?php esc_html_e('Error Rate', 'hungry-rest-api-monitor'); ?>
                </option>
            </select>
        </div>

        <div class="nandrestapi-filter-group">
            <label>
                <?php esc_html_e('Order:', 'hungry-rest-api-monitor'); ?>
            </label>
            <select id="nandrestapi-endpoints-order">
                <option value="DESC">
                    <?php esc_html_e('Descending', 'hungry-rest-api-monitor'); ?>
                </option>
                <option value="ASC">
                    <?php esc_html_e('Ascending', 'hungry-rest-api-monitor'); ?>
                </option>
            </select>
        </div>

        <button type="button" id="nandrestapi-endpoints-refresh" class="button button-primary">
            <?php esc_html_e('Apply Filters', 'hungry-rest-api-monitor'); ?>
        </button>
    </div>

    <!-- Endpoints Table -->
    <table class="widefat nandrestapi-table nandrestapi-endpoints-table" id="nandrestapi-endpoints-table">
        <thead>
            <tr>
                <th class="nandrestapi-sortable" data-sort="endpoint">
                    <?php esc_html_e('Endpoint', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th class="nandrestapi-sortable" data-sort="total_calls">
                    <?php esc_html_e('Total Calls', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th class="nandrestapi-sortable" data-sort="avg_time">
                    <?php esc_html_e('Avg Time', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th class="nandrestapi-sortable" data-sort="avg_memory">
                    <?php esc_html_e('Avg Memory', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th class="nandrestapi-sortable" data-sort="avg_queries">
                    <?php esc_html_e('Avg Queries', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th class="nandrestapi-sortable" data-sort="error_rate">
                    <?php esc_html_e('Error Rate', 'hungry-rest-api-monitor'); ?>
                    <span class="dashicons dashicons-sort"></span>
                </th>
                <th>
                    <?php esc_html_e('Actions', 'hungry-rest-api-monitor'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" class="nandrestapi-loading">
                    <?php esc_html_e('Loading...', 'hungry-rest-api-monitor'); ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>