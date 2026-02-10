<?php
if (! isset($data)) {
	exit;
}
?>
<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
     class="wpacu_plugin_load_exception_options_wrap <?php if ($data['no_unload_rule_set']) { ?>wpacu_hide<?php } ?>">
	<div class="wpacu_plugin_rules_wrap">
		<fieldset>
			<legend>Make an exception from any unload rule &amp; <strong>always load it</strong> in the front-end:</legend>
			<ul class="wpacu_plugin_rules wpacu_exception_options_area">
				<li>
					<label for="wpacu_home_page_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_load_homepage']) { echo 'class="wpacu_plugin_load_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_load_home_page wpacu_plugin_load_rule_input"
						       id="wpacu_home_page_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_load_homepage']) { echo 'checked="checked"'; } ?>
							   value="load_home_page" />
						<span>On the homepage</span></label>
				</li>
				<li>
					<label for="wpacu_via_post_type_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_load_via_post_type']) { echo 'class="wpacu_plugin_load_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_load_via_post_type wpacu_plugin_load_rule_input"
						       id="wpacu_via_post_type_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_load_via_post_type']) { echo 'checked="checked"'; } ?>
							   value="load_via_post_type" />
						<span>On pages of the following post types:</span></label>

					<!-- -->

					<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
					     class="wpacu_plugin_load_via_post_type_select_wrap <?php if (! $data['is_load_via_post_type']) { ?>wpacu_hide<?php } ?>">
						<?php
						\WpAssetCleanUp\PluginsManager::buildPostTypesListDd(
							'load_via_post_type',
							$data['is_load_via_post_type'],
							$data['post_types_list'],
							( ( isset( $data['rules'][ $data['plugin_path'] ]['load_via_post_type']['values'] ) && is_array( $data['rules'][ $data['plugin_path'] ]['load_via_post_type']['values'] ) )
								? $data['rules'][ $data['plugin_path'] ]['load_via_post_type']['values']
								: array() ),
							$data['plugin_path']
						);
						?>
					</div>
				</li>

				<!-- [Load exception based on the page type] -->
				<?php
				$data['tax_group_list'] = \WpAssetCleanUp\PluginsManager::generatePublicTaxonomyListForDd(esc_attr($data['plugin_path']));

				$data['load_via_tax_chosen'] = ( ( isset( $data['rules'][ $data['plugin_path'] ]['load_via_tax']['values'] ) && is_array( $data['rules'][ $data['plugin_path'] ]['load_via_tax']['values'] ) )
					? $data['rules'][ $data['plugin_path'] ]['load_via_tax']['values']
					: array() );
				?>
				<li>
					<label for="wpacu_via_page_type_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						<?php if ($data['is_load_via_tax']) { echo 'class="wpacu_plugin_load_rule_input_checked"'; } ?>>
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_load_via_tax wpacu_plugin_load_rule_input"
						       id="wpacu_via_page_type_load_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       type="checkbox"
						       name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][status][]"
							<?php if ($data['is_load_via_tax']) { echo 'checked="checked"'; } ?>
							   value="load_via_tax" />
						<span>On the following taxonomy pages:</span></label>
					<!-- -->

					<div data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
					     class="wpacu_plugin_load_via_tax_select_wrap <?php if (! $data['is_load_via_tax']) { ?>wpacu_hide<?php } ?>">
						<?php
						\WpAssetCleanUp\PluginsManager::buildTaxListDd(
							'load_via_tax',
							$data['is_load_via_tax'],
							$data['tax_group_list'],
							$data['load_via_tax_chosen'],
							$data['plugin_path']
						);
						?>
					</div>
				</li>
				<!-- [/Load exception based on the page type] -->
				<li>
					<label for="wpacu_load_it_regex_option_plugin_<?php echo esc_attr($data['plugin_path']); ?>" style="margin-right: 0;">
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       id="wpacu_load_it_regex_option_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_load_exception_regex wpacu_plugin_load_rule_input"
						       type="checkbox"
							<?php if ($data['is_load_via_regex_enabled']) { echo 'checked="checked"'; } ?>
							   name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][load_via_regex][enable]"
							   value="1" />&nbsp;<span>If the URL (its URI) is matched by a RegEx(es):</span>
					</label>&nbsp;<a style="color: #74777b;" class="help_link" target="_blank" href="https://assetcleanup.com/docs/?p=372#wpacu-unload-plugins-via-regex"><span class="dashicons dashicons-editor-help"></span></a>&nbsp;
					<div class="wpacu_load_regex_input_wrap <?php if (! $data['is_load_via_regex_enabled']) { echo 'wpacu_hide'; } ?>"
					     data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>">
                                            <textarea class="wpacu_regex_rule_textarea"
                                                      data-wpacu-adapt-height="1"
                                                      name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][load_via_regex][value]"><?php if (isset($data['rules'][$data['plugin_path']]['load_via_regex']['value']) && $data['rules'][$data['plugin_path']]['load_via_regex']['value']) {
		                                            echo esc_attr($data['rules'][$data['plugin_path']]['load_via_regex']['value']); } ?></textarea>
						<p><small><span style="font-weight: 500;">Note:</span> Multiple RegEx rules can be added as long as they are one per line.</small></p>
					</div>
				</li>
				<li>
					<label for="wpacu_load_it_logged_in_plugin_<?php echo esc_attr($data['plugin_path']); ?>" style="margin-right: 0;">
						<input data-wpacu-plugin-path="<?php echo esc_attr($data['plugin_path']); ?>"
						       id="wpacu_load_it_logged_in_plugin_<?php echo esc_attr($data['plugin_path']); ?>"
						       class="wpacu_plugin_load_exception_logged_in wpacu_plugin_load_rule_input"
						       type="checkbox"
							<?php if ($data['is_load_if_logged_in_enabled']) { echo 'checked="checked"'; } ?>
							   name="wpacu_plugins[<?php echo esc_attr($data['plugin_path']); ?>][load_logged_in][enable]"
							   value="1" />&nbsp;<span>If the user is logged in</span>
					</label>
				</li>
			</ul>
			<div class="wpacu-clearfix"></div>
		</fieldset>
	</div>
</div>