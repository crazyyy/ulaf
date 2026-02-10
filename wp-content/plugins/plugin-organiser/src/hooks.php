<?php

namespace Innocow\Plugin_Organiser;

use Innocow\Plugin_Organiser\Services\WP_Options;
use Innocow\Plugin_Organiser\Views\Admin\Loader;

class Hooks {

    public static function aa_init() {

        $Plugin = $GLOBALS["ICWPPO"];
        $plugin_file_path = $Plugin->get_plugin_file_name_and_path();
        $plugin_slug = $Plugin->get_plugin_slug();

        $arr_plugin_headers = get_file_data( 
            $plugin_file_path, 
            [
                "text_domain" => "Text Domain",
                "domain_path" => "Domain Path" 
            ]
        );

        load_plugin_textdomain(
            $arr_plugin_headers["text_domain"],
            false,
            $plugin_slug . $arr_plugin_headers["domain_path"]
        );
        
    }    

    public static function aa_rest_api_init() {

        $Plugin = $GLOBALS["ICWPPO"];
        $rest_namespace = $Plugin->get_rest_namespace();

        $namespace = __NAMESPACE__;

        register_rest_route(
            $rest_namespace, 
            "groups",
            [
                "methods" => "POST",
                "callback" => 
                    "$namespace\\Rest\\Groups::save",
                
                "permission_callback" => 
                    "$namespace\\Rest\\Groups::is_user_permissible"
                ,
            ]
        );

        register_rest_route(
            $rest_namespace, 
            "groups",
            [
                "methods" => "DELETE",
                "callback" => 
                    "$namespace\\Rest\\Groups::delete",
                
                "permission_callback" => 
                    "$namespace\\Rest\\Groups::is_user_permissible"
                ,
            ]
        );
    }    

    public static function aa_admin_enqueue_scripts() {

        $Plugin = $GLOBALS["ICWPPO"];
        $plugin_code = $Plugin->get_plugin_code();
        $plugin_file_path = $Plugin->get_plugin_file_name_and_path();

        $arr_js = [
            [
                "id" => "http-rest",
                "file" => "/views/admin/js/http-rest.js"
            ],
            [
                "id" => "html",
                "file" => "/views/admin/js/html.js"
            ],            
            [
                "id" => "pluginorganiser-http-rest-groups",
                "file" => "/views/admin/js/pluginorganiser/http-rest-groups.js"
            ],            
            [
                "id" => "pluginorganiser-filter",
                "file" => "/views/admin/js/pluginorganiser/filter.js"
            ],
            [
                "id" => "pluginorganiser-groups-form",
                "file" => "/views/admin/js/pluginorganiser/groups-form.js"
            ],
        ];

        $arr_css = [
            [
                "id" => "admin",
                "file" => "/views/admin/css/admin.css"
            ],
            [
                "id" => "form",
                "file" => "/views/admin/css/form.css"
            ],
        ];


        foreach( $arr_js as $js ) {

            $js_ver = filemtime( __DIR__ . $js["file"] );
            $js_deps = isset( $js["deps"] ) ? $js["deps"] : [];
            wp_enqueue_script(
                $plugin_code . "-" . $js["id"],
                plugins_url( "/src" . $js["file"], $plugin_file_path ),
                $js_deps,
                $js_ver,
                true // load in footer
            );

        }

        foreach ( $arr_css as $css ) {

            $css_ver = filemtime( __DIR__ . $css["file"] );
            $css_deps = isset( $css["deps"] ) ? $css["deps"] : [];
            wp_enqueue_style( 
                $plugin_code . "-" . $css["id"],
                plugins_url( "/src" . $css["file"], $plugin_file_path ),
                null,
                $css_ver,
                "all" // media attribute for <link>.
            );

        }        

    }

    public static function aa_admin_menu() {

        $Plugin = $GLOBALS["ICWPPO"];
        $plugin_slug = $Plugin->get_plugin_slug();
        $tr_key = $Plugin->get_translation_key();

        add_submenu_page( 
            "options-general.php",
            __( "Plugin Organiser", $tr_key ), 
            __( "Plugin Organiser", $tr_key ), 
            "administrator",
            $plugin_slug . "-settings",
            function() { Loader::load( "settings" ); }
        );

    }

    public static function aa_pre_current_active_plugins() {

        $Plugin = $GLOBALS["ICWPPO"];
        $tr_key = $Plugin->get_translation_key();        

        $WP_Options = new WP_Options();

        $txt_select_prefix = __( "Plugin Group", $tr_key );
        $txt_filtered = __( "filtered", $tr_key );
        $txt_label_all = __( "All", $tr_key );
        $txt_label_active = __( "Active", $tr_key );
        $txt_label_inactive = __( "Inactive", $tr_key );
        $txt_label_au_enabled = __( "Auto-Update Enabled", $tr_key );
        $txt_label_au_disabled = __( "Auto-Update Disabled", $tr_key );

        $js = <<< JAVASCRIPT

        document.addEventListener( "DOMContentLoaded", eventDCL => {

            
            let filter = new Innocow.WP.PluginOrganiser.Filter();
            filter.filtersCustom = {$WP_Options->find_all_groups_as_json()};
            filter.options.textFiltered = "$txt_filtered";
            filter.options.textSelectPrefix = "$txt_select_prefix";
            filter.filtersDefault = {
                all: "$txt_label_all",
                active: "$txt_label_active",
                inactive: "$txt_label_inactive",
                autoUpdateEnabled: "$txt_label_au_enabled",
                autoUpdateDisabled: "$txt_label_au_disabled"
            };
            filter.loadForm();
            
            document.getElementById( "plugin-organiser-filter" )
            .addEventListener( "change", eventC => {
                filter.filter( eventC.srcElement.value );
            } );


        } );

JAVASCRIPT;

        echo "<script>$js</script>";

    }

}