<?php
/**
 * Classe per la generazione del testo alternativo
 * 
 * @package ImgSEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe ImgSEO_Alt_Text_Generator
 * Gestisce la generazione del testo alternativo per le immagini
 */
class ImgSEO_Alt_Text_Generator extends ImgSEO_Generator_Base {
    
    /**
     * Constructor - semplificato per evitare registrazioni multiple
     */
    public function __construct() {
        // Hook per imgseo_single_generate rimosso per evitare duplicazioni
        // Il hook principale è gestito nel file imgseo.php tramite generator-main
    }
    
    /**
     * Inizializza gli hook necessari - questo metodo deve essere chiamato solo una volta
     * dal file principale per evitare registrazioni multiple
     */
    public static function initialize_hooks() {
        // Registrazione centralizzata per evitare duplicati
        static $initialized = false;
        
        if ($initialized) {
            imgseo_debug_log('initialize_hooks already called, ignoring duplicate');
            return;
        }
        
        $initialized = true;
        imgseo_debug_log('initialize_hooks eseguito - inizio registrazione');
        
        // Ottieni l'istanza singleton per la registrazione
        $instance = IMGSEO_Init::init()->generator->get_alt_text_generator();
        if (!$instance) {
            imgseo_debug_log('ERRORE CRITICO - impossibile ottenere istanza generator');
            return;
        }
        
        imgseo_debug_log('Generator instance obtained: ' . (is_object($instance) ? get_class($instance) : 'not an object'));
        
        // Registra l'hook standard di WordPress per gli upload
        add_action('add_attachment', array($instance, 'auto_generate_alt_text'), 10);
        imgseo_debug_log('Hook add_attachment registered for auto_generate_alt_text - priority 10');
        
        // Registra anche un hook per upload API REST (e.g. Gutenberg)
        add_action('rest_insert_attachment', array($instance, 'auto_generate_alt_text'), 10);
        imgseo_debug_log('Hook rest_insert_attachment registered for auto_generate_alt_text - priority 10');
        
        // Registrazione debug sulla funzione di callback
        imgseo_debug_log('Callback function: ' . (is_callable(array($instance, 'auto_generate_alt_text')) ? 'is callable' : 'is NOT callable'));
    }
    
