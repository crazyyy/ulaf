<?php
if (! isset($data)) {
	exit;
}
?>
<div class="wpacu-wrap" id="wpacu-plugins-load-manager-wrap">
	<form method="post" action="" class="wpacu-settings-form">
		<?php
		$pluginsRows = array();

		foreach ($data['active_plugins'] as $pluginData) {
			$data['plugin_path'] = $pluginPath = $pluginData['path'];
			list($pluginDir) = explode('/', $pluginPath);

			// [wpacu_pro]
			$pluginStatus = isset($data['rules'][$pluginPath]['status']) ? $data['rules'][$pluginPath]['status'] : array(); // array() from v1.1.8.3

			if (! is_array($pluginStatus)) {
				$pluginStatus = array($pluginStatus); // from v1.1.8.3
			}

			$data['is_unload_site_wide'] = in_array('unload_site_wide', $pluginStatus);
			$data['is_unload_homepage'] = in_array('unload_home_page', $pluginStatus);
			$data['is_unload_via_post_type'] = in_array('unload_via_post_type', $pluginStatus);

            // [Unload via taxonomy]
			$data['is_unload_via_tax'] = in_array('unload_via_tax', $pluginStatus);

			$data['tax_group_list'] = \WpAssetCleanUp\PluginsManager::generatePublicTaxonomyListForDd(esc_attr($pluginPath));

			$data['unload_via_tax_chosen'] = ( ( isset( $data['rules'][ $pluginPath ]['unload_via_tax']['values'] ) && is_array( $data['rules'][ $pluginPath ]['unload_via_tax']['values'] ) )
				? $data['rules'][ $pluginPath ]['unload_via_tax']['values']
				: array() );

			$data['is_unload_via_regex'] = in_array('unload_via_regex', $pluginStatus);
			// [/Unload via taxonomy]

			$data['is_unload_if_logged_in_enabled'] = in_array('unload_logged_in', $pluginStatus);

            // [Load exceptions]
			$data['no_unload_rule_set'] = ! ($data['is_unload_site_wide'] || $data['is_unload_homepage'] || $data['is_unload_via_post_type'] || $data['is_unload_via_tax'] || $data['is_unload_via_regex'] || $data['is_unload_if_logged_in_enabled']);

			$data['is_load_homepage'] = in_array('load_home_page', $pluginStatus);
			$data['is_load_via_post_type'] = in_array('load_via_post_type', $pluginStatus);
			$data['is_load_via_tax'] = in_array('load_via_tax', $pluginStatus);
			$data['is_load_via_regex_enabled'] = isset($data['rules'][$pluginPath]['load_via_regex']['enable']) && $data['rules'][$pluginPath]['load_via_regex']['enable'];
			$data['is_load_if_logged_in_enabled'] = isset($data['rules'][$pluginPath]['load_logged_in']['enable']) && $data['rules'][$pluginPath]['load_logged_in']['enable'];
            // [/Load exceptions]
            // [/wpacu_pro]

			ob_start();
			?>
			<tr>
				<td class="wpacu_plugin_icon" width="46">
					<?php if (isset($data['plugins_icons'][$pluginDir])) { ?>
						<img width="44" height="44" alt="" src="<?php echo esc_url($data['plugins_icons'][$pluginDir]); ?>" />
					<?php } else { ?>
						<div><span class="dashicons dashicons-admin-plugins"></span></div>
					<?php } ?>
				</td>
				<td class="wpacu_plugin_details" id="wpacu-front-manage-<?php echo esc_attr($pluginData['path']); ?>">
					<span class="wpacu_plugin_title"><?php echo esc_html($pluginData['title']); ?></span>
                    <span class="wpacu_plugin_path">&nbsp;<?php echo esc_html($pluginData['path']); ?></span>

					<?php
                    if ($pluginData['network_activated']) {
						echo '&nbsp;<span title="Network Activated" class="dashicons dashicons-admin-multisite wpacu-tooltip"></span>';
					}
                    ?>

					<div class="wpacu-clearfix"></div>

                    <!-- [Start] Unload Rules -->
					<?php
                    include '_front-areas/_unloads.php';
                    ?>
                    <!-- [End] Unload Rules -->

					<!-- [Start] Make exceptions: Load Rules -->
					<?php
					include '_front-areas/_load-exceptions.php';
					?>
					<!-- [End] Make exceptions: Load Rules -->
                    <div class="wpacu-clearfix"></div>
				</td>
			</tr>
			<?php
			$trOutput = ob_get_clean();

			if (empty($pluginStatus)) {
				$pluginsRows['always_loaded'][] = $trOutput;
			} else {
				$pluginsRows['has_unload_rules'][] = $trOutput;
			}
		}

		if (isset($pluginsRows['has_unload_rules']) && ! empty($pluginsRows['has_unload_rules'])) {
			$totalWithUnloadRulesPlugins = count($pluginsRows['has_unload_rules']);
			?>
			<h3><span style="color: #c00;" class="dashicons dashicons-admin-plugins"></span> <span style="color: #c00;"><?php echo (int)$totalWithUnloadRulesPlugins; ?></span> plugin<?php echo ($totalWithUnloadRulesPlugins > 1) ? 's' : ''; ?> with active unload rules</h3>
			<table class="wp-list-table wpacu-list-table widefat plugins striped">
				<?php
				foreach ( $pluginsRows['has_unload_rules'] as $pluginRowOutput ) {
					echo \WpAssetCleanUp\Misc::stripIrrelevantHtmlTags($pluginRowOutput) . "\n";
				}
				?>
			</table>
			<?php
		}

		if (isset($pluginsRows['always_loaded']) && ! empty($pluginsRows['always_loaded'])) {
			if (isset($pluginsRows['has_unload_rules']) && count($pluginsRows['has_unload_rules']) > 0) {
				?>
				<div style="margin-top: 35px;"></div>
				<?php
			}

			$totalAlwaysLoadedPlugins = count($pluginsRows['always_loaded']);
			?>

			<h3><span style="color: green;" class="dashicons dashicons-admin-plugins"></span> <span style="color: green;"><?php echo (int)$totalAlwaysLoadedPlugins; ?></span> plugin<?php echo ($totalAlwaysLoadedPlugins > 1) ? 's' : ''; ?> with no active unload rules (loaded by default)</h3>
			<table class="wp-list-table wpacu-list-table widefat plugins striped">
				<?php
				foreach ( $pluginsRows['always_loaded'] as $pluginRowOutput ) {
					echo \WpAssetCleanUp\Misc::stripIrrelevantHtmlTags($pluginRowOutput) . "\n";
				}
				?>
			</table>
			<?php
		}
		?>
		<div id="wpacu-update-button-area" style="margin-left: 0;">
			<?php
			wp_nonce_field('wpacu_plugin_manager_update', 'wpacu_plugin_manager_nonce');
			submit_button('Apply changes within frontend view');
			?>
			<div id="wpacu-updating-settings" style="margin-left: 294px; top: 31px;">
				<img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" align="top" width="20" height="20" alt="" />
			</div>
			<input type="hidden" name="wpacu_plugins_manager_submit" value="1" />
		</div>
	</form>
</div>