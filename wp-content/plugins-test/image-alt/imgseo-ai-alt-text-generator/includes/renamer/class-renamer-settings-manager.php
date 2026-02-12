<?php
/**
 * Class Renamer_Settings_Manager
 * Manages settings for the Image Renamer functionality
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_Settings_Manager {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Log retention days
     */
    private $log_retention_days = 7;
    
    /**
     * Initialize the class and set its properties.
     */
    private function __construct() {
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
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
     * Register settings for the renamer
     */
    public function register_settings() {
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

        // Auto-rename on upload
        register_setting('imgseo_renamer_settings', 'imgseo_auto_rename_on_upload', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

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

        // Advanced settings
        register_setting('imgseo_renamer_settings', 'imgseo_renamer_block_optimization_plugins', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));

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

        // Advanced section
        add_settings_section(
            'imgseo_renamer_advanced_section',
            __('Advanced Settings', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_advanced_section'),
            'imgseo_renamer_settings'
        );

        // ========== CAMPI PER SEZIONE GENERALE ==========

        // Auto-rename on upload
        $auto_alt_enabled = (bool) get_option('imgseo_auto_generate', 0);
        $credit_phrase = $auto_alt_enabled
            ? __('uses 0.5 credits per image', 'imgseo-ai-alt-text-generator')
            : __('uses 1 credit per image', 'imgseo-ai-alt-text-generator');
        $auto_rename_description = sprintf(
            /* translators: %s: credit usage description (e.g., "uses 0.5 credits per image") */
            __('Automatically rename images on upload using AI (%s). Alt-text will be generated afterwards if enabled.', 'imgseo-ai-alt-text-generator'),
            $credit_phrase
        );
        add_settings_field(
            'imgseo_auto_rename_on_upload',
            __('Auto-Rename on Upload', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_general_section',
            array(
                'name' => 'imgseo_auto_rename_on_upload',
                'description' => $auto_rename_description
            )
        );

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

        // ========== CAMPI PER SEZIONE ADVANCED ==========

        add_settings_field(
            'imgseo_renamer_block_optimization_plugins',
            __('Block Image Optimization Plugins', 'imgseo-ai-alt-text-generator'),
            array($this, 'render_checkbox_field'),
            'imgseo_renamer_settings',
            'imgseo_renamer_advanced_section',
            array(
                'name' => 'imgseo_renamer_block_optimization_plugins',
                'description' => __('Temporarily block image optimization plugins (ShortPixel, Smush, Imagify, etc.) during rename to prevent resize/compression. Enable only if you experience image quality changes after renaming. <strong>Warning:</strong> This is an advanced feature that may cause conflicts with some plugins.', 'imgseo-ai-alt-text-generator')
            )
        );
    }
    
    /**
     * Render the general settings section
     */
    public function render_general_section() {
        echo '<p>' . esc_html__('General settings for the image renaming functionality.', 'imgseo-ai-alt-text-generator') . '</p>';
    }
    
    /**
     * Render the sanitization settings section
     */
    public function render_sanitization_section() {
        echo '<p>' . esc_html__('Configure how to sanitize file names during renaming.', 'imgseo-ai-alt-text-generator') . '</p>';
    }
    
    /**
     * Render the AI settings section
     */
    public function render_ai_section() {
        echo '<p>' . esc_html__('Configure how AI generates filenames for your images.', 'imgseo-ai-alt-text-generator') . '</p>';
    }
    
    /**
     * Render the integration settings section
     */
    public function render_integration_section() {
        echo '<p>' . esc_html__('Configure integration with page builders and other plugins.', 'imgseo-ai-alt-text-generator') . '</p>';
    }

    /**
     * Render the advanced settings section
     */
    public function render_advanced_section() {
        ?>
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 15px; margin-bottom: 15px;">
            <strong>⚠️ <?php esc_html_e('Advanced Settings', 'imgseo-ai-alt-text-generator'); ?></strong>
            <p style="margin: 8px 0 0 0;">
                <?php esc_html_e('These settings are for advanced users only. Change them only if you understand their implications and are experiencing specific issues.', 'imgseo-ai-alt-text-generator'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render a checkbox field
     */
    public function render_checkbox_field($args) {
        $name = $args['name'];
        $description = isset($args['description']) ? $args['description'] : '';
        $value = get_option($name, 1);
        ?>
        <label>
            <?php
            // ✅ FIX: Hidden input per permettere unchecked (0) value
            // Quando checkbox è unchecked, il form invia solo l'hidden field con value="0"
            // Quando checkbox è checked, il value="1" sovrascrive l'hidden field
            ?>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="0" />
            <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked(1, $value); ?> />
            <?php echo wp_kses_post($description); // Allow HTML in description for <strong> tags ?>
        </label>
        <?php
    }
    
    /**
     * Render a number field
     */
    public function render_number_field($args) {
        $name = $args['name'];
        $description = isset($args['description']) ? $args['description'] : '';
        $min = isset($args['min']) ? $args['min'] : 1;
        $max = isset($args['max']) ? $args['max'] : 100;
        $value = get_option($name, $min);
        ?>
        <input type="number" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" />
        <p class="description"><?php echo esc_html($description); ?></p>
        <?php
    }
    
    /**
     * Render a text field
     */
    public function render_text_field($args) {
        $name = $args['name'];
        $description = isset($args['description']) ? $args['description'] : '';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $value = get_option($name, $placeholder);
        ?>
        <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="<?php echo esc_attr($placeholder); ?>" />
        <p class="description"><?php echo esc_html($description); ?></p>
        <?php
    }
    
    /**
     * Render a select field
     */
    public function render_select_field($args) {
        $name = $args['name'];
        $description = isset($args['description']) ? $args['description'] : '';
        $options = isset($args['options']) ? $args['options'] : array();
        $value = get_option($name, '');
        ?>
        <select name="<?php echo esc_attr($name); ?>">
            <?php foreach ($options as $option_value => $option_label) : ?>
                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>><?php echo esc_html($option_label); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php echo esc_html($description); ?></p>
        <?php
    }

    /**
     * Get a specific renamer setting
     * 
     * @param string $key Setting key (without prefix)
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function get_setting($key, $default = '') {
        $option_name = 'imgseo_renamer_' . $key;
        return get_option($option_name, $default);
    }
    
    /**
     * Verifica se un'impostazione di checkbox è abilitata
     * 
     * @param string $key Setting key (without prefix)
     * @param bool $default Default value
     * @return bool Setting enabled
     */
    public function is_enabled($key, $default = true) {
        return (bool) $this->get_setting($key, $default ? 1 : 0);
    }
}
