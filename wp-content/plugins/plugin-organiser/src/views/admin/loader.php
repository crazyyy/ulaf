<?php

/**
 * Class Loader | src/views/admin/loader.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Plugin_Organiser\Views\Admin;

use Innocow\Plugin_Organiser\Views\Admin\Pages\Settings;

class Loader {

    public static function js_init() {

        $Plugin = $GLOBALS["ICWPPO"];

        $rest_namespace = $Plugin->get_rest_namespace();
        $rest_urlroot =  esc_url_raw( rest_url() );
        $rest_nonce = wp_create_nonce( 'wp_rest' );

        $session_user_id = intval( get_current_user_id() );
        $session_user_email = ( get_userdata( get_current_user_id() ) )->get( "user_email" );
        $plugin_slug = $Plugin->get_plugin_slug();
        $url_admin = admin_url( "admin.php" );
        $locale = get_locale();
        
        $tz_offset = intval( get_option( "gmt_offset" ) );
        $tz_string = get_option( "timezone_string" );

        if ( $tz_string == "" && $tz_offset == 0 ) {
            $tz_string = "UTC";
        }


        $js = <<< HTML

<script type="text/javascript">
    
    var Innocow = Innocow || {};

    Innocow.WP = class WP {

        constructor() {

            this.urlAdmin = "$url_admin";

            this.locale = {
                withUnderscore: "$locale",
                withHyphen: "$locale".replace('_','-'),
                language: "$locale".split('_')[0],
                region: "$locale".split('_')[1],
            }

            this.timezone = {
                offsetHours: $tz_offset,
                offsetMinutes: ($tz_offset * 60),
                string: "$tz_string",
            }

            this.session = {
                userId: $session_user_id,
                userEmail: "$session_user_email",
            }

            this.plugin = {
                slug: "$plugin_slug",
            }

            this.rest = {
                nonce: "$rest_nonce",
                namespace: "$rest_namespace",
                url: "$rest_urlroot",
            }

        }

        createPluginUrl() {
            let params = new URLSearchParams( { "page": this.plugin.slug } );
            return this.urlAdmin + "?" + params.toString();
        }

    };

</script>
HTML;

        return $js;

    }

    public static function html_title() {

        $Plugin = $GLOBALS["ICWPPO"];
        $tr_key = $Plugin->get_translation_key();

        $title = __( "Plugin Organiser", $tr_key );

        $html_title = "<h1 class='wp-heading-inline'>$title</h1>";

        return $html_title;

    }

    public static function html_nav() {
        return "";
    }

    public static function load( $page=null ) {

        // Initialise our JS namespace and helper properties.
        echo self::js_init();

        $Plugin = $GLOBALS["ICWPPO"];

        switch( $page ) {

            case "settings":
            default:
                $SettingsPage = new Settings();
                echo $SettingsPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

            case "about":
                $AboutPage = new About();
                echo $AboutPage->html( [
                    "title" => self::html_title(),
                    "nav" => self::html_nav()
                ] );
                break;

        }

    }

}