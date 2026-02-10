<?php
namespace WpAssetCleanUp;

/**
 * Class AdminBar
 * @package WpAssetCleanUp
 */
class AdminBar
{
	/**
	 *
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'topBar' ) );

		// Hide top WordPress admin bar on request for debugging purposes and a cleared view of the tested page
		// This is done in /early-triggers.php within assetCleanUpNoLoad() function
	}

	/**
	 *
	 */
	public function topBar()
	{
		if (Menu::userCanManageAssets() && (! Main::instance()->settings['hide_from_admin_bar'])) {
			add_action( 'admin_bar_menu', array( $this, 'topBarInfo' ), 81 );
		}
	}

	/**
	 * @param $wp_admin_bar
	 */
	public function topBarInfo($wp_admin_bar)
	{
		$topTitle = WPACU_PLUGIN_TITLE;

		$wpacuUnloadedAssetsStatus = false;

		if (! is_admin()) {
			$markedCssListForUnload = isset(Main::instance()->allUnloadedAssets['styles'])  ? array_unique(Main::instance()->allUnloadedAssets['styles'])  : array();
			$markedJsListForUnload  = isset(Main::instance()->allUnloadedAssets['scripts']) ? array_unique(Main::instance()->allUnloadedAssets['scripts']) : array();
			$wpacuUnloadedAssetsStatus = (count($markedCssListForUnload) + count($markedJsListForUnload)) > 0;
		}

		// [wpacu_pro]
		$wpacuUnloadedPluginsStatus = false;

		if ( isset( $GLOBALS['wpacu_filtered_plugins'] ) && $wpacuFilteredPlugins = $GLOBALS['wpacu_filtered_plugins'] ) {
			$wpacuUnloadedPluginsStatus = true; // there are rules applied
		}
		$anyUnloadedItems = ($wpacuUnloadedPluginsStatus || $wpacuUnloadedAssetsStatus);
		// [/wpacu_pro]

		if ($anyUnloadedItems) {
		$styleAttrType = Misc::getStyleTypeAttribute();

			$cssStyle = <<<HTML
<style {$styleAttrType}>
#wpadminbar .wpacu-alert-sign-top-admin-bar {
    font-size: 20px;
    color: lightyellow;
    vertical-align: top;
    margin: -7px 0 0;
    display: inline-block;
    box-sizing: border-box;
}

#wp-admin-bar-assetcleanup-plugin-unload-rules-notice-default .ab-item {
	min-width: 250px !important;
}

#wp-admin-bar-assetcleanup-plugin-unload-rules-notice .ab-item > .dashicons-admin-plugins {
	width: 20px;
	height: 20px;
    font-size: 20px;
    line-height: normal;
    vertical-align: middle;
    margin-top: -2px;
}
</style>
HTML;
			$topTitle .= $cssStyle . '&nbsp;<span class="wpacu-alert-sign-top-admin-bar dashicons dashicons-filter"></span>';
		}

		if (Main::instance()->settings['test_mode']) {
			$topTitle .= '&nbsp; <span class="dashicons dashicons-admin-tools"></span> <strong>TEST MODE</strong> is <strong>ON</strong>';

			// [wpacu_pro]
			if ( ! (defined('WPACU_ALLOW_DASH_PLUGIN_FILTER') && WPACU_ALLOW_DASH_PLUGIN_FILTER) ) {
				// point out the fact that "Test Mode" is only relevant in this situation within the front-end view
				// as plugin filtering (unloading) is not enabled within the Dashboard view
				$topTitle .= ' (front-end view)';
			}
			// [/wpacu_pro]
		}

		$goBackToCurrentUrl = '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$wp_admin_bar->add_menu(array(
			'id'    => 'assetcleanup-parent',
			'title' => $topTitle,
			'href'  => esc_url(admin_url('admin.php?page=' . WPACU_PLUGIN_ID . '_settings'))
		));

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-settings',
			'title'  => __('Settings', 'wp-asset-clean-up'),
			'href'   => esc_url(admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_settings'))
		));

		$wp_admin_bar->add_menu( array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-clear-css-js-files-cache',
			'title'  => __('Clear CSS/JS Files Cache', 'wp-asset-clean-up'),
			'href'   => esc_url(wp_nonce_url( admin_url( 'admin-post.php?action=assetcleanup_clear_assets_cache' . $goBackToCurrentUrl ), 'assetcleanup_clear_assets_cache' ))
		) );

		// Only trigger in the front-end view
		if (! is_admin()) {
			if ( ! Misc::isHomePage() ) {
				// Not on the home page
				$homepageManageAssetsHref = Main::instance()->frontendShow()
					? get_site_url().'#wpacu_wrap_assets'
					: esc_url(admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_assets_manager&wpacu_for=homepage' ));

				$wp_admin_bar->add_menu(array(
					'parent' => 'assetcleanup-parent',
					'id'     => 'assetcleanup-homepage',
					'title'  => esc_html__('Manage Homepage Assets', 'wp-asset-clean-up'),
					'href'   => $homepageManageAssetsHref
				));
			} else {
				// On the home page
				// Front-end view is disabled! Go to Dashboard link
				if ( ! Main::instance()->frontendShow() ) {
					$wp_admin_bar->add_menu( array(
						'parent' => 'assetcleanup-parent',
						'id'     => 'assetcleanup-homepage',
						'title'  => esc_html__('Manage Page Assets', 'wp-asset-clean-up'),
						'href'   => esc_url(admin_url('admin.php?page=' . WPACU_PLUGIN_ID . '_assets_manager&wpacu_for=homepage'))
					) );
				}
			}
		}

		if (! is_admin() && Main::instance()->frontendShow()) {
			$wp_admin_bar->add_menu(array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-jump-to-assets-list',
				'title'  => esc_html__('Manage Page Assets', 'wp-asset-clean-up'),
				'href'   => '#wpacu_wrap_assets'
			));
		}

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-bulk-unloaded',
			'title'  => esc_html__('Bulk Changes', 'wp-asset-clean-up'),
			'href'   => esc_url(admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_bulk_unloads'))
		));

		$wp_admin_bar->add_menu( array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-overview',
			'title'  => esc_html__('Overview', 'wp-asset-clean-up'),
			'href'   => esc_url(admin_url( 'admin.php?page=' . WPACU_PLUGIN_ID . '_overview'))
		) );

		$wp_admin_bar->add_menu(array(
			'parent' => 'assetcleanup-parent',
			'id'     => 'assetcleanup-support-forum',
			'title'  => esc_html__('Support Ticket', 'wp-asset-clean-up'),
			'href'   => 'https://www.gabelivan.com/contact/',
			'meta'   => array('target' => '_blank')
		));

		// [START LISTING UNLOADED ASSETS]
		if (! is_admin()) { // Frontend view (show any unloaded handles)
			$totalUnloadedAssets = count($markedCssListForUnload) + count($markedJsListForUnload);

			if ($totalUnloadedAssets > 0) {
				$titleUnloadText = sprintf( _n( '%d unload asset rules took effect on this frontend page',
					'%d unload asset rules took effect on this frontend page', $totalUnloadedAssets, 'wp-asset-clean-up' ),
					$totalUnloadedAssets );

				$wp_admin_bar->add_menu( array(
					'parent' => 'assetcleanup-parent',
					'id'     => 'assetcleanup-asset-unload-rules-notice',
					'title'  => '<span style="margin: -10px 0 0;" class="wpacu-alert-sign-top-admin-bar dashicons dashicons-filter"></span> &nbsp; '. $titleUnloadText,
					'href'   => '#'
				) );

				if ( count( $markedCssListForUnload ) > 0 ) {
					$wp_admin_bar->add_menu(array(
						'parent' => 'assetcleanup-asset-unload-rules-notice',
						'id'     => 'assetcleanup-asset-unload-rules-css',
						'title'  => esc_html__('CSS', 'wp-asset-clean-up'). ' ('.count( $markedCssListForUnload ).')',
						'href'   => '#'
					));
					sort($markedCssListForUnload);

					foreach ($markedCssListForUnload as $cssHandle) {
						$wp_admin_bar->add_menu(array(
							'parent' => 'assetcleanup-asset-unload-rules-css',
							'id'     => 'assetcleanup-asset-unload-rules-css-'.$cssHandle,
							'title'  => $cssHandle,
							'href'   => esc_url(admin_url('admin.php?page=wpassetcleanup_overview#wpacu-overview-css-'.$cssHandle))
						));
					}
				}

				if ( count( $markedJsListForUnload ) > 0 ) {
					$wp_admin_bar->add_menu(array(
						'parent' => 'assetcleanup-asset-unload-rules-notice',
						'id'     => 'assetcleanup-asset-unload-rules-js',
						'title'  => esc_html__('JavaScript', 'wp-asset-clean-up'). ' ('.count( $markedJsListForUnload ).')',
						'href'   => '#'
					));
					sort($markedJsListForUnload);

					foreach ($markedJsListForUnload as $jsHandle) {
						$wp_admin_bar->add_menu(array(
							'parent' => 'assetcleanup-asset-unload-rules-js',
							'id'     => 'assetcleanup-asset-unload-rules-js-'.$jsHandle,
							'title'  => $jsHandle,
							'href'   => esc_url(admin_url('admin.php?page=wpassetcleanup_overview#wpacu-overview-js-'.$jsHandle))
						));
					}
					}
			}
		}

		// [wpacu_pro]
		if ($wpacuUnloadedPluginsStatus) {
			$allPlugins = function_exists('get_plugins') ? \get_plugins() : Misc::getActivePlugins();

			$pluginsIcons = Misc::getAllActivePluginsIcons();

			if (is_admin()) { // Dashboard view
				$titleUnloadText = sprintf( _n( '%d unloaded plugin on this admin page',
					'%d unload plugin rules took effect on this admin page', count( $wpacuFilteredPlugins ), 'wp-asset-clean-up-pro' ),
					count( $wpacuFilteredPlugins ) );
			} else { // Frontend view
				$titleUnloadText = sprintf( _n( '%d unloaded plugin on this frontend page',
					'%d unload plugin rules took effect on this frontend page', count( $wpacuFilteredPlugins ), 'wp-asset-clean-up-pro' ),
					count( $wpacuFilteredPlugins ) );
			}

			$wp_admin_bar->add_menu( array(
				'parent' => 'assetcleanup-parent',
				'id'     => 'assetcleanup-plugin-unload-rules-notice',
				'title'  => '<span style="margin: -10px 0 0;" class="wpacu-alert-sign-top-admin-bar dashicons dashicons-filter"></span> &nbsp; '.$titleUnloadText,
				'href'   => '#'
			) );

			$wpacuFilteredPluginsToPrint = array();

			foreach ($wpacuFilteredPlugins as $pluginPath) {
				if ( isset( $allPlugins[ $pluginPath ]['Name'] ) && $allPlugins[ $pluginPath ]['Name'] ) {
					$pluginTitle = $allPlugins[ $pluginPath ]['Name'];
				} else {
					$pluginTitle = $pluginPath;
				}

				$wpacuFilteredPluginsToPrint[] = array('title' => $pluginTitle, 'path' => $pluginPath);
			}

			uasort($wpacuFilteredPluginsToPrint, function($a, $b) {
				return strcmp($a['title'], $b['title']);
			});

			foreach ($wpacuFilteredPluginsToPrint as $pluginData) {
				$pluginTitle = $pluginData['title'];
				$pluginPath = $pluginData['path'];

				list($pluginDir) = explode('/', $pluginPath);

				if (isset($pluginsIcons[$pluginDir])) {
			        $pluginIcon = '<img style="width: 20px; height: 20px; vertical-align: middle; display: inline-block;" width="20" height="20" alt="" src="'.$pluginsIcons[$pluginDir].'" />';
				} else {
					$pluginIcon = '<span class="dashicons dashicons-admin-plugins"></span>';
				}

				if (is_admin()) {
					$wpacuHref = esc_url(admin_url('admin.php?page=wpassetcleanup_plugins_manager&wpacu_sub_page=manage_plugins_dash#wpacu-dash-manage-'.$pluginPath));
				} else {
					$wpacuHref = esc_url(admin_url('admin.php?page=wpassetcleanup_plugins_manager&wpacu_sub_page=manage_plugins_front#wpacu-front-manage-'.$pluginPath));
				}

				$wp_admin_bar->add_menu(array(
					'parent' => 'assetcleanup-plugin-unload-rules-notice',
					'id'     => 'assetcleanup-plugin-unload-rules-list-'.$pluginPath,
					'title'  => $pluginIcon . ' &nbsp;' . $pluginTitle,
					'href'   => $wpacuHref
				));
			}
			}
		// [/wpacu_pro]
		// [END LISTING UNLOADED ASSETS]

		}
}
