<?php
class Meow_MWCODE_Modules_Snippet
{
    private $table = 'mwcode_snippets';
    private $option_functions = 'mwcode_functions';
    private $option_intervals = 'mwcode_intervals';

    private $wpdb = null;
    private $db_check = false;
    private $table_name = null;
    private $core = null;

    private $mwcode_db_snippet_version = '1.0';



    public function __construct( $core = null  )
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . $this->table;

        if ( $core == null ) {
            $this->core = new Meow_MWCODE_Core();
        }

        $this->core = $core;

        $this->check_db(  );
    }

    #region Interval Snippets

    public function get_scheduled(  )
    {

        $interval_snippets = get_option( $this->option_intervals, [] );

        return $interval_snippets;
    }

    public function create_or_update_interval_snippet( $params )
    {
        if ( $params['scope'] != 'scheduled' ) return;

        $interval_snippets = get_option( $this->option_intervals, [] );

        $snippetId = $params['id'];
        $snippet = [
            'snippetId' => $snippetId,
            'hours' => $params['intervalHours'],
            'minutes' => $params['intervalMinutes'],
        ];

        $snippetExists = false;
        foreach ( $interval_snippets as $key => $existingSnippet ) {
            if ( $existingSnippet['snippetId'] == $snippetId ) {
                $interval_snippets[$key] = $snippet;
                $snippetExists = true;
                break;
            }
        }

        if ( !$snippetExists ) {
            $interval_snippets[] = $snippet;
        }

        update_option( $this->option_intervals, $interval_snippets );
    }

    public function delete_interval_snippet( $params )
    {
        $interval_snippets = get_option( $this->option_intervals, [] );

        $snippetId = $params['id'];
        $updatedSnippets = [];

        foreach ( $interval_snippets as $existingSnippet ) {
            if ( $existingSnippet['snippetId'] != $snippetId ) {
                $updatedSnippets[] = $existingSnippet;
            } else {
                $hook = 'mwcode_execute_snippet_' . $existingSnippet['snippetId'];
                $timestamp = wp_next_scheduled( $hook );
                if ( $timestamp ) {
                    wp_unschedule_event( $timestamp, $hook );
                }
            }
        }

        update_option( $this->option_intervals, $updatedSnippets );
    }

    private function get_interval_snippets_data( &$snippets )
    {
        if ( isset( $snippets['id'] ) ) {
            $this->get_interval_snippet_data( $snippets );
        } else {
            foreach ( $snippets as &$snippet ) {
                $this->get_interval_snippet_data( $snippet );
            }
        }
    }

    private function get_interval_snippet_data( &$snippet )
    {
        if ( $snippet['scope'] != 'scheduled' ) {
            return;
        }

        $interval_snippets = get_option( $this->option_intervals, [] );

        $snippetId = $snippet['id'];
        $snippet['intervalHours'] = '';
        $snippet['intervalMinutes'] = '';

        foreach ( $interval_snippets as $interval_snippet ) {
            if ( $interval_snippet['snippetId'] == $snippetId ) {
                $snippet['intervalHours'] = $interval_snippet['hours'];
                $snippet['intervalMinutes'] = $interval_snippet['minutes'];
            }
        }
    }


    #endregion

    #region Functions Snippets

    private function get_function_snippet_data( &$snippet )
    {
        if ( $snippet['scope'] != 'function' ) {
            return;
        }

        $functions_snippet = get_option( $this->option_functions, [] );

        $snippetId = $snippet['id'];
        $snippet['functionName'] = '';
        $snippet['functionArgs'] = [];
        $snippet['functionArgsDict'] = [];
        $snippet['functionBehavior'] = '';
        $snippet['functionTarget'] = 'PHP';

        foreach ( $functions_snippet as $function_snippet ) {
            if ( $function_snippet['snippetId'] == $snippetId ) {

                $snippet['functionName'] = $function_snippet['name'];
                if ( !isset( $snippet['functionBehavior'] ) || empty( $snippet['functionBehavior'] ) ) {
                    $snippet['functionBehavior'] = 'dynamic';
                }
                else {
                    $snippet['functionBehavior'] = $function_snippet['behavior'];
                }
                if ( !isset( $snippet['functionTarget'] ) || empty( $snippet['functionTarget'] ) ) {
                    $snippet['functionTarget'] = 'PHP';
                }
                else {
                    $snippet['functionTarget'] = $function_snippet['target'];
                }
                foreach ( $function_snippet['args'] as $arg ) {
                    $snippet['functionArgs'][] = $arg['name'];
                    $snippet['functionArgsDict'][$arg['name']] = $arg;
                }
            }
        }
    }

    public function get_function_snippets_data( &$snippets )
    {
        if ( isset( $snippets['id'] ) ) {
            $this->get_function_snippet_data( $snippets );
        } else {
            foreach ( $snippets as &$snippet ) {
                $this->get_function_snippet_data( $snippet );
            }
        }
    }

    public function delete_function_snippet( $params )
    {
        $functions_snippet = get_option( $this->option_functions, [] );

        $snippetId = $params['id'];
        $updatedSnippets = [];

        foreach ( $functions_snippet as $existingSnippet ) {
            if ( $existingSnippet['snippetId'] != $snippetId ) {
                $updatedSnippets[] = $existingSnippet;
            }
        }

        update_option( $this->option_functions, $updatedSnippets );
    }

    private function sanitize_function_snippet( $snippet )
    {
        // Add logic to make sure the function snippet is valid
        // 1 -  Make sure the function always has a behavior (  is none set it to "dynamic"  )
        if ( !isset( $snippet['behavior'] ) || empty( $snippet['behavior'] ) ) {
            $snippet['behavior'] = 'dynamic';
        }


        return $snippet;
    }

    public function create_or_update_function_snippet( $params )
    {
        if ( $params['scope'] != 'function' ) return;

        $functions_snippet = get_option( $this->option_functions, [] );

        $snippetId = $params['id'];
        $snippet = [
            'snippetId' => $snippetId,
            'active' => $params['active'],
            'name' => $params['functionName'],
            'behavior' => $params['functionBehavior'],
            'desc' => $params['description'] ?? '',
            'target' => $params['functionTarget'] ?? 'PHP',
            'args' => [],
        ];

        foreach ( $params['functionArgs'] as $argName ) {
            if ( !empty( $argName ) ) {
                $argData = $params['functionArgsDict'][$argName];
                $snippet['args'][] = [
                    'name' => $argName,
                    'desc' => $argData['desc'],
                    'default' => $argData['default'],
                    'required' => empty( $argData['default'] ),
                    'type' => $argData['type'],
                ];
            } else {
                // TODO: The client-side sends an empty string when there is no argument.
                // This has to be fixed on the client-side.
                $this->core->log( '❌ ( Code Engine ) Empty argument name.' );
            }
        }

        $snippet = $this->sanitize_function_snippet( $snippet );

        $snippetExists = false;
        foreach ( $functions_snippet as $key => $existingSnippet ) {
            if ( $existingSnippet['snippetId'] == $snippetId ) {
                $functions_snippet[$key] = $snippet;
                $snippetExists = true;
                break;
            }
        }

        if ( !$snippetExists ) {
            $functions_snippet[] = $snippet;
        }

        update_option( $this->option_functions, $functions_snippet );
    }

    public function get_functions_raw(  )
    {
        return get_option( $this->option_functions, [] );
    }

    public function set_functions_raw( $functions )
    {
        return update_option( $this->option_functions, $functions );
    }

    public function get_functions(  )
    {
        $functions = get_option( $this->option_functions, array(  ) );

        //TODO: Delete this later, it's just to make sure all functions have the "active" key, as it's new.
        $needs_update = false;
        $filtered_functions = [];



        foreach ( $functions as $function ) {

            if ( !array_key_exists( 'active', $function ) ) {
                $snippet = $this->select_one( $function['snippetId'] );

                if ( !empty( $snippet ) ) {
                    $function['active'] = $snippet['active'];
                    $this->core->log( '✅ Function\'s related snippet found: ' . $function['snippetId'] . '. Active: ' . $function['active'] );
                } else {
                    $this->core->log( '❌ Function\'s related snippet not found: ' . $function['snippetId'] . '. Disabling function.' );
                    $function['active'] = false;
                }

                $needs_update = true;
            }

            $filtered_functions[] = $function;
        }

        if ( $needs_update ) {
            update_option( $this->option_functions, $filtered_functions );
        }

        $filtered_functions = array_filter( $filtered_functions, function ( $function ) {
            return $function['snippetId'] !== null && $function['active'];
        } );

        // Reindex the keys
        $filtered_functions = array_values( $filtered_functions );

        return $filtered_functions;
    }

    public function set_functions( $functions )
    {
        update_option( $this->option_functions, $functions );
    }


    public function get_function( $id, $options = [] )
    {
        $functions = $this->get_functions(  );

        foreach ( $functions as $function ) {
            if ( $function['snippetId'] == $id ) {

                if ( array_key_exists( 'php_ready_args', $options ) && $options['php_ready_args'] === false ) {
                    $function['args'] = array_map( function ( $arg ) {
                        $arg['name'] = ltrim( $arg['name'], '$' );
                        return $arg;
                    }, $function['args'] );
                }

                return $function;
            }
        }
        return null;
    }

    #endregion

    #region Utilities

    /**
     * Validate the parameters with consistency with other snippets.
     * For example, the endpoint should be unique.
     *
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function validate( $params )
    {
        $valide_scopes = ['backend', 'frontend', 'function', 'persistent', 'scheduled'];
        $errors = [];
        if ( isset( $params['endpoint'] ) && !empty( $params['endpoint'] ) ) {
            $query = $this->wpdb->prepare( 
                "SELECT * FROM $this->table_name where endpoint = %s",
                $params['endpoint']
             );
            if ( isset( $params['id'] ) && !empty( $params['id'] ) ) {
                $query .= $this->wpdb->prepare( " AND id <> %d", ( int ) $params['id'] );
            }
            $exists = ( int ) $this->wpdb->get_var( "SELECT COUNT( * ) FROM ( $query ) AS t" ) > 0;
            if ( $exists ) {
                $errors[] = __( 'The endpoint is already used.', 'code-engine' );
            }
        }


        if ( isset( $params['scope'] ) && !empty( $params['scope'] ) ) {

            $scopes = [
                "global" => "persistent",
                "front-end" => "frontend",
                "admin" => "backend",
            ];

            if ( array_key_exists( $params['scope'], $scopes ) ) {
                $params['scope'] = $scopes[$params['scope']];
            }


            if ( !empty( $params['tags'] ) ) {
                $tags = is_array( $params['tags'] ) ? $params['tags'] : explode( ',', $params['tags'] );
                // Add the current scope to tags and remove duplicates
                $tags[] = $params['scope'];
                $tags = array_unique( $tags );

                // Filter tags to include only valid scopes or the current scope
                $tags = array_filter( $tags, function ( $tag ) use ( $valide_scopes, $params ) {
                    return !in_array( $tag, $valide_scopes ) || $tag === $params['scope'];
                } );

                // Reset array keys and assign back to params
                $params['tags'] = array_values( $tags );
            } else {
                $params['tags'] = $params['scope'];
            }

            if ( !in_array( $params['scope'], $valide_scopes ) ) {
                $errors[] = sprintf( __( 'Invalid scope: %s', 'code-engine' ), $params['scope'] );
            }
        }

        // Make sure that the function name declared in the are unique
        if ( isset( $params['code'] ) && !empty( $params['code'] ) ) {
            $is_updating = array_key_exists( 'update', $params ) && $params['update'] === true;
            $function_data = $this->sanitize_and_check_functions( $params['code'], $is_updating );
            if ( !$function_data['is_valid'] ) {
                $errors = array_merge( $errors, $function_data['errors'] );
            }
        }

        if ( isset( $params['scope'] ) && $params['scope'] === 'function' ) {
            if ( !isset( $params['functionName'] ) || empty( $params['functionName'] ) ) {
                $errors[] = __( 'Function name is required.', 'code-engine' );
            }
        }

        if ( isset( $params['functionError'] ) && !empty( $params['functionError'] ) ) {
            $errors[] = __( 'An error was not resolved. (' . $params['functionError'] . ')', 'code-engine' );
        }

        if ( !empty( $errors ) ) {
            throw new Exception( "❌ Your code could not be saved because of the following issue( s ): \n\n" . implode( ', ', $errors ) );
        }

        return $params;
    }

    public function sanitize_and_check_functions( $code, $is_updating = false )
    {
        $errors = [];
        $function_names = [];
        $attributes = [];

        // Regular expression to match function declarations
        $pattern = '/function\s+(\w+)\s*\(/';

        // Find all function declarations
        preg_match_all( $pattern, $code, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );

        if ( !empty( $matches ) ) {
            foreach ( $matches as $match ) {
                $function_name = $match[1][0];
                $start_pos = $match[0][1];

                // Calculate line numbers and positions
                $lines_before = substr_count( substr( $code, 0, $start_pos ), "\n" );
                $line_start = strrpos( substr( $code, 0, $start_pos ), "\n" ) + 1;
                $line_end = strpos( $code, "\n", $start_pos );
                if ( $line_end === false ) $line_end = strlen( $code );

                $attr = [
                    'startLine' => $lines_before + 1,
                    'startTokenPos' => $start_pos - $line_start,
                    'startFilePos' => $start_pos,
                    'endLine' => $lines_before + 1,
                    'endTokenPos' => $line_end - $line_start,
                    'endFilePos' => $line_end
                ];

                // Check if function already exists in PHP
                if ( function_exists( $function_name ) && !$is_updating ) {
                    $errors[] = [
                        'message' => "Function '$function_name' is already declared in PHP.",
                        'attributes' => $attr
                    ];
                }
                // Check if function is already declared in this code block
                elseif ( in_array( $function_name, $function_names ) ) {
                    $errors[] = [
                        'message' => "Function '$function_name' is declared multiple times in the provided code.",
                        'attributes' => $attr
                    ];
                } else {
                    $function_names[] = $function_name;
                }

                $attributes[] = $attr;
            }
        }

        return [
            'is_valid' => empty( $errors ),
            'errors' => $errors,
            'function_names' => $function_names,
            'attributes' => $attributes
        ];
    }

    /**
     * Format parameters for saving in the database
     *
     * @param array $params
     * @return array
     */
    public function formatParamsForDatabase( $params )
    {
        // Gather the scope tags into the tags
        $tags = null;
        if ( isset( $params['tags'] ) ) {
            if ( is_array( $params['tags'] ) ) {
                $tags = array_map( function ( $tag ) {
                    return trim( $tag );
                }, $params['tags'] );
            } else {
                $tags = array_map( function ( $tag ) {
                    return trim( $tag );
                }, explode( ',', $params['tags'] ) );
            }
        }
        if ( isset( $params['scope'] ) ) {
            $tags = array_merge( $tags, is_array( $params['scope'] ) ? $params['scope'] : explode( ',', $params['scope'] ) );
        }
        if ( count( $tags ) > 0 ) {
            $tags = array_filter( $tags, function ( $tag ) {
                return $tag !== '';
            } );
        }
        $params['tags'] = $tags ? implode( ',', $tags ) : '';
        $params['code'] = $this->sanitize_code( $params['code'] );
        return $params;
    }

    /**
     * Format parameters for the front-end
     *
     * @param array $params
     * @return array
     */
    private function formatParamsForFront( $params )
    {
        // Separate the scope tags from the tags
        $scopes = ['backend', 'frontend', 'function', 'persistent', 'scheduled'];

        if ( isset( $params['tags'] ) && !empty( $params['tags'] ) ) {

            $tags = array_map( function ( $tag ) use ( $scopes ) {
                if ( in_array( $tag, $scopes ) ) {
                    return null;
                }
                return trim( $tag );
            }, explode( ',', $params['tags'] ) );
            $params['tags'] = array_filter( $tags, function ( $tag ) {
                return $tag !== null;
            } );
        }
        return $params;
    }

    public function stats()
    {
        $scopes = ['function', 'scheduled', 'global'];
        $globalScopes = ['backend', 'frontend', 'persistent'];
    
        $stats = [
            'all' => 0,
            'disabled' => $this->wpdb->get_var( "SELECT COUNT( * ) FROM $this->table_name WHERE active = 0" ),
        ];
    
        foreach ( $scopes as $scope ) {
            if ( $scope === 'global' ) {
                $globalScopeQuery = implode( "', '", array_map( 'esc_sql', $globalScopes ) );
                $stats[$scope] = $this->wpdb->get_var( "SELECT COUNT( * ) FROM $this->table_name WHERE scope IN ('$globalScopeQuery')" );
            } else {
                $stats[$scope] = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT( * ) FROM $this->table_name WHERE scope = %s", $scope ) );
            }
            $stats['all'] += $stats[$scope];
        }
    
        return $stats;
    }    

    public function import(  )
    {
        $table = $this->wpdb->prefix . 'snippets';
        $snippets = $this->wpdb->get_results( "SELECT * FROM $table", ARRAY_A );

        if ( !$snippets ) {
            return 0;
        }

        // Disable (  set active = 0  ) all the snippets in the old table
        $this->wpdb->update( $table, ['active' => 0], ['active' => 1] );

        foreach ( $snippets as $snippet ) {
            $snippet['id'] = null; // Reset the ID so it will be inserted as a new snippet.
            $snippet = $this->validate( $snippet );
            $this->insert( $this->formatParamsForDatabase( $snippet ) );
        }

        return count( $snippets );
    }



    private function sanitize_code( $code )
    {
        $code = preg_replace( '/<\?php/', '', $code, 1 );
        $code = ltrim( $code );
        return $code;
    }

    #endregion

    #region Snippet CRUD

    public function select( $offset, $limit, $filters, $sort )
    {
        if ( !$this->check_db() ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }
    
        $list = [];
        $offset = !empty( $offset ) ? intval( $offset ) : 0;
        $limit = !empty( $limit ) ? intval( $limit ) : 10;
        $filters = !empty( $filters ) ? $filters : [];
        $sort = !empty( $sort ) ? $sort : ['accessor' => 'updated', 'by' => 'desc'];
        $query = "SELECT * FROM $this->table_name";
    
        // Filters
        if ( is_array( $filters ) && count( $filters ) > 0 ) {
            $where = [];
    
            $freshFilters = [];
            // Little trick that allows searching by tags using the snippet accessor
            // And to have a global scope that will search for backend, frontend, and persistent
            foreach ( $filters as $filter ) {
                if ( $filter['accessor'] === 'snippet' ) {
                    //$freshFilters['accessor'] = 'tags';
                    $freshFilters[] = [ 'accessor' => 'tags', 'value' => $filter['value'] ];
                }
                else if ( $filter['accessor'] === 'scope' && $filter['value'] === 'global' ) {
                    $freshFilters[] = [ 'accessor' => 'scope', 'value' => ['backend', 'frontend', 'persistent'] ];
                }
                else {
                    $freshFilters[] = $filter;
                }
            }
            $filters = $freshFilters;
    
            foreach ( $filters as $filter ) {
                if ( $filter['accessor'] === 'tags' ) {
                    $value = ( array )$filter['value'];
    
                    if ( count( $value ) === 0 ) {
                        continue;
                    }
                    $where_unit = [];
                    foreach ( $value as $tag ) {
                        if ( strpos( $tag, ',' ) !== false ) {
                            $tags = explode( ',', $tag );
                            $where_combination_unit = [];
                            foreach ( $tags as $t ) {
                                $where_combination_unit[] = "FIND_IN_SET( '{$t}', tags )";
                            }
                            $where_unit[] = '( ' . implode( ' AND ', $where_combination_unit ) . ' )';
                            continue;
                        }
                        $where_unit[] = "FIND_IN_SET( '{$tag}', tags )";
                    }
                    $where[] = '( ' . implode( ' OR ', $where_unit ) . ' )';
                } elseif ( $filter['accessor'] === 'active' ) {
                    $value = esc_sql( $filter['value'] );
                    $where[] = $this->wpdb->prepare( "active = %d", $value );
                } elseif ( $filter['accessor'] === 'endpoint' ) {
                    $where[] = boolval( $filter['value'] ) ? "endpoint <> ''" : "endpoint = ''";
                } elseif ( $filter['accessor'] === 'scope' ) {
                    if ( is_array( $filter['value'] ) ) {
                        $scopes = array_map( function( $scope ) {
                            return esc_sql( $scope );
                        }, $filter['value'] );
                        $where[] = "scope IN ('" . implode( "', '", $scopes ) . "')";
                    } else {
                        $value = esc_sql( $filter['value'] );
                        $where[] = $this->wpdb->prepare( "scope = %s", $value );
                    }
                }
            }
            if ( count( $where ) > 0 ) {
                $query .= " WHERE " . implode( " AND ", $where );
            }
        }
    
        // Count based on this query
        $list['total'] = $this->wpdb->get_var( "SELECT COUNT( * ) FROM ( $query ) AS t" );
    
        // Order by
        $query .= " ORDER BY " . esc_sql( $sort['accessor'] ) . " " . esc_sql( $sort['by'] );
    
        // Limits
        if ( $limit > 0 ) {
            $query .= " LIMIT $offset, $limit";
        }
    
        $list['data'] = array_map( function ( $snippet ) {
            return $this->formatParamsForFront( $snippet );
        }, $this->wpdb->get_results( $query, ARRAY_A ) );
    
        $this->get_function_snippets_data( $list['data'] );
        $this->get_interval_snippets_data( $list['data'] );
    
        return $list;
    }    

    public function select_tags(  )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $tags = [];
        $query = "SELECT tags FROM $this->table_name";
        $result = $this->wpdb->get_results( $query, ARRAY_A );
        foreach ( $result as $row ) {
            $tags = array_merge( $tags, explode( ',', $row['tags'] ) );
        }
        // Remove the scope tags: admin, front, once.
        $tags = array_diff( $tags, ['backend', 'frontend', 'function', 'persistent', 'scheduled'] );
        return array_values( array_unique( $tags ) );
    }

    public function select_one( $id, $options = [] )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $query = "SELECT * FROM $this->table_name WHERE id = %s";
        if ( isset( $options['active'] ) ) {
            $query .= " AND active = " . ( $options['active'] ? '1' : '0' );
        }

        return $this->formatParamsForFront( 
            $this->wpdb->get_row( 
                $this->wpdb->prepare( $query, ( string ) $id ),
                ARRAY_A
             )
         );
    }

    public function insert( $insert_data )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $data = [];
        $update_columns = array_keys( MWCODE_SNIPPET_COLUMNS );

        foreach ( $update_columns as $column ) {
            if ( isset( $insert_data[$column] ) ) {
                $data[$column] = $insert_data[$column];
            } else {
                unset( $data[$column] ); // Remove it if it's empty, so it uses the default db value.
            }
        }

        $data['created'] = date( 'Y-m-d H:i:s' );
        $data['updated'] = date( 'Y-m-d H:i:s' );

        $this->wpdb->insert( $this->table_name, $data );
        $id = $this->wpdb->insert_id;
        if ( !$id ) {
            throw new Exception( __( 'Could not insert the snippet.', 'code-engine' ) );
        }
        return $id;
    }

    public function update( $update_data )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $data = [];
        $update_columns = array_keys( MWCODE_SNIPPET_COLUMNS );
        foreach ( $update_columns as $column ) {
            if ( isset( $update_data[$column] ) ) {
                $data[$column] = $update_data[$column];
            }
        }
        if ( count( $data ) === 0 ) {
            throw new Exception( __( 'No data to update.', 'code-engine' ) );
        }
        $data['updated'] = date( 'Y-m-d H:i:s' );
        $result = $this->wpdb->update( $this->table_name, $data, ['id' => $update_data['id']] );
        if ( $result === false ) {
            throw new Exception( __( 'Could not insert the snippet.', 'code-engine' ) );
        }
        return $result;
    }

    public function force_disable( $id )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $result = $this->wpdb->update( $this->table_name, ['active' => 0], ['id' => $id] );
        if ( $result === false ) {
            throw new Exception( __( 'Could not disable the snippet.', 'code-engine' ) );
        }
        return $result;
    }

    public function delete( $delete_data )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $result = $this->wpdb->delete( $this->table_name, ['id' => $delete_data['id']] );
        if ( $result === false ) {
            throw new Exception( __( 'Could not delete the snippet.', 'code-engine' ) );
        }
        return $result;
    }

    public function delete_all(  )
    {
        if ( !$this->check_db(  ) ) {
            throw new Exception( __( 'Could not access the database.', 'code-engine' ) );
        }

        $result = $this->wpdb->query( "TRUNCATE TABLE $this->table_name" );
        if ( $result === false ) {
            throw new Exception( __( 'Could not delete all snippets.', 'code-engine' ) );
        }
        return $result;
    }

    #endregion

    #region Database

    function create_db(  )
    {
        $this->core->log( '💾 ( Code Engine ) Creating Table: ' . $this->table_name );
        try {
            $charset_collate = $this->wpdb->get_charset_collate(  );

            $column_definitions = array_map( function ( $column_name, $column_definition ) {
                return "$column_name $column_definition";
            }, array_keys( MWCODE_SNIPPET_COLUMNS ), MWCODE_SNIPPET_COLUMNS );
            $column_definitions = implode( ",\n", $column_definitions ) . ', PRIMARY KEY  ( id )';

            $sql = "CREATE TABLE $this->table_name ( $column_definitions ) $charset_collate;";
            $this->core->log( '💾 ( Code Engine ) Create table request: ' . $sql );
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        } catch ( Exception $e ) {
            $this->core->log( '💾 ( Code Engine ) Error creating Table: ' . $e->getMessage(  ) );
        }

        add_option( 'mwcode_db_snippet_version', $this->mwcode_db_snippet_version );
    }

    function check_db(  )
    {
        if ( $this->does_table_exist( $this->table_name ) ) {
            $this->check_columns(  );
            $this->db_check = true;
        } else {
            $this->create_db(  );
            $this->core->log( '💾 ( Code Engine ) Table created, checking if it was successful.' );
            $this->db_check = $this->does_table_exist( $this->table_name );
        }

        return $this->db_check;
    }

    private function check_columns(  )
    {
        $db_version = get_option( 'mwcode_db_snippet_version' );
        if ( $db_version == $this->mwcode_db_snippet_version ) {
            return;
        }

        $this->core->log( '💾 ( Code Engine ) Database version is ' . $db_version . ', upgrading to ' . $this->mwcode_db_snippet_version . '.' );

        global $wpdb;
        $table_name = $this->table_name;
        $charset = $wpdb->get_charset_collate(  );
        $desired_columns = MWCODE_SNIPPET_COLUMNS;
        $existing_columns = $wpdb->get_results( "DESCRIBE $table_name", ARRAY_A );

        // Handle column removals
        $columns_to_remove = array_diff( array_column( $existing_columns, 'Field' ), array_keys( $desired_columns ) );
        if ( !empty( $columns_to_remove ) ) {
            $remove_queries = array_map( function ( $column_name ) use ( $table_name ) {
                return "DROP COLUMN $column_name";
            }, $columns_to_remove );
            $remove_query = "ALTER TABLE $table_name " . implode( ', ', $remove_queries );
            $wpdb->query( $remove_query );
        }

        // Handle column additions and updates
        $alter_queries = array(  );
        foreach ( $desired_columns as $column_name => $column_definition ) {
            $existing_column = array_filter( $existing_columns, function ( $column ) use ( $column_name ) {
                return $column['Field'] === $column_name;
            } );

            if ( empty( $existing_column ) ) {
                $alter_queries[] = "ADD COLUMN $column_name $column_definition";
            } else {
                $existing_column = array_shift( $existing_column );
                $existing_column_definition = $existing_column['Type'];
                if ( $existing_column_definition !== $column_definition ) {
                    $alter_queries[] = "MODIFY COLUMN $column_name $column_definition";
                }
            }
        }

        if ( !empty( $alter_queries ) ) {
            $alter_query = "ALTER TABLE $table_name " . implode( ', ', $alter_queries );
            $wpdb->query( $alter_query );
        }

        update_option( 'mwcode_db_snippet_version', $this->mwcode_db_snippet_version );
    }

    private function does_table_exist( $table_name )
    {

        $found = false;
        $table_name = strtolower( $table_name );

        // Try the fast way first
        try {
            $query = "SHOW TABLES LIKE '{$table_name}'";
            $result = strtolower( $this->wpdb->get_var( $query ) );

            $found = $result === $table_name;
        } catch ( Exception $e ) {
            $this->core->log( '💾 ( Code Engine ) Database Check 1 Error: ' . $e->getMessage(  ) );
        }

        // If not found, try the slow way
        if ( !$found ) {
            try {
                $query = "SHOW TABLES";
                $tables = $this->wpdb->get_results( $query, ARRAY_N );
                foreach ( $tables as $table ) {
                    $result = strtolower( $table[0] );
                    if ( $result === $table_name ) {
                        $found = true;
                        break;
                    }
                }
            } catch ( Exception $e ) {
                $this->core->log( '💾 ( Code Engine ) Database Check 2 Error: ' . $e->getMessage(  ) );
            }
        }

        if ( !$found ) {
            $this->core->log( '💾 ( Code Engine ) Database table doesn\'t seem to exist.' );
        }

        return $found;
    }

    #endregion
}
