<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles infinite scrolling in the WordPress media library interface.
 */
class MediaLibraryInfiniteScrolling {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'media' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !is_admin() ) {
			return;
		}
		
		if( !empty( $this->settings['media_library_infinite_scrolling'] ) ) {
			add_filter( 'media_library_infinite_scrolling', '__return_true' );
		}
	}
	
	/**
	 * Modifies and adds custom settings fields for the Adminease plugin.
	 *
	 * @param array $fields The existing settings fields array to be customized and updated.
	 *
	 * @return array The modified settings fields array with additional custom fields.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['media']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'media-library-infinite-scrolling',
			'name'        => 'adminease[media][media_library_infinite_scrolling]',
			'value'       => $this->settings['media_library_infinite_scrolling'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Enable Infinite Scrolling', 'adminease' ),
			'description' => __( 'Automatically load more media items when you scroll to the bottom of the media library.', 'adminease' ),
		];
		
		return $fields;
	}
}