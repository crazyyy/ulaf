<?php

namespace Yipresser\AdminOptimizer\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ADMINOPTIMIZER_MODULES_URI' ) ) {
	define( 'ADMINOPTIMIZER_MODULES_URI', ADMINOPTIMIZER_URI . 'modules/' );
}

if ( ! defined( 'ADMINOPTIMIZER_MODULES_PATH' ) ) {
	define( 'ADMINOPTIMIZER_MODULES_PATH', ADMINOPTIMIZER_PATH . 'modules/' );
}

if ( ! defined( 'ADMINOPTIMIZER_MODULES_MENU_SLUG' ) ) {
	define( 'ADMINOPTIMIZER_MODULES_MENU_SLUG', 'admin-optimizer' );
}

use Yipresser\AdminOptimizer\Modules\{Content_Management,
	Media_Management,
	Export_Import,
	Security,
	Utilities,
	Disable_Features,
	Site_Management,
	Users_Management};

const MODULES_OPTION = 'adminoptim-modules';

/**
 * Modules Singleton class
 */
final class Bootstrap {
	/**
	 * Single Instance
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * List of Modules classes
	 *
	 * @var array
	 */
	public $modules = [];

	/**
	 * Settings class
	 *
	 * @var Modules_Settings
	 */
	protected $settings;

	/**
	 * User options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	const MODULE_URL = ADMINOPTIMIZER_URI . 'modules/';

	const MODULE_PATH = ADMINOPTIMIZER_PATH . 'modules/';

	/**
	 * Initialize a singleton instance
	 *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->options = get_option( MODULES_OPTION, [] );
		$this->load_modules();
		$this->setup();
	}

	/**
	 * Initialize all modules
	 *
	 * @return void
	 */
	private function load_modules() {
		$this->modules['content']          = new Content_Management( $this->options );
		$this->modules['media']            = new Media_Management( $this->options );
		$this->modules['security']         = new Security( $this->options );
		$this->modules['utilities']        = new Utilities( $this->options );
		$this->modules['disable-features'] = new Disable_Features( $this->options );
		$this->modules['site']             = new Site_Management( $this->options );
		$this->modules['users']            = new Users_Management( $this->options );
		$this->modules['export']           = new Export_Import();
		$this->settings                    = new Admin_Settings();
	}

