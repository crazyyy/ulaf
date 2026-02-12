<?php

/**
 * Single round-robin algorithm.
 *
 * @package AFGSP
 */
declare (strict_types = 1);
namespace AFGSP\Algorithms\single_round_robin;

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Register algorithm with the plugin via filter.
 */
add_filter(
    'afgsp_algorithms',
    static function ( array $algorithms ) : array {
        $algorithms['single-round-robin'] = array(
            'label'          => __( 'Single Round Robin', 'auto-fixture-generator-for-sportspress' ),
            'file'           => __FILE__,
            'options_schema' => array(),
            'rounds'         => 1,
        );
        return $algorithms;
    },
    10,
    1
);
/**
 * Generate fixtures using the circle method for a single round-robin.
 *
 * Note: This function name is intentionally unprefixed as it's part of a
 * namespaced algorithm system where the namespace provides sufficient isolation.
 *
 * @param array<int,int> $teams   List of team IDs.
 * @param array          $options Algorithm options.
 * @return array<int,array{home:int,away:int,meta:array}>
 */
function generate_fixtures(  array $teams, array $options = array()  ) : array {
    $fixtures = array();
    $team_ids = array_values( $teams );
    $n = count( $team_ids );
    if ( 0 !== $n % 2 ) {
        $team_ids[] = 0;
        // 0 represents a bye week.
        $n++;
    }
    $rounds = $n - 1;
    $half = (int) ($n / 2);
    $teams_ring = $team_ids;
    $even_count = 0 === $n % 2;
    // Track home/away counts for balancing (excludes bye team 0).
    $home_count = array();
    $away_count = array();
    foreach ( $team_ids as $tid ) {
        if ( 0 !== $tid ) {
            $home_count[$tid] = 0;
            $away_count[$tid] = 0;
        }
    }
    for ($round = 0; $round < $rounds; $round++) {
        for ($i = 0; $i < $half; $i++) {
            $home_team = (int) $teams_ring[$i];
            $away_team = (int) $teams_ring[$n - 1 - $i];
            if ( 0 === $home_team || 0 === $away_team ) {
                continue;
                // Skip byes.
            }
            // Balance home/away distribution: swap if away_team has fewer home games.
            $home_games_home = $home_count[$home_team] ?? 0;
            $home_games_away = $home_count[$away_team] ?? 0;
            if ( $home_games_away < $home_games_home ) {
                $swap = $home_team;
                $home_team = $away_team;
                $away_team = $swap;
            }
            $fixtures[] = array(
                'home' => $home_team,
                'away' => $away_team,
                'meta' => array(),
            );
            // Update home/away counts.
            $home_count[$home_team]++;
            $away_count[$away_team]++;
        }
        // Rotate for next round.
        if ( $even_count ) {
            // Keep index 0 fixed; move last element to index 1.
            $last = array_pop( $teams_ring );
            array_splice(
                $teams_ring,
                1,
                0,
                $last
            );
        } else {
            // Rotate all teams: move last element to the front.
            $last = array_pop( $teams_ring );
            array_unshift( $teams_ring, $last );
        }
    }
    return $fixtures;
}
