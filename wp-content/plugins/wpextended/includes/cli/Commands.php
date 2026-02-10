<?php

namespace Wpextended\Includes\Cli;

use Wpextended\Includes\Modules;
use Wpextended\Includes\Utils;

if (!defined('ABSPATH')) {
    // Security: prevent direct access
    exit;
}

/**
 * WP-CLI commands for WP Extended.
 */
class Commands
{
    /**
     * Dispatch top-level command.
     *
     * Usage: wp wpextended <subcommand> <action> [<args>...] [--flags]
     * Subcommands: module|setting
     * Actions (module): list|enable|disable
     * Actions (setting): get|set|update|import|reset
     *
     * ## EXAMPLES
     *     wp wpextended modules list
     *     wp wpextended modules enable smtp-email
     *     wp wpextended settings get global --format=json
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args)
    {
        if (empty($args)) {
            \WP_CLI::error('Missing subcommand. Use: module or setting');
        }

        $sub = array_shift($args);
        switch ($sub) {
            case 'module':
                return $this->dispatchModule($args, $assoc_args);
            case 'setting':
                return $this->dispatchSetting($args, $assoc_args);
            case 'import':
                return $this->importAll($args, $assoc_args);
            case 'export':
                return $this->exportAll($args, $assoc_args);
            case 'group':
                return $this->dispatchGroup($args, $assoc_args);
            case 'license':
                return $this->dispatchLicense($args, $assoc_args);
            case 'status':
                return $this->pluginStatus($assoc_args);
            case 'version':
                return $this->pluginVersion($assoc_args);
            case 'info':
                return $this->pluginInfo($assoc_args);
            // Temporary backward compatibility
            case 'modules':
                return $this->dispatchModule($args, $assoc_args);
            case 'settings':
                return $this->dispatchSetting($args, $assoc_args);
            default:
                \WP_CLI::error('Unknown subcommand. Use: module or setting');
        }
    }

    /**
     * Dispatch module subcommand.
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    private function dispatchModule($args, $assoc_args)
    {
        $action = $args[0] ?? 'list';
        if ($action === 'list') {
            return $this->moduleList([], $assoc_args);
        }
        if ($action === 'enable') {
            return $this->moduleEnable(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'disable') {
            return $this->moduleDisable(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'info') {
            return $this->moduleInfo(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'check-type') {
            return $this->moduleCheckType(array_slice($args, 1));
        }
        \WP_CLI::error('Unknown module action. Use: list, enable, disable');
    }

    private function dispatchGroup($args, $assoc_args)
    {
        $action = $args[0] ?? 'list';
        if ($action === 'list') {
            return $this->groupList($assoc_args);
        }
        if ($action === 'info') {
            return $this->groupInfo(array_slice($args, 1), $assoc_args);
        }
        \WP_CLI::error('Unknown group action. Use: list, info');
    }

    private function dispatchLicense($args, $assoc_args)
    {
        $action = $args[0] ?? null;
        if ($action === 'activate') {
            return $this->licenseActivate(array_slice($args, 1));
        }
        if ($action === 'deactivate') {
            return $this->licenseDeactivate();
        }
        if ($action === 'status') {
            return $this->licenseStatus($assoc_args);
        }
        \WP_CLI::error('Unknown license action. Use: activate, deactivate, status');
    }

    /**
     * Activate license.
     *
     * ## OPTIONS
     * <license_key>
     * : License key
     */
    public function licenseActivate($args)
    {
        list($key) = $args;
        $lic = new \Wpextended\Includes\Licensing\Licensing();
        $method = $lic->activateLicenseKey($key);
        $response = $method;
        if (!$response || empty($response->success)) {
            \WP_CLI::error($response->message ?? 'Activation failed');
        }
        \WP_CLI::success($response->message ?? 'License activated');
    }

    /**
     * Deactivate license.
     */
    public function licenseDeactivate()
    {
        $lic = new \Wpextended\Includes\Licensing\Licensing();
        $response = $lic->deactivateLicense();
        if (!$response || empty($response->success)) {
            \WP_CLI::error($response->message ?? 'Deactivation failed');
        }
        \WP_CLI::success($response->message ?? 'License deactivated');
    }

    /**
     * Show license status.
     */
    public function licenseStatus($assoc_args)
    {
        $lic = new \Wpextended\Includes\Licensing\Licensing();
        $response = $lic->checkLicense(null, true);
        if (!$response) {
            \WP_CLI::error('Failed to fetch license status');
        }
        $data = [
            'success' => (bool) ($response->success ?? false),
            'message' => $response->message ?? '',
            'data' => isset($response->data) ? $response->data : null,
        ];
        \WP_CLI::print_value($data, ['format' => $assoc_args['format'] ?? 'json']);
    }

