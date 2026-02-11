<?php
/**
 * Bulk Rename Processor Class
 * Handles bulk renaming operations with parallel processing
 *
 * @package ImgSEO
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ImgSEO_Bulk_Rename_Processor
 * Manages bulk renaming of images with parallel processing capabilities
 */
class ImgSEO_Bulk_Rename_Processor {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_ajax_imgseo_get_all_images', array($this, 'handle_get_all_images'));
        add_action('wp_ajax_imgseo_preview_bulk_rename', array($this, 'handle_preview_bulk_rename'));
        add_action('wp_ajax_imgseo_start_bulk_rename', array($this, 'handle_start_bulk_rename'));
        add_action('wp_ajax_imgseo_stop_bulk_rename', array($this, 'handle_stop_bulk_rename'));
        add_action('wp_ajax_imgseo_bulk_rename_single', array($this, 'handle_bulk_rename_single'));
        
        // Ensure logs manager is initialized
        add_action('init', array($this, 'ensure_logs_system'));
    }
    
    /**
     * Ensure the logging system is properly initialized
     */
    public function ensure_logs_system() {
        if (class_exists('Renamer_Logs_Manager')) {
            // Initialize logs manager to ensure table is created
            Renamer_Logs_Manager::get_instance();
            // Debug: Logs manager initialized
        } else {
            // Debug: Warning - Renamer_Logs_Manager class not available
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Handle AJAX request to get all images from media library
     */
    public function handle_get_all_images() {
        check_ajax_referer('imgseo_bulk_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }
        
        try {
            // Query all image attachments
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            
            $query = new WP_Query($args);
            $images = [];
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $attachment_id = get_the_ID();
                    $attachment_url = wp_get_attachment_url($attachment_id);
                    $filename = basename($attachment_url);
                    $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
                    
                    $images[] = [
                        'id' => $attachment_id,
                        'url' => $attachment_url,
                        'filename' => $filename,
                        'title' => get_the_title(),
                        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                        'thumbnail' => $thumbnail_url ? $thumbnail_url[0] : $attachment_url
                    ];
                }
                wp_reset_postdata();
            }
            
            wp_send_json_success([
                'images' => $images,
                'total' => count($images),
                'message' => sprintf('Found %d images in media library', count($images))
            ]);
            
        } catch (Exception $e) {
            // Debug: Error getting all images - " . $e->getMessage()
            wp_send_json_error(['message' => 'Failed to load images: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle AJAX request to preview bulk rename
     */
    public function handle_preview_bulk_rename() {
        check_ajax_referer('imgseo_bulk_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }
        
        $image_ids = isset($_POST['images']) ? array_map('absint', wp_unslash($_POST['images'])) : array();
        $options = isset($_POST['options']) ? array_map('sanitize_text_field', wp_unslash($_POST['options'])) : array();
        
        if (empty($image_ids)) {
            wp_send_json_error(['message' => 'No images provided']);
        }
        
        try {
            $results = [];
            $stats = [
                'total' => count($image_ids),
                'willRename' => 0,
                'willSkip' => 0,
                'conflicts' => 0
            ];
            
            foreach ($image_ids as $image_id) {
                $result = $this->preview_single_rename($image_id, $options);
                $results[] = $result;
                
                switch ($result['status']) {
                    case 'will_rename':
                        $stats['willRename']++;
                        break;
                    case 'will_skip':
                        $stats['willSkip']++;
                        break;
                    case 'conflict':
                        $stats['conflicts']++;
                        break;
                }
            }
            
            wp_send_json_success([
                'results' => $results,
                'total' => $stats['total'],
                'willRename' => $stats['willRename'],
                'willSkip' => $stats['willSkip'],
                'conflicts' => $stats['conflicts'],
                'message' => sprintf('Preview generated for %d images', count($image_ids))
            ]);
            
        } catch (Exception $e) {
            // Debug: Error in preview - " . $e->getMessage()
            wp_send_json_error(['message' => 'Failed to generate preview: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle AJAX request to start bulk rename
     */
    public function handle_start_bulk_rename() {
        check_ajax_referer('imgseo_bulk_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }
        
        $image_ids = isset($_POST['images']) ? array_map('absint', wp_unslash($_POST['images'])) : array();
        $options = isset($_POST['options']) ? array_map('sanitize_text_field', wp_unslash($_POST['options'])) : array();
        $processing_speed = isset($_POST['processing_speed']) ? sanitize_text_field(wp_unslash($_POST['processing_speed'])) : 'normal';
        
        if (empty($image_ids)) {
            wp_send_json_error(['message' => 'No images provided']);
        }
        
        // Validate processing speed
        $valid_speeds = ['safe', 'normal', 'fast', 'insane'];
        if (!in_array($processing_speed, $valid_speeds)) {
            $processing_speed = 'normal';
        }
        
        try {
            // Create a unique job ID
            $job_id = 'bulk_rename_' . uniqid();
            
            // Store job data in transient for parallel processing
            set_transient('imgseo_bulk_rename_job_' . $job_id, [
                'image_ids' => $image_ids,
                'options' => $options,
                'processing_speed' => $processing_speed,
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'total_images' => count($image_ids),
                'processed_images' => 0
            ], HOUR_IN_SECONDS);
            
            wp_send_json_success([
                'job_id' => $job_id,
                'image_ids' => $image_ids,
                'total_images' => count($image_ids),
                'processing_speed' => $processing_speed,
                'message' => sprintf('Bulk rename job started for %d images', count($image_ids))
            ]);
            
        } catch (Exception $e) {
            // Debug: Error starting bulk rename - " . $e->getMessage()
            wp_send_json_error(['message' => 'Failed to start bulk rename: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle AJAX request to stop bulk rename
     */
    public function handle_stop_bulk_rename() {
        check_ajax_referer('imgseo_bulk_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }
        
        $job_id = isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
        
        if (empty($job_id)) {
            wp_send_json_error(['message' => 'No job ID provided']);
        }
        
        try {
            // Get job data
            $job_data = get_transient('imgseo_bulk_rename_job_' . $job_id);
            
            if (!$job_data) {
                wp_send_json_error(['message' => 'Job not found or already completed']);
            }
            
            // Update job status to stopped
            $job_data['status'] = 'stopped';
            $job_data['stopped_at'] = current_time('mysql');
            set_transient('imgseo_bulk_rename_job_' . $job_id, $job_data, HOUR_IN_SECONDS);
            
            wp_send_json_success([
                'job_id' => $job_id,
                'processed_images' => $job_data['processed_images'],
                'message' => 'Bulk rename job stopped successfully'
            ]);
            
        } catch (Exception $e) {
            // Debug: Error stopping bulk rename - " . $e->getMessage()
            wp_send_json_error(['message' => 'Failed to stop bulk rename: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle AJAX request to rename a single image (for parallel processing)
     */
    public function handle_bulk_rename_single() {
        check_ajax_referer('imgseo_bulk_renamer_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }
        
        $image_id = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
        $job_id = isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
        $options = isset($_POST['options']) ? array_map('sanitize_text_field', wp_unslash($_POST['options'])) : array();
        
        if (!$image_id || !$job_id) {
            wp_send_json_error(['message' => 'Missing image ID or job ID']);
        }
        
        try {
            // Check if job is still active
            $job_data = get_transient('imgseo_bulk_rename_job_' . $job_id);
            
            if (!$job_data || $job_data['status'] === 'stopped') {
                wp_send_json_error(['message' => 'Job has been stopped']);
            }
            
            // Perform the rename operation
            $result = $this->rename_single_image($image_id, $options);
            
            if ($result['success']) {
                // Update job progress
                $job_data['processed_images']++;
                set_transient('imgseo_bulk_rename_job_' . $job_id, $job_data, HOUR_IN_SECONDS);
                
                // No need to manually log - the file processor already handles logging
                
                wp_send_json_success([
                    'image_id' => $image_id,
                    'old_filename' => $result['old_filename'],
                    'new_filename' => $result['new_filename'],
                    'new_url' => $result['new_url'],
                    'processed_count' => $job_data['processed_images'],
                    'message' => 'Image renamed successfully'
                ]);
            } else {
                // No need to manually log errors - the file processor handles this too
                
                wp_send_json_error([
                    'image_id' => $image_id,
                    'message' => $result['message']
                ]);
            }
            
        } catch (Exception $e) {
            // Debug: Error renaming single image - " . $e->getMessage()
            
            wp_send_json_error([
                'image_id' => $image_id,
                'message' => 'Failed to rename image: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Preview rename for a single image
     */
    private function preview_single_rename($image_id, $options) {
        $attachment_url = wp_get_attachment_url($image_id);
        $current_filename = basename($attachment_url);
        $thumbnail_url = wp_get_attachment_image_src($image_id, 'thumbnail');
        
        $result = [
            'id' => $image_id,
            'currentFilename' => $current_filename,
            'newFilename' => null,
            'thumbnail' => $thumbnail_url ? $thumbnail_url[0] : $attachment_url,
            'status' => 'will_skip',
            'statusMessage' => 'No changes needed'
        ];
        
        try {
            // Only AI method is supported now
            if ($options['method'] === 'ai') {
                // For preview, always show what the AI would generate
                // Check if we should overwrite or if it needs AI generation
                $current_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                
                // Generate AI filename with same options as bulk
                $ai_options = [
                    'max_words' => $options['max_words'] ?? 4,
                    'include_post_title' => $options['include_post_title'] ?? true,
                    'include_category' => $options['include_category'] ?? true,
                    'include_alt_text' => $options['include_alt_text'] ?? true
                ];
                
                $new_filename = $this->generate_ai_filename_preview($image_id, $ai_options);
                
            } else {
                $result['statusMessage'] = 'Invalid rename method - only AI supported';
                return $result;
            }
            
            if ($new_filename && $new_filename !== pathinfo($current_filename, PATHINFO_FILENAME)) {
                $extension = pathinfo($current_filename, PATHINFO_EXTENSION);
                $full_new_filename = $new_filename . '.' . $extension;
                
                // Apply text transformations
                if ($options['lowercase']) {
                    $full_new_filename = strtolower($full_new_filename);
                }
                
                if ($options['removeAccents']) {
                    $full_new_filename = $this->remove_accents($full_new_filename);
                }
                
                // Sanitize filename
                $full_new_filename = sanitize_file_name($full_new_filename);
                
                // Check for conflicts
                if ($this->filename_exists($full_new_filename, $image_id)) {
                    $result['status'] = 'conflict';
                    $result['statusMessage'] = 'Filename already exists';
                    $result['newFilename'] = $full_new_filename;
                } else {
                    $result['status'] = 'will_rename';
                    $result['statusMessage'] = 'Will be renamed';
                    $result['newFilename'] = $full_new_filename;
                }
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['statusMessage'] = 'Error: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Rename a single image using the existing single rename system
     */
    private function rename_single_image($image_id, $options) {
        try {
            // Generate new filename using AI (only method supported)
            if ($options['method'] !== 'ai') {
                return ['success' => false, 'message' => 'Invalid rename method - only AI supported'];
            }
            
            // Temporarily set AI options to match bulk settings
            $this->set_temporary_ai_options($options);
            
            // Use the same controller logic as single rename
            if (!class_exists('Renamer_Controller')) {
                require_once plugin_dir_path(__FILE__) . 'class-renamer-controller.php';
            }
            
            $controller = Renamer_Controller::get_instance();
            
            // Simulate the same AJAX call structure but programmatically
            $_POST['attachment_id'] = $image_id;
            $_POST['source'] = 'bulk_processor'; // Identify as bulk operation
            
            // Generate filename using the same AI generator as single rename
            if (!class_exists('Renamer_AI_Generator')) {
                require_once plugin_dir_path(__FILE__) . 'class-renamer-ai-generator.php';
            }
            
            $ai_generator = Renamer_AI_Generator::get_instance();
            $new_filename_base = $ai_generator->generate_filename($image_id);
            
            // Restore original AI settings
            $this->restore_original_ai_options();
            
            if (!$new_filename_base || is_wp_error($new_filename_base)) {
                $error_msg = is_wp_error($new_filename_base) ? $new_filename_base->get_error_message() : 'Failed to generate new filename';
                return ['success' => false, 'message' => $error_msg];
            }
            
            // Apply text transformations exactly like the single rename system
            if ($options['lowercase']) {
                $new_filename_base = strtolower($new_filename_base);
            }
            
            if ($options['removeAccents']) {
                $new_filename_base = $this->remove_accents($new_filename_base);
            }
            
            // Sanitize filename base 
            $new_filename_base = sanitize_file_name($new_filename_base);
            
            // Use the existing file processor to perform the exact same rename operation as single rename
            if (!class_exists('Renamer_File_Processor')) {
                require_once plugin_dir_path(__FILE__) . 'class-renamer-file-processor.php';
            }
            
            $file_processor = Renamer_File_Processor::get_instance();
            
            // Use the same options as the single rename system
            if (!class_exists('Renamer_Settings_Manager')) {
                require_once plugin_dir_path(__FILE__) . 'class-renamer-settings-manager.php';
            }
            
            $settings_manager = Renamer_Settings_Manager::get_instance();
            
            // Prepare options that match the single rename system exactly
            $rename_options = [
                'sanitize' => true,
                'update_references' => true, // Enable reference updates to match single rename system
                'handle_duplicates' => $settings_manager->get_setting('handle_duplicates', 'increment'),
                'remove_accents' => $settings_manager->is_enabled('remove_accents', true),
                'lowercase' => $settings_manager->is_enabled('lowercase', true)
            ];
            
            // Get current filename for potential error logging
            $current_filename = get_attached_file($image_id);
            
            // Call the same rename_image method used by single rename
            $result = $file_processor->rename_image($image_id, $new_filename_base, $rename_options);
            
            // Log only errors to WordPress error log
            if (is_wp_error($result)) {
                // Debug: Failed to rename image ID " . $image_id . " - " . $result->get_error_message()
            }
            
            if (is_wp_error($result)) {
                return ['success' => false, 'message' => $result->get_error_message()];
            }
            
            // Return success in the format expected by bulk processor
            return [
                'success' => true,
                'old_filename' => $result['old_filename'],
                'new_filename' => $result['new_filename'],
                'new_url' => $result['new_url'],
                'thumbnails_renamed' => $result['thumbnails_renamed'] ?? 0
            ];
            
        } catch (Exception $e) {
            // Debug: Exception in rename_single_image - " . $e->getMessage()
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    
    /**
     * Store original AI settings before temporary changes
     */
    private $original_ai_settings = [];
    
    /**
     * Set temporary AI options to match bulk settings
     */
    private function set_temporary_ai_options($options) {
        // Store original settings for restoration
        $this->original_ai_settings = [
            'max_words' => get_option('imgseo_renamer_ai_max_words', 4),
            'include_post_title' => get_option('imgseo_renamer_ai_include_post_title', 1),
            'include_category' => get_option('imgseo_renamer_ai_include_category', 1),
            'include_alt_text' => get_option('imgseo_renamer_ai_include_alt_text', 1)
        ];
        
        // Apply bulk options temporarily
        if (isset($options['max_words'])) {
            update_option('imgseo_renamer_ai_max_words', $options['max_words']);
        }
        if (isset($options['include_post_title'])) {
            update_option('imgseo_renamer_ai_include_post_title', $options['include_post_title'] ? 1 : 0);
        }
        if (isset($options['include_category'])) {
            update_option('imgseo_renamer_ai_include_category', $options['include_category'] ? 1 : 0);
        }
        if (isset($options['include_alt_text'])) {
            update_option('imgseo_renamer_ai_include_alt_text', $options['include_alt_text'] ? 1 : 0);
        }
    }
    
    /**
     * Restore original AI settings
     */
    private function restore_original_ai_options() {
        if (!empty($this->original_ai_settings)) {
            update_option('imgseo_renamer_ai_max_words', $this->original_ai_settings['max_words']);
            update_option('imgseo_renamer_ai_include_post_title', $this->original_ai_settings['include_post_title']);
            update_option('imgseo_renamer_ai_include_category', $this->original_ai_settings['include_category']);
            update_option('imgseo_renamer_ai_include_alt_text', $this->original_ai_settings['include_alt_text']);
            $this->original_ai_settings = [];
        }
    }
    
    /**
     * Generate AI filename preview using the same AI generator as single rename
     */
    private function generate_ai_filename_preview($image_id, $options = []) {
        // Temporarily set AI options to match bulk settings
        $this->set_temporary_ai_options($options);
        
        // Use the same AI generator as single rename
        if (!class_exists('Renamer_AI_Generator')) {
            require_once plugin_dir_path(__FILE__) . 'class-renamer-ai-generator.php';
        }
        
        $ai_generator = Renamer_AI_Generator::get_instance();
        $result = $ai_generator->generate_filename($image_id);
        
        // Restore original AI settings
        $this->restore_original_ai_options();
        
        return $result;
    }
    
    /**
     * Generate pattern-based filename
     */
    private function generate_pattern_filename($image_id, $pattern) {
        $replacements = [
            '{post_title}' => $this->get_post_title_for_image($image_id),
            '{category}' => $this->get_category_for_image($image_id),
            '{numero}' => str_pad($image_id, 3, '0', STR_PAD_LEFT),
            '{originale}' => pathinfo(basename(wp_get_attachment_url($image_id)), PATHINFO_FILENAME),
            '{data}' => gmdate('Ymd'),
            '{alt}' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'image'
        ];
        
        $filename = str_replace(array_keys($replacements), array_values($replacements), $pattern);
        return sanitize_title($filename);
    }
    
    /**
     * Helper function to get post title for image
     */
    private function get_post_title_for_image($image_id) {
        $parent_id = wp_get_post_parent_id($image_id);
        if ($parent_id) {
            return get_the_title($parent_id);
        }
        return get_the_title($image_id) ?: 'untitled';
    }
    
    /**
     * Helper function to get category for image
     */
    private function get_category_for_image($image_id) {
        $parent_id = wp_get_post_parent_id($image_id);
        if ($parent_id) {
            $categories = get_the_category($parent_id);
            if (!empty($categories)) {
                return $categories[0]->slug;
            }
        }
        return 'uncategorized';
    }
    

    
    /**
     * Check if filename already exists
     */
    private function filename_exists($filename, $exclude_id = null) {
        global $wpdb;
        
        $like_pattern = '%' . $wpdb->esc_like($filename);
        
        if ($exclude_id) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s AND post_id != %d",
                '_wp_attached_file', $like_pattern, $exclude_id
            ));
        } else {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
                '_wp_attached_file', $like_pattern
            ));
        }
        return !empty($results);
    }
    
    /**
     * Handle duplicate filenames
     */
    private function handle_duplicate_filename($filename, $image_id) {
        $original_filename = $filename;
        $counter = 1;
        
        while ($this->filename_exists($filename, $image_id)) {
            $pathinfo = pathinfo($original_filename);
            $filename = $pathinfo['filename'] . '-' . $counter . '.' . $pathinfo['extension'];
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 100) {
                $filename = $pathinfo['filename'] . '-' . time() . '.' . $pathinfo['extension'];
                break;
            }
        }
        
        return $filename;
    }
    
    /**
     * Remove accents from string
     */
    private function remove_accents($string) {
        return remove_accents($string);
    }
    
    /**
     * Regenerate thumbnails for renamed image
     */
    private function regenerate_thumbnails($image_id, $new_path) {
        // Use WordPress function to regenerate thumbnails
        if (function_exists('wp_generate_attachment_metadata')) {
            $metadata = wp_generate_attachment_metadata($image_id, $new_path);
            wp_update_attachment_metadata($image_id, $metadata);
        }
    }
    
}

// Initialize the bulk rename processor
ImgSEO_Bulk_Rename_Processor::get_instance();