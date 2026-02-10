<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro;

use org\wplake\acf_views\Options;
use org\wplake\acf_views\Plugin;
use org\wplake\acf_views\Settings;
use org\wplake\acf_views\vendors\Puc_v4p13_Plugin_UpdateChecker;

defined('ABSPATH') || exit;

class License
{
    private string $error;
    private bool $isProcessed;
    private Settings $settings;
    /**
     * @var Puc_v4p13_Plugin_UpdateChecker
     */
    private $updateChecker;
    private Options $options;
    private Plugin $plugin;
    private bool $isWarningAboutLicenseShowed;

    /**
     * @param Puc_v4p13_Plugin_UpdateChecker $updateChecker
     */
    public function __construct(Settings $settings, Options $options, Plugin $plugin, $updateChecker)
    {
        $this->settings = $settings;
        $this->error = '';
        $this->isProcessed = false;
        $this->options = $options;
        $this->plugin = $plugin;
        $this->updateChecker = $updateChecker;
        $this->isWarningAboutLicenseShowed = false;
    }

    protected function addError(string $error): void
    {
        $this->error .= $error;
    }

    protected function deactivateLicense(): void
    {
        $this->settings->setLicense('');
        $this->settings->setLicenseUsedDomains('');
        $this->settings->setLicenseUsedDevDomains('');

        $this->settings->save();
    }

    /**
     * automatically refresh the update info message at the plugins page (if there is)
     * so if the license just added, but an update was already available,
     * then the 'update now' link will appear without pressing check for updates. And visa versa
     */
    protected function refreshTheUpdateInfo(): void
    {
        $this->updateChecker->checkForUpdates();
    }

    protected function updateLicenseInformation(object $info): void
    {
        $expiration = $info->_expiration ?? '';
        $usedDomains = $info->_usedDomains ?? '';
        $usedDevDomains = $info->_usedDevDomains ?? '';

        $this->settings->setLicenseExpiration($expiration);
        $this->settings->setLicenseUsedDomains($usedDomains);
        $this->settings->setLicenseUsedDevDomains($usedDevDomains);

        $this->settings->save();
    }

    protected function setLicense(string $license): void
    {
        $this->isProcessed = true;

        $this->settings->setLicense($license);
        // updateLicenseInformation() method will be called automatically via the 'request_info_result' hook
        $this->updateChecker->requestInfo();

        if ($this->settings->getLicenseExpiration()) {
            return;
        }

        $this->deactivateLicense();

        $this->addError(
            __(
                "Your license key appears to be invalid or you've reached the maximum number of websites for this key.",
                'acf-views'
            )
        );
    }

    protected function getCurrentAdminUrl(): string
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $uri = preg_replace('|^.*/wp-admin/|i', '', $uri);

        if (!$uri) {
            return '';
        }

