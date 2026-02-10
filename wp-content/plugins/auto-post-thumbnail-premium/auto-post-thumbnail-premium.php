<?php
/**
 * Plugin Name: Auto Featured Image premium (Premium)
 * Plugin URI: https://cm-wp.com/apt
 * Description: Premium functions for Auto Post Thumbnail plugin.
 * Author: Creativemotion <support@cm-wp.com>
 * Author URI: cm-wp.com
 * Version: 1.4.5
 * Update URI: https://api.freemius.com
 * Text Domain: aptp
 * Domain Path: /languages/
 */

// @formatter:off
// Выход при непосредственном доступе
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wbcr_apt_premium_load' ) ) {

	function wbcr_apt_premium_load() {

		if ( ! defined( 'WAPT_PLUGIN_ACTIVE' ) ) {
			add_action( 'admin_notices', 'waptp_notice' );
			add_action( 'network_admin_notices', 'waptp_notice' );

			return;
		}
		if ( defined( 'WAPT_PLUGIN_THROW_ERROR' ) ) {
			return;
		}

		// Если лицензия не активирована премиум пакет не должен работать
		if ( class_exists( 'WAPT_Plugin' ) && ! WAPT_Plugin::app()->premium->is_activate() ) {
			return;
		}
		// Устанавливает статус плагина, как активный
		define( 'WAPTP_PLUGIN_ACTIVE', true );
		// Версия плагина
		define( 'WAPTP_PLUGIN_VERSION', '1.3.9' );

		define( 'WAPTP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WAPTP_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
		// Ссылка к директории плагина
		define( 'WAPTP_PLUGIN_URL', plugins_url( null, __FILE__ ) );
		// Директория плагина
		define( 'WAPTP_PLUGIN_DIR', dirname( __FILE__ ) );

		require_once WAPTP_PLUGIN_DIR . '/includes/class-post-images-pro.php';
		require_once WAPTP_PLUGIN_DIR . '/includes/class.waptp.php';
		require_once WAPTP_PLUGIN_DIR . '/includes/class.ibm-watson.php';
		require_once WAPTP_PLUGIN_DIR . '/includes/image-search/boot.php';

		new WBCR\APT\WAPT_Premium();
	}

	add_action( 'plugins_loaded', 'wbcr_apt_premium_load', 20 );
}

function waptp_notice() {
	echo '<div class="notice notice-warning"><p><strong>' . __( 'Auto Featured Image premium', 'aptp' ) . ':</strong></p><p>' . __( 'Install and activate the main free plugin! It is needed for the premium plugin to work. The premium plugin is an addon', 'aptp' ) . '</p></div>';
}
// @formatter:on
