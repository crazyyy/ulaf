/**
 * Gmail OAuth Connection Handler
 *
 * Handles client-side OAuth URL generation for Gmail provider.
 * Now saves credentials BEFORE redirecting to prevent data loss.
 */

(function($) {
	'use strict';

	// Get localized strings from the global object
	var strings = window.authorityMailerOnboarding || window.authorityMailerSettings || {};

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
		return fallback || '';
	}

	$(document).on('click', '#authority-mailer-google-connect', function(e) {
		e.preventDefault();

		var clientId = $.trim($('#google_client_id').val() || '');
		var clientSecret = $.trim($('#google_client_secret').val() || '');
		var redirect = $.trim($('#google_redirect_uri').val() || '');

		// Validate Client ID
		if (!clientId) {
			$('#google_client_id').focus();
			alert(getString('i18n_google_client_id_required', 'Please enter your Google Client ID first.'));
			return false;
		}

		// Validate Client Secret
		if (!clientSecret) {
			$('#google_client_secret').focus();
			alert(getString('i18n_google_client_secret_required', 'Please enter your Google Client Secret first.'));
			return false;
		}

		// Validate Redirect URI
		if (!redirect) {
			alert(getString('i18n_redirect_uri_missing', 'Redirect URI is missing. Please refresh the page.'));
			return false;
		}

		// **FIX: Save credentials BEFORE redirecting**
		var $btn = $(this);
		var originalText = $btn.text();
		$btn.prop('disabled', true).text(getString('i18n_saving_credentials', 'Saving credentials...'));

		// Collect all form data
		var $form = $btn.closest('form');
		var formData = $form.serialize();

		// Save via AJAX
		$.ajax({
			url: $form.attr('action') || window.ajaxurl,
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				if (response && response.success) {
					// Credentials saved! Now redirect to Google OAuth
					$btn.text(getString('i18n_redirecting_google', 'Redirecting to Google...'));

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
						state: 'authority-mailer-smtp'
					};

					var query = $.map(params, function(v, k) {
						return encodeURIComponent(k) + '=' + encodeURIComponent(v);
					}).join('&');

					var authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' + query;
					window.location.href = authUrl;
				} else {
					alert(getString('i18n_save_credentials_failed', 'Failed to save credentials: ') + (response.data?.message || getString('i18n_unknown_error', 'Unknown error')));
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert(getString('i18n_network_error_credentials', 'Network error. Could not save credentials. Please try again.'));
				$btn.prop('disabled', false).text(originalText);
			}
		});

		return false;
	});

})(jQuery);
