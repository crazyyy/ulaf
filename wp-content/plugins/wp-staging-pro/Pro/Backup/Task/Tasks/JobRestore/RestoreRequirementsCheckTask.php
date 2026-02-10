<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRestore;

use WPStaging\Backup\Task\Tasks\JobRestore\RestoreRequirementsCheckTask as BasicRestoreRequirementsCheckTask;

class RestoreRequirementsCheckTask extends BasicRestoreRequirementsCheckTask
{
    protected function cannotRestoreOnMultisite()
    {
        // no-op
    }

    protected function cannotMigrate()
    {
        // no-op
    }

    protected function cannotRestoreMultipartBackup()
    {
        // no-op
    }
}
