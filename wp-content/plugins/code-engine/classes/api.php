<?php

class Meow_MWCODE_API {
  public $core;
  private $snippet_module;
  private $debug = false;

  public function __construct( $core, $snippet_module ) {
    $this->core = $core;
    $this->snippet_module = $snippet_module;
    $this->debug = $this->core->get_option( 'server_debug_mode' );
  }

  #region Simple API
  
  /**
   * Get a snippet by its ID.
   * 
   * The options are used to filter the snippets:
   * - 'php_ready_args' (bool): If false, the arguments will not be formatted for PHP. (no $ before the names).
   *
   * @param int $id The snippet ID.
   * @param array $options Options for filtering.
   *
   * @return array|null The snippet data or null if not found.
   * @throws Exception If the snippet cannot be retrieved.
   */
  public function getSnippet( $id, $options = [] ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [GetSnippet]: ID=%d, Options=%s', $id, json_encode( $options ) ) );
      }
      
      // Try to get as function snippet first (for backward compatibility)
      $snippet = $this->snippet_module->get_function( $id, $options );
      
      // If not found as function, get from general snippets
      if ( empty( $snippet ) ) {
        $snippet = $this->snippet_module->select_one( $id, $options );
        if ( empty( $snippet ) ) {
          throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
        }
      }
      
