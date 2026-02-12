<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Utilitify
 * @subpackage Utilitify/includes
 */

namespace Kaizencoders\Utilitify;


/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Utilitify
 * @subpackage Utilitify/includes
 * @author     Your Name <email@example.com>
 */
class Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Utilitify_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @var Plugin $instance
	 */
	public static $instance;

	/**
	 * @var object|Notices
	 */
	public $notices = null;

	/**
	 * @since 1.0.5
	 * @var null
	 *
	 */
	public $query = null;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $Utilitify The string used to uniquely identify this plugin.
	 */
	protected $plugin_name = 'utilitify';

	/**
	 * Plugin Settings.
	 *
	 * @since 1.1.0
	 * @var array
	 *
	 */
	public $settings = [];

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version = '1.0.0';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $version = '' ) {
		$this->version = $version;
		$this->loader  = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new I18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$plugin_i18n->load_plugin_textdomain();
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

		$this->loader->add_action( 'admin_print_scripts', $plugin_admin, 'remove_admin_notices' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'redirect_to_dashboard' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'dismiss_admin_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'kc_uf_show_admin_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_custom_notices' );
		$this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'update_admin_footer_text' );

		// Hide Admin Bar
		$this->loader->add_action( 'admin_print_scripts', $plugin_admin, 'hide_admin_bar_from_admin' );
		//$this->loader->add_action( 'admin_head', $plugin_admin, 'add_404_header_code' );
		$this->loader->add_filter( 'show_admin_bar', $plugin_admin, 'hide_admin_bar' );

		// Auto Update.
		$this->loader->add_filter( 'automatic_updater_disabled', $plugin_admin, 'disabled_auto_update_core', 999999999,
			1 );

		$this->loader->add_action( 'in_plugin_update_message-utilitify/utilitify.php', $plugin_admin,
			'in_plugin_update_message', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_frontend_hooks() {

		$plugin_frontend = new Frontend( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_scripts' );


		$this->loader->add_action( 'init', $plugin_frontend, 'kc_uf_action_filters' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Utilitify_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function define_constants() {

		if ( ! defined( 'KC_UF_ADMIN_TEMPLATES_DIR' ) ) {
			define( 'KC_UF_ADMIN_TEMPLATES_DIR', KC_UF_PLUGIN_DIR . 'lite/includes/Admin/Templates' );
		}

	}

	public function load_dependencies() {

	}

	/**
	 * @since 1.0.0
	 *
	 * @param  string  $group
	 *
	 * @return false|mixed|void
	 *
	 */
	public function get_settings( $group = 'kc_uf' ) {
		return $this->settings;
	}

	/**
	 * Load settings.
	 *
	 * @param $group
	 *
	 * @return void
	 *
	 * @since 1.1.0
	 */
	public function load_settings( $group = 'kc_uf' ) {
		$this->settings = get_option( $group . '_settings' );
	}

	/**
	 * Init Classes
	 *
	 * @since 1.0.0
	 */
	public function init_classes() {

		$classes = [
			'Kaizencoders\Utilitify\Install',
			'Kaizencoders\Utilitify\Feedback',
			'Kaizencoders\Utilitify\Modules\Handle404',
			'Kaizencoders\Utilitify\Modules\Recaptcha',

		];

		foreach ( $classes as $class ) {
			$this->loader->add_class( $class );
		}
	}

	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Plugin ) ) {
			global $wpdb;

			self::$instance = new Plugin( KC_UF_PLUGIN_VERSION );

			self::$instance->define_constants();
			self::$instance->load_dependencies();
			self::$instance->set_locale();
			self::$instance->define_admin_hooks();
			self::$instance->define_frontend_hooks();
			self::$instance->init_classes();
			self::$instance->load_settings();

			self::$instance->notices = new Notices();

			self::$instance->query = Query::boot();
		}

		return self::$instance;
	}

}
