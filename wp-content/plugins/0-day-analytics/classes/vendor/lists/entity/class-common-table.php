<?php
/**
 * Responsible for the entities functionalities
 *
 * @package    advan
 * @subpackage entities
 * @since      1.1
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/0-day-analytics/
 */

declare(strict_types=1);

namespace ADVAN\Entities_Global;

use ADVAN\Helpers\WP_Helper;
use ADVAN\Entities\WP_Fatals_Entity;
use ADVAN\Entities\Hooks_Capture_Entity;
use ADVAN\Lists\Requests_List;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Sites table class
 */
if ( ! class_exists( '\ADVAN\Entities_Global\Common_Table' ) ) {

	/**
	 * Base class for all the entities
	 *
	 * @since 2.1.0
	 */
	class Common_Table {

		/**
		 * Validate a table identifier (letters, numbers and underscore only).
		 * Prevents cross-database references and injection via dots/backticks.
		 *
		 * @param string $name Table name to validate.
		 * @return bool
		 */
		protected static function validate_table_name( string $name ): bool {
			return (bool) preg_match( '/^[A-Za-z0-9_]+$/', $name );
		}

		/**
		 * All MySQL integer types.
		 *
		 * @property const array Holds the MySql integer data types
		 */
		public const INT_TYPES = array(
			'TINYINT'   => 'TINYINT',
			'SMALLINT'  => 'SMALLINT',
			'MEDIUMINT' => 'MEDIUMINT',
			'INT'       => 'INT',
			'BIGINT'    => 'BIGINT',
			'BIT'       => 'BIT',
		);

		/**
		 * All MySQL float types.
		 *
		 * @property const array Holds the MySql float data types
		 */
		public const FLOAT_TYPES = array(
			'DECIMAL' => 'DECIMAL',
			'FLOAT'   => 'FLOAT',
			'DOUBLE'  => 'DOUBLE',
		);

		/**
		 * Name of the table ID
		 *
		 * @var string
		 *
		 * @since 2.1.0
		 */
		private static $id = '';

		/**
		 * Name of the real table ID
		 *
		 * @var string
		 *
		 * @since 2.1.0
		 */
		private static $real_id = '';

		/**
		 * The name of the table
		 *
		 * @var string
		 *
		 * @since 2.1.0
		 */
		protected static $table_name = '';

		/**
		 * Stores info for the table columns - that is extracted from the MySQL server.
		 *
		 * @var array
		 *
		 * @since 2.1.0
		 */
		protected static $columns_info = array();

		/**
		 * Class cache that holds all of the tables in schema.
		 *
		 * @var array
		 *
		 * @since 2.2.0
		 */
		private static $tables = array();

		/**
		 * Class cache that holds all core tables of WP.
		 *
		 * @var array
		 *
		 * @since 2.2.0
		 */
		private static $core_tables = array();

		/**
		 * Holds the prepared options for speeding the proccess
		 *
		 * @var array
		 *
		 * @since 2.1.0
		 */
		protected static $admin_columns = array();

		/**
		 * Class cache keeps the size of the table
		 *
		 * @var int
		 *
		 * @since 2.1.2
		 */
		private static $table_size = null;

		/**
		 * Inner class cache to store the table status
		 *
		 * @var array
		 *
		 * @since 2.4.1
		 */
		private static $table_stat = array();

		/**
		 * Inits the class and sets the vars
		 *
		 * @param string $table_name - The name of the table to use.
		 *
		 * @return void
		 *
		 * @since 2.1.0
		 */
		public static function init( string $table_name ) {
			self::$table_name = $table_name;
		}

		/**
		 * Returns the name of the table
		 *
		 * @return string
		 *
		 * @since 2.1.0
		 */
		public static function get_name(): string {

			return static::$table_name;
		}

		/**
		 * Checks if give table exists
		 *
		 * @param string $table_name - The table to check for. If empty checks for the current table.
		 * @param \wpdb  $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return boolean
		 *
		 * @since 2.1.0
		 */
		public static function check_table_exists( string $table_name = '', $connection = null ): bool {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$_wpdb = $connection;
				}
			} else {
				global $wpdb;

				$_wpdb = $wpdb;
			}

			if ( '' === $table_name ) {
				$table_name = static::get_name();
			} else {
				// Basic table name validation (letters, numbers + underscore only; dot disallowed to prevent cross-db reference).
				if ( ! is_string( $table_name ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $table_name ) ) {
					new \WP_Error( 'invalid_table', 'Invalid table name.' );
					return false;
				}
			}

			foreach ( $_wpdb->get_col( 'SHOW TABLES', 0 ) as $table ) {
				if ( $table === $table_name ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Executes query.
		 *
		 * Important - query string is not checked nor validated, the calling script is responsible for that.
		 *
		 * @param string $query - The query which needs to be executed.
		 * @param \wpdb  $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return array
		 *
		 * @since 2.1.0
		 */
		public static function execute_query( string $query, $connection = null, ?array $args = null ) {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$_wpdb = $connection;
				} else {
					global $wpdb;
					$_wpdb = $wpdb;
				}
			} else {
				global $wpdb;
				$_wpdb = $wpdb;
			}

			if ( is_array( $args ) && ! empty( $args ) ) {
				$prepared = $_wpdb->prepare( $query, $args );
				return $_wpdb->query( $prepared );
			}

			return $_wpdb->query( $query );
		}

		/**
		 * Calls for the create table syntax and executes the query.
		 *
		 * @return void
		 *
		 * @since 2.1.0
		 */
		public static function create_table() {
			// self::execute_query( static::get_create_table_sql() );
		}

		/**
		 * Drop the table from the DB.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 * @param string           $table_name - The name of the table, if one is not provided, the default will be used.
		 * @param \wpdb            $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 2.1.0
		 */
		public static function drop_table( ?\WP_REST_Request $request = null, string $table_name = '', $connection = null ) {

			// If coming from REST context, enforce capability check.
			if ( null !== $request && ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'forbidden',
					__( 'Sorry, you are not allowed to perform this action.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}

			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$_wpdb = $connection;
				} else {
					global $wpdb;
					$_wpdb = $wpdb;
				}
			} else {
				global $wpdb;
				$_wpdb = $wpdb;
			}

			if ( null !== $request ) {
				$table_name = $request->get_param( 'table_name' );
			}

			if ( '' === $table_name ) {
				$table_name = static::get_name();
			}

			// Validate table name strictly.
			if ( ! self::validate_table_name( $table_name ) ) {
				return new \WP_Error(
					'invalid_table',
					__( 'Invalid table name.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( ! \in_array( $table_name, self::get_wp_core_tables(), true )
			&& \in_array( $table_name, self::get_tables( $_wpdb ), true ) ) {

				// Use backticks around table name (already validated) to guard against edge cases.
				self::execute_query( 'DROP TABLE IF EXISTS `' . $table_name . '`', $_wpdb );
			} elseif ( null !== $request ) { // Call is coming from REST API.
				return new \WP_Error(
					'core_table',
					__( 'Can not delete core table.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( null !== $request ) { // Call is coming from REST API.
				return rest_ensure_response(
					array(
						'success' => true,
					)
				);
			}
		}

		/**
		 * Truncates the table.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 * @param string           $table_name - The name of the table, if one is not provided, the default will be used.
		 * @param \wpdb            $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 2.4.1
		 */
		public static function truncate_table( ?\WP_REST_Request $request = null, string $table_name = '', $connection = null ) {
			// If coming from REST context, enforce capability check for destructive action.
			if ( null !== $request && ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'forbidden',
					__( 'Sorry, you are not allowed to perform this action.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$_wpdb = $connection;
				} else {
					global $wpdb;
					$_wpdb = $wpdb;
				}
			} else {
				global $wpdb;
				$_wpdb = $wpdb;
			}

			if ( null !== $request ) {
				$table_name = $request->get_param( 'table_name' );
			}

			if ( '' === $table_name ) {
				$table_name = static::get_name();
			}

			// Validate table name strictly.
			if ( ! self::validate_table_name( $table_name ) ) {
				return new \WP_Error(
					'invalid_table',
					__( 'Invalid table name.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( \in_array( $table_name, self::get_tables( $_wpdb ), true ) ) {

				if ( ! \in_array( $table_name, self::get_wp_core_tables(), true ) ) {

					// Use backticks around table name (already validated) to guard against edge cases.
					self::execute_query( 'TRUNCATE TABLE `' . $table_name . '`', $_wpdb );
				} else {
					return new \WP_Error(
						'truncate_table',
						__( 'You are not allowed to truncate WP Core table.', '0-day-analytics' ),
						array( 'status' => 400 )
					);
				}
			} elseif ( null !== $request ) { // Call is coming from REST API.
				return new \WP_Error(
					'truncate_table',
					__( 'Can not truncate table.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( null !== $request ) { // Call is coming from REST API.
				return rest_ensure_response(
					array(
						'success' => true,
					)
				);
			}
		}

		/**
		 * Returns the name of the id column for the table
		 *
		 * @return string
		 *
		 * @since 2.1.0
		 */
		public static function get_id_name(): string {
			return static::$id;
		}

		/**
		 * Returns the name of the real id column for the table - some times there are different ids - for internal use and for global use.
		 *
		 * @return string
		 *
		 * @since 2.1.0
		 */
		public static function get_real_id_name(): string {
			if ( empty( self::$real_id ) ) {

				global $wpdb;

				// Ensure table name safety and quote it.
				$table = self::get_name();
				if ( ! self::validate_table_name( $table ) ) {
					return '';
				}

				$sql = 'SHOW KEYS FROM `' . $table . '` WHERE Key_name = \'PRIMARY\'';

				$result = $wpdb->get_results(
					$sql,
					ARRAY_A
				);

				if ( \is_array( $result ) && ! empty( $result ) && isset( $result[0]['Column_name'] ) ) {
					static::$real_id = $result[0]['Column_name'];
				} else {
					$sql = 'SHOW INDEX FROM  `' . $table . '`';

					$result = $wpdb->get_results(
						$sql,
						ARRAY_A
					);
					if ( \is_array( $result ) && ! empty( $result ) && isset( $result[0]['Column_name'] ) ) {
						static::$real_id = $result[0]['Column_name'];
					}

					if ( empty( static::$real_id ) ) {
						$columns         = self::get_column_names();
						static::$real_id = reset( $columns );
					}
				}
			}

			return static::$real_id;
		}

		/**
		 * Checks for given column existence using custom connection.
		 *
		 * @param string       $col_name - The name of the column.
		 * @param string       $col_type - Type of the column.
		 * @param string       $table_name - The name of the table.
		 * @param boolean|null $is_null - Is it null.
		 * @param mixed        $key - Is it key.
		 * @param mixed        $default - The default value of the column.
		 * @param mixed        $extra - Extra parameters.
		 *
		 * @return boolean - True if the column exists and all given parameters are the same, false otherwise.
		 *
		 * @since 2.1.0
		 */
		public static function check_column(
			string $col_name,
			string $col_type,
			string $table_name = '',
			?bool $is_null = null,
			$key = null,
			$default = null,
			$extra = null ): bool {

			global $wpdb;

			if ( '' === $table_name ) {
				$table_name = static::get_name();
			}

			// Validate and quote table identifier to prevent injection.
			if ( ! self::validate_table_name( $table_name ) ) {
				return false;
			}
			$table_quoted = '`' . $table_name . '`';

			$diffs   = 0;
			$results = $wpdb->get_results( 'DESC ' . $table_quoted ); // phpcs:ignore

			foreach ( $results as $row ) {

				if ( $row->Field === $col_name ) { // phpcs:ignore

					// Got our column, check the params.
					if ( ( null !== $col_type ) && ( strtolower( str_replace( ' ', '', $row->Type ) ) !== strtolower( str_replace( ' ', '', $col_type ) ) ) ) { // phpcs:ignore
						++$diffs;
					}
					if ( ( null !== $is_null ) && ( $row->Null !== $is_null ) ) { // phpcs:ignore
						++$diffs;
					}
					if ( ( null !== $key ) && ( $row->Key !== $key ) ) { // phpcs:ignore
						++$diffs;
					}
					if ( ( null !== $default ) && ( $row->Default !== $default ) ) { // phpcs:ignore
						++$diffs;
					}
					if ( ( null !== $extra ) && ( $row->Extra !== $extra ) ) { // phpcs:ignore
						++$diffs;
					}

					if ( $diffs > 0 ) {
						return false;
					}

					return true;
				} // End if found our column.
			}

			return false;
		}

		/**
		 * Getter for the collected table column info
		 *
		 * @return array
		 *
		 * @since 3.2.0
		 */
		public static function get_columns_info(): array {
			if ( empty( static::$columns_info ) ) {
				global $wpdb;

				$query = $wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$wpdb->esc_like( self::get_name() )
				);

				if ( $wpdb->get_var( $query ) == self::get_name() ) {
					static::$columns_info = $wpdb->get_results(
						'DESC `' .
						self::get_name() . '`',
						ARRAY_A
					);
				}
			}

			return static::$columns_info;
		}

		/**
		 * Default find method
		 *
		 * @param array $data Must contains formats and data.
		 *
		 * @return int bool
		 *
		 * @since 3.8.0
		 */
		public static function find( array $data ) {
			global $wpdb;

			/**
			 * \WPDB has very powerful method called process_fields @see \WPDB::process_fields().
			 * Unfortunately this method is not accessible, because it is marked protected. The best solution at the moment is to clone the class, lower the visibility and use the method.
			 *
			 * That of course takes resources so possible solution is to add also caching to this method, so that is marked as todo below.
			 *
			 * TODO: Add caching functionality to the method.
			 */
			$wpdb_clone = new class() extends \wpdb {

				public function __construct() {
					$dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
					$dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
					$dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
					$dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';

					parent::__construct( $dbuser, $dbpassword, $dbname, $dbhost );
				}

				public function process_fields( $name, $data, $formats ) {
					return parent::process_fields( $name, $data, $formats );
				}

			};

			$where_clause = $wpdb_clone->process_fields(
				self::get_name(),
				$data['data'],
				$data['formats']
			);

			$where_data = self::prepare_full_where( $where_clause );

			$conditions = $where_data['conditions'];
			$values     = $where_data['values'];

			$wpdb->check_current_query = false;

			$result = $wpdb->get_results(
				$wpdb->prepare(
			// phpcs:disable
			'SELECT * from `' . self::get_name() . '` WHERE ' . $conditions,
			$values
			// phpcs:enable
				),
				ARRAY_A
			);

			self::check_error();

			return $result;
		}

		/**
		 * Checks for errors and throws exception if any
		 *
		 * @throws \Exception Last wpdb error.
		 *
		 * @return void
		 */
		protected static function check_error() {
			global $wpdb;
			if ( '' !== $wpdb->last_error ) {
				throw new \Exception( 'Error with query - check the logs' );
			}
		}

		/**
		 * Prepares full where clause
		 *
		 * @param array        $where_clause - Array prepared based on fields and values from the WP_DB.
		 * @param string       $condition - The where clause condition - default AND.
		 * @param string|null  $criteria - The criteria to check for.
		 * @param boolean|null $left_pref - For any starting value - partial where clause.
		 * @param boolean|null $right_pref - For any ending value - partial where clause.
		 *
		 * @return array
		 *
		 * @since 2.1.0
		 */
		public static function prepare_full_where( array $where_clause,
			string $condition = ' AND ',
			?string $criteria = ' = ',
			?bool $left_pref = false,
			?bool $right_pref = false
			): array {

			$conditions = array();
			$values     = array();

			// Sanitize boolean connector and comparison operator.
			$condition_sanitized = strtoupper( trim( (string) $condition ) );
			if ( ! in_array( $condition_sanitized, array( 'AND', 'OR' ), true ) ) {
				$condition_sanitized = 'AND';
			}

			$crit        = strtoupper( trim( (string) $criteria ) );
			$allowed_ops = array( '=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE', 'REGEXP', 'IS', 'IS NOT' );
			if ( ! in_array( $crit, $allowed_ops, true ) ) {
				$crit = '=';
			}

			foreach ( $where_clause as $field => $value ) {
				if ( is_null( $value['value'] ) ) {
					$conditions[] = '`' . self::get_name() . '` . `' . $field . '` IS null';
					continue;
				}

				$conditions[] = '`' . self::get_name() . '` . `' . $field . '` ' . $crit . ' ' .
				$value['format'];
				$values[]     =
				( ( $left_pref ) ? ' % ' : '' ) .
				$value['value'] .
				( ( $right_pref ) ? ' % ' : '' );
			}

			$conditions = implode( ' ' . $condition_sanitized . ' ', $conditions );

			return array(
				'conditions' => $conditions,
				'values'     => $values,
			);
		}

		/**
		 * Collects table column names
		 *
		 * @return array
		 *
		 * @since 2.4.1
		 */
		public static function get_column_names(): array {
			$array = array_column( self::get_columns_info(), 'Field', 'Field' );

			return $array;
		}

		/**
		 * Returns the class shortname
		 *
		 * @return string
		 *
		 * @since 2.4.1
		 */
		public static function get_entity_name(): string {
			return ( new \ReflectionClass( get_called_class() ) )->getShortName();
		}

		/**
		 * Default delete method
		 *
		 * @param integer $id - The real id of the table.
		 *
		 * @return int|bool
		 *
		 * @since 2.4.1
		 */
		public static function delete_by_id( int $id ) {
			global $wpdb;

			$result = $wpdb->delete(
				self::get_name(),
				array( self::get_real_id_name() => $id ),
				array( '%d' )
			);

			self::check_error();

			return $result;
		}

		/**
		 * Default delete method for API
		 *
		 * @param array $data - Array with key and value pair.
		 *
		 * @return int bool
		 *
		 * @since 2.4.1
		 */
		public static function delete_data( array $data ) {
			global $wpdb;

			if ( isset( $data['data'][ self::get_real_id_name() ] ) ) {
				return self::delete_by_id( (int) $data['data'][ self::get_real_id_name() ] );
			} else {

				$where        = $data['data'];
				$where_format = $data['formats'];

				$result = $wpdb->delete( self::get_name(), $where, $where_format );

				self::check_error();

				return $result;
			}
		}

		/**
		 * Default update method
		 *
		 * @param array $data - Array with key and value pair.
		 *
		 * @return int bool
		 *
		 * @since 2.4.1
		 */
		public static function update_data( array $data ) {
			global $wpdb;

			$where_clause = self::extract_where( $data );

			$where        = $where_clause['where'];
			$where_format = $where_clause['whereFormat'];

			$result = $wpdb->update( self::get_name(), $data['data'], $where, $data['formats'], $where_format );

			self::check_error();

			return $result;
		}

		/**
		 * Default insert method
		 *
		 * @param array $data - Array with key and value pair.
		 *
		 * @return int|bool
		 *
		 * @since 2.4.1
		 */
		public static function insert_data( array $data ) {
			global $wpdb;

			$wpdb->insert(
				self::get_name(),
				$data['data'],
				$data['formats']
			);

			self::check_error();

			return $wpdb->insert_id;
		}

		/**
		 * Backups the table.
		 * Format is the table name + yearmonthday-hour:minute:second
		 *
		 * @return void
		 *
		 * @since 2.1.0
		 */
		public static function backup_table() {
			global $wpdb;

			// Validate base table name and assemble safe backup table identifier with timestamp.
			if ( ! self::validate_table_name( self::get_name() ) ) {
				return new \WP_Error( 'invalid_table', 'Invalid base table name.' );
			}
			$new_table = self::get_name() . gmdate( 'YmdHis' );
			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $new_table ) ) {
				$new_table = preg_replace( '/[^A-Za-z0-9_]/', '_', $new_table );
			}

			$sql = 'CREATE TABLE `' . $new_table . '` LIKE `' . self::get_name() . '`';

			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$sql = 'INSERT INTO `' . $new_table . '` SELECT * FROM `' . self::get_name() . '`';

			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Extracts array with where values / formats from prepared data array
		 *
		 * @param array $data - The array to extract the data from.
		 *
		 * @return array
		 *
		 * @since 2.4.1
		 */
		protected static function extract_where( array &$data ): array {
			$where[ self::get_real_id_name() ] = $data['data'][ self::get_real_id_name() ];
			unset( $data['data'][ self::get_real_id_name() ] );
			$where_format[ self::get_real_id_name() ] = $data['formats'][ self::get_real_id_name() ];
			unset( $data['formats'][ self::get_real_id_name() ] );

			return array(
				'where'       => $where,
				'whereFormat' => $where_format,
			);
		}

		/**
		 * Returns the table CMS admin fields
		 *
		 * @return array
		 *
		 * @since 2.1.0
		 */
		public static function get_column_names_admin(): array {
			$clmns = self::get_columns_info();

			$array_clmns = array();

			foreach ( $clmns as $column ) {
				$array_clmns[ $column['Field'] ] = $column['Field'] . '<br>' . $column['Type'];
			}

			return $array_clmns;
		}

		/**
		 * Adds columns to the screen options screed
		 *
		 * @param array $columns - Array of column names.
		 *
		 * @return array
		 *
		 * @since 2.1.0
		 */
		public static function manage_columns( $columns ): array {
			if ( empty( self::$admin_columns ) ) {
				$screen_options = self::get_column_names_admin();

				$table_columns = array(
					'cb' => '<input type="checkbox" />', // to display the checkbox.
				);

				self::$admin_columns = \array_merge( $table_columns, $screen_options, $columns );
			}

			return self::$admin_columns;
		}

		/**
		 * Returns a list with all available tables.
		 *
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return array
		 *
		 * @since 2.1.0
		 */
		public static function get_tables( $connection = null ): array {

			if ( empty( self::$tables ) ) {
				if ( null !== $connection ) {
					if ( $connection instanceof \wpdb ) {
						$_wpdb = $connection;
					}
				} else {
					global $wpdb;
					$_wpdb = $wpdb;
				}

				$_wpdb->suppress_errors( true );
				$results = $_wpdb->get_results(
					// $wpdb->prepare(
						// 'SELECT table_name FROM information_schema.tables WHERE table_schema = %s;',
					'SHOW TABLES;',
					// $wpdb->dbname
					// ),
					\ARRAY_A
				);

				if ( '' !== $_wpdb->last_error || null === $results ) {

					self::$tables = self::get_wp_core_tables();

				} else {
					foreach ( $results as $table ) {
						self::$tables[] = reset( $table );
					}
				}

				$_wpdb->suppress_errors( false );
			}

			return self::$tables;
		}

		/**
		 * Returns all of the core WP tables.
		 *
		 * @return array
		 *
		 * @since 2.2.0
		 */
		public static function get_wp_core_tables() {
			if ( empty( self::$core_tables ) ) {
				global $wpdb;

				self::$core_tables = $wpdb->tables( 'all' );
			}

			return self::$core_tables;
		}

		/**
		 * Returns the table size in Megabyte format
		 *
		 * @return int
		 *
		 * @since 2.1.2
		 */
		public static function get_table_size() {
			if ( null === self::$table_size ) {
				global $wpdb;

				$sql = $wpdb->prepare(
					'SELECT ROUND(((data_length + index_length)), 2) AS `Size (B)` FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s;',
					$wpdb->dbname,
					self::get_name()
				);

				$wpdb->suppress_errors( true );
				$results = $wpdb->get_var( $sql );

				if ( '' !== $wpdb->last_error || null === $results ) {

					$results = array();

				}

				$wpdb->suppress_errors( false );

				if ( $results ) {
					self::$table_size = $results;
				} else {
					self::$table_size = 0;
				}
			}

			return self::$table_size;
		}

		/**
		 * Extracts table information and returns it
		 *
		 * @return array
		 *
		 * @since 2.3.0
		 */
		public static function get_table_status(): array {

			if ( empty( self::$table_stat ) ) {
				global $wpdb;

				// Parameterize SHOW TABLE STATUS LIKE query for consistency and safety.
				$sql = $wpdb->prepare( 'SHOW TABLE STATUS FROM `' . $wpdb->dbname . '` LIKE %s;', self::get_name() );

				$wpdb->suppress_errors( true );

				$results = $wpdb->get_results( $sql, \ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				if ( '' !== $wpdb->last_error || null === $results ) {

					$results = array();

				}

				$wpdb->suppress_errors( false );

				self::$table_stat = $results;
			}

			return self::$table_stat;
		}

		/**
		 * Returns the default table to show if none selected.
		 *
		 * @return string
		 *
		 * @since 2.4.1
		 */
		public static function get_default_table(): string {
			global $wpdb;

			return $wpdb->prefix . 'options';
		}

		/**
		 * Extracts single row data from given table and shows it in HTML format.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 3.2.0
		 */
		public static function extract_row_data( \WP_REST_Request $request ) {
			// Enforce capability check for data disclosure.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'forbidden',
					__( 'Sorry, you are not allowed to perform this action.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}
			$table_name = $request->get_param( 'table_name' );

			if ( '' === trim( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table name is not provided.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( ! self::check_table_exists( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table does not exist.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			$id = $request->get_param( 'id' );

			if ( empty( $id ) ) {
				return new \WP_Error(
					'show_row',
					__( 'ID is not provided or wrong.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			self::init( $table_name );

			global $wpdb;

			$query = $wpdb->prepare(
				'SELECT * FROM `' . self::get_name() . '` WHERE `' . self::get_real_id_name() . '` = %s;', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$id
			);

			$wpdb->suppress_errors( true );

			$results = $wpdb->get_results( $query, \ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( '' !== $wpdb->last_error || null === $results ) {

				$results = array();

			}

			$wpdb->suppress_errors( false );

			if ( ! empty( $results ) ) {
				\ob_start();

				?>
				<table class="widefat striped table-view-list" style="max-width:100%;table-layout: fixed;">
					<col width="20%" />
					<col width="80%" />
					<thead>
						<tr>
							<th>
								<?php \esc_html_e( 'Column name', '0-day-analytics' ); ?>
							</th>
							<th>
								<?php \esc_html_e( 'Column value', '0-day-analytics' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
				<?php

				foreach ( $results[0] as $key => $value ) {
					?>
					<tr>
						<td width="40%"><strong><?php echo \esc_html( $key ); ?></strong></td>
						<td><?php echo ( self::format_value_for_html( $value ) );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
				</table>
				<?php
				$message = \ob_get_clean();

				return rest_ensure_response(
					array(
						'success'    => true,
						'mail_body'  => $message,
						'table_name' => $table_name,
					)
				);

			} else {
				return new \WP_Error(
					'empty_row',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Loads single row data..
		 *
		 * @param mixed $id - The ID of the row to load.
		 *
		 * @return array|\WP_Error
		 *
		 * @since 3.2.0
		 */
		public static function load_row_data( $id ) {
			$table_name = self::get_name();

			if ( '' === trim( $table_name ) ) {
				return new \WP_Error(
					'edit_row',
					__( 'Table name is not provided.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( ! self::check_table_exists( $table_name ) ) {
				return new \WP_Error(
					'edit_row',
					__( 'Table does not exist.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( empty( $id ) ) {
				return new \WP_Error(
					'edit_row',
					__( 'ID is not provided or wrong.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			global $wpdb;

			$query = $wpdb->prepare(
				'SELECT * FROM `' . self::get_name() . '` WHERE `' . self::get_real_id_name() . '` = %s;', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$id
			);

			$wpdb->suppress_errors( true );

			$results = $wpdb->get_results( $query, \ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( '' !== $wpdb->last_error || null === $results ) {

				$results = array();

			}

			$wpdb->suppress_errors( false );

			if ( ! empty( $results ) ) {

				return $results[0];

			} else {
				return new \WP_Error(
					'empty_row',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Extracts single row data from given table and shows it in HTML format.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 3.2.0
		 */
		public static function extract_fatals_row_data( \WP_REST_Request $request ) {
			// Enforce capability check for data disclosure.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'forbidden',
					__( 'Sorry, you are not allowed to perform this action.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}
			$table_name = $request->get_param( 'table_name' );

			if ( '' === trim( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table name is not provided.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( ! self::check_table_exists( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table does not exist.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( WP_Fatals_Entity::get_table_name() !== $table_name ) {
				return new \WP_Error(
					'show_row',
					__( 'Wrong call.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			$id = $request->get_param( 'id' );

			if ( empty( $id ) ) {
				return new \WP_Error(
					'show_row',
					__( 'ID is not provided or wrong.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			self::init( $table_name );

			global $wpdb;

			$query = $wpdb->prepare(
				'SELECT * FROM `' . self::get_name() . '` WHERE `' . self::get_real_id_name() . '` = %s;', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$id
			);

			$wpdb->suppress_errors( true );

			$results = $wpdb->get_results( $query, \ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( '' !== $wpdb->last_error || null === $results ) {

				$results = array();

			}

			$wpdb->suppress_errors( false );

			if ( ! empty( $results ) ) {
				\ob_start();

				?>
				<table class="widefat striped table-view-list" style="max-width:100%;table-layout: fixed;">
					<col width="20%" />
					<col width="80%" />
					<thead>
						<tr>
							<th>
								<?php \esc_html_e( 'Column name', '0-day-analytics' ); ?>
							</th>
							<th>
								<?php \esc_html_e( 'Column value', '0-day-analytics' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
				<?php

				foreach ( $results[0] as $key => $value ) {
					if ( ! \in_array( $key, \array_keys( WP_Fatals_Entity::get_details_columns() ) ) ) {
						continue;
					}
					?>
					<tr>
						<td width="40%"><strong><?php echo \esc_html( WP_Fatals_Entity::get_details_columns()[ $key ] ); ?></strong></td>

						<?php
						if ( 'backtrace_segment' === $key ) {
							?>
							<td><?php echo \wp_kses_post( Requests_List::format_trace( $value, -1 ) );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php
						} elseif ( 'error_file' === $key ) {
							if ( isset( $value ) && ! empty( $value ) ) {

								$query_array = array(
									'_wpnonce' => \wp_create_nonce( 'source-view' ),
									'action'   => 'log_source_view',
								);

								$query_array['error_file'] = $value;
								$query_array['error_line'] = 1;

								if ( isset( $results[0]['error_line'] ) && ! empty( $results[0]['error_line'] ) ) {
									$query_array['error_line'] = $results[0]['error_line'];
								}

								$query_array['TB_iframe'] = 'true';

								$view_url = \esc_url_raw(
									\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
								);

								$title = __( 'Viewing: ', '0-day-analytics' ) . $query_array['error_file'];

								$anchor  = '<a href="' . esc_url( $view_url ) . '" title="' . esc_attr( $title ) . '" class="thickbox view-source">';
								$anchor .= esc_html( $query_array['error_file'] . ':' . $query_array['error_line'] );
								$anchor .= '</a><br>';
								$value   = ' ' . $anchor;

								// return $source_link;
							}
							?>
							<td><?php echo \wp_kses_post( $value );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php
						} elseif ( 'datetime' === $key ) {

							$item = array();

							$time_format = 'g:i a';

							$item['date_added'] = (int) $value;

							$event_datetime_utc = \gmdate( 'Y-m-d H:i:s', $item['date_added'] );

							$timezone_local  = \wp_timezone();
							$event_local     = \get_date_from_gmt( $event_datetime_utc, 'Y-m-d' );
							$today_local     = ( new \DateTimeImmutable( 'now', $timezone_local ) )->format( 'Y-m-d' );
							$tomorrow_local  = ( new \DateTimeImmutable( 'tomorrow', $timezone_local ) )->format( 'Y-m-d' );
							$yesterday_local = ( new \DateTimeImmutable( 'yesterday', $timezone_local ) )->format( 'Y-m-d' );

							// If the offset of the date of the event is different from the offset of the site, add a marker.
							if ( \get_date_from_gmt( $event_datetime_utc, 'P' ) !== get_date_from_gmt( 'now', 'P' ) ) {
								$time_format .= ' (P)';
							}

							$event_time_local = \get_date_from_gmt( $event_datetime_utc, $time_format );

							if ( $event_local === $today_local ) {
								$date = sprintf(
								/* translators: %s: Time */
									__( 'Today at %s', '0-day-analytics' ),
									$event_time_local,
								);
							} elseif ( $event_local === $tomorrow_local ) {
								$date = sprintf(
								/* translators: %s: Time */
									__( 'Tomorrow at %s', '0-day-analytics' ),
									$event_time_local,
								);
							} elseif ( $event_local === $yesterday_local ) {
								$date = sprintf(
								/* translators: %s: Time */
									__( 'Yesterday at %s', '0-day-analytics' ),
									$event_time_local,
								);
							} else {
								$date = sprintf(
								/* translators: 1: Date, 2: Time */
									__( '%1$s at %2$s', '0-day-analytics' ),
									\get_date_from_gmt( $event_datetime_utc, 'F jS' ),
									$event_time_local,
								);
							}

							$time = sprintf(
								'<time datetime="%1$s">%2$s</time>',
								\esc_attr( gmdate( 'c', $item['date_added'] ) ),
								\esc_html( $date )
							);

							$until = $item['date_added'] - time();

							if ( $until < 0 ) {
								$ago = sprintf(
								/* translators: %s: Time period, for example "8 minutes" */
									__( '%s ago', '0-day-analytics' ),
									WP_Helper::interval( abs( $until ) )
								);

								$value = sprintf(
									'<span class="status-control-warning"><span class="dashicons dashicons-clock" aria-hidden="true"></span> %s</span><br>%s',
									\esc_html( $ago ),
									$time,
								);
							} elseif ( 0 === $until ) {
								$value = __( 'Now', '0-day-analytics' );
							} else {
								$value = sprintf(
								/* translators: %s: Time period, for example "8 minutes" */
									__( 'In %s', '0-day-analytics' ),
									WP_Helper::interval( $until ),
								);
							}

							// $value = sprintf(
							// '<span class="status-control-warning"><span class="dashicons dashicons-clock" aria-hidden="true"></span> %s</span><br>%s',
							// \esc_html( $in ),
							// $time,
							// );

							?>
							<td><?php echo \wp_kses_post( $value );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php
						} else {
							?>

						<td><?php echo ( self::format_value_for_html( $value ) );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<?php } ?>
					</tr>
					<?php
				}
				?>
				</tbody>
				</table>
				<?php
				$message = \ob_get_clean();

				return rest_ensure_response(
					array(
						'success'    => true,
						'mail_body'  => $message,
						'table_name' => $table_name,
					)
				);

			} else {
				return new \WP_Error(
					'empty_row',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Extracts Hook Capture record data for the REST API.
		 *
		 * @param \WP_REST_Request $request - The REST request object.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 4.5.0
		 */
		public static function extract_hook_capture_row_data( \WP_REST_Request $request ) {
			// Enforce capability check for data disclosure.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'forbidden',
					__( 'Sorry, you are not allowed to perform this action.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}

			$table_name = $request->get_param( 'table_name' );

			if ( '' === trim( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table name is not provided.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( ! self::check_table_exists( $table_name ) ) {
				return new \WP_Error(
					'show_row',
					__( 'Table does not exist.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			if ( Hooks_Capture_Entity::get_table_name() !== $table_name ) {
				return new \WP_Error(
					'show_row',
					__( 'Wrong call.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			$id = $request->get_param( 'id' );

			if ( empty( $id ) ) {
				return new \WP_Error(
					'show_row',
					__( 'ID is not provided or wrong.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			self::init( $table_name );

			global $wpdb;

			$query = $wpdb->prepare(
				'SELECT * FROM `' . self::get_name() . '` WHERE `' . self::get_real_id_name() . '` = %d;', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$id
			);

			$wpdb->suppress_errors( true );

			$results = $wpdb->get_results( $query, \ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( '' !== $wpdb->last_error || null === $results ) {
				$results = array();
			}

			$wpdb->suppress_errors( false );

			if ( ! empty( $results ) ) {
				$hook_data = $results[0];

				// Decode JSON fields
				$parameters = ! empty( $hook_data['parameters'] ) ? json_decode( $hook_data['parameters'], true ) : array();
				$output     = ! empty( $hook_data['output'] ) ? json_decode( $hook_data['output'], true ) : array();
				$backtrace  = ! empty( $hook_data['backtrace'] ) ? json_decode( $hook_data['backtrace'], true ) : array();

				\ob_start();
				?>
				<table class="widefat fixed striped" style="max-width:100%; table-layout: fixed;">
					<col width="20%" />
					<col width="80%" />
					<thead>
						<tr>
							<th><?php \esc_html_e( 'Property', '0-day-analytics' ); ?></th>
							<th><?php \esc_html_e( 'Value', '0-day-analytics' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php \esc_html_e( 'Hook Name', '0-day-analytics' ); ?></strong></td>
							<td><code><?php echo \esc_html( $hook_data['hook_name'] ); ?></code></td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'Hook Type', '0-day-analytics' ); ?></strong></td>
							<td>
								<span class="badge badge-<?php echo 'action' === $hook_data['hook_type'] ? 'success' : 'info'; ?>">
									<?php echo \esc_html( ucfirst( $hook_data['hook_type'] ) ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'Triggered At', '0-day-analytics' ); ?></strong></td>
							<td><?php echo \esc_html( \date_i18n( 'Y-m-d H:i:s', ! empty( $hook_data['date_added'] ) ? (int) $hook_data['date_added'] : 0 ) ); ?></td>
						</tr>
						<?php if ( \is_multisite() && ! empty( $hook_data['blog_id'] ) ) : ?>
						<tr>
							<td><strong><?php \esc_html_e( 'Blog', '0-day-analytics' ); ?></strong></td>
							<td>
								<?php
								$blog = \get_site( $hook_data['blog_id'] );
								echo $blog ? \esc_html( $blog->blogname . ' (' . $blog->domain . ')' ) : \esc_html( $hook_data['blog_id'] );
								?>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td><strong><?php \esc_html_e( 'Trigger Source', '0-day-analytics' ); ?></strong></td>
							<td><?php echo \esc_html( $hook_data['trigger_source'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'User', '0-day-analytics' ); ?></strong></td>
							<td><?php echo ! empty( $hook_data['user_login'] ) ? \esc_html( $hook_data['user_login'] ) : '<em>' . \esc_html__( 'N/A', '0-day-analytics' ) . '</em>'; ?></td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'Execution Time', '0-day-analytics' ); ?></strong></td>
							<td><?php echo \esc_html( number_format( (float) $hook_data['execution_time'], 6 ) ); ?> s</td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'Memory Usage', '0-day-analytics' ); ?></strong></td>
							<td><?php echo \esc_html( size_format( (int) $hook_data['memory_usage'] ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php \esc_html_e( 'CLI', '0-day-analytics' ); ?></strong></td>
							<td><?php echo $hook_data['is_cli'] ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>'; ?></td>
						</tr>
					</tbody>
				</table>

				<?php if ( ! empty( $parameters ) ) : ?>
				<div style="margin-top: 20px;">
					<h3><?php \esc_html_e( 'Parameters', '0-day-analytics' ); ?></h3>
					<pre style="padding: 15px; overflow-x: auto; border: 1px solid #ddd; border-radius: 3px;"><?php echo \esc_html( print_r( $parameters, true ) ); ?></pre>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $output ) ) : ?>
				<div style="margin-top: 20px;">
					<h3><?php \esc_html_e( 'Output', '0-day-analytics' ); ?></h3>
					<pre style="padding: 15px; overflow-x: auto; border: 1px solid #ddd; border-radius: 3px;"><?php echo \esc_html( print_r( $output, true ) ); ?></pre>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $backtrace ) ) : ?>
				<div style="margin-top: 20px;">
					<h3><?php \esc_html_e( 'Backtrace', '0-day-analytics' ); ?></h3>
					<pre style="padding: 15px; overflow-x: auto; border: 1px solid #ddd; border-radius: 3px;">
					<?php
					$backtrace_data = $backtrace;
					if ( \is_array( $backtrace_data ) && ! empty( $backtrace_data ) ) {
						echo '<div style="padding: 10px; border: 1px solid #ddd;">';
						foreach ( $backtrace_data as $frame ) {
							$call_display = '';
							if ( ! empty( $frame['class'] ) ) {
								$call_display = \esc_html( $frame['class'] );
								if ( ! empty( $frame['function'] ) ) {
									$call_display .= '::' . \esc_html( $frame['function'] ) . '()';
								}
							} elseif ( ! empty( $frame['function'] ) ) {
								$call_display = \esc_html( $frame['function'] ) . '()';
							}

							if ( ! empty( $call_display ) ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above with esc_html().
								echo '<div><strong>' . $call_display . '</strong></div>';
							}

							if ( ! empty( $frame['full_path'] ) ) {
								$line_number = ! empty( $frame['line'] ) ? (int) $frame['line'] : 1;
								$query_array = array(
									'_wpnonce'   => \wp_create_nonce( 'source-view' ),
									'action'     => 'log_source_view',
									'error_file' => $frame['full_path'],
									'error_line' => $line_number,
									'TB_iframe'  => 'true',
								);
								$view_url    = \esc_url_raw(
									\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
								);
								$title       = __( 'Viewing: ', '0-day-analytics' ) . $frame['full_path'];
								echo '<div style="margin-bottom: 10px;">';
								echo '<a href="' . \esc_url( $view_url ) . '" title="' . \esc_attr( $title ) . '" class="thickbox view-source">';
								if ( ! empty( $frame['file'] ) ) {
									echo \esc_html( $frame['file'] );
								} else {
									echo \esc_html( \basename( $frame['full_path'] ) );
								}
								if ( ! empty( $frame['line'] ) ) {
									echo ':' . \esc_html( (string) $frame['line'] );
								}
									echo '</a>';
									echo '</div>';
							} elseif ( ! empty( $frame['file'] ) || ! empty( $frame['line'] ) ) {
								echo '<div style="margin-bottom: 10px;">';
								echo ! empty( $frame['file'] ) ? \esc_html( $frame['file'] ) : '';
								echo ! empty( $frame['line'] ) ? ':' . \esc_html( (string) $frame['line'] ) : '';
								echo '</div>';
							}
						}
							echo '</div>';
					} else {
						echo '<pre style="max-height: 300px; overflow: auto; padding: 10px; border: 1px solid #ddd;">';
						echo \esc_html( $backtrace );
						echo '</pre>';
					}
					?>
					</pre>
				</div>
				<?php endif; ?>
				<?php
				$message = \ob_get_clean();

				return rest_ensure_response(
					array(
						'success'    => true,
						'mail_body'  => $message,
						'table_name' => $table_name,
					)
				);

			} else {
				return new \WP_Error(
					'empty_row',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Formats the value for HTML output
		 *
		 * @param mixed $value - The value to be formatted.
		 *
		 * @return string
		 *
		 * @since 3.8.0
		 */
		public static function format_value_for_html( $value ) {
			// Try to decode JSON if it's a string.
			if ( is_string( $value ) ) {
				$decoded = json_decode( $value, true );

				// Handle valid JSON (objects or arrays).
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$value = $decoded;
				} elseif ( preg_match( '/^[aOs]:[0-9]+:/', $value ) ) { // Try unserialize if not valid JSON but looks like serialized PHP.
					$unserialized = @unserialize( $value, array( 'allowed_classes' => false ) );
					if ( false !== $unserialized || 'b:0;' === $value ) {
						$value = $unserialized;
					}
				}
			}

			// Format by type.
			if ( is_array( $value ) ) {
				// Pretty print arrays or objects.
				$formatted = '<pre>' . \esc_html( print_r( $value, true ) ) . '</pre>';
			} elseif ( is_object( $value ) ) {
				$formatted = '<pre>' . \esc_html( print_r( $value, true ) ) . '</pre>';
			} elseif ( is_bool( $value ) ) {
				$formatted = $value ? 'true' : 'false';
			} elseif ( is_null( $value ) ) {
				$formatted = '<em>null</em>';
			} elseif ( is_numeric( $value ) ) {
				$formatted = \esc_html( (string) $value );
			} else {
				// Fallback to escaped plain string.
				$formatted = \esc_html( (string) $value );
			}

			return $formatted;
		}

		/**
		 * Smart insert/update that respects the destination table's column types.
		 *
		 * @param string $table_name Full table name (may include $wpdb->prefix).
		 * @param array  $data       Associative array column => value (raw values).
		 * @param array  $where      Optional associative array column=>value used for update condition. If provided, function performs update; otherwise insert.
		 *
		 * @return int|bool|WP_Error Insert ID on insert, true on successful update, WP_Error on failure.
		 *
		 * @since 3.9.4
		 */
		public static function insert_row_record( $table_name, array $data, ?array $where = null ) {

			if ( ! self::check_table_exists( $table_name ) ) {
				return new \WP_Error( 'table_not_found', 'Table not found.' );
			}

			self::init( $table_name );

			// Fetch column metadata.
			$columns = self::get_columns_info();

			// Build a map: column_name => column_meta.
			$colmap = array();
			foreach ( $columns as $col ) {
				$colmap[ $col['Field'] ] = $col; // ['Type'], ['Null'], ['Key'], ['Default'], ['Extra']
			}

			// sanitize incoming data: only columns that exist and have safe names.
			$prepared_data = array();
			$formats       = array();
			foreach ( $data as $col => $val ) {
				if ( ! is_string( $col ) || ! isset( $colmap[ $col ] ) ) {
					// skip columns that do not exist in table.
					continue;
				}

				$ctype        = $colmap[ $col ]['Type']; // e.g. "int(11) unsigned", "varchar(255)", "enum('a','b')", "json".
				$null_allowed = ( 'YES' === $colmap[ $col ]['Null'] );

				// Normalize value to column type.
				$normalized = null;
				$use_format = '%s'; // default for wpdb insert format.

				// Helper to extract base type and extra info.
				$lower_type = strtolower( $ctype );

				// INT types.
				if ( preg_match( '/^(tinyint|smallint|mediumint|int|bigint)\b/', $lower_type, $m ) ) {
					// treat boolean-like tinyint(1) as integer 0/1 if scalar boolean or numeric.
					if ( is_bool( $val ) ) {
						$normalized = $val ? 1 : 0;
					} elseif ( is_numeric( $val ) ) {
						$normalized = intval( $val );
					} elseif ( is_null( $val ) && $null_allowed ) {
						$normalized = null;
					} else {
						// attempt cast from string.
						$normalized = intval( $val );
					}
					$use_format = '%d';
				} elseif ( preg_match( '/^(float|double|decimal)\b/', $lower_type, $m ) ) { // FLOAT/DOUBLE/DECIMAL.
					if ( is_numeric( $val ) ) {
						$normalized = floatval( $val );
					} elseif ( is_null( $val ) && $null_allowed ) {
						$normalized = null;
					} else {
						// try numeric cast.
						$normalized = floatval( $val );
					}
					$use_format = '%f';
				} elseif ( preg_match( '/\b(date|datetime|timestamp|time|year)\b/', $lower_type, $m ) ) {// DATE / DATETIME / TIMESTAMP / TIME / YEAR.
					if ( is_null( $val ) && $null_allowed ) {
						$normalized = null;
					} elseif ( $val instanceof \DateTime ) {
						$type = $m[1];
						switch ( $type ) {
							case 'date':
								$normalized = $val->format( 'Y-m-d' );
								break;
							case 'datetime':
							case 'timestamp':
								$normalized = $val->format( 'Y-m-d H:i:s' );
								break;
							case 'time':
								$normalized = $val->format( 'H:i:s' );
								break;
							case 'year':
								$normalized = $val->format( 'Y' );
								break;
						}
					} elseif ( is_numeric( $val ) ) {
						// treat as unix timestamp.
						$type = $m[1];
						$ts   = intval( $val );
						if ( 'date' === $type ) {
							$normalized = gmdate( 'Y-m-d', $ts );
						} elseif ( 'time' === $type ) {
							$normalized = gmdate( 'H:i:s', $ts );
						} elseif ( 'year' === $type ) {
							$normalized = gmdate( 'Y', $ts );
						} else {
							$normalized = gmdate( 'Y-m-d H:i:s', $ts );
						}
					} elseif ( is_string( $val ) ) {
						// try to parse common formats; be conservative: try strtotime.
						$ts = strtotime( $val );
						if ( false !== $ts ) {
							$type = $m[1];
							if ( 'date' === $type ) {
								$normalized = gmdate( 'Y-m-d', $ts );
							} elseif ( 'time' === $type ) {
								$normalized = gmdate( 'H:i:s', $ts );
							} elseif ( 'year' === $type ) {
								$normalized = gmdate( 'Y', $ts );
							} else {
								$normalized = gmdate( 'Y-m-d H:i:s', $ts );
							}
						} else {
							// if can't parse, leave raw string (caller must ensure correctness).
							$normalized = (string) $val;
						}
					} else {
						$normalized = (string) $val;
					}
					$use_format = '%s';
				} elseif ( preg_match( '/\bjson\b/', $lower_type ) ) { // JSON column (MySQL JSON type) -- store JSON string.
					$json = wp_json_encode( $val );
					if ( false !== $json && json_last_error() === JSON_ERROR_NONE ) {
						$normalized = $json;
					} else {
						// fallback to string cast.
						$normalized = is_scalar( $val ) || is_null( $val ) ? $val : wp_json_encode( (string) $val );
					}
					$use_format = '%s';
				} elseif ( preg_match( '/^enum\((.*)\)$/', $lower_type, $m ) || preg_match( '/^set\((.*)\)$/', $lower_type, $m ) ) { // ENUM / SET -> try to validate against allowed values.
					$raw = $m[1];
					// parse allowed values 'a','b','c'.
					$allowed = array();
					if ( preg_match_all( "/'((?:[^'\\\\]|\\\\.)*)'/", $raw, $vals ) ) {
						foreach ( $vals[1] as $v ) {
							$allowed[] = stripslashes( $v );
						}
					}
					// if value is array for SET, join by comma.
					if ( is_array( $val ) ) {
						$candidate = implode( ',', array_map( 'strval', $val ) );
					} else {
						$candidate = (string) $val;
					}
					// validate: every value in candidate must be allowed (for SET split by comma).
					$valid = true;
					if ( strpos( $candidate, ',' ) !== false ) {
						$parts = array_map( 'trim', explode( ',', $candidate ) );
					} else {
						$parts = array( $candidate );
					}
					foreach ( $parts as $p ) {
						if ( '' === $p ) {
							continue;
						}
						if ( ! in_array( $p, $allowed, true ) ) {
							$valid = false;
							break;
						}
					}
					if ( $valid ) {
						$normalized = $candidate;
					} else {
						// invalid enum/set value: reject.
						return new \WP_Error( 'invalid_enum', "Invalid value for {$col}." );
					}
					$use_format = '%s';
				} elseif ( preg_match( '/\b(blob|binary|varbinary)\b/', $lower_type ) ) { // BINARY / BLOB types => store binary safely; if non-scalar convert to base64(serialized).
					if ( is_null( $val ) && $null_allowed ) {
						$normalized = null;
					} elseif ( is_string( $val ) ) {
						$normalized = $val;
					} else {
						// encode complex structure to base64 serialized.
						$normalized = base64_encode( serialize( $val ) );
					}
					$use_format = '%s';
				} else { // TEXT / CHAR / VARCHAR and default fallback
					// If scalar string/number/null use as-is (cast to string for safety).
					if ( is_null( $val ) && $null_allowed ) {
						$normalized = null;
					} elseif ( is_scalar( $val ) ) {
						$normalized = (string) $val;
					} else {
						// try JSON encode for arrays/objects; prefer JSON so other apps can recognize JSON if expected.
						$json = wp_json_encode( $val );
						if ( false !== $json && json_last_error() === JSON_ERROR_NONE ) {
							$normalized = $json;
						} else {
							// fallback to base64(serialized).
							$normalized = base64_encode( serialize( $val ) );
						}
					}
					$use_format = '%s';
				}

				// Enforce maximum length if column has a length (varchar(n)).
				if ( preg_match( '/^(?:var)?char\((\d+)\)/', $lower_type, $m ) || preg_match( '/^varchar\((\d+)\)/', $lower_type, $m ) ) {
					$max = intval( $m[1] );
					if ( is_string( $normalized ) && strlen( $normalized ) > $max ) {
						// truncate to max length (preserve valid utf8 boundary).
						$normalized = mb_strcut( $normalized, 0, $max, 'UTF-8' );
					}
				}

				$prepared_data[ $col ] = $normalized;
				$formats[ $col ]       = $use_format;
			}

			if ( empty( $prepared_data ) ) {
				return new \WP_Error( 'no_valid_columns', 'No valid columns provided.' );
			}

			// Format array for wpdb insert/update: an ordered array of format values corresponding to values.
			$format_ordered = array();
			foreach ( $prepared_data as $k => $v ) {
				$format_ordered[] = isset( $formats[ $k ] ) ? $formats[ $k ] : '%s';
			}

			global $wpdb;

			// If $where is provided -> update, else insert.
			if ( ! empty( $where ) && is_array( $where ) ) {
				// sanitize where keys and values similarly.
				$prepared_where = array();
				$where_formats  = array();
				foreach ( $where as $col => $val ) {
					if ( ! is_string( $col ) || ! isset( $colmap[ $col ] ) ) {
						continue;
					}
					// reuse a simple normalization: cast to scalar or string.
					if ( is_null( $val ) ) {
						$prepared_where[ $col ] = null;
						$where_formats[]        = '%s';
					} elseif ( is_numeric( $val ) && preg_match( '/int|tinyint|smallint|mediumint|bigint/', strtolower( $colmap[ $col ]['Type'] ) ) ) {
						$prepared_where[ $col ] = intval( $val );
						$where_formats[]        = '%d';
					} else {
						$prepared_where[ $col ] = (string) $val;
						$where_formats[]        = '%s';
					}
				}

				if ( empty( $prepared_where ) ) {
					return new \WP_Error( 'invalid_where', 'Invalid WHERE clause.' );
				}

				$updated = $wpdb->update(
					$table_name,
					$prepared_data,
					$prepared_where,
					array_values( $format_ordered ),
					$where_formats
				);

				if ( false === $updated ) {
					return new \WP_Error( 'update_failed', 'Database update failed.' );
				}

				// $updated may be 0 if no rows changed; return true to indicate success
				return true;
			} else {
				$inserted = $wpdb->replace(
					$table_name,
					$prepared_data,
					array_values( $format_ordered )
				);

				if ( false === $inserted ) {
					return new \WP_Error( 'insert_failed', 'Database insert failed.' );
				}
				return (int) $wpdb->insert_id;
			}
		}

		/**
		 * Get detailed table information including structure, indexes, foreign keys, and health.
		 *
		 * @param string $table_name The table name to get information for.
		 * @param \wpdb  $connection Optional database connection.
		 *
		 * @return array|\WP_Error Array of table information or WP_Error on failure.
		 *
		 * @since 4.7.0
		 */
		public static function get_table_info( string $table_name, $connection = null ) {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$_wpdb = $connection;
				} else {
					global $wpdb;
					$_wpdb = $wpdb;
				}
			} else {
				global $wpdb;
				$_wpdb = $wpdb;
			}

			// Validate table name.
			if ( ! self::validate_table_name( $table_name ) ) {
				return new \WP_Error( 'invalid_table', 'Invalid table name.' );
			}

			// Check if table exists.
			if ( ! self::check_table_exists( $table_name, $_wpdb ) ) {
				return new \WP_Error( 'table_not_found', 'Table does not exist.' );
			}

			$table_info = array();

			try {
				// Get table structure (columns) - using DESC command like other methods.
				$table_info['structure'] = $_wpdb->get_results(
					$_wpdb->prepare( 'DESC %i', $table_name ),
					ARRAY_A
				);

				// Get indexes - using SHOW INDEX command.
				$table_info['indexes'] = $_wpdb->get_results(
					$_wpdb->prepare( 'SHOW INDEX FROM %i', $table_name ),
					ARRAY_A
				);

				// Get foreign keys from information_schema - using prepared query.
				$table_info['foreign_keys'] = $_wpdb->get_results(
					$_wpdb->prepare(
						'SELECT 
							kcu.constraint_name,
							kcu.column_name,
							kcu.referenced_table_name,
							kcu.referenced_column_name
						FROM information_schema.key_column_usage kcu
						JOIN information_schema.table_constraints tc 
							ON kcu.constraint_name = tc.constraint_name
							AND kcu.table_schema = tc.table_schema
						WHERE kcu.table_schema = %s 
							AND kcu.table_name = %s 
							AND tc.constraint_type = %s',
						$_wpdb->dbname,
						$table_name,
						'FOREIGN KEY'
					),
					ARRAY_A
				);

				// Get table health information from information_schema - using prepared query.
				$health_info = $_wpdb->get_row(
					$_wpdb->prepare(
						'SELECT 
							table_rows as row_count,
							data_length as data_size,
							index_length as index_size,
							data_free as fragmentation
						FROM information_schema.tables 
						WHERE table_schema = %s AND table_name = %s',
						$_wpdb->dbname,
						$table_name
					),
					ARRAY_A
				);

				$table_info['health'] = array(
					'row_count' => $health_info ? (int) $health_info['row_count'] : 0,
					'data_size' => $health_info ? (int) $health_info['data_size'] : 0,
					'index_size' => $health_info ? (int) $health_info['index_size'] : 0,
					'needs_optimization' => $health_info && (int) $health_info['fragmentation'] > 0,
				);

			} catch ( \Exception $e ) {
				return new \WP_Error( 'database_error', 'Database error: ' . $e->getMessage() );
			}

			return $table_info;
		}
	}
}
