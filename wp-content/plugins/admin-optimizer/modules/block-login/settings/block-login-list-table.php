<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Block_Login_List_Table extends \WP_List_Table {
	public $columns = array();

	// the number of items for each table page.
	public $post_per_page = 20;
	/**
	 * Construct the extended class
	 */
	public function __construct() {
		parent::__construct(
			array(
				'plural'   => 'lockouts',
				'singular' => 'lockout',
				'ajax'     => true,
			)
		);
	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_columns() {

		$columns = [
			'ip_address'       => __( 'IP Address', 'admin-optimizer' ),
			'username'         => __( 'Username', 'admin-optimizer' ),
			'lockout_count'    => __( 'Lockout Count', 'admin-optimizer' ),
			'lockout_time'     => __( 'Lockout Start Time', 'admin-optimizer' ),
			'release_time'     => __( 'Lockout End Time', 'admin-optimizer' ),
			'lockout_duration' => __( 'Lockout Duration', 'admin-optimizer' ),
			'status'           => __( 'Status', 'admin-optimizer' ),
			'action'           => __( 'Action', 'admin-optimizer' ),
		];

		return $columns;
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->post_per_page;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = [];

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */

		$data = $this->get_data();
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ),   // WE have to calculate the total number of pages.
			)
		);
	}

	function get_data() {
		return Block_Login_DB::get_ids();
	}

	function single_row( $id ) {
		$locked_record = Block_Login_DB::get_record_by_id( $id );

		$item['id']               = $locked_record->ID;
		$item['ip_address']       = $locked_record->ip_address;
		$item['username']         = $locked_record->username;
		$item['lockout_count']    = $locked_record->lockout_count;
		$item['lockout_time']     = '<div id="lockout-time-' . $locked_record->ID . '">' . wp_date(
			'Y-m-d H:i:s',
			$locked_record->lockout_time
		) . '</div>';
		$release_time             = ! empty( $locked_record->release_time ) ? wp_date(
			'Y-m-d H:i:s',
			$locked_record->release_time
		) : '-';
		$item['release_time']     = '<div id="release-time-' . $locked_record->ID . '">' . $release_time . '</div>';
		$item['lockout_duration'] = '<div id="lockout-duration-' . $locked_record->ID . '">' . human_time_diff(
			1,
			$locked_record->lockout_duration
		) . '</div>';
		$item['status']           = '<div id="status-' . $locked_record->ID . '">' . ucfirst( $locked_record->status ) . '</div>';
		$item['action'] = '';
		if ( 'locked' === $locked_record->status ) {
			$item['action'] = '<button class="ip-action-btn button button-primary btn-release" data-ipid="' .
				$locked_record->ID . '" data-action="release">' . __( 'Release lock', 'admin-optimizer' ) . '</button>';
		}

		?>
		<tr id="locked-ip-<?php echo esc_attr( $locked_record->ID ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
	}
}