<?php
namespace WpAssetCleanUp;

use WpAssetCleanUp\OptimiseAssets\OptimizeCommon;

/**
 * Class Debug
 * @package WpAssetCleanUp
 */
class Debug
{
	/**
	 * Debug constructor.
	 */
	public function __construct()
	{
		if (isset($_GET['wpacu_debug'])) {
		    if (is_admin()) { // Dashboard view
                // [wpacu_pro]
			    add_action('admin_init', array($this, 'showDebugOptionsDashPrepare'), PHP_INT_MAX);
			    add_action('wp_loaded', array($this, 'showDebugOptionsDashOutput'), PHP_INT_MAX);
			    // [/wpacu_pro]
            } else { // Frontend View
			    add_action('wp_footer', array($this, 'showDebugOptionsFront'), PHP_INT_MAX);
            }
		}

		foreach(array('wp', 'admin_init') as $wpacuActionHook) {
			add_action( $wpacuActionHook, static function() {
				if (isset( $_GET['wpacu_get_cache_dir_size'] ) && Menu::userCanManageAssets()) {
					self::printCacheDirInfo();
				}

				// For debugging purposes
				if (isset($_GET['wpacu_get_already_minified']) && Menu::userCanManageAssets()) {
                    echo '<pre>'; print_r(OptimizeCommon::getAlreadyMarkedAsMinified()); echo '</pre>';
                    exit();
                }

				if (isset($_GET['wpacu_remove_already_minified']) && Menu::userCanManageAssets()) {
					echo '<pre>'; print_r(OptimizeCommon::removeAlreadyMarkedAsMinified()); echo '</pre>';
					exit();
				}

				if (isset($_GET['wpacu_limit_already_minified']) && Menu::userCanManageAssets()) {
					OptimizeCommon::limitAlreadyMarkedAsMinified();
					echo '<pre>'; print_r(OptimizeCommon::getAlreadyMarkedAsMinified()); echo '</pre>';
					exit();
				}
			} );
		}
	}

