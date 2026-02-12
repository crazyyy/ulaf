<?php
/**
 * Listing all tools to protect the website.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if accessed directly. 
}

include 'tools/security-headers.php';
include 'tools/disable-xmlrpc.php';
include 'tools/svg-remove.php';
include 'tools/remove-inner-block.php';