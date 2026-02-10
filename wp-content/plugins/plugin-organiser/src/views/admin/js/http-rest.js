var Innocow = Innocow || {};
Innocow.WP = Innocow.WP || {};

Innocow.WP.HttpRest = class HttpRest {

    constructor( urlBase, urlPathPlugin, nonce ) {

        if ( urlBase === undefined ) {
            throw new Error( "Undefined first parameter: restBaseUrl" ); 
        }

        if ( urlPathPlugin === undefined ) {
            throw new Error( "Undefined second parameter: urlPathPlugin" ); 
        }

        this.urlBase = urlBase;
        this.urlPathPlugin = urlPathPlugin;
        this.nonce = nonce || undefined;
        this.endpoints = {};

        // In case pathPlugin has a leading "/", remove it.
        if ( this.urlBase.substr( -1 ) === "/" && this.urlPathPlugin.charAt( 0 ) === "/" ) {
            this.urlPathPlugin = this.urlPathPlugin.substr( 1 );
        }

        this.debug = false;

    }

    getURL( endpoint ) {

        if ( this.endpoints.hasOwnProperty( endpoint ) ) {
            return this.urlBase + this.urlPathPlugin + this.endpoints[endpoint];
        }

    }

     _applyParameters( url, parameters ) {

        if ( url.includes( "/wp-json/" ) && parameters ) {
            url += "?" + parameters;
        }

        if ( url.includes( "/index.php?rest_route=/") && parameters ) {
            url += "&" + parameters;
        }
        
        return url;

    }

    async _fetch( url, parameters=undefined, method="GET", requestHeaders={} ) {

        let requestOptions = { 
            method: method,
            headers: requestHeaders,
        }

        if ( this.nonce ) { 
            requestHeaders["X-WP-Nonce"] = this.nonce;
            requestOptions["credentials"] = "include";
        }

        switch ( method ) {

            case "POST":
                if ( parameters ) {
                    requestHeaders["Content-Type"] = "application/x-www-form-urlencoded";
                    requestOptions["body"] = parameters;
                }
                break;

            case "PUT":
                if ( parameters ) {
                    requestHeaders["Content-Type"] = "application/x-www-form-urlencoded";
                    requestOptions["body"] = parameters;
                }
                break;

            case "GET":
            case "DELETE":
            default:
                if ( parameters ) {
                    url = this._applyParameters( url, parameters );
                }
                break;

        }

        if ( this.debug ) {
            console.debug( url );
            console.debug( requestOptions );
        }

        return fetch(
            url,
            requestOptions
        );

    }

    async _parseNetworkResponse( promiseResponse, callbackOnClientError, returnType="json" ) {

        if ( ! promiseResponse.ok ) {

            return promiseResponse.text().then( text => {

                let errorMessage;

                try { 
                    errorMessage = JSON.parse( text ).message;
                } catch( error ) {
                    errorMessage = text;
                }

                if ( 400 <= promiseResponse.status && promiseResponse.status <= 499 ) {

                    if ( typeof( callbackOnClientError ) === "function" ) {
                        callbackOnClientError( errorMessage );
                    } else {
                        console.error( errorMessage );
                    }

                } else {
                    throw new Error( errorMessage );
                }

            } );

        }

        switch( returnType ) {

            case "text":
                return promiseResponse.clone().text().catch( () => promiseResponse.text() );
                break;

            case "blob":
                return promiseResponse.clone().blob().catch( () => promiseResponse.text() );
                break;

            case "json":
            default:
                // Wonky JS: if json() fails because its just a string, catch and return the text.
                return promiseResponse.clone().json().catch( () => promiseResponse.text() );
                break;

        }


    }    

}