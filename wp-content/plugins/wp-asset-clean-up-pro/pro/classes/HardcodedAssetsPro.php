<?php
namespace WpAssetCleanUpPro;

use WpAssetCleanUp\HardcodedAssets;
use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\Misc;
use WpAssetCleanUp\ObjectCache;
use WpAssetCleanUp\OptimiseAssets\MinifyCss;
use WpAssetCleanUp\OptimiseAssets\MinifyJs;

/**
 * Class HardcodedAssetsPro
 * @package WpAssetCleanUpPro
 */
class HardcodedAssetsPro
{
	/**
	 * Late call in case hardcoded CSS/JS loaded later needs to be stripped (e.g. from plugins such as "Smart Slider 3" that loads non-enqueued files)
	 */
	public static function initLateAlterationForGuestView()
	{
		// Not for logged-in admin managing the assets
		if (HardcodedAssets::useBufferingForEditFrontEndView()) {
			return;
		}

		// Do not do any changes if "Test Mode" is Enabled and the user is a guest
		if (! Menu::userCanManageAssets() && Main::instance()->settings['test_mode']) {
			return;
		}

		// No hardcoded assets marked for unload? No point in continuing, save resources!
		$anyHardCodedAssetsMarkedForUnload = self::getHardcodedUnloadList();

		if (empty($anyHardCodedAssetsMarkedForUnload)) {
			return;
		}

		ob_start();

		add_action('shutdown', static function() {
			$htmlSource = '';

			// We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
			// that buffer's output into the final output.
			$htmlSourceLevel = ob_get_level();

			for ($wpacuI = 0; $wpacuI < $htmlSourceLevel; $wpacuI++) {
				$htmlSource .= ob_get_clean();
			}

			$anyHardCodedAssetsList = HardcodedAssets::getAll( $htmlSource, false, true );

			if (! empty($anyHardCodedAssetsList)) {
				$htmlSource = self::maybeStripHardcodedAssets( $htmlSource, $anyHardCodedAssetsList );
			}

			echo $htmlSource;
		}, 0);
	}

	/**
	 * @return array
	 */
	public static function getHardcodedUnloadList()
	{
		$hardcodedUnloadList['wpacu_hardcoded_links']                    = ObjectCache::wpacu_cache_get('wpacu_hardcoded_links')  ?: array();
		$hardcodedUnloadList['wpacu_hardcoded_styles']                   = ObjectCache::wpacu_cache_get('wpacu_hardcoded_styles') ?: array();
		$hardcodedUnloadList['wpacu_hardcoded_scripts_src']              = ObjectCache::wpacu_cache_get('wpacu_hardcoded_scripts_src') ?: array();
		$hardcodedUnloadList['wpacu_hardcoded_scripts_noscripts_inline'] = ObjectCache::wpacu_cache_get('wpacu_hardcoded_scripts_noscripts_inline') ?: array();

		return Misc::filterList($hardcodedUnloadList);
	}

