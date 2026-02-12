<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Handles post metadata box functionality for displaying and managing post meta data.
 */
class PostsMetadataBox {
	private array $settings;
	private array $selected_post_types;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'posts' );
		
		add_action( 'init', [ $this, 'init' ], 999 );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		if( !empty( $this->settings['enable_posts_metadata_box'] ) ) {
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			
			add_action( 'wp_ajax_adminease_get_post_metadata', [ $this, 'ajax_get_post_metadata' ] );
			add_action( 'wp_ajax_adminease_update_post_metadata', [ $this, 'ajax_update_post_metadata' ] );
			add_action( 'wp_ajax_adminease_delete_post_metadata', [ $this, 'ajax_delete_post_metadata' ] );
		}
	}
	
	/**
	 * Initialize post types on init hook to ensure all custom post types are registered.
	 */
	public function init() {
		$default_post_types = Utils::get_post_types( [ 'base', 'media' ] );
		
		$allowed_post_types        = apply_filters( 'adminease_posts_metadata_box_allowed_post_types', $default_post_types );
		$this->selected_post_types = isset( $this->settings['posts_metadata_box_post_types'] ) ? array_intersect_key( $allowed_post_types, array_flip( $this->settings['posts_metadata_box_post_types'] ) ) : $default_post_types;
	}
	
	/**
	 * Modifies and returns the settings fields for admin security features.
	 *
	 * @param array $fields The existing configuration fields to be updated with additional options.
	 *
	 * @return array The updated configuration fields, including the option to add metadata boxes to posts.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['posts']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'enable-posts-metadata-box',
			'name'        => 'adminease[posts][enable_posts_metadata_box]',
			'value'       => $this->settings['enable_posts_metadata_box'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Enable Posts Metadata Box', 'adminease' ),
			'description' => __( 'Enable a metadata box on post edit screens to view and manage custom fields easily. In addition, it adds image sizes to images in the media library.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Registers the meta box for allowed post types.
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		foreach( $this->selected_post_types as $post_type_key => $post_type_label ) {
			add_meta_box(
				'adminease-posts-metadata-box',
				__( 'Post Metadata', 'adminease' ),
				[ $this, 'render_metabox' ],
				$post_type_key,
				'normal',
			);
		}
	}
	
	/**
	 * Renders the meta box content.
	 *
	 * @param  $post_or_order_object
	 *
	 * @return void
	 */
	public function render_metabox( $post_or_order_object ): void {
		$post_id = is_a( $post_or_order_object, 'Automattic\WooCommerce\Admin\Overrides\Order' ) ? $post_or_order_object->get_id() : $post_or_order_object->ID;
		
		// Don't render if no post ID (shouldn't happen, but safety check)
		if( !$post_id ) {
			echo '<p style="text-align: center; padding: 20px; color: #666;">';
			esc_html_e( 'Please save this post first to manage metadata.', 'adminease' );
			echo '</p>';
			
			return;
		}
		?>
		<div id="adminease-posts-metadata-box-inner" class="adminease" data-post-id="<?php echo esc_attr( $post_id ); ?>">
			<div class="adminease-metadata-actions" style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between;">
				<div class="col">
					<label for="adminease-metadata-search" class="screen-reader-text">
						<span class="dashicons dashicons-search"></span>
						<?php esc_html_e( 'Search metadata', 'adminease' ); ?>
					</label>
					<input type="text" id="adminease-metadata-search" placeholder="<?php esc_attr_e( 'Search metadata...', 'adminease' ); ?>" style="flex: 1; max-width: 300px;">
					<label for="adminease-metadata-sort" class="screen-reader-text">
						<?php esc_html_e( 'Sort metadata', 'adminease' ); ?>
					</label>
					<select id="adminease-metadata-sort" style="min-width: 120px;">
						<option value="asc"><?php esc_html_e( 'A-Z', 'adminease' ); ?></option>
						<option value="desc"><?php esc_html_e( 'Z-A', 'adminease' ); ?></option>
					</select>
				</div>
				<div class="col">
					<button type="button" class="button button-primary" id="adminease-add-metadata">
						<span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'Add New Metadata', 'adminease' ); ?>
					</button>
				</div>
			</div>
			
			<div id="adminease-metadata-table-container">
				<div class="adminease-loading" style="text-align: center; padding: 20px;">
					<?php esc_html_e( 'Loading metadata...', 'adminease' ); ?>
				</div>
			</div>
		</div>
		
		<!-- Modal -->
		<div id="adminease-metadata-modal" class="adminease-modal" style="display: none;">
			<div class="adminease-modal-overlay"></div>
			<div class="adminease-modal-dialog">
				<div class="adminease-modal-content">
					<div class="adminease-modal-header">
						<h3 id="adminease-modal-title"><?php esc_html_e( 'Edit Metadata', 'adminease' ); ?></h3>
						<button type="button" class="adminease-modal-close" aria-label="<?php esc_attr_e( 'Close', 'adminease' ); ?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="adminease-modal-body">
						<div class="adminease-modal-errors"></div>
						<form id="adminease-metadata-form">
							<div class="form-group">
								<label for="adminease-meta-key">
									<?php esc_html_e( 'Meta Key', 'adminease' ); ?>
									<span style="color: #d32f2f;">*</span>
								</label>
								<input type="text" id="adminease-meta-key" name="meta_key" class="form-control"/>
							</div>
							<div class="form-group">
								<label for="adminease-meta-value">
									<?php esc_html_e( 'Meta Value', 'adminease' ); ?>
								</label>
								<textarea id="adminease-meta-value" name="meta_value" class="form-control" rows="8"></textarea>
								<p class="field-description" style="margin-top: 5px; font-size: 13px; color: #646970;">
									<?php esc_html_e( 'For arrays or objects, use JSON format. Example: {"key": "value"}', 'adminease' ); ?>
								</p>
							</div>
							<input type="hidden" id="adminease-post-id" name="post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
							<input type="hidden" id="adminease-original-key" name="original_key" value=""/>
						</form>
					</div>
					<div class="adminease-modal-footer">
						<button type="button" class="button button-secondary adminease-modal-close">
							<?php esc_html_e( 'Cancel', 'adminease' ); ?>
						</button>
						<button type="submit" class="button button-primary" form="adminease-metadata-form" id="adminease-save-metadata">
							<?php esc_html_e( 'Save Changes', 'adminease' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Enqueues scripts and styles for the metadata box.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ): void {
		// Only enqueue on post edit screens
		if( !in_array( $hook, [ 'post.php', 'post-new.php', 'woocommerce_page_wc-orders' ] ) ) {
			return;
		}
		
		// Check if current post type is allowed
		$screen = get_current_screen();
		
		if( !$screen || !in_array( $screen->post_type, array_keys( $this->selected_post_types ) ) ) {
			return;
		}
		
		// Enqueue CSS
		wp_enqueue_style(
			ADMINEASE_NAME . 'PostsMetadataBox',
			ADMINEASE_PLUGIN_URL . 'assets/css/AdminEasePostsMetadataBox.css',
			[],
			filemtime( ADMINEASE_DIR . 'assets/css/AdminEasePostsMetadataBox.css' )
		);
		
		// Enqueue JavaScript
		wp_enqueue_script(
			ADMINEASE_NAME . 'PostsMetadataBox',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEasePostsMetadataBox.js',
			[ 'jquery' ],
			filemtime( ADMINEASE_DIR . 'assets/js/AdminEasePostsMetadataBox.js' ),
			true
		);
		
		// Localize script with its own AJAX object
		wp_localize_script(
			ADMINEASE_NAME . 'PostsMetadataBox',
			ADMINEASE_NAME . 'PostsMetadataBoxAjaxObj',
			[
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'security' => [
					'getPostMetadata'    => wp_create_nonce( 'adminease_get_post_metadata' ),
					'updatePostMetadata' => wp_create_nonce( 'adminease_update_post_metadata' ),
					'deletePostMetadata' => wp_create_nonce( 'adminease_delete_post_metadata' ),
				],
				'i18n'     => [
					'metadataModalTitleEdit'     => esc_html__( 'Edit Metadata', 'adminease' ),
					'metadataModalTitleAdd'      => esc_html__( 'Add New Metadata', 'adminease' ),
					'metadataConfirmDelete'      => esc_html__( 'Are you sure you want to delete this metadata?', 'adminease' ),
					'metadataLoadError'          => esc_html__( 'Failed to load metadata. Please refresh the page and try again.', 'adminease' ),
					'metadataSaveError'          => esc_html__( 'Failed to save metadata. Please try again.', 'adminease' ),
					'metadataDeleteError'        => esc_html__( 'Failed to delete metadata. Please try again.', 'adminease' ),
					'metadataSaveSuccess'        => esc_html__( 'Metadata saved successfully.', 'adminease' ),
					'metadataDeleteSuccess'      => esc_html__( 'Metadata deleted successfully.', 'adminease' ),
					'metadataKeyRequired'        => esc_html__( 'Meta key is required.', 'adminease' ),
					'metadataNoMetadata'         => esc_html__( 'No metadata found for this post.', 'adminease' ),
					'metadataEdit'               => esc_html__( 'Edit', 'adminease' ),
					'metadataDelete'             => esc_html__( 'Delete', 'adminease' ),
					'metadataInvalidJSON'        => esc_html__( 'Invalid JSON format. Please check your value.', 'adminease' ),
					'metadataLoading'            => esc_html__( 'Loading metadata...', 'adminease' ),
					'metadataTableHeaderKey'     => esc_html__( 'Meta Key', 'adminease' ),
					'metadataTableHeaderValue'   => esc_html__( 'Meta Value', 'adminease' ),
					'metadataTableHeaderActions' => esc_html__( 'Actions', 'adminease' ),
					'metadataProtectedKey'       => esc_html__( 'Protected meta key', 'adminease' ),
					'metadataSearch'             => esc_html__( 'Search metadata...', 'adminease' ),
					'metadataSortAZ'             => esc_html__( 'Sort A-Z', 'adminease' ),
					'metadataSortZA'             => esc_html__( 'Sort Z-A', 'adminease' ),
					'metadataNoResults'          => esc_html__( 'No metadata found matching your search.', 'adminease' ),
					'metadataImageSize'          => esc_html__( 'Image size', 'adminease' ),
				],
			]
		);
	}
	
	/**
	 * Adds localization data for JavaScript.
	 *
	 * @param array $data Existing localization data.
	 *
	 * @return array Modified localization data.
	 */
	public function adminease_localize_script( array $data ): array {
		$data['security']['getPostMetadata']    = wp_create_nonce( 'adminease_get_post_metadata' );
		$data['security']['updatePostMetadata'] = wp_create_nonce( 'adminease_update_post_metadata' );
		$data['security']['deletePostMetadata'] = wp_create_nonce( 'adminease_delete_post_metadata' );
		
		$data['i18n']['metadataLoadError']     = esc_html__( 'Failed to load metadata. Please refresh the page.', 'adminease' );
		$data['i18n']['metadataUpdateSuccess'] = esc_html__( 'Metadata updated successfully.', 'adminease' );
		$data['i18n']['metadataUpdateError']   = esc_html__( 'Failed to update metadata. Please try again.', 'adminease' );
		$data['i18n']['metadataDeleteConfirm'] = esc_html__( 'Are you sure you want to delete this metadata?', 'adminease' );
		$data['i18n']['metadataDeleteSuccess'] = esc_html__( 'Metadata deleted successfully.', 'adminease' );
		$data['i18n']['metadataDeleteError']   = esc_html__( 'Failed to delete metadata. Please try again.', 'adminease' );
		$data['i18n']['metadataKeyRequired']   = esc_html__( 'Meta key is required.', 'adminease' );
		$data['i18n']['metadataNoMetadata']    = esc_html__( 'No metadata found for this post.', 'adminease' );
		$data['i18n']['metadataAddNew']        = esc_html__( 'Add New Metadata', 'adminease' );
		$data['i18n']['metadataEdit']          = esc_html__( 'Edit Metadata', 'adminease' );
		$data['i18n']['metadataInvalidJson']   = esc_html__( 'Invalid JSON format in meta value.', 'adminease' );
		
		return $data;
	}
	
	/**
	 * AJAX handler to get post metadata.
	 *
	 * @return void
	 */
	public function ajax_get_post_metadata(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_get_post_metadata' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'adminease' ) ] );
		}
		
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		
		if( !$post_id || !current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid post or insufficient permissions.', 'adminease' ) ] );
		}
		
		// Get all post meta
		$all_meta = get_post_meta( $post_id );
		
		$metadata = [];
		
		foreach( $all_meta as $key => $values ) {
			// Get the first value (get_post_meta returns array of values)
			$value = $values[0] ?? '';
			
			// Format serialized data
			$display_value = $this->format_meta_value( $value );
			
			$metadata[] = [
				'key'           => $key,
				'value'         => $value,
				'display_value' => $display_value,
				'is_serialized' => is_serialized( $value ),
				'is_protected'  => substr( $key, 0, 1 ) === '_',
			];
		}
		
		// Add image size metadata for attachments
		$post_type = get_post_type( $post_id );
		
		if( 'attachment' === $post_type ) {
			$image_sizes = $this->get_image_size_metadata( $post_id );
			$metadata    = array_merge( $metadata, $image_sizes );
		}
		
		wp_send_json_success( [ 'metadata' => $metadata ] );
	}
	
	/**
	 * AJAX handler to update post metadata.
	 *
	 * @return void
	 */
	public function ajax_update_post_metadata(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_update_post_metadata' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'adminease' ) ] );
		}
		
		$post_id      = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$meta_key     = isset( $_POST['meta_key'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_key'] ) ) : '';
		$meta_value   = isset( $_POST['meta_value'] ) ? wp_unslash( $_POST['meta_value'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$original_key = isset( $_POST['original_key'] ) ? sanitize_text_field( wp_unslash( $_POST['original_key'] ) ) : '';
		
		if( !$post_id || !current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid post or insufficient permissions.', 'adminease' ) ] );
		}
		
		if( empty( $meta_key ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Meta key is required.', 'adminease' ) ] );
		}
		
		// Prevent editing image size metadata
		if( strpos( $meta_key, '_image_size_' ) === 0 || strpos( $original_key, '_image_size_' ) === 0 ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Image size metadata cannot be edited.', 'adminease' ) ] );
		}
		
		// Process meta value - detect if it's JSON and handle accordingly
		$processed_value = $this->process_meta_value( $meta_value );
		
		// If key changed, delete old key
		if( !empty( $original_key ) && $original_key !== $meta_key ) {
			delete_post_meta( $post_id, $original_key );
		}
		
		// Update or add meta
		update_post_meta( $post_id, $meta_key, $processed_value );
		
		wp_send_json_success( [ 'message' => esc_html__( 'Metadata updated successfully.', 'adminease' ) ] );
	}
	
	/**
	 * AJAX handler to delete post metadata.
	 *
	 * @return void
	 */
	public function ajax_delete_post_metadata(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_delete_post_metadata' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'adminease' ) ] );
		}
		
		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$meta_key = isset( $_POST['meta_key'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_key'] ) ) : '';
		
		if( !$post_id || !current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid post or insufficient permissions.', 'adminease' ) ] );
		}
		
		if( empty( $meta_key ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Meta key is required.', 'adminease' ) ] );
		}
		
		// Prevent deleting image size metadata
		if( strpos( $meta_key, '_image_size_' ) === 0 ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Image size metadata cannot be deleted.', 'adminease' ) ] );
		}
		
		delete_post_meta( $post_id, $meta_key );
		
		wp_send_json_success( [ 'message' => esc_html__( 'Metadata deleted successfully.', 'adminease' ) ] );
	}
	
	/**
	 * Formats meta value for display.
	 *
	 * @param mixed $value The meta value.
	 *
	 * @return string Formatted value.
	 */
	private function format_meta_value( $value ): string {
		$unserialized = maybe_unserialize( $value );
		
		if( is_array( $unserialized ) || is_object( $unserialized ) ) {
			// Use JSON encoding for better readability
			return wp_json_encode( $unserialized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}
		
		return (string) $value;
	}
	
	/**
	 * Processes meta value from form input.
	 * Detects JSON and converts it to proper PHP structure.
	 *
	 * @param mixed $value The input value.
	 *
	 * @return mixed Processed value.
	 */
	private function process_meta_value( $value ) {
		// If it's already serialized, return as is
		if( is_serialized( $value ) ) {
			return $value;
		}
		
		// Try to decode as JSON
		$decoded = json_decode( $value, true );
		
		if( json_last_error() === JSON_ERROR_NONE && ( is_array( $decoded ) || is_object( $decoded ) ) ) {
			return $decoded;
		}
		
		// Return as string
		return sanitize_textarea_field( $value );
	}
	
	/**
	 * Gets image size metadata for an attachment.
	 * Returns an array of virtual metadata entries for each registered image size.
	 *
	 * @param int $attachment_id The attachment post ID.
	 *
	 * @return array Array of image size metadata entries.
	 */
	private function get_image_size_metadata( int $attachment_id ): array {
		$metadata = [];
		
		// Check if this is an image attachment
		$mime_type = get_post_mime_type( $attachment_id );
		if( !$mime_type || strpos( $mime_type, 'image/' ) !== 0 ) {
			return $metadata;
		}
		
		// Get all registered image sizes
		$image_sizes = get_intermediate_image_sizes();
		
		// Add 'full' size
		$image_sizes[] = 'full';
		
		foreach( $image_sizes as $size_name ) {
			// Get the image URL for this size
			$image_data = wp_get_attachment_image_src( $attachment_id, $size_name );
			
			if( $image_data && !empty( $image_data[0] ) ) {
				$url    = $image_data[0];
				$width  = $image_data[1] ?? 0;
				$height = $image_data[2] ?? 0;
				
				// Format the display value with dimensions
				$display_value = $url;
				if( $width && $height ) {
					$display_value .= " ({$width}x{$height})";
				}
				
				$metadata[] = [
					'key'           => '_image_size_' . $size_name,
					'value'         => $url,
					'display_value' => $display_value,
					'is_serialized' => false,
					'is_protected'  => true,
					'is_image_size' => true,
					'is_readonly'   => true,
				];
			}
		}
		
		return $metadata;
	}
}