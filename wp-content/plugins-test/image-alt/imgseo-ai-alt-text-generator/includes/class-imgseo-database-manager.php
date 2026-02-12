<?php
/**
 * Classe per la gestione del database del sistema ImgSEO
 * Gestisce la creazione e aggiornamento delle tabelle per il nuovo sistema di scansione
 *
 * @package ImgSEO
 * @since 2.0.0
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe ImgSEO_Database_Manager
 * 
 * Gestisce la struttura del database per il sistema di scansione avanzato
 */
class ImgSEO_Database_Manager {
    
    /**
     * Versione corrente del database
     */
    const DB_VERSION = '2.0.0';
    
    /**
     * Istanza singleton della classe
     *
     * @var ImgSEO_Database_Manager|null
     */
    private static $instance = null;
    
    /**
     * Ottiene l'istanza singleton della classe
     *
     * @return ImgSEO_Database_Manager
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Costruttore privato per implementare il pattern singleton
     */
    private function __construct() {
        // Hook per l'attivazione del plugin
        register_activation_hook(IMGSEO_FILE, array($this, 'create_tables'));
        
        // Hook per verificare aggiornamenti database
        add_action('plugins_loaded', array($this, 'check_database_version'));
    }
    
    /**
     * Verifica se il database necessita di aggiornamenti
     */
    public function check_database_version() {
        $installed_version = get_option('imgseo_db_version', '1.0.0');
        
        if (version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->create_tables();
            update_option('imgseo_db_version', self::DB_VERSION);
        }
    }
    
    /**
     * Crea tutte le tabelle necessarie per il sistema
     */
    public function create_tables() {
        global $wpdb;
        
        // Richiede il file per dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabella principale per il registro delle immagini per contenuto
        $table_content_images = $wpdb->prefix . 'imgseo_content_images';
        $sql_content_images = "CREATE TABLE $table_content_images (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_type varchar(50) NOT NULL,
            content_id bigint(20) DEFAULT NULL,
            content_url varchar(500) DEFAULT NULL,
            image_url varchar(500) NOT NULL,
            image_context varchar(100) DEFAULT 'content',
            attachment_id bigint(20) DEFAULT NULL,
            has_alt_text tinyint(1) DEFAULT 0,
            alt_text text DEFAULT NULL,
            image_title varchar(255) DEFAULT NULL,
            source_location varchar(255) DEFAULT NULL,
            image_width int DEFAULT NULL,
            image_height int DEFAULT NULL,
            last_scanned datetime DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            KEY content_type_id (content_type, content_id),
            KEY content_url (content_url(255)),
            KEY attachment_id (attachment_id),
            KEY image_url (image_url(255)),
            KEY is_active (is_active),
            KEY last_scanned (last_scanned)
        ) $charset_collate;";
        
