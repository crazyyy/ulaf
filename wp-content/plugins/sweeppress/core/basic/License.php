<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\v54\Core\Plugins\License as BaseLicense;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License extends BaseLicense {
	protected string $freemius = 'sweeppress_fs';
	protected string $plugin = 'sweeppress';

	protected function plugin() {
		return sweeppress();
	}
}
