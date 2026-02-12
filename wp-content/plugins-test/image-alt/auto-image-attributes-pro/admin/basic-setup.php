<?php 
/**
 * Basic setup functions for the plugin
 *
 * @since 	1.0
 * @function 	iaffpro_activate_plugin() 	Plugin activatation todo list
 * @function 	iaffpro_settings_link()		Print direct link to plugin settings in plugins list in admin
 * @function 	iaffpro_plugin_row_meta() 	Add donate and other links to plugins list
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Plugin activatation todo list
 *
 * This function runs when user activates the plugin. Used in register_activation_hook in the main plugin file. 
 * @since 	1.0
 */
function iaffpro_activate_plugin() {
	// Nothing to do.
}

/**
 * Redirect to License Key page on plugin activation.
 *
 * - Will redirect when plugin is activated and license key is missing.
 * - Will not redirect if multiple plugins are activated at the same time.
 * - Will not redirect when activated network wide on multisite.
 * 
 * @since 4.2
 * @link https://github.com/SuperPWA/Super-Progressive-Web-Apps/blob/6d6f6b2e93c78e5cdb0201aa65cc812ad8a83ed9/admin/basic-setup.php#L58-L100
 * 
 * @param $plugin (string) Path to the main plugin file from plugins directory.
 * @param $network_wide (bool) True when network activated on multisites. False otherwise. 
 */
function iaffpro_activation_redirect( $plugin, $network_wide ) {
	
	// Return if not Image Attributes Pro or if plugin is activated network wide.
	if ( $plugin !== 'auto-image-attributes-pro/auto-image-attributes-pro.php' || $network_wide === true ) {
		return false;
	}
	
	if ( ! class_exists( 'WP_Plugins_List_Table' ) ) {
		return false;
	}

	// Get plugin Settings
	$settings = iaffpro_get_settings();

	// Return if license key is already set.
	if ( ! empty( $settings['registered_email'] ) && ! empty( $settings['license_key'] ) ) {
		return false;
	}

	/**
	 * An instance of the WP_Plugins_List_Table class.
	 *
	 * @link https://core.trac.wordpress.org/browser/tags/4.9.8/src/wp-admin/plugins.php#L15
	 */
	$wp_list_table_instance = new WP_Plugins_List_Table();
	$current_action         = $wp_list_table_instance->current_action();

	// When only one plugin is activated, the current_action() method will return activate.
	if ( $current_action !== 'activate' ) {
		return false;
	}

	// Redirect to Image Attributes Pro License Key settings page. 
	exit( wp_redirect( admin_url( 'options-general.php?page=image-attributes-pro-activation' ) ) );
}
add_action( 'activated_plugin', 'iaffpro_activation_redirect', PHP_INT_MAX, 2 );

/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since	1.0
 */
function iaffpro_settings_link( $links ) {
	
	return array_merge(
		array(
			'license' => '<a href="' . admin_url( 'options-general.php?page=image-attributes-pro-activation' ) . '">' . __( 'License Key', 'auto-image-attributes-pro' ) . '</a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO . '/auto-image-attributes-pro.php', 'iaffpro_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since 	1.0
 */
function iaffpro_plugin_row_meta( $links, $file ) {
	
	if ( strpos( $file, 'auto-image-attributes-pro.php' ) !== false ) {
		
		$new_links = array(
				'support' 	=> '<a href="https://imageattributespro.com/docs/?utm_source=iap&utm_medium=plugins-list" target="_blank">Plugin Support</a>',
				);
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}
add_filter( 'plugin_row_meta', 'iaffpro_plugin_row_meta', 10, 2 );

/**
 * Show a custom upgrade notice in the plugins list
 *
 * @since	1.02
 */
function iaffpro_display_upgrade_notice() {

	// Check if license is given.
	$settings = iaffpro_get_settings();
	if ( empty( $settings['registered_email'] ) || empty( $settings['license_key'] ) ) {
		printf( 
			__( '<br><span class="dashicons dashicons-no" style="color:#DC3232; margin-right: 6px;"></span><a href="%s">Please enter your license info</a> to download this update. <a target="_blank" href="%s">Whats new?</a>', 'auto-image-attributes-pro' ), 
			admin_url( 'options-general.php?page=image-attributes-pro-activation' ),
			'https://imageattributespro.com/changelog/?utm_source=iap&utm_medium=plugins-list-upgrade-notice' 
		);

		return;
	}
	
	// Get all the meta data
	global $MyUpdateChecker;
	$meta = $MyUpdateChecker->requestInfo();
	
	// Return if no custom upgrade notice is set. i.e. the user has a valid license
	if ( empty( $meta->upgrade_notice ) ) {
		return;
	}
	
	// Print custom upgrade notice.
	echo $meta->upgrade_notice;
}
add_action( 'in_plugin_update_message-auto-image-attributes-pro/auto-image-attributes-pro.php', 'iaffpro_display_upgrade_notice' );

/**
 * Display message to activate plugin in WordPress Plugins list.
 * 
 * @since 4.0
 * 
 * @link https://developer.wordpress.org/reference/hooks/after_plugin_row_plugin_file/
 * 
 * @param $plugin_file (sting) Path to the plugin file relative to the plugins directory.
 * @param $plugin_data (array) An array of plugin data.
 */
function iaffpro_display_activation_message_in_plugins_list( $plugin_file, $plugin_data ) {

	// Hide message when update is available to prevent overlap with update notification.
	if ( isset( $plugin_data['update'] ) && $plugin_data['update'] ) {
		return;
	}

	// Check if license is given.
	$settings = iaffpro_get_settings();
	if ( ! empty( $settings['registered_email'] ) && ! empty( $settings['license_key'] ) ) {
		return;
	}

	// Do not show in network admin pages.
	if ( is_network_admin() ) {
		return;
	}

	?>
	<tr class="update-message inline notice-warning notice-alt">
		<td colspan="4" style="border-left: 4px #dba617 solid !important; box-shadow: inset 0 -1px 0 rgb(0 0 0 / 10%);">
				<p style="margin: 0.5em 0 0.5em 30px !important;"><span style="margin-left: 6px;">
					<?php printf( __( '<strong>Thank you for choosing Image Attributes Pro.</strong> <a href="%s">Please enter license info</a> to activate the plugin and download plugin updates.', 'auto-image-attributes-pro' ), admin_url('options-general.php?page=image-attributes-pro-activation') ); ?>
				</span></p>
		</td>
	</tr>
	<?php
}
add_action( 'after_plugin_row_auto-image-attributes-pro/auto-image-attributes-pro.php', 'iaffpro_display_activation_message_in_plugins_list', 10, 2 );