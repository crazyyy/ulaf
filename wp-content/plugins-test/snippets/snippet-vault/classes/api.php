<?php

class Meow_MWCODE_API
{
  private $core = null;
  private $snippet = null;


  public function __construct( $core, $snippet ) {
		$this->core = $core;
    $this->snippet = $snippet;
	}

  /**
   * Get a function by its ID.
   * 
   * The options are used to filter the functions:
   * - 'php_ready_args' (bool): If false, the arguments will not be formatted for PHP. (no $ before the names).
   *
   * @param $id
   * @param array $options
   *
   * @return mixed
   */
  public function get_function( $id, $options = [] ) {
    $snippet = $this->snippet->get_function( $id, $options );
    return $snippet;
  }

  /**
   * Get all functions.
   * 
   * @return mixed 
   * 
   */
  public function get_functions( $safe = true ) {
    $snippets = $this->snippet->get_functions();

    if ( $safe ) {

      $snippets = array_filter( $snippets, function( $snippet ) {
        $name = $snippet['name'];
        if ( !preg_match( '/^[a-zA-Z0-9_-]{1,64}$/', $name ) ) {
          return false;
        }

        return true;
      } );
      
    }

    return $snippets;
  }

  /**
   * Execute a function by its ID.
   * The arguments should be an associative array with the argument names as keys.
   * Example: [ "$city" => " 'Tokyo' ", "$date" => "1999" ]
   *
   * @param $id
   * @param $args
   *
   * @return mixed
   */
  public function execute_function( $id, $args ) {
    $output = $this->core->run_snippet( $id, $args );
    return $output;
  }
}
