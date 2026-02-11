<?php
/**
 * Entity: Snippet definitions and helpers.
 *
 * @package advan
 */

declare(strict_types=1);

namespace ADVAN\Entities;

use ADVAN\Helpers\WP_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Snippet_Entity' ) ) {
	/**
	 * Responsible for storing reusable code snippets.
	 */
	class Snippet_Entity extends Abstract_Entity {

		public const STATUS_TRASHED  = -1;
		public const STATUS_DISABLED = 0;
		public const STATUS_ENABLED  = 1;

		public const SCOPE_EVERYWHERE = 'global';
		public const SCOPE_ADMIN      = 'admin';
		public const SCOPE_FRONTEND   = 'frontend';
		public const SCOPE_MANUAL     = 'manual';

		private const DEFAULT_HOOK     = 'init';
		private const DEFAULT_PRIORITY = 10;

		/**
		 * Execution scope options for UI elements.
		 *
		 * @return array<string,string>
		 */
		public static function get_execution_scopes(): array {
			return array(
				self::SCOPE_EVERYWHERE => \__( 'Everywhere', '0-day-analytics' ),
				self::SCOPE_ADMIN      => \__( 'Admin area', '0-day-analytics' ),
				self::SCOPE_FRONTEND   => \__( 'Front-end', '0-day-analytics' ),
				self::SCOPE_MANUAL     => \__( 'Manual execution', '0-day-analytics' ),
			);
		}

		/**
		 * Table name suffix (prefixed with wp base prefix during runtime).
		 *
		 * @var string
		 */
		protected static $table = ADVAN_PREFIX . 'snippets';

		/**
		 * Definition of table columns and types.
		 *
		 * @var array<string,string>
		 *
		 * @since 4.3.0
		 */
		protected static $fields = array(
			'id'               => 'int',
			'blog_id'          => 'int',
			'name'             => 'string',
			'slug'             => 'string',
			'type'             => 'string',
			'status'           => 'int',
			'code'             => 'string',
			'tags'             => 'string',
			'execution_scope'  => 'string',
			'execution_hook'   => 'string',
			'hook_priority'    => 'int',
			'shortcode_tag'    => 'string',
			'run_conditions'   => 'string',
			'last_run_at'      => 'string',
			'last_run_status'  => 'string',
			'last_run_message' => 'string',
			'created_at'       => 'string',
			'updated_at'       => 'string',
		);

		/**
		 * Default field values.
		 *
		 * @var array<string,mixed>
		 *
		 * @since 4.3.0
		 */
		protected static $fields_values = array(
			'id'               => 0,
			'blog_id'          => 0,
			'name'             => '',
			'slug'             => '',
			'type'             => 'php',
			'status'           => self::STATUS_DISABLED,
			'code'             => '',
			'tags'             => '',
			'execution_scope'  => self::SCOPE_EVERYWHERE,
			'execution_hook'   => self::DEFAULT_HOOK,
			'hook_priority'    => self::DEFAULT_PRIORITY,
			'shortcode_tag'    => '',
			'run_conditions'   => '',
			'last_run_at'      => null,
			'last_run_status'  => 'never',
			'last_run_message' => '',
			'created_at'       => '',
			'updated_at'       => '',
		);

		/**
		 * Allowed snippet execution types mapped to translated labels.
		 *
		 * @var array<string,string>
		 *
		 * @since 4.3.0
		 */
		private const SUPPORTED_TYPES = array(
			'php'    => 'PHP',
			'wp_cli' => 'WP-CLI',
		);

		/**
		 * Create snippets table if missing.
		 *
		 * @param null|\wpdb $connection Optional DB connection.
		 *
		 * @return bool
		 *
		 * @since 4.3.0
		 */
		public static function create_table( $connection = null ): bool {
			$wpdb    = ( $connection instanceof \wpdb ) ? $connection : self::get_connection();
			$collate = \esc_sql( $wpdb->get_charset_collate() );
			$table   = self::get_table_name( $wpdb );

			$sql = 'CREATE TABLE `' . $table . '` (
					id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
					blog_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					name VARCHAR(190) NOT NULL,
					slug VARCHAR(190) NOT NULL,
					type VARCHAR(40) NOT NULL DEFAULT "php",
					status TINYINT(1) NOT NULL DEFAULT 0,
					code LONGTEXT NOT NULL,
					tags VARCHAR(255) DEFAULT NULL,
					execution_scope VARCHAR(20) NOT NULL DEFAULT "global",
					execution_hook VARCHAR(190) NOT NULL DEFAULT "init",
					hook_priority INT NOT NULL DEFAULT 10,
					shortcode_tag VARCHAR(100) DEFAULT NULL,
					run_conditions LONGTEXT NULL,
					last_run_at DATETIME NULL,
					last_run_status VARCHAR(40) NOT NULL DEFAULT "never",
					last_run_message TEXT NULL,
					created_at DATETIME NOT NULL,
					updated_at DATETIME NOT NULL,
				PRIMARY KEY (id),
				KEY `idx_status` (status),
				KEY `idx_blog` (blog_id),
				UNIQUE KEY `uniq_blog_slug` (blog_id, slug),
				UNIQUE KEY `uniq_blog_shortcode` (blog_id, shortcode_tag)
			) ' . $collate . ';';

			return self::maybe_create_table( $table, $sql, $wpdb );
		}

		/**
		 * Return admin column labels used by list table.
		 *
		 * @return array<string,string>
		 *
		 * @since 4.3.0
		 */
		public static function get_column_names_admin(): array {
			$columns = array(
				'name'            => \__( 'Name', '0-day-analytics' ),
				'type'            => \__( 'Type', '0-day-analytics' ),
				'status'          => \__( 'Status', '0-day-analytics' ),
				'execution_scope' => \__( 'Scope', '0-day-analytics' ),
				'execution_hook'  => \__( 'Hook', '0-day-analytics' ),
				'hook_priority'   => \__( 'Priority', '0-day-analytics' ),
				'shortcode_tag'   => \__( 'Shortcode', '0-day-analytics' ),
				'tags'            => \__( 'Tags', '0-day-analytics' ),
				'last_run_at'     => \__( 'Last Run', '0-day-analytics' ),
				'last_run_status' => \__( 'Last Result', '0-day-analytics' ),
				'updated_at'      => \__( 'Updated', '0-day-analytics' ),
			);

			if ( WP_Helper::is_multisite() ) {
				$columns = array( 'blog_id' => \__( 'Site ID', '0-day-analytics' ) ) + $columns;
			}

			return $columns;
		}

		/**
		 * Helper returning supported snippet types.
		 *
		 * @return array<string,string>
		 *
		 * @since 4.3.0
		 */
		public static function get_supported_types(): array {
			$types = array();
			foreach ( self::SUPPORTED_TYPES as $key => $label ) {
				$types[ $key ] = $key;
			}

			return $types;
		}

		/**
		 * Load single snippet by ID.
		 *
		 * @param int $id - Snippet ID.
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		public static function get_snippet( int $id ): array {
			$blog_id = \get_current_blog_id();
			$snippet = self::load( 'id=%d AND blog_id=%d', array( $id, $blog_id ) );
			return ( is_array( $snippet ) ) ? $snippet : array();
		}

		/**
		 * Locate snippet by slug.
		 *
		 * @param string   $slug    - Snippet slug.
		 * @param int|null $blog_id - Optional blog ID, current blog used if null.
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		public static function get_by_slug( string $slug, ?int $blog_id = null ): array {
			$blog_id = null === $blog_id ? \get_current_blog_id() : (int) $blog_id;
			$row     = self::load( 'slug=%s AND blog_id=%d', array( $slug, $blog_id ) );
			return ( is_array( $row ) ) ? $row : array();
		}

		/**
		 * Prepare comma separated tags value.
		 *
		 * @param string $raw - Raw tags input.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		public static function sanitize_tags( string $raw ): string {
			$raw  = \wp_unslash( $raw );
			$list = array_filter( array_map( 'sanitize_text_field', explode( ',', $raw ) ) );
			$list = array_slice( array_unique( $list ), 0, 12 );
			return implode( ',', $list );
		}

		/**
		 * Build slug from snippet name (per blog uniqueness handled at DB level).
		 *
		 * @param string $name - Snippet name.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		public static function slugify( string $name ): string {
			$slug = \sanitize_title( $name );
			if ( '' === $slug ) {
				$slug = 'snippet-' . \wp_generate_password( 6, false, false );
			}
			return substr( $slug, 0, 180 );
		}

		/**
		 * Generate slug that is unique for the current blog.
		 *
		 * @param string   $name       - Snippet name.
		 * @param int|null $exclude_id - Optional snippet ID to exclude from uniqueness check (for updates).
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		public static function build_unique_slug( string $name, ?int $exclude_id = null ): string {
			$base    = self::slugify( $name );
			$slug    = $base;
			$blog_id = \get_current_blog_id();
			$number  = 1;

			while ( '' !== $slug ) {
				$existing = self::get_by_slug( $slug, $blog_id );
				if ( empty( $existing ) || ( null !== $exclude_id && isset( $existing['id'] ) && (int) $existing['id'] === (int) $exclude_id ) ) {
					break;
				}
				$suffix = '-' . $number;
				$slug   = substr( $base, 0, max( 1, 180 - strlen( $suffix ) ) ) . $suffix;
				++$number;
				if ( $number > 50 ) {
					$slug = substr( $base, 0, 160 ) . '-' . \wp_generate_password( 4, false, false );
					break;
				}
			}

			return $slug;
		}

		/**
		 * Sanitize shortcode tag.
		 *
		 * @param string $raw Raw shortcode input.
		 *
		 * @return string
		 */
		public static function sanitize_shortcode_tag( string $raw ): string {
			$raw = strtolower( trim( is_string( $raw ) ? $raw : '' ) );
			$raw = (string) preg_replace( '/[^a-z0-9_\-]/', '', $raw );
			$raw = trim( (string) $raw, '-' );
			if ( '' === $raw ) {
				return '';
			}

			// Shortcodes cannot be purely numeric.
			if ( ctype_digit( $raw ) ) {
				$raw = 'snippet_' . $raw;
			}

			return substr( $raw, 0, 60 );
		}

		/**
		 * Ensure shortcode tag uniqueness per blog.
		 *
		 * @param string   $tag        Sanitized shortcode.
		 * @param int|null $exclude_id Snippet ID to exclude from checks.
		 *
		 * @return string
		 */
		public static function build_unique_shortcode_tag( string $tag, ?int $exclude_id = null ): string {
			$base    = self::sanitize_shortcode_tag( $tag );
			$slug    = $base;
			$blog_id = \get_current_blog_id();
			$number  = 1;

			while ( '' !== $slug ) {
				$existing = self::get_by_shortcode( $slug, $blog_id );
				if ( empty( $existing ) || ( null !== $exclude_id && isset( $existing['id'] ) && (int) $existing['id'] === (int) $exclude_id ) ) {
					break;
				}
				$suffix = '-' . $number;
				$slug   = substr( $base, 0, max( 1, 60 - strlen( $suffix ) ) ) . $suffix;
				++$number;
				if ( $number > 50 ) {
					$slug = substr( $base, 0, 40 ) . '-' . \wp_generate_password( 3, false, false );
					break;
				}
			}

			return $slug;
		}

		/**
		 * Get snippet by shortcode tag.
		 *
		 * @param string   $tag     Shortcode.
		 * @param int|null $blog_id Optional blog ID.
		 *
		 * @return array
		 */
		public static function get_by_shortcode( string $tag, ?int $blog_id = null ): array {
			$tag = self::sanitize_shortcode_tag( $tag );
			if ( '' === $tag ) {
				return array();
			}
			$blog_id = null === $blog_id ? \get_current_blog_id() : (int) $blog_id;
			$row     = self::load( 'shortcode_tag=%s AND blog_id=%d', array( $tag, $blog_id ) );
			return ( is_array( $row ) ) ? $row : array();
		}

		/**
		 * Sanitize WordPress action hook names for snippet execution.
		 *
		 * @param string $raw Raw hook input.
		 *
		 * @return string
		 */
		public static function sanitize_hook_name( string $raw ): string {
			$raw = strtolower( trim( is_string( $raw ) ? $raw : '' ) );
			$raw = (string) preg_replace( '/[^a-z0-9_\.:-]/', '', $raw );
			if ( '' === $raw ) {
				return self::DEFAULT_HOOK;
			}

			return substr( $raw, 0, 180 );
		}

		/**
		 * Update metadata after snippet execution.
		 *
		 * @param int         $id - Snippet ID.
		 * @param string      $status - Execution status.
		 * @param string      $message - Execution message.
		 * @param string|null $timestamp - Optional execution timestamp, current time used if null.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function store_execution_result( int $id, string $status, string $message = '', ?string $timestamp = null ): void {
			$wpdb    = self::get_connection();
			$blog_id = \get_current_blog_id();
			$wpdb->update(
				self::get_table_name( $wpdb ),
				array(
					'last_run_status'  => substr( \sanitize_key( $status ), 0, 40 ),
					'last_run_message' => \wp_trim_words( \wp_strip_all_tags( $message ), 80, '…' ),
					'last_run_at'      => $timestamp ? $timestamp : gmdate( 'Y-m-d H:i:s' ),
					'updated_at'       => gmdate( 'Y-m-d H:i:s' ),
				),
				array(
					'id'      => $id,
					'blog_id' => $blog_id,
				),
				array( '%s', '%s', '%s', '%s' ),
				array( '%d', '%d' )
			);
		}

		/**
		 * Mark snippet as trashed.
		 *
		 * @param int $id - Snippet ID.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function trash_by_id( int $id ): void {
			self::set_status( $id, self::STATUS_TRASHED );
		}

		/**
		 * Restore snippet from trash back to disabled state.
		 *
		 * @param int $id - Snippet ID.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function restore_by_id( int $id ): void {
			self::set_status( $id, self::STATUS_DISABLED );
		}

		/**
		 * Enable snippet by ID.
		 *
		 * @param int $id Snippet ID.
		 */
		public static function enable_by_id( int $id ): void {
			self::set_status( $id, self::STATUS_ENABLED );
		}

		/**
		 * Disable snippet by ID.
		 *
		 * @param int $id Snippet ID.
		 */
		public static function disable_by_id( int $id ): void {
			self::set_status( $id, self::STATUS_DISABLED );
		}

		/**
		 * Whether the provided snippet array is trashed.
		 *
		 * @param array $snippet - Snippet record array.
		 *
		 * @return bool
		 *
		 * @since 4.3.0
		 */
		public static function is_trashed( array $snippet ): bool {
			return isset( $snippet['status'] ) && self::STATUS_TRASHED === (int) $snippet['status'];
		}

		/**
		 * Duplicate a snippet record (stored disabled by default).
		 *
		 * @param int $id - Snippet ID to duplicate.
		 *
		 * @return int|null - New snippet ID or null on failure.
		 *
		 * @since 4.3.0
		 */
		public static function duplicate( int $id ): ?int {
			$source = self::get_snippet( $id );
			if ( empty( $source ) ) {
				return null;
			}

			if ( (int) ( $source['blog_id'] ?? 0 ) !== (int) \get_current_blog_id() ) {
				return null;
			}

			$now      = gmdate( 'Y-m-d H:i:s' );
			$new_name = self::generate_clone_name( (string) $source['name'] );
			$new_slug = self::build_unique_slug( $new_name, null );

			$record = array(
				'blog_id'          => (int) $source['blog_id'],
				'name'             => $new_name,
				'slug'             => $new_slug,
				'type'             => $source['type'],
				'code'             => $source['code'],
				'tags'             => $source['tags'],
				'execution_scope'  => $source['execution_scope'] ?? self::SCOPE_EVERYWHERE,
				'execution_hook'   => $source['execution_hook'] ?? self::DEFAULT_HOOK,
				'hook_priority'    => (int) ( $source['hook_priority'] ?? self::DEFAULT_PRIORITY ),
				'run_conditions'   => $source['run_conditions'] ?? '',
				'shortcode_tag'    => '',
				'created_at'       => $now,
				'updated_at'       => $now,
				'last_run_status'  => 'never',
				'last_run_message' => '',
				'last_run_at'      => null,
				'status'           => self::STATUS_DISABLED,
			);

			$insert_id = self::insert( $record );

			return $insert_id > 0 ? (int) $insert_id : null;
		}

		/**
		 * Return totals for list view filters.
		 *
		 * @return array<string,int>
		 *
		 * @since 4.3.0
		 */
		public static function get_status_counters(): array {
			$wpdb    = self::get_connection();
			$table   = self::get_table_name( $wpdb );
			$blog_id = \get_current_blog_id();

			$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( 'SELECT status, COUNT(*) as total FROM ' . $table . ' WHERE blog_id = %d GROUP BY status', $blog_id ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				ARRAY_A
			);

			$counts = array(
				'enabled'  => 0,
				'disabled' => 0,
				'trashed'  => 0,
			);

			foreach ( $rows as $row ) {
				$status = isset( $row['status'] ) ? (int) $row['status'] : self::STATUS_DISABLED;
				$total  = (int) ( $row['total'] ?? 0 );
				if ( self::STATUS_TRASHED === $status ) {
					$counts['trashed'] += $total;
				} elseif ( self::STATUS_ENABLED === $status ) {
					$counts['enabled'] += $total;
				} else {
					$counts['disabled'] += $total;
				}
			}

			$counts['all']   = $counts['enabled'] + $counts['disabled'];
			$counts['total'] = $counts['all'] + $counts['trashed'];

			return $counts;
		}

		/**
		 * Generate a readable clone name limited to 180 chars.
		 *
		 * @param string $original - Original snippet name.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function generate_clone_name( string $original ): string {
			$base   = '' === trim( $original ) ? \__( 'Cloned Snippet', '0-day-analytics' ) : $original;
			$suffix = ' ' . \__( '(Copy)', '0-day-analytics' );
			return substr( $base, 0, max( 1, 180 - strlen( $suffix ) ) ) . $suffix;
		}

		/**
		 * Update snippet status.
		 *
		 * @param integer $id - Snippet ID.
		 * @param integer $status - New status value.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function set_status( int $id, int $status ): void {
			$wpdb    = self::get_connection();
			$blog_id = \get_current_blog_id();
			$wpdb->update(
				self::get_table_name( $wpdb ),
				array(
					'status'     => $status,
					'updated_at' => gmdate( 'Y-m-d H:i:s' ),
				),
				array(
					'id'      => $id,
					'blog_id' => $blog_id,
				),
				array( '%d', '%s' ),
				array( '%d', '%d' )
			);
		}

		/**
		 * Delete snippet scoped to current blog.
		 *
		 * @param int        $id Snippet ID.
		 * @param mixed|null $connection Optional database connection.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function delete_by_id( int $id, $connection = null ): void {
			$wpdb    = self::get_connection();
			$blog_id = \get_current_blog_id();
			$wpdb->delete(
				self::get_table_name( $wpdb ),
				array(
					'id'      => $id,
					'blog_id' => $blog_id,
				),
				array( '%d', '%d' )
			);
		}

		/**
		 * Get runtime snippets (enabled PHP snippets for current blog).
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		public static function get_runtime_snippets(): array {
			$wpdb    = self::get_connection();
			$table   = self::get_table_name( $wpdb );
			$blog_id = \get_current_blog_id();

			$query = $wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE blog_id = %d AND status = %d AND type = %s',
				$blog_id,
				self::STATUS_ENABLED,
				'php'
			);

			$wpdb->suppress_errors( true );
			$results = $wpdb->get_results( $query, ARRAY_A );
			if ( '' !== $wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $wpdb ) ) {
					if ( self::create_table( $wpdb ) ) {
						$results = array();
					}
				}
			}
			$wpdb->suppress_errors( false );

			return is_array( $results ) ? $results : array();
		}

		/**
		 * Get snippets with filters for AJAX listing.
		 *
		 * @param array $filters Array of filters (search, status, type, etc.).
		 * @param int   $offset  Pagination offset.
		 * @param int   $limit   Pagination limit.
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		public static function get_snippets_with_filters( array $filters = array(), int $offset = 0, int $limit = 50 ): array {
			$wpdb    = self::get_connection();
			$table   = self::get_table_name( $wpdb );
			$blog_id = \get_current_blog_id();

			$where    = array( 'blog_id = %d' );
			$bindings = array( $blog_id );

			// Search filter
			if ( ! empty( $filters['search'] ) ) {
				$search = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
				$where[] = '(name LIKE %s OR tags LIKE %s)';
				$bindings[] = $search;
				$bindings[] = $search;
			}

			// Status filter
			if ( isset( $filters['status'] ) ) {
				$status_map = array(
					'enabled'  => self::STATUS_ENABLED,
					'disabled' => self::STATUS_DISABLED,
					'trash'    => self::STATUS_TRASHED,
				);

				if ( isset( $status_map[ $filters['status'] ] ) ) {
					$where[] = 'status = %d';
					$bindings[] = $status_map[ $filters['status'] ];
				} else {
					$where[] = 'status >= %d';
					$bindings[] = self::STATUS_DISABLED;
				}
			}

			// Type filter
			if ( ! empty( $filters['type'] ) && in_array( $filters['type'], array_keys( self::get_supported_types() ), true ) ) {
				$where[] = 'type = %s';
				$bindings[] = $filters['type'];
			}

			$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
			$orderby   = 'updated_at';
			$order     = 'DESC';

			$query = 'SELECT * FROM ' . $table . ' ' . $where_sql . ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';
			$bindings[] = $limit;
			$bindings[] = $offset;

			$wpdb->suppress_errors( true );
			$results = $wpdb->get_results( $wpdb->prepare( $query, $bindings ), ARRAY_A );
			if ( '' !== $wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $wpdb ) ) {
					if ( self::create_table( $wpdb ) ) {
						$results = array();
					}
				}
			}
			$wpdb->suppress_errors( false );

			return is_array( $results ) ? $results : array();
		}

		/**
		 * Get total count of snippets with filters.
		 *
		 * @param array $filters Array of filters (search, status, type, etc.).
		 *
		 * @return int
		 *
		 * @since 4.3.0
		 */
		public static function get_snippets_count_with_filters( array $filters = array() ): int {
			$wpdb    = self::get_connection();
			$table   = self::get_table_name( $wpdb );
			$blog_id = \get_current_blog_id();

			$where    = array( 'blog_id = %d' );
			$bindings = array( $blog_id );

			// Search filter.
			if ( ! empty( $filters['search'] ) ) {
				$search = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
				$where[] = '(name LIKE %s OR tags LIKE %s)';
				$bindings[] = $search;
				$bindings[] = $search;
			}

			// Status filter.
			if ( isset( $filters['status'] ) ) {
				$status_map = array(
					'enabled'  => self::STATUS_ENABLED,
					'disabled' => self::STATUS_DISABLED,
					'trash'    => self::STATUS_TRASHED,
				);

				if ( isset( $status_map[ $filters['status'] ] ) ) {
					$where[] = 'status = %d';
					$bindings[] = $status_map[ $filters['status'] ];
				} else {
					$where[] = 'status >= %d';
					$bindings[] = self::STATUS_DISABLED;
				}
			}

			// Type filter.
			if ( ! empty( $filters['type'] ) && in_array( $filters['type'], array_keys( self::get_supported_types() ), true ) ) {
				$where[] = 'type = %s';
				$bindings[] = $filters['type'];
			}

			$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
			$query = 'SELECT COUNT(*) FROM ' . $table . ' ' . $where_sql;

			$wpdb->suppress_errors( true );
			$count = (int) $wpdb->get_var( $wpdb->prepare( $query, $bindings ) );
			if ( '' !== $wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $wpdb ) ) {
					if ( self::create_table( $wpdb ) ) {
						$count = 0;
					}
				}
			}
			$wpdb->suppress_errors( false );

			return $count;
		}
	}
}
