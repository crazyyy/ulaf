<?php

/**
 * @package Backup_Snippets\Functions
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check requirements
if(!function_exists('backup_snippets_check_requirements')) {
	function backup_snippets_check_requirements() {
		if ( !in_array( 'code-snippets/code-snippets.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$plugins = array(
				'backup-snippets/backup-snippets.php'
			);

			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			deactivate_plugins($plugins);

			set_transient( 'code-snippets-is-required', true, 5 );
		}
	}

	add_action( 'admin_init', 'backup_snippets_check_requirements' );
}