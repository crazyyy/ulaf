<?php
class Meow_MWCODE_Modules_Cron {

    private $core;

    public function __construct( $core ) {
        add_filter( 'cron_schedules', [$this, 'add_cron_schedule'] );
        add_action( 'init', [$this, 'register_hooks'] );

        $this->core = $core;
    }

    public function add_cron_schedule( $schedules ) {
        $schedules['mwcode_once_daily'] = array(
            'interval' => 86400, // 24 hours in seconds
            'display'  => esc_html__( 'Code Engine Daily' ),
        );
        return $schedules;
    }

    public function register_hooks() {

        $snippet = new Meow_MWCODE_Modules_Snippet( $this->core );
        $snippets = $snippet->get_scheduled();

        foreach ( $snippets as $snippet ) {
            
            $hook = 'mwcode_execute_snippet_' . $snippet['snippetId'];

            // Check if the hook exists
            if ( !has_action( $hook ) ) {
                add_action( $hook, function() use ( $snippet ) {
                    $this->execute_snippet( $snippet['snippetId'] );
                });
            }

            // Schedule the event if it's not already scheduled
            if ( !wp_next_scheduled( $hook ) ) {
                
                $targetTime = mktime( $snippet['hours'], $snippet['minutes'] );
                $this->core->log( '🟢 Scheduling snippet: ' . $snippet['snippetId'] . ' at ' . date( 'H:i:s', $targetTime ) );

                wp_schedule_event( $targetTime, 'mwcode_once_daily', $hook );
            } else {
                //$this->core->log( '🟠 Snippet already scheduled: ' . $snippet['snippetId'] );
            }
        }
    }

    public function execute_snippet( $snippetId ) {
        $this->core->log( '🟢 Executing scheduled snippet: ' . $snippetId );
        $core = new Meow_MWCODE_Core();
        $core->run_non_fn_snippet( $snippetId );
    }
}
