<?php

/**
 * Class Settings | src/views/pages/settings.php
 * 
 * @author Innocow
 * @copyright 2020 Innocow
 */

namespace Innocow\Plugin_Organiser\Views\Admin\Pages;

use Innocow\Plugin_Organiser\Services\Plugins;
use Innocow\Plugin_Organiser\Services\WP_Options;

class Settings {

    private $arr_content = [];

    public function __construct() {

        $Plugin = $GLOBALS["ICWPPO"];
        $tr_key = $Plugin->get_translation_key();

        $this->tr["tip"] = __( 
            "Is this plugin useful? Buy us a coffee(s)!", 
            $tr_key 
        );
        $this->tr["save_ok"] = __( 
            "Plugin group has been saved.", 
            $tr_key 
        );
        $this->tr["delete_ok"] = __( 
            "Plugin group has been deleted.", 
            $tr_key 
        );
        $this->tr["error"] = __( 
            "Plugin group update or delete failed.", 
            $tr_key 
        );
        $this->tr["page_error"] = __( 
            "Page failed from unrecognised error.", 
            $tr_key 
        );
        $this->tr["add_group"] = __( "Add Group", $tr_key );
        $this->tr["header"] = __( "Settings", $tr_key );
        $this->tr["translationsJSON"] = json_encode([
            "name" => __( "Name", $tr_key ),
            "add_plugin" => __( "Add Plugin", $tr_key ),
            "delete_plugin" => __( "Delete Plugin", $tr_key ),
            "save_group" => __( "Save Group", $tr_key ),
            "delete_group" => __( "Delete Group", $tr_key ),
        ] );

    }

    private function javascript() {

        $WP_Options = new WP_Options();
        $Plugins = new Plugins();

        $js = <<< JAVASCRIPT

document.addEventListener( "DOMContentLoaded", function( eventDOMContentLoaded ) {

    let savedGroups = {$WP_Options->find_all_groups_as_json()};

    let container = document.getElementById( "groups" );
    let page = new Innocow.WP.Html( container );    
    let groupForms = new Innocow.WP.PluginOrganiser.GroupsForm( container );
    let innocowWP = new Innocow.WP();
    let httpRest = new Innocow.WP.PluginOrganiser.HttpRestGroups( 
        innocowWP.rest.url, 
        innocowWP.rest.namespace, 
        innocowWP.rest.nonce 
    );    

    groupForms.pluginsInstalled = {$Plugins->get_plugins_as_json()};
    groupForms.translations = {$this->tr["translationsJSON"]};

    let deleteGroup = function( form ) {

        httpRest.deleteGroup( form )
        .then( response => httpRest._parseNetworkResponse(
            response,
            function( errorMessage ) { 
                page.displayErrorStatus( "{$this->tr['error']}" );
                throw { isDisplayed: true, message: errorMessage }
            }
        ) )
        .then( responseJSON => {

            if ( responseJSON.isDeleted ) {
                page.displayOkStatus( "{$this->tr['delete_ok']}" );
            } else {
                throw Error( "Unrecognised response from server." );
            }

        } );

        form.parentElement.removeChild( form );


    }

    let saveGroup = function( form ) {

        httpRest.postGroup( form )
        .then( response => httpRest._parseNetworkResponse(
            response,
            function( errorMessage ) { 
                page.displayErrorStatus( "{$this->tr['error']}" );
                throw { isDisplayed: true, message: errorMessage }
            }
        ) )
        .then( responseJSON => {

            if ( responseJSON.isSaved || responseJSON.isUpdated ) {

                page.displayOkStatus( "{$this->tr['save_ok']}")
                
                if ( responseJSON.hasOwnProperty( "id" ) ) {
                    form.elements["id"].value = responseJSON.id;
                }

            } else {
                throw Error( "Unrecognised response from server." );
            }

        } );

    }

    try {

        if ( savedGroups ) {

            Array.from( savedGroups ).map( group => {
                groupForms.addSaved( group );
            } );

        }

        document.addEventListener( "click", evClick => {

            let form;
            let name = evClick.srcElement.name;

            if ( evClick.srcElement.form ) {
                form = evClick.srcElement.form;
            }

            switch( name ) {

                case "add_group":
                    groupForms.addNew();
                    break;

                case "add_plugin":
                    groupForms.addPluginToGroup(
                        form.elements["plugins_installed"],
                        form.elements["plugins_selected[]"]
                    );
                    break;

                case "del_plugin":
                    groupForms.removeSelectedPlugins(
                        form.elements["plugins_selected[]"]
                    );
                    break;

                case "del_group":
                    deleteGroup( form );
                    break;

                case "save_group":
                    groupForms.markAllSelect( form.elements["plugins_selected[]"] );
                    saveGroup( form );
                    break;

                case "go_tip":
                    let a = document.createElement( "a" );
                    a.href = "https://innocow.com/tipjar";
                    a.target = "_blank";
                    a.click();
                    break;

            }

        } );

    } catch ( error ) {

        console.error( error );

        if ( ! error.isDisplayed ) {
            page.displayErrorStatus( "{$this->tr['page_error']}" );
        }

    }

} );

JAVASCRIPT;

        return $js;

    }

    public function html( array $page_elements, $use_echo=false ) {

        $html_nav = isset( $page_elements["nav"] ) ? $page_elements["nav"] : "";
        $html_title = isset( $page_elements["title"] ) ? $page_elements["title"] : "";
        $js = $this->javascript();

        $Plugin = $GLOBALS["ICWPPO"];
        $tr_key = $Plugin->get_translation_key();

        $preamble = __(
'<p>Create your plugin groups here to be filtered on the plugins page.</p>'
,$tr_key );

        $html = <<< HTML

        <div id="settings" class="wrap">

        $html_title

        <hr class="wp-header-end">

        $html_nav

        <h2> {$this->tr['header']} </h2>
        <div class="preamble">$preamble</div>
        <div class="status" style="display:none;"></div>

        <div class="content">

        <p>
        <button class="button button-primary" name="add_group">{$this->tr['add_group']}</button>
        <button id="tip" name='go_tip' class='button'>
            <span style="margin-right:10px">☕</span>{$this->tr["tip"]}
        </button>
        </p>

          <div id="groups">
          </div>

        </div>
        
        </div> <!-- /wrap -->

        <script>$js</script>

HTML;

        if ( ! $use_echo ) {
            return $html;
        } 

        echo $html;

    }

}