	/**
	 * @param $htmlSource
	 * @param array $anyHardCodedAssets
	 * @param bool|array $hardcodedMarkedForUnloadList
	 *
	 * @return string|string[]
	 */
	public static function maybeStripHardcodedAssets($htmlSource, $anyHardCodedAssets = array(), $hardcodedMarkedForUnloadList = false)
	{
		// No hardcoded assets were found on this page, thus any hardcoded assets chosen to be unloaded are irrelevant to be checked
		if (empty($anyHardCodedAssets)) {
			return $htmlSource;
		}

		$handlesInfo = Main::getHandlesInfo();
		if ($hardcodedMarkedForUnloadList === false) {
			$hardcodedMarkedForUnloadList = self::getHardcodedUnloadList();
		}

		// Go through the unloaded CSS/JS and strip them from the HTML code
		if (! empty($hardcodedMarkedForUnloadList)) {
			foreach ($hardcodedMarkedForUnloadList as $hardCodedType => $hardcodedHandles) {
				$hardcodedHandles = array_unique($hardcodedHandles);

				foreach ($hardcodedHandles as $hardcodedHandle) {
					// This has to be turned off; sometimes it's used for loading the scripts marked for unload for debugging purposes
					$preventHardCodedCssUnloading = isset($_GET['wpacu_no_hd_css_unload']);

					// STYLEs and LINKs ("stylesheet")
					if ( (! $preventHardCodedCssUnloading) &&
					     (isset($handlesInfo['styles'][$hardcodedHandle]['output']) && $handlesInfo['styles'][$hardcodedHandle]['output']) &&
					     in_array($hardCodedType, array('wpacu_hardcoded_links', 'wpacu_hardcoded_styles')) )
					{
						$htmlSourceBefore = $htmlSource;
						$htmlSource = str_replace( $handlesInfo['styles'][ $hardcodedHandle ]['output'], '', $htmlSource );

						if ($htmlSource === $htmlSourceBefore) { // No change? Perhaps it was altered (e.g. minified or had white space stripped)
							$htmlSource = str_replace( self::alternativeValuesIfMinified($handlesInfo['styles'][ $hardcodedHandle ]['output'] ), '', $htmlSource );

							if ( $htmlSource === $htmlSourceBefore && isset( $handlesInfo['styles'][ $hardcodedHandle ]['output_min'] ) && $handlesInfo['styles'][ $hardcodedHandle ]['output_min'] ) {
								$htmlSource = str_replace( $handlesInfo['styles'][ $hardcodedHandle ]['output_min'], '', $htmlSource );
							}

							// Still no change? The tag output might be changed, but the relative source file could be the same
							// Or the tag has new attribute, but the inline code is the same
							// Go through the HTML source one last time and attempt to strip the tag if it matches the criteria
							if ($htmlSource === $htmlSourceBefore && count(self::possibleHardcodedOutputs($handlesInfo['styles'][ $hardcodedHandle ]['output'], $hardCodedType, $htmlSource)) > 1) {
								foreach ( self::possibleHardcodedOutputs( $handlesInfo['styles'][ $hardcodedHandle ]['output'], $hardCodedType, $htmlSource ) as $outputToReplace ) {
									$htmlSource = str_replace( $outputToReplace, '', $htmlSource );
								}
							}
						}
						}

					// This has to be turned off; sometimes it's used for loading the scripts marked for unload for debugging purposes
					$preventHardCodedJsUnloading = isset($_GET['wpacu_no_hd_js_unload']);

					// SCRIPTs ("src" and inline) and NOSCRIPTs
					if ( (! $preventHardCodedJsUnloading) &&
					     (isset($handlesInfo['scripts'][$hardcodedHandle]['output']) && $handlesInfo['scripts'][$hardcodedHandle]['output']) &&
					     in_array($hardCodedType, array('wpacu_hardcoded_scripts_src', 'wpacu_hardcoded_scripts_noscripts_inline')) )
					{
						$htmlSourceBefore = $htmlSource;

						$htmlSource = str_replace( $handlesInfo['scripts'][ $hardcodedHandle ]['output'], '', $htmlSource );

						if ($htmlSource === $htmlSourceBefore) { // No change? Perhaps it was altered (e.g. minified or had white space stripped)
							$htmlSource = str_replace( self::alternativeValuesIfMinified( $handlesInfo['scripts'][ $hardcodedHandle ]['output'] ), '', $htmlSource );

							if ( $htmlSource === $htmlSourceBefore && isset( $handlesInfo['scripts'][ $hardcodedHandle ]['output_min'] ) && $handlesInfo['scripts'][ $hardcodedHandle ]['output_min'] ) {
								$htmlSource = str_replace( $handlesInfo['scripts'][ $hardcodedHandle ]['output_min'], '', $htmlSource );
							}

							// Still no change? The tag output might be changed, but the relative source file could be the same
							// Or the tag has new attribute, but the inline code is the same
							// Go through the HTML source one last time and attempt to strip the tag if it matches the criteria
							$possibleHardcodedOutputs = self::possibleHardcodedOutputs($handlesInfo['scripts'][ $hardcodedHandle ]['output'], $hardCodedType, $htmlSource);

							if ($htmlSource === $htmlSourceBefore && count($possibleHardcodedOutputs) > 1) {
								foreach ( $possibleHardcodedOutputs as $outputToReplace ) {
									$htmlSource = str_replace( $outputToReplace, '', $htmlSource );
								}
							}
						}
						}
				}
			}
		}

		return $htmlSource;
	}

