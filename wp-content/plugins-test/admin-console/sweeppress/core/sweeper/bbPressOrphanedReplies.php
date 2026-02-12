<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\bbPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class bbPressOrphanedReplies extends Sweeper {
	protected string $_code = 'bbpress-orphaned-replies';
	protected string $_version = '6.0';
	protected string $_category = 'bbpress';
	protected array $_affected_tables = array(
		'posts',
		'postmeta',
	);

	protected bool $_flag_quick_cleanup = false;
	protected bool $_flag_auto_cleanup = false;

	public static function instance() : bbPressOrphanedReplies {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new bbPressOrphanedReplies();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Orphaned Replies', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all bbPress replies and their meta records if those replies are orphaned and no longer connected to terms.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Orphaned Replies can still be accessed directly by the reply ID, they are just not associated with any topics.', 'sweeppress' ),
			__( 'Replies can be orphaned if the deletion of terms produces some error that stops the process and not every reply get deleted.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = array(
			$this->_code => bbPress::instance()->orphaned_replies_records(),
		);
	}

	public function sweep( $tasks = array() ) : array {
		$this->register_active_sweeper();
		$this->log_sweep_start();

		$removal = Removal::instance()->bbpress_orphaned_replies();

		return $this->base_sweep( $removal );
	}
}
