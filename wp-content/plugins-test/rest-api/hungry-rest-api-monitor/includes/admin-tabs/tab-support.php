<?php
/**
 * Support Tab.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nandrestapi-support">
    <div class="nandrestapi-support-grid">
        <!-- Plugin Info -->
        <div class="nandrestapi-support-card">
            <h2>
                <?php esc_html_e('Plugin Information', 'hungry-rest-api-monitor'); ?>
            </h2>
            <table class="nandrestapi-info-table">
                <tr>
                    <th>
                        <?php esc_html_e('Version', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(NANDRESTAPI_VERSION); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('PHP Version', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(PHP_VERSION); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('WordPress Version', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(get_bloginfo('version')); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Memory Limit', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(ini_get('memory_limit')); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Max Execution Time', 'hungry-rest-api-monitor'); ?>
                    </th>
                    <td>
                        <?php echo esc_html(ini_get('max_execution_time')); ?>s
                    </td>
                </tr>
            </table>
        </div>

        <!-- Contact Form -->
        <div class="nandrestapi-support-card">
            <h2>
                <?php esc_html_e('Contact Support', 'hungry-rest-api-monitor'); ?>
            </h2>
            <form id="nandrestapi-contact-form">
                <p>
                    <label for="contact-name">
                        <?php esc_html_e('Name', 'hungry-rest-api-monitor'); ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="contact-name" name="name" required>
                </p>
                <p>
                    <label for="contact-email">
                        <?php esc_html_e('Email', 'hungry-rest-api-monitor'); ?> <span class="required">*</span>
                    </label>
                    <input type="email" id="contact-email" name="email"
                        value="<?php echo esc_attr(get_option('admin_email')); ?>" required>
                </p>
                <p>
                    <label for="contact-subject">
                        <?php esc_html_e('Subject', 'hungry-rest-api-monitor'); ?>
                    </label>
                    <input type="text" id="contact-subject" name="subject">
                </p>
                <p>
                    <label for="contact-message">
                        <?php esc_html_e('Message', 'hungry-rest-api-monitor'); ?> <span class="required">*</span>
                    </label>
                    <textarea id="contact-message" name="message" rows="5" required></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Send Message', 'hungry-rest-api-monitor'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>
            </form>
        </div>
    </div>

    <!-- Links -->
    <div class="nandrestapi-support-links">
        <a href="https://wordpress.org/support/plugin/hungry-rest-api-monitor/" target="_blank" class="button">
            <span class="dashicons dashicons-sos"></span>
            <?php esc_html_e('Support Forum', 'hungry-rest-api-monitor'); ?>
        </a>
        <a href="https://wordpress.org/plugins/hungry-rest-api-monitor/#reviews" target="_blank" class="button">
            <span class="dashicons dashicons-star-filled"></span>
            <?php esc_html_e('Leave a Review', 'hungry-rest-api-monitor'); ?>
        </a>
        <a href="https://nandann.com/contact" target="_blank" class="button">
            <span class="dashicons dashicons-admin-site"></span>
            <?php esc_html_e('Visit Website', 'hungry-rest-api-monitor'); ?>
        </a>
    </div>
</div>