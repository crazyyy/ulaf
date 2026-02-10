<?php
/**
 * Authority Mailer Dashboard Stats with Caching
 *
 * Provides cached statistics for the dashboard to improve performance.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard Statistics Class
 *
 * Handles fetching and caching of dashboard statistics with transient support.
 */
class Authority_Mailer_Dashboard_Stats {

	/**
	 * Cache duration in seconds (5 minutes)
	 *
	 * @var int
	 */
	const CACHE_DURATION = 300;

	/**
	 * Transient key for dashboard stats cache
	 *
	 * @var string
	 */
	const CACHE_KEY = 'authority_mailer_dashboard_stats';

	/**
	 * Get dashboard statistics with caching
	 *
	 * @return array Array of statistics including daily, weekly, monthly data and summary stats
	 */
	public static function get_stats() {
		// Try to get from cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Cache miss - fetch fresh data.
		$stats = self::fetch_fresh_stats();

		// Cache the results.
		set_transient( self::CACHE_KEY, $stats, self::CACHE_DURATION );

		return $stats;
	}

	/**
	 * Fetch fresh statistics from database
	 *
	 * @return array Array of statistics
	 */
	private static function fetch_fresh_stats() {
		global $wpdb;
		$table = $wpdb->prefix . 'am_email_log';

		// Check if table exists.
		$cache_key    = 'authority_mailer_smtp_table_exists_' . $table;
		$table_exists = wp_cache_get( $cache_key );

		if ( false === $table_exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table;
			wp_cache_set( $cache_key, $table_exists, '', 3600 );
		}

		if ( ! $table_exists ) {
			return self::get_empty_stats();
		}

		$today          = gmdate( 'Y-m-d 00:00:00' );
		$seven_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

		// Table name is safe: constructed from $wpdb->prefix (validated by WordPress core) + hardcoded string.
		// Using esc_sql() as table names cannot be parameterized with $wpdb->prepare() placeholders.
		$escaped_table = esc_sql( $table );

		// Fetch summary stats for last 7 days.
		$stats = array(
			'total_7d'    => 0,
			'success_7d'  => 0,
			'failed_7d'   => 0,
			'pending_7d'  => 0,
			'total_today' => 0,
		);

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$stats['total_7d']    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s", $seven_days_ago ) );
		$stats['success_7d']  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND status IN ('success', 'accepted')", $seven_days_ago ) );
		$stats['failed_7d']   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND status = 'error'", $seven_days_ago ) );
		$stats['pending_7d']  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND status = 'attempt'", $seven_days_ago ) );
		$stats['total_today'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s", $today ) );
		$stats['total_all']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$escaped_table}`" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Fetch daily stats (last 7 days).
		$daily_stats = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$day_start = gmdate( 'Y-m-d 00:00:00', strtotime( "-$i days" ) );
			$day_end   = gmdate( 'Y-m-d 23:59:59', strtotime( "-$i days" ) );
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$day_total   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s", $day_start, $day_end ) );
			$day_success = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status IN ('success', 'accepted')", $day_start, $day_end ) );
			$day_failed  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'error'", $day_start, $day_end ) );
			$day_pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'attempt'", $day_start, $day_end ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$daily_stats[] = array(
				'label'   => gmdate( 'D', strtotime( "-$i days" ) ),
				'date'    => gmdate( 'M j', strtotime( "-$i days" ) ),
				'total'   => $day_total,
				'success' => $day_success,
				'failed'  => $day_failed,
				'pending' => $day_pending,
			);
		}

		// Fetch weekly stats (last 4 weeks).
		$weekly_stats = array();
		for ( $i = 3; $i >= 0; $i-- ) {
			$days_ago_start = ( $i + 1 ) * 7;
			$days_ago_end   = $i * 7;
			$week_start     = gmdate( 'Y-m-d 00:00:00', strtotime( "-{$days_ago_start} days" ) );
			$week_end       = gmdate( 'Y-m-d 23:59:59', strtotime( "-{$days_ago_end} days" ) );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$week_total   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s", $week_start, $week_end ) );
			$week_success = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status IN ('success', 'accepted')", $week_start, $week_end ) );
			$week_failed  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'error'", $week_start, $week_end ) );
			$week_pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'attempt'", $week_start, $week_end ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$weekly_stats[] = array(
				'label'   => gmdate( 'M j', strtotime( $week_start ) ),
				'total'   => $week_total,
				'success' => $week_success,
				'failed'  => $week_failed,
				'pending' => $week_pending,
			);
		}

		// Fetch monthly stats (last 12 months).
		$monthly_stats = array();
		for ( $i = 11; $i >= 0; $i-- ) {
			$month_start = gmdate( 'Y-m-01 00:00:00', strtotime( "-$i months" ) );
			$month_end   = gmdate( 'Y-m-t 23:59:59', strtotime( "-$i months" ) );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$month_total   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s", $month_start, $month_end ) );
			$month_success = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status IN ('success', 'accepted')", $month_start, $month_end ) );
			$month_failed  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'error'", $month_start, $month_end ) );
			$month_pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$escaped_table}` WHERE created_at >= %s AND created_at <= %s AND status = 'attempt'", $month_start, $month_end ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$monthly_stats[] = array(
				'label'   => gmdate( 'M Y', strtotime( $month_start ) ),
				'total'   => $month_total,
				'success' => $month_success,
				'failed'  => $month_failed,
				'pending' => $month_pending,
			);
		}

		return array(
			'summary' => $stats,
			'daily'   => $daily_stats,
			'weekly'  => $weekly_stats,
			'monthly' => $monthly_stats,
		);
	}

	/**
	 * Get empty stats structure
	 *
	 * @return array Empty stats structure
	 */
	private static function get_empty_stats() {
		return array(
			'summary' => array(
				'total_7d'    => 0,
				'success_7d'  => 0,
				'failed_7d'   => 0,
				'pending_7d'  => 0,
				'total_today' => 0,
				'total_all'   => 0,
			),
			'daily'   => array(),
			'weekly'  => array(),
			'monthly' => array(),
		);
	}

	/**
	 * Clear the dashboard stats cache
	 *
	 * @return bool True if cache was cleared successfully
	 */
	public static function clear_cache() {
		return delete_transient( self::CACHE_KEY );
	}
}
