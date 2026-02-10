<?php
/**
 * Class Renamer_AI_Generator
 * Gestisce la generazione di nomi file ottimizzati per SEO tramite AI
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_AI_Generator {
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Inizializza il generatore
     */
    private function __construct() {
        // Registra l'AJAX handler
        add_action('wp_ajax_imgseo_generate_ai_filename', array($this, 'ajax_generate_ai_filename'));
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
     * AJAX handler per la generazione del nome file
     */
    public function ajax_generate_ai_filename() {
        // Verifica nonce
        check_ajax_referer('imgseo_renamer_nonce', 'security');
        
        // Verifica permessi
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'imgseo-ai-alt-text-generator')));
        }
        
        // Ottieni ID allegato
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        if (!$attachment_id) {
            wp_send_json_error(array('message' => __('Invalid attachment ID', 'imgseo-ai-alt-text-generator')));
        }
        
        // Genera il nome file
        $result = $this->generate_filename($attachment_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('filename' => $result));
    }
    
    /**
     * Genera un nome file ottimizzato per SEO utilizzando l'AI
     * 
     * @param int $attachment_id ID dell'allegato
     * @return string|WP_Error Generated filename or error
     */
    public function generate_filename($attachment_id) {
        // Verifica se esiste un blocco globale di processo
        if (class_exists('ImgSEO_Process_Lock') && ImgSEO_Process_Lock::is_globally_locked()) {
            return new WP_Error('process_locked', __('ImgSEO operations are temporarily blocked. Please try again in a moment.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Ottieni l'URL dell'immagine
        $image_url = wp_get_attachment_url($attachment_id);
        if (!$image_url) {
            return new WP_Error('invalid_attachment', __('Invalid attachment ID', 'imgseo-ai-alt-text-generator'));
        }
        
        // Ottieni il contesto dell'immagine
        $context = $this->get_image_context($attachment_id);
        
        // Genera il prompt per l'AI
        $prompt = $this->build_ai_prompt($context);
        
        // Verifica i crediti disponibili prima di chiamare l'API
        $api_instance = ImgSEO_API::get_instance();
        $credits_before = get_option('imgseo_credits', 0);
        
        // Se i crediti sono insufficienti, avvisa l'utente
        if ($credits_before <= 0) {
            return new WP_Error('insufficient_credits', __('Insufficient ImgSEO credits. Purchase more credits to continue.', 'imgseo-ai-alt-text-generator'));
        }
        
        // Log per debug: crediti prima della chiamata
        // Debug: Credits before generation
        
        // Ottieni il codice lingua dalle impostazioni
        $lang_code = get_option('imgseo_language', 'english');
        
        // Imposta opzioni per le API nuove - force refresh per variazioni AI nel renamer singolo
        $options = array(
            'source' => 'ai_generator',
            'attachment_id' => $attachment_id,
            'lang' => $lang_code, // Passa direttamente il codice lingua all'API
            'force_refresh' => true // Disabilita cache per permettere variazioni AI
        );
        
        // Prepara fields_config per il nuovo endpoint (solo filename)
        $max_words = (int) get_option('imgseo_renamer_ai_max_words', 4);
        $max_length = (int) get_option('imgseo_filename_max_length', 50);

        $fields_config = array(
            'filename' => array(
                'enabled' => true,
                'prompt' => $prompt
            )
        );

        // Chiama l'endpoint v2 per generare metadata (filename)
        $response = $api_instance->generate_metadata($image_url, $fields_config, $options);
        
        // Log per debug: crediti dopo la chiamata
        $credits_after = get_option('imgseo_credits', 0);
        // Debug: Credits after generation
        
        // Process the response
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Verifica la risposta della nuova API
        if (!isset($response['success']) || !$response['success'] || !isset($response['data']['filename'])) {
            return new WP_Error('invalid_response', __('Invalid API response', 'imgseo-ai-alt-text-generator'));
        }

        // Estrarre il filename generato
        $filename = $response['data']['filename'];
        // Rimuovi eventuali estensioni accidentalmente incluse
        $filename = preg_replace('/\.[a-zA-Z0-9]+$/', '', (string) $filename);
        
        // Log per debug: risultato AI grezzo
        // Debug: AI raw result
        
        // Applica post-processing per garantire numero esatto di token
        $filename = $this->imgseo_enforce_exact_tokens($filename, $max_words, $context, $lang_code);
        
        // Log per debug: risultato finale e conteggio parole
        $word_count = count(explode('-', $filename));
        
        return $filename;
    }
    
    /**
     * Ottiene il contesto dell'immagine per arricchire il prompt
     * 
     * @param int $attachment_id ID dell'allegato
     * @return array Contesto dell'immagine
     */
    private function get_image_context($attachment_id) {
        $context = array();
        
        // Ottieni i metadati dell'immagine
        $attachment = get_post($attachment_id);
        if ($attachment) {
            $context['title'] = $attachment->post_title;
            $context['alt_text'] = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            $context['caption'] = $attachment->post_excerpt;
            $context['description'] = $attachment->post_content;
        }
        
        // Ottieni il post parent se esiste
        $post_parent = wp_get_post_parent_id($attachment_id);
        if ($post_parent) {
            $parent = get_post($post_parent);
            if ($parent) {
                $context['post_title'] = $parent->post_title;
                $context['post_content'] = wp_trim_words($parent->post_content, 50); // Limita la lunghezza
                
                // Ottieni categorie
                $categories = get_the_category($post_parent);
                if (!empty($categories)) {
                    $context['category'] = $categories[0]->name;
                }
            }
        }
        
        return $context;
    }
    
    /**
     * Costruisce il prompt per l'AI in base al contesto e alle impostazioni
     * 
     * @param array $context Contesto dell'immagine
     * @return string Prompt per l'AI
     */
    private function build_ai_prompt($context) {
        // Ottieni la lingua dalle impostazioni generali del plugin
        $lang_code = get_option('imgseo_language', 'english');
        
        // Mappa dei codici di lingua ai nomi visualizzati
        $languages = [
            'english' => 'English',
            'italiano' => 'Italian',
            'japanese' => 'Japanese',
            'korean' => 'Korean',
            'arabic' => 'Arabic',
            'bahasa_indonesia' => 'Indonesian',
            'bengali' => 'Bengali',
            'bulgarian' => 'Bulgarian',
            'chinese_simplified' => 'Chinese (Simplified)',
            'chinese_traditional' => 'Chinese (Traditional)',
            'croatian' => 'Croatian',
            'czech' => 'Czech',
            'danish' => 'Danish',
            'dutch' => 'Dutch',
            'estonian' => 'Estonian',
            'farsi' => 'Persian',
            'finnish' => 'Finnish',
            'french' => 'French',
            'german' => 'German',
            'gujarati' => 'Gujarati',
            'greek' => 'Greek',
            'hebrew' => 'Hebrew',
            'hindi' => 'Hindi',
            'hungarian' => 'Hungarian',
            'kannada' => 'Kannada',
            'latvian' => 'Latvian',
            'lithuanian' => 'Lithuanian',
            'malayalam' => 'Malayalam',
            'marathi' => 'Marathi',
            'norwegian' => 'Norwegian',
            'polish' => 'Polish',
            'portuguese' => 'Portuguese',
            'romanian' => 'Romanian',
            'russian' => 'Russian',
            'serbian' => 'Serbian',
            'slovak' => 'Slovak',
            'slovenian' => 'Slovenian',
            'spanish' => 'Spanish',
            'swahili' => 'Swahili',
            'swedish' => 'Swedish',
            'tamil' => 'Tamil',
            'telugu' => 'Telugu',
            'thai' => 'Thai',
            'turkish' => 'Turkish',
            'ukrainian' => 'Ukrainian',
            'urdu' => 'Urdu',
            'vietnamese' => 'Vietnamese'
        ];
        
        // Ottieni il nome della lingua dalla mappatura o usa English come fallback
        $lang = isset($languages[$lang_code]) ? $languages[$lang_code] : 'English';
        
        // Log per debug
        
        // Ottieni le impostazioni AI
        $max_words = (int) get_option('imgseo_renamer_ai_max_words', 4);
        $include_post_title = (bool) get_option('imgseo_renamer_ai_include_post_title', 1);
        $include_category = (bool) get_option('imgseo_renamer_ai_include_category', 1);
        $include_alt_text = (bool) get_option('imgseo_renamer_ai_include_alt_text', 1);
        
        // Prepara un esempio coerente col numero richiesto
        $tokens_example = implode('-', array_fill(0, max(1, (int)$max_words), 'xxx'));
        $max_words_minus_one = max(0, (int)$max_words - 1);
        $regex = "^[a-z0-9]+(?:-[a-z0-9]+){{$max_words_minus_one}}$";
        
        // 1. PROMPT BASE (rivisto)
        $prompt = "You are a filename generator. Produce a lowercase, ASCII-only, SEO-friendly filename in {$lang}. Tokens must be nouns/adjectives relevant to the image context.";
        
        // 2. NUMERO PAROLE (definizione chiara di 'parola')
        $prompt .= " Output exactly {$max_words} tokens separated by single hyphens ('-'). A token is one or more alphanumeric characters: [a-z0-9]+. No spaces, underscores, slashes, or extra hyphens.";
        
        // Esempio coerente
        $prompt .= " Example format (must have {$max_words} tokens): {$tokens_example}.";
        
        // Stopwords e ripetizioni (aiuta il conteggio esatto)
        $prompt .= " Do not use articles, prepositions, pronouns or stopwords in {$lang} (e.g., 'di', 'del', 'a', 'the', 'of'). Do not repeat tokens. Prefer singular forms.";
        
        // Log per debug: prompt base
        
        // Includi informazioni contestuali se disponibili e abilitate
        if ($include_post_title && !empty($context['post_title'])) {
            $prompt .= " The image is related to this article title: \"{$context['post_title']}\".";
        }
        
        if ($include_category && !empty($context['category'])) {
            $prompt .= " The article category is: \"{$context['category']}\".";
        }
        
        if ($include_alt_text && !empty($context['alt_text'])) {
            $prompt .= " Current alt text of the image is: \"{$context['alt_text']}\".";
        }
        
        // Se c'è una didascalia, usala come contesto aggiuntivo
        if (!empty($context['caption'])) {
            $prompt .= " Image caption: \"{$context['caption']}\".";
        }
        
        // 7. ISTRUZIONI FINALI (rafforzate con regex)
        $prompt .= " Return only the filename (without extension), matching this regex: /{$regex}/. No additional text or punctuation. Use hyphens to separate tokens.";
        
        // Log per debug: prompt finale
        
        return $prompt;
    }
    
    /**
     * Normalizza un filename rimuovendo caratteri speciali e garantendo formato corretto
     * 
     * @param string $name Il nome file da normalizzare
     * @return string Il nome file normalizzato
     */
    private function imgseo_normalize_filename(string $name): string {
        // Converti caratteri speciali in ASCII
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        
        // Converti in minuscolo
        $name = strtolower($name);
        
        // Sostituisci caratteri non alfanumerici con trattini
        $name = preg_replace('/[^\p{L}\p{N}]+/u', '-', $name);
        
        // Rimuovi trattini multipli
        $name = preg_replace('/-+/', '-', $name);
        
        // Rimuovi trattini all'inizio e alla fine
        $name = trim($name, '-');
        
        // Rimuovi eventuali caratteri non a-z0-9-
        $name = preg_replace('/[^a-z0-9-]/', '', $name);
        
        return $name;
    }
    
    /**
     * Estrae token utili dal contesto per completare filename incompleti
     * 
     * @param array $context Il contesto dell'immagine
     * @param string $lang La lingua per il processing
     * @return array Array di token estratti
     */
    private function imgseo_tokens_from_context(array $context, string $lang): array {
        $pool = [];
        
        // Raccogli testo da tutti i campi disponibili
        foreach (['alt_text', 'title', 'post_title', 'category', 'caption', 'description', 'post_content'] as $field) {
            if (!empty($context[$field])) {
                $pool[] = $context[$field];
            }
        }
        
        if (empty($pool)) {
            return [];
        }
        
        // Converti tutto in ASCII e normalizza
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', strtolower(implode(' ', $pool)));
        $str = preg_replace('/[^a-z0-9]+/', ' ', $str);
        $raw = preg_split('/\s+/', trim($str)) ?: [];
        
        // Filtra e deduplica
        $seen = [];
        $out = [];
        
        foreach ($raw as $word) {
            // Salta parole troppo corte o lunghe
            if (strlen($word) < 2 || strlen($word) > 20) {
                continue;
            }
            
            // Salta duplicati
            if (isset($seen[$word])) {
                continue;
            }
            
            $seen[$word] = true;
            $out[] = $word;
        }
        
        return $out;
    }
    
    /**
     * Garantisce che il filename abbia esattamente il numero di token richiesto
     * 
     * @param string $name Il nome file generato dall'AI
     * @param int $max_words Il numero esatto di token richiesto
     * @param array $context Il contesto per completare se necessario
     * @param string $lang La lingua per il processing
     * @return string Il nome file con il numero esatto di token
     */
    private function imgseo_enforce_exact_tokens(string $name, int $max_words, array $context, string $lang): string {
        // Normalizza il nome
        $name = $this->imgseo_normalize_filename($name);
        
        // Estrai i token
        $tokens = array_values(array_filter(explode('-', $name), fn($t) => $t !== ''));
        
        // Deduplica preservando l'ordine
        $seen = [];
        $tokens = array_values(array_filter($tokens, function($t) use (&$seen) {
            if (isset($seen[$t])) {
                return false;
            }
            $seen[$t] = true;
            return true;
        }));
        
        // Se abbiamo troppi token, tronca
        if (count($tokens) > $max_words) {
            $tokens = array_slice($tokens, 0, $max_words);
        }
        // Se abbiamo pochi token, completa dal contesto
        elseif (count($tokens) < $max_words) {
            $extras = $this->imgseo_tokens_from_context($context, $lang);
            
            // Filtra token già presenti
            $extras = array_values(array_filter($extras, fn($e) => !isset($seen[$e])));
            
            // Aggiungi token fino al limite
            foreach ($extras as $extra) {
                $tokens[] = $extra;
                $seen[$extra] = true;
                
                if (count($tokens) >= $max_words) {
                    break;
                }
            }
            
            // Se ancora non abbiamo abbastanza token, usa fallback
            if (count($tokens) < $max_words) {
                $fallbacks = stripos($lang, 'ital') !== false
                    ? ['immagine', 'foto', 'visual', 'media', 'file']
                    : ['image', 'photo', 'visual', 'media', 'file'];
                
                foreach ($fallbacks as $fallback) {
                    if (!isset($seen[$fallback])) {
                        $tokens[] = $fallback;
                        $seen[$fallback] = true;
                        
                        if (count($tokens) >= $max_words) {
                            break;
                        }
                    }
                }
            }
        }
        
        // Assicurati di avere esattamente max_words token
        $tokens = array_slice($tokens, 0, $max_words);
        
        return implode('-', $tokens);
    }
}
