<?php
if ( !class_exists('ImltInstall') ) return;

class ImltInstall
{
    public function __construct()
    {
        $this->createTables();
    }

    public function createTables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "imlt_speed_tests";
        if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
          require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
          $sql = "CREATE TABLE " . $table_name . " (
                                id int(11) NOT NULL AUTO_INCREMENT,
                                url VARCHAR(500) NOT NULL,
                                loading_time VARCHAR(10) NOT NULL,
                                PRIMARY KEY (`id`)
          );
          ";
          dbDelta ( $sql );
        }
    }

}
