<?php

class MeowPro_MWAI_MCP_Database {
  private $core = null;

  #region Initialize
  public function __construct( $core ) {
    $this->core = $core;
    add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
  }

  public function rest_api_init() {
    add_filter( 'mwai_mcp_tools', [ $this, 'register_rest_tools' ] );
    add_filter( 'mwai_mcp_callback', [ $this, 'handle_call' ], 10, 4 );
  }
  #endregion

  #region Tools
  private function tools(): array {
    global $wpdb;
    return [
      'wp_db_query' => [
        'name' => 'wp_db_query',
        'description' => "Execute a SQL query on the WordPress database and return the results. The table prefix is '{$wpdb->prefix}'.",
        'inputSchema' => [
          'type' => 'object',
          'properties' => [
            'query' => [
              'type' => 'string',
              'description' => 'The SQL query to execute.',
            ],
          ],
          'required' => [ 'query' ],
        ],
        'category' => 'AI Engine (Database)',
        'annotations' => [
          'readOnlyHint' => false,
          'destructiveHint' => true,
          'openWorldHint' => false,
        ],
      ],
    ];
  }
  #endregion

  #region Register
  public function register_rest_tools( array $prev ): array {
    $tools = $this->tools();
    $merged = array_merge( $prev, array_values( $tools ) );
    return $merged;
  }
  #endregion

  #region Callback
  public function handle_call( $prev, string $tool, array $args, ?int $id ) {
    if ( !empty( $prev ) || !isset( $this->tools()[ $tool ] ) ) {
      return $prev;
    }
    if ( !current_user_can( 'administrator' ) ) {
      wp_set_current_user( 1 );
    }
    return $this->dispatch( $tool, $args );
  }
  #endregion

  #region Dispatcher
  private function dispatch( string $tool, array $a ) {
    global $wpdb;

    switch ( $tool ) {
      case 'wp_db_query':
        $query = $a['query'] ?? '';
        if ( empty( $query ) ) {
          throw new Exception( 'Query is required.' );
        }
        $trimmed = ltrim( $query );
        $is_read = preg_match( '/^(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $trimmed );
        if ( $is_read ) {
          $results = $wpdb->get_results( $query, ARRAY_A );
          if ( $wpdb->last_error ) {
            throw new Exception( $wpdb->last_error );
          }
          $count = count( $results );
          return [
            'rows' => $count,
            'data' => $results,
          ];
        }
        else {
          $affected = $wpdb->query( $query );
          if ( $wpdb->last_error ) {
            throw new Exception( $wpdb->last_error );
          }
          return [
            'affected_rows' => $affected,
          ];
        }

        // no break
      default:
        throw new Exception( 'Unknown tool' );
    }
  }
  #endregion
}
