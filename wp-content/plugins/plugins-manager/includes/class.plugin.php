<?php

namespace WTBP;

/*
 * Main plugin class
 *
 * */

class Plugin extends Plugin_Base {

	/**
	 * Settings class
	 *
	 * @var Page_Settings
	 */
	public $settings;

	/**
	 * Plugin constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {
		parent::__construct();

		$customizer = new PluginPageCustomizer();
	}

	/**
	 * Admin code
	 */
	public function admin_code() {
		$this->register_page( 'Page_Settings' );
	}


}