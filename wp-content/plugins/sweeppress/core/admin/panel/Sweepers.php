<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sweepers extends Panel {
	protected bool $table = true;

	public function screen_options_show() {
		$this->get_table_object();
	}

	public function get_table_object() {
		if ( is_null( $this->table_object ) ) {
			$this->table_object = new \Dev4Press\Plugin\SweepPress\Table\Sweepers();
		}

		return $this->table_object;
	}
}
