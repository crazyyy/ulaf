<?php
/**
 * Redirect Manager Quick Edit and Bulk Edit
 *
 * Handles quick edit and bulk edit functionality
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing quick edit and bulk edit
 */
class ASENHA_Redirect_Manager_Quick_Bulk_Edit {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		add_action( 'quick_edit_custom_box', array( $this, 'add_quick_edit_fields' ), 10, 2 );
		add_action( 'bulk_edit_custom_box', array( $this, 'add_bulk_edit_fields' ), 10, 2 );
		add_action( 'save_post_asenha_redirect', array( $this, 'save_quick_edit_data' ) );
		add_action( 'bulk_edit_posts', array( $this, 'save_bulk_edit_data' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_quick_edit_script' ) );
	}

	/**
	 * Add quick edit fields
	 *
	 * @since 8.1.0
	 * @param string $column_name The column name
	 * @param string $post_type The post type
	 */
	public function add_quick_edit_fields( $column_name, $post_type ) {
		// We're only working with inline data added to the 'redirect_from' column, so we check for that.
		if ( 'asenha_redirect' !== $post_type || 'redirect_from' !== $column_name ) {
			return;
		}
		
		// Get available groups
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// The following div is added after div.inline-edit-col-left and div.inline-edit-col-right 
		// inside the Quick Edit section (.inline-edit-wrapper)
		?>
		<div class="asenha-redirect-quick-edit-fields">
			<label class="asenha-qe-redirect-from">
				<span class="title"><?php esc_html_e( 'Redirect From', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="redirect_from_quick" class="redirect_from_quick" value="" />
				</span>
			</label>
			
			<label class="asenha-qe-regex-enabled">
				<span class="title"><?php esc_html_e( 'Enable regular expression (Regex)', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<input type="checkbox" name="redirect_regex_enabled_quick" class="redirect_regex_enabled_quick" value="1" />
				</span>
			</label>
			
			<label class="asenha-qe-redirect-to">
				<span class="title"><?php esc_html_e( 'Redirect To', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="redirect_to_quick" class="redirect_to_quick" value="" />
				</span>
			</label>
			
			<label class="asenha-qe-strip-params">
				<span class="title"><?php esc_html_e( 'Do not pass URL / query parameters', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<input type="checkbox" name="redirect_strip_params_quick" class="redirect_strip_params_quick" value="1" />
				</span>
			</label>
			
			<label class="asenha-qe-action-type">
				<span class="title"><?php esc_html_e( 'When Matched', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_action_type_quick" class="redirect_action_type_quick">
						<option value="redirect"><?php esc_html_e( 'Redirect to URL', 'admin-site-enhancements' ); ?></option>
						<option value="error"><?php esc_html_e( 'Show error message', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-qe-status-code">
				<span class="title"><?php esc_html_e( 'HTTP Status Code', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_http_status_code_quick" class="redirect_http_status_code_quick">
						<option value="301" data-action-type="redirect">301 <?php esc_html_e( 'Moved Permanently', 'admin-site-enhancements' ); ?></option>
						<option value="302" data-action-type="redirect">302 <?php esc_html_e( 'Found', 'admin-site-enhancements' ); ?></option>
						<option value="303" data-action-type="redirect">303 <?php esc_html_e( 'See Other', 'admin-site-enhancements' ); ?></option>
						<option value="304" data-action-type="redirect">304 <?php esc_html_e( 'Not Modified', 'admin-site-enhancements' ); ?></option>
						<option value="307" data-action-type="redirect">307 <?php esc_html_e( 'Temporary Redirect', 'admin-site-enhancements' ); ?></option>
						<option value="308" data-action-type="redirect">308 <?php esc_html_e( 'Permanent Redirect', 'admin-site-enhancements' ); ?></option>
						<option value="400" data-action-type="error">400 <?php esc_html_e( 'Bad Request', 'admin-site-enhancements' ); ?></option>
						<option value="401" data-action-type="error">401 <?php esc_html_e( 'Unauthorized', 'admin-site-enhancements' ); ?></option>
						<option value="403" data-action-type="error">403 <?php esc_html_e( 'Forbidden', 'admin-site-enhancements' ); ?></option>
						<option value="404" data-action-type="error">404 <?php esc_html_e( 'Not Found', 'admin-site-enhancements' ); ?></option>
						<option value="410" data-action-type="error">410 <?php esc_html_e( 'Gone', 'admin-site-enhancements' ); ?></option>
						<option value="500" data-action-type="error">500 <?php esc_html_e( 'Internal Server Error', 'admin-site-enhancements' ); ?></option>
						<option value="501" data-action-type="error">501 <?php esc_html_e( 'Not Implemented', 'admin-site-enhancements' ); ?></option>
						<option value="503" data-action-type="error">503 <?php esc_html_e( 'Service Unavailable', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-qe-group">
				<span class="title"><?php esc_html_e( 'Group', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_group_quick" class="redirect_group_quick">
						<option value=""><?php esc_html_e( 'No Group', 'admin-site-enhancements' ); ?></option>
						<?php foreach ( $groups as $group ) : ?>
							<option value="<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $group ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</label>
			
			<label class="asenha-qe-notes">
				<span class="title"><?php esc_html_e( 'Notes', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<textarea name="redirect_notes_quick" class="redirect_notes_quick" rows="3"></textarea>
				</span>
			</label>
			
			<label class="asenha-qe-message">
				<span class="title"><?php esc_html_e( 'Custom Message (for error codes)', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<textarea name="redirect_message_quick" class="redirect_message_quick" rows="2"></textarea>
				</span>
			</label>
		</div>
		<?php
	}

	/**
	 * Add bulk edit fields
	 *
	 * @since 8.1.0
	 * @param string $column_name The column name
	 * @param string $post_type The post type
	 */
	public function add_bulk_edit_fields( $column_name, $post_type ) {
		if ( 'asenha_redirect' !== $post_type || 'redirect_from' !== $column_name ) {
			return;
		}
		
		// Get available groups
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// The following div is added after div.inline-edit-col-left and div.inline-edit-col-right 
		// inside the Bulk Edit section (.inline-edit-wrapper)
		?>
		<div class="asenha-redirect-bulk-edit-fields">
			<label class="asenha-be-action-type">
				<span class="title"><?php esc_html_e( 'When Matched', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_action_type_bulk" class="redirect_action_type_bulk">
						<option value="-1"><?php esc_html_e( '— No Change —', 'admin-site-enhancements' ); ?></option>
						<option value="redirect"><?php esc_html_e( 'Redirect to URL', 'admin-site-enhancements' ); ?></option>
						<option value="error"><?php esc_html_e( 'Show error message', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-be-status-code">
				<span class="title"><?php esc_html_e( 'HTTP Status Code', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_http_status_code_bulk" class="redirect_http_status_code_bulk">
						<option value="-1"><?php esc_html_e( '— No Change —', 'admin-site-enhancements' ); ?></option>
						<option value="301" data-action-type="redirect">301 <?php esc_html_e( 'Moved Permanently', 'admin-site-enhancements' ); ?></option>
						<option value="302" data-action-type="redirect">302 <?php esc_html_e( 'Found', 'admin-site-enhancements' ); ?></option>
						<option value="303" data-action-type="redirect">303 <?php esc_html_e( 'See Other', 'admin-site-enhancements' ); ?></option>
						<option value="304" data-action-type="redirect">304 <?php esc_html_e( 'Not Modified', 'admin-site-enhancements' ); ?></option>
						<option value="307" data-action-type="redirect">307 <?php esc_html_e( 'Temporary Redirect', 'admin-site-enhancements' ); ?></option>
						<option value="308" data-action-type="redirect">308 <?php esc_html_e( 'Permanent Redirect', 'admin-site-enhancements' ); ?></option>
						<option value="400" data-action-type="error">400 <?php esc_html_e( 'Bad Request', 'admin-site-enhancements' ); ?></option>
						<option value="401" data-action-type="error">401 <?php esc_html_e( 'Unauthorized', 'admin-site-enhancements' ); ?></option>
						<option value="403" data-action-type="error">403 <?php esc_html_e( 'Forbidden', 'admin-site-enhancements' ); ?></option>
						<option value="404" data-action-type="error">404 <?php esc_html_e( 'Not Found', 'admin-site-enhancements' ); ?></option>
						<option value="410" data-action-type="error">410 <?php esc_html_e( 'Gone', 'admin-site-enhancements' ); ?></option>
						<option value="500" data-action-type="error">500 <?php esc_html_e( 'Internal Server Error', 'admin-site-enhancements' ); ?></option>
						<option value="501" data-action-type="error">501 <?php esc_html_e( 'Not Implemented', 'admin-site-enhancements' ); ?></option>
						<option value="503" data-action-type="error">503 <?php esc_html_e( 'Service Unavailable', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-be-regex-enabled">
				<span class="title"><?php esc_html_e( 'Enable Regular Expression (Regex)', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_regex_enabled_bulk" class="redirect_regex_enabled_bulk">
						<option value="-1"><?php esc_html_e( '— No Change —', 'admin-site-enhancements' ); ?></option>
						<option value="1"><?php esc_html_e( 'Yes', 'admin-site-enhancements' ); ?></option>
						<option value="0"><?php esc_html_e( 'No', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-be-strip-params">
				<span class="title"><?php esc_html_e( 'Do not pass URL / query parameters', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_strip_params_bulk" class="redirect_strip_params_bulk">
						<option value="-1"><?php esc_html_e( '— No Change —', 'admin-site-enhancements' ); ?></option>
						<option value="1"><?php esc_html_e( 'Yes', 'admin-site-enhancements' ); ?></option>
						<option value="0"><?php esc_html_e( 'No', 'admin-site-enhancements' ); ?></option>
					</select>
				</span>
			</label>
			
			<label class="asenha-be-message">
				<span class="title"><?php esc_html_e( 'Custom Message (for error codes)', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<textarea name="redirect_message_bulk" class="redirect_message_bulk" rows="2" placeholder="<?php esc_attr_e( 'Leave blank for no change', 'admin-site-enhancements' ); ?>"></textarea>
				</span>
			</label>
			
			<label class="asenha-be-group">
				<span class="title"><?php esc_html_e( 'Group', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<select name="redirect_group_bulk" class="redirect_group_bulk">
						<option value="-1"><?php esc_html_e( '— No Change —', 'admin-site-enhancements' ); ?></option>
						<option value=""><?php esc_html_e( 'No Group', 'admin-site-enhancements' ); ?></option>
						<?php foreach ( $groups as $group ) : ?>
							<option value="<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $group ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>
			</label>
			
			<label class="asenha-be-notes">
				<span class="title"><?php esc_html_e( 'Notes', 'admin-site-enhancements' ); ?></span>
				<span class="input-text-wrap">
					<textarea name="redirect_notes_bulk" class="redirect_notes_bulk" rows="3" placeholder="<?php esc_attr_e( 'Leave blank for no change', 'admin-site-enhancements' ); ?>"></textarea>
				</span>
			</label>
		</div>
		<?php
	}

	/**
	 * Save quick edit data
	 *
	 * @since 8.1.0
	 * @param int $post_id The post ID
	 */
	public function save_quick_edit_data( $post_id ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Check if this is quick edit
		if ( ! isset( $_POST['redirect_from_quick'] ) ) {
			return;
		}
		
		// Handle quick edit
		if ( isset( $_POST['redirect_from_quick'] ) ) {
			$redirect_from = sanitize_text_field( $_POST['redirect_from_quick'] );
			update_post_meta( $post_id, '_redirect_from', $redirect_from );
			
			// Update post title to match redirect_from value
			if ( ! empty( $redirect_from ) ) {
				// Remove the save_post hook temporarily to avoid infinite loop
				remove_action( 'save_post_asenha_redirect', array( $this, 'save_quick_edit_data' ) );
				
				wp_update_post( array(
					'ID'         => $post_id,
					'post_title' => $redirect_from,
				) );
				
				// Re-add the save_post hook
				add_action( 'save_post_asenha_redirect', array( $this, 'save_quick_edit_data' ) );
			}
		}
		
		if ( isset( $_POST['redirect_to_quick'] ) ) {
			update_post_meta( $post_id, '_redirect_to', esc_url_raw( $_POST['redirect_to_quick'] ) );
		}
		
		if ( isset( $_POST['redirect_action_type_quick'] ) ) {
			// Action type is saved implicitly via the status code
			// but we can use it to validate or provide context if needed
		}
		
		if ( isset( $_POST['redirect_http_status_code_quick'] ) ) {
			update_post_meta( $post_id, '_redirect_http_status_code', absint( $_POST['redirect_http_status_code_quick'] ) );
		}
		
		$regex_enabled = isset( $_POST['redirect_regex_enabled_quick'] ) ? true : false;
		update_post_meta( $post_id, '_redirect_from_regex', $regex_enabled );
		
		$strip_query_params = isset( $_POST['redirect_strip_params_quick'] ) ? true : false;
		update_post_meta( $post_id, '_redirect_strip_query_params', $strip_query_params );
		
		if ( isset( $_POST['redirect_group_quick'] ) ) {
			update_post_meta( $post_id, '_redirect_group', sanitize_text_field( $_POST['redirect_group_quick'] ) );
		}
		
		if ( isset( $_POST['redirect_notes_quick'] ) ) {
			update_post_meta( $post_id, '_redirect_notes', wp_kses_post( $_POST['redirect_notes_quick'] ) );
		}
		
		if ( isset( $_POST['redirect_message_quick'] ) ) {
			update_post_meta( $post_id, '_redirect_message', wp_kses_post( $_POST['redirect_message_quick'] ) );
		}
		
		// Bust cache when redirect is updated
		$cache = new ASENHA_Redirect_Manager_Cache();
		$cache->bust_cache();
	}

	/**
	 * Save bulk edit data
	 *
	 * @since 8.1.0
	 * @param int[] $updated An array of updated post IDs
	 * @param array $shared_post_data Associative array containing the post data
	 */
	public function save_bulk_edit_data( $updated, $shared_post_data ) {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Verify we have bulk edit data
		if ( ! isset( $_REQUEST['redirect_http_status_code_bulk'] ) ) {
			return;
		}
		
		// Loop through each updated post
		foreach ( $updated as $post_id ) {
			// Verify this is our post type
			if ( get_post_type( $post_id ) !== 'asenha_redirect' ) {
				continue;
			}
			
			// Update action type (implicit via status code)
			if ( isset( $_REQUEST['redirect_action_type_bulk'] ) && $_REQUEST['redirect_action_type_bulk'] != '-1' ) {
				// Action type is saved implicitly via the status code
				// but we can use it to validate or provide context if needed
			}
			
			// Update HTTP status code
			if ( isset( $_REQUEST['redirect_http_status_code_bulk'] ) && $_REQUEST['redirect_http_status_code_bulk'] != '-1' ) {
				update_post_meta( $post_id, '_redirect_http_status_code', absint( $_REQUEST['redirect_http_status_code_bulk'] ) );
			}
			
			// Update regex enabled
			if ( isset( $_REQUEST['redirect_regex_enabled_bulk'] ) && $_REQUEST['redirect_regex_enabled_bulk'] != '-1' ) {
				$regex_enabled = $_REQUEST['redirect_regex_enabled_bulk'] === '1' ? true : false;
				update_post_meta( $post_id, '_redirect_from_regex', $regex_enabled );
			}
			
			// Update strip query params
			if ( isset( $_REQUEST['redirect_strip_params_bulk'] ) && $_REQUEST['redirect_strip_params_bulk'] != '-1' ) {
				$strip_query_params = $_REQUEST['redirect_strip_params_bulk'] === '1' ? true : false;
				update_post_meta( $post_id, '_redirect_strip_query_params', $strip_query_params );
			}
			
			// Update custom message
			if ( isset( $_REQUEST['redirect_message_bulk'] ) && ! empty( $_REQUEST['redirect_message_bulk'] ) ) {
				update_post_meta( $post_id, '_redirect_message', wp_kses_post( $_REQUEST['redirect_message_bulk'] ) );
			}
			
			// Update group
			if ( isset( $_REQUEST['redirect_group_bulk'] ) && $_REQUEST['redirect_group_bulk'] != '-1' ) {
				update_post_meta( $post_id, '_redirect_group', sanitize_text_field( $_REQUEST['redirect_group_bulk'] ) );
			}
			
			// Update notes
			if ( isset( $_REQUEST['redirect_notes_bulk'] ) && ! empty( $_REQUEST['redirect_notes_bulk'] ) ) {
				update_post_meta( $post_id, '_redirect_notes', wp_kses_post( $_REQUEST['redirect_notes_bulk'] ) );
			}
		}
		
		// Bust cache once after all updates
		$cache = new ASENHA_Redirect_Manager_Cache();
		$cache->bust_cache();
	}

	/**
	 * Enqueue quick edit script
	 *
	 * @since 8.1.0
	 * @param string $hook The current admin page hook
	 */
	public function enqueue_quick_edit_script( $hook ) {
		if ( 'edit.php' !== $hook ) {
			return;
		}
		
		global $post_type;
		if ( 'asenha_redirect' !== $post_type ) {
			return;
		}
		
		// Script is already enqueued by metaboxes class
		// Add inline script for quick edit field reorganization and population
		$inline_script = "
		jQuery(document).ready(function($) {
			// Reorganize fields and populate quick edit
			$('#the-list').on('click', '.editinline', function() {
				var post_id = $(this).closest('tr').attr('id').replace('post-', '');
				var \$row = $('#edit-' + post_id);
				
				// Wait for WordPress to render quick edit
				setTimeout(function() {
					// Find our custom fields container
					var \$customFields = \$row.find('.asenha-redirect-quick-edit-fields');
					
					if (\$customFields.length === 0) {
						return;
					}
					
					// Find WordPress's existing columns
					var \$leftCol = \$row.find('.inline-edit-col-left .inline-edit-col').first();
					var \$rightCol = \$row.find('.inline-edit-col-right .inline-edit-col').first();
					
					if (\$leftCol.length === 0 || \$rightCol.length === 0) {
						return;
					}
					
					// Move fields to left column (after title field)
					\$customFields.find('.asenha-qe-redirect-from').appendTo(\$leftCol).show();
					\$customFields.find('.asenha-qe-regex-enabled').appendTo(\$leftCol).show();
					\$customFields.find('.asenha-qe-redirect-to').appendTo(\$leftCol).show();
					\$customFields.find('.asenha-qe-strip-params').appendTo(\$leftCol).show();
					\$customFields.find('.asenha-qe-action-type').appendTo(\$leftCol).show();
					\$customFields.find('.asenha-qe-status-code').appendTo(\$leftCol).show();
					
					// Move fields to right column (before status field)
					var \$statusField = \$rightCol.find('.inline-edit-status');
					\$customFields.find('.asenha-qe-group').insertBefore(\$statusField).show();
					\$customFields.find('.asenha-qe-notes').insertBefore(\$statusField).show();
					\$customFields.find('.asenha-qe-message').insertBefore(\$statusField).show();
					
					// Populate field values
					var inline_data = $('#asenha_redirect_data_' + post_id);
					if (inline_data.length > 0) {
						var statusCode = inline_data.find('.status_code').text();
						var statusCodeInt = parseInt(statusCode);
						var redirectCodes = [301, 302, 303, 304, 307, 308];
						var errorCodes = [400, 401, 403, 404, 410, 500, 501, 503];
						var actionType = errorCodes.indexOf(statusCodeInt) !== -1 ? 'error' : 'redirect';
						
						$('.redirect_from_quick').val(inline_data.find('.redirect_from').text());
						$('.redirect_regex_enabled_quick').prop('checked', inline_data.find('.regex_enabled').text() === '1');
						$('.redirect_to_quick').val(inline_data.find('.redirect_to').text());
						$('.redirect_strip_params_quick').prop('checked', inline_data.find('.strip_params').text() === '1');
						$('.redirect_action_type_quick').val(actionType);
						$('.redirect_http_status_code_quick').val(statusCode);
						$('.redirect_group_quick').val(inline_data.find('.group').text());
						$('.redirect_notes_quick').val(inline_data.find('.notes').text());
						$('.redirect_message_quick').val(inline_data.find('.message').text());
					}
				}, 100);
			});
			
			// Reorganize fields for bulk edit
			$('#doaction, #doaction2').on('click', function(e) {
				var action = $(this).attr('id') === 'doaction' ? $('#bulk-action-selector-top').val() : $('#bulk-action-selector-bottom').val();
				
				if (action === 'edit') {
					// Wait for WordPress to render bulk edit
					setTimeout(function() {
						var \$bulkRow = $('#bulk-edit');
						
						// Find our custom fields container
						var \$customFields = \$bulkRow.find('.asenha-redirect-bulk-edit-fields');
						
						if (\$customFields.length === 0) {
							return;
						}
						
						// Find WordPress's existing columns
						var \$leftCol = \$bulkRow.find('.inline-edit-col-left .inline-edit-col').first();
						var \$rightCol = \$bulkRow.find('.inline-edit-col-right .inline-edit-col').first();
						
						if (\$leftCol.length === 0 || \$rightCol.length === 0) {
							return;
						}
						
						// Move fields to left column
						\$customFields.find('.asenha-be-action-type').appendTo(\$leftCol).show();
						\$customFields.find('.asenha-be-status-code').appendTo(\$leftCol).show();
						\$customFields.find('.asenha-be-regex-enabled').appendTo(\$leftCol).show();
						\$customFields.find('.asenha-be-strip-params').appendTo(\$leftCol).show();
						
						// Move fields to right column (before status field)
						var \$statusField = \$rightCol.find('.inline-edit-status');
						\$customFields.find('.asenha-be-message').insertBefore(\$statusField).show();
						\$customFields.find('.asenha-be-notes').insertBefore(\$statusField).show();
						\$customFields.find('.asenha-be-group').insertBefore(\$statusField).show();
						
						// Setup status code filtering for bulk edit
						setupBulkEditStatusCodeFiltering();
					}, 100);
				}
			});
			
			// Bulk edit status code filtering
			function setupBulkEditStatusCodeFiltering() {
				var \$actionType = $('.redirect_action_type_bulk');
				var \$statusCode = $('.redirect_http_status_code_bulk');
				
				if (\$actionType.length === 0 || \$statusCode.length === 0) {
					return;
				}

				// Define status code groups
				var redirectCodes = [301, 302, 303, 304, 307, 308];
				var errorCodes = [400, 401, 403, 404, 410, 500, 501, 503];

				// Filter status code options based on action type
				var filterStatusCodes = function(actionType) {
					if (actionType === '-1') {
						// Show all options when 'No Change' is selected
						\$statusCode.find('option').show();
						return;
					}
					
					var \$options = \$statusCode.find('option');
					var currentValue = \$statusCode.val();

					// Set default values based on action type
					var defaultValues = {
						'redirect': '302',
						'error': '403'
					};

					// Show/hide options based on action type
					\$options.each(function() {
						var \$option = $(this);
						var optionValue = \$option.val();
						var optionActionType = \$option.attr('data-action-type');
						
						// Always show 'No Change' option
						if (optionValue === '-1') {
							\$option.show();
							return;
						}
						
						if (optionActionType === actionType) {
							\$option.show();
						} else {
							\$option.hide();
						}
					});

					// If current value is hidden or is 'No Change', set to default
					if (currentValue === '-1' || \$statusCode.find('option[value=\"' + currentValue + '\"]').is(':hidden')) {
						\$statusCode.val(defaultValues[actionType] || '-1');
					}
				};

				// Handle action type changes
				\$actionType.on('change', function() {
					var actionType = $(this).val();
					filterStatusCodes(actionType);
				});

				// Initialize on setup
				filterStatusCodes(\$actionType.val());
			}
		});
		";
		
		wp_add_inline_script( 'jquery', $inline_script );
	}
}

