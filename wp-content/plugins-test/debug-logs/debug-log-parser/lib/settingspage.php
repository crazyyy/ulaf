<?php

/**
 * Class SettingsPage
 */
class SettingsPage
{
    /** @var Settings  */
    private $settings;

    /**
     * @param Settings $_settings
     */
    function __construct(Settings $_settings)
    {
        if (isset($_POST['save-debug-log-parser-options']))
        {

            $_settings->debugLogFilePath = $_POST["debugLogFilePath"];
            $_settings->debug = $_POST["debug"];

            foreach ($_POST["errorLevelColours"] as $errorLevel => $colour)
            {
                $_settings->errorLevelColours->$errorLevel = $colour;
            }

            $_settings->memoryLimit = $_POST["memoryLimit"];
            $_settings->maxDashboardLogsLength = $_POST["maxDashboardLogsLength"];

            $_settings->saveSettings();
        }

        if (isset($_POST['delete-debug-log']))
        {

            rename($_settings->debugLogFilePath,  substr($_settings->debugLogFilePath,0,-3).date("Y-m-d H.i.s ", time()).".log");
        }

        $this->settings = $_settings;
    }

    /**
     * Prints the settings-page.
     *
     */
    function settingsPage()
    {
        echo '<div class="wrap" id="debug-log-parser-settings">';

        echo '<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">';

        echo "<h2>".__("General", 'debuglogparser')."</h2>";

        echo '<table class="form-table">';

        echo '<tr>
                <td><label for="maxDashboardLogsLength">'.__("Numbers of errors to display in dashboard:", 'debuglogparser').'</label></td>
                <td><input name="maxDashboardLogsLength" size="1" id="maxDashboardLogsLength" type="text" value="'.$this->settings->maxDashboardLogsLength.'" /></td>
              </tr>';

        echo '<tr>
                <td><label for="debugLogFilePath">'.__("Filepath to logfile:", 'debuglogparser').'</label></td>
                <td><input name="debugLogFilePath" size="70" id="debugLogFilePath" type="text" value="'.$this->settings->debugLogFilePath.'" /></td>
             </tr>';

        echo '<tr>
                <td><label for="memoryLimit">'.__("Maximum Megabytes to read of the logfile.", 'debuglogparser').'</label></td>
                <td><input name="memoryLimit" id="memoryLimit" size="1" type="text" value="'.$this->settings->memoryLimit.'" /></td>
              </div>';

        echo '<tr>
                <td><label for="debug">'.__("Activate debug mode:", 'debuglogparser').'</label></td>
                <td><input name="debug" id="debug" type="checkbox" value="true" ';
                if ($this->settings->debug==true) echo 'checked';
        echo'/></td>
              </tr>';

        echo '</table>';

        echo "<h2>".__("Colours of errorlevels", 'debuglogparser').":</h2>";
        echo '<p>'.__("Hex-codes are allowed", 'debuglogparser').'...</p>';
        echo '<table class="form-table">';

            foreach ($this->settings->errorLevelColours as $errorLevel => $colour)
            {
                echo '<tr>
                        <td><label for="'.$errorLevel.'">'.$errorLevel.'</label></td>
                        <td><input name="errorLevelColours['.$errorLevel.']" id="'.$errorLevel.'" type="text" value="'.$colour.'" /></td>
                      </tr>';
            }

        echo '</table>';

        echo '<button class="button button-primary" name="save-debug-log-parser-options">'.__("Save", 'debuglogparser').'</button>
              <button class="button button-primary" name="delete-debug-log">'.__("Clear logfile.", 'debuglogparser').'</button>
            </form>
        </div>';
    }
} 