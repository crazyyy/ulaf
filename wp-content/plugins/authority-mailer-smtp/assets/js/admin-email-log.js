/* assets/js/admin-email-log.js
 * JS for Authority Mailer admin Email Log page
 * Relies on the localized global authorityMailerEmailLog object (ajax_url, nonce, strings).
 */

(function () {
	'use strict';

	// Minimal guard for localized object
	var L = window.authorityMailerEmailLog || {};
	var ajaxUrl = L.ajax_url || (window.ajaxurl || '/wp-admin/admin-ajax.php');
	var nonce = L.nonce || '';
	var S = (L.strings || {});

	function qs(selector) { return document.querySelector(selector); }
	function qsa(selector) { return Array.prototype.slice.call(document.querySelectorAll(selector)); }

	// Toggle sections
	function initToggles() {
		qsa('.authority-mailer-section').forEach(function (sec) {
			var hdr = sec.querySelector('.hdr');
			var body = sec.querySelector('.body');
			var label = sec.querySelector('.toggle-label');
			if (!hdr || !body) return;
			var isVisible = ( body.style.display !== 'none' );
			hdr.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
			if ( label ) label.textContent = isVisible ? (S.collapse || 'Collapse') : (S.expand || 'Expand');
			hdr.addEventListener('click', function () {
				var visible = ( body.style.display !== 'none' );
				if ( visible ) {
					body.style.display = 'none';
					hdr.setAttribute('aria-expanded', 'false');
					if ( label ) label.textContent = (S.expand || 'Expand');
				} else {
					body.style.display = 'block';
					hdr.setAttribute('aria-expanded', 'true');
					if ( label ) label.textContent = (S.collapse || 'Collapse');
				}
			});
		});
	}

	// Helper to post FormData and parse JSON
	function postFormData(fd, onDone, onFail) {
		var url = ajaxUrl;
		fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (json) { if (typeof onDone === 'function') onDone(json); })
			.catch(function (err) { if (typeof onFail === 'function') onFail(err); });
	}

	// Resend from detail
	function initDetailResendDelete() {
		var detailResend = qs('#authority-mailer-view-resend');
		if (detailResend) {
			detailResend.addEventListener('click', function (e) {
				e.preventDefault();
				var id = this.getAttribute('data-id');
				if (!id) return;
				if (!confirm(S.confirm_resend || 'Resend this email via the same provider?')) return;
				var fd = new FormData();
				fd.append('action', 'authority_mailer_smtp_resend_email_from_log');
				fd.append('log_id', id);
				fd.append('nonce', nonce);
				postFormData(fd, function (r) {
					if (r && r.success) {
						alert(S.resend_attempted || 'Resend queued/attempted. New log entry created if successful.');
					} else {
						var msg = (r && r.data && r.data.message) ? r.data.message : (S.resend_failed || 'Resend failed');
						alert(msg);
					}
					// redirect back to list
					window.location.href = (location.origin + location.pathname + '?page=authority-mailer-smtp-email-log');
				}, function () {
					alert(S.resend_failed || 'Resend failed');
					window.location.href = (location.origin + location.pathname + '?page=authority-mailer-smtp-email-log');
				});
			});
		}

		var del = qs('#authority-mailer-view-delete');
		if (del) {
			del.addEventListener('click', function (e) {
				e.preventDefault();
				var id = this.getAttribute('data-id');
				if (!id) return;
				if (!confirm(S.confirm_delete || 'Delete this log entry? This action cannot be undone.')) return;
				var fd = new FormData();
				fd.append('action', 'authority_mailer_smtp_delete_email_log');
				fd.append('log_id', id);
				fd.append('nonce', nonce);
				postFormData(fd, function () {
					window.location.href = (location.origin + location.pathname + '?page=authority-mailer-smtp-email-log');
				}, function () {
					window.location.href = (location.origin + location.pathname + '?page=authority-mailer-smtp-email-log');
				});
			});
		}
	}

	// List behaviors: select all, resend, delete, bulk actions
	function initListBehavior() {
		var selectAll = qs('#authority-mailer-select-all');
		if (selectAll) {
			selectAll.addEventListener('change', function (e) {
				var checked = e.target.checked;
				qsa('.authority-mailer-select-row').forEach(function (cb) { cb.checked = checked; });
			});
		}

		// Resend buttons in list - use event delegation
		document.addEventListener('click', function (e) {
			var btn = e.target.closest && e.target.closest('.authority-mailer-resend');
			if (!btn) return;
			e.preventDefault();
			var id = btn.getAttribute('data-id');
			if (!id) return;
			if (!confirm(S.confirm_resend || 'Resend this email via the same provider?')) return;
			var fd = new FormData();
			fd.append('action', 'authority_mailer_smtp_resend_email_from_log');
			fd.append('log_id', id);
			fd.append('nonce', nonce);
			postFormData(fd, function (r) {
				if (r && r.success) {
					alert(S.resend_attempted || 'Resend attempted. New log entry created if successful.');
					window.location.reload();
				} else {
					var msg = (r && r.data && r.data.message) ? r.data.message : (S.resend_failed || 'Resend failed');
					alert(msg);
				}
			}, function () {
				alert(S.resend_failed || 'Resend failed');
			});
		}, false);

		// Delete single rows - use event delegation
		document.addEventListener('click', function (e) {
			var btn = e.target.closest && e.target.closest('.authority-mailer-delete-log');
			if (!btn) return;
			e.preventDefault();
			if (!confirm(S.confirm_delete || 'Delete this log entry? This action cannot be undone.')) return;
			var id = btn.getAttribute('data-id');
			if (!id) return;
			var fd = new FormData();
			fd.append('action', 'authority_mailer_smtp_delete_email_log');
			fd.append('log_id', id);
			fd.append('nonce', nonce);
			postFormData(fd, function (r) {
				if (r && r.success) {
					alert(S.delete_success || 'Log entry deleted successfully.');
					window.location.reload();
				} else {
					var msg = (r && r.data && r.data.message) ? r.data.message : (S.delete_failed || 'Delete failed');
					alert(msg);
				}
			}, function () {
				alert(S.delete_failed || 'Delete failed');
			});
		}, false);

		// Bulk action
		var bulkBtn = qs('#authority-mailer-bulk-apply');
		if (bulkBtn) {
			bulkBtn.addEventListener('click', function (e) {
				e.preventDefault();
				var action = (qs('#authority-mailer-bulk-action') || {}).value;
				if (!action) { alert(S.select_bulk_action || 'Select a bulk action first'); return; }
				var ids = [];
				qsa('.authority-mailer-select-row:checked').forEach(function (cb) { ids.push(cb.getAttribute('data-id')); });
				if (ids.length === 0) { alert(S.no_rows_selected || 'No rows selected'); return; }
				if (action === 'delete') {
					if (!confirm(S.bulk_delete_confirm || 'Delete selected log entries? This action cannot be undone.')) return;
					var fd = new FormData();
					fd.append('action', 'authority_mailer_smtp_bulk_delete_email_logs');
					ids.forEach(function (id) { fd.append('ids[]', id); });
					fd.append('nonce', nonce);
					postFormData(fd, function (r) {
						if (r && r.success) {
							alert(S.bulk_delete_success || 'Selected log entries deleted successfully.');
							window.location.reload();
						} else {
							var msg = (r && r.data && r.data.message) ? r.data.message : (S.bulk_delete_failed || 'Bulk delete failed');
							alert(msg);
						}
					}, function () {
						alert(S.bulk_delete_failed || 'Bulk delete failed');
					});
				} else {
					alert(S.unknown_bulk_action || 'Unknown bulk action');
				}
			});
		}
	}

	// Init on DOMReady
	document.addEventListener('DOMContentLoaded', function () {
		initToggles();
		initDetailResendDelete();
		initListBehavior();
	});
})();
