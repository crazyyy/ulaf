<?php
/**
 * Bulk Updater Functions
 *
 * @since 4.0
 */

// Exit if accessed directly.
if ( ! defined('ABSPATH') ) exit;

// Start bulk updater on ajax call.
add_action( 'wp_ajax_iaffpro_bu_bulk_updater_init', 'iaffpro_bu_bulk_updater_wp_ajax_init' );

// Stop the bulk updater on ajax call.
add_action( 'wp_ajax_iaffpro_bu_stop_bulk_updater', 'iaffpro_bu_stop_bulk_updater' );

// Admin Notice.
add_action( 'admin_notices', 'iaffpro_bu_bulk_update_in_progress_admin_notice' );

/**
 * Run Image Attributes Pro Bulk Updater.
 * 
 * @since 4.0
 * 
 * @param $batch_size (int) Defines the number of images that will be processed in a single call to the function. Higher batch sizes can lead to PHP time outs.
 */
function iaffpro_bu_run_bulk_updater( $batch_size = 20 ) {

	$bu_start_time = time();

	$remaining_images = iaff_count_remaining_images();

	// Return if all images are updated. 
	if ( $remaining_images === 0 ) {
		return;
	}

	$maybe_batch_size = get_transient( 'iaffpro_bu_batch_size' );

	if ( ( $maybe_batch_size !== false ) && ( $maybe_batch_size > 20 ) ) {
		$batch_size = $maybe_batch_size;
	}

	/**
	 * Filter the Bulk Updater batch size.
	 * 
	 * @since 4.1
	 * 
	 * @param $batch_size (int) Defines the number of images that will be processed in a single call to the function. Higher batch sizes can lead to PHP time outs.
	 */
	$batch_size = apply_filters( 'iaffpro_bu_batch_size', $batch_size );

	$batch_size = ( $remaining_images < $batch_size ) ? $remaining_images : intval( $batch_size );

	// Retrieve Counter
	$counter = get_option( 'iaff_bulk_updater_counter' );
	$counter = intval( $counter );

	global $wpdb;
	$images = $wpdb->get_results( "SELECT ID, post_parent FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND post_mime_type LIKE 'image%' ORDER BY post_date LIMIT {$batch_size} OFFSET {$counter}" );

	foreach( $images as $image ) {

		// Die if no image
		if ( $image === NULL ) {
			continue;
		}

		// Running the pro module
		iaffpro_auto_image_attributes_pro( $image, true );
	}

	// Increment counter and update it
	$counter = $counter + $batch_size;
	update_option( 'iaff_bulk_updater_counter', $counter );

	/**
	 * Increase batch size by 20 images if the last update took less than 20 seconds.
	 * 20 is chosen to stay under the 30 second php timeout that most hosts use.
	 * 
	 * If it takes more than 20 seconds, then decrease batch size by 10.
	 */
	if ( ( time() - $bu_start_time ) <= 20 ) {
		set_transient( 'iaffpro_bu_batch_size', $batch_size + 20, HOUR_IN_SECONDS );
	} else {
		set_transient( 'iaffpro_bu_batch_size', $batch_size - 10, HOUR_IN_SECONDS );
	}
}

/**
 * Start the bulk update process.
 * 
 * Hooks on to wp_ajax_iaffpro_bu_bulk_updater_init to start on Ajax call from 
 * "Run Bulk Updater" button in "Bulk Updater" tab.
 * 
 * @since 4.0
 */
function iaffpro_bu_bulk_updater_init() {

	/**
	 * Action hook that is fired at the start of the 
	 * Bulk Updater before updating any image.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_before_bulk_updater/
	 */
	do_action( 'iaff_before_bulk_updater' );

	iaffpro_bu_bulk_updater_process();

	if ( as_has_scheduled_action( 'iaffpro_bu_bulk_updater' ) === false ) {
		
		/**
		 * This makes sure that the process runs until it's complete.
		 * 
		 * Even if PHP times out it between, every 5 minutes we check if the bulk updater is running.
		 * That way the bulk updater will re-spawn and continue from where it left off.
		 */
		as_schedule_recurring_action( time(), 300, 'iaffpro_bu_bulk_updater' );
	}
}

/**
 * Run the bulk updater and schedule next iteration until all images are done.
 * 
 * @since 4.0
 */
function iaffpro_bu_bulk_updater_process() {
	
	// Run the bulk updater.
	iaffpro_bu_run_bulk_updater();

	// Iterate until all images are done.
	if ( iaff_count_remaining_images() > 0 ) {
		as_enqueue_async_action( 'iaffpro_bu_bulk_updater_process' );
	} 
	// All images done.
	else {

		/**
		 * Action hook that is fired at the end of the 
		 * Bulk Updater after updating all images.
		 * 
		 * @link https://imageattributespro.com/codex/iaff_after_bulk_updater/
		 */
		do_action( 'iaff_after_bulk_updater' );
		
		as_unschedule_all_actions( 'iaffpro_bu_bulk_updater' );

		delete_transient( 'iaffpro_bu_batch_size' );
	}
}
add_action( 'iaffpro_bu_bulk_updater_process', 'iaffpro_bu_bulk_updater_process' );

/**
 * Respawn bulk updater process if it dies out prematurely.
 * 
 * If for some reason iaffpro_bu_bulk_updater_process is cleared off before all images are completed,
 * then iaffpro_bu_bulk_updater action will call here and restart the process. 
 * 
 * When all images are completed, iaffpro_bu_bulk_updater action is unscheduled.
 * 
 * @since 4.0
 */
function iaffpro_bu_bulk_updater_respawn() {

	if ( as_has_scheduled_action( 'iaffpro_bu_bulk_updater_process' ) === false ) {
		as_enqueue_async_action( 'iaffpro_bu_bulk_updater_process' );
	} 
}
add_action( 'iaffpro_bu_bulk_updater', 'iaffpro_bu_bulk_updater_respawn' );

/**
 * Wrapper for iaffpro_bu_bulk_updater_init() to call it via ajax.
 * 
 * @since 4.0
 */
function iaffpro_bu_bulk_updater_wp_ajax_init() {
	
	// Security Check
	check_ajax_referer( 'iaffpro_bu_bulk_updater_init_nonce', 'security' );

	iaffpro_bu_bulk_updater_init();

	wp_die();
}

/**
 * Stop the bulk updater on clicking "Stop Bulk Updater" button.
 * 
 * @since 4.0
 */
function iaffpro_bu_stop_bulk_updater() {

	// Security Check
	check_ajax_referer( 'iaffpro_bu_stop_bulk_updater_nonce', 'security' );

	as_unschedule_all_actions( 'iaffpro_bu_bulk_updater_process' );
	as_unschedule_all_actions( 'iaffpro_bu_bulk_updater' );

	wp_die();
}

/**
 * Admin notice when bulk update is in progress.
 * 
 * @since 4.0
 */
function iaffpro_bu_bulk_update_in_progress_admin_notice() {

	// Display only on Image Attributes Pro settings page.
	$screen = get_current_screen();
	if ( $screen->id !== 'settings_page_image-attributes-from-filename' ) {
		return;
	}

	// Proceed only when bulk update is in progress.
	if ( as_has_scheduled_action( 'iaffpro_bu_bulk_updater' ) === false ) {
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( 
		__( '<strong>Image Attributes Pro:</strong> Bulk Update is in progress. Please be patient. %s / %s images processed.', 'auto-image-attributes-pro' ),
		iaff_number_of_images_updated(),
		iaff_total_number_of_images()
		) . '</p></div>';
}