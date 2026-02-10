<?php

namespace DebugLogViewer\Admin\Controllers;

use DebugLogViewer\Admin\Services\EmailService;
use DebugLogViewer\Admin\Models\LogModel;

if (!defined('ABSPATH')) {
	exit;
	// Exit if accessed directly
}

class AlertController {


	public $action;
	public $options;
	public $emailRecurrences;
	public $sendTestEmailHandler;

	public function __construct() {
		$this->action               = LogController::SCHEDULE_MAIL_SEND;
		$this->sendTestEmailHandler = [ $this, 'sendTestEmail' ];

		$this->emailRecurrences = [
			'hourly'     => __('Hourly', 'debug-log-viewer'),
			'twicedaily' => __('Twice Daily', 'debug-log-viewer'),
			'daily'      => __('Daily', 'debug-log-viewer'),
			'weekly'     => __('Weekly', 'debug-log-viewer'),
		];
		$this->options          = get_option($this->buildUniqueEventName());
	}

	public function buildUniqueEventName() {
		return strtoupper($this->action . '_user_' . \wp_get_current_user()->ID);
	}

	public function getNotificationEmails() {
		if ($this->options && array_key_exists('dbg_lv_notifications_emails', $this->options)) {
			return $this->options['dbg_lv_notifications_emails'];
		}

		return [];
	}

	public function isEnabled() {
		return (bool) $this->options && array_key_exists('dbg_lv_notifications_emails', $this->options);
	}

	public function getRecurrence() {
		foreach ($this->emailRecurrences as $key => $value) {
			$selected = $key == $this->getRecurrences() ? 'selected="selected"' : '';
			printf('<option value="%s" %s>%s</option>', esc_attr($key), esc_html($selected), esc_html($value));
		}
	}

	private function getRecurrences() {
		if (!$this->options) {
			return null;
		}

		if (array_key_exists('dbg_lv_notifications_email_recurrence', $this->options)) {
			return $this->options['dbg_lv_notifications_email_recurrence'];
		}
		return null;
	}

	public function getLevelsToReport() {
		if ($this->options && array_key_exists('dbg_lv_notifications_levels', $this->options)) {
			return $this->options['dbg_lv_notifications_levels'];
		}

		// Default levels if none are configured
		return [
			LogModel::LOG_LEVEL_DATABASE,
			LogModel::LOG_LEVEL_FATAL,
			LogModel::LOG_LEVEL_PARSE,
			LogModel::LOG_LEVEL_DEPRECATED,
		];
	}

