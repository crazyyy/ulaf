<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Updraft_Slack_Logger')) return;

/**
 * Class Updraft_Slack_Logger
 */
class Updraft_Slack_Logger extends Updraft_Abstract_Logger {

	protected $allow_multiple = true;

	/**
	 * Updraft_Slack_Logger constructor
	 *
	 * @param string $webhook_url
	 */
	public function __construct($webhook_url = '') {
		parent::__construct();
		$this->set_webhook_url($webhook_url);
	}

	/**
	 * Set Webhook URL
	 *
	 * @param string $webhook_url Setting the webhook url.
	 */
	public function set_webhook_url($webhook_url) {
		$this->set_option('slack_webhook_url', $webhook_url);
	}

	/**
	 * Get Webhook URL
	 *
	 * @return string
	 */
	public function get_webhook_url() {
		return $this->get_option('slack_webhook_url');
	}

	/**
	 * Returns logger description
	 *
	 * @return string
	 */
	public function get_description() {
		return __('Log events into Slack', 'wp-optimize');
	}

	/**
	 * Returns list of logger options.
	 *
	 * @return array
	 */
	public function get_options_list() {
		return array(
			'slack_webhook_url' => array(
				__('Slack webhook URL', 'wp-optimize'),
				'url', // validator
			)
		);
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

		$prefix  = '['.Updraft_Log_Levels::to_text($level).']: ';
		$message = $prefix.$this->interpolate($message, $context);
		$this->post_message($message);
	}

	/**
	 * Post message to Slack
	 *
	 * @param  string $message
	 * @return void
	 */
	protected function post_message($message) {
		$webhook_url = $this->get_webhook_url();
		$logger_name = $this->get_option('logger_name', 'Updraft Logger');

		if (!$webhook_url) return;

		$params = array(
			'username' => $logger_name,
			'text'     => $message,
		);

		wp_remote_post(
			$webhook_url,
			array(
				'body' => array('payload' => wp_json_encode($params)),
			)
		);
	}
}
