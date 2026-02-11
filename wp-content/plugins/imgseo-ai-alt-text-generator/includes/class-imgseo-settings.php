<?php

/**

 * Class ImgSEO_Settings

 * Manages all plugin settings and admin pages

 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class ImgSEO_Settings {



/**

 * Singleton instance

 */

private static $instance = null;



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

 * Constructor

 */

private function __construct() {

    // Register settings

    add_action('admin_init', array($this, 'register_settings'));

    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));



    add_action('wp_footer', array($this, 'add_footer_badge'));



    $this->register_shortcodes();



    // Add redirect filter to active tab

    // Solo sui salvataggi delle impostazioni ImgSEO per evitare conflitti SEO
    add_action('admin_init', array($this, 'handle_imgseo_form_submission'));

}



    // Il metodo add_admin_menu è stato rimosso e spostato in ImgSEO_Menu_Manager



    /**

     * Registers all plugin settings

     */

    public function register_settings() {

        // API Section

        register_setting('imgseo_api_settings', 'imgseo_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));

        register_setting('imgseo_api_settings', 'imgseo_api_verified', array(
            'sanitize_callback' => 'absint'
        ));



        // Custom Prompt Section
        register_setting('imgseo_prompt_settings', 'imgseo_custom_prompt', array(
            'default' => 'Carefully analyze the image and generate an SEO-friendly alt text that accurately describes the main visual elements. {page_title_info} {image_name_info} Include relevant keywords extracted from the page title and file name when available. Describe the image concisely yet comprehensively in {language}, using exactly {max_characters} characters or getting as close as possible to this limit. The description should be natural, informative, and optimized for search engines. Provide only the alt text without quotation marks, containing apostrophes, or other textual decorations.',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        // WooCommerce Product Prompt Section
        register_setting('imgseo_prompt_settings', 'imgseo_woocommerce_prompt', array(
            'default' => 'Generate alt text for this product image: {product_name} by {product_brand}\n\nContext: {product_short_description} - {product_categories} - {product_price} {on_sale} - {product_attributes}\n\nDescribe the visual elements shown (colors, style, angle, details) while naturally incorporating the product name. Keep it descriptive, SEO-friendly, under {max_characters} characters in {language}. Focus on what customers would want to know about this product image.',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        // Enable WooCommerce Specific Prompt
        register_setting('imgseo_prompt_settings', 'imgseo_enable_woocommerce_prompt', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));



        // General Section

        register_setting('imgseo_general_settings', 'imgseo_language', array(

            'default' => 'english',

            'sanitize_callback' => 'sanitize_text_field'

        ));

        register_setting('imgseo_general_settings', 'imgseo_max_characters', array(

            'default' => 125,

            'sanitize_callback' => 'absint'

        ));

        register_setting('imgseo_general_settings', 'imgseo_include_page_title', array(

            'default' => 1,

            'sanitize_callback' => 'absint'

        ));

        register_setting('imgseo_general_settings', 'imgseo_include_image_name', array(

            'default' => 1,

            'sanitize_callback' => 'absint'

        ));

        register_setting('imgseo_general_settings', 'imgseo_overwrite', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));

        // registro impostazioni renamer

        // ========== IMPOSTAZIONI AI ==========

        // Impostazioni per generazione AI
        register_setting('imgseo_renamer_settings', 'imgseo_renamer_ai_max_words', array(
            'default' => 4,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_ai_include_post_title', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_ai_include_category', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_ai_include_alt_text', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        // ========== NUOVE IMPOSTAZIONI ==========

        // Opzioni di sanitizzazione
        register_setting('imgseo_renamer_settings', 'imgseo_renamer_remove_accents', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_lowercase', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        // Opzioni per duplicati
        register_setting('imgseo_renamer_settings', 'imgseo_renamer_handle_duplicates', array(
            'default' => 'increment',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Supporto page builder
        register_setting('imgseo_renamer_settings', 'imgseo_renamer_elementor_support', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_visualcomposer_support', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_divi_support', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_renamer_settings', 'imgseo_renamer_beaver_support', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));



        // Automatic Generation Option (moved to general settings)

        register_setting('imgseo_general_settings', 'imgseo_auto_generate', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));

        // Always use base64 option to bypass hotlinking protections and Cloudflare restrictions
        register_setting('imgseo_general_settings', 'imgseo_always_use_base64', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        // Delete data on uninstall option
        register_setting('imgseo_general_settings', 'imgseo_delete_data_on_uninstall', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        // Image Compression Settings
        register_setting('imgseo_compression_settings', 'imgseo_compression_enabled', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_quality', array(
            'default' => 80,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_format', array(
            'default' => 'auto',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Force base64 for compression requests (specific to compression feature)
        register_setting('imgseo_compression_settings', 'imgseo_compression_always_use_base64', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_auto_upload', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_backup_original', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_max_width', array(
            'default' => 1920,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_max_height', array(
            'default' => 1080,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_enable_webp', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_enable_avif', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));


        // Qualità specifiche per formato
        register_setting('imgseo_compression_settings', 'imgseo_compression_webp_quality', array(
            'default' => 50,
            'sanitize_callback' => array($this, 'sanitize_quality_value')
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_avif_quality', array(
            'default' => 30,
            'sanitize_callback' => array($this, 'sanitize_quality_value')
        ));

        // Ottimizzazioni API critiche
        register_setting('imgseo_compression_settings', 'imgseo_compression_optimize_web', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_compression_settings', 'imgseo_compression_strip_metadata', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        // Controllo rimozione formati più grandi
        register_setting('imgseo_compression_settings', 'imgseo_compression_auto_remove_larger', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));

        // Modalità serving
        register_setting('imgseo_compression_settings', 'imgseo_compression_serving_method', array(
            'default' => 'picture',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Footer badge settings

        register_setting('imgseo_general_settings', 'imgseo_footer_badge', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));



        register_setting('imgseo_general_settings', 'imgseo_support_link', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));



        // Field Updates Section

        register_setting('imgseo_update_settings', 'imgseo_update_title', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));

        register_setting('imgseo_update_settings', 'imgseo_update_caption', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));

        register_setting('imgseo_update_settings', 'imgseo_update_description', array(

            'default' => 0,

            'sanitize_callback' => 'absint'

        ));

        // Note: filename generation moved to Renamer Settings (imgseo_auto_rename_on_upload)
        // The old imgseo_update_filename option is deprecated

        // Metadata Fields Prompts (for /api-v1-metadata endpoint)
        register_setting('imgseo_prompt_settings', 'imgseo_title_prompt', array(
            'default' => 'Create an informative title attribute for an image. The text must be in {language} and no more than {max_length} characters. It should provide a concise, keyword-rich description (ideal for a tooltip) based on the Page Title ({page_title_info}) and Image Filename ({image_name_info}). This text can be similar to the alt text but may be slightly more focused on keywords.',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        register_setting('imgseo_prompt_settings', 'imgseo_caption_prompt', array(
            'default' => 'Write a brief and engaging image caption in {language}, with a maximum of {max_length} characters. The caption should be conversational, complement the image by adding context or insight (what, where, when, or why), and be relevant to the Page Title ({page_title_info}). Use {image_name_info} for thematic context.',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        register_setting('imgseo_prompt_settings', 'imgseo_description_prompt', array(
            'default' => 'Generate a comprehensive, SEO-friendly description for an image, suitable for surrounding text or an accessibility description. The description must be in {language} and under {max_length} characters. It should expand significantly on the alt text, providing greater detail, context, and purpose. Naturally integrate primary and secondary keywords from the Page Title ({page_title_info}) and Image Filename ({image_name_info}) to improve search engine ranking and provide rich context.',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        // Note: filename prompt removed - now managed in Renamer Settings

        // Max lengths for metadata fields
        register_setting('imgseo_update_settings', 'imgseo_title_max_length', array(
            'default' => 60,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_update_settings', 'imgseo_caption_max_length', array(
            'default' => 150,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_update_settings', 'imgseo_description_max_length', array(
            'default' => 500,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_update_settings', 'imgseo_filename_max_words', array(
            'default' => 4,
            'sanitize_callback' => 'absint'
        ));

        register_setting('imgseo_update_settings', 'imgseo_filename_max_length', array(
            'default' => 50,
            'sanitize_callback' => 'absint'
        ));



        // Add sections

        add_settings_section(

            'imgseo_api_section',

            __('API Settings', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_api_section'),

            'imgseo_api_settings'

        );



        // Custom Prompt Section
        add_settings_section(
            'imgseo_prompt_section',
            __('Custom Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_prompt_section'),
            'imgseo_prompt_settings'
        );

        // Custom Prompt Field (Alt Text)
        add_settings_field(
            'imgseo_custom_prompt',
            __('Alt Text Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_custom_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_prompt_section'
        );

        // Title Prompt Field
        add_settings_field(
            'imgseo_title_prompt',
            __('Title Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_title_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_prompt_section'
        );

        // Caption Prompt Field
        add_settings_field(
            'imgseo_caption_prompt',
            __('Caption Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_caption_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_prompt_section'
        );

        // Description Prompt Field
        add_settings_field(
            'imgseo_description_prompt',
            __('Description Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_description_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_prompt_section'
        );

        // Note: Filename prompt field removed - now managed in Renamer Settings

        // WooCommerce Prompt Section
        add_settings_section(
            'imgseo_woocommerce_prompt_section',
            __('WooCommerce Product Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_woocommerce_prompt_section'),
            'imgseo_prompt_settings'
        );

        // Enable WooCommerce Prompt Field
        add_settings_field(
            'imgseo_enable_woocommerce_prompt',
            __('Enable WooCommerce Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_enable_woocommerce_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_woocommerce_prompt_section'
        );

        // WooCommerce Prompt Field
        add_settings_field(
            'imgseo_woocommerce_prompt',
            __('WooCommerce Product Prompt', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_woocommerce_prompt_field'),
            'imgseo_prompt_settings',
            'imgseo_woocommerce_prompt_section'
        );



        add_settings_section(

            'imgseo_general_section',

            __('General Settings', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_general_section'),

            'imgseo_general_settings'

        );





        add_settings_section(

            'imgseo_update_section',

            __('Field Updates', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_update_section'),

            'imgseo_update_settings'

        );

        // Field Update: Title
        add_settings_field(
            'imgseo_update_title',
            __('Update Image Title', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_update_title_field'),
            'imgseo_update_settings',
            'imgseo_update_section'
        );

        // Field Update: Caption
        add_settings_field(
            'imgseo_update_caption',
            __('Update Image Caption', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_update_caption_field'),
            'imgseo_update_settings',
            'imgseo_update_section'
        );

        // Field Update: Description
        add_settings_field(
            'imgseo_update_description',
            __('Update Image Description', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_update_description_field'),
            'imgseo_update_settings',
            'imgseo_update_section'
        );

        // Note: Filename field removed - now managed in Renamer Settings as "Auto-Rename on Upload"


        // aggiungo sezione renamer
          // ========== SEZIONI IMPOSTAZIONI ==========

        // Sezione generale
        add_settings_section(
            'imgseo_renamer_general_section',
            __('General Settings', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_general_section'),
            'imgseo_renamer_settings'
        );

        // Sanitization section
        add_settings_section(
            'imgseo_renamer_sanitization_section',
            __('Sanitization Options', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_sanitization_section'),
            'imgseo_renamer_settings'
        );

        // AI Section
        add_settings_section(
            'imgseo_renamer_ai_section',
            __('AI Filename Generator', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_ai_section'),
            'imgseo_renamer_settings'
        );

        // Integration section
        add_settings_section(
            'imgseo_renamer_integration_section',
            __('Page Builder Integration', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_integration_section'),
            'imgseo_renamer_settings'
        );

        // Image Compression Section
        add_settings_section(
            'imgseo_compression_section',
            __('Image Compression Settings', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_section'),
            'imgseo_compression_settings'
        );



        // Add fields

        // API Section

        add_settings_field(

            'imgseo_api_key',

            __('ImgSEO Token', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_api_key_field'),

            'imgseo_api_settings',

            'imgseo_api_section'

        );



        add_settings_field(

            'imgseo_credits',

            __('Available Credits', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_credits_field'),

            'imgseo_api_settings',

            'imgseo_api_section'

        );



        // General Section

        add_settings_field(

            'imgseo_language',

            __('Language', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_language_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



        add_settings_field(

            'imgseo_max_characters',

            __('Maximum Characters', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_max_characters_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



        add_settings_field(

            'imgseo_include_page_title',

            __('Include Page Title', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_include_page_title_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



        add_settings_field(

            'imgseo_include_image_name',

            __('Include Image Filename', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_include_image_name_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



        add_settings_field(

            'imgseo_overwrite',

            __('Overwrite Existing Alt Texts', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_overwrite_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



        // Add automatic generation field to general settings

        add_settings_field(

            'imgseo_auto_generate',

            __('Automatic Generation', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_auto_generate_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );



          // Add footer badge field to general settings

          add_settings_field(

            'imgseo_footer_badge',

            __('Footer badge', 'imgseo-ai-alt-text-generator'),

            array($this, 'render_footer_badge_field'),

            'imgseo_general_settings',

            'imgseo_general_section'

        );


        // Add always use base64 field to general settings
        add_settings_field(
            'imgseo_always_use_base64',
            __('Force base64 image transfer', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_always_use_base64_field'),
            'imgseo_general_settings',
            'imgseo_general_section'
        );

        // Database Maintenance Section
        add_settings_field(
            'imgseo_reset_logs_cache',
            __('Database Maintenance', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_database_maintenance_field'),
            'imgseo_general_settings',
            'imgseo_general_section'
        );





        // (Removed) Legacy aggregated "Update Other Fields" UI — duplicated by individual field toggles

        // ========== CAMPI PER SEZIONE AI ==========

        // Campo per numero massimo di parole
        add_settings_field(
            'imgseo_renamer_ai_max_words',
            __('Max Words in AI Generated Filename', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_number_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_ai_section',
            array(
                'name' => 'imgseo_renamer_ai_max_words',
                'min' => 2,
                'max' => 10,
                'description' => __('Number of words to use in AI generated filenames', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campi per le opzioni di contesto
        add_settings_field(
            'imgseo_renamer_ai_include_post_title',
            __('Include Post Title in Context', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_ai_section',
            array(
                'name' => 'imgseo_renamer_ai_include_post_title',
                'description' => __('Include post title in AI prompt for better context', 'imgseo-ai-alt-text-generator')
            )
        );

        add_settings_field(
            'imgseo_renamer_ai_include_category',
            __('Include Category in Context', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_ai_section',
            array(
                'name' => 'imgseo_renamer_ai_include_category',
                'description' => __('Include post category in AI prompt for better context', 'imgseo-ai-alt-text-generator')
            )
        );

        add_settings_field(
            'imgseo_renamer_ai_include_alt_text',
            __('Include Alt Text in Context', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_ai_section',
            array(
                'name' => 'imgseo_renamer_ai_include_alt_text',
                'description' => __('Include image alt text in AI prompt for better context', 'imgseo-ai-alt-text-generator')
            )
        );

        // ========== CAMPI PER SEZIONE SANITIZZAZIONE ==========

        // Campo per rimuovere accenti
        add_settings_field(
            'imgseo_renamer_remove_accents',
            __('Remove Accents', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_sanitization_section',
            array(
                'name' => 'imgseo_renamer_remove_accents',
                'description' => __('Remove accents from filenames', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campo per conversione in minuscolo
        add_settings_field(
            'imgseo_renamer_lowercase',
            __('Convert to Lowercase', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_sanitization_section',
            array(
                'name' => 'imgseo_renamer_lowercase',
                'description' => __('Convert filenames to lowercase', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campo per gestione duplicati
        add_settings_field(
            'imgseo_renamer_handle_duplicates',
            __('Duplicate Handling', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_select_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_sanitization_section',
            array(
                'name' => 'imgseo_renamer_handle_duplicates',
                'description' => __('How to handle duplicate filenames', 'imgseo-ai-alt-text-generator'),
                'options' => array(
                    'increment' => __('Add sequential number (file-1.jpg, file-2.jpg)', 'imgseo-ai-alt-text-generator'),
                    'timestamp' => __('Add timestamp (file-1679419361.jpg)', 'imgseo-ai-alt-text-generator'),
                    'fail' => __('Do not rename if already exists', 'imgseo-ai-alt-text-generator'),
                )
            )
        );
        // aggiungo field renamer
        // ========== CAMPI PER SEZIONE INTEGRAZIONE ==========

        // Campo per supporto Elementor
        add_settings_field(
            'imgseo_renamer_elementor_support',
            __('Elementor Support', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_integration_section',
            array(
                'name' => 'imgseo_renamer_elementor_support',
                'description' => __('Update image references in Elementor', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campo per supporto Visual Composer
        add_settings_field(
            'imgseo_renamer_visualcomposer_support',
            __('Visual Composer / WPBakery Support', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_integration_section',
            array(
                'name' => 'imgseo_renamer_visualcomposer_support',
                'description' => __('Update image references in Visual Composer / WPBakery', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campo per supporto Divi
        add_settings_field(
            'imgseo_renamer_divi_support',
            __('Divi Support', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_integration_section',
            array(
                'name' => 'imgseo_renamer_divi_support',
                'description' => __('Update image references in Divi', 'imgseo-ai-alt-text-generator')
            )
        );

        // Campo per supporto Beaver Builder
        add_settings_field(
            'imgseo_renamer_beaver_support',
            __('Beaver Builder Support', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_integration_section',
            array(
                'name' => 'imgseo_renamer_beaver_support',
                'description' => __('Update image references in Beaver Builder', 'imgseo-ai-alt-text-generator')
            )
        );

        // Image Compression Fields
        add_settings_field(
            'imgseo_compression_enabled',
            __('Enable Image Compression', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_enabled_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_quality',
            __('Main Image Quality (%)', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_quality_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_webp_quality',
            __('WebP Quality (%)', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_webp_quality_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_avif_quality',
            __('AVIF Quality (%)', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_avif_quality_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_optimize_web',
            __('Optimize for Web', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_optimize_web_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_strip_metadata',
            __('Strip Metadata', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_strip_metadata_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_format',
            __('Output Format', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_format_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_always_use_base64',
            __('Force base64 image transfer', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_always_use_base64_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_auto_upload',
            __('Auto-compress on Upload', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_auto_upload_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_backup_original',
            __('Backup Original Images', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_backup_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_max_width',
            __('Maximum Width (px)', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_max_width_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_max_height',
            __('Maximum Height (px)', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_max_height_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_enable_webp',
            __('Generate WebP Format', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_webp_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_enable_avif',
            __('Generate AVIF Format', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_avif_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );


        add_settings_field(
            'imgseo_compression_auto_remove_larger',
            __('Auto Remove Larger Files', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_auto_remove_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

        add_settings_field(
            'imgseo_compression_serving_method',
            __('Serving Method', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_compression_serving_method_field'),
            'imgseo_compression_settings',
            'imgseo_compression_section'
        );

    }



    /**

     * Function to redirect to the active tab after saving

     */

    /**
     * Gestisce i form submission specifici di ImgSEO senza interferire con altri plugin
     */
    public function handle_imgseo_form_submission() {
        // Controlla solo sui POST e per pagine ImgSEO
        if (!isset($_POST['option_page']) || !isset($_POST['imgseo_active_tab'])) {
            return;
        }

        $option_page = wp_unslash($_POST['option_page']);
        if (strpos($option_page, 'imgseo_') !== 0) {
            return;
        }

        // Se stiamo processando un form ImgSEO, registra un redirect personalizzato
        add_filter('wp_redirect', array($this, 'redirect_to_active_tab'), 10, 2);
    }

    /**
     * Redirect migliorato - ora più specifico per evitare conflitti SEO
     */
    public function redirect_to_active_tab($location, $status) {
        // Più specifico: solo per salvataggi ImgSEO
        if (
            strpos($location, 'options.php') !== false &&
            isset($_POST['imgseo_active_tab']) &&
            isset($_POST['option_page']) &&
            strpos(wp_unslash($_POST['option_page']), 'imgseo_') === 0
        ) {

            $active_tab = sanitize_text_field(wp_unslash($_POST['imgseo_active_tab']));

            // Build the redirect URL with the active tab
            $redirect_url = add_query_arg(
                array(
                    'page' => 'imgseo-ai-alt-text-generator',
                    'tab' => $active_tab,
                    'settings-updated' => 'true'
                ),
                admin_url('admin.php')
            );

            // Rimuovi il filtro dopo l'uso per non interferire con altri redirect
            remove_filter('wp_redirect', array($this, 'redirect_to_active_tab'), 10);

            return $redirect_url;
        }

        return $location;

    }



    /**

     * Aggiorna le impostazioni tramite AJAX

     */

    public function ajax_update_settings() {

        check_ajax_referer('imgseo_settings_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        // Security: Rate limiting - max 60 settings updates per 5 minutes per user
        $user_id = get_current_user_id();
        $rate_key = 'imgseo_settings_update_attempts_' . $user_id;
        $attempts = get_transient($rate_key) ?: 0;

        if ($attempts >= 60) {
            wp_send_json_error(['message' => __('Too many settings update attempts. Please wait 5 minutes.', 'imgseo-ai-alt-text-generator')]);
        }

        set_transient($rate_key, $attempts + 1, 300); // 5 minutes



        $update_title = isset($_POST['update_title']) ? (bool)sanitize_text_field(wp_unslash($_POST['update_title'])) : false;

        $update_caption = isset($_POST['update_caption']) ? (bool)sanitize_text_field(wp_unslash($_POST['update_caption'])) : false;

        $update_description = isset($_POST['update_description']) ? (bool)sanitize_text_field(wp_unslash($_POST['update_description'])) : false;



        update_option('imgseo_update_title', $update_title ? 1 : 0);

        update_option('imgseo_update_caption', $update_caption ? 1 : 0);

        update_option('imgseo_update_description', $update_description ? 1 : 0);



        wp_send_json_success(['message' => 'Impostazioni aggiornate']);

    }



    /**

     * Carica gli script e gli stili necessari per le pagine di amministrazione

     */

    public function enqueue_admin_assets($hook) {

        // Carica gli asset solo nelle pagine del plugin

        if (strpos($hook, 'imgseo') === false) {

            return;

        }



        // CSS per le pagine admin

        wp_enqueue_style(

            'imgseo-admin-css',

            IMGSEO_PLUGIN_URL . 'assets/css/admin-style.css',

            array(),

            IMGSEO_PLUGIN_VERSION

        );



        // Script per l'API

        wp_enqueue_script(

            'imgseo-api-settings-js',

            IMGSEO_PLUGIN_URL . 'assets/js/imgseo-api-settings.js',

            array('jquery'),

            IMGSEO_PLUGIN_VERSION,

            true

        );



        // Localizza lo script con i parametri

        wp_localize_script('imgseo-api-settings-js', 'ImgSEOSettings', array(

            'ajax_url' => admin_url('admin-ajax.php'),

            'nonce' => wp_create_nonce('imgseo_settings_nonce'),

            'settings_url' => admin_url('admin.php?page=imgseo-ai-alt-text-generator'),

            'verify_message' => esc_html__('Verification in progress...', 'imgseo-ai-alt-text-generator'),

            'success_message' => esc_html__('Valid ImgSEO Token!', 'imgseo-ai-alt-text-generator'),

            'error_message' => esc_html__('Invalid ImgSEO Token!', 'imgseo-ai-alt-text-generator'),

            'refresh_credits_message' => esc_html__('Updating credits...', 'imgseo-ai-alt-text-generator'),

            'debug' => defined('IMGSEO_DEBUG_MODE') && IMGSEO_DEBUG_MODE

        ));



        // Script per l'admin

        wp_enqueue_script(

            'imgseo-admin-js',

            IMGSEO_PLUGIN_URL . 'assets/js/admin-script.js',

            array('jquery'),

            IMGSEO_PLUGIN_VERSION,

            true

        );



        try {

            // Prepare base data with types explicitly cast to ensure valid JS

            $localize_data = array(

                'ajax_url' => admin_url('admin-ajax.php'),

                'nonce' => wp_create_nonce('imgseo_nonce'),

                'debug' => (bool)WP_DEBUG

            );



            // Apply the filter to allow other components to add data, with error handling

            if (has_filter('imgseo_localize_script')) {

                $filtered_data = apply_filters('imgseo_localize_script', $localize_data);

                // Verify the filter returned an array

                if (is_array($filtered_data)) {

                    $localize_data = $filtered_data;

                } else {


                }

            }



            // Log the data for debugging

            if (WP_DEBUG) {


            }



            // Localize the script

            wp_localize_script('imgseo-admin-js', 'ImgSEO', $localize_data);

        } catch (Exception $e) {

            // Log any errors but don't break the script




            // Provide minimal working data in case of error

            wp_localize_script('imgseo-admin-js', 'ImgSEO', array(

                'ajax_url' => admin_url('admin-ajax.php'),

                'nonce' => wp_create_nonce('imgseo_nonce')

            ));

        }

    }



    /**

     * Rendering della pagina delle impostazioni

     */

    public function render_settings_page() {

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'imgseo-ai-alt-text-generator'));
        }



        // Determina la tab attiva

        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'api';

        ?>

        <div class="wrap">

            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php
            // Show warning if API token is marked as invalid
            if (get_transient('imgseo_invalid_api_token') === true) {
                $invalid_code = get_transient('imgseo_invalid_token_code');
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php esc_html_e('ImgSEO API Error:', 'imgseo-ai-alt-text-generator'); ?></strong>
                        <?php
                        printf(
                            /* translators: %s: HTTP status code (e.g. 401/403). */
                            esc_html__('Your API key is invalid or has expired (HTTP %s). All API operations have been temporarily disabled to prevent excessive failed requests. Please update your API key below to restore functionality.', 'imgseo-ai-alt-text-generator'),
                            esc_html($invalid_code ? $invalid_code : '401/403')
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
            ?>

            <div class="imgseo-tabs">

                <div class="nav-tab-wrapper">

                    <a href="?page=imgseo-ai-alt-text-generator&tab=api" class="nav-tab <?php echo $active_tab == 'api' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('API Settings', 'imgseo-ai-alt-text-generator'); ?></a>

                    <a href="?page=imgseo-ai-alt-text-generator&tab=prompt" class="nav-tab <?php echo $active_tab == 'prompt' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Custom Prompt', 'imgseo-ai-alt-text-generator'); ?></a>

                    <a href="?page=imgseo-ai-alt-text-generator&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General Settings', 'imgseo-ai-alt-text-generator'); ?></a>

                    <a href="?page=imgseo-ai-alt-text-generator&tab=update" class="nav-tab <?php echo $active_tab == 'update' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Field Updates', 'imgseo-ai-alt-text-generator'); ?></a>

                    <a href="?page=imgseo-ai-alt-text-generator&tab=renamer" class="nav-tab <?php echo $active_tab == 'renamer' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Renamer Settings', 'imgseo-ai-alt-text-generator'); ?></a>

                </div>





                <div id="tab-api" class="tab-content <?php echo $active_tab == 'api' ? 'active' : ''; ?>">

                    <div class="imgseo-api-wrapper">

                        <?php

                        settings_fields('imgseo_api_settings');

                        do_settings_sections('imgseo_api_settings');

                        ?>

                        <!-- Il pulsante "Salva le modifiche" è stato rimosso perché non necessario in questa tab -->

                        <!-- La API Key viene salvata automaticamente durante la verifica -->

                    </div>

                </div>



                <div id="tab-prompt" class="tab-content <?php echo $active_tab == 'prompt' ? 'active' : ''; ?>">

                    <form method="post" action="options.php">

                        <input type="hidden" name="imgseo_active_tab" value="prompt">

                        <?php

                        settings_fields('imgseo_prompt_settings');

                        do_settings_sections('imgseo_prompt_settings');

                         ?> <input type="submit" id="submit" class="btn-custom-primary" value="<?php esc_attr_e('Save Changes', 'imgseo-ai-alt-text-generator'); ?>">

                    </form>

                </div>



                <div id="tab-general" class="tab-content <?php echo $active_tab == 'general' ? 'active' : ''; ?>">

                    <form method="post" action="options.php">

                        <input type="hidden" name="imgseo_active_tab" value="general">

                        <?php

                        settings_fields('imgseo_general_settings');

                        do_settings_sections('imgseo_general_settings');

                        ?> <input type="submit" id="submit" class="btn-custom-primary" value="<?php esc_attr_e('Save Changes', 'imgseo-ai-alt-text-generator'); ?>">

                    </form>

                </div>





                <div id="tab-update" class="tab-content <?php echo $active_tab == 'update' ? 'active' : ''; ?>">

                    <form method="post" action="options.php">

                        <input type="hidden" name="imgseo_active_tab" value="update">

                        <?php

                        settings_fields('imgseo_update_settings');

                        do_settings_sections('imgseo_update_settings');

                         ?> <input type="submit" id="submit" class="btn-custom-primary" value="<?php esc_attr_e('Save Changes', 'imgseo-ai-alt-text-generator'); ?>">

                    </form>

                </div>



                <div id="tab-renamer" class="tab-content <?php echo $active_tab == 'renamer' ? 'active' : ''; ?>">

                    <form method="post" action="options.php">

                        <input type="hidden" name="imgseo_active_tab" value="renamer">

                        <?php

                        settings_fields('imgseo_renamer_settings');

                        do_settings_sections('imgseo_renamer_settings');

                         ?> <input type="submit" id="submit" class="btn-custom-primary" value="<?php esc_attr_e('Save Changes', 'imgseo-ai-alt-text-generator'); ?>">

                    </form>

                </div>

            </div>

        </div>

        <?php

    }



    /**

     * Rendering della pagina di generazione in bulk

     */

    public function render_bulk_page() {

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'imgseo-ai-alt-text-generator'));
        }



        include IMGSEO_DIRECTORY_PATH . 'templates/bulk-page.php';

    }



    /**

     * Render API section

     */

    public function render_api_section() {

        echo '<p>' . esc_html__('Configure your ImgSEO Token to access the ImgSEO service.', 'imgseo-ai-alt-text-generator') . '</p>';

    }



    /**

     * Render general section

     */

    public function render_general_section() {

        echo '<p>' . esc_html__('Configure general settings for alt text generation.', 'imgseo-ai-alt-text-generator') . '</p>';

    }



    /**

     * Render update fields section

     */

    public function render_update_section() {
        ?>
        <p><?php esc_html_e('Configure which metadata fields to generate besides alt text. Each additional field costs +0.5 credits.', 'imgseo-ai-alt-text-generator'); ?></p>
        
        <?php
    }

    /**
     * Render update title field
     */
    public function render_update_title_field() {
        $update_title = get_option('imgseo_update_title', 0);
        $title_max_length = get_option('imgseo_title_max_length', 60);
        ?>
        <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="imgseo_update_title" value="1" class="imgseo-metadata-field-settings" <?php checked(1, $update_title); ?> />
            <?php esc_html_e('Generate and update image title', 'imgseo-ai-alt-text-generator'); ?>
            <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
        </label>
        <p class="description" style="margin-top: 10px;">
            <?php esc_html_e('Generates SEO-optimized title saved to post_title field', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 10px;">
            <label>
                <?php esc_html_e('Maximum characters:', 'imgseo-ai-alt-text-generator'); ?>
                <input type="number" name="imgseo_title_max_length" value="<?php echo esc_attr($title_max_length); ?>" min="10" max="200" style="width: 80px;" />
                <span class="description"><?php esc_html_e('(Default: 60)', 'imgseo-ai-alt-text-generator'); ?></span>
            </label>
        </div>
        <?php
    }

    /**
     * Render update caption field
     */
    public function render_update_caption_field() {
        $update_caption = get_option('imgseo_update_caption', 0);
        $caption_max_length = get_option('imgseo_caption_max_length', 150);
        ?>
        <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="imgseo_update_caption" value="1" class="imgseo-metadata-field-settings" <?php checked(1, $update_caption); ?> />
            <?php esc_html_e('Generate and update image caption', 'imgseo-ai-alt-text-generator'); ?>
            <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
        </label>
        <p class="description" style="margin-top: 10px;">
            <?php esc_html_e('Generates engaging caption saved to post_excerpt field', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 10px;">
            <label>
                <?php esc_html_e('Maximum characters:', 'imgseo-ai-alt-text-generator'); ?>
                <input type="number" name="imgseo_caption_max_length" value="<?php echo esc_attr($caption_max_length); ?>" min="10" max="500" style="width: 80px;" />
                <span class="description"><?php esc_html_e('(Default: 150)', 'imgseo-ai-alt-text-generator'); ?></span>
            </label>
        </div>
        <?php
    }

    /**
     * Render update description field
     */
    public function render_update_description_field() {
        $update_description = get_option('imgseo_update_description', 0);
        $description_max_length = get_option('imgseo_description_max_length', 500);
        ?>
        <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="imgseo_update_description" value="1" class="imgseo-metadata-field-settings" <?php checked(1, $update_description); ?> />
            <?php esc_html_e('Generate and update image description', 'imgseo-ai-alt-text-generator'); ?>
            <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
        </label>
        <p class="description" style="margin-top: 10px;">
            <?php esc_html_e('Generates comprehensive SEO description saved to post_content field', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 10px;">
            <label>
                <?php esc_html_e('Maximum characters:', 'imgseo-ai-alt-text-generator'); ?>
                <input type="number" name="imgseo_description_max_length" value="<?php echo esc_attr($description_max_length); ?>" min="50" max="2000" style="width: 80px;" />
                <span class="description"><?php esc_html_e('(Default: 500)', 'imgseo-ai-alt-text-generator'); ?></span>
            </label>
        </div>
        <?php
    }

    // Note: render_update_filename_field() removed - filename generation now in Renamer Settings



    /**

     * Render custom prompt section

     */

    public function render_prompt_section() {
        ?>
        <p><?php esc_html_e('Customize the prompts used to generate metadata with AI (API v2.0). Each field has its own prompt with specific variables.', 'imgseo-ai-alt-text-generator'); ?></p>

        <div style="background: #f9f9f9; padding: 15px 25px; border-left: 4px solid #6330ED; margin-bottom: 20px;">
            <h4 style="margin-top: 0;"><?php esc_html_e('Available Dynamic Variables', 'imgseo-ai-alt-text-generator'); ?></h4>

            <h5><?php esc_html_e('Common Variables (available for all prompts):', 'imgseo-ai-alt-text-generator'); ?></h5>
            <ul style="margin-top: 5px;">
                <li><code>{language}</code> - <?php esc_html_e('Selected language', 'imgseo-ai-alt-text-generator'); ?></li>
                <li><code>{page_title_info}</code> - <?php esc_html_e('Page title context (if enabled)', 'imgseo-ai-alt-text-generator'); ?></li>
                <li><code>{image_name_info}</code> - <?php esc_html_e('Image filename context (if enabled)', 'imgseo-ai-alt-text-generator'); ?></li>
            </ul>

            <h5><?php esc_html_e('Field-Specific Variables:', 'imgseo-ai-alt-text-generator'); ?></h5>
            <ul style="margin-top: 5px; margin-bottom: 0;">
                <li><code>{max_characters}</code> - <?php esc_html_e('For Alt Text: Maximum number of characters', 'imgseo-ai-alt-text-generator'); ?></li>
                <li><code>{max_length}</code> - <?php esc_html_e('For Title, Caption, Description: Maximum number of characters', 'imgseo-ai-alt-text-generator'); ?></li>
            </ul>
        </div>

        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px;">
            <strong>💡 <?php esc_html_e('Tip:', 'imgseo-ai-alt-text-generator'); ?></strong>
            <?php esc_html_e('Each field is sent as a separate prompt to the AI. Customize each prompt to get the best results for each metadata type. Enable fields in "Field Updates" settings to use them.', 'imgseo-ai-alt-text-generator'); ?>
        </div>
        <?php
    }



    /**
     * Render custom prompt field
     */
    public function render_custom_prompt_field() {
        $default_prompt = 'Carefully analyze the image and generate an SEO-friendly alt text that accurately describes the main visual elements. {page_title_info} {image_name_info} Include relevant keywords extracted from the page title and file name when available. Describe the image concisely yet comprehensively in {language}, using exactly {max_characters} characters or getting as close as possible to this limit. The description should be natural, informative, and optimized for search engines. Provide only the alt text without quotation marks, containing apostrophes, or other textual decorations.';
        $custom_prompt = get_option('imgseo_custom_prompt', $default_prompt);
        ?>
        <textarea name="imgseo_custom_prompt" id="imgseo_custom_prompt" rows="6" cols="80" class="large-text code"><?php echo esc_textarea($custom_prompt); ?></textarea>

        <p class="description">
            <?php esc_html_e('Customize the prompt sent to the AI to generate alt text. Use the dynamic variables listed above to make the prompt more effective.', 'imgseo-ai-alt-text-generator'); ?>
        </p>

        <button type="button" id="reset_prompt" class="button btn-custom-secondary" style="margin-top: 10px;">
            <span class="dashicons dashicons-image-rotate" style="margin-top: 3px;"></span>
            <?php esc_html_e('Reset Default Prompt', 'imgseo-ai-alt-text-generator'); ?>
        </button>

        <script>
            jQuery(document).ready(function($) {
                $('#reset_prompt').on('click', function() {
                    if (confirm('<?php echo esc_js(__('Are you sure you want to reset to the default prompt? This operation will overwrite the current prompt.', 'imgseo-ai-alt-text-generator')); ?>')) {
                        $('#imgseo_custom_prompt').val('Carefully analyze the image and generate an SEO-friendly alt text that accurately describes the main visual elements. {page_title_info} {image_name_info} Include relevant keywords extracted from the page title and file name when available. Describe the image concisely yet comprehensively in {language}, using exactly {max_characters} characters or getting as close as possible to this limit. The description should be natural, informative, and optimized for search engines. Provide only the alt text without quotation marks, containing apostrophes, or other textual decorations.');
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Render title prompt field
     */
    public function render_title_prompt_field() {
        $default_prompt = 'Create an informative title attribute for an image. The text must be in {language} and no more than {max_length} characters. It should provide a concise, keyword-rich description (ideal for a tooltip) based on the Page Title ({page_title_info}) and Image Filename ({image_name_info}). This text can be similar to the alt text but may be slightly more focused on keywords.';
        $title_prompt = get_option('imgseo_title_prompt', $default_prompt);
        $is_title_enabled = (bool) get_option('imgseo_update_title', 0);
        $field_updates_tab_url = admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=update');
        $title_indicator_color = $is_title_enabled ? '#2ecc71' : '#e74c3c';
        $title_indicator_label = $is_title_enabled ? __('Field Update: active', 'imgseo-ai-alt-text-generator') : __('Field Update: not active', 'imgseo-ai-alt-text-generator');
        ?>
        <textarea name="imgseo_title_prompt" id="imgseo_title_prompt" rows="4" cols="80" class="large-text code"><?php echo esc_textarea($title_prompt); ?></textarea>
        <p class="description">
            <?php esc_html_e('Prompt for generating image title. Available variables: {language}, {max_length}, {page_title_info}, {image_name_info}', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr($title_indicator_color); ?>; display: inline-block;"></span>
            <span style="font-size: 12px; color: #555;"><?php echo esc_html($title_indicator_label); ?></span>
            <a href="<?php echo esc_url($field_updates_tab_url); ?>" style="font-size: 12px; color: #2271b1; text-decoration: underline;">
                <?php esc_html_e('Go to Field Updates tab', 'imgseo-ai-alt-text-generator'); ?>
            </a>
        </div>
        <button type="button" class="button btn-custom-secondary reset-prompt" data-target="imgseo_title_prompt" data-default="Create an informative title attribute for an image. The text must be in {language} and no more than {max_length} characters. It should provide a concise, keyword-rich description (ideal for a tooltip) based on the Page Title ({page_title_info}) and Image Filename ({image_name_info}). This text can be similar to the alt text but may be slightly more focused on keywords." style="margin-top: 10px;">
            <span class="dashicons dashicons-image-rotate" style="margin-top: 3px;"></span>
            <?php esc_html_e('Reset Default', 'imgseo-ai-alt-text-generator'); ?>
        </button>
        <?php
    }

    /**
     * Render caption prompt field
     */
    public function render_caption_prompt_field() {
        $default_prompt = 'Write a brief and engaging image caption in {language}, with a maximum of {max_length} characters. The caption should be conversational, complement the image by adding context or insight (what, where, when, or why), and be relevant to the Page Title ({page_title_info}). Use {image_name_info} for thematic context.';
        $caption_prompt = get_option('imgseo_caption_prompt', $default_prompt);
        $is_caption_enabled = (bool) get_option('imgseo_update_caption', 0);
        $field_updates_tab_url = admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=update');
        $caption_indicator_color = $is_caption_enabled ? '#2ecc71' : '#e74c3c';
        $caption_indicator_label = $is_caption_enabled ? __('Field Update: active', 'imgseo-ai-alt-text-generator') : __('Field Update: not active', 'imgseo-ai-alt-text-generator');
        ?>
        <textarea name="imgseo_caption_prompt" id="imgseo_caption_prompt" rows="4" cols="80" class="large-text code"><?php echo esc_textarea($caption_prompt); ?></textarea>
        <p class="description">
            <?php esc_html_e('Prompt for generating image caption. Available variables: {language}, {max_length}, {page_title_info}, {image_name_info}', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr($caption_indicator_color); ?>; display: inline-block;"></span>
            <span style="font-size: 12px; color: #555;"><?php echo esc_html($caption_indicator_label); ?></span>
            <a href="<?php echo esc_url($field_updates_tab_url); ?>" style="font-size: 12px; color: #2271b1; text-decoration: underline;">
                <?php esc_html_e('Go to Field Updates tab', 'imgseo-ai-alt-text-generator'); ?>
            </a>
        </div>
        <button type="button" class="button btn-custom-secondary reset-prompt" data-target="imgseo_caption_prompt" data-default="Write a brief and engaging image caption in {language}, with a maximum of {max_length} characters. The caption should be conversational, complement the image by adding context or insight (what, where, when, or why), and be relevant to the Page Title ({page_title_info}). Use {image_name_info} for thematic context." style="margin-top: 10px;">
            <span class="dashicons dashicons-image-rotate" style="margin-top: 3px;"></span>
            <?php esc_html_e('Reset Default', 'imgseo-ai-alt-text-generator'); ?>
        </button>
        <?php
    }

    /**
     * Render description prompt field
     */
    public function render_description_prompt_field() {
        $default_prompt = 'Generate a comprehensive, SEO-friendly description for an image, suitable for surrounding text or an accessibility description. The description must be in {language} and under {max_length} characters. It should expand significantly on the alt text, providing greater detail, context, and purpose. Naturally integrate primary and secondary keywords from the Page Title ({page_title_info}) and Image Filename ({image_name_info}) to improve search engine ranking and provide rich context.';
        $description_prompt = get_option('imgseo_description_prompt', $default_prompt);
        $is_description_enabled = (bool) get_option('imgseo_update_description', 0);
        $field_updates_tab_url = admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=update');
        $description_indicator_color = $is_description_enabled ? '#2ecc71' : '#e74c3c';
        $description_indicator_label = $is_description_enabled ? __('Field Update: active', 'imgseo-ai-alt-text-generator') : __('Field Update: not active', 'imgseo-ai-alt-text-generator');
        ?>
        <textarea name="imgseo_description_prompt" id="imgseo_description_prompt" rows="4" cols="80" class="large-text code"><?php echo esc_textarea($description_prompt); ?></textarea>
        <p class="description">
            <?php esc_html_e('Prompt for generating image description. Available variables: {language}, {max_length}, {page_title_info}, {image_name_info}', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo esc_attr($description_indicator_color); ?>; display: inline-block;"></span>
            <span style="font-size: 12px; color: #555;"><?php echo esc_html($description_indicator_label); ?></span>
            <a href="<?php echo esc_url($field_updates_tab_url); ?>" style="font-size: 12px; color: #2271b1; text-decoration: underline;">
                <?php esc_html_e('Go to Field Updates tab', 'imgseo-ai-alt-text-generator'); ?>
            </a>
        </div>
        <button type="button" class="button btn-custom-secondary reset-prompt" data-target="imgseo_description_prompt" data-default="Generate a comprehensive, SEO-friendly description for an image, suitable for surrounding text or an accessibility description. The description must be in {language} and under {max_length} characters. It should expand significantly on the alt text, providing greater detail, context, and purpose. Naturally integrate primary and secondary keywords from the Page Title ({page_title_info}) and Image Filename ({image_name_info}) to improve search engine ranking and provide rich context." style="margin-top: 10px;">
            <span class="dashicons dashicons-image-rotate" style="margin-top: 3px;"></span>
            <?php esc_html_e('Reset Default', 'imgseo-ai-alt-text-generator'); ?>
        </button>

        <script>
        jQuery(document).ready(function($) {
            // Generic reset handler for all prompt fields
            $('.reset-prompt').on('click', function() {
                var target = $(this).data('target');
                var defaultValue = $(this).data('default');
                if (confirm('<?php echo esc_js(__('Are you sure you want to reset to the default prompt?', 'imgseo-ai-alt-text-generator')); ?>')) {
                    $('#' + target).val(defaultValue);
                }
            });
        });
        </script>
        <?php
    }

    // Note: render_filename_prompt_field() removed - filename prompt now in Renamer Settings

    /**
      * Render WooCommerce prompt section
      */
     public function render_woocommerce_prompt_section() {
         ?>
         <p>
             <?php esc_html_e('Configure a specific prompt for WooCommerce product images. When enabled, this prompt will be used for all images attached to WooCommerce products.', 'imgseo-ai-alt-text-generator'); ?>
         </p>
         <p>
             <?php esc_html_e('You can use the following dynamic variables in your prompt:', 'imgseo-ai-alt-text-generator'); ?>
         </p>
         <ul class="imgseo-dynamic-variables">
             <li><code>{language}</code> - <?php esc_html_e('Will be replaced with the selected language', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{max_characters}</code> - <?php esc_html_e('Will be replaced with the maximum characters setting', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_name}</code> - <?php esc_html_e('Will be replaced with the product name', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_brand}</code> - <?php esc_html_e('Will be replaced with the product brand', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_short_description}</code> - <?php esc_html_e('Will be replaced with the product short description', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_categories}</code> - <?php esc_html_e('Will be replaced with the product categories', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_price}</code> - <?php esc_html_e('Will be replaced with the product price', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{on_sale}</code> - <?php esc_html_e('Will be replaced with sale status', 'imgseo-ai-alt-text-generator'); ?></li>
             <li><code>{product_attributes}</code> - <?php esc_html_e('Will be replaced with the product attributes', 'imgseo-ai-alt-text-generator'); ?></li>
         </ul>
         <?php
     }

    /**
     * Render enable WooCommerce prompt field
     */
    public function render_enable_woocommerce_prompt_field() {
        $enable_woocommerce_prompt = get_option('imgseo_enable_woocommerce_prompt', 0);
        ?>
        <label>
            <input type="checkbox" name="imgseo_enable_woocommerce_prompt" value="1" <?php checked(1, $enable_woocommerce_prompt); ?> />
            <?php esc_html_e('Use a specific prompt for WooCommerce product images', 'imgseo-ai-alt-text-generator'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, images attached to WooCommerce products will use the product-specific prompt below.', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <?php
    }

    /**
      * Render WooCommerce prompt field
      */
     public function render_woocommerce_prompt_field() {
         $default_prompt = 'Generate alt text for this product image: {product_name} by {product_brand}\n\nContext: {product_short_description} - {product_categories} - {product_price} {on_sale} - {product_attributes}\n\nDescribe the visual elements shown (colors, style, angle, details) while naturally incorporating the product name. Keep it descriptive, SEO-friendly, under {max_characters} characters in {language}. Focus on what customers would want to know about this product image.';
         $woocommerce_prompt = get_option('imgseo_woocommerce_prompt', $default_prompt);
         ?>
         <textarea name="imgseo_woocommerce_prompt" id="imgseo_woocommerce_prompt" rows="6" cols="80" class="large-text code"><?php echo esc_textarea($woocommerce_prompt); ?></textarea>

         <p class="description">
             <?php esc_html_e('Customize the prompt sent to the AI to generate alt text for WooCommerce product images. Use the dynamic variables listed above to make the prompt more effective.', 'imgseo-ai-alt-text-generator'); ?>
         </p>

         <button type="button" id="reset_woocommerce_prompt" class="button btn-custom-secondary" style="margin-top: 10px;">
             <span class="dashicons dashicons-image-rotate" style="margin-top: 3px;"></span>
             <?php esc_html_e('Reset Default Product Prompt', 'imgseo-ai-alt-text-generator'); ?>
         </button>

         <script>
             jQuery(document).ready(function($) {
                 $('#reset_woocommerce_prompt').on('click', function() {
                     if (confirm('<?php echo esc_js(__('Are you sure you want to reset to the default WooCommerce product prompt? This operation will overwrite the current prompt.', 'imgseo-ai-alt-text-generator')); ?>')) {
                         $('#imgseo_woocommerce_prompt').val('Generate alt text for this product image: {product_name} by {product_brand}\\n\\nContext: {product_short_description} - {product_categories} - {product_price} {on_sale} - {product_attributes}\\n\\nDescribe the visual elements shown (colors, style, angle, details) while naturally incorporating the product name. Keep it descriptive, SEO-friendly, under {max_characters} characters in {language}. Focus on what customers would want to know about this product image.');
                     }
                 });
             });
         </script>
         <?php
     }



    /**

     * Rendering del campo API Key

     */

    public function render_api_key_field() {

        $api_key = get_option('imgseo_api_key', '');

        $api_verified = !empty($api_key) && get_option('imgseo_api_verified', false);

        ?>

        <div class="imgseo-api-key-container">

            <div class="api-key-input-group">

                <input type="password"

                       name="imgseo_api_key"

                       id="imgseo_api_key"

                       value="<?php echo esc_attr($api_key); ?>"

                       class="regular-text"

                       placeholder="<?php esc_attr_e('Enter your ImgSEO Token', 'imgseo-ai-alt-text-generator'); ?>"

                       <?php echo $api_verified ? 'readonly' : ''; ?>>



                <button type="button" id="toggle_api_key_visibility" class="button btn-custom-secondary">

                    <span class="dashicons dashicons-visibility"></span>

                </button>

            </div>



            <!-- Spazio tra il campo di input e i pulsanti d'azione -->

            <div style="margin-top: 10px;">

                <div class="api-key-actions">

                    <?php if (!$api_verified): ?>

                        <button type="button" id="verify_api_key" class="button btn-custom-primary">

                            <span class="dashicons dashicons-yes"></span> <?php esc_html_e('Verify ImgSEO Token', 'imgseo-ai-alt-text-generator'); ?>

                        </button>

                    <?php else: ?>

                        <button type="button" id="disconnect_api_key" class="button btn-custom-disconnect">

                            <span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Disconnect', 'imgseo-ai-alt-text-generator'); ?>

                        </button>



                        <button type="button" id="verify_api_key" class="button btn-custom-secondary">

                            <span class="dashicons dashicons-update"></span> <?php esc_html_e('Verify again', 'imgseo-ai-alt-text-generator'); ?>

                        </button>

                    <?php endif; ?>

                </div>

            </div>



            <div id="api_key_status" class="api-key-status">

                <?php if ($api_verified): ?>

                    <div class="status-message success">

                        <span class="dashicons dashicons-yes-alt"></span>

                        <?php esc_html_e('ImgSEO Token Verified!', 'imgseo-ai-alt-text-generator'); ?>

                        <?php

                        $plan = get_option('imgseo_plan', '');

                        if (!empty($plan)):

                            echo ' Plan:  <strong>' . esc_html($plan) . '</strong>';

                        endif;

                        ?>

                    </div>

                <?php else: ?>

                    <div class="status-message info">

                        <span class="dashicons dashicons-info"></span>

                        <?php esc_html_e('Enter your ImgSEO Token and click on "Verify ImgSEO Token".', 'imgseo-ai-alt-text-generator'); ?>

                    </div>



                    <p class="register-link">
                        <?php esc_html_e('You don\'t have an ImgSEO Token?', 'imgseo-ai-alt-text-generator'); ?>

                        <a href="https://dashboard.imgseo.net/login" target="_blank" class="button button-link">
                            <span class="dashicons dashicons-external"></span>
                            <?php esc_html_e('Register on ImgSEO. You\'ll receive 30 free credits and 10 free credits daily (if your balance falls below 10).', 'imgseo-ai-alt-text-generator'); ?>
                        </a>
                    </p>

                <?php endif; ?>

            </div>

        </div>

        <?php

    }



    /**

     * Rendering del campo crediti

     */

    public function render_credits_field() {

        $api_key = get_option('imgseo_api_key', '');

        $api_verified = !empty($api_key) && get_option('imgseo_api_verified', false);

        $credits_raw = get_option('imgseo_credits', 0);
        $credits = is_numeric($credits_raw) ? (float) $credits_raw : 0.0;

        $last_check = get_option('imgseo_last_check', 0);



        if (!$api_verified) {

            echo '<p>' . esc_html__('Verify your ImgSEO Token to view available credits.', 'imgseo-ai-alt-text-generator') . '</p>';

            return;

        }



        $last_check_time = '';

        if ($last_check > 0) {

            $last_check_time = human_time_diff($last_check, time()) . ' ' . __('ago', 'imgseo-ai-alt-text-generator');

        }

        ?>

        <div class="imgseo-credits-container">
            <div id="imgseo_credits_display" class="credits-count <?php echo $credits <= 10 ? 'low-credits' : ''; ?>">
                <?php echo number_format($credits, 1); ?>
            </div>
            <div class="credits-label"><?php esc_html_e('available credits', 'imgseo-ai-alt-text-generator'); ?></div>



            <div class="credits-actions">

                <button type="button" id="refresh_credits" class="button btn-custom-primary">

                    <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh Credits', 'imgseo-ai-alt-text-generator'); ?>

                </button>



                <a href="https://dashboard.imgseo.net/subscription" target="_blank" class="button btn-custom-secondary">

                    <span class="dashicons dashicons-cart"></span> <?php esc_html_e('Purchase credits', 'imgseo-ai-alt-text-generator'); ?>

                </a>

            </div>



            <?php if (!empty($last_check_time)): ?>

                <p class="description" id="last_credits_check">

                    <span class="dashicons dashicons-clock"></span> <?php echo esc_html__('Last update:', 'imgseo-ai-alt-text-generator') . ' ' . esc_html($last_check_time); ?>

                </p>

            <?php endif; ?>



            <?php if ($credits <= 10 && $credits > 0): ?>

                <div class="credits-warning warning">

                    <span class="dashicons dashicons-warning"></span>

                    <?php esc_html_e('Your credits are running low. Consider purchasing additional credits.', 'imgseo-ai-alt-text-generator'); ?>

                </div>

            <?php elseif ($credits <= 0): ?>

                <div class="credits-warning error">

                    <span class="dashicons dashicons-dismiss"></span>

                    <?php esc_html_e('You have no available credits! Purchase new credits to continue using the service.', 'imgseo-ai-alt-text-generator'); ?>

                </div>

            <?php else: ?>

                <div class="credits-status success">

                    <span class="dashicons dashicons-yes-alt"></span>

                    <?php esc_html_e('You have sufficient credits to generate alternative texts.', 'imgseo-ai-alt-text-generator'); ?>

                </div>

            <?php endif; ?>

        </div>

        <?php

    }



    /**

     * Rendering del campo lingua

     */

    public function render_language_field() {

        $languages = [

            'english' => 'English',

            'italiano' => 'Italiano',

            'japanese' => '日本語',

            'korean' => '한국어',

            'arabic' => 'العربية',

            'bahasa_indonesia' => 'Bahasa Indonesia',

            'bengali' => 'বাংলা',

            'bulgarian' => 'Български',

            'chinese_simplified' => '中文 (简体)',

            'chinese_traditional' => '中文 (繁體)',

            'croatian' => 'Hrvatski',

            'czech' => 'Čeština',

            'danish' => 'Dansk',

            'dutch' => 'Nederlands',

            'estonian' => 'Eesti',

            'farsi' => 'فارسی',

            'finnish' => 'Suomi',

            'french' => 'Français',

            'german' => 'Deutsch',

            'gujarati' => 'ગુજરાતી',

            'greek' => 'Ελληνικά',

            'hebrew' => 'עברית',

            'hindi' => 'हिन्दी',

            'hungarian' => 'Magyar',

            'kannada' => 'ಕನ್ನಡ',

            'latvian' => 'Latviešu',

            'lithuanian' => 'Lietuvių',

            'malayalam' => 'മലയാളം',

            'marathi' => 'मराठी',

            'norwegian' => 'Norsk',

            'polish' => 'Polski',

            'portuguese' => 'Português',

            'romanian' => 'Română',

            'russian' => 'Русский',

            'serbian' => 'Српски',

            'slovak' => 'Slovenčina',

            'slovenian' => 'Slovenščina',

            'spanish' => 'Español',

            'swahili' => 'Kiswahili',

            'swedish' => 'Svenska',

            'tamil' => 'தமிழ்',

            'telugu' => 'తెలుగు',

            'thai' => 'ไทย',

            'turkish' => 'Türkçe',

            'ukrainian' => 'Українська',

            'urdu' => 'اردو',

            'vietnamese' => 'Tiếng Việt'

        ];



        $selected = get_option('imgseo_language', 'english');

        ?>

        <select name="imgseo_language">

            <?php foreach ($languages as $value => $label): ?>

                <option value="<?php echo esc_attr($value); ?>" <?php selected($selected, $value); ?>>

                    <?php echo esc_html($label); ?>

                </option>

            <?php endforeach; ?>

        </select>

        <p class="description"><?php esc_html_e('Select the language in which alternative texts will be generated.', 'imgseo-ai-alt-text-generator'); ?></p>

        <?php

    }



    /**

     * Rendering del campo caratteri massimi

     */

    public function render_max_characters_field() {

        $max_characters = get_option('imgseo_max_characters', 125);

        ?>

        <input type="number" name="imgseo_max_characters" value="<?php echo esc_attr($max_characters); ?>" min="50" max="300" />

        <p class="description"><?php esc_html_e('Maximum length of the generated alternative text. Recommended value: 125 characters.', 'imgseo-ai-alt-text-generator'); ?></p>

        <?php

    }



    /**

     * Rendering del campo includi titolo pagina

     */

    public function render_include_page_title_field() {

        $include_page_title = get_option('imgseo_include_page_title', 1);

        ?>

        <input type="checkbox" name="imgseo_include_page_title" id="imgseo_include_page_title" value="1" <?php checked($include_page_title, 1); ?> />

        <label for="imgseo_include_page_title"><?php esc_html_e('Include page title in prompt', 'imgseo-ai-alt-text-generator'); ?></label>

        <p class="description"><?php esc_html_e('If selected, the title of the page containing the image will be included in the prompt to generate a more contextualized alternative text.', 'imgseo-ai-alt-text-generator'); ?></p>

        <?php

    }



    /**

     * Rendering del campo includi nome immagine

     */

    public function render_include_image_name_field() {

        $include_image_name = get_option('imgseo_include_image_name', 1);

        ?>

        <input type="checkbox" name="imgseo_include_image_name" id="imgseo_include_image_name" value="1" <?php checked($include_image_name, 1); ?> />

        <label for="imgseo_include_image_name"><?php esc_html_e('Include image filename in prompt', 'imgseo-ai-alt-text-generator'); ?></label>

        <p class="description"><?php esc_html_e('If selected, the image filename will be included in the prompt, useful if filenames contain relevant information.', 'imgseo-ai-alt-text-generator'); ?></p>

        <?php

    }



    /**

     * Rendering del campo sovrascrivi

     */

    public function render_overwrite_field() {

        $overwrite = get_option('imgseo_overwrite', 0);

        ?>

        <input type="checkbox" name="imgseo_overwrite" id="imgseo_overwrite" value="1" <?php checked($overwrite, 1); ?> />

        <label for="imgseo_overwrite">
            <span id="imgseo-overwrite-label-settings"><?php esc_html_e('Overwrite existing alt texts', 'imgseo-ai-alt-text-generator'); ?></span>
        </label>

        <p class="description"><?php esc_html_e('If selected, the plugin will overwrite existing alt texts during batch processing or automatic generation.', 'imgseo-ai-alt-text-generator'); ?></p>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Dynamic overwrite label update based on selected fields
            function updateOverwriteLabelSettings() {
                var fields = [];

                // Alt text is always included
                fields.push('<?php echo esc_js(__('Alt Texts', 'imgseo-ai-alt-text-generator')); ?>');

                // Check if other fields are selected
                if ($('input[name="imgseo_update_title"]').is(':checked')) {
                    fields.push('<?php echo esc_js(__('Titles', 'imgseo-ai-alt-text-generator')); ?>');
                }
                if ($('input[name="imgseo_update_caption"]').is(':checked')) {
                    fields.push('<?php echo esc_js(__('Captions', 'imgseo-ai-alt-text-generator')); ?>');
                }
                if ($('input[name="imgseo_update_description"]').is(':checked')) {
                    fields.push('<?php echo esc_js(__('Descriptions', 'imgseo-ai-alt-text-generator')); ?>');
                }

                // Build the label text
                var labelText = '<?php echo esc_js(__('Overwrite existing:', 'imgseo-ai-alt-text-generator')); ?> ' + fields.join(', ');

                // Update the label
                $('#imgseo-overwrite-label-settings').text(labelText);
            }

            // Wait for all elements to be in DOM, then initialize
            setTimeout(function() {
                updateOverwriteLabelSettings();

                // Update label when metadata checkboxes change
                $('.imgseo-metadata-field-settings').on('change', function() {
                    updateOverwriteLabelSettings();
                });
            }, 100);
        });
        </script>

        <?php

    }



    /**

     * Rendering del campo generazione automatica

     */

    public function render_auto_generate_field() {

        $auto_generate = get_option('imgseo_auto_generate', 0);

        ?>

        <input type="checkbox" name="imgseo_auto_generate" id="imgseo_auto_generate" value="1" <?php checked($auto_generate, 1); ?> />




        <label for="imgseo_auto_generate"><?php esc_html_e('Automatically generate alt text when uploading images', 'imgseo-ai-alt-text-generator'); ?></label>

        <p class="description"><?php esc_html_e('When enabled, alt text will be automatically generated for each newly uploaded image.', 'imgseo-ai-alt-text-generator'); ?></p>

        <?php
        // Dynamic cost calculator box
        $auto_rename = get_option('imgseo_auto_rename_on_upload', 0);
        $update_title = get_option('imgseo_update_title', 0);
        $update_caption = get_option('imgseo_update_caption', 0);
        $update_description = get_option('imgseo_update_description', 0);
        ?>

        <div id="imgseo-cost-calculator" style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px; display: <?php echo $auto_generate ? 'block' : 'none'; ?>;">
            <p style="margin: 0 0 10px 0; font-weight: 600;">
                <span class="dashicons dashicons-money-alt" style="color: #2271b1;"></span>
                <?php esc_html_e('Cost per uploaded image:', 'imgseo-ai-alt-text-generator'); ?>
                <strong id="imgseo-upload-cost" style="color: #2271b1; font-size: 16px;">
                    <?php
                    $cost = 1.0;
                    if ($auto_rename) $cost += 0.5;
                    if ($update_title) $cost += 0.5;
                    if ($update_caption) $cost += 0.5;
                    if ($update_description) $cost += 0.5;
                    echo number_format($cost, 1);
                    ?>
                </strong> <?php esc_html_e('credits', 'imgseo-ai-alt-text-generator'); ?>
            </p>
            <ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 13px; line-height: 1.6; color: #666;">
                <li><?php esc_html_e('Base: 1.0 credit (alt text generation)', 'imgseo-ai-alt-text-generator'); ?></li>
                <li id="imgseo-cost-rename" style="display: <?php echo $auto_rename ? 'list-item' : 'none'; ?>;">
                    <?php esc_html_e('+0.5 credits (Auto-Rename on Upload enabled in Renamer Settings)', 'imgseo-ai-alt-text-generator'); ?>
                </li>
                <li id="imgseo-cost-title" style="display: <?php echo $update_title ? 'list-item' : 'none'; ?>;">
                    <?php esc_html_e('+0.5 credits (Title generation enabled in Field Updates)', 'imgseo-ai-alt-text-generator'); ?>
                </li>
                <li id="imgseo-cost-caption" style="display: <?php echo $update_caption ? 'list-item' : 'none'; ?>;">
                    <?php esc_html_e('+0.5 credits (Caption generation enabled in Field Updates)', 'imgseo-ai-alt-text-generator'); ?>
                </li>
                <li id="imgseo-cost-description" style="display: <?php echo $update_description ? 'list-item' : 'none'; ?>;">
                    <?php esc_html_e('+0.5 credits (Description generation enabled in Field Updates)', 'imgseo-ai-alt-text-generator'); ?>
                </li>
            </ul>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Update cost calculator dynamically
            function updateCostCalculator() {
                var baseCost = 1.0;
                var extraCost = 0.0;

                // Check if auto-rename is enabled (not in this form, but stored in DB)
                // We'll update this via AJAX or on page load
                var autoGenerate = $('#imgseo_auto_generate').is(':checked');
                var calculator = $('#imgseo-cost-calculator');

                // Show/hide calculator based on auto-generate checkbox
                if (autoGenerate) {
                    calculator.show();
                } else {
                    calculator.hide();
                    return;
                }

                // Calculate cost from Field Updates checkboxes (if on same page)
                if ($('input[name="imgseo_update_title"]').is(':checked')) {
                    extraCost += 0.5;
                    $('#imgseo-cost-title').show();
                } else {
                    $('#imgseo-cost-title').hide();
                }

                if ($('input[name="imgseo_update_caption"]').is(':checked')) {
                    extraCost += 0.5;
                    $('#imgseo-cost-caption').show();
                } else {
                    $('#imgseo-cost-caption').hide();
                }

                if ($('input[name="imgseo_update_description"]').is(':checked')) {
                    extraCost += 0.5;
                    $('#imgseo-cost-description').show();
                } else {
                    $('#imgseo-cost-description').hide();
                }

                // For auto-rename, we need to check the stored value since it's in Renamer Settings
                // The PHP already renders the correct state, so we just keep it as is
                // But we need to recalculate the total
                var renameEnabled = $('#imgseo-cost-rename').is(':visible');
                if (renameEnabled) {
                    extraCost += 0.5;
                }

                var totalCost = baseCost + extraCost;
                $('#imgseo-upload-cost').text(totalCost.toFixed(1));
            }

            // Update on checkbox change
            $('#imgseo_auto_generate').on('change', updateCostCalculator);
            $('input[name="imgseo_update_title"], input[name="imgseo_update_caption"], input[name="imgseo_update_description"]').on('change', updateCostCalculator);

            // Initial update
            updateCostCalculator();
        });
        </script>

        <?php

    }



    /**
     * Rendering del campo forza utilizzo base64
     */
    public function render_always_use_base64_field() {
        $always_use_base64 = get_option('imgseo_always_use_base64', 1);
        ?>
        <input type="checkbox" name="imgseo_always_use_base64" id="imgseo_always_use_base64" value="1" <?php checked($always_use_base64, 1); ?> />
        <label for="imgseo_always_use_base64"><?php esc_html_e('Force base64 image transfer', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('When enabled, images will always be sent to the service in base64 format instead of as URLs. Useful for sites with anti-hotlinking protection or with Cloudflare active.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo Database Maintenance
     */
    public function render_database_maintenance_field() {
        $delete_on_uninstall = get_option('imgseo_delete_data_on_uninstall', 0);
        ?>
        <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #2271b1; margin-top: 10px;">
            <h4 style="margin-top: 0;"><?php esc_html_e('Database Cleanup & Reset', 'imgseo-ai-alt-text-generator'); ?></h4>
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e('Manage plugin data and database cleanup. Logs are automatically cleaned up weekly (kept for 7 days).', 'imgseo-ai-alt-text-generator'); ?>
            </p>

            <!-- Uninstall Option -->
            <div style="margin-bottom: 20px; padding: 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="imgseo_delete_data_on_uninstall" id="imgseo_delete_data_on_uninstall" value="1" <?php checked($delete_on_uninstall, 1); ?> style="margin-right: 8px;" />
                    <strong><?php esc_html_e('Delete all data when plugin is uninstalled', 'imgseo-ai-alt-text-generator'); ?></strong>
                </label>
                <p class="description" style="margin: 8px 0 0 24px;">
                    <?php esc_html_e('If enabled, all plugin tables and data will be permanently deleted when you uninstall the plugin. Leave disabled to keep your data for future use.', 'imgseo-ai-alt-text-generator'); ?>
                    <br><strong style="color: #d63638;"><?php esc_html_e('Warning:', 'imgseo-ai-alt-text-generator'); ?></strong>
                    <?php esc_html_e('This action cannot be undone once the plugin is uninstalled!', 'imgseo-ai-alt-text-generator'); ?>
                </p>
            </div>

            <div style="margin-bottom: 15px;">
                <button type="button" id="imgseo_reset_logs_btn" class="button button-secondary">
                    <span class="dashicons dashicons-trash" style="vertical-align: middle;"></span>
                    <?php esc_html_e('Clear Logs & Cache', 'imgseo-ai-alt-text-generator'); ?>
                </button>
                <p class="description" style="margin-top: 5px; margin-left: 0;">
                    <?php esc_html_e('Removes all scan logs, rename logs, and cached statistics. Image data will be preserved.', 'imgseo-ai-alt-text-generator'); ?>
                </p>
            </div>

            <div>
                <button type="button" id="imgseo_reset_all_btn" class="button button-danger" style="background: #dc3232; border-color: #dc3232; color: #fff;">
                    <span class="dashicons dashicons-warning" style="vertical-align: middle;"></span>
                    <?php esc_html_e('Factory Reset', 'imgseo-ai-alt-text-generator'); ?>
                </button>
                <p class="description" style="margin-top: 5px; margin-left: 0; color: #d63638;">
                    <strong><?php esc_html_e('Warning:', 'imgseo-ai-alt-text-generator'); ?></strong>
                    <?php esc_html_e('This will reset ALL plugin data including image data, logs, and cache. This action cannot be undone!', 'imgseo-ai-alt-text-generator'); ?>
                </p>
            </div>

            <div id="imgseo_reset_message" style="margin-top: 15px; display: none;"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Reset Logs & Cache
            $('#imgseo_reset_logs_btn').on('click', function() {
                if (!confirm('<?php esc_html_e('Are you sure you want to clear all logs and cache? This action cannot be undone.', 'imgseo-ai-alt-text-generator'); ?>')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php esc_html_e('Processing...', 'imgseo-ai-alt-text-generator'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'imgseo_reset_logs_and_cache',
                        nonce: '<?php echo esc_js(wp_create_nonce('imgseo_reset_data_nonce')); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash" style="vertical-align: middle;"></span> <?php esc_html_e('Clear Logs & Cache', 'imgseo-ai-alt-text-generator'); ?>');

                        if (response.success) {
                            $('#imgseo_reset_message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>').show();
                        } else {
                            $('#imgseo_reset_message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>').show();
                        }

                        setTimeout(function() {
                            $('#imgseo_reset_message').fadeOut();
                        }, 5000);
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash" style="vertical-align: middle;"></span> <?php esc_html_e('Clear Logs & Cache', 'imgseo-ai-alt-text-generator'); ?>');
                        $('#imgseo_reset_message').html('<div class="notice notice-error"><p><?php esc_html_e('An error occurred. Please try again.', 'imgseo-ai-alt-text-generator'); ?></p></div>').show();
                    }
                });
            });

            // Factory Reset
            $('#imgseo_reset_all_btn').on('click', function() {
                if (!confirm('<?php esc_html_e('WARNING: This will delete ALL plugin data! Are you absolutely sure?', 'imgseo-ai-alt-text-generator'); ?>')) {
                    return;
                }

                if (!confirm('<?php esc_html_e('This is your last chance. All data will be permanently deleted. Continue?', 'imgseo-ai-alt-text-generator'); ?>')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php esc_html_e('Resetting...', 'imgseo-ai-alt-text-generator'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'imgseo_reset_all_data',
                        nonce: '<?php echo esc_js(wp_create_nonce('imgseo_reset_data_nonce')); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-warning" style="vertical-align: middle;"></span> <?php esc_html_e('Factory Reset', 'imgseo-ai-alt-text-generator'); ?>');

                        if (response.success) {
                            $('#imgseo_reset_message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>').show();
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#imgseo_reset_message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>').show();
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-warning" style="vertical-align: middle;"></span> <?php esc_html_e('Factory Reset', 'imgseo-ai-alt-text-generator'); ?>');
                        $('#imgseo_reset_message').html('<div class="notice notice-error"><p><?php esc_html_e('An error occurred. Please try again.', 'imgseo-ai-alt-text-generator'); ?></p></div>').show();
                    }
                });
            });
        });
        </script>
        <?php
    }


       /**

     * Rendering del campo aggiungi badge nel footer

     */

public function render_footer_badge_field() {




    $footer_badge = get_option('imgseo_footer_badge', 0);
    $support_link = get_option('imgseo_support_link', 0);
    ?>

    <input type="checkbox" name="imgseo_footer_badge" id="imgseo_footer_badge" value="1" <?php checked($footer_badge, 1); ?> />
    <label for="imgseo_footer_badge"><?php esc_html_e('Display Accessibility Compliance Badge', 'imgseo-ai-alt-text-generator'); ?></label>

    <p class="description">
        <?php esc_html_e('The badge shows your site\'s compliance with accessibility standards for images. When less than 95% of your images have proper alt text, the badge will appear without a checkmark. Once you reach or exceed 95% alt text coverage, the badge will display with a green checkmark, demonstrating your commitment to accessibility. You can also use the shortcode [imgseo_badge] to place the badge anywhere on your site.', 'imgseo-ai-alt-text-generator'); ?>
    </p>

    <br>

    <input type="checkbox" name="imgseo_support_link" id="imgseo_support_link" value="1" <?php checked($support_link, 1); ?> />
    <label for="imgseo_support_link"><?php esc_html_e('Remove ImgSEO reference link', 'imgseo-ai-alt-text-generator'); ?></label>

    <p class="description">
        <?php esc_html_e('When checked, this option will remove the link to ImgSEO\'s accessibility guidelines from the badge. By keeping this unchecked, you help support ImgSEO\'s mission of promoting web accessibility standards while providing visitors with access to valuable resources about image accessibility compliance.', 'imgseo-ai-alt-text-generator'); ?>
    </p>

    <?php
}




    public function render_badge_svg() {
    $support_link = get_option('imgseo_support_link', 0);

    // ✅ FIX: Usa cache transient per evitare query costose su ogni page load
    $cache_key = 'imgseo_badge_data';
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        $total_images = $cached_data['total'];
        $percent_with_alt = $cached_data['percent'];
    } else {
        // ✅ FIX: Usa query SQL diretta invece di WP_Query per performance
        global $wpdb;

        // Conta totale immagini VALIDE (solo mime type image/*)
        $total_images = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_mime_type LIKE %s
            AND post_status = %s",
            'attachment',
            'image/%',
            'inherit'
        ));

        // ✅ FIX: Gestisci caso con 0 immagini
        if (!$total_images || $total_images == 0) {
            return '';
        }

        // Conta immagini CON alt text (in una query, no N+1)
        $with_alt = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_mime_type LIKE %s
            AND p.post_status = %s
            AND pm.meta_key = %s
            AND pm.meta_value != %s
            AND pm.meta_value IS NOT NULL",
            'attachment',
            'image/%',
            'inherit',
            '_wp_attachment_image_alt',
            ''
        ));

        // ✅ FIX: Proteggi da division by zero
        $total_images = (int)$total_images;
        $with_alt = (int)$with_alt;

        $percent_with_alt = ($total_images > 0) ? ($with_alt / $total_images) * 100 : 0;

        // Cache per 10 minuti
        set_transient($cache_key, array(
            'total' => $total_images,
            'percent' => $percent_with_alt
        ), 10 * MINUTE_IN_SECONDS);
    }

    // Show checkmark when at least 95% of images have alt text
    $show_svg = $percent_with_alt >= 95;

    ob_start();
    ?>
    <div class="imgseo-badge" style="margin: 10px auto; position: relative; display: inline-block; line-height: 0; max-width: 150px;">
        <?php if ($support_link != 1): // se il checkbox NON è selezionato, mostra il link ?>
            <a href="https://imgseo.net/web-image-accessibility/" target="_blank" rel="noopener" style="display: block;">
        <?php endif; ?>

        <?php
        // ✅ FIX: Fallback se il file immagine non esiste
        $badge_img_path = plugin_dir_url(dirname(__FILE__)) . 'assets/img/w3c-badge-2-1.png';
        ?>
        <img
            src="<?php echo esc_url($badge_img_path); ?>"
            alt="<?php echo esc_attr__('Accessibility compliance badge', 'imgseo-ai-alt-text-generator'); ?>"
            style="display: block; width: 100%; max-width: 150px; height: auto; object-fit: contain;"
            loading="lazy"
        >

        <?php if ($show_svg): ?>
            <svg style="position: absolute; right: -10px; bottom: -10px;"
                 width="26" height="26" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M4 12.6111L8.92308 17.5L20 6.5"
                      stroke="#0A4906" stroke-width="3.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        <?php endif; ?>

        <?php if ($support_link != 1): ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}






    public function add_footer_badge() {

        $footer_badge = get_option('imgseo_footer_badge', 0);



        if ($footer_badge == 1) {

            echo wp_kses_post($this->render_badge_svg());

        }

    }



    public function register_shortcodes() {

        add_shortcode('imgseo_badge', [$this, 'render_badge_svg']);

    }





    /**

     * Rendering del campo dimensione batch

     */



    /**

     * Rendering del campo aggiorna altri campi

     */

    public function render_update_fields() {

        $update_title = get_option('imgseo_update_title', 0);

        $update_caption = get_option('imgseo_update_caption', 0);

        $update_description = get_option('imgseo_update_description', 0);

        ?>

        <fieldset>

            <p>

                <input type="checkbox" name="imgseo_update_title" id="imgseo_update_title" value="1" <?php checked($update_title, 1); ?> />

                <label for="imgseo_update_title"><?php esc_html_e('Update image title', 'imgseo-ai-alt-text-generator'); ?></label>

            </p>



            <p>

                <input type="checkbox" name="imgseo_update_caption" id="imgseo_update_caption" value="1" <?php checked($update_caption, 1); ?> />

                <label for="imgseo_update_caption"><?php esc_html_e('Update image caption', 'imgseo-ai-alt-text-generator'); ?></label>

            </p>



            <p>

                <input type="checkbox" name="imgseo_update_description" id="imgseo_update_description" value="1" <?php checked($update_description, 1); ?> />

                <label for="imgseo_update_description"><?php esc_html_e('Update image description', 'imgseo-ai-alt-text-generator'); ?></label>

            </p>



            <p class="description"><?php esc_html_e('Choose which other image fields to generate and update via AI. These options no longer copy Alt Text and apply to supported generation flows (single and modal).', 'imgseo-ai-alt-text-generator'); ?></p>

        </fieldset>

        <?php
    }

    /**
     * Rendering del campo per abilitare i dati strutturati
     */
    public function render_structured_data_enabled_field() {
        // Implementazione vuota per ora
    }

    /**
     * Rendering della sezione compressione immagini
     */
    public function render_compression_section() {
        echo '<p>' . esc_html__('Configure image compression settings to optimize your images automatically.', 'imgseo-ai-alt-text-generator') . '</p>';
    }

    /**
     * Rendering del campo abilita compressione
     */
    public function render_compression_enabled_field() {
        $enabled = get_option('imgseo_compression_enabled', 0);
        ?>
        <input type="checkbox" name="imgseo_compression_enabled" id="imgseo_compression_enabled" value="1" <?php checked($enabled, 1); ?> />
        <label for="imgseo_compression_enabled"><?php esc_html_e('Enable image compression', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('When enabled, images can be compressed using the ImgSEO API.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo qualità compressione
     */
    public function render_compression_quality_field() {
        $quality = get_option('imgseo_compression_quality', 80);
        ?>
        <input type="range" name="imgseo_compression_quality" id="imgseo_compression_quality" value="<?php echo esc_attr($quality); ?>" min="10" max="100" oninput="this.nextElementSibling.value = this.value" />
        <output><?php echo esc_html($quality); ?></output>%
        <p class="description">
            <?php esc_html_e('Compression quality for main images (JPEG/PNG) (10-100). Default: 80', 'imgseo-ai-alt-text-generator'); ?><br>
            <em><?php esc_html_e('This applies to the primary image format. Modern formats (WebP/AVIF) have separate quality settings below.', 'imgseo-ai-alt-text-generator'); ?></em>
        </p>
        <?php
    }

    /**
     * Rendering del campo formato output
     */
    public function render_compression_format_field() {
        $format = get_option('imgseo_compression_format', 'auto');
        ?>
        <select name="imgseo_compression_format" id="imgseo_compression_format">
            <option value="auto" <?php selected($format, 'auto'); ?>><?php esc_html_e('Auto (recommended)', 'imgseo-ai-alt-text-generator'); ?></option>
            <option value="jpeg" <?php selected($format, 'jpeg'); ?>>JPEG</option>
            <option value="png" <?php selected($format, 'png'); ?>>PNG</option>
            <option value="webp" <?php selected($format, 'webp'); ?>>WebP</option>
        </select>
        <p class="description"><?php esc_html_e('Output format for compressed images. Auto will choose the best format.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo compressione automatica
     */
    public function render_compression_auto_upload_field() {
        $auto_upload = get_option('imgseo_compression_auto_upload', 0);
        ?>
        <input type="checkbox" name="imgseo_compression_auto_upload" id="imgseo_compression_auto_upload" value="1" <?php checked($auto_upload, 1); ?> />
        <label for="imgseo_compression_auto_upload"><?php esc_html_e('Automatically compress images on upload', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('When enabled, images will be automatically compressed when uploaded to the media library.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo base64 specifico per compressione
     */
    public function render_compression_always_use_base64_field() {
        $enabled = get_option('imgseo_compression_always_use_base64', 1);
        ?>
        <input type="checkbox" name="imgseo_compression_always_use_base64" id="imgseo_compression_always_use_base64" value="1" <?php checked($enabled, 1); ?> />
        <label for="imgseo_compression_always_use_base64"><?php esc_html_e('Force base64 image transfer', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('Send images as Base64 to the compression API. Useful to bypass hotlinking protections or CDNs like Cloudflare that block direct fetch.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo backup originali
     */
    public function render_compression_backup_field() {
        $backup = get_option('imgseo_compression_backup_original', 1);
        ?>
        <input type="checkbox" name="imgseo_compression_backup_original" id="imgseo_compression_backup_original" value="1" <?php checked($backup, 1); ?> />
        <label for="imgseo_compression_backup_original"><?php esc_html_e('Backup original images', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('When enabled, original images will be backed up before compression.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo larghezza massima
     */
    public function render_compression_max_width_field() {
        $max_width = get_option('imgseo_compression_max_width');
        ?>
        <input type="number" name="imgseo_compression_max_width" id="imgseo_compression_max_width" value="<?php echo esc_attr($max_width); ?>" min="100" max="8000" step="10" />
        <p class="description"><?php esc_html_e('Maximum width in pixels. Images wider than this will be resized.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo altezza massima
     */
    public function render_compression_max_height_field() {
        $max_height = get_option('imgseo_compression_max_height');
        ?>
        <input type="number" name="imgseo_compression_max_height" id="imgseo_compression_max_height" value="<?php echo esc_attr($max_height); ?>" min="100" max="8000" step="10" />
        <p class="description"><?php esc_html_e('Maximum height in pixels. Images taller than this will be resized.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo WebP
     */
    public function render_compression_webp_field() {
        $enabled = get_option('imgseo_compression_enable_webp', 0);
        ?>
        <input type="checkbox" name="imgseo_compression_enable_webp" id="imgseo_compression_enable_webp" value="1" <?php checked($enabled, 1); ?> />
        <label for="imgseo_compression_enable_webp"><?php esc_html_e('Generate WebP format alongside original format', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('Creates WebP version for better compression with browser fallback support.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo AVIF
     */
    public function render_compression_avif_field() {
        $enabled = get_option('imgseo_compression_enable_avif', 0);
        ?>
        <input type="checkbox" name="imgseo_compression_enable_avif" id="imgseo_compression_enable_avif" value="1" <?php checked($enabled, 1); ?> />
        <label for="imgseo_compression_enable_avif"><?php esc_html_e('Generate AVIF format alongside original format', 'imgseo-ai-alt-text-generator'); ?></label>
        <p class="description"><?php esc_html_e('Creates next-gen AVIF format for maximum compression with browser fallback support.', 'imgseo-ai-alt-text-generator'); ?></p>
        <p class="description" style="color: #d63638; font-weight: 500;"><strong><?php esc_html_e('⚠️ Warning:', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('AVIF processing currently consumes significant server resources. We recommend leaving this disabled unless absolutely needed.', 'imgseo-ai-alt-text-generator'); ?></p>
        <?php
    }

    /**
     * Rendering del campo modalità fallback
     */
    public function render_compression_webp_quality_field() {
        $webp_quality = get_option('imgseo_compression_webp_quality', 50);
        ?>
        <input type="number" name="imgseo_compression_webp_quality" id="imgseo_compression_webp_quality"
               value="<?php echo esc_attr($webp_quality); ?>" min="1" max="100" class="small-text" />
        <p class="description">
            <?php esc_html_e('Compression quality for WebP format (1-100). Default: 50', 'imgseo-ai-alt-text-generator'); ?><br>
            <em><?php esc_html_e('WebP provides excellent compression. 50% typically gives great results with significant size reduction.', 'imgseo-ai-alt-text-generator'); ?></em>
        </p>
        <?php
    }

    public function render_compression_avif_quality_field() {
        $avif_quality = get_option('imgseo_compression_avif_quality', 30);
        ?>
        <input type="number" name="imgseo_compression_avif_quality" id="imgseo_compression_avif_quality"
               value="<?php echo esc_attr($avif_quality); ?>" min="1" max="100" class="small-text" />
        <p class="description">
            <?php esc_html_e('Compression quality for AVIF format (1-100). Default: 30', 'imgseo-ai-alt-text-generator'); ?><br>
            <em><?php esc_html_e('AVIF is extremely efficient. 30% provides excellent quality with maximum size reduction.', 'imgseo-ai-alt-text-generator'); ?></em>
        </p>
        <?php
    }

    public function render_compression_optimize_web_field() {
        $optimize_web = get_option('imgseo_compression_optimize_web', 1);
        ?>
        <input type="checkbox" name="imgseo_compression_optimize_web" id="imgseo_compression_optimize_web"
               value="1" <?php checked($optimize_web, 1); ?> />
        <label for="imgseo_compression_optimize_web">
            <strong><?php esc_html_e('Enable API web optimization', 'imgseo-ai-alt-text-generator'); ?></strong>
        </label>
        <p class="description">
            <?php esc_html_e('🎯 CRITICAL: Enables advanced web optimizations in the API for maximum compression efficiency.', 'imgseo-ai-alt-text-generator'); ?><br>
            <em><?php esc_html_e('This significantly improves WebP/AVIF compression. Highly recommended to keep enabled.', 'imgseo-ai-alt-text-generator'); ?></em>
        </p>
        <?php
    }

    public function render_compression_strip_metadata_field() {
        $strip_metadata = get_option('imgseo_compression_strip_metadata', 1);
        ?>
        <input type="checkbox" name="imgseo_compression_strip_metadata" id="imgseo_compression_strip_metadata"
               value="1" <?php checked($strip_metadata, 1); ?> />
        <label for="imgseo_compression_strip_metadata">
            <strong><?php esc_html_e('Strip metadata from images', 'imgseo-ai-alt-text-generator'); ?></strong>
        </label>
        <p class="description">
            <?php esc_html_e('🎯 CRITICAL: Removes EXIF, GPS, and other metadata for smaller file sizes and privacy.', 'imgseo-ai-alt-text-generator'); ?><br>
            <em><?php esc_html_e('Can reduce file size by 10-30%. Essential for web optimization.', 'imgseo-ai-alt-text-generator'); ?></em>
        </p>
        <?php
    }

    public function render_compression_auto_remove_field() {
        $auto_remove = get_option('imgseo_compression_auto_remove_larger', 1);
        ?>
        <input type="checkbox" name="imgseo_compression_auto_remove_larger" id="imgseo_compression_auto_remove_larger"
               value="1" <?php checked($auto_remove, 1); ?> />
        <label for="imgseo_compression_auto_remove_larger">
            <?php esc_html_e('Automatically remove modern format files that are larger than the original', 'imgseo-ai-alt-text-generator'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('If a WebP or AVIF file ends up larger than the original JPEG/PNG, it will be automatically deleted to save disk space.', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <?php
    }

    public function render_compression_serving_method_field() {
        $serving_method = get_option('imgseo_compression_serving_method', 'advanced_picture');
        ?>
        <select name="imgseo_compression_serving_method" id="imgseo_compression_serving_method">
            <option value="advanced_picture" <?php selected($serving_method, 'advanced_picture'); ?>><?php esc_html_e('Advanced Picture System (Recommended)', 'imgseo-ai-alt-text-generator'); ?></option>
            <option value="picture" <?php selected($serving_method, 'picture'); ?>><?php esc_html_e('Picture Element (WordPress Filters)', 'imgseo-ai-alt-text-generator'); ?></option>
        </select>

        <p class="description">
            <?php esc_html_e('Choose how modern formats (WebP/AVIF) are delivered to browsers:', 'imgseo-ai-alt-text-generator'); ?><br><br>
            <strong style="color: #0073aa;">✅ <?php esc_html_e('Advanced Picture System (Recommended):', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('Most advanced and reliable system with maximum browser compatibility. Uses HTML processing to create optimized <picture> elements with automatic fallback.', 'imgseo-ai-alt-text-generator'); ?><br><br>
            <strong><?php esc_html_e('Picture Element (WordPress Filters):', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('Standard WordPress integration using filters. Good compatibility with themes and plugins, ideal for sites with specific WordPress requirements.', 'imgseo-ai-alt-text-generator'); ?>
        </p>
        <?php
    }


    /**
     * Sanitizza i valori di qualità (1-100)
     */
    public function sanitize_quality_value($value) {
        $value = intval($value);
        if ($value < 1) {
            return 1;
        }
        if ($value > 100) {
            return 100;
        }
        return $value;
    }

    /**
     * AJAX handler per reset dei log e cache
     */
    public function ajax_reset_logs_and_cache() {
        // Verifica nonce e permessi
        check_ajax_referer('imgseo_reset_data_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'imgseo-ai-alt-text-generator')));
            return;
        }

        // Esegui il reset
        $db_manager = ImgSEO_Database_Manager::get_instance();
        $result = $db_manager->reset_logs_and_cache();

        if ($result) {
            wp_send_json_success(array('message' => __('Logs and cache have been reset successfully.', 'imgseo-ai-alt-text-generator')));
        } else {
            wp_send_json_error(array('message' => __('An error occurred while resetting data.', 'imgseo-ai-alt-text-generator')));
        }
    }

    /**
     * AJAX handler per reset completo del plugin
     */
    public function ajax_reset_all_data() {
        // Verifica nonce e permessi
        check_ajax_referer('imgseo_reset_data_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'imgseo-ai-alt-text-generator')));
            return;
        }

        // Esegui il reset completo
        $db_manager = ImgSEO_Database_Manager::get_instance();
        $result = $db_manager->reset_all_data();

        if ($result) {
            wp_send_json_success(array('message' => __('Plugin data has been reset successfully. The plugin is now in factory state.', 'imgseo-ai-alt-text-generator')));
        } else {
            wp_send_json_error(array('message' => __('An error occurred while resetting data.', 'imgseo-ai-alt-text-generator')));
        }
    }

}
