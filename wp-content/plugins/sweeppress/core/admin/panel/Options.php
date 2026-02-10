<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\Plugin\SweepPress\Admin\Panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options extends Panel {
	protected bool $table = true;

	public function __construct( $admin ) {
		parent::__construct( $admin );

		$subpanels = array(
			'index' => array(
				'title' => __( 'Options Management', 'sweeppress' ),
				'icon'  => 'ui-sliders-base',
				'info'  => __( 'Review all the records in the WordPress options table with ability to remove them.', 'sweeppress' ),
			),
			'quick' => array(
				'title' => __( 'Options Quick Tasks', 'sweeppress' ),
				'icon'  => 'ui-target',
				'info'  => __( 'Run quick cleanup tasks for the Options database table.', 'sweeppress' ),
			),
		);

		$this->subpanels = $this->subpanels + $subpanels;

		if ( $this->a()->subpanel == 'quick' ) {
			$this->cards = true;
			$this->table = false;
		}
	}

	public function screen_options_show() {
		add_screen_option( 'per_page', array(
			'label'   => __( 'Rows', 'sweeppress' ),
			'default' => 50,
			'option'  => 'sweeppress_options_rows_per_page',
		) );

		$this->get_table_object();
	}

	public function get_table_object() {
		if ( is_null( $this->table_object ) ) {
			$this->table_object = new \Dev4Press\Plugin\SweepPress\Table\Options();
		}

		return $this->table_object;
	}
}
