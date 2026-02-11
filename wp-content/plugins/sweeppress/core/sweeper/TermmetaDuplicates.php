<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweep\MetaDuplicates;
use Dev4Press\Plugin\SweepPress\DB\Shared;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermmetaDuplicates extends MetaDuplicates {
	protected string $_code = 'termmeta-duplicates';
	protected string $_category = 'terms';
	protected string $table = 'termmeta';
	protected array $db_columns = array(
		'id'    => 'meta_id',
		'ref'   => 'term_id',
		'key'   => 'meta_key',
		'value' => 'meta_value',
	);
	protected array $_affected_tables = array(
		'termmeta',
	);

	public function __construct() {
		parent::__construct();

		$this->db_table   = Shared::instance()->termmeta;
		$this->exceptions = sweeppress_settings()->get( 'dupe_allow_termmeta', 'sweepers' );
	}

	public static function instance() : TermmetaDuplicates {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new TermmetaDuplicates();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Termmeta Duplicates', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all duplicated records from the `termmeta` database table.', 'sweeppress' );
	}

	public function help() : array {
		$list = parent::help();

		array_unshift( $list, __( 'Duplicated record in termmeta table is the record with same `term_id`, `meta_key` and `meta_value`.', 'sweeppress' ) );

		return $list;
	}
}
