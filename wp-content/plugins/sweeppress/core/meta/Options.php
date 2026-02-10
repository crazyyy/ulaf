<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

use Dev4Press\Plugin\SweepPress\Base\Options as BaseOptions;
use Dev4Press\Plugin\SweepPress\DB\Prepare;
use Dev4Press\Plugin\SweepPress\DB\Removal;
use Dev4Press\Plugin\SweepPress\DB\Shared;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options extends BaseOptions {
	protected string $scope = 'options';
	protected array $transients = array(
		'_transient_%',
		'_site_transient_%',
		'_transient_timeout_%',
		'_site_transient_timeout_%',
	);

	public function __construct() {
		$this->defaults = Storage::instance()->options();

		parent::__construct();
	}

	public function quick_tasks() : array {
		$items = $this->quick_tasks_all_options();
		$tasks = array(
			'widgets-missing' => array(
				'count' => 0,
				'size'  => 0,
				'items' => array(),
			),
			'known-missing'   => array(
				'count' => 0,
				'size'  => 0,
				'items' => array(),
			),
			'known-installed' => array(
				'count' => 0,
				'size'  => 0,
				'items' => array(),
			),
			'unknown'         => array(
				'count' => 0,
				'size'  => 0,
				'items' => array(),
			),
		);

		foreach ( $items as $item ) {
			if ( $item->result['type'] == 'widget' && ! $item->result['installed'] ) {
				$tasks['widgets-missing']['count'] ++;
				$tasks['widgets-missing']['size']    += absint( $item->option_size );
				$tasks['widgets-missing']['items'][] = absint( $item->option_id );
			}

			if ( $item->result['type'] != 'unknown' ) {
				if ( $item->result['installed'] ) {
					$tasks['known-installed']['count'] ++;
					$tasks['known-installed']['size']    += absint( $item->option_size );
					$tasks['known-installed']['items'][] = absint( $item->option_id );
				} else {
					$tasks['known-missing']['count'] ++;
					$tasks['known-missing']['size']    += absint( $item->option_size );
					$tasks['known-missing']['items'][] = absint( $item->option_id );
				}
			}

			if ( $item->result['type'] == 'unknown' ) {
				$tasks['unknown']['count'] ++;
				$tasks['unknown']['size']    += absint( $item->option_size );
				$tasks['unknown']['items'][] = absint( $item->option_id );
			}
		}

		return $tasks;
	}

	public function delete_ids( $ids, $log, $key ) {
		sweeppress()->log( $log . ' # BEGIN' );
		sweeppress()->log( ' OPTIONS # BEGIN' . ( SWEEPPRESS_SIMULATION ? ' # SIMULATION_MODE' : '' ) );

		$statistics = Prepare::instance()->get_options_records( $ids );
		$options    = Prepare::instance()->get_options_from_ids( $ids );
		$started    = microtime( true );

		if ( sweeppress()->is_backup_enabled() ) {
			sweeppress()->backup()->options( $ids );
		}

		$query   = "DELETE FROM " . sweeppress_db()->options . " WHERE `option_id` IN (" . sweeppress_db()->prepare_in_list( $ids, '%d' ) . ")";
		$records = Removal::instance()->go( $query, false );

		sweeppress_settings()->log_statistics( array(
			'source'   => 'options',
			'records'  => $statistics['records'],
			'size'     => $statistics['size'],
			'time'     => microtime( true ) - $started,
			'jobs'     => 1,
			'tasks'    => 1,
			'sweepers' => array(
				$key => array(
					'records' => $statistics['records'],
					'size'    => $statistics['size'],
					'time'    => microtime( true ) - $started,
				),
			),
		) );

		do_action( 'sweeppress_options_deleted', array(
			'names' => $options,
			'size'  => $statistics['size'],
		) );

		sweeppress()->log( ' OPTIONS # END' );
		sweeppress()->log( $log . ' # END' );
		sweeppress()->log( '------------------------------------------------------' );

		return $records;
	}

	public function autoload_ids( $ids, $autoload ) {
		$records = 0;

		if ( ! empty( $ids ) && in_array( $autoload, array( 'yes', 'no' ) ) ) {
			$query   = sweeppress_db()->prepare( "UPDATE " . sweeppress_db()->options . " SET `autoload` = %s WHERE `option_id` IN (" . sweeppress_db()->prepare_in_list( $ids, '%d' ) . ")", $autoload );
			$records = sweeppress_db()->query( $query );
		}

		return $records;
	}

	private function quick_tasks_all_options() : array {
		$items = array();
		$names = Shared::instance()->get_options_names();
		$data  = Shared::instance()->query_options_table( array(
			'per_page' => 0,
			'options'  => $names,
		) );

		foreach ( $data['items'] as $item ) {
			$item->result = $this->identify( $item->option_name );

			$items[] = $item;
		}

		return $items;
	}
}
