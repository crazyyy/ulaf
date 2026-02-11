<?php

/*

* Plugin Name: ImgSEO – AI Bulk Image Alt Text Generator, Renamer & SEO Tools

* Description: Context-aware AI that analyzes both images AND page content to generate accurate alt text, titles, captions & descriptions. Process 1000+ images with 16x faster parallel processing. Includes smart renaming, JSON-LD schema, XML sitemaps. Meet WCAG 2.1, EAA & ADA compliance.

* Version: 2.5

* Author: pianoweb

* Author URI: https://pianoweb.eu

* Author Email: info@pianoweb.eu

* Plugin URI: https://imgseo.net

* Text Domain: imgseo-ai-alt-text-generator

* Domain Path: /languages

* Requires at least: 5.0

* Tested up to: 6.8

* Requires PHP: 7.3

* License: GPLv2 or later

* License URI: https://www.gnu.org/licenses/gpl-2.0.html

*/

defined('ABSPATH') or die('Access Denied!');

// Debug mode - can be overridden in wp-config.php by adding: define('IMGSEO_DEBUG_MODE', true);
if (!defined('IMGSEO_DEBUG_MODE')) {
    define('IMGSEO_DEBUG_MODE', false); // Set to true for debugging, false for production
}

/**
 * Debug logging helper function
 * Only logs when IMGSEO_DEBUG_MODE is enabled
 */
function imgseo_debug_log($message) {
    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
        if (is_array($message) || is_object($message)) {
            error_log('[ImgSEO] ' . print_r($message, true));
        } else {
            error_log('[ImgSEO] ' . $message);
        }
    }
}

/**
 * Security helper function to validate attachment ID
 *
 * @param int $attachment_id The attachment ID to validate
 * @return bool|int False if invalid, valid attachment ID if valid
 */
function imgseo_validate_attachment_id($attachment_id) {
    $attachment_id = intval($attachment_id);

    if ($attachment_id <= 0) {
        return false;
    }

    // Check if attachment exists and user has permission to edit it
    if (!get_post($attachment_id) || !current_user_can('edit_post', $attachment_id)) {
        return false;
    }

    // Verify it's actually an attachment
    if (get_post_type($attachment_id) !== 'attachment') {
        return false;
    }

    return $attachment_id;
}

// Constants definitions

define('IMGSEO_FILE', __FILE__);

define('IMGSEO_DIRECTORY', dirname(__FILE__));

// Text domain constant removed - use literal string 'imgseo-ai-alt-text-generator' directly

define('IMGSEO_ACCESS_DENIED', 'Access Denied!');

define('IMGSEO_DIRECTORY_PATH', plugin_dir_path(IMGSEO_FILE));

define('IMGSEO_PLUGIN_URL', plugin_dir_url(IMGSEO_FILE));

define('IMGSEO_PLUGIN_NAME', 'imgseo');

define('IMGSEO_PLUGIN_VERSION', '2.3');
define('IMGSEO_CRON_HOOK', 'imgseo_cron_process');
define('IMGSEO_STATUS_CREATED', 201);
define('IMGSEO_STATUS_OK', 200);



// Main class for plugin initialization

class IMGSEO_Init {

    // Singleton instance
    protected static $instance = null;

    // Plugin components
    public $api;
    public $settings;
    public $generator;
    public $media_button;
    public $renamer;
    public $sitemap_generator;
    public $structured_data;
    public $structured_data_admin;
    public $system_initializer;

    // WordPress best practice: Option cache to reduce database queries
    private $settings_cache = array();



    /**

     * Initializes the plugin and returns the singleton instance

     *

     * @return IMGSEO_Init

     */

    public static function init() {

        if (self::$instance === null) {

            self::$instance = new self();

        }

        return self::$instance;

    }



    /**

     * Constructor: loads files, initializes components, and registers hooks

     */

    private function __construct() {

        $this->load_files();

        $this->initialize_components();

        $this->register_hooks();

    }



    /**

     * Loads necessary class files

     */

