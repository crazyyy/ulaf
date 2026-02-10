<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish_Missed_Schedule class
 */
class Publish_Missed_Schedule {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init(): void {
		add_action( 'init', [ $this, 'schedule_check_missed_post' ] );

		add_action( 'adminoptim_publish_missed_post', [ $this, 'publish_missed_posts' ] );
		add_action( 'adminoptim_deactivate_plugin', [ $this, 'remove_schedule_on_deactivation' ] );

		add_filter(
			'action_scheduler_queue_runner_concurrent_batches',
			function () {
				return 2;
			}
		);
	}

	/**
	 * Set schedule to check for missed post
	 *
	 * @return void
	 */
	public function schedule_check_missed_post(): void {
		if ( ! as_has_scheduled_action( 'adminoptim_publish_missed_post' ) ) {
			as_schedule_recurring_action( time(), 900, 'adminoptim_publish_missed_post', [], '', true );
		}
	}

	/**
	 * Deactivate schedule
	 *
	 * @return void
	 */
	public function remove_schedule_on_deactivation(): void {
		if ( as_has_scheduled_action( 'adminoptim_publish_missed_post' ) ) {
			as_unschedule_action( 'adminoptim_publish_missed_post' );
		}
	}

	/**
	 * Publish missed posts
	 *
	 * @return void
	 */
	public function publish_missed_posts(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$missed_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_date_gmt > 0 AND post_date_gmt < UTC_TIMESTAMP() AND post_status = 'future'" );
		if ( ! count( $missed_ids ) ) {
			return;
		}
		foreach ( $missed_ids as $missed_id ) {
			if ( ! $missed_id ) {
				continue;
			}
			wp_publish_post( $missed_id ); // Let's publish missed schedule posts.
		}
	}
}
