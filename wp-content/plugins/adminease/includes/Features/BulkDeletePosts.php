<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;
use WP_Error;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Class BulkDeletePosts
 * Provides functionality for bulk deletion of posts, pages, and custom post types
 * with real-time progress tracking and additional configurable settings.
 */
class BulkDeletePosts {
	private array $settings;
	private string $selected_post_type;
	private string $selected_post_status;
	private array $all_post_types = [];
	private array $all_post_statuses = [];
	
	public function __construct() {
		$this->settings             = Plugin::get_settings( 'posts' );
		$this->selected_post_type   = $this->settings['bulk_delete_posts_post_type'] ?? '';
		$this->selected_post_status = $this->settings['bulk_delete_posts_post_status'] ?? 'any';
		
		add_action( 'init', [ $this, 'init' ], 999 );
		add_action( 'adminease_after_field_render', [ $this, 'adminease_after_field_render' ] );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		if( !empty( $this->settings['bulk_delete_posts_enabled'] ) ) {
			if( !empty( $this->selected_post_type ) ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
				
				add_action( 'wp_ajax_adminease_bulk_delete_preview', [ $this, 'ajax_preview_posts' ] );
				add_action( 'wp_ajax_adminease_bulk_delete_batch', [ $this, 'ajax_process_batch' ] );
			}
		}
	}
	
	/**
	 * Initializes all post types and post statuses.
	 * This method sets up necessary data or configurations related
	 * to all post types and post statuses within the application context.
	 *
	 * @return void
	 */
	public function init() {
		$this->init_all_post_types();
		$this->init_all_post_statuses();
	}
	
	/**
	 * Initializes all post types for use within the system.
	 * Filters out specific post types such as "attachment" and allows customization via a filter hook.
	 *
	 * @return void
	 */
	public function init_all_post_types() {
		$this->all_post_types = apply_filters( 'adminease_bulk_delete_post_types_options', Utils::get_post_types( [ 'base' ] ) );
	}
	
	/**
	 * Initializes the list of all post statuses, excluding specific statuses such as 'auto-draft' and 'inherit'.
	 * Filters the resulting list of statuses through a custom filter for further modifications.
	 *
	 * @return void
	 */
	public function init_all_post_statuses() {
		$all_post_statuses = get_post_statuses();
		
		unset( $all_post_statuses['auto-draft'] );
		unset( $all_post_statuses['inherit'] );
		
		$this->all_post_statuses = apply_filters( 'adminease_bulk_delete_post_statuses_options', $all_post_statuses );
	}
	
	/**
	 * Adds custom settings fields for the BulkDeletePosts feature.
	 *
	 * @param array $fields The existing settings fields to which custom fields will be appended.
	 *
	 * @return array The modified settings fields including the added custom fields.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$link = '<a href="https://precisionwp.net/product/adminease/" target="_blank" rel="noopener noreferrer">' . __( 'Upgrade to AdminEasePro', 'adminease' ) . '</a>';
		/* translators: %s: link to upgrade to AdminEase Pro */
		$bulk_posts_enabled_str = sprintf( __( 'Allow bulk deletion of posts and pages with real-time progress tracking. Need custom post type support? %s', 'adminease' ), $link );
		
		if( Utils::is_pro_plugin_active() ) {
			$bulk_posts_enabled_str = __( 'Allow bulk deletion of posts, pages, and custom post types with real-time progress tracking.', 'adminease' );
		}
		
