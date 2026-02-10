<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;
use WP_Term_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Class DragAndDropOrderingTaxonomies
 * Implements functionality for drag-and-drop reordering of taxonomies in the WordPress admin interface.
 * This class manages actions, filters, and scripts required for reordering taxonomies using a custom "Order" column.
 */
class DragAndDropOrderingTaxonomies {
	private array $settings;
	private array $taxonomies;
	
	public function __construct() {
		$this->settings   = Plugin::get_settings( 'taxonomies' );
		$this->taxonomies = [ 'category', 'post_tag' ];
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['drag_and_drop_ordering_taxonomies'] ) ) {
			add_action( 'init', [ $this, 'init' ] );
			
			if( is_admin() && current_user_can( 'edit_posts' ) ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
				
				add_action( 'wp_ajax_adminease_update_term_order', [ $this, 'ajax_update_term_order' ] );
				
				add_filter( 'adminease_global_inline_css', [ $this, 'add_global_css' ] );
			}
			
			add_filter( 'get_terms', [ $this, 'sort_terms_by_menu_order' ], 10, 4 );
		}
	}
	
	/**
	 * Modifies the settings fields to include a drag-and-drop ordering option.
	 *
	 * @param array $fields An array of existing settings fields.
	 *
	 * @return array The modified settings fields array, including the drag-and-drop ordering option.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['taxonomies']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'drag-and-drop-ordering-taxonomies',
			'name'        => 'adminease[taxonomies][drag_and_drop_ordering_taxonomies]',
			'value'       => $this->settings['drag_and_drop_ordering_taxonomies'] ?? '',
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Order Taxonomies using Drag and Drop', 'adminease' ),
			'description' => __( "Rearrange items on your site exactly how you want <strong>just click, drag, and drop!</strong> Instantly organize taxonomies with a simple, intuitive interface. <strong>No coding, no hassle</strong>, just smooth, visual control over your site’s order. Make your WordPress experience truly yours!", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Executes the necessary setup by attaching actions and filters.
	 * @return void
	 */
	public function init(): void {
		$this->taxonomies = apply_filters( 'adminease_drag_and_drop_ordering_taxonomies_taxonomies', $this->taxonomies );

		foreach( $this->taxonomies as $taxonomy ) {
			add_action( "manage_{$taxonomy}_custom_column", [ $this, 'render_term_order_column' ], 10, 3 );
			add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'add_term_order_column' ] );
		}
	}
	
	/**
	 * Enqueues the necessary scripts for drag-and-drop ordering in the admin interface.
	 *
	 * @param string $hook The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ): void {
		if( 'edit-tags.php' !== $hook ) {
			return;
		}
		
		$filetime = filemtime( ADMINEASE_DIR . 'assets/js/AdminEaseDragAndDropOrderingTaxonomies.js' );
		
		wp_enqueue_script(
			ADMINEASE_NAME . 'DragDropOrderingTaxonomies',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseDragAndDropOrderingTaxonomies.js',
			[ 'jquery-ui-sortable' ],
			$filetime,
			true
		);
		
		wp_localize_script(
			ADMINEASE_NAME . 'DragDropOrderingTaxonomies',
			ADMINEASE_NAME . 'DragDropOrderingTaxonomiesAjaxObj',
			[
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'security' => [
					'termsNonce' => wp_create_nonce( 'adminease_update_term_order_nonce' ),
				],
				'i18n'     => [
					'updating' => esc_html__( 'Updating taxonomy order...', 'adminease' ),
					'success'  => esc_html__( 'Taxonomy order updated successfully.', 'adminease' ),
				],
			]
		);
	}
	
	/**
	 * Sorts terms by their 'menu_order' meta value, prioritizing those with menu order set.
	 *
	 * @param array         $terms Array of found terms.
	 * @param array|null    $taxonomies An array of taxonomies if known.
	 * @param array         $query_vars An array of get_terms() arguments.
	 * @param WP_Term_Query $term_query The WP_Term_Query object.
	 *
	 * @return array The sorted array of terms by 'menu_order' and name fallback.
	 */
	public function sort_terms_by_menu_order( array $terms, ?array $taxonomies, array $query_vars, WP_Term_Query $term_query ): array {
		if( empty( $terms ) || is_wp_error( $terms ) ) {
			return $terms;
		}
		
		// Check if any of the taxonomies are ones we want to sort
		$target_taxonomies = array_intersect( (array) $taxonomies, $this->taxonomies );
		
		if( empty( $target_taxonomies ) ) {
			return $terms;
		}

		// Sort terms by menu_order
		usort( $terms, function( $a, $b ) {
			if( !is_a( $a, 'WP_Term' ) || !is_a( $b, 'WP_Term' )) {
				return 0;
			}
			
			$order_a = get_term_meta( $a->term_id, 'menu_order', true );
			$order_b = get_term_meta( $b->term_id, 'menu_order', true );
			
			// If both have order values, sort by those
			if( $order_a !== '' && $order_b !== '' ) {
				return (int) $order_a - (int) $order_b;
			}
			
			// If only one has an order value, prioritize it
			if( $order_a !== '' ) {
				return -1;
			}
			if( $order_b !== '' ) {
				return 1;
			}
			
			// If neither has order, sort by name
			return strcmp( $a->name, $b->name );
		} );
		
		// Remove the filter to avoid affecting other queries
		remove_filter( 'get_terms', [ $this, 'sort_terms_by_menu_order' ], 10 );
		
		return $terms;
	}
	
	/**
	 * Adds drag and drop CSS to the global stylesheet
	 *
	 * @param string $css Existing CSS
	 *
	 * @return string Modified CSS with drag and drop styles
	 */
	public function add_global_css( string $css ): string {
		$drag_drop_css = '
        .column-order {
            width: 30px !important;
            text-align: center;
        }
        
        .column-order > .dashicons-align-wide {
            padding: 0 5px;
        }
        
        .adminease-orderable .ui-draggable-handle,
        .adminease-orderable .ui-sortable-handle {
            cursor: grab;
        }
        
        tbody tr.adminease-orderable {
            transition: background-color 0.3s ease;
        }
        ';
		
		return $css . $drag_drop_css;
	}
	
	/**
	 * Handles AJAX request to update the order of taxonomy terms.
	 * Validates nonce and user permissions, processes term IDs, updates the order, and returns success or error response.
	 * @return void
	 */
	public function ajax_update_term_order(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_update_term_order_nonce' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'Security check failed. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Capability check
		if( !current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$term_ids = isset( $_POST['term_ids'] ) ? array_map( 'absint', $_POST['term_ids'] ) : [];
		
		if( empty( $term_ids ) ) {
			wp_send_json_error( esc_html__( 'No terms to update', 'adminease' ) );
		}
		
		$this->update_terms_order( $term_ids );
		
		wp_send_json_success( esc_html__( 'Terms order updated successfully', 'adminease' ) );
	}
	
	/**
	 * Updates the order of taxonomy terms using term meta.
	 *
	 * @param array $term_ids An array of term IDs, where the array keys represent the new positions.
	 *
	 * @return void This method does not return a value.
	 */
	private function update_terms_order( array $term_ids ): void {
		foreach( $term_ids as $position => $term_id ) {
			update_term_meta( $term_id, 'menu_order', $position );
		}
	}
	
	/**
	 * Adds an "Order" column to the list of columns.
	 *
	 * @param array $columns An array of existing columns.
	 *
	 * @return array The modified array of columns including the "Order" column.
	 */
	public function add_order_column( array $columns ): array {
		return array_merge( [ 'order' => '<span class="dashicons dashicons-align-wide"></span>' ], $columns );
	}
	
	/**
	 * Adds an "Order" column to taxonomy term columns.
	 *
	 * @param array $columns An array of existing columns.
	 *
	 * @return array The modified array of columns including the "Order" column.
	 */
	public function add_term_order_column( array $columns ): array {
		return array_merge( [ 'order' => '<span class="dashicons dashicons-align-wide"></span>' ], $columns );
	}
	
	/**
	 * Renders the custom order column in the admin taxonomies table.
	 *
	 * @param string $column_name The name of the column being rendered.
	 * @param int    $post_id The ID of the current post.
	 *
	 * @return void
	 */
	public function render_order_column( string $column_name, int $post_id ): void {
		if( 'order' === $column_name ) {
			echo sprintf(
				'<div class="drag-handle" data-post-id="%d"><span class="dashicons dashicons-move"></span></div>',
				esc_attr( $post_id )
			);
		}
	}
	
	/**
	 * Renders the custom order column in the admin taxonomy terms table.
	 *
	 * @param string $content Column content (empty by default).
	 * @param string $column_name The name of the column being rendered.
	 * @param int    $term_id The ID of the current term.
	 *
	 * @return void
	 */
	public function render_term_order_column( string $content, string $column_name, int $term_id ): void {
		if( 'order' === $column_name ) {
			echo sprintf(
				'<div class="drag-handle" data-term-id="%d"><span class="dashicons dashicons-move"></span></div>',
				esc_attr( $term_id )
			);
		}
	}
}