<?php
/**
 * Class Renamer_Batch_Processor
 * Gestisce operazioni di rinomina in blocco
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_Batch_Processor {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Controller delle rinomina
     */
    private $controller;
    
    /**
     * File processor
     */
    private $file_processor;
    
    /**
     * Pattern manager
     */
    private $pattern_manager;
    
    /**
     * Logs manager
     */
    private $logs_manager;
    
    /**
     * Inizializza il processore
     */
    private function __construct() {
        // Ottieni le istanze degli altri componenti necessari
        $this->controller = Renamer_Controller::get_instance();
        $this->file_processor = Renamer_File_Processor::get_instance();
        $this->pattern_manager = Renamer_Pattern_Manager::get_instance();
        $this->logs_manager = Renamer_Logs_Manager::get_instance();
        
        // Registra handler AJAX
        add_action('wp_ajax_imgseo_batch_rename', array($this, 'ajax_batch_rename'));
        add_action('wp_ajax_imgseo_prepare_batch_rename', array($this, 'ajax_prepare_batch_rename'));
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
     * Ottiene i dati per la rinomina in batch da un array di attachment ID
     * 
     * @param array $attachment_ids Array di ID degli allegati
     * @param array $options Opzioni di rinomina
     * @return array Dati preparati per la rinomina in batch
     */
    public function prepare_batch_rename($attachment_ids, $options = array()) {
        $result = array(
            'items' => array(),
            'success' => array(),
            'errors' => array(),
        );
        
        // Resetta il contatore sequenziale se necessario
        if (!empty($options['reset_sequence'])) {
            $this->pattern_manager->reset_sequence();
        }
        
        // Se è specificata una sequenza di partenza, imposta il contatore
        if (isset($options['sequence_start']) && is_numeric($options['sequence_start'])) {
            $this->pattern_manager->set_sequence_start(intval($options['sequence_start']));
        }
        
        foreach ($attachment_ids as $attachment_id) {
            $item = $this->prepare_single_rename($attachment_id, $options);
            if ($item) {
                $result['items'][] = $item;
            } else {
                $result['errors'][] = array(
                    'id' => $attachment_id,
                    'message' => __('Unable to prepare rename for this attachment', 'imgseo-ai-alt-text-generator')
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Prepara i dati per la rinomina di un singolo attachment
     * 
     * @param int $attachment_id ID dell'allegato
     * @param array $options Opzioni di rinomina
     * @return array|bool Prepared data or false on error
     */
    private function prepare_single_rename($attachment_id, $options = array()) {
        // Verifica se l'allegato esiste
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }
        
        // Ottieni il file allegato
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return false;
        }
        
        // Estrai informazioni sul file
        $pathinfo = pathinfo($file);
        $current_filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        
        // Ottieni i dati dell'immagine
        $post_title = $attachment->post_title;
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        
        // Ottieni dati per i pattern
        $context = array(
            'original_filename' => $current_filename,
            'post_title' => $post_title,
            'alt_text' => $alt_text
        );
        
        // Genera il nuovo nome file in base ai pattern se specificato
        if (!empty($options['pattern']) && !empty($options['use_patterns'])) {
            $new_name = $this->pattern_manager->apply_patterns($options['pattern'], $attachment_id, $context);
        } else {
            // Altrimenti usa il nome fornito o il titolo
            $new_name = !empty($options['new_name']) ? $options['new_name'] : $post_title;
        }
        
        // Applica sanitizzazione avanzata se richiesto
        if (!empty($options['sanitize'])) {
            $sanitize_options = array();
            
            if (isset($options['lowercase'])) {
                $sanitize_options['lowercase'] = (bool) $options['lowercase'];
            }
            
            if (isset($options['remove_accents'])) {
                $sanitize_options['remove_accents'] = (bool) $options['remove_accents'];
            }
            
            $new_name = $this->pattern_manager->sanitize_filename($new_name, $sanitize_options);
        }
        
        // Se il nome è vuoto dopo la sanitizzazione, usa un fallback
        if (empty($new_name)) {
            $new_name = 'image-' . $attachment_id;
        }
        
        return array(
            'id' => $attachment_id,
            'current_filename' => $current_filename,
            'extension' => $extension,
            'new_filename' => $new_name,
            'thumbnail_url' => $thumbnail_url,
            'post_title' => $post_title,
            'alt_text' => $alt_text
        );
    }
    
    /**
     * Esegue la rinomina in batch
     * 
     * @param array $items Array di item da rinominare
     * @param array $options Opzioni di rinomina
     * @return array Risultati della rinomina
     */
    public function execute_batch_rename($items, $options = array()) {
        $results = array(
            'success' => array(),
            'errors' => array(),
        );
        
        foreach ($items as $item) {
            // Configura le opzioni per la rinomina
            $rename_options = array_merge($options, array(
                'update_metadata' => !empty($options['update_metadata']),
                'update_title' => !empty($options['update_title']),
                'sanitize' => !empty($options['sanitize']),
                'handle_duplicates' => isset($options['handle_duplicates']) ? $options['handle_duplicates'] : 'increment'
            ));
            
            // Esegui la rinomina
            $result = $this->file_processor->rename_image($item['id'], $item['new_filename'], $rename_options);
            
            if (is_wp_error($result)) {
                $results['errors'][$item['id']] = array(
                    'id' => $item['id'],
                    'message' => $result->get_error_message(),
                );
            } else {
                $results['success'][$item['id']] = array(
                    'id' => $item['id'],
                    'old_filename' => $result['old_filename'],
                    'new_filename' => $result['new_filename'],
                    'new_url' => $result['new_url'],
                );
            }
        }
        
        // Log batch operation
        if (!empty($results['success']) || !empty($results['errors'])) {
            $batch_id = 'batch_' . uniqid();
            $this->logs_manager->log_batch_operation($results, $batch_id, $options);
        }
        
        return $results;
    }
    
    /**
     * AJAX handler per preparare la rinomina in batch
     */
    public function ajax_prepare_batch_rename() {
        check_ajax_referer('imgseo_renamer_nonce', 'security');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')));
        }
        
        $attachment_ids = isset($_POST['attachment_ids']) ? array_map('intval', wp_unslash($_POST['attachment_ids'])) : array();
        $options = isset($_POST['options']) ? wp_unslash($_POST['options']) : array();
        
        if (empty($attachment_ids)) {
            wp_send_json_error(array('message' => __('No images selected', 'imgseo-ai-alt-text-generator')));
        }
        
        // Prepara la rinomina in batch
        $batch_data = $this->prepare_batch_rename($attachment_ids, $options);
        
        wp_send_json_success(array(
            'batch_data' => $batch_data,
            'message' => sprintf(
                // translators: %d is the number of items prepared for renaming
                __('Prepared %d items for renaming', 'imgseo-ai-alt-text-generator'),
                count($batch_data['items'])
            ),
        ));
    }
    
    /**
     * AJAX handler per eseguire la rinomina in batch
     */
    public function ajax_batch_rename() {
        check_ajax_referer('imgseo_renamer_nonce', 'security');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')));
        }
        
        $items = isset($_POST['items']) ? wp_unslash($_POST['items']) : array();
        $options = isset($_POST['options']) ? wp_unslash($_POST['options']) : array();
        
        if (empty($items)) {
            wp_send_json_error(array('message' => __('No items to rename', 'imgseo-ai-alt-text-generator')));
        }
        
        // Esegui la rinomina in batch
        $results = $this->execute_batch_rename($items, $options);
        
        wp_send_json_success(array(
            'results' => $results,
            'message' => sprintf(
                // translators: %1$d is the total number of items processed, %2$d is the number of successful renames, %3$d is the number of errors
                __('Processed %1$d items: %2$d renamed successfully, %3$d errors', 'imgseo-ai-alt-text-generator'),
                count($items),
                count($results['success']),
                count($results['errors'])
            ),
        ));
    }
}