	/**
	 *
	 */
	public function showDebugOptionsFront()
	{
	    if (! Menu::userCanManageAssets()) {
	        return;
        }

	    $markedCssListForUnload = array_unique(Main::instance()->allUnloadedAssets['styles']);
		$markedJsListForUnload  = array_unique(Main::instance()->allUnloadedAssets['scripts']);

		$allDebugOptions = array(
			// [For CSS]
			'wpacu_no_css_unload'  => 'Do not apply any CSS unload rules',
			'wpacu_no_css_minify'  => 'Do not minify any CSS',
			'wpacu_no_css_combine' => 'Do not combine any CSS',

			'wpacu_no_css_preload_basic' => 'Do not preload any CSS (Basic)',
			// [wpacu_pro]
            'wpacu_no_css_position_change' => 'Do not change any CSS Position (e.g. from HEAD to BODY)',
			'wpacu_no_css_preload_async' => 'Do not preload any CSS (Async)',
			// [/wpacu_pro]
            // [/For CSS]

			// [For JS]
			'wpacu_no_js_unload'  => 'Do not apply any JavaScript unload rules',
			'wpacu_no_js_minify'  => 'Do not minify any JavaScript',
			'wpacu_no_js_combine' => 'Do not combine any JavaScript',

			// [wpacu_pro]
			'wpacu_no_async'      => 'Do not async load any JavaScript',
			'wpacu_no_defer'     => 'Do not defer load any JavaScript',
			// [/wpacu_pro]

			'wpacu_no_js_preload_basic' => 'Do not preload any JS (Basic)',

            // [wpacu_pro]
			'wpacu_no_js_position_change' => 'Do not change any JS Position (e.g. from HEAD to BODY)',
			// [/wpacu_pro]
			// [/For JS]

			// Others
			'wpacu_no_frontend_show' => 'Do not show the bottom CSS/JS managing list',
			'wpacu_no_admin_bar'     => 'Do not show the admin bar',
			'wpacu_no_html_changes'  => 'Do not alter the HTML DOM (this will also load all assets non-minified and non-combined)',
		);
		?>
		<style <?php echo Misc::getStyleTypeAttribute(); ?>>
			<?php echo file_get_contents(WPACU_PLUGIN_DIR.'/assets/wpacu-debug.css'); ?>
		</style>

        <script <?php echo Misc::getScriptTypeAttribute(); ?>>
	        <?php echo file_get_contents(WPACU_PLUGIN_DIR.'/assets/wpacu-debug.js'); ?>
        </script>

		<div id="wpacu-debug-options">
            <table>
                <tr>
                    <td style="vertical-align: top;">
                        <p>View the page with the following options <strong>disabled</strong> (for debugging purposes):</p>
                        <form method="post">
                            <ul class="wpacu-options">
                            <?php
                            foreach ($allDebugOptions as $debugKey => $debugText) {
                            ?>
                                <li>
                                    <label><input type="checkbox"
                                                  name="<?php echo esc_attr($debugKey); ?>"
                                                  <?php if ( ! empty($_GET) && array_key_exists($debugKey, $_GET) ) { echo 'checked="checked"'; } ?> /> &nbsp;<?php echo esc_html($debugText); ?></label>
                                </li>
                            <?php
                            }
                            ?>
                            </ul>

                            <!-- [wpacu_pro] -->
                            <hr />

                            <strong>Preview page: Thick plugins for unloading</strong><br >
                            <p><small>By default, any already unloaded plugins from "Plugins Manager" will be selected here (unless you already submitted the form with a different selection).</small></p>

                            <?php if (isset($GLOBALS['wpacu_filtered_plugins']) && ! empty($GLOBALS['wpacu_filtered_plugins'])) { ?>
                                <p><small>If you want ALL plugins to load back, just deselect everything from the list below and submit the form.</small></p>
                            <?php } ?>

                            <table>
                                <?php
                                $activePlugins = PluginsManager::getActivePlugins();
                                uasort($activePlugins, function($a, $b) {
	                                return strcmp($a['title'], $b['title']);
                                });

                                $pluginsIcons = Misc::getAllActivePluginsIcons();

                                foreach ($activePlugins as $pluginData) {
	                                $pluginTitle = $pluginData['title'];
	                                $pluginPath = $pluginData['path'];
	                                list($pluginDir) = explode('/', $pluginPath);
                                    ?>
                                        <tr class="wpacu_plugin_row_debug_unload <?php if (isset($GLOBALS['wpacu_filtered_plugins']) && in_array($pluginPath, $GLOBALS['wpacu_filtered_plugins'])) { echo 'wpacu_plugin_row_debug_unload_marked'; } ?>">
                                            <td style="padding: 0; width: 46px; text-align: center;">
                                                <label style="cursor: pointer; margin: -12px 0 0 12px;" class="wpacu_plugin_unload_debug_container" for="wpacu_filter_plugin_<?php echo esc_attr($pluginPath); ?>">
                                                    <input type="checkbox"
                                                           class="wpacu_plugin_unload_debug_checkbox"
                                                           style="cursor: pointer; width: 20px; height: 20px;"
                                                           id="wpacu_filter_plugin_<?php echo esc_attr($pluginPath); ?>"
                                                           name="wpacu_filter_plugins[]"
                                                           value="<?php echo esc_attr($pluginPath); ?>"
			                                            <?php if (isset($GLOBALS['wpacu_filtered_plugins']) && in_array($pluginPath, $GLOBALS['wpacu_filtered_plugins'])) { echo 'checked="checked"'; } ?> />
                                                    <span class="wpacu_plugin_unload_debug_checkbox_checkmark"></span>
                                                </label>
                                            </td>
                                            <td style="padding: 5px; text-align: center; cursor: pointer;">
                                                <label style="cursor: pointer;" for="wpacu_filter_plugin_<?php echo esc_attr($pluginPath); ?>">
                                                    <?php if(isset($pluginsIcons[$pluginDir])) { ?>
                                                        <img width="40" height="40" alt="" src="<?php echo esc_attr($pluginsIcons[$pluginDir]); ?>" />
                                                    <?php } else { ?>
                                                        <div><span style="font-size: 34px; width: 34px; height: 34px;" class="dashicons dashicons-admin-plugins"></span></div>
                                                    <?php } ?>
                                                </label>
                                            </td>
                                            <td style="padding: 10px;">
                                                <label for="wpacu_filter_plugin_<?php echo esc_attr($pluginPath); ?>" style="cursor: pointer;"><span class="wpacu_plugin_title" style="font-weight: 500;"><?php echo esc_html($pluginTitle); ?></span></label><br />
                                                <span class="wpacu_plugin_path" style="font-style: italic;"><small><?php echo esc_attr($pluginPath); ?></small></span>
                                            </td>
                                        </tr>
                                    <?php
                                }
                                ?>
                            </table>
                            <!-- [/wpacu_pro] -->

                            <div>
                                <input type="submit"
                                       value="Preview this page with the changes made above" />
                            </div>
                            <input type="hidden" name="wpacu_debug" value="on" />
                        </form>
                    </td>
                    <td style="vertical-align: top;">
                        <?php
                        // [wpacu_pro]
                        if (isset($GLOBALS['wpacu_filtered_plugins']) && $wpacuFilteredPlugins = $GLOBALS['wpacu_filtered_plugins']) {
                            sort($wpacuFilteredPlugins);
                            ?>
                            <p><strong>Unloaded plugins:</strong> The following plugins were unloaded on this page as they have matching unload rules.</p>
                            <ul>
                                <?php
                                foreach ($wpacuFilteredPlugins as $filteredPlugin) {
                                    echo '<li style="color: darkred;">'.esc_html($filteredPlugin).'</li>'."\n";
                                }
                                ?>
                            </ul>
                        <?php
                        }
                        // [/wpacu_pro]
                        ?>

	                    <div style="margin: 0 0 10px; padding: 10px 0;">
	                        <strong>CSS handles marked for unload:</strong>&nbsp;
	                        <?php
	                        if (! empty($markedCssListForUnload)) {
	                            sort($markedCssListForUnload);
		                        $markedCssListForUnloadFiltered = array_map(static function($handle) {
		                        	return '<span style="color: darkred;">'.esc_html($handle).'</span>';
		                        }, $markedCssListForUnload);
	                            echo implode(' &nbsp;/&nbsp; ', $markedCssListForUnloadFiltered);
	                        } else {
	                            echo 'None';
	                        }
	                        ?>
	                    </div>

	                    <div style="margin: 0 0 10px; padding: 10px 0;">
	                        <strong>JS handles marked for unload:</strong>&nbsp;
	                        <?php
	                        if (! empty($markedJsListForUnload)) {
	                            sort($markedJsListForUnload);
		                        $markedJsListForUnloadFiltered = array_map(static function($handle) {
			                        return '<span style="color: darkred;">'.esc_html($handle).'</span>';
		                        }, $markedJsListForUnload);

	                            echo implode(' &nbsp;/&nbsp; ', $markedJsListForUnloadFiltered);
	                        } else {
	                            echo 'None';
	                        }
	                        ?>
	                    </div>

	                    <hr />

                        <div style="margin: 0 0 10px; padding: 10px 0;">
							<ul style="list-style: none; padding-left: 0;">
                                <li style="margin-bottom: 10px;">Dequeue any chosen styles (.css): <?php echo Misc::printTimingFor('filter_dequeue_styles',  '{wpacu_filter_dequeue_styles_exec_time} ({wpacu_filter_dequeue_styles_exec_time_sec})'); ?></li>
                                <li style="margin-bottom: 20px;">Dequeue any chosen scripts (.js): <?php echo Misc::printTimingFor('filter_dequeue_scripts', '{wpacu_filter_dequeue_scripts_exec_time} ({wpacu_filter_dequeue_scripts_exec_time_sec})'); ?></li>

                                <li style="margin-bottom: 10px;">Prepare CSS files to optimize: {wpacu_prepare_optimize_files_css_exec_time} ({wpacu_prepare_optimize_files_css_exec_time_sec})</li>
                                <li style="margin-bottom: 20px;">Prepare JS files to optimize: {wpacu_prepare_optimize_files_js_exec_time} ({wpacu_prepare_optimize_files_js_exec_time_sec})</li>

                                <li style="margin-bottom: 10px;">OptimizeCommon - HTML alteration via <em>wp_loaded</em>: {wpacu_alter_html_source_exec_time} ({wpacu_alter_html_source_exec_time_sec})
                                    <ul id="wpacu-debug-timing">
                                        <li style="margin-top: 10px; margin-bottom: 10px;">&nbsp;OptimizeCSS: {wpacu_alter_html_source_for_optimize_css_exec_time} ({wpacu_alter_html_source_for_optimize_css_exec_time_sec})
                                            <ul>
                                                <li>Google Fonts Optimization/Removal: {wpacu_alter_html_source_for_google_fonts_optimization_removal_exec_time}</li>
                                                <li>From CSS file to Inline: {wpacu_alter_html_source_for_inline_css_exec_time}</li>
                                                <li>Update Original to Optimized: {wpacu_alter_html_source_original_to_optimized_css_exec_time}</li>
                                                <li>Move CSS LINKs (HEAD to BODY and vice-versa): {wpacu_alter_html_source_for_change_css_position_exec_time}</li>
                                                <li>Preloads: {wpacu_alter_html_source_for_preload_css_exec_time}</li>
	                                            <!-- [wpacu_pro] -->
                                                    <li>Preloads (NOSCRIPT fallback): {wpacu_alter_html_source_for_add_async_preloads_noscript_exec_time}</li>
	                                            <!-- [/wpacu_pro] -->
                                                <!-- -->
                                                <li>Combine: {wpacu_alter_html_source_for_combine_css_exec_time}</li>
                                                <li>Minify Inline Tags: {wpacu_alter_html_source_for_minify_inline_style_tags_exec_time}</li>
                                                <li>Unload (ignore dependencies): {wpacu_alter_html_source_unload_ignore_deps_css_exec_time}</li>
	                                            <!-- [wpacu_pro] -->
                                                    <li>Defer Footer CSS: {wpacu_alter_html_source_for_defer_footer_css_exec_time}</li>
	                                                <li>Alter Inline CSS (font-display): {wpacu_local_fonts_display_style_inline_exec_time}</li>
	                                            <!-- [/wpacu_pro] -->
                                            </ul>
                                        </li>

                                        <li style="margin-top: 10px; margin-bottom: 10px;">OptimizeJs: {wpacu_alter_html_source_for_optimize_js_exec_time} ({wpacu_alter_html_source_for_optimize_js_exec_time_sec})
                                            <ul>
	                                            <!-- [wpacu_pro] -->
                                                    <li>From JS File to Inline: {wpacu_alter_html_source_for_inline_js_exec_time}</li>
	                                            <!-- [/wpacu_pro] -->
                                                <li>Update Original to Optimized: {wpacu_alter_html_source_original_to_optimized_js_exec_time}</li>
                                                <li>Preloads: {wpacu_alter_html_source_for_preload_js_exec_time}</li>
                                                <!-- -->
                                                <li>Combine: {wpacu_alter_html_source_for_combine_js_exec_time}</li>
	                                            <!-- [wpacu_pro] -->
	                                                <li>Move scripts to BODY: {wpacu_alter_html_source_move_scripts_to_body_exec_time}</li>
                                                    <li>Minify Inline Tags: {wpacu_alter_html_source_for_minify_inline_script_tags_exec_time}</li>
	                                            <!-- [/wpacu_pro] -->
                                                <li>Unload (ignore dependencies): {wpacu_alter_html_source_unload_ignore_deps_js_exec_time}</li>
                                                <li>Move any inline wih jQuery code after jQuery library: {wpacu_alter_html_source_move_inline_jquery_after_src_tag_exec_time}</li>
                                            </ul>
                                        </li>

                                        <li style="margin-top: 10px; margin-bottom: 10px;">Hardcoded CSS/JS (fetch &amp; strip): {wpacu_fetch_strip_hardcoded_assets_exec_time}
                                            <ul>
	                                            <li>Fetch Marked for Unload: {wpacu_fetch_marked_for_unload_hardcoded_assets_exec_time}</li>
	                                            <li>Fetch All from the Current Page: {wpacu_fetch_all_hardcoded_assets_exec_time}</li>
	                                            <li>Strip Marked For Unload: {wpacu_strip_marked_hardcoded_assets_exec_time}</li>
                                            </ul>
                                        </li>

                                        <li>HTML CleanUp: {wpacu_alter_html_source_cleanup_exec_time}
                                            <ul>
                                                <li>Strip HTML Comments: {wpacu_alter_html_source_for_remove_html_comments_exec_time}</li>
	                                            <li>Remove Generator Meta Tags: {wpacu_alter_html_source_for_remove_meta_generators_exec_time}</li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

								<li style="margin-bottom: 10px;">Output CSS &amp; JS Management List: {wpacu_output_css_js_manager_exec_time} ({wpacu_output_css_js_manager_exec_time_sec})</li>

                                <!-- -->
							</ul>
	                    </div>
                    </td>
                </tr>
            </table>
		</div>
		<?php
	}

