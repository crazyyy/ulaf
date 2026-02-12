<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfCard\AcfCards;
use org\wplake\acf_views\AcfView\AcfViews;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * Custom implementation of a growth counter (native was removed) https://meta.trac.wordpress.org/ticket/6511
 * IT DOESN'T SEND ANY PRIVATE DATA, only a DOMAIN. And the domain is only used to avoid multiple counting from one website
 */
class ActiveInstallations
{
    const HOOK = AcfViews::NAME . '_refresh';
    const DELAY_MIN_HR = 12;
    const DELAY_MAX_HRS = 48;
    const REQUEST_URL = 'https://wplake.org/wp-admin/admin-ajax.php';

    private Plugin $plugin;
    private Settings $settings;
    private Options $options;

    public function __construct(Plugin $plugin, Settings $settings, Options $options)
    {
        $this->plugin = $plugin;
        $this->settings = $settings;
        $this->options = $options;
    }

    protected function getCountOfPosts(string $postType): int
    {
        $queryArgs = [
            'fields' => 'ids',
            'post_type' => $postType,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query($queryArgs);

        return $query->found_posts;
    }

    protected function sendActiveInstallationRequest(bool $isActive = true): void
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::REQUEST_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        // IT DOESN'T SEND ANY PRIVATE DATA, only a DOMAIN. And the domain is only used to avoid multiple counting from one website
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'action' => 'active_installations',
            '_domain' => parse_url(get_site_url())['host'] ?? '',
            '_version' => $this->plugin->getVersion(),
            '_isPro' => $this->plugin->isProVersion(),
            '_license' => $this->settings->getLicense(),
            '_isActive' => $isActive,
            '_viewsCount' => $this->getCountOfPosts(AcfViews::NAME),
            '_cardsCount' => $this->getCountOfPosts(AcfCards::NAME),
            // 'is_plugin_active()' is available only later
            '_isAcfPro' => class_exists('acf_pro'),
            '_language' => get_bloginfo('language'),
        ]);
        // avoid any output
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_exec($ch);
        curl_close($ch);
    }

    protected function scheduleNext(): void
    {
        // nextCheckingTime in seconds. Randomly to avoid DDOS
        $nextCheckingTime = time() + rand(self::DELAY_MIN_HR * 3600, self::DELAY_MAX_HRS * 3600);

        wp_schedule_single_event($nextCheckingTime, self::HOOK);
    }

    protected function unschedule(): void
    {
        $checkingTime = wp_next_scheduled(self::HOOK);
        if (false === $checkingTime) {
            return;
        }

        wp_unschedule_event($checkingTime, self::HOOK);
    }

    // WP Cron is unreliable. Execute also within the dashboard (in case the time has come)
    public function rescheduleOutdated(): void
    {
        $checkingTime = wp_next_scheduled(self::HOOK);

        if ($checkingTime > time()) {
            return;
        }

        // firstly, unschedule the outdated event
        wp_unschedule_event($checkingTime, self::HOOK);
        // then send and schedule the next
        $this->sendAndScheduleNext();
    }

    public function sendAndScheduleNext(): void
    {
        $this->sendActiveInstallationRequest();
        $this->scheduleNext();
    }

    public function init(): void
    {
        $checkingTime = wp_next_scheduled(self::HOOK);

        if (!$checkingTime) {
            $this->scheduleNext();
            return;
        }

        // WP Cron is unreliable. Execute also within the dashboard (in case the time has come)
        add_action('admin_init', [$this, 'rescheduleOutdated']);
    }

    public function setHooks(): void
    {
        add_action('init', [$this, 'init']);
        // CRON job
        add_action(self::HOOK, [$this, 'sendAndScheduleNext']);

        register_activation_hook($this->plugin->getSlug(), function () {
            $this->sendActiveInstallationRequest();
        });
        register_deactivation_hook($this->plugin->getSlug(), function () {
            $this->sendActiveInstallationRequest(false);
            $this->unschedule();
        });

        // alternative way to send the request, in case of usage of the 'another instance was deactivated' feature
        // as only old one was loaded that time, and new one skipped code execution (see the main plugin file)
        $isActivatedAfterAnotherDeactivation = (int)$this->options->getTransient(
            Options::TRANSIENT_DEACTIVATED_OTHER_INSTANCES
        );
        if ($isActivatedAfterAnotherDeactivation) {
            $this->sendActiveInstallationRequest();
        }
    }
}