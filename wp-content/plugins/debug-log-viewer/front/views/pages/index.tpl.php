<?php

namespace DebugLogViewer\Front\Views\Pages;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


use function DebugLogViewer\Admin\Controllers\dbg_lv;

class LogView
{

	public static function render()
	{
		$current_tab = isset($_GET['tab']) ? \sanitize_text_field(\wp_unslash($_GET['tab'])) : 'log-view';

		// Get Freemius values
		$is_premium = $checkout_url = false;
		try {
			$fs = dbg_lv();
			if ($fs && is_object($fs)) {
				$is_premium = $fs->is_premium();
				$checkout_url = $fs->checkout_url();
			}
		} catch (\Exception $e) {
			$is_premium = $checkout_url = false;
		}
?>
		<script>
			// Freemius data is now loaded via HooksController::dbg_lv_admin_assets_enqueue
			// Ensure compatibility by merging data if needed
			if (window.dbg_lv_backend_data && window.dbg_lv_freemius_data) {
				Object.assign(window.dbg_lv_backend_data, window.dbg_lv_freemius_data);
			}
		</script>

		<header class="app-header">
			<div class="container">
				<div class="header-wrapper">
					<div class=" header-left">
						<div class="header-icons tab-buttons">
							<button class="tab-button <?php echo $current_tab === 'log-view' ? 'active' : ''; ?>"
								id="log-view-tab-btn"
								type="button">
								<i class="fa-solid fa-file-lines"></i>
								<span><?php esc_html_e('Log View', 'debug-log-viewer'); ?></span>
							</button>
							<button class="tab-button <?php echo $current_tab === 'settings' ? 'active' : ''; ?>"
								id="settings-tab-btn"
								type="button">
								<i class="fa-solid fa-gear"></i>
								<span><?php esc_html_e('Settings', 'debug-log-viewer'); ?></span>
							</button>
							<button class="tab-button <?php echo $current_tab === 'alerts' ? 'active' : ''; ?>"
								id="alerts-tab-btn"
								type="button">
								<i class="fa-solid fa-message"></i>
								<span><?php esc_html_e('Alerts', 'debug-log-viewer'); ?></span>
							</button>
						</div>
					</div>
					<div class="header-right">
						<!-- Plan Badge -->
						<div class="plan-badge-container">
							<?php if (dbg_lv()->is_premium()): ?>
								<div class="plan-badge plan-badge-pro" title="You have the Pro plan! All features unlocked.">
									<i class="fas fa-crown"></i>
									<span>Pro Plan</span>
								</div>
							<?php else: ?>
								<span class="plan-badge plan-badge-free freemius-checkout-trigger"
									title="Click to upgrade and unlock all Pro features!">
									<i class="fas fa-rocket"></i>
									<span>Upgrade to Pro</span>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</header>

		<?php include_once realpath(__DIR__) . '/log-view.tpl.php'; ?>
		<?php include_once realpath(__DIR__) . '/alerts.tpl.php'; ?>
		<?php include_once realpath(__DIR__) . '/settings.tpl.php'; ?>

		<?php require_once realpath(__DIR__) . '/../components/toast.tpl.php'; ?>

		<?php if (!$is_premium): ?>
			<?php require_once realpath(__DIR__) . '/../components/modals/quick-features.tpl.php'; ?>
		<?php endif; ?>
<?php
	}
}
