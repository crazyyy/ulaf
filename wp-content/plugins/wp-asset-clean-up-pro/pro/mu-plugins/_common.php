<?php
if (! defined('WP_PLUGIN_DIR')) {
	exit; // no direct access to this file
}

if (! defined('WPACU_PLUGIN_ID')) {
	define( 'WPACU_PLUGIN_ID', 'wpassetcleanup' ); // unique prefix (same plugin ID name for 'lite' and 'pro')
}

if (! defined('WPACU_MU_FILTER_PLUGIN_DIR')) {
	define( 'WPACU_MU_FILTER_PLUGIN_DIR', __DIR__ );
}

/*
 * [FRONT-END VIEW]
 */
if (! is_admin()) {
	if (! defined('WPACU_EARLY_TRIGGERS_CALLED')) {
		require_once dirname(dirname(WPACU_MU_FILTER_PLUGIN_DIR)) . '/early-triggers.php';
	}

	if (assetCleanUpNoLoad()) {
		// Is the assets list fetched via AJAX?
		if ( isset( $_REQUEST['wpassetcleanup_load'] ) && $_REQUEST['wpassetcleanup_load'] ) {
			$wpacuNoLoadMatchesStatus = assetCleanUpHasNoLoadMatches();

			if ( 'is_set_in_settings' === $wpacuNoLoadMatchesStatus ) {
				$msg = sprintf( __( 'This page\'s URL is matched by one of the RegEx rules you have in <em>"Settings"</em> -&gt; <em>"Plugin Usage Preferences"</em> -&gt; <em>"Do not load the plugin on certain pages"</em>, thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please remove the matching RegEx rule and reload this page.',
					'wp-asset-clean-up' ), WPACU_PLUGIN_TITLE );
				exit( $msg );
			}

			if ( 'is_set_in_page' === $wpacuNoLoadMatchesStatus ) {
				$msg = sprintf( __( 'This page\'s URI is matched by the rule you have in the "Page Options", thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please uncheck the following option shown below: <em>"Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin"</em>.',
					'wp-asset-clean-up' ), WPACU_PLUGIN_TITLE );
				exit( $msg );
			}
		}

		add_filter( 'wpacu_plugin_no_load', '__return_true' );

		// Trigger the plugin filtering if the user specifically asked for it
		// This is true whenever the "WPACU_ALLOW_ONLY_UNLOAD_RULES" constant is set to "true"
		if ( ! (defined('WPACU_ALLOW_ONLY_UNLOAD_RULES') && WPACU_ALLOW_ONLY_UNLOAD_RULES) ) {
			define( 'WPACU_DO_NOT_TRIGGER_PLUGIN_FILTER', true );
		}
	}
}
/*
 * [/FRONT-END VIEW]
 */

