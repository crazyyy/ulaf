<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfView\AcfViews;

defined('ABSPATH') || exit;

class Options
{
    const PREFIX = AcfViews::NAME . '_';
    const OPTION_SETTINGS = self::PREFIX . 'settings';
    const TRANSIENT_DEACTIVATED_OTHER_INSTANCES = self::PREFIX . 'deactivated_other_instances';
    const TRANSIENT_LICENSE_EXPIRATION_DISMISS = self::PREFIX . 'license_expiration_dismiss';

    public function getOption(string $name)
    {
        return get_option($name, '');
    }

    public function getTransient(string $name)
    {
        return get_transient($name);
    }

    // autoload = true, to avoid real requests to the DB, as settings are common for all
    public function updateOption(string $name, $value, bool $isAutoload = true): void
    {
        update_option($name, $value, $isAutoload);
    }

    public function setTransient(string $name, $value, int $expirationInSeconds): void
    {
        set_transient($name, $value, $expirationInSeconds);
    }

    public function deleteOption(string $name): void
    {
        delete_option($name);
    }

    public function deleteTransient(string $name): void
    {
        delete_transient($name);
    }
}