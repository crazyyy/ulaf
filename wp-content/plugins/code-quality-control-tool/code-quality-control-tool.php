<?php
/*
Plugin Name: Code Quality Control Tool
Description: Trace all PHP error types. Creates logs file. Useful for PHP code analytics.
Version: 2.2
Author: GoodCodeTeam
License: GPLv2
*/

define("cqctphp_plg_version", '2.2');

if (is_admin()) {
    // Register admin styles
    add_action('admin_init', 'cqctphp_admin_init');
    function cqctphp_admin_init() {
        wp_register_style('cqctphp_Load_CSS', plugins_url('css/style.css', __FILE__));
    }

    wp_enqueue_style('cqctphp_Load_CSS');

    // Add admin bar menu
    add_action('admin_bar_menu', 'cqctphp_frontend_shortcut', 95);
    function cqctphp_frontend_shortcut() {
        global $wp_admin_bar;

        $errors_count = PHPCodeControl_general::GetErrorCount();
        $alert_html = $errors_count > 0 ? ' <span class="numcirc">' . $errors_count . '</span>' : '';

        $wp_admin_bar->add_menu([
            'id' => 'php-code-control-menu',
            'class' => 'dashicons-before dashicons-dashboard',
            'title' => 'PHP Code Control' . $alert_html,
            'href' => get_admin_url(null, 'options-general.php?page=php-code-control-settings'),
            'meta' => ['tabindex' => 0, 'class' => 'code-control-top-toolbar'],
        ]);
    }

    // Handle log download
    add_action('init', 'cqctphp_download_file');
    function cqctphp_download_file() {
        if (isset($_POST['action']) && $_POST['action'] == 'download_log' && check_admin_referer('cqctphp_save_settings_BF944B')) {
            PHPCodeControl_general::Download_Log_File();
            exit;
        }
    }

    // Add settings page
    add_action('admin_menu', 'cqctphp_register_page_settings');
    function cqctphp_register_page_settings() {
        add_options_page('PHP Code Control', 'PHP Code Control', 'manage_options', 'php-code-control-settings', 'cqctphp_page_settings');
    }

    // Settings page content
    function cqctphp_page_settings() {
        // Patch wp-config if needed
        PHPCodeControl_general::Patch_WPconfig_file(true);

        $show_message = false;
        $message_text = '';
        $support_link = 'https://www.safetybis.com/contact/?' . get_site_url();

        // Handle form submissions
        if (isset($_POST['action']) && check_admin_referer('cqctphp_save_settings_BF944B')) {
            $action = isset($_POST['action']) ? trim($_POST['action']) : '';

            switch ($action) {
                case 'clear_log':
                    PHPCodeControl_general::Clear_Log_File();
                    $show_message = true;
                    $message_text = 'Log cleared.';
                    break;

                case 'save_settings':
                    $settings = [
                        'is_active' => intval($_POST['is_active']),
                        'errortypes' => implode(",", $_POST['errortypes']),
                        'filer_by_ip' => explode("\n", sanitize_textarea_field($_POST['filer_by_ip'])),
                        'logsize' => intval($_POST['logsize']),
                        'object_check' => $_POST['object_check'],
                        'skip_dups' => intval($_POST['skip_dups']),
                    ];

                    // Handle object_check
                    if (in_array("ALL", $settings['object_check'])) {
                        $settings['object_check'] = ["ALL"];
                    }
                    $settings['object_check'] = array_values(array_filter($settings['object_check']));
                    if (count($settings['object_check']) == 0) {
                        $settings['object_check'] = ["ALL"];
                    }

                    // Validate IPs
                    if (count($settings['filer_by_ip'])) {
                        $valid_ip_addresses = [];
                        foreach ($settings['filer_by_ip'] as $ip) {
                            $ip = trim($ip);
                            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                                $valid_ip_addresses[$ip] = $ip;
                            }
                        }
                        $settings['filer_by_ip'] = array_values($valid_ip_addresses);
                    }

                    PHPCodeControl_general::SaveSettings($settings);
                    $show_message = true;
                    $message_text = 'Settings saved.';
                    break;
            }
        }

        $settings = PHPCodeControl_general::LoadSettings();
        ?>
        <div class="wrap">
            <!-- Header with icon -->
            <h1 class="cqctphp-header">
                <span class="dashicons dashicons-admin-tools"></span>
                PHP Code Control <?php echo $settings['is_active'] == 0 ? '<span class="numcirc">Logger is disabled</span>' : '<span class="numcirc greennumcirc">Logger is active</span>'; ?>
            </h1>

            <!-- Show success message -->
            <?php if ($show_message): ?>
                <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
                    <p><strong><?php echo $message_text; ?></strong></p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
            <?php endif; ?>

            <!-- Info card -->
            <div class="cqctphp-card">
                <div class="cqctphp-info-block">
                    <div class="cqctphp-info-item">
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    </div>
                    <div class="cqctphp-info-item">
                        <p><strong>Total Issues:</strong> <?php $errors_count = PHPCodeControl_general::GetErrorCount(); echo $errors_count > 0 ? '<span class="numcirc">' . $errors_count . '</span>' : $errors_count; ?></p>
                    </div>
                </div>
                <p>Logger ver.: <?php echo cqctphp_plg_version;?></p>
                <?php if ($errors_count > 0): ?>
                    <p><strong>Note:</strong> Errors/Warnings/Notices indicate issues. Address them to avoid bugs.</p>
                    <p>Contact your developers or <a href="<?php echo $support_link; ?>" target="_blank">SafetyBis.com</a></p>
                    <p><a href="<?php echo $support_link; ?>" target="_blank"><img src="<?php echo plugins_url('images/livechat.png', __FILE__); ?>"/></a></p>
                <?php endif; ?>
            </div>


            <!-- Settings section -->
            <h2 class="cqctphp-header">
                <span class="dashicons dashicons-admin-settings"></span>
                Settings
            </h2>

            <form method="post" action="options-general.php?page=php-code-control-settings">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">Error Logger</th>
                        <td>
                            <select name="is_active">
                                <option <?php if ($settings['is_active'] == 0) echo 'selected="selected"'; ?> value="0">Not active</option>
                                <option <?php if ($settings['is_active'] == 1) echo 'selected="selected"'; ?> value="1">Active</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Error types to trace</th>
                        <td>
                            <?php
                            $list = 'E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_CORE_ERROR,E_CORE_WARNING,E_COMPILE_ERROR,E_COMPILE_WARNING,E_USER_ERROR,E_USER_WARNING,E_USER_NOTICE,E_STRICT,E_RECOVERABLE_ERROR,E_DEPRECATED,E_USER_DEPRECATED';
                            $list = explode(",", $list);
                            $selected_list = explode(",", $settings['errortypes']);
                            foreach ($list as $v) {
                                ?>
                                <label for="type_<?php echo $v; ?>">
                                    <input class="errortypes <?php echo $v; ?>" name="errortypes[]" type="checkbox" id="type_<?php echo $v; ?>" value="<?php echo $v; ?>" <?php if (in_array($v, $selected_list)) echo 'checked="checked"'; ?>>
                                    PHP error type: <?php echo $v; ?>
                                </label><br>
                                <?php
                            }
                            ?>
                            <p>
                                <a href="javascript:;" onclick="ManageErrorTypes('uncheck')">Uncheck All</a>  |  
                                <a href="javascript:;" onclick="ManageErrorTypes('all')">Select All</a>  |  
                                <a href="javascript:;" onclick="ManageErrorTypes('error')">Select ERROR only</a>  |  
                                <a href="javascript:;" onclick="ManageErrorTypes('warning')">Select WARNING only</a>  |  
                                <a href="javascript:;" onclick="ManageErrorTypes('notice')">Select NOTICE only</a>
                            </p>
                            <p>Error handling is the process of catching errors raised by your program and then taking appropriate action. If you would handle errors properly then it may lead to many unforeseen consequences.</p>
                            <p>For more information please read <a href="https://www.php.net/manual/en/errorfunc.constants.php" target="_blank">https://www.php.net/manual/en/errorfunc.constants.php</a></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">File size of log file (Mb)</th>
                        <td>
                            <input name="logsize" type="number" step="1" min="0" value="<?php echo $settings['logsize']; ?>" class="small-text"> Mb (0 for unlimited)
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Error Dups</th>
                        <td>
                            <select name="skip_dups">
                                <option <?php if ($settings['skip_dups'] == 0) echo 'selected="selected"'; ?> value="0">Log all errors</option>
                                <option <?php if ($settings['skip_dups'] == 1) echo 'selected="selected"'; ?> value="1">Log uniq errors only (skip dups)</option>
                            </select>
                            <p>Skip dups - will skip logging if error is already logged before</p>
                            <p class="cqctphp-info">When enabled, the "Skip dups" option prevents the plugin from logging duplicate errors that have already been recorded in the log file. This feature checks if an error with the same message, file, and line number already exists in the log before adding a new entry. If a match is found, the error is skipped, reducing redundant entries. This is particularly useful for minimizing the volume of data in the log file, making it easier to analyze and focus on unique issues without sifting through repeated errors. For example, if a specific warning occurs multiple times during a single page load, only the first occurrence will be logged, keeping the log file concise and manageable.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Filter by IP</th>
                        <td>
                            <textarea name="filer_by_ip" id="filer_by_ip" rows="5" cols="50" class="large-text code"><?php if (isset($settings['filer_by_ip']) && is_array($settings['filer_by_ip'])) echo implode("\n", $settings['filer_by_ip']); ?></textarea>
                            <p>It will save logs for specific IP addresses only (one IP per row)</p>
                            <p>Your current IP is <b><?php echo $_SERVER['REMOTE_ADDR']; ?></b> <a href="javascript:;" onclick="AddMyIP()">[Add to List]</a></p>
                            <p class="cqctphp-info">The "Filter by IP" option allows you to specify a list of IP addresses for which the plugin will save error logs, with one IP address per row. When this filter is active, the plugin will only log errors triggered by requests from the listed IP addresses, ignoring all others. This is especially convenient when performing analysis on a live website with active visitors, as it enables you to isolate and focus on errors related to your own actions. By excluding logs from other users, you can avoid irrelevant data and concentrate on debugging issues specific to your testing or development activities, making the troubleshooting process more efficient and targeted.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Filter by Object</th>
                        <td>
                            <select name="object_check[]" id="object_check" onchange="ManageThemesPlugins()">
                                <option <?php if (in_array("ALL", $settings['object_check'])) echo 'selected="selected"'; ?> value="ALL">Trace everything (plugins, themes and WordPress core files)</option>
                                <option <?php if (!in_array("ALL", $settings['object_check'])) echo 'selected="selected"'; ?> value="">Trace selected objects only</option>
                            </select>
                        </td>
                    </tr>

                    <tr class="selected_object" <?php if (in_array("ALL", $settings['object_check'])) echo 'style="display:none"'; ?>>
                        <th scope="row">Trace WordPress Themes</th>
                        <td>
                            <?php
                            $list = PHPCodeControl_general::Get_List_WP_Themes();
                            foreach ($list as $v) {
                                ?>
                                <label for="type_<?php echo $v['theme_slug']; ?>">
                                    <input class="obj_themes" name="object_check[]" type="checkbox" id="type_<?php echo $v['theme_slug']; ?>" value="<?php echo $v['theme_path']; ?>" <?php if (in_array($v['theme_path'], $settings['object_check'])) echo 'checked="checked"'; ?>>
                                    <?php echo $v['theme_name'] . ' (' . $v['theme_slug'] . ')'; ?>
                                </label><br>
                                <?php
                            }
                            ?>
                            <p>
                                <a href="javascript:;" onclick="ManageThemes('uncheck')">Uncheck All</a>  |  
                                <a href="javascript:;" onclick="ManageThemes('all')">Select All</a>
                            </p>
                            <p>It will save logs for selected themes only</p>
                        </td>
                    </tr>

                    <tr class="selected_object" <?php if (in_array("ALL", $settings['object_check'])) echo 'style="display:none"'; ?>>
                        <th scope="row">Trace WordPress Plugins</th>
                        <td>
                            <?php
                            $list = PHPCodeControl_general::Get_List_WP_Plugins();
                            foreach ($list as $v) {
                                ?>
                                <label for="type_<?php echo $v['plugin_slug']; ?>">
                                    <input class="obj_plugins" name="object_check[]" type="checkbox" id="type_<?php echo $v['plugin_slug']; ?>" value="<?php echo $v['plugin_path']; ?>" <?php if (in_array($v['plugin_path'], $settings['object_check'])) echo 'checked="checked"'; ?>>
                                    <?php echo $v['plugin_name'] . ' (' . $v['plugin_slug'] . ')'; ?>
                                </label><br>
                                <?php
                            }
                            ?>
                            <p>
                                <a href="javascript:;" onclick="ManagePlugins('uncheck')">Uncheck All</a>  |  
                                <a href="javascript:;" onclick="ManagePlugins('all')">Select All</a>
                            </p>
                            <p>It will save logs for selected plugins only</p>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- JavaScript for form interactions -->
                <script>
                    // Add IP to textarea
                    function AddMyIP() {
                        var v = jQuery("#filer_by_ip").val();
                        var sep = v != "" ? "\n" : "";
                        jQuery("#filer_by_ip").val(v + sep + "<?php echo $_SERVER['REMOTE_ADDR']; ?>");
                    }

                    // Manage error type checkboxes
                    function ManageErrorTypes(t) {
                        if (t == 'uncheck') jQuery(".errortypes").prop("checked", false);
                        if (t == 'all') jQuery(".errortypes").prop("checked", true);
                        if (t == 'error') {
                            jQuery(".errortypes").prop("checked", false);
                            jQuery(".E_ERROR,.E_PARSE,.E_CORE_ERROR,.E_COMPILE_ERROR,.E_USER_ERROR,.E_STRICT,.E_RECOVERABLE_ERROR").prop("checked", true);
                        }
                        if (t == 'warning') {
                            jQuery(".errortypes").prop("checked", false);
                            jQuery(".E_WARNING,.E_CORE_WARNING,.E_COMPILE_WARNING,.E_USER_WARNING").prop("checked", true);
                        }
                        if (t == 'notice') {
                            jQuery(".errortypes").prop("checked", false);
                            jQuery(".E_NOTICE,.E_USER_NOTICE").prop("checked", true);
                        }
                    }

                    // Toggle themes/plugins visibility
                    function ManageThemesPlugins() {
                        var v = jQuery("#object_check").val();
                        if (v == 'ALL') {
                            ManageThemes('uncheck');
                            ManagePlugins('uncheck');
                            jQuery(".selected_object").hide();
                        } else {
                            ManageThemes('all');
                            ManagePlugins('all');
                            jQuery(".selected_object").show();
                        }
                    }

                    // Manage theme checkboxes
                    function ManageThemes(t) {
                        if (t == 'uncheck') jQuery(".obj_themes").prop("checked", false);
                        if (t == 'all') jQuery(".obj_themes").prop("checked", true);
                    }

                    // Manage plugin checkboxes
                    function ManagePlugins(t) {
                        if (t == 'uncheck') jQuery(".obj_plugins").prop("checked", false);
                        if (t == 'all') jQuery(".obj_plugins").prop("checked", true);
                    }

                    // Handle form actions with animation
                    function FormActions(v) {
                        jQuery('#action_value').val(v);
                        jQuery('#FormActions').submit();
                        jQuery('.cqctphp-action-button').addClass('button-clicked');
                        setTimeout(() => jQuery('.cqctphp-action-button').removeClass('button-clicked'), 300);
                    }
                </script>

                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></p>
                <input type="hidden" name="action" value="save_settings">
                <?php wp_nonce_field('cqctphp_save_settings_BF944B'); ?>
            </form>

            <hr />

            <!-- Logs actions section -->
            <h2 class="cqctphp-header">
                <span class="dashicons dashicons-text-page"></span>
                Logs Actions
            </h2>

            <?php
            $log_file_info = PHPCodeControl_general::GetLogFileInfo();
            $is_disabled = $log_file_info['filesize'] == 0;
            $html_label = $is_disabled ? '' : ' (' . $log_file_info['filesize_mb'] . ' Mb)';
            ?>

            <a href="javascript:;" <?php if (!$is_disabled) echo 'onclick="FormActions(\'download_log\');"'; ?> class="button action cqctphp-action-button" <?php if ($is_disabled) echo 'disabled="disabled"'; ?>>
                <span class="dashicons dashicons-download"></span> Download Log<?php echo $html_label; ?>
            </a>
            <a href="javascript:;" <?php if (!$is_disabled) echo 'onclick="FormActions(\'clear_log\');"'; ?> class="button action cqctphp-action-button" <?php if ($is_disabled) echo 'disabled="disabled"'; ?>>
                <span class="dashicons dashicons-trash"></span> Clear Log
            </a>

            <form method="post" id="FormActions" action="options-general.php?page=php-code-control-settings">
                <input type="hidden" name="action" id="action_value" value="">
                <?php wp_nonce_field('cqctphp_save_settings_BF944B'); ?>
            </form>

            <!-- Log table -->
            <h3 class="cqctphp-header">
                <span class="dashicons dashicons-list-view"></span>
                Latest 100 Lines of Log File
            </h3>

            <?php
            $log_file = PHPCodeControl_general::GetLogFile();
            $lines = file_exists($log_file) ? file($log_file) : [];
            $total_lines = count($lines);

            if ($total_lines > 0) {
                if ($total_lines > 100) {
                    ?>
                    <p>If you need to see all <?php echo $total_lines; ?> lines of log, please download the log file.</p>
                    <?php
                }
                ?>
                <table class="wp-list-table widefat striped">
                    <thead>
                    <th><span>Date / IP</span></th>
                    <th><span>Type / Line</span></th>
                    <th><span>Message / File / URL</span></th>
                    </thead>
                    <tbody id="the-list">
                    <?php
                    $lines = array_reverse($lines);
                    $i = 100;
                    foreach ($lines as $line) {
                        $line = explode("| ", $line);
                        ?>
                        <tr>
                            <td><?php echo $line[0] . "<br>" . $line[1]; ?></td>
                            <td><?php echo str_replace("Type:", "<b>Type:</b>", $line[2]) . "<br><br>" . str_replace("Line:", "<b>Line:</b>", $line[5]); ?></td>
                            <td><?php echo str_replace("Msg:", "<b>Msg:</b>", $line[3]) . "<br><br>" . str_replace("File:", "<b>File:</b>", $line[4]) . "<br><br>" . str_replace("URL:", "<b>URL:</b>", $line[6]); ?></td>
                        </tr>
                        <?php
                        $i--;
                        if ($i == 0) break;
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            } else {
                ?>
                <p>Log file is empty</p>
                <?php
            }
            ?>
        </div>
        <?php
    }

    // Uninstall hook
    register_uninstall_hook(__FILE__, 'cqctphp_delete_plugin');
    function cqctphp_delete_plugin() {
        $log_folder = WP_CONTENT_DIR . '/code-quality-logs';
        PHPCodeControl_general::ensurePrivateDir($log_folder);

        $log_file = $log_folder . '/_php_errors.log';
        if (file_exists($log_file)) unlink($log_file);
    }

    // Activation hook
    function cqctphp_plugin_activation() {
        PHPCodeControl_general::SaveSettings();
        PHPCodeControl_general::Patch_WPconfig_file(true);
        add_option('cqctphp_activation_redirect', true);
    }
    register_activation_hook(__FILE__, 'cqctphp_plugin_activation');

    // Deactivation hook
    function cqctphp_plugin_deactivation() {
        PHPCodeControl_general::Patch_WPconfig_file(false);
    }
    register_deactivation_hook(__FILE__, 'cqctphp_plugin_deactivation');

    // Redirect after activation
    function cqctphp_activation_do_redirect() {
        if (get_option('cqctphp_activation_redirect', false)) {
            delete_option('cqctphp_activation_redirect');
            wp_redirect("options-general.php?page=php-code-control-settings");
            exit;
        }
    }
    add_action('admin_init', 'cqctphp_activation_do_redirect');
}

