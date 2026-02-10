<?php
/*
 * WPGear. Plugin Notes Label
 * options.php
 */
 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	$PluginNotesLabel_NonceKey = 'Update_Options Plugin Notes Label';
	$PluginNotesLabel_Nonce = wp_create_nonce ($PluginNotesLabel_NonceKey);
	
	$PluginNotesLabel_AJAX_NonceKey = 'AJAX_Processing Plugin Notes Label';
	$PluginNotesLabel_AJAX_Nonce = wp_create_nonce ($PluginNotesLabel_AJAX_Nonce);
	
	$PluginNotesLabel_Action 		= isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null;
	$PluginNotesLabel_NonceRequest 	= isset($_REQUEST['_wpnonce']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : 'none';	
	$PluginNotesLabel_NonceRequest2 = isset($_REQUEST['_wpnonce2']) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce2'] ) ) : 'none';	
	
	$PluginNotesLabel_Debug_Process = 'admin_options';	
	
	if ($PluginNotesLabel_Action == 'update') {
		if (!wp_verify_nonce($PluginNotesLabel_NonceRequest, $PluginNotesLabel_NonceKey)) {
			?>
				<div class="wrap">
					<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
					<hr>
					<div class="wdpq_options_box">						
						<?php echo esc_html( __('Warning! Data Incorrect. Update Disable.', 'plugin-notes-label') ); ?>
					</div>
				</div>
			<?php
			
			exit;
		}
	
		// Save Options.
		
		$PluginNotesLabel_Setup_AdminOnly 	= isset( $_REQUEST['pluginnotelabel_option_adminonly'] ) ? 1 : 0;
		$PluginNotesLabel_Setup_ShowAuthor 	= isset( $_REQUEST['pluginnotelabel_option_show_author'] ) ? 1 : 0;
		$PluginNotesLabel_Setup_ShowDate 	= isset( $_REQUEST['pluginnotelabel_option_show_date'] ) ? 1 : 0;
		$PluginNotesLabel_Setup_Clearing 	= isset( $_REQUEST['pluginnotelabel_option_clearing'] ) ? 1 : 0;	
		
		$PluginNotesLabel_Options = PluginNotesLabel_Get_Options();

		$PluginNotesLabel_TimeStamp = isset( $PluginNotesLabel_Options['export'] ) ? $PluginNotesLabel_Options['export'] : '';
		
		$PluginNotesLabel_Options = array(
			'adminonly' => $PluginNotesLabel_Setup_AdminOnly,
			'show_author' => $PluginNotesLabel_Setup_ShowAuthor,
			'show_date' => $PluginNotesLabel_Setup_ShowDate,
			'clearing' => $PluginNotesLabel_Setup_Clearing,
			'export' => $PluginNotesLabel_TimeStamp,
		);
		
		update_option( 'plugin-note-label_options', $PluginNotesLabel_Options ); // phpcs:ignore
	} 
	
	if ($PluginNotesLabel_Action == 'upload') {		
		if (!wp_verify_nonce($PluginNotesLabel_NonceRequest2, $PluginNotesLabel_NonceKey)) {
			?>
				<div class="wrap">
					<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
					<hr>
					<div class="wdpq_options_box">						
						<?php echo esc_html( __('Warning! Data Incorrect. Update Disable.', 'plugin-notes-label') ); ?>
					</div>
				</div>
			<?php
			
			exit;
		}
		// Import Notes.
		$PluginNotesLabel_Import_File_MaxSize = 102400; // Максимальный допустимый для загрузки, размер файла. 100K
		
		$PluginNotesLabel_DateCreated = current_time("Y-m-d H:m");
		
		$File = isset($_FILES['pluginnotelabel_upload_file']) ? $_FILES['pluginnotelabel_upload_file'] : null; // phpcs:ignore		
		
		if ($File) {
			// $File_Name 	= $File['name'];
			$PluginNotesLabel_FileSize 	= $File['size'];
			$PluginNotesLabel_FileType 	= $File['type'];
			$PluginNotesLabel_FileError = $File['error'];
			$PluginNotesLabel_FileTmp 	= $File['tmp_name'];
			
			PluginNotesLabel_Debugger ($PluginNotesLabel_FileTmp, '$PluginNotesLabel_FileTmp', $PluginNotesLabel_Debug_Process, __FUNCTION__, __LINE__);			
			
			$PluginNotesLabel_NotesCount = 0;

			if ($PluginNotesLabel_FileSize > 0 && $PluginNotesLabel_FileSize <= $PluginNotesLabel_Import_File_MaxSize && $PluginNotesLabel_FileType == 'text/plain') {
				switch ($PluginNotesLabel_FileError) {
					case 0:					
						// OK						
						if (file_exists($PluginNotesLabel_FileTmp)) {		
							// $PluginNotesLabel_FileContent = wp_remote_get($PluginNotesLabel_FileTmp); // phpcs:ignore	
							$PluginNotesLabel_FileContent = file_get_contents($PluginNotesLabel_FileTmp);							
							PluginNotesLabel_Debugger ($PluginNotesLabel_FileContent, '$PluginNotesLabel_FileContent', $PluginNotesLabel_Debug_Process, __FUNCTION__, __LINE__);

							$PluginNotesLabel_FileContentArray = explode(PHP_EOL, $PluginNotesLabel_FileContent);							
							
							foreach ($PluginNotesLabel_FileContentArray as $PluginNotesLabel_Line) {
								if ($PluginNotesLabel_Line) {
									$PluginNotesLabel_Note = json_decode($PluginNotesLabel_Line, true);
									
									$PluginNotesLabel_NoteSlug = sanitize_text_field( wp_unslash( $PluginNotesLabel_Note['slug'] ) );
									$PluginNotesLabel_NoteContent = sanitize_text_field( wp_unslash( $PluginNotesLabel_Note['content'] ));
									$PluginNotesLabel_NoteUser = sanitize_text_field( wp_unslash( $PluginNotesLabel_Note['user'] ) );
									$PluginNotesLabel_NoteDate = sanitize_text_field( wp_unslash( $PluginNotesLabel_Note['date'] ) );
									
									unset($PluginNotesLabel_Note['slug']);
									
									if ($PluginNotesLabel_NoteSlug && $PluginNotesLabel_NoteContent) {
										// Добавляем 'НЕ пустые' Note.
										update_option("plugin-note-label_$PluginNotesLabel_NoteSlug", $PluginNotesLabel_Note); // phpcs:ignore

										$PluginNotesLabel_NotesCount = $PluginNotesLabel_NotesCount + 1;
									}
								}	
							}
						}					

						break;
					case 3:	
						// ERROR_UPLOADING
						break;
					default:
						// SYSTEM_ERROR_UPLOADING
				}
			} else {
				// Errors
			}
			
			unlink ($PluginNotesLabel_FileTmp);
							
			?>
			<script>
				// Post Processing. Messaging.
				window.addEventListener ('load', function() {
					var File_Error = <?php echo esc_html( $PluginNotesLabel_FileError ); ?>;
					var File_Size = <?php echo esc_html( $PluginNotesLabel_FileSize ); ?>;
					var File_Size_Max = <?php echo esc_html( $PluginNotesLabel_Import_File_MaxSize ); ?>;
					var File_Type = '<?php echo esc_html( $PluginNotesLabel_FileType ); ?>'
					var Notes_Count = <?php echo esc_html( $PluginNotesLabel_NotesCount ); ?>;				
					
					if (File_Error == 0) {
						// May be - Success.
						if (File_Size > 0) {
							if (File_Size <= File_Size_Max) {
								if (File_Type == 'text/plain') {
									if (Notes_Count > 0) {
										// All OK
										document.getElementById("pluginnotelabel_message_box").innerHTML = Notes_Count + ' Notes successfully imported.';
										document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_success");
									} else {
										// 0 Notes.
										document.getElementById("pluginnotelabel_message_box").innerHTML = 'File content No Notes...';
										document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_warning");											
									}
								} else {
									// File_Type incorrect
									document.getElementById("pluginnotelabel_message_box").innerHTML = 'Error Uploading. File Type - incorrect';
									document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_error");									
								}
							} else {
								// File_Size too Large
								document.getElementById("pluginnotelabel_message_box").innerHTML = 'Error Uploading. File Size too Large! Size = ' + File_Size;
								document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_error");								
							}
						} else {
							// File_Size = 0
							document.getElementById("pluginnotelabel_message_box").innerHTML = 'Error Uploading. File Size = 0';
							document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_error");								
						}
					} else {
						// Error
						document.getElementById("pluginnotelabel_message_box").innerHTML = 'Error Uploading! Cod = ' + File_Error;
						document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_error");						
					}
					document.getElementById("pluginnotelabel_message_box").style.display = 'block';
				});		
			</script>									
			<?php			
		}			
	} 
	
	global $PluginNotesLabel_upload_url_path, $PluginNotesLabel_File_Export_Name;
	
	$PluginNotesLabel_Options = PluginNotesLabel_Get_Options();

	$PluginNotesLabel_Setup_AdminOnly 	= isset( $PluginNotesLabel_Options['adminonly'] ) ? $PluginNotesLabel_Options['adminonly'] : 1;
	$PluginNotesLabel_Setup_ShowAuthor 	= isset( $PluginNotesLabel_Options['show_author'] ) ? $PluginNotesLabel_Options['show_author'] : 1;
	$PluginNotesLabel_Setup_ShowDate 	= isset( $PluginNotesLabel_Options['show_date'] ) ? $PluginNotesLabel_Options['show_date'] : 1;
	$PluginNotesLabel_Setup_Clearing 	= isset( $PluginNotesLabel_Options['clearing'] ) ? $PluginNotesLabel_Options['clearing'] : 0;
	
	if ($PluginNotesLabel_Setup_AdminOnly) {
		if (!current_user_can( 'edit_dashboard' )) {
			?>
			<div class="pluginnotelabel_warning" style="margin: 40px;">
				<?php echo esc_html( __('Sorry, you are not allowed to view this page.', 'plugin-notes-label') ); ?>
			</div>
			<?php
			
			return;
		}		
	}

	$PluginNotesLabel_CountLabels = PluginNotesLabel_Get_CountLabels ();	
	
	?>
	<div class="wrap">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		
		<div id="pluginnotelabel_message_box" class="pluginnotelabel_message_box"></div>
		
		<hr>
		
		<div class="pluginnotelabel_options_box">			
			<form name="form_PluginNotesLabel_Options" method="post" style="margin-top: 20px;">
				<h3><?php echo esc_html( __('General', 'plugin-notes-label') ); ?></h3>
				<div style="margin-left: 20px; margin-bottom: 10px;">
					<div style="margin-top: 10px;">
						<label for="pluginnotelabel_option_adminonly" title="On/Off">
							<?php echo esc_html( __('Enable this Page for Admin only', 'plugin-notes-label') ); ?>
						</label>
						<input id="pluginnotelabel_option_adminonly" name="pluginnotelabel_option_adminonly" type="checkbox" <?php if($PluginNotesLabel_Setup_AdminOnly) {echo 'checked';} ?>>
					</div>	

					<div style="margin-top: 10px; margin-left: 79px;">
						<label for="pluginnotelabel_option_show_author" title="On/Off">							
							<?php echo esc_html( __('Show note Author.', 'plugin-notes-label') ); ?>
						</label>
						<input id="pluginnotelabel_option_show_author" name="pluginnotelabel_option_show_author" type="checkbox" <?php if($PluginNotesLabel_Setup_ShowAuthor) {echo 'checked';} ?>>
					</div>		

					<div style="margin-top: 10px; margin-left: 91px;">
						<label for="pluginnotelabel_option_show_date" title="On/Off">
							<?php echo esc_html( __('Show note Date.', 'plugin-notes-label') ); ?>
						</label>
						<input id="pluginnotelabel_option_show_date" name="pluginnotelabel_option_show_date" type="checkbox" <?php if($PluginNotesLabel_Setup_ShowDate) {echo 'checked';} ?>>
					</div>				
				</div>
				
				<hr>
				
				<h3><?php echo esc_html( __('Clearing', 'plugin-notes-label') ); ?></h3>
				<div style="margin-left: 20px; margin-bottom: 10px;">				
					<div>
						<?php echo esc_html( __('Total Notes Label:', 'plugin-notes-label') ); ?>
						<span id="pluginnotelabel_count">
							<?php echo esc_html( $PluginNotesLabel_CountLabels ); ?>
						</span>
					</div>
					
					<div style="margin-top: 20px;">
						<label for="pluginnotelabel_option_clearing" title="On/Off">
							<?php echo esc_html( __('Delete MetaData with Uninstall Plugin.', 'plugin-notes-label') ); ?>
						</label>
						<input id="pluginnotelabel_option_clearing" name="pluginnotelabel_option_clearing" type="checkbox" <?php if($PluginNotesLabel_Setup_Clearing) {echo 'checked';} ?>>
					</div>

					<div>
						<div style="margin-top: 10px; margin-bottom: 5px;">
							<input id="pluginnotelabel_btn_clear" type="button" class="button" style="margin-right: 5px;" onclick="Do_Confirm_PluginNotesLabel_clear()" value="<?php echo esc_attr( __('Clear All Notes', 'plugin-notes-label') ); ?>">
						</div>				
					</div>	
					
					<div id="pluginnotelabel_confirm_clear_box" class="pluginnotelabel_confirm_clear_box" style="display: none;">
						<div>
							<div class="pluginnotelabel_confirm_clear_box_title"><?php echo esc_html( __('All Notes will be deleted!', 'plugin-notes-label') ); ?></div>
						</div>
						<input id="pluginnotelabel_btn_clear_confirm" type="button" class="button" style="margin-right: 5px;" onclick="Do_PluginNotesLabel_clear()" value="<?php echo esc_attr( __('Confirm', 'plugin-notes-label') ); ?>">
						<input id="pluginnotelabel_btn_clear_cancel" type="button" class="button" style="margin-right: 5px;" onclick="Do_PluginNotesLabel_cancel()" value="<?php echo esc_attr( __('Cancel', 'plugin-notes-label') ); ?>">
						<span id="pluginnotelabel_indicator_processing_clear" class="pluginnotelabel_indicator_processing_clear" style="display: none;">...processing...</span>
					</div>
				</div>				

				<hr>				
				
				<div style="margin-top: 10px; margin-bottom: 5px; text-align: right;">
					<input id="pluginnotelabel_btn_options_save" type="submit" class="button button-primary" style="margin-right: 5px;" value="<?php echo esc_attr( __('Save', 'plugin-notes-label') ); ?>">
				</div>
				<input id="action" name="action" type="hidden" value="update">	
				<input id="_wpnonce" name="_wpnonce" type="hidden" value="<?php echo esc_attr( $PluginNotesLabel_Nonce ); ?>">				
			</form>
			
			<hr>
			
			<h3><?php echo esc_html( __('Export - Import', 'plugin-notes-label') ); ?></h3>
			<div style="margin-left: 20px;">				
				<div style="float: left;">
					<div style="margin-top: 10px; margin-bottom: 5px;">
						<input id="pluginnotelabel_btn_export" type="button" class="button" style="margin-right: 5px;" onclick="Do_PluginNotesLabel_export()" value="<?php echo esc_attr( __('Export Notes', 'plugin-notes-label') ); ?>">
						<span id="pluginnotelabel_indicator_processing_export" style="display: none;">...processing...</span>		
					</div>			

					<div style="margin-top: 10px; margin-bottom: 5px;">
						<input id="pluginnotelabel_btn_import" type="button" class="button" style="margin-right: 5px;" onclick="Enable_PluginNotesLabel_UploadForm()" value="<?php echo esc_attr( __('Import Notes', 'plugin-notes-label') ); ?>">
						
						<form id="form_PluginNotesLabel_Upload" name="form_PluginNotesLabel_Upload" method="post" enctype="multipart/form-data" style="display: none;">
							<input type="hidden" name="action" value="upload"/>
							<input id="_wpnonce2" name="_wpnonce2" type="hidden" value="<?php echo esc_attr( $PluginNotesLabel_Nonce ); ?>">
							
							<div>
								<input id="pluginnotelabel_upload_file" type="file" onchange="Enable_PluginNotesLabel_UploadBtn()" name="pluginnotelabel_upload_file" value="">
							</div>
							
							<div id="pluginnotelabel_upload_btn" style="display: none;">
								<input type="submit" class="button button-primary" onclick="return Check_PluginNotesLabel_FormSaveFile ();" name="pluginnotelabel_upload_btn" value="<?php echo esc_attr( __('Upload Notes', 'plugin-notes-label') ); ?>">
							</div>
						</form>				
					</div>			
				</div>
			</div>
		</div>			
	</div>
	
	<script>
		// Export all Notes.
		function Do_PluginNotesLabel_export() {
			var Ajax_Nonce = '<?php echo esc_html( $PluginNotesLabel_AJAX_Nonce ); ?>';			
			var File_Upload_Path = '<?php echo $PluginNotesLabel_upload_url_path; // phpcs:ignore	 ?>';
			var File_Name = '<?php echo esc_html( $PluginNotesLabel_File_Export_Name ); ?>';			
			var Download_Name = 'plugin_notes_label_export.txt';
			
			var Download_URL = File_Upload_Path + '/' + File_Name;
			
			Do_PluginNotesLabel_cancel();
			
			document.getElementById("pluginnotelabel_indicator_processing_export").style.display = 'inline-block';
			
			var PluginNote2_Ajax_URL = ajaxurl;
			var PluginNote2_Ajax_Data = 'action=plugin_note_label&mode=export&_wpnonce=' + Ajax_Nonce;		

			// console.log(PluginNote2_Ajax_Data);			
						
			jQuery.ajax({
				type:"POST",
				url: PluginNote2_Ajax_URL,
				dataType: 'json',
				data: PluginNote2_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	
					
					var Status = Obj_Request.status;
					var Answer = Obj_Request.answer;
					var TimeStamp = Obj_Request.timestamp;

					document.getElementById("pluginnotelabel_indicator_processing_export").style.display = 'none';
					
					if (TimeStamp) {
						// Download
						Download_URL = Download_URL + '_' + TimeStamp + '.txt';

						var Download_Link = document.createElement("a");
						
						Download_Link.setAttribute('download', Download_Name);
						Download_Link.href = Download_URL;
						document.body.appendChild(Download_Link);
						
						Download_Link.click();
						Download_Link.remove();
					} else {
						// No Notes for Export
						document.getElementById("pluginnotelabel_message_box").innerHTML = 'No Notes for Export.';
						document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_warning");
						document.getElementById("pluginnotelabel_message_box").style.display = 'block';						
					}
				}
			});							
		}
		
		// Import Notes.
		function Enable_PluginNotesLabel_UploadForm() {
			Do_PluginNotesLabel_cancel();

			document.getElementById("form_PluginNotesLabel_Upload").style.display = 'inline-block';
		}

		function Enable_PluginNotesLabel_UploadBtn() {
			document.getElementById("pluginnotelabel_upload_btn").style.display = 'block';
		}
		
		function Check_PluginNotesLabel_FormSaveFile () {
			var File_Name = document.getElementById('pluginnotelabel_upload_file').files[0].name;
	
			if (File_Name == "") {
				alert('please Select File.');
				
				return false;
			}

			return true;
		}

		// Confirmation Clear Notes.
		function Do_Confirm_PluginNotesLabel_clear() {
			Do_PluginNotesLabel_cancel();
			
			document.getElementById("pluginnotelabel_confirm_clear_box").style.display = 'block';			
			document.getElementById("pluginnotelabel_btn_clear_confirm").style.display = 'inline-block';
			document.getElementById("pluginnotelabel_btn_clear_cancel").style.display = 'inline-block';		
		}
		
		// Clear Notes.
		function Do_PluginNotesLabel_clear() {
			document.getElementById("pluginnotelabel_btn_clear_confirm").style.display = 'none';
			document.getElementById("pluginnotelabel_btn_clear_cancel").style.display = 'none';
			document.getElementById("pluginnotelabel_indicator_processing_clear").style.display = 'block';
			
			var Ajax_Nonce = '<?php echo esc_html( $PluginNotesLabel_AJAX_Nonce ); ?>';		
			var PluginNote2_Ajax_URL = ajaxurl;
			var PluginNote2_Ajax_Data = 'action=plugin_note_label&mode=clear&_wpnonce=' + Ajax_Nonce;
						
			jQuery.ajax({
				type:"POST",
				url: PluginNote2_Ajax_URL,
				dataType: 'json',
				data: PluginNote2_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	
					
					var Status = Obj_Request.status;
					var Answer = Obj_Request.answer;					
					
					if (Answer) {
						document.getElementById("pluginnotelabel_confirm_clear_box").style.display = 'none';
						
						document.getElementById("pluginnotelabel_message_box").innerHTML = 'All Notes successfully Deleted.';
						document.getElementById("pluginnotelabel_message_box").classList.add("pluginnotelabel_message_success");
						document.getElementById("pluginnotelabel_message_box").style.display = 'block';	
						document.getElementById("pluginnotelabel_count").innerHTML = '0';						
					}
				}
			});				
		}
		
		// Cancel Clear Notes.
		function Do_PluginNotesLabel_cancel() {
			document.getElementById("pluginnotelabel_message_box").style.display = 'none';
			document.getElementById("pluginnotelabel_confirm_clear_box").style.display = 'none';
			document.getElementById("form_PluginNotesLabel_Upload").style.display = 'none';			
			document.getElementById("pluginnotelabel_indicator_processing_clear").style.display = 'none';			
		}
	</script>
