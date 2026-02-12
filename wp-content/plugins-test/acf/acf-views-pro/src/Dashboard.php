<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfCard\AcfCards;
use org\wplake\acf_views\AcfView\AcfViews;
use WP_Screen;

defined('ABSPATH') || exit;

class Dashboard
{
    const PAGE_OVERVIEW = 'overview';
    const PAGE_DEMO_IMPORT = 'demo-import';

    private Plugin $plugin;
    private Html $html;
    private Acf $acf;
    private Options $options;
    private DemoImport $demoImport;

    public function __construct(Plugin $plugin, Html $html, Acf $acf, Options $options, DemoImport $demoImport)
    {
        $this->plugin = $plugin;
        $this->html = $html;
        $this->acf = $acf;
        $this->options = $options;
        $this->demoImport = $demoImport;
    }

    protected function getProBanner(): array
    {
        return $this->html->getProBanner(Plugin::PRO_VERSION_URL, $this->plugin->getAssetsUrl('pro.png'));
    }

    protected function getVideoReview(): string
    {
        return 'https://www.youtube.com/embed/0Vv23bmYzzo';
    }

    protected function getPages(): array
    {
        return [
            [
                'isLeftBlock' => true,
                'url' => $this->plugin->getAdminUrl(),
                'label' => __('ACF Views', 'acf-views'),
                'isActive' => false,
            ],
            [
                'isLeftBlock' => true,
                'url' => $this->plugin->getAdminUrl('', AcfCards::NAME),
                'label' => __('ACF Cards', 'acf-views'),
                'isActive' => false,
            ],
            [
                'isLeftBlock' => true,
                'url' => $this->plugin->getAdminUrl(self::PAGE_OVERVIEW),
                'label' => __('Overview', 'acf-views'),
                'isActive' => false,
            ],
            [
                'isLeftBlock' => true,
                'url' => Plugin::PRO_VERSION_URL,
                'isBlank' => true,
                'label' => __('Get PRO', 'acf-views'),
                'isActive' => false,
                'icon' => '<i class="av-toolbar__external-icon dashicons dashicons-star-filled"></i>',
            ],
            [
                'isRightBlock' => true,
                'url' => $this->plugin->getAdminUrl(self::PAGE_DEMO_IMPORT),
                'label' => __('Demo Import', 'acf-views'),
                'isActive' => false,
            ],
            [
                'isRightBlock' => true,
                'url' => Plugin::DOCS_URL,
                'isBlank' => true,
                'label' => __('Docs', 'acf-views'),
                'isActive' => false,
                'icon' => '<i class="av-toolbar__external-icon dashicons dashicons-external"></i>',
            ],
            [
                'isRightBlock' => true,
                'url' => Plugin::SURVEY_URL,
                'isBlank' => true,
                'label' => __('Survey', 'acf-views'),
                'isActive' => false,
                'icon' => '<i class="av-toolbar__external-icon dashicons dashicons-external"></i>',
            ],
        ];
    }

    protected function getCurrentAdminUrl(): string
    {
        $uri = isset($_SERVER['REQUEST_URI']) ?
            esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) :
            '';
        $uri = preg_replace('|^.*/wp-admin/|i', '', $uri);

        if (!$uri) {
            return '';
        }

