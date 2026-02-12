<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableComments
 * Disables new comments and pings while keeping administrative access to existing data.
 */
class DisableComments {
	private array $settings;
	private array $post_types;
	
	public function __construct() {
		$this->settings   = Plugin::get_settings( 'posts' );
		$this->post_types = [ 'post', 'page' ];
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );

		if( empty( $this->settings['disable_comments_enabled'] ) ) {
			return;
		}
		
		// Disable opening of comments/pings on the frontend
		add_filter( 'comments_open', [ $this, 'filter_comments_status' ], 20, 2 );
		add_filter( 'pings_open', [ $this, 'filter_comments_status' ], 20, 2 );
		
		// Remove "Comments" support from post types (hides the metabox in editor)
		add_action( 'init', [ $this, 'remove_post_types_support' ], 100 );
		
		// Remove from Admin Bar for a cleaner frontend experience
		add_action( 'wp_before_admin_bar_render', [ $this, 'remove_admin_bar_comments' ] );
		
		// Hide comment form/list from block themes
		add_filter( 'comments_template', [ $this, 'disable_comments_template' ], 20, 1 );
		add_filter( 'comments_array', [ $this, 'disable_comments_array' ], 20, 2 );
	}
	
	/**
	 * Adds custom settings fields for the AdminEase plugin configuration.
	 *
	 * @param array $fields The existing settings fields to which custom fields will be appended.
	 *
	 * @return array The modified settings fields including the added custom fields.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['posts']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'disable-comments-enabled',
			'name'              => 'adminease[posts][disable_comments_enabled]',
			'value'             => $this->settings['disable_comments_enabled'] ?? false,
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control',
			'label'             => __( 'Disable Comments', 'adminease' ),
			'description'       => __( 'Disable new comments and pings across specified post types. This hides the discussion metabox in the editor and closes comments on the frontend, but keeps existing data accessible in the admin menu.', 'adminease' ),
			'field_description' => __( 'Enable to disable comment functionality for public post types.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Determines if comments should be open based on the post type.
	 *
	 * @param bool $open Whether comments are open.
	 * @param int  $post_id Post ID.
	 *
	 * @return bool
	 */
	public function filter_comments_status( bool $open, int $post_id ): bool {
		$post_type = get_post_type( $post_id );
		
		if( !$post_type ) {
			return $open;
		}
		
		$disabled_post_types = $this->get_disabled_post_types();
		
		if( in_array( $post_type, $disabled_post_types, true ) ) {
			return false;
		}
		
		return $open;
	}
	
	/**
	 * Removes support for comments and trackbacks from specified post types.
	 * @return void
	 */
	public function remove_post_types_support(): void {
		foreach( $this->get_disabled_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
	
	/**
	 * Retrieves the list of post types that should have comments disabled.
	 * This can be filtered to allow customization of the post types, for example,
	 * excluding specific ones based on user settings in the Pro version.
	 * @return array Array of post type names that have comments disabled.
	 */
	private function get_disabled_post_types(): array {
		/**
		 * Filters the post types that should have comments disabled.
		 * The Pro version can use this to exclude post types (like 'product')
		 * based on user settings.
		 *
		 * @param array $post_types Array of post type names.
		 */
		return (array) apply_filters( 'adminease_disable_comments_post_types', $this->post_types );
	}
	
	/**
	 * Removes the "Comments" menu item from the WordPress admin bar.
	 * @return void
	 */
	public function remove_admin_bar_comments(): void {
		global $wp_admin_bar;
		
		if( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'comments' );
		}
	}
	
	/**
	 * Returns an empty template to prevent comment form display.
	 *
	 * @param string $template The path to the comments' template.
	 *
	 * @return string
	 */
	public function disable_comments_template( string $template ): string {
		global $post;

		if( $post && in_array( $post->post_type, $this->get_disabled_post_types(), true ) ) {
			return ADMINEASE_DIR . '/index.php';
		}
		
		return $template;
	}
	
	/**
	 * Returns an empty comments array to prevent display.
	 *
	 * @param array $comments Array of comments.
	 * @param int   $post_id Post ID.
	 *
	 * @return array
	 */
	public function disable_comments_array( array $comments, int $post_id ): array {
		$post_type = get_post_type( $post_id );
		
		if( $post_type && in_array( $post_type, $this->get_disabled_post_types(), true ) ) {
			return [];
		}
		
		return $comments;
	}
}