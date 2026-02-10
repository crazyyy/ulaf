import apiFetch from '@wordpress/api-fetch';

/**
 * Make an API request to the plugin's REST API.
 *
 * @param {Object} options Options for the request.
 * @param {string} options.path The path to the endpoint (relative to the plugin namespace).
 * @param {string} [options.method='GET'] The HTTP method.
 * @param {Object} [options.data] The data to send with the request.
 * @return {Promise<any>} The response data.
 */
export const fetchFromApi = ({ path, method = 'GET', data }) => {
    const settings = window.healthBeamSettings;

    if (!settings) {
        return Promise.reject(new Error('Plugin settings not found.'));
    }

    // Remove leading slash if present to avoid double slashes when combining with root
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;

    return apiFetch({
        url: `${settings.root}${cleanPath}`,
        method,
        data,
        headers: {
            'X-WP-Nonce': settings.nonce,
        },
    });
};
