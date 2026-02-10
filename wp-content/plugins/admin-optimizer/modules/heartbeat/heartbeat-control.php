<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heartbeat_Control class
 */
class Heartbeat_Control {
	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var Heartbeat_Settings
	 */
	protected $settings;

	const OPTION_NAME = 'adminoptim_heartbeat_control';

	const MENU_SLUG = 'adminoptimizer-heartbeat-control';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'heartbeat/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'heartbeat/';

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
	private function init() {
		$this->settings = new Heartbeat_Settings();

		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'disable_heartbeat' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'disable_heartbeat' ], 99 );
		add_filter( 'heartbeat_settings', [ $this, 'modify_heartbeat_frequency' ], 99 );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Heartbeat Control', 'admin-optimizer' ),
			__( 'Heartbeat Control', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Disable heartbeat
	 *
	 * @return void
	 */
	public function disable_heartbeat() {
		global $pagenow;

		if ( ( isset( $this->options['admin'] ) && 'disable' === $this->options['admin'] && is_admin() ) ||
			( isset( $this->options['frontend'] ) && 'disable' === $this->options['frontend'] && ! is_admin() ) ||
			( isset( $this->options['editor'] ) && 'disable' === $this->options['editor'] && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

	/**
	 * Modify heartbeat frequency
	 *
	 * @param array $settings List of user defined options.
	 *
	 * @return array
	 */
	public function modify_heartbeat_frequency( $settings ) {
		global $pagenow;

		if ( wp_doing_cron() ) {
			return $settings;
		}

		$default = [
			'admin'    => '',
			'frontend' => '',
			'editor'   => '',
		];
		$options = wp_parse_args( $this->options, $default );

		// Disable heartbeat autostart.
		$settings['autostart'] = false;

		if ( is_admin() ) {
			if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				if ( 'modify' === $options['editor'] ) {
					$settings['minimalInterval'] = absint( $options['editor_freq'] );
				} elseif ( 'disable' === $options['editor'] ) {
					$settings['disabled'] = true;
				}
			} elseif ( 'modify' === $options['admin'] ) {
					$settings['interval'] = absint( $options['admin_freq'] );
			} elseif ( 'disable' === $options['admin'] ) {
				$settings['disabled'] = true;
			}
		} elseif ( 'modify' === $options['frontend'] ) {
				$settings['interval'] = absint( $options['frontend'] );
		} elseif ( 'disable' === $options['frontend'] ) {
			$settings['disabled'] = true;
		}

		return $settings;
	}
}
