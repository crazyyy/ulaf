<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Backup\Task\Tasks\JobBackup;

use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Backup\BackupScheduler;
use WPStaging\Backup\Dto\StepsDto;
use WPStaging\Backup\Task\BackupTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;

class ScheduleBackupTask extends BackupTask
{
    private $backupScheduler;

    public function __construct(BackupScheduler $backupScheduler, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->backupScheduler = $backupScheduler;
    }

    public static function getTaskName()
    {
        return 'backup_scheduler';
    }

    public static function getTaskTitle()
    {
        return 'Creating Backup Plan';
    }

    public function execute()
    {
        $scheduleId = wp_generate_password(12, false);

        $this->jobDataDto->setScheduleId($scheduleId);

        $this->backupScheduler->scheduleBackup($this->jobDataDto, $scheduleId);

        $this->logger->info(sprintf('Created scheduled backup id: '  . $scheduleId));

        return $this->generateResponse(true);
    }
}
