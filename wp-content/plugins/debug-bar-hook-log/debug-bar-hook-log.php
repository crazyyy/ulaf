<?php

/*
 * Plugin Name: Debug Bar Hook Log
 * Description: Logs all actions and filters that are called, along with their arguments
 * Author: CodeAwhile.com
 * Author URI: http://codeawhile.com/
 * Version: 1.0
 */

class Debug_Bar_Hook_Log {
	public static function start() {
		add_filter( 'debug_bar_panels', array( __CLASS__, 'add_debug_panels' ) );
	}

	public static function add_debug_panels( $panels ) {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_resources' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_resources' ) );

		require_once( plugin_dir_path( __FILE__ ) . 'includes/hook-log.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'includes/hook-panel.php' );

		$panels[] = new Debug_Bar_Hook_Panel();

		return $panels;
	}

	public static function enqueue_resources() {
		wp_enqueue_script( 'debug-bar-hook-log', plugins_url( 'resources/debug-bar-hook-log.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'debug-bar-hook-log', plugins_url( 'resources/debug-bar-hook-log.css', __FILE__ ) );
	}

	public static function plugin_dir( $file ) {
		return plugin_dir_path( __FILE__ ) . $file;
	}

}

Debug_Bar_Hook_Log::start();