	/**
	 * Sometimes, libraries such as Minify_HTML (source: https://github.com/mrclay/minify/blob/master/lib/Minify.php) are used
	 * which alter the content of the hardcoded asset, so even though it's the same, Asset CleanUp (Pro) might not detect it
	 * Let's make sure they are still found in case Minify_HTML is triggered by a different optimization plugin (e.g. WP Rocket, Autoptimize)
	 *
	 * @param $hardcodedAsset
	 * @return array
	 */
	public static function alternativeValuesIfMinified($hardcodedAsset)
	{
		return array(
			MinifyHtmlPro::minify($hardcodedAsset)
		);
	}

	/**
	 * @param $savedHardcodedOutput
	 * @param $hardCodedType
	 * @param $htmlSource
	 *
	 * @return array
	 */
	public static function possibleHardcodedOutputs($savedHardcodedOutput, $hardCodedType, $htmlSource)
	{
		$possibleHardcodedOutputs = array($savedHardcodedOutput);

		// Is the hardcoded content the same for the targeted tag? Stop here
		if (strpos($htmlSource, $savedHardcodedOutput) !== false) {
			return $possibleHardcodedOutputs;
		}

		// Step 1: Determine the type of the hardcoded asset (STYLE, LINK with "href", SCRIPT, SCRIPT with "src")
		if (in_array($hardCodedType, array('wpacu_hardcoded_links', 'wpacu_hardcoded_scripts_src'))) {
			$relSource = HardcodedAssets::getRelSourceFromTagOutputForReference($savedHardcodedOutput);

			if (! $relSource) {
				return $possibleHardcodedOutputs;
			}

			if ($hardCodedType === 'wpacu_hardcoded_links') {
				preg_match_all( '#<link[^>]*' . preg_quote( $relSource, '/' ) . '.*(>)#Usmi', $htmlSource, $matchedTags );
			} else {
				preg_match_all( '#<script[^>]*' . preg_quote( $relSource, '/' ) . '.*(>)(|\s+)</script>#Usmi', $htmlSource, $matchedTags );

				}

			if ( ! isset($matchedTags[0][0]) ) {
				return $possibleHardcodedOutputs;
			}

			foreach ($matchedTags[0] as $matchedAliasTag) {
				$toMatchWithinString = ($hardCodedType === 'wpacu_hardcoded_links')
					? '#rel(\s+|)=(\s+|)(["\'])stylesheet(["\'])|src(\s+|)=(\s+|)stylesheet(\s+)#Usmi'
					: '#src(\s+|)=(\s+|)(["\'])(.*)(["\'])|src(\s+|)=(\s+|)(.*)(\s+)#Usmi';

				if (! preg_match($toMatchWithinString, $matchedAliasTag)) {
					continue;
				}

				$possibleHardcodedOutputs[] = $matchedAliasTag;
				$possibleHardcodedOutputs[] = MinifyHtmlPro::minify($matchedAliasTag); // perhaps it was manually minified (e.g. by a developer)
			}
		} elseif (in_array($hardCodedType, array('wpacu_hardcoded_styles', 'wpacu_hardcoded_scripts_noscripts_inline'))) {
			if ($hardCodedType === 'wpacu_hardcoded_styles') {
				$forTagType = 'style';
			} elseif ($hardCodedType === 'wpacu_hardcoded_scripts_noscripts_inline') {
				if (strpos($savedHardcodedOutput, '<script') === 0) {
					$forTagType = 'script';
				} elseif (strpos($savedHardcodedOutput, '<noscript') === 0) {
					$forTagType = 'noscript';
				} else {
					return $possibleHardcodedOutputs; // something's funny there
				}
			}

			$tagContent = self::getTagContent($savedHardcodedOutput, $forTagType);

			if ($tagContent) {
				preg_match_all('@(<'.$forTagType.'[^>]*?>)(.*?)</'.$forTagType.'>@si', $htmlSource, $matchedFromHtmlSource);

				if (isset($matchedFromHtmlSource[0], $matchedFromHtmlSource[2]) && ! empty($matchedFromHtmlSource[0]) && ! empty($matchedFromHtmlSource[2])) {
					foreach ($matchedFromHtmlSource[2] as $tagIndex => $tagContentFromList) {
						if ($forTagType === 'script' || $forTagType === 'noscript') {
							$tagOutputFromMatch = $matchedFromHtmlSource[0][$tagIndex];

							if ($forTagType === 'script' && stripos($tagOutputFromMatch, ' src') !== false) {
								continue; // Only SCRIPT with inline JS is allowed (no "src" attribute)
							}
						}

						if (trim($tagContentFromList) === '') {
							continue; // it needs to have a content to compare against
						}

						if ( trim($tagContentFromList) === $tagContent || self::compareMinifiedCss($tagContentFromList, $tagContent) ) {
							$possibleHardcodedOutputs[] = $matchedFromHtmlSource[0][$tagIndex];
						}
					}
				}
			}
		}

		return $possibleHardcodedOutputs;
	}

