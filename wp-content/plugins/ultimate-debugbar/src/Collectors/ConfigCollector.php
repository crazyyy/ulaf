<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\RequestDataCollector;

class ConfigCollector extends RequestDataCollector
{
    protected $options;

    public function collect()
    {
        $data = [];
        $vars = wp_load_alloptions();

        foreach ($vars as $name => $var) {

            if ($var == 'a:0:{}') {
                $data[$name] = 'array:0 [ ]';
            } elseif (strpos($var, 'a:') === 0 || strpos($var, 'O:') === 0) {
                $test = unserialize($var);
                if ($test) {
                    $data[$name] = $this->getDataFormatter()->formatVar((array)$test);
                } else {
                    $data[$name] = $var;
                }
            } elseif ($var === '') {
                $data[$name] = '(empty)';
            } else {
                $test = json_decode($var, true);
                if ($test) {
                    $data[$name] = $this->getDataFormatter()->formatVar($test);
                } else {
                    $data[$name] = $var;
                }
            }
        }

        return [
            'data'  => $data,
            'count' => count($data),
        ];
    }

    public function getName()
    {
        return 'Options';
    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                "icon"    => "cogs",
                "tooltip" => "WP and WC constants",
                "map"     => $this->getName().".data",
                "default" => "{}",
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget'
            ),
            $this->getName().":badge" => array(
                "map"     => $this->getName().".count",
                "default" => 0,
            )
        );
    }

    protected function contains($haystack, $needles)
    {
        $needles = is_array($needles) ? $needles : [$needles];

        foreach ($needles as $needle) {
            if (stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

}