    /**
     * Generates alt text automatically when a new image is uploaded
     * Simple version without multiple attempts
     *
     * @param int|WP_Post $attachment_id Attachment ID or WP_Post object
     */
    public function auto_generate_alt_text($attachment_id) {
        // FIX: Gestisce sia ID intero che oggetto WP_Post
        // Gutenberg e alcuni contesti REST API possono passare l'oggetto invece dell'ID
        if (is_object($attachment_id)) {
            if (isset($attachment_id->ID)) {
                // Oggetto WP_Post valido
                $attachment_id = $attachment_id->ID;
            } else {
                // Oggetto non valido, impossibile procedere
                imgseo_debug_log('auto_generate_alt_text: invalid object received (no ID property)');
                return;
            }
        }

        // Converte esplicitamente a intero per sicurezza
        $attachment_id = (int) $attachment_id;

        // Validazione ID
        if ($attachment_id <= 0) {
            imgseo_debug_log('auto_generate_alt_text: invalid attachment ID: ' . $attachment_id);
            return;
        }

        imgseo_debug_log('auto_generate_alt_text chiamato per ID: ' . $attachment_id);

        // Verifica che sia un'immagine
        if (!wp_attachment_is_image($attachment_id)) {
            imgseo_debug_log('ID ' . $attachment_id . ' is not an image, exiting');
            return;
        }

        // Verifica che il formato sia supportato dall'API
        // Formati supportati: JPEG, PNG, WebP, AVIF, HEIC, BMP, GIF, TIFF
        if (!self::is_supported_image_format($attachment_id)) {
            $mime_type = get_post_mime_type($attachment_id);
            imgseo_debug_log('ID ' . $attachment_id . ' has unsupported format (' . $mime_type . '), skipping. Supported: ' . self::get_supported_formats_string());
            return;
        }
        
        // Verifica se la funzionalità è abilitata
        $auto_generate = get_option('imgseo_auto_generate', 0);
        imgseo_debug_log('auto_generate is: ' . ($auto_generate ? 'ENABLED' : 'DISABLED'));
        
        if (!$auto_generate) {
            imgseo_debug_log('Generazione automatica disattivata, ignorando');
            return;
        }
        
        // Enhanced overwrite logic: check ALL selected fields, not just alt text
        $overwrite = get_option('imgseo_overwrite', 0);

        if (!$overwrite) {
            // Get which fields are enabled for generation
            $update_title = (bool) get_option('imgseo_update_title', 0);
            $update_caption = (bool) get_option('imgseo_update_caption', 0);
            $update_description = (bool) get_option('imgseo_update_description', 0);

            $should_skip = true;

            // Check alt text (always checked)
            $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if (empty($current_alt_text)) {
                $should_skip = false;
                imgseo_debug_log('Alt text is empty, will process');
            }

            // Check title if enabled
            if ($should_skip && $update_title) {
                $attachment = get_post($attachment_id);
                $current_title = $attachment ? $attachment->post_title : '';
                if (empty($current_title)) {
                    $should_skip = false;
                    imgseo_debug_log('Title is empty and update_title enabled, will process');
                }
            }

            // Check caption if enabled
            if ($should_skip && $update_caption) {
                $attachment = isset($attachment) ? $attachment : get_post($attachment_id);
                $current_caption = $attachment ? $attachment->post_excerpt : '';
                if (empty($current_caption)) {
                    $should_skip = false;
                    imgseo_debug_log('Caption is empty and update_caption enabled, will process');
                }
            }

            // Check description if enabled
            if ($should_skip && $update_description) {
                $attachment = isset($attachment) ? $attachment : get_post($attachment_id);
                $current_description = $attachment ? $attachment->post_content : '';
                if (empty($current_description)) {
                    $should_skip = false;
                    imgseo_debug_log('Description is empty and update_description enabled, will process');
                }
            }

            // Skip only if ALL selected fields are already populated
            if ($should_skip) {
                imgseo_debug_log('All selected fields are already populated and overwrite disabled, ignoring');
                return;
            }
        }

        imgseo_debug_log('Overwrite enabled or at least one field is empty, proceeding with generation');
        
        // Verifica crediti disponibili
        $credits_exhausted = get_transient('imgseo_insufficient_credits');
        $credits = get_option('imgseo_credits', 0);
        imgseo_debug_log('Crediti disponibili: ' . $credits . ', esauriti: ' . ($credits_exhausted ? 'SÌ' : 'NO'));
        
        if ($credits_exhausted || $credits < 1) {
            imgseo_debug_log('Crediti insufficienti, generazione saltata');
            return;
        }
        
        // Previene multiple elaborazioni per la stessa immagine
        $processing_key = 'imgseo_processing_' . $attachment_id;
        if (get_transient($processing_key)) {
            imgseo_debug_log('Processing already in progress for ID: ' . $attachment_id . ', ignoring');
            return;
        }
        
        // Imposta un lock di 60 secondi
        set_transient($processing_key, true, 60);
        
        try {
            // Nota: abbiamo rimosso il sleep(1) che poteva interrompere l'esecuzione
            
            // Ottieni l'URL dell'immagine
            $image_url = wp_get_attachment_url($attachment_id);
            if (!$image_url) {
                imgseo_debug_log('Impossibile ottenere URL per ID: ' . $attachment_id);
                return;
            }
            
            // Usa una versione ridotta se disponibile per migliorare le performance
            $sizes = array('large', 'medium_large', 'medium', 'thumbnail');
            foreach ($sizes as $size) {
                $image_data = wp_get_attachment_image_src($attachment_id, $size);
                if ($image_data && !empty($image_data[0])) {
                    $image_url = $image_data[0];
                    imgseo_debug_log('Usando versione ridotta ' . $size . ': ' . $image_url);
                    break;
                }
            }
            
            // Ottieni il titolo del post genitore se disponibile
            $parent_post_id = wp_get_post_parent_id($attachment_id);
            $parent_post_title = $parent_post_id ? get_the_title($parent_post_id) : '';
            
            // Genera il testo alternativo e metadata (NEW API v2.0) con override coerenti
            imgseo_debug_log('Avvio generazione per ID: ' . $attachment_id);
            $field_overrides = array(
                'update_title' => (bool) get_option('imgseo_update_title', 0),
                'update_caption' => (bool) get_option('imgseo_update_caption', 0),
                'update_description' => (bool) get_option('imgseo_update_description', 0),
                // Note: update_filename removed - filename generation is now handled exclusively
                // by the Auto-Rename system (System 2) which calls the API separately
                // when the time is right (after thumbnail generation)
            );
            $response = $this->generate_alt_text($image_url, $attachment_id, $parent_post_title, $field_overrides);

            if (is_wp_error($response)) {
                imgseo_debug_log('Error during generation: ' . $response->get_error_message());
                return;
            }

            // Extract generated data
            $data = isset($response['data']) ? $response['data'] : array();

            if (empty($data['alt_text'])) {
                imgseo_debug_log('Error: No alt_text in response');
                return;
            }

            // Save alt text
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $data['alt_text']);
            imgseo_debug_log('Alt text generated and saved: "' . $data['alt_text'] . '"');

            // Save other metadata fields if generated
            $attachment_update = array();

            if (isset($data['title']) && !empty($data['title'])) {
                $attachment_update['post_title'] = $data['title'];
                imgseo_debug_log('Title generated: "' . $data['title'] . '"');
            }

            if (isset($data['caption']) && !empty($data['caption'])) {
                $attachment_update['post_excerpt'] = $data['caption'];
                imgseo_debug_log('Caption generated: "' . $data['caption'] . '"');
            }

            if (isset($data['description']) && !empty($data['description'])) {
                $attachment_update['post_content'] = $data['description'];
                imgseo_debug_log('Description generated: "' . $data['description'] . '"');
            }

            // Update attachment if we have metadata fields
            if (!empty($attachment_update)) {
                $attachment_update['ID'] = $attachment_id;

                // Prevent recursion
                remove_action('attachment_updated', array(IMGSEO_Init::init(), 'handle_attachment_update'), 20);
                wp_update_post($attachment_update);
                add_action('attachment_updated', array(IMGSEO_Init::init(), 'handle_attachment_update'), 20);

                imgseo_debug_log('Metadata fields updated');
            }

            // DEPRECATED: Old System 1 (imgseo_update_filename) has been removed
            // Filename renaming is now handled exclusively by System 2 (imgseo_auto_rename_on_upload)
            // System 2 uses a two-phase approach that waits for WordPress to generate thumbnails first
            // This prevents timing conflicts and ensures all thumbnails are renamed correctly

            // MIGRATION: Auto-migrate users from old system to new system
            $old_system_active = (bool) get_option('imgseo_update_filename', 0);
            if ($old_system_active) {
                imgseo_debug_log('MIGRATION: Detected old system (imgseo_update_filename) is active.');
                imgseo_debug_log('Auto-migrating to new system (imgseo_auto_rename_on_upload).');

                // Enable new system
                update_option('imgseo_auto_rename_on_upload', 1);

                // Disable old system
                update_option('imgseo_update_filename', 0);

                imgseo_debug_log('Migration completed: Old system disabled, new system enabled.');
            }

            // Note: Filename will be applied by System 2 after thumbnail generation
            if (isset($data['filename']) && !empty($data['filename'])) {
                imgseo_debug_log('Filename generated by API: "' . $data['filename'] . '"');
                imgseo_debug_log('Will be applied by Auto-Rename system after thumbnail generation.');
            }

            // Log credit usage
            if (isset($response['credits']['cost'])) {
                imgseo_debug_log('Credits used: ' . $response['credits']['cost']);
                imgseo_debug_log('Credits remaining: ' . $response['credits']['remaining']);
            }
        } catch (Exception $e) {
            imgseo_debug_log('Eccezione durante la generazione: ' . $e->getMessage());
        } finally {
            // Rimuovi il lock di elaborazione
            delete_transient($processing_key);
        }
    }
    
    /**
     * Handles AJAX request to generate alt text
     */
    public function handle_generate_alt_text() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        // FIX: Handle both types of possible parameters
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) :
                       (isset($_POST['image_id']) ? intval($_POST['image_id']) : 0);
        
        if (!$attachment_id) {
            wp_send_json_error(['message' => 'Missing or invalid attachment ID']);
            return;
        }
        
        // Verify the attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment) {
            wp_send_json_error(['message' => 'Attachment not found']);
            return;
        }
        
        // Verify it's an image
        if (!wp_attachment_is_image($attachment_id)) {
            wp_send_json_error(['message' => 'The attachment is not an image']);
            return;
        }

        // Verify the image format is supported by the API
        // Supported formats: JPEG, PNG, WebP, AVIF, HEIC, BMP, GIF, TIFF
        if (!self::is_supported_image_format($attachment_id)) {
            $mime_type = get_post_mime_type($attachment_id);
            wp_send_json_error([
                'message' => sprintf(
                    /* translators: 1: image mime type, 2: supported formats list. */
                    __('Unsupported image format (%1$s). Supported formats: %2$s', 'imgseo-ai-alt-text-generator'),
                    $mime_type,
                    self::get_supported_formats_string()
                )
            ]);
            return;
        }
        
        // Use smaller image size to optimize API call
        $image_url = null;
        $image_sizes = array('large', 'medium_large', 'medium', 'thumbnail');
        
        // Try to get a thumbnail version first
        foreach ($image_sizes as $size) {
            $image_size = wp_get_attachment_image_src($attachment_id, $size);
            if ($image_size && is_array($image_size) && !empty($image_size[0])) {
                $image_url = $image_size[0];
                break;
            }
        }
        
        // Fallback to original if no thumbnails available
        if (!$image_url) {
            $image_url = wp_get_attachment_url($attachment_id);
        }
        
        if (!$image_url) {
            wp_send_json_error(['message' => 'URL immagine non trovato']);
            return;
        }

        // Fix: Ensure filename is always a string to prevent JSON errors
        $filename = basename($image_url);
        if (empty($filename) || is_array($filename) || is_object($filename)) {
            $filename = 'image-' . $attachment_id . '.jpg';
        }
        
        // Ottieni job_id se presente e spostalo in alto per la logica di overwrite
        $job_id = isset($_POST['job_id']) ? sanitize_text_field($_POST['job_id']) : '';

        static $table_exists = null;
        
        // Determina il valore di overwrite con priorità: POST > Job > Global
        $overwrite = false;
        
        // 1. Controlla se è passato via POST
        if (isset($_POST['overwrite'])) {
            $overwrite = (bool) $_POST['overwrite'];
        } 
        // 2. Controlla se c'è un job_id e recupera dal DB
        elseif (!empty($job_id)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'imgseo_jobs';
            
            // Verifica se la tabella esiste (cache per performance)
            if ($table_exists === null) {
                $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
            }
            
            if ($table_exists) {
                $job_overwrite = $wpdb->get_var($wpdb->prepare(
                    "SELECT overwrite FROM $table_name WHERE job_id = %s",
                    $job_id
                ));
                
                if ($job_overwrite !== null) {
                    $overwrite = (bool) $job_overwrite;
                } else {
                    // Fallback alle opzioni globali se il job non si trova (caso raro)
                    $overwrite = get_option('imgseo_overwrite', 0);
                }
            } else {
                $overwrite = get_option('imgseo_overwrite', 0);
            }
        } 
        // 3. Fallback alle opzioni globali
        else {
            $overwrite = get_option('imgseo_overwrite', 0);
        }

        // Enhanced overwrite logic: check ALL selected fields for manual generation
        if (!$overwrite) {
            // Get which fields are enabled for generation
            $update_title = isset($_POST['update_title']) ? (bool)$_POST['update_title'] : (bool) get_option('imgseo_update_title', 0);
            $update_caption = isset($_POST['update_caption']) ? (bool)$_POST['update_caption'] : (bool) get_option('imgseo_update_caption', 0);
            $update_description = isset($_POST['update_description']) ? (bool)$_POST['update_description'] : (bool) get_option('imgseo_update_description', 0);

            $should_skip = true;

            // Check alt text (always checked)
            $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if (empty($current_alt_text)) {
                $should_skip = false;
            }

            // Check title if enabled
            if ($should_skip && $update_title) {
                $current_title = get_the_title($attachment_id);
                if (empty($current_title)) {
                    $should_skip = false;
                }
            }

            // Check caption if enabled
            if ($should_skip && $update_caption) {
                $attachment = get_post($attachment_id);
                $current_caption = $attachment ? $attachment->post_excerpt : '';
                if (empty($current_caption)) {
                    $should_skip = false;
                }
            }

            // Check description if enabled
            if ($should_skip && $update_description) {
                $attachment = isset($attachment) ? $attachment : get_post($attachment_id);
                $current_description = $attachment ? $attachment->post_content : '';
                if (empty($current_description)) {
                    $should_skip = false;
                }
            }

            // Skip only if ALL selected fields are already populated
            if ($should_skip) {
                wp_send_json_error([
                    'message' => __('All selected fields are already populated. Enable "Overwrite" to regenerate.', 'imgseo-ai-alt-text-generator'),
                    'filename' => basename($image_url)
                ]);
                return;
            }
        }

        // Verifica cache prima di procedere con la generazione
        $cached_result = get_transient('imgseo_alt_text_' . $attachment_id);
        if ($cached_result && !empty($cached_result) && !isset($_POST['force_refresh'])) {

            // Crea un array per la risposta
            $response_data = [
                'alt_text' => $cached_result,
                'image_url' => $image_url,
                'filename' => basename($image_url),
                'cached' => true
            ];

            wp_send_json_success($response_data);
            return;
        }
        
        // Nuovo controllo crediti con il flusso a tre fasi
        // La verifica effettiva dei crediti sarà gestita dal generator base
        // Qui facciamo solo un controllo preliminare per evitare chiamate inutili
        $credits = get_option('imgseo_credits', 0);
        $insufficient_credits_transient = get_transient('imgseo_insufficient_credits');
        
        if ($insufficient_credits_transient && $credits <= 0) {
            wp_send_json_error([
                'message' => 'Insufficient ImgSEO credits. Please purchase more credits to continue.',
                'credits' => $credits,
                'phase' => 'preliminary_check'
            ]);
            return;
        }
        
        // Ottieni il titolo della pagina genitore se disponibile
        $parent_post_id = get_post_field('post_parent', $attachment_id);
        $parent_post_title = $parent_post_id ? get_the_title($parent_post_id) : '';
        
        // Ottieni il nome del file per i log
        $filename = basename($image_url);
        
        // Aggiungi un parametro univoco all'URL dell'immagine per evitare cache CDN
        $unique_image_url = add_query_arg('t', time(), $image_url);
        
        // Raccogli field settings con priorità: POST > Job DB > Global Options
        $field_overrides = array();

        // Determina i field settings con la stessa logica di overwrite
        // 1. Controlla se passati via POST
        if (isset($_POST['update_title'])) {
            $field_overrides['update_title'] = (bool)$_POST['update_title'];
        }
        if (isset($_POST['update_caption'])) {
            $field_overrides['update_caption'] = (bool)$_POST['update_caption'];
        }
        if (isset($_POST['update_description'])) {
            $field_overrides['update_description'] = (bool)$_POST['update_description'];
        }

        // 2. Se non passati via POST e c'è un job_id, recupera dal DB
        if (!empty($job_id) && (empty($field_overrides) || count($field_overrides) < 3)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'imgseo_jobs';

            // Usa la stessa cache table_exists di prima
            if ($table_exists === null) {
                $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
            }

            if ($table_exists) {
                $job_settings = $wpdb->get_row($wpdb->prepare(
                    "SELECT update_title, update_caption, update_description FROM $table_name WHERE job_id = %s",
                    $job_id
                ), ARRAY_A);

                if ($job_settings) {
                    // Usa i valori dal job solo se non già impostati via POST
                    if (!isset($field_overrides['update_title'])) {
                        $field_overrides['update_title'] = (bool) $job_settings['update_title'];
                    }
                    if (!isset($field_overrides['update_caption'])) {
                        $field_overrides['update_caption'] = (bool) $job_settings['update_caption'];
                    }
                    if (!isset($field_overrides['update_description'])) {
                        $field_overrides['update_description'] = (bool) $job_settings['update_description'];
                    }
                }
            }
        }
        // Note: update_filename POST parameter removed - filename generation now handled by Auto-Rename system

        // Merge overrides with global defaults
        $global_overrides = array(
            'update_title' => (bool) get_option('imgseo_update_title', 0),
            'update_caption' => (bool) get_option('imgseo_update_caption', 0),
            'update_description' => (bool) get_option('imgseo_update_description', 0),
            // Note: update_filename removed - Auto-Rename system handles this separately
        );
        $field_overrides = array_merge($global_overrides, $field_overrides);

        // Generate alt text and metadata using new API v2.0 (con override opzionali)
        $response = $this->generate_alt_text($unique_image_url, $attachment_id, $parent_post_title, $field_overrides);

        if (is_wp_error($response)) {
            $error_code = $response->get_error_code();
            $error_message = $response->get_error_message();

            // Log error to file if we have a job_id
            if (!empty($job_id)) {
                $file_logger = ImgSEO_File_Logger::get_instance();
                $file_logger->add_log($job_id, $attachment_id, $filename, $image_url, '', 'error', $error_message);
            }

            // Gestione specifica per errori di crediti insufficienti
            if ($error_code === 'insufficient_credits') {
                set_transient('imgseo_insufficient_credits', true, 3600);
                wp_send_json_error([
                    'message' => 'Insufficient ImgSEO credits. Please purchase more credits to continue.',
                    'filename' => $filename,
                    'error_type' => 'insufficient_credits',
                    'phase' => 'credit_verification'
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Error generating alt text: ' . $error_message,
                    'filename' => $filename,
                    'error_type' => $error_code,
                    'phase' => 'generation'
                ]);
            }
            return;
        }

        // Extract generated data from response
        $data = isset($response['data']) ? $response['data'] : array();

        if (empty($data['alt_text'])) {
            // Log error to file if we have a job_id
            if (!empty($job_id)) {
                $file_logger = ImgSEO_File_Logger::get_instance();
                $file_logger->add_log($job_id, $attachment_id, $filename, $image_url, '', 'error', 'No alt text in API response');
            }
            wp_send_json_error([
                'message' => 'No alt text in API response',
                'filename' => $filename
            ]);
            return;
        }

        $alt_text = $data['alt_text'];

        // Update alt text
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);

        // CLEAR CACHE: Invalidate stats cache
        delete_transient('imgseo_images_without_alt_count_v2');

        // Prepare response data
        $response_data = [
            'alt_text' => $alt_text,
            'image_url' => $image_url,
            'page_title' => $parent_post_title,
            'filename' => $filename
        ];

        // Update metadata fields if generated
        $attachment_update = array();

        if (isset($data['title']) && !empty($data['title'])) {
            $attachment_update['post_title'] = $data['title'];
            $response_data['title'] = $data['title'];
        }

        if (isset($data['caption']) && !empty($data['caption'])) {
            $attachment_update['post_excerpt'] = $data['caption'];
            $response_data['caption'] = $data['caption'];
        }

        if (isset($data['description']) && !empty($data['description'])) {
            $attachment_update['post_content'] = $data['description'];
            $response_data['description'] = $data['description'];
        }

        // Update attachment if we have metadata
        if (!empty($attachment_update)) {
            $attachment_update['ID'] = $attachment_id;
            wp_update_post($attachment_update);
        }

        // Handle filename renaming if generated (apply same features as single renamer)
        if (isset($data['filename']) && !empty($data['filename'])) {
            if (class_exists('Renamer_File_Processor')) {
                $renamer = Renamer_File_Processor::get_instance();
                if ($renamer && method_exists($renamer, 'rename_image')) {
                    // Build options consistent with single-image renaming
                    $rename_options = array(
                        'sanitize' => true,
                        'update_references' => true
                    );

                    // Pull sanitization and duplicate handling settings if available
                    if (class_exists('Renamer_Settings_Manager')) {
                        $settings_manager = Renamer_Settings_Manager::get_instance();
                        $rename_options['remove_accents'] = $settings_manager->is_enabled('remove_accents', true);
                        $rename_options['lowercase'] = $settings_manager->is_enabled('lowercase', true);
                        $rename_options['handle_duplicates'] = $settings_manager->get_setting('handle_duplicates', 'increment');
                    } else {
                        // Sensible defaults
                        $rename_options['remove_accents'] = true;
                        $rename_options['lowercase'] = true;
                        $rename_options['handle_duplicates'] = 'increment';
                    }

                    $rename_result = $renamer->rename_image($attachment_id, $data['filename'], $rename_options);

                    if (!is_wp_error($rename_result) && is_array($rename_result)) {
                        $response_data['renamed_filename'] = isset($rename_result['new_filename']) ? $rename_result['new_filename'] : $data['filename'];
                        if (!empty($rename_result['new_url'])) {
                            $response_data['renamed_url'] = $rename_result['new_url'];
                        }
                    }
                }
            }
        }

        // CRITICAL: Always update credits from API response to keep them synchronized
        if (isset($response['credits_remaining'])) {
            $credits_remaining = floatval($response['credits_remaining']);
            update_option('imgseo_credits', $credits_remaining);
            $response_data['credits_remaining'] = $credits_remaining;

            // Clear insufficient credits flag if we have credits
            if ($credits_remaining > 0) {
                delete_transient('imgseo_insufficient_credits');
            }
        }

        // Legacy support - old API response format
        if (isset($response['credits'])) {
            $response_data['credits'] = $response['credits'];
        }

        // Legacy support - keep these checks for backward compatibility but they won't do anything
        // since fields are now generated by API
        $update_title = isset($_POST['update_title']) ? (bool)$_POST['update_title'] : get_option('imgseo_update_title', 0);
        $update_caption = isset($_POST['update_caption']) ? (bool)$_POST['update_caption'] : get_option('imgseo_update_caption', 0);
        $update_description = isset($_POST['update_description']) ? (bool)$_POST['update_description'] : get_option('imgseo_update_description', 0);

        // This section is now deprecated - fields are generated by API if enabled in settings
        $attachment_data = [
            'ID' => $attachment_id
        ];

        // Skip this legacy code since API now handles it
        if (false && ($update_title || $update_caption || $update_description)) {
            $attachment_data['post_title'] = $alt_text;
            $response_data['title'] = $alt_text;
        }
        
        // Fallback legacy behavior ONLY if API did not return these fields
        if ($update_caption && empty($response_data['caption'])) {
            $attachment_data['post_excerpt'] = $alt_text;
            $response_data['caption'] = $alt_text;
        }
        
        if ($update_description && empty($response_data['description'])) {
            $attachment_data['post_content'] = $alt_text;
            $response_data['description'] = $alt_text;
        }
        
        // Se c'è almeno un campo da aggiornare, aggiorna l'allegato
        if (count($attachment_data) > 1) {
            $result = wp_update_post($attachment_data);
            
            if (is_wp_error($result)) {
            }
        }
        
        // Se abbiamo un job_id, aggiungi un log dell'operazione (file-based)
        if (!empty($job_id)) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->add_log(
                $job_id,
                $attachment_id,
                $filename,
                $image_url,
                $alt_text,
                'success',
                '', // message
                isset($response_data['title']) ? $response_data['title'] : '',
                isset($response_data['caption']) ? $response_data['caption'] : '',
                isset($response_data['description']) ? $response_data['description'] : ''
            );
        }
        
        wp_send_json_success($response_data);
    }
    
    /**
     * Processa la generazione del testo alternativo per una singola immagine
     * Versione migliorata con protezione anti-ricorsione
     * Chiamato dal cron job pianificato da auto_generate_alt_text
     *
     * @param int|WP_Post $attachment_id ID dell'allegato o oggetto WP_Post
     * @param int $attempt_number Numero del tentativo (opzionale)
     */
    public function process_single_generate($attachment_id, $attempt_number = 1) {
        // FIX: Gestisce sia ID intero che oggetto WP_Post
        if (is_object($attachment_id)) {
            if (isset($attachment_id->ID)) {
                $attachment_id = $attachment_id->ID;
            } else {
                return;
            }
        }

        // Converte esplicitamente a intero
        $attachment_id = (int) $attachment_id;

        // Validazione ID
        if ($attachment_id <= 0) {
            return;
        }

        // Check if API token is invalid - don't process
        if (get_transient('imgseo_invalid_api_token') === true) {
            error_log('[ImgSEO] Skipping process_single_generate for attachment ' . $attachment_id . ' - API token is invalid');
            return;
        }

        // ========== PROTEZIONE CONTRO RICORSIONE MIGLIORATA ===========
        // Verifica se c'è un lock attivo per evitare elaborazioni multiple simultanee
        $processing_lock = get_transient('imgseo_processing_' . $attachment_id);
        if ($processing_lock) {
            return;
        }
        
        // Imposta un lock temporaneo (30 secondi max per completare la generazione)
        // Usa la funzione di WordPress con terzo parametro = true per garantire che solo
        // un processo possa impostare il transient (protezione contro race condition)
        $lock_set = set_transient('imgseo_processing_' . $attachment_id, true, 30);
        
        // Se non siamo riusciti a impostare il lock, un altro processo potrebbe averlo fatto
        if (!$lock_set) {
            return;
        }
        
        // Flag statico per evitare ricorsione all'interno dello stesso processo PHP
        static $processing_ids = array();
        if (isset($processing_ids[$attachment_id])) {
            delete_transient('imgseo_processing_' . $attachment_id);
            return;
        }
        
        // Segna l'ID come in elaborazione
        $processing_ids[$attachment_id] = true;
        // ========== FINE PROTEZIONE RICORSIONE ===========
        
        
        try {
            // Controllo per evitare tentativi eccessivi - limitato a 2 tentativi come richiesto
            if ($attempt_number > 2) {
                delete_post_meta($attachment_id, '_imgseo_pending_generation');
                return;
            }
            
            // Verifica se un altro tentativo ha già avuto successo
            $pending_generation = get_post_meta($attachment_id, '_imgseo_pending_generation', true);
            $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            
            // Se il flag non è più presente o abbiamo già un testo alternativo e non dobbiamo sovrascrivere, il processo è completato
            $overwrite = get_option('imgseo_overwrite', 0);
            if (empty($pending_generation) && !empty($current_alt_text) && !$overwrite) {
                return;
            }
            
            // Verifica che l'allegato esista
            $attachment = get_post($attachment_id);
            if (!$attachment) {
                delete_post_meta($attachment_id, '_imgseo_pending_generation');
                return;
            }
            
            // Verifica che sia un'immagine (controllo multiplo)
            $is_image = wp_attachment_is_image($attachment_id);
            $mime_type = get_post_mime_type($attachment_id);
            $is_image_mime = strpos($mime_type, 'image/') === 0;

            if (!$is_image && !$is_image_mime) {
                delete_post_meta($attachment_id, '_imgseo_pending_generation');
                return;
            }

            // Verifica che il formato sia supportato dall'API
            // Formati supportati: JPEG, PNG, WebP, AVIF, HEIC, BMP, GIF, TIFF
            if (!self::is_supported_image_format($attachment_id)) {
                delete_post_meta($attachment_id, '_imgseo_pending_generation');
                return;
            }
            
            // Verifica crediti ImgSEO - controllo migliorato come negli altri punti
            $credits_exhausted = get_transient('imgseo_insufficient_credits');
            $credits = get_option('imgseo_credits', 0);
            
            // Controllo più rigoroso: crediti < 1 o transient impostato
            if ($credits_exhausted || $credits < 1) {
                
                // Imposta il transient se non è già impostato
                if (!$credits_exhausted) {
                    set_transient('imgseo_insufficient_credits', true, 3600); // 1 ora
                }
                
                delete_post_meta($attachment_id, '_imgseo_pending_generation');
                return;
            }
            
            // Optimized: prioritize using thumbnails for API processing
            // This reduces bandwidth usage and processing time
            
            // Define image sizes to try in priority order
            $image_sizes = array('large', 'medium_large', 'medium', 'thumbnail');

            $valid_url = null;

            // Performance optimization: Trust WordPress-generated thumbnails without HTTP checks
            // If WordPress generated the thumbnail, the file should exist
            foreach ($image_sizes as $size) {
                $image_size = wp_get_attachment_image_src($attachment_id, $size);
                if ($image_size && is_array($image_size) && !empty($image_size[0])) {
                    // Removed HTTP check for better performance
                    $valid_url = $image_size[0];
                    break;
                }
            }

            // Fallback to original if no thumbnails available
            if (!$valid_url) {
                $image_original = wp_get_attachment_url($attachment_id);
                if ($image_original) {
                    // Removed HTTP check for better performance
                    $valid_url = $image_original;
                }
            }
            
            if (!$valid_url) {
                // Ripianifica solo se siamo sotto il limite di tentativi
                if ($attempt_number < 4) {
                    if (!wp_next_scheduled('imgseo_single_generate', array($attachment_id, $attempt_number + 1))) {
                        wp_schedule_single_event(time() + 30, 'imgseo_single_generate', array($attachment_id, $attempt_number + 1));
                    }
                } else {
                    delete_post_meta($attachment_id, '_imgseo_pending_generation');
                }
                return;
            }
            
            // Ottieni il titolo della pagina genitore se disponibile
            $parent_post_id = get_post_field('post_parent', $attachment_id);
            $parent_post_title = $parent_post_id ? get_the_title($parent_post_id) : '';
            
            // Ottieni anche il titolo dell'allegato per contesto aggiuntivo
            $attachment_title = get_the_title($attachment_id);
            
            // Genera il testo alternativo
            $alt_text = $this->generate_alt_text($valid_url, $attachment_id, $parent_post_title);
            
            if (is_wp_error($alt_text)) {
                $error_message = $alt_text->get_error_message();
                
                // Ripianifica solo se è un errore temporaneo e siamo sotto il limite di tentativi
                if (($attempt_number < 4) && 
                    (strpos($error_message, 'elaborare l\'immagine') !== false || 
                     strpos($error_message, 'timeout') !== false || 
                     strpos($error_message, 'temporaneo') !== false)) {
                    
                    // Incremento esponenziale del tempo di attesa tra tentativi
                    $wait_time = 30 * pow(2, $attempt_number - 1);
                    if (!wp_next_scheduled('imgseo_single_generate', array($attachment_id, $attempt_number + 1))) {
                        wp_schedule_single_event(time() + $wait_time, 'imgseo_single_generate', array($attachment_id, $attempt_number + 1));
                    }
                } else {
                    // Dopo troppi tentativi o errore permanente, rimuovi il flag di pending
                    delete_post_meta($attachment_id, '_imgseo_pending_generation');
                }
                return;
            }
            
            // SOSPENDI I HOOK temporaneamente per evitare ricorsione
            $suspended_meta_hook = remove_action('updated_post_meta', array(IMGSEO_Init::init(), 'check_image_alt_on_meta_update'), 15);
            
            // Aggiorna il testo alternativo con strategia a doppio passaggio
            try {
                // Fase 1: Rimuovi eventuale valore esistente
                delete_post_meta($attachment_id, '_wp_attachment_image_alt');
                
                // Aggiungi un piccolo ritardo tra operazioni
                usleep(100000); // 0.1 secondi
                
                // Fase 2: Aggiungi il nuovo valore
                add_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
                
                // Fase 3: Forza l'aggiornamento con update_post_meta
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
                
                // Verifica che l'aggiornamento sia avvenuto con successo
                $updated_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                if ($updated_alt_text !== $alt_text) {
                    // Ultimo tentativo forzato
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text, '');
                }
            } finally {
                // RIPRISTINA I HOOK anche in caso di errori
                if ($suspended_meta_hook) {
                    add_action('updated_post_meta', array(IMGSEO_Init::init(), 'check_image_alt_on_meta_update'), 15, 4);
                }
            }
            
            // Rimuovi il flag che indica che l'immagine è in attesa di generazione
            delete_post_meta($attachment_id, '_imgseo_pending_generation');
            
            // CLEAR CACHE: Invalidate stats cache to ensure accurate counts
            delete_transient('imgseo_images_without_alt_count_v2');
            
            
            // Aggiungi un breve lock di 5 secondi per evitare ulteriori aggiornamenti immediati
            set_transient('imgseo_alt_updated_' . $attachment_id, true, 5);
            
            // Aggiorna gli altri campi in base alle opzioni
            $update_title = get_option('imgseo_update_title', 0);
            $update_caption = get_option('imgseo_update_caption', 0);
            $update_description = get_option('imgseo_update_description', 0);
            
            $attachment_data = ['ID' => $attachment_id];
            $updates_made = false;
            
            if ($update_title) {
                $attachment_data['post_title'] = $alt_text;
                $updates_made = true;
            }
            
            if ($update_caption) {
                $attachment_data['post_excerpt'] = $alt_text;
                $updates_made = true;
            }
            
            if ($update_description) {
                $attachment_data['post_content'] = $alt_text;
                $updates_made = true;
            }
            
            if ($updates_made) {
                // SOSPENDI ANCHE QUI GLI HOOK TEMPORANEAMENTE
                $suspended_hook = remove_action('attachment_updated', array(IMGSEO_Init::init(), 'handle_attachment_update'), 20);
                
                try {
                    $result = wp_update_post($attachment_data);
                    
                    if (is_wp_error($result)) {
                    } else {
                    }
                } finally {
                    // RIPRISTINA GLI HOOK ANCHE IN CASO DI ERRORI
                    if ($suspended_hook) {
                        add_action('attachment_updated', array(IMGSEO_Init::init(), 'handle_attachment_update'), 20);
                    }
                }
            }
        } catch (Exception $e) {
        } finally {
            // PULIZIA FINALE - sempre eseguita
            
            // Marca questo ID come non più in elaborazione
            unset($processing_ids[$attachment_id]);
            
            // Rimuovi il lock
            delete_transient('imgseo_processing_' . $attachment_id);
        }
    }
}
