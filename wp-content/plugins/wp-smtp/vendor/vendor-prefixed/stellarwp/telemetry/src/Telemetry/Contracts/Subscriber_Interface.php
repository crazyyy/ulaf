<?php
/**
 * The API implemented by all subscribers.
 *
 * @package SolidWP\Mail\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Mail\StellarWP\Telemetry\Contracts;

/**
 * Interface Subscriber_Interface
 *
 * @package SolidWP\Mail\StellarWP\Telemetry\Contracts
 */
interface Subscriber_Interface {

	/**
	 * Register action/filter listeners to hook into WordPress
	 *
	 * @return void
	 */
	public function register();
}
