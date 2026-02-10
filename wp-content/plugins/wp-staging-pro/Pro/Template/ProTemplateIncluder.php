<?php

namespace WPStaging\Pro\Template;

class ProTemplateIncluder
{
    /** @var string */
    private $backEndViewsFolder;

    public function __construct()
    {
        $this->backEndViewsFolder = trailingslashit(WPSTG_PLUGIN_DIR) . 'Backend/Pro/views/';
    }

    /**
     * Add the "Push" button to the template
     */
    public function addPushButton($cloneID, $data, $license)
    {
        include $this->backEndViewsFolder . 'clone/ajax/push-button.php';
    }

    /**
     * Add the "Edit this Clone" link to the template
     */
    public function addEditCloneLink($cloneID, $data, $license)
    {
        include $this->backEndViewsFolder . 'clone/ajax/edit-clone.php';
    }

    /**
     * Add generate login link to the action menu for staging site
     *
     * @param  mixed $cloneID
     * @param  mixed $data
     * @param  mixed $license
     * @return void
     */
    public function addGenerateLoginLink($cloneID, $data, $license)
    {
        include $this->backEndViewsFolder . 'clone/ajax/generate-login.php';
    }
    /**
     * Add "Sync User Account" button on the actions tab
     *
     * @param  mixed $cloneID
     * @return void
     */
    public function addSyncAccountButton($cloneID)
    {
        include $this->backEndViewsFolder . 'clone/ajax/sync-button.php';
    }
}
