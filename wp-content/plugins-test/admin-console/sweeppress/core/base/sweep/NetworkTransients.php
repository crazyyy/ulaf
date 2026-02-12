<?php

namespace Dev4Press\Plugin\SweepPress\Base\Sweep;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class NetworkTransients extends Sweeper {
	protected string $_scope = 'network';
	protected string $_category = 'network';
	protected array $_affected_tables = array(
		'sitemeta',
	);
	protected bool $_flag_backup = false;

	public function sweep( $tasks = array() ) : array {
		$results = array(
			'records' => 0,
			'size'    => 0,
			'tasks'   => array(),
		);

		$task = $this->get_task();

		$this->register_active_sweeper();
		$this->log_sweep_start();

		$deleted = 0;
		if ( ! SWEEPPRESS_SIMULATION ) {
			foreach ( $task['transients'] as $transient ) {
				if ( delete_site_option( '_site_transient_' . $transient ) ) {
					$deleted ++;
				}

				if ( delete_site_option( '_site_transient_timeout_' . $transient ) ) {
					$deleted ++;
				}
			}
		} else {
			$deleted ++;
		}

		$results['tasks'][ $this->_code ] = $task['title'];

		if ( $deleted > 0 ) {
			$results['records'] = $task['records'];
			$results['size']    = $task['size'];
		}

		$this->log_sweep_end();
		$this->unregister_active_sweeper();

		return $results;
	}
}
