<?php

namespace DebugLogViewer\Admin\Traits;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Controllers\LogController;
use DebugLogViewer\Admin\Controllers\AlertController;
use DebugLogViewer\Admin\Helpers\Utils;

trait ScheduleTrait {

	public function getScheduleInterval( $recurrence ) {
		$intervals =  [
			'hourly'     => new \DateInterval('PT1H'),
			'twicedaily' => new \DateInterval('PT12H'),
			'daily'      => new \DateInterval('P1D'),
			'weekly'     => new \DateInterval('P7D'),
		];
		if (array_key_exists($recurrence, $intervals)) {
			return $intervals[ $recurrence ];
		} else {
			throw new \Exception('Invalid recurrence value');
		}
	}

	// Fire if the notification is enabled
	public function addWpScheduleEvent( $event, $options ) {

		$action     = $options['action'];
		$recurrence = $options['dbg_lv_notifications_email_recurrence'];

		$time = new \DateTime();
		$time->add($this->getScheduleInterval($recurrence));

		if (!wp_next_scheduled($action)) {
			wp_schedule_event($time->getTimestamp(), $recurrence, $action, [ $event ]);
		}

		update_option($event, $options);
	}

	// Fire if the notification is disabled or on plugin deactivation (uninstalling)
	public function deleteWpScheduleEvent( $event ) {
		$options = get_option($event);

		if (!$options) {
			error_log('Options not found for event ' . $event);
			// Don't send JSON error during deactivation/uninstall
			if (wp_doing_ajax()) {
				wp_send_json_error(__('Internal error', 'debug-log-viewer'));
			}
			return;
		}

		if (!array_key_exists('action', $options)) {
			error_log('Action not found in options for event ' . $event);
			// Don't send JSON error during deactivation/uninstall
			if (wp_doing_ajax()) {
				wp_send_json_error(__('Internal error', 'debug-log-viewer'));
			}
			return;
		}

		if (wp_next_scheduled($options['action'], [ $event ])) {
			wp_clear_scheduled_hook($options['action'], [ $event ]);
		}

		delete_option($event);
	}

	public function dbg_lv_change_notifications_status() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		Utils::verifyNonce(isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '');

		try {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$emails          = isset($_POST['emails']) ? array_map('sanitize_email', wp_unslash($_POST['emails'])) : [];
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$status          = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : null;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$send_test_email = isset($_POST['send_test_email']) ? (bool) sanitize_text_field(wp_unslash($_POST['send_test_email'])) : null;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$recurrence      = isset($_POST['recurrence']) ? sanitize_text_field(wp_unslash($_POST['recurrence'])) : null;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$levels          = isset($_POST['levels']) ? array_map('sanitize_text_field', wp_unslash($_POST['levels'])) : [];

			$emails = array_filter($emails, 'is_email');

			if (empty($emails)) {
				wp_send_json_error(__('At least one valid email address is required', 'debug-log-viewer'));
			}

			if (!$status) {
				wp_send_json_error(__('Alert status was not provided', 'debug-log-viewer'));
			}

			if ($status == 'enable' && empty($levels)) {
				wp_send_json_error(__('At least one error level must be selected', 'debug-log-viewer'));
			}

			$notificator = new AlertController();
			// Validate field recurrence only if alerts are turn on
			if ($status == 'enable' && ( !isset($recurrence) || !array_key_exists($recurrence, $notificator->emailRecurrences) )) {
				wp_send_json_error(__('An invalid value received', 'debug-log-viewer'));
			}

			$event = $notificator->buildUniqueEventName();

			$options = [
				'dbg_lv_notifications_emails'           => $emails,
				'dbg_lv_notifications_email_recurrence' => $recurrence,
				'dbg_lv_notifications_levels'           => $levels,
				'action'                                => $notificator->action,
			];

			switch ($status) {
				case 'enable':
					if ($send_test_email === true) {
						$notificator->dbg_lv_send_test_email($options);
					}
					( new LogController() )->addWpScheduleEvent($event, $options);
					break;
				case 'disable':
					( new LogController() )->deleteWpScheduleEvent($event);
					break;
			}
			wp_send_json_success();
		} catch (\Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}
}
