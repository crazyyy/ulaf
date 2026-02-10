<?php
/**
 * Responsible for the plugin wizard ordering
 *
 * @package    advana
 * @subpackage traits
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/wp-2fa/
 */

namespace ADVAN\Lists\Traits;

use ADVAN\Helpers\WP_Helper;
use ADVAN\Helpers\Settings;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Ensure the trait is declared only once. Use trait_exists (not class_exists) with the correct FQCN.
if ( ! \trait_exists( '\ADVAN\Lists\Traits\List_Trait', false ) ) {
	/**
	 * Responsible for the list logs show
	 *
	 * @since 2.1.0
	 */
	trait List_Trait {

		/**
		 * Holds the array with all of the column names and their representation in the table header.
		 *
		 * @var array
		 *
		 * @since 1.7.0
		 */
		protected static $columns = array();

		/**
		 * Current setting (if any) of per_page property - caching value.
		 *
		 * @var int
		 *
		 * @since 1.7.5
		 */
		protected static $per_page = null;

		/**
		 * Returns the search query string sanitized (no SQL escaping).
		 *
		 * Guidance:
		 * - Use this sanitized string inside parameterized queries: $wpdb->prepare( '... LIKE %s', '%' . $term . '%' ).
		 * - Escape on output (esc_html/esc_attr) when rendering in HTML.
		 * - Avoid manual concatenation into raw SQL; prefer prepare() or WP_Query args.
		 *
		 * @return string Sanitized search string safe for further context-specific escaping.
		 *
		 * @since 1.1.0
		 */
		public static function escaped_search_input() {
			return isset( $_REQUEST[ static::SEARCH_INPUT ] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST[ static::SEARCH_INPUT ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get a list of columns. The format is:
		 * 'internal-name' => 'Title'.
		 *
		 * @since 1.7.0
		 *
		 * @return array
		 */
		public function get_columns() {
			return static::$columns;
		}

		/**
		 * Form table per-page screen option value.
		 *
		 * @since 1.1.0
		 *
		 * @param bool   $keep   Whether to save or skip saving the screen option value. Default false.
		 * @param string $option The option name.
		 * @param int    $value  The number of rows to use.
		 *
		 * @return mixed
		 */
		public static function set_screen_option( $keep, $option, $value ) {

			if ( false !== \strpos( $option, static::SCREEN_OPTIONS_SLUG . '_' ) ) {
				return $value;
			}

			return $keep;
		}

		/**
		 * Returns the columns array (with column name).
		 *
		 * @return array
		 *
		 * @since 1.7.0
		 */
		private static function get_column_names() {
			return self::$columns;
		}

		/**
		 * Returns the records to show per page.
		 *
		 * @return int
		 *
		 * @since 1.7.0
		 */
		public static function get_default_per_page(): int {
			return static::$rows_per_page;
		}

		/**
		 * Get the screen option per_page.
		 *
		 * @return int
		 *
		 * @since 1.1.0
		 */
		private static function get_screen_option_per_page() {
			if ( null !== self::$per_page ) {
				return self::$per_page;
			} else {
				$wp_screen = WP_Helper::get_wp_screen();

				if ( is_a( $wp_screen, '\WP_Screen' ) && self::PAGE_SLUG === $wp_screen->base ) {
					$option = $wp_screen->get_option( 'per_page', 'option' );
					if ( ! $option ) {
						$option = str_replace( '-', '_', $wp_screen->id . '_per_page' );
					}
				} else {
					$option = static::SCREEN_OPTIONS_SLUG . '_per_page';
				}

				self::$per_page = (int) \get_user_option( $option );
				if ( empty( self::$per_page ) || self::$per_page < 1 ) {
					if ( is_a( $wp_screen, '\WP_Screen' ) ) {
						self::$per_page = $wp_screen->get_option( 'per_page', 'default' );
						if ( ! self::$per_page ) {
							self::$per_page = self::get_default_per_page();
						}
					} else {
						self::$per_page = self::get_default_per_page();
					}
				}

				return self::$per_page;
			}
		}

		/**
		 * Adds a screen options to the current screen table.
		 *
		 * @param \WP_Hook $hook - The hook object to attach to.
		 *
		 * @return void
		 *
		 * @since 1.7.0
		 */
		public static function add_screen_options( $hook ) {
			$screen_options = array( 'per_page' => static::get_screen_per_page_title() );

			$result = array();

			\array_walk(
				$screen_options,
				function ( &$a, $b ) use ( &$result ) {
					$result[ static::SCREEN_OPTIONS_SLUG . '_' . $b ] = $a;
				}
			);
			$screen_options = $result;

			foreach ( $screen_options as $key => $value ) {
				\add_action(
					"load-$hook",
					function () use ( $key, $value ) {
						$option = 'per_page';
						$args   = array(
							'label'   => $value,
							'default' => self::get_default_per_page(),
							'option'  => $key,
						);
						\add_screen_option( $option, $args );
					}
				);
			}
		}

		/**
		 * Returns the value of all of the records
		 *
		 * @return int
		 *
		 * @since 3.8.0
		 */
		public function get_count() {
			return $this->count;
		}

		/**
		 * Returns an array of CSS class names for the table.
		 *
		 * @return array<int,string> Array of class names.
		 *
		 * @since 1.4.0
		 */
		public function get_table_classes() {
			return array( 'widefat', 'striped', 'table-view-list', $this->_args['plural'] );
		}

		/**
		 * Returns the order in SQL format
		 *
		 * @param string $order The order string.
		 *
		 * @return string
		 *
		 * @since 1.7.0
		 */
		public static function get_order( string $order ) {
			if ( 'asc' === strtolower( $order ) ) {
				return 'ASC';
			} else {
				return 'DESC';
			}
		}

		/**
		 * Returns the order by column name
		 *
		 * @param string $order_by The order by string.
		 *
		 * @return string
		 *
		 * @since 4.1.0
		 */
		public static function get_order_by( string $order_by ) {
			$columns = self::$entity::get_column_names_admin();
			if ( array_key_exists( $order_by, $columns ) ) {
				return $order_by;
			} else {
				return static::$default_order_by;
			}
		}

		/**
		 * Add a standard cron job.
		 *
		 * @param array  $crons       Existing crons.
		 * @param string $setting_key The setting key to check.
		 * @param string $cron_key    The cron key.
		 * @param array  $hook        The hook callback.
		 *
		 * @return array
		 *
		 * @since 4.4.1
		 */
		protected static function add_standard_cron_job( array $crons, string $setting_key, string $cron_key, array $hook ): array {
			if ( -1 !== (int) Settings::get_option( $setting_key ) ) {
				$crons[ $cron_key ] = array(
					'time' => Settings::get_option( $setting_key ),
					'hook' => $hook,
					'args' => array(),
				);
			}
			return $crons;
		}

		/**
		 * Returns the table name
		 *
		 * @return string
		 *
		 * @since 3.8.0
		 */
		public static function get_table_name(): string {
			if ( property_exists( static::class, 'table' ) ) {
				return static::$table::get_name();
			} else {
				return '';
			}
		}
	}
}
