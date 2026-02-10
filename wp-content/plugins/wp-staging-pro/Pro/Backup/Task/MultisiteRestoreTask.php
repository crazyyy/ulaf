<?php

namespace WPStaging\Pro\Backup\Task;

use UnexpectedValueException;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Backup\Dto\StepsDto;
use WPStaging\Backup\Task\RestoreTask;
use WPStaging\Pro\Backup\Service\Database\Importer\DomainPathUpdater;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class MultisiteRestoreTask
 *
 * This is an abstract class for the multisite specific restore actions of restoring a site.
 *
 * @package WPStaging\Pro\Backup\Task
 */
abstract class MultisiteRestoreTask extends RestoreTask
{
    /** @var array */
    protected $sites;

    /** @var wpdb */
    protected $wpdb;

    /** @var string */
    protected $sourceSiteDomain;

    /** @var string */
    protected $sourceSitePath;

    /** @var bool */
    protected $isSubdomainInstall;

    /** @var DomainPathUpdater */
    protected $domainPathUpdater;

    public function __construct(DomainPathUpdater $domainPathUpdater, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);

        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->domainPathUpdater = $domainPathUpdater;
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function adjustDomainPath()
    {
        $this->domainPathUpdater->readMetaData($this->jobDataDto);
        $this->sourceSiteDomain = $this->domainPathUpdater->getSourceSiteDomain();
        $this->sourceSitePath = $this->domainPathUpdater->getSourceSitePath();
        $this->isSubdomainInstall = $this->domainPathUpdater->getIsSourceSubdomainInstall();
        $this->sites = $this->domainPathUpdater->getSitesWithNewURLs(DOMAIN_CURRENT_SITE, PATH_CURRENT_SITE, home_url(), is_subdomain_install());
    }
}
