<?php
/**
 * Classe principale per il generatore
 * 
 * @package ImgSEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe ImgSEO_Generator_Main
 * Classe principale che inizializza e coordina tutte le funzionalità di generazione
 */
class ImgSEO_Generator_Main {
    
    /**
     * Istanza del generatore di testo alternativo
     *
     * @var ImgSEO_Alt_Text_Generator
     */
    private $alt_text_generator;
    
    /**
     * Istanza del processore batch
     *
     * @var ImgSEO_Batch_Processor
     */
    private $batch_processor;
    
    /**
     * Istanza del gestore della libreria media
     *
     * @var ImgSEO_Media_Library_Manager
     */
    private $media_library_manager;
    
    /**
     * Singleton instance
     * 
     * @var ImgSEO_Generator_Main
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return ImgSEO_Generator_Main
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
        // Usa la costante già definita IMGSEO_DIRECTORY_PATH
        // Non serve definire IMGSEO_PLUGIN_DIR, usiamo direttamente IMGSEO_DIRECTORY_PATH
        
        // Caricamento delle classi necessarie
        $this->load_dependencies();
        
        // Inizializzazione delle classi
        $this->alt_text_generator = new ImgSEO_Alt_Text_Generator();
        $this->batch_processor = new ImgSEO_Batch_Processor();
        $this->media_library_manager = new ImgSEO_Media_Library_Manager();
        
        // Registrazione degli hook AJAX
        $this->register_ajax_hooks();
        
        // Non registriamo più qui il hook per la generazione automatica
        // è gestito centralmente dal metodo ImgSEO_Alt_Text_Generator::initialize_hooks()
        
        // Registrazione del hook per l'elaborazione batch tramite cron
        add_action(IMGSEO_CRON_HOOK, array($this->batch_processor, 'process_cron_batch'));
    }
    
    /**
     * Carica le dipendenze necessarie
     */
    private function load_dependencies() {
        // Debug: verifica che la costante sia definita
        if (!defined('IMGSEO_DIRECTORY_PATH')) {
            wp_die('IMGSEO_DIRECTORY_PATH constant not defined');
        }

        // Base class - DEVE essere caricata per prima
        $base_file = IMGSEO_DIRECTORY_PATH . 'includes/generator/class-generator-base.php';
        if (!file_exists($base_file)) {
            wp_die('Base class file not found: ' . esc_html($base_file));
        }
        require_once $base_file;

        // Specialized classes
        $alt_text_file = IMGSEO_DIRECTORY_PATH . 'includes/generator/class-alt-text-generator.php';
        if (!file_exists($alt_text_file)) {
            wp_die('Alt text generator file not found: ' . esc_html($alt_text_file));
        }
        require_once $alt_text_file;

        require_once IMGSEO_DIRECTORY_PATH . 'includes/generator/class-batch-processor.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/generator/class-media-library-manager.php';

        // Verifica che le classi siano state caricate
        if (!class_exists('ImgSEO_Generator_Base')) {
            wp_die('ImgSEO_Generator_Base class not loaded');
        }
        if (!class_exists('ImgSEO_Alt_Text_Generator')) {
            wp_die('ImgSEO_Alt_Text_Generator class not loaded');
        }
    }
    
    /**
     * Registra gli hook AJAX
     */
    private function register_ajax_hooks() {
        // Alt text generation
        add_action('wp_ajax_imgseo_generate_alt_text', array($this->alt_text_generator, 'handle_generate_alt_text'));
        add_action('wp_ajax_generate_alt_text', array($this->alt_text_generator, 'handle_generate_alt_text'));
        add_action('wp_ajax_imgseo_process_single_image', array($this->alt_text_generator, 'handle_generate_alt_text'));

        // Backward compatibility for old JavaScript (app.js)
        add_action('wp_ajax_single_alttext_generate', array($this->alt_text_generator, 'handle_generate_alt_text'));
        add_action('wp_ajax_bulk_alttext_generate', array($this, 'handle_legacy_bulk_generate'));

        // Batch processing
        add_action('wp_ajax_imgseo_start_bulk', array($this->batch_processor, 'handle_start_bulk'));
        add_action('wp_ajax_imgseo_check_job_status', array($this->batch_processor, 'handle_check_job_status'));
        add_action('wp_ajax_imgseo_stop_job', array($this->batch_processor, 'handle_stop_job'));
        add_action('wp_ajax_imgseo_delete_job', array($this->batch_processor, 'handle_delete_job'));
        add_action('wp_ajax_imgseo_delete_all_jobs', array($this->batch_processor, 'handle_delete_all_jobs'));
        add_action('wp_ajax_imgseo_force_cron', array($this->batch_processor, 'force_cron_execution'));
        add_action('wp_ajax_imgseo_view_job_log', array($this->batch_processor, 'handle_view_job_log'));

        // Register filter to add nonce data for backward compatibility
        add_filter('imgseo_localize_script', array($this, 'add_legacy_nonces'));
    }
    
