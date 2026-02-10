<?php

namespace org\wplake\acf_views\vendors;

if (!\class_exists('Puc_v4p13_DebugBar_PluginPanel', \false)) {
    class Puc_v4p13_DebugBar_PluginPanel extends \org\wplake\acf_views\vendors\Puc_v4p13_DebugBar_Panel
    {
        /**
         * @var Puc_v4p13_Plugin_UpdateChecker
         */
        protected $updateChecker;
        protected function displayConfigHeader()
        {
            $this->row('Plugin file', \htmlentities($this->updateChecker->pluginFile));
            parent::displayConfigHeader();
        }
        protected function getMetadataButton()
        {
            $requestInfoButton = '';
            if (\function_exists('get_submit_button')) {
                $requestInfoButton = \get_submit_button('Request Info', 'secondary', 'puc-request-info-button', \false, array('id' => $this->updateChecker->getUniqueName('request-info-button')));
            }
            return $requestInfoButton;
        }
        protected function getUpdateFields()
        {
            return \array_merge(parent::getUpdateFields(), array('homepage', 'upgrade_notice', 'tested'));
        }
    }
}
