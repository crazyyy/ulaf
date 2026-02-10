<?php
/**
 * Class: Responsible for Hooks Management views and operations.
 *
 * Add, edit, and manage hooks to capture.
 *
 * @package advanced-analytics
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Helpers\Settings;
use ADVAN\Lists\Hooks_Management_List;
use ADVAN\Entities\Hooks_Management_Entity;
use ADVAN\Controllers\Hooks_Capture;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Lists\Views\Hooks_Management_View' ) ) {
	/**
	 * Responsible for Hooks Management views.
	 *
	 * @since 4.5.0
	 */
	class Hooks_Management_View {

		/**
		 * Displays the hooks management page.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function analytics_hooks_management_page() {
			// Capability guard.
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage hooks.', '0-day-analytics' ) );
			}

			?>
			<script>
				if( 'undefined' != typeof localStorage ){
					var skin = localStorage.getItem('aadvana-backend-skin');
					if( skin == 'dark' ){
						var element = document.getElementsByTagName("html")[0];
						element.classList.add("aadvana-darkskin");
					}
				}
			</script>
			<?php

			$action = ! empty( $_REQUEST['action'] ) ? \sanitize_key( $_REQUEST['action'] ) : '';

			// Note: toggle and delete are handled in handle_table_actions during load hook.
			if ( in_array( $action, array( 'new', 'edit' ), true ) ) {
				self::edit_hook();
			} else {
				self::list_hooks();
			}
		}

		/**
		 * List hooks.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function list_hooks() {
			$hooks_management = new Hooks_Management_List( '' );
			$hooks_management->prepare_items();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'Hooks Management', '0-day-analytics' ); ?></h1>
				<a href="<?php echo \esc_url( \admin_url( 'admin.php?page=' . Hooks_Management_List::HOOKS_MANAGEMENT_MENU_SLUG . '&action=new' ) ); ?>" class="page-title-action">
					<?php \esc_html_e( 'Add New', '0-day-analytics' ); ?>
				</a>

				<hr class="wp-header-end">
				<?php
				if ( ! Settings::get_option( 'hooks_capture_module_enabled' ) ) {
					?>
				<div id="advana-status-error" class="notice notice-error">
					<?php
					printf(
						'<p>%1$s</p>',
						sprintf(
							/* translators: %s: Link to settings. */
							\esc_html__( 'The hooks capture module is disabled. To enable it go to: %s', '0-day-analytics' ),
							'<a href="' . \esc_url( \add_query_arg( array( 'page' => Settings::SETTINGS_MENU_SLUG ), \admin_url( 'admin.php' ) ) ) . '#aadvana-options-tab-hooks">' . \esc_html__( 'settings', '0-day-analytics' ) . '</a>'
						)
					);
					?>
				</div>
					<?php
				}

				if ( isset( $_GET['updated'] ) ) {
					?>
				<div id="message" class="updated notice is-dismissible">
					<p>
					<?php
					printf(
						/* translators: %d: Number of updated items */
						\esc_html( _n( '%d hook updated.', '%d hooks updated.', \absint( $_GET['updated'] ), '0-day-analytics' ) ),
						\absint( $_GET['updated'] )
					);
					?>
					</p>
				</div>
					<?php
				}

				if ( isset( $_GET['saved'] ) ) {
					?>
				<div id="message" class="updated notice is-dismissible">
					<p><?php \esc_html_e( 'Hook saved successfully.', '0-day-analytics' ); ?></p>
				</div>
					<?php
				}
				?>

				<form id="hooks-management-filter" method="get">
				<?php

				$page  = isset( $_GET['page'] ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
				$paged = isset( $_GET['paged'] ) ? \filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

				printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
				printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

				echo '<div style="clear:both; float:right">';
				$hooks_management->search_box(
					\__( 'Search', '0-day-analytics' ),
					'hooks-management-find'
				);
				echo '</div>';
				$hooks_management->display();

				?>
				</form>
			</div>
			<style>
				.hook-status.enabled {
					color: #46b450;
					font-weight: bold;
				}
				.hook-status.disabled {
					color: #dc3232;
				}
			</style>
			<?php
		}

		/**
		 * Edit hook form.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function edit_hook() {
			$id   = isset( $_REQUEST['id'] ) ? \absint( $_REQUEST['id'] ) : 0;
			$hook = null;

			if ( $id ) {
				$hook = Hooks_Management_Entity::load( 'id = %d', array( $id ) );

				if ( ! $hook ) {
					\wp_die( \esc_html__( 'Hook not found.', '0-day-analytics' ) );
				}
			}

			$is_new = ! $hook;

			?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php
					echo $is_new ? \esc_html__( 'Add New Hook', '0-day-analytics' ) : \esc_html__( 'Edit Hook', '0-day-analytics' );
					?>
				</h1>
				<a href="<?php echo \esc_url( \admin_url( 'admin.php?page=' . Hooks_Management_List::HOOKS_MANAGEMENT_MENU_SLUG ) ); ?>" class="page-title-action">
					<?php \esc_html_e( 'Back to List', '0-day-analytics' ); ?>
				</a>

				<hr class="wp-header-end">

				<form method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo \esc_attr( Hooks_Management_List::SAVE_ACTION ); ?>" />
					<input type="hidden" name="id" value="<?php echo \esc_attr( $id ); ?>" />
					<?php \wp_nonce_field( 'save_hook_' . $id ); ?>

					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="hook_name"><?php \esc_html_e( 'Hook Name', '0-day-analytics' ); ?> <span class="required">*</span></label>
								</th>
								<td>
									<input type="text" name="hook_name" id="hook_name" class="regular-text" value="<?php echo $hook ? \esc_attr( $hook['hook_name'] ) : ''; ?>" required />
									<p class="description"><?php \esc_html_e( 'The name of the WordPress hook to capture (e.g., wp_login, user_register, save_post)', '0-day-analytics' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="hook_label"><?php \esc_html_e( 'Human-readable Name', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<input type="text" name="hook_label" id="hook_label" class="regular-text" value="<?php echo $hook && isset( $hook['hook_label'] ) ? \esc_attr( $hook['hook_label'] ) : ''; ?>" />
									<p class="description"><?php \esc_html_e( 'Optional friendly label to display in capture logs before the hook name.', '0-day-analytics' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="hook_type"><?php \esc_html_e( 'Hook Type', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<select name="hook_type" id="hook_type">
										<option value="action" <?php echo ( $hook && 'action' === $hook['hook_type'] ) || ! $hook ? 'selected' : ''; ?>><?php \esc_html_e( 'Action', '0-day-analytics' ); ?></option>
										<option value="filter" <?php echo $hook && 'filter' === $hook['hook_type'] ? 'selected' : ''; ?>><?php \esc_html_e( 'Filter', '0-day-analytics' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="priority"><?php \esc_html_e( 'Priority', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<input type="number" name="priority" id="priority" value="<?php echo $hook ? \esc_attr( $hook['priority'] ) : '10'; ?>" min="1" max="9999" />
									<p class="description"><?php \esc_html_e( 'Hook priority (default: 10). Lower numbers run earlier.', '0-day-analytics' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="category"><?php \esc_html_e( 'Category', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<select name="category" id="category">
										<option value="custom" <?php echo ( $hook && 'custom' === $hook['category'] ) || ! $hook ? 'selected' : ''; ?>><?php \esc_html_e( 'Custom', '0-day-analytics' ); ?></option>
										<option value="auth" <?php echo $hook && 'auth' === $hook['category'] ? 'selected' : ''; ?>><?php \esc_html_e( 'Authentication', '0-day-analytics' ); ?></option>
										<option value="user" <?php echo $hook && 'user' === $hook['category'] ? 'selected' : ''; ?>><?php \esc_html_e( 'User', '0-day-analytics' ); ?></option>
										<option value="post" <?php echo $hook && 'post' === $hook['category'] ? 'selected' : ''; ?>><?php \esc_html_e( 'Post', '0-day-analytics' ); ?></option>
										<option value="update" <?php echo $hook && 'update' === $hook['category'] ? 'selected' : ''; ?>><?php \esc_html_e( 'Update', '0-day-analytics' ); ?></option>
										<option value="core" <?php echo $hook && 'core' === $hook['category'] ? 'selected' : ''; ?>><?php \esc_html_e( 'Core', '0-day-analytics' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php \esc_html_e( 'Options', '0-day-analytics' ); ?>
								</th>
								<td>
									<fieldset>
										<label>
											<input type="checkbox" name="enabled" value="1" <?php echo ( $hook && ! empty( $hook['enabled'] ) ) || ! $hook ? 'checked' : ''; ?> />
											<?php \esc_html_e( 'Enabled', '0-day-analytics' ); ?>
										</label>
										<br />
										<label>
											<input type="checkbox" name="capture_args" value="1" <?php echo ( $hook && ! empty( $hook['capture_args'] ) ) || ! $hook ? 'checked' : ''; ?> />
											<?php \esc_html_e( 'Capture Arguments', '0-day-analytics' ); ?>
										</label>
										<br />
										<label>
											<input type="checkbox" name="capture_output" value="1" <?php echo ( $hook && ! empty( $hook['capture_output'] ) ) || ! $hook ? 'checked' : ''; ?> />
											<?php \esc_html_e( 'Capture Output', '0-day-analytics' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="description"><?php \esc_html_e( 'Description', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<textarea name="description" id="description" rows="3" class="large-text"><?php echo $hook ? \esc_textarea( $hook['description'] ) : ''; ?></textarea>
									<p class="description"><?php \esc_html_e( 'Optional description of what this hook captures.', '0-day-analytics' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">									<label for="group_id"><?php \esc_html_e( 'Hook Group', '0-day-analytics' ); ?></label>
								</th>
								<td>
									<select name="group_id" id="group_id">
										<option value="0" <?php echo ( ! $hook || ! isset( $hook['group_id'] ) || (int) $hook['group_id'] === 0 ) ? 'selected' : ''; ?>><?php \esc_html_e( 'No Group', '0-day-analytics' ); ?></option>
										<?php
										if ( \class_exists( '\ADVAN\Entities\Hook_Groups_Entity' ) ) {
											$groups = \ADVAN\Entities\Hook_Groups_Entity::get_groups_array();
											foreach ( $groups as $group_id => $group ) {
												$selected = ( $hook && isset( $hook['group_id'] ) && (int) $hook['group_id'] === (int) $group_id ) ? 'selected' : '';
												echo '<option value="' . \esc_attr( $group_id ) . '" ' . $selected . '>' . \esc_html( $group['name'] ) . '</option>';
											}
										}
										?>
									</select>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to settings page */
											\esc_html__( 'Assign this hook to a group for better organization and visual identification. You can manage the groups (names and colors) by editing them on %s.', '0-day-analytics' ),
											'<a href="' . \esc_url( \admin_url( 'admin.php?page=advan_logs_settings#aadvana-options-tab-hooks-capture' ) ) . '">' . \esc_html__( 'this page', '0-day-analytics' ) . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">									<?php \esc_html_e( 'Hook Arguments', '0-day-analytics' ); ?>
								</th>
								<td>
									<div id="hook-parameters-container">
										<?php
										$parameters = array();
										if ( $hook && ! empty( $hook['hook_parameters'] ) ) {
											$parameters = json_decode( $hook['hook_parameters'], true );
											if ( ! is_array( $parameters ) ) {
												$parameters = array();
											}
										}

										if ( empty( $parameters ) ) {
											$parameters = array(
												array(
													'name' => '',
													'type' => 'string',
													'extraction_code' => '',
												),
											);
										}

										foreach ( $parameters as $index => $param ) :
											?>
											<div class="parameter-row" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
												<div style="margin-bottom: 10px;">
													<label style="display: inline-block; width: 150px; font-weight: 600;">
														<?php \esc_html_e( 'Argument Name:', '0-day-analytics' ); ?>
													</label>
													<input type="text" name="hook_parameters[<?php echo $index; ?>][name]" 
														value="<?php echo isset( $param['name'] ) ? \esc_attr( $param['name'] ) : ''; ?>" 
														class="regular-text" 
														placeholder="e.g., user_id, post_id, user_login" />
												</div>
												<div style="margin-bottom: 10px;">
													<label style="display: inline-block; width: 150px; font-weight: 600;">
														<?php \esc_html_e( 'Argument Type:', '0-day-analytics' ); ?>
													</label>
													<select name="hook_parameters[<?php echo $index; ?>][type]" style="width: 300px;">
														<optgroup label="<?php \esc_attr_e( 'Simple Types', '0-day-analytics' ); ?>">
															<option value="string" <?php echo isset( $param['type'] ) && 'string' === $param['type'] ? 'selected' : ''; ?>>String</option>
															<option value="int" <?php echo isset( $param['type'] ) && 'int' === $param['type'] ? 'selected' : ''; ?>>Integer</option>
															<option value="float" <?php echo isset( $param['type'] ) && 'float' === $param['type'] ? 'selected' : ''; ?>>Float</option>
															<option value="bool" <?php echo isset( $param['type'] ) && 'bool' === $param['type'] ? 'selected' : ''; ?>>Boolean</option>
															<option value="array" <?php echo isset( $param['type'] ) && 'array' === $param['type'] ? 'selected' : ''; ?>>Array</option>
															<option value="object" <?php echo isset( $param['type'] ) && 'object' === $param['type'] ? 'selected' : ''; ?>>Object</option>
														</optgroup>
														<optgroup label="<?php \esc_attr_e( 'WordPress Types', '0-day-analytics' ); ?>">
															<option value="user_id" <?php echo isset( $param['type'] ) && 'user_id' === $param['type'] ? 'selected' : ''; ?>>User ID</option>
															<option value="post_id" <?php echo isset( $param['type'] ) && 'post_id' === $param['type'] ? 'selected' : ''; ?>>Post ID</option>
															<option value="term_id" <?php echo isset( $param['type'] ) && 'term_id' === $param['type'] ? 'selected' : ''; ?>>Term ID</option>
															<option value="comment_id" <?php echo isset( $param['type'] ) && 'comment_id' === $param['type'] ? 'selected' : ''; ?>>Comment ID</option>
															<option value="blog_id" <?php echo isset( $param['type'] ) && 'blog_id' === $param['type'] ? 'selected' : ''; ?>>Blog ID</option>
															<option value="wp_user" <?php echo isset( $param['type'] ) && 'wp_user' === $param['type'] ? 'selected' : ''; ?>>WP_User Object</option>
															<option value="wp_post" <?php echo isset( $param['type'] ) && 'wp_post' === $param['type'] ? 'selected' : ''; ?>>WP_Post Object</option>
															<option value="wp_error" <?php echo isset( $param['type'] ) && 'wp_error' === $param['type'] ? 'selected' : ''; ?>>WP_Error Object</option>
														</optgroup>
														<optgroup label="<?php \esc_attr_e( 'Advanced', '0-day-analytics' ); ?>">
															<option value="custom" <?php echo isset( $param['type'] ) && 'custom' === $param['type'] ? 'selected' : ''; ?>>Custom Extraction</option>
														</optgroup>
													</select>
												</div>
												<div class="extraction-code-wrapper" style="margin-bottom: 10px; <?php echo isset( $param['type'] ) && 'custom' === $param['type'] ? '' : 'display:none;'; ?>">
													<label style="display: inline-block; width: 150px; font-weight: 600; vertical-align: top;">
														<?php \esc_html_e( 'Extraction Code:', '0-day-analytics' ); ?>
													</label>
													<textarea name="hook_parameters[<?php echo $index; ?>][extraction_code]" 
														rows="3" 
														class="large-text code" 
														placeholder="return $value->some_property;"
														style="font-family: monospace; width: calc(100% - 160px);"><?php echo isset( $param['extraction_code'] ) ? \esc_textarea( $param['extraction_code'] ) : ''; ?></textarea>
													<p class="description" style="margin-left: 160px;">
														<?php \esc_html_e( 'PHP code to extract data from $value (the hook argument). Return the value to display.', '0-day-analytics' ); ?>
													</p>
												</div>
												<button type="button" class="button remove-parameter" style="margin-top: 5px;">
													<?php \esc_html_e( 'Remove Argument', '0-day-analytics' ); ?>
												</button>
											</div>
										<?php endforeach; ?>
									</div>
									<button type="button" id="add-parameter" class="button button-secondary" style="margin-top: 10px;">
										<?php \esc_html_e( '+ Add Hook Argument', '0-day-analytics' ); ?>
									</button>
									<p class="description">
										<?php \esc_html_e( 'Define the arguments this hook receives. For example, wp_login receives $user_login (string) and $user (WP_User). These will be displayed in a formatted way in the capture list.', '0-day-analytics' ); ?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>

					<p class="submit">
						<?php \submit_button( $is_new ? \__( 'Add Hook', '0-day-analytics' ) : \__( 'Update Hook', '0-day-analytics' ), 'primary', 'submit', false ); ?>
					</p>
				</form>
			</div>
			<script>
				jQuery(document).ready(function($) {
					var paramIndex = <?php echo count( $parameters ); ?>;

					// Show/hide extraction code based on type selection.
					$(document).on('change', 'select[name^="hook_parameters"]', function() {
						var $select = $(this);
						var $row = $select.closest('.parameter-row');
						var $codeWrapper = $row.find('.extraction-code-wrapper');
						
						if ($select.val() === 'custom') {
							$codeWrapper.slideDown();
						} else {
							$codeWrapper.slideUp();
						}
					});

					// Add new parameter.
					$('#add-parameter').on('click', function() {
						var html = '<div class="parameter-row" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">' +
							'<div style="margin-bottom: 10px;">' +
								'<label style="display: inline-block; width: 150px; font-weight: 600;"><?php \esc_html_e( 'Argument Name:', '0-day-analytics' ); ?></label>' +
								'<input type="text" name="hook_parameters[' + paramIndex + '][name]" class="regular-text" placeholder="e.g., user_id, post_id" />' +
							'</div>' +
							'<div style="margin-bottom: 10px;">' +
								'<label style="display: inline-block; width: 150px; font-weight: 600;"><?php \esc_html_e( 'Argument Type:', '0-day-analytics' ); ?></label>' +
								'<select name="hook_parameters[' + paramIndex + '][type]" style="width: 300px;">' +
									'<optgroup label="<?php \esc_attr_e( 'Simple Types', '0-day-analytics' ); ?>">' +
										'<option value="string">String</option>' +
										'<option value="int">Integer</option>' +
										'<option value="float">Float</option>' +
										'<option value="bool">Boolean</option>' +
										'<option value="array">Array</option>' +
										'<option value="object">Object</option>' +
									'</optgroup>' +
									'<optgroup label="<?php \esc_attr_e( 'WordPress Types', '0-day-analytics' ); ?>">' +
										'<option value="user_id">User ID</option>' +
										'<option value="post_id">Post ID</option>' +
										'<option value="term_id">Term ID</option>' +
										'<option value="comment_id">Comment ID</option>' +
										'<option value="blog_id">Blog ID</option>' +
										'<option value="wp_user">WP_User Object</option>' +
										'<option value="wp_post">WP_Post Object</option>' +
										'<option value="wp_error">WP_Error Object</option>' +
									'</optgroup>' +
									'<optgroup label="<?php \esc_attr_e( 'Advanced', '0-day-analytics' ); ?>">' +
										'<option value="custom">Custom Extraction</option>' +
									'</optgroup>' +
								'</select>' +
							'</div>' +
							'<div class="extraction-code-wrapper" style="margin-bottom: 10px; display: none;">' +
								'<label style="display: inline-block; width: 150px; font-weight: 600; vertical-align: top;"><?php \esc_html_e( 'Extraction Code:', '0-day-analytics' ); ?></label>' +
								'<textarea name="hook_parameters[' + paramIndex + '][extraction_code]" rows="3" class="large-text code" placeholder="return $value->some_property;" style="font-family: monospace; width: calc(100% - 160px);"></textarea>' +
								'<p class="description" style="margin-left: 160px;"><?php \esc_html_e( 'PHP code to extract data from $value. Return the value to display.', '0-day-analytics' ); ?></p>' +
							'</div>' +
							'<button type="button" class="button remove-parameter" style="margin-top: 5px;"><?php \esc_html_e( 'Remove Argument', '0-day-analytics' ); ?></button>' +
						'</div>';
						
						$('#hook-parameters-container').append(html);
						paramIndex++;
					});

					// Remove parameter.
					$(document).on('click', '.remove-parameter', function() {
						if ($('.parameter-row').length > 1) {
							$(this).closest('.parameter-row').remove();
						} else {
							alert('<?php \esc_html_e( 'You must have at least one argument defined.', '0-day-analytics' ); ?>');
						}
					});
				});
			</script>
			<style>
				.required {
					color: #dc3232;
				}
			</style>
			<?php
		}

		/**
		 * Save hook.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function save_hook() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage hooks.', '0-day-analytics' ) );
			}

			$id = isset( $_POST['id'] ) ? \absint( $_POST['id'] ) : 0;

			if ( ! \wp_verify_nonce( isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '', 'save_hook_' . $id ) ) {
				\wp_die( \esc_html__( 'Security check failed.', '0-day-analytics' ) );
			}

			$hook_name      = isset( $_POST['hook_name'] ) ? \sanitize_text_field( \wp_unslash( $_POST['hook_name'] ) ) : '';
			$hook_label     = isset( $_POST['hook_label'] ) ? \sanitize_text_field( \wp_unslash( $_POST['hook_label'] ) ) : '';
			$hook_type      = isset( $_POST['hook_type'] ) ? \sanitize_text_field( \wp_unslash( $_POST['hook_type'] ) ) : 'action';
			$priority       = isset( $_POST['priority'] ) ? \absint( $_POST['priority'] ) : 10;
			$category       = isset( $_POST['category'] ) ? \sanitize_text_field( \wp_unslash( $_POST['category'] ) ) : 'custom';
			$group_id       = isset( $_POST['group_id'] ) ? \absint( $_POST['group_id'] ) : 0;
			$enabled        = isset( $_POST['enabled'] ) ? 1 : 0;
			$capture_args   = isset( $_POST['capture_args'] ) ? 1 : 0;
			$capture_output = isset( $_POST['capture_output'] ) ? 1 : 0;
			$description    = isset( $_POST['description'] ) ? \sanitize_textarea_field( \wp_unslash( $_POST['description'] ) ) : '';

			// Process hook parameters.
			$hook_parameters = array();
			if ( isset( $_POST['hook_parameters'] ) && is_array( $_POST['hook_parameters'] ) ) {
				foreach ( $_POST['hook_parameters'] as $param ) {
					$param_name            = isset( $param['name'] ) ? \sanitize_text_field( \wp_unslash( $param['name'] ) ) : '';
					$param_type            = isset( $param['type'] ) ? \sanitize_text_field( \wp_unslash( $param['type'] ) ) : 'string';
					$param_extraction_code = isset( $param['extraction_code'] ) ? \wp_unslash( $param['extraction_code'] ) : '';

						// Only save if parameter name is not empty.
					if ( ! empty( $param_name ) ) {
						$hook_parameters[] = array(
							'name'            => $param_name,
							'type'            => $param_type,
							'extraction_code' => $param_extraction_code,
						);
					}
				}
			}

			if ( empty( $hook_name ) ) {
				\wp_die( \esc_html__( 'Hook name is required.', '0-day-analytics' ) );
			}

			$data = array(
				'hook_name'       => $hook_name,
				'hook_label'      => $hook_label,
				'hook_type'       => $hook_type,
				'priority'        => $priority,
				'category'        => $category,
				'group_id'        => $group_id,
				'enabled'         => $enabled,
				'capture_args'    => $capture_args,
				'capture_output'  => $capture_output,
				'description'     => $description,
				'hook_parameters' => ! empty( $hook_parameters ) ? \wp_json_encode( $hook_parameters ) : '',
				'date_modified'   => \microtime( true ),
			);

			if ( $id ) {
						// Update existing hook.
				Hooks_Management_Entity::update( $id, $data );
			} else {
						// Insert new hook.
				$data['date_added'] = \microtime( true );
				Hooks_Management_Entity::insert( $data );
			}

			Hooks_Management_Entity::clear_hook_labels_cache();

			// Clear cache and reload hooks.
			Hooks_Capture::clear_cache();
			\do_action( 'advan_hooks_management_updated' );

			\wp_safe_redirect(
				\add_query_arg(
					array(
						'page'  => Hooks_Management_List::HOOKS_MANAGEMENT_MENU_SLUG,
						'saved' => 1,
					),
					\admin_url( 'admin.php' )
				)
			);
			exit;
		}

		/**
		 * Toggle hook enabled status.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function toggle_hook() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage hooks.', '0-day-analytics' ) );
			}

			$id = isset( $_REQUEST['id'] ) ? \absint( $_REQUEST['id'] ) : 0;

			if ( ! \wp_verify_nonce( isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '', 'toggle_hook_' . $id ) ) {
				\wp_die( \esc_html__( 'Security check failed.', '0-day-analytics' ) );
			}

			if ( $id ) {
				Hooks_Management_Entity::toggle_enabled( $id );
				Hooks_Capture::clear_cache();
				\do_action( 'advan_hooks_management_updated' );
			}

			\wp_safe_redirect( \wp_get_referer() );
			exit;
		}

		/**
		 * Delete hook.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function delete_hook() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage hooks.', '0-day-analytics' ) );
			}

			$id = isset( $_REQUEST['id'] ) ? \absint( $_REQUEST['id'] ) : 0;

			if ( ! \wp_verify_nonce( isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '', 'delete_hook_' . $id ) ) {
				\wp_die( \esc_html__( 'Security check failed.', '0-day-analytics' ) );
			}

			if ( $id ) {
				Hooks_Management_Entity::delete_by_id( $id );
				Hooks_Capture::clear_cache();
				\do_action( 'advan_hooks_management_updated' );
			}

			\wp_safe_redirect(
				\add_query_arg(
					array(
						'page'    => Hooks_Management_List::HOOKS_MANAGEMENT_MENU_SLUG,
						'deleted' => 1,
					),
					\admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}
}
