<?php

namespace DebugLogViewer\Front\Views\Pages;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Controllers\AlertController;

use function DebugLogViewer\Admin\Controllers\dbg_lv;

?>

<!-- Alerts Tab Content -->
<?php
// Safely validate tab parameter to prevent XSS
$dbg_lv_current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
$dbg_lv_valid_tabs = ['alerts', 'log-view', 'settings'];
$dbg_lv_is_alerts_active = in_array($dbg_lv_current_tab, $dbg_lv_valid_tabs, true) && $dbg_lv_current_tab === 'alerts';
?>
<div class="tab-pane fade <?php echo $dbg_lv_is_alerts_active ? 'show active' : ''; ?>"
	id="alerts-content"
	role="tabpanel"
	aria-labelledby="alerts-tab">

	<div class="container">

		<div class="alerts section">

			<!-- Dynamic next email info container -->
			<div id="next-email-info" class="next-email-info">
				<p style="margin: 0; color: #0073aa;">
					<strong id="next-email-time"></strong>
					<small id="next-email-countdown"></small>
				</p>
			</div>
			<?php $dbg_lv_notificator = new AlertController(); ?>
			<form class="form-group" id="dbg_lv_log_viewer_alerts_form" data-alerts-enabled="<?php echo esc_attr($dbg_lv_notificator->isEnabled() ? 'true' : 'false'); ?>">
				<p>
					<?php 
					if (dbg_lv()->is_premium()): ?>
						<?php esc_html_e('Get alerts for the error levels you choose. You have full control over your notifications.', 'debug-log-viewer'); ?>
					<?php else: ?>
						<?php esc_html_e('By default, you will be notified about serious problems. Upgrade to Pro to choose specific error levels and reduce noise.', 'debug-log-viewer'); ?>
					<?php endif; ?>
				</p>


				<p><?php esc_html_e('Configure your email settings and select which error levels you want to monitor', 'debug-log-viewer'); ?></p>

				<!-- Email Settings Section -->
				<div class="alerts-section mt-4">

					<label for="emails"><?php esc_html_e('Send alerts to:', 'debug-log-viewer'); ?></label>
					<div id="email-recipients-container">
						<?php
						$dbg_lv_emails = $dbg_lv_notificator->getNotificationEmails();
						if (empty($dbg_lv_emails)) {
							$dbg_lv_emails = [''];
						}
						foreach ($dbg_lv_emails as $dbg_lv_index => $dbg_lv_email) :
						?>
						<div class="email-input-row" data-index="<?php echo esc_attr($dbg_lv_index); ?>">
							<input type="email" name="emails[]" value="<?php echo esc_attr($dbg_lv_email); ?>" disabled />
							<?php if ($dbg_lv_index === 0) : ?>
									<button type="button" class="btn btn-outline-primary btn-sm add-email-btn" title="<?php esc_attr_e('Add another email', 'debug-log-viewer'); ?>" disabled>+</button>
								<?php else : ?>
									<button type="button" class="btn btn-outline-danger btn-sm remove-email-btn" title="<?php esc_attr_e('Remove this email', 'debug-log-viewer'); ?>" disabled>-</button>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

					<label class="mt-4" for="recurrence"><?php esc_html_e('How often?', 'debug-log-viewer'); ?></label>
					<select name="recurrence" id="recurrence" disabled>
						<?php $dbg_lv_notificator->getRecurrence(); ?>
					</select>

					<!-- Error Levels Section -->
					<div class="alerts-section mt-4 <?php echo !dbg_lv()->is_premium() ? 'pro-feature-section' : ''; ?>">
						<label><?php esc_html_e('Notify me about:', 'debug-log-viewer'); ?></label>

						<?php if (!dbg_lv()->is_premium()): ?>
							<div class="pro-feature-notice">
								<p class="upgrade-notice">
									<i class="fas fa-star"></i>
									<?php esc_html_e('Upgrade to Pro to choose specific error levels', 'debug-log-viewer'); ?>
									<span class="upgrade-link freemius-checkout-trigger"><?php esc_html_e('Upgrade Now', 'debug-log-viewer'); ?></span>
								</p>
							</div>
						<?php endif; ?>
						<div class="togglers-group <?php echo !dbg_lv()->is_premium() ? 'pro-disabled' : ''; ?>">
							<?php
							$dbg_lv_available_levels = $dbg_lv_notificator->getAllAvailableLevels();
							$dbg_lv_selected_levels = $dbg_lv_notificator->getSelectedLevels();
							
							// For free users, show only critical error levels as default/preview
							if (!dbg_lv()->is_premium()) {
								$dbg_lv_available_levels = array_slice($dbg_lv_available_levels, 0, 4, true); // Show only first 4 levels as preview
							}
							
							foreach ($dbg_lv_available_levels as $dbg_lv_level => $dbg_lv_label) :
								$dbg_lv_is_checked = in_array($dbg_lv_level, $dbg_lv_selected_levels) ? 'checked' : '';
								$dbg_lv_toggle_id = 'dbg_lv_toggle_level_' . esc_attr($dbg_lv_level);
								$dbg_lv_is_disabled = 'disabled'; // Disabled for all users
							?>
								<div class="log-info-block form-switch">
									<input
										id="<?php echo esc_attr($dbg_lv_toggle_id); ?>"
										type="checkbox"
										name="levels[]"
										value="<?php echo esc_attr($dbg_lv_level); ?>"
										class="form-check-input error-level-checkbox"
										<?php echo esc_attr($dbg_lv_is_checked); ?>
										<?php echo esc_attr($dbg_lv_is_disabled); ?> />
									<label class="form-check-label" for="<?php echo esc_attr($dbg_lv_toggle_id); ?>"><?php echo esc_html($dbg_lv_label); ?></label>
									
								</div>
							<?php endforeach; ?>
							
							<?php if (!dbg_lv()->is_premium()): ?>
								<div class="log-info-block form-switch pro-preview">
									<span class="preview-text">
										<?php esc_html_e('+ 3 more error levels available in ', 'debug-log-viewer'); ?>
										<span class="pro-badge freemius-checkout-trigger">Pro</span>
									</span>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="form-check send-test-email">
						<label class="form-check-label">
							<input type="checkbox" name="send_test_email" id="send_test_email" class="form-check-input" disabled>
							<span class="form-check-sign"></span>
							<span><?php esc_html_e('Send test Email now', 'debug-log-viewer'); ?></span>
						</label>
					</div>
				</div>

				<div class="form-actions mt-4">
					<input type="submit" value="<?php esc_attr_e('Loading...', 'debug-log-viewer'); ?>" class="btn btn-primary btn-sm" disabled />
				</div>
			</form>
		</div>
	</div>
</div>
