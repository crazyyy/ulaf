<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<div class="modal fade" id="display-errors-warning-modal" tabindex="-1" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="alert alert-warning" role="alert">
					<p>
						<i class="fa fa-exclamation-triangle me-2"></i>
						<?php esc_html_e('Enabling "Display errors" may expose sensitive site data, break page layouts, and display warnings to visitors.', 'debug-log-viewer'); ?>
					</p>
					<p>
						<?php esc_html_e('For security, disable this setting on live sites.', 'debug-log-viewer'); ?>
					</p>

					<p>
						<?php esc_html_e('✅ Use only in development environments.', 'debug-log-viewer'); ?>
						<br />
						<?php esc_html_e('✅ Enable "Log in file" to log errors instead.', 'debug-log-viewer'); ?>
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" id="cancel-display-errors">
					<?php esc_html_e('Cancel', 'debug-log-viewer'); ?>
				</button>
				<button type="button" class="btn btn-danger btn-sm" id="confirm-display-errors">
					<?php esc_html_e('I understand', 'debug-log-viewer'); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.getElementById('display-errors-warning-modal').addEventListener('shown.bs.modal', function() {
		document.getElementById('confirm-display-errors').focus(); // Set focus to a button inside modal
	});
</script>
