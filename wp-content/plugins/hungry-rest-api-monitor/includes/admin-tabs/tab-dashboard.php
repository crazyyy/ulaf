<?php
/**
 * Dashboard Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

$nandrestapi_summary = NANDRESTAPI_Performance_Stats::get_summary(7);
?>

<div class="nandrestapi-dashboard">
    <!-- Period Selector -->
    <div class="nandrestapi-period-selector">
        <label>
            <?php esc_html_e('Time Period:', 'hungry-rest-api-monitor'); ?>
        </label>
        <select id="nandrestapi-period">
            <option value="24h">
                <?php esc_html_e('Last 24 Hours', 'hungry-rest-api-monitor'); ?>
            </option>
            <option value="7d" selected>
                <?php esc_html_e('Last 7 Days', 'hungry-rest-api-monitor'); ?>
            </option>
            <option value="30d">
                <?php esc_html_e('Last 30 Days', 'hungry-rest-api-monitor'); ?>
            </option>
        </select>
        <button type="button" id="nandrestapi-refresh" class="button">
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e('Refresh', 'hungry-rest-api-monitor'); ?>
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="nandrestapi-summary-cards">
        <div class="nandrestapi-card nandrestapi-card-primary">
            <div class="nandrestapi-card-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="nandrestapi-card-content">
                <h3 id="stat-total-requests">
                    <?php echo esc_html(number_format($nandrestapi_summary['total_requests'])); ?>
                </h3>
                <p>
                    <?php esc_html_e('Total Requests', 'hungry-rest-api-monitor'); ?>
                </p>
            </div>
        </div>

        <div class="nandrestapi-card nandrestapi-card-success">
            <div class="nandrestapi-card-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="nandrestapi-card-content">
                <h3 id="stat-avg-time">
                    <?php echo esc_html(nandrestapi_format_time($nandrestapi_summary['avg_response_time'])); ?>
                </h3>
                <p>
                    <?php esc_html_e('Avg Response Time', 'hungry-rest-api-monitor'); ?>
                </p>
            </div>
        </div>

        <div class="nandrestapi-card nandrestapi-card-warning">
            <div class="nandrestapi-card-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="nandrestapi-card-content">
                <h3 id="stat-error-rate">
                    <?php echo esc_html(number_format($nandrestapi_summary['error_rate'], 1)); ?>%
                </h3>
                <p>
                    <?php esc_html_e('Error Rate', 'hungry-rest-api-monitor'); ?>
                </p>
            </div>
        </div>

        <div class="nandrestapi-card nandrestapi-card-info">
            <div class="nandrestapi-card-icon">
                <span class="dashicons dashicons-admin-plugins"></span>
            </div>
            <div class="nandrestapi-card-content">
                <h3 id="stat-endpoints">
                    <?php echo esc_html(number_format($nandrestapi_summary['unique_endpoints'])); ?>
                </h3>
                <p>
                    <?php esc_html_e('Active Endpoints', 'hungry-rest-api-monitor'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="nandrestapi-charts-row">
        <div class="nandrestapi-chart-container nandrestapi-chart-large">
            <h3>
                <?php esc_html_e('Requests Over Time', 'hungry-rest-api-monitor'); ?>
            </h3>
            <canvas id="nandrestapi-traffic-chart"></canvas>
        </div>
    </div>

    <div class="nandrestapi-charts-row nandrestapi-charts-half">
        <div class="nandrestapi-chart-container">
            <h3>
                <?php esc_html_e('HTTP Methods', 'hungry-rest-api-monitor'); ?>
            </h3>
            <canvas id="nandrestapi-method-chart"></canvas>
        </div>

        <div class="nandrestapi-chart-container">
            <h3>
                <?php esc_html_e('Status Codes', 'hungry-rest-api-monitor'); ?>
            </h3>
            <canvas id="nandrestapi-status-chart"></canvas>
        </div>
    </div>

    <!-- Top Endpoints Table -->
    <div class="nandrestapi-table-container">
        <h3>
            <?php esc_html_e('Top Endpoints', 'hungry-rest-api-monitor'); ?>
        </h3>
        <table class="widefat nandrestapi-table" id="nandrestapi-top-endpoints">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Endpoint', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Calls', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Avg Time', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Avg Memory', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Error Rate', 'hungry-rest-api-monitor'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="nandrestapi-loading">
                        <?php esc_html_e('Loading...', 'hungry-rest-api-monitor'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Database Info -->
    <div class="nandrestapi-info-bar">
        <span>
            <strong>
                <?php esc_html_e('Database Size:', 'hungry-rest-api-monitor'); ?>
            </strong>
            <span id="stat-db-size">
                <?php echo esc_html(nandrestapi_format_bytes(NANDRESTAPI_DB_Cleanup::get_table_size())); ?>
            </span>
        </span>
        <span>
            <strong>
                <?php esc_html_e('Total Logs:', 'hungry-rest-api-monitor'); ?>
            </strong>
            <span id="stat-log-count">
                <?php echo esc_html(number_format(NANDRESTAPI_DB_Cleanup::get_log_count())); ?>
            </span>
        </span>
    </div>
</div>