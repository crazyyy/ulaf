<?php

/**
 * Admin UI for Auto Fixture Generator for SportsPress.
 *
 * @package AFGSP
 */
declare (strict_types = 1);
namespace AFGSP;

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Class AFGSP_Admin
 *
 * Renders a submenu page under SportsPress and handles form submission to
 * generate fixtures based on selected options.
 */
class AFGSP_Admin {
    /**
     * Singleton instance.
     *
     * @var AFGSP_Admin|null
     */
    private static $instance = null;

    /**
     * Hook suffix for the submenu page, used to enqueue assets conditionally.
     *
     * @var string|null
     */
    private $page_hook = null;

    /**
     * Get instance (singleton).
     *
     * @return AFGSP_Admin
     */
    public static function get_instance() : AFGSP_Admin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'admin_menu', array($this, 'register_menu'), 20 );
        add_action( 'admin_init', array($this, 'maybe_handle_submission') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_assets') );
        add_action( 'wp_ajax_afgsp_get_teams', array($this, 'ajax_get_teams') );
        add_action( 'wp_ajax_afgsp_start_generation', array($this, 'ajax_start_generation') );
        add_action( 'wp_ajax_afgsp_process_generation', array($this, 'ajax_process_generation') );
    }

    /**
     * Register submenu under SportsPress.
     *
     * Adds the Auto Fixture Generator submenu page under the SportsPress Events menu.
     * Sets the page hook for conditional asset loading.
     *
     * @return void
     */
    public function register_menu() : void {
        // Place under the SportsPress Events (sp_event) post type menu.
        $this->page_hook = add_submenu_page(
            'edit.php?post_type=sp_event',
            esc_html__( 'Auto Fixture Generator', 'auto-fixture-generator-for-sportspress' ),
            esc_html__( 'Auto Fixture Generator', 'auto-fixture-generator-for-sportspress' ),
            'manage_options',
            'afgsp-generator',
            array($this, 'render_page')
        );
    }

    /**
     * Enqueue minimal admin JS for dynamic options rendering.
     *
     * Loads JavaScript assets only on the Auto Fixture Generator admin page.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     * @return void
     */
    public function enqueue_assets( string $hook_suffix ) : void {
        if ( empty( $this->page_hook ) || $hook_suffix !== $this->page_hook ) {
            return;
        }
        // Enqueue CSS for visual gameweek builder.
        wp_enqueue_style(
            'afgsp-admin',
            AFGSP_PLUGIN_URL . 'assets/admin.css',
            array(),
            AFGSP_VERSION
        );
        wp_enqueue_script(
            'afgsp-admin',
            AFGSP_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            AFGSP_VERSION,
            true
        );
        // Conditionally load debug assets when debug mode is available.
        $debug_mode_available = AFGSP_Debug::is_debug_mode_available();
        if ( $debug_mode_available ) {
            wp_enqueue_style(
                'afgsp-admin-debug',
                AFGSP_PLUGIN_URL . 'assets/admin-debug.css',
                array('afgsp-admin'),
                AFGSP_VERSION
            );
            wp_enqueue_script(
                'afgsp-admin-debug',
                AFGSP_PLUGIN_URL . 'assets/admin-debug.js',
                array('jquery', 'afgsp-admin'),
                AFGSP_VERSION,
                true
            );
        }
        wp_localize_script( 'afgsp-admin', 'AFGSP_ADMIN', array(
            'optionsByAlgorithm' => $this->get_options_schema_map_for_js(),
            'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
            'nonce'              => wp_create_nonce( 'afgsp_admin' ),
            'isPremium'          => (bool) afgsp_fs()->can_use_premium_code__premium_only(),
            'progressText'       => esc_html__( 'Generating fixtures…', 'auto-fixture-generator-for-sportspress' ),
            'debugModeAvailable' => $debug_mode_available,
            'dryRunProgressText' => esc_html__( 'Dry run in progress…', 'auto-fixture-generator-for-sportspress' ),
        ) );
    }

    /**
     * Start an asynchronous generation job.
     *
     * Creates a transient with queued fixtures and scheduling state.
     *
     * @return void
     */
    public function ajax_start_generation() : void {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'auto-fixture-generator-for-sportspress' ),
            ), 403 );
        }
        check_ajax_referer( 'afgsp_admin', 'nonce' );
        $league_id = ( isset( $_POST['league'] ) ? absint( wp_unslash( $_POST['league'] ) ) : 0 );
        $season_id = ( isset( $_POST['season'] ) ? absint( wp_unslash( $_POST['season'] ) ) : 0 );
        $algorithm = ( isset( $_POST['algorithm'] ) ? sanitize_text_field( wp_unslash( $_POST['algorithm'] ) ) : '' );
        $raw_opts = ( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['options'] ) ) : array() );
        $raw_teams = ( isset( $_POST['teams'] ) && is_array( $_POST['teams'] ) ? array_map( 'absint', wp_unslash( $_POST['teams'] ) ) : array() );
        $raw_schedule = ( isset( $_POST['schedule'] ) && is_array( $_POST['schedule'] ) ? map_deep( wp_unslash( $_POST['schedule'] ), 'sanitize_text_field' ) : array() );
        $dry_run = isset( $_POST['dry_run'] ) && ('1' === $_POST['dry_run'] || true === $_POST['dry_run']);
        $algorithms = AFGSP_Registry::get_algorithms();
        if ( $league_id <= 0 || $season_id <= 0 || empty( $algorithm ) || !isset( $algorithms[$algorithm] ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid request.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        $all_teams = afgsp_get_teams_for_league_and_season( $league_id, $season_id );
        $team_ids = array_map( static function ( $post ) {
            return (int) $post->ID;
        }, $all_teams );
        if ( count( $team_ids ) < 2 ) {
            wp_send_json_error( array(
                'message' => __( 'Please select at least two teams.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        // Sanitize options and enforce free limits.
        $schema = ( isset( $algorithms[$algorithm]['options_schema'] ) ? (array) $algorithms[$algorithm]['options_schema'] : array() );
        $schema['shuffle_teams'] = array(
            'type' => 'bool',
        );
        $schema['no_consecutive_away'] = array(
            'type' => 'bool',
        );
        // Add post-processing options to schema so they are preserved during sanitization.
        $schema['create_calendar'] = array(
            'type' => 'bool',
        );
        $schema['calendar_name'] = array(
            'type' => 'string',
        );
        $schema['create_table'] = array(
            'type' => 'bool',
        );
        $schema['table_name'] = array(
            'type' => 'string',
        );
        $options = afgsp_sanitize_options_against_schema( $raw_opts, $schema );
        $options['shuffle_teams'] = false;
        $options['no_consecutive_away'] = false;
        $time_slots = ( isset( $raw_schedule['time_slots'] ) && is_array( $raw_schedule['time_slots'] ) ? array_values( array_filter( array_map( static function ( $v ) {
            return sanitize_text_field( (string) $v );
        }, (array) $raw_schedule['time_slots'] ) ) ) : array() );
        if ( !afgsp_fs()->can_use_premium_code__premium_only() && count( $time_slots ) > 1 ) {
            $time_slots = array_slice( $time_slots, 0, 1 );
        }
        $blocked_dates = ( isset( $raw_schedule['blocked_dates'] ) && is_array( $raw_schedule['blocked_dates'] ) ? array_values( array_filter( array_map( static function ( $v ) {
            return sanitize_text_field( (string) $v );
        }, (array) $raw_schedule['blocked_dates'] ) ) ) : array() );
        $blocked_dates = array();
        // Process events mode and events per slot (premium feature).
        $events_mode = ( isset( $raw_schedule['events_mode'] ) ? sanitize_text_field( (string) $raw_schedule['events_mode'] ) : 'auto' );
        $events_per_slot = ( isset( $raw_schedule['events_per_slot'] ) && is_array( $raw_schedule['events_per_slot'] ) ? array_map( 'absint', $raw_schedule['events_per_slot'] ) : array() );
        $events_mode = 'auto';
        $events_per_slot = array();
        $schedule = array(
            'start_date'      => ( isset( $raw_schedule['start_date'] ) ? sanitize_text_field( (string) $raw_schedule['start_date'] ) : '' ),
            'days'            => ( isset( $raw_schedule['days'] ) && is_array( $raw_schedule['days'] ) ? array_values( array_unique( array_map( 'intval', (array) $raw_schedule['days'] ) ) ) : array(6, 0) ),
            'time_slots'      => $time_slots,
            'blocked_dates'   => $blocked_dates,
            'gameweek_name'   => ( isset( $raw_schedule['gameweek_name'] ) ? sanitize_text_field( (string) $raw_schedule['gameweek_name'] ) : 'Gameweek %No%' ),
            'events_mode'     => $events_mode,
            'events_per_slot' => $events_per_slot,
        );
        if ( empty( $schedule['start_date'] ) ) {
            wp_send_json_error( array(
                'message' => __( 'Please select a scheduling start date.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        // Build fixtures once up-front.
        $callable = AFGSP_Registry::get_algorithm_callable( $algorithm );
        if ( !$callable ) {
            wp_send_json_error( array(
                'message' => __( 'Selected algorithm could not be loaded.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        if ( !empty( $options['shuffle_teams'] ) && function_exists( 'afgsp_fs' ) && afgsp_fs()->can_use_premium_code__premium_only() ) {
            shuffle( $team_ids );
        }
        try {
            $raw_fixtures = call_user_func( $callable, $team_ids, array(
                'schedule' => $schedule,
            ) + $options );
            if ( !is_array( $raw_fixtures ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Algorithm returned an invalid response.', 'auto-fixture-generator-for-sportspress' ),
                ), 400 );
            }
        } catch ( \Throwable $e ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'Algorithm error: %s', 'auto-fixture-generator-for-sportspress' ), $e->getMessage() ),
            ), 500 );
        }
        // Apply scheduling using centralized prepare_fixtures method.
        // This ensures both normal mode and dry-run mode use identical scheduling logic.
        $prepared_fixtures = AFGSP_Generator::prepare_fixtures(
            $raw_fixtures,
            $schedule,
            count( $team_ids ),
            $algorithm
        );
        $total = count( $prepared_fixtures );
        if ( $total <= 0 ) {
            wp_send_json_error( array(
                'message' => __( 'No fixtures to generate.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        $job_id = 'afgsp_' . wp_generate_password( 12, false, false );
        $teams_count = count( $team_ids );
        // Calculate actual matches per round: floor(n/2) accounts for bye games being excluded.
        $round_size = max( 1, (int) floor( $teams_count / 2 ) );
        $state = array(
            'league_id'           => $league_id,
            'season_id'           => $season_id,
            'algorithm'           => $algorithm,
            'fixtures'            => array_values( $prepared_fixtures ),
            'next_index'          => 0,
            'total'               => $total,
            'created'             => 0,
            'duplicates'          => 0,
            'gameweeks'           => array(),
            'schedule'            => $schedule,
            'round_size'          => max( 1, $round_size ),
            'create_calendar'     => !empty( $options['create_calendar'] ),
            'calendar_name'       => ( isset( $options['calendar_name'] ) ? (string) $options['calendar_name'] : '' ),
            'create_table'        => !empty( $options['create_table'] ),
            'table_name'          => ( isset( $options['table_name'] ) ? (string) $options['table_name'] : '' ),
            'shuffle_teams'       => !empty( $options['shuffle_teams'] ),
            'no_consecutive_away' => !empty( $options['no_consecutive_away'] ),
            'algorithm_options'   => $options,
            'messages'            => array(),
            'created_entities'    => false,
            'dry_run'             => $dry_run,
            'team_ids'            => $team_ids,
            'dry_run_fixtures'    => array(),
        );
        set_transient( $job_id, $state, 60 * 30 );
        // 30 minutes.
        wp_send_json_success( array(
            'job_id' => $job_id,
            'total'  => $total,
        ) );
    }

    /**
     * Process next batch (one matchday) for a generation job.
     *
     * @return void
     */
    public function ajax_process_generation() : void {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'auto-fixture-generator-for-sportspress' ),
            ), 403 );
        }
        check_ajax_referer( 'afgsp_admin', 'nonce' );
        $job_id = ( isset( $_POST['job_id'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['job_id'] ) ) : '' );
        $state = ( $job_id ? get_transient( $job_id ) : false );
        if ( !is_array( $state ) ) {
            wp_send_json_error( array(
                'message' => __( 'Job not found or expired.', 'auto-fixture-generator-for-sportspress' ),
            ), 400 );
        }
        // Extract state - fixtures are now pre-scheduled with datetime and gameweek.
        $fixtures = ( isset( $state['fixtures'] ) ? (array) $state['fixtures'] : array() );
        $next_index = (int) ($state['next_index'] ?? 0);
        $total = (int) ($state['total'] ?? 0);
        $created = (int) ($state['created'] ?? 0);
        $duplicates = (int) ($state['duplicates'] ?? 0);
        $gameweeks = ( isset( $state['gameweeks'] ) && is_array( $state['gameweeks'] ) ? (array) $state['gameweeks'] : array() );
        $league_id = (int) ($state['league_id'] ?? 0);
        $season_id = (int) ($state['season_id'] ?? 0);
        $messages = ( isset( $state['messages'] ) && is_array( $state['messages'] ) ? (array) $state['messages'] : array() );
        $created_entities = !empty( $state['created_entities'] );
        $create_calendar = !empty( $state['create_calendar'] );
        $calendar_name = ( isset( $state['calendar_name'] ) ? (string) $state['calendar_name'] : '' );
        $create_table = !empty( $state['create_table'] );
        $table_name = ( isset( $state['table_name'] ) ? (string) $state['table_name'] : '' );
        // Dry run mode.
        $dry_run = !empty( $state['dry_run'] );
        $team_ids = ( isset( $state['team_ids'] ) && is_array( $state['team_ids'] ) ? $state['team_ids'] : array() );
        $dry_run_fixtures = ( isset( $state['dry_run_fixtures'] ) && is_array( $state['dry_run_fixtures'] ) ? $state['dry_run_fixtures'] : array() );
        $schedule = ( isset( $state['schedule'] ) && is_array( $state['schedule'] ) ? $state['schedule'] : array() );
        $algorithm = ( isset( $state['algorithm'] ) ? (string) $state['algorithm'] : '' );
        // If already complete, ensure we still return any completion messages.
        if ( $next_index >= $total ) {
            if ( $dry_run ) {
                // Log the dry run results.
                AFGSP_Debug::log_dry_run( array(
                    'league_id'           => $league_id,
                    'season_id'           => $season_id,
                    'algorithm'           => $algorithm,
                    'algorithm_options'   => ( isset( $state['algorithm_options'] ) ? (array) $state['algorithm_options'] : array() ),
                    'schedule'            => $schedule,
                    'team_ids'            => $team_ids,
                    'fixtures'            => $dry_run_fixtures,
                    'shuffle_teams'       => !empty( $state['shuffle_teams'] ),
                    'no_consecutive_away' => !empty( $state['no_consecutive_away'] ),
                    'create_calendar'     => !empty( $state['create_calendar'] ),
                    'calendar_name'       => ( isset( $state['calendar_name'] ) ? (string) $state['calendar_name'] : '' ),
                    'create_table'        => !empty( $state['create_table'] ),
                    'table_name'          => ( isset( $state['table_name'] ) ? (string) $state['table_name'] : '' ),
                ) );
                $messages[] = __( 'Dry run completed. Check debug.log for details.', 'auto-fixture-generator-for-sportspress' );
            } elseif ( !$created_entities ) {
                list( $messages, $created_entities, $entity_details ) = $this->afgsp_maybe_create_entities_and_messages(
                    $league_id,
                    $season_id,
                    $fixtures,
                    $calendar_name,
                    $table_name,
                    $create_calendar,
                    $create_table,
                    $messages
                );
            }
            delete_transient( $job_id );
            wp_send_json_success( array(
                'done'       => true,
                'created'    => $created,
                'duplicates' => $duplicates,
                'gameweeks'  => count( $gameweeks ),
                'processed'  => $total,
                'messages'   => array_values( $messages ),
                'dry_run'    => $dry_run,
            ) );
        }
        // Process a batch of pre-scheduled fixtures.
        // Fixtures now have home_id, away_id, extra_meta (with datetime and sp_day), and gameweek already assigned.
        $batch_size = (int) ($state['round_size'] ?? 4);
        $processed_this_batch = 0;
        // Track algorithm to determine if duplicate checking should be skipped.
        $skip_duplicate_check = 'fixed-week-season' === $algorithm;
        while ( $next_index < $total && $processed_this_batch < $batch_size ) {
            $fixture = $fixtures[$next_index];
            $home_id = (int) ($fixture['home_id'] ?? 0);
            $away_id = (int) ($fixture['away_id'] ?? 0);
            $extra_meta = ( isset( $fixture['extra_meta'] ) && is_array( $fixture['extra_meta'] ) ? $fixture['extra_meta'] : array() );
            if ( $home_id <= 0 || $away_id <= 0 || $home_id === $away_id ) {
                $next_index++;
                continue;
            }
            if ( $dry_run ) {
                // Dry run mode: collect fixture data instead of creating events.
                $dry_run_fixtures[] = array(
                    'home_id'    => $home_id,
                    'away_id'    => $away_id,
                    'extra_meta' => $extra_meta,
                );
                $created++;
                // Track unique gameweeks.
                if ( isset( $extra_meta['sp_day'] ) && '' !== $extra_meta['sp_day'] ) {
                    $gameweek_key = sanitize_text_field( (string) $extra_meta['sp_day'] );
                    if ( !isset( $gameweeks[$gameweek_key] ) ) {
                        $gameweeks[$gameweek_key] = true;
                    }
                }
            } else {
                // Normal mode: create events in the database.
                $created_post = \AFGSP\afgsp_create_event(
                    $home_id,
                    $away_id,
                    $league_id,
                    $season_id,
                    $extra_meta,
                    $skip_duplicate_check
                );
                if ( !is_wp_error( $created_post ) ) {
                    $created++;
                    // Track unique gameweeks.
                    if ( isset( $extra_meta['sp_day'] ) && '' !== $extra_meta['sp_day'] ) {
                        $gameweek_key = sanitize_text_field( (string) $extra_meta['sp_day'] );
                        if ( !isset( $gameweeks[$gameweek_key] ) ) {
                            $gameweeks[$gameweek_key] = true;
                        }
                    }
                } else {
                    // Track duplicates (WP_Error typically indicates duplicate).
                    if ( 'afgsp_duplicate' === $created_post->get_error_code() ) {
                        $duplicates++;
                    }
                }
            }
            $next_index++;
            $processed_this_batch++;
        }
        $state['next_index'] = $next_index;
        $state['created'] = $created;
        $state['duplicates'] = $duplicates;
        $state['gameweeks'] = $gameweeks;
        $state['messages'] = $messages;
        $state['created_entities'] = $created_entities;
        $state['dry_run_fixtures'] = $dry_run_fixtures;
        set_transient( $job_id, $state, 60 * 30 );
        $done = $next_index >= $total;
        if ( $done ) {
            if ( $dry_run ) {
                // Log the dry run results.
                AFGSP_Debug::log_dry_run( array(
                    'league_id'           => $league_id,
                    'season_id'           => $season_id,
                    'algorithm'           => $algorithm,
                    'algorithm_options'   => ( isset( $state['algorithm_options'] ) ? (array) $state['algorithm_options'] : array() ),
                    'schedule'            => $schedule,
                    'team_ids'            => $team_ids,
                    'fixtures'            => $dry_run_fixtures,
                    'shuffle_teams'       => !empty( $state['shuffle_teams'] ),
                    'no_consecutive_away' => !empty( $state['no_consecutive_away'] ),
                    'create_calendar'     => !empty( $state['create_calendar'] ),
                    'calendar_name'       => ( isset( $state['calendar_name'] ) ? (string) $state['calendar_name'] : '' ),
                    'create_table'        => !empty( $state['create_table'] ),
                    'table_name'          => ( isset( $state['table_name'] ) ? (string) $state['table_name'] : '' ),
                ) );
                $messages[] = __( 'Dry run completed. Check debug.log for details.', 'auto-fixture-generator-for-sportspress' );
            } else {
                list( $messages, $created_entities, $entity_details ) = $this->afgsp_maybe_create_entities_and_messages(
                    $league_id,
                    $season_id,
                    $fixtures,
                    $calendar_name,
                    $table_name,
                    $create_calendar,
                    $create_table,
                    $messages
                );
            }
            $state['messages'] = $messages;
            $state['created_entities'] = $created_entities;
            set_transient( $job_id, $state, 60 * 30 );
            delete_transient( $job_id );
        }
        wp_send_json_success( array(
            'done'       => $done,
            'created'    => $created,
            'duplicates' => $duplicates,
            'gameweeks'  => count( $gameweeks ),
            'processed'  => $next_index,
            'total'      => $total,
            'messages'   => ( $done ? array_values( $messages ) : array() ),
            'dry_run'    => $dry_run,
        ) );
    }

    /**
     * Create calendar/table after async completion and produce messages.
     *
     * @param int    $league_id League term ID.
     * @param int    $season_id Season term ID.
     * @param array  $fixtures  Fixtures list to derive team IDs.
     * @param string $calendar_name Calendar post title to create when $create_calendar is true.
     * @param string $table_name    League table post title to create when $create_table is true.
     * @param bool   $create_calendar Whether to create a calendar entity after generation.
     * @param bool   $create_table    Whether to create a league table entity after generation.
     * @param array  $messages        Accumulated admin notice messages to append to.
     * @return array{0:array,1:bool,2:array} Updated messages list, created flag, and entity details.
     */
    private function afgsp_maybe_create_entities_and_messages(
        int $league_id,
        int $season_id,
        array $fixtures,
        string $calendar_name,
        string $table_name,
        bool $create_calendar,
        bool $create_table,
        array $messages
    ) : array {
        $created_entities = false;
        $entity_details = array(
            'calendar_id'   => 0,
            'table_id'      => 0,
            'calendar_name' => '',
            'table_name'    => '',
        );
        $league_term = get_term( $league_id, 'sp_league' );
        $season_term = get_term( $season_id, 'sp_season' );
        $league_name = ( $league_term && !is_wp_error( $league_term ) ? (string) $league_term->name : '' );
        $season_name = ( $season_term && !is_wp_error( $season_term ) ? (string) $season_term->name : '' );
        $default_entity_name = trim( trim( $league_name ) . ' ' . trim( $season_name ) );
        if ( true === $create_calendar ) {
            $name = ( '' !== $calendar_name ? $calendar_name : $default_entity_name );
            $calendar_id = wp_insert_post( array(
                'post_type'   => 'sp_calendar',
                'post_title'  => ( $name ? $name : __( 'Calendar', 'auto-fixture-generator-for-sportspress' ) ),
                'post_status' => 'publish',
            ), true );
            if ( is_wp_error( $calendar_id ) ) {
                /* translators: %s: error message from wp_insert_post. */
                $messages[] = sprintf( __( 'Failed to create calendar: %s', 'auto-fixture-generator-for-sportspress' ), $calendar_id->get_error_message() );
            } else {
                wp_set_post_terms(
                    (int) $calendar_id,
                    array($league_id),
                    'sp_league',
                    false
                );
                wp_set_post_terms(
                    (int) $calendar_id,
                    array($season_id),
                    'sp_season',
                    false
                );
                update_post_meta( (int) $calendar_id, 'sp_format', 'list' );
                /* translators: %s: calendar title. */
                $messages[] = sprintf( __( 'Calendar created: %s', 'auto-fixture-generator-for-sportspress' ), get_the_title( (int) $calendar_id ) );
                $created_entities = true;
                $entity_details['calendar_id'] = (int) $calendar_id;
                $entity_details['calendar_name'] = get_the_title( (int) $calendar_id );
            }
        }
        if ( true === $create_table ) {
            $name = ( '' !== $table_name ? $table_name : $default_entity_name );
            $table_id = wp_insert_post( array(
                'post_type'   => 'sp_table',
                'post_title'  => ( $name ? $name : __( 'League Table', 'auto-fixture-generator-for-sportspress' ) ),
                'post_status' => 'publish',
            ), true );
            if ( is_wp_error( $table_id ) ) {
                /* translators: %s: error message from wp_insert_post. */
                $messages[] = sprintf( __( 'Failed to create league table: %s', 'auto-fixture-generator-for-sportspress' ), $table_id->get_error_message() );
            } else {
                wp_set_post_terms(
                    (int) $table_id,
                    array($league_id),
                    'sp_league',
                    false
                );
                wp_set_post_terms(
                    (int) $table_id,
                    array($season_id),
                    'sp_season',
                    false
                );
                // Derive unique team IDs from fixtures.
                $team_ids_set = array();
                foreach ( $fixtures as $fx ) {
                    $h = ( isset( $fx['home'] ) ? (int) $fx['home'] : 0 );
                    $a = ( isset( $fx['away'] ) ? (int) $fx['away'] : 0 );
                    if ( $h > 0 ) {
                        $team_ids_set[$h] = true;
                    }
                    if ( $a > 0 ) {
                        $team_ids_set[$a] = true;
                    }
                }
                $team_ids = array_map( 'intval', array_keys( $team_ids_set ) );
                foreach ( $team_ids as $team_id ) {
                    add_post_meta( (int) $table_id, 'sp_team', (int) $team_id );
                }
                update_post_meta( (int) $table_id, 'sp_select', 'manual' );
                // Add by default all columns to the table.
                $columns_args = array(
                    'post_type'      => 'sp_column',
                    'numberposts'    => -1,
                    'posts_per_page' => -1,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                    'status'         => 'publish',
                );
                $columns = new \WP_Query($columns_args);
                $sp_import_columns = array();
                if ( $columns->have_posts() ) {
                    while ( $columns->have_posts() ) {
                        $columns->the_post();
                        $sp_import_columns[] = get_post()->post_name;
                    }
                    wp_reset_postdata();
                }
                update_post_meta( (int) $table_id, 'sp_columns', $sp_import_columns );
                /* translators: %s: league table title. */
                $messages[] = sprintf( __( 'League table created: %s', 'auto-fixture-generator-for-sportspress' ), get_the_title( (int) $table_id ) );
                $created_entities = true;
                $entity_details['table_id'] = (int) $table_id;
                $entity_details['table_name'] = get_the_title( (int) $table_id );
            }
        }
        return array($messages, $created_entities, $entity_details);
    }

    /**
     * Calculate the end date of a gameweek based on the start date and selected days.
     *
     * @param int   $start_date Timestamp of the gameweek start date.
     * @param array $selected_days Array of selected day numbers (0=Sunday, 1=Monday, etc.).
     * @return int Timestamp of the gameweek end date.
     */
    private static function get_gameweek_end_date( int $start_date, array $selected_days ) : int {
        if ( empty( $selected_days ) ) {
            return $start_date;
        }
        // Sort days numerically.
        sort( $selected_days );
        // Find the first and last selected days.
        $first_day = reset( $selected_days );
        $last_day = end( $selected_days );
        // Get the current weekday of the start date.
        $current_weekday = (int) gmdate( 'w', $start_date );
        // For the rolling gameweek concept, we need to find the next occurrence.
        // Of the last selected day after the start date.
        // Look ahead up to 7 days to find the last selected day.
        $days_to_add = 0;
        $test_date = $start_date;
        for ($i = 0; $i < 7; $i++) {
            $test_weekday = (int) gmdate( 'w', $test_date );
            if ( $test_weekday === $last_day ) {
                $days_to_add = $i;
                break;
            }
            $test_date = strtotime( '+1 day', $test_date );
        }
        // If we didn't find the last day within 7 days, it means we need to wrap around.
        if ( 0 === $days_to_add ) {
            // Calculate days from current weekday to the last selected day.
            $days_to_add = ($last_day - $current_weekday + 7) % 7;
        }
        return strtotime( '+' . $days_to_add . ' days', $start_date );
    }

    /**
     * AJAX callback to return teams for a given league and season.
     *
     * Handles AJAX requests to fetch teams associated with a specific league and season.
     * Returns JSON response with team data for dynamic team selection in the admin interface.
     *
     * @return void
     */
    public function ajax_get_teams() : void {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'auto-fixture-generator-for-sportspress' ),
            ), 403 );
        }
        check_ajax_referer( 'afgsp_admin', 'nonce' );
        // Nonce verification is done above. Sanitize GET parameters with absint for IDs.
        $league_id = ( isset( $_GET['league'] ) ? absint( $_GET['league'] ) : 0 );
        $season_id = ( isset( $_GET['season'] ) ? absint( $_GET['season'] ) : 0 );
        if ( $league_id <= 0 || $season_id <= 0 ) {
            wp_send_json_success( array(
                'teams' => array(),
            ) );
        }
        $posts = afgsp_get_teams_for_league_and_season( $league_id, $season_id );
        $teams = array_map( static function ( $post ) {
            return array(
                'id'    => (int) $post->ID,
                'title' => (string) get_the_title( $post ),
            );
        }, $posts );
        wp_send_json_success( array(
            'teams' => $teams,
        ) );
    }

    /**
     * Build algorithm options schema for JS.
     *
     * @return array<string,array>
     */
    private function get_options_schema_map_for_js() : array {
        $schemas = array();
        $algo_map = AFGSP_Registry::get_algorithms();
        foreach ( $algo_map as $slug => $def ) {
            // Extract algorithm-specific options from the original schema.
            $algo_options = ( isset( $def['options_schema'] ) && is_array( $def['options_schema'] ) ? $def['options_schema'] : array() );
            // Build schema in the correct display order.
            $schema = array();
            // ========================================
            // CATEGORY 1: SCHEDULING CONSTRAINTS
            // ========================================
            $schema['_category_scheduling'] = array(
                'type'  => 'category',
                'label' => __( 'Scheduling Constraints', 'auto-fixture-generator-for-sportspress' ),
            );
            $schema['shuffle_teams'] = array(
                'type'        => 'bool',
                'label'       => __( 'Shuffle Teams', 'auto-fixture-generator-for-sportspress' ),
                'description' => __( 'Randomize team order before generating fixtures for varied pairing sequences.', 'auto-fixture-generator-for-sportspress' ),
                'disabled'    => !afgsp_fs()->can_use_premium_code__premium_only(),
                'premiumNote' => ( !afgsp_fs()->can_use_premium_code__premium_only() ? __( 'This is a premium feature.', 'auto-fixture-generator-for-sportspress' ) : '' ),
                'category'    => 'scheduling',
            );
            $schema['no_consecutive_away'] = array(
                'type'        => 'bool',
                'label'       => __( 'No 2x away', 'auto-fixture-generator-for-sportspress' ),
                'description' => __( 'Try to avoid scheduling the same team away two gameweeks in a row.', 'auto-fixture-generator-for-sportspress' ),
                'disabled'    => !afgsp_fs()->can_use_premium_code__premium_only(),
                'premiumNote' => ( !afgsp_fs()->can_use_premium_code__premium_only() ? __( 'This is a premium feature.', 'auto-fixture-generator-for-sportspress' ) : '' ),
                'category'    => 'scheduling',
            );
            // ========================================
            // CATEGORY 2: POST PROCESSING ACTIONS
            // ========================================
            $schema['_category_postprocessing'] = array(
                'type'  => 'category',
                'label' => __( 'Post Processing Actions', 'auto-fixture-generator-for-sportspress' ),
            );
            $schema['create_calendar'] = array(
                'type'     => 'bool',
                'label'    => __( 'Create calendar', 'auto-fixture-generator-for-sportspress' ),
                'category' => 'postprocessing',
            );
            $schema['calendar_name'] = array(
                'type'     => 'string',
                'label'    => __( 'Calendar name', 'auto-fixture-generator-for-sportspress' ),
                'category' => 'postprocessing',
            );
            $schema['create_table'] = array(
                'type'     => 'bool',
                'label'    => __( 'Create league table', 'auto-fixture-generator-for-sportspress' ),
                'category' => 'postprocessing',
            );
            $schema['table_name'] = array(
                'type'     => 'string',
                'label'    => __( 'Table name', 'auto-fixture-generator-for-sportspress' ),
                'category' => 'postprocessing',
            );
            // ========================================
            // CATEGORY 3: ALGORITHM SPECIFIC OPTIONS
            // ========================================
            // Only add category header and options if algorithm has specific options.
            if ( !empty( $algo_options ) ) {
                $schema['_category_algorithm'] = array(
                    'type'  => 'category',
                    'label' => __( 'Algorithm Specific Options', 'auto-fixture-generator-for-sportspress' ),
                );
                // Add algorithm-specific options after the header.
                foreach ( $algo_options as $key => $opt ) {
                    $schema[$key] = $opt;
                }
            }
            // Add rounds information for event count calculation.
            $schema['_rounds'] = ( isset( $def['rounds'] ) ? (int) $def['rounds'] : 1 );
            $schemas[$slug] = $schema;
        }
        return $schemas;
    }

    /**
     * Display premium algorithm labels for free version.
     *
     * Returns a hardcoded array of premium algorithm labels that are available
     * in the premium version but not in the free version.
     *
     * @return array<int,string> Sorted list of unique premium algorithm labels.
     */
    private function get_premium_algorithm_labels_for_message() : array {
        return array(__( 'Double Round Robin', 'auto-fixture-generator-for-sportspress' ), __( 'Fixed Week Season', 'auto-fixture-generator-for-sportspress' ));
    }

    /**
     * Handle form submission securely.
     *
     * Processes the fixture generation form submission with proper validation,
     * sanitization, and security checks.
     * Generates fixtures using the selected algorithm.
     *
     * This function serves as a FALLBACK for when JavaScript/AJAX is disabled.
     * The primary generation method is now via AJAX (ajax_start_generation + ajax_process_generation).
     *
     * @return void
     */
    public function maybe_handle_submission() : void {
        if ( !isset( $_POST['afgsp_action'] ) || 'generate' !== sanitize_text_field( wp_unslash( $_POST['afgsp_action'] ) ) ) {
            return;
        }
        // Skip synchronous processing if AJAX is available.
        // This prevents duplicate fixture creation when both form POST and AJAX run.
        // The synchronous handler only acts as a fallback when JavaScript is disabled.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }
        // Check if request came from JavaScript-enabled browser with AJAX capability.
        // If AFGSP_ADMIN object is available in JS, the form will be handled via AJAX.
        if ( !empty( $_SERVER['HTTP_REFERER'] ) && !empty( $_POST['afgsp_nonce'] ) ) {
            // Most modern browsers will handle this via AJAX, so skip synchronous processing.
            // This check prevents the race condition where both handlers try to create fixtures.
            error_log( '[AFGSP-SYNC] Skipping synchronous form handler - AJAX will process this request' );
            return;
        }
        error_log( '[AFGSP-SYNC] Processing form synchronously (AJAX fallback)' );
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        check_admin_referer( 'afgsp_generate_fixtures', 'afgsp_nonce' );
        $league_id = ( isset( $_POST['league'] ) ? absint( wp_unslash( $_POST['league'] ) ) : 0 );
        $season_id = ( isset( $_POST['season'] ) ? absint( wp_unslash( $_POST['season'] ) ) : 0 );
        $algorithm = ( isset( $_POST['algorithm'] ) ? sanitize_text_field( wp_unslash( $_POST['algorithm'] ) ) : '' );
        $raw_opts = ( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['options'] ) ) : array() );
        $raw_teams = ( isset( $_POST['teams'] ) && is_array( $_POST['teams'] ) ? array_map( 'absint', wp_unslash( $_POST['teams'] ) ) : array() );
        $all_teams = afgsp_get_teams_for_league_and_season( $league_id, $season_id );
        $team_ids = array_map( static function ( $post ) {
            return (int) $post->ID;
        }, $all_teams );
        $algorithms = AFGSP_Registry::get_algorithms();
        $schema = ( isset( $algorithms[$algorithm]['options_schema'] ) ? (array) $algorithms[$algorithm]['options_schema'] : array() );
        // Ensure global options exist in schema for sanitization.
        $schema['shuffle_teams'] = array(
            'type' => 'bool',
        );
        $schema['no_consecutive_away'] = array(
            'type' => 'bool',
        );
        // Add post-processing options to schema so they are preserved during sanitization.
        $schema['create_calendar'] = array(
            'type' => 'bool',
        );
        $schema['calendar_name'] = array(
            'type' => 'string',
        );
        $schema['create_table'] = array(
            'type' => 'bool',
        );
        $schema['table_name'] = array(
            'type' => 'string',
        );
        $options = afgsp_sanitize_options_against_schema( $raw_opts, $schema );
        $options['shuffle_teams'] = false;
        $options['no_consecutive_away'] = false;
        // Normalize scheduling fields (outside algorithm options).
        $raw_schedule = ( isset( $_POST['schedule'] ) && is_array( $_POST['schedule'] ) ? map_deep( wp_unslash( $_POST['schedule'] ), 'sanitize_text_field' ) : array() );
        $time_slots = ( isset( $raw_schedule['time_slots'] ) && is_array( $raw_schedule['time_slots'] ) ? array_values( array_filter( array_map( static function ( $v ) {
            return sanitize_text_field( (string) $v );
        }, (array) $raw_schedule['time_slots'] ) ) ) : array() );
        // For free users, limit to only one time slot.
        if ( !afgsp_fs()->can_use_premium_code__premium_only() && count( $time_slots ) > 1 ) {
            $time_slots = array_slice( $time_slots, 0, 1 );
        }
        $blocked_dates = ( isset( $raw_schedule['blocked_dates'] ) && is_array( $raw_schedule['blocked_dates'] ) ? array_values( array_filter( array_map( static function ( $v ) {
            return sanitize_text_field( (string) $v );
        }, (array) $raw_schedule['blocked_dates'] ) ) ) : array() );
        $blocked_dates = array();
        $schedule = array(
            'start_date'    => ( isset( $raw_schedule['start_date'] ) ? sanitize_text_field( (string) $raw_schedule['start_date'] ) : '' ),
            'days'          => ( isset( $raw_schedule['days'] ) && is_array( $raw_schedule['days'] ) ? array_values( array_unique( array_map( 'intval', (array) $raw_schedule['days'] ) ) ) : array(6, 0) ),
            'time_slots'    => $time_slots,
            'blocked_dates' => $blocked_dates,
            'gameweek_name' => ( isset( $raw_schedule['gameweek_name'] ) ? sanitize_text_field( (string) $raw_schedule['gameweek_name'] ) : 'Gameweek %No%' ),
        );
        $errors = array();
        if ( $league_id <= 0 ) {
            $errors[] = __( 'Please select a league.', 'auto-fixture-generator-for-sportspress' );
        }
        if ( $season_id <= 0 ) {
            $errors[] = __( 'Please select a season.', 'auto-fixture-generator-for-sportspress' );
        }
        if ( empty( $algorithm ) || !isset( $algorithms[$algorithm] ) ) {
            $errors[] = __( 'Please select a valid algorithm.', 'auto-fixture-generator-for-sportspress' );
        }
        if ( count( $team_ids ) < 2 ) {
            $errors[] = __( 'Please select at least two teams.', 'auto-fixture-generator-for-sportspress' );
        }
        if ( empty( $schedule['start_date'] ) ) {
            $errors[] = __( 'Please select a scheduling start date.', 'auto-fixture-generator-for-sportspress' );
        }
        if ( !empty( $errors ) ) {
            add_action( 'admin_notices', static function () use($errors) {
                foreach ( $errors as $msg ) {
                    echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
                }
            } );
            return;
        }
        $result = AFGSP_Generator::run(
            $league_id,
            $season_id,
            $algorithm,
            array_merge( $options, array(
                'schedule' => $schedule,
            ) ),
            $team_ids
        );
        if ( !empty( $result['errors'] ) ) {
            add_action( 'admin_notices', static function () use($result) {
                foreach ( $result['errors'] as $msg ) {
                    echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
                }
            } );
        }
        $created = (int) $result['created'];
        if ( $created > 0 ) {
            add_action( 'admin_notices', static function () use($created, $result) {
                /* translators: %d: number of fixtures created. */
                printf( '<div class="notice notice-success"><p>%s</p><ul>', esc_html( sprintf( _n(
                    '%d fixture created.',
                    '%d fixtures created.',
                    $created,
                    'auto-fixture-generator-for-sportspress'
                ), $created ) ) );
                foreach ( $result['messages'] as $line ) {
                    echo '<li>' . esc_html( (string) $line ) . '</li>';
                }
                echo '</ul></div>';
            } );
        }
    }

    /**
     * Calculate total matches for k-round robin tournament.
     *
     * @param int $teams Number of teams (n).
     * @param int $rounds Number of rounds (k).
     * @return int Total number of matches.
     */
    public static function calculate_round_robin_matches( $teams, $rounds = 1 ) {
        // Ensure valid integers.
        $teams = absint( $teams );
        $rounds = absint( $rounds );
        // If less than 2 teams, no matches can be scheduled.
        if ( $teams < 2 || $rounds < 1 ) {
            return 0;
        }
        /**
         * Formula:
         *   Matches = k * ( n * ( n - 1 ) / 2 )
         * where n = number of teams, k = number of rounds.
         */
        $matches = $rounds * ($teams * ($teams - 1) / 2);
        return (int) $matches;
    }

    /**
     * Get informative description of events to be generated for an algorithm.
     *
     * @param string $algorithm_slug Algorithm slug.
     * @param int    $teams_count    Number of teams.
     * @return string Informative description.
     */
    public static function get_algorithm_events_description( $algorithm_slug, $teams_count ) {
        $algorithms = AFGSP_Registry::get_algorithms();
        if ( !isset( $algorithms[$algorithm_slug] ) ) {
            return '';
        }
        $algorithm = $algorithms[$algorithm_slug];
        $rounds = ( isset( $algorithm['rounds'] ) ? (int) $algorithm['rounds'] : 1 );
        $events_count = self::calculate_round_robin_matches( $teams_count, $rounds );
        if ( $events_count <= 0 ) {
            return '';
        }
        return sprintf( 
            /* translators: %d: number of events */
            __( 'Will be generated **%d** of events', 'auto-fixture-generator-for-sportspress' ),
            $events_count
         );
    }

    /**
     * Render admin page.
     *
     * Displays the Auto Fixture Generator admin interface with form fields for
     * league, season, scheduling options, team selection, and algorithm choice.
     *
     * @return void
     */
    public function render_page() : void {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'auto-fixture-generator-for-sportspress' ) );
        }
        $leagues = afgsp_get_sportspress_terms_map( 'sp_league' );
        $seasons = afgsp_get_sportspress_terms_map( 'sp_season' );
        $algos = AFGSP_Registry::get_algorithms();
        // Get selected days from POST data (with nonce) or default to weekend.
        $selected_days = array('6', '0');
        // Default to Saturday, Sunday.
        if ( isset( $_POST['afgsp_nonce'] ) ) {
            $nonce_value = sanitize_text_field( (string) wp_unslash( $_POST['afgsp_nonce'] ) );
            if ( wp_verify_nonce( $nonce_value, 'afgsp_generate_fixtures' ) ) {
                if ( isset( $_POST['schedule']['days'] ) && is_array( $_POST['schedule']['days'] ) ) {
                    // Sanitize and validate the days array.
                    $raw_days = map_deep( wp_unslash( $_POST['schedule']['days'] ), 'sanitize_text_field' );
                    $selected_days = array_values( $raw_days );
                }
            }
        }
        ?>
		<div class="wrap">
			<?php 
        ?>
				<h1><?php 
        echo esc_html__( 'Auto Fixture Generator for SportsPress', 'auto-fixture-generator-for-sportspress' );
        ?></h1>
			<?php 
        ?>
			<form method="post" id="afgsp_form">
				<?php 
        wp_nonce_field( 'afgsp_generate_fixtures', 'afgsp_nonce' );
        ?>
				<input type="hidden" name="afgsp_action" value="generate" />

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="afgsp_league"><?php 
        esc_html_e( 'League', 'auto-fixture-generator-for-sportspress' );
        ?></label></th>
							<td>
								<select name="league" id="afgsp_league" required>
									<option value=""><?php 
        esc_html_e( 'Select league', 'auto-fixture-generator-for-sportspress' );
        ?></option>
									<?php 
        foreach ( $leagues as $id => $name ) {
            ?>
										<option value="<?php 
            echo esc_attr( (string) $id );
            ?>"><?php 
            echo esc_html( (string) $name );
            ?></option>
									<?php 
        }
        ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="afgsp_season"><?php 
        esc_html_e( 'Season', 'auto-fixture-generator-for-sportspress' );
        ?></label></th>
							<td>
								<select name="season" id="afgsp_season" required>
									<option value=""><?php 
        esc_html_e( 'Select season', 'auto-fixture-generator-for-sportspress' );
        ?></option>
									<?php 
        foreach ( $seasons as $id => $name ) {
            ?>
										<option value="<?php 
            echo esc_attr( (string) $id );
            ?>"><?php 
            echo esc_html( (string) $name );
            ?></option>
									<?php 
        }
        ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php 
        esc_html_e( 'Scheduling start date', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td>
							<input type="date" name="schedule[start_date]" id="afgsp_start_date" required />
								<p class="description"><?php 
        esc_html_e( 'Fixtures will be scheduled from this date onward.', 'auto-fixture-generator-for-sportspress' );
        ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php 
        esc_html_e( 'Gameweek Structure', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td>
								<div id="afgsp_gameweek_builder">
									<div class="afgsp-week-view">
										<div class="afgsp-day-header">
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Mon', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Tue', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Wed', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Thu', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Fri', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Sat', 'auto-fixture-generator-for-sportspress' );
        ?></span>
											<span class="afgsp-day-label"><?php 
        esc_html_e( 'Sun', 'auto-fixture-generator-for-sportspress' );
        ?></span>
										</div>
										<div class="afgsp-day-checkboxes">
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="1" <?php 
        checked( in_array( '1', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="2" <?php 
        checked( in_array( '2', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="3" <?php 
        checked( in_array( '3', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="4" <?php 
        checked( in_array( '4', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="5" <?php 
        checked( in_array( '5', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="6" <?php 
        checked( in_array( '6', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
											<label class="afgsp-day-checkbox">
												<input type="checkbox" name="schedule[days][]" value="0" <?php 
        checked( in_array( '0', $selected_days, true ) );
        ?> />
												<span class="afgsp-checkbox-visual"></span>
											</label>
										</div>
									</div>
									<div id="afgsp_gameweek_info" class="afgsp-gameweek-info">
										<p class="description">
											<?php 
        esc_html_e( 'Select the days that will be used for each gameweek. Gameweek spans from the first selected day to the last selected day.', 'auto-fixture-generator-for-sportspress' );
        ?>
										</p>
										<div id="afgsp_gameweek_preview">
											<strong><?php 
        esc_html_e( 'Gameweek Structure:', 'auto-fixture-generator-for-sportspress' );
        ?></strong>
											<span id="afgsp_gameweek_range"><?php 
        esc_html_e( 'No days selected', 'auto-fixture-generator-for-sportspress' );
        ?></span>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php 
        esc_html_e( 'Gameweek name', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td>
								<input type="text" name="schedule[gameweek_name]" id="afgsp_gameweek_name" value="Gameweek %No%" class="regular-text" />
								<p class="description">
									<?php 
        esc_html_e( 'Enter the gameweek naming pattern. Use %No% as a placeholder for the gameweek number (1, 2, 3, etc.).', 'auto-fixture-generator-for-sportspress' );
        ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php 
        esc_html_e( 'Events per timeslot mode', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td>
								<select name="schedule[events_mode]" id="afgsp_events_mode" <?php 
        echo ( !afgsp_fs()->can_use_premium_code__premium_only() ? 'disabled' : '' );
        ?>>
									<option value="auto" selected><?php 
        esc_html_e( 'AUTO', 'auto-fixture-generator-for-sportspress' );
        ?></option>
									<?php 
        ?>
								</select>
								<p class="description">
									<?php 
        ?>
										<?php 
        esc_html_e( 'Events per slot are calculated automatically. Upgrade to premium for manual control.', 'auto-fixture-generator-for-sportspress' );
        ?>
										<a href="<?php 
        echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
        ?>" target="_blank" style="font-weight: 600;">
											<?php 
        esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
        ?> →
										</a>
									<?php 
        ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php 
        esc_html_e( 'Time slots per match day', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td>
								<div id="afgsp_time_slots">
									<div>
										<input type="time" name="schedule[time_slots][]" value="20:00" />
										<input type="number" name="schedule[events_per_slot][]" class="afgsp-events-per-slot small-text" min="1" value="" disabled />
										<?php 
        ?>
									</div>
									<?php 
        ?>
								</div>
								<?php 
        ?>
								<p class="description">
									<?php 
        ?>
										<?php 
        esc_html_e( 'Define kickoff time for match days. Upgrade to premium for multiple time slots.', 'auto-fixture-generator-for-sportspress' );
        ?>
										<a href="<?php 
        echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
        ?>" target="_blank" style="font-weight: 600;">
											<?php 
        esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
        ?> →
										</a>
									<?php 
        ?>
								</p>
							</td>
						</tr>
					<tr>
						<th scope="row"><?php 
        esc_html_e( 'Blocked dates', 'auto-fixture-generator-for-sportspress' );
        ?></th>
						<td>
							<div id="afgsp_blocked_dates">
								<input type="date" name="schedule[blocked_dates][]" <?php 
        echo ( !afgsp_fs()->can_use_premium_code__premium_only() ? 'disabled' : '' );
        ?> />
							</div>
							<p>
								<button type="button" class="button" id="afgsp_add_blocked_date" <?php 
        echo ( !afgsp_fs()->can_use_premium_code__premium_only() ? 'disabled' : '' );
        ?>><?php 
        esc_html_e( 'Add blocked date', 'auto-fixture-generator-for-sportspress' );
        ?></button>
							</p>
							<p class="description">
								<?php 
        ?>
									<?php 
        esc_html_e( 'Blocked dates are available in Premium. Upgrade to enable this feature.', 'auto-fixture-generator-for-sportspress' );
        ?>
									<a href="<?php 
        echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
        ?>" target="_blank" style="font-weight: 600;">
										<?php 
        esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
        ?> →
									</a>
								<?php 
        ?>
							</p>
						</td>
					</tr>
					<tr id="afgsp_teams_wrap">
						<th scope="row"><?php 
        esc_html_e( 'Teams to include', 'auto-fixture-generator-for-sportspress' );
        ?></th>
						<td>
							<p class="description" id="afgsp_teams_desc">
								<?php 
        ?>
									<?php 
        esc_html_e( 'Advanced team selection is a Premium feature. All teams will be included in the free version.', 'auto-fixture-generator-for-sportspress' );
        ?>
									<a href="<?php 
        echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
        ?>" target="_blank" style="font-weight: 600;">
										<?php 
        esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
        ?> →
									</a>
								<?php 
        ?>
							</p>
							<ul id="afgsp_teams" style="display:none; max-height:240px; overflow:auto; border:1px solid #ccd0d4; padding:8px; background:#fff; <?php 
        echo ( !afgsp_fs()->can_use_premium_code__premium_only() ? 'opacity:0.6; pointer-events:none;' : '' );
        ?>">
								<?php 
        ?>
									<li><?php 
        esc_html_e( 'Upgrade to Premium to select specific teams.', 'auto-fixture-generator-for-sportspress' );
        ?></li>
								<?php 
        ?>
							</ul>
						</td>
					</tr>
						<tr>
							<th scope="row"><label for="afgsp_algorithm"><?php 
        esc_html_e( 'Algorithm', 'auto-fixture-generator-for-sportspress' );
        ?></label></th>
							<td>
								<select name="algorithm" id="afgsp_algorithm" required>
									<option value=""><?php 
        esc_html_e( 'Select algorithm', 'auto-fixture-generator-for-sportspress' );
        ?></option>
									<?php 
        foreach ( $algos as $slug => $def ) {
            ?>
										<option value="<?php 
            echo esc_attr( (string) $slug );
            ?>"><?php 
            echo esc_html( (string) ($def['label'] ?? ucfirst( (string) $slug )) );
            ?></option>
									<?php 
        }
        ?>
								</select>
							<?php 
        ?>
								<?php 
        $premium_algo_labels = $this->get_premium_algorithm_labels_for_message();
        ?>
								<p class="description">
									<?php 
        if ( !empty( $premium_algo_labels ) ) {
            /* translators: %s: comma-separated list of premium algorithm names. */
            echo esc_html( sprintf( __( 'More algorithms are available in Premium: %s.', 'auto-fixture-generator-for-sportspress' ), implode( ', ', array_map( 'strval', $premium_algo_labels ) ) ) );
            ?>
										<a href="<?php 
            echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
            ?>" target="_blank" style="font-weight: 600;">
											<?php 
            esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
            ?> →
										</a>
									<?php 
        } else {
            esc_html_e( 'More algorithms are available in Premium.', 'auto-fixture-generator-for-sportspress' );
            ?>
										<a href="<?php 
            echo esc_url( 'https://savvasha.com/auto-fixture-generator-for-sportspress-premium/' );
            ?>" target="_blank" style="font-weight: 600;">
											<?php 
            esc_html_e( 'Upgrade to premium version now!', 'auto-fixture-generator-for-sportspress' );
            ?> →
										</a>
										<?php 
        }
        ?>
								</p>
							<?php 
        ?>
							</td>
						</tr>
						<tr id="afgsp_dynamic_options_row" style="display:none;">
							<th scope="row"><?php 
        esc_html_e( 'Algorithm Options', 'auto-fixture-generator-for-sportspress' );
        ?></th>
							<td id="afgsp_dynamic_options"></td>
						</tr>
					</tbody>
				</table>

				<p class="submit afgsp-submit-buttons">
					<?php 
        submit_button(
            __( 'Generate Fixtures', 'auto-fixture-generator-for-sportspress' ),
            'primary',
            'submit',
            false
        );
        ?>
					<?php 
        if ( AFGSP_Debug::is_debug_mode_available() ) {
            ?>
						<button type="button" id="afgsp_dry_run_btn" class="button afgsp-dry-run-btn">
							<?php 
            esc_html_e( 'Generate Fixtures (DRY MODE)', 'auto-fixture-generator-for-sportspress' );
            ?>
						</button>
					<?php 
        }
        ?>
				</p>
			</form>
		</div>
		<?php 
    }

}
