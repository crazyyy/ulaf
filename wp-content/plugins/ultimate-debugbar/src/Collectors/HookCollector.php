<?php

namespace Avram\WPDebugBar\Collectors;


use DebugBar\DataCollector\RequestDataCollector;

class HookCollector extends RequestDataCollector
{
    protected $hooks = [];
    protected $name;

    public function __construct($name = 'Hooks')
    {
        $this->name = $name;
        add_action('all', array($this, 'collectHook'));
    }

    public function collect()
    {
        uasort($this->hooks, function ($a, $b) {
            $a = array_sum(array_column($a, 'duration'));
            $b = array_sum(array_column($b, 'duration'));
            if ($a == $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;
        });

        $return = [];

        foreach ($this->hooks as $hook => $data) {
            $totalDuration = array_sum(array_column($data, 'duration'));

            if ($totalDuration * 1000 < 1) {
                continue;
            }

            if (count($data) > 1) {
                $return[$hook] = vsprintf(__('%s spent by %d invocations, min: %s, max: %s, avg: %s'), [
                    $this->getDataFormatter()->formatDuration($totalDuration),
                    count($data),
                    $this->getDataFormatter()->formatDuration(min(array_column($data, 'duration'))),
                    $this->getDataFormatter()->formatDuration(max(array_column($data, 'duration'))),
                    $this->getDataFormatter()->formatDuration($totalDuration / count($data)),
                ]);
            } else {
                $return[$hook] = vsprintf(__('%s spent by %d invocation'), [
                    $this->getDataFormatter()->formatDuration(array_sum(array_column($data, 'duration'))),
                    count($data),
                ]);
            }

        }

        return [
            'data'  => $return,
            'count' => count($return),
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                "icon"    => "cogs",
                "tooltip" => __("Hooks taking over 1ms to execute"),
                "map"     => $this->getName().".data",
                "default" => "{}",
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget'
            ),
            $this->getName().":badge" => array(
                "map"     => $this->getName().".count",
                "default" => count($this->hooks),
            )
        );
    }

    public function collectHook()
    {
        $hook                          = current_filter();
        $this->hooks[$hook][]['start'] = microtime(true);
        add_filter($hook, array($this, 'measureHook'), 99999);
    }

    public function measureHook($return = null)
    {
        $end  = microtime(true);
        $hook = current_filter();
        remove_filter($hook, array($this, 'measureHook'), 99999);
        end($this->hooks[$hook]);
        $index                                  = key($this->hooks[$hook]);
        $this->hooks[$hook][$index]['stop']     = $end;
        $this->hooks[$hook][$index]['duration'] = $end - $this->hooks[$hook][$index]['start'];

        return $return;
    }

}