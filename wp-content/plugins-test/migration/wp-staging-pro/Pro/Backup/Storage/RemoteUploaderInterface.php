<?php

namespace WPStaging\Pro\Backup\Storage;

use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Exceptions\DiskNotWritableException;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

interface RemoteUploaderInterface
{
    /** @return string */
    public function getProviderName();

    public function setupUpload(LoggerInterface $logger, JobBackupDataDto $jobDataDto, $chunkSize = 1024 * 1024);

    /**
     * @var string $backupFilePath
     * @var string $fileName
     * @return bool
     */
    public function setBackupFilePath($backupFilePath, $fileName);

    /** @return int */
    public function chunkUpload();

    /**
     * @param array $uploadsToVerify
     * @return bool
     */
    public function verifyUploads($uploadsToVerify);

    /**
     * @param int $backupSize
     * @throws DiskNotWritableException
     */
    public function checkDiskSize($backupSize);

    /**
     * Mainly added to improve unit testing of remote storage
     * Though can be later added to remote storages settings page so user can test upload himself
     * @param string $filePath
     * @param string $remoteFileName
     * @return bool
     */
    public function uploadFile($filePath, $remoteFileName = '');

    public function stopUpload();

    /** @return string */
    public function getError();

    /** @return bool */
    public function deleteOldestBackups();

    /** @return array */
    public function getBackups();
}
