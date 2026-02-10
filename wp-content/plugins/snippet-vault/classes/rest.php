<?php

class Meow_MWCODE_Rest
{
	private $admin = null;
	private $core = null;
	private $snippet = null;
	private $namespace = 'code-engine/v1';

	private $bearer_token = null;

	public function __construct( $core, $admin, $snippet ) {
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}
		$this->core = $core;
		$this->admin = $admin;
		$this->snippet = $snippet;
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		try {
			#region REST Settings
			register_rest_route( $this->namespace, '/settings/update', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_settings_update' )
			) );
			register_rest_route( $this->namespace, '/settings/list', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_settings_list' ),
			) );
			register_rest_route( $this->namespace, '/settings/reset', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_settings_reset' ),
			) );
			register_rest_route( $this->namespace, '/settings/functions', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_export_functions' ),
			) );
			register_rest_route( $this->namespace, '/settings/functions/replace', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_replace_functions' ),
			) );
			register_rest_route( $this->namespace, '/settings/functions/raw', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_functions_raw' ),
			) );
			register_rest_route( $this->namespace, '/settings/functions/raw', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_functions_raw_update' ),
			) );
			#endregion

			#region MISC

			register_rest_route( $this->namespace, '/server_clock', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_server_clock' ),
			) );

			#endregion

			#region REST AI Engine
			register_rest_route( $this->namespace, '/ai_read_comments', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_ai_read_comments' ),
			) );
			register_rest_route( $this->namespace, '/ai_query', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_ai_query' ),
			) );
			#endregion

			#region REST Run
			register_rest_route( $this->namespace, '/run/test', [
				'methods' => 'POST',
				'callback' => [ $this, 'rest_test' ],
				'permission_callback' => [ $this->core, 'can_access_settings' ],
			] );
	
			register_rest_route( $this->namespace, '/run/lint', [
				'methods' => 'POST',
				'callback' => [ $this, 'rest_lint' ],
				'permission_callback' => [ $this->core, 'can_access_settings' ],
			] );
			#endregion

			#region REST Snippets

			register_rest_route($this->namespace, '/snippets/list', [
				'methods' => 'POST',
				'callback' => [$this, 'rest_list'],
				'permission_callback' => [$this->core, 'can_access_settings'],
			]);

			register_rest_route( $this->namespace, '/snippets/stats', [
				'methods' => 'GET',
				'callback' => [$this, 'rest_stats'],
				'permission_callback' => [$this->core, 'can_access_settings'],
			]);

			register_rest_route($this->namespace, '/snippets/tags', [
				'methods' => 'POST',
				'callback' => [$this, 'rest_tags'],
				'permission_callback' => [$this->core, 'can_access_settings'],
			]);

			register_rest_route($this->namespace, '/snippets/import', [
				'methods' => 'GET',
				'callback' => [$this, 'rest_import'],
				'permission_callback' => [$this->core, 'can_access_settings'],
			]);

			register_rest_route($this->namespace, '/snippets/add', array(
				'methods' => 'POST',
				'callback' => array($this, 'rest_add'),
				'permission_callback' => array($this->core, 'can_access_settings')
			));

			register_rest_route($this->namespace, '/snippets/update', array(
				'methods' => 'POST',
				'callback' => array($this, 'rest_update'),
				'permission_callback' => array($this->core, 'can_access_settings')
			));

			register_rest_route($this->namespace, '/snippets/delete', array(
				'methods' => 'POST',
				'callback' => array($this, 'rest_delete'),
				'permission_callback' => array($this->core, 'can_access_settings')
			));


			register_rest_route($this->namespace, '/snippets/replace', array(
				'methods' => 'POST',
				'callback' => array($this, 'rest_replace'),
				'permission_callback' => array($this->core, 'can_access_settings')
			));


			// SNIPPETS ENDPOINTS
			$snippets = $this->snippet->select(
				null, // offset
				-1, // limit
				[
					[ 'accessor' => 'active', 'value' => 1 ],
					[ 'accessor' => 'endpoint', 'value' => 1 ],
				], // filter
				null, // sort
			);

			$public_api_enabled = $this->core->get_option( 'api_endpoint', false );
			if ( (int) $snippets['total'] > 0 && $public_api_enabled ) {

				$this->bearer_token = $this->core->get_option( 'api_token', null );
				if ( !empty( $this->bearer_token ) ) {
					add_filter( 'mwcode_allow_public_api', [ $this, 'auth_via_bearer_token' ], 10, 3 );
				}

				$data = $snippets['data'];

				foreach ( $data as $snippet ) {
					register_rest_route( $this->namespace, '/snippets-endpoint/' . $snippet['endpoint'], array(
						'methods' => $snippet['method'] ?: 'POST',
						'permission_callback' =>  function( $request ) {
							return $this->can_access_public_api( 'snippets-endpoint', $request );
						},
						'callback' => array( $this, 'rest_call_snippet' ),
						'args' => array(
							'id' => array( 'required' => true, 'default' => $snippet['id'] ),
						),
					) );

					//$this->core->log( "Registered REST API endpoint: /{$this->namespace}/snippets-endpoint/{$snippet['endpoint']} ({$snippet['method']})" );
				}
			}

			#endregion

			#region REST Logs
			register_rest_route( $this->namespace, '/get_logs', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_get_logs' )
			) );
			register_rest_route( $this->namespace, '/clear_logs', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'rest_clear_logs' )
			) );

			#endregion
		}
		catch (Exception $e) {
			var_dump($e);
		}
	}

	#region Auth

	public function auth_via_bearer_token( $allow, $feature, $extra ) {
		if ( !empty( $extra ) && !empty( $extra->get_header( 'Authorization' ) ) ) {    
			$token = $extra->get_header( 'Authorization' );
			$token = str_replace( 'Bearer ', '', $token );
			if ( $token === $this->bearer_token ) {
				$admin = $this->get_admin_user();
				wp_set_current_user( $admin->ID, $admin->user_login );
				return true;
			}
		}
		return $allow;
	}

	function can_access_public_api( $feature, $extra ) {
		$logged_in = is_user_logged_in();
		return apply_filters( 'mwcode_allow_public_api', $logged_in, $feature, $extra );
	}

	function get_admin_user() {
		$admin = get_users( [ 'role' => 'administrator' ] );
		if ( !empty( $admin ) ) {
			return $admin[0];
		}
		return null;
	}

	#endregion

	#region Logs

	function rest_get_logs() {
		$logs = $this->core->get_logs();
		return new WP_REST_Response( [ 'success' => true, 'data' => $logs ], 200 );
	}

	function rest_clear_logs() {
		$this->core->clear_logs();
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}


	#endregion

	#region Settings

	function rest_settings_list() {
		return new WP_REST_Response( [
			'success' => true,
			'options' => $this->core->get_all_options()
		], 200 );
	}


	function rest_settings_update( $request ) {
		try {
			$params = $request->get_json_params();
			$value = $params['options'];
			$options = $this->core->update_options( $value );
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not update options.", 'code-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_settings_reset() {
		try {
			$options = $this->core->reset_options();
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not reset options.", 'code-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function rest_export_functions() {
		$functions = $this->snippet->get_functions();
		return new WP_REST_Response([ 'success' => true, 'functions' => $functions ], 200);
	}

	function rest_replace_functions( $request ) {
		try {
			$params = $request->get_json_params();
			$functions = $params['functions'];

			$this->snippet->set_functions( $functions );

			return new WP_REST_Response([ 'success' => true, 'data' => $functions ], 200);
		} catch (Exception $e) {
			return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
		}
	
	}

	function rest_functions_raw() {
		$functions = $this->snippet->get_functions_raw();
		return new WP_REST_Response([ 'success' => true, 'functions' => $functions ], 200);
	}

	function rest_functions_raw_update( $request ) {
		try {
			$params = $request->get_json_params();
			$functions = $params['functions'];
	
			error_log( print_r( $functions, 1 ) );
	
			if ( empty( $functions ) ) {
				$functions = [];
			} else {
				// Decode the JSON string into a PHP array
				$functions = json_decode($functions, true);
	
				// Check if decoding was successful
				if (json_last_error() !== JSON_ERROR_NONE) {
					return new WP_REST_Response([ 'success' => false, 'message' => 'Invalid JSON format' ], 400);
				}
			}
	
			$this->snippet->set_functions_raw( $functions );
	
			return new WP_REST_Response([ 'success' => true, 'data' => $functions ], 200);
		} catch (Exception $e) {
			return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	#endregion


	#region MISC

	function rest_server_clock() {
		$now = current_datetime();
		$now = $now->format( 'H:i:s ( Y/m/d )' );

		return new WP_REST_Response( [ 'success' => true, 'time' => $now ], 200 );
	}

	#endregion

	#region AI Engine

	function rest_ai_query( $request ) {

		$query = $request->get_param('query');
		$code = $request->get_param('code');

		global $mwai;

		if ( is_null( $query ) || !isset( $query ) ) {
			return new WP_REST_Response([ 'success' => false, 'message' => 'No query provided.' ], 500 );
		}

		if ( is_null( $mwai ) || !isset( $mwai ) ) {
			return new WP_REST_Response([ 'success' => false, 'message' => 'AI Engine is not available.' ], 500 );
		}

		$prompt = "In your response, do not include any explanation outside the code if this is a functon, everything should be inside. Any information should be added as comments only, not example function calls. Do not use <?php tags or markdown ``` format, just provide the raw code. Based on the code provided, modify it as needed: $query\n\n$code";
		$response = $mwai->simpleTextQuery( $prompt );

		return new WP_REST_Response([ 'success' => true, 'code' => $response ], 200);
	}

	function rest_ai_read_comments( $request ) {
		$code = $request->get_param('code');
		global $mwai;

		if ( is_null( $code ) || !isset( $code ) ) {
			return new WP_REST_Response([ 'success' => false, 'message' => 'No code provided.' ], 500 );
		}

		if ( is_null( $mwai ) || !isset( $mwai ) ) {
			return new WP_REST_Response([ 'success' => false, 'message' => 'AI Engine is not available.' ], 500 );
		}

		$prompt = "Based on the comments in the code, modify the snippet so it accords with what the comments say. In your response, only include the modfied code with the comments removed and nothing else, no <?php tag, no markdown ``` format, just the raw code.\n\n$code";
		$prompt = apply_filters( 'mwcode_ai_suggestion_prompt', $prompt, $code );

		$response = $mwai->simpleTextQuery( $prompt );

		return new WP_REST_Response([ 'success' => true, 'code' => $response ], 200);

	}

	#endregion

	#region Snippets

	function rest_call_snippet( $request ) {
		try {
			$route = $request->get_route();
			$endpoint = basename( $route );

			$id = $request->get_param( 'id' );

			$snippet = $this->snippet->select_one( $id );
			if ( empty( $snippet ) || $snippet['endpoint'] !== $endpoint ) {
				return new WP_REST_Response( ['success' => false, 'message' => 'Snippet not found.' ], 404 );
			} elseif ( !$snippet['active'] ) {
				return new WP_REST_Response( ['success' => false, 'message' => 'Snippet is not active.' ], 403 );
			}

			// Get the arguments from the request
			$args = $request->get_param('args');
			if ( !empty( $args ) ) {
				$args = json_decode( $args, true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					throw new Exception('Failed to decode JSON from args: ' . json_last_error_msg() );
				}
			}

			// Get the body from the request
			$body = $request->get_body();
			if ( !empty( $body ) ) {
				$bodyDecoded = json_decode( $body, true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					throw new Exception( 'Failed to decode JSON from body: ' . json_last_error_msg() );
				}
				if ( !empty( $args ) && is_array( $args ) ) {
					$args = array_merge( $args, $bodyDecoded );
				} else {
					$args = $bodyDecoded;
				}
			}

			$output = $this->core->run_snippet( $id, $args );

			return new WP_REST_Response( $output, 200);
		} catch (ParseError $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
		} catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	public function rest_list( $request )
    {
        try {
            $params = $request->get_json_params();
            $page = $params['page'] ? intval($params['page']) : 1;
            $limit = $params['limit'] ?? 10;
            $offset = ($page - 1) * $limit;

            $filters = $params['filters'] ?? [];

            $filters = array_filter( $filters, function( $filter ) {
                return $filter['value'] !== 'all';
            });

            foreach ($filters as $key => $filter) {
                if ( $filter['accessor'] === 'scope' && $filter['value'] === 'disabled' ) {
                    $filters[] = ['accessor' => 'active', 'value' => 0];
                    unset( $filters[$key] );
                }
            }

			$sort = ['accessor' => 'id', 'by' => 'asc'];
			$sortByFunctionName = $params['sort']['accessor'] == 'functionName';
		
			if ( !$sortByFunctionName && !empty( $params['sort'] ) ) {
				$sort = $params['sort'];
			}
      

      if ( !empty( $filters['scope'] ) ) {
        //$filters['scope'] = [ 'accessor' => 'scope', 'value' => 'frontend' ];
      }

      $list = $this->snippet->select( $offset, $limit, $filters, $sort );

			//when listing let's reset any error message on the dashboard
			$this->core->update_option( 'fatal_error', '' );

			
			//TODO: Delete in future versions, we are converting the scopes since they have changed
			$to_update = [];
			foreach ( $list['data'] as $key => $snippet ) {
				if ( $snippet['scope'] === 'admin' ) {
					$list['data'][$key]['scope'] = 'backend';
					$to_update[] = $key;
				}
			
				if ( $snippet['scope'] === 'front' ) {
					$list['data'][$key]['scope'] = 'frontend';
					$to_update[] = $key;
				}
			
				if ( $snippet['scope'] === 'front,admin' ) {
					$list['data'][$key]['scope'] = 'persistent';
					$to_update[] = $key;
				}

				if ( $snippet['scope'] === 'library' ) {
					$list['data'][$key]['scope'] = 'persistent';
					$to_update[] = $key;
				}
			
				if ( $snippet['scope'] === 'interval' ) {
					$list['data'][$key]['scope'] = 'scheduled';
					$to_update[] = $key;
				}
			}
			
			foreach ( $to_update as $key ) {
				$snippet = $list['data'][$key];
				$this->core->log( "Updating snippet for new scopes: " . $snippet['id'] );
				$this->snippet->update( $snippet );
			}

			if ( $sortByFunctionName ) {
				// Manually sort by functionName
				usort( $list['data'], function( $a, $b ) use ( $params ) {
					$result = strcmp( $a['functionName'], $b['functionName'] );
					// If sorting by 'desc', invert the comparison result
					if ( $params['sort']['by'] === 'desc' ) {
						return -$result;
					}
					// Otherwise, return the comparison result for 'asc'	
					return $result;
				} );
			}

			//$this->core->log( "snippets" . print_r($list['data'], 1) );

            return new WP_REST_Response([ 'success' => true, 'total' => $list['total'], 'data' => $list['data'] ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	public function rest_stats(  )
    {
        try {
			$stats = $this->snippet->stats();
            return new WP_REST_Response([ 'success' => true, 'data' => $stats ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	public function rest_tags()
    {
        try {
            $list = $this->snippet->select_tags();
            return new WP_REST_Response([ 'success' => true, 'data' => $list ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	public function rest_import(  )
	{
		try {
			$snippet = $this->snippet->import(  );
			return new WP_REST_Response([ 'success' => true, 'total' => $snippet ], 200);
		} catch (Exception $e) {
			return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	public function rest_add( $request )
    {
        try {
            $params = $request->get_json_params();
            $name = $params['name'];
            $code = $params['code'];
            if ( empty( $name ) || empty( $code ) ) {
                throw new Exception( __( 'Name and code are required.', 'code-engine' ) );
            }
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

            return new WP_REST_Response([ 'success' => true, 'data' => $result, 'snippet' => $snippet ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	public function rest_update( $request )
    {
        try {
            $params = $request->get_json_params();
            $id = $params['id'];
            if ( empty( $id ) ) {
                throw new Exception( __( 'ID is required.', 'code-engine' ) );
            }

			$params['update'] = true;
            $this->snippet->validate( $params );
            $this->snippet->create_or_update_function_snippet( $params );
			$this->snippet->create_or_update_interval_snippet( $params );

			$params = $this->snippet->formatParamsForDatabase( $params );
            $result = $this->snippet->update( $params  );
            return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	
    public function rest_delete( $request )
    {
        try {
            $params = $request->get_json_params();
            $id = $params['id'];
			
            if ( empty( $id ) ) {
                throw new Exception( __( 'ID is required.', 'code-engine' ) );
            }

            $this->snippet->delete_function_snippet( $params );
			$this->snippet->delete_interval_snippet( $params );

            $result = $this->snippet->delete( $params );

            return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
        } catch (Exception $e) {
            return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }

	public function rest_replace( $request ){
		try {
			$params = $request->get_json_params();
			$snippets = $params['snippets'];

			// Delete all current snippets
			$this->snippet->delete_all();

			// Insert new snippets
			$result = [];
			foreach ( $snippets as $snippet ) {
				$this->snippet->validate( $snippet );
				$snippet = $this->snippet->formatParamsForDatabase( $snippet );
				$result[] = $this->snippet->insert( $snippet );
			}

			return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
		} catch (Exception $e) {
			return new WP_REST_Response( ['success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}
	

	#endregion

	#region Run

	/**
     * Execute a function snippet and return the result.
     *
     * It executes the provided function code with the given arguments and returns the result or any error encountered.
     *
     * @param WP_REST_Request $request The REST API request object.
     * @return WP_REST_Response The REST API response containing the result or error.
     */
    public function rest_test( $request ) {
        if ( is_array( $request ) ) {
            $params = $request;
        } else {
            $params = $request->get_json_params();
        }

        try {
            $output = $this->core->run_snippet( null, [], $params );

			$error = $output['error'] ?? null;
			unset( $output['error'] );
			
            return new WP_REST_Response( [ 'success' => true, 'return' => $output, 'error' => $error ], 200 );
        } catch ( Exception $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => $e->getMessage() ], 500 );
        }   
    }


	    /**
     * Lint the code.
     *
     * @param $request
     * @return WP_REST_Response
     */
    public function rest_lint( $request ) {
        try {
            $params = $request->get_json_params();
            $code = $params['code'];

            if ( empty( $code ) ) {
                throw new Exception( __( 'Code is required.', 'code-engine' ) );
            }

			$linting_new_code = empty( $params['id'] );
			$result = $this->core->parse_snippet( $code, $linting_new_code );		

            return new WP_REST_Response( [ 'success' => true, 'lint' => $result ], 200 );
        } catch ( ParseError $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => $e->getMessage() ], 500 );
        } catch ( Exception $e ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => $e->getMessage() ], 500 );
        }
    }
	#endregion
}
