<?php

namespace Dev4Press\Plugin\SweepPress\Query;

use Dev4Press\Plugin\SweepPress\Base\Query;
use Dev4Press\Plugin\SweepPress\Basic\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Terms extends Query {
	protected string $_group = 'query-terms';

	public static function instance() : Terms {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Terms();
		}

		return $instance;
	}

	public function terms_amp_errors() : array {
		$list = $this->retrieve( 'terms-amp-errors' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_terms_amp_errors();

			$list = array();

			foreach ( $raw as $row ) {
				$_size = absint( $row['terms_size'] ) + absint( $row['meta_size'] ) + absint( $row['tax_size'] ) + absint( $row['rel_size'] );

				$list[ $row['assigned'] ] = array(
					'title'   => $row['assigned'] == 'yes' ? __( 'Assigned to Post', 'sweeppress' ) : __( 'Not Assigned to Post', 'sweeppress' ),
					'items'   => absint( $row['term_records'] ),
					'records' => absint( $row['term_records'] ) + absint( $row['meta_records'] ) + absint( $row['tax_records'] ) + absint( $row['rel_records'] ),
					'size'    => $_size > 0 && $_size < 1025 ? 1024 : $_size,
				);
			}
		}

		return $list;
	}

	public function terms_unused_status() : array {
		$list = $this->retrieve( 'terms-unused' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_unused_terms();

			$list = array();

			foreach ( $raw as $row ) {
				$_size = absint( $row['term_size'] ) + absint( $row['meta_size'] ) + absint( $row['tax_size'] );

				$list[ $row['taxonomy'] ] = array(
					'type'       => 'taxonomy',
					'registered' => taxonomy_exists( $row['taxonomy'] ),
					'real_title' => $row['taxonomy'],
					'title'      => Data::get_taxonomy_title( $row['taxonomy'] ),
					'items'      => absint( $row['term_records'] ),
					'records'    => absint( $row['term_records'] ) + absint( $row['meta_records'] ) + absint( $row['tax_records'] ),
					'size'       => $_size > 0 && $_size < 1025 ? 1024 : $_size,
				);
			}

			$this->store( 'terms-unused', $list );
		}

		return $list;
	}

	public function terms_orphaned_status() : array {
		$list = $this->retrieve( 'terms-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_terms_orphans();

			$list = array(
				'title'   => __( 'Orphaned Terms', 'sweeppress' ),
				'items'   => $raw['terms_records'],
				'records' => $raw['terms_records'] + $raw['meta_records'],
				'size'    => $raw['terms_size'] + $raw['meta_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'terms-orphans', $list );
		}

		return $list;
	}

	public function termmeta_orphaned_status() : array {
		$list = $this->retrieve( 'meta-record-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_termmeta_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'meta-record-orphans', $list );
		}

		return $list;
	}

	public function term_relationships_object_orphaned_status() : array {
		$list = $this->retrieve( 'meta-relationships-object-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_term_relationships_object_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'meta-relationships-object-orphans', $list );
		}

		return $list;
	}

	public function term_relationships_taxonomy_orphaned_status() : array {
		$list = $this->retrieve( 'meta-relationships-taxonomy-orphans' );

		if ( ! $list ) {
			$raw = sweeppress_prepare()->get_term_relationships_taxonomy_orphaned_records();

			$list = array(
				'title'   => __( 'Orphaned Records', 'sweeppress' ),
				'records' => $raw['key_records'],
				'size'    => $raw['key_size'],
			);

			if ( $list['size'] > 0 && $list['size'] < 1025 ) {
				$list['size'] = 1024;
			}

			$this->store( 'meta-relationships-taxonomy-orphans', $list );
		}

		return $list;
	}
}
