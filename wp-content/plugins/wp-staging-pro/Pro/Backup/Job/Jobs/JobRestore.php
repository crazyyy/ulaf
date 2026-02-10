<?php

namespace WPStaging\Pro\Backup\Job\Jobs;

use WPStaging\Backup\Job\Jobs\JobRestore as BasicJobRestore;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\RestoreRequirementsCheckTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\UpdateDomainPathTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\UpdateSubsiteSiteHomeUrlTask;

class JobRestore extends BasicJobRestore
{
    protected function setRequirementTask()
    {
        $this->tasks[] = RestoreRequirementsCheckTask::class;
    }

    protected function addMultisiteTasks()
    {
        if (!is_multisite() || !is_main_site()) {
            return;
        }

        $this->tasks[] = UpdateDomainPathTask::class;
        $this->tasks[] = UpdateSubsiteSiteHomeUrlTask::class;
    }
}
