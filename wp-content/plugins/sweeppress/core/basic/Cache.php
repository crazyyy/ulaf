<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\v54\Core\Cache\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cache extends Core {
	public string $store = 'sweeppress';

	public static function instance() : Cache {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Cache();
		}

		return $instance;
	}
}
