var Innocow = Innocow || {};
Innocow.WP = Innocow.WP || {};
Innocow.WP.PluginOrganiser = Innocow.WP.PluginOrganiser || {};

Innocow.WP.PluginOrganiser.GroupsForm = class GroupsForm {

    constructor( container ) {

        this.container = container || document;
        this.translations = {};

        this.pluginsInstalled = [];

    }

    get translations() {
        return this._translations;
    }

    set translations( t ) {
        this._translations = typeof( t ) === "object" ? t : [];
    }

    _translate( text ) {

        if ( typeof( text ) === "string" ) {
            let textLower = text.toLowerCase();
            return this.translations.hasOwnProperty( textLower ) ? this.translations[textLower] : text;
        }
        
        return text;

    }

    getForm() {

        let rawHTML = `
                      <input type="hidden" name="id" value="0">
                      <fieldset class="plugin-group">
                        <div class="flex-row">
                          <div class="flex-column flex-column-1">
                            <span class="form-control-wrapper">
                              <input id="name" class="name" type="text" name="name"
                                     placeholder="${this._translate('name')}" required>
                            </span>
                          </div>
                        </div>
                        <div class="flex-row">
                          <div class="flex-column flex-column-1">
                            <span class="form-control-wrapper">
                              <select class="plugins-installed" name="plugins_installed">
                              </select>
                            </span>
                          </div>
                          <div class="flex-column">
                            <span class="form-control-wrapper">
                              <button type="button" class="button" name="add_plugin">
                                ${this._translate('add_plugin')}
                              </button>
                            </span>
                          </div>
                        </div>
                        <div class="flex-row">
                          <div class="flex-column flex-column-1">
                            <span class="form-control-wrapper">
                              <select class="plugins-selected" name="plugins_selected[]"
                                      style="max-width:unset" multiple>
                              </select>
                            </span>
                          </div>
                          <div class="flex-column">
                            <span class="form-control-wrapper">
                              <button type="button" class="button" name="del_plugin">
                                ${this._translate('delete_plugin')}
                              </button>
                            </span>
                          </div>                        
                        </div>
                        <div class="flex-row">
                          <div class="flex-column-1">
                            <span class="form-control-wrapper">
                              <button type="button" class="button" name="del_group">
                                ${this._translate('delete_group')}
                              </button>
                              <button type="button" class="button button-primary" name="save_group">
                                ${this._translate('save_group')}
                              </button>
                            </span>
                          </div>
                        </div>
                      </fieldset>`;

        let form = document.createElement( "form" );
        form.innerHTML = rawHTML;

        return form;

    }

    populateInstalledPluginsSelect( select ) {

        Array.from( this.pluginsInstalled ).map( plugin => {

            if ( ! plugin.hasOwnProperty( "name" ) || ! plugin.hasOwnProperty( "file" ) ) {
                throw new Error( "Unrecognised installed plugin list." );
            }

            let option = document.createElement( "option" );
            option.innerHTML = plugin.name;
            option.value = plugin.file;

            select.append( option );

        } );

    }

    addPluginToGroup( selectSource, selectTarget ) {

        let selectedOption = selectSource.options[selectSource.selectedIndex];
        let boolTests = [];

        if ( selectedOption ) {
            // Uses map() to return an array of booleans if the .value exists in the select tag.
            boolTests = Array.from( selectTarget.options ).map( 
                option => option.value === selectedOption.value 
            );

            // Uses filter() to sum booleans into a single number. If the value is greater than 0,
            // then the source value already exists.
            if ( boolTests.filter( Boolean ).length === 0 ) {
                selectTarget.append( selectedOption.cloneNode( true ) );
            }
        }
        
    }

    removeSelectedPlugins( select ) {
        Array.from( select.selectedOptions ).map( option => {
            select.remove( option.index );
        } );
    }

    markAllSelect( select ) {
        Array.from( select ).map( option => option.selected = true );
    }

    addNew() {

        let container = document.createElement( "div" );
        let form = this.getForm();

        this.populateInstalledPluginsSelect( form.querySelector( "select.plugins-installed" ) );

        this.container.append( form );

        return form;

    }

    addSaved( group ) {

        let form = this.addNew( group.id );

        form.elements["id"].value = group.id;
        form.elements["name"].value = group.name;

        Array.from( group.plugins ).map( file => {

            form.elements["plugins_installed"].value = file;
            this.addPluginToGroup(
              form.elements["plugins_installed"],
              form.elements["plugins_selected[]"]
            );

        } );

        form.elements["plugins_installed"].selectedIndex = 0;

    }

}
