<?php
/**
 * Entity: Hooks Capture.
 *
 * @package advan
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Entities;

use ADVAN\Entities_Global\Common_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Entities\Hooks_Capture_Entity' ) ) {
	/**
	 * Responsible for the hooks capture metadata.
	 */
	class Hooks_Capture_Entity extends Abstract_Entity {
		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $table = ADVAN_PREFIX . 'hooks_capture';

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields = array(
			'id'                  => 'int',
			'blog_id'             => 'int',
			'hook_name'           => 'string',
			'hook_type'           => 'string',
			'trigger_source'      => 'string',
			'request_id'          => 'string',
			'user_id'             => 'int',
			'user_login'          => 'string',
			'parameters'          => 'string',
			'output'              => 'string',
			'backtrace'           => 'string',
			'execution_time'      => 'float',
			'memory_usage'        => 'int',
			'is_cli'              => 'int',
			'hooks_management_id' => 'int',
			'count'               => 'int',
			'date_added'          => 'float',
		);

		/**
		 * Holds all the default values for the columns.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields_values = array(
			'id'                  => 0,
			'blog_id'             => 0,
			'hook_name'           => '',
			'hook_type'           => 'action',
			'trigger_source'      => '',
			'request_id'          => '',
			'user_id'             => 0,
			'user_login'          => '',
			'parameters'          => '',
			'output'              => '',
			'backtrace'           => '',
			'execution_time'      => 0.0,
			'memory_usage'        => 0,
			'is_cli'              => 0,
			'hooks_management_id' => 0,
			'count'               => 1,
			'date_added'          => 0.0,
		);

		/**
		 * Creates table functionality.
		 *
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @since 4.5.0
		 */
		public static function create_table( $connection = null ): bool {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$collate = $connection->get_charset_collate();
				}
			} else {
				$collate = self::get_connection()->get_charset_collate();
			}
			$table_name = self::get_table_name( $connection );

			// Defensive validation of table name to avoid unexpected identifier injection.
			if ( ! is_string( $table_name ) || ! preg_match( '/^[a-zA-Z0-9_]+$/', $table_name ) ) {
				return false;
			}

			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					id BIGINT unsigned NOT NULL AUTO_INCREMENT,
					blog_id BIGINT unsigned NOT NULL DEFAULT 0,
					hook_name VARCHAR(191) NOT NULL DEFAULT "",
					hook_type VARCHAR(10) NOT NULL DEFAULT "action",
					trigger_source VARCHAR(50) NOT NULL DEFAULT "",
					request_id VARCHAR(50) NOT NULL DEFAULT "",
					user_id BIGINT unsigned NOT NULL DEFAULT 0,
					user_login VARCHAR(60) NOT NULL DEFAULT "",
					parameters MEDIUMTEXT,
					output MEDIUMTEXT,
					backtrace TEXT,
					execution_time DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
					memory_usage BIGINT unsigned NOT NULL DEFAULT 0,
					is_cli TINYINT(1) NOT NULL DEFAULT 0,
					hooks_management_id BIGINT unsigned NOT NULL DEFAULT 0,
					count INT NOT NULL DEFAULT 1,
					date_added DOUBLE NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				KEY `blog_id` (`blog_id`),
				KEY `hook_name` (`hook_name`),
				KEY `hook_type` (`hook_type`),
				KEY `trigger_source` (`trigger_source`),
				KEY `request_id` (`request_id`),
				KEY `user_id` (`user_id`),
				KEY `date_added` (`date_added`),
				KEY `is_cli` (`is_cli`),
				KEY `hooks_management_id` (`hooks_management_id`)
				)
			  ' . $collate . ';';

			return self::maybe_create_table( $table_name, $wp_entity_sql, $connection );
		}

		/**
		 * Returns the table CMS admin fields
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function get_column_names_admin(): array {
			$columns = array(
				'date_added'     => __( 'Date', '0-day-analytics' ),
				'hook_name'      => __( 'Hook Name', '0-day-analytics' ),
				'hook_type'      => __( 'Type', '0-day-analytics' ),
				'trigger_source' => __( 'Source', '0-day-analytics' ),
				'user_login'     => __( 'User', '0-day-analytics' ),
				'execution_time' => __( 'Time (s)', '0-day-analytics' ),
				'memory_usage'   => __( 'Memory', '0-day-analytics' ),
				'is_cli'         => __( 'CLI', '0-day-analytics' ),
				'count'          => __( 'Count', '0-day-analytics' ),
				'parameters'     => __( 'Parameters', '0-day-analytics' ),
			);

			// Add blog_id column for multisite.
			if ( \is_multisite() ) {
				$columns = array_merge(
					array( 'blog_id' => __( 'Site ID', '0-day-analytics' ) ),
					$columns
				);
			}

			return $columns;
		}

		/**
		 * Alters the table to add request_id column for version 4.6.0.
		 *
		 * @return void
		 *
		 * @since 4.6.0
		 */
		public static function alter_table_460() {
			$table_name = self::get_table_name();

			if ( ! Common_Table::check_table_exists( $table_name ) ) {
				return;
			}

			$connection = self::get_connection();

			// Check if request_id column already exists.
			$columns = $connection->get_results( "SHOW COLUMNS FROM `{$table_name}` LIKE 'request_id'" );

			if ( empty( $columns ) ) {
				// Add the request_id column.
				$alter_sql = "ALTER TABLE `{$table_name}` ADD COLUMN `request_id` VARCHAR(50) NOT NULL DEFAULT '' AFTER `trigger_source`";
				$connection->query( $alter_sql );

				// Add index for the new column.
				$index_sql = "ALTER TABLE `{$table_name}` ADD KEY `request_id` (`request_id`)";
				$connection->query( $index_sql );
			}

			// Check if count column already exists.
			$columns_count = $connection->get_results( "SHOW COLUMNS FROM `{$table_name}` LIKE 'count'" );

			if ( empty( $columns_count ) ) {
				// Add the count column.
				$alter_sql_count = "ALTER TABLE `{$table_name}` ADD COLUMN `count` INT NOT NULL DEFAULT 1 AFTER `hooks_management_id`";
				$connection->query( $alter_sql_count );
			}
		}
	}
}
