<?php
// This file is included from /templates/_admin-page-plugins-manager/_front.php
if (! isset($data)) {
	exit;
}
?>
<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
     class="wpacu_plugin_unload_rules_options_wrap">
	<div class="wpacu_plugin_rules_wrap">
		<fieldset>
			<legend><strong>Unload this plugin</strong> in the front-end:</legend>
			<ul class="wpacu_plugin_rules">
				<li>
					<label for="wpacu_global_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_unload_site_wide']) { echo 'class="wpacu_plugin_unload_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_site_wide wpacu_plugin_unload_rule_input"
						       id="wpacu_global_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_unload_site_wide']) { echo 'checked="checked"'; } ?>
							   value="unload_site_wide" />
						<span>On all pages</span></label>
				</li>
				<li>
					<label for="wpacu_home_page_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_unload_homepage']) { echo 'class="wpacu_plugin_unload_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_home_page wpacu_plugin_unload_rule_input"
						       id="wpacu_home_page_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_unload_homepage']) { echo 'checked="checked"'; } ?>
							   value="unload_home_page" />
						<span>On the homepage</span></label>
				</li>
				<!-- [Unload based on the post type] -->
				<li>
					<label for="wpacu_via_post_type_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_unload_via_post_type']) { echo 'class="wpacu_plugin_unload_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_via_post_type wpacu_plugin_unload_rule_input"
						       id="wpacu_via_post_type_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_unload_via_post_type']) { echo 'checked="checked"'; } ?>
							   value="unload_via_post_type" />
						<span>On pages of the following post types:</span></label>
					<!-- -->

					<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
					     class="wpacu_plugin_unload_via_post_type_select_wrap <?php if (! $data['is_unload_via_post_type']) { ?>wpacu_hide<?php } ?>">
						<?php
						\WpAssetCleanUp\PluginsManager::buildPostTypesListDd(
							'unload_via_post_type',
							$data['is_unload_via_post_type'],
							$data['post_types_list'],
							( ( isset( $data['rules'][ $data['plugin_path'] ]['unload_via_post_type']['values'] ) && is_array( $data['rules'][ $data['plugin_path'] ]['unload_via_post_type']['values'] ) )
								? $data['rules'][ $data['plugin_path'] ]['unload_via_post_type']['values']
								: array() ),
							$data['plugin_path']
						);
						?>
					</div>
				</li>
				<!-- [/Unload based on the post type] -->

				<!-- [Unload based on the page type] -->
				<li>
					<label for="wpacu_via_page_type_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_unload_via_tax']) { echo 'class="wpacu_plugin_unload_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_via_tax wpacu_plugin_unload_rule_input"
						       id="wpacu_via_page_type_unload_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_unload_via_tax']) { echo 'checked="checked"'; } ?>
							   value="unload_via_tax" />
						<span>On the following taxonomy pages:</span></label>
					<!-- -->

					<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
					     class="wpacu_plugin_unload_via_tax_select_wrap <?php if (! $data['is_unload_via_tax']) { ?>wpacu_hide<?php } ?>">
						<?php
						\WpAssetCleanUp\PluginsManager::buildTaxListDd(
							'unload_via_tax',
							$data['is_unload_via_tax'],
							$data['tax_group_list'],
							$data['unload_via_tax_chosen'],
							$data['plugin_path']
						);
						?>
					</div>
				</li>
				<!-- [/Unload based on the page type] -->
				<li>
					<label for="wpacu_unload_it_regex_option_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_unload_via_regex']) { echo 'class="wpacu_plugin_unload_rule_input_checked"'; } ?>
						   style="margin-right: 0;">
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       id="wpacu_unload_it_regex_option_<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_regex_option wpacu_plugin_unload_rule_input"
						       type="checkbox"
							<?php if ($data['is_unload_via_regex']) { echo 'checked="checked"'; } ?>
							   name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							   value="unload_via_regex">&nbsp;<span>If request URI matches these RegEx(es):</span></label>
					<a class="help_link unload_it_regex"
					   target="_blank"
					   href="https://assetcleanup.com/docs/?p=372#wpacu-unload-plugins-via-regex"><span style="color: #74777b;" class="dashicons dashicons-editor-help"></span></a>
					<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
					     class="wpacu_plugin_unload_regex_input_wrap <?php if (! $data['is_unload_via_regex']) { ?>wpacu_hide<?php } ?>">
                                            <textarea class="wpacu_regex_rule_textarea"
                                                      data-wpacu-adapt-height="1"
                                                      name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][unload_via_regex][value]"><?php if (isset($data['rules'][$data['plugin_path']]['unload_via_regex']['value']) && $data['rules'][$data['plugin_path']]['unload_via_regex']['value']) {
		                                            echo esc_attr($data['rules'][$data['plugin_path']]['unload_via_regex']['value']); } ?></textarea>
						<p><small><span style="font-weight: 500;">Note:</span> Multiple RegEx rules can be added as long as they are one per line.</small></p>
					</div>
				</li>
				<li>
					<label for="wpacu_unload_it_logged_in_plugin_<?php echo esc_attr($data['plugin_path']); ?>" style="margin-right: 0;">
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       id="wpacu_unload_it_logged_in_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_unload_logged_in"
						       type="checkbox"
							<?php if ($data['is_unload_if_logged_in_enabled']) { echo 'checked="checked"'; } ?>
							   name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							   value="unload_logged_in" />&nbsp;<span>If the user is logged in</span>
					</label>
				</li>
			</ul>
			<div class="wpacu-clearfix"></div>
		</fieldset>
	</div>
</div>