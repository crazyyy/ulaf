<?php

namespace Wpextended\Includes;

use Wpextended\Includes\Framework\Framework;
use Wpextended\Includes\Modules;

class Utils
{
    /**
     * The prefix used for all settings
     */
    private const SETTINGS_PREFIX = 'wpextended__';

    public static function initializeFramework($option_group, array $settings = [])
    {
        return Framework::getInstance($option_group, $settings);
    }

    /**
     * Get the full option name for a context
     *
     * @param string $context Context (module_id or 'core')
     * @return string Full option name
     */
    public static function getOptionName($context)
    {
        if ($context === 'core') {
            return self::SETTINGS_PREFIX . 'settings';
        }
        return self::SETTINGS_PREFIX . $context . '_settings';
    }

    /**
     * Get settings for an option group or context
     *
     * @param string $option_group_or_context Option group or context (module_id or 'core')
     * @return array
     */
    public static function getSettings($option_group_or_context)
    {
        $option_name = self::getOptionName($option_group_or_context);
        $settings = get_option($option_name, []);

        // Ensure we always return an array
        return is_array($settings) ? $settings : [];
    }

    /**
     * Get a specific setting value
     *
     * @param string $option_group_or_context Option group or context (module_id or 'core')
     * @param string $field_id_or_key Field ID or setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    public static function getSetting($option_group_or_context, $field_id_or_key, $default = null)
    {
        $settings = self::getSettings($option_group_or_context);

        // If the key exists in saved settings (even falsy/empty), return it
        if (self::hasArrayKey($settings, $field_id_or_key)) {
            return self::getArrayValue($settings, $field_id_or_key, null);
        }


        // If a caller default is provided, prefer it over schema default
        if (!is_null($default)) {
            return $default;
        }

        return null;
    }

    /**
     * Update settings for an option group or context
     *
     * @param string $option_group_or_context Option group or context (module_id or 'core')
     * @param array $settings The settings to update
     * @return bool
     */
    public static function updateSettings($option_group_or_context, $settings)
    {
        $option_name = self::getOptionName($option_group_or_context);
        return update_option($option_name, $settings);
    }

    /**
     * Delete settings for an option group or context
     *
     * @param string $option_group_or_context Option group or context (module_id or 'core')
     * @return bool
     */
    public static function deleteSettings($option_group_or_context)
    {
        $option_name = self::getOptionName($option_group_or_context);
        return delete_option($option_name);
    }

    /**
     * Flatten a nested array into dot-notation keys with a prefix.
     *
     * @param array $array
     * @param string $prefix
     * @return array<string, mixed>
     */
    private static function flattenArrayWithPrefix(array $array, $prefix)
    {
        $result = [];

        $iterator = function ($data, $path) use (&$result, &$iterator) {
            foreach ($data as $key => $val) {
                $currentPath = $path === '' ? (string) $key : $path . '.' . $key;
                if (is_array($val)) {
                    $iterator($val, $currentPath);
                } else {
                    $result[$currentPath] = $val;
                }
            }
        };

        $iterator($array, (string) $prefix);

        return $result;
    }

    /**
     * Update a specific setting
     *
     * @param string $option_group Option group
     * @param string $field_id Field ID
     * @param mixed $value Value to set
     * @return bool
     */
    public static function updateSetting($option_group, $field_id, $value)
    {
        $settings = self::getSettings($option_group);

        // Handle nested keys
        if (strpos($field_id, '.') !== false) {
            $keys = explode('.', $field_id);
            $current = &$settings;

            foreach ($keys as $i => $key) {
                if ($i === count($keys) - 1) {
                    $current[$key] = $value;
                } else {
                    if (!isset($current[$key]) || !is_array($current[$key])) {
                        $current[$key] = [];
                    }
                    $current = &$current[$key];
                }
            }
        } else {
            $settings[$field_id] = $value;
        }

        return self::updateSettings($option_group, $settings);
    }

