<?php
/**
 * Responsible for plugin updates.
 *
 * @package    advanced-analytics
 * @subpackage migration
 * @copyright  %%YEAR%%
 * @license    https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

declare(strict_types=1);

namespace ADVAN\Migration;

use ADVAN\Helpers\Settings;
use ADVAN\Entities\WP_Mail_Entity;
use ADVAN\Entities\WP_Fatals_Entity;
use ADVAN\Entities\Requests_Log_Entity;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Migration\Abstract_Migration;


defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Migration class
 */
if ( ! class_exists( '\ADVAN\Migration\Migration' ) ) {

	/**
	 * Put all you migration methods here
	 *
	 * @since 1.0.1
	 */
	class Migration extends Abstract_Migration {

		/**
		 * Migrates the plugin up-to version 1.0.1
		 *
		 * @return void
		 *
		 * @since 1.0.1
		 */
		public static function migrate_up_to_101() {
			$settings = Settings::get_current_options();

			$defs = array();

			$defaults = Settings::get_default_options()['severities'];

			foreach ( $defaults as $name => $default ) {
				$defs[ $name ] = $default['name'];
			}

			if ( isset( $settings['severity_colors'] ) && isset( $settings['severity_show'] ) ) {
				$settings['severities'] = \array_merge_recursive(
					$settings['severity_colors'],
					$settings['severity_show'],
					$defs
				);

				unset( $settings['severity_colors'] );
				unset( $settings['severity_show'] );
			}

			if ( isset( $settings['severity_colors'] ) ) {
				$settings['severities'] = \array_merge_recursive( $settings['severity_colors'], $defs );
				unset( $settings['severity_colors'] );
			}

			if ( isset( $settings['severity_show'] ) ) {
				$settings['severities'] = \array_merge_recursive( $settings['severity_show'], $defs );
				unset( $settings['severity_show'] );
			}

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );
		}

		/**
		 * Migrates the plugin up-to version 1.8.2
		 *
		 * @return void
		 *
		 * @since 1.8.2
		 */
		public static function migrate_up_to_182() {
			$settings = Settings::get_current_options();

			if ( ! isset( $settings['live_notifications_admin_bar'] ) ) {
				$settings['live_notifications_admin_bar'] = true;
			}

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );
		}

		/**
		 * Migrates the plugin up-to version 1.8.4
		 *
		 * @return void
		 *
		 * @since 1.8.4
		 */
		public static function migrate_up_to_184() {
			$settings = Settings::get_current_options();

			if ( ! isset( $settings['environment_type_admin_bar'] ) ) {
				$settings['environment_type_admin_bar'] = true;
			}

			$settings['severities']['user'] = array(
				'name'    => __( 'User', '0-day-analytics' ),
				'color'   => '#0d4c24',
				'display' => true,
			);

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );
		}

		/**
		 * Migrates the plugin up-to version 1.9.6
		 *
		 * @return void
		 *
		 * @since 1.9.6
		 */
		public static function migrate_up_to_196() {

			$settings = Settings::get_current_options();
			if ( isset( $settings['severities']['user'] ) && isset( $settings['severities']['user']['color'] ) && '#0d4c24' === $settings['severities']['user']['color'] ) {
				$settings['severities']['user']['color'] = '#85b395';
			}
			if ( isset( $settings['severities']['info'] ) && isset( $settings['severities']['info']['color'] ) && '#0000ff' === $settings['severities']['info']['color'] ) {
				$settings['severities']['info']['color'] = '#aeaeec';
			}
			if ( isset( $settings['severities']['fatal'] ) && isset( $settings['severities']['fatal']['color'] ) && '#b92a2a' === $settings['severities']['fatal']['color'] ) {
				$settings['severities']['fatal']['color'] = '#f09595';
			}
			if ( isset( $settings['severities']['parse'] ) && isset( $settings['severities']['parse']['color'] ) && '#b9762a' === $settings['severities']['parse']['color'] ) {
				$settings['severities']['parse']['color'] = '#e3bb8d';
			}

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );
		}

		/**
		 * Migrates the plugin up-to version 2.8.1
		 *
		 * @return void
		 *
		 * @since 2.8.1
		 */
		public static function migrate_up_to_281() {
			$settings = Settings::get_current_options();

			$defaults = Settings::get_default_options()['severities'];

			foreach ( $defaults as $name => $default ) {

				if ( ! isset( $settings['severities'][ $name ] ) ) {
					$settings['severities'][ $name ] = $default;
				}
			}

			Settings::store_options( $settings );
			Settings::set_current_options( $settings );
		}

		/**
		 * Migrates the plugin up-to version 3.0.1
		 *
		 * @return void
		 *
		 * @since 3.0.1
		 */
		public static function migrate_up_to_301() {
			if ( \class_exists( '\ADVAN\Entities\WP_Mail_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Mail_Entity::get_table_name() ) && ! Common_Table::check_column( 'email_from', 'text', WP_Mail_Entity::get_table_name() ) ) {
					WP_Mail_Entity::alter_table_301();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 3.6.3
		 *
		 * @return void
		 *
		 * @since 3.6.3
		 */
		public static function migrate_up_to_363() {
			if ( \class_exists( '\ADVAN\Entities\WP_Mail_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Mail_Entity::get_table_name() ) && ! Common_Table::check_column( 'blog_id', 'int', WP_Mail_Entity::get_table_name() ) ) {
					WP_Mail_Entity::alter_table_363();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 3.9.2
		 *
		 * @return void
		 *
		 * @since 3.9.2
		 */
		public static function migrate_up_to_392() {
			if ( \class_exists( '\ADVAN\Entities\Requests_Log_Entity' ) ) {
				if ( Common_Table::check_table_exists( Requests_Log_Entity::get_table_name() ) && ! Common_Table::check_column( 'plugin', 'varchar(200)', Requests_Log_Entity::get_table_name() ) ) {
					Requests_Log_Entity::alter_table_393();
				}
				if ( Common_Table::check_table_exists( Requests_Log_Entity::get_table_name() ) && ! Common_Table::check_column( 'domain', 'varchar(255)', Requests_Log_Entity::get_table_name() ) ) {
					Requests_Log_Entity::alter_table_3931();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 4.2.0 (adds plugin_slug column to mail log).
		 *
		 * @return void
		 *
		 * @since 4.2.0
		 */
		public static function migrate_up_to_420() {
			if ( \class_exists( '\\ADVAN\\Entities\\WP_Mail_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Mail_Entity::get_table_name() ) && ! Common_Table::check_column( 'plugin_slug', 'varchar(255)', WP_Mail_Entity::get_table_name() ) ) {
					\ADVAN\Entities\WP_Mail_Entity::alter_table_411();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 4.3.0
		 *
		 * Removes the now-redundant `version` key that used to be stored inside the
		 * main settings array. This key duplicated the separately stored plugin
		 * version and is no longer necessary.
		 *
		 * @return void
		 *
		 * @since 4.3.0
		 */
		public static function migrate_up_to_430() {
			$settings = Settings::get_current_options();

			if ( isset( $settings['version'] ) ) {
				unset( $settings['version'] );
				Settings::store_options( $settings );
				Settings::set_current_options( $settings );
			}
		}

		/**
		 * Migrates the plugin up-to version 4.6.0 (adds request_id column to hooks capture).
		 *
		 * @return void
		 *
		 * @since 4.6.0
		 */
		public static function migrate_up_to_460() {
			if ( \class_exists( '\\ADVAN\\Entities\\Hooks_Capture_Entity' ) ) {
				if ( Common_Table::check_table_exists( \ADVAN\Entities\Hooks_Capture_Entity::get_table_name() ) && ! Common_Table::check_column( 'request_id', 'varchar(50)', \ADVAN\Entities\Hooks_Capture_Entity::get_table_name() ) ) {
					\ADVAN\Entities\Hooks_Capture_Entity::alter_table_460();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 4.7.0 (adds performance indexes to fatals table).
		 *
		 * @return void
		 *
		 * @since 4.7.0
		 */
		public static function migrate_up_to_470() {
			if ( \class_exists( '\\ADVAN\\Entities\\WP_Fatals_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Fatals_Entity::get_table_name() ) ) {
					WP_Fatals_Entity::alter_table_470();
				}
			}
		}

		/**
		 * Migrates the plugin up-to version 4.8.0 (adds enhanced mail tracking columns).
		 * Adds columns for CC/BCC recipients, Reply-To, email sizes, attachment metadata,
		 * delivery time, email categorization, and resend capability.
		 *
		 * @return void
		 *
		 * @since 4.8.0
		 */
		public static function migrate_up_to_480() {
			if ( \class_exists( '\\ADVAN\\Entities\\WP_Mail_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Mail_Entity::get_table_name() ) ) {
					WP_Mail_Entity::alter_table_471();
				}
			}
			if ( \class_exists( '\\ADVAN\\Entities\\Requests_Log_Entity' ) ) {
				if ( Common_Table::check_table_exists( Requests_Log_Entity::get_table_name() ) ) {
					Requests_Log_Entity::alter_table_480();
				}
			}
			if ( \class_exists( '\\ADVAN\\Entities\\WP_Fatals_Entity' ) ) {
				if ( Common_Table::check_table_exists( WP_Fatals_Entity::get_table_name() ) ) {
					WP_Fatals_Entity::alter_table_470();
				}
			}
		}
	}
}
