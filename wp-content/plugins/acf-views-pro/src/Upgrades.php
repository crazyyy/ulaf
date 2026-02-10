<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfCard\AcfCards;
use org\wplake\acf_views\AcfView\AcfViews;
use WP_Query;

defined('ABSPATH') || exit;

class Upgrades
{
    private Plugin $plugin;
    private Settings $settings;
    private Cache $cache;
    private AcfViews $acfViews;

    public function __construct(
        Plugin $plugin,
        Settings $settings,
        Cache $cache,
        AcfViews $acfViews
    ) {
        $this->plugin   = $plugin;
        $this->settings = $settings;
        $this->cache    = $cache;
        $this->acfViews = $acfViews;
    }

    protected function isVersionLower(string $version, string $targetVersion): bool
    {
        // empty means the very first run, no data is available, nothing to fix
        if (! $version) {
            return false;
        }

        $currentVersion = explode('.', $version);
        $targetVersion  = explode('.', $targetVersion);

        // versions are broken
        if (3 !== count($currentVersion) ||
            3 !== count($targetVersion)) {
            return false;
        }

        //// convert to int

        foreach ($currentVersion as &$part) {
            $part = (int)$part;
        }
        foreach ($targetVersion as &$part) {
            $part = (int)$part;
        }

        //// compare

        // major
        if ($currentVersion[0] > $targetVersion[0]) {
            return false;
        } elseif ($currentVersion[0] < $targetVersion[0]) {
            return true;
        }

        // minor
        if ($currentVersion[1] > $targetVersion[1]) {
            return false;
        } elseif ($currentVersion[1] < $targetVersion[1]) {
            return true;
        }

        // patch
        if ($currentVersion[2] >= $targetVersion[2]) {
            return false;
        }

        return true;
    }

    protected function moveViewAndCardMetaToPostContentJson(): void
    {
        $queryArgs = [
            'post_type'      => [AcfViews::NAME, AcfCards::NAME,],
            'post_status'    => ['publish', 'draft', 'trash',],
            'posts_per_page' => -1,
        ];
        $myPosts   = new WP_Query($queryArgs);
        $myPosts   = $myPosts->get_posts();

        global $wpdb;

        foreach ($myPosts as $myPost) {
            $postId = $myPost->ID;

            $data = AcfViews::NAME === $myPost->post_type ?
                $this->cache->getAcfViewData($postId) :
                $this->cache->getAcfCardData($postId);

            $data->load($myPost->ID);

            $data->saveToPostContent();

            $wpdb->delete($wpdb->prefix . 'postmeta', [
                'post_id' => $postId,
            ]);
        }
    }

    protected function moveOptionsToSettings(): void
    {
        $license           = (string)get_option(Options::PREFIX . 'license', '');
        $licenceExpiration = (string)get_option(Options::PREFIX . 'license_expiration', '');
        $demoImport        = (array)get_option(Options::PREFIX . 'demo_import', []);

        $this->settings->setLicense($license);
        $this->settings->setLicenseExpiration($licenceExpiration);
        $this->settings->setDemoImport($demoImport);

        $this->settings->save();

        ////

        delete_option(Options::PREFIX . 'license');
        delete_option(Options::PREFIX . 'license_expiration');
        delete_option(Options::PREFIX . 'demo_import');
    }

    // it was for 1.5.10, when versions weren't available
    protected function firstRun(): bool
    {
        // skip upgrading as hook won't be fired and data is not available
        if (! $this->plugin->isAcfPluginAvailable()) {
            return false;
        }

        add_action('acf/init', function () {
            $this->moveViewAndCardMetaToPostContentJson();
            $this->moveOptionsToSettings();
        });

        return true;
    }

    protected function fixMultipleSlashesInPostContentJson(): void
    {
        global $wpdb;

        // don't use 'get_post($id)->post_content' / 'wp_update_post()'
        // to avoid the kses issue https://core.trac.wordpress.org/ticket/38715

        $myPosts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->posts} WHERE post_type IN (%s,%s) AND post_content != ''",
                AcfViews::NAME,
                AcfCards::NAME
            )
        );

        foreach ($myPosts as $myPost) {
            $content = str_replace('\\\\\\', '\\', $myPost->post_content);

            $wpdb->update($wpdb->posts, ['post_content' => $content], ['ID' => $myPost->ID]);
        }
    }

    public function updateMarkupIdentifiers(): void
    {
        $queryArgs = [
            'post_type'      => AcfViews::NAME,
            'post_status'    => ['publish', 'draft', 'trash',],
            'posts_per_page' => -1,
        ];
        $query     = new WP_Query($queryArgs);
        $posts     = $query->posts;

        foreach ($posts as $post) {
            $acfViewData = $this->cache->getAcfViewData($post->ID);

            // replace identifiers for Views without Custom Markup
            if (! trim($acfViewData->customMarkup) &&
                $acfViewData->cssCode) {
                foreach ($acfViewData->items as $item) {
                    $oldClass = '.' . $item->field->id;
                    $newClass = '.acf-view__' . $item->field->id;

                    $acfViewData->cssCode = str_replace($oldClass . ' ', $newClass . ' ', $acfViewData->cssCode);
                    $acfViewData->cssCode = str_replace($oldClass . '{', $newClass . '{', $acfViewData->cssCode);
                    $acfViewData->cssCode = str_replace($oldClass . ',', $newClass . ',', $acfViewData->cssCode);

                    foreach ($item->repeaterFields as $repeaterField) {
                        $oldClass = '.' . $repeaterField->id;
                        $newClass = '.acf-view__' . $repeaterField->id;

                        $acfViewData->cssCode = str_replace($oldClass . ' ', $newClass . ' ', $acfViewData->cssCode);
                        $acfViewData->cssCode = str_replace($oldClass . '{', $newClass . '{', $acfViewData->cssCode);
                        $acfViewData->cssCode = str_replace($oldClass . ',', $newClass . ',', $acfViewData->cssCode);
                    }
                }
                // don't call the 'saveToPostContent()' method, as it'll be called in the 'performSaveActions()' method
            }

            // update markup field for all
            $this->acfViews->performSaveActions($post->ID);
        }
    }

    public function upgrade(): void
    {
        // all versions since 1.6.0 has a version
        // empty means the very first run, no data is available, nothing to fix
        $previousVersion = $this->settings->getVersion();

        if ('1.6.0' === $previousVersion) {
            $this->fixMultipleSlashesInPostContentJson();
        }

        if ($this->isVersionLower($previousVersion, '1.7.0')) {
            add_action('acf/init', [$this, 'updateMarkupIdentifiers']);
        }

        $this->settings->setVersion($this->plugin->getVersion());
        $this->settings->save();
    }

    public function setHooks(): void
    {
        // don't use 'upgrader_process_complete' hook, as user can update the plugin manually by FTP
        $dbVersion   = $this->settings->getVersion();
        $codeVersion = $this->plugin->getVersion();

        // run upgrade if version in the DB is different from the code version
        if ($dbVersion !== $codeVersion) {
            // only at this hook can be sure that other plugin's functions are available
            add_action('plugins_loaded', [$this, 'upgrade']);
        }
    }
}
