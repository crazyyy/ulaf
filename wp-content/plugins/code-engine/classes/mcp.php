<?php

class Meow_MWCODE_MCP {
  private $core;
  private $api;
  
  public function __construct( $core ) {
    $this->core = $core;
    
    // Initialize everything on 'init' to ensure options are loaded
    add_action( 'init', array( $this, 'init' ), 20 );
  }
  
  public function init() {
    global $mwcode, $mwai;
    $this->api = $mwcode;
    
    // Only register MCP if enabled AND AI Engine is available
    if ( $this->core->get_option( 'mcp_support', false ) && isset( $mwai ) ) {
      // Register MCP tools
      add_filter( 'mwai_mcp_tools', array( $this, 'register_tools' ) );
      
      // Handle MCP tool execution
      add_filter( 'mwai_mcp_callback', array( $this, 'handle_tool_execution' ), 10, 4 );
    }
  }
  
  public function register_tools( $tools ) {
    // Get Snippet
    $tools[] = [
      'name' => 'mwcode_get_snippet',
      'description' => 'Get a Code Engine snippet by its ID',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ],
          'options' => [
            'type' => 'object',
            'description' => 'Optional filtering options',
            'properties' => [
              'php_ready_args' => [
                'type' => 'boolean',
                'description' => 'If false, arguments will not be formatted for PHP (no $ before names)'
              ]
            ]
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Get Snippet by Name
    $tools[] = [
      'name' => 'mwcode_get_snippet_by_name',
      'description' => 'Get a Code Engine snippet by its name',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'The snippet name'
          ],
          'options' => [
            'type' => 'object',
            'description' => 'Optional filtering options',
            'properties' => [
              'php_ready_args' => [
                'type' => 'boolean',
                'description' => 'If false, arguments will not be formatted for PHP (no $ before names)'
              ]
            ]
          ]
        ],
        'required' => ['name']
      ]
    ];
    
    // Get Snippets
    $tools[] = [
      'name' => 'mwcode_get_snippets',
      'description' => 'Get all Code Engine snippets, optionally filtered by scope',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'safe' => [
            'type' => 'boolean',
            'description' => 'Whether to filter out snippets with invalid names',
            'default' => true
          ],
          'scope' => [
            'type' => 'string',
            'description' => 'Optional scope filter',
            'enum' => ['function', 'backend', 'frontend', 'scheduled', 'persistent']
          ]
        ]
      ]
    ];
    
    // Execute Snippet
    $tools[] = [
      'name' => 'mwcode_execute_snippet',
      'description' => 'Execute a Code Engine snippet by its ID',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ],
          'args' => [
            'type' => 'object',
            'description' => 'Arguments to pass to the snippet (key-value pairs)',
            'additionalProperties' => true
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Execute Snippet by Name
    $tools[] = [
      'name' => 'mwcode_execute_snippet_by_name',
      'description' => 'Execute a Code Engine snippet by its name',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'The snippet name'
          ],
          'args' => [
            'type' => 'object',
            'description' => 'Arguments to pass to the snippet (key-value pairs)',
            'additionalProperties' => true
          ]
        ],
        'required' => ['name']
      ]
    ];
    
    // Create Snippet
    $tools[] = [
      'name' => 'mwcode_create_snippet',
      'description' => 'Create a new Code Engine snippet',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'Name of the snippet'
          ],
          'code' => [
            'type' => 'string',
            'description' => 'Code of the snippet'
          ],
          'scope' => [
            'type' => 'string',
            'description' => 'Scope of the snippet',
            'enum' => ['function', 'backend', 'frontend', 'scheduled', 'persistent'],
            'default' => 'function'
          ],
          'options' => [
            'type' => 'object',
            'description' => 'Additional options for the snippet',
            'properties' => [
              'target' => [
                'type' => 'string',
                'enum' => ['php', 'js'],
                'description' => 'Target language (for function snippets)'
              ],
              'description' => [
                'type' => 'string',
                'description' => 'Description of the snippet'
              ],
              'args' => [
                'type' => 'array',
                'description' => 'Arguments for function snippets',
                'items' => [ 'type' => 'string' ]
              ],
              'argsData' => [
                'type' => 'object',
                'description' => 'Argument data for function snippets',
                'additionalProperties' => [
                  'type' => 'object',
                  'properties' => [
                    'type' => [ 'type' => 'string' ],
                    'description' => [ 'type' => 'string' ],
                    'default' => [ 'type' => 'string' ]
                  ]
                ]
              ],
              'behavior' => [
                'type' => 'string',
                'enum' => ['dynamic', 'static'],
                'description' => 'Behavior for function snippets'
              ],
              'tags' => [
                'type' => 'array',
                'description' => 'Array of tags',
                'items' => [ 'type' => 'string' ]
              ],
              'priority' => [
                'type' => 'integer',
                'description' => 'Execution priority'
              ],
              'active' => [
                'type' => 'boolean',
                'description' => 'Active status'
              ],
              'endpoint' => [
                'type' => 'string',
                'description' => 'REST endpoint'
              ],
              'method' => [
                'type' => 'string',
                'enum' => ['GET', 'POST'],
                'description' => 'HTTP method'
              ],
              'intervalHours' => [
                'type' => 'integer',
                'description' => 'Hours for scheduled snippets'
              ],
              'intervalMinutes' => [
                'type' => 'integer',
                'description' => 'Minutes for scheduled snippets'
              ]
            ]
          ]
        ],
        'required' => ['name', 'code']
      ]
    ];
    
    // Update Snippet
    $tools[] = [
      'name' => 'mwcode_update_snippet',
      'description' => 'Update an existing Code Engine snippet',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'ID of the snippet to update'
          ],
          'params' => [
            'type' => 'object',
            'description' => 'Parameters to update',
            'properties' => [
              'name' => [ 'type' => 'string' ],
              'code' => [ 'type' => 'string' ],
              'scope' => [
                'type' => 'string',
                'enum' => ['function', 'backend', 'frontend', 'scheduled', 'persistent']
              ],
              'description' => [ 'type' => 'string' ],
              'active' => [ 'type' => 'boolean' ],
              'priority' => [ 'type' => 'integer' ],
              'tags' => [
                'type' => 'array',
                'items' => [ 'type' => 'string' ]
              ],
              'endpoint' => [ 'type' => 'string' ],
              'method' => [
                'type' => 'string',
                'enum' => ['GET', 'POST']
              ],
              'target' => [
                'type' => 'string',
                'enum' => ['php', 'js']
              ],
              'args' => [
                'type' => 'array',
                'items' => [ 'type' => 'string' ]
              ],
              'argsData' => [
                'type' => 'object',
                'additionalProperties' => true
              ],
              'behavior' => [
                'type' => 'string',
                'enum' => ['dynamic', 'static']
              ],
              'intervalHours' => [ 'type' => 'integer' ],
              'intervalMinutes' => [ 'type' => 'integer' ]
            ]
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Delete Snippet
    $tools[] = [
      'name' => 'mwcode_delete_snippet',
      'description' => 'Delete a Code Engine snippet by its ID',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Delete Snippet by Name
    $tools[] = [
      'name' => 'mwcode_delete_snippet_by_name',
      'description' => 'Delete a Code Engine snippet by its name',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'The snippet name'
          ]
        ],
        'required' => ['name']
      ]
    ];
    
    // Activate Snippet
    $tools[] = [
      'name' => 'mwcode_activate_snippet',
      'description' => 'Activate a Code Engine snippet',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Deactivate Snippet
    $tools[] = [
      'name' => 'mwcode_deactivate_snippet',
      'description' => 'Deactivate a Code Engine snippet',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Get Snippets by Scope
    $tools[] = [
      'name' => 'mwcode_get_snippets_by_scope',
      'description' => 'Get Code Engine snippets filtered by scope',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'scope' => [
            'type' => 'string',
            'description' => 'The scope to filter by',
            'enum' => ['function', 'backend', 'frontend', 'scheduled', 'persistent']
          ],
          'filters' => [
            'type' => 'object',
            'description' => 'Additional filters',
            'properties' => [
              'active' => [
                'type' => 'boolean',
                'description' => 'Filter by active status'
              ],
              'tags' => [
                'type' => 'array',
                'description' => 'Filter by tags',
                'items' => [ 'type' => 'string' ]
              ]
            ]
          ]
        ],
        'required' => ['scope']
      ]
    ];
    
    // Get Active Snippets
    $tools[] = [
      'name' => 'mwcode_get_active_snippets',
      'description' => 'Get all active Code Engine snippets',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'scope' => [
            'type' => 'string',
            'description' => 'Optional scope filter',
            'enum' => ['function', 'backend', 'frontend', 'scheduled', 'persistent']
          ]
        ]
      ]
    ];
    
    // Snippet Exists
    $tools[] = [
      'name' => 'mwcode_snippet_exists',
      'description' => 'Check if a Code Engine snippet exists by ID',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'id' => [
            'type' => 'integer',
            'description' => 'The snippet ID'
          ]
        ],
        'required' => ['id']
      ]
    ];
    
    // Snippet Exists by Name
    $tools[] = [
      'name' => 'mwcode_snippet_exists_by_name',
      'description' => 'Check if a Code Engine snippet exists by name',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'name' => [
            'type' => 'string',
            'description' => 'The snippet name'
          ]
        ],
        'required' => ['name']
      ]
    ];
    
    // Validate Snippet Code
    $tools[] = [
      'name' => 'mwcode_validate_snippet_code',
      'description' => 'Validate snippet code syntax',
      'category' => 'Code Engine',
      'inputSchema' => [
        'type' => 'object',
        'properties' => [
          'code' => [
            'type' => 'string',
            'description' => 'The snippet code to validate'
          ],
          'target' => [
            'type' => 'string',
            'description' => 'The target language',
            'enum' => ['php', 'js'],
            'default' => 'php'
          ]
        ],
        'required' => ['code']
      ]
    ];
    
    return $tools;
  }
  
  public function handle_tool_execution( $result, $tool, $args, $id ) {
    // Only handle our tools
    if ( strpos( $tool, 'mwcode_' ) !== 0 ) {
      return $result;
    }
    
    // Ensure API is initialized
    if ( !$this->api ) {
      return [ 'success' => false, 'error' => 'Code Engine API not initialized' ];
    }
    
    try {
      switch ( $tool ) {
        case 'mwcode_get_snippet':
          $data = $this->api->getSnippet( $args['id'], $args['options'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_get_snippet_by_name':
          $data = $this->api->getSnippetByName( $args['name'], $args['options'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_get_snippets':
          $data = $this->api->getSnippets( $args['safe'] ?? true, $args['scope'] ?? null );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_execute_snippet':
          $data = $this->api->executeSnippet( $args['id'], $args['args'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_execute_snippet_by_name':
          $data = $this->api->executeSnippetByName( $args['name'], $args['args'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_create_snippet':
          $data = $this->api->createSnippet( 
            $args['name'], 
            $args['code'], 
            $args['scope'] ?? 'function', 
            $args['options'] ?? [] 
          );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_update_snippet':
          $data = $this->api->updateSnippet( $args['id'], $args['params'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_delete_snippet':
          $success = $this->api->deleteSnippet( $args['id'] );
          return [ 'success' => true, 'data' => [ 'deleted' => $success ] ];
          
        case 'mwcode_delete_snippet_by_name':
          $success = $this->api->deleteSnippetByName( $args['name'] );
          return [ 'success' => true, 'data' => [ 'deleted' => $success ] ];
          
        case 'mwcode_activate_snippet':
          $success = $this->api->activateSnippet( $args['id'] );
          return [ 'success' => true, 'data' => [ 'activated' => $success ] ];
          
        case 'mwcode_deactivate_snippet':
          $success = $this->api->deactivateSnippet( $args['id'] );
          return [ 'success' => true, 'data' => [ 'deactivated' => $success ] ];
          
        case 'mwcode_get_snippets_by_scope':
          $data = $this->api->getSnippetsByScope( $args['scope'], $args['filters'] ?? [] );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_get_active_snippets':
          $data = $this->api->getActiveSnippets( $args['scope'] ?? null );
          return [ 'success' => true, 'data' => $data ];
          
        case 'mwcode_snippet_exists':
          $exists = $this->api->snippetExists( $args['id'] );
          return [ 'success' => true, 'data' => [ 'exists' => $exists ] ];
          
        case 'mwcode_snippet_exists_by_name':
          $exists = $this->api->snippetExistsByName( $args['name'] );
          return [ 'success' => true, 'data' => [ 'exists' => $exists ] ];
          
        case 'mwcode_validate_snippet_code':
          $validation = $this->api->validateSnippetCode( $args['code'], $args['target'] ?? 'php' );
          return [ 'success' => true, 'data' => $validation ];
      }
    }
    catch ( Exception $e ) {
      return [ 'success' => false, 'error' => $e->getMessage() ];
    }
    
    return $result;
  }
}