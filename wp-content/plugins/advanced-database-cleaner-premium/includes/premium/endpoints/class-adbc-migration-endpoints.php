<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Migration Endpoints.
 * 
 * This class provides the endpoints (controllers) for the migration routes.
 */
class ADBC_Migration_Endpoints {

	/**
	 * Get the available migration data.
	 *
	 * Uses the current settings to determine whether to check 'all' data
	 * or only 'pro' data. Returns a JSON response with the list of available
	 * items to migrate.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public static function get_available_migration_data() {

		try {

			// Check if the migration notice is dismissed
			if ( ADBC_Notifications::instance()->is_notification_dismissed( 'migration_available' ) ) {
				return ADBC_Rest::error( 'Import is already done before.', ADBC_Rest::BAD_REQUEST );
			}

			$data_type = ADBC_Settings::instance()->get_setting( 'free_migration_done' ) === '1' ? 'pro' : 'all';
			$available_data = ADBC_Migration::get_available_migration_data( $data_type );

			return ADBC_Rest::success( "", $available_data );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Migrate the data.
	 *
	 * Expects body params:
	 * - items_to_migrate: array of strings, subset of ['keep_last','automation_tasks','manual_corrections']
	 * - uninstall_old_versions: boolean
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public static function migrate_data( WP_REST_Request $request ) {

		try {

			// Check if there is a migration available
			if ( ! in_array( 'migration_available', array_keys( ADBC_Notifications::instance()->get_local_notifications() ) ) ) {
				return ADBC_Rest::error( 'No migration available.', ADBC_Rest::BAD_REQUEST );
			}

			$data = $request->get_params();

			$items_to_migrate = $data['items_to_migrate'] ?? null;
			$uninstall_old_versions = $data['uninstall_old_versions'] ?? null;

			// Validate items_to_migrate
			if ( ! is_array( $items_to_migrate ) ) {
				return ADBC_Rest::error( 'Invalid parameter: items_to_migrate must be an array.', ADBC_Rest::BAD_REQUEST );
			}

			$allowed_types = [ 'keep_last', 'automation_tasks', 'manual_corrections' ];
			foreach ( $items_to_migrate as $type ) {
				if ( ! is_string( $type ) || ! in_array( $type, $allowed_types, true ) ) {
					return ADBC_Rest::error( 'Invalid parameter: items_to_migrate contains unsupported type.', ADBC_Rest::BAD_REQUEST );
				}
			}

			// Validate uninstall_old_versions
			if ( ! is_bool( $uninstall_old_versions ) ) {
				return ADBC_Rest::error( 'Invalid parameter: uninstall_old_versions must be a boolean.', ADBC_Rest::BAD_REQUEST );
			}

			$data_type = ADBC_Settings::instance()->get_setting( 'free_migration_done' ) === '1' ? 'pro' : 'all';
			$result = ADBC_Migration::run( $items_to_migrate, $uninstall_old_versions, $data_type );

			return ADBC_Rest::success( '', $result );

		} catch (Exception $e) {
			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );
		}

	}

}