	/**
	 * @param $minifyOne
	 * @param $minifyTwo
	 *
	 * @return bool
	 */
	public static function compareMinifiedCss($minifyOne, $minifyTwo)
	{
		$minifyOneResult = MinifyCss::applyMinification($minifyOne, true);
		$minifyTwoResult = MinifyCss::applyMinification($minifyTwo, true);

		$reps = array(
			':0px}' => ':0}'
		);

		$minifyOneResult = str_replace(array_keys($reps), array_values($reps), $minifyOneResult);
		$minifyTwoResult = str_replace(array_keys($reps), array_values($reps), $minifyTwoResult);

		return $minifyOneResult === $minifyTwoResult;
	}

	/**
	 * @param $tagOutput
	 * @param $forTagType
	 *
	 * @return false|string
	 */
	public static function getTagContent($tagOutput, $forTagType)
	{
		preg_match_all('@(<'.$forTagType.'[^>]*?>)(.*?)</'.$forTagType.'>@si', $tagOutput, $matches);

		if (isset($matches[0][0], $matches[2][0]) && strlen($tagOutput) === strlen($matches[0][0])) {
			return trim($matches[2][0]);
		}

		return false;
	}

	/**
	 * @param $tagOutput
	 * @param $prefix
	 *
	 * @return string[]
	 */
	public static function getPossibleOlderHandlesForHardcodedTag($tagOutput, $prefix)
	{
		$possibleHandles = array( $prefix . sha1($tagOutput) ); // the original tag

		if (strpos($prefix, '_style') !== false) {
			$tagContentMinified = MinifyCss::applyMinification(self::getTagContent($tagOutput, 'style'));

			if ($tagContentMinified) {
				$possibleHandles[] = $prefix . sha1($tagContentMinified);

				$reps = array( ':0px}' => ':0}' );
				$minifyCssAlt = str_replace(array_keys($reps), array_values($reps), $tagContentMinified);

				$possibleHandles[] = $prefix . sha1($minifyCssAlt);
			}
		} elseif (strpos($prefix, '_script_inline') !== false) {
			$tagContentMinified = MinifyJs::applyMinification(self::getTagContent($tagOutput, 'script'));

			if ($tagContentMinified) {
				$possibleHandles[] = $prefix . sha1($tagContentMinified);
			}
		}

		return $possibleHandles;
	}

