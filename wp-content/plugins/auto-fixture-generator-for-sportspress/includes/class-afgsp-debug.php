<?php
/**
 * Debug and Dry Run functionality for Auto Fixture Generator for SportsPress.
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
 * Class AFGSP_Debug
 *
 * Handles debug mode detection and dry-run logging functionality.
 */
class AFGSP_Debug {

	/**
	 * Log prefix for dry run messages.
	 *
	 * @var string
	 */
	private const LOG_PREFIX = '[AFGSP-DRY-RUN]';

	/**
	 * Check if debug mode is available.
	 *
	 * Debug mode is only available when both WP_DEBUG and WP_DEBUG_LOG
	 * are set to true in wp-config.php.
	 *
	 * @return bool True if debug mode is available, false otherwise.
	 */
	public static function is_debug_mode_available(): bool {
		return ( defined( 'WP_DEBUG' ) && WP_DEBUG === true )
			&& ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG === true );
	}

	/**
	 * Log a single line to the debug log with the dry-run prefix.
	 *
	 * @param string $message The message to log.
	 * @return void
	 */
	private static function log_line( string $message ): void {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( self::LOG_PREFIX . ' ' . $message );
	}

	/**
	 * Log dry run results to debug.log.
	 *
	 * @param array $context The dry run context containing all generation details.
	 * @return void
	 */
	public static function log_dry_run( array $context ): void {
		if ( ! self::is_debug_mode_available() ) {
			return;
		}

		// Header.
		self::log_line( '========================================' );
		self::log_line( 'AFGSP DRY RUN - Fixture Generation Debug' );
		self::log_line( '========================================' );
		self::log_line( 'Timestamp: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC' );
		self::log_line( '' );

		// Plugin Info.
		self::log_line( '--- Plugin Info ---' );
		self::log_line( 'Plugin Version: ' . ( defined( 'AFGSP_VERSION' ) ? AFGSP_VERSION : 'Unknown' ) );
		$license_mode = ( function_exists( 'afgsp_fs' ) && afgsp_fs()->can_use_premium_code__premium_only() ) ? 'Premium' : 'Free';
		self::log_line( 'License Mode: ' . $license_mode );
		self::log_line( '' );

		// Selection.
		self::log_line( '--- Selection ---' );
		$league_id   = isset( $context['league_id'] ) ? (int) $context['league_id'] : 0;
		$season_id   = isset( $context['season_id'] ) ? (int) $context['season_id'] : 0;
		$league_term = get_term( $league_id, 'sp_league' );
		$season_term = get_term( $season_id, 'sp_season' );
		$league_name = ( $league_term && ! is_wp_error( $league_term ) ) ? $league_term->name : 'Unknown';
		$season_name = ( $season_term && ! is_wp_error( $season_term ) ) ? $season_term->name : 'Unknown';
		self::log_line( 'Selected League: ' . $league_name . ' (ID: ' . $league_id . ')' );
		self::log_line( 'Selected Season: ' . $season_name . ' (ID: ' . $season_id . ')' );
		self::log_line( '' );

		// Schedule Settings.
		self::log_line( '--- Schedule Settings ---' );
		$schedule = isset( $context['schedule'] ) && is_array( $context['schedule'] ) ? $context['schedule'] : array();

		// Start Date.
		$start_date = isset( $schedule['start_date'] ) ? (string) $schedule['start_date'] : 'Not set';
		self::log_line( 'Start Date: ' . $start_date );

		// Gameweek Structure (days).
		$days        = isset( $schedule['days'] ) && is_array( $schedule['days'] ) ? $schedule['days'] : array();
		$day_names   = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
		$sorted_days = afgsp_sort_gameweek_days( $days );

		$day_labels = array();
		foreach ( $sorted_days as $day_num ) {
			if ( isset( $day_names[ $day_num ] ) ) {
				$day_labels[] = $day_names[ $day_num ];
			}
		}
		$gameweek_structure = ! empty( $day_labels ) ? implode( ', ', $day_labels ) : 'None';
		self::log_line( 'Gameweek Structure: ' . $gameweek_structure );

		// Gameweek Name Pattern.
		$gameweek_name = isset( $schedule['gameweek_name'] ) ? (string) $schedule['gameweek_name'] : 'Gameweek %No%';
		self::log_line( 'Gameweek Name Pattern: ' . $gameweek_name );

		// Time Slots.
		$time_slots      = isset( $schedule['time_slots'] ) && is_array( $schedule['time_slots'] ) ? $schedule['time_slots'] : array();
		$time_slots_str  = ! empty( $time_slots ) ? implode( ', ', $time_slots ) : 'None';
		self::log_line( 'Time Slots: ' . $time_slots_str );

		// Events per Timeslot.
		$team_ids    = isset( $context['team_ids'] ) && is_array( $context['team_ids'] ) ? $context['team_ids'] : array();
		$events_mode = isset( $schedule['events_mode'] ) ? (string) $schedule['events_mode'] : 'auto';

		if ( 'manual' === $events_mode && ! empty( $schedule['events_per_slot'] ) && is_array( $schedule['events_per_slot'] ) ) {
			$manual_values = implode( ', ', array_map( 'intval', $schedule['events_per_slot'] ) );
			self::log_line( 'Events per Timeslot: ' . $manual_values . ' (MANUAL mode)' );
		} else {
			$events_per_slot = afgsp_calculate_events_per_timeslot( count( $team_ids ), count( $days ), count( $time_slots ) );
			self::log_line( 'Events per Timeslot: ' . $events_per_slot . ' (AUTO mode)' );
		}

		// Blocked Dates.
		$blocked_dates     = isset( $schedule['blocked_dates'] ) && is_array( $schedule['blocked_dates'] ) ? $schedule['blocked_dates'] : array();
		$blocked_dates_str = ! empty( $blocked_dates ) ? implode( ', ', $blocked_dates ) : 'None';
		self::log_line( 'Blocked Dates: ' . $blocked_dates_str );
		self::log_line( '' );

		// Selected Teams (already extracted above for events per slot calculation).
		self::log_line( '--- Selected Teams (' . count( $team_ids ) . ') ---' );
		foreach ( $team_ids as $team_id ) {
			$team_id   = (int) $team_id;
			$team_name = get_the_title( $team_id );
			self::log_line( '  - ' . $team_name . ' (ID: ' . $team_id . ')' );
		}
		self::log_line( '' );

		// Algorithm.
		self::log_line( '--- Algorithm ---' );
		$algorithm_slug  = isset( $context['algorithm'] ) ? (string) $context['algorithm'] : 'Unknown';
		$algorithms      = AFGSP_Registry::get_algorithms();
		$algorithm_label = isset( $algorithms[ $algorithm_slug ]['label'] ) ? $algorithms[ $algorithm_slug ]['label'] : ucfirst( $algorithm_slug );
		self::log_line( 'Selected Algorithm: ' . $algorithm_label . ' (' . $algorithm_slug . ')' );

		// Algorithm-specific options.
		$algorithm_options = isset( $context['algorithm_options'] ) && is_array( $context['algorithm_options'] ) ? $context['algorithm_options'] : array();
		if ( 'fixed-week-season' === $algorithm_slug && isset( $algorithm_options['season_weeks'] ) ) {
			self::log_line( 'Season Weeks: ' . (int) $algorithm_options['season_weeks'] );
		}
		self::log_line( '' );

		// Scheduling Constraints.
		self::log_line( '--- Scheduling Constraints ---' );
		$shuffle_teams       = ! empty( $context['shuffle_teams'] );
		$no_consecutive_away = ! empty( $context['no_consecutive_away'] );
		self::log_line( 'Shuffle Teams: ' . ( $shuffle_teams ? 'Yes' : 'No' ) );
		self::log_line( 'No Consecutive Away: ' . ( $no_consecutive_away ? 'Yes' : 'No' ) );
		self::log_line( '' );

		// Post Processing Actions.
		self::log_line( '--- Post Processing Actions ---' );
		$create_calendar = ! empty( $context['create_calendar'] );
		$calendar_name   = isset( $context['calendar_name'] ) ? (string) $context['calendar_name'] : '';
		$create_table    = ! empty( $context['create_table'] );
		$table_name      = isset( $context['table_name'] ) ? (string) $context['table_name'] : '';
		self::log_line( 'Create Calendar: ' . ( $create_calendar ? ( $calendar_name ? $calendar_name : 'Yes' ) : 'No' ) );
		self::log_line( 'Create League Table: ' . ( $create_table ? ( $table_name ? $table_name : 'Yes' ) : 'No' ) );
		self::log_line( '' );

		// Generated Fixtures.
		$fixtures = isset( $context['fixtures'] ) && is_array( $context['fixtures'] ) ? $context['fixtures'] : array();
		self::log_line( '--- Generated Fixtures (' . count( $fixtures ) . ') ---' );
		foreach ( $fixtures as $fixture ) {
			$home_id   = isset( $fixture['home_id'] ) ? (int) $fixture['home_id'] : 0;
			$away_id   = isset( $fixture['away_id'] ) ? (int) $fixture['away_id'] : 0;
			$home_name = get_the_title( $home_id );
			$away_name = get_the_title( $away_id );

			$extra_meta = isset( $fixture['extra_meta'] ) && is_array( $fixture['extra_meta'] ) ? $fixture['extra_meta'] : array();
			$datetime   = isset( $extra_meta['datetime'] ) ? (string) $extra_meta['datetime'] : '';
			$gameweek   = isset( $extra_meta['sp_day'] ) ? (string) $extra_meta['sp_day'] : '';

			// Parse date, day name, and time from datetime.
			$date     = '';
			$time     = '';
			$day_name = '';
			if ( $datetime ) {
				$parts     = explode( ' ', $datetime );
				$date      = isset( $parts[0] ) ? $parts[0] : '';
				$time      = isset( $parts[1] ) ? $parts[1] : '';
				$timestamp = strtotime( $datetime );
				if ( $timestamp ) {
					$day_name = gmdate( 'l', $timestamp );
				}
			}

			$date_display = $date;
			if ( $day_name ) {
				$date_display .= ' (' . $day_name . ')';
			}

			self::log_line( '  ' . $home_name . ' vs ' . $away_name . ' - ' . $date_display . ' - ' . $time . ' - ' . $gameweek );
		}
		self::log_line( '' );

		// Footer.
		self::log_line( '========================================' );
		self::log_line( 'END OF DRY RUN DEBUG LOG' );
		self::log_line( '========================================' );
	}
}