        return remove_query_arg(
            ['_wpnonce',],
            admin_url($uri)
        );
    }

    protected function getMessageAboutLicenseExpirationWithinMonth(bool $isWithoutName = false): string
    {
        $expiration = $this->settings->getLicenseExpiration('d-m-Y');
        $prologLink = Plugin::PRO_VERSION_URL . '?_prolong-license=' . $this->settings->getLicense();

        $intro = !$isWithoutName ?
            __('"ACF Views Pro" license expires on', 'acf-views') :
            __('Your license expires on', 'acf-views');

        $warning = __('Automatic security and compatibility updates will be unavailable.', 'acf-views');

        $renewLink = sprintf(
            '<a target="_blank" href="%s">%s</a>',
            $prologLink,
            __('Renew now', 'acf-views')
        );

        return $intro . ' ' . $expiration . '. ' . $warning . ' ' . $renewLink . ' ' . __('and save 20%', 'acf-views');
    }

    protected function getWarningAboutLicense(): string
    {
        if ($this->settings->isLicenseExpiresWithinMonth()) {
            return $this->getMessageAboutLicenseExpirationWithinMonth(true);
        }

        $overviewPage = $this->plugin->getAdminUrl(Dashboard::PAGE_PRO);

        $activateLink = sprintf(
            '<a href="%s">%s</a>',
            $overviewPage,
            __('Activate your Pro license', 'acf-views')
        );
        $message = __(
            "to receive automatic updates for security and compatibility. If you don't have a license key, please see",
            'acf-views'
        );
        $pricing = sprintf(
            '<a target="_blank" href="%s">%s</a>',
            Plugin::PRO_PRICING_URL,
            __('pricing', 'acf-views')
        );

        return $activateLink . ' ' . $message . ' ' . $pricing . ' ' . __('on our website.', 'acf-views');
    }

    public function setHooks(): void
    {
        $slug = $this->plugin->getSlug();

        // two separate actions for the warning, as the WP table supports (visually) only 1 extra row per plugin
        // so, we need either append to the existing update message, or print own, if there is no update
        add_action(
            sprintf('in_plugin_update_message-%s', $slug),
            [$this, 'maybePrintWarningAboutLicense']
        );
        // with the bigger priority, 10 is for the default message, we need after
        add_action("after_plugin_row_{$slug}", [$this, 'maybePrintWarningRowAboutLicense'], 11);
        add_action('wp_loaded', function () {
            if (!isset($_POST['_av-pro'])) {
                return;
            }

            check_admin_referer('_av-pro');

            if ($this->settings->getLicense() &&
                isset($_POST['_deactivate'])) {
                $this->deactivateLicense();
            } else {
                $license = sanitize_text_field($_POST['_license'] ?? '');
                $this->setLicense($license);
            }

            $this->refreshTheUpdateInfo();
        });
        add_action('admin_notices', [$this, 'showWarningAboutExpiringWithinMonth']);
        add_filter(
            sprintf('puc_request_info_result-%s', $this->plugin->getShortSlug()),
            [$this, 'refreshLicenseInfo',]
        );

        // show updates info even if there is no license
        $this->updateChecker->addQueryArgFilter([$this, 'addLicenseKeyToQueryArgs']);
    }

    public function showWarningAboutExpiringWithinMonth(): void
    {
        if (!$this->settings->isLicenseExpiresWithinMonth() ||
            !!$this->options->getTransient(Options::TRANSIENT_LICENSE_EXPIRATION_DISMISS)) {
            return;
        }

        $dismissKey = sprintf('_%s-expiration-dismiss', $this->plugin->getShortSlug());

        if (isset($_GET[$dismissKey])) {
            $this->options->setTransient(Options::TRANSIENT_LICENSE_EXPIRATION_DISMISS, '1', WEEK_IN_SECONDS);
            return;
        }

        $dismissUrl = add_query_arg([
            $dismissKey => 1,
        ], $this->getCurrentAdminUrl());

        printf(
            '<div class="notice notice-warning"><p>%s<a style="float:right;" href="%s">%s</a></p></div>',
            $this->getMessageAboutLicenseExpirationWithinMonth(),
            $dismissUrl,
            __('Dismiss for a week', 'acf-views')
        );
    }

    public function addLicenseKeyToQueryArgs(array $queryArgs): array
    {
        $queryArgs['_license-key'] = $this->settings->getLicense();
        $queryArgs['_version'] = $this->settings->getVersion();

        return $queryArgs;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function isHasError(): bool
    {
        return !!$this->error;
    }

    public function refreshLicenseInfo(object $info): object
    {
        $this->updateLicenseInformation($info);

        return $info;
    }

    public function maybePrintWarningRowAboutLicense(): void
    {
        if (($this->settings->isActiveLicense() && !$this->settings->isLicenseExpiresWithinMonth()) ||
            $this->isWarningAboutLicenseShowed) {
            return;
        }

        $wpListTable = _get_list_table('WP_Plugins_List_Table');
        $colspan = $wpListTable->get_column_count();

        echo '<style>
#the-list tr[data-plugin="acf-views-pro/acf-views-pro.php"] td,
#the-list tr[data-plugin="acf-views-pro/acf-views-pro.php"] th{
box-shadow: none!important;
}

.av-plugin-notice__td {
background-color: #f0f6fc;border-left: 4px solid #72aee6;
}
.av-plugin-notice__div{
padding: 6px 12px;
background-color: #fcf9e8!important;
border-left-color: #dba617;
color:#2c3338;
}</style>';
        echo '<tr class="plugin-update-tr av-plugin-notice">';
        echo '<td class="plugin-update colspanchange av-plugin-notice__td" colspan="' . $colspan . '">';
        echo '<div class="update-message inline notice notice-error notice-alt av-plugin-notice__div">';
        echo $this->getWarningAboutLicense();
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }

    public function maybePrintWarningAboutLicense(): void
    {
        if ($this->settings->isActiveLicense() &&
            !$this->settings->isLicenseExpiresWithinMonth()) {
            return;
        }

        $this->isWarningAboutLicenseShowed = true;

        echo "<br>" . $this->getWarningAboutLicense();
    }
}
