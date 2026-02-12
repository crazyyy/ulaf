<?php if (!defined('ABSPATH')) {
    exit;
}
$jserrlog_errors = $this->_logger->get_log_content(0, $this->_ignored_data);
$jserrlog_format = $this->_date_time_format;
$jserrlog_log = $this->_logger->maintain_log();
?>
<div class="js-err-log-refreshable"></div>
<div class="js-err-log-log">
    <section>
        <div class="js-err-log-full-actions">
            <?php if ($jserrlog_errors) { ?>
                <a href="#" class="js-err-log-action" data-action="purge"><?php esc_html_e('Purge Log', 'js-error-logger'); ?></a>
            <?php } ?>
            <a href="#" class="js-err-log-action" data-action="refresh"><?php esc_html_e('Refresh Log', 'js-error-logger'); ?></a>
        </div>
        <?php if (!$jserrlog_errors) {
            echo '<div class="js-err-log-log-no-error">';
            esc_html_e('Congratulations! There are currently no JS errors to display.', 'js-error-logger');
            echo '</div>';
        } else { ?>
            <table class="js-err-log-full-log">
                <tr>
                    <th class="js-err-log-full-date"><?php esc_html_e('Date', 'js-error-logger'); ?></th>
                    <th class="js-err-log-full-error"><?php esc_html_e('Error', 'js-error-logger'); ?></th>
                    <th class="js-err-log-full-url"><?php esc_html_e('URLs', 'js-error-logger'); ?></th>
                    <th class="js-err-log-full-position"><?php esc_html_e('Position', 'js-error-logger'); ?></th>
                    <th class="js-err-log-full-agent"><?php esc_html_e('User Agent', 'js-error-logger'); ?></th>
                </tr>
                <?php foreach ($jserrlog_errors as $jserrlog_error) {
                    $jserrlog_userAgent = $jserrlog_error['agent'] ?: 'Unknown';
                    list ($jserrlog_fullError) = self::error_texts($jserrlog_error, true);
                    $jserrlog_time = wp_date($jserrlog_format, $jserrlog_error['time']);
                    $jserrlog_urls = json_decode($jserrlog_error['urls']);
                    ?>
                    <tr>
                        <td class="js-err-log-full-date"><?php echo esc_html($jserrlog_time); ?></td>
                        <td class="js-err-log-full-error"><?php echo wp_kses($jserrlog_fullError, ['br' => [], 'strong' => [], 'a' => ['href' => [], 'target' => []]]); ?></td>
                        <td class="js-err-log-full-url">
                            <strong><?php esc_html_e('Script', 'js-error-logger'); ?>:</strong><br>
                            <?php if ($jserrlog_urls[0] == 'Inline script') {
                                esc_html_e('Inline script', 'js-error-logger');
                            } else { ?>
                                <a href="<?php echo esc_url($jserrlog_urls[0]); ?>" target="_blank"><?php echo esc_url($jserrlog_urls[0]); ?></a>
                            <?php } ?>
                            <br><br>
                            <strong><?php esc_html_e('Page', 'js-error-logger'); ?>:</strong><br>
                            <a href="<?php echo esc_url($jserrlog_urls[1]); ?>" target="_blank"><?php echo esc_url($jserrlog_urls[1]); ?></a>
                        </td>
                        <td class="js-err-log-full-position"><?php echo (int)$jserrlog_error['line'] . ':' . (int)($jserrlog_error['col']); ?></td>
                        <td class="js-err-log-full-agent"><?php echo esc_html($jserrlog_userAgent); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </section>
</div>