<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom_Login_Url class
 */
class Custom_Login_Url {

	const OPTION_NAME = 'adminoptim_custom_login';

	/**
	 * User options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings Class
	 *
	 * @var Custom_Login_Url_Settings
	 */
	protected $settings;

	/**
	 * Check permalink if have trailing slash
	 *
	 * @var bool
	 */
	private $has_trailing_slash = false;

	/**
	 * Placeholder for wp-login parameter
	 *
	 * @var bool
	 */
	private $wp_login_php;

	const MENU_SLUG = 'adminoptimizer-custom-login-url';

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
		$this->settings = new Custom_Login_Url_Settings( $this->options );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		if ( ! empty( $this->options['custom_slug'] ) && ! empty( $this->options['redirection_slug'] ) ) {
			$this->has_trailing_slash = $this->check_permalink_trailing_slash();
			$this->change_login_url();
			add_action( 'wp_loaded', [ $this, 'maybe_redirect_load_url' ] );
			add_filter( 'site_url', [ $this, 'site_url' ], 10, 4 );
			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
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
			__( 'Custom Login URL', 'admin-optimizer' ),
			__( 'Custom Login URL', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Change login url
	 *
	 * @return void
	 */
	public function change_login_url() {
		global $pagenow;

		if ( ! is_multisite() && isset( $_SERVER['REQUEST_URI'] )
			&& ( strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-signup' ) !== false
				|| strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-activate' ) !== false ) ) {

			wp_die( esc_html__( 'This feature is not enabled.', 'admin-optimizer' ) );

		}

		$request = wp_parse_url( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );

		if ( ( strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-login.php' ) !== false
				|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
			&& ! is_admin() ) {

			$this->wp_login_php = true;
			add_filter( 'adminoptim_is_login_page', '__return_true' );

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		} elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->options['custom_slug'], 'relative' ) )
			|| ( ! get_option( 'permalink_structure' )
				&& isset( $_GET[ $this->options['custom_slug'] ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				&& empty( $_GET[ $this->options['custom_slug'] ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$pagenow = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			add_filter( 'adminoptim_is_login_page', '__return_true' );

		} elseif ( ( strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-register.php' ) !== false
				|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
			&& ! is_admin() ) {

			add_filter( 'adminoptim_is_login_page', '__return_true' );
			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	/**
	 * Redirect to user defined url
	 *
	 * @return void
	 */
	public function maybe_redirect_load_url() {
		global $pagenow;

		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {
			wp_safe_redirect( $this->get_url( 'redirect' ) );
			die();
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : '';

		if ( 'wp-login.php' === $pagenow && $request['path'] !== $this->user_trailingslashit( $request['path'] ) && get_option( 'permalink_structure' ) ) {
			wp_safe_redirect( $this->user_trailingslashit( $this->get_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : '' ) );
			die;
		} elseif ( $this->wp_login_php ) {
			$referer        = wp_get_referer();
			$referred_query = wp_parse_url( $referer );
			if ( strpos( $referer, 'wp-activate.php' ) !== false && ! empty( $referred_query['query'] ) ) {
				parse_str( $referred_query['query'], $referer );
				if ( ! empty( $referer['key'] ) ) {
					$result = wpmu_activate_signup( $referer['key'] );
					if ( is_wp_error( $result ) && ( 'already_active' === $result->get_error_code() || 'blog_taken' === $result->get_error_code() ) ) {
						wp_safe_redirect( $this->get_url() . ( ! empty( wp_unslash( $_SERVER['QUERY_STRING'] ) ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : '' ) );
						die;
					}
				}
			}

			$this->wp_template_loader();
		} elseif ( 'wp-login.php' === $pagenow ) {
			global $error, $interim_login, $action, $user_login;

			@require_once ABSPATH . 'wp-login.php';  //phpcs:ignore

			die;
		}
	}

	/**
	 * Template loader for login page
	 *
	 * @return void
	 */
	private function wp_template_loader() {
		global $pagenow;

		$pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		wp();

		require_once ABSPATH . WPINC . '/template-loader.php';
		die;
	}

	/**
	 * Retrieve user defined login url
	 *
	 * @param string      $login_or_redirect  Login or Redirect.
	 * @param string|null $scheme http or https.
	 *
	 * @return string
	 */
	private function get_url( $login_or_redirect = 'login', $scheme = null ) {
		$slug                    = ( 'redirect' === $login_or_redirect ) ? $this->options['redirection_slug'] : $this->options['custom_slug'];
		$has_permalink_structure = get_option( 'permalink_structure' );

		if ( $has_permalink_structure ) {
			$url = home_url( '/', $scheme ) . $slug;
			if ( $this->has_trailing_slash ) {
				$url = trailingslashit( $url );
			}
		} else {
			$url = home_url( '/', $scheme ) . '?' . $slug;
		}

		return $url;
	}

	/**
	 * Check permalink trailing slash
	 *
	 * @return bool
	 */
	private function check_permalink_trailing_slash() {
		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );
	}

	/**
	 * Check user trailing slash
	 *
	 * @param string $text  String to check.
	 *
	 * @return string
	 */
	private function user_trailingslashit( $text ) {
		return $this->has_trailing_slash ? trailingslashit( $text ) : untrailingslashit( $text );
	}

	/**
	 * Update login url
	 *
	 * @param string      $login_url  Login url.
	 * @param string|null $redirect  Redirect url.
	 * @param bool        $force_reauth  Force reauthentication.
	 *
	 * @return string|null
	 */
	public function update_login_url( $login_url, $redirect, $force_reauth ) {
		if ( is_404() ) {
			return '#';
		}

		if ( false === $force_reauth ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( admin_url( 'options.php' ) === $redirect[0] ) {
			$login_url = admin_url();
		}

		return $login_url;
	}

	/**
	 * Redirection
	 *
	 * @param string $location Location to redirect to.
	 * @param string $status  Status - not used.
	 *
	 * @return string
	 */
	public function wp_redirect( $location, $status ) {
		if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
			return $location;
		}

		return $this->filter_wp_login_php( $location );
	}

	/**
	 * Filter wp_login.php
	 *
	 * @param string $url  Url to filter.
	 * @param string $scheme http or https.
	 *
	 * @return string
	 */
	public function filter_wp_login_php( $url, $scheme = null ) {
		if ( strpos( $url, 'wp-login.php' ) !== false ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				$url = add_query_arg( $args, $this->get_url( 'login', $scheme ) );
			} else {
				$url = $this->get_url( 'login', $scheme );
			}
		}

		return $url;
	}

	/**
	 * Get the login url
	 *
	 * @param string $login_url  Login url.
	 * @param string $redirect  Redirect url.
	 * @param bool   $force_reauth Force reauthentication.
	 *
	 * @return string|null
	 */
	public function login_url( $login_url, $redirect, $force_reauth ) {
		if ( is_404() ) {
			return '#';
		}

		if ( false === $force_reauth ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( admin_url( 'options.php' ) === $redirect[0] ) {
			$login_url = admin_url();
		}

		return $login_url;
	}

	/**
	 * Site Url
	 *
	 * @param string $url  Site url.
	 * @param string $path Site path.
	 * @param string $scheme  http or https.
	 * @param int    $blog_id  Blog id.
	 *
	 * @return mixed|string
	 */
	public function site_url( $url, $path, $scheme, $blog_id ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}
}