    private function load_files() {

        // API File
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-api.php';

        // Core files

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-menu-manager.php';

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-settings.php';

        require_once IMGSEO_DIRECTORY_PATH . 'includes/generator/class-generator-main.php';

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-media-modal-button.php';

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-image-renamer.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/renamer/class-bulk-rename-processor.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-file-logger.php';
require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-image-sitemap-generator.php';

        // Load new universal scanning system FIRST (v2.0)
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-database-manager.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-image-registry.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-page-builder-detector.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-universal-scanner.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-accurate-stats-calculator.php';
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-system-initializer.php';

        // Load structured data classes AFTER the new system
require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-structured-data.php';
require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-structured-data-admin.php';



        // Load processing speed classes

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-process-speed.php';

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-speed-integrator.php';

    }



    /**

     * Initializes plugin components

     */

    private function initialize_components() {

        // Initialize main components

        $this->api = ImgSEO_API::get_instance();

        // Verifica automatica dei crediti all'inizializzazione
        // Solo se necessario e non troppo frequentemente
        $this->maybe_refresh_credits();

        // Inizializzazione del gestore menu centralizzato (prima di altri componenti che potrebbero utilizzare menu)
        $menu_manager = ImgSEO_Menu_Manager::get_instance();



        // Inizializza gli altri componenti utilizzando il pattern singleton dove disponibile

        $this->settings = ImgSEO_Settings::get_instance();

        // Assicura che la classe ImgSEO_Generator_Main sia caricata per evitare fatal error
        if (!class_exists('ImgSEO_Generator_Main')) {
            $generator_file = IMGSEO_DIRECTORY_PATH . 'includes/generator/class-generator-main.php';
            if (file_exists($generator_file)) {
                require_once $generator_file;
            } else {
                // Log diagnostico in ambiente di debug
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('ImgSEO: file della classe generatore non trovato: ' . $generator_file);
                }
            }
        }
        $this->generator = ImgSEO_Generator_Main::get_instance();

        $this->media_button = Media_Modal_Button::instance();

        $this->renamer = Image_Renamer::get_instance();
        $this->sitemap_generator = ImgSEO_Image_Sitemap_Generator::get_instance();

        // Inizializza il nuovo sistema v2.0
        if (class_exists('ImgSEO_System_Initializer')) {
            $this->system_initializer = ImgSEO_System_Initializer::get_instance();
        }

