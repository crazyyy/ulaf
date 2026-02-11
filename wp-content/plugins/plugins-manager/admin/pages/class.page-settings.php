<?php

namespace WTBP;

class Page_Settings extends PageBaseSettings {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Settings constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->id                 = "settings";
		$this->page_menu_position = 20;
		$this->page_title         = __( 'Plugins Page Tweaker', 'bulk-plugins' );
		$this->page_menu_title    = __( 'Plugins Page Tweaker', 'bulk-plugins' );
		$this->template           = 'admin/settings-page';

		$this->settings = $this->settings();
	}

	/**
	 * Array of the settings
	 *
	 * @return array
	 */
	public function settings() {
		$settings = [
			'settings_group' => [ //unique slug of the settings group
				'sections' => [
					[
						'title'   => __( 'General settings', 'bulk-plugins' ),
						'slug'    => 'section_general',
						'options' => [
							'show_icons_column' => [
								'type'    => 'checkbox',
								'title'   => __( 'Show plugins icons column', 'bulk-plugins' ),
								'default' => 1,
							],
							'show_changelog'  => [
								'type'    => 'checkbox',
								'title'   => __( 'Show changelog in update notice', 'bulk-plugins' ),
								'default' => 0,
							],
							'show_git'          => [
								'type'    => 'checkbox',
								'title'   => __( 'Check and show GIT', 'bulk-plugins' ),
								'default' => 1,
							],
							'show_git_branch'   => [
								'type'    => 'checkbox',
								'title'   => __( 'Show GIT branch', 'bulk-plugins' ),
								'default' => 0,
							],
							'change_autoupdate'   => [
								'type'    => 'checkbox',
								'title'   => __( 'Replace autoupdate text to checkboxes', 'bulk-plugins' ),
								'default' => 1,
							],
						],
					],
				],
			],
		];

		return $settings;
	}

}