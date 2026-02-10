<?php
/**
 * Responsible for the Showing the list of the crons.
 *
 * @package    advanced-analytics
 * @subpackage helpers
 *
 * @since 1.1.0
 *
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

declare(strict_types=1);

namespace ADVAN\Lists;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Abstract_List;
use ADVAN\Helpers\Crons_Helper;
use ADVAN\Lists\Views\Crons_View;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Controllers\Api\Endpoints;
use ADVAN\Helpers\Plugin_Theme_Helper;

/*
 * Base list table class
 */
if ( ! class_exists( '\ADVAN\Lists\Crons_List' ) ) {
	/**
	 * Responsible for rendering base table for manipulation.
	 *
	 * @since 1.1.0
	 */
	class Crons_List extends Abstract_List {

		use List_Trait;

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_crons_list';

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_cron_jobs';

		public const UPDATE_ACTION = 'advan_crons_update';

		public const NEW_ACTION = 'advan_crons_new';

		public const NONCE_NAME = 'advana_crons_manager';

		public const SEARCH_INPUT = 'sgp';

		public const CRON_MENU_SLUG = 'advan_cron_jobs';

		public const PLUGIN_FILTER_ACTION = self::PAGE_SLUG . '_filter_plugin';

		public const SITE_FILTER_ACTION = self::PAGE_SLUG . '_filter_site';

		/**
		 * Format for the file link.
		 *
		 * @var string|false|null
		 *
		 * @since 1.4.0
		 */
		private static $file_link_format = null;

		/**
		 * Current screen.
		 *
		 * @var \WP_Screen
		 *
		 * @since 1.1.0
		 */
		protected static $wp_screen;

		/**
		 * Name of the table to show.
		 *
		 * @var string
		 *
		 * @since 1.1.0
		 */
		protected static $table_name;

		/**
		 * How many.
		 *
		 * @var int
		 *
		 * @since 1.1.0
		 */
		protected $count;

		/**
		 * Events Query Arguments.
		 *
		 * @since 1.1.0
		 * @since 1.1.0 Transformed to array
		 *
		 * @var array
		 */
		private static $query_args;

		/**
		 * Holds the current query arguments.
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $query_occ = array();

		/**
		 * Holds the current query order.
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $query_order = array();

		/**
		 * Holds the read lines from error log.
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private static $read_items = null;

		/**
		 * Default class constructor.
		 *
		 * @param stdClass $query_args Events query arguments.
		 *
		 * @since 1.1.0
		 */
		public function __construct( $query_args ) {
			self::$query_args = $query_args;

			parent::__construct(
				array(
					'singular' => 'generated-cron',
					'plural'   => 'generated-crons',
					'ajax'     => true,
					'screen'   => WP_Helper::get_wp_screen(),
				)
			);

			self::$columns = self::manage_columns( array() );

			self::$table_name = 'advanced_crons';
		}

		/**
		 * Inits the module hook.
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function hooks_init() {

			\add_action( 'admin_print_styles-' . self::PAGE_SLUG, array( Settings::class, 'print_styles' ) );
			\add_action( 'admin_post_' . self::UPDATE_ACTION, array( Crons_View::class, 'update_cron' ) );
			\add_action( 'admin_post_' . self::NEW_ACTION, array( Crons_View::class, 'new_cron' ) );
			\add_action( 'admin_post_' . self::PLUGIN_FILTER_ACTION, array( Crons_View::class, 'plugin_filter_action' ) );
			\add_action( 'admin_post_' . self::SITE_FILTER_ACTION, array( Crons_View::class, 'site_filter_action' ) );
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function menu_add() {

			/* Crons */
			$cron_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Crons viewer', '0-day-analytics' ),
				( ( Settings::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
				self::CRON_MENU_SLUG,
				array( Crons_View::class, 'analytics_cron_page' ),
				2
			);

			self::add_screen_options( $cron_hook );

			\add_filter( 'manage_' . $cron_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $cron_hook, array( Settings::class, 'aadvana_common_help' ) );
			// Process actions early to avoid header warnings on redirects.
			\add_action( 'load-' . $cron_hook, array( self::class, 'process_actions_load' ) );

			/* Crons end */
		}

		/**
		 * Handle cron table actions on the early page load hook.
		 *
		 * @return void
		 *
		 * @since 4.2.0
		 */
		public static function process_actions_load() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}
			$table = new self( array() );
			$table->handle_table_actions();
		}

		/**
		 * Displays the search box.
		 *
		 * @since 1.1.0
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {

			if ( empty( $_REQUEST[ self::SEARCH_INPUT ] ) && ! self::are_there_items() ) {
				return;
			}

			$input_id = $input_id . '-search-input';
			?>
			<p class="search-box" style="position:relative">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo \esc_html( $text ); ?>:</label>

				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" class="<?php echo \esc_attr( ADVAN_PREFIX ); ?>search_input" name="<?php echo \esc_attr( self::SEARCH_INPUT ); ?>" value="<?php echo \esc_attr( self::escaped_search_input() ); ?>" />

				<?php \submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>

			<?php
		}

		/**
		 * Adds columns to the screen options screed.
		 *
		 * @param array $columns - Array of column names.
		 *
		 * @since 1.1.0
		 */
		public static function manage_columns( $columns ): array {
			$admin_fields = array(
				'cb'         => '<input type="checkbox" />', // to display the checkbox.
				'hook'       => __( 'Hook', '0-day-analytics' ),
				'schedule'   => esc_html(
					sprintf(
						/* translators: %s: UTC offset */
						__( 'Next Run (%s)', '0-day-analytics' ),
						WP_Helper::get_timezone_location()
					),
				),
				'recurrence' => __( 'Interval', '0-day-analytics' ),
				'args'       => __( 'Args', '0-day-analytics' ),
				'actions'    => __( 'Actions', '0-day-analytics' ),
			);

			// Insert site column for multisite network admins for clarity.
			if ( function_exists( 'is_multisite' ) && is_multisite() && current_user_can( 'manage_network' ) ) {
				$admin_fields = array(
					'cb'         => $admin_fields['cb'],
					'hook'       => $admin_fields['hook'],
					'site'       => __( 'Site', '0-day-analytics' ),
					'schedule'   => $admin_fields['schedule'],
					'recurrence' => $admin_fields['recurrence'],
					'args'       => $admin_fields['args'],
					'actions'    => $admin_fields['actions'],
				);
			}

			$screen_options = $admin_fields;

			return \array_merge( $screen_options, $columns );
		}

		/**
		 * Returns the table name.
		 *
		 * @since 1.1.0
		 */
		public static function get_table_name(): string {
			return self::$table_name;
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
		 *
		 * @since 1.1.0
		 */
		public function prepare_items() {
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->fetch_table_data();

			$hidden = \get_user_option( 'manage' . WP_Helper::get_wp_screen()->id . 'columnshidden', false );
			if ( ! $hidden ) {
				$hidden = array();
			}

			$this->_column_headers = array( self::$columns, $hidden, $sortable );
		}

		/**
		 * Returns the currently hidden column headers for the current user
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function get_hidden_columns() {
			return array_filter(
				(array) \get_user_option( 'manage' . Settings::get_main_menu_page_hook() . 'columnshidden', false )
			);
		}

		/**
		 * Get a list of sortable columns. The format is:
		 * 'internal-name' => 'orderby'
		 * or
		 * 'internal-name' => array( 'orderby', true ).
		 *
		 * The second format will make the initial sorting order be descending
		 *
		 * @since 1.1.0
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return array(
				'hook'       => array( 'hook', false ),
				'schedule'   => array( 'schedule', false, null, null, 'asc' ),
				'recurrence' => array( 'recurrence', false ),
			);
		}

		/**
		 * Text displayed when no user data is available.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		public function no_items() {
			\esc_html_e( 'No crons found', '0-day-analytics' );
		}

		/**
		 * Fetch table data from the WordPress database.
		 *
		 * @param array $args - Arguments for fetching data.
		 *
		 * @since 1.1.0
		 *
		 * @return array
		 */
		public function fetch_table_data( array $args = array() ) {

			$this->items = self::get_cron_items();

			return $this->items;
		}

		/**
		 * Collect cron items.
		 *
		 * @param bool $no_type_filtering - When true, events returned are without type filtering applied.
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 * @since 2.9.0 - Introduced flag getting the events without the type filtering
		 */
		public static function get_cron_items( bool $no_type_filtering = false ): array {

			if ( null === self::$read_items || $no_type_filtering ) {

				self::$read_items = Crons_Helper::get_events();

			}

			if ( isset( $_REQUEST['schedules_filter'] ) && ! empty( $_REQUEST['schedules_filter'] ) ) {

				$s = \sanitize_text_field( \wp_unslash( $_REQUEST['schedules_filter'] ) );
				if ( 'single_event' === $s ) {
					$s                = '';
					self::$read_items = array_filter(
						self::$read_items,
						function ( $event ) use ( $s ) {
							return ( $s === $event['recurrence'] );
						}
					);
				} else {
					self::$read_items = array_filter(
						self::$read_items,
						function ( $event ) use ( $s ) {
							return ( false !== strpos( $event['recurrence'], $s ) );
						}
					);
				}
			}

			if ( ! empty( $_REQUEST[ self::SEARCH_INPUT ] ) && is_string( $_REQUEST[ self::SEARCH_INPUT ] ) ) {
				$s = \sanitize_text_field( \wp_unslash( $_REQUEST[ self::SEARCH_INPUT ] ) );

				self::$read_items = array_filter(
					self::$read_items,
					function ( $event ) use ( $s ) {
						return ( false !== strpos( $event['hook'], $s ) );
					}
				);
			}

			// Plugin filtering (only when not requesting unfiltered set for counts).
			$site_filter = ( isset( $_REQUEST['site'] ) && '' !== trim( (string) $_REQUEST['site'] ) && -1 !== (int) $_REQUEST['site'] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST['site'] ) ) : -1;
			if ( -1 !== $site_filter && function_exists( 'is_multisite' ) && is_multisite() ) {
				self::$read_items = Crons_Helper::get_events_for_site( $site_filter );
			}

			if ( isset( $_REQUEST['plugin'] ) && '' !== trim( (string) $_REQUEST['plugin'] ) && -1 !== (int) $_REQUEST['plugin'] ) {
				$plugin_filter    = sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) );
				self::$read_items = array_filter(
					self::$read_items,
					function ( $event ) use ( $plugin_filter ) {
						if ( isset( $event['plugin_slugs'] ) && is_array( $event['plugin_slugs'] ) ) {
							return in_array( $plugin_filter, $event['plugin_slugs'], true );
						}
						return ( isset( $event['plugin_slug'] ) && '' !== $event['plugin_slug'] && $event['plugin_slug'] === $plugin_filter );
					}
				);
			}

			if ( ! $no_type_filtering ) {
				if ( ! empty( $_REQUEST['event_type'] ) && is_string( $_REQUEST['event_type'] ) ) {
					$hooks_type = \sanitize_text_field( \wp_unslash( $_REQUEST['event_type'] ) );
					$filtered   = self::get_filtered_events( self::$read_items );

					if ( isset( $filtered[ $hooks_type ] ) ) {
						self::$read_items = $filtered[ $hooks_type ];
					}
				}
			}

			if ( null !== self::$read_items ) {
				uasort( self::$read_items, array( __CLASS__, 'uasort_order_events' ) );
			}

			return self::$read_items ?? array();
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * Use that method for common rendering and separate columns logic in different methods. See below.
		 *
		 * @param array  $item        - Array with the current row values.
		 * @param string $column_name - The name of the currently processed column.
		 *
		 * @return mixed
		 *
		 * @since 1.1.0
		 */
		public function column_default( $item, $column_name ) {
			return self::format_column_value( $item, $column_name );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * Use that method for common rendering and separate columns logic in different methods. See below.
		 *
		 * @param array  $item        - Array with the current row values.
		 * @param string $column_name - The name of the currently processed column.
		 *
		 * @return mixed
		 *
		 * @since 1.1.0
		 */
		public static function format_column_value( $item, $column_name ) {
			switch ( $column_name ) {
				case 'site':
					if ( isset( $item['site_id'] ) ) {
						$site_id   = (int) $item['site_id'];
						$site_name = isset( $item['site_name'] ) ? $item['site_name'] : '';
						return '<code>' . esc_html( $site_id ) . '</code> ' . ( ! empty( $site_name ) ? esc_html( $site_name ) : '' );
					}
					return esc_html__( 'N/A', '0-day-analytics' );
				case 'hook':
					$query_args_view_data             = array();
					$query_args_view_data['hash']     = $item['hash'];
					$query_args_view_data['_wpnonce'] = \wp_create_nonce( 'bulk-custom-delete' );

					$actions['delete'] = '<a class="aadvana-cron-delete" href="#" data-nonce="' . \esc_attr( $query_args_view_data['_wpnonce'] ) . '" data-hash="' . \esc_attr( $query_args_view_data['hash'] ) . '">' . \esc_html__( 'Delete', '0-day-analytics' ) . '</a>';

					$actions['run'] = '<a class="aadvana-cron-run" href="#" data-nonce="' . \esc_attr( $query_args_view_data['_wpnonce'] ) . '" data-hash="' . \esc_attr( $query_args_view_data['hash'] ) . '">' . \esc_html__( 'Run', '0-day-analytics' ) . '</a>';

					$edit_url = \remove_query_arg(
						array( 'updated', 'deleted' ),
						\add_query_arg(
							array(
								'action'           => 'edit_cron',
								'hash'             => $item['hash'],
								self::SEARCH_INPUT => self::escaped_search_input(),
								'_wpnonce'         => $query_args_view_data['_wpnonce'],
							)
						)
					);

					$actions['edit'] = '<a class="aadvana-transient-run" href="' . \esc_url( $edit_url ) . '">' . \esc_html__( 'Edit', '0-day-analytics' ) . '</a>';

					$core_crons = '';

					if ( in_array( $item['hook'], Crons_Helper::WP_CORE_CRONS, true ) ) {
						$core_crons = '<span class="dashicons dashicons-wordpress" aria-hidden="true"></span> ';
					} else {
						foreach ( Crons_Helper::WP_CORE_CRONS as $cron_name ) {
							if ( \str_starts_with( $item['hook'], $cron_name ) ) {
								$core_crons = '<span class="dashicons dashicons-wordpress" aria-hidden="true"></span> ';

								break;
							}
						}
					}

					return '<span>' . $core_crons . '<b>' . \esc_html( (string) $item['hook'] ) . '</b></span>' . self::single_row_actions( $actions );
				case 'recurrence':
					return ( ! empty( $item['recurrence'] ) ? \esc_html( (string) $item['recurrence'] ) : __( 'once', '0-day-analytics' ) );
				case 'args':
					if ( empty( $item['args'] ) ) {
						return __( 'NO', '0-day-analytics' );
					}
					$display_args = is_string( $item['args'] ) ? $item['args'] : wp_json_encode( $item['args'], JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR );
					return '<pre>' . \esc_html( (string) $display_args ) . '</pre>';
				case 'schedule':
					return WP_Helper::time_formatter( $item, esc_html__( 'overdue', '0-day-analytics' ) );
				case 'actions':
					$hook_callbacks = Crons_Helper::get_cron_callbacks( $item['hook'] );

					if ( ! empty( $hook_callbacks ) ) {
						$callbacks = array();

						foreach ( $hook_callbacks as $callback ) {
							if ( \key_exists( 'error', $callback['callback'] ) ) {
								if ( \is_a( $callback['callback']['error'], '\WP_Error' ) ) {
									$callbacks[] = '<span style="color: #b32d2e; background:#ffd6d6;padding:3px;">' . \esc_html__( 'Error occurred with cron callback', '0-day-analytics' ) . ' - ' . \esc_html( $callback['callback']['error']->get_error_message() ) . '</span>';
								} else {
									$callbacks[] = '<span style="color: #b32d2e; background:#ffd6d6;padding:3px;">' . \esc_html__( 'Unknown error occurred', '0-day-analytics' ) . '</span>';
								}
							} else {
								$callbacks[] = self::output_filename(
									$callback['callback']['name'],
									$callback['callback']['file'],
									$callback['callback']['line']
								);
							}

							if ( isset( $callback['callback']['component'] ) && ! empty( $callback['callback']['component'] ) && isset( $callback['callback']['component']['name'] ) && ! empty( $callback['callback']['component']['name'] ) ) {
								$callbacks[ \array_key_last( $callbacks ) ] .= '<br><span class="status-crontrol-info"><span class="dashicons dashicons-info" aria-hidden="true"></span> ' . \esc_html( $callback['callback']['component']['name'] ) . '</span>';
							}
						}

						if ( 'action_scheduler_run_queue' === $item['hook'] ) {
							if ( \count( $callbacks ) ) {
								$callbacks[ \array_key_last( $callbacks ) ] .= sprintf(
									'<br><span class="status-crontrol-info"><span class="dashicons dashicons-info" aria-hidden="true"></span> <a href="%s">%s</a></span>',
									\admin_url( 'tools.php?page=action-scheduler' ),
									\esc_html__( 'View the scheduled actions here &raquo;', '0-day-analytics' )
								);
							} else {
								$callbacks[] = sprintf(
									'<span class="status-crontrol-info"><span class="dashicons dashicons-info" aria-hidden="true"></span> <a href="%s">%s</a></span>',
									\admin_url( 'tools.php?page=action-scheduler' ),
									\esc_html__( 'View the scheduled actions here &raquo;', '0-day-analytics' )
								);
							}
						}

						return '<ol><li>' . implode( '</li><li><hr>', $callbacks ) . '</li></ol>'; // WPCS:: XSS ok.
					}

					return '';
				default:
					return isset( $item[ $column_name ] )
						? \esc_html( $item[ $column_name ] )
						: __( 'Column "', '0-day-analytics' ) . \esc_html( $column_name ) . __( '" not found', '0-day-analytics' );
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
		 * @since 1.1.0
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<label class="screen-reader-text" for="' . \esc_attr( $item['hash'] ) . '">' . sprintf(
					// translators: The column name.
					__( 'Select %s', '0-day-analytics' ),
					'id'
				) . '</label>'
				. '<input type="checkbox" name="' . \esc_attr( self::$table_name ) . '[]" id="' . \esc_attr( $item['hash'] ) . '" value="' . \esc_attr( $item['hash'] ) . '" />'
			);
		}

		/**
		 * Returns an associative array containing the bulk actions.
		 *
		 * @since 1.1.0
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			/**
			 * On hitting apply in bulk actions the url params are set as
			 * ?action=bulk-download&paged=1&action2=-1.
			 *
			 * Action and action2 are set based on the triggers above or below the table
			 */
			$actions = array(
				'delete' => __( 'Delete', '0-day-analytics' ),
				'run'    => __( 'Run', '0-day-analytics' ),
			);

			return $actions;
		}

		/**
		 * Process actions triggered by the user.
		 *
		 * @since 1.1.0
		 */
		public function handle_table_actions() {
			if ( ! isset( $_REQUEST[ self::$table_name ] ) ) {
				return;
			}

			// Enforce capability for destructive bulk actions.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return;
			}
			/**
			 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2'].
			 *
			 * Action - is set if checkbox from top-most select-all is set, otherwise returns -1
			 * Action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
			 */

			// check for individual row actions.

			// check for table bulk actions.
			if ( ( ( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) || ( isset( $_REQUEST['action2'] ) && 'delete' === $_REQUEST['action2'] ) ) ) {
				/**
				 * Note: the nonce field is set by the parent class
				 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );.
				 */
				WP_Helper::verify_admin_nonce( 'bulk-' . $this->_args['plural'] );

				if ( isset( $_REQUEST[ self::$table_name ] ) && \is_array( $_REQUEST[ self::$table_name ] ) ) {
					foreach ( \wp_unslash( $_REQUEST[ self::$table_name ] ) as $id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$id = \sanitize_text_field( $id );
						if ( ! empty( $id ) ) {
							// Delete the cron.
							Crons_Helper::delete_event( $id );
						}
					}
				}

				$redirect =
				\remove_query_arg(
					array( 'delete', '_wpnonce' ),
					\add_query_arg(
						array(
							self::SEARCH_INPUT => self::escaped_search_input(),
							'page'             => self::CRON_MENU_SLUG,
						),
						\admin_url( 'admin.php' )
					)
				);

				\wp_safe_redirect( $redirect );
				exit;
			}
			if ( ( ( isset( $_REQUEST['action'] ) && 'run' === $_REQUEST['action'] ) || ( isset( $_REQUEST['action2'] ) && 'run' === $_REQUEST['action2'] ) ) ) {
				/**
				 * Note: the nonce field is set by the parent class
				 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );.
				 */
				WP_Helper::verify_admin_nonce( 'bulk-' . $this->_args['plural'] );

				if ( isset( $_REQUEST[ self::$table_name ] ) && \is_array( $_REQUEST[ self::$table_name ] ) ) {
					foreach ( \wp_unslash( $_REQUEST[ self::$table_name ] ) as $id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$id = \sanitize_text_field( $id );
						if ( ! empty( $id ) ) {
							// Delete the cron.
							Crons_Helper::execute_event( $id );
						}
					}
				}

				$redirect =
				\remove_query_arg(
					array( 'delete', '_wpnonce' ),
					\add_query_arg(
						array(
							self::SEARCH_INPUT => self::escaped_search_input(),
							'page'             => self::CRON_MENU_SLUG,
						),
						\admin_url( 'admin.php' )
					)
				);

				\wp_safe_redirect( $redirect );
				exit;
			}
		}

		/**
		 * Adds a screen options to the current screen table.
		 *
		 * @param \WP_Hook $hook - The hook object to attach to.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function add_screen_options( $hook ) {
			return;
		}

		/**
		 * Table navigation.
		 *
		 * @param string $which - Position of the nav.
		 *
		 * @since 1.1.0
		 */
		public function extra_tablenav( $which ) {

			// If the position is not top then render.

			// Show site alerts widget.
			// NOTE: this is shown when the filter IS NOT true.
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @param object|array $item - The current item.
		 *
		 * @since 1.1.0
		 */
		public function single_row( $item ) {
			$late = Crons_Helper::is_late( $item );

			if ( $late ) {
				$classes = ' late';
			} else {
				$classes = ' on-time';
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
		 * @since 1.1.0
		 */
		public function display_tablenav( $which ) {
			if ( 'top' === $which ) {
				\wp_nonce_field( 'bulk-' . $this->_args['plural'] );

				?>
				<script>
					jQuery(document).ready(function(){
						jQuery('.aadvana-cron-delete').on('click', function(e){

							e.preventDefault();

							if ( confirm( '<?php echo esc_js( __( 'You sure you want to delete this cron?', '0-day-analytics' ) ); ?>' ) ) {

								let that = this;

								jQuery(that).css({
									"pointer-events": "none",
									"cursor": "default"
								});

								var data = {
									'action': '<?php echo ADVAN_PREFIX; ?>delete_cron',
									'post_type': 'GET',
									'_wpnonce': jQuery(this).data('nonce'),
									'hash': jQuery(this).data('hash'),
								};

								jQuery.get(ajaxurl, data, function(response) {

									if ( 2 === response['data'] || 0 === response['data'] ) {
										jQuery(that).closest("tr").animate({
											opacity: 0
										}, 1000, function() {
											jQuery(that).closest("tr").remove();
										});
									} else {
										jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + response['data'] + '</div></td></tr>');
									}
								}, 'json').fail(function(xhr, status, error) {
									if ( xhr.responseJSON && xhr.responseJSON.data ) {
										errorMessage = xhr.responseJSON.data;
										jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + errorMessage + '</div></td></tr>');
									}
								}).always(function() {

									jQuery(that).css({
										"pointer-events": "",
										"cursor": ""
									})
								});
							}

						});
						jQuery('.aadvana-cron-run').on('click', function(e){

							e.preventDefault();

							let that = this;

							jQuery(that).css({
								"pointer-events": "none",
								"cursor": "default"
							});

							if (typeof wp.apiFetch === 'function') {

								try {
									attResp = wp.apiFetch({
										path: '/<?php echo Endpoints::ENDPOINT_ROOT_NAME; ?>/v1/cron_run/' + jQuery(this).data('hash') + '?aadvana_run_cron=1',
										method: 'GET',
										cache: 'no-cache',
										headers: (window.wpApiSettings && window.wpApiSettings.nonce) ? { 'X-WP-Nonce': window.wpApiSettings.nonce } : undefined
									}).then( ( attResp ) => {
										
										if (attResp.success) {

											let success = '<?php echo esc_js( __( 'Successfully run', '0-day-analytics' ) ); ?>';
											let dynRun = jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="updated" style="background:#fff; color:#000;"> ' + success + '</div></td></tr>');
											dynRun.next('tr').fadeOut( 5000, function() {
												dynRun.next('tr').remove();
											});

										}
									} ).catch(
										( error ) => {
											if (error.message) {
												jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + error.message + '</div></td></tr>');
											}
										}
									);
								} catch (error) {
									throw error;
								} finally {
									jQuery(that).css({
										"pointer-events": "",
										"cursor": ""
									})
								}
							} else {

								var data = {
									'action': 'aadvana_run_cron',
									'post_type': 'GET',
									'_wpnonce': jQuery(this).data('nonce'),
									'hash': jQuery(this).data('hash'),
								};

								jQuery.get(ajaxurl, data, function(response) {
									if ( 2 === response['data'] || 0 === response['data'] ) {

											let success = '<?php echo esc_js( __( 'Successfully run', '0-day-analytics' ) ); ?>';
											let dynRun = jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="updated" style="background:#fff; color:#000;"> ' + success + '</div></td></tr>');
											dynRun.next('tr').fadeOut( 5000, function() {
												dynRun.next('tr').remove();
											});
										
									} else {
										let dynRun = jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + response['data'] + '</div></td></tr>');
										dynRun.next('tr').fadeOut( 5000, function() {
											dynRun.next('tr').remove();
										});
									}
								}, 'json').fail(function(xhr, status, error) {
									if ( xhr.responseJSON && xhr.responseJSON.data ) {
										errorMessage = xhr.responseJSON.data;
										jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + errorMessage + '</div></td></tr>');
									} else {
										if ( error ) {
											errorMessage = error + ' Check your browser console for more information.';
											jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + errorMessage + '</div></td></tr>');
										}
									}
								}).always(function() {

									jQuery(that).css({
										"pointer-events": "",
										"cursor": ""
									})
								});

							}
						});
					});
				</script>
				<style>
					.generated-crons .late th:nth-child(1) {
						border-left: 7px solid #dd9192 !important;
					}
					.generated-crons .on-time th:nth-child(1) {
						border-left: 7px solid rgb(49, 179, 45) !important;
					}
				</style>
				<?php
			}
			?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			if ( $this->has_items() ) {
				?>
				<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
				</div>

				<?php
			}
			if ( 'top' === $which ) {
				?>
				<div class="alignleft actions">
					<?php
					// Plugin dropdown.
					$selected_plugin    = ( isset( $_REQUEST['plugin'] ) && '' !== trim( (string) $_REQUEST['plugin'] ) ) ? ( ( -1 === (int) $_REQUEST['plugin'] ) ? -1 : sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) ) : -1;
					$selected_site      = ( isset( $_REQUEST['site'] ) && '' !== trim( (string) $_REQUEST['site'] ) ) ? ( ( -1 === (int) $_REQUEST['site'] ) ? -1 : (int) sanitize_text_field( wp_unslash( $_REQUEST['site'] ) ) ) : -1;
					$plugins_dropdown   = self::get_plugins_dropdown( $selected_plugin, $which );
						$sites_dropdown = self::get_sites_dropdown( $selected_site, $which );
					if ( ! empty( $plugins_dropdown ) ) {
						echo $plugins_dropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						if ( ! empty( $sites_dropdown ) ) {
							echo $sites_dropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
							<script>
							jQuery(document).ready(function(){
								jQuery('form .site_filter').on('change', function(){
									jQuery('form .site_filter').val(jQuery(this).val());
									jQuery(this).closest('form')
										.attr('action','<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>')
										.append('<input type="hidden" name="action" value="<?php echo esc_attr( self::SITE_FILTER_ACTION ); ?>">')
										.append('<?php wp_nonce_field( self::SITE_FILTER_ACTION, self::SITE_FILTER_ACTION . 'nonce' ); ?>')
										.submit();
								});
							});
							</script>
							<?php
						}
						?>

						<script>
						jQuery(document).ready(function(){
							jQuery('form .plugin_filter').on('change', function(){
								jQuery('form .plugin_filter').val(jQuery(this).val());
								jQuery(this).closest('form')
									.attr('action','<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>')
									.append('<input type="hidden" name="action" value="<?php echo esc_attr( self::PLUGIN_FILTER_ACTION ); ?>">')
									.append('<?php wp_nonce_field( self::PLUGIN_FILTER_ACTION, self::PLUGIN_FILTER_ACTION . 'nonce' ); ?>')
									.submit();
							});
						});
						</script>
						<?php
					}
					?>
					<select class="schedules-filter" name="schedules_filter">
					<?php
					$schedules = \wp_get_schedules();
					uasort( $schedules, array( __CLASS__, 'sort_schedules' ) );
					?>
						<option value=""><?php esc_html_e( 'All Schedules', '0-day-analytics' ); ?></option>
						<?php
						foreach ( $schedules as $schedule_id => $schedule ) {

							$selected = '';

							if ( isset( $_REQUEST['schedules_filter'] ) && ! empty( $_REQUEST['schedules_filter'] ) ) {
								if ( \sanitize_text_field( \wp_unslash( $_REQUEST['schedules_filter'] ) ) === $schedule_id ) {
									$selected = 'selected="selected"';
								}
							}
							?>
							<option value="<?php echo \esc_attr( $schedule_id ); ?>" <?php echo $selected; ?>><?php echo \esc_html( $schedule['display'] ); ?></option>
						<?php } ?>
						<option value="single_event" <?php echo ( isset( $_REQUEST['schedules_filter'] ) && ! empty( $_REQUEST['schedules_filter'] ) && 'single_event' === $_REQUEST['schedules_filter'] ) ? 'selected="selected"' : ''; ?>><?php \esc_html_e( 'Single event', '0-day-analytics' ); ?></option>
					</select>
					<?php \submit_button( __( 'Filter', '0-day-analytics' ), '', 'filter_action', false, array( 'id' => 'schedules-submit' ) ); ?>
				</div>
				<div id="export-form">
					<div>
						<button id=" " class="button" data-type-export="cron" data-plugin_filter="<?php echo esc_attr( $selected_plugin ); ?>" data-site_filter="<?php echo esc_attr( $selected_site ); ?>">
							<?php echo esc_html__( 'CSV Export', '0-day-analytics' ); ?>
						</button>
						<button id="cancel-export" class="button cancel-btn" style="display:none;">
							<?php echo esc_html__( 'Cancel', '0-day-analytics' ); ?>
						</button>
					</div>

					<div id="progress-container" class="progress-wrap" style="display:none;">
						<div id="progress-bar"></div>
					</div>

					<p id="progress-text" style="display:none;"><?php echo esc_html__( 'Waiting to start...', '0-day-analytics' ); ?></p>
				</div>
				<?php
			}
			if ( $this->has_items() ) {
				?>
				<div class="tablenav-pages one-page">
					<span class="displaying-num"><?php echo \esc_html( (string) count( self::get_cron_items() ) . ' ' . __( 'events', '0-day-analytics' ) ); ?></span>
				</div>

				<?php
			}
			?>

				<br class="clear" />
			</div>
			<?php
				$this->extra_tablenav( $which );

			if ( 'bottom' === $which ) {
				$schedules = \wp_get_schedules();
				uasort( $schedules, array( __CLASS__, 'sort_schedules' ) );
				?>
				<h2><?php \esc_html_e( 'Available schedules', '0-day-analytics' ); ?></h2>
				<table class="widefat striped" style="width:auto">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Frequency', '0-day-analytics' ); ?></th>
							<th><?php esc_html_e( 'ID', '0-day-analytics' ); ?></th>
							<th><?php esc_html_e( 'Interval (seconds)', '0-day-analytics' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $schedules as $schedule_id => $schedule ) { ?>
							<tr>
								<td><?php echo esc_html( $schedule['display'] ); ?></td>
								<td><code><?php echo esc_html( $schedule_id ); ?></code></td>
								<td><?php echo esc_html( $schedule['interval'] ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php
			}
		}

		/**
		 * Responsible for sorting the schedule intervals
		 *
		 * @param int $a - Timestamp.
		 * @param int $b - Timestamp.
		 *
		 * @return int
		 *
		 * @since 1.7.4
		 */
		public static function sort_schedules( $a, $b ) {
			if ( $a['interval'] == $b['interval'] ) {
				return strcmp( $a['display'], $b['display'] );
			}
			if ( $a['interval'] ) {
				if ( $b['interval'] ) {
					return $a['interval'] - $b['interval'];
				}
				return -1;
			}
			if ( $b['interval'] ) {
				return 1;
			}
			return 0;
		}

		/**
		 * Generates the required HTML for a list of row action links.
		 *
		 * @since 1.3.0
		 *
		 * @param string[] $actions        An array of action links.
		 * @param bool     $always_visible Whether the actions should be always visible.
		 * @return string The HTML for the row actions.
		 */
		protected static function single_row_actions( $actions, $always_visible = false ) {
			$action_count = count( $actions );

			if ( ! $action_count ) {
				return '';
			}

			$mode = \get_user_setting( 'posts_list_mode', 'list' );

			if ( 'excerpt' === $mode ) {
				$always_visible = true;
			}

			$output = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';

			$i = 0;

			foreach ( $actions as $action => $link ) {
				++$i;

				$separator = ( $i < $action_count ) ? ' | ' : '';

				$output .= "<span class='$action'>{$link}{$separator}</span>";
			}

			$output .= '</div>';

			$output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
			/* translators: Hidden accessibility text. */
			__( 'Show more details', '0-day-analytics' ) .
			'</span></button>';

			return $output;
		}

		/**
		 * Checks and returns if there are items to show.
		 *
		 * @return bool
		 *
		 * @since 1.4.0
		 */
		public static function are_there_items(): bool {
			return ( isset( self::$read_items ) && ! empty( self::$read_items ) );
		}

		/**
		 * Returns a file path, name, and line number, or a clickable link to the file. Safe for output.
		 *
		 * @param  string $text        The display text, such as a function name or file name.
		 * @param  string $file        The full file path and name.
		 * @param  int    $line        Optional. A line number, if appropriate.
		 * @param  bool   $is_filename Optional. Is the text a plain file name? Default false.
		 *
		 * @return string The fully formatted file link or file name, safe for output.
		 *
		 * @since 2.3.0
		 */
		public static function output_filename( $text, $file, $line = 0, $is_filename = false ) {
			if ( empty( $file ) ) {
				if ( $is_filename ) {
					return esc_html( $text );
				} else {
					return '<code>' . esc_html( $text ) . '</code>';
				}
			}

			$link_line = $line ? $line : 1;

			$source_link = '';

			$query_array = array(
				'_wpnonce' => \wp_create_nonce( 'source-view' ),
				'action'   => 'log_source_view',
			);

			if ( isset( $file ) && ! empty( $file ) ) {
				$query_array['error_file'] = $file;

				if ( isset( $link_line ) && ! empty( $link_line ) ) {
					$query_array['error_line'] = $link_line;
				}

				$query_array['TB_iframe'] = 'true';

				$view_url = \esc_url(
					\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
				);

				$title = __( 'Viewing: ', '0-day-analytics' ) . $query_array['error_file'];

				$source_link = '<div> <a href="' . $view_url . '" title="' . \esc_attr( $title ) . '" class="thickbox view-source gray_lab badge">' . __( 'view source', '0-day-analytics' ) . '</a></div>';

			}

			if ( ! self::has_clickable_links() ) {
				$fallback = WP_Helper::standard_dir( $file, '' );
				if ( $line ) {
					$fallback .= ':' . $line;
				}
				if ( $is_filename ) {
					$return = esc_html( $text );
				} else {
					$return = '<code>' . esc_html( $text ) . '</code>';
				}
				if ( $fallback !== $text ) {
					$return .= '<br><span>' . esc_html( $fallback ) . '</span>' . $source_link;
				}
				return $return;
			}

			$map = self::get_file_path_map();

			if ( ! empty( $map ) ) {
				foreach ( $map as $from => $to ) {
					$file = str_replace( $from, $to, $file );
				}
			}

			$link_format = self::get_file_link_format();
			$link        = sprintf( $link_format, rawurlencode( $file ), intval( $link_line ) );

			if ( $is_filename ) {
				$format = '<a href="%1$s">%2$s%3$s</a>';
			} else {
				$format = '<a href="%1$s"><code>%2$s</code>%3$s</a>';
			}

			return sprintf(
				$format,
				\esc_attr( $link ),
				\esc_html( $text ),
				( 'edit' )
			);
		}

		/**
		 * Returns file path map
		 *
		 * @return array<string, string>
		 *
		 * @since 1.4.0
		 */
		public static function get_file_path_map() {
			$map = array();

			$host_path = getenv( 'HOST_PATH' );

			if ( ! empty( $host_path ) ) {
				$source         = rtrim( ABSPATH, DIRECTORY_SEPARATOR );
				$replacement    = rtrim( $host_path, DIRECTORY_SEPARATOR );
				$map[ $source ] = $replacement;
			}

			return $map;
		}

		/**
		 * Returns the extracted file format.
		 *
		 * @return string|false
		 *
		 * @since 1.4.0
		 */
		public static function get_file_link_format() {
			if ( ! isset( self::$file_link_format ) ) {
				$format = ini_get( 'xdebug.file_link_format' );

				if ( empty( $format ) ) {
					self::$file_link_format = false;
				} else {
					self::$file_link_format = str_replace( array( '%f', '%l' ), array( '%1$s', '%2$d' ), $format );
				}
			}

			return self::$file_link_format;
		}

		/**
		 * Check if there are clickable links in the file formatter.
		 *
		 * @return bool
		 *
		 * @since 1.4.0
		 */
		public static function has_clickable_links(): bool {
			return ( false !== self::get_file_link_format() );
		}

		/**
		 * Sorts the events by the selected column.
		 *
		 * @param array $a - First item to compare.
		 * @param array $b - Second item to compare.
		 *
		 * @return int
		 *
		 * @since 1.4.0
		 */
		private static function uasort_order_events( $a, $b ) {
			$orderby = ( ! empty( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['orderby'] ) ) : 'crontrol_next';
			$order   = ( ! empty( $_GET['order'] ) && is_string( $_GET['order'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['order'] ) ) : 'asc';
			$compare = 0;

			switch ( $orderby ) {
				case 'hook':
					if ( 'asc' === $order ) {
						$compare = strcmp( $a['hook'], $b['hook'] );
					} else {
						$compare = strcmp( $b['hook'], $a['hook'] );
					}
					break;
				case 'recurrence':
					if ( 'asc' === $order ) {
						$compare = ( $a['recurrence'] ?? 0 ) <=> ( $b['recurrence'] ?? 0 );
					} else {
						$compare = ( $b['recurrence'] ?? 0 ) <=> ( $a['recurrence'] ?? 0 );
					}
					break;
				default:
					if ( 'asc' === $order ) {
						$compare = $a['schedule'] <=> $b['schedule'];
					} else {
						$compare = $b['schedule'] <=> $a['schedule'];
					}
					break;
			}

			return $compare;
		}

		/**
		 * Display the list of hook types.
		 *
		 * @return array<string,string>
		 *
		 * @since 2.9.0
		 */
		public function get_views() {

			$views      = array();
			$hooks_type = ( isset( $_REQUEST['event_type'] ) && is_string( $_REQUEST['event_type'] ) ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['event_type'] ) ) : '';

			$types = array(
				// 'all'      => __( 'All events', '0-day-analytics' ),
				'noaction' => __( 'Events with no action', '0-day-analytics' ),
				'core'     => __( 'WordPress core events', '0-day-analytics' ),
				'custom'   => __( 'Custom events', '0-day-analytics' ),
			// 'url'      => __( 'URL events', '0-day-analytics' ),
			);

			$url = \add_query_arg(
				array(
					'page'       => self::CRON_MENU_SLUG,
					// self::SEARCH_INPUT => self::escaped_search_input(),
					// 'schedules_filter' => isset( $_REQUEST['schedules_filter'] ) && ! empty( $_REQUEST['schedules_filter'] ) ? $_REQUEST['schedules_filter'] : '',
					'event_type' => 'all',
				),
				\admin_url( 'admin.php' )
			);

			$views['all'] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
				\esc_url( $url ),
				$hooks_type === 'all' ? ' class="current"' : '',
				\esc_html__( 'All events (no filters)', '0-day-analytics' ),
				\esc_html( \number_format_i18n( count( Crons_Helper::get_events() ) ) )
			);

			$filtered = self::get_filtered_events( self::get_cron_items( true ) );

			/**
			 * @var array<string,string> $types
			 */
			foreach ( $types as $key => $type ) {
				if ( ! isset( $filtered[ $key ] ) ) {
					continue;
				}

				$count = count( $filtered[ $key ] );

				if ( ! $count ) {
					continue;
				}

				$url = \add_query_arg(
					array(
						'page'             => self::CRON_MENU_SLUG,
						self::SEARCH_INPUT => self::escaped_search_input(),
						'schedules_filter' => isset( $_REQUEST['schedules_filter'] ) && ! empty( $_REQUEST['schedules_filter'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['schedules_filter'] ) ) : '',
						'event_type'       => $key,
					),
					\admin_url( 'admin.php' )
				);

				$views[ $key ] = sprintf(
					'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
					\esc_url( $url ),
					$hooks_type === $key ? ' class="current"' : '',
					\esc_html( $type ),
					\esc_html( \number_format_i18n( $count ) )
				);
			}

			return $views;
		}

		/**
		 * Returns events filtered by various parameters
		 *
		 * @param array<string,stdClass> $events The list of all events.
		 * @return array<string,array<string,stdClass>> Array of filtered events keyed by filter name.
		 *
		 * @since 2.9.0
		 */
		public static function get_filtered_events( array $events ) {

			$filtered['noaction'] = array_filter(
				$events,
				function ( $event ) {
					$hook_callbacks = Crons_Helper::get_cron_callbacks( $event['hook'] );

					return empty( $hook_callbacks );
				}
			);

			$filtered['core'] = array_filter(
				$events,
				function ( $event ) {
					return ( in_array( $event['hook'], Crons_Helper::WP_CORE_CRONS, true ) );
				}
			);

			$filtered['custom'] = array_filter(
				$events,
				function ( $event ) {
					return ( ! in_array( $event['hook'], Crons_Helper::WP_CORE_CRONS, true ) );
				}
			);

			// $filtered['url'] = array_filter(
			// $events,
			// function ( $event ) {
			// return ( 'crontrol_url_cron_job' === $event->hook );
			// }
			// );

			return $filtered;
		}

		/**
		 * Builds sites dropdown (multisite only) listing all sites (optionally only those having cron events).
		 *
		 * @param int    $selected Selected site id or -1.
		 * @param string $which  Position top|bottom.
		 * @return string
		 */
		public static function get_sites_dropdown( $selected = -1, string $which = 'top' ): string {
			if ( ! function_exists( 'is_multisite' ) || ! is_multisite() ) {
				return '';
			}
			if ( ! current_user_can( 'manage_network' ) ) {
				return '';
			}
			$which = in_array( $which, array( 'top', 'bottom' ), true ) ? $which : 'top';
			$sites = get_sites( array( 'number' => 0 ) );
			if ( empty( $sites ) ) {
				return '';
			}
			$output  = '<select class="site_filter" name="site_' . esc_attr( $which ) . '" id="site_' . esc_attr( $which ) . '">';
			$output .= '<option value="-1">' . __( 'All sites', '0-day-analytics' ) . '</option>';
			foreach ( $sites as $site ) {
				$blog_id = (int) $site->blog_id;
				$details = get_blog_details( $blog_id );
				$name    = ( $details && isset( $details->blogname ) ) ? $details->blogname : 'Site ' . $blog_id;
				$sel     = ( (int) $selected === $blog_id ) ? ' selected' : '';
				$output .= '<option value="' . esc_attr( $blog_id ) . '"' . $sel . '>' . esc_html( $name ) . '</option>';
			}
			$output .= '</select>';
			return $output;
		}

		/**
		 * Builds plugins dropdown based on detected plugin slugs from cron callbacks.
		 *
		 * @param string|int $selected Currently selected plugin slug or -1 for all.
		 * @param string     $which    Position (top|bottom) for unique element naming.
		 *
		 * @return string HTML select or empty string if no plugins detected.
		 */
		public static function get_plugins_dropdown( $selected = -1, $which = 'top' ): string {
			$which = in_array( $which, array( 'top', 'bottom' ), true ) ? $which : 'top';

			$all_events = self::get_cron_items( true );
			$plugins    = array();
			foreach ( $all_events as $event ) {
				if ( isset( $event['plugin_slugs'] ) && is_array( $event['plugin_slugs'] ) ) {
					foreach ( $event['plugin_slugs'] as $slug ) {
						if ( ! empty( $slug ) ) {
							$plugins[ $slug ] = $slug;
						}
					}
				} elseif ( isset( $event['plugin_slug'] ) && '' !== $event['plugin_slug'] ) {
					$plugins[ $event['plugin_slug'] ] = $event['plugin_slug'];
				}
			}

			if ( empty( $plugins ) ) {
				return '';
			}

			$output  = '<select class="plugin_filter" name="plugin_' . esc_attr( $which ) . '" id="plugin_' . esc_attr( $which ) . '">';
			$output .= '<option value="-1">' . __( 'All plugins', '0-day-analytics' ) . '</option>';
			foreach ( $plugins as $slug ) {
				$details  = Plugin_Theme_Helper::get_plugin_from_path( $slug );
				$name     = ( isset( $details['Name'] ) && ! empty( $details['Name'] ) ) ? $details['Name'] : $slug;
				$sel_attr = ( (string) $selected === (string) $slug ) ? ' selected' : '';
				$output  .= '<option value="' . esc_attr( $slug ) . '"' . $sel_attr . '>' . esc_html( $name ) . '</option>';
			}
			$output .= '</select>';

			return $output;
		}
	}
}
