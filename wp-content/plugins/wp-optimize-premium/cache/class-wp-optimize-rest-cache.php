<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WP_Optimize_REST_Cache')) :

/**
 * Class WP_Optimize_REST_Cache
 */
class WP_Optimize_REST_Cache {

	/**
	 * Logger for this class
	 *
	 * @var Updraft_PHP_Logger
	 */
	private $logger;
	
	/**
	 * Headers to be cached for the current request
	 *
	 * @var array
	 */
	private $headers_to_cache;

	/**
	 * Result status
	 *
	 * @var int
	 */
	private $result_status;
	
	/**
	 * WP_Optimize_REST_Cache constructor.
	 */
	private function __construct() {
		$this->logger = new Updraft_PHP_Logger();
		$this->register_purge_cache_hooks();
		$this->enable();
	}
	
	/**
	 * Enable the REST cache.
	 *
	 * @return void
	 */
	private function enable() {
		// Don't cache for logged in users
		if (is_user_logged_in()) return;
	
		add_filter('rest_pre_serve_request', array($this, 'store_headers_to_cache'), PHP_INT_MAX, 2);
		add_filter('rest_pre_echo_response', array($this, 'cache_rest_response'), PHP_INT_MAX, 3);
	}
	
	/**
	 * Used with the rest_pre_serve_request filter to store headers for caching
	 *
	 * @param bool 			   $served Whether the request has already been served.
	 *								   Default false.
	 * @param WP_HTTP_Response $result Result to send to the client. Usually a `WP_REST_Response`.
	 * @return bool
	 */
	public function store_headers_to_cache($served, $result) {
		$headers = $result->get_headers();
	
		if (!empty($headers)) {
			$this->headers_to_cache = $this->get_headers_to_cache($headers);
		}

		// save result status code to use later
		$this->result_status = $result->get_status();
	
		return $served;
	}
	
	/**
	 * Handles REST requests and, if it is a GET request, stores the result in the cache.
	 *
	 * @param array           $result  Response data to send to the client
	 * @param WP_REST_Server  $server  Server instance
	 * @param WP_REST_Request $request Request used to generate the response
	 * @return array
	 */
	public function cache_rest_response($result, $server, $request) {
		// Don’t cache _pretty requests as they are typically not used in production.
		$cache_pretty = apply_filters('wpo_rest_cache_pretty', false);
		if (false === $cache_pretty && $this->is_pretty_request($request)) {
			return $result;
		}
	
		// Invalid status, don't cache
		if (!$this->is_200_ok_status()) return $result;
	
		if (!$this->can_cache_the_rest_request($request)) return $result;
	
		$options = 0;
	
		if ($this->is_pretty_request($request)) {
			$options |= JSON_PRETTY_PRINT;
		}
	
		/**
		 * Filter to modify JSON encoding options before the response is rendered in WP_REST_Server::serve_request().
		 */
		$options = apply_filters('rest_json_encode_options', $options, $request);
		$result_json = $this->encode_result_json($result, $options);
	
		if (false === $result_json) return $result;
	
		$relative_path = $this->get_cache_relative_path($request->get_route());
		$filename = wpo_rest_cache_filename($request->get_query_params());
		$path = WPO_CACHE_FILES_DIR . '/' . $relative_path;
		$path_filename = preg_replace('/\/+/', '/', $path.'/'.$filename);

		$this->make_cache_dirs($relative_path);
		
		// save cache data to cache
		if (false === @file_put_contents($path_filename, $result_json)) { // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Silenced to suppress errors when the directory is not writable.
			$this->log("Failed to write REST cache to {$path_filename}");
		} else {

			if (!headers_sent()) {
				header('WPO-Cache-Status: saving to cache');
			}

			// if we can then cache gzipped content in .gz file.
			if (function_exists('gzencode') && apply_filters('wpo_allow_cache_gzip_files', true)) {
				if (false === @file_put_contents($path_filename . '.gz', gzencode($result_json, apply_filters('wpo_cache_gzip_level', 6)))) { // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Silenced to suppress errors when the directory is not writable.
					$this->log("Failed to write REST cache (gzip) to {$path_filename}.gz");
				}
			}
		}
	
		$this->maybe_cache_headers($path, $filename);

		$this->delete_cache_size_information();
	
		return $result;
	}
	
	/**
	 * Checks whether request has param pretty.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	private function is_pretty_request($request) {
		return $request->has_param('_pretty');
	}
	
	/**
	 * Checks whether the result status is 200.
	 *
	 * @return bool
	 */
	private function is_200_ok_status() {
		return 200 === $this->result_status;
	}
	
