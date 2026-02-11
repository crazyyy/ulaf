<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block_Login_DB class
 */
class Block_Login_DB {

	const DB_VERSION = '1.0';

	/**
	 * DB table name
	 *
	 * @var string
	 */
	private $tablename;

	const OPTION_NAME = 'adminoptim_block_login_db';

	const CACHE_GROUP = 'adminoptim_block_login';
	/**
	 * List of blocked login status
	 *
	 * @var string[]
	 */
	public $all_statuses = [
		'lock'    => 'locked',
		'release' => 'released',
		'delete'  => 'deleted',
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->tablename = $wpdb->prefix . 'blocked_logins';
		$this->setup_db();
	}

	/**
	 * Install new database if necessary
	 *
	 * @return void
	 */
	private function setup_db() {
		$db_option = get_option( self::OPTION_NAME, [] );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( empty( $db_option ) || 1 !== $db_option['db_installed'] || ( 1 === $db_option['db_installed'] && self::DB_VERSION !== $db_option['db_version'] ) ) {
			$sql = "CREATE TABLE {$this->tablename} (
					ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					ip_address varchar(40) NOT NULL,
					username varchar(24) NOT NULL,
					lockout_count int(10) unsigned NOT NULL,
					lockout_time int(10) NOT NULL,
					lockout_duration varchar(10) NOT NULL,
					release_time int(10) NOT NULL,
					status varchar(10) NOT NULL,
					PRIMARY KEY (ID),
					UNIQUE KEY (ip_address));";
			dbDelta( $sql );

			$db_option['db_installed'] = 1;
			$db_option['db_version']   = self::DB_VERSION;
			update_option( self::OPTION_NAME, $db_option, false );
		}
	}

	/**
	 * Database function to add blocked record
	 *
	 * @param array $postarr  list of block records.
	 *
	 * @return int
	 */
	public function insert_record( $postarr ) {
		global $wpdb;

		$defaults = array(
			'ip_address'       => '',
			'username'         => '',
			'lockout_count'    => 1,
			'lockout_time'     => time(),
			'lockout_duration' => '15',
			'status'           => 'locked',
		);

		$postarr = wp_parse_args( $postarr, $defaults );

		if ( isset( $postarr['ID'] ) ) {
			unset( $postarr['ID'] );
		}

		$ip_address       = $postarr['ip_address'];
		$username         = sanitize_user( $postarr['username'] );
		$lockout_count    = absint( $postarr['lockout_count'] );
		$lockout_time     = absint( $postarr['lockout_time'] );
		$lockout_duration = absint( $postarr['lockout_duration'] );
		$status           = in_array( $postarr['status'], [ 'locked', 'blacklisted', 'whitelisted','released' ], true ) ? $postarr['status'] : 'locked';

		$data = compact(
			'ip_address',
			'username',
			'lockout_count',
			'lockout_time',
			'lockout_duration',
			'status'
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert( $this->tablename, $data );
		return $wpdb->insert_id;
	}

	/**
	 * Retrieve lockout count from database
	 *
	 * @param string $ip_addr  IP address.
	 *
	 * @return int
	 */
	public function get_lockout_count( $ip_addr ) {
		global $wpdb;
		$is_ip_valid = filter_var( $ip_addr, FILTER_VALIDATE_IP );
		if ( $is_ip_valid ) {
			$cache_key = 'block_login_lockout_count' . $ip_addr;
			$count     = wp_cache_get( $cache_key, self::CACHE_GROUP );
			if ( false === $count ) {
				$count = $wpdb->get_var( $wpdb->prepare( 'SELECT lockout_count FROM `%1s` WHERE ip_address = %s', $this->tablename, $ip_addr ) ); // phpcs:ignore
				wp_cache_set( $cache_key, $count, self::CACHE_GROUP );
			}
			return ( null === $count ) ? 0 : (int) $count;
		} else {
			return 0;
		}
	}

	/**
	 * Update record in database
	 *
	 * @param string $ip_addr  IP address.
	 * @param string $username  User's username.
	 * @param string $status  Lockout status.
	 * @param int    $duration  Lockout duration.
	 *
	 * @return void
	 */
	public function update_record( $ip_addr, $username, $status, int $duration ) {
		global $wpdb;
		$is_ip_valid  = filter_var( $ip_addr, FILTER_VALIDATE_IP );
		$username     = sanitize_user( $username );
		$status       = in_array( $status, [ 'locked', 'released' ], true ) ? $status : 'locked';
		$lockout_time = time();
		if ( $is_ip_valid ) {
			$cache_key = 'block_login_have_row' . $ip_addr;
			$record    = wp_cache_get( $cache_key, self::CACHE_GROUP );
			if ( false === $record ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$record = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT * FROM `%1s` WHERE ip_address = %s', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
						$this->tablename,
						$ip_addr
					)
				);
				wp_cache_set( $cache_key, $record, self::CACHE_GROUP );
			}
			if ( null === $record ) {
				$this->insert_record(
					[
						'ip_address'       => $ip_addr,
						'username'         => $username,
						'lockout_count'    => 1,
						'lockout_time'     => $lockout_time,
						'lockout_duration' => $duration,
						'release_time'     => $lockout_time + $duration,
						'status'           => $status,
					]
				);
			} else {
				$count = $record->lockout_count + 1;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update(
					$this->tablename,
					[
						'lockout_count'    => $count,
						'lockout_time'     => $lockout_time,
						'lockout_duration' => $duration,
						'release_time'     => $lockout_time + $duration,
						'status'           => $status,
					],
					[ 'ip_address' => $ip_addr ]
				);
			}
		}
	}

	/**
	 * Get blocked ID
	 *
	 * @return array
	 */
	public static function get_ids() {
		global $wpdb;
		$tablename = $wpdb->prefix . 'blocked_logins';
		$cache_key = 'block_login_get_ids';
		$ids       = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false === $ids ) {
			$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM `%1s`', $tablename ) ); // phpcs:ignore
			wp_cache_set( $cache_key, $ids, self::CACHE_GROUP );
		}

		return $ids;
	}

	/**
	 * Get record by ID
	 *
	 * @param int $id  Record's ID.
	 *
	 * @return array|object|\stdClass|null
	 */
	public static function get_record_by_id( int $id ) {
		global $wpdb;
		$tablename = $wpdb->prefix . 'blocked_logins';
		$cache_key = 'block_login_get_record_' . $id;
		$record    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false === $record ) {
			$record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `%1s` WHERE ID = %d', $tablename, $id ) ); // phpcs:ignore
			wp_cache_set( $cache_key, $record, self::CACHE_GROUP );
		}

		return $record;
	}

	/**
	 * Delete record from db
	 *
	 * @param int $id  Record's ID.
	 *
	 * @return bool|int|\mysqli_result|null
	 */
	public function delete_record( int $id ) {
		global $wpdb;

		wp_cache_delete( 'block_login_get_ids', self::CACHE_GROUP );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->delete( $this->tablename, [ 'ID' => $id ] );
	}

	/**
	 * Manage IP address blocked status
	 *
	 * @param int    $ip_id  Record ID.
	 * @param string $status  Record status.
	 *
	 * @return array|\WP_Error
	 */
	public function manage_ip_status( int $ip_id, $status ) {
		global $wpdb;

		$record    = self::get_record_by_id( $ip_id );
		$ip_addr   = $record->ip_address;
		$ip_number = Block_Login::ip_to_number( $ip_addr );
		switch ( $status ) {
			case 'delete':
				delete_transient( 'adminoptim_locked_' . $ip_number );
				delete_transient( 'adminoptim_failed_login_' . $ip_number );
				$result = $this->delete_record( $ip_id );
				if ( $result > 0 ) {
					$response['message'] = wp_get_admin_notice(
						__( 'IP log deleted successfully.', 'admin-optimizer' ),
						[
							'type'        => 'success',
							'dismissible' => true,
						]
					);
				} else {
					$response = new \WP_Error(
						'adminoptim-error-delete-ip',
						wp_get_admin_notice(
							__( '<strong>Error</strong>: IP log not deleted.', 'admin-optimizer' ),
							[
								'type'        => 'error',
								'dismissible' => true,
							]
						)
					);
				}
				break;
			case 'release':
				delete_transient( 'adminoptim_locked_' . $ip_number );
				delete_transient( 'adminoptim_failed_login_' . $ip_number );
				$release_time = time();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$result = $wpdb->update(
					$this->tablename,
					[
						'status'       => 'released',
						'release_time' => $release_time,
					],
					[
						'ID'
						=> $ip_id,
					]
				);
				if ( $result > 0 ) {
					wp_cache_delete( 'block_login_get_record_' . $ip_id, self::CACHE_GROUP );
					$response['message']      = wp_get_admin_notice(
						__( 'IP lockout released successfully.', 'admin-optimizer' ),
						[
							'type'        => 'success',
							'dismissible' => true,
						]
					);
					$response['release_time'] = wp_date( 'Y-m-d H:i:s', $release_time );
					$response['status']       = __( 'Released', 'admin-optimizer' );
				} else {
					$response = new \WP_Error(
						'adminoptim-error-release-ip',
						wp_get_admin_notice(
							__( '<strong>Error</strong>: IP release not successful.', 'admin-optimizer' ),
							[
								'type'        => 'error',
								'dismissible' => true,
							]
						)
					);
				}

				break;
			case 'lock':
			default:
				$record    = self::get_record_by_id( $ip_id );
				$ip_addr   = $record->ip_address;
				$ip_number = Block_Login::ip_to_number( $ip_addr );
				set_transient( 'adminoptim_locked_' . $ip_number, true, DAY_IN_SECONDS );
				$lockout_time = time();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$result = $wpdb->update(
					$this->tablename,
					[
						'status'           => 'locked',
						'lockout_time'     => $lockout_time,
						'lockout_duration' => 86400,
						'release_time'     => $lockout_time + 86400,
					],
					[ 'ID' => $ip_id ]
				);
				if ( $result > 0 ) {
					wp_cache_delete( 'block_login_get_record_' . $ip_id, self::CACHE_GROUP );
					$response['message']          = wp_get_admin_notice(
						__( 'IP locked successfully.', 'admin-optimizer' ),
						[
							'type'        => 'success',
							'dismissible' => true,
						]
					);
					$response['lockout_time']     = wp_date( 'Y-m-d H:i:s', $lockout_time );
					$response['lockout_duration'] = __( '24 hours', 'admin-optimizer' );
					$response['release_time']     = wp_date( 'Y-m-d H:i:s', $lockout_time + 86400 );
					$response['status']           = __( 'Locked', 'admin-optimizer' );
				} else {
					$response = new \WP_Error(
						'adminoptim-error-lockining-ip',
						wp_get_admin_notice(
							__( '<strong>Error</strong>: IP record cannot be locked.', 'admin-optimizer' ),
							[
								'type'        => 'error',
								'dismissible' => true,
							]
						)
					);
				}

				break;
		}

		return $response;
	}

	/**
	 * Clean lockout
	 *
	 * @return void
	 */
	public function clean_lockout() {
		global $wpdb;

		$current_time = time();
		$records = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `%1s` WHERE release_time < %d AND status = 'locked'", $this->tablename, $current_time ) ); // phpcs:ignore
		if ( null !== $records ) {
			foreach ( $records as $record ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update( $this->tablename, [ 'status' => 'released' ], [ 'ID' => $record->ID ] );
				wp_cache_delete( 'block_login_get_record_' . $record->ID, self::CACHE_GROUP );
			}
		}
	}
}
