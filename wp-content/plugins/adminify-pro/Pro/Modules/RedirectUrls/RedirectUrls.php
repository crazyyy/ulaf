<?php

namespace WPAdminify\Pro;

use WPAdminify\Inc\Classes\Helper;
use \WPAdminify\Inc\Admin\AdminSettings;
use \WPAdminify\Inc\Admin\AdminSettingsModel;

// no direct access allowed
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WPAdminify
 *
 * @package Redirect URLs
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

class RedirectUrls
{

	public $url;
	public $options;
	public $login_redirect_slug;
	public $redirect_admin_url;
	public $new_register_url;
	public $new_logout_url;
	public $old_login_page = false;

	public function __construct()
	{
		$this->options             = (array) AdminSettings::get_instance()->get('redirect_urls_fields');

		if( empty($this->options['enable_redirect_urls']) ) return;

		$this->options 			   = $this->options['redirect_urls_options']['redirect_urls_tabs'];
	

		$this->login_redirect_slug = !empty($this->options['new_login_url']) ? $this->options['new_login_url'] : '';
		$this->redirect_admin_url  = !empty($this->options['redirect_admin_url']) ? $this->options['redirect_admin_url'] : '';
		$this->new_register_url    = !empty($this->options['new_register_url']) ? $this->options['new_register_url'] : '';
		$this->new_logout_url      = !empty($this->options['new_logout_url']) ? $this->options['new_logout_url'] : '';

		// Defend wp-admin if custom login URL or redirect admin URL is set
		// Run immediately during constructor - before WordPress admin bootstrap
		if ($this->redirect_admin_url || $this->login_redirect_slug) {
			$this->defend_wp_admin();
		}

		if ($this->new_register_url) {
			add_filter('register_url', [$this, 'register_redirect_url']);
			add_action('wp_loaded', [$this, 'redirect_register_url']);
		}

		// Login Redirect
		add_filter('login_redirect', [$this, 'login_redirect_callback'], 999999999999, 3);

		// Logout Redirect
		// if (!empty($this->new_logout_url)) {
		add_filter('logout_redirect', [$this, 'redirect_logout_url'], 999999999999, 3);
		// }


		// Stop if custom login slug is not set.
		if (empty($this->login_redirect_slug)) {
			return;
		}
		add_action('init', [$this, 'change_url'], 99999);
		add_action('wp_loaded', [$this, 'defend_wp_login']);
		add_action('site_url', [$this, 'site_url'], 10, 4);
		add_action('network_site_url', [$this, 'network_site_url'], 10, 3);
		add_action('wp_redirect', [$this, 'wp_redirect'], 10, 2);
		// add_action('site_option_welcome_email', [$this, 'welcome_email']);
		// remove_action('template_redirect', 'wp_redirect_admin_locations', 99999);
	}

	/**
	 * Get Redirect URL
	 */

	public function get_redirect_url($login_redirects, $user, $default_url)
	{

		$user_caps = Helper::get_user_capabilities($user);
		$user_roles = Helper::get_user_roles($user);

		// $login_redirects = wp_list_sort($login_redirects, 'redirect_order', 'DESC');

		foreach ($login_redirects as $redirect_value) {

			$con_redirect_to = sanitize_url($redirect_value['redirect_url']);

			if (empty($con_redirect_to)) continue;

			$user_types = $redirect_value['user_types'];

			// Assign value for User Types
			if ($user_types === "user_role") $redirect_value_value = $redirect_value['redirect_role'];
			if ($user_types === 'user_name') $redirect_value_value = $redirect_value['redirect_user'];
			if ($user_types === 'user_cap') $redirect_value_value = $redirect_value['redirect_cap'];

			// Check Types of Users and Redirect
			if ($user_types == 'user_name' && ($user->ID == $redirect_value_value)) return $con_redirect_to;
			if ($user_types == 'user_role' && in_array($redirect_value_value, $user_roles)) return $con_redirect_to;
			if ($user_types == 'user_cap' && in_array($redirect_value_value, $user_caps)) return $con_redirect_to;
		}

		return $default_url;
	}


	/**
	 * Login Redirect
	 */
	public function login_redirect_callback($redirect_to, $requested_redirect_to, $user)
	{
		// Return $redirect_to when no user found
		if (!isset($user->user_login)) return $redirect_to;

		// Return $redirect_to when login condition found
		if (empty($this->options['login_redirects'])) return $redirect_to;

		// Return matched $redirect url if found
		$new_redirect_url = $this->get_redirect_url($this->options['login_redirects'], $user, $redirect_to);
		if (!empty($new_redirect_url)) {
			$redirect_to = $this->maybe_make_full_url($new_redirect_url);
		}

		Helper::allowed_host($redirect_to);
		return $redirect_to;
	}


