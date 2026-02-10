<?php
namespace WpAssetCleanUp;

/**
 * Class PluginsManager
 * @package WpAssetCleanUp
 */
class PluginsManager
{
    /**
     * @var array
     */
    public $data = array();

    // [wpacu_pro]
	/**
	 * PluginsManager constructor.
	 */
	public function __construct()
    {
        // Note: The rules update takes place in /pro/classes/UpdatePro.php
	    if (Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_plugins_manager') {
		    add_action('wpacu_admin_notices', array($this, 'notices'));
	    }
    }
    // [/wpacu_pro]

	/**
	 *
	 */
	public function page()
    {
    	// Get active plugins and their basic information
	    $this->data['active_plugins'] = self::getActivePlugins();
	    $this->data['plugins_icons']  = Misc::getAllActivePluginsIcons();

	    // [wpacu_pro]
	    $this->data['rules']          = self::getAllRules(); // get all rules from the database (for either the frontend or dash view)

	    $this->data['mu_file_missing']  = false; // default
	    $this->data['mu_file_rel_path'] = '/' . str_replace(Misc::getWpRootDirPath(), '', WPMU_PLUGIN_DIR)
	                                      . '/' . \WpAssetCleanUpPro\PluginPro::$muPluginFileName;

	    if ( ! is_file(WPMU_PLUGIN_DIR . '/' . \WpAssetCleanUpPro\PluginPro::$muPluginFileName) ) {
			$this->data['mu_file_missing']  = true; // alert the user in the "Plugins Manager" area
	    }

	    $postTypes                     = get_post_types( array( 'public' => true ) );
	    $this->data['post_types_list'] = Misc::filterPostTypesList($postTypes);
        // [/wpacu_pro]

	    Main::instance()->parseTemplate('admin-page-plugins-manager', $this->data, true);
    }

    // [wpacu_pro]
	/**
	 * @param false $fetchAllLocations (if set to true, it will return the rules for both the frontend and the backend
	 *
	 * @return array
	 */
	public static function getAllRules($fetchAllLocations = false)
	{
		$pluginsRulesDbListJson = get_option(WPACU_PLUGIN_ID . '_global_data');

		if ($pluginsRulesDbListJson) {
			$pluginsRulesDbList = @json_decode($pluginsRulesDbListJson, true);

			// Issues with decoding the JSON file? Return an empty list
			if (Misc::jsonLastError() !== JSON_ERROR_NONE) {
				return array();
			}

			// 1) For listing them in "Overview"
			if ($fetchAllLocations) {
                $rulesList = array();

				if ( isset( $pluginsRulesDbList['plugins'] ) && ! empty( $pluginsRulesDbList['plugins'] ) ) {
					$rulesList['plugins'] = $pluginsRulesDbList['plugins'];
				}

				if ( isset( $pluginsRulesDbList['plugins_dash'] ) && ! empty( $pluginsRulesDbList['plugins_dash'] ) ) {
					$rulesList['plugins_dash'] = $pluginsRulesDbList['plugins_dash'];
				}

				return $rulesList;
            }

			// 2) For listing them within "Plugins Manager" -> "In Frontend View" or "In the Dashboard" when the admin is managing the rules
			$wpacuSubPage = ( isset($_GET['wpacu_sub_page']) && $_GET['wpacu_sub_page'] ) ? $_GET['wpacu_sub_page'] : 'manage_plugins_front';

			$mainGlobalKey = ($wpacuSubPage === 'manage_plugins_front') ? 'plugins' : 'plugins_dash';

			if ( isset( $pluginsRulesDbList[$mainGlobalKey] ) && ! empty( $pluginsRulesDbList[$mainGlobalKey] ) ) {
				return $pluginsRulesDbList[$mainGlobalKey];
			}
		}

		return array();
	}
    // [/wpacu_pro]

	/**
	 * @return array
	 */
	public static function getActivePlugins()
	{
		$activePluginsFinal = array();

		// Get active plugins and their basic information
        $activePlugins = wp_get_active_and_valid_plugins();
        // Also check any network activated plugins in case we're dealing with a MultiSite setup
		if ( is_multisite() ) {
			$activeNetworkPlugins = wp_get_active_network_plugins();

			if ( ! empty( $activeNetworkPlugins ) ) {
				foreach ( $activeNetworkPlugins as $activeNetworkPlugin ) {
					$activePlugins[] = $activeNetworkPlugin;
				}
			}
		}

		$activePlugins = array_unique($activePlugins);

		foreach ($activePlugins as $pluginPath) {
			// Skip Asset CleanUp as it's obviously needed for the functionality
			if (strpos($pluginPath, 'wp-asset-clean-up') !== false) {
				continue;
			}

			$networkActivated = isset($activeNetworkPlugins) && in_array($pluginPath, $activeNetworkPlugins);

			$pluginRelPath = trim(str_replace(WP_PLUGIN_DIR, '', $pluginPath), '/');

			$pluginData = get_plugin_data($pluginPath);

			$activePluginsFinal[] = array(
                'title'             => $pluginData['Name'],
                'path'              => $pluginRelPath,
                'network_activated' => $networkActivated
			);
		}

		usort($activePluginsFinal, static function($a, $b)
		{
			return strcmp($a['title'], $b['title']);
		});

		return $activePluginsFinal;
	}

