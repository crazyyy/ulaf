<?php
/**
 * Trait to make a class a singleton.
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 * @copyright MIT License
 */

namespace KolorWeb\KWLogManager\Characteristic;

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}


// Trait to make a class a singleton.
trait IsSingleton {

	/**
	 * Singleton instance variable
	 *
	 * @var object
	 * @static
	 */
	public static $instance;


	/**
	 * Get and return the instance of service.
	 *
	 * @return object
	 * @static
	 */
	public static function get_instance() {
		$class = __CLASS__;
		return isset( static::$instance ) ? static::$instance : static::$instance = new $class();
	}


	/**
	 * Protected constructor to prevent creating a new instance of the *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
		$this->init();
	}


	/**
	 * Protected clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	protected function __clone() {}


	/**
	 * Unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	public function __wakeup() {}


	/**
	 * Called when an instance is created.  Overwrite this method to perform a custom action when an instance is created.
	 *
	 * @return void
	 */
	public function init() {}

}
