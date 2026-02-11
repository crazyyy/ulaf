<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Staging\Ajax\UserAccountSynchronizer;

/**
 * Used to register classes and hooks for the staging site.
 */
class StagingSiteServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->singleton(SettingsTabs::class);
        $this->container->make(UserAccountSynchronizer::class);
    }

    protected function addHooks()
    {
        add_action("wp_ajax_wpstg_sync_account", $this->container->callback(UserAccountSynchronizer::class, "ajaxSyncAccount"));

        if (!$this->container->make(SiteInfo::class)->isStagingSite()) {
            return;
        }
        if (apply_filters('wpstg.notices.disable.plugin-update-notice', false) === true) {
            add_filter('site_transient_update_plugins', $this->container->callback(PluginUpdates::class, 'disablePluginUpdateChecksOnStagingSite'), 10, 1);
        }

        add_filter('wpstg_main_settings_tabs', $this->container->callback(SettingsTabs::class, 'addMailSettingsTabOnStagingSite'), 10, 1);
        add_action("wp_ajax_wpstg_update_staging_mail_settings", $this->container->callback(SettingsTabs::class, 'ajaxUpdateStagingMailSettings'));
    }
}
