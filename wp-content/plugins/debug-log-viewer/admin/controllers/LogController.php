<?php

namespace DebugLogViewer\Admin\Controllers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Traits\ScheduleTrait;
use DebugLogViewer\Admin\Models\LogModel;
use DebugLogViewer\Admin\Helpers\Utils;

require_once realpath(__DIR__) . '/../../vendor/autoload.php';
require_once realpath(__DIR__) . '/LiveUpdatesController.php';
require_once realpath(__DIR__) . '/../../admin/models/LogModel.php';
require_once realpath(__DIR__) . '/../../front/views/pages/index.tpl.php';
require_once realpath(__DIR__) . '/../../admin/helpers/utils.php';
require_once realpath(__DIR__) . '/../../admin/traits/ScheduleTrait.php';
require_once realpath(__DIR__) . '/../../admin/services/Email.php';

class LogController {

	use ScheduleTrait;

	const SCHEDULE_MAIL_SEND = 'DBG_LV_NOTIFY_LOG_CONTROLLER';

	private $configEditor;
	private $logModel;
	private $notificationController;

	public function __construct() {

		$this->logModel               = new LogModel();
		$this->notificationController = new AlertController();
		try {
			$this->configEditor = new \WPConfigTransformer($this->logModel->getWpConfigPath());
		} catch (\Exception $error) {
			// Please, make sure permissions and path is correct.
			// The plugin need an access to wp-config.php to manage debugging constants
		}
	}

