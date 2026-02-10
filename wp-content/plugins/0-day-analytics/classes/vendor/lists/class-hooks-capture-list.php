<?php
/**
 * Responsible for the hooks capture view
 *
 * @package    advana
 * @subpackage lists
 * @since      4.5.0
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       https://wordpress.org/plugins/0-day-analytics/
 */

declare(strict_types=1);

namespace ADVAN\Lists;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Abstract_List;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Entities\Hook_Groups_Entity;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Entities\Hooks_Capture_Entity;
use ADVAN\Lists\Views\Hooks_Capture_View;
use ADVAN\Helpers\Hook_Parameter_Renderer;
use ADVAN\Entities\Hooks_Management_Entity;

/**
 * Hooks capture list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Hooks_Capture_List' ) ) {

	/**
	 * Responsible for rendering hooks capture table
	 *
	 * @since 4.5.0
	 */
	class Hooks_Capture_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_hooks_capture';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_hooks_capture_list';

		public const SEARCH_INPUT = 's';

		public const HOOKS_CAPTURE_MENU_SLUG = 'advan_hooks_capture';

		public const MENU_SLUG = 'advan_hooks_capture';

		/**
		 * The table to show
		 *
		 * @var Common_Table
		 *
		 * @since 4.5.0
		 */
		private static $table;

		/**
		 * How many
		 *
		 * @var int
		 *
		 * @since 4.5.0
		 */
		protected $count;

		/**
		 * How many records to show per page
		 *
		 * @var integer
		 *
		 * @since 4.5.0
		 */
		protected static $rows_per_page = 20;

		/**
		 * Holds the prepared options for speeding the process
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $admin_columns = array();

		/**
		 * The entity class related to the list
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $entity = Hooks_Capture_Entity::class;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $default_order_by = 'id';

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 4.5.0
		 */
		public function __construct( string $table_name = '' ) {

			$class = Common_Table::class;

			Common_Table::init( Hooks_Capture_Entity::get_table_name() );
			self::$table = $class;

			parent::__construct(
				array(
					'plural'   => Hooks_Capture_Entity::get_table_name(),
					'singular' => Hooks_Capture_Entity::get_table_name(),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Inits class hooks. That is called every time - not in some specific environment set.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function init() {
			\add_filter( 'advan_cron_hooks', array( __CLASS__, 'add_cron_job' ) );
		}

		/**
		 * Adds a cron job for truncating the records in the hooks capture table
		 *
		 * @param array $crons - The array with all the crons associated with the plugin.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function add_cron_job( $crons ) {
			if ( -1 !== (int) Settings::get_option( 'advana_hooks_capture_clear' ) ) {
				$crons[ ADVAN_PREFIX . 'hooks_capture_clear' ] = array(
					'time' => Settings::get_option( 'advana_hooks_capture_clear' ),
					'hook' => array( __CLASS__, 'truncate_hooks_capture_table' ),
					'args' => array(),
				);
			}

			return $crons;
		}

		/**
		 * Truncates the hooks capture table from CRON job
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function truncate_hooks_capture_table() {
			Common_Table::truncate_table( null, Hooks_Capture_Entity::get_table_name() );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function menu_add() {
			$hooks_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Hooks Capture', '0-day-analytics' ),
				'manage_options',
				self::HOOKS_CAPTURE_MENU_SLUG,
				array( Hooks_Capture_View::class, 'analytics_hooks_capture_page' ),
				7
			);

			self::add_screen_options( $hooks_hook );

			\add_filter( 'manage_' . $hooks_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $hooks_hook, array( Settings::class, 'aadvana_common_help' ) );
			\add_action( 'load-' . $hooks_hook, array( self::class, 'process_actions_load' ) );
		}

		/**
		 * Handle actions on the early page load hook to avoid header issues.
		 *
		 * @return void
		 *
		 * @since 4.5.0
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
		 * @since 4.5.0
		 */
		public function prepare_items() {
			$per_page = self::get_screen_option_per_page();

			$current_page = $this->get_pagenum();
			if ( 1 < $current_page ) {
				$offset = $per_page * ( $current_page - 1 );
			} else {
				$offset = 0;
			}

			$search_string = self::escaped_search_input();
			$wpdb_table    = $this->get_table_name();

			$orderby = ( isset( $_GET['orderby'] ) && '' !== trim( $_GET['orderby'] ) ) ? \esc_sql( \wp_unslash( $_GET['orderby'] ) ) : 'id';
			$order   = ( isset( $_GET['order'] ) && '' !== trim( $_GET['order'] ) ) ? \esc_sql( \wp_unslash( $_GET['order'] ) ) : 'DESC';

				// Filter by hook type.
			$hook_type = '';
			if ( isset( $_REQUEST['hook_type'] ) && ! empty( $_REQUEST['hook_type'] ) ) {
				$hook_type = \sanitize_text_field( \wp_unslash( $_REQUEST['hook_type'] ) );
			}

			// Filter by trigger source.
			$trigger_source = '';
			if ( isset( $_REQUEST['trigger_source'] ) && ! empty( $_REQUEST['trigger_source'] ) ) {
				$trigger_source = \sanitize_text_field( \wp_unslash( $_REQUEST['trigger_source'] ) );
			}

			// Filter by date from.
			$date_from = '';
			if ( isset( $_REQUEST['date_from'] ) && ! empty( $_REQUEST['date_from'] ) ) {
				$date_from = \sanitize_text_field( \wp_unslash( $_REQUEST['date_from'] ) );
			}

			// Filter by date to.
			$date_to = '';
			if ( isset( $_REQUEST['date_to'] ) && ! empty( $_REQUEST['date_to'] ) ) {
				$date_to = \sanitize_text_field( \wp_unslash( $_REQUEST['date_to'] ) );
			}

			// Filter by group.
			$group_filter = 0;
			if ( isset( $_REQUEST['group_filter'] ) && ! empty( $_REQUEST['group_filter'] ) ) {
				$group_filter = absint( $_REQUEST['group_filter'] );
			}

			$items = $this->fetch_table_data(
				array(
					'search_string'  => $search_string,
					'offset'         => $offset,
					'per_page'       => $per_page,
					'wpdb_table'     => $wpdb_table,
					'orderby'        => $orderby,
					'order'          => $order,
					'hook_type'      => $hook_type,
					'trigger_source' => $trigger_source,
					'date_from'      => $date_from,
					'date_to'        => $date_to,
					'group_filter'   => $group_filter,
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
			$this->set_pagination_args(
				array(
					'total_items' => $this->count,
					'per_page'    => self::get_screen_option_per_page(),
					'total_pages' => ceil( $this->count / self::get_screen_option_per_page() ),
				)
			);
		}

		/**
		 * Get a list of columns.
		 *
		 * @since 4.5.0
		 *
		 * @return array
		 */
		public function get_columns() {
			return self::manage_columns( array() );
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @since 4.5.0
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$columns = array_keys( Hooks_Capture_Entity::get_column_names_admin() );

			$sortable_columns = array();

			foreach ( $columns as $value ) {
				$sortable_columns[ $value ] = array( $value, false );
			}

			return $sortable_columns;
		}

		/**
		 * Text displayed when no data is available
		 *
		 * @since 4.5.0
		 *
		 * @return void
		 */
		public function no_items() {
			\esc_html_e( 'No hooks captured yet.', '0-day-analytics' );
		}

		/**
		 * Fetch table data from the WordPress database.
		 *
		 * @param array $args - The arguments collected / passed.
		 *
		 * @since 4.5.0
		 *
		 * @return array
		 */
		public function fetch_table_data( array $args = array() ) {

			global $wpdb;

			$parsed_args = \wp_parse_args(
				$args,
				array(
					'offset'         => 0,
					'search_string'  => self::escaped_search_input(),
					'per_page'       => self::get_screen_option_per_page(),
					'wpdb_table'     => $this->get_table_name(),
					'orderby'        => 'id',
					'order'          => 'DESC',
					'count'          => false,
					'hook_type'      => '',
					'trigger_source' => '',
					'group_filter'   => 0,
					'date_from'      => '',
					'date_to'        => '',
				)
			);

			$search_string  = \sanitize_text_field( \wp_unslash( $parsed_args['search_string'] ) );
			$offset         = (int) $parsed_args['offset'];
			$per_page       = (int) $parsed_args['per_page'];
			$wpdb_table     = \sanitize_text_field( \wp_unslash( $parsed_args['wpdb_table'] ) );
			$orderby        = \sanitize_text_field( \wp_unslash( $parsed_args['orderby'] ) );
			$order          = \sanitize_text_field( \wp_unslash( $parsed_args['order'] ) );
			$hook_type      = \sanitize_text_field( \wp_unslash( $parsed_args['hook_type'] ) );
			$trigger_source = \sanitize_text_field( \wp_unslash( $parsed_args['trigger_source'] ) );
			$group_filter   = (int) $parsed_args['group_filter'];
			$date_from      = \sanitize_text_field( \wp_unslash( $parsed_args['date_from'] ) );
			$date_to        = \sanitize_text_field( \wp_unslash( $parsed_args['date_to'] ) );

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			$where_sql_parts = array();
			$where_args      = array();

			// Search filter.
			if ( ! empty( $search_string ) ) {
				$where_sql_parts[] = '(hook_name LIKE %s OR trigger_source LIKE %s OR user_login LIKE %s)';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
			}

			// Hook type filter.
			if ( ! empty( $hook_type ) && 'all' !== $hook_type ) {
				$where_sql_parts[] = 'hook_type = %s';
				$where_args[]      = $hook_type;
			}

			// Trigger source filter.
			if ( ! empty( $trigger_source ) && 'all' !== $trigger_source ) {
				$where_sql_parts[] = 'trigger_source = %s';
				$where_args[]      = $trigger_source;
			}

			// Group filter.
			if ( ! empty( $group_filter ) && $group_filter > 0 ) {
				$hooks_management_results = Hooks_Management_Entity::get_results( $wpdb->prepare( 'SELECT id FROM `' . Hooks_Management_Entity::get_table_name() . '` WHERE group_id = %d', $group_filter ) );
				if ( ! empty( $hooks_management_results ) ) {
					$hooks_management_ids = array_column( $hooks_management_results, 'id' );
					$placeholders         = implode( ',', array_fill( 0, count( $hooks_management_ids ), '%d' ) );
					$where_sql_parts[]    = 'hooks_management_id IN (' . $placeholders . ')';
					$where_args           = array_merge( $where_args, $hooks_management_ids );
				}
			}

			// Date from filter.
			if ( ! empty( $date_from ) ) {
				$date_from_timestamp = strtotime( $date_from . ' 00:00:00' );
				if ( false !== $date_from_timestamp ) {
					$where_sql_parts[] = 'date_added >= %d';
					$where_args[]      = $date_from_timestamp;
				}
			}

			// Date to filter.
			if ( ! empty( $date_to ) ) {
				$date_to_timestamp = strtotime( $date_to . ' 23:59:59' );
				if ( false !== $date_to_timestamp ) {
					$where_sql_parts[] = 'date_added <= %d';
					$where_args[]      = $date_to_timestamp;
				}
			}

			$where_sql = '';
			if ( ! empty( $where_sql_parts ) ) {
				$where_sql = ' WHERE ' . implode( ' AND ', $where_sql_parts );
			}

			// Count query.
			$count_sql = 'SELECT COUNT(*) FROM `' . $wpdb_table . '`' . $where_sql;

			if ( ! empty( $where_args ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->count = (int) Hooks_Capture_Entity::get_var( $wpdb->prepare( $count_sql, $where_args ) );
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->count = (int) Hooks_Capture_Entity::get_var( $count_sql );
			}

			if ( $parsed_args['count'] ) {
				return array();
			}

			// Main query.
			$sql = 'SELECT * FROM `' . $wpdb_table . '`' . $where_sql . ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';

			$query_args = array_merge( $where_args, array( $per_page, $offset ) );

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results = Hooks_Capture_Entity::get_results( $wpdb->prepare( $sql, $query_args ) );

			return is_array( $results ) ? $results : array();
		}

		/**
		 * Extra table nav controls.
		 *
		 * @param string $which Position of the nav (top/bottom).
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' !== $which ) {
				if ( 'top' !== $which ) {
					?>
				<style>
					<?php
					$groups = Hook_Groups_Entity::get_groups_array();
					foreach ( $groups as $group_id => $group ) {
						echo '.' . \esc_attr( $this->_args['plural'] ) . ' tr.group-' . \esc_attr( $group_id ) . ' th:nth-child(1) { border-left: 7px solid ' . \esc_attr( $group['color'] ) . ' !important;}';
					}
					?>
				</style>
					<?php
				}
				return;
			}

			$hook_type      = isset( $_REQUEST['hook_type'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['hook_type'] ) ) : '';
			$trigger_source = isset( $_REQUEST['trigger_source'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['trigger_source'] ) ) : '';
			$group_filter   = isset( $_REQUEST['group_filter'] ) ? \absint( $_REQUEST['group_filter'] ) : 0;
			$date_from      = isset( $_REQUEST['date_from'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_from'] ) ) : '';
			$date_to        = isset( $_REQUEST['date_to'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['date_to'] ) ) : '';

			?>
			<div class="alignleft actions">
				<select name="hook_type" id="hook_type">
					<option value="all"><?php esc_html_e( 'All Types', '0-day-analytics' ); ?></option>
					<option value="action" <?php selected( $hook_type, 'action' ); ?>><?php esc_html_e( 'Action', '0-day-analytics' ); ?></option>
					<option value="filter" <?php selected( $hook_type, 'filter' ); ?>><?php esc_html_e( 'Filter', '0-day-analytics' ); ?></option>
				</select>

				<select name="trigger_source" id="trigger_source">
					<option value="all"><?php esc_html_e( 'All Sources', '0-day-analytics' ); ?></option>
					<option value="cli" <?php selected( $trigger_source, 'cli' ); ?>><?php esc_html_e( 'CLI', '0-day-analytics' ); ?></option>
					<option value="cron" <?php selected( $trigger_source, 'cron' ); ?>><?php esc_html_e( 'Cron', '0-day-analytics' ); ?></option>
					<option value="ajax" <?php selected( $trigger_source, 'ajax' ); ?>><?php esc_html_e( 'Ajax', '0-day-analytics' ); ?></option>
					<option value="rest" <?php selected( $trigger_source, 'rest' ); ?>><?php esc_html_e( 'REST', '0-day-analytics' ); ?></option>
					<option value="admin" <?php selected( $trigger_source, 'admin' ); ?>><?php esc_html_e( 'Admin', '0-day-analytics' ); ?></option>
					<option value="user" <?php selected( $trigger_source, 'user' ); ?>><?php esc_html_e( 'User', '0-day-analytics' ); ?></option>
					<option value="frontend" <?php selected( $trigger_source, 'frontend' ); ?>><?php esc_html_e( 'Frontend', '0-day-analytics' ); ?></option>
				</select>

				<select name="group_filter" id="group_filter">
					<option value="0"><?php esc_html_e( 'All Groups', '0-day-analytics' ); ?></option>
					<?php
					if ( \class_exists( '\ADVAN\Entities\Hook_Groups_Entity' ) ) {
						$groups = Hook_Groups_Entity::get_groups_array();
						foreach ( $groups as $group_id => $group ) {
							echo '<option value="' . \esc_attr( $group_id ) . '" ' . selected( $group_filter, $group_id, false ) . '>' . \esc_html( $group['name'] ) . '</option>';
						}
					}
					?>
				</select>

				<label for="date_from"><?php esc_html_e( 'From:', '0-day-analytics' ); ?></label>
				<input type="date" name="date_from" id="date_from" value="<?php echo esc_attr( $date_from ); ?>" />

				<label for="date_to"><?php esc_html_e( 'To:', '0-day-analytics' ); ?></label>
				<input type="date" name="date_to" id="date_to" value="<?php echo esc_attr( $date_to ); ?>" />

				<?php submit_button( __( 'Filter', '0-day-analytics' ), 'button', 'filter_action', false ); ?>
			</div>

				<?php if ( $this->count > 0 ) { ?>
				<div id="export-form">
					<div>
						<button id="start-export" class="button" data-type-export="hooks_capture" data-search="<?php echo esc_attr( self::escaped_search_input() ); ?>" data-hook_type="<?php echo esc_attr( $hook_type ); ?>" data-trigger_source="<?php echo esc_attr( $trigger_source ); ?>" data-group_filter="<?php echo esc_attr( $group_filter ); ?>" data-date_from="<?php echo esc_attr( $date_from ); ?>" data-date_to="<?php echo esc_attr( $date_to ); ?>" data-batch-size="500">
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
				<?php } ?>
			<?php
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public function get_bulk_actions() {
			return array(
				'delete' => \esc_html__( 'Delete', '0-day-analytics' ),
			);
		}

		/**
		 * Get the screen per page title for screen options.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		private static function get_screen_per_page_title(): string {
			return __( 'Number of captured hooks to show', '0-day-analytics' );
		}

		/**
		 * Manage columns.
		 *
		 * @param array $columns - Array of column names.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function manage_columns( $columns ): array {
			$admin_fields = Hooks_Capture_Entity::get_column_names_admin();

			$screen_options = $admin_fields;

			$table_columns = array(
				'cb' => '<input type="checkbox" />', // to display the checkbox.
			);

			if ( empty( self::$admin_columns ) ) {
				self::$admin_columns = \array_merge( $table_columns, $screen_options, $admin_fields );
			}

			return self::$admin_columns;
		}

		/**
		 * Render the checkbox column.
		 *
		 * @param array $item Row data.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		protected function column_cb( $item ) {
			$id    = isset( $item['id'] ) ? (int) $item['id'] : 0;
			$table = self::$table::get_name();
			return sprintf(
				'<label class="screen-reader-text" for="%1$s_%2$d">%3$s</label><input type="checkbox" name="id[]" id="%1$s_%2$d" value="%2$d" />',
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
		 * Default column rendering.
		 *
		 * @param array  $item        Row data.
		 * @param string $column_name Column name.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'date_added':
					$time_format = 'g:i a';

					$item[ $column_name ] = (int) $item[ $column_name ];

					$event_datetime_utc = \gmdate( 'Y-m-d H:i:s', $item[ $column_name ] );

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
						\esc_attr( gmdate( 'c', $item[ $column_name ] ) ),
						\esc_html( $date )
					);

					$until = $item[ $column_name ] - time();

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
						);
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
					);

				case 'blog_id':
					if ( \is_multisite() && ! empty( $item[ $column_name ] ) ) {
						$site = \get_site( $item[ $column_name ] );
						if ( $site ) {
							return sprintf(
								'<a href="%s" target="_blank">%d - %s</a>',
								\esc_url( \get_admin_url( $item[ $column_name ] ) ),
								(int) $item[ $column_name ],
								\esc_html( $site->blogname )
							);
						}
					}
					return isset( $item[ $column_name ] ) ? \esc_html( $item[ $column_name ] ) : '';

				case 'hook_name':
					$id         = absint( $item['id'] );
					$hook_label = Hooks_Management_Entity::get_hook_label( $item[ $column_name ] );
					$hook_name  = '<code>' . \esc_html( $item[ $column_name ] ) . '</code>';

					// Make hook name a link to hooks management if hooks_management_id is available.
					if ( ! empty( $item['hooks_management_id'] ) ) {
						$edit_url  = \network_admin_url( 'admin.php?page=advan_hooks_management&action=edit&id=' . absint( $item['hooks_management_id'] ) );
						$hook_name = '<code><a href="' . \esc_url( $edit_url ) . '" title="' . \esc_attr__( 'Edit hook in Hooks Management', '0-day-analytics' ) . '">' . \esc_html( $item[ $column_name ] ) . '</a></code>';
					}

					// Check for post-related hooks and add post type information.
					$post_type_info = '';
					if ( self::is_post_related_hook( $item[ $column_name ] ) && ! empty( $item['parameters'] ) ) {
						$post_type = self::extract_post_type_from_parameters( $item['parameters'] );
						if ( $post_type ) {
							$post_type_info = ' <code style="font-weight: normal;">(<b>' . \esc_html( $post_type ) . '</b>)</code>';
						}
					}

					$display = $hook_label ? '<strong>' . \esc_html( $hook_label ) . $post_type_info . '</strong> ' . $hook_name : $hook_name;

					// Add row actions.
					$actions = array(
						'view'   => sprintf(
							'<a class="aadvana-hook-view" href="#" data-details-id="%d">%s</a>',
							$id,
							__( 'View Details', '0-day-analytics' )
						),
						'delete' => sprintf(
							'<a href="?page=%s&action=delete&id=%d&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>',
							esc_attr( self::HOOKS_CAPTURE_MENU_SLUG ),
							$id,
							\wp_create_nonce( 'delete_capture_' . $id ),
							esc_js( __( 'Are you sure you want to delete this capture log?', '0-day-analytics' ) ),
							__( 'Delete', '0-day-analytics' )
						),
					);

					// Add disable hook action if applicable.
					$actions = self::add_disable_hook_action( $actions, $item );

					return sprintf(
						'%s %s',
						$display,
						$this->row_actions( $actions )
					);

				case 'hook_type':
					return '<span class="badge badge-' . ( 'action' === $item[ $column_name ] ? 'success' : 'info' ) . '">' . \esc_html( ucfirst( $item[ $column_name ] ) ) . '</span>';

				case 'execution_time':
					return isset( $item[ $column_name ] ) ? \esc_html( number_format( (float) $item[ $column_name ], 6 ) ) : '';

				case 'memory_usage':
					return isset( $item[ $column_name ] ) ? \esc_html( size_format( (int) $item[ $column_name ] ) ) : '';

				case 'is_cli':
					return ! empty( $item[ $column_name ] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';

				case 'count':
					$count = isset( $item[ $column_name ] ) ? (int) $item[ $column_name ] : 1;
					if ( $count > 1 ) {
						return '<span class="badge badge-warning">' . \esc_html( $count ) . '</span>';
					}
					return \esc_html( $count );

				case 'parameters':
					return Hook_Parameter_Renderer::render_parameters(
						$item['hook_name'],
						isset( $item['parameters'] ) ? $item['parameters'] : ''
					);
				default:
					// Default rendering for any other columns.
					return isset( $item[ $column_name ] ) ? \esc_html( $item[ $column_name ] ) : '';
			}
		}

		/**
		 * Handle table actions.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public function handle_table_actions() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}

			$action = $this->current_action();

			if ( 'delete' === $action ) {
				// Handle single delete from row actions.
				if ( isset( $_REQUEST['id'] ) && ! is_array( $_REQUEST['id'] ) ) {
					$id    = absint( $_REQUEST['id'] );
					$nonce = isset( $_REQUEST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

					if ( ! \wp_verify_nonce( $nonce, 'delete_capture_' . $id ) ) {
						return;
					}

					if ( $id ) {
						Hooks_Capture_Entity::delete_by_id( $id );
						\wp_safe_redirect( \add_query_arg( array( 'deleted' => 1 ), \remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) ) );
						exit;
					}
				} else {
					// Handle bulk delete.
					$nonce = isset( $_REQUEST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

					if ( ! \wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
						return;
					}

					$ids = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
					$ids = array_map( 'absint', $ids );

					if ( ! empty( $ids ) ) {
						foreach ( $ids as $id ) {
							Hooks_Capture_Entity::delete_by_id( $id );
						}

						\wp_safe_redirect( \add_query_arg( array( 'deleted' => count( $ids ) ), \wp_get_referer() ) );
						exit;
					}
				}
			}
		}

		/**
		 * Generates content for a single row of the table with group-based border coloring.
		 *
		 * @param object|array $item - The current item.
		 *
		 * @since 4.5.0
		 */
		public function single_row( $item ) {
			$classes = '';
			if ( isset( $item['hooks_management_id'] ) && ! empty( $item['hooks_management_id'] ) ) {
				$hooks_management_record = Hooks_Management_Entity::load( 'id=%d', array( (int) $item['hooks_management_id'] ) );
				if ( $hooks_management_record && isset( $hooks_management_record['group_id'] ) && ! empty( $hooks_management_record['group_id'] ) ) {
					$classes .= ' group-' . (int) $hooks_management_record['group_id'];
				}
			}
			echo '<tr class="' . \esc_attr( $classes ) . '">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		/**
		 * Check if a hook is post-related.
		 *
		 * @param string $hook_name The hook name to check.
		 *
		 * @return bool True if post-related, false otherwise.
		 *
		 * @since 4.6.1
		 */
		private static function is_post_related_hook( string $hook_name ): bool {
			$post_related_hooks = array(
				'wp_insert_post',
				'wp_update_post',
				'wp_delete_post',
				'save_post',
				'publish_post',
				'transition_post_status',
				'before_delete_post',
				'after_delete_post',
				'post_updated',
				'edit_post',
				'delete_post',
			);

			return in_array( $hook_name, $post_related_hooks, true );
		}

		/**
		 * Extract post type from hook parameters.
		 *
		 * @param string $parameters_json JSON-encoded parameters.
		 *
		 * @return string|null Post type if found, null otherwise.
		 *
		 * @since 4.6.1
		 */
		private static function extract_post_type_from_parameters( string $parameters_json ): ?string {
			if ( empty( $parameters_json ) ) {
				return null;
			}

			$parameters = json_decode( $parameters_json, true );
			if ( ! is_array( $parameters ) || empty( $parameters ) ) {
				return null;
			}

			// Try different parameter positions and structures.
			foreach ( $parameters as $param ) {
				// Check if parameter is an array/object with post_type.
				if ( is_array( $param ) && isset( $param['post_type'] ) ) {
					return $param['post_type'];
				}

				// Check if parameter is an object with post_type property.
				if ( is_array( $param ) && isset( $param['__class__'] ) && isset( $param['post_type'] ) ) {
					return $param['post_type'];
				}

				// Check if parameter is a post ID and try to get post type from database.
				if ( is_numeric( $param ) && $param > 0 ) {
					$post = \get_post( (int) $param );
					if ( $post && isset( $post->post_type ) ) {
						return $post->post_type;
					}
				}
			}

			return null;
		}

		/**
		 * =======================================================================
		 * NEW FEATURES: Clear All Logs Button & Disable Hook Actions
		 * =======================================================================
		 */

		/**
		 * Initialize admin hooks for new features.
		 *
		 * @return void
		 */
		public static function init_admin_hooks() {
			// Clear logs functionality.
			\add_action( 'admin_notices', array( __CLASS__, 'render_clear_logs_button' ) );
			\add_action( 'admin_post_clear_hooks_logs', array( __CLASS__, 'handle_clear_logs' ) );

			// Disable/enable hook functionality.
			\add_action( 'admin_post_disable_hook_capture', array( __CLASS__, 'handle_disable_hook' ) );
			\add_action( 'admin_post_enable_hook_capture', array( __CLASS__, 'handle_enable_hook' ) );
		}

		/**
		 * Render the clear logs button in admin notices.
		 *
		 * @return void
		 */
		public static function render_clear_logs_button() {
			$screen = \get_current_screen();

			if ( ! $screen || ! \in_array( $screen->id, array( '0-day_page_advan_hooks_capture' ), true ) ) {
				return;
			}

			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}

			$logs_count = self::get_logs_count();
			if ( 0 === $logs_count ) {
				return;
			}

			?>
			<div class="notice">
				<p>
					<strong><?php \esc_html_e( 'Hooks Capture', '0-day-analytics' ); ?></strong>
					<?php
					printf(
						/* translators: %d: number of logs */
						\esc_html__( 'Currently tracking %d hook executions.', '0-day-analytics' ),
						\number_format_i18n( $logs_count )
					);
					?>
					<a href="<?php echo \esc_url( \wp_nonce_url( \network_admin_url( 'admin-post.php?action=clear_hooks_logs' ), 'clear_hooks_logs' ) ); ?>"
						class="button button-secondary"
						onclick="return confirm('<?php \esc_attr_e( 'Are you sure you want to clear all hook logs? This action cannot be undone.', '0-day-analytics' ); ?>')">
						<?php \esc_html_e( 'Clear All Logs', '0-day-analytics' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		/**
		 * Handle the clear logs action.
		 *
		 * @return void
		 */
		public static function handle_clear_logs() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
			}

			\check_admin_referer( 'clear_hooks_logs' );

			try {
				// Use the proper architectural method to truncate the table.
				Common_Table::truncate_table( null, Hooks_Capture_Entity::get_table_name() );

				// Clear any cached data.
				\wp_cache_flush();

				// Add success message.
				\add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-success is-dismissible">
						<p><?php \esc_html_e( 'All hook logs have been cleared successfully.', '0-day-analytics' ); ?></p>
					</div>
						<?php
					}
				);
			} catch ( \Exception $e ) {
				// Add error message.
				\add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-error is-dismissible">
						<p><?php \esc_html_e( 'Failed to clear hook logs. Please try again.', '0-day-analytics' ); ?></p>
					</div>
						<?php
					}
				);
			}

			// Redirect back to the hooks capture page.
			\wp_redirect( \network_admin_url( 'admin.php?page=advan_hooks_capture' ) );
			exit;
		}

		/**
		 * Get the total count of logs.
		 *
		 * @return int
		 */
		private static function get_logs_count() {
			// Use the proper architectural method through the entity class.
			return Hooks_Capture_Entity::count( '1=%d', array( 1 ) );
		}

		/**
		 * Add disable/enable hook action to row actions.
		 *
		 * @param array $actions Existing actions.
		 * @param array $item    The current item.
		 * @return array Modified actions.
		 */
		public static function add_disable_hook_action( $actions, $item ) {
			if ( ! empty( $item['hooks_management_id'] ) && \current_user_can( 'manage_options' ) ) {
				// Load the hook configuration to check if it's enabled or disabled.
				$hook_config = Hooks_Management_Entity::load( 'id=%d', array( $item['hooks_management_id'] ) );
				if ( ! $hook_config ) {
					return $actions;
				}

				$is_enabled = isset( $hook_config['enabled'] ) ? (bool) $hook_config['enabled'] : true;

				if ( $is_enabled ) {
					// Hook is enabled, show disable action.
					$action_url = \wp_nonce_url(
						\network_admin_url( 'admin-post.php?action=disable_hook_capture&id=' . \absint( $item['hooks_management_id'] ) ),
						'disable_hook_capture_' . $item['hooks_management_id']
					);

					$actions['disable_hook'] = \sprintf(
						'<a href="%s" onclick="return confirm(\'%s\')" style="color: #dc3232;">%s</a>',
						\esc_url( $action_url ),
						\esc_js( __( 'Are you sure you want to disable this hook? It will stop being captured.', '0-day-analytics' ) ),
						__( 'Disable Hook', '0-day-analytics' )
					);
				} else {
					// Hook is disabled, show enable action.
					$action_url = \wp_nonce_url(
						\network_admin_url( 'admin-post.php?action=enable_hook_capture&id=' . \absint( $item['hooks_management_id'] ) ),
						'enable_hook_capture_' . $item['hooks_management_id']
					);

					$actions['enable_hook'] = \sprintf(
						'<a href="%s" onclick="return confirm(\'%s\')" style="color: #007cba;">%s</a>',
						\esc_url( $action_url ),
						\esc_js( __( 'Are you sure you want to enable this hook? It will start being captured again.', '0-day-analytics' ) ),
						__( 'Enable Hook', '0-day-analytics' )
					);
				}
			}

			return $actions;
		}

		/**
		 * Handle disable hook action.
		 *
		 * @return void
		 */
		public static function handle_disable_hook() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
			}

			$hook_id = isset( $_GET['id'] ) ? \absint( $_GET['id'] ) : 0;
			if ( ! $hook_id ) {
				\wp_die( \esc_html__( 'Invalid hook ID.', '0-day-analytics' ) );
			}

			\check_admin_referer( 'disable_hook_capture_' . $hook_id );

			// Load the hook configuration.
			$hook_config = Hooks_Management_Entity::load( 'id=%d', array( $hook_id ) );
			if ( ! $hook_config ) {
				\wp_die( \esc_html__( 'Hook not found.', '0-day-analytics' ) );
			}

			// Disable the hook by setting enabled to 0.
			$result = Hooks_Management_Entity::insert( \array_merge( $hook_config, array( 'enabled' => 0 ) ) );

			if ( $result ) {
				// Clear cache to reflect changes.
				\do_action( 'advan_hooks_management_updated' );

				\add_action(
					'admin_notices',
					function() use ( $hook_config ) {
						?>
					<div class="notice notice-success is-dismissible">
						<p>
							<?php
							printf(
								/* translators: %s: hook name */
								\esc_html__( 'Hook "%s" has been disabled successfully.', '0-day-analytics' ),
								\esc_html( $hook_config['hook_name'] )
							);
							?>
						</p>
					</div>
						<?php
					}
				);
			} else {
				\add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-error is-dismissible">
						<p><?php \esc_html_e( 'Failed to disable hook. Please try again.', '0-day-analytics' ); ?></p>
					</div>
						<?php
					}
				);
			}

			\wp_redirect( \network_admin_url( 'admin.php?page=advan_hooks_capture' ) );
			exit;
		}

		/**
		 * Handle enable hook action.
		 *
		 * @return void
		 */
		public static function handle_enable_hook() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
			}

			$hook_id = isset( $_GET['id'] ) ? \absint( $_GET['id'] ) : 0;
			if ( ! $hook_id ) {
				\wp_die( \esc_html__( 'Invalid hook ID.', '0-day-analytics' ) );
			}

			\check_admin_referer( 'enable_hook_capture_' . $hook_id );

			// Load the hook configuration.
			$hook_config = Hooks_Management_Entity::load( 'id=%d', array( $hook_id ) );
			if ( ! $hook_config ) {
				\wp_die( \esc_html__( 'Hook not found.', '0-day-analytics' ) );
			}

			// Enable the hook by setting enabled to 1.
			$result = Hooks_Management_Entity::insert( \array_merge( $hook_config, array( 'enabled' => 1 ) ) );

			if ( $result ) {
				// Clear cache to reflect changes.
				\do_action( 'advan_hooks_management_updated' );

				\add_action(
					'admin_notices',
					function() use ( $hook_config ) {
						?>
					<div class="notice notice-success is-dismissible">
						<p>
							<?php
							printf(
								/* translators: %s: hook name */
								\esc_html__( 'Hook "%s" has been enabled successfully.', '0-day-analytics' ),
								\esc_html( $hook_config['hook_name'] )
							);
							?>
						</p>
					</div>
						<?php
					}
				);
			} else {
				\add_action(
					'admin_notices',
					function() {
						?>
					<div class="notice notice-error is-dismissible">
						<p><?php \esc_html_e( 'Failed to enable hook. Please try again.', '0-day-analytics' ); ?></p>
					</div>
						<?php
					}
				);
			}

			\wp_redirect( \network_admin_url( 'admin.php?page=advan_hooks_capture' ) );
			exit;
		}
	}
}
