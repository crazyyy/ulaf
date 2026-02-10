<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Models\LogModel;

?>

<div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'settings') ? 'show active' : ''; ?>"
	id="settings-content"
	role="tabpanel"
	aria-labelledby="settings-tab">

	<div class="container">
		<div class="settings section">
			<?php
			$dbg_lv_selected_mode = get_option(LogModel::LOG_UPDATES_MODE_OPTION_NAME);
			if (empty($dbg_lv_selected_mode)) {
				$dbg_lv_selected_mode = 'MANUAL';
			}

			$dbg_lv_selected_datetime_format = get_option(LogModel::DATETIME_FORMAT_OPTION_NAME);
			if (empty($dbg_lv_selected_datetime_format)) {
				$dbg_lv_selected_datetime_format = 'ABSOLUTE';
			}
			?>

			<form class="form-group mt-3" id="dbg_lv_log_viewer_settings_form">

				<div class="settings-group mb-4">
					<label class="settings-group-title">
						<?php esc_html_e('The log update mode', 'debug-log-viewer'); ?>
						<i class="fas fa-info-circle tooltip-icon"
							data-toggle="tooltip"
							data-placement="right"
							title="<?php esc_attr_e('Choose how you want the log to update: manually or automatically in real-time', 'debug-log-viewer'); ?>"></i>
					</label>

					<div class="form-check">
						<label class="form-check-label">
							<input class="form-check-input"
								value="<?php echo esc_attr('MANUAL'); ?>"
							<?php checked($dbg_lv_selected_mode, 'MANUAL'); ?>
								type="radio"
								name="UpdatesModeRadioOptions"
								id="<?php echo esc_attr('UpdatesModeRadioOptionsManual'); ?>">
							<span class="form-check-sign"></span>
							<span><?php esc_html_e('Manual', 'debug-log-viewer'); ?></span>
						</label>
					</div>

					<div class="form-check">
						<label class="form-check-label">
							<input class="form-check-input"
								value="<?php echo esc_attr('AUTO'); ?>"
							<?php checked($dbg_lv_selected_mode, 'AUTO'); ?>
								type="radio"
								name="UpdatesModeRadioOptions"
								id="<?php echo esc_attr('UpdatesModeRadioOptionsAuto'); ?>">
							<span class="form-check-sign"></span>
							<span><?php esc_html_e('Auto', 'debug-log-viewer'); ?></span>
						</label>
					</div>
				</div>

				<div class="settings-group mb-4">
					<label class="settings-group-title">
						<?php esc_html_e('Datetime format in logs', 'debug-log-viewer'); ?>
						<i class="fas fa-info-circle tooltip-icon"
							data-toggle="tooltip"
							data-placement="right"
							title="<?php esc_attr_e('Choose how you want the datetime to be displayed: absolute or relative time', 'debug-log-viewer'); ?>"></i>
					</label>

					<div class="form-check">
						<label class="form-check-label">
							<input class="form-check-input"
								value="<?php echo esc_attr('ABSOLUTE'); ?>"
							<?php checked($dbg_lv_selected_datetime_format, 'ABSOLUTE'); ?>
								type="radio"
								name="DatetimeFormatRadioOptions"
								id="<?php echo esc_attr('DatetimeFormatRadioOptionsAbsolute'); ?>">
							<span class="form-check-sign"></span>
							<span><?php esc_html_e('Absolute', 'debug-log-viewer'); ?></span>
						</label>
					</div>

					<div class="form-check">
						<label class="form-check-label">
							<input class="form-check-input"
								value="<?php echo esc_attr('RELATIVE'); ?>"
							<?php checked($dbg_lv_selected_datetime_format, 'RELATIVE'); ?>
								type="radio"
								name="DatetimeFormatRadioOptions"
								id="<?php echo esc_attr('DatetimeFormatRadioOptionsRelative'); ?>">
							<span class="form-check-sign"></span>
							<span><?php esc_html_e('Relative', 'debug-log-viewer'); ?></span>
						</label>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