	public function getAllAvailableLevels() {
		return [
			LogModel::LOG_LEVEL_DATABASE => __('Database Errors', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_FATAL => __('Fatal Errors', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_PARSE => __('Parse Errors', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_DEPRECATED => __('Deprecated', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_WARNING => __('Warnings', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_NOTICE => __('Notices', 'debug-log-viewer'),
			LogModel::LOG_LEVEL_CUSTOM => __('Custom', 'debug-log-viewer'),
		];
	}

	public function getSelectedLevels() {
		// Always return the levels that should be selected (either saved or defaults)
		return $this->getLevelsToReport();
	}

	private function sendTestEmail( $args ) {
		$emails = $args['dbg_lv_notifications_emails'] ?? [];

		if (empty($emails)) {
			return;
		}

		$entries = [];
		foreach ($this->getLevelsToReport() as $type) {
			$timestamp = new \DateTime();
			$datetime  = $timestamp->format('Y-m-d H:i:s e');
			$text      = $type . ' error test description';
			$hash      = md5($text . '::' . $datetime);

			$entries[ $type ][ $hash ] = [
				'datetime'    => $datetime,
				'line'        => wp_rand(1, 100),
				'file'        => 'example.php',
				'type'        => $type,
				'description' => [
					'text'        => $text,
					'stack_trace' => null,
				],
				'hits'        => 1,
			];
		}

		$emailService = new EmailService();
		$emailService->setEmails($emails);
		$emailService->setSubject(__('Debug Log Viewer: Log Monitoring Test Email', 'debug-log-viewer'));
		$emailService->prepare(
			'monitoring.tpl',
			[
				'website' => get_site_url(),
				'entries' => $entries,
			]
		);
		$emailService->send();
	}

	public function dbg_lv_send_test_email( $options ) {
		if (isset($this->sendTestEmailHandler) && is_callable($this->sendTestEmailHandler)) {
			call_user_func($this->sendTestEmailHandler, $options);
		}
	}

	public function dbg_lv_send_logs_handler( $event ) {
		$options = get_option($event);

		if (!$options) {
			// Clean up orphaned scheduled event and exit silently
			wp_clear_scheduled_hook($this->action, [ $event ]);
			return;
		}

		$emails = $options['dbg_lv_notifications_emails'] ?? [];

		if (empty($emails)) {
			wp_clear_scheduled_hook($this->action, [ $event ]);
			error_log(__('Alert email was not found in the options for the event ', 'debug-log-viewer') . $event);
			return;
		}

		if (!array_key_exists('dbg_lv_notifications_email_recurrence', $options)) {
			wp_clear_scheduled_hook($this->action, [ $event ]);
			error_log(__('Alert email recurrence was not found in the options for the event ', 'debug-log-viewer') . $event);
			return;
		}

		$recurrence = $options['dbg_lv_notifications_email_recurrence'];

		$entries = ( new LogController() )->getEntriesByLevel($recurrence);

		if (!$entries) {
			return;
		}

		$emailService = new EmailService();
		$emailService->setEmails($emails);
		$emailService->setSubject(__('Debug Log Viewer: Monitoring has detected some problems on your website', 'debug-log-viewer'));
		$emailService->prepare(
			'monitoring.tpl',
			[
				'website' => get_site_url(),
				'entries' => $entries,
			]
		);
		$emailService->send();
	}

	public function getNextScheduledTime() {
		if (!$this->isEnabled()) {
			return null;
		}

		$event_name = $this->buildUniqueEventName();
		$timestamp  = wp_next_scheduled($this->action, [ $event_name ]);

		if ($timestamp) {
			return $timestamp;
		}

		return null;
	}

	public function getFormattedNextScheduledTime() {
		$timestamp = $this->getNextScheduledTime();

		if (!$timestamp) {
			return __('Not scheduled', 'debug-log-viewer');
		}

		$datetime = new \DateTime();
		$datetime->setTimestamp($timestamp);
		$datetime->setTimezone(new \DateTimeZone(wp_timezone_string()));

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');

		return sprintf(
			/* translators: %1$s: date, %2$s: time */
			__('Next run: %1$s at %2$s', 'debug-log-viewer'),
			$datetime->format($date_format),
			$datetime->format($time_format)
		);
	}

	public function getTimeUntilNextEmail() {
		$timestamp = $this->getNextScheduledTime();

		if (!$timestamp) {
			return null;
		}

		// Use time() instead of current_time('timestamp') to get UTC timestamp
		$current_time = time();
		$time_diff    = $timestamp - $current_time;

		if ($time_diff <= 0) {
			return __('Sending soon...', 'debug-log-viewer');
		}

		$days    = floor($time_diff / 86400); // 86400 seconds in a day
		$hours   = floor(( $time_diff % 86400 ) / 3600);
		$minutes = floor(( $time_diff % 3600 ) / 60);

		if ($days > 0) {
			if ($hours > 0) {
				return sprintf(
					/* translators: %d: number of days */
					_n('%d day', '%d days', $days, 'debug-log-viewer') . ' ' .
						/* translators: %d: number of hours */
						_n('%d hour', '%d hours', $hours, 'debug-log-viewer'),
					$days,
					$hours
				);
			} else {
				return sprintf(
					/* translators: %d: number of days */
					_n('%d day', '%d days', $days, 'debug-log-viewer'),
					$days
				);
			}
		} elseif ($hours > 0) {
			return sprintf(
				/* translators: %d: number of hours */
				_n('%d hour', '%d hours', $hours, 'debug-log-viewer'),
				$hours
			);
		} else {
			return sprintf(
				/* translators: %d: number of minutes */
				_n('%d minute', '%d minutes', $minutes, 'debug-log-viewer'),
				$minutes
			);
		}
	}

	public function getNextScheduledTimeForAjax() {
		$timestamp = $this->getNextScheduledTime();

		if (!$timestamp) {
			return [
				'success'   => true,
				'scheduled' => false,
				'message'   => __('Not scheduled', 'debug-log-viewer'),
			];
		}

		return [
			'success'        => true,
			'scheduled'      => true,
			'timestamp'      => $timestamp,
			'formatted_time' => $this->getFormattedNextScheduledTime(),
			'time_until'     => $this->getTimeUntilNextEmail(),
		];
	}
}
