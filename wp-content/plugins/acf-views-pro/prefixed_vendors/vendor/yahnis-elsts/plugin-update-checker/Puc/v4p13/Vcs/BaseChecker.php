<?php

namespace org\wplake\acf_views\vendors;

if (!\interface_exists('org\\wplake\\acf_views\\vendors\\Puc_v4p13_Vcs_BaseChecker', \false)) {
    interface Puc_v4p13_Vcs_BaseChecker
    {
        /**
         * Set the repository branch to use for updates. Defaults to 'master'.
         *
         * @param string $branch
         * @return $this
         */
        public function setBranch($branch);
        /**
         * Set authentication credentials.
         *
         * @param array|string $credentials
         * @return $this
         */
        public function setAuthentication($credentials);
        /**
         * @return Puc_v4p13_Vcs_Api
         */
        public function getVcsApi();
    }
}
