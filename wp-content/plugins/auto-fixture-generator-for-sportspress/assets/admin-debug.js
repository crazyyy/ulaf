/**
 * Debug/Dry Run functionality for Auto Fixture Generator for SportsPress.
 *
 * This file handles the dry-run button click and AJAX submission.
 * Only loaded when WP_DEBUG and WP_DEBUG_LOG are both enabled.
 *
 * @package AFGSP
 */

(function ($) {
	'use strict';

	/**
	 * Handle dry run button click.
	 * Submits the form via AJAX with dry_run=1 parameter.
	 */
	$(document).on('click', '#afgsp_dry_run_btn', function (ev) {
		ev.preventDefault();

		var $form = $('#afgsp_form');
		var $btn = $(this);
		var $submit = $form.find('input[type="submit"], button[type="submit"]');

		if (!window.AFGSP_ADMIN || !window.AFGSP_ADMIN.ajaxUrl) {
			alert('AJAX not available. Please refresh the page and try again.');
			return;
		}

		// Disable both buttons during processing.
		$submit.prop('disabled', true);
		$btn.prop('disabled', true);

		// Create or update progress bar.
		var $progressWrap = $('#afgsp_progress_wrap');
		if ($progressWrap.length === 0) {
			$progressWrap = $('<div id="afgsp_progress_wrap" style="margin-top:12px;"></div>');
			var $barOuter = $('<div id="afgsp_progress_bar" style="width:100%; max-width:520px; height:18px; background:#eee; border:1px solid #ccd0d4; position:relative;"></div>');
			var $barInner = $('<div id="afgsp_progress_fill" style="height:100%; width:0; background:#dc3232;"></div>');
			var $label = $('<div id="afgsp_progress_label" style="margin-top:6px;"></div>');
			$barOuter.append($barInner);
			$progressWrap.append($barOuter).append($label);
			$form.find('p.submit').after($progressWrap);
		} else {
			// Reset progress bar and change color to red for dry run.
			$('#afgsp_progress_fill').css({width: '0', background: '#dc3232'});
		}

		$('#afgsp_progress_label').text((window.AFGSP_ADMIN && window.AFGSP_ADMIN.dryRunProgressText) || 'Dry run in progress...');

		function setProgress(processed, total, isDryRun) {
			var pct = total > 0 ? Math.floor((processed / total) * 100) : 0;
			$('#afgsp_progress_fill').css('width', pct + '%');
			var text = '[DRY RUN] ' + processed + ' / ' + total + ' fixtures processed';
			$('#afgsp_progress_label').text(text);
		}

		// Serialize form data and add dry_run flag.
		var data = $form.serializeArray();
		data.push({name: 'action', value: 'afgsp_start_generation'});
		data.push({name: 'nonce', value: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''});
		data.push({name: 'dry_run', value: '1'});

		$.ajax({
			url: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '',
			type: 'POST',
			data: $.param(data),
			dataType: 'json'
		}).done(function (resp) {
			if (!resp || !resp.success || !resp.data || !resp.data.job_id) {
				$submit.prop('disabled', false);
				$btn.prop('disabled', false);
				$('#afgsp_progress_label').text('Error starting dry run');
				return;
			}

			var jobId = resp.data.job_id;
			var total = resp.data.total || 0;
			setProgress(0, total, true);

			(function processNext() {
				$.ajax({
					url: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.ajaxUrl) || '',
					type: 'POST',
					data: {
						action: 'afgsp_process_generation',
						job_id: jobId,
						nonce: (window.AFGSP_ADMIN && window.AFGSP_ADMIN.nonce) || ''
					},
					dataType: 'json'
				}).done(function (r) {
					if (!r || !r.success || !r.data) {
						$submit.prop('disabled', false);
						$btn.prop('disabled', false);
						$('#afgsp_progress_label').text('Error processing dry run');
						return;
					}

					var processed = r.data.processed || 0;
					setProgress(processed, total, true);

					if (r.data.done) {
						var createdTotal = (typeof r.data.created !== 'undefined') ? Number(r.data.created) : 0;
						var gameweeksTotal = (typeof r.data.gameweeks !== 'undefined') ? Number(r.data.gameweeks) : 0;
						var completionMsg = '[DRY RUN] Completed: ' + createdTotal + ' fixture' + (createdTotal !== 1 ? 's' : '');
						if (gameweeksTotal > 0) {
							completionMsg += ' in ' + gameweeksTotal + ' gameweek' + (gameweeksTotal !== 1 ? 's' : '');
						}
						completionMsg += ' - Check debug.log for details';
						$('#afgsp_progress_label').text(completionMsg);
						$submit.prop('disabled', false);
						$btn.prop('disabled', false);
					} else {
						setTimeout(processNext, 300);
					}
				}).fail(function (xhr) {
					$submit.prop('disabled', false);
					$btn.prop('disabled', false);
					var errorMsg = 'Dry run request failed';

					// Try to extract error message from server response.
					if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
						errorMsg = xhr.responseJSON.data.message;
					} else if (xhr.responseText) {
						try {
							var resp = JSON.parse(xhr.responseText);
							if (resp.data && resp.data.message) {
								errorMsg = resp.data.message;
							}
						} catch (e) {
							// Keep default message.
						}
					}

					$('#afgsp_progress_label').text('[DRY RUN] ' + errorMsg);
				});
			})();
		}).fail(function (xhr) {
			$submit.prop('disabled', false);
			$btn.prop('disabled', false);
			var errorMsg = 'Dry run request failed';

			// Try to extract error message from server response.
			if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
				errorMsg = xhr.responseJSON.data.message;
			} else if (xhr.responseText) {
				try {
					var resp = JSON.parse(xhr.responseText);
					if (resp.data && resp.data.message) {
						errorMsg = resp.data.message;
					}
				} catch (e) {
					// Keep default message.
				}
			}

			$('#afgsp_progress_label').text('[DRY RUN] ' + errorMsg);
		});
	});

})(jQuery);
