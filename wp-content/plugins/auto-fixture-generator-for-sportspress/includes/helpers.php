<?php
/**
 * Helper functions for Auto Fixture Generator for SportsPress.
 *
 * @package AFGSP
 */

declare( strict_types=1 );

namespace AFGSP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get SportsPress terms for a given taxonomy as an associative array suitable for select inputs.
 *
 * @param string $taxonomy Taxonomy name (e.g., 'sp_league', 'sp_season').
 * @return array<string,string> Map of term_id => term_name.
 */
function afgsp_get_sportspress_terms_map( string $taxonomy ): array {
	$terms_map = array();
	$terms     = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $terms_map;
	}

	foreach ( $terms as $term ) {
		$terms_map[ (string) $term->term_id ] = (string) $term->name;
	}

	return $terms_map;
}

/**
 * Get SportsPress teams (posts of type 'sp_team') associated with the provided league and season terms.
 *
 * @param int $league_term_id League term ID.
 * @param int $season_term_id Season term ID.
 * @return array<int,\WP_Post> Array of team posts.
 */
function afgsp_get_teams_for_league_and_season( int $league_term_id, int $season_term_id ): array {
	$args = array(
		'post_type'      => 'sp_team',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'tax_query'      => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'sp_league',
				'field'    => 'term_id',
				'terms'    => array( $league_term_id ),
			),
			array(
				'taxonomy' => 'sp_season',
				'field'    => 'term_id',
				'terms'    => array( $season_term_id ),
			),
		),
	);

	$query = new \WP_Query( $args );

	return $query->posts ? array_values( $query->posts ) : array();
}

/**
 * Check if an event (fixture) already exists between two teams in a given league/season.
 * This prevents creating duplicate fixtures.
 *
 * @param int $home_team_id Home team post ID.
 * @param int $away_team_id Away team post ID.
 * @param int $league_term_id League term ID.
 * @param int $season_term_id Season term ID.
 * @return bool True if duplicate exists, false otherwise.
 */
function afgsp_event_exists_between_teams( int $home_team_id, int $away_team_id, int $league_term_id, int $season_term_id ): bool {
	// Check exact home/away pairing with dedicated plugin meta to avoid false positives.
	$pair_key = $home_team_id . '_' . $away_team_id;

	$args = array(
		'post_type'      => 'sp_event',
		'posts_per_page' => 1,
		'post_status'    => array( 'publish', 'future', 'draft', 'pending' ),
		'tax_query'      => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'sp_league',
				'field'    => 'term_id',
				'terms'    => array( $league_term_id ),
			),
			array(
				'taxonomy' => 'sp_season',
				'field'    => 'term_id',
				'terms'    => array( $season_term_id ),
			),
		),
		'meta_query'     => array(
			array(
				'key'     => 'afgsp_teams',
				'value'   => $pair_key,
				'compare' => '=',
				'type'    => 'CHAR',
			),
		),
	);

	$query = new \WP_Query( $args );

	return ( $query->found_posts > 0 );
}

/**
 * Check if an event exists for the same teams on a specific date (Y-m-d).
 *
 * @param int    $team_one_id    Team ID.
 * @param int    $team_two_id    Team ID.
 * @param int    $league_term_id League term ID.
 * @param int    $season_term_id Season term ID.
 * @param string $ymd_date       Date in Y-m-d.
 * @return bool True if an event exists on that date, false otherwise.
 */
function afgsp_event_exists_between_teams_on_date( int $team_one_id, int $team_two_id, int $league_term_id, int $season_term_id, string $ymd_date ): bool {
	$start = strtotime( $ymd_date . ' 00:00:00' );
	$end   = strtotime( $ymd_date . ' 23:59:59' );
	if ( ! $start || ! $end ) {
		return false;
	}

	$pair_key = $team_one_id . '_' . $team_two_id;

	$args = array(
		'post_type'      => 'sp_event',
		'posts_per_page' => 1,
		'post_status'    => array( 'publish', 'future', 'draft', 'pending' ),
		'tax_query'      => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'sp_league',
				'field'    => 'term_id',
				'terms'    => array( $league_term_id ),
			),
			array(
				'taxonomy' => 'sp_season',
				'field'    => 'term_id',
				'terms'    => array( $season_term_id ),
			),
		),
		'meta_query'     => array(
			array(
				'key'     => 'afgsp_teams',
				'value'   => $pair_key,
				'compare' => '=',
				'type'    => 'CHAR',
			),
		),
		'date_query'     => array(
			array(
				'after'     => gmdate( 'Y-m-d H:i:s', $start ),
				'before'    => gmdate( 'Y-m-d H:i:s', $end ),
				'inclusive' => true,
			),
		),
	);

	$query = new \WP_Query( $args );

	return ( $query->found_posts > 0 );
}

