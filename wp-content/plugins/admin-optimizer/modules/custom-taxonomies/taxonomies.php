<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomies class
 */
class Taxonomies {
	const MENU_SLUG = 'adminoptimizer-custom-taxonomies';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'custom-taxonomies/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'custom-taxonomies/';


	const OPTION_NAME = 'adminoptim-custom-taxonomies';

	/**
	 * User defined custom taxonomies
	 *
	 * @var false|mixed|null
	 */
	protected $taxonomies;

	/**
	 * Settings class
	 *
	 * @var Taxonomies_Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->taxonomies = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Taxonomies_Settings( $this->taxonomies );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		if ( ! empty( $this->taxonomies ) ) {
			add_action( 'init', [ $this, 'register_custom_taxonomies' ] );
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
			__( 'Custom Taxonomies', 'admin-optimizer' ),
			__( 'Custom Taxonomies', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Register custom taxonomies
	 *
	 * @return void
	 */
	public function register_custom_taxonomies() {
		if ( ! empty( $this->taxonomies ) ) {
			foreach ( $this->taxonomies as $slug => $taxonomy_arg ) {
				$post_types = $taxonomy_arg['posttypes'] ?? [];
				register_taxonomy( $slug, $post_types, $taxonomy_arg );
			}
		}
	}
}