class PHPCodeControl_general {
    // Clear log files
    public static function Clear_Log_File() {
        $log_file = self::GetLogFile();
        if (file_exists($log_file)) unlink($log_file);

        $log_counter_file = self::GetErrorCounterFile();
        if (file_exists($log_counter_file)) unlink($log_counter_file);
    }

    // Download log file
    public static function Download_Log_File() {
        $log_file = self::GetLogFile();
        if (file_exists($log_file)) {
            $name = '_php_errors_' . time() . '.log';
            $type = 'text/plain';
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . $name . '";');
            header('Content-Type: ' . $type);
            header('Content-Length: ' . filesize($log_file));

            ob_clean();
            flush();
            @readfile($log_file);
            exit;
        }
    }

    // Get error count
    public static function GetErrorCount() {
        $counter_file = self::GetErrorCounterFile();
        return file_exists($counter_file) ? filesize($counter_file) : 0;
    }

    /**
     * Creates a directory (if not exists) and places a .htaccess file inside
     * to block direct access to its contents.
     *
     * @param string $dir Absolute or relative directory path
     * @param int $mode Permissions for the directory (default 0755)
     * @throws RuntimeException on creation/write errors
     */
    public static function ensurePrivateDir(string $dir, int $mode = 0755): void
    {
        // Normalize the path
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        // If directory does not exist — create it (including intermediate directories)
        if (!is_dir($dir)) {
            // Keep in mind that real permissions are affected by umask: $mode & ~umask()
            if (!mkdir($dir, $mode, true) && !is_dir($dir)) {
                throw new RuntimeException("Failed to create directory: {$dir}");
            }
        }

        // Check if directory is writable
        if (!is_writable($dir)) {
            throw new RuntimeException("Directory exists but is not writable: {$dir}");
        }

        // Path to .htaccess
        $htaccessPath = $dir . DIRECTORY_SEPARATOR . '.htaccess';

        // .htaccess content for Apache 2.4+ (modern syntax)
        // Alternative (for older Apache): "Deny from all"
        $htaccess = <<<HTA
# Deny direct access to this directory
# Apache 2.4+:
Require all denied

# Backwards compatibility:
<IfModule !mod_authz_core.c>
    Deny from all
</IfModule>
HTA;

        // Write the file (overwrite if exists)
        if (file_put_contents($htaccessPath, $htaccess, LOCK_EX) === false) {
            throw new RuntimeException("Failed to write .htaccess to: {$htaccessPath}");
        }

        // (Optional) set secure permissions on .htaccess
        @chmod($htaccessPath, 0644);
    }

    // Get log file path
    public static function GetLogFile() {
        $log_folder = WP_CONTENT_DIR . '/code-quality-logs';
        self::ensurePrivateDir($log_folder);
        return $log_folder . '/_php_errors.log';
    }

    // Get counter file path
    public static function GetErrorCounterFile() {
        $log_folder = WP_CONTENT_DIR . '/code-quality-logs';
        self::ensurePrivateDir($log_folder);
        return $log_folder . '/_php_errors.count.log';
    }

    // Get settings file path
    public static function GetSettingsFile() {
        $log_folder = WP_CONTENT_DIR . '/code-quality-logs';
        self::ensurePrivateDir($log_folder);
        return $log_folder . '/_php_code_control.ini';
    }

    // Get log file info
    public static function GetLogFileInfo() {
        $log_file = self::GetLogFile();
        $log_filesize = file_exists($log_file) ? filesize($log_file) : 0;
        $log_filesize_mb = round($log_filesize / 1024 / 1024, 2);

        return [
            'file' => $log_file,
            'filesize' => $log_filesize,
            'filesize_mb' => $log_filesize_mb,
        ];
    }

    // Save settings to ini file
    public static function SaveSettings($settings = []) {
        $blank_settings = [
            'is_active' => 1,
            'errortypes' => 'E_ERROR,E_WARNING,E_PARSE,E_NOTICE,E_CORE_ERROR,E_CORE_WARNING,E_COMPILE_ERROR,E_COMPILE_WARNING,E_USER_ERROR,E_USER_WARNING,E_USER_NOTICE,E_STRICT,E_RECOVERABLE_ERROR,E_DEPRECATED,E_USER_DEPRECATED',
            'filer_by_ip' => [],
            'logsize' => 1,
            'object_check' => ['ALL'],
            'skip_dups' => 0,
        ];

        foreach ($settings as $k => $v) {
            $blank_settings[$k] = $v;
        }

        $fp = fopen(self::GetSettingsFile(), 'w');
        fwrite($fp, self::build_ini_string($blank_settings));
        fclose($fp);
    }

    // Load settings from ini file
    public static function LoadSettings() {
        $settings_file = self::GetSettingsFile();
        if (!file_exists($settings_file)) self::SaveSettings();

        $settings = parse_ini_file($settings_file);
        if (!isset($settings['object_check']) || !is_array($settings['object_check'])) {
            $settings['object_check'] = ["ALL"];
        }

        return $settings;
    }

    // Build ini string from array
    public static function build_ini_string(array $a) {
        $out = '';
        $sectionless = '';
        foreach ($a as $rootkey => $rootvalue) {
            if (is_array($rootvalue)) {
                $indexed_root = array_keys($rootvalue) == range(0, count($rootvalue) - 1);
                if (!$indexed_root) $out .= PHP_EOL . "[$rootkey]" . PHP_EOL;
                foreach ($rootvalue as $key => $value) {
                    if (is_array($value)) {
                        $indexed_item = array_keys($value) == range(0, count($value) - 1);
                        foreach ($value as $subkey => $subvalue) {
                            if ($indexed_item) $subkey = "";
                            $out .= "{$key}[$subkey] = $subvalue" . PHP_EOL;
                        }
                    } else {
                        if ($indexed_root) {
                            $sectionless .= "{$rootkey}[]=\"$value\"" . PHP_EOL;
                        } else {
                            $out .= "$key=\"$value\"" . PHP_EOL;
                        }
                    }
                }
            } else {
                $sectionless .= "$rootkey = $rootvalue" . PHP_EOL;
            }
        }
        return $sectionless . $out;
    }

    // Patch wp-config.php
    public static function Patch_WPconfig_file($action = true) {
        if (!defined('DIRSEP')) {
            define('DIRSEP', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
        }

        $file = dirname(__FILE__) . DIRSEP . "error_logger.php";
        $integration_code = '<?php /* PHP Code Control A8E15CA27213-START */if(file_exists("' . $file . '"))include_once("' . $file . '");/* PHP Code Control A8E15CA27213-END */?>';

        $root_path = defined('ABSPATH') && strlen(ABSPATH) >= 8 ? ABSPATH : dirname(dirname(dirname(dirname(__FILE__))));
        $filename = $root_path . DIRSEP . 'wp-config.php';
        $handle = fopen($filename, "r");
        if ($handle === false) return false;
        $contents = fread($handle, filesize($filename));
        if ($contents === false) return false;
        fclose($handle);

        $pos_code = stripos($contents, $integration_code);

        if ($action === false) {
            $contents = str_replace($integration_code, "", $contents);
        } else {
            if ($pos_code !== false) {
                return true;
            } else {
                $contents = $integration_code . $contents;
            }
        }

        $handle = fopen($filename, 'w');
        if ($handle === false) {
            if (chmod($filename, 0666) === false) return false;
            $handle = fopen($filename, 'w');
            if ($handle === false) return false;
        }

        $status = fwrite($handle, $contents);
        if ($status === false) return false;
        fclose($handle);

        return true;
    }

    // Get list of WP themes
    public static function Get_List_WP_Themes() {
        $result = [];
        $themes = wp_get_themes();
        foreach ($themes as $theme_slug => $theme_block) {
            $theme_info = wp_get_theme($theme_slug);
            $result[] = [
                'theme_name' => $theme_info->get('Name'),
                'theme_path' => str_replace(ABSPATH, "", $theme_info->theme_root . '/' . $theme_slug),
                'theme_slug' => $theme_slug,
            ];
        }
        return $result;
    }

    // Get list of WP plugins
    public static function Get_List_WP_Plugins() {
        $result = [];
        $plugins = get_plugins();
        foreach ($plugins as $plugin_file => $plugin_block) {
            $result[] = [
                'plugin_name' => $plugin_block['Name'],
                'plugin_path' => str_replace(ABSPATH, "", WP_CONTENT_DIR . '/plugins/' . dirname($plugin_file)),
                'plugin_slug' => dirname($plugin_file),
            ];
        }
        return $result;
    }
}