    /**
     * Delete a specific setting
     *
     * @param string $option_group Option group
     * @param string $field_id Field ID
     * @return bool
     */
    public static function deleteSetting($option_group, $field_id)
    {
        $settings = self::getSettings($option_group);

        // Handle nested keys
        if (strpos($field_id, '.') !== false) {
            $keys = explode('.', $field_id);
            $current = &$settings;

            for ($i = 0; $i < count($keys) - 1; $i++) {
                if (!isset($current[$keys[$i]]) || !is_array($current[$keys[$i]])) {
                    return false;
                }
                $current = &$current[$keys[$i]];
            }

            unset($current[end($keys)]);
        } else {
            unset($settings[$field_id]);
        }

        return self::updateSettings($option_group, $settings);
    }

    /**
     * Convert a string to PascalCase.
     *
     * @param string $string The string to convert.
     * @return string|false PascalCase string or false if invalid.
     */
    private static function toPascalCase($string)
    {
        if (!is_string($string)) {
            return false;
        }
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Get module class name from module ID.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @return string|false PascalCase class name or false if invalid.
     */
    public static function getModuleClassName($module_id)
    {
        return self::toPascalCase($module_id);
    }

    /**
     * Get module file path from module ID.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @return string|false Lowercase-dashed path or false if invalid.
     */
    public static function getModulePath($module_id)
    {
        if (!is_string($module_id)) {
            return false;
        }
        return $module_id;
    }

    /**
     * Get module namespace parts.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @param bool $is_pro Whether this is a Pro module.
     * @return array|false Array of namespace parts or false if invalid.
     */
    private static function getModuleNamespaceParts($module_id, $is_pro = false)
    {
        $class_name = self::getModuleClassName($module_id);
        if (!$class_name) {
            return false;
        }

        $parts = ['Wpextended', 'Modules', $class_name];

        if ($is_pro) {
            $parts[] = 'Pro';
        }

        $parts[] = 'Bootstrap';

        return $parts;
    }

    /**
     * Get module class path.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @param bool $is_pro Whether this is a Pro module.
     * @return string|false Full class path or false if invalid module ID.
     */
    public static function getModuleClassPath($module_id, $is_pro = false)
    {
        $parts = self::getModuleNamespaceParts($module_id, $is_pro);
        if (!$parts) {
            return false;
        }
        return implode('\\', $parts);
    }

    /**
     * Get module file path with optional subpath.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @param string $subpath Optional subpath to append.
     * @return string|false Full file path or false if invalid.
     */
    public static function getModuleFilePath($module_id, $subpath = '')
    {
        $path = self::getModulePath($module_id);
        if (!$path) {
            return false;
        }
        return 'modules/' . $path . ($subpath ? '/' . ltrim($subpath, '/') : '');
    }

    /**
     * Get module absolute file path with optional subpath.
     *
     * @param string $module_id Module ID (in lowercase-dashed format).
     * @param string $subpath Optional subpath to append.
     * @return string|false Full absolute file path or false if invalid.
     */
    public static function getModuleAbsolutePath($module_id, $subpath = '')
    {
        $path = self::getModuleFilePath($module_id, $subpath);
        if (!$path) {
            return false;
        }
        return WP_EXTENDED_PATH . $path;
    }

    /**
     * Check if the current screen is a screen for WP Extended.
     *
     * @param string $module_id Module ID.
     * @return bool True if the current screen is a plugin screen, false otherwise.
     */
    public static function isPluginScreen($module_id)
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        if (!is_admin()) {
            return false;
        }

        $screen = get_current_screen();

        if (!$screen) {
            return false;
        }

        // Check for main modules page
        if ($module_id === 'modules' && $screen->id === 'toplevel_page_wpextended') {
            return true;
        }

        // Check for settings page
        if ($module_id === 'settings' && $screen->id === 'wp-extended_page_wpextended-settings') {
            return true;
        }

        // Check for module-specific pages
        $sublevel = sprintf('wp-extended_page_wpextended-%s', $module_id);
        $top_level = sprintf('admin_page_wpextended-%s', $module_id);

        if ($screen->id === $sublevel || $screen->id === $top_level) {
            return true;
        }

        return false;
    }

