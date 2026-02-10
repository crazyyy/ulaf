<?php
/**
 * Snippets settings for the plugin.
 *
 * These options were previously embedded in the general `advanced.php` settings
 * file but belong in their own standalone file to keep settings organized.
 *
 * @package awe
 *
 * @since 1.1.0
 */

use ADVAN\Helpers\Settings;

$settings = Settings::get_current_options();
Settings::set_current_options( $settings );

Settings::build_option(
	array(
		'title' => esc_html__( 'Snippets Options', '0-day-analytics' ),
		'id'    => 'snippets-options-settings-tab',
		'type'  => 'tab-title',
	)
);

Settings::build_option(
	array(
		'type'  => 'header',
		'id'    => 'snippets-settings-options',
		'title' => \esc_html__( 'Snippets options', '0-day-analytics' ),
	)
);

Settings::build_option(
	array(
		'name'    => \esc_html__( 'Enable Snippets module', '0-day-analytics' ),
		'id'      => 'snippets_module_enabled',
		'type'    => 'checkbox',
		'hint'    => \esc_html__( 'If you disable this, the Snippets module will be disabled and related options will be hidden.', '0-day-analytics' ),
		'toggle'  => '#advana_snippets_settings-item',
		'default' => Settings::get_option( 'snippets_module_enabled' ),
	)
);

?>
<div id="advana_snippets_settings-item">
<?php
Settings::build_option(
	array(
		'name'    => \esc_html__( 'Sandbox storage location', '0-day-analytics' ),
		'id'      => 'snippets_temp_storage',
		'type'    => 'radio',
		'default' => Settings::get_option( 'snippets_temp_storage' ),
		'options' => array(
			'uploads'  => \esc_html__( 'Dedicated folder inside uploads/', '0-day-analytics' ),
			'php_temp' => \esc_html__( 'php://temp memory stream', '0-day-analytics' ),
		),
		'hint'    => \esc_html__( 'Use the uploads directory when /tmp is unavailable, or switch to php://temp to avoid touching the filesystem.', '0-day-analytics' ),
	)
);

?>
</div>

