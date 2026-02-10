<?php
/**
 * Redirect Manager AJAX Handlers
 *
 * Handles AJAX requests for duplicate checking, autocomplete, and group management
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for AJAX handlers
 */
class ASENHA_Redirect_Manager_Ajax {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		// Duplicate checking
		add_action( 'wp_ajax_asenha_check_duplicate_redirect', array( $this, 'check_duplicate_redirect' ) );
		
		// Autocomplete for redirect to
		add_action( 'wp_ajax_asenha_autocomplete_redirect_to', array( $this, 'autocomplete_redirect_to' ) );
		
		// Group management
		add_action( 'wp_ajax_asenha_add_redirect_group', array( $this, 'add_redirect_group' ) );
		add_action( 'wp_ajax_asenha_edit_redirect_group', array( $this, 'edit_redirect_group' ) );
		add_action( 'wp_ajax_asenha_delete_redirect_group', array( $this, 'delete_redirect_group' ) );
	}

	/**
	 * Check for duplicate redirect
	 *
	 * @since 8.1.0
	 */
	public function check_duplicate_redirect() {
		// Verify nonce
		check_ajax_referer( 'asenha_redirect_manager', 'nonce' );
		
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ) ) );
		}
		
		// Get parameters
		$redirect_from = isset( $_POST['redirect_from'] ) ? sanitize_text_field( $_POST['redirect_from'] ) : '';
		$current_post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		
		if ( empty( $redirect_from ) ) {
			wp_send_json_success( array( 'exists' => false ) );
		}
		
		// Query for existing redirects with same from path
		global $wpdb;
		
		$query = $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} 
			WHERE meta_key = '_redirect_from' 
			AND meta_value = %s 
			AND post_id != %d
			LIMIT 1",
			$redirect_from,
			$current_post_id
		);
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$existing_post_id = $wpdb->get_var( $query );
		
		if ( $existing_post_id ) {
			// Check if the post is published
			$post_status = get_post_status( $existing_post_id );
			
			if ( $post_status === 'publish' ) {
				$edit_link = get_edit_post_link( $existing_post_id );
				
				wp_send_json_success( array(
					'exists'    => true,
					'edit_link' => $edit_link,
					'post_id'   => $existing_post_id,
				) );
			}
		}
		
		wp_send_json_success( array( 'exists' => false ) );
	}

	/**
	 * Autocomplete for redirect to field
	 *
	 * @since 8.1.0
	 */
	public function autocomplete_redirect_to() {
		// Verify nonce
		check_ajax_referer( 'asenha_redirect_manager', 'nonce' );
		
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ) ) );
		}
		
		// Get search term
		$search_term = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
		
		if ( empty( $search_term ) ) {
			wp_send_json_success( array() );
		}
		
		// Get all public post types except asenha_redirect
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		unset( $post_types['asenha_redirect'] );
		
		// Query posts
		$args = array(
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			's'              => $search_term,
			'posts_per_page' => 10,
			'orderby'        => 'relevance',
		);
		
		$query = new WP_Query( $args );
		$results = array();
		
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				
				$post_id = get_the_ID();
				$post_type_object = get_post_type_object( get_post_type() );
				
				$results[] = array(
					'label' => get_the_title() . ' (' . $post_type_object->labels->singular_name . ')',
					'value' => get_permalink( $post_id ),
					'type'  => get_post_type(),
				);
			}
			wp_reset_postdata();
		}
		
		wp_send_json_success( $results );
	}

	/**
	 * Add new redirect group
	 *
	 * @since 8.1.0
	 */
	public function add_redirect_group() {
		// Verify nonce
		check_ajax_referer( 'asenha_redirect_manager', 'nonce' );
		
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ) ) );
		}
		
		// Get group name
		$group_name = isset( $_POST['group_name'] ) ? sanitize_text_field( $_POST['group_name'] ) : '';
		
		if ( empty( $group_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Group name cannot be empty.', 'admin-site-enhancements' ) ) );
		}
		
		// Get current groups from options
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// Check if group already exists
		if ( in_array( $group_name, $groups ) ) {
			wp_send_json_error( array( 'message' => __( 'This group already exists.', 'admin-site-enhancements' ) ) );
		}
		
		// Add new group
		$groups[] = $group_name;
		sort( $groups );
		$options_extra['redirect_groups'] = $groups;
		
		// Save to options
		update_option( ASENHA_SLUG_U . '_extra', $options_extra );
		
		wp_send_json_success( array(
			'message' => __( 'Group added successfully.', 'admin-site-enhancements' ),
			'groups'  => $groups,
		) );
	}

	/**
	 * Edit redirect group
	 *
	 * @since 8.1.0
	 */
	public function edit_redirect_group() {
		// Verify nonce
		check_ajax_referer( 'asenha_redirect_manager', 'nonce' );
		
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ) ) );
		}
		
		// Get old and new group names
		$old_group_name = isset( $_POST['old_group_name'] ) ? sanitize_text_field( $_POST['old_group_name'] ) : '';
		$new_group_name = isset( $_POST['new_group_name'] ) ? sanitize_text_field( $_POST['new_group_name'] ) : '';
		
		if ( empty( $old_group_name ) || empty( $new_group_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Group names cannot be empty.', 'admin-site-enhancements' ) ) );
		}
		
		// Get current groups from options
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// Check if old group exists
		$old_key = array_search( $old_group_name, $groups );
		if ( $old_key === false ) {
			wp_send_json_error( array( 'message' => __( 'Original group not found.', 'admin-site-enhancements' ) ) );
		}
		
		// Check if new group name already exists
		$new_key = array_search( $new_group_name, $groups );
		
		if ( $new_key !== false ) {
			// New group name exists - merge groups
			// Remove old group name
			unset( $groups[ $old_key ] );
			$groups = array_values( $groups ); // Re-index array
		} else {
			// New group name doesn't exist - rename
			$groups[ $old_key ] = $new_group_name;
		}
		sort( $groups );
		
		// Save updated groups to options
		$options_extra['redirect_groups'] = $groups;
		update_option( ASENHA_SLUG_U . '_extra', $options_extra );
		
		// Update all redirects with the old group name to use the new group name
		$args = array(
			'post_type'      => 'asenha_redirect',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_redirect_group',
					'value' => $old_group_name,
				),
			),
			'fields'         => 'ids',
		);

		$redirects_with_group = get_posts( $args );

		if ( ! empty( $redirects_with_group ) ) {
			foreach ( $redirects_with_group as $redirect_id ) {
				update_post_meta( $redirect_id, '_redirect_group', $new_group_name );
			}
		}

		// Bust cache after group edit
		$cache = new ASENHA_Redirect_Manager_Cache();
		$cache->bust_cache();
		
		wp_send_json_success( array(
			'message' => __( 'Group updated successfully.', 'admin-site-enhancements' ),
			'groups'  => $groups,
		) );
	}

	/**
	 * Delete redirect group
	 *
	 * @since 8.1.0
	 */
	public function delete_redirect_group() {
		// Verify nonce
		check_ajax_referer( 'asenha_redirect_manager', 'nonce' );
		
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ) ) );
		}
		
		// Get group name
		$group_name = isset( $_POST['group_name'] ) ? sanitize_text_field( $_POST['group_name'] ) : '';
		
		if ( empty( $group_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Group name cannot be empty.', 'admin-site-enhancements' ) ) );
		}
		
		// Get current groups from options
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		// Check if group exists
		$key = array_search( $group_name, $groups );
		if ( $key === false ) {
			wp_send_json_error( array( 'message' => __( 'Group not found.', 'admin-site-enhancements' ) ) );
		}
		
		// Remove group
		unset( $groups[ $key ] );
		$groups = array_values( $groups ); // Re-index array
		sort( $groups );
		$options_extra['redirect_groups'] = $groups;
		
		// Save to options
		update_option( ASENHA_SLUG_U . '_extra', $options_extra );
		
		// Remove the group from all redirects that have it assigned
		$args = array(
			'post_type'      => 'asenha_redirect',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_redirect_group',
					'value' => $group_name,
				),
			),
			'fields'         => 'ids',
		);

		$redirects_with_group = get_posts( $args );

		if ( ! empty( $redirects_with_group ) ) {
			foreach ( $redirects_with_group as $redirect_id ) {
				delete_post_meta( $redirect_id, '_redirect_group' );
			}
		}

		// Bust cache after group deletion
		$cache = new ASENHA_Redirect_Manager_Cache();
		$cache->bust_cache();
		
		wp_send_json_success( array(
			'message' => __( 'Group deleted successfully.', 'admin-site-enhancements' ),
			'groups'  => $groups,
		) );
	}
}

