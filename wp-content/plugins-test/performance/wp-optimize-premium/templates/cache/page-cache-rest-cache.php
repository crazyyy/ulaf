<div class="wpo-fieldgroup__subgroup">
	<label for="enable_rest_caching">
		<input name="enable_rest_caching" id="enable_rest_caching" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_rest_caching'], 1); ?>>
		<?php esc_html_e('Enable caching for WordPress REST API requests', 'wp-optimize'); ?> 
	</label>
	
	<span tabindex="0" data-tooltip="<?php esc_attr_e('Enable this option to cache WordPress REST API requests (works only for unauthenticated GET requests).', 'wp-optimize');?>"><span class="dashicons dashicons-editor-help"></span> </span>
</div>