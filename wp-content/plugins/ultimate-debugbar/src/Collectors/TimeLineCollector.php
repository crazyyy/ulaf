<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;

class TimeLineCollector extends TimeDataCollector
{
    /**
     * TimeLineCollector constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->startMeasure('plugins_loaded', 'Loading plugins');
        $this->addMeasure('Initializing WP core', $this->getRequestStartTime(), microtime(true));

        add_action('plugins_loaded', function () {
            $this->hasStartedMeasure('plugins_loaded') && $this->stopMeasure('plugins_loaded');
            $this->startMeasure('pre_theme', 'Initializing plugins');
        });

        add_action('setup_theme', function () {
            $this->hasStartedMeasure('pre_theme') && $this->stopMeasure('pre_theme');
            $this->startMeasure('setup_theme', 'Loading theme');
        });


        add_action('after_setup_theme', function () {
            $this->hasStartedMeasure('setup_theme') && $this->stopMeasure('setup_theme');
            $this->startMeasure('wp_loaded', 'Preparing data to render');
        });


        add_action('wp_loaded', function () {
            $this->hasStartedMeasure('wp_loaded') && $this->stopMeasure('wp_loaded');
            $this->startMeasure('render', 'Rendering');
        });


        add_action('loop_start', function () {
            $this->hasStartedMeasure('get_header') && $this->stopMeasure('get_header');
            !is_admin() && $this->startMeasure('loop_end', 'WP Loop');
        });


        add_action('loop_end', function () {
            !is_admin() && $this->hasStartedMeasure('loop_end') && $this->stopMeasure('loop_end');
        });


        add_action('get_header', function () {
            $this->startMeasure('get_header', 'Template header');
        });


        add_action('get_sidebar', function () {
            $this->hasStartedMeasure('get_header') && $this->stopMeasure('get_header');
            $this->startMeasure('get_sidebar', 'Template sidebar');
        });

        add_action('get_footer', function () {
            $this->hasStartedMeasure('get_sidebar') && $this->stopMeasure('get_sidebar');
            $this->hasStartedMeasure('get_header') && $this->stopMeasure('get_header');
            $this->startMeasure('wp_print_footer_scripts', 'Template footer');
        });

        add_action('wp_print_footer_scripts', function () {
            $this->hasStartedMeasure('wp_print_footer_scripts') && $this->stopMeasure('wp_print_footer_scripts');
            $this->hasStartedMeasure('render') && $this->stopMeasure('render');
        });
    }




}