	/**
	 * Setup admin menu
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_menu() {
		$menu_title    = apply_filters( 'adminoptimizer_menu_title', __( 'Admin Optimizer', 'admin-optimizer' ) );
		$page_title    = apply_filters( 'adminoptimizer_page_title', __( 'Admin Optimizer', 'admin-optimizer' ) );
		$menu_svg_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMjAiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDIxIDIwIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZyB0cmFuc2Zvcm09Im1hdHJpeCguMjI3ODcgMCAwIC4yMTgzOCAuMjI4NzcgLjI4MjkzKSIgZmlsbD0ibm9uZSI+Cgk8cGF0aCBkPSJtODkuOTgzIDUuNjNjLTZlLTMgLTAuMjY3LTAuMDE2LTAuNTM0LTAuMDI2LTAuODAyLTAuMDExLTAuMjk5LTAuMDItMC41OTctMC4wMzYtMC44OTctMC4wMzEtMC42MDItMC4wNy0xLjIwNy0wLjEyMS0xLjgxNC0wLjA4MS0wLjk3My0wLjg1NC0xLjc0NS0xLjgyNy0xLjgyNy0wLjYwNy0wLjA1MS0xLjIxLTAuMDg5LTEuODExLTAuMTIxLTAuMzA1LTAuMDE2LTAuNjA3LTAuMDI1LTAuOTA5LTAuMDM2LTAuMjYyLTllLTMgLTAuNTI1LTAuMDItMC43ODYtMC4wMjUtMC40MzctMC4wMS0wLjg3MS0wLjAxMy0xLjMwNC0wLjAxMy0wLjA3MiAwLTAuMTQ1IDAtMC4yMTcgMWUtMyAtOC42MjggMC4wNDItMTYuNTQ4IDIuMTYtMjQuNTQ0IDYuNTI2LTAuMTQxIDAuMDc4LTAuMjgyIDAuMTUxLTAuNDIzIDAuMjI4LTAuMDUgMC4wMjgtMC4wOTkgMC4wNTItMC4xNDkgMC4wOC0wLjAxMSA2ZS0zIC0wLjAyIDAuMDE2LTAuMDMxIDAuMDIyLTYuNTU2IDMuNjU0LTEzLjEwMSA4LjgxMS0xOS44NzUgMTUuNTg1LTAuNzcgMC43Ny0xLjUyMyAxLjU1LTIuMjY4IDIuMzM0bC0xMy4xNjQgMS4wMDFjLTAuMzg1IDAuMDI5LTAuNzUzIDAuMTY5LTEuMDYgMC40MDJsLTIwLjY0NyAxNS43MTNjLTAuNjU3IDAuNS0wLjk0IDEuMzUyLTAuNzExIDIuMTQ1IDAuMjI4IDAuNzkzIDAuOTIgMS4zNjQgMS43NDIgMS40MzlsMTkuMzczIDEuNzQ5IDYuMTM0IDYuMTM0Yy0yLjE3NCAwLjQ5Ny00LjM4OSAxLjcxNS02LjI4NiAzLjYxMS0xLjEzNiAxLjEzNy0yLjA0OCAyLjQxMS0yLjcxNiAzLjgwMy0wLjg3MyAxLjg0OS0yLjc5IDYuNjEtNC44MiAxMS42NTFsLTAuOTkxIDIuNDU5Yy0wLjMgMC43NDQtMC4xMjcgMS41OTUgMC40NDEgMi4xNjIgMC4zODIgMC4zODMgMC44OTQgMC41ODYgMS40MTUgMC41ODYgMC4yNTEgMCAwLjUwNS0wLjA0OCAwLjc0OC0wLjE0NmwyLjU0Ny0xLjAyN2M1LTIuMDE0IDkuNzIzLTMuOTE3IDExLjU3Ni00Ljc5IDEuMzgtMC42NjQgMi42NTUtMS41NzYgMy43OS0yLjcxMSAxLjg5Ni0xLjg5NiAzLjExMy00LjExMSAzLjYxLTYuMjg1bDUuOTUyIDUuOTUyIDEuNzQ5IDE5LjM3MmMwLjA3NCAwLjgyMiAwLjY0NiAxLjUxNCAxLjQzOSAxLjc0MiAwLjE4MyAwLjA1MyAwLjM2OSAwLjA3OCAwLjU1MyAwLjA3OCAwLjYxNCAwIDEuMjA3LTAuMjgzIDEuNTkyLTAuNzg5bDE1LjcxMS0yMC42NDZjMC4yMzMtMC4zMDcgMC4zNzMtMC42NzUgMC40MDItMS4wNmwwLjk3MS0xMi43NzVjMC44NTctMC44MTEgMS43MDYtMS42MzUgMi41NDctMi40NzUgNi43NzktNi43NzkgMTEuOTM5LTEzLjMyNyAxNS41OTQtMTkuODg3IDRlLTMgLTdlLTMgMC4wMS0wLjAxMyAwLjAxNC0wLjAyIDAuMDE4LTAuMDMyIDAuMDMzLTAuMDYzIDAuMDUxLTAuMDk1IDAuMTY3LTAuMzAxIDAuMzI2LTAuNjAyIDAuNDg2LTAuOTA0IDQuMjA3LTcuODQ3IDYuMjUxLTE1LjYzNSA2LjI5NS0yNC4wOTkgMWUtMyAtMC4wODMgMWUtMyAtMC4xNjUgMWUtMyAtMC4yNDggMmUtMyAtMC40MjUtMWUtMyAtMC44NTMtMC4wMTEtMS4yODN6bS0yNS41NyAzMS44NjNjLTEuNTc3IDEuNTc3LTMuNjc1IDIuNDQ3LTUuOTA3IDIuNDQ3LTIuMjMxIDAtNC4zMjktMC44NjktNS45MDctMi40NDctMy4yNTctMy4yNTgtMy4yNTctOC41NTcgMC0xMS44MTV2MGMzLjI1OS0zLjI1NyA4LjU1OS0zLjI1NSAxMS44MTQgMCAxLjU3OCAxLjU3NyAyLjQ0OCAzLjY3NSAyLjQ0OCA1LjkwN3MtMC44NjkgNC4zMy0yLjQ0OCA1LjkwOHoiIGZpbGw9IiMwMDAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L2c+Cjwvc3ZnPgo=';

		add_menu_page( $page_title, $menu_title, 'manage_options', ADMINOPTIMIZER_MODULES_MENU_SLUG, '', $menu_svg_icon );

		do_action( 'adminoptimizer_add_submenu_page' );
	}
}
