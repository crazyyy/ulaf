<?php

/**
 * Class Plugins | src/services/plugins.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Plugin_Organiser\Services;

class Plugins {

    public function get_plugins() {

        $plugins = get_plugins();
        $filtered = [];

        foreach( $plugins as $plugin => $details ) {

            $filters[] = [
                "name" => $details["Name"],
                "file" => $plugin,
                "slug" => dirname( $plugin ),
            ];

        }

        return $filters;

    }

    public function get_plugins_as_json() {
        return json_encode( $this->get_plugins() );
    }



}

