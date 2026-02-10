<?php
/**
 * Entity: Hook Groups.
 *
 * @package advan
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Entities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Entities\Hook_Groups_Entity' ) ) {
	/**
	 * Responsible for managing hook groups for organization and color coding.
	 */
	class Hook_Groups_Entity extends Abstract_Entity {
		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $table = ADVAN_PREFIX . 'hook_groups';

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields = array(
			'id'          => 'int',
			'name'        => 'string',
			'color'       => 'string',
			'description' => 'string',
			'date_added'  => 'float',
		);

		/**
		 * Holds all the default values for the columns.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields_values = array(
			'id'          => 0,
			'name'        => '',
			'color'       => '#007cba',
			'description' => '',
			'date_added'  => 0.0,
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
					name VARCHAR(100) NOT NULL DEFAULT "",
					color VARCHAR(7) NOT NULL DEFAULT "#007cba",
					description TEXT,
					date_added DOUBLE NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				UNIQUE KEY `name` (`name`),
				KEY `date_added` (`date_added`)
				)
			  ' . $collate . ';';

			return self::maybe_create_table( $table_name, $wp_entity_sql, $connection );
		}

		/**
		 * Get all hook groups as an associative array.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function get_groups_array(): array {
			$table_name = self::get_table_name();
			$results    = self::get_results( "SELECT id, name, color FROM {$table_name} ORDER BY name ASC" );

			$groups = array();
			foreach ( $results as $result ) {
				$groups[ $result['id'] ] = array(
					'name'  => $result['name'],
					'color' => $result['color'],
				);
			}

			return $groups;
		}

		/**
		 * Get a hook group by ID.
		 *
		 * @param int $group_id The group ID.
		 *
		 * @return array|null
		 *
		 * @since 4.5.0
		 */
		public static function get_group( int $group_id ): ?array {
			$result     = self::load( 'id = %d', $group_id );

			return $result ?: null;
		}

		/**
		 * Create a new hook group.
		 *
		 * @param string $name        The group name.
		 * @param string $color       The group color (hex code).
		 * @param string $description The group description.
		 *
		 * @return int The group ID on success, 0 on failure.
		 *
		 * @since 4.5.0
		 */
		public static function create_group( string $name, string $color = '#007cba', string $description = '' ): int {
			$data = array(
				'name'        => sanitize_text_field( $name ),
				'color'       => sanitize_hex_color( $color ) ?: '#007cba',
				'description' => sanitize_textarea_field( $description ),
				'date_added'  => microtime( true ),
			);

			return self::insert( $data );
		}

		/**
		 * Update an existing hook group.
		 *
		 * @param int    $group_id    The group ID.
		 * @param string $name        The group name.
		 * @param string $color       The group color (hex code).
		 * @param string $description The group description.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		public static function update_group( int $group_id, string $name, string $color = '#007cba', string $description = '' ): bool {
			$data = array(
				'id'          => $group_id,
				'name'        => sanitize_text_field( $name ),
				'color'       => sanitize_hex_color( $color ) ?: '#007cba',
				'description' => sanitize_textarea_field( $description ),
			);

			$result = self::insert( $data );
			return $result > 0;
		}

		/**
		 * Delete a hook group.
		 *
		 * @param int $group_id The group ID.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.5.0
		 */
		public static function delete_group( int $group_id ): bool {
			return self::delete_by_id( $group_id ) !== false;
		}
	}
}
