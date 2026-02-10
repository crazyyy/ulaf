<?php
/**
 * Option settings of the plugin
 *
 * @package awe
 *
 * @since 2.0.0
 */

use ADVAN\Helpers\Settings;

	Settings::build_option(
		array(
			'title' => \esc_html__( 'PHP errors list Options', '0-day-analytics' ),
			'id'    => 'options-settings-tab',
			'type'  => 'tab-title',
		)
	);

	// Cron options.
	Settings::build_option(
		array(
			'title' => \esc_html__( 'PHP errors list options', '0-day-analytics' ),
			'id'    => 'fatals-settings-options',
			'type'  => 'header',
			'hint'  => \esc_html__( 'This module stores errors (coming from PHP) in a separate table, which gives more control and more consistent way to manage / check them. It can be used in parallel with the Error Log viewer. This module helps when error logging is disabled completely, but it can not help with most of the WP core errors, because usually when debug is disabled, WP does not generates them. Also you may see errors that are not part of the WP error log, simply because some of the PHP-cli executions do not log in it, but this module is still referenced. To simplify the above - module collects errors generated from PHP itself, only when it is enabled, and stores them in the separate DB table.', '0-day-analytics' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => \esc_html__( 'Enable PHP errors list module', '0-day-analytics' ),
			'id'      => 'fatals_module_enabled',
			'type'    => 'checkbox',
			'hint'    => \esc_html__( 'If you disable this, the entire PHP errors module will be disabled. This applies only for the plugin module (if you are not using it and don\'t want it to take unnecessarily resources.', '0-day-analytics' ),
			'default' => Settings::get_option( 'fatals_module_enabled' ),
		)
	);
