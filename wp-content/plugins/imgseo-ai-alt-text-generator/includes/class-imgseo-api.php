<?php
/**
 * Class ImgSEO_API
 * Manages all interactions with the ImgSEO API for alt text generation
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// FIX: Add a check to avoid class redeclarations
if (!class_exists('ImgSEO_API')) {

class ImgSEO_API {
    /**
   /**
     * API Endpoint
     */
    const API_ENDPOINT = 'https://api.imgseo.net';
    
    /**
     * ImgSEO Token
     *
     * @var string
     */
    private $api_key;
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Ottiene la chiave API corrente
     * 
     * @return string La chiave API
     */
    public function get_api_key() {
        return $this->api_key;
    }
    
    /**
     * Constructor
     *
     * @param string $api_key API Key (optional, otherwise taken from options)
     */
    private function __construct($api_key = null) {
        if ($api_key === null) {
            $this->api_key = get_option('imgseo_api_key', '');
        } else {
            $this->api_key = $api_key;
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
     * Check if API token is currently marked as invalid
     *
     * @return bool True if token is invalid
     */
    private function is_token_invalid() {
        return get_transient('imgseo_invalid_api_token') === true;
    }

    /**
     * Mark API token as invalid and prevent further API calls
     *
     * @param int $response_code HTTP response code
     */
    private function mark_token_invalid($response_code) {
        // Set flag for 24 hours
        set_transient('imgseo_invalid_api_token', true, 24 * HOUR_IN_SECONDS);
        set_transient('imgseo_invalid_token_code', $response_code, 24 * HOUR_IN_SECONDS);

        // Clear API verification status
        delete_option('imgseo_api_verified');

        // Delete the invalid token from database to prevent periodic validation requests
        delete_option('imgseo_api_key');

        // Log the error
        error_log(sprintf(
            '[ImgSEO] API token marked as invalid (HTTP %d) and deleted from database. API calls will be blocked for 24 hours or until a new valid token is provided.',
            $response_code
        ));
    }

    /**
     * Clear invalid token flag (called when user updates API key)
     */
    public function clear_invalid_token_flag() {
        delete_transient('imgseo_invalid_api_token');
        delete_transient('imgseo_invalid_token_code');
    }

    /**
     * Check if response indicates authentication failure
     *
     * @param int $response_code HTTP response code
     * @return bool True if auth failure (401, 403)
     */
    private function is_auth_failure($response_code) {
        return in_array($response_code, [401, 403], true);
    }

    /**
     * Verifies if the API key is valid via AJAX
     */
    public function ajax_verify_api_key() {
        check_ajax_referer('imgseo_settings_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Security: Rate limiting - max 5 attempts per 10 minutes per user
        $user_id = get_current_user_id();
        $rate_key = 'imgseo_api_verify_attempts_' . $user_id;
        $attempts = get_transient($rate_key) ?: 0;
        
        if ($attempts >= 5) {
            wp_send_json_error(['message' => __('Too many verification attempts. Please wait 10 minutes.', 'imgseo-ai-alt-text-generator')]);
        }
        
        set_transient($rate_key, $attempts + 1, 600); // 10 minutes
        
        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        
        if (empty($api_key)) {
            wp_send_json_error(['message' => __('ImgSEO Token not provided', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Security: Validate API key format (basic validation)
        if (strlen($api_key) < 10 || strlen($api_key) > 100) {
            wp_send_json_error(['message' => __('Invalid ImgSEO Token format', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Security: Check for malicious patterns
        if (preg_match('/[<>"\']/', $api_key)) {
            wp_send_json_error(['message' => __('Invalid ImgSEO Token format', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Verify the API key - create a new instance with the specified API key
        $api = new self($api_key);  // We use self to maintain access to the private constructor within the class
        $account_details = $api->verify_api_key();
        
        if ($account_details !== false) {
            // Save the API key and account details
            update_option('imgseo_api_key', $api_key);
            update_option('imgseo_api_verified', true);

            // Clear invalid token flag since we have a valid token now
            $this->clear_invalid_token_flag();

            wp_send_json_success([
                'message' => __('ImgSEO Token verified successfully!', 'imgseo-ai-alt-text-generator'),
                'plan' => $account_details['plan'],
                'credits' => $account_details['available']
            ]);
        } else {
            // Invalid token - do NOT save it and delete if it existed
            delete_option('imgseo_api_key');
            update_option('imgseo_api_verified', false);

            // If we are here, verify_api_key() returned false.
            // Since we forced the check, it means the API likely rejected it (or network error).
            // Let's check if the token is marked invalid to give a better message.
            if ($api->is_token_invalid()) {
                wp_send_json_error(['message' => __('ImgSEO Token rejected by server. You may be temporarily blocked due to too many failed attempts.', 'imgseo-ai-alt-text-generator')]);
            } else {
                wp_send_json_error(['message' => __('Invalid ImgSEO Token. Please check your key.', 'imgseo-ai-alt-text-generator')]);
            }
        }
    }
    
    /**
     * Updates available credits via AJAX
     */
    public function ajax_refresh_credits() {
        check_ajax_referer('imgseo_settings_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')]);
        }

        // Check if token is marked as invalid
        if ($this->is_token_invalid()) {
            wp_send_json_error([
                'message' => __('Your API key is invalid or expired. Please update it in settings.', 'imgseo-ai-alt-text-generator'),
                'invalid_token' => true
            ]);
        }

        // Usa l'endpoint /credits per controllare i crediti
        // senza consumarli
        $credits_check = wp_remote_get(
            self::API_ENDPOINT . '/credits',
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'imgseo-token' => $this->api_key
                ),
                'timeout' => 45
            )
        );

        $credits = 0;
        $update_success = false;

        if (!is_wp_error($credits_check)) {
            $response_code = wp_remote_retrieve_response_code($credits_check);
            $response_body = json_decode(wp_remote_retrieve_body($credits_check), true);

            // Check for authentication failures
            if ($this->is_auth_failure($response_code)) {
                $this->mark_token_invalid($response_code);
                wp_send_json_error([
                    'message' => __('Your API key is invalid or expired. Please update it in settings.', 'imgseo-ai-alt-text-generator'),
                    'invalid_token' => true
                ]);
            }

            if ($response_code === 200) {
                if (isset($response_body['credits_remaining'])) {
                    $credits = (float) $response_body['credits_remaining'];
                    update_option('imgseo_credits', $credits);
                    update_option('imgseo_last_check', time());
                    $update_success = true;

                    // Check if credits are sufficient
                    if ($credits <= 0) {
                        set_transient('imgseo_insufficient_credits', true, 3600);
                    } else {
                        delete_transient('imgseo_insufficient_credits');
                    }
                }
            }
        }

        if ($update_success) {
            $last_check_time = human_time_diff(time(), time() + 1) . ' ' . __('ago', 'imgseo-ai-alt-text-generator');

            wp_send_json_success([
                'credits' => $credits,
                'last_check' => $last_check_time,
                'message' => __('Credits updated successfully!', 'imgseo-ai-alt-text-generator')
            ]);
        } else {
            // In caso di errore utilizziamo i crediti memorizzati
            $credits = get_option('imgseo_credits', 0);
            $last_check_time = human_time_diff(get_option('imgseo_last_check', time()), time()) . ' ' . __('ago', 'imgseo-ai-alt-text-generator');

            wp_send_json_success([
                'credits' => $credits,
                'last_check' => $last_check_time,
                'message' => __('Using saved credits information.', 'imgseo-ai-alt-text-generator')
            ]);
        }
    }
    
    /**
     * Handles API disconnection via AJAX
     */
    public function ajax_disconnect_api() {
        check_ajax_referer('imgseo_settings_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')]);
        }
        
        // Remove API options
        delete_option('imgseo_api_key');
        delete_option('imgseo_api_verified');
        delete_option('imgseo_credits');
        delete_option('imgseo_plan');
        delete_option('imgseo_last_check');
        
        wp_send_json_success(['message' => __('API disconnected successfully', 'imgseo-ai-alt-text-generator')]);
    }
    
    /**
     * Verifies if the API key is valid
     *
     * @return bool|array False if invalid, or array with account details
     */
    public function verify_api_key() {
        // When explicitly verifying, we ignore the lockout to allow the user to fix the key
        // And we force a refresh to bypass any cached error states
        $account_details = $this->get_account_details(true, true);
        return $account_details !== false ? $account_details : false;
    }

    /**
     * Gets account details, including available credits
     * Ottimizzato per ridurre le chiamate API ridondanti
     *
     * @param bool $ignore_lockout Whether to ignore the invalid token lockout (default: false)
     * @param bool $force_refresh Whether to force a refresh from the API, ignoring cache (default: false)
     * @return array|bool False in case of error, or array with account details
     */
    public function get_account_details($ignore_lockout = false, $force_refresh = false) {
        if (empty($this->api_key)) {
            return false;
        }

        // Check if token is marked as invalid - don't make API calls
        // Unless we are explicitly ignoring the lockout (e.g. during manual verification)
        if (!$ignore_lockout && $this->is_token_invalid()) {
            error_log('[ImgSEO] Skipping API call - token is marked as invalid');
            return false;
        }

        // Verifica se c'è una cache recente per evitare richieste multiple ravvicinate
        $cache_key = 'imgseo_account_details_' . md5($this->api_key);
        $cached_details = get_transient($cache_key);

        // Usa la cache se disponibile e fresca (5 minuti), unless forced refresh
        if (!$force_refresh && $cached_details !== false) {
            return $cached_details;
        }

        // Usa l'endpoint /credits per verificare il token
        // e recuperare i crediti senza consumarli
        $credits_response = wp_remote_get(
             self::API_ENDPOINT . '/credits',
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'imgseo-token' => $this->api_key
                ),
                'timeout' => 45
            )
        );

        // Prepara i dettagli dell'account
        $account_details = array();
        $account_details['plan'] = "ImgSEO Plan"; // Valore di default
        $account_details['expires_at'] = 'never';

        // Verifica errori di connessione
        if (is_wp_error($credits_response)) {
            error_log('[ImgSEO] Network error in get_account_details: ' . $credits_response->get_error_message());

            // Usa i valori memorizzati come fallback
            $account_details['available'] = get_option('imgseo_credits', 0);

            // FIX: Set transient to prevent immediate retry loops on network failure
            set_transient($cache_key, $account_details, 5 * MINUTE_IN_SECONDS);

            return $account_details;
        }

        $response_code = wp_remote_retrieve_response_code($credits_response);

        // Check for authentication failures (401, 403)
        if ($this->is_auth_failure($response_code)) {
            $this->mark_token_invalid($response_code);
            error_log('[ImgSEO] Authentication failed (HTTP ' . $response_code . ') - API token is invalid');
            return false;
        }

        // Verifica del successo della richiesta
        if ($response_code !== 200) {
            error_log('[ImgSEO] API request failed with HTTP ' . $response_code);

            // Usa i valori memorizzati come fallback
            $account_details['available'] = get_option('imgseo_credits', 0);

            // FIX: Set transient to prevent immediate retry loops on API errors
            set_transient($cache_key, $account_details, 5 * MINUTE_IN_SECONDS);

            return $account_details;
        }
        
        // Elabora la risposta
        $credits_body = json_decode(wp_remote_retrieve_body($credits_response), true);
        
        if (isset($credits_body['credits_remaining'])) {
            $account_details['available'] = (float) $credits_body['credits_remaining'];

            
            // Aggiorna anche user_id e status se disponibili
            if (isset($credits_body['user_id'])) {
                $account_details['user_id'] = $credits_body['user_id'];
            }
            if (isset($credits_body['status'])) {
                $account_details['status'] = $credits_body['status'];
            }
        } else {
            // Se l'endpoint non restituisce i crediti, utilizza quelli memorizzati
            $account_details['available'] = get_option('imgseo_credits', 0);

        }
        
        // Store credits in WordPress options
        update_option('imgseo_credits', $account_details['available']);
        update_option('imgseo_plan', $account_details['plan']);
        update_option('imgseo_last_check', time());
        
        // Check if credits are sufficient
        if ($account_details['available'] <= 0) {
            set_transient('imgseo_insufficient_credits', true, 3600);
        } else {
            delete_transient('imgseo_insufficient_credits');
        }
        
        // Salva la cache per 5 minuti per evitare troppe chiamate ripetute
        set_transient($cache_key, $account_details, 5 * MINUTE_IN_SECONDS);
        
        return $account_details;
    }
    
    /**
     * Verifica i crediti disponibili senza consumarli
     *
     * @return array|WP_Error Risposta con i crediti disponibili o errore
     */
    public function verify_credits_only() {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'API key not configured');
        }

        // Check if token is marked as invalid - don't make API calls
        if ($this->is_token_invalid()) {
            return new WP_Error('invalid_token', 'API token is invalid. Please update your API key in settings.');
        }

        $max_retries = 2;
        $retry_delay_ms = 500000; // 0.5 secondi in microsecondi (API ottimizzata per velocità)

        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            $response = wp_remote_get(
                self::API_ENDPOINT . '/credits',
                array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'imgseo-token' => $this->api_key
                    ),
                    'timeout' => 30
                )
            );

            if (is_wp_error($response)) {
                // Se è l'ultimo tentativo, restituisci l'errore
                if ($attempt === $max_retries) {
                    return $response;
                }
                // Altrimenti, breve attesa e riprova
                usleep($retry_delay_ms);
                continue;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $decoded_response = json_decode($response_body, true);

            // Check for authentication failures (401, 403) - NEVER RETRY
            if ($this->is_auth_failure($response_code)) {
                $this->mark_token_invalid($response_code);
                return new WP_Error(
                    'invalid_token',
                    sprintf('Authentication failed (HTTP %d). Please check your API key.', $response_code)
                );
            }

            // Se la risposta è valida, restituisci il risultato
            if ($response_code === 200 && isset($decoded_response['credits_remaining'])) {
                return array(
                    'success' => true,
                    'credits_remaining' => (float) $decoded_response['credits_remaining'],
                    'can_process' => (float) $decoded_response['credits_remaining'] > 0
                );
            }

            // Retry ONLY for server errors (502 Bad Gateway e 503 Service Unavailable)
            if ($response_code === 502 || $response_code === 503) {
                if ($attempt < $max_retries) {
                    usleep($retry_delay_ms * $attempt); // Backoff leggero
                    continue;
                }
            }

            // For other errors (400, 404, 500, etc.), don't retry
            break;
        }

        return new WP_Error('verification_failed', 'Failed to verify credits after ' . $max_retries . ' attempts: ' . ($response_body ?? 'Unknown error'));
    }

    /**
     * Consuma un credito dopo il successo dell'elaborazione
     *
     * @param string $image_url URL dell'immagine
     * @param string $image_name Nome dell'immagine
     * @param string $alt_text Testo alternativo generato
     * @param float $credit_cost Costo in crediti (default 1.0)
     * @return array|WP_Error Risposta dell'API o errore
     */
    public function consume_credit($image_url = '', $image_name = '', $alt_text = '', $credit_cost = 1.0) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'API key not configured');
        }

        $response = wp_remote_post(
            self::API_ENDPOINT . '/api-v1-images',
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'imgseo-token' => $this->api_key
                ),
                'body' => json_encode(array(
                    'image_url' => $image_url,
                    'image_name' => $image_name
                )),
                'timeout' => 45
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);

        if ($response_code === 200) {
            // Aggiorna i crediti locali se forniti
            if (isset($decoded_response['credits_remaining'])) {
                update_option('imgseo_credits', (float) $decoded_response['credits_remaining']);
            }
            return array(
                'success' => true,
                'credits_remaining' => isset($decoded_response['credits_remaining']) ? (float) $decoded_response['credits_remaining'] : null
            );
        }

        return new WP_Error('consumption_failed', 'Failed to consume credit: ' . $response_body);
    }

    /**
     * Genera il testo alternativo per un'immagine
     *
     * @param string $image_url URL dell'immagine
     * @param string $prompt Prompt per la generazione
     * @param array $options Opzioni aggiuntive
     * @return array|WP_Error Risposta dell'API o errore
     */
    public function generate_alt_text_no_credit_consumption($image_url, $prompt = '', $options = array()) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'API key not configured');
        }

        // IMPORTANT: Check if we should use base64 method (same as generate_alt_text)
        $always_use_base64 = get_option('imgseo_always_use_base64', 1);
        $always_use_base64 = (int) $always_use_base64;

        // If base64 is enabled, use the base64 method instead
        if ($always_use_base64 === 1) {
            // Call the base64 method which returns the same response format
            return $this->generate_alt_text_with_base64($image_url, $prompt, $options);
        }

        $attachment_id = isset($options['attachment_id']) ? absint($options['attachment_id']) : 0;
        $parent_post_title = isset($options['parent_post_title']) ? sanitize_text_field($options['parent_post_title']) : '';
        $language = isset($options['language']) ? sanitize_text_field($options['language']) : 'it';

        // Prepara il corpo della richiesta secondo la nuova API
        $body = array(
            'image_url' => $image_url,
            'prompt' => $prompt,
            'lang' => $language,
            'model' => isset($options['model']) ? $options['model'] : 'google/gemini-2.0-flash-001',
            'optimize' => isset($options['optimize']) ? (bool)$options['optimize'] : true
        );
        
        // Aggiungi image_name se disponibile
        if (isset($options['image_name']) && !empty($options['image_name'])) {
            $body['image_name'] = sanitize_file_name($options['image_name']);
        } elseif ($attachment_id > 0) {
            // Prova a ottenere il nome del file dall'attachment
            $filename = get_post_meta($attachment_id, '_wp_attached_file', true);
            if ($filename) {
                $body['image_name'] = sanitize_file_name(basename($filename));
            }
        }

        // parent_post_title non è supportato nell'API v1
        // Il titolo della pagina può essere incluso nel prompt se necessario



        // Esegui la richiesta API
        $response = wp_remote_post(
            self::API_ENDPOINT . '/api-v1-images',
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'imgseo-token' => $this->api_key
                ),
                'body' => wp_json_encode($body),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {

            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200 && $response_code !== 201) {

            return new WP_Error('api_error', 'API request failed with code: ' . $response_code);
        }

        // FIX: Assicura che il body sia UTF-8 prima del decode per preservare caratteri speciali
        if (!mb_check_encoding($response_body, 'UTF-8')) {
            $response_body = mb_convert_encoding($response_body, 'UTF-8', mb_detect_encoding($response_body, mb_detect_order(), true));
        }

        $decoded_response = json_decode($response_body, true);

        if (!$decoded_response || !isset($decoded_response['data']) || !isset($decoded_response['data']['altText'])) {

            return new WP_Error('invalid_response', 'Invalid API response format');
        }

        // FIX: Assicura UTF-8 corretto per preservare umlaut e caratteri speciali
        $alt_text = $this->sanitize_utf8_string(trim($decoded_response['data']['altText']));

        if (empty($alt_text)) {

            return new WP_Error('empty_alt_text', 'Empty alt text received from API');
        }



        // Prepara la risposta con i crediti rimanenti se disponibili
        $response_data = array(
            'success' => true,
            'alt_text' => $alt_text,
            'credits_consumed' => true // I crediti sono stati consumati da questa chiamata API
        );
        
        // Aggiungi i crediti rimanenti se forniti dall'API
        if (isset($decoded_response['credits_remaining'])) {
            $response_data['credits_remaining'] = (float) $decoded_response['credits_remaining'];
        }
        
        return $response_data;
    }

    /**
     * Generate alt text for an image using the ImgSEO API
     * Versione migliorata che utilizza l'endpoint /api-v1-metadata (strategia Renamer)
     *
     * @param string $image_url The image URL
     * @param string $prompt The prompt for text generation
     * @param array $options Additional options for the API request
     * @return array|WP_Error API response or WP_Error in case of error
     */
    public function generate_alt_text($image_url, $prompt, $options = array()) {
       $attachment_id = isset($options['attachment_id']) ? absint($options['attachment_id']) : 0;
       
       // Genera un ID univoco basato su URL e attachment ID
       $request_key = md5($image_url . '_' . $attachment_id);
       
       // Sistema di lock migliorato per evitare elaborazioni duplicate
       if ($attachment_id > 0) {
           $lock_key = 'imgseo_processing_' . $attachment_id;
           $request_id = uniqid('req_', true); // ID univoco per questa richiesta
           
           // Verifica se esiste un lock recente (ultimi 5 secondi per migliori performance)
           $processing_lock = get_transient($lock_key);
           
           if ($processing_lock) {
               $lock_data = is_array($processing_lock) ? $processing_lock : array('time' => (int)$processing_lock, 'request_id' => 'unknown');
               $lock_time = isset($lock_data['time']) ? (int)$lock_data['time'] : 0;
               $time_diff = time() - $lock_time;
               
               // Se il lock è recente (meno di 5 secondi), verifica se abbiamo un risultato cached
               if ($time_diff < 5) {
                   $last_result = get_transient('imgseo_last_result_' . $attachment_id);
                   
                   // Se abbiamo un risultato recente, lo restituiamo
                   if ($last_result) {
                       return $last_result;
                   }
                   
                   // Se non abbiamo un risultato cached, aspettiamo un po' e ritorniamo un errore temporaneo
                   return new WP_Error('processing_in_progress', __('Processing already in progress for this image. Please try again in a few seconds.', 'imgseo-ai-alt-text-generator'));
               }
               
               // Lock scaduto, lo rimuoviamo
               delete_transient($lock_key);
           }
           
           // Imposta un nuovo lock con timestamp e request ID (durata 15 secondi)
           $lock_data = array(
               'time' => time(),
               'request_id' => $request_id
           );
           set_transient($lock_key, $lock_data, 15);
       }
       
       if (empty($this->api_key)) {
           if ($attachment_id > 0) {
               delete_transient('imgseo_processing_' . $attachment_id);
           }
           return new WP_Error('api_key_missing', 'ImgSEO Token not provided');
       }
       
       // Funzione per il cleanup dei transient in caso di errore
       $cleanup = function() use ($attachment_id) {
           if ($attachment_id > 0) {
               // Rilascia tutti i lock e cache
               delete_transient('imgseo_processing_' . $attachment_id);
               delete_transient('imgseo_global_processing_' . $attachment_id);
               // Nota: NON cancelliamo imgseo_last_result_ qui per permettere ad altre richieste di usarlo
           }
       };
       
       // Controllo dei crediti: blocchiamo definitivamente se crediti insufficienti
       $credits_exhausted = get_transient('imgseo_insufficient_credits');
       $credits = get_option('imgseo_credits', 0);
       
       // Controllo più rigoroso: se siamo a zero o sotto, o se abbiamo già impostato il flag
       if ($credits_exhausted || $credits < 1) {
           // Aggiorniamo il flag se non è già impostato
           if (!$credits_exhausted) {
               set_transient('imgseo_insufficient_credits', true, 3600); // 1 ora
           }
           $cleanup();
           return new WP_Error('insufficient_credits', 'Crediti ImgSEO insufficienti. Acquista altri crediti per continuare.');
       }
       
       try {
           // Utilizza generate_metadata invece della chiamata diretta API
           // Questo uniforma il comportamento con il Renamer e sfrutta il supporto base64 integrato
           
           $fields_config = array(
               'alt_text' => array(
                   'enabled' => true,
                   'prompt' => $prompt
               )
           );
           
           // Esegui la richiesta tramite generate_metadata
           $result = $this->generate_metadata($image_url, $fields_config, $options);
           
           if (is_wp_error($result)) {
               $cleanup();
               return $result;
           }
           
           // Verifica che l'alt text sia stato generato
           if (!isset($result['data']['alt_text'])) {
               $cleanup();
               return new WP_Error('generation_failed', 'Alt text generation failed');
           }
           
           // Prepara la risposta nel formato atteso dal controller
           $response_data = array(
               'alt_text' => $result['data']['alt_text'],
               'credits_remaining' => isset($result['credits']['remaining']) ? (float)$result['credits']['remaining'] : null,
               'model' => isset($options['model']) ? $options['model'] : 'google/gemini-2.0-flash-001',
               'image_optimized' => false,
               'file_name' => isset($result['data']['filename']) ? $result['data']['filename'] : null,
               'processing_method' => 'metadata_api'
           );
           
           // Cache il risultato per un breve periodo (30 secondi)
           if ($attachment_id > 0) {
               set_transient('imgseo_last_result_' . $attachment_id, $response_data, 30);
           }
           
           // Release the lock
           $cleanup();
           
           return $response_data;
           
       } catch (Exception $e) {
           $cleanup();
           return new WP_Error('exception', $e->getMessage());
       }
    }
    
   /**
    * Genera alt text utilizzando il metodo base64 con thumbnail ottimizzate
    *
    * @param string $image_url URL dell'immagine originale
    * @param string $prompt Il prompt per la generazione
    * @param array $options Opzioni aggiuntive
    * @param bool $force_download Se true, forza il download dell'immagine se il file locale non esiste
    * @return array|WP_Error Risposta API o errore
    */
   private function generate_alt_text_with_base64($image_url, $prompt, $options = array(), $force_download = false) {
       $attachment_id = isset($options['attachment_id']) ? absint($options['attachment_id']) : 0;
       
       try {
           // Se abbiamo un ID allegato valido, utilizziamo le thumbnail di WordPress
           if ($attachment_id > 0) {
               // Tenta di ottenere la migliore thumbnail disponibile
               list($thumbnail_url, $thumbnail_path) = $this->get_best_thumbnail($attachment_id);
               
               if ($thumbnail_path && file_exists($thumbnail_path)) {
                   $image_path = $thumbnail_path;
               } else {
                   // Fallback all'immagine originale
                   $image_path = $this->get_local_path_from_url($image_url);
               }
           } else {
               // Senza ID allegato, utilizziamo l'URL originale
               $image_path = $this->get_local_path_from_url($image_url);
           }
           
           // Verifica che il file esista
            $base64_data = '';
            if (!file_exists($image_path) || $force_download) {
                // Try to download it if local file is missing OR force_download is true
                if ( ! function_exists( 'download_url' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                $temp_file = download_url($image_url);

                if (!is_wp_error($temp_file)) {
                    $image_data = @file_get_contents($temp_file);
                    wp_delete_file($temp_file);
                    
                    if ($image_data !== false) {
                        $base64_data = base64_encode($image_data);
                    }
                }
                
                if (empty($base64_data) && !file_exists($image_path)) {
                    return new WP_Error('file_not_found', 'Unable to find local image file for base64 fallback');
                }
            }
           
           if (empty($base64_data)) {
               $mime_type = mime_content_type($image_path);
               $supported_formats = array(
                   'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'
               );
               
               if (!in_array($mime_type, $supported_formats)) {
                   if ($mime_type === 'image/svg+xml') {
                       return new WP_Error('unsupported_format', 'SVG files are not supported by the ImgSEO API. Consider converting them to PNG format.');
                   } else {
                       return new WP_Error('unsupported_format', 'Unsupported image format: ' . $mime_type);
                   }
               }
               
               $file_size = filesize($image_path);
               $max_size_warning = 1 * 1024 * 1024; // 1MB come soglia di avviso
               
               if ($file_size > $max_size_warning) {
               } else {
               }
               
               $base64_data = $this->get_image_as_base64($image_path);
               if (empty($base64_data)) {
                   return new WP_Error('base64_conversion_failed', 'Error converting the image to base64');
               }
           }
           
           // Prepara i dati per l'API con base64 invece dell'URL
           $body = array(
               'image_data' => $base64_data,
               'prompt' => $prompt,
               'lang' => isset($options['lang']) ? sanitize_text_field($options['lang']) : 'it',
               'model' => isset($options['model']) ? sanitize_text_field($options['model']) : 'google/gemini-2.0-flash-001',
               'optimize' => isset($options['optimize']) ? (bool)$options['optimize'] : true
           );
           
           // Aggiungi image_name se disponibile
           if (isset($options['image_name']) && !empty($options['image_name'])) {
               $body['image_name'] = sanitize_file_name($options['image_name']);
           } elseif ($attachment_id > 0) {
               // Prova a ottenere il nome del file dall'attachment
               $filename = get_post_meta($attachment_id, '_wp_attached_file', true);
               if ($filename) {
                   $body['image_name'] = sanitize_file_name(basename($filename));
               }
           }
           
           
           // Esegui la richiesta API con gli stessi endpoint ma dati diversi
           $response = wp_remote_post(
               self::API_ENDPOINT . '/api-v1-images',  // Endpoint unificato per tutte le operazioni
               array(
                   'headers' => array(
                       'Content-Type' => 'application/json',
                       'Accept' => 'application/json',
                       'imgseo-token' => $this->api_key
                   ),
                   'body' => wp_json_encode($body),
                   'timeout' => 180  // Timeout più lungo per le richieste base64
                )
            );
           
           // Check for connection errors
           if (is_wp_error($response)) {
               $error_message = $response->get_error_message();
               return $response;
           }
           
           // Check response code
           $response_code = wp_remote_retrieve_response_code($response);
           if ($response_code !== 200 && $response_code !== 201) {
               $error_message = wp_remote_retrieve_body($response);
               
               // Log più dettagliato in base al tipo di errore
               $error_type = 'api_error';
               $error_message_user = 'API Error (' . $response_code . '): ' . $error_message;
               
               // Gestione specifica errore 413 (Payload Too Large)
               if ($response_code === 413) {
                   $error_type = 'image_too_large';
                   $error_message_user = 'Image too large for the API (max ~10MB). Try optimizing the image.';
               }
               // Check for image format errors
               else if ($response_json = json_decode($error_message, true)) {
                   if (isset($response_json['error']) &&
                       (strpos($response_json['error'], 'Invalid image data') !== false ||
                       strpos($response_json['details'], 'unsupported image format') !== false)) {
                       
                       $error_type = 'unsupported_format';
                       $error_message_user = 'Unsupported image format or invalid image data.';
                   }
               }
               
               return new WP_Error($error_type, $error_message_user);
           }
           
           // Decode response
           // FIX: Assicura che il body sia UTF-8 prima del decode per preservare caratteri speciali
           $response_body_raw = wp_remote_retrieve_body($response);

           // Forza UTF-8 se necessario
           if (!mb_check_encoding($response_body_raw, 'UTF-8')) {
               $response_body_raw = mb_convert_encoding($response_body_raw, 'UTF-8', mb_detect_encoding($response_body_raw, mb_detect_order(), true));
           }

           $response_body = json_decode($response_body_raw, true);

           // Verifica che la risposta contenga l'alt text nella nuova struttura
           if (!isset($response_body['data']) || !isset($response_body['data']['altText'])) {
               return new WP_Error('invalid_response', 'Invalid API response: data.altText is missing');
           }

           // Estrai l'alt text dalla nuova struttura
           // FIX: Assicura UTF-8 corretto per preservare umlaut e caratteri speciali
           $alt_text = $this->sanitize_utf8_string($response_body['data']['altText']);
           
           // Estrai i crediti residui se disponibili
           $remaining_credits = isset($response_body['credits_remaining'])
               ? (float) $response_body['credits_remaining']
               : null;
           
           // Aggiorna i crediti se disponibili nella risposta
           if ($remaining_credits !== null) {
               update_option('imgseo_credits', $remaining_credits);
               
               // If credits are exhausted, set a warning
               if ($remaining_credits <= 0) {
                   set_transient('imgseo_insufficient_credits', true, 3600);
               } else {
                   delete_transient('imgseo_insufficient_credits');
               }
           } else {
               // Se non riceviamo i crediti, diminuiamo manualmente di 1
               $credits = get_option('imgseo_credits', 0);
               update_option('imgseo_credits', max(0, $credits - 1));
               
               if ($credits - 1 <= 0) {
                   set_transient('imgseo_insufficient_credits', true, 3600);
               }
           }
           
           // Prepara la risposta nel formato compatibile con il resto del plugin
           $result = array(
               'alt_text' => $alt_text,
               'credits_remaining' => $remaining_credits,
               'model' => isset($response_body['model']) ? $response_body['model'] : null,
               'image_optimized' => isset($response_body['image_optimized']) ? $response_body['image_optimized'] : null,
               'file_name' => isset($response_body['file_name']) ? $response_body['file_name'] : null,
               'processing_method' => isset($response_body['processing_method']) ? $response_body['processing_method'] : 'base64'
           );
           
           
           return $result;
           
       } catch (Exception $e) {
           return new WP_Error('base64_fallback_error', 'Error during base64 fallback: ' . $e->getMessage());
       }
   }

   /**
    * Generate metadata (alt_text + optional title/caption/description/filename) using new /api-v1-metadata endpoint
    *
    * @param string $image_url The image URL
    * @param array $fields_config Configuration for which fields to generate
    * @param array $options Additional options for the API request
    * @return array|WP_Error API response or WP_Error in case of error
    */
   public function generate_metadata($image_url, $fields_config, $options = array()) {
       $attachment_id = isset($options['attachment_id']) ? absint($options['attachment_id']) : 0;

       if (empty($this->api_key)) {
           return new WP_Error('api_key_missing', 'ImgSEO Token not provided');
       }

       // Check credits
       $credits = get_option('imgseo_credits', 0);
       if ($credits < 1) {
           return new WP_Error('insufficient_credits', 'Insufficient ImgSEO credits');
       }

       try {
           // ✅ FIX: Check if we should use base64 method (same as generate_alt_text)
           $always_use_base64 = get_option('imgseo_always_use_base64', 1);
           $always_use_base64 = (int) $always_use_base64;

           // Build request body for /api-v1-metadata endpoint
           $body = array(
               'fields' => $fields_config,
               'options' => array(
                   'lang' => isset($options['lang']) ? sanitize_text_field($options['lang']) : 'en',
                   'model' => isset($options['model']) ? sanitize_text_field($options['model']) : 'google/gemini-2.0-flash-001',
                   'optimize' => isset($options['optimize']) ? (bool)$options['optimize'] : true,
                   'temperature' => isset($options['temperature']) ? (float)$options['temperature'] : 0.7
               )
           );

           // ✅ FIX: Add base64 support (convert local image to base64 if enabled)
           if ($always_use_base64 === 1 && $attachment_id > 0) {
               // Try to get thumbnail for optimization
               list($thumbnail_url, $thumbnail_path) = $this->get_best_thumbnail($attachment_id);

               $image_path = ($thumbnail_path && file_exists($thumbnail_path))
                   ? $thumbnail_path
                   : $this->get_local_path_from_url($image_url);

               if ($image_path && file_exists($image_path)) {
                    $base64_data = $this->get_image_as_base64($image_path);
                    if ($base64_data) {
                        $body['image_data'] = $base64_data;
                        error_log('ImgSEO: Using base64 method for metadata generation (bypass Shield Security)');
                    } else {
                        // Fallback to URL if base64 fails
                        $body['image_url'] = $image_url;
                        error_log('ImgSEO: Base64 conversion failed, falling back to URL method');
                    }
                } else {
                    // File not found locally - try to download it (useful for S3/Offload/Cloudflare)
                    if ( ! function_exists( 'download_url' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                    }

                    $temp_file = download_url($image_url);

                    if (!is_wp_error($temp_file)) {
                        $image_data = @file_get_contents($temp_file);
                        if ($image_data !== false) {
                            $base64_data = base64_encode($image_data);
                            $body['image_data'] = $base64_data;
                            error_log('ImgSEO: Downloaded remote image for base64 generation (local file missing)');
                        } else {
                            $body['image_url'] = $image_url;
                            error_log('ImgSEO: Failed to read downloaded temp file, using URL method');
                        }
                        wp_delete_file($temp_file);
                    } else {
                        // Download failed, use URL
                        $body['image_url'] = $image_url;
                        error_log('ImgSEO: Local file not found and download failed, using URL method');
                    }
                }
           } else {
               // Base64 disabled or no attachment ID, use URL
               $body['image_url'] = $image_url;
           }

           // Add image_name if available
           if (isset($options['image_name']) && !empty($options['image_name'])) {
               $body['image_name'] = sanitize_file_name($options['image_name']);
           } elseif ($attachment_id > 0) {
               $filename = get_post_meta($attachment_id, '_wp_attached_file', true);
               if ($filename) {
                   $body['image_name'] = sanitize_file_name(basename($filename));
               }
           }

           // Make API request to /api-v1-metadata
           $response = wp_remote_post(self::API_ENDPOINT . '/api-v1-metadata', array(
               'headers' => array(
                    'imgseo-token' => $this->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ),
                'body' => json_encode($body),
                'timeout' => 180,
                'sslverify' => true
            ));

           if (is_wp_error($response)) {
               return new WP_Error('api_request_failed', 'API request failed: ' . $response->get_error_message());
           }

           $response_code = wp_remote_retrieve_response_code($response);
           $response_body = json_decode(wp_remote_retrieve_body($response), true);

           // Handle error responses
           if ($response_code !== 200) {
               $error_message = isset($response_body['error']) ? $response_body['error'] : 'Unknown error';
               $error_details = isset($response_body['details']) ? $response_body['details'] : '';

               // Handle specific error codes
               if ($response_code === 402) {
                   set_transient('imgseo_insufficient_credits', true, 3600);
                   return new WP_Error('insufficient_credits', $error_message . ' - ' . $error_details);
               } elseif ($response_code === 400) {
                   return new WP_Error('bad_request', $error_message . ' - ' . $error_details);
               }

               return new WP_Error('api_error', $error_message . ' - ' . $error_details);
           }

           // Validate response structure
           if (!isset($response_body['success']) || !$response_body['success']) {
               return new WP_Error('api_response_invalid', 'Invalid API response structure');
           }

           if (!isset($response_body['data'])) {
               return new WP_Error('api_response_invalid', 'Response missing data field');
           }

           // Extract data
           $data = $response_body['data'];
           $credits_info = isset($response_body['credits']) ? $response_body['credits'] : array();

           // Update credits
           if (isset($credits_info['remaining'])) {
               update_option('imgseo_credits', (float)$credits_info['remaining']);

               if ($credits_info['remaining'] <= 0) {
                   set_transient('imgseo_insufficient_credits', true, 3600);
               } else {
                   delete_transient('imgseo_insufficient_credits');
               }
           }

           // Sanitize UTF-8 for all text fields
           $result = array(
               'success' => true,
               'data' => array(),
               'credits' => $credits_info,
               'generation_info' => isset($response_body['generation_info']) ? $response_body['generation_info'] : array(),
               'processing_info' => isset($response_body['processing_info']) ? $response_body['processing_info'] : array()
           );

           // Add each field that was generated
           if (isset($data['alt_text'])) {
               $result['data']['alt_text'] = $this->sanitize_utf8_string($data['alt_text']);
           }

           if (isset($data['title'])) {
               $result['data']['title'] = $this->sanitize_utf8_string($data['title']);
           }

           if (isset($data['caption'])) {
               $result['data']['caption'] = $this->sanitize_utf8_string($data['caption']);
           }

           if (isset($data['description'])) {
               $result['data']['description'] = $this->sanitize_utf8_string($data['description']);
           }

           if (isset($data['filename'])) {
               $result['data']['filename'] = sanitize_file_name($data['filename']);
           }

           return $result;

       } catch (Exception $e) {
           return new WP_Error('metadata_generation_error', 'Error generating metadata: ' . $e->getMessage());
       }
   }

   /**
    * Trova la migliore thumbnail disponibile per un allegato
    *
    * @param int $attachment_id ID dell'allegato
    * @return array Array con [url_thumbnail, percorso_file_locale]
    */
   private function get_best_thumbnail($attachment_id) {
       // Dimensione ottimale per l'API (in byte)
       // Usa thumbnail solo se l'originale è > 5MB per massimizzare qualità analisi
       $optimal_size = 5 * 1024 * 1024; // 5MB - soglia per usare thumbnail (aumentata per qualità)
       $max_acceptable_size = 10 * 1024 * 1024; // 10MB - limite assoluto

       // Array delle dimensioni thumbnail in ordine di preferenza (large = migliore qualità)
       $sizes = array('large', 'medium_large', 'medium', 'thumbnail');

       // Verifica se l'originale è sotto la soglia ottimale
       $original_path = get_attached_file($attachment_id);
       $original_url = wp_get_attachment_url($attachment_id);
       $original_size = file_exists($original_path) ? filesize($original_path) : 0;

       // Se l'originale è sotto 3MB, usalo direttamente
       if ($original_size > 0 && $original_size <= $optimal_size) {
           return array($original_url, $original_path);
       }

       // Se l'originale è > 3MB O se è troppo grande, cerca una thumbnail adatta
       foreach ($sizes as $size) {
           // Ottieni l'URL della thumbnail
           $thumb = wp_get_attachment_image_src($attachment_id, $size);
           if ($thumb) {
               $thumb_url = $thumb[0];

               // Converti URL in percorso locale
               $upload_dir = wp_upload_dir();
               $thumb_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $thumb_url);

               // Verifica che il file esista e sia sotto il limite assoluto
               if (file_exists($thumb_path)) {
                   $file_size = filesize($thumb_path);
                   if ($file_size <= $max_acceptable_size && $file_size > 0) {
                       if ($original_size > $optimal_size) {
                           error_log(sprintf(
                               'ImgSEO: Using %s thumbnail (%.2fMB) instead of original (%.2fMB) for attachment #%d',
                               $size,
                               $file_size / 1024 / 1024,
                               $original_size / 1024 / 1024,
                               $attachment_id
                           ));
                       }
                       return array($thumb_url, $thumb_path);
                   } else {
                   }
               }
           }
       }
       
       // Se arriviamo qui, tutte le thumbnail sono troppo grandi o non disponibili
       // Restituisci la thumbnail più piccola disponibile come ultimo tentativo
       foreach (array_reverse($sizes) as $size) {
           $thumb = wp_get_attachment_image_src($attachment_id, $size);
           if ($thumb) {
               $thumb_url = $thumb[0];
               $upload_dir = wp_upload_dir();
               $thumb_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $thumb_url);
               
               if (file_exists($thumb_path)) {
                   // Use smallest available thumbnail as last resort
                   return array($thumb_url, $thumb_path);
               }
           }
       }
       
       // Se proprio non troviamo alternative, restituisci l'originale e lascia che l'API gestisca l'errore
       return array($original_url, $original_path);
   }
   
   /**
    * Converte un'immagine in stringa base64
    *
    * @param string $file_path Percorso del file immagine
    * @return string|false Stringa base64 o false in caso di errore
    */
   private function get_image_as_base64($file_path) {
       // Security: Validate path is within upload directory
       $upload_dir = wp_upload_dir();
       $upload_basedir = realpath($upload_dir['basedir']);
       $resolved_path = realpath($file_path);

       if ($resolved_path === false || strpos($resolved_path, $upload_basedir) !== 0) {
           return false;
       }

       // Verifica che il file esista
       if (!file_exists($file_path)) {
           return false;
       }

       // Check file size BEFORE loading into memory (WordPress best practice)
       $file_size = filesize($file_path);
       $max_size = 10 * 1024 * 1024; // 10MB in bytes

       if ($file_size === false) {
           error_log('ImgSEO: Cannot get file size for ' . $file_path);
           return false;
       }

       if ($file_size > $max_size) {
           error_log('ImgSEO: File too large (' . round($file_size / 1024 / 1024, 2) . 'MB): ' . $file_path);
           return false;
       }

       // Ottieni tipo MIME
       $mime_type = mime_content_type($file_path);

       // Leggi e codifica file con error handling
       $image_data = @file_get_contents($file_path);
       if ($image_data === false) {
           error_log('ImgSEO: Cannot read file: ' . $file_path);
           return false;
       }

       $base64 = base64_encode($image_data);

       // Free memory immediately after encoding
       unset($image_data);

       // Restituisci solo la stringa base64 senza prefisso data URI
       // L'API si aspetta solo il contenuto base64 puro, non il formato data URI completo
       return $base64;
   }
   
   /**
    * Converte un URL immagine nel percorso file locale
    *
    * @param string $image_url URL dell'immagine
    * @return string Percorso locale del file
    */
   private function get_local_path_from_url($image_url) {
       // Gestione URL WordPress standard
       $upload_dir = wp_upload_dir();
       $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
       
       // Security: Validate that the path is within upload directory
       $upload_basedir = realpath($upload_dir['basedir']);
       $resolved_path = realpath($file_path);
       
       if ($resolved_path === false || strpos($resolved_path, $upload_basedir) !== 0) {
           // Path is outside upload directory - security risk
           
           // Fallback: try to get safe path via attachment ID
           $attachment_id = attachment_url_to_postid($image_url);
           if ($attachment_id) {
               $safe_path = get_attached_file($attachment_id);
               $safe_resolved = realpath($safe_path);
               if ($safe_resolved && strpos($safe_resolved, $upload_basedir) === 0) {
                   return $safe_path;
               }
           }
           return false; // Reject unsafe path
       }
       
       // Gestione URL con CDN o personalizzati
       if (!file_exists($file_path)) {
           // Tenta di risolvere l'URL tramite l'ID dell'allegato
           $attachment_id = attachment_url_to_postid($image_url);
           if ($attachment_id) {
               $safe_path = get_attached_file($attachment_id);
               $safe_resolved = realpath($safe_path);
               if ($safe_resolved && strpos($safe_resolved, $upload_basedir) === 0) {
                   return $safe_path;
               }
           }
       }
       
       return $file_path;
   }
   
   /**
     * Checks if the site is offline (not accessible from the internet)
     *
     * @return bool True if the site is offline, false otherwise
     */
    private function is_site_offline() {
       $site_url = get_site_url();
       
       // List of known local domains
       $local_domains = array(
           'localhost',
           '.local',
           '.test',
           '.dev',
           '127.0.0.1',
           '192.168.',
           '10.',
           '172.16.',
           '172.17.',
           '172.18.',
           '172.19.',
           '172.20.',
           '172.21.',
           '172.22.',
           '172.23.',
           '172.24.',
           '172.25.',
           '172.26.',
           '172.27.',
           '172.28.',
           '172.29.',
           '172.30.',
           '172.31.'
       );
       
       // Check if the site URL contains one of the local domains
       foreach ($local_domains as $domain) {
           if (strpos($site_url, $domain) !== false) {
               return true;
           }
       }
       
       return false;
   }
   
   /**
    * Comprimi un'immagine utilizzando l'API ImgSEO
    *
    * @param string $image_url URL dell'immagine da comprimere
    * @param array $options Opzioni di compressione
    * @return array|WP_Error Risultato della compressione o errore
    */
   public function compress_image($image_url, $options = array()) {
       if (empty($this->api_key)) {
           return new WP_Error('no_api_key', 'API key not configured');
       }

       // Prepara i dati per l'API (snake_case richiesto)
       $api_data = array(
           'image_url' => $image_url
       );

       // Aggiungi le opzioni di compressione
       $default_options = array(
           'format' => 'auto',
           'quality' => 80,
           'optimizeForWeb' => true,
           'stripMetadata' => true
       );

       $compression_options = wp_parse_args($options, $default_options);

       foreach ($compression_options as $key => $value) {
           $api_data[$key] = $value;
       }

       // Normalizza chiavi camelCase in snake_case per compatibilità con API v3
       if (isset($api_data['optimizeForWeb'])) {
           $api_data['optimize_for_web'] = (bool) $api_data['optimizeForWeb'];
           unset($api_data['optimizeForWeb']);
       }
       if (isset($api_data['stripMetadata'])) {
           $api_data['strip_metadata'] = (bool) $api_data['stripMetadata'];
           unset($api_data['stripMetadata']);
       }

       // Effettua la chiamata API con retry logic ottimizzato per API veloce
       $max_retries = 2;
       $retry_delay_ms = 500000; // 0.5 secondi in microsecondi

       for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
           $response = wp_remote_post(
               self::API_ENDPOINT . '/compress',
               array(
                   'headers' => array(
                       'Content-Type' => 'application/json',
                       'imgseo-token' => $this->api_key,
                       'Authorization' => 'Bearer ' . $this->api_key
                   ),
                   'body' => json_encode($api_data),
                   'timeout' => 90
               )
           );

           if (is_wp_error($response)) {
               // Se è l'ultimo tentativo, restituisci l'errore
               if ($attempt === $max_retries) {
                   return new WP_Error('api_error', 'Connection error to compression API after ' . $max_retries . ' attempts: ' . $response->get_error_message());
               }
               // Altrimenti, breve attesa e riprova
               usleep($retry_delay_ms * $attempt);
               continue;
           }

           $response_code = wp_remote_retrieve_response_code($response);
           $response_body = wp_remote_retrieve_body($response);
           $decoded_response = json_decode($response_body, true);

           // Se la compressione è riuscita, esci dal loop
           if ($response_code === 200) {
               break;
           }

           // Retry per errori 502 Bad Gateway, 503 Service Unavailable e 504 Gateway Timeout
           if (in_array($response_code, [502, 503, 504])) {
               if ($attempt < $max_retries) {
                   usleep($retry_delay_ms * $attempt); // Backoff leggero
                   continue;
               }
           }

           // Per altri errori, non riprova
           break;
       }

       if ($response_code !== 200) {
           // Estrarre messaggi di errore in modo più robusto (stringa o oggetto)
           $error_message = 'Unknown compression error (HTTP ' . $response_code . ')';
           if (isset($decoded_response['error'])) {
               if (is_string($decoded_response['error'])) {
                   $error_message = $decoded_response['error'];
               } elseif (is_array($decoded_response['error']) && isset($decoded_response['error']['message'])) {
                   $error_message = $decoded_response['error']['message'];
               }
           }
           if (isset($decoded_response['details']) && is_string($decoded_response['details'])) {
               $error_message .= ' — ' . $decoded_response['details'];
           }
           return new WP_Error('compression_failed', $error_message);
       }

       // Normalizza risposte prive di 'success'/'data' e mappa chiavi legacy attese dal plugin
       $has_top_level_image = isset($decoded_response['compressed_image']) || isset($decoded_response['compressed_images']);
       if (!isset($decoded_response['success']) && $has_top_level_image) {
           $decoded_response['success'] = true;
       }
       if (!isset($decoded_response['data']) && $has_top_level_image) {
           $decoded_response['data'] = array();
           if (isset($decoded_response['compressed_image'])) {
               $decoded_response['data']['compressed_image'] = $decoded_response['compressed_image'];
           }
           if (isset($decoded_response['compressed_images']) && is_array($decoded_response['compressed_images'])) {
               $decoded_response['data']['compressed_images'] = $decoded_response['compressed_images'];
           }
           foreach (array('format','mime_type','size','savings_percentage','dimensions') as $k) {
               if (isset($decoded_response[$k])) {
                   $decoded_response['data'][$k] = $decoded_response[$k];
               }
           }
       }
       if (isset($decoded_response['data'])) {
           // Mappa camelCase → snake_case per risposta single-format v2.1
           if (!isset($decoded_response['data']['compressed_image']) && isset($decoded_response['data']['compressedImage'])) {
               $decoded_response['data']['compressed_image'] = $decoded_response['data']['compressedImage'];
           }
           if (!isset($decoded_response['data']['compressionRatio']) && isset($decoded_response['data']['savings_percentage'])) {
               $decoded_response['data']['compressionRatio'] = floatval($decoded_response['data']['savings_percentage']);
           }
           if (!isset($decoded_response['data']['compressedSize']) && isset($decoded_response['data']['size'])) {
               $decoded_response['data']['compressedSize'] = intval($decoded_response['data']['size']);
           }
       }

       if (!isset($decoded_response['success']) || !$decoded_response['success']) {
           return new WP_Error('compression_failed', 'Compression was not successful');
       }

       // Aggiorna i crediti se forniti nella risposta
       if (isset($decoded_response['credits']['remaining'])) {
           update_option('imgseo_credits', (float) $decoded_response['credits']['remaining']);
       }

       return $decoded_response;
   }

   /**
    * Gets available credits
    *
    * @param bool $refresh If true, forces an update from ImgSEO servers
    * @return int The number of available credits
    */
   public function get_available_credits($refresh = false) {
       // If an update is requested or the last check is older than an hour
       $last_check = get_option('imgseo_last_check', 0);
        if ($refresh || time() - $last_check > 3600) {
            $account_details = $this->get_account_details(false, $refresh);
            if ($account_details !== false) {
                return $account_details['available'];
            }
        }

        return get_option('imgseo_credits', 0);
    }

   /**
    * Sanitizza una stringa assicurando corretta codifica UTF-8
    * Preserva caratteri speciali come umlaut tedeschi (ä, ö, ü, ß) e altri caratteri non-ASCII
    *
    * @param string $text Il testo da sanitizzare
    * @return string Il testo con encoding UTF-8 corretto
    */
   private function sanitize_utf8_string($text) {
       if (empty($text)) {
           return $text;
       }

       // Se la stringa è già valida UTF-8, restituiscila così com'è
       if (mb_check_encoding($text, 'UTF-8')) {
           return $text;
       }

       // Rileva l'encoding e converti a UTF-8
       $detected_encoding = mb_detect_encoding($text, mb_detect_order(), true);
       if ($detected_encoding && $detected_encoding !== 'UTF-8') {
           $text = mb_convert_encoding($text, 'UTF-8', $detected_encoding);
       }

       // Verifica ancora una volta e pulisci se necessario
       if (!mb_check_encoding($text, 'UTF-8')) {
           // Come ultimo tentativo, forza la pulizia dei caratteri non validi
           $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
       }

       return $text;
   }
}

} // Fine controllo class_exists
