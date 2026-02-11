<?php
/**
 * Results table for displaying hook search results.
 *
 * Extends WP_List_Table to provide a professional interface for displaying
 * hook search results with sorting, pagination, and action buttons.
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not already loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Hook Locator Results Table class.
 *
 * Provides a structured table interface for displaying hook search results
 * with proper WordPress admin styling and functionality.
 *
 * @since 1.0
 */
class Hook_Locator_Results_Table extends WP_List_Table {

	/**
	 * Search results data.
	 *
	 * @since 1.0
	 * @var   array
	 */
	private $data = array();

	/**
	 * Current hook name being searched.
	 *
	 * @since 1.0
	 * @var   string
	 */
	private $hook_name = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @param array  $data      Search results data.
	 * @param string $hook_name Hook name being searched.
	 */
	public function __construct( $data = array(), $hook_name = '' ) {
		$this->data      = $data;
		$this->hook_name = sanitize_text_field( $hook_name );

		parent::__construct(
			array(
				'singular' => 'hook_result',
				'plural'   => 'hook_results',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get table columns.
	 *
	 * @since 1.0
	 * @return array Associative array of column slugs and labels.
	 */
	public function get_columns() {
		return array(
			'file'   => __( 'File', 'hook-locator' ),
			'line'   => __( 'Line', 'hook-locator' ),
			'type'   => __( 'Type', 'hook-locator' ),
			'code'   => __( 'Code Snippet', 'hook-locator' ),
			'detail' => __( 'Actions', 'hook-locator' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @since 1.0
	 * @return array Associative array of sortable column slugs and sort data.
	 */
	public function get_sortable_columns() {
		return array(
			'file' => array( 'file', false ),
			'line' => array( 'line', false ),
			'type' => array( 'type', false ),
		);
	}

	/**
	 * Prepare table items for display.
	 *
	 * Handles sorting, pagination, and data preparation.
	 *
	 * @since 1.0
	 */
	public function prepare_items() {
		// Set column headers.
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Handle sorting.
		$orderby = 'file';
		$order   = 'asc';

		if (
			isset( $_GET['hook_locator_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['hook_locator_nonce'] ) ), 'hook_locator_search' )
		) {
			if ( isset( $_GET['orderby'] ) ) {
				$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			}
			if ( isset( $_GET['order'] ) ) {
				$order = sanitize_text_field( wp_unslash( $_GET['order'] ) );
			}
		}

		$data = $this->data;
		if ( ! empty( $data ) ) {
			uasort(
				$data,
				function ( $a, $b ) use ( $orderby, $order ) {
					$result = 0;

					switch ( $orderby ) {
						case 'line':
							$result = (int) $a['line'] - (int) $b['line'];
							break;
						case 'type':
							$result = strcmp( $a['type'], $b['type'] );
							break;
						case 'file':
						default:
							$result = strcmp( $a['file'], $b['file'] );
							break;
					}

					return 'desc' === $order ? -$result : $result;
				}
			);
		}

		// Handle pagination.
		$per_page     = 25;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		// Set items for current page.
		$this->items = array_slice( $data, ( $current_page - 1 ) * $per_page, $per_page, true );
	}

	/**
	 * Render file column.
	 *
	 * @since 1.0
	 * @param array $item Current row item data.
	 * @return string HTML for file column.
	 */
	public function column_file( $item ) {
		$file_path     = $item['file'];
		$relative_path = str_replace( ABSPATH, '', $file_path );

		// Determine file type icon.
		$is_plugin = false !== strpos( $file_path, WP_PLUGIN_DIR );
		$is_theme  = false !== strpos( $file_path, get_theme_root() );

		$icon_class = 'dashicons-media-code';
		$type_label = __( 'File', 'hook-locator' );

		if ( $is_plugin ) {
			$icon_class = 'dashicons-admin-plugins';
			$type_label = __( 'Plugin', 'hook-locator' );
		} elseif ( $is_theme ) {
			$icon_class = 'dashicons-admin-appearance';
			$type_label = __( 'Theme', 'hook-locator' );
		}

		$output  = '<div class="hook-locator-file-info">';
		$output .= '<span class="dashicons ' . esc_attr( $icon_class ) . '" title="' . esc_attr( $type_label ) . '"></span>';
		$output .= '<code class="hook-locator-file-path" title="' . esc_attr( $file_path ) . '">';
		$output .= esc_html( $relative_path );
		$output .= '</code>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render line column.
	 *
	 * @since 1.0
	 * @param array $item Current row item data.
	 * @return string HTML for line column.
	 */
	public function column_line( $item ) {
		$line_number = absint( $item['line'] );
		return '<span class="hook-locator-line-number">' . esc_html( number_format_i18n( $line_number ) ) . '</span>';
	}

	/**
	 * Render type column.
	 *
	 * @since 1.0
	 * @param array $item Current row item data.
	 * @return string HTML for type column.
	 */
	public function column_type( $item ) {
		$hook_type  = isset( $item['type'] ) ? $item['type'] : 'unknown';
		$type_label = Hook_Locator_Search::get_hook_type_label( $hook_type );
		$css_class  = 'hook-locator-type-' . str_replace( '_', '-', $hook_type );

		return '<span class="hook-locator-type-badge ' . esc_attr( $css_class ) . '">' . esc_html( $type_label ) . '</span>';
	}

	/**
	 * Render code column.
	 *
	 * @since 1.0
	 * @param array $item Current row item data.
	 * @return string HTML for code column.
	 */
	public function column_code( $item ) {
		$code = $item['code'];

		// Truncate long code snippets.
		$max_length = 80;
		if ( strlen( $code ) > $max_length ) {
			$code = substr( $code, 0, $max_length ) . '...';
		}

		return '<code class="hook-locator-code-snippet" title="' . esc_attr__( 'Click to copy', 'hook-locator' ) . '">' . esc_html( $code ) . '</code>';
	}

	/**
	 * Render actions column.
	 *
	 * @since 1.0
	 * @param array $item Current row item data.
	 * @return string HTML for actions column.
	 */
	public function column_detail( $item ) {
		$detail_key = base64_encode( $item['file'] . '|' . $item['line'] );

		$url = add_query_arg(
			array(
				'page'               => 'hook-locator',
				'hook_name'          => rawurlencode( $this->hook_name ),
				'type'               => rawurlencode( $item['type'] ),
				'directory'          => (
					isset( $_GET['directory'], $_GET['hook_locator_nonce'] ) &&
					wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['hook_locator_nonce'] ) ), 'hook_locator_search' )
				) ? sanitize_text_field( wp_unslash( $_GET['directory'] ) ) : 'all',
				'detail'             => rawurlencode( $detail_key ),
				'hook_locator_nonce' => wp_create_nonce( 'hook_locator_search' ),
			),
			admin_url( 'tools.php' )
		);

		return '<a href="' . esc_url( $url ) . '" class="button button-small hook-locator-detail-btn">' .
				'<span class="dashicons dashicons-visibility"></span> ' .
				esc_html__( 'View Details', 'hook-locator' ) .
				'</a>';
	}

	/**
	 * Display message when no items are found.
	 *
	 * @since 1.0
	 */
	public function no_items() {
		esc_html_e( 'No hooks found matching your search criteria.', 'hook-locator' );
	}

	/**
	 * Display the table.
	 *
	 * Overrides parent method to add custom wrapper.
	 *
	 * @since 1.0
	 */
	public function display() {
		echo '<div class="hook-locator-results-table-wrapper">';
		parent::display();
		echo '</div>';
	}
}
