<?php

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

use Nextgenthemes\WP\Settings;
use Nextgenthemes\WP\SettingsData;

function settings_instance(): Settings {

	static $instance = null;

	if ( null === $instance ) {

		$instance = new Settings(
			array(
				'namespace'           => __NAMESPACE__,
				'base_url'            => plugins_url( '', PLUGIN_FILE ),
				'base_path'           => PLUGIN_DIR,
				'plugin_file'         => PLUGIN_FILE,
				'settings'            => settings_data(),
				'menu_title'          => esc_html__( 'TweakMaster', 'tweakmaster' ),
				'settings_page_title' => esc_html__( 'TweakMaster', 'tweakmaster' ),
				'tabs'                => array(
					'general'     => array( 'title' => __( 'General', 'tweakmaster' ) ),
					'privacy'     => array( 'title' => __( 'Privacy', 'tweakmaster' ) ),
					'media'       => array( 'title' => __( 'Media', 'tweakmaster' ) ),
					'revisions'   => array( 'title' => __( 'Revisions', 'tweakmaster' ) ),
					'security'    => array( 'title' => __( 'Security', 'tweakmaster' ) ),
					'performance' => array( 'title' => __( 'Performance', 'tweakmaster' ) ),
					'plugins'     => array( 'title' => __( 'Plugins', 'tweakmaster' ) ),
					'tools'       => array( 'title' => __( 'Tools', 'tweakmaster' ) ),
				),
			)
		);
	}

	return $instance;
}

/**
 * @return array<string, mixed>
 */
function options(): array {
	return settings_instance()->get_options();
}

/**
 * @return array<string, mixed>
 */
function default_options(): array {
	return settings_instance()->get_options_defaults();
}

function settings_data(): SettingsData {
	$settings = array_merge(
		general_settings(),
		privacy_settings(),
		media_settings(),
		revision_settings(),
		security_settings(),
		performance_settings(),
		plugins_settings(),
		tools_settings()
	);

	return new SettingsData( $settings );
}

/**
 * @return array<string, array<string, mixed>>
 */
