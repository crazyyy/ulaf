<?php
if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Localizes Google Analytics.
 */
if (!class_exists('WP_Optimize_Minify_Analytics')) :

class WP_Optimize_Minify_Analytics {

	/**
	 * Directory to the stored Gtag script
	 *
	 * @var string
	 */
	const WPO_CACHE_GTAG_DIR = WP_CONTENT_DIR . '/cache/gtag';

	/**
	 * URL to the stored Gtag script
	 *
	 * @var string
	 */
	const WPO_CACHE_GTAG_URL = WP_CONTENT_URL . '/cache/gtag';

	/**
	 * Transient key for Gtag URL
	 *
	 * @var string
	 */
	const GTAG_URL_TRANSIENT = 'wpo_gtag_url';

	/**
	 * Analytics ID
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Analytics Method
	 *
	 * @var string
	 */
	private $method;

	/**
	 * Is hosting local analytics script enabled
	 *
	 * @var bool
	 */
	private $is_enabled;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$config = wp_optimize_minify_config()->get();
		$this->id = $config['tracking_id'];
		$this->method = $config['analytics_method'];
		$this->is_enabled = $config['enable_analytics'];

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_print_footer_scripts', array($this, 'inject_analytics_js'));
		global $wp_version;
		if (version_compare($wp_version, '6.3', '<')) {
			add_filter( 'script_loader_tag', array($this, 'loading_strategy_fallback'), 10, 2);
		}
	}

	/**
	 * Singleton instance
	 *
	 * @return WP_Optimize_Minify_Analytics
	 */
	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Enqueue analytics script
	 */
	public function enqueue_scripts() {
		$enqueue_version = WP_Optimize()->get_enqueue_version();
		$file_name = 'analytics-' . $this->id . '.min.js';
		$local_gtag_path = wp_normalize_path(self::WPO_CACHE_GTAG_DIR . '/' . $file_name);
		$local_gtag_url = self::WPO_CACHE_GTAG_URL . '/' . $file_name;

		if ('gtagv4' === $this->method) {
			$remote_gtag_url = sprintf('https://www.googletagmanager.com/gtag/js?id=%s', $this->id);
			$local_gtag_present = file_exists($local_gtag_path);

			if (!$local_gtag_present && !$this->should_skip_gtag_download($remote_gtag_url)) {
				// If the local gtag file is missing, check if a download was already attempted in the last 24h. If not, try downloading again and record the result in a transient. This is to avoid `wp_remote_get()` call repeatedly in case of download failure.

				$local_gtag_present = $this->download_analytics_script($remote_gtag_url, $local_gtag_path);
				if (!$local_gtag_present) {
					set_transient(self::GTAG_URL_TRANSIENT, $remote_gtag_url, DAY_IN_SECONDS);
				}
			}

			$script_source = $local_gtag_present ? $local_gtag_url : $remote_gtag_url;
			wp_enqueue_script($this->method, $script_source, array(), $enqueue_version);
		} elseif ('minimal-analytics' === $this->method) {
			wp_enqueue_script($this->method, WPO_PLUGIN_URL . 'js/minimal-analytics/minimal-analytics.min.js', array(), $enqueue_version);
		}
		wp_script_add_data($this->method, 'strategy', 'defer');
	}

	/**
	 * Injects corresponding JS to footer.
	 */
	public function inject_analytics_js() {
		if ('gtagv4' === $this->method) {
			echo "<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '".esc_attr($this->id)."');</script>";
		} elseif ('minimal-analytics' === $this->method) {
			echo "<script>window.minimalAnalytics = { trackingId: '".esc_attr($this->id)."', autoTrack: true, };</script>";
		}
	}

	/**
	 * Fallback to add script loading strategy attribute to enqueued scripts
	 *
	 * @param string $tag    The `<script>` tag for the enqueued script
	 * @param string $handle Handle of the enqueued script
	 *
	 * @return string URL of the enqueued script with defer loading strategy
	 */
	public function loading_strategy_fallback($tag, $handle) {
		if (!in_array($handle, array('gtagv4', 'minimal-analytics'))) return $tag;

		return str_replace(' src=', ' defer src=', $tag);
	}

	/**
	 * Download gtag script for specific analytics id
	 *
	 * @param string $url       Gtag script download url for specific id
	 * @param string $file_path Path to gtag/analytics-***.js file
	 * @return bool return true if download and cache was successful otherwise false
	 */
	private function download_analytics_script($url, $file_path) {
		$base_google_url = 'https://www.googletagmanager.com/gtag/js';
		if (0 !== strpos($url, $base_google_url)) return false;

		// Ensure gtag directory is present
		if (!wp_mkdir_p(dirname($file_path))) return false;

		// Download and cache gtag script
		$response = wp_remote_get($url);
		if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) return false;

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) return false;
		
		return false !== file_put_contents($file_path, $body);
	}

	/**
	 * Determine whether the Gtag script download should be skipped
	 *
	 * @param string $url Remote Gtag URL
	 * @return bool
	 */
	private function should_skip_gtag_download($url) {
		return get_transient(self::GTAG_URL_TRANSIENT) === $url;
	}
}

endif;
