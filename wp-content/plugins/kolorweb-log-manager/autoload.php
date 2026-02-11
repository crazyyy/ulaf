<?php
/**
 * Autoloader.
 *
 * @package kw-log-manager
 * Autoload Dependencies.
 */

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

require_once 'vendor/autoload.php';

/**
 * Register autoloader for this project
 *
 * @since 0.1.0
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(

	function( $class ) {

		// project-specific namespace prefix.
		$prefix = 'KolorWeb\\KWLogManager\\';

		// base directory for the namespace prefix.
		$base_dir = __DIR__ . '/libs/';

		// If the class doesn't use the namespace prefix continue to next autoloader.
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		if ( 'Characteristic\IsSingleton' === $relative_class ) {
			$relative_class = 'Characteristic\is-singleton';
		} else {
			$relative_class = 'class-' . strtolower( $relative_class );
		}

		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}

	}
);
