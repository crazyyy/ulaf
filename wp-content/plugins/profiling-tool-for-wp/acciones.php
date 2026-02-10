<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if( isset( $_GET['ptfwp'] ) )
{
    if(!defined('SAVEQUERIES'))
        define('SAVEQUERIES', true);

    add_action( 'wp_loaded','ptfwp_plugin_control' );
    
    if( $_GET['ptfwp'] == '3' )
    {
        add_action('wp_footer', 'ptfwp_name_show_debug_queries', PHP_INT_MAX);
    }
}
 
function ptfwp_name_show_debug_queries()
{
    $peak_memory_usage = memory_get_peak_usage();

    echo '[PEAK_MEMORY_USAGE]'.number_format($peak_memory_usage / 1024 / 1024, 3).'[/PEAK_MEMORY_USAGE]';

    if (defined('SAVEQUERIES') && SAVEQUERIES) {
        global $wpdb;

        echo '[QUERIES_NUMBER]'.count($wpdb->queries).'[/QUERIES_NUMBER]';
        
        exit('closing request');
    }
}

function ptfwp_plugin_control() {

    $active_plugins = get_option('active_plugins');
    $ptfwp_backup_active_plugins = get_option('ptfwp_backup_active_plugins');

    if( $_GET['ptfwp'] == '1' ) // disable plugin
    {
        if( isset( $_GET['plugin'] ))
        {
            $currentPlugin = sanitize_text_field($_GET['plugin']);
            $key = array_search($currentPlugin, $active_plugins);
            if (false !== $key) {
                unset($active_plugins[$key]);
                
                
                exit('disabled: ' . esc_html(sanitize_text_field($_GET['plugin'])));
            }

            if( $_GET['plugin'] == 'NONE' )
            {
                
            }
        }

        exit('closing request');
    }
    elseif( $_GET['ptfwp'] == '2' )
    {
        if(isset($_GET['plugin']))
        {   
            $currentPlugin = sanitize_text_field($_GET['plugin']);
            $key = array_search($currentPlugin, $active_plugins);

            if($currentPlugin != 'NONE' && $currentPlugin != 'ALL')
            if (false === $key) {
            

                exit('enabled: '. esc_html(sanitize_text_field($_GET['plugin'])));
            }

            if($_GET['plugin'] == 'NONE')
            {
                
            }
        }

        exit('closing request');
    }
    elseif( $_GET['ptfwp'] == '3' )
    {
        
    }
}

