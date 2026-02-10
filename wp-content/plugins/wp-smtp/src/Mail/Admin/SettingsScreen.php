<?php

namespace SolidWP\Mail\Admin;

/**
 * Class SettingsScreen
 *
 * Manages the settings screen for the Solid Mail plugin.
 */
class SettingsScreen {
	/**
	 * Settings screen slug.
	 */
	public const SETTINGS_SLUG = 'solid_mail_settings';

	/**
	 * Registers the settings screen.
	 *
	 * Defines the setting for the Solid Mail settings, including type, description, default values, and REST schema.
	 */
	public function register_settings_screen() {
		register_setting(
			self::SETTINGS_SLUG,
			self::SETTINGS_SLUG,
			[
				'type'              => 'object',
				'description'       => esc_html__( 'Solid Mail Settings', 'LION' ),
				'sanitize_callback' => [ $this, 'sanitize_setting' ],
				'default'           => $this->get_default_settings(),
				'show_in_rest'      => [
					'schema' => [
						'properties' => [
							'disable_logs'              => [
								'type'        => 'string',
								'description' => esc_html__( 'Enable or disable logging of sent emails.', 'LION' ),
							],
							'use_unmatched_connections' => [
								'type'        => 'string',
								'description' => esc_html__( 'If the default connection fails then an alternative connection will be used as a fallback. Connections with the same "from" address are prioritized.', 'LION' ),
							],
						],
					],
				],
			] 
		);
	}

	/**
	 * Sanitizes the setting values.
	 *
	 * Ensures that the provided settings are valid and safe to use.
	 *
	 * @param array $value The settings values to sanitize.
	 *
	 * @return array The sanitized settings values.
	 */
	public function sanitize_setting( $value ) {
		$parse_args = wp_parse_args( $value, $this->get_default_settings() );

		if ( ! in_array( $parse_args['disable_logs'], [ 'no', 'yes' ], true ) ) {
			$parse_args['disable_logs'] = 'no';
		}

		if ( ! in_array( $parse_args['use_unmatched_connections'], [ 'no', 'yes' ], true ) ) {
			$parse_args['use_unmatched_connections'] = 'no';
		}

		return $parse_args;
	}

	/**
	 * Gets the default settings.
	 *
	 * Returns an array of default settings for the Solid Mail plugin.
	 *
	 * @return array The default settings.
	 */
	public function get_default_settings(): array {
		return [
			'disable_logs'              => 'no',
			'use_unmatched_connections' => 'no',
		];
	}

	/**
	 * Retrieves the plugin settings.
	 *
	 * @return array The sanitized settings array.
	 */
	public function get_settings(): array {
		$settings = get_option( self::SETTINGS_SLUG, [] );

		return $this->sanitize_setting( $settings );
	}
}
