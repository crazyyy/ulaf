<?php
/**
 * Batch processing class
 *
 * @package ImgSEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ImgSEO_Batch_Processor
 * Manages batch processing of images
 */
class ImgSEO_Batch_Processor extends ImgSEO_Generator_Base {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Aggiungiamo un comando di emergenza per fermare tutti i processi
        add_action('admin_init', array($this, 'check_emergency_stop'));
    }
    
    /**
     * Controlla se è stata richiesta una fermata di emergenza di tutti i processi
     */
    public function check_emergency_stop() {
        // Se non è una richiesta di emergenza, esci
        if (!isset($_GET['imgseo_emergency_stop']) || !current_user_can('manage_options')) {
            return;
        }
        
        // Verifica nonce per sicurezza
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'imgseo_emergency_stop')) {
            return;
        }
        
        // Includi la classe process lock
        require_once IMGSEO_DIRECTORY_PATH . 'includes/renamer/class-imgseo-process-lock.php';
        
        // Imposta il blocco globale
        ImgSEO_Process_Lock::set_global_lock();
        
        // Segna tutti i job come fermati
        global $wpdb;
        $table_name = $wpdb->prefix . 'imgseo_jobs';
        
        // Aggiorna tutti i job pendenti o in elaborazione a 'stopped'
        // Table name is safe as it's constructed with $wpdb->prefix
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query($wpdb->prepare(
            "UPDATE `$table_name` 
            SET status = 'stopped', updated_at = %s 
            WHERE status IN ('pending', 'processing')",
            current_time('mysql')
        ));
        
        // Cancella tutti i cron job pianificati
        wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);
        
        // Reindirizza alla pagina di bulk generazione con un messaggio
        wp_safe_redirect(add_query_arg(
            array('page' => 'imgseo-bulk', 'emergency_stopped' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }
    
    /**
     * Handles the AJAX request to start bulk generation
     */
    public function handle_start_bulk() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        // Check if the API key is present and valid
        $api_key = get_option('imgseo_api_key', '');
        $api_verified = !empty($api_key) && get_option('imgseo_api_verified', false);
        
        if (empty($api_key)) {
            wp_send_json_error([
                'message' => __('API Key missing. To use bulk generation, you must first configure an API Key.', 'imgseo-ai-alt-text-generator'),
                'redirect_url' => admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=api')
            ]);
            return;
        }
        
        if (!$api_verified) {
            wp_send_json_error([
                'message' => __('API Key not verified. To use bulk generation, you need to verify API key first.', 'imgseo-ai-alt-text-generator'),
                'redirect_url' => admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=api')
            ]);
            return;
        }
        
        $overwrite = isset($_POST['overwrite']) && $_POST['overwrite'] == 1;
        $processing_mode = 'async'; // Modalità fast impostata di default
        
        // Get update settings for this job
        $update_title = isset($_POST['update_title']) && $_POST['update_title'] == 1;
        $update_caption = isset($_POST['update_caption']) && $_POST['update_caption'] == 1;
        $update_description = isset($_POST['update_description']) && $_POST['update_description'] == 1;
        
        // Get processing speed settings
        $processing_speed = isset($_POST['processing_speed']) ? sanitize_text_field($_POST['processing_speed']) : 'normal';
        
        // Validate processing speed value
        $valid_speeds = ['slow', 'normal', 'fast', 'ultra', 'insane'];
        if (!in_array($processing_speed, $valid_speeds)) {
            $processing_speed = 'normal'; // Default to normal if invalid value provided
        }
        
        // Options update removed to prevent side effects on global settings
        // The settings are passed directly to the generator via AJAX
        
        // Get all images - Use pagination to avoid memory exhaustion
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 100,  // Process in batches to avoid memory issues
            'paged' => 1,
            'post_status' => 'inherit',
            'fields' => 'ids'  // Only get IDs to reduce memory usage
        );

        $query = new WP_Query($args);
        $all_image_ids = $query->posts;

        // If there are more pages, fetch them
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            for ($page = 2; $page <= $total_pages; $page++) {
                $args['paged'] = $page;
                $paged_query = new WP_Query($args);
                $all_image_ids = array_merge($all_image_ids, $paged_query->posts);
            }
        }

        $total_images = count($all_image_ids);

        if ($total_images === 0) {
            wp_send_json_error(['message' => 'No images found.']);
            return;
        }

        // Create a unique ID for this job
        $job_id = 'job_' . uniqid();

        // PERFORMANCE FIX: Pre-load all metadata in cache BEFORE processing
        // This avoids N+1 queries when checking alt text, title, etc.
        update_meta_cache('post', $all_image_ids);

        // PERFORMANCE FIX: Load all post objects in a single query instead of N individual queries
        $images = get_posts(array(
            'post_type' => 'attachment',
            'post__in' => $all_image_ids,
            'posts_per_page' => -1,
            'post_status' => 'inherit',
            'orderby' => 'post__in'
        ));

        $images_data = [];
        $skipped_unsupported = 0;
        foreach ($images as $image) {
            // Check that it's a valid image
            if (!wp_attachment_is_image($image->ID)) {
                continue;
            }

            // Check that image format is supported by the API
            // Supported formats: JPEG, PNG, WebP, AVIF, HEIC, BMP, GIF, TIFF
            // Unsupported formats (like SVG) are skipped
            if (!self::is_supported_image_format($image->ID)) {
                $skipped_unsupported++;
                continue;
            }

            // Enhanced overwrite logic: check ALL selected fields, not just alt text
            if (!$overwrite) {
                $should_skip = true;

                // Check alt text (always checked)
                $current_alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                if (empty($current_alt_text)) {
                    $should_skip = false;
                }

                // Check title if enabled
                if ($should_skip && $update_title) {
                    $current_title = get_the_title($image->ID);
                    // FIX: If title exists (not empty) we must skip ONLY IF overwrite is disabled
                    // Since overwrite=false here, we skip if title exists.
                    // If title is empty, we must process (skip=false)
                    if (empty($current_title)) {
                        $should_skip = false;
                    }
                }

                // Check caption if enabled
                if ($should_skip && $update_caption) {
                    $current_caption = $image->post_excerpt;
                    if (empty($current_caption)) {
                        $should_skip = false;
                    }
                }

                // Check description if enabled
                if ($should_skip && $update_description) {
                    $current_description = $image->post_content;
                    if (empty($current_description)) {
                        $should_skip = false;
                    }
                }

                // Skip only if ALL selected fields are already populated
                if ($should_skip) {
                    continue;
                }
            }
            
            // Add image to list
            $images_data[] = [
                'id' => $image->ID,
                'url' => wp_get_attachment_url($image->ID)
            ];
        }
        
        $images_to_process = count($images_data);

        if ($images_to_process === 0) {
            wp_send_json_error(['message' => 'No images to process. All images already have alternative text.']);
            return;
        }

        // Calculate credit cost per image based on selected fields
        // Base cost: 1.0 credit for alt text
        // Additional: +0.5 credits for each metadata field (title, caption, description)
        $cost_per_image = 1.0;
        if ($update_title) {
            $cost_per_image += 0.5;
        }
        if ($update_caption) {
            $cost_per_image += 0.5;
        }
        if ($update_description) {
            $cost_per_image += 0.5;
        }

        // NUOVO FLUSSO: Verifica preliminare dei crediti usando l'API
        // Solo se non abbiamo già un controllo di fallback
        $api = ImgSEO_API::get_instance();

        // Verifica preliminare "soft" senza bloccare tutto se l'API fallisce
        // In caso di errore API, usiamo i crediti locali come fallback
        $credit_verification = $api->verify_credits_only();

        $credits = 0;
        $warning_message = '';
        $using_local_credits = false;

        if (is_wp_error($credit_verification)) {
            // Log dell'errore per debug ma NON bloccare il processo
            error_log('[ImgSEO Bulk] Credit check API failed: ' . $credit_verification->get_error_message() . '. Using local credits.');

            // Fallback alla verifica locale
            $credits = get_option('imgseo_credits', 0);
            $using_local_credits = true;

            if ($credits <= 0) {
                // Se anche localmente sono 0, allora blocca
                wp_send_json_error([
                    'message' => 'Insufficient ImgSEO credits (Local check). Please purchase more credits to continue.',
                    'phase' => 'preliminary_check_local_fallback'
                ]);
                return;
            }
            $warning_message = 'Note: Could not verify credits with server (network error). Using local credit count.';
        } else {
            // API ha risposto correttamente
            $credits = $credit_verification['credits_remaining'];
            if (!$credit_verification['can_process']) {
                wp_send_json_error([
                    'message' => 'Insufficient ImgSEO credits. Please purchase more credits to continue.',
                    'credits_remaining' => $credits,
                    'phase' => 'preliminary_check_api'
                ]);
                return;
            }
            // Aggiorna i crediti locali con quelli verificati dall'API
            update_option('imgseo_credits', $credits);
        }

        // Calculate how many images can be processed with available credits
        $max_processable_images = floor($credits / $cost_per_image);

        // CRITICAL: Block immediately if zero credits or can't process even one image
        if ($max_processable_images <= 0) {
            wp_send_json_error([
                'message' => sprintf(
                    'Insufficient credits to process any images. You have %.1f credits but need %.1f credits per image (with current settings: alt text%s%s%s). Please purchase more credits to continue.',
                    $credits,
                    $cost_per_image,
                    $update_title ? ' + title' : '',
                    $update_caption ? ' + caption' : '',
                    $update_description ? ' + description' : ''
                ),
                'credits_remaining' => $credits,
                'cost_per_image' => $cost_per_image,
                'phase' => 'insufficient_credits_for_single_image'
            ]);
            return;
        }

        // Limit queue to only processable images
        $original_count = $images_to_process;
        if ($max_processable_images < $images_to_process) {
            // Limit the array to only what we can afford
            $images_data = array_slice($images_data, 0, $max_processable_images);
            $images_to_process = count($images_data);

            // Double-check: If still zero after limiting, block
            if ($images_to_process <= 0) {
                wp_send_json_error([
                    'message' => sprintf(
                        'No images can be processed. You have %.1f credits but need %.1f credits per image. Please purchase more credits to continue.',
                        $credits,
                        $cost_per_image
                    ),
                    'credits_remaining' => $credits,
                    'cost_per_image' => $cost_per_image,
                    'phase' => 'zero_images_after_limiting'
                ]);
                return;
            }

            // Create clear warning message
            $additional_warning = sprintf(
                'IMPORTANT: You have %.1f credits but need %.1f credits per image (%.1f total needed for all %d images). Queue limited to %d images that can be processed with available credits. Remaining %d images will NOT be processed.',
                $credits,
                $cost_per_image,
                $original_count * $cost_per_image,
                $original_count,
                $images_to_process,
                $original_count - $images_to_process
            );
            $warning_message = empty($warning_message) ? $additional_warning : $warning_message . ' ' . $additional_warning;
        } elseif ($credits < ($images_to_process * $cost_per_image * 1.1)) {
            // Warning if credits are just barely enough (within 10% margin)
            $additional_warning = sprintf(
                'Note: You have %.1f credits for %d images (%.1f credits per image = %.1f total). Credits are sufficient but with minimal margin.',
                $credits,
                $images_to_process,
                $cost_per_image,
                $images_to_process * $cost_per_image
            );
            $warning_message = empty($warning_message) ? $additional_warning : $warning_message . ' ' . $additional_warning;
        }

        // Avviso se ci sono immagini con formati non supportati (es. SVG)
        if ($skipped_unsupported > 0) {
            $unsupported_warning = sprintf(
                'Note: %d image(s) with unsupported formats (e.g., SVG) were skipped. Supported formats: %s.',
                $skipped_unsupported,
                self::get_supported_formats_string()
            );
            $warning_message = empty($warning_message) ? $unsupported_warning : $warning_message . ' ' . $unsupported_warning;
        }
        
        
            // Check if the jobs table exists
            global $wpdb;
            $table_name = $wpdb->prefix . 'imgseo_jobs';
            
            // Verifica se la tabella esiste
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )) === $table_name;
            
            if (!$table_exists) {
                // Se la tabella non esiste, crea la tabella usando query diretta
                // invece di dbDelta per maggiore compatibilità
                try {
                    // Attiva il logging degli errori per catturare eventuali problemi
                    $wpdb->show_errors();
                    
                    // Crea una versione semplificata della tabella per massima compatibilità
                    $charset_collate = $wpdb->get_charset_collate();
                    
                    // Prima prova a creare solo una tabella di base molto semplice
                    $create_query = "CREATE TABLE IF NOT EXISTS $table_name (
                        id INT NOT NULL AUTO_INCREMENT,
                        job_id VARCHAR(50) NOT NULL,
                        total_images INT NOT NULL,
                        processed_images INT NOT NULL DEFAULT 0,
                        images_data LONGTEXT NOT NULL,
                        overwrite TINYINT(1) NOT NULL DEFAULT 0,
                        status VARCHAR(20) NOT NULL DEFAULT 'pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id)
                    ) $charset_collate;";
                    
                    // Esegui la query per creare la tabella
                    // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $result = $wpdb->query($create_query);
                    
                    // Verifica se la query ha generato un errore
                    if ($result === false) {
                        $error_message = 'Errore nella creazione della tabella. ' . 
                            'Tabella: ' . $table_name . '. ' . 
                            'Errore SQL: ' . $wpdb->last_error;
                        
                        
                        // Fallback: prova con una versione ancora più semplice, senza caratteristiche avanzate
                        // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
                        $simple_query = "CREATE TABLE IF NOT EXISTS `$table_name` (
                            id INT NOT NULL AUTO_INCREMENT,
                            job_id VARCHAR(50) NOT NULL,
                            total_images INT NOT NULL,
                            processed_images INT NOT NULL DEFAULT 0,
                            images_data TEXT NOT NULL,
                            status VARCHAR(20) NOT NULL,
                            PRIMARY KEY (id)
                        )";
                        
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                        $result = $wpdb->query($simple_query);
                        
                        if ($result === false) {
                            // Non possiamo creare la tabella, segnala l'errore dettagliato
                            wp_send_json_error([
                                'message' => 'Errore critico nella creazione della tabella nel database. ' . 
                                            'Tabella: ' . $table_name . '. ' . 
                                            'Errore SQL: ' . $wpdb->last_error . '. ' . 
                                            'Query: ' . $simple_query
                            ]);
                            return;
                        }
                    }
                    
                    // Ora aggiungi l'indice in una query separata
                    // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
                    $index_query = "ALTER TABLE `$table_name` ADD INDEX (job_id)";
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $wpdb->query($index_query);  // Ignora eventuali errori qui
                    
                    // Controlla che la tabella esista ora
                    $table_exists_after_creation = $wpdb->get_var($wpdb->prepare(
                        "SHOW TABLES LIKE %s",
                        $table_name
                    )) === $table_name;
                    
                    if (!$table_exists_after_creation) {
                        wp_send_json_error([
                            'message' => 'La tabella non risulta creata nonostante il tentativo sia riuscito. ' . 
                                        'Tabella: ' . $table_name . '. ' . 
                                        'Verifica i permessi del database e le impostazioni.'
                        ]);
                        return;
                    }
                } catch (Exception $e) {
                    wp_send_json_error([
                        'message' => 'Eccezione durante la creazione della tabella: ' . $e->getMessage()
                    ]);
                    return;
                }
            }
        
        // Salva il job nel database con tutte le impostazioni bulk-specific
        $result = $wpdb->insert(
            $table_name,
            [
                'job_id' => $job_id,
                'total_images' => $images_to_process,
                'processed_images' => 0,
                'images_data' => json_encode($images_data),
                'overwrite' => $overwrite ? 1 : 0,
                'update_title' => $update_title ? 1 : 0,
                'update_caption' => $update_caption ? 1 : 0,
                'update_description' => $update_description ? 1 : 0,
                'processing_speed' => $processing_speed,
                'status' => 'processing' // Sempre processing con modalità fast
            ]
        );
        
        if ($result === false) {
            wp_send_json_error(['message' => 'Error saving job to database: ' . $wpdb->last_error]);
            return;
        }
        
        // Verifica anche se la tabella dei log esiste
        $log_table_name = $wpdb->prefix . 'imgseo_logs';
        $log_table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $log_table_name
        )) === $log_table_name;
        
        if (!$log_table_exists) {
            // Se la tabella dei log non esiste, creala con lo stesso approccio sicuro
            try {
                // Attiva il logging degli errori per catturare eventuali problemi
                $wpdb->show_errors();
                
                // Crea una versione semplificata della tabella per massima compatibilità
                $charset_collate = $wpdb->get_charset_collate();
                
                // Prima prova a creare una tabella di base molto semplice
                $create_query = "CREATE TABLE IF NOT EXISTS $log_table_name (
                    id INT NOT NULL AUTO_INCREMENT,
                    job_id VARCHAR(50) NOT NULL,
                    image_id BIGINT(20) NOT NULL,
                    filename TEXT NOT NULL,
                    alt_text TEXT,
                    status VARCHAR(20) NOT NULL DEFAULT 'success',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) $charset_collate;";
                
                // Esegui la query per creare la tabella
                // Table name is safe as it's constructed with $wpdb->prefix
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $result = $wpdb->query($create_query);
                
                // Verifica se la query ha generato un errore
                if ($result === false) {
                    $error_message = 'Errore nella creazione della tabella dei log. ' . 
                        'Tabella: ' . $log_table_name . '. ' . 
                        'Errore SQL: ' . $wpdb->last_error;
                    
                    // Fallback: prova con una versione ancora più semplice
                    // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
                    $simple_query = "CREATE TABLE IF NOT EXISTS `$log_table_name` (
                        id INT NOT NULL AUTO_INCREMENT,
                        job_id VARCHAR(50) NOT NULL,
                        image_id BIGINT(20) NOT NULL,
                        filename TEXT NOT NULL,
                        alt_text TEXT,
                        status VARCHAR(20) NOT NULL,
                        PRIMARY KEY (id)
                    )";
                    
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $result = $wpdb->query($simple_query);
                    
                    if ($result === false) {
                        // Non possiamo creare la tabella, segnala l'errore dettagliato
                        wp_send_json_error([
                            'message' => 'Errore critico nella creazione della tabella dei log. ' . 
                                        'Tabella: ' . $log_table_name . '. ' . 
                                        'Errore SQL: ' . $wpdb->last_error . '. ' . 
                                        'Query: ' . $simple_query
                        ]);
                        return;
                    }
                }
                
                // Ora aggiungi gli indici in query separate
                $wpdb->query("ALTER TABLE $log_table_name ADD INDEX (job_id);");
                $wpdb->query("ALTER TABLE $log_table_name ADD INDEX (image_id);");
                
                // Controlla che la tabella esista ora
                $log_table_exists_after_creation = $wpdb->get_var($wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $log_table_name
                )) === $log_table_name;
                
                if (!$log_table_exists_after_creation) {
                    wp_send_json_error([
                        'message' => 'La tabella dei log non risulta creata nonostante il tentativo sia riuscito. ' . 
                                    'Tabella: ' . $log_table_name . '. ' . 
                                    'Verifica i permessi del database e le impostazioni.'
                    ]);
                    return;
                }
            } catch (Exception $e) {
                wp_send_json_error([
                    'message' => 'Eccezione durante la creazione della tabella dei log: ' . $e->getMessage()
                ]);
                return;
            }
        }

        // La modalità background è stata rimossa, ora c'è solo la modalità fast (async)
        
        // Includi avviso sui crediti nella risposta come parte del messaggio
        // senza utilizzare flag che potrebbero generare popup
        $credit_limited = ($original_count > $images_to_process);

        if ($credit_limited) {
            $message = sprintf(
                "Processing %d of %d images (limited due to insufficient credits - %.1f credits available, %.1f needed per image)",
                $images_to_process,
                $original_count,
                $credits,
                $cost_per_image
            );
        } else {
            $message = "Processing started for $images_to_process images";
        }

        if (!empty($warning_message)) {
            $message .= ". " . $warning_message;
        }

        wp_send_json_success([
            'job_id' => $job_id,
            'total_images' => $images_to_process,
            'original_total_images' => $original_count,
            'credit_limited' => $credit_limited,
            'image_ids' => array_column($images_data, 'id'),
            'processing_mode' => $processing_mode,
            'message' => $message
        ]);
    }
    
    /**
     * Handles the AJAX request to check status of a job
     */
    public function handle_check_job_status() {
        check_ajax_referer('imgseo_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $job_id = isset($_POST['job_id']) ? sanitize_text_field($_POST['job_id']) : '';

        if (empty($job_id)) {
            wp_send_json_error(['message' => 'ID job mancante']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'imgseo_jobs';

        // Table name is safe as it's constructed with $wpdb->prefix
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $job = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE job_id = %s",
            $job_id
        ));

        if (!$job) {
            wp_send_json_error(['message' => 'Job non trovato']);
        }

        // Calcolo della percentuale di avanzamento
        // Assicuriamoci che i job completati mostrino sempre 100%
        $progress = 0;
        if ($job->total_images > 0) {
            if ($job->status === 'completed') {
                $progress = 100;
            } else {
                $progress = round(($job->processed_images / $job->total_images) * 100);
            }
        }

        // Ottieni gli ultimi log per questo job (file-based)
        $last_line = isset($_POST['last_log_id']) ? intval($_POST['last_log_id']) : 0;

        $file_logger = ImgSEO_File_Logger::get_instance();
        $log_result = $file_logger->get_logs($job_id, $last_line, 50);

        // Formatta i log per il client
        $formatted_logs = [];
        foreach ($log_result['logs'] as $log) {
            $log_entry = [
                'id' => $log['line_number'],
                'image_id' => $log['image_id'],
                'filename' => $log['filename'],
                'alt_text' => $log['alt_text'],
                'status' => $log['status'],
                'time' => $log['timestamp'],
                'message' => isset($log['message']) ? $log['message'] : ''
            ];

            // Add metadata fields if present
            if (isset($log['title']) && !empty($log['title'])) {
                $log_entry['title'] = $log['title'];
            }
            if (isset($log['caption']) && !empty($log['caption'])) {
                $log_entry['caption'] = $log['caption'];
            }
            if (isset($log['description']) && !empty($log['description'])) {
                $log_entry['description'] = $log['description'];
            }

            $formatted_logs[] = $log_entry;
        }

        wp_send_json_success([
            'job_id' => $job->job_id,
            'status' => $job->status,
            'total_images' => $job->total_images,
            'processed_images' => $job->processed_images,
            'progress' => $progress,
            'message' => "Processing: $job->processed_images of $job->total_images completed",
            'is_completed' => ($job->status === 'completed' || $job->status === 'stopped'),
            'logs' => $formatted_logs,
            'max_log_id' => $log_result['last_line'],
            'last_updated' => $job->updated_at
        ]);
    }
    
    /**
     * Handles the AJAX request to stop a job
     * Supports also the completion of async jobs (fast processing)
     */
    public function handle_stop_job() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'imgseo-ai-alt-text-generator')]);
        }
        
        $job_id = isset($_POST['job_id']) ? sanitize_text_field($_POST['job_id']) : '';
        
        if (empty($job_id)) {
            wp_send_json_error(['message' => 'ID job mancante']);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'imgseo_jobs';
        
        // Verifica se il job esiste
        // Table name is safe as it's constructed with $wpdb->prefix
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $job = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE job_id = %s",
            $job_id
        ));
        
        if (!$job) {
            wp_send_json_error(['message' => 'Job non trovato']);
        }
        
        // Verifica se il job è già completato o interrotto
        if ($job->status === 'completed' || $job->status === 'stopped') {
            // Anche se già interrotto, ottieni comunque il conteggio corretto dai log file
            $processed_count = $job->processed_images;

            // Ottieni il conteggio dai log file
            $file_logger = ImgSEO_File_Logger::get_instance();
            $log_stats = $file_logger->get_log_stats($job_id);

            if ($log_stats['total'] > 0) {
                $processed_count = max($log_stats['total'], $processed_count);

                // Aggiorna il conteggio nel database anche se è già interrotto
                if ($processed_count > $job->processed_images) {
                    $wpdb->update(
                        $table_name,
                        ['processed_images' => $processed_count],
                        ['job_id' => $job_id]
                    );
                }
            }

            wp_send_json_success([
                'message' => __('Job has already been stopped or completed', 'imgseo-ai-alt-text-generator'),
                'job_id' => $job_id,
                'status' => $job->status,
                'processed_images' => $processed_count
            ]);
            return;
        }

        // Determina se è una interruzione o un completamento normale
        // Il flag completion_status è usato dal processo asincrono per indicare completamento invece di interruzione
        $completion_status = isset($_POST['completion_status']) && $_POST['completion_status'] === 'completed' ? 'completed' : 'stopped';

        // Ottieni il conteggio delle immagini processate
        // Prima prova a prendere il valore passato esplicitamente dalla richiesta
        $processed_count = isset($_POST['processed_count']) ? intval($_POST['processed_count']) : $job->processed_images;

        // SEMPRE verifica i log file per ottenere il conteggio più accurato
        $file_logger = ImgSEO_File_Logger::get_instance();
        $log_stats = $file_logger->get_log_stats($job_id);

        if ($log_stats['total'] > 0) {
            // Prendi sempre il valore più alto tra il conteggio dai log
            // e quello eventualmente passato dal frontend
            $processed_count = max($log_stats['total'], $processed_count);
        }
        
        // Se è un job di terminazione normale, setta una transient per comunicare al cron job di fermarsi immediatamente
        if ($completion_status === 'stopped') {
            set_transient('imgseo_stop_job_' . $job_id, 'yes', 60 * 5); // 5 minuti di durata
        }
        
        // Aggiorna lo stato del job e il conteggio delle immagini
        $result = $wpdb->update(
            $table_name,
            [
                'status' => $completion_status,
                'processed_images' => $processed_count,
                'updated_at' => current_time('mysql')
            ],
            ['job_id' => $job_id]
        );
        
        // Forza una nuova query per verificare che i dati siano stati effettivamente salvati
        // Table name is safe as it's constructed with $wpdb->prefix
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $updated_job = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE job_id = %s",
            $job_id
        ));
        
        // Log per debug
        
        if ($result === false) {
            wp_send_json_error(['message' => 'Error updating job status']);
        }
        
        // Assicurati che il conteggio finale sia quello realmente salvato nel database
        $final_count = $updated_job ? $updated_job->processed_images : $processed_count;
        
        wp_send_json_success([
            'message' => $completion_status === 'completed' ? 'Job completato con successo' : 'Job interrotto con successo',
            'job_id' => $job_id,
            'processed_images' => $final_count,
            'total_images' => $job->total_images,
            'progress' => $job->total_images > 0 ? round(($final_count / $job->total_images) * 100) : 0,
            'status' => $completion_status
        ]);
    }
    
    /**
     * Gestisce la richiesta AJAX per eliminare un job
     */
    public function handle_delete_job() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'imgseo-ai-alt-text-generator')]);
        }
        
        $job_id = isset($_POST['job_id']) ? sanitize_text_field($_POST['job_id']) : '';
        
        if (empty($job_id)) {
            wp_send_json_error(['message' => 'ID job mancante']);
        }
        
        // Temporaneamente disabilita controlli API per evitare consumo crediti
        // Salva lo stato attuale per ripristinarlo dopo
        $did_http_api_filter = false;
        if (!has_filter('pre_http_request', array($this, 'block_external_api_requests'))) {
            add_filter('pre_http_request', array($this, 'block_external_api_requests'), 10, 3);
            $did_http_api_filter = true;
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'imgseo_jobs';

            // Blocca immediatamente questo job per prevenire elaborazioni addizionali
            require_once IMGSEO_DIRECTORY_PATH . 'includes/renamer/class-imgseo-process-lock.php';
            ImgSEO_Process_Lock::set_job_lock($job_id);
            
            // Verifica se il job esiste
            // Table name is safe as it's constructed with $wpdb->prefix
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $job = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM `$table_name` WHERE job_id = %s",
                $job_id
            ));
            
            if (!$job) {
                // Se il job non esiste, potrebbe essere già stato eliminato
                wp_send_json_success([
                    'message' => __('Job already deleted', 'imgseo-ai-alt-text-generator'),
                    'job_id' => $job_id
                ]);
                return;
            }
            
            // Prima interrompi il job se è in esecuzione
            if ($job->status === 'pending' || $job->status === 'processing') {
                $wpdb->update(
                    $table_name,
                    ['status' => 'stopped', 'updated_at' => current_time('mysql')],
                    ['job_id' => $job_id]
                );
            }
            
            // Delete log file associated with the job
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->delete_job_log($job_id);

            // Poi elimina il job
            $result = $wpdb->delete(
                $table_name,
                ['job_id' => $job_id]
            );
            
            if ($result === false) {
                wp_send_json_error(['message' => 'Error deleting job']);
            }
            
            wp_send_json_success([
                'message' => 'Job eliminato con successo',
                'job_id' => $job_id
            ]);
        } finally {
            // Ripristina il comportamento normale delle API
            if ($did_http_api_filter) {
                remove_filter('pre_http_request', array($this, 'block_external_api_requests'), 10);
            }
        }
    }
    
    /**
     * Blocca le richieste API esterne durante alcune operazioni
     */
    public function block_external_api_requests($preempt, $args, $url) {
        return new WP_Error('api_disabled', 'Le API esterne sono temporaneamente disabilitate durante le operazioni amministrative.');
    }
    
    /**
     * Gestisce la richiesta AJAX per eliminare tutti i job
     */
    public function handle_delete_all_jobs() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Temporaneamente disabilita controlli API per evitare consumo crediti
        // Salva lo stato attuale per ripristinarlo dopo
        $did_http_api_filter = false;
        if (!has_filter('pre_http_request', array($this, 'block_external_api_requests'))) {
            add_filter('pre_http_request', array($this, 'block_external_api_requests'), 10, 3);
            $did_http_api_filter = true;
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'imgseo_jobs';

            // Prima interrompi tutti i job in esecuzione
            // Table name is safe as it's constructed with $wpdb->prefix
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query($wpdb->prepare(
                "UPDATE `$table_name` 
                SET status = 'stopped', updated_at = %s 
                WHERE status IN ('pending', 'processing')",
                current_time('mysql')
            ));
            
            // Delete all log files (cleanup with 0 days = delete all)
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->cleanup_old_logs(0);

            // Delete all jobs
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
            if ($table_exists) {
                // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->query("TRUNCATE TABLE `$table_name`");
            }
            
            // Rimuovi qualsiasi evento cron
            wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);
            
            wp_send_json_success([
                'message' => 'Tutti i job sono stati eliminati con successo'
            ]);
        } finally {
            // Ripristina il comportamento normale delle API
            if ($did_http_api_filter) {
                remove_filter('pre_http_request', array($this, 'block_external_api_requests'), 10);
            }
        }
    }

    /**
     * Handles the AJAX request to view job logs
     */
    public function handle_view_job_log() {
        check_ajax_referer('imgseo_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'imgseo-ai-alt-text-generator')]);
        }
        
        $job_id = isset($_POST['job_id']) ? sanitize_text_field($_POST['job_id']) : '';
        
        if (empty($job_id)) {
            wp_send_json_error(['message' => 'ID job mancante']);
        }

        $file_logger = ImgSEO_File_Logger::get_instance();
        $log_file_path = $file_logger->get_log_file_path($job_id);

        if (!file_exists($log_file_path)) {
             wp_send_json_error(['message' => __('Log file not found', 'imgseo-ai-alt-text-generator')]);
        }

        // Read file
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $content = file_get_contents($log_file_path);
        if ($content === false) {
             wp_send_json_error(['message' => __('Error reading log file', 'imgseo-ai-alt-text-generator')]);
        }

        // Parse JSON lines into a readable format
        $lines = explode("\n", $content);
        $formatted_content = "";
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $entry = json_decode($line, true);
            if ($entry) {
                // Get basic info
                $time = isset($entry['timestamp']) ? $entry['timestamp'] : '';
                $status = isset($entry['status']) ? strtoupper($entry['status']) : 'INFO';
                $filename = isset($entry['filename']) ? $entry['filename'] : '';
                $alt = isset($entry['alt_text']) ? $entry['alt_text'] : '';
                $msg = isset($entry['message']) ? $entry['message'] : '';
                $image_id = isset($entry['image_id']) ? $entry['image_id'] : '';
                
                // Get URL from log entry or fallback to WordPress
                $url = '';
                if (isset($entry['image_url']) && !empty($entry['image_url'])) {
                    $url = $entry['image_url'];
                } elseif (!empty($image_id)) {
                    // Fallback: try to get URL from WordPress if not in log
                    $url = wp_get_attachment_url($image_id);
                }
                
                // FIX: Handle object/array in filename to prevent "Array" or "Object" string conversion
                if (is_array($filename) || is_object($filename)) {
                    $filename = 'Unknown Filename (Data Error)';
                }

                // Format: [TIMESTAMP] [STATUS] Filename - URL: https://... - Alt: ...
                $formatted_content .= "[$time] [$status] $filename\n";
                if ($url) {
                    $formatted_content .= "  URL: $url\n";
                }
                if ($alt) {
                    $formatted_content .= "  Alt: $alt\n";
                }

                // Add metadata fields if present
                if (isset($entry['title']) && !empty($entry['title'])) {
                    $formatted_content .= "  Title: " . $entry['title'] . "\n";
                }
                if (isset($entry['caption']) && !empty($entry['caption'])) {
                    $formatted_content .= "  Caption: " . $entry['caption'] . "\n";
                }
                if (isset($entry['description']) && !empty($entry['description'])) {
                    $formatted_content .= "  Description: " . $entry['description'] . "\n";
                }

                if ($msg) {
                    $formatted_content .= "  Message: $msg\n";
                }
                $formatted_content .= "\n";
            } else {
                // If not JSON, append raw line
                $formatted_content .= $line . "\n";
            }
        }

        wp_send_json_success(['log_content' => $formatted_content, 'job_id' => $job_id]);
    }
}
