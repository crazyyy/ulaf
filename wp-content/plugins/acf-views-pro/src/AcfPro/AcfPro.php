<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfPro;

use org\wplake\acf_views\Plugin;

class AcfPro
{
    protected Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function setHooks(): void
    {
        // only at this hook, to make sure ACF is loaded
        add_action('plugins_loaded', function () {
            // skip if 'ACF' is not available (both versions) or 'ACF PRO' is available
            // so only executed when 'ACF' (not PRO) is available
            if (! $this->plugin->isAcfPluginAvailable() ||
                $this->plugin->isAcfPluginAvailable(true)) {
                return;
            }


            add_action('init', array($this, 'register_assets'));
            add_action('acf/include_field_types', array($this, 'include_field_types'), 5);
            add_action('acf/input/admin_enqueue_scripts', array($this, 'input_admin_enqueue_scripts'));
        });
    }

    public function include_field_types(): void
    {
        include_once __DIR__ . '/class-acf-field-clone.php';
        include_once __DIR__ . '/class-acf-field-repeater.php';
    }

    public function register_assets(): void
    {
        // register scripts
        wp_register_script(
            'acf-pro-input',
            $this->plugin->getAcfProAssetsUrl('acf-pro-input.min.js'),
            array('acf-input'),
            $this->plugin->getVersion()
        );

        // register styles
        wp_register_style(
            'acf-pro-input',
            $this->plugin->getAcfProAssetsUrl('acf-pro-input.min.css'),
            array('acf-input'),
            $this->plugin->getVersion()
        );
    }

    public function input_admin_enqueue_scripts(): void
    {
        wp_enqueue_script('acf-pro-input');
        wp_enqueue_style('acf-pro-input');
    }
}

