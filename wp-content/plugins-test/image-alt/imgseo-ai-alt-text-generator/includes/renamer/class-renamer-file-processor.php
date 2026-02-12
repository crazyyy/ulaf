<?php
/**
 * Class Renamer_File_Processor
 * Handles the actual file renaming operations
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_File_Processor {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Managers interni
     */
    private $logs_manager;
    private $pattern_manager;
    private $integration_manager;
    private $settings_manager;
    
    /**
     * Initialize the class and set its properties.
     */
    private function __construct() {
        // Dipendenza da logs manager per le operazioni di logging
        require_once plugin_dir_path(__FILE__) . 'class-renamer-logs-manager.php';
        $this->logs_manager = Renamer_Logs_Manager::get_instance();

        // Le altre dipendenze potrebbero non essere ancora disponibili qui
        add_action('init', array($this, 'late_initialize'), 20);

        // Hook per auto-rename on upload - Strategia a due fasi:
        // 1. Filter: marca attachment per rename ma NON rinomina (evita conflitti timing)
        // 2. Action: esegue rename DOPO che WordPress ha salvato i metadata
        add_filter('wp_generate_attachment_metadata', array($this, 'mark_for_auto_rename'), 999, 2);
        add_action('updated_post_meta', array($this, 'execute_auto_rename_after_save'), 10, 4);

        // Hook per delayed auto-rename (fallback per casi edge e compatibilità con plugin)
        add_action('imgseo_delayed_auto_rename', array($this, 'handle_delayed_auto_rename'), 10);
    }
    
    /**
     * Rename WebP, AVIF and other format variants of the main image and its thumbnails
     * 
     * @param string $file_dir Directory containing the files
     * @param string $old_filename_base Old filename without extension
     * @param string $new_filename_base New filename without extension
     * @param WP_Filesystem_Base $wp_filesystem WordPress filesystem instance
     * @param array &$renamed_thumbnails Reference to array tracking renamed files
     */
    private function rename_format_variants($file_dir, $old_filename_base, $new_filename_base, $wp_filesystem, &$renamed_thumbnails) {
        // Modern formats to check for
        $modern_formats = array('webp', 'avif', 'jxl', 'heic');
        
        // Get all files in the directory
        $dir_handle = opendir($file_dir);
        if (!$dir_handle) {
            return;
        }
        
        $files_to_rename = array();
        
        while (($file_in_dir = readdir($dir_handle)) !== false) {
            // Skip . and .. directories
            if ($file_in_dir == '.' || $file_in_dir == '..') {
                continue;
            }
            
            $file_path_info = pathinfo($file_in_dir);
            $file_base = $file_path_info['filename'];
            $file_ext = isset($file_path_info['extension']) ? strtolower($file_path_info['extension']) : '';
            
            // Check if this file belongs to our image (main file or thumbnail)
            $is_main_variant = ($file_base === $old_filename_base);
            $is_thumbnail_variant = preg_match('/^' . preg_quote($old_filename_base, '/') . '-\d+x\d+$/', $file_base);
            
            if (($is_main_variant || $is_thumbnail_variant) && in_array($file_ext, $modern_formats)) {
                $old_file_path = $file_dir . '/' . $file_in_dir;
                
                // Create new filename
                if ($is_main_variant) {
                    $new_filename = $new_filename_base . '.' . $file_ext;
                } else {
                    // For thumbnails, replace the base part
                    $new_filename = str_replace($old_filename_base, $new_filename_base, $file_in_dir);
                }
                
                $new_file_path = $file_dir . '/' . $new_filename;
                
                $files_to_rename[] = array(
                    'old_path' => $old_file_path,
                    'new_path' => $new_file_path,
                    'filename' => $file_in_dir,
                    'new_filename' => $new_filename,
                    'format' => $file_ext,
                    'type' => $is_main_variant ? 'main' : 'thumbnail'
                );
            }
        }
        
        closedir($dir_handle);
        
        // Rename all identified format variants
        foreach ($files_to_rename as $file_info) {
            if (file_exists($file_info['old_path'])) {
                if ($wp_filesystem->move($file_info['old_path'], $file_info['new_path'])) {
                    $renamed_thumbnails[$file_info['format'] . '_' . $file_info['type'] . '_' . count($renamed_thumbnails)] = $file_info['new_path'];
                    
                    // Log successful rename of format variant
                } else {
                    // Log failed rename
                }
            }
        }
        
        // Log summary
        if (!empty($files_to_rename)) {
            $total_variants = count($files_to_rename);
            $successful_renames = count(array_filter($files_to_rename, function($file) {
                return file_exists($file['new_path']);
            }));
            
        }
    }
    
    /**
     * Inizializzazione tardiva per componenti che potrebbero non essere ancora disponibili
     */
    public function late_initialize() {
        if (class_exists('Renamer_Pattern_Manager')) {
            $this->pattern_manager = Renamer_Pattern_Manager::get_instance();
        }
        
        if (class_exists('Renamer_Settings_Manager')) {
            $this->settings_manager = Renamer_Settings_Manager::get_instance();
        }
        
        // Non caricare l'integration manager immediatamente per risparmiare memoria
        // Verrà caricato solo quando effettivamente necessario
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
     * Rename an image file
     * 
     * @param int $attachment_id The attachment ID
     * @param string $new_filename The new filename (without extension)
     * @param array $options Opzioni aggiuntive per la rinomina
     * @return array|WP_Error Result of the rename operation
     */
    public function rename_image($attachment_id, $new_filename, $options = array()) {
        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new WP_Error('invalid_attachment', __('Invalid attachment.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Get attachment file path
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return new WP_Error('file_not_found', __('Attachment file not found.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Get file info
        $path_parts = pathinfo($file);
        $old_filename = basename($file);
        $old_filename_base = $path_parts['filename'];
        $extension = isset($path_parts['extension']) ? $path_parts['extension'] : '';
        $dir_path = $path_parts['dirname'];

        if (empty($extension)) {
            return new WP_Error('no_extension', __('Could not determine file extension.', 'imgseo-ai-alt-text-generator'));
        }

        // IMPORTANTE: Gestione file -scaled di WordPress 5.3+
        // Se il file principale è -scaled, dobbiamo:
        // 1. Usare il nome BASE senza -scaled per le thumbnail
        // 2. Mantenere -scaled nel nuovo nome del file principale
        // 3. Rinominare l'originale (senza -scaled) se esiste
        $is_scaled_image = (substr($old_filename_base, -7) === '-scaled');
        $old_filename_base_without_scaled = $is_scaled_image ? substr($old_filename_base, 0, -7) : $old_filename_base;
        
        // Applica pattern se pattern manager è disponibile e l'opzione è attivata
        if (!empty($options['use_patterns']) && !empty($options['pattern']) && $this->pattern_manager) {
            $context = array(
                'original_filename' => $old_filename_base,
                'attachment_id' => $attachment_id,
            );
            
            // Genera il nuovo nome file usando il pattern fornito
            $pattern_result = $this->pattern_manager->apply_patterns($options['pattern'], $attachment_id, $context);
            
            // Usa il risultato del pattern solo se non è vuoto
            if (!empty($pattern_result)) {
                $new_filename = $pattern_result;
            }
        }
        
        // Applica sanitizzazione opzionale al nuovo nome file
        if (!empty($options['sanitize'])) {
            $sanitize_options = array();
            
            // Imposta opzioni di sanitizzazione dalle opzioni o dalle impostazioni
            $sanitize_options['remove_accents'] = isset($options['remove_accents']) ? 
                (bool)$options['remove_accents'] : 
                ($this->settings_manager ? $this->settings_manager->is_enabled('remove_accents') : true);
                
            $sanitize_options['lowercase'] = isset($options['lowercase']) ? 
                (bool)$options['lowercase'] : 
                ($this->settings_manager ? $this->settings_manager->is_enabled('lowercase') : true);
            
            // Sanitizza usando il pattern manager o una sanitizzazione base
            if ($this->pattern_manager) {
                $new_filename = $this->pattern_manager->sanitize_filename($new_filename, $sanitize_options);
            } else {
                // Sanitizzazione base fallback
                if ($sanitize_options['remove_accents']) {
                    $new_filename = remove_accents($new_filename);
                }
                
                if ($sanitize_options['lowercase']) {
                    $new_filename = strtolower($new_filename);
                }
                
                // Sostituisci caratteri non alfanumerici con trattini
                $new_filename = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $new_filename);
                $new_filename = preg_replace('/-+/', '-', $new_filename);
                $new_filename = trim($new_filename, '-');
            }
        }
        
        // Se il file principale è -scaled, aggiungi -scaled al nuovo nome
        if ($is_scaled_image && substr($new_filename, -7) !== '-scaled') {
            $new_filename .= '-scaled';
        }

        // Gestisci i nomi file duplicati
        $handle_duplicates = isset($options['handle_duplicates']) ?
            $options['handle_duplicates'] :
            ($this->settings_manager ? $this->settings_manager->get_setting('handle_duplicates', 'increment') : 'increment');

        $new_filename_with_ext = $this->handle_duplicate_filename($new_filename, $extension, $dir_path, $handle_duplicates);

        // Se la gestione duplicati restituisce false, significa che non dobbiamo rinominare
        if ($new_filename_with_ext === false) {
            return new WP_Error('duplicate_filename', __('A file with this name already exists and duplicate handling is set to fail.', 'imgseo-ai-alt-text-generator'));
        }

        // Manteniamo il nuovo nome file come restituito dalla gestione duplicati
        // Non estraiamo nuovamente il filename base perché potrebbe contenere suffissi come -1, -2, etc.
        $final_new_filename = $new_filename_with_ext;

        // Calcola il nuovo nome base (senza -scaled e senza estensione) per le thumbnail
        $new_filename_base = pathinfo($final_new_filename, PATHINFO_FILENAME);
        $new_filename_base_without_scaled = $is_scaled_image ? substr($new_filename_base, 0, -7) : $new_filename_base;
        
        // Check if new filename is same as old
        if ($old_filename === $final_new_filename) {
            return new WP_Error('same_filename', __('The new filename is the same as the current one.', 'imgseo-ai-alt-text-generator'));
        }

        // Create the new file path
        $new_file = str_replace($old_filename, $final_new_filename, $file);
        
        // Get the current metadata
        $old_metadata = wp_get_attachment_metadata($attachment_id);
        
        // Calculate OLD URL before any changes (for reference updates)
        $old_attachment_url = wp_get_attachment_url($attachment_id);
        
        // Get current thumbnails before rename
        $old_thumbnails = array();
        if (!empty($old_metadata['sizes'])) {
            foreach ($old_metadata['sizes'] as $size_name => $size_info) {
                $old_thumbnail_file = $dir_path . '/' . $size_info['file'];
                if (file_exists($old_thumbnail_file)) {
                    $old_thumbnails[$size_name] = $old_thumbnail_file;
                }
            }
        }
        
        try {
            // Start a transaction if possible
            global $wpdb;
            $wpdb->query('START TRANSACTION');
            
            // Rename the main file using WP_Filesystem
            if (!WP_Filesystem()) {
                $wpdb->query('ROLLBACK');
                $this->logs_manager->log_rename_operation($attachment_id, $old_filename, $final_new_filename, 'error');
                return false;
            }
            global $wp_filesystem;
            if (!$wp_filesystem->move($file, $new_file)) {
                $wpdb->query('ROLLBACK');
                $this->logs_manager->log_rename_operation($attachment_id, $old_filename, $final_new_filename, 'error');
                return new WP_Error('rename_failed', __('Failed to rename the file.', 'imgseo-ai-alt-text-generator'));
            }

            // Update attachment metadata file path
            update_attached_file($attachment_id, $new_file);

            // IMPORTANTE: Gestisci il caso di immagini grandi scalate da WordPress (big_image_size_threshold)
            // WordPress 5.3+ crea automaticamente una versione -scaled per immagini > 2560px
            // In questo caso:
            // - Il file principale (da get_attached_file) è filename-scaled.jpg
            // - L'originale molto grande è salvato come metadata['original_image'] = 'filename.jpg'
            // - Le thumbnail usano il nome BASE senza -scaled: filename-300x300.jpg
            // Dobbiamo rinominare ENTRAMBI i file mantenendo questa logica
            if (!empty($old_metadata['original_image'])) {
                $original_image_filename = $old_metadata['original_image'];
                $original_image_path = $dir_path . '/' . $original_image_filename;

                if (file_exists($original_image_path)) {
                    $original_extension = pathinfo($original_image_filename, PATHINFO_EXTENSION);

                    // L'originale usa SEMPRE il nome base senza -scaled
                    $new_original_filename = $new_filename_base_without_scaled . '.' . $original_extension;
                    $new_original_path = $dir_path . '/' . $new_original_filename;

                    // Rinomina il file originale grande
                    if ($wp_filesystem->move($original_image_path, $new_original_path)) {
                        // Aggiorna il metadata con il nuovo nome
                        $old_metadata['original_image'] = $new_original_filename;

                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                            imgseo_debug_log('Renamed original large image: ' . $original_image_filename . ' → ' . $new_original_filename);
                        }
                    } else {
                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                            imgseo_debug_log('WARNING: Failed to rename original large image: ' . $original_image_path);
                        }
                    }
                }
            }
            
            // Manually rename all existing thumbnails - first get a complete inventory of all thumbnails
            $renamed_thumbnails = array();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'];
            $file_dir = dirname($file);
            
            // First, rename thumbnails that are in the metadata
            // IMPORTANTE: Le thumbnail usano il nome BASE senza -scaled anche se generate da file -scaled
            foreach ($old_thumbnails as $size_name => $old_thumbnail_path) {
                $thumb_path_parts = pathinfo($old_thumbnail_path);
                $thumb_old_filename = $thumb_path_parts['basename'];
                $thumb_extension = $thumb_path_parts['extension'];

                // CRITICO: Rimuovi -scaled dal nome della thumbnail se presente
                // Le thumbnail NON devono MAI avere -scaled nel nome (convenzione WordPress)
                $thumb_old_filename_without_ext = $thumb_path_parts['filename'];

                // Se la thumbnail ha -scaled nel nome (errore di WordPress o rename precedente), rimuovilo
                $thumb_old_filename_clean = str_replace('-scaled', '', $thumb_old_filename_without_ext);

                // Ora sostituisci il nome base vecchio con quello nuovo (entrambi senza -scaled)
                $thumb_new_filename_without_ext = str_replace($old_filename_base_without_scaled, $new_filename_base_without_scaled, $thumb_old_filename_clean);

                // Ricostruisci il nome completo
                $thumb_new_filename = $thumb_new_filename_without_ext . '.' . $thumb_extension;
                $thumb_new_path = $thumb_path_parts['dirname'] . '/' . $thumb_new_filename;

                // Rename the thumbnail file
                if (file_exists($old_thumbnail_path)) {
                    if ($wp_filesystem->move($old_thumbnail_path, $thumb_new_path)) {
                        $renamed_thumbnails[$size_name] = $thumb_new_path;

                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                            imgseo_debug_log('Renamed thumbnail: ' . basename($old_thumbnail_path) . ' → ' . $thumb_new_filename);
                        }
                    }
                }
            }

            // Then search for any other thumbnail sizes that might not be in the metadata
            // Common WordPress thumbnail pattern is: filename-WIDTHxHEIGHT.ext (senza -scaled!)
            // MA potrebbero esistere thumbnail con -scaled nel nome (errore o rename precedente)
            $dir_handle = opendir($file_dir);
            if ($dir_handle) {
                // Pattern per thumbnail senza -scaled
                $pattern_normal = '/^' . preg_quote($old_filename_base_without_scaled, '/') . '-\d+x\d+\.[a-zA-Z0-9]+$/';

                // Pattern per thumbnail con -scaled (da correggere)
                $pattern_scaled = '/^' . preg_quote($old_filename_base, '/') . '-\d+x\d+\.[a-zA-Z0-9]+$/';

                while (($file_in_dir = readdir($dir_handle)) !== false) {
                    // Skip . and .. directories
                    if ($file_in_dir == '.' || $file_in_dir == '..') {
                        continue;
                    }

                    // Check if this is a thumbnail file for our image (con o senza -scaled)
                    $is_thumbnail_normal = preg_match($pattern_normal, $file_in_dir);
                    $is_thumbnail_scaled = $is_scaled_image && preg_match($pattern_scaled, $file_in_dir);

                    if ($is_thumbnail_normal || $is_thumbnail_scaled) {
                        $old_thumb_path = $file_dir . '/' . $file_in_dir;

                        // Only continue if this thumbnail wasn't already renamed above
                        if (!in_array($old_thumb_path, $old_thumbnails)) {
                            // Parse filename
                            $thumb_parts = pathinfo($file_in_dir);
                            $thumb_name_clean = str_replace('-scaled', '', $thumb_parts['filename']);

                            // Create new thumbnail filename (SEMPRE senza -scaled)
                            $thumb_new_name = str_replace($old_filename_base_without_scaled, $new_filename_base_without_scaled, $thumb_name_clean);
                            $new_thumb_filename = $thumb_new_name . '.' . $thumb_parts['extension'];
                            $new_thumb_path = $file_dir . '/' . $new_thumb_filename;

                            // Rename the thumbnail
                            if ($wp_filesystem->move($old_thumb_path, $new_thumb_path)) {
                                $renamed_thumbnails['extra_' . count($renamed_thumbnails)] = $new_thumb_path;

                                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                                    imgseo_debug_log('Renamed extra thumbnail: ' . $file_in_dir . ' → ' . $new_thumb_filename);
                                }
                                // Debug: Renamed additional thumbnail
                            }
                        }
                    }
                }
                
                closedir($dir_handle);
            }
            
            // NEW: Handle WebP, AVIF and other format variants
            // IMPORTANTE: Le varianti moderne (WebP, AVIF) seguono la stessa convenzione:
            // - Thumbnail e originale: usano nome base SENZA -scaled
            // - File principale scaled: usa nome CON -scaled

            // Prima rinomina le varianti delle thumbnail e dell'originale (senza -scaled)
            $this->rename_format_variants($file_dir, $old_filename_base_without_scaled, $new_filename_base_without_scaled, $wp_filesystem, $renamed_thumbnails);

            // Se il file principale è scaled, rinomina anche le sue varianti moderne (con -scaled)
            if ($is_scaled_image) {
                $this->rename_format_variants($file_dir, $old_filename_base, $new_filename_base, $wp_filesystem, $renamed_thumbnails);
            }

            // Salva il valore aggiornato di original_image (se esiste)
            $updated_original_image = isset($old_metadata['original_image']) ? $old_metadata['original_image'] : null;

            // IMPORTANTE: NON rigenerare i metadata chiamando wp_generate_attachment_metadata()
            // perché questo triggera hook (compressor, ecc.) e può causare problemi
            // Invece, aggiorniamo manualmente i metadata esistenti con i nuovi nomi
            $metadata = $old_metadata;

            // Aggiorna il nome del file principale nei metadata
            if (isset($metadata['file'])) {
                $metadata['file'] = str_replace($old_filename, $final_new_filename, $metadata['file']);
            }

            // Aggiorna il campo original_image se presente
            if ($updated_original_image) {
                $metadata['original_image'] = $updated_original_image;
            }

            // Aggiorna i nomi delle thumbnail nei metadata
            if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
                foreach ($metadata['sizes'] as $size_name => $size_info) {
                    if (isset($size_info['file'])) {
                        // Sostituisci il vecchio nome base con il nuovo (senza -scaled per le thumbnail)
                        $old_thumb_name = $size_info['file'];
                        $new_thumb_name = str_replace($old_filename_base_without_scaled, $new_filename_base_without_scaled, $old_thumb_name);
                        $metadata['sizes'][$size_name]['file'] = $new_thumb_name;
                    }
                }
            }

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Updated metadata manually (without regenerating) for attachment: ' . $attachment_id);
                imgseo_debug_log('Metadata sizes after manual update: ' . (isset($metadata['sizes']) ? count($metadata['sizes']) : 0));
            }

            // IMPORTANTE: Rimuovi temporaneamente il nostro hook per evitare ricorsione
            // quando aggiorniamo i metadata
            remove_action('updated_post_meta', array($this, 'execute_auto_rename_after_save'), 10);

            // PROTEZIONE: Blocca temporaneamente tutti i plugin di ottimizzazione immagini
            // per evitare che facciano resize/compress durante il rename
            $blocked_hooks = $this->block_external_optimization_hooks($attachment_id);

            // Update the database to use the new metadata
            wp_update_attachment_metadata($attachment_id, $metadata);

            // VERIFICA FINALE: Controlla che i metadata siano corretti e corrispondano ai file fisici
            $metadata = $this->verify_and_fix_metadata($attachment_id, $metadata, $dir_path, $is_scaled_image);

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Metadata sizes after verify_and_fix: ' . (isset($metadata['sizes']) ? count($metadata['sizes']) : 0));
            }

            // Aggiorna nuovamente i metadata dopo la verifica
            wp_update_attachment_metadata($attachment_id, $metadata);

            // PROTEZIONE: Ripristina tutti gli hook di ottimizzazione bloccati
            $this->restore_external_optimization_hooks($blocked_hooks);

            // Ri-aggiungi il nostro hook
            add_action('updated_post_meta', array($this, 'execute_auto_rename_after_save'), 10, 4);

            // Update post guid if needed
            $guid = get_the_guid($attachment_id);
            if (strpos($guid, $old_filename) !== false) {
                $new_guid = str_replace($old_filename, $final_new_filename, $guid);
                $wpdb->update(
                    $wpdb->posts,
                    array('guid' => $new_guid),
                    array('ID' => $attachment_id)
                );
            }
            
            // Get old and new attachment URLs for reference updates
            $old_url_base = $old_attachment_url; // URL before rename (saved earlier)
            $new_url = wp_get_attachment_url($attachment_id); // URL after rename (updated)
            
            // Prepara array di vecchi e nuovi URL per gli aggiornamenti
            $old_urls = array($old_url_base);
            $new_urls = array($new_url);
            
            // Raccogli URL di miniature per gli aggiornamenti
            // IMPORTANTE: Le thumbnail usano il nome base SENZA -scaled
            if (!empty($old_metadata['sizes'])) {
                foreach ($old_metadata['sizes'] as $size => $size_info) {
                    $old_size_file = $size_info['file'];
                    $old_size_url = str_replace(basename($old_url_base), $old_size_file, $old_url_base);
                    $old_urls[] = $old_size_url;

                    // Calcola l'URL nuovo corrispondente
                    // Usa il nome base senza -scaled per le thumbnail (convenzione WordPress)
                    $new_size_file = str_replace($old_filename_base_without_scaled, $new_filename_base_without_scaled, $old_size_file);
                    $new_size_url = str_replace(basename($new_url), $new_size_file, $new_url);
                    $new_urls[] = $new_size_url;
                }
            }
            
            // Determina se dobbiamo aggiornare i riferimenti (manteniamo compatibilità con vecchie versioni)
            $update_references = isset($options['update_references']) ? (bool)$options['update_references'] : true;
            // Inizializza sempre la variabile per evitare warning quando gli aggiornamenti sono disabilitati
            $integration_results = array();
            
            if ($update_references) {
                // Fix: Extract directory from old_url_base since it contains the full file URL
                $old_url_directory = dirname($old_url_base);
                $new_url_directory = dirname($new_url);
                
                // Update URLs in post content with correct directory paths
                $this->update_image_references($old_filename_base, $new_filename_base, $old_url_directory, $new_url_directory);
                
                // Aggiorna riferimenti nei page builder usando l'Integration Manager SOLO se necessario
                $enable_integrations = get_option('imgseo_renamer_enable_integrations', 1);
                
                if ($enable_integrations && !defined('IMGSEO_DISABLE_INTEGRATIONS')) {
                    // Carica l'integration manager solo se effettivamente necessario
                    if (!$this->integration_manager && class_exists('Renamer_Integration_Manager')) {
                        $this->integration_manager = Renamer_Integration_Manager::get_instance();
                    }
                    
                    if ($this->integration_manager) {
                        $integration_results = $this->integration_manager->update_all_references($old_urls, $new_urls, $attachment_id);
                    }
                }
                
                // Force refresh post caches (solo se stiamo aggiornando i riferimenti)
                clean_post_cache($attachment_id);
                
                // Clear all WordPress caches to ensure URL updates are reflected immediately
                wp_cache_flush();
                
                // Force regeneration of attachment metadata to update URLs
                wp_update_attachment_metadata($attachment_id, wp_get_attachment_metadata($attachment_id));
                
                // Debug: Cache cleared and metadata updated
            }
            
            // Update _wp_attached_file again to ensure it's correctly set
            update_post_meta($attachment_id, '_wp_attached_file', str_replace(trailingslashit($upload_dir['basedir']), '', $new_file));

            // Update attachment slug (post_name) to match the new filename base
            // Default behavior: keep slug in sync with filename when renaming
            $update_slug = isset($options['update_slug']) ? (bool)$options['update_slug'] : true;
            if ($update_slug) {
                $new_slug_base = pathinfo($final_new_filename, PATHINFO_FILENAME);
                $desired_slug = sanitize_title($new_slug_base);

                // Ensure unique slug within attachments context
                $parent_id = wp_get_post_parent_id($attachment_id);
                if (function_exists('wp_unique_post_slug')) {
                    $unique_slug = wp_unique_post_slug($desired_slug, $attachment_id, $attachment->post_status, 'attachment', $parent_id);
                } else {
                    $unique_slug = $desired_slug;
                }

                if (!empty($unique_slug) && $attachment->post_name !== $unique_slug) {
                    wp_update_post(array(
                        'ID' => $attachment_id,
                        'post_name' => $unique_slug,
                    ));
                }
            }

            // Log the successful operation
            $this->logs_manager->log_rename_operation($attachment_id, $old_filename, $final_new_filename, 'success');

            // Trigger action hook for other components (e.g., sitemap invalidation)
            do_action('imgseo_image_renamed', $attachment_id, $old_filename, $final_new_filename);

            // Update compression backup paths to maintain compatibility
            $this->update_compression_backup_paths($attachment_id, $old_filename, $final_new_filename);

            // Commit the transaction
            $wpdb->query('COMMIT');
            
            return array(
                'old_filename' => $old_filename,
                'new_filename' => $final_new_filename,
                'new_url' => $new_url,
                'thumbnails_renamed' => count($renamed_thumbnails),
                'integration_results' => $integration_results
            );
            
        } catch (Exception $e) {
            // Rollback the transaction
            $wpdb->query('ROLLBACK');
            
            // Log the error
            $this->logs_manager->log_rename_operation($attachment_id, $old_filename, $final_new_filename, 'error');
            
            return new WP_Error('exception', $e->getMessage());
        }
    }
    
    /**
     * Gestisce la generazione di nomi file in caso di duplicati
     * 
     * @param string $filename Nome file base (senza estensione)
     * @param string $extension Estensione del file
     * @param string $dir_path Percorso della directory
     * @param string $mode Modalità di gestione ("increment", "timestamp", "fail")
     * @return string|false Nuovo nome file con estensione o false se fallisce
     */
    private function handle_duplicate_filename($filename, $extension, $dir_path, $mode = 'increment') {
        $filename_with_ext = $filename . '.' . $extension;
        $file_path = $dir_path . '/' . $filename_with_ext;
        
        // Se il file non esiste già, usa il nome originale
        if (!file_exists($file_path)) {
            return $filename_with_ext;
        }
        
        // Gestisci il caso in cui esiste già un file con questo nome
        switch ($mode) {
            case 'increment':
                // Aggiungi un numero incrementale (file-1.jpg, file-2.jpg, ecc.)
                $i = 1;
                while (file_exists($dir_path . '/' . $filename . '-' . $i . '.' . $extension)) {
                    $i++;
                }
                return $filename . '-' . $i . '.' . $extension;
                
            case 'timestamp':
                // Aggiungi un timestamp (file-1679419361.jpg)
                return $filename . '-' . time() . '.' . $extension;
                
            case 'fail':
                // Non rinominare se esiste già un file con lo stesso nome
                return false;
                
            default:
                return $filename_with_ext;
        }
    }
    
    /**
     * Restore a previously renamed image file to its original filename
     * 
     * @param int $attachment_id The attachment ID
     * @param string $original_filename The original filename to restore to
     * @param string $current_filename The current filename
     * @return array|WP_Error Result of the restore operation
     */
    public function restore_image($attachment_id, $original_filename, $current_filename) {
        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new WP_Error('invalid_attachment', __('Invalid attachment.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Get attachment file path
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return new WP_Error('file_not_found', __('Attachment file not found.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Extract original and current filename components
        $orig_path_parts = pathinfo($original_filename);
        $curr_path_parts = pathinfo($current_filename);
        
        $original_base = $orig_path_parts['filename'];
        $current_base = $curr_path_parts['filename'];
        $extension = isset($curr_path_parts['extension']) ? $curr_path_parts['extension'] : '';
        
        if (empty($extension)) {
            return new WP_Error('no_extension', __('Could not determine file extension.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Get the directory from the current file path
        $dir_path = pathinfo($file, PATHINFO_DIRNAME);
        
        // Set the paths for renaming
        $current_path = $file;
        $original_path = $dir_path . '/' . $original_filename;
        
        // Check if the original filename already exists (to prevent conflicts)
        if (file_exists($original_path)) {
            return new WP_Error('file_exists', __('Cannot restore: a file with the original filename already exists.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Get the current metadata
        $old_metadata = wp_get_attachment_metadata($attachment_id);
        
        // Calculate CURRENT URL before any changes (for reference updates during restore)
        $current_attachment_url = wp_get_attachment_url($attachment_id);
        
        // Get current thumbnails before rename
        $current_thumbnails = array();
        if (!empty($old_metadata['sizes'])) {
            foreach ($old_metadata['sizes'] as $size_name => $size_info) {
                $current_thumbnail_file = $dir_path . '/' . $size_info['file'];
                if (file_exists($current_thumbnail_file)) {
                    $current_thumbnails[$size_name] = $current_thumbnail_file;
                }
            }
        }
        
        try {
            // Start a transaction if possible
            global $wpdb;
            $wpdb->query('START TRANSACTION');
            
            // Rename the main file using WP_Filesystem
            if (!WP_Filesystem()) {
                $wpdb->query('ROLLBACK');
                $this->logs_manager->log_rename_operation($attachment_id, $current_filename, $original_filename, 'error');
                return false;
            }
            global $wp_filesystem;
            if (!$wp_filesystem->move($current_path, $original_path)) {
                $wpdb->query('ROLLBACK');
                $this->logs_manager->log_rename_operation($attachment_id, $current_filename, $original_filename, 'error');
                return new WP_Error('rename_failed', __('Failed to restore the file.', 'imgseo-ai-alt-text-generator'));
            }
            
            // Update attachment metadata file path
            update_attached_file($attachment_id, $original_path);
            
            // Manually rename all existing thumbnails - first get a complete inventory of all thumbnails
            $restored_thumbnails = array();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'];
            $file_dir = dirname($file);
            
            // First, rename thumbnails that are in the metadata
            foreach ($current_thumbnails as $size_name => $current_thumb_path) {
                $thumb_path_parts = pathinfo($current_thumb_path);
                $thumb_current_filename = $thumb_path_parts['basename'];
                
                // Replace the base filename part in the thumbnail filename
                $thumb_original_filename = str_replace($current_base, $original_base, $thumb_current_filename);
                $thumb_original_path = $thumb_path_parts['dirname'] . '/' . $thumb_original_filename;
                
                // Rename the thumbnail file
                if (file_exists($current_thumb_path)) {
                    if ($wp_filesystem->move($current_thumb_path, $thumb_original_path)) {
                        $restored_thumbnails[$size_name] = $thumb_original_path;
                        // Debug: Restored thumbnail
                    }
                }
            }
            
            // Then search for any other thumbnail sizes that might not be in the metadata
            // Common WordPress thumbnail pattern is: filename-WIDTHxHEIGHT.ext
            $dir_handle = opendir($file_dir);
            if ($dir_handle) {
                $pattern = '/^' . preg_quote($current_base, '/') . '-\d+x\d+\.[a-zA-Z0-9]+$/';
                
                while (($file_in_dir = readdir($dir_handle)) !== false) {
                    // Skip . and .. directories
                    if ($file_in_dir == '.' || $file_in_dir == '..') {
                        continue;
                    }
                    
                    // Check if this is a thumbnail file for our image
                    if (preg_match($pattern, $file_in_dir)) {
                        $current_thumb_path = $file_dir . '/' . $file_in_dir;
                        
                        // Only continue if this thumbnail wasn't already renamed above
                        if (!in_array($current_thumb_path, $current_thumbnails)) {
                            // Create new thumbnail filename
                            $thumb_original_filename = str_replace($current_base, $original_base, $file_in_dir);
                            $thumb_original_path = $file_dir . '/' . $thumb_original_filename;
                            
                            // Rename the thumbnail
                            if ($wp_filesystem->move($current_thumb_path, $thumb_original_path)) {
                                $restored_thumbnails['extra_' . count($restored_thumbnails)] = $thumb_original_path;
                                // Debug: Restored additional thumbnail
                            }
                        }
                    }
                }
                
                closedir($dir_handle);
            }
            
            // Generate metadata for the attachment
            $metadata = wp_generate_attachment_metadata($attachment_id, $original_path);
            
            // Update the database to use the new metadata
            wp_update_attachment_metadata($attachment_id, $metadata);
            
            // Update post guid if needed
            $guid = get_the_guid($attachment_id);
            if (strpos($guid, $current_filename) !== false) {
                $new_guid = str_replace($current_filename, $original_filename, $guid);
                $wpdb->update(
                    $wpdb->posts,
                    array('guid' => $new_guid),
                    array('ID' => $attachment_id)
                );
            }
            
            // Get old and new attachment URLs for reference updates (restore operation)
            $old_url_base = $current_attachment_url; // URL before restore (saved earlier)  
            $new_url = wp_get_attachment_url($attachment_id); // URL after restore (updated)
            
            // Prepara array di vecchi e nuovi URL per gli aggiornamenti
            $old_urls = array($old_url_base);
            $new_urls = array($new_url);
            
            // Raccogli URL di miniature per gli aggiornamenti
            if (!empty($old_metadata['sizes'])) {
                foreach ($old_metadata['sizes'] as $size => $size_info) {
                    $current_size_file = $size_info['file'];
                    $current_size_url = str_replace(basename($old_url_base), $current_size_file, $old_url_base);
                    $old_urls[] = $current_size_url;
                    
                    // Calcola l'URL originale corrispondente
                    $original_size_file = str_replace($current_base, $original_base, $current_size_file);
                    $original_size_url = str_replace(basename($new_url), $original_size_file, $new_url);
                    $new_urls[] = $original_size_url;
                }
            }
            
            // Update URLs in post content
            $this->update_image_references($current_base, $original_base, dirname($old_url_base), dirname($new_url));
            
            // Aggiorna riferimenti nei page builder usando l'Integration Manager SOLO se necessario
            $integration_results = array();
            $enable_integrations = get_option('imgseo_renamer_enable_integrations', 1);
            
            if ($enable_integrations && !defined('IMGSEO_DISABLE_INTEGRATIONS')) {
                // Carica l'integration manager solo se effettivamente necessario
                if (!$this->integration_manager && class_exists('Renamer_Integration_Manager')) {
                    $this->integration_manager = Renamer_Integration_Manager::get_instance();
                }
                
                if ($this->integration_manager) {
                    $integration_results = $this->integration_manager->update_all_references($old_urls, $new_urls, $attachment_id);
                }
            }
            
            // Add a short delay to let WordPress process the file changes
            usleep(300000); // 0.3 secondi invece di 1 secondo
            
            // Force refresh post caches
            $this->force_refresh_content_caches();
            
            // Update _wp_attached_file again to ensure it's correctly set
            update_post_meta($attachment_id, '_wp_attached_file', str_replace(trailingslashit($upload_dir['basedir']), '', $original_path));

            // Restore attachment slug (post_name) to match the original filename base
            // Default behavior: keep slug in sync with filename when restoring
            $restore_slug = true; // can be gated if needed in future via options
            if ($restore_slug) {
                $original_slug_base = pathinfo($original_filename, PATHINFO_FILENAME);
                $desired_slug = sanitize_title($original_slug_base);

                // Ensure unique slug within attachments context
                $parent_id = wp_get_post_parent_id($attachment_id);
                if (function_exists('wp_unique_post_slug')) {
                    $unique_slug = wp_unique_post_slug($desired_slug, $attachment_id, $attachment->post_status, 'attachment', $parent_id);
                } else {
                    $unique_slug = $desired_slug;
                }

                if (!empty($unique_slug) && $attachment->post_name !== $unique_slug) {
                    wp_update_post(array(
                        'ID' => $attachment_id,
                        'post_name' => $unique_slug,
                    ));
                }
            }

            // Log the successful operation
            $this->logs_manager->log_rename_operation($attachment_id, $current_filename, $original_filename, 'restore');

            // Update compression backup paths to maintain compatibility after restore
            $this->update_compression_backup_paths($attachment_id, $current_filename, $original_filename);

            // Commit the transaction
            $wpdb->query('COMMIT');
            
            return array(
                'old_filename' => $current_filename,
                'new_filename' => $original_filename,
                'new_url' => $new_url,
                'thumbnails_restored' => count($restored_thumbnails),
                'integration_results' => $integration_results
            );
            
        } catch (Exception $e) {
            // Rollback the transaction
            $wpdb->query('ROLLBACK');
            
            // Log the error
            $this->logs_manager->log_rename_operation($attachment_id, $current_filename, $original_filename, 'error');
            
            return new WP_Error('exception', $e->getMessage());
        }
    }

    /**
     * Verify and fix attachment metadata to ensure consistency with physical files
     * This is crucial for scaled images where WordPress may generate incorrect metadata
     *
     * @param int $attachment_id Attachment ID
     * @param array $metadata Current metadata array
     * @param string $dir_path Directory path where files are stored
     * @param bool $is_scaled_image Whether this is a scaled image
     * @return array Corrected metadata
     */
    private function verify_and_fix_metadata($attachment_id, $metadata, $dir_path, $is_scaled_image) {
        $changes_made = false;

        // 1. Verifica il file principale
        $main_file = get_attached_file($attachment_id);
        if (!file_exists($main_file)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('WARNING: Main file does not exist: ' . $main_file);
            }
            return $metadata; // File principale non esiste, problema grave
        }

        // 2. Verifica e correggi original_image se presente
        if (!empty($metadata['original_image'])) {
            $original_file_path = $dir_path . '/' . $metadata['original_image'];

            if (!file_exists($original_file_path)) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('WARNING: Original image does not exist, removing from metadata: ' . $metadata['original_image']);
                }
                unset($metadata['original_image']);
                $changes_made = true;
            } else {
                // Verifica che non contenga -scaled (non dovrebbe mai averlo)
                $original_name = $metadata['original_image'];
                if (strpos($original_name, '-scaled') !== false) {
                    $corrected_name = str_replace('-scaled', '', $original_name);
                    $metadata['original_image'] = $corrected_name;
                    $changes_made = true;

                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('Corrected original_image name in metadata: ' . $original_name . ' → ' . $corrected_name);
                    }
                }
            }
        }

        // 3. Verifica e correggi tutte le thumbnail
        if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('verify_and_fix_metadata: Checking ' . count($metadata['sizes']) . ' thumbnails');
                imgseo_debug_log('verify_and_fix_metadata: Base dir_path: ' . $dir_path);
            }

            foreach ($metadata['sizes'] as $size_name => $size_info) {
                $should_remove = false;

                // Verifica che abbia il campo 'file'
                if (!isset($size_info['file'])) {
                    $should_remove = true;
                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('WARNING: Size ' . $size_name . ' missing file field');
                    }
                } else {
                    $thumb_filename = $size_info['file'];
                    $thumb_path = $dir_path . '/' . $thumb_filename;

                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('Checking thumbnail: ' . $thumb_filename);
                        imgseo_debug_log('Full path: ' . $thumb_path);
                        imgseo_debug_log('Exists: ' . (file_exists($thumb_path) ? 'YES' : 'NO'));
                    }

                    // Verifica che il file esista
                    if (!file_exists($thumb_path)) {
                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                            imgseo_debug_log('WARNING: Thumbnail does not exist, removing from metadata: ' . $thumb_filename . ' (size: ' . $size_name . ')');
                            // List files in directory for debugging
                            $files_in_dir = scandir($dir_path);
                            imgseo_debug_log('Files in directory: ' . implode(', ', array_slice($files_in_dir, 2, 10))); // Skip . and ..
                        }
                        $should_remove = true;
                    } else {
                        // Verifica che non contenga -scaled nel nome (le thumbnail non dovrebbero mai averlo)
                        if (strpos($thumb_filename, '-scaled') !== false) {
                            $corrected_filename = str_replace('-scaled', '', $thumb_filename);
                            $corrected_path = $dir_path . '/' . $corrected_filename;

                            // IMPORTANTE: Rinomina il file fisico se esiste con -scaled
                            if (file_exists($thumb_path)) {
                                // Inizializza WP_Filesystem se necessario
                                if (!function_exists('WP_Filesystem')) {
                                    require_once ABSPATH . 'wp-admin/includes/file.php';
                                }
                                WP_Filesystem();
                                global $wp_filesystem;

                                // Se il file corretto esiste già, elimina quello con -scaled
                                if (file_exists($corrected_path)) {
                                    $wp_filesystem->delete($thumb_path);
                                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                                        imgseo_debug_log('Deleted duplicate thumbnail with -scaled: ' . $thumb_filename);
                                    }
                                } else {
                                    // Rinomina il file da -scaled a senza -scaled
                                    if ($wp_filesystem->move($thumb_path, $corrected_path)) {
                                        $metadata['sizes'][$size_name]['file'] = $corrected_filename;
                                        $changes_made = true;

                                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                                            imgseo_debug_log('Renamed thumbnail file: ' . $thumb_filename . ' → ' . $corrected_filename);
                                        }
                                    } else {
                                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                                            imgseo_debug_log('Failed to rename thumbnail file: ' . $thumb_path);
                                        }
                                        $should_remove = true;
                                    }
                                }
                            } else {
                                // File con -scaled non esiste ma è nei metadata? Verifica se esiste senza -scaled
                                if (file_exists($corrected_path)) {
                                    // File corretto esiste, aggiorna solo metadata
                                    $metadata['sizes'][$size_name]['file'] = $corrected_filename;
                                    $changes_made = true;
                                } else {
                                    // Nessun file esiste, rimuovi dai metadata
                                    $should_remove = true;
                                }
                            }
                        }

                        // Verifica le dimensioni (width e height)
                        if (!$should_remove) {
                            if (!isset($size_info['width']) || !isset($size_info['height']) ||
                                $size_info['width'] <= 0 || $size_info['height'] <= 0) {

                                // Prova a ottenere le dimensioni reali dal file
                                $image_size = @getimagesize($thumb_path);
                                if ($image_size && isset($image_size[0]) && isset($image_size[1])) {
                                    $metadata['sizes'][$size_name]['width'] = $image_size[0];
                                    $metadata['sizes'][$size_name]['height'] = $image_size[1];
                                    $changes_made = true;

                                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                                        imgseo_debug_log('Fixed dimensions for thumbnail ' . $thumb_filename . ': ' . $image_size[0] . 'x' . $image_size[1]);
                                    }
                                } else {
                                    $should_remove = true;
                                }
                            }
                        }
                    }
                }

                // Rimuovi la thumbnail dai metadata se necessario
                if ($should_remove) {
                    unset($metadata['sizes'][$size_name]);
                    $changes_made = true;
                }
            }
        }

        // 4. Verifica finale: assicurati che il file principale esista e sia accessibile
        $main_file = get_attached_file($attachment_id);
        $file_field = isset($metadata['file']) ? $metadata['file'] : '';

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Final metadata verification for attachment ' . $attachment_id . ':');
            imgseo_debug_log('  - Main file (get_attached_file): ' . $main_file);
            imgseo_debug_log('  - File field in metadata: ' . $file_field);
            imgseo_debug_log('  - File exists: ' . (file_exists($main_file) ? 'YES' : 'NO'));
            if (!empty($metadata['original_image'])) {
                imgseo_debug_log('  - Original image: ' . $metadata['original_image']);
            }
            if (!empty($metadata['sizes'])) {
                imgseo_debug_log('  - Thumbnail count: ' . count($metadata['sizes']));
                $scaled_thumbs = 0;
                foreach ($metadata['sizes'] as $size_name => $size_info) {
                    if (isset($size_info['file']) && strpos($size_info['file'], '-scaled') !== false) {
                        $scaled_thumbs++;
                    }
                }
                if ($scaled_thumbs > 0) {
                    imgseo_debug_log('  - WARNING: ' . $scaled_thumbs . ' thumbnails still have -scaled in name!');
                }
            }
        }

        // 5. Log finale
        if ($changes_made && defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Metadata verification completed for attachment ID ' . $attachment_id . ' - changes were made');
        }

        return $metadata;
    }

    /**
     * Update image references in post content and metadata
     *
     * @param string $old_base Old filename base (without extension)
     * @param string $new_base New filename base (without extension)
     * @param string $old_url_base Old URL base (directory part)
     * @param string $new_url_base New URL base (directory part)
     */
    private function update_image_references($old_base, $new_base, $old_url_base, $new_url_base) {
        global $wpdb;
        
        // Update image references in post content
        
        // Make absolutely sure we have trailing slashes for path components
        $old_url_base = rtrim($old_url_base, '/');
        $new_url_base = rtrim($new_url_base, '/');
        
        // First, get ALL posts that could possibly contain images
        // This is more aggressive than before, but ensures we catch everything
        // Build targeted query for posts likely containing the old image reference
        $uploads = wp_get_upload_dir();
        $uploads_baseurl = isset($uploads['baseurl']) ? $uploads['baseurl'] : '';
        $uploads_basepath = $uploads_baseurl ? wp_parse_url($uploads_baseurl, PHP_URL_PATH) : '';
        if (empty($uploads_basepath)) { $uploads_basepath = '/wp-content/uploads'; }
        
        $like_abs = '%' . $wpdb->esc_like(rtrim($old_url_base, '/') . '/' . $old_base) . '%';
        $like_rel = '%' . $wpdb->esc_like(rtrim($uploads_basepath, '/') . '/' . $old_base) . '%';
        $like_name = '%' . $wpdb->esc_like($old_base) . '%';
        
        $posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_content FROM {$wpdb->posts}
                 WHERE post_type NOT IN ('revision','nav_menu_item')
                   AND post_status NOT IN ('auto-draft')
                   AND (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)",
                $like_abs, $like_rel, $like_name
            )
        );
     
         // Process posts that may contain image references
         
         // Direct replacement of URLs with old base
         $old_url_pattern = $old_url_base . '/' . $old_base;
         $new_url_pattern = $new_url_base . '/' . $new_base;
                 
         $posts_updated = 0;
         foreach ($posts as $post) {
             $original_content = $post->post_content;
             $updated_content = $original_content;
             $content_changed = false;
             
             // Check if this post contains any of our old URLs
             $contains_old_url = false;
             $old_url_pattern = $old_url_base . '/' . $old_base;
             if (strpos($original_content, $old_url_pattern) !== false) {
                 $contains_old_url = true;
             }
             
             // 1. Replace all occurrences of the old base filename with the new one
             // This handles both main files and thumbnails in a more robust way
             $old_pattern = preg_quote($old_url_base . '/' . $old_base, '/');
             $new_replacement = $new_url_base . '/' . $new_base;
             
             // Pattern to match the old filename with any extension or thumbnail suffix
             $pattern = '/' . $old_pattern . '((?:-\d+x\d+)?\.\w+)/';
             
             if (preg_match_all($pattern, $updated_content, $matches, PREG_SET_ORDER)) {
                 foreach ($matches as $match) {
                     $old_full_url = $match[0];
                     $suffix = $match[1]; // This includes thumbnail dimensions and extension
                     $new_full_url = $new_replacement . $suffix;
                     
                     $updated_content = str_replace($old_full_url, $new_full_url, $updated_content);
                     $content_changed = true;
                 }
             }
             
             // 2. Additional fallback: direct string replacement for any remaining occurrences
             $simple_old = $old_url_base . '/' . $old_base;
             $simple_new = $new_url_base . '/' . $new_base;
             if (strpos($updated_content, $simple_old) !== false) {
                 $updated_content = str_replace($simple_old, $simple_new, $updated_content);
                 $content_changed = true;
             }

            // 2b. Generic, domain-agnostic replacement that also handles relative/protocol-relative URLs
            // Preserve the existing directory in the URL/path and only swap the filename base
            $uploads_path_quoted = preg_quote(rtrim($uploads_basepath, '/'), '/');
            $generic_pattern = '/((?:https?:)?//[^"\')\s]+)?(' . $uploads_path_quoted . '\/[^"\')\s]+\/)' . preg_quote($old_base, '/') . '((?:-\d+x\d+)?\.\w+)/i';
            $generic_replacement = '$1$2' . $new_base . '$3';
            $updated_tmp = preg_replace($generic_pattern, $generic_replacement, $updated_content, -1, $generic_count);
            if ($updated_tmp !== null && $generic_count > 0) {
                $updated_content = $updated_tmp;
                $content_changed = true;
            }
             
             // 3. Replace srcset attributes which may contain multiple thumbnail URLs
             $srcset_pattern = '/srcset="([^"]*' . preg_quote($old_url_base . '/' . $old_base, '/') . '[^"]*)"/';
             if (preg_match_all($srcset_pattern, $updated_content, $srcset_matches, PREG_SET_ORDER)) {
                 foreach ($srcset_matches as $srcset_match) {
                     $old_srcset = $srcset_match[0];
                     $srcset_content = $srcset_match[1];
                     $new_srcset_content = str_replace(
                         $old_url_base . '/' . $old_base, 
                         $new_url_base . '/' . $new_base,
                         $srcset_content
                     );
                     $new_srcset = 'srcset="' . $new_srcset_content . '"';
                     
                     $updated_content = str_replace($old_srcset, $new_srcset, $updated_content);
                     $content_changed = true;
                 }
             }
 
             // 4. Replace data-* attributes which may contain image URLs
             $data_pattern = '/data-(?:large|medium|thumb|small|orig|src|image)="([^"]*' . preg_quote($old_url_base . '/' . $old_base, '/') . '[^"]*)"/i';
             if (preg_match_all($data_pattern, $updated_content, $data_matches, PREG_SET_ORDER)) {
                 foreach ($data_matches as $data_match) {
                     $old_data = $data_match[0];
                     $data_content = $data_match[1];
                     $new_data_content = str_replace(
                         $old_url_base . '/' . $old_base, 
                         $new_url_base . '/' . $new_base,
                         $data_content
                     );
                     $new_data = str_replace($data_content, $new_data_content, $old_data);
                     
                     $updated_content = str_replace($old_data, $new_data, $updated_content);
                     $content_changed = true;
                 }
             }
 
             // Update the post if content has changed
             if ($content_changed) {
                 $wpdb->update(
                     $wpdb->posts,
                     array('post_content' => $updated_content),
                     array('ID' => $post->ID)
                 );
                 $posts_updated++;
                 
                 // Clear any post cache
                 clean_post_cache($post->ID);
             }
         }
 
         // Posts updated: $posts_updated (removed verbose logging)
 
         // Update postmeta (for galleries, featured images, etc.)
         $meta_items = $wpdb->get_results(
             $wpdb->prepare(
                 "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} 
                 WHERE meta_value LIKE %s OR meta_value LIKE %s",
                 '%' . $wpdb->esc_like($old_url_base) . '%',
                 '%' . $wpdb->esc_like($old_base) . '%'
             )
         );
         
         foreach ($meta_items as $meta) {
             $updated_value = $meta->meta_value;
             $value_changed = false;
             
             // Handle serialized data
             if (is_serialized($meta->meta_value)) {
                 // Unserialize, modify, and reserialize
                 $unserialized = @unserialize($meta->meta_value);
                 if ($unserialized !== false) {
                     // Convert to JSON and back to handle nested structures
                     $json = json_encode($unserialized);
                     if ($json !== false) {
                         // Replace in JSON string
                         $old_patterns = array(
                             $old_url_base . '/' . $old_base . '.',
                             '"' . $old_url_base . '/' . $old_base . '-',
                             '{' . $old_url_base . '/' . $old_base . '-',
                             '[' . $old_url_base . '/' . $old_base . '-',
                             ' ' . $old_url_base . '/' . $old_base . '-'
                         );
                         
                         $new_patterns = array(
                             $new_url_base . '/' . $new_base . '.',
                             '"' . $new_url_base . '/' . $new_base . '-',
                             '{' . $new_url_base . '/' . $new_base . '-',
                             '[' . $new_url_base . '/' . $new_base . '-',
                             ' ' . $new_url_base . '/' . $new_base . '-'
                         );
                         
                         $json_new = str_replace($old_patterns, $new_patterns, $json);

                        // Generic fallback (domain-agnostic) inside JSON string
                        $uploads_path_quoted = preg_quote(rtrim($uploads_basepath, '/'), '/');
                        $generic_pattern = '/((?:https?:)?\/\/[^"\'\)\s]+)?(' . $uploads_path_quoted . '\/[^"\'\)\s]+\/)' . preg_quote($old_base, '/') . '((?:-\d+x\d+)?\.\w+)/i';
                        $json_fallback = preg_replace($generic_pattern, '$1$2' . $new_base . '$3', $json_new, -1, $json_count);
                        if ($json_fallback !== null && $json_count > 0) {
                            $json_new = $json_fallback;
                        }
                         
                         if ($json_new !== $json) {
                             $value_changed = true;
                             // Convert back to PHP array
                             $modified = json_decode($json_new, true);
                             if (json_last_error() === JSON_ERROR_NONE) {
                                 // Reserialize
                                 $updated_value = serialize($modified);
                             }
                         }
                     }
                 }
             } 
             // Handle JSON data
             else if (($meta->meta_value[0] === '{' && substr($meta->meta_value, -1) === '}') || 
                     ($meta->meta_value[0] === '[' && substr($meta->meta_value, -1) === ']')) {
                 
                 // Replace in JSON string
                 $json = $meta->meta_value;
                 $old_patterns = array(
                     $old_url_base . '/' . $old_base . '.',
                     '"' . $old_url_base . '/' . $old_base . '-',
                     ' ' . $old_url_base . '/' . $old_base . '-'
                 );
                 
                 $new_patterns = array(
                     $new_url_base . '/' . $new_base . '.',
                     '"' . $new_url_base . '/' . $new_base . '-',
                     ' ' . $new_url_base . '/' . $new_base . '-'
                 );
                 
                 $json_new = str_replace($old_patterns, $new_patterns, $json);

                // Generic fallback (domain-agnostic) inside JSON string
                $uploads_path_quoted = preg_quote(rtrim($uploads_basepath, '/'), '/');
                $generic_pattern = '/((?:https?:)?\/\/[^"\'\)\s]+)?(' . $uploads_path_quoted . '\/[^"\'\)\s]+\/)' . preg_quote($old_base, '/') . '((?:-\d+x\d+)?\.\w+)/i';
                $json_fallback = preg_replace($generic_pattern, '$1$2' . $new_base . '$3', $json_new, -1, $json_count);
                if ($json_fallback !== null && $json_count > 0) {
                    $json_new = $json_fallback;
                }
                 
                 if ($json_new !== $json) {
                     $updated_value = $json_new;
                     $value_changed = true;
                 }
             }
             // Handle regular string metadata
             else {
                 // Direct replacement for full URLs
                 if (strpos($meta->meta_value, $old_url_base . '/' . $old_base) !== false) {
                     $updated_value = str_replace(
                         $old_url_base . '/' . $old_base,
                         $new_url_base . '/' . $new_base,
                         $meta->meta_value
                     );
                     $value_changed = true;
                 }
             }

            // Generic fallback for any remaining meta values (domain-agnostic, relative/absolute)
            if (!$value_changed && is_string($updated_value)) {
                $uploads_path_quoted = preg_quote(rtrim($uploads_basepath, '/'), '/');
                $generic_pattern = '/((?:https?:)?\/\/[^"\'\)\s]+)?(' . $uploads_path_quoted . '\/[^"\'\)\s]+\/)' . preg_quote($old_base, '/') . '((?:-\d+x\d+)?\.\w+)/i';
                $meta_tmp = preg_replace($generic_pattern, '$1$2' . $new_base . '$3', $updated_value, -1, $meta_count);
                if ($meta_tmp !== null && $meta_count > 0) {
                    $updated_value = $meta_tmp;
                    $value_changed = true;
                }
            }
             
             // Update the meta if it has changed
             if ($value_changed && $updated_value !== $meta->meta_value) {
                 $wpdb->update(
                     $wpdb->postmeta,
                     array('meta_value' => $updated_value),
                     array('meta_id' => $meta->meta_id)
                 );
             }
         }
         
         // Clear any caches that might be holding old URLs
         if (function_exists('wp_cache_flush')) {
             wp_cache_flush();
         }
     }
    
    /**
     * Force refresh all content caches
     * This addresses persistent reference issues after renaming
     */
    private function force_refresh_content_caches() {
        global $wpdb;
        
        // Refresh post caches
        $posts_with_images = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%<img%' OR post_content LIKE '%wp-image-%' LIMIT 100");
        
        if (!empty($posts_with_images)) {
            foreach ($posts_with_images as $post_id) {
                clean_post_cache($post_id);
            }
        }
        
        // Flush object cache per specifiche chiavi, non tutto
        if (function_exists('wp_cache_delete_many')) {
            $keys = array('posts', 'post_meta');
            wp_cache_delete_many($keys);
        }
    }

    /**
     * Update compression backup paths after file rename to maintain compatibility
     * Supports ImgSEO compression and detects third-party compression plugins
     *
     * @param int    $attachment_id     Attachment ID
     * @param string $old_filename      Old filename (without path)
     * @param string $new_filename      New filename (without path)
     */
    private function update_compression_backup_paths($attachment_id, $old_filename, $new_filename) {
        // Handle ImgSEO compression system
        $this->handle_imgseo_compression_compatibility($attachment_id, $old_filename, $new_filename);

        // Handle third-party compression plugins
        $this->handle_third_party_compression_compatibility($attachment_id, $old_filename, $new_filename);
    }

    /**
     * Handle ImgSEO compression system compatibility
     */
    private function handle_imgseo_compression_compatibility($attachment_id, $old_filename, $new_filename) {
        $backup_path = get_post_meta($attachment_id, '_imgseo_backup_path', true);

        if (empty($backup_path)) {
            return; // No ImgSEO backup to update
        }

        if (file_exists($backup_path)) {
            // Backup file exists, metadata is still valid
            // No action needed - ImgSEO compression system uses attachment ID for backup tracking
            return;
        }

        // Clear the backup metadata since the file doesn't exist
        delete_post_meta($attachment_id, '_imgseo_backup_path');
        delete_post_meta($attachment_id, '_imgseo_backup_created');
        delete_post_meta($attachment_id, '_imgseo_backup_available');
        delete_post_meta($attachment_id, '_imgseo_compressed');
    }

    /**
     * Handle third-party compression plugins compatibility
     */
    private function handle_third_party_compression_compatibility($attachment_id, $old_filename, $new_filename) {
        // Common third-party compression plugin metadata patterns
        $compression_plugins = array(
            'shortpixel' => array(
                'backup_meta' => '_shortpixel_backup_path',
                'status_meta' => '_shortpixel_status',
                'compressed_meta' => '_shortpixel_compressed'
            ),
            'smush' => array(
                'backup_meta' => '_smush_backup_path',
                'status_meta' => '_smush_status',
                'compressed_meta' => '_smush_compressed'
            ),
            'tinypng' => array(
                'backup_meta' => '_tinypng_backup_path',
                'status_meta' => '_tinypng_status',
                'compressed_meta' => '_tinypng_compressed'
            ),
            'imagify' => array(
                'backup_meta' => '_imagify_backup_path',
                'status_meta' => '_imagify_status',
                'compressed_meta' => '_imagify_compressed'
            ),
            'optimole' => array(
                'backup_meta' => '_optimole_backup_path',
                'status_meta' => '_optimole_status',
                'compressed_meta' => '_optimole_compressed'
            )
        );

        foreach ($compression_plugins as $plugin_name => $meta_keys) {
            $this->update_plugin_backup_compatibility($attachment_id, $old_filename, $new_filename, $plugin_name, $meta_keys);
        }

        // Handle ShortPixel specifically (has different metadata structure)
        $this->handle_shortpixel_specific_compatibility($attachment_id, $old_filename, $new_filename);

        // Handle WP Smush specifically
        $this->handle_smush_specific_compatibility($attachment_id, $old_filename, $new_filename);
    }

    /**
     * Update backup compatibility for generic compression plugin
     */
    private function update_plugin_backup_compatibility($attachment_id, $old_filename, $new_filename, $plugin_name, $meta_keys) {
        $backup_path = get_post_meta($attachment_id, $meta_keys['backup_meta'], true);

        if (empty($backup_path)) {
            return; // No backup for this plugin
        }

        // Check if backup path needs updating (contains old filename)
        if (strpos($backup_path, $old_filename) !== false) {
            $new_backup_path = str_replace($old_filename, $new_filename, $backup_path);

            // Check if old backup exists and needs renaming
            if (file_exists($backup_path) && $backup_path !== $new_backup_path) {
                // Initialize WP_Filesystem if not already done
                global $wp_filesystem;
                if (!$wp_filesystem) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    WP_Filesystem();
                }
                
                // Try to rename the backup file using WP_Filesystem
                if ($wp_filesystem->move($backup_path, $new_backup_path)) {
                    // Update metadata with new path
                    update_post_meta($attachment_id, $meta_keys['backup_meta'], $new_backup_path);} else {
                    // Couldn't rename backup file, clear metadata
                    delete_post_meta($attachment_id, $meta_keys['backup_meta']);
                    if (isset($meta_keys['status_meta'])) {
                        delete_post_meta($attachment_id, $meta_keys['status_meta']);
                    }
                    if (isset($meta_keys['compressed_meta'])) {
                        delete_post_meta($attachment_id, $meta_keys['compressed_meta']);
                    }}
            }
        }
    }

    /**
     * Handle ShortPixel specific compatibility
     */
    private function handle_shortpixel_specific_compatibility($attachment_id, $old_filename, $new_filename) {
        // ShortPixel uses specific metadata keys
        $shortpixel_data = get_post_meta($attachment_id, '_shortpixel_status', true);

        if (!empty($shortpixel_data) && is_array($shortpixel_data)) {
            // ShortPixel stores backup info in status array
            if (isset($shortpixel_data['backup']) && !empty($shortpixel_data['backup'])) {
                $backup_path = $shortpixel_data['backup'];

                if (strpos($backup_path, $old_filename) !== false) {
                    $new_backup_path = str_replace($old_filename, $new_filename, $backup_path);

                    // Initialize WP_Filesystem if not already done
                    global $wp_filesystem;
                    if (!$wp_filesystem) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        WP_Filesystem();
                    }

                    if (file_exists($backup_path) && $wp_filesystem->move($backup_path, $new_backup_path)) {
                        $shortpixel_data['backup'] = $new_backup_path;
                        update_post_meta($attachment_id, '_shortpixel_status', $shortpixel_data);}
                }
            }
        }
    }

    /**
     * Handle WP Smush specific compatibility
     */
    private function handle_smush_specific_compatibility($attachment_id, $old_filename, $new_filename) {
        // Smush stores data differently
        $smush_data = get_post_meta($attachment_id, 'wp-smpro-smush-data', true);

        if (!empty($smush_data) && is_array($smush_data)) {
            $updated = false;

            // Check if any backup paths need updating
            if (isset($smush_data['backup_path']) && strpos($smush_data['backup_path'], $old_filename) !== false) {
                $old_backup = $smush_data['backup_path'];
                $new_backup = str_replace($old_filename, $new_filename, $old_backup);

                // Initialize WP_Filesystem if not already done
                global $wp_filesystem;
                if (!$wp_filesystem) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    WP_Filesystem();
                }

                if (file_exists($old_backup) && $wp_filesystem->move($old_backup, $new_backup)) {
                    $smush_data['backup_path'] = $new_backup;
                    $updated = true;
                }
            }

            if ($updated) {
                update_post_meta($attachment_id, 'wp-smpro-smush-data', $smush_data);
            }
        }
    }

    /**
     * Detect active compression plugins
     *
     * @return array List of detected compression plugin names
     */
    private function detect_active_compression_plugins() {
        $detected = array();

        // Check for common compression plugins by their classes/functions
        $plugin_checks = array(
            'shortpixel' => array('ShortPixelAPI', 'shortPixelInit'),
            'smush' => array('WP_Smush', 'wp_smush_init'),
            'tinypng' => array('Tiny_Plugin', 'tiny_compress_images_init'),
            'imagify' => array('Imagify_Plugin', 'imagify_init'),
            'optimole' => array('Optml_Main', 'optml_init'),
            'ewww' => array('EWWW_Image_Optimizer', 'ewww_image_optimizer_init'),
            'kraken' => array('Kraken_Plugin', 'kraken_init'),
            'compress-jpeg-png' => array('TinyCompress', 'tiny_compress_images'),
            'robin-image-optimizer' => array('RobinImageOptimizer', 'rio_init')
        );

        foreach ($plugin_checks as $plugin_name => $identifiers) {
            foreach ($identifiers as $identifier) {
                if (class_exists($identifier) || function_exists($identifier)) {
                    $detected[] = $plugin_name;
                    break; // Found this plugin, no need to check other identifiers
                }
            }
        }

        // Check for active plugins by plugin file paths
        if (function_exists('is_plugin_active')) {
            $plugin_files = array(
                'shortpixel-image-optimiser/wp-shortpixel.php' => 'shortpixel',
                'wp-smushit/wp-smush.php' => 'smush',
                'tiny-compress-images/tiny-compress-images.php' => 'tinypng',
                'imagify/imagify.php' => 'imagify',
                'optimole-wp/optimole-wp.php' => 'optimole',
                'ewww-image-optimizer/ewww-image-optimizer.php' => 'ewww',
                'kraken-image-optimizer/kraken.php' => 'kraken'
            );

            foreach ($plugin_files as $plugin_file => $plugin_name) {
                if (is_plugin_active($plugin_file) && !in_array($plugin_name, $detected)) {
                    $detected[] = $plugin_name;
                }
            }
        }

        return array_unique($detected);
    }

    /**
     * Mark attachment for auto-rename - called AFTER thumbnails are generated
     * Phase 1: This filter only MARKS the attachment, does NOT rename
     * The actual rename happens in execute_auto_rename_after_save()
     *
     * Why this approach?
     * - Renaming during filter wp_generate_attachment_metadata causes timing conflicts
     * - WordPress may overwrite our metadata changes with original values
     * - Better to wait until metadata is saved, then rename
     *
     * @param array $metadata Attachment metadata (just generated)
     * @param int $attachment_id Attachment ID
     * @return array Metadata (UNCHANGED - we don't modify here)
     */
    public function mark_for_auto_rename($metadata, $attachment_id) {
        // DEBUG: Log SEMPRE quando questo filter viene chiamato
        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('=== FILTER wp_generate_attachment_metadata CALLED ===');
            imgseo_debug_log('Attachment ID: ' . $attachment_id);
            imgseo_debug_log('Metadata received: ' . print_r($metadata, true));
            imgseo_debug_log('Metadata is_array: ' . (is_array($metadata) ? 'YES' : 'NO'));
            imgseo_debug_log('Metadata empty: ' . (empty($metadata) ? 'YES' : 'NO'));
            if (is_array($metadata)) {
                imgseo_debug_log('Metadata has file key: ' . (isset($metadata['file']) ? 'YES' : 'NO'));
                imgseo_debug_log('Metadata has sizes key: ' . (isset($metadata['sizes']) ? 'YES (' . count($metadata['sizes']) . ')' : 'NO'));
            }
        }

        // Verifica se auto-rename è abilitato
        if (!get_option('imgseo_auto_rename_on_upload', 0)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename NOT enabled in settings - returning metadata unchanged');
            }
            return $metadata;
        }

        // Verifica che sia un'immagine
        if (!wp_attachment_is_image($attachment_id)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Attachment is NOT an image - returning metadata unchanged');
            }
            return $metadata;
        }

        // Verifica che i metadata siano validi
        if (empty($metadata) || !isset($metadata['file'])) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('WARNING: Metadata EMPTY or missing file key - WordPress may have failed to generate thumbnails!');
                imgseo_debug_log('This is NOT our plugin fault - WordPress thumbnail generation failed');
            }
            return $metadata;
        }

        // Evita elaborazioni multiple
        if (get_post_meta($attachment_id, '_imgseo_auto_rename_pending', true)) {
            return $metadata;
        }

        // Verifica che il file esista
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        // SIMPLIFIED APPROACH: Rename ALL uploaded images, no pattern matching
        // If auto-rename is enabled, we rename every new image on upload
        // This is simpler, more reliable, and covers all cases
        $current_filename = basename($file);

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Auto-rename: Will rename uploaded image: ' . $current_filename);
        }

        // MARCA l'attachment per auto-rename (sarà processato da execute_auto_rename_after_save)
        update_post_meta($attachment_id, '_imgseo_auto_rename_pending', time());

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('[Phase 1] Marked attachment ' . $attachment_id . ' for auto-rename. File: ' . $metadata['file'] . ', Sizes: ' . count($metadata['sizes'] ?? []));
            imgseo_debug_log('[Phase 1] Returning metadata UNCHANGED to WordPress');
        }

        // CRITICO: Restituisci i metadata ORIGINALI senza modifiche
        // WordPress li salverà, poi execute_auto_rename_after_save() farà il rename
        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('=== FILTER wp_generate_attachment_metadata RETURNING ===');
            imgseo_debug_log('Returning same metadata structure with ' . count($metadata['sizes'] ?? []) . ' sizes');
        }
        return $metadata;
    }

    /**
     * Execute auto-rename AFTER WordPress has saved the metadata
     * Phase 2: This action executes the actual rename
     * Called by updated_post_meta hook
     *
     * @param int $meta_id Meta ID
     * @param int $object_id Post/Attachment ID
     * @param string $meta_key Meta key being updated
     * @param mixed $_meta_value Meta value (we don't use this, we re-fetch)
     */
    public function execute_auto_rename_after_save($meta_id, $object_id, $meta_key, $_meta_value) {
        // Only act on attachment metadata updates
        if ($meta_key !== '_wp_attachment_metadata') {
            return;
        }

        // Check if this attachment is marked for auto-rename
        $pending = get_post_meta($object_id, '_imgseo_auto_rename_pending', true);
        if (!$pending) {
            return; // Not marked for rename
        }

        // Verify it's an image attachment
        if (!wp_attachment_is_image($object_id)) {
            delete_post_meta($object_id, '_imgseo_auto_rename_pending');
            return;
        }

        // Evita ricorsione: se stiamo già rinominando, skip
        if (get_transient('imgseo_auto_rename_executing_' . $object_id)) {
            return;
        }

        // IMPORTANTE: WordPress 5.3+ salva metadata MULTIPLE volte durante thumbnail generation
        // Aspettiamo che TUTTE le thumbnails siano state generate
        $metadata = wp_get_attachment_metadata($object_id);
        $file = get_attached_file($object_id);

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            $thumb_count = isset($metadata['sizes']) ? count($metadata['sizes']) : 0;
            imgseo_debug_log('[Phase 2] updated_post_meta triggered for attachment ' . $object_id . '. Thumbnails in metadata: ' . $thumb_count);
            imgseo_debug_log('[Phase 2] File path: ' . $file);
            if (isset($metadata['sizes'])) {
                imgseo_debug_log('[Phase 2] Thumbnail sizes: ' . implode(', ', array_keys($metadata['sizes'])));
            }
        }

        // Verifica che le thumbnails esistano fisicamente sul disco
        $all_thumbs_exist = true;
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $upload_dir = wp_upload_dir();
            $file_dir = dirname($file);

            foreach ($metadata['sizes'] as $size_name => $size_info) {
                $thumb_file = $file_dir . '/' . $size_info['file'];
                if (!file_exists($thumb_file)) {
                    $all_thumbs_exist = false;
                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('[Phase 2] Thumbnail does not exist yet: ' . $size_info['file']);
                    }
                }
            }
        }

        // Se non tutte le thumbnails esistono ancora, aspetta il prossimo update
        if (!$all_thumbs_exist) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('[Phase 2] Not all thumbnails exist yet, waiting for next metadata update');
            }
            return; // Aspetta il prossimo save
        }

        // Marca come in esecuzione
        set_transient('imgseo_auto_rename_executing_' . $object_id, true, 60);

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('[Phase 2] All thumbnails exist! Executing auto-rename for attachment ' . $object_id);
        }

        // Rimuovi il flag pending PRIMA di eseguire perform_auto_rename
        // (perform_auto_rename imposta altri marker)
        delete_post_meta($object_id, '_imgseo_auto_rename_pending');

        // COMPATIBILITÀ: Se ci sono plugin di compressione, usa delayed rename
        $compression_plugins = $this->detect_active_compression_plugins();
        if (!empty($compression_plugins)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('[Phase 2] Compression plugins detected, scheduling delayed rename (5 sec)');
            }
            wp_schedule_single_event(time() + 5, 'imgseo_delayed_auto_rename', array($object_id));
            delete_transient('imgseo_auto_rename_executing_' . $object_id);
            return;
        }

        // Esegui il rename IMMEDIATAMENTE
        $this->perform_auto_rename($object_id);

        // Rimuovi il flag di esecuzione
        delete_transient('imgseo_auto_rename_executing_' . $object_id);

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('[Phase 2] Auto-rename execution completed for attachment ' . $object_id);
        }
    }

    /**
     * DEPRECATED: Vecchio metodo con hook add_attachment
     * Mantenuto temporaneamente per riferimento, ma non più utilizzato
     * Il nuovo sistema usa mark_for_auto_rename() + execute_auto_rename_after_save()
     *
     * @deprecated 2.2.0 Use mark_for_auto_rename() and execute_auto_rename_after_save() instead
     * @param int $attachment_id The attachment ID
     */
    public function auto_rename_on_upload_complete($attachment_id) {
        // Verifica se auto-rename è abilitato
        if (!get_option('imgseo_auto_rename_on_upload', 0)) {
            return; // Feature disabled
        }

        // Verifica che sia un'immagine
        if (!wp_attachment_is_image($attachment_id)) {
            return; // Not an image, skip
        }

        // Evita elaborazioni multiple per lo stesso attachment
        // Usa un transient invece di static array per funzionare anche tra richieste diverse
        $processing_key = 'imgseo_auto_rename_processing_' . $attachment_id;
        if (get_transient($processing_key)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename already processing or completed for attachment ID: ' . $attachment_id);
            }
            return; // Already processing or completed
        }

        // Marca come in elaborazione (dura 5 minuti)
        set_transient($processing_key, time(), 300);

        // COMPATIBILITÀ: Rileva plugin di compressione attivi
        $compression_plugins = $this->detect_active_compression_plugins();

        // Se ci sono plugin di compressione attivi, usa un delay per permettere
        // loro di completare tutte le operazioni prima del rename
        if (!empty($compression_plugins)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Compression plugins detected: ' . implode(', ', $compression_plugins));
                imgseo_debug_log('Scheduling delayed auto-rename for attachment ID: ' . $attachment_id);
            }
            // Schedule rename dopo 5 secondi per sicurezza
            wp_schedule_single_event(time() + 5, 'imgseo_delayed_auto_rename', array($attachment_id));
            return;
        }

        // Esegui il rename immediatamente (nessun plugin di compressione attivo)
        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('No compression plugins detected, performing immediate auto-rename for attachment ID: ' . $attachment_id);
        }
        $this->perform_auto_rename($attachment_id);

        // Marca come completato
        $completed_key = 'imgseo_auto_rename_completed_' . $attachment_id;
        set_transient($completed_key, time(), 3600);
    }

    /**
     * Perform the actual auto-rename logic
     * Separated into its own method to be called both immediately and delayed
     *
     * @param int $attachment_id The attachment ID
     */
    private function perform_auto_rename($attachment_id) {
        try {
            // CRITICO: Marca come "in elaborazione" IMMEDIATAMENTE per evitare esecuzioni multiple
            // Questo deve essere fatto PRIMA di qualsiasi altra operazione
            $rename_marker = get_post_meta($attachment_id, '_imgseo_auto_renamed', true);
            if ($rename_marker) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('Auto-rename skipped - attachment ID ' . $attachment_id . ' already has rename marker');
                }
                return;
            }

            // Imposta il marker SUBITO (anche prima di generare il nome)
            // Questo previene esecuzioni multiple anche se wp_generate_attachment_metadata trigger altri hook
            update_post_meta($attachment_id, '_imgseo_auto_renamed', 'in_progress');
            update_post_meta($attachment_id, '_imgseo_auto_rename_started', time());

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename marker set for attachment ID: ' . $attachment_id);
            }

            // NUOVO: Verifica che il file esista prima di procedere
            $current_file = get_attached_file($attachment_id);
            if (!$current_file || !file_exists($current_file)) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('Auto-rename skipped - file does not exist for attachment ID: ' . $attachment_id);
                }
                // Rimuovi marker per permettere retry futuro
                delete_post_meta($attachment_id, '_imgseo_auto_renamed');
                delete_post_meta($attachment_id, '_imgseo_auto_rename_started');
                return;
            }

            // NUOVO: Verifica che i metadata siano pronti (sicurezza aggiuntiva)
            $metadata = wp_get_attachment_metadata($attachment_id);
            if (empty($metadata) || !isset($metadata['file'])) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('Auto-rename skipped - metadata not ready for attachment ID: ' . $attachment_id);
                }
                // Rimuovi marker per permettere retry futuro
                delete_post_meta($attachment_id, '_imgseo_auto_renamed');
                delete_post_meta($attachment_id, '_imgseo_auto_rename_started');
                return;
            }

            // Get current filename for logging
            $current_filename = basename($current_file);

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename: Processing file: ' . $current_filename);
            }

            // Carica AI Generator per generare il nuovo filename
            if (!class_exists('Renamer_AI_Generator')) {
                require_once plugin_dir_path(__FILE__) . 'class-renamer-ai-generator.php';
            }
            $ai_generator = Renamer_AI_Generator::get_instance();

            // Genera nuovo filename usando AI (stesse regole del bulk/manuale)
            $new_filename = $ai_generator->generate_filename($attachment_id);

            // Se errore (crediti insufficienti, API down, etc.), cleanup e skip
            if (is_wp_error($new_filename)) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('Auto-rename FAILED - AI generation error for attachment ' . $attachment_id . ': ' . $new_filename->get_error_message());
                }
                // CRITICO: Pulisci tutti i marker quando fallisce
                delete_post_meta($attachment_id, '_imgseo_auto_renamed');
                delete_post_meta($attachment_id, '_imgseo_auto_rename_started');
                delete_post_meta($attachment_id, '_imgseo_auto_rename_pending');
                return;
            }

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename AI generated filename for attachment ' . $attachment_id . ': "' . $new_filename . '"');
            }

            // Usa ESATTAMENTE le stesse opzioni del rename manuale
            $rename_options = array();

            // Apply settings from the settings manager (come nel rename manuale)
            if ($this->settings_manager) {
                $rename_options['remove_accents'] = $this->settings_manager->is_enabled('remove_accents', true);
                $rename_options['lowercase'] = $this->settings_manager->is_enabled('lowercase', true);
                $rename_options['handle_duplicates'] = $this->settings_manager->get_setting('handle_duplicates', 'increment');
            } else {
                // Fallback se il settings manager non è ancora disponibile
                $rename_options['remove_accents'] = (bool) get_option('imgseo_renamer_remove_accents', 1);
                $rename_options['lowercase'] = (bool) get_option('imgseo_renamer_lowercase', 1);
                $rename_options['handle_duplicates'] = get_option('imgseo_renamer_handle_duplicates', 'increment');
            }

            // Enable sanitization (come nel rename manuale)
            $rename_options['sanitize'] = true;

            // Abilita sempre l'aggiornamento dei riferimenti (come nel rename manuale)
            $rename_options['update_references'] = true;

            // Esegui rename usando il sistema esistente
            // NOTA: Non chiamiamo più wp_generate_attachment_metadata() quindi non servono unhook
            $result = $this->rename_image($attachment_id, $new_filename, $rename_options);

            // Se rename fallisce, termina silenziosamente
            if (is_wp_error($result)) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('Auto-rename failed for attachment ID ' . $attachment_id . ': ' . $result->get_error_message());
                }

                // IMPORTANTE: Rimuovi il marker "in_progress" per permettere un nuovo tentativo in futuro
                delete_post_meta($attachment_id, '_imgseo_auto_renamed');
                delete_post_meta($attachment_id, '_imgseo_auto_rename_started');

                return;
            }

            // VERIFICA POST-RENAME: Assicurati che il file rinominato esista
            $renamed_file = get_attached_file($attachment_id);
            if (!$renamed_file || !file_exists($renamed_file)) {
                if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                    imgseo_debug_log('CRITICAL ERROR: Auto-rename succeeded but file does not exist: ' . $renamed_file);
                }
                // Rollback? Per ora logghiamo
                return;
            }

            // Verifica che le thumbnails esistano
            $metadata_after = wp_get_attachment_metadata($attachment_id);
            $missing_thumbs = 0;
            if (isset($metadata_after['sizes'])) {
                $file_dir = dirname($renamed_file);
                foreach ($metadata_after['sizes'] as $size_name => $size_info) {
                    $thumb_path = $file_dir . '/' . $size_info['file'];
                    if (!file_exists($thumb_path)) {
                        $missing_thumbs++;
                        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                            imgseo_debug_log('WARNING: Thumbnail missing after rename: ' . $size_info['file']);
                        }
                    }
                }
            }

            // Marca l'immagine come rinominata automaticamente con successo
            update_post_meta($attachment_id, '_imgseo_auto_renamed', time());
            update_post_meta($attachment_id, '_imgseo_auto_rename_completed', time());

            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                $thumb_count = isset($metadata_after['sizes']) ? count($metadata_after['sizes']) : 0;
                imgseo_debug_log('Auto-rename completed successfully for attachment ID: ' . $attachment_id . ' → ' . $new_filename);
                imgseo_debug_log('Post-rename verification: Main file exists, ' . $thumb_count . ' thumbnails in metadata, ' . $missing_thumbs . ' missing');
            }

        } catch (Exception $e) {
            // In caso di eccezione, termina silenziosamente
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Auto-rename exception for attachment ID ' . $attachment_id . ': ' . $e->getMessage());
            }

            // IMPORTANTE: Rimuovi il marker per permettere un nuovo tentativo
            delete_post_meta($attachment_id, '_imgseo_auto_renamed');
            delete_post_meta($attachment_id, '_imgseo_auto_rename_started');

            return;
        }
    }

    /**
     * Handle delayed auto-rename (triggered by wp_schedule_single_event)
     * Used when compression plugins are active to avoid conflicts
     *
     * @param int $attachment_id The attachment ID
     */
    public function handle_delayed_auto_rename($attachment_id) {
        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Delayed auto-rename triggered for attachment ID: ' . $attachment_id);
        }

        // Verifica che sia ancora un'immagine valida
        if (!wp_attachment_is_image($attachment_id)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Delayed auto-rename skipped - attachment ID ' . $attachment_id . ' is not an image');
            }
            return;
        }

        // PROTEZIONE 1: Controlla il marker permanente nel database
        $rename_marker = get_post_meta($attachment_id, '_imgseo_auto_renamed', true);
        if ($rename_marker) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Delayed auto-rename skipped - attachment ID ' . $attachment_id . ' already has rename marker: ' . $rename_marker);
            }
            return;
        }

        // PROTEZIONE 2: Controlla il transient
        $completed_key = 'imgseo_auto_rename_completed_' . $attachment_id;
        if (get_transient($completed_key)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Delayed auto-rename skipped - transient already set for attachment ID: ' . $attachment_id);
            }
            return;
        }

        // NUOVO: Verifica che il file esista
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Delayed auto-rename skipped - file does not exist for attachment ID: ' . $attachment_id);
            }
            return;
        }

        // NUOVO: Verifica che i metadata siano pronti (anche dopo 5 secondi potrebbe non esserlo in casi edge)
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (empty($metadata) || !isset($metadata['file'])) {
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('Delayed auto-rename skipped - metadata still not ready for attachment ID: ' . $attachment_id);
            }
            return;
        }

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Executing delayed auto-rename for attachment ID: ' . $attachment_id);
            imgseo_debug_log('Metadata ready - File: ' . $metadata['file'] . ', Sizes: ' . count($metadata['sizes'] ?? []));
        }

        // Esegui il rename
        $this->perform_auto_rename($attachment_id);

        // Marca come completato (dura 1 ora)
        set_transient($completed_key, time(), 3600);

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Delayed auto-rename completed for attachment ID: ' . $attachment_id);
        }
    }

    /**
     * Blocca temporaneamente tutti i plugin di ottimizzazione immagini comuni
     * per evitare che interferiscano durante il rename (resize, compress, ecc.)
     *
     * @param int $attachment_id ID dell'attachment
     * @return array Array con gli hook bloccati per poterli ripristinare
     */
    private function block_external_optimization_hooks($attachment_id) {
        // Check if blocking is enabled in settings (disabled by default)
        $block_enabled = (bool) get_option('imgseo_renamer_block_optimization_plugins', 0);

        if (!$block_enabled) {
            // Blocking is disabled, return empty array
            if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                imgseo_debug_log('External optimization hooks blocking is DISABLED in settings');
            }
            return array();
        }

        global $wp_filter;

        $blocked_hooks = array();

        // Imposta flag globale per segnalare che siamo in modalità rename
        // Altri plugin possono controllare questo flag
        if (!defined('IMGSEO_RENAMING_IN_PROGRESS')) {
            define('IMGSEO_RENAMING_IN_PROGRESS', true);
        }
        set_transient('imgseo_renaming_' . $attachment_id, true, 300); // 5 minuti

        // Lista dei hook più comuni usati dai plugin di ottimizzazione
        $hooks_to_block = array(
            // Hook principale di WordPress per i metadata
            'wp_update_attachment_metadata' => 10,
            'wp_generate_attachment_metadata' => 10,

            // ShortPixel Image Optimizer
            'shortpixel_image_optimised' => 10,
            'shortpixel/image/optimised' => 10,
            'shortpixel_after_restore' => 10,

            // Smush (WP Smush)
            'wp_smush_image_optimised' => 10,
            'smush_image_optimised' => 10,

            // Imagify
            'imagify_optimize_attachment' => 10,
            'imagify_after_optimize_attachment' => 10,

            // EWWW Image Optimizer
            'ewww_image_optimizer_optimized' => 10,

            // Optimole
            'optml_after_image_optimization' => 10,

            // Compress JPEG & PNG images (TinyPNG)
            'tiny_compress_after_upload' => 10,

            // Imsanity (auto-resize)
            'imsanity_attachment_uploaded' => 10,

            // Regenerate Thumbnails
            'regenerate_thumbs_after_regenerate' => 10,

            // WebP conversion plugins
            'webp_uploading' => 10,
            'webpc_convert_attachment' => 10,
        );

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Blocking external optimization hooks for attachment: ' . $attachment_id);
        }

        // Blocca tutti gli hook nella lista
        foreach ($hooks_to_block as $hook_name => $priority) {
            if (isset($wp_filter[$hook_name]) && isset($wp_filter[$hook_name]->callbacks[$priority])) {
                // Salva i callback per poterli ripristinare
                $blocked_hooks[$hook_name][$priority] = $wp_filter[$hook_name]->callbacks[$priority];

                // Rimuovi tutti i callback per questa priorità
                foreach ($wp_filter[$hook_name]->callbacks[$priority] as $callback_key => $callback) {
                    remove_filter($hook_name, $callback['function'], $priority);

                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('Blocked hook: ' . $hook_name . ' (priority ' . $priority . ')');
                    }
                }
            }
        }

        return $blocked_hooks;
    }

    /**
     * Ripristina gli hook di ottimizzazione precedentemente bloccati
     *
     * @param array $blocked_hooks Array degli hook bloccati da ripristinare
     */
    private function restore_external_optimization_hooks($blocked_hooks) {
        if (empty($blocked_hooks)) {
            return;
        }

        if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
            imgseo_debug_log('Restoring external optimization hooks');
        }

        // Ripristina tutti gli hook bloccati
        foreach ($blocked_hooks as $hook_name => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback_key => $callback) {
                    add_filter($hook_name, $callback['function'], $priority, $callback['accepted_args']);

                    if (defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE) {
                        imgseo_debug_log('Restored hook: ' . $hook_name . ' (priority ' . $priority . ')');
                    }
                }
            }
        }
    }
}


