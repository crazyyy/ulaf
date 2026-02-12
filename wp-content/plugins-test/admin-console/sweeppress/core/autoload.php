<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dev4press_plugin_sweeppress_autoload( $class ) {
	$path = __DIR__ . '/';
	$base = 'Dev4Press\\Plugin\\SweepPress\\';

	dev4press_v54_autoload_for_plugin( $class, $base, $path );
}

spl_autoload_register( 'dev4press_plugin_sweeppress_autoload' );
