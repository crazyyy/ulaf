<?php

/**
 * The Log.
 */
class Log
{
    /** @var  DateTime */
    private $date;
    private $logText;
    private $level;

    /**
     * @param String $_date 05-May-2014 20:05:07 UTC;
     * @param string $_logText The error-message
     * @param string $_logLevel The error-level.
     */
    function __construct($_date, $_logText, $_logLevel)
    {
        $timezone = new DateTimeZone(strtoupper(substr($_date,21, 24)));
        $this->date = DateTime::createFromFormat("d-M-Y H:i:s", substr($_date,0,20));
        $this->logText = $_logText;
        $this->level = $_logLevel;
    }

    /**
     * Returns the DateTime of the Log.
     *
     * @return DateTime
     */
    function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    function getLogText()
    {
        return $this->logText;
    }

    /**
     * @return mixed
     */
    function getErrorLevel()
    {
        return $this->level;
    }
} 