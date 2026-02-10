<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

use UnexpectedValueException;
use WPStaging\Backup\Dto\Job\JobRestoreDataDto;

class DomainPathUpdater
{
    /** @var array */
    protected $sites;

    /** @var string */
    private $sourceSiteDomain;

    /** @var string */
    private $sourceSitePath;

    /** @var bool */
    protected $isSourceSubdomainInstall;

    /** @return string */
    public function getSourceSiteDomain()
    {
        return $this->sourceSiteDomain;
    }

    /** @return string */
    public function getSourceSitePath()
    {
        return $this->sourceSitePath;
    }

    /** @return bool */
    public function getIsSourceSubdomainInstall()
    {
        return $this->isSourceSubdomainInstall;
    }

    /**
     * @param string $sourceSiteDomain
     */
    public function setSourceSiteDomain($sourceSiteDomain)
    {
        $this->sourceSiteDomain = $sourceSiteDomain;
    }

    /**
     * @param string $sourceSitePath
     */
    public function setSourceSitePath($sourceSitePath)
    {
        $this->sourceSitePath = $sourceSitePath;
    }

    /**
     * @param bool $isSubdomainInstall
     */
    public function setSourceSubdomainInstall($isSubdomainInstall)
    {
        $this->isSourceSubdomainInstall = $isSubdomainInstall;
    }

    /**
     * @param array $sites
     */
    public function setSourceSites($sites)
    {
        $this->sites = $sites;
    }

    /**
     * Get Sites with adjusted new urls
     *
     * @param string $baseDomain
     * @param string $basePath
     * @param string $homeURL
     * @param bool   $isSubdomainInstall
     *
     * @return array array of site info
     */
    public function getSitesWithNewURLs($baseDomain, $basePath, $homeURL, $isSubdomainInstall)
    {
        $adjustedSites = [];
        foreach ($this->sites as $site) {
            $adjustedSites[] = $this->adjustSiteDomainPath($site, $baseDomain, $basePath, $homeURL, $isSubdomainInstall);
        }

        return apply_filters('wpstg.backup.restore.multisites.subsites', $adjustedSites, $baseDomain, $basePath, $homeURL, $isSubdomainInstall);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function readMetaData(JobRestoreDataDto $jobDataDto)
    {
        $this->isSourceSubdomainInstall = $jobDataDto->getBackupMetadata()->getSubdomainInstall();

        $sourceSiteURL = $jobDataDto->getBackupMetadata()->getSiteUrl();
        $sourceSiteURLWithoutWWW = str_ireplace('//www.', '//', $sourceSiteURL);
        $parsedURL = parse_url($sourceSiteURLWithoutWWW);

        if (!is_array($parsedURL) || !array_key_exists('host', $parsedURL)) {
            throw new UnexpectedValueException("Bad URL format, cannot proceed.");
        }

        $this->sourceSiteDomain = $parsedURL['host'];
        $this->sourceSitePath = '/';
        if (array_key_exists('path', $parsedURL)) {
            $this->sourceSitePath = $parsedURL['path'];
        }

        $this->sites = $jobDataDto->getBackupMetadata()->getSites();
    }

    private function adjustSiteDomainPath($site, $baseDomain, $basePath, $homeURL, $isSubdomainInstall)
    {
        $subsiteDomain = str_replace($this->sourceSiteDomain, $baseDomain, $site['domain']);
        $subsitePath = str_replace(trailingslashit($this->sourceSitePath), $basePath, $site['path']);
        $subsiteUrlWithoutScheme = untrailingslashit($subsiteDomain . $subsitePath);
        $mainsiteUrlWithoutScheme = untrailingslashit($baseDomain . $basePath);

        $wwwPrefix = '';
        if (strpos($homeURL, '//www.') !== false) {
            $wwwPrefix = 'www.';
        }

        if ($this->isSourceSubdomainInstall === $isSubdomainInstall && $subsiteUrlWithoutScheme === $mainsiteUrlWithoutScheme) {
            $site['new_url'] = parse_url($homeURL, PHP_URL_SCHEME) . '://' . $wwwPrefix . $subsiteUrlWithoutScheme;
            $site['new_domain'] = rtrim($subsiteDomain, '/');
            $site['new_path'] = $subsitePath;
            return $site;
        }

        $subsiteDomain = $baseDomain;
        $subsitePath = $basePath;

        // Check whether domain based mapping
        if (strpos($subsiteUrlWithoutScheme, $mainsiteUrlWithoutScheme) === false) {
            return $this->mapSubsiteFromDomain($site, $homeURL, $wwwPrefix, $baseDomain, $basePath, $mainsiteUrlWithoutScheme, $subsiteUrlWithoutScheme, $isSubdomainInstall);
        }

        $subsiteName = str_replace($mainsiteUrlWithoutScheme, '', $subsiteUrlWithoutScheme);
        $subsiteName = rtrim($subsiteName, '.');
        $subsiteName = trim($subsiteName, '/');
        if ($wwwPrefix === '' && (strpos($subsiteDomain, 'www.') === 0)) {
            $subsiteDomain = substr($subsiteDomain, 4);
        }

        if ($isSubdomainInstall && ($subsiteName !== '')) {
            $subsiteDomain = $subsiteName . '.' . $subsiteDomain;
        }

        if (!$isSubdomainInstall && ($subsiteName !== '')) {
            $subsiteName = strpos($subsiteUrlWithoutScheme, 'www.') === 0 ? substr($subsiteName, 4) : $subsiteName;
            $subsiteName = empty($subsiteName) ? '' : trailingslashit($subsiteName);
            $subsiteName = ltrim($subsiteName, '/');
            $subsitePath = $subsitePath . $subsiteName;
        }

        $subsiteUrlWithoutScheme = untrailingslashit(rtrim($subsiteDomain, '/') . $subsitePath);
        $site['new_url'] = parse_url($homeURL, PHP_URL_SCHEME) . '://' . $wwwPrefix . $subsiteUrlWithoutScheme;
        $site['new_domain'] = rtrim($subsiteDomain, '/');
        $site['new_path'] = $subsitePath;
        return $site;
    }

    protected function mapSubsiteFromDomain($site, $homeURL, $wwwPrefix, $baseDomain, $basePath, $mainsiteUrlWithoutScheme, $subsiteUrlWithoutScheme, $isSubdomainInstall)
    {
        if (!$isSubdomainInstall) {
            $site['new_url'] = parse_url($homeURL, PHP_URL_SCHEME) . '://' . $wwwPrefix . trailingslashit($mainsiteUrlWithoutScheme) . $site['domain'];
            $site['new_domain'] = rtrim($baseDomain, '/');
            $site['new_path'] = $basePath . trailingslashit($site['domain']);
            return $site;
        }

        $site['new_url'] = parse_url($homeURL, PHP_URL_SCHEME) . '://' . $wwwPrefix . $site['domain'] . '.' . $mainsiteUrlWithoutScheme;
        $site['new_domain'] = $site['domain'] . '.' . rtrim($baseDomain, '/');
        $site['new_path'] = $basePath;
        return $site;
    }
}
