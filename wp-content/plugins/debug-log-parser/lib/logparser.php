<?php


/**
 * Class LogParser
 */
class LogParser
{
    private $debugLogPath;
    private $loadDuration = 0;
    private $memoryUsage = 0;
    private $seekOffset;
    private $bytesRead;
    private $fileSize;
    private $logLevels = array();

    /** @var Log[] $logs */
    private $logs = array();

    function __construct(Settings $_settings)
    {
        $this->debugLogPath = $_settings->debugLogFilePath;
        $this->seekOffset = $_settings->memoryLimit*1024*1024;

        foreach ($_settings->errorLevelColours as $logLevels => $colour)
        {
            $this->logLevels[] = $logLevels;
        }

    }

    /**
     *  Returns the microtime.
     *
     * @return float
     */
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Parses the log-file.
     */
    function parseLogFile($_dashboard)
    {
        $this->loadDuration = $this->microtime_float();
        $this->memoryUsage = memory_get_usage();
        $bytesRead = 0;

        if (file_exists($this->debugLogPath) && is_admin())
        {
            $handle = fopen($this->debugLogPath, "r");

            if ($handle)
            {
                if ($_dashboard)
                {
                    //for more performance on dashboard
                    //$this->seekOffset = $this->seekOffset/4;
                }

                fseek($handle, ((-1)*$this->seekOffset), SEEK_END);

                while (($logLine = fgets($handle)) !== false)
                {
                    if (strpos($logLine, "[")===0)
                    {
                        $date = substr($logLine, 1, 24);
                        $logText = substr($logLine, 26);

                        $logLevel ="Unknown-error";

                        foreach ($this->logLevels as $_logLevel)
                        {
                            if (strpos($logText, $_logLevel)!==false)
                            {
                                $logLevel = $_logLevel;
                                break;
                            }
                        }

                        $logMessage =  substr($logText, strpos($logText, $logLevel)+2);

                        $this->logs[] = new Log($date, $logMessage, $logLevel);
                    }
                    $bytesRead = $bytesRead + strlen($logLine);
                }
                fclose($handle);
                $this->logs = array_reverse($this->logs);
            }
            $this->loadDuration = $this->microtime_float()-$this->loadDuration;
            $this->memoryUsage = memory_get_usage()-$this->memoryUsage;
            $this->bytesRead = $bytesRead;
            $this->fileSize = filesize($this->debugLogPath);

        }
    }

    /**
     * Returns the loading time for the duration of parsing the log file.
     *
     * @return string The Loading time in seconds.
     */
    function getLoadingTime()
    {
        return round($this->loadDuration,1)." Seconds";
    }

    /**
     * Returns the memory-usage of Debug Log Parser of parsing the log file.
     *
     * @return string The memory-usage in MB.
     */
    function getMemoryUsage()
    {
        return round($this->memoryUsage/1024/1024, 2)." MB";
    }

    /**
     * Returns the filesize of the logfile.
     *
     * @return string The Filesize in MB.
     */
    function getFileSize()
    {
        return round($this->fileSize/1024/1024,2)." MB";
    }

    /**
     * Returns the read filesize of the logfile.
     *
     * @return string The read filesize of the logfile in MB.
     */
    function getFileSizeRead()
    {
        return round($this->bytesRead/1024/1024,2)." MB";
    }

    /**
     * Returns the parsed logs.
     *
     * @param int $maxLength Maximum of returning logs.
     * @return Log[] The Logs.
     */
    function getParsedLogs($maxLength = 5)
    {
        $logs = array();
        foreach ($this->logs as $log)
        {
            if (count($logs)==$maxLength)
            {
                if (isset($logs[$log->getLogText()]))
                {
                    $logs[$log->getLogText()] = $log;
                }
            }
            else
            {
                $logs[$log->getLogText()]= $log;
            }
        }
        return $logs;
    }

    /**
     * How many Logs have the same message?
     *
     * @param string $_error The errormessage.
     * @return int The counted logs with the same error.
     */
    function getLengthOfError($_error)
    {
        $count = 0;
        foreach ($this->logs as $log)
        {
            if ($log->getLogText() == $_error)
            {
                $count++;
            }
        }

        return $count;
    }
} 