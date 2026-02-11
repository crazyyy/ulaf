<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\Renderable;

class SimpleQueryCollector extends MessagesCollector
{
    /**
     * SimpleQueryCollector constructor.
     *
     * @param string $name
     */
    public function __construct($name = 'Database')
    {
        parent::__construct($name);
        add_action('query', [$this, 'collectQuery']);
    }

    public function collectQuery($sql)
    {
        $this->addMessage($sql, __('unknown execution time'));
        return $sql;
    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                'icon'    => 'database',
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