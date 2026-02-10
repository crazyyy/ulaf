<?php
/**
 * Media Library Access Control module.
 *
 * @package ASENHA
 * @since 8.2.4
 */

namespace ASENHA\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Media Library/media modal attachment listings to the current user's uploads.
 *
 * Notes:
 * - Restricts only attachment *listing* queries in wp-admin (Media Library list view and media modal/grid queries).
 * - Does not affect frontend rendering, and does not block direct access to attachment URLs.
 */
class Media_Files_Visibility_Control {

	/**
	 * Filter attachment query args for media modal / media grid.
	 *
	 * @param array $query_args WP_Query args for attachments.
	 * @return array
	 */
	public function filter_attachments_grid( $query_args ) {
		if ( ! is_admin() ) {
			return $query_args;
		}

		if ( ! $this->should_restrict_current_user() ) {
			return $query_args;
		}

		$query_args['author'] = get_current_user_id();

		return $query_args;
	}

	/**
	 * Restrict attachment list view in Media Library (`upload.php` list mode).
	 *
	 * @param \WP_Query $query Query instance.
	 * @return void
	 */
	public function filter_attachments_list( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! $query instanceof \WP_Query ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		// Only affect Media Library list screen.
		global $pagenow;
		if ( 'upload.php' !== $pagenow ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'attachment' !== $post_type && ( ! is_array( $post_type ) || ! in_array( 'attachment', $post_type, true ) ) ) {
			return;
		}

		if ( ! $this->should_restrict_current_user() ) {
			return;
		}

		$query->set( 'author', get_current_user_id() );
	}

	/**
	 * Determine whether the current user should be restricted to seeing only their own uploads.
	 *
	 * @return bool
	 */
	private function should_restrict_current_user() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Administrators (and multisite super admins) can always see all media.
		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( is_multisite() && is_super_admin() ) {
			return false;
		}

		// Only apply when the user can upload files (i.e., they are a relevant role for media).
		if ( ! current_user_can( 'upload_files' ) ) {
			return false;
		}

		// Free behavior: restrict all non-admin upload-capable users.
		$should_restrict = true;

		// Pro behavior: restrict only selected roles (defaults to all eligible roles).
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$options = get_option( ASENHA_SLUG_U, array() );

			$selected_roles = array();
			if ( isset( $options['media_files_visibility_control_for_roles'] ) ) {
				$selected_roles = (array) $options['media_files_visibility_control_for_roles'];
			}

			$selected_roles = array_filter( array_map( 'sanitize_key', $selected_roles ) );

			if ( empty( $selected_roles ) ) {
				// Default: all eligible (non-admin) roles that can upload files are restricted.
				$selected_roles = $this->get_upload_capable_non_admin_roles__premium_only();
			}

			$current_user = wp_get_current_user();
			$user_roles   = is_object( $current_user ) ? (array) $current_user->roles : array();

			$should_restrict = ! empty( array_intersect( $user_roles, $selected_roles ) );
		}

		return (bool) $should_restrict;
	}

	/**
	 * Get non-administrator roles that have the `upload_files` capability.
	 *
	 * @return string[] Array of role slugs.
	 */
	private function get_upload_capable_non_admin_roles__premium_only() {
		$wp_roles = wp_roles();
		if ( empty( $wp_roles ) || empty( $wp_roles->roles ) || ! is_array( $wp_roles->roles ) ) {
			return array();
		}

		$eligible = array();
		foreach ( $wp_roles->roles as $role_slug => $role_data ) {
			if ( 'administrator' === $role_slug ) {
				continue;
			}

			$role_object = get_role( $role_slug );
			if ( empty( $role_object ) ) {
				continue;
			}

			if ( ! $role_object->has_cap( 'upload_files' ) ) {
				continue;
			}

			$eligible[] = $role_slug;
		}

		return $eligible;
	}
}

