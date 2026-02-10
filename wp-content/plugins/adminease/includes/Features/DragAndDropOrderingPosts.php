<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Class DragAndDropOrderingPosts
 * Implements functionality for drag-and-drop reordering of posts in the WordPress admin interface.
 * This class manages actions, filters, and scripts required for reordering posts using a custom "Order" column.
 */
class DragAndDropOrderingPosts {
	private array $settings;
	private array $post_types;
	
	public function __construct() {
		$this->settings   = Plugin::get_settings( 'posts' );
		$this->post_types = [ 'post', 'page' ];
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['drag_and_drop_ordering_posts'] ) ) {
			add_action( 'init', [ $this, 'init' ] );
			
			// Admin-only hooks
			if( is_admin() ) {
				if( current_user_can( 'edit_posts' ) ) {
					add_action( 'admin_init', [ $this, 'admin_init' ] );
					add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
					
					add_action( 'wp_ajax_adminease_update_post_order', [ $this, 'ajax_update_post_order' ] );
					
					add_filter( 'adminease_global_inline_css', [ $this, 'add_global_css' ] );
					add_filter( 'post_class', [ $this, 'post_class' ], 10, 3 );
				}
			}
			
			add_filter( 'pre_get_posts', [ $this, 'set_post_order' ] );
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
		$fields['posts']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'drag-and-drop-ordering-posts',
			'name'        => 'adminease[posts][drag_and_drop_ordering_posts]',
			'value'       => $this->settings['drag_and_drop_ordering_posts'] ?? '',
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Order Posts using Drag and Drop', 'adminease' ),
			'description' => __( "Rearrange items on your site exactly how you want <strong>just click, drag, and drop!</strong> Instantly organize posts with a simple, intuitive interface. <strong>No coding, no hassle</strong>, just smooth, visual control over your site’s order. Make your WordPress experience truly yours!<br><i>* Gutenberg should be deactivated for this feature to work.</i></i>", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Executes the necessary setup by attaching actions and filters.
	 * @return void
	 */
	public function init(): void {
		$this->post_types = apply_filters( 'adminease_drag_and_drop_ordering_posts_post_types', $this->post_types );
		
		foreach( $this->post_types as $post_type ) {
			add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'render_order_column' ], 10, 2 );
			
			add_filter( "manage_edit-{$post_type}_columns", [ $this, 'add_order_column' ] );
			add_filter( "manage_{$post_type}_posts_columns", [ $this, 'add_order_column' ] );
		}
	}
	
	/**
	 * Initializes admin-specific actions by enabling page attributes support for all registered post types.
	 * @return void
	 */
	public function admin_init(): void {
		foreach( $this->post_types as $post_type ) {
			add_post_type_support( $post_type, 'page-attributes' );
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
		if( 'edit.php' !== $hook ) {
			return;
		}
		
		$filetime = filemtime( ADMINEASE_DIR . 'assets/js/AdminEaseDragAndDropOrderingPosts.js' );
		
		wp_enqueue_script(
			ADMINEASE_NAME . 'DragDropOrderingPosts',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseDragAndDropOrderingPosts.js',
			[ 'jquery-ui-sortable' ],
			$filetime,
			true
		);
		
		wp_localize_script(
			ADMINEASE_NAME . 'DragDropOrderingPosts',
			ADMINEASE_NAME . 'DragDropOrderingPostsAjaxObj',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'security'   => [
					'postsNonce' => wp_create_nonce( 'update_post_order_nonce' ),
				],
				'i18n'    => [
					'updating' => esc_html__( 'Updating post order...', 'adminease' ),
					'success'  => esc_html__( 'Post order updated successfully.', 'adminease' ),
				],
			]
		);
	}
	
	/**
	 * Modifies the query to set post order for specific post types.
	 *
	 * @param WP_Query $query The query object to be modified.
	 *
	 * @return WP_Query The modified query object.
	 */
	public function set_post_order( WP_Query $query ): WP_Query {
		if( $query->is_main_query() && in_array( $query->get( 'post_type' ), $this->post_types ) ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
		
		return $query;
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
	 * Filters the list of CSS classes for the current post.
	 *
	 * @param array $classes An array of CSS classes for the post element.
	 * @param       $class A comma-separated list of additional classes added to the post.
	 * @param int   $post_id The ID of the current post.
	 *
	 * @return array An updated array of CSS classes for the post element.
	 */
	public function post_class( array $classes, $class, int $post_id ): array {
		if( is_admin() ) {
			$classes[] = ADMINEASE_SLUG . '-orderable';
		}
		
		return $classes;
	}
	
	/**
	 * Handles AJAX request to update the order of posts.
	 * Validates nonce and user permissions, processes post IDs, updates the order, and returns success or error response.
	 * @return void
	 */
	public function ajax_update_post_order(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'update_post_order_nonce' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'Security check failed. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Capability check
		if( !current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', $_POST['post_ids'] ) : [];
		
		if( empty( $post_ids ) ) {
			wp_send_json_error( 'No posts to update' );
		}
		
		$this->update_posts_order( $post_ids );
		
		wp_send_json_success( 'Posts order updated successfully' );
	}
	
	/**
	 * Updates the menu order of posts based on the provided post IDs and their positions.
	 *
	 * @param array $post_ids An array of post IDs, where the array keys represent the new positions.
	 *
	 * @return void This method does not return a value.
	 */
	private function update_posts_order( array $post_ids ): void {
		foreach( $post_ids as $position => $post_id ) {
			wp_update_post( [
				'ID'         => $post_id,
				'menu_order' => $position,
			] );
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
	 * Renders the custom order column in the admin posts table.
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