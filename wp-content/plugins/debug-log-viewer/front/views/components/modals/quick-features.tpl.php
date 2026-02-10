<?php
/**
 * Quick features preview modal (simple, no pricing)
 *
 * @package DebugLogViewer
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="modal fade" id="dbg-lv-features-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-0 pb-2">
				<h5 class="modal-title">
					<i class="fas fa-crown text-warning"></i> <?php esc_html_e('Pro Features', 'debug-log-viewer'); ?>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body px-4">
				<ul class="list-unstyled mb-3">
					<li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php esc_html_e('Export logs to CSV', 'debug-log-viewer'); ?></li>
					<li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php esc_html_e('Email alerts for errors', 'debug-log-viewer'); ?></li>
					<li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php esc_html_e('Custom time range filters', 'debug-log-viewer'); ?></li>
					<li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php esc_html_e('Choose specific error levels to monitor', 'debug-log-viewer'); ?></li>
					<li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php esc_html_e('Priority support', 'debug-log-viewer'); ?></li>
				</ul>
				<button class="btn btn-primary w-100" id="features-modal-upgrade-btn">
					<?php esc_html_e('View Pricing & Upgrade', 'debug-log-viewer'); ?> <i class="fas fa-arrow-right"></i>
				</button>
			</div>
		</div>
	</div>
</div>
