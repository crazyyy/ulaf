<?php if (!defined('ABSPATH')) exit;
$tab = sanitize_text_field(wp_unslash($_GET['tab']?? ''));
if (!in_array($tab, ['settings'])) {
    $tab = '';
}
?>
<div class="wrap">
    <h1><?php esc_html_e('JS Error Logger', 'js-error-logger'); ?></h1>
    <div class="js-err-log-tabs-wrapper">
        <nav class="nav-tab-wrapper js-err-log-settings-nav">
            <a href="<?php echo esc_url(admin_url('tools.php?page=js-error-logger')); ?>" id="js-err-log-switch-1"
               data-tab="1"
               class="nav-tab<?php if (!$tab) echo ' nav-tab-active'; ?>"><?php esc_html_e('Error Log', 'js-error-logger'); ?></a>
                <a href="<?php echo esc_url(admin_url('tools.php?page=js-error-logger&tab=settings')); ?>"
                   id="js-err-log-switch-2" data-tab="2"
                   class="nav-tab<?php if ($tab == 'settings') echo ' nav-tab-active'; ?>"><?php esc_html_e('Settings','default'); ?>
                </a>
        </nav>
        <div class="js-err-log-cache-warning" style="display:none"><?php esc_html_e('Some of the settings you changed may require you to clear your cache before being effective.', 'js-error-logger')?></div>
        <div id="js-err-log-tab-1" class="js-err-log-tab<?php if ($tab) echo ' hidden'; ?>">
            <?php echo wp_kses_post($this->_render_log()); ?>
        </div>
        <div id="js-err-log-tab-2" class="js-err-log-tab<?php if ($tab != 'settings') echo ' hidden'; ?>">
            <?php wp_kses_post($this->_render_settings()); ?>
        </div>
    </div>
        <div class="js-err-log-sidebar-wrapper">
            <div class="js-err-log-sidebar rate-box"><p>
                    <?php
                    /* translators: URL. */
                    printf(wp_kses(__('Please <a href="%s" target="_blank">rate the plugin ★★★★★</a> to help keep it up-to-date & maintained. Thank you!', 'js-error-logger'),['a'=>['href'=>[],'target'=>[]]]),'https://wordpress.org/support/plugin/js-error-logger/reviews/#new-post'); ?>
                </p></div>
            <div class="js-err-log-sidebar other-plugins-box"><p><?php esc_html_e('You may also find our other plugins useful:','js-error-logger')
                    ?></p>
                <ul>
                    <li><?php
                        /* translators: Plugin Name. */
                        printf(esc_html__('%s: it allows you to create a to-do list, and easily assign tasks to other users.', 'js-error-logger'), '<a href="https://wordpress.org/plugins/sortable-dashboard-to-do-list/" target="_blank" rel="noopener">
Sortable Dashboard To-Do List</a>');
                        ?></li>
                    <li><?php
                        /* translators: Plugin Name. */
                        printf(esc_html__('%s: it allows you to stop being nagged by plugin updates that you don\'t want to do, and works on a version-per-version basis.', 'js-error-logger'), '<a href="https://wordpress.org/plugins/ignore-single-update/" target="_blank" rel="noopener">
Ignore Or Disable Plugin Update</a>');
                        ?></li>
                </ul></div>
        </div>
</div>