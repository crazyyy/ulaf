<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post_Types class
 */
class Post_Types {
	const MENU_SLUG = 'adminoptimizer-custom-post-types';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'custom-post-types/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'custom-post-types/';

	const OPTION_NAME = 'adminoptim-post-types';

	/**
	 * User defined Post Types
	 *
	 * @var false|mixed|null
	 */
	protected $post_types;

	/**
	 * Settings class
	 *
	 * @var Post_Types_Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->post_types = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Post_Types_Settings( $this->post_types );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		if ( ! empty( $this->post_types ) ) {
			add_action( 'init', [ $this, 'register_custom_post_type' ] );
		}
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Custom Post Types', 'admin-optimizer' ),
			__( 'Custom Post Types', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Register custom post types
	 *
	 * @return void
	 */
	public function register_custom_post_type() {
		if ( ! empty( $this->post_types ) ) {
			foreach ( $this->post_types as $slug => $post_type_arg ) {
				register_post_type( $slug, $post_type_arg );
			}
		}
	}
}