    /**
     * Dispatch setting subcommand.
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    private function dispatchSetting($args, $assoc_args)
    {
        $action = $args[0] ?? null;
        if ($action === 'get') {
            return $this->settingsGet(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'set') {
            return $this->settingsSet(array_slice($args, 1));
        }
        if ($action === 'update') {
            return $this->settingsUpdate(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'import') {
            return $this->settingsImport(array_slice($args, 1), $assoc_args);
        }
        if ($action === 'reset') {
            return $this->settingsReset(array_slice($args, 1), $assoc_args);
        }
        \WP_CLI::error('Unknown setting action. Use: get, set, update, import, reset');
    }

    /**
     * List modules with filters.
     *
     * ## OPTIONS
     * [--status=<status>]
     * : all|active|inactive|free|pro. Default: all
     * [--group=<group>]
     * : Filter by group (slug)
     * [--has-settings]
     * : Only modules with settings
     * [--no-settings]
     * : Only modules without settings
     * [--format=<format>]
     * : table|json|yaml|csv. Default: table
     */
    public function moduleList($args, $assoc_args)
    {
        $modules = Modules::getAvailableModules();
        $enabled = Modules::getEnabledModules();

        $status = isset($assoc_args['status']) ? strtolower($assoc_args['status']) : 'all';
        $groupFilter = isset($assoc_args['group']) ? strtolower(str_replace([' ', '_'], ['-', '-'], $assoc_args['group'])) : null;
        $wantHasSettings = array_key_exists('has-settings', $assoc_args) ? true : (array_key_exists('no-settings', $assoc_args) ? false : null);

        $items = [];
        foreach ($modules as $module) {
            $isEnabled = in_array($module['id'], $enabled, true);
            $hasSettings = Modules::hasSettings($module['id']);
            $isPro = !empty($module['pro']);

            if ($status === 'active' && !$isEnabled) {
                continue;
            }
            if ($status === 'inactive' && $isEnabled) {
                continue;
            }
            if ($status === 'free' && $isPro) {
                continue;
            }
            if ($status === 'pro' && !$isPro) {
                continue;
            }

            if ($groupFilter) {
                $moduleGroupSlug = strtolower(str_replace([' ', '_'], ['-', '-'], $module['group'] ?? 'other'));
                if ($moduleGroupSlug !== $groupFilter) {
                    continue;
                }
            }

            if ($wantHasSettings === true && !$hasSettings) {
                continue;
            }
            if ($wantHasSettings === false && $hasSettings) {
                continue;
            }

            $items[] = [
                'id' => $module['id'],
                'name' => $module['name'],
                'type' => $isPro ? 'pro' : 'free',
                'settings' => $hasSettings ? 'yes' : 'no',
                'group' => $module['group'] ?? 'Other',
                'status' => $isEnabled ? 'active' : 'inactive',
            ];
        }

        \WP_CLI\Utils\format_items($assoc_args['format'] ?? 'table', $items, ['id', 'name', 'type', 'settings','group', 'status']);
    }

    /**
     * Show information about a module.
     *
     * ## OPTIONS
     * <module_id>
     * : Module ID
     */
    public function moduleInfo($args, $assoc_args)
    {
        list($module_id) = $args;
        $module = Modules::findModule($module_id);
        if (!$module) {
            \WP_CLI::error("Module not found: {$module_id}");
        }
        $enabled = in_array($module_id, Modules::getEnabledModules(), true);
        $hasSettings = Modules::hasSettings($module_id);
        $info = [
            'id' => $module['id'],
            'name' => $module['name'],
            'status' => $enabled ? 'active' : 'inactive',
            'tier' => !empty($module['pro']) ? 'pro' : 'free',
            'group' => $module['group'] ?? 'Other',
            'settings' => $hasSettings ? 'yes' : 'no',
        ];
        \WP_CLI::print_value($info, ['format' => $assoc_args['format'] ?? 'json']);
    }

    /**
     * Check module type (free/pro).
     *
     * ## OPTIONS
     * <module_id>...
     * : One or more module IDs
     */
    public function moduleCheckType($args)
    {
        if (empty($args)) {
            \WP_CLI::error('Provide one or more module IDs.');
        }
        $rows = [];
        foreach ($args as $module_id) {
            $module = Modules::findModule($module_id);
            $rows[] = [
                'id' => $module_id,
                'type' => $module ? (!empty($module['pro']) ? 'pro' : 'free') : 'unknown',
                'exists' => $module ? 'yes' : 'no',
            ];
        }
        \WP_CLI\Utils\format_items('table', $rows, ['id', 'type', 'exists']);
    }

