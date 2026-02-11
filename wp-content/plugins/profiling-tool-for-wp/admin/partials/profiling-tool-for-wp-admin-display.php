<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://dnsempresas.com/
 * @since      1.0.0
 *
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$tabla = $wpdb->prefix . 'ptfwp_options';

$query = $wpdb->prepare( "SELECT * FROM {$tabla} WHERE ID = %d", 1 );

$options = $wpdb->get_row( $query, ARRAY_A );

switch( $options["LANGUAGE"]  ){
    case "EN":	
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'admin/partials/profiling-tool-for-wp-admin-en-display.php' );	
        break;
		
    case "ES":
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'admin/partials/profiling-tool-for-wp-admin-es-display.php' );
        break;
		
    case "GL":
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'admin/partials/profiling-tool-for-wp-admin-gl-display.php' );
        break; 
		
    default:
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'admin/partials/profiling-tool-for-wp-admin-es-display.php' );
		
}

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
