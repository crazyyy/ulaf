<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Installation' ) ) :

	final class WPDT_Installation {

		/**
		 * Currently installed version
		 *
		 * @var integer
		 */
		public static $current_version;

		/**
		 * For checking whether upgrade available or not
		 *
		 * @var boolean
		 */
		public static $is_upgrade = false;

		/**
		 * Initialize installation
		 */
		public static function init() {

			self::get_current_version();
			self::check_upgrade();

			if ( self::$is_upgrade ) {

				// Do not allow parallel process to run.
				if ( 'yes' === get_transient( 'wpdt_installing' ) ) {
					return;
				}

				// Set transient.
				set_transient( 'wpdt_installing', 'yes', MINUTE_IN_SECONDS * 10 );

				// Create database tables.
				self::create_db_tables();

				// Run installation.
				if ( self::$current_version == 0 ) {
					add_action( 'init', array( __CLASS__, 'initial_setup' ), 1 );
				} else {
					add_action( 'init', array( __CLASS__, 'upgrade' ), 1 );
				}

				// Delete transient.
				delete_transient( 'wpdt_installing' );
			}

			// Activation functionality.
			register_activation_hook( WPDT_PLUGIN_FILE, array( __CLASS__, 'activate' ) );

			// Deactivate functionality.
			register_deactivation_hook( WPDT_PLUGIN_FILE, array( __CLASS__, 'deactivate' ) );
		}

		/**
		 * Check version
		 */
		public static function get_current_version() {
			self::$current_version = get_option( 'debug_log_tool_current_version', 0 );
		}

		/**
		 * Check for upgrade
		 */
		public static function check_upgrade() {
			if ( self::$current_version != WPDT_VERSION ) {
				self::$is_upgrade = true;
			}
		}

		/**
		 * First time installation
		 */
		public static function initial_setup() {

			// Set auto-refresh status.
			update_option( 'wpdt_auto_refresh_status', 0 );
			self::set_upgrade_complete();
		}

		/**
		 * Upgrade the version
		 */
		public static function upgrade() {

			if ( version_compare( self::$current_version, '1.0.3', '<' ) ) {
				// Set auto-refresh status.
				update_option( 'wpdt_auto_refresh_status', 0 );
			}

			if ( version_compare( self::$current_version, '1.0.5', '<' ) ) {
				// Set auto-refresh status.
				update_option( 'wpdt_group_logs_status', 0 );
			}

			if ( version_compare( self::$current_version, '1.0.7', '<' ) ) {
				$settings = get_option( 'wpdt_settings', array() );
				if ( ! isset( $settings['log_date_timezone'] ) ) {
					$settings['log_date_timezone'] = 'utc';
					update_option( 'wpdt_settings', $settings );
				}
			}

			self::set_upgrade_complete();
		}

		/**
		 * Mark upgrade as complete
		 */
		public static function set_upgrade_complete() {

			update_option( 'debug_log_tool_current_version', WPDT_VERSION );
			self::$current_version = WPDT_VERSION;
			self::$is_upgrade      = false;
		}

		/**
		 * Create database table
		 *
		 * @return void
		 */
		public static function create_db_tables() {
		}

		/**
		 * Actions to perform after plugin activated
		 *
		 * @return void
		 */
		public static function activate() {
			do_action( 'wpdt_activation' );
		}

		/**
		 * Actions to perform after plugin deactivated
		 *
		 * @return void
		 */
		public static function deactivate() {
			do_action( 'wpdt_deactivation' );
		}
	}
endif;

WPDT_Installation::init();
