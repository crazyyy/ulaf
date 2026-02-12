<?php

namespace Dev4Press\Plugin\SweepPress\Query;

use Dev4Press\Plugin\SweepPress\Base\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor extends Query {
	protected string $_group = 'query-elementor';

	public static function instance() : Elementor {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Elementor();
		}

		return $instance;
	}

	public function trash_submissions( int $keep_days = 0 ) : array {
		$list = $this->retrieve( 'trash' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_elementor_trashed_submissions( $keep_days );

			$list = array();

			foreach ( $raw as $row ) {
				$_size = absint( $row['entry_size'] ) + absint( $row['log_size'] ) + absint( $row['values_size'] );
				$_slug = $row['element_id'];

				$list[ 'id-' . $row['element_id'] ] = array(
					'real_title' => $_slug,
					'title'      => $row['form_name'],
					'items'      => absint( $row['entry_records'] ),
					'records'    => absint( $row['entry_records'] ) + absint( $row['log_records'] ) + absint( $row['values_records'] ),
					'size'       => $_size > 0 && $_size < 1025 ? 1024 : $_size,
				);
			}

			$this->store( 'trash', $list );
		}

		return $list;
	}
}