<?php

require_once ( MWCODE_PATH . '/vendor/autoload.php' );
use PhpParser\ParserFactory;
use PhpParser\NodeDumper;
use PhpParser\Error;

class Meow_MWCODE_Core
{
	public $admin = null;
	public $snippet = null;
	public $is_rest = false;
	public $is_cli = false;
	public $site_url = null;
	private $option_name = 'mwcode_options';

	public function __construct() {
		$this->site_url = get_site_url();
		$this->is_rest = MeowCommon_Helpers::is_rest();
		$this->is_cli = defined( 'WP_CLI' ) && WP_CLI;

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {
		// Part of the core, settings and stuff
		$this->admin = new Meow_MWCODE_Admin( $this );

		// Snippets
		$snippet = new Meow_MWCODE_Modules_Snippet( $this );
		$this->snippet = $snippet;

		// Only for REST
		if ( $this->is_rest ) {
			new Meow_MWCODE_Rest( $this, $this->admin, $snippet );
		}

		// API
		global $mwcode;
		$mwcode = new Meow_MWCODE_API( $this, $snippet );
	}


	/**
	 *
	 * Roles & Access Rights
	 *
	 */
	#region Roles & Access Rights
	public function can_access_settings() {
		return apply_filters( 'mwcode_allow_setup', current_user_can( 'manage_options' ) );
	}

	public function can_access_features() {
		return apply_filters( 'mwcode_allow_usage', current_user_can( 'administrator' ) );
	}

	public function check_rest_nonce( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return wp_verify_nonce( $nonce, 'wp_rest' );
	}
	#endregion

	#region Options

	function get_option( $option, $default = null ) {
		$options = $this->get_all_options();
		return $options[$option] ?? $default;
	}

	function list_options() {
		return [
			//Safemode
			"safe_mode_status" => "on", // on, off, whitelist
			"safe_mode_whitelist" => [],
			
			//LOGS
			"server_debug_mode" => false,

			//UI
			"ui_show_preview" => true,

			//AI
			"ai_suggestions" => false,
			"ai_engine_status"=> false,
			"ai_engine_message" => "",

			//API
			"api_endpoint" => false,
			"api_token" => md5( time() . rand() ),
		];
	}

	function get_all_options( ) {
		$options = get_option( $this->option_name, $this->list_options( ) );
		$options = $this->sanitize_options( $options );
		
		return $options;
	}

	function update_options( $options ) {
		$current_options = get_option($this->option_name);
		
		if ($current_options === $options) {
			// $this->log('💾  The options are already the expected value.');
		} else {
			if ( !update_option( $this->option_name, $options, false ) ) {
				$this->log( '💾  There was an issue updating the options.' );
			}
		}
		
		$options = $this->sanitize_options( $options );
		return $options;
	}

	function update_option( $option, $value ) {
		$options = $this->get_all_options();
		$options[$option] = $value;
		return $this->update_options( $options );
	}

	function reset_options() {
		if ( $this->get_all_options() === $this->list_options() ) {
			return true;
		}
		return $this->update_options( $this->list_options() );
	}

	// Validate and keep the options clean and logical.
	function sanitize_options( $options ) {
		$options_modified  = false;

		// Make sure safe mode whitelist is an array
		if ( ! is_array( $options['safe_mode_whitelist'] ) ) {
			$options['safe_mode_whitelist'] = explode( ",", $options['safe_mode_whitelist'] );
			$options_modified                = true;
		}

		// Update AI Engine status
		$options_modified = $this->updateAIEngineStatus( $options ) || $options_modified;

		// Disable AI related features if AI Engine is not available
		if ( ! $options['ai_engine_status'] && $options['ai_suggestions'] !== false ) {
			$options['ai_suggestions'] = false;
			$options_modified          = true;
		}

		if ( $options_modified ) {
			update_option( $this->option_name, $options, false );
		}

		return $options;
	}

	private function updateAIEngineStatus( &$options ) {
		global $mwai;

		if ( is_null( $mwai ) || ! isset( $mwai ) ) {
			$options['ai_engine_status']  = false;
			$options['ai_engine_message'] = 'AI Engine is not available.';
			return true;
		}

		try {
			$status = $mwai->checkStatus();

			if ( $options['ai_engine_status'] != true || $options['ai_engine_message'] != $status ) {
				$options['ai_engine_status']  = true;
				$options['ai_engine_message'] = $status;
				return true;
			}
		} catch ( Exception $e ) {
			if ( $options['ai_engine_status'] != false || $options['ai_engine_message'] != $e->getMessage() ) {
				$options['ai_engine_status']  = false;
				$options['ai_engine_message'] = $e->getMessage();
				return true;
			}
		}

		return false;
	}

	// #endregion

	#region Snippets

	/**
     * Get snippet.
     *
     * @param $id
     * @return mixed
     */
    protected function get_snippet( $id ) {
		if ( $this->snippet === null ) {
			$this->snippet = new Meow_MWCODE_Modules_Snippet( $this );
		}

        return $this->snippet->select_one( $id );
    }

	private function sanitize_arg( $name, $value ) {
		if ( $name[0] !== '$' ) { $name = '$' . $name; }
		
		if ( !empty( $value ) && !is_numeric( $value ) && $value[0] !== '"' && $value[strlen( $value ) - 1] !== '"' ) {
			$value = '"' . esc_sql( $value ) . '"';
		}

		return [ $name, $value ];
	}

	function run_non_fn_snippet( $id ) {
		$snippet = $this->get_snippet( $id );
		$snippet['code'] = preg_replace( '/<\?php/', '', $snippet['code'], 1 );

		$error = null;
		$output = null;

		try {
			ob_start();
			eval( $snippet['code'] );
			$output = ob_get_clean();
		} catch ( Throwable $e ) {
			$error = new Exception( ' Error executing the snippet, ' . $e->getMessage() );
			ob_clean();
		} finally {
			restore_error_handler();
		}

		if ( $error !== null ) {
			throw $error;
		}

		return $output;
	}

	function run_snippet( $id, $args = [], $params = [] )
	{
		// Static array to track defined functions
		static $defined_functions = array();

		if ( $id ) { // If there is an ID, we get the snippet, if not we get the data from the params
			$snippet = $this->get_snippet( $id );
			$this->snippet->get_function_snippets_data( $snippet ); // adds the function data to the snippet

			$params = [ // We set the params according to the snippet we fetched
				'test' =>   false, // If we pass an ID to the function, we are not testing the snippet
				// 'test' =>   $params['test'] ?? false if needed we can still use ID and test at the same time (should not happen)
				'code' =>   $snippet['code'],
				'name' =>   $snippet['functionName'],
				'args' =>   $snippet['functionArgs'],
				'values' => $snippet['functionArgsDict'] // Contains the default values of the arguments
			];
		}

		// Sanitize all the arguments if the option is enabled
		if ( $this->get_option( 'sanitize_arguments', true ) ) {

			if ( $args ) {
				foreach ( $args as $name => $value ) {
					list( $sanitizedName, $sanitizedValue ) = $this->sanitize_arg( $name, $value );
					unset( $args[$name] );

                	$args[$sanitizedName] = $sanitizedValue;
				}
			}

			foreach ( $params['values'] as $name => $value ) {

				if( array_key_exists( 'input', $value) ) {
					list( $sanitizedInputName, $sanitizedInputValue ) = $this->sanitize_arg( $name, $value['input'] );
					$params['values'][$sanitizedInputName]['input'] = $sanitizedInputValue;
				}
				
				if( array_key_exists( 'default', $value) ) {
					list( $sanitizedDefaultValueName, $sanitizedDefaultValue ) = $this->sanitize_arg( $name, $value['default'] );
					$params['values'][$sanitizedDefaultValueName]['default'] = $sanitizedDefaultValue;
				}
			}

		}

		

		// Make sure the function is existing and is the one in the snippet
		if ( empty( $params['code'] ) ) {
			throw new Exception( 'Code Engine: The snippet code appears to be empty.' );
		}
		
		if ( empty( $params['name'] ) || ! str_contains( $params['code'], $params['name'] ) ) {
			throw new Exception( "Code Engine: Function name does not match. The name should be {$params['name']}." );
		}

		// Overwrite the default values with the provided ones
		if ( $args ) {
			foreach ( $args as $name => $value ) {
				$params['values'][$name]['input'] = $value;
			}

			$this->log( '⚡  Arguments provided: ' . json_encode( $args ) );
		}

		// Check if the function has already been defined
		if ( !in_array( $params['name'], $defined_functions ) ) {

			// If not, proceed with modification and definition
			if ( $params['test'] ) { // Make sure the echo statement uses a line break
				$params['code'] = preg_replace( '/echo\s+(.+?);/s', 'echo $1 . "\n";', $params['code'] );
			} else { // Remove all echo statements
				$params['code'] = preg_replace( '/echo\s+(.+?);/s', '', $params['code'] );
			}

			$params['code'] = "if (!function_exists('{$params['name']}')) {\n" . $params['code'] . "\n}\n";

			// Add the function name to the array to avoid redefinition
			$defined_functions[] = $params['name'];
		} else {
			// If already defined, just prepare to call the function without redefining it
			$params['code'] = '';
		}

		// Prepare the code to be executed
		$params['code'] .= "\n\$mwcode_result = {$params['name']}(";
		foreach ( $params['args'] as $index => $arg ) {
			$value = 'null'; // In case the argument is not provided it will be null

			if ( array_key_exists( $arg, $params['values'] ) ) { // Avoid warnings if the argument is not provided

				// If the argument is provided, use it, if not use the default value
				if ( !empty( $params['values'][$arg]['input'] ) ) {
					$value = $params['values'][$arg]['input'];

				} else if ( !empty( $params['values'][$arg]['default'] ) ) {
					$value = $params['values'][$arg]['default'];
				}
			}

			$params['code'] .= "{$value}";
			if ( $index < count( $params['args'] ) - 1 ) {
				$params['code'] .= ', ';
			}
		}
		$params['code'] .= ");\necho print_r(\$mwcode_result, true);";

		$error = null;
		$output = null;

		try {
			ob_start();
			eval( $params['code'] );
			$output = ob_get_clean();
			
			if ( $params['test'] ){
				$output = explode( "\n", $output );
			}
			
		} catch ( Throwable $e ) {
			//$this->log('Code Engine: Error executing the function: ' . $e->getMessage());
			$error = new Exception(' Error executing the function, ' . $e->getMessage());

			ob_clean();
		} finally {
			restore_error_handler();
		}

		if ( $error !== null ) {
			if( $params['test'] ){
				$output['error'] = $error->getMessage();
			} else {
				throw $error;
			}
		}

		return $output;
	}


	function parse_snippet( $code, $new_snippet = false ){
		$parser = ( new ParserFactory( ) )->createForNewestSupportedVersion( );

		if( !$this->snippet ){
			$this->snippet = new Meow_MWCODE_Modules_Snippet( $this  );
		}

		// First we check the function names are unique
		$fn = $this->snippet->sanitize_and_check_functions( $code, $new_snippet );
		if ( ! $fn['is_valid'] ) {

			$lint = [
				'line' => 1,
				'attributes' => $fn['attributes'][0],
				'raw_message' => implode(', ', $fn['errors'][0]),
				'message' => implode(', ', $fn['errors'][0]),
			];

			return $lint;
		}

		try {
			$stmts = $parser->parse( $code );
			$result = $stmts;
		} catch ( PhpParser\Error $e ) {

			$lint = [
				'line' => $e->getStartLine(),
				'attributes' => $e->getAttributes(),
				'raw_message' => $e->getRawMessage(),
				'message' => $e->getMessage(),
			];

			return $lint;
		}

		return null;
	}

	public function get_js_functions_to_push() {
		$functions = $this->snippet->get_functions();
		$js_functions = [];
		foreach ( $functions as &$function ) {
			if ( !isset( $function['target'] ) ) {
				$function['target'] = 'php';
			}
			if ( $function['target'] == 'js' ) {
				$js_functions[] = $function;
			}
		}
		$snippets = [];
		foreach ( $js_functions as $function ) {
			$snippet = $this->snippet->select_one( $function['snippetId'] );
			$snippet['function_info'] = $function; // Add function info to snippet
			$snippets[] = $snippet;
		}
	   
		return $this->generate_js_functions_code( $snippets );
	}
	
	function generate_js_functions_code ($snippets ) {
		$code = "";
		foreach ( $snippets as $snippet ) {
			$function_code = $snippet['code'];
			$function_info = $snippet['function_info'];
			
			// Extract function name and arguments
			preg_match( '/(?:const|let|var)?\s*(\w+)\s*=\s*\((.*?)\)\s*=>/', $function_code, $matches );
			$function_name = $matches[1] ?? $function_info['name'];
			$function_args = $matches[2] ?? '';
	
			// Prepare default values
			$default_args = [];
			foreach ( $function_info['args'] as $arg ) {
				if ( isset( $arg['default'] ) && $arg['default'] !== '' ) {
					$default_args[$arg['name']] = $arg['default'];
				}
			}
	
			// Modify function to use default values
			if ( !empty( $default_args ) ) {
				$new_args = explode( ',', $function_args );
				foreach ( $new_args as &$arg ) {
					$arg = trim( $arg );
					if ( isset( $default_args[$arg] ) ) {
						$arg .= " = " . json_encode( $default_args[$arg] );
					}
				}
				$new_args_string = implode( ', ', $new_args );
				$function_code = preg_replace(
					'/(\w+)\s*=\s*\((.*?)\)\s*=>/',
					"$1 = ($new_args_string) =>",
					$function_code
				);
			}
	
			$code .= $function_code . "\n\n";
		}

		return $code;
	}


	/**
     * [STATIC] Execute active snippets.
     *
     * @return array
     */
    public function execute_active_snippets() {

		$blocked = false;
		$page = isset( $_GET["page"] ) ? sanitize_text_field( $_GET["page"] ) : null;
		if ( $page === 'mwcode_settings' || !Meow_MWCODE_Core::is_white_listed_rest() ) {
			$blocked = true;
		}

		if ( empty( $this->snippet ) ) {
			$this->snippet = new Meow_MWCODE_Modules_Snippet( $this );
		}

		$ts = $this->get_option( 'thrown_snippet', null );
		if ( !empty( $ts ) ) {
			$this->log( "⚠️  Your snippet \"{$ts['name']}\" has thrown a fatal error last time, so we disabled it. Please check the logs for more information." );
			$this->snippet->force_disable( $ts['id'] );
			$this->update_option( 'thrown_snippet', null );
		}

        $scope = is_admin() ? [ 'backend', 'persistent' ] : [ 'frontend', 'persistent' ];
        // Get all active snippets

		

        $snippets = $this->snippet->select(
            null, // offset
            -1, // limit
            [
                [ 'accessor' => 'active', 'value' => 1 ],
                [ 'accessor' => 'scope', 'value' => $scope ],
            ], // filter
            [ 'accessor' => 'priority', 'by' => 'DESC' ] // sort
        )['data'];


        if ( empty( $snippets ) ) {
            return;
        }

		$snippets = array_map( function ( $snippet ) use ( $blocked ) {
			$snippet['code'] = preg_replace( '/<\?php/', '', $snippet['code'], 1 );
			$snippet['blocked'] = $blocked;

			// If the snippet must be executed only in the frontend, we bypass the block
			if ( !is_admin() && $snippet['scope'] === 'frontend' ) {
				$snippet['blocked'] = false;
			}

			return $snippet;
		}, $snippets );

		

        return $snippets;
    }


	#endregion

	#region Logs

	function get_logs() {
		$log_file_path = $this->get_logs_path();

		if ( !file_exists( $log_file_path ) ) {
			return "Empty log file.";
		}

		$content = file_get_contents( $log_file_path );
		$lines = explode( "\n", $content );
		$lines = array_filter( $lines );
		$lines = array_reverse( $lines );
		$content = implode( "\n", $lines );
		return $content;
	}

	function clear_logs() {
		$logPath = $this->get_logs_path();
		if ( file_exists( $logPath ) ) {
			unlink( $logPath );
		}

		$options = $this->get_all_options();
		$options['logs_path'] = null;
		$this->update_options( $options );
	}

	function get_logs_path() {
		$uploads_dir = wp_upload_dir();
		$uploads_dir_path = trailingslashit( $uploads_dir['basedir'] );

		$path = $this->get_option( 'logs_path' );

		if ( $path && file_exists( $path ) ) {
			// make sure the path is legal (within the uploads directory with the MWCODE_PREFIX and log extension)
			if ( strpos( $path, $uploads_dir_path ) !== 0 || strpos( $path, MWCODE_PREFIX ) === false || substr( $path, -4 ) !== '.log' ) {
				$path = null;
			} else {
				return $path;
			}
		}

		if ( !$path ) {
			$path = $uploads_dir_path . MWCODE_PREFIX . "_" . $this->random_ascii_chars() . ".log";
			if ( !file_exists( $path ) ) {
				touch( $path );
			}
			$options = $this->get_all_options();
			$options['logs_path'] = $path;
			$this->update_options( $options );
		}

		return $path;
	}

	function log( $data = null ) {
		if ( !$this->get_option( 'server_debug_mode', false ) ) { return false; }
		$log_file_path = $this->get_logs_path();
		$fh = @fopen( $log_file_path, 'a' );
		if ( !$fh ) { return false; }
		$date = date( "Y-m-d H:i:s" );
		if ( is_null( $data ) ) {
			fwrite( $fh, "\n" );
		}
		else {
			fwrite( $fh, "$date: {$data}\n" );
			//$this->log( "[MWCODE] $data" );
		}
		fclose( $fh );
		return true;
	}

	private function random_ascii_chars( $length = 8 ) {
		$characters = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ), range( '0', '9' ) );
		$characters_length = count( $characters );
		$random_string = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[rand(0, $characters_length - 1)];
		}

		return $random_string;
	}

	#endregion

	#region Helpers

	/**
     * Check if the request is from a white-listed REST route.
     *
     * @return bool
     */
    public static function is_white_listed_rest() {
        $authorized = false;
        $white_listed = array(
            'mwai/v1',
            'mwai-ui/v1',
            'media-file-renamer/v1',
            'media-cleaner/v1',
            'wplr/v1',
            'code-engine/v1',
            'wp/v2',
			'meow-gallery/v1',
        );

        $white_listed = apply_filters( 'meow_mwcode_white_listed_rest', $white_listed );

        $route = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
        $requested_route = null;
        
        if ( $route ) {
            $route_parts = explode( '/wp-json/', $route );
            
			if ( isset( $route_parts[1] ) ) {
                $requested_route = trim( $route_parts[1], '/' );
                foreach ( $white_listed as $white_listed_route ) {
                    if ( strpos( $requested_route, $white_listed_route ) === 0 ) {
                        $authorized = true;
                        $authorized = apply_filters( 'meow_mwcode_white_listed_rest_authorized', $authorized, $requested_route );
                        return $authorized;
                    }
                }
            }
				 
			if ( is_admin() ) {
				$authorized = true;

				$authorized = apply_filters( 'meow_mwcode_white_listed_rest_authorized', $authorized, $requested_route );
				return $authorized;
			}


        }

        $authorized = apply_filters( 'meow_mwcode_white_listed_rest_authorized', $authorized, $requested_route );
        return $authorized;
    }

	#endregion
}

?>