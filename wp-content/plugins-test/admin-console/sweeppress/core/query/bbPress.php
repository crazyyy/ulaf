<?php

namespace Dev4Press\Plugin\SweepPress\Query;

use Dev4Press\Plugin\SweepPress\Base\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class bbPress extends Query {
	protected string $_group = 'query-bbpress';

	public static function instance() : bbPress {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new bbPress();
		}

		return $instance;
	}

	public function orphaned_replies_records() : array {
		$list = $this->retrieve( 'bbpress-orphaned-replies' );

		if ( ! $list ) {
			$raw   = sweeppress_prepare()->get_bbpress_orphaned_replies();
			$_size = absint( $raw['posts_size'] ) + absint( $raw['metas_size'] );

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'items'   => absint( $raw['posts_records'] ),
				'records' => absint( $raw['posts_records'] ) + absint( $raw['metas_records'] ),
				'size'    => $_size > 0 && $_size < 1025 ? 1024 : $_size,
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'bbpress-orphaned-replies', $list );
		}

		return $list;
	}
}
