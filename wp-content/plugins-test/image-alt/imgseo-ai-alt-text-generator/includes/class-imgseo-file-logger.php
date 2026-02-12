<?php
/**
 * File-based logging system for ImgSEO bulk operations
 *
 * Provides file-based logging to avoid database bloat.
 * Logs are stored in wp-content/uploads/imgseo-logs/
 *
 * @package ImgSEO
 * @since 2.3.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ImgSEO_File_Logger
 * Handles file-based logging for bulk operations
 */
class ImgSEO_File_Logger {

    /**
     * Singleton instance
     *
     * @var ImgSEO_File_Logger
     */
    private static $instance = null;

    /**
     * Log directory path
     *
     * @var string
     */
    private $log_dir;

    /**
     * Days to keep log files before cleanup
     *
     * @var int
     */
    private $retention_days = 7;

    /**
     * Get singleton instance
     *
     * @return ImgSEO_File_Logger
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = trailingslashit($upload_dir['basedir']) . 'imgseo-logs';

        // Ensure log directory exists
        $this->ensure_log_directory();
    }

    /**
     * Ensure log directory exists with proper protection
     *
     * @return bool True if directory exists or was created
     */
    private function ensure_log_directory() {
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }

        // Create .htaccess to protect log files
        $htaccess_file = $this->log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Protect log files from direct access\n";
            $htaccess_content .= "Order deny,allow\n";
            $htaccess_content .= "Deny from all\n";
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
            file_put_contents($htaccess_file, $htaccess_content);
        }

        // Create index.php for additional protection
        $index_file = $this->log_dir . '/index.php';
        if (!file_exists($index_file)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
            file_put_contents($index_file, '<?php // Silence is golden');
        }

        return is_dir($this->log_dir) && wp_is_writable($this->log_dir);
    }

    /**
     * Get log file path for a specific job
     *
     * @param string $job_id The job ID
     * @return string Full path to the log file
     */
    public function get_log_file_path($job_id) {
        $safe_job_id = sanitize_file_name($job_id);
        $date = current_time('Y-m-d');
        return $this->log_dir . '/' . $safe_job_id . '_' . $date . '.log';
    }

    /**
     * Add a log entry for a job
     *
     * @param string $job_id      The job ID
     * @param int    $image_id    The image attachment ID
     * @param string $filename    The image filename
     * @param string $image_url   The full image URL
     * @param string $alt_text    The generated alt text (or error message)
     * @param string $status      Status: 'success', 'error', 'skipped'
     * @param string $message     Optional additional message
     * @param string $title       Optional generated title
     * @param string $caption     Optional generated caption
     * @param string $description Optional generated description
     * @return bool True if log was written successfully
     */
    public function add_log($job_id, $image_id, $filename, $image_url = '', $alt_text = '', $status = 'success', $message = '', $title = '', $caption = '', $description = '') {
        if (empty($job_id)) {
            return false;
        }

        $log_file = $this->get_log_file_path($job_id);

        // Create log entry as JSON for easy parsing
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'image_id' => (int) $image_id,
            'filename' => sanitize_file_name($filename),
            'image_url' => esc_url_raw($image_url),
            'alt_text' => $alt_text,
            'status' => $status,
            'message' => $message,
        );

        // Add optional metadata fields if provided
        if (!empty($title)) {
            $log_entry['title'] = $title;
        }
        if (!empty($caption)) {
            $log_entry['caption'] = $caption;
        }
        if (!empty($description)) {
            $log_entry['description'] = $description;
        }

        $log_line = wp_json_encode($log_entry) . "\n";

        // Append to log file
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        $result = file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);

        return $result !== false;
    }

    /**
     * Get logs for a specific job
     *
     * @param string $job_id      The job ID
     * @param int    $last_line   Start reading from this line (0-based, for pagination)
     * @param int    $limit       Maximum number of entries to return
     * @return array Array with 'logs' and 'total_lines'
     */
    public function get_logs($job_id, $last_line = 0, $limit = 50) {
        $log_file = $this->get_log_file_path($job_id);

        $result = array(
            'logs' => array(),
            'total_lines' => 0,
            'last_line' => $last_line,
        );

        if (!file_exists($log_file)) {
            return $result;
        }

        // Read file and count lines
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $content = file_get_contents($log_file);
        if ($content === false) {
            return $result;
        }

        $lines = explode("\n", trim($content));
        $result['total_lines'] = count($lines);

        // Get lines after last_line
        $logs = array();
        $line_number = 0;

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $line_number++;

            // Skip lines we've already seen
            if ($line_number <= $last_line) {
                continue;
            }

            // Limit the number of entries
            if (count($logs) >= $limit) {
                break;
            }

            $entry = json_decode($line, true);
            if ($entry) {
                $entry['line_number'] = $line_number;
                $logs[] = $entry;
            }
        }

        $result['logs'] = $logs;
        $result['last_line'] = $line_number;

        return $result;
    }

    /**
     * Get log statistics for a job
     *
     * @param string $job_id The job ID
     * @return array Statistics array with counts
     */
    public function get_log_stats($job_id) {
        $log_file = $this->get_log_file_path($job_id);

        $stats = array(
            'total' => 0,
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
        );

        if (!file_exists($log_file)) {
            return $stats;
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $content = file_get_contents($log_file);
        if ($content === false) {
            return $stats;
        }

        $lines = explode("\n", trim($content));

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $entry = json_decode($line, true);
            if ($entry) {
                $stats['total']++;

                if (isset($entry['status'])) {
                    switch ($entry['status']) {
                        case 'success':
                            $stats['success']++;
                            break;
                        case 'error':
                            $stats['error']++;
                            break;
                        case 'skipped':
                            $stats['skipped']++;
                            break;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Delete log file for a specific job
     *
     * @param string $job_id The job ID
     * @return bool True if deleted or didn't exist
     */
    public function delete_job_log($job_id) {
        $log_file = $this->get_log_file_path($job_id);

        if (file_exists($log_file)) {
            wp_delete_file($log_file);
            return !file_exists($log_file);
        }

        return true;
    }

    /**
     * Cleanup old log files
     *
     * @param int $days Number of days to keep logs (default: 7)
     * @return int Number of files deleted
     */
    public function cleanup_old_logs($days = null) {
        if ($days === null) {
            $days = $this->retention_days;
        }

        $deleted = 0;
        $cutoff_time = time() - ($days * DAY_IN_SECONDS);

        if (!is_dir($this->log_dir)) {
            return $deleted;
        }

        $files = glob($this->log_dir . '/*.log');

        if ($files === false) {
            return $deleted;
        }

        foreach ($files as $file) {
            $file_time = filemtime($file);

            if ($file_time !== false && $file_time < $cutoff_time) {
                wp_delete_file($file);
                if (!file_exists($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get all log files info
     *
     * @return array Array of log file info
     */
    public function get_all_logs_info() {
        $logs_info = array();

        if (!is_dir($this->log_dir)) {
            return $logs_info;
        }

        $files = glob($this->log_dir . '/*.log');

        if ($files === false) {
            return $logs_info;
        }

        foreach ($files as $file) {
            $filename = basename($file);
            $logs_info[] = array(
                'filename' => $filename,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'job_id' => $this->extract_job_id_from_filename($filename),
            );
        }

        // Sort by modification time, newest first
        usort($logs_info, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $logs_info;
    }

    /**
     * Extract job ID from log filename
     *
     * @param string $filename The log filename
     * @return string The job ID
     */
    private function extract_job_id_from_filename($filename) {
        // Format: job_xxx_YYYY-MM-DD.log
        $parts = explode('_', $filename);
        if (count($parts) >= 2) {
            return $parts[0] . '_' . $parts[1];
        }
        return '';
    }

    /**
     * Get the log directory path
     *
     * @return string
     */
    public function get_log_directory() {
        return $this->log_dir;
    }
}
