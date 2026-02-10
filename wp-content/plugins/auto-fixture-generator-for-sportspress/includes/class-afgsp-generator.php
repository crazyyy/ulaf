<?php

/**
 * Core generation workflow for Auto Fixture Generator for SportsPress.
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
 * Class AFGSP_Generator
 *
 * Orchestrates data retrieval from SportsPress, invokes the selected algorithm,
 * and persists the resulting fixtures as SportsPress events.
 */
class AFGSP_Generator {
    /**
     * Prepare fixtures with scheduling applied.
     *
     * This method calls the algorithm, applies scheduling (dates, times, gameweeks),
     * and returns the prepared fixtures array. Used by both normal and dry-run modes
     * to ensure consistent scheduling logic.
     *
     * @param array  $raw_fixtures    Raw fixtures from algorithm (array of home/away/meta).
     * @param array  $schedule        Schedule settings (start_date, days, time_slots, blocked_dates, gameweek_name).
     * @param int    $teams_count     Number of teams (for calculating matches per round).
     * @param string $algorithm       Algorithm slug (used to determine duplicate handling).
     * @return array Prepared fixtures with datetime and gameweek assigned.
     */
    public static function prepare_fixtures(
        array $raw_fixtures,
        array $schedule,
        int $teams_count,
        string $algorithm = ''
    ) : array {
        // For round-robin: matches per round = floor(n/2) (when n is odd, one team has bye).
        $matches_per_round = max( 1, (int) floor( $teams_count / 2 ) );
        // Parse schedule settings.
        $start_date = ( isset( $schedule['start_date'] ) ? (string) $schedule['start_date'] : '' );
        $days = ( isset( $schedule['days'] ) && is_array( $schedule['days'] ) ? array_map( 'intval', (array) $schedule['days'] ) : array() );
        $time_slots = ( isset( $schedule['time_slots'] ) && is_array( $schedule['time_slots'] ) ? array_values( array_filter( (array) $schedule['time_slots'] ) ) : array() );
        $blocked = ( isset( $schedule['blocked_dates'] ) && is_array( $schedule['blocked_dates'] ) ? array_values( array_filter( (array) $schedule['blocked_dates'] ) ) : array() );
        $blocked_map = array_fill_keys( $blocked, true );
        $gameweek_name = ( isset( $schedule['gameweek_name'] ) ? (string) $schedule['gameweek_name'] : 'Gameweek %No%' );
        // Parse events mode settings (premium feature).
        $events_mode = ( isset( $schedule['events_mode'] ) ? (string) $schedule['events_mode'] : 'auto' );
        $events_per_slot_limits = ( isset( $schedule['events_per_slot'] ) && is_array( $schedule['events_per_slot'] ) ? array_map( 'intval', $schedule['events_per_slot'] ) : array() );
        // Initialize scheduling state.
        $slot_index = 0;
        $matches_on_current_day = 0;
        $last_gameweek = 0;
        // Track events assigned per slot for MANUAL mode (reset daily).
        $slots_count = ( !empty( $time_slots ) ? count( $time_slots ) : 1 );
        $events_assigned_to_slots = array_fill( 0, $slots_count, 0 );
        // Calculate how many matches should be scheduled per day (AUTO mode).
        // This ensures fixtures are distributed across available days in a gameweek.
        $matches_per_day = ( count( $days ) > 0 ? (int) ceil( $matches_per_round / count( $days ) ) : $matches_per_round );
        $cursor = ( $start_date ? strtotime( $start_date . ' 00:00:00' ) : false );
        if ( $cursor && empty( $days ) ) {
            // Default to weekend if start date provided but no specific days selected.
            $days = array(6, 0);
            // Saturday, Sunday.
        }
        $prepared_fixtures = array();
        $seen_pairs = array();
        $fixture_index = 0;
        foreach ( $raw_fixtures as $fixture ) {
            $home_id = ( isset( $fixture['home'] ) ? (int) $fixture['home'] : 0 );
            $away_id = ( isset( $fixture['away'] ) ? (int) $fixture['away'] : 0 );
            $extra_meta = ( isset( $fixture['meta'] ) && is_array( $fixture['meta'] ) ? $fixture['meta'] : array() );
            // Skip invalid fixtures.
            if ( $home_id <= 0 || $away_id <= 0 || $home_id === $away_id ) {
                continue;
            }
            // Suppress duplicates within a single generation run.
            // Skip duplicate check for fixed-week-season as it allows teams to play multiple times.
            $skip_duplicate_check = 'fixed-week-season' === $algorithm;
            if ( !$skip_duplicate_check ) {
                $pair_key = $home_id . '_' . $away_id;
                if ( isset( $seen_pairs[$pair_key] ) ) {
                    continue;
                }
                $seen_pairs[$pair_key] = true;
            }
            // Calculate gameweek based on fixture index and round size.
            $current_gameweek = (int) floor( $fixture_index / $matches_per_round ) + 1;
            $gameweek_display_name = str_replace( '%No%', (string) $current_gameweek, $gameweek_name );
            $extra_meta['sp_day'] = $gameweek_display_name;
            // If gameweek changed, advance cursor to next week and reset counters.
            if ( $last_gameweek > 0 && $current_gameweek > $last_gameweek ) {
                $slot_index = 0;
                $matches_on_current_day = 0;
                $events_assigned_to_slots = array_fill( 0, $slots_count, 0 );
                $cursor = self::advance_cursor_to_next_week( $cursor, $days, $blocked_map );
            }
            // Assign datetime if not already set by algorithm.
            if ( !isset( $extra_meta['datetime'] ) && $cursor ) {
                $assigned = false;
                while ( !$assigned ) {
                    $weekday = (int) gmdate( 'w', $cursor );
                    $ymd = gmdate( 'Y-m-d', $cursor );
                    // Skip blocked dates.
                    if ( isset( $blocked_map[$ymd] ) ) {
                        $cursor = strtotime( '+1 day', $cursor );
                        continue;
                    }
                    // Check if current day is a selected match day.
                    if ( in_array( $weekday, $days, true ) ) {
                        if ( 'manual' === $events_mode && !empty( $events_per_slot_limits ) && !empty( $time_slots ) ) {
                            // MANUAL mode: find a slot that hasn't reached its limit.
                            $slot_found = false;
                            for ($i = 0; $i < $slots_count; $i++) {
                                $try_slot = ($slot_index + $i) % $slots_count;
                                $limit = ( isset( $events_per_slot_limits[$try_slot] ) ? (int) $events_per_slot_limits[$try_slot] : PHP_INT_MAX );
                                if ( $events_assigned_to_slots[$try_slot] < $limit ) {
                                    $slot = $time_slots[$try_slot];
                                    $extra_meta['datetime'] = $ymd . ' ' . $slot;
                                    $events_assigned_to_slots[$try_slot]++;
                                    $slot_index = $try_slot + 1;
                                    $slot_found = true;
                                    $matches_on_current_day++;
                                    $assigned = true;
                                    break;
                                }
                            }
                            if ( !$slot_found ) {
                                // All slots full for today, advance to next day.
                                $cursor = strtotime( '+1 day', $cursor );
                                $matches_on_current_day = 0;
                                $events_assigned_to_slots = array_fill( 0, $slots_count, 0 );
                                continue;
                            }
                        } else {
                            // AUTO mode: cycle through available slots.
                            $slot = ( !empty( $time_slots ) ? $time_slots[$slot_index % $slots_count] : '15:00' );
                            $extra_meta['datetime'] = $ymd . ' ' . $slot;
                            $slot_index++;
                            $matches_on_current_day++;
                            $assigned = true;
                            // Advance cursor when daily quota is met.
                            if ( $matches_on_current_day >= $matches_per_day ) {
                                $cursor = strtotime( '+1 day', $cursor );
                                $matches_on_current_day = 0;
                                $events_assigned_to_slots = array_fill( 0, $slots_count, 0 );
                            }
                        }
                    } else {
                        $cursor = strtotime( '+1 day', $cursor );
                    }
                }
            }
            $prepared_fixtures[] = array(
                'home_id'    => $home_id,
                'away_id'    => $away_id,
                'extra_meta' => $extra_meta,
                'gameweek'   => $current_gameweek,
            );
            $last_gameweek = $current_gameweek;
            $fixture_index++;
        }
        return $prepared_fixtures;
    }

