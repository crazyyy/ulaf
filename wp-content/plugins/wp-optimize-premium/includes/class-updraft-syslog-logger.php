<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Updraft_Syslog_Logger')) return;

/**
 * Class Updraft_Syslog_Logger
 */
class Updraft_Syslog_Logger extends Updraft_Abstract_Logger {

	protected $log_ident;

	protected $log_facility;

	protected $syslog = null;

	/**
	 * Updraft_Syslog_Logger constructor
	 *
	 * @param string $log_ident
	 * @param ?int   $log_facility
	 */
	public function __construct($log_ident = 'updraft-syslog', $log_facility = null) {
		parent::__construct();


		$this->log_ident    = $log_ident;
		$this->log_facility = (!empty($log_facility) ? $log_facility : LOG_USER);
		$this->syslog       = openlog($this->log_ident, (LOG_ODELAY | LOG_PID), $this->log_facility);
	}

	/**
	 * Returns logger description
	 *
	 * @return string
	 */
	public function get_description() {
		return __('Log events in syslog', 'wp-optimize');
	}

	/**
	 * Emergency message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function emergency($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::EMERGENCY, $context);
	}

	/**
	 * Alert message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function alert($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::ALERT, $context);
	}

	/**
	 * Critical message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function critical($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::CRITICAL, $context);
	}

	/**
	 * Error message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function error($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::ERROR, $context);
	}

	/**
	 * Warning message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function warning($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::WARNING, $context);
	}

	/**
	 * Notice message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function notice($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::NOTICE, $context);
	}

	/**
	 * Info message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function info($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::INFO, $context);
	}

	/**
	 * Debug message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return void
	 */
	public function debug($message, $context = array()) {
		$this->log($message, Updraft_Log_Levels::DEBUG, $context);
	}

	/**
	 * Log message with any level
	 *
	 * @param  string $message
	 * @param  mixed  $level
	 * @param  array  $context
	 * @return void
	 */
	public function log($message, $level, $context = array()) {
		if (!$this->is_enabled() || !$this->syslog) return;

		$message = $this->interpolate($message, $context);
		if ($this->syslog) syslog($this->syslog_level($level), $message);
	}

	/**
	 * Return syslog level constant value by Updraft_Log_Levels level
	 *
	 * @param  string $level
	 * @return int
	 */
	private function syslog_level($level) {
		switch ($level) {
			case Updraft_Log_Levels::EMERGENCY:
				return LOG_EMERG;
			case Updraft_Log_Levels::ALERT:
				return LOG_ALERT;
			case Updraft_Log_Levels::CRITICAL:
				return LOG_CRIT;
			case Updraft_Log_Levels::ERROR:
				return LOG_ERR;
			case Updraft_Log_Levels::WARNING:
				return LOG_WARNING;
			case Updraft_Log_Levels::NOTICE:
				return LOG_NOTICE;
			case Updraft_Log_Levels::INFO:
				return LOG_INFO;
			case Updraft_Log_Levels::DEBUG:
				return LOG_DEBUG;
		}

		return LOG_INFO;
	}
}
