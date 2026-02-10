<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class QueryCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function collect()
    {
        global $wpdb;

        $queries       = array();
        $totalExecTime = 0;
        foreach ($wpdb->queries as $query) {

            $trace = explode(',', $query[2]);
            $trace = array_map('trim', $trace);

            $queries[]     = array(
                'sql'          => $query[0],
                'duration'     => $query[1],
                'duration_str' => $this->getDataFormatter()->formatDuration($query[1]),
                'stmt_id'      => array_pop($trace),
            );
            $totalExecTime += $query[1];
        }
        return array(
            'nb_statements'            => count($queries),
            'accumulated_duration'     => $totalExecTime,
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($totalExecTime),
            'statements'               => $queries
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                "icon"    => "database",
                "widget"  => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map"     => $this->getName(),
                "default" => "[]"
            ),
            $this->getName().":badge" => array(
                "map"     => $this->getName().".nb_statements",
                "default" => 0
            )
        );
    }

    public function getAssets()
    {
        return array(
            'css' => plugins_url('vendor/maximebf/debugbar/src/DebugBar/Resources/widgets/sqlqueries/widget.css', ULTIMATE_DEBUGBAR_PLUGIN_FILE),
            'js'  => plugins_url('vendor/maximebf/debugbar/src/DebugBar/Resources/widgets/sqlqueries/widget.js', ULTIMATE_DEBUGBAR_PLUGIN_FILE),
        );
    }


}