      return $snippet;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [GetSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Get a snippet by its name.
   * 
   * The options are used to filter the snippets:
   * - 'php_ready_args' (bool): If false, the arguments will not be formatted for PHP. (no $ before the names).
   *
   * @param string $name The name of the snippet to be retrieved.
   * @param array $options Options for filtering.
   *
   * @return array|null The snippet data or null if not found.
   * @throws Exception If the snippet cannot be retrieved.
   */
  public function getSnippetByName( $name, $options = [] ) {
    try {
      if ( empty( $name ) ) {
        throw new Exception( 'The snippet name is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [GetSnippetByName]: Name=%s, Options=%s', $name, json_encode( $options ) ) );
      }
      
      $snippet = $this->snippet_module->get_function_by_name( $name, $options );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with name "%s" not found.', $name ) );
      }
      
      return $snippet;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [GetSnippetByName]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Get all snippets.
   * 
   * @param bool $safe Whether to filter out snippets with invalid names.
   * @param string|null $scope Optional scope filter: 'function', 'backend', 'frontend', 'scheduled', 'persistent'.
   * @return array The list of snippets.
   */
  public function getSnippets( $safe = true, $scope = null ) {
    try {

      $allSnippets = $this->snippet_module->select( 0, 9999, 
      [ ['accessor' => 'scope', 'value' => $scope] ]
      , [] );

      $snippets = $allSnippets['data'] ?? [];
      
      
      if ( $safe ) {

        $snippets = array_filter( $snippets, function( $snippet ) {

          if( $snippet['scope'] === 'function' ) {

            if( array_key_exists( 'functionName', $snippet ) )
            {
              $name = $snippet['functionName'];
              if ( !preg_match( '/^[a-zA-Z0-9_-]{1,64}$/', $name ) ) {
                if ( $this->debug ) {
                  $this->core->log( sprintf( 'API [GetSnippets]: Filtered out snippet with invalid name: %s', $name ) );
                }
                return false;
              }
            } else {
              if ( $this->debug ) {
                $this->core->log( 'API [GetSnippets]: Filtered out snippet with missing functionName.' );
              }
              return false;
            }
            
          }

          return true;
        } );
      }
      
      return array_values( $snippets ); // Reset array keys
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [GetSnippets]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  
  /**
   * Backward compatibility wrapper for getSnippets().
   * @deprecated Use getSnippets() instead.
   * 
   * @param bool $safe Whether to filter out snippets with invalid names.
   * @return array The list of function snippets only.
   */
  public function get_functions( $safe = true ) {
    $this->core->log( '⚠️ API Warning: get_functions() is deprecated. Please use getSnippets() instead.' );
    // Return only function snippets for backward compatibility
    return $this->getSnippets( $safe, 'function' );
  }

  /**
   * Execute a snippet by its ID.
   * The arguments should be an associative array with the argument names as keys.
   * Example: [ "$city" => "'Tokyo'", "$date" => "1999" ]
   *
   * @param int $id The snippet ID.
   * @param array $args The arguments to pass to the snippet.
   *
   * @return mixed The result of the snippet execution.
   * @throws Exception If the snippet cannot be executed.
   */
  public function executeSnippet( $id, $args = [], $reply = null ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [ExecuteSnippet]: ID=%d, Args=%s', $id, json_encode( $args ) ) );
      }
      
      // Verify the snippet exists
      $snippet = $this->snippet_module->get_function( $id );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
      }

      $query = $reply ? $reply->query : null;
      $array_query = [];
      if( $query ) {
        $array_query['envId']           = $query->envId    ?? 'No EnvId in Query';
        $array_query['botId']           = $query->botId    ?? 'No BotId in Query';
        $array_query['customId']        = $query->customId ?? 'No CustomId in Query';
        $array_query['embeddingsEnvId'] = $query->embeddingsEnvId ?? 'No EmbeddingsEnvId in Query';


        $array_query['chatId']       = $query->chatId  ?? 'No ChatId in Query';
        $array_query['session']      = $query->session ?? 'No Session in Query';
        $array_query['scope']        = $query->scope   ?? 'No Scope in Query';

        $array_query['instructions'] = $query->instructions ?? 'No Instructions in Query';
        $array_query['context']      = $query->context ?? 'No Context in Query';

        $array_query['messages']     = $query->messages ? json_encode( $query->messages ) : 'No Messages in Query';
        $array_query['message']      = $query->message  ?? 'No Message in Query';

        $array_query['model']        = $query->model    ?? 'No Model in Query';
        $array_query['feature']      = $query->feature  ?? 'No Feature in Query';
      }

      $args['__mwai_query'] = $array_query;

      $output = $this->core->run_snippet( $id, $args );
      
      if ( $this->debug ) {
        $shortOutput = is_string( $output ) ? substr( $output, 0, 100 ) : json_encode( $output );
        $this->core->log( sprintf( 'API [ExecuteSnippet]: Success, Output=%s%s', 
          $shortOutput, 
          strlen( $shortOutput ) > 100 ? '...' : '' 
        ) );
      }
      
      return $output;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [ExecuteSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Execute a snippet by its name.
   *
   * @param string $name The snippet name.
   * @param array $args The arguments to pass to the snippet.
   *
   * @return mixed The result of the snippet execution.
   * @throws Exception If the snippet cannot be executed.
   */
  public function executeSnippetByName( $name, $args = [] ) {
    try {
      if ( empty( $name ) ) {
        throw new Exception( 'The snippet name is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [ExecuteSnippetByName]: Name=%s, Args=%s', $name, json_encode( $args ) ) );
      }
      
      // Get the snippet by name to find its ID
      $snippet = $this->snippet_module->get_function_by_name( $name );
      if ( empty( $snippet ) || empty( $snippet['snippetId'] ) ) {
        throw new Exception( sprintf( 'Snippet with name "%s" not found.', $name ) );
      }
      
      return $this->executeSnippet( $snippet['snippetId'], $args );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [ExecuteSnippetByName]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  #endregion
  
  #region Standard API
  
  /**
   * Create a new snippet.
   *
   * @param string $name Name of the snippet.
   * @param string $code Code of the snippet.
   * @param string $scope Scope of the snippet: 'function', 'backend', 'frontend', 'scheduled', 'persistent'.
   * @param array $options Additional options:
   *   - target: 'php' or 'js' (for function snippets)
   *   - description: Description of the snippet
   *   - args: Arguments for function snippets
   *   - argsData: Argument data for function snippets (use 'description' for each argument's description)
   *   - behavior: 'dynamic' or 'static' (for function snippets)
   *   - tags: Array of tags
   *   - priority: Execution priority
   *   - active: Active status (true/false)
   *   - endpoint: REST endpoint
   *   - method: HTTP method (GET/POST)
   *   - intervalHours: Hours for scheduled snippets
   *   - intervalMinutes: Minutes for scheduled snippets
   * 
   * @return array The created snippet data.
   * @throws Exception If the snippet cannot be created.
   */
  public function createSnippet( $name, $code, $scope = 'function', $options = [] ) {

    try {
      if ( empty( $name ) ) {
        throw new Exception( 'The snippet name is required.' );
      }
      
      if ( empty( $code ) ) {
        throw new Exception( 'The snippet code is required.' );
      }
      
      $validScopes = ['function', 'backend', 'frontend', 'scheduled', 'persistent'];
      if ( !in_array( $scope, $validScopes ) ) {
        throw new Exception( sprintf( 'Invalid scope. Must be one of: %s', implode( ', ', $validScopes ) ) );
      }
      
      // Extract options with defaults
      $target = $options['target'] ?? 'php';
      $description = $options['description'] ?? '';
      $args = $options['args'] ?? [];
      $argsData = $options['argsData'] ?? [];
      $behavior = $options['behavior'] ?? 'dynamic';
      $tags = $options['tags'] ?? ['api'];
      $priority = $options['priority'] ?? 10;
      $active = $options['active'] ?? true;
      $endpoint = $options['endpoint'] ?? '';
      $method = $options['method'] ?? 'POST';
      $intervalHours = $options['intervalHours'] ?? 0;
      $intervalMinutes = $options['intervalMinutes'] ?? 0;
      
      // Validate function-specific options
      if ( $scope === 'function' ) {
        if ( !in_array( $target, ['php', 'js'] ) ) {
          throw new Exception( 'The target must be either "php" or "js" for function snippets.' );
        }
        if ( !in_array( $behavior, ['dynamic', 'static'] ) ) {
          throw new Exception( 'The behavior must be either "dynamic" or "static" for function snippets.' );
        }
      }
      
      if ( $this->debug ) {
        $shortCode = substr( $code, 0, 100 );
        $this->core->log( sprintf( 'API [CreateSnippet]: Name=%s, Scope=%s, Options=%s', 
          $name, $scope, json_encode( $options ) ) );
      }
      
      $params = [
        // Core values
        'id'           => null,
        'name'         => $name,
        'code'         => $code,
        'scope'        => $scope,
        'active'       => $active ? 1 : 0,
        'priority'     => $priority,
        'tags'         => $tags,
        'description'  => $description,
        'endpoint'     => $endpoint,
        'method'       => $method,
      ];
      
      // Add function-specific params
      if ( $scope === 'function' ) {
        $params['functionName'] = $name;
        $params['functionTarget'] = $target;
        $params['functionArgs'] = $args;
        $params['functionArgsDict'] = $argsData;
        $params['functionBehavior'] = $behavior;
      }
      
      // Add scheduled-specific params
      if ( $scope === 'scheduled' ) {
        $params['intervalHours'] = $intervalHours;
        $params['intervalMinutes'] = $intervalMinutes;
      }
      
      $result = $this->core->add_snippet( $params );
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [CreateSnippet]: Success, ID=%d', $result['id'] ?? 0 ) );
      }
      
      return $result;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [CreateSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Update a snippet by its ID.
   * All parameters except ID are optional. If not specified, the current values will be used.
   *
   * @param int $id ID of the snippet to update.
   * @param array $params Parameters to update. Can include:
   *   - name: Snippet name
   *   - code: Snippet code
   *   - scope: Snippet scope
   *   - description: Description
   *   - active: Active status
   *   - priority: Execution priority
   *   - tags: Array of tags
   *   - endpoint: REST endpoint
   *   - method: HTTP method
   *   - target: 'php' or 'js' (for function snippets)
   *   - args: Arguments (for function snippets)
   *   - argsData: Argument data (for function snippets)
   *   - behavior: 'dynamic' or 'static' (for function snippets)
   *   - intervalHours: Hours (for scheduled snippets)
   *   - intervalMinutes: Minutes (for scheduled snippets)
   * 
   * @return array The updated snippet data.
   * @throws Exception If the snippet cannot be updated.
   */
  public function updateSnippet( $id, $params = [] ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [UpdateSnippet]: ID=%d, Params=%s', $id, json_encode( $params ) ) );
      }
      
      // Get existing snippet
      $snippet = $this->snippet_module->select_one( $id );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
      }
      
      // Validate scope if provided
      if ( isset( $params['scope'] ) ) {
        $validScopes = ['function', 'backend', 'frontend', 'scheduled', 'persistent'];
        if ( !in_array( $params['scope'], $validScopes ) ) {
          throw new Exception( sprintf( 'Invalid scope. Must be one of: %s', implode( ', ', $validScopes ) ) );
        }
      }
      
      // Validate function-specific parameters if provided
      $scope = $params['scope'] ?? $snippet['scope'];
      if ( $scope === 'function' ) {
        if ( isset( $params['target'] ) && !in_array( $params['target'], ['php', 'js'] ) ) {
          throw new Exception( 'The target must be either "php" or "js" for function snippets.' );
        }
        if ( isset( $params['behavior'] ) && !in_array( $params['behavior'], ['dynamic', 'static'] ) ) {
          throw new Exception( 'The behavior must be either "dynamic" or "static" for function snippets.' );
        }
      }
      
      // Build update params - merge with existing values
      $updateParams = [
        'id'           => $id,
        'name'         => $params['name'] ?? $snippet['name'],
        'code'         => $params['code'] ?? $snippet['code'],
        'scope'        => $scope,
        'active'       => isset( $params['active'] ) ? ( $params['active'] ? 1 : 0 ) : $snippet['active'],
        'priority'     => $params['priority'] ?? $snippet['priority'],
        'tags'         => $params['tags'] ?? $snippet['tags'],
        'description'  => $params['description'] ?? $snippet['description'] ?? '',
        'endpoint'     => $params['endpoint'] ?? $snippet['endpoint'] ?? '',
        'method'       => $params['method'] ?? $snippet['method'] ?? 'POST',
      ];
      
      // Add function-specific params if it's a function snippet
      if ( $scope === 'function' ) {
        $updateParams['functionName'] = $params['name'] ?? $snippet['name'];
        $updateParams['functionTarget'] = $params['target'] ?? $snippet['functionTarget'] ?? 'php';
        $updateParams['functionArgs'] = $params['args'] ?? $snippet['functionArgs'] ?? [];
        $updateParams['functionArgsDict'] = $params['argsData'] ?? $snippet['functionArgsDict'] ?? [];
        $updateParams['functionBehavior'] = $params['behavior'] ?? $snippet['functionBehavior'] ?? 'dynamic';
      }
      
      // Add scheduled-specific params if it's a scheduled snippet
      if ( $scope === 'scheduled' ) {
        $updateParams['intervalHours'] = $params['intervalHours'] ?? $snippet['intervalHours'] ?? 0;
        $updateParams['intervalMinutes'] = $params['intervalMinutes'] ?? $snippet['intervalMinutes'] ?? 0;
      }
      
      $result = $this->core->add_snippet( $updateParams );
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [UpdateSnippet]: Success, ID=%d', $result['id'] ?? $id ) );
      }
      
      return $result;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [UpdateSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Delete a snippet by its ID.
   *
   * @param int $id Snippet ID.
   * @return bool True if the snippet was deleted successfully.
   * @throws Exception If the snippet cannot be deleted.
   */
  public function deleteSnippet( $id ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [DeleteSnippet]: ID=%d', $id ) );
      }
      
      // Verify the snippet exists
      $snippet = $this->snippet_module->select_one( $id );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
      }
      
      $params = [ 'id' => $id ];
      
      // Delete function snippet data if it's a function
      if ( $snippet['scope'] === 'function' ) {
        $this->snippet_module->delete_function_snippet( $params );
      }
      
      // Delete scheduled snippet data if it's scheduled
      if ( $snippet['scope'] === 'scheduled' ) {
        $this->snippet_module->delete_interval_snippet( $params );
      }
      
      $result = $this->snippet_module->delete( $params );
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [DeleteSnippet]: Success, ID=%d', $id ) );
      }
      
      return !empty( $result );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [DeleteSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }

  /**
   * Delete a snippet by its name.
   *
   * @param string $name Snippet name.
   * @return bool True if the snippet was deleted successfully.
   * @throws Exception If the snippet cannot be deleted.
   */
  public function deleteSnippetByName( $name ) {
    try {
      if ( empty( $name ) ) {
        throw new Exception( 'The snippet name is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [DeleteSnippetByName]: Name=%s', $name ) );
      }
      
      // Try to find as function first
      $snippet = $this->snippet_module->get_function_by_name( $name );
      if ( !empty( $snippet ) && !empty( $snippet['snippetId'] ) ) {
        return $this->deleteSnippet( $snippet['snippetId'] );
      }
      
      // If not found as function, search in all snippets
      $allSnippets = $this->snippet_module->select( 0, 9999, [], [] );
      foreach ( $allSnippets['data'] as $s ) {
        if ( $s['name'] === $name ) {
          return $this->deleteSnippet( $s['id'] );
        }
      }
      
      throw new Exception( sprintf( 'Snippet with name "%s" not found.', $name ) );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [DeleteSnippetByName]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  #endregion
  
  #region Standard API (No REST API)
  
  /**
   * Check if a snippet exists by ID.
   * 
   * @param int $id The snippet ID.
   * @return bool True if the snippet exists.
   */
  public function snippetExists( $id ) {
    try {
      if ( empty( $id ) ) {
        return false;
      }
      
      $snippet = $this->snippet_module->select_one( $id );
      return !empty( $snippet );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [SnippetExists]: ' . $e->getMessage() );
      }
      return false;
    }
  }
  
  /**
   * Check if a snippet exists by name.
   * 
   * @param string $name The snippet name.
   * @return bool True if the snippet exists.
   */
  public function snippetExistsByName( $name ) {
    try {
      if ( empty( $name ) ) {
        return false;
      }
      
      // Check in functions first
      $snippet = $this->snippet_module->get_function_by_name( $name );
      if ( !empty( $snippet ) ) {
        return true;
      }
      
      // Check in all snippets
      $allSnippets = $this->snippet_module->select( 0, 9999, [], [] );
      foreach ( $allSnippets['data'] as $s ) {
        if ( $s['name'] === $name ) {
          return true;
        }
      }
      
      return false;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [SnippetExistsByName]: ' . $e->getMessage() );
      }
      return false;
    }
  }
  
  /**
   * Validates snippet code syntax.
   * 
   * @param string $code The snippet code to validate.
   * @param string $target The target language ('php' or 'js').
   * @return array ['valid' => bool, 'error' => string|null]
   */
  public function validateSnippetCode( $code, $target = 'php' ) {
    try {
      if ( empty( $code ) ) {
        return [ 'valid' => false, 'error' => 'Code is empty.' ];
      }
      
      if ( !in_array( $target, ['php', 'js'] ) ) {
        return [ 'valid' => false, 'error' => 'Invalid target language.' ];
      }
      
      if ( $target === 'php' ) {
        // Use the core's validate_php_code method if available
        if ( method_exists( $this->core, 'validate_php_code' ) ) {
          $validation = $this->core->validate_php_code( $code );
          return [ 
            'valid' => $validation['valid'] ?? false, 
            'error' => $validation['error'] ?? null 
          ];
        }
      }
      
      // Basic validation if no specific validator available
      return [ 'valid' => true, 'error' => null ];
    }
    catch ( Exception $e ) {
      return [ 'valid' => false, 'error' => $e->getMessage() ];
    }
  }
  #endregion
  
  #region Snippet Management
  
  /**
   * Activate a snippet by its ID.
   * 
   * @param int $id The snippet ID.
   * @return bool True if activated successfully.
   * @throws Exception If the snippet cannot be activated.
   */
  public function activateSnippet( $id ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [ActivateSnippet]: ID=%d', $id ) );
      }
      
      // Get the snippet
      $snippet = $this->snippet_module->select_one( $id );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
      }
      
      // Update only the active status
      $result = $this->updateSnippet( $id, [ 'active' => true ] );
      return !empty( $result );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [ActivateSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  
  /**
   * Deactivate a snippet by its ID.
   * 
   * @param int $id The snippet ID.
   * @return bool True if deactivated successfully.
   * @throws Exception If the snippet cannot be deactivated.
   */
  public function deactivateSnippet( $id ) {
    try {
      if ( empty( $id ) ) {
        throw new Exception( 'The snippet ID is required.' );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [DeactivateSnippet]: ID=%d', $id ) );
      }
      
      // Get the snippet
      $snippet = $this->snippet_module->select_one( $id );
      if ( empty( $snippet ) ) {
        throw new Exception( sprintf( 'Snippet with ID %d not found.', $id ) );
      }
      
      // Update only the active status
      $result = $this->updateSnippet( $id, [ 'active' => false ] );
      return !empty( $result );
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [DeactivateSnippet]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  
  /**
   * Get snippets by scope.
   * 
   * @param string $scope The scope to filter by: 'backend', 'frontend', 'function', 'scheduled', 'persistent'.
   * @param array $filters Additional filters: 'active' (bool), 'tags' (array).
   * @return array The list of snippets matching the criteria.
   * @throws Exception If the scope is invalid.
   */
  public function getSnippetsByScope( $scope, $filters = [] ) {
    try {
      $validScopes = ['function', 'backend', 'frontend', 'scheduled', 'persistent', 'content_js', 'content_php'];
      if ( !in_array( $scope, $validScopes ) ) {
        throw new Exception( sprintf( 'Invalid scope. Must be one of: %s', implode( ', ', $validScopes ) ) );
      }
      
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [GetSnippetsByScope]: Scope=%s, Filters=%s', $scope, json_encode( $filters ) ) );
      }
      
      // For function scope, we can use the existing get_functions method
      if ( $scope === 'function' && empty( $filters ) ) {
        return $this->snippet_module->get_functions();
      }
      
      // For other scopes or with filters, we need to query all snippets
      $allSnippets = $this->snippet_module->select( 0, 9999, [], [] );
      $filtered = [];
      
      foreach ( $allSnippets['data'] as $snippet ) {
        // Filter by scope
        if ( $snippet['scope'] !== $scope ) {
          continue;
        }
        
        // Apply additional filters
        if ( isset( $filters['active'] ) && $snippet['active'] != $filters['active'] ) {
          continue;
        }
        
        if ( isset( $filters['tags'] ) && is_array( $filters['tags'] ) ) {
          $snippetTags = is_array( $snippet['tags'] ) ? $snippet['tags'] : [];
          $hasAllTags = true;
          foreach ( $filters['tags'] as $tag ) {
            if ( !in_array( $tag, $snippetTags ) ) {
              $hasAllTags = false;
              break;
            }
          }
          if ( !$hasAllTags ) {
            continue;
          }
        }
        
        $filtered[] = $snippet;
      }
      
      return $filtered;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [GetSnippetsByScope]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  
  /**
   * Get all active snippets.
   * 
   * @param string|null $scope Optional scope filter.
   * @return array The list of active snippets.
   */
  public function getActiveSnippets( $scope = null ) {
    try {
      if ( $this->debug ) {
        $this->core->log( sprintf( 'API [GetActiveSnippets]: Scope=%s', $scope ?? 'all' ) );
      }
      
      if ( $scope ) {
        return $this->getSnippetsByScope( $scope, [ 'active' => true ] );
      }
      
      // Get all active snippets
      $allSnippets = $this->snippet_module->select( 0, 9999, [], [] );
      $active = [];
      
      foreach ( $allSnippets['data'] as $snippet ) {
        if ( $snippet['active'] ) {
          $active[] = $snippet;
        }
      }
      
      return $active;
    }
    catch ( Exception $e ) {
      if ( $this->debug ) {
        $this->core->log( '⚠️ API Error [GetActiveSnippets]: ' . $e->getMessage() );
      }
      throw $e;
    }
  }
  #endregion
}