        // Tabella per l'indice veloce URL → Attachment ID
        $table_url_index = $wpdb->prefix . 'imgseo_url_index';
        $sql_url_index = "CREATE TABLE $table_url_index (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            image_url varchar(500) NOT NULL,
            attachment_id bigint(20) DEFAULT NULL,
            url_hash varchar(32) NOT NULL,
            last_verified datetime DEFAULT CURRENT_TIMESTAMP,
            verification_count int DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY url_hash (url_hash),
            KEY attachment_id (attachment_id),
            KEY last_verified (last_verified)
        ) $charset_collate;";
        
        // Tabella per lo stato della scansione
        $table_scan_status = $wpdb->prefix . 'imgseo_scan_status';
        $sql_scan_status = "CREATE TABLE $table_scan_status (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_type varchar(50) NOT NULL,
            content_id bigint(20) DEFAULT NULL,
            content_url varchar(500) DEFAULT NULL,
            last_scanned datetime DEFAULT CURRENT_TIMESTAMP,
            scan_duration float DEFAULT NULL,
            images_found int DEFAULT 0,
            scan_status enum('pending', 'scanning', 'completed', 'error') DEFAULT 'pending',
            error_message text DEFAULT NULL,
            scan_method varchar(100) DEFAULT 'auto',
            PRIMARY KEY (id),
            UNIQUE KEY content_identifier (content_type, content_id, content_url(255)),
            KEY scan_status (scan_status),
            KEY last_scanned (last_scanned)
        ) $charset_collate;";
        
        // Tabella per le statistiche aggregate (cache)
        $table_stats_cache = $wpdb->prefix . 'imgseo_stats_cache';
        $sql_stats_cache = "CREATE TABLE $table_stats_cache (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            stat_key varchar(100) NOT NULL,
            stat_value longtext NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY stat_key (stat_key),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        // Esegui la creazione delle tabelle
        dbDelta($sql_content_images);
        dbDelta($sql_url_index);
        dbDelta($sql_scan_status);
        dbDelta($sql_stats_cache);
        
        // Crea indici aggiuntivi per performance
        $this->create_additional_indexes();
        
        // Inizializza dati di base
        $this->initialize_default_data();
    }
    
    /**
     * Crea indici aggiuntivi per migliorare le performance
     */
    private function create_additional_indexes() {
        global $wpdb;
        
        $table_content_images = $wpdb->prefix . 'imgseo_content_images';
        
        // Indice composito per query frequenti
        // DDL queries cannot be prepared, table name is safe as it comes from get_table_name()
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_content_active ON `$table_content_images` (content_type, is_active, last_scanned)");
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_attachment_alt ON `$table_content_images` (attachment_id, has_alt_text)");
    }
    
    /**
     * Inizializza dati di default
     */
    private function initialize_default_data() {
        // Scanner options - only used during scan operations, disable autoload
        add_option('imgseo_universal_scanner_enabled', 1, '', 'no');
        add_option('imgseo_scan_batch_size', 50, '', 'no');
        add_option('imgseo_scan_timeout', 300, '', 'no'); // 5 minuti
        add_option('imgseo_cache_expiry_hours', 24, '', 'no');
        add_option('imgseo_auto_scan_frequency', 'daily', '', 'no');
        add_option('imgseo_scan_external_images', 1, '', 'no');
        add_option('imgseo_scan_background_images', 1, '', 'no');
        add_option('imgseo_scan_page_builders', 1, '', 'no');
    }
    
    /**
     * Ottiene il nome della tabella con prefisso
     *
     * @param string $table_name Nome della tabella senza prefisso
     * @return string Nome completo della tabella
     */
    public function get_table_name($table_name) {
        global $wpdb;
        return $wpdb->prefix . 'imgseo_' . $table_name;
    }
    
    /**
     * Verifica se le tabelle esistono
     *
     * @return bool
     */
    public function tables_exist() {
        global $wpdb;
        
        $tables = array(
            'imgseo_content_images',
            'imgseo_url_index',
            'imgseo_scan_status',
            'imgseo_stats_cache'
        );
        
        foreach ($tables as $table) {
            $table_name = $this->get_table_name(str_replace('imgseo_', '', $table));
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if ($result !== $table_name) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Pulisce i dati obsoleti
     */
    public function cleanup_old_data() {
        global $wpdb;

        $table_content_images = $this->get_table_name('content_images');
        $table_url_index = $this->get_table_name('url_index');
        $table_stats_cache = $this->get_table_name('stats_cache');
        $table_scan_status = $this->get_table_name('scan_status');
        $table_rename_logs = $wpdb->prefix . 'imgseo_rename_logs';

        // Retention in giorni (di default 7 giorni per i log)
        $log_retention_days = 7;

        // Rimuovi immagini non attive più vecchie di 30 giorni
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_content_images
             WHERE is_active = 0
             AND last_scanned < DATE_SUB(NOW(), INTERVAL %d DAY)",
            30
        ));

        // Rimuovi URL index non verificati da più di 7 giorni
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_url_index
             WHERE last_verified < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $log_retention_days
        ));

        // Rimuovi cache statistiche scadute
        $wpdb->query("DELETE FROM $table_stats_cache WHERE expires_at IS NOT NULL AND expires_at < NOW()");

        // Rimuovi log di scansione più vecchi di 7 giorni
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_scan_status
             WHERE last_scanned < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $log_retention_days
        ));

        // Rimuovi log file più vecchi di 7 giorni (file-based logging)
        if (class_exists('ImgSEO_File_Logger')) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->cleanup_old_logs($log_retention_days);
        }

        // Rimuovi log di rename più vecchi di 7 giorni (se la tabella esiste)
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_rename_logs));
        if ($table_exists === $table_rename_logs) {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_rename_logs
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $log_retention_days
            ));
        }
    }
    
    /**
     * Ottiene statistiche sulle tabelle
     *
     * @return array
     */
    public function get_database_stats() {
        global $wpdb;
        
        $stats = array();
        
        $tables = array(
            'content_images' => 'Immagini per Contenuto',
            'url_index' => 'Indice URL',
            'scan_status' => 'Stato Scansioni',
            'stats_cache' => 'Cache Statistiche'
        );
        
        foreach ($tables as $table => $label) {
            $table_name = $this->get_table_name($table);
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $stats[$table] = array(
                'label' => $label,
                'count' => intval($count)
            );
        }
        
        return $stats;
    }
    
    /**
     * Esegue la migrazione dei dati dal vecchio sistema
     */
    public function migrate_legacy_data() {
        // Questa funzione sarà implementata per migrare i dati esistenti
        // dal vecchio sistema al nuovo formato

        // Per ora, registra che la migrazione è stata eseguita
        update_option('imgseo_legacy_migration_completed', time());
    }

    /**
     * Reset dei log e della cache (mantiene i dati delle immagini)
     */
    public function reset_logs_and_cache() {
        global $wpdb;

        $table_scan_status = $this->get_table_name('scan_status');
        $table_stats_cache = $this->get_table_name('stats_cache');
        $table_rename_logs = $wpdb->prefix . 'imgseo_rename_logs';
        $table_jobs = $wpdb->prefix . 'imgseo_jobs';

        // Svuota la tabella scan_status
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("TRUNCATE TABLE `$table_scan_status`");

        // Svuota la tabella stats_cache
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("TRUNCATE TABLE `$table_stats_cache`");

        // Svuota la tabella jobs
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("TRUNCATE TABLE `$table_jobs`");

        // Svuota la tabella rename_logs se esiste
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_rename_logs));
        if ($table_exists === $table_rename_logs) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("TRUNCATE TABLE `$table_rename_logs`");
        }

        // Elimina tutti i file di log (file-based logging)
        if (class_exists('ImgSEO_File_Logger')) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->cleanup_old_logs(0); // 0 = delete all
        }

        // Elimina transient del cleanup
        delete_transient('imgseo_last_log_cleanup');

        return true;
    }

    /**
     * Reset completo di tutti i dati del plugin (mantiene le tabelle)
     * FACTORY RESET: Riporta il plugin allo stato iniziale cancellando tutti i dati e le impostazioni
     */
    public function reset_all_data() {
        global $wpdb;

        $tables = array(
            'imgseo_content_images',
            'imgseo_url_index',
            'imgseo_scan_status',
            'imgseo_stats_cache',
            'imgseo_jobs',
            'imgseo_rename_logs'
        );

        // Svuota tutte le tabelle
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            // Verifica se la tabella esiste prima di truncarla
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if ($table_exists === $table_name) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->query("TRUNCATE TABLE `$table_name`");
            }
        }

        // Elimina tutti i file di log (file-based logging)
        if (class_exists('ImgSEO_File_Logger')) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->cleanup_old_logs(0); // 0 = delete all
        }

        // ==================================================
        // DELETE ALL PLUGIN OPTIONS (Factory Reset)
        // ==================================================

        // API Settings
        delete_option('imgseo_api_key');
        delete_option('imgseo_api_verified');
        delete_option('imgseo_api_credits');
        delete_option('imgseo_credits');
        delete_option('imgseo_plan');
        delete_option('imgseo_last_check');

        // General Settings
        delete_option('imgseo_language');
        delete_option('imgseo_max_characters');
        delete_option('imgseo_include_page_title');
        delete_option('imgseo_include_image_name');
        delete_option('imgseo_overwrite');
        delete_option('imgseo_auto_generate');
        delete_option('imgseo_always_use_base64');
        delete_option('imgseo_footer_badge');
        delete_option('imgseo_support_link');
        delete_option('imgseo_delete_data_on_uninstall');
        delete_option('imgseo_update_title');
        delete_option('imgseo_update_caption');
        delete_option('imgseo_update_description');
        delete_option('imgseo_processing_speed');

        // Prompt Settings
        delete_option('imgseo_custom_prompt');
        delete_option('imgseo_woocommerce_prompt');
        delete_option('imgseo_enable_woocommerce_prompt');

        // Database/Scanner Settings
        delete_option('imgseo_db_version');
        delete_option('imgseo_universal_scanner_enabled');
        delete_option('imgseo_scan_batch_size');
        delete_option('imgseo_scan_timeout');
        delete_option('imgseo_cache_expiry_hours');
        delete_option('imgseo_auto_scan_frequency');
        delete_option('imgseo_scan_external_images');
        delete_option('imgseo_scan_background_images');
        delete_option('imgseo_scan_page_builders');
        delete_option('imgseo_legacy_migration_completed');
        delete_option('imgseo_initial_scan_completed');

        // Renamer Settings
        delete_option('imgseo_log_retention_days');
        delete_option('imgseo_renamer_ai_max_words');
        delete_option('imgseo_renamer_ai_include_post_title');
        delete_option('imgseo_renamer_ai_include_category');
        delete_option('imgseo_renamer_enabled');
        delete_option('imgseo_renamer_mode');
        delete_option('imgseo_renamer_pattern');

        // Compression Settings
        delete_option('imgseo_compression_enabled');
        delete_option('imgseo_compression_quality');
        delete_option('imgseo_compression_format');
        delete_option('imgseo_compression_enable_webp');
        delete_option('imgseo_compression_enable_avif');
        delete_option('imgseo_compression_webp_quality');
        delete_option('imgseo_compression_avif_quality');
        delete_option('imgseo_compression_optimize_web');
        delete_option('imgseo_compression_strip_metadata');
        delete_option('imgseo_compression_auto_remove_larger');
        delete_option('imgseo_compression_serving_method');

        // Sitemap & Structured Data Settings
        delete_option('imgseo_enable_sitemap');
        delete_option('imgseo_sitemap_include_external');
        delete_option('imgseo_sitemap_auto_refresh');
        delete_option('imgseo_sitemap_auto_refresh_interval');
        delete_option('imgseo_sitemap_last_generated');
        delete_option('imgseo_enable_structured_data');
        delete_option('imgseo_structured_data_type');

        // ==================================================
        // DELETE TRANSIENTS
        // ==================================================
        delete_transient('imgseo_stats_cache');
        delete_transient('imgseo_api_credits');
        delete_transient('imgseo_last_log_cleanup');

        // ==================================================
        // CLEAR SCHEDULED HOOKS
        // ==================================================
        wp_clear_scheduled_hook('imgseo_cleanup_old_data');
        wp_clear_scheduled_hook('imgseo_refresh_credits');
        wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);

        return true;
    }

    /**
     * Rimuove tutte le tabelle del plugin (per disinstallazione)
     */
    public function drop_tables() {
        global $wpdb;

        $tables = array(
            'imgseo_content_images',
            'imgseo_url_index',
            'imgseo_scan_status',
            'imgseo_stats_cache',
            'imgseo_jobs',
            'imgseo_rename_logs'
        );

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            // DDL queries cannot be prepared, table name is safe as it's constructed with $wpdb->prefix
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("DROP TABLE IF EXISTS `$table_name`");
        }

        // Elimina tutti i file di log e la directory
        if (class_exists('ImgSEO_File_Logger')) {
            $file_logger = ImgSEO_File_Logger::get_instance();
            $file_logger->cleanup_old_logs(0); // Delete all log files

            // Rimuovi la directory dei log
            $log_dir = $file_logger->get_log_directory();
            if (is_dir($log_dir)) {
                // Rimuovi i file di protezione
                wp_delete_file($log_dir . '/.htaccess');
                wp_delete_file($log_dir . '/index.php');

                if (!class_exists('WP_Filesystem_Direct')) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                $filesystem = new WP_Filesystem_Direct(null);
                $filesystem->rmdir($log_dir);
            }
        }

        // Rimuovi le opzioni
        delete_option('imgseo_db_version');
        delete_option('imgseo_universal_scanner_enabled');
        delete_option('imgseo_scan_batch_size');
        delete_option('imgseo_scan_timeout');
        delete_option('imgseo_cache_expiry_hours');
        delete_option('imgseo_auto_scan_frequency');
        delete_option('imgseo_scan_external_images');
        delete_option('imgseo_scan_background_images');
        delete_option('imgseo_scan_page_builders');
        delete_option('imgseo_legacy_migration_completed');

        // Elimina transient del cleanup
        delete_transient('imgseo_last_log_cleanup');
    }
}
