<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Terms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermsAMPErrors extends Sweeper {
	protected string $_code = 'terms-amp-errors';
	protected string $_category = 'terms';
	protected array $_affected_tables = array(
		'term_relationships',
		'term_taxonomy',
		'termmeta',
		'terms',
	);

	protected bool $_flag_single_task = false;
	protected bool $_flag_quick_cleanup = false;
	protected bool $_flag_scheduled_cleanup = false;
	protected bool $_flag_auto_cleanup = false;
	protected bool $_flag_monitor_task = false;

	public static function instance() : TermsAMPErrors {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new TermsAMPErrors();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'AMP Validation Errors Terms', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all records from all the terms related database tables belonging to the AMP plugin used for validation errors.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'This is related to taxonomy `amp_validation_error` registered by the official WordPress `AMP` plugin.', 'sweeppress' ),
			__( 'Each error saved as term can have big description that takes a lot of space, and terms can\'t be reused!', 'sweeppress' ),
			__( 'If you use AMP plugin, make sure to check out the validation errors first, before deleting them.', 'sweeppress' ),
			__( 'If you do not use AMP plugin anymore, removing these terms will not cause any issues.', 'sweeppress' ),
			__( 'This will remove term and term meta data, and the connection of the term to posts.', 'sweeppress' ),
		);
	}

	public function limitations() : array {
		return array(
			__( 'This sweeper can be used only from the main Sweep panel.', 'sweeppress' ),
			__( 'Make sure to check out the Help information provided for this sweeper to better understand these limitations.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = Terms::instance()->terms_amp_errors();
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
				$removal = Removal::instance()->terms_amp_errors( $name );

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
