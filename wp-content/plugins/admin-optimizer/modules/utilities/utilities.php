<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Utilities class
 */
class Utilities {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * List of modules
	 *
	 * @var array
	 */
	protected $modules = [];

	/**
	 * Constructor
	 *
	 * @param array $options User options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		add_filter( 'adminoptimizer_settings_navtab', [ $this, 'add_settings_navtab' ] );
		add_filter( 'adminoptimizer_settings_sections', [ $this, 'settings_fields' ] );
		if ( ! empty( $this->options['enable_smtp_mail'] ) ) {
			$this->modules['smtp'] = new SMTP_Email();
		}
		if ( ! empty( $this->options['enable_heartbeat_control'] ) ) {
			$this->modules['heartbeat'] = new Heartbeat_Control();
		}
		if ( ! empty( $this->options['enable_db_cleaner'] ) ) {
			$this->modules['db_cleaner'] = new DB_Cleaner();
		}
	}

	/**
	 * Add settings navtab
	 *
	 * @param array $nav_tab List of nav tabs.
	 *
	 * @return array
	 */
	public function add_settings_navtab( $nav_tab ) {
		if ( empty( $nav_tab['utilities'] ) ) {
			$nav_tab['utilities'] = [
				'title' => __( 'Utilities', 'admin-optimizer' ),
				'slug'  => 'adminoptim-utilities-settings',
			];
		}
		return $nav_tab;
	}

	/**
	 * List of Settings fields
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ) {
		if ( empty( $fields['utilities'] ) ) {
			$fields['utilities'] = [
				'id'          => 'adminoptimizer-utilities-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-utilities-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => [
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Enable Heartbeat Control', 'admin-optimizer' ),
						'id'    => 'enable-heartbeat-control',
						'name'  => 'enable_heartbeat_control',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Modify the interval of the WordPress heartbeat API to reduce CPU load on the server.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/heartbeat-control/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Enable SMTP Mail', 'admin-optimizer' ),
						'id'    => 'enable-smtp-mail',
						'name'  => 'enable_smtp_mail',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Enable email sending functionality in WordPress using your own SMTP provider.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/smtp-mail/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Enable Database Cleaner', 'admin-optimizer' ),
						'id'    => 'enable-db-cleaner',
						'name'  => 'enable_db_cleaner',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Schedule regular optimization and cleaning up of the WP database to improve the performance of the site. Comes with Pro options. (comes with Pro options)%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/database-cleaner/' ) . '" target="_blank">', '</a>' ),
					],
				],
			];
		}
		return $fields;
	}
}
