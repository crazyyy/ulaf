<?php

namespace Dev4Press\Plugin\SweepPress\Table;

use Dev4Press\Plugin\SweepPress\Basic\Sweep;
use Dev4Press\v54\WordPress\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sweepers extends Table {
	public string $_table_class_name = 'sweeppress-table-sweepers';

	public function __construct( $args = array() ) {
		parent::__construct( array(
			'singular' => 'sweeper',
			'plural'   => 'sweepers',
			'ajax'     => false,
		) );
	}

	protected function display_tablenav( $which ) {
	}

	public function get_columns() : array {
		return array(
			'category' => __( 'Category', 'sweeppress' ),
			'sweeper'  => __( 'Sweeper', 'sweeppress' ),
			'id'       => __( 'Code', 'sweeppress' ),
			'auto'     => __( 'Auto Sweep', 'sweeppress' ),
			'quick'    => __( 'Quick Sweep', 'sweeppress' ),
			'cron'     => __( 'Scheduled Sweep', 'sweeppress' ),
			'monitor'  => __( 'Monitor', 'sweeppress' ),
			'backup'   => __( 'Backup', 'sweeppress' ),
			'scope'    => __( 'Scope', 'sweeppress' ),
		);
	}

	public function column_category( $item ) : string {
		return $item['category'];
	}

	public function column_id( $item ) : string {
		return $item['code'];
	}

	public function column_sweeper( $item ) : string {
		$return = $item['name'];

		if ( $item['high'] ) {
			$return .= '<span class="flat-system" title="' . esc_attr__( 'This sweeper estimation process has high system requirements and it can be slow to estimate.', 'sweeppress' ) . '"><i class="d4p-icon d4p-ui-gauge-bolt"></i></span>';
		}

		return $return;
	}

	public function column_scope( $item ) : string {
		return $item['scope'] == 'blog' ? __( 'Blog', 'sweeppress' ) : __( 'Network', 'sweeppress' );
	}

	public function column_backup( $item ) : string {
		return $item['backup'] ? '<i aria-label="' . esc_attr__( 'Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-check-square"></i>' : '<i aria-label="' . esc_attr__( 'Not Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-box"></i>';
	}

	public function column_auto( $item ) : string {
		return $item['auto'] ? '<i aria-label="' . esc_attr__( 'Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-check-square"></i>' : '<i aria-label="' . esc_attr__( 'Not Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-box"></i>';
	}

	public function column_monitor( $item ) : string {
		return $item['monitor'] ? '<i aria-label="' . esc_attr__( 'Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-check-square"></i>' : '<i aria-label="' . esc_attr__( 'Not Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-box"></i>';
	}

	public function column_quick( $item ) : string {
		return $item['quick'] ? '<i aria-label="' . esc_attr__( 'Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-check-square"></i>' : '<i aria-label="' . esc_attr__( 'Not Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-box"></i>';
	}

	public function column_cron( $item ) : string {
		return $item['cron'] ? '<i aria-label="' . esc_attr__( 'Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-check-square"></i>' : '<i aria-label="' . esc_attr__( 'Not Available', 'sweeppress' ) . '" class="d4p-icon d4p-ui-box"></i>';
	}

	public function prepare_items() {
		$this->items = Sweep::instance()->all();
	}
}
