<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   CSMTP_Mailer
 * @author    Denys Cherkasov
 * @license   GPL-2.0+
 * @link      https://cherkasov.dev
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$log_table_name = $wpdb->prefix . 'csmtp_logs';
$wpdb->query( "DROP TABLE IF EXISTS `{$log_table_name}`" );

delete_option( 'csmtp_options' );

delete_transient( 'csmtp_last_log_id' );

$cron_hook = 'csmtp_log_cleanup_cron';
$timestamp = wp_next_scheduled( $cron_hook );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, $cron_hook );
}