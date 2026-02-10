<?php
/**
 * Plugin Class
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 */

namespace KolorWeb\KWLogManager;

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}


/**
 * Dependencies
 */
use KolorWeb\KWLogManager\Characteristic\IsSingleton;


/**
 * Settings handler
 *
 * @since 1.0.0
 */
class Settings {

	use IsSingleton;


	/**
	 * Default global settings.
	 *
	 * @var $global_fields store global settings.
	 * @since 1.0.0
	 */
	private $global_fields = array(
		'view'              => 'group',
		'sort'              => 'newest',
		'query'             => '',
		'custom_errors'     => '',
		'legends'           => '',
		'fold_sidebar'      => 1,
		'truncate_download' => 1,
		'log_limit'         => 0,
	);


	/**
	 * Default user settings
	 *
	 * @var $user_fields store user settings.
	 * @since 1.0.0
	 */
	private $user_fields = array(
		'view'              => 'group',
		'sort'              => 'newest',
		'query'             => '',
		'legends'           => '',
		'fold_sidebar'      => 1,
		'truncate_download' => 1,
		'log_limit'         => 0, // 1.1.1
	);


	/**
	 * Get all settings.  If user id is provided, user and global settings will be merged and returned.  If no user id is provided, only global settings are returned.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user id for the user to get settings for.
	 * @return array
	 */
	public function get_settings( $user_id = 0 ) {

		$global_settings = $this->get_global_settings();
		$global_settings = $this->merge_settings( $this->global_fields, $global_settings, array_keys( $this->global_fields ) );

		if ( $user_id > 0 ) {
			$user_settings = $this->get_user_settings( $user_id );
			$user_settings = $this->merge_settings( $this->user_fields, $user_settings, array_keys( $this->user_fields ) );

			return array_merge( $user_settings, array_diff_key( $global_settings, $user_settings ) );
		} else {
			return $global_settings;
		}
	}


	/**
	 * Get global settings
	 *
	 * @since 1.0.0
	 *
	 * @return array|false
	 */
	public function get_global_settings() {
		return \get_option( '_kwlm_settings', false );
	}


	/**
	 * Update global settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_settings The settings to set.
	 * @return boolean
	 */
	public function update_global_settings( $new_settings ) {

		$settings = $this->get_settings();
		$settings = $this->merge_settings( $settings, $new_settings, array_keys( $this->global_fields ) );
		$updated  = \update_option( '_kwlm_settings', $settings );

		return $updated;
	}


	/**
	 * Get settings for a user
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user id for the user to get settings for.
	 * @return object|false
	 */
	public function get_user_settings( $user_id ) {

		$settings = \get_user_meta( $user_id, '_kwlm_settings', true );

		return empty( $settings ) ? false : $settings;
	}


	/**
	 * Update settings for a user
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id The user id of the user to get settings for.
	 * @param array $new_settings The settings to set.
	 * @return boolean
	 */
	public function update_user_settings( $user_id, $new_settings ) {

		$settings = $this->get_settings( $user_id );
		$settings = $this->merge_settings( $settings, $new_settings, array_keys( $this->user_fields ) );

		return \update_user_meta( $user_id, '_kwlm_settings', $settings );
	}


	/**
	 * Merge key and value into global array if it's not currently present set.  If it is already set, update the value
	 *
	 * @since 1.0.0
	 *
	 * @param array $global The array to merge into or update.
	 * @param array $new The array to merge.
	 * @param array $allowed The allowed fields.
	 * @return array
	 */
	private function merge_settings( $global, $new, $allowed ) {

		if ( is_array( $new ) && count( $new ) > 0 ) {
			foreach ( $new as $key => $value ) {
				if ( in_array( $key, $allowed, true ) ) {
					$global[ $key ] = $value;
				}
			}
		}

		return $global;
	}

	/**
	 * Clean Settings Data
	 *
	 * @since 1.0.0
	 *
	 * @param array  $settings Settings array.
	 * @param string $callback calback function.
	 * @return array
	 */
	public function clean_settings( $settings, $callback ) {

		$new = array();

		foreach ( $settings as $key => $val ) {
			if ( is_array( $val ) ) {
				$new[ $key ] = $this->clean_settings( $val, $callback );
			} else {
				$new[ $key ] = call_user_func( $callback, $val );
			}
		}

		return $new;
	}
}