	/**
	 * Checks if we can cache current REST request
	 *
	 * @param WP_REST_Request $request
	 * @return boolean
	 */
	private function can_cache_the_rest_request($request) {
		// Cache only GET requests and not authenticated requests
		return 'GET' === $request->get_method() && is_null($request->get_header('x_wp_nonce'));
	}
	
	/**
	 * Cache response headers when they are not empty
	 *
	 * @param string $path
	 * @param string $filename
	 * @return void
	 */
	private function maybe_cache_headers($path, $filename) {
		if (!empty($this->headers_to_cache)) {
			$headers_filename = $filename . '.headers';
			$headers_to_cache = wp_json_encode($this->headers_to_cache);
	
			if (false === @file_put_contents($path . '/'. $headers_filename, $headers_to_cache)) { // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Silenced to suppress errors when the directory is not writable.
				$this->log("Failed to write REST cache headers to {$path}/{$headers_filename}");
			}
		}
	}
	
	/**
	 * Purges the REST cache.
	 *
	 * @return bool
	 */
	public function purge_cache() {
		$cache_dir = WPO_CACHE_FILES_DIR . $this->get_cache_relative_path('/');
		$this->delete_cache_size_information();
		return wpo_delete_files($cache_dir);
	}
	
	/**
	 * Encodes the provided result into a JSON string.
	 *
	 * @param array $result  The data to be encoded into JSON format.
	 * @param int $options
	 * @return string|false
	 */
	private function encode_result_json($result, $options = 0) {
		$result_json = wp_json_encode($result, $options);
		return (JSON_ERROR_NONE === json_last_error()) ? $result_json : false;
	}
	
	/**
	 * Returns singleton instance object
	 *
	 * @return WP_Optimize_REST_Cache
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}
	
	/**
	 * Registers REST cache purge callback to a list of hooks.
	 *
	 * @return void
	 */
	private function register_purge_cache_hooks() {
		$hooks = $this->get_purge_cache_hooks();
		foreach ($hooks as $hook) {
			add_action($hook, array($this, 'purge_cache'));
		}
	}
	
	/**
	 * Retrieves a list of hooks that need to be registered to purge the REST cache.
	 *
	 * @return array
	 */
	private function get_purge_cache_hooks() {
		$hooks = array(
			'save_post',
			'delete_post',
			'wp_insert_post',
			'edit_post',
			'trashed_post',
			'untrashed_post',
	
			'profile_update',
			'user_register',
			'deleted_user',
			'add_user_meta',
			'update_user_meta',
			'delete_user_meta',
	
			'created_term',
			'edited_term',
			'delete_term',
			'set_object_terms',
	
			'add_attachment',
			'edit_attachment',
			'delete_attachment',
	
			'comment_post',
			'edit_comment',
			'deleted_comment',
			'trashed_comment',
			'untrashed_comment',
		);
	
		return apply_filters('wpo_purge_rest_cache_hooks', $hooks);
	}
	
	/**
	 * Retrieves an array of headers and returns only those that should be cached.
	 *
	 * @param array $headers Headers
	 *
	 * @return array
	 */
	private function get_headers_to_cache($headers) {
		$header_names_to_cache = apply_filters('wpo_rest_headers_names_to_cache', array(
			'X-WP-Total',
			'X-WP-TotalPages',
			'Link',
		));
		
		return array_intersect_key($headers, array_flip($header_names_to_cache));
	}
	
	/**
	 * Returns the relative path to the cache for the specified route.
	 *
	 * @param string $route
	 * @return string
	 */
	private function get_cache_relative_path($route) {
		$url = rest_url(ltrim($route, '/'));
		$parsed = wp_parse_url($url);
		$relative_path = (isset($parsed['host']) ? '/'.$parsed['host'] : '') . ($parsed['path'] ?? '');
		return $relative_path;
	}
	
	/**
	 * Creates any necessary subdirectories for storing cached files.
	 *
	 * @param string $relative_path
	 * @return void
	 */
	private function make_cache_dirs($relative_path) {
		$dirs = explode('/', $relative_path);
	
		$path = WPO_CACHE_FILES_DIR;
	
		foreach ($dirs as $dir) {
			if (!empty($dir)) {
				$path .= '/' . $dir;
	
				if (!file_exists($path)) {
					if (!wp_mkdir_p($path)) {
						break;
					}
				}
			}
		}
	}

	/**
	 * Delete the cached information about the cache size.
	 *
	 * @return void
	 */
	private function delete_cache_size_information() {
		WP_Optimize()->get_page_cache()->delete_cache_size_information();
	}
	
	/**
	 * Logs error messages
	 *
	 * @param  string $message
	 * @return void
	 */
	private function log($message) {
		if (isset($this->logger)) {
			$this->logger->log($message, 'error');
		}
	}
}
endif;
