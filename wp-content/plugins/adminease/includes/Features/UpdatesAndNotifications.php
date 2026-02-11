<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Handles updates and notifications configuration for the plugin by applying the necessary filters
 * based on settings retrieved from the plugin's configuration.
 */
class UpdatesAndNotifications {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'updates-and-notifications' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( isset( $this->settings['disable_auto_update_core'] ) && true === (bool) $this->settings['disable_auto_update_core'] ) {
			add_filter( 'auto_update_core', '__return_false', 9999 );
			add_filter( 'allow_major_auto_core_updates', '__return_false', 9999 );
			add_filter( 'allow_minor_auto_core_updates', '__return_false', 9999 );
			add_filter( 'allow_dev_auto_core_updates', '__return_false', 9999 );
			add_filter( 'automatic_updater_disabled', '__return_true', 9999 );
		}
		
		if( isset( $this->settings['disable_auto_update_plugin'] ) && true === (bool) $this->settings['disable_auto_update_plugin'] ) {
			add_filter( 'auto_update_plugin', '__return_false', 9999 );
			add_filter( 'plugins_auto_update_enabled', '__return_false', 9999 );
		}
		
		if( isset( $this->settings['disable_auto_update_theme'] ) && true === (bool) $this->settings['disable_auto_update_theme'] ) {
			add_filter( 'auto_update_theme', '__return_false', 9999 );
			add_filter( 'themes_auto_update_enabled', '__return_false', 9999 );
		}
		
		if( isset( $this->settings['disable_auto_update_translation'] ) && true === (bool) $this->settings['disable_auto_update_translation'] ) {
			add_filter( 'auto_update_translation', '__return_false', 9999 );
		}
		
		if( isset( $this->settings['disable_auto_core_update_send_email'] ) && true === (bool) $this->settings['disable_auto_core_update_send_email'] ) {
			add_filter( 'auto_core_update_send_email', '__return_false' );
		}
		
		if( isset( $this->settings['disable_auto_theme_update_send_email'] ) && true === (bool) $this->settings['disable_auto_theme_update_send_email'] ) {
			add_filter( 'auto_theme_update_send_email', '__return_false' );
		}
		
		if( isset( $this->settings['disable_auto_plugin_update_send_email'] ) && true === (bool) $this->settings['disable_auto_plugin_update_send_email'] ) {
			add_filter( 'auto_plugin_update_send_email', '__return_false' );
		}
		
		if( isset( $this->settings['disable_new_user_admin_notification_email'] ) && true === (bool) $this->settings['disable_new_user_admin_notification_email'] ) {
			add_action( 'plugins_loaded', [ $this, 'disable_new_user_admin_notification_email' ] );
		}
		
		if( isset( $this->settings['disable_user_password_change_admin_notification_email'] ) && true === (bool) $this->settings['disable_user_password_change_admin_notification_email'] ) {
			// Disable password change notification emails to admin
			if( !function_exists( 'wp_password_change_notification' ) ) {
				function wp_password_change_notification() {
				}
			}
			add_filter( 'send_password_change_email', '__return_false' );
			add_filter( 'send_email_change_email', '__return_false' );
		}
		
		if( isset( $this->settings['disable_comment_post_author_notification_email'] ) && true === (bool) $this->settings['disable_comment_post_author_notification_email'] ) {
			add_filter( 'notify_post_author', '__return_false' );
		}
		
		if( isset( $this->settings['disable_comment_admin_notification_email'] ) && true === (bool) $this->settings['disable_comment_admin_notification_email'] ) {
			add_filter( 'notify_moderator', '__return_false' );
		}
		
		if( isset( $this->settings['disable_recovery_mode_email_notification'] ) && true === (bool) $this->settings['disable_recovery_mode_email_notification'] ) {
			add_filter( 'recovery_mode_email', '__return_false' );
		}
		