        $this->structured_data = ImgSEO_Structured_Data::get_instance();
        $this->structured_data_admin = ImgSEO_Structured_Data_Admin::get_instance();
    }



    /**

     * Registers main and custom hooks

     */

    private function register_hooks() {

        // Load translation files

        add_action('init', array($this, 'load_textdomain'));



        // Inizializza centralmente gli hook di generazione alt text (solo se la classe è disponibile)
        if (class_exists('ImgSEO_Alt_Text_Generator')) {
            ImgSEO_Alt_Text_Generator::initialize_hooks();
        }



        // Hook for single image generation

        add_action('imgseo_single_generate', array($this->generator, 'process_single_generate'));



        // Hook for single generation via AJAX - RIMOSSO per evitare doppia registrazione
        // add_action('wp_ajax_generate_alt_text', array($this->generator, 'handle_generate_alt_text'));



        // Hooks for bulk processing

        add_action('wp_ajax_imgseo_start_bulk', array($this->generator, 'handle_start_bulk'));

        add_action('wp_ajax_imgseo_check_job_status', array($this->generator, 'handle_check_job_status'));

        add_action('wp_ajax_imgseo_stop_job', array($this->generator, 'handle_stop_job'));

        add_action('wp_ajax_imgseo_delete_job', array($this->generator, 'handle_delete_job'));

        add_action('wp_ajax_imgseo_delete_all_jobs', array($this->generator, 'handle_delete_all_jobs'));



        // Hook for cron processing

        add_action(IMGSEO_CRON_HOOK, array($this->generator, 'process_cron_batch'));



        // Hook to force cron execution

        add_action('wp_ajax_imgseo_force_cron', array($this->generator, 'force_cron_execution'));



        // Hooks for settings

        add_action('wp_ajax_imgseo_verify_api_key', array($this->api, 'ajax_verify_api_key'));

        add_action('wp_ajax_imgseo_refresh_credits', array($this->api, 'ajax_refresh_credits'));

        add_action('wp_ajax_imgseo_disconnect_api', array($this->api, 'ajax_disconnect_api'));

        add_action('wp_ajax_imgseo_update_settings', array($this->settings, 'ajax_update_settings'));

        // Reset data hooks
        add_action('wp_ajax_imgseo_reset_logs_and_cache', array($this->settings, 'ajax_reset_logs_and_cache'));
        add_action('wp_ajax_imgseo_reset_all_data', array($this->settings, 'ajax_reset_all_data'));

        // Hook per gestire flag operazioni intensive (evita conflitti SEO)
        add_action('wp_ajax_imgseo_set_bulk_flag', array($this, 'ajax_set_bulk_operation_flag'));



        // Renamer AJAX hooks
        add_action('wp_ajax_imgseo_rename_image', array($this->renamer, 'ajax_rename_image'));
        add_action('wp_ajax_imgseo_get_rename_logs', array($this->renamer, 'ajax_get_rename_logs'));
        add_action('wp_ajax_imgseo_delete_rename_logs', array($this->renamer, 'ajax_delete_rename_logs'));
        add_action('wp_ajax_imgseo_restore_image', array($this->renamer, 'ajax_restore_image'));

        // Image existence check AJAX hook
        add_action('wp_ajax_imgseo_check_image_exists', array($this, 'ajax_check_image_exists'));
        add_action('wp_ajax_nopriv_imgseo_check_image_exists', array($this, 'ajax_check_image_exists'));

        // Log file cleanup - runs once per day on admin_init
        add_action('admin_init', array($this, 'maybe_cleanup_old_log_files'));

        // Conditional Hook Registration - WordPress best practice
        // Only register sitemap hooks if sitemap is enabled to reduce overhead
        $sitemap_enabled = get_option('imgseo_enable_sitemap', 0);

        if ($sitemap_enabled) {
            // Image Sitemap Hooks
            add_action('init', array($this->sitemap_generator, 'register_sitemap_rewrite_rule'), 10);
            // Hooks per invalidare la cache della sitemap
            add_action('save_post', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            add_action('delete_post', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            add_action('add_attachment', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            add_action('edit_attachment', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            add_action('delete_attachment', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            add_action('imgseo_image_renamed', array($this->sitemap_generator, 'invalidate_sitemap_cache'));
            // Hook per l'aggiornamento automatico della sitemap
            add_action('imgseo_auto_refresh_sitemap', array($this->sitemap_generator, 'auto_refresh_sitemap'));
        }


        // Hook di attivazione e disattivazione sono gestiti globalmente più avanti
    }



    /**

     * Plugin activation function

     */

    public static function on_activation() {

        // Create database tables

        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-activator.php';

        ImgSEO_Activator::activate();

        // Attiva anche il generatore di sitemap (per flushare le rewrite rules)
        // Prima dobbiamo assicurarci che la classe sia caricata
        require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-image-sitemap-generator.php';
        $sitemap_generator = ImgSEO_Image_Sitemap_Generator::get_instance();
        $sitemap_generator->activate();
    }



    /**
	 * Plugin deactivation function
	 */
	public static function on_deactivation() {
		// Remove cron jobs
		wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);
		wp_clear_scheduled_hook('imgseo_check_stuck_jobs');
		wp_clear_scheduled_hook('imgseo_cleanup_rename_logs');
		wp_clear_scheduled_hook('imgseo_auto_refresh_sitemap'); // Rimuovi cron sitemap

		// Disattiva anche il generatore di sitemap (per flushare le rewrite rules)
		// Prima dobbiamo assicurarci che la classe sia caricata
		require_once IMGSEO_DIRECTORY_PATH . 'includes/class-imgseo-image-sitemap-generator.php';
		$sitemap_generator = ImgSEO_Image_Sitemap_Generator::get_instance();
		$sitemap_generator->deactivate();
	}



    /**

     * Loads the text domain for translations

     */

    public function load_textdomain() {
        // Le traduzioni sono caricate automaticamente da WordPress per i plugin su WordPress.org
    }



    /**

     * Registers new uploads to ensure automatic generation works

     * Improved and more reliable method

     *

     * @param array $metadata Attachment metadata

     * @param int $attachment_id Attachment ID

     * @return array Unmodified metadata

     */

    public function log_new_attachment($metadata, $attachment_id) {
        imgseo_debug_log('log_new_attachment chiamato per ID: ' . $attachment_id . ' al ' . current_time('mysql'));

        // Debug della struttura dei metadati
        imgseo_debug_log('Metadati ricevuti: ' . (is_array($metadata) ? json_encode($metadata) : 'non è un array'));

        // Check if the attachment is an image
        $is_image = wp_attachment_is_image($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);
        $is_image_mime = strpos($mime_type, 'image/') === 0;

        imgseo_debug_log('Tipo MIME: ' . $mime_type . ', wp_attachment_is_image(): ' . ($is_image ? 'true' : 'false'));

        if (!$is_image && !$is_image_mime) {
            imgseo_debug_log('ID ' . $attachment_id . ' non è un\'immagine, uscita da log_new_attachment');
            return $metadata;
        }

        imgseo_debug_log('Nuova immagine caricata, ID: ' . $attachment_id);



        // Check if automatic generation is enabled
        $auto_generate = get_option('imgseo_auto_generate', 0);
        imgseo_debug_log('Opzione auto_generate: ' . $auto_generate);

        if ($auto_generate) {
            // Check if the image already has alt text
            $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            imgseo_debug_log('Testo alternativo attuale: ' . ($current_alt_text ? '"'.$current_alt_text.'"' : 'NESSUNO'));

            // Check the overwrite option
            $overwrite = get_option('imgseo_overwrite', 0);
            imgseo_debug_log('Opzione overwrite: ' . $overwrite);

            if (empty($current_alt_text) || $overwrite) {
                // Execute generation immediately
                imgseo_debug_log('Avvio immediato della generazione del testo alternativo per ID: ' . $attachment_id);
                update_post_meta($attachment_id, '_imgseo_pending_generation', time());

                // Verifica dei crediti
                $credits_exhausted = get_transient('imgseo_insufficient_credits');
                $credits = get_option('imgseo_credits', 0);
                imgseo_debug_log('Crediti disponibili: ' . $credits . ', esauriti: ' . ($credits_exhausted ? 'SI' : 'NO'));

                // Solo se ci sono crediti, procedi
                if (!$credits_exhausted && $credits > 0) {
                    imgseo_debug_log('Chiamata a process_single_generate per ID: ' . $attachment_id);
                    $this->generator->process_single_generate($attachment_id, 1);

                    // Schedule a fallback in case of issues
                    imgseo_debug_log('Pianificazione fallback tra 30 secondi');
                    wp_schedule_single_event(time() + 30, 'imgseo_single_generate', array($attachment_id, 2));
                } else {
                    imgseo_debug_log('Generazione automatica bloccata - crediti insufficienti');
                }
            } else {
                imgseo_debug_log('L\'immagine ha già un testo alternativo e overwrite è disattivato, salto generazione');
            }
        } else {
            imgseo_debug_log('Generazione automatica disattivata nelle impostazioni');
        }



        return $metadata;

    }



    /**

     * Safe handler for attachment updates to avoid recursion

     *

     * @param int $attachment_id Attachment ID

     */

    public function handle_attachment_update($attachment_id) {
        imgseo_debug_log('handle_attachment_update chiamato per ID: ' . $attachment_id . ' al ' . current_time('mysql'));

        static $is_processing = false;

        // Protection against recursion
        if ($is_processing) {
            imgseo_debug_log('Ricorsione rilevata in handle_attachment_update, ignorata');
            return;
        }

        $is_processing = true;

        // Verify it's an image
        $is_image = wp_attachment_is_image($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);
        $is_image_mime = strpos($mime_type, 'image/') === 0;

        imgseo_debug_log('Tipo MIME: ' . $mime_type . ', wp_attachment_is_image(): ' . ($is_image ? 'true' : 'false'));

        if (!$is_image && !$is_image_mime) {
            imgseo_debug_log('ID ' . $attachment_id . ' non è un\'immagine, uscita da handle_attachment_update');
            $is_processing = false;
            return;
        }



        // Check if automatic generation is enabled

        $auto_generate = get_option('imgseo_auto_generate', 0);

        imgseo_debug_log('Opzione auto_generate: ' . $auto_generate);



        if (!$auto_generate) {

            imgseo_debug_log('Generazione automatica disattivata nelle impostazioni');

            $is_processing = false;

            return;

        }



        // Check if it's already being processed

        $processing_lock = get_transient('imgseo_processing_' . $attachment_id);

        if ($processing_lock) {

            imgseo_debug_log('Elaborazione già in corso per ID: ' . $attachment_id);

            $is_processing = false;

            return;

        }



        // Set a temporary lock (10 seconds)

        set_transient('imgseo_processing_' . $attachment_id, true, 10);

        imgseo_debug_log('Lock temporaneo impostato per ID: ' . $attachment_id);



        // Check if the image already has alt text

        $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

        $overwrite = get_option('imgseo_overwrite', 0);

        imgseo_debug_log('Testo alternativo attuale: ' . ($current_alt_text ? '"'.$current_alt_text.'"' : 'NESSUNO') . ', overwrite: ' . $overwrite);



        if (!$overwrite && !empty($current_alt_text)) {

            imgseo_debug_log('L\'immagine ha già un testo alternativo e overwrite è disattivato, salto generazione');

            $is_processing = false;

            delete_transient('imgseo_processing_' . $attachment_id);

            return;

        }



        imgseo_debug_log('Verifica crediti per ID: ' . $attachment_id);



        // Verifica dei crediti

        $credits_exhausted = get_transient('imgseo_insufficient_credits');

        $credits = get_option('imgseo_credits', 0);

        imgseo_debug_log('Crediti disponibili: ' . $credits . ', esauriti: ' . ($credits_exhausted ? 'SI' : 'NO'));



        // Solo se ci sono crediti, procedi

        if (!$credits_exhausted && $credits > 0) {

            imgseo_debug_log('Avvio generazione per ID: ' . $attachment_id);

            // Execute generation immediately (first attempt)

            update_post_meta($attachment_id, '_imgseo_pending_generation', time());



            imgseo_debug_log('Chiamata a process_single_generate per ID: ' . $attachment_id);

            $this->generator->process_single_generate($attachment_id, 1);



            // Schedule a fallback attempt as backup

            imgseo_debug_log('Pianificazione fallback tra 30 secondi');

            if (!wp_next_scheduled('imgseo_single_generate', array($attachment_id, 2))) {

                wp_schedule_single_event(time() + 30, 'imgseo_single_generate', array($attachment_id, 2));

            }

        } else {

            imgseo_debug_log('Generazione automatica bloccata - crediti insufficienti');

        }



        // Reset state

        $is_processing = false;

        imgseo_debug_log('Reset stato processing per ID: ' . $attachment_id);



        // Remove the lock after processing

        delete_transient('imgseo_processing_' . $attachment_id);

        imgseo_debug_log('Lock temporaneo rimosso per ID: ' . $attachment_id);

    }



    /**

     * Handler for attachments uploaded via REST API

     *

     * @param WP_Post $attachment Attachment object

     */

    public function handle_rest_attachment($attachment) {

        if (!is_object($attachment) || !isset($attachment->ID)) {

            return;

        }



        $attachment_id = $attachment->ID;



        // Use the safe wrapper to avoid recursion

        $this->handle_attachment_update($attachment_id);

    }



    /**

     * Checks meta updates to trigger alt text generation

     * when needed.

     *

     * @param int $meta_id Metadata ID

     * @param int $post_id Post ID

     * @param string $meta_key Meta key

     * @param mixed $meta_value Meta value

     */

    public function check_image_alt_on_meta_update($meta_id, $post_id, $meta_key, $meta_value) {

        imgseo_debug_log('check_image_alt_on_meta_update chiamato per ID: ' . $post_id . ', meta_key: ' . $meta_key . ' al ' . current_time('mysql'));



        // Ignore _wp_attachment_image_alt meta updates to avoid recursion

        if ($meta_key === '_wp_attachment_image_alt' || $meta_key === '_imgseo_pending_generation') {

            imgseo_debug_log('meta_key ' . $meta_key . ' ignorata per evitare ricorsione');

            return;

        }



        // Check if it's an image and automatic generation is enabled

        $is_image = wp_attachment_is_image($post_id);

        $auto_generate = get_option('imgseo_auto_generate', 0);

        imgseo_debug_log('Verifica tipo: è immagine? ' . ($is_image ? 'SI' : 'NO') . ', auto_generate: ' . $auto_generate);



        if (!$is_image || !$auto_generate) {

            imgseo_debug_log('Uscita - non è immagine o generazione automatica disattivata');

            return;

        }



        // Check if the updated metadata is relevant for alt text

        $relevant_meta_keys = array('_wp_attachment_metadata', '_wp_attached_file');

        $is_relevant = in_array($meta_key, $relevant_meta_keys);

        imgseo_debug_log('meta_key ' . $meta_key . ' rilevante? ' . ($is_relevant ? 'SI' : 'NO'));



        if (!$is_relevant) {

            imgseo_debug_log('Uscita - meta_key non rilevante');

            return;

        }



        // Check if it's already being processed

        $processing_lock = get_transient('imgseo_processing_' . $post_id);

        if ($processing_lock) {

            imgseo_debug_log('Meta update - elaborazione già in corso per ID: ' . $post_id);

            return;

        }



        // Set a temporary lock (10 seconds)

        set_transient('imgseo_processing_' . $post_id, true, 10);

        imgseo_debug_log('Lock temporaneo impostato per ID: ' . $post_id);



        // Check if the image already has alt text

        $current_alt_text = get_post_meta($post_id, '_wp_attachment_image_alt', true);

        $overwrite = get_option('imgseo_overwrite', 0);

        imgseo_debug_log('Testo alternativo attuale: ' . ($current_alt_text ? '"'.$current_alt_text.'"' : 'NESSUNO') . ', overwrite: ' . $overwrite);



        // If it shouldn't overwrite and already has alt text, skip

        if (!$overwrite && !empty($current_alt_text)) {

            imgseo_debug_log('Uscita - testo alternativo già presente e overwrite disattivato');

            delete_transient('imgseo_processing_' . $post_id);

            return;

        }



        // Avoid double updates

        $pending_generation = get_post_meta($post_id, '_imgseo_pending_generation', true);

        if ($pending_generation) {

            $pending_time = intval($pending_generation);

            $current_time = time();

            $time_diff = $current_time - $pending_time;

            imgseo_debug_log('Generazione già in attesa da ' . $time_diff . ' secondi');



            if ($time_diff < 180) { // 3 minutes

                imgseo_debug_log('Generazione già pianificata, uscita');

                delete_transient('imgseo_processing_' . $post_id);

                return;

            }

        }



        imgseo_debug_log('Metadati immagine aggiornati, avvio generazione testo alt per ID: ' . $post_id);



        // Verifica dei crediti

        $credits_exhausted = get_transient('imgseo_insufficient_credits');

        $credits = get_option('imgseo_credits', 0);

        imgseo_debug_log('Crediti disponibili: ' . $credits . ', esauriti: ' . ($credits_exhausted ? 'SI' : 'NO'));



        // Solo se ci sono crediti, procedi

        if (!$credits_exhausted && $credits > 0) {

            // Execute generation immediately

            update_post_meta($post_id, '_imgseo_pending_generation', time());

            imgseo_debug_log('Chiamata a process_single_generate per ID: ' . $post_id);

            $this->generator->process_single_generate($post_id, 1);



            // Schedule a fallback attempt as backup

            imgseo_debug_log('Pianificazione fallback tra 30 secondi');

            if (!wp_next_scheduled('imgseo_single_generate', array($post_id, 2))) {

                wp_schedule_single_event(time() + 30, 'imgseo_single_generate', array($post_id, 2));

            }

        } else {

            imgseo_debug_log('Generazione automatica bloccata - crediti insufficienti');

        }



        // Remove the lock after processing
        delete_transient('imgseo_processing_' . $post_id);
        imgseo_debug_log('Lock temporaneo rimosso per ID: ' . $post_id);
    }

    /**
     * AJAX handler to check if an image exists
     */
    public function ajax_check_image_exists() {
        check_ajax_referer('imgseo_nonce', 'security');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }

        $attachment_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

        if (!$attachment_id) {
            wp_send_json_error(array('message' => 'Invalid attachment ID'));
        }

        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error(array('message' => 'Attachment not found', 'exists' => false));
        }

        // Check if it's an image
        if (!wp_attachment_is_image($attachment_id)) {
            wp_send_json_error(array('message' => 'Not an image', 'exists' => false));
        }

        // Check if the physical file exists
        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            wp_send_json_error(array('message' => 'Image file does not exist', 'exists' => false));
        }

        // Get image URL to verify it's accessible
        $image_url = wp_get_attachment_url($attachment_id);
        if (!$image_url) {
            wp_send_json_error(array('message' => 'Cannot get image URL', 'exists' => false));
        }

        wp_send_json_success(array(
            'exists' => true,
            'url' => $image_url,
            'file_path' => $file_path
        ));
    }

    /**
     * Cleanup old log files - runs once per day
     * Deletes log files older than 7 days to keep the uploads directory clean
     */
    public function maybe_cleanup_old_log_files() {
        // Use transient to run only once per day
        $last_cleanup = get_transient('imgseo_last_log_cleanup');
        if ($last_cleanup) {
            return;
        }

        // Set transient to prevent running again for 24 hours
        set_transient('imgseo_last_log_cleanup', time(), DAY_IN_SECONDS);

        // Cleanup old log files (older than 7 days)
        if (class_exists('ImgSEO_File_Logger')) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $deleted = $file_logger->cleanup_old_logs(7);

            if ($deleted > 0) {
                imgseo_debug_log('Cleaned up ' . $deleted . ' old log files');
            }
        }
    }


    /**
     * AJAX handler per gestire il flag operazioni bulk intensive
     * Questo aiuta a evitare falsi positivi SEO durante operazioni massive
     */
    public function ajax_set_bulk_operation_flag() {
        // Verifica nonce
        if (!wp_verify_nonce($_POST['security'] ?? '', 'imgseo_nonce')) {
            wp_send_json_error(array('message' => 'Nonce verification failed'));
            return;
        }

        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        $active = isset($_POST['active']) && intval($_POST['active']) === 1;

        if ($active) {
            // Segnala che sono in corso operazioni bulk intensive
            set_transient('imgseo_bulk_operations_active', time(), 3600); // 1 ora max
        } else {
            // Rimuovi il flag
            delete_transient('imgseo_bulk_operations_active');
        }

        wp_send_json_success(array(
            'bulk_active' => $active,
            'message' => $active ? 'Bulk operations flag set' : 'Bulk operations flag cleared'
        ));
    }

    /**
     * Funzione helper per plugin SEO per verificare operazioni bulk in corso
     * I plugin SEO possono utilizzare questa funzione per evitare falsi positivi
     *
     * @return bool True se sono in corso operazioni bulk intensive
     */
    public static function is_bulk_operation_active() {
        return get_transient('imgseo_bulk_operations_active') !== false;
    }


    /**
     * Verifica e aggiorna automaticamente i crediti se necessario
     * Chiamato all'inizializzazione del plugin per garantire che i crediti
     * siano disponibili per la generazione automatica
     */
    private function maybe_refresh_credits() {
        // Controlla se esiste un'API key
        $api_key = get_option('imgseo_api_key', '');
        if (empty($api_key)) {
            imgseo_debug_log('maybe_refresh_credits: nessuna API key configurata');
            return;
        }

        // Check if token is marked as invalid - don't make API calls
        if (get_transient('imgseo_invalid_api_token') === true) {
            imgseo_debug_log('maybe_refresh_credits: API token is invalid, skipping credit check');
            return;
        }

        // Controlla se i crediti sono già stati verificati
        $credits = get_option('imgseo_credits', false);
        $last_check = get_option('imgseo_last_check', 0);
        $time_since_last_check = time() - $last_check;

        // Verifica i crediti se:
        // 1. Non sono mai stati verificati (credits === false)
        // 2. È passato più di 1 giorno dall'ultimo controllo (86400 secondi)
        $should_check = false;

        if ($credits === false) {
            imgseo_debug_log('maybe_refresh_credits: crediti non inizializzati, verifica necessaria');
            $should_check = true;
        } elseif ($time_since_last_check > 86400) {
            imgseo_debug_log('maybe_refresh_credits: ultimo controllo > 24h fa, verifica necessaria');
            $should_check = true;
        }

        if (!$should_check) {
            imgseo_debug_log('maybe_refresh_credits: verifica non necessaria (crediti: ' . $credits . ', ultimo controllo: ' . human_time_diff($last_check) . ' fa)');
            return;
        }

        // Evita verifiche multiple simultanee usando un transient
        $checking_key = 'imgseo_checking_credits';
        if (get_transient($checking_key)) {
            imgseo_debug_log('maybe_refresh_credits: verifica già in corso, skip');
            return;
        }

        // Imposta un lock per 5 minuti
        set_transient($checking_key, true, 300);

        imgseo_debug_log('maybe_refresh_credits: inizio verifica crediti in background');

        // Esegui la verifica dei crediti in modo sincrono ma leggero
        // Usiamo get_account_details che ha già un sistema di cache interno
        try {
            $account_details = $this->api->get_account_details();

            if ($account_details !== false && isset($account_details['available'])) {
                $new_credits = (float) $account_details['available'];
                imgseo_debug_log('maybe_refresh_credits: crediti aggiornati a ' . $new_credits);

                // I crediti sono già salvati da get_account_details()
                // ma verifichiamo comunque il transient per crediti insufficienti
                if ($new_credits <= 0) {
                    set_transient('imgseo_insufficient_credits', true, 3600);
                    imgseo_debug_log('maybe_refresh_credits: crediti insufficienti, transient impostato');
                } else {
                    delete_transient('imgseo_insufficient_credits');
                    imgseo_debug_log('maybe_refresh_credits: crediti sufficienti, transient rimosso');
                }
            } else {
                imgseo_debug_log('maybe_refresh_credits: impossibile recuperare crediti dall\'API');
            }
        } catch (Exception $e) {
            imgseo_debug_log('maybe_refresh_credits: errore durante verifica - ' . $e->getMessage());
        }

        // Rimuovi il lock
        delete_transient($checking_key);
    }

    /**
     * WordPress best practice: Get cached option to reduce database queries
     * Cache frequently accessed options to avoid repeated get_option() calls
     *
     * @param string $option_name The option name
     * @param mixed $default The default value if option doesn't exist
     * @return mixed The option value
     */
    public function get_cached_option($option_name, $default = false) {
        if (!isset($this->settings_cache[$option_name])) {
            $this->settings_cache[$option_name] = get_option($option_name, $default);
        }
        return $this->settings_cache[$option_name];
    }

    /**
     * Clear option cache when option is updated
     * Should be called after update_option() for cached options
     *
     * @param string $option_name The option name to clear from cache
     */
    public function clear_option_cache($option_name = null) {
        if ($option_name === null) {
            $this->settings_cache = array();
        } else {
            unset($this->settings_cache[$option_name]);
        }
    }
}



// Register activation and deactivation hooks

register_activation_hook(IMGSEO_FILE, array('IMGSEO_Init', 'on_activation'));

register_deactivation_hook(IMGSEO_FILE, array('IMGSEO_Init', 'on_deactivation'));



/**
 * Funzione globale per verificare se ImgSEO sta eseguendo operazioni bulk
 * Utilizzabile da altri plugin per evitare conflitti
 *
 * @return bool True se sono in corso operazioni bulk intensive
 */
function imgseo_is_bulk_processing() {
    return IMGSEO_Init::is_bulk_operation_active();
}

// Initialize the plugin when WordPress is ready

add_action('plugins_loaded', array('IMGSEO_Init', 'init'));