	/**
	 * @param $tagOutputFromRow
	 * @param $handlePrefix
	 * @param $generatedHandle
	 * @param $handlesInfo
	 *
	 * @return void
	 */
	public static function maybeUpdateOldGeneratedHandleNameWithTheNewOne($tagOutputFromRow, $handlePrefix, $generatedHandle, $handlesInfo)
	{
		// If the newly generated handle name is different from the old one from the database, removing the rule form the CSS/JS manager won't work
		// Update the old handle name from the database with the new one (fetch the database entries and update them accordingly)
		if ( $handlePrefix === 'wpacu_hardcoded_style_' || $handlePrefix === 'wpacu_hardcoded_link_' ) {
			$forTagType = 'style';
			$assetType  = 'styles';
		} else {
			$forTagType = 'script';
			$assetType  = 'scripts';
		}

		if (empty($handlesInfo[$assetType]) || (isset($handlesInfo[$assetType][$generatedHandle]['output']) && ! empty($handlesInfo[$assetType][$generatedHandle]['output']))) {
			return; // stop here as the newly generated handle already exists in the database or there are no assets saved yet (so nothing to update)
		}

		foreach ($handlesInfo[$assetType] as $assetHandleFromDb => $assetValuesFromDb) {
			if (strpos($assetHandleFromDb, 'wpacu_hardcoded_') === false) {
				continue; // not a hardcoded tag
			}

			$tagOutputFromDb = (isset($assetValuesFromDb['output']) && $assetValuesFromDb['output']) ? $assetValuesFromDb['output'] : false;

			if (! $tagOutputFromDb) {
				continue; // no output to compare against
			}

			if (self::canBeConsideredTheSameTag($tagOutputFromRow, $tagOutputFromDb, $forTagType, $generatedHandle)) {
				// A match was found. The handle from the database is outdated, and $assetHandleFromDb will get updated to $generatedHandle

				self::updateHandle($assetHandleFromDb, $generatedHandle);
			}
		}
	}

	/**
	 * Look for all the places where a handle could be located and change its name
	 *
	 * @param $currentHandle
	 * @param $newHandle
	 * @param $triggerQueries
	 *
	 * @return void
	 */
	public static function updateHandle($currentHandle, $newHandle, $triggerQueries = true)
	{
		global $wpdb;

		if ( ! $triggerQueries ) {
			echo 'Queries are not triggered for debugging purposes' . "\n";
		}

		// Prepare just in case
		$preparedCurrentHandle = sanitize_title($currentHandle);
		$preparedNewHandle     = sanitize_title($newHandle);
		$preparedPluginPrefix  = sanitize_title(WPACU_PLUGIN_ID.'_');

		// "options" table
		$sqlUpdateQuery = <<<SQL
UPDATE `{$wpdb->prefix}options`
SET `option_value` = REPLACE(`option_value`, '{$preparedCurrentHandle}', '{$preparedNewHandle}')
WHERE `option_name` LIKE '%{$preparedPluginPrefix}%' AND `option_value` LIKE '%{$preparedCurrentHandle}%'
SQL;
		if ($triggerQueries) {
			$wpdb->query( $sqlUpdateQuery );
		}

		// Make sure all other tables from the database where there might be plugin rules are also updated
		// `postmeta`, `usermeta`, `termmeta` tables
		// `usermeta` and `termmeta` might have traces from the Pro version (if ever used)
		foreach (array('postmeta', 'usermeta', 'termmeta') as $tableBaseName) {
			$sqlUpdateQuery = <<<SQL
UPDATE `{$wpdb->prefix}{$tableBaseName}`
SET `meta_value` = REPLACE(`meta_value`, '{$preparedCurrentHandle}', '{$preparedNewHandle}')
WHERE `meta_key` LIKE '%{$preparedPluginPrefix}%' AND `meta_value` LIKE '%{$preparedCurrentHandle}%'
SQL;
			if ($triggerQueries) {
				$wpdb->query( $sqlUpdateQuery );
			}
		}
	}

	/**
	 * @param $dataRowObj
	 * @param $data
	 * @param $assetType
	 *
	 * @return array
	 */
	public static function wpacuGenerateHardcodedAssetData( $dataRowObj, $data, $assetType )
	{
		$dataHH = $data;

		$dataHH['row']        = array();
		$dataHH['row']['obj'] = $dataRowObj;

		$tagOutputFromRow = $dataHH['row']['obj']->tag_output;

		$possibleHardcodedHandles = array_merge(
			array($dataHH['row']['obj']->handle),
			$dataHH['row']['obj']->handles_maybe
		);

		$activePageLevel = ( isset( $dataHH['current_unloaded_page_level'][$assetType] )
			&& self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['current_unloaded_page_level'][$assetType], $tagOutputFromRow ) );