    /**
     * Advance cursor to the next week's first selected day.
     *
     * @param int   $cursor      Current timestamp.
     * @param array $days        Selected days of the week (0=Sun, 6=Sat).
     * @param array $blocked_map Map of blocked dates.
     * @return int New cursor timestamp.
     */
    private static function advance_cursor_to_next_week( int $cursor, array $days, array $blocked_map ) : int {
        if ( empty( $days ) ) {
            return $cursor;
        }
        // Move to next day first.
        $cursor = strtotime( '+1 day', $cursor );
        // Find the first day in the gameweek structure (not numeric min).
        // Use the sorted gameweek days to get the correct starting day.
        // For example, with days [6, 0] (Sat, Sun), Saturday is the first day, not Sunday.
        $sorted_days = \AFGSP\afgsp_sort_gameweek_days( $days );
        $first_day = $sorted_days[0];
        // Advance until we reach the next occurrence of the first gameweek day.
        $max_iterations = 14;
        // Safety limit.
        $iterations = 0;
        while ( $iterations < $max_iterations ) {
            $weekday = (int) gmdate( 'w', $cursor );
            $ymd = gmdate( 'Y-m-d', $cursor );
            // Skip blocked dates.
            if ( isset( $blocked_map[$ymd] ) ) {
                $cursor = strtotime( '+1 day', $cursor );
                $iterations++;
                continue;
            }
            // Check if we've reached the first day of the next gameweek.
            if ( $weekday === $first_day ) {
                return $cursor;
            }
            $cursor = strtotime( '+1 day', $cursor );
            $iterations++;
        }
        return $cursor;
    }

