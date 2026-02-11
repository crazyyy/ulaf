<?php

// Sanitize export fields
function ext_info_exporter_sanitize_export_fields($input) {
    if (!is_array($input)) {
        return array();
    }
    return array_map('sanitize_text_field', $input);
}

// Register settings
add_action('admin_init', 'ext_info_exporter_register_settings');
function ext_info_exporter_register_settings()
{
    register_setting('ext_info_exporter_settings_group', 'ext_info_exporter_export_fields', array(
        'sanitize_callback' => 'ext_info_exporter_sanitize_export_fields'
    ));
}

// Render settings page
function ext_info_exporter_settings_page()
{
    // Ensure the value returned from get_option() is always an array
    $export_fields = get_option('ext_info_exporter_export_fields', array());

    if (!is_array($export_fields)) {
        $export_fields = array(); // Ensure it's an array
    }
?>
    <div class="wrap ext-ie-container">
        <h1 class="ext-ie-h2">Extension Info Exporter Settings</h1>

        <form method="post" action="options.php" class="ext-ie-settings-form" onsubmit="return validateForm()">
            <?php settings_fields('ext_info_exporter_settings_group'); ?>
            <?php do_settings_sections('ext_info_exporter_settings_group'); ?>

            <div class="ext-ie-cards-grid">
            <div class="ext-ie-card" id="ext-ie-card-fields">
                <div class="ext-ie-card__header"><strong>Select Fields to Export</strong></div>
                <div class="ext-ie-card__body">
                    <p class="ext-ie-note">Plugin Name will always be included.</p>
                    <fieldset class="ext-ie-fieldset ext-ie-grid">
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="name" checked disabled />
                            Plugin Name
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="version"
                                <?php checked(in_array('version', $export_fields)); ?> />
                            Plugin Version
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="latest_version"
                                <?php checked(in_array('latest_version', $export_fields)); ?> />
                            Latest Available Version
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="slug"
                                <?php checked(in_array('slug', $export_fields)); ?> />
                            Plugin Slug
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="author"
                                <?php checked(in_array('author', $export_fields)); ?> />
                            Plugin Author
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="author_url"
                                <?php checked(in_array('author_url', $export_fields)); ?> />
                            Author URL
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="needs_update"
                                <?php checked(in_array('needs_update', $export_fields)); ?> />
                            Needs Update
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="status"
                                <?php checked(in_array('status', $export_fields)); ?> />
                            Active/Inactive Status
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="requires_wp_version"
                                <?php checked(in_array('requires_wp_version', $export_fields)); ?> />
                            Requires WordPress Version
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="compatible_up_to"
                                <?php checked(in_array('compatible_up_to', $export_fields)); ?> />
                            Compatible up to
                        </label>
                        <label>
                            <input type="checkbox" name="ext_info_exporter_export_fields[]" value="requires_php_version"
                                <?php checked(in_array('requires_php_version', $export_fields)); ?> />
                            Requires PHP Version
                        </label>
                    </fieldset>
                    <div class="ext-ie-actions" style="margin-top:12px;">
                        <?php submit_button('Save Changes', 'primary', 'submit', false); ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="ext-ie-card" id="ext-ie-card-export">
            <div class="ext-ie-card__header"><strong>Export Plugins</strong></div>
            <div class="ext-ie-card__body">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return validateExportForm()">
            <input type="hidden" name="action" value="ext_info_exporter_export">
            <?php wp_nonce_field('ext_info_exporter_export_action', 'ext_info_exporter_export_nonce'); ?>

            <div class="ext-ie-row-2">
                <div>
                    <h3>Export Type</h3>
                    <p class="ext-ie-note">Select which plugins to include in the export.</p>
                    <label for="ext_info_exporter_export_type" class="screen-reader-text">Export Type</label>
                    <select name="ext_info_exporter_export_type" id="ext_info_exporter_export_type">
                        <option value="all" selected>Export All Plugins</option>
                        <option value="active">Export Only Active Plugins</option>
                        <option value="inactive">Export Only Inactive Plugins</option>
                        <option value="needs_update">Export Plugins That Need Updates</option>
                    </select>
                    
                </div>
                <div>
                    <h3>Export Format</h3>
                    <p class="ext-ie-note">Choose the file format for the export.</p>
                    <select name="ext_info_exporter_format">
                        <option value="csv" selected>CSV</option>
                        <option value="json">JSON</option>
                        <option value="txt">TXT</option>
                        <option value="xml">XML</option>
                    </select>
                </div>
            </div>

            <h3 style="margin-top:16px;">File Name</h3>
            <p class="ext-ie-note">Use variables: {date}, {time}, {site_name}, {export_type}, {format}. Example: my-plugins-{date}</p>
            <input type="text" id="ext_info_exporter_filename_template" name="ext_info_exporter_filename_template" value="{site_name}_{date}" style="min-width:320px;" />
            <button type="button" class="button" id="ext_ie_reset_filename">Reset</button>
            <div class="ext-ie-filename-preview" id="ext_ie_filename_preview"></div>

            <div class="ext-ie-actions" style="margin-top:12px;">
                <button type="submit" class="button button-primary">Export Plugins Details</button>
                <span class="ext-ie-muted">The correct extension is added automatically.</span>
            </div>
        </form>
            </div>
        </div>
            </div> <!-- .ext-ie-cards-grid -->
    </div>
<?php
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'ext_info_exporter_enqueue_admin_scripts');
function ext_info_exporter_enqueue_admin_scripts($hook)
{
    // Only load the script on the Extension Info Exporter settings page
    if ($hook !== 'toplevel_page_extension-info-exporter') {
        return;
    }

    // Define the path to the admin JavaScript file
    $script_path = plugin_dir_path(__FILE__) . '../admin/js/admin-settings.js';
    $style_path = plugin_dir_path(__FILE__) . '../admin/css/admin-settings.css';


    // Enqueue the admin JavaScript file
    wp_enqueue_script(
        'ext-info-exporter-admin-js', // Handle
        plugin_dir_url(__FILE__) . '../admin/js/admin-settings.js', // File path
        array('jquery'), // Dependencies
        filemtime($script_path), // Use file modification time as version
        true // Load in the footer
    );

    // Enqueue admin CSS
    wp_enqueue_style(
        'ext-info-exporter-admin-css',
        plugin_dir_url(__FILE__) . '../admin/css/admin-settings.css',
        array(),
        file_exists($style_path) ? filemtime($style_path) : null
    );

    // Localize data for JS (site name)
    wp_localize_script('ext-info-exporter-admin-js', 'ExtIE', array(
        'siteName' => wp_parse_url(home_url(), PHP_URL_HOST),
    ));
}

?>
