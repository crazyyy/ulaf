<?php
/**
 * Responsible for the requests view
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
use ADVAN\Controllers\Api\Endpoints;
use ADVAN\Lists\Views\Requests_View;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Entities\Requests_Log_Entity;
use ADVAN\Entities_Global\Common_Table;

/**
 * Base list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Requests_List' ) ) {

	/**
	 * Responsible for rendering base table for manipulation
	 *
	 * @since 2.1.0
	 */
	class Requests_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_requests';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_requests_list';

		public const SEARCH_INPUT = 's';

		public const REQUESTS_MENU_SLUG = 'advan_requests';

		public const PLUGIN_FILTER_ACTION = self::PAGE_SLUG . '_filter_plugin';

		/**
		 * The table to show
		 *
		 * @var Common_Table
		 *
		 * @since 2.1.0
		 */
		private static $table;

		/**
		 * How many
		 *
		 * @var int
		 *
		 * @since 2.1.0
		 */
		protected $count;

		/**
		 * How many records to show per page
		 *
		 * @var integer
		 *
		 * @since 2.1.0
		 */
		protected static $rows_per_page = 20;

		/**
		 * Holds the prepared options for speeding the process
		 *
		 * @var array
		 *
		 * @since 2.1.0
		 */
		protected static $admin_columns = array();

		/**
		 * The entity class related to the list
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $entity = Requests_Log_Entity::class;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $default_order_by = 'id';

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 2.1.0
		 */
		public function __construct( string $table_name = '' ) {

			$class = Common_Table::class;

			Common_Table::init( Requests_Log_Entity::get_table_name() );
			self::$table = $class;

			// \add_filter( 'manage_' . WP_Helper::get_wp_screen()->id . '_columns', array( $class, 'manage_columns' ) );

			parent::__construct(
				array(
					'plural'   => Requests_Log_Entity::get_table_name(),    // Plural value used for labels and the objects being listed.
					'singular' => Requests_Log_Entity::get_table_name(),     // Singular label for an object being listed, e.g. 'post'.
					'ajax'     => false,      // If true, the parent class will call the _js_vars() method in the footer.
				)
			);
		}

		/**
		 * Inits class hooks. That is called every time - not in some specific environment set.
		 *
		 * @return void
		 *
		 * @since 2.8.2
		 */
		public static function init() {
			\add_filter( 'advan_cron_hooks', array( __CLASS__, 'add_cron_job' ) );
			\add_action( 'admin_post_' . self::PLUGIN_FILTER_ACTION, array( Requests_View::class, 'plugin_filter_action' ) );
		}

		/**
		 * Adds a cron job for truncating the records in the requests table
		 *
		 * @param array $crons - The array with all the crons associated with the plugin.
		 *
		 * @return array
		 *
		 * @since 2.8.2
		 */
		public static function add_cron_job( $crons ) {
			if ( -1 !== (int) Settings::get_option( 'advana_rest_requests_clear' ) ) {
				$crons[ ADVAN_PREFIX . 'request_table_clear' ] = array(
					'time' => Settings::get_option( 'advana_rest_requests_clear' ),
					'hook' => array( __CLASS__, 'truncate_requests_table' ),
					'args' => array(),
				);
			}

			return $crons;
		}

		/**
		 * Truncates the requests table from CRON job
		 *
		 * @return void
		 *
		 * @since 2.8.2
		 */
		public static function truncate_requests_table() {
			Common_Table::truncate_table( null, Requests_Log_Entity::get_table_name() );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function menu_add() {
			$requests_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Requests viewer', '0-day-analytics' ),
				( ( Settings::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
				self::REQUESTS_MENU_SLUG,
				array( Requests_View::class, 'analytics_requests_page' ),
				6
			);

			self::add_screen_options( $requests_hook );

			\add_filter( 'manage_' . $requests_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $requests_hook, array( Settings::class, 'aadvana_common_help' ) );
			// Process any actions early to allow safe redirects before output.
			\add_action( 'load-' . $requests_hook, array( self::class, 'process_actions_load' ) );
		}

		/**
		 * Handle actions on the early page load hook to avoid header issues.
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
			// Actions are processed during load-<hook> to avoid late redirects.
			$per_page = self::get_screen_option_per_page();

			$current_page = $this->get_pagenum();
			if ( 1 < $current_page ) {
				$offset = $per_page * ( $current_page - 1 );
			} else {
				$offset = 0;
			}

			$search_string = self::escaped_search_input();

			if ( isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ) {
				if ( -1 === (int) $_REQUEST['plugin'] ) {
					$plugin = -1;
				} else {
					$plugin = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin'] ) );
				}
			} else {
				$plugin = '';
			}

			// Get filter parameters.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameters for list table filtering.
			$date_from     = isset( $_REQUEST['date_from'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_from'] ) ) : '';
			$date_to       = isset( $_REQUEST['date_to'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_to'] ) ) : '';
			$status_filter = isset( $_REQUEST['status_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['status_filter'] ) ) : '';
			$type_filter   = isset( $_REQUEST['type_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['type_filter'] ) ) : '';
			$domain_filter = isset( $_REQUEST['domain_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['domain_filter'] ) ) : '';
			$runtime_min   = isset( $_REQUEST['runtime_min'] ) && '' !== $_REQUEST['runtime_min'] ? \sanitize_text_field( \wp_unslash( $_REQUEST['runtime_min'] ) ) : '';
			$runtime_max   = isset( $_REQUEST['runtime_max'] ) && '' !== $_REQUEST['runtime_max'] ? \sanitize_text_field( \wp_unslash( $_REQUEST['runtime_max'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			$wpdb_table = $this->get_table_name();

			$orderby = ( isset( $_GET['orderby'] ) && '' !== trim( $_GET['orderby'] ) ) ? \esc_sql( \wp_unslash( $_GET['orderby'] ) ) : 'id';
			$order   = ( isset( $_GET['order'] ) && '' !== trim( $_GET['order'] ) ) ? \esc_sql( \wp_unslash( $_GET['order'] ) ) : 'DESC';

			$items = $this->fetch_table_data(
				array(
					'search_string' => $search_string,
					'offset'        => $offset,
					'per_page'      => $per_page,
					'wpdb_table'    => $wpdb_table,
					'orderby'       => $orderby,
					'order'         => $order,
					'plugin'        => $plugin,
					'date_from'     => $date_from,
					'date_to'       => $date_to,
					'status_filter' => $status_filter,
					'type_filter'   => $type_filter,
					'domain_filter' => $domain_filter,
					'runtime_min'   => $runtime_min,
					'runtime_max'   => $runtime_max,
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
			$first6_columns = array_keys( Requests_Log_Entity::get_column_names_admin() );

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
		 * @since   1.0.0
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
		 * @since 1.0.0
		 * @since 3.8.0 - added $args param.
		 *
		 * @return array
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
					'wpdb_table'    => $this->get_table_name(),
					'search_sql'    => '',
					'orderby'       => 'id',
					'order'         => 'DESC',
					'count'         => false,
					'plugin'        => '',
					'date_from'     => '',
					'date_to'       => '',
					'status_filter' => '',
					'type_filter'   => '',
					'domain_filter' => '',
					'runtime_min'   => '',
					'runtime_max'   => '',
				)
			);

			$search_string = \sanitize_text_field( \wp_unslash( $parsed_args['search_string'] ) );
			$offset        = (int) $parsed_args['offset'];
			$per_page      = (int) $parsed_args['per_page'];
			$wpdb_table    = \sanitize_text_field( \wp_unslash( $parsed_args['wpdb_table'] ) );
			$orderby       = \sanitize_text_field( \wp_unslash( $parsed_args['orderby'] ) );
			$order         = \sanitize_text_field( \wp_unslash( $parsed_args['order'] ) );
			$plugin        = \sanitize_text_field( \wp_unslash( $parsed_args['plugin'] ) );
			$date_from     = \sanitize_text_field( \wp_unslash( $parsed_args['date_from'] ) );
			$date_to       = \sanitize_text_field( \wp_unslash( $parsed_args['date_to'] ) );
			$status_filter = \sanitize_text_field( \wp_unslash( $parsed_args['status_filter'] ) );
			$type_filter   = \sanitize_text_field( \wp_unslash( $parsed_args['type_filter'] ) );
			$domain_filter = \sanitize_text_field( \wp_unslash( $parsed_args['domain_filter'] ) );
			$runtime_min   = \sanitize_text_field( \wp_unslash( $parsed_args['runtime_min'] ) );
			$runtime_max   = \sanitize_text_field( \wp_unslash( $parsed_args['runtime_max'] ) );

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			if ( '0' === (string) $plugin ) {
				$plugin = '';
			}

			$where_sql_parts = array();
			$where_args      = array();

			if ( '' !== $search_string ) {
				$like           = '%' . $wpdb->esc_like( $search_string ) . '%';
				$search_columns = array_merge( array( 'id' ), array_keys( Requests_Log_Entity::get_all_columns() ) );

				// Exclude columns that are being filtered to avoid conflicts.
				if ( ! empty( $status_filter ) ) {
					$search_columns = array_diff( $search_columns, array( 'request_status' ) );
				}
				if ( ! empty( $type_filter ) ) {
					$search_columns = array_diff( $search_columns, array( 'type' ) );
				}
				if ( ! empty( $domain_filter ) ) {
					$search_columns = array_diff( $search_columns, array( 'domain' ) );
				}
				if ( '' !== $runtime_min || '' !== $runtime_max ) {
					$search_columns = array_diff( $search_columns, array( 'runtime' ) );
				}
				if ( ! empty( $date_from ) || ! empty( $date_to ) ) {
					$search_columns = array_diff( $search_columns, array( 'date_added' ) );
				}

				$like_clauses = array();
				foreach ( $search_columns as $col ) {
					// Column names are from internal whitelist; no user input.
					$like_clauses[] = $col . ' LIKE %s';
					$where_args[]   = $like;
				}
				$where_sql_parts[] = 'AND (' . implode( ' OR ', $like_clauses ) . ')';
			}

			if ( '' !== $plugin && -1 !== (int) $plugin ) {
				$where_sql_parts[] = 'AND plugin = %s';
				$where_args[]      = $plugin; // Already sanitized above.
			}

			// Date range filter - validate date format.
			if ( ! empty( $date_from ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) {
				$where_sql_parts[] = 'AND date_added >= %s';
				$where_args[]      = $date_from . ' 00:00:00';
			}
			if ( ! empty( $date_to ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_to ) ) {
				$where_sql_parts[] = 'AND date_added <= %s';
				$where_args[]      = $date_to . ' 23:59:59';
			}

			// Status filter - whitelist validation.
			if ( ! empty( $status_filter ) && in_array( $status_filter, array( 'success', 'error', 'timeout' ), true ) ) {
				$where_sql_parts[] = 'AND request_status = %s';
				$where_args[]      = $status_filter;
			}

			// Type filter - whitelist validation.
			if ( ! empty( $type_filter ) && in_array( $type_filter, array( 'ajax', 'rest_api', 'cron', 'xmlrpc', 'wp-cli', 'login', 'admin', 'frontend', 'core', 'installing', 'activate', 'undetermined' ), true ) ) {
				$where_sql_parts[] = 'AND type = %s';
				$where_args[]      = $type_filter;
			}

			// Domain filter.
			if ( ! empty( $domain_filter ) ) {
				$where_sql_parts[] = 'AND domain LIKE %s';
				$where_args[]      = '%' . $wpdb->esc_like( $domain_filter ) . '%';
			}

			// Runtime range filter.
			if ( ! empty( $runtime_min ) && is_numeric( $runtime_min ) && (float) $runtime_min > 0 ) {
				$where_sql_parts[] = 'AND runtime >= %f';
				$where_args[]      = (float) $runtime_min;
			}
			if ( ! empty( $runtime_max ) && is_numeric( $runtime_max ) && (float) $runtime_max > 0 ) {
				$where_sql_parts[] = 'AND runtime <= %f';
				$where_args[]      = (float) $runtime_max;
			}

			$wpdb_table = $this->get_table_name();

			// Build WHERE with prepare on dynamic value placeholders.
			$where_clause = '';
			if ( ! empty( $where_sql_parts ) ) {
				// Combine parts and prepare using variable args.
				$sql_unprepared = ' ' . implode( ' ', $where_sql_parts ) . ' ';
				$where_clause   = $wpdb->prepare( $sql_unprepared, $where_args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Count query.
			$count_sql   = "SELECT COUNT(id) FROM {$wpdb_table} WHERE 1=1 {$where_clause}";
			$this->count = (int) Requests_Log_Entity::get_var( $count_sql );

			if ( $parsed_args['count'] ) {
				return array();
			}

			// Whitelist order/orderby via helper methods already applied above.
			$fields = implode( ', ', array_keys( Requests_Log_Entity::get_fields() ) );
			$query  = "SELECT {$fields} FROM {$wpdb_table} WHERE 1=1 {$where_clause} ORDER BY {$orderby} {$order}";
			$query .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, $offset );

			$query_results = Requests_Log_Entity::get_results( $query );

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

				case 'type':
					return '<span id="advana-request-type-' . $item['id'] . '" class="dark-badge badge">' . \esc_html( $item[ $column_name ] ) . '</span>';

				case 'url':
				case 'page_url':
					$value = \str_replace( array( 'http://', 'https://' ), '', $item[ $column_name ] );
					$value = \str_replace( WP_Helper::get_blog_domain(), '', $value );

					$title = \esc_html( $value );

					$value = substr( $value, 0, 70 );

					// Escape & wrap in <code> tag.
					$value = '<code id="advana-request-' . $column_name . '-' . $item['id'] . '" title="' . $title . '">' . \esc_html( $value ) . '</code>';
					return $value;

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

				case 'domain':
					// Escape & wrap in <code> tag.
					return '<code id="advana-request-' . $column_name . '-' . $item['id'] . '">' . \esc_html( $item[ $column_name ] ) . '</code>';

				case 'runtime':
					// Escape & wrap in <code> tag.
					return '<code id="advana-request-' . $column_name . '-' . $item['id'] . '">' . \esc_html( \number_format( (float) $item[ $column_name ], 3 ) ) . 's</code>';

				case 'request_status':
					// Escape & wrap in <code> tag.
					$extra_info = '';
					$style      = 'style="color: #00ff00 !important;"';
					if ( 'error' === $item[ $column_name ] ) {
						$extra_info = ' <div class="status-control-error"><span class="dashicons dashicons-warning" aria-hidden="true"></span> ' . \esc_html( $item['response'] ) . '</div>';
						$style      = 'style="color:rgb(235, 131, 55) !important;"';
					}
					return '<code class="badge dark-badge" id="advana-request-' . $column_name . '-' . $item['id'] . '" ' . $style . '>' . \esc_html( $item[ $column_name ] ) . '</code></br>' . $extra_info . self::format_trace( $item['trace'] );

				case 'request_group':
				case 'request_source':
					// Escape & wrap in <code> tag.
					return '<code>' . \esc_html( $item[ $column_name ] ) . '</code>';
				case 'date_added':
					$query_args_view_data             = array();
					$query_args_view_data['_wpnonce'] = \wp_create_nonce( 'bulk-' . $this->_args['plural'] );
					$delete_url                       =
					\add_query_arg(
						array(
							'action'           => 'delete',
							'advan_' . self::$table::get_name() => (int) $item['id'],
							self::SEARCH_INPUT => self::escaped_search_input(),
							'_wpnonce'         => $query_args_view_data['_wpnonce'],
						)
					);

					$actions['delete'] = '<a class="aadvana-transient-delete" href="' . \esc_url( $delete_url ) . '" onclick="return confirm(\'' . \esc_html__( 'You sure you want to delete this record?', '0-day-analytics' ) . '\');">' . \esc_html__( 'Delete', '0-day-analytics' ) . '</a>';

					$actions['details'] = '<a href="#" class="aadvan-request-show-details" data-details-id="' . \esc_attr( (int) $item['id'] ) . '">' . \esc_html__( 'Details', '0-day-analytics' ) . '</a>';

					$data  = '<div id="advana-request-details-' . $item['id'] . '" style="display: none;">';
					$data .= '<pre style="overflow-y: hidden;">' . \esc_html( var_export( self::get_formatted_string( $item['request_args'] ), true ) ) . '</pre>';
					$data .= '</div>';
					$data .= '<div id="advana-response-details-' . $item['id'] . '" style="display: none;">';
					$data .= '<pre style="overflow-y: hidden;">' . \esc_html( var_export( self::get_formatted_string( $item['response'] ), true ) ) . '</pre>';
					$data .= '</div>';

					$time_format = 'g:i a';

					$item['date_added'] = (int) $item['date_added'];

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

						return sprintf(
							'<span class="status-control-warning"><span class="dashicons dashicons-clock" aria-hidden="true"></span> %s</span><br>%s',
							esc_html( $ago ),
							$time,
						) . $this->row_actions( $actions ) . $data;
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
					) . $this->row_actions( $actions ) . $data;

				case 'plugin':
					if ( ! empty( $item['plugin'] ) ) {
						$plugin = Plugin_Theme_Helper::get_plugin_from_path( $item['plugin'] );
						if ( ! empty( $plugin ) ) {

							return __( 'Plugin: ', '0-day-analytics' ) . '<b>' . \esc_html( $plugin['Name'] ) . '</b><br>' . \__( 'Current version: ', '0-day-analytics' ) . \esc_html( $plugin['Version'] );
						}
					} else {
						return \esc_html__( 'Core or Unknown', '0-day-analytics' );
					}
			}
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
		 * @since 2.1.0
		 */
		protected function column_cb( $item ) {
			$id    = isset( $item['id'] ) ? (int) $item['id'] : 0;
			$table = self::$table::get_name();
			return sprintf(
				'<label class="screen-reader-text" for="%1$s_%2$d">%3$s</label><input type="checkbox" name="advan_%1$s[]" id="%1$s_%2$d" value="%2$d" />',
				\esc_attr( $table ),
				$id,
				sprintf(
					/* translators: The column name. */
					__( 'Select %s', '0-day-analytics' ),
					'id'
				)
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
					 * \wp_nonce_field( 'bulk-' . $this->_args['plural'] );
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
								'paged'            => isset( $_REQUEST['paged'] ) ? (int) $_REQUEST['paged'] : 1,
								'page'             => self::REQUESTS_MENU_SLUG,
								'show_table'       => self::$table::get_name(),
							),
							\admin_url( 'admin.php' )
						)
					);

					// Use server-side safe redirect instead of inline JS for better security.
					\wp_safe_redirect( \esc_url_raw( $redirect ) );
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

			// Edit_Data::set_table( $this->table );
			// Edit_Data::edit_record( $table_id );
		}

		/**
		 * Table navigation.
		 *
		 * @param string $which - Position of the nav.
		 *
		 * @since 1.1.0
		 */
		public function extra_tablenav( $which ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameters for list table filtering.
			if ( isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ) {
				if ( -1 === (int) $_REQUEST['plugin'] ) {
					$plugin = -1;
				} else {
					$plugin = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin'] ) );
				}
			} else {
				$plugin = 0;
			}

			// Get current filter values.
			$date_from     = isset( $_REQUEST['date_from'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_from'] ) ) : '';
			$date_to       = isset( $_REQUEST['date_to'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_to'] ) ) : '';
			$status_filter = isset( $_REQUEST['status_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['status_filter'] ) ) : '';
			$type_filter   = isset( $_REQUEST['type_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['type_filter'] ) ) : '';
			$domain_filter = isset( $_REQUEST['domain_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['domain_filter'] ) ) : '';
			$runtime_min   = isset( $_REQUEST['runtime_min'] ) && '' !== $_REQUEST['runtime_min'] ? \sanitize_text_field( \wp_unslash( $_REQUEST['runtime_min'] ) ) : '';
			$runtime_max   = isset( $_REQUEST['runtime_max'] ) && '' !== $_REQUEST['runtime_max'] ? \sanitize_text_field( \wp_unslash( $_REQUEST['runtime_max'] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			?>
				<!-- <div class="alignleft actions bulkactions"> -->
					<?php echo Requests_Log_Entity::get_all_plugins_dropdown( $plugin, $which ); ?>

					<?php if ( 'top' === $which ) { ?>

						<!-- Status Filter -->
						<select name="status_filter" id="status_filter_<?php echo \esc_attr( $which ); ?>">
							<option value=""><?php \esc_html_e( 'All Statuses', '0-day-analytics' ); ?></option>
							<option value="success" <?php \selected( $status_filter, 'success' ); ?>><?php \esc_html_e( 'Success', '0-day-analytics' ); ?></option>
							<option value="error" <?php \selected( $status_filter, 'error' ); ?>><?php \esc_html_e( 'Error', '0-day-analytics' ); ?></option>
							<option value="timeout" <?php \selected( $status_filter, 'timeout' ); ?>><?php \esc_html_e( 'Timeout', '0-day-analytics' ); ?></option>
						</select>

						<!-- Type Filter -->
						<select name="type_filter" id="type_filter_<?php echo \esc_attr( $which ); ?>">
							<option value=""><?php \esc_html_e( 'All Types', '0-day-analytics' ); ?></option>
							<option value="ajax" <?php \selected( $type_filter, 'ajax' ); ?>><?php \esc_html_e( 'AJAX', '0-day-analytics' ); ?></option>
							<option value="rest_api" <?php \selected( $type_filter, 'rest_api' ); ?>><?php \esc_html_e( 'REST API', '0-day-analytics' ); ?></option>
							<option value="cron" <?php \selected( $type_filter, 'cron' ); ?>><?php \esc_html_e( 'Cron', '0-day-analytics' ); ?></option>
							<option value="xmlrpc" <?php \selected( $type_filter, 'xmlrpc' ); ?>><?php \esc_html_e( 'XML-RPC', '0-day-analytics' ); ?></option>
							<option value="wp-cli" <?php \selected( $type_filter, 'wp-cli' ); ?>><?php \esc_html_e( 'WP-CLI', '0-day-analytics' ); ?></option>
							<option value="login" <?php \selected( $type_filter, 'login' ); ?>><?php \esc_html_e( 'Login', '0-day-analytics' ); ?></option>
							<option value="admin" <?php \selected( $type_filter, 'admin' ); ?>><?php \esc_html_e( 'Admin', '0-day-analytics' ); ?></option>
							<option value="frontend" <?php \selected( $type_filter, 'frontend' ); ?>><?php \esc_html_e( 'Frontend', '0-day-analytics' ); ?></option>
							<option value="core" <?php \selected( $type_filter, 'core' ); ?>><?php \esc_html_e( 'Core', '0-day-analytics' ); ?></option>
							<option value="installing" <?php \selected( $type_filter, 'installing' ); ?>><?php \esc_html_e( 'Installing', '0-day-analytics' ); ?></option>
							<option value="activate" <?php \selected( $type_filter, 'activate' ); ?>><?php \esc_html_e( 'Activate', '0-day-analytics' ); ?></option>
							<option value="undetermined" <?php \selected( $type_filter, 'undetermined' ); ?>><?php \esc_html_e( 'Undetermined', '0-day-analytics' ); ?></option>
						</select>

						<!-- Date Range Filter -->
						<label for="date_from_<?php echo \esc_attr( $which ); ?>"><?php \esc_html_e( 'From:', '0-day-analytics' ); ?></label>
						<input type="date" id="date_from_<?php echo \esc_attr( $which ); ?>" name="date_from" value="<?php echo \esc_attr( $date_from ); ?>" />

						<label for="date_to_<?php echo \esc_attr( $which ); ?>"><?php \esc_html_e( 'To:', '0-day-analytics' ); ?></label>
						<input type="date" id="date_to_<?php echo \esc_attr( $which ); ?>" name="date_to" value="<?php echo \esc_attr( $date_to ); ?>" />

						<!-- Domain Filter -->
						<input type="text" name="domain_filter" id="domain_filter_<?php echo \esc_attr( $which ); ?>" placeholder="<?php \esc_attr_e( 'Domain', '0-day-analytics' ); ?>" value="<?php echo \esc_attr( $domain_filter ); ?>" />

						<!-- Runtime Range Filter -->
						<label for="runtime_min_<?php echo \esc_attr( $which ); ?>"><?php \esc_html_e( 'Runtime (s):', '0-day-analytics' ); ?></label>
						<input type="number" id="runtime_min_<?php echo \esc_attr( $which ); ?>" name="runtime_min" placeholder="Min" value="<?php echo \esc_attr( $runtime_min ); ?>" step="0.001" min="0" style="width: 80px;" />
						<input type="number" id="runtime_max_<?php echo \esc_attr( $which ); ?>" name="runtime_max" placeholder="Max" value="<?php echo \esc_attr( $runtime_max ); ?>" step="0.001" min="0" style="width: 80px;" />

						<!-- Filter Button -->
						<input type="submit" name="filter_action" id="filter_action_<?php echo \esc_attr( $which ); ?>" class="button" value="<?php \esc_attr_e( 'Filter', '0-day-analytics' ); ?>" />
						<a href="<?php echo \esc_url( remove_query_arg( array( 'date_from', 'date_to', 'status_filter', 'type_filter', 'domain_filter', 'runtime_min', 'runtime_max', 's' ) ) ); ?>" class="button"><?php \esc_html_e( 'Clear Filters', '0-day-analytics' ); ?></a>
					<?php } ?>
				<!-- </div> -->
				<script type="text/javascript">
					(function($) {
						'use strict';
						$(document).ready(function() {
							var pluginFilterData = {
								action_url: <?php echo \wp_json_encode( \esc_url( admin_url( 'admin-post.php' ) ) ); ?>,
								action_name: <?php echo \wp_json_encode( \esc_attr( self::PLUGIN_FILTER_ACTION ) ); ?>,
								context: <?php echo \wp_json_encode( \esc_attr( is_network_admin() ? 'network' : 'site' ) ); ?>,
								nonce_field: <?php echo \wp_json_encode( \wp_nonce_field( self::PLUGIN_FILTER_ACTION, self::PLUGIN_FILTER_ACTION . 'nonce', true, false ) ); ?>
							};

							$('form .plugin_filter').on('change', function(e) {
								$('form .plugin_filter').val($(this).val());
								$(this).closest('form')
									.attr('action', pluginFilterData.action_url)
									.append('<input type="hidden" name="action" value="' + pluginFilterData.action_name + '">')
									.append('<input type="hidden" name="context" value="' + pluginFilterData.context + '">')
									.append(pluginFilterData.nonce_field)
									.submit();
							});
							
							// Function to remove empty parameters before form submission
							function submitFormWithCleanParams() {
								var form = $('#requests-filter');
								// Remove empty filter inputs before submission
								form.find('input[name="date_from"], input[name="date_to"], input[name="domain_filter"], input[name="runtime_min"], input[name="runtime_max"]').each(function() {
									if ($(this).val() === '' || $(this).val() === '0') {
										$(this).prop('disabled', true);
									}
								});
								// Remove empty selects
								form.find('select[name="status_filter"], select[name="type_filter"]').each(function() {
									if ($(this).val() === '') {
										$(this).prop('disabled', true);
									}
								});
								form.submit();
							}

							// Auto-submit on filter changes
							$('#requests-filter select[name="status_filter"], #requests-filter select[name="type_filter"]').on('change', function() {
								submitFormWithCleanParams();
							});
							
							$('#requests-filter input[name="date_from"], #requests-filter input[name="date_to"]').on('change', function() {
								submitFormWithCleanParams();
							});
							
							$('#requests-filter input[name="domain_filter"], #requests-filter input[name="runtime_min"], #requests-filter input[name="runtime_max"]').on('blur', function() {
								submitFormWithCleanParams();
							}).on('keypress', function(e) {
								if (e.which === 13) {
									e.preventDefault();
									submitFormWithCleanParams();
								}
							});
							
							// Also handle manual filter button click
							$('#requests-filter input[type="submit"][name="filter_action"]').on('click', function(e) {
								e.preventDefault();
								submitFormWithCleanParams();
							});
						});
					})(jQuery);
				</script>
				<?php
				if ( 'top' === $which && $this->count > 0 ) {
					?>
				<div id="export-form">
					<div>
						<button id="start-export" class="button" data-type-export="requests" data-search="<?php echo \esc_attr( self::escaped_search_input() ); ?>" data-plugin="<?php echo \esc_attr( $plugin ); ?>" data-date-from="<?php echo \esc_attr( $date_from ); ?>" data-date-to="<?php echo \esc_attr( $date_to ); ?>" data-status-filter="<?php echo \esc_attr( $status_filter ); ?>" data-type-filter="<?php echo \esc_attr( $type_filter ); ?>" data-domain-filter="<?php echo \esc_attr( $domain_filter ); ?>" data-runtime-min="<?php echo \esc_attr( $runtime_min ); ?>" data-runtime-max="<?php echo \esc_attr( $runtime_max ); ?>">
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
					<?php echo Miscellaneous::get_flex_style(); ?>
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
					.checkbox-wrapper-2 label{
						margin-right: 7px !important;
						cursor: pointer !important;
					}

					.checkbox-wrapper-2 .ikxBAC {
						appearance: none;
						background-color: #dfe1e4;
						border-radius: 72px;
						border-style: none;
						flex-shrink: 0;
						height: 20px;
						margin: 0;
						position: relative;
						width: 30px;
						cursor: pointer !important;
						border: 1px solid #cec6c6;
					}

					.checkbox-wrapper-2 .ikxBAC::before {
						bottom: -6px !important;
						content: "" !important;
						left: -6px !important;
						position: absolute !important;
						right: -6px !important;
						top: -6px !important;
					}

					.checkbox-wrapper-2 .ikxBAC,
					.checkbox-wrapper-2 .ikxBAC::after {
						transition: all 100ms ease-out;
					}

					.checkbox-wrapper-2 .ikxBAC::after {
						background-color: #e68a6e;
						border-radius: 50%;
						content: "";
						height: 14px;
						left: 3px;
						position: absolute;
						top: 3px;
						width: 14px;
					}

					.checkbox-wrapper-2 input[type=checkbox] {
						cursor: default;
					}

					.checkbox-wrapper-2 .ikxBAC:hover {
						background-color: #c9cbcd;
						transition-duration: 0s;
					}

					.checkbox-wrapper-2 .ikxBAC:checked {
						background-color: #d3f9d6;
					}

					html.aadvana-darkskin .checkbox-wrapper-2 .ikxBAC:checked {
						background-color:rgb(27, 27, 28) !important;
					}

					.checkbox-wrapper-2 .ikxBAC:checked::after {
						background-color: #17c622;
						left: 13px;
					}

					.checkbox-wrapper-2 :focus:not(.focus-visible) {
						outline: 0;
					}

					.checkbox-wrapper-2 .ikxBAC:checked:hover {
						background-color: #dfe1e4;
					}

					.tablenav {
						height: auto !important;
					}
				</style>
				<div style="clear: both;">
					<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
						<div class="checkbox-wrapper-2">
						
							<input type="checkbox"  class="sc-gJwTLC ikxBAC requests-monitoring-filter" name="disable_monitoring[]" value="http" id="advana_http_requests_disable" <?php \checked( Settings::get_option( 'advana_http_requests_disable' ), true ); ?>>
								
							<label for="advana_http_requests_disable" class="badge dark-badge">
							<?php \esc_html_e( 'Disable HTTP monitoring', '0-day-analytics' ); ?>
							</label>
						
							<input type="checkbox"  class="sc-gJwTLC ikxBAC requests-monitoring-filter" name="disable_monitoring[]" value="rest" id="advana_rest_requests_disable" <?php \checked( Settings::get_option( 'advana_rest_requests_disable' ), true ); ?>>
								
							<label for="advana_rest_requests_disable" class="badge dark-badge">
							<?php \esc_html_e( 'Disable REST API monitoring', '0-day-analytics' ); ?>
							</label>
							<script>
								let requests_disable = document.getElementsByClassName("requests-monitoring-filter");

								let len = requests_disable.length;

								// call updateCost() function to onclick event on every checkbox
								for (var i = 0; i < len; i++) {
									if (requests_disable[i].type === 'checkbox') {
										requests_disable[i].onclick = setMonitoring;
									}
								}

								async function setMonitoring(e) {

									let monitoringName = e.target.value;
									let monitoringStatus = e.target.checked;
									let attResp;

									try {
										attResp = await wp.apiFetch({
											path: '/<?php echo Endpoints::ENDPOINT_ROOT_NAME; ?>/v1/requests/' + monitoringName + '/' + ( monitoringStatus ? 'enable' : 'disable' ),
											method: 'GET',
											cache: 'no-cache'
										});

										if (attResp.success) {
											
											location.reload();
										} else if (attResp.message) {
											jQuery('#wp-admin-bar-aadvan-menu .ab-item').html('<b><i>' + attResp.message + '</i></b>');
										}

									} catch (error) {
										throw error;
									}
								}

							</script>
						</div>
					</div>
				</div>
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
			return __( 'Number of requests to show', '0-day-analytics' );
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

				$admin_columns = Requests_Log_Entity::get_column_names_admin();

				$screen_options = $admin_columns;

				$table_columns = array(
					'cb' => '<input type="checkbox" />', // to display the checkbox.
				);

				self::$admin_columns = \array_merge( $table_columns, $screen_options, $columns );
			}

			return self::$admin_columns;
		}

		/**
		 * Formats the trace from the request log.
		 *
		 * @param string $trace - JSON encoded trace.
		 * @param int    $how_back - How much records to skip from the trace array.
		 *
		 * @return string
		 *
		 * @since 2.7.0
		 * @since 3.8.0 - $how_back parameter introduced
		 */
		public static function format_trace( string $trace, ?int $how_back = 6 ): string {

			if ( empty( $trace ) ) {
				return '';
			}

			$trace = \json_decode( $trace, true );

			$defaults = array(
				'line'     => '',
				'file'     => '',
				'class'    => '',
				'function' => '',
			);

			$out = '';

			if ( \is_array( $trace ) && ! empty( $trace ) ) {

				$query_array = array(
					'_wpnonce' => \wp_create_nonce( 'source-view' ),
					'action'   => 'log_source_view',
				);

				$counter = count( $trace ) - $how_back;
				for ( $i = 1; $i < $counter; $i++ ) {
					$sf    = (object) \shortcode_atts( $defaults, $trace[ $i + $how_back ] );
					$index = $i - 1;
					$file  = isset( $sf->file ) ? $sf->file : '';

					$caller = '';
					if ( ! empty( $sf->class ) && ! empty( $sf->function ) ) {
						$caller = $sf->class . '::' . $sf->function . '()';
					} elseif ( ! empty( $sf->function ) ) {
						$caller = $sf->function . '()';
					}

					$source_link = '';

					if ( ! empty( $file ) ) {
						$query_array['error_file'] = $file;
						$query_array['error_line'] = 1;

						if ( isset( $sf->line ) && ! empty( $sf->line ) ) {
							$query_array['error_line'] = (int) $sf->line;
						}

						$query_array['TB_iframe'] = 'true';

						$view_url = \esc_url_raw(
							\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
						);

						$title_attr = \esc_attr( __( 'Viewing: ', '0-day-analytics' ) . $query_array['error_file'] );
						$link_text  = \esc_html( $file ) . '(' . ( isset( $sf->line ) ? (int) $sf->line : '' ) . ')';

						$source_link = ' <a href="' . $view_url . '" title="' . $title_attr . '" class="thickbox view-source">' . $link_text . '</a>';

					}

					$out .= '#' . $index . ' ' . $source_link . ': ' . \esc_html( $caller ) . '<br>';
				}
			}

			return $out;
		}

		/**
		 * Checks string format and decodes it if it is a valid JSON.
		 *
		 * @param string $string - The string to check and decode.
		 *
		 * @return string
		 *
		 * @since 2.7.0
		 */
		public static function get_formatted_string( $string ) {
			$encoded = json_decode( $string, true, 512, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( json_last_error() === JSON_ERROR_NONE ) {

				if ( ! is_array( $encoded ) ) {
					return $encoded;
				}

				foreach ( $encoded as $key => $value ) {
					if ( ! empty( $value ) && is_string( $value ) && ! is_numeric( $value ) ) {
						$encoded[ $key ] = self::get_formatted_string( $value );
					}
				}

				return $encoded;
			} else {
				return $string;
			}
		}

		/**
		 * Sets the request type status.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 *
		 * @return \WP_REST_Response|\WP_Error
		 *
		 * @since 2.8.0
		 */
		public static function set_request_status( \WP_REST_Request $request ) {
			$request_type = $request->get_param( 'request_type' );
			$status       = $request->get_param( 'status' );

			// Restrict to administrators/managers.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'rest_forbidden',
					__( 'Sorry, you are not allowed to modify request monitoring settings.', '0-day-analytics' ),
					array( 'status' => \rest_authorization_required_code() )
				);
			}

			if ( ! in_array( $request_type, array( 'http', 'rest' ), true ) ) {
				return new \WP_Error(
					'invalid_request_type',
					__( 'Invalid request type name.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			$request_type = 'advana_' . $request_type . '_requests_disable';

			$settings = Settings::get_current_options();

			if ( 'enable' === $status ) {
				$settings[ $request_type ] = true;
			} elseif ( 'disable' === $status ) {
				$settings[ $request_type ] = false;
			} else {
				return new \WP_Error(
					'invalid_status',
					__( 'Invalid status.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );

			return rest_ensure_response(
				array(
					'success' => true,
				)
			);
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
			if ( isset( $item['request_status'] ) && ! empty( $item['request_status'] ) ) {
				$classes .= ' ' . $item['request_status'];
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
					
					.<?php echo \esc_attr( Requests_Log_Entity::get_table_name() ); ?> .error th:nth-child(1) {
						border-left: 7px solid #dd9192 !important;
					}
					.<?php echo \esc_attr( Requests_Log_Entity::get_table_name() ); ?> .success th:nth-child(1) {
						border-left: 7px solid rgb(49, 179, 45) !important;
					}
				</style>
				<?php
			}
			?>
			<div class="tablenav <?php echo \esc_attr( $which ); ?>">

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
