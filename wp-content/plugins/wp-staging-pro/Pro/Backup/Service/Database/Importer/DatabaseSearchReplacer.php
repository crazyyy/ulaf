<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

use WPStaging\Backup\Dto\Job\JobRestoreDataDto;
use WPStaging\Backup\Service\Database\Importer\DatabaseSearchReplacerInterface;
use WPStaging\Framework\Database\SearchReplace;

class DatabaseSearchReplacer implements DatabaseSearchReplacerInterface
{
    protected $search  = [];
    protected $replace = [];

    protected $sourceSiteUrl;
    protected $sourceHomeUrl;

    protected $sourceSiteHostname;
    protected $sourceHomeHostname;

    protected $sourceSiteUploadURL;

    protected $destinationSiteUrl;
    protected $destinationHomeUrl;

    protected $destinationSiteHostname;
    protected $destinationHomeHostname;

    protected $destinationSiteUploadURL;

    protected $matchingScheme;

    protected $plugins = [];

    /** @var bool|null */
    protected $requireCslashEscaping = null;

    /**
     * @return SearchReplace
     */
    public function getSearchAndReplace(JobRestoreDataDto $jobDataDto, $destinationSiteUrl, $destinationHomeUrl, $absPath = ABSPATH, $destinationSiteUploadURL = null)
    {
        $this->plugins = $jobDataDto->getBackupMetadata()->getPlugins();

        $this->sourceSiteUrl = untrailingslashit($jobDataDto->getBackupMetadata()->getSiteUrl());
        $this->sourceHomeUrl = untrailingslashit($jobDataDto->getBackupMetadata()->getHomeUrl());

        $this->sourceSiteHostname = untrailingslashit($this->buildHostname($this->sourceSiteUrl));
        $this->sourceHomeHostname = untrailingslashit($this->buildHostname($this->sourceHomeUrl));

        $this->destinationSiteUrl = untrailingslashit($destinationSiteUrl);
        $this->destinationHomeUrl = untrailingslashit($destinationHomeUrl);

        $this->destinationSiteHostname = untrailingslashit($this->buildHostname($this->destinationSiteUrl));
        $this->destinationHomeHostname = untrailingslashit($this->buildHostname($this->destinationHomeUrl));

        $this->sourceSiteUploadURL = untrailingslashit($jobDataDto->getBackupMetadata()->getUploadsUrl());
        $this->destinationSiteUploadURL = $destinationSiteUploadURL;
        $this->prepareUploadURLs();

        // Check if scheme is identical https or http on both sites
        $this->matchingScheme = parse_url($this->sourceSiteUrl, PHP_URL_SCHEME) === parse_url($this->destinationSiteUrl, PHP_URL_SCHEME);

        if ($this->matchingScheme) {
            $this->replaceGenericScheme();
        } else {
            $this->replaceMultipleSchemes();
            $this->replaceGenericScheme();
        }

        $this->replaceAbsPath($jobDataDto, $absPath);

        // Remove unnecessary S/R
        foreach ($this->search as $k => $searchItem) {
            if ($this->replace[$k] === $searchItem) {
                unset($this->search[$k]);
                unset($this->replace[$k]);
            }
        }

        // Recalculate indexes
        $this->search = array_values($this->search);
        $this->replace = array_values($this->replace);

        // This help sort according to search also keeping replace associated with search
        $searchReplaceToSort = array_combine($this->search, $this->replace);

        $searchReplaceToSort = apply_filters('wpstg.backup.restore.searchreplace', $searchReplaceToSort, $absPath, $this->sourceSiteUrl, $this->sourceHomeUrl, $this->destinationSiteUrl, $this->destinationHomeUrl);

        /*
         * Order items, so that the biggest values are replaced first.
         * This prevents smaller items from being replaced on
         * bigger items that starts with the same string but
         * ends differently.
         */
        uksort($searchReplaceToSort, function ($item1, $item2) {
            if (strlen($item1) == strlen($item2)) {
                return 0;
            }
            return (strlen($item1) > strlen($item2)) ? -1 : 1;
        });

        $orderedSearch = array_keys($searchReplaceToSort);
        $orderedReplace = array_values($searchReplaceToSort);

        return (new SearchReplace())
            ->setSearch($orderedSearch)
            ->setReplace($orderedReplace)
            ->setWpBakeryActive($jobDataDto->getBackupMetadata()->getWpBakeryActive());
    }