		$dataHH['row']['class']   = $activePageLevel ? 'wpacu_not_load' : '';
		$dataHH['row']['checked'] = $activePageLevel ? 'checked="checked"' : '';

		/*
		 * $data['row']['is_group_unloaded'] is only used to apply a red background in the asset's area to point out that the asset is unloaded
		 * is set to `true` if either the asset is unloaded everywhere or it's unloaded on a group of pages (such as all pages belonging to 'page' post type)
		*/
		$dataHH['row']['global_unloaded'] = $dataHH['row']['is_post_type_unloaded'] = $dataHH['row']['is_group_unloaded'] = false;

		// Mark it as unloaded - Everywhere
		if ( isset($dataHH['global_unload'][$assetType]) &&
		     is_array($dataHH['global_unload'][$assetType]) &&
		     self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['global_unload'][$assetType], $tagOutputFromRow ) ) {
			$dataHH['row']['global_unloaded'] = $dataHH['row']['is_group_unloaded'] = true;
		}

		// Mark it as unloaded - for the Current Post Type
		if ( isset( $dataHH['bulk_unloaded_type'], $dataHH['bulk_unloaded'][ $dataHH['bulk_unloaded_type'] ][ $assetType ] )
		     && is_array( $dataHH['bulk_unloaded'][ $dataHH['bulk_unloaded_type'] ][ $assetType ] )
		     && self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['bulk_unloaded'][ $dataHH['bulk_unloaded_type'] ][ $assetType ], $tagOutputFromRow ) ) {
			$dataHH['row']['is_group_unloaded'] = true;

			if ( $dataHH['bulk_unloaded_type'] === 'post_type' ) {
				$dataHH['row']['is_post_type_unloaded'] = true;
			}
		}

		$isLoadExceptionPerPage = isset( $dataHH['load_exceptions_per_page'][$assetType] )
			 && self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['load_exceptions_per_page'][$assetType], $tagOutputFromRow );

		$isLoadExceptionForCurrentPostType = isset( $dataHH['load_exceptions_post_type'][$assetType] )
			&& self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['load_exceptions_post_type'][$assetType], $tagOutputFromRow );

		$isUnloadRegExMatch = isset( $dataHH['unloads_regex_matches'][$assetType] )
			&& self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['unloads_regex_matches'][$assetType], $tagOutputFromRow );

		$isLoadExceptionRegExMatch = isset( $dataHH['load_exceptions_regex_matches'][$assetType] )
			&& self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $dataHH['load_exceptions_regex_matches'][$assetType], $tagOutputFromRow );

		$isLoadExceptionForCurrentPostViaTax = isset( $data['load_exceptions_post_type_via_tax_matches'][$assetType] )
			&& self::ruleMatchesForHardcodedList( $possibleHardcodedHandles, $data['load_exceptions_post_type_via_tax_matches'][$assetType], $tagOutputFromRow );

		$dataHH['row']['is_load_exception_per_page']  = $isLoadExceptionPerPage;
		$dataHH['row']['is_load_exception_post_type'] = $isLoadExceptionForCurrentPostType;

		$isLoadException = $isLoadExceptionPerPage || $isLoadExceptionRegExMatch || $isLoadExceptionForCurrentPostViaTax;

		// No load exception to any kind and a bulk unload rule is applied? Append the CSS class for unloading
		if ( ! $isLoadException && ( $dataHH['row']['is_group_unloaded'] || $isUnloadRegExMatch ) ) {
			$dataHH['row']['class'] .= ' wpacu_not_load';
		}

		$classPart = ($assetType === 'styles') ? ' style_' : ' script_';

		$dataHH['row']['class'] .= $classPart . $dataHH['row']['obj']->handle;

		$dataHH['row']['asset_type'] = $assetType;

		return $dataHH;
	}

	/**
	 * @param $allPossibleHardcodedHandles
	 * @param $ruleList
	 * @param $tagOutputFromRow
	 *
	 * @return bool
	 */
	public static function ruleMatchesForHardcodedList($allPossibleHardcodedHandles, $ruleList, $tagOutputFromRow)
	{
		// Take the current hardcoded asset that is marked for unload
		// This is like a fallback for an older outputs saved
		// If the tag content or its minified content is the same as any of the found tags in the HTML source
		// grab its handle name and append it to the $allPossibleHardcodedHandles list

		// Any match already?
		if (MiscPro::inArrayIfAnyExists($allPossibleHardcodedHandles, $ruleList)) {
			return true;
		}

		// Last check, compare the outputs of the saved tag marked for unload and the ones found in the HTML source
		if ( ! empty($ruleList) ) {
			$handlesInfo = Main::getHandlesInfo();

			foreach ($ruleList as $ruleHardcodedHandle) {
				if (strpos($ruleHardcodedHandle, 'wpacu_hardcoded_') === false) {
					continue; // skip non-hardcoded assets as they are irrelevant in this case
				}

				if ( strpos( $ruleHardcodedHandle, 'wpacu_hardcoded_style_' ) !== false || strpos( $ruleHardcodedHandle, 'wpacu_hardcoded_link_' ) !== false ) {
					$assetType  = 'styles';
					$forTagType = 'style';
				} else {
					$assetType  = 'scripts';
					$forTagType = 'script';
				}

				// Get the output of the tag from $handlesInfo and compare it with the current targeted tag: $tagOutputFromRow
				$tagOutputFromHandlesInfo = isset( $handlesInfo[ $assetType ][ $ruleHardcodedHandle ]['output'] ) ? $handlesInfo[ $assetType ][ $ruleHardcodedHandle ]['output'] : false;

				if ( ! $tagOutputFromHandlesInfo ) {
					continue; // nothing to compare against
				}

				if (self::canBeConsideredTheSameTag($tagOutputFromHandlesInfo, $tagOutputFromRow, $forTagType, $ruleHardcodedHandle)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $tagOne
	 * @param $tagTwo
	 * @param $forTagType
	 * @param $hardcodedHandle
	 *
	 * @return bool
	 */
	public static function canBeConsideredTheSameTag($tagOne, $tagTwo, $forTagType, $hardcodedHandle)
	{
		$hardcodedLinkOrScriptWithSource = ( strpos($hardcodedHandle, 'wpacu_hardcoded_link_') !== false)
		                                   || ( strpos($hardcodedHandle, 'wpacu_hardcoded_script_src_') !== false);

		if ($hardcodedLinkOrScriptWithSource) {
			// In case old LINK[rel="stylesheet"][src] and SCRIPT[src] tags are in the database, make sure to compare the source code with the ones from the HTML source
			$finalCleanSourceFromOne = HardcodedAssets::getRelSourceFromTagOutputForReference($tagOne);
			$finalCleanSourceFromTwo = HardcodedAssets::getRelSourceFromTagOutputForReference($tagTwo);

			if (($finalCleanSourceFromOne && $finalCleanSourceFromTwo) && $finalCleanSourceFromOne === $finalCleanSourceFromTwo) {
				return true;
			}
		}

		$hardcodedInlineStyleOrInlineScript = ( strpos($hardcodedHandle, 'wpacu_hardcoded_style_') !== false) || ( strpos($hardcodedHandle, 'wpacu_hardcoded_script_inline_') !== false);

		if ($hardcodedInlineStyleOrInlineScript) {
			$tagContentFromOne = self::getTagContent( $tagOne, $forTagType );
			$tagContentFromTwo = self::getTagContent( $tagTwo, $forTagType );

			if ( ($tagOne && $tagTwo) && $tagContentFromOne === $tagContentFromTwo ) { // If the tag content the same?
				return true;
			}

			// Last try, compare the minified content for both tag outputs
			if ( $forTagType === 'style' && self::compareMinifiedCss( $tagContentFromOne, $tagContentFromTwo ) ) {
				return true;
			}

			if ( $forTagType === 'script' && MinifyJs::applyMinification( $tagContentFromOne ) === MinifyJs::applyMinification( $tagContentFromTwo ) ) {
				return true;
			}
		}

		return false;
	}
}
