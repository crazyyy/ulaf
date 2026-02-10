<?php

/**
 * Plugin Name: JS Error Logger Early Loader
 * Description: Early placeholder to enqueue the logging script as early as possible
 * Author: JFG Media
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
//Kind of a hack here
//Since the plugin purpose is to monitor JS errors, we need to do all we can to enqueue its script as early as possible, before as many other js files as possible
//Passing false to the src didn't allow us to rewrite the src via script_loader_src later down the line
//We temporarily enqueue a core WP js file (jquery) to get an early place in the queue
//Then 2 options:
//*Plugin is active on a given page, in which case src is rewritten via script_loader_src
//*Plugin is not active on a given page, in which case, jserrlog is dequeued to prevent jquery from being (re)enqueued

function jserrlog_early_script_enqueue()
{
    wp_enqueue_script('jserrlog', includes_url('js/jquery/jquery.js'),[],null,["in_footer"=>false]);
}
add_action( 'wp_enqueue_scripts', 'jserrlog_early_script_enqueue',1);
add_action( 'admin_enqueue_scripts', 'jserrlog_early_script_enqueue',1);