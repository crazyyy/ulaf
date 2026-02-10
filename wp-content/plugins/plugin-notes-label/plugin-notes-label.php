<?php
/*
Plugin Name: Plugin Notes Label
Plugin URI: https://wpgear.xyz/plugin-notes-label/
Description: Add your Notes to each plugin.
Version: 5.21
Text Domain: plugin-notes-label
Domain Path: /languages
Author: WPGear
Author URI: https://wpgear.xyz
License: GPLv2
*/

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	$PluginNotesLabel_plugin_url = plugin_dir_url( __FILE__); // со слэшем на конце	
	
	$PluginNotesLabel_UploadDir = wp_get_upload_dir();	
	$PluginNotesLabel_upload_dir_path = $PluginNotesLabel_UploadDir['basedir'];	// без слэша на конце. Папка Uploads
	$PluginNotesLabel_upload_url_path = $PluginNotesLabel_UploadDir['baseurl'];	// без слэша на конце. Папка Uploads
	$PluginNotesLabel_File_Export_Name = 'plugin_notes_label_export';
	
	$PluginNotesLabel_Options = get_option("plugin-note-label_options", array());

	$PluginNotesLabel_Setup_ShowAuthor 	= (isset($PluginNotesLabel_Options['show_author'])) ? intval($PluginNotesLabel_Options['show_author']) : 1;
	$PluginNotesLabel_Setup_ShowDate 	= (isset($PluginNotesLabel_Options['show_date'])) ? intval($PluginNotesLabel_Options['show_date']) : 1;	
	
	$PluginNotesLabel_LocalePath = dirname (plugin_basename ( __FILE__ )) . '/languages/';
	// __('Add your Notes to each plugin.', 'plugin-notes-label');	
	
	include_once( __DIR__ .'/includes/functions.php' );
	include_once( __DIR__ .'/includes/admin/admin.php' );
	
	/* AJAX Processing
	----------------------------------------------------------------- */
    add_action( 'wp_ajax_plugin_note_label', 'PluginNotesLabel_Ajax' );
    function PluginNotesLabel_Ajax(){		
		include_once ('includes/ajax_note.php');
    }
	
	/* Translate.
	----------------------------------------------------------------- */
	add_action ('init', 'PluginNotesLabel_Action_init');
	function PluginNotesLabel_Action_init() {
		global $PluginNotesLabel_LocalePath;
				
		$Result = load_plugin_textdomain ('plugin-notes-label', false, $PluginNotesLabel_LocalePath);
	}