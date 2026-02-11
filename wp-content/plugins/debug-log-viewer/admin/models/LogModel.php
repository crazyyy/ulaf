<?php

namespace DebugLogViewer\Admin\Models;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use DebugLogViewer\Admin\Controllers\LogController;
use DebugLogViewer\Admin\Translations\Phrases;
use DebugLogViewer\Admin\Helpers\Utils;

class LogModel
{

	// Log level constants
	const LOG_LEVEL_NOTICE     = 'Notice';
	const LOG_LEVEL_WARNING    = 'Warning';
	const LOG_LEVEL_FATAL      = 'Fatal';
	const LOG_LEVEL_DATABASE   = 'Database';
	const LOG_LEVEL_PARSE      = 'Parse';
	const LOG_LEVEL_DEPRECATED = 'Deprecated';
	const LOG_LEVEL_CUSTOM     = 'Custom';

	const LAST_POSITION_OPTION_NAME    = 'dbg_lv_log_last_position';
	const LOG_UPDATES_MODE_OPTION_NAME = 'dbg_lv_log_updates_mode';
	const DATETIME_FORMAT_OPTION_NAME  = 'dbg_lv_datetime_format';
	const DATETIME_FORMAT_ABSOLUTE     = 'ABSOLUTE';
	const DATETIME_FORMAT_RELATIVE     = 'RELATIVE';
	const LOG_FILE_LIMIT               = 10 * 1024 * 1024; // 10 MB
	const LOG_UPDATES_INTERVAL         = 10; // 10 seconds

	// File size units constants
	const UNIT_BYTES      = 'bytes';
	const UNIT_KILOBYTES = 'kilobytes';
	const UNIT_MEGABYTES = 'megabytes';
	const UNIT_GIGABYTES = 'gigabytes';

	public function getWpConfigPath() {
		// Starting from the current directory
		$dir = __DIR__;

		// Traverse up to 10 levels to avoid infinite loops
		for ($i = 0; $i < 10; $i++) {
			if (file_exists($dir . '/wp-config.php')) {
				return realpath($dir . '/wp-config.php');
			}
			// Move up one directory level
			$dir = dirname($dir);
		}
		return 'wp-config.php not found!';
	}

	public function getLogLimit($unit = self::UNIT_MEGABYTES, $with_units = false) {
		// Validate unit parameter
		$allowed_units = [
			self::UNIT_BYTES,
			self::UNIT_KILOBYTES,
			self::UNIT_MEGABYTES,
			self::UNIT_GIGABYTES
		];

		if (!in_array($unit, $allowed_units, true)) {
			// Fallback to default unit if invalid value provided
			$unit = self::UNIT_MEGABYTES;
		}

		$limit_in_bytes = defined('DBG_LV_USER_DEFINED_LOG_FILE_LIMIT')
			? constant('DBG_LV_USER_DEFINED_LOG_FILE_LIMIT')
			: static::LOG_FILE_LIMIT;

		$converted_value = $this->convertBytesToUnit($limit_in_bytes, $unit);

		if ($with_units) {
			$phrases = Phrases::getParsedContentPhrases();
			$unit_key = $unit;
			$unit_label = isset($phrases[$unit_key]) ? $phrases[$unit_key] : $unit;
			return $converted_value . ' ' . $unit_label;
		}

		return $converted_value;
	}

	public function getInitialLogPosition() {
		// Calculate the initial log position based on the file size and the log limit

		$initial_position = $this->getLogSize([ 'raw' => true ]) - $this->getLogLimit(self::UNIT_BYTES);

		return $initial_position > 0 ? $initial_position : 0;
	}

	private function convertBytesToUnit($bytes, $unit) {
		switch ($unit) {
			case self::UNIT_BYTES:
				return $bytes;
			case self::UNIT_KILOBYTES:
				return round($bytes / 1024, 2);
			case self::UNIT_MEGABYTES:
				return round($bytes / (1024 * 1024), 2);
			case self::UNIT_GIGABYTES:
				return round($bytes / (1024 * 1024 * 1024), 2);
			default:
				return $bytes;
		}
	}

	public function isOverlimited() {
		return $this->getLogSize([ 'raw' => true ]) > $this->getLogLimit(self::UNIT_BYTES);
	}

	private function readFromFileEnd($file_handle, $limit) {
		fseek($file_handle, -$limit, SEEK_END);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		$content = fread($file_handle, $limit);
		return $content;
	}

	public function getRawContent( $filename ) {
		if ($this->isOverlimited()) {
			$actual_limit = $this->getLogLimit(self::UNIT_BYTES);
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$file_handle  = fopen($filename, 'r');
			$content = $this->readFromFileEnd($file_handle, $actual_limit);
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose($file_handle);
			return $content;
		} else {
			return file_get_contents($filename);
		}
	}

