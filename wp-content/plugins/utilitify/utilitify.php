<?php

/**
 *
 * Utilitify
 *
 * Supercharge Your WordPress Site With Powerpack WordPress Utilities
 *
 * @package   Utilitify
 * @author    KaizenCoders <hello@kaizencoders.com>
 * @license   GPL-3.0+
 * @link      https://wordpress.org/plugins/utilitify
 * @copyright 2020-22 KaizenCoders
 *
 * @wordpress-plugin
 * Plugin Name:       Utilitify
 * Plugin URI:        https://wordpress.org/plugins/utilitify
 * Description:       Supercharge Your WordPress Site With Powerpack WordPress Utilities
 * Version:           1.1.1
 * Author:            KaizenCoders
 * Author URI:        https://kaizencoders.com
 * Text Domain:       utilitify
 * Tested up to:      6.7.2
 * Requires PHP:      5.6.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( function_exists( 'kc_uf_fs' ) ) {
	kc_uf_fs()->set_basename( true, __FILE__ );
} else {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 */
	if ( ! defined( 'KC_UF_PLUGIN_VERSION' ) ) {
		define( 'KC_UF_PLUGIN_VERSION', '1.1.1' );
	}

	/**
	 * Minimum PHP version required for URL Shortify
	 *
	 * @since 1.0.0
	 *
	 */
	if ( ! defined( 'KC_UF_MIN_PHP_VER' ) ) {
		define( 'KC_UF_MIN_PHP_VER', '5.6.4' );
	}


	if ( ! function_exists( 'kc_uf_fs' ) ) {
		// Create a helper function for easy SDK access.
		function kc_uf_fs() {
			global $kc_uf_fs;

			if ( ! isset( $kc_uf_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( __FILE__ ) . '/libs/fs/start.php';

				$kc_uf_fs = fs_dynamic_init( [
					'id'             => '8134',
					'slug'           => 'utilitify',
					'type'           => 'plugin',
					'public_key'     => 'pk_95d0b121cf968d7c179dfdb5c95d1',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => [
						'slug'       => 'utilitify',
						'first-path' => 'admin.php?page=utilitify',
					],
				] );
			}

			return $kc_uf_fs;
		}

		// Init Freemius.
		kc_uf_fs();

		// Use custom icon for onboarding.
		kc_uf_fs()->add_filter('plugin_icon', function () {
			return dirname( __FILE__ ) . '/assets/images/plugin-icon.png';
		});

		// Signal that SDK was initiated.
		do_action( 'kc_uf_fs_loaded' );
	}

	if ( ! function_exists( 'kc_uf_fail_php_version_notice' ) ) {

		/**
		 * Admin notice for minimum PHP version.
		 *
		 * Warning when the site doesn't have the minimum required PHP version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function kc_uf_fail_php_version_notice() {
			/* translators: %s: PHP version */
			$message      = sprintf( esc_html__( 'Utilitify requires PHP version %s+, plugin is currently NOT RUNNING.',
				'utilitify' ), KC_UF_MIN_PHP_VER );
			$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}
	}


	if ( ! version_compare( PHP_VERSION, KC_UF_MIN_PHP_VER, '>=' ) ) {
		add_action( 'admin_notices', 'kc_uf_fail_php_version_notice' );

		return;
	}

	if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
		require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	}

	// Plugin Folder Path.
	if ( ! defined( 'KC_UF_PLUGIN_DIR' ) ) {
		define( 'KC_UF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'KC_UF_PLUGIN_BASE_NAME' ) ) {
		define( 'KC_UF_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
	}

	if ( ! defined( 'KC_UF_PLUGIN_FILE' ) ) {
		define( 'KC_UF_PLUGIN_FILE', __FILE__ );
	}

	if ( ! defined( 'KC_UF_PLUGIN_URL' ) ) {
		define( 'KC_UF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	if ( ! defined( 'KC_UF_PLUGIN_ASSETS_DIR_URL' ) ) {
		define( 'KC_UF_PLUGIN_ASSETS_DIR_URL', KC_UF_PLUGIN_URL . 'lite/dist/assets' );
	}

	if ( ! defined( 'KC_UF_PLUGIN_STYLES_DIR_URL' ) ) {
		define( 'KC_UF_PLUGIN_STYLES_DIR_URL', KC_UF_PLUGIN_URL . 'lite/dist/styles' );
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in lib/Activator.php
	 */
	\register_activation_hook( __FILE__, '\Kaizencoders\Utilitify\Activator::activate' );

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in lib/Deactivator.php
	 */
	\register_deactivation_hook( __FILE__, '\Kaizencoders\Utilitify\Deactivator::deactivate' );


	if ( ! function_exists( 'KC_UF' ) ) {
		/**
		 * Get plugin instance
		 *
		 * @since 1.0.0
		 */
		function KC_UF() {
			return \Kaizencoders\Utilitify\Plugin::instance();
		}

		add_action( 'plugins_loaded', function () {
			KC_UF()->run();
		} );
	}

}
