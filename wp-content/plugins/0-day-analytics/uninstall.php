<?php
/**
 * Uninstaller
 *
 * Uninstall the plugin by removing any options from the database
 *
 * @package  0-day
 * @since    1.0
 */

use ADVAN\Helpers\Settings;
use ADVAN\Helpers\PHP_Helper;
use ADVAN\Entities_Global\Common_Table;

// If the uninstall was not called by WordPress, exit.

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'advanced-analytics.php';

// Delete any saved data.
\delete_option( ADVAN_SETTINGS_NAME );
\delete_option( Settings::SETTINGS_VERSION );

$classes = PHP_Helper::get_classes_by_namespace( 'ADVAN\Entities' );
foreach ( $classes as $class ) {
	if ( method_exists( $class, 'get_column_names_admin' ) ) {
		Common_Table::drop_table( null, $class::get_table_name() );
	}
}
