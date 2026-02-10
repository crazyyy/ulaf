<?php
/**
 * Responsible for the hooks management view
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
use ADVAN\Controllers\Hooks_Capture;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Entities\Hooks_Management_Entity;
use ADVAN\Entities\Hook_Groups_Entity;
use ADVAN\Lists\Views\Hooks_Management_View;

/**
 * Hooks management list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Hooks_Management_List' ) ) {

	/**
	 * Responsible for rendering hooks management table
	 *
	 * @since 4.5.0
	 */
	class Hooks_Management_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_hooks_management';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_hooks_management_list';

		public const SEARCH_INPUT = 's';

		public const HOOKS_MANAGEMENT_MENU_SLUG = 'advan_hooks_management';

		public const SAVE_ACTION = 'advan_hook_save';

		public const TOGGLE_ACTION = 'advan_hook_toggle';

		public const MENU_SLUG = 'advan_hooks_management';

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
		protected static $entity = Hooks_Management_Entity::class;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $default_order_by = 'hook_name';

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 4.5.0
		 */
		public function __construct( string $table_name = '' ) {

			$class = Common_Table::class;

			Common_Table::init( Hooks_Management_Entity::get_table_name() );
			self::$table = $class;

			parent::__construct(
				array(
					'plural'   => Hooks_Management_Entity::get_table_name(),
					'singular' => Hooks_Management_Entity::get_table_name(),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Inits class hooks.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function init() {
			\add_action( 'admin_post_' . self::SAVE_ACTION, array( Hooks_Management_View::class, 'save_hook' ) );
			\add_action( 'admin_post_' . self::TOGGLE_ACTION, array( Hooks_Management_View::class, 'toggle_hook' ) );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function menu_add() {
			$hooks_mgmt_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Hooks Management', '0-day-analytics' ),
				'manage_options',
				self::HOOKS_MANAGEMENT_MENU_SLUG,
				array( Hooks_Management_View::class, 'analytics_hooks_management_page' ),
				8
			);

			self::add_screen_options( $hooks_mgmt_hook );

			\add_filter( 'manage_' . $hooks_mgmt_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $hooks_mgmt_hook, array( Settings::class, 'aadvana_common_help' ) );
			\add_action( 'load-' . $hooks_mgmt_hook, array( self::class, 'process_actions_load' ) );
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

			$orderby = ( isset( $_GET['orderby'] ) && '' !== trim( $_GET['orderby'] ) ) ? \esc_sql( \wp_unslash( $_GET['orderby'] ) ) : 'hook_name';
			$order   = ( isset( $_GET['order'] ) && '' !== trim( $_GET['order'] ) ) ? \esc_sql( \wp_unslash( $_GET['order'] ) ) : 'ASC';

			// Filter by category.
			$category = '';
			if ( isset( $_REQUEST['category'] ) && ! empty( $_REQUEST['category'] ) ) {
				$category = \sanitize_text_field( \wp_unslash( $_REQUEST['category'] ) );
			}

			// Filter by enabled status.
			$enabled = '';
			if ( isset( $_REQUEST['enabled'] ) && '' !== $_REQUEST['enabled'] ) {
				$enabled = \sanitize_text_field( \wp_unslash( $_REQUEST['enabled'] ) );
			}

			$items = $this->fetch_table_data(
				array(
					'search_string' => $search_string,
					'offset'        => $offset,
					'per_page'      => $per_page,
					'wpdb_table'    => $wpdb_table,
					'orderby'       => $orderby,
					'order'         => $order,
					'category'      => $category,
					'enabled'       => $enabled,
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
			$columns = array_keys( Hooks_Management_Entity::get_column_names_admin() );

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
			\esc_html_e( 'No hooks configured. Add hooks to start capturing.', '0-day-analytics' );
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
					'offset'        => 0,
					'search_string' => self::escaped_search_input(),
					'per_page'      => self::get_screen_option_per_page(),
					'wpdb_table'    => $this->get_table_name(),
					'orderby'       => 'hook_name',
					'order'         => 'ASC',
					'count'         => false,
					'category'      => '',
					'enabled'       => '',
				)
			);

			$search_string = \sanitize_text_field( \wp_unslash( $parsed_args['search_string'] ) );
			$offset        = (int) $parsed_args['offset'];
			$per_page      = (int) $parsed_args['per_page'];
			$wpdb_table    = \sanitize_text_field( \wp_unslash( $parsed_args['wpdb_table'] ) );
			$orderby       = \sanitize_text_field( \wp_unslash( $parsed_args['orderby'] ) );
			$order         = \sanitize_text_field( \wp_unslash( $parsed_args['order'] ) );
			$category      = \sanitize_text_field( \wp_unslash( $parsed_args['category'] ) );
			$enabled       = \sanitize_text_field( \wp_unslash( $parsed_args['enabled'] ) );

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			$where_sql_parts = array();
			$where_args      = array();

			// Search filter.
			if ( ! empty( $search_string ) ) {
				$where_sql_parts[] = '(hook_name LIKE %s OR hook_label LIKE %s OR description LIKE %s)';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
				$where_args[]      = '%' . $wpdb->esc_like( $search_string ) . '%';
			}

			// Category filter.
			if ( ! empty( $category ) && 'all' !== $category ) {
				$where_sql_parts[] = 'category = %s';
				$where_args[]      = $category;
			}

			// Enabled filter.
			if ( '' !== $enabled && 'all' !== $enabled ) {
				$where_sql_parts[] = 'enabled = %d';
				$where_args[]      = (int) $enabled;
			}

			$where_sql = '';
			if ( ! empty( $where_sql_parts ) ) {
				$where_sql = ' WHERE ' . implode( ' AND ', $where_sql_parts );
			}

			// Count query.
			$count_sql = 'SELECT COUNT(*) FROM `' . $wpdb_table . '`' . $where_sql;

			if ( ! empty( $where_args ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->count = (int) Hooks_Management_Entity::get_var( $wpdb->prepare( $count_sql, $where_args ) );
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->count = (int) Hooks_Management_Entity::get_var( $count_sql );
			}

			if ( $parsed_args['count'] ) {
				return array();
			}

			// Main query - show enabled hooks first.
			$sql = 'SELECT * FROM `' . $wpdb_table . '`' . $where_sql . ' ORDER BY enabled DESC, ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';

			$query_args = array_merge( $where_args, array( $per_page, $offset ) );

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results = Hooks_Management_Entity::get_results( $wpdb->prepare( $sql, $query_args ), ARRAY_A );

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
				return;
			}

			$category = isset( $_REQUEST['category'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['category'] ) ) : '';
			$enabled  = isset( $_REQUEST['enabled'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['enabled'] ) ) : '';

			?>
			<div class="alignleft actions">
				<select name="category" id="category">
					<option value="all"><?php esc_html_e( 'All Categories', '0-day-analytics' ); ?></option>
					<option value="auth" <?php selected( $category, 'auth' ); ?>><?php esc_html_e( 'Authentication', '0-day-analytics' ); ?></option>
					<option value="user" <?php selected( $category, 'user' ); ?>><?php esc_html_e( 'User', '0-day-analytics' ); ?></option>
					<option value="post" <?php selected( $category, 'post' ); ?>><?php esc_html_e( 'Post', '0-day-analytics' ); ?></option>
					<option value="update" <?php selected( $category, 'update' ); ?>><?php esc_html_e( 'Update', '0-day-analytics' ); ?></option>
					<option value="core" <?php selected( $category, 'core' ); ?>><?php esc_html_e( 'Core', '0-day-analytics' ); ?></option>
					<option value="custom" <?php selected( $category, 'custom' ); ?>><?php esc_html_e( 'Custom', '0-day-analytics' ); ?></option>
				</select>

				<select name="enabled" id="enabled">
					<option value="all"><?php esc_html_e( 'All Status', '0-day-analytics' ); ?></option>
					<option value="1" <?php selected( $enabled, '1' ); ?>><?php esc_html_e( 'Enabled', '0-day-analytics' ); ?></option>
					<option value="0" <?php selected( $enabled, '0' ); ?>><?php esc_html_e( 'Disabled', '0-day-analytics' ); ?></option>
				</select>

				<?php submit_button( __( 'Filter', '0-day-analytics' ), 'button', 'filter_action', false ); ?>

				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::HOOKS_MANAGEMENT_MENU_SLUG . '&action=new' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add New Hook', '0-day-analytics' ); ?>
				</a>

				<?php if ( $this->count > 0 ) : ?>
				<div id="export-form" style="display:inline-block; margin-left:12px;">
					<div>
						<button id="start-export" class="button" data-type-export="hooks_management" data-search="<?php echo esc_attr( self::escaped_search_input() ); ?>" data-category="<?php echo esc_attr( $category ); ?>" data-enabled="<?php echo esc_attr( $enabled ); ?>" data-batch-size="500">
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
				<?php endif; ?>
			</div>
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
			$actions = array(
				'delete'  => \esc_html__( 'Delete', '0-day-analytics' ),
				'enable'  => \esc_html__( 'Enable', '0-day-analytics' ),
				'disable' => \esc_html__( 'Disable', '0-day-analytics' ),
			);

			// Add group assignment actions.
			$groups = Hook_Groups_Entity::get_groups_array();
			if ( ! empty( $groups ) ) {
				foreach ( $groups as $group_id => $group ) {
					$actions[ 'assign_group_' . $group_id ] = sprintf(
						/* translators: %s: Group name */
						\esc_html__( 'Assign to group: %s', '0-day-analytics' ),
						$group['name']
					);
				}
			}

			return $actions;
		}

		/**
		 * Calculate contrast color (black or white) for given background color.
		 *
		 * @param string $hex_color Hex color code.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		private static function get_contrast_color( string $hex_color ): string {
			// Remove # if present.
			$hex_color = ltrim( $hex_color, '#' );

			// Convert to RGB.
			$r = hexdec( substr( $hex_color, 0, 2 ) );
			$g = hexdec( substr( $hex_color, 2, 2 ) );
			$b = hexdec( substr( $hex_color, 4, 2 ) );

			// Calculate luminance.
			$luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

			// Return black for light backgrounds, white for dark backgrounds.
			return $luminance > 0.5 ? '#000000' : '#ffffff';
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

			// Handle toggle from row action (enable/disable single hook).
			if ( 'toggle' === $action && isset( $_REQUEST['id'] ) && ! is_array( $_REQUEST['id'] ) ) {
				$id    = absint( $_REQUEST['id'] );
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! \wp_verify_nonce( $nonce, 'toggle_hook_' . $id ) ) {
					return;
				}

				if ( $id ) {
					Hooks_Management_Entity::toggle_enabled( $id );
					Hooks_Capture::clear_cache();
					Hooks_Management_Entity::clear_hook_labels_cache();
					\do_action( 'advan_hooks_management_updated' );

					\wp_safe_redirect( \add_query_arg( array( 'updated' => 1 ), \remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) ) );
					exit;
				}
			}

			// Handle delete from row action (not bulk).
			if ( 'delete' === $action && isset( $_REQUEST['id'] ) && ! is_array( $_REQUEST['id'] ) ) {
				$id    = absint( $_REQUEST['id'] );
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! \wp_verify_nonce( $nonce, 'delete_hook_' . $id ) ) {
					return;
				}

				if ( $id ) {
					Hooks_Management_Entity::delete_by_id( $id );
					Hooks_Capture::clear_cache();
					Hooks_Management_Entity::clear_hook_labels_cache();
					\do_action( 'advan_hooks_management_updated' );

					\wp_safe_redirect( \add_query_arg( array( 'updated' => 1 ), \remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) ) );
					exit;
				}
			}

			// Handle bulk actions.
			if ( in_array( $action, array( 'delete', 'enable', 'disable' ), true ) || ( is_string( $action ) && strpos( $action, 'assign_group_' ) === 0 ) ) {
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! \wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
					return;
				}

				$ids = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
				$ids = array_map( 'absint', $ids );

				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {
						if ( 'delete' === $action ) {
							Hooks_Management_Entity::delete_by_id( $id );
						} elseif ( 'enable' === $action ) {
							Hooks_Management_Entity::set_enabled( $id, true );
						} elseif ( 'disable' === $action ) {
							Hooks_Management_Entity::set_enabled( $id, false );
						} elseif ( is_string( $action ) && strpos( $action, 'assign_group_' ) === 0 ) {
							// Extract group ID from action.
							$group_id = str_replace( 'assign_group_', '', $action );
							$group_id = absint( $group_id );

							// Update the group_id for this hook.
							Hooks_Management_Entity::set_group_id( $id, $group_id );
						}
					}

					// Clear cache and reload hooks.
					Hooks_Capture::clear_cache();
						Hooks_Management_Entity::clear_hook_labels_cache();
					\do_action( 'advan_hooks_management_updated' );

					\wp_safe_redirect( \add_query_arg( array( 'updated' => count( $ids ) ), \wp_get_referer() ) );
					exit;
				}
			}
		}

		/**
		 * Get the screen per page title for screen options.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		private static function get_screen_per_page_title(): string {
			return __( 'Number of hooks to show', '0-day-analytics' );
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
			$admin_fields = Hooks_Management_Entity::get_column_names_admin();

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
		 * Render hook_name column with row actions.
		 *
		 * @param array $item Row data.
		 *
		 * @return string
		 *
		 * @since 4.5.0
		 */
		public function column_hook_name( $item ) {
			$id = absint( $item['id'] );

			$actions = array(
				'edit'   => sprintf(
					'<a href="?page=%s&action=edit&id=%d">%s</a>',
					esc_attr( self::HOOKS_MANAGEMENT_MENU_SLUG ),
					$id,
					__( 'Edit', '0-day-analytics' )
				),
				'toggle' => sprintf(
					'<a href="?page=%s&action=toggle&id=%d&_wpnonce=%s">%s</a>',
					esc_attr( self::HOOKS_MANAGEMENT_MENU_SLUG ),
					$id,
					\wp_create_nonce( 'toggle_hook_' . $id ),
					! empty( $item['enabled'] ) ? __( 'Disable', '0-day-analytics' ) : __( 'Enable', '0-day-analytics' )
				),
				'delete' => sprintf(
					'<a href="?page=%s&action=delete&id=%d&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>',
					esc_attr( self::HOOKS_MANAGEMENT_MENU_SLUG ),
					$id,
					\wp_create_nonce( 'delete_hook_' . $id ),
					esc_js( __( 'Are you sure you want to delete this hook?', '0-day-analytics' ) ),
					__( 'Delete', '0-day-analytics' )
				),
			);

			return sprintf(
				'<strong>%s</strong> %s',
				\esc_html( $item['hook_name'] ),
				$this->row_actions( $actions )
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
				case 'hook_label':
					return isset( $item[ $column_name ] ) && '' !== $item[ $column_name ] ? \esc_html( $item[ $column_name ] ) : '<span style="color: #999;">&mdash;</span>';
				case 'hook_type':
					$badge_class = 'action' === $item[ $column_name ] ? 'badge-primary' : 'badge-info';
					return '<span class="badge ' . $badge_class . '">' . \esc_html( $item[ $column_name ] ) . '</span>';

				case 'enabled':
					return $item[ $column_name ] ? '<span class="dashicons dashicons-yes" style="color: green;"></span>' : '<span class="dashicons dashicons-no" style="color: red;"></span>';

				case 'capture_args':
				case 'capture_output':
					return $item[ $column_name ] ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';

				case 'priority':
					return '<code>' . \esc_html( $item[ $column_name ] ) . '</code>';

				case 'category':
					$categories = array(
						'core'   => __( 'WordPress Core', '0-day-analytics' ),
						'plugin' => __( 'Plugin', '0-day-analytics' ),
						'theme'  => __( 'Theme', '0-day-analytics' ),
						'custom' => __( 'Custom', '0-day-analytics' ),
					);
					$label      = isset( $categories[ $item[ $column_name ] ] ) ? $categories[ $item[ $column_name ] ] : $item[ $column_name ];
					return \esc_html( $label );

				case 'date_added':
				case 'date_modified':
					$time_field = $column_name;

					$time_format = 'g:i a';

					$item[ $time_field ] = (int) $item[ $time_field ];

					$event_datetime_utc = \gmdate( 'Y-m-d H:i:s', $item[ $time_field ] );

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
						\esc_attr( gmdate( 'c', $item[ $time_field ] ) ),
						\esc_html( $date )
					);

					$until = $item[ $time_field ] - time();

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

				case 'group_id':
					$group_id = (int) $item[ $column_name ];
					if ( $group_id > 0 && \class_exists( '\ADVAN\Entities\Hook_Groups_Entity' ) ) {
						$group = \ADVAN\Entities\Hook_Groups_Entity::get_group( $group_id );
						if ( $group ) {
							return sprintf(
								'<span class="hook-group-badge" style="background-color: %s; color: %s; padding: 2px 6px; border-radius: 3px; font-size: 11px;">%s</span>',
								\esc_attr( $group['color'] ),
								\esc_attr( self::get_contrast_color( $group['color'] ) ),
								\esc_html( $group['name'] )
							);
						}
					}
					return '<span class="hook-group-badge" style="background-color: #f0f0f0; color: #666; padding: 2px 6px; border-radius: 3px; font-size: 11px;">' . \esc_html__( 'No Group', '0-day-analytics' ) . '</span>';

				default:
					return isset( $item[ $column_name ] ) ? \esc_html( $item[ $column_name ] ) : '';
			}
		}
	}
}
