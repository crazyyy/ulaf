<?php
/**
 * Snippets admin view/controller.
 *
 * @package advanced-analytics
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Entities\Snippet_Entity;
use ADVAN\Helpers\Snippet_Condition_Evaluator;
use ADVAN\Helpers\Snippets_Sandbox;
use ADVAN\Lists\Snippets_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Snippets_View' ) ) {
	/**
	 * Handles render + actions for snippets module.
	 */
	class Snippets_View {

		/**
		 * Render the snippets admin page or form.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function render_page(): void {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
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

			$action = isset( $_GET['action'] ) ? \sanitize_key( \wp_unslash( $_GET['action'] ) ) : '';

			self::maybe_render_notices();

			if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
				self::render_form( $action );
				return;
			}

			$list = new Snippets_List();
			$list->prepare_items();

			$create_url = \add_query_arg(
				array(
					'page'   => Snippets_List::MENU_SLUG,
					'action' => 'add',
				),
				self::get_base_admin_url()
			);
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'Code Snippets', '0-day-analytics' ); ?></h1>
				<a href="<?php echo \esc_url( $create_url ); ?>" class="page-title-action"><?php \esc_html_e( 'Add New', '0-day-analytics' ); ?></a>
				<hr class="wp-header-end">

				<h2 class='screen-reader-text'><?php \esc_html_e( 'Filter snippet list', '0-day-analytics' ); ?></h2>
				<?php $list->views(); ?>

				<form method="get">
					<input type="hidden" name="page" value="<?php echo \esc_attr( Snippets_List::MENU_SLUG ); ?>" />
					<?php $list->search_box( \__( 'Search snippets', '0-day-analytics' ), 'advan-snippets-search' ); ?>
					<?php $list->display(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Persist snippet add/edit.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_save(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_POST['snippet_id'] ) ? (int) $_POST['snippet_id'] : 0;
			$name       = isset( $_POST['snippet_name'] ) ? \sanitize_text_field( \wp_unslash( $_POST['snippet_name'] ) ) : '';
			$type       = isset( $_POST['snippet_type'] ) ? \sanitize_key( \wp_unslash( $_POST['snippet_type'] ) ) : 'php';
			$status     = isset( $_POST['snippet_status'] ) ? Snippet_Entity::STATUS_ENABLED : Snippet_Entity::STATUS_DISABLED;
			$tags       = isset( $_POST['snippet_tags'] ) ? Snippet_Entity::sanitize_tags( $_POST['snippet_tags'] ) : '';
			$code       = isset( $_POST['snippet_code'] ) ? \wp_unslash( $_POST['snippet_code'] ) : '';
			$scope      = isset( $_POST['snippet_scope'] ) ? \sanitize_key( \wp_unslash( $_POST['snippet_scope'] ) ) : Snippet_Entity::SCOPE_EVERYWHERE;
			$scopes     = array_keys( Snippet_Entity::get_execution_scopes() );
			if ( ! in_array( $scope, $scopes, true ) ) {
				$scope = Snippet_Entity::SCOPE_EVERYWHERE;
			}

			$hook_raw = isset( $_POST['snippet_hook'] ) ? (string) \wp_unslash( $_POST['snippet_hook'] ) : '';
			$hook     = Snippet_Entity::sanitize_hook_name( $hook_raw );

			$priority = isset( $_POST['snippet_priority'] ) ? (int) $_POST['snippet_priority'] : 10;
			if ( $priority <= 0 ) {
				$priority = 10;
			}

			$shortcode_raw = isset( $_POST['snippet_shortcode'] ) ? (string) \wp_unslash( $_POST['snippet_shortcode'] ) : '';
			$shortcode_tag = Snippet_Entity::sanitize_shortcode_tag( $shortcode_raw );
			if ( '' !== $shortcode_tag ) {
				$shortcode_owner = Snippet_Entity::get_by_shortcode( $shortcode_tag );
				if ( ! empty( $shortcode_owner ) && (int) ( $shortcode_owner['id'] ?? 0 ) !== $snippet_id ) {
					self::redirect_with_notice( 'error', \__( 'Shortcode already belongs to another snippet.', '0-day-analytics' ) );
				}
			}

			$conditions = isset( $_POST['snippet_conditions'] ) ? trim( (string) \wp_kses_post( \wp_unslash( $_POST['snippet_conditions'] ) ) ) : '';
			if ( '' !== $conditions && ! Snippet_Condition_Evaluator::is_valid( $conditions ) ) {
				self::redirect_with_notice( 'error', \__( 'Unable to parse run conditions. Check the syntax and try again.', '0-day-analytics' ) );
			}

			if ( '' === $name || '' === trim( $code ) ) {
				self::redirect_with_notice( 'error', \__( 'Name and code are required.', '0-day-analytics' ) );
			}

			$supported = array_keys( Snippet_Entity::get_supported_types() );
			if ( ! in_array( $type, $supported, true ) ) {
				$type = 'php';
			}

			$existing = array();
			if ( $snippet_id > 0 ) {
				$existing = Snippet_Entity::get_snippet( $snippet_id );
				if ( empty( $existing ) ) {
					self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
				}
				self::enforce_blog_scope( $existing );
			}

			$now  = gmdate( 'Y-m-d H:i:s' );
			$slug = Snippet_Entity::build_unique_slug( $name, $snippet_id ?: null );
			$data = array(
				'id'               => $snippet_id,
				'blog_id'          => \get_current_blog_id(),
				'name'             => $name,
				'slug'             => $slug,
				'type'             => $type,
				'status'           => $status,
				'code'             => $code,
				'tags'             => $tags,
				'execution_scope'  => $scope,
				'execution_hook'   => $hook,
				'hook_priority'    => $priority,
				'shortcode_tag'    => ( '' === $shortcode_tag ) ? null : $shortcode_tag,
				'run_conditions'   => $conditions,
				'updated_at'       => $now,
				'created_at'       => $existing['created_at'] ?? $now,
				'last_run_status'  => $existing['last_run_status'] ?? 'never',
				'last_run_message' => $existing['last_run_message'] ?? '',
				'last_run_at'      => $existing['last_run_at'] ?? null,
			);

			if ( 0 === $snippet_id ) {
				unset( $data['id'] );
			}

			Snippet_Entity::insert( $data );

			self::redirect_with_notice(
				$snippet_id > 0 ? 'updated' : 'created',
				$snippet_id > 0 ? \__( 'Snippet updated.', '0-day-analytics' ) : \__( 'Snippet created.', '0-day-analytics' )
			);
		}

		/**
		 * Handle single delete action.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_delete(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_REQUEST['snippet'] ) ? (int) $_REQUEST['snippet'] : 0;
			if ( $snippet_id <= 0 ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}

			$snippet = Snippet_Entity::get_snippet( $snippet_id );
			if ( empty( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}
			self::enforce_blog_scope( $snippet );

			if ( ! Snippet_Entity::is_trashed( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Move the snippet to trash before deleting permanently.', '0-day-analytics' ) );
			}

			Snippet_Entity::delete_by_id( $snippet_id );

			self::redirect_with_notice( 'deleted', \__( 'Snippet deleted permanently.', '0-day-analytics' ) );
		}

		/**
		 * Move snippet to trash.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_trash(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_REQUEST['snippet'] ) ? (int) $_REQUEST['snippet'] : 0;
			if ( $snippet_id <= 0 ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}

			$snippet = Snippet_Entity::get_snippet( $snippet_id );
			if ( empty( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}
			self::enforce_blog_scope( $snippet );

			if ( Snippet_Entity::is_trashed( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet is already in trash.', '0-day-analytics' ) );
			}

			Snippet_Entity::trash_by_id( $snippet_id );
			self::redirect_with_notice( 'trashed', \__( 'Snippet moved to trash.', '0-day-analytics' ) );
		}

		/**
		 * Restore trashed snippet.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_restore(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_REQUEST['snippet'] ) ? (int) $_REQUEST['snippet'] : 0;
			if ( $snippet_id <= 0 ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}

			$snippet = Snippet_Entity::get_snippet( $snippet_id );
			if ( empty( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}
			self::enforce_blog_scope( $snippet );

			if ( ! Snippet_Entity::is_trashed( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Only trashed snippets can be restored.', '0-day-analytics' ) );
			}

			Snippet_Entity::restore_by_id( $snippet_id );
			self::redirect_with_notice( 'restored', \__( 'Snippet restored.', '0-day-analytics' ) );
		}

		/**
		 * Duplicate an existing snippet record.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_clone(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_REQUEST['snippet'] ) ? (int) $_REQUEST['snippet'] : 0;
			if ( $snippet_id <= 0 ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}

			$snippet = Snippet_Entity::get_snippet( $snippet_id );
			if ( empty( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}
			self::enforce_blog_scope( $snippet );

			$new_id = Snippet_Entity::duplicate( $snippet_id );
			if ( null === $new_id ) {
				self::redirect_with_notice( 'error', \__( 'Could not duplicate snippet.', '0-day-analytics' ) );
			}

			self::redirect_with_notice( 'cloned', \__( 'Snippet duplicated and saved as draft.', '0-day-analytics' ) );
		}

		/**
		 * Execute snippet in sandbox.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function handle_execute(): void {
			self::enforce_capability();
			\check_admin_referer( Snippets_List::NONCE_NAME );

			$snippet_id = isset( $_REQUEST['snippet'] ) ? (int) $_REQUEST['snippet'] : 0;
			$snippet    = ( $snippet_id > 0 ) ? Snippet_Entity::get_snippet( $snippet_id ) : array();
			if ( empty( $snippet ) ) {
				self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
			}
			self::enforce_blog_scope( $snippet );

			if ( Snippet_Entity::STATUS_ENABLED !== (int) $snippet['status'] ) {
				self::redirect_with_notice( 'disabled', \__( 'Enable the snippet before executing it.', '0-day-analytics' ) );
			}

			if ( 'php' !== $snippet['type'] ) {
				self::redirect_with_notice( 'error', \__( 'Only PHP snippets can be executed from here.', '0-day-analytics' ) );
			}

			$result = Snippets_Sandbox::execute(
				(string) $snippet['code'],
				array(
					'snippet'    => array(
						'id'    => (int) $snippet['id'],
						'name'  => $snippet['name'],
						'scope' => $snippet['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE,
					),
					'conditions' => $snippet['run_conditions'] ?? '',
					'trigger'    => 'manual',
				)
			);

			$message = $result['message'] ?: $result['result_dump'];
			Snippet_Entity::store_execution_result( (int) $snippet['id'], $result['status'], (string) $message );

			$token = \wp_generate_password( 12, false, false );
			\set_transient(
				'advana_snippet_exec_' . $token,
				array_merge(
					$result,
					array(
						'snippet' => array(
							'id'   => (int) $snippet['id'],
							'name' => $snippet['name'],
						),
					)
				),
				5 * MINUTE_IN_SECONDS
			);

			self::redirect_with_notice( 'executed', \__( 'Snippet executed.', '0-day-analytics' ), array( 'snippet_token' => $token ) );
		}

		/**
		 * Render add/edit form.
		 *
		 * @param string $action - 'add' or 'edit'.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function render_form( string $action ): void {
			$is_edit    = ( 'edit' === $action );
			$core_title = $is_edit ? \__( 'Edit Snippet', '0-day-analytics' ) : \__( 'Add Snippet', '0-day-analytics' );

			$snippet_id = isset( $_GET['snippet'] ) ? (int) $_GET['snippet'] : 0;
			$snippet    = array();
			if ( $is_edit ) {
				$snippet = ( $snippet_id > 0 ) ? Snippet_Entity::get_snippet( $snippet_id ) : array();
				if ( empty( $snippet ) ) {
					self::redirect_with_notice( 'error', \__( 'Snippet not found.', '0-day-analytics' ) );
				}
				self::enforce_blog_scope( $snippet );
			}

			$types           = Snippet_Entity::get_supported_types();
			$scopes          = Snippet_Entity::get_execution_scopes();
			$status_value    = (int) ( $snippet['status'] ?? 1 );
			$status_label    = $status_value ? \__( 'Enabled', '0-day-analytics' ) : \__( 'Disabled', '0-day-analytics' );
			$status_css      = $status_value ? 'is-enabled' : 'is-disabled';
			$slug_display    = ! empty( $snippet['slug'] ) ? $snippet['slug'] : \__( 'Will be generated on save', '0-day-analytics' );
			$last_run_status = ! empty( $snippet['last_run_status'] ) ? $snippet['last_run_status'] : \__( 'Never executed', '0-day-analytics' );
			$last_run_time   = self::format_relative_time( $snippet['last_run_at'] ?? null );
			$created_time    = self::format_datetime( $snippet['created_at'] ?? null );
			$updated_time    = self::format_datetime( $snippet['updated_at'] ?? null );
			$scope_value     = $snippet['execution_scope'] ?? Snippet_Entity::SCOPE_EVERYWHERE;
			if ( ! isset( $scopes[ $scope_value ] ) ) {
				$scope_value = Snippet_Entity::SCOPE_EVERYWHERE;
			}
			$hook_value     = $snippet['execution_hook'] ?? 'init';
			$priority_value = (int) ( $snippet['hook_priority'] ?? 10 );
			if ( $priority_value <= 0 ) {
				$priority_value = 10;
			}
			$shortcode_value    = $snippet['shortcode_tag'] ?? '';
			$conditions_value   = $snippet['run_conditions'] ?? '';
			$conditions_preview = '' !== $conditions_value ? Snippet_Condition_Evaluator::get_normalized_rules( $conditions_value ) : array();
			$execute_url        = '';
			if ( $is_edit && $snippet_id > 0 && 'php' === ( $snippet['type'] ?? 'php' ) ) {
				$execute_url = \wp_nonce_url(
					\add_query_arg(
						array(
							'action'  => Snippets_List::EXECUTE_ACTION,
							'snippet' => (int) $snippet_id,
						),
						\admin_url( 'admin-post.php' )
					),
					Snippets_List::NONCE_NAME
				);
			}

			// Build cancel URL preserving list state (paged, search, filters).
			$cancel_args = array();
			if ( isset( $_REQUEST['paged'] ) ) {
				$cancel_args['paged'] = (int) $_REQUEST['paged'];
			}
			if ( isset( $_REQUEST[ Snippets_List::SEARCH_INPUT ] ) ) {
				$search = \sanitize_text_field( \wp_unslash( $_REQUEST[ Snippets_List::SEARCH_INPUT ] ) );
				if ( '' !== $search ) {
					$cancel_args[ Snippets_List::SEARCH_INPUT ] = $search;
				}
			}
			if ( isset( $_REQUEST['snippet_status'] ) ) {
				$status = \sanitize_key( \wp_unslash( $_REQUEST['snippet_status'] ) );
				if ( '' !== $status ) {
					$cancel_args['snippet_status'] = $status;
				}
			}
			if ( isset( $_REQUEST['snippet_type'] ) ) {
				$type_f = \sanitize_key( \wp_unslash( $_REQUEST['snippet_type'] ) );
				if ( '' !== $type_f ) {
					$cancel_args['snippet_type'] = $type_f;
				}
			}

			$cancel_url = Snippets_List::get_admin_page_url( $cancel_args );
			?>
			<div class="wrap advan-snippet-wrap">
				<div class="advan-snippet-heading">
					<div>
						<p class="advan-snippet-subtitle"><?php echo \esc_html( $is_edit ? \__( 'Update your snippet and keep track of its history.', '0-day-analytics' ) : \__( 'Create a reusable automation snippet.', '0-day-analytics' ) ); ?></p>
						<h1 class="wp-heading-inline"><?php echo \esc_html( $core_title ); ?></h1>
					</div>
					<span class="advan-snippet-status-badge <?php echo \esc_attr( $status_css ); ?>"><?php echo \esc_html( $status_label ); ?></span>
				</div>
				<form id="advan-snippet-form" class="advan-snippet-form" method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo \esc_attr( Snippets_List::SAVE_ACTION ); ?>" />
					<input type="hidden" name="snippet_id" value="<?php echo \esc_attr( (int) ( $snippet['id'] ?? 0 ) ); ?>" />
					<?php \wp_nonce_field( Snippets_List::NONCE_NAME ); ?>
					<div class="advan-snippet-workspace" data-storage-key="advanSnippetSidebarState" data-sidebar-collapsed="false">
						<section class="advan-snippet-primary">
							<div class="advan-field">
								<label class="advan-field-label" for="snippet-name"><?php \esc_html_e( 'Name', '0-day-analytics' ); ?></label>
								<input name="snippet_name" id="snippet-name" type="text" class="regular-text" required value="<?php echo \esc_attr( $snippet['name'] ?? '' ); ?>" placeholder="<?php \esc_attr_e( 'My daily cleanup', '0-day-analytics' ); ?>" />
							</div>
							<div class="advan-field">
								<label class="advan-field-label" for="snippet-code"><?php \esc_html_e( 'Code', '0-day-analytics' ); ?></label>
								<textarea name="snippet_code" id="snippet-code" rows="18" class="large-text code" data-required="true"><?php echo isset( $snippet['code'] ) ? \esc_textarea( $snippet['code'] ) : ''; ?></textarea>
								<p class="description"><?php \esc_html_e( 'PHP snippets run inside a sandboxed closure.', '0-day-analytics' ); ?></p>
								<p class="advan-snippet-error" data-snippet-error role="alert" hidden></p>
							</div>
						</section>
						<div class="advan-snippet-divider" role="separator" aria-orientation="vertical" tabindex="-1"></div>
						<aside class="advan-snippet-sidebar" data-collapsed="false">
							<button type="button" class="advan-snippet-sidebar-toggle" aria-expanded="true" aria-controls="advan-snippet-sidebar-fields"><?php \esc_html_e( 'Collapse sidebar', '0-day-analytics' ); ?></button>
							<div id="advan-snippet-sidebar-fields" class="advan-snippet-sidebar-inner">
								<div class="advan-snippet-actions">
									<?php echo \submit_button( \__( 'Save Snippet', '0-day-analytics' ), 'button button-primary button-large', 'submit', false ); ?>
									<?php if ( $execute_url ) : ?>
										<a class="button button-secondary" href="<?php echo \esc_url( $execute_url ); ?>"><?php \esc_html_e( 'Execute snippet', '0-day-analytics' ); ?></a>
									<?php endif; ?>
									<a class="button button-link" href="<?php echo \esc_url( $cancel_url ); ?>"><?php \esc_html_e( 'Cancel', '0-day-analytics' ); ?></a>
								</div>

								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-type"><?php \esc_html_e( 'Execution type', '0-day-analytics' ); ?></label>
									<select name="snippet_type" id="snippet-type" class="widefat">
										<?php foreach ( $types as $key => $label ) : ?>
											<option value="<?php echo \esc_attr( $key ); ?>" <?php \selected( $snippet['type'] ?? 'php', $key ); ?>><?php echo \esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="advan-snippet-meta-group">
									<span class="advan-field-label"><?php \esc_html_e( 'Status', '0-day-analytics' ); ?></span>
									<label class="advan-toggle">
										<input type="checkbox" name="snippet_status" value="1" <?php \checked( $status_value, 1 ); ?> />
										<span><?php \esc_html_e( 'Enabled', '0-day-analytics' ); ?></span>
									</label>
									<p class="description"><?php \esc_html_e( 'Disabled snippets stay dormant until re-enabled.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-tags"><?php \esc_html_e( 'Tags', '0-day-analytics' ); ?></label>
									<input type="text" name="snippet_tags" id="snippet-tags" class="widefat" value="<?php echo \esc_attr( $snippet['tags'] ?? '' ); ?>" placeholder="wp,cleanup,utility" />
									<p class="description"><?php \esc_html_e( 'Comma separated helper labels.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-scope"><?php \esc_html_e( 'Execution scope', '0-day-analytics' ); ?></label>
									<select name="snippet_scope" id="snippet-scope" class="widefat">
										<?php foreach ( $scopes as $key => $label ) : ?>
											<option value="<?php echo \esc_attr( $key ); ?>" <?php \selected( $scope_value, $key ); ?>><?php echo \esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php \esc_html_e( 'Choose where this snippet runs automatically when enabled.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-hook"><?php \esc_html_e( 'Hook name', '0-day-analytics' ); ?></label>
									<input type="text" name="snippet_hook" id="snippet-hook" class="widefat" value="<?php echo \esc_attr( $hook_value ); ?>" placeholder="init" />
									<p class="description"><?php \esc_html_e( 'We attach the snippet to this WordPress action on every request.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-priority"><?php \esc_html_e( 'Hook priority', '0-day-analytics' ); ?></label>
									<input type="number" name="snippet_priority" id="snippet-priority" class="widefat" min="1" step="1" value="<?php echo \esc_attr( $priority_value ); ?>" />
									<p class="description"><?php \esc_html_e( 'Lower numbers run earlier. Default is 10.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-shortcode"><?php \esc_html_e( 'Shortcode tag', '0-day-analytics' ); ?></label>
									<input type="text" name="snippet_shortcode" id="snippet-shortcode" class="widefat" value="<?php echo \esc_attr( $shortcode_value ); ?>" placeholder="my_snippet" />
									<p class="description"><?php \esc_html_e( 'Register [tag] for this snippet. Shortcodes must be unique per site; leave blank to skip registration.', '0-day-analytics' ); ?></p>
								</div>
								<div class="advan-snippet-meta-group">
									<label class="advan-field-label" for="snippet-conditions"><?php \esc_html_e( 'Run conditions', '0-day-analytics' ); ?></label>
									<textarea name="snippet_conditions" id="snippet-conditions" class="widefat" rows="4" placeholder="<?php \esc_attr_e( 'user_role=editor, post_type=product', '0-day-analytics' ); ?>"><?php echo \esc_textarea( $conditions_value ); ?></textarea>
									<p class="description"><?php \esc_html_e( 'Comma or newline separated rules such as user_role=editor or request=frontend. JSON objects are also accepted.', '0-day-analytics' ); ?></p>
									<details class="advan-conditions-help">
										<summary><?php \esc_html_e( 'View supported rule keys', '0-day-analytics' ); ?></summary>
										<ul>
											<li><?php \esc_html_e( 'role / user_role = administrator|editor', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'cap / user_capability = manage_options', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'request / context = admin|frontend|ajax|cron|rest|cli', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'trigger = hook|shortcode', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'hook = init or init_* (supports * wildcard)', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'shortcode = my_snippet_tag', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'post_type = product', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'url_path = /shop/* (matches prefixes)', '0-day-analytics' ); ?></li>
											<li><?php \esc_html_e( 'request_param / query_var = key=value', '0-day-analytics' ); ?></li>
										</ul>
									</details>
									<?php if ( ! empty( $conditions_preview ) ) : ?>
										<div class="advan-conditions-preview">
											<strong><?php \esc_html_e( 'Parsed rules:', '0-day-analytics' ); ?></strong>
											<ul>
												<?php foreach ( $conditions_preview as $rule ) : ?>
													<?php
														$field    = isset( $rule['field'] ) ? (string) $rule['field'] : '';
														$operator = ( 'neq' === ( $rule['operator'] ?? 'eq' ) ) ? '!=' : '=';
														$values   = array();
													foreach ( (array) ( $rule['values'] ?? array() ) as $preview_value ) {
														$values[] = \sanitize_text_field( (string) $preview_value );
													}
													?>
													<li>
														<code><?php echo \esc_html( trim( $field . ' ' . $operator . ' ' . implode( ' | ', $values ) ) ); ?></code>
													</li>
												<?php endforeach; ?>
											</ul>
										</div>
									<?php endif; ?>
								</div>
								<div class="advan-snippet-meta-overview">
									<h2><?php \esc_html_e( 'Snapshot', '0-day-analytics' ); ?></h2>
									<ul>
										<li><span><?php \esc_html_e( 'Slug', '0-day-analytics' ); ?></span><code><?php echo \esc_html( $slug_display ); ?></code></li>
										<li><span><?php \esc_html_e( 'Scope', '0-day-analytics' ); ?></span><?php echo \esc_html( $scopes[ $scope_value ] ?? '' ); ?></li>
										<li><span><?php \esc_html_e( 'Last run', '0-day-analytics' ); ?></span><strong><?php echo \esc_html( $last_run_time ); ?></strong></li>
										<li><span><?php \esc_html_e( 'Last result', '0-day-analytics' ); ?></span><?php echo \esc_html( $last_run_status ); ?></li>
										<li><span><?php \esc_html_e( 'Created', '0-day-analytics' ); ?></span><?php echo \esc_html( $created_time ); ?></li>
										<li><span><?php \esc_html_e( 'Updated', '0-day-analytics' ); ?></span><?php echo \esc_html( $updated_time ); ?></li>
										<li><span><?php \esc_html_e( 'Shortcode', '0-day-analytics' ); ?></span><?php echo '' !== $shortcode_value ? '<code>[' . \esc_html( $shortcode_value ) . ']</code>' : '&mdash;'; ?></li>
									</ul>
								</div>
							</div>
						</aside>
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Format relative time helper.
		 *
		 * @param string|null $timestamp - GMT timestamp.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function format_relative_time( ?string $timestamp ): string {
			if ( empty( $timestamp ) ) {
				return \__( 'Never', '0-day-analytics' );
			}

			$time = strtotime( $timestamp . ' UTC' );
			if ( ! $time ) {
				return $timestamp;
			}

			$diff = \human_time_diff( $time, \time() );
			return sprintf( \__( '%s ago', '0-day-analytics' ), $diff );
		}

		/**
		 * Format stored GMT timestamp into local time string.
		 *
		 * @param string|null $timestamp - GMT timestamp.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function format_datetime( ?string $timestamp ): string {
			if ( empty( $timestamp ) ) {
				return \__( 'Not recorded yet', '0-day-analytics' );
			}

			$format = \get_option( 'date_format' ) . ' ' . \get_option( 'time_format' );
			return \get_date_from_gmt( $timestamp, $format );
		}

		/**
		 * Output admin notices based on query args and execution tokens.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function maybe_render_notices(): void {
			if ( isset( $_GET['snippet_notice'] ) ) {
				$notice  = \sanitize_key( \wp_unslash( $_GET['snippet_notice'] ) );
				$message = '';

				if ( isset( $_GET['_msg'] ) ) {
					$message = \sanitize_text_field( \rawurldecode( (string) \wp_unslash( $_GET['_msg'] ) ) );
				} else {
					$message = self::get_default_notice_message( $notice );
				}

				if ( '' !== $message ) {
					self::print_notice( $notice, $message );
				}
			}

			if ( isset( $_GET['snippet_token'] ) ) {
				$token = \sanitize_key( \wp_unslash( $_GET['snippet_token'] ) );
				$data  = \get_transient( 'advana_snippet_exec_' . $token );
				if ( $data ) {
					\delete_transient( 'advana_snippet_exec_' . $token );
					self::print_execution_notice( $data );
				}
			}
		}

		/**
		 * Print standard admin notice.
		 *
		 * @param string $type - Notice type: 'error', 'disabled', 'success'.
		 * @param string $message - Notice message.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function print_notice( string $type, string $message ): void {
			switch ( $type ) {
				case 'error':
					$css = 'notice notice-error';
					break;
				case 'disabled':
					$css = 'notice notice-warning';
					break;
				default:
					$css = 'notice notice-success';
					break;
			}
			printf( '<div class="%1$s"><p>%2$s</p></div>', \esc_attr( $css ), \esc_html( $message ) );
		}

		/**
		 * Rich notice for execution results.
		 *
		 * @param array $data - Execution result data.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function print_execution_notice( array $data ): void {
			static $share_script_output = false;
			$status_class               = ( 'success' === $data['status'] ) ? 'notice-success' : 'notice-error';
			?>
			<style>
				.advan-snippet-exec-notice { position: relative; }
				.advan-snippet-sharebar { position: absolute; top: 8px; right: 8px; display: flex; gap: 8px; align-items: center; }
				.advan-snippet-sharebar .dashicons { cursor: pointer; }
				.advan-snippet-toast { position: absolute; bottom: 8px; right: 8px; background: #1e1e2d; color: #fff; padding: 6px 10px; border-radius: 4px; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); opacity: 0.95; }
			</style>
			<div class="notice <?php echo \esc_attr( $status_class ); ?> advan-snippet-exec-notice">
				<div class="advan-snippet-sharebar" aria-label="<?php echo \esc_attr__( 'Snippet execution actions', '0-day-analytics' ); ?>">
					<span title="<?php echo \esc_attr__( 'Copy to clipboard', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard advan-snippet-copy" aria-hidden="true"></span>
					<span title="<?php echo \esc_attr__( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share advan-snippet-share" aria-hidden="true"></span>
				</div>
				<p><strong><?php echo \esc_html( sprintf( \__( 'Execution result (%s)', '0-day-analytics' ), $data['status'] ) ); ?></strong></p>
				<?php if ( ! empty( $data['message'] ) ) : ?>
					<p><?php echo \esc_html( $data['message'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $data['output'] ) ) : ?>
					<p><strong><?php \esc_html_e( 'Output:', '0-day-analytics' ); ?></strong></p>
					<textarea readonly class="large-text code advan-snippet-result" rows="6"><?php echo \esc_textarea( $data['output'] ); ?></textarea>
				<?php endif; ?>
				<?php if ( ! empty( $data['result_dump'] ) ) : ?>
					<p><strong><?php \esc_html_e( 'Result:', '0-day-analytics' ); ?></strong></p>
					<textarea readonly class="large-text code advan-snippet-result" rows="4"><?php echo \esc_textarea( $data['result_dump'] ); ?></textarea>
				<?php endif; ?>
			</div>
			<?php
			if ( ! $share_script_output ) :
				$share_script_output = true;
				?>
				<script>
					(function($){
						function showToast(container, message) {
							if ( ! container || ! message ) { return; }
							var toast = $('<div class="advan-snippet-toast" role="status" aria-live="polite"></div>').text(message);
							container.append(toast);
							setTimeout(function(){ toast.fadeOut(200, function(){ $(this).remove(); }); }, 2000);
						}

						function gatherSnippetText(container){
							var pieces = [];
							$(container).find('textarea.large-text.code').each(function(){
								var val = $(this).val();
								if ( val ) { pieces.push( val ); }
							});
							if ( ! pieces.length ) {
								var fallback = $(container).text();
								if ( fallback ) { pieces.push( fallback ); }
							}
							return pieces.join('\n\n');
						}

						$(document).on('click', '.advan-snippet-copy', function(e){
							e.preventDefault();
							var notice = $(this).closest('.advan-snippet-exec-notice');
							var text = gatherSnippetText(notice);
							if ( ! text ) { return; }
							if ( navigator.clipboard && navigator.clipboard.writeText ) {
								navigator.clipboard.writeText(text);
							} else {
								var tmp = $('<textarea>').css({ position: 'absolute', left: '-9999px', top: '0' }).text(text);
								$('body').append(tmp);
								tmp[0].select();
								document.execCommand('copy');
								tmp.remove();
							}
							showToast(notice, '<?php echo esc_js( __( 'Copied to clipboard', '0-day-analytics' ) ); ?>');
						});

						function shareSupported(){ return typeof navigator !== 'undefined' && !!navigator.share; }

						if ( ! shareSupported() ) {
							$('.advan-snippet-share').remove();
						} else {
							$(document).on('click', '.advan-snippet-share', function(e){
								e.preventDefault();
								var notice = $(this).closest('.advan-snippet-exec-notice');
								var text = gatherSnippetText(notice);
								if ( ! text ) { return; }
								var payload = { text: text + '\n\n' + <?php echo wp_json_encode( get_site_url() ); ?> };
								navigator.share(payload)
									.then(function(){ showToast(notice, '<?php echo esc_js( __( 'Shared', '0-day-analytics' ) ); ?>'); })
									.catch(function(){ showToast(notice, '<?php echo esc_js( __( 'Share failed', '0-day-analytics' ) ); ?>'); });
							});
						}
					})(jQuery);
				</script>
			<?php endif; ?>
			<?php
		}

		/**
		 * Ensure only administrators interact with controller endpoints.
		 */
		private static function enforce_capability(): void {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'Insufficient permissions.', '0-day-analytics' ) );
			}
		}

		/**
		 * Ensure the snippet belongs to the current site on multisite installs.
		 *
		 * @param array $snippet Snippet row.
		 */
		private static function enforce_blog_scope( array $snippet ): void {
			$blog_id = (int) ( $snippet['blog_id'] ?? 0 );
			if ( $blog_id && $blog_id !== (int) \get_current_blog_id() ) {
				self::redirect_with_notice( 'error', \__( 'Snippet does not belong to this site.', '0-day-analytics' ) );
			}
		}

		/**
		 * Helper for consistent redirects with notices.
		 *
		 * @param string $code - Notice code.
		 * @param string $message - Optional custom message.
		 * @param array  $extra - Extra query args.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		private static function redirect_with_notice( string $code, string $message = '', array $extra = array() ): void {
			$params = array(
				'page'           => Snippets_List::MENU_SLUG,
				'snippet_notice' => $code,
			);

			if ( '' !== $message ) {
				$params['_msg'] = \rawurlencode( $message );
			}

			$status = isset( $_REQUEST['snippet_status'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['snippet_status'] ) ) : '';
			if ( '' !== $status && ! isset( $extra['snippet_status'] ) ) {
				$params['snippet_status'] = $status;
			}

			$search = isset( $_REQUEST[ Snippets_List::SEARCH_INPUT ] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST[ Snippets_List::SEARCH_INPUT ] ) ) : '';
			if ( '' !== $search && ! isset( $extra[ Snippets_List::SEARCH_INPUT ] ) ) {
				$params[ Snippets_List::SEARCH_INPUT ] = $search;
			}

			if ( ! empty( $extra ) ) {
				$params = array_merge( $params, $extra );
			}

			$location = \add_query_arg( $params, self::get_base_admin_url() );
			\wp_safe_redirect( $location );
			exit;
		}

		/**
		 * Provide fallback messages for known notice codes.
		 *
		 * @param string $code - Notice code.
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_default_notice_message( string $code ): string {
			$map = array(
				'created'  => \__( 'Snippet created.', '0-day-analytics' ),
				'updated'  => \__( 'Snippet updated.', '0-day-analytics' ),
				'deleted'  => \__( 'Snippet deleted permanently.', '0-day-analytics' ),
				'trashed'  => \__( 'Snippet moved to trash.', '0-day-analytics' ),
				'restored' => \__( 'Snippet restored.', '0-day-analytics' ),
				'executed' => \__( 'Snippet executed.', '0-day-analytics' ),
				'cloned'   => \__( 'Snippet duplicated.', '0-day-analytics' ),
			);

			return $map[ $code ] ?? '';
		}

		/**
		 * Base admin url helper (respects network admin context).
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		private static function get_base_admin_url(): string {
			return \is_network_admin() ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' );
		}
	}
}