		$fields['posts']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'bulk-delete-posts-enabled',
			'name'              => 'adminease[posts][bulk_delete_posts_enabled]',
			'value'             => $this->settings['bulk_delete_posts_enabled'] ?? false,
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control toggle-field',
			'label'             => __( 'Enable Bulk Delete Posts', 'adminease' ),
			'description'       => $bulk_posts_enabled_str,
			'field_description' => __( 'Enable to activate the bulk delete feature for posts and pages.', 'adminease' ),
			'child_fields'      => [
				[
					'type'              => 'select',
					'id'                => 'bulk-delete-posts-deletion-method',
					'name'              => 'adminease[posts][bulk_delete_posts_deletion_method]',
					'value'             => $this->settings['bulk_delete_posts_deletion_method'] ?? 'trash',
					'label_class'       => '',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Deletion Method', 'adminease' ),
					'description'       => __( 'Choose how posts should be deleted. WARNING: Permanent deletion cannot be undone!', 'adminease' ),
					'field_description' => __( 'Select the default deletion method.', 'adminease' ),
					'options'           => [
						'trash'     => __( 'Move to Trash', 'adminease' ),
						'permanent' => __( 'Delete Permanently', 'adminease' ),
					],
					'attributes'        => [
						'data-parent' => 'bulk-delete-posts-enabled',
					],
				],
				[
					'type'              => 'number',
					'id'                => 'bulk-delete-posts-batch-size',
					'name'              => 'adminease[posts][bulk_delete_posts_batch_size]',
					'value'             => $this->settings['bulk_delete_posts_batch_size'] ?? 20,
					'label_class'       => '',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Batch Size', 'adminease' ),
					'description'       => __( 'Number of posts to process per batch. Lower values are safer for slower servers.', 'adminease' ),
					'field_description' => __( 'Set between 10 and 100 posts per batch.', 'adminease' ),
					'min'               => 10,
					'max'               => 100,
					'attributes'        => [
						'data-parent' => 'bulk-delete-posts-enabled',
					],
				],
				[
					'type'          => 'select',
					'id'            => 'bulk-delete-posts-post-types',
					'name'          => 'adminease[posts][bulk_delete_posts_post_type]',
					'value'         => $this->selected_post_type,
					'options'       => $this->all_post_types,
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control adminease-choices',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Select post type', 'adminease' ),
					'description'   => __( 'Choose which post types to include in bulk deletion.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'bulk-delete-posts-enabled',
					],
				],
				[
					'type'          => 'select',
					'id'            => 'bulk-delete-posts-post-status',
					'name'          => 'adminease[posts][bulk_delete_posts_post_status]',
					'value'         => $this->selected_post_status,
					'options'       => $this->all_post_statuses,
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Select post status', 'adminease' ),
					'description'   => __( 'Choose which post status to include in bulk deletion.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'bulk-delete-posts-enabled',
					],
				],
				[
					'type'          => 'date_range',
					'id'            => 'bulk-delete-posts-date-range',
					'name'          => 'adminease[posts][bulk_delete_posts_date_range]',
					'value'         => $this->settings['bulk_delete_posts_date_range'] ?? '',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Select date range <small>(optional)</small>', 'adminease' ),
					'description'   => __( 'Choose a date range to include posts published within that range.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'bulk-delete-posts-enabled',
					],
				],
				[
					'type'              => 'button',
					'id'                => 'bulk-delete-posts-submit',
					'input_class'       => 'button button-secondary',
					'label'             => __( 'Preview', 'adminease' ),
					'field_description' => __( 'Click to begin the bulk deletion process based on selected criteria.', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'bulk-delete-posts-enabled',
						'role'        => 'button',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Performs operations after a specific field is rendered in the admin interface.
	 * This method checks the field ID and user permissions, and includes a specific
	 * template file if conditions are met.
	 *
	 * @param array $field The field data array, which contains attributes such as the field ID.
	 *
	 * @return void
	 */
	public function adminease_after_field_render( array $field ): void {
		if( 'bulk-delete-posts-enabled' !== $field['id'] ) {
			return;
		}
		
		if( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		if( !file_exists( ADMINEASE_DIR . 'partials/bulk-delete-posts.php' ) ) {
			return;
		}
		
		include ADMINEASE_DIR . 'partials/bulk-delete-posts.php';
	}
	
	/**
	 * Enqueue JavaScript and CSS assets for the bulk delete feature.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts(): void {
		wp_enqueue_script(
			ADMINEASE_NAME . 'BulkDeletePosts',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseBulkDeletePosts.js',
			[ 'jquery', ADMINEASE_NAME ],
			filemtime( ADMINEASE_DIR . 'assets/js/AdminEaseBulkDeletePosts.js' ),
			true
		);
		
		wp_localize_script(
			ADMINEASE_NAME . 'BulkDeletePosts',
			ADMINEASE_NAME . 'BulkDeletePostsAjaxObj',
			[
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'security'       => wp_create_nonce( 'adminease_bulk_delete_nonce' ),
				'batchSize'      => absint( $this->settings['bulk_delete_posts_batch_size'] ?? 20 ),
				'deletionMethod' => $this->settings['bulk_delete_posts_deletion_method'] ?? 'trash',
				'i18n'           => [
					'confirmTitle'     => esc_html__( 'Confirm Bulk Delete', 'adminease' ),
					'confirmMessage'   => esc_html__( 'Are you sure you want to delete the selected posts? This action cannot be easily undone.', 'adminease' ),
					'confirmPermanent' => esc_html__( 'WARNING: You are about to PERMANENTLY delete posts. This action CANNOT be undone. Are you absolutely sure?', 'adminease' ),
					'processing'       => esc_html__( 'Processing...', 'adminease' ),
					'complete'         => esc_html__( 'Deletion Complete!', 'adminease' ),
					'error'            => esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ),
					'noPostsFound'     => esc_html__( 'No posts found matching the criteria.', 'adminease' ),
					/* translators: %d: number of posts found */
					'foundPosts'       => esc_html__( '➜ Found %d posts matching criteria', 'adminease' ),
					'deletedPosts'     => esc_html__( 'Deleted Posts:', 'adminease' ),
					/* translators: %d: number of posts deleted so far */
					'totalDeleted'     => esc_html__( 'Total: %d posts deleted', 'adminease' ),
					/* translators: %d: number of posts remaining */
					'postsSummary'     => esc_html__( '%1$d posts, %2$d pages', 'adminease' ),
					'startButton'      => esc_html__( 'Start Bulk Delete', 'adminease' ),
					'post'             => esc_html__( 'Post', 'adminease' ),
					'page'             => esc_html__( 'Page', 'adminease' ),
				],
			]
		);
	}
	
	/**
	 * AJAX handler for previewing posts that match the criteria.
	 *
	 * @return void
	 */
	public function ajax_preview_posts(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_bulk_delete_nonce' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'Security check failed. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Capability check
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		
		if( empty( $post_type ) || !array_key_exists( $post_type, $this->all_post_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select a valid post type.', 'adminease' ) ] );
		}
		
		$post_status = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'any';
		
		if( empty( $post_status ) || !array_key_exists( $post_status, $this->all_post_statuses ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select a valid post status.', 'adminease' ) ] );
		}
		
		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		
		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		];
		
		if( !empty( $date_from ) || !empty( $date_to ) ) {
			$query_args['date_query'] = [
				[
					'after'     => $date_from,
					'before'    => $date_to,
					'inclusive' => true,
				],
			];
		}
		
		// Allow Pro version to modify query args
		$query_args = apply_filters( 'adminease_bulk_delete_preview_query_args', $query_args );
		
		$query = new WP_Query( $query_args );
		
		// Count by post type
		$breakdown = [
			$post_type => $query->found_posts,
		];
		
		wp_send_json_success( [
			'total'     => $query->found_posts,
			'breakdown' => $breakdown,
		] );
	}
	
	/**
	 * AJAX handler for processing a batch of post deletions.
	 *
	 * @return void
	 */
	public function ajax_process_batch(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_bulk_delete_nonce' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'Security check failed. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Capability check
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		
		if( empty( $post_type ) || !array_key_exists( $post_type, $this->all_post_types ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select a valid post type.', 'adminease' ) ] );
		}
		
		$post_status = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'any';
		
		if( empty( $post_status ) || !array_key_exists( $post_status, $this->all_post_statuses ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select a valid post status.', 'adminease' ) ] );
		}
		
		$post_status     = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'any';
		$date_from       = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to         = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$page            = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$posts_per_page  = absint( !empty( $_POST['batch_size'] ) ? sanitize_text_field( wp_unslash( $_POST['batch_size'] ) ) : ( $this->settings['bulk_delete_posts_batch_size'] ?? 20 ) );
		$deletion_method = isset( $_POST['deletion_method'] ) && in_array( $_POST['deletion_method'], [ 'trash', 'permanent' ] )
			? sanitize_text_field( wp_unslash( $_POST['deletion_method'] ) )
			: $this->settings['bulk_delete_posts_deletion_method'] ?? 'trash';
		
		// Always query page 1 since we're deleting posts in real-time
		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'posts_per_page' => $posts_per_page,
			'paged'          => 1, // Always fetch page 1
			'fields'         => 'ids',
			'no_found_rows'  => false,
		];
		
		if( !empty( $date_from ) || !empty( $date_to ) ) {
			$query_args['date_query'] = [
				[
					'after'     => $date_from,
					'before'    => $date_to,
					'inclusive' => true,
				],
			];
		}
		
		// Allow Pro version to modify query args
		$query_args = apply_filters( 'adminease_bulk_delete_query_args', $query_args );
		
		$query   = new WP_Query( $query_args );
		$deleted = [];
		
		// Action hook before batch processing
		do_action( 'adminease_bulk_delete_batch_start', $query->posts, $page );
		
		foreach( $query->posts as $post_id ) {
			$post_title = get_the_title( $post_id );
			$post_type  = get_post_type( $post_id );
			
			if( $deletion_method === 'permanent' ) {
				$result = wp_delete_post( $post_id, true );
			}
			else {
				$result = wp_trash_post( $post_id );
			}
			
			if( $result ) {
				$deleted[] = [
					'id'    => $post_id,
					'title' => $post_title,
					'type'  => $post_type,
				];
				
				// Action hook after individual post deletion
				do_action( 'adminease_bulk_delete_post', $post_id, $post_type, $deletion_method );
			}
		}
		
		// Calculate remaining by subtracting what we just deleted from total found
		$total_remaining = max( 0, $query->found_posts - count( $deleted ) );
		
		// Action hook after batch completes
		do_action( 'adminease_bulk_delete_batch_complete', $deleted, $page );
		
		wp_send_json_success( [
			'deleted'         => $deleted,
			'processed'       => count( $deleted ),
			'total_remaining' => $total_remaining,
			'current_page'    => $page,
			'total_found'     => $query->found_posts,
		] );
	}
}