    /**
     * Enable modules by IDs, group, or all.
     *
     * ## OPTIONS
     * [<module_id>...]
     * : One or more module IDs
     * [--group=<group>]
     * : Enable all modules in a group
     * [--all]
     * : Enable all modules
     */
    public function moduleEnable($args, $assoc_args = [])
    {
        $modules = Modules::getInstance();

        // Build targets
        $targets = $args;
        if (!empty($assoc_args['all'])) {
            $targets = array_map(function ($m) {
                return $m['id'];
            }, Modules::getAvailableModules());
        } elseif (!empty($assoc_args['group'])) {
            $grp = strtolower(str_replace([' ', '_'], ['-', '-'], $assoc_args['group']));
            $targets = [];
            foreach (Modules::getAvailableModules() as $m) {
                $g = strtolower(str_replace([' ', '_'], ['-', '-'], $m['group'] ?? 'other'));
                if ($g === $grp) {
                    $targets[] = $m['id'];
                }
            }
        }

        if (empty($targets)) {
            \WP_CLI::error('No modules specified. Provide IDs, --group, or --all.');
        }

        $enabledNow = [];
        $already = [];
        $errors = [];

        foreach (array_unique($targets) as $module_id) {
            $request = new \WP_REST_Request('POST');
            $request->set_param('module_id', $module_id);
            $result = $modules->toggleModule($request);

            if (is_wp_error($result)) {
                $errors[] = $module_id . ': ' . $result->get_error_message();
                continue;
            }

            if (is_array($result) && isset($result['enabled']) && $result['enabled'] === true) {
                $enabledNow[] = $module_id;
                continue;
            }

            $current = Modules::getEnabledModules();
            if (!in_array($module_id, $current, true)) {
                $current[] = $module_id;
                $update = (new \ReflectionClass(Modules::class))->getMethod('updateModuleSettings');
                $update->setAccessible(true);
                $update->invoke($modules, $current, $module_id);
                $enabledNow[] = $module_id;
            } else {
                $already[] = $module_id;
            }
        }

        if (!empty($enabledNow)) {
            \WP_CLI::success('Enabled: ' . implode(', ', $enabledNow));
        }
        if (!empty($already)) {
            \WP_CLI::log('Already enabled: ' . implode(', ', $already));
        }
        if (!empty($errors)) {
            \WP_CLI::warning('Errors: ' . implode(' | ', $errors));
        }
    }

    /**
     * Disable modules by IDs, group, or all.
     *
     * ## OPTIONS
     * [<module_id>...]
     * : One or more module IDs
     * [--group=<group>]
     * : Disable all modules in a group
     * [--all]
     * : Disable all modules
     */
    public function moduleDisable($args, $assoc_args = [])
    {
        $modules = Modules::getInstance();

        // Build targets
        $targets = $args;
        if (!empty($assoc_args['all'])) {
            $targets = array_map(function ($m) {
                return $m['id'];
            }, Modules::getAvailableModules());
        } elseif (!empty($assoc_args['group'])) {
            $grp = strtolower(str_replace([' ', '_'], ['-', '-'], $assoc_args['group']));
            $targets = [];
            foreach (Modules::getAvailableModules() as $m) {
                $g = strtolower(str_replace([' ', '_'], ['-', '-'], $m['group'] ?? 'other'));
                if ($g === $grp) {
                    $targets[] = $m['id'];
                }
            }
        }

        if (empty($targets)) {
            \WP_CLI::error('No modules specified. Provide IDs, --group, or --all.');
        }

        $disabledNow = [];
        $already = [];
        $errors = [];

        foreach (array_unique($targets) as $module_id) {
            $request = new \WP_REST_Request('POST');
            $request->set_param('module_id', $module_id);
            $result = $modules->toggleModule($request);

            if (is_wp_error($result)) {
                $errors[] = $module_id . ': ' . $result->get_error_message();
                continue;
            }

            if (is_array($result) && isset($result['enabled']) && $result['enabled'] === false) {
                $disabledNow[] = $module_id;
                continue;
            }

            $current = Modules::getEnabledModules();
            if (in_array($module_id, $current, true)) {
                $current = array_values(array_diff($current, [$module_id]));
                $update = (new \ReflectionClass(Modules::class))->getMethod('updateModuleSettings');
                $update->setAccessible(true);
                $update->invoke($modules, $current, $module_id);
                $disabledNow[] = $module_id;
            } else {
                $already[] = $module_id;
            }
        }

        if (!empty($disabledNow)) {
            \WP_CLI::success('Disabled: ' . implode(', ', $disabledNow));
        }
        if (!empty($already)) {
            \WP_CLI::log('Already disabled: ' . implode(', ', $already));
        }
        if (!empty($errors)) {
            \WP_CLI::warning('Errors: ' . implode(' | ', $errors));
        }
    }
    /**
     * List available modules with status.
     *
     * ## OPTIONS
     * [--format=<format>]
     * : Render output in a particular format. Default: table. Options: table, json, csv, yaml
     *
     * ## EXAMPLES
     *     wp wpextended modules list
     */
    public function modulesList($args, $assoc_args)
    {
        $modules = Modules::getAvailableModules();
        $enabled = Modules::getEnabledModules();

        $items = [];
        foreach ($modules as $module) {
            $items[] = [
                'id' => $module['id'],
                'name' => $module['name'],
                'enabled' => in_array($module['id'], $enabled, true) ? 'true' : 'false',
                'settings' => Modules::hasSettings($module['id']) ? 'true' : 'false',
                'pro' => !empty($module['pro']) ? 'true' : 'false',
            ];
        }

        \WP_CLI\Utils\format_items($assoc_args['format'] ?? 'table', $items, ['id', 'name', 'enabled', 'settings', 'pro']);
    }

