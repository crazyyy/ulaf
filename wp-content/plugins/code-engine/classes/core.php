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
  public $mwcode = null;
  public $licenser = null;

  private $option_name = 'mwcode_options';

  public function __construct() {
    global $mwcode;

    $this->site_url = get_site_url();
    $this->is_rest = MeowKit_MWCODE_Helpers::is_rest();
    $this->is_cli = defined( 'WP_CLI' ) && WP_CLI;

    // Snippets
    $snippet = new Meow_MWCODE_Modules_Snippet( $this );
    $this->snippet = $snippet;

    // Create API before plugins_loaded
    $this->mwcode = new Meow_MWCODE_API( $this, $snippet );
    $mwcode = $this->mwcode;

    // Add the shortcode for the "content" snippets
    add_shortcode( 'code-engine', [ $this, 'content_shortcode' ] );

    add_action( 'plugins_loaded', array( $this, 'init' ) );
  }

  function init() {
    // Initialize the licenser for Pro version
    if ( class_exists( 'MeowKitPro_MWCODE_Licenser' ) ) {
      $this->licenser = new MeowKitPro_MWCODE_Licenser( MWCODE_PREFIX, MWCODE_ENTRY, MWCODE_DOMAIN, MWCODE_ITEM_ID, MWCODE_VERSION );
    }

    // Part of the core, settings and stuff
    $this->admin = new Meow_MWCODE_Admin( $this );

    // Only for REST
    if ( $this->is_rest ) {
      new Meow_MWCODE_Rest( $this, $this->admin, $this->snippet );
    }
    
    // MCP integration - check both class and global variable
    if ( class_exists( 'Meow_MWAI_Core' ) || isset( $GLOBALS['mwai'] ) ) {
      new Meow_MWCODE_MCP( $this );
    }
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
      //"disallow_block_php" => true, // Do not allow PHP code to be execute through Blocks "code" parameter
      "code_blocks" => false,
      "code_blocks_whitelist" => [], // Whitelist for code blocks, if empty, all code blocks are allowed
      
      //LOGS
      "server_debug_mode" => false,

      //UI
      "ui_show_preview" => false,

      //AI
      "ai_suggestions" => false,
      "ai_engine_status"=> false,
      "ai_engine_message" => "",

      //API
      "api_endpoint" => false,
      "api_token" => md5( time() . rand() ),
      
      //MCP
      "mcp_support" => false,

      //MAINTENANCE
      "clean_uninstall" => false,
    ];
  }

  function get_all_options( ) {
    $options = get_option( $this->option_name, [] );
    $defaults = $this->list_options();
    
    // Merge with defaults to ensure all options exist
    $options = array_merge( $defaults, $options );
    
    $options = $this->sanitize_options( $options );
    return $options;
  }

  function update_options( $options ) {

    $options = $this->sanitize_options( $options );

    if ( !update_option( $this->option_name, $options, false ) ) {
      $this->log( '💾  There was an issue updating the options.' );
    }
   
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
    
    // Ensure mcp_support exists in options
    if ( !isset( $options['mcp_support'] ) ) {
      $options['mcp_support'] = false;
    }

    // Make sure safe mode whitelist is an array
    if ( ! is_array( $options['safe_mode_whitelist'] ) ) {
      $options['safe_mode_whitelist'] = explode( ",", $options['safe_mode_whitelist'] );
      $options_modified                = true;
    }

    // Update AI Engine status
    $options_modified = $this->updateAIEngineStatus( $options ) || $options_modified;

    // Disable AI related features if AI Engine is not available
    if ( ! $options['ai_engine_status'] ) {
      if ( $options['ai_suggestions'] !== false ) {
        $options['ai_suggestions'] = false;
        $options_modified          = true;
      }
      // Note: We don't disable MCP support here anymore
      // It will be checked at runtime in the MCP class
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

  #endregion

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

  function add_snippet( $params ) {

    $response = [
      "snippet" => null,
      "result" => false,
    ];

    $this->snippet->validate( $params );

    $params  = $this->snippet->formatParamsForDatabase( $params );
    $result  = $this->snippet->insert( $params );
    $snippet = $this->snippet->select_one( $result );

    if( $result ) {
      $params['id'] = (string)$result;

      $this->snippet->create_or_update_function_snippet( $params );
      $this->snippet->create_or_update_interval_snippet( $params );

      $this->snippet->get_function_snippets_data( $snippet );
    }

    $response['snippet'] = $snippet;
    $response['result']  = $result;

    return $response;
  }

  private function sanitize_arg( $name, $value, $type = null) {
    $real_type = gettype( $value );

    if ( $name[0] !== '$' ) { $name = '$' . $name; }

    if ( $type == null ) {
      $type = $real_type;
    }

    if ( $type != 'array' && !empty( $value ) && !is_numeric( $value ) && $value[0] !== '"' && $value[strlen( $value ) - 1] !== '"' ) {
      $value = '"' . esc_sql( $value ) . '"';
    }

    if ( $type === 'array' && $real_type === 'string' ) {
      // We got a string like this:  "["a", "b", "c"]" or "[ 1, 2, 3 ]"
      // We need to convert it to an array
      $value = str_replace( '"', '', $value );
      $value = str_replace( '[', '', $value );
      $value = str_replace( ']', '', $value );
      $value = explode( ',', $value );
      $value = array_map( 'trim', $value );
    }

    if ( $type === 'array' ) {
      // Convert to PHP array format instead of JSON
      $value = var_export( $value, true );
    }

    return [ $name, $value ];
  }

  function run_non_fn_snippet( $id, $code = null, $test = false, $prefix = '' ) {
    // Retrieve the snippet code from the provided code or via the snippet ID.
    if ( $code ) {
      $snippet = [ 'code' => $code ];
    } else {
      $snippet = $this->get_snippet( $id );
    }

    // Remove any PHP opening tag.
    $snippet['code'] = $this->snippet->sanitize_code( $snippet['code'] );

    if ( $test ) {
      $snippet['code'] = preg_replace( '/echo\s+(.+?);/s', 'echo $1 . "\n";', $snippet['code'] );
    }

    if( $prefix ) {
      $snippet['code'] = $prefix . "\n" . $snippet['code'];
    }
  
    $error  = null;
    $output = null;
  
    try {
      ob_start();
      eval( $snippet['code'] );
      $output = ob_get_clean();
    } catch ( Throwable $e ) {
      $snippet_id = $id ? " ( ID: $id )" : '(Content Gutenberg Block)';
      $this->log( '🔴 Error executing the snippet ' . $snippet_id . ' : ' . $e->getMessage() );
      ob_clean();
    } finally {
      restore_error_handler();
    }
  
    // If in test mode, return output as an array of lines with an 'error' key if needed.
    if ( $test ) {
      $output = explode( "\n", trim( $output ) );
      if ( $error !== null ) {
        $output['error'] = $error->getMessage();
      }
    } else {
      if ( $error !== null ) {
        throw $error;
      }
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
          list( $sanitizedInputName, $sanitizedInputValue ) = $this->sanitize_arg( $name, $value['input'], $value['type'] );
          $params['values'][$sanitizedInputName]['input'] = $sanitizedInputValue;
        }

        if( array_key_exists( 'default', $value) ) {
          list( $sanitizedDefaultValueName, $sanitizedDefaultValue ) = $this->sanitize_arg( $name, $value['default'], $value['type'] );
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
    

    if ( $page === 'mwcode_settings' ) {
      // If we blocks global snippets like nonce_life filter, we would block the settings page so let's remove the block for this page
      
      $blocked = false;
      //$blocked = true;
    }
    // Block REST requests that aren't whitelisted
    elseif ( MeowKit_MWCODE_Helpers::is_rest() && !Meow_MWCODE_Core::is_white_listed_rest() ) {
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
      $snippet['code'] = $this->snippet->sanitize_code( $snippet['code'] );
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

  #region Shortcodes
  function separate_mwcode_atts( $atts ) {

    if( array_key_exists( 'id', $atts ) )     unset( $atts['id'] );
    if( array_key_exists( 'target', $atts ) ) unset( $atts['target'] );
    if( array_key_exists( 'code', $atts ) )   unset( $atts['code'] );

    return $atts;
  }

  function content_shortcode( $atts ) {

    $user_atts = $this->separate_mwcode_atts( $atts );

    $atts = shortcode_atts( array(
      'id'     => null,
      'target' => null, // js or php
      'code'   => null, // For Guttenberg block usage
    ), $atts, 'code-engine' );

    $id = $atts['id'];
    $target = $atts['target'];
    $code = $atts['code'];
    $current_post = get_post();
    
    $no_js  = defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML;
    $allow_php = $this->get_option( 'code_blocks', false );
    $allow_php_whitelist = $this->get_option( 'code_blocks_whitelist', [] );
   
    // If the ID is null, it means it comes from a Guttenberg block
    $is_block = empty( $id ) && !empty( $code );

    if( $is_block ) {

      if( $target !== 'js' && $target !== 'php' ) {
        return '<b>Code Engine:</b> Please provide a valid target (js or php).';
      }

      if ( $no_js && $target === 'js' ) {
        return '<b>Code Engine:</b> Code Block JS are disabled because unfiltered HTML is not allowed on your server.';
      }

      if ( $target === 'php' ) {

        if ( !$allow_php ) {
          return '<b>Code Engine:</b> Code Block PHP are disabled. If you are an administrator, you can enable it in the settings, this is not recommended. Please use a Content Snippet ( PHP ) instead.';
        }

        if ( !empty( $allow_php_whitelist ) && !in_array( $current_post->ID, $allow_php_whitelist ) ) {
          return '<b>Code Engine:</b> Code Block PHP are disabled for this post. If you are an administrator, you can enable it in the settings, this is not recommended. Please use a Content Snippet ( PHP ) instead.';
        }
      }

      // Because the code from Blocks are sanitized, we need to replace the &quot; with "
      $code = str_replace( '&quot;', '"', $code );

      if ( $target === 'js' ) {
        $output = '<script>' . $code . '</script>';
      }

      if ( $target === 'php' ) {
        $output = $this->run_non_fn_snippet( null, $code );
      }

      return $output;
    }

    // If not a block, we get the snippet by ID
    // If the ID is not null, it means it comes from a shortcode		
    if ( empty( $id ) && empty( $code ) ) {
      return '<b>Code Engine:</b> Please provide a snippet ID.';
    }

    $snippet = $this->get_snippet( $id );

    if ( empty( $snippet ) ) {
      return '<b>Code Engine:</b> The snippet does not exist.';
    }

    //Check if the snippet scope is either content_php or content_js
    $is_content_php = $snippet['scope'] === 'content_php';
    $is_content_js  = $snippet['scope'] === 'content_js';

    if ( !$is_content_php && !$is_content_js ) {
      return '<b>Code Engine:</b> The snippet is not a content snippet.';
    }

    if( $no_js && $is_content_js ) {
      return '<b>Code Engine:</b> Code Engine JS snippets are disabled because unfiltered HTML is not allowed on your server.';
    }

    //Check if the snippet is active
    if ( !$snippet['active'] ) {
      return '<b>Code Engine:</b> The snippet is not active.';
    }

    $output = '<b>Code Engine:</b> No output.';

    if ( $is_content_js ) {
      $output = '<script>' . $snippet['code'] . '</script>';
    }

    if ( $is_content_php ) {
      $prefix = "\$mwcode_atts = unserialize( '" . serialize( $user_atts ) . "' );";
      $output = $this->run_non_fn_snippet( $id, null, false, $prefix );
    }

    return $output;
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
    $options = get_option( 'mwcode_snippet_vault_options', array() );
    
    // Early return if bypass is enabled
    if ( !empty( $options['bypass_rest_security'] ) ) {
      return true;
    }

    // Early return for admin requests
    if ( is_admin() ) {
      return apply_filters( 'mwcode_rest_authorized', true, null );
    }

    // Get the requested route
    $requested_route = self::get_requested_rest_route();
    if ( !$requested_route ) {
      return apply_filters( 'mwcode_rest_authorized', false, null );
    }

    // Check against whitelist
    $white_listed = apply_filters( 'mwcode_rest_whitelist', array(
      'mwai/v1',
      'mwai-ui/v1',
      'media-file-renamer/v1',
      'media-cleaner/v1',
      'wplr/v1',
      'code-engine/v1',
      'wp/v2',
      'meow-gallery/v1',
      'mcp/v1',
    ));

    $authorized = self::is_route_whitelisted( $requested_route, $white_listed );
    
    // Log if debug mode is enabled
    if ( !empty( $options['server_debug_mode'] ) ) {
      self::log_route_status( $requested_route, $authorized );
    }

    return apply_filters( 'mwcode_rest_authorized', $authorized, $requested_route );
  }

  /**
   * Extract the REST route from the request URI.
   *
   * @return string|null
   */
  public static function get_requested_rest_route() {
    if ( !isset( $_SERVER['REQUEST_URI'] ) ) {
      return null;
    }

    $route_parts = explode( '/wp-json/', $_SERVER['REQUEST_URI'] );
    
    if ( isset( $route_parts[1] ) ) {
      return trim( $route_parts[1], '/' );
    }

    return null;
  }

  /**
   * Check if a route is in the whitelist.
   *
   * @param string $route The route to check
   * @param array $white_listed The whitelist array
   * @return bool
   */
  private static function is_route_whitelisted( $route, $white_listed ) {
    foreach ( $white_listed as $white_listed_route ) {
      if ( strpos( $route, $white_listed_route ) === 0 ) {
        return true;
      }
    }
    return false;
  }

  /**
   * Log the route authorization status.
   *
   * @param string $route The route being checked
   * @param bool $authorized Whether the route is authorized
   */
  private static function log_route_status( $route, $authorized ) {
    global $mwcode_core;
    
    $message = $authorized 
      ? "✅ REST route authorized: " . $route
      : "❌ REST route rejected (not whitelisted): " . $route;

    if ( isset( $mwcode_core ) ) {
      $mwcode_core->log( $message );
    } else {
      error_log( "[Code Engine] " . $message );
    }
  }

  #endregion
}

?>