	/**
	 * Logout Redirect
	 *
	 * @return void
	 */
	public function redirect_logout_url($redirect_to, $requested_redirect_to, $user)
	{
		if (!empty($this->new_logout_url)) {
			// Convert slug to full URL if it's not already a full URL
			$redirect_to = $this->maybe_make_full_url($this->new_logout_url);
		}

		// Return $redirect_to when no user found
		if (!isset($user->user_login)) return $redirect_to;

		// Return $redirect_to when logout condition found
		if (empty($this->options['logout_redirects'])) return $redirect_to;

		// Return matched $redirect url if found
		$matched_url = $this->get_redirect_url($this->options['logout_redirects'], $user, $redirect_to);
		if (!empty($matched_url)) {
			$redirect_to = $this->maybe_make_full_url($matched_url);
		}

		Helper::allowed_host($redirect_to);
		return $redirect_to;
	}

	/**
	 * Convert a slug or relative path to full URL if needed
	 * Handles subdirectory installations
	 *
	 * @param string $url_or_slug URL or slug
	 * @return string Full URL
	 */
	private function maybe_make_full_url($url_or_slug)
	{
		// If it's already a full URL, return as-is
		if (filter_var($url_or_slug, FILTER_VALIDATE_URL)) {
			return $url_or_slug;
		}

		// If it starts with http:// or https://, return as-is
		if (preg_match('/^https?:\/\//i', $url_or_slug)) {
			return $url_or_slug;
		}

		// Handle slugs with query strings (e.g., loadinads/?action=register)
		$url_or_slug = ltrim($url_or_slug, '/');

		// Check if slug contains query string
		if (strpos($url_or_slug, '?') !== false) {
			list($path, $query) = explode('?', $url_or_slug, 2);
			return site_url($path) . '?' . $query;
		}

		return site_url($url_or_slug);
	}

