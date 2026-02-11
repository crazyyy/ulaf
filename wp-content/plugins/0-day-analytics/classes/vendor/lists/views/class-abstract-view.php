<?php
/**
 * Abstract view class for common page display functionality.
 *
 * @package advanced-analytics
 * @subpackage lists/views
 * @since 4.4.1
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( '\ADVAN\Lists\Views\Abstract_View' ) ) {
	/**
	 * Abstract base class for views.
	 *
	 * Provides common functionality for displaying admin pages.
	 *
	 * @since 4.4.1
	 */
	abstract class Abstract_View {

		/**
		 * Display the page with common elements.
		 *
		 * @param string $permission_message Message for permission error.
		 *
		 * @return void
		 *
		 * @since 4.5.2
		 */
		protected static function display_page( string $permission_message ): void {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html( $permission_message ) );
			}

			\wp_enqueue_script( 'wp-api-fetch' );
			\wp_enqueue_style( 'media-views' );
			\add_thickbox();

			// Skin script.
			?>
			<script>
				if ( typeof localStorage !== 'undefined' ) {
					var skin = localStorage.getItem('aadvana-backend-skin');
					if ( skin === 'dark' ) {
						document.getElementsByTagName("html")[0].classList.add("aadvana-darkskin");
					}
				}
			</script>
			<?php

			static::render_page_content();
		}

		/**
		 * Render the specific page content.
		 *
		 * @return void
		 *
		 * @since 4.5.2
		 */
		abstract protected static function render_page_content(): void;
	}
}
