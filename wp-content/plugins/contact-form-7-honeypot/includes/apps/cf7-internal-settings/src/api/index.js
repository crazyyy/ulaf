function cf7appsFetch( url, options ) {
    options = options || {};
    options.headers = options.headers || {};
    options.headers['X-WP-Nonce'] = CF7AppsInternalSettings.nonce;
    return fetch( `${ CF7AppsInternalSettings.restURL }cf7apps/v1/${ url }`, options );
}

export async function getMenu() {
    const response = await cf7appsFetch( 'get-menu-items?menu-for=internal-settings', {
        method: 'GET',
    } );

    if ( ! response.ok ) {
        return false;
    }

    const json = await response.json();

    return json.data;
}

export async function getApps( app, formId ) {
    const response = await cf7appsFetch( `get-apps/${ app }?settings-for=internal-settings&form-id=${ formId }`, {
        method: 'GET'
    } );

    if ( ! response.ok ) {
        return false;
    }

    const json = await response.json();

    return json.data;
}

export async function saveSettings( app, formData, formId ) {

    const response = await cf7appsFetch( `save-app-settings`, {
        method: 'POST',
        body: JSON.stringify( {
            id: app,
            form_id: formId,
            app_settings: formData,
            'settings-for': 'internal-settings'
        } ),
    } );

    if ( ! response.ok ) {
        return false;
    }

    const json = await response.json();

    return json.data;
}
