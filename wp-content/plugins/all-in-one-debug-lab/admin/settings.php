<?php
/**
* All-in-One Debug Lab
* @version 1.0.0
*
**/

/**
* Set Define Value to a specific file.
* @since 1.0.0
*
* @defineName string of Define Name-Value.
* @path_and_fileName string of path and name file.
*
* @return boolean.
*
*/
function aiodl_setDefineValue_36bQ3n($defineName, $path_and_fileName, $sign) { 
	
	if (!file_exists($path_and_fileName)) {	
		aiodl_admin_notice_message_36bQ3n( "Something went wrong. The file was not found", 'error'); exit;
	}

	$contents = file_get_contents($path_and_fileName);
	$new_contents= "";
	
	if( strpos($contents, $defineName."'") !== false) { 
		$contents_array = preg_split("/\\r\\n|\\r|\\n/", trim($contents));
		foreach ($contents_array as $record) {
			if (strpos($record, $defineName."'") !== false) { 	
				$new_contents .= "define('".$defineName."', ".$sign.");"."\r";  														
			} else {
				$new_contents .= $record."\r";
			}
		}
		$result = file_put_contents($path_and_fileName, $new_contents);
		return true;
		if ( $result === strlen($new_contents)) {
			return true;
		} else {
			return false;
		}
	}
}

function aiodl_call_function_settings_36bQ3n() {
	$path_and_fileName = AIODL_PLUGIN_DIR_36bQ3n.'admin/' . AIODL_SET_EXT_FILE_36bQ3n;
	
	// Array with all available define names
	$defineNames = array("WP_DEBUG", "WP_DEBUG_LOG", "WP_DEBUG_DISPLAY", "SCRIPT_DEBUG", "SAVEQUERIES");

	// When posting, the destination file is updated.
	if(isset($_POST['submit'])){
		if( isset($_POST['check_list']) && is_array($_POST['check_list']) )	{
			foreach($_POST['check_list'] as $defineName) {
				if ( !aiodl_setDefineValue_36bQ3n($defineName, $path_and_fileName, 'true') ) { 
					aiodl_admin_notice_message_36bQ3n( "Something went wrong. The definition value setting cannot be specified", 'error'); exit;
				}
				foreach (array_keys($defineNames, $defineName) as $key) {
					unset($defineNames[$key]); 
				}
			}
		}

		foreach($defineNames as $defineName) { 
			if ( !aiodl_setDefineValue_36bQ3n($defineName, $path_and_fileName, 'false') ) { 
				aiodl_admin_notice_message_36bQ3n( "Something went wrong. The definition value setting cannot be specified", 'error') ; exit;
			}
		}				
		aiodl_admin_notice_message_36bQ3n( "Settings saved successfully.", 'success');		
	}	
	
	// The define names are taken from the file.
	if (file_exists($path_and_fileName)) {	
		$WP_DEBUG_value = aiodl_getDefineValue_36bQ3n("WP_DEBUG'", $path_and_fileName);
		$WP_DEBUG_LOG_value = aiodl_getDefineValue_36bQ3n("WP_DEBUG_LOG'", $path_and_fileName);
		$WP_DEBUG_DISPLAY_value = aiodl_getDefineValue_36bQ3n("WP_DEBUG_DISPLAY'", $path_and_fileName);	
		$SCRIPT_DEBUG_value = aiodl_getDefineValue_36bQ3n("SCRIPT_DEBUG'", $path_and_fileName);
		$SAVEQUERIES_value = aiodl_getDefineValue_36bQ3n("SAVEQUERIES'", $path_and_fileName);		
	} else {
		aiodl_admin_notice_message_36bQ3n( "Something went wrong. The file was not found", 'error'); exit;
	}	

	?>
	<div class="wrap">
		<h2><?php 'Debug Settings';?></h2>
		<form method="post" action="">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">Debug settings</th>
						<td> 
						<fieldset>
							<legend class="screen-reader-text"><span>Debug settings</span></legend>
							<label for="wp_debug">
								<input name="check_list[]" type="checkbox" id="wp_debug" value="WP_DEBUG" <?php checked($WP_DEBUG_value, 1); ?> >
								Enable the “debug” mode throughout WordPress (WP_DEBUG, false by default)
							</label>
							<p class="description">
								(When this setting is enabled, WordPress displays all PHP errors, notices, and warnings.)  
							</p>
							</br>
							<label for="wp_debug_log">
								<input name="check_list[]" type="checkbox" id="wp_debug_log" value="WP_DEBUG_LOG" <?php checked($WP_DEBUG_LOG_value, 1); ?> >
								Save all errors in a log file (WP_DEBUG_LOG, false by default)
							</label>
							<p class="description">
								(Is a companion to WP_DEBUG that causes all errors to also be saved to a debug.log log file This is useful if you want to review all notices later or need to view notices generated off-screen.)  
							</p>
							</br>
							<label for="wp_debug_display">
								<input name="check_list[]" type="checkbox" id="wp_debug_display" value="WP_DEBUG_DISPLAY" <?php checked($WP_DEBUG_DISPLAY_value, 1); ?> >
								Enable display of error messages on your site. (WP_DEBUG_DISPLAY, true by default)
							</label>
							<p class="description">
								(Another companion to WP_DEBUG that controls whether debug messages are shown inside the HTML of pages or not.) </br>
								(If used on a live website this could lead to the disclosure of sensitive information about the website and server setup, so use cautiously.)  
							</p>
							</br>
							<label for="script_debug">
								<input name="check_list[]" type="checkbox" id="script_debug" value="SCRIPT_DEBUG" <?php checked($SCRIPT_DEBUG_value, 1); ?> >
								Enabling this feature, WordPress uses the developed versions of CSS and JavaScript instead of compressed versions. (SCRIPT_DEBUG, false by default)
							</label>
							<p class="description">
								(This is useful when you are testing modifications to any built-in .js or .css files.)  
							</p>	
							</br>
							<label for="savequeries">
								<input name="check_list[]" type="checkbox" id="savequeries" value="SAVEQUERIES" <?php checked($SAVEQUERIES_value, 1); ?> >
								Enable a database query log (SAVEQUERIES, false by default)
							</label>
							<p class="description">
								(This option comes in handy if you are experiencing WordPress database issues.)  
							</p>	
							<p class="description">
							(NOTE: This will have a performance impact on your site, so make sure to turn this off when you aren't debugging.)
							</p>
							</br>	
							<p class="description">
							For more information <a href="https://wordpress.org/support/article/debugging-in-wordpress/" target="_blank">wordpress.org</a>
							</p>							
						</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
		</form>
	</div>
	<?php	
}
?>