// "pluggable-custom.php" a file that emulates some native WordPress functions that are not available since some calls
// to verify if te user is logged-in (e.g. for front-end view rules) are not available in MU plugins
if ( ! function_exists('wpacuTriggerPluginsFilter') ) {
	/**
	 * @return bool
	 */
	function wpacuTriggerPluginsFilter()
	{
		if (defined('WPACU_DO_NOT_TRIGGER_PLUGIN_FILTER') && WPACU_DO_NOT_TRIGGER_PLUGIN_FILTER) {
			return false; // e.g. in case "assetCleanUpNoLoad()" returned true in an earlier call
		}

		// When these debugging query strings are used, do not filter any active plugins and load them all
		if ( isset($_GET['wpacu_no_plugin_unload']) || isset($_GET['wpacu_no_load']) ) {
			return false;
		}

		if ( is_admin() ) {
			if ( isset($_GET['wpacu_no_dash_plugin_unload']) ) {
				return false;
			}

			$wpacuAllowPluginFilterWithinDashboard = defined( 'WPACU_ALLOW_DASH_PLUGIN_FILTER' ) && WPACU_ALLOW_DASH_PLUGIN_FILTER
				&& ( strpos( $_SERVER['REQUEST_URI'], '/admin-ajax.php' ) === false );

			if ( ! $wpacuAllowPluginFilterWithinDashboard ) {
				return false;
			}

			// It shouldn't trigger in pages such as "Plugins" or "Updates"
			if (strpos($_SERVER['REQUEST_URI'], '/plugins.php') !== false ||
			    strpos($_SERVER['REQUEST_URI'], '/plugin-install.php') !== false ||
			    strpos($_SERVER['REQUEST_URI'], '/plugin-editor.php') !== false ||
			    strpos($_SERVER['REQUEST_URI'], '/update-core.php') !== false) {
				return false;
			}

			// Do not trigger any plugin unload rules on Asset CleanUp Pro pages to avoid confusion in pages like "Overview"
			// e.g. The list of all the custom post types generated in "CSS & JS Manager" -> "Manage Critical CSS" -> "Custom Post Types" has to be printed
			// with all the custom post types that might be generated from all the active plugins (so no plugins unloading on such a page)
			if (isset($_GET['page']) && $_GET['page'] && strpos($_GET['page'], 'wpassetcleanup_') !== false) {
				return false;
			}
		} else {
			// Do not unload any plugins if an AJAX call is made to any front-end view as some plugins like WooCommerce and Gravity Forms
			// are using index.php?[query string here] type of calls, and we don't want to deactivate the plugins in this instance
			// e.g. when the plugin should be unloaded on the homepage view, but not the AJAX call made from a "Checkout" or "Contact" page, etc.
			$wpacuIsAjaxRequest = ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' );

			if ( $wpacuIsAjaxRequest && ! wpacuTriggerPluginsFilterOnAjaxCallsAsException() ) {
				return false;
			}
		}

		// Is "Test Mode" enabled and the user is a guest (not admin)? Do not continue with any filtering
		// Also, if the user is logged-in within the Dashboard and there are rules in "PLUGINS MANAGER" -- "IN THE DASHBOARD /wp-admin/"
		// Those rules will not apply if he's not an administrator
		// No rules will be triggered including any in "Plugins Manager" as the MU plugin is part of Asset CleanUp Pro
		$wpacuCheckTestMode = ! is_admin(); // front-end view by default

		if (defined('WPACU_ALLOW_DASH_PLUGIN_FILTER') && WPACU_ALLOW_DASH_PLUGIN_FILTER) {
			$wpacuCheckTestMode = true; // check it on both front-end and Dashboard views
		}

		if ($wpacuCheckTestMode) {
			$wpacuSettingsJson   = get_option( 'wpassetcleanup_settings' );
			$wpacuSettingsDbList = @json_decode( $wpacuSettingsJson, true );
			$wpacuIsTestMode     = isset( $wpacuSettingsDbList['test_mode'] ) && $wpacuSettingsDbList['test_mode'];

			if ( $wpacuIsTestMode ) {
				if ( ! defined( 'WPACU_PLUGGABLE_LOADED' ) ) {
					require_once WPACU_MU_FILTER_PLUGIN_DIR . '/pluggable-custom.php';
					define( 'WPACU_PLUGGABLE_LOADED', true );
				}

				if ( ! wpacu_current_user_can( 'administrator' ) ) {
					return false;
				}
			}
		}

		// So far, there are no reasons to stop avoiding the filtering
		// Perform the latest checks below

		// Check for any query strings meant to be used for debugging purposes to load or unload certain plugins
		if ( isset( $_GET['wpacu_filter_plugins'] ) || isset( $_GET['wpacu_only_load_plugins'] ) ) {
			return true;
		}

		// Plugins selectively unloaded from a form within /?wpacu_debug (bottom of the page)
		$wpacuIsUnloadPluginsViaDebugForm = (isset($_POST['wpacu_filter_plugins']) && is_array($_POST['wpacu_filter_plugins']) && ! empty($_POST['wpacu_filter_plugins'])) || (isset($_POST['wpacu_debug']) && $_POST['wpacu_debug'] === 'on');

		if ($wpacuIsUnloadPluginsViaDebugForm) {
			return true;
		}

		// Check for any rules in the database!
		$pluginsRulesDbListJson = get_option( 'wpassetcleanup_global_data' );

		if ( $pluginsRulesDbListJson ) {
			$pluginsRulesDbList = @json_decode( $pluginsRulesDbListJson, true );

			$keyToCheck = is_admin() ? 'plugins_dash' : 'plugins';

			$anyRulesSet = ( isset( $pluginsRulesDbList[ $keyToCheck ] ) && ! empty( $pluginsRulesDbList[ $keyToCheck ] ) );

			if ( $anyRulesSet ) {
				$hasAtLeastOneUnloadStatus = false;

				foreach ($pluginsRulesDbList[ $keyToCheck ] as $pluginRule) {
					if (isset($pluginRule['status']) && ! empty($pluginRule['status'])) {
						$hasAtLeastOneUnloadStatus = true;
						break;
					}
				}

				if ($hasAtLeastOneUnloadStatus) {
					return true;
				}
			}
		}

		// Finally, no rules in the database and no debugging query strings? Do not trigger it!
		return false;
	}
}

