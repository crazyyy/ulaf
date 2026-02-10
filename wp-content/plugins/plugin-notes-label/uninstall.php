<?php
/*
 * WPGear. Plugin Notes Label
 * uninstall.php
 */	

	if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
	}
	
	if (! function_exists( 'PluginNotesLabel_Get_Options' )) {
		include_once( __DIR__ .'/includes/functions.php' );
	}
	
	$PluginNotesLabel_Options = PluginNotesLabel_Get_Options();

	$PluginNotesLabel_Setup_Clearing = isset( $PluginNotesLabel_Options['clearing'] ) ? $PluginNotesLabel_Options['clearing'] : 0;
	
	if ($PluginNotesLabel_Setup_Clearing) {
		// Удаляем Options Плагина
		
		global $wpdb;
		
		$PluginNotesLabel_options_table = $wpdb -> prefix .'options';
				
		$PluginNotesLabel_Query = "
			DELETE 
			FROM $PluginNotesLabel_options_table 
			WHERE option_name LIKE 'plugin-note-label_%'
		";
		
		$wpdb -> query($PluginNotesLabel_Query); // phpcs:ignore	
	}