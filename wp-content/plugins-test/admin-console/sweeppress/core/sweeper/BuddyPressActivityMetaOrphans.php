<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\BuddyPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BuddyPressActivityMetaOrphans extends Sweeper {
	protected string $_code = 'buddypress-activity-meta-orphans';
	protected string $_version = '4.0';
	protected string $_category = 'buddypress';
	protected array $_affected_tables = array(
		'bp_activity_meta',
	);

	protected bool $_flag_bulk_network = true;

	public static function instance() : BuddyPressActivityMetaOrphans {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new BuddyPressActivityMetaOrphans();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Activity Meta Orphans', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all records from the BuddyPress activity meta database table that are no longer connected to terms.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Orphaned meta records are usually product of broken activity deletion, or other issues.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = array(
			$this->_code => BuddyPress::instance()->activity_meta_orphaned_status(),
		);
	}

	public function sweep( $tasks = array() ) : array {
		$this->register_active_sweeper();
		$this->log_sweep_start();

		$removal = Removal::instance()->bp_activity_meta_orphans();

		return $this->base_sweep( $removal );
	}
}
