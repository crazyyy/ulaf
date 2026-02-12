<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro;

use org\wplake\acf_views\AcfView\AcfViews;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\Plugin;
use org\wplake\acf_views\Settings;
use org\wplake\acf_views\vendors\Puc_v4p13_Plugin_UpdateChecker;

defined('ABSPATH') || exit;

class Upgrades extends \org\wplake\acf_views\Upgrades
{
    /**
     * @var Puc_v4p13_Plugin_UpdateChecker
     */
    private $updateChecker;

    /**
     * @param Puc_v4p13_Plugin_UpdateChecker $updateChecker
     */
    public function __construct(
        Plugin $plugin,
        Settings $settings,
        Cache $cache,
        AcfViews $acfViews,
        $updateChecker
    ) {
        parent::__construct($plugin, $settings, $cache, $acfViews);

        $this->updateChecker = $updateChecker;
    }

    public function upgrade(): void
    {
        parent::upgrade();

        // remove cached update/download urls in the DB, as it might be changed
        $this->updateChecker->resetUpdateState();
    }
}