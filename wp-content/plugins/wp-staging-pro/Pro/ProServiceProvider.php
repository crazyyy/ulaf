<?php

namespace WPStaging\Pro;

use WPStaging\Framework\DI\Container;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Notices\Notices as NoticesBase;
use WPStaging\Pro\Backup\BackupServiceProvider;
use WPStaging\Pro\License\LicenseServiceProvider;
use WPStaging\Pro\Push\PushServiceProvider;
use WPStaging\Pro\Notices\Notices;
use WPStaging\Pro\Staging\StagingSiteServiceProvider;
use WPStaging\Pro\Template\TemplateServiceProvider;
use WPStaging\Pro\Auth\AuthServiceProvider;
use WPStaging\Pro\WpCli\WpCliServiceProvider;

class ProServiceProvider extends ServiceProvider
{
    /** @var Container $container */
    protected $container;

    protected function registerClasses()
    {
        $this->container->register(TemplateServiceProvider::class);
        $this->container->register(LicenseServiceProvider::class);
        $this->container->register(AuthServiceProvider::class);
        $this->container->register(StagingSiteServiceProvider::class);
        $this->container->register(PushServiceProvider::class);

        // Feature providers.
        $this->container->register(WpCliServiceProvider::class);
        $this->container->register(BackupServiceProvider::class);
    }

    protected function addHooks()
    {
        add_action(NoticesBase::PRO_NOTICES_ACTION, $this->container->callback(Notices::class, 'renderNotices'));
    }
}