    /**
     * Get module page link.
     *
     * @param string $module_id Module ID.
     * @param array $args Optional. Additional query arguments.
     * @return string|null Module settings page URL or null if not available.
     */
    public static function getModulePageLink($module_id, $args = [])
    {
        $base_url = null;

        if ($module_id === 'modules') {
            $base_url = admin_url('admin.php?page=wpextended');
        }

        if ($module_id === 'settings' || $module_id === 'global') {
            $base_url = admin_url('admin.php?page=wpextended-settings');
        }

        if ($base_url === null) {
            $module = Modules::findModule($module_id);
            if ($module) {
                $base_url = admin_url(sprintf('admin.php?page=wpextended-%s', $module['id']));
            }
        }

        if (!$base_url) {
            return null;
        }

        if (empty($args)) {
            return $base_url;
        }

        // Add each query arg individually since $args contains key/value pairs
        foreach ($args as $key => $value) {
            $base_url = add_query_arg($key, $value, $base_url);
        }

        return $base_url;
    }

    /**
     * Generate a tracked link with UTM and platform parameters, or an HTML <a> tag.
     *
     * @param string $url Base URL to add tracking to.
     * @param string $context Optional. Current page context (module, settings, modules).
     * @param array $args Optional. Additional arguments for tracking.
     * @param string $text Optional. If provided, returns an HTML <a> tag with this text.
     * @param array $attrs Optional. Array of HTML attributes for the <a> tag.
     * @return string Tracked URL or HTML <a> tag if $text is provided.
     */
    public static function generateTrackedLink($url, $context = '', $args = [], $text = '', $attrs = [])
    {
        global $wp_version;

        // Set campaign based on context
        $campaign = $context ? $context : '';

        // Default tracking parameters
        $default_args = [
            'utm_source'        => 'wpextended',
            'utm_medium'        => 'plugin',
            'utm_campaign'      => $campaign,
            'utm_content'       => 'plugin-link',
            'php_version'       => PHP_VERSION,
            'wordpress_version' => $wp_version,
            'plugin_type'       => defined('WP_EXTENDED_PRO') ? 'pro' : 'free',
            'plugin_version'    => WP_EXTENDED_VERSION,
            'days_active'       => self::getDaysActive()
        ];

        // Merge default args with provided args
        $tracking_args = array_filter(wp_parse_args($args, $default_args));

        // Add tracking parameters to URL
        $tracked_url = add_query_arg($tracking_args, $url);

        // If no link text, just return the URL
        if (empty($text)) {
            return $tracked_url;
        }

        // Build HTML attributes string, always escape
        $attr_str = '';
        if (!empty($attrs) && is_array($attrs)) {
            foreach ($attrs as $attr => $val) {
                if (is_bool($val)) {
                    if ($val) {
                        $attr_str .= ' ' . esc_attr($attr);
                    }
                    continue;
                }
                $attr_str .= sprintf(' %s="%s"', esc_attr($attr), esc_attr($val));
            }
        }

        // Always escape URL and text
        $html = sprintf(
            '<a href="%s"%s>%s</a>',
            esc_url($tracked_url),
            $attr_str,
            esc_html($text)
        );

        return $html;
    }

    /**
     * Get number of days the plugin has been active
     *
     * @return int Number of days active
     */
    private static function getDaysActive()
    {
        $activation_time = get_option('wpextended_activation_time');

        if (!$activation_time) {
            return 0;
        }

        $now = time();
        $days = floor(($now - $activation_time) / DAY_IN_SECONDS);

        return max(0, $days);
    }

