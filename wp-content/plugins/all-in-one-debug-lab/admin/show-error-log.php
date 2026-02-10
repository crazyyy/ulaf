<?php
/**
* All-in-One Debug Lab
* @version 1.0.0
*
**/

function aiodl_call_function_error_log_36bQ3n() {	
	$dirAndFile = AIODL_PLUGIN_DIR_36bQ3n . 'admin/' . AIODL_SET_EXT_FILE_36bQ3n;
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32">
			<br>
		</div>
		<h2><?php echo 'Show Debug Log';?></h2>
	</div>
	
	<div class="wrap">
		<h2><?php echo 'Debug Log'; ?></h2> 
		<?php
		if (isset($_POST['aiodl-clearLogFile-36bQ3n'])) {
			$responce = aiodl_clearLogFile_36bQ3n();
		} 
	
		$filePath = WP_CONTENT_DIR . '/' . AIODL_SYS_LOG_FILE_36bQ3n;
		if (file_exists($filePath)) {
			$content = file_get_contents($filePath); 
			$content = esc_textarea( htmlentities($content) );
		} else{
			$content = "";
		}
	
		if($content !== false){ 
			?>
			<form method="post" action="">
				<p class="form-header-button">
					<input type="submit" name="aiodl-clearLogFile-36bQ3n" id="aiodl-clearLogFile-36bQ3n" class="button button-primary" value="<?php echo 'Clear Log'; ?>">
					<input type="submit" name="aiodl-downloadLogFile-36bQ3n" id="aiodl-downloadLogFile-36bQ3n" class="button button-primary" value="<?php echo 'Download Log'; ?>">
				</p>
			</form>
			<textarea id="aiodl-debug-log" readonly cols="300" rows="30" name="newcontent"  aria-describedby="editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"><?php echo $content; ?></textarea>
			<?php

		} else {
			aiodl_admin_notice_message_36bQ3n( "Something went wrong. The file was not found", 'error');
		}
		
		if ( aiodl_getDefineValue_36bQ3n("SAVEQUERIES'", $dirAndFile) == 1 ) { 
			global $wpdb;
			?>
			<h2><?php echo 'SAVEQUERIES Log'; ?></h2> 
				<p class="form-header-button">
				<label for="comment_order">
					<?php print_r($wpdb->num_queries); ?> (Queries)
				</label>
				</p>
				<textarea id="savequeries-log" readonly cols="300" rows="30" name="newcontent"  aria-describedby="editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"><?php print_r($wpdb->queries); ?></textarea>
			<?php	
		} 
		else { 
			
		}		
		?>
	</div>
<?php
}