    /**
     * Enable one or more modules by ID.
     *
     * ## OPTIONS
     * <module_id>...
     * : One or more module IDs to enable (e.g. smtp-email quick-search)
     *
     * ## EXAMPLES
     *     wp wpextended modules enable smtp-email
     *     wp wpextended modules enable smtp-email quick-search
     */
    public function modulesEnable($args)
    {
        if (empty($args)) {
            \WP_CLI::error('Please provide at least one module ID.');
        }

        $modules = Modules::getInstance();
        $enabledNow = [];
        $already = [];
        $errors = [];

        foreach ($args as $module_id) {
            $request = new \WP_REST_Request('POST');
            $request->set_param('module_id', $module_id);
            $result = $modules->toggleModule($request);

            if (is_wp_error($result)) {
                $errors[] = $module_id . ': ' . $result->get_error_message();
                continue;
            }

            if (is_array($result) && isset($result['enabled']) && $result['enabled'] === true) {
                $enabledNow[] = $module_id;
                continue;
            }

            // If it was already enabled, ensure persisted state
            $current = Modules::getEnabledModules();
            if (!in_array($module_id, $current, true)) {
                $current[] = $module_id;
                $update = (new \ReflectionClass(Modules::class))->getMethod('updateModuleSettings');
                $update->setAccessible(true);
                $update->invoke($modules, $current, $module_id);
                $enabledNow[] = $module_id;
            } else {
                $already[] = $module_id;
            }
        }

        if (!empty($enabledNow)) {
            \WP_CLI::success('Enabled: ' . implode(', ', $enabledNow));
        }
        if (!empty($already)) {
            \WP_CLI::log('Already enabled: ' . implode(', ', $already));
        }
        if (!empty($errors)) {
            \WP_CLI::warning('Errors: ' . implode(' | ', $errors));
        }
    }

    /**
     * Disable one or more modules by ID.
     *
     * ## OPTIONS
     * <module_id>...
     * : One or more module IDs to disable (e.g. smtp-email quick-search)
     *
     * ## EXAMPLES
     *     wp wpextended modules disable smtp-email
     *     wp wpextended modules disable smtp-email quick-search
     */
    public function modulesDisable($args)
    {
        if (empty($args)) {
            \WP_CLI::error('Please provide at least one module ID.');
        }

        $modules = Modules::getInstance();
        $disabledNow = [];
        $already = [];
        $errors = [];

        foreach ($args as $module_id) {
            $request = new \WP_REST_Request('POST');
            $request->set_param('module_id', $module_id);
            $result = $modules->toggleModule($request);

            if (is_wp_error($result)) {
                $errors[] = $module_id . ': ' . $result->get_error_message();
                continue;
            }

            if (is_array($result) && isset($result['enabled']) && $result['enabled'] === false) {
                $disabledNow[] = $module_id;
                continue;
            }

            // Ensure disabled in settings if toggle reported enabled
            $current = Modules::getEnabledModules();
            if (in_array($module_id, $current, true)) {
                $current = array_values(array_diff($current, [$module_id]));
                $update = (new \ReflectionClass(Modules::class))->getMethod('updateModuleSettings');
                $update->setAccessible(true);
                $update->invoke($modules, $current, $module_id);
                $disabledNow[] = $module_id;
            } else {
                $already[] = $module_id;
            }
        }

        if (!empty($disabledNow)) {
            \WP_CLI::success('Disabled: ' . implode(', ', $disabledNow));
        }
        if (!empty($already)) {
            \WP_CLI::log('Already disabled: ' . implode(', ', $already));
        }
        if (!empty($errors)) {
            \WP_CLI::warning('Errors: ' . implode(' | ', $errors));
        }
    }

