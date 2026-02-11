<?php

namespace Avram\WPDebugBar;

use Avram\WPDebugBar\Collectors\TimeLineCollector;
use DebugBar\DebugBar;
use Avram\WPDebugBar\Collectors\ConfigCollector;
use Avram\WPDebugBar\Collectors\ConstantCollector;
use Avram\WPDebugBar\Collectors\HookCollector;
use Avram\WPDebugBar\Collectors\LogCollector;
use Avram\WPDebugBar\Collectors\SimpleQueryCollector;
use Avram\WPDebugBar\Collectors\LoadedFilesCollector;
use Avram\WPDebugBar\Collectors\QueryCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;

class WPDebugBar extends DebugBar
{
    protected static $instance = false;

    protected $renderer;

    private function __construct()
    {
        $this->renderer = $this->getJavascriptRenderer();
        $this->renderer->setOptions(array('base_url' => plugins_url('vendor/maximebf/debugbar/src/DebugBar/Resources/', ULTIMATE_DEBUGBAR_PLUGIN_FILE)));
        $this->renderer->setIncludeVendors(true);

        add_action('init', [$this, 'handleAjax']);
        add_action('admin_init', [$this, 'handleAjax']);

        add_action('wp_head', [$this, 'renderHead']);
        add_action('admin_head', [$this, 'renderHead']);

        add_action('wp_footer', [$this, 'renderDebugBar']);
        add_action('admin_footer', [$this, 'renderDebugBar']);

        add_action('wp_enqueue_scripts', [$this, 'addRendererAssets']);
        add_action('admin_enqueue_scripts', [$this, 'addRendererAssets']);

        add_filter('plugin_action_links_'.plugin_basename(ULTIMATE_DEBUGBAR_PLUGIN_FILE), [$this, 'addPluginLinks']);

        $wpLogFile = WP_CONTENT_DIR.'/debug.log';

        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MemoryCollector());
        !wp_doing_ajax() && $this->addCollector(new ConstantCollector());
        !wp_doing_ajax() && $this->addCollector(new ConfigCollector());
        $this->addCollector(new RequestDataCollector());
        !wp_doing_ajax() && $this->addCollector(new TimeLineCollector());
        $this->addCollector(new HookCollector());
        $this->addCollector(SAVEQUERIES ? new QueryCollector('Database') : new SimpleQueryCollector('Database'));
        !wp_doing_ajax() && $wpLogFile && is_file($wpLogFile) && $this->addCollector(new LogCollector($wpLogFile));
        !wp_doing_ajax() && $this->addCollector(new LoadedFilesCollector('Files'));
    }


    public static function run()
    {
        if (!static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function shouldDisplayBar()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return true;
        }

        return get_current_user_id() && (current_user_can('administrator') || is_super_admin(get_current_user_id()));
    }

    public function handleAjax()
    {
        if (wp_doing_ajax() && ULTIMATE_DEBUG_AJAX && $this->shouldDisplayBar()) {
            $this->sendDataInHeaders(null, 'phpdebugbar', PHP_INT_MAX);
        }
    }

    public function renderHead()
    {
        if ($this->shouldDisplayBar()) {
            echo $this->renderer->renderHead();
        }
    }

    public function renderDebugBar()
    {
        if ($this->shouldDisplayBar()) {
            echo $this->renderer->render();
        }
    }

    public function addRendererAssets()
    {
        wp_enqueue_style('ultimate_debugbar_query_css', plugins_url('vendor/maximebf/debugbar/src/DebugBar/Resources/widgets/sqlqueries/widget.css', ULTIMATE_DEBUGBAR_PLUGIN_FILE));
        wp_enqueue_style('ultimate_debugbar_wp_icon_css', plugins_url('ultimate-debugbar.css', ULTIMATE_DEBUGBAR_PLUGIN_FILE));
        wp_enqueue_script('ultimate_debugbar_query_js', plugins_url('vendor/maximebf/debugbar/src/DebugBar/Resources/widgets/sqlqueries/widget.js', ULTIMATE_DEBUGBAR_PLUGIN_FILE), [], false, true);
    }

    public function addPluginLinks($links)
    {
        $links[] = '<a href="'.
            'https://paypal.me/avramator'.
            '" target="_blank">'.__('Donate!').'</a>';
        return $links;
    }


}