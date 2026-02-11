<?php

namespace WPStaging\Pro\Backup\Service\Multipart;

use WPStaging\Backup\Dto\Job\JobRestoreDataDto;
use WPStaging\Backup\Dto\StepsDto;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Backup\Service\Database\DatabaseImporter;
use WPStaging\Backup\Service\Database\Importer\DatabaseSearchReplacerInterface;
use WPStaging\Backup\Service\Extractor;
use WPStaging\Backup\Service\Multipart\MultipartRestoreInterface;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Filesystem\MissingFileException;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class MultipartRestorer implements MultipartRestoreInterface
{
    /** @var int */
    private $totalFilesPart;

    /** @var int */
    private $filesExtractionIndex;

    public function __construct()
    {
        $this->totalFilesPart = 0;
        $this->filesExtractionIndex = 0;
    }

    /**
     * @param JobRestoreDataDto $jobDataDto
     * @param LoggerInterface $logger
     * @param StepsDto $stepsDto
     * @param Extractor $extractorService
     *
     * @throws MissingFileException
     */
    public function prepareExtraction(JobRestoreDataDto $jobDataDto, LoggerInterface $logger, StepsDto $stepsDto, Extractor $extractorService)
    {
        $this->filesExtractionIndex = $jobDataDto->getFilePartIndex();
        $metadata = $jobDataDto->getBackupMetadata();

        $filesPart = $metadata->getMultipartMetadata()->getFileParts();
        $this->totalFilesPart = count($filesPart);
        $backupPart = $filesPart[$this->filesExtractionIndex];

        $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
        $partMetadata = new BackupMetadata();

        $fileToExtract = $backupsDirectory . $backupPart;

        if (!file_exists($fileToExtract)) {
            $logger->warning(sprintf(esc_html__('Backup part %s doesn\'t exist. Skipping from extraction', 'wp-staging'), basename($fileToExtract)));
            throw new MissingFileException();
        }

        $partMetadata = $partMetadata->hydrateByFilePath($fileToExtract);
        $stepsDto->setTotal($partMetadata->getMultipartMetadata()->getTotalFiles());
        $extractorService->inject($jobDataDto, $logger);
        $extractorService->setFileToExtract($fileToExtract);
    }

    /**
     * @param JobRestoreDataDto $jobDataDto
     * @param LoggerInterface $logger
     */
    public function setNextExtractedFile(JobRestoreDataDto $jobDataDto, LoggerInterface $logger)
    {
        $this->filesExtractionIndex++;
        $jobDataDto->setFilePartIndex($this->filesExtractionIndex);
        $jobDataDto->setExtractorFilesExtracted(0);
        $jobDataDto->setExtractorMetadataIndexPosition(0);
        if ($this->filesExtractionIndex === $this->totalFilesPart && $jobDataDto->getBackupMetadata()->getIsExportingUploads()) {
            $logger->info(esc_html__('Restored Media Library', 'wp-staging'));
        }
    }

    public function prepareDatabaseRestore(JobRestoreDataDto $jobDataDto, LoggerInterface $logger, DatabaseImporter $databaseRestore, StepsDto $stepsDto, DatabaseSearchReplacerInterface $databaseSearchReplacer, $backupsDirectory)
    {
        $metadata = $jobDataDto->getBackupMetadata();
        $databasePartIndex = $jobDataDto->getDatabasePartIndex();
        $databasePart = $metadata->getMultipartMetadata()->getDatabaseParts()[$databasePartIndex];
        $databaseFile = $backupsDirectory . $databasePart;

        if (!file_exists($databaseFile)) {
            $jobDataDto->setDatabasePartIndex($databasePartIndex + 1);
            $jobDataDto->setIsMissingDatabaseFile(true);
            $logger->warning(sprintf('Skip restoring database. Missing Part Index: %d.', $databasePartIndex));

            throw new MissingFileException();
        }

        $databaseRestore->setFile($databaseFile);
        $databaseRestore->seekLine($stepsDto->getCurrent());

        if (!$stepsDto->getTotal()) {
            $stepsDto->setTotal($databaseRestore->getTotalLines());
            if ($databasePartIndex !== 0) {
                $logger->info(sprintf('Restoring Database File Part Index: %d', $databasePartIndex));
            }
        }

        $databaseRestore->setSearchReplace($databaseSearchReplacer->getSearchAndReplace($jobDataDto, get_site_url(), get_home_url()));
    }
}
