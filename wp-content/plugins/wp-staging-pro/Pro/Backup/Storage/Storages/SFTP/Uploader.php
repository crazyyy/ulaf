<?php

namespace WPStaging\Pro\Backup\Storage\Storages\SFTP;

use Exception;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Pro\Backup\Storage\RemoteUploaderInterface;
use WPStaging\Backup\WithBackupIdentifier;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

use function WPStaging\functions\debug_log;

class Uploader implements RemoteUploaderInterface
{
    use WithBackupIdentifier;

    /** @var JobBackupDataDto */
    private $jobDataDto;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $filePath;

    /** @var string */
    private $fileName;

    /** @var string */
    private $path;

    /** @var string */
    private $remotePath;

    /** @var int */
    private $maxBackupsToKeep;

    /** @var FileObject */
    private $fileObject;

    /** @var int */
    private $chunkSize;

    /** @var Auth */
    private $auth;

    /** @var ClientInterface */
    private $client;

    /** @var bool|string */
    private $error;

    /** @var Strings */
    private $strings;

    public function __construct(Auth $auth, Strings $strings)
    {
        $this->error = false;
        $this->auth = $auth;
        $this->strings = $strings;
        if (!$this->auth->isAuthenticated()) {
            $this->error = __('FTP / SFTP service is not authenticated. Backup is still available locally.', 'wp-staging');
            return;
        }

        $this->client = $auth->getClient();
        $options = $this->auth->getOptions();
        $this->path = !empty($options['location']) ? trailingslashit($options['location']) : '';
        $this->maxBackupsToKeep = isset($options['maxBackupsToKeep']) ? $options['maxBackupsToKeep'] : 15;
        $this->maxBackupsToKeep = intval($this->maxBackupsToKeep);
        $this->maxBackupsToKeep = $this->maxBackupsToKeep > 0 ? $this->maxBackupsToKeep : 15;
    }

    public function getProviderName()
    {
        return 'SFTP / FTP';
    }

    /**
     * @param int $backupSize
     * @throws DiskNotWritableException
     */
    public function checkDiskSize($backupSize)
    {
        //no-op
    }

    /**
     * @param LoggerInterface $logger
     * @param JobBackupDataDto $jobDataDto
     * @param $chunkSize
     * @return void
     */
    public function setupUpload(LoggerInterface $logger, JobBackupDataDto $jobDataDto, $chunkSize = 1 * 1024 * 1024)
    {
        $this->logger = $logger;
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize = $chunkSize;
    }

    public function setBackupFilePath($backupFilePath, $fileName)
    {
        $this->fileName = $fileName;
        $this->filePath = $backupFilePath;
        $this->fileObject = new FileObject($this->filePath, FileObject::MODE_READ);
        $this->remotePath = $this->path . $this->fileObject->getBasename();

        if (!$this->client->login()) {
            $this->error = 'Unable to connect to ' . $this->getProviderName();
            return false;
        }

        $uploadMetadata = $this->jobDataDto->getRemoteStorageMeta();
        if (!array_key_exists($this->fileName, $uploadMetadata)) {
            $this->setMetadata(0);
            $this->logger->info('Starting upload of file:' . $this->fileName);
            return true;
        }

        return true;
    }

    /**
     * @return int
     */
    public function chunkUpload()
    {
        $fileMetadata = $this->jobDataDto->getRemoteStorageMeta()[$this->fileName];
        $offset = $fileMetadata['Offset'];

        $this->fileObject->fseek($offset);
        $chunk = $this->fileObject->fread($this->chunkSize);

        $chunkSize = strlen($chunk);
        try {
            $this->client->upload($this->path, $this->fileName, $chunk, $offset);
            $offset += $chunkSize;
        } catch (StorageException $ex) {
            throw new StorageException($ex->getMessage());
        } catch (Exception $ex) {
            debug_log("Error: " . $ex->getMessage());
        }

        if ($offset >= $this->fileObject->getSize()) {
            throw new FinishedQueueException();
        }

        $this->setMetadata($offset);
        return $chunkSize;
    }

    /**
     * @param string $filePath
     * @param string $remoteFileName
     * @return bool
     */
    public function uploadFile($filePath, $remoteFileName = '')
    {
        $fileObject = new FileObject($filePath, FileObject::MODE_READ);

        if (empty($remoteFileName)) {
            $remoteFileName = $fileObject->getBasename();
        }

        $fileObject->fseek(0);
        $chunk = $fileObject->fread($fileObject->getSize());

        return $this->uploadFileRetry($remoteFileName, $chunk, 0);
    }

    public function stopUpload()
    {
        $this->client->close();
    }