if ( ! function_exists('wpacuTriggerPluginsFilterOnAjaxCallsAsException') ) {
	/**
	 * @return bool
	 */
	function wpacuTriggerPluginsFilterOnAjaxCallsAsException()
	{
		// Any exceptions to trigger the plugin filter on AJAX calls?
		if (assetCleanUpIsRestCall() && defined('WPACU_LOAD_ON_REST_CALLS') && WPACU_LOAD_ON_REST_CALLS) {
			return true;
		}

		return false; // default
	}
}

if (! function_exists('wpacuPregMatchInput')) {
	/**
	 * @param $pattern
	 * @param $subject
	 *
	 * @return bool|false|int
	 */
	function wpacuPregMatchInput( $pattern, $subject )
	{
		$pattern = trim( $pattern );

		if ( ! $pattern ) {
			return false;
		}

		// One line (there aren't several lines in the textarea)
		if ( strpos( $pattern, "\n" ) === false ) {
			return @preg_match( $pattern, $subject );
		}

		// Multiple lines
		foreach ( explode( "\n", $pattern ) as $patternRow ) {
			$patternRow = trim( $patternRow );
			if ( @preg_match( $patternRow, $subject ) ) {
				return true;
			}
		}

		return false;
	}
}

add_filter( 'pre_update_option_active_plugins', function ($newPluginList, $oldPluginList) {
	// Both lists are empty, thus there's no point in continuing
	if (empty($newPluginList) && empty($oldPluginList)) {
		return $newPluginList;
	}

	// Not filtered by Asset CleanUp Pro either in the front-end view or within the Dashboard? Then, return the original value!
	if ( is_admin() && ! (defined('WPACU_ALLOW_DASH_PLUGIN_FILTER') && WPACU_ALLOW_DASH_PLUGIN_FILTER) ) {
		return $newPluginList;
	}

	if ( ! (isset($GLOBALS['wpacu_filtered_plugins']) && ! empty($GLOBALS['wpacu_filtered_plugins'])) ) {
		return $newPluginList;
	}

	// Check if the stripped plugins by the potential faulty plugin (that incorrectly updates the "active_plugins" option) are exactly the same as the filtered ones by Asset CleanUp Pro
	$strippedFromOriginal = $oldPluginList;

	foreach ($oldPluginList as $oldPluginKey => $oldPluginValue) {
		if (in_array($oldPluginValue, $GLOBALS['wpacu_filtered_plugins'])) {
			unset($strippedFromOriginal[$oldPluginKey]);
		}
	}

	asort($strippedFromOriginal);

	$newListToUpdate = $newPluginList;
	asort($newListToUpdate);

	$strOne = implode(',', $strippedFromOriginal);
	$strTwo = implode(',', $newListToUpdate);

	// Put back the plugins that were filtered incorrectly by the faulty plugin or theme
	if ($strOne === $strTwo) {
		foreach ( $GLOBALS['wpacu_filtered_plugins'] as $filteredPlugin ) {
			$newPluginList[] = $filteredPlugin;
		}
	}

	return $newPluginList;
}, PHP_INT_MAX, 2 );
