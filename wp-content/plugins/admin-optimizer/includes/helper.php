<?php
namespace Yipresser\AdminOptimizer\Helpers;

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Helper {
	/**
	 * Check if the user is an administrator
	 *
	 * @param int $user_id // User ID.
	 *
	 * @return bool
	 */
	protected function is_administrator( int $user_id ) {
		if ( $user_id > 0 ) {
			$user = get_user_by( 'id', $user_id );
			if ( in_array( 'administrator', $user->roles, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a module is activated
	 *
	 * @param string $module_name Module name.
	 * @return boolean
	 */
	private function is_module_activated( string $module_name ) {
		$modules = get_option( MODULES_OPTION, [] );
		if ( ! empty( $module_name ) && ! empty( $modules ) ) {
			return array_key_exists( sanitize_text_field( $module_name ), $modules );
		}
		return false;
	}
}
