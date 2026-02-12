<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweeper;
use Dev4Press\Plugin\SweepPress\Basic\Data;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\Query\Terms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermsUnused extends Sweeper {
	protected string $_code = 'terms-unused';
	protected string $_version = '6.0';
	protected string $_category = 'terms';
	protected array $_affected_tables = array(
		'term_taxonomy',
		'termmeta',
		'terms',
	);

	protected bool $_flag_single_task = false;
	protected bool $_flag_bulk_network = false;
	protected bool $_flag_auto_cleanup = false;
	protected bool $_flag_quick_cleanup = false;
	protected bool $_flag_scheduled_cleanup = false;

	public static function instance() : TermsUnused {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new TermsUnused();
		}

		return $instance;
	}

	public function items_count_n( int $value ) : string {
		return _n( '%s term', '%s terms', $value, 'sweeppress' );
	}

	public function title() : string {
		return __( 'Unused Terms', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all terms that are no longer associated with any posts.', 'sweeppress' );
	}

	public function help() : array {
		return array(
			__( 'Even if they are currently unused, these terms can be used later. If you delete them, you will need to create them again.', 'sweeppress' ),
			__( 'If you had a lot of tags that are no longer used, it can be beneficial to remove them.', 'sweeppress' ),
		);
	}

	protected function taxonomies() : array {
		return Data::get_taxonomies();
	}

	public function prepare() {
		foreach ( $this->taxonomies() as $tax => $label ) {
			$this->_tasks[ $tax ] = array(
				'title'      => $label,
				'real_title' => $tax,
				'registered' => true,
				'items'      => 0,
				'records'    => 0,
				'size'       => 0,
			);
		}

		$this->_tasks = array_merge( $this->_tasks, Terms::instance()->terms_unused_status() );

		$x = 0;
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
				$removal = Removal::instance()->unused_terms_by_taxonomy( $name );

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