        return admin_url($uri);
    }

    protected function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function setHooks(): void
    {
        $pluginSlug = $this->plugin->getSlug();

        add_action('admin_menu', [$this, 'addPages']);

        add_action('current_screen', function (WP_Screen $screen) {
            if (!isset($screen->post_type) ||
                !in_array($screen->post_type, [AcfViews::NAME, AcfCards::NAME,])) {
                return;
            }
            add_action('in_admin_header', [$this, 'getHeader']);
        });

        add_filter("plugin_action_links_{$pluginSlug}", [$this, 'addUpgradeToProLink']);
        // Overview should be later than the Pro link
        add_filter("plugin_action_links_{$pluginSlug}", [$this, 'addOverviewLink']);

        add_action('admin_menu', [$this, 'removeImportSubmenuLink']);
    }

    public function addPages(): void
    {
        add_submenu_page(
            sprintf('edit.php?post_type=%s', AcfViews::NAME),
            __('Overview', 'acf-views'),
            __('Overview', 'acf-views'),
            'edit_posts',
            self::PAGE_OVERVIEW,
            [$this, 'getOverviewPage']
        );
        add_submenu_page(
            sprintf('edit.php?post_type=%s', AcfViews::NAME),
            __('Demo import', 'acf-views'),
            __('Demo import', 'acf-views'),
            'edit_posts',
            self::PAGE_DEMO_IMPORT,
            [$this, 'getImportPage']
        );
    }

    public function getHeader(): void
    {
        $tabs = $this->getPages();

        $currentUrl = $this->getCurrentAdminUrl();

        foreach ($tabs as &$tab) {
            if ($currentUrl !== $tab['url']) {
                continue;
            }

            $tab['isActive'] = true;
            break;
        }

        echo $this->html->dashboardHeader($this->plugin->getName(), $tabs);
    }

    public function getOverviewPage(): void
    {
        $createAcfViewLink = $this->plugin->getAdminUrl('', AcfViews::NAME, 'post-new.php');
        $createAcfCardLink = $this->plugin->getAdminUrl('', AcfCards::NAME, 'post-new.php');

        echo $this->html->dashboardOverview(
            $createAcfViewLink,
            $createAcfCardLink,
            $this->acf->getGroupedFieldTypes(),
            [],
            [],
            $this->plugin->getVersion(),
            $this->plugin->getAdminUrl(self::PAGE_DEMO_IMPORT),
            $this->getVideoReview(),
            $this->getProBanner()
        );
    }

    public function getImportPage(): void
    {
        $isWithDeleteButton = false;

        $formMessage = '';

        if ($this->demoImport->isProcessed()) {
            if (!$this->demoImport->isHasError()) {
                $message = $this->demoImport->isImportRequest() ?
                    __("Import was successful. You’re all set!", 'acf-views') :
                    __('All demo objects have been deleted.', 'acf-views');
                $formMessage .= sprintf('<p class="av-introduction__title">%s</p>', $message);
            } else {
                $message = __('Request is failed.', 'acf-views');
                $formMessage .= sprintf(
                    '<p class="av-introduction__title">%s</p><br><br>%s',
                    $message,
                    $this->demoImport->getError()
                );
            }
        } else {
            $this->demoImport->readIDs();
        }

        if ($this->demoImport->isHasData() &&
            !$this->demoImport->isHasError()) {
            $isWithDeleteButton = true;
            $formMessage .= sprintf(
                '<p class="av-introduction__title">%s</p>',
                __('Imported items', 'acf-views')
            );

            $formMessage .= sprintf(
                '<p><b>%s</b></p>',
                __("Display page's ACF fields on the same page", 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getSamsungLink(),
                __('"Samsung Galaxy A53" Page', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getNokiaLink(),
                __('"Nokia X20" Page', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getXiaomiLink(),
                __('"Xiaomi 12T" Page', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getAcfGroupLink(),
                __('"Phone" Field Group', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getPhoneAcfViewLink(),
                __('"Phone" ACF View', 'acf-views')
            );

            $formMessage .= sprintf(
                '<p><b>%s</b></p>',
                __('Display a specific post, page or CPT item with its fields', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getSamsungArticleLink(),
                __('"Article about Samsung" page', 'acf-views')
            );

            $formMessage .= sprintf(
                '<p><b>%s<br>%s</b></p>',
                __('Display specific posts, pages or CPT items and their fields by using filters', 'acf-views'),
                __('or by manually assigning items', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getPhonesAcfCardLink(),
                __('"Phones" ACF Card', 'acf-views')
            );
            $formMessage .= sprintf(
                '<a target="_blank" href="%s">%s</a><br><br>',
                $this->demoImport->getPhonesArticleLink(),
                __('"Most popular phones in 2022" page', 'acf-views')
            );
        }

        $formNonce = wp_create_nonce('_av-demo-import');
        echo $this->html->dashboardImport($isWithDeleteButton, $formNonce, $formMessage);
    }

    public function addOverviewLink(array $links): array
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            $this->plugin->getAdminUrl(self::PAGE_OVERVIEW),
            __('Overview', 'acf-views')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    public function addUpgradeToProLink(array $links): array
    {
        $settings_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            Plugin::PRO_VERSION_URL,
            __('Get Pro', 'acf-views')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    public function removeImportSubmenuLink(): void
    {
        $url = sprintf('edit.php?post_type=%s', AcfViews::NAME);

        global $submenu;

        if (!$submenu[$url]) {
            $submenu[$url] = [];
        }

        foreach ($submenu[$url] as $itemKey => $item) {
            if (4 !== count($item) ||
                $item[2] !== self::PAGE_DEMO_IMPORT) {
                continue;
            }

            unset($submenu[$url][$itemKey]);
            break;
        }
    }
}