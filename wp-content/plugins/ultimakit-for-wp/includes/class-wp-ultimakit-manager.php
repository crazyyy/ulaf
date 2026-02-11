<?php

class UltimaKit_Module_Manager extends UltimaKit_Helpers {
    public $module_settings;

    protected $ID = '';

    protected $name;

    protected $description;

    protected $plan = 'free';

    protected $category = '';

    protected $type = '';

    protected $is_active;

    protected $read_more_link = 'https://www.wpultimakit.com';

    protected $settings = 'no';

    protected $settings_link = '#';

    public $modules;

    public $module_settings_obj;

    private $helper;

    private static $instance = null;

    public function __construct() {
        add_action( 'wp_ajax_ultimakit_update_settings', array($this, 'ultimakit_update_settings') );
        add_action( 'wp_ajax_ultimakit_uninstall_settings', array($this, 'ultimakit_uninstall_settings') );
        // Register AJAX actions for logged-in users
        add_action( 'wp_ajax_export_ultimakit_settings', array($this, 'export_settings_to_json') );
        add_action( 'wp_ajax_import_ultimakit_settings', array($this, 'import_settings_from_json') );
        add_action( 'wp_ajax_ultimakit_update_module_width', array($this, 'ultimakit_update_module_width') );
        add_action( 'init', array($this, 'autoloadModuleSettings') );
        $this->helper = new UltimaKit_Helpers();
        // Initialize modules from main and registered add-on paths
        $addon_paths = apply_filters( 'ultimakit_addon_module_paths', array() );
        $this->ultimakit_initializeModules( $addon_paths );
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function ultimakit_initializeModules( $additional_paths = array() ) {
        $base_paths = [ULTIMAKIT_FOR_WP_PATH . 'modules/', ULTIMAKIT_FOR_WP_PATH . 'modules/pro-modules/'];
        // Allow addon modules to hook into this.
        $paths = array_merge( $base_paths, apply_filters( 'wpuk_module_paths', [] ), $additional_paths );
        foreach ( $paths as $modules_directory ) {
            if ( is_dir( $modules_directory ) ) {
                $module_folders = glob( $modules_directory . '*', GLOB_ONLYDIR );
                foreach ( $module_folders as $module_folder ) {
                    $module_name = basename( $module_folder );
                    $metadata_file = $module_folder . '/metadata.json';
                    if ( !file_exists( $metadata_file ) ) {
                        // echo "Skipping module $module_name: metadata.json is missing.\n";
                        continue;
                    }
                    $metadata = json_decode( file_get_contents( $metadata_file ), true );
                    if ( !isset( $metadata['id'] ) ) {
                        // echo "Skipping module $module_name: Invalid metadata (missing module_id).\n";
                        continue;
                    }
                    if ( isset( $metadata['type'] ) && $metadata['type'] === 'Gravity Forms' && !is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
                        // Skip Gravity Forms modules if Gravity Forms plugin is not active.
                        continue;
                    }
                    $is_active = $this->isModuleActive( $metadata['id'] );
                    $module_instance = null;
                    if ( $is_active ) {
                        $module_file = $module_folder . '/class-wpultimakit-module-' . $module_name . '.php';
                        if ( file_exists( $module_file ) ) {
                            require_once $module_file;
                            $class_name = 'UltimaKit_Module_' . ucfirst( str_replace( '-', '_', $module_name ) );
                            if ( class_exists( $class_name ) ) {
                                $module_instance = new $class_name();
                                // echo "Module {$metadata['name']} loaded successfully.\n";
                            }
                        }
                    }
                    // Use the real class for active modules or UltimaKit_Module_Base for inactive ones
                    $module_info = $module_instance ?? new UltimaKit_Module_Base([
                        'id'            => $metadata['id'],
                        'name'          => $metadata['name'] ?? ucwords( str_replace( '-', ' ', $module_name ) ),
                        'description'   => $metadata['description'] ?? 'No description available.',
                        'category'      => $metadata['category'] ?? 'General',
                        'plan'          => $metadata['plan'] ?? 'free',
                        'type'          => $metadata['type'] ?? 'WordPress',
                        'link'          => $metadata['link'] ?? '',
                        'is_active'     => $is_active,
                        'settings'      => ( $module_instance ? $module_instance->getSettings() : null ),
                        'settings_link' => ( $module_instance ? $module_instance->getSettingsLink() : null ),
                    ]);
                    $this->modules[] = $module_info;
                }
            }
        }
        $pro_modules = array_merge( $this->get_pro_modules(), $this->get_pro_modules( 'WooCommerce' ) );
        if ( !empty( $pro_modules ) ) {
            foreach ( $pro_modules as $pro_module ) {
                // Create a module base instance for each pro module
                $module_info = new UltimaKit_Module_Base([
                    'id'            => $pro_module['id'],
                    'name'          => $pro_module['name'],
                    'description'   => $pro_module['description'],
                    'category'      => $pro_module['category'],
                    'plan'          => $pro_module['plan'],
                    'type'          => $pro_module['type'],
                    'link'          => $pro_module['link'],
                    'is_active'     => false,
                    'settings'      => null,
                    'settings_link' => '#',
                ]);
                $this->modules[] = $module_info;
            }
        }
        // Apply filters and ensure modules are unique by ID
        $filtered_modules = apply_filters( 'ultimakit_modules', $this->modules );
        // Create a temporary array to track unique module IDs
        $unique_modules = [];
        $module_ids = [];
        // Only keep modules with unique IDs (first occurrence wins)
        foreach ( $filtered_modules as $module ) {
            $module_id = $module->getID();
            if ( !in_array( $module_id, $module_ids ) ) {
                $module_ids[] = $module_id;
                $unique_modules[] = $module;
            }
        }
        $this->modules = $unique_modules;
    }

    public function getAllCategories() {
        $all_categories_array = [];
        foreach ( $this->modules as $module ) {
            $type = $module->getType();
            if ( in_array( $type, ['WordPress', 'WooCommerce'] ) ) {
                $all_categories_array[] = $module->getCategory();
            }
        }
        $all_categories_array = array_unique( $all_categories_array );
        sort( $all_categories_array );
        foreach ( $all_categories_array as $category ) {
            echo '<option value="' . esc_html( $category ) . '">' . esc_html( $category ) . '</option>';
        }
    }

    public function getAllCategoriesList() {
        $all_categories_array = [];
        foreach ( $this->modules as $module ) {
            $type = $module->getType();
            if ( in_array( $type, ['WordPress', 'WooCommerce'] ) ) {
                $all_categories_array[] = $module->getCategory();
            }
        }
        $all_categories_array = array_unique( $all_categories_array );
        sort( $all_categories_array );
        return $all_categories_array;
    }

    public function getAllModules( $type = '', $plan = '' ) {
        $all_module_info = [];
        foreach ( $this->modules as $module ) {
            if ( (empty( $type ) || $type === $module->getType()) && (empty( $plan ) || $plan === $module->getPlan()) ) {
                $all_module_info[] = [
                    'id'            => $module->getID(),
                    'name'          => $module->getName(),
                    'description'   => $module->getDescription(),
                    'category'      => $module->getCategory(),
                    'plan'          => $module->getPlan(),
                    'type'          => $module->getType(),
                    'link'          => $module->getLink(),
                    'is_active'     => $this->isModuleActive( $module->getID() ),
                    'settings'      => $module->getSettings(),
                    'settings_link' => $module->getSettingsLink(),
                ];
            }
        }
        return $all_module_info;
    }

    public function getAllModulesByCategory( $category = '', $types = array() ) {
        $all_module_info = [];
        foreach ( $this->modules as $module ) {
            $type = $module->getType();
            if ( !empty( $types ) && !in_array( $type, $types, true ) ) {
                continue;
            }
            if ( !empty( $category ) && $category === $module->getCategory() ) {
                $all_module_info[] = [
                    'id'            => $module->getID(),
                    'name'          => $module->getName(),
                    'description'   => $module->getDescription(),
                    'category'      => $module->getCategory(),
                    'plan'          => $module->getPlan(),
                    'type'          => $module->getType(),
                    'link'          => $module->getLink(),
                    'is_active'     => $this->isModuleActive( $module->getID() ),
                    'settings'      => $module->getSettings(),
                    'settings_link' => $module->getSettingsLink(),
                ];
            }
        }
        return $all_module_info;
    }

    public function get_pro_modules( $type = 'WordPress' ) {
        // Define the path to the JSON file
        $dir = plugin_dir_path( __FILE__ );
        $filePath = $dir . 'pro-modules.json';
        // Check if the file exists
        if ( !file_exists( $filePath ) ) {
            echo 'File not found';
            return null;
            // Return null or handle the error as appropriate
        }
        // Read th e JSON file contents
        $jsonContent = file_get_contents( $filePath );
        if ( $jsonContent === false ) {
            echo 'Failed to read from file';
            return null;
            // Handle error appropriately
        }
        // Decode the JSON content into a PHP array
        $data = json_decode( $jsonContent, true );
        // Passing true to convert objects to associative arrays
        if ( $data === null ) {
            echo 'Failed to decode JSON';
            return null;
            // Handle JSON decoding errors
        }
        $modules = array();
        foreach ( $data as $module ) {
            if ( $type === $module['type'] ) {
                $modules[] = $module;
            }
        }
        return $modules;
    }

    public function isModuleActive( $moduleID ) {
        $ultimakitManager = new UltimaKit();
        $settings = $this->get_module_settings( $moduleID );
        // Check if the object is created successfully and the property exists
        if ( $ultimakitManager && $settings ) {
            $this->module_settings = $settings;
        } else {
            // Handle the error as you see fit, e.g., throw an exception or log an error.
            // error_log( 'Failed to get module settings from UltimaKit.' );
        }
        if ( 'WooCommerce' === $this->getType() ) {
            if ( !$this->woo_activation_check() ) {
                return false;
            }
        }
        return $this->is_module_enabled( $moduleID );
    }

    public function getID() {
        return $this->ID;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getPlan() {
        return $this->plan;
    }

    public function getType() {
        return $this->type;
    }

    public function getLink() {
        return $this->read_more_link;
    }

    public function getSettings() {
        return ( $this->settings ? $this->settings : 'no' );
    }

    public function getSettingsLink() {
        return ( $this->settings_link ? $this->settings_link : '#' );
    }

    public function getModuleSettings( $module_id = '', $key = '', $default = false ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // If a specific setting key is provided, fetch only that setting
        if ( $key ) {
            $setting = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$table_name} WHERE module_name = %s AND setting_key = %s", $module_id, 'settings' ) );
            // Unserialize the settings array if it exists and return the specific key, or the default
            $unserialized_settings = ( $setting ? maybe_unserialize( $setting ) : $default );
            return ( isset( $unserialized_settings[$key] ) ? $unserialized_settings[$key] : $default );
        }
        // If no specific key is provided, fetch all settings for the module
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT setting_key, setting_value FROM {$table_name} WHERE module_name = %s", $module_id ), OBJECT_K );
        $settings = array();
        if ( $results ) {
            foreach ( $results as $setting_key => $result ) {
                $settings[$setting_key] = maybe_unserialize( $result->setting_value );
            }
        }
        return ( !empty( $settings ) ? $settings : $default );
    }

    public function setModuleStatus( $module_id, $module_status ) {
        return $this->helper->ultimakit_update_module_setting(
            $module_id,
            'enabled',
            $module_status,
            true
        );
    }

    public function setModuleSettings( $module_id, $module_settings_ar ) {
        $module_settings_ar = array_map( function ( $value ) {
            return $value;
        }, $module_settings_ar );
        return $this->helper->ultimakit_update_module_setting(
            $module_id,
            'settings',
            $module_settings_ar,
            true
        );
    }

    // First, define an array of allowed keys (whitelist)
    private function get_allowed_settings_keys() {
        return array('noti_bar_text_area');
    }

    // Then, modify your settings processing code
    public function process_module_settings() {
        if ( !isset( $_POST['module_settings'] ) || !is_array( $_POST['module_settings'] ) ) {
            return false;
        }
        $allowed_keys = $this->get_allowed_settings_keys();
        $sanitized_settings = array();
        foreach ( $_POST['module_settings'] as $key => $value ) {
            // Sanitize value based on key type
            $sanitized_value = $this->sanitize_setting_value( $key, $value );
            if ( $sanitized_value !== null ) {
                $sanitized_settings[$key] = $sanitized_value;
            }
        }
        return $sanitized_settings;
    }

    // Add a method to handle different types of sanitization based on the key
    private function sanitize_setting_value( $key, $value ) {
        // Remove slashes and trim
        $value = stripslashes( trim( $value ) );
        // Sanitize based on key type
        switch ( $key ) {
            // HTML content
            case 'noti_bar_text_area':
                return wp_kses_post( $value );
            // Default fallback
            default:
                return sanitize_text_field( str_replace( '\\', '', $value ) );
        }
    }

    public function ultimakit_update_settings() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Verify the nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ultimakit_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'ultimakit-for-wp' ),
            ), 401 );
        }
        $helper = new UltimaKit_Helpers();
        // Check if migration has already been performed
        $migration_completed = get_option( 'ultimakit_migration_completed' );
        if ( !$migration_completed ) {
            $helper->ultimakit_check_and_migrate_settings();
        }
        $module_id = ( isset( $_POST['module_id'] ) ? sanitize_text_field( wp_unslash( $_POST['module_id'] ) ) : '' );
        $module_status = ( isset( $_POST['module_status'] ) ? sanitize_text_field( wp_unslash( $_POST['module_status'] ) ) : '' );
        // When getting the status from POST
        $module_status = ( isset( $_POST['module_status'] ) ? $this->validate_and_sanitize_status( wp_unslash( $_POST['module_status'] ) ) : 'off' );
        if ( isset( $_POST['module_settings'] ) && $_POST['module_settings'] ) {
            $module_settings = $this->process_module_settings( $_POST['module_settings'] );
        } else {
            $module_settings = ( isset( $_POST['module_settings'] ) ? sanitize_text_field( $_POST['module_settings'] ) : '' );
        }
        // Sanitize save mode and initialize response array
        $save_mode = ( isset( $_POST['save_mode'] ) ? sanitize_text_field( $_POST['save_mode'] ) : '' );
        $response = array();
        if ( 'settings' == $save_mode ) {
            if ( isset( $module_settings['custom_option'] ) && (1 === $module_settings['custom_option'] || true === $module_settings['custom_option']) ) {
                // For Custom JS and CSS Module
                if ( isset( $module_settings['css_js_snippets'] ) ) {
                    $helper->ultimakit_update_module_setting(
                        $module_id,
                        'settings',
                        $module_settings['css_js_snippets'],
                        true
                    );
                }
            } else {
                $helper->ultimakit_update_module_setting(
                    $module_id,
                    'settings',
                    $module_settings,
                    true
                );
            }
            $response = array(
                'message' => __( 'Module Settings Saved Successfully', 'ultimakit-for-wp' ),
            );
        } elseif ( 'on' === $module_status ) {
            $this->setModuleStatus( $module_id, $module_status );
            $response = array(
                'message' => __( 'Module Enabled Successfully', 'ultimakit-for-wp' ),
                'status'  => 'on',
            );
            $module_instance = $this->getModuleInstance( $module_id );
            if ( $module_instance ) {
                if ( method_exists( $module_instance, 'activate' ) ) {
                    $module_instance->activate();
                }
            }
            $helper->ultimakit_update_module_setting(
                $module_id,
                'enabled',
                'on',
                true
            );
        } elseif ( 'off' === $module_status ) {
            $this->setModuleStatus( $module_id, $module_status );
            $response = array(
                'message' => __( 'Module Disabled Successfully', 'ultimakit-for-wp' ),
                'status'  => 'off',
            );
            $module_instance = $this->getModuleInstance( $module_id );
            if ( $module_instance ) {
                if ( method_exists( $module_instance, 'deactivate' ) ) {
                    $module_instance->deactivate();
                }
            }
            $helper->ultimakit_update_module_setting(
                $module_id,
                'enabled',
                'off',
                true
            );
        }
        wp_send_json_success( $response );
    }

    /**
     * Get an instance of a module by its ID.
     *
     * @param string $module_id The ID of the module to retrieve.
     * @return object|null The module instance if found, null otherwise.
     */
    public function getModuleInstance( $module_id ) {
        // Sanitize the module ID
        $module_id = sanitize_key( $module_id );
        // If the module isn't already loaded, try to load it
        $module_class = $this->getModuleClass( $module_id );
        if ( $module_class && class_exists( $module_class ) ) {
            try {
                $instance = new $module_class();
                $this->modules[$module_id] = $instance;
                return $instance;
            } catch ( Exception $e ) {
                error_log( 'Failed to instantiate module ' . $module_id . ': ' . $e->getMessage() );
            }
        }
        return null;
    }

    /**
     * Get the class name for a module by its ID.
     *
     * @param string $module_id The ID of the module.
     * @return string|null The class name if found, null otherwise.
     */
    private function getModuleClass( $module_id ) {
        // Map module IDs to their class names
        // This could be enhanced to use a more dynamic approach
        $module_classes = $this->getModuleClassMap();
        return ( isset( $module_classes[$module_id] ) ? $module_classes[$module_id] : null );
    }

    /**
     * Get a mapping of module IDs to their class names.
     *
     * @return array Associative array of module IDs to class names.
     */
    private function getModuleClassMap() {
        // For now, we'll use a static mapping
        return apply_filters( 'ultimakit_module_class_map', [
            'custom_css_js' => 'WP_Ultimakit_Custom_CSS_JS',
            'admin_bar'     => 'WP_Ultimakit_Admin_Bar',
        ] );
    }

    // Add this before the switch statements
    private function validate_and_sanitize_status( $status ) {
        // Clean the input
        $status = trim( strtolower( strval( $status ) ) );
        // Validate against allowed values
        if ( !in_array( $status, ['on', 'off'], true ) ) {
            error_log( 'Invalid status detected: ' . $status );
            return 'off';
            // default value
        }
        return $status;
    }

    public function ultimakit_uninstall_settings() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Verify the nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ultimakit_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'ultimakit-for-wp' ),
            ), 401 );
        }
        $stat = sanitize_text_field( $_POST['stat'] );
        update_option( 'ultimakit_uninstall_settings', $stat, 'no' );
        wp_send_json_success( $stat );
    }

    public function export_settings_to_json() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Check for user capability
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Query all rows from the settings table
        $results = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
        if ( empty( $results ) ) {
            wp_send_json_error( 'No settings found to export.' );
        }
        // Unserialize values for JSON export readability
        foreach ( $results as &$row ) {
            $row['setting_value'] = maybe_unserialize( $row['setting_value'] );
        }
        // JSON filename and path
        $filename = date( 'Y-m-d_H-i-s' ) . '-ultimakit_settings_export.json';
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/' . $filename;
        // Save JSON to file
        file_put_contents( $file_path, json_encode( $results ) );
        // Send the file URL as the response
        wp_send_json_success( array(
            'file_url' => $file_url,
        ) );
    }

    public function import_settings_from_json() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Check for user capability
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Verify the nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ultimakit_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'ultimakit-for-wp' ),
            ), 401 );
        }
        // Handle the uploaded JSON file
        if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['error'] === 0 ) {
            $file = $_FILES['json_file'];
            // Ensure the file type is JSON
            if ( $file['type'] !== 'application/json' ) {
                wp_send_json_error( 'Invalid file type' );
            }
            // Read and decode JSON file contents
            $file_contents = file_get_contents( $file['tmp_name'] );
            if ( $file_contents === false ) {
                wp_send_json_error( 'Failed to read file' );
                return;
            }
            $settings = json_decode( $file_contents, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                wp_send_json_error( 'Invalid JSON: ' . json_last_error_msg() );
                return;
            }
            // Insert or update each row in the custom settings table
            foreach ( $settings as $row ) {
                // Ensure required fields are present in each row
                if ( !isset( 
                    $row['module_name'],
                    $row['setting_key'],
                    $row['setting_value'],
                    $row['autoload']
                 ) ) {
                    continue;
                    // Skip rows that do not have all necessary fields
                }
                // Serialize the setting_value to match the database requirements
                $setting_value = maybe_serialize( $row['setting_value'] );
                // Check if the setting already exists
                $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE module_name = %s AND setting_key = %s", $row['module_name'], $row['setting_key'] ) );
                if ( $exists ) {
                    // Update existing row
                    $wpdb->update( $table_name, array(
                        'setting_value' => $setting_value,
                        'autoload'      => $row['autoload'],
                    ), array(
                        'module_name' => $row['module_name'],
                        'setting_key' => $row['setting_key'],
                    ) );
                } else {
                    // Insert new row
                    $wpdb->insert( $table_name, array(
                        'module_name'   => $row['module_name'],
                        'setting_key'   => $row['setting_key'],
                        'setting_value' => $setting_value,
                        'autoload'      => $row['autoload'],
                    ) );
                }
            }
            // Clear cache for consistency
            wp_cache_delete( 'ultimakit_autoload_settings', 'ultimakit' );
            wp_send_json_success( 'Settings imported successfully' );
        }
        wp_send_json_error( 'No file uploaded' );
    }

    public function ultimakit_update_module_width() {
        // Check for user capability
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Verify the nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ultimakit_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'ultimakit-for-wp' ),
            ), 401 );
        }
        $view_modue = sanitize_text_field( $_POST['view'] );
        update_option( 'ultimakit_modules_list_view', $view_modue );
        wp_send_json_success( 'View settings successfully updated.' );
    }

    public function is_module_enabled( $module_name ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Query to check if the module is enabled
        $enabled = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$table_name} WHERE module_name = %s AND setting_key = %s", $module_name, 'enabled' ) );
        return maybe_unserialize( $enabled ) === 'on';
    }

    public function get_module_settings( $module_name ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Query to fetch all settings for the specified module
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT setting_value FROM {$table_name} WHERE module_name = %s AND setting_key = %s", $module_name, 'settings' ), ARRAY_A );
        // Initialize settings array
        $settings = array();
        // Debugging: Check if results are fetched
        if ( $results ) {
            foreach ( $results as $key => $result ) {
                // Capture the raw serialized value
                $raw_value = $result['setting_value'];
                // Apply maybe_unserialize
                $unserialized_value = maybe_unserialize( $raw_value );
                // Logging for debugging
                // error_log("Key: $key | Serialized Value: $raw_value | Unserialized Value: " . print_r($unserialized_value, true));
                // Assign unserialized value to settings array
                $settings[$key] = $unserialized_value;
            }
        } else {
            // error_log("No results found for module: $module_name");
        }
        return $settings;
    }

    public function autoloadModuleSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ultimakit_module_settings';
        // Cache key for all autoload settings
        $cache_key = 'ultimakit_autoload_settings';
        // Attempt to retrieve autoloaded settings from cache
        $autoload_settings = wp_cache_get( $cache_key, 'ultimakit' );
        if ( $autoload_settings === false ) {
            // Query to fetch all autoloaded settings from the database
            $results = $wpdb->get_results( "SELECT module_name, setting_key, setting_value FROM {$table_name} WHERE autoload = 1", OBJECT );
            // Prepare the settings array with unserialized values
            $autoload_settings = array();
            if ( $results ) {
                foreach ( $results as $result ) {
                    if ( !isset( $autoload_settings[$result->module_name] ) ) {
                        $autoload_settings[$result->module_name] = array();
                    }
                    $autoload_settings[$result->module_name][$result->setting_key] = maybe_unserialize( $result->setting_value );
                }
            }
            // Store in cache for future requests
            wp_cache_set( $cache_key, $autoload_settings, 'ultimakit' );
        }
        return $autoload_settings;
    }

}
