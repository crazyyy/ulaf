<?php
/**
 * Responsible for the fatals view
 *
 * @package    advana
 * @subpackage lists
 * @since      1.1
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/0-day-analytics/
 */

declare(strict_types=1);

namespace ADVAN\Lists;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Abstract_List;
use ADVAN\Helpers\Miscellaneous;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Lists\Views\Fatals_View;
use ADVAN\Entities\WP_Fatals_Entity;
use ADVAN\Entities_Global\Common_Table;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Fatals_List' ) ) {

	/**
	 * Responsible for rendering base table for manipulation
	 *
	 * @since 3.8.0
	 */
	class Fatals_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_fatals';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_fatals_list';

		public const SEARCH_INPUT = 's';

		public const FATALS_MENU_SLUG = 'advan_fatals';

		public const PLUGIN_FILTER_ACTION = self::PAGE_SLUG . '_filter_plugin';

		/**
		 * The table to show
		 *
		 * @var Common_Table
		 *
		 * @since 3.8.0
		 */
		private static $table;

		/**
		 * How many
		 *
		 * @var int
		 *
		 * @since 3.8.0
		 */
		protected $count;

		/**
		 * How many records to show per page
		 *
		 * @var integer
		 *
		 * @since 3.8.0
		 */
		protected static $rows_per_page = 20;

		/**
		 * Maximum stack trace lines to display in details
		 *
		 * @var int
		 *
		 * @since 4.7.0
		 */
		protected const MAX_STACK_TRACE_LINES = 50;

		/**
		 * Holds the prepared options for speeding the process
		 *
		 * @var array
		 *
		 * @since 3.8.0
		 */
		protected static $admin_columns = array();

		/**
		 * The entity class related to the list
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $entity = WP_Fatals_Entity::class;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $default_order_by = 'datetime';

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 3.8.0
		 */
		public function __construct( string $table_name = '' ) {

			$class = Common_Table::class;

			Common_Table::init( WP_Fatals_Entity::get_table_name() );
			self::$table = $class;

			// Hook to manage columns can be added here if needed.

			parent::__construct(
				array(
					'plural'   => WP_Fatals_Entity::get_table_name(),    // Plural value used for labels and the objects being listed.
					'singular' => WP_Fatals_Entity::get_table_name(),     // Singular label for an object being listed, e.g. 'post'.
					'ajax'     => false,      // If true, the parent class will call the _js_vars() method in the footer.
				)
			);
		}

		/**
		 * Inits class hooks. That is called every time - not in some specific environment set.
		 *
		 * @return void
		 *
		 * @since 3.8.0
		 */
		public static function init() {
			\add_action( 'admin_post_' . self::PLUGIN_FILTER_ACTION, array( Fatals_View::class, 'plugin_filter_action' ) );
		}

		/**
		 * Truncates the fatals table from CRON job
		 *
		 * @return void
		 *
		 * @since 3.8.0
		 */
		public static function truncate_fatals_table() {
			Common_Table::truncate_table( null, WP_Fatals_Entity::get_table_name() );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function menu_add() {
			$fatals_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'PHP error viewer', '0-day-analytics' ),
				( ( Settings::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
				self::FATALS_MENU_SLUG,
				array( Fatals_View::class, 'analytics_fatals_page' ),
				1
			);

			self::add_screen_options( $fatals_hook );

			\add_filter( 'manage_' . $fatals_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $fatals_hook, array( Settings::class, 'aadvana_common_help' ) );
			// Process actions early to allow safe redirects before any output.
			\add_action( 'load-' . $fatals_hook, array( self::class, 'process_actions_load' ) );
		}

		/**
		 * Handle bulk and row actions during the early page load hook.
		 *
		 * @return void
		 *
		 * @since 4.2.0
		 */
		public static function process_actions_load() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}
			$table = new self( '' );
			$table->handle_table_actions();
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
		 *
		 * @since   1.0.0
		 */
		public function prepare_items() {
			// Actions are processed during load-<hook> to avoid header warnings.

			$per_page = self::get_screen_option_per_page();

			$current_page = $this->get_pagenum();
			if ( 1 < $current_page ) {
				$offset = $per_page * ( $current_page - 1 );
			} else {
				$offset = 0;
			}

			$search_string = self::escaped_search_input();

			/* phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view-only filter state; sanitized below. */
			if ( isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ) {
				if ( -1 === (int) $_REQUEST['plugin'] ) {
					$plugin = -1;
				} else {
					$plugin_raw = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin'] ) );
					// Validate plugin slug format (alphanumeric, dashes, underscores, max 255 chars).
					if ( preg_match( '/^[a-zA-Z0-9\-_]{1,255}$/', $plugin_raw ) ) {
						$plugin = $plugin_raw;
					} else {
						$plugin = '';
					}
				}
			} else {
				$plugin = '';
			}

			// $wpdb_table = $this->get_table_name();

			/* phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view-only sorting params */
			$orderby = ( isset( $_GET['orderby'] ) && '' !== trim( (string) $_GET['orderby'] ) ) ? \esc_sql( \sanitize_text_field( \wp_unslash( $_GET['orderby'] ) ) ) : 'datetime';
			/* phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view-only sorting params */
			$order = ( isset( $_GET['order'] ) && '' !== trim( (string) $_GET['order'] ) ) ? \esc_sql( \sanitize_text_field( \wp_unslash( $_GET['order'] ) ) ) : 'DESC';

			$items = $this->fetch_table_data(
				array(
					'search_string' => $search_string,
					'offset'        => $offset,
					'per_page'      => $per_page,
					// 'wpdb_table'    => $wpdb_table,
					'orderby'       => $orderby,
					'order'         => $order,
					'plugin'        => $plugin,
				)
			);

			$columns = self::manage_columns( array() );
			$hidden  = \get_user_option( 'manage' . WP_Helper::get_wp_screen()->id . 'columnshidden', false );
			if ( ! $hidden ) {
				$hidden = array();
			}
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->items = $items;
			// Set the pagination.
			$this->set_pagination_args(
				array(
					'total_items' => $this->count,
					'per_page'    => self::get_screen_option_per_page(),
					'total_pages' => ceil( $this->count / self::get_screen_option_per_page() ),
				)
			);
		}

		/**
		 * Get a list of columns. The format is:
		 * 'internal-name' => 'Title'
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_columns() {
			return self::manage_columns( array() );
		}

		/**
		 * Get a list of sortable columns. The format is:
		 * 'internal-name' => 'orderby'
		 * or
		 * 'internal-name' => array( 'orderby', true )
		 *
		 * The second format will make the initial sorting order be descending
		 *
		 * @since 1.1.0
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$first6_columns   = array_keys( WP_Fatals_Entity::get_column_names_admin() );
			$sortable_columns = array();

			/**
			 * Actual sorting still needs to be done by prepare_items.
			 * specify which columns should have the sort icon.
			 *
			 * The second bool param sets the column sort order - true ASC, false - DESC or unsorted.
			 */
			foreach ( $first6_columns as  $value ) {
				$sortable_columns[ $value ] = array( $value, false );
			}

			return $sortable_columns;
		}

		/**
		 * Text displayed when no user data is available
		 *
		 * @since 3.8.0
		 *
		 * @return void
		 */
		public function no_items() {
			\esc_html_e( 'No rows', '0-day-analytics' );
		}

		/**
		 * Fetch table data from the WordPress database.
		 *
		 * @param array $args - The arguments collected / passed.
		 *
		 * @since 3.8.0
		 *
		 * @return  Array
		 */
		public function fetch_table_data( array $args = array() ) {

			global $wpdb;

			// Parse.
			$parsed_args = \wp_parse_args(
				$args,
				array(
					'offset'        => 0,
					'search_string' => self::escaped_search_input(),
					'per_page'      => self::get_screen_option_per_page(),
					// 'wpdb_table'    => $this->get_table_name(),
					'search_sql'    => '',
					'orderby'       => 'datetime',
					'order'         => 'DESC',
					'count'         => false,
					'plugin'        => '',
				)
			);

			$search_string = $wpdb->esc_like( \sanitize_text_field( \wp_unslash( $parsed_args['search_string'] ) ) );
			$offset        = (int) $parsed_args['offset'];
			$per_page      = (int) $parsed_args['per_page'];
			// $wpdb_table    = \sanitize_key( (string) $parsed_args['wpdb_table'] );
			$orderby = \esc_sql( \sanitize_text_field( \wp_unslash( $parsed_args['orderby'] ) ) );
			$order   = \sanitize_text_field( \wp_unslash( $parsed_args['order'] ) );
			$plugin  = \sanitize_text_field( \wp_unslash( $parsed_args['plugin'] ) );

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			if ( '0' === (string) $plugin ) {
				$plugin = '';
			}

			$search_sql = '';

			if ( '' !== $search_string ) {
				$like           = '%' . $wpdb->esc_like( $search_string ) . '%';
				$search_parts   = array();
				$search_parts[] = $wpdb->prepare( 'id LIKE %s', $like );
				foreach ( array_keys( WP_Fatals_Entity::get_all_columns() ) as $value ) {
					// Column names come from a trusted source. Only values are prepared.
					$search_parts[] = $value . ' ' . $wpdb->prepare( 'LIKE %s', $like );
				}
				$search_sql = ' AND (' . implode( ' OR ', $search_parts ) . ') ';
			}

			if ( '' !== $plugin && -1 !== (int) $plugin ) {
				$search_sql .= $wpdb->prepare( ' AND source_slug = %s ', (string) $plugin );
			}

			$wpdb_table = $this->get_table_name();

			$query = 'SELECT
				' . implode( ', ', \array_keys( WP_Fatals_Entity::get_fields() ) ) . '
			  FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql . ' ORDER BY ' . $orderby . ' ' . $order;

			$query .= $wpdb->prepare( ' LIMIT %d OFFSET %d;', $per_page, $offset );

			// query output_type will be an associative array with ARRAY_A.
			$query_results = WP_Fatals_Entity::get_results( $query );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Counting rows for pagination; table name is trusted; dynamic WHERE clause values are prepared above.
			$this->count = (int) WP_Fatals_Entity::get_var( 'SELECT COUNT(id) FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql );

			// return result array to prepare_items.
			return $query_results;
		}

		/**
		 * Filter the table data based on the user search key
		 *
		 * @since 1.0.0
		 *
		 * @param array  $table_data - The data from the row.
		 * @param string $search_key - The search key.
		 *
		 * @return array
		 */
		public function filter_table_data( $table_data, $search_key ) {
			$filtered_table_data = array_values(
				array_filter(
					$table_data,
					function( $row ) use ( $search_key ) {
						foreach ( $row as $row_val ) {
							if ( stripos( $row_val, $search_key ) !== false ) {
								return true;
							}
						}
					}
				)
			);

			return $filtered_table_data;
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * Use that method for common rendering and separate columns logic in different methods. See below.
		 *
		 * @param array  $item - Array with the current row values.
		 * @param string $column_name - The name of the currently processed column.
		 *
		 * @return mixed
		 *
		 * @since 2.7.0
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'user_id':
					if ( isset( $item[ $column_name ] ) && ! empty( $item[ $column_name ] ) && 0 !== $item[ $column_name ] ) {
						$user = \get_user_by( 'id', $item[ $column_name ] );
						if ( $user ) {
							return '<a href="' . \esc_url( \get_edit_user_link( $user->ID ) ) . '">' . \esc_html( $user->display_name ) . '</a> (' . \esc_html( $user->user_email ) . ')';
						} else {
							return \esc_html__( 'Unknown or deleted user', '0-day-analytics' );
						}
					} else {
						return \esc_html__( 'WP System or Anonymous user', '0-day-analytics' );
					}

				case 'message':
					$message  = '<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
						<div>
						</div>
						<div class=""><span title="' . \esc_attr__( 'Copy to clipboard', '0-day-analytics' ) . '" class="dashicons dashicons-clipboard" style="cursor:pointer;" aria-hidden="true"></span> <span title="' . \esc_attr__( 'Share', '0-day-analytics' ) . '" class="dashicons dashicons-share" style="cursor:pointer;" aria-hidden="true"></span></div>
					</div>';
					$message .= '<span class="error_message">' . \esc_html( $item[ $column_name ] ) . '</span>';
					if ( isset( $item['sub_items'] ) && ! empty( $item['sub_items'] ) ) {
						$message .= '<div style="margin-top:10px;"><input type="button" class="button button-primary show_log_details" value="' . \esc_attr__( 'Show details', '0-day-analytics' ) . '"></div>';

						$reversed_details = \array_reverse( $item['sub_items'] );
						$message         .= '<div class="log_details_show" style="display:none;position: relative;"><pre style="background:#07073a; color:#c2c8cd; padding: 5px; overflow-y:auto;max-width: 95%; position: absolute;">';

						$query_array = array(
							'_wpnonce' => \wp_create_nonce( 'source-view' ),
							'action'   => 'log_source_view',
						);
						$line_count = 0;
						foreach ( $reversed_details as $val ) {
							if ( $line_count >= self::MAX_STACK_TRACE_LINES ) {
								$message .= '<br>... (' . ( count( $reversed_details ) - $line_count ) . ' more lines truncated)';
								break;
							}

							$source_link = '';

							if ( isset( $val['file'] ) && ! empty( $val['file'] ) ) {
								$query_array['error_file'] = $val['file'];
								$query_array['error_line'] = 1;

								if ( isset( $val['line'] ) && ! empty( $val['line'] ) ) {
									$query_array['error_line'] = $val['line'];
								}

								$query_array['TB_iframe'] = 'true';

								$view_url = \esc_url_raw(
									\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
								);

								$title = __( 'Viewing: ', '0-day-analytics' ) . $query_array['error_file'];

								$source_link = ' <a href="' . \esc_url( $view_url ) . '" title="' . \esc_attr( $title ) . '" class="thickbox view-source">' . \esc_html( $query_array['error_file'] . ':' . (string) $query_array['error_line'] ) . '</a><br>';

							}

							$message .= ( isset( $val['call'] ) && ! empty( $val['call'] ) ) ? '<b><i>' . \esc_html( (string) $val['call'] ) . '</i></b> - ' : '';

							if ( ! empty( $source_link ) ) {
								$message .= $source_link;
							} else {
								$message .= ( isset( $val['file'] ) && ! empty( $val['file'] ) ) ? \esc_html( (string) $val['file'] ) . ' ' : '';
								$message .= ( isset( $val['line'] ) && ! empty( $val['line'] ) ) ? \esc_html( (string) $val['line'] ) . '<br>' : '';
							}

							$message = \rtrim( $message, ' - ' );
							$line_count++;
						}
						$message .= '</pre></div>';
					}
					return $message;

				case 'error_file':
					if ( isset( $item['error_file'] ) && ! empty( $item['error_file'] ) ) {

						$query_array = array(
							'_wpnonce' => \wp_create_nonce( 'source-view' ),
							'action'   => 'log_source_view',
						);

						$source_link = '';

						$query_array['error_file'] = $item['error_file'];
						$query_array['error_line'] = 1;

						if ( isset( $item['error_line'] ) && ! empty( $item['error_line'] ) ) {
							$query_array['error_line'] = $item['error_line'];
						}

						$query_array['TB_iframe'] = 'true';

						$view_url = \esc_url_raw(
							\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
						);

						$title = __( 'Viewing: ', '0-day-analytics' ) . $query_array['error_file'];

						$source_link = ' <a href="' . \esc_url( $view_url ) . '" title="' . \esc_attr( $title ) . '" class="thickbox view-source">' . \esc_html( $query_array['error_file'] . ':' . (string) $query_array['error_line'] ) . '</a><br>';

						return $source_link;
					}
					return isset( $item['error_file'] ) ? \esc_html( (string) $item['error_file'] ) : '';

				case 'ip':
					if ( \is_string( $item['ip'] ) ) {
						$ips = \explode( ',', $item['ip'] );
						$ips = array_map( 'esc_html', array_map( 'trim', $ips ) );
						return implode( '<br>', $ips );
					}
					return \esc_html( (string) $item['ip'] );
				case 'severity':
				case 'type_env':
				case 'user_roles':
				case 'version_text':
				case 'source_type':
				case 'repeating':
					return '<b>' . \esc_html( $item[ $column_name ] ) . '</b>';
				case 'datetime':
					$query_args_view_data             = array();
					$query_args_view_data['_wpnonce'] = \wp_create_nonce( 'bulk-' . $this->_args['plural'] );
					$delete_url                       =
					\add_query_arg(
						array(
							'action'           => 'delete',
							'advan_' . self::$table::get_name() => $item['id'],
							self::SEARCH_INPUT => self::escaped_search_input(),
							'_wpnonce'         => $query_args_view_data['_wpnonce'],
						)
					);

					$actions['delete'] = '<a class="aadvana-transient-delete" href="' . \esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'You sure you want to delete this record?', '0-day-analytics' ) ) . '\');">' . \esc_html__( 'Delete', '0-day-analytics' ) . '</a>';

					$actions['details'] = '<a class="aadvana-tablerow-view" href="#" data-details-id="' . \esc_attr( (string) $item[ self::$table::get_real_id_name() ] ) . '">' . \esc_html__( 'View', '0-day-analytics' ) . '</a>';

					$time_format = 'g:i a';

					$item['date_added'] = (int) $item['datetime'];

					$event_datetime_utc = \gmdate( 'Y-m-d H:i:s', $item['date_added'] );

					$timezone_local  = \wp_timezone();
					$event_local     = \get_date_from_gmt( $event_datetime_utc, 'Y-m-d' );
					$today_local     = ( new \DateTimeImmutable( 'now', $timezone_local ) )->format( 'Y-m-d' );
					$tomorrow_local  = ( new \DateTimeImmutable( 'tomorrow', $timezone_local ) )->format( 'Y-m-d' );
					$yesterday_local = ( new \DateTimeImmutable( 'yesterday', $timezone_local ) )->format( 'Y-m-d' );

					// If the offset of the date of the event is different from the offset of the site, add a marker.
					if ( \get_date_from_gmt( $event_datetime_utc, 'P' ) !== \get_date_from_gmt( 'now', 'P' ) ) {
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

						return sprintf(
							'<span class="status-control-warning"><span class="dashicons dashicons-clock" aria-hidden="true"></span> %s</span><br>%s',
							esc_html( $ago ),
							$time,
						) . $this->row_actions( $actions );
					} elseif ( 0 === $until ) {
						$in = __( 'Now', '0-day-analytics' );
					} else {
						$in = sprintf(
						/* translators: %s: Time period, for example "8 minutes" */
							__( 'In %s', '0-day-analytics' ),
							WP_Helper::interval( $until ),
						);
					}

					return sprintf(
						'<span class="status-control-warning"><span class="dashicons dashicons-clock" aria-hidden="true"></span> %s</span><br>%s',
						\esc_html( $in ),
						$time,
					) . $this->row_actions( $actions );

				case 'source':
					if ( ! empty( $item['source'] ) ) {

						return '<b>' . \esc_html( $item['source'] ) . '</b>';

					} else {
						return \esc_html__( 'Unknown', '0-day-analytics' );
					}
			}
			// Default return for unhandled columns.
			return isset( $item[ $column_name ] ) ? \esc_html( $item[ $column_name ] ) : '';
		}

		/**
		 * Get value for checkbox column.
		 *
		 * The special 'cb' column
		 *
		 * @param object $item - A row's data.
		 *
		 * @return string Text to be placed inside the column < td > .
		 *
		 * @since 3.8.0
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<label class="screen-reader-text" for="' . self::$table::get_name() . '_' . (int) $item['id'] . '">' . sprintf(
				// translators: The column name.
					__( 'Select %s', '0-day-analytics' ),
					'id'
				) . '</label>'
				. '<input type="checkbox" name="advan_' . self::$table::get_name() . '[]" id="' . self::$table::get_name() . '_' . (int) $item['id'] . '" value="' . (int) $item['id'] . '" />'
			);
		}

		/**
		 * Returns an associative array containing the bulk actions
		 *
		 * @since    1.0.0
		 *
		 * @return array
		 */
		public function get_bulk_actions() {

			/**
			 * On hitting apply in bulk actions the url params are set as
			 * ?action=bulk-download&paged=1&action2=-1
			 *
			 * Action and action2 are set based on the triggers above or below the table
			 */
			$actions = array(
				'delete' => __( 'Delete Records', '0-day-analytics' ),
			);

			return $actions;
		}

		/**
		 * Process actions triggered by the user
		 *
		 * @since    1.0.0
		 */
		public function handle_table_actions() {

			/**
			 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
			 *
			 * Action - is set if checkbox from top-most select-all is set, otherwise returns -1
			 * Action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
			 */

			if ( \is_user_logged_in() && \current_user_can( 'manage_options' ) ) {

				// check for individual row actions.
				$the_table_action = $this->current_action();

				// check for table bulk actions.
				if ( ( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) || ( isset( $_REQUEST['action2'] ) && 'delete' === $_REQUEST['action2'] ) ) {

					// verify the nonce.
					/**
					 * Note: the nonce field is set by the parent class
					 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
					 */
					WP_Helper::verify_admin_nonce( 'bulk-' . $this->_args['plural'] );

					if ( isset( $_REQUEST[ 'advan_' . self::$table::get_name() ] ) ) {
						foreach ( (array) $_REQUEST[ 'advan_' . self::$table::get_name() ] as $id ) {
							self::$table::delete_by_id( (int) $id );
						}
					}

					$redirect =
					\remove_query_arg(
						array( 'delete', '_wpnonce', 'advan_' . self::$table::get_name(), 'action' ),
						\add_query_arg(
							array(
								self::SEARCH_INPUT => self::escaped_search_input(),
								'paged'            => $this->get_pagenum(),
								'page'             => self::FATALS_MENU_SLUG,
								'show_table'       => self::$table::get_name(),
							),
							\admin_url( 'admin.php' )
						)
					);

					wp_safe_redirect( $redirect );
					exit;
				}
			}
		}

		/**
		 * View a license information.
		 *
		 * @since   1.0.0
		 *
		 * @param int $table_id  - Record ID.
		 */
		public function page_view_data( $table_id ) {

			// Render/edit view is handled by the respective view class.
		}

		/**
		 * Table navigation.
		 *
		 * @param string $which - Position of the nav.
		 *
		 * @since 1.1.0
		 */
		public function extra_tablenav( $which ) {
			/* phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view-only filter state; sanitized below. */
			if ( isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ) {
				if ( -1 === (int) $_REQUEST['plugin'] ) {
					$plugin = -1;
				} else {
					$plugin = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin'] ) );
				}
			} else {
				$plugin = 0;
			}
			?>
				<div class="alignleft actions bulkactions">
					
					<?php echo WP_Fatals_Entity::get_all_plugins_dropdown( $plugin, $which );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					
				</div>
				<script>
					jQuery('form .plugin_filter').on('change', function(e) {
						jQuery('form .plugin_filter').val(jQuery(this).val());
						jQuery( this ).closest( 'form' ).attr( 'action', '<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>').append('<input type="hidden" name="action" value="<?php echo \esc_attr( self::PLUGIN_FILTER_ACTION ); ?>">').append('<input type="hidden" name="context" value="<?php echo \esc_attr( ( \is_network_admin() ) ? 'network' : 'site' ); ?>">').append('<?php \wp_nonce_field( self::PLUGIN_FILTER_ACTION, self::PLUGIN_FILTER_ACTION . 'nonce' ); ?>').submit();
					});
				</script>
				<?php
				if ( 'top' === $which && $this->count > 0 ) {
					?>
				<div id="export-form">
					<div>
						<button id="start-export" class="button" data-type-export="fatals" data-search="<?php echo \esc_attr( self::escaped_search_input() ); ?>" data-plugin="<?php echo \esc_attr( $plugin ); ?>">
							<?php echo \esc_html__( 'CSV Export', '0-day-analytics' ); ?>
						</button>
						<button id="cancel-export" class="button cancel-btn" style="display:none;">
							<?php echo \esc_html__( 'Cancel', '0-day-analytics' ); ?>
						</button>
					</div>

					<div id="progress-container" class="progress-wrap" style="display:none;">
						<div id="progress-bar"></div>
					</div>

					<p id="progress-text" style="display:none;"><?php echo esc_html__( 'Waiting to start...', '0-day-analytics' ); ?></p>
				</div>

					<?php
				}
				if ( 'top' === $which ) {
					?>
				<style>
					<?php echo Miscellaneous::get_flex_style(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					/* .wp-list-table {
						display: block;
						overflow-x: auto;
						white-space: nowrap;
					}
					.wp-list-table tbody {
						display: table;
						width: 100%;
					}
					.wp-list-table thead {
						position: sticky;
						z-index: 2;
						top: 0;
					} */
					.tablenav {
						height: auto !important;
					}
				</style>


						<?php } ?>
				<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
					<div class=""> <?php \esc_html_e( 'Size: ', '0-day-analytics' ); ?> <?php echo \esc_attr( \size_format( Common_Table::get_table_size() ) ); ?>

						<?php
						$table_info = Common_Table::get_table_status();
						if ( ! empty( $table_info ) && isset( $table_info[0] ) ) {

							if ( isset( $table_info[0]['Engine'] ) ) {
								?>
							| <b><?php \esc_html_e( 'Engine: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Engine'] ); ?></span>
								<?php
							}

							if ( isset( $table_info[0]['Auto_increment'] ) ) {
								?>
							| <b><?php \esc_html_e( 'Auto increment: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Auto_increment'] ); ?></span>
								<?php
							}

							if ( isset( $table_info[0]['Collation'] ) ) {
								?>
							| <b><?php \esc_html_e( 'Collation: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Collation'] ); ?></span>
								<?php
							}

							if ( isset( $table_info[0]['Create_time'] ) ) {
								?>
							| <b><?php \esc_html_e( 'Create time : ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Create_time'] ); ?></span>
								<?php
							}

							if ( isset( $table_info[0]['Update_time'] ) ) {
								?>
							| <b><?php \esc_html_e( 'Update time : ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Update_time'] ); ?></span>
								<?php
							}
						}
						?>
					</div>
					<div>
						
					</div>
				</div>
					<?php
		}

		/**
		 * Returns translated text for per page option
		 *
		 * @return string
		 *
		 * @since 2.3.0
		 */
		private static function get_screen_per_page_title(): string {
			return __( 'Number of fatals to show', '0-day-analytics' );
		}

		/**
		 * Adds columns to the screen options screed.
		 *
		 * @param array $columns - Array of column names.
		 *
		 * @since 1.1.0
		 */
		public static function manage_columns( $columns ): array {

			if ( empty( self::$admin_columns ) ) {

				$admin_columns = WP_Fatals_Entity::get_column_names_admin();

				$screen_options = $admin_columns;

				$table_columns = array(
					'cb' => '<input type="checkbox" />', // to display the checkbox.
				);

				self::$admin_columns = \array_merge( $table_columns, $screen_options, $columns );
			}

			return self::$admin_columns;
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @param object|array $item - The current item.
		 *
		 * @since 3.3.1
		 */
		public function single_row( $item ) {
			$classes = '';
			if ( isset( $item['fatal_status'] ) && ! empty( $item['fatal_status'] ) ) {
				$classes .= ' ' . $item['fatal_status'];
			}
			echo '<tr class="' . \esc_attr( $classes ) . '">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		/**
		 * Generates the table navigation above or below the table
		 *
		 * @param string $which - Holds info about the top and bottom navigation.
		 *
		 * @since 3.3.1
		 */
		public function display_tablenav( $which ) {
			if ( 'top' === $which ) {
				\wp_nonce_field( 'bulk-' . $this->_args['plural'] );

				?>
				<style>
					
					.<?php echo esc_attr( WP_Fatals_Entity::get_table_name() ); ?> .error th:nth-child(1) {
						border-left: 7px solid #dd9192 !important;
					}
					.<?php echo esc_attr( WP_Fatals_Entity::get_table_name() ); ?> .success th:nth-child(1) {
						border-left: 7px solid rgb(49, 179, 45) !important;
					}
				</style>
				<?php
			}
			?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) { ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php
			}
			$this->extra_tablenav( $which );
			$this->pagination( $which );

			?>

				<br class="clear" />
			</div>
			<?php
		}
	}
}