function general_settings(): array {
	return array(
		'remove-asset-ver-parameter' => array(
			'tab'         => 'general',
			'label'       => __( 'Remove version query strings', 'tweakmaster' ),
			'description' => __( 'Removes <code>?ver=1.2.3</code> from all styles and scripts.', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-feeds' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable feeds', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-emojis' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable emojis', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'search-single-result-redirect' => array(
			'tab'         => 'general',
			'label'       => __( 'Search single result redirect', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disallow-file-edit' => array(
			'tab'         => 'general',
			'label'       => __( 'Disallow File Edits', 'tweakmaster' ),
			'description' => __( 'Disables the ability to edit files in the file manager. Sets <code>DISALLOW_FILE_EDIT</code> constant to true. Does only work if the constant is already defined, usually in wp-config.php', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'admin-bar-greeting' => array(
			'tab'         => 'general',
			'label'       => __( 'Admin bar greeting', 'tweakmaster' ),
			'description' => __( 'Replace "Howdy, {name}" with a custom message. Use <code>{name}</code> for the user\'s display name. For example <code>Hi, {name}!</code>. Leave empty for no greeting. Use <code>default</code> for the default greeting, preventing the tweak from running.', 'tweakmaster' ),
			'type'        => 'string',
			'default'     => 'default',
		),
		'enable-fonts-to-uploads' => array(
			'tab'         => 'general',
			'label'       => __( 'Enable fonts to uploads', 'tweakmaster' ),
			'description' => __( 'Move (Google) Fonts enabled in the Block Editor from wp-content/fonts to wp-content/uploads', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-auto-trash-emptying' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable auto trash emptying', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'trash-keep-days' => array(
			'tab'         => 'general',
			'label'       => __( 'Set trash keep days', 'tweakmaster' ),
			'description' => __( 'Set the number of days to keep posts in the trash. Default is 30 days.', 'tweakmaster' ),
			'type'        => 'integer',
			'default'     => 30,
		),
		'scroll-progress-bar' => array(
			'tab'         => 'general',
			'label'       => __( 'Scroll progress bar', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'scroll-progress-bar-progress-bg-color' => array(
			'tab'         => 'general',
			'label'       => __( 'Scroll progress color', 'tweakmaster' ),
			'type'        => 'string',
			'description' => sprintf(
				'And valid css will work. You can use <a href="%s" target="_blank">this color picker!</a>.',
				'https://oklch.com'
			),
			'default'     => 'var(--wp--preset--color--primary, oklch(45.44% 0.1924 270 / 78.17%))',
		),
		'scroll-progress-bar-bg-color' => array(
			'tab'         => 'general',
			'label'       => __( 'Scroll progress bar background color', 'tweakmaster' ),
			'type'        => 'string',
			'default'     => 'transparent',
		),
		'scroll-progress-bar-progress-height' => array(
			'tab'         => 'general',
			'label'       => __( 'Scroll progress height', 'tweakmaster' ),
			'type'        => 'string',
			'default'     => '0.4rem',
		),
		'disable-ssl-verify-self' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable self SSL verify', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		// 'disable-auto-updates' => array(
		//  'tab'         => 'general',
		//  'label'       => __( 'Disable Auto Updates', 'tweakmaster' ),
		//  'type'        => 'boolean',
		//  'default'     => false,
		// ),
		'disable-comments' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable Comments', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-email-login' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable Email Login', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-rest-api' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable REST API', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-success-update-emails' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable Success Update Emails', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'remove-admin-bar-logo' => array(
			'tab'         => 'general',
			'label'       => __( 'Remove Admin Bar WordPress Logo', 'tweakmaster' ),
			'description' => __( 'Requires a hard refresh of the page to take effect.', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'remove-asset-attr' => array(
			'tab'         => 'general',
			'label'       => __( 'Remove Asset Attributes', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-non-production-emails' => array(
			'tab'         => 'general',
			'label'       => __( 'Disable Non Production Emails', 'tweakmaster' ),
			'description' => __( 'If WP_ENV (Trellis) or wp_get_environment_type is not production, emails sending is mocked.', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'remove-rest-api-links' => array(
			'tab'         => 'general',
			'label'       => __( 'Remove REST API links', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'admin-email-check-interval' => array(
			'tab'         => 'general',
			'label'       => __( 'Admin email check interval', 'tweakmaster' ),
			'type'        => 'integer',
			'default'     => 15552000,
			'description' => __( 'Time in seconds to display email verification message on login. Set to 0 to disable. Default is 15552000 (6 months).', 'tweakmaster' ),
		),
		'admin-footer-text' => array(
			'tab'         => 'general',
			'label'       => __( 'Admin footer text', 'tweakmaster' ),
			'description' => __( 'a href links are allowed. Use "default" for default text.', 'tweakmaster' ),
			'type'        => 'string',
			'default'     => 'default',
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function security_settings(): array {

	ob_start();
	wp_generator();
	$generator_html = ob_get_clean();

	$remove_wp_version_description = wp_kses(
		sprintf(
			/* translators: %s: example of a "generator" meta tag */
			__( 'Remove <code>%s</code> from html head', 'tweakmaster' ),
			esc_html( $generator_html )
		),
		array(
			'code' => array(),
		)
	);

	return array(
		'disable-xmlrpc' => array(
			'tab'         => 'security',
			'label'       => __( 'Disable XML-RPC', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'disable-xmlrpc-allow-jetpack-ips' => array(
			'tab'         => 'security',
			'label'       => __( 'Disable XML-RPC - allow Jetpack IPs', 'tweakmaster' ),
			'description' => __( 'Allow XML-RPC only from Jetpack IPs', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'remove-wp-version' => array(
			'tab'         => 'security',
			'default'     => false,
			'option'      => true,
			'label'       => __( 'Remove WP version', 'tweakmaster' ),
			'type'        => 'boolean',
			// translators: %s is example html
			'description' => $remove_wp_version_description,
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function performance_settings(): array {
	return array(
		'dequeue-jquery-migrate' => array(
			'tab'         => 'performance',
			'label'       => __( 'Dequeue jQuery Migrate', 'tweakmaster' ),
			'description' => __( 'Dequeue jQuery Migrate from the jQuery script dependencies on the frontend. This is used to help devs debug from old versions of jQuery. You really do not need this on a production site.', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'enable-script-optimizer' => array(
			'tab'         => 'performance',
			'label'       => __( 'Script Optimizer', 'tweakmaster' ),
			'description' => __( 'Optimize script loading by moving them into the <code>head</code> and adding <code>defer</code> attribute. This may break your site. Use at your own risk!', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'enable-relative-urls' => array(
			'tab'         => 'performance',
			'label'       => __( 'Enable relative URLs', 'tweakmaster' ),
			'description' => __( 'Enable relative URLs on the frontend. This may break your site. Use at your own risk!', 'tweakmaster' ),
			'type'        => 'boolean',
			'default'     => false,
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function media_settings(): array {
	return array(
		'convert-jpeg-to-avif' => array(
			'tab'         => 'media',
			'default'     => false,
			'label'       => __( 'Convert jpeg to avif', 'tweakmaster' ),
			'description' => __( 'Convert uploaded jpeg to avif', 'tweakmaster' ),
			'type'        => 'boolean',
		),
		'enable-clean-upload-filenames' => array(
			'tab'         => 'media',
			'default'     => false,
			'label'       => __( 'Clean upload filenames', 'tweakmaster' ),
			'description' => __( 'Sanitize media filenames to remove non-latin special characters and accents', 'tweakmaster' ),
			'type'        => 'boolean',
		),
		'avif-compression' => array(
			'tab'         => 'media',
			'default'     => 82,
			'label'       => __( 'Avif compression', 'tweakmaster' ),
			'type'        => 'integer',
			'description' => __( 'Default is 82', 'tweakmaster' ),
		),
		'jpeg-compression' => array(
			'tab'         => 'media',
			'default'     => 82,
			'label'       => __( 'Jpeg compression', 'tweakmaster' ),
			'description' => __( 'Default is 82', 'tweakmaster' ),
			'type'        => 'integer',
		),
		'webp-compression' => array(
			'tab'         => 'media',
			'default'     => 86,
			'label'       => __( 'Webp compression', 'tweakmaster' ),
			'description' => __( 'Default is 86', 'tweakmaster' ),
			'type'        => 'integer',
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function privacy_settings(): array {

	// https://github.com/WordPress/WordPress/blob/c10dbc8434d14371366e413e510cb0d54b4f367b/wp-includes/class-wp-http.php#L206
	$default_user_agent     = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
	$chrome_user_agent      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';
	$user_agent_description = wp_kses(
		sprintf(
			// translators: %1$s: Default user agent. %2$s: Chrome user agent.
			__( 'WP really hates privacy and sends this <code>%1$s</code> to every site it makes calls to. You can empty the field, pretend to be Chrome <code>%2$s</code> or something else. <code>default</code> will change nothing.', 'tweakmaster' ),
			esc_html( $default_user_agent ),
			esc_html( $chrome_user_agent )
		),
		array(
			'code' => array(),
		)
	);

	return array(
		'user-agent' => array(
			'tab'         => 'privacy',
			'default'     => 'default',
			'option'      => true,
			'label'       => __( 'Set user agent', 'tweakmaster' ),
			'type'        => 'string',
			'description' => $user_agent_description,
		),

		'remove-exif' => array(
			'tab'         => 'privacy',
			'default'     => false,
			'option'      => true,
			'label'       => __( 'Remove EXIF', 'tweakmaster' ),
			'description' => __( 'Remove EXIF data from uploaded images.', 'tweakmaster' ),
			'type'        => 'boolean',
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function plugins_settings(): array {

	return array(
		'disable-cf7-css' => array(
			'tab'         => 'plugins',
			'default'     => false,
			'option'      => true,
			'label'       => __( 'Disable Contact Form 7 CSS', 'tweakmaster' ),
			'description' => __( 'Sets <code>wpcf7_load_css</code> filter to <code>false</code>', 'tweakmaster' ),
			'type'        => 'boolean',
		),
		'disable-cf7-autop' => array(
			'tab'         => 'plugins',
			'default'     => false,
			'option'      => true,
			'label'       => __( 'Disable Contact Form 7 Autop', 'tweakmaster' ),
			'description' => __( 'Sets <code>wpcf7_autop_or_not</code> filter to <code>false</code>', 'tweakmaster' ),
			'type'        => 'boolean',
		),
		'enable-jetpack-offline-mode' => array(
			'tab'         => 'plugins',
			'default'     => false,
			'option'      => true,
			'label'       => __( 'Enable Jetpack offline mode', 'tweakmaster' ),
			'type'        => 'boolean',
		),
	);
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function revision_settings(): array {

	$settings = array();

	foreach ( get_revision_post_types() as $type => $name ) {
		$settings[ 'revisions-limit-' . $type ] = array(
			'tab'         => 'revisions',
			'default'     => -1,
			'option'      => true,
			// translators: %s is post type name
			'label'       => sprintf( __( 'Revisions for %s', 'tweakmaster' ), $name ),
			'type'        => 'integer',
		);
	}

	$settings['revisions-limit-all'] = array(
		'tab'         => 'revisions',
		'default'     => -1,
		'option'      => true,
		'label'       => __( 'Limit ALL revisions', 'tweakmaster' ),
		'description' => __( 'Limit revisions for all post types. This will override the limit for each post type from above!', 'tweakmaster' ),
		'type'        => 'integer',
	);

	return $settings;
}

/**
 * @return array<string, array<string, string|int|bool|float>>
 */
function tools_settings(): array {

	return array(
		'enable-maintenance-mode' => array(
			'tab'         => 'tools',
			'default'     => false,
			'label'       => __( 'Enable maintenance mode', 'tweakmaster' ),
			'type'        => 'boolean',
		),
		'enable-duplicate-post' => array(
			'tab'         => 'tools',
			'default'     => false,
			'label'       => __( 'Enable duplicate post', 'tweakmaster' ),
			'type'        => 'boolean',
		),
	);
}

function option_is_set( string $key ): bool {

	$default_option = default_options()[ $key ];

	return options()[ $key ] !== $default_option;
}

/**
 * @param array<string> $options_keys
 */
function any_option_is_set( array $options_keys ): bool {

	foreach ( $options_keys as $key ) {
		if ( option_is_set( $key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get post types which support revisions.
 *
 * @return array<string, string>
 */
function get_revision_post_types(): array {

	$revision_post_types = [];

	foreach ( get_post_types() as $type ) {

		$object = get_post_type_object( $type );

		if ( ! post_type_supports( $type, 'revisions' ) || null === $object ) {
			continue;
		}

		$name = ( property_exists( $object, 'labels' ) && property_exists( $object->labels, 'name' ) )
			? $object->labels->name
			: $object->name;

		$revision_post_types[ $type ] = $name;
	}

	return $revision_post_types;
}