	public function getNewEntries() {
		$logController = new LogController();
		$path          = $logController->getLogFilePath();
		// Handle missing file
		if (!file_exists($path)) {
			return $this->resetLog();
		}

		$file_size = filesize($path);
		// Handle empty file
		if ($file_size === 0) {
			return $this->resetLog();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file_handle = fopen($path, 'r');
		if (!$file_handle) {
			return $this->resetLog();
		}
		$last_position = get_option(self::LAST_POSITION_OPTION_NAME, 0);

		// Handle new or truncated content
		if ($last_position === 0 || $file_size > $last_position) {
			fseek($file_handle, $last_position, SEEK_SET);
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			$content      = fread($file_handle, $file_size - $last_position);
			$new_position = ftell($file_handle);
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose($file_handle);

			update_option(self::LAST_POSITION_OPTION_NAME, $new_position);

			return [
				'action' => [],
				'data'   => $this->splitLogToRows($content),
			];
		}

		// Handle file truncation
		if ($file_size < $last_position) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose($file_handle);
			return $this->resetLog();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose($file_handle);
	}

	private static function resetLog() {
		update_option(self::LAST_POSITION_OPTION_NAME, 0);
		return [
			'action' => 'clear',
			'data'   => [],
		];
	}

	public function getParsedContent() {
		$logController = new LogController();
		$path          = $logController->getLogFilePath();

		if (!file_exists($path) || !is_file($path)) {
			return false;
		}

		$content = $this->getRawContent($path);
		return $this->splitLogToRows($content);
	}

	private function splitLogToRows( $content ) {
		$pattern = '/\[[^\]]+\].*?(?=\n\[|$)/s';
		$count   = preg_match_all($pattern, $content, $matches);

		if (!$count) {
			return [];
		}

		return array_reverse($matches[0]);
	}

	public static function getDatetime( $row ) {
		preg_match_all('/\[(.*?)\]/m', $row, $matches, PREG_SET_ORDER, 0);
		return isset($matches[0][1]) ? $matches[0][1] : '';
	}

	public static function getLine( $row ) {
		preg_match_all('/(on line |php:)(\d{1,})/m', $row, $matches, PREG_SET_ORDER, 0);
		return isset($matches[0][2]) ? $matches[0][2] : '';
	}

	public static function getFile( $row ) {
		preg_match_all('/ in ' . preg_quote(Utils::getDocumentRoot(), '/') . '(.*?)( on line |:)\d{1,}/m', $row, $matches, PREG_SET_ORDER, 0);
		return isset($matches[0][1]) ? $matches[0][1] : '';
	}

	public static function getType( $row ) {
		if (strpos($row, 'PHP Notice:') !== false) {
			return self::LOG_LEVEL_NOTICE;
		} elseif (strpos($row, 'PHP Warning:') !== false) {
			return self::LOG_LEVEL_WARNING;
		} elseif (strpos($row, 'PHP Fatal error:') !== false) {
			return self::LOG_LEVEL_FATAL;
		} elseif (strpos($row, 'WordPress database error') !== false) {
			return self::LOG_LEVEL_DATABASE;
		} elseif (strpos($row, 'PHP Parse error:') !== false) {
			return self::LOG_LEVEL_PARSE;
		} elseif (strpos($row, 'PHP Deprecated:') !== false) {
			return self::LOG_LEVEL_DEPRECATED;
		} else {
			return self::LOG_LEVEL_CUSTOM;
		}
	}

	public static function getStackTrace( $row ) {
		$re = '/Stack trace:\n(.*?)thrown in/s';
		preg_match_all($re, $row, $matches, PREG_SET_ORDER, 0);
		if (isset($matches[0])) {
			return $matches[0][1];
		}
		return null;
	}

	public static function getDescription( $row ) {
		if (self::getType($row) === self::LOG_LEVEL_DATABASE) {

			$re = '/WordPress database error (.*)/m';
			preg_match_all($re, $row, $matches, PREG_SET_ORDER, 0);
			return isset($matches[0]) && $matches[0][1] ? $matches[0][1] : __('N/A', 'debug-log-viewer');
		}

		$re = '/ (PHP Notice:|PHP Warning:|PHP Fatal error:|PHP Parse error:|PHP Deprecated:)(.*?)(\[ | in |on line)/m';
		preg_match_all($re, $row, $matches, PREG_SET_ORDER, 0);
		return isset($matches[0]) && $matches[0][2] ? $matches[0][2] : trim(str_replace('[' . self::getDatetime($row) . ']', '', $row));
	}

	public function getLogSize( $params ) {
		$withUnits = isset($params['with_measure_units']) ? $params['with_measure_units'] : null;
		$raw       = isset($params['raw']) ? $params['raw'] : null;

		$logController = new LogController();
		$path          = $logController->getLogFilePath();

		if (is_file($path) && filesize($path)) {
			$filesizeInBytes = filesize($path);
			if ($raw) {
				return $filesizeInBytes;
			}
			$filesizeInMegabytes = $filesizeInBytes / 1024 / 1024;
			return $withUnits
				? round($filesizeInMegabytes, 2) . ' ' . __('Mb', 'debug-log-viewer')
				: round($filesizeInMegabytes, 2);
		} else {
			return 0;
		}
	}
}
