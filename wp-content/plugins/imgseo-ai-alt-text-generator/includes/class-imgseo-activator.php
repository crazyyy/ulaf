<?php

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**

 * Classe ImgSEO_Activator

 * Gestisce l'attivazione del plugin e la creazione delle tabelle di database

 */

class ImgSEO_Activator {



    /**

     * Esegui le operazioni di attivazione del plugin

     */

    public static function activate() {

        // Crea tabella per tenere traccia dei lavori di elaborazione

        self::create_jobs_table();



        // Crea tabella per i log di elaborazione

        self::create_logs_table();



        // Configura i cron job

        self::setup_cron_jobs();



        // Imposta le opzioni predefinite

        self::set_default_options();



        // Upgrade existing tables if needed

        self::upgrade_jobs_table();

    }



    /**

     * Crea la tabella dei job

     */

    private static function create_jobs_table() {

        global $wpdb;

        $table_name = $wpdb->prefix . 'imgseo_jobs';



        $charset_collate = $wpdb->get_charset_collate();



        $sql = "CREATE TABLE $table_name (

            id mediumint(9) NOT NULL AUTO_INCREMENT,

            job_id varchar(50) NOT NULL,

            total_images int NOT NULL,

            processed_images int NOT NULL DEFAULT 0,

            images_data longtext NOT NULL,

            overwrite tinyint(1) NOT NULL DEFAULT 0,

            update_title tinyint(1) NOT NULL DEFAULT 0,

            update_caption tinyint(1) NOT NULL DEFAULT 0,

            update_description tinyint(1) NOT NULL DEFAULT 0,

            processing_speed varchar(20) DEFAULT 'normal',

            status varchar(20) NOT NULL DEFAULT 'pending',

            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,

            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,

            PRIMARY KEY  (id),

            KEY job_id (job_id)

        ) $charset_collate;";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

    }



    /**

     * Crea la tabella dei log

     */

    private static function create_logs_table() {

        global $wpdb;

        $log_table_name = $wpdb->prefix . 'imgseo_logs';



        $charset_collate = $wpdb->get_charset_collate();



        $sql = "CREATE TABLE $log_table_name (

            id mediumint(9) NOT NULL AUTO_INCREMENT,

            job_id varchar(50) NOT NULL,

            image_id int(11) NOT NULL,

            filename varchar(255) NOT NULL,

            alt_text text NOT NULL,

            status varchar(20) NOT NULL DEFAULT 'success',

            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,

            PRIMARY KEY  (id),

            KEY job_id (job_id)

        ) $charset_collate;";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);



        // Create table for image rename logs

        self::create_rename_logs_table();

    }



    /**

     * Create the table for image renamer logs

     */

    private static function create_rename_logs_table() {

        global $wpdb;

        $log_table = $wpdb->prefix . 'imgseo_rename_logs';



        $charset_collate = $wpdb->get_charset_collate();



        $sql = "CREATE TABLE $log_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            image_id bigint(20) NOT NULL,
            old_filename varchar(255) NOT NULL,
            new_filename varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'success',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            operation_type varchar(20) DEFAULT 'single',
            batch_id varchar(36) DEFAULT NULL,
            operation_details text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY image_id (image_id),
            KEY created_at (created_at),
            KEY user_id (user_id),
            KEY batch_id (batch_id)
        ) $charset_collate;";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

    }



    /**

     * Configura i cron job

     */

    private static function setup_cron_jobs() {

        // Aggiungi l'intervallo personalizzato per il cron

        add_filter('cron_schedules', array('ImgSEO_Activator', 'add_cron_schedules'));



        // Rimuovi qualsiasi evento cron esistente per evitare duplicazioni

        wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);

        wp_clear_scheduled_hook('imgseo_check_stuck_jobs');



        // Pianifica il controllo dei job bloccati ogni giorno

        wp_schedule_event(time(), 'daily', 'imgseo_check_stuck_jobs');

    }



    /**

     * Aggiunge intervalli personalizzati per i cron job

     */

    public static function add_cron_schedules($schedules) {

        $schedules['every_minute'] = array(

            'interval' => 60,

            'display'  => __('Ogni Minuto', 'imgseo-ai-alt-text-generator')

        );

        $schedules['every_30_seconds'] = array(

            'interval' => 30,

            'display'  => __('Ogni 30 Secondi', 'imgseo-ai-alt-text-generator')

        );

        $schedules['every_2_minutes'] = array(

            'interval' => 120,

            'display'  => __('Ogni 2 Minuti', 'imgseo-ai-alt-text-generator')

        );

        $schedules['every_5_minutes'] = array(

            'interval' => 300,

            'display'  => __('Ogni 5 Minuti', 'imgseo-ai-alt-text-generator')

        );

        return $schedules;

    }



    /**

     * Upgrade jobs table to add new columns for bulk-specific settings

     */

    private static function upgrade_jobs_table() {

        global $wpdb;

        $table_name = $wpdb->prefix . 'imgseo_jobs';



        // Check if table exists

        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

        if ($table_exists !== $table_name) {

            return; // Table doesn't exist yet, will be created by create_jobs_table()

        }



        // Check and add update_title column if missing

        $column_exists = $wpdb->get_results($wpdb->prepare(

            "SHOW COLUMNS FROM `$table_name` LIKE %s",

            'update_title'

        ));

        if (empty($column_exists)) {

            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN update_title tinyint(1) NOT NULL DEFAULT 0 AFTER overwrite");

        }



        // Check and add update_caption column if missing

        $column_exists = $wpdb->get_results($wpdb->prepare(

            "SHOW COLUMNS FROM `$table_name` LIKE %s",

            'update_caption'

        ));

        if (empty($column_exists)) {

            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN update_caption tinyint(1) NOT NULL DEFAULT 0 AFTER update_title");

        }



        // Check and add update_description column if missing

        $column_exists = $wpdb->get_results($wpdb->prepare(

            "SHOW COLUMNS FROM `$table_name` LIKE %s",

            'update_description'

        ));

        if (empty($column_exists)) {

            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN update_description tinyint(1) NOT NULL DEFAULT 0 AFTER update_caption");

        }



        // Check and add processing_speed column if missing

        $column_exists = $wpdb->get_results($wpdb->prepare(

            "SHOW COLUMNS FROM `$table_name` LIKE %s",

            'processing_speed'

        ));

        if (empty($column_exists)) {

            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN processing_speed varchar(20) DEFAULT 'normal' AFTER update_description");

        }

    }



    /**

     * Imposta le opzioni predefinite

     */

    private static function set_default_options() {

        // Impostazioni generali - KEEP autoload for frequently accessed options
        add_option('imgseo_language', 'english'); // Used frequently
        add_option('imgseo_max_characters', 125); // Used frequently
        add_option('imgseo_auto_generate', 0); // Checked on every upload

        // Rarely used options - DISABLE autoload to reduce memory
        add_option('imgseo_include_page_title', 1, '', 'no');
        add_option('imgseo_include_image_name', 1, '', 'no');
        add_option('imgseo_overwrite', 0, '', 'no');

        // Batch/Cron settings - only used during batch operations
        add_option('imgseo_batch_size', 5, '', 'no');
        add_option('imgseo_cron_interval', 'every_2_minutes', '', 'no');
        add_option('imgseo_last_cron_run', 0, '', 'no');

        // Update settings - only used during generation
        add_option('imgseo_update_title', 0, '', 'no');
        add_option('imgseo_update_caption', 0, '', 'no');
        add_option('imgseo_update_description', 0, '', 'no');

        // API settings
        add_option('imgseo_api_verified', 0, '', 'no');

    }

}
