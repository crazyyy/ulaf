<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\v54\Core\Plugins\Settings as BaseSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings extends BaseSettings {
	public string $base = 'sweeppress';
	public string $plugin = 'sweeppress';
	public bool $cache = true;

	public array $network_groups = array(
		'storage',
	);
	public array $skip_export = array(
		'cache',
	);

	public array $settings = array(
		'core'       => array(
			'activated' => 0,
		),
		'statistics' => array(
			'months'   => array(),
			'total'    => array(),
			'database' => 'tiny',
		),
		'cache'      => array(
			'sweepers' => array(),
			'metadata' => array(),
		),
		'settings'   => array(
			'expand_cli'                  => false,
			'expand_rest'                 => false,
			'expand_admin_bar'            => true,
			'expand_backup'               => false,
			'backup_options'              => true,
			'backup_metadata'             => true,
			'backup_sweepers'             => true,
			'backup_sitemeta'             => true,
			'backup_format_compress'      => 'none',
			'admin_bar_show_quick'        => true,
			'admin_bar_show_auto'         => true,
			'admin_bar_quick_sweepers'    => array(
				'all-transients',
				'all-network-transients',
			),
			'log_removal_queries'         => false,
			'hide_backup_notices_v6'      => false,
			'hide_meta_notices'           => false,
			'monitor_daily'               => false,
			'monitor_daily_min_records'   => 200,
			'monitor_daily_min_size'      => 1,
			'monitor_daily_email'         => array(),
			'monitor_weekly'              => false,
			'monitor_weekly_email'        => array(),
			'monitor_weekly_min_records'  => 1000,
			'monitor_weekly_min_size'     => 2,
			'database_enable_truncate'    => false,
			'database_enable_drop'        => false,
			'meta_tracker_monitor'        => true,
			'meta_tracker_monitor_update' => true,
			'meta_tracker_monitor_get'    => false,
			'meta_allow_wp_deletion'      => false,
			'meta_allow_active_deletion'  => false,
			'serialized_debugpress_view'  => false,
		),
		'scheduler'  => array(
			'jobs'   => array(),
			'job_id' => 1,
		),
		'sweepers'   => array(
			'method'                                => 'auto',
			'dashboard_auto_quick'                  => true,
			'estimated_mode_full'                   => true,
			'estimated_cache'                       => true,
			'estimated_with_index'                  => false,
			'show_keep_days_notices'                => true,
			'keep_days_posts-auto-draft'            => 14,
			'keep_days_posts-spam'                  => 14,
			'keep_days_posts-trash'                 => 14,
			'keep_days_posts-revisions'             => 14,
			'keep_days_posts-draft-revisions'       => 14,
			'keep_days_comments-spam'               => 14,
			'keep_days_comments-trash'              => 14,
			'keep_days_comments-unapproved'         => 90,
			'keep_days_comments-pingback'           => 14,
			'keep_days_comments-trackback'          => 14,
			'keep_days_comments-ua'                 => 14,
			'keep_days_comments-akismet'            => 14,
			'keep_days_signups-inactive'            => 90,
			'keep_days_gravityforms_entry-spam'     => 14,
			'keep_days_gravityforms_entry-trash'    => 14,
			'keep_days_actionscheduler-log'         => 14,
			'keep_days_actionscheduler-failed'      => 14,
			'keep_days_actionscheduler-complete'    => 14,
			'keep_days_actionscheduler-canceled'    => 14,
			'keep_days_elementor_submissions-trash' => 14,
			'dupe_allow_postmeta'                   => array(),
			'dupe_allow_commentmeta'                => array(),
			'dupe_allow_termmeta'                   => array(),
			'dupe_allow_usermeta'                   => array(),
			'db_table_optimize_threshold'           => 40,
			'db_table_optimize_min_size'            => 6,
			'db_table_optimize_method'              => 'optimize',
			'last_used_timestamp'                   => array(),
		),
		'storage'    => array(
			'cron_detection'        => array(),
			'scan_plugins'          => array(),
			'scan_themes'           => array(),
			'monitor_meta_get'      => array(),
			'monitor_meta_update'   => array(),
			'monitor_option_get'    => array(),
			'monitor_option_update' => array(),
		),
	);

	public function count_usage_monitor_data( $scope, $type ) : int {
		$base = 'monitor_' . $scope . '_';

		if ( str_ends_with( $type, 'meta' ) ) {
			$type = substr( $type, 0, strlen( $type ) - 4 );
		}

		$get    = count( $this->current['storage'][ $base . 'get' ][ $type ] ?? array() );
		$update = count( $this->current['storage'][ $base . 'update' ][ $type ] ?? array() );

		return $get + $update;
	}

	public function add_monitor_value( $scope, $method, $type, $key, $source, $save = true ) {
		$base = 'monitor_' . $scope . '_' . $method;

		if ( ! isset( $this->current['storage'][ $base ][ $type ] ) ) {
			$this->current['storage'][ $base ][ $type ] = array();
		}

		if ( ! isset( $this->current['storage'][ $base ][ $type ][ $key ] ) ) {
			$this->current['storage'][ $base ][ $type ][ $key ] = array();
		}

		if ( ! in_array( $source, $this->current['storage'][ $base ][ $type ][ $key ] ) ) {
			$this->current['storage'][ $base ][ $type ][ $key ][] = $source;

			if ( $save ) {
				$this->save( 'storage' );
			}
		}
	}

	public function temp_disable_cache() {
		$this->cache = false;
	}

	public function temp_enable_cache() {
		$this->cache = true;
	}

	public function get_from_cache( string $key, string $group ) {
		$cache = $this->current['cache'][ $group ][ $key ] ?? array();

		if ( empty( $cache ) ) {
			return false;
		}

		$expire = $cache['expire'] ?? 0;
		$data   = $cache['data'] ?? array();

		if ( $expire < time() || empty( $data ) ) {
			return false;
		}

		return $data;
	}

	public function set_to_cache( string $key, $data, string $group, int $expiration = 0, bool $save = true ) {
		$expiration = $expiration === 0 ? apply_filters( 'sweeppress_cache_expiration', 7200 ) : $expiration;

		$this->current['cache'][ $group ][ $key ] = array(
			'expire' => time() + absint( $expiration ),
			'data'   => $data,
		);

		if ( $save ) {
			$this->save( 'cache' );
		}
	}

	public function get_sweeper_cache( string $sweeper ) {
		if ( $this->cache && $this->get( 'estimated_cache', 'sweepers' ) ) {
			return $this->get_from_cache( $sweeper, 'sweepers' );
		}

		return false;
	}

	public function set_sweeper_cache( string $sweeper, $data, int $expiration = 0 ) {
		if ( $this->get( 'estimated_cache', 'sweepers' ) ) {
			$this->set_to_cache( $sweeper, $data, 'sweepers', $expiration );
		}
	}

	public function delete_sweeper_cache( string $sweeper ) {
		if ( $this->get( 'estimated_cache', 'sweepers' ) ) {
			if ( isset( $this->current['cache']['sweepers'][ $sweeper ] ) ) {
				unset( $this->current['cache']['sweepers'][ $sweeper ] );

				$this->save( 'cache' );
			}
		}
	}

	public function purge_sweeper_cache() {
		$this->current['cache']['sweepers'] = array();
		$this->current['cache']['metadata'] = array();

		$this->save( 'cache' );
	}

	public function add_detected_cron_job( $plugin, $name, $job, $type = 'plugin' ) {
		if ( ! isset( $this->current['storage']['cron_detection'][ $plugin ] ) ) {
			$this->current['storage']['cron_detection'][ $plugin ] = array(
				'name' => $name,
				'type' => $type,
				'cron' => array(),
			);
		}

		if ( ! in_array( $job, $this->current['storage']['cron_detection'][ $plugin ]['cron'] ) ) {
			$this->current['storage']['cron_detection'][ $plugin ]['cron'][] = $job;
		}

		$this->save( 'storage' );
	}

	public function log_statistics( $input = array() ) {
		$default = array(
			'source'   => '',
			'records'  => 0,
			'size'     => 0,
			'time'     => 0,
			'jobs'     => 0,
			'tasks'    => 0,
			'sweepers' => array(),
		);

		$input = wp_parse_args( $input, $default );

		if ( in_array( $input['source'], array( 'quick', 'scheduler', 'panel', 'auto', 'cli', 'rest', 'adminbar', 'metadata', 'options', 'sitemeta' ) ) ) {
			$month = gmdate( 'Y-m' );

			if ( ! isset( $this->current['statistics']['months'][ $month ] ) ) {
				$this->current['statistics']['months'][ $month ] = $this->_statistics_empty();
			}

			$this->current['statistics']['months'][ $month ][ $input['source'] ] ++;
			$this->current['statistics']['months'][ $month ]['records'] += $input['records'] ?? 0;
			$this->current['statistics']['months'][ $month ]['size']    += $input['size'] ?? 0;
			$this->current['statistics']['months'][ $month ]['time']    += $input['time'] ?? 0;
			$this->current['statistics']['months'][ $month ]['jobs']    += $input['jobs'] ?? 0;
			$this->current['statistics']['months'][ $month ]['tasks']   += $input['tasks'] ?? 0;

			foreach ( $input['sweepers'] as $sweep => $data ) {
				if ( ! isset( $this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ] ) ) {
					$this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ] = array(
						'records' => 0,
						'size'    => 0,
						'time'    => 0,
						'counts'  => 0,
					);
				}

				$this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ]['records'] += $data['records'] ?? 0;
				$this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ]['size']    += $data['size'] ?? 0;
				$this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ]['time']    += $data['time'] ?? 0;
				$this->current['statistics']['months'][ $month ]['sweepers'][ $sweep ]['counts'] ++;

				$this->current['sweepers']['last_used_timestamp'][ $sweep ] = time();
			}

			if ( empty( $this->current['statistics']['total'] ) ) {
				$this->current['statistics']['total'] = $this->_statistics_empty();
			}

			$this->current['statistics']['total'][ $input['source'] ] ++;
			$this->current['statistics']['total']['records'] += $input['records'] ?? 0;
			$this->current['statistics']['total']['size']    += $input['size'] ?? 0;
			$this->current['statistics']['total']['time']    += $input['time'] ?? 0;
			$this->current['statistics']['total']['jobs']    += $input['jobs'] ?? 0;
			$this->current['statistics']['total']['tasks']   += $input['tasks'] ?? 0;

			foreach ( $input['sweepers'] as $sweep => $data ) {
				if ( ! isset( $this->current['statistics']['total']['sweepers'][ $sweep ] ) ) {
					$this->current['statistics']['total']['sweepers'][ $sweep ] = array(
						'records' => 0,
						'size'    => 0,
						'time'    => 0,
						'counts'  => 0,
					);
				}

				$this->current['statistics']['total']['sweepers'][ $sweep ]['records'] += $data['records'] ?? 0;
				$this->current['statistics']['total']['sweepers'][ $sweep ]['size']    += $data['size'] ?? 0;
				$this->current['statistics']['total']['sweepers'][ $sweep ]['time']    += $data['time'] ?? 0;
				$this->current['statistics']['total']['sweepers'][ $sweep ]['counts'] ++;
			}

			$this->save( 'statistics' );
			$this->save( 'sweepers' );
		}
	}

	public function get_statistics( string $month = '' ) : array {
		if ( empty( $month ) || ! isset( $this->current['statistics']['months'][ $month ] ) ) {
			$value          = $this->current['statistics']['total'];
			$value['label'] = isset( $value['started'] ) ? sprintf( __( 'Since: %s', 'sweeppress' ), $value['started'] ) : __( 'All Time', 'sweeppress' );
		} else {
			$value          = $this->current['statistics']['months'][ $month ];
			$value['label'] = sprintf( __( 'For Month: %s', 'sweeppress' ), date_create_from_format( 'Y-m', $month )->format( 'Y F' ) );
		}

		$value['size_total'] = $value['size'];

		if ( isset( $value['sweepers']['optimize-tables']['size'] ) ) {
			$value['size'] = $value['size'] - $value['sweepers']['optimize-tables']['size'];
		}

		return $value;
	}

	public function list_statistics() : array {
		$list = array(
			'' => __( 'All Time Statistics', 'sweeppress' ),
		);

		foreach ( array_keys( $this->current['statistics']['months'] ) as $month ) {
			$list[ $month ] = date_create_from_format( 'Y-m', $month )->format( 'Y F' );
		}

		return $list;
	}

	public function count_the_jobs() : int {
		return count( $this->current['scheduler']['jobs'] );
	}

	public function get_the_job( int $job_id ) : array {
		if ( $job_id > 0 && isset( $this->current['scheduler']['jobs'][ $job_id ] ) ) {
			$job = $this->current['scheduler']['jobs'][ $job_id ];

			return wp_parse_args( $job, $this->_default_job() );
		}

		return $this->_default_job();
	}

	public function save_the_job( array $job ) {
		$current        = $this->get_the_job( $job['id'] );
		$job['counter'] = $current['counter'] ?? 0;
		$job['report']  = $current['report'] ?? array();

		$this->current['scheduler']['jobs'][ $job['id'] ] = $job;

		$this->save( 'scheduler' );
	}

	public function delete_the_job( int $job_id ) {
		if ( isset( $this->current['scheduler']['jobs'][ $job_id ] ) ) {
			unset( $this->current['scheduler']['jobs'][ $job_id ] );

			$this->save( 'scheduler' );
		}
	}

	public function update_job_report( int $job_id, array $report ) {
		$job            = $this->get_the_job( $job_id );
		$job['counter'] = $job['counter'] ?? 0;
		$job['counter'] ++;
		$job['report'] = $report;

		$this->current['scheduler']['jobs'][ $job_id ] = $job;

		$this->save( 'scheduler' );
	}

	public function get_next_job_id() : int {
		$id = $this->current['scheduler']['job_id'];
		$this->current['scheduler']['job_id'] ++;

		return $id;
	}

	public function get_the_schedules() : array {
		$list = array();

		foreach ( wp_get_schedules() as $value => $data ) {
			$list[ $value ] = $data['display'];
		}

		return $list;
	}

	public function get_valid_schedules_keys() : array {
		return array_keys( wp_get_schedules() );
	}

	public function show_notice( string $name = 'backup' ) : bool {
		switch ( $name ) {
			default:
			case 'backup':
				return ! $this->get( 'hide_backup_notices_v6' );
			case 'meta':
				return ! $this->get( 'hide_meta_notices' );
		}
	}

	public function get_sweeper_last_used_timestamp( string $sweeper ) : int {
		return isset( $this->current['sweepers']['last_used_timestamp'][ $sweeper ] ) ? intval( $this->current['sweepers']['last_used_timestamp'][ $sweeper ] ) : 0;
	}

	public function get_method() {
		$method   = $this->get( 'method', 'sweepers' );
		$database = $this->get( 'database', 'statistics' );

		if ( $method == 'auto' ) {
			if ( $database == 'huge' ) {
				$method = 'request';
			} else if ( in_array( $database, array( 'large', 'big' ) ) ) {
				$method = 'partial';
			} else {
				$method = 'normal';
			}
		}

		return $method;
	}

	protected function constructor() {
		$this->info = new Information();

		add_action( 'sweeppress_load_settings', array( $this, 'init' ) );
	}

	protected function _default_job() : array {
		return array(
			'id'       => 0,
			'name'     => 'Sweep Job',
			'run'      => 'idle',
			'schedule' => 'weekly',
			'first'    => sweeppress_prepare()->datetime(),
			'sweep'    => array(),
			'counter'  => 0,
			'report'   => array(),
		);
	}

	protected function _statistics_empty() : array {
		return array(
			'started'   => gmdate( 'c' ),
			'quick'     => 0,
			'scheduler' => 0,
			'panel'     => 0,
			'auto'      => 0,
			'cli'       => 0,
			'rest'      => 0,
			'options'   => 0,
			'metadata'  => 0,
			'sitemeta'  => 0,
			'records'   => 0,
			'size'      => 0,
			'jobs'      => 0,
			'tasks'     => 0,
			'time'      => 0,
			'sweepers'  => array(),
		);
	}
}
