<?php
/**
 * Redirect Manager Module
 *
 * This file is part of the premium version of Admin Site Enhancements
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ASENHA_REDIRECT_MANAGER_SLUG', 'redirect-manager' );
define( 'ASENHA_REDIRECT_MANAGER_VERSION', ASENHA_VERSION );

// Require module classes
require_once __DIR__ . '/includes/class-redirect-manager-cpt.php';
require_once __DIR__ . '/includes/class-redirect-manager-admin.php';
require_once __DIR__ . '/includes/class-redirect-manager-list-table.php';
require_once __DIR__ . '/includes/class-redirect-manager-metaboxes.php';
require_once __DIR__ . '/includes/class-redirect-manager-quick-bulk-edit.php';
require_once __DIR__ . '/includes/class-redirect-manager-engine.php';
require_once __DIR__ . '/includes/class-redirect-manager-loop-detection.php';
require_once __DIR__ . '/includes/class-redirect-manager-cache.php';
require_once __DIR__ . '/includes/class-redirect-manager-ajax.php';

/**
 * Initialize Redirect Manager module
 */
function asenha_redirect_manager_init() {
	// Initialize CPT
	$cpt = new ASENHA_Redirect_Manager_CPT();
	$cpt->init();
	
	// Initialize admin features
	if ( is_admin() ) {
		$admin = new ASENHA_Redirect_Manager_Admin();
		$admin->init();
		
		$list_table = new ASENHA_Redirect_Manager_List_Table();
		$list_table->init();
		
		$metaboxes = new ASENHA_Redirect_Manager_Metaboxes();
		$metaboxes->init();
		
		$quick_bulk_edit = new ASENHA_Redirect_Manager_Quick_Bulk_Edit();
		$quick_bulk_edit->init();
		
		$ajax = new ASENHA_Redirect_Manager_Ajax();
		$ajax->init();
	}
	
	// Initialize redirect engine (frontend)
	$engine = new ASENHA_Redirect_Manager_Engine();
	$engine->init();
}

add_action( 'plugins_loaded', 'asenha_redirect_manager_init' );

