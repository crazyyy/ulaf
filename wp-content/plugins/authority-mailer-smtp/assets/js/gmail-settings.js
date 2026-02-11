/**
 * Authority Mailer SMTP - Gmail Settings JavaScript
 *
 * Handles the Gmail provider settings form functionality including
 * OAuth connection URL generation.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	// Get localized strings from the global object (check multiple sources for flexibility)
	var strings = window.authorityMailerOnboarding || window.authorityMailerSettings || window.authorityMailerGmailSettings || {};

	/**
	 * Get a localized string with fallback.
	 *
	 * @param {string} key The string key.
	 * @param {string} fallback The fallback value.
	 * @return {string} The localized string or fallback.
	 */
	function getString(key, fallback) {
		if (strings && strings[key]) {
			return strings[key];
		}
		if (strings && strings.strings && strings.strings[key]) {
			return strings.strings[key];
		}
		// Check authorityMailerGmailSettings as fallback if other sources don't have the key
		if (window.authorityMailerGmailSettings && window.authorityMailerGmailSettings[key]) {
			return window.authorityMailerGmailSettings[key];
		}
		return fallback || '';
	}

	// Enhance the Connect button so it works when the server filter did not supply an auth URL.
	// If the filter returned a real URL, we let the anchor behave normally.
	$(document).on('click', '#authority-mailer-google-connect', function(e) {
		var href = $(this).attr('href') || '';

		// Allow normal navigation only if a valid HTTPS/HTTP URL was provided.
		// Explicitly check for http:// or https:// prefix to prevent XSS via
		// javascript:, data:, vbscript: or other potentially dangerous schemes.
		if (href && href !== '#' && /^https?:\/\//i.test(href)) {
			return true;
		}
		e.preventDefault();

		// Build auth URL client-side when client_id and redirect_uri are present.
		var clientId = $.trim($('#google_client_id').val() || '');
		var redirect = $.trim($('#google_redirect_uri').val() || '');

		if (!clientId) {
			// Show inline error if possible, fallback to alert
			var $cid = $('#google_client_id');
			if ($cid.length) {
				$cid.focus();
				var $err = $('#google_client_id-error');
				if ($err.length) {
					$err.text(getString('i18n_google_client_id_required', 'Please enter a Client ID.')).show();
				}
			} else {
				alert(getString('i18n_google_client_id_required', 'Please enter a Client ID.'));
			}
			return false;
		}

		if (!redirect) {
			alert(getString('google_oauth_client_missing_detail', 'Provide OAuth Client ID and Client Secret for Google/Gmail (fields: client_id, client_secret).'));
			return false;
		}

		// Scopes: minimal required for sending and getting user email/profile.
		var scopes = [
			'https://www.googleapis.com/auth/gmail.send',
			'https://www.googleapis.com/auth/userinfo.email',
			'https://www.googleapis.com/auth/userinfo.profile'
		].join(' ');

		var params = {
			client_id: clientId,
			redirect_uri: redirect,
			response_type: 'code',
			scope: scopes,
			access_type: 'offline',
			include_granted_scopes: 'true',
			prompt: 'consent',
			state: 'authority-mailer-smtp' // lightweight state; server can validate/refine if needed
		};

		var query = $.map(params, function(v, k) {
			return encodeURIComponent(k) + '=' + encodeURIComponent(v);
		}).join('&');

		var authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' + query;

		// Open in same window (so redirect returns to WP callback), or new window if you want.
		window.location.href = authUrl;
		return false;
	});

})(jQuery);
