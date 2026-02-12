<?php

namespace DebugLogViewer\Admin\Translations;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Phrases {

	private static $phrases = [];

	private static function init() {
		if (empty(self::$phrases)) {
			self::$phrases = [
				'loading_in_process'           => __('Loading...', 'debug-log-viewer'),
				'filter'                       => __('Filter', 'debug-log-viewer'),
				'stack_trace'                  => __('Stack Trace', 'debug-log-viewer'),
				'show_stack_trace'             => __('Show Stack Trace', 'debug-log-viewer'),
				'hide_stack_trace'             => __('Hide Stack Trace', 'debug-log-viewer'),
				'debug_mode'                   => __('Debug Mode:', 'debug-log-viewer'),
				'request_error'                => __('Request Error:', 'debug-log-viewer'),
				'debug_log_scripts'            => __('Debug Log Scripts:', 'debug-log-viewer'),
				'logging_enabled_successfully' => __('Logging enabled successfully.', 'debug-log-viewer'),
				'debug_scripts'                => __('Debug Scripts:', 'debug-log-viewer'),
				'display_errors'               => __('Display Errors:', 'debug-log-viewer'),
				'flush_log_confirmation'       => __('Are you sure? This action cannot be undone after flushing the log.', 'debug-log-viewer'),
				'log_was_cleared'              => __('The log was cleared.', 'debug-log-viewer'),
				'log_was_refreshed'            => __('The log was refreshed.', 'debug-log-viewer'),
				'email_is_not_specified'       => __('Email is not specified.', 'debug-log-viewer'),
				'notifications_disabled'       => __('Alerts have been disabled.', 'debug-log-viewer'),
				'disable'                      => __('Disable', 'debug-log-viewer'),
				'enable'                       => __('Enable', 'debug-log-viewer'),
				'notifications_enabled'        => __('Alerts have been enabled.', 'debug-log-viewer'),
				'more'                         => __('More', 'debug-log-viewer'),
				'show-hide'                    => __('Show/Hide', 'debug-log-viewer'),
				'clear_log'                    => __('Clear Log', 'debug-log-viewer'),
				'download_log'                 => __('Download Log', 'debug-log-viewer'),
				'refresh_log'                  => __('Refresh Log', 'debug-log-viewer'),
				'empty_log_table'              => __('The debug log file is empty - no log entries found', 'debug-log-viewer'),
				'no_matching_records'          => __('No log entries match the current filtering criteria', 'debug-log-viewer'),

				'day' 						   => __('day', 'debug-log-viewer'),
				'days'						   => __('days', 'debug-log-viewer'),
				'hour' 						   => __('hour', 'debug-log-viewer'),
				'hours'						   => __('hours', 'debug-log-viewer'),
				'minute' 					   => __('minute', 'debug-log-viewer'),
				'minutes'					   => __('minutes', 'debug-log-viewer'),
				'in' 					       => __('in', 'debug-log-viewer'),
				'export_csv'				   => __('Export CSV', 'debug-log-viewer'),
				'datetime_format_in_logs'      => __('Datetime format in logs', 'debug-log-viewer'),
				'datetime_format_tooltip'      => __('Choose how you want the datetime to be displayed: absolute or relative time', 'debug-log-viewer'),
				'absolute'                     => __('Absolute', 'debug-log-viewer'),
				'relative'                     => __('Relative', 'debug-log-viewer'),

				// Relative time phrases
				'just_now'                     => __('just now', 'debug-log-viewer'),
				'one_minute_ago'               => __('1 minute ago', 'debug-log-viewer'),
				'two_minutes_ago'              => __('2 minutes ago', 'debug-log-viewer'),
				'five_minutes_ago'             => __('5 minutes ago', 'debug-log-viewer'),
				'ten_minutes_ago'              => __('10 minutes ago', 'debug-log-viewer'),
				'fifteen_minutes_ago'          => __('15 minutes ago', 'debug-log-viewer'),
				'thirty_minutes_ago'           => __('30 minutes ago', 'debug-log-viewer'),
				'one_hour_ago'                 => __('1 hour ago', 'debug-log-viewer'),
				'two_hours_ago'                => __('2 hours ago', 'debug-log-viewer'),
				'three_hours_ago'              => __('3 hours ago', 'debug-log-viewer'),
				'six_hours_ago'                => __('6 hours ago', 'debug-log-viewer'),
				'twelve_hours_ago'             => __('12 hours ago', 'debug-log-viewer'),
				'one_day_ago'                  => __('1 day ago', 'debug-log-viewer'),
				'two_days_ago'                 => __('2 days ago', 'debug-log-viewer'),
				'three_days_ago'               => __('3 days ago', 'debug-log-viewer'),
				'one_week_ago'                 => __('1 week ago', 'debug-log-viewer'),
				'two_weeks_ago'                => __('2 weeks ago', 'debug-log-viewer'),
				'one_month_ago'                => __('1 month ago', 'debug-log-viewer'),
				'two_months_ago'               => __('2 months ago', 'debug-log-viewer'),
				'three_months_ago'             => __('3 months ago', 'debug-log-viewer'),
				'six_months_ago'               => __('6 months ago', 'debug-log-viewer'),
				'one_year_ago'                 => __('1 year ago', 'debug-log-viewer'),
				/* translators: %d: number of years */
				'years_ago'                    => __('%d years ago', 'debug-log-viewer'),

				// Email validation phrases
				'add_another_email'            => __('Add another email', 'debug-log-viewer'),
				'remove_this_email'            => __('Remove this email', 'debug-log-viewer'),
				'at_least_one_email_required'  => __('At least one email address is required', 'debug-log-viewer'),
				/* translators: %d: email number/position in the list */
				'email_not_valid'              => __('Email %d is not valid', 'debug-log-viewer'),
				'please_select_error_level'    => __('Please select at least one error level to monitor', 'debug-log-viewer'),
				'email_alerts_form_not_found'  => __('Email alerts form not found', 'debug-log-viewer'),

				// Custom date range phrases
				'custom_date_range'            => __('Custom Date Range', 'debug-log-viewer'),
				'from_date'                    => __('From:', 'debug-log-viewer'),
				'to_date'                      => __('To:', 'debug-log-viewer'),
				'apply_date_range'             => __('Apply', 'debug-log-viewer'),
				'cancel_date_range'            => __('Cancel', 'debug-log-viewer'),
				'select_both_dates'            => __('Please select both from and to dates', 'debug-log-viewer'),
				'invalid_date_range'           => __('From date cannot be later than to date', 'debug-log-viewer'),
				'future_date_not_allowed'      => __('Future dates are not allowed. Please select dates up to the current time.', 'debug-log-viewer'),
				'custom_date_range_pro'        => __('Custom date range filtering is a Pro feature', 'debug-log-viewer'),

				// Log file info phrases
				'log_file_information'         => __('Log File Information', 'debug-log-viewer'),
				'last_modified'                => __('Last modified', 'debug-log-viewer'),
				'file_size'                    => __('File size', 'debug-log-viewer'),
				'permissions'                  => __('Permissions', 'debug-log-viewer'),
				'log_file_status_warnings'     => __('Log File Status & Warnings', 'debug-log-viewer'),

				// File size units
				'bytes'                        => __('bytes', 'debug-log-viewer'),
				'kilobytes'                    => __('kilobytes', 'debug-log-viewer'),
				'megabytes'                    => __('megabytes', 'debug-log-viewer'),
				'gigabytes'                    => __('gigabytes', 'debug-log-viewer'),

				'upgrade_to_pro'               => __('Upgrade to Pro', 'debug-log-viewer')
			];
		}
	}

	public static function getParsedContentPhrases() {
		self::init(); // Ensure phrases are initialized before returning
		return self::$phrases;
	}
}
