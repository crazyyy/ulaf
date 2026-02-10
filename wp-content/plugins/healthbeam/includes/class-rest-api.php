<?php
namespace HealthBeam;

class REST_API
{
	public function __construct()
	{
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes()
	{
		$namespace = 'healthbeam/v1';

		register_rest_route(
			$namespace,
			'/debug-log',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_debug_log'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/file-integrity',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'check_file_integrity'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/view-file-diff',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'view_file_diff'),
				'permission_callback' => array($this, 'check_permission'),
				'args' => array(
					'file' => array(
						'required' => true,
						'type' => 'string',
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/mail-check',
			array(
				'methods' => 'POST',
				'callback' => array($this, 'send_test_email'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/plugin-compat',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'check_plugin_compatibility'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/htaccess',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_htaccess'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/phpinfo',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_phpinfo'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/robotstxt',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_robotstxt'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		register_rest_route(
			$namespace,
			'/transients',
			array(
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_transients'),
					'permission_callback' => array($this, 'check_permission'),
				),
				array(
					'methods' => 'DELETE',
					'callback' => array($this, 'clear_transients'),
					'permission_callback' => array($this, 'check_permission'),
				),
			)
		);

		// Database Optimizer
		register_rest_route(
			$namespace,
			'/database',
			array(
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_database_overhead'),
					'permission_callback' => array($this, 'check_permission'),
				),
				array(
					'methods' => 'POST',
					'callback' => array($this, 'optimize_database'),
					'permission_callback' => array($this, 'check_permission'),
				),
			)
		);

		// Cron Manager
		register_rest_route(
			$namespace,
			'/cron',
			array(
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_cron_jobs'),
					'permission_callback' => array($this, 'check_permission'),
				),
				array(
					'methods' => 'POST',
					'callback' => array($this, 'run_cron_job'),
					'permission_callback' => array($this, 'check_permission'),
				),
				array(
					'methods' => 'DELETE',
					'callback' => array($this, 'delete_cron_job'),
					'permission_callback' => array($this, 'check_permission'),
				),
			)
		);

		// Capability Checker
		register_rest_route(
			$namespace,
			'/capabilities',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_capabilities'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);

		// Server Resources
		register_rest_route(
			$namespace,
			'/server-resources',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_server_resources'),
				'permission_callback' => array($this, 'check_permission'),
			)
		);
	}

	public function check_permission()
	{
		return current_user_can('view_site_health_checks');
	}

	public function get_debug_log()
	{
		if (!defined('WP_DEBUG_LOG') || false === WP_DEBUG_LOG) {
			return new \WP_Error('no_debug_log', __('WP_DEBUG_LOG is not enabled.', 'healthbeam'), array('status' => 404));
		}

		$logfile = WP_DEBUG_LOG;
		if (is_bool($logfile)) {
			$logfile = WP_CONTENT_DIR . '/debug.log';
		}

		if (!file_exists($logfile)) {
			/* translators: %s: Path to the debug log file */
			return new \WP_Error('no_debug_log_file', sprintf(__('Debug log file not found at %s.', 'healthbeam'), $logfile), array('status' => 404));
		}

		$content = file_get_contents($logfile);
		return rest_ensure_response(array('content' => $content));
	}

	public function check_file_integrity()
	{
		// Simplified implementation for now, reusing logic from original plugin would be ideal but complex to copy-paste entirely.
		// For this demo, we'll return a mock response or a simple check.
		// Real implementation should ideally reuse the logic from the original plugin class if possible, or reimplement it.
		// Given the constraints, I'll implement a basic check.

		if (!function_exists('get_core_checksums')) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$wpversion = \get_bloginfo('version');
		$wplocale = \get_locale();

		// Setup API Call.
		$checksums = \get_core_checksums($wpversion, $wplocale);

		if (false === $checksums) {
			/* translators: 1: WordPress version, 2: Locale */
			return new \WP_Error('checksum_api_error', sprintf(__('Could not retrieve checksums for version %1$s and locale %2$s.', 'healthbeam'), $wpversion, $wplocale), array('status' => 500));
		}

		set_transient('healthbeam_checksums', $checksums, 2 * HOUR_IN_SECONDS);
		$files = array();
		foreach ($checksums as $file => $checksum) {
			if (false !== strpos($file, 'wp-content/')) {
				continue;
			}
			if (file_exists(ABSPATH . $file) && md5_file(ABSPATH . $file) !== $checksum) {
				$reason = __('Content changed', 'healthbeam') . ' <a href="#site-health-diff" data-file="' . esc_attr($file) . '" class="view-diff-link">' . __('(View Diff)', 'healthbeam') . '</a>';
				$files[] = array(
					'file' => ABSPATH . $file,
					'status' => 'error',
					'reason' => $reason,
				);
			} elseif (!file_exists(ABSPATH . $file)) {
				$files[] = array(
					'file' => ABSPATH . $file,
					'status' => 'error',
					'reason' => __('File not found', 'healthbeam'),
				);
			}
		}

		// Iterate over the core directories to see if any unexpected files exist.
		if (class_exists('RecursiveDirectoryIterator')) {
			$directories = array(
				untrailingslashit(ABSPATH),            // Root directory.
				untrailingslashit(ABSPATH . 'wp-admin'),    // Admin directory.
				untrailingslashit(ABSPATH . WPINC), // Includes directory.
			);

			$excluded_files = array(
				'.htaccess',
				'wp-config.php',
				'local-xdebuginfo.php',
			);

			foreach ($directories as $directory) {
				if (!file_exists($directory)) {
					continue;
				}

				if (untrailingslashit(ABSPATH) === $directory) {
					$iterator = new \DirectoryIterator($directory);
					foreach ($iterator as $file) {
						if ($file->isFile()) {
							$file_path = wp_normalize_path($file->getPathname());
							$root_path = wp_normalize_path(ABSPATH);
							$path = str_replace($root_path, '', $file_path);
							$path = ltrim($path, '/');

							if (!isset($checksums[$path]) && !in_array($path, $excluded_files, true)) {
								$files[] = array(
									'file' => $file_path,
									'status' => 'warning',
									'reason' => __('This is an unknown file', 'healthbeam'),
								);
							}
						}
					}
				} else {
					$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
					foreach ($iterator as $file) {
						if ($file->isFile()) {
							$file_path = wp_normalize_path($file->getPathname());
							$root_path = wp_normalize_path(ABSPATH);
							$path = str_replace($root_path, '', $file_path);
							$path = ltrim($path, '/');

							if (!isset($checksums[$path]) && !in_array($path, $excluded_files, true)) {
								$files[] = array(
									'file' => $file_path,
									'status' => 'warning',
									'reason' => __('This is an unknown file', 'healthbeam'),
								);
							}
						}
					}
				}
			}
		}

		return rest_ensure_response(array('files' => $files));
	}

	public function send_test_email($request)
	{
		$email = sanitize_email($request->get_param('email'));
		$message = sanitize_text_field($request->get_param('message'));

		if (!is_email($email)) {
			return new \WP_Error('invalid_email', __('Invalid email address.', 'healthbeam'), array('status' => 400));
		}

		/* translators: %s: Site name */
		$subject = sprintf(__('Test Message from %s', 'healthbeam'), get_bloginfo('name'));
		/* translators: %s: Site URL */
		$body = sprintf(__('This is a test message sent from %s.', 'healthbeam'), get_bloginfo('url'));
		if ($message) {
			$body .= "\n\n" . $message;
		}

		$result = wp_mail($email, $subject, $body);

		if ($result) {
			return rest_ensure_response(array('success' => true, 'message' => __('Email sent successfully.', 'healthbeam')));
		} else {
			return new \WP_Error('mail_failed', __('Failed to send email.', 'healthbeam'), array('status' => 500));
		}
	}

	public function check_plugin_compatibility()
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		$data = array();

		foreach ($plugins as $file => $plugin) {
			$data[] = array(
				'name' => $plugin['Name'],
				'version' => $plugin['Version'],
				'requires_php' => isset($plugin['RequiresPHP']) ? $plugin['RequiresPHP'] : null,
				'file' => $file,
			);
		}

		return rest_ensure_response(array('plugins' => $data));
	}

	public function get_htaccess()
	{
		if (file_exists(ABSPATH . '.htaccess')) {
			return rest_ensure_response(array('content' => file_get_contents(ABSPATH . '.htaccess')));
		}
		return new \WP_Error('no_htaccess', __('.htaccess file not found.', 'healthbeam'), array('status' => 404));
	}

	public function get_phpinfo()
	{
		if (!function_exists('phpinfo')) {
			return new \WP_Error('phpinfo_disabled', __('phpinfo() is disabled.', 'healthbeam'), array('status' => 403));
		}
		ob_start();
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_phpinfo
		phpinfo();
		$content = ob_get_clean();

		return rest_ensure_response(array('content' => $content));
	}

	public function get_robotstxt()
	{
		if (file_exists(ABSPATH . 'robots.txt')) {
			return rest_ensure_response(array('content' => file_get_contents(ABSPATH . 'robots.txt')));
		}

		$response = wp_remote_get(home_url('robots.txt'));
		if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
			return rest_ensure_response(array('content' => wp_remote_retrieve_body($response)));
		}

		return new \WP_Error('no_robotstxt', __('robots.txt file not found.', 'healthbeam'), array('status' => 404));
	}