	public static function isCustomLoggingPath() {
		return defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG) && !in_array(WP_DEBUG_LOG, [ '1', '0', 'true', 'false' ], true) && !empty(WP_DEBUG_LOG);
	}

	public function getLogFilePath() {
		return self::isCustomLoggingPath()
			? WP_DEBUG_LOG
			: WP_CONTENT_DIR . '/debug.log';
	}

	public function firstRunEnableLogging() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		try {

			$path = $this->getLogFilePath();
			$this->configEditor->update('constant', 'WP_DEBUG', '1');

			if (!self::isCustomLoggingPath()) {
				$this->configEditor->update('constant', 'WP_DEBUG_LOG', '1');
			}

			if (filesize($path) == 0) {
				$message     = __('This is a demo entry. Debugging is enabled. Any notices, warnings, or errors that occur on your site will appear here.', 'debug-log-viewer');
				$demo_string = '[' . gmdate('d-M-Y H:i:s T') . '] PHP Notice: <b>' . $message . '</b>  in ' . Utils::getDocumentRoot() . "/example.php on line 0\n";
				file_put_contents($path, $demo_string);
			}

			wp_send_json_success();
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function toggleDebugMode() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$state = $this->dbg_lv_prepare_state();

		try {
			$this->configEditor->update('constant', 'WP_DEBUG', $state);

			wp_send_json_success([
				'state' => $this->stateToText($state),
			]);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function toggleDebugScripts() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$state = $this->dbg_lv_prepare_state();

		try {
			$this->configEditor->update('constant', 'SCRIPT_DEBUG', $state);

			wp_send_json_success([
				'state' => $this->stateToText($state),
			]);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function toggleLogInFile() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$state = $this->dbg_lv_prepare_state();

		try {
			if ($state == '1') {
				$this->configEditor->update('constant', 'WP_DEBUG', $state);
			}

			$this->configEditor->update('constant', 'WP_DEBUG_LOG', $state);

			wp_send_json_success([
				'state' => $this->stateToText($state),
			]);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function toggleDisplayErrors() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$state = $this->dbg_lv_prepare_state();

		try {
			if ($state == '1') {
				$this->configEditor->update('constant', 'WP_DEBUG', $state);
			}

			$this->configEditor->update('constant', 'WP_DEBUG_DISPLAY', $state);

			wp_send_json_success([
				'state' => $this->stateToText($state),
			]);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function clear() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		try {
			$path = $this->getLogFilePath();

			if (is_file($path) && file_exists($path)) {

				if (is_writable($path)) {
					file_put_contents($path, '');

					update_option(LogModel::LAST_POSITION_OPTION_NAME, 0); // reset the stored position

					wp_send_json_success();
				}

				throw new \Exception(__('The log file was found but cannot be cleared due to missing write permissions', 'debug-log-viewer'));
			}
			throw new \Exception(__('The log file was not found and cannot be removed', 'debug-log-viewer'));
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function download() {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		try {
			$path = $this->getLogFilePath();

			if (is_file($path) && file_exists($path)) {

				$basename = basename($path);
				$filesize = filesize($path);

				header('Content-Description: File Transfer');
				header('Content-Type: text/plain');
				header('Cache-Control: no-cache, must-revalidate');
				header('Expires: 0');
				header("Content-Disposition: attachment; filename=$basename");
				header("Content-Length: $filesize");
				header('Pragma: public');

				flush();

				readfile($path);
				wp_die();
			}

			throw new \Exception(__('The log file was not found and cannot be removed', 'debug-log-viewer'));
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public static function getCurrentUserEmail() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');

		try {
			global $current_user;

			wp_send_json_success($current_user->user_email);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	public function dbg_lv_prepare_state() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');

		if (!isset($_POST['state'])) {
			throw new \Exception('Empty state passed');
		}

		$state = sanitize_text_field(wp_unslash($_POST['state']));
		switch ( (int) $state) {
			case 0:
			case 1:
				return (string) $state;
			default:
				throw new \Exception(esc_html__('An incorrect state value was passed', 'debug-log-viewer'));
		}
	}

	public function getEntriesByLevel( $recurrence ) {
		$rows = $this->logModel->getParsedContent();

		if (!$rows) {
			return;
		}

		$errors = [];
		foreach ($this->notificationController->getLevelsToReport() as $level) {
			$errors[ $level ] = [];
		}

		$new_lines = 0;
		foreach ($rows as $row) {
			if (empty($row)) {
				continue;
			}

			$datetime       = LogModel::getDatetime($row);
			$datetimeOffset = new \DateTime();
			$datetimeOffset->sub($this->getScheduleInterval($recurrence));
			$datetimeError = new \DateTime();
			$strToTime      = strtotime($datetime);
			$datetimeError->setTimestamp($strToTime);

			if ($datetimeError < $datetimeOffset) {
				continue;
			}

			$type = LogModel::getType($row);

			if (!in_array($type, $this->notificationController->getLevelsToReport())) {
				continue;
			}

			$line = LogModel::getLine($row);
			$file = LogModel::getFile($row);
			$text = LogModel::getDescription($row);
			$hash = md5($line . '::' . $file . '::' . $text);

			if (array_key_exists($hash, $errors[ $type ])) {
				$errors[ $type ][ $hash ]['hits'] += 1;
			} else {
				$errors[ $type ][ $hash ] = [
					'timestamp'   => $strToTime * 1000,
					'datetime'    => $datetime,
					'line'        => $line,
					'file'        => $file,
					'type'        => $type,
					'description' => [
						'text'        => $text,
						'stack_trace' => LogModel::getStackTrace($row),
					],
					'hits'        => 1,
				];
			}
			$new_lines += 1;
		}

		return $new_lines ? $errors : null;
	}

	public function changeLogsUpdateMode() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$mode = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : null;

		if (!$mode) {
			wp_send_json_error(__('The "mode" parameter is missing from the request', 'debug-log-viewer'), 400);
		}

		$allowed_modes = [ 'AUTO', 'MANUAL' ];
		if (!in_array($mode, $allowed_modes, true)) {
			wp_send_json_error(__('The specified mode is invalid. Please select either "AUTO" or "MANUAL"', 'debug-log-viewer'), 400);
		}

		// Retrieve the current mode setting
		$current_mode = get_option(LogModel::LOG_UPDATES_MODE_OPTION_NAME);
		if ($current_mode === $mode) {
			// If the current value matches the new value, consider it a success
			wp_send_json_success(__('The updates mode has been successfully updated', 'debug-log-viewer'));
		}

		// Attempt to update the mode setting
		if (update_option(LogModel::LOG_UPDATES_MODE_OPTION_NAME, $mode)) {
			wp_send_json_success(__('The update mode has been successfully updated', 'debug-log-viewer'));
		} else {
			wp_send_json_error(__('An error occurred while updating the update mode. Please try again', 'debug-log-viewer'), 500);
		}
	}

	public function changeDatetimeFormat() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		$format = isset($_POST['format']) ? sanitize_text_field(wp_unslash($_POST['format'])) : null;

		if (!$format) {
			wp_send_json_error(__('The "format" parameter is missing from the request', 'debug-log-viewer'), 400);
		}

		$allowed_formats = [ 'ABSOLUTE', 'RELATIVE' ];
		if (!in_array($format, $allowed_formats, true)) {
			wp_send_json_error(__('The specified format is invalid. Please select either "ABSOLUTE" or "RELATIVE"', 'debug-log-viewer'), 400);
		}

		// Retrieve the current format setting
		$current_format = get_option(LogModel::DATETIME_FORMAT_OPTION_NAME);
		if ($current_format === $format) {
			// If the current value matches the new value, consider it a success
			wp_send_json_success(__('The datetime format has been successfully updated', 'debug-log-viewer'));
		}

		// Attempt to update the format setting
		if (update_option(LogModel::DATETIME_FORMAT_OPTION_NAME, $format)) {
			wp_send_json_success(__('The datetime format has been successfully updated', 'debug-log-viewer'));
		} else {
			wp_send_json_error(__('An error occurred while updating the datetime format. Please try again', 'debug-log-viewer'), 500);
		}
	}

	public function dbg_lv_is_debug_log_publicly_accessible() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');
		Utils::verifyAdminCapability();

		try {
			$path = $this->getLogFilePath();

		// If file doesn't exist, it's not accessible
		if (!file_exists($path)) {
			wp_send_json_success(['is_public' => false]);
		}
			// Convert file path to URL
			$site_url  = site_url();
			$site_path = wp_normalize_path(ABSPATH);
			$log_path  = wp_normalize_path($path);

			// Only proceed if the log is within the WordPress directory structure
			if (strpos($log_path, $site_path) === 0) {
				// Get the relative path and convert to URL
				$relative_path = str_replace($site_path, '', $log_path);
				$log_url       = trailingslashit($site_url) . ltrim($relative_path, '/');

				// Use HEAD request instead of GET (much more efficient)
				$response = wp_remote_head($log_url, [
					'timeout'     => 3, // Short timeout is sufficient for HEAD request
					'sslverify'   => false,
					'redirection' => 0,
				]);

				if (!is_wp_error($response)) {
					$is_public = wp_remote_retrieve_response_code($response) === 200;
					// If the response is 200, the file is publicly accessible
					if ($is_public) {
						wp_send_json_success([
							'is_public' => true,
							'info' => [
								'icon' => 'fa-globe',
								'type' => 'danger',
								'title' => __('Public Access', 'debug-log-viewer'),
								'message' => __('Log file is publicly accessible via URL', 'debug-log-viewer'),
								'key' => 'dbg_lv_public_log_alert_dismissed',
							]
						]);
					} else {
						// If the response is not 200, the file is not publicly accessible
						wp_send_json_success(['is_public' => false]);
					}
				}
			}

			wp_send_json_success(['is_public' => false]);
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	private function stateToText( $state ): string {
		return (int) $state ? __('ON', 'debug-log-viewer') : __('OFF', 'debug-log-viewer');
	}

	public function getLogFileWarnings() {
		$path = $this->getLogFilePath();

		// Get file information and check for warnings
		$file_exists = file_exists($path);
		if(!$file_exists) {
			return [];
		}

		$warnings = [];

		// Check file size
		$file_size = filesize($path);
		if ($file_size > LogModel::LOG_FILE_LIMIT) {
			$limit = $this->logModel->getLogLimit(LogModel::UNIT_MEGABYTES, true);

			$warnings[] = [
				'icon' => 'fa-database',
				'type' => 'warning',
				'title' => __('Large File Size', 'debug-log-viewer'),
				'message' => sprintf(__('The file exceeds the %s limit, only the last %s will be displayed.', 'debug-log-viewer'), $limit, $limit)
			];
		}

		return $warnings;
	}

	public function getWarningsSeverity() {
		$warnings = $this->getLogFileWarnings();

		if (empty($warnings)) {
			return 'none';
		}

		// Check for danger level warnings (public access)
		foreach ($warnings as $warning) {
			if ($warning['type'] === 'danger') {
				return 'danger';
			}
		}

		// Default level is warning
		return 'warning';
	}

	/**
	 * Get popover data for JavaScript rendering
	 * @return array Data for wp_localize_script
	 */
	public function getPopoverData() {
		$path = $this->getLogFilePath();
		$file_exists = file_exists($path);

		$fileInfo = [];

		if ($file_exists) {
			$last_modified = filemtime($path);
			$file_size = $this->logModel->getLogSize(['with_measure_units' => true]);
			$permissions = substr(sprintf('%o', fileperms($path)), -4);
			$is_readable = is_readable($path);
			$is_writable = is_writable($path);

			$fileInfo = [
				'fileSize' => $file_size,
				'lastModified' => $last_modified ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_modified) : __('Unknown', 'debug-log-viewer'),
				'permissions' => $permissions . ' (' . ($is_readable ? 'R' : '-') . ($is_writable ? 'W' : '-') . ')',
			];
		}

		$warnings = $this->getLogFileWarnings();

		return [
			'fileInfo' => $fileInfo,
			'warnings' => $warnings
		];
	}
}
