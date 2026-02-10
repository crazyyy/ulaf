<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Dto\Job\JobRestoreDataDto;
use WPStaging\Backup\Dto\JobDataDto;
use WPStaging\Backup\Job\AbstractJob;
use WPStaging\Backup\Job\JobBackupProvider;
use WPStaging\Backup\Job\JobRestoreProvider;
use WPStaging\Backup\Service\Database\Importer\DatabaseSearchReplacerInterface;
use WPStaging\Backup\Service\Multipart\MultipartInjection;
use WPStaging\Backup\Service\Multipart\MultipartRestoreInterface;
use WPStaging\Backup\Service\Multipart\MultipartSplitInterface;
use WPStaging\Backup\Task\Tasks\JobRestore\RestoreDatabaseTask;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Pro\Backup\Ajax\ManageSchedules;
use WPStaging\Pro\Backup\Job\Jobs\JobBackup;
use WPStaging\Pro\Backup\Job\Jobs\JobRestore;
use WPStaging\Pro\Backup\Service\Database\Importer\DatabaseSearchReplacer;
use WPStaging\Pro\Backup\Service\Multipart\MultipartRestorer;
use WPStaging\Pro\Backup\Service\Multipart\MultipartSplitter;
use WPStaging\Pro\Backup\Storage\StoragesServiceProvider;

/**
 * Class BackupServiceProvider
 * @package WPStaging\Pro\Backup
 *
 * This class is used to register all the services related to the Backup feature that are PRO only features like
 * Multisite Support, Multipart Backups, Remote Storages, Migration, Multiple Backup Schedules etc etc
 */
class BackupServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->when(JobBackup::class)
                ->needs(JobDataDto::class)
                ->give(JobBackupDataDto::class);

        $this->container->when(JobRestore::class)
                ->needs(JobDataDto::class)
                ->give(JobRestoreDataDto::class);

        $this->container->register(StoragesServiceProvider::class);

        $container = $this->container;

        $this->container->when(JobBackupProvider::class)
                        ->needs(AbstractJob::class)
                        ->give(function () use (&$container) {
                            return $container->make(JobBackup::class);
                        });

        $this->container->when(JobRestoreProvider::class)
                        ->needs(AbstractJob::class)
                        ->give(function () use (&$container) {
                            return $container->make(JobRestore::class);
                        });

        foreach (MultipartInjection::MULTIPART_CLASSES as $classId) {
            $this->container->when($classId)
                            ->needs(MultipartSplitInterface::class)
                            ->give(MultipartSplitter::class);
        }

        foreach (MultipartInjection::RESTORE_CLASSES as $classId) {
            $this->container->when($classId)
                            ->needs(MultipartRestoreInterface::class)
                            ->give(MultipartRestorer::class);
        }

        $this->container->when(RestoreDatabaseTask::class)
                        ->needs(DatabaseSearchReplacerInterface::class)
                        ->give(DatabaseSearchReplacer::class);
    }

    protected function addHooks()
    {
        add_action('wp_ajax_wpstg--backups-edit-schedule', $this->container->callback(ManageSchedules::class, 'editSchedule'), 10, 1);
        add_action('wp_ajax_wpstg--backups-edit-schedule-modal', $this->container->callback(ManageSchedules::class, 'editScheduleModal'), 10, 1);
    }
}