	public function get_transients()
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$transients = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE ( option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%' ) AND option_name NOT LIKE '%_transient_timeout_%'");

		return rest_ensure_response(array(
			'count' => count($transients),
			'size' => size_format(strlen(serialize($transients))),
		));
	}

	public function clear_transients()
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%'");
		wp_cache_flush();
		return rest_ensure_response(array('success' => true, 'message' => __('Transients cleared.', 'healthbeam')));
	}

	public function view_file_diff($request)
	{
		$file = $request->get_param('file');

		// Security check: validate file path
		if (0 !== validate_file($file)) {
			return new \WP_Error('invalid_file', __('Invalid file path.', 'healthbeam'), array('status' => 400));
		}

		$allowed_files = get_transient('healthbeam_checksums');
		if (false === $allowed_files) {
			if (!function_exists('get_core_checksums')) {
				require_once ABSPATH . 'wp-admin/includes/update.php';
			}
			$allowed_files = get_core_checksums(get_bloginfo('version'), get_locale());
		}

		if (!isset($allowed_files[$file])) {
			return new \WP_Error('file_not_allowed', __('You do not have access to this file.', 'healthbeam'), array('status' => 403));
		}

		$local_file_path = ABSPATH . $file;
		if (!file_exists($local_file_path)) {
			return new \WP_Error('file_not_found', __('File not found.', 'healthbeam'), array('status' => 404));
		}

		$local_file_body = file_get_contents($local_file_path);

		$wp_version = get_bloginfo('version');
		$remote_url = 'https://core.svn.wordpress.org/tags/' . $wp_version . '/' . $file;

		$remote_response = wp_remote_get($remote_url);

		if (is_wp_error($remote_response)) {
			return new \WP_Error('remote_error', __('Could not fetch remote file.', 'healthbeam'), array('status' => 500));
		}

		$remote_file_body = wp_remote_retrieve_body($remote_response);

		if (!function_exists('wp_text_diff')) {
			require_once ABSPATH . 'wp-admin/includes/misc.php';
		}

		$diff_args = array(
			'show_split_view' => true,
		);

		$diff = wp_text_diff($remote_file_body, $local_file_body, $diff_args);

		// Wrap in table as per original plugin if wp_text_diff doesn't provide the full wrapper we want, 
		// but wp_text_diff returns the table rows. We need the table headers.
		$output = '<table class="diff"><thead><tr class="diff-sub-title"><th>';
		$output .= esc_html__('Original', 'healthbeam');
		$output .= '</th><th>';
		$output .= esc_html__('Modified', 'healthbeam');
		$output .= '</th></tr></thead><tbody>';
		$output .= $diff;
		$output .= '</tbody></table>';

		return rest_ensure_response(array('diff' => $output));
	}

	// Database Optimizer Methods
	public function get_database_overhead()
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tables = $wpdb->get_results("SHOW TABLE STATUS");
		$overhead_tables = array();
		$total_overhead = 0;

		foreach ($tables as $table) {
			if ($table->Data_free > 0) {
				$overhead_tables[] = array(
					'name' => $table->Name,
					'overhead' => size_format($table->Data_free),
					'overhead_bytes' => $table->Data_free,
				);
				$total_overhead += $table->Data_free;
			}
		}

		return rest_ensure_response(array(
			'tables' => $overhead_tables,
			'total_overhead' => size_format($total_overhead),
			'total_overhead_bytes' => $total_overhead,
		));
	}

	public function optimize_database()
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tables = $wpdb->get_results("SHOW TABLE STATUS");
		foreach ($tables as $table) {
			if ($table->Data_free > 0) {
				$table_name = esc_sql($table->Name);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query("OPTIMIZE TABLE $table_name");
			}
		}
		return rest_ensure_response(array('success' => true, 'message' => __('Database optimized successfully.', 'healthbeam')));
	}

	// Cron Manager Methods
	public function get_cron_jobs()
	{
		$cron_jobs = _get_cron_array();
		$formatted_jobs = array();

		foreach ($cron_jobs as $timestamp => $cronhooks) {
			foreach ($cronhooks as $hook => $keys) {
				foreach ($keys as $k => $v) {
					$formatted_jobs[] = array(
						'hook' => $hook,
						'timestamp' => $timestamp,
						'next_run' => get_date_from_gmt(gmdate('Y-m-d H:i:s', $timestamp), 'Y-m-d H:i:s'),
						'schedule' => isset($v['schedule']) ? $v['schedule'] : 'one-off',
						'args' => isset($v['args']) ? $v['args'] : array(),
					);
				}
			}
		}

		return rest_ensure_response(array('jobs' => $formatted_jobs));
	}

	public function run_cron_job($request)
	{
		$hook = $request->get_param('hook');
		$args = $request->get_param('args');

		if (!$hook) {
			return new \WP_Error('missing_hook', __('Missing hook name.', 'healthbeam'), array('status' => 400));
		}

		// Execute the hook
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		do_action($hook, ...$args);

		/* translators: %s: Hook name */
		return rest_ensure_response(array('success' => true, 'message' => sprintf(__('Cron job "%s" executed.', 'healthbeam'), $hook)));
	}

	public function delete_cron_job($request)
	{
		$hook = $request->get_param('hook');
		$timestamp = $request->get_param('timestamp');
		$args = $request->get_param('args');

		if (!$hook || !$timestamp) {
			return new \WP_Error('missing_params', __('Missing hook or timestamp.', 'healthbeam'), array('status' => 400));
		}

		$result = wp_unschedule_event($timestamp, $hook, $args);

		if ($result) {
			return rest_ensure_response(array('success' => true, 'message' => __('Cron job deleted.', 'healthbeam')));
		} else {
			return new \WP_Error('delete_failed', __('Failed to delete cron job.', 'healthbeam'), array('status' => 500));
		}
	}

	// Capability Checker Methods
	public function get_capabilities($request)
	{
		$role_name = $request->get_param('role');
		$user_id = $request->get_param('user_id');

		if ($user_id) {
			$user = get_userdata($user_id);
			if (!$user) {
				return new \WP_Error('user_not_found', __('User not found.', 'healthbeam'), array('status' => 404));
			}
			return rest_ensure_response(array(
				'type' => 'user',
				'name' => $user->display_name,
				'capabilities' => $user->allcaps,
			));
		}

		if ($role_name) {
			$role = get_role($role_name);
			if (!$role) {
				return new \WP_Error('role_not_found', __('Role not found.', 'healthbeam'), array('status' => 404));
			}
			return rest_ensure_response(array(
				'type' => 'role',
				'name' => $role_name,
				'capabilities' => $role->capabilities,
			));
		}

		// Return list of roles if no specific request
		global $wp_roles;
		return rest_ensure_response(array(
			'roles' => $wp_roles->roles,
		));
	}

	// Server Resources Methods
	public function get_server_resources()
	{
		$memory_usage = memory_get_usage();
		$memory_limit = ini_get('memory_limit');

		$load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

		return rest_ensure_response(array(
			'memory_usage' => size_format($memory_usage),
			'memory_limit' => $memory_limit,
			'load_average' => $load,
			'php_version' => phpversion(),
			'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown',
		));
	}
}