		if( isset( $this->settings['customize_recovery_mode_recipient_email'] ) && true === (bool) $this->settings['customize_recovery_mode_recipient_email'] ) {
			add_filter( 'recovery_mode_email', [ $this, 'recovery_mode_email' ] );
		}
	}
	
	/**
	 * Extends and modifies the array of settings fields for the admin interface.
	 *
	 * @param array $fields An array of existing settings fields to be extended. This array is structured hierarchically, containing categories and their respective fields.
	 *
	 * @return array The modified array of settings fields, including added fields for disabling automatic updates and related notifications for core, plugins, themes, and translations.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-update-core',
			'name'        => 'adminease[updates-and-notifications][disable_auto_update_core]',
			'value'       => $this->settings['disable_auto_update_core'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto update core', 'adminease' ),
			'description' => __( 'WordPress has a built-in feature that can automatically update itself to the latest version. This helps keep your website secure and up to date without needing to do anything manually. However, some people prefer to disable automatic updates so they can test new versions first or avoid unexpected changes. You can easily turn this option on or off depending on how much control you want over updates.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-update-plugin',
			'name'        => 'adminease[updates-and-notifications][disable_auto_update_plugin]',
			'value'       => $this->settings['disable_auto_update_plugin'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto update plugins', 'adminease' ),
			'description' => __( 'WordPress also lets you automatically update your plugins to keep your site secure and running smoothly. This means you don’t have to manually check for updates—WordPress will do it for you. It’s a great way to save time and protect your site from security risks. However, some users prefer to disable auto-updates for plugins so they can test each update first, especially on websites with custom setups or many plugins. You can turn plugin auto-updates on or off individually from your WordPress dashboard.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-update-theme',
			'name'        => 'adminease[updates-and-notifications][disable_auto_update_theme]',
			'value'       => $this->settings['disable_auto_update_theme'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto update themes', 'adminease' ),
			'description' => __( "WordPress gives you the option to automatically update your themes, just like plugins. This helps ensure your site stays secure and compatible with the latest features. When auto-updates are enabled for a theme, WordPress will install new versions as they’re released, without requiring manual action. However, if you've made custom changes to your theme files, automatic updates could overwrite them. That’s why some users prefer to turn off auto-updates for themes and update them manually after testing. You can easily enable or disable theme auto-updates from the Appearance > Themes section in your dashboard.", 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-update-translation',
			'name'        => 'adminease[updates-and-notifications][disable_auto_update_translation]',
			'value'       => $this->settings['disable_auto_update_translation'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto update translations', 'adminease' ),
			'description' => __( 'WordPress also supports automatic updates for translations, which include language files for the WordPress core, themes, and plugins. These updates help keep your site’s content accurate and fully translated in your chosen language. Translation files are usually small and safe to update, so WordPress enables these auto-updates by default. Most users leave this feature on, as it improves the user experience without affecting the site’s design or functionality. If needed, advanced users can disable translation auto-updates through code or a plugin, but in most cases, it’s best to keep them enabled.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-core-update-send-email',
			'name'        => 'adminease[updates-and-notifications][disable_auto_core_update_send_email]',
			'value'       => $this->settings['disable_auto_core_update_send_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto core update send email', 'adminease' ),
			'description' => __( "When WordPress automatically updates its core software, it sends an email notification to the site administrator. This email lets you know that the update was successful and includes details about what was updated. It’s a helpful way to stay informed about changes to your site, especially if you're not logging in regularly. While you can disable these emails with custom code, they’re useful for keeping track of important updates and ensuring everything is working as expected.", 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-theme-update-send-email',
			'name'        => 'adminease[updates-and-notifications][disable_auto_theme_update_send_email]',
			'value'       => $this->settings['disable_auto_theme_update_send_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto theme update send email', 'adminease' ),
			'description' => __( "When a theme is automatically updated in WordPress, the site administrator receives an email notification with details about the update. This helps you stay informed about changes to your theme, especially if you’re using auto-updates. The email includes the theme name and version, so you know exactly what was updated. It’s a useful feature for tracking updates and making sure your site looks and functions as expected after the change.", 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-auto-plugin-update-send-email',
			'name'        => 'adminease[updates-and-notifications][disable_auto_plugin_update_send_email]',
			'value'       => $this->settings['disable_auto_plugin_update_send_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable auto plugin update send email', 'adminease' ),
			'description' => __( "When a plugin is automatically updated in WordPress, the site administrator gets an email notification with information about the update. This email lists which plugin was updated and its new version, helping you keep track of changes to your site. These notifications are especially useful for monitoring plugin activity, ensuring everything still works correctly, and spotting potential issues early.", 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-new-user-admin-notification-email',
			'name'        => 'adminease[updates-and-notifications][disable_new_user_admin_notification_email]',
			'value'       => $this->settings['disable_new_user_admin_notification_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable new user admin notification email', 'adminease' ),
			'description' => __( 'The <strong>New User Admin Notification Email</strong> is a message sent to the site administrator whenever a new user registers on the website. It includes the new user’s username and email address, helping you <strong>keep track of user activity</strong> and detect unwanted registrations. While useful, some site owners choose to disable this notification—especially on busy sites or when using custom user management tools—to reduce inbox clutter.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-user-password-change-admin-notification-email',
			'name'        => 'adminease[updates-and-notifications][disable_user_password_change_admin_notification_email]',
			'value'       => $this->settings['disable_user_password_change_admin_notification_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable user password change admin notification email', 'adminease' ),
			'description' => __( 'The <strong>User Password Change Admin Notification Email</strong> is sent to the site administrator when any user changes their password. This alert helps you <strong>monitor account activity</strong> and quickly spot suspicious behavior—like unexpected password changes. While it adds a layer of security awareness, some admins may choose to disable it to avoid unnecessary notifications on high-traffic sites.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-comment-admin-notification-email',
			'name'        => 'adminease[updates-and-notifications][disable_comment_admin_notification_email]',
			'value'       => $this->settings['disable_comment_admin_notification_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable comment admin notification email', 'adminease' ),
			'description' => __( 'The <strong>Comment Moderator Notification Email</strong> is sent to the site moderator whenever a new comment is posted on the site. It includes the comment content, author details, and a direct link to moderate or reply. This feature helps you <strong>stay on top of user engagement</strong> and quickly catch spam or inappropriate comments.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-comment-post-author-notification-email',
			'name'        => 'adminease[updates-and-notifications][disable_comment_post_author_notification_email]',
			'value'       => $this->settings['disable_comment_post_author_notification_email'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable comment post author notification email', 'adminease' ),
			'description' => __( 'The <strong>Comment Post Author Notification Email</strong> is sent to the site administrator whenever a new comment is posted on the site. It includes the comment content, author details, and a direct link to moderate or reply. This feature helps you <strong>stay on top of user engagement</strong> and quickly catch spam or inappropriate comments.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-recovery-mode-email-notification',
			'name'        => 'adminease[updates-and-notifications][disable_recovery_mode_email_notification]',
			'value'       => $this->settings['disable_recovery_mode_email_notification'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable recovery mode email notification', 'adminease' ),
			'description' => __( 'The <strong>Recovery Mode Email Notification</strong> is sent to the site administrator when recovery mode is activated. This email provides details about the activation and includes a link to deactivate recovery mode. This feature helps you <strong>monitor site security</strong> and take immediate action if necessary.', 'adminease' ),
		];
		
		$fields['updates-and-notifications']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'customize-recovery-mode-recipient-email',
			'name'         => 'adminease[updates-and-notifications][customize_recovery_mode_recipient_email]',
			'value'        => $this->settings['customize_recovery_mode_recipient_email'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Customize recovery mode recipient email', 'adminease' ),
			'description'  => __( 'By default, recovery mode notification emails are sent to the site administrator email address. You can specify a different email address here to receive these notifications instead.', 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'text',
					'id'                => 'recovery-mode-recipient-email',
					'name'              => 'adminease[updates-and-notifications][recovery_mode_recipient_email]',
					'value'             => $this->settings['recovery_mode_recipient_email'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Recovery mode recipient email', 'adminease' ),
					'description'       => __( 'Enter the email address where recovery mode notifications should be sent.', 'adminease' ),
					'field_description' => __( 'You can enter multiple email addresses separated by commas.', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'customize-recovery-mode-recipient-email',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Disables the email notification sent to the site administrator when a new user is registered.
	 * This method applies filters to prevent the default behavior of sending email notifications
	 * to the site administrator upon new user registration. Compatibility measures are included
	 * for different WordPress versions.
	 * @return void
	 */
	public function disable_new_user_admin_notification_email(): void {
		// For WordPress 6.1.0 and newer
		add_filter( 'wp_send_new_user_notification_to_admin', '__return_false', 10, 2 );
		// For WordPress 4.9.0+ (fallback)
		add_filter( 'wp_new_user_notification_email_admin', '__return_false', 10, 3 );
	}
	
	/**
	 * Modifies the password change notification email for a user.
	 * This method updates the recipient of the password change notification email,
	 * effectively disabling the default behavior by setting the recipient to an empty value.
	 *
	 * @param array   $wp_password_change_notification_email The email data for the password change notification.
	 * @param WP_User $user The user object for whom the password change notification is being processed.
	 * @param string  $blogname The name of the blog where the password change occurred.
	 *
	 * @return array The filtered email data with updated recipient information.
	 */
	public function wp_password_change_notification_email( array $wp_password_change_notification_email, WP_User $user, string $blogname ): array {
		$wp_password_change_notification_email['to'] = '';
		
		return $wp_password_change_notification_email;
	}
	
	/**
	 * Modifies the email recipient(s) for recovery mode notifications.
	 * This method updates the 'to' field in the email data to use the configured
	 * recovery mode recipient email address from the settings. If multiple email
	 * addresses are specified (comma-separated), they are sanitized and added as an array.
	 * If no recovery mode recipient email is configured, the original email data is returned.
	 *
	 * @param array $email_data The original email data array containing email details like 'to', 'subject', etc.
	 *
	 * @return array The modified email data array with an updated 'to' field for recovery mode notifications.
	 */
	public function recovery_mode_email( array $email_data ): array {
		$recovery_mode_recipient_email = $this->settings['recovery_mode_recipient_email'] ?? '';
		
		if( empty( $recovery_mode_recipient_email ) ) {
			return $email_data;
		}
		
		if( str_contains( $recovery_mode_recipient_email, ',' ) ) {
			$recovery_mode_recipient_email = array_map( 'trim', explode( ',', $recovery_mode_recipient_email ) );
		}
		
		if( is_array( $recovery_mode_recipient_email ) ) {
			$recovery_mode_recipient_email = array_map( 'sanitize_email', $recovery_mode_recipient_email );
		} else {
			$recovery_mode_recipient_email = sanitize_email( $recovery_mode_recipient_email );
		}
		
		$email_data['to'] = $recovery_mode_recipient_email;
		
		return $email_data;
	}
}