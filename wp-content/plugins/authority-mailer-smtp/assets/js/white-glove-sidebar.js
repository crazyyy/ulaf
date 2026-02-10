/**
 * White-Glove Installation Service Sidebar JavaScript
 *
 * Handles booking link tracking (dismiss functionality removed).
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Initialize white-glove sidebar functionality
	 */
	function initWhiteGloveSidebar() {
		const $sidebar = $('.am-white-glove-sidebar');
		
		if (!$sidebar.length) {
			return;
		}

		// Dismiss functionality has been removed - banner always visible

		// Track CTA button clicks (optional analytics)
		$sidebar.find('.am-white-glove-cta-button').on('click', function() {
			// Track button click if analytics is available
			if (typeof gtag === 'function') {
				gtag('event', 'white_glove_cta_click', {
					event_category: 'onboarding',
					event_label: 'White-Glove Service CTA'
				});
			}
			// Continue with default link behavior
		});
	}

	// Initialize on document ready
	$(document).ready(function() {
		initWhiteGloveSidebar();
	});

})(jQuery);