	/**
	 * register redirect url
	 *
	 * @return string
	 */
	public function redirect_register_url()
	{
		if ((isset($_GET['action']) && 'register' === $_GET['action']) || (isset($_GET['registration']) && 'disabled' === $_GET['registration'])) {
			$redirect_url = $this->maybe_make_full_url($this->new_register_url);
			// Only apply trailing slash to URLs without query strings
			if (get_option('permalink_structure') && !filter_var($this->new_register_url, FILTER_VALIDATE_URL)) {
				if (strpos($redirect_url, '?') === false) {
					$redirect_url = $this->is_trailingslashit($redirect_url);
				}
			}
			Helper::allowed_host($redirect_url);
			wp_safe_redirect($redirect_url);
			exit;
		}
	}
	/**
	 * wp-admin redirect url
	 *
	 * @return string
	 */
	public function register_redirect_url()
	{
		$redirect_url = $this->maybe_make_full_url($this->new_register_url);

		// Only apply trailing slash to URLs without query strings
		if (get_option('permalink_structure') && !filter_var($this->new_register_url, FILTER_VALIDATE_URL)) {
			// Don't add trailing slash if URL has query string
			if (strpos($redirect_url, '?') === false) {
				$redirect_url = $this->is_trailingslashit($redirect_url);
			}
		}

		return $redirect_url;
	}
	/**
	 * Change url.
	 */
	public function change_url()
	{
		if (!$this->login_redirect_slug) {
			return;
		}

		global $pagenow;

		$uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));

		$has_signup_slug   = false !== stripos($uri, 'wp-signup') ? true : false;
		$has_activate_slug = false !== stripos($uri, 'wp-activate') ? true : false;

		if (!is_multisite() && ($has_signup_slug || $has_activate_slug)) {
			return;
		}

		$request      = wp_parse_url($uri);
		$request_path = isset($request['path']) ? untrailingslashit($request['path']) : '';

		$using_permalink = get_option('permalink_structure') ? true : false;

		$has_new_slug = (isset($_GET[$this->login_redirect_slug]) && ('' != $_GET[$this->login_redirect_slug])) ? true : false;
		$has_old_slug = false !== stripos($uri, 'wp-login.php') ? true : false;

		$has_register_slug = false !== stripos($uri, 'wp-register.php') ? true : false;

		if (!is_admin() && ($has_old_slug || site_url('wp-login', 'relative') === $request_path)) {
			$pagenow                = 'index.php';
			$this->old_login_page   = true;
			$_SERVER['REQUEST_URI'] = $this->is_trailingslashit('/' . str_repeat('-/', 10));
		} elseif (site_url($this->login_redirect_slug, 'relative') === $request_path || (!$using_permalink && $has_new_slug)) {
			// If current page is new login page, let WordPress know if this is a login page.
			$pagenow = 'wp-login.php';
		} elseif (!is_admin() && ($has_register_slug || site_url('wp-register', 'relative') === $request_path)) {
			$pagenow = 'index.php';

			$this->old_login_page   = true;
			$_SERVER['REQUEST_URI'] = $this->is_trailingslashit('/' . str_repeat('-/', 10));
		}
	}

	/**
	 * Get WordPress base path (handles subdirectory installations)
	 *
	 * @return string
	 */
	private function get_wp_base_path()
	{
		$site_url = site_url();
		$parsed   = wp_parse_url($site_url);
		return isset($parsed['path']) ? rtrim($parsed['path'], '/') : '';
	}

	/**
	 * Defend wp-admin
	 */
	public function defend_wp_admin()
	{
		if (isset($_GET['action']) && 'postpass' === $_GET['action'] && isset($_GET['post_password'])) {
			return;
		}

		$request_uri  = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		$request      = wp_parse_url($request_uri);
		$request_path = isset($request['path']) ? $request['path'] : '';

		// Get WordPress base path for subdirectory installations
		// e.g., /testone for example.com/testone/
		$wp_base_path = $this->get_wp_base_path();

		// Build the expected wp-admin path for this installation
		$wp_admin_path = $wp_base_path . '/wp-admin';

		// Check if trying to access wp-admin for THIS WordPress installation
		$is_admin_request = (
			false !== stripos($request_path, $wp_admin_path) &&
			false === stripos($request_path, $wp_admin_path . '/admin-ajax.php') &&
			false === stripos($request_path, $wp_admin_path . '/admin-post.php') &&
			false === stripos($request_path, $wp_admin_path . '/options.php') &&
			false === stripos($request_path, $wp_admin_path . '/load-scripts.php') &&
			false === stripos($request_path, $wp_admin_path . '/load-styles.php')
		);

		if (!$is_admin_request) {
			return;
		}

		// Check if user is logged in (works early by checking cookies)
		$logged_in = false;
		if (function_exists('is_user_logged_in')) {
			$logged_in = is_user_logged_in();
		} else {
			// Fallback: check for logged-in cookie
			foreach ($_COOKIE as $cookie_name => $cookie_value) {
				if (strpos($cookie_name, 'wordpress_logged_in_') === 0) {
					$logged_in = true;
					break;
				}
			}
		}

		if (!$logged_in) {
			$redirect_url = $this->admin_redirect_url();
			Helper::allowed_host($redirect_url);
			wp_safe_redirect($redirect_url);
			exit;
		}
	}

	/**
	 * wp-admin redirect url
	 *
	 * @return string
	 */
	public function admin_redirect_url()
	{
		// Use configured redirect URL or fallback to 404 (default placeholder)
		$redirect_url = !empty($this->redirect_admin_url) ? $this->redirect_admin_url : '404';

		// Use maybe_make_full_url to handle both slugs and full URLs
		return $this->maybe_make_full_url($redirect_url);
	}

	/**
	 * Return a string with or without trailing slash based on permalink structure.
	 */
	public function is_trailingslashit($string)
	{
		$use_trailingslash = '/' === substr(get_option('permalink_structure'), -1, 1) ? true : false;
		return ($use_trailingslash ? trailingslashit($string) : untrailingslashit($string));
	}

	/**
	 * Defend wp-login.php based on the setting.
	 */
	public function defend_wp_login()
	{
		if (isset($_GET['action']) && 'postpass' === $_GET['action'] && isset($_GET['post_password'])) {
			return;
		}

		global $pagenow;

		$request      = wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
		$request_path = isset($request['path']) ? $request['path'] : '';

		$query_string     = isset($_SERVER['QUERY_STRING']) ? esc_url_raw(wp_unslash($_SERVER['QUERY_STRING'])) : '';
		$add_query_string = $query_string ? '?' . $query_string : '';

		// Get the expected login URL path for this installation (handles subdirectory)
		$expected_login_path = site_url($this->login_redirect_slug, 'relative');

		if ('wp-login.php' === $pagenow && $request_path !== $this->is_trailingslashit($request_path) && get_option('permalink_structure')) {
			wp_safe_redirect($this->is_trailingslashit($this->new_login_url()) . $add_query_string);
			exit;
		} elseif ($this->old_login_page) {
			$referer  = wp_get_referer();
			$referers = wp_parse_url($referer);

			$referer_contains_activate_url = false !== stripos($referer, 'wp-activate.php') ? true : false;

			if ($referer_contains_activate_url && !empty($referers['query'])) {
				parse_str($referers['query'], $referer_queries);

				$signup_key           = $referer_queries['key'];
				$wpmu_activate_signup = wpmu_activate_signup($signup_key);

				@require_once WPINC . '/ms-functions.php';

				if (!empty($signup_key) && is_wp_error($wpmu_activate_signup)) {
					if ('already_active' === $wpmu_activate_signup->get_error_code() || 'blog_taken' === $wpmu_activate_signup->get_error_code()) {
						wp_safe_redirect($this->new_login_url() . $add_query_string);
						exit;
					}
				}
			}

			$this->wp_template_loader();
		} elseif ('wp-login.php' === $pagenow) {
			$redirect_to           = admin_url();
			$requested_redirect_to = '';

			if (isset($_REQUEST['redirect_to'])) {
				$requested_redirect_to = esc_url_raw(wp_unslash($_REQUEST['redirect_to']));
			}

			if (is_user_logged_in()) {
				$user = wp_get_current_user();

				if (!isset($_REQUEST['action'])) {
					wp_safe_redirect($redirect_to);
					exit;
				}
			}

			// Prevent warnings in wp-login.php file by providing these globals.
			global $user_login, $error, $iterim_login, $action;

			@require_once ABSPATH . 'wp-login.php';
			exit;
		}
	}


	/**
	 * Filter site_url.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/site_url/
	 */
	public function site_url($url, $path, $scheme, $blog_id)
	{
		return $this->filter_old_login_page($url, $scheme);
	}


	/**
	 * Filter old login page.
	 *
	 * @param string $scheme Scheme to give the site URL context. Accepts 'http', 'https', 'login', 'login_post', 'admin', 'relative' or null.
	 */
	public function filter_old_login_page($url, $scheme = null)
	{
		if (false !== stripos($url, 'wp-login.php?action=postpass')) {
			return $url;
		}

		// Skip register URLs - handled by register_url filter
		if ($this->new_register_url && false !== stripos($url, 'action=register')) {
			return $url;
		}

		$url_contains_old_login_url     = false !== stripos($url, 'wp-login.php') ? true : false;
		$referer_contains_old_login_url = false !== stripos(wp_get_referer(), 'wp-login.php') ? true : false;

		if ($url_contains_old_login_url && !$referer_contains_old_login_url) {
			if (is_ssl()) {
				$scheme = 'https';
			}

			$url_parts = explode('?', $url);

			if (isset($url_parts[1])) {
				parse_str($url_parts[1], $args);

				if (isset($args['login'])) {
					$args['login'] = rawurlencode($args['login']);
				}

				$url = add_query_arg($args, $this->new_login_url($scheme));
			} else {
				$url = $this->new_login_url($scheme);
			}
		}

		return $url;
	}

	/**
	 * Filter network_site_url.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/network_site_url/
	 */
	public function network_site_url($url, $path, $scheme)
	{
		return $this->filter_old_login_page($url, $scheme);
	}

	/**
	 * Filter wp_redirect.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/wp_redirect/
	 */
	public function wp_redirect($location, $status)
	{
		return $this->filter_old_login_page($location);
	}

	/**
	 * WordPress template loader.
	 *
	 * @return void
	 */
	public function wp_template_loader()
	{
		global $pagenow;

		$pagenow = 'index.php';

		if (!defined('WP_USE_THEMES')) {
			define('WP_USE_THEMES', true);
		}

		wp();

		require_once ABSPATH . WPINC . '/template-loader.php';

		exit;
	}

	/**
	 * Get new login url.
	 *
	 * @param string|null $scheme Scheme to give the site URL context. Accepts 'http', 'https', 'login', 'login_post', 'admin', 'relative' or null.
	 */
	public function new_login_url($scheme = null)
	{
		$login_url = site_url($this->login_redirect_slug, $scheme);

		if (get_option('permalink_structure')) {
			return $this->is_trailingslashit($login_url);
		} else {
			return home_url('/', $scheme) . '?' . $this->login_redirect_slug;
		}

		return $login_url;
	}
}
