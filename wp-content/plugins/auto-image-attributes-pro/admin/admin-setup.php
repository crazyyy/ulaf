<?php
/**
 * Admin setup for the plugin
 *
 * @since 1.0
 * @function	iaffpro_add_menu_links()			Add admin page for license key
 * @function	iaffpro_register_settings			Register Settings
 * @function	iaffpro_validater_and_sanitizer()	Validate And Sanitize User Input Before Its Saved To Database
 * @function	iaffpro_get_settings()				Get settings from database
 * @function	iaffpro_admin_notices() 			All the admin notices
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit; 
 
/**
 * Add admin page for license key
 *
 * Page is not shown in the admin menu and can only be accessed via the link in the plugins list (near deactivate plugin).
 * @since 	1.0
 */
function iaffpro_add_menu_links() {
	add_submenu_page ( null, __('Image Attributes Pro','auto-image-attributes-pro'), __('Image Attributes Pro','auto-image-attributes-pro'), 'manage_options', 'image-attributes-pro-activation','iaffpro_admin_interface_render'  );
}
add_action( 'admin_menu', 'iaffpro_add_menu_links' );

/**
 * Register Settings
 *
 * @since 	1.0
 */
function iaffpro_register_settings() {

	// Register Setting
	register_setting( 
		'iaffpro_settings_group', 			// Group name
		'iaffpro_settings', 				// Setting name = html form <input> name on settings form
		'iaffpro_validater_and_sanitizer'	// Input sanitizer
	);
	
	// License Section
    add_settings_section(
        'iaffpro_license_section',								// ID
        __('Image Attribute Pro Activation', 'auto-image-attributes-pro'),	// Title
        'iaffpro_license_section_callback',						// Callback Function
        'image-attributes-pro-activation'						// Page slug
    );
	
	// Registered Email
    add_settings_field(
        'iaffpro_license_email_field',				// ID
        __('Registered Email', 'auto-image-attributes-pro'),	// Title
        'iaffpro_license_email_field_callback',		// Callback function
        'image-attributes-pro-activation',			// Page slug
        'iaffpro_license_section'					// Settings Section ID
    );
	
	// License Key
    add_settings_field(
        'iaffpro_license_key_field',				// ID
        __('License Key', 'auto-image-attributes-pro'),		// Title
        'iaffpro_license_key_field_callback',		// Callback function
        'image-attributes-pro-activation',			// Page slug
        'iaffpro_license_section'					// Settings Section ID
    );
	
}
add_action( 'admin_init', 'iaffpro_register_settings' );

/**
 * Validate and sanitize user input before its saved to database
 *
 * @since 		1.0
 */
function iaffpro_validater_and_sanitizer ( $settings ) {
	
	$settings['registered_email'] 	= sanitize_email($settings['registered_email']);
	$settings['license_key'] 		= sanitize_text_field($settings['license_key']);

	// Display link to Image Attributes Pro Settings page in the admin notice after settings are saved.
	$message = sprintf( __( 'License information is saved. Configure <a href="%s">Image Attributes Pro &rarr;</a>', 'auto-image-attributes-pro' ), admin_url( 'options-general.php?page=image-attributes-from-filename' ) );

	add_settings_error( 'iaffpro_settings', 'iaffpro_settings-error', $message, 'success' );
	
	return $settings;
}
			
/**
 * Get settings from database
 *
 * @since 	1.0
 * @return	Array	A merged array of default and settings saved in database. 
 */
function iaffpro_get_settings() {

	$defaults = array(); // Empty for now. Might be useful in the future.
	$settings = get_option('iaffpro_settings', $defaults);
	
	return $settings;
}

/**
 * All the admin notices
 *
 * @since 1.0
 */
function iaffpro_admin_notices() {

	// Admin notice if the base plugin isnt installed 
	if( ! function_exists('iaff_auto_image_attributes') ) { ?>
		<div class="notice notice-warning">
			<p>
				<?php

				if ( current_user_can( 'install_plugins' ) ) {
					
					if ( file_exists( WP_PLUGIN_DIR . '/auto-image-attributes-from-filename-with-bulk-updater/iaff_image-attributes-from-filename.php' ) ) {

						// Display link to activate plugin if plugin is installed but not activated.
						printf( 
							__( '<strong>Image Attributes Pro:</strong> <a href="%s" target="_blank">Auto Image Attributes From Filename With Bulk Updater</a> is installed but not activated. To use Image Attributes Pro, <a href="%s">click here and activate the plugin &rarr;</a>', 'auto-image-attributes-pro' ),
							'https://wordpress.org/plugins/auto-image-attributes-from-filename-with-bulk-updater/',
							wp_nonce_url( self_admin_url('plugins.php?action=activate&plugin=' . urlencode( 'auto-image-attributes-from-filename-with-bulk-updater/iaff_image-attributes-from-filename.php') ), 'activate-plugin_' . 'auto-image-attributes-from-filename-with-bulk-updater/iaff_image-attributes-from-filename.php' )
						);
					} else {

						// Display link to install plugin.
						printf( 
							__( '<strong>Image Attributes Pro:</strong> Please install and activate <a href="%s" target="_blank">Auto Image Attributes From Filename With Bulk Updater</a> to use Image Attributes Pro. <a href="%s">Click here to install plugin &rarr;</a>', 'auto-image-attributes-pro' ),
							'https://wordpress.org/plugins/auto-image-attributes-from-filename-with-bulk-updater/',
							wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=auto-image-attributes-from-filename-with-bulk-updater' ), 'install-plugin_auto-image-attributes-from-filename-with-bulk-updater' )
						);
					}
				}
				
				?>
			</p>
		</div>
	<?php }
	
	// Admin notice if license information is not given
	$settings = iaffpro_get_settings();
	if ( empty( $settings['registered_email'] ) || empty( $settings['license_key'] ) ) { ?>
		<div class="notice notice-success">
			<p><?php printf( __( '<strong>Thank you for choosing Image Attributes Pro.</strong> <a href="%s">Please enter license info</a> to activate the plugin and download plugin updates.', 'auto-image-attributes-pro' ),
			admin_url('options-general.php?page=image-attributes-pro-activation')); ?></p>
		</div>
	<?php }
}
add_action('admin_notices', 'iaffpro_admin_notices');