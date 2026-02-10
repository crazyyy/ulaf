<?php
/**
 * Responsible for managing reusable snippets UI.
 *
 * @package advan
 */

declare(strict_types=1);

namespace ADVAN\Lists;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Abstract_List;
use ADVAN\Entities\Snippet_Entity;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Lists\Views\Snippets_View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\\ADVAN\\Lists\\Snippets_List' ) ) {
	/**
	 * Snippets admin list table.
	 *
	 * @package advan
	 *
	 * @extends \WP_List_Table<array>
	 *
	 * @uses List_Trait
	 *
	 * @since 4.3.0
	 */
	class Snippets_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_snippets';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_snippets_list';

		public const SEARCH_INPUT = 's';

		public const MENU_SLUG = 'advan_snippets';

		public const NONCE_NAME = 'advan_manage_snippets';

		public const SAVE_ACTION = 'advan_snippet_save';

		public const DELETE_ACTION = 'advan_snippet_delete';

		public const EXECUTE_ACTION = 'advan_snippet_execute';

		public const TRASH_ACTION = 'advan_snippet_trash';

		public const RESTORE_ACTION = 'advan_snippet_restore';

		public const CLONE_ACTION = 'advan_snippet_clone';

		private const BULK_FIELD = 'aadvana_snippet_ids';

		/**
		 * Default rows per page.
		 *
		 * @var int
		 */
		protected static $rows_per_page = 20;

		/**
		 * Related entity class.
		 *
		 * @var string
		 */
		protected static $entity = Snippet_Entity::class;

		/**
		 * Default order by column.
		 *
		 * @var string
		 */
		protected static $default_order_by = 'updated_at';

		/**
		 * Cached admin columns map.
		 *
		 * @var array
		 */
		protected static $admin_columns = array();

		/**
		 * Cached items count used by parent table class.
		 *
		 * @var int
		 */
		protected $count = 0;

		/**
		 * Constructor.
		 */
		public function __construct() {
			Snippet_Entity::create_table();

			parent::__construct(
				array(
					'plural'   => Snippet_Entity::get_table_name(),
					'singular' => 'aadvana_snippet',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Register admin-post endpoints.
		 */
		public static function init(): void {
			\add_action( 'admin_post_' . self::SAVE_ACTION, array( Snippets_View::class, 'handle_save' ) );
			\add_action( 'admin_post_' . self::DELETE_ACTION, array( Snippets_View::class, 'handle_delete' ) );
			\add_action( 'admin_post_' . self::EXECUTE_ACTION, array( Snippets_View::class, 'handle_execute' ) );
			\add_action( 'admin_post_' . self::TRASH_ACTION, array( Snippets_View::class, 'handle_trash' ) );
			\add_action( 'admin_post_' . self::RESTORE_ACTION, array( Snippets_View::class, 'handle_restore' ) );
			\add_action( 'admin_post_' . self::CLONE_ACTION, array( Snippets_View::class, 'handle_clone' ) );
		}

		/**
		 * Adds snippets submenu under plugin menu.
		 */
		public static function menu_add(): void {
			$hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				\__( 'Snippets', '0-day-analytics' ),
				\__( 'Snippets', '0-day-analytics' ),
				'manage_options',
				self::MENU_SLUG,
				array( Snippets_View::class, 'render_page' ),
				6
			);

			self::add_screen_options( $hook );
			\add_filter( 'manage_' . $hook . '_columns', array( __CLASS__, 'manage_columns' ) );
			\add_action( 'load-' . $hook, array( Settings::class, 'aadvana_common_help' ) );
			\add_action( 'load-' . $hook, array( __CLASS__, 'process_actions_load' ) );
			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_code_editor' ) );
		}

		/**
		 * Load CodeMirror for snippet form when needed.
		 *
		 * @param string $hook_suffix Current admin page hook suffix.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function maybe_enqueue_code_editor( string $hook_suffix ): void {
			$valid = array( self::PAGE_SLUG, self::PAGE_SLUG . '-network' );
			if ( ! in_array( $hook_suffix, $valid, true ) ) {
				return;
			}

			$action = isset( $_GET['action'] ) ? \sanitize_key( \wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! in_array( $action, array( 'add', 'edit' ), true ) ) {
				return;
			}

			self::enqueue_snippet_assets();

			$settings = \wp_enqueue_code_editor(
				array(
					'type'       => 'text/x-php',
					'codemirror' => array(
						'indentUnit' => 4,
						'tabSize'    => 4,
						'theme'      => 'cobalt',
					),
				)
			);

			if ( false !== $settings ) {
				\wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery(function(){ wp.codeEditor.initialize( "snippet-code", %s ); });',
						\wp_json_encode( $settings )
					)
				);

				\wp_add_inline_style(
					'code-editor',
					'.CodeMirror-wrap { width: 99%; border: 1px solid #8c8f94; border-radius: 3px; overflow: hidden; } .CodeMirror-gutters { background: transparent; }'
				);
			}
		}

		/**
		 * Enqueue form specific assets.
		 */
		private static function enqueue_snippet_assets(): void {
			\wp_enqueue_style(
				'advan-snippets-editor',
				\ADVAN_PLUGIN_ROOT_URL . 'css/admin/snippets-editor.css',
				array(),
				\ADVAN_VERSION
			);

			\wp_enqueue_script(
				'advan-snippets-editor',
				\ADVAN_PLUGIN_ROOT_URL . 'js/admin/snippets-editor.js',
				array( 'jquery' ),
				\ADVAN_VERSION,
				true
			);

			\wp_localize_script(
				'advan-snippets-editor',
				'advanSnippetEditor',
				array(
					'emptyCodeMessage' => \__( 'Please add code before saving.', '0-day-analytics' ),
					'sidebarCollapsed' => \__( 'Expand sidebar', '0-day-analytics' ),
					'sidebarExpanded'  => \__( 'Collapse sidebar', '0-day-analytics' ),
					'storageKey'       => 'advanSnippetSidebarState',
				)
			);
		}

		/**
		 * Process pending table actions early on load.
		 */
		public static function process_actions_load(): void {
			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}

			$table = new self();
			$table->handle_table_actions();
		}

		/**
		 * Prepare list items.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public function prepare_items(): void {
			$search   = self::escaped_search_input();
			$per_page = self::get_screen_option_per_page();
			$orderby  = isset( $_GET['orderby'] ) ? \sanitize_key( \wp_unslash( $_GET['orderby'] ) ) : static::$default_order_by;
			$order    = isset( $_GET['order'] ) ? \sanitize_key( \wp_unslash( $_GET['order'] ) ) : 'DESC';
			$page     = $this->get_pagenum();
			$offset   = $per_page * ( $page - 1 );
			$status   = self::get_status_filter_value();
			$type     = self::get_type_filter_value();

			$data = $this->fetch_table_data(
				array(
					'offset'   => $offset,
					'per_page' => $per_page,
					'orderby'  => $orderby,
					'order'    => $order,
					'search'   => $search,
					'status'   => $status,
					'type'     => $type,
				)
			);

			$this->items = $data['items'];
			$this->count = $data['total'];

			$columns = self::manage_columns( array() );
			$hidden  = \get_user_option( 'manage' . WP_Helper::get_wp_screen()->id . 'columnshidden', false );
			if ( ! $hidden ) {
				$hidden = array();
			}
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->set_pagination_args(
				array(
					'total_items' => $this->count,
					'per_page'    => $per_page,
					'total_pages' => ( $per_page > 0 ) ? (int) ceil( $this->count / $per_page ) : 1,
				)
			);
		}

		/**
		 * Fetch data for current request.
		 *
		 * @param array $args Query arguments.
		 *
		 * @return array{items:array,total:int}
		 *
		 * @since 4.3.0
		 */
		public function fetch_table_data( array $args = array() ) {
			$defaults = array(
				'offset'   => 0,
				'per_page' => self::get_screen_option_per_page(),
				'orderby'  => static::$default_order_by,
				'order'    => 'DESC',
				'search'   => '',
				'status'   => '',
				'type'     => '',
			);
			$args     = \wp_parse_args( $args, $defaults );

			$wpdb     = Snippet_Entity::get_connection();
			$table    = Snippet_Entity::get_table_name( $wpdb );
			$where    = array( 'blog_id = %d' );
			$bindings = array( \get_current_blog_id() );

			if ( '' !== $args['search'] ) {
				$like       = '%' . $wpdb->esc_like( $args['search'] ) . '%';
				$where[]    = '( name LIKE %s OR tags LIKE %s )';
				$bindings[] = $like;
				$bindings[] = $like;
			}

			$status_map = array(
				'enabled'  => Snippet_Entity::STATUS_ENABLED,
				'disabled' => Snippet_Entity::STATUS_DISABLED,
				'trash'    => Snippet_Entity::STATUS_TRASHED,
			);

			if ( isset( $status_map[ $args['status'] ] ) ) {
				$where[]    = 'status = %d';
				$bindings[] = $status_map[ $args['status'] ];
			} else {
				$where[]    = 'status >= %d';
				$bindings[] = Snippet_Entity::STATUS_DISABLED;
			}

			$types = array_keys( Snippet_Entity::get_supported_types() );
			if ( '' !== $args['type'] && in_array( $args['type'], $types, true ) ) {
				$where[]    = 'type = %s';
				$bindings[] = $args['type'];
			}

			$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

			$count_sql = 'SELECT COUNT(*) FROM ' . $table . ' ' . $where_sql;
			$total     = (int) Snippet_Entity::get_var( $wpdb->prepare( $count_sql, $bindings ) );

			$order   = self::get_order( $args['order'] );
			$orderby = self::get_order_by( $args['orderby'] );

			$list_sql   = 'SELECT * FROM ' . $table . ' ' . $where_sql . ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';
			$list_items = Snippet_Entity::get_results(
				$wpdb->prepare(
					$list_sql,
					array_merge( $bindings, array( (int) $args['per_page'], (int) $args['offset'] ) )
				)
			);

			return array(
				'items' => $list_items ?: array(),
				'total' => $total,
			);
		}

		/**
		 * Default column renderer.
		 *
		 * @param array  $item        The current item.
		 * @param string $column_name The name of the column.
		 * @return string Rendered column content.
		 *
		 * @since 4.3.0
		 */
		protected function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'type':
					$types = Snippet_Entity::get_supported_types();
					return isset( $types[ $item['type'] ] ) ? \esc_html( $types[ $item['type'] ] ) : \esc_html( $item['type'] );
				case 'status':
					return $this->format_status_label( (int) $item['status'] );
				case 'execution_scope':
					return $this->format_scope_label( (string) ( $item['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE ) );
				case 'execution_hook':
					$hook = trim( (string) ( $item['execution_hook'] ?? '' ) );
					return '' === $hook ? '&mdash;' : '<code>' . \esc_html( $hook ) . '</code>';
				case 'hook_priority':
					$priority = (int) ( $item['hook_priority'] ?? 10 );
					if ( $priority <= 0 ) {
						$priority = 10;
					}
					return \esc_html( (string) $priority );
				case 'shortcode_tag':
					$tag = trim( (string) ( $item['shortcode_tag'] ?? '' ) );
					return '' === $tag ? '&mdash;' : '<code>[' . \esc_html( $tag ) . ']</code>';
				case 'tags':
					return $this->format_tags( $item['tags'] ?? '' );
				case 'last_run_at':
					return $this->format_timestamp( $item['last_run_at'] );
				case 'last_run_status':
					return \esc_html( $item['last_run_status'] ?? '' );
				case 'updated_at':
					return $this->format_timestamp( $item['updated_at'] );
				case 'blog_id':
					return \esc_html( (string) ( $item['blog_id'] ?? '' ) );
				default:
					return isset( $item[ $column_name ] ) ? \esc_html( (string) $item[ $column_name ] ) : '';
			}
		}

		/**
		 * Status views (All, Enabled, Disabled, Trash).
		 *
		 * @return array<string,string> View links.
		 *
		 * @since 4.3.0
		 */
		public function get_views() {
			$status = self::get_status_filter_value();
			$counts = Snippet_Entity::get_status_counters();
			$views  = array();

			$views['all']      = $this->format_view_link( '', \__( 'All', '0-day-analytics' ), (int) ( $counts['all'] ?? 0 ), $status );
			$views['enabled']  = $this->format_view_link( 'enabled', \__( 'Enabled', '0-day-analytics' ), (int) ( $counts['enabled'] ?? 0 ), $status );
			$views['disabled'] = $this->format_view_link( 'disabled', \__( 'Disabled', '0-day-analytics' ), (int) ( $counts['disabled'] ?? 0 ), $status );
			$views['trash']    = $this->format_view_link( 'trash', \__( 'Trash', '0-day-analytics' ), (int) ( $counts['trashed'] ?? 0 ), $status );

			return $views;
		}

		/**
		 * Helper to format a single view link.
		 *
		 * @param string $value   The status value for the view link.
		 * @param string $label   The label for the view link.
		 * @param int    $count   The count of items for the view.
		 * @param string $current The current status filter value.
		 *
		 * @return string The formatted view link HTML.
		 *
		 * @since 4.3.0
		 */
		private function format_view_link( string $value, string $label, int $count, string $current ): string {
			$classes = array();
			if ( ( '' === $value && '' === $current ) || ( '' !== $value && $value === $current ) ) {
				$classes[] = 'current';
			}

			$args = array();
			if ( '' !== $value ) {
				$args['snippet_status'] = $value;
			}

			$url        = self::get_admin_page_url( $args );
			$label_html = sprintf( '%1$s <span class="count">(%2$s)</span>', \esc_html( $label ), \number_format_i18n( $count ) );

			return sprintf(
				'<a href="%1$s"%2$s>%3$s</a>',
				\esc_url( $url ),
				$classes ? ' class="' . \esc_attr( implode( ' ', $classes ) ) . '"' : '',
				$label_html
			);
		}

		/**
		 * Render name column with row actions.
		 *
		 * @param array $item The current item.
		 *
		 * @return string Rendered column content.
		 *
		 * @since 4.3.0
		 */
		protected function column_name( $item ): string {
			$label      = '<strong>' . \esc_html( $item['name'] ) . '</strong>';
			$is_trashed = Snippet_Entity::STATUS_TRASHED === (int) $item['status'];
			$context    = array();
			$status     = self::get_status_filter_value();
			if ( '' !== $status ) {
				$context['snippet_status'] = $status;
			}

			$actions = array();

			if ( ! $is_trashed ) {
				$edit_url = self::get_admin_page_url(
					array(
						'action'  => 'edit',
						'snippet' => (int) $item['id'],
					)
				);

				$clone_url = \wp_nonce_url(
					\add_query_arg(
						array_merge(
							array(
								'action'  => self::CLONE_ACTION,
								'snippet' => (int) $item['id'],
							),
							$context
						),
						\admin_url( 'admin-post.php' )
					),
					self::NONCE_NAME
				);

				$trash_url = \wp_nonce_url(
					\add_query_arg(
						array_merge(
							array(
								'action'  => self::TRASH_ACTION,
								'snippet' => (int) $item['id'],
							),
							$context
						),
						\admin_url( 'admin-post.php' )
					),
					self::NONCE_NAME
				);

				$actions['edit'] = '<a href="' . \esc_url( $edit_url ) . '">' . \esc_html__( 'Edit', '0-day-analytics' ) . '</a>';
				if ( 'php' === ( $item['type'] ?? '' ) ) {
					$execute_url    = \wp_nonce_url(
						\add_query_arg(
							array(
								'action'  => self::EXECUTE_ACTION,
								'snippet' => (int) $item['id'],
							),
							\admin_url( 'admin-post.php' )
						),
						self::NONCE_NAME
					);
					$actions['run'] = '<a href="' . \esc_url( $execute_url ) . '">' . \esc_html__( 'Execute', '0-day-analytics' ) . '</a>';
				}

				$actions['clone'] = '<a href="' . \esc_url( $clone_url ) . '">' . \esc_html__( 'Duplicate', '0-day-analytics' ) . '</a>';
				$actions['trash'] = '<a class="submitdelete" href="' . \esc_url( $trash_url ) . '">' . \esc_html__( 'Move to Trash', '0-day-analytics' ) . '</a>';
			} else {
				$restore_url = \wp_nonce_url(
					\add_query_arg(
						array_merge(
							array(
								'action'  => self::RESTORE_ACTION,
								'snippet' => (int) $item['id'],
							),
							$context
						),
						\admin_url( 'admin-post.php' )
					),
					self::NONCE_NAME
				);

				$delete_url = \wp_nonce_url(
					\add_query_arg(
						array_merge(
							array(
								'action'  => self::DELETE_ACTION,
								'snippet' => (int) $item['id'],
							),
							$context
						),
						\admin_url( 'admin-post.php' )
					),
					self::NONCE_NAME
				);

				$actions['restore'] = '<a href="' . \esc_url( $restore_url ) . '">' . \esc_html__( 'Restore', '0-day-analytics' ) . '</a>';
				$actions['delete']  = '<a class="submitdelete" href="' . \esc_url( $delete_url ) . '">' . \esc_html__( 'Delete Permanently', '0-day-analytics' ) . '</a>';
			}

			return $label . $this->row_actions( $actions );
		}

		/**
		 * Checkbox column content.
		 *
		 * @param array $item  - The current item.
		 *
		 * @return string Rendered column content.
		 *
		 * @since 4.3.0
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<label class="screen-reader-text" for="snippet-%1$d">%2$s</label><input type="checkbox" name="%3$s[]" id="snippet-%1$d" value="%1$d" />',
				(int) $item['id'],
				\esc_html__( 'Select snippet', '0-day-analytics' ),
				\esc_attr( self::BULK_FIELD )
			);
		}

		/**
		 * Bulk actions map.
		 *
		 * @return array<string,string> Bulk actions.
		 *
		 * @since 4.3.0
		 */
		public function get_bulk_actions() {
			$status = self::get_status_filter_value();
			if ( 'trash' === $status ) {
				return array(
					'bulk-restore' => \__( 'Restore', '0-day-analytics' ),
					'bulk-delete'  => \__( 'Delete Permanently', '0-day-analytics' ),
				);
			}

			return array(
				'bulk-enable'  => \__( 'Enable', '0-day-analytics' ),
				'bulk-disable' => \__( 'Disable', '0-day-analytics' ),
				'bulk-trash'   => \__( 'Move to Trash', '0-day-analytics' ),
			);
		}

		/**
		 * Process bulk delete action.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public function handle_table_actions(): void {
			$action = $this->current_action();
			if ( ! $action ) {
				return;
			}

			if ( ! in_array( $action, array( 'bulk-enable', 'bulk-disable', 'bulk-trash', 'bulk-restore', 'bulk-delete' ), true ) ) {
				return;
			}

			\check_admin_referer( 'bulk-' . $this->_args['plural'] );

			$ids = isset( $_REQUEST[ self::BULK_FIELD ] ) ? array_map( 'intval', (array) $_REQUEST[ self::BULK_FIELD ] ) : array();
			$ids = array_filter( $ids );
			if ( empty( $ids ) ) {
				$this->redirect_back( 'error', array(), \__( 'Select at least one snippet.', '0-day-analytics' ) );
			}

			switch ( $action ) {
				case 'bulk-enable':
					foreach ( $ids as $id ) {
						Snippet_Entity::enable_by_id( $id );
					}
					$this->redirect_back( 'updated', array(), \__( 'Selected snippets enabled.', '0-day-analytics' ) );
					break;
				case 'bulk-disable':
					foreach ( $ids as $id ) {
						Snippet_Entity::disable_by_id( $id );
					}
					$this->redirect_back( 'updated', array(), \__( 'Selected snippets disabled.', '0-day-analytics' ) );
					break;
				case 'bulk-trash':
					foreach ( $ids as $id ) {
						Snippet_Entity::trash_by_id( $id );
					}
					$this->redirect_back( 'trashed', array(), \__( 'Selected snippets moved to trash.', '0-day-analytics' ) );
					break;
				case 'bulk-restore':
					foreach ( $ids as $id ) {
						Snippet_Entity::restore_by_id( $id );
					}
					$this->redirect_back( 'restored', array(), \__( 'Selected snippets restored.', '0-day-analytics' ) );
					break;
				case 'bulk-delete':
					foreach ( $ids as $id ) {
						$snippet = Snippet_Entity::get_snippet( $id );
						if ( Snippet_Entity::is_trashed( $snippet ) ) {
							Snippet_Entity::delete_by_id( $id );
						}
					}
					$this->redirect_back( 'deleted', array(), \__( 'Selected snippets deleted permanently.', '0-day-analytics' ) );
					break;
			}
		}

		/**
		 * Extra filters above table.
		 *
		 * @param string $which 'top' or 'bottom' placement.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' !== $which ) {
				return;
			}
			$status = isset( $_GET['snippet_status'] ) ? \sanitize_key( \wp_unslash( $_GET['snippet_status'] ) ) : '';
			$type   = isset( $_GET['snippet_type'] ) ? \sanitize_key( \wp_unslash( $_GET['snippet_type'] ) ) : '';
			$types  = Snippet_Entity::get_supported_types();
			?>
			<div class="alignleft actions">
				<label class="screen-reader-text" for="snippet-status-filter"><?php \esc_html_e( 'Filter by status', '0-day-analytics' ); ?></label>
				<select name="snippet_status" id="snippet-status-filter">
					<option value=""><?php \esc_html_e( 'All statuses', '0-day-analytics' ); ?></option>
					<option value="enabled" <?php \selected( $status, 'enabled' ); ?>><?php \esc_html_e( 'Enabled', '0-day-analytics' ); ?></option>
					<option value="disabled" <?php \selected( $status, 'disabled' ); ?>><?php \esc_html_e( 'Disabled', '0-day-analytics' ); ?></option>
					<option value="trash" <?php \selected( $status, 'trash' ); ?>><?php \esc_html_e( 'Trash', '0-day-analytics' ); ?></option>
				</select>
				<label class="screen-reader-text" for="snippet-type-filter"><?php \esc_html_e( 'Filter by type', '0-day-analytics' ); ?></label>
				<select name="snippet_type" id="snippet-type-filter">
					<option value=""><?php \esc_html_e( 'All types', '0-day-analytics' ); ?></option>
					<?php foreach ( $types as $key => $label ) : ?>
						<option value="<?php echo \esc_attr( $key ); ?>" <?php \selected( $type, $key ); ?>><?php echo \esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php \submit_button( \__( 'Filter', '0-day-analytics' ), 'secondary', 'filter_action', false ); ?>
			</div>
			<div id="export-form">
				<div>
					<button id="start-export" class="button" data-type-export="snippets" data-search="<?php echo \esc_attr( self::escaped_search_input() ); ?>" data-snippet_status="<?php echo \esc_attr( isset( $_REQUEST['snippet_status'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['snippet_status'] ) ) : '' ); ?>" data-snippet_type="<?php echo \esc_attr( isset( $_REQUEST['snippet_type'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['snippet_type'] ) ) : '' ); ?>">
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

		/**
		 * Sortable columns.
		 *
		 * @return array<string,array> Sortable columns.
		 *
		 * @since 4.3.0
		 */
		public function get_sortable_columns() {
			$columns   = array_keys( Snippet_Entity::get_column_names_admin() );
			$sortable  = array();
			$columns[] = 'created_at';
			$columns[] = 'updated_at';
			foreach ( array_unique( $columns ) as $column ) {
				$sortable[ $column ] = array( $column, false );
			}

			return $sortable;
		}

		/**
		 * Screen-option label text.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_screen_per_page_title(): string {
			return \__( 'Number of snippets to show', '0-day-analytics' );
		}

		/**
		 * Build columns structure for WP_List_Table.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array Modified columns.
		 *
		 * @since 4.3.0
		 */
		public static function manage_columns( $columns ): array {
			if ( empty( self::$admin_columns ) ) {
				$table_columns       = array( 'cb' => '<input type="checkbox" />' );
				self::$admin_columns = array_merge( $table_columns, Snippet_Entity::get_column_names_admin(), $columns );
			}

			return self::$admin_columns;
		}

		/**
		 * Output message when table empty.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public function no_items(): void {
			\esc_html_e( 'No snippets available yet.', '0-day-analytics' );
		}

		/**
		 * Current status filter helper.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_status_filter_value(): string {
			return isset( $_REQUEST['snippet_status'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['snippet_status'] ) ) : '';
		}

		/**
		 * Current type filter helper.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_type_filter_value(): string {
			return isset( $_REQUEST['snippet_type'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['snippet_type'] ) ) : '';
		}

		/**
		 * Helper: format enabled/disabled label.
		 *
		 * @param int $status Status value.
		 *
		 * @return string Formatted status label.
		 *
		 * @since 4.3.0
		 */
		private function format_status_label( int $status ): string {
			if ( Snippet_Entity::STATUS_TRASHED === $status ) {
				return '<span class="status-trashed">' . \esc_html__( 'Trashed', '0-day-analytics' ) . '</span>';
			}

			return $status
				? '<span class="status-enabled">' . \esc_html__( 'Enabled', '0-day-analytics' ) . '</span>'
				: '<span class="status-disabled">' . \esc_html__( 'Disabled', '0-day-analytics' ) . '</span>';
		}

		/**
		 * Helper: format execution scope label.
		 *
		 * @param string $scope Scope slug.
		 *
		 * @return string Human readable scope label.
		 */
		private function format_scope_label( string $scope ): string {
			$scopes = Snippet_Entity::get_execution_scopes();
			return isset( $scopes[ $scope ] ) ? \esc_html( $scopes[ $scope ] ) : \esc_html( ucwords( str_replace( '_', ' ', $scope ) ) );
		}

		/**
		 * Helper: format csv tags.
		 *
		 * @param string $csv Comma separated tags.
		 *
		 * @return string Formatted tags.
		 *
		 * @since 4.3.0
		 */
		private function format_tags( string $csv ): string {
			if ( '' === trim( $csv ) ) {
				return '&mdash;';
			}
			$tags = array_filter( array_map( 'sanitize_text_field', explode( ',', $csv ) ) );
			return \esc_html( implode( ', ', $tags ) );
		}

		/**
		 * Human readable timestamp.
		 *
		 * @param string|null $timestamp Timestamp string.
		 *
		 * @return string Formatted timestamp.
		 *
		 * @since 4.3.0
		 */
		private function format_timestamp( ?string $timestamp ): string {
			if ( empty( $timestamp ) ) {
				return '&mdash;';
			}

			$time = strtotime( $timestamp . ' UTC' );
			if ( ! $time ) {
				return \esc_html( $timestamp );
			}

			$diff = \human_time_diff( $time, \time() );
			return \esc_html(
				sprintf(
					\__( '%s ago', '0-day-analytics' ),
					$diff
				)
			);
		}

		/**
		 * Redirect helper after actions.
		 *
		 * @param string $notice  Notice type.
		 * @param array  $extra   Extra query args.
		 * @param string $message Optional custom message.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private function redirect_back( string $notice, array $extra = array(), string $message = '' ): void {
			$params = array(
				'snippet_notice'   => $notice,
				self::SEARCH_INPUT => self::escaped_search_input(),
			);

			if ( '' === $message && isset( $extra['_msg'] ) ) {
				$message = $extra['_msg'];
				unset( $extra['_msg'] );
			}

			if ( '' !== $message ) {
				$params['_msg'] = \rawurlencode( $message );
			}

			$status_filter = self::get_status_filter_value();
			if ( '' !== $status_filter && ! isset( $extra['snippet_status'] ) ) {
				$params['snippet_status'] = $status_filter;
			}

			if ( ! empty( $extra ) ) {
				$params = array_merge( $params, $extra );
			}

			\wp_safe_redirect( self::get_admin_page_url( $params ) );
			exit;
		}

		/**
		 * Base admin url helper respecting network context.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_admin_page_base_url(): string {
			return \is_network_admin() ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' );
		}

		/**
		 * Generates the table navigation above or below the table
		 *
		 * @param string $which - Holds info about the top and bottom navigation.
		 *
		 * @since latest
		 */
		public function display_tablenav( $which ) {
			if ( 'top' === $which ) {

				?>

				<style>
					.<?php echo \esc_attr( Snippet_Entity::get_table_name() ); ?> .late th:nth-child(1) {
						border-left: 7px solid #dd9192 !important;
					}
					.<?php echo esc_attr( Snippet_Entity::get_table_name() ); ?> .on-time th:nth-child(1) {
						border-left: 7px solid rgb(49, 179, 45) !important;
					}
				</style>
				<?php
			}
			parent::display_tablenav( $which );
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @param object|array $item - The current item.
		 *
		 * @since latest
		 */
		public function single_row( $item ) {
			$late = $item['status'] ?? 0;

			if ( $late ) {
				$classes = ' on-time';
			} else {
				$classes = ' late';
			}

			echo '<tr class="' . \esc_attr( $classes ) . '">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		/**
		 * Build menu url with query args.
		 *
		 * @param array $args Query arguments.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		public static function get_admin_page_url( array $args = array() ): string {
			$defaults = array( 'page' => self::MENU_SLUG );
			return \add_query_arg( array_merge( $defaults, $args ), self::get_admin_page_base_url() );
		}
	}
}
