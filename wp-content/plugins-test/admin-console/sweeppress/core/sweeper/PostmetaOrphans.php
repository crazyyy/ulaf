<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostmetaOrphans extends Sweeper {
	protected string $_code = 'postmeta-orphans';
	protected string $_category = 'posts';
	protected array $_affected_tables = array(
		'postmeta',
	);

	protected bool $_flag_bulk_network = true;
	protected bool $_flag_high_system_requirements = true;

	public static function instance() : PostmetaOrphans {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new PostmetaOrphans();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Postmeta Orphans', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all records from the `postmeta` database table that are no longer connected to posts.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Orphaned meta records are usually product of broken posts deletion, or other issues.', 'sweeppress' ),
		);
	}

	public function prepare() {
		$this->_tasks = array(
			$this->_code => Posts::instance()->postmeta_orphaned_status(),
		);
	}

	public function sweep( $tasks = array() ) : array {
		$this->register_active_sweeper();
		$this->log_sweep_start();

		$removal = Removal::instance()->postmeta_orphans();

		return $this->base_sweep( $removal );
	}
}
