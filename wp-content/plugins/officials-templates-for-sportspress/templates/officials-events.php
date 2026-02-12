<?php
/**
 * Official Events.
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( get_option( 'sportspress_officials_show_events', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $official_id ) ) {
	$official_id = get_the_ID();
}

$format = get_option( 'otfs_officials_events_format', 'list' );
if ( 'list' === $format ) {
	$args = array(
		'official_id'  => $official_id,
		'title_format' => 'homeaway',
		'time_format'  => 'separate',
		'columns'      => array( 'event', 'time', 'results' ),
		'order'        => 'DESC',
	);
	$args = apply_filters( 'otfs_official_events_list_args', $args );
	sp_get_template( 'otfs-event-list.php', $args, '', OTFS_PLUGIN_DIR . 'templates/' );
} else {
	sp_get_template( 'otfs-event-fixtures-results.php', array( 'official_id' => $official_id ), '', OTFS_PLUGIN_DIR . 'templates/' );
}
