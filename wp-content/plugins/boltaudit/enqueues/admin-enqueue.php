<?php

use BoltAudit\WpMVC\Enqueue\Enqueue;

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard Scripts
 */
if ( 'tools_page_boltaudit' === $hook_suffix ) {
	Enqueue::script( 'boltaudit-app-script', 'build/js/app' );
	Enqueue::style( 'boltaudit-app-style', 'build/css/app' );

	wp_localize_script(
		'boltaudit-app-script', 'boltaudit_data', [
			'siteUrl'        => site_url(),
			'siteName'       => wp_parse_url( site_url( '' ), PHP_URL_HOST ),
			'hasWooCommerce' => is_plugin_active( 'woocommerce/woocommerce.php' ),
		]
	);
}