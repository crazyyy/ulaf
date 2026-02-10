<?php

namespace DebugLogViewer\Admin\Controllers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Controllers\LogController;

class ServiceController {


	private static function cleanupScheduledEvents() {
		global $wpdb;
		
		// Find all options matching the pattern for scheduled events (all users)
		$pattern = strtoupper(LogController::SCHEDULE_MAIL_SEND . '_user_%');
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$pattern
			)
		);
		
		// Delete each scheduled event properly
		foreach ($options as $option) {
			$event_name = $option->option_name;
			( new LogController() )->deleteWpScheduleEvent($event_name);
		}
		
		// Fallback: clear any remaining hooks
		wp_clear_scheduled_hook(LogController::SCHEDULE_MAIL_SEND);
		delete_option(LiveUpdatesController::DEBUG_LOG_LAST_FILESIZE);
	}

	public static function deactivationHandler() {
		self::cleanupScheduledEvents();
	}

	public static function uninstallHandler() {
		self::cleanupScheduledEvents();
	}
}