/**
 * Create a SportsPress event (fixture).
 *
 * @param int   $home_team_id   Home team ID.
 * @param int   $away_team_id   Away team ID.
 * @param int   $league_term_id League term ID.
 * @param int   $season_term_id Season term ID.
 * @param array $extra_meta     Additional post meta to add to the event.
 * @param bool  $skip_duplicate_check Whether to skip duplicate checking (for algorithms that allow rematches).
 * @return int|\WP_Error Post ID on success, WP_Error on failure.
 */
function afgsp_create_event( int $home_team_id, int $away_team_id, int $league_term_id, int $season_term_id, array $extra_meta = array(), bool $skip_duplicate_check = false ) {
	// If we know the date, ensure we do not create duplicates on the same day (unless skipping).
	$ymd = '';
	if ( isset( $extra_meta['datetime'] ) && is_string( $extra_meta['datetime'] ) ) {
		$parts = explode( ' ', $extra_meta['datetime'] );
		$ymd   = sanitize_text_field( (string) ( $parts[0] ?? '' ) );
	}

	if ( ! $skip_duplicate_check && $ymd && afgsp_event_exists_between_teams_on_date( $home_team_id, $away_team_id, $league_term_id, $season_term_id, $ymd ) ) {
		$home_title = get_the_title( $home_team_id );
		$away_title = get_the_title( $away_team_id );
		return new \WP_Error(
			'afgsp_duplicate',
			sprintf(
				/* translators: 1: home team, 2: away team, 3: date */
				__( 'Duplicate event detected for this date: %1$s vs %2$s (%3$s).', 'auto-fixture-generator-for-sportspress' ),
				(string) $home_title,
				(string) $away_title,
				(string) $ymd
			)
		);
	}

	// Fallback duplicate check in league/season regardless of date (unless skipping).
	if ( ! $skip_duplicate_check && afgsp_event_exists_between_teams( $home_team_id, $away_team_id, $league_term_id, $season_term_id ) ) {
		$home_title = get_the_title( $home_team_id );
		$away_title = get_the_title( $away_team_id );
		return new \WP_Error(
			'afgsp_duplicate',
			sprintf(
				/* translators: 1: home team, 2: away team */
				__( 'Duplicate event detected in this league/season: %1$s vs %2$s.', 'auto-fixture-generator-for-sportspress' ),
				(string) $home_title,
				(string) $away_title
			)
		);
	}

	// Insert a base event like the importer does; title will be updated after adding teams.
	$postarr = array(
		'post_type'   => 'sp_event',
		'post_title'  => __( 'Event', 'auto-fixture-generator-for-sportspress' ),
		'post_status' => 'publish',
	);

	if ( isset( $extra_meta['datetime'] ) && is_string( $extra_meta['datetime'] ) && $extra_meta['datetime'] ) {
		$timestamp = strtotime( $extra_meta['datetime'] );
		if ( $timestamp ) {
			$postarr['post_date'] = gmdate( 'Y-m-d H:i:s', $timestamp );
		}
	}

	$event_id = wp_insert_post( $postarr, true );
	if ( is_wp_error( $event_id ) ) {
		return $event_id;
	}

	// Assign league and season.
	wp_set_post_terms( $event_id, array( $league_term_id ), 'sp_league', false );
	wp_set_post_terms( $event_id, array( $season_term_id ), 'sp_season', false );

	// Teams: add as separate sp_team metas (importer behavior), not a single array.
	add_post_meta( $event_id, 'sp_team', $home_team_id );
	add_post_meta( $event_id, 'sp_team', $away_team_id );

	// Store ordered pair for duplicate detection across this plugin.
	update_post_meta( $event_id, 'afgsp_teams', $home_team_id . '_' . $away_team_id );

	// Update event title and slug similar to importer.
	$delimiter = get_option( 'sportspress_event_teams_delimiter', 'vs' );
	$title     = trim( sprintf( '%1$s %3$s %2$s', get_the_title( $home_team_id ), get_the_title( $away_team_id ), $delimiter ) );
	wp_update_post(
		array(
			'ID'         => $event_id,
			'post_title' => $title,
			'post_name'  => (string) $event_id,
		)
	);

	// Optional: venue and match day.
	if ( isset( $extra_meta['sp_venue'] ) ) {
		$venue_term_id = (int) $extra_meta['sp_venue'];
		if ( $venue_term_id > 0 ) {
			wp_set_object_terms( $event_id, $venue_term_id, 'sp_venue', false );
		}
	} else {
		// Auto-assign home team's venue if available.
		$home_team_venue = sp_get_the_term_id( $home_team_id, 'sp_venue' );
		if ( $home_team_venue > 0 ) {
			wp_set_object_terms( $event_id, $home_team_venue, 'sp_venue', false );
		}
	}
	if ( isset( $extra_meta['sp_day'] ) && '' !== $extra_meta['sp_day'] ) {
		update_post_meta( $event_id, 'sp_day', sanitize_text_field( (string) $extra_meta['sp_day'] ) );
	}

	// Persist any non-reserved extra meta.
	foreach ( $extra_meta as $meta_key => $meta_value ) {
		$meta_key = (string) $meta_key;
		if ( in_array( $meta_key, array( 'datetime', 'sp_venue', 'sp_day' ), true ) ) {
			continue;
		}
		update_post_meta( $event_id, sanitize_key( $meta_key ), $meta_value );
	}

	// Ensure event format is set to league.
	update_post_meta( $event_id, 'sp_format', 'league' );

	return $event_id;
}