	// [wpacu_pro]
	/**
	 * Make sure there is a status for the rule, otherwise it's likely set to "Load it",
	 * thus the rule wouldn't count
	 * @param bool $checkIfPluginIsActive
	 * @param bool $getRulesForAllLocations
     *
	 * @return array
	 */
	public static function getPluginRulesFiltered($checkIfPluginIsActive = true, $getRulesForAllLocations = false)
    {
	    $pluginsWithRules = array();

		$pluginsAllDbRules = self::getAllRules($getRulesForAllLocations);

		// Are there any load exceptions / unload RegExes?
	    if (! empty( $pluginsAllDbRules ) ) {
	        foreach ($pluginsAllDbRules as $locationKey => $pluginsRules) {
		        foreach ( $pluginsRules as $pluginPath => $pluginData ) {
			        // Only the rules for the active plugins are retrieved
			        if ( $checkIfPluginIsActive && ! Misc::isPluginActive( $pluginPath ) ) {
				        continue;
			        }

			        // 'status' refers to the Unload Status (any option that was chosen)
			        $pluginStatus = isset( $pluginData['status'] ) && ! empty( $pluginData['status'] ) ? $pluginData['status'] : array();

			        if ( ! empty( $pluginStatus ) ) {
				        $pluginsWithRules[ $locationKey ][ $pluginPath ] = $pluginData;
			        }
		        }
	        }

		    }

	    return $pluginsWithRules;
    }

	/**
     * @param $ddKeyName
	 * @param $enabled
     * @param $postTypesList
	 * @param $currentPostTypes
	 * @param $pluginPath
	 */
	public static function buildPostTypesListDd($ddKeyName, $enabled, $postTypesList, $currentPostTypes, $pluginPath)
	{
		$ddList = array();

        // "WooCommerce" (or any other plugins that generates custom post types) creates the "product" page
        // It doesn't make sense to unload "WooCommerce" when a "WooCommerce" product page is visited
        // as that page would not exist anyway without WooCommerce (thus, hide it from the drop-down list)
		if ($pluginPath === 'woocommerce/woocommerce.php' && array_key_exists('product', $postTypesList)) {
            unset($postTypesList['product']);
		}

		if ($pluginPath === 'easy-digital-downloads/easy-digital-downloads.php' && array_key_exists('product', $postTypesList)) {
			unset($postTypesList['download']);
		}

		foreach ($postTypesList as $postTypeKey => $postTypeValue) {
			if (in_array($postTypeKey, array('post', 'page', 'attachment'))) {
				$ddList['WordPress (default)'][$postTypeKey] = $postTypeValue;
			} else {
				$ddList['Custom Post Types (Singular pages)'][$postTypeKey] = $postTypeValue;
			}
		}
		?>
        <select multiple="multiple"
                style="width: 100%;"
                data-placeholder="<?php esc_attr_e('Choose the post types', 'wp-asset-clean-up-pro'); ?>..."
                class="<?php if ( $enabled && ! empty($currentPostTypes) ) { ?>wpacu_chosen_select<?php } ?> wpacu_plugin_manage_via_post_type_dd"
                name="wpacu_plugins[<?php echo esc_attr($pluginPath); ?>][<?php echo $ddKeyName; ?>][values][]">
			<?php
			foreach ($ddList as $groupLabel => $groupPostTypesList) {
				echo '<optgroup label="'.esc_attr($groupLabel).'">';

				foreach ($groupPostTypesList as $postTypeKey => $postTypeValue) {
					?>
                    <option <?php if (in_array($postTypeKey, $currentPostTypes)) { echo 'selected="selected"'; } ?> value="<?php echo esc_attr($postTypeKey); ?>"><?php echo esc_html($postTypeValue); ?></option>
					<?php
				}

				echo '</optgroup>';
			}
			?>
        </select>
		<?php
	}

