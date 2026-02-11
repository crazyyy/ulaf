<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Post_Status_List_Table class
 */
class Post_Status_List_Table extends \WP_List_Table {
	/**
	 * Columns in table
	 *
	 * @var array
	 */
	public $columns = array();

	/**
	 * The number of items for each table page
	 *
	 * @var int
	 */
	public $post_per_page = 20;

	/**
	 * Construct the extended class
	 */
	public function __construct() {
		parent::__construct(
			array(
				'plural'   => 'custom_statuses',
				'singular' => 'custom_status',
				'ajax'     => true,
			)
		);
	}

	/**
	 * Columb for checkbox
	 *
	 * @param array $item  Item to render in the column.
	 *
	 * @return string|void
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			'delete_poststatus',
			/*$2%s*/
			$item['id'] // The value of the checkbox should be the record's id.
		);
	}

	/**
	 * Render details for the "name" column
	 *
	 * @param array $item  Item to be rendered.
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$name = $item['name'];

		$output            = sprintf(
			'<strong><a href="%1$s">%2$s</a></strong>',
			$this->generate_link(
				[
					'action'  => 'edit',
					'term_id' => $item['id'],
					'nonce'   => wp_create_nonce( 'edit-post-status_' . $item['id'] ),
				]
			),
			$name
		);
		$actions           = [];
		$actions['edit']   = sprintf(
			'<a href="%s">' . __( 'Edit', 'admin-optimizer' ) . '</a>',
			$this->generate_link(
				[
					'action'  => 'edit',
					'term_id' => $item['id'],
					'nonce'   => wp_create_nonce( 'edit-post-status_' . $item['id'] ),
				]
			)
		);
		$actions['delete'] = sprintf(
			'<a href="%s">' . __( 'Delete', 'admin-optimizer' ) . '</a>',
			$this->generate_link(
				[
					'action'  => 'delete',
					'term_id' => $item['id'],
					'nonce'   => wp_create_nonce( 'delete-post-status_' . $item['id'] ),
				]
			)
		);

		$output .= $this->row_actions( $actions );
		$output .= '<div class="hidden" id="inline_' . esc_attr( $item['id'] ) . '">';
		$output .= '<div class="name">' . esc_html( $item['name'] ) . '</div>';
		$output .= '<div class="description">' . esc_html( $item['description'] ) . '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Generate quick action link
	 *
	 * @param array $query Item to append link to.
	 *
	 * @return string
	 */
	public function generate_link( array $query ) {
		$default = [
			'page'    => Post_Status::MENU_SLUG,
			'action'  => 'add',
			'term_id' => 0,
			'nonce'   => wp_create_nonce( 'adminoptimizer-post-status' ),
		];
		$query   = shortcode_atts( $default, $query );
		return esc_url( add_query_arg( $query, admin_url( 'admin.php' ) ), null, '&' );
	}

	/**
	 * Render details for default column
	 *
	 * @param array  $item  Item to be rendered.
	 * @param string $column_name  Name of column.
	 *
	 * @return mixed|void
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Get bulk actions
	 *
	 * @return string[]
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete',
		];
		return $actions;
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
	public function get_columns() {

		$columns = [
			'cb'          => '<input type="checkbox" />', // Render a checkbox instead of text.
			'name'        => __( 'Name', 'admin-optimizer' ),
			'description' => __( 'Description', 'admin-optimizer' ),
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
	public function prepare_items() {
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
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		// $this->process_bulk_action();

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
			[
				'total_items' => $total_items,                  // WE have to calculate the total number of items.
				'per_page'    => $per_page,                     // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ),   // WE have to calculate the total number of pages.
			]
		);
	}

	/**
	 * Get data to be rendered in the table
	 *
	 * @return int[]|string|string[]|\WP_Error|\WP_Term[]
	 */
	public function get_data() {
		return Post_Status::get_custom_post_statuses( [ 'fields' => 'ids' ] );
	}

	/**
	 * Handle single row data
	 *
	 * @param int $term_id  Term ID.
	 *
	 * @return void
	 */
	public function single_row( $term_id ) {
		$post_status = get_term( $term_id );

		$item['id']          = $post_status->term_id;
		$item['name']        = $post_status->name;
		$item['description'] = $post_status->description;
		?>
		<tr id="poststatus-<?php echo esc_attr( $post_status->term_id ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
	}
}