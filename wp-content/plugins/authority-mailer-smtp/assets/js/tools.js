/**
 * Authority Mailer SMTP - Tools Page JavaScript
 *
 * @package Authority_Mailer
 * @since   1.0.3
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const $form = $('#am-tools-form');
		const $domainInput = $('#am-domain-input');
		const $runCheckBtn = $('#am-run-check-btn');
		const $runAgainBtn = $('#am-run-again-btn');
		const $loading = $('#am-tools-loading');
		const $results = $('#am-tools-results');
		const $summary = $('#am-deliverability-summary');
		const $summaryIcon = $('#am-summary-icon');
		const $summaryTitle = $('#am-summary-title');
		const $summaryDescription = $('#am-summary-description');
		const $socialProof = $('#am-social-proof');

		/**
		 * Show error message
		 */
		function showError(message) {
			// Remove existing error if any
			$('.am-tools-error').remove();
			
			// Create and show error message with proper escaping
			const $error = $('<div class="am-tools-error"></div>').text(message);
			$form.after($error);
			
			// Auto-hide after 5 seconds
			setTimeout(function() {
				$error.fadeOut(400, function() {
					$(this).remove();
				});
			}, 5000);
		}

		/**
		 * Run deliverability check
		 */
		function runCheck() {
			const domain = $domainInput.val().trim();

			if (!domain) {
				showError(authorityMailerTools.strings.emptyDomain);
				$domainInput.focus();
				return;
			}

			// Show loading, hide form and results
			$form.hide();
			$loading.show();
			$results.hide();
			$('.am-tools-error').remove();

			// Disable button
			$runCheckBtn.prop('disabled', true);

			// Make AJAX request
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'authority_mailer_check_deliverability',
					nonce: authorityMailerTools.nonce,
					domain: domain
				},
				success: function(response) {
					if (response.success && response.data) {
						displayResults(response.data);
					} else {
						const errorMsg = response.data && response.data.message ? response.data.message : authorityMailerTools.strings.checkFailed;
						showError(errorMsg);
						resetForm();
					}
				},
				error: function() {
					showError(authorityMailerTools.strings.networkError);
					resetForm();
				},
				complete: function() {
					$loading.hide();
					$runCheckBtn.prop('disabled', false);
				}
			});
		}

		/**
		 * Display check results
		 */
		function displayResults(data) {
			// Set checked domain
			$('#checked-domain').text(data.domain);

			// Check if any critical checks failed
			const criticalChecks = ['spf', 'dkim', 'dmarc'];
			const hasCriticalFailure = criticalChecks.some(function(check) {
				return data[check] && data[check].status === 'fail';
			});

			// Update summary banner
			updateSummaryBanner(hasCriticalFailure);

			// Update each check
			updateCheck('spf', data.spf);
			updateCheck('dkim', data.dkim);
			updateCheck('dmarc', data.dmarc);
			updateCheck('mx', data.mx);
			updateCheck('reputation', data.reputation);
			updateCheck('blacklist', data.blacklist);

			// Show social proof if there are any failures
			if (hasCriticalFailure) {
				$socialProof.fadeIn(300);
			} else {
				$socialProof.hide();
			}

			// Show results with fade-in animation
			$results.fadeIn(400);
		}

		/**
		 * Update summary banner based on check results
		 */
		function updateSummaryBanner(hasCriticalFailure) {
			if (hasCriticalFailure) {
				// Warning state
				$summary.removeClass('am-summary-success').addClass('am-summary-warning');
				$summaryIcon.html('⚠️');
				$summaryTitle.text(authorityMailerTools.strings.summaryWarningTitle);
				$summaryDescription.text(authorityMailerTools.strings.summaryWarningDescription);
			} else {
				// Success state
				$summary.removeClass('am-summary-warning').addClass('am-summary-success');
				$summaryIcon.html('✅');
				$summaryTitle.text(authorityMailerTools.strings.summarySuccessTitle);
				$summaryDescription.text(authorityMailerTools.strings.summarySuccessDescription);
			}
			
			// Show the summary banner with fade-in
			$summary.fadeIn(300);
		}

		/**
		 * Update a specific check result
		 */
		function updateCheck(checkType, checkData) {
			const $badge = $('[data-check="' + checkType + '"]');
			const $details = $('[data-details="' + checkType + '"]');
			const $actions = $('[data-actions="' + checkType + '"]');

			// Update badge
			const status = checkData.status || 'fail';
			$badge.removeClass('pass fail clean unknown').addClass(status);
			$badge.text(status.toUpperCase());

			// Update details
			$details.removeClass('pass fail clean unknown').addClass(status);
			$details.text(checkData.message || 'No information available');

			// Show/hide action buttons for critical checks (SPF, DKIM, DMARC)
			if ($actions.length > 0) {
				if (status === 'fail') {
					$actions.slideDown(300);
				} else {
					$actions.hide();
				}
			}
		}

		/**
		 * Reset form to initial state
		 */
		function resetForm() {
			$form.show();
			$loading.hide();
			$results.hide();
			$domainInput.val('');
		}

		/**
		 * Event Handlers
		 */
		$runCheckBtn.on('click', function(e) {
			e.preventDefault();
			runCheck();
		});

		$runAgainBtn.on('click', function(e) {
			e.preventDefault();
			resetForm();
		});

		// Allow Enter key to submit
		$domainInput.on('keypress', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				runCheck();
			}
		});
	});

})(jQuery);
