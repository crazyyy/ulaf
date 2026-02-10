/**
 * Brevo Settings JavaScript
 *
 * Handles the toggle between API and SMTP authentication modes
 * in the Brevo provider settings.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function() {
	'use strict';
	
	/**
	 * Toggle SMTP fields visibility based on checkbox
	 */
	function toggleBrevoSMTPFields() {
		var useSmtpCheckbox = document.getElementById('brevo_use_smtp');
		var apiFields = document.getElementById('brevo-api-fields');
		var smtpFields = document.getElementById('brevo-smtp-fields');
		
		if (!useSmtpCheckbox || !apiFields || !smtpFields) {
			return;
		}
		
		if (useSmtpCheckbox.checked) {
			apiFields.style.display = 'none';
			smtpFields.style.display = 'block';
		} else {
			apiFields.style.display = 'block';
			smtpFields.style.display = 'none';
		}
	}
	
	/**
	 * Initialize on page load
	 */
	function init() {
		var checkbox = document.getElementById('brevo_use_smtp');
		if (checkbox) {
			toggleBrevoSMTPFields();
			checkbox.addEventListener('change', toggleBrevoSMTPFields);
		}
	}
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
