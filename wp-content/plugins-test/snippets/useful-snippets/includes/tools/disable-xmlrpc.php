<?php
/**
 * Disable XML-RPC
 * This function removes serious security vulnerabilities to your site.
 * 
 * @since 1.0.0 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if accessed directly. 
}

add_filter('xmlrpc_enabled', '__return_false');