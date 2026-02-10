<?php
/**
 * KWLM CONFIG FILE
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 * @copyright MIT License
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

define( 'KWLM_DEBUG', false );

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', defined( 'KWLM_DEBUG' ) ? KWLM_DEBUG : false );
	define( 'WP_DEBUG_LOG', defined( 'WP_DEBUG' ) ? WP_DEBUG : false );
}
