<?php
/**
 * Entity: WP_Mail.
 *
 * @package advan
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace ADVAN\Entities;

use ADVAN\Helpers\WP_Helper;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Entities_Global\Common_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Entities\WP_Mail_Entity' ) ) {
	/**
	 * Responsible for the mail metadata.
	 */
	class WP_Mail_Entity extends Abstract_Entity {
		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 3.0.0
		 */
		protected static $table = ADVAN_PREFIX . 'wp_mail_log';

		/**
		 * Cache for rendered site dropdowns keyed by a composite of `$which` and `$selected`.
		 * Avoids returning stale markup for different selections while still preventing repeat queries.
		 *
		 * @var array<string,string> key => rendered HTML
		 *
		 * @since 3.7.1 Updated 3.7.x: changed from single string cache to keyed array for correctness & security clarity.
		 */
		private static $drop_down_sites_rendered = array();

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 3.0.0
		 */
		protected static $fields = array(
			'id'                    => 'int',
			'blog_id'               => 'int',
			'plugin_slug'           => 'string',
			'time'                  => 'string',
			'email_to'              => 'string',
			'email_from'            => 'string',
			'email_cc'              => 'string',
			'email_bcc'             => 'string',
			'email_reply_to'        => 'string',
			'subject'               => 'string',
			'message'               => 'string',
			'message_size'          => 'int',
			'total_size'            => 'int',
			'backtrace_segment'     => 'string',
			'status'                => 'int',
			'is_html'               => 'int',
			'error'                 => 'string',
			'attachments'           => 'string',
			'attachment_count'      => 'int',
			'attachment_total_size' => 'int',
			'additional_headers'    => 'string',
			'delivery_time'         => 'string',
			'email_category'        => 'string',
			'can_resend'            => 'int',
		);

		/**
		 * Holds all the default values for the columns.
		 *
		 * @var array
		 *
		 * @since 3.0.0
		 */
		protected static $fields_values = array(
			'id'                    => 0,
			'blog_id'               => 0,
			'plugin_slug'           => '',
			'time'                  => '',
			'email_to'              => '',
			'email_from'            => '',
			'email_cc'              => '',
			'email_bcc'             => '',
			'email_reply_to'        => '',
			'subject'               => '',
			'message'               => '',
			'message_size'          => 0,
			'total_size'            => 0,
			'backtrace_segment'     => '',
			'status'                => 0,
			'is_html'               => 0,
			'error'                 => '',
			'attachments'           => '',
			'attachment_count'      => 0,
			'attachment_total_size' => 0,
			'additional_headers'    => '',
			'delivery_time'         => '',
			'email_category'        => '',
			'can_resend'            => 1,
		);

		/**
		 * Creates table functionality.
		 *
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @since 3.0.0
		 */
		public static function create_table( $connection = null ): bool {
			// Fetch the collate info from either provided or default connection.
			if ( null !== $connection && $connection instanceof \wpdb ) {
				$collate = $connection->get_charset_collate();
			} else {
				$collate = self::get_connection()->get_charset_collate();
			}

			$table_name = self::get_table_name( $connection );

			// Basic hardening: ensure collate string is safe for direct inclusion.
			$collate = \esc_sql( $collate );

			// IMPORTANT: Removed trailing comma before PRIMARY KEY (was a subtle SQL issue) and kept explicit column types.
			// Keeping creation via maybe_create_table (assumed to wrap dbDelta) to preserve existing upgrade semantics.
			$wp_entity_sql = 'CREATE TABLE `' . $table_name . '` (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				blog_id INT NOT NULL,
				plugin_slug VARCHAR(255) DEFAULT NULL,
				time DOUBLE NOT NULL DEFAULT 0,
				email_to TEXT DEFAULT NULL,
				email_from TEXT DEFAULT NULL,
			email_cc TEXT DEFAULT NULL,
			email_bcc TEXT DEFAULT NULL,
			email_reply_to TEXT DEFAULT NULL,
			subject TEXT DEFAULT NULL,
			message MEDIUMTEXT DEFAULT NULL,
			message_size INT UNSIGNED DEFAULT 0,
			total_size INT UNSIGNED DEFAULT 0,
			backtrace_segment MEDIUMTEXT NOT NULL,
			status BOOL NOT NULL DEFAULT 1,
			is_html BOOL NOT NULL DEFAULT 1,
			error TEXT DEFAULT NULL,
			attachments MEDIUMTEXT DEFAULT NULL,
			attachment_count TINYINT UNSIGNED DEFAULT 0,
			attachment_total_size INT UNSIGNED DEFAULT 0,
			additional_headers TEXT DEFAULT NULL,
			delivery_time DECIMAL(10,6) DEFAULT NULL,
			email_category VARCHAR(50) DEFAULT NULL,
			can_resend BOOL NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			KEY email_category (email_category),
			KEY status (status)
) ' . $collate . ';';

			return self::maybe_create_table( $table_name, $wp_entity_sql, $connection );
		}

		/**
		 * Alters table for 3.0.1
		 *
		 * @return array|bool
		 *
		 * @since 3.0.1
		 */
		public static function alter_table_301() {
			$table  = self::get_table_name();
			$column = 'email_from';
			if ( self::column_exists( $table, $column ) ) {
				return true; // Already upgraded.
			}
			$sql = 'ALTER TABLE `' . $table . '` ADD `email_from` TEXT DEFAULT NULL AFTER `email_to`;';
			return Common_Table::execute_query( $sql );
		}

		/**
		 * Alters the table to add the blog_id for more precise logging in multisite setups.
		 *
		 * @return array|bool
		 *
		 * @since 3.6.3
		 */
		public static function alter_table_363() {
			$table  = self::get_table_name();
			$column = 'blog_id';
			if ( self::column_exists( $table, $column ) ) {
				return true; // Already upgraded.
			}
			$sql = 'ALTER TABLE `' . $table . '` ADD `blog_id` INT NOT NULL AFTER `id`;';
			// Extend logging to capture current blog id/site url stored in the new column.
			return Common_Table::execute_query( $sql );
		}

		/**
		 * Adds plugin_slug column (version 4.1.1 migration) capturing originating plugin.
		 *
		 * @return array|bool
		 *
		 * @since 4.1.1
		 */
		public static function alter_table_411() {
			$table  = self::get_table_name();
			$column = 'plugin_slug';
			if ( self::column_exists( $table, $column ) ) {
				return true;
			}
			$sql = 'ALTER TABLE `' . $table . '` ADD `plugin_slug` VARCHAR(255) DEFAULT NULL AFTER `blog_id`;';
			return Common_Table::execute_query( $sql );
		}

		/**
		 * Adds enhanced mail tracking columns for version 4.7.1.
		 * - email_cc, email_bcc: CC and BCC recipients
		 * - email_reply_to: Reply-To address
		 * - message_size, total_size: Email size tracking
		 * - attachment_count, attachment_total_size: Enhanced attachment metadata
		 * - delivery_time: Time taken to send email
		 * - email_category: Auto-detected email category
		 * - can_resend: Flag indicating if email can be resent
		 *
		 * @return array|bool
		 *
		 * @since 4.7.1
		 */
		public static function alter_table_471() {
			$table = self::get_table_name();

			// Check if already migrated by checking for one of the new columns.
			if ( self::column_exists( $table, 'email_cc' ) ) {
				return true;
			}

			// Add all new columns in one ALTER statement for efficiency.
			$sql = 'ALTER TABLE `' . $table . '`
				ADD `email_cc` TEXT DEFAULT NULL AFTER `email_from`,
				ADD `email_bcc` TEXT DEFAULT NULL AFTER `email_cc`,
				ADD `email_reply_to` TEXT DEFAULT NULL AFTER `email_bcc`,
				ADD `message_size` INT UNSIGNED DEFAULT 0 AFTER `message`,
				ADD `total_size` INT UNSIGNED DEFAULT 0 AFTER `message_size`,
				ADD `attachment_count` TINYINT UNSIGNED DEFAULT 0 AFTER `attachments`,
				ADD `attachment_total_size` INT UNSIGNED DEFAULT 0 AFTER `attachment_count`,
				ADD `delivery_time` DECIMAL(10,6) DEFAULT NULL AFTER `error`,
				ADD `email_category` VARCHAR(50) DEFAULT NULL AFTER `delivery_time`,
				ADD `can_resend` BOOL NOT NULL DEFAULT 1 AFTER `email_category`,
				ADD KEY `email_category` (`email_category`);';

			return Common_Table::execute_query( $sql );
		}

		/**
		 * Helper to check if a column exists in the given table.
		 *
		 * @param string     $table      Table name (already trusted internal value).
		 * @param string     $column     Column name to check.
		 * @param null|\wpdb $connection Optional connection.
		 *
		 * @return bool
		 *
		 * @since 4.1.1
		 */
		protected static function column_exists( string $table, string $column, $connection = null ): bool {
			$wpdb = ( $connection instanceof \wpdb ) ? $connection : self::get_connection();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Internal schema introspection; table name is trusted and cannot be parameterized.
			$results = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM `' . $table . '` LIKE %s', $column ) );
			return ! empty( $results );
		}

		/**
		 * Returns the table CMS admin fields
		 *
		 * @return array
		 *
		 * @since 3.0.0
		 */
		public static function get_column_names_admin(): array {
			$columns = array(
				'time'              => __( 'Date', '0-day-analytics' ),
				'email_to'          => __( 'To', '0-day-analytics' ),
				'email_from'        => __( 'From', '0-day-analytics' ),
				'email_cc'          => __( 'CC', '0-day-analytics' ),
				'email_bcc'         => __( 'BCC', '0-day-analytics' ),
				'email_reply_to'    => __( 'Reply-To', '0-day-analytics' ),
				'subject'           => __( 'Subject', '0-day-analytics' ),
				'message_size'      => __( 'Size', '0-day-analytics' ),
				'email_category'    => __( 'Category', '0-day-analytics' ),
				'is_html'           => __( 'Is HTML', '0-day-analytics' ),
				'attachments'       => __( 'Attachments', '0-day-analytics' ),
				'attachment_count'  => __( 'Att. Count', '0-day-analytics' ),
				'delivery_time'     => __( 'Delivery Time', '0-day-analytics' ),
				'backtrace_segment' => __( 'Source', '0-day-analytics' ),
			);

			if ( WP_Helper::is_multisite() ) {
				$columns['blog_id'] = __( 'From Blog', '0-day-analytics' );
			}

			return $columns;
		}

		/**
		 * Generates drop down with all the subsites that have mail logs.
		 *
		 * @param string $selected - The selected (if any) site ID.
		 * @param string $which - Indicates position of the dropdown (top or bottom).
		 *
		 * @return string
		 *
		 * @since 3.6.3
		 */
		public static function get_all_sites_dropdown( $selected = '', $which = '' ): string {
			// Normalize selected early for comparisons.
			$selected  = ( '' === $selected ) ? '' : (string) $selected;
			$cache_key = $which . '|' . $selected;
			if ( isset( self::$drop_down_sites_rendered[ $cache_key ] ) ) {
				return self::$drop_down_sites_rendered[ $cache_key ];
			}

			// Query strictly internal table (no user input). Still keep explicit GROUP BY & ORDER for determinism.
			$sql     = 'SELECT blog_id FROM ' . self::get_table_name() . ' GROUP BY blog_id ORDER BY blog_id DESC';
			$results = self::get_results( $sql );
			$sites   = array();
			$output  = '';

			if ( $results ) {
				foreach ( $results as $result ) {
					$blog_id = (int) $result['blog_id'];
					$details = \get_blog_details( array( 'blog_id' => $blog_id ) );
					$name    = ( $details && isset( $details->blogname ) ) ? $details->blogname : \sprintf( /* translators: %s: Site ID */ __( 'Site %s', '0-day-analytics' ), $blog_id );
					$sites[] = array(
						'id'   => $blog_id,
						'name' => $name,
					);
				}
			}

			if ( ! empty( $sites ) ) {
				$output  = '<select class="site_id_filter" name="site_id_' . \esc_attr( $which ) . '" id="site_id_' . \esc_attr( $which ) . '">';
				$output .= '<option value="-1">' . __( 'All sites', '0-day-analytics' ) . '</option>';
				foreach ( $sites as $site_info ) {
					$site_id       = (int) $site_info['id'];
					$selected_attr = ( '' !== $selected && (int) $selected === $site_id ) ? ' selected' : '';
					$output       .= '<option value="' . \esc_attr( $site_id ) . '"' . $selected_attr . '>' . \esc_html( $site_info['name'] ) . '</option>';
				}
				$output .= '</select>';
			}

			self::$drop_down_sites_rendered[ $cache_key ] = $output;
			return $output;
		}

		/**
		 * Generates dropdown with all distinct plugin slugs seen in the mail log.
		 *
		 * @param string $selected The currently selected plugin slug (or -1 for all).
		 * @param string $which    Indicates position of the dropdown (top or bottom).
		 *
		 * @return string Rendered HTML <select> element, or empty string if none.
		 *
		 * @since 4.1.1
		 */
		public static function get_all_plugins_dropdown( $selected = '', $which = '' ): string {
			// Restrict visibility to administrators to avoid information disclosure.
			if ( function_exists( 'current_user_can' ) && ! \current_user_can( 'manage_options' ) ) {
				return '';
			}

			$which = in_array( $which, array( 'top', 'bottom' ), true ) ? $which : '';

			// Build cache key per position and selection (reuse sites cache container for simplicity).
			$cache_key = 'plugins|' . $which . '|' . (string) $selected;
			if ( isset( self::$drop_down_sites_rendered[ $cache_key ] ) ) {
				return self::$drop_down_sites_rendered[ $cache_key ];
			}

			$sql     = 'SELECT plugin_slug FROM ' . self::get_table_name() . ' GROUP BY plugin_slug ORDER BY plugin_slug DESC';
			$results = self::get_results( $sql );
			$plugins = array();
			$output  = '';

			if ( $results ) {
				foreach ( $results as $result ) {
					$slug = isset( $result['plugin_slug'] ) ? trim( (string) $result['plugin_slug'] ) : '';
					if ( '' === $slug ) {
						continue;
					}
					$details   = Plugin_Theme_Helper::get_plugin_from_path( $slug );
					$name      = ( isset( $details['Name'] ) && ! empty( $details['Name'] ) ) ? $details['Name'] : $slug;
					$plugins[] = array(
						'id'   => $slug,
						'name' => $name,
					);
				}
			}

			if ( ! empty( $plugins ) ) {
				$output  = '<select class="plugin_filter" name="plugin_' . \esc_attr( $which ) . '" id="plugin_' . \esc_attr( $which ) . '">';
				$output .= '<option value="-1">' . __( 'All plugins', '0-day-analytics' ) . '</option>';
				foreach ( $plugins as $plugin_info ) {
					$selected_attr = ( isset( $selected ) && '' !== trim( (string) $selected ) && (string) $selected === (string) $plugin_info['id'] ) ? ' selected' : '';
					$output       .= '<option value="' . \esc_attr( $plugin_info['id'] ) . '"' . $selected_attr . '>' . \esc_html( $plugin_info['name'] ) . '</option>';
				}
				$output .= '</select>';
			}

			self::$drop_down_sites_rendered[ $cache_key ] = $output;
			return $output;
		}
	}
}
