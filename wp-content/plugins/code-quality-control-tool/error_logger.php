<?php
/*
 * Logger for Code Quality Control Tool, executes before WordPress started
 * ver. 2.1
*/
function cqctphp_start_phptrace_error_handler($errno, $errstr, $errfile, $errline)
{
    $WP_CONTENT_DIR = dirname(dirname(dirname(__FILE__)));

    $WP_CONTENT_DIR = $WP_CONTENT_DIR . '/code-quality-logs';
    PHPCodeControl_logger::ensurePrivateDir($WP_CONTENT_DIR);

    $settings = parse_ini_file($WP_CONTENT_DIR.'/_php_code_control.ini');

    // Check if active
    if (!isset($settings['is_active']) && $settings['is_active'] == 0) return;

    // Check for IP filtering
    if (isset($settings['filer_by_ip']) && count($settings['filer_by_ip']))
    {
        if (!in_array($_SERVER['REMOTE_ADDR'], $settings['filer_by_ip'])) return;
    }
    
    // Check by File filtering
    if ( isset($settings['object_check']) && !in_array("ALL", $settings['object_check']) )
    {
        $is_matched = false;
        foreach ($settings['object_check'] as $file_pattern)
        {
            if (strpos($errfile, $file_pattern) !== false) 
            {
                $is_matched = true;
                break;
            }
        }
        if (!$is_matched) return;
    }


    $errstr = htmlspecialchars($errstr);
	
	switch($errno) 
	{ 
		case E_ERROR: // 1 // 
			$errno_txt = 'E_ERROR'; break;
		case E_WARNING: // 2 // 
			$errno_txt =  'E_WARNING';	break;
		case E_PARSE: // 4 // 
			$errno_txt =  'E_PARSE'; break;
		case E_NOTICE: // 8 // 
			$errno_txt =  'E_NOTICE'; break;
		case E_CORE_ERROR: // 16 // 
			$errno_txt =  'E_CORE_ERROR'; break;
		case E_CORE_WARNING: // 32 // 
			$errno_txt =  'E_CORE_WARNING'; break;
		case E_COMPILE_ERROR: // 64 // 
			$errno_txt =  'E_COMPILE_ERROR'; break;
		case E_COMPILE_WARNING: // 128 // 
			$errno_txt =  'E_COMPILE_WARNING'; break;
		case E_USER_ERROR: // 256 // 
			$errno_txt =  'E_USER_ERROR'; break;
		case E_USER_WARNING: // 512 // 
			$errno_txt =  'E_USER_WARNING'; break;
		case E_USER_NOTICE: // 1024 // 
			$errno_txt =  'E_USER_NOTICE'; break;
		case E_STRICT: // 2048 // 
			$errno_txt =  'E_STRICT'; break;
		case E_RECOVERABLE_ERROR: // 4096 // 
			$errno_txt =  'E_RECOVERABLE_ERROR'; break;
		case E_DEPRECATED: // 8192 // 
			$errno_txt =  'E_DEPRECATED'; break;
		case E_USER_DEPRECATED: // 16384 // 
			$errno_txt =  'E_USER_DEPRECATED';break;
	}
	
    
    // Check for allowed error types
    $allowed_errortypes = explode(",", $settings['errortypes']);
    if (!in_array($errno_txt, $allowed_errortypes)) return;
    
    
    // Add to log file
    $log_file = $WP_CONTENT_DIR.'/_php_errors.log';
    
    $line = array();
    $line['date'] = date("Y-m-d H:i:s");
    $line['ip'] = 'IP: '.$_SERVER['REMOTE_ADDR'];
    $line['type'] = 'Type: '.$errno_txt;
    $line['msg'] = 'Msg: '.$errstr;
    $line['file'] = 'File: '.$errfile;
    $line['line'] = 'Line: '.$errline;
    $line['url'] = 'URL: '.$_SERVER['REQUEST_URI'];

    
    // Check for dups
    if ($settings['skip_dups'] == 1)
    {
        $search_txt = $line['msg']."| ".$line['file']."| ".$line['line'];
        //$matches = array();
        
        $handle = @fopen($log_file, "r");
        if ($handle)
        {
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                if(strpos($buffer, $search_txt) !== false)
                {
                    // Already exists
                    //$matches[] = $buffer;
                    fclose($handle);
                    return;
                }
            }
            fclose($handle);
        }
    }
    
    
    // Save line to log file
	$fp = fopen($log_file, 'a');
	fwrite($fp, implode("| ", $line)."\n");
	fclose($fp);
    
    
    // Add to conter file
    $log_counter_file = $WP_CONTENT_DIR.'/_php_errors.count.log';
    
	$fp = fopen($log_counter_file, 'a');
	fwrite($fp, "0");
	fclose($fp);
    
    // Check for logsize
    if ($settings['logsize'] > 0)
    {
        $filesize = filesize($log_file);
        if ($filesize > $settings['logsize'] * 1024 * 1024 * 1.1)
        {
            // Cut log file
            $lines = file($log_file);
            if ($lines !== false)
            {
                $total_lines = count($lines);
                $keep_total_lines = round($total_lines * 0.8);
                $lines = array_slice($lines, $keep_total_lines);
                
            	$fp = fopen($log_file, 'w');
            	fwrite($fp, implode("\n", $lines)."\n");
            	fclose($fp);
            }
        }
    }
}

$old_error_handler_5FB609D0735B = set_error_handler("cqctphp_start_phptrace_error_handler");


class PHPCodeControl_logger {

    /**
     * Creates a directory (if not exists) and places a .htaccess file inside
     * to block direct access to its contents.
     *
     * @param string $dir Absolute or relative directory path
     * @param int $mode Permissions for the directory (default 0755)
     * @throws RuntimeException on creation/write errors
     */
    public static function ensurePrivateDir(string $dir, int $mode = 0755): void
    {
        // Normalize the path
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        // If directory does not exist — create it (including intermediate directories)
        if (!is_dir($dir)) {
            // Keep in mind that real permissions are affected by umask: $mode & ~umask()
            if (!mkdir($dir, $mode, true) && !is_dir($dir)) {
                throw new RuntimeException("Failed to create directory: {$dir}");
            }
        }

        // Check if directory is writable
        if (!is_writable($dir)) {
            throw new RuntimeException("Directory exists but is not writable: {$dir}");
        }

        // Path to .htaccess
        $htaccessPath = $dir . DIRECTORY_SEPARATOR . '.htaccess';

        // .htaccess content for Apache 2.4+ (modern syntax)
        // Alternative (for older Apache): "Deny from all"
        $htaccess = <<<HTA
# Deny direct access to this directory
# Apache 2.4+:
Require all denied

# Backwards compatibility:
<IfModule !mod_authz_core.c>
    Deny from all
</IfModule>
HTA;

        // Write the file (overwrite if exists)
        if (file_put_contents($htaccessPath, $htaccess, LOCK_EX) === false) {
            throw new RuntimeException("Failed to write .htaccess to: {$htaccessPath}");
        }

        // (Optional) set secure permissions on .htaccess
        @chmod($htaccessPath, 0644);
    }
}