    /**
     * Run generation process.
     *
     * @param int    $league_term_id    League term ID.
     * @param int    $season_term_id    Season term ID.
     * @param string $algorithm_slug    Algorithm slug.
     * @param array  $options           Algorithm options.
     * @param array  $selected_team_ids Optional selected team IDs.
     * @param bool   $dry_run           If true, skip event creation and return fixture data only.
     * @return array Result with created count, errors, messages, and fixtures (for dry run).
     */
    public static function run(
        int $league_term_id,
        int $season_term_id,
        string $algorithm_slug,
        array $options = array(),
        array $selected_team_ids = array(),
        bool $dry_run = false
    ) : array {
        $result = array(
            'created'  => 0,
            'errors'   => array(),
            'messages' => array(),
            'fixtures' => array(),
        );
        // Load teams for the selected league & season if not explicitly provided.
        $teams = array();
        if ( empty( $selected_team_ids ) ) {
            $teams = \AFGSP\afgsp_get_teams_for_league_and_season( $league_term_id, $season_term_id );
            if ( count( $teams ) < 2 ) {
                $result['errors'][] = __( 'At least two teams are required to generate fixtures.', 'auto-fixture-generator-for-sportspress' );
                return $result;
            }
        }
        if ( !empty( $selected_team_ids ) && count( $selected_team_ids ) < 2 ) {
            $result['errors'][] = __( 'At least two teams are required to generate fixtures.', 'auto-fixture-generator-for-sportspress' );
            return $result;
        }
        // Convert teams to a simpler structure for algorithms.
        $team_ids = ( !empty( $selected_team_ids ) ? array_values( array_unique( array_map( 'intval', $selected_team_ids ) ) ) : array_map( static function ( $post ) {
            return (int) $post->ID;
        }, $teams ) );
        // Find and invoke algorithm.
        $callable = AFGSP_Registry::get_algorithm_callable( $algorithm_slug );
        if ( !$callable ) {
            $result['errors'][] = __( 'Selected algorithm could not be loaded.', 'auto-fixture-generator-for-sportspress' );
            return $result;
        }
        try {
            $raw_fixtures = call_user_func( $callable, $team_ids, $options );
            if ( !is_array( $raw_fixtures ) ) {
                $result['errors'][] = __( 'Algorithm returned an invalid response.', 'auto-fixture-generator-for-sportspress' );
                return $result;
            }
        } catch ( \Throwable $e ) {
            $result['errors'][] = sprintf( 
                /* translators: %s: error message */
                __( 'Algorithm error: %s', 'auto-fixture-generator-for-sportspress' ),
                $e->getMessage()
             );
            return $result;
        }
        // Extract schedule from options.
        $schedule = ( isset( $options['schedule'] ) && is_array( $options['schedule'] ) ? (array) $options['schedule'] : array() );
        // Prepare fixtures with scheduling applied (centralized logic).
        $prepared_fixtures = self::prepare_fixtures(
            $raw_fixtures,
            $schedule,
            count( $team_ids ),
            $algorithm_slug
        );
        // Process fixtures: create events or collect for dry run.
        foreach ( $prepared_fixtures as $fixture_data ) {
            if ( $dry_run ) {
                // Dry run: collect fixture data without creating events.
                $result['fixtures'][] = $fixture_data;
                $result['created']++;
                $result['messages'][] = sprintf(
                    '%1$s vs %2$s (%3$s)',
                    get_the_title( $fixture_data['home_id'] ),
                    get_the_title( $fixture_data['away_id'] ),
                    $fixture_data['extra_meta']['sp_day'] ?? ''
                );
            } else {
                // Normal mode: create events in database.
                $created = \AFGSP\afgsp_create_event(
                    $fixture_data['home_id'],
                    $fixture_data['away_id'],
                    $league_term_id,
                    $season_term_id,
                    $fixture_data['extra_meta']
                );
                if ( is_wp_error( $created ) ) {
                    $result['errors'][] = $created->get_error_message();
                    continue;
                }
                $result['created']++;
                $result['messages'][] = sprintf(
                    '%1$s vs %2$s (%3$s)',
                    get_the_title( $fixture_data['home_id'] ),
                    get_the_title( $fixture_data['away_id'] ),
                    $fixture_data['extra_meta']['sp_day'] ?? ''
                );
            }
        }
        return $result;
    }

}
