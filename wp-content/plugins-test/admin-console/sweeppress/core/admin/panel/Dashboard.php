<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\v54\Core\UI\Admin\PanelDashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard extends PanelDashboard {
	public function __construct( $admin ) {
		parent::__construct( $admin );

		$this->sidebar_links['plugin'] = array(
			'sweep' => array(
				'icon'  => $this->a()->menu_items['sweep']['icon'],
				'class' => 'button-primary',
				'url'   => $this->a()->panel_url( 'sweep' ),
				'label' => __( 'Sweep', 'sweeppress' ),
			),
			'jobs'  => array(
				'icon'  => $this->a()->menu_items['jobs']['icon'],
				'class' => 'button-primary',
				'url'   => $this->a()->panel_url( 'jobs' ),
				'label' => __( 'Jobs', 'sweeppress' ),
			),
		);
	}

	public function show() {
		sweeppress_core()->run_dashboard();

		parent::show();
	}
}
