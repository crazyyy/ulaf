<?php

namespace JSERRLOG;

if (!defined('ABSPATH')) {
    exit;
}

define('JSERRLOG_VERSION', '1.3.1');

class Plugin
{
    private object $_logger;
    private string $_path;
    private string $_base_dir;
    private array $_options;
    private array $_default_settings;
    private array $_settings;
    private bool $_has_known_cache_plugin = false;
    private bool $_is_plugin_screen;
    private string $_date_time_format;
    private bool $_can_access_errors = true;
    private array $_ignored_data = [];

    public function __construct()
    {
        $this->_init_settings();
        $this->_logger = new Logger();
        $this->set_front_end_hooks();
        $this->set_admin_hooks();
    }

    public function set_front_end_hooks(): void
    {
        if (!$this->_settings['activated']) {
            add_action('wp_enqueue_scripts', function () {
                wp_dequeue_script('jserrlog');
            });
            return;
        }
        add_action('wp_ajax_jserrlog_log_error', [$this, 'process_error']);
        add_action('wp_ajax_nopriv_jserrlog_log_error', [$this, 'process_error']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_js'], 1);
    }

    public function set_admin_hooks(): void
    {
        if ($this->_settings['activated'] && $this->_settings['log_back_end']) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_js'], 1);
        }
        if (!$this->_can_access_errors) {
            return;
        }
        if ($this->_settings['activated']) {
            add_action('wp_dashboard_setup', [$this, 'widget_setup']);
            add_action("wp_ajax_jserrlog_refresh_dashboard_log", [$this, "refresh_dashboard_log"]);
        }
        add_action("wp_ajax_jserrlog_refresh_log", [$this, "refresh_log"]);
        add_action("wp_ajax_jserrlog_purge_log", [$this, "purge_log"]);
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('wp_ajax_jserrlog_dismissed_notice_handler', [$this, "admin_notice_handler"]);
        add_filter('plugin_action_links', [$this, 'plugin_action_links'], 10, 2);
        add_action('admin_menu', [$this, 'register_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action("wp_ajax_jserrlog_update_settings", [$this, "update_settings"]);
        add_action('admin_footer', [$this, 'enqueue_admin_scripts']);
    }

    public function inline_styles(): void
    {
        wp_add_inline_style('jserrlog', ':root {--jserrlog-bg-color: ' . $this->_settings['accent'] . '52; --jserrlog-color: ' . $this->_settings['accent'] . ';}');
    }

    public function plugin_action_links($links, $file): array
    {
        if ($this->_base_dir . '/js-error-logger.php' == $file) {
            $settingsLink = '<a href="' . esc_url(admin_url('tools.php?page=js-error-logger')) . '">' . esc_html__('Settings', 'default') . '</a>';
            $links = array_merge([$settingsLink], $links);
        }
        return $links;
    }

    public function enqueue_admin_scripts(): void
    {
        if (!$this->_is_plugin_screen) {
            return;
        }
        $localization = [
            'nonce' => wp_create_nonce("jserrlog_nonce"),
            'settings' => $this->_default_settings,
            'text' => [
                'SettingsUpdateSuccess' => esc_html__('Settings successfully updated', 'js-error-logger'),
                'SettingsUpdateError' => esc_html__('An error occurred. Please refresh the page and try again.', 'js-error-logger'),
                'SettingsUpdating' => esc_html__('Updating settings...', 'js-error-logger'),
                'SelectPostTypes' => esc_html__('Select Post Type(s)', 'js-error-logger'),
                'SearchPostTypes' => esc_html__('Search', 'default'),
                'SelectedPostTypes' => ' ' . esc_html__('Post Types', 'default')
            ],
            'booleans' => [
                'has_known_cache_plugin' => $this->_has_known_cache_plugin
            ]
        ];
        wp_enqueue_script('toastr', $this->_path . '/res/toastr/toastr.min.js', [], '2.1.4', ['in_footer' => true]);
        wp_enqueue_script('jserrlog-settings', $this->_path . '/js/settings.js', ['toastr', 'jquery'], JSERRLOG_VERSION, ['in_footer' => true]);
        wp_localize_script('jserrlog-settings', 'jserrlog', $localization);
    }

    public function refresh_log(): void
    {
        $this->_check_nonce();
        wp_send_json_success($this->_render_log());
    }

    public function purge_log(): void
    {
        $this->_check_nonce();
        $this->_logger->purge_log();
        wp_send_json_success();
    }

    private function _init_settings(): void
    {
        if (!current_user_can('administrator')) {
            $this->_can_access_errors = false;
        }
        $this->_set_paths();
        $this->_check_for_known_cache_plugin();
        $this->_default_settings = $this->_get_default_settings();
        $this->_options = $this->_get_options();
        $this->_maybe_update_known_version();
        $this->_settings = $this->_populate_settings();
        $this->_maybe_dequeue_back_end_script();
        $this->_ignored_data = [
            'agents' => $this->_ignored_strings('ignored_user_agents'),
            'errors' => $this->_ignored_strings('ignored_errors'),
            'scripts' => $this->_ignored_strings('ignored_scripts'),
            'combined' => $this->_ignored_strings('ignored_combined')
        ];
        $this->_date_time_format = get_option('date_format') . ' ' . get_option('time_format');
    }

    private function _maybe_dequeue_back_end_script(): void
    {
        if (is_admin() && (!$this->_settings['activated'] || !$this->_settings['log_back_end'])) {
            add_action('admin_enqueue_scripts', function () {
                wp_dequeue_script('jserrlog');
            });
        }
    }

    private function _check_nonce(): void
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'jserrlog_nonce')) {
            wp_send_json_error();
            die;
        }
    }

    public function update_settings(): void
    {
        $this->_check_nonce();
        $requested_setting = sanitize_text_field(wp_unslash($_REQUEST['setting'] ?? ''));
        if (!$requested_setting) {
            wp_send_json_error();
        }
        $value = wp_unslash($_REQUEST['value'] ?? '');
        if ($requested_setting == 'accent') {
            $value = sanitize_hex_color($value);
        } elseif ($requested_setting == 'ignored_post_types') {
            $value = map_deep($value, 'sanitize_text_field');
        } else {
            $value = sanitize_textarea_field($value);
            if ($value == 'false') {
                $value = false;
            } elseif ($value == 'true') {
                $value = true;
            } elseif (in_array($requested_setting, ['ignored_user_agents', 'ignored_errors', 'ignored_scripts'])) {
                $value = $this->clean_simple_ignored_logic($value);
            } elseif ($requested_setting == 'ignored_combined') {
                $value = $this->clean_combined_ignored_logic($value);
            }
        }
        $settings = $this->_settings;
        $settings[$requested_setting] = $value;
        $options = $this->_get_options();
        $options['settings'] = $settings;
        $this->_settings = $settings;
        $this->_ignored_data = [
            'agents' => $this->_ignored_strings('ignored_user_agents'),
            'errors' => $this->_ignored_strings('ignored_errors'),
            'scripts' => $this->_ignored_strings('ignored_scripts'),
            'combined' => $this->_ignored_strings('ignored_combined')
        ];
        $this->_update_options($options);
        wp_send_json_success();
        die;
    }

    private function clean_simple_ignored_logic($value): string
    {
        $strings = explode(PHP_EOL, $value);
        $strings = array_filter($strings);
        $validStrings = [];
        foreach ($strings as $string) {
            $validStrings[] = trim($string);
        }
        return implode(PHP_EOL, $validStrings);
    }

    private function clean_combined_ignored_logic($value): string
    {
        $strings = explode(PHP_EOL, $value);
        $validStrings = [];
        foreach ($strings as $string) {
            if (!trim($string)) {
                continue;
            }
            $string = explode('||', $string);
            if (count($string) != 2) {
                continue;
            }
            $ignoredError = trim($string[0]);
            $ignoredScript = trim($string[1]);
            if (!$ignoredError || !$ignoredScript) {
                continue;
            }
            $validStrings[] = $ignoredError . '||' . $ignoredScript;
        }
        return sanitize_textarea_field(implode(PHP_EOL, $validStrings));
    }

    private function _update_options($options): void
    {
        update_option(JSERRLOG_OPTION, $options);
    }

    private function _get_options(): array
    {
        return get_option(JSERRLOG_OPTION, ['settings' => $this->_populate_settings()]);
    }

    private function _maybe_update_known_version(): void
    {
        $options = $this->_options;
        if (!isset($options['version'])) {
            $this->_update_known_version();
            self::create_mu_plugin();
            return;
        }
        if (version_compare($options['version'], JSERRLOG_VERSION, '<')) {
            if (version_compare($options['version'], '1.1.7', '<')) {
                self::create_mu_plugin();
            }
            $this->_update_known_version();
        }
    }

    private function _update_known_version(): void
    {
        $options = $this->_options;
        $options['version'] = JSERRLOG_VERSION;
        $this->_options = $options;
        $this->_update_options($options);
    }

    private function _populate_settings(): array
    {
        $settings = [];
        foreach ($this->_default_settings as $optionName => $data) {
            if (!isset($this->_options['settings'][$optionName])) {
                $settings[$optionName] = $data['value'];
            } else {
                $settings[$optionName] = $this->_options['settings'][$optionName];
            }
        }
        return $settings;
    }

    private function _get_default_settings(): array
    {
        //load_plugin_textdomain('js-error-logger', false, $this->_base_dir . '/lang');
        $settings = [
            'activated' => [
                'type' => 'switch',
                'logRefresh' => false,
                'value' => true,
                'text' => __('Activate JS Error Logging', 'js-error-logger'),
                'display' => 'normal',
                'cache_warning' => true
            ],
            'max_widget_results' => [
                'type' => 'number',
                'logRefresh' => false,
                'value' => 5,
                'range' => [1, 10],
                'text' => __('Max number of errors to show on the dashboard widget', 'js-error-logger'),
                'display' => 'normal',
                'cache_warning' => false
            ],
            'third_party' => [
                'type' => 'switch',
                'logRefresh' => true,
                'value' => false,
                'text' => __('Include errors from third party domains', 'js-error-logger'),
                'desc' => [
                    /* translators: 'Script error.' (Untranslatable, appears as is in the browser developer tools) */
                    sprintf(__('These errors are usually just returned as "%s"', 'js-error-logger'), 'Script error.')
                ],
                'display' => 'normal',
                'cache_warning' => true
            ],
            'log_back_end' => [
                'type' => 'switch',
                'logRefresh' => false,
                'value' => false,
                'text' => __('Log back end errors', 'js-error-logger'),
                'display' => 'normal',
                'cache_warning' => false
            ],
            'accent' => [
                'type' => 'color',
                'logRefresh' => false,
                'value' => '#2271b1',
                'text' => __('Accent color', 'js-error-logger'),
                'display' => 'normal',
                'cache_warning' => false
            ],
            'ignored_user_agents' => [
                'type' => 'textarea',
                'logRefresh' => true,
                'value' => '',
                'text' => __('Ignore errors if user agent string contains', 'js-error-logger'),
                'desc' => [__('1 per line', 'js-error-logger')],
                'display' => 'normal',
                'cache_warning' => true
            ],
            'ignored_errors' => [
                'type' => 'textarea',
                'logRefresh' => true,
                'value' => '',
                'text' => __('Ignore errors if error string contains', 'js-error-logger'),
                'desc' => [__('1 per line', 'js-error-logger')],
                'display' => 'normal',
                'cache_warning' => true
            ],
            'ignored_scripts' => [
                'type' => 'textarea',
                'logRefresh' => true,
                'value' => '',
                'text' => __('Ignore errors if script url contains', 'js-error-logger'),
                'desc' => [__('1 per line', 'js-error-logger')],
                'display' => 'normal',
                'cache_warning' => true
            ],
            'ignored_combined' => [
                'type' => 'textarea',
                'logRefresh' => true,
                'value' => '',
                'text' => __('Ignore errors if error string contains AND script url contains', 'js-error-logger'),
                'desc' => [__('1 per line, error and script separated by TWO pipe "||" characters', 'js-error-logger'), __('e.g. "jQuery is not defined||my_script" will ignore the error only if the script url contains "my_script"', 'js-error-logger')],
                'display' => 'normal',
                'cache_warning' => true
            ], 'show_advanced' => [
                'type' => 'switch',
                'logRefresh' => false,
                'value' => false,
                'text' => __('Show advanced settings', 'js-error-logger'),
                'display' => 'normal',
                'shows' => 'ignored_post_types,send_on_visibility_change,max_errors_per_page',
                'cache_warning' => false
            ],
            'ignored_post_types' => [
                'type' => 'multiselect',
                'logRefresh' => false,
                'value' => [],
                'choices' => call_user_func([$this, '_get_post_types']),
                'filter' => 'jserrlog_ignored_post_types',
                'text' => __('Ignored post types', 'js-error-logger'),
                'desc' => [
                    __('By default, the plugin will add its script on all post types.', 'js-error-logger')
                ],
                'display' => 'normal',
                'conditional' => 'show_advanced',
                'cache_warning' => true
            ],
            'max_errors_per_page' => [
                'type' => 'number',
                'logRefresh' => false,
                'value' => 10,
                'range' => [1, 10],
                'filter' => 'jserrlog_max_errors_per_page',
                'text' => __('Max errors to log per page', 'js-error-logger'),
                'desc' => [
                    __('To prevent logging errors coming from infinite loops, the plugin will not log more than that number of errors per page load.', 'js-error-logger'),
                ],
                'display' => 'normal',
                'conditional' => 'show_advanced',
                'cache_warning' => true
            ],
            'send_on_visibility_change' => [
                'type' => 'switch',
                'logRefresh' => false,
                'value' => false,
                'filter' => 'jserrlog_send_on_visibility_change',
                'text' => __('Ajax calls on visibility change', 'js-error-logger'),
                'desc' => [
                    __('By default, the plugin will call the server each time an error is triggered.', 'js-error-logger'),
                    __('Activate this setting to only make one server call when the page visibility changes (tab change, browser reduced, etc…).', 'js-error-logger'),
                ],
                'display' => 'normal',
                'conditional' => 'show_advanced',
                'cache_warning' => true
            ],
        ];
        return $settings;
    }

    private function _get_post_types(): array
    {
        $args = [
            'public' => true,
        ];
        $types = [];
        $post_types = get_post_types($args, 'objects');
        foreach ($post_types as $post_type) {
            $types[$post_type->label] = $post_type->name;
        }
        return $types;
    }

    private function _set_paths(): void
    {
        $this->_path = plugins_url('', JSERRLOG_PLUGIN_FILE);
        $this->_base_dir = dirname(plugin_basename(JSERRLOG_PLUGIN_FILE));
    }

    private function _check_for_known_cache_plugin(): void
    {
        if (
            defined('W3TC') //W3 TOTAL CACHE
            || defined('SWIFT_PERFORMANCE_VER') //SWIFT PERFORMANCE
            || defined('LSCWP_V') //LIGHTSPEED CACHE
            || defined('CACHE_ENABLER_VERSION') //CACHE ENABLER
            || defined('SiteGround_Optimizer\VERSION') //SITEGROUND OPTIMIZER
            || defined('WPCACHEHOME') //WP SUPER CACHE
            || defined('SPC_PATH') //SUPER PAGE CACHE
            || defined('WPFC_MAIN_PATH')//WP FASTEST CACHE
            || defined('WP_ROCKET_VERSION')//WP ROCKET
        ) {
            $this->_has_known_cache_plugin = true;
        }
    }

    public function register_menu_page(): void
    {
        add_submenu_page(
            'tools.php',
            esc_html__('JS Error Logger', 'js-error-logger'),
            esc_html__('JS Error Logger', 'js-error-logger'),
            'administrator',
            'js-error-logger',
            [$this, 'settings_page'],
            10
        );
    }

    private function _render_log(): string
    {
        ob_start();
        require_once __DIR__ . '/../templates/log.php';
        return ob_get_clean();
    }

    private function _render_settings(): void
    {
        require_once __DIR__ . '/../templates/settings.php';
    }

    public function settings_page(): void
    {
        require_once __DIR__ . '/../templates/plugin-page.php';
    }

    private function _set_screen_vars(): void
    {
        $this->_is_plugin_screen = get_current_screen()->id === 'tools_page_js-error-logger';
    }

    public function enqueue_styles(): void
    {
        $this->_set_screen_vars();
        if (!$this->_is_plugin_screen) {
            return;
        }
        wp_enqueue_style('jserrlog', $this->_path . '/css/admin.css', [], JSERRLOG_VERSION);
        wp_enqueue_style('toastr', $this->_path . '/res/toastr/toastr.min.css', [], '2.1.4');
        $this->inline_styles();
    }

    public function enqueue_dashboard_scripts(): void
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jserrlog-dashboard', $this->_path . '/js/dashboard.js', ['jquery-ui-dialog'], JSERRLOG_VERSION, ['in_footer' => true]);
        wp_enqueue_style('jserrlog', $this->_path . '/css/admin.css', ['wp-jquery-ui-dialog'], JSERRLOG_VERSION);
        $this->inline_styles();
        wp_localize_script('jserrlog-dashboard', 'jserrlog', [
                'nonce' => wp_create_nonce("jserrlog_nonce"),
                'strings' => [
                    'Close' => esc_html__('Close', 'default'),
                    'InlineScript' => esc_html__('Inline script', 'js-error-logger')
                ]
            ]
        );
    }

    private function _ignored_strings($type): array
    {
        $settings = $this->_settings;
        if (!isset($settings[$type])) {
            return [];
        }
        $ignoredStrings = [];
        if ($settings[$type]) {
            $strings = explode(PHP_EOL, $settings[$type]);
            $strings = array_filter($strings);
            foreach ($strings as $string) {
                $ignoredStrings[] = html_entity_decode(stripslashes(trim(strtolower($string))));
            }
        }
        return $ignoredStrings;
    }

    public static function is_ignored($ignoredStrings, $string): bool
    {
        if ($ignoredStrings) {
            foreach ($ignoredStrings as $ignoredString) {
                $matches = self::get_string_matches(html_entity_decode($ignoredString), html_entity_decode($string));
                if (!empty($matches)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function get_string_matches($ignoredString, $string): array
    {
        preg_match('/' . preg_quote($ignoredString, '/') . '/', strtolower($string), $matches);
        return $matches;
    }

    public static function is_multi_ignored($ignoredStrings, $strings): bool
    {
        if ($ignoredStrings) {
            foreach ($ignoredStrings as $ignoredString) {
                $ignoredString = explode('||', $ignoredString);
                $ignoredError = $ignoredString[0];
                $ignoredScript = $ignoredString[1];
                $errorMatch = self::get_string_matches(html_entity_decode($ignoredError), html_entity_decode($strings[0]));
                if (empty($errorMatch)) {
                    continue;
                }
                $scriptMatch = self::get_string_matches($ignoredScript, $strings[1]);
                if (!empty($scriptMatch)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function process_error(): void
    {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'jserrlog_log_error')) {
            wp_send_json_error();
            die;
        }
        $data = sanitize_textarea_field(wp_unslash($_REQUEST['data'] ?? ''));
        if ($data) {
            $errors = json_decode($data, true);
            foreach ($errors as $error) {
                $this->_sanitize_and_log_error($error, 'multiple');
            }
            wp_send_json_success();
        }

        if (isset($_REQUEST['msg']) && isset($_REQUEST['line']) && isset($_REQUEST['urls'])) {
            $this->_sanitize_and_log_error($_REQUEST);
            wp_send_json_success();
        }
        wp_die();
    }

    private function _sanitize_and_log_error($data, $type = 'single'): void
    {
        $msg = sanitize_textarea_field(stripslashes($data['msg']));
        $urls = json_decode(stripslashes($data['urls']));
        foreach ($urls as $key => $url) {
            $urls[$key] = sanitize_url($url);
        }
        if ($urls[0] == $urls[1]) {
            $urls[0] = 'Inline script';
        }
        $line = (int)($data['line']);
        $col = (int)$data['col'];
        $agent = sanitize_text_field($data['agent']) ?: esc_html__('Unknown', 'js-error-logger');
        $err = sanitize_text_field($data['err']);
        if ($type == 'single') {
            $err = stripslashes($err);
        }
        $err = html_entity_decode($err);
        $time = (int)$data['time'];
        $logger = $this->_logger;
        if ($this->_settings['third_party'] || trim(strtolower($msg)) != 'script error.') {
            $error_data = '[TIME] ' . $time . ' [ERROR] ' . html_entity_decode($msg, ENT_QUOTES) . ' [URLS] ' . json_encode($urls) . ' [LINE] ' . $line . ' [COL] ' . $col . ' [ERR] ' . $err . ' [AGENT] ' . $agent;
            $error_data = apply_filters('jserrlog_pre_insert_error', $error_data);
            $logger->error($error_data . PHP_EOL . PHP_EOL);
            do_action('jserrlog_after_log', $error_data);
        }
    }

    public function enqueue_js(): void
    {
        $ignoredPostTypes = apply_filters('jserrlog_ignored_post_types', null);
        if ($ignoredPostTypes === null) {
            $ignoredPostTypes = $this->_settings['ignored_post_types'];
        } elseif (!is_array($ignoredPostTypes)) {
            $ignoredPostTypes = [$ignoredPostTypes];
        }
        if (in_array(get_post_type(get_the_ID()), $ignoredPostTypes) || (is_admin() && !$this->_settings['log_back_end'])) {
            add_action('wp_enqueue_scripts', function () {
                wp_dequeue_script('jserrlog');
            });
            add_action('admin_enqueue_scripts', function () {
                wp_dequeue_script('jserrlog');
            });
            return;
        }
        if (wp_script_is('jserrlog')) {
            //was early loaded in mu-plugin
            add_filter('script_loader_src', [$this, 'rewrite_script_src'], 10, 2);
        } else {
            //fallback in case it couldn't be loaded as early as we wished
            wp_enqueue_script('jserrlog', $this->_path . '/js/front.min.js', [], JSERRLOG_VERSION, ["in_footer" => false]);
        }
        $delay_send = apply_filters('jserrlog_send_on_visibility_change', null);
        if ($delay_send === null) {
            $delay_send = $this->_settings['send_on_visibility_change'];
        }
        $max_errors = apply_filters('jserrlog_max_errors_per_page', null);
        if ($max_errors === null) {
            $max_errors = $this->_settings['max_errors_per_page'];
        }
        $localization = [
            'nonce' => wp_create_nonce("jserrlog_log_error"),
            'ajax_url' => admin_url('admin-ajax.php'),
            'booleans' => [
                'delay_send' => $delay_send,
                'third_party_scripts' => $this->_settings['third_party'],
            ],
            'max_errors_per_page' => (int)$max_errors,
            'time' => round(microtime(true) * 1000),
            'ignored_data' => $this->_ignored_data
        ];
        wp_localize_script('jserrlog', 'js_err_log', $localization);
    }

    public function rewrite_script_src($src, $handle)
    {
        if ($handle != 'jserrlog') {
            return $src;
        }
        return $this->_path . '/js/front.min.js?ver=' . JSERRLOG_VERSION;
    }

    public function widget_setup(): void
    {
        wp_add_dashboard_widget(
            'js-err-log_widget',
            esc_html__('Last JS Errors', 'js-error-logger'),
            [$this, 'widget'],
            null, [
            'logger' => $this->_logger,
            'settings' => $this->_settings,
            'date_format' => $this->_date_time_format,
            'ignored_data' => $this->_ignored_data
        ]);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_dashboard_scripts']);
    }

    public static function timeago($timestamp): string
    {

        $length = ["60", "60", "24", "30", "12", "10"];

        $currentTime = time();
        if ($currentTime >= $timestamp) {
            $diff = time() - $timestamp;
            for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            $strTime = [
                /* translators: Number. */
                sprintf(_n("%s second ago", "%s seconds ago", $diff, 'js-error-logger'), $diff),
                /* translators: Number. */
                sprintf(_n("%s minute ago", "%s minutes ago", $diff, 'js-error-logger'), $diff),
                /* translators: Number. */
                sprintf(_n("%s hour ago", "%s hours ago", $diff, 'js-error-logger'), $diff),
                /* translators: Number. */
                sprintf(_n("%s day ago", "%s days ago", $diff, 'js-error-logger'), $diff),
                /* translators: Number. */
                sprintf(_n("%s month ago", "%s months ago", $diff, 'js-error-logger'), $diff),
                /* translators: Number. */
                sprintf(_n("%s year ago", "%s years ago", $diff, 'js-error-logger'), $diff),
            ];
            return $strTime[$i];
        }
        return __('Unknown', 'js-error-logger');
    }

    public static function error_texts($error, $isFullLog = false): array
    {
        $error['err'] = htmlspecialchars($error['err'], ENT_NOQUOTES);
        $errorData = str_replace('\n', '<br>', $error['err']);
        $errorData = json_decode($errorData);
        $errorText = $errorData->message ?? $error['error'];
        $fullError = '<strong>' . esc_html__('Message', 'js-error-logger') . ':</strong><br>' . $errorText;
        if ($isFullLog) {
            $fullError .= '<br><a href="https://www.google.com/search?q=js+error+' . urlencode('"' . $errorText . '"') . '" target="_blank">' . esc_html__('Search error', 'js-error-logger') . '</a>';
        }
        if (isset($errorData->stack)) {
            $fullError .= '<br><br><strong>Stack:</strong><br>' . $errorData->stack;
        }
        return [$fullError, $errorText];
    }

    public static function widget($var, $args): void
    {
        $passedVars = $args['args'];
        $logger = $passedVars['logger'];
        $max_number = $passedVars['settings']['max_widget_results'];
        $jserrlog_format = $passedVars['date_format'];
        $jserrlog_errors = $logger->get_log_content($max_number, $passedVars['ignored_data']);
        require_once __DIR__ . '/../templates/dashboard-widget.php';
    }

    public function refresh_dashboard_log(): void
    {
        $this->_check_nonce();
        ob_start();
        $settings = $this->_settings;
        $jserrlog_format = $this->_date_time_format;
        $max_number = $settings['max_widget_results'];
        $logger = $this->_logger;
        $jserrlog_errors = $logger->get_log_content($max_number, $this->_ignored_data);
        require __DIR__ . '/../templates/dashboard-widget.php';
        wp_send_json_success(ob_get_clean());
    }

    public function admin_notice(): void
    {
        if (get_current_screen()->id != 'dashboard') {
            return;
        }
        if ($this->_is_notice_active('jfgmedia-plugins')) { ?>
            <div class="notice js-err-log-notice notice-info is-dismissible" data-notice="jfgmedia-plugins">
                <p><?php printf(
                    /* translators: Plugin Name. */
                        esc_html__('We noticed that you have been using our %s plugin for quite some time now. We are glad that you find it useful!',
                            'js-error-logger'),
                        '<b>' . esc_html__('JS Error Logger', 'js-error-logger') . '</b>'
                    );
                    ?></p>
                <p><?php printf(
                    /* translators: URL. */
                        wp_kses(__('If you haven\'t done so yet, we would highly appreciate if you could take 2 minutes of your time to <a href="%s" target="_blank" rel="noopener">rate it</a>. That would really help us in keeping it maintained and coming up with new features.',
                            'js-error-logger'), ['a' => ['href' => [], 'target' => []]]),
                        'https://wordpress.org/support/plugin/sjs-error-logger/reviews/#new-post'
                    );
                    ?></p>
                <p><?php esc_html_e('You may also find our other plugins useful:', 'js-error-logger')
                    ?></p>
                <ul>
                    <li><?php
                        /* translators: Plugin Name. */
                        printf(esc_html__('%s: it allows you to create a to-do list, and easily assign tasks to other users.', 'js-error-logger'), '<a href="https://wordpress.org/plugins/sortable-dashboard-to-do-list/" target="_blank" rel="noopener">
Sortable Dashboard To-Do List</a>');
                        ?></li>
                    <li><?php
                        /* translators: Plugin Name. */
                        printf(esc_html__('%s: it allows you to stop being nagged by plugin updates that you don\'t want to do, and works on a version-per-version basis.', 'js-error-logger'), '<a href="https://wordpress.org/plugins/ignore-single-update/" target="_blank" rel="noopener">
Ignore Or Disable Plugin Update</a>');
                        ?></li>
                </ul>
            </div>
            <script>
                jQuery(document).on('click', '.js-err-log-notice .notice-dismiss', function () {
                    let notice = jQuery(this).closest('.js-err-log-notice'),
                        type = notice.data('notice');
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: ajaxurl,
                        data: {
                            action: 'jserrlog_dismissed_notice_handler',
                            type: type,
                            nonce: "<?php echo esc_html(wp_create_nonce("jserrlog_nonce")); ?>"
                        }
                    });
                });
            </script>
        <?php }
    }

    private function _is_notice_active($type): bool
    {
        if (!isset($this->_options['dismissed_notices'][$type])) {
            if ($type == 'jfgmedia-plugins') {
                $options = $this->_options;
                $options['dismissed_notices'][$type] = gmdate('Y-m-d', strtotime('+14 days'));
                $this->_update_options($options);
                return false;
            }
            return true;
        }
        if ($this->_options['dismissed_notices'][$type] <= gmdate('Y-m-d')) {
            return true;
        }
        return false;
    }

    public function admin_notice_handler(): void
    {
        $this->_check_nonce();
        if (!isset($_REQUEST['type'])) {
            wp_send_json_error();
        }
        $type = sanitize_text_field(wp_unslash($_REQUEST['type']));
        $options = $this->_options;
        $options['dismissed_notices'][$type] = gmdate('Y-m-d', strtotime('+90 days'));
        $this->_update_options($options);
        $this->_options = $options;
        wp_send_json_success();
        die;
    }

    public static function init(): void
    {
        new self();
    }

    public static function activate_plugin(): void
    {
        self::create_mu_plugin();
        Logger::create_log_directory();
        if (!wp_next_scheduled('jserrlog-cleanup')) {
            wp_schedule_event(time(), 'daily', 'jserrlog-cleanup');
        }
    }

    public static function create_mu_plugin(): void
    {
        // We want our script loaded as early as possible (must-use plugin)
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        WP_Filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem) {
            // Could optionally log this failure
            return;
        }

        $mu_plugins_dir = WPMU_PLUGIN_DIR;
        $source_file = JSERRLOG_PLUGIN_DIR . '/templates/early-loader.php';
        $target_file = trailingslashit($mu_plugins_dir) . 'jserrlog-early-loader.php';

        // Ensure mu-plugins directory exists
        if (!$wp_filesystem->is_dir($mu_plugins_dir)) {
            $wp_filesystem->mkdir($mu_plugins_dir, FS_CHMOD_DIR);
        }

        // If we still don't have a usable directory, bail
        if (
            !$wp_filesystem->is_dir($mu_plugins_dir)
            || !$wp_filesystem->is_writable($mu_plugins_dir)
        ) {
            return;
        }

        // Make sure source exists before copying
        if (!$wp_filesystem->exists($source_file)) {
            return;
        }
        // Copy the file as a must-use plugin
        // Params: source, destination, overwrite, chmod
        $wp_filesystem->copy($source_file, $target_file, true, FS_CHMOD_FILE);
    }

    public static function delete_mu_plugin(): void
    {
        $file = trailingslashit(WPMU_PLUGIN_DIR) . 'jserrlog-early-loader.php';
        if (is_file($file)) {
            wp_delete_file($file);
        }
    }

    public static function uninstall_plugin(): void
    {
        Logger::delete_log_directory();
        self::delete_mu_plugin();
        delete_option(JSERRLOG_OPTION);
        wp_clear_scheduled_hook('jserrlog-cleanup');
    }

    public static function deactivate_plugin(): void
    {
        self::delete_mu_plugin();
    }
}
