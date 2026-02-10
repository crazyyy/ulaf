<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post_Status_Settings class
 */
class Post_Status_Settings {
	/**
	 * Settings fields
	 *
	 * @var array
	 */
	protected $setting_fields;

	/**
	 * Pro Settings fields
	 *
	 * @var array
	 */
	protected $pro_setting_fields = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add Settings fields
	 *
	 * @return void
	 */
	protected function add_setting_fields() {
		$this->setting_fields = [
			'name'        => [
				'id'          => 'post-status-name',
				'type'        => 'text',
				'name'        => 'poststatus[name]',
				'value'       => '',
				'label'       => __( 'Name', 'admin-optimizer' ),
				'description' => __( 'The name of the custom post status.', 'admin-optimizer' ),
				'required'    => true,
			],
			'slug'        => [
				'id'          => 'post-status-slug',
				'type'        => 'text',
				'name'        => 'poststatus[slug]',
				'value'       => '',
				'label'       => __( 'Slug', 'admin-optimizer' ),
				'description' => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'admin-optimizer' ),
				'required'    => true,
			],
			'description' => [
				'id'          => 'post-status-description',
				'type'        => 'textarea',
				'name'        => 'poststatus[description]',
				'value'       => '',
				'label'       => __( 'Description', 'admin-optimizer' ),
				'description' => __( 'Describe what this custom post status is used for.', 'admin-optimizer' ),
				'required'    => true,
			],
		];
	}

	/**
	 * Add Settings fields
	 *
	 * @return void
	 */
	protected function add_pro_setting_fields() {
		$this->pro_setting_fields['post_types'] = $this->add_post_types_field();
		$this->pro_setting_fields['user_roles'] = $this->add_user_roles_field();
	}

	/**
	 * Add post types Settings field
	 *
	 * @return array
	 */
	private function add_post_types_field() {
		$custom_post_types = get_post_types(
			[
				'_builtin' => false,
				'show_ui'  => 'true',
			],
			'objects'
		);
		$custom_post_types = array_diff_key(
			$custom_post_types,
			array_fill_keys(
				[
					'lazyblocks',
					'lazyblocks_templates',
					'attachment',
					'wp_block',
					'wp_navigation',
				],
				true
			)
		);

		$post_types = [];
		$disabled   = [ 'post', 'page' ];
		foreach ( $custom_post_types as $custom_post_type ) {
			$post_types[ $custom_post_type->name ] = $custom_post_type->label;
			$disabled[]                            = $custom_post_type->name;
		}
		$post_types = array_merge(
			[
				'post' => 'Post',
				'page' => 'Page',
			],
			$post_types
		);

		return [
			'id'       => 'post-status-post-types',
			'type'     => 'checkboxes',
			'choices'  => $post_types,
			'checked'  => [],
			'disabled' => $disabled,
			'name'     => 'poststatus-post-types[]',
			'label'    => __( 'Enable for these post types', 'admin-optimizer' ),
			'required' => false,
		];
	}

	/**
	 * Add user roles Settings field
	 *
	 * @return array
	 */
	private function add_user_roles_field() {
		$roles       = get_editable_roles();
		$role_fields = [];
		$disabled    = [];
		foreach ( $roles as $role => $details ) {
			if ( 'subscriber' === $role ) {
				continue;
			}
			$name                 = translate_user_role( $details['name'] );
			$role_fields[ $role ] = $name;
			$disabled[]           = $role;
		}
		return [
			'id'       => 'post-status-post-types',
			'type'     => 'checkboxes',
			'choices'  => $role_fields,
			'checked'  => [],
			'disabled' => $disabled,
			'name'     => 'poststatus-user-roles][]',
			'label'    => __( 'Enable for these user roles', 'admin-optimizer' ),
			'required' => false,
		];
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		$this->add_setting_fields();
		$this->add_pro_setting_fields();
		$message               = '';
		$error                 = false;
		$action                = $_REQUEST['action'] ?? ''; //phpcs:ignore
		$render_setting_fields = true;
		if ( in_array( $action, [ 'add', 'save-edit', 'edit', 'delete', 'bulk-delete' ], true ) ) {
			$response = $this->handle_post_data( $action );
			extract( $response ); //phpcs:ignore
		}
		if ( $render_setting_fields ) : ?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Admin Optimizer - Custom Post Statuses', 'admin-optimizer' ); ?></h1>
				<?php
				if ( ! empty( $message ) ) {
					$class = $error ? 'error' : 'updated';
					wp_admin_notice(
						$message,
						[
							'id'                 => 'message',
							'additional_classes' => [ $class ],
							'dismissible'        => true,
						]
					);
				}
				?>
				<div id="ajax-response"></div>
				<p><?php esc_html_e( 'You can add and manage custom post status here.', 'admin-optimizer' ); ?></p>
				<div id="col-container" class="wp-clearfix">
					<div id="col-left">
						<div class="form-wrap">
							<h2><?php esc_html_e( 'Add New Post Status', 'admin-optimizer' ); ?></h2>
							<form id="addpoststatus" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Status::MENU_SLUG ), null, '&' ); ?>">
								<?php wp_nonce_field( 'add-post-status', 'nonce' ); ?>
								<input type="hidden" name="action" value="add">
								<?php $this->output_setting_fields( $this->setting_fields ); ?>
								<div class="adminoptim-pro-options">
									<h2><?php esc_html_e( 'Pro Options', 'admin-optimizer' ); ?></h2>
									<p>
										<?php
										/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
										$upgrade_message = sprintf( __( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' );
										echo wp_kses( $upgrade_message, 'a' );
										?>
									</p>
									<?php $this->output_setting_fields( $this->pro_setting_fields ); ?>
								</div>
								<p class="submit">
									<?php submit_button( __( 'Add New Status', 'admin-optimizer' ), 'primary', 'submit', false ); ?>
									<span class="spinner"></span>
								</p>
							</form></div>
					</div>
					<div id="col-right">
						<form id="bulk-delete-poststatus" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=adminoptimizer-custom-post-status' ), null, '&' ); ?>">
							<?php
							$poststatus_list_table = new Post_Status_List_Table();
							$poststatus_list_table->prepare_items();
							$poststatus_list_table->display();
							?>
						</form>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Handle post data
	 *
	 * @param string $action  Action variable for handling of post data.
	 *
	 * @return true[]
	 */
	private function handle_post_data( $action ) {
		$response = [ 'render_setting_fields' => true ];
		switch ( $action ) {
			case 'add':
				check_admin_referer( 'add-post-status', 'nonce' );
				$post_status         = isset( $_REQUEST['poststatus'] ) ? wp_unslash( $_REQUEST['poststatus'] ) : []; //phpcs:ignore
				if ( empty( $post_status['name'] ) ) {
					$response['message'] = __( 'A name is required for this term.', 'admin-optimizer' );
					$response['error']   = true;
				} else {
					$new_post_status                = [];
					$new_post_status['slug']        = sanitize_title_with_dashes( ! empty( $post_status['slug'] ) ? $post_status['slug'] : $post_status['name'] );
					$new_post_status['description'] = ! empty( $post_status['description'] ) ? sanitize_textarea_field( $post_status['description'] ) : '';
					$post_status_arr                = wp_insert_term( $post_status['name'], Post_Status::TERM_KEY, $new_post_status );
					if ( $post_status_arr && ! is_wp_error( $post_status_arr ) ) {
						$response['message'] = __( 'Post Status added successfully.', 'admin-optimizer' );
					} else {
						if ( is_wp_error( $post_status_arr ) ) {
							$response['message'] = $post_status_arr->get_error_message();
						} else {
							$response['message'] = __( 'There was an error adding the post status.', 'admin-optimizer' );
						}
						$response['error'] = true;
					}
				}
				break;
			case 'delete':
				if ( ! isset( $_REQUEST['term_id'] ) ) {
					break;
				}

				$post_status_id = (int) $_REQUEST['term_id'];
				check_admin_referer( 'delete-post-status_' . $post_status_id, 'nonce' );

				if ( ! current_user_can( 'delete_term', $post_status_id ) ) {
					wp_die(
						'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
						'<p>' . esc_html( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
						403
					);
				}

				$result = wp_delete_term( $post_status_id, Post_Status::TERM_KEY );
				if ( $result ) {
					$response['message'] = __( 'Post Status deleted successfully.', 'admin-optimizer' );
				} else {
					$response['message'] = __( 'Post Status does not exist.', 'admin-optimizer' );
					$response['error']   = true;
				}
				break;
			case 'bulk-delete':
				check_admin_referer( 'bulk-custom_statuses' );

				$delete_poststatus = $_REQUEST['delete_poststatus'] ? array_map( 'absint', wp_unslash( $_REQUEST['delete_poststatus'] ) ) : []; //phpcs:ignore
				if ( empty( $delete_poststatus ) ) {
					break;
				}
				$has_error = false;
				foreach ( $delete_poststatus as $post_status_id ) {
					if ( ! current_user_can( 'delete_term', $post_status_id ) ) {
						wp_die(
							'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
							'<p>' . esc_html( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
							403
						);
					}

					$result = wp_delete_term( $post_status_id, Post_Status::TERM_KEY );
					if ( ! $result || is_wp_error( $result ) ) {
						$response['message'] = __( 'Post Status does not exist.', 'admin-optimizer' );
						$response['error']   = true;
						$has_error           = true;
						break;
					}
				}
				if ( ! $has_error ) {
					$response['message'] = __( 'Post Status deleted successfully.', 'admin-optimizer' );
				}
				break;
			case 'edit':
				if ( ! isset( $_REQUEST['term_id'] ) ) {
					break;
				}

				$post_status_id = (int) $_REQUEST['term_id'];
				check_admin_referer( 'edit-post-status_' . $post_status_id, 'nonce' );
				$this->render_edit_page( $post_status_id );
				$response['render_setting_fields'] = false;
				break;
			case 'save-edit':
				if ( ! isset( $_REQUEST['term_id'] ) ) {
					wp_die( esc_html( 'You attempted to save a post status with improper parameter. Please try again!' ) );
				}

				$post_status_id = (int) $_REQUEST['term_id'];
				check_admin_referer( 'save-edit-post-status_' . $post_status_id, 'nonce' );

				$post_status = $_REQUEST['poststatus'] ?? []; //phpcs:ignore
				if ( ! term_exists( $post_status_id, Post_Status::TERM_KEY ) ) {
					wp_die( esc_html( 'You attempted to edit a post status that does not exist. Perhaps it was deleted?' ) );
				}

				$name = ! empty( $post_status['name'] ) ? sanitize_text_field( $post_status['name'] ) : '';
				if ( empty( $name ) ) {
					$response['message'] = __( 'Post Status name cannot be empty.', 'admin-optimizer' );
					$response['error']   = true;
					$this->render_edit_page( $post_status_id, $response['message'], $response['error'] );
					$response['render_setting_fields'] = false;
					break;
				}
				$slug        = sanitize_title_with_dashes( $post_status['slug'] ?? $post_status['name'] );
				$description = ! empty( $post_status['description'] ) ? sanitize_textarea_field( $post_status['description'] ) : '';
				wp_update_term(
					$post_status_id,
					Post_Status::TERM_KEY,
					[
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					]
				);
				$response['message'] = __( 'Post status updated successfully', 'admin-optimizer' );
				break;
		}
		return $response;
	}

	/**
	 * Render Settings fields
	 *
	 * @param array $setting_fields  A list of settings fields to output.
	 *
	 * @return void
	 */
	private function output_setting_fields( $setting_fields ) {
		if ( ! empty( $setting_fields ) ) {
			foreach ( $setting_fields as $field ) {
				$required_class = $field['required'] ? ' form-required' : '';
				switch ( $field['type'] ) {
					case 'text':
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<input name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $field['value'] ); ?>" size="40" aria-required="
							<?php
							echo ( $field['required'] ? 'true' : 'false' );
							?>
							" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description" />
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'textarea':
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<textarea name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" rows="5" cols="40" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['value'] ); ?></textarea>
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'checkboxes':
						$disabled_fields = ! empty( $field['disabled'] ) && is_array( $field['disabled'] ) ? $field['disabled'] : [];
						?>
						<table class="form-table" role="presentation">
							<tbody>
							<tr>
								<th scope="row"><?php echo esc_html( $field['label'] ); ?></th>
								<td>
									<?php
									foreach ( $field['choices'] as $name => $label ) :
										$checked  = '';
										$disabled = '';
										if ( in_array( $name, $field['checked'], true ) ) {
											$checked = 'checked';
										}
										if ( in_array( $name, $disabled_fields, true ) ) {
											$disabled = ' disabled';
										}
										?>
										<label for="<?php echo esc_attr( $field['id'] ) . '-' . esc_attr( $name ); ?>">
											<input id="<?php echo esc_attr( $field['id'] ) . '-' . esc_attr( $name ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" type="checkbox" value="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $checked ); ?><?php echo esc_attr( $disabled ); ?>><?php echo esc_html( $label ); ?>
										</label><br/>
									<?php endforeach; ?>
								</td>
							</tr>
							</tbody>
						</table>
						<?php
						break;
				}
			}
		}
	}

	/**
	 * Render Edit page
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $message  Message to display.
	 * @param bool   $error  Handle error.
	 *
	 * @return void
	 */
	private function render_edit_page( $term_id, $message = '', $error = false ) {
		?>
		<div class="wrap">
			<?php
			$post_status = get_term( $term_id, Post_Status::TERM_KEY );
			if ( ! $post_status instanceof \WP_Term ) {
				wp_die( esc_html( 'You attempted to edit a post status that does not exist. Perhaps it was deleted?' ) );
			}
			if ( ! current_user_can( 'edit_term', $post_status->term_id ) ) {
				wp_die(
					'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
					'<p>' . esc_html( 'Sorry, you are not allowed to edit this item.' ) . '</p>',
					403
				);
			}
			?>
			<h1><?php esc_html_e( 'Admin Optimizer - Edit Post Status', 'admin-optimizer' ); ?></h1>
			<?php
			if ( ! empty( $message ) ) {
				$class = $error ? 'error' : 'updated';
				wp_admin_notice(
					$message,
					[
						'id'                 => 'message',
						'additional_classes' => array( $class ),
						'dismissible'        => true,
					]
				);
			}
			?>
			<div id="ajax-response"></div>
			<form id="edittag" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Status::MENU_SLUG ), null, '&' ); ?>">
				<input type="hidden" name="action" value="save-edit" >
				<input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>" >
				<?php wp_nonce_field( 'save-edit-post-status_' . $term_id, 'nonce' ); ?>
				<table class="form-table" role="presentation">
					<tbody><tr class="form-field form-required term-name-wrap">
						<th scope="row">
							<label for="name"><?php esc_html_e( 'Name', 'admin-optimizer' ); ?></label></th>
						<td><input name="poststatus[name]" id="name" type="text" value="<?php echo esc_attr( $post_status->name ); ?>" size="40" aria-required="true" aria-describedby="name-description">
							<p class="description"><?php esc_html_e( 'The name is used to identify the status', 'admin-optimizer' ); ?></p>
						</td>
					</tr>
					<tr class="form-field term-slug-wrap">
						<th scope="row"><label for="slug"><?php esc_html_e( 'Slug', 'admin-optimizer' ); ?></label></th>
						<td><input name="poststatus[slug]" id="slug" type="text" value="<?php echo esc_attr( $post_status->slug ); ?>" size="40" aria-describedby="slug-description">
							<p class="description" id="slug-description"><?php esc_html_e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'admin-optimizer' ); ?></p></td>
					</tr>
					<tr class="form-field term-description-wrap">
						<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'admin-optimizer' ); ?></label></th>
						<td><div id="wp-description-wrap" class="wp-core-ui wp-editor-wrap"><textarea class="wp-editor-area" rows="20" autocomplete="off" cols="40" name="poststatus[description]" id="description"><?php echo esc_html( $post_status->description ); ?></textarea>
							</div><p class="description" id="description-description"><?php esc_html_e( 'The description is primarily for administrative use, to give you some context on what the custom status is to be used for.', 'admin-optimizer' ); ?></p></td>
					</tr>
					</tbody>
				</table>
				<p><?php submit_button( 'Save Post Status', 'primary', 'submit', false ); ?> <a class="cancel-settings-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Status::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Cancel', 'admin-optimizer' ); ?></a></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts on the Settings page
	 *
	 * @param string $hook_suffix  The hook suffix to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Post_Status::MENU_SLUG ) ) {
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );
		}
	}
}