<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweep\MetaDuplicates;
use Dev4Press\Plugin\SweepPress\DB\Shared;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostmetaDuplicates extends MetaDuplicates {
	protected string $_code = 'postmeta-duplicates';
	protected string $_category = 'posts';
	protected string $table = 'postmeta';
	protected array $db_columns = array(
		'id'    => 'meta_id',
		'ref'   => 'post_id',
		'key'   => 'meta_key',
		'value' => 'meta_value',
	);
	protected array $_affected_tables = array(
		'postmeta',
	);

	public function __construct() {
		parent::__construct();

		$this->db_table   = Shared::instance()->postmeta;
		$this->exceptions = sweeppress_settings()->get( 'dupe_allow_postmeta', 'sweepers' );
	}

	public static function instance() : PostmetaDuplicates {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new PostmetaDuplicates();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Postmeta Duplicates', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all duplicated records from the `postmeta` database table.', 'sweeppress' );
	}

	public function help() : array {
		$list = parent::help();

		array_unshift( $list, __( 'Duplicated record in postmeta table is the record with same `post_id`, `meta_key` and `meta_value`.', 'sweeppress' ) );

		return $list;
	}
}
