<?php

namespace f12_profiler\includes {
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	use f12_profiler\Profiler;

	require_once( 'class.HardwareController.php' );

	/**
	 * Class AdminPage
	 * @package f12_profiler\includes
	 */
	class Measure {
		/**
		 * @var $_instance AdminPage
		 */
		private static $_instance = null;

		/**
		 * @return AdminPage
		 */
		public static function getInstance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Measure();
			}

			return self::$_instance;
		}

		/**
		 * AdminPage constructor.
		 */
		/*private function __construct() {
			add_action( 'wp_ajax_nopriv_f12_profiler_measure', array( $this, 'wp_StartMeasurement' ), 10, 0 );
		}*/

		/**
		 * Save the new settings to the database
		 */
		/*public function wp_StartMeasurement() {
			if(!function_exists('curl_version')){
				return json_encode('Curl not working');
			}

			curl_init();
		}*/
	}
}