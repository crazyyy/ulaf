<?php

namespace BoltAudit\App\Repositories;

class DatabaseRepository {

	protected static $table_stats = null;

	public static function get_table_stats() {
		global $wpdb;

		if ( null !== self::$table_stats ) {
			return self::$table_stats;
		}

		$raw_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					TABLE_NAME AS table_name,
					ENGINE,
					TABLE_ROWS AS row_count,
					DATA_LENGTH AS data_size,
					INDEX_LENGTH AS index_size,
					(DATA_LENGTH + INDEX_LENGTH) AS total_size
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s",
				DB_NAME
			),
			ARRAY_A
		);

		// Format sizes for human readability
		self::$table_stats = array_map( function ( $table ) {
			$table['data_size_formatted']  = size_format( $table['data_size'], 2 );
			$table['index_size_formatted'] = size_format( $table['index_size'], 2 );
			$table['total_size_formatted'] = size_format( $table['total_size'], 2 );

			return $table;
		}, $raw_tables );

		return self::$table_stats;
	}

	public static function get_heavy_autoloaded_options( $limit = 10 ) {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) AS size
			 FROM {$wpdb->options}
			 WHERE autoload = 'yes'
			 ORDER BY size DESC
			 LIMIT {$limit}",
			ARRAY_A
		);
	}

	public static function get_option_counts() {
		global $wpdb;

		$total_options            = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );
		$total_autoloaded_options = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload = 'yes'" );
		$total_transient          = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%'" );
		$total_expired_transients = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_%' AND option_value < UNIX_TIMESTAMP()" );
		$total_autoloaded_size    = (int) $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'" );

		return [
			'total_options'            => $total_options,
			'total_transient'          => $total_transient,
			'total_expired_transients' => $total_expired_transients,
			'total_autoloaded_options' => $total_autoloaded_options,
			'total_autoloaded_size'    => $total_autoloaded_size,
		];
	}

	public static function get_largest_tables( $limit = 10 ) {
		$tables = self::get_table_stats();

		usort( $tables, function ( $a, $b ) {
			return $b['total_size'] <=> $a['total_size'];
		} );

		return array_slice( $tables, 0, $limit );
	}

	public static function get_engine_summary() {
		$tables = self::get_table_stats();

		$summary = [];

		foreach ( $tables as $table ) {
			$engine           = $table['ENGINE'] ?? 'UNKNOWN';
			$summary[$engine] = isset( $summary[$engine] ) ? $summary[$engine] + 1 : 1;
		}

		return $summary;
	}

        public static function get_empty_tables() {
                $tables = self::get_table_stats();

                return array_values( array_filter( $tables, fn( $table ) => (int) $table['row_count'] === 0 ) );
        }

	public static function get_total_db_size() {
		$tables = self::get_table_stats();

		$total = array_reduce( $tables, fn( $carry, $t ) => $carry + (int) $t['total_size'], 0 );

		return size_format( $total, 2 );
	}

	public static function get_index_efficiency() {
		$tables = self::get_table_stats();

		$stats = [];

		foreach ( $tables as $table ) {
			$index = (int) $table['index_size'];
			$data  = (int) $table['data_size'];

			$efficiency = $data ? round( ( $index / $data ) * 100, 2 ) : 0;

			$stats[] = [
				'table'       => $table['table_name'],
				'data_size'   => $data,
				'index_size'  => $index,
				'index_ratio' => $efficiency, // in percentage
			];
		}

		usort( $stats, fn( $a, $b ) => $b['index_ratio'] <=> $a['index_ratio'] );

		return array_slice( $stats, 0, 5 );
	}

	public static function get_all() {
		$table_stats   = self::get_table_stats(); // fetched once, cached
		$empty_tables  = self::get_empty_tables();
		$larget_tables = self::get_largest_tables();

		return [
			'tables'             => $table_stats,
			'total_tables'       => count( $table_stats ),
			'empty_tables'       => $empty_tables,
			'total_empty_tables' => count( $empty_tables ),
			'largest_tables'     => $larget_tables,
			'options'            => self::get_option_counts(),
			'engine_summary'     => self::get_engine_summary(),
			'db_size'            => self::get_total_db_size(),
			'index_efficiency'   => self::get_index_efficiency(),
			'heavy_autoloaded'   => self::get_heavy_autoloaded_options(),
		];
	}
}
