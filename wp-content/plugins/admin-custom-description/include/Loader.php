<?php

namespace AdminCustomDescription;

class Loader
{

    private static $instance = null;

    public function __construct()
    {
        add_action('init', array($this, 'load_textdomain'));
        add_action(
            'admin_enqueue_scripts',
            array($this, 'admin_enqueue_assets')
        );
        add_filter(
            'plugin_row_meta',
            array($this, 'add_custom_description_links_foreach_plugin'),
            9999,
            4
        );
        new Ajax('activate_plugins', 'acd_nonce');
    }

    public static function getInstance(): Loader
    {
        if ( ! self::$instance) {
            self::$instance = new Loader();
        }

        return self::$instance;
    }


    /**
     * Filters the array of row meta for each plugin in the Plugins list table.
     * Appends additional option to add custom description below each plugin on the plugins page.
     *
     * @access  public
     *
     * @param array  $links_array      An array of the plugin's metadata
     * @param string $plugin_file_name Path to the plugin file
     *
     * @return  array       $links_array
     */
    public function add_custom_description_links_foreach_plugin(
        $links_array,
        $plugin_file_name,
        $plugin_data,
        $status
    ) {
        $plugin_name        = explode('/', $plugin_file_name);
        $plugin_name        = $plugin_name[0];
        $custom_description = get_option("acd_{$plugin_name}") ?? '';
        $empty_style        = '';
        $button_text        = __('Edit', 'admin-custom-description');
        if (empty($custom_description)) {
            $empty_style = "style='display:none;'";
            $button_text = __('Add description', 'admin-custom-description');
        }

        $links_array[] = "<span class='display-plugin-comments' onclick='showPluginCustomDescription(this)'>
                            <i class=' dashicons dashicons-info-outline'></i>
                             " . __(
                'Show Description',
                'admin-custom-description'
            ) .
            "</span>
                          <div class='acd-description-wrapper'>
                            <div class='acd-description'  {$empty_style} href='#'>
                            {$custom_description}</div>
                             <div class='acd-edit-button' data-plugin='{$plugin_name}' onclick='editpluginComment(this)' >
                              {$button_text} </div>
                            
                          </div>
                        ";

        return $links_array;
    }


    public static function load_textdomain()
    {
        load_plugin_textdomain(
            'admin-custom-description',
            false,
            'admin-custom-description/languages'
        );
    }

    public function admin_enqueue_assets()

    {
        global $pagenow;

        if (is_admin() && $pagenow === 'plugins.php') {
            wp_enqueue_style(
                'acd-admin',
                plugins_url('assets/css/acd-admin.css', ADMIN_CUSTOM_DESCRIPTION_FILE),
                [],
                filemtime(ADMIN_CUSTOM_DESCRIPTION_DIR . 'assets/css/acd-admin.css')
            );
            wp_enqueue_script(
                'acd-admin',
                plugins_url('assets/js/acd-admin.js', ADMIN_CUSTOM_DESCRIPTION_FILE),
                array('jquery','wp-i18n'),
                filemtime(ADMIN_CUSTOM_DESCRIPTION_DIR . 'assets/js/acd-admin.js'),
                true
            );
            wp_enqueue_script(
                'acd-sweetalert',
                plugins_url('assets/js/sweetalert2.js', ADMIN_CUSTOM_DESCRIPTION_FILE),
                [],
                filemtime(ADMIN_CUSTOM_DESCRIPTION_DIR . 'assets/js/sweetalert2.js'),
                true
            );
            wp_add_inline_script(
                'acd-admin',
                'let acdAjax =
             {ajaxUrl:' . wp_json_encode(admin_url('admin-ajax.php')) . ',
             ajaxNonce:' . wp_json_encode(wp_create_nonce('acd_nonce')) . '}',
                'before'
            );

            wp_set_script_translations(
                'acd-admin',
                'admin-custom-description',
                ADMIN_CUSTOM_DESCRIPTION_DIR . 'languages'
            );
        }
    }

}