    /**
     * @return bool|string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getBackups()
    {
        if ($this->client === false) {
            $this->error = 'Unable to Initiate a Client';
            return [];
        }

        if (!$this->client->login()) {
            $this->error = "Unable to connect to " . $this->client->getError();
            return [];
        }

        try {
            $files = $this->client->getFiles($this->path);
            if (!is_array($files)) {
                $this->error = $this->client->getError() . ' - ' . __('Unable to fetch existing backups for cleanup', 'wp-staging');
                return [];
            }

            $backups = [];
            foreach ($files as $file) {
                if ($this->strings->endsWith($file['name'], '.wpstg') || $this->strings->endsWith($file['name'], '.sql')) {
                    $backups[] = $file;
                }
            }

            return $backups;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return [];
        }
    }

    /**
     * @return bool
     */
    public function deleteOldestBackups()
    {
        if ($this->client === false) {
            $this->error = 'Unable to Initiate a Client';
            return false;
        }

        if (!$this->client->login()) {
            $this->error = "Unable to connect to " . $this->client->getError();
            return false;
        }

        try {
            $files = $this->getBackups();

            if (count($files) < $this->maxBackupsToKeep) {
                return true;
            }

            $backups = [];
            /**
             * arrange the backup in the format key value format to make it easy to delete
             * Extract the id of the backup from the file
             */
            foreach ($files as $file) {
                $fileName = str_replace($this->path, '', $file['name']);
                $backupId = $this->extractBackupIdFromFilename($fileName);
                if (!array_key_exists($backupId, $backups)) {
                    $backups[$backupId] = [];
                }

                if (!$this->isBackupPart($fileName)) {
                    $backups[$backupId]['id'] = $file['name'];
                    continue;
                }

                if (!array_key_exists('parts', $backups[$backupId])) {
                    $backups[$backupId]['parts'] = [];
                }

                $backups[$backupId]['parts'][] = $file['name'];
            }

            $backupsToDelete = count($backups) - $this->maxBackupsToKeep;
            foreach ($backups as $backup) {
                if ($backupsToDelete < 0) {
                    return true;
                }

                $this->client->setPath($this->path);

                if (array_key_exists('id', $backup)) {
                    $this->client->deleteFile($backup['id']);
                }

                if (array_key_exists('parts', $backup)) {
                    foreach ($backup['parts'] as $part) {
                        $result = $this->deleteFile($part);
                        //$result = $this->client->deleteFile($part);
                        if ($result === false) {
                            $this->error = $this->client->getError();
                            debug_log($this->error);
                            return false;
                        }
                    }
                }

                $backupsToDelete--;
            }

            return true;
        } catch (Exception $ex) {
            debug_log("Delete oldest backup");
            $this->error = $ex->getMessage();
            return false;
        }
    }

    /**
     * @param $uploadsToVerify array of backup files to verify
     * @return bool
     */
    public function verifyUploads($uploadsToVerify)
    {
        if (!$this->client->login()) {
            $this->error = "Unable to connect to " . $this->client->getError();
            return false;
        }

        $files = $this->client->getFiles($this->path);
        $this->client->close();
        if (!is_array($files)) {
            $this->error = $this->client->getError() . ' - ' . __('Unable to fetch existing backups for verification', 'wp-staging');
            return false;
        };

        $uploadsConfirmed = [];
        foreach ($files as $file) {
            $fileName = str_replace($this->path, '', $file['name']);
            if (array_key_exists($fileName, $uploadsToVerify) && (is_null($file['size']) || $uploadsToVerify[$fileName] === $file['size'])) {
                $uploadsConfirmed[] = $fileName;
            }
        }

        return count($uploadsConfirmed) === count($uploadsToVerify);
    }

    /**
     * @param int $offset
     */
    protected function setMetadata($offset = 0)
    {
        $this->jobDataDto->setRemoteStorageMeta([
            $this->fileName => [
                'Offset' => $offset,
            ]
        ]);
    }

    /**
     * @param string $remoteFileName
     * @param string $chunk
     * @param int $offset
     * @param int $retry
     * @return bool
     *
     * @throws StorageException
     */
    protected function uploadFileRetry($remoteFileName, $chunk, $offset, $retry = 3)
    {
        try {
            if (!$this->client->login()) {
                debug_log("Login Error: " . $this->client->getError());
                return false;
            }

            $this->client->upload($this->path, $remoteFileName, $chunk, $offset);
            $this->client->close();
        } catch (StorageException $ex) {
            debug_log("Storage Error: " . $ex->getMessage());
            $this->client->close();
            throw new StorageException($ex->getMessage());
        } catch (Exception $ex) {
            if ($retry > 0) {
                debug_log("Error: " . $ex->getMessage() . '... Trying again!');
                return $this->uploadFileRetry($remoteFileName, $chunk, $offset, $retry - 1);
            }

            $this->client->close();
            return false;
        }

        return true;
    }

    /**
     * @param string $file
     * @param int $retry
     * @return bool
     */
    protected function deleteFile($file, $retry = 3)
    {
        $result = $this->client->deleteFile($file);
        if ($result) {
            return true;
        }

        if ($retry > 0) {
            debug_log($this->client->getError() . '... Trying again!');
            usleep(500);
            return $this->deleteFile($file, $retry - 1);
        }

        return false;
    }
}
