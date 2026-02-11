<?php


/**
 * A wrapper for the settings
 */
class Settings
{
    public $errorLevelColours = array(
        "PHP Strict Standards" => "#F7FE2E",
        "PHP Warning" => "#FF8000",
        "PHP Notice" => "#FF8000",
        "PHP Error" => "#FF0000",
        "PHP Fatal error" => "#FF0000",
        "PHP Stack trace" => "#FF0000",
        "PHP Parse error" => "#FF0000",
        "Unknown-error" => "#58ACFA" //For unknown error-types.
    );

    public $maxDashboardLogsLength = 5;
    public $debugLogFilePath = null;
    public $memoryLimit = 8;
    public $debug = false;

    function __construct($_settings = null)
    {
        $this->debugLogFilePath = WP_CONTENT_DIR."/debug.log";

        if (get_option("maxDashboardLogsLength")) $this->maxDashboardLogsLength = get_option("maxDashboardLogsLength");
        if (get_option("debugLogFilePath")) $this->debugLogFilePath = get_option("debugLogFilePath");
        if (get_option("memoryLimit")) $this->memoryLimit = get_option("memoryLimit");
        if (get_option("debug")) $this->debug = get_option("debug");

        if (get_option("errorLevelColours"))
        {
            foreach (json_decode(get_option("errorLevelColours"), true) as $errorLevel => $colour)
            {
                $this->errorLevelColours[$errorLevel] = $colour;
            }
        }
    }

    /**
     * Save the settings in wordpress options.
     */
    function saveSettings()
    {
        update_option("maxDashboardLogsLength", $this->maxDashboardLogsLength);
        update_option("debugLogFilePath", $this->debugLogFilePath);
        update_option("memoryLimit", $this->memoryLimit);
        update_option("debug", $this->debug);
        update_option("errorLevelColours", json_encode($this->errorLevelColours));
    }

} 