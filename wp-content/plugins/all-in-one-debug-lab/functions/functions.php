<?php
/**
* All-in-One Debug Lab
* @version 1.0.0
*
**/

/**
* Show a admin notice.
* @since 1.0.0
*
* @message string 
* @messageKind string of [error, success]
*
*/
function aiodl_admin_notice_message_36bQ3n($message, $messageKind) {
	switch ( $messageKind ) {
		case 'error':
			$class = 'notice notice-error';
		break;
		case 'success':
			$class = 'notice notice-success';
		break;
	}
	printf( '<div id="setting-error-settings_updated" class="%1$s settings-error is-dismissible"><p><strong>%2$s</strong></p><button type="%3$s" class="%4$s"><span class="%5$s">Dismiss this notice.</span></button></div>', esc_attr( $class ), esc_html( $message ), esc_attr( 'button' ), esc_attr( 'notice-dismiss' ), esc_attr( 'screen-reader-text' ) ); 	
}

/**
* Unlink the "debug.log" file.
* @since 1.0.0
*
*/
function aiodl_clearLogFile_36bQ3n() { 
    $filePath = WP_CONTENT_DIR . '/' . AIODL_SYS_LOG_FILE_36bQ3n;
    if (file_exists($filePath)) {
        $status = unlink($filePath);
        if ($status) {
			aiodl_admin_notice_message_36bQ3n( 'The file is clear', 'success'); 
        }
    } else {
		aiodl_admin_notice_message_36bQ3n( 'Something went wrong. The file was not found.', 'error' );
	}
}

/**
* Download a file ("debug.log").
* @since 1.0.0
*
* @pathAndName string path and name file from content. 
* 
*/
function aiodl_local_downloadLogFile_36bQ3n($pathAndName) { 
    $content = file_get_contents($pathAndName);
    header('Content-type: application/octet-stream', true);
    header('Content-Disposition: attachment; filename="' . AIODL_SYS_LOG_FILE_36bQ3n . '"', true);
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $content;
    exit();
}

/**
* Assignment function for download, as specified in add action.
* @since 1.0.0
*
* @pathAndName string path and name file from content. 
* 
*/
function aiodl_downloadLogFile_36bQ3n() { 
    if(is_super_admin()){
        aiodl_local_downloadLogFile_36bQ3n( WP_CONTENT_DIR . '/' . AIODL_SYS_LOG_FILE_36bQ3n);
    }
}

/**
* Get Define Value from a specific file.
* @since 1.0.0
*
* @defineStr string of Define Name-Value.
* @path_and_fileName string of path and name file.
*
* @return int [1,0] 0=false, 1=true
*
*/
function aiodl_getDefineValue_36bQ3n($defineStr, $path_and_fileName){  
	$defineValue = -1;  
	$contents = file_get_contents($path_and_fileName); 
	if ($contents === false) { 
		aiodl_admin_notice_message_36bQ3n( "Something went wrong. The file was not found", 'error');
	} else {
		$contents_array = preg_split("/\\r\\n|\\r|\\n/", $contents);
		foreach ($contents_array as &$record) {    
			if (strpos($record, $defineStr) !== false) { 
				if ( strpos($record, 'true') !== false ) {
					$defineValue = 1;
				} else {
					$defineValue = 0; 
				}
			}
		}
		if ($defineValue == -1) { 
			$contents = str_replace('?>', "define('". $defineStr.", false);" . "\r\n" . '?>', $contents);
			file_put_contents($path_and_fileName, $contents); 
		}
		return $defineValue;
	} 
}
	
?>