	/**
	 * $pluginPath (the plugin from "Plugins Manager" for which the rules are shown)
	 *
	 * @return array
	 */
	public static function generatePublicTaxonomyListForDd($pluginPath)
	{
		$taxGroupList = array(
			__('Default (built in)', 'wp-asset-clean-up') => array(
				'category_all' => __('Categories', 'wp-asset-clean-up'),
				'post_tag_all' => __('Tags', 'wp-asset-clean-up')
			)
		);

		$possibleTaxonomiesWithLink = get_taxonomies( array( 'public' => true, 'show_ui' => true, 'query_var' => true, 'rewrite' => true, 'show_in_menu' => true, '_builtin' => false ) );

		// "WooCommerce" (or any other plugins that generates custom taxonomies) creates the "product_cat" taxonomy page
		// It doesn't make sense to unload "WooCommerce" when a "WooCommerce" taxonomy page is visited
		// as that page would not exist anyway without WooCommerce (thus, hide it from the drop-down list)
		if ($pluginPath === 'woocommerce/woocommerce.php') {
			if (array_key_exists('product_cat', $possibleTaxonomiesWithLink)) {
				unset($possibleTaxonomiesWithLink['product_cat']);
			}

			if (array_key_exists('product_tag', $possibleTaxonomiesWithLink)) {
				unset($possibleTaxonomiesWithLink['product_tag']);
			}
		}

		if ($pluginPath === 'easy-digital-downloads/easy-digital-downloads.php') {
			if (array_key_exists('download_category', $possibleTaxonomiesWithLink)) {
				unset($possibleTaxonomiesWithLink['download_category']);
			}

			if (array_key_exists('download_tag', $possibleTaxonomiesWithLink)) {
				unset($possibleTaxonomiesWithLink['download_tag']);
			}
		}

		if ( Misc::isPluginActive('woocommerce/woocommerce.php')
		     && array_key_exists('product_cat', $possibleTaxonomiesWithLink)
		     && array_key_exists('product_tag', $possibleTaxonomiesWithLink) ) {
			$taxGroupList[__('WooCommerce', 'wp-asset-clean-up')] = array(
				'product_cat_all' => __('Product categories', 'wp-asset-clean-up'),
				'product_tag_all' => __('Product tags', 'wp-asset-clean-up'),
			);
			unset( $possibleTaxonomiesWithLink['product_cat'], $possibleTaxonomiesWithLink['product_tag'] );
		}

		if ( Misc::isPluginActive('easy-digital-downloads/easy-digital-downloads.php')
		     && array_key_exists('download_category', $possibleTaxonomiesWithLink)
		     && array_key_exists('download_tag', $possibleTaxonomiesWithLink) ) {
			$taxGroupList[__('Easy Digital Downloads', 'wp-asset-clean-up')] = array(
				'download_category_all' => __('Download categories', 'wp-asset-clean-up'),
				'download_tag_all' => __('Download tags', 'wp-asset-clean-up'),
			);
			unset( $possibleTaxonomiesWithLink['download_category'], $possibleTaxonomiesWithLink['download_tag'] );
		}

		if ( ! empty($possibleTaxonomiesWithLink) ) {
			$taxGroupList['Others'] = $possibleTaxonomiesWithLink;
		}

		return $taxGroupList;
	}

	/**
	 * @param $ddKeyName
	 * @param $enabled
	 * @param $taxGroupList
	 * @param $unloadViaTaxChosen
	 * @param $pluginPath
	 *
	 * @return void
	 */
	public static function buildTaxListDd($ddKeyName, $enabled, $taxGroupList, $unloadViaTaxChosen, $pluginPath)
    {
        ?>
        <select multiple="multiple"
                style="width: 100%;"
                data-placeholder="<?php esc_attr_e('Choose the page types'); ?>..."
                class="<?php if ($enabled && ! empty($unloadViaTaxChosen)) { ?>wpacu_chosen_select<?php } ?> wpacu_plugin_manage_via_page_type_dd"
                name="wpacu_plugins[<?php echo esc_attr($pluginPath); ?>][<?php echo $ddKeyName; ?>][values][]">
		    <?php
		    foreach ($taxGroupList as $taxGroupLabel => $taxList) {
			    echo '<optgroup label="'.esc_attr($taxGroupLabel).'">';

			    foreach ($taxList as $taxValue => $taxText) {
				    ?>
                    <option <?php if (in_array($taxValue, $unloadViaTaxChosen)) { echo 'selected="selected"'; } ?> value="<?php echo $taxValue; ?>"><?php echo $taxText; ?></option>
				    <?php
			    }

			    echo '</optgroup>';
		    }
		    ?>
        </select>
        <?php
    }

	/**
	 *
	 */
	public function notices()
	{
		// After "Save changes" is clicked
		if (Misc::getVar('get', 'page') === WPACU_PLUGIN_ID.'_plugins_manager' &&
            Misc::getVar('get', 'wpacu_sub_page') &&
            get_transient('wpacu_plugins_manager_updated')) {
			delete_transient('wpacu_plugins_manager_updated');

			$appliedForText = '';
			if ( isset($_GET['wpacu_sub_page']) ) {
				if ( $_GET['wpacu_sub_page'] === 'manage_plugins_front' ) {
					$appliedForText = 'the frontend view';
				} elseif ( $_GET['wpacu_sub_page'] === 'manage_plugins_dash' ) {
					$appliedForText = 'the Dashboard view (/wp-admin/)';
				}
			}

			if ($appliedForText !== '') {
			?>
			<div style="margin-bottom: 15px; margin-left: 0; width: 90%;" class="notice notice-success is-dismissible">
				<p><span class="dashicons dashicons-yes"></span> <?php echo sprintf(__('The plugins\' rules were successfully applied within %s.', 'wp-asset-clean-up-pro'), $appliedForText); ?></p>
			</div>
			<?php
            }
		}
	}
	// [/wpacu_pro]
}
