<?php
/*
Plugin Name: Debug Log Parser
Description: Is a tool to parse the wordpress debug log file and summarize errors.
Version: 1.0
Author: Patrick Hausmann
Min WP Version: 3.8
Text Domain: debuglogparser
Domain Path: /lang
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

$pluginDir = plugin_dir_path(__FILE__);

//Load libraries
foreach (glob($pluginDir."/lib/*.php") as $filename)
{
    require_once $filename;
}

//Start Application
$debugLogParserApp = new DebugLogParser($pluginDir);

load_plugin_textdomain( 'debuglogparser', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
