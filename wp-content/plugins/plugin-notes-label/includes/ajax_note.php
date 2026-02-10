<?php
/*
 * WPGear. Plugin Notes Label
 * ajax_note.php
 */	 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	$PluginNotesLabel_DebugProcess = 'ajax_note_processing';
	PluginNotesLabel_Debugger ($_REQUEST, '$_REQUEST', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);	 // phpcs:ignore
	
	$PluginNotesLabel_NonceKey = 'AJAX_Processing Plugin Notes Label';
	$PluginNotesLabel_Nonce = wp_create_nonce ($PluginNotesLabel_NonceKey);
		
	$PluginNotesLabel_Mode 			= isset($_REQUEST['mode']) ? sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ) : null;
	$PluginNotesLabel_NoteSlug 		= isset($_REQUEST['slug']) ? sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) ) : null;	
	$PluginNotesLabel_NoteContent	= isset($_REQUEST['note']) ? sanitize_text_field( wp_unslash( $_REQUEST['note'] ) ) : null;	
	$PluginNotesLabel_NoteNames		= isset($_REQUEST['names']) ? sanitize_text_field( wp_unslash( $_REQUEST['names'] ) ) : null;
	$PluginNotesLabel_NonceRequest 	= isset($_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : 'none';
	
	if (!wp_verify_nonce( $PluginNotesLabel_NonceRequest, $PluginNotesLabel_NonceKey )) {
		PluginNotesLabel_Debugger ('False', 'verify_nonce', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);
		
		exit;
	}
	
	$current_user = wp_get_current_user();	
	$PluginNotesLabel_UserName 	= $current_user -> user_login;
	
	$PluginNotesLabel_Date = current_time('d.m.Y');
	$PluginNotesLabel_TimeStamp = null;
	$PluginNotesLabel_Notes = array();

	global $PluginNotesLabel_upload_dir_path, $PluginNotesLabel_File_Export_Name;	

	$PluginNotesLabel_Result = false; 
	
	// Save Note
	if ($PluginNotesLabel_Mode == 'save_note') {			
		$PluginNotesLabel_Note = array(
			'user' => $PluginNotesLabel_UserName,
			'content' => $PluginNotesLabel_NoteContent,
			'date' => $PluginNotesLabel_Date
		);
		
		update_option( "plugin-note-label_$PluginNotesLabel_NoteSlug", $PluginNotesLabel_Note ); // phpcs:ignore
	
		$PluginNotesLabel_Result = true;	
	}

	// Export all Notes.
	if ($PluginNotesLabel_Mode == 'export') {
		$PluginNotesLabel_Records = PluginNotesLabel_Get_Labels ();		
		
		if ($PluginNotesLabel_Records) {
			$PluginNotesLabel_OptionName 	= null;
			$PluginNotesLabel_OptionValue 	= null;;
			$PluginNotesLabel_Note 			= array();
			$PluginNotesLabel_ExportNotes 	= '';
			
			foreach ($PluginNotesLabel_Records as $Record) {
				$PluginNotesLabel_OptionName 	= $Record -> option_name;
				$PluginNotesLabel_OptionValue 	= $Record -> option_value;
				
				$PluginNotesLabel_NoteSlug = substr($PluginNotesLabel_OptionName, 18);
				
				if (is_serialized ($PluginNotesLabel_OptionValue)) {
					$PluginNotesLabel_NoteContentArray = unserialize( trim( $PluginNotesLabel_OptionValue ) );
					
					$PluginNotesLabel_Note = array (
						'slug' 		=> $PluginNotesLabel_NoteSlug,
						'content' 	=> $PluginNotesLabel_NoteContentArray['content'],
						'user' 		=> $PluginNotesLabel_NoteContentArray['user'],							
						'date' 		=> $PluginNotesLabel_NoteContentArray['date'],
					);						
					
					$PluginNotesLabel_ExportNotes .= wp_json_encode( $PluginNotesLabel_Note, JSON_UNESCAPED_UNICODE ) ."\r\n";	
				} else {
					// нет данных или самая ранняя версия.
					if ($PluginNotesLabel_OptionValue) {
						$PluginNotesLabel_Note = array (
							'slug' 		=> $PluginNotesLabel_NoteSlug,
							'content' 	=> $PluginNotesLabel_OptionValue,
							'user' 		=> $PluginNotesLabel_UserName,								
							'date' 		=> $PluginNotesLabel_Date,
						);
						
						$PluginNotesLabel_ExportNotes .= wp_json_encode( $PluginNotesLabel_Note, JSON_UNESCAPED_UNICODE ) ."\r\n";
					}
				}		
			}

			// Create File
			$PluginNotesLabel_Options = get_option( "plugin-note-label_options", array() );
			
			$PluginNotesLabel_TimeStamp = $PluginNotesLabel_Options['export'];
			
			if ($PluginNotesLabel_TimeStamp) {
				// Удаляем предыдущий Файл.
				$PluginNotesLabel_UploadDirPath = $PluginNotesLabel_upload_dir_path .'/' .$PluginNotesLabel_File_Export_Name .'_' .$PluginNotesLabel_TimeStamp .'.txt';
				
				if (file_exists($PluginNotesLabel_UploadDirPath)) {
					unlink ($PluginNotesLabel_UploadDirPath);
				}
			}
			
			$PluginNotesLabel_TimeStamp = gmdate("Ymdhis");
			$PluginNotesLabel_UploadDirPath = $PluginNotesLabel_upload_dir_path .'/' .$PluginNotesLabel_File_Export_Name .'_' .$PluginNotesLabel_TimeStamp .'.txt';

			// file_put_contents($PluginNotesLabel_UploadDirPath, $PluginNotesLabel_ExportNotes);
			// WP: File operations should use WP_Filesystem methods instead of direct PHP filesystem
				global $wp_filesystem;
				
				if( ! $wp_filesystem ){
					require_once ABSPATH . 'wp-admin/includes/file.php';
					
					WP_Filesystem();
				} 
				
				$PluginNotesLabel_Result = $wp_filesystem -> put_contents( $PluginNotesLabel_UploadDirPath, $PluginNotesLabel_ExportNotes );			
			
			$PluginNotesLabel_Options['export'] = $PluginNotesLabel_TimeStamp;
			update_option( 'plugin-note-label_options', $PluginNotesLabel_Options ); // phpcs:ignore
		}			
		
		$PluginNotesLabel_Result = true;	
	}
	
	// Delete ALL Notes
	if ($PluginNotesLabel_Mode == 'clear') {		
		PluginNotesLabel_Delete_Labels ();

		$PluginNotesLabel_Result = true;				
	}	
	
	// Get Notes (Страница Обновлений)
	if ($PluginNotesLabel_Mode == 'get_notes') {
		if ($PluginNotesLabel_NoteNames) {
			$PluginNotesLabel_AllPlugins = get_plugins();
		
			$PluginNotesLabel_NoteNames = explode(',', $PluginNotesLabel_NoteNames);
	
			foreach($PluginNotesLabel_NoteNames as $PluginNotesLabel_Name) {
				PluginNotesLabel_Debugger ($PluginNotesLabel_Name, '$PluginNotesLabel_Name', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);			
				
				if ($PluginNotesLabel_Name == 'update disable') {
					// May be this Plugin Can't Update.
					
					$PluginNotesLabel_PluginNote = array();
					
					$PluginNotesLabel_PluginNote['content'] = "May be this Plugin Can't Update";
					$PluginNotesLabel_PluginNote['user'] = '*';
					$PluginNotesLabel_PluginNote['date'] = '';
					$PluginNotesLabel_PluginNote['slug'] = 'update disable';
					
					$PluginNotesLabel_Notes[] = $PluginNotesLabel_PluginNote;
					
				} else {
					// Преобразуем HTML Сущности обратно в Символы
					$PluginNotesLabel_Name = html_entity_decode($PluginNotesLabel_Name);
					PluginNotesLabel_Debugger ($PluginNotesLabel_Name, '$PluginNotesLabel_Name', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);			

					
					foreach ($PluginNotesLabel_AllPlugins as $PluginNotesLabel_Key => $PluginNotesLabel_Value) {
						$PluginNotesLabel_PluginSlug = substr($PluginNotesLabel_Key, 0, stripos($PluginNotesLabel_Key, "/"));	
						PluginNotesLabel_Debugger ($PluginNotesLabel_PluginSlug, '$PluginNotesLabel_PluginSlug', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);			
						
						if ($PluginNotesLabel_Name == $PluginNotesLabel_PluginSlug) {
							$PluginNotesLabel_PluginNote = get_option("plugin-note-label_$PluginNotesLabel_PluginSlug", '');
							
							if (is_array($PluginNotesLabel_PluginNote)) {
								// Нормальный набор данных.
							} else {
								// нет данных или самая ранняя версия.
								$PluginNotesLabel_PluginNoteContent = '';
								if ($PluginNotesLabel_PluginNote) {
									$PluginNotesLabel_PluginNoteContent = $PluginNotesLabel_PluginNote;
								} 
								
								$PluginNotesLabel_PluginNote = array();
								
								$PluginNotesLabel_PluginNote['content'] = $PluginNotesLabel_PluginNoteContent;
								$PluginNotesLabel_PluginNote['user'] = 'UFO';
								$PluginNotesLabel_PluginNote['date'] = '';
							}

							$PluginNotesLabel_PluginNote['slug'] = $PluginNotesLabel_PluginSlug;
						
							$PluginNotesLabel_Notes[] = $PluginNotesLabel_PluginNote;	
						}
					}		
				}
			}
		}
		
		$PluginNotesLabel_Result = true;		
	}
	
	$PluginNotesLabel_ObjRequest = new stdClass();
	$PluginNotesLabel_ObjRequest->status 	= 'OK';
	$PluginNotesLabel_ObjRequest->answer 	= $PluginNotesLabel_Result;
	$PluginNotesLabel_ObjRequest->timestamp = $PluginNotesLabel_TimeStamp;
	$PluginNotesLabel_ObjRequest->notes 	= $PluginNotesLabel_Notes;
	
	PluginNotesLabel_Debugger ($PluginNotesLabel_ObjRequest, '$PluginNotesLabel_ObjRequest', $PluginNotesLabel_DebugProcess, __FUNCTION__, __LINE__);	

	wp_send_json($PluginNotesLabel_ObjRequest);    

	die; // Complete.