    /**
     * Proxy method per auto_generate_alt_text
     *
     * @param int|WP_Post $attachment_id ID dell'allegato o oggetto WP_Post
     */
    public function auto_generate_alt_text($attachment_id) {
        $this->alt_text_generator->auto_generate_alt_text($attachment_id);
    }

    /**
     * Proxy method per process_single_generate
     *
     * @param int|WP_Post $attachment_id ID dell'allegato o oggetto WP_Post
     * @param int $attempt_number Numero del tentativo
     */
    public function process_single_generate($attachment_id, $attempt_number = 1) {
        $this->alt_text_generator->process_single_generate($attachment_id, $attempt_number);
    }
    
    /**
     * Proxy method per handle_generate_alt_text
     */
    public function handle_generate_alt_text() {
        $this->alt_text_generator->handle_generate_alt_text();
    }
    
    /**
     * Proxy method per handle_start_bulk
     */
    public function handle_start_bulk() {
        $this->batch_processor->handle_start_bulk();
    }
    
    /**
     * Proxy method per handle_check_job_status
     */
    public function handle_check_job_status() {
        $this->batch_processor->handle_check_job_status();
    }
    
    /**
     * Proxy method per handle_stop_job
     */
    public function handle_stop_job() {
        $this->batch_processor->handle_stop_job();
    }
    
    /**
     * Proxy method per handle_delete_job
     */
    public function handle_delete_job() {
        $this->batch_processor->handle_delete_job();
    }
    
    /**
     * Proxy method per handle_delete_all_jobs
     */
    public function handle_delete_all_jobs() {
        $this->batch_processor->handle_delete_all_jobs();
    }
    
    /**
     * Proxy method per process_cron_batch
     */
    public function process_cron_batch() {
        $this->batch_processor->process_cron_batch();
    }
    
    /**
     * Proxy method per force_cron_execution
     */
    public function force_cron_execution() {
        $this->batch_processor->force_cron_execution();
    }
    
    /**
     * Ottiene l'istanza del generatore di testo alternativo
     *
     * @return ImgSEO_Alt_Text_Generator
     */
    public function get_alt_text_generator() {
        imgseo_debug_log('get_alt_text_generator chiamato, restituendo istanza: ' . (is_object($this->alt_text_generator) ? get_class($this->alt_text_generator) : 'non è un oggetto'));
        return $this->alt_text_generator;
    }

    /**
     * Adds legacy nonces for backward compatibility with old JavaScript (app.js)
     *
     * @param array $data Existing localized data
     * @return array Modified data with legacy nonces
     */
    public function add_legacy_nonces($data) {
        // Add legacy nonce fields for backward compatibility
        $data['security_single_alttext_generate'] = wp_create_nonce('imgseo_nonce');
        $data['security_bulk_alttext_generate'] = wp_create_nonce('imgseo_nonce');
        $data['security_single_generate'] = wp_create_nonce('imgseo_nonce');

        // Ensure has_app_key is set
        if (!isset($data['has_app_key'])) {
            $api_key = get_option('imgseo_api_key', '');
            $data['has_app_key'] = !empty($api_key);
        }

        // Ensure settings_url is set
        if (!isset($data['settings_url'])) {
            $data['settings_url'] = admin_url('admin.php?page=imgseo-ai-alt-text-generator');
        }

        return $data;
    }

    /**
     * Handles legacy bulk generation AJAX request from old JavaScript (app.js)
     * This is a wrapper for backward compatibility
     */
    public function handle_legacy_bulk_generate() {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'imgseo_nonce')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
            return;
        }

        // Extract parameters from old format
        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 5;
        $all_images = isset($_POST['all_images']) ? (bool)$_POST['all_images'] : false;
        $last_post_id = isset($_POST['last_post_id']) ? intval($_POST['last_post_id']) : 0;

        // Get images to process
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => $batch_size,
            'post_status' => 'inherit',
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        if ($last_post_id > 0) {
            $args['post__not_in'] = array($last_post_id);
            $args['post__in'] = array();
            // Get IDs greater than last_post_id
            global $wpdb;
            $mime_like = $wpdb->esc_like('image') . '%';
            // Query is safe: table name is from WordPress core, and last_post_id is cast to int
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'attachment'
                AND post_mime_type LIKE %s
                AND ID > %d
                ORDER BY ID ASC
                LIMIT %d",
                $mime_like,
                $last_post_id,
                $batch_size
            ));
            $args['post__in'] = $ids;
        }

        $query = new WP_Query($args);
        $images = $query->posts;

        $processed_count = 0;
        $last_processed_id = $last_post_id;

        foreach ($images as $image_id) {
            // Check if image needs processing (no alt text)
            $current_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if (empty($current_alt) || $all_images) {
                // Process this image
                $this->alt_text_generator->process_single_generate($image_id);
                $processed_count++;
                $last_processed_id = $image_id;
            }
        }

        // Determine if we should loop again
        $total_images = wp_count_posts('attachment')->inherit;
        $loop_again = count($images) === $batch_size;

        wp_send_json_success([
            'processed_count' => $processed_count,
            'last_post_id' => $last_processed_id,
            'loop_again' => $loop_again,
            'total_images' => $total_images
        ]);
    }
}
