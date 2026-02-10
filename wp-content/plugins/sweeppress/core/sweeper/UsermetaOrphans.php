<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UsermetaOrphans extends Sweeper {
	protected string $_code = 'usermeta-orphans';
	protected string $_category = 'users';
	protected array $_affected_tables = array(
		'usermeta',
	);
	protected bool $_flag_high_system_requirements = true;

	public static function instance() : UsermetaOrphans {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new UsermetaOrphans();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Usermeta Orphans', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all records from the `usermeta` database table that are no longer connected to users.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Orphaned meta records are usually product of broken users deletion, or other issues.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = array(
			$this->_code => Users::instance()->usermeta_orphaned_status(),
		);
	}

	public function sweep( $tasks = array() ) : array {
		$this->register_active_sweeper();
		$this->log_sweep_start();

		$removal = Removal::instance()->usermeta_orphans();

		return $this->base_sweep( $removal );
	}
}
