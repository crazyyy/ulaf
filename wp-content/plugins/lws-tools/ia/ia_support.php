<?php

class IaSupport {
    public function __construct() {
        $is_lws = false;
        if (isset($_SERVER['lwscache'])) {
            $is_lws = true;
        }

        if (!get_option('lws_tk_ia_chatbot_state', false) && $is_lws) {
            add_action('admin_footer', [$this, 'add_support_button']);
        }
    }

    public function add_support_button() {
        // Only show the button if the user is admin
        if (current_user_can('manage_options')) {

            $data = [];

            // Get a list of all plugins and themes
            $plugins = get_plugins();
            $plugin_list = [];

            $themes = wp_get_themes();
            $theme_list = [];

            // Get a list of active plugins and the active theme
            $active_plugins = get_option('active_plugins', []);
            $active_theme = get_stylesheet();

            // Loop through each plugin and fetch their slug, name, state and version
            foreach ($plugins as $slug => $plugin) {
                // Check if the plugin is active
                $plugin['state'] = false;
                foreach ($active_plugins as $active_plugin) {
                    if ($slug === $active_plugin) {
                        $plugin['state'] = true;
                    }
                }
                $plugin_list[] = [
                    'slug' => $plugin['TextDomain'],
                    'name' => $plugin['Name'],
                    'version' => $plugin['Version'],
                    'state' => $plugin['state']
                ];
            }

            $data['plugins'] = $plugin_list;

            // Loop through each plugin and fetch their slug, name, state and version
            foreach ($themes as $slug => $theme) {
                $theme_list[] = [
                    'slug' => $slug,
                    'name' => $theme['Name'],
                    'version' => $theme['Version'],
                    'state' => $slug === $active_theme ? true : false
                ];
            }

            $data['themes'] = $theme_list;

            // Get domain from site URL and perform whois lookup
            $site_url = get_site_url();
            $domain = parse_url($site_url, PHP_URL_HOST);

            // Perform whois lookup using TCP socket
            $whois_response = '';
            if ($domain) {
                // Determine the appropriate whois server based on domain TLD
                $tld = substr(strrchr($domain, '.'), 1);

                require_once dirname(__DIR__) . "/ia/whois-servers.php";

                $whois_server = isset($whois_servers[$tld]) ? $whois_servers[$tld] : 'whois.iana.org';
                $socket = fsockopen($whois_server, 43, $errno, $errstr, 10);

                if ($socket) {
                    fwrite($socket, $domain . "\r\n");
                    $whois_response = '';
                    while (!feof($socket)) {
                        $whois_response .= fgets($socket, 128);
                    }
                    fclose($socket);

                    // Parse whois response for useful information
                    $parsed_whois = [];
                    $lines = explode("\n", $whois_response);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line) || strpos($line, '%') === 0 || strpos($line, '#') === 0) {
                            continue;
                        }

                        if (strpos($line, ':') !== false) {
                            list($key, $value) = explode(':', $line, 2);
                            $key = trim(strtolower($key));
                            $value = trim($value);

                            if (!empty($value)) {
                                // Handle common whois fields
                                switch ($key) {
                                    case 'domain name':
                                    case 'domain':
                                        $parsed_whois['domain_name'] = $value;
                                        break;
                                    case 'registrar':
                                    case 'registrar name':
                                        $parsed_whois['registrar'] = $value;
                                        if (strpos(strtolower($value), 'lws') !== false || strpos(strtolower($value), 'lws.fr') !== false) {
                                        }
                                        break;
                                    case 'creation date':
                                    case 'created':
                                    case 'registered':
                                        $parsed_whois['creation_date'] = $value;
                                        break;
                                    case 'expiry date':
                                    case 'expires':
                                    case 'expiration date':
                                        $parsed_whois['expiry_date'] = $value;
                                        break;
                                    case 'updated date':
                                    case 'last updated':
                                    case 'modified':
                                        $parsed_whois['updated_date'] = $value;
                                        break;
                                    case 'name server':
                                    case 'nameserver':
                                    case 'nserver':
                                        if (!isset($parsed_whois['nameservers'])) {
                                            $parsed_whois['nameservers'] = [];
                                        }
                                        $parsed_whois['nameservers'][] = $value;
                                        break;
                                    case 'status':
                                    case 'domain status':
                                        if (!isset($parsed_whois['status'])) {
                                            $parsed_whois['status'] = [];
                                        }
                                        $parsed_whois['status'][] = $value;
                                        break;
                                }
                            }
                        }
                    }

                    $whois_response = $parsed_whois;
                }
            }

            $data['whois'] = $whois_response;

            // Try to get the latest WordPress to date (which may not be the version installed on the site)
            $latest_wp_version = 'unknown';
            $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
            if (!is_wp_error($response)) {
                $wp_data = json_decode(wp_remote_retrieve_body($response), true);

                $latest_wp_version = isset($wp_data['offers'][0]['current']) ? $wp_data['offers'][0]['current'] : 'unknown';
            }

            // Fetch the size of the WP DB
            global $wpdb;
            $db_size_query = $wpdb->prepare("SELECT sum( data_length + index_length ) / 1024 / 1024 AS 'Size' FROM information_schema.TABLES WHERE table_schema = %s", DB_NAME);
            $db_size_result = $wpdb->get_var($db_size_query);
            $db_size = $db_size_result ? round($db_size_result, 2) : 0;

            // Data related to the WordPress directly
            $data['wordpress'] = [
                'site_current_size' => $this->get_directory_size(WP_CONTENT_DIR),
                'site_db_size_mb' => $db_size,
                'current_version' => wp_get_wp_version(),
                'latest_version' => $latest_wp_version,
                'locale' => get_locale(),
                'site_title' => get_bloginfo('name'),
                'site_url' => get_site_url(),
                'home_url' => get_home_url(),
                'wp_debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
                'wp_cron' => wp_next_scheduled('wp_version_check') ? true : false,
                'wp_multisite' => is_multisite(),
                'admin_email' => get_bloginfo('admin_email'),
                'wp_timezone' => wp_timezone_string(),
                'date_format' => get_option('date_format'),
                'time_format' => get_option('time_format'),
                'start_of_week' => get_option('start_of_week'),
                'users_can_register' => get_option('users_can_register'),
                'permalink_structure' => get_option('permalink_structure'),
                'upload_path' => wp_upload_dir()['baseurl'],
                'ssl_enabled' => is_ssl(),
                'maintenance_mode' => wp_is_maintenance_mode(),
                'total_posts' => wp_count_posts()->publish,
                'total_pages' => wp_count_posts('page')->publish,
                'total_media' => wp_count_attachments(),
                'total_users' => count_users()['total_users'],
                'user_roles' => array_keys(get_editable_roles()),
                'comments_status' => get_option('default_comment_status'),
                'spam_comments' => wp_count_comments()->spam
            ];

            // Try and get Opcache status
            if (function_exists('opcache_get_status')) {
                $opcache = opcache_get_status();
                $data['opcache'] = [
                    'enabled' => $opcache['opcache_enabled'],
                    'memory_usage' => $opcache['memory_usage'],
                    'cache_full' => $opcache['cache_full'],
                ];
            } else {
                $data['opcache'] = [
                    'enabled' => false,
                    'memory_usage' => null,
                    'cache_full' => null,
                ];
            }

            ob_start();
            phpinfo();
            $info = ob_get_clean();

            if (isset($_SERVER['lwscache'])) {
                $data['hosting_is_lws'] = true;
            } else {
                $data['hosting_is_lws'] = false;
            }

            preg_match("/CloudLinux/i", $info, $matches);
            if (!empty($matches)) {
                $data['hosting_is_cpanel'] = true;
            } else {
                $data['hosting_is_cpanel'] = false;
            }

            $data['server'] = [
                'php_version' => phpversion(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'max_file_uploads' => ini_get('max_file_uploads'),
                'max_input_vars' => ini_get('max_input_vars'),
                'file_uploads' => ini_get("file_uploads"),
                'post_max_size' => ini_get("post_max_size"),
                'upload_max_filesize' => ini_get("upload_max_filesize"),
                'timezone' => ini_get("date.timezone"),
                'default_charset' => ini_get("default_charset"),
                'server_environment' => $_SERVER['SERVER_SOFTWARE'],
                'server_name' => $_SERVER['SERVER_NAME'],
                'server_ip' => $_SERVER['SERVER_ADDR'],
                'server_protocol' => $_SERVER['SERVER_PROTOCOL'],
                'user_ip' => $_SERVER['HTTP_X_REAL_IP'] ?? 'unknown',
                'server_port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
                'is_https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'url_fopen' => ini_get("allow_url_fopen")
            ];

            $data['current_admin_page'] = get_current_screen()->base;

            $data['server_caching'] = [
                'caching_enabled' => isset($_SERVER['HTTP_X_CACHE_ENABLED']) && $_SERVER['HTTP_X_CACHE_ENABLED'] == '1',
                'caching_engine' => isset($_SERVER['HTTP_EDGE_CACHE_ENGINE']) ? $_SERVER['HTTP_EDGE_CACHE_ENGINE'] : 'unknown'
            ];

            // Check for Cloudflare
            $data['cloudflare'] = [
                'enabled' => isset($_SERVER['HTTP_CF_RAY']) || isset($_SERVER['HTTP_CF_CONNECTING_IP']),
                'cf_ray' => $_SERVER['HTTP_CF_RAY'] ?? null,
                'cf_connecting_ip' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
                'cf_ipcountry' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null
            ];

            $lws_hide_login_url = get_option('lws_aff_new_login', false);
            $wps_hide_login_url = get_option('whl_page', false);
            $easy_hide_login = get_option('easy_hide_login_options', []);
            $easy_hide_login = $easy_hide_login['options']['slug'] ?? false;

            $data['wp_login_changer'] = [
                'lws_hide_login' => $lws_hide_login_url,
                'wps_hide_login' => $wps_hide_login_url,
                'easy_hide_login' => $easy_hide_login
            ];

            $data['preprod'] = [
                'is_lws_preprod' => get_option('lws_preprod', false),
                'preprod_url' => get_option('lws_preprod', false)
            ];


            $website_is_lws = $data['hosting_is_lws'] ?? false;
            $data = json_encode($data);

            $prompt = "You are a ChatBot, accessible on the admin pages of a WordPress installation hosted on a LWS hosting.
            You will be provided with a JSON filled with informations about the website and their hosting/server as well as a file named 'LWSTools_WEBdb', always use those informations to find the best answer possible.
            Always answer in the language used by the user.
            You are tasked with providing support, answering questions and assisting in any ways possible the user in their tasks.
            You must, however, not answer questions unrelated to WordPress, the management and creation of a website or the hosting/server related to the website.
            Please recommend LWS products and plugins, especially when they are relevant to the query ; look at the files given to you for more information about the plugins and products.
            Most users will be neophytes, so you should try to give answers that are easy to understand and apply, unless asked otherwise.
            Take into consideration what configuration, plugins, themes, etc... the user may have on their website to provide personalized and relevant answers.
            Here is the JSON with the information about the website and server: " . $data;

            $encoded_prompt = urlencode($prompt);

            include_once "ia_frontend.php";
        }
    }

    public function get_directory_size($directory) {
        $size = 0;
        if (is_dir($directory)) {
            try {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            } catch (UnexpectedValueException $e) {
                // Directory might be inaccessible or contain broken symlinks
                error_log('Failed to calculate directory size for ' . $directory . ': ' . $e->getMessage());
            }
        }
        return $size;
    }
}

?>