<?php
/**
 * Security: Stop direct access to this file.
 */
if (!defined('ABSPATH')) exit;

/**
 * Security: Check if the user is an administrator.
 */
if (!current_user_can('manage_options')) {
    wp_die(__('Unauthorized access.'));
}

/**
 * Logic: Look through installed plugins to see if a specific one exists.
 */
function zamfpi_is_installed($slug) {
    $plugins = get_plugins();
    foreach ($plugins as $path => $data) {
        if (strpos($path, $slug . '/') === 0) return true;
    }
    return false;
}

/**
 * Logic: Start processing when the form is submitted.
 */
if (isset($_POST['zamfpi_trigger'])) {
    
    // Security: Verify the request is legitimate (Nonce check).
    check_admin_referer('zamfpi_secure_install', 'zamfpi_token');
    
    // Database: Save to your ORIGINAL option name 'za_favorite_plugins'
    $list = trim($_POST['plugin_stack']);
    update_option('za_favorite_plugins', $list);

    // Logic: Run the installation if the 'Execute' button was clicked.
    if ($_POST['zamfpi_trigger'] == 'execute') {
        
        $stack = preg_split("/\r\n|\n|\r/", $list);
        
        echo '<div class="wrap"><h2>Deployment Progress</h2><hr>';
        
        if (ob_get_level() > 0) ob_end_flush();
        flush();

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        foreach ($stack as $item) {
            $item = trim($item);
            if (empty($item)) continue;

            $slug = str_replace(['https://wordpress.org/plugins/', 'http://wordpress.org/plugins/', 'https://www.wordpress.org/plugins/', 'http://www.wordpress.org/plugins/'], '', $item);
            $slug = trim($slug, '/');

            if (zamfpi_is_installed($slug)) {
                echo "<p>📦 <strong>$slug:</strong> Already active. Skipping.</p>";
                continue;
            }

            echo "<div id='loader-$slug' class='zamfpi-loading'>
                    <div class='spinner-box'></div> ⚡ Processing: <strong>$slug</strong>...
                  </div>";
            flush(); 

            $api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));

            if (is_wp_error($api)) {
                echo "<script>document.getElementById('loader-$slug').innerHTML = \"❌ <strong style='color:#d63638;'>$slug:</strong> Not found.\";</script>";
            } else {
                $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
                $status = $upgrader->install($api->download_link);

                if ($status === true) {
                    wp_cache_flush(); 
                    $all_plugins = get_plugins();
                    foreach ($all_plugins as $file => $data) {
                        if (strpos($file, $slug . '/') === 0) {
                            activate_plugin($file);
                            echo "<script>document.getElementById('loader-$slug').innerHTML = \"<span style='color:#00a32a;'>✓ <strong>" . esc_js($api->name) . "</strong> Deployed & Activated.</span>\";</script>";
                            break;
                        }
                    }
                }
            }
            flush();
        }
        echo "<hr><p><strong>Batch Operation Complete.</strong> <a href=''>Back to Dashboard</a></p></div>";
        return; 
    }
}

// Database: Get the saved plugin list from the ORIGINAL option name
$stored_list = get_option('za_favorite_plugins', '');
?>

<style>
    .zamfpi-wrap { background: #fff; padding: 30px; border: 1px solid #c3c4c7; border-radius: 8px; max-width: 850px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .zamfpi-header { border-bottom: 2px solid #f0f0f1; margin-bottom: 20px; padding-bottom: 10px; }
    textarea { width: 100%; font-family: monospace; padding: 15px; border: 1px solid #8c8f94; border-radius: 6px; font-size: 14px; }
    .btn-row { margin-top: 20px; display: flex; gap: 12px; }
    .btn-primary { background: #2271b1; color: #fff; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
    .btn-secondary { background: #fff; color: #2271b1; border: 1px solid #2271b1; padding: 12px 30px; border-radius: 4px; cursor: pointer; }
    .zamfpi-loading { margin: 10px 0; padding: 10px; background: #f6f7f7; border-radius: 4px; display: flex; align-items: center; gap: 10px; }
    .spinner-box { width: 16px; height: 16px; border: 2px solid #2271b1; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<div class="wrap">
    <div class="zamfpi-wrap">
        <div class="zamfpi-header">
            <h1>ZA My Favorite Plugins Installer <small>v2.0</small></h1>
            <p class="description">High-speed automation tool to download, install, and activate custom plugin collections in one click.</p>
        </div>
        
        <form method="post">
            <?php wp_nonce_field('zamfpi_secure_install', 'zamfpi_token'); ?>
            <p><strong>Input Plugin Slugs or WordPress URLs:</strong></p>
            <textarea name="plugin_stack" rows="10" placeholder="e.g., akismet"><?php echo esc_textarea($stored_list); ?></textarea>
            
            <div class="btn-row">
                <button type="submit" name="zamfpi_trigger" value="save" class="btn-secondary">Save Stack</button>
                <button type="submit" name="zamfpi_trigger" value="execute" class="btn-primary">Start Installation</button>
            </div>
        </form>
    </div>
</div>