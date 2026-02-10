<?php
/**
 * Abstract list table class for common list functionality.
 *
 * @package advanced-analytics
 * @subpackage lists
 * @since 4.4.1
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

declare(strict_types=1);

namespace ADVAN\Lists;

use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Traits\List_Trait;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/template.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table-compat.php';
	require_once ABSPATH . 'wp-admin/includes/list-table.php';
}

/**
 * Abstract base class for list tables.
 *
 * Provides common functionality for list tables in the plugin.
 *
 * @since 4.4.1
 */
abstract class Abstract_List extends \WP_List_Table {

	use List_Trait;

	/**
	 * Table name for the list.
	 *
	 * @var string
	 *
	 * @since 4.5.2
	 */
	protected static $table_name;

	/**
	 * Default constructor for list tables.
	 *
	 * @param array $args Arguments for the list table.
	 *
	 * @since 4.5.2
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'singular' => 'item',
			'plural'   => 'items',
			'ajax'     => true,
			'screen'   => WP_Helper::get_wp_screen(),
		);

		$args = wp_parse_args( $args, $defaults );

		parent::__construct( $args );

		if ( method_exists( $this, 'manage_columns' ) ) {
			static::$columns = static::manage_columns( array() );
		}
	}

	/**
	 * Manage columns for the table.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 *
	 * @since 4.5.2
	 */
	abstract public static function manage_columns( $columns ): array;

	/**
	 * Get default per page.
	 *
	 * @return int
	 *
	 * @since 4.5.2
	 */
	abstract public static function get_default_per_page(): int;

	/**
	 * Register cron job if applicable.
	 *
	 * @param array $crons Existing crons.
	 *
	 * @return array
	 *
	 * @since 4.5.2
	 */
	public static function add_cron_job( $crons ) {
		// Default implementation: no cron.
		return $crons;
	}

	/**
	 * Prepare items for display.
	 *
	 * @since 4.5.2
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->handle_table_actions();
		$this->fetch_table_data();

		$hidden = get_user_option( 'manage' . WP_Helper::get_wp_screen()->id . 'columnshidden', false );
		if ( ! $hidden ) {
			$hidden = array();
		}

		$this->_column_headers = array( static::$columns, $hidden, $sortable );

		$this->set_pagination_args(
			array(
				'total_items' => $this->count,
				'per_page'    => self::get_screen_option_per_page(),
				'total_pages' => ceil( $this->count / self::get_screen_option_per_page() ),
			)
		);
	}

	/**
	 * Fetch table data. To be implemented by subclasses.
	 *
	 * @param array $args - Arguments for fetching data.
	 *
	 * @since 4.5.2
	 */
	abstract public function fetch_table_data( array $args = array() );

	/**
	 * Handle table actions. To be implemented by subclasses.
	 *
	 * @since 4.5.2
	 */
	abstract protected function handle_table_actions();
}