    /**
     * Get settings for a module or global.
     *
     * ## OPTIONS
     * <context>
     * : Module ID or 'global'
     * [--key=<key>]
     * : Dot-notated key. If omitted, returns all settings
     * [--format=<format>]
     * : table|json|yaml|csv (defaults to json for full settings)
     *
     * ## EXAMPLES
     *     wp wpextended settings get smtp-email --key=from_email
     *     wp wpextended settings get global --format=json
     */
    public function settingsGet($args, $assoc_args)
    {
        list($context) = $args;
        $key = $assoc_args['key'] ?? null;

        if ($key !== null) {
            $value = Utils::getSetting($context, $key, null);
            if (is_array($value) || is_object($value)) {
                \WP_CLI::print_value($value, ['format' => $assoc_args['format'] ?? 'json']);
            } else {
                \WP_CLI::line((string) $value);
            }
            return;
        }

        $settings = Utils::getSettings($context);
        \WP_CLI::print_value($settings, ['format' => $assoc_args['format'] ?? 'json']);
    }

    /**
     * Set a settings value for a module or global.
     *
     * ## OPTIONS
     * <context>
     * : Module ID or 'global'
     * <key>
     * : Dot-notated key to set
     * <value>
     * : Value to set (JSON accepted)
     *
     * ## EXAMPLES
     *     wp wpextended settings set smtp-email from_email "admin@example.com"
     *     wp wpextended settings set global enable_all_submodules true
     */
    public function settingsSet($args)
    {
        list($context, $key, $value) = $args;

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $value = $decoded;
        }

