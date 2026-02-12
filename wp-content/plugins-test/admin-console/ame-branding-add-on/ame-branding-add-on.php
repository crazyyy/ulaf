<?php
/*
Plugin Name: AME Branding Add-on
Plugin URI: https://adminmenueditor.com/branding-add-on/
Description: Adds branding options to <em>Admin Menu Editor Pro</em>. It creates three new tabs on the "Settings &rarr; Menu Editor Pro" page: "Branding", "Login", and "Colors".
Version: 1.3.11
Author: Janis Elsts
Author URI: https://adminmenueditor.com/
*/

use YahnisElsts\PluginUpdateChecker\v5p3\PucFactory;

define('AME_BRANDING_ADD_ON_FILE', __FILE__);
define('AME_BRANDING_ADD_ON_DIR', __DIR__);

if ( version_compare(phpversion(), '5.4', '>=') ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

class ameBrandingAddOn {
	/**
	 * @var YahnisElsts\PluginUpdateChecker\v5p3\Plugin\UpdateChecker
	 */
	private $updateChecker;

	/**
	 * @var Wslm_LicenseManagerClient
	 */
	private $licenseManager;

	public function __construct() {
		add_filter('admin_menu_editor-available_modules', array($this, 'registerModules'));
		add_action('admin_menu_editor-register_scripts', array($this, 'registerScripts'));

		if ( !defined('IS_DEMO_MODE') && !defined('IS_MASTER_MODE') ) {
			//Add-ons use the same license as the main plugin.
			require AME_BRANDING_ADD_ON_DIR . '/includes/plugin-update-checker/plugin-update-checker.php';
			$this->updateChecker = PucFactory::buildUpdateChecker(
				'http://adminmenueditor.com/?get_metadata_for=ame-branding-add-on',
				AME_BRANDING_ADD_ON_FILE
			);

			if ( isset($GLOBALS['ameProLicenseManager']) ) {
				$this->licenseManager = $GLOBALS['ameProLicenseManager'];
				$this->licenseManager->addUpdateFiltersTo($this->updateChecker);
			}
		}
	}

	public function registerModules($modules) {
		//All add-on modules will require the "Customizables" classes.
		if ( !defined('AME_HAS_CUSTOMIZABLES') ) {
			return $modules;
		}

		$modules = array_merge($modules, array(
			'branding'     => array(
				'path'               => __DIR__ . '/modules/branding/branding.php',
				'className'          => 'ameBrandingEditor',
				'title'              => 'Branding add-on: Branding',
				'requiredPhpVersion' => '5.6',
			),
			'login-page'   => array(
				'path'               => __DIR__ . '/modules/login-page/login-page.php',
				'className'          => 'ameLoginPageCustomizer',
				'title'              => 'Branding add-on: Login',
				'requiredPhpVersion' => '5.6',
			),
			'admin-colors' => array(
				'path'               => __DIR__ . '/modules/admin-colors/admin-colors.php',
				'className'          => 'ameBrandingColors',
				'title'              => 'Branding add-on: Colors',
				'requiredPhpVersion' => '5.6',
			),
		));
		return $modules;
	}

	public function registerScripts() {
		//Note: You still need to call wp_enqueue_media() for this script to work.
		wp_register_auto_versioned_script(
			'ame-branding-image-selector',
			plugins_url('assets/image-selector.js', __FILE__),
			array('jquery')
		);
	}

	public static function maybeCreateInstance() {
		//Is the core plugin active?
		$isAmeProActive = class_exists('WPMenuEditor') && apply_filters('admin_menu_editor_is_pro', false);
		if ( !$isAmeProActive ) {
			add_action('admin_notices', array(__CLASS__, 'displayDependencyError'));
			return null;
		}

		//Is the core plugin recent enough? The modules in this add-on inherit from
		//the "persistent module (Pro version)" class that's introduced in 2.7.
		if ( !class_exists('amePersistentProModule') ) {
			add_action('admin_notices', array(__CLASS__, 'displayPluginVersionError'));
			return null;
		}

		return new self();
	}

	public static function displayDependencyError() {
		if ( !current_user_can('activate_plugins') ) {
			return;
		}
		print(
		'<div class="notice notice-error">
			<p>
				<strong>AME Branding Add-on is disabled.</strong>
				Please install and activate Admin Menu Editor Pro to use this add-on.
			</p>
		</div>'
		);
	}

	public static function displayPluginVersionError() {
		if ( !current_user_can('update_plugins') ) {
			return;
		}

		print(
		'<div class="notice notice-error">
			<p>
				<strong>AME Branding Add-on is disabled.</strong>
				This add-on requires at least Admin Menu Editor Pro version 2.7.
			</p>
		</div>'
		);
	}
}

add_action('plugins_loaded', array('ameBrandingAddOn', 'maybeCreateInstance'));