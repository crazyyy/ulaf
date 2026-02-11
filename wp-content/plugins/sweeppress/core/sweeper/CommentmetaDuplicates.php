<?php

namespace Dev4Press\Plugin\SweepPress\Sweeper;

use Dev4Press\Plugin\SweepPress\Base\Sweep\MetaDuplicates;
use Dev4Press\Plugin\SweepPress\DB\Shared;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CommentmetaDuplicates extends MetaDuplicates {
	protected string $_code = 'commentmeta-duplicates';
	protected string $_category = 'comments';
	protected string $table = 'commentmeta';
	protected array $db_columns = array(
		'id'    => 'meta_id',
		'ref'   => 'comment_id',
		'key'   => 'meta_key',
		'value' => 'meta_value',
	);
	protected array $_affected_tables = array(
		'commentmeta',
	);

	public function __construct() {
		parent::__construct();

		$this->db_table   = Shared::instance()->commentmeta;
		$this->exceptions = sweeppress_settings()->get( 'dupe_allow_commentmeta', 'sweepers' );
	}

	public static function instance() : CommentmetaDuplicates {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new CommentmetaDuplicates();
		}

		return $instance;
	}

	public function title() : string {
		return __( 'Commentmeta Duplicates', 'sweeppress' );
	}

	public function description() : string {
		return __( 'Remove all duplicated records from the `commentmeta` database table.', 'sweeppress' );
	}

	public function help() : array {
		$list = parent::help();

		array_unshift( $list, __( 'Duplicated record in commentmeta table is the record with same `comment_id`, `meta_key` and `meta_value`.', 'sweeppress' ) );

		return $list;
	}
}
