<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\Renderable;

class LogCollector extends MessagesCollector
{

    /**
     * @var string
     */
    private $logFile;

    /**
     * WPDB_LogCollector constructor.
     *
     * @param string $logFile
     */
    public function __construct($logFile)
    {
        parent::__construct('Debug Log');
        $this->logFile = $logFile;
    }

    public function collect()
    {
        $log = $this->parseLog($this->logFile);
        $log = array_splice($log, -100, 100, true);

        foreach ($log as $line) {
            if (is_numeric($line[3])) {
                $this->addMessage("[{$line[1]}] {$line[3]}{$line[4]}", trim($line[2]));
            } else {
                $this->addMessage("[{$line[1]}] {$line[4]}", trim($line[2]));
            }
        }

        $messages = $this->getMessages();
        return array(
            'count'    => count($messages),
            'messages' => $messages
        );
    }

    protected function parseLog($logfile)
    {
        $log = file_get_contents($logfile);

        preg_match_all('/\[(.*)\](.*?)([:^\d])([^\[]*)/m', $log, $matches, PREG_SET_ORDER, 0);

        return $matches;
    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                'icon'    => 'file-alt',
                "widget"  => "PhpDebugBar.Widgets.MessagesWidget",
                "map"     => $this->getName().".messages",
                "default" => "[]"
            ),
            $this->getName().":badge" => array(
                "map"     => $this->getName().".count",
                "default" => "null"
            )
        );
    }


}