    /**
     * Internal function to handle both script and style registration
     *
     * @param string  $handle    Asset handle
     * @param string  $path     Path to the asset relative to plugin root
     * @param array   $deps     Array of dependencies
     * @param string  $version   Version string
     * @param bool    $in_footer Whether to register script in footer (scripts only)
     * @param string  $type     Either 'script' or 'style'
     * @return void
     */
    private static function registerAsset($handle, $path, $deps, $version, $in_footer, $type)
    {
        $is_dev = defined('WP_EXTENDED_DEV') && WP_EXTENDED_DEV;

        // Only modify path if not already minified
        if (!$is_dev && !preg_match('/\.min\.(js|css)$/', $path)) {
            $path = str_replace(['.js', '.css'], ['.min.js', '.min.css'], $path);
        }

        if ($is_dev) {
            $file_path = WP_EXTENDED_PATH . $path;
            $version = file_exists($file_path) ? filemtime($file_path) : time();
        } else {
            $version = $version ?? WP_EXTENDED_VERSION;
        }

        if ($type === 'script') {
            wp_register_script(
                $handle,
                WP_EXTENDED_URL . $path,
                $deps,
                $version,
                $in_footer
            );
        } else {
            wp_register_style(
                $handle,
                WP_EXTENDED_URL . $path,
                $deps,
                $version
            );
        }
    }

    /**
     * Register a script with automatic dev/production file selection
     *
     * @param string  $handle    Script handle
     * @param string  $path     Path to the script relative to plugin root
     * @param array   $deps     Array of dependencies
     * @param bool    $in_footer Whether to register in footer
     * @param string  $version   Version string (defaults to plugin version)
     * @return void
     */
    public static function registerScript($handle, $path, $deps = [], $in_footer = true, $override_version = null)
    {
        self::registerAsset($handle, $path, $deps, $override_version, $in_footer, 'script');
    }

    /**
     * Register a stylesheet with automatic dev/production file selection
     *
     * @param string  $handle    Style handle
     * @param string  $path     Path to the stylesheet relative to plugin root
     * @param array   $deps     Array of dependencies
     * @param string  $version   Version string (defaults to plugin version)
     * @return void
     */
    public static function registerStyle($handle, $path, $deps = [], $override_version = null)
    {
        self::registerAsset($handle, $path, $deps, $override_version, false, 'style');
    }

    /**
     * Enqueue a script with automatic dev/production file selection
     *
     * @param string  $handle    Script handle
     * @param string  $path     Path to the script relative to plugin root
     * @param array   $deps     Array of dependencies
     * @param bool    $in_footer Whether to enqueue in footer
     * @param string  $version   Version string (defaults to plugin version)
     * @return void
     */
    public static function enqueueScript($handle, $path = null, $deps = [], $in_footer = true, $override_version = null)
    {
        if ($path !== null) {
            self::registerAsset($handle, $path, $deps, $override_version, $in_footer, 'script');
        }
        wp_enqueue_script($handle);
    }

    /**
     * Enqueue a stylesheet with automatic dev/production file selection
     *
     * @param string  $handle    Style handle
     * @param string  $path     Path to the stylesheet relative to plugin root
     * @param array   $deps     Array of dependencies
     * @param string  $version   Version string (defaults to plugin version)
     * @return void
     */
    public static function enqueueStyle($handle, $path = null, $deps = [], $override_version = null)
    {
        if ($path !== null) {
            self::registerAsset($handle, $path, $deps, $override_version, false, 'style');
        }
        wp_enqueue_style($handle);
    }

    public static function enqueueNotify()
    {
        self::enqueueStyle('wpext-notyf', 'includes/framework/assets/lib/notyf/notyf.min.css');
        self::enqueueScript('wpext-notyf', 'includes/framework/assets/lib/notyf/notyf.min.js');
        self::enqueueStyle('wpext-notify', 'includes/framework/assets/css/notify.css');
        self::enqueueScript('wpext-notify', 'includes/framework/assets/js/notify.js', ['wpext-notyf'], true);
    }

