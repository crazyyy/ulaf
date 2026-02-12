<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UnhookedCronJobs extends Sweeper {
	protected string $_code = 'unhooked-cron-jobs';
	protected string $_category = 'options';
	protected array $_affected_tables = array(
		'options',
	);

	protected bool $_flag_auto_cleanup = false;
	protected bool $_flag_monitor_task = false;
	protected bool $_flag_backup = false;

	protected bool $_flag_bulk_network = true;

	public static function instance() : UnhookedCronJobs {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new UnhookedCronJobs();
		}

		return $instance;
	}

	public function items_count_n( int $value ) : string {
		return _n( '%s job', '%s jobs', $value, 'sweeppress' );
	}

	public function title() : string {
		return __( 'Unhooked CRON Jobs', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove CRON Jobs from WordPress CRON Scheduler no longer having action hooked.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'If the CRON job is no longer hooked to any action, that means that plugin that registered the job is no longer active.', 'sweeppress' ),
			__( 'This is useful to remove scheduled jobs from plugins that are no longer in use.', 'sweeppress' ),
			__( 'All CRON Jobs are stored inside the single record.', 'sweeppress' ),
		);
	}

	public function limitations() : array {
		return array(
			__( 'This sweeper can\'t be used from Dashboard for Auto Sweep and Monitor Task.', 'sweeppress' ),
			__( 'Make sure to check out the Help information provided for this sweeper to better understand these limitations.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = array(
			$this->_code => Options::instance()->unhooked_cron_jobs(),
		);
	}

	public function sweep( $tasks = array() ) : array {
		$results = array(
			'records' => 1,
			'size'    => 0,
			'tasks'   => array(),
		);

		$this->register_active_sweeper();
		$this->log_sweep_start();

		if ( in_array( $this->_code, $tasks ) ) {
			$input = Options::instance()->unhooked_cron_jobs();

			$results['tasks'][ $this->_code ] = $input['title'];

			Removal::instance()->cron_jobs_by_job( $input['jobs'] );
		}

		$this->log_sweep_end();
		$this->unregister_active_sweeper();

		return $results;
	}
}
