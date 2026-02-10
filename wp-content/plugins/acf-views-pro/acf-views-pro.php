<?php
/*
Plugin Name: ACF Views Pro
Plugin URI: https://wplake.org/acf-views/
Description: Display ACF fields and Posts using shortcodes.
Version: 1.9.2
Author: WPLake
Author URI: https://wplake.org/acf-views-pro/
Text Domain: acf-views
*/

namespace org\wplake\acf_views\pro;

use org\wplake\acf_views\AcfGroups\{AcfCardData, AcfViewData, Item};
use org\wplake\acf_views\AcfPro\AcfPro;
use org\wplake\acf_views\ActiveInstallations;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\DemoImport;
use org\wplake\acf_views\Options;
use org\wplake\acf_views\pro\AcfCard\{AcfCards, CardMarkup, QueryBuilder};
use org\wplake\acf_views\pro\AcfView\{AcfViews, ViewMarkup};
use org\wplake\acf_views\Settings;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Creator;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Loader as GroupsLoader;
use org\wplake\acf_views\vendors\Puc_v4_Factory;

defined('ABSPATH') || exit;

// wrapper to avoid variable name conflicts
$acfViewsPro = function () {
    // skip initialization if Basic already active
    if (class_exists(\org\wplake\acf_views\Plugin::class)) {
        return;
    }

    require_once __DIR__ . '/prefixed_vendors/vendor/scoper-autoload.php';

    $groupCreator = new Creator();
    $acfViewData = $groupCreator->create(AcfViewData::class);
    $acfCardData = $groupCreator->create(AcfCardData::class);
    $item = $groupCreator->create(Item::class);
    $options = new Options();
    $settings = new Settings($options);
    // load right here, as used everywhere
    $settings->load();
    $updateChecker = Puc_v4_Factory::buildUpdateChecker(
        'https://wplake.org/pro/?_update_action=get_metadata&_update_slug=acf-views-pro',
        __FILE__,
        'acf-views-pro'
    );

    $acf = new Acf();
    $html = new Html();
    $viewMarkup = new ViewMarkup($html);
    $queryBuilder = new QueryBuilder();
    $cardMarkup = new CardMarkup($queryBuilder);
    $cache = new Cache($acfViewData, $acfCardData);
    $plugin = new Plugin(
        $settings,
        $acf,
        $viewMarkup,
        $cardMarkup,
        $queryBuilder,
        $options,
        $cache
    );
    $license = new License($settings, $options, $plugin, $updateChecker);
    $acfViews = new AcfViews($html, $viewMarkup, $plugin, $acf, $cache);
    $acfCards = new AcfCards($html, $plugin, $queryBuilder, $cardMarkup, $cache);
    $demoImport = new DemoImport($acfViews, $settings, $item, $acfCards, $cache);
    $dashboard = new Dashboard($license, $plugin, $html, $acf, $options, $demoImport, $settings);
    $acfPro = new AcfPro($plugin);
    $upgrades = new Upgrades($plugin, $settings, $cache, $acfViews, $updateChecker);
    $mountPoints = new MountPoints($cache);
    $activeInstallations = new ActiveInstallations($plugin, $settings, $options);

    $acfGroupsLoader = new GroupsLoader();
    $acfGroupsLoader->signUpGroups('org\wplake\acf_views\AcfGroups', __DIR__ . '/src/AcfGroups');

    $plugin->setHooks();
    $acfViews->setHooks();
    $acf->setHooks();
    $dashboard->setHooks();
    $demoImport->setHooks();
    $license->setHooks();
    $acfCards->setHooks();
    $acfPro->setHooks();
    $upgrades->setHooks();
    $mountPoints->setHooks();
    $activeInstallations->setHooks();
};
$acfViewsPro();
