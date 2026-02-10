<?php
/**
 * Redirect Manager CPT Registration
 *
 * Handles custom post type registration and meta fields
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing the Redirect CPT
 */
class ASENHA_Redirect_Manager_CPT {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'save_post_asenha_redirect', array( $this, 'save_post_meta' ), 10, 2 );
	}

	/**
	 * Register the asenha_redirect custom post type
	 *
	 * @since 8.1.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Redirects', 'Post type general name', 'admin-site-enhancements' ),
			'singular_name'         => _x( 'Redirect', 'Post type singular name', 'admin-site-enhancements' ),
			'menu_name'             => _x( 'Redirects', 'Admin Menu text', 'admin-site-enhancements' ),
			'name_admin_bar'        => _x( 'Redirect', 'Add New on Toolbar', 'admin-site-enhancements' ),
			'add_new'               => __( 'Add New', 'admin-site-enhancements' ),
			'add_new_item'          => __( 'Add New Redirect', 'admin-site-enhancements' ),
			'new_item'              => __( 'New Redirect', 'admin-site-enhancements' ),
			'edit_item'             => __( 'Edit Redirect', 'admin-site-enhancements' ),
			'view_item'             => __( 'View Redirect', 'admin-site-enhancements' ),
			'all_items'             => __( 'All Redirects', 'admin-site-enhancements' ),
			'search_items'          => __( 'Search Redirects', 'admin-site-enhancements' ),
			'parent_item_colon'     => __( 'Parent Redirects:', 'admin-site-enhancements' ),
			'not_found'             => __( 'No redirects found.', 'admin-site-enhancements' ),
			'not_found_in_trash'    => __( 'No redirects found in Trash.', 'admin-site-enhancements' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false, // We'll add a custom menu item
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
			),
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'page-attributes' ),
			'show_in_rest'       => false,
		);

		register_post_type( 'asenha_redirect', $args );
	}

	/**
	 * Register meta fields for redirects
	 *
	 * @since 8.1.0
	 */
	public function register_meta_fields() {
		$meta_fields = array(
			'_redirect_from'              => 'string',
			'_redirect_to'                => 'string',
			'_redirect_http_status_code'  => 'integer',
			'_redirect_from_regex'   	  => 'boolean',
			'_redirect_strip_query_params' => 'boolean',
			'_redirect_group'             => 'string',
			'_redirect_notes'             => 'string',
			'_redirect_message'           => 'string',
		);

		foreach ( $meta_fields as $meta_key => $meta_type ) {
			register_post_meta(
				'asenha_redirect',
				$meta_key,
				array(
					'type'              => $meta_type,
					'description'       => '',
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => array( $this, 'sanitize_meta_field' ),
				)
			);
		}
	}

	/**
	 * Sanitize meta field values
	 *
	 * @since 8.1.0
	 * @param mixed $meta_value The meta value to sanitize
	 * @param string $meta_key The meta key
	 * @param string $object_type The object type
	 * @return mixed Sanitized value
	 */
	public function sanitize_meta_field( $meta_value, $meta_key, $object_type ) {
		switch ( $meta_key ) {
			case '_redirect_from':
			case '_redirect_group':
				return sanitize_text_field( $meta_value );
			
			case '_redirect_to':
				return esc_url_raw( $meta_value );
			
			case '_redirect_http_status_code':
				return absint( $meta_value );
			
			case '_redirect_from_regex':
			case '_redirect_strip_query_params':
				return (bool) $meta_value;
			
			case '_redirect_notes':
			case '_redirect_message':
				return wp_kses_post( $meta_value );
			
			default:
				return sanitize_text_field( $meta_value );
		}
	}

	/**
	 * Save post meta on redirect save
	 *
	 * @since 8.1.0
	 * @param int $post_id The post ID
	 * @param WP_Post $post The post object
	 */
	public function save_post_meta( $post_id, $post ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check nonce
		if ( ! isset( $_POST['asenha_redirect_meta_nonce'] ) || ! wp_verify_nonce( $_POST['asenha_redirect_meta_nonce'], 'asenha_redirect_meta_save' ) ) {
			return;
		}

		// Save redirect from
		if ( isset( $_POST['redirect_from'] ) ) {
			$redirect_from = sanitize_text_field( $_POST['redirect_from'] );
			update_post_meta( $post_id, '_redirect_from', $redirect_from );
			
			// Update post title to match redirect_from value
			if ( ! empty( $redirect_from ) ) {
				// Remove the save_post hook temporarily to avoid infinite loop
				remove_action( 'save_post_asenha_redirect', array( $this, 'save_post_meta' ), 10 );
				
				wp_update_post( array(
					'ID'         => $post_id,
					'post_title' => $redirect_from,
				) );
				
				// Re-add the save_post hook
				add_action( 'save_post_asenha_redirect', array( $this, 'save_post_meta' ), 10, 2 );
			}
		}

		// Save action type
		if ( isset( $_POST['redirect_action_type'] ) ) {
			$action_type = sanitize_text_field( $_POST['redirect_action_type'] );
			// Validate action type
			if ( in_array( $action_type, array( 'redirect', 'error' ) ) ) {
				update_post_meta( $post_id, '_redirect_action_type', $action_type );
			}
		}

		// Save HTTP status code
		if ( isset( $_POST['redirect_http_status_code'] ) ) {
			$status_code = absint( $_POST['redirect_http_status_code'] );
			update_post_meta( $post_id, '_redirect_http_status_code', $status_code );
		} else {
			update_post_meta( $post_id, '_redirect_http_status_code', 302 ); // Default to 302
		}
		
		// Save redirect to
		if ( isset( $_POST['redirect_to'] ) ) {
			$redirect_to = esc_url_raw( $_POST['redirect_to'] );
			update_post_meta( $post_id, '_redirect_to', $redirect_to );
		}

		// Save regex enabled
		$regex_enabled = isset( $_POST['redirect_rule_from_regex'] ) ? true : false;
		update_post_meta( $post_id, '_redirect_from_regex', $regex_enabled );

		// Save strip query params
		$strip_query_params = isset( $_POST['redirect_strip_query_params'] ) ? true : false;
		update_post_meta( $post_id, '_redirect_strip_query_params', $strip_query_params );

		// Save group
		if ( isset( $_POST['redirect_group'] ) ) {
			$group = sanitize_text_field( $_POST['redirect_group'] );
			update_post_meta( $post_id, '_redirect_group', $group );
		}

		// Save notes
		if ( isset( $_POST['redirect_notes'] ) ) {
			$notes = wp_kses_post( $_POST['redirect_notes'] );
			update_post_meta( $post_id, '_redirect_notes', $notes );
		}

		// Save message
		if ( isset( $_POST['redirect_message'] ) ) {
			$message = wp_kses_post( $_POST['redirect_message'] );
			update_post_meta( $post_id, '_redirect_message', $message );
		}

		// Bust cache when redirect is saved
		$cache = new ASENHA_Redirect_Manager_Cache();
		$cache->bust_cache();
	}
}

