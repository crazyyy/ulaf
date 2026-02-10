<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\Plugin\SweepPress\DB\Prepare;
use Dev4Press\Plugin\SweepPress\Meta\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {
	private array $_system = array(
		'options',
		'blog_versions',
		'blogs',
		'site',
		'sitemeta',
		'users',
		'usermeta',
	);
	private array $_wordpress = array(
		'blog'    => array(
			'posts',
			'comments',
			'links',
			'options',
			'postmeta',
			'terms',
			'term_taxonomy',
			'term_relationships',
			'termmeta',
			'commentmeta',
		),
		'global'  => array(
			'users',
			'usermeta',
		),
		'network' => array(
			'blogs',
			'blogmeta',
			'signups',
			'site',
			'sitemeta',
			'registration_log',
		),
	);
	private string $_prefix;
	private string $_base_prefix;
	private array $_tables;
	private array $_core;
	private array $_optimize;
	private array $_engines = array();
	private array $_collation = array();
	private array $_totals = array(
		'size'        => 0,
		'free'        => 0,
		'index'       => 0,
		'rows'        => 0,
		'total'       => 0,
		'to_optimize' => 0,
		'to_repair'   => 0,
		'tables'      => 0,
	);
	private array $_totals_wp = array(
		'size'        => 0,
		'free'        => 0,
		'index'       => 0,
		'rows'        => 0,
		'total'       => 0,
		'to_optimize' => 0,
		'to_repair'   => 0,
		'tables'      => 0,
		'estimate'    => 'tiny',
	);

	public function __construct() {
		$this->_prefix      = sweeppress_prepare()->prefix();
		$this->_base_prefix = sweeppress_prepare()->base_prefix();

		$this->_optimize = array(
			'threshold' => sweeppress_settings()->get( 'db_table_optimize_threshold', 'sweepers' ),
			'min_size'  => sweeppress_settings()->get( 'db_table_optimize_min_size', 'sweepers' ) * 1024 * 1024,
		);

		$this->_tables = Prepare::instance()->get_tables_status();
		$this->_core   = array(
			'blog'    => $this->_prepare_wp_tables( 'blog' ),
			'global'  => $this->_prepare_wp_tables( 'global' ),
			'network' => $this->_prepare_wp_tables( 'network' ),
		);

		foreach ( $this->_tables as $name => $table ) {
			$this->_process_table( $name, $table );
		}

		$this->_totals['free_percentage']    = $this->_totals['size'] > 0 ? floor( 100 * ( $this->_totals['free'] / $this->_totals['size'] ) ) . '%' : '0%';
		$this->_totals_wp['free_percentage'] = $this->_totals_wp['size'] > 0 ? floor( 100 * ( $this->_totals_wp['free'] / $this->_totals_wp['size'] ) ) . '%' : '0%';

		if ( $this->_totals_wp['size'] > 1048576 * 14400 ) {
			$this->_totals_wp['estimate'] = 'huge';
		} else if ( $this->_totals_wp['size'] > 1048576 * 7200 ) {
			$this->_totals_wp['estimate'] = 'large';
		} else if ( $this->_totals_wp['size'] > 1048576 * 2400 ) {
			$this->_totals_wp['estimate'] = 'big';
		} else if ( $this->_totals_wp['size'] > 1048576 * 800 ) {
			$this->_totals_wp['estimate'] = 'medium';
		} else if ( $this->_totals_wp['size'] > 1048576 * 200 ) {
			$this->_totals_wp['estimate'] = 'small';
		}
	}

	public static function instance() : Database {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Database();
		}

		return $instance;
	}

	public function get_engines() : array {
		return $this->_engines;
	}

	public function get_collations() : array {
		return $this->_collation;
	}

	public function get_tables() : array {
		return $this->_tables;
	}

	public function total( string $name = '' ) {
		return $this->_totals[ $name ] ?? $this->_totals;
	}

	public function total_wp( string $name = '' ) {
		return $this->_totals_wp[ $name ] ?? $this->_totals_wp;
	}

	public function table( $name ) : array {
		$name = strtolower( $name );

		return $this->_tables[ $name ] ?? $this->_empty_stats( $name );
	}

	public function calculate( string $value, array $tables ) : int {
		$size = 0;

		if ( empty( $tables ) ) {
			foreach ( $this->_tables as $obj ) {
				$size += $obj[ $value ];
			}
		} else {
			foreach ( $tables as $table ) {
				$name = Prepare::instance()->wpdb()->$table ?? '';

				if ( ! empty( $name ) ) {
					$obj  = $this->table( $name );
					$size += $obj[ $value ];
				}
			}
		}

		return $size;
	}

	public function size() : ?string {
		switch ( $this->_totals_wp['estimate'] ) {
			default:
			case 'tiny':
				return __( 'Tiny', 'sweeppress' );
			case 'small':
				return __( 'Small', 'sweeppress' );
			case 'medium':
				return __( 'Medium', 'sweeppress' );
			case 'big':
				return __( 'Big', 'sweeppress' );
			case 'large':
				return __( 'Large', 'sweeppress' );
			case 'huge':
				return __( 'Huge', 'sweeppress' );
		}
	}

	private function _prepare_wp_tables( string $type ) : array {
		$list   = $this->_wordpress[ $type ] ?? array();
		$tables = array();

		foreach ( $list as $table ) {
			$prefix = $type == 'blog' ? $this->_prefix : $this->_base_prefix;

			$tables[ $table ] = $prefix . $table;
		}

		return $tables;
	}

	private function _process_table( string $name, array $table ) {
		if ( ! in_array( $table['engine'], $this->_engines ) ) {
			$this->_engines[ $table['engine'] ] = $table['engine'];
		}

		if ( ! in_array( $table['collation'], $this->_collation ) ) {
			$this->_collation[ $table['collation'] ] = $table['collation'];
		}

		foreach ( $this->_core as $type => $tables ) {
			foreach ( $tables as $real => $tbl_name ) {
				if ( $name == $tbl_name ) {
					$this->_tables[ $name ]['wp_table']         = $real;
					$this->_tables[ $name ][ 'is_wp_' . $type ] = true;
					break;
				}
			}
		}

		$this->_tables[ $name ]['is_wp']     = $this->_tables[ $name ]['is_wp_blog'] || $this->_tables[ $name ]['is_wp_global'] || $this->_tables[ $name ]['is_wp_network'];
		$this->_tables[ $name ]['is_non_wp'] = ! $this->_tables[ $name ]['is_wp'];

		if ( ! empty( $this->_tables[ $name ]['wp_table'] ) ) {
			if ( in_array( $this->_tables[ $name ]['wp_table'], $this->_system ) ) {
				$this->_tables[ $name ]['is_core'] = true;
			}
		}

		if ( $table['fragment'] >= $this->_optimize['threshold'] && $table['total'] > $this->_optimize['min_size'] ) {
			$this->_tables[ $name ]['for_optimization'] = true;
			$this->_totals['to_optimize'] ++;
		}

		if ( $table['is_corrupted'] ) {
			$this->_totals['to_repair'] ++;
		}

		if ( $this->_tables[ $name ]['is_non_wp'] ) {
			$this->_tables[ $name ]['detected_plugin'] = Storage::instance()->detect_table_plugin( $name );
		}

		$this->_totals['size']  += $table['size'];
		$this->_totals['free']  += $table['free'];
		$this->_totals['index'] += $table['index'];
		$this->_totals['rows']  += $table['rows'];
		$this->_totals['total'] += $table['total'];
		$this->_totals['tables'] ++;

		if ( $this->_tables[ $name ]['is_wp'] ) {
			$this->_totals_wp['size']  += $table['size'];
			$this->_totals_wp['free']  += $table['free'];
			$this->_totals_wp['index'] += $table['index'];
			$this->_totals_wp['rows']  += $table['rows'];
			$this->_totals_wp['total'] += $table['total'];
			$this->_totals_wp['tables'] ++;
		}
	}

	private function _empty_stats( string $name ) : array {
		return array(
			'table'            => $name,
			'engine'           => '',
			'total'            => 0,
			'size'             => 0,
			'free'             => 0,
			'index'            => 0,
			'rows'             => 0,
			'average_row_size' => 0,
			'auto_increment'   => 0,
			'fragment'         => 0,
			'created'          => '',
			'updated'          => '',
			'collation'        => '',
			'comment'          => '',
			'is_corrupted'     => '',
			'is_wp'            => false,
			'is_wp_blog'       => false,
			'is_wp_global'     => false,
			'is_wp_network'    => false,
			'is_core'          => false,
			'is_non_wp'        => false,
			'wp_table'         => '',
			'detected_plugin'  => '',
			'for_optimization' => false,
		);
	}
}
