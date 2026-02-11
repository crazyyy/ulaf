<?php


/**


 * Handle processing speed settings and batch processing.


 *


 * @package ImgSEO


 */





if (!defined('ABSPATH')) {


    exit; // Exit if accessed directly.


}





class ImgSEO_Process_Speed {





    /**
     * Speed presets with their corresponding batch sizes (ottimizzati per performance)
     */
    const SPEED_PRESETS = [
        'slow' => 5,
        'normal' => 8,
        'fast' => 12,
        'ultra' => 20
    ];





    /**
     * Delay between image processing in seconds
     */
    const IMAGE_DELAY = 0.2;





    /**


     * Initialize the process speed settings


     */


    public static function init() {


        // Register settings


        add_action('admin_init', [self::class, 'register_settings']);





        // Add speed settings to the settings page


        add_filter('imgseo_settings_fields', [self::class, 'add_speed_settings'], 20, 1);





        // Initialize the speed setting if it doesn't exist


        self::maybe_add_default_option();


    }





    /**


     * Add the default speed option if it doesn't exist


     */


    public static function maybe_add_default_option() {


        if (false === get_option('imgseo_processing_speed')) {
            // Processing speed - only used during processing, disable autoload
            add_option('imgseo_processing_speed', 'normal', '', 'no');
        }


    }





    /**


     * Register the settings


     */


    public static function register_settings() {


        register_setting('imgseo_settings', 'imgseo_processing_speed', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));


    }





    /**


     * Add speed settings to the ImgSEO settings


     */


    public static function add_speed_settings($fields) {


        $speed_field = [


            'id' => 'imgseo_processing_speed',


            'label' => __('Processing Speed', 'imgseo-ai-alt-text-generator'),


            'description' => __('Select the processing speed for bulk operations. Higher speeds process more images in parallel.', 'imgseo-ai-alt-text-generator'),


            'type' => 'select',


            'options' => [
                'slow' => __('Slow (5 images at once)', 'imgseo-ai-alt-text-generator'),
				'normal' => __('Normal (8 images at once)', 'imgseo-ai-alt-text-generator'),
				'fast' => __('Fast (12 images at once)', 'imgseo-ai-alt-text-generator'),
				'ultra' => __('Ultra (20 images at once)', 'imgseo-ai-alt-text-generator')
            ],


            'default' => 'normal'


        ];





        // Find the position to insert our field - after the batch size if it exists


        $batch_size_index = -1;


        foreach ($fields as $index => $field) {


            if (isset($field['id']) && $field['id'] === 'imgseo_batch_size') {


                $batch_size_index = $index;


                break;


            }


        }





        if ($batch_size_index >= 0) {


            // Insert after the batch size field


            array_splice($fields, $batch_size_index + 1, 0, [$speed_field]);


        } else {


            // Just append to the end if batch size field not found


            $fields[] = $speed_field;


        }





        return $fields;


    }





    /**


     * Get current batch size based on the selected speed


     */


    public static function get_batch_size() {


        $speed = get_option('imgseo_processing_speed', 'normal');


        return isset(self::SPEED_PRESETS[$speed]) ? self::SPEED_PRESETS[$speed] : self::SPEED_PRESETS['normal'];


    }





    /**
     * Get delay between image processing in milliseconds
     *
     * Calculate delay dynamically based on batch size, system load and performance
     */
    public static function get_delay_ms() {
        try {
            $batch_size = self::get_batch_size();
            // Ensure we have a valid number to prevent division by zero
            if (!is_numeric($batch_size) || $batch_size <= 0) {
                $batch_size = 8; // Updated default fallback
            }
            
            // Base delay calculation (ridotto da 3 a 1.5 secondi)
            $base_delay = 1.5 / (float)$batch_size;
            
            // Dynamic adjustment based on system load
            $load_factor = self::get_system_load_factor();
            $adjusted_delay = $base_delay * $load_factor;
            
            // Minimum delay of 50ms, maximum of 2000ms
            $delay_ms = max(50, min(2000, (int)($adjusted_delay * 1000)));
            
            return $delay_ms;
        } catch (Exception $e) {
            return 200; // Reduced default fallback
        }
    }
    
    /**
     * Get system load factor for dynamic delay adjustment
     *
     * @return float Load factor between 0.5 and 2.0
     */
    private static function get_system_load_factor() {
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        $memory_limit = self::parse_memory_limit(ini_get('memory_limit'));
        $memory_ratio = $memory_usage / $memory_limit;
        
        // Check recent processing performance
        $recent_performance = get_transient('imgseo_recent_performance');
        
        // Base load factor
        $load_factor = 1.0;
        
        // Adjust based on memory usage
        if ($memory_ratio > 0.8) {
            $load_factor *= 1.5; // Slow down if memory is high
        } elseif ($memory_ratio < 0.5) {
            $load_factor *= 0.7; // Speed up if memory is low
        }
        
        // Adjust based on recent performance
        if ($recent_performance && $recent_performance < 2.0) {
            $load_factor *= 0.8; // Speed up if recent performance was good
        } elseif ($recent_performance && $recent_performance > 5.0) {
            $load_factor *= 1.3; // Slow down if recent performance was poor
        }
        
        // Ensure factor is within reasonable bounds
        return max(0.5, min(2.0, $load_factor));
    }
    
    /**
     * Parse memory limit string to bytes
     *
     * @param string $limit Memory limit string (e.g., '256M')
     * @return int Memory limit in bytes
     */
    private static function parse_memory_limit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $limit = (int) $limit;
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return $limit ?: 268435456; // Default 256MB
    }


}





// Initialize the class


ImgSEO_Process_Speed::init();