/**
 * Discover algorithms by scanning the /algorithms directory and/or using a filter.
 *
 * @return array<string,array> Map of algorithm slug to details: [ 'label' => string, 'file' => string, 'options_schema' => array ]
 */
function afgsp_discover_algorithms(): array {
	$algorithms = apply_filters( 'afgsp_algorithms', array() );

	// Ensure each algorithm has mandatory fields.
	foreach ( $algorithms as $slug => $definition ) {
		if ( ! isset( $definition['label'] ) ) {
			$algorithms[ $slug ]['label'] = ucfirst( (string) $slug );
		}
		if ( ! isset( $definition['file'] ) ) {
			$algorithms[ $slug ]['file'] = '';
		}
		if ( ! isset( $definition['options_schema'] ) || ! is_array( $definition['options_schema'] ) ) {
			$algorithms[ $slug ]['options_schema'] = array();
		}
	}

	return $algorithms;
}

/**
 * Sanitize posted options based on a simple schema.
 *
 * Schema structure example:
 * [
 *   'no_consecutive_away' => [ 'type' => 'bool' ],
 *   'start_date' => [ 'type' => 'string' ],
 * ]
 *
 * @param array $raw Raw posted data.
 * @param array $schema Validation schema.
 * @return array Sanitized data.
 */
