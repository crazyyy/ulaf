/**
 * Conflict Detector JavaScript
 *
 * Handles dismissible admin notices for conflict detection.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		$(document).on('click', '.authority-mailer-notice .notice-dismiss', function() {
			var notice = $(this).closest('.authority-mailer-notice');
			var noticeId = notice.data('notice-id');
			
			if (noticeId) {
				$.ajax({
					url: authorityMailerConflictDetector.ajaxUrl,
					type: 'POST',
					data: {
						action: 'authority_mailer_dismiss_notice',
						notice_id: noticeId,
						nonce: authorityMailerConflictDetector.nonce
					}
				});
			}
		});
	});

})(jQuery);
