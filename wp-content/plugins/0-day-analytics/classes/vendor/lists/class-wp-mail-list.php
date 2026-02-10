<?php
/**
 * Responsible for the wp-mails view
 *
 * @package    advana
 * @subpackage lists
 * @since 3.0.0
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
use ADVAN\Controllers\WP_Mail_Log;
use ADVAN\Entities\WP_Mail_Entity;
use ADVAN\Lists\Traits\List_Trait;
use ADVAN\Lists\Views\WP_Mail_View;
use ADVAN\Helpers\Plugin_Theme_Helper;
use ADVAN\Entities_Global\Common_Table;

/**
 * Base list table class
 */
if ( ! class_exists( '\ADVAN\Lists\WP_Mail_List' ) ) {

	/**
	 * Responsible for rendering base table for manipulation
	 *
	 * @since 3.0.0
	 */
	class WP_Mail_List extends Abstract_List {

		use List_Trait;

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_wp_mail';

		public const SCREEN_OPTIONS_SLUG = 'advanced_analytics_wp_mail_list';

		public const SEARCH_INPUT = 's';

		public const WP_MAIL_MENU_SLUG = 'advan_wp_mail';

		public const NEW_ACTION = 'advan_mail_new';

		public const NONCE_NAME = 'advana_wp_mail_manager';

		public const SITE_ID_FILTER_ACTION = 'filter_site_id';

		public const PLUGIN_FILTER_ACTION = self::PAGE_SLUG . '_filter_plugin';

		/**
		 * The table to show
		 *
		 * @var Common_Table
		 *
		 * @since 3.0.0
		 */
		private static $table;

		/**
		 * How many
		 *
		 * @var int
		 *
		 * @since 3.0.0
		 */
		protected $count;

		/**
		 * How many records to show per page
		 *
		 * @var integer
		 *
		 * @since 3.0.0
		 */
		protected static $rows_per_page = 20;

		/**
		 * Holds the prepared options for speeding the process
		 *
		 * @var array
		 *
		 * @since 3.0.0
		 */
		protected static $admin_columns = array();

		/**
		 * The entity class related to the list
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $entity = WP_Mail_Entity::class;

		/**
		 * Default order by column
		 *
		 * @var string
		 *
		 * @since 3.8.0
		 */
		protected static $default_order_by = 'id';

		/**
		 * Holds the array with all of the collected sources.
		 *
		 * @var array
		 *
		 * @since 3.0.0
		 */
		private static $sources = array(
			'plugins' => array(),
			'themes'  => array(),
		);

		/**
		 * Default class constructor
		 *
		 * @param string $table_name - The name of the table to use for the listing.
		 *
		 * @since 3.0.0
		 */
		public function __construct( string $table_name = '' ) {

			$class = Common_Table::class;

			Common_Table::init( WP_Mail_Entity::get_table_name() );
			self::$table = $class;

			parent::__construct(
				array(
					'plural'   => WP_Mail_Entity::get_table_name(),    // Plural value used for labels and the objects being listed.
					'singular' => WP_Mail_Entity::get_table_name(),     // Singular label for an object being listed, e.g. 'post'.
					'ajax'     => false,      // If true, the parent class will call the _js_vars() method in the footer.
				)
			);
		}

		/**
		 * Inits class hooks. That is called every time - not in some specific environment set.
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public static function init() {
			\add_action( 'admin_post_' . self::NEW_ACTION, array( WP_Mail_View::class, 'new_mail' ) );
			\add_action( 'admin_post_' . self::SITE_ID_FILTER_ACTION, array( WP_Mail_View::class, 'site_id_filter_action' ) );
			\add_action( 'admin_post_' . self::PLUGIN_FILTER_ACTION, array( WP_Mail_View::class, 'plugin_filter_action' ) );
			\add_action( 'admin_post_resend_email', array( __CLASS__, 'handle_resend_email' ) );
			\add_action( 'admin_notices', array( __CLASS__, 'display_resend_notice' ) );
			\add_filter( 'advan_cron_hooks', array( __CLASS__, 'add_cron_job' ) );
		}

		/**
		 * Adds a cron job for truncating the records in the requests table
		 *
		 * @param array $crons - The array with all the crons associated with the plugin.
		 *
		 * @return array
		 *
		 * @since 3.0.0
		 */
		public static function add_cron_job( $crons ) {
			if ( -1 !== (int) Settings::get_option( 'advana_mail_logging_clear' ) ) {
				$crons[ ADVAN_PREFIX . 'mail_logging_clear' ] = array(
					'time' => Settings::get_option( 'advana_mail_logging_clear' ),
					'hook' => array( __CLASS__, 'truncate_wp_mail_table' ),
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
		 * @since 3.0.0
		 */
		public static function truncate_wp_mail_table() {
			Common_Table::truncate_table( null, WP_Mail_Entity::get_table_name() );
		}

		/**
		 * Handles the email resend action
		 *
		 * @return void
		 *
		 * @since 4.8.0
		 */
		public static function handle_resend_email() {
			// Check user capabilities.
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to resend emails.', '0-day-analytics' ) );
			}

			// Get mail ID.
			$mail_id = isset( $_GET['mail_id'] ) ? \absint( $_GET['mail_id'] ) : 0;

			// Verify nonce.
			if ( ! isset( $_GET['_wpnonce'] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_GET['_wpnonce'] ) ), 'resend_email_' . $mail_id ) ) {
				\wp_die( \esc_html__( 'Security check failed.', '0-day-analytics' ) );
			}

			if ( ! $mail_id ) {
				\wp_die( \esc_html__( 'Invalid email ID.', '0-day-analytics' ) );
			}

			// Load email data.
			$email = WP_Mail_Entity::load( 'id=%d', array( $mail_id ) );
			if ( ! $email ) {
				\wp_die( \esc_html__( 'Email not found.', '0-day-analytics' ) );
			}

			// Check if email can be resent.
			if ( isset( $email['can_resend'] ) && 0 === (int) $email['can_resend'] ) {
				\wp_die( \esc_html__( 'This email cannot be resent (it may contain time-sensitive information like password resets or verification codes).', '0-day-analytics' ) );
			}

			// Prepare email parameters.
			$to      = $email['email_to'];
			$subject = $email['subject'];
			$message = $email['message'];

			// Reconstruct headers.
			$headers = array();

			// Add From header if we have plugin context.
			if ( ! empty( $email['plugin_slug'] ) ) {
				$headers[] = 'From: ' . \get_bloginfo( 'name' ) . ' <' . \get_option( 'admin_email' ) . '>';
			}

			// Add CC if present.
			if ( ! empty( $email['email_cc'] ) ) {
				$headers[] = 'CC: ' . $email['email_cc'];
			}

			// Add BCC if present.
			if ( ! empty( $email['email_bcc'] ) ) {
				$headers[] = 'BCC: ' . $email['email_bcc'];
			}

			// Add Reply-To if present.
			if ( ! empty( $email['email_reply_to'] ) ) {
				$headers[] = 'Reply-To: ' . $email['email_reply_to'];
			}

			// Add Content-Type if HTML.
			if ( ! empty( $email['is_html'] ) && 1 === (int) $email['is_html'] ) {
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
			}

			// Parse attachments from JSON.
			$attachments = array();
			if ( ! empty( $email['attachments'] ) ) {
				$attachments_data = \json_decode( $email['attachments'], true );
				if ( \is_array( $attachments_data ) ) {
					foreach ( $attachments_data as $attachment ) {
						// Handle different attachment formats.
						if ( \is_string( $attachment ) && \file_exists( $attachment ) ) {
							$attachments[] = $attachment;
						} elseif ( \is_array( $attachment ) && isset( $attachment['path'] ) && \file_exists( $attachment['path'] ) ) {
							$attachments[] = $attachment['path'];
						}
					}
				}
			}

			// Attempt to resend the email.
			$result = \wp_mail( $to, $subject, $message, $headers, $attachments );

			// Redirect back with status.
			$redirect_url = \add_query_arg(
				array(
					'page'           => self::WP_MAIL_MENU_SLUG,
					'resend_status'  => $result ? 'success' : 'failed',
					'resent_mail_id' => $mail_id,
				),
				\admin_url( 'admin.php' )
			);

			\wp_safe_redirect( $redirect_url );
			exit;
		}

		/**
		 * Displays admin notice after email resend attempt
		 *
		 * @return void
		 *
		 * @since 4.8.0
		 */
		public static function display_resend_notice() {
			// Only show on mail list page.
			if ( ! isset( $_GET['page'] ) || self::WP_MAIL_MENU_SLUG !== $_GET['page'] ) {
				return;
			}

			if ( ! isset( $_GET['resend_status'] ) ) {
				return;
			}

			$status  = \sanitize_text_field( \wp_unslash( $_GET['resend_status'] ) );
			$mail_id = isset( $_GET['resent_mail_id'] ) ? \absint( $_GET['resent_mail_id'] ) : 0;

			if ( 'success' === $status ) {
				echo '<div class="notice notice-success is-dismissible"><p>';
				echo \esc_html( \sprintf( \__( 'Email #%d has been successfully resent.', '0-day-analytics' ), $mail_id ) );
				echo '</p></div>';
			} elseif ( 'failed' === $status ) {
				echo '<div class="notice notice-error is-dismissible"><p>';
				echo \esc_html( \sprintf( \__( 'Failed to resend email #%d. Please check your mail configuration.', '0-day-analytics' ), $mail_id ) );
				echo '</p></div>';
			}
		}

		/**
		 * Adds the module to the main plugin menu
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public static function menu_add() {
			$wp_mail_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'Mail viewer', '0-day-analytics' ),
				'manage_options', // Tightened capability requirement to protect potentially sensitive mail log data.
				self::WP_MAIL_MENU_SLUG,
				array( WP_Mail_View::class, 'analytics_wp_mail_page' ),
				4
			);

			self::add_screen_options( $wp_mail_hook );

			\add_filter( 'manage_' . $wp_mail_hook . '_columns', array( self::class, 'manage_columns' ) );

			\add_action( 'load-' . $wp_mail_hook, array( Settings::class, 'aadvana_common_help' ) );
			// Process actions early to ensure redirects happen before output.
			\add_action( 'load-' . $wp_mail_hook, array( self::class, 'process_actions_load' ) );
		}

		/**
		 * Handle actions during the early page load hook to avoid header warnings.
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
			// Actions are processed on load-<hook> to prevent late redirects.

			// Vars.
			$search   = self::escaped_search_input();
			$per_page = ! empty( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : self::get_screen_option_per_page();
			$orderby  = ! empty( $_GET['orderby'] ) ? \esc_sql( \wp_unslash( $_GET['orderby'] ) ) : '';
			$order    = ! empty( $_GET['order'] ) ? \esc_sql( \wp_unslash( $_GET['order'] ) ) : 'DESC';
			$page     = $this->get_pagenum();
			$offset   = $per_page * ( $page - 1 );
			// $pages    = ceil( $this->count / $per_page );
			// $one_page = ( 1 === $pages ) ? 'one-page' : '';
			$type = ! empty( $_GET['mail_type'] ) ? \sanitize_text_field( \wp_unslash( $_GET['mail_type'] ) ) : '';

			if ( isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ) {
				if ( -1 === (int) $_REQUEST['plugin'] ) {
					$plugin = -1;
				} else {
					$plugin = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin'] ) );
				}
			} else {
				$plugin = '';
			}

			if ( isset( $_REQUEST['site_id'] ) && ! empty( $_REQUEST['site_id'] ) ) {
				if ( -1 === (int) $_REQUEST['site_id'] ) {
					$site_id = -1;
				} else {
					$site_id = \absint( $_REQUEST['site_id'] );
				}
			} else {
				$site_id = '';
			}

			if ( isset( $type ) && ! empty( $type ) ) {
				$site_id = -1;
			}

			$items = $this->fetch_table_data(
				array(
					'search'   => $search,
					'offset'   => $offset,
					'per_page' => $per_page,
					'orderby'  => $orderby,
					'order'    => $order,
					'type'     => $type,
					'site_id'  => $site_id,
					'plugin'   => $plugin,
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
			$first6_columns = array_keys( WP_Mail_Entity::get_column_names_admin() );

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
		 *
		 * @return  Array
		 */
		public function fetch_table_data( array $args = array() ) {

			global $wpdb;

			// Parse.
			$parsed_args = \wp_parse_args(
				$args,
				array(
					'offset'   => 0,
					'per_page' => self::get_screen_option_per_page(),
					'search'   => '',
					'orderby'  => 'id',
					'order'    => 'DESC',
					'count'    => false,
					'site_id'  => '',
					'plugin'   => '',
				)
			);

			$search_sql  = '';
			$where_parts = array();
			$where_args  = array();

			$orderby = \esc_sql( \sanitize_text_field( \wp_unslash( $parsed_args['orderby'] ) ) );
			if ( empty( $orderby ) ) {
				$orderby = 'id';
			}
			$order = $wpdb->esc_like( \sanitize_text_field( \wp_unslash( $parsed_args['order'] ) ) );

			$order   = self::get_order( $order );
			$orderby = self::get_order_by( $orderby );

			$wpdb_table = $this->get_table_name();

			// Build WHERE conditions (shared between queries).
			$search_string = \sanitize_text_field( \wp_unslash( $parsed_args['search'] ) );
			$site_id       = \sanitize_text_field( \wp_unslash( (string) $parsed_args['site_id'] ) );
			$type          = \sanitize_text_field( \wp_unslash( $parsed_args['type'] ?? '' ) );
			$plugin        = \sanitize_text_field( \wp_unslash( (string) ( $parsed_args['plugin'] ?? '' ) ) );

			if ( '' !== $search_string ) {
				$like         = '%' . $wpdb->esc_like( $search_string ) . '%';
				$like_clauses = array();
				$columns      = array_keys( WP_Mail_Entity::get_all_columns() );
				$columns      = array_unique( array_merge( array( 'id' ), $columns ) );
				foreach ( $columns as $col ) {
					$like_clauses[] = "$col LIKE %s";
					$where_args[]   = $like;
				}
				if ( ! empty( $like_clauses ) ) {
					$where_parts[] = 'AND (' . implode( ' OR ', $like_clauses ) . ')';
				}
			}

			if ( '' !== $site_id && -1 !== (int) $site_id ) {
				$where_parts[] = 'AND blog_id = %d';
				$where_args[]  = (int) $site_id;
			} elseif ( ( '' === $site_id && -1 !== (int) $site_id ) && WP_Helper::is_multisite() && ! \is_main_site() ) {
				$where_parts[] = 'AND blog_id = %d';
				$where_args[]  = (int) \get_current_blog_id();
			}

			if ( ! empty( $type ) ) {
				if ( 'successful' === $type ) {
					$where_parts[] = 'AND status = 1';
				}
				if ( 'unsuccessful' === $type ) {
					$where_parts[] = 'AND status = 0';
				}
				if ( 'html' === $type ) {
					$where_parts[] = 'AND is_html = 1';
				}
				if ( 'text' === $type ) {
					$where_parts[] = 'AND is_html != 1';
				}
				if ( 'attachments' === $type ) {
					$where_parts[] = 'AND attachments != "[]"';
				}
			}

			if ( '' !== $plugin && -1 !== (int) $plugin ) {
				$where_parts[] = 'AND plugin_slug = %s';
				$where_args[]  = $plugin;
			}

			if ( ! empty( $where_parts ) ) {
				$search_sql = ' ' . implode( ' ', $where_parts ) . ' ';
			}

			if ( ! isset( $parsed_args['all'] ) ) {

				$per_page = absint( $parsed_args['per_page'] );
				$offset   = absint( $parsed_args['offset'] );

				// $current_page = $this->get_pagenum();
				// if ( 1 < $current_page ) {
				// $offset = $per_page * ( $current_page - 1 );
				// } else {
				// $offset = 0;
				// }

				$query = 'SELECT
				' . implode( ', ', \array_keys( WP_Mail_Entity::get_fields() ) ) . '
			  FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql . ' ORDER BY ' . $orderby . ' ' . $order;

				if ( ! isset( $parsed_args['all'] ) ) {
					$query       .= ' LIMIT %d OFFSET %d';
					$where_args[] = $per_page;
					$where_args[] = $offset;
				}

				if ( ! empty( $where_args ) ) {
					$query = $wpdb->prepare( $query, $where_args );
				}
			} else {

				$query = 'SELECT
				' . implode( ', ', \array_keys( WP_Mail_Entity::get_fields() ) ) . '
			  FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql . ' ORDER BY ' . $orderby . ' ' . $order;
				if ( ! empty( $where_args ) ) {
					$query = $wpdb->prepare( $query, $where_args );
				}
			}

			// query output_type will be an associative array with ARRAY_A.
			$query_results = WP_Mail_Entity::get_results( $query );

			// Build count query with the same WHERE, excluding LIMIT args.
			$count_query = 'SELECT COUNT(id) FROM ' . $wpdb_table . '  WHERE 1=1 ' . $search_sql;
			$count_args  = $where_args;
			if ( ! isset( $parsed_args['all'] ) && ! empty( $count_args ) ) {
				// Remove LIMIT and OFFSET from args (last two values).
				$count_args = array_slice( $count_args, 0, max( 0, count( $count_args ) - 2 ) );
			}
			if ( ! empty( $count_args ) ) {
				$count_query = $wpdb->prepare( $count_query, $count_args );
			}
			$this->count = WP_Mail_Entity::get_var( $count_query );

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
		 * Generates the table navigation above or below the table
		 *
		 * @param string $which - Holds info about the top and bottom navigation.
		 *
		 * @since 1.1.0
		 */
		public function display_tablenav( $which ) {
			if ( 'top' === $which ) {

				?>

				<style>
					.<?php echo \esc_attr( WP_Mail_Entity::get_table_name() ); ?> .late th:nth-child(1) {
						border-left: 7px solid #dd9192 !important;
					}
					.<?php echo esc_attr( WP_Mail_Entity::get_table_name() ); ?> .on-time th:nth-child(1) {
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
		 * @since 1.1.0
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

				case 'subject':
				case 'email_to':
				case 'email_from':
				case 'email_cc':
				case 'email_bcc':
				case 'email_reply_to':
					// Escape & wrap in <code> tag.
					if ( empty( $item[ $column_name ] ) ) {
						return '<span class="dashicons dashicons-minus" style="color:#ccc;"></span>';
					}
					return '<code>' . \esc_html( $item[ $column_name ] ) . '</code>';
				case 'backtrace_segment':
					$source_link = '';

					$query_array = array(
						'_wpnonce' => \wp_create_nonce( 'source-view' ),
						'action'   => 'log_source_view',
					);

					$source = \json_decode( $item['backtrace_segment'], true );

					if ( isset( $source['file'] ) ) {
						$item['error_file'] = $source['file'];
						if ( isset( $source['line'] ) ) {
							$item['error_line'] = $source['line'];
						}
					}
					unset( $source );

					if ( isset( $item['error_file'] ) && ! empty( $item['error_file'] ) ) {
						$query_array['error_file'] = $item['error_file'];

						if ( isset( $item['error_line'] ) && ! empty( $item['error_line'] ) ) {
							$query_array['error_line'] = $item['error_line'];
						}

						$query_array['TB_iframe'] = 'true';

						$view_url = \esc_url_raw(
							\add_query_arg( $query_array, \admin_url( 'admin-ajax.php' ) )
						);

						$title = __( 'Viewing: ', '0-day-analytics' ) . $item['error_file'];

						$source_link = '<div> <a href="' . \esc_url( $view_url ) . '" title = "' . \esc_attr( $title ) . '" class="thickbox view-source gray_lab badge">' . __( 'view mail source', '0-day-analytics' ) . '</a></div>';
					}

					if ( isset( $item['error_file'] ) ) {

						$plugins_dir_basename = basename( \WP_PLUGIN_DIR );

						if ( false !== \mb_strpos( $item['error_file'], $plugins_dir_basename . \DIRECTORY_SEPARATOR ) ) {

							$split_plugin = explode( \DIRECTORY_SEPARATOR, $item['error_file'] );

							$next        = false;
							$plugin_base = '';
							foreach ( $split_plugin as $part ) {
								if ( $next ) {
									$plugin_base = $part;
									break;
								}
								if ( $plugins_dir_basename === $part ) {
									$next = true;
								}
							}

							if ( isset( $plugin_base ) && ! empty( $plugin_base ) ) {

								$item['plugin'] = Plugin_Theme_Helper::get_plugin_from_path( $plugin_base );

								if ( isset( $item['plugin'] ) && ! empty( $item['plugin'] ) ) {
									return __( 'Plugin: ', '0-day-analytics' ) . '<b>' . \esc_html( $item['plugin']['Name'] ) . '</b><br>' . \__( 'Current version: ', '0-day-analytics' ) . \esc_html( $item['plugin']['Version'] ) . $source_link;
								}
							}
						}

						$theme = Plugin_Theme_Helper::get_theme_from_file_path( $item['error_file'] );

						if ( $theme ) {

							$name = $theme->get( 'Name' );

							$version = $theme->get( 'Version' );
							$version = ( ! empty( $version ) ) ? '<br>' . __( 'Current version: ', '0-day-analytics' ) . \esc_html( $version ) : '<br>' . __( 'Unknown version', '0-day-analytics' );

							$name = ( ( ! empty( $name ) ) ? \esc_html( $name ) : __( 'Unknown theme', '0-day-analytics' ) ) . $version;

							$parent = $theme->parent(); // ( 'parent_theme' );
							if ( $parent ) {
								$parent = $theme->parent()->get( 'Name' );

								$parent_version = $theme->parent()->get( 'Version' );
								$parent_version = ( ! empty( $parent_version ) ) ? \esc_html( $parent_version ) : __( 'Unknown version', '0-day-analytics' );

								$parent = ( ! empty( $parent ) ) ? '<div>' . __( 'Parent theme: ', '0-day-analytics' ) . \esc_html( $parent ) . '<br>' . __( 'Parent Current Version: ', '0-day-analytics' ) . $parent_version . '</div>' : '';
							}
							$name .= (string) $parent;

							return __( 'Theme: ', '0-day-analytics' ) . '<b>' . ( $name ) . '</b>' . $source_link;
						}

						if ( false !== \mb_strpos( $item['error_file'], ABSPATH . WPINC . \DIRECTORY_SEPARATOR ) ) {
							return '<span><span class="dashicons dashicons-wordpress" aria-hidden="true"></span> ' . __( 'WP Core', '0-day-analytics' ) . $source_link;
						}

						$admin_path = str_replace( \get_home_url( 1 ) . '/', ABSPATH, \network_admin_url() );

						if ( false !== \mb_strpos( $item['error_file'], $admin_path ) ) {
							return '<span><span class="dashicons dashicons-wordpress" aria-hidden="true"></span></span> ' . __( 'WP Admin Core', '0-day-analytics' ) . $source_link;
						} else {
							return ' ' . __( 'Source', '0-day-analytics' ) . $source_link;
						}
					}

					return '';

				case 'email_category':
					if ( empty( $item['email_category'] ) ) {
						return '<span class="badge" style="background:#999;">general</span>';
					}
					$colors = array(
						'order'         => '#4CAF50',
						'user'          => '#2196F3',
						'system'        => '#FF5722',
						'marketing'     => '#9C27B0',
						'notification'  => '#FF9800',
						'transactional' => '#00BCD4',
						'general'       => '#999',
					);
					$color  = isset( $colors[ $item['email_category'] ] ) ? $colors[ $item['email_category'] ] : '#999';
					return '<span class="badge" style="background:' . \esc_attr( $color ) . '; color:white; padding:3px 8px; border-radius:3px; font-size:11px;">' . \esc_html( $item['email_category'] ) . '</span>';

				case 'message_size':
				case 'total_size':
				case 'attachment_total_size':
					if ( empty( $item[ $column_name ] ) || 0 === (int) $item[ $column_name ] ) {
						return '<span class="dashicons dashicons-minus" style="color:#ccc;"></span>';
					}
					return \esc_html( \size_format( (int) $item[ $column_name ], 2 ) );

				case 'attachment_count':
					if ( empty( $item['attachment_count'] ) || 0 === (int) $item['attachment_count'] ) {
						return '<span class="dashicons dashicons-minus" style="color:#ccc;"></span>';
					}
					$count = (int) $item['attachment_count'];
					$size  = ! empty( $item['attachment_total_size'] ) ? ' (' . \size_format( (int) $item['attachment_total_size'], 2 ) . ')' : '';
					return '<span class="dashicons dashicons-paperclip"></span> ' . \esc_html( $count . $size );

				case 'delivery_time':
					if ( empty( $item['delivery_time'] ) || 0.0 === (float) $item['delivery_time'] ) {
						return '<span class="dashicons dashicons-minus" style="color:#ccc;"></span>';
					}
					$ms = round( (float) $item['delivery_time'] * 1000, 2 );
					return \esc_html( $ms . ' ms' );

				case 'can_resend':
					if ( isset( $item['can_resend'] ) && 1 === (int) $item['can_resend'] ) {
						return '<span class="dashicons dashicons-yes-alt" style="color:#46b450;" title="' . \esc_attr__( 'Can be resent', '0-day-analytics' ) . '"></span>';
					}
					return '<span class="dashicons dashicons-dismiss" style="color:#dc3232;" title="' . \esc_attr__( 'Contains time-sensitive content', '0-day-analytics' ) . '"></span>';

				case 'is_html':
					$icon = ( $item['is_html'] ) ? 'saved' : 'minus';
					return '<span><span class="dashicons dashicons-' . $icon . '" aria-hidden="true"></span></span>';

				case 'attachments':
					if ( ! \is_string( $item['attachments'] ) ) {
						return esc_html__( 'No', '0-day-analytics' );
					}
					$item['attachments'] = json_decode( $item['attachments'], true );
					if ( isset( $item['attachments'] ) && ! empty( $item['attachments'] ) && is_array( $item['attachments'] ) ) {
						$item['attachments'] = array_map(
							function ( $attachment ) {
								if ( ! isset( $attachment['id'] ) || -1 === $attachment['id'] ) {
									$attachment['note'] = __( 'Attachment not in media library', '0-day-analytics' );

									if ( empty( $attachment['src'] ) ) {
										$attachment['src'] = 'data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%201024%201024%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M832%20384H576V128H192v768h640V384zm-26.496-64L640%20154.496V320h165.504zM160%2064h480l256%20256v608a32%2032%200%200%201-32%2032H160a32%2032%200%200%201-32-32V96a32%2032%200%200%201%2032-32zm160%20448h384v64H320v-64zm0-192h160v64H320v-64zm0%20384h384v64H320v-64z%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E';
									}

									return $attachment;
								}

								if ( empty( $attachment['alt'] ) ) {
									$attachment['alt'] = $attachment['url'];
								}

								if ( empty( $attachment['src'] ) ) {
									$attachment['src'] = 'data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%201024%201024%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M832%20384H576V128H192v768h640V384zm-26.496-64L640%20154.496V320h165.504zM160%2064h480l256%20256v608a32%2032%200%200%201-32%2032H160a32%2032%200%200%201-32-32V96a32%2032%200%200%201%2032-32zm160%20448h384v64H320v-64zm0-192h160v64H320v-64zm0%20384h384v64H320v-64z%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E';
								}

								return $attachment;
							},
							$item['attachments']
						);
						// $item['attachments'] = array_column( $item['attachments'], 'url' );
						// $item['attachments'] = WP_Mail_Log::array_to_string(
						// $item['attachments'],
						// ' | '
						// );
					} else {
						$item['attachments'] = null;
					}
					if ( empty( $item['attachments'] ) ) {
						return esc_html__( 'No', '0-day-analytics' );
					} else {
						\ob_start();
						?>
						<ul>
							<?php
							foreach ( $item['attachments'] as $attachment ) {
								?>
									<li class="attachment-container">
										<?php
										if ( isset( $attachment['note'] ) ) {
											echo $attachment['note']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											?>
											<a href="<?php echo \esc_url( $attachment['url'] ); ?>" alt="<?php echo \esc_attr( $attachment['alt'] ); ?>" title="<?php echo \esc_attr( $attachment['alt'] ); ?>" target="_blank"
											class="attachment-item"
											style=" display:block; width:35px; height: 35px; background: url(<?php echo \esc_url( $attachment['src'] ); ?>) no-repeat; background-size: contain;"></a>
											<?php
											continue;
										}

										if ( \wp_attachment_is_image( $attachment['id'] ) ) {
											?>

											<a href="<?php echo \esc_url( $attachment['url'] ); ?>" alt="<?php echo \esc_attr( $attachment['alt'] ); ?>" title="<?php echo \esc_attr( $attachment['alt'] ); ?>" target="_blank"><img src="<?php echo \esc_url( $attachment['url'] ); ?>" alt="<?php echo \esc_attr( $attachment['alt'] ); ?>" title="<?php echo \esc_attr( $attachment['alt'] ); ?>" 
											class="attachment-item"
											style=" display:block; width:35px; height: 35px;"/></a>
											<?php
										} else {
											?>

										<a href="<?php echo \esc_url( $attachment['url'] ); ?>" alt="<?php echo \esc_attr( $attachment['alt'] ); ?>" title="<?php echo \esc_attr( $attachment['alt'] ); ?>" target="_blank"
											class="attachment-item"
											style=" display:block; width:35px; height: 35px; background: url(<?php echo \esc_url( $attachment['src'] ); ?>) no-repeat; background-size: contain;"></a>
											<?php } ?>
									</li>
								<?php } ?>
							</ul>
							<?php

							return \ob_get_clean();
					}
				case 'time':
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

					$actions['delete'] = '<a class="aadvana-transient-delete" href="' . \esc_url( $delete_url ) . '" onclick="return confirm(\'' . \esc_html__( 'You sure you want to delete this record?', '0-day-analytics' ) . '\');">' . \esc_html__( 'Delete', '0-day-analytics' ) . '</a>';

					// Add resend action if email can be resent.
					if ( isset( $item['can_resend'] ) && 1 === (int) $item['can_resend'] ) {
						$resend_url        = \add_query_arg(
							array(
								'action'   => 'resend_email',
								'mail_id'  => $item['id'],
								'_wpnonce' => \wp_create_nonce( 'resend_email_' . $item['id'] ),
							),
							\admin_url( 'admin-post.php' )
						);
						$actions['resend'] = '<a href="' . \esc_url( $resend_url ) . '" onclick="return confirm(\'' . \esc_html__( 'Resend this email?', '0-day-analytics' ) . '\');" style="color:#46b450;">' . \esc_html__( 'Resend', '0-day-analytics' ) . '</a>';
					}

					$actions['details'] = '<a href="#" class="aadvan-request-show-details" data-details-id="' . \esc_attr( (string) $item['id'] ) . '">' . \esc_html__( 'Details', '0-day-analytics' ) . '</a>';

					$data = '';

					if ( 0 === (int) $item['status'] ) {
						$data = '<div>' . __( 'Error occurred:', '0-day-analytics' ) . '<br><span class="badge dark-badge" style="color: #ffb3b3 !important;">' . \esc_html( (string) $item['error'] ) . '</span></div>';
					}

					$time_format = 'g:i a';

					$item['date_added'] = (int) $item['time'];

					$mail_datetime_utc = \gmdate( 'Y-m-d H:i:s', $item['date_added'] );

					$timezone_local  = \wp_timezone();
					$mail_local      = \get_date_from_gmt( $mail_datetime_utc, 'Y-m-d' );
					$today_local     = ( new \DateTimeImmutable( 'now', $timezone_local ) )->format( 'Y-m-d' );
					$tomorrow_local  = ( new \DateTimeImmutable( 'tomorrow', $timezone_local ) )->format( 'Y-m-d' );
					$yesterday_local = ( new \DateTimeImmutable( 'yesterday', $timezone_local ) )->format( 'Y-m-d' );

					// If the offset of the date of the event is different from the offset of the site, add a marker.
					if ( \get_date_from_gmt( $mail_datetime_utc, 'P' ) !== get_date_from_gmt( 'now', 'P' ) ) {
						$time_format .= ' (P)';
					}

					$mail_time_local = \get_date_from_gmt( $mail_datetime_utc, $time_format );

					if ( $mail_local === $today_local ) {
						$date = sprintf(
						/* translators: %s: Time */
							__( 'Today at %s', '0-day-analytics' ),
							$mail_time_local,
						);
					} elseif ( $mail_local === $tomorrow_local ) {
						$date = sprintf(
						/* translators: %s: Time */
							__( 'Tomorrow at %s', '0-day-analytics' ),
							$mail_time_local,
						);
					} elseif ( $mail_local === $yesterday_local ) {
						$date = sprintf(
						/* translators: %s: Time */
							__( 'Yesterday at %s', '0-day-analytics' ),
							$mail_time_local,
						);
					} else {
						$date = sprintf(
						/* translators: 1: Date, 2: Time */
							__( '%1$s at %2$s', '0-day-analytics' ),
							\get_date_from_gmt( $mail_datetime_utc, 'F jS' ),
							$mail_time_local,
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

				case 'blog_id':
					if ( WP_Helper::is_multisite() && 1 !== (int) $item['blog_id'] ) {
						$site = \get_site( (int) $item['blog_id'] );
						if ( $site ) {
							$blog_details = \get_blog_details( array( 'blog_id' => $item['blog_id'] ) );
							$name         = ( $blog_details ) ? $blog_details->blogname : \sprintf( /* translators: %s: Site ID */ __( 'Site %s', '0-day-analytics' ), (int) $item['blog_id'] );
							$details      = \sprintf(
								/* translators: 1: Site ID, 2: Site domain, 3: Site path */
								__( 'Site ID: %1$s, Domain: %2$s, Path: %3$s', '0-day-analytics' ),
								(int) $site->blog_id,
								$site->domain,
								$site->path
							);
							return '<a href="' . \esc_url( \get_admin_url( (int) $item['blog_id'] ) ) . '" title="' . \esc_attr( $details ) . '">' . \esc_html( $name ) . '</a>';
						} else {
							return \esc_html__( 'Unknown or deleted site', '0-day-analytics' );
						}
					} elseif ( WP_Helper::is_multisite() && 1 === (int) $item['blog_id'] ) {
						return \esc_html__( 'Main Site', '0-day-analytics' );
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
		 * @since 3.0.0
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<label class="screen-reader-text" for="%1$s">%2$s</label><input type="checkbox" name="advan_%3$s[]" id="%1$s" value="%4$s" />',
				\esc_attr( self::$table::get_name() . '_' . $item['id'] ),
				sprintf(
					// translators: The column name.
					__( 'Select %s', '0-day-analytics' ),
					'id'
				),
				\esc_attr( self::$table::get_name() ),
				\esc_attr( (string) $item['id'] )
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
								'paged'            => (int) ( $_REQUEST['paged'] ?? 1 ),
								'page'             => self::WP_MAIL_MENU_SLUG,
								'show_table'       => self::$table::get_name(),
							),
							\admin_url( 'admin.php' )
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

			// Plugin filter dropdown (mirrors Requests list behavior).
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
					<?php echo WP_Mail_Entity::get_all_plugins_dropdown( $plugin, $which ); ?>
				</div>
				<script>
					jQuery('form .plugin_filter').on('change', function(e) {
						jQuery('form .plugin_filter').val(jQuery(this).val());
						jQuery( this ).closest( 'form' )
							.attr( 'action', '<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>')
							.append('<input type="hidden" name="action" value="<?php echo \esc_attr( self::PLUGIN_FILTER_ACTION ); ?>">')
							.append('<input type="hidden" name="context" value="<?php echo \esc_attr( ( \is_network_admin() ) ? 'network' : 'site' ); ?>">')
							.append('<?php \wp_nonce_field( self::PLUGIN_FILTER_ACTION, self::PLUGIN_FILTER_ACTION . 'nonce' ); ?>')
							.submit();
					});
				</script>
			<?php

			if ( WP_Helper::is_multisite() ) {
				if ( isset( $_REQUEST['site_id'] ) && ! empty( $_REQUEST['site_id'] ) ) {
					if ( -1 === (int) $_REQUEST['site_id'] ) {
						$site_id = -1;
					} else {
						$site_id = \absint( $_REQUEST['site_id'] );
					}
				} else {
					$site_id = 0;

					if ( ! \is_main_site() ) {
						$site_id = \get_current_blog_id();
					}
				}

				$type = ! empty( $_GET['mail_type'] ) ? \sanitize_text_field( \wp_unslash( $_GET['mail_type'] ) ) : '';

				if ( isset( $type ) && ! empty( $type ) ) {
					$site_id = -1;
				}
				?>
				<div class="alignleft actions bulkactions">
					<?php echo WP_Mail_Entity::get_all_sites_dropdown( $site_id, $which ); ?>
				</div>
				<script>
					jQuery('form .site_id_filter').on('change', function(e) {
						jQuery('form .site_id_filter').val(jQuery(this).val());
						jQuery( this ).closest( 'form' ).attr( 'action', '<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>').append('<input type="hidden" name="action" value="<?php echo \esc_attr( self::SITE_ID_FILTER_ACTION ); ?>">').append('<input type="hidden" name="context" value="<?php echo \esc_attr( ( \is_network_admin() ) ? 'network' : 'site' ); ?>">').append('<?php \wp_nonce_field( self::SITE_ID_FILTER_ACTION, self::SITE_ID_FILTER_ACTION . 'nonce' ); ?>').submit();
					});
				</script>
				<?php
			}
			if ( 'top' === $which && count( $this->items ) > 0 ) {
				?>
				<div id="export-form">
					<div>
						<button id="start-export" class="button" data-type-export="mail" data-search="<?php echo self::escaped_search_input(); ?>" data-plugin="<?php echo \esc_attr( isset( $plugin ) ? (string) $plugin : '' ); ?>">
							<?php echo \esc_html__( 'CSV Export', '0-day-analytics' ); ?>
						</button>
						<button id="cancel-export" class="button cancel-btn" style="display:none;">
							<?php echo \esc_html__( 'Cancel', '0-day-analytics' ); ?>
						</button>
					</div>

					<div id="progress-container" class="progress-wrap" style="display:none;">
						<div id="progress-bar"></div>
					</div>

					<p id="progress-text" style="display:none;"><?php echo \esc_html__( 'Waiting to start...', '0-day-analytics' ); ?></p>
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
			return __( 'Number of mails to show', '0-day-analytics' );
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

				$admin_columns = WP_Mail_Entity::get_column_names_admin();

				$screen_options = $admin_columns;

				$table_columns = array(
					'cb' => '<input type="checkbox" />', // to display the checkbox.
				);

				self::$admin_columns = \array_merge( $table_columns, $screen_options, $columns );
			}

			return self::$admin_columns;
		}

		/**
		 * Retrieves mail body by given record ID.
		 *
		 * @param \WP_REST_Request $request - The request object.
		 *
		 * @return \WP_REST_Response|\WP_Error|string
		 *
		 * @since 3.0.0
		 */
		public static function get_mail_body_api( \WP_REST_Request $request ) {
			// Basic capability check to protect sensitive mail content. Adjust capability if plugin defines custom caps.
			if ( ! \current_user_can( 'manage_options' ) ) {
				return new \WP_Error(
					'insufficient_permissions',
					__( 'You do not have permission to view this resource.', '0-day-analytics' ),
					array( 'status' => 403 )
				);
			}

			$id = abs( (int) $request->get_param( 'id' ) );

			$record = WP_Mail_Entity::load( 'id=%d', array( $id ) );

			$message = '<table class="widefat striped table-view-list" style="max-width:100%;table-layout: fixed;">
				<thead>
					<tr>
						<th>
							' . \esc_html__( 'Mail Content', '0-day-analytics' ) . '
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>';
			if ( is_array( $record ) && ! empty( $record ) ) {
				if ( isset( $record['is_html'] ) && (bool) $record['is_html'] ) {
					$message .= WP_Mail_Log::filter_html( $record['message'] );
				} else {
					$message .= \nl2br( $record['message'] );

				}

				$attachments = '';

				$record['attachments'] = json_decode( (string) $record['attachments'], true );
				if ( isset( $record['attachments'] ) && ! empty( $record['attachments'] ) && is_array( $record['attachments'] ) ) {
					$record['attachments'] = array_map(
						function ( $attachment ) {
							if ( ! isset( $attachment['id'] ) || -1 === $attachment['id'] ) {
								$attachment['note'] = __( 'Attachment not in media library', '0-day-analytics' );

								if ( empty( $attachment['src'] ) ) {
									$attachment['src'] = 'data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%201024%201024%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M832%20384H576V128H192v768h640V384zm-26.496-64L640%20154.496V320h165.504zM160%2064h480l256%20256v608a32%2032%200%200%201-32%2032H160a32%2032%200%200%201-32-32V96a32%2032%200%200%201%2032-32zm160%20448h384v64H320v-64zm0-192h160v64H320v-64zm0%20384h384v64H320v-64z%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E';
								}

								return $attachment;
							}

							if ( empty( $attachment['alt'] ) ) {
								$attachment['alt'] = $attachment['url'];
							}

							if ( empty( $attachment['src'] ) ) {
								$attachment['src'] = 'data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%201024%201024%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M832%20384H576V128H192v768h640V384zm-26.496-64L640%20154.496V320h165.504zM160%2064h480l256%20256v608a32%2032%200%200%201-32%2032H160a32%2032%200%200%201-32-32V96a32%2032%200%200%201%2032-32zm160%20448h384v64H320v-64zm0-192h160v64H320v-64zm0%20384h384v64H320v-64z%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E';
							}

							return $attachment;
						},
						$record['attachments']
					);

				} else {
					$record['attachments'] = null;
				}
				if ( ! empty( $record['attachments'] ) ) {
					\ob_start();
					?>
						<ul>
						<?php
						foreach ( $record['attachments'] as $attachment ) {
							?>
							<li class="attachment-container">
									<?php
									if ( isset( $attachment['note'] ) ) {
										echo $attachment['note']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
										<a href="<?php echo \esc_url_raw( $attachment['url'] ); ?>" alt="<?php echo \esc_html( $attachment['alt'] ); ?>" title="<?php echo \esc_html( $attachment['alt'] ); ?>" target="_blank"
											class="attachment-item"
											style=" display:block; width:35px; height: 35px; background: url(<?php echo \esc_url_raw( $attachment['src'] ); ?>) no-repeat; background-size: contain;"></a>
											<?php
											continue;
									}

									if ( \wp_attachment_is_image( $attachment['id'] ) ) {
										?>

										<a href="<?php echo \esc_url_raw( $attachment['url'] ); ?>" alt="<?php echo \esc_html( $attachment['alt'] ); ?>" title="<?php echo \esc_html( $attachment['alt'] ); ?>" target="_blank"><img src="<?php echo \esc_url_raw( $attachment['url'] ); ?>" alt="<?php echo \esc_attr( $attachment['alt'] ); ?>" title="<?php echo \esc_attr( $attachment['alt'] ); ?>" 
											class="attachment-item"
											style=" display:block; width:50px; height: 50px;"/></a>
											<?php
									} else {
										?>

										<a href="<?php echo \esc_url_raw( $attachment['url'] ); ?>" alt="<?php echo \esc_html( $attachment['alt'] ); ?>" title="<?php echo \esc_html( $attachment['alt'] ); ?>" target="_blank"
											class="attachment-item"
											style=" display:block; width:50px; height: 50px; background: url(<?php echo \esc_url_raw( $attachment['src'] ); ?>) no-repeat; background-size: contain;"></a>
											<?php } ?>
							</li>
								<?php } ?>
						</ul>
							<?php

							$attachments = \ob_get_clean();
				}
				$message .= '</td></tr></tbody></table>';

				return rest_ensure_response(
					array(
					'success'                        => true,
					'mail_body'                      => $message,
					'email_to'                       => $record['email_to'],
					'email_from'                     => $record['email_from'],
					'email_cc'                       => $record['email_cc'] ?? '',
					'email_bcc'                      => $record['email_bcc'] ?? '',
					'email_reply_to'                 => $record['email_reply_to'] ?? '',
					'subject'                        => $record['subject'],
					'email_category'                 => $record['email_category'] ?? 'general',
					'additional_headers'             => $record['additional_headers'] ?? esc_html__( 'No additional headers', '0-day-analytics' ),
					'message_size'                   => $record['message_size'] ?? 0,
					'message_size_formatted'         => ! empty( $record['message_size'] ) ? size_format( (int) $record['message_size'] ) : '',
					'total_size'                     => $record['total_size'] ?? 0,
					'total_size_formatted'           => ! empty( $record['total_size'] ) ? size_format( (int) $record['total_size'] ) : '',
					'attachment_count'               => $record['attachment_count'] ?? 0,
					'attachment_total_size'          => $record['attachment_total_size'] ?? 0,
					'attachment_total_size_formatted' => ! empty( $record['attachment_total_size'] ) ? size_format( (int) $record['attachment_total_size'] ) : '',
					'delivery_time'                  => $record['delivery_time'] ?? 0,
					'can_resend'                     => $record['can_resend'] ?? 0,
					'attachments'                    => $attachments,
					)
				);

			} else {
				return new \WP_Error(
					'empty_mail_record',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Display the list of hook types.
		 *
		 * @return array<string,string>
		 *
		 * @since 3.5.0
		 */
		public function get_views() {

			$views      = array();
			$hooks_type = ( $_REQUEST['mail_type'] ) ?? '';

			$types = array(
				'successful'   => __( 'Successful', '0-day-analytics' ),
				'unsuccessful' => __( 'Unsuccessful', '0-day-analytics' ),
				'html'         => __( 'HTML', '0-day-analytics' ),
				'text'         => __( 'Text', '0-day-analytics' ),
				'attachments'  => __( 'With attachments', '0-day-analytics' ),
			// 'url'      => __( 'URL events', '0-day-analytics' ),
			);

			$url = \add_query_arg(
				array(
					'page'      => self::WP_MAIL_MENU_SLUG,
					'mail_type' => 'all',
				),
				\admin_url( 'admin.php' )
			);

			$all_mails = $this->fetch_table_data( array( 'all' => true ) );

			$views['all'] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
				\esc_url( $url ),
				'all' === $hooks_type ? ' class="current"' : '',
				\esc_html__( 'All mails (no filters)', '0-day-analytics' ),
				\esc_html( \number_format_i18n( count( $all_mails ) ) )
			);

			$filtered = self::get_filtered_mails( $all_mails );

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
						'page'             => self::WP_MAIL_MENU_SLUG,
						self::SEARCH_INPUT => self::escaped_search_input(),
						'mail_type'        => $key,
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
		 * Returns mails filtered by various parameters
		 *
		 * @param array<string,stdClass> $mails The list of all events.
		 * @return array<string,array<string,stdClass>> Array of filtered events keyed by filter name.
		 *
		 * @since 3.3.1
		 */
		public static function get_filtered_mails( array $mails ) {

			$filtered['successful'] = array_filter(
				$mails,
				function ( $mail ) {
					return ( 1 === (int) $mail['status'] );
				}
			);

			$filtered['unsuccessful'] = array_filter(
				$mails,
				function ( $mail ) {
					return ( 0 === (int) $mail['status'] );
				}
			);

			$filtered['html'] = array_filter(
				$mails,
				function ( $mail ) {
					return ( 1 === (int) $mail['is_html'] );
				}
			);

			$filtered['text'] = array_filter(
				$mails,
				function ( $mail ) {
					return ( 0 === (int) $mail['is_html'] );
				}
			);

			$filtered['attachments'] = array_filter(
				$mails,
				function ( $mail ) {
					return ( '[]' !== $mail['attachments'] );
					// $mail['attachments'] = json_decode( $mail['attachments'], true );
					// return (
					// ( isset( $mail['attachments'] ) && ! empty( $mail['attachments'] ) && is_array( $mail['attachments'] ) ) );
				}
			);

			return $filtered;
		}
	}
}
