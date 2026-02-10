var Innocow = Innocow || {};
Innocow.WP = Innocow.WP || {};
Innocow.WP.PluginOrganiser = Innocow.WP.PluginOrganiser || {};

Innocow.WP.PluginOrganiser.HttpRestGroups = class HttpRestGroups 
extends Innocow.WP.HttpRest {

    constructor( urlBase, urlPathPlugin, nonce ) {

        super( urlBase, urlPathPlugin, nonce );

        this.endpoints = {
            groups: "/groups"
        }

    }

    async postGroup( formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( this.getURL( "groups" ), parameters, "POST" );

    }

    async deleteGroup( formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( this.getURL( "groups" ), parameters, "DELETE" );

    }

}