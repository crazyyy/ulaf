<?php
/**
 * Authority Mailer Database Setup
 *
 * Centralized database table creation for all plugin tables.
 * Tables are created only during plugin activation.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Authority_Mailer_Database_Setup' ) ) {

	/**
	 * Authority_Mailer_Database_Setup class.
	 *
	 * Handles creation of all database tables during plugin activation.
	 *
	 * @since 1.0.0
	 */
	class Authority_Mailer_Database_Setup {

		/**
		 * Create all plugin database tables.
		 *
		 * This method should be called only during plugin activation.
		 * It creates all tables needed by both free and pro versions.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function create_all_tables() {
			global $wpdb;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$charset_collate = $wpdb->get_charset_collate();

			// Create email log table (free feature).
			self::create_email_log_table( $wpdb, $charset_collate );

			// Create pro tables.
			self::create_email_events_table( $wpdb, $charset_collate );
			self::create_tracking_links_table( $wpdb, $charset_collate );
			self::create_tracking_pixels_table( $wpdb, $charset_collate );
			self::create_recipient_profiles_table( $wpdb, $charset_collate );
			self::create_email_health_scores_table( $wpdb, $charset_collate );
			self::create_provider_performance_table( $wpdb, $charset_collate );
			self::create_ab_tests_table( $wpdb, $charset_collate );
			self::create_suppression_list_table( $wpdb, $charset_collate );
			self::create_notification_log_table( $wpdb, $charset_collate );
			self::create_consent_log_table( $wpdb, $charset_collate );
		}

		/**
		 * Create email log table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_email_log_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_email_log';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
				provider VARCHAR(100) NOT NULL,
				to_email VARCHAR(255) DEFAULT '',
				from_email VARCHAR(255) DEFAULT '',
				from_name VARCHAR(255) DEFAULT '',
				subject TEXT,
				headers LONGTEXT,
				body LONGTEXT,
				payload LONGTEXT,
				response_code INT DEFAULT NULL,
				response_body LONGTEXT,
				status VARCHAR(50) DEFAULT 'attempt' COMMENT 'attempt|accepted|delivered|bounced|spam_complaint|error',
				spam_score FLOAT DEFAULT NULL,
				spam_details TEXT DEFAULT NULL,
				sent_at DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY provider (provider),
				KEY created_at (created_at),
				KEY sent_at (sent_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create email events table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_email_events_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_email_events';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email_log_id BIGINT(20) UNSIGNED DEFAULT NULL,
				event_type VARCHAR(50) NOT NULL,
				tracking_id VARCHAR(100) NOT NULL,
				recipient_email VARCHAR(255) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				ip_address VARCHAR(45) DEFAULT NULL,
				user_agent TEXT DEFAULT NULL,
				country VARCHAR(100) DEFAULT NULL,
				city VARCHAR(100) DEFAULT NULL,
				device_type VARCHAR(50) DEFAULT NULL,
				browser VARCHAR(100) DEFAULT NULL,
				os VARCHAR(100) DEFAULT NULL,
				metadata LONGTEXT DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY email_log_id (email_log_id),
				KEY event_type (event_type),
				KEY tracking_id (tracking_id),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create tracking links table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_tracking_links_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_tracking_links';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email_log_id BIGINT(20) UNSIGNED DEFAULT NULL,
				tracking_id VARCHAR(100) NOT NULL,
				original_url TEXT NOT NULL,
				short_code VARCHAR(50) NOT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				click_count INT DEFAULT 0,
				PRIMARY KEY  (id),
				UNIQUE KEY short_code (short_code),
				KEY email_log_id (email_log_id),
				KEY tracking_id (tracking_id)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create tracking pixels table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_tracking_pixels_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_tracking_pixels';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email_log_id BIGINT(20) UNSIGNED DEFAULT NULL,
				tracking_id VARCHAR(100) NOT NULL,
				recipient_email VARCHAR(255) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				open_count INT DEFAULT 0,
				first_opened_at DATETIME DEFAULT NULL,
				last_opened_at DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY tracking_id (tracking_id),
				KEY email_log_id (email_log_id),
				KEY recipient_email (recipient_email)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create recipient profiles table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_recipient_profiles_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_recipient_profiles';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email VARCHAR(255) NOT NULL,
				emails_sent INT DEFAULT 0,
				opens INT DEFAULT 0,
				clicks INT DEFAULT 0,
				bounces INT DEFAULT 0,
				status VARCHAR(50) DEFAULT 'active',
				score INT DEFAULT 50,
				segment VARCHAR(50) DEFAULT 'new',
				tags LONGTEXT DEFAULT NULL,
				first_seen DATETIME DEFAULT NULL,
				last_sent DATETIME DEFAULT NULL,
				last_opened DATETIME DEFAULT NULL,
				last_clicked DATETIME DEFAULT NULL,
				last_activity DATETIME DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				KEY status (status),
				KEY segment (segment),
				KEY score (score)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create email health scores table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_email_health_scores_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_email_health_scores';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				score INT NOT NULL,
				grade VARCHAR(2) NOT NULL,
				metrics LONGTEXT DEFAULT NULL,
				domain_health LONGTEXT DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create provider performance table.
		 *
		 * Stores discrete performance events (success/failure) for each provider.
		 * Events are aggregated in queries to calculate metrics like success rate.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_provider_performance_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_provider_performance';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				provider_id VARCHAR(100) NOT NULL,
				event_type VARCHAR(50) NOT NULL,
				error_code VARCHAR(100) DEFAULT NULL,
				error_msg TEXT DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY provider_id (provider_id),
				KEY event_type (event_type),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create A/B tests table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_ab_tests_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_ab_tests';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				test_name VARCHAR(255) NOT NULL,
				variant_a_subject TEXT NOT NULL,
				variant_b_subject TEXT NOT NULL,
				variant_a_sent INT DEFAULT 0,
				variant_b_sent INT DEFAULT 0,
				variant_a_opens INT DEFAULT 0,
				variant_b_opens INT DEFAULT 0,
				variant_a_clicks INT DEFAULT 0,
				variant_b_clicks INT DEFAULT 0,
				winner VARCHAR(10) DEFAULT NULL,
				status VARCHAR(50) DEFAULT 'active',
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				completed_at DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY status (status),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create suppression list table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_suppression_list_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_suppression_list';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email VARCHAR(255) NOT NULL,
				reason VARCHAR(100) NOT NULL,
				details TEXT DEFAULT NULL,
				source VARCHAR(100) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				KEY reason (reason),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create notification log table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_notification_log_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_notification_log';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				notification_type VARCHAR(100) NOT NULL,
				channel VARCHAR(50) NOT NULL,
				recipient VARCHAR(255) NOT NULL,
				message TEXT NOT NULL,
				status VARCHAR(50) DEFAULT 'pending',
				sent_at DATETIME DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY notification_type (notification_type),
				KEY channel (channel),
				KEY status (status),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

		/**
		 * Create consent log table.
		 *
		 * @since 1.0.0
		 * @param wpdb   $wpdb            WordPress database object.
		 * @param string $charset_collate Charset collation string.
		 * @return void
		 */
		private static function create_consent_log_table( $wpdb, $charset_collate ) {
			$table_name = $wpdb->prefix . 'am_consent_log';

			$sql = "CREATE TABLE `{$table_name}` (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email VARCHAR(255) NOT NULL,
				consent_type VARCHAR(100) NOT NULL,
				consent_given TINYINT(1) DEFAULT 1,
				ip_address VARCHAR(45) DEFAULT NULL,
				user_agent TEXT DEFAULT NULL,
				source VARCHAR(100) DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY email (email),
				KEY consent_type (consent_type),
				KEY created_at (created_at)
			) {$charset_collate};";

			dbDelta( $sql );
		}

	}
}
