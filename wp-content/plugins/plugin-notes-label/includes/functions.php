<?php
/*
 * WPGear. Plugin Notes Label
 * functions.php
 */
 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	/* Get Options
	----------------------------------------------------------------- */
	function PluginNotesLabel_Get_Options () {
		$PluginNotesLabel_Options = get_option( "plugin-note-label_options", array(
			'adminonly' => 1,
			'show_author' => 1,
			'show_date' => 1,
			'clearing' => 1,
			'export' => '',
			)
		);

		return $PluginNotesLabel_Options;
	}
	
	/* Get Count of Labels
	----------------------------------------------------------------- */
	function PluginNotesLabel_Get_CountLabels () {
		$debug_process = 'f_get_count_labels';
		
		global $wpdb;
		
		$Options_table = $wpdb -> prefix .'options';
		$Options_Name = 'plugin-note-label_options';	
		
		$Query = "
			SELECT COUNT(*)
			FROM $Options_table
			WHERE (option_name LIKE 'plugin-note-label_%' AND option_name != %s)
		";
		
		$CountLabels = $wpdb -> get_var ($wpdb -> prepare ($Query, $Options_Name)); // phpcs:ignore	
		PluginNotesLabel_Debugger ($CountLabels, '$CountLabels', $debug_process, __FUNCTION__, __LINE__);
		
		return $CountLabels;
	}
	
	/* Get Labels
	----------------------------------------------------------------- */
	function PluginNotesLabel_Get_Labels () {
		$debug_process = 'f_get_labels';
		
		global $wpdb;
		
		$Options_table = $wpdb -> prefix .'options';
		$Options_Name = 'plugin-note-label_options';			
		
		$Query = "
			SELECT * 
			FROM $Options_table
			WHERE (option_name LIKE 'plugin-note-label_%' AND option_name != %s)
		";
		
		$Records = $wpdb -> get_results ($wpdb -> prepare ($Query, $Options_Name)); // phpcs:ignore
		PluginNotesLabel_Debugger ($Records, '$Records', $debug_process, __FUNCTION__, __LINE__);		
		
		return $Records;
	}
	
	/* Delete Labels
	----------------------------------------------------------------- */
	function PluginNotesLabel_Delete_Labels () {
		$debug_process = 'f_delete_labels';
		
		global $wpdb;
		
		$PluginNotesLabel_options_table = $wpdb -> prefix .'options';
		
		$Query = "
			DELETE 
			FROM $PluginNotesLabel_options_table 
			WHERE (option_name LIKE 'plugin-note-label_%' AND option_name != 'plugin-note-label_options')
		";
		PluginNotesLabel_Debugger ($Query, '$Query', $debug_process, __FUNCTION__, __LINE__);
		
		$Result = $wpdb -> query($Query); // phpcs:ignore
		PluginNotesLabel_Debugger ($Result, '$Result', $debug_process, __FUNCTION__, __LINE__);		
		
		return true;
	}
	
	/* Debugger. 
	----------------------------------------------------------------- */
	function PluginNotesLabel_Debugger ($Content, $Subject = null, $Process = null, $Function = '', $Line = '') {
		if (function_exists( 'WPGear_Debugger' )) {
			$Source = 'PNL';
			$Description = 'Plugin: Plugin Notes Label';
			
			$TimeStamp = true;
			
			$Parameters = array(
				'source' => $Source,
				'description' => $Description,
				'content' => $Content,
				'subject' => esc_html( $Subject ),
				'process' => esc_html( $Process ),
				'function' => esc_html( $Function ),
				'timestamp' => $TimeStamp,
				'line' => esc_html( $Line ),
			);
			
			WPGear_Debugger ($Parameters);
		}
	}