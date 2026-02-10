<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use WPStaging\Backup\Task\Tasks\JobBackup\BackupRequirementsCheckTask as BasicBackupRequirementsCheckTask;

class BackupRequirementsCheckTask extends BasicBackupRequirementsCheckTask
{
    protected function cannotBackupMultisite()
    {
        // no-op
    }
}
