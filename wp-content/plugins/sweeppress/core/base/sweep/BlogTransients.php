<?php

namespace Dev4Press\Plugin\SweepPress\Base\Sweep;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class BlogTransients extends Sweeper {
	protected string $_category = 'options';
	protected array $_affected_tables = array(
		'options',
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
			foreach ( $task['transients']['local'] as $transient ) {
				if ( delete_option( '_transient_' . $transient ) ) {
					$deleted ++;
				}

				if ( delete_option( '_transient_timeout_' . $transient ) ) {
					$deleted ++;
				}
			}

			foreach ( $task['transients']['site'] as $transient ) {
				if ( delete_option( '_site_transient_' . $transient ) ) {
					$deleted ++;
				}

				if ( delete_option( '_site_transient_timeout_' . $transient ) ) {
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