    /**
     * @dev Public for testing purposes only.
     */
    public function buildHostname($url)
    {
        $parsedUrl = parse_url($url);

        if (!is_array($parsedUrl) || !array_key_exists('host', $parsedUrl)) {
            throw new \UnexpectedValueException("Bad URL format, cannot proceed.");
        }

        // This is a requirement
        $hostname = $parsedUrl['host'];

        // This will be populated if WordPress was/is going to be in a subfolder
        if (array_key_exists('path', $parsedUrl)) {
            $hostname = trailingslashit($hostname) . trim($parsedUrl['path'], '/');
        }

        return $hostname;
    }

    protected function replaceAbsPath($jobDataDto, $absPath)
    {
        // Early bail if ABSPATH already same
        if ($jobDataDto->getBackupMetadata()->getAbsPath() === $absPath) {
            return;
        }

        $this->search[] = $jobDataDto->getBackupMetadata()->getAbsPath();
        $this->search[] = addcslashes($jobDataDto->getBackupMetadata()->getAbsPath(), '/');
        $this->search[] = urlencode($jobDataDto->getBackupMetadata()->getAbsPath());

        $this->replace[] = $absPath;
        $this->replace[] = addcslashes($absPath, '/');
        $this->replace[] = urlencode($absPath);

        if (urlencode($jobDataDto->getBackupMetadata()->getAbsPath()) !== rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath())) {
            $this->search[] = rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath());
            $this->replace[] = rawurlencode($absPath);
        }

        // Normalized $absPath
        if (wp_normalize_path($jobDataDto->getBackupMetadata()->getAbsPath()) !== $jobDataDto->getBackupMetadata()->getAbsPath()) {
            $this->search[] = wp_normalize_path($jobDataDto->getBackupMetadata()->getAbsPath());
            $this->search[] = wp_normalize_path(addcslashes($jobDataDto->getBackupMetadata()->getAbsPath(), '/'));
            $this->search[] = wp_normalize_path(urlencode($jobDataDto->getBackupMetadata()->getAbsPath()));

            $this->replace[] = wp_normalize_path($absPath);
            $this->replace[] = wp_normalize_path(addcslashes($absPath, '/'));
            $this->replace[] = wp_normalize_path(urlencode($absPath));

            if (wp_normalize_path(urlencode($jobDataDto->getBackupMetadata()->getAbsPath())) !== wp_normalize_path(rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath()))) {
                $this->search[] = wp_normalize_path(rawurlencode($jobDataDto->getBackupMetadata()->getAbsPath()));
                $this->replace[] = wp_normalize_path(rawurlencode($absPath));
            }
        }
    }

    protected function replaceGenericScheme()
    {
        if ($this->isIdenticalSiteHostname()) {
            $this->replaceGenericHomeScheme();
            return;
        }

        $this->replaceURLs($this->sourceSiteHostname, $this->destinationSiteHostname);

        $this->replaceUploadURLs();
        $this->replaceGenericHomeScheme();
    }

    protected function replaceGenericHomeScheme()
    {
        // A cross-domain scenario between FE and Admin
        if (!$this->isCrossDomain()) {
            return;
        }

        if ($this->isIdenticalHomeHostname()) {
            return;
        }

        $this->replaceURLs($this->sourceHomeHostname, $this->destinationHomeHostname);
    }

    protected function replaceUploadURLs()
    {
        if ($this->isIdenticalUploadURL()) {
            return;
        }

        $sourceUploadURLWithoutScheme = trailingslashit($this->sourceSiteHostname) . $this->sourceSiteUploadURL;
        $destinationUploadURLWithoutScheme = trailingslashit($this->destinationSiteHostname) . $this->destinationSiteUploadURL;
        $this->replaceURLs($sourceUploadURLWithoutScheme, $destinationUploadURLWithoutScheme);
    }

    protected function replaceURLs($sourceURL, $destinationURL, $doubleSlashPrefix = true)
    {
        $prefix = $doubleSlashPrefix ? '//' : '';
        $sourceGenericProtocol = $prefix . $sourceURL;
        $destinationGenericProtocol = $prefix . $destinationURL;

        $sourceGenericProtocolJsonEscaped = addcslashes($sourceGenericProtocol, '/');
        $destinationGenericProtocolJsonEscaped = addcslashes($destinationGenericProtocol, '/');

        $this->search[] = $sourceGenericProtocol;
        $this->search[] = $sourceGenericProtocolJsonEscaped;
        $this->search[] = urlencode($sourceGenericProtocol);

        $this->replace[] = $destinationGenericProtocol;
        $this->replace[] = $destinationGenericProtocolJsonEscaped;
        $this->replace[] = urlencode($destinationGenericProtocol);

        if ($this->isExtraCslashEscapingRequired()) {
            $this->search[] = addcslashes($sourceGenericProtocolJsonEscaped, '/');
            $this->replace[] = addcslashes($destinationGenericProtocolJsonEscaped, '/');
        }
    }

    protected function replaceMultipleSchemes()
    {
        if ($this->isIdenticalSiteHostname()) {
            $this->replaceMultipleHomeSchemes();
            $this->replaceMultipleSchemesUploadURL();
            return;
        }

        $sourceSiteHostnameJsonEscapedHttps = addcslashes('https://' . $this->sourceSiteHostname, '/');
        $sourceSiteHostnameJsonEscapedHttp = addcslashes('http://' . $this->sourceSiteHostname, '/');

        $this->search[] = 'https://' . $this->sourceSiteHostname;
        $this->search[] = 'http://' . $this->sourceSiteHostname;
        $this->search[] = $sourceSiteHostnameJsonEscapedHttps;
        $this->search[] = $sourceSiteHostnameJsonEscapedHttp;
        $this->search[] = urlencode('https://' . $this->sourceSiteHostname);
        $this->search[] = urlencode('http://' . $this->sourceSiteHostname);

        $this->replace[] = $this->destinationSiteUrl;
        $this->replace[] = $this->destinationSiteUrl;
        $this->replace[] = addcslashes($this->destinationSiteUrl, '/');
        $this->replace[] = addcslashes($this->destinationSiteUrl, '/');
        $this->replace[] = urlencode($this->destinationSiteUrl);
        $this->replace[] = urlencode($this->destinationSiteUrl);

        // One more time json escaping for some edge cases i.e. RevSlider
        if ($this->isExtraCslashEscapingRequired()) {
            $this->search[] = addcslashes($sourceSiteHostnameJsonEscapedHttps, '/');
            $this->search[] = addcslashes($sourceSiteHostnameJsonEscapedHttp, '/');
            $this->replace[] = addcslashes($this->destinationSiteUrl, '/');
            $this->replace[] = addcslashes($this->destinationSiteUrl, '/');
        }

        $this->replaceMultipleHomeSchemes();
    }

    protected function replaceMultipleHomeSchemes()
    {
        // A cross-domain scenario between FE and Admin
        if (!$this->isCrossDomain()) {
            return;
        }

        if ($this->isIdenticalHomeHostname()) {
            return;
        }

        $sourceHomeHostnameJsonEscapedHttps = addcslashes('https://' . $this->sourceHomeHostname, '/');
        $sourceHomeHostnameJsonEscapedHttp = addcslashes('http://' . $this->sourceHomeHostname, '/');

        $this->search[] = 'https://' . $this->sourceHomeHostname;
        $this->search[] = 'http://' . $this->sourceHomeHostname;
        $this->search[] = $sourceHomeHostnameJsonEscapedHttps;
        $this->search[] = $sourceHomeHostnameJsonEscapedHttp;
        $this->search[] = urlencode('https://' . $this->sourceHomeHostname);
        $this->search[] = urlencode('http://' . $this->sourceHomeHostname);

        $this->replace[] = $this->destinationHomeUrl;
        $this->replace[] = $this->destinationHomeUrl;
        $this->replace[] = addcslashes($this->destinationHomeUrl, '/');
        $this->replace[] = addcslashes($this->destinationHomeUrl, '/');
        $this->replace[] = urlencode($this->destinationHomeUrl);
        $this->replace[] = urlencode($this->destinationHomeUrl);

        // One more time json escaping for some edge cases i.e. RevSlider
        if ($this->isExtraCslashEscapingRequired()) {
            $this->search[] = addcslashes($sourceHomeHostnameJsonEscapedHttps, '/');
            $this->search[] = addcslashes($sourceHomeHostnameJsonEscapedHttp, '/');
            $this->replace[] = addcslashes($this->destinationHomeUrl, '/');
            $this->replace[] = addcslashes($this->destinationHomeUrl, '/');
        }
    }

    protected function replaceMultipleSchemesUploadURL()
    {
        if ($this->isIdenticalUploadURL()) {
            return;
        }

        $sourceUploadURLWithHttpsScheme = 'https://' . trailingslashit($this->sourceSiteHostname) . $this->sourceSiteUploadURL;
        $destinationUploadURLWithScheme = trailingslashit($this->destinationSiteUrl) . $this->destinationSiteUploadURL;
        $this->replaceURLs($sourceUploadURLWithHttpsScheme, $destinationUploadURLWithScheme, $doubleSlashPrefix = false);
        $sourceUploadURLWithHttpScheme = 'http://' . trailingslashit($this->sourceSiteHostname) . $this->sourceSiteUploadURL;
        $this->replaceURLs($sourceUploadURLWithHttpScheme, $destinationUploadURLWithScheme, $doubleSlashPrefix = false);
    }

    /**
     * Plugins like rev-slider plugin require extra cslash escaping.
     * Return true to add this extra rules for S/R
     * Run this only as a dependency to lower the execution time.
     *
     * @return bool
     */
    protected function isExtraCslashEscapingRequired()
    {
        if ($this->requireCslashEscaping !== null) {
            return $this->requireCslashEscaping;
        }

        $requireCslashEscaping = false;
        foreach ($this->plugins as $plugin) {
            if (in_array($plugin, $this->getPluginsWhichRequireCslashEscaping())) {
                $requireCslashEscaping = true;
                break;
            }
        }

        $this->requireCslashEscaping = apply_filters('wpstg.backup.restore.extended-cslash-search-replace', $requireCslashEscaping) === true;

        return $this->requireCslashEscaping;
    }

    protected function getPluginsWhichRequireCslashEscaping()
    {
        return [
            'revslider/revslider.php',
            'elementor/elementor.php'
        ];
    }

    /**
     * Sometimes the site url is not the same as the site hostname. E.g. https://example.com vs. http://example.com
     * @return bool
     */
    protected function isCrossDomain()
    {
        return $this->sourceSiteHostname !== $this->sourceHomeHostname;
    }

    /**
     * Is site hostname same between source and destination
     * @return bool
     */
    protected function isIdenticalSiteHostname()
    {
        return $this->sourceSiteHostname === $this->destinationSiteHostname;
    }

    /**
     * Is home hostname same between source and destination
     * @return bool
     */
    protected function isIdenticalHomeHostname()
    {
        return $this->sourceHomeHostname === $this->destinationHomeHostname;
    }

    protected function isIdenticalUploadURL()
    {
        return $this->sourceSiteUploadURL === $this->destinationSiteUploadURL;
    }

    protected function prepareUploadURLs()
    {
        if (empty($this->destinationSiteUploadURL)) {
            $uploadDir = wp_upload_dir(null, false, true);
            if (is_array($uploadDir)) {
                $this->destinationSiteUploadURL = array_key_exists('baseurl', $uploadDir) ? $uploadDir['baseurl'] : '';
            }
        }

        $this->destinationSiteUploadURL = untrailingslashit($this->destinationSiteUploadURL);

        $this->sourceSiteUploadURL = str_replace(trailingslashit($this->sourceSiteUrl), '', $this->sourceSiteUploadURL);
        $this->destinationSiteUploadURL = str_replace(trailingslashit($this->destinationSiteUrl), '', $this->destinationSiteUploadURL);
    }
}
