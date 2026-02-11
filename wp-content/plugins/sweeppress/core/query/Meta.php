<?php

namespace Dev4Press\Plugin\SweepPress\Query;

use Dev4Press\Plugin\SweepPress\Base\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Meta extends Query {
	protected string $_group = 'meta';

	public static function instance() : Meta {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Meta();
		}

		return $instance;
	}

	public function duplicated_records( $name, $table, $columns, $exceptions ) : array {
		$list = $this->retrieve( 'dupe-' . $name );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_duplicated_meta_records( $name, $table, $columns, $exceptions );

			$records = 0;
			$size    = 0;

			foreach ( $raw as $record ) {
				$total  = absint( $record['meta_records'] );
				$remove = $total - 1;

				$records += $remove;
				$size    += ceil( floatval( $record['meta_size'] ) * ( $remove / $total ) );
			}

			$list = array(
				'title'   => __( 'Duplicated Records', 'sweeppress' ),
				'records' => $records,
				'size'    => $size > 0 && $size < 1025 ? 1024 : $size,
			);

			$this->store( 'dupe-' . $name, $list );
		}

		return $list;
	}
}
