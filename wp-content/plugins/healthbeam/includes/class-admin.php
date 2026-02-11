<?php
namespace HealthBeam;

class Admin
{
	public function __construct()
	{
		add_filter('site_health_navigation_tabs', array($this, 'add_tools_tab'));
		add_action('site_health_tab_content', array($this, 'add_tools_tab_content'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_filter('plugin_action_links_' . plugin_basename(HEALTHBEAM_PATH . 'healthbeam.php'), array($this, 'add_plugin_action_links'));
	}

	public function add_tools_tab($tabs)
	{
		$tabs['advanced-tools'] = __('Advanced Tools', 'healthbeam');
		return $tabs;
	}

	public function add_tools_tab_content($tab)
	{
		if ('advanced-tools' !== $tab) {
			return;
		}

		echo '<div id="healthbeam-root"></div>';
	}

	public function enqueue_scripts()
	{
		$screen = get_current_screen();
		if (!$screen || 'site-health' !== $screen->id) {
			return;
		}

		$asset_file = include HEALTHBEAM_PATH . 'build/index.asset.php';

		wp_enqueue_script(
			'healthbeam',
			HEALTHBEAM_URL . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_style(
			'healthbeam',
			HEALTHBEAM_URL . 'build/index.css',
			array('wp-components'),
			$asset_file['version']
		);

		wp_localize_script(
			'healthbeam',
			'healthBeamSettings',
			array(
				'root' => esc_url_raw(rest_url('healthbeam/v1/')),
				'nonce' => wp_create_nonce('wp_rest'),
				'current_user_email' => wp_get_current_user()->user_email,
			)
		);
	}

	public function add_plugin_action_links($links)
	{
		$settings_link = '<a href="' . esc_url(admin_url('site-health.php?tab=advanced-tools')) . '">' . __('Settings', 'healthbeam') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
}
