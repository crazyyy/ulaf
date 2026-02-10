<?php
/**
 * Responsible for the table view
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
use ADVAN\Lists\Views\Table_View;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Entities_Global\Common_Table;


/**
 * Base list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Table_List' ) ) {

	/**
	 * Responsible for rendering base table for manipulation
	 *
	 * @since 2.1.0
	 */
	class Table_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_table';

		public const SWITCH_ACTION = 'switch_advan_table';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_table_list';

		public const SEARCH_INPUT = 's';

		public const TABLE_MENU_SLUG = 'advan_table';

		public const UPDATE_ACTION = 'advan_table_update';

		public const INSERT_ACTION = 'advan_table_insert';

		public const NONCE_NAME = 'advana_table_manager';

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
		 * The entity class related to the list
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $entity = null;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $default_order_by = null;

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 2.1.0
		 */
		public function __construct( string $table_name ) {

			$class = Common_Table::class;

			Common_Table::init( $table_name );
			self::$table = $class;

			// \add_filter( 'manage_' . WP_Helper::get_wp_screen()->id . '_columns', array( $class, 'manage_columns' ) );

			parent::__construct(
				array(
					'plural'   => $table_name,    // Plural value used for labels and the objects being listed.
					'singular' => $table_name,     // Singular label for an object being listed, e.g. 'post'.
					'ajax'     => false,      // If true, the parent class will call the _js_vars() method in the footer.
				)
			);
		}

		/**
		 * Inits the module hooks.
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function hooks_init() {
			\add_action( 'admin_post_' . self::SWITCH_ACTION, array( Table_View::class, 'switch_action' ) );
			\add_action( 'load-' . self::PAGE_SLUG, array( Table_View::class, 'page_load' ) );
			\add_action( 'admin_post_' . self::UPDATE_ACTION, array( Table_View::class, 'update_table' ) );
			\add_action( 'admin_post_' . self::INSERT_ACTION, array( Table_View::class, 'insert_table' ) );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function menu_add() {

			$table_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Table viewer', '0-day-analytics' ),
				( ( Settings::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
				self::TABLE_MENU_SLUG,
				array( Table_View::class, 'analytics_table_page' ),
				5
			);

			self::add_screen_options( $table_hook );

			// \add_filter( 'manage_' . $table_hook . '_columns', array( Table_List::class, 'manage_columns' ) );

			// Process bulk/table actions early on page load before any output.
			\add_action( 'load-' . $table_hook, array( __CLASS__, 'process_actions_load' ), 5 );

			\add_action( 'load-' . $table_hook, array( Settings::class, 'aadvana_common_help' ) );
		}

		/**
		 * Runs table actions during the load-<hook> phase to avoid premature output.
		 *
		 * @return void
		 *
		 * @since 4.2.0
		 */
		public static function process_actions_load() {
			if ( ! \is_user_logged_in() || ! \current_user_can( 'manage_options' ) ) {
				return;
			}

			$table_name      = Common_Table::get_default_table();
			$requested_table = isset( $_REQUEST['show_table'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['show_table'] ) ) : '';
			if ( $requested_table && \in_array( $requested_table, Common_Table::get_tables(), true ) ) {
				$table_name = $requested_table;
			}

			// Instantiate and process actions. Redirects will exit before any output.
			$table = new self( $table_name );
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

			$per_page = self::get_screen_option_per_page();

			$current_page = $this->get_pagenum();
			if ( 1 < $current_page ) {
				$offset = $per_page * ( $current_page - 1 );
			} else {
				$offset = 0;
			}

			$wpdb_table = $this->get_table_name();

			// Validate and allowlist orderby/order from request.
			$valid_columns = array_keys( self::$table::get_column_names_admin() );
			$orderby       = self::$table::get_real_id_name();
			if ( isset( $_GET['orderby'] ) && '' !== trim( (string) $_GET['orderby'] ) ) {
				$requested_orderby = sanitize_key( \wp_unslash( (string) $_GET['orderby'] ) );
				if ( in_array( $requested_orderby, $valid_columns, true ) ) {
					$orderby = $requested_orderby;
				}
			}

			$order = 'ASC';
			if ( isset( $_GET['order'] ) && '' !== trim( (string) $_GET['order'] ) ) {
				$requested_order = strtoupper( (string) \sanitize_text_field( \wp_unslash( $_GET['order'] ) ) );
				if ( in_array( $requested_order, array( 'ASC', 'DESC' ), true ) ) {
					$order = $requested_order;
				}
			}

			$items = $this->fetch_table_data(
				array(
					'search_string' => self::escaped_search_input(),
					'offset'        => $offset,
					'per_page'      => $per_page,
					'wpdb_table'    => $wpdb_table,
					'orderby'       => $orderby,
					'order'         => $order,
				)
			);

			$columns = self::$table::manage_columns( array() );
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
			return self::$table::manage_columns( array() );
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
			$first6_columns   = array_keys( self::$table::get_column_names_admin() );
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
					'orderby'       => self::$table::get_real_id_name(),
					'order'         => 'DESC',
					'count'         => false,
				)
			);

			self::$entity           = self::$table;
			self::$default_order_by = self::$table::get_real_id_name();

			$search_string = (string) \sanitize_text_field( \wp_unslash( $parsed_args['search_string'] ) );
			$offset        = max( 0, (int) $parsed_args['offset'] );
			$per_page      = max( 1, (int) $parsed_args['per_page'] );
			$wpdb_table    = (string) \sanitize_text_field( \wp_unslash( $parsed_args['wpdb_table'] ) );

			// Allowlist orderby and order.
			$valid_columns   = array_keys( self::$table::get_column_names_admin() );
			$default_orderby = self::$table::get_real_id_name();
			$orderby         = in_array( (string) $parsed_args['orderby'], $valid_columns, true ) ? sanitize_key( \wp_unslash( (string) $parsed_args['orderby'] ) ) : $default_orderby;
			$order           = strtoupper( (string) \sanitize_text_field( \wp_unslash( (string) $parsed_args['order'] ) ) );
			$order           = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

			if ( ! Common_Table::check_table_exists( $wpdb_table ) ) {
				$this->count = 0;
				return array();
			}

			$search_sql = '';

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			if ( '' !== $search_string ) {
				$like           = '%' . $wpdb->esc_like( $search_string ) . '%';
				$search_parts   = array();
				$search_parts[] = $wpdb->prepare( self::$table::get_real_id_name() . ' LIKE %s', $like );
				foreach ( array_keys( self::$table::get_column_names_admin() ) as $value ) {
					$search_parts[] = $wpdb->prepare( "{$value} LIKE %s", $like );
				}
				$search_sql = 'AND (' . implode( ' OR ', $search_parts ) . ') ';
			}

			$query = 'SELECT
				' . implode( ', ', self::$table::get_column_names() ) . '
			  FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql . ' ORDER BY ' . $orderby . ' ' . $order;

			$query .= $wpdb->prepare( ' LIMIT %d OFFSET %d;', $per_page, $offset );

			// query output_type will be an associative array with ARRAY_A.
			$query_results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$this->count = (int) $wpdb->get_var( 'SELECT COUNT(' . self::$table::get_real_id_name() . ') FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

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
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				default:
					return $this->common_column_render( $item, $column_name );
			}
		}

		/**
		 * Responsible for common column rendering
		 *
		 * @param array  $item - The current riw with data.
		 * @param string $column_name - The column name.
		 *
		 * @return string
		 *
		 * @since 2.1.0
		 */
		private function common_column_render( array $item, $column_name ): string {

			if ( $column_name === self::$table::get_real_id_name() ) {
				$actions = array();

				// View action is non-destructive; show to all viewers of this screen.
				$actions['view'] = '<a class="aadvana-tablerow-view" href="#" data-details-id="' . \esc_attr( (string) $item[ self::$table::get_real_id_name() ] ) . '">' . \esc_html__( 'View', '0-day-analytics' ) . '</a>';

				if ( \current_user_can( 'manage_options' ) ) {
					$query_args_view_data             = array();
					$query_args_view_data['_wpnonce'] = \wp_create_nonce( 'bulk-' . $this->_args['plural'] );

					$delete_url =
						\add_query_arg(
							array(
								'action'           => 'delete',
								'advan_' . self::$table::get_name() => $item[ self::$table::get_real_id_name() ],
								self::SEARCH_INPUT => self::escaped_search_input(),
								'_wpnonce'         => $query_args_view_data['_wpnonce'],
							)
						);

					$actions['delete'] = '<a class="aadvana-transient-delete" href="' . \esc_url( $delete_url ) . '" onclick="return confirm(\'' . \esc_html__( 'You sure you want to delete this record?', '0-day-analytics' ) . '\');">' . \esc_html__( 'Delete', '0-day-analytics' ) . '</a>';

					$edit_url = \remove_query_arg(
						array( 'updated', 'deleted' ),
						\add_query_arg(
							array(
								'action'           => 'edit_table_data',
								'id'               => $item[ self::$table::get_real_id_name() ],
								self::SEARCH_INPUT => self::escaped_search_input(),
								'_wpnonce'         => \wp_create_nonce( 'edit-row' ),
								'show_table'       => self::$table::get_name(),
							)
						)
					);

					$actions['edit'] = '<a class="aadvana-table-edit" href="' . \esc_url( $edit_url ) . '">' . \esc_html__( 'Edit', '0-day-analytics' ) . '</a>';
				}

				$row_value = \esc_html( $item[ $column_name ] ) . $this->row_actions( $actions );

			} else {
				$len   = \mb_strlen( (string) $item[ $column_name ] );
				$value = \mb_substr( (string) $item[ $column_name ], 0, 100 );

				// . '[&hellip;]'

				// Escape & wrap in <code> tag.
				$row_value = '<code>' . \esc_html( $value ) . ( ( 100 < $len ) ? '[&hellip;]' : '' ) . '</code>';

				// $row_value = \esc_html( $item[ $column_name ] );
			}

			return $row_value;
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
			return sprintf(
				'<label class="screen-reader-text" for="' . self::$table::get_name() . '_' . $item[ self::$table::get_real_id_name() ] . '">' . sprintf(
					// translators: The column name.
					__( 'Select %s', '0-day-analytics' ),
					self::$table::get_real_id_name()
				) . '</label>'
				. '<input type="checkbox" name="advan_' . self::$table::get_name() . '[]" id="' . self::$table::get_name() . '_' . $item[ self::$table::get_real_id_name() ] . '" value="' . $item[ self::$table::get_real_id_name() ] . '" />'
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
							array( 'delete', '_wpnonce', 'advan_' . self::$table::get_name() ),
							\add_query_arg(
								array(
									self::SEARCH_INPUT => self::escaped_search_input(),
									'paged'            => $_REQUEST['paged'] ?? 1,
									'page'             => self::TABLE_MENU_SLUG,
									'show_table'       => self::$table::get_name(),
								),
								\network_admin_url( 'admin.php' )
							)
						);

					\wp_safe_redirect( $redirect );
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

			?>

			<div class="alignleft actions bulkactions">
				
				<select id="table_filter_<?php echo \esc_attr( $which ); ?>" class="table_filter" name="table_filter_<?php echo \esc_attr( $which ); ?>" class="advan-filter-table" style="font-family: dashicons;">
					<?php
					foreach ( Common_Table::get_tables() as $table ) {
						$selected = '';
						if ( self::$table::get_name() === $table ) {
							$selected = ' selected="selected"';
						}
						$core_table = '';
						if ( in_array( $table, Common_Table::get_wp_core_tables(), true ) ) {
							$core_table = ' ';
						}
						?>
						<option <?php echo $selected; ?> value="<?php echo \esc_attr( $table ); ?>" style="font-family: dashicons;"><?php echo \esc_html( $core_table . $table ); ?></option>
						<?php
					}
					?>
					
				</select>
				<?php if ( 'top' === $which ) : ?>
				<button id="table-info-btn" class="button button-secondary" title="<?php esc_attr_e( 'View table information', '0-day-analytics' ); ?>">
					<span class="dashicons dashicons-info"></span>
					<?php esc_html_e( 'Info', '0-day-analytics' ); ?>
				</button>
				<?php endif; ?>
				
			</div>
			<?php
			if ( 'top' === $which && $this->count > 0 ) {
				?>
				<div id="export-form">
					<div>
						<button id="start-export" class="button" data-type-export="table" data-table-name="<?php echo \esc_attr( self::$table::get_name() ); ?>" data-search="<?php echo \esc_attr( self::escaped_search_input() ); ?>">
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

			// Table info modal - only for top navigation
			if ( 'top' === $which ) :
			?>
			<div id="table-info-modal" class="table-info-modal" style="display: none;">
				<div class="table-info-modal-content">
					<div class="table-info-modal-header">
						<h3><?php printf( esc_html__( 'Table Information: %s', '0-day-analytics' ), esc_html( self::$table::get_name() ) ); ?></h3>
						<button type="button" class="table-info-modal-close">&times;</button>
					</div>
					<div class="table-info-modal-body">
						<div class="table-info-loading">
							<span class="spinner is-active"></span>
							<?php esc_html_e( 'Loading table information...', '0-day-analytics' ); ?>
						</div>
						<div class="table-info-content" style="display: none;">
							<div class="table-info-section">
								<h4><?php esc_html_e( 'Table Structure', '0-day-analytics' ); ?></h4>
								<div class="table-structure-info"></div>
							</div>
							<div class="table-info-section">
								<h4><?php esc_html_e( 'Indexes', '0-day-analytics' ); ?></h4>
								<div class="table-indexes-info"></div>
							</div>
							<div class="table-info-section">
								<h4><?php esc_html_e( 'Foreign Keys', '0-day-analytics' ); ?></h4>
								<div class="table-foreign-keys-info"></div>
							</div>
							<div class="table-info-section">
								<h4><?php esc_html_e( 'Table Health', '0-day-analytics' ); ?></h4>
								<div class="table-health-info"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php
			// if ( 'top' === $which ) {
			global $wpdb;
			?>
			<script>
				jQuery('form .table_filter').on('change', function(e) {
					jQuery('form .table_filter').val(jQuery(this).val());
					jQuery( this ).closest( 'form' ).attr( 'action', '<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>').append('<input type="hidden" name="action" value="<?php echo \esc_attr( self::SWITCH_ACTION ); ?>">').append('<input type="hidden" name="context" value="<?php echo \esc_attr( ( \is_network_admin() ) ? 'network' : 'site' ); ?>">').append('<?php \wp_nonce_field( self::SWITCH_ACTION, self::SWITCH_ACTION . 'nonce' ); ?>').submit();
				});

				function makeSearchableDropdown(selectEl) {
					selectEl.style.display = "none";

					const container = document.createElement("div");
					container.className = "dropdown-container";

					const input = document.createElement("input");
					input.className = "dropdown-search";
					input.placeholder = "<?php \esc_html_e( 'Search tables...', '0-day-analytics' ); ?>";
					input.type = "search";
					input.style.fontFamily = "dashicons";

					const clearBtn = document.createElement("button");
					clearBtn.className = "clear-btn";
					clearBtn.type = "button";
					//clearBtn.textContent = "✕";

					const list = document.createElement("div");
					list.className = "dropdown-list";

					container.appendChild(input);
					container.appendChild(clearBtn);
					container.appendChild(list);
					selectEl.parentNode.insertBefore(container, selectEl.nextSibling);

					const options = Array.from(selectEl.options);
					let filteredOptions = options;
					let activeIndex = -1;

					// --- Measure dropdown width based on longest option ---
					const measurer = document.createElement("span");
					measurer.className = "text-measurer";
					document.body.appendChild(measurer);
					measurer.style.font = getComputedStyle(input).font;

					let maxWidth = 0;
					options.forEach(opt => {
						measurer.textContent = opt.text;
						maxWidth = Math.max(maxWidth, measurer.offsetWidth);
					});
					measurer.remove();

					const inputWidth = input.offsetWidth;
					const dropdownWidth = Math.max(maxWidth + 30, inputWidth);
					list.style.width = dropdownWidth + "px";

					// --- Fill input with current selected value ---
					const selectedOption = selectEl.options[selectEl.selectedIndex];
					if (selectedOption && selectedOption.text) {
						input.value = selectedOption.text;
						clearBtn.style.display = "block";
					}

					// --- Clear button logic ---
					clearBtn.addEventListener("click", () => {
						input.value = "";
						// selectEl.value = "";
						clearBtn.style.display = "none";
						// const event = new Event("change", { bubbles: true });
						// selectEl.dispatchEvent(event);
						renderList("");
						input.focus();
					});

					// --- Rendering dropdown list ---
					function renderList(filter = "") {
						list.innerHTML = "";
						filteredOptions = options.filter(opt =>
						opt.text.toLowerCase().includes(filter.toLowerCase())
						);

						filteredOptions.forEach((opt, i) => {
						const item = document.createElement("div");
						item.textContent = opt.text;
						item.className = "dropdown-item";
						if (i === activeIndex) item.classList.add("active");
						item.addEventListener("mousedown", e => {
							e.preventDefault();
							selectOption(opt);
						});
						list.appendChild(item);
						});

						list.style.display = filteredOptions.length ? "block" : "none";
						if (activeIndex >= 0) scrollActiveIntoView();
					}

					// --- Handle selection ---
					function selectOption(opt) {
						input.value = opt.text;
						selectEl.value = opt.value;
						list.style.display = "none";
						clearBtn.style.display = opt.value ? "block" : "none";
						input.focus();
						const event = new Event("change", { bubbles: true });
						selectEl.dispatchEvent(event);
					}

					function moveActive(delta) {
						if (!filteredOptions.length) return;
						activeIndex = (activeIndex + delta + filteredOptions.length) % filteredOptions.length;
						renderList(input.value);
					}

					function scrollActiveIntoView() {
						const activeItem = list.querySelector(".active");
						if (activeItem) {
						const listRect = list.getBoundingClientRect();
						const itemRect = activeItem.getBoundingClientRect();
						if (itemRect.bottom > listRect.bottom) {
							list.scrollTop += itemRect.bottom - listRect.bottom;
						} else if (itemRect.top < listRect.top) {
							list.scrollTop -= listRect.top - itemRect.top;
						}
						}
					}

					// --- Input events ---
					input.addEventListener("input", e => {
						activeIndex = -1;
						renderList(e.target.value);
						clearBtn.style.display = e.target.value ? "block" : "none";
					});

					input.addEventListener("focus", () => {
						activeIndex = -1;
						renderList(input.value);
					});

					input.addEventListener("keydown", e => {
						if (list.style.display === "none" && !["ArrowDown", "ArrowUp"].includes(e.key)) return;
						switch (e.key) {
						case "ArrowDown":
							e.preventDefault();
							moveActive(1);
							break;
						case "ArrowUp":
							e.preventDefault();
							moveActive(-1);
							break;
						case "Enter":
							e.preventDefault();
							if (activeIndex >= 0 && filteredOptions[activeIndex]) {
							selectOption(filteredOptions[activeIndex]);
							}
							break;
						case "Escape":
							list.style.display = "none";
							break;
						}
					});

					document.addEventListener("click", e => {
						if (!container.contains(e.target)) list.style.display = "none";
					});
				}

				// --- Initialize ---
				makeSearchableDropdown(document.getElementById("table_filter_<?php echo \esc_attr( $which ); ?>"));

				<?php if ( 'top' === $which ) { ?>
				// Table info modal functionality
				const tableInfoBtn = document.getElementById('table-info-btn');
				const tableInfoModal = document.getElementById('table-info-modal');
				const tableInfoClose = document.querySelector('.table-info-modal-close');

				if (tableInfoBtn && tableInfoModal) {
					tableInfoBtn.addEventListener('click', function(e) {
						e.preventDefault();
						loadTableInfo();
						tableInfoModal.style.display = 'block';
					});

					tableInfoClose.addEventListener('click', function() {
						tableInfoModal.style.display = 'none';
					});

					window.addEventListener('click', function(e) {
						if (e.target === tableInfoModal) {
							tableInfoModal.style.display = 'none';
						}
					});
				}

				function loadTableInfo() {
					const loadingEl = document.querySelector('.table-info-loading');
					const contentEl = document.querySelector('.table-info-content');

					loadingEl.style.display = 'block';
					contentEl.style.display = 'none';

					fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'aadvana_get_table_info',
							table_name: '<?php echo esc_js(self::$table::get_name()); ?>',
							security: '<?php echo wp_create_nonce('get_table_info'); ?>'
						})
					})
					.then(response => response.json())
					.then(data => {
						loadingEl.style.display = 'none';
						if (data.success) {
							displayTableInfo(data.data);
							contentEl.style.display = 'block';
						} else {
							alert('<?php esc_html_e('Error loading table information', '0-day-analytics'); ?>: ' + data.data);
						}
					})
					.catch(error => {
						loadingEl.style.display = 'none';
						alert('<?php esc_html_e('Error loading table information', '0-day-analytics'); ?>: ' + error.message);
					});
				}

				function displayTableInfo(info) {
					// Table Structure
					const structureEl = document.querySelector('.table-structure-info');
					if (info.structure && info.structure.length > 0) {
						let html = '<table class="widefat striped"><thead><tr><th><?php esc_html_e('Column', '0-day-analytics'); ?></th><th><?php esc_html_e('Type', '0-day-analytics'); ?></th><th><?php esc_html_e('Null', '0-day-analytics'); ?></th><th><?php esc_html_e('Key', '0-day-analytics'); ?></th><th><?php esc_html_e('Default', '0-day-analytics'); ?></th><th><?php esc_html_e('Extra', '0-day-analytics'); ?></th></tr></thead><tbody>';
						info.structure.forEach(col => {
							html += `<tr>
								<td><code>${escapeHtml(col.Field)}</code></td>
								<td><code>${escapeHtml(col.Type)}</code></td>
								<td>${escapeHtml(col.Null)}</td>
								<td>${escapeHtml(col.Key || '')}</td>
								<td>${escapeHtml(col.Default || '')}</td>
								<td>${escapeHtml(col.Extra || '')}</td>
							</tr>`;
						});
						html += '</tbody></table>';
						structureEl.innerHTML = html;
					} else {
						structureEl.innerHTML = '<p><?php esc_html_e('No column information available', '0-day-analytics'); ?></p>';
					}

					// Indexes
					const indexesEl = document.querySelector('.table-indexes-info');
					if (info.indexes && info.indexes.length > 0) {
						let html = '<table class="widefat striped"><thead><tr><th><?php esc_html_e('Key Name', '0-day-analytics'); ?></th><th><?php esc_html_e('Column', '0-day-analytics'); ?></th><th><?php esc_html_e('Unique', '0-day-analytics'); ?></th><th><?php esc_html_e('Type', '0-day-analytics'); ?></th></tr></thead><tbody>';
						info.indexes.forEach(idx => {
							html += `<tr>
								<td><code>${escapeHtml(idx.Key_name)}</code></td>
								<td><code>${escapeHtml(idx.Column_name)}</code></td>
								<td>${idx.Non_unique === '0' ? '<?php esc_html_e('Yes', '0-day-analytics'); ?>' : '<?php esc_html_e('No', '0-day-analytics'); ?>'}</td>
								<td>${escapeHtml(idx.Index_type)}</td>
							</tr>`;
						});
						html += '</tbody></table>';
						indexesEl.innerHTML = html;
					} else {
						indexesEl.innerHTML = '<p><?php esc_html_e('No indexes found', '0-day-analytics'); ?></p>';
					}

					// Foreign Keys
					const fkEl = document.querySelector('.table-foreign-keys-info');
					if (info.foreign_keys && info.foreign_keys.length > 0) {
						let html = '<table class="widefat striped"><thead><tr><th><?php esc_html_e('Constraint', '0-day-analytics'); ?></th><th><?php esc_html_e('Column', '0-day-analytics'); ?></th><th><?php esc_html_e('Referenced Table', '0-day-analytics'); ?></th><th><?php esc_html_e('Referenced Column', '0-day-analytics'); ?></th></tr></thead><tbody>';
						info.foreign_keys.forEach(fk => {
							html += `<tr>
								<td><code>${escapeHtml(fk.constraint_name)}</code></td>
								<td><code>${escapeHtml(fk.column_name)}</code></td>
								<td><code>${escapeHtml(fk.referenced_table_name)}</code></td>
								<td><code>${escapeHtml(fk.referenced_column_name)}</code></td>
							</tr>`;
						});
						html += '</tbody></table>';
						fkEl.innerHTML = html;
					} else {
						fkEl.innerHTML = '<p><?php esc_html_e('No foreign keys found', '0-day-analytics'); ?></p>';
					}

					// Table Health
					const healthEl = document.querySelector('.table-health-info');
					if (info.health) {
						let html = '<ul>';
						if (info.health.needs_optimization) {
							html += `<li><span style="color: #d63638;">⚠️ <?php esc_html_e('Table may benefit from optimization', '0-day-analytics'); ?></span></li>`;
						} else {
							html += `<li><span style="color: #00a32a;">✅ <?php esc_html_e('Table appears to be well optimized', '0-day-analytics'); ?></span></li>`;
						}
						if (info.health.row_count) {
							html += `<li><?php esc_html_e('Row count', '0-day-analytics'); ?>: ${info.health.row_count.toLocaleString()}</li>`;
						}
						if (info.health.data_size) {
							html += `<li><?php esc_html_e('Data size', '0-day-analytics'); ?>: ${formatBytes(info.health.data_size)}</li>`;
						}
						if (info.health.index_size) {
							html += `<li><?php esc_html_e('Index size', '0-day-analytics'); ?>: ${formatBytes(info.health.index_size)}</li>`;
						}
						html += '</ul>';
						healthEl.innerHTML = html;
					} else {
						healthEl.innerHTML = '<p><?php esc_html_e('Health information not available', '0-day-analytics'); ?></p>';
					}
				}

				function escapeHtml(text) {
					const div = document.createElement('div');
					div.textContent = text;
					return div.innerHTML;
				}

				function formatBytes(bytes) {
					if (bytes === 0) return '0 Bytes';
					const k = 1024;
					const sizes = ['Bytes', 'KB', 'MB', 'GB'];
					const i = Math.floor(Math.log(bytes) / Math.log(k));
					return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
				}
				<?php } ?>

			</script>
						<?php
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
						.dropdown-container {
						position: relative;
						display: inline-block;
						font-family: dashicons;
						}

						.dropdown-search {
						width: 200px; /* fixed input width */
						box-sizing: border-box;
						padding: 6px 28px 6px 6px; /* space for clear button */
						font-size: 14px;
						}

						/* 🔘 Clear button styling */
						.clear-btn {
						position: absolute;
						right: 6px;
						top: 50%;
						transform: translateY(-50%);
						width: 18px;
						height: 18px;
						border: 1px solid #ccc;
						border-radius: 50%;
						background: white;
						color: #666;
						font-size: 12px;
						font-weight: bold;
						line-height: 1;
						text-align: center;
						cursor: pointer;
						display: none;
						padding: 0;
						box-shadow: 0 1px 2px rgba(0,0,0,0.1);
						}

						.clear-btn:hover {
						background: #f2f2f2;
						color: #000;
						border-color: #999;
						}

						.clear-btn::before {
						content: "✕";
						top: -3px;
						position: relative;
						font-size: 0.7em;
						color: #9e9898;
						}

						.dropdown-list {
						position: absolute;
						top: 100%;
						left: 0;
						border: 1px solid #ccc;
						border-top: none;
						max-height: 150px;
						overflow-y: auto;
						background: #fff;
						display: none;
						z-index: 100;
						min-width: 100%;
						}

						.dropdown-item {
						padding: 6px;
						cursor: pointer;
						white-space: nowrap;
						}

						.dropdown-item:hover,
						.dropdown-item.active {
						background-color: #0078d4;
						color: #fff;
						}

						.text-measurer {
						position: absolute;
						visibility: hidden;
						white-space: nowrap;
						font-size: 14px;
						font-family: sans-serif;
						left: -9999px;
						top: -9999px;
						}

						/* Table Info Modal Styles */
						.table-info-modal {
						position: fixed;
						z-index: 100000;
						left: 0;
						top: 0;
						width: 100%;
						height: 100%;
						background-color: rgba(0, 0, 0, 0.5);
						}

						.table-info-modal-content {
						background-color: #fefefe;
						margin: 5% auto;
						padding: 0;
						border: 1px solid #888;
						width: 90%;
						max-width: 1000px;
						max-height: 80vh;
						overflow-y: auto;
						border-radius: 8px;
						box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
						}

						.aadvana-darkskin .table-info-modal-content {
						background-color: #1d456b !important;
						border-color: #555;
						}

						.table-info-modal-header {
						padding: 15px 20px;
						/* background: #f8f9fa; */
						border-bottom: 1px solid #dee2e6;
						display: flex;
						justify-content: space-between;
						align-items: center;
						border-radius: 8px 8px 0 0;
						}

						.table-info-modal-header h3 {
						margin: 0;
						/* color: #333; */
						font-size: 18px;
						font-weight: 600;
						}

						.table-info-modal-close {
						background: none;
						border: none;
						font-size: 24px;
						font-weight: bold;
						/* color: #666; */
						cursor: pointer;
						padding: 0;
						width: 30px;
						height: 30px;
						display: flex;
						align-items: center;
						justify-content: center;
						border-radius: 50%;
						transition: background-color 0.2s;
						}

						.table-info-modal-close:hover {
						/* background-color: #e9ecef; */
						/* color: #333; */
						}

						.table-info-modal-body {
						padding: 20px;
						}

						.table-info-loading {
						text-align: center;
						padding: 40px;
						}

						.table-info-loading .spinner {
						float: none;
						margin: 0 auto 10px;
						}

						.table-info-section {
						margin-bottom: 30px;
						}

						.table-info-section h4 {
						margin: 0 0 15px 0;
						padding: 10px 15px;
						/* background: #f8f9fa; */
						border-left: 4px solid #007cba;
						font-size: 16px;
						font-weight: 600;
						/* color: #333; */
						}

						.table-info-section table {
						margin: 0;
						}

						.table-info-section table code {
						/* background: #f8f9fa; */
						padding: 2px 6px;
						border-radius: 3px;
						font-size: 12px;
						/* color: #495057; */
						}

						.table-info-section ul {
						margin: 0;
						padding-left: 20px;
						}

						.table-info-section li {
						margin-bottom: 8px;
						}

						@media (max-width: 768px) {
						.table-info-modal-content {
							margin: 2% auto;
							width: 95%;
							max-height: 90vh;
						}

						.table-info-modal-body {
							padding: 15px;
						}

						.table-info-section table {
							font-size: 12px;
						}

						.table-info-section table th,
						.table-info-section table td {
							padding: 8px 4px;
						}
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
						<b><?php \esc_html_e( 'Schema: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( defined( 'DB_NAME' ) ? DB_NAME : '' ); ?></span> | <b><?php \esc_html_e( 'Tables: ', '0-day-analytics' ); ?></b><span class="italic"><?php echo \esc_attr( count( Common_Table::get_tables() ) ); ?></span>
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
			return __( 'Number of rows to show', '0-day-analytics' );
		}

		/**
		 * Adds columns to the screen options screed.
		 *
		 * @param array $columns - Array of column names.
		 *
		 * @since 1.1.0
		 */
		public static function manage_columns( $columns ): array {

			return self::$table::manage_columns( $columns );
		}
	}
}
