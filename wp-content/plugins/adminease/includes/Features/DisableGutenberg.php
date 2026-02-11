<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableGutenberg
 * Handles the disabling of the Gutenberg editor for specific post types
 * and restores the classic editor functionality.
 */
class DisableGutenberg {
	private array $settings;
	private array $post_types;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'posts' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		if( empty( $this->settings['disable_gutenberg'] ) ) {
			return;
		}
		
		// Set default post types (page and post)
		$this->post_types = [ 'page', 'post' ];
		
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_init', [ $this, 'restore_classic_editor' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_gutenberg_assets' ], 100 );
		add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
		add_filter( 'use_widgets_block_editor', '__return_false' );
	}
	
	/**
	 * Adds additional settings fields to the provided settings array.
	 * Extends the settings array with specific options related to disabling Gutenberg.
	 *
	 * @param array $fields An existing array of settings fields, organized by category.
	 *
	 * @return array The modified array of settings fields with additional options.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['posts']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-gutenberg',
			'name'        => 'adminease[posts][disable_gutenberg]',
			'value'       => $this->settings['disable_gutenberg'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Disable Gutenberg', 'adminease' ),
			'description' => __( 'Disable the Gutenberg block editor and revert to the classic editor for all post types. This is useful for users who prefer the classic editing experience or have compatibility issues with Gutenberg.', 'adminease' ),
			'field_description' => __( 'Enable to disable the Gutenberg block editor and use the classic editor instead.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Initializes the process to disable Gutenberg editor for specific post types.
	 * Applies a filter to allow customization of the post types for which Gutenberg editor is disabled.
	 * Adds filters to prevent the use of the block editor for the specified post types.
	 * @return void
	 */
	public function init(): void {
		$this->post_types = apply_filters( 'adminease_disable_gutenberg_post_types', $this->post_types );
		
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg_for_post_type' ], 100, 2 );
		add_filter( 'gutenberg_can_edit_post_type', [ $this, 'disable_gutenberg_for_post_type' ], 100, 2 );
	}
	
	/**
	 * Disables the Gutenberg editor for specified post types.
	 * Checks if the given post type is included in the list of post types for which the Gutenberg editor is disabled.
	 * If the post type matches, it prevents the editor from being enabled.
	 *
	 * @param bool   $can_edit Whether the Gutenberg editor can be used for the post type.
	 * @param string $post_type The post type being checked.
	 *
	 * @return bool Returns false to disable Gutenberg if the post type matches; otherwise, returns the original $can_edit value.
	 */
	public function disable_gutenberg_for_post_type( bool $can_edit, string $post_type ): bool {
		if( in_array( $post_type, $this->post_types ) ) {
			return false;
		}
		
		return $can_edit;
	}
	
	/**
	 * Restores the classic editor by removing Gutenberg-specific menu and redirection actions.
	 * Adds a filter to disable the block editor for posts if the 'classic-editor' query parameter is set.
	 * @return void
	 */
	public function restore_classic_editor() {
		remove_action( 'admin_menu', 'gutenberg_menu' );
		remove_action( 'admin_init', 'gutenberg_redirect_demo' );
		
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to conditionally disable block editor, no sensitive processing
		if( isset( $_GET['classic-editor'] ) ) {
			add_filter( 'use_block_editor_for_post', '__return_false', 100 );
		}
	}
	
	/**
	 * Removes Gutenberg-specific assets including block styles and global styles.
	 * The assets are dequeued based on the current post type in both admin and frontend contexts.
	 * It checks the post type against the specified post types to determine whether the assets should be removed.
	 * In the admin area, the post type is determined using global variables and request parameters.
	 * On the frontend, it verifies the post type of the singular post being viewed.
	 * @return void
	 */
	public function remove_gutenberg_assets() {
		global $post;
		
		$should_remove = false;
		
		// Admin context
		if( is_admin() ) {
			$current_post_type = $this->get_current_admin_post_type();
			
			if( $current_post_type && in_array( $current_post_type, $this->post_types ) ) {
				$should_remove = true;
			}
		}
		
		// Frontend context
		if( !is_admin() && is_singular() && $post && in_array( $post->post_type, $this->post_types ) ) {
			$should_remove = true;
		}
		
		if( $should_remove ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-block-style' );
			wp_dequeue_style( 'global-styles' );
		}
	}
	
	/**
	 * Determines the current post type in admin context.
	 * Uses global variables and request parameters to identify the post type.
	 * @return string|null The current post type or null if not determinable.
	 */
	private function get_current_admin_post_type(): ?string {
		global $typenow, $pagenow;
		
		if( $typenow ) {
			return $typenow;
		}
		
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to determine post type for asset removal, no sensitive processing
		if( isset( $_GET['post_type'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to determine post type for asset removal, no sensitive processing
			return sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
		}
		
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to determine post for asset removal, no sensitive processing
		if( isset( $_GET['post'] ) && $pagenow === 'post.php' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to determine post for asset removal, no sensitive processing
			return get_post_type( intval( $_GET['post'] ) );
		}
		
		return null;
	}
}