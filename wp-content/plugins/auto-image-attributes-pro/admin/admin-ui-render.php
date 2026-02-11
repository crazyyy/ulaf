<?php
/**
 * Admin UI setup and render
 *
 * @since 1.0
 * @function	iaffpro_license_section_callback()		Callback function for License Settings section
 * @function	iaffpro_license_email_field_callback()	Callback function for Registered email field
 * @function	iaffpro_license_key_field_callback()	Callback function for License Key field
 * @function	iaffpro_admin_interface_render()				Admin interface renderer
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Callback function for License Settings section
 *
 * @since 1.0
 */
function iaffpro_license_section_callback() {
	printf( __( '<p>Please enter your license information to activate the plugin and receive automatic updates.<br>You will find the details in the welcome email or your <a href="%s" target="_blank">account dashboard</a>. Please do not hesitate to <a href="%s" target="_blank">contact support</a> if you need any help.</p>', 'auto-image-attributes-pro' ), 
	'https://imageattributespro.com/my-account/?utm_source=iap&utm_medium=license-page',
	'https://imageattributespro.com/contact/?utm_source=iap&utm_medium=license-page' );
}

/**
 * Callback function for Registered email field
 *
 * @since 1.0
 */
function iaffpro_license_email_field_callback() {	

	// Get Settings
	$settings = iaffpro_get_settings();
	?>
	
	<!-- Registered Email -->
	<input type="email" name="iaffpro_settings[registered_email]" placeholder="registered@email.com" class="all-options" value="<?php if ( isset( $settings['registered_email'] ) && ( ! empty($settings['registered_email']) ) ) esc_attr_e($settings['registered_email']); ?>"/><br>

	<?php
}

/**
 * Callback function for License Key field
 *
 * @since 1.0
 */
function iaffpro_license_key_field_callback() {	

	// Get Settings
	$settings = iaffpro_get_settings();
	?>
	
	<!-- License Key -->
	<input type="password" name="iaffpro_settings[license_key]" placeholder="####-####-####-####-####" class="all-options" value="<?php if ( isset( $settings['license_key'] ) && ( ! empty($settings['license_key']) ) ) esc_attr_e($settings['license_key']); ?>"/><br>

	<?php
}
 
/**
 * Admin interface renderer
 *
 * @since 1.0
 */ 
function iaffpro_admin_interface_render () {
	
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?> 
	
	<div class="wrap">	
		<h1><?php echo 'Image Attributes Pro <sup>'. IAFFPRO_VERSION_NUM .'</sup>'; ?></h1>
		
		<form action="options.php" method="post">		
			<?php
			// Output nonce, action, and option_page fields for a settings page.
			settings_fields( 'iaffpro_settings_group' );
			
			// Prints out all settings sections added to a particular settings page. 
			do_settings_sections( 'image-attributes-pro-activation' );	// Page slug
			
			// Output save settings button
			submit_button( __( 'Save License Information ', 'auto-image-attributes-pro' ) );
			?>
		</form>
	</div>
	<?php
}