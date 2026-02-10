<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ElementorTrashedSubmissions extends Sweeper {
	protected string $_code = 'elementor-trashed-submissions';
	protected string $_category = 'elementor';
	protected int $_days_to_keep = 0;

	protected array $_affected_tables = array(
		'e_submissions',
		'e_submissions_actions_log',
		'e_submissions_values',
	);

	protected bool $_flag_quick_cleanup = false;
	protected bool $_flag_auto_cleanup = false;
	protected bool $_flag_scheduled_cleanup = true;
	protected bool $_flag_monitor_task = true;
	protected bool $_flag_days_to_keep = true;

	public function __construct() {
		parent::__construct();

		$this->_days_to_keep = sweeppress_settings()->get( 'keep_days_elementor_submissions-trash', 'sweepers', 0 );
	}

	public static function instance() : ElementorTrashedSubmissions {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new ElementorTrashedSubmissions();
		}

		return $instance;
	}

	public function items_count_n( int $value ) : string {
		return _n( '%s submissions', '%s submissions', $value, 'sweeppress' );
	}

	public function title() : string {
		return __( 'Elementor Trashed Submissions', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all all trashed submissions from forms in Elementor Pro.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Forms submissions saving is feature of Elementor Pro plugin.', 'sweeppress' ),
		);
	}

	public function limitations() : array {
		return array(
			__( 'This sweeper can\'t be used for auto or quick sweeping.', 'sweeppress' ),
			__( 'Make sure to check out the Help information provided for this sweeper to better understand these limitations.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = Elementor::instance()->trash_submissions( $this->_days_to_keep );
	}

	public function sweep( $tasks = array() ) : array {
		$results = array(
			'records' => 0,
			'size'    => 0,
			'tasks'   => array(),
		);

		$start = $this->get_tasks();

		$this->register_active_sweeper();
		$this->log_sweep_start();

		foreach ( $tasks as $name ) {
			$task = $start[ $name ] ?? array( 'records' => 0 );

			if ( $task['records'] > 0 ) {
				$id = substr( $name, 3 );

				$removal = Removal::instance()->elementor_trashed_submission( $id, $this->_days_to_keep );

				if ( is_wp_error( $removal ) ) {
					$results['tasks'][ $name ] = $removal;
				} else {
					$results['tasks'][ $name ] = $task['title'];
					$results['records']        += $task['records'];
					$results['size']           += $task['size'];
				}
			}
		}

		$this->log_sweep_end();
		$this->unregister_active_sweeper();

		return $results;
	}
}
