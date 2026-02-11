<?php
/**
 * Option settings of the plugin
 *
 * @package awe
 *
 * @since 2.0.0
 */

use ADVAN\Helpers\Settings;

$settings = Settings::get_current_options();

foreach ( $settings['severities'] as $name => $severity ) {
	$settings[ 'severity_colors_' . $name . '_color' ] = $severity['color'];
	$settings[ 'severity_show_' . $name . '_display' ] = $severity['display'];
}

Settings::set_current_options( $settings );

	Settings::build_option(
		array(
			'title' => esc_html__( 'Serer Info Options', '0-day-analytics' ),
			'id'    => 'server-info-options-settings-tab',
			'type'  => 'tab-title',
		)
	);

	// Cron options.
	Settings::build_option(
		array(
			'title' => \esc_html__( 'Server info options', '0-day-analytics' ),
			'id'    => 'server-info-settings-options',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Enable Server Info module', '0-day-analytics' ),
			'id'      => 'server_info_module_enabled',
			'type'    => 'checkbox',
			'hint'    => \esc_html__( 'If you disable this, the entire plugin server info module will be disabled. This applies only for the plugin module (if you are not using it and don\'t want it to take unnecessarily resources.', '0-day-analytics' ),
			'toggle'  => '#advana_server_info_settings-item',
			'default' => Settings::get_option( 'server_info_module_enabled' ),
		)
	);
	?>
<div id="advana_server_info_settings-item">
	<?php
	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Enable CPU info in Admin bar', '0-day-analytics' ),
			'id'      => 'advana_server_info_cpu_enable',
			'type'    => 'checkbox',
			'default' => Settings::get_option( 'advana_server_info_cpu_enable' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Enable MEM info in Admin bar', '0-day-analytics' ),
			'id'      => 'advana_server_info_mem_enable',
			'type'    => 'checkbox',

			'default' => Settings::get_option( 'advana_server_info_mem_enable' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Enable HDD info in Admin bar', '0-day-analytics' ),
			'id'      => 'advana_server_info_hdd_enable',
			'type'    => 'checkbox',

			'default' => Settings::get_option( 'advana_server_info_hdd_enable' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Refresh Admin Bar info interval in seconds', '0-day-analytics' ),
			'id'      => 'advana_server_info_admin_bar_refresh_interval',
			'type'    => 'number',
			'min'     => 1,
			'max'     => 100,
			'hint'    => \esc_html__( 'Set the interval for refreshing the Admin Bar info in seconds. Maximum allowed number is 100, minimum is 1.', '0-day-analytics' ),
			'default' => Settings::get_option( 'advana_server_info_admin_bar_refresh_interval' ),
		)
	);
	?>
</div>
