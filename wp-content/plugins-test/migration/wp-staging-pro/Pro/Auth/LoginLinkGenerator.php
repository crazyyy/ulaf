<?php

namespace WPStaging\Pro\Auth;

use WPStaging\Core\Cron\Cron;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Staging\Sites;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Adapter\SourceDatabase;
use wpdb;

/**
 * Class Generate Login Link
 * @package WPStaging\Pro\Auth
 */
class LoginLinkGenerator
{
    /** @var mixed */
    private $currentClone;

    /** @var mixed */
    private $cloneID;

    /** @var mixed */
    private $loginLinkSettings;

    /**
     * Path to plugin's Backend Dir
     * @var string
     */
    private $backendPath;

    /** @var Sanitize */
    private $sanitize;

    /** @var Auth */
    private $auth;

    /** @var wpdb */
    private $cloneDB;

    public function __construct(Auth $auth, Sanitize $sanitize)
    {
        // Path to backend
        $this->backendPath = WPSTG_PLUGIN_DIR . 'Backend/';
        $this->auth = $auth;
        $this->sanitize = $sanitize;
    }

    /**
     * @return void
     */
    public function ajaxLoginLinkUserInterface()
    {
        if (!$this->isAuthenticated()) {
            return;
        }
        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (isset($_POST["clone"]) && array_key_exists($_POST["clone"], $existingClones)) {
            $clone = $existingClones[$this->sanitize->sanitizeString($_POST["clone"])];
            require_once "{$this->backendPath}Pro/views/generate-login-ui.php";
            wp_die();
        }

        wp_send_json_error([
            'message' => esc_html__("Unknown error. Please reload the page and try again", "wp-staging")
        ]);
    }

    /**
     * @return void
     */
    public function ajaxSaveGeneratedLinkData()
    {
        if (!$this->isAuthenticated()) {
            return;
        }
        $result = $this->start();
        if ($result === false) {
            wp_send_json_error(['message' => esc_html__('Fail to save data!', 'wp-staging')]);
        }
        wp_send_json_success(['message' => esc_html__('Login Link created successfully!', 'wp-staging')]);
    }

    /**
     * @return int|false
     */
    public function start()
    {
        if (empty($_POST['cloneID']) || empty($_POST['role']) || empty($_POST['uniqueid'])) {
            return false;
        }
        $this->cloneID = sanitize_text_field($_POST['cloneID']);
        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (!isset($existingClones[$this->cloneID])) {
            return false;
        }
        $this->currentClone = $existingClones[$this->cloneID];
        $this->loginLinkSettings['role'] = sanitize_text_field($_POST['role']);
        $this->loginLinkSettings['loginID'] = sanitize_text_field($_POST['uniqueid']);
        $this->loginLinkSettings['minutes'] = isset($_POST['minutes']) ? sanitize_text_field($_POST['minutes']) : '';
        $this->loginLinkSettings['hours'] = isset($_POST['hours']) ? sanitize_text_field($_POST['hours']) : '';
        $this->loginLinkSettings['days'] = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '';

        /** @var SourceDatabase */
        $sourceDatabase = WPStaging::make(SourceDatabase::class);
        $sourceDatabase->setOptions((object)$this->currentClone);
        $this->cloneDB = $sourceDatabase->getDatabase();

        $result = $this->saveData();
        if ($result !== false) {
            $this->scheduleCleanLoginData();
        }
        return $result;
    }

    /**
     * @return int|false int for the number of rows affected during the updating of the clone's DB, or false on failure.
     */
    protected function saveData()
    {
        $cloneOptionsTable = $this->currentClone['prefix'] . 'options';
        $cloneOptionsName = Sites::STAGING_LOGIN_LINK_SETTINGS;
        $cloneOptions = $this->cloneDB->query("SELECT * FROM  {$cloneOptionsTable} WHERE option_name='{$cloneOptionsName}';");
        if (empty($cloneOptions)) {
            $result = $this->cloneDB->insert(
                $cloneOptionsTable,
                [
                    'option_name' => $cloneOptionsName,
                    'option_value' => serialize($this->loginLinkSettings),
                ]
            );
        } else {
            $result = $this->cloneDB->update(
                $cloneOptionsTable,
                [
                    'option_value' => serialize($this->loginLinkSettings),
                ],
                ['option_name' => $cloneOptionsName]
            );
        }
        return $result;
    }

    protected function scheduleCleanLoginData()
    {
        wp_clear_scheduled_hook('wpstg_clean_login_link_data', [$this->cloneID]);
        wp_schedule_event(strtotime($this->loginLinkSettings['days'] . ' days ' . $this->loginLinkSettings['hours'] . ' hours' . $this->loginLinkSettings['minutes'] . ' minutes'), Cron::HOURLY, 'wpstg_clean_login_link_data', [$this->cloneID]);
    }

    /**
     * @return bool Whether the current request is considered to be authenticated.
     */
    protected function isAuthenticated()
    {
        return $this->auth->isAuthenticatedRequest();
    }
}
