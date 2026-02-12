<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Updraft_Simple_History_Logger')) return;

/**
 * Class Updraft_Simple_History_Logger
 */
class Updraft_Simple_History_Logger extends Updraft_Abstract_Logger {

	/**
	 * Updraft_Simple_History_Logger constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Returns true if SimpleHistory plugin is installed.
	 *
	 * @return bool
	 */
	public function is_available() {
		return class_exists('SimpleHistory');
	}

	/**
	 * Returns logger description
	 *
	 * @return string
	 */
	public function get_description() {
		return __('Log events via the Simple History plugin', 'wp-optimize');
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

		if (!$this->is_enabled()) return;

		if (is_callable('SimpleLogger')) {
			SimpleLogger()->log($level, $message, $context);
		}
	}
}
