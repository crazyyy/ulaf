<?php
if (! isset($activePlugins, $activePluginsToUnload, $tagName)) {
	exit;
}

$pluginsRulesDbListJson = get_option('wpassetcleanup_global_data');

if ($pluginsRulesDbListJson) {
	$pluginsRulesDbList = @json_decode( $pluginsRulesDbListJson, true );

	// Are there any valid load exceptions / unload RegExes? Fill $activePluginsToUnload
	if ( isset( $pluginsRulesDbList[ 'plugins' ] ) && ! empty( $pluginsRulesDbList[ 'plugins' ] ) ) {
		$pluginsRules = $pluginsRulesDbList[ 'plugins' ];

		// We want to make sure the RegEx rules will be working fine if certain characters (e.g. Thai ones) are used
		$requestUriAsItIs = rawurldecode($_SERVER['REQUEST_URI']);

		// Unload site-wide
		foreach ($pluginsRules as $pluginPath => $pluginRule) {
			if (! in_array($pluginPath, $activePlugins)) {
				// Only relevant if the plugin is active
				// Otherwise it's unloaded (inactive) anyway
				continue;
			}

			// 'status' refers to the Unload Status (any option that was chosen)
			if (isset($pluginRule['status']) && ! empty($pluginRule['status'])) {
				if ( ! is_array($pluginRule['status']) ) {
					$pluginRule['status'] = array($pluginRule['status']); // from v1.1.8.3
				}

				// Are there any load exceptions?
				if ( in_array( 'load_home_page', $pluginRule['status'] ) && wpacuIsHomePageUrl($requestUriAsItIs) ) {
					continue;
				}

				$isLoadExceptionViaPostTypeSet = in_array('load_via_post_type', $pluginRule['status'])
					&& isset($pluginRule['load_via_post_type']['values'])
                    && is_array($pluginRule['load_via_post_type']['values'])
				    && (! empty($pluginRule['load_via_post_type']['values']));

				if ($isLoadExceptionViaPostTypeSet && $requestUriAsItIs) {
					$uriToPost = wpacuUrlToPageType($requestUriAsItIs);

					if (isset($uriToPost['post_type'])
					    && $uriToPost['post_type']
					    && in_array($uriToPost['post_type'], $pluginRule['load_via_post_type']['values'])) {
						continue; // Skip to the next plugin as this one has a load exception matching the condition
					}
				}

				$isLoadExceptionViaTaxSet = in_array('load_via_tax', $pluginRule['status'])
                          && isset($pluginRule['load_via_tax']['values'])
                          && is_array($pluginRule['load_via_tax']['values'])
                          && (! empty($pluginRule['load_via_tax']['values']));

				if ($isLoadExceptionViaTaxSet && $requestUriAsItIs) {
					$uriToPageType = wpacuUrlToPageType($requestUriAsItIs);

					$isTaxMatch = false;

					if ( isset( $uriToPageType['page_type'], $pluginRule['load_via_tax']['values'] ) &&
					     $uriToPageType['page_type'] && is_array($pluginRule['load_via_tax']['values']) && ! empty($pluginRule['load_via_tax']['values']) &&
					     in_array( $uriToPageType['page_type'], array('category', 'post_tag', 'product_cat', 'product_tag') ) &&
					     in_array( $uriToPageType['page_type'].'_all', $pluginRule['load_via_tax']['values'] ) ) {
						$isTaxMatch = true;
					}

					if ($isTaxMatch) {
						continue; // Skip to the next plugin as this one has a load exception matching the condition
					}
				}

				$isLoadExceptionRegExMatch = isset($pluginRule['load_via_regex']['enable'], $pluginRule['load_via_regex']['value'])
				                        && $pluginRule['load_via_regex']['enable'] && wpacuPregMatchInput($pluginRule['load_via_regex']['value'], $requestUriAsItIs);

				if ( $isLoadExceptionRegExMatch ) {
					continue; // Skip to the next plugin as this one has a load exception matching the condition
				}

				// Should the plugin be always loaded as a if the user is logged-in? (priority over the same rule for unloading)
				$isLoadExceptionIfLoggedInEnable = isset($pluginRule['load_logged_in']['enable']) && $pluginRule['load_logged_in']['enable'];

				// Unload the plugin if the user is logged-in?
				$isUnloadIfLoggedInEnable = in_array('unload_logged_in', $pluginRule['status']);

				if (($isLoadExceptionIfLoggedInEnable || $isUnloadIfLoggedInEnable) && ! defined('WPACU_PLUGGABLE_LOADED')) {
					require_once WPACU_MU_FILTER_PLUGIN_DIR . '/pluggable-custom.php';
					define('WPACU_PLUGGABLE_LOADED', true);
				}

				if ($isLoadExceptionIfLoggedInEnable && function_exists('wpacu_is_user_logged_in') && wpacu_is_user_logged_in()) {
					continue; // Do not unload it (priority)
				}

				// Unload the plugin if the user is logged-in?
				if ($isUnloadIfLoggedInEnable && (function_exists('wpacu_is_user_logged_in') && wpacu_is_user_logged_in())) {
					$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
				}

				if ( in_array('unload_site_wide', $pluginRule['status']) ) {
					$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
				} else {
					if ( in_array( 'unload_home_page', $pluginRule['status'] ) && wpacuIsHomePageUrl($requestUriAsItIs) ) {
						$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
					}

					if ( in_array( 'unload_via_post_type', $pluginRule['status'] ) ) {
						$isUnloadViaPostTypeSet = isset( $pluginRule['unload_via_post_type']['values'] )
						                          && is_array( $pluginRule['unload_via_post_type']['values'] )
						                          && ( ! empty( $pluginRule['unload_via_post_type']['values'] ) );

						if ( $isUnloadViaPostTypeSet && $requestUriAsItIs ) {
							$uriToPost = wpacuUrlToPageType( $requestUriAsItIs );

							if ( isset( $uriToPost['post_type'] )
							     && $uriToPost['post_type']
							     && in_array( $uriToPost['post_type'],
									$pluginRule['unload_via_post_type']['values'] ) ) {
								$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
							}
						}
					}

					if ( in_array( 'unload_via_tax', $pluginRule['status'] ) ) {
						$isUnloadViaTaxSet = isset( $pluginRule['unload_via_tax']['values'] )
						                     && is_array( $pluginRule['unload_via_tax']['values'] )
						                     && ( ! empty( $pluginRule['unload_via_tax']['values'] ) );

						if ( $isUnloadViaTaxSet && $requestUriAsItIs ) {
							$uriToPageType = wpacuUrlToPageType( $requestUriAsItIs );
							$isTaxMatch = false;

							if ( isset( $uriToPageType['page_type'] ) && $uriToPageType['page_type'] &&
								in_array($uriToPageType['page_type'], array('category', 'post_tag', 'product_cat', 'product_tag')) &&
							    in_array( $uriToPageType['page_type'].'_all', $pluginRule['unload_via_tax']['values'] ) ) {
								$isTaxMatch = true;
							}

							if ( $isTaxMatch ) {
								$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
							}
						}
					}

					if ( in_array( 'unload_via_regex', $pluginRule['status'] ) ) {
						$isUnloadRegExMatch = isset( $pluginRule['unload_via_regex']['value'] ) && wpacuPregMatchInput( $pluginRule['unload_via_regex']['value'],
								$requestUriAsItIs );
						if ( $isUnloadRegExMatch ) {
							$activePluginsToUnload[] = $pluginPath; // Add it to the unload list
						}
					}
				}
			}
		}
	}
}

// [START - Make exception and load the plugin for debugging purposes]
if (isset($_GET['wpacu_load_plugins']) && $_GET['wpacu_load_plugins']) {
	require WPACU_MU_FILTER_PLUGIN_DIR . '/_common/_plugin-load-exceptions-via-query-string.php';
}
// [END - Make exception and load the plugin for debugging purposes
