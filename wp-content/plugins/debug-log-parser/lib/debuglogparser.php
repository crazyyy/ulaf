<?php

/**
 * A Wrapper class to start the Application.
 */
class DebugLogParser
{
    private $id = 'debug-log-parser';
    private $name = 'Debug Log Parser';
    private $relativePluginPath;
    /** @var \LogParser  */
    private $logParser;
    /** @var \DashboardWidget  */
    private $dashboardWidget;
    /** @var \SettingsPage  */
    private $settingsPage;
    /** @var \Settings  */
    private $settings;

    /**
     * Registers plugin-settings-page and dashboard-widget.
     */
    function __construct($_pluginDir)
    {
        add_action( 'wp_dashboard_setup', array($this, 'registerDashboardWidget'));
        add_action( 'admin_menu', array($this, 'registerSettingsPage' ));

        //Installer
        $this->relativePluginPath = WP_CONTENT_DIR.'/plugins/debug-log-parser/debug-log-parser.php';
        register_activation_hook( $this->relativePluginPath, array($this, "install"));

        $this->settings = new Settings();

        $this->logParser = new LogParser($this->settings);

        $this->dashboardWidget = new DashboardWidget($this->logParser, $this->settings);
        $this->settingsPage = new SettingsPage($this->settings);
    }

    /**
     * Registers the settings-page.
     */
    function registerSettingsPage()
    {
        add_menu_page($this->name.' Plugin Settings',$this->name,'administrator',__FILE__, array($this->settingsPage,'settingsPage'));
    }

    /**
     * Registers the dashboard-widget.
     */
    function registerDashboardWidget()
    {
        wp_add_dashboard_widget($this->id, $this->name, array($this->dashboardWidget, 'dashboardWidget'));
    }

    /**
     * Shows the register Message (For further versions)
     */
    function showRegisterMessage()
    {
        $message = __("May ".$this->name." register this Wordpress? ");
    }

    /**
     * The installer, when the plugin is activated for the first time.
     * define('WP_DEBUG', true);
     * define('WP_DEBUG_LOG', true);
     * define('WP_DEBUG_DISPLAY', false);
     * @ini_set('display_errors',0);
     */
    function install()
    {
        $rootPath = WP_CONTENT_DIR."/../";
        $wpConfigFilePath = $rootPath."/wp-config.php";
        $wpConfig = file_get_contents($wpConfigFilePath);

        if (strpos($wpConfig, "define('WP_DEBUG', true);")===false)
        {
            $wpConfig .= "\n".
                        "//Created by Plugin Debug Log Parser".
                        "\n".
                        "define('WP_DEBUG', true);";
        }

        if (strpos($wpConfig, "define('WP_DEBUG_LOG', true);")===false)
        {
            $wpConfig .= "\n".
                "//Created by Plugin Debug Log Parser".
                "\n".
                "define('WP_DEBUG_LOG', true);";
        }

        if (strpos($wpConfig, "define('WP_DEBUG_DISPLAY', false);")===false)
        {
            $wpConfig .= "\n".
                "//Created by Plugin Debug Log Parser".
                "\n".
                "define('WP_DEBUG_DISPLAY', false);";
        }

        if (strpos($wpConfig, "@ini_set('display_errors',0);")===false)
        {
            $wpConfig .= "\n".
                "//Created by Plugin Debug Log Parser".
                "\n".
                "@ini_set('display_errors',0);";
        }

        return file_put_contents($wpConfigFilePath, $wpConfig);
    }
}



