<?php

namespace SolidWP\Mail\Repository;

use SolidWP\Mail\Admin\SettingsScreen;

/**
 * Class SettingsRepository
 *
 * This class handles settings-related operations for the Solid Mail plugin.
 * It provides methods to retrieve and check plugin settings.
 *
 * @package SolidWP\Mail\Repository
 */
class SettingsRepository {

	/**
	 * The SettingsScreen instance.
	 *
	 * @var SettingsScreen
	 */
	private SettingsScreen $settings_screen;

	/**
	 * Constructor.
	 *
	 * @param SettingsScreen $settings_screen The SettingsScreen instance.
	 */
	public function __construct( SettingsScreen $settings_screen ) {
		$this->settings_screen = $settings_screen;
	}

	/**
	 * Checks if logs are disabled.
	 *
	 * @return bool True if logs are disabled, false otherwise.
	 */
	public function logs_disabled(): bool {
		$settings = $this->settings_screen->get_settings();

		return $settings['disable_logs'] === 'yes';
	}

	/**
	 * Checks if unmatched connections should be used as fallbacks.
	 *
	 * @return bool True if unmatched connections should be used, false otherwise.
	 */
	public function use_unmatched_connections(): bool {
		$settings = $this->settings_screen->get_settings();

		return $settings['use_unmatched_connections'] === 'yes';
	}
}