    /**
     * Generate an internal link to a specific tab and optionally a field within the tab
     *
     * @param string $module_id The module ID (e.g. 'smtp-email')
     * @param string $tab_id The tab ID to link to (e.g. 'email_logs')
     * @param string|null $field_id Optional. The field ID to scroll to
     * @param array $args Optional. Additional query arguments
     * @param string $link_text Optional. The text for the link. Defaults to tab name
     * @return string The formatted HTML link
     */
    public static function getInternalLink($module_id, $tab_id, $field_id = null, $args = [], $link_text = '')
    {
        // Get the base module page URL
        $url = self::getModulePageLink($module_id, $args);

        if (!$url) {
            return '';
        }

        // Build the internal link fragment
        $fragment = '#tab-' . $tab_id;
        if ($field_id) {
            $fragment .= '|field-' . $field_id;
        }

        // Combine URL and fragment
        $href = $url . $fragment;

        // If no link text provided, use the tab ID as a fallback
        if (empty($link_text)) {
            $link_text = ucwords(str_replace('_', ' ', $tab_id));
        }

        return sprintf(
            '<a href="%s" class="wpext-internal-link">%s</a>',
            esc_url($href),
            esc_html($link_text)
        );
    }

    /**
     * Checks if the current screen is the block editor.
     *
     * @return bool True if the current screen is the block editor, false otherwise.
     */
    public static function isBlockEditor()
    {
        if (!is_admin()) {
            return false;
        }

        $current_screen = get_current_screen();

        if (!$current_screen) {
            return false;
        }

        // Return false if the method 'is_block_editor' does not exist.
        if (!method_exists($current_screen, 'is_block_editor')) {
            return false;
        }

        return $current_screen->is_block_editor();
    }

    /**
     * Get a value from an array using dot notation.
     *
     * @param array $array The array to search in.
     * @param string|array $key The key to look for (can use dot notation for nested arrays).
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value if found, otherwise the default value.
     */
    public static function getArrayValue($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        // If the key is an array, we'll assume it's an array of keys and return an array of values
        if (is_array($key)) {
            $result = [];
            foreach ($key as $k) {
                $result[$k] = self::getArrayValue($array, $k, $default);
            }
            return $result;
        }

        // If the key contains a dot, we'll assume it's a nested array
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $array;

            foreach ($keys as $segment) {
                if (!is_array($value) || !array_key_exists($segment, $value)) {
                    return $default;
                }
                $value = $value[$segment];
            }

            return $value;
        }

