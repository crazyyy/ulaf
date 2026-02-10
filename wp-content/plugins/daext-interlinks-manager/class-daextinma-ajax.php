<?php
/**
 * This file contains the class Daextinma_Ajax, used to include ajax actions.
 *
 * @package daext-interlinks-manager
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextinma_Ajax {

	/**
	 * The instance of the Daextinma_Ajax class.
	 *
	 * @var Daextinma_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daextinma_Shared class.
	 *
	 * @var Daextinma_Shared
	 */
	private $shared = null;

	/**
	 * The constructor of the Daextinma_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextinma_Shared::get_instance();

		// Ajax requests --------------------------------------------------------.

		// For logged-in users --------------------------------------------------.
		add_action( 'wp_ajax_generate_interlinks_optimization', array( $this, 'generate_interlinks_optimization' ) );
	}

	/**
	 * Return an istance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Ajax handler used to generate the content of the "Interlinks Optimization" meta box.
	 */
	public function generate_interlinks_optimization() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextinma', 'security', false ) ) {
			echo 'Invalid AJAX Request';
			die();}

		// Check the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo 'Invalid Capability';
			die();
		}

		// Get data.
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'], 10 ) : null;

		// Generate the HTML of the meta-box.
		$this->shared->generate_interlinks_optimization_metabox_html( get_post( $post_id ) );

		die();
	}
}
