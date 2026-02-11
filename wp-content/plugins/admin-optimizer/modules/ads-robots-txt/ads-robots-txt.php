<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ads_Robots_Txt class
 */
class Ads_Robots_Txt {
	const OPTION_NAME = 'adminoptim_ads_robots_txt';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var AdsTXT_Settings
	 */
	protected $settings;

	const MENU_SLUG = 'adminoptimizer-adstxt';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Ads_Robots_Txt_Settings( $this->options );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'init', [ $this, 'display_entries' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Manage ads.txt and robots.txt', 'admin-optimizer' ),
			__( 'Manage ads.txt and robots.txt', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Display ads.txt and app-ads.txt content
	 *
	 * @return void
	 */
	public function display_entries() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;

		if ( '/robots.txt' === $request && ! empty( $this->options['robotstxt_content'] ) ) {
			header( 'Content-Type: text/plain' );
			echo esc_textarea( stripslashes( $this->options['robotstxt_content'] ) );
			die();
		}

		if ( '/ads.txt' === $request && ! empty( $this->options['adstxt_content'] ) ) {
			header( 'Content-Type: text/plain' );
			echo esc_textarea( stripslashes( $this->options['adstxt_content'] ) );
			die();
		}

		if ( '/app-ads.txt' === $request && ! empty( $this->options['app_adstxt_content'] ) ) {
			header( 'Content-Type: text/plain' );
			echo esc_textarea( stripslashes( $this->options['app_adstxt_content'] ) );
			die();
		}
	}
}