function afgsp_sanitize_options_against_schema( array $raw, array $schema ): array {
	$sanitized = array();
	foreach ( $schema as $key => $rules ) {
		$type = isset( $rules['type'] ) ? (string) $rules['type'] : 'string';
		if ( 'bool' === $type ) {
			$sanitized[ $key ] = isset( $raw[ $key ] ) ? (bool) $raw[ $key ] : false;
		} elseif ( 'int' === $type ) {
			$sanitized[ $key ] = isset( $raw[ $key ] ) ? (int) $raw[ $key ] : 0;
		} elseif ( 'array_string' === $type ) {
			$values            = isset( $raw[ $key ] ) && is_array( $raw[ $key ] ) ? (array) $raw[ $key ] : array();
			$sanitized[ $key ] = array_values(
				array_filter(
					array_map(
						static function ( $v ) {
							return sanitize_text_field( (string) $v );
						},
						$values
					),
					static function ( $v ) {
						return '' !== $v;
					}
				)
			);
		} elseif ( 'array_int' === $type ) {
			$values            = isset( $raw[ $key ] ) && is_array( $raw[ $key ] ) ? (array) $raw[ $key ] : array();
			$sanitized[ $key ] = array_values(
				array_filter(
					array_map( 'intval', $values ),
					static function ( $v ) {
						return is_int( $v );
					}
				)
			);
		} else {
			$sanitized[ $key ] = isset( $raw[ $key ] ) ? sanitize_text_field( (string) $raw[ $key ] ) : '';
		}
	}
	return $sanitized;
}

/**
 * Sanitize a nested array recursively.
 *
 * @param array $array The array to sanitize.
 * @return array The sanitized array.
 */
function afgsp_sanitize_nested_array( array $array ): array {
	$sanitized = array();
	foreach ( $array as $key => $value ) {
		$key = sanitize_key( $key );
		if ( is_array( $value ) ) {
			$sanitized[ $key ] = afgsp_sanitize_nested_array( $value );
		} else {
			$sanitized[ $key ] = sanitize_text_field( (string) $value );
		}
	}
	return $sanitized;
}

/**
 * Calculate the number of events (fixtures) per time slot in AUTO mode.
 *
 * This centralizes the scheduling calculation used by both the generator
 * and debug logging to ensure consistent results.
 *
 * @param int $teams_count Number of teams.
 * @param int $days_count  Number of selected match days per gameweek.
 * @param int $slots_count Number of time slots per day.
 * @return int Number of fixtures per time slot.
 */
function afgsp_calculate_events_per_timeslot( int $teams_count, int $days_count, int $slots_count ): int {
	$matches_per_round = max( 1, (int) floor( $teams_count / 2 ) );
	$days_count        = max( 1, $days_count );
	$slots_count       = max( 1, $slots_count );
	$matches_per_day   = (int) ceil( $matches_per_round / $days_count );
	$events_per_slot   = (int) ceil( $matches_per_day / $slots_count );

	return $events_per_slot;
}

/**
 * Sort gameweek days in proper chronological order.
 *
 * Handles week-boundary-crossing scenarios (e.g., Sat-Sun-Mon)
 * where late-week days should come before early-week days.
 *
 * Examples:
 * - [0, 6] (Sun, Sat) => [6, 0] (Sat, Sun)
 * - [0, 1, 6] (Sun, Mon, Sat) => [6, 0, 1] (Sat, Sun, Mon)
 * - [0, 5, 6] (Sun, Fri, Sat) => [5, 6, 0] (Fri, Sat, Sun)
 * - [2, 3] (Tue, Wed) => [2, 3] (Tue, Wed) - no change
 *
 * @param array $days Array of day numbers (0=Sunday, 1=Monday, ..., 6=Saturday).
 * @return array Days sorted in proper gameweek order.
 */
function afgsp_sort_gameweek_days( array $days ): array {
	if ( empty( $days ) ) {
		return array();
	}

	$sorted_days = array_map( 'intval', $days );
	sort( $sorted_days );

	// Check for week boundary crossing (late-week days + early-week days).
	$has_sunday    = in_array( 0, $sorted_days, true );
	$has_monday    = in_array( 1, $sorted_days, true );
	$late_week     = array_filter( $sorted_days, function( $d ) { return $d >= 5; } ); // Fri=5, Sat=6.
	$has_late_week = ! empty( $late_week );

	$crosses_boundary = ( $has_sunday || $has_monday ) && $has_late_week;

	if ( $crosses_boundary ) {
		// Reorder: late-week days first (Fri, Sat), then early-week days (Sun, Mon, etc.).
		$early_week = array_filter( $sorted_days, function( $d ) { return $d <= 4; } ); // Sun=0 to Thu=4.
		sort( $late_week );
		sort( $early_week );
		$sorted_days = array_merge( array_values( $late_week ), array_values( $early_week ) );
	}

	return $sorted_days;
}