        $ok = Utils::updateSetting($context, $key, $value);
        if (!$ok) {
            \WP_CLI::error('Failed to update setting.');
        }
        \WP_CLI::success('Setting updated.');
    }
    /**
     * Update settings for a module or global from a file or JSON, merging with existing values.
     *
     * ## OPTIONS
     * <context>
     * : Module ID or 'global'
     * [--file=<path>]
     * : Path to JSON file containing settings
     * [--json=<json>]
     * : JSON string containing settings
     * [--replace]
     * : Replace existing settings instead of merging
     *
     * ## EXAMPLES
     *     wp wpextended settings update global --file=./settings.json
     *     wp wpextended settings update smtp-email --json='{"from_email":"admin@example.com"}'
     */
    public function settingsUpdate($args, $assoc_args)
    {
        if (empty($args)) {
            \WP_CLI::error('Missing context. Provide a module ID or "global".');
        }
        $context = $args[0];
        $data = null;

        if (!empty($assoc_args['json'])) {
            $json = $assoc_args['json'];
            $decoded = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                \WP_CLI::error('Invalid JSON provided via --json.');
            }
            $data = $decoded;
        } elseif (!empty($assoc_args['file'])) {
            $file = $assoc_args['file'];

            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            if (!$wp_filesystem || !$wp_filesystem->exists($file)) {
                \WP_CLI::error('File not found or filesystem unavailable.');
            }
            $contents = $wp_filesystem->get_contents($file);
            if ($contents === false) {
                \WP_CLI::error('Could not read file.');
            }
            $decoded = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                \WP_CLI::error('Invalid JSON in file.');
            }
            $data = $decoded;
        } else {
            \WP_CLI::error('Provide either --json or --file.');
        }

        $replace = !empty($assoc_args['replace']);
        if ($replace) {
            $new_settings = $data;
        } else {
            $current = Utils::getSettings($context);
            // Merge recursively with new values taking precedence
            $new_settings = array_replace_recursive(is_array($current) ? $current : [], $data);
        }

        $ok = Utils::updateSettings($context, $new_settings);
        if (!$ok) {
            \WP_CLI::error('Failed to update settings.');
        }
        \WP_CLI::success('Settings updated.');
    }

    /**
     * Reset settings for a module or core, or for all modules.
     *
     * ## OPTIONS
     * [<context>]
     * : Module ID or 'core'. If omitted, requires --all
     * [--all]
     * : Reset all module settings (does not affect 'core' unless specified)
     * [--confirm]
     * : Confirm the reset action (required in non-interactive contexts)
     *
     * ## EXAMPLES
     *     wp wpextended settings reset core --confirm
     *     wp wpextended settings reset smtp-email --confirm
     *     wp wpextended settings reset --all --confirm
     */
    public function settingsReset($args, $assoc_args)
    {
        $has_all = !empty($assoc_args['all']);
        $context = $args[0] ?? null;

        if (!$has_all && !$context) {
            \WP_CLI::error('Provide a context (module ID or "core") or use --all.');
        }

        if (empty($assoc_args['confirm'])) {
            \WP_CLI::error('Destructive action. Re-run with --confirm to proceed.');
        }

        if ($has_all) {
            $modules = Modules::getAvailableModules();
            $reset = [];
            foreach ($modules as $module) {
                $ok = Utils::deleteSettings($module['id']);
                if ($ok) {
                    $reset[] = $module['id'];
                }
            }
            if (!empty($reset)) {
                \WP_CLI::success('Reset settings for modules: ' . implode(', ', $reset));
            } else {
                \WP_CLI::log('No module settings were reset.');
            }
            return;
        }

        // Single context
        $ok = Utils::deleteSettings($context);
        if (!$ok) {
            \WP_CLI::error('Failed to reset settings.');
        }
        \WP_CLI::success(sprintf('Settings reset for "%s".', $context));
    }

    /**
     * Wrapper: wpextended settings get
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public function get($args, $assoc_args)
    {
        return $this->settingsGet($args, $assoc_args);
    }

    /**
     * Wrapper: wpextended settings set
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public function set($args, $assoc_args = [])
    {
        return $this->settingsSet($args);
    }

    /**
     * Wrapper: wpextended settings import
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public function import($args, $assoc_args)
    {
        return $this->settingsImport($args, $assoc_args);
    }
    /**
     * Import settings from a JSON file into a context.
     *
     * ## OPTIONS
     * <context>
     * : Module ID or 'core'
     * <file>
     * : Path to JSON file
     * [--merge]
     * : Merge with existing settings (default replaces)
     *
     * ## EXAMPLES
     *     wp wpextended settings import core ./defaults.json --merge
     */
    public function settingsImport($args, $assoc_args)
    {
        list($context, $file) = $args;

        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            \WP_Filesystem();
        }

        if (!$wp_filesystem || !$wp_filesystem->exists($file)) {
            \WP_CLI::error('File not found or filesystem unavailable.');
        }

        $contents = $wp_filesystem->get_contents($file);
        if ($contents === false) {
            \WP_CLI::error('Could not read file.');
        }

        $data = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            \WP_CLI::error('Invalid JSON.');
        }

        if (!empty($assoc_args['merge'])) {
            $current = Utils::getSettings($context);
            $data = array_replace_recursive($current, $data);
        }

        $ok = Utils::updateSettings($context, $data);
        if (!$ok) {
            \WP_CLI::error('Failed to import settings.');
        }

        \WP_CLI::success('Settings imported.');
    }

    /**
     * List groups with stats.
     */
    public function groupList($assoc_args)
    {
        $modules = Modules::getAvailableModules();
        $enabled = Modules::getEnabledModules();
        $stats = [];
        foreach ($modules as $m) {
            $g = $m['group'] ?? 'Other';
            if (!isset($stats[$g])) {
                $stats[$g] = ['group' => $g, 'modules' => 0, 'active' => 0];
            }
            $stats[$g]['modules']++;
            if (in_array($m['id'], $enabled, true)) {
                $stats[$g]['active']++;
            }
        }
        \WP_CLI\Utils\format_items($assoc_args['format'] ?? 'table', array_values($stats), ['group', 'modules', 'active']);
    }

    /**
     * Group info and modules.
     */
    public function groupInfo($args, $assoc_args)
    {
        list($group) = $args;
        $groupSlug = strtolower($group);
        $modules = Modules::getAvailableModules();
        $enabled = Modules::getEnabledModules();
        $rows = [];
        foreach ($modules as $m) {
            $g = strtolower($m['group'] ?? 'other');
            if ($g !== $groupSlug) {
                continue;
            }
            $rows[] = [
                'id' => $m['id'],
                'name' => $m['name'],
                'status' => in_array($m['id'], $enabled, true) ? 'active' : 'inactive',
                'tier' => !empty($m['pro']) ? 'pro' : 'free',
                'settings' => Modules::hasSettings($m['id']) ? 'yes' : 'no',
            ];
        }
        if (empty($rows)) {
            \WP_CLI::error('Group not found or has no modules.');
        }
        \WP_CLI\Utils\format_items($assoc_args['format'] ?? 'table', $rows, ['id', 'name', 'status', 'tier', 'settings']);
    }

    /**
     * Import global and module settings from a single JSON (file or inline).
     *
     * JSON structure example:
     * {
     *   "global": { ... },
     *   "modules": {
     *     "smtp-email": { ... },
     *     "maintenance-mode": { ... }
     *   }
     * }
     *
     * ## OPTIONS
     * [<file>]
     * : Path to JSON file
     * [--json=<json>]
     * : Inline JSON payload
     * [--overwrite]
     * : Replace existing settings (default merges)
     * [--dry-run]
     * : Show changes without applying
     *
     * ## EXAMPLES
     *     wp wpextended import ./everything.json
     *     wp wpextended import ./everything.json --overwrite
     *     wp wpextended import ./everything.json --dry-run
     *     wp wpextended import --json='{"global":{},"modules":{}}'
     */
    public function importAll($args, $assoc_args)
    {
        $jsonInline = $assoc_args['json'] ?? null;
        $data = null;

        if ($jsonInline !== null) {
            $decoded = json_decode($jsonInline, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                \WP_CLI::error('Invalid JSON provided via --json.');
            }
            $data = $decoded;
        } else {
            $file = $args[0] ?? null;
            if (!$file) {
                \WP_CLI::error('Provide a JSON file path or use --json.');
            }

            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            if (!$wp_filesystem || !$wp_filesystem->exists($file)) {
                \WP_CLI::error('File not found or filesystem unavailable.');
            }

            $contents = $wp_filesystem->get_contents($file);
            if ($contents === false) {
                \WP_CLI::error('Could not read file.');
            }

            $data = json_decode($contents, true);
        }
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            \WP_CLI::error('Invalid JSON.');
        }

        $overwrite = !empty($assoc_args['overwrite']);
        $dryRun = !empty($assoc_args['dry-run']);

        $updatedModules = [];
        $hadGlobal = false;
        $updatedEnabled = false;

        // Normalize supported shapes:
        // 1) Object: { global: {...}, modules: { id: {...} } }
        // 2) Array of { section, data } entries from UI export
        $globalPayload = null;
        $modulesSettingsPayload = null;
        $modulesEnabledPayload = null;

        if (isset($data['global']) || isset($data['modules'])) {
            $globalPayload = isset($data['global']) && is_array($data['global']) ? $data['global'] : null;
            if (isset($data['modules']) && is_array($data['modules'])) {
                // Either { id: settings } or { enabled: [...], settings: {...} }
                if (isset($data['modules']['settings']) || isset($data['modules']['enabled'])) {
                    $modulesSettingsPayload = isset($data['modules']['settings']) && is_array($data['modules']['settings']) ? $data['modules']['settings'] : null;
                    $modulesEnabledPayload = isset($data['modules']['enabled']) && is_array($data['modules']['enabled']) ? $data['modules']['enabled'] : null;
                } else {
                    $modulesSettingsPayload = $data['modules'];
                }
            }
        } elseif (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0]['section'])) {
            foreach ($data as $entry) {
                if (!is_array($entry) || !isset($entry['section'])) {
                    continue;
                }
                $section = $entry['section'];
                $payload = $entry['data'] ?? null;
                if ($section === 'global' && is_array($payload)) {
                    $globalPayload = $payload;
                }
                if ($section === 'modules' && is_array($payload)) {
                    if (isset($payload['settings']) && is_array($payload['settings'])) {
                        $modulesSettingsPayload = $payload['settings'];
                    }
                    if (isset($payload['enabled']) && is_array($payload['enabled'])) {
                        $modulesEnabledPayload = $payload['enabled'];
                    }
                }
            }
        }

        // Apply global
        if (is_array($globalPayload)) {
            $hadGlobal = true;
            if ($dryRun) {
                \WP_CLI::log('[dry-run] Would ' . ($overwrite ? 'replace' : 'merge into') . ' global settings');
            } else {
                $new = $globalPayload;
                if (!$overwrite) {
                    $current = Utils::getSettings('global');
                    $new = array_replace_recursive(is_array($current) ? $current : [], $new);
                }
                Utils::updateSettings('global', $new);
            }
        }

        // Apply module settings
        if (is_array($modulesSettingsPayload)) {
            foreach ($modulesSettingsPayload as $module_id => $module_settings) {
                if (!is_array($module_settings)) {
                    continue;
                }
                $exists = is_array(Modules::findModule($module_id));
                if (!$exists) {
                    \WP_CLI::warning("Skipping unknown module: {$module_id}");
                    continue;
                }
                if ($dryRun) {
                    \WP_CLI::log('[dry-run] Would ' . ($overwrite ? 'replace' : 'merge into') . " module '{$module_id}' settings");
                    $updatedModules[] = $module_id;
                    continue;
                }
                $new = $module_settings;
                if (!$overwrite) {
                    $current = Utils::getSettings($module_id);
                    $new = array_replace_recursive(is_array($current) ? $current : [], $new);
                }
                if (Utils::updateSettings($module_id, $new)) {
                    $updatedModules[] = $module_id;
                }
            }
        }

        // Apply enabled modules list
        if (is_array($modulesEnabledPayload)) {
            if ($dryRun) {
                \WP_CLI::log('[dry-run] Would set enabled modules: ' . implode(', ', $modulesEnabledPayload));
                $updatedEnabled = true;
            } else {
                $current = Utils::getSettings('modules');
                if (!is_array($current)) {
                    $current = [];
                }
                $current['modules'] = array_values($modulesEnabledPayload);
                $updatedEnabled = Utils::updateSettings('modules', $current);
            }
        }

        if ($dryRun) {
            \WP_CLI::success('Dry run complete.');
            return;
        }

        if ($hadGlobal) {
            \WP_CLI::success('Updated global settings');
        }
        if (!empty($updatedModules)) {
            \WP_CLI::success('Updated modules: ' . implode(', ', $updatedModules));
        }
        if ($updatedEnabled) {
            \WP_CLI::success('Updated enabled modules');
        }
        if (!$hadGlobal && empty($updatedModules) && !$updatedEnabled) {
            \WP_CLI::log('No settings found to import.');
        }
    }

    /**
     * Export configuration to stdout or file.
     *
     * ## OPTIONS
     * [--include=<parts>]
     * : Comma-separated list: global,modules. Default: global,modules
     * [--modules=<ids>]
     * : Comma-separated module IDs to include (only when including modules)
     * [--file=<path>]
     * : If provided, writes JSON to file instead of stdout
     */
    public function exportAll($args, $assoc_args)
    {
        $include = isset($assoc_args['include']) ? array_filter(array_map('trim', explode(',', $assoc_args['include']))) : ['global', 'modules'];
        $limitModules = isset($assoc_args['modules']) ? array_filter(array_map('trim', explode(',', $assoc_args['modules']))) : null;

        $export = [];

        if (in_array('global', $include, true)) {
            $export[] = [
                'section' => 'global',
                'data' => Utils::getSettings('global'),
            ];
        }

        if (in_array('modules', $include, true)) {
            // Collect enabled and settings
            $enabled = Modules::getEnabledModules();
            $settings = [];
            $available = Modules::getAvailableModules();
            $validIds = array_map(function ($m) {
                return $m['id'];
            }, $available);
            $targetIds = $limitModules ? array_values(array_intersect($limitModules, $validIds)) : $validIds;
            foreach ($targetIds as $id) {
                $settings[$id] = Utils::getSettings($id);
            }
            $export[] = [
                'section' => 'modules',
                'data' => [
                    'enabled' => $enabled,
                    'settings' => $settings,
                ],
            ];
        }

        $json = wp_json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (!empty($assoc_args['file'])) {
            $file = $assoc_args['file'];
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            if (!$wp_filesystem) {
                \WP_CLI::error('Filesystem unavailable.');
            }
            if (false === $wp_filesystem->put_contents($file, $json, FS_CHMOD_FILE)) {
                \WP_CLI::error('Failed to write export file.');
            }
            \WP_CLI::success('Export written to ' . $file);
            return;
        }

        \WP_CLI::line($json);
    }

    /**
     * Show plugin status summary.
     */
    public function pluginStatus($assoc_args)
    {
        $modules = Modules::getAvailableModules();
        $enabled = Modules::getEnabledModules();
        $groups = [];
        foreach ($modules as $m) {
            $g = $m['group'] ?? 'Other';
            if (!isset($groups[$g])) {
                $groups[$g] = ['group' => $g, 'active' => 0, 'total' => 0];
            }
            $groups[$g]['total']++;
            if (in_array($m['id'], $enabled, true)) {
                $groups[$g]['active']++;
            }
        }

        $summary = [
            'plugin' => defined('WP_EXTENDED_PRO') ? 'WP Extended Pro' : 'WP Extended',
            'version' => WP_EXTENDED_VERSION,
            'active_modules' => count($enabled),
            'total_modules' => count($modules),
        ];

        \WP_CLI::log(sprintf('%s v%s | Active: %d/%d modules', $summary['plugin'], $summary['version'], $summary['active_modules'], $summary['total_modules']));
        $rows = [];
        foreach ($groups as $group) {
            $rows[] = [
                'group' => $group['group'],
                'active' => sprintf('%d/%d', $group['active'], $group['total']),
            ];
        }
        \WP_CLI\Utils\format_items($assoc_args['format'] ?? 'table', $rows, ['group', 'active']);
    }

    /**
     * Show plugin version; optionally check for updates.
     */
    public function pluginVersion($assoc_args)
    {
        $check = !empty($assoc_args['check-updates']);
        if (!$check) {
            \WP_CLI::log('Plugin: WP Extended' . (defined('WP_EXTENDED_PRO') ? ' Pro' : ''));
            \WP_CLI::log('Version: ' . WP_EXTENDED_VERSION);
            return;
        }

        $lic = new \Wpextended\Includes\Licensing\Licensing();
        $data = (new \ReflectionClass($lic))->getMethod('handleCheckVersion')->invoke($lic);
        $payload = method_exists($data, 'get_data') ? $data->get_data() : null;
        if (!$payload || empty($payload['success'])) {
            \WP_CLI::error('Failed to check for updates.');
        }
        \WP_CLI::line(wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Show plugin info (basic).
     */
    public function pluginInfo($assoc_args)
    {
        $info = [
            'name' => defined('WP_EXTENDED_PRO') ? 'WP Extended Pro' : 'WP Extended',
            'version' => WP_EXTENDED_VERSION,
            'type' => defined('WP_EXTENDED_PRO') ? 'pro' : 'free',
        ];
        \WP_CLI::print_value($info, ['format' => $assoc_args['format'] ?? 'json']);
    }
}
