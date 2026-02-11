<?php if (!defined('ABSPATH')) {
    exit;
}
ob_start(); ?>
    <div class="js-err-log-widget-actions">
        <a class="js-err-log-settings-button" href="<?php
        if ($jserrlog_errors) {
            echo esc_url(admin_url('tools.php?page=js-error-logger'));
        } else {
            echo esc_url(admin_url('tools.php?page=js-error-logger&tab=settings'));
        } ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-labelledby="title"
                 role="button" xmlns:xlink="http://www.w3.org/1999/xlink">
                <title><?php esc_html_e('Settings','default'); ?></title>
                <path data-name="layer1"
                      d="M58.906 27a3.127 3.127 0 0 1-2.977-2.258 24.834 24.834 0 0 0-1.875-4.519 3.131 3.131 0 0 1 .505-3.71 3.1 3.1 0 0 0 0-4.376l-2.693-2.698a3.1 3.1 0 0 0-4.376 0 3.131 3.131 0 0 1-3.71.505 24.834 24.834 0 0 0-4.519-1.875A3.127 3.127 0 0 1 37 5.094 3.1 3.1 0 0 0 33.906 2h-3.812A3.1 3.1 0 0 0 27 5.094a3.127 3.127 0 0 1-2.258 2.977 24.834 24.834 0 0 0-4.519 1.875 3.131 3.131 0 0 1-3.71-.505 3.1 3.1 0 0 0-4.376 0l-2.695 2.7a3.1 3.1 0 0 0 0 4.376 3.131 3.131 0 0 1 .505 3.71 24.834 24.834 0 0 0-1.875 4.519A3.127 3.127 0 0 1 5.094 27 3.1 3.1 0 0 0 2 30.094v3.811A3.1 3.1 0 0 0 5.094 37a3.127 3.127 0 0 1 2.977 2.258 24.833 24.833 0 0 0 1.875 4.519 3.131 3.131 0 0 1-.505 3.71 3.1 3.1 0 0 0 0 4.376l2.7 2.7a3.1 3.1 0 0 0 4.376 0 3.131 3.131 0 0 1 3.71-.505 24.834 24.834 0 0 0 4.519 1.875A3.127 3.127 0 0 1 27 58.906 3.1 3.1 0 0 0 30.094 62h3.811A3.1 3.1 0 0 0 37 58.906a3.127 3.127 0 0 1 2.258-2.977 24.834 24.834 0 0 0 4.519-1.875 3.131 3.131 0 0 1 3.71.505 3.1 3.1 0 0 0 4.376 0l2.7-2.695a3.1 3.1 0 0 0 0-4.376 3.131 3.131 0 0 1-.505-3.71 24.833 24.833 0 0 0 1.875-4.519A3.127 3.127 0 0 1 58.906 37 3.1 3.1 0 0 0 62 33.906v-3.812A3.1 3.1 0 0 0 58.906 27z"
                      fill="#fff" stroke="#202020" stroke-linecap="round" stroke-miterlimit="10"
                      stroke-width="2" stroke-linejoin="round"></path>
                <circle data-name="layer2"
                        cx="32" cy="32" r="14" fill="#2196F3" stroke="#202020" stroke-linecap="round"
                        stroke-miterlimit="10" stroke-width="2" stroke-linejoin="round"></circle>
            </svg>
            <?php if ($jserrlog_errors) {
                esc_html_e('Settings & Full Log', 'js-error-logger');
            } else {
                esc_html_e('Settings','default');
            } ?>
        </a>
        <a class="js-err-log-refresh-log" href="#">
            <?php esc_html_e('Refresh Log','js-error-logger'); ?>
        </a>
    </div>
<div class="js-err-log-dialog-content" title="<?php esc_attr_e('Error Details', 'js-error-logger'); ?>">
    <table class="js-err-log-view">
        <tr>
            <td><?php esc_html_e('Date','js-error-logger');?></td>
            <td class="js-err-log-view-date"></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Error','js-error-logger');?></td>
            <td class="js-err-log-view-error"></td>
        </tr>
        <tr>
            <td><?php esc_html_e('URLs','js-error-logger');?></td>
            <td class="js-err-log-view-url">
                <strong><?php esc_html_e('Script', 'js-error-logger'); ?>:</strong><br>
                <span class="js-err-log-view-script-url"></span><br>
                <strong><?php esc_html_e('Page', 'js-error-logger'); ?>:</strong><br>
                <span class="js-err-log-view-page-url"></span>
            </td>
        </tr>
        <tr>
            <td><?php esc_html_e('Position','js-error-logger');?></td>
            <td class="js-err-log-view-position"></td>
        </tr>
        <tr>
            <td><?php esc_html_e('User Agent','js-error-logger');?></td>
            <td class="js-err-log-view-agent"></td>
        </tr>
    </table>
</div>
<?php $jserrlog_settingsButton = ob_get_clean();

if (!$jserrlog_errors) {
    echo '<div class="js-err-log-widget-no-error">' . esc_html__('No errors right now!', 'js-error-logger') . '</div>';
    echo wp_kses_post($jserrlog_settingsButton); ?>
    <div class="js-err-log-please-rate clearfix">
        <?php
        /* translators: URL. */
        printf(wp_kses(__('Please <a href="%s" target="_blank">rate the plugin ★★★★★</a> to help keep it up-to-date & maintained. Thank you!', 'js-error-logger'), ['a' => ['href' => [], 'target' => []]]), 'https://wordpress.org/support/plugin/js-error-logger/reviews/#new-post'); ?>
    </div>
    <?php  return;
}
?>
    <table class="js-err-log-table">
        <tr>
            <th><?php esc_html_e('Date', 'js-error-logger'); ?></th>
            <th><?php esc_html_e('Error', 'js-error-logger'); ?></th>
            <th><?php esc_html_e('Details', 'js-error-logger'); ?></th>
        </tr>
        <?php foreach ($jserrlog_errors as $jserrlog_error) {
            list($jserrlog_fullError, $jserrlog_errorText) = self::error_texts($jserrlog_error);
            $jserrlog_time = wp_date($jserrlog_format, $jserrlog_error['time']);
            $jserrlog_urls = htmlspecialchars($jserrlog_error['urls']);
            ?>
            <tr>
                <td><?php echo esc_html(self::timeago($jserrlog_error['time'])); ?></td>
                <td><?php echo esc_html($jserrlog_errorText); ?></td>
                <td>
                    <button class="js-err-log-details<?php if (wp_doing_ajax()) {
                        echo " active";
                    } ?>" data-time="<?php echo esc_attr($jserrlog_time); ?>" data-position="<?php echo (int)$jserrlog_error['line'] . ':' . (int)$jserrlog_error['col']; ?>"
                         data-urls="<?php echo esc_attr($jserrlog_urls); ?>" data-err="<?php echo esc_attr(wp_kses($jserrlog_fullError, ['br' => [],'strong'=>[]])); ?>"
                         data-agent="<?php echo esc_attr($jserrlog_error['agent']); ?>"><?php esc_html_e('View', 'js-error-logger'); ?></button>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php echo wp_kses_post($jserrlog_settingsButton); ?>
<div class="js-err-log-please-rate clearfix">
    <?php
    /* translators: URL. */
    printf(wp_kses(__('Please <a href="%s" target="_blank">rate the plugin ★★★★★</a> to help keep it up-to-date & maintained. Thank you!', 'js-error-logger'), ['a' => ['href' => [], 'target' => []]]), 'https://wordpress.org/support/plugin/js-error-logger/reviews/#new-post'); ?>
</div>