        // If the key doesn't contain a dot, we'll just return the value directly
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Set a value in an array using dot notation.
     *
     * @param array $array The array to modify.
     * @param string $key The key to set (can use dot notation for nested arrays).
     * @param mixed $value The value to set.
     * @return array The modified array.
     */
    public static function setArrayValue(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $current[$segment] = $value;
            } else {
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            }
        }

        return $array;
    }

    /**
     * Check if a key exists in an array using dot notation.
     *
     * @param array $array The array to search in.
     * @param string $key The key to look for (can use dot notation for nested arrays).
     * @return bool True if the key exists, false otherwise.
     */
    public static function hasArrayKey($array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }

        if (strpos($key, '.') === false) {
            return array_key_exists($key, $array);
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Remove a key from an array using dot notation.
     *
     * @param array $array The array to modify.
     * @param string $key The key to remove (can use dot notation for nested arrays).
     * @return array The modified array.
     */
    public static function removeArrayKey(&$array, $key)
    {
        if (is_null($key)) {
            return $array;
        }

        $keys = explode('.', $key);
        $current = &$array;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($current[$key]) || !is_array($current[$key])) {
                return $array;
            }

            $current = &$current[$key];
        }

        $key = array_shift($keys);
        unset($current[$key]);

        return $array;
    }

    /**
     * Normalize a mixed value into a boolean using common truthy strings.
     *
     * Accepts true, 1, '1', 'true', 'yes', 'on' (case-insensitive) as true.
     * Everything else is false.
     *
     * @param mixed $value The value to evaluate
     * @return bool True if the value is a recognized truthy representation
     */
    public static function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * Generate CSS variables
     *
     * @param array $css_variables The CSS variables
     * @return void
     */
    public static function generateCssVariables($css_variables)
    {
        $css = '';

        foreach ($css_variables as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $css .= sprintf('--%s: %s;', $key, $value);
        }

        return sprintf(
            ':root { %s }',
            $css
        );
    }

    /**
     * Get icon SVG content by icon name.
     *
     * @param string $icon_name Icon name (logo, cross, grid, row).
     * @param bool $as_data_uri Optional. Return as base64 data URI instead of raw SVG. Default false.
     * @return string SVG content or data URI.
     */
    public static function getIcon($icon_name, $as_data_uri = false)
    {
        $svg_content = '';

        switch ($icon_name) {
            case 'logo':
                $svg_content = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="500.000000pt" height="500.000000pt" viewBox="0 0 500.000000 500.000000" preserveAspectRatio="xMidYMid meet">
    <g transform="translate(0.000000,500.000000) scale(0.100000,-0.100000)" fill="#4d66d7" stroke="none">
        <path d="M1335 4865 c-631 -100 -1104 -577 -1201 -1210 -20 -132 -20 -2178 0 -2310 98 -638 573 -1113 1211 -1211 72 -11 300 -14 1155 -14 1115 0 1140 1 1310 44 496 127 899 530 1026 1026 43 170 44 195 44 1310 0 855 -3 1083 -14 1155 -98 638 -573 1113 -1211 1211 -126 19 -2198 18 -2320 -1z m1655 -938 c220 -65 398 -244 455 -457 9 -32 15 -101 15 -167 0 -92 -4 -126 -25 -192 -31 -98 -105 -211 -183 -281 -65 -58 -209 -133 -290 -149 -33 -7 -205 -11 -455 -11 -325 0 -414 3 -467 16 -215 50 -400 229 -466 449 -25 83 -25 258 0 340 76 252 281 431 535 466 31 4 229 6 441 5 320 -3 394 -6 440 -19z m-30 -1613 c215 -50 400 -228 466 -449 13 -43 18 -92 18 -175 0 -102 -4 -125 -30 -200 -61 -174 -182 -305 -348 -380 -118 -53 -139 -55 -576 -55 -452 0 -460 1 -599 67 -157 75 -291 237 -336 408 -9 32 -15 101 -15 167 0 92 4 126 25 192 31 98 105 211 183 281 64 57 193 126 274 145 83 21 851 20 938 -1z"/>
        <path d="M2097 3689 c-90 -21 -178 -83 -232 -164 -50 -77 -68 -141 -63 -239 8 -186 141 -336 324 -366 95 -16 706 -12 784 4 247 53 380 320 273 547 -50 108 -142 184 -258 215 -58 16 -763 18 -828 3z m201 -168 c50 -25 109 -93 122 -140 34 -123 -26 -251 -140 -299 -53 -22 -141 -18 -195 9 -145 73 -179 264 -67 382 72 76 187 95 280 48z"/>
        <path d="M2085 2075 c-242 -53 -374 -321 -268 -546 34 -74 105 -150 172 -183 87 -44 131 -48 536 -44 l380 3 58 27 c80 38 128 78 172 143 50 77 68 141 63 239 -8 186 -141 336 -324 366 -92 15 -716 11 -789 -5z m834 -168 c111 -54 161 -194 110 -307 -43 -96 -117 -144 -219 -144 -79 0 -131 24 -184 86 -117 136 -40 354 136 387 56 10 103 4 157 -22z"/>
    </g>
</svg>';
                break;

            case 'cross':
                $svg_content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" color="#000000" fill="none">
    <path d="M4.11654 18.116C3.62839 18.6041 3.62839 19.3956 4.11654 19.8838C4.6047 20.3719 5.39616 20.3719 5.88431 19.8838L19.8843 5.88376C20.3725 5.39561 20.3725 4.60415 19.8843 4.11599C19.3962 3.62784 18.6047 3.62784 18.1165 4.11599L4.11654 18.116Z" fill="currentColor" />
    <path d="M5.88434 4.11599C5.39619 3.62784 4.60473 3.62784 4.11657 4.11599C3.62842 4.60415 3.62842 5.39561 4.11657 5.88376L18.1166 19.8838C18.6047 20.3719 19.3962 20.3719 19.8843 19.8838C20.3725 19.3956 20.3725 18.6041 19.8843 18.116L5.88434 4.11599Z" fill="currentColor" />
</svg>';
                break;

            case 'grid':
                $svg_content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" color="#000000" fill="none">
    <path d="M2 18C2 16.4596 2 15.6893 2.34673 15.1235C2.54074 14.8069 2.80693 14.5407 3.12353 14.3467C3.68934 14 4.45956 14 6 14C7.54044 14 8.31066 14 8.87647 14.3467C9.19307 14.5407 9.45926 14.8069 9.65327 15.1235C10 15.6893 10 16.4596 10 18C10 19.5404 10 20.3107 9.65327 20.8765C9.45926 21.1931 9.19307 21.4593 8.87647 21.6533C8.31066 22 7.54044 22 6 22C4.45956 22 3.68934 22 3.12353 21.6533C2.80693 21.4593 2.54074 21.1931 2.34673 20.8765C2 20.3107 2 19.5404 2 18Z" stroke="currentColor" stroke-width="1.5" />
    <path d="M14 18C14 16.4596 14 15.6893 14.3467 15.1235C14.5407 14.8069 14.8069 14.5407 15.1235 14.3467C15.6893 14 16.4596 14 18 14C19.5404 14 20.3107 14 20.8765 14.3467C21.1931 14.5407 21.4593 14.8069 21.6533 15.1235C22 15.6893 22 16.4596 22 18C22 19.5404 22 20.3107 21.6533 20.8765C21.4593 21.1931 21.1931 21.4593 20.8765 21.6533C20.3107 22 19.5404 22 18 22C16.4596 22 15.6893 22 15.1235 21.6533C14.8069 21.4593 14.5407 21.1931 14.3467 20.8765C14 20.3107 14 19.5404 14 18Z" stroke="currentColor" stroke-width="1.5" />
    <path d="M2 6C2 4.45956 2 3.68934 2.34673 3.12353C2.54074 2.80693 2.80693 2.54074 3.12353 2.34673C3.68934 2 4.45956 2 6 2C7.54044 2 8.31066 2 8.87647 2.34673C9.19307 2.54074 9.45926 2.80693 9.65327 3.12353C10 3.68934 10 4.45956 10 6C10 7.54044 10 8.31066 9.65327 8.87647C9.45926 9.19307 9.19307 9.45926 8.87647 9.65327C8.31066 10 7.54044 10 6 10C4.45956 10 3.68934 10 3.12353 9.65327C2.80693 9.45926 2.54074 9.19307 2.34673 8.87647C2 8.31066 2 7.54044 2 6Z" stroke="currentColor" stroke-width="1.5" />
    <path d="M14 6C14 4.45956 14 3.68934 14.3467 3.12353C14.5407 2.80693 14.8069 2.54074 15.1235 2.34673C15.6893 2 16.4596 2 18 2C19.5404 2 20.3107 2 20.8765 2.34673C21.1931 2.54074 21.4593 2.80693 21.6533 3.12353C22 3.68934 22 4.45956 22 6C22 7.54044 22 8.31066 21.6533 8.87647C21.4593 9.19307 21.1931 9.45926 20.8765 9.65327C20.3107 10 19.5404 10 18 10C16.4596 10 15.6893 10 15.1235 9.65327C14.8069 9.45926 14.5407 9.19307 14.3467 8.87647C14 8.31066 14 7.54044 14 6Z" stroke="currentColor" stroke-width="1.5" />
</svg>';
                break;

            case 'row':
                $svg_content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" color="#000000" fill="none">
    <path d="M3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M2.5 9L21.5 9" stroke="currentColor" stroke-width="1.5" />
    <path d="M2.5 13L21.5 13" stroke="currentColor" stroke-width="1.5" />
    <path d="M2.5 17L21.5 17" stroke="currentColor" stroke-width="1.5" />
    <path d="M12 21.5L12 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
</svg>';
                break;

            default:
                return '';
        }

        if ($as_data_uri) {
            return sprintf('data:image/svg+xml;base64,%s', base64_encode($svg_content));
        }

        return $svg_content;
    }
}
