<?php
/**
 * Class ImgSEO_Elementor_Integration
 * Gestisce l'integrazione con Elementor
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class ImgSEO_Elementor_Integration implements Renamer_Integration_Interface {
    
    /**
     * Updates image references in Elementor
     * 
     * @param array $old_urls Vecchi URL
     * @param array $new_urls Nuovi URL
     * @param int $attachment_id ID dell'allegato
     * @return array|bool Update result
     */
    public function update_references($old_urls, $new_urls, $attachment_id) {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            return array(
                'status' => false,
                'updated' => 0,
                'message' => __('Elementor is not active', 'imgseo-ai-alt-text-generator')
            );
        }
        
        global $wpdb;
        
        // Ottieni tutti i post che potrebbero contenere dati Elementor
        $elementor_posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} 
                WHERE meta_key = %s 
                AND meta_value LIKE %s",
                '_elementor_data',
                '%' . $wpdb->esc_like('"url":"' . $old_urls[0]) . '%'
            )
        );
        
        $updated_count = 0;
        
        foreach ($elementor_posts as $e_post) {
            $post_id = $e_post->post_id;
            $data = $e_post->meta_value;
            
            $modified = false;
            
            // Sostituisci tutti gli URL vecchi con i nuovi
            foreach ($old_urls as $index => $old_url) {
                // Gestisci sia url che placeholder interni a Elementor
                $old_patterns = array(
                    '"url":"' . $old_url . '"',
                    '"url":"' . str_replace('/', '\/', $old_url) . '"'
                );
                
                $new_patterns = array(
                    '"url":"' . $new_urls[$index] . '"',
                    '"url":"' . str_replace('/', '\/', $new_urls[$index]) . '"'
                );
                
                $new_data = str_replace($old_patterns, $new_patterns, $data);
                
                if ($new_data !== $data) {
                    $data = $new_data;
                    $modified = true;
                }
            }
            
            // Update post meta if changes were made
            if ($modified) {
                // Update meta data
                $wpdb->update(
                    $wpdb->postmeta, 
                    array('meta_value' => $data),
                    array('post_id' => $post_id, 'meta_key' => '_elementor_data')
                );
                
                // Force CSS regeneration
                delete_post_meta($post_id, '_elementor_css');
                
                $updated_count++;
            }
        }
        
        return array(
            'status' => true,
            'updated' => $updated_count,
            // translators: %d is the number of Elementor posts updated
            'message' => sprintf(__('Updated %d Elementor posts', 'imgseo-ai-alt-text-generator'), $updated_count)
        );
    }
}
