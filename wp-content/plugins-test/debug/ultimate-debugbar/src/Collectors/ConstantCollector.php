<?php

namespace Avram\WPDebugBar\Collectors;


use DebugBar\DataCollector\RequestDataCollector;

class ConstantCollector extends RequestDataCollector
{
    public function collect()
    {
        $data = [];
        $vars = get_defined_constants(true)['user'];

        foreach ($vars as $name => $var) {
            if (!$this->contains($name, ['wp', 'wc', 'db_'])) {
                continue;
            }

            if ($name == 'DB_PASSWORD') {
                $data['DB_PASSWORD'] = '(hidden)';
            } else {
                $data[$name] = $var;
            }
        }

        $data['SAVEQUERIES'] = SAVEQUERIES;

        return $data;
    }

    public function getName()
    {
        return 'Constants';
    }

    public function getWidgets()
    {
        return array(
            $this->getName() => array(
                "icon"    => "list",
                "tooltip" => "WP and WC constants",
                "map"     => $this->getName(),
                "default" => "{}",
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget'
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