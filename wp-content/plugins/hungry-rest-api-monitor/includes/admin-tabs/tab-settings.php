<?php
/**
 * Settings Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

$nandrestapi_options = nandrestapi_get_options();
?>

<div class="nandrestapi-settings">
    <form id="nandrestapi-settings-form">
        <?php wp_nonce_field('nandrestapi_admin_nonce', 'nandrestapi_settings_nonce'); ?>

        <div class="nandrestapi-settings-section">
            <h2>
                <?php esc_html_e('General Settings', 'hungry-rest-api-monitor'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_logging">
                            <?php esc_html_e('Enable Logging', 'hungry-rest-api-monitor'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="nandrestapi-toggle">
                            <input type="checkbox" name="enable_logging" id="enable_logging" value="1" <?php checked($nandrestapi_options['enable_logging'], 1); ?>>
                            <span class="nandrestapi-toggle-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Enable or disable REST API request logging.', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="data_retention_days">
                            <?php esc_html_e('Data Retention', 'hungry-rest-api-monitor'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" name="data_retention_days" id="data_retention_days"
                            value="<?php echo esc_attr($nandrestapi_options['data_retention_days']); ?>" min="1"
                            max="365" class="small-text">
                        <span>
                            <?php esc_html_e('days', 'hungry-rest-api-monitor'); ?>
                        </span>
                        <p class="description">
                            <?php esc_html_e('Logs older than this will be automatically deleted.', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="nandrestapi-settings-section">
            <h2>
                <?php esc_html_e('Privacy Settings', 'hungry-rest-api-monitor'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="log_ip_address">
                            <?php esc_html_e('Log IP Addresses', 'hungry-rest-api-monitor'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="nandrestapi-toggle">
                            <input type="checkbox" name="log_ip_address" id="log_ip_address" value="1" <?php checked($nandrestapi_options['log_ip_address'], 1); ?>>
                            <span class="nandrestapi-toggle-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Store client IP addresses. Disable for GDPR compliance.', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="nandrestapi-settings-section">
            <h2>
                <?php esc_html_e('Advanced Settings', 'hungry-rest-api-monitor'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_stack_traces">
                            <?php esc_html_e('Enable Stack Traces', 'hungry-rest-api-monitor'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="nandrestapi-toggle">
                            <input type="checkbox" name="enable_stack_traces" id="enable_stack_traces" value="1" <?php checked($nandrestapi_options['enable_stack_traces'], 1); ?>>
                            <span class="nandrestapi-toggle-slider"></span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Record stack traces for slow queries (development only).', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="excluded_endpoints">
                            <?php esc_html_e('Excluded Endpoints', 'hungry-rest-api-monitor'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea name="excluded_endpoints" id="excluded_endpoints" rows="5"
                            class="large-text code"><?php echo esc_textarea($nandrestapi_options['excluded_endpoints']); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('One pattern per line. Use * for wildcards. Example: /oembed/*', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="nandrestapi-settings-section">
            <h2>
                <?php esc_html_e('Data Management', 'hungry-rest-api-monitor'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Test HTTP Tracking', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <button type="button" id="nandrestapi-run-test" class="button button-secondary">
                            <?php esc_html_e('Run Test Requests', 'hungry-rest-api-monitor'); ?>
                        </button>
                        <span class="spinner" id="nandrestapi-test-spinner"></span>
                        <p class="description">
                            <?php esc_html_e('Make sample HTTP requests to test the tracking functionality.', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Clear All Logs', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <button type="button" id="nandrestapi-clear-logs" class="button button-secondary">
                            <?php esc_html_e('Clear All Logs', 'hungry-rest-api-monitor'); ?>
                        </button>
                        <p class="description">
                            <?php esc_html_e('Permanently delete all logged data. This cannot be undone.', 'hungry-rest-api-monitor'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php esc_html_e('Save Settings', 'hungry-rest-api-monitor'); ?>
            </button>
            <span class="spinner"></span>
        </p>
    </form>
</div>