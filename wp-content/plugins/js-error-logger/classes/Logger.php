<?php

namespace JSERRLOG;
if (!defined('ABSPATH')) {
    exit;
}

class Logger
{
    public const FILE_NAME = 'js-errors';

    public function __construct()
    {
        add_action('jserrlog-cleanup', [$this, 'maintain_log']);
    }


    public static function create_log_directory(): void
    {
        if (is_dir(JSERRLOG_LOG_DIR)) {
            return;
        }
        wp_mkdir_p(JSERRLOG_LOG_DIR);
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->chmod(JSERRLOG_LOG_DIR, 0755);
    }

    public static function delete_log_directory(): void
    {
        $files = glob(JSERRLOG_LOG_DIR . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                wp_delete_file($file);
            }
        }
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        WP_Filesystem();
        global $wp_filesystem;
        if ($wp_filesystem && $wp_filesystem->is_dir(JSERRLOG_LOG_DIR)) {
            $wp_filesystem->rmdir(JSERRLOG_LOG_DIR, true);
        }
    }

    public function error(string $message): void
    {
        $file = $this->get_file_path();

        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        WP_Filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem) {
            return; // fail silently like WordPress loves to do
        }

        // Ensure directory exists
        $dir = dirname($file);
        if (!$wp_filesystem->is_dir($dir)) {
            $wp_filesystem->mkdir($dir, FS_CHMOD_DIR);
        }

        // Ensure file exists
        if (!$wp_filesystem->exists($file)) {
            $wp_filesystem->put_contents($file, '', FS_CHMOD_FILE);
        }

        // Fetch old log
        $existing = $wp_filesystem->get_contents($file);
        if ($existing === false) {
            $existing = '';
        }

        // Append new log entry
        $new = $existing . $message;

        // Save
        $wp_filesystem->put_contents($file, $new, FS_CHMOD_FILE);
    }

    public function get_file_content(): string
    {
        $filePath = $this->get_file_path();

        if (!file_exists($filePath)) {
            return '';
        }
        return file_get_contents($filePath);
    }

    public function get_log_content($max = 0, $ignoredData = ['agents' => [], 'scripts' => [], 'errors' => [], 'combined' => []]): array
    {
        $matches = $this->get_errors();
        $errors = [];
        $i = 1;
        foreach ($matches as $error) {
            $error = $this->get_exploded_error($error);
            if (Plugin::is_ignored($ignoredData['agents'], $error['agent'])) {
                continue;
            }
            if (Plugin::is_ignored($ignoredData['scripts'], json_decode($error['urls'])[0])) {
                continue;
            }
            [$fullError] = Plugin::error_texts($error);
            if (Plugin::is_ignored($ignoredData['errors'], $fullError)) {
                continue;
            }
            if (Plugin::is_multi_ignored($ignoredData['combined'], [$fullError, json_decode($error['urls'])[0]])) {
                continue;
            }
            $errors[] = $error;
            if ($max > 0 && $i == $max) {
                break;
            }
            $i++;
        }
        return $errors;
    }

    public function get_errors(): array
    {
        $content = $this->get_file_content();
        preg_match_all('#\[TIME\]+[\S\s]+[\r\n]{2}#U', $content, $matches);

        if (empty($matches)) {
            return [];
        }
        return array_reverse($matches[0]);
    }

    public function maintain_log(): void
    {
        $errors = $this->get_errors();
        do_action('jserrlog_before_log_maintenance', $errors);
        $maxErrors = (int)apply_filters('jserrlog_max_log_entries', 100);
        if (count($errors) > $maxErrors) {
            $errors = array_slice($errors, 0, $maxErrors);
        }
        $errors = array_reverse($errors);
        $string = implode('', $errors);
        $this->purge_log();
        $this->error($string);
    }

    public function get_exploded_error($err): array
    {
        $vars = ['agent', 'err', 'col', 'line', 'urls', 'error', 'time'];
        $error = [];
        foreach ($vars as $var) {
            $err = explode('[' . strtoupper($var) . '] ', $err);
            $val = $err[1] ?? '';
            $error[$var] = trim($val);
            $err = $err[0];
        }
        return $error;
    }

    public function purge_log(): void
    {
        $file = $this->get_file_path();

        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        WP_Filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem) {
            return;
        }
        // Make sure directory exists
        $dir = dirname($file);
        if (!$wp_filesystem->is_dir($dir)) {
            $wp_filesystem->mkdir($dir, FS_CHMOD_DIR);
        }

        // Overwrite with empty contents
        $wp_filesystem->put_contents($file, '', FS_CHMOD_FILE);
    }


    public function get_file_path(): string
    {
        $fileName = self::FILE_NAME;
        if (is_multisite()) {
            $fileName .= "-" . get_current_blog_id();
        }
        return JSERRLOG_LOG_DIR . $fileName . '.log';
    }
}