<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Security class
 */
class Security {
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

		if ( ! empty( $this->options['hide_wp_version'] ) ) {
			remove_action( 'wp_head', 'wp_generator' );
		}
		if ( ! empty( $this->options['hide_update_notices'] ) ) {
			add_action( 'admin_head', [ $this, 'hide_update_notice_to_all_but_admin_users' ], 1 );
		}
		if ( ! empty( $this->options['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter(
				'wp_xmlrpc_server_class',
				function () {
					header( 'HTTP/1.1 403 Forbidden' );
					exit( 'You don\'t have permission to access this file.' );
				}
			);
		}
		if ( ! empty( $this->options['enable_custom_login_url'] ) ) {
			$this->modules['custom_login_url'] = new Custom_Login_Url();
		}
		if ( ! empty( $this->options['enable_block_login'] ) ) {
			$this->modules['block_login'] = new Block_Login();
		}
		if ( ! empty( $this->options['enable_2fa'] ) ) {
			$this->modules['2fa'] = new Two_Factor_Authentication();
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
		if ( empty( $nav_tab['security'] ) ) {
			$nav_tab['security'] = [
				'title' => __( 'Security', 'admin-optimizer' ),
				'slug'  => 'adminoptim-security-settings',
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
		if ( empty( $fields['security'] ) ) {
			$setting_fields = [
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Hide WP version', 'admin-optimizer' ),
					'id'    => 'hide-wp-version',
					'name'  => 'hide_wp_version',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Remove WP version from the header.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/hide-wp-version/' ) . '" target="_blank">', '</a>' ),
				],
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Hide Update notice to all users, except for Administrators', 'admin-optimizer' ),
					'id'    => 'hide-update-notices',
					'name'  => 'hide_update_notices',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Only show update notices to Administrators and those with update capabilities.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/hide-update-notice/' ) . '" target="_blank">', '</a>' ),
				],
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Disable XML-RPC', 'admin-optimizer' ),
					'id'    => 'disable-xmlrpc',
					'name'  => 'disable_xmlrpc',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Disable XML-RPC for the whole site.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-xml-rpc/' ) . '" target="_blank">', '</a>' ),
				],
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Custom Login URL', 'admin-optimizer' ),
					'id'    => 'enable-custom-login-url',
					'name'  => 'enable_custom_login_url',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Hide the wp-login page and create a custom login URL for all users.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/custom-login-url/' ) . '" target="_blank">', '</a>' ),
				],
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Block Failed Login', 'admin-optimizer' ),
					'id'    => 'enable-block-login',
					'name'  => 'enable_block_login',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Block the user from login after repeated failed login attempt. (comes with Pro options) %1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/block-failed-login/' ) . '" target="_blank">', '</a>' ),
				],
				[
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable Two Factor Authentication (2FA)', 'admin-optimizer' ),
					'id'    => 'enable-2fa',
					'name'  => 'enable_2fa',
					// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
					'label' => sprintf( __( 'Enable two factor authentication for all users. (comes with Pro options)%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/two-factor-authentication/' ) . '" target="_blank">', '</a>' ),
				],
			];
			$fields['security'] = [
				'id'          => 'adminoptimizer-security-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-security-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => $setting_fields,
			];
		}
		return $fields;
	}

	/**
	 * Hide update notice
	 *
	 * @return void
	 */
	public function hide_update_notice_to_all_but_admin_users() {
		if ( ! current_user_can( 'update_core' ) ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}
	}
}