    // [wpacu_pro]
	/**
	 *
	 */
	public function showDebugOptionsDashPrepare()
	{
		if (! Menu::userCanManageAssets()) {
			return;
		}

		ob_start();
		?>
        <!-- [ASSET CLEANUP PRO DEBUG] -->
        <style <?php echo Misc::getStyleTypeAttribute(); ?>>
            <?php echo file_get_contents(WPACU_PLUGIN_DIR.'/assets/wpacu-debug.css'); ?>
        </style>
        <script <?php echo Misc::getScriptTypeAttribute(); ?>>
            <?php echo file_get_contents(WPACU_PLUGIN_DIR.'/assets/wpacu-debug.js'); ?>
        </script>
        <?php
        $wpacuUnloadedPluginsStatus = false;

        if (isset($GLOBALS['wpacu_filtered_plugins']) && $wpacuFilteredPlugins = $GLOBALS['wpacu_filtered_plugins']) {
	        $wpacuUnloadedPluginsStatus = true; // there are rules applied
        }
        ?>
        <div id="wpacu-debug-admin-area">
            <h4><?php echo WPACU_PLUGIN_TITLE; ?>: Debug Notice</h4>
	        <?php
	        // [wpacu_pro]
	        if ($wpacuUnloadedPluginsStatus) {
		        sort($wpacuFilteredPlugins);

		        // Get all plugins and their basic information
		        $allPlugins = $allRemainingPlugins = get_plugins();
		        $pluginsIcons = Misc::getAllActivePluginsIcons();

		        ?>
                <p>The following plugins are <strong style="color: darkred;">unloaded on this page</strong> using the rules that took effect from <em>"Plugins Manager" -&gt; "IN THE DASHBOARD /wp-admin/"</em> (within <?php echo WPACU_PLUGIN_TITLE; ?>'s menu):</p>
                <div id="wpacu-debug-plugins-list">
			        <?php
			        foreach ($wpacuFilteredPlugins as $pluginPath) {
                        unset($allRemainingPlugins[$pluginPath]);

			            $pluginTitle = '';
			            if (isset($allPlugins[$pluginPath]['Name']) && $allPlugins[$pluginPath]['Name']) {
				            $pluginTitle = $allPlugins[$pluginPath]['Name'];
			            }

				        list($pluginDir) = explode('/', $pluginPath);
				        ?>
                        <div style="margin: 0 0 6px;">
                            <div class="wpacu_plugin_icon" style="float: left;">
                                <?php if (isset($pluginsIcons[$pluginDir])) { ?>
                                    <img width="20" height="20" alt="" src="<?php echo esc_attr($pluginsIcons[$pluginDir]); ?>" />
                                <?php } else { ?>
                                    <div><span class="dashicons dashicons-admin-plugins"></span></div>
                                <?php } ?>
                            </div>

                            <div style="float: left; margin-left: 8px;">
                                <div><span><strong><?php echo esc_html($pluginTitle); ?></strong></span> * <em><?php echo esc_html($pluginPath); ?></em></div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    <?php
			        }
			        ?>
                </div>
		        <?php

                if ( ! empty($allRemainingPlugins) ) {
                ?>
                    <hr />
                    <p>The following plugins remain <strong style="color: green;">loaded on this page</strong>:</p>
                    <div id="wpacu-debug-plugins-list">
		                <?php
		                foreach (array_keys($allRemainingPlugins) as $pluginPath) {
			                $pluginTitle = '';
			                if (isset($allPlugins[$pluginPath]['Name']) && $allPlugins[$pluginPath]['Name']) {
				                $pluginTitle = $allPlugins[$pluginPath]['Name'];
			                }

			                list($pluginDir) = explode('/', $pluginPath);
			                ?>
                            <div style="margin: 0 0 6px;">
                                <div class="wpacu_plugin_icon" style="float: left;">
					                <?php if (isset($pluginsIcons[$pluginDir])) { ?>
                                        <img width="20" height="20" alt="" src="<?php echo esc_attr($pluginsIcons[$pluginDir]); ?>" />
					                <?php } else { ?>
                                        <div><span class="dashicons dashicons-admin-plugins"></span></div>
					                <?php } ?>
                                </div>

                                <div style="float: left; margin-left: 8px;">
                                    <div><span><strong><?php echo esc_html($pluginTitle); ?></strong></span> * <em><?php echo esc_html($pluginPath); ?></em></div>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
			                <?php
		                }
		                ?>
                    </div>
                <?php
                }
	        } else {
	            ?>
                <p>There are no plugins unloaded rules that apply to this page from <em>"Plugins Manager" -&gt; "IN THE DASHBOARD /wp-admin/"</em> (within <?php echo WPACU_PLUGIN_TITLE; ?>'s menu).</p>
                <?php
	        }
	        // [/wpacu_pro]
	        ?>
        </div>
        <!-- [/ASSET CLEANUP PRO DEBUG] -->
		<?php
        $GLOBALS['wpacu_debug_output'] = ob_get_clean();
	}

	/**
	 *
	 */
	public function showDebugOptionsDashOutput()
    {
        ob_start(function($htmlSource) {
            return preg_replace(
                '#</body>(\s+|\n+)</html>#si',
                $GLOBALS['wpacu_debug_output'].'</body>'."\n".'</html>',
                $htmlSource);
        });
    }
	// [/wpacu_pro]

	/**
	 *
	 */
	public static function printCacheDirInfo()
    {
    	$assetCleanUpCacheDirRel = OptimizeCommon::getRelPathPluginCacheDir();
	    $assetCleanUpCacheDir  = WP_CONTENT_DIR . $assetCleanUpCacheDirRel;

	    echo '<h3>'.WPACU_PLUGIN_TITLE.': Caching Directory Stats</h3>';

	    if (is_dir($assetCleanUpCacheDir)) {
	    	$printCacheDirOutput = '<em>'.str_replace($assetCleanUpCacheDirRel, '<strong>'.$assetCleanUpCacheDirRel.'</strong>', $assetCleanUpCacheDir).'</em>';

	    	if (! is_writable($assetCleanUpCacheDir)) {
			    echo '<span style="color: red;">'.
			            'The '.wp_kses($printCacheDirOutput, array('em' => array(), 'strong' => array())).' directory is <em>not writable</em>.</span>'.
			         '<br /><br />';
		    } else {
			    echo '<span style="color: green;">The '.wp_kses($printCacheDirOutput, array('em' => array(), 'strong' => array())).' directory is <em>writable</em>.</span>' . '<br /><br />';
		    }

		    $dirItems = new \RecursiveDirectoryIterator( $assetCleanUpCacheDir,
			    \RecursiveDirectoryIterator::SKIP_DOTS );

		    $totalFiles = 0;
		    $totalSize  = 0;

		    foreach (
			    new \RecursiveIteratorIterator( $dirItems, \RecursiveIteratorIterator::SELF_FIRST,
				    \RecursiveIteratorIterator::CATCH_GET_CHILD ) as $item
		    ) {
			    if ($item->isDir()) {
			    	echo '<br />';

				    $appendAfter = ' - ';

			    	if (is_writable($item)) {
					    $appendAfter .= ' <em><strong>writable</strong> directory</em>';
				    } else {
					    $appendAfter .= ' <em><strong style="color: red;">not writable</strong> directory</em>';
				    }
			    } elseif ($item->isFile()) {
			    	$appendAfter = '(<em>'.Misc::formatBytes($item->getSize()).'</em>)';

			    	echo '&nbsp;-&nbsp;';
			    }

			    echo wp_kses($item.' '.$appendAfter, array(
			            'em' => array(),
                        'strong' => array('style' => array()),
                        'br' => array(),
                        'span' => array('style' => array())
                    ))

                     .'<br />';

			    if ( $item->isFile() ) {
				    $totalSize += $item->getSize();
				    $totalFiles ++;
			    }
		    }

		    echo '<br />'.'Total Files: <strong>'.$totalFiles.'</strong> / Total Size: <strong>'.Misc::formatBytes($totalSize).'</strong>';
	    } else {
		    echo 'The directory does not exists.';
	    }

	    exit();
    }
}
