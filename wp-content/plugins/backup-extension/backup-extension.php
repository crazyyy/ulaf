<?php
/**
 * Plugin Name: Backup Extension
 * Plugin URI: https://wordpress.org/plugins/backup-extension/
 * Description: A plugin to generate backups of installed plugins. You can take backup of your installed plugins.
 * Version: 1.0.1
 * Author: Shehab Mahamud
 * Author URI: https://github.com/shehab24
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; 
}

class Backup_Extension_Plugin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'process_backup_submission'));
    }

    public function register_menu()
    {
        add_menu_page(
            'Backup Extension',
            'Backup Extension',
            'manage_options',
            'backup-extension-backup',
            array($this, 'backup_extension_page'),
            'dashicons-download',
            99
        );
    }

    public function backup_extension_page()
    {
        $backupExtension = get_plugins();

        ?>
        <div class="wrap">
            <h1> Backup Your Extension</h1>

            <form method="post">
                <table class="form-table">
                    <tbody>
                        <?php foreach ($backupExtension as $slug => $data) {
                            $directory = dirname($slug);
                            $this->render_backup_option($slug, $directory, $data['Name']);
                        } ?>
                    </tbody>
                </table>

                <p class="submit">
                    <?php wp_nonce_field('backup_extension_nonce', 'backup_extension_nonce'); ?>
                    <input type="submit" name="backup_submit" class="button-primary" value="Generate Backup">
                </p>
            </form>
        </div>
        <?php
    }

    private function render_backup_option($slug, $directory, $name)
    {
        ?>
        <tr>
            <td>
                <label for="<?php echo esc_attr($slug); ?>">
                    <input type="radio" name="selected_extension" id="<?php echo esc_attr($slug); ?>"
                        value="<?php echo esc_attr($directory); ?>">
                    <?php echo esc_html($name); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    public function process_backup_submission()
    {
        if (isset($_POST['backup_submit']) && isset($_POST['selected_extension']) && isset($_POST['backup_extension_nonce'])) {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['backup_extension_nonce'], 'backup_extension_nonce')) {
                wp_die('Access denied.', 'Error', array('response' => 403));
            }
    
            $selected_extension = sanitize_text_field($_POST['selected_extension']);
            $extension_directory = WP_CONTENT_DIR . '/plugins/' . $selected_extension;
    
            if (is_dir($extension_directory)) {
                $backup_filename = $selected_extension . '_backup_' . date('Y-m-d') . '.zip';
                $backup_filepath = WP_CONTENT_DIR . '/' . $backup_filename;
    
                $this->zipDirectory($extension_directory, $backup_filepath);
    
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
                header('Content-Length: ' . filesize($backup_filepath));
    
                readfile($backup_filepath);
                unlink($backup_filepath);
    
                exit;
            }
        }
    }
    
    
    private function zipDirectory($source, $destination)
    {
        $rootPath = realpath($source);
    
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::SELF_FIRST
            );
    
            foreach ($files as $file) {
                $file = realpath($file);
                $localPath = substr($file, strlen($rootPath) + 1);
    
                if (is_file($file)) {
                    $zip->addFile($file, $localPath);
                }
            }
    
            $zip->close();
        }
    }
    
}

new Backup_Extension_Plugin();