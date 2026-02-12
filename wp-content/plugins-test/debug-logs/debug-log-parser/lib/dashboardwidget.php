<?php


/**
 * Displays the dashboard-widget.
 */
class DashboardWidget
{
    private $logParser;
    private $maxDashboardLogsLength = 5;
    private $debug;

    /**
     * @var array Defines the standard-colours of php-errors.
     */
    private $errorLevelColours = array();

    /**
     * @param LogParser $_logParser
     * @param Settings $_settings
     */
    function __construct(LogParser $_logParser, Settings $_settings)
    {
        $this->logParser = $_logParser;

        //For more performance on dashboard
        $this->errorLevelColours = $_settings->errorLevelColours;
        $this->debug = $_settings->debug;
        $this->maxDashboardLogsLength = $_settings->maxDashboardLogsLength;
    }

    /**
     * Draws the cycles.
     *
     * @param int $number The count of the errors.
     * @param string $colour The hex-code for the colour.
     * @return string Html 5 code of the cycle.
     */
    function drawACycle($number, $colour)
    {
        $cycleSize = 20;
        $cycleRadius = 10;
        $fontSize = 10;

        if ($number<50)
        {

            $cycleSize = $cycleSize + ($number*2);
            $cycleRadius = $cycleRadius + $number;
            $fontSize = $fontSize + $number;
        }
        else
        {

            $cycleSize = $cycleSize + 100;
            $cycleRadius  = $cycleRadius + 50;
            $fontSize = $fontSize + 30;
        }

        $cycle = '<div style="line-height: '.$cycleSize.'px;margin:5px; text-align: center; float:left; box-shadow: 0 1px 1px #ddd; '.
            'width: '.$cycleSize.'px; height: '.$cycleSize.'px; border-radius: '.$cycleRadius.'px;'.
            'font-size: '.$fontSize.'px;'.
            'background-color: '.$colour.'">'.
            '<div>'.$number.'</div>'.
            '</div>';

        return $cycle;
    }

    /**
     * Prints the dashboard-widget
     */
    function dashboardWidget()
    {
        $this->logParser->parseLogFile(true);

        $logs = $this->logParser->getParsedLogs($this->maxDashboardLogsLength);

        if (count($logs)===0)
        {
            echo '<div style="border: 3px #eee dashed; margin:7px;">';
            echo __('No log file found or log file is empty.', 'debuglogparser');
            echo '</div>';
        }
        else
        {
            foreach ($logs as $log)
            {
                $errorNum = $this->logParser->getLengthOfError($log->getLogText());

                if (isset($this->errorLevelColours[$log->getErrorLevel()]))
                {
                    $colour = $this->errorLevelColours[$log->getErrorLevel()];
                }
                else $colour = $this->errorLevelColours["Unknown-error"];

                echo '<div style="border: 3px #eee dashed; margin:7px;">';
                echo $this->drawACycle($errorNum, $colour);
                echo '<div width="100%">'.
                    '<p><strong>'.__('Error', 'debuglogparser').': '.$log->getErrorLevel().'</strong></p>'.
                    '<p>'.$log->getLogText().'</p>'.
                    '</div>';
                echo '<div style="clear:both"></div>';
                echo '</div>';
            }
        }

        echo '<div>'.__('Loading time', 'debuglogparser').': '.$this->logParser->getLoadingTime().'</div>';

        if ($this->debug)
        {
            echo '<div>'.__('Filesize of logfile', 'debuglogparser').': '.$this->logParser->getFileSize().'</div>';
            echo '<div>'.__('Read filesize of logfile', 'debuglogparser').': '.$this->logParser->getFileSizeRead().'</div>';
            echo '<div>'.__('Memory-usage', 'debuglogparser').': '.$this->logParser->getMemoryUsage().'</div>';
        }
    }
} 