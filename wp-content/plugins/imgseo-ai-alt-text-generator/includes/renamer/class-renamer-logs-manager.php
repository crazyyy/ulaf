<?php
/**
 * Class Renamer_Logs_Manager
 * Manages the logs for the Image Renamer functionality
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_Logs_Manager {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Log table name
     */
    private $log_table;
    
    /**
     * Initialize the class and set its properties.
     */
    private function __construct() {
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'imgseo_rename_logs';
        
        // Create the log table if it doesn't exist
        $this->create_logs_table();
        
        // Update table structure if needed
        $this->update_table_structure();
        
        // Dependency on settings manager for log retention
        require_once plugin_dir_path(__FILE__) . 'class-renamer-settings-manager.php';
        
        // Setup log cleanup
        add_action('imgseo_cleanup_rename_logs', array($this, 'cleanup_old_logs'));
        if (!wp_next_scheduled('imgseo_cleanup_rename_logs')) {
            wp_schedule_event(time(), 'daily', 'imgseo_cleanup_rename_logs');
        }
    }
    
    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create the rename logs table if it doesn't exist
     */
    private function create_logs_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->log_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            image_id bigint(20) NOT NULL,
            old_filename varchar(255) NOT NULL,
            new_filename varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'success',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            operation_type varchar(20) DEFAULT 'single',
            batch_id varchar(36) DEFAULT NULL,
            operation_details text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY image_id (image_id),
            KEY created_at (created_at),
            KEY user_id (user_id),
            KEY batch_id (batch_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Update table structure if needed (for existing installations)
     */
    private function update_table_structure() {
        global $wpdb;
        
        // Check if the new columns exist
        $columns = $wpdb->get_col("DESCRIBE `{$this->log_table}`");
        
        $missing_columns = array();
        if (!in_array('user_id', $columns)) {
            $missing_columns[] = 'ADD COLUMN user_id bigint(20) DEFAULT NULL';
        }
        if (!in_array('operation_type', $columns)) {
            $missing_columns[] = 'ADD COLUMN operation_type varchar(20) DEFAULT \'single\'';
        }
        if (!in_array('batch_id', $columns)) {
            $missing_columns[] = 'ADD COLUMN batch_id varchar(36) DEFAULT NULL';
        }
        if (!in_array('operation_details', $columns)) {
            $missing_columns[] = 'ADD COLUMN operation_details text DEFAULT NULL';
        }
        
        // Add missing columns
        if (!empty($missing_columns)) {
            // Execute each column addition separately for security
            // Note: ALTER TABLE statements cannot use placeholders for table names
            // The table name is validated and sanitized in constructor
            foreach ($missing_columns as $column_sql) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->query("ALTER TABLE {$this->log_table} " . $column_sql);
            }
            
            // Add indexes for new columns
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE {$this->log_table} ADD INDEX user_id (user_id)");
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE {$this->log_table} ADD INDEX batch_id (batch_id)");
        }
    }
    
    /**
     * Log a rename operation
     * 
     * @param int $image_id The attachment ID
     * @param string $old_filename The original filename
     * @param string $new_filename The new filename
     * @param string $status The status of the operation (success, error, restore)
     * @param array $extra_data Additional data to log
     * @return bool Success or failure
     */
    public function log_rename_operation($image_id, $old_filename, $new_filename, $status = 'success', $extra_data = array()) {
        global $wpdb;
        
        // Determine current user
        $user_id = get_current_user_id();
        
        // Prepare log entry
        $log_data = array(
            'image_id' => $image_id,
            'old_filename' => $old_filename,
            'new_filename' => $new_filename,
            'status' => $status,
            'created_at' => current_time('mysql'),
            'user_id' => $user_id ?: null
        );
        
        // Add optional extra data
        if (!empty($extra_data['operation_type'])) {
            $log_data['operation_type'] = sanitize_text_field($extra_data['operation_type']);
        }
        
        if (!empty($extra_data['batch_id'])) {
            $log_data['batch_id'] = sanitize_text_field($extra_data['batch_id']);
        }
        
        if (!empty($extra_data['details'])) {
            $log_data['operation_details'] = is_array($extra_data['details']) ? 
                wp_json_encode($extra_data['details']) : 
                sanitize_text_field($extra_data['details']);
        }
        
        // Format types for wpdb
        $format = array('%d', '%s', '%s', '%s', '%s', '%d');
        
        if (isset($log_data['operation_type'])) {
            $format[] = '%s';
        }
        
        if (isset($log_data['batch_id'])) {
            $format[] = '%s';
        }
        
        if (isset($log_data['operation_details'])) {
            $format[] = '%s';
        }
        
        return $wpdb->insert($this->log_table, $log_data, $format);
    }
    
    /**
     * Log a batch rename operation
     * 
     * @param array $batch_results Results from batch operation
     * @param string $batch_id Unique ID for this batch
     * @param array $options Options used for the batch operation
     * @return bool Success or failure
     */
    public function log_batch_operation($batch_results, $batch_id = null, $options = array()) {
        if (empty($batch_id)) {
            $batch_id = 'batch_' . uniqid();
        }
        
        $success = true;
        
        // Log successful renames
        if (!empty($batch_results['success'])) {
            foreach ($batch_results['success'] as $attachment_id => $result) {
                $extra_data = array(
                    'operation_type' => 'batch',
                    'batch_id' => $batch_id,
                    'details' => array(
                        'options' => $options
                    )
                );
                
                $log_result = $this->log_rename_operation(
                    $attachment_id,
                    $result['old_filename'],
                    $result['new_filename'],
                    'success',
                    $extra_data
                );
                
                if (!$log_result) {
                    $success = false;
                }
            }
        }
        
        // Log errors
        if (!empty($batch_results['errors'])) {
            foreach ($batch_results['errors'] as $attachment_id => $result) {
                $extra_data = array(
                    'operation_type' => 'batch',
                    'batch_id' => $batch_id,
                    'details' => array(
                        'options' => $options,
                        'error_message' => $result['message']
                    )
                );
                
                $old_filename = isset($result['old_filename']) ? $result['old_filename'] : 'unknown';
                $new_filename = isset($result['new_filename']) ? $result['new_filename'] : 'unknown';
                
                $log_result = $this->log_rename_operation(
                    $attachment_id,
                    $old_filename,
                    $new_filename,
                    'error',
                    $extra_data
                );
                
                if (!$log_result) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Get logs for a specific image
     * 
     * @param int $image_id The attachment ID
     * @param int $limit Maximum number of logs to return
     * @return array Logs for the specified image
     */
    public function get_logs_for_image($image_id, $limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT * FROM {$this->log_table} WHERE image_id = %d ORDER BY created_at DESC LIMIT %d",
                $image_id,
                $limit
            ),
            ARRAY_A
        );
    }
    
    /**
     * Get the latest log entry for an image
     * 
     * @param int $image_id The attachment ID
     * @return array|null The latest log entry or null if none found
     */
    public function get_latest_log_for_image($image_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT * FROM {$this->log_table} WHERE image_id = %d ORDER BY created_at DESC LIMIT 1",
                $image_id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Clean up old logs based on retention setting
     */
    public function cleanup_old_logs() {
        global $wpdb;

        // Keep logs for 7 days (hardcoded since UI setting was removed)
        $retention_days = 7;

        // Delete logs older than retention days
        $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "DELETE FROM {$this->log_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $retention_days
            )
        );
    }
    
    /**
     * AJAX handler for getting rename logs
     */
    public function ajax_get_logs() {
        check_ajax_referer('imgseo_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to view logs.', 'imgseo-ai-alt-text-generator')));
        }
        
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $per_page = 20;
        
        global $wpdb;
        
        // Get total count
        $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %s", $this->log_table));
        
        // Calculate pagination
        $offset = ($page - 1) * $per_page;
        $total_pages = ceil($total_items / $per_page);
        
        // Get logs
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT * FROM {$this->log_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        
        // Format dates for display
        foreach ($logs as &$log) {
            $log['created_at'] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']));
        }
        
        wp_send_json_success(array(
            'logs' => $logs,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page
        ));
    }
    
    /**
     * AJAX handler for deleting rename logs
     */
    public function ajax_delete_logs() {
        check_ajax_referer('imgseo_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to delete logs.', 'imgseo-ai-alt-text-generator')));
        }
        
        global $wpdb;
        
        // Delete all logs
        $wpdb->query($wpdb->prepare("TRUNCATE TABLE %s", $this->log_table));
        
        wp_send_json_success(array('message' => esc_html__('All logs have been deleted.', 'imgseo-ai-alt-text-generator')));
    }
    

}
