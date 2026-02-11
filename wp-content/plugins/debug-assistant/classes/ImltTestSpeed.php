<?php
if ( class_exists('ImltTestSpeed') ) return;

class ImltTestSpeed
{
    private static $startTime   = '';
    private static $onlyOnce    = false;

    public function __construct()
    {
        if ( !get_option('imtl_test_speed_enabled') || ( defined('DOING_AJAX') && DOING_AJAX ) || is_admin() ){
            return;
        }
        $this->start();
        add_action( 'wp_loaded', [ $this, 'pageLoaded' ] );
    }

    public function start()
    {
        self::$startTime = microtime( true );
    }

    public function pageLoaded()
    {
        global $wpdb;
        $time = microtime( true ) - self::$startTime;
        if ( $time <= 0 || self::$onlyOnce || ( defined('DOING_AJAX') && DOING_AJAX ) ){
            return;
        }
        self::$onlyOnce = true;
        $currentUrl = IMLT_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}imlt_speed_tests VALUES( null, %s, %s );", $currentUrl, $time );
        $wpdb->query( $query );
      

    }

}
