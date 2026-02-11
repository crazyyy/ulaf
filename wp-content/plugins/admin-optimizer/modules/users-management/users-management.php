<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Users class
 */
class Users_Management {
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

		if ( ! empty( $this->options['display_user_registration_date'] ) || ! empty( $this->options['enable_user_login_tracking'] ) ) {
			add_filter( 'manage_users_columns', [ $this, 'add_user_custom_columns' ] );
			add_action( 'manage_users_custom_column', [ $this, 'render_user_custom_columns' ], 10, 3 );
			add_filter( 'request', [ $this, 'user_custom_column_orderby' ] );
		}
		if ( ! empty( $this->options['disable_user_account'] ) ) {
			$this->modules['disable_user_login'] = new Disable_User_Account();
		}
		if ( ! empty( $this->options['display_user_registration_date'] ) ) {
			add_filter( 'manage_users_sortable_columns', [ $this, 'add_user_custom_column_sortable' ] );
		}
		if ( ! empty( $this->options['enable_user_login_tracking'] ) ) {
			add_action( 'wp_login', [ $this, 'user_last_login' ], 10, 2 );
		}
		if ( ! empty( $this->options['hide_admin_toolbar'] ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
		if ( ! empty( $this->options['disable_user_signup_notification'] ) ) {
			add_action(
				'init',
				function () {
					remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
					remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications' );
				}
			);
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
		if ( empty( $nav_tab['users'] ) ) {
			$nav_tab['users'] = [
				'title' => __( 'Users Management', 'admin-optimizer' ),
				'slug'  => 'adminoptim-users-settings',
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
		if ( empty( $fields['users'] ) ) {
			$fields['users'] = [
				'id'          => 'adminoptimizer-users-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-users-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => [
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable User Account', 'admin-optimizer' ),
						'id'    => 'disable-user-account',
						'name'  => 'disable_user_account',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable user accounts and prevent them from logging in.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-user-accounts/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Display User registration date', 'admin-optimizer' ),
						'id'    => 'display-user-registration-date',
						'name'  => 'display_user_registration_date',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Track and show the user registration date.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/display-user-registration-date/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Track User\'s last login date', 'admin-optimizer' ),
						'id'    => 'enable-user-login-tracking',
						'name'  => 'enable_user_login_tracking',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Track and display the user last login date.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/last-login-date/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Hide admin toolbar for all users', 'admin-optimizer' ),
						'id'    => 'hide-admin-toolbar',
						'name'  => 'hide_admin_toolbar',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable the admin toolbar for all users when they are logged in and viewing the site on the frontend.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/hide-admin-toolbar/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable new user signup notification', 'admin-optimizer' ),
						'id'    => 'disable-user-signup-notification',
						'name'  => 'disable_user_signup_notification',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable the option to send the new user an email when their account was created.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-new-user-signup-notification/' ) . '" target="_blank">', '</a>' ),
					],
				],
			];
		}
		return $fields;
	}

	/**
	 * Check user last login
	 *
	 * @param string   $user_login User's username.
	 * @param \WP_User $user WP_User class.
	 *
	 * @return void
	 */
	public function user_last_login( $user_login, $user ) {
		update_user_meta( $user->ID, 'last_login', time() );
	}

	/**
	 * Render Custom columns in User page
	 *
	 * @param string $value Custom column output.
	 * @param string $column_name Column name.
	 * @param int    $user_id ID of the currently-listed user.
	 *
	 * @return false|string
	 */
	public function render_user_custom_columns( $value, $column_name, $user_id ) {
		if ( 'registerdate' === $column_name ) {
			$user       = get_userdata( $user_id );
			$registered = strtotime( get_date_from_gmt( $user->user_registered ) );

			return '<span>' . esc_html( wp_date( get_option( 'date_format' ), $registered ) ) . '<br />' . esc_html( wp_date( get_option( 'time_format' ), $registered ) ) . '</span>';
		} elseif ( 'lastlogin' === $column_name ) {
			$last_login     = (int) get_user_meta( $user_id, 'last_login', true );
			$the_login_date = wp_date( 'd M Y, H:i:s', $last_login );
			if ( empty( $the_login_date ) || empty( $last_login ) ) {
				$the_login_date = 'Never';
			}
			return $the_login_date;

		} else {
			return $value;
		}
	}

	/**
	 * Add custom colums to Users page.
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function add_user_custom_column_sortable( $columns ): array {
		$custom = [];
		if ( ! empty( $this->options['display_user_registration_date'] ) ) {
			$custom['registerdate'] = 'registered';
		}
		if ( ! empty( $custom ) ) {
			return wp_parse_args( $custom, $columns );
		} else {
			return $columns;
		}
	}

	/**
	 * Define custom column orderby
	 *
	 * @param array $vars Vars.
	 *
	 * @return mixed
	 */
	public function user_custom_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'registerdate' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				[
					'meta_key' => 'registerdate', //phpcs:ignore
					'orderby'  => 'meta_value',
				]
			);
		}
		return $vars;
	}

	/**
	 * Add custom columns to Users page.
	 *
	 * @param array $columns All columns.
	 *
	 * @return array
	 */
	public function add_user_custom_columns( $columns ) {
		if ( ! empty( $this->options['display_user_registration_date'] ) ) {
			$columns['registerdate'] = __( 'Registered', 'admin-optimizer' );
		}
		if ( ! empty( $this->options['enable_user_login_tracking'] ) ) {
			$columns['lastlogin'] = __( 'Last Login', 'admin-optimizer' );
		}

		return $columns;
	}
}
