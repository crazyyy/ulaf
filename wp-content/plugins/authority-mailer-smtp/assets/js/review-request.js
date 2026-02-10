/**
 * Authority Mailer Review Request JavaScript
 *
 * Handles user interactions for the 3-tier review request system:
 * - Admin notice buttons (Tier 1)
 * - Success toast display and auto-dismiss (Tier 3)
 * - AJAX communication with backend
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Initialize review request functionality
	 */
	$(document).ready(function() {
		// Handle admin notice button clicks.
		initAdminNotice();

		// Handle toast on test email success (triggered from support.js).
		$(document).on('authorityMailerTestEmailSuccess', function() {
			showReviewToast();
		});

		// Hook into any AJAX complete to check for showToast flag.
		$(document).ajaxComplete(function(event, xhr, settings) {
			// Check if this is a successful test email response.
			if (settings.data && settings.data.indexOf('authority_mailer_smtp_send_test_email') !== -1) {
				try {
					var response = JSON.parse(xhr.responseText);
					if (response && response.success && response.data && response.data.showToast) {
						showReviewToast();
					}
				} catch (e) {
					// Ignore parse errors.
				}
			}
		});
	});

	/**
	 * Initialize admin notice button handlers
	 */
	function initAdminNotice() {
		$('.authority-mailer-review-btn').on('click', function(e) {
			var $btn = $(this);
			var action = $btn.data('action');
			var $notice = $('#authority-mailer-review-notice');

			// If it's the "leave review" link, let it open naturally.
			if (action === 'leave_review') {
				// Send AJAX to mark as completed, but don't prevent default.
				sendReviewAction(action);
				// Fade out notice.
				fadeOutNotice($notice);
				return; // Let the link work normally.
			}

			// Prevent default for buttons.
			e.preventDefault();

			// Send AJAX request.
			sendReviewAction(action);

			// Fade out notice.
			fadeOutNotice($notice);
		});
	}

	/**
	 * Send AJAX request for review action
	 *
	 * @param {string} action The action to perform (leave_review, maybe_later, already_did)
	 */
	function sendReviewAction(action) {
		$.ajax({
			url: authorityMailerReview.ajaxUrl,
			type: 'POST',
			data: {
				action: 'authority_mailer_review_action',
				nonce: authorityMailerReview.nonce,
				review_action: action
			},
			success: function(response) {
				if (response.success) {
					// Action completed successfully.
					console.log('Review action completed:', action);
				} else {
					console.error('Review action failed:', response.data);
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX error:', error);
			}
		});
	}

	/**
	 * Fade out and remove notice
	 *
	 * @param {jQuery} $notice The notice element
	 */
	function fadeOutNotice($notice) {
		$notice.addClass('fade-out');
		setTimeout(function() {
			$notice.slideUp(300, function() {
				$notice.remove();
			});
		}, 300);
	}

	/**
	 * Show review toast notification
	 */
	function showReviewToast() {
		// Check if toast should be shown.
		if (!authorityMailerReview.showToast) {
			return;
		}

		// Don't show if toast already exists.
		if ($('#authority-mailer-review-toast').length > 0) {
			return;
		}

		// Build toast HTML.
		var toastHtml = '<div class="authority-mailer-review-toast" id="authority-mailer-review-toast">' +
			'<button type="button" class="authority-mailer-review-toast-close" aria-label="Close">&times;</button>' +
			'<div class="authority-mailer-review-toast-content">' +
			'<p class="authority-mailer-review-toast-success">✅ ' + escapeHtml(authorityMailerReview.toastSuccess) + '</p>' +
			'<p class="authority-mailer-review-toast-prompt">' +
			escapeHtml(authorityMailerReview.toastPrompt) + ' ' +
			'<a href="' + escapeHtml(authorityMailerReview.reviewUrl) + '" ' +
			'class="authority-mailer-review-toast-link" ' +
			'target="_blank" rel="noopener noreferrer">' +
			escapeHtml(authorityMailerReview.toastLink) + ' →</a>' +
			'</p>' +
			'</div>' +
			'</div>';

		// Append to body.
		$('body').append(toastHtml);

		var $toast = $('#authority-mailer-review-toast');

		// Show toast with animation.
		setTimeout(function() {
			$toast.addClass('show');
		}, 100);

		// Handle close button.
		$toast.find('.authority-mailer-review-toast-close').on('click', function() {
			hideReviewToast($toast);
		});

		// Handle review link click.
		$toast.find('.authority-mailer-review-toast-link').on('click', function() {
			// Mark as completed.
			sendReviewAction('leave_review');
			// Hide toast.
			hideReviewToast($toast);
		});

		// Auto-dismiss after 5 seconds.
		setTimeout(function() {
			hideReviewToast($toast);
		}, 5000);
	}

	/**
	 * Hide and remove review toast
	 *
	 * @param {jQuery} $toast The toast element
	 */
	function hideReviewToast($toast) {
		$toast.addClass('fade-out');
		setTimeout(function() {
			$toast.remove();
		}, 300);
	}

	/**
	 * Simple HTML escape function
	 *
	 * @param {string} str String to escape
	 * @return {string} Escaped string
	 */
	function escapeHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

})(jQuery);
