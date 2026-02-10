<?php

namespace Kaizencoders\Utilitify\Admin;

use Kaizencoders\Utilitify\Helper;
use Kaizencoders\Utilitify\Models\Link;

/**
 * Class Links_Table
 *
 * @since 1.0.0
 * @package Kaizencoders\Utilitify\Admin
 *
 */
class Request_404_Table extends UF_List_Table {
	/**
	 * @since 1.0.0
	 * @var string
	 *
	 */
	public static $option_per_page = 'uf_404_requests_per_page';

	/**
	 * @var Link
	 */
	public $db;

	/**
	 * Group ID Name Map
	 *
	 * @since 1.3.7
	 */
	public $group_id_name_map;

	/**
	 * Links_Table constructor.
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( '404 Request', 'utilitify' ), //singular name of the listed records
			'plural'   => __( '404 Requests', 'utilitify' ), //plural name of the listed records
			'ajax'     => false, //does this table support ajax?
			'screen'   => 'uf_404_requests'
		) );

		$this->db = new Link();
	}

	/**
	 * Add Screen Option
	 *
	 * @since 1.0.0
	 */
	public static function screen_options() {

		$action = Helper::get_request_data( 'action' );

		if ( ! ( 'new' === $action || 'edit' === $action ) ) {

			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Number of Requests per page', 'utilitify' ),
				'default' => 10,
				'option'  => self::$option_per_page
			);

			add_screen_option( $option, $args );
		}

	}

	/**
	 * Render links page
	 *
	 * @since 1.0.0
	 */
	public function render() {

		try {

			$template_data = array(
				'object' => $this,
				'title'  => __( '404 Requests', 'utilitify' )
			);

			ob_start();

			include KC_UF_ADMIN_TEMPLATES_DIR . '/404-requests.php';


		} catch ( \Exception $e ) {

		}

	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {

		$columns = array(
			'link'       => __( 'URL', 'utilitify' ),
			'created_at' => __( 'Created At', 'utilitify' ),
		);

		$columns = apply_filters( 'kc_uf_filter_requests_columns', $columns );

		return $columns;
	}

	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string|void
	 *
	 * @since 1.0.0
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'link':
				return esc_url( Helper::get_data( $item, 'link', '' ) );
				break;
			case 'created_at':
				return Helper::format_date_time( Helper::get_data( $item, 'created_at', '' ) );
				break;
			default:
				return '';
		}
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'link'       => array( 'URL', true ),
			'created_at' => array( 'created_at', true )
		);

	}

	/**
	 * @param int $per_page
	 * @param int $page_number
	 * @param bool $do_count_only
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function get_lists( $per_page = 10, $page_number = 1, $do_count_only = false ) {

		$order_by = sanitize_sql_orderby( Helper::get_request_data( 'orderby' ) );
		$order    = Helper::get_request_data( 'order' );
		$search   = Helper::get_request_data( 's' );

		$query = KC_UF()->query->table( 'kc_uf_links' );

		if ( ! empty( $search ) ) {
			$query->where( 'link', 'like', '%' . $search . '%' );
		}

		if ( ! $do_count_only ) {
			$order = ! empty( $order ) ? strtolower( $order ) : 'desc';

			$expected_order_values = array( 'asc', 'desc' );
			if ( ! in_array( $order, $expected_order_values ) ) {
				$order = 'desc';
			}

			$default_order_by = esc_sql( 'created_at' );

			$expected_order_by_values = array( 'name', 'created_at' );

			if ( ! in_array( $order_by, $expected_order_by_values ) ) {
				$query->orderBy( $default_order_by, 'DESC' );
			} else {
				$order_by = esc_sql( $order_by );

				$query->orderBy( $order_by, $order )->orderBy( $default_order_by, 'DESC' );
			}

			$offset = ( $page_number - 1 ) * $per_page;

			$query->offset( $offset )->limit( $per_page );

			$result = $query->get();

			$result = array_map( function ( $value ) {
				return (array) $value;
			}, $result );

		} else {
			$result = $query->count();
		}

		return $result;
	}

	public function search_box( $text, $input_id ) {
		?>

        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( __( 'Search Requests', 'utilitify' ), 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * No items
	 *
	 * @since 1.0.0
	 */
	public function no_items() { ?>
        <div class="block ml-auto mr-auto" style="width:50%;">
            <img src="<?php echo KC_UF_PLUGIN_ASSETS_DIR_URL . '/images/empty.svg'?>" />
